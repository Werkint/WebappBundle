/* LIST FILTERED
 * by Bogdan Yurov <bogdan@yurov.me> aka nick4fake
 * version 0.3, 2012
 * */
(function () {
	var flist = (function () {
		// Ссылка на список
		var flist = this;

		// Настройки
		this.config = {
			boolTrue: 'да',
			boolFalse:'нет'
		};

		// Инициализаторы
		this.setters = {
			callbacks:(function () {
				this.callbacks = {};
			}),
			// Кнопки сортировки
			sorters:  (function () {
				this.sorters = $();
			}),
			// Список всех колонок
			columns:  (function () {
				this.columns = {
					rawlist:[],
					list:   {}
				};
			}),
			// Список
			list:     (function () {
				this.list = null;
			}),
			// Фильтры
			filters:  (function () {
				this.filters = {};
			}),
			// Сортировка
			orderer:  (function () {
				this.orderer = {
					column:null,
					desc:  false
				};
			})

		}
		this.oldhtml = '';
		this.restore = function () {
			if (this.oldhtml) {
				$(this.list).html(this.oldhtml);
			}
			return this;
		};
		this.init = function (name) {
			if (this.ajaxQuery) {
				this.ajaxQuery.abort();
			}
			if (name) {
				this.setters[name].call(flist);
			} else {
				$.each(this.setters, function () {
					this.call(flist);
				});
			}
			return this;
		};
		// Вызываем
		this.init();

		// Получение данных
		this.fGetter = null;
		this.ajaxQuery = null;
		this.setGetter = function (getter) {
			this.fGetter = getter;
			return this;
		};
		this.update = this.reload = function () {
			if(!this.fGetter) {
				return;
			}
			if (this.ajaxQuery) {
				this.ajaxQuery.abort();
			}
			if (this.callbacks.change) {
				this.callbacks.change.call(this);
			} else if (this.list) {
				this.setLoading();
				this.ajaxQuery = this.fGetter(this.filters, this.getOrder());
			}
			return this;
		};

		// Сортировка
		this.order = function (col, dir_desc) {
			this.orderer.column = col;
			this.orderer.desc = dir_desc;
			this.sorters.removeClass('order-desc').removeClass('order-asc');
			this.sorters.parent().filter('.col_' + col).children('button').addClass('order-' + (dir_desc ? 'desc' : 'asc'));
			this.update();
			return this;
		};
		this.getOrder = function () {
			return this.orderer.column + ' ' + (this.orderer.desc ? 'desc' : 'asc');
		};

		// Установка фильтра
		this.setFilter = function (name, val, autoupd) {
			var oldval = this.filters[name];
			this.filters[name] = val;
			if (autoupd && (oldval != val)) {
				this.update();
			}
			return this;
		};

		this.clear = function () {
			if (this.listBody) {
				this.listBody.html('');
			}
		};
		this.setRes = function (str) {
			this.clear();
			var tr = $('<tr></tr>').append($('<td></td>').html(str).addClass('nothing'));
			tr.find('td').attr('colspan', this.listHead.find('th').size());
			this.listBody.append(tr);
			this.listHead.hide();
		};
		this.setLoading = function () {
			this.clear();
			var tr = $('<tr></tr>').append($('<td></td>').html('<img src="' + CONST.webapp_res + '/listfiltered/preloader-hor.gif" alt="preloader" />').addClass('loading'));
			tr.find('td').attr('colspan', this.listHead.find('th').size());
			this.listBody.append(tr);
			this.listHead.hide();
		};

	});

	// Установка списка
	flist.prototype.setList = function (in_list) {
		this.list = in_list;
		this.oldhtml = $(this.list).html();
		this.columns.rawlist = [];
		this.columns.list = {};
		this.listBody = $(this.list).find('tbody');
		this.listHead = $(this.list).find('thead');
		var list = this;
		var regex = /^(.*\s)?col_([a-z0-9_A-Z]+)(\s.*)?$/;
		var ordr = (function () {
			list.order($(this).parent().data('colName'), $(this).hasClass('order-asc'));
		});
		this.listHead.find('th').each(function () {
			var clss = $(this).attr('class')
			var name = regex.exec($(this).attr('class'));
			list.columns.rawlist.push(name ? name[2] : '');
			if (!name) {
				return;
			}
			name = name[2];
			list.columns.list[name] = ({
				name:  name,
				params:{}
			});
			$(this).data('colName', name);
			var html = $(this).html();
			if ($(this).hasClass('sortable')) {
				var button = $(this).html('').append('<button></button>').find('button');
				button.html(html).click(ordr).addClass('global-button').addClass('flist-sorter');
				list.sorters = list.sorters.add(button);
			} else {
				$(this).html('').append('<span class="coltitle"></button>').find('span').html(html);
			}
		});
		return this;
	};

	// Установка контрольной панели
	flist.prototype.setPanel = function (panel) {
		var list = this;
		var updater = (function (test, el) {
			var el = test === true ? el : this;
			var val = $(el).attr('type') == 'checkbox' ? ($(el).prop('checked') ? $(el).val() : '') : $(el).val();
			list.setFilter($(el).attr('name'), val);
			if ($(this).hasClass('list-param-auto') || $(this).get(0).tagName == 'SELECT') {
				list.update();
			}
		});
		$(panel).find('.list-param').each(function (ind, el) {
			$(this).keydown(updater).change(updater);
			updater.call(this, true, el);
		});
		$(panel).find('.list-update').click(function () {
			list.update();
		});
		return this;
	};

	// Настройка типов колонок
	flist.prototype.setColType = function (name, type, params) {
		if (!this.columns.list[name]) {
			this.columns.list[name] = {};
		}
		this.columns.list[name].type = type;
		if (!params) {
			params = {};
		}
		this.columns.list[name].params = params;
		var col = this.sorters.parent().filter('.col_' + name);
		switch (type) {
			case 'bool':
			case 'int':
			case 'id':
				col.css('width', '1px');
				break;
		}
	};

	// Выводим полученные строки
	flist.prototype.setRows = function (rows) {
		this.listHead.show();
		this.clear();
		var list = this;
		var evi = 0;
		$.each(rows, function (ind, el) {
			var tr = $('<tr></tr>').addClass('row-' + ((++evi) % 2 == 0 ? 'even' : 'uneven'));
			for (var i = 0; i < list.columns.rawlist.length; i++) {
				var td = $('<td></td>');
				var name = list.columns.rawlist[i];
				if (name) {
					td.attr('class', 'col_' + name);
					var text = el[list.columns.rawlist[i]];
					var val = null;
					var col = list.columns.list[name];
					if (col) {
						var cont = $('<div class="type-col type-' + col.type + '"></div>');
						if (col.params.setter) {
							val = col.params.setter.call(el, text, cont, td, tr);
							if (val === false || val === null) {
								continue;
							}
							if (typeof val == 'string') {
								cont.html(val);
							}
						} else if (col.type != 'none') {
							cont.text(text);
						}
						if (val === null) {
							switch (col.type) {
								case 'int':
									cont.text(Number(text));
									break;
								case 'money':
									cont.text((col.params.prefix ? col.params.prefix : '') + String(Math.round(text * 100) / 100) + (col.params.postfix ? col.params.postfix : ''));
									break
								case 'bool':
									var val = text == col.params.equalTo;
									cont.addClass('bool-' + (val ? 'true' : 'false'));
									cont.text(val ? list.config.boolTrue : list.config.boolFalse);
									break;
							}
						}
						td.append(cont);
					} else {
						td.text(text);
					}
				}
				tr.append(td);
			}
			if (list.callbacks.rowSet) {
				list.callbacks.rowSet.call(el, tr);
			}
			list.listBody.append(tr);
		});
	};

	// Глобализируем и урбанизируем
	window.flist = flist;
})();