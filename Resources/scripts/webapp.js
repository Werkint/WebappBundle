app.log('App functions were included');

// Индикатор загрузки
app.conf.preloader = '#preloader';
app.data.loading = 0;
app.fn.loading = (function (state) {
	return;
	if (!app.conf.preloader) {
		return;
	}
	if (state) {
		app.data.loading++;
		if (app.data.loading == 1) {
			$(app.conf.preloader).fadeIn(100);
		}
	} else {
		app.data.loading--;
		if (app.data.loading < 0) {
			app.data.loading = 0;
		}
		if (app.data.loading == 0) {
			$(app.conf.preloader).stop(0, 0).fadeOut(100, function () {
				$(app.conf.preloader).css('opacity', 1);
			});
		}
	}
});

// Жесткое url-кодирование
app.fn.rawurlencode = (function (str) {
	str = (str + '').toString();
	return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
		replace(/\)/g, '%29').replace(/\*/g, '%2A');
});

// Форматирование даты
app.fn.date2str = (function (d) {
	// Форматирует дату как yyyy-mm-dd
	if (!d) var d = new Date();
	return d.getFullYear() + '-' + $fn.trailzero(d.getMonth() + 1) + '-' + $fn.trailzero(d.getDate());
});

// Нормировка даты
app.fn.date_correct = (function (d, h, m) {
	// Возвращает yyyy-mm-dd hh:mm, пустые заменяет текущими
	var date = new Date();
	if (!d || !$.trim(d)) d = $fn.date2str(date);
	if (!h || !$.trim(h)) h = $fn.trailzero(date.getHours());
	else h = $fn.trailzero(h);
	if (!m || !$.trim(m)) m = $fn.trailzero(date.getMinutes());
	else m = $fn.trailzero(m);
	return d + ' ' + h + ':' + m + ':00';
});

// Предварение нулями
app.fn.trailzero = (function (i) {
	var num = Number(i);
	return (num < 10) ? '0' + num : num;
});

// Запрос + сообщение об ошибке
app.fn.query = (function () {
	app.data.lastid = 0;
	return (function (action, callback, data, options) {
		if (!action) {
			return;
		}
		var defOptions = {
			root:    app.queryPrefix,
			nosplash:false,
			type:    'json'
		};
		var options = $.extend(defOptions, options ? options : {});
		var id = ++app.data.lastid;
		app.log('Query id[' + id + '], action = "' + options.root + '/' + action + '"');
		if (!options.nosplash) {
			$fn.loading(true);
		}
		var xhr;
		xhr = $.ajax({
			type:    'POST',
			url:     options.root + '/' + action,
			data:    data,
			success: function (ret) {
				if (ret.is_error) {
					alert('Ошибка! ' + ret.message);
					app.error('Query id[' + id + '], error: "' + ret.message + '"');
				} else {
					app.log('Query id[' + id + '] finished successfully');
					if (callback) {
						callback.call(xhr, options.type == 'json' ? ret.data : ret);
					}
				}
				if (!options.nosplash) {
					$fn.loading(false);
				}
			},
			error:   function (req_obj, msg, error) {
				app.error('Query id[' + id + '] ' + (error == 'abort' ? 'aborted' : 'unknown error'));
				if (!app.debug) {
					alert('Ошибка! Обновите страницу!');
				} else if (!options.nosplash) {
					$fn.loading(false);
				}
				app.log('Ошибка:', $.trim(req_obj.responseText));
			},
			dataType:options.type
		});
		return xhr;
	});
})();

app.fn.extractVal = (function (str, val_pref) {
	var regex = new RegExp('^(.*\\s)?' + val_pref + '[\-_]([a-z0-9\-_A-Z]+)(\\s.*)?$');
	regex = regex.exec(str);
	return regex ? regex[2] : null;
});

app.fn.eachSorted = (function (obj, sorter, callback) {
	var tuples = [];

	for (var key in obj) tuples.push([key, obj[key]]);

	tuples.sort(function (a, b) {
		return sorter(a[1], b[1]);
	});

	var length = tuples.length;
	while (length--) {
		callback.call(tuples[length][1], tuples[length][0], tuples[length][1]);
	}
});

app.fn.eachSortedByKey = (function (obj, key, callback) {
	$fn.eachSorted(obj, function (obj1, obj2) {
		return obj1[key] < obj2[key] ? 1 : obj1[key] > obj2[key] ? -1 : 0
	}, callback);
});

app.fn.objLength = (function (obj) {
	var len = 0;
	for (var key in obj) {
		len++;
	}
	return len;
});

app.fn.objRemoveKeys = (function (obj, keys) {
	if (!keys) {
		return;
	}
	var skipped = keys;
	while (skipped.length) {
		var key = skipped.pop();
		delete obj[key];
	}
	return $fn.objLength(obj);
});