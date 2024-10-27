(function (global, $, _, Backbone) {
  'use strict';

  var Form = {};

  Form.Model = Backbone.Model.extend({
    initialize: function () {
      this.defaults = _.clone(this.attributes);
    },

    setDefaults: function () {
      this.defaults = _.defaults(this.defaults, this.attributes);
    },

    updateDefaults: function () {
      this.defaults = _.extend(this.defaults, this.attributes);
    },

    hasChanges: function () {
      return !_.isEqual(this.attributes, this.defaults);
    }
  });

  Form.View = Backbone.View.extend({
    events: {
      'input :input:not([type="radio"]):not([type="checkbox"])': 'changeInModel',
      'change input[type="radio"]': 'changeInModel',
      'change input[type="checkbox"]': 'changeInModel'
    },

    initialize: function (params) {
      var defaults = {
            onSave: function () {}
          }, Model = Form.Model;

      this.options = $.extend({}, defaults, params);

      if (this.options.Model) {
        Model = this.options.Model;
      }

      this.model = new Model();
      this.initModelFromForm();
    },

    show: function () {
      this.$el.show();
    },

    hide: function () {
      this.$el.hide();
    },

    save: function (params) {
      var _this = this,
        options = $.extend({
          success: function () {},
          error: function () {}
        }, params||{});
      this.model.save({
        success: _.bind(function (res) {
          this.updateDefaults();

          options.success.call(this, res);

          _this.checkChanges();
        }, this.model),
        error: function (res) {
          options.error.call(this, res);
        }
      });
    },

    revert: function () {
      this.model.attributes = _.clone(this.model.defaults);

      _.each(this.model.attributes, function (value, name) {
          this.setInputValue(this.$(':input[name="' + name + '"]'), value, name);
      }, this);

      this.model.trigger('has_reverted');
    },

    checkDeleted: function() {
      var _this = this;

      _.each(this.model.attributes, function (value, name) {
        if (!_this.$el.find(':input[name="' + name + '"]').length) {
          delete _this.model.attributes[name];
        }
      }, this);

      this.checkChanges();
    },

    initModelFromForm: function () {
      this.model.clear();
      _.each(this.$(':input'), function (input) {
        var $input = $(input),
            attr = this.model.get($input.attr('name'));

        if (attr == undefined || attr == '') {//условие для кастомного поля список(radio)
          this.model.set($input.attr('name'), this.getInputValue($input));
        }
      }, this);
      this.model.setDefaults();
    },

    hasChanges: function () {
      return this.model.hasChanges();
    },

    setInputValue: function ($input, val, name) {
      var type = $input.attr('type');

      name = name || $input.attr('name');

      switch (type) {
        case 'checkbox':
        case 'radio':
          $input.filter(function () {
            return $(this).val() == val;
          }).prop('checked', true);
          break;

        default:
          $input.val(val);
      }

      $input.trigger('change');
      this.model.set(name, val);
    },

    getInputValue: function ($input) {
      var value = '';

      switch ($input.attr('type')) {
        case 'checkbox':
        case 'radio':
          value = $input.prop('checked') ? $input.val() || 'on' : '';
          break;

        default:
          value = $.trim($input.val());
      }

      if (AMOFORMS.core.fn.isNumeric(value)) {
        value = parseInt(value, 10);
      }

      return value;
    },

    changeInModel: function (e) {
      var $this = $(e.currentTarget);
      this.model.set($this.attr('name'), this.getInputValue($this));

      this.checkChanges();
    },

    checkChanges: function () {
      var has_changes = this.hasChanges(),
        event_name = 'has_changes';

      if (!has_changes) {
        event_name = 'has_reverted';
      }
      this.model.trigger(event_name);

      return has_changes;
    }
  });


  global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
    core: {
      form: Form
    }
  });
}(window, jQuery, _, Backbone));
