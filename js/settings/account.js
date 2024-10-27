(function ($) {
  'use strict';

  $(function () {
    var timer_id,
        $submit_button = $('#submit_button'),
        $form = $('#account-settings');

    $submit_button.attr('disabled', true);

    $(document).on('input', '.amoforms input[type="text"], .amoforms input[type="email"]', function (e) {
      var $input = $(e.target),

      id = $input.attr('id');
      clearTimeout(timer_id);

      timer_id = setTimeout(function () {
        var $input_subdomain = $('#form_settings_subdomain'),
            $input_api_key = $('#form_settings_api_key'),
            $submit_button = $('#submit_button'),
            $user_login = $('#account_settings_user_login');

        if ($input_api_key.val() && $input_subdomain.val()) {
          $submit_button.addClass('loading');

          $.post(ajaxurl + '?action=amoforms_has_connection', {
            login: $user_login.val(),
            api_key: $input_api_key.val(),
            subdomain: $input_subdomain.val()
          }).done(function (response) {
            $submit_button.removeClass('loading');

            if (response.response && response.response['has_connection']) {
              $submit_button.attr('disabled', false);
            } else {
              $submit_button.attr('disabled', true);
            }
          });
        } else {
          $submit_button.attr('disabled', true);
        }
      }, 500);
    });
  });
})(jQuery);
