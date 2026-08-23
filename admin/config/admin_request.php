<?php
/**
 * admin/config/admin_request.php
 * ---------------------------------------------------------------------
 * Single endpoint every admin form/button posts to (see
 * admin/assets/js/admin-app.js). Reads $_POST['action'], calls the
 * matching Authroller method, and always replies with JSON:
 * { status, message, data } — status is one of success | danger |
 * warning | info, same contract as the investor-side config/request.php
 * so the shared toast styling behaves identically.
 *
 * Every action except admin_login requires an active admin session
 * (admin_require_login() below) — this is the actual "admin functions
 * don't mix with user functions" boundary: nothing in here ever reads
 * or writes using the investor session, and nothing on the investor
 * side ever calls into this file.
 */

require_once __DIR__ . '/admin_session.php';
require_once __DIR__ . '/Authroller.php';

header('Content-Type: application/json');

$authroller = new Authroller();
$action     = $_POST['action'] ?? '';

function respond($status, $message, $data = [])
{
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

// admin_login is the only action allowed while signed out.
if ($action !== 'admin_login' && !admin_is_logged_in()) {
    respond('danger', 'Your admin session has expired. Please log in again.');
}

if (!$authroller->isConnected() && $action !== 'admin_login') {
    respond('warning', 'The database isn\'t connected yet, so this action can\'t be saved right now. Import database/musixvest.sql and configure config/Controller.php to enable it.');
}

switch ($action) {

    /* --------------------------------------------------------------
     * Auth
     * ------------------------------------------------------------ */

    case 'admin_login':
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if (!$authroller->isConnected()) {
            respond('warning', 'The database isn\'t connected yet. Import database/musixvest.sql and configure config/Controller.php to enable admin login.');
        }

        $admin = $authroller->AdminAuthenticate($email, $pass);
        if (!$admin) {
            respond('danger', 'Incorrect email or password.');
        }

        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_name']  = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];

        respond('success', 'Welcome back, ' . $admin['name'] . '.');
        break;

    case 'admin_logout':
        // Only clear the admin_* keys — never touch a co-existing investor session.
        unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
        respond('success', 'You have been logged out.');
        break;

    /* --------------------------------------------------------------
     * Offerings
     * ------------------------------------------------------------ */

    case 'add_offering':
    case 'update_offering':
        $offeringId = (int) ($_POST['offering_id'] ?? 0);

        $title = trim($_POST['title'] ?? '');
        $artist = trim($_POST['artist'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $totalShares = (int) ($_POST['total_shares'] ?? 0);

        if (!$title || !$artist || $price <= 0 || $totalShares <= 0) {
            respond('warning', 'Please fill in the song title, artist, price per share, and total shares.');
        }

        $milestoneDays = $_POST['milestone_days'] ?? [];
        $milestonePct  = $_POST['milestone_pct'] ?? [];
        $milestones = [];
        foreach ($milestoneDays as $i => $days) {
            $pct = $milestonePct[$i] ?? '';
            if ($days !== '' && $pct !== '') {
                $milestones[] = ['days' => $days, 'pct' => $pct];
            }
        }

        $payload = [
            'title'          => $title,
            'artist'         => $artist,
            'category'       => trim($_POST['category'] ?? '') ?: null,
            'description'    => trim($_POST['description'] ?? '') ?: null,
            'image_url'      => trim($_POST['image_url'] ?? '') ?: null,
            'price'          => $price,
            'total_shares'   => $totalShares,
            'yield_percent'  => $_POST['yield_percent'] ?? '',
            'duration_days'  => $_POST['duration_days'] ?? '',
            'status'         => $_POST['status'] ?? 'sale',
            'featured'       => !empty($_POST['featured']),
            'milestones'     => $milestones,
        ];

        if ($action === 'update_offering') {
            if (!$offeringId) {
                respond('warning', 'Missing offering id.');
            }
            $ok = $authroller->UpdateOffering($offeringId, $payload);
            $ok
                ? respond('success', 'Offering updated.')
                : respond('danger', 'Could not update that offering. Please try again.');
        } else {
            $newId = $authroller->AddOffering($payload);
            $newId
                ? respond('success', 'Offering published.', ['id' => $newId])
                : respond('danger', 'Could not publish that offering. Please try again.');
        }
        break;

    case 'delete_offering':
        $offeringId = (int) ($_POST['offering_id'] ?? 0);
        if (!$offeringId) {
            respond('warning', 'Missing offering id.');
        }
        $ok = $authroller->DeleteOffering($offeringId);
        $ok
            ? respond('success', 'Offering removed.')
            : respond('danger', 'Could not remove that offering. Please try again.');
        break;

    /* --------------------------------------------------------------
     * Deposits
     * ------------------------------------------------------------ */

    case 'confirm_deposit':
        $id = (int) ($_POST['deposit_id'] ?? 0);
        if (!$id) respond('warning', 'Missing deposit id.');
        $ok = $authroller->ConfirmDeposit($id);
        $ok
            ? respond('success', 'Deposit confirmed — funds are now available to the investor.')
            : respond('danger', 'Could not confirm that deposit. Please try again.');
        break;

    case 'reject_deposit':
        $id = (int) ($_POST['deposit_id'] ?? 0);
        if (!$id) respond('warning', 'Missing deposit id.');
        $ok = $authroller->RejectDeposit($id);
        $ok
            ? respond('info', 'Deposit rejected.')
            : respond('danger', 'Could not reject that deposit. Please try again.');
        break;

    /* --------------------------------------------------------------
     * Withdrawals
     * ------------------------------------------------------------ */

    case 'complete_withdrawal':
        $id = (int) ($_POST['withdrawal_id'] ?? 0);
        if (!$id) respond('warning', 'Missing withdrawal id.');
        $ok = $authroller->CompleteWithdrawal($id);
        $ok
            ? respond('success', 'Withdrawal marked complete.')
            : respond('danger', 'Could not update that withdrawal. Please try again.');
        break;

    case 'reject_withdrawal':
        $id = (int) ($_POST['withdrawal_id'] ?? 0);
        if (!$id) respond('warning', 'Missing withdrawal id.');
        $ok = $authroller->RejectWithdrawal($id);
        $ok
            ? respond('info', 'Withdrawal rejected — funds remain in the investor\'s balance.')
            : respond('danger', 'Could not reject that withdrawal. Please try again.');
        break;

    /* --------------------------------------------------------------
     * Payment wallets
     * ------------------------------------------------------------ */

    case 'save_wallets':
        $networks = $_POST['wallet_network'] ?? [];
        $addresses = $_POST['wallet_address'] ?? [];
        $qrCodes = $_POST['wallet_qr'] ?? [];
        if (!$networks) {
            respond('warning', 'Nothing to save.');
        }
        foreach ($networks as $i => $network) {
            $network = trim($network);
            $address = trim($addresses[$i] ?? '');
            $qr      = trim($qrCodes[$i] ?? '');
            if ($network === '' || $address === '') continue;
            $authroller->SavePaymentWallet($network, $address, $qr);
        }
        respond('success', 'Wallet addresses updated.');
        break;

    case 'add_wallet':
        $network = trim($_POST['network'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $qr      = trim($_POST['qr_code_url'] ?? '');
        if (!$network || !$address) {
            respond('warning', 'Please enter both a network name and a wallet address.');
        }
        $ok = $authroller->SavePaymentWallet($network, $address, $qr);
        $ok
            ? respond('success', 'Wallet added.')
            : respond('danger', 'Could not add that wallet. Please try again.');
        break;

    case 'delete_wallet':
        $id = (int) ($_POST['wallet_id'] ?? 0);
        if (!$id) respond('warning', 'Missing wallet id.');
        $ok = $authroller->DeletePaymentWallet($id);
        $ok
            ? respond('success', 'Wallet removed.')
            : respond('danger', 'Could not remove that wallet. Please try again.');
        break;

    /* --------------------------------------------------------------
     * Fallback
     * ------------------------------------------------------------ */

    default:
        respond('warning', 'Unknown action.');
}
