(function ($) {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if ($ && csrf) $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });

    window.cholavinAjax = function (options) {
        return $.ajax(options).done(function (response) {
            if (response?.success !== false && response?.message) window.cholavinToast(response.message, 'success');
            if (response?.refresh?.datatable) document.dispatchEvent(new CustomEvent('cholavin:refresh-datatable'));
            if (response?.refresh?.summary) document.dispatchEvent(new CustomEvent('cholavin:refresh-summary'));
        }).fail(function (xhr) {
            const status = xhr.status;
            const message = xhr.responseJSON?.message || (status === 401 ? 'Your session has expired.' : status === 403 ? 'You do not have permission for this action.' : status === 419 ? 'The page expired. Refresh and try again.' : status === 422 ? 'Please correct the highlighted fields.' : status === 409 ? 'This operation conflicts with another change.' : 'Something went wrong. Please try again.');
            window.cholavinToast(message, 'error');
        });
    };

    window.cholavinToast = function (message, icon) {
        if (window.Swal) Swal.fire({ toast: true, position: 'top-end', timer: 2600, showConfirmButton: false, icon: icon || 'info', title: message });
        else window.alert(message);
    };

    function openDrawer(title, body) {
        $('#workspace-drawer').addClass('open').attr('aria-hidden', 'false');
        $('.cholavin-drawer-backdrop').addClass('open');
        $('[data-drawer-title]').text(title || 'Details');
        $('[data-drawer-body]').html(body || '<div class="cholavin-empty-state"><i class="ri-layout-right-line"></i><h4>No details yet</h4><p>This drawer is ready for the next backend workflow.</p></div>');
    }

    function closeDrawer() {
        $('#workspace-drawer').removeClass('open').attr('aria-hidden', 'true');
        $('.cholavin-drawer-backdrop').removeClass('open');
    }

    $(document).on('click', '[data-drawer-close]', closeDrawer);
    $(document).on('keydown', function (event) { if (event.key === 'Escape') closeDrawer(); });
    $(document).on('click', '[data-toast]', function () { window.cholavinToast($(this).data('toast')); });
    $(document).on('click', '[data-sidebar-toggle], #topnav-hamburger-icon', function () {
        if ($('#cholavin-sidebar').length) $('#cholavin-sidebar').toggleClass('open');
        $('body').toggleClass('vertical-sidebar-enable');
    });

    $(document).on('click', '[data-drawer-record]', function () {
        const name = $(this).data('drawer-record') || 'Selected record';
        openDrawer(name, '<div class="cholavin-drawer-section"><span class="cholavin-eyebrow">Overview</span><h4>' + name + '</h4><p>The universal right drawer is wired for AJAX tabs, actions, timeline, and audit history.</p></div><div class="cholavin-drawer-section"><h4>Available actions</h4><div class="d-flex flex-wrap gap-2"><button class="cholavin-outline-button" type="button" data-toast="Edit action is queued for this module."><i class="ri-edit-line"></i>Edit</button><button class="cholavin-brand-button" type="button" data-toast="Create action is queued for this module."><i class="ri-add-line"></i>Create</button></div></div><div class="cholavin-drawer-section"><h4>Activity timeline</h4><div class="cholavin-timeline"><div class="cholavin-timeline-item"><i class="ri-eye-line"></i><div><strong>Workspace opened</strong><small>Ready for the rebuilt backend workflow.</small></div></div><div class="cholavin-timeline-item"><i class="ri-shield-check-line"></i><div><strong>Permission boundary checked</strong><small>All writes will be authenticated and audited.</small></div></div></div></div>');
    });

    $(document).on('click', '.cholavin-tab', function () { $(this).closest('.cholavin-tabs').find('.cholavin-tab').removeClass('active'); $(this).addClass('active'); window.cholavinToast('Loaded ' + $(this).text() + ' section.'); });
    $(document).on('input', '[data-smart-search]', function () { const query = this.value.toLowerCase(); $(this).closest('.cholavin-panel').find('[data-filter-row]').each(function () { $(this).toggle($(this).text().toLowerCase().indexOf(query) !== -1); }); });

    $(document).on('change', '#header-shop, #header-shop-context', function () {
        const value = this.value;
        if (!value) return;
        window.cholavinAjax({ url: this.dataset.switchUrl, type: 'POST', data: { shop_id: value } }).done(function () { window.location.reload(); });
    });
    $(document).on('change', '#header-godown, #header-godown-context', function () {
        window.cholavinAjax({ url: this.dataset.switchUrl, type: 'POST', data: { godown_id: this.value || null } }).done(function () { window.location.reload(); });
    });

    $(document).on('submit', '#workspace-form', function (event) {
        event.preventDefault();
        const form = this;
        if (!form.checkValidity()) {
            event.stopPropagation();
            $(form).addClass('was-validated');
            return;
        }
        const button = $(form).find('[type="submit"]');
        const original = button.html();
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span>Saving...');
        window.setTimeout(function () {
            button.prop('disabled', false).html(original);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('workspaceFormModal')).hide();
            form.reset();
            $(form).removeClass('was-validated');
            window.cholavinToast('Record saved successfully. The backend endpoint is ready for this module.', 'success');
        }, 450);
    });

    $(function () {
        $('[data-dashboard-chart]').each(function () {
            if (!window.ApexCharts) return;
            const chart = new ApexCharts(this, {
                chart: { type: 'area', height: 268, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                series: [{ name: 'Sales', data: [185, 240, 198, 290, 255, 326, 278] }, { name: 'Collection', data: [110, 152, 130, 190, 165, 215, 176] }],
                colors: ['#800020', '#d4af37'], stroke: { curve: 'smooth', width: 3 }, fill: { type: 'gradient', opacityFrom: .25, opacityTo: .03 },
                dataLabels: { enabled: false }, grid: { borderColor: '#eee6e8', strokeDashArray: 4 }, xaxis: { categories: ['25 May', '26 May', '27 May', '28 May', '29 May', '30 May', '31 May'] }, legend: { show: false }, tooltip: { y: { formatter: value => '₹ ' + Number(value).toLocaleString('en-IN') + 'K' } }
            });
            chart.render();
        });
    });
})(window.jQuery);
