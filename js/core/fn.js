(function (global, $) {
  'use strict';

  var fn = {},
      hexDigits = ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"],
      filemaxsize = 64;

  fn.isNumeric = function (n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
  };

  fn.s4 = function () {
    return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
  };

  fn.hex = function (x) {
    return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
  };

  fn.rgb2hex = function (rgb) {
    var hex;
    rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,?[\s+]?([\d\.]*)[\s+]?/i);
    if(rgb && rgb.length === 5){
      hex =  "#" +
          ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
          ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
          ("0" + parseInt(rgb[3],10).toString(16)).slice(-2);
      if(rgb[4] != '' && (parseFloat(rgb[4]) < 0.5)){
        hex = 'transparent';
      }
    }
    return hex;
  };

  fn.hex2rgb = function (hex) {
    if (hex.lastIndexOf('#') > -1) {
      hex = hex.replace(/#/, '0x');
    } else {
      hex = '0x' + hex;
    }

    return [hex >> 16, (hex & 0x00FF00) >> 8, hex & 0x0000FF];
  };

  fn.isDarkColor = function(hex) {
    if (hex == 'transparent') {
      return false;
    }

    var rgb = fn.hex2rgb(hex),
        brightness;

    brightness = (rgb[0] * 299) + (rgb[1] * 587) + (rgb[2] * 114);
    brightness = brightness / 255000;

    // values range from 0 to 1
    // anything greater than 0.5 should be bright enough for dark text
    return brightness < 0.5;
  };

  fn.parseDate = function (str) {
    var parts = str.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (parts !== null) {
      var d = +parts[2],
          m = +parts[1],
          y = +parts[3],
          date = new Date(y, m - 1, d);
      if (date.getFullYear() === y && date.getMonth() === m - 1) {
        return date;
      }
    }
    return null;
  };

  fn.validEmail = function (email) {
    return /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,12}(?:\.[a-z]{2})?)$/i.test(email);
  };

  fn.validDate = function (date) {
    return Boolean(this.parseDate(date));
  };

  fn.validNumber = function (number) {
    return /^\d+[\.,]?\d*$/i.test(number);
  };

  fn.validUrl = function (url) {
    return /^(https?:\/\/)?([\da-zа-яё\.-]+)\.([a-zа-яё\.]{2,6})([=&\?\/\w %\.\-]*)*\/?$/i.test(url);
  };

  fn.validSubdomain = function (subdomain) {
    return subdomain && /^[0-9a-z]+$/.test(subdomain);
  };

  fn.validApiKey = function (api_key) {
    return api_key && /^[0-9a-f]{32,40}$/i.test(api_key);
  };

  fn.validExtensions = function (extensions) {
    return extensions && /^(\.[a-z0-9]+,?\s?)*$/i.test(extensions);
  };

  fn.validFilesize = function (filesize) {
    return filesize &&  +filesize <= filemaxsize && +filesize > 0;
  };

  fn.validTax = function (tax) {
    return tax && /^[0-9]{1,3}([\.,]{1}[0-9]{1,2}%|%)$/i.test(tax);
  };

  fn.pickmeup_options = {
    first_day: 0,
    format: 'm/d/Y',
    hide_on_select: true,
    default_date: false,
    trigger_event: 'click touchstart focus'
  };

  fn.generateCSS = function(style) {
    if(style !== undefined){
      var css = '';
      _.each(style.elements, function (rule, name){
        var rules = '';
        _.each(rule, function(v, k){
          rules += k + ':' + v + ';';
        });
        switch (name) {
          case 'form_container':
            css += '.amoforms .amoforms_theme-container {' + rules + '} ';
            break;
          case 'form_row':
            css += '.amoforms .amoforms__fields__container {' + rules + '} ';
            break;
          default:
            css += '.amoforms #style-' + style.id + ' .amoforms_' + name + ' {' + rules + '} ';
            break;
        }
      }, this);
    }
    return css ? css : '';
  };

  global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
    core: {
      fn: fn
    }
  });
}(window, jQuery));
