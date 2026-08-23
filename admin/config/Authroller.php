<?php
/**
 * Authroller
 * ---------------------------------------------------------------------
 * The admin side's equivalent of config/Controller.php.
 *
 * It EXTENDS Controller rather than opening its own PDO connection, so
 * the admin panel always talks to the exact same database as the rest
 * of the site (see Controller::__construct() for the connection setup —
 * $DB is `protected` there specifically so this class can reuse it).
 *
 * Everything in here is scoped to what an admin does: authenticate
 * against admin_users (a completely separate table from the investor
 * `users` table, so admin and investor logins never mix), publish/edit/
 * remove SongShare offerings, review investor accounts, approve or
 * reject deposits and withdrawals, manage the crypto wallet addresses
 * investors deposit to, and pull a platform-wide transaction feed.
 *
 * Investor-facing pages (offering-detail.php, settings.php, etc.) never
 * touch this class — they only use Controller. Admin pages never use
 * Controller directly for writes — they go through Authroller. That
 * split is what keeps "admin functions" and "user functions" from
 * mixing.
 */
require_once __DIR__ . '/../../config/Controller.php';

class Authroller extends Controller
{
    /* =================================================================
     * Admin auth (admin_users table — separate from investor `users`)
     * ================================================================= */

    /** Verifies admin credentials. Returns the admin_users row (minus password_hash) or null. */
    public function AdminAuthenticate($email, $password)
    {
        if (!$this->DB || !$email) return null;

        $stmt = $this->DB->prepare("SELECT * FROM admin_users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            unset($admin['password_hash']);
            return $admin;
        }
        return null;
    }

    /** A single admin by id (used to re-hydrate the session-stored admin on each request). */
    public function AdminUser($id)
    {
        if (!$this->DB) return null;
        $stmt = $this->DB->prepare("SELECT id, name, email, role, created_at FROM admin_users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /* =================================================================
     * Offerings (songs table) — admin CRUD.
     * Reads (list/detail/shares-allocated) already exist on Controller
     * (Offerings(), Offering(), SharesAllocated(), Milestones()) and are
     * reused as-is; only writes live here.
     * ================================================================= */

    /** All offerings for the admin table, richest-first, with shares-sold worked out per row. */
    public function AdminOfferings()
    {
        $rows = $this->Offerings(); // Controller::Offerings() — SELECT * FROM songs ORDER BY created_at DESC
        foreach ($rows as &$row) {
            $row['shares_sold'] = $this->SharesAllocated($row['id']);
        }
        return $rows;
    }

    /** Creates a new offering. Returns the new song id, or false on failure. */
    public function AddOffering(array $data)
    {
        if (!$this->DB) return false;

        $sql = "INSERT INTO songs
                    (title, artist, category, description, image_url, price_per_share, total_shares, yield_percent, duration_days, status, featured, ends_at, created_at)
                VALUES
                    (:title, :artist, :category, :description, :image_url, :price, :total_shares, :yield_percent, :duration_days, :status, :featured, :ends_at, NOW())";

        $ok = $this->DB->prepare($sql)->execute([
            ':title'          => $data['title'] ?? '',
            ':artist'         => $data['artist'] ?? '',
            ':category'       => $data['category'] ?? null,
            ':description'    => $data['description'] ?? null,
            ':image_url'      => $data['image_url'] ?? null,
            ':price'          => $data['price'] ?? 0,
            ':total_shares'   => $data['total_shares'] ?? 0,
            ':yield_percent'  => ($data['yield_percent'] ?? '') !== '' ? $data['yield_percent'] : null,
            ':duration_days'  => ($data['duration_days'] ?? '') !== '' ? $data['duration_days'] : null,
            ':status'         => $data['status'] ?? 'sale',
            ':featured'       => !empty($data['featured']) ? 1 : 0,
            ':ends_at'        => $data['ends_at'] ?? null,
        ]);

        if (!$ok) return false;

        $songId = (int) $this->DB->lastInsertId();

        if (!empty($data['milestones'])) {
            $this->ReplaceMilestones($songId, $data['milestones']);
        }

        return $songId;
    }

    /** Updates an existing offering (and its milestone schedule, if provided). */
    public function UpdateOffering($id, array $data)
    {
        if (!$this->DB || !$id) return false;

        $sql = "UPDATE songs SET
                    title = :title,
                    artist = :artist,
                    category = :category,
                    description = :description,
                    image_url = :image_url,
                    price_per_share = :price,
                    total_shares = :total_shares,
                    yield_percent = :yield_percent,
                    duration_days = :duration_days,
                    status = :status,
                    featured = :featured,
                    ends_at = :ends_at
                WHERE id = :id";

        $ok = $this->DB->prepare($sql)->execute([
            ':title'          => $data['title'] ?? '',
            ':artist'         => $data['artist'] ?? '',
            ':category'       => $data['category'] ?? null,
            ':description'    => $data['description'] ?? null,
            ':image_url'      => $data['image_url'] ?? null,
            ':price'          => $data['price'] ?? 0,
            ':total_shares'   => $data['total_shares'] ?? 0,
            ':yield_percent'  => ($data['yield_percent'] ?? '') !== '' ? $data['yield_percent'] : null,
            ':duration_days'  => ($data['duration_days'] ?? '') !== '' ? $data['duration_days'] : null,
            ':status'         => $data['status'] ?? 'sale',
            ':featured'       => !empty($data['featured']) ? 1 : 0,
            ':ends_at'        => $data['ends_at'] ?? null,
            ':id'             => $id,
        ]);

        if ($ok && isset($data['milestones'])) {
            $this->ReplaceMilestones($id, $data['milestones']);
        }

        return $ok;
    }

    /** Deletes an offering. Investments/favorites/milestones cascade via FK. */
    public function DeleteOffering($id)
    {
        if (!$this->DB || !$id) return false;
        return $this->DB->prepare("DELETE FROM songs WHERE id = :id")->execute([':id' => $id]);
    }

    /** Replaces an offering's growth-milestone schedule with the given rows: [['days'=>30,'pct'=>3], ...]. */
    private function ReplaceMilestones($songId, array $milestones)
    {
        if (!$this->DB) return false;

        $this->DB->prepare("DELETE FROM offering_milestones WHERE song_id = :id")->execute([':id' => $songId]);

        $stmt = $this->DB->prepare(
            "INSERT INTO offering_milestones (song_id, days, pct, sort_order) VALUES (:song_id, :days, :pct, :sort_order)"
        );
        $order = 0;
        foreach ($milestones as $m) {
            if (($m['days'] ?? '') === '' || ($m['pct'] ?? '') === '') continue;
            $order++;
            $stmt->execute([
                ':song_id'    => $songId,
                ':days'       => (int) $m['days'],
                ':pct'        => (float) $m['pct'],
                ':sort_order' => $order,
            ]);
        }
        return true;
    }

    /* =================================================================
     * Investors (read-only overview of the `users` table)
     * ================================================================= */

    /** Every registered investor, with holdings/balance worked out per row, newest first. */
    public function AllInvestors()
    {
        if (!$this->DB) return [];

        $stmt = $this->DB->query("SELECT id, first_name, last_name, email, verified, created_at FROM users ORDER BY created_at DESC");
        $rows = $stmt->fetchAll();

        $sharesStmt = $this->DB->prepare("SELECT COALESCE(SUM(shares), 0) FROM investments WHERE user_id = :id AND status = 'Active'");

        foreach ($rows as &$row) {
            $sharesStmt->execute([':id' => $row['id']]);
            $row['holdings'] = (int) $sharesStmt->fetchColumn();
            $row['balance']  = $this->AccountBalance($row['id']);
        }

        return $rows;
    }

    /* =================================================================
     * Deposits — admin review queue
     * ================================================================= */

    /** Deposits still awaiting review, oldest-first so the queue drains in order. */
    public function PendingDeposits()
    {
        if (!$this->DB) return [];
        $sql = "SELECT d.*, CONCAT(u.first_name, ' ', u.last_name) AS investor_name
                FROM deposits d
                JOIN users u ON u.id = d.user_id
                WHERE d.status = 'Pending'
                ORDER BY d.created_at ASC";
        return $this->DB->query($sql)->fetchAll();
    }

    /** Recently resolved (Confirmed/Failed) deposits, newest first. */
    public function ResolvedDeposits($limit = 25)
    {
        if (!$this->DB) return [];
        $sql = "SELECT d.*, CONCAT(u.first_name, ' ', u.last_name) AS investor_name
                FROM deposits d
                JOIN users u ON u.id = d.user_id
                WHERE d.status != 'Pending'
                ORDER BY d.created_at DESC
                LIMIT " . (int) $limit;
        return $this->DB->query($sql)->fetchAll();
    }

    /** Marks a deposit Confirmed — Controller::AccountBalance() picks this up immediately. */
    public function ConfirmDeposit($id)
    {
        if (!$this->DB || !$id) return false;
        return $this->DB->prepare("UPDATE deposits SET status = 'Confirmed' WHERE id = :id AND status = 'Pending'")
            ->execute([':id' => $id]);
    }

    /** Marks a deposit Failed (rejected). */
    public function RejectDeposit($id)
    {
        if (!$this->DB || !$id) return false;
        return $this->DB->prepare("UPDATE deposits SET status = 'Failed' WHERE id = :id AND status = 'Pending'")
            ->execute([':id' => $id]);
    }

    /* =================================================================
     * Withdrawals — admin review queue
     * ================================================================= */

    /** Withdrawals still awaiting review, oldest-first. */
    public function PendingWithdrawals()
    {
        if (!$this->DB) return [];
        $sql = "SELECT w.*, CONCAT(u.first_name, ' ', u.last_name) AS investor_name
                FROM withdrawals w
                JOIN users u ON u.id = w.user_id
                WHERE w.status = 'Pending'
                ORDER BY w.created_at ASC";
        return $this->DB->query($sql)->fetchAll();
    }

    /** Recently resolved (Completed/Rejected) withdrawals, newest first. */
    public function ResolvedWithdrawals($limit = 25)
    {
        if (!$this->DB) return [];
        $sql = "SELECT w.*, CONCAT(u.first_name, ' ', u.last_name) AS investor_name
                FROM withdrawals w
                JOIN users u ON u.id = w.user_id
                WHERE w.status != 'Pending'
                ORDER BY w.created_at DESC
                LIMIT " . (int) $limit;
        return $this->DB->query($sql)->fetchAll();
    }

    /** Marks a withdrawal Completed (funds already sent off-platform by the admin). */
    public function CompleteWithdrawal($id)
    {
        if (!$this->DB || !$id) return false;
        return $this->DB->prepare("UPDATE withdrawals SET status = 'Completed' WHERE id = :id AND status = 'Pending'")
            ->execute([':id' => $id]);
    }

    /** Rejects a withdrawal — the funds simply stay in the investor's available balance. */
    public function RejectWithdrawal($id)
    {
        if (!$this->DB || !$id) return false;
        return $this->DB->prepare("UPDATE withdrawals SET status = 'Rejected' WHERE id = :id AND status = 'Pending'")
            ->execute([':id' => $id]);
    }

    /* =================================================================
     * Payment wallets (payment_wallets table) — the crypto deposit
     * addresses shown to investors on settings.php. Reads are inherited
     * from Controller::PaymentWallets(); only writes live here.
     * ================================================================= */

    /** Upserts a single network's address + QR code (adds it if the network is new).
     *  Passing a null/empty $qrCodeUrl leaves an existing QR code untouched. */
    public function SavePaymentWallet($network, $address, $qrCodeUrl = null)
    {
        if (!$this->DB || !$network) return false;

        $sql = "INSERT INTO payment_wallets (network, address, qr_code_url, sort_order)
                VALUES (:network, :address, :qr_code_url, 99)
                ON DUPLICATE KEY UPDATE
                    address = VALUES(address),
                    qr_code_url = COALESCE(NULLIF(VALUES(qr_code_url), ''), qr_code_url)";
        return $this->DB->prepare($sql)->execute([
            ':network'     => $network,
            ':address'     => $address,
            ':qr_code_url' => $qrCodeUrl ?: null,
        ]);
    }

    /** Removes a wallet/network entirely. */
    public function DeletePaymentWallet($id)
    {
        if (!$this->DB || !$id) return false;
        return $this->DB->prepare("DELETE FROM payment_wallets WHERE id = :id")->execute([':id' => $id]);
    }

    /* =================================================================
     * Platform-wide transactions feed
     * ================================================================= */

    /** Latest transactions across every investor, newest first. */
    public function AllTransactions($limit = 25)
    {
        if (!$this->DB) return [];
        $sql = "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) AS investor_name
                FROM transactions t
                JOIN users u ON u.id = t.user_id
                ORDER BY t.created_at DESC
                LIMIT " . (int) $limit;
        return $this->DB->query($sql)->fetchAll();
    }

    /* =================================================================
     * Dashboard stat cards
     * ================================================================= */

    public function Stats()
    {
        if (!$this->DB) {
            return [
                'total_offerings'    => 0,
                'active_offerings'   => 0,
                'total_users'        => 0,
                'verified_users'     => 0,
                'shares_sold'        => 0,
                'capital_raised'     => 0,
                'pending_deposits'   => 0,
                'pending_deposit_amount' => 0,
                'registered_investors'  => 0,
            ];
        }

        $totalOfferings  = (int) $this->DB->query("SELECT COUNT(*) FROM songs")->fetchColumn();
        $activeOfferings = (int) $this->DB->query("SELECT COUNT(*) FROM songs WHERE status = 'sale'")->fetchColumn();
        $totalUsers      = (int) $this->DB->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $verifiedUsers   = (int) $this->DB->query("SELECT COUNT(*) FROM users WHERE verified = 1")->fetchColumn();
        $sharesSold      = (int) $this->DB->query("SELECT COALESCE(SUM(shares), 0) FROM investments WHERE status = 'Active'")->fetchColumn();
        $capitalRaised   = (float) $this->DB->query("SELECT COALESCE(SUM(value), 0) FROM investments WHERE status = 'Active'")->fetchColumn();
        $pendingDeposits = (int) $this->DB->query("SELECT COUNT(*) FROM deposits WHERE status = 'Pending'")->fetchColumn();
        $pendingAmount   = (float) $this->DB->query("SELECT COALESCE(SUM(amount), 0) FROM deposits WHERE status = 'Pending'")->fetchColumn();

        return [
            'total_offerings'        => $totalOfferings,
            'active_offerings'       => $activeOfferings,
            'total_users'            => $totalUsers,
            'verified_users'         => $verifiedUsers,
            'shares_sold'            => $sharesSold,
            'capital_raised'         => $capitalRaised,
            'pending_deposits'       => $pendingDeposits,
            'pending_deposit_amount' => $pendingAmount,
            'registered_investors'   => $totalUsers,
        ];
    }
}
