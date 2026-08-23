<?php
require_once __DIR__ . '/config/Controller.php';
$controller = new Controller();

$faqs = $controller->Faqs();

$page_title = "How It Works — MusixVest";
$active_nav = 'how-it-works';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <?php include 'inc/head.php'; ?>
    <meta name="description" content="How MusixVest works: verify your identity, deposit funds, buy SongShares, and earn royalty distributions.">
</head>
<body class="bg-white text-slate-700 antialiased">

    <?php include 'inc/header-landing.php'; ?>

    <main id="main">

        <!-- HERO -->
        <section class="max-w-3xl mx-auto px-4 sm:px-6 pt-14 pb-10 text-center">
            <p class="eyebrow">How It Works</p>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                From new account to your first royalty payout
            </h1>
            <p class="text-sm sm:text-base text-slate-500 mt-3 max-w-xl mx-auto">
                MusixVest walks every investor through the same five steps — verification, funding, investing, holding, and getting paid.
            </p>
        </section>

        <!-- STEP PROCESS -->
        <section class="max-w-2xl mx-auto px-4 sm:px-6 pb-20">
            <div class="step-track">

                <div class="step-row">
                    <span class="step-num" aria-hidden="true">1</span>
                    <div class="card p-5 flex-1">
                        <h2 class="font-bold text-slate-900 text-base">Create your account</h2>
                        <p class="text-sm text-slate-500 mt-1">Register with your email and set a password to get started.</p>
                    </div>
                </div>

                <div class="step-row">
                    <span class="step-num" aria-hidden="true">2</span>
                    <div class="card p-5 flex-1">
                        <h2 class="font-bold text-slate-900 text-base">Verify your identity</h2>
                        <p class="text-sm text-slate-500 mt-1">Confirm a few details so we can unlock investing on your account.</p>
                    </div>
                </div>

                <div class="step-row">
                    <span class="step-num" aria-hidden="true">3</span>
                    <div class="card p-5 flex-1">
                        <h2 class="font-bold text-slate-900 text-base">Deposit funds</h2>
                        <p class="text-sm text-slate-500 mt-1">Send crypto to your deposit address. An admin confirms it before it appears in your balance.</p>
                    </div>
                </div>

                <div class="step-row">
                    <span class="step-num" aria-hidden="true">4</span>
                    <div class="card p-5 flex-1">
                        <h2 class="font-bold text-slate-900 text-base">Buy SongShares</h2>
                        <p class="text-sm text-slate-500 mt-1">Browse open offerings and buy fractional shares in the songs you believe in.</p>
                    </div>
                </div>

                <div class="step-row">
                    <span class="step-num is-final" aria-hidden="true">5</span>
                    <div class="card p-5 flex-1">
                        <h2 class="font-bold text-slate-900 text-base">Track your returns</h2>
                        <p class="text-sm text-slate-500 mt-1">Watch your portfolio and royalty distributions from your dashboard.</p>
                    </div>
                </div>

            </div>

            <div class="text-center mt-10">
                <a href="register.php" class="btn btn-primary">Create Your Account</a>
            </div>
        </section>

        <!-- FAQ -->
        <section id="faq" class="max-w-3xl mx-auto px-4 sm:px-6 pb-24">
            <h2 class="text-2xl font-bold text-slate-900 text-center mb-8">Frequently asked questions</h2>

            <?php if (!$faqs): ?>
            <p class="text-center text-sm text-slate-400">No FAQs published yet.</p>
            <?php else: ?>
            <div class="space-y-3" x-data="{ openId: null }">
                <?php foreach ($faqs as $i => $faq): ?>
                <div class="card faq-item">
                    <button type="button"
                            class="faq-trigger"
                            @click="openId = (openId === <?php echo $i; ?> ? null : <?php echo $i; ?>)"
                            :aria-expanded="openId === <?php echo $i; ?>"
                            aria-controls="faq-panel-<?php echo $i; ?>">
                        <span class="font-semibold text-slate-800 text-sm"><?php echo htmlspecialchars($faq['question']); ?></span>
                        <span class="faq-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </span>
                    </button>
                    <div id="faq-panel-<?php echo $i; ?>" x-show="openId === <?php echo $i; ?>" x-transition style="display:none">
                        <p class="text-sm text-slate-500 px-5 pb-5"><?php echo htmlspecialchars($faq['answer']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>

    </main>

    <?php include 'inc/footer-landing.php'; ?>

</body>
</html>
