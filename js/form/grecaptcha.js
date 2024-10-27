window.amoformsCaptchaOnloadCallback = function() {
  var $ = jQuery;
    $(document).ready(function() {
      var recaptchas = [];
          $('.amoform_submit_form').each(function() {
            var $form = $(this),
                recaptcha = $form.find('.g-recaptcha'),
                recaptcha_id;
            if (recaptcha.length > 0) {
                recaptcha_id = recaptcha.attr('id');
                recaptchas.push(recaptcha_id);
            }
        });
        for (var i = 0; i < recaptchas.length; i++) {
            grecaptcha.render(recaptchas[i], {
                sitekey: AMOFORMS.captcha.site_key,
                stoken: AMOFORMS.captcha.stokens[i],
            });
        }
    });
};
