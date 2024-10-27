(function($, _) {
  'use strict';


  $(function () {

    var
        amofiles = [],
        $forms = $('.amoform_submit_form'),
        $limit = $('.js-is-limited'),
        $date_inputs = $('.amoforms__date .amoforms__text-input'),
        $error_wrapper = $('#amoforms__error_message_wrapper'),
        $stars_wrapper = $('.amoforms__rating-stars'),
        $antispam_wrapper = $('.amoforms__antispam'),
        $modals = $('[data-modal]'),
        $error_text = $('#amoforms__error_message_text'),
        $antispam_error_text = 'Wrong antispam answer!',
        $network_error = 'Lost connection to server. Please try again later!',
        $file_error = 'Lost connection to server. File upload failed!';


    $('.amoforms-currency').autoNumeric({aSep: ',', aDec: '.'});
    /**
     * Tooltips
     */
    $('.tooltip-form').tooltipster({
      theme: 'tooltipster-shadow',
      position: 'top-right',
      maxWidth: 200,
      multiple: true
    });

    /**
     * Update Analytics Field
     */
    function updateAnalyticsField() {
      if (AMOFORMS.modules && AMOFORMS.modules.analytics && AMOFORMS.modules.analytics.initData) {
        $('.amoforms__analytics_field').val(AMOFORMS.modules.analytics.initData().getJsonData());
      }
    }

    updateAnalyticsField();
    description_pos();

    function getFormData(form) {
      updateAnalyticsField();
      var arr = [];
      var serialized = $(form).serializeArray(),
          oldName = '',
          k = 0;
      for (var i = 0; i < serialized.length; i++) {
        var name = serialized[i].name,
            value = serialized[i].value;

        if (oldName == name) {
          oldName = name;
          name = name + k;
          arr[name] = value;
          k++;
        }else {
          arr[name] = value;
          oldName = name;
        }
      }
      return arr;
    }

    $(".amoforms__forms_hint").each(function() {
      var $tooltip = $(this),
          $sibling = $tooltip.siblings(".amoforms_field_element"),
          parentBgColor = $sibling.css('background-color');

      if (parentBgColor && parentBgColor.indexOf('rgb') != -1) {
        parentBgColor = AMOFORMS.core.fn.rgb2hex(parentBgColor);
      }
      if(parentBgColor && AMOFORMS.core.fn.isDarkColor(parentBgColor)){
        $tooltip.addClass('white');
      } else {
        $tooltip.removeClass('white');
      }
    });

    $forms.each(function() {
      var form = this,
          dropzone;
      if ($(form).find('.amoforms_dropzone').length > 0) {
        form.dropzones = [];
        $(form).find(".amoforms_dropzone").each(function() {
          var $self = $(this),
              filesize = $self.find('span.amoforms__file_size').text(),
              extensions = $self.find('span.amoforms__file_types').text();
          $self.css({"min-height" : $self.css("height"), "height" : "auto"});
          var $dropzone = $self.dropzone({
            url: ajaxurl + "?action=amoforms_submit&controller=Form",
            paramName: function (n) {
              return amofiles[n].field_name + '[' + n + ']';
            },
            autoProcessQueue: false,
            uploadMultiple: true,
            parallelUploads: 1,
            maxFiles: 100,
            maxFilesize: filesize || 1,
            acceptedFiles: extensions,
            accept: function(file, done) {
              if ($self.hasClass('amoforms_dropzone_required')) {
                $self.removeClass('invalid_field');
                $self.parent().find('.error.req').remove();
                return done();
              }
              return done();
            },
            previewTemplate: document.getElementById('amoforms__file-input__preview').innerHTML,
            init: function() {
              this.on('addedfile', function(file) {
                var template, FR;
                if (file.type.match(/image.*/) && file.size <= this.options.maxThumbnailFilesize * 1024 * 1024) {
                  template = file.previewTemplate;
                  FR = new FileReader();
                  FR.onload = function(e) {
                    $(template).find('.dz-image-preview').attr('src',e.target.result);
                    $(template).find('.amoforms__file-input__preview__icon').hide();
                  };
                  FR.readAsDataURL( file );
                } else {
                  template = file.previewTemplate;
                  $(template).find('.dz-image-preview').hide();
                }
              });
              this.on('removedfile', function() {
                if ($self.hasClass('amoforms_dropzone_required')) {
                  if (this.getAcceptedFiles().length <= 0) {
                    $self.addClass('invalid_field');
                    $self.parent().find('.error.req').remove();
                    $self.after('<p class="error req">Field is required</p>');
                  }
                }
              });
            },
            success: function(file, data) {
              form_submitted = false;
              if (data.result === true) {
                switch (data.type) {
                  case 'redirect' :
                    if (!/^.+:\/\/.+/gi.test(data.value)) {
                      data.value = 'http://' + data.value;
                    }
                    window.location.href = data.value;
                    break;

                  case 'html':
                    $(form).replaceWith(data.value);
                    break;
                }
              } else {
                showError($(form), $file_error);
              }
            }
          });
          dropzone = {
            'dropzone': $dropzone,
            'required': $self.hasClass('amoforms_dropzone_required')
          };
          form.dropzones.push(dropzone);
        });
      }
    });

    var showError = function (form, $text) {
          var $form = $(form),
              $submit = $form.find('.amoforms__fields__row-submit');

          $error_text.text($text);
          $error_wrapper.show();
          $submit.addClass('animated shake');

          setTimeout(function () {
            $error_wrapper.fadeOut(500);
          }, 5000);
          setTimeout(function () {
            $submit.removeClass('animated shake');
          }, 800);
        },
        toggleSubmitLoader = function ($form, on) {
          var $button = $form.find('.amoforms__fields__row__submit');
          if ($button.length) {
            $button.toggleClass('loading', Boolean(on));
          }
        },
        validateDate = function (e) {
          validateByType($(e.target), 'validDate');
        },
        validateUrl = function (e) {
          validateByType($(e.target), 'validUrl');
        },
        validateEmail = function (e) {
          validateByType($(e.target), 'validEmail');
        },
        validateByType = function ($this, type) {
          var val = $.trim($this.val()),
              result = !val || (val && AMOFORMS.core.fn[type](val)),
              error_text = [];

          $this[!result ? 'addClass' : 'removeClass']('invalid_field');

          $this.parent().children('p.error').remove();
          if (!result) {
            switch (type) {
              case 'validDate': error_text = 'Date is not correct'; break;
              case 'validUrl': error_text = 'Url is not correct'; break;
              case 'validEmail': error_text = 'Email is not correct'; break;
            }
            $this.after('<p class="error">' + error_text + '</p>');
          }

          return result;
        },
        form_submitted = false;

    // selects
    $('.amoforms__select select').selectize({
      allowEmptyOption: true
    });

    $('.amoforms .js-is-url').on('change', validateUrl);
    $('.amoforms .js-is-email').on('change', validateEmail);
    $date_inputs.on('change', validateDate);

    /**
     * Required fields handler
     */
    $('.amoforms :input.js-is-required')
        .on('submit:change', function () {
          var $this = $(this),
              input_name = $this.attr('name'),
              $after = $this,
              type = (this.type ? this.type : this.tagName).toString().toLowerCase(),
              is_check_or_radio = $.inArray(type, ['radio', 'checkbox']) >= 0;

          if ((!is_check_or_radio && !$this.val()) ||
              (is_check_or_radio && !$('.amoforms input[name="' + input_name + '"]:checked').length)
          ) {
            $this.addClass('invalid_field');

            if (is_check_or_radio) {
              $after = $this.closest('.amoforms__fields__row__inner__name-checkbox, .amoforms__radio-control');
            }
            if ($.inArray(type, ['select', 'select-multiple']) >= 0) {
              $after = $this.closest('.amoforms__select');
            }
            if (!$after.next('.error.req').length) {
              $after.after('<p class="error req">Field is required</p>');
            }
          }
        })
        .on('change', function (e, from_required) {
          var $this = $(this),
              input_name = $this.attr('name'),
              type = (this.type ? this.type : this.tagName).toString().toLowerCase(),
              is_check_or_radio = $.inArray(type, ['radio', 'checkbox']) >= 0;

          if (from_required) {
            return;
          }

          if ($this.hasClass('invalid_field') &&
              ((!is_check_or_radio && $this.val()) || (is_check_or_radio && $('.amoforms input[name="' + input_name + '"]:checked').length))
          ) {
            if (is_check_or_radio) {
              $('.amoforms input[name="' + input_name + '"]').removeClass('invalid_field');
              $this.closest('.amoforms__fields__row__inner__name-checkbox, .amoforms__radio-control')
                  .next('.error.req')
                  .remove();
            } else {
              if ($.inArray(type, ['select', 'select-multiple']) >= 0) {
                $this.closest('.amoforms__select')
                    .next('.error.req')
                    .remove();

                $this.closest('.amoforms__select')
                    .children('.amoforms__select__input')
                    .removeClass('invalid_field');
              } else {
                $this
                    .removeClass('invalid_field')
                    .trigger('change', [true])
                    .next('.error.req')
                    .remove();
              }
            }
          }
        });

    /**
     * Numeric validator
     */
    $('.amoforms__text-input.js-is-number').on('keyup input blur', function() {
      var $this = $(this),
          cleaned_value = $this.val().replace(/[^0-9\.,]+/, '');
      if ($this.val() !== cleaned_value) {
        $this.val(cleaned_value);
        return false;
      }
      validateByType($this, 'validNumber');
    });

    /**
     * Date picker
     */
    $date_inputs.pickmeup({
      first_day: 0,
      format: 'm/d/Y',
      hide_on_select: true,
      default_date: false,
      trigger_event: 'click touchstart focus',
      change: function () {
        $(this).trigger('change');
      }
    });

    /**
     * Submit handler
     */
    $forms.submit(function (e) {
      e.preventDefault();
      var $has_errors,
          form = this,
          amoforms_dropzones = $(form).find('.amoforms_dropzone'),
          recaptcha = $(form).find('.g-recaptcha-response');

      $(form).find('.js-is-required').trigger('submit:change');

      if (amoforms_dropzones.length > 0) {
        _.each(form.dropzones, function(el) {
              if (el.required) {
                if (el.dropzone.context.dropzone.getAcceptedFiles().length <= 0) {
                  el.dropzone.parent().find('.error.req').remove();
                  el.dropzone.after('<p class="error req">Field is required</p>');
                  el.dropzone.addClass('invalid_field');
                }
                else {
                  el.dropzone.removeClass('invalid_field').removeAttr('style');
                  el.dropzone.parent().find('.error.req').remove();
                }
              }
            }
        );
      }

      if (recaptcha.length > 0) {
        if (recaptcha.val().length <= 0) {
          recaptcha.addClass('invalid_field');
          recaptcha.parent().find('.error.req').remove();
          recaptcha.after('<p class="error req">Invalid reCAPTCHA</p>');
        }
        else {
          recaptcha.parent().find('.error.req').remove();
          recaptcha.removeClass('invalid_field');
        }
      }

      $has_errors = $(form).find('.invalid_field:first');

      if (form_submitted || $has_errors.length) {
        if ($has_errors.length) {
          $('html, body').animate({
            scrollTop: $has_errors.offset().top - 40
          }, 300);
        }
        showError($(form), $network_error);
        return;
      }

      if (amoforms_dropzones.length > 0) {
        amofiles = [];
        _.each(form.dropzones, function(el) {
              if (el.required) {
                if (el.dropzone.context.dropzone.getAcceptedFiles().length <= 0) {
                  el.dropzone.parent().find('.error.req').remove();
                  el.dropzone.after('<p class="error req">Field is required</p>');
                  el.dropzone.addClass('invalid_field');
                }
                else {
                  el.dropzone.removeClass('invalid_field').removeAttr('style');
                  el.dropzone.parent().find('.error.req').remove();
                  _.each(el.dropzone.context.dropzone.getAcceptedFiles(), function (file) {
                    file.field_name = el.dropzone.find('input').attr('name');
                    amofiles.push(file);
                  });
                }
              } else {
                _.each(el.dropzone.context.dropzone.getAcceptedFiles(), function (file) {
                  file.field_name = el.dropzone.find('input').attr('name');
                  amofiles.push(file);
                });
              }
            }
        );
        if (amofiles.length > 0) {
          form.dropzones[0].dropzone.context.dropzone.options.params = getFormData(form);
          form.dropzones[0].dropzone.context.dropzone.processFiles(amofiles);
        }
        else {
          sendForm();
        }
      }
      else {
        sendForm();
      }

      form_submitted = true;

      function sendForm() {
        updateAnalyticsField();
        var $form = $(form),
            formData = $form.serialize();

        toggleSubmitLoader($form, true);

        $.ajax({
          type: 'POST',
          cache: false,
          url: ajaxurl + '?action=amoforms_submit&controller=Form',
          data: formData,
          dataType: 'json',
          success: function (data) {
            toggleSubmitLoader($form, false);
            form_submitted = false;
            if (data.result === true) {
              switch (data.type) {
                case 'redirect' :
                  if (!/^.+:\/\/.+/gi.test(data.value)) {
                    data.value = 'http://' + data.value;
                  }
                  window.location.href = data.value;
                  break;
                case 'html':
                  $(form).replaceWith(data.value);
                  setTimeout(hide_modals, 1000);
                  break;
              }
            }
            else {
              if (data.stoken) {
                var $recaptcha = $(form).find('.g-recaptcha'),
                    recaptcha_id = $recaptcha.attr('id');
                $recaptcha.html('');
                grecaptcha.render(recaptcha_id, {
                  sitekey: AMOFORMS.captcha.site_key,
                  stoken: data.stoken
                });
                return;
              }
              if (data.antispam == false){
                $antispam_wrapper.find('input').addClass('invalid_field').after('<p class="error">' + $antispam_error_text + '</p>');
              }

              showError($(form), data.message);
            }
          },
          error: function () {
            toggleSubmitLoader($form, false);
            form_submitted = false;
            showError($(form), $network_error);
          }
        });
      }
    });
    // end of submit handler

    /**
     * Limit
     */
    $limit.on('keyup input', function() {
      var $this = $(this),
          max = $this.attr('maxlength'),
          chars = $this.val().length,
          rest = max - chars,
          $wrapper = $this.parents('.amoforms__fields__row'),
          $inner = $wrapper.find(".amoforms__fields__row__inner__control"),
          $input = $inner.find(".amoforms__text-input");
          $wrapper.find('.amoforms__fields__row__inner__descr').hide();
          $wrapper.find('.amoforms__fields__row__inner__limit').text('Chars left: ' + rest)
              .show()
              .css("margin-left", ($inner.position().left+parseInt($input.css('marginLeft'), 10)) + "px");
    });
    $limit.on('blur change', function() {
      var $this = $(this),
          $wrapper = $this.parentsUntil('.amoforms__fields__row');
      $wrapper.children('.amoforms__fields__row__inner__descr').show();
      $wrapper.children('.amoforms__fields__row__inner__limit').hide();
    });


    /*
    * Stars
    * */

    $stars_wrapper.on('click', 'span.amoforms_rating_star', function(){
      var $star = $(this),
          $wrapper = $star.parent();
      $star.siblings( 'span' ).removeClass( 'pressed' );
      $star.prevAll().addClass('pressed');
      $star.addClass( 'pressed' );
      $wrapper.find('.amoforms__rating-input').val($star.attr('data-number'));
    });

    // end of stars

    // Antispam
    $antispam_wrapper.on('keyup input blur', function(){
      $(this).find('input').removeClass('invalid_field');
      $(this).find('p').remove();

    });
    // end of antispam

    $forms.each(function() {
      var $form = $(this),
          $calculations = $form.find('[data-calculate]'),
          $total_input = $form.find('input.amoforms.calculations_total'),
          $total_span = $form.find('span.amoforms.calculations_total'),
          $tax_wrapper = $form.find('div.amoforms__form_calculations.tax').first(),
          tax = parseFloat($tax_wrapper.text(), 10)/100;
      if ($calculations.length > 0) {
        $calculations.on('change', function() {
          var total = 0,
              count = 0;
          $calculations.each(function() {
            if($(this).val() != ''){
              count = $(this).val().replace(",",".");
              count = parseFloat(count, 10);
            } else{
              return true;
            }
            if(!isNaN(count)){
              total += count;
            }
          });
          var total_tax = (total*tax),
              total_sum = (total+total_tax).toFixed(2);
          $total_input.val(total_sum);
          $total_span.text(total_sum);
        });
      }
    });

    $modals.on('click', function() {
      var id = $(this).data('modal');
      $('#'+id).modal('show');
      description_pos();
    });

    function hide_modals() {
      $modals.each(function() {
        var id = $(this).data('modal');
        $('#'+id).modal('hide');
      });
    }

    function description_pos() {
      $('.amoforms__fields__row__inner__descr').each(function () {
        var $descr = $(this);
        if($descr.is(":visible")){
          var $inner = $descr.siblings().find(".amoforms__fields__row__inner__control"),
              $input = $inner.find(".amoforms__text-input");
          if($inner.length == 1 && $input.length == 1){
            $descr.css("left", ($inner.position().left+parseInt($input.css('marginLeft'), 10)) + "px");
          }
        }
      });
    }
  });
})(jQuery, _);
