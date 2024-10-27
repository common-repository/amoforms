(function (global, $, _, Backbone) {
  'use strict';

  var Confirm;

  Confirm = Backbone.View.extend({
    className: 'amoforms amoforms__confirm',

    selectors: {
      inner: '.amoforms__confirm__inner',
    },

    events: {
      'click .js-amoforms-accept': 'accept',
      'click .js-amoforms-decline': 'decline',
    },

    initialize: function (options) {
      this.ns = 'amoforms-confirm' + AMOFORMS.core.fn.s4();

      this.options = _.extend({
        $container: $('#wpbody'),
        template_params: {}
      }, options);

      this.$el.html(_.template(
          '<div class="amoforms__confirm__overlay"></div>\
          <div class="amoforms__confirm__inner">\
            <div>\
              <% if (data.caption) { %><h2 class="amoforms__confirm__caption"><%= data.caption %></h2><% } %>\
              <div class="amoforms__confirm__actions">\
                <button class="amoforms__button-input js-amoforms-accept"><%= data.accept_btn %></button>\
                <button class="amoforms__button-input-cancel js-amoforms-decline"><%= data.decline_btn %></button>\
              </div>\
            </div>\
          </div>'
      )({data: options.template_params}));

      this.options.$container.append(this.$el);
      $(document).on('keyup.' + this.ns, _.bind(this.hotkeys, this));
      $(window).on('resize.' + this.ns, _.bind(this.windowResize, this));

      this.$el.focus().addClass('active');
      this.windowResize();
    },

    remove: function () {
      $(document).off('.' + this.ns);
      $(window).off('.' + this.ns);
      Backbone.View.prototype.remove.apply(this, arguments);
    },

    windowResize: function () {
      this.$(this.selectors['inner']).css('left', this.options.$container.offset().left);
    },

    failed: function () {
      var $animated_inner = this.$(this.selectors['inner']).children('div:first');

      $animated_inner.addClass('animated shake');
      _.delay(function () {
        $animated_inner.removeClass('shake');
      }, 800);
    },

    done: function () {
      this.remove();
    },

    /* DOM-events */
    hotkeys: function (e) {
      if (e.handleObj.namespace !== this.ns) {
        return;
      }

      e.stopPropagation();

      switch (e.keyCode) {
        case 27: this.decline(); break; // ESC key
        case 13: this.accept(); break; // ENTER key
      }
    },

    accept: function () {
      if (_.isFunction(this.options.accept)) {
        this.options.accept(this);
      } else {
        this.remove();
      }
    },

    decline: function () {
      if (_.isFunction(this.options.decline)) {
        this.options.decline(this);
      } else {
        this.remove();
      }
    },
    /* end of DOM-events */
  });

  global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
    core: {
      confirm: Confirm
    }
  });
}(window, jQuery, _, Backbone));
