<?php
/**
 * config/request.php
 * ---------------------------------------------------------------------
 * Single endpoint every form on the site posts to (see assets/js/app.js).
 * Reads $_POST['action'], calls the matching Controller method, and
 * always replies with JSON: { status, message, data }.
 *
 * status is one of: success | danger | warning | info — the front-end
 * toast picks its color from this value.
 *
 * NOTE (prototype scope): there's no full login-gate on the app pages
 * yet, so signed-in actions fall back to the seeded demo user (id 1,
 * James Williams) when no session exists, matching the "Hi James"
 * mockup used throughout the dashboard. Wire in a real auth guard
 * before shipping this anywhere near production.
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/Controller.php';

header('Content-Type: application/json');

$controller = new Controller();
$action     = $_POST['action'] ?? '';
$userId     = $_SESSION['user_id'] ?? 1;

function respond($status, $message, $data = [])
{
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

if (!$controller->isConnected() && !in_array($action, ['newsletter_signup'], true)) {
    respond('warning', 'The database isn\'t connected yet, so this action can\'t be saved right now. Import database/musixvest.sql and configure config/Controller.php to enable it.');
}

switch ($action) {

    /* --------------------------------------------------------------
     * Auth
     * ------------------------------------------------------------ */

    case 'register':
        $first = trim($_POST['first_name'] ?? '');
        $last  = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!$first || !$last || !$email || !$pass) {
            respond('warning', 'Please fill in every field.');
        }
        if (strlen($pass) < 8) {
            respond('warning', 'Password must be at least 8 characters.');
        }
        if ($pass !== $confirm) {
            respond('warning', 'Passwords do not match.');
        }
        if ($controller->User(null, $email)) {
            respond('danger', 'An account with that email already exists.');
        }

        $newId = $controller->AddUser([
            'first_name' => $first,
            'last_name'  => $last,
            'email'      => $email,
            'password'   => $pass,
        ]);

        if (!$newId) {
            respond('danger', 'Could not create your account. Please try again.');
        }

        $_SESSION['user_id']         = $newId;
        $_SESSION['user_first_name'] = $first;
        $_SESSION['user_last_name']  = $last;

        respond('success', 'Account created! Let\'s verify your identity next.');
        break;

    case 'login':
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        $user = $controller->Authenticate($email, $pass);
        if (!$user) {
            respond('danger', 'Incorrect email or password.');
        }

        $_SESSION['user_id']         = $user['id'];
        $_SESSION['user_first_name'] = $user['first_name'];
        $_SESSION['user_last_name']  = $user['last_name'];

        respond('success', 'Welcome back, ' . $user['first_name'] . '!');
        break;

    case 'logout':
        $_SESSION = [];
        session_destroy();
        respond('success', 'You have been logged out.');
        break;

    /* --------------------------------------------------------------
     * Identity verification (KYC)
     * ------------------------------------------------------------ */

    case 'verify_identity':
        $ok = $controller->SaveVerification($userId, [
            'citizenship_status' => $_POST['citizenship'] ?? null,
            'dob'                => $_POST['dob'] ?? null,
            'ssn'                => $_POST['ssn'] ?? null,
            'address1'           => $_POST['address1'] ?? null,
            'address2'           => $_POST['address2'] ?? null,
            'city'               => $_POST['city'] ?? null,
            'state'              => $_POST['state'] ?? null,
            'country'            => $_POST['country'] ?? null,
            'zip'                => $_POST['zip'] ?? null,
            'mobile'             => $_POST['mobile'] ?? null,
        ]);

        $ok
            ? respond('success', 'Identity verified. Taking you to your dashboard...')
            : respond('danger', 'We couldn\'t save your verification details. Please try again.');
        break;

    /* --------------------------------------------------------------
     * Account settings
     * ------------------------------------------------------------ */

    case 'update_account':
        $ok = $controller->UpdateUser($userId, [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name'] ?? ''),
            'email'      => trim($_POST['email'] ?? ''),
            'phone'      => $_POST['phone'] ?? null,
            'country'    => $_POST['country'] ?? null,
        ]);

        if ($ok) {
            $_SESSION['user_first_name'] = trim($_POST['first_name'] ?? ($_SESSION['user_first_name'] ?? ''));
        }

        $ok
            ? respond('success', 'Your account details have been updated.')
            : respond('danger', 'Could not update your account. Please try again.');
        break;

    case 'change_password':
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!$current || !$new) {
            respond('warning', 'Please fill in every field.');
        }
        if ($new !== $confirm) {
            respond('warning', 'New passwords do not match.');
        }
        if (strlen($new) < 8) {
            respond('warning', 'New password must be at least 8 characters.');
        }

        $ok = $controller->ChangePassword($userId, $current, $new);
        $ok
            ? respond('success', 'Your password has been updated.')
            : respond('danger', 'Current password is incorrect.');
        break;

    case 'update_preferences':
        $type = $_POST['preferences_type'] ?? '';

        if ($type === 'notifications') {
            $ok = $controller->UpdateUser($userId, [
                'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                'offering_alerts'     => isset($_POST['offering_alerts']) ? 1 : 0,
                'royalty_alerts'      => isset($_POST['royalty_alerts']) ? 1 : 0,
            ]);
        } else {
            $ok = $controller->UpdateUser($userId, [
                'preferred_offering_type' => $_POST['preferred_offering_type'] ?? null,
                'investment_range'        => $_POST['investment_range'] ?? null,
                'autobuy'                 => isset($_POST['autobuy']) && $_POST['autobuy'] === '1' ? 1 : 0,
            ]);
        }

        $ok
            ? respond('success', 'Preferences saved.')
            : respond('danger', 'Could not save your preferences. Please try again.');
        break;

    /* --------------------------------------------------------------
     * Payment methods
     * ------------------------------------------------------------ */

    case 'add_payment_method':
        $number = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
        if (strlen($number) < 12) {
            respond('warning', 'Please enter a valid card number.');
        }
        if (empty($_POST['expiry_month']) || empty($_POST['expiry_year']) || empty($_POST['cvc'])) {
            respond('warning', 'Please complete the expiry date and CVC.');
        }

        $ok = $controller->AddPaymentMethod($userId, [
            'card_brand'   => $_POST['card_brand'] ?? 'Card',
            'card_number'  => $number,
            'expiry_month' => $_POST['expiry_month'],
            'expiry_year'  => $_POST['expiry_year'],
            'is_default'   => isset($_POST['is_default']) ? 1 : 0,
        ]);

        $ok
            ? respond('success', 'Payment method added.')
            : respond('danger', 'Could not add that payment method. Please try again.');
        break;

    case 'delete_payment_method':
        $id = $_POST['id'] ?? null;
        if (!$id) {
            respond('warning', 'Missing payment method id.');
        }
        $ok = $controller->DeletePaymentMethod($id, $userId);
        $ok
            ? respond('success', 'Payment method removed.')
            : respond('danger', 'Could not remove that payment method.');
        break;

    /* --------------------------------------------------------------
     * Deposits & withdrawals
     * ------------------------------------------------------------ */

    case 'add_deposit':
        $amount = (float) ($_POST['amount'] ?? 0);
        $txHash = trim($_POST['tx_hash'] ?? '');
        $network = $_POST['network'] ?? '';

        if ($amount <= 0) {
            respond('warning', 'Please enter a valid deposit amount.');
        }
        if (!$txHash) {
            respond('warning', 'Please paste your transaction hash.');
        }

        $ok = $controller->AddDeposit($userId, [
            'network' => $network,
            'amount'  => $amount,
            'tx_hash' => $txHash,
        ]);

        $ok
            ? respond('success', 'Thanks — we\'ll confirm your deposit once it\'s verified on-chain.')
            : respond('danger', 'Could not record your deposit. Please try again.');
        break;

    case 'add_withdrawal':
        $amount = (float) ($_POST['amount'] ?? 0);
        $network = $_POST['network'] ?? '';
        $wallet = trim($_POST['wallet'] ?? '');

        if ($amount <= 0) {
            respond('warning', 'Please enter a valid withdrawal amount.');
        }
        if (!$network) {
            respond('warning', 'Please select a network.');
        }
        if (!$wallet) {
            respond('warning', 'Please enter a wallet address.');
        }

        $result = $controller->AddWithdrawal($userId, [
            'network' => $network,
            'amount'  => $amount,
            'wallet'  => $wallet,
        ]);

        if ($result === true) {
            respond('success', 'Withdrawal requested — a 5% fee applies and it\'s now pending processing.');
        } elseif ($result === 'insufficient_balance') {
            $available = $controller->AccountBalance($userId);
            respond('danger', 'Insufficient balance — your available balance is $' . number_format($available, 2) . ', which doesn\'t cover this amount plus the 5% fee.');
        } else {
            respond('danger', 'Could not request that withdrawal. Please try again.');
        }
        break;

    /* --------------------------------------------------------------
     * Favorites & investing
     * ------------------------------------------------------------ */

    case 'toggle_favorite':
        $songId = $_POST['song_id'] ?? null;
        if (!$songId) {
            respond('warning', 'Missing offering id.');
        }
        $result = $controller->ToggleFavorite($userId, $songId);

        if ($result === 'added') {
            respond('success', 'Added to your favorites.', ['state' => 'added']);
        } elseif ($result === 'removed') {
            respond('info', 'Removed from your favorites.', ['state' => 'removed']);
        } else {
            respond('danger', 'Could not update your favorites.');
        }
        break;

    case 'buy_offering':
        $songId = $_POST['song_id'] ?? null;
        $shares = (int) ($_POST['shares'] ?? 0);

        if (!$songId || $shares <= 0) {
            respond('warning', 'Please choose a valid number of shares.');
        }

        $result = $controller->AddInvestment($userId, $songId, $shares);

        if ($result === true) {
            respond('success', 'Purchase complete — SongShares added to your portfolio.');
        } elseif ($result === 'insufficient_balance') {
            $available = $controller->AccountBalance($userId);
            respond('danger', 'Insufficient balance — your available balance is $' . number_format($available, 2) . '. Please deposit more funds and try again.');
        } else {
            respond('danger', 'Could not complete that purchase. Please try again.');
        }
        break;

    /* --------------------------------------------------------------
     * Misc
     * ------------------------------------------------------------ */

    case 'newsletter_signup':
        // Mockup-only: the footer newsletter form doesn't have a backing
        // table in this prototype, so just acknowledge the submission.
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if (!$name || !$email) {
            respond('warning', 'Please enter your name and email.');
        }
        respond('success', 'You\'re on the list — thanks for subscribing!');
        break;

    case 'contact_message':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$name || !$email || !$message) {
            respond('warning', 'Please fill in your name, email, and message.');
        }

        $ok = $controller->AddContactMessage([
            'name'    => $name,
            'email'   => $email,
            'subject' => $subject,
            'message' => $message,
        ]);

        $ok
            ? respond('success', 'Thanks — your message has been sent. We\'ll get back to you soon.')
            : respond('danger', 'Could not send your message. Please try again.');
        break;

    default:
        respond('warning', 'Unknown request.');
}
