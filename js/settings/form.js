(function ($, _) {
  'use strict';

  $(function () {
    switchConfirmationType();

    var $form = $('#general-form-settings'),
        serialised_form = $form.serialize(), // State after loading the page
        $submit = $form.find('input[type="submit"]'),
        $modal_btn = $('.amoforms_modal_button'),
        name = $('#amoforms-form-name').val(),
        $save_button = $('.js-modal-save'),
        $modal_cancel =$('.amoforms__modal_editor_cancel'),
        $button_constructor = $('.button_modal_constructor'),
        $edit_field = $('.amoforms__styles__edit-modal');

    $submit.attr('disabled', true);

    $('.tooltip-form-settings').tooltipster({
      theme: 'tooltipster-shadow',
      multiple: true,
      maxWidth: 400,
      position: 'bottom-left',
      contentAsHTML: true
    });

    _.each($('.amoforms__colorsetting'), function (e) {
      var color = $(e).val(),
          transparent = (color == 'transparent');
      $(e).css({
        color: color,
        backgroundColor: color
      });
      $(e).ColorPicker({
        color: $(e).val(),
        isTransparent: transparent,
        onChange: function (rgb, hex_color) {
          if (hex_color !== 'transparent') {
            hex_color = '#' + hex_color;
          }

          $(e)
              .css({
                color: hex_color,
                backgroundColor: hex_color
              })
              .val(hex_color)
              .trigger('input');
        }
      });
    });

    $('.amoforms__modal__style_edit_input').on('input', function (event) {
      renderStyleChange(
          {
            key: /style[\d]{0,}?\[\w+\]\[([\w\d-]+)\]\[([\w\d-]+)\]/gi.exec(event.target.attributes.name.value)[2] || '',
            value: event.target.value
          }
      );
    });

    $('.js-amoforms_form_status_radio_btn_wrapper').on('mousedown touchstart', function (event) {
      event.preventDefault();
      event.stopPropagation();
      var $input = $(event.currentTarget).find('input[type=radio]');
      if (!$input.prop('checked')) {
        new AMOFORMS.core.confirm({
          template_params: {
            caption: 'Do you want to change form status?',
            accept_btn: 'Yes',
            decline_btn: 'No'
          },
          accept: _.bind(function (confirm) {
            $input.prop('checked', true);
            formAjax($form.serialize());
            confirm.done();
          }, this)
        }, this);
      }
    });


    $('.js-amoforms_form_views_radio_btn_wrapper').on('mousedown touchstart', function (event) {
      event.preventDefault();
      event.stopPropagation();
      var $input = $(event.currentTarget).find('input[type=radio]');
      if(!$input.prop('checked')){
        $input.prop('checked', true);
      }
      var new_serialized_form = $form.serialize();
      compareSerializedForms(serialised_form, new_serialized_form);
      switch ($input.val()){
        case 'modal':
          $button_constructor.show('slow');
          break;
        case 'classic':
          $button_constructor.hide('slow');
          break;
      }
    });
    $('[data-action="edit"]').on('click', function() {
        $edit_field.show('slow');
    });
    $modal_cancel.on('click', function(e) {
      if (e && e.preventDefault) {
        e.preventDefault();
      }
      $edit_field.hide('slow');
    });
    $('input[name="form[settings][modal][text]"]').on('keyup input', function() {
      var value = $(this).val();
      if(value != ''){
        $(this).removeClass('error');
        $modal_btn.text(value);
      } else{
        $(this).addClass('error');
      }
    });
    $save_button.on('click', function () {
      $edit_field.hide('slow');
    });

    function renderStyleChange (changed) {
        $modal_btn.css(changed.key, changed.value);
    }

    function toggleSaveLoader(on) {
      $save_button.toggleClass('loading', !!on);
    }

    function switchConfirmationType() {
      var type = AMOFORMS.page_settings.form.type,
          $parent = $('.amoforms__text_editor'),
          template,
          rendered,
          value,
          new_serialized_form;

      switch (type) {
        case 'text' :
          template = $('#amoforms__text_editor__textarea').html();
          Mustache.parse(template);
          value = (type == AMOFORMS.page_settings.form.current_type) ? AMOFORMS.page_settings.form.value : '';
          rendered = Mustache.render(template, {value: value});
          break;

        case 'wp_page' :
          template = $('#amoforms__text_editor__select').html();
          Mustache.parse(template);
          var list_items = AMOFORMS.page_settings.form.list_items;
          rendered = Mustache.render(template, {list_items: list_items});
          break;

        case 'redirect' :
          template = $('#amoforms__text_editor__input').html();
          Mustache.parse(template);
          value = (type == AMOFORMS.page_settings.form.current_type) ? AMOFORMS.page_settings.form.value : '';
          rendered = Mustache.render(template, {value: value});
          break;
      }
      $parent.html(rendered);

      // Re-init tinyMCE
      if (type == 'text') {
        tinymce.remove();

        tinymce.init({
          selector: "#text-editor",
          menubar: false,
          height: 250,
          font_formats: "Andale Mono=andale mono,times;" +
          "Arial=arial,helvetica,sans-serif;" +
          "Arial Black=arial black,avant garde;" +
          "Book Antiqua=book antiqua,palatino;" +
          "Comic Sans MS=comic sans ms,sans-serif;" +
          "Courier New=courier new,courier;" +
          "Georgia=georgia,palatino;" +
          "Helvetica=helvetica;" +
          "Impact=impact,chicago;" +
          "PT Sans=pt sans;" +
          "Symbol=symbol;" +
          "Tahoma=tahoma,arial,helvetica,sans-serif;" +
          "Terminal=terminal,monaco;" +
          "Times New Roman=times new roman,times;" +
          "Trebuchet MS=trebuchet ms,geneva;" +
          "Verdana=verdana,geneva;" +
          "Webdings=webdings;" +
          "Wingdings=wingdings,zapf dingbats",
          toolbar: [
            "undo redo | fontselect | sizeselect | fontsizeselect | forecolor backcolor | bold italic | alignleft aligncenter alignright alignjustify | bullist | numlist | outdent indent "
          ],
          fontsize_formats: "8px 10px 12px 14px 16px 18px 20px 22px 24px 36px",
          resize: false,
          setup: function(editor) {
            editor.on('nodechange', function() {
              tinyMCE.triggerSave();
              AMOFORMS.page_settings.form.value = $('#text-editor').val();
              new_serialized_form = $form.serialize();
              compareSerializedForms(serialised_form, new_serialized_form);
            });
          },
          plugins: "image textcolor"
        });
      } else if (type == 'wp_page') {
        $('.amoforms__text_editor__select').change(function() {
          AMOFORMS.page_settings.form.value = $(this).val();
          new_serialized_form = $form.serialize();
          compareSerializedForms(serialised_form, new_serialized_form);
        });
      } else if (type == 'redirect') {
        $('.amoforms__text_editor__input').change(function () {
          AMOFORMS.page_settings.form.value = $(this).val();
          new_serialized_form = $form.serialize();
          compareSerializedForms(serialised_form, new_serialized_form);
        });
      }
    }

    function formAjax(serialized) {
      toggleSaveLoader(true);
      $.ajax({
        type: 'POST',
        cache: false,
        url: ajaxurl + '?action=amoforms_update_form',
        data: serialized,
        dataType: 'JSON',
        success: function (data) {
          toggleSaveLoader(false);
          if (data.result === true) {
            if (name !== $('#amoforms-form-name').val()) {
              location.reload();
              return;
            }
            $('.amoforms__success_message').show();
            $submit.attr('disabled', true);
            serialised_form = serialized;
            AMOFORMS.page_settings.form.current_type = AMOFORMS.page_settings.form.type;

            // Clear selects
            for (var i = 0; i < AMOFORMS.page_settings.form.list_items.length; i++) {
              var obj = AMOFORMS.page_settings.form.list_items[i];
              if (obj.selected == 'selected') {
                obj.selected = '';
              }
            }

            // If confirmation type with select, then set new selected value
            if (AMOFORMS.page_settings.form.type == 'wp_page'){
              var ind = $('.amoforms__text_editor__select').find('option:selected').index();
              AMOFORMS.page_settings.form.list_items[ind].selected = 'selected';
            }
          } else {
            $('.amoforms__error_message').show();
          }

          $('html, body').animate({'scrollTop': 0});

          setTimeout(function() {
            $('.amoforms__message').fadeOut(500);
          }, 5000);
        },
        error: function (xhr, status, http_error) {
          AMOFORMS.core.errors.sendErrorAjax(xhr, status, http_error, action, data);
        }
      });
    }

    function compareSerializedForms(serialized, new_serialized) {
      $submit.attr('disabled', serialized == new_serialized);
    }

    $form.find('input[type="checkbox"], input[type="radio"], input[type="text"], textarea, select').on('change keyup', function() {
      var $el = $(this);
      if ($el.hasClass('amoforms__form-setting__check')) {
        AMOFORMS.page_settings.form.type = $el.val();
        switchConfirmationType();
      }

      var new_serialized_form = $form.serialize();
      compareSerializedForms(serialised_form, new_serialized_form);
    });

    $form.submit(function (e) {
      e.preventDefault();
      tinyMCE.triggerSave(); //Save tinyMCE to textarea
      var serialized = $form.serialize();
      formAjax(serialized);
    });
  });
})(jQuery, _);
