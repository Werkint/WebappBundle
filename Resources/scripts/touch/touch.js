(function (classTouchable, classTouched) {
	(function ($) {
		if (Modernizr.touch) {
			$.fn.touchable = function () {
				return this;
			};
			return;
		}
		var touch;
		var clearTouch = (function () {
			touch = false;
			$(document.body).find('.' + classTouchable).each(function () {
				if ($(this).hasClass(classTouched)) {
					$(this).removeClass(classTouched).trigger('untouch');
				}
			});
		});
		var setTouch = (function () {
			touch = true;
			$(this).addClass(classTouched);
		});
		$(document).ready(function () {
			$(document.body).click(function (e) {
				clearTouch();
				// TODO: propagate
			});
		});

		$.fn.touchable = function () {
			return this.addClass(classTouchable).click(function (e) {
				e.stopPropagation();
				if ($(this).prop('disabled')) {
					return;
				}
				if ($(this).hasClass(classTouched)) {
					$(this).removeClass(classTouched).trigger('untouch');
					if (!$(document.body).find('.' + classTouchable + '.' + classTouched).size()) {
						touch = false;
					}
					return;
				}
				clearTouch();
				var el = $(this);
				while (el.get(0) != document.body) {
					setTouch.call(el);
					el.trigger('touch');
					el = el.parent();
				}
			});
		};
	})(jQuery);
})('global-touchable', 'touched');