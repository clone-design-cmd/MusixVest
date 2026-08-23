<?php
require_once __DIR__ . '/config/admin_session.php';
admin_require_login();
require_once __DIR__ . '/config/Authroller.php';

$authroller = new Authroller();

$valid_tabs = ['overview', 'offerings', 'users', 'deposits', 'withdrawals', 'payments', 'transactions'];
$tab = $_GET['tab'] ?? 'overview';
if (!in_array($tab, $valid_tabs, true)) $tab = 'overview';

/** On the Overview tab every section is shown at once; otherwise only the matching one is. */
function show_section($key, $tab) { return $tab === 'overview' || $tab === $key; }

$stats               = $authroller->Stats();
$offerings           = show_section('offerings', $tab) ? $authroller->AdminOfferings() : [];
$investors           = show_section('users', $tab) ? $authroller->AllInvestors() : [];
$pendingDeposits     = show_section('deposits', $tab) ? $authroller->PendingDeposits() : [];
$resolvedDeposits    = show_section('deposits', $tab) ? $authroller->ResolvedDeposits(15) : [];
$pendingWithdrawals  = show_section('withdrawals', $tab) ? $authroller->PendingWithdrawals() : [];
$resolvedWithdrawals = show_section('withdrawals', $tab) ? $authroller->ResolvedWithdrawals(15) : [];
$wallets             = show_section('payments', $tab) ? $authroller->PaymentWallets() : [];
$transactions        = show_section('transactions', $tab) ? $authroller->AllTransactions(30) : [];

$offering_status_badge = ['sale' => 'badge-success', 'auction' => 'badge-warning', 'soldout' => 'badge-danger'];
$offering_status_label = ['sale' => 'Active', 'auction' => 'Auction', 'soldout' => 'Sold out'];
$deposit_badge  = ['Confirmed' => 'badge-success', 'Pending' => 'badge-warning', 'Failed' => 'badge-danger'];
$withdraw_badge = ['Completed' => 'badge-success', 'Pending' => 'badge-warning', 'Rejected' => 'badge-danger'];

$page_title = "MusixVest Admin — Dashboard";
$active_tab = $tab;
$page_heading = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <?php include 'inc/admin-head.php'; ?>
</head>

<body class="bg-slate-50 text-slate-700"
      x-data="{ sidebarOpen: false, profileOpen: false }">

    <?php include 'inc/admin-sidebar.php'; ?>

    <div class="app-main min-h-screen flex flex-col">

        <?php include 'inc/admin-header.php'; ?>

        <main id="main" class="flex-1 p-4 sm:p-6 space-y-6">

            <?php if (!$authroller->isConnected()): ?>
            <div class="alert alert-danger" role="alert">
                The database isn't connected yet. Import <code>database/musixvest.sql</code> and configure <code>config/Controller.php</code> to bring the admin panel to life.
            </div>
            <?php endif; ?>

            <!-- ============================================================
                 Stat cards
                 ============================================================ -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="card p-4">
                    <p class="text-xs text-slate-500">Offerings</p>
                    <p class="text-xl font-bold text-slate-900 mt-1"><?php echo number_format($stats['total_offerings']); ?></p>
                    <p class="text-xs text-slate-400 mt-1"><?php echo number_format($stats['active_offerings']); ?> active</p>
                </div>
                <div class="card p-4">
                    <p class="text-xs text-slate-500">Registered investors</p>
                    <p class="text-xl font-bold text-slate-900 mt-1"><?php echo number_format($stats['registered_investors']); ?></p>
                    <p class="text-xs text-slate-400 mt-1"><?php echo number_format($stats['verified_users']); ?> verified</p>
                </div>
                <div class="card p-4">
                    <p class="text-xs text-slate-500">Capital raised</p>
                    <p class="text-xl font-bold text-slate-900 mt-1">$<?php echo number_format($stats['capital_raised'], 2); ?></p>
                    <p class="text-xs text-slate-400 mt-1"><?php echo number_format($stats['shares_sold']); ?> shares sold</p>
                </div>
                <div class="card p-4">
                    <p class="text-xs text-slate-500">Pending deposits</p>
                    <p class="text-xl font-bold text-slate-900 mt-1"><?php echo number_format($stats['pending_deposits']); ?></p>
                    <p class="text-xs text-slate-400 mt-1">$<?php echo number_format($stats['pending_deposit_amount'], 2); ?> awaiting review</p>
                </div>
            </div>

            <!-- ============================================================
                 Offerings
                 ============================================================ -->
            <?php if (show_section('offerings', $tab)): ?>
            <section id="offerings" class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-slate-900">Manage Offerings</h2>
                    <button type="button" data-add-offering data-modal-target="#offeringModal" class="btn btn-primary">+ Add Offering</button>
                </div>
                <div class="table-wrap">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs text-slate-400 border-b border-slate-100">
                                <th class="py-3">Offering</th>
                                <th class="py-3">Price</th>
                                <th class="py-3">Shares (sold / total)</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Featured</th>
                                <th class="py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <?php if (!$offerings): ?>
                            <tr><td class="py-6 text-center text-slate-400" colspan="6">No offerings yet — add the first one.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($offerings as $o): ?>
                            <tr>
                                <td class="py-3">
                                    <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($o['title']); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo htmlspecialchars($o['artist']); ?></p>
                                </td>
                                <td class="py-3">$<?php echo number_format((float) $o['price_per_share'], 2); ?></td>
                                <td class="py-3"><?php echo number_format($o['shares_sold']); ?> / <?php echo number_format((int) $o['total_shares']); ?></td>
                                <td class="py-3"><span class="badge <?php echo $offering_status_badge[$o['status']] ?? 'badge-warning'; ?>"><?php echo $offering_status_label[$o['status']] ?? htmlspecialchars($o['status']); ?></span></td>
                                <td class="py-3"><?php echo $o['featured'] ? '&#9733; Featured' : '—'; ?></td>
                                <td class="py-3 text-right whitespace-nowrap">
                                    <button type="button"
                                        class="btn btn-secondary"
                                        style="padding:.4rem .8rem"
                                        data-edit-offering
                                        data-modal-target="#offeringModal"
                                        data-id="<?php echo (int) $o['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($o['title']); ?>"
                                        data-artist="<?php echo htmlspecialchars($o['artist']); ?>"
                                        data-category="<?php echo htmlspecialchars($o['category'] ?? ''); ?>"
                                        data-description="<?php echo htmlspecialchars($o['description'] ?? ''); ?>"
                                        data-image="<?php echo htmlspecialchars($o['image_url'] ?? ''); ?>"
                                        data-price="<?php echo htmlspecialchars($o['price_per_share']); ?>"
                                        data-total-shares="<?php echo (int) $o['total_shares']; ?>"
                                        data-yield="<?php echo htmlspecialchars($o['yield_percent'] ?? ''); ?>"
                                        data-duration="<?php echo htmlspecialchars($o['duration_days'] ?? ''); ?>"
                                        data-status="<?php echo htmlspecialchars($o['status']); ?>"
                                        data-featured="<?php echo (int) $o['featured']; ?>"
                                        data-milestones="<?php echo htmlspecialchars(json_encode($authroller->Milestones($o['id']))); ?>"
                                    >Edit</button>
                                    <button type="button"
                                        class="btn btn-danger"
                                        style="padding:.4rem .8rem"
                                        data-delete-offering
                                        data-modal-target="#deleteOfferingModal"
                                        data-id="<?php echo (int) $o['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($o['title']); ?>"
                                    >Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- ============================================================
                 Investors
                 ============================================================ -->
            <?php if (show_section('users', $tab)): ?>
            <section id="users" class="card p-5">
                <h2 class="text-sm font-bold text-slate-900 mb-4">Investors</h2>
                <div class="table-wrap">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs text-slate-400 border-b border-slate-100">
                                <th class="py-3">Name</th>
                                <th class="py-3">Email</th>
                                <th class="py-3">Verified</th>
                                <th class="py-3">Shares held</th>
                                <th class="py-3">Balance</th>
                                <th class="py-3">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <?php if (!$investors): ?>
                            <tr><td class="py-6 text-center text-slate-400" colspan="6">No investors yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($investors as $u): ?>
                            <tr>
                                <td class="py-3 font-semibold text-slate-900"><?php echo htmlspecialchars(trim($u['first_name'] . ' ' . $u['last_name'])); ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td class="py-3"><span class="badge <?php echo $u['verified'] ? 'badge-success' : 'badge-warning'; ?>"><?php echo $u['verified'] ? 'Verified' : 'Unverified'; ?></span></td>
                                <td class="py-3"><?php echo number_format($u['holdings']); ?></td>
                                <td class="py-3">$<?php echo number_format($u['balance'], 2); ?></td>
                                <td class="py-3 text-slate-500"><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- ============================================================
                 Deposits
                 ============================================================ -->
            <?php if (show_section('deposits', $tab)): ?>
            <section id="deposits" class="card p-5">
                <h2 class="text-sm font-bold text-slate-900 mb-1">Pending Deposits</h2>
                <p class="text-xs text-slate-500 mb-4">Confirming a deposit makes the funds available to the investor immediately.</p>
                <div class="table-wrap mb-6">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs text-slate-400 border-b border-slate-100">
                                <th class="py-3">Investor</th>
                                <th class="py-3">Network</th>
                                <th class="py-3">Amount</th>
                                <th class="py-3">Tx hash</th>
                                <th class="py-3">Submitted</th>
                                <th class="py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <?php if (!$pendingDeposits): ?>
                            <tr><td class="py-6 text-center text-slate-400" colspan="6">Nothing pending — you're all caught up.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($pendingDeposits as $d): ?>
                            <tr>
                                <td class="py-3 font-semibold text-slate-900"><?php echo htmlspecialchars($d['investor_name']); ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($d['network']); ?></td>
                                <td class="py-3">$<?php echo number_format((float) $d['amount'], 2); ?></td>
                                <td class="py-3 font-mono text-xs"><?php echo htmlspecialchars(substr($d['tx_hash'], 0, 10)) . '…'; ?></td>
                                <td class="py-3 text-slate-500"><?php echo date('M j, g:ia', strtotime($d['created_at'])); ?></td>
                                <td class="py-3 text-right whitespace-nowrap">
                                    <button type="button" class="btn btn-primary" style="padding:.4rem .8rem" data-quick-action="confirm_deposit" data-id-field="deposit_id" data-id="<?php echo (int) $d['id']; ?>">Confirm</button>
                                    <button type="button" class="btn btn-secondary" style="padding:.4rem .8rem" data-quick-action="reject_deposit" data-id-field="deposit_id" data-id="<?php echo (int) $d['id']; ?>">Reject</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h3 class="text-sm font-bold text-slate-700 mb-3">Recently resolved</h3>
                <div class="table-wrap">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs text-slate-400 border-b border-slate-100">
                                <th class="py-3">Investor</th>
                                <th class="py-3">Network</th>
                                <th class="py-3">Amount</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <?php if (!$resolvedDeposits): ?>
                            <tr><td class="py-6 text-center text-slate-400" colspan="5">No history yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($resolvedDeposits as $d): ?>
                            <tr>
                                <td class="py-3"><?php echo htmlspecialchars($d['investor_name']); ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($d['network']); ?></td>
                                <td class="py-3">$<?php echo number_format((float) $d['amount'], 2); ?></td>
                                <td class="py-3"><span class="badge <?php echo $deposit_badge[$d['status']] ?? 'badge-warning'; ?>"><?php echo htmlspecialchars($d['status']); ?></span></td>
                                <td class="py-3 text-slate-500"><?php echo date('M j, Y', strtotime($d['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- ============================================================
                 Withdrawals
                 ============================================================ -->
            <?php if (show_section('withdrawals', $tab)): ?>
            <section id="withdrawals" class="card p-5">
                <h2 class="text-sm font-bold text-slate-900 mb-1">Pending Withdrawals</h2>
                <p class="text-xs text-slate-500 mb-4">Complete a withdrawal only after you've sent the funds off-platform. Rejecting one returns the amount to the investor's available balance.</p>
                <div class="table-wrap mb-6">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs text-slate-400 border-b border-slate-100">
                                <th class="py-3">Investor</th>
                                <th class="py-3">Network</th>
                                <th class="py-3">Amount</th>
                                <th class="py-3">Wallet</th>
                                <th class="py-3">Requested</th>
                                <th class="py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <?php if (!$pendingWithdrawals): ?>
                            <tr><td class="py-6 text-center text-slate-400" colspan="6">Nothing pending.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($pendingWithdrawals as $w): ?>
                            <tr>
                                <td class="py-3 font-semibold text-slate-900"><?php echo htmlspecialchars($w['investor_name']); ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($w['network']); ?></td>
                                <td class="py-3">$<?php echo number_format((float) $w['amount'], 2); ?></td>
                                <td class="py-3 font-mono text-xs"><?php echo htmlspecialchars(substr($w['wallet_address'], 0, 6) . '...' . substr($w['wallet_address'], -4)); ?></td>
                                <td class="py-3 text-slate-500"><?php echo date('M j, g:ia', strtotime($w['created_at'])); ?></td>
                                <td class="py-3 text-right whitespace-nowrap">
                                    <button type="button" class="btn btn-primary" style="padding:.4rem .8rem" data-quick-action="complete_withdrawal" data-id-field="withdrawal_id" data-id="<?php echo (int) $w['id']; ?>">Complete</button>
                                    <button type="button" class="btn btn-secondary" style="padding:.4rem .8rem" data-quick-action="reject_withdrawal" data-id-field="withdrawal_id" data-id="<?php echo (int) $w['id']; ?>">Reject</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h3 class="text-sm font-bold text-slate-700 mb-3">Recently resolved</h3>
                <div class="table-wrap">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs text-slate-400 border-b border-slate-100">
                                <th class="py-3">Investor</th>
                                <th class="py-3">Network</th>
                                <th class="py-3">Amount</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <?php if (!$resolvedWithdrawals): ?>
                            <tr><td class="py-6 text-center text-slate-400" colspan="5">No history yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($resolvedWithdrawals as $w): ?>
                            <tr>
                                <td class="py-3"><?php echo htmlspecialchars($w['investor_name']); ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($w['network']); ?></td>
                                <td class="py-3">$<?php echo number_format((float) $w['amount'], 2); ?></td>
                                <td class="py-3"><span class="badge <?php echo $withdraw_badge[$w['status']] ?? 'badge-warning'; ?>"><?php echo htmlspecialchars($w['status']); ?></span></td>
                                <td class="py-3 text-slate-500"><?php echo date('M j, Y', strtotime($w['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- ============================================================
                 Payment Settings (crypto wallets investors deposit to)
                 ============================================================ -->
            <?php if (show_section('payments', $tab)): ?>
            <section id="payments" class="card p-5">
                <h2 class="text-sm font-bold text-slate-900 mb-1">Payment Settings</h2>
                <p class="text-xs text-slate-500 mb-4">These addresses (and their QR codes) are exactly what investors see on the Deposit Funds page — edit an address here and it updates there immediately.</p>

                <form class="ajax-form" data-reload="true">
                    <input type="hidden" name="action" value="save_wallets">
                    <div class="space-y-4">
                        <?php foreach ($wallets as $w): ?>
                        <div class="rounded-lg border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-[auto_1fr_1fr] gap-4 items-start">
                            <img src="<?php echo htmlspecialchars($w['qr_code_url'] ?: 'https://api.qrserver.com/v1/create-qr-code/?size=96x96&data=' . urlencode($w['address'])); ?>" alt="QR code for <?php echo htmlspecialchars($w['network']); ?>" class="w-16 h-16 rounded border border-slate-200 object-cover">
                            <div class="field mb-0">
                                <label class="field-label">Network</label>
                                <input type="text" name="wallet_network[]" value="<?php echo htmlspecialchars($w['network']); ?>" class="input" readonly>
                            </div>
                            <div class="space-y-2">
                                <div class="field mb-0">
                                    <label class="field-label">Wallet address</label>
                                    <input type="text" name="wallet_address[]" value="<?php echo htmlspecialchars($w['address']); ?>" class="input font-mono text-xs">
                                </div>
                                <div class="field mb-0">
                                    <label class="field-label">QR code image URL</label>
                                    <input type="text" name="wallet_qr[]" value="<?php echo htmlspecialchars($w['qr_code_url'] ?? ''); ?>" class="input text-xs" placeholder="https://...">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (!$wallets): ?>
                        <p class="text-sm text-slate-400">No wallets configured yet — add one below.</p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary mt-5"><span class="btn-label">Save Changes</span></button>
                </form>

                <hr class="my-6 border-slate-100">

                <h3 class="text-sm font-bold text-slate-700 mb-3">Add a new network</h3>
                <form class="ajax-form flex flex-col sm:flex-row gap-3 items-start sm:items-end" data-reload="true">
                    <input type="hidden" name="action" value="add_wallet">
                    <div class="field flex-1 w-full mb-0">
                        <label class="field-label" for="new-network">Network name</label>
                        <input type="text" id="new-network" name="network" required class="input" placeholder="e.g. USDC (Polygon)">
                    </div>
                    <div class="field flex-1 w-full mb-0">
                        <label class="field-label" for="new-address">Wallet address</label>
                        <input type="text" id="new-address" name="address" required class="input" placeholder="Paste the deposit address">
                    </div>
                    <div class="field flex-1 w-full mb-0">
                        <label class="field-label" for="new-qr">QR code image URL</label>
                        <input type="text" id="new-qr" name="qr_code_url" class="input" placeholder="https:// (optional)">
                    </div>
                    <button type="submit" class="btn btn-secondary w-full sm:w-auto"><span class="btn-label">Add Network</span></button>
                </form>
            </section>
            <?php endif; ?>

            <!-- ============================================================
                 Transactions (platform-wide feed)
                 ============================================================ -->
            <?php if (show_section('transactions', $tab)): ?>
            <section id="transactions" class="card p-5">
                <h2 class="text-sm font-bold text-slate-900 mb-4">Recent Transactions</h2>
                <div class="table-wrap">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs text-slate-400 border-b border-slate-100">
                                <th class="py-3">Investor</th>
                                <th class="py-3">Description</th>
                                <th class="py-3">Type</th>
                                <th class="py-3">Amount</th>
                                <th class="py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <?php if (!$transactions): ?>
                            <tr><td class="py-6 text-center text-slate-400" colspan="5">No transactions yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="py-3 font-semibold text-slate-900"><?php echo htmlspecialchars($t['investor_name']); ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($t['description']); ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($t['type']); ?></td>
                                <td class="py-3 <?php echo $t['amount'] < 0 ? 'text-red-600' : 'text-emerald-600'; ?> font-semibold"><?php echo $t['amount'] < 0 ? '-' : '+'; ?>$<?php echo number_format(abs((float) $t['amount']), 2); ?></td>
                                <td class="py-3 text-slate-500"><?php echo date('M j, Y', strtotime($t['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

        </main>
    </div>

    <!-- ================================================================
         Offering Add/Edit modal (shared — admin-app.js swaps its mode)
         ================================================================ -->
    <div id="offeringModal" class="mv-modal hidden">
        <div class="mv-modal-backdrop" data-modal-close></div>
        <div class="mv-modal-panel max-w-2xl p-6 space-y-4">
            <h3 id="offeringModalTitle" class="text-base font-bold text-slate-900">Publish New Offering</h3>

            <form id="offeringForm" class="ajax-form space-y-4" data-reload="true">
                <input type="hidden" name="action" value="add_offering">
                <input type="hidden" name="offering_id" value="">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="field mb-0">
                        <label class="field-label" for="of-title">Song title</label>
                        <input type="text" id="of-title" name="title" required class="input">
                    </div>
                    <div class="field mb-0">
                        <label class="field-label" for="of-artist">Artist</label>
                        <input type="text" id="of-artist" name="artist" required class="input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="field mb-0">
                        <label class="field-label" for="of-category">Category</label>
                        <input type="text" id="of-category" name="category" class="input" placeholder="e.g. Single Catalog">
                    </div>
                    <div class="field mb-0">
                        <label class="field-label" for="of-image">Cover image URL</label>
                        <input type="text" id="of-image" name="image_url" class="input" placeholder="https://...">
                    </div>
                </div>

                <div class="field mb-0">
                    <label class="field-label" for="of-description">Description</label>
                    <textarea id="of-description" name="description" rows="3" class="input"></textarea>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="field mb-0">
                        <label class="field-label" for="of-price">Price / share</label>
                        <input type="number" id="of-price" name="price" min="0.01" step="0.01" required class="input">
                    </div>
                    <div class="field mb-0">
                        <label class="field-label" for="of-shares">Total shares</label>
                        <input type="number" id="of-shares" name="total_shares" min="1" required class="input">
                    </div>
                    <div class="field mb-0">
                        <label class="field-label" for="of-yield">Yield %</label>
                        <input type="number" id="of-yield" name="yield_percent" min="0" step="0.1" class="input">
                    </div>
                    <div class="field mb-0">
                        <label class="field-label" for="of-duration">Duration (days)</label>
                        <input type="number" id="of-duration" name="duration_days" min="1" class="input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                    <div class="field mb-0">
                        <label class="field-label" for="of-status">Status</label>
                        <select id="of-status" name="status" class="input">
                            <option value="sale">Active (on sale)</option>
                            <option value="auction">Auction</option>
                            <option value="soldout">Sold out</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 pb-2">
                        <input type="checkbox" name="featured" value="1" class="h-4 w-4 rounded border-slate-300 text-[#2D60C3]">
                        <span>Feature on landing page</span>
                    </label>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="field-label mb-0">Growth milestones</label>
                        <button type="button" data-add-milestone class="text-xs font-semibold text-[#2D60C3] hover:underline">+ Add milestone</button>
                    </div>
                    <div id="milestoneRows"></div>
                    <p class="text-xs text-slate-400">Days since purchase → cumulative % growth shown on the offering page.</p>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" data-modal-close class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary"><span class="btn-label">Save Offering</span></button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================================================================
         Delete offering confirmation modal
         ================================================================ -->
    <div id="deleteOfferingModal" class="mv-modal hidden">
        <div class="mv-modal-backdrop" data-modal-close></div>
        <div class="mv-modal-panel max-w-sm p-6 space-y-4">
            <div class="w-11 h-11 rounded-full bg-red-50 text-red-600 flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z"/></svg>
            </div>
            <div>
                <h3 class="text-[15px] font-bold text-slate-900">Delete "<span id="deleteOfferingName"></span>"?</h3>
                <p class="text-[12px] text-slate-500 mt-1">This also removes any investor holdings and history tied to this offering. This can't be undone.</p>
            </div>
            <form id="deleteOfferingForm" class="ajax-form flex justify-end gap-2 pt-2" data-reload="true">
                <input type="hidden" name="action" value="delete_offering">
                <input type="hidden" name="offering_id" value="">
                <button type="button" data-modal-close class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger"><span class="btn-label">Delete Offering</span></button>
            </form>
        </div>
    </div>

</body>
</html>
