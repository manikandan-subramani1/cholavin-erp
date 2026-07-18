(function ($) {
    'use strict';
    var $root = $('#sessions-module');
    if (!$root.length || !$) return;

    var table = initializeDataTable({
        selector: '#sessions-table',
        url: $root.data('index-url'),
        order: [[8, 'desc']],
        filters: function () {
            return {user_id: $('#session-user-filter').val(), status: $('#session-status-filter').val()};
        },
        columns: [
            {data: 'DT_RowIndex'},
            {data: 'user_name', name: 'users.name'},
            {data: 'role_name', name: 'roles.name'},
            {data: 'ip_address', name: 'sessions.ip_address'},
            {data: 'user_agent', name: 'sessions.user_agent'},
            {data: 'shop_name', name: 'shops.name'},
            {data: 'godown_name', name: 'godowns.name'},
            {data: 'financial_year_name', name: 'financial_years.name'},
            {data: 'last_activity', name: 'sessions.last_activity'},
            {data: 'status', orderable: false, searchable: false},
            {data: 'action', orderable: false, searchable: false}
        ]
    });

    $('#session-user-filter,#session-status-filter').off('.sessionModule').on('change.sessionModule', function () {
        table.ajax.reload(null, false);
    });
    $('#reset-session-filters').off('.sessionModule').on('click.sessionModule', function () {
        $('#session-filters').trigger('reset');
        table.ajax.reload(null, false);
    });
    $(document).off('click.sessionModule', '.revoke-session').on('click.sessionModule', '.revoke-session', function () {
        var $button = $(this);
        Swal.fire({title: 'Force logout this session?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Force logout'})
            .then(function (result) {
                if (!result.isConfirmed) return;
                CholavinAjax.request({
                    url: $button.data('url'),
                    method: 'DELETE',
                    disableButton: $button,
                    onSuccess: function (response) {
                        toastr.success(response.message);
                        table.ajax.reload(null, false);
                    }
                });
            });
    });
})(window.jQuery);
