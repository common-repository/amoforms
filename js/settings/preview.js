(function (global, $, _, Backbone) {
  'use strict';

  var PreviewPage = {};

  PreviewPage.View = Backbone.View.extend({
    selectors: {
      panel_item: '.amoforms__fields__expander__item'
    },

    events: {
      'click .amoforms__fields__expander__item:not(.expanded)': '_expand_panel',
      'click #amoforms_save_css_btn': '_handle_save_click',
      'click #amoforms_save_js_btn': '_handle_save_click',
      'click #amoforms_test_js_btn': '_test_js_click'
    },

    'const': {
      endpoints: {
        save: 'update_custom_code_settings'
      }
    },

    params: {
      ajax_url: global.ajaxurl,
      ajax_timeout: 20 * 1000
    },

    settings: {
      css: '',
      js: ''
    },

    elements: {
      document: $(document),
      html_body: $('html, body'),

      forms: {
        custom_css: $('#amoforms_custom_css_form'),
        custom_js: $('#amoforms_custom_js_form')
      },
      inputs: {
        css_area: $('#amoforms_css_area'),
        js_area: $('#amoforms_js_area')
      },
      css_style: $('style.amoforms_custom_css_style'),
      custom_js_wrapper: $('.amoforms_custom_js_wrapper'),
      buttons: {
        save_css: $('#amoforms_save_css_btn'),
        save_js: $('#amoforms_save_js_btn')
      },
      messages: {
        success: {
          wrapper: $('#amoforms__success_message_wrapper'),
          text: $('#amoforms__success_message_text')
        },
        error: {
          wrapper: $('#amoforms__error_message_wrapper'),
          text: $('#amoforms__error_message_text')
        }
      }
    },

    initialize: function () {
      this._expand_panel({
        currentTarget: this.$(this.selectors['panel_item'] + ':first')
      });
      this._update_local_settings('css');
      this._update_local_settings('js');

      this.elements.inputs.css_area.on('change keyup paste keydown', _.bind(function () {
        this._handle_change_css_area();
      }, this));

      this.elements.inputs.js_area.on('change keyup paste keydown', _.bind(function () {
        this._handle_change_js_area();
      }, this));

      this.elements.document.on('amoforms:custom_js:exception', _.bind(function ($event, message) {
        this._show_error('Custom JS error' + (message ? ': ' + message : ''));
        this._toggle_save_button('js', false);
      }, this));
    },

    // Handlers

    _expand_panel: function ($event) {
      var $this = $($event.currentTarget);

      this
        .$(this.selectors['panel_item'] + '.expanded')
        .removeClass('expanded')
        .find('.amoforms__fields__expander__item__content')
        .css('min-height', '');

      $this
        .addClass('expanded')
        .find($this.find('.amoforms__fields__expander__item__content'))
        .css('min-height', $this.find('.amoforms__fields__expander__item__content__inner')[0].offsetHeight);

      this._scroll_to_top();
    },

    _handle_change_css_area: function () {
      var raw_css = this.elements.inputs.css_area.val(),
        new_css = this._sanitize_css(raw_css);

      if (raw_css !== new_css) {
        this.elements.inputs.css_area.val(new_css);
      }

      this.elements.css_style.text(new_css);
      this._toggle_save_button('css', new_css !== this.settings.css);
    },

    _handle_change_js_area: function () {
      this._toggle_save_button('js', this.elements.inputs.js_area.val() !== this.settings.js);
    },

    _test_js_click: function () {
      var code = this.elements.inputs.js_area.val();
      this.elements.custom_js_wrapper.empty();
      this.elements.custom_js_wrapper.append(
        '<script class="amoforms_custom_js_script">' +
        '(function () {' +
        '  try {' + code + '} catch (e) {' +
        '    console.error(e);' +
        '    jQuery(document).trigger("amoforms:custom_js:exception", e.message);' +
        '  }' +
        '})();' +
        '</script>'
      );
    },

    _handle_save_click: function ($event) {
      var $el = $($event.currentTarget),
        type = $el.data('type');

      if (type === 'js') {
        this._test_js_click();
      }

      if (this.settings[type] === undefined
        || !this._has_changes(type)
        || this._save_button_is_loading(type)
        || this._save_button_is_disabled(type)) {
        $event.preventDefault();
        $event.stopPropagation();
        return false;
      }

      this._save(type);
    },

    // Toggles

    /**
     * @param {String} type - css / js
     * @param {Boolean} on
     * @private
     */
    _toggle_save_button: function (type, on) {
      type = 'save_' + type;
      if (this.elements.buttons[type]) {
        this.elements.buttons[type].prop('disabled', !on);
      }
    },

    /**
     * @param {String} type - css / js
     * @param {Boolean} on
     * @private
     */
    _toggle_save_button_loader: function (type, on) {
      type = 'save_' + type;
      if (this.elements.buttons[type]) {
        this.elements.buttons[type].toggleClass('loading', !!on);
      }
    },

    // Internal methods

    /**
     * @param {String} type - css / js
     * @return {Boolean}
     * @private
     */
    _save_button_is_loading: function (type) {
      return this.elements.buttons['save_' + type].hasClass('loading');
    },

    /**
     * @param {String} type - css / js
     * @return {Boolean}
     * @private
     */
    _save_button_is_disabled: function (type) {
      return this.elements.buttons['save_' + type].is(':disabled');
    },

    /**
     * @param {String} type - css / js
     * @return {Boolean}
     * @private
     */
    _has_changes: function (type) {
      return this.settings[type] !== this.elements.inputs[type + '_area'].val();
    },

    /**
     * Update local css settings
     * @param {String} type - css / js
     * @private
     */
    _update_local_settings: function (type) {
      this.settings[type] = this.elements.inputs[type + '_area'].val();
    },

    /**
     * @param {String} css
     * @return {String}
     * @private
     */
    _sanitize_css: function (css) {
      if (css.indexOf('<') > -1) {
        css = css.replace(/<+/, '');
      }
      return css;
    },

    /**
     * Save settings on server
     * @param {String} type - css / js
     * @private
     */
    _save: function (type) {
      if (this.settings[type] === undefined) {
        return false;
      }

      this._toggle_save_button_loader(type, true);

      this._send_request(this.const.endpoints.save, this.elements.forms['custom_' + type].serialize(),
        // on success
        _.bind(function (response) {
          this._toggle_save_button_loader(type, false);
          this._toggle_save_button(type, false);

          if (!response || !_.isObject(response) || response.result !== true) {
            this._show_error(_.isObject(response) && response.message ? response.message : 'An error has occurred');
            return;
          }

          this._update_local_settings(type);
          this._show_message('Changes successfully saved!');

        }, this),
        // on error
        _.bind(function () {
          this._toggle_save_button_loader(type, false);
        }, this));
    },

    /**
     * Send request to server
     * @param {String} action
     * @param {Object} data
     * @param {Function} success_callback
     * @param {Function} [error_callback]
     * @private
     */
    _send_request: function (action, data, success_callback, error_callback) {
      var _this = this;
      $.ajax({
        type: 'POST',
        url: _this.params.ajax_url + '?controller=settings&action=amoforms_' + action,
        data: data,
        dataType: 'json',
        timeout: _this.params.ajax_timeout,
        success: success_callback,
        error: function (xhr, status, http_error) {
          AMOFORMS.core.errors.sendErrorAjax(xhr, status,http_error, action, data);

          _this._show_error('Error: ' + http_error);
          if ($.isFunction(error_callback)) {
            error_callback(xhr, status, http_error);
          }
        }
      });
    },

    /**
     * Show message
     * @param {String} message
     * @param {Boolean} [is_error]
     * @private
     */
    _show_message: function (message, is_error) {
      var wrapper = is_error ? this.elements.messages.error.wrapper : this.elements.messages.success.wrapper,
        paragraph = is_error ? this.elements.messages.error.text : this.elements.messages.success.text;
      paragraph.text(message);
      wrapper.show();
      this._scroll_to_top();
      setTimeout(function () {
        wrapper.fadeOut(500);
      }, 5000);
    },

    /**
     * Show error
     * @param {String} message
     * @private
     */
    _show_error: function (message) {
      this._show_message(message, true);
    },

    /**
     * Scroll page to top
     * @private
     */
    _scroll_to_top: function () {
      this.elements.html_body.animate({scrollTop: 0});
    }
  });

  global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
    views: {
      PreviewPage: PreviewPage
    }
  });
})
(window, jQuery, _, Backbone);


(function ($, global) {
  'use strict';

  $(function () {
    new global.AMOFORMS.views.PreviewPage.View({el: document.getElementById('amoforms_content')});
  });
})(jQuery, window);
