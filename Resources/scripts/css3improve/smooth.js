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
	var getTransition = function (prop, dur, ease) {
		var transitionMap = {},
			property = prop.join(', '),
			duration = dur.join(', '),
			easing = ease.join(', ');
		for (var i = 0; i < PREFIXES.length; i++) {
			var prefix = PREFIXES[i];
			transitionMap[prefix + 'transition-property'] = property;
			transitionMap[prefix + 'transition-duration'] = duration;
			transitionMap[prefix + 'transition-timing-function'] = easing;
		}
		return transitionMap;
	};
	var smoothIt = (function (stylesIn, settingsIn) {
		if (typeof $(this).data('smoothed') == 'undefined') {
			$(this).data('smoothed', true);
			/*for (var i = 0; i < EVENTS.length; i++) {
			 $(this).unbind(EVENTS[i]).bind(EVENTS[i], resolve);
			 }*/
		}
		var settings = $.extend(true, def, settingsIn);
		var callback = settingsIn.complete;
		var that = this;

		var styles = {};
		for (var i in stylesIn) {
			styles[i] = stylesIn[i];
		}

		if (!transitionsAvailable) {
			$.extend(true, settings, {
				complete:function () {
					callback.call(that);
				}
			});
			$(this).stop(false, false).animate(styles, settings);
		} else {
			//fixing easing CSS3-jQuery difference
			if ('swing' == settings.easing) {
				settings.easing = 'ease';
			}

			var property = [], duration = [], easing = [];

			var dur = (settings.duration / 1000) + 's';
			for (var style in styles) {
				property.push(style);
				duration.push(dur);
				easing.push(settings.easing);
			}


			$(this).css(
				getTransition(property, duration, easing)
			);
			$(this).css(styles);
		}

		if ($(this).data('smoothTm')) {
			clearTimeout($(this).data('smoothTm'));
		}
		$(this).data(
			'smoothTm',
			setTimeout(function () {
				callback.call(that);
			}, settings.duration)
		);
	});
	$.fn.smooth = (function (stylesIn, settingsIn) {
		return this.each(function () {
			smoothIt.call(this, stylesIn, settingsIn);
		});
	});
})(jQuery, Modernizr.csstransitions);