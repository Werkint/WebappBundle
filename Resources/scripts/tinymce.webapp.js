(function (app) {
	console.log(app);
	app.conf.tinymce = {
		/**
		 * Configuration should ONLY be affected by changing of this property ("conf") in runtime
		 */
		conf:      {
			plugins:                          ['autolink', 'lists', 'pagebreak', 'style', 'images', 'video', 'mylink', 'inlinepopups', 'searchreplace', 'paste', 'directionality', 'noneditable', 'xhtmlxtras', 'template', 'advlist'],
			theme_advanced_toolbar_location:  'top',
			theme_advanced_toolbar_align:     'center',
			theme_advanced_statusbar_location:'bottom',
			theme:                            'advanced',
			language:                         'ru',
			object_resizing:                  false,
			content_css:                      CONST.webapp_res + '/tinymce/paragraph.css',
			convert_urls:                     false,
			pagebreak_separator:              '<!--more-->'
		},
		/**
		 * First property controls Advanced theme bars preloading
		 */
		bars:      [true,
			['bold', 'italic', 'underline', true, 'justifyleft', 'justifycenter', 'justifyright', 'formatselect', 'bullist', 'numlist', true, 'link', 'unlink', 'images', 'video', true, 'pagebreak', true, 'code'],
			[], [], []
		],
		script_url:CONST.webapp_res + '/tinymce/tiny_mce/tiny_mce.js'
	};

	/**
	 * TinyMce loader
	 * @type {Function}
	 */
	app.fn.editbox = (function (id, close, options) {
		var editor = $('#' + id);
		if (!editor.size()) {
			return app.error('Editor with id=' + id + ' not found');
		}
		if (close) {
			return tinyMCE.execCommand('mceRemoveControl', false, id);
		}

		// Settings preload
		var conf = $.extend({}, app.conf.tinymce.conf, options, {
			script_url:app.conf.tinymce.script_url,
			oninit:    (function () {
				app.log('TinyMCE loaded');
				// This event could be bound to initialize editor after load
				$(editor).trigger('loadedTinyMce');
			})
		});
		conf.plugins = conf.plugins.join();
		if (!conf.width) {
			conf.width = Math.round(editor.width());
		}
		if (!conf.height) {
			conf.height = Math.round(editor.height());
		}
		// Formatting bars
		if (app.conf.tinymce.bars[0]) {
			var separator = app.conf.tinymce.pluginSeparator;
			for (var i = 1; i < app.conf.tinymce.bars.length; i++) {
				var bar = app.conf.tinymce.bars[i];
				for (var j = 0; j < bar.length; j++) {
					if (bar[j] === true) {
						bar[j] = separator;
					}
				}
				conf['theme_advanced_buttons' + String(i)] = bar.join(',');
			}
		}

		// Start
		editor.tinymce(conf);
	});
})(window.app);