(function (global, _) {
  'use strict';

  var sbjs = global.sbjs;

  /**
   * Analytics Module
   */
  var Analytics = function () {
    this._data = {
      sbjs: {},
      ga: {},
      datetime: (new Date).toDateString() + ' ' + (new Date).toTimeString()
    };

    this.initData();

    // Call it again for guaranteed result
    setTimeout(_.bind(function () {
      this.initData();
    }, this), 1000);
  };

  /**
   * Init Analytics data
   * @return {Object}
   */
  Analytics.prototype.initData = function () {
    this._initSbjsData();
    this._initGaData();
    return this;
  };

  /**
   * Get Analytics data
   * @return {Object}
   */
  Analytics.prototype.getData = function () {
    return this._data;
  };

  /**
   * Get Analytics data in JSON string
   * @returns {String}
   */
  Analytics.prototype.getJsonData = function () {
    return JSON.stringify(this.getData());
  };

  /**
   * Init Sourcebuster
   */
  Analytics.prototype._initSbjsData = function () {
    if (_.isObject(sbjs)) {
      if (sbjs.get) {
        this._setSbjsData(sbjs.get);
      } else {
        sbjs.init({
          callback: _.bind(this._setSbjsData, this)
        });
      }
    }
  };

  /**
   * Set Sourcebuster Data
   * @param {Object} data
   */
  Analytics.prototype._setSbjsData = function (data) {
    if (_.isObject(data)) {
      this._data.sbjs = data;
    }
  };

  /**
   * Init Google Analytics Data
   */
  Analytics.prototype._initGaData = function () {
    var _this = this;

    if (typeof ga === 'function') {
      ga(function (tracker) {
        try {
          if (_.isObject(tracker) && (typeof tracker.get === 'function')) {
            _this._data.ga.trackingId = tracker.get('trackingId');
            _this._data.ga.clientId = tracker.get('clientId');
          }
        } catch (e) {
        }
      });
    } else { //noinspection JSUnresolvedVariable
      if (typeof _gaq !== 'undefined') {
        try {
          //noinspection JSUnresolvedVariable
          _gaq.push(function () {
            //noinspection JSUnresolvedVariable,JSUnresolvedFunction
            _this._data.ga.trackingId = _gat._getTrackerByName()._getAccount();

            var utmz = document.cookie.match(/__utmz=(.+?)(&|[#]|$|;)/);
            utmz = (utmz && utmz[1]) ? utmz[1] : null;

            if (utmz) {
              _this._data.ga.clientId = utmz.split('.')[1];
            }
          });
        } catch (e) {
        }
      }
    }
  };

  global.AMOFORMS.modules = global.AMOFORMS.modules || {};
  global.AMOFORMS.modules.analytics = new Analytics();
}(window, _));
