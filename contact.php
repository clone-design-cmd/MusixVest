<?php
$page_title = "Contact Us — MusixVest";
$active_nav = 'contact';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <?php include 'inc/head.php'; ?>
    <meta name="description" content="Get in touch with the MusixVest team — questions, support, or catalog inquiries.">
</head>
<body class="bg-white text-slate-700 antialiased">

    <?php include 'inc/header-landing.php'; ?>

    <main id="main">

        <section class="max-w-3xl mx-auto px-4 sm:px-6 pt-14 pb-6 text-center">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Get in touch</h1>
            <p class="text-sm sm:text-base text-slate-500 mt-3 max-w-xl mx-auto">
                Questions about investing, your account, or listing a catalog on MusixVest? Send us a message and we'll get back to you.
            </p>
        </section>

        <section class="max-w-xl mx-auto px-4 sm:px-6 pb-24">
            <div class="card p-6 sm:p-8">
                <form class="ajax-form space-y-4">
                    <input type="hidden" name="action" value="contact_message">

                    <div class="field">
                        <label class="field-label" for="contact-name">Name</label>
                        <input type="text" id="contact-name" name="name" required class="input" placeholder="Your full name" autocomplete="name">
                    </div>
                    <div class="field">
                        <label class="field-label" for="contact-email">Email</label>
                        <input type="email" id="contact-email" name="email" required class="input" placeholder="you@example.com" autocomplete="email">
                    </div>
                    <div class="field">
                        <label class="field-label" for="contact-subject">Subject</label>
                        <input type="text" id="contact-subject" name="subject" class="input" placeholder="What's this about?">
                    </div>
                    <div class="field">
                        <label class="field-label" for="contact-message">Message</label>
                        <textarea id="contact-message" name="message" required rows="6" class="input" placeholder="Tell us how we can help"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-full"><span class="btn-label">Send Message</span></button>
                    <p class="text-xs text-slate-400 text-center">We typically reply within one business day.</p>
                </form>
            </div>
        </section>

    </main>

    <?php include 'inc/footer-landing.php'; ?>

</body>
</html>
