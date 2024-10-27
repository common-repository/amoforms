(function (global, $, _, Backbone) {
  var FormSubmit, FormSubmitModel, FormSubmitStyleModel;

  FormSubmitStyleModel = AMOFORMS.core.form.Model.extend({
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
    getByCode: function (code) {
      return _.find(this.attributes, function (value, key) {
        return new RegExp('style[\\d]{0,}?\\[' + code + '\\]', 'gi').exec(key) !== null;
      });
    }
  });

  FormSubmitModel = AMOFORMS.core.form.Model.extend({
    save: function (options) {
      $.ajax({
        url: ajaxurl,
        data: _.extend({
          action: AMOFORMS.ajax_action_prefix + 'update_submit_button',
          'form[id]': this.form_id
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
    toJSON: function () {
      var attrs = {};

      _.each(this.attributes, function (value, key) {
        attrs[key.replace(/(field\d+)/gi, 'field')] = value;
      });

      return attrs;
    },
    extendAttr: function (params) {
      this.attributes = _.extend(this.attributes, params);
    }
  });

  FormSubmit = Backbone.View.extend({
    selectors: {
      submit_btn: '.amoforms__fields__row__submit',
      edit_submit: '.amoforms__fields__edit-submit',
      form_wrap: '.amoforms__fields__edit',
      form: '.amoforms__fields__edit__row',
      style: '.amoforms__styles__edit__row',
      actions: '.amoforms__fields__edit__actions',
      row_actions: '.amoforms__fields__editor__row__actions',
      size_input: '[name="form[settings][submit][size]"]',
      size_btns: '.amoforms__size-btn',
      color_picker: '.amoforms__colorpicker',
      style_el: '#amoforms__custom_style'
    },

    events: {
      'click .amoforms__fields__editor__row__actions__action-edit': 'setEdit',
      'click .js-amoforms-field-save': 'saveClick',
      'click .js-amoforms-field-cancel': 'cancelEdit',
      'blur [name="form[settings][submit][text]"]': 'checkError',
      'input [name="form[settings][submit][text]"]': 'clearError',
      'click .js-amoforms-style-reset': 'styleReset',
      'mouseenter .js-style-visual': 'setVisualHover',
      'mouseleave .js-style-visual': 'removeVisualHover'
    },

    initialize: function (options) {

      this.css = new AMOFORMS.views.cssstorage;

      this.form = new AMOFORMS.core.form.View({
        el: this.$(this.selectors['form']),
        Model: FormSubmitModel
      });

      this.form.model.form_id = options.form_id;

      this.form.model.on('change', _.bind(function (model) {
        this.renderChange(_.map(model.changed, function (value, key) {
          return {
            key: /form\[settings\]\[submit\]\[([\w\d]+)\]/gi.exec(key)[1] || '',
            value: value
          };
        }));
      }, this));

      this.style = new AMOFORMS.core.form.View({
        el: this.$(this.selectors['style']),
        Model: FormSubmitStyleModel
      });

      this.style.model.form_id = options.form_id;

      this.style.model.on('change', _.bind(function (model) {
        this.renderStyleChange(_.map(model.changed, function (value, key) {
          return {
            category: /style[\d]{0,}?\[\w+\]\[([\w\d-]+)\]\[([\w\d-]+)\]/gi.exec(key)[1 ] || '',
            key: /style[\d]{0,}?\[\w+\]\[([\w\d-]+)\]\[([\w\d-]+)\]/gi.exec(key)[2] || '',
            value: value
          };
        }));
      }, this));
      this.editPosition();
      this.$('.tooltip-visual').tooltipster({
        theme: 'tooltipster-shadow',
        multiple: true,
        maxWidth: 400,
        position: 'bottom-left',
        contentAsHTML: true
      });
    },

    renderChange: function (changed_values) {
      _.each(changed_values, function (changed) {
        switch (changed.key) {
          case 'text':
            this.$(this.selectors['submit_btn'] + ' .amoforms__form_submit_btn_text').text(changed.value);
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
          this.style.model.attributes["style[elements][submit_button]["+changed.key+"]"] = changed.value;
        }
        if($.inArray(changed.key, fromVisual.padding) != -1){
          key = 'padding';
          changed.value = this.mergeCSSProperties(json, current_id, changed, key);
          changed.key = key;
          this.style.model.attributes["style[elements][submit_button]["+changed.key+"]"] = changed.value;
        }
        if($.inArray(changed.key, fromVisual.border) != -1){
          key = 'border-width';
          changed.value = this.mergeCSSProperties(json, current_id, changed, key);
          changed.key = key;
          this.style.model.attributes["style[elements][submit_button]["+changed.key+"]"] = changed.value;
        }
        json.children[".amoforms #" + current_id + " .amoforms_submit_button"].attributes[changed.key] = changed.value;
      }, this);
      $(this.selectors['style_el']).text(CSSJSON.toCSS(json));
      this.editPosition();
    },

    mergeCSSProperties: function (json, current_id, changed, key) {
      var current_css = json.children[".amoforms #" + current_id + " .amoforms_submit_button"].attributes[key].split(' '),
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

    editPosition: function () {
      var height = this.$(this.selectors['submit_btn']).innerHeight(),
          margin = height/2 - 13;
      this.$(this.selectors['row_actions']).css("margin-top", margin);
    },

    /* DOM-events */
    setEdit: function () {
      this.$el.addClass('in-edit');
      this.fillVisualEditor();
      _.each(this.$('.amoforms__colorsetting'), function (e) {
        var $colorpicker = $(e),
            color = $colorpicker.val(),
            transparent = (color == 'transparent');
        $colorpicker.css({
          color: color,
          backgroundColor: color
        });
        $colorpicker.ColorPicker({
          color: $(e).val(),
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
      this.$(this.selectors['form_wrap']).show();

      $(document.body).animate({
        scrollTop: this.$el.offset().top - 40
      }, 300);
    },

    cancelEdit: function (e) {
      if (e && e.preventDefault) {
        e.preventDefault();
      }
      $(this.selectors['style_el']).text(this.css.getStyle());
      this.$el.removeClass('in-edit');
      this.form.model.set(this.form.model.defaults);
      this.style.model.set(this.style.model.defaults);
      this.form.revert();
      this.style.revert();
      this.fillVisualEditor();
      this.$(this.selectors['form_wrap']).hide();
    },

    checkError: function (e) {
      var $this = $(e.currentTarget);

      if (!$this.val()) {
        $this.addClass('error');
      } else {
        $this.removeClass('error');
      }
    },

    clearError: function (e) {
      $(e.currentTarget).removeClass('error');
    },

    /* end of DOM-events */

    setColor: function (color) {
      this.renderStyleChange([{
        key: 'background-color',
        value: color
      }]);
      this.style.model.attributes['style[elements][submit_button][background-color]'] = color;
    },

    styleReset: function () {
      var style = this.style.model.toJSON(),
          caption = 'Are you sure you want to reset style for submit button?';

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
              id: this.style.model.form_id
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
      this.cancelEdit();
      var json = CSSJSON.toJSON($(this.selectors['style_el']).text()),
          new_style = CSSJSON.toJSON(AMOFORMS.core.fn.generateCSS(res.style));
      _.extend(json.children, new_style.children);
      this.css.setStyle(CSSJSON.toCSS(json));
      $(this.selectors['style_el']).text(this.css.getStyle());
    },

    saveClick: function () {
      this.css.setStyle($(this.selectors['style_el']).text());
      this.form.model.extendAttr(this.style.model.toJSON());
      this.style.model.defaults = this.style.model.toJSON();
      this.form.save({
        success: _.bind(function () {
          this.$(this.selectors['form_wrap']).hide();
          this.$el.removeClass('in-edit');
        }, this),
        error: _.bind(function () {
          this.$(this.selectors['actions']).addClass('animated shake');

          _.delay(_.bind(function () {
            this.$(this.selectors['actions']).removeClass('shake');
          }, this), 800);
        }, this)
      }
      );
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
    }
  });

  global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
    views: {
      submit: FormSubmit
    }
  });
}(window, jQuery, _, Backbone));
