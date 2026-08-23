<?php
/**
 * Controller
 * ---------------------------------------------------------------------
 * A single class that owns the PDO database connection and exposes the
 * CRUD operations the rest of the app needs (users, offerings,
 * investments, transactions, deposits/withdrawals, and editable
 * landing-page content).
 *
 * The connection is intentionally forgiving: if the database isn't
 * reachable (e.g. this is a fresh checkout and nobody has set up MySQL
 * yet), $DB stays null and every read method returns an empty result
 * instead of throwing. Pages render an honest empty/error state in that
 * case rather than showing fake data — see database/musixvest.sql to
 * get a real database up and running.
 *
 * Edit the connection constants below (or set the matching environment
 * variables) to point this at a real MySQL database seeded from
 * database/musixvest.sql.
 */
class Controller
{
    /** @var \PDO|null protected (not private) so admin/config/Authroller.php can extend this
     *  class and reuse the same connection instead of opening a second one. */
    protected $DB;

    public function __construct()
    {
        $host    = getenv('DB_HOST') ?: '127.0.0.1';
        $name    = getenv('DB_NAME') ?: 'musixvest';
        $user    = getenv('DB_USER') ?: 'root';
        $pass    = getenv('DB_PASS') ?: '';
        $charset = 'utf8mb4';

        try {
            $this->DB = new PDO(
                "mysql:host={$host};dbname={$name};charset={$charset}",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            // No DB yet — leave $DB null so callers can fall back gracefully.
            $this->DB = null;
        }
    }

    /** Whether a live database connection is available. */
    public function isConnected()
    {
        return $this->DB !== null;
    }

    /* =================================================================
     * Users
     * ================================================================= */

    /** Adds a new user. Returns the new user id, or false on failure. */
    public function AddUser(array $data)
    {
        if (!$this->DB) return false;

        $sql = "INSERT INTO users (first_name, last_name, email, password_hash, phone, country, created_at)
                VALUES (:first_name, :last_name, :email, :password_hash, :phone, :country, NOW())";
        $stmt = $this->DB->prepare($sql);
        $ok = $stmt->execute([
            ':first_name'    => trim($data['first_name'] ?? ''),
            ':last_name'     => trim($data['last_name'] ?? ''),
            ':email'         => trim(strtolower($data['email'] ?? '')),
            ':password_hash' => password_hash($data['password'] ?? '', PASSWORD_DEFAULT),
            ':phone'         => $data['phone'] ?? null,
            ':country'       => $data['country'] ?? null,
        ]);

        return $ok ? (int) $this->DB->lastInsertId() : false;
    }

    /** Returns a user by id or email. Null if not found / no DB. */
    public function User($id = null, $email = null)
    {
        if (!$this->DB) return null;

        if ($id !== null) {
            $stmt = $this->DB->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
        } elseif ($email !== null) {
            $stmt = $this->DB->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => trim(strtolower($email))]);
        } else {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Updates whichever allowed fields are present in $data. */
    public function UpdateUser($id, array $data)
    {
        if (!$this->DB) return false;

        $allowed = [
            'first_name', 'last_name', 'email', 'phone', 'country',
            'preferred_offering_type', 'investment_range', 'autobuy',
            'email_notifications', 'offering_alerts', 'royalty_alerts',
        ];

        $fields = [];
        $params = [':id' => $id];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (!$fields) return false;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        return $this->DB->prepare($sql)->execute($params);
    }

    /** Verifies the current password then sets a new one. */
    public function ChangePassword($id, $currentPassword, $newPassword)
    {
        if (!$this->DB) return false;

        $user = $this->User($id);
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            return false;
        }

        $stmt = $this->DB->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        return $stmt->execute([
            ':hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id'   => $id,
        ]);
    }

    /** Looks a user up by email and checks their password. Returns the user row or null. */
    public function Authenticate($email, $password)
    {
        $user = $this->User(null, $email);
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return null;
    }

    /** Saves (or updates) the KYC/identity-verification details for a user. */
    public function SaveVerification($userId, array $data)
    {
        if (!$this->DB) return false;

        $ssnDigits = preg_replace('/\D/', '', $data['ssn'] ?? '');

        $sql = "INSERT INTO kyc_details
                    (user_id, citizenship_status, dob, ssn_last4, address_line1, address_line2, city, state, country, zip, mobile, submitted_at)
                VALUES
                    (:user_id, :citizenship_status, :dob, :ssn_last4, :address_line1, :address_line2, :city, :state, :country, :zip, :mobile, NOW())
                ON DUPLICATE KEY UPDATE
                    citizenship_status = VALUES(citizenship_status),
                    dob                = VALUES(dob),
                    ssn_last4          = VALUES(ssn_last4),
                    address_line1      = VALUES(address_line1),
                    address_line2      = VALUES(address_line2),
                    city               = VALUES(city),
                    state              = VALUES(state),
                    country            = VALUES(country),
                    zip                = VALUES(zip),
                    mobile             = VALUES(mobile),
                    submitted_at       = NOW()";

        $ok = $this->DB->prepare($sql)->execute([
            ':user_id'            => $userId,
            ':citizenship_status' => $data['citizenship_status'] ?? null,
            ':dob'                => $data['dob'] ?? null,
            ':ssn_last4'          => $ssnDigits ? substr($ssnDigits, -4) : null,
            ':address_line1'      => $data['address1'] ?? null,
            ':address_line2'      => $data['address2'] ?? null,
            ':city'               => $data['city'] ?? null,
            ':state'              => $data['state'] ?? null,
            ':country'            => $data['country'] ?? null,
            ':zip'                => $data['zip'] ?? null,
            ':mobile'             => $data['mobile'] ?? null,
        ]);

        if ($ok) {
            $this->DB->prepare("UPDATE users SET verified = 1 WHERE id = :id")->execute([':id' => $userId]);
        }

        return $ok;
    }

    /* =================================================================
     * Offerings / SongShares catalog
     * ================================================================= */

    /**
     * Returns catalog offerings. $filter supports:
     *   status   => 'sale' | 'auction' | 'soldout'
     *   featured => true
     *   search   => string (matches title or artist)
     *   limit    => int
     */
    public function Offerings(array $filter = [])
    {
        if (!$this->DB) return [];

        $sql = "SELECT * FROM songs WHERE 1 = 1";
        $params = [];

        if (!empty($filter['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filter['status'];
        }
        if (!empty($filter['featured'])) {
            $sql .= " AND featured = 1";
        }
        if (!empty($filter['search'])) {
            $sql .= " AND (title LIKE :search OR artist LIKE :search)";
            $params[':search'] = '%' . $filter['search'] . '%';
        }

        $sql .= " ORDER BY created_at DESC";

        if (!empty($filter['limit'])) {
            $sql .= " LIMIT " . (int) $filter['limit'];
        }

        $stmt = $this->DB->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Shortcut for Offerings(['featured' => true]) — used on the homepage. */
    public function FeaturedOfferings($limit = 6)
    {
        return $this->Offerings(['featured' => true, 'limit' => $limit]);
    }

    /** A single offering by id. */
    public function Offering($id)
    {
        if (!$this->DB) return null;
        $stmt = $this->DB->prepare("SELECT * FROM songs WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Total shares already invested in an offering (used to compute shares available / % allocated). */
    public function SharesAllocated($songId)
    {
        if (!$this->DB) return 0;
        $stmt = $this->DB->prepare("SELECT COALESCE(SUM(shares), 0) FROM investments WHERE song_id = :song_id");
        $stmt->execute([':song_id' => $songId]);
        return (int) $stmt->fetchColumn();
    }

    /** Illustrative projected-value milestones for an offering (Days Held / Projected Value Increase). */
    public function Milestones($songId)
    {
        if (!$this->DB) return [];
        $stmt = $this->DB->prepare("SELECT * FROM offering_milestones WHERE song_id = :song_id ORDER BY sort_order ASC");
        $stmt->execute([':song_id' => $songId]);
        return $stmt->fetchAll();
    }

    /* =================================================================
     * Investments (SongShares a user owns)
     * ================================================================= */

    /**
     * Returns a user's holdings. $filter supports:
     *   status => 'Active' | 'Pending'
     */
    public function getSongShares($userId, array $filter = [])
    {
        if (!$this->DB) return [];

        $sql = "SELECT i.*, s.title AS song_title, s.artist AS song_artist
                FROM investments i
                JOIN songs s ON s.id = i.song_id
                WHERE i.user_id = :user_id";
        $params = [':user_id' => $userId];

        if (!empty($filter['status'])) {
            $sql .= " AND i.status = :status";
            $params[':status'] = $filter['status'];
        }

        $sql .= " ORDER BY i.created_at DESC";

        $stmt = $this->DB->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Buys shares in an offering: creates the investment row + a matching transaction.
     * Returns:
     *   true                    - purchase completed
     *   'insufficient_balance'  - user's available balance can't cover the cost
     *   false                   - invalid input / offering not found / db error
     */
    public function AddInvestment($userId, $songId, $shares)
    {
        if (!$this->DB) return false;

        $song = $this->Offering($songId);
        if (!$song || $shares <= 0) return false;

        $value = $song['price_per_share'] * $shares;

        // Verify the buyer actually has the funds before debiting the account.
        if ($value > $this->AccountBalance($userId)) {
            return 'insufficient_balance';
        }

        $this->DB->beginTransaction();
        try {
            // Re-check the balance now that we hold the transaction, in case a
            // second request (e.g. a double-click or another tab) spent the
            // funds between the first check above and here.
            if ($value > $this->AccountBalance($userId)) {
                $this->DB->rollBack();
                return 'insufficient_balance';
            }

            $stmt = $this->DB->prepare(
                "INSERT INTO investments (user_id, song_id, shares, value, return_percent, status, created_at)
                 VALUES (:user_id, :song_id, :shares, :value, 0, 'Active', NOW())"
            );
            $stmt->execute([
                ':user_id' => $userId,
                ':song_id' => $songId,
                ':shares'  => $shares,
                ':value'   => $value,
            ]);

            $this->AddTransaction($userId, [
                'description' => $song['title'] . ' SongShares',
                'type'        => 'Investment',
                'amount'      => -$value,
                'status'      => 'Completed',
            ]);

            $this->DB->commit();
            return true;
        } catch (Exception $e) {
            $this->DB->rollBack();
            return false;
        }
    }

    /* =================================================================
     * Transactions (Invoices)
     * ================================================================= */

    /**
     * Returns a user's transactions. $filter supports:
     *   type  => 'Deposit' | 'Investment' | 'Credit'
     *   limit => int
     */
    public function getTransactions($userId, array $filter = [])
    {
        if (!$this->DB) return [];

        $sql = "SELECT * FROM transactions WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if (!empty($filter['type'])) {
            $sql .= " AND type = :type";
            $params[':type'] = $filter['type'];
        }

        $sql .= " ORDER BY created_at DESC";

        if (!empty($filter['limit'])) {
            $sql .= " LIMIT " . (int) $filter['limit'];
        }

        $stmt = $this->DB->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Logs a transaction row (deposit, investment debit, royalty credit, etc). */
    public function AddTransaction($userId, array $data)
    {
        if (!$this->DB) return false;

        $sql = "INSERT INTO transactions (user_id, description, type, amount, status, created_at)
                VALUES (:user_id, :description, :type, :amount, :status, NOW())";
        return $this->DB->prepare($sql)->execute([
            ':user_id'     => $userId,
            ':description' => $data['description'] ?? '',
            ':type'        => $data['type'] ?? 'Other',
            ':amount'      => $data['amount'] ?? 0,
            ':status'      => $data['status'] ?? 'Completed',
        ]);
    }

    /** Net available balance: completed transactions + confirmed deposits - completed withdrawals (incl. fee). */
    public function AccountBalance($userId)
    {
        if (!$this->DB) return 0;

        $stmt = $this->DB->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = :id AND status = 'Completed'");
        $stmt->execute([':id' => $userId]);
        $balance = (float) $stmt->fetchColumn();

        $stmt = $this->DB->prepare("SELECT COALESCE(SUM(amount), 0) FROM deposits WHERE user_id = :id AND status = 'Confirmed'");
        $stmt->execute([':id' => $userId]);
        $balance += (float) $stmt->fetchColumn();

        $stmt = $this->DB->prepare("SELECT COALESCE(SUM(amount + fee), 0) FROM withdrawals WHERE user_id = :id AND status = 'Completed'");
        $stmt->execute([':id' => $userId]);
        $balance -= (float) $stmt->fetchColumn();

        return $balance;
    }

    /** Lifetime royalty distributions (completed 'Credit' transactions). */
    public function TotalRoyalties($userId)
    {
        if (!$this->DB) return 0;
        $stmt = $this->DB->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = :id AND type = 'Credit' AND status = 'Completed'");
        $stmt->execute([':id' => $userId]);
        return (float) $stmt->fetchColumn();
    }

    /* =================================================================
     * Deposits (crypto-only, per Deposit Funds page)
     * ================================================================= */

    /** Logs a deposit notification; starts life as 'Pending' until manually confirmed. */
    public function AddDeposit($userId, array $data)
    {
        if (!$this->DB) return false;

        $sql = "INSERT INTO deposits (user_id, network, amount, tx_hash, status, created_at)
                VALUES (:user_id, :network, :amount, :tx_hash, 'Pending', NOW())";
        return $this->DB->prepare($sql)->execute([
            ':user_id' => $userId,
            ':network' => $data['network'] ?? '',
            ':amount'  => $data['amount'] ?? 0,
            ':tx_hash' => $data['tx_hash'] ?? '',
        ]);
    }

    /**
     * Returns a user's deposit history. $filter supports:
     *   status => 'Pending' | 'Confirmed' | 'Failed'
     */
    public function GetDeposits($userId, array $filter = [])
    {
        if (!$this->DB) return [];

        $sql = "SELECT * FROM deposits WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if (!empty($filter['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filter['status'];
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->DB->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /* =================================================================
     * Withdrawals (crypto-only, 5% fee, per Withdraw Funds page)
     * ================================================================= */

    /**
     * Requests a withdrawal (a 5% fee is recorded alongside the amount).
     * Returns:
     *   true                    - withdrawal requested
     *   'insufficient_balance'  - amount + fee exceeds the available balance
     *   false                   - invalid input / db error
     */
    public function AddWithdrawal($userId, array $data)
    {
        if (!$this->DB) return false;

        $amount = (float) ($data['amount'] ?? 0);
        if ($amount <= 0) return false;

        $fee = round($amount * 0.05, 2);
        if ($amount + $fee > $this->AccountBalance($userId)) {
            return 'insufficient_balance';
        }

        $sql = "INSERT INTO withdrawals (user_id, network, amount, fee, wallet_address, status, created_at)
                VALUES (:user_id, :network, :amount, :fee, :wallet_address, 'Pending', NOW())";
        return $this->DB->prepare($sql)->execute([
            ':user_id'       => $userId,
            ':network'       => $data['network'] ?? '',
            ':amount'        => $amount,
            ':fee'           => $fee,
            ':wallet_address' => $data['wallet'] ?? '',
        ]);
    }

    /**
     * Returns a user's withdrawal history. $filter supports:
     *   status => 'Pending' | 'Completed' | 'Rejected'
     */
    public function GetWithdrawals($userId, array $filter = [])
    {
        if (!$this->DB) return [];

        $sql = "SELECT * FROM withdrawals WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if (!empty($filter['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filter['status'];
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->DB->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * The crypto deposit addresses (+ QR codes) investors send funds to,
     * in display order. Read-only here — these are managed from the
     * admin panel (admin/config/Authroller.php::SavePaymentWallet()).
     */
    public function PaymentWallets()
    {
        if (!$this->DB) return [];
        return $this->DB->query("SELECT * FROM payment_wallets ORDER BY sort_order ASC, network ASC")->fetchAll();
    }

    /* =================================================================
     * Favorites
     * ================================================================= */

    /** Returns the offerings a user has favorited. */
    public function GetFavorites($userId)
    {
        if (!$this->DB) return [];

        $sql = "SELECT s.*
                FROM favorites f
                JOIN songs s ON s.id = f.song_id
                WHERE f.user_id = :user_id
                ORDER BY f.created_at DESC";
        $stmt = $this->DB->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /** Adds the favorite if it doesn't exist, removes it if it does. Returns 'added' | 'removed' | false. */
    public function ToggleFavorite($userId, $songId)
    {
        if (!$this->DB) return false;

        $stmt = $this->DB->prepare("SELECT id FROM favorites WHERE user_id = :user_id AND song_id = :song_id");
        $stmt->execute([':user_id' => $userId, ':song_id' => $songId]);

        if ($row = $stmt->fetch()) {
            $this->DB->prepare("DELETE FROM favorites WHERE id = :id")->execute([':id' => $row['id']]);
            return 'removed';
        }

        $this->DB->prepare("INSERT INTO favorites (user_id, song_id, created_at) VALUES (:user_id, :song_id, NOW())")
            ->execute([':user_id' => $userId, ':song_id' => $songId]);
        return 'added';
    }

    /* =================================================================
     * Payment methods
     * ================================================================= */

    /** Adds a payment method (mockup only — never store real card numbers like this in production). */
    public function AddPaymentMethod($userId, array $data)
    {
        if (!$this->DB) return false;

        $digits = preg_replace('/\D/', '', $data['card_number'] ?? '');

        $sql = "INSERT INTO payment_methods (user_id, card_brand, last4, expiry_month, expiry_year, is_default, created_at)
                VALUES (:user_id, :card_brand, :last4, :expiry_month, :expiry_year, :is_default, NOW())";
        return $this->DB->prepare($sql)->execute([
            ':user_id'      => $userId,
            ':card_brand'   => $data['card_brand'] ?? 'Card',
            ':last4'        => substr($digits, -4),
            ':expiry_month' => $data['expiry_month'] ?? null,
            ':expiry_year'  => $data['expiry_year'] ?? null,
            ':is_default'   => !empty($data['is_default']) ? 1 : 0,
        ]);
    }

    /**
     * Returns a user's payment methods. $filter supports:
     *   is_default => true
     */
    public function GetPaymentMethods($userId, array $filter = [])
    {
        if (!$this->DB) return [];

        $sql = "SELECT * FROM payment_methods WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if (!empty($filter['is_default'])) {
            $sql .= " AND is_default = 1";
        }

        $sql .= " ORDER BY is_default DESC, created_at DESC";

        $stmt = $this->DB->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Removes a payment method (scoped to the owning user). */
    public function DeletePaymentMethod($id, $userId)
    {
        if (!$this->DB) return false;
        $stmt = $this->DB->prepare("DELETE FROM payment_methods WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /* =================================================================
     * Static-ish landing page content (About team, FAQs, testimonials)
     * ================================================================= */

    /** Team members shown on the About page. */
    public function TeamMembers()
    {
        if (!$this->DB) return [];
        $stmt = $this->DB->query("SELECT * FROM team_members ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    /** FAQ entries shown on the How It Works page. */
    public function Faqs()
    {
        if (!$this->DB) return [];
        $stmt = $this->DB->query("SELECT * FROM faqs ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    /** Investor testimonial quotes shown on the How It Works page. */
    public function Testimonials()
    {
        if (!$this->DB) return [];
        $stmt = $this->DB->query("SELECT * FROM testimonials ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    /**
     * Editable prose blocks (About intro paragraphs, How It Works blurbs, etc).
     * Returns an associative array keyed by section_key for the given page.
     */
    public function PageContent($pageKey)
    {
        if (!$this->DB) return [];

        $stmt = $this->DB->prepare("SELECT section_key, content FROM page_content WHERE page_key = :page_key");
        $stmt->execute([':page_key' => $pageKey]);

        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[$row['section_key']] = $row['content'];
        }
        return $out;
    }

    /* =================================================================
     * Contact form
     * ================================================================= */

    /** Stores a message submitted through the Contact page. */
    public function AddContactMessage(array $data)
    {
        if (!$this->DB) return false;

        $sql = "INSERT INTO contact_messages (name, email, subject, message, created_at)
                VALUES (:name, :email, :subject, :message, NOW())";
        return $this->DB->prepare($sql)->execute([
            ':name'    => $data['name'] ?? '',
            ':email'   => $data['email'] ?? '',
            ':subject' => $data['subject'] ?? '',
            ':message' => $data['message'] ?? '',
        ]);
    }
}
