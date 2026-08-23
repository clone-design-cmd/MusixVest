<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/Controller.php';

$controller = new Controller();
$userId = $_SESSION['user_id'] ?? 1; // demo fallback user for this prototype
$available_balance = $controller->AccountBalance($userId);

$songId = (int) ($_GET['id'] ?? 0);
$offering = $songId ? $controller->Offering($songId) : null;

$allocated = $offering ? $controller->SharesAllocated($offering['id']) : 0;
$totalShares = $offering ? max(1, (int) $offering['total_shares']) : 1;
$available = $offering ? max(0, (int) $offering['total_shares'] - $allocated) : 0;
$allocatedPct = $offering ? (int) round(($allocated / $totalShares) * 100) : 0;
$milestones = $offering ? $controller->Milestones($offering['id']) : [];
$isActive = $offering && $offering['status'] !== 'soldout';
$royaltyRate = $offering && $offering['yield_percent'] !== null
    ? number_format((float) $offering['yield_percent'], 1) . '% APR (illustrative)'
    : 'VIP auction — bidding';

$page_title = $offering ? "MusixVest - " . $offering['title'] : "MusixVest - Offering Not Found";
$active = 'offerings';
$page_heading = 'SongShare Details';
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
            <div class="max-w-4xl mx-auto">

                <a href="offerings.php" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-900 mb-5">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back to Open Offerings
                </a>

                <?php if (!$offering): ?>

                <div class="card p-8 text-center text-sm text-slate-500">
                    We couldn't find that offering. It may have been removed, or the database isn't connected.
                </div>

                <?php else: ?>

                <div class="card p-6 sm:p-8 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-6">
                        <div class="w-full sm:w-40 h-40 rounded-xl overflow-hidden shrink-0 bg-gradient-to-br from-[#2d60c3] to-[#0a2a5e]">
                            <?php if (!empty($offering['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($offering['image_url']); ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-white text-5xl" aria-hidden="true">&#127925;</div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[#2d60c3] text-xs font-bold uppercase tracking-wider">SongShare Offering</p>
                                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1"><?php echo htmlspecialchars($offering['title']); ?></h1>
                                    <p class="text-sm text-slate-500 mt-1"><?php echo htmlspecialchars($offering['artist']); ?></p>
                                </div>
                                <span class="badge <?php echo $isActive ? 'badge-success' : 'badge-danger'; ?>"><?php echo $isActive ? 'Active' : 'Sold out'; ?></span>
                            </div>
                            <?php if (!empty($offering['description'])): ?>
                            <p class="text-sm text-slate-600 leading-relaxed mt-4"><?php echo htmlspecialchars($offering['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-7 pt-6 border-t border-slate-100">
                        <div><p class="text-xs text-slate-500">Price / share</p><p class="font-bold text-slate-900 mt-1">$<?php echo number_format((float) $offering['price_per_share'], 2); ?></p></div>
                        <div><p class="text-xs text-slate-500">Shares available</p><p class="font-bold text-slate-900 mt-1"><?php echo number_format($available); ?> / <?php echo number_format((int) $offering['total_shares']); ?></p></div>
                        <div><p class="text-xs text-slate-500">Royalty rate</p><p class="font-bold text-slate-900 mt-1"><?php echo htmlspecialchars($royaltyRate); ?></p></div>
                        <div><p class="text-xs text-slate-500">Investment term</p><p class="font-bold text-slate-900 mt-1"><?php echo $offering['duration_days'] ? (int) $offering['duration_days'] . ' days' : '—'; ?></p></div>
                    </div>

                    <div class="mt-5">
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-[#2d60c3]" style="width:<?php echo $allocatedPct; ?>%"></div></div>
                        <p class="text-xs text-slate-500 mt-2"><?php echo $allocatedPct; ?>% of shares allocated</p>
                    </div>

                    <?php if ($isActive && $available > 0): ?>
                    <form class="ajax-form mt-6" data-reload="true">
                        <input type="hidden" name="action" value="buy_offering">
                        <input type="hidden" name="song_id" value="<?php echo (int) $offering['id']; ?>">
                        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                            <div class="field flex-1 w-full sm:max-w-[160px]">
                                <label class="field-label" for="buy-shares">Shares to buy</label>
                                <input type="number" id="buy-shares" name="shares" min="1" max="<?php echo $available; ?>" value="1" required class="input">
                            </div>
                            <button type="submit" class="btn btn-primary w-full sm:w-auto"><span class="btn-label">Invest in This SongShare</span></button>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Available balance: <span class="font-semibold text-slate-700">$<?php echo number_format($available_balance, 2); ?></span></p>
                    </form>
                    <?php else: ?>
                    <button disabled class="btn btn-secondary w-full mt-6">Sold Out</button>
                    <?php endif; ?>
                </div>

                <div class="card p-6 sm:p-8">
                    <h2 class="text-sm font-bold text-slate-900 mb-1">Projected Value Growth</h2>
                    <p class="text-xs text-slate-500 mb-5">Illustrative value increase set by MusixVest for this offering, shown at set milestones over the investment term. Actual returns depend on real royalty performance and are not guaranteed.</p>

                    <?php if (!$milestones): ?>
                    <p class="text-sm text-slate-400">No projection milestones have been set for this offering yet.</p>
                    <?php else: ?>
                    <div class="table-wrap">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs uppercase tracking-wide text-slate-400 border-b border-slate-100">
                                    <th class="py-3">Days Held</th>
                                    <th class="py-3">Projected Value Increase</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <?php foreach ($milestones as $m): ?>
                                <tr class="border-b border-slate-50">
                                    <td class="py-3">Day <?php echo (int) $m['days']; ?></td>
                                    <td class="py-3 font-semibold text-emerald-600">+<?php echo rtrim(rtrim(number_format((float) $m['pct'], 2), '0'), '.'); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-4">Milestones are set by MusixVest admins per offering and may be updated at any time.</p>
                    <?php endif; ?>
                </div>

                <?php endif; ?>

            </div>
        </main>

        <?php include 'inc/footer-app.php'; ?>
    </div>

</body>
</html>
