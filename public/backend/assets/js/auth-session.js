(function (window, $) {
    'use strict';

    if (!$) return;

    $(function () {
        var $form = $('#ajax-logout-form');
        if (!$form.length || typeof window.submitFormUsingAjax !== 'function') return;

        $form.on('submit.authSession', function (event) {
            event.preventDefault();

            window.submitFormUsingAjax(this, {
                reset: false,
                onSuccess: function (response) {
                    window.location.assign(response.data.redirect);
                }
            });
        });
    });
})(window, window.jQuery);
