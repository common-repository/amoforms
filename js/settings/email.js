// WARNING! This file is used on a page "Email Settings" and on page "First Setup"!
(function (global, $, _, Backbone) {
  'use strict';
  var
    AMOFORMS = global.AMOFORMS,
    EmailSettings = {};

  EmailSettings.View = Backbone.View.extend({

    fn: AMOFORMS.core.fn,

    'const': {
      input_validation_delay: 500,
      account_actions: {
        register: AMOFORMS.page_settings.account_actions['register'],
        update: AMOFORMS.page_settings.account_actions['update']
      },
      account_settings_states: {
        edit: 'edit',
        connected: 'connected',
        just_registered: 'just_registered',
        connection_error: 'connection_error'
      },
      endpoints: {
        save: 'update_email'
      },
      triggers: {
        saved: 'amoforms:email_settings:saved'
      }
    },

    settings: {
      account: {
        login: AMOFORMS.page_settings.account.login,
        subdomain: AMOFORMS.page_settings.account.subdomain,
        api_key: AMOFORMS.page_settings.account.api_key,
        registered: Boolean(AMOFORMS.page_settings.account.registered),
        url: AMOFORMS.page_settings.account.url,
        short_url: AMOFORMS.page_settings.account.short_url
      },
      is_blocked: AMOFORMS.page_settings.is_blocked, //TODO: use or remove
      show_stats_reporting: AMOFORMS.page_settings.show_stats_reporting
    },

    params: {
      ajax_timeout: 20 * 1000,
      login_validation_timer_id: null,
      auth_validation_timer_id: null,
      is_valid: true,
      loading_in_process: false
    },

    ids: {
      email_name: 'amoforms_email_name',
      email_subject: 'amoforms_email_subject',
      email_to: 'form_settings_email_to',
      phone: 'form_settings_phone',
      phone_full: 'form_settings_phone_full',
      phone_block: 'form_settings_phone_block',
      login: 'account_settings_user_login',
      subdomain: 'form_settings_subdomain',
      api_key: 'form_settings_api_key',
      account_action: 'amoforms_account_action'
    },

    elements: {
      document: $(document),

      text_inputs: [],
      text_email_inputs: [],

      checkboxes: {
        stats_reporting: $('#stats_reporting_checkbox')
      },

      // blocks
      html_body: $('html, body'),
      form: $('#email-settings'),
      stats_reporting_wrapper: $('#amoforms__stats_reporting_wrapper'),
      account_settings: $('#amo_account_settings'),
      account_inputs_wrapper: $('#amo_account_inputs_wrapper'),

      messages: {
        success: {
          wrapper: $('#amoforms__success_message_wrapper'),
          text: $('#amoforms__success_message_text')
        },
        error: {
          wrapper: $('#amoforms__error_message_wrapper'),
          text: $('#amoforms__error_message_text')
        }
      },

      buttons: {
        show_terms: $('#show_terms'),
        go_to_amo_account: $('.js_amoforms_go-to-amocrm-account_link'),
        change_account_settings: $('#amoforms_change-account-settings-btn'),
        save: $('#submit_button')
      },

      texts: {
        api_keys: $('.amoforms_api_key_value'),
        account_url: $('.js-amoforms_account_url'),
        amoforms_offer: $('#amoforms_offer')
      }
    },

    init_params: function () {
      this.params.need_auth = this.settings.account.registered;
      this.params.disabled_mark_invalid_inputs = !this.settings.account.registered; // we will not mark invalid inputs if user is not registered or not attempt to save settings
    },

    init_elements: function () {
      var _this = this;

      $.extend(this.elements, {
        // inputs
        email_name: $('#' + _this.ids.email_name),
        email_subject: $('#' + _this.ids.email_subject),
        email_to: $('#' + _this.ids.email_to),
        phone: $('#' + _this.ids.phone),
        phone_full: $('#' + _this.ids.phone_full),
        phone_block: $('#' + _this.ids.phone_block),
        login: $('#' + _this.ids.login),
        subdomain: $('#' + _this.ids.subdomain),
        api_key: $('#' + _this.ids.api_key),

        // hidden inputs
        account_action: $('#' + this.ids.account_action)
      });

      this.elements.text_inputs = [
        this.elements.email_name,
        this.elements.email_subject,
        this.elements.email_to,
        this.elements.login,
        this.elements.subdomain,
        this.elements.api_key
      ];

      this.elements.text_email_inputs = [
        this.elements.email_name,
        this.elements.email_subject
      ];
    },

    initialize: function () {
      this.init_email_settings();
    },

    init_email_settings: function () {
      var _this = this;

      this.init_params();
      this.init_elements();

      // bind events
      this.elements.buttons.change_account_settings.on('click', function (event) {
        event.preventDefault();
        _this.change_account_settings_state(_this.const.account_settings_states.edit);
      });

      this.elements.buttons.go_to_amo_account.on('click', function (event) {
        event.preventDefault();
        if (_this.settings.account['url']) {
          window.open(_this.settings.account['url'], '_blank');
        }
      });

      this.elements.buttons.show_terms.on('click', function (event) {
        event.preventDefault();
        _this.toggle_offer();
      });

      this.elements.checkboxes.stats_reporting.on('change', function (event) {
        _this.handle_input_change_event(event);
      });

      this.elements.form
        .on('input', '.amoforms__js-form-input', function (event) {
          _this.normalize_inputs();
          _this.handle_input_change_event(event);
        })
        .on('submit', function (event) {
          event.preventDefault();
          _this.on_submit_form();
        });

      // If account is registered, we will check authorization and show an error if the authentication will fail.
      if (this.settings.account.registered) {
        this.check_authorization_settings();
      }

      _this.elements.phone.intlTelInput({
        defaultCountry: AMOFORMS.page_settings['country'],
        preferredCountries: ['us', 'gb', 'au', 'mx', 'in'],
        utilsScript: AMOFORMS.page_settings['libphonenumber_path']
      });
    },

    /**
     * Get or set validation status
     * @param [bool]
     * @return {boolean}
     */
    is_valid: function (bool) {
      if (typeof bool === 'undefined') {
        return this.params.is_valid;
      }
      this.params.is_valid = Boolean(bool);

      if (!bool) {
        this.toggle_save_button(false);
      }
    },

    validate_email: function (email) {
      return email && this.fn.validEmail(email);
    },

    //noinspection JSUnusedGlobalSymbols
    validate_login: function (login) {
      return this.validate_email(login);
    },

    //noinspection JSUnusedGlobalSymbols
    validate_subdomain: function (subdomain) {
      return this.fn.validSubdomain(subdomain);
    },

    //noinspection JSUnusedGlobalSymbols
    validate_api_key: function (api_key) {
      return this.fn.validApiKey(api_key);
    },

    handle_input_change_event: function (event) {
      var _this = this,
        element_id = $(event.target).attr('id'),
        account_inputs_is_valid;

      this.is_valid(true); // assume that all is valid before validation
      this.toggle_save_button(true); // turn on Save Button before validation
      this.toggle_save_loader(true); // turn on loader for validation time

      this.validate_checkboxes();
      this.validate_email_inputs();
      account_inputs_is_valid = this.validate_account_inputs();

      var validation_callback = function (is_valid) {
        _this.toggle_save_loader(false);
        if (is_valid === false) {
          _this.is_valid(false);
        }
      };

      // if need authorization, but account inputs is invalid and current changed element is not "Email to", because "Email to" has own validator
      if (this.params.need_auth && !account_inputs_is_valid && element_id !== this.ids.email_to) {
        validation_callback(false);
      }

      this.validate_phone_input();

      switch (element_id) {
        case this.ids.email_to:
          this.on_change_email_to(validation_callback);
          break;

        case this.ids.login:
        case this.ids.subdomain:
        case this.ids.api_key:
          this.on_change_account_settings(validation_callback);
          break;
        default:
          validation_callback();
          break;
      }
    },

    /**
     * @param {Function} validation_callback Must be called before return
     */
    on_change_email_to: function (validation_callback) {
      var _this = this,
        email = this.elements.email_to.val(),
        is_valid_email = this.validate_email(email);

      if (!is_valid_email) {
        this.is_valid(false);
      }

      if (this.settings.account.registered) {
        // if account is registered, then we will ignore changes "email_to" and we will listen changes of "login" field
        validation_callback(is_valid_email);
        return;
      }

      // if account is not yet registered in plugin, we will use "email_to" field as login for account
      if (!is_valid_email) {
        // if email is not valid and account is not registered, we hide account settings, because we can't use invalid email for account
        _this.params.need_auth = false;
        _this.toggle_account_settings_block(false);
        validation_callback(false);
        return;
      }

      if (this.params.login_validation_timer_id) {
        //noinspection JSCheckFunctionSignatures
        clearTimeout(this.params.login_validation_timer_id);
      }

      this.params.login_validation_timer_id = setTimeout(function () {
        _this.can_register(email, function (is_free) {
          _this.params.need_auth = !is_free;

          if (is_free) {
            // if login is free, then we can automatically register account for user and show his subdomain & api_key
            _this.toggle_phone_block(true);
            _this.set_account_action(_this.const.account_actions.register);
            _this.toggle_account_settings_block(false);
            _this.validate_phone_input();
          } else {
            // if email (login) is not free, then we ask user for subdomain & api_key
            _this.toggle_phone_block(false);
            _this.set_account_action(_this.const.account_actions.update);
            _this.elements.login.val(email);
            _this.elements.subdomain.val('');
            _this.elements.api_key.val('');
            _this.elements.checkboxes.stats_reporting.prop('checked', true);
            _this.is_valid(false); // we require account settings, but they are not fully filled. So, form is not valid yet.
            _this.toggle_account_settings_block(true);
          }

          validation_callback(true); // we send result to callback before return from this method
        }, function () {
          // on error
          validation_callback(false); // we send result to callback before return from this method
        });
      }, this.const.input_validation_delay);
    },

    validate_email_inputs: function () {
      var _this = this,
        invalid,
        result = true;

      $.each(_this.elements.text_email_inputs, function (index, $element) {
        invalid = !$.trim($element.val());
        _this.toggle_invalid_input($element, invalid);
        if (invalid) {
          result = false;
        }
      });

      invalid = !this.validate_email(this.elements.email_to.val());
      _this.toggle_invalid_input(this.elements.email_to, invalid);
      if (invalid) {
        result = false;
      }

      if (!result) {
        // Email settings always must be filled and valid. If they aren't valid, we disable save button.
        this.is_valid(false);
      }

      return result;
    },

    validate_phone_input: function () {
      if (AMOFORMS.page_settings.phone_settings == 'email_settings_phone_optional'){
        return true;
      }
      var is_valid = true;
      if (!this.params.need_auth && !this.elements.phone.intlTelInput('isValidNumber')) {
        this.is_valid(false);
        is_valid = false;
      }
      this.toggle_invalid_input(this.elements.phone, !is_valid);
      return is_valid;
    },

    validate_checkboxes: function () {
      if (!this.elements.checkboxes.stats_reporting.prop('checked')) {
        this.is_valid(false);
        return false;
      }
      return true;
    },

    validate_account_inputs: function () {
      var _this = this,
        $element,
        invalid,
        method,
        result = true;

      $.each(['login', 'subdomain', 'api_key'], function () {
        $element = _this.elements[this];
        method = 'validate_' + this;
        invalid = !_this[method]($.trim($element.val()));
        _this.toggle_invalid_input($element, invalid);
        if (invalid) {
          result = false;
        }
      });

      return result;
    },

    /**
     * @param {Function} validation_callback Must be called before return
     */
    on_change_account_settings: function (validation_callback) {
      var _this = this;

      if (!this.validate_account_inputs()) {
        validation_callback(false);
        return;
      }

      if (this.params.auth_validation_timer_id) {
        //noinspection JSCheckFunctionSignatures
        clearTimeout(this.params.auth_validation_timer_id);
      }

      this.params.auth_validation_timer_id = setTimeout(function () {
        _this.check_auth(
          _this.elements.login.val(),
          _this.elements.api_key.val(),
          _this.elements.subdomain.val(),
          function (is_authorized) {
            if (!is_authorized) {
              _this.show_auth_error();
            } else {
              _this.change_account_settings_state(_this.const.account_settings_states.edit);
            }
            validation_callback(Boolean(is_authorized));
          }, function () {
            // on error
            validation_callback(false);
          });
      }, this.const.input_validation_delay);
    },

    show_auth_error: function () {
      var _this = this;
      this.is_valid(false);
      this.is_disabled_mark_invalid_inputs(false);

      $.each([this.elements.login, this.elements.subdomain, this.elements.api_key], function () {
        _this.toggle_invalid_input(this, true);
      });

      this.change_account_settings_state(this.const.account_settings_states.connection_error);
    },

    /**
     * Saving form handler
     */
    on_submit_form: function () {
      var _this = this;

      this.is_disabled_mark_invalid_inputs(false);
      this.validate_phone_input();
      this.update_full_phone();

      if (this.save_button_is_disabled() || !this.is_valid() || this.loading_in_process()) {
        return;
      }

      this.toggle_save_loader(true);

      this.send_save_request(this.elements.form.serialize(), function (response) {
        _this.toggle_save_loader(false);
        _this.toggle_save_button(false);

        // We must get account settings from response. If they are not exists, it is error.
        if (!response['result'] || !response['account_action'] || !response['account'] || !response['account']['registered']) {
          if (response['message']) {
            _this.show_error(response['message']);
          }
          _this.change_account_settings_state(_this.const.account_settings_states.connection_error);
        } else {
          _this.set_account_settings(response['account']);
          _this.set_account_action(_this.const.account_actions.update);
          _this.hide_stats_reporting();
          _this.toggle_phone_block(false);

          switch (response['account_action']) {
            case _this.const.account_actions.register:
              _this.refresh_account_settings_texts();
              _this.change_account_settings_state(_this.const.account_settings_states.just_registered);
              _this.toggle_account_settings_block(true);
              _this._handle_registration_event();
              _this.elements.document.trigger(_this.const.triggers.saved, response);
              break;

            case _this.const.account_actions.update:
              if (_this.get_account_settings_state() !== _this.const.account_settings_states.just_registered) {
                _this.change_account_settings_state(_this.const.account_settings_states.connected);
              }
              _this.elements.document.trigger(_this.const.triggers.saved, response);
              break;
          }
        }
      }, function () {
        // on error
        _this.toggle_save_loader(false);
        _this.toggle_save_button(false);
      });
    },

    _handle_registration_event: function () {
      this.send_request('send_registration_event');
      try {
        if (window.yaCounter34815135) {
          yaCounter34815135.reachGoal('registration');
        }
      } catch (e) {
        console.error(e);
      }
    },

    /**
     * @param {Object} account_settings
     */
    set_account_settings: function (account_settings) {
      this.settings.account.login = account_settings['login'];
      this.settings.account.subdomain = account_settings['subdomain'];
      this.settings.account.api_key = account_settings['api_key'];
      this.settings.account.registered = Boolean(account_settings['registered']);

      this.settings.account.url = account_settings['url'];
      this.settings.account.short_url = account_settings['short_url'];

      this.elements.login.val(this.settings.account.login);
      this.elements.subdomain.val(this.settings.account.subdomain);
      this.elements.api_key.val(this.settings.account.api_key);
    },

    check_authorization_settings: function () {
      var _this = this;
      if (this.settings.account.registered
        && this.settings.account.login
        && this.settings.account.subdomain
        && this.settings.account.api_key) {

        this.check_auth(this.settings.account.login, this.settings.account.api_key, this.settings.account.subdomain, function (authorized) {
          if (!authorized) {
            _this.show_auth_error();
          }
        });
      }
    },

    normalize_inputs: function () {
      this.elements.subdomain.val(this.elements.subdomain.val().toLowerCase());
      return this;
    },

    send_request: function (action, data, success_callback, error_callback) {
      var _this = this;
      $.ajax({
        type: 'POST',
        url: ajaxurl + '?action=amoforms_' + action,
        data: data,
        dataType: 'json',
        timeout: _this.params.ajax_timeout,
        success: success_callback,
        error: function (xhr, status, http_error) {
          AMOFORMS.core.errors.sendErrorAjax(xhr, status, http_error, action, data);

          _this.show_error(error_message);
          if ($.isFunction(error_callback)) {
            error_callback(xhr, status, http_error);
          }
        }
      });
    },

    can_register: function (email, callback, error_callback) {
      this.send_request('can_register', {email: email}, function (response) {
        callback(Boolean(response['result']));
      }, error_callback);
    },

    check_auth: function (login, api_key, subdomain, callback, error_callback) {
      this.send_request('has_connection', {
        login: login,
        api_key: api_key,
        subdomain: subdomain
      }, function (response) {
        callback(Boolean(response['result']));
      }, error_callback);
    },

    send_save_request: function (form_data, callback, error_callback) {
      var _this = this;
      _this.toggle_save_loader(true);

      this.send_request(this.const.endpoints.save, form_data, function (response) {
        _this.toggle_save_loader(false);
        callback(response);
      }, error_callback);
    },

    toggle_phone_block: function (on) {
      this.elements.phone_block.toggleClass('hidden', !on);
    },

    update_full_phone: function () {
      this.elements.phone_full.val(this.elements.phone.intlTelInput('getNumber'));
    },

    set_account_action: function (action) {
      this.elements.account_action.val(action);
    },

    toggle_account_settings_block: function (on) {
      this.elements.account_settings.toggleClass('hidden', !on);
    },

    /**
     * @param {String} state
     */
    change_account_settings_state: function (state) {
      this.elements.account_settings.attr('data-state', state);
    },

    /**
     * Get current state of account settings
     * @return {String}
     */
    get_account_settings_state: function () {
      return this.elements.account_settings.attr('data-state');
    },

    toggle_save_loader: function (on) {
      this.elements.buttons.save.toggleClass('loading', Boolean(on));
      this.loading_in_process(on);
    },

    toggle_save_button: function (on) {
      this.elements.buttons.save.attr('data-disabled', on ? '0' : '1');
    },

    save_button_is_disabled: function () {
      return this.elements.buttons.save.attr('data-disabled') === '1';
    },

    toggle_invalid_input: function ($input, add_mark) {
      if (!this.is_disabled_mark_invalid_inputs()) {
        $input.toggleClass('amoforms__invalid_input', Boolean(add_mark));
      }
    },

    is_disabled_mark_invalid_inputs: function (bool) {
      if (bool === false) {
        // we can only enable mark invalid inputs, but no disable it again.
        this.params.disabled_mark_invalid_inputs = false;
      }
      return !this.settings.account.registered && this.params.disabled_mark_invalid_inputs;
    },

    toggle_offer: function () {
      if (this.elements.buttons.show_terms.hasClass('active')) {
        this.elements.buttons.show_terms.removeClass('active');
        this.elements.texts.amoforms_offer.slideUp();
      } else {
        this.elements.buttons.show_terms.addClass('active');
        this.elements.texts.amoforms_offer.slideDown();
      }
    },

    hide_stats_reporting: function () {
      if (this.elements.stats_reporting_wrapper.length) {
        this.elements.stats_reporting_wrapper.hide();
      }
    },

    loading_in_process: function (bool) {
      if (typeof bool === 'undefined') {
        return this.params.loading_in_process;
      }
      this.params.loading_in_process = Boolean(bool);
    },

    refresh_account_settings_texts: function () {
      this.elements.texts.account_url.text(this.settings.account['short_url']);
      this.elements.texts.api_keys.text(this.settings.account.api_key);
    },

    show_message: function (message, is_error) {
      var wrapper = is_error ? this.elements.messages.error.wrapper : this.elements.messages.success.wrapper,
        paragraph = is_error ? this.elements.messages.error.text : this.elements.messages.success.text;
      paragraph.text(message);
      wrapper.show();
      this.elements.html_body.animate({scrollTop: 0});
      setTimeout(function () {
        wrapper.fadeOut(500);
      }, 5000);
    },

    show_error: function (message) {
      this.show_message(message, true);
    }
  });

  global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
    views: {
      EmailSettings: EmailSettings
    }
  });
})
(window, jQuery, _, Backbone);
