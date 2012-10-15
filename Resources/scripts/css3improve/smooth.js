/**
 * Smooth.js v0.1
 * small js library for CSS3 transitions with fallback to $.animate in case of IE
 * http://github.com/alevkon/smooth/
 *
 * Copyright 2011, Alexey Konyshev alevkon@gmail.com
 * MIT license
 */
(function ($, transitionsAvailable) {
	"use strict";
	var PREFIXES = ['', '-o-', '-moz-', '-webkit-', '-ms-'];
	var EVENTS = ['transitionend', 'oTransitionEnd', 'webkitTransitionEnd', 'MSTransitionEnd'];
	var def = {
		duration:500,
		easing:  'linear'
	};
	var resolve = (function () {
		callbacks.resolve();
	});
	var smoothIt = (function (stylesIn, settingsIn) {
		if (typeof $(this).data('smoothed') == 'undefined') {
			$(this).data('smoothed', $.Deferred());
			for (var i = 0; i < EVENTS.length; i++) {
				$(this).unbind(EVENTS[i]).bind(EVENTS[i], resolve);
			}
		}
		var callbacks = $(this).data('smoothed');
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
			$(this).stop(false, false).animate(styles, settings);
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

			$(this).css(transitionMap);
			$(this).css(styles);
		}

		if (settingsIn.complete && (settingsIn.complete instanceof Function)) {
			callbacks.done(function (target, callback) {
				return function () {
					callback.apply($(target), []);
				};
			}(this, settingsIn.complete));
		}
	});
	$.fn.smooth = (function () {
		return this.each(smoothIt);
	});
})(jQuery, Modernizr.csstransitions);