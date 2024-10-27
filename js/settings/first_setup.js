(function (global, $, _) {
  'use strict';

  var FirstSetup = {};

  FirstSetup.View = global.AMOFORMS.views.EmailSettings.View.extend({
    initialize: function () {
      this.init_email_settings();

      $.extend(true, this.elements, {
        buttons: {
          get_started: $('.amoforms__first_setup__tutorial__get_started_btn'),
          continue_btn: $('#continue_button')
        },
        account_settings_wrapper: $('#amoforms__first_setup__account_settings_wrapper'),
        account_settings_big_header: $('#amoforms__first_setup__account_settings__header_wrapper'),
        feedbacks_images_wrapper: $('#feedbacks__img_list_wrapper'),
        promo_wrapper: $('#amoforms__first_setup__promo2_wrapper')
      });

      this.const.endpoints.save = 'update_first_setup_settings';

      // bind events
      this.feedbacks_rotator = this.elements.feedbacks_images_wrapper.carousel({
        hAlign: 'right',
        hMargin: 0.1,
        vMargin: 0,
        frontWidth: 165,
        frontHeight: 165,
        carouselWidth: 165,
        carouselHeight: 165,
        backZoom: 1,
        slidesPerScroll: 3,
        speed: 500,
        description: true,
        descriptionContainer: '.feedbacks__list',
        buttonNav: 'none',
        directionNav: true,
        autoplay: true,
        autoplayInterval: 6000,
        pauseOnHover: false,
        mouse: false
      });

      // bind events
      this.elements.document
        .on('click', '.icon-arrow-left.feedbacks__arrow', _.bind(function () {
          this.feedbacks_rotator.prev();
        }, this))
        .on('click', '.icon-arrow-right.feedbacks__arrow', _.bind(function () {
          this.feedbacks_rotator.next();
        }, this))
        .on('click', '.slideItem:visible', _.bind(function (event) {
          if ($(event.currentTarget).css('z-index') == '3') {
            this.feedbacks_rotator.next();
          }
        }, this))
        .on(this.const.triggers.saved, _.bind(this._handle_saved_settings, this));

      this.elements.buttons.get_started.one('click', _.bind(function () {
        this._handle_get_started_click();
      }, this));

      this.elements.buttons.continue_btn.on('click', _.bind(function () {
        this._redirect(this.elements.buttons.continue_btn.attr('data-href'));
      }, this));
    },

    /**
     * Handle click on Get Started button
     * @private
     */
    _handle_get_started_click: function () {
      this._send_get_started_event();
      this.elements.promo_wrapper.hide();
      this.elements.account_settings_wrapper.show();
    },

    /**
     * Send event about click on "Get started button"
     * @private
     */
    _send_get_started_event: function () {
      this.send_request('send_get_started_event');

      try {
        if (window.yaCounter34815135) {
          yaCounter34815135.reachGoal('getstarted');
        }
      } catch (e) {
        var
            error_message = 'Error: ' + e,
            error = new Error(error_message);
        AMOFORMS.core.errors.sendError(error, {
          action: 'send_get_started_event',
          message: e.message,
          error_name: e.name
        });
      }
    },

    /**
     * Handle saved settings
     * @param event
     * @param {Object} data
     * @private
     */
    _handle_saved_settings: function (event, data) {
      if (!_.isObject(data) || !data['result'] || !data['account_action'] || !data['form_url']) {
        return;
      }
      this._toggle_form_saved_state(true);
      this.elements.buttons.continue_btn.attr('data-href', data['form_url']);
      this.elements.buttons.continue_btn.show();
    },

    /**
     * Toggle state of saved form
     * @param {Boolean} on - saved or unsaved
     * @private
     */
    _toggle_form_saved_state: function (on) {
      this.elements.form.toggleClass('amoforms_saved_email_settings', !!on);
    },

    /**
     * @param {String} url
     * @private
     */
    _redirect: function (url) {
      if (url) {
        window.location.replace(url);
      }
    }
  });

  global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
    views: {
      FirstSetup: FirstSetup
    }
  });
})
(window, jQuery, _);
