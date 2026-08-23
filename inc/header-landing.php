<?php
/**
 * Header/navbar for public marketing pages (index, about, how-it-works, contact).
 * Optionally set $active_nav before including this file to highlight the
 * current page in the nav: 'offerings' | 'how-it-works' | 'about' | 'contact' | 'faq'
 */
$active_nav = $active_nav ?? '';

function mv_nav_class($key, $active) {
    return $key === $active
        ? 'text-slate-900 font-semibold text-sm transition-colors'
        : 'text-slate-500 hover:text-slate-900 text-sm transition-colors';
}
?>
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-20 flex items-center justify-between">
        <a href="index.php" class="flex items-center gap-2">
            <img src="assets/img/logo.png" alt="MusixVest home" class="h-10 w-auto object-contain">
        </a>

        <nav aria-label="Primary" class="hidden lg:flex items-center gap-7">
            <a href="offerings.php" class="<?php echo mv_nav_class('offerings', $active_nav); ?>">Explore Offerings</a>
            <a href="how-it-works.php" class="<?php echo mv_nav_class('how-it-works', $active_nav); ?>">How It Works</a>
            <a href="index.php#selling" class="<?php echo mv_nav_class('selling', $active_nav); ?>">Selling Royalties</a>
            <a href="about.php" class="<?php echo mv_nav_class('about', $active_nav); ?>">About</a>
            <a href="contact.php" class="<?php echo mv_nav_class('contact', $active_nav); ?>"<?php echo $active_nav === 'contact' ? ' aria-current="page"' : ''; ?>>Contact</a>
            <a href="how-it-works.php#faq" class="<?php echo mv_nav_class('faq', $active_nav); ?>">FAQ</a>
            <a href="login.php" class="<?php echo mv_nav_class('login', $active_nav); ?>">Log In</a>
        </nav>

        <a href="register.php" class="btn btn-primary">Get Started</a>
    </div>
</header>
