<?php
/**
 * Starts (or resumes) the PHP session. Included at the very top of any
 * page/script that reads or writes $_SESSION, before any HTML output.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
