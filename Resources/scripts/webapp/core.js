window.app = new (function () {
	this.timeStarted = window.performance ? window.performance.timing.fetchStart : new Date().getTime();

	// Список загруженных библиотек
	this._loaded = new Array();
	this.hello = (function (name) {
		this._loaded.push(name);
	});

	// Лог сообщений
	this.debug = true;
	this._debug_log = new Array();
	this._debug_dump = (function () {
		while (this._debug_log.length) {
			try {
				var row = this._debug_log.pop();
				var data = row.data;
				if (data === null) {
					data = '[NULL]';
				} else if (data === undefined) {
					data = '[UNDEFINED]';
				}
				// Получаем время
				var t = String(row.time - app.timeStarted);
				if (t.length < 4) {
					while (t.length < 3) {
						t += '0';
					}
					t = '0' + t;
				}
				// Форматируем время
				while (t.length < 6) {
					t = ' ' + t;
				}
				t = t.substr(0, t.length - 3) + '.' + t.substr(t.length - 3, 3) + ':';
				if (window.console && console.log) {
					data.unshift(t);
					switch (row.type) {
						case 1:
							console.error.apply(console, data);
							break;
						case 3:
							console.log.apply(console, data);
							break;
					}
				}
			} catch (e) {
			}
		}
	});
	this.__log = (function (type, msg) {
		try {
			var args = Array.prototype.slice.call(arguments);
			var type = args.shift();
			this._debug_log.push({
				'data':args,
				'time':new Date().getTime(),
				'type':type
			});
			if (this.debug) {
				this._debug_dump();
			}
		} catch (e) {
			if (this.debug && window.console && console.log) {
				console.log('Console error: ' + e);
			}
		}
	});
	var that = this;
	this.log = (function (msg) {
		try {
			var args = Array.prototype.slice.call(arguments);
			args.unshift(3);
			that.__log.apply(that, args);
		} catch (e) {
		}
	});
	this.error = (function (msg) {
		try {
			var args = Array.prototype.slice.call(arguments);
			args.unshift(1);
			this.__log.apply(this, args);
		} catch (e) {
		}
	});

	// Объект настроек
	this.conf = {
		'months':    ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
		'weekdays':  ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
		'dateFormat':'yy-mm-dd',
		'monthsCh':  ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря']
	}

	// Буфер данных
	this.data = {};

	// Буфер функций
	this.fn = {};

	// Префикс запросов
	this.queryPrefix = '/ajax';

	window.$fn = this.fn;
});
app.log('App is ready');