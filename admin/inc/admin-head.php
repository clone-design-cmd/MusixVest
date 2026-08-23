<?php
/**
 * Shared admin <head> content.
 * Set $page_title before including this file.
 *
 * Reuses the main site's design system (../assets/css/styles.css) so
 * the admin panel looks and behaves consistently with the investor
 * site, but loads its own admin-app.js instead of the investor
 * assets/js/app.js — admin pages never load investor-side JS.
 */
$page_title = $page_title ?? 'MusixVest Admin';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#071342">
<meta name="robots" content="noindex, nofollow">
<title><?php echo htmlspecialchars($page_title); ?></title>
<link rel="shortcut icon" href="../assets/img/favicon.png" type="image/png">

<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/styles.css">
<script src="assets/js/admin-app.js" defer></script>
