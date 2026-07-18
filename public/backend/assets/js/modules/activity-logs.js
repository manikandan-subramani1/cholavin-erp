(function (window, document, $) {
    'use strict';
    if (!$ || !$('#activity-log-module').length) return;
    var module = $('#activity-log-module');
    var table = initializeDataTable({
        selector: '#activity-log-table', url: module.data('index-url'), filters: function () {
            return {
                user_id: $('#log-user-filter').val(), event: $('#log-event-filter').val(), module: $('#log-module-filter').val(), from_date: $('#log-from-filter').val(), to_date: $('#log-to-filter').val()
            };
        }, columns: [{ data: 'DT_RowIndex' }, { data: 'created_at' }, { data: 'user_name', name: 'user.name' }, { data: 'event' }, { data: 'module' }, { data: 'request', orderable: false, searchable: false }, { data: 'ip_address' }, { data: 'details', orderable: false, searchable: false }], order: [[1, 'desc']]
    });
    $('#log-user-filter,#log-event-filter,#log-module-filter,#log-from-filter,#log-to-filter').on('change', function () { table.ajax.reload(null, false); });
    $('#reset-log-filters').on('click', function () { $('#log-filters').trigger('reset'); table.ajax.reload(null, false); });
})(window, document, window.jQuery);
