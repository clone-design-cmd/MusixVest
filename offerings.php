<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/Controller.php';

$controller = new Controller();

$db_offerings = $controller->Offerings();
$offerings = array_map(function ($row) use ($controller) {
    $allocated = $controller->SharesAllocated($row['id']);
    $available = max(0, (int) $row['total_shares'] - $allocated);
    $totalShares = max(1, (int) $row['total_shares']);
    return [
        'id'              => $row['id'],
        'title'           => $row['title'],
        'artist'          => $row['artist'],
        'status'          => $row['status'] === 'soldout' ? 'Sold out' : 'Active',
        'price'           => (float) $row['price_per_share'],
        'sharesAvailable' => $available,
        'sharesTotal'     => (int) $row['total_shares'],
        'allocatedPct'    => (int) round((($totalShares - $available) / $totalShares) * 100),
        'image'           => $row['image_url'] ?? null,
    ];
}, $db_offerings);

$page_title = "MusixVest - Open Offerings";
$active = 'offerings';
$page_heading = 'Open Offerings';
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
            <div class="max-w-6xl mx-auto">

                <div class="mb-7">
                    <p class="text-[#2d60c3] text-xs font-bold uppercase tracking-wider">Marketplace</p>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1">Open Offerings</h1>
                    <p class="text-sm text-slate-500 mt-2">Buy fractional SongShares in the songs currently open for investment.</p>
                </div>

                <?php if (!$offerings): ?>
                <p class="text-center text-sm text-slate-400 py-20">No offerings are live right now — check back soon.</p>
                <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    <?php foreach ($offerings as $offer):
                        $isActive = $offer['status'] === 'Active';
                    ?>
                    <article class="card overflow-hidden flex flex-col <?php echo $isActive ? '' : 'opacity-80'; ?>">
                        <div class="w-full h-40 bg-gradient-to-br from-[#2d60c3] to-[#0a2a5e] shrink-0">
                            <?php if (!empty($offer['image'])): ?>
                            <img src="<?php echo htmlspecialchars($offer['image']); ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-white text-5xl" aria-hidden="true">&#127925;</div>
                            <?php endif; ?>
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <h2 class="font-bold text-slate-900 text-base"><?php echo htmlspecialchars($offer['title']); ?></h2>
                                <p class="text-sm text-slate-500"><?php echo htmlspecialchars($offer['artist']); ?></p>
                            </div>
                            <span class="badge <?php echo $isActive ? 'badge-success' : 'badge-danger'; ?>"><?php echo htmlspecialchars($offer['status']); ?></span>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-500">Price per share</p>
                                <p class="font-bold text-slate-900 mt-1">$<?php echo number_format($offer['price'], 2); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Shares available</p>
                                <p class="font-bold text-slate-900 mt-1"><?php echo number_format($offer['sharesAvailable']); ?></p>
                            </div>
                        </div>

                        <?php if ($isActive): ?>
                        <div class="mt-5 h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-[#2d60c3]" style="width:<?php echo $offer['allocatedPct']; ?>%"></div></div>
                        <p class="text-xs text-slate-500 mt-2"><?php echo $offer['allocatedPct']; ?>% of shares allocated</p>
                        <a href="offering-detail.php?id=<?php echo (int) $offer['id']; ?>" class="btn btn-primary w-full text-center mt-5">View Investment</a>
                        <?php else: ?>
                        <div class="mt-5 h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-slate-400 w-full"></div></div>
                        <p class="text-xs text-slate-500 mt-2">Offering fully allocated</p>
                        <a href="offering-detail.php?id=<?php echo (int) $offer['id']; ?>" class="btn btn-secondary w-full text-center mt-5">View Details</a>
                        <?php endif; ?>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </main>

        <?php include 'inc/footer-app.php'; ?>
    </div>

</body>
</html>
