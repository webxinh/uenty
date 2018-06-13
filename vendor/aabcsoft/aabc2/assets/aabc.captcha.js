/**
 * aabc Captcha widget.
 *
 * This is the JavaScript widget used by the aabc\captcha\Captcha widget.
 *
 * @link http://www.aabcframework.com/
 * @copyright Copyright (c) 2008 aabc Software LLC
 * @license http://www.aabcframework.com/license/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
(function ($) {
    $.fn.aabcCaptcha = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist in jQuery.aabcCaptcha');
            return false;
        }
    };

    var defaults = {
        refreshUrl: undefined,
        hashKey: undefined
    };

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                var settings = $.extend({}, defaults, options || {});
                $e.data('aabcCaptcha', {
                    settings: settings
                });

                $e.on('click.aabcCaptcha', function () {
                    methods.refresh.apply($e);
                    return false;
                });
            });
        },

        refresh: function () {
            var $e = this,
                settings = this.data('aabcCaptcha').settings;
            $.ajax({
                url: $e.data('aabcCaptcha').settings.refreshUrl,
                dataType: 'json',
                cache: false,
                success: function (data) {
                    $e.attr('src', data.url);
                    $('body').data(settings.hashKey, [data.hash1, data.hash2]);
                }
            });
        },

        destroy: function () {
            this.off('.aabcCaptcha');
            this.removeData('aabcCaptcha');
            return this;
        },

        data: function () {
            return this.data('aabcCaptcha');
        }
    };
})(window.jQuery);
