<?php
/**
 * admin/config/admin_session.php
 * ---------------------------------------------------------------------
 * Starts (or resumes) the PHP session for the admin panel and provides
 * a login guard. Admin identity is kept in its own session keys
 * (admin_id / admin_name / admin_email) so it can never collide with
 * or be mistaken for the investor session keys (user_id / ...) set by
 * config/session.php on the main site — a browser could in theory be
 * signed into both an investor account and the admin panel at once
 * without either one leaking into the other.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** True if an admin is currently signed in. */
function admin_is_logged_in()
{
    return !empty($_SESSION['admin_id']);
}

/**
 * Call at the top of any protected admin page (before any HTML output).
 * Redirects to the admin login screen if nobody is signed in.
 */
function admin_require_login()
{
    if (!admin_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
