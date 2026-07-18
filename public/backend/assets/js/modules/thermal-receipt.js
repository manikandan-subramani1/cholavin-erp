(function (window, $) {
    'use strict';
    if (!$) return;
    var $root = $('#thermal-receipt-module');
    if (!$root.length) return;

    $('#printReceipt').on('click.thermalReceipt', function () { window.print(); });
    $('#paperWidth').on('change.thermalReceipt', function () {
        var url = $root.data('index-url') + '?paper_width=' + encodeURIComponent($(this).val());
        if (window.CholavinNavigation) window.CholavinNavigation.visit(url);
        else window.location.assign(url);
    });
})(window, window.jQuery);
