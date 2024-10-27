(function (global, $) {
  'use strict';

  var templates = {
        partials: {}
      }, name;

  $(function () {
    _.each(document.getElementsByTagName('script'), function (script) {
      name = script.getAttribute('data-name');

      if (script.getAttribute('type') == 'text/mustache') {
        if (parseInt(script.getAttribute('data-partial'))) {
          templates.partials[name] = script.innerHTML;
        } else {
          templates[name] = script.innerHTML;
        }
      }
    });

    global.AMOFORMS.templates = templates;
  });

  global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
    templates: {}
  });
}(window, jQuery));
