(function (global, $, _, Backbone) {
  'use strict';

  var FieldView, FieldFormModel, FieldStyleModel;

  FieldFormModel = AMOFORMS.core.form.Model.extend({
    url: ajaxurl,

    toJSON: function () {
      var attrs = {};

      _.each(this.attributes, function (value, key) {
        attrs[key.replace(/(field\d+)/gi, 'field')] = value;
      });

      return attrs;
    },

    toFullJSON: function () {
      var attrs = {
        field: {}
      };

      _.each(this.attributes, function (value, key) {
        if (key.match(/\[enums\]/)) {
          if (!attrs.field.enums) {
            attrs.field.enums = [];
          }

          attrs.field.enums[key.replace(/field(\d+)?\[enums\]\[(\d+)\]/gi, '$2')] = value;
        } else {
          attrs.field[key.replace(/field(\d+)?\[(\w+)\]/gi, '$2')] = AMOFORMS.core.fn.isNumeric(value) ? parseInt(value, 10) : value;
        }
      });

      return attrs;
    },

    save: function (options) {
      $.ajax({
        url: ajaxurl,
        data: _.extend({
          action: AMOFORMS.ajax_action_prefix + 'edit_field',
          form: {
            id: this.form_id
          }
        }, this.toJSON()),
        dataType: 'json',
        type: 'POST'
      })
      .done(_.bind(function (res) {
        if (res.result) {
          (options.success || function () {})(res, this);
        } else {
          (options.error || function () {})(res, this);
        }
      }, this))
      .fail(_.bind(function () {
        (options.error || function () {})(res, this);
      }));
    },

    destroy: function (options) {
      options = options || {};

      $.ajax({
        url: ajaxurl,
        data: {
          action: AMOFORMS.ajax_action_prefix + 'delete_field',
          form: {
            id: this.form_id
          },
          field: {
            id: this.getByCode('id')
          }
        },
        dataType: 'json',
        type: 'POST'
      })
      .done(_.bind(function (res) {
        if (res.result) {
          options.success(res, this);
        } else {
          (options.fail || function () {})(res, this);
        }
      }, this))
      .fail(_.bind(function () {
        (options.fail || function () {})(res, this);
      }, this));
    },

    getByCode: function (code) {
      return _.find(this.attributes, function (value, key) {
        return new RegExp('field[\\d]{0,}?(\\[.*\\])?\\[' + code + '\\](\\[.*\\])?', 'gi').exec(key) !== null;
      });
    }
  });

  FieldStyleModel = AMOFORMS.core.form.Model.extend({
    url: ajaxurl,

    toJSON: function () {
      var attrs = {};

      _.each(this.attributes, function (value, key) {
        attrs[key.replace(/(style\d+)/gi, 'style')] = value;
      });

      return attrs;
    },

    toFullJSON: function () {
      var attrs = {
        style: {}
      };

      _.each(this.attributes, function (value, key) {
        if (key.match(/\[enums\]/)) {
          if (!attrs.style.enums) {
            attrs.style.enums = [];
          }

          attrs.style.enums[key.replace(/style(\d+)?\[enums\]\[(\d+)\]/gi, '$2')] = value;
        } else {
          attrs.style[key.replace(/style(\d+)?\[(\w+)\]/gi, '$2')] = AMOFORMS.core.fn.isNumeric(value) ? parseInt(value, 10) : value;
        }
      });

      return attrs;
    },

    save: function (options) {
      $.ajax({
            url: ajaxurl,
            data: _.extend({
              action: AMOFORMS.ajax_action_prefix + 'edit_style',
              form: {
                id: this.form_id
              }
            }, this.toJSON()),
            dataType: 'json',
            type: 'POST'
          })
          .done(_.bind(function (res) {
            if (res.result) {
              (options.success || function () {})(res, this);
            } else {
              (options.error || function () {})(res, this);
            }
          }, this))
          .fail(_.bind(function () {
            (options.error || function () {})(res, this);
          }));
    },

    getByCode: function (code) {
      return _.find(this.attributes, function (value, key) {
        return new RegExp('style[\\d]{0,}?\\[' + code + '\\]', 'gi').exec(key) !== null;
      });
    }
  });

  FieldView = Backbone.View.extend({
    selectors: {
      inner: '.amoforms__fields__row__inner',
      name: '.amoforms__fields__row__inner__name',
      control: '.amoforms__fields__row__inner__control',
      description: '.amoforms__fields__row__inner__descr',
      edit_action: '.amoforms__fields__editor__row__actions__action-edit',
      editor: '.amoforms__fields__edit',
      form: '.amoforms__fields__edit__settings',
      style: '.amoforms__fields__edit__styles',
      save: '.js-amoforms-field-save',
      cancel: '.js-amoforms-field-cancel',
      save_style: '.js-amoforms-style-save',
      reset_style: '.js-amoforms-style-reset',
      date: '.amoforms__date .amoforms__text-input',
      enums: '.amoforms__fields__edit__row__options',
      enum: '.amoforms__fields__edit__row__options__item',
      enum_handle: '.amoforms__fields__edit__row__options__item__move',
      enum_add: '.amoforms__fields__edit__row__options__add',
      mask: '.js-mask',
      edit_input: 'input[name="field[default_value]"]',
      placeholder_input: 'input[name="field[placeholder]"]',
      file_label: '.amoforms__file-input__title',
      select_mask: '.amoforms__mask_select',
      system_mask: '.amoforms__mask_system',
      forms_hint: '.amoforms__forms_hint',
      settings_hint: '.amoforms__settings_hint',
      item: '.amoforms__fields__expander__item',
      item_head: '.amoforms__fields__expander__item__head',
      switcher: '.amoforms__fields__settings__btn.fs',
      settings_wrap: '.amoforms__fields__edit__settings',
      styles_wrap: '.amoforms__fields__edit__styles',
      style_el: '#amoforms__custom_style',
      style_type: '.amoforms__style_type_checker'
    },

    events: {
      'click .amoforms__fields__editor__row__actions__action-edit': 'setEdit',
      'click .amoforms__fields__editor__row__actions__action-delete': 'confirmDelete',
      'click .amoforms__fields__editor__row__actions__action-duplicate': 'duplicateField',
      'click .js-amoforms-field-save': 'submitEdit',
      'click .js-amoforms-field-cancel': 'cancelEdit',
      'click .js-amoforms-style-save': 'submitStyleEdit',
      'click .js-amoforms-style-reset': 'styleReset',
      'click .amoforms__fields__edit__row__options__item__trash': 'deleteEnum',
      'click .amoforms__fields__edit__row__options__add': 'addEnum',
      'input .js-is-number': 'validateNumber',
      'change .js-is-date': 'validateDate',
      'change .js-is-email': 'validateEmail',
      'change .js-is-url': 'validateUrl',
      'change .js-is-required': 'validateRequired',
      'blur .js-is-extensions': 'validateExtensions',
      'change .js-is-filesize': 'validateFilesize',
      'input .js-is-percents': 'validatePercents',
      'change .js-is-percents': 'validateTax',
      'mouseover': 'setHover',
      'mouseout': 'removeHover',
      'click .amoforms__fields__expander__item__head': 'expanderToggle',
      'click .amoforms__fields__settings__btn.fs': 'switcherToggle',
      'click .amoforms__style_type_checker' : 'styleTypeChange',
      'input .js-edit-limit-wrapper input': 'limitValidation',
      'mouseenter .js-style-visual': 'setVisualHover',
      'mouseleave .js-style-visual': 'removeVisualHover'
    },

    setHover: function () {
      this.$el.addClass('hover');
    },

    removeHover: function () {
      this.$el.removeClass('hover');
    },

    initialize: function (options) {
      options = options || {};
      var lists = ['family', 'weight', 'border'];

      this.css = new AMOFORMS.views.cssstorage;

      this.enumsSortUpdate();
      this.render();

      this.form = new AMOFORMS.core.form.View({
        el: this.$(this.selectors['form']),
        Model: FieldFormModel
      });
      this.form.model.form_id = options.form_id;
      this.ns = '.amoforms:field:ns' + this.form.model.getByCode('id');

      this.form.model.on('change', _.bind(function (model) {
        this.renderChange(_.map(model.changed, function (value, key) {
          return {
            key: /field[\d]{0,}?\[([\w\d]+)\]/gi.exec(key)[1] || '',
            value: value
          };
        }));
        this.render();
      }, this));

      this.style = new AMOFORMS.core.form.View({
        el: this.$(this.selectors['style']),
        Model: FieldStyleModel
      });

      this.style.model.form_id = options.form_id;
      this.style.model.style_id = this.style.model.attributes["style[id]"];

      this.style.model.on('change', _.bind(function (model) {
        this.renderStyleChange(_.map(model.changed, function (value, key) {
          var for_type = /style[\d]{0,}?\[([\w]+)\]/gi.exec(key);
          if(for_type && for_type.length != 0 && for_type[1] == 'is_type_style'){
            return {
              category: '',
              key: 'is_type_style',
              value: (value) ? 'enabled' : 'disabled'
            };
          } else {
            var pattern = /style[\d]{0,}?\[\w+\]\[([\w\d-]+)\]\[([\w\d-]+)\]/gi.exec(key)
            return {
              category: pattern ? pattern[1] : '',
              key: pattern ? pattern[2] : '',
              value: value
            };
          }
        }));
        this.render();
      }, this));

      this.$('.js-is-date').pickmeup(_.extend({}, AMOFORMS.core.fn.pickmeup_options, {
        change: function () {
          $(this)
            .trigger('input')
            .trigger('change');
        }
      }));
      this.$('.tooltip-settings').tooltipster({
        theme: 'tooltipster-shadow',
        multiple: true,
        maxWidth: 600,
        position: 'bottom-right',
        contentAsHTML: true
      });
      this.$('.tooltip-visual').tooltipster({
        theme: 'tooltipster-shadow',
        multiple: true,
        maxWidth: 400,
        position: 'bottom-left',
        contentAsHTML: true
      });
      _.each($('[data-family], [data-weight], [data-border]'), function(e){
        var $el = $(e);
        _.each(lists, function(datatype) {
          if(!_.isUndefined($el.data(datatype))){
            $el.val($el.data(datatype))
          }
        });
      });
      $(document).on('amoforms:field:render' + this.ns, _.bind(this.render, this));
      $(document).on('amoforms:field:reset'  + this.ns, _.bind(this.reset, this));
    },

    remove: function () {
      $(document).off(this.ns);
      this.form.remove();
      Backbone.View.prototype.remove.apply(this, arguments);
    },

    reset: function (evt, res) {
      _.each(res.style, function(style){
        if(style.id ==  this.style.model.style_id){
          this.resetStyleAtrr(style);
        }
      }, this);
    },

    resetStyleAtrr: function (style) {
      _.each(style.elements, function(value, name){
        _.each(value, function(prop, attr) {
          var index = "style[elements]["+name+"]["+attr+"]";
          this.style.model.defaults[index] = prop;
        }, this);
      }, this);
      this.cancelEdit();
      this.style.model.set(this.style.model.defaults);
    },

    render: function (e, name_position) {
      if(name_position != undefined){
        var $description_before;

        this.$el.removeClass('amoforms-field-name-position-before amoforms-field-name-position-above amoforms-field-name-position-inside');
        this.$el.addClass('amoforms-field-name-position-' + name_position);

        this.$(this.selectors['name'])[(name_position == 'inside') && (_.indexOf(['checkbox', 'radio'], this.form.model.getByCode('type')) === -1) ? 'hide' : 'show']();

        if (this.form.model.getByCode('description_position') == 'before') {
          $description_before = name_position == 'before' ? this.$(this.selectors['inner']) : this.$(this.selectors['control']);
          if ((this.form.model.getByCode('type') == 'radio') && (name_position == 'inside')) {
            $description_before = this.$(this.selectors['inner']);
          }
          $description_before.before(this.$(this.selectors['description']));
        }

        this.$('.js-edit-placeholder-wrapper')[name_position == 'inside' ? 'hide' : 'show']();
        this.$(this.selectors['control'] + ' .amoforms__text-input, ' + this.selectors['control'] + ' .amoforms__select__input')
            .attr('placeholder', this.form.model.getByCode(name_position == 'inside' ? 'name' : 'placeholder'));
      }
      this.hintsColor();
      this.description();
    },

    /* DOM-events */
    validateRequired: function (e) {
      var $this = $(e.currentTarget);

      $this[$this.val() ? 'removeClass' : 'addClass']('error');
      // $(e.currentTarget).
    },

    validateDate: function (e) {
      this.validateByType($(e.currentTarget), 'validDate');
    },

    validateUrl: function (e) {
      this.validateByType($(e.currentTarget), 'validUrl');
    },

    validateEmail: function (e) {
      this.validateByType($(e.currentTarget), 'validEmail');
    },

    validateExtensions: function (e) {
      this.validateByType($(e.currentTarget), 'validExtensions');
    },

    validateFilesize: function (e) {
      this.validateByType($(e.currentTarget), 'validFilesize');
    },

    validateTax: function (e) {
      this.validateByType($(e.currentTarget), 'validTax');
    },

    validateByType: function ($this, type) {
      var val = $.trim($this.val());
      $this[!val || AMOFORMS.core.fn[type](val) ? 'removeClass' : 'addClass']('error');
    },

    validateNumber: function (e, no_validate) {
      var $this, cleaned_value;

      if (no_validate === true) {
        return;
      }

      $this = $(e.currentTarget);
      cleaned_value = $this.val().replace(/[^0-9\.]+/, '');

      if ($this.val() !== cleaned_value) {
        $this.val(cleaned_value).trigger('input', [true]);
        return false;
      }
    },

    validatePercents: function (e) {
      var $this, cleaned_value;

      $this = $(e.currentTarget);
      cleaned_value = $this.val().replace(/[^0-9\.,%]+/, '');

      if ($this.val() !== cleaned_value) {
        $this.val(cleaned_value).trigger('input', [true]);
        return false;
      }
    },

    duplicateField: function (e) {
      if ($(e.target).parent().prev().hasClass('amoforms__captcha')) {
        return;
      }
      AMOFORMS.app.duplicateField(this.$el, this.form.model.getByCode('id'));
    },

    addEnum: function () {
      var $last_enum = this.$(this.selectors['enums']).find(this.selectors['enum'] + ':last'),
          $new_enum;

      if (!$last_enum.find('input').val()) {
        $last_enum.find('input').focus();
        return;
      }

      $new_enum = $last_enum.clone();
      this.$(this.selectors['enums']).append($new_enum);
      $new_enum.find('input').val('').focus();
      this.enumsSortUpdate();
    },

    deleteEnum: function (e) {
      if (this.$(this.selectors['enum']).length == 2) {
        return;
      }

      $(e.currentTarget).closest(this.selectors['enum']).remove();
      this.form.checkDeleted();
    },

    setEdit: function () {
      var $editor = this.$(this.selectors['editor']);
      this.createPickers();
      this.fillVisualEditor();
      $editor.show();
      this.$el.addClass('in-edit').find('.amoforms__fields__edit__textarea').css('height', '').trigger('textarea:resize:manual');
      this.makeSortable(true);

      if(this.$el.siblings().length == 1){
        var $sibling = this.$el.siblings();
        if($sibling.find(this.selectors['editor']).is(":visible")){
          $sibling.find(this.selectors['editor']).hide();
          $sibling.removeClass('in-edit');
        }
      }

      $('html, body').animate({
        scrollTop: this.$el.offset().top - 40
      }, 300);
        $(document).on('click mousedown', _.bind(function(e) {
          if($editor.is(':visible')){
            if ($(e.target).closest('.amoforms__fields__row').length) return;
            if ($(e.target).closest('.amoforms.colorpicker').length) return;
            if ($(e.target).hasClass('wp-toolbar')) return;
            this.$(this.selectors['cancel']).trigger('click');
          }
        }, this));
    },

    createPickers: function () {
      _.each(this.$('.amoforms__colorsetting'), function (e) {
        var $colorpicker = $(e),
            color = $colorpicker.val(),
            transparent = (color == 'transparent');
        $colorpicker.css({
          color: color,
          backgroundColor: color
        });
        $colorpicker.ColorPicker({
          color: $colorpicker.val(),
          isTransparent: transparent,
          onChange: function (rgb, hex_color) {
            if (hex_color !== 'transparent') {
              hex_color = '#' + hex_color;
            }

            $colorpicker
                .css({
                  color: hex_color,
                  backgroundColor: hex_color
                })
                .val(hex_color)
                .trigger('input');
          }
        });
      });
    },

    fillVisualEditor: function() {
      _.each(this.$(".amoforms__styles_editor__visual_wrapper"), function(e) {
        var $visual = $(e),
            type = $visual.prop("id"),
            values = {
              margin : this.style.model.attributes["style[elements][" + type + "][margin]"],
              padding : this.style.model.attributes["style[elements][" + type + "][padding]"],
              border : this.style.model.attributes["style[elements][" + type + "][border-width]"]
            };

        _.each(values, function(value, name){
          value  = (value) ? value.split(' ') : ['0px', '0px', '0px', '0px'];
          var count = value.length;
          switch (count){
            case 1:
              $visual.find("." + name + "-top").val(value[0]);
              $visual.find("." + name + "-right").val(value[0]);
              $visual.find("." + name + "-bottom").val(value[0]);
              $visual.find("." + name + "-left").val(value[0]);
              break;
            case 2:
              $visual.find("." + name + "-top").val(value[0]);
              $visual.find("." + name + "-bottom").val(value[0]);
              $visual.find("." + name + "-left").val(value[1]);
              $visual.find("." + name + "-right").val(value[1]);
              break;
            case 3:
              $visual.find("." + name + "-top").val(value[0]);
              $visual.find("." + name + "-left", "." + name + "-right").val(value[1]);
              $visual.find("." + name + "-bottom").val(value[2]);
              break;
            case 4:
              $visual.find("." + name + "-top").val(value[0]);
              $visual.find("." + name + "-right").val(value[1]);
              $visual.find("." + name + "-bottom").val(value[2]);
              $visual.find("." + name + "-left").val(value[3]);
              break;
            default:
              $visual.find("." + name + "-top").val('0px');
              $visual.find("." + name + "-right").val('0px');
              $visual.find("." + name + "-bottom").val('0px');
              $visual.find("." + name + "-left").val('0px');
              break;
          }
        });
      }, this);
    },

    confirmDelete: function () {
      new AMOFORMS.core.confirm({
        template_params: {
          caption: 'Are you sure you want to delete «' + (this.form.model.getByCode('name') || '') + '»?',
          accept_btn: 'Yes',
          decline_btn: 'No'
        },
        accept: _.bind(function (confirm) {
          this.form.model.destroy({
            success: _.bind(function () {
              if (this.$el.find('.amoforms__captcha').length > 0) {
                $('[data-type="captcha"]').attr('data-active', 'true');
              }
              this.layoutRemove(this.$el);
              confirm.done();
            }, this),
            fail: _.bind(function () {
              confirm.failed();
            }, this)
          });
        }, this)
      });
    },

    layoutRemove: function ($el) {
      var $siblings = $el.siblings('.amoforms__fields__row');
      if($siblings.length == 0){
        $el.parent().parent().remove();
      } else{
        var fields = new AMOFORMS.views.fields({ el: $('#amoforms_fields') });
        $el.remove();
        $siblings.removeClass('half').addClass('full');
        $siblings.data('view').form.model.attributes["field[grid]"] = 0;
        $siblings.data('view').form.model.save({
          success: _.bind(function () {}),
          error: _.bind(function () {})
        });
        fields.toggleBlockList($siblings.parent()[0], true);
      }
    },

    styleReset: function () {
      var style = this.style.model.toJSON(),
          caption;
      if(this.style.model.getByCode('is_type_style') == 1){
        caption = 'Are you sure you want to reset style for type?';
      } else {
        caption = 'Are you sure you want to reset style for field?';
      }
      new AMOFORMS.core.confirm({
        template_params: {
          caption: caption,
          accept_btn: 'Yes',
          decline_btn: 'No'
        },
        accept: _.bind(function (confirm) {
          this.sendRequest('reset_field_style');
          confirm.done();
        }, this)
      }, this);
    },

    sendRequest: function (action, error) {
      var style_id = this.style.$el.parents('.amoforms__fields__row ').attr('id');
      style_id = /style-([\d]+)/gi.exec(style_id)[1];
      $.post(
          ajaxurl,
          {
            action: AMOFORMS.ajax_action_prefix + action,
            form: {
              id: this.form.model.form_id
            },
            style: {
              id: style_id
            }
          },
          _.bind(function (res) {
            if (!res.result) {
              error();
            } else {
              this.dropStyle(res);
            }
          }, this)
      );
    },

    dropStyle: function (res) {
      this.resetStyleAtrr(res.style);
      var json = CSSJSON.toJSON($(this.selectors['style_el']).text()),
          new_style = CSSJSON.toJSON(AMOFORMS.core.fn.generateCSS(res.style));
      _.extend(json.children, new_style.children);
      this.css.setStyle(CSSJSON.toCSS(json));
      $(this.selectors['style_el']).text(this.css.getStyle());
    },

    submitEdit: function () {
      var $submit_btn;
      if (this.form.$el.find('input.error').length) {
        $submit_btn = this.form.$el.find(this.selectors['save']).addClass('animated shake');
        _.delay(function () {
          $submit_btn.removeClass('animated shake');
        }, 800);

        return;
      }

      this.form.save({
        success: _.bind(function () {
          this.cancelEdit();
        }, this),
        error: _.bind(function () {
          this.$(this.selectors['actions']).addClass('animated shake');

          _.delay(_.bind(function () {
            this.$(this.selectors['actions']).removeClass('shake');
          }, this), 800);
        }, this),
      });
    },

    submitStyleEdit: function () {
      var $submit_btn,
          $elem = $(this.el),
          type_selector = '[data-type="'+$elem.data('type')+'"]';
      if (this.style.$el.find('input.error').length) {
        $submit_btn = this.form.$el.find(this.selectors['save']).addClass('animated shake');
        _.delay(function () {
          $submit_btn.removeClass('animated shake');
        }, 800);

        return;
      }

      this.css.setStyle($(this.selectors['style_el']).text());

      if ($elem.hasClass('edited_id')){
        $elem.removeClass('edited_id');
        AMOFORMS.style.max_id++;
        if($elem.attr('id') != $elem.data('id')){
          $elem.data('id', $elem.attr('id'));
          this.style.model.attributes["style[id]"] = /style-([\d]+)/gi.exec($elem.data('id'))[1];
        }
      }

      $(type_selector).each(function() {
        var $el = $(this);
        if($el.attr('id') != $el.data('id')){
          $el.data('id', $el.attr('id'));
        }
      });

      this.style.save({
        success: _.bind(function () {
          this.cancelEdit();
        }, this),
        error: _.bind(function () {
          this.$(this.selectors['actions']).addClass('animated shake');

          _.delay(_.bind(function () {
            this.$(this.selectors['actions']).removeClass('shake');
          }, this), 800);
        }, this)
      });
    },

    cancelEdit: function (e) {
      if (e && e.preventDefault) {
        e.preventDefault();
      }

      $(this.selectors['style_el']).text(this.css.getStyle());

      this.makeSortable(false);
      this.$el.removeClass('in-edit edited_id');
      this.$el.attr('id', this.$el.data('id'));
      this.form.model.set(this.form.model.defaults);
      this.style.model.set(this.style.model.defaults);
      this.form.revert();
      this.style.revert();
      this.fillVisualEditor();
      this.$(this.selectors['editor']).hide();
      $('html, body').animate({
        scrollTop: this.$el.offset().top - 100
      }, 300);
      this.renderChange([{
        key: 'enums',
        value: ''
      }]);
    },
    /* endof DOM-events */

    renderChange: function (changed_values) {
      _.each(changed_values, function (changed) {
        switch (changed.key) {
          case 'name':
            if (this.form.model.getByCode('type') == 'heading') {
              this.$(this.selectors['control'] + ' h1').text(changed.value);
            } else {
              if (this.$el.hasClass('amoforms-field-name-position-inside')) {
                this.$(this.selectors['control'] + ' .amoforms__text-input, ' + this.selectors['control'] + ' .amoforms__select__input').attr('placeholder', changed.value);
              } else {
                this.$(this.selectors['name'] + ' > span').text(changed.value).parent()[changed.value ? 'show' : 'hide']();
              }
            }
            break;
          case 'hint':
            this.$(this.selectors['forms_hint'])[changed.value ? 'show' : 'hide']();
            break;
          case 'required':
            this.$(this.selectors['name'] + ' > b').remove();

            if (parseInt(changed.value) === 1) {
              this.$(this.selectors['name'] + ' > span').after('<b>*</b>');
            }
            break;

          case 'default_value':
            if (this.form.model.getByCode('type') == 'tax') {
              this.$(this.selectors['control']).find('.tax').text(changed.value).trigger('change');
            } else{
              this.$(this.selectors['control'] + ' :input').val(changed.value).trigger('change');
            }
            break;

          case 'placeholder':
            this.$(this.selectors['control'] + ' :input').attr('placeholder', changed.value);
            break;

          case 'description':
            if (this.form.model.getByCode('type') == 'instructions') {
              this.$('.amoforms__control-instructions').text(changed.value);
            } else {
              this.$(this.selectors['description']).text(changed.value)[changed.value ? 'show' : 'hide']();
            }
            break;

          case 'description_position':
            if (!changed.value) {
              return;
            }
            if (this.form.model.getByCode('type') == 'checkbox') {
              if (this.$el.hasClass('amoforms-field-name-position-before')) {
                this.$(this.selectors['inner'])[changed.value](this.$(this.selectors['description']))
              } else {
                this.$(this.selectors['control'])[changed.value](this.$(this.selectors['description']));
              }
            } else {
              if (changed.value == 'after') {
                this.$(this.selectors['inner']).after(this.$(this.selectors['description']));
              } else {
                // для name-position before
                if (this.$el.hasClass('amoforms-field-name-position-before') ||
                    ((this.form.model.getByCode('type') == 'radio') && this.$el.hasClass('amoforms-field-name-position-inside'))
                ) {
                  this.$(this.selectors['inner']).before(this.$(this.selectors['description']))
                } else {
                  this.$(this.selectors['name']).after(this.$(this.selectors['description']));
                }
              }
            }
            break;

          case 'options':
            var $mask_checker = this.$(this.selectors['mask']).find('input:checked');

            if ($mask_checker.length > 0) {
              var value;
              switch ($mask_checker.val()) {
                case '0':  //disable mask
                  this.$(this.selectors['select_mask']).hide();
                  this.$(this.selectors['system_mask']).hide();
                  this.enableinputs();
                  break;
                case '1': //enable or change default masks
                  this.$(this.selectors['select_mask']).show();
                  this.$(this.selectors['system_mask']).hide();
                  this.disableinputs();
                  value = this.$(this.selectors['select_mask'] + ' select option:selected').data("masktext");
                  break;
                case '2': //enable or change custom mask
                  value = this.form.model.getByCode('mask-custom');
                  this.$(this.selectors['select_mask']).hide();
                  this.$(this.selectors['system_mask']).show();
                  this.disableinputs();
                  break;
                default:
                  //do nothing
                  break;
              }
              if(value){
                this.$(this.selectors['control'] + ' .amoforms__text-input, ' + this.selectors['control'] + ' .amoforms__select__input').attr('placeholder', value);
              }
            }
            if(this.form.model.getByCode('type') == 'total'){
              var $left = this.$(this.selectors['control']).find('.total-left'),
                  $right = this.$(this.selectors['control']).find('.total-right'),
                  $total;
              switch (this.form.model.getByCode('curr_position')){
                case 1:
                  $right.show();
                  $left.hide();
                  $total = $right;
                  break;
                case 0:
                default:
                  $left.show();
                  $right.hide();
                  $total = $left;
                  break;
              }
              $total.text(this.form.model.getByCode('curr_symbol')).trigger('change');
            }
            break;

          case 'spam':
              this.$(this.selectors['name'] + " > span").text(changed.value);
            break;

          case 'layout':
            if (changed.value) {
              this.$('.amoforms__radio-control')[changed.value == AMOFORMS.consts.layout_inline ? 'removeClass' : 'addClass']('amoforms__radio-control-block');
            }
            break;

          case 'enums':
            if (changed.value !== undefined && changed.value != '') {
              if (this.form.model.getByCode('type') == 'radio') {
                this.$('.amoforms__radio-control').html(
                  $.makeArray(
                    this.$(this.selectors['enum']).map(function () {
                      if (!$(this).find('input').val()) {
                        return '';
                      }

                      return '<label class="amoforms__radio__label"><span class="amoforms__radio"><input type="radio"><b></b></span><b>' +
                                $(this).find('input').val() +
                              '</b></label>';
                    })
                  ).join('')
                );
              }
            }
            break;
          case 'label':
            this.$(this.selectors['file_label']).text(changed.value);
            break;
        }
      }, this);
    },

    renderStyleChange: function (changed_values) {
      var $el = $(this.el),
          current_id = $el.attr('id'),
          json,
          fromVisual = {},
          key;
      fromVisual = {
          margin : ['margin-top', 'margin-bottom', 'margin-left', 'margin-right'],
          padding: ['padding-top', 'padding-bottom', 'padding-left', 'padding-right'],
          border: ['border-top', 'border-bottom', 'border-left', 'border-right']
      };
      json = CSSJSON.toJSON($(this.selectors['style_el']).text());
      _.each(changed_values, function (changed) {
        if($.inArray(changed.key, fromVisual.margin) != -1){
          key = 'margin';
          changed.value = this.mergeCSSProperties(json,current_id, changed, key);
          changed.key = key;
          this.style.model.attributes["style[elements]["+changed.category+"]["+changed.key+"]"] = changed.value;
        }
        if($.inArray(changed.key, fromVisual.padding) != -1){
          key = 'padding';
          changed.value = this.mergeCSSProperties(json, current_id, changed, key);
          changed.key = key;
          this.style.model.attributes["style[elements]["+changed.category+"]["+changed.key+"]"] = changed.value;
        }
        if($.inArray(changed.key, fromVisual.border) != -1){
          key = 'border-width';
          changed.value = this.mergeCSSProperties(json, current_id, changed, key);
          changed.key = key;
          this.style.model.attributes["style[elements]["+changed.category+"]["+changed.key+"]"] = changed.value;
        }
        if(changed.key != 'is_type_style'){
          json.children[".amoforms #" + current_id + " .amoforms_" + changed.category].attributes[changed.key] = changed.value;
        }
      }, this);
      $(this.selectors['style_el']).text(CSSJSON.toCSS(json));
    },

    mergeCSSProperties: function (json, current_id, changed, key) {
      var current_css = json.children[".amoforms #" + current_id + " .amoforms_" + changed.category].attributes[key].split(' '),
          output,
          i,
          position = changed.key.split('-')[1],
          postitons = {
            0: 'top',
            1: 'right',
            2: 'bottom',
            3: 'left'
          };
      switch (current_css.length){
        case 1:
          output = '';
          for (i = 0; i < 4; i++) {
            if(postitons[i] == position){
              output += changed.value + " ";
            } else {
              output += current_css[0] + " ";
            }
          }
          break;
        case 2:
          output = '';
          for (i = 0; i < 4; i++) {
            if(postitons[i] == position){
              output += changed.value + " ";
            } else {
              output += (i == 0 || i == 1) ? current_css[i] + " " : current_css[i-2] + " ";
            }
          }
          break;
        case 4:
          output = '';
          for (i = 0; i < 4; i++) {
            if(postitons[i] == position){
              output += changed.value + " ";
            } else {
              output += current_css[i] + " ";
            }
          }
          break;
        default:
          output = '';
          for (i = 0; i < 4; i++) {
              output += changed.value + " ";
          }
          break;
      }
      return output.trim();
    },

    styleTypeChange: function () {
      var $el = $(this.el),
          checked = ($el.find(this.selectors['style_type']).attr('checked')) ? true : false,
          type_selector = '[data-type="'+$el.data('type')+'"]',
          current_id = $el.attr('id'),
          new_id = 'style-' + (AMOFORMS.style.max_id + 1),
          new_style = {},
          json,
          css;
      if(checked){
        var _this = this;
        $(type_selector).each(function() {
          var $el = $(this);
          if(!$el.hasClass("amoforms__fields__expander__item__content__fields__field")){
            $el.attr('id', current_id);
            $el.find(_this.selectors['style_type']).attr("checked", true);
          }
        });
      } else {
        $(type_selector).each(function() {
          var $type_el = $(this);
          if($type_el.attr('id') != $type_el.data('id')){
            $type_el.attr('id', $type_el.data('id'));
          }
        });
        json = CSSJSON.toJSON($(this.selectors['style_el']).text());
        _.each(json.children, function(value, name) {
          if(name.indexOf(current_id) != -1){
            var newname = name.replace(current_id, new_id);
              new_style[newname] = value;
          }
        });
        _.extend(json.children, new_style);
        $el.attr('id', new_id).addClass('edited_id');
        css = CSSJSON.toCSS(json);
        $(this.selectors['style_el']).text(css);
      }
    },

    makeSortable: function (active) {
      if (!this.$(this.selectors['enums'])[0]) {
        return;
      }

      if (active) {
        this.options_sortable = new Sortable(this.$(this.selectors['enums'])[0], {
          handle: this.selectors['enum_handle'],
          draggable: this.selectors['enum'],
          animation: 150,
          onEnd: _.bind(this.enumsSortUpdate, this)
        });
      } else {
        if (this.options_sortable) {
          this.options_sortable.destroy();
        }
      }
    },

    enumsSortUpdate: function () {
      var $input, name;

      this.$(this.selectors['enum']).each(function (key, value) {
        $input = $(this).find('input');
        name = $input.attr('name');

        $input.attr('name', name.replace(/(field\[enums\]\[)(.*)(\])/gi, '$1' + key + '$3'));
      });

      if (this.form) {
        this.form.initModelFromForm();
        this.renderChange([{
          key: 'enums',
          value: ''
        }]);
      }
    },

    disableinputs: function () {
      this.$(this.selectors['edit_input']).val('');
      this.$(this.selectors['control'] + ' :input').val('');
      this.$(this.selectors['control'] + ' :input').removeAttr('placeholder');
      $('.js-edit-placeholder-wrapper').hide();
      $('.js-default-value-wrapper').hide();
    },

    enableinputs: function () {
      this.$(this.selectors['control'] + ' :input').attr('placeholder', this.$(this.selectors['placeholder_input']).val());
      $('.js-edit-placeholder-wrapper').show();
      $('.js-default-value-wrapper').show();
    },

    expanderToggle: function (e) {
      var $this = $(e.currentTarget).parent(),
      active = $this.hasClass('expanded');
      this
          .$(this.selectors['item'] + '.expanded')
          .removeClass('expanded')
          .find('.amoforms__fields__expander__item__content')
          .css('min-height', '');

      if(!active){
        $this
            .addClass('expanded')
            .find($this.find('.amoforms__fields__expander__item__content'))
            .css('min-height', $this.find('.amoforms__fields__expander__item__content__inner')[0].offsetHeight);
      }
    },

    switcherToggle: function (e) {

      var $this = $(e.currentTarget);

      this
          .$(this.selectors['switcher'] + '.pressed')
          .removeClass('pressed');

      $this
          .addClass('pressed');

      if($this.data('type') == 'field-styles'){
        this.$(this.selectors['settings_wrap']).hide();
        this.$(this.selectors['styles_wrap']).show();
      } else {
        this.$(this.selectors['settings_wrap']).show();
        this.$(this.selectors['styles_wrap']).hide();
      }
    },

    hintsColor: function () {
      $(this.selectors['forms_hint']).each(function() {
        var $tooltip = $(this),
            $sibling,
            parentBgColor;
        if($tooltip.is(':visible')){
          $sibling = $tooltip.siblings(".amoforms_field_element");
          parentBgColor = $sibling.css('background-color');
          if (parentBgColor && parentBgColor.indexOf('rgb') != -1) {
            parentBgColor = AMOFORMS.core.fn.rgb2hex(parentBgColor);
          }
          if(parentBgColor && AMOFORMS.core.fn.isDarkColor(parentBgColor)){
            $tooltip.addClass('white');
          } else {
            $tooltip.removeClass('white');
          }
        }
      });
    },

    description: function () {
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
    },

    limitValidation: function (e) {
      var $elem = $(e.currentTarget),
          cleaned_value = $elem.val().replace(/[^0-9]+/, '');
      if ($elem.val() !== cleaned_value) {
        $elem.val(cleaned_value);
        return false;
      }
    },

    setVisualHover: function(e) {
      var $el = $(e.target);
      $el.siblings(".js-style-visual").addClass("amoforms__styles_editor__opacity");
    },

    removeVisualHover: function(e) {
      var $el = $(e.target);
      $el.siblings(".js-style-visual").removeClass("amoforms__styles_editor__opacity");
    }

  });

  global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
    views: {
      field: FieldView
    }
  });
}(window, jQuery, _, Backbone));
