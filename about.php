<?php
require_once __DIR__ . '/config/Controller.php';
$controller = new Controller();

// Normalize DB column name (image_url) to the key the template below uses (img).
$team_members = array_map(function ($m) {
    return ['name' => $m['name'], 'role' => $m['role'], 'img' => $m['image_url']];
}, $controller->TeamMembers());

$about_content = $controller->PageContent('about');
$intro_1 = $about_content['intro_1'] ?? '';
$intro_2 = $about_content['intro_2'] ?? '';

$page_title = "MusixVest - About Us";
$active_nav = 'about';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <?php include 'inc/head.php'; ?>
</head>
<body class="bg-white text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    <?php include 'inc/header-landing.php'; ?>

    <!-- MAIN CONTENT CONTAINER -->
    <main id="main" class="py-16">

        <!-- HEADER TEXT SECTION -->
        <section class="max-w-3xl mx-auto px-4 sm:px-6 text-center space-y-6 mb-20">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight">
                About MusixVest
            </h1>

            <?php if ($intro_1): ?>
            <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                <?php echo htmlspecialchars($intro_1); ?>
            </p>
            <?php endif; ?>

            <?php if ($intro_2): ?>
            <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                <?php echo htmlspecialchars($intro_2); ?>
            </p>
            <?php endif; ?>
        </section>

        <!-- TEAM GRID SECTION -->
        <section class="bg-[#F3F4F6] py-16 border-y border-slate-100">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">

                <?php if (!$team_members): ?>
                <p class="text-center text-sm text-slate-500 py-10">Team information coming soon.</p>
                <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($team_members as $member): ?>
                    <div class="bg-[#8B9F93] text-white rounded-2xl p-5 text-center flex flex-col items-center space-y-3 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-full aspect-[4/3] rounded-xl overflow-hidden bg-slate-200">
                            <img src="<?php echo htmlspecialchars($member['img']); ?>" class="w-full h-full object-cover" alt="<?php echo htmlspecialchars($member['name']); ?>">
                        </div>
                        <div class="space-y-0.5">
                            <h3 class="font-bold text-lg text-slate-900"><?php echo htmlspecialchars($member['name']); ?></h3>
                            <p class="text-xs text-slate-100 font-medium"><?php echo htmlspecialchars($member['role']); ?></p>
                        </div>
                        <a href="index.php" class="text-white hover:opacity-80 pt-1">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </section>

    </main>

    <?php include 'inc/footer-landing.php'; ?>

</body>
</html>
