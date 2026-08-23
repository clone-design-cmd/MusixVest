<?php
require_once __DIR__ . '/config/Controller.php';
$controller = new Controller();

$featured = $controller->FeaturedOfferings(3);

$page_title = "MusixVest — Invest in Songs You Love";
$active_nav = '';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <?php include 'inc/head.php'; ?>
    <meta name="description" content="MusixVest lets fans buy fractional SongShares in music royalties and earn a return as songs generate revenue.">
</head>
<body class="bg-white text-slate-700 antialiased">

    <?php include 'inc/header-landing.php'; ?>

    <main id="main">

        <!-- HERO -->
        <section class="max-w-6xl mx-auto px-4 sm:px-6 pt-14 pb-20 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-6 space-y-6">
                <h1 class="text-4xl sm:text-5xl xl:text-6xl font-extrabold text-slate-900 leading-[1.1] tracking-tight">
                    Make music your next investment
                </h1>
                <p class="text-base sm:text-lg text-slate-500 leading-relaxed max-w-lg">
                    Buy fractional SongShares in music royalties and earn a portion of the income each time a song streams, sells, or gets licensed.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="offerings.php" class="btn btn-primary">Explore Offerings</a>
                    <a href="how-it-works.php" class="btn btn-secondary">How It Works</a>
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="card p-6 max-w-sm mx-auto" aria-hidden="true">
                    <div class="flex justify-between items-center text-xs text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-100 pb-3 mb-4">
                        <span>Portfolio Overview</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <p class="text-xs text-slate-400">Portfolio value</p>
                    <p class="text-3xl font-extrabold text-slate-900 mb-4">$12,480.00</p>
                    <div class="h-20 w-full bg-slate-50 rounded-lg p-2 flex items-end justify-between gap-1">
                        <div class="bg-blue-200 w-full h-[30%] rounded-sm"></div>
                        <div class="bg-blue-300 w-full h-[45%] rounded-sm"></div>
                        <div class="bg-blue-400 w-full h-[60%] rounded-sm"></div>
                        <div class="bg-blue-500 w-full h-[85%] rounded-sm"></div>
                        <div class="bg-[#2d60c3] w-full h-[100%] rounded-sm"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TRUST STRIP -->
        <section class="border-y border-slate-100 py-6 bg-slate-50/50" aria-label="Featured coverage">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <p class="text-sm text-center font-semibold text-slate-400 uppercase tracking-widest mb-4">
                    Built for music investors
                </p>
                <p class="text-center text-sm text-slate-500 max-w-2xl mx-auto">
                    Explore music royalty opportunities through a streamlined investment experience.
                </p>
            </div>
        </section>

        <!-- FEATURED OFFERINGS -->
        <section id="offerings-preview" class="bg-slate-50 py-20 border-b border-slate-100">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 text-center mb-12">Featured Offerings</h2>

                <?php if (!$featured): ?>
                <p class="text-center text-sm text-slate-400 py-6">No featured offerings right now — check back soon.</p>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($featured as $offer):
                        $allocated = $controller->SharesAllocated($offer['id']);
                        $available = max(0, (int) $offer['total_shares'] - $allocated);
                        $isActive = $offer['status'] !== 'soldout';
                    ?>
                    <article class="card overflow-hidden">
                        <div class="w-full h-36 bg-gradient-to-br from-[#2d60c3] to-[#0a2a5e]">
                            <?php if (!empty($offer['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($offer['image_url']); ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-white text-4xl" aria-hidden="true">&#127925;</div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm"><?php echo htmlspecialchars($offer['title']); ?></h3>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($offer['artist']); ?></p>
                            </div>
                            <span class="badge <?php echo $isActive ? 'badge-success' : 'badge-danger'; ?>"><?php echo $isActive ? 'Active' : 'Sold out'; ?></span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-500 mb-3">
                            <span>$<?php echo number_format((float) $offer['price_per_share'], 2); ?> / share</span>
                            <span><?php echo number_format($available); ?> available</span>
                        </div>
                        <a href="offering-detail.php?id=<?php echo (int) $offer['id']; ?>" class="btn btn-primary w-full text-center">View Offering</a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="text-center mt-10">
                    <a href="offerings.php" class="btn btn-primary">View All Offerings</a>
                </div>
            </div>
        </section>

        <!-- GETTING STARTED -->
        <section class="py-20 bg-white border-t border-slate-100">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 text-center mb-14">Getting started is easy</h2>
                <ol class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <li class="flex gap-4 items-start">
                        <span class="w-9 h-9 rounded-full bg-[#2d60c3] text-white flex items-center justify-center font-bold text-sm shrink-0" aria-hidden="true">1</span>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Create your account</h3>
                            <p class="text-sm text-slate-500 mt-1">Register and verify your identity to unlock investing.</p>
                        </div>
                    </li>
                    <li class="flex gap-4 items-start">
                        <span class="w-9 h-9 rounded-full bg-[#2d60c3] text-white flex items-center justify-center font-bold text-sm shrink-0" aria-hidden="true">2</span>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Deposit and buy SongShares</h3>
                            <p class="text-sm text-slate-500 mt-1">Add funds to your balance, then invest in songs you believe in.</p>
                        </div>
                    </li>
                    <li class="flex gap-4 items-start">
                        <span class="w-9 h-9 rounded-full bg-[#2d60c3] text-white flex items-center justify-center font-bold text-sm shrink-0" aria-hidden="true">3</span>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Earn royalty distributions</h3>
                            <p class="text-sm text-slate-500 mt-1">Track your portfolio and returns from your dashboard.</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <!-- SELLING ROYALTIES CTA -->
        <section id="selling" class="max-w-5xl mx-auto px-4 sm:px-6 my-16">
            <div class="bg-slate-100 rounded-2xl p-8 sm:p-12 border border-slate-200/60 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="space-y-3 max-w-lg">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">Are you an artist, songwriter, or rightsholder?</h2>
                    <p class="text-sm text-slate-500">List your catalog on MusixVest and raise funding directly from fans.</p>
                </div>
                <a href="contact.php" class="btn btn-primary shrink-0">Contact Us</a>
            </div>
        </section>

    </main>

    <?php include 'inc/footer-landing.php'; ?>

</body>
</html>
