(function (window, $) {
    'use strict';

    if (!$ || !$.fn.DataTable) return;

    var excludedTables = ['parties-table', 'party-transactions-table'];

    function readable(value) {
        if (value === null || value === undefined || value === '') return '—';
        if (typeof value === 'object') {
            if (value.display !== undefined) return readable(value.display);
            if (value.name !== undefined) return readable(value.name);
            if (value.title !== undefined) return readable(value.title);
            return '—';
        }

        return $.trim($('<div>').html(String(value)).text()) || '—';
    }

    function valueAt(record, path) {
        if (!record || !path) return null;
        return path.split('.').reduce(function (value, segment) {
            return value !== null && value !== undefined ? value[segment] : null;
        }, record);
    }

    function labelFor(column) {
        return readable(column.sTitle || column.mData || '').replace(/[_-]+/g, ' ');
    }

    function usableColumns(settings) {
        return settings.aoColumns.filter(function (column) {
            var key = typeof column.mData === 'string' ? column.mData : '';
            return key && !['DT_RowIndex', 'action', 'actions', 'checkbox', 'image_preview'].includes(key);
        });
    }

    function recordTitle(record, columns) {
        var preferred = ['name', 'product_name', 'user_name', 'document_number', 'number', 'title', 'code'];
        var column = columns.find(function (item) {
            var key = String(item.mData || '').toLowerCase();
            return preferred.some(function (candidate) { return key === candidate || key.endsWith('.' + candidate); });
        }) || columns[0];

        return column ? readable(valueAt(record, column.mData)) : 'Record details';
    }

    function ensureDrawer() {
        var $drawer = $('#erp-record-detail-drawer');
        if ($drawer.length) return $drawer;

        $drawer = $('<div>', {
            id: 'erp-record-detail-drawer',
            class: 'modal fade erp-form-modal erp-record-detail-drawer',
            tabindex: -1,
            'aria-hidden': 'true',
            'aria-labelledby': 'erp-record-detail-title'
        }).html(
            '<div class="modal-dialog modal-dialog-scrollable">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        '<div><span class="erp-eyebrow">Module record</span><h5 id="erp-record-detail-title" class="modal-title">Record details</h5></div>' +
                        '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="modal-body"><div class="erp-record-detail-grid"></div></div>' +
                    '<div class="modal-footer"><div class="erp-record-detail-actions"></div><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>' +
                '</div>' +
            '</div>'
        ).appendTo('body');

        return $drawer;
    }

    function renderDrawer(api, record) {
        var columns = usableColumns(api.settings()[0]);
        var $drawer = ensureDrawer();
        var $grid = $drawer.find('.erp-record-detail-grid').empty();

        $.each(columns, function (_, column) {
            $('<div>', {class: 'erp-record-detail-item'})
                .append($('<span>').text(labelFor(column)))
                .append($('<strong>').text(readable(valueAt(record, column.mData))))
                .appendTo($grid);
        });

        $drawer.find('.modal-title').text(recordTitle(record, columns));
        $drawer.find('.erp-record-detail-actions').html(typeof record.action === 'string' ? record.action : (typeof record.actions === 'string' ? record.actions : ''));
        window.bootstrap.Modal.getOrCreateInstance($drawer.get(0)).show();
    }

    function enhanceTable(api) {
        var $table = $(api.table().node());
        var tableId = $table.attr('id');

        if (!$table.length || excludedTables.includes(tableId) || $table.closest('.modal').length) return;
        if ($table.attr('data-erp-enhanced') === '1') return;

        $table.attr('data-erp-enhanced', '1').addClass('erp-data-table erp-clickable-table');
        $table.closest('.card').addClass('erp-table-workspace');
        $table.closest('.dataTables_wrapper').addClass('erp-datatable-shell');

        $table.off('click.erpRecordDrawer').on('click.erpRecordDrawer', 'tbody tr', function (event) {
            if ($(event.target).closest('a, button, input, select, textarea, label, .dropdown-menu').length) return;

            var record = api.row(this).data();
            if (!record) return;

            $table.find('tbody tr').removeClass('is-selected');
            $(this).addClass('is-selected');
            renderDrawer(api, record);
        });
    }

    $(document).on('init.dt.erpModuleWorkspace draw.dt.erpModuleWorkspace', function (event, settings) {
        window.setTimeout(function () { enhanceTable(new $.fn.dataTable.Api(settings)); }, 0);
    });

    $(function () {
        $.each($.fn.dataTable.tables(), function (_, table) { enhanceTable($(table).DataTable()); });
    });
})(window, window.jQuery);
