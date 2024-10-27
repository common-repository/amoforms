(function (global, Erroneous) {
  'use strict';

  var Errors = function () {
    var _this = this;
    _this._error_data = null;
    Erroneous.serverURL = ajaxurl + '?action=amoforms_send_error';
    Erroneous.register(function (err) {
      if (err.file.search(pluginurl) != -1){
        console.log(pluginurl);
        Erroneous.postToServer(_this._prepareDataToPost(err));
      }
    });
  };

  /**
   * @private
   */
  Errors.prototype._prepareDataToPost = function (error) {
    error = error || {};
    error.request = {
      method: 'GET',
      url: location.origin + location.pathname,
      query_string: location.search ? location.search.substr(1) : ''
    };
    if (this._error_data) {
      error.data = this._error_data;
    }
    return error;
  };

  /**
   * Send error to server
   * @param {Error} error
   * @param {Object} [data]
   */
  Errors.prototype.sendError = function (error, data) {
    this._error_data = data;
    Erroneous.error(error);
    this._error_data = null;
  };

  /**
   * Wrapper for ajax requests errors
   * @param xhr
   * @param status
   * @param http_error
   * @param action
   * @param data
   */
  Errors.prototype.sendErrorAjax = function (xhr, status, http_error, action, data) {
    var
        error_message = 'Error: ' + status + ' (' + http_error + ')',
        error = new Error(error_message);
    action = action || '';
    data = data || {} ;
    this.sendError(error, {
      action: action,
      data: data,
      status: status,
      http_error: http_error,
      xhr: xhr
    });
  };

  global.AMOFORMS = global.AMOFORMS || {};
  global.AMOFORMS.core = global.AMOFORMS.core || {};
  global.AMOFORMS.core.errors = new Errors();
}(window, Erroneous));
