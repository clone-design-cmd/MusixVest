<?php
/**
 * Left sidebar for the admin panel.
 * Set $active_tab before including this file:
 *   'overview' | 'offerings' | 'users' | 'deposits' | 'withdrawals' | 'payments' | 'transactions'
 * Requires Alpine `sidebarOpen` state on a parent element (see admin-header.php).
 */
$active_tab = $active_tab ?? 'overview';

function mv_admin_side_class($key, $active) {
    return $key === $active ? 'side-link active' : 'side-link';
}
?>
<!-- Mobile overlay -->
<div x-show="sidebarOpen"
     x-transition.opacity
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-black/40 z-40 lg:hidden"
     style="display:none"></div>

<!-- Sidebar -->
<aside class="app-sidebar fixed inset-y-0 left-0 bg-white border-r border-slate-200 z-50 flex flex-col"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       style="transition: transform .2s ease;">

    <div class="h-[88px] flex items-center justify-center px-5 border-b border-slate-100 shrink-0">
        <a href="dashboard.php"><img src="../assets/img/logo.png" alt="MusixVest admin home" class="w-[132px] h-auto"></a>
    </div>

    <nav aria-label="Admin" class="flex-1 p-3 space-y-1 overflow-y-auto">

        <a href="dashboard.php?tab=overview" class="<?php echo mv_admin_side_class('overview', $active_tab); ?> flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-slate-50">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><path d="M3 9.5 12 3l9 6.5"/><path d="M5 10v10a1 1 0 0 0 1 1h3v-6h6v6h3a1 1 0 0 0 1-1V10"/></svg>
            Overview
        </a>

        <a href="dashboard.php?tab=offerings" class="<?php echo mv_admin_side_class('offerings', $active_tab); ?> flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-slate-50">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
            Manage Offerings
        </a>

        <a href="dashboard.php?tab=users" class="<?php echo mv_admin_side_class('users', $active_tab); ?> flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-slate-50">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Investors
        </a>

        <a href="dashboard.php?tab=deposits" class="<?php echo mv_admin_side_class('deposits', $active_tab); ?> flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-slate-50">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><circle cx="12" cy="12" r="9"/><polyline points="8 12 12 16 16 12"/><line x1="12" y1="8" x2="12" y2="16"/></svg>
            Deposits
        </a>

        <a href="dashboard.php?tab=withdrawals" class="<?php echo mv_admin_side_class('withdrawals', $active_tab); ?> flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-slate-50">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><circle cx="12" cy="12" r="9"/><polyline points="16 12 12 8 8 12"/><line x1="12" y1="16" x2="12" y2="8"/></svg>
            Withdrawals
        </a>

        <a href="dashboard.php?tab=payments" class="<?php echo mv_admin_side_class('payments', $active_tab); ?> flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-slate-50">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            Payment Settings
        </a>

        <a href="dashboard.php?tab=transactions" class="<?php echo mv_admin_side_class('transactions', $active_tab); ?> flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-slate-50">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Transactions
        </a>

        <button type="button" data-modal-target="#adminLogoutModal" class="w-full flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><path d="M16 17l5-5-5-5M21 12H9M13 21H6a2 2 0 01-2-2V5a2 2 0 012-2h7"/></svg>
            Logout
        </button>
    </nav>

    <div class="p-3 border-t border-slate-100">
        <a href="../index.php" class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to website
        </a>
    </div>
</aside>

<!-- Logout confirmation modal -->
<div id="adminLogoutModal" class="mv-modal hidden">
    <div class="mv-modal-backdrop" data-modal-close></div>
    <div class="mv-modal-panel max-w-sm p-6 space-y-4">
        <div class="w-11 h-11 rounded-full bg-red-50 text-red-600 flex items-center justify-center">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 17l5-5-5-5M21 12H9M13 21H6a2 2 0 01-2-2V5a2 2 0 012-2h7"/></svg>
        </div>
        <div>
            <h3 class="text-[15px] font-bold text-slate-900">Log out of the admin panel?</h3>
            <p class="text-[12px] text-slate-500 mt-1">You'll need to sign back in to manage the platform.</p>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" data-modal-close class="btn btn-secondary">Cancel</button>
            <button type="button" id="confirmAdminLogoutBtn" class="btn btn-danger">Log Out</button>
        </div>
    </div>
</div>
