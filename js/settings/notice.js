(function ($) {
    'use strict';

    $(function () {

        var $button = $('[data-action]');

        $($button).on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            var notice = $(this).parents('.amoforms_notice'),
                action = $(this).data('action'),
                type = notice.attr('id');
            sendAjax(action, type);
            notice.hide();
        });

        function sendAjax(action, type) {
            $.ajax({
                type: 'POST',
                cache: false,
                url: ajaxurl + '?action=amoforms_' + action + '_notice&type=' + type,
                dataType: 'JSON',
                error: function (xhr, status, http_error) {
                    AMOFORMS.core.errors.sendErrorAjax(xhr, status, http_error, action);
                }
            });
        }
    });
})(jQuery);
