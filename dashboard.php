<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/Controller.php';

$controller = new Controller();
$userId = $_SESSION['user_id'] ?? 1; // demo fallback user for this prototype
$user_first_name = $_SESSION['user_first_name'] ?? 'James';

$holdings = $controller->getSongShares($userId);
$portfolio_value = array_sum(array_column($holdings, 'value'));
$shares_owned = array_sum(array_column($holdings, 'shares'));
$offerings_count = count($holdings);

$transactions = $controller->getTransactions($userId);
$total_invested = 0;
foreach ($transactions as $t) {
    if ($t['type'] === 'Investment') {
        $total_invested += abs((float) $t['amount']);
    }
}

$available_balance = $controller->AccountBalance($userId);

$page_title = "MusixVest - Dashboard";
$active = 'dashboard';
$page_heading = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <?php include 'inc/head.php'; ?>
</head>
<body class="bg-slate-50 text-slate-700" x-data="{ sidebarOpen: false, profileOpen: false }">

<?php include 'inc/sidebar-app.php'; ?>

<div class="app-main min-h-screen flex flex-col">

    <?php include 'inc/header-app.php'; ?>

    <main id="main" class="flex-1 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <div class="max-w-7xl mx-auto">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-7">
                <div>
                    <p class="text-[#2d60c3] text-xs font-bold uppercase tracking-wider">Investor Portal</p>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1">Dashboard</h1>
                    <p class="text-sm text-slate-500 mt-2">Welcome back, <?php echo htmlspecialchars($user_first_name); ?>. Here is your latest portfolio overview.</p>
                </div>
                <a href="offerings.php" class="btn btn-primary">Explore Offerings</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <div class="card p-5">
                    <p class="text-xs text-slate-500">Portfolio Value</p>
                    <p class="text-2xl font-bold text-slate-900 mt-2">$<?php echo number_format($portfolio_value, 2); ?></p>
                    <p class="text-xs text-slate-500 mt-2">Current holding value</p>
                </div>
                <div class="card p-5">
                    <p class="text-xs text-slate-500">SongShares Owned</p>
                    <p class="text-2xl font-bold text-slate-900 mt-2"><?php echo number_format($shares_owned); ?></p>
                    <p class="text-xs text-slate-500 mt-2">Across <?php echo (int) $offerings_count; ?> offerings</p>
                </div>
                <div class="card p-5">
                    <p class="text-xs text-slate-500">Total Invested</p>
                    <p class="text-2xl font-bold text-slate-900 mt-2">$<?php echo number_format($total_invested, 2); ?></p>
                    <p class="text-xs text-slate-500 mt-2">Lifetime purchases</p>
                </div>
                <div class="card p-5">
                    <p class="text-xs text-slate-500">Available Balance</p>
                    <p class="text-2xl font-bold text-slate-900 mt-2">$<?php echo number_format($available_balance, 2); ?></p>
                    <div class="flex items-center gap-3 mt-2">
                        <a href="settings.php#payments" class="text-xs text-[#2d60c3] font-semibold hover:underline">Deposit funds &rarr;</a>
                        <a href="settings.php#withdraw" class="text-xs text-[#2d60c3] font-semibold hover:underline">Withdraw funds &rarr;</a>
                    </div>
                </div>
            </div>

            <section id="investments" class="card p-5 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">SongShares Owned</h2>
                        <p class="text-xs text-slate-500 mt-1">Your current music royalty holdings.</p>
                    </div>
                    <a href="offerings.php" class="text-xs font-semibold text-[#2d60c3]">Browse more &rarr;</a>
                </div>
                <div class="table-wrap">
                    <?php if (!$holdings): ?>
                    <p class="text-sm text-slate-400 py-6">You don't own any SongShares yet.</p>
                    <?php else: ?>
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-slate-400 border-b border-slate-100">
                                <th class="py-3">Offering</th>
                                <th class="py-3">Shares</th>
                                <th class="py-3">Value</th>
                                <th class="py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php foreach ($holdings as $h): ?>
                            <tr class="border-b border-slate-50">
                                <td class="py-3 font-semibold"><?php echo htmlspecialchars($h['song_title']); ?></td>
                                <td><?php echo (int) $h['shares']; ?></td>
                                <td>$<?php echo number_format((float) $h['value'], 2); ?></td>
                                <td><span class="badge <?php echo $h['status'] === 'Active' ? 'badge-success' : 'badge-warning'; ?>"><?php echo htmlspecialchars($h['status']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </section>

            <section id="transactions" class="card p-5">
                <div class="mb-4">
                    <h2 class="text-sm font-bold text-slate-900">Recent Transactions</h2>
                    <p class="text-xs text-slate-500 mt-1">Latest activity on your account.</p>
                </div>
                <div class="table-wrap">
                    <?php if (!$transactions): ?>
                    <p class="text-sm text-slate-400 py-6">No transactions yet.</p>
                    <?php else: ?>
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-slate-400 border-b border-slate-100">
                                <th class="py-3">Date</th>
                                <th class="py-3">Description</th>
                                <th class="py-3">Type</th>
                                <th class="py-3">Amount</th>
                                <th class="py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php foreach ($transactions as $t):
                                $amount = (float) $t['amount'];
                            ?>
                            <tr class="border-b border-slate-50">
                                <td class="py-3"><?php echo htmlspecialchars(date('M j, Y', strtotime($t['created_at']))); ?></td>
                                <td><?php echo htmlspecialchars($t['description']); ?></td>
                                <td><?php echo htmlspecialchars($t['type']); ?></td>
                                <td><?php echo ($amount >= 0 ? '+' : '-') . '$' . number_format(abs($amount), 2); ?></td>
                                <td><span class="badge <?php echo $t['status'] === 'Completed' ? 'badge-success' : 'badge-warning'; ?>"><?php echo htmlspecialchars($t['status']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </main>

    <?php include 'inc/footer-app.php'; ?>
</div>
</body>
</html>
