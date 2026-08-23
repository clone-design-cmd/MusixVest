<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/Controller.php';

/**
 * Top bar for the authenticated investor portal.
 * Set before including:
 *   $page_heading  (string) e.g. "Dashboard" — shown as the header title.
 */
$page_heading = $page_heading ?? '';

$controller = $controller ?? new Controller();
$userId = $_SESSION['user_id'] ?? 1; // demo fallback user for this prototype

$user_first_name = $_SESSION['user_first_name'] ?? 'James';
$user_last_name = $_SESSION['user_last_name'] ?? '';
$avatar_initials = strtoupper(substr($user_first_name, 0, 1) . substr($user_last_name, 0, 1));
$header_balance = $controller->AccountBalance($userId);
?>
<header class="mv-gradient text-white sticky top-0 z-40">
    <div class="h-[68px] px-4 sm:px-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="lg:hidden min-w-11 min-h-11 rounded-lg bg-white/10 hover:bg-white/15 flex items-center justify-center" aria-label="Open menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <?php if ($page_heading): ?>
            <div class="hidden sm:block text-sm font-semibold"><?php echo htmlspecialchars($page_heading); ?></div>
            <?php endif; ?>
        </div>

        <div class="flex items-center gap-3 sm:gap-5">
            <span class="text-sm font-semibold bg-white/10 px-3 py-1.5 rounded-full">Balance: $<?php echo number_format($header_balance, 2); ?></span>

            <div class="relative">
                <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 text-sm font-semibold" :aria-expanded="profileOpen" aria-haspopup="true">
                    <span class="w-7 h-7 rounded-full bg-white/10 border border-white/20 flex items-center justify-center text-[10px] font-bold" aria-hidden="true"><?php echo htmlspecialchars($avatar_initials); ?></span>
                    <span aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div x-show="profileOpen" @click.outside="profileOpen = false" x-transition class="absolute right-0 mt-2 w-36 rounded-lg bg-white text-slate-700 shadow-xl py-1" style="display:none">
                    <a href="settings.php" class="block px-4 py-2 text-sm hover:bg-slate-50">Settings</a>
                    <button type="button" data-modal-target="#logoutModal" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Log Out</button>
                </div>
            </div>
        </div>
    </div>
</header>
