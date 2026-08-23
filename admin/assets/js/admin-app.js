/**
 * MusixVest Admin — admin/assets/js/admin-app.js
 * -------------------------------------------------------------------
 * Deliberately separate from the investor-side assets/js/app.js so
 * admin interactions never depend on (or accidentally call) anything
 * wired to the investor session/endpoint. Same toast + modal system
 * (reusing the shared mv-toast / mv-modal CSS in ../../assets/css/styles.css)
 * but every ajax-form here posts to config/admin_request.php.
 */
(function ($) {
    'use strict';

    var DEFAULT_ENDPOINT = 'config/admin_request.php';

    /* ------------------------------------------------------------
     * Toasts (identical behavior to the investor site's app.js)
     * ---------------------------------------------------------- */

    var TOAST_STYLES = {
        success: { bg: '#059669', icon: '&#10003;' },
        danger:  { bg: '#dc2626', icon: '&#10005;' },
        warning: { bg: '#d97706', icon: '&#9888;'  },
        info:    { bg: '#2563eb', icon: '&#8505;'  }
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

        window.requestAnimationFrame(function () {
            $toast.addClass('mv-toast-visible');
        });

        setTimeout(function () {
            $toast.removeClass('mv-toast-visible');
            setTimeout(function () { $toast.remove(); }, 300);
        }, 4000);
    }

    window.mvToast = showToast;

    /* ------------------------------------------------------------
     * Generic AJAX form handler — identical contract to the
     * investor-side app.js (data-redirect / data-reload / data-reset),
     * just posting to the admin endpoint by default.
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
        }).fail(function (err) {
            showToast('danger', 'Something went wrong. Please try again.');
            console.error(err);
        }).always(function () {
            $btn.prop('disabled', false).text(originalText);
        });
    });

    /* ------------------------------------------------------------
     * Modals — identical data-modal-target / data-modal-close contract.
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

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            $('.mv-modal.mv-modal-open').removeClass('mv-modal-open').addClass('hidden');
            $('body').removeClass('overflow-hidden');
        }
    });

    /* ------------------------------------------------------------
     * Admin logout confirmation
     * ---------------------------------------------------------- */

    $(document).on('click', '#confirmAdminLogoutBtn', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Logging out...');

        $.ajax({
            url: DEFAULT_ENDPOINT,
            method: 'POST',
            data: { action: 'admin_logout' },
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
     * Offering modal (Add / Edit) — a single shared #offeringModal is
     * reused for both. "Add" opens it empty; each row's "Edit" button
     * carries the offering's current values on data-* attributes and
     * this handler copies them into the form fields before opening.
     *
     *   <button data-edit-offering
     *           data-id="4" data-title="..." data-artist="..." ...>
     * ---------------------------------------------------------- */

    $(document).on('click', '[data-add-offering]', function () {
        var $form = $('#offeringForm');
        $form[0].reset();
        $form.find('[name="offering_id"]').val('');
        $form.find('[name="action"]').val('add_offering');
        $('#offeringModalTitle').text('Publish New Offering');
        $('#milestoneRows').empty();
        addMilestoneRow();
    });

    $(document).on('click', '[data-edit-offering]', function () {
        var $btn = $(this);
        var $form = $('#offeringForm');
        $form[0].reset();

        $form.find('[name="action"]').val('update_offering');
        $form.find('[name="offering_id"]').val($btn.data('id'));
        $form.find('[name="title"]').val($btn.data('title'));
        $form.find('[name="artist"]').val($btn.data('artist'));
        $form.find('[name="category"]').val($btn.data('category'));
        $form.find('[name="description"]').val($btn.data('description'));
        $form.find('[name="image_url"]').val($btn.data('image'));
        $form.find('[name="price"]').val($btn.data('price'));
        $form.find('[name="total_shares"]').val($btn.data('total-shares'));
        $form.find('[name="yield_percent"]').val($btn.data('yield'));
        $form.find('[name="duration_days"]').val($btn.data('duration'));
        $form.find('[name="status"]').val($btn.data('status'));
        $form.find('[name="featured"]').prop('checked', $btn.data('featured') == 1);

        $('#offeringModalTitle').text('Edit Offering');

        var $rows = $('#milestoneRows').empty();
        var milestones = $btn.data('milestones');
        if (typeof milestones === 'string' && milestones) {
            try { milestones = JSON.parse(milestones); } catch (e) { milestones = []; }
        }
        if (milestones && milestones.length) {
            milestones.forEach(function (m) { addMilestoneRow(m.days, m.pct); });
        } else {
            addMilestoneRow();
        }
    });

    $(document).on('click', '[data-delete-offering]', function () {
        var $btn = $(this);
        $('#deleteOfferingForm [name="offering_id"]').val($btn.data('id'));
        $('#deleteOfferingName').text($btn.data('title'));
    });

    /* ------------------------------------------------------------
     * Milestone rows (growth-schedule builder inside the offering modal)
     * ---------------------------------------------------------- */

    function addMilestoneRow(days, pct) {
        var $row = $(
            '<div class="flex gap-2 items-center mb-2">' +
                '<input type="number" name="milestone_days[]" placeholder="Days" min="1" class="input" style="width:90px">' +
                '<input type="number" name="milestone_pct[]" placeholder="% growth" min="0" step="0.1" class="input" style="width:110px">' +
                '<button type="button" class="btn btn-secondary" data-remove-milestone style="padding:.4rem .7rem">&times;</button>' +
            '</div>'
        );
        if (days !== undefined) $row.find('[name="milestone_days[]"]').val(days);
        if (pct !== undefined) $row.find('[name="milestone_pct[]"]').val(pct);
        $('#milestoneRows').append($row);
    }

    $(document).on('click', '[data-add-milestone]', function () {
        addMilestoneRow();
    });

    $(document).on('click', '[data-remove-milestone]', function () {
        $(this).closest('div').remove();
    });

    /* ------------------------------------------------------------
     * One-click quick actions (confirm/reject deposit, complete/reject
     * withdrawal, delete wallet) — small POST + reload, no modal needed.
     *
     *   <button data-quick-action="confirm_deposit" data-id="12">
     * ---------------------------------------------------------- */

    $(document).on('click', '[data-quick-action]', function () {
        var $btn = $(this);
        var action = $btn.data('quick-action');
        var idField = $btn.data('id-field') || 'id';
        var payload = { action: action };
        payload[idField] = $btn.data('id');

        $btn.prop('disabled', true);

        $.ajax({
            url: DEFAULT_ENDPOINT,
            method: 'POST',
            data: payload,
            dataType: 'json'
        }).done(function (res) {
            showToast(res && res.status ? res.status : 'info', (res && res.message) || 'Done.');
            if (res && res.status === 'success') {
                setTimeout(function () { window.location.reload(); }, 800);
            } else {
                $btn.prop('disabled', false);
            }
        }).fail(function () {
            showToast('danger', 'Something went wrong. Please try again.');
            $btn.prop('disabled', false);
        });
    });

})(jQuery);
