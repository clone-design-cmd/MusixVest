/**
 * MusixVest — assets/js/app.js
 * -------------------------------------------------------------------
 * - Generic AJAX submit handler for every <form class="ajax-form">.
 *   Posts to config/request.php (or a custom data-action-url), shows
 *   a toast with the JSON response, and either resets the form,
 *   reloads the page, or redirects — based on data-* attributes.
 * - A small toast/notification system (success/warning/danger/info).
 * - Simple modal open/close via data-modal-target / data-modal-close.
 * - A couple of one-off handlers: favorite hearts and the logout
 *   confirmation button.
 */
(function ($) {
    'use strict';

    var DEFAULT_ENDPOINT = 'config/request.php';

    /* ------------------------------------------------------------
     * Toasts
     * ---------------------------------------------------------- */

    var TOAST_STYLES = {
        success: { bg: '#059669', icon: '&#10003;' }, // emerald-600
        danger:  { bg: '#dc2626', icon: '&#10005;' }, // red-600
        warning: { bg: '#d97706', icon: '&#9888;'  }, // amber-600
        info:    { bg: '#2563eb', icon: '&#8505;'  }  // blue-600
    };

    function ensureToastContainer() {
        var $container = $('#mvToastContainer');
        if (!$container.length) {
            $container = $('<div id="mvToastContainer" class="mv-toast-container"></div>');
            $('body').append($container);
        }
        return $container;
    }

    function showToast(type, message) {
        var style = TOAST_STYLES[type] || TOAST_STYLES.info;
        var $container = ensureToastContainer();

        var $toast = $('<div class="mv-toast"></div>')
            .css('background', style.bg)
            .html('<span class="mv-toast-icon">' + style.icon + '</span><span class="mv-toast-msg"></span>');

        $toast.find('.mv-toast-msg').text(message);
        $container.append($toast);

        // Force reflow so the transition triggers, then show.
        window.requestAnimationFrame(function () {
            $toast.addClass('mv-toast-visible');
        });

        setTimeout(function () {
            $toast.removeClass('mv-toast-visible');
            setTimeout(function () { $toast.remove(); }, 300);
        }, 4000);
    }

    // Expose for any inline/one-off usage.
    window.mvToast = showToast;

    /* ------------------------------------------------------------
     * Generic AJAX form handler
     *
     *   <form class="ajax-form"
     *         data-action-url="config/request.php"   (optional, defaults above)
     *         data-redirect="dashboard.php"           (optional — go here on success)
     *         data-reload="true"                      (optional — reload page on success)
     *         data-reset="false">                     (optional — skip form.reset() on success)
     * ---------------------------------------------------------- */

    $(document).on('submit', 'form.ajax-form', function (e) {
        e.preventDefault();

        var $form = $(this);
        var url = $form.data('action-url') || DEFAULT_ENDPOINT;
        var redirect = $form.data('redirect');
        var reload = $form.data('reload') === true || $form.data('reload') === 'true';
        var skipReset = $form.data('reset') === false || $form.data('reset') === 'false';

        var $btn = $form.find('button[type="submit"]').first();
        var originalText = $btn.text();
        $btn.prop('disabled', true).text($btn.data('loading-text') || 'Please wait...');

        $.ajax({
            url: url,
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        }).done(function (res) {
            var status = res && res.status ? res.status : 'info';
            var message = res && res.message ? res.message : 'Done.';
            showToast(status, message);

            if (status === 'success') {
                $form.trigger('ajax-form:success', [res]);

                if (redirect) {
                    setTimeout(function () { window.location.href = redirect; }, 900);
                } else if (reload) {
                    setTimeout(function () { window.location.reload(); }, 900);
                } else if (!skipReset) {
                    $form[0].reset();
                }
            }
        }).fail(function () {
            showToast('danger', 'Something went wrong. Please try again.');
        }).always(function () {
            $btn.prop('disabled', false).text(originalText);
        });
    });

    /* ------------------------------------------------------------
     * Modals
     *
     *   <button data-modal-target="#logoutModal">Log out</button>
     *   <div id="logoutModal" class="mv-modal hidden"> ... </div>
     *   Anything with [data-modal-close] inside the modal (or the
     *   backdrop) closes it.
     * ---------------------------------------------------------- */

    $(document).on('click', '[data-modal-target]', function (e) {
        e.preventDefault();
        var target = $(this).data('modal-target');
        $(target).removeClass('hidden').addClass('mv-modal-open');
        $('body').addClass('overflow-hidden');
    });

    $(document).on('click', '[data-modal-close]', function (e) {
        e.preventDefault();
        $(this).closest('.mv-modal').removeClass('mv-modal-open').addClass('hidden');
        $('body').removeClass('overflow-hidden');
    });

    // Escape key closes any open modal.
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            $('.mv-modal.mv-modal-open').removeClass('mv-modal-open').addClass('hidden');
            $('body').removeClass('overflow-hidden');
        }
    });

    /* ------------------------------------------------------------
     * Logout confirmation
     * ---------------------------------------------------------- */

    $(document).on('click', '#confirmLogoutBtn', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Logging out...');

        $.ajax({
            url: DEFAULT_ENDPOINT,
            method: 'POST',
            data: { action: 'logout' },
            dataType: 'json'
        }).done(function (res) {
            showToast(res && res.status ? res.status : 'success', (res && res.message) || 'Logged out.');
            setTimeout(function () { window.location.href = 'login.php'; }, 700);
        }).fail(function () {
            showToast('danger', 'Could not log out. Please try again.');
            $btn.prop('disabled', false).text('Log Out');
        });
    });

    /* ------------------------------------------------------------
     * Favorite hearts (offerings.php / favorites.php)
     *
     *   <button class="js-toggle-favorite" data-song-id="12">...</button>
     * ---------------------------------------------------------- */

    $(document).on('click', '.js-toggle-favorite', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var songId = $btn.data('song-id');
        if (!songId) return;

        $.ajax({
            url: DEFAULT_ENDPOINT,
            method: 'POST',
            data: { action: 'toggle_favorite', song_id: songId },
            dataType: 'json'
        }).done(function (res) {
            showToast(res && res.status ? res.status : 'info', (res && res.message) || 'Updated.');
            if (res && res.data && res.data.state === 'added') {
                $btn.addClass('text-red-500');
            } else if (res && res.data && res.data.state === 'removed') {
                $btn.removeClass('text-red-500');
                // On the dedicated Favorites page, removing drops the card entirely.
                if ($btn.closest('body').hasClass('mv-page-favorites')) {
                    $btn.closest('article').fadeOut(200, function () { $(this).remove(); });
                }
            }
        }).fail(function () {
            showToast('danger', 'Could not update your favorites.');
        });
    });

})(jQuery);
