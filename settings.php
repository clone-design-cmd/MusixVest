<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/Controller.php';

$controller = new Controller();
$userId = $_SESSION['user_id'] ?? 1; // demo fallback user for this prototype

$user = $controller->User($userId);
$balance = $controller->AccountBalance($userId);
$deposits = $controller->GetDeposits($userId);
$withdrawals = $controller->GetWithdrawals($userId);

// Wallets are admin-managed (see admin/config/Authroller.php::SavePaymentWallet()).
// Fall back to a small hardcoded set only if the table is empty / DB isn't connected yet,
// so this page still renders something sensible on a fresh checkout.
$wallets = $controller->PaymentWallets();
if (!$wallets) {
    $wallets = [
        ['network' => 'USDT (TRC20)', 'address' => 'TQn9Y2khEsLMWD1c3v7ZmXk9Z8Rj4pQhAB', 'qr_code_url' => null],
        ['network' => 'USDT (ERC20)', 'address' => '0x8f3a2C1b6E4d9A0F7c5B2e8D1a4C6f9E3b7D5a2C', 'qr_code_url' => null],
        ['network' => 'BTC',          'address' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh', 'qr_code_url' => null],
        ['network' => 'ETH',          'address' => '0x4a1B7c2E9f6D3a8C5e0F2b7A4d9C1e6B3f8A2d5E', 'qr_code_url' => null],
    ];
}

$deposit_networks = array_column($wallets, 'network');
$deposit_addresses = array_combine($deposit_networks, array_column($wallets, 'address'));
$deposit_qrcodes = array_combine(
    $deposit_networks,
    array_map(function ($w) {
        // Fall back to generating a QR on the fly if the admin hasn't set one explicitly.
        return $w['qr_code_url'] ?: 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($w['address']);
    }, $wallets)
);

$deposit_badge = ['Confirmed' => 'badge-success', 'Pending' => 'badge-warning', 'Failed' => 'badge-danger'];
$withdraw_badge = ['Completed' => 'badge-success', 'Pending' => 'badge-warning', 'Rejected' => 'badge-danger'];

$page_title = "MusixVest - Settings";
$active = 'settings';
$page_heading = 'Settings';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <?php include 'inc/head.php'; ?>
</head>

<body class="bg-slate-50 text-slate-700"
      x-data="{ sidebarOpen: false, profileOpen: false }">

    <?php include 'inc/sidebar-app.php'; ?>

    <div class="app-main min-h-screen flex flex-col">

        <?php include 'inc/header-app.php'; ?>

        <main id="main" class="flex-1 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="max-w-4xl mx-auto space-y-6">

                <div class="mb-1">
                    <p class="text-[#2d60c3] text-xs font-bold uppercase tracking-wider">Account</p>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1">Settings</h1>
                </div>

                <?php if (!$user): ?>
                <section class="card p-8 text-center text-sm text-slate-500">
                    We couldn't load your account. Please check the database connection and try again.
                </section>
                <?php else: ?>

                <!-- Profile -->
                <section class="card p-5 sm:p-6">
                    <h2 class="text-sm font-bold text-slate-900 mb-4">Profile</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-400">Name</dt>
                            <dd class="font-semibold text-slate-800 mt-0.5"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Email</dt>
                            <dd class="font-semibold text-slate-800 mt-0.5"><?php echo htmlspecialchars($user['email']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Verification status</dt>
                            <dd class="mt-0.5"><span class="badge <?php echo !empty($user['verified']) ? 'badge-success' : 'badge-warning'; ?>"><?php echo !empty($user['verified']) ? 'Verified' : 'Unverified'; ?></span></dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Member since</dt>
                            <dd class="font-semibold text-slate-800 mt-0.5"><?php echo htmlspecialchars(date('M j, Y', strtotime($user['created_at']))); ?></dd>
                        </div>
                    </dl>
                </section>

                <!-- Deposit Funds -->
                <section id="payments" class="card p-5 sm:p-6"
                         x-data="{
                             depositNetwork: <?php echo htmlspecialchars(json_encode($deposit_networks[0] ?? ''), ENT_QUOTES, 'UTF-8'); ?>,
                             depositAddresses: <?php echo htmlspecialchars(json_encode($deposit_addresses), ENT_QUOTES, 'UTF-8'); ?>,
                             depositQrCodes: <?php echo htmlspecialchars(json_encode($deposit_qrcodes), ENT_QUOTES, 'UTF-8'); ?>
                         }">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-sm font-bold text-slate-900">Deposit Funds</h2>
                        <span class="text-sm font-semibold text-slate-500">Balance: <span>$<?php echo number_format($balance, 2); ?></span></span>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">Deposits are accepted via crypto only, to avoid the high fees charged by card and bank payment processors.</p>

                    <div class="field mb-4 max-w-xs">
                        <label class="field-label" for="deposit-network">Network / Asset</label>
                        <select id="deposit-network" name="network" x-model="depositNetwork" class="input">
                            <?php foreach ($deposit_networks as $net): ?>
                                <option><?php echo htmlspecialchars($net); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 mb-6 flex flex-col sm:flex-row gap-4 items-start">
                        <img :src="depositQrCodes[depositNetwork]" :alt="'QR code for ' + depositNetwork" class="w-28 h-28 rounded-md border border-slate-200 bg-white p-1 shrink-0">
                        <div class="min-w-0">
                            <p class="text-xs text-slate-500 mb-1">Send your deposit to this address</p>
                            <p class="font-mono text-sm font-semibold text-slate-900 break-all" x-text="depositAddresses[depositNetwork]"></p>
                            <p class="text-xs text-slate-400 mt-2">Only send <span x-text="depositNetwork"></span> to this address, or scan the QR code. Deposits sent on the wrong network may be lost.</p>
                        </div>
                    </div>

                    <form class="ajax-form flex flex-col sm:flex-row gap-3 items-start sm:items-end mb-6" data-reload="true">
                        <input type="hidden" name="action" value="add_deposit">
                        <input type="hidden" name="network" :value="depositNetwork">

                        <div class="field flex-1 w-full">
                            <label class="field-label" for="deposit-amount">Amount Sent</label>
                            <input type="number" id="deposit-amount" name="amount" min="1" step="0.01" required class="input" placeholder="$0.00">
                        </div>
                        <div class="field flex-1 w-full">
                            <label class="field-label" for="deposit-tx">Transaction Hash</label>
                            <input type="text" id="deposit-tx" name="tx_hash" required class="input" placeholder="Paste your transaction hash">
                        </div>
                        <button type="submit" class="btn btn-primary w-full sm:w-auto"><span class="btn-label">Notify of Deposit</span></button>
                    </form>

                    <h3 class="text-sm font-bold text-slate-700 mb-3">Deposit history</h3>
                    <div class="table-wrap">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs uppercase tracking-wide text-slate-400 border-b border-slate-100">
                                    <th class="py-3">Requested</th>
                                    <th class="py-3">Amount</th>
                                    <th class="py-3">Network</th>
                                    <th class="py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <?php if (!$deposits): ?>
                                <tr><td class="py-6 text-center text-slate-400" colspan="4">No deposits yet.</td></tr>
                                <?php else: ?>
                                <?php foreach ($deposits as $d): ?>
                                <tr>
                                    <td class="py-3"><?php echo htmlspecialchars(date('M j, Y', strtotime($d['created_at']))); ?></td>
                                    <td>$<?php echo number_format((float) $d['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($d['network']); ?></td>
                                    <td><span class="badge <?php echo $deposit_badge[$d['status']] ?? 'badge-warning'; ?>"><?php echo htmlspecialchars($d['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Withdraw Funds -->
                <section id="withdraw" class="card p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-sm font-bold text-slate-900">Withdraw Funds</h2>
                        <span class="text-sm font-semibold text-slate-500">Balance: <span>$<?php echo number_format($balance, 2); ?></span></span>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">Withdrawals are processed via crypto only.</p>
                    <p class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 mb-4">A <strong>5% fee</strong> applies to all withdrawals.</p>

                    <form class="ajax-form flex flex-col sm:flex-row gap-3 items-start sm:items-end mb-6" data-reload="true">
                        <input type="hidden" name="action" value="add_withdrawal">

                        <div class="field flex-1 w-full">
                            <label class="field-label" for="withdraw-amount">Amount</label>
                            <input type="number" id="withdraw-amount" name="amount" min="1" step="0.01" max="<?php echo number_format($balance, 2, '.', ''); ?>" required class="input" placeholder="$0.00">
                        </div>
                        <div class="field flex-1 w-full">
                            <label class="field-label" for="withdraw-network">Blockchain</label>
                            <select id="withdraw-network" name="network" required class="input">
                                <option value="" disabled selected>Select network</option>
                                <?php foreach ($deposit_networks as $net): ?>
                                    <option><?php echo htmlspecialchars($net); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field flex-1 w-full">
                            <label class="field-label" for="withdraw-wallet">Wallet Address</label>
                            <input type="text" id="withdraw-wallet" name="wallet" required class="input" placeholder="Enter your wallet address">
                        </div>
                        <button type="submit" class="btn btn-primary w-full sm:w-auto"><span class="btn-label">Request Withdrawal</span></button>
                    </form>

                    <h3 class="text-sm font-bold text-slate-700 mb-3">Withdrawal history</h3>
                    <div class="table-wrap">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs uppercase tracking-wide text-slate-400 border-b border-slate-100">
                                    <th class="py-3">Requested</th>
                                    <th class="py-3">Amount</th>
                                    <th class="py-3">Fee (5%)</th>
                                    <th class="py-3">Network</th>
                                    <th class="py-3">Wallet</th>
                                    <th class="py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <?php if (!$withdrawals): ?>
                                <tr><td class="py-6 text-center text-slate-400" colspan="6">No withdrawal requests yet.</td></tr>
                                <?php else: ?>
                                <?php foreach ($withdrawals as $w): ?>
                                <tr>
                                    <td class="py-3"><?php echo htmlspecialchars(date('M j, Y', strtotime($w['created_at']))); ?></td>
                                    <td>$<?php echo number_format((float) $w['amount'], 2); ?></td>
                                    <td>$<?php echo number_format((float) $w['fee'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($w['network']); ?></td>
                                    <td class="font-mono text-xs"><?php echo htmlspecialchars(substr($w['wallet_address'], 0, 6) . '...' . substr($w['wallet_address'], -4)); ?></td>
                                    <td><span class="badge <?php echo $withdraw_badge[$w['status']] ?? 'badge-warning'; ?>"><?php echo htmlspecialchars($w['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <?php endif; ?>

            </div>
        </main>

        <footer class="bg-[#3478bd] text-white mt-auto">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-9">
                <p class="text-xs leading-relaxed text-white/85">
                    Manage your account, review funding activity, and keep track of your investment portfolio.
                </p>
            </div>
        </footer>
    </div>

</body>
</html>
