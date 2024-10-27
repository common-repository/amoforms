(function (global, $, Backbone, _) {
  'use strict';

  var PageView, SidebarView, FieldsView,
      prepareField = function (field) {
        var attrs = {};
        _.each(field, function (value, key) {
          attrs[key.replace(/field\[(.*)\]/gi, '$1')] = value;
        });
        return attrs;
      };

  SidebarView = Backbone.View.extend({
    selectors: {
      item: '.amoforms__fields__expander__item',
      item_head: '.amoforms__fields__expander__item__head',
      fields: '.amoforms__fields__expander__item__content__fields'
    },

    events: {
      'click .amoforms__fields__expander__item:not(.expanded)': 'itemToggle',
      'click .amoforms__fields__expander__item__content__fields__field': 'addNewFieldClick',
      'focus .js-amoforms-shortcode': 'selectOnFocus',
      'mouseup .js-amoforms-shortcode': 'selectOnMouseUp'
    },

    initialize: function () {
      this.itemToggle({
        currentTarget: this.$(this.selectors['item'] + ':first')
      });
      this.css = new AMOFORMS.views.cssstorage;
      var _this = this;
      this.$(this.selectors['fields']).each(function($i) {
        _this.items_new = new Sortable(_this.$(_this.selectors['fields'])[$i], {
          group: {
            name: 'fields',
            put: false,
            pull: 'clone'
          },
          scroll: _this.$('.amoforms__fields__expander__item__head:first')[0],
          scrollSensitivity: 100,
          scrollSpeed: 20,
          sort: false,

          onMove: _.bind(function (e) {
            var $dragEl = $(e.dragged);
            if ($dragEl.find('[data-type="captcha"]').length > 0 && $dragEl.find('[data-type="captcha"]').attr('data-active') == 'false') {
              return;
            }

            $dragEl.find('.amoforms__fields__container').remove();

            $dragEl.append(_this.generateField({
              type: $dragEl.find('.amoforms__fields__expander__item__content__fields__field').attr('data-type'),
              class_name: 'sortable-ghost'
            }));

          }, _this),

          onEnd: _.bind(function (e) {
            var $dragEl = $(e.item),
                type = $dragEl.find('.amoforms__fields__expander__item__content__fields__field').attr('data-type'),
                pos = 0;

            if (!$dragEl.closest(_this.selectors['fields']).length) {
              for (var i = 0; i < $dragEl.index(); i++) {
                pos += $($dragEl.siblings()[i]).find(".amoforms__fields__row").length;
              }
              AMOFORMS.app.createField({
                type: type,
                position: pos,
                success: _.bind(function (res) {
                  var $new_field = _this.generateField({
                    field: res.field,
                    style: res.style,
                    type: type
                  });

                  $dragEl.before($new_field);
                  $dragEl.remove();

                  AMOFORMS.app.addField($new_field);
                  if (type == 'captcha') {
                    $('[data-type="captcha"]').attr('data-active', 'false');
                  }
                }, _this)
              });
            } else {
              $dragEl.find('.amoforms__fields__container').remove();
            }
          }, _this)
        });
      });
    },

    /* DOM-events */
    addNewFieldClick: function (e) {
      var type = $(e.currentTarget).attr('data-type');
      if (type == 'captcha' && $(e.currentTarget).attr('data-active') == 'false') {
        return;
      }
      AMOFORMS.app.createField({
        type: type,
        position: AMOFORMS.app.fields.$('.js-fields-sortable .amoforms__fields__row').length,
        success: _.bind(function (res) {
          var $new_field = this.generateField({
            field: res.field,
            style: res.style,
            type: type
          });

          AMOFORMS.app.fields.$('.js-fields-sortable').append($new_field);

          AMOFORMS.app.addField($new_field);
          if (type == 'captcha') {
            $('[data-type="captcha"]').attr('data-active', 'false');
          }
        }, this)
      });
    },

    itemToggle: function (e) {
      var $this = $(e.currentTarget);

      this
        .$(this.selectors['item'] + '.expanded')
        .removeClass('expanded')
        .find('.amoforms__fields__expander__item__content')
        .css('min-height', '');

      $this
        .addClass('expanded')
        .find($this.find('.amoforms__fields__expander__item__content'))
        .css('min-height', $this.find('.amoforms__fields__expander__item__content__inner')[0].offsetHeight);
    },

    selectOnMouseUp: function (e) {
      setTimeout(function () {
        $(e.currentTarget).select();
      }, 30);
    },
    /* end of DOM-events */

      generateField: function (options) {
      var render_params,
          css,
          $style = $("#amoforms__custom_style");
      options = options || {};
        if(options.style){
          css = AMOFORMS.core.fn.generateCSS(options.style);
          $style.append(css);
          this.css.setStyle($style.text());
        }
      render_params = {
        class_name: options.class_name,
        edit_mode: true,
        field_id: (options.field && options.field.id) ? options.field.id : 999,
        field: options.field || { name: options.type.charAt(0).toUpperCase() + options.type.slice(1) },
        is_pos_after: options.field ? options.field.description_position == AMOFORMS.consts.pos_after : '',
        is_layout_inline: options.field ? options.field.layout == AMOFORMS.consts.layout_inline : '',
        use_mask: options.field ? options.field.options.use_mask : false,
        system_masks: options.field ? options.field.options.system_masks : [],
        default_mask: options.field ? options.field.options.default_mask : '',
        field_style: options.style,
        grid: {
          left: options.field ? options.field.grid == 1 : false,
          right: options.field ? options.field.grid == 2 : false,
          full: options.field ? options.field.grid == 0 : true
        },
        consts: AMOFORMS.consts
      };
      render_params['name_position_' + AMOFORMS.app.$('.amoforms__fields__settings__btn[data-type="name-position"]').attr('data-value')] = true;
      render_params['is_' + options.type] = true;
      return $(Mustache.render(AMOFORMS.templates.field_in_edit, render_params, AMOFORMS.templates.partials));
    },

  });

  FieldsView = Backbone.View.extend({
    selectors: {
      field: '.amoforms__fields__row',
      fields_container: '.amoforms__fields__container',
      fields_container_list: '.amoforms__fields__container__list',
      submit: '.amoforms__fields__row-submit',
      edit_form: '.amoforms__fields__edit',
      settings: '.amoforms__fields__settings',
      style_el: '#amoforms__custom_style'
    },

    events: {
      'mouseenter .amoforms__fields__container': 'setContainerHover',
      'mouseleave .amoforms__fields__container': 'removeContainerHover',
      'click .amoforms__fields__editor__row__actions__action-edit': 'editorLock',
      'click .js-amoforms-field-cancel': 'editorUnlock',
      'click .js-amoforms-style-save': 'editorUnlock',
      'click .js-amoforms-style-reset': 'editorUnlock',
      'click .js-amoforms-field-save': 'editorUnlock'
    },

    initialize: function () {
      var _this = this;

      this.css = new AMOFORMS.views.cssstorage;

      this.form_id = this.$el.attr('data-form-id');

      this.initSubmit();

      this.settings = new AMOFORMS.views.settings({
        el: this.$(this.selectors['settings']),
        form_id: this.form_id,
        $fields: this.$el,
        submit: this.submit
      });

      this.sortables = [];

      this.$('.js-fields-sortable ' + this.selectors['field']).each(function() {
        _this.addField($(this));
      });

      Sortable.create(this.$el.find('.js-fields-sortable')[0], {
        group: {name: 'fields', pull: true, put: true},
        delay: 0,
        animation: 250,
        sort: true,
        disabled: false,
        draggable: this.selectors['fields_container'],
        filter: '.amoforms__fields__row-submit',
        scroll: this.$(this.selectors['settings'])[0],
        scrollSensitivity: 100,
        scrollSpeed: 20,

        onEnd: _.bind(function (e) {
          var $dragEl = $(e.item).removeClass('drag-started'),
              view, fields = [];

          if ($dragEl.hasClass('drag-hidden')) {
            $dragEl
                .removeClass('drag-hidden')
                .find(this.selectors['edit_form'])
                .show();
          }

          this.$('.js-fields-sortable .amoforms__fields__container .amoforms__fields__row').each(function () {
            view = $(this).data('view');
            fields.push(prepareField(view.form.model.toJSON()));
          });

          $.ajax({
            url: ajaxurl,
            data: {
              action: AMOFORMS.ajax_action_prefix + 'update_fields',
              form: {
                id: this.form_id,
                fields: fields
              }
            },
            dataType: 'json',
            type: 'POST'
          });

          this.$el.removeClass('drag-sorting');
        }, this)
      });
      _.each(this.$el.find(this.selectors['fields_container_list']), function(el) {
        var put = $(el).find(this.selectors['field']).length != 2;
        this.sortables.push(this.createSortableField(el, put));
      }, this);
    },

    createSortableField: function(el, put) {
      put = (typeof put === 'boolean') ? put : true;
      return Sortable.create(el, {
        group: {name: 'field', pull: true, put: put},
        draggable: this.selectors['field'],
        animation: 150,
        sort: true,

        onMove: _.bind(function (e) {
          if ($(e.to).find(this.selectors['field']).length > 1 && e.from != e.to) {
            return false;
          }
          if($(e.related).hasClass('full')){
            $(e.related).removeClass('full').addClass('half');
            $(e.dragged).removeClass('full').addClass('half');
          } else if($(e.related).hasClass('half')){
            $(e.dragged).removeClass('full').addClass('half');
          } else if (e.from != e.to) {
            $(e.dragged).removeClass('half').addClass('full');
          }
        }, this),
        onStart: _.bind(function (e) {
          if($(e.item).hasClass('half')){
            this.createFieldContainer(e);
          }
        }, this),
        onRemove: _.bind(function (e){
          this.removeContainerHover($(e.target).parent());
          var $source = $(e.target),
              count = $source.find(this.selectors['field']).length;
          if(count == 0){
            $source.parent().remove();
          } else if(count == 1){
            $source.find(this.selectors['field']).removeClass('half').addClass('full');
            this.toggleBlockList(e.target, true);
          }
        }, this),
        onAdd: _.bind(function (e) {
          var $siblings = $(e.item).siblings('.amoforms__fields__row');
          if($siblings.length == 1){
            $siblings.removeClass('full').addClass('half');
            $(e.item).removeClass('full').addClass('half');
            this.toggleBlockList(e.target, false);
          } else if ($siblings.length == 0){
            $siblings.removeClass('half').addClass('full');
            $(e.item).removeClass('half').addClass('full');
            this.toggleBlockList(e.target, true);
          }
        }, this),
        onEnd: _.bind(function (e) {
          var $container = $(e.item).closest(this.selectors['fields_container']),
              view, fields = [];
          if($container.attr('id') == 'empty-container'){
            $container.removeAttr('id');
          } else {
            _.each($('.js-fields-sortable ' + this.selectors['fields_container']), function(el) {
              if($(el).attr('id') == 'empty-container'){
                $(el).remove();
              }
            });
          }
          this.checkEmptySpace();
          this.$('.js-fields-sortable .amoforms__fields__container .amoforms__fields__row').each(function () {
            var before = $(this).prev('.amoforms__fields__row')[0] != undefined,
                after = $(this).next('.amoforms__fields__row')[0] != undefined,
                pos = (before) ? 2 : (after) ? 1 : 0;
            view = $(this).data('view');
            if($(this).hasClass('half')){
              view.form.model.attributes["field[grid]"] = pos;
            } else if(view.form.model.attributes["field[grid]"] != 0){
              view.form.model.attributes["field[grid]"] = 0;
            }
            fields.push(prepareField(view.form.model.toJSON()));
          });
          $.ajax({
            url: ajaxurl,
            data: {
              action: AMOFORMS.ajax_action_prefix + 'update_fields',
              form: {
                id: this.form_id,
                fields: fields
              }
            },
            dataType: 'json',
            type: 'POST'
          });
          $(document).trigger('amoforms:field:render');
        }, this)
      }, this);
    },

    createFieldContainer: function(e) {
      var $this = $(e.target).parent(),
          $container =  '<div id="empty-container" class="amoforms__fields__container amoforms__fields__container_hover">'+
                          '<div class="amoforms__fields__container__handler"></div>'+
                            '<div class="amoforms__fields__container__list">'+
                            '</div>'+
                            '<div class="clearfix"></div>'+
                        '</div>';
      $($container).insertAfter($this);
      this.sortables.push(this.createSortableField($this.next().find(this.selectors['fields_container_list'])[0]));
    },

    checkEmptySpace: function() {
      _.each($(this.selectors['field']), function (field) {
        if($(field).hasClass('half') && $(field).siblings(this.selectors['field']).length == 0){
          $(field).removeClass('half').addClass('full');
        }
      }, this);
    },

    getSortable: function(target) {
      var element_index = $(target).index(this.selectors['fields_container_list']),
          sort = false;
      _.each(this.sortables, function (sortable) {
        var sortable_index = $(sortable.el).index(this.selectors['fields_container_list']);
        if(sortable_index == element_index){
          sort = sortable;
        }
      }, this);
      return sort;
    },

    toggleBlockList: function(target, put, pull) {
      put = (typeof put === 'boolean') ? put : true;
      pull = (typeof pull === 'boolean') ? pull : true;
      var sortable = this.getSortable(target);
      if(sortable){
        sortable.option("group", {name: 'field', pull: pull, put: put});
      }
    },

    toggleDrag: function(target, status) {
      status = (typeof status === 'boolean') ? status : true;
      var sortable = this.getSortable(target);
      if(sortable){
        sortable.option("disabled", status);
      }
    },

    setContainerHover: function(e) {
      var $el = e.currentTarget ? $(e.currentTarget) : e,
          height = $el.height();
      if(!$el.hasClass("amoforms__fields__container")){
        $el = $el.parent().parent().parent();
      }
      $el.addClass('amoforms__fields__container_hover');
      $el.find('.amoforms__fields__container__handler').css({"opacity":"1","margin-top":height/2-9});
      this.handlerColor($el);
    },

    removeContainerHover: function(e) {
      var $el = e.currentTarget ? $(e.currentTarget) : e;
      if(!$el.hasClass("amoforms__fields__container")){
        $el = $el.parent().parent().parent();
      }
      $el.removeClass('amoforms__fields__container_hover');
      $el.find('.amoforms__fields__container__handler').css("opacity", "0");
    },

    handlerColor: function($e) {
      var element = $e,
          parentBgColor = element.css('background-color'),
          $handler = $e.find('.amoforms__fields__container__handler');
      while (this.isBgColorTransparent(element)) {
        element = element.parent();
        if (element.is("html")) {
          parentBgColor = 'rgb(255, 255, 255)';
          break;
        }
        parentBgColor = element.css('background-color');
      }
      if (parentBgColor && parentBgColor.indexOf('rgb') != -1) {
        parentBgColor = AMOFORMS.core.fn.rgb2hex(parentBgColor);
      }
      if (parentBgColor && AMOFORMS.core.fn.isDarkColor(parentBgColor)) {
        $handler.addClass("white");
        $e.addClass("white");
      } else if($handler.hasClass("white")){
        $handler.removeClass("white");
        $e.removeClass("white");
      }
    },

    isBgColorTransparent: function(elem) {
      var bgColor = elem.css('background-color');
      return bgColor.indexOf('rgba') != -1 || bgColor.indexOf('transparent') != -1;
    },

    editorLock: function(e) {
      var $el = e.currentTarget ? $(e.currentTarget) : e,
          $elem = $el.parents(this.selectors['fields_container_list']),
          $parent = $el.parents(".amoforms__fields__container"),
          json = CSSJSON.toJSON(this.css.getStyle()),
          copy = {};
      copy[".amoforms .amoforms__fields__container_locked"] = json.children[".amoforms .amoforms__fields__container"];
      _.extend(json.children, copy);
      this.css.setStyle(CSSJSON.toCSS(json));
      $(this.selectors['style_el']).text(this.css.getStyle());
      $parent
          .removeClass("amoforms__fields__container_hover")
          .removeClass("amoforms__fields__container")
          .addClass("amoforms__fields__container_locked");

      $parent.find('.amoforms__fields__container__handler').css('opacity','0');
      this.toggleDrag($elem, true);
    },

    editorUnlock: function(e) {
      var $el = e.currentTarget ? $(e.currentTarget) : e,
          $elem = $el.parents(this.selectors['fields_container_list']),
          $parent = $el.parents(".amoforms__fields__container_locked");
      $parent
          .removeClass("amoforms__fields__container_locked")
          .addClass("amoforms__fields__container");
      this.toggleDrag($elem, false);
    },

    addField: function ($el) {
      var view = new AMOFORMS.views.field({
        el: $el,
        form_id: this.form_id
      });
      $el.data('view', view);
      return view;
    },

    initSubmit: function () {
      this.submit = new AMOFORMS.views.submit({
        el: this.$(this.selectors['submit']),
        form_id: this.form_id
      });
    }
  });

  PageView = Backbone.View.extend({
    selectors: {
      'style_el' : '#amoforms__custom_style'
    },
    events: {
      'click #delete_form': 'deleteFormClick',
      'click #duplicate_form': 'duplicateFormClick',
      'click #reset_form': 'resetFormClick',
      'click #user_guide': 'openGuide'
    },

    sendRequest: function (action, error) {
      $.post(
        ajaxurl,
        {
          action: AMOFORMS.ajax_action_prefix + action,
          form: {
            id: this.fields.form_id
          }
        },
        function (res) {
          if (!res.form_url) {
            error();
          } else {
            window.location = res.form_url;
          }
        }
      );
    },

    initialize: function () {
      this.sidebar = new SidebarView({ el: $('#amoforms_sidebar') });
      this.fields = new FieldsView({ el: $('#amoforms_fields') });
      this.css = new AMOFORMS.views.cssstorage;
    },

    deleteFormClick: function (e) {
      var $this = $(e.currentTarget);

      $this[0].blur();

      new AMOFORMS.core.confirm({
        template_params: {
          caption: 'Are you sure you want to delete form?',
          accept_btn: 'Yes',
          decline_btn: 'No'
        },
        accept: _.bind(function (confirm) {
          this.sendRequest('delete_form', _.bind(function () {
            this.shakeError($this);
          }, this));

          confirm.done();
        }, this)
      });
    },

    duplicateFormClick: function (e) {
      this.sendRequest('duplicate_form', function () {
        this.shakeError($(e.currentTarget));
      });
    },

    openGuide: function () {
      window.open('http://lp.amocrm.com/wordpress/amoForms%20Layout%20User%20Guide.pdf','_blank');
    },

    resetFormClick: function (e) {
      var _this = this;
      new AMOFORMS.core.confirm({
        template_params: {
          caption: 'Are you sure you want to reset form styles?',
          accept_btn: 'Yes',
          decline_btn: 'No'
        },
        accept: _.bind(function (confirm) {
          $.post(
              ajaxurl,
              {
                action: AMOFORMS.ajax_action_prefix + 'reset_form_style',
                form: {
                  id: _this.fields.form_id
                }
              },
              function (res) {
                if (!res.result) {
                  _this.shakeError($(e.currentTarget));
                } else {
                  _this.dropFormStyle(res);
                }
              }
          );
          confirm.done();
        }, this)
      });

    },

    dropFormStyle: function (res) {
      _.each(res.style, function(style){
        if(style.type == 'form'){
          _.each(style.elements, function(value, name) {
            _.each(value, function(prop, attr) {
              AMOFORMS.style.form.elements[name][attr] = prop;
            });
          }, this);
        }
      }, this);
      $(".amoforms__fields__settings__btn span.color").removeAttr("style");
      var css = '';
      _.each(res.style, function (value) {
        css +=  AMOFORMS.core.fn.generateCSS(value);
      });
      this.css.setStyle(css);
      $(this.selectors['style_el']).text(this.css.getStyle());
      $(document).trigger('amoforms:field:reset', res);

    },

    shakeError: function ($el) {
      $el.addClass('animated shake');
      setTimeout(function () {
        $el.removeClass('animated shake');
      }, 800);
    },

    duplicateField: function ($field_el, field_id) {
      var fail = function () {
            $field_el.find('.amoforms__fields__editor__row__actions').addClass('visible animated shake');

            _.delay(function () {
              $field_el.find('.amoforms__fields__editor__row__actions').removeClass('visible animated shake');
            }, 800);
          };

      if (this.duplicate_wip) {
        return;
      }
      this.duplicate_wip = true;

      $.ajax({
        url: ajaxurl,
        data: {
          action: AMOFORMS.ajax_action_prefix + 'duplicate_field',
          form: {
            id: this.fields.form_id
          },
          field: {
            id: field_id
          }
        },
        dataType: 'json',
        type: 'POST'
      })
      .always(_.bind(function () {
        this.duplicate_wip = false;
      }, this))
      .done(_.bind(function (res) {
        if (res.result) {
          var $field = (this.sidebar.generateField({
            field: res.field,
            style: res.style,
            type: res.field.type
          }));
          this.addField($field);
          $field_el.parent().parent().after($field);
        } else {
          fail();
        }
      }, this))
      .fail(_.bind(function () {
        fail();
      }, this));
    },

    createField: function (options) {
      if (this.create_wip) {
        return;
      }
      this.create_wip = true;

      $.ajax({
        url: ajaxurl,
        data: {
          action: AMOFORMS.ajax_action_prefix + 'add_field',
          form: {
            id: this.fields.form_id,
          },
          field: {
            type: options.type,
            position: options.position
          }
        },
        dataType: 'json',
        type: 'POST'
      })
      .always(_.bind(function () {
        this.create_wip = false;
      }, this))
       .done(_.bind(function (res) {
        if (res.result) {
          (options.success || function () {})(res);
        } else {
          (options.fail || function () {})(res);
        }
      }, this))
      .fail(_.bind(function (res) {
        (options.fail || function () {})(res);
      }, this));
    },

    addField: function ($el) {
      var $this = this.fields;
      $this.sortables.push(
          $this.createSortableField(
              $el.find($this.selectors['fields_container_list'])[0]
          )
      );
      this.fields.addField($el.find('.amoforms__fields__row'));
    }
  });

  $(function () {
    global.AMOFORMS.app = new PageView({ el: $('#amoforms') });
  });

  global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
    ajax_action_prefix: 'amoforms_',
    views: {
      page: PageView,
      sidebar: SidebarView,
      fields: FieldsView
    }
  });
}(window, jQuery, Backbone, _));
