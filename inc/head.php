<?php
/**
 * Shared <head> content.
 * Set $page_title before including this file, e.g.
 *   <?php $page_title = "MusixVest - Dashboard"; ?>
 */
$page_title = $page_title ?? 'MusixVest - Invest in Hit Songs';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#071342">
<title><?php echo htmlspecialchars($page_title); ?></title>
<link rel="shortcut icon" href="assets/img/favicon.png" type="image/png">

<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/styles.css">
<script src="assets/js/site.js" defer></script>
<script src="assets/js/app.js" defer></script>
