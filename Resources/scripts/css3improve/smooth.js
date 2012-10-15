/**
 * Smooth.js v0.1
 * small js library for CSS3 transitions with fallback to $.animate in case of IE
 * http://github.com/alevkon/smooth/
 *
 * Copyright 2011, Alexey Konyshev alevkon@gmail.com
 * MIT license
 */
(function ($, transitionsAvailable) {
	var PREFIXES = ['', '-o-', '-moz-', '-webkit-', '-ms-'];
	var EVENTS = ['transitionend', 'oTransitionEnd', 'webkitTransitionEnd', 'MSTransitionEnd'];
	var def = {
		duration:500,
		easing:  'linear'
	};
	$.fn.smooth = function (stylesIn, settingsIn) {
		var callbacks = $.Deferred();
		var settings = $.extend(true, def, settingsIn);

		var styles = {};
		for (var i in stylesIn) {
			styles[i] = stylesIn[i];
		}

		if (!transitionsAvailable) {
			$.extend(true, settings, {
				complete:function () {
					callbacks.resolve();
				}
			});
			this.stop(false, false).animate(styles, settings);
		} else {
			//fixing easing CSS3-jQuery difference
			if ('swing' == settings.easing) {
				settings.easing = 'ease';
			}

			var property = [], duration = [], easing = []
				, transitionMap = {};

			var dur = (settings.duration / 1000) + 's';
			for (var style in styles) {
				property.push(style);
				duration.push(dur);
				easing.push(settings.easing);
			}
			property = property.join(', ');
			duration = duration.join(', ');
			easing = easing.join(', ');

			for (var prefix = 0; prefix < PREFIXES.length; prefix++) {
				prefix = PREFIXES[prefix];
				transitionMap[prefix + 'transition-property'] = property;
				transitionMap[prefix + 'transition-duration'] = duration;
				transitionMap[prefix + 'transition-timing-function'] = easing;
			}

			this.css(transitionMap);
			this.css(styles);

			var resolve = (function () {
				callbacks.resolve();
			});
			for (var i = 0; i < EVENTS.length; i++) {
				this.unbind(EVENTS[i]).bind(EVENTS[i], resolve);
			}
		}

		var self = this;
		callbacks.done(function () {
			if (settingsIn && settingsIn.complete && (settingsIn.complete instanceof Function)) {
				settingsIn.complete.apply(self, []);
			}
		});

		return callbacks;
	};
})(jQuery, Modernizr.csstransitions);