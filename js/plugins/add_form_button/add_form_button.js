jQuery(function ($) {

  var FormButton = function () {
    var _this = this;
    this.document = $(document);
    this.forms = window.AMOFORMS && window.AMOFORMS['forms_list'] ? window.AMOFORMS['forms_list'] : [];
    this.modal = $('#amoforms-add-form-modal');
    this.form_select = $('#amoforms-form-select');

    this.document
      .on('click', '#amoforms-add-form-button', function () {
        _this.show_modal();
      })
      .on('click', '#amoforms-select-form-button-ok', function () {
        _this.on_select_form();
      })
      .on('click', '#amoforms-select-form-button-cancel', function () {
        _this.on_cancel_select();
      });
  };

  FormButton.prototype.show_modal = function () {
    this.modal.show();
  };

  FormButton.prototype.on_select_form = function () {
    this.insert_short_code(this.form_select.val());
    this.modal.hide();
  };

  FormButton.prototype.on_cancel_select = function () {
    this.modal.hide();
  };

  FormButton.prototype.insert_short_code = function (form_id) {
    tinymce.activeEditor.execCommand('mceInsertContent', false, '[amoforms id="' + form_id + '"]');
  };

  new FormButton();
});
