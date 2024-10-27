(function (global, $, _, Backbone, Dropzone) {
  'use strict';

  var SettingsView,
      SettingView,
      NamePositionView,
      FieldFormView,
      FormPaddingsView,
      FontView,
      BackgroundColorView,
      BackgroundImageView,
      ThemesView,
      FormStyleView,
      FormStyleModel,
      themes = [
        {
          settings: {
            background: {
              type: 'color',
              value: 'transparent'
            },
            font: {
              family: "Arial",
              size: 15
            },
            names_position: 'before',
            form_paddings: 'no',
            borders_type: 'rounded',
            theme_id: 0
          },
          submit: {
            color: '#2184db'
          }
        },
        {
          settings: {
            background: {
              type: 'color',
              value: '#f6f7fa'
            },
            font: {
              family: "PT Sans",
              size: 15
            },
            names_position: 'before',
            form_paddings: 'yes',
            borders_type: 'rounded',
            theme: {
              id: 1
            }
          },
          submit: {
            color: '#49b65d'
          }
        },
        {
          settings: {
            background: {
              type: 'color',
              value: '#323d45'
            },
            font: {
              family: "Georgia",
              size: 15
            },
            names_position: 'above',
            form_paddings: 'yes',
            borders_type: 'rectangular',
            theme: {
              id: 2
            }
          },
          submit: {
            color: '#f78d46'
          }
        },
        {
          settings: {
            background: {
              type: 'image',
              value: AMOFORMS.images.url + 'bg/bg1.jpg'
            },
            font: {
              family: "PT Sans",
              size: 15
            },
            names_position: 'inside',
            form_paddings: 'yes',
            borders_type: 'rounded',
            theme: {
              id: 3
            }
          },
          submit: {
            color: '#ff5722'
          }
        },
        {
          settings: {
            background: {
              type: 'image',
              value: AMOFORMS.images.url + 'bg/bg3.jpg'
            },
            font: {
              family: "Times New Roman",
              size: 15
            },
            names_position: 'inside',
            form_paddings: 'yes',
            borders_type: 'rectangular',
            theme: {
              id: 4
            }
          },
          submit: {
            color: '#f78d46'
          }
        },
        {
          settings: {
            background: {
              type: 'image',
              value: AMOFORMS.images.url + 'bg/bg2.jpg'
            },
            font: {
              family: "Lucida Console",
              size: 15
            },
            names_position: 'inside',
            form_paddings: 'yes',
            borders_type: 'rounded',
            theme: {
              id: 5
            }
          },
          submit: {
            color: '#2e95d1'
          }
        }
      ];

  SettingView = Backbone.View.extend({
    ns: '.amoforms:setting:view',
    className: 'amoforms',

    events: {
      'click': 'stopPropagation'
    },

    initialize: function (options) {
      this.options = options || {};
      $(document).on('click' + this.ns, _.bind(this.remove, this));
    },

    _remove: function () {},

    remove: function () {

      if (this._removed) {
        return;
      }

      this._remove();
      this._removed = true;
      $(document).off(this.ns);
      Backbone.View.prototype.remove.apply(this, arguments);
    },

    render: function () {
      this._render_template();
      this._render();

      this.reposition();
    },

    _render_template: function() {
      this.$el.html(this.template());
    },

    _render: function () {},

    stopPropagation: function (e) {
      e.stopPropagation();
    },

    reposition: function () {
      var offset = this.options.$button.offset();

      this.$el.css({
        position: 'absolute',
        top: offset.top + this.options.$button[0].offsetHeight + 10,
        left: offset.left - (this.el.offsetWidth/2) + (this.options.$button[0].offsetWidth/2),
        zIndex: 10
      });
    },

    commit: function (new_settings) {
      if (this.ajax_sended) {
        return;
      }
      this.ajax_sended = true;

      $.ajax({
        url: ajaxurl,
        data: {
          action: AMOFORMS.ajax_action_prefix + 'update_design_settings',
          form: {
            id: this.options.form_id,
            settings: _.extend({}, new_settings)
          }
        },
        dataType: 'json',
        type: 'POST'
      })
      .always(_.bind(function () {
        this.ajax_sended = false;
      }, this))
      .done(_.bind(function (res) {

      }, this));
    }
  });

  /* Themes View */
  ThemesView = SettingView.extend({
    className: SettingView.prototype.className + ' amoforms__setting-modal-themes',
    template: _.template(
      '<div class="amoforms__settings-modal">' +
        '<ul class="amoforms__settings-modal__themes__wrapper">' +
          _.map(themes, function (v, k) {
            return '<li data-value="' + k + '"><img src="' + AMOFORMS.images.url + 'themes/theme' + (k+1) + '.png"></li>';
          }).join('') +
        '</ul>' +
      '</div>'
    ),

    events: {
      'click li': 'changeTheme'
    },

    changeTheme: function (e) {
      var val = $(e.currentTarget).attr('data-value');

      this.options.changeDesign(val);
      this.commit(_.extend({}, themes[val].settings, {
        submit: themes[val].submit
      }));
    },

    reposition: function () {
      var offset = this.options.$button.offset();

      this.$el.css({
        position: 'absolute',
        top: offset.top + this.options.$button[0].offsetHeight + 10,
        left: offset.left,
        zIndex: 10
      });
    }
  });
  /* end of Themes View */

  /* Name Position View */
  NamePositionView = SettingView.extend({
    className: SettingView.prototype.className + ' amoforms__setting-modal-name-position',
    template: _.template(
      '<div class="amoforms__settings-modal">' +
        '<button class="amoforms__setting-modal-name-position__btn" data-type="before"><b></b>Before</button>' +
        '<button class="amoforms__setting-modal-name-position__btn" data-type="above"><b></b>Above</button>' +
        '<button class="amoforms__setting-modal-name-position__btn" data-type="inside"><b></b>Inside</button>' +
      '</div>'
    ),

    events: _.extend({}, SettingView.prototype.events, {
      'click .amoforms__setting-modal-name-position__btn': 'changeNamePosition'
    }),

    _render: function () {
      this.$('[data-type="' + this.options.$button.attr('data-value') + '"]').addClass('selected');
    },

    changeNamePosition: function (e) {
      var $this = $(e.currentTarget);

      $this.parent().find('.selected').removeClass('selected');
      $this.addClass('selected');

      this.options.$button.attr('data-value', $this.attr('data-type'));
      $(document).trigger('amoforms:field:render', [$this.attr('data-type')]);
    },

    _remove: function () {
      this.commit({
        names_position: this.$('.selected').attr('data-type')
      });
    }
  });
  /* End of Name Position View */

  /* Field Form View */
  FieldFormView = SettingView.extend({
    className: SettingView.prototype.className + ' amoforms__setting-modal-field-form',
    template: _.template(
      '<div class="amoforms__settings-modal">' +
        '<button class="amoforms__setting-modal-field-form__btn" data-type="rectangular">Square</button>' +
        '<button class="amoforms__setting-modal-field-form__btn" data-type="rounded">Rounded</button>' +
      '</div>'
    ),
    css_rule: 'field-form',

    events: {
      'click .amoforms__setting-modal-field-form__btn': 'changeFieldForm'
    },

    _remove: function () {
      this.commit({
        borders_type: this.$('.selected').attr('data-type')
      });
    },

    changeFieldForm: function (e) {
      var $this = $(e.currentTarget),
          type = $this.attr('data-type');

      e.stopPropagation();

      $this.parent().find('.selected').removeClass('selected');
      $this.addClass('selected');

      this.options.$button.attr('data-value', type);
      this.options.changeSetting(this.css_rule, [type]);
      //this.options.$fields_view.attr('data-fields-form', type);
    },

    _render: function () {
      this.$('[data-type="' + this.options.$button.attr('data-value') + '"]').addClass('selected');
    }
  });
  /* End of Field Form View */

  /* Form Paddings View */
  FormPaddingsView = SettingView.extend({
    className: SettingView.prototype.className + ' amoforms__setting-modal-form-paddings',
    template: _.template(
      '<div class="amoforms__settings-modal">' +
        '<button class="amoforms__setting-modal-form-paddings__btn" data-type="yes">With borders</button>' +
        '<button class="amoforms__setting-modal-form-paddings__btn" data-type="no">Without borders</button>' +
      '</div>'
    ),
    css_rule: 'form-paddings',

    selectors: {
      size_btns: '.amoforms__size-btn',
      fields__edit: '.amoforms__fields__edit',
      fields__row: '.amoforms__fields__row'
    },

    events: _.extend({}, SettingView.prototype.events, {
      'click .amoforms__setting-modal-form-paddings__btn': 'changeFormPaddings'
    }),

    _remove: function () {
      this.commit({
        form_paddings: this.$('.selected').attr('data-type')
      });
    },

    _render: function () {
      this.$('[data-type="' + this.options.$button.attr('data-value') + '"]').addClass('selected');
    },

    changeFormPaddings: function (e) {
      var $this = $(e.currentTarget),
          val = $this.attr('data-type');

      $this.parent().find('.selected').removeClass('selected');
      $this.addClass('selected');

      this.options.$button.attr('data-value', val);
      this.options.changeSetting(this.css_rule, val);

      if (val == 'yes') {
        $(this.selectors['fields__edit']).removeClass('delete');
      } else {
        $(this.selectors['fields__edit']).addClass('delete');
      }

    }
  });
  /* End of Form Paddings View */

  /* Font View */
  FontView = SettingView.extend({
    className: SettingView.prototype.className + ' amoforms__setting-modal-font',
    template: _.template(
      '<div class="amoforms__settings-modal">' +
        '<ul class="amoforms__settings-modal__font-family">' +
          '<li style="font-family: \'PT Sans\',serif" data-value="PT Sans">PT Sans</li>' +
          '<li style="font-family: Arial,serif" data-value="Arial">Arial</li>' +
          '<li style="font-family: \'Courier New\',serif" data-value="Courier New">Courier New</li>' +
          '<li style="font-family: Georgia,serif" data-value="Georgia">Georgia</li>' +
          '<li style="font-family: \'Lucida Console\',serif" data-value="Lucida Console">Lucida Console</li>' +
          '<li style="font-family: Tahoma,serif" data-value="Tahoma">Tahoma</li>' +
          '<li style="font-family: \'Times New Roman\',serif" data-value="Times New Roman">Times New Roman</li>' +
          '<li style="font-family: Verdana,serif" data-value="Verdana">Verdana</li>' +
        '</ul>' +
      '</div>'
    ),
    css_rule: 'font',

    events: _.extend({}, SettingView.prototype.events, {
      'click li': 'changeFontFamily'
    }),

    _remove: function () {
      this.commit({
        font: {
          family: this.$('li.selected').attr('data-value')
        }
      });
    },

    changeFontFamily: function (e) {
      var $this = $(e.currentTarget);

      $this.parent().children('.selected').removeClass('selected');
      $this.addClass('selected');
      this.options.$button.attr('data-font-family', $this.attr('data-value'));
      this.changeSetting();
    },

    changeSetting: function () {
      this.options.changeSetting(this.css_rule, this.$('li.selected').attr('data-value'));
    },

    _render: function () {
      this.$('[data-value="' + this.options.$button.attr('data-font-family') + '"]').addClass('selected');
    }
  });
  /* End of Font View */

  /* Background Color View */
  BackgroundColorView = SettingView.extend({
    tagName: 'input',
    css_rule: 'background-color',

    render: function () {
      var $color_indicator = this.options.$button.find('.color'),
          color = $color_indicator.css('background-color'),
          isTransparent = false;

      if (color.indexOf('rgba') != -1) {
        var alpha = color.replace(/^.*,(.+)\)/,'$1')
        if (alpha == 0) {
          color = 'transparent';
          isTransparent = true;
        }
      } else if (color.indexOf('transparent') != -1) {
          color = 'transparent';
          isTransparent = true;
      } else {
        color = AMOFORMS.core.fn.rgb2hex(color);
      }
      this.$el.val(color).css({
        position: 'absolute',
        opacity: 0,
        top: -999,
        left: -999
      }).ColorPicker({
        color: color,
        isTransparent: isTransparent,

        onChange: _.bind(this.changeColor, this),
        onShow: _.bind(this.colorpickerShow, this),
        onHide: _.bind(this.colorpickerHide, this)
      }).ColorPickerShow();
    },

    changeColor: function (rgb, hex_color) {
      var color = hex_color == 'transparent' ? hex_color : '#' + hex_color;

      this.options.$button.find('.color').css('background-color', color);
      this.options.changeSetting(this.css_rule, color);
      this.$el.val(color);
    },

    colorpickerShow: function (colorpicker_el) {
      var offset = this.options.$button.offset(),
          color = this.$el.val();

      $(colorpicker_el).css({
        left: offset.left,
        top: offset.top + this.options.$button[0].offsetHeight
      });

      this.options.changeSetting(this.css_rule, color);
    },

    _remove: function () {
      this.commit({
        background: {
          type: 'color',
          value: this.$el.val()
        }
      });
    },

    colorpickerHide: function (colorpicker_el) {
      $(colorpicker_el).remove();
      if (this._removed) {
        this._remove();
      } else {
        this.remove();
      }
    }
  });
  /* End of Background Color View */

  /* Background Image View */
  BackgroundImageView = SettingView.extend({
    className: SettingView.prototype.className + ' amoforms__setting-modal-background-image',
    template: function () {
      var backgrounds = _.map(AMOFORMS.images.backgrounds, function (background) {
        if (!background.thumb || !background.thumb.url || !background.img_basename) {
          return '';
        }
        return '<li class="js-background-choose" data-value="' + background.url + '" data-item="' + background.img_basename + '"><img src="' + background.thumb.url + '"><div class="js-background-clear"></div></li>';
      }).join('');

      return '<div class="amoforms__settings-modal">' +
        '<div class="amoforms__settings-modal__background-image__title clearfix"><div class="title-left">Load or Drag&Drop Image</div><div class="title-right-clear">Clear</div></div>'+
        '<ul class="amoforms__settings-modal__background-image__wrapper">' +
        backgrounds +
        '<li><div id="background-dropzone" style="width: 92px; height: 92px;"><div class="dz-message" data-dz-message><span>Load Image</span><div class="dz-success-mark"><span></span></div></div></li>' +
        '</ul>' +
        '</div>';
    },

    css_rule: 'background-image',

    events: _.extend({}, SettingView.prototype.events, {
      'click li.js-background-choose': 'changeBackgroundImage',
      'click .title-right-clear': 'clearBackground',
      'click .js-background-clear': 'deleteImages'
    }),

    _render: function () {
      this.$('[data-value="' + this.options.$button.attr('data-value') + '"]').addClass('selected');
      this.initDropZone();
      this.changed = false;
      this.is_style = this.options.$button.hasClass('amoforms__style_background');

    },

    reposition: function () {
      var offset = this.options.$button.offset(),
          left;
      if (this.is_style)
        left = offset.left;
      else if ($(window).width() > 1300)
        left = offset.left + this.options.$button[0].offsetWidth - this.el.offsetWidth;
      else
        left = offset.left - (this.el.offsetWidth/2) + (this.options.$button[0].offsetWidth/2);
      this.$el.css({
        position: 'absolute',
        top: offset.top + this.options.$button[0].offsetHeight + 10,
        left: left,
        zIndex: 10
      });
    },

    initDropZone: function () {
      var selector = '#background-dropzone',
        $zone = $(selector);

      $(document.body).on('click' + this.ns, '.dz-hidden-input', function(e) {
        e.stopPropagation();
      });

      this.destroyDropZone();

      if (!$zone.length) {
        return;
      }

      this.drop_zone = new Dropzone(selector, {
        url: ajaxurl + "?action=amoforms_upload_form_background",
        paramName: 'background_image',
        autoProcessQueue: true,
        uploadMultiple: false,
        parallelUploads: 1,
        maxFiles: 1,
        previewTemplate: '<p>File</p>',
        init: _.bind(function () {

        }, this),
        success: _.bind(function (file, data) {
          // @TODO: handle !data.result
          if (!data.result) {

            return;
          }

          data = _.extend({
            image_url: '',
            thumb_url: '',
            img_basename: ''
          }, data);

          var background = {
            url: data.image_url,
            thumb: {url: data.thumb_url},
            img_basename: data.img_basename
          };

          AMOFORMS.images.backgrounds.push(background);
          this._render_template();
          this.initDropZone();
        }, this)
      });
    },

    destroyDropZone: function () {
      this.drop_zone && this.drop_zone.destroy && this.drop_zone.destroy();
      $(document).off('click' + this.ns, '.dz-input-hidden');
    },

    deleteImages: function (e) {
      var $this = $(e.currentTarget);
      e.stopPropagation();
      new AMOFORMS.core.confirm({
        template_params: {
          caption: 'Are you sure you want to delete?',
          accept_btn: 'Yes',
          decline_btn: 'No'
        },
        accept: _.bind(function (confirm) {
              if (this.$el.find('.amoforms__captcha').length > 0) {
                $('[data-type="captcha"]').attr('data-active', 'true');
              }
              $.ajax({
                  url: ajaxurl,
                  data: {
                    action: AMOFORMS.ajax_action_prefix + 'delete_form_background',
                    img_basename: $this.parent().attr('data-item')
                  },
                  dataType: 'json',
                  type: 'POST'
                }).done(_.bind(function () {
                  var nameImg = $this.parent().attr('data-item');
                  $this.closest('.js-background-choose').remove();
                  for (var p in AMOFORMS.images.backgrounds) {
                    if (nameImg == AMOFORMS.images.backgrounds[p].img_basename) {
                      AMOFORMS.images.backgrounds.splice(p, 1);
                    }
                  }
                }, this)
                );
              this.changed = ['image', ''];
              confirm.done();
            }, this),
            fail: _.bind(function () {
              confirm.failed();
            }, this),
      });
    },

    removeImages: function(e) {
      var $this = $(e.currentTarget);
      e.remove();
      this.commit({
        background: {
          type: 'color',
          value: color
        }
      });
    },

    clearBackground: function (e) {
      var color = 'transparent';

      e.stopPropagation();

      this.options.changeSetting('background-color', color);
      this.changed = ['color', color];
    },

    changeBackgroundImage: function (e) {
      var $this = $(e.currentTarget),
          val = $this.attr('data-value');

      e.stopPropagation();

      $this.parent().find('.selected').removeClass('selected');
      $this.addClass('selected');

      this.options.$button.attr('data-value', val);
      if (this.is_style) {
        this.options.$button.trigger('change');
      }
      this.options.changeSetting(this.css_rule, val);
      this.changed = ['image', this.options.$button.attr('data-value')];
    },

    _remove: function () {
      if (!this.changed || !this.changed[0] || !this.changed[1]) {
        return;
      }

      this.commit({
        background: {
          type: this.changed[0],
          value: this.changed[1]
        }
      });
    },

    destroy: function () {
      this.destroyDropZone();
      SettingView.prototype.destroy.call(this, arguments);
    }
  });
  /* End of Background Image View */


  /* Forms Styles View */
  FormStyleView = SettingView.extend({
    className: SettingView.prototype.className + ' amoforms__setting-modal-form-css',
    names: {
      form_container: 'Form Container',
      form_row: 'Form Row',
      background_color: 'Background Color',
      background_image: 'Background Image',
      border_color: 'Border Color',
      border_radius: 'Border Radius',
      border_style: 'Border Style',
      border_width: 'Border Width',
      font_family: 'Font Family',
      margin: 'Margin',
      padding: 'Padding',
      height: 'Height'
    },

    selectors: {
      save: '.js-amoforms-form-style-save',
      form: '.amoforms__fields__editor',
      row: '.amoforms__fields__container',
      style_el: '#amoforms__custom_style',
      reset: '.js-amoforms-style-reset'
    },

    events: {
      'click .amoforms__fields__expander__item__head': 'expanderToggle',
      'click .js-amoforms-form-style-cancel': 'closeModal',
      'click .js-amoforms-form-style-save': 'saveForm',
      'click [data-type="background-image"]' : 'renderBackgrounds',
      'change .amoforms__style_background' : 'updateModel',
      'click .js-amoforms-style-reset' : 'resetStyle'
    },

    template: function () {
      var form = _.map(AMOFORMS.style.form.elements, function (style, name) {
        var params = {
          background_color: style['background-color'],
          border_width: style['border-width'],
          background_image: style['background-image'],
          border_radius: style['border-radius'],
          border_color: style['border-color'],
          border_style: style['border-style'],
          font_family: style['font-family'],
          margin: style.margin,
          padding: style.padding,
          height: style.height
        },
        colorpickers = ['background_color', 'border_color', 'color'],
        lists = ['font_family', 'border_style'],
        selects = {
          font_family: [
            'PT Sans',
            'Arial',
            'Courier New',
            'Georgia',
            'Lucida Console',
            'Tahoma',
            'Times New Roman',
            'Verdana'
          ],
          border_style: [
            'solid',
            'dashed',
            'dotted',
            'double',
            'groove',
            'ridge',
            'inset',
            'outset'
          ]
        };
        var types = _.map(params, function(value, key) {
          if(value != undefined) {
            var wrapper = '<div class="style_wrapper">'+
                '<div class="amoforms__fields__edit__descr amoforms__fields__edit__descr-placeholder">'+this.names[key]+'</div>';
            if(_.indexOf(lists, key) != -1){
              wrapper += '<select class="amoforms__select__input amoforms__fields__edit__input" name="style[elements]['+name+']['+key.replace('_','-')+']">';
              _.each(selects[key], function(option) {
                switch (key) {
                  case 'font_family':
                    wrapper += '<option style="font-family: '+ option+',serif" value="'+option+'" '+(option == value ? 'selected' : '')+'>'+option+'</option>';
                    break;
                  case 'border_style':
                    wrapper += '<option value="'+option+'" '+(option == value ? 'selected' : '')+'>'+option+'</option>';
                    break;
                }
              });
              wrapper += '</select>';
            } else if (key == 'background_image') {
              wrapper += '<button class="amoforms__style_background" data-type="background-image" data-value="'+value+'"><i></i></button>';
            } else {
              wrapper += '<input type="text" class="amoforms__text-input amoforms__fields__edit__input'+
                  (($.inArray(key, colorpickers) != -1) ? ' amoforms__colorsetting' : '') +
                  '" name="style[elements]['+name+']['+key.replace('_','-')+']" value="'+value+'">';
            }
            return wrapper+'</div>';
          }
        }, this).join('');
        return '<div class="amoforms__fields__expander__item">'+
            '<div class="amoforms__fields__expander__item__head">'+
            '<p class="amoforms__fields__expander__item__head__text">'+this.names[name]+'</p>'+
            '</div>'+
            '<div class="amoforms__fields__expander__item__content">'+
            '<div class="amoforms__fields__expander__item__content__inner">'+
            '<div class="clearfix"></div>'+
            types
            +'</div>'+
            '</div>'+
            '</div>';
      }, this).join('');

      return '<div class="amoforms__settings-modal">' +
          '<div class="amoforms__styles__expander">'+
          form +
          '</div>'+
          '<div class="amoforms__fields__edit__actions">'+
          '<button class="amoforms__button-input js-amoforms-form-style-save">Save</button>'+
          '<button class="amoforms__button-input-cancel js-amoforms-form-style-cancel">Cancel</button>'+
          '<button class="amoforms__button-input-reset js-amoforms-style-reset">Reset</button>'+
          '</div>'+
          '</div>';
    },

    initialize: function (options) {
      this.options = options || {};
      this.css = new AMOFORMS.views.cssstorage;
      this.picker = AMOFORMS.core.fn.rgb2hex($(".amoforms__fields__settings__btn span.color").css("background-color"));
    },

    _render: function() {
      this.style = new AMOFORMS.core.form.View({
        el: this.$('.amoforms__styles__expander'),
        Model: FormStyleModel
      });
      this.style.model.form_id = this.options.form_id;
      this.style.model.style_id = AMOFORMS.style.form.id;
      this.style.model.style_type = AMOFORMS.style.form.type;
      this.style.model.on('change', _.bind(function (model) {
        this.renderStyleChange(_.map(model.changed, function (value, key) {
          return {
            category: /style[\d]{0,}?\[\w+\]\[([\w\d-]+)\]\[([\w\d-]+)\]/gi.exec(key)[1 ] || '',
            key: /style[\d]{0,}?\[\w+\]\[([\w\d-]+)\]\[([\w\d-]+)\]/gi.exec(key)[2] || '',
            value: value
          };
        }));
      }, this));

      _.each($('.amoforms__colorsetting'), function(e) {
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
    },

    renderStyleChange: function (changed_values) {
      var json = CSSJSON.toJSON($(this.selectors['style_el']).text());
      _.map(changed_values, function (value) {
        AMOFORMS.style.form.elements[value.category][value.key] = value.value;
        switch (value.category){
          case 'form_container':
            json.children[".amoforms .amoforms_theme-container"].attributes[value.key] = value.value;
            if(value.key == 'background-color') {
              $(".amoforms__fields__settings__btn span.color").css({
                    color: value.value,
                    backgroundColor: value.value
                  })
                  .val(value.value);
            }
            break;
          case 'form_row':
            json.children[".amoforms .amoforms__fields__container"].attributes[value.key] = value.value;
            break;
        }
      }, this);
      $(this.selectors['style_el']).text(CSSJSON.toCSS(json));
    },

    renderBackgrounds: function(e) {
      var current_inited = false;

      if (this.setting) {
        current_inited = this.setting.current_setting.$el.is(':visible') && (this.setting instanceof SettingsView);
        delete this.setting;

        if (current_inited) {
          return;
        }
      }
      this.setting = new SettingsView({form_id: this.options.form_id});
      this.setting.newSetting(e);
    },

    updateModel: function(e) {
      var name = 'style[elements][form_container][background-image]';
      this.style.model.attributes[name] = 'url(' + $(e.currentTarget).data('value')+ ')';
    },

    closeModal: function (){
      $(this.selectors['style_el']).text(this.css.getStyle());
      this.style.model.set(this.style.model.defaults);
      this.style.revert();
      $(".amoforms__fields__settings__btn span.color").css({
            color: this.picker,
            backgroundColor: this.picker
          })
          .val(this.picker);
      this.remove();
    },

    saveForm: function (){
      var $submit_btn;
      if (this.style.$el.find('input.error').length) {
        $submit_btn = this.form.$el.find(this.selectors['save']).addClass('animated shake');
        _.delay(function () {
          $submit_btn.removeClass('animated shake');
        }, 800);

        return;
      }

      this.css.setStyle($(this.selectors['style_el']).text());

      this.style.save({
        success: _.bind(function () {
          this.closeModal();
        }, this),
        error: _.bind(function () {
          this.$(this.selectors['actions']).addClass('animated shake');

          _.delay(_.bind(function () {
            this.$(this.selectors['actions']).removeClass('shake');
          }, this), 800);
        }, this)
      });
    },

    expanderToggle: function (e) {
      var $this = $(e.currentTarget).parent(),
          active = $this.hasClass('expanded');
      this
          .$('.amoforms__fields__expander__item' + '.expanded')
          .removeClass('expanded')
          .find('.amoforms__fields__expander__item__content')
          .css('min-height', '');
      this.$(this.selectors['reset']).show();

      if(!active){
        $this
            .addClass('expanded')
            .find($this.find('.amoforms__fields__expander__item__content'))
            .css('min-height', $this.find('.amoforms__fields__expander__item__content__inner')[0].offsetHeight);
        this.$(this.selectors['reset']).hide();
      }
    },

    resetStyle: function () {
      var style = this.style.model.toJSON(),
          caption = 'Are you sure you want to reset style for form and rows?';

      new AMOFORMS.core.confirm({
        template_params: {
          caption: caption,
          accept_btn: 'Yes',
          decline_btn: 'No'
        },
        accept: _.bind(function (confirm) {
          $.post(
              ajaxurl,
              {
                action: AMOFORMS.ajax_action_prefix + 'reset_field_style',
                form: {
                  id: this.style.model.form_id
                },
                style: {
                  id: this.style.model.style_id
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
          confirm.done();
        }, this)
      }, this);
    },

    dropStyle: function (res) {
      _.each(res.style.elements, function(value, name){
        _.each(value, function(prop, attr) {
          var index = "style[elements]["+name+"]["+attr+"]";
          this.style.model.defaults[index] = prop;
          if(name == "form_container" && attr == 'background-color'){
            this.picker = prop;
          }
        }, this);
      }, this);
      this.closeModal();
      this.style.model.set(this.style.model.defaults);
      var json = CSSJSON.toJSON($(this.selectors['style_el']).text()),
          new_style = CSSJSON.toJSON(AMOFORMS.core.fn.generateCSS(res.style));
      _.extend(json.children, new_style.children);
      this.css.setStyle(CSSJSON.toCSS(json));
      $(this.selectors['style_el']).text(this.css.getStyle());
    },

    reposition: function () {
      var offset = this.options.$button.offset();

      this.$el.css({
        position: 'absolute',
        top: offset.top + this.options.$button[0].offsetHeight + 10,
        left: offset.left,
        zIndex: 10
      });
    }
  });
  /* End of Form Style View */

  SettingsView = Backbone.View.extend({
    events: _.extend({}, SettingView.prototype.events, {
      'click .amoforms__fields__settings__btn': 'newSetting'
    }),

    style_el_id: '#amoforms__custom_style',

    css_rules: {
      'background-image': {
        getValue: function (json, url) {
          json.children[".amoforms .amoforms_theme-container"].attributes['background-image'] = 'url('+url+')';
          json.children[".amoforms .amoforms_theme-container"].attributes['background-color'] = 'transparent';
          return json;
        },
        afterUpdate: function () {
          this.css_rules['background-color'].value = '';
          AMOFORMS.style.form.elements.form_container['background-color'] = 'transparent';
          AMOFORMS.style.form.elements.form_container['background-image'] = 'url('+this.css_rules['background-image'].value+')';
        },
        value: ''
      },

      'background-color': {
        getValue: function (json, color) {
          json.children[".amoforms .amoforms_theme-container"].attributes['background-image'] = '';
          json.children[".amoforms .amoforms_theme-container"].attributes['background-color'] = color;
          return json;
        },
        afterUpdate: function () {
          this.css_rules['background-image'].value = '';
          AMOFORMS.style.form.elements.form_container['background-color'] = this.css_rules['background-color'].value;
          AMOFORMS.style.form.elements.form_container['background-image'] = '';
        },
        value: ''
      },

      'font': {
        getValue: function (json, font_family) {
          json.children[".amoforms .amoforms_theme-container"].attributes['font-family'] = font_family;
          return json;
        },
        afterUpdate: function () {
          AMOFORMS.style.form.elements.form_container['font-family'] = this.css_rules['font'].value;
        },
        value: ''
      },

      'field-form': {
        getValue: function (json, value) {
          var new_value = ((value == 'rounded') ? 8 : 0) + 'px';
          _.each(json.children, function(value, name) {
            if(name.indexOf('amoforms_field_element') != -1){
              json.children[name].attributes['border-radius'] = new_value;
            }
          });
          return json;
        },
        afterUpdate: function () {
          var _this = this,
              radius = ((_this.css_rules['field-form'].value == 'rounded') ? 8 : 0) + 'px',
              $input = $("input[name='style[elements][field_element][border-radius]']");
          $('.js-fields-sortable .amoforms__fields__container .amoforms__fields__row').each(function () {
            var view = $(this).data('view');
            if(view.style.model.attributes['style[elements][field_element][border-radius]'] != undefined){
              view.style.model.attributes['style[elements][field_element][border-radius]'] = radius;
              $(this).find($input).val(radius);
            }
          });
        },
        value: ''
      },

      'form-paddings': {
        getValue: function (json, value) {
          json.children[".amoforms .amoforms_theme-container"].attributes['padding'] = (value == 'yes') ? '' : '1px 0 0 0 !important; ';
          json.children[".amoforms .amoforms_theme-container"].attributes['border-width'] = (value == 'yes') ? '1px' : '0px';
          json.children[".amoforms .amoforms_theme-container"].attributes['border-color'] = (value == 'yes') ? 'rgba(0, 0, 0, 0.13)' : 'transparent';
          json.children[".amoforms .amoforms_theme-container"].attributes['border-radius'] = '0px';
          return json;
        },
        afterUpdate: function () {
          AMOFORMS.style.form.elements.form_container['padding'] = ((this.css_rules['form-paddings'].value == 'yes') ? '1px 40px 40px 40px' : '1px 0 0 0');
          AMOFORMS.style.form.elements.form_container['border-width'] = ((this.css_rules['form-paddings'].value == 'yes') ? '1px' : '0px');
          AMOFORMS.style.form.elements.form_container['border-color'] = ((this.css_rules['form-paddings'].value == 'yes') ? 'rgba(0, 0, 0, 0.13)' : 'transparent');
          AMOFORMS.style.form.elements.form_container['border-radius'] = '0';
        },
        value: ''
      }
    },

    allowed_settings: {
      'themes': ThemesView,
      'form-css' : FormStyleView,
      'name-position': NamePositionView,
      'field-form': FieldFormView,
      'form-paddings': FormPaddingsView,
      'font': FontView,
      'background-color': BackgroundColorView,
      'background-image': BackgroundImageView
    },

    initialize: function (options) {
      this.options = options || {};
      this.css = new AMOFORMS.views.cssstorage;
      this.updateRulesFromButtons();
    },

    updateRulesFromButtons: function () {
      var type;

      this.$('.amoforms__fields__settings__btn').each(_.bind(function (key, el) {
        type = el.getAttribute('data-type');

        switch (type) {
          case 'background-color':
            if (!this.$('[data-type="background-image"]').attr('data-value')) {
              var color = $(el).find('.color').css('background-color');

              if (color.indexOf('rgba') != -1) {
                var alpha = color.replace(/^.*,(.+)\)/,'$1')
                if (alpha == 0) {
                  color = 'transparent';
                }
              } else if (color.indexOf('transparent') != -1) {
                color = 'transparent';
              } else {
                color = AMOFORMS.core.fn.rgb2hex(color);
              }

              this.css_rules['background-color'].value = color;
            }
            break;

          case 'form-paddings':
          case 'field-form':
          case 'background-image':
            if (el.getAttribute('data-value')) {
              this.css_rules[type].value = el.getAttribute('data-value');
            }
            break;

          case 'font':
            this.css_rules['font'].value = el.getAttribute('data-font-family');
            break;
        }
      }, this));
    },

    updateCSS: function (rule, value) {
      var json;

      if (rule && value && this.css_rules[rule]) {
        this.css_rules[rule].value = value;

        if (_.isFunction(this.css_rules[rule].afterUpdate)) {
          this.css_rules[rule].afterUpdate.call(this);
        }
      }

      json = CSSJSON.toJSON(this.css.getStyle());
      _.each(this.css_rules, function (rule) {
        if (rule.value) {
          json = rule.getValue(json, rule.value);
        }
      }, this);

      this.css.setStyle(CSSJSON.toCSS(json));
      $(this.style_el_id).text(this.css.getStyle());

      $(document).trigger('amoforms:field:render', [this.$('[data-type="name-position"]').attr('data-value')]);
    },

    newSetting: function (e) {
      var $this = $(e.currentTarget),
          current_inited = false;
      e.stopPropagation();
      if (this.current_setting) {
        current_inited = !this.current_setting._removed && (this.current_setting instanceof this.allowed_settings[$this.attr('data-type')]);
        this.current_setting.remove();

        if (current_inited) {
          return;
        }
      }

      this.current_setting = new this.allowed_settings[$this.attr('data-type')]({
        form_id: this.options.form_id,
        $button: $this,
        $fields: this.options.$fields,
        changeSetting: _.bind(this.updateCSS, this),
        changeDesign: _.bind(this.updateDesign, this)
      });
      $(document.body).append(this.current_setting.$el);
      this.current_setting.render();
    },

    updateDesign: function (theme_index) {
      var theme = themes[theme_index],
          json,
          _this = this;
      if(theme_index == 0 || theme_index == 1 || theme_index == 2){
        var color = (theme_index == 2) ? '#fff' : '#313942',
            margin = (theme_index == 2) ? '0 10px 5px 0' : '0 10px 0 0';
        $('.js-fields-sortable .amoforms__fields__container .amoforms__fields__row').each(function () {
          var view = $(this).data('view');
          if(view.style.model.attributes['style[elements][field_label][color]'] != undefined){
            view.style.model.attributes['style[elements][field_label][color]'] = color;
            $(this).find("input[name='style[elements][field_label][color]']").val(color);
          }
          if(view.style.model.attributes['style[elements][field_label][margin]'] != undefined){
            view.style.model.attributes['style[elements][field_label][margin]'] = margin;
            $(this).find("input[name='style[elements][field_label][margin]']").val(margin);
          }
          json = CSSJSON.toJSON(_this.css.getStyle());
          _.each(json.children, function (value, name) {
            if(name.indexOf('amoforms_field_label') != -1){
              json.children[name].attributes.color = color;
              json.children[name].attributes.margin = margin;
            }
          });
          _this.css.setStyle(CSSJSON.toCSS(json));
          $(_this.style_el_id).text(_this.css.getStyle());
        });
      }

      _.each(theme.settings, function (setting, key) {
        switch (key) {
          case 'background':
            if (setting.type == 'color') {
              this.$('[data-type="background-color"] .color').css('background-color', setting.value);
              this.$('[data-type="background-image"]').attr('data-value', '');
              AMOFORMS.style.form.elements.form_container['background-color'] = setting.value;
              AMOFORMS.style.form.elements.form_container['background-image'] = '';
              this.css_rules['background-image'].value = '';
            } else {
              this.$('[data-type="background-image"]').attr('data-value', setting.value);
              this.$('[data-type="background-color"] .color').css('background-color', '#fff');
              AMOFORMS.style.form.elements.form_container['background-image'] = setting.value;
              AMOFORMS.style.form.elements.form_container['background-color'] = '';
              this.css_rules['background-color'].value = '';
            }
            break;

          case 'names_position':
            this.$('[data-type="name-position"]').attr('data-value', setting);
            break;

          case 'font':
            this.$('[data-type="font"]')
              .attr('data-font-family', setting.family);
              AMOFORMS.style.form.elements.form_container['font-family'] = setting.family;
            break;

          case 'borders_type':
            this.$('[data-type="field-form"]').attr('data-value', setting);
            break;

          case 'form_paddings':
            this.$('[data-type="form-paddings"]').attr('data-value', setting);
            break;
        }
      }, this);

      this.updateRulesFromButtons();
      this.updateCSS();

      this.options.submit.setColor(theme.submit.color);
    }
  });

  FormStyleModel = AMOFORMS.core.form.Model.extend({
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
              },
              style: {
                id: this.style_id,
                type: this.style_type,
                is_type_style: true
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

  global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
    views: {
      settings: SettingsView
    }
  });
}(window, jQuery, _, Backbone, Dropzone));
