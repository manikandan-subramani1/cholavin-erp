(function (window, $) {
    'use strict';
    if (!$ || !$('#user-permissions-form').length) return;
    $('#user-permissions-form').validate({ignore: [], submitHandler: function (element) {
        submitFormUsingAjax(element, {reset: false, onSuccess: function (response) { toastr.success(response.message); }});
    }});
})(window, window.jQuery);
