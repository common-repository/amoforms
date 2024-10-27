(function (global, $, _, Backbone) {
    'use strict';

    Backbone.Singleton = {
        getInstance: function () {
            if (this._instance === undefined) {
                this._instance = new this();
            }
            return this._instance;
        }
    };

    var StyleStorage = function() {
        _.extend(StyleStorage, Backbone.Singleton);

        this.getStyle = function() {
            return AMOFORMS.style.css;
        };

        this.setStyle = function(css) {
            AMOFORMS.style.css = css;
        };
    };


    global.AMOFORMS = $.extend(true, global.AMOFORMS || {}, {
        views: {
            cssstorage: StyleStorage
        }
    });
}(window, jQuery, _, Backbone));
