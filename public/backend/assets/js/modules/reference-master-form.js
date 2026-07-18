(function (window, $) {
    'use strict';

    var $form = $('#form-validate');
    if (!$ || !$form.length || !$.fn.validate) return;
    if ($form.data('validator')) return;

    var rules = {};
    var messages = {};

    $form.find('[name]').each(function () {
        if (this.name === '_token' || this.name === '_method') return;

        if (rules[this.name]) return;

        var $input = $(this);
        var type = (this.type || 'text').toLowerCase();
        var maxlength = Number.parseInt($input.attr('maxlength'), 10);
        var minimum = Number.parseFloat($input.attr('min'));
        var controls = $form.get(0).elements[this.name];
        var groupRequired = controls && typeof controls.length === 'number'
            ? Array.prototype.some.call(controls, function (control) { return control.required === true; })
            : this.required === true;
        var fieldRules = {
            required: groupRequired
        };

        if (type !== 'file') {
            fieldRules.normalizer = function (value) {
                return typeof value === 'string' ? $.trim(value) : value;
            };
        }

        if (type === 'number') {
            fieldRules.number = true;
            if (!Number.isNaN(minimum)) fieldRules.min = minimum;
        }

        if (type === 'date') {
            fieldRules.dateISO = true;
        }

        if (!Number.isNaN(maxlength) && type !== 'number' && type !== 'date') {
            fieldRules.maxlength = maxlength;
        }

        rules[this.name] = fieldRules;
        messages[this.name] = {
            required: 'This field is required.',
            number: 'Enter a valid number.',
            min: 'Enter a value greater than or equal to {0}.',
            maxlength: 'Enter no more than {0} characters.',
            dateISO: 'Enter a valid date.'
        };
    });

    $form.validate({
        ignore: [],
        rules: rules,
        messages: messages,
        errorElement: 'span',
        errorClass: 'error text-danger',
        errorPlacement: function (error, element) {
            var statusOptions = element.closest('.reference-status-options');
            if (statusOptions.length) {
                error.insertAfter(statusOptions);
                return;
            }

            error.insertAfter(element);
        },
        highlight: function (element) {
            var $element = $(element);
            var name = element.name;
            $element.closest('.form-group').addClass('has-error');
            $form.find('[name="' + name + '"]').addClass('is-invalid');
        },
        unhighlight: function (element) {
            var $element = $(element);
            var name = element.name;
            $element.closest('.form-group').removeClass('has-error');
            $form.find('[name="' + name + '"]').removeClass('is-invalid');
        },
        submitHandler: function (element) {
            window.submitFormUsingAjax(element, {
                reset: false,
                onSuccess: function (response) {
                    var redirect = response.redirect || (response.data && response.data.redirect) || $form.data('index-url');
                    window.CholavinNavigation.visit(redirect);
                }
            });
        }
    });
})(window, window.jQuery);
