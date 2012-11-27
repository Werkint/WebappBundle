/* LIST FILTERED
 * by Bogdan Yurov <bogdan@yurov.me> aka nick4fake
 * version 0.4, 2012
 * */
void function ($, generalClass) {
	"use strict";

	var defSettings = {
		bools:        {
			btrue:  'да',
			bfalse: 'нет'
		},
		panel:        null,
		paging:       null,
		noresMessage: 'Результатов нет',
		colregex:     {
			exp:   /^(.*\s)?col-([a-z0-9_A-Z]+)(\s.*)?$/,
			index: 2
		},
		pagingParams: {
			num_edge_entries:    1,
			num_display_entries: 4,
			callback:            null,
			items_per_page:      1,
			current_page:        null
		}
	};

	var f = {
		bindFormUpdate: function (context, settings) {
			var form = $(context.form);
			var xhr;
			var page;
			if (settings.paging) {
				page = $('<input type="hidden" name="page" />').attr('value', 1);
				page.appendTo(form);
			}
			form.submit(function (e) {
				e.preventDefault();
				if (xhr) {
					xhr.abort();
				}
				context.updateState();
				var post = form.formSerialize();
				app.log('List request', form, post);
				xhr = $fn.query(form.attr('action'), function (data) {
					form.trigger('xhr', data);
					if (data.res) {
						context.updateState(data.res);
					} else if (!data.countTotal) {
						context.updateState(settings.noresMessage);
					} else {
						context.updateState(data.rows);
					}
					if (page) {
						if (data.countPage < page.attr('value')) {
							page.attr('value', Math.max(1, data.countPage));
							form.submit();
							return;
						}
						var params = $.extend({}, settings.pagingParams, {
							current_page: page.attr('value') - 1,
							callback:     function (inpage) {
								if (page.attr('value') == inpage + 1) {
									return;
								}
								page.attr('value', inpage + 1);
								form.submit();
							}
						});
						settings.paging.pagination(data.countPage, params);
					}
				}, post, {root: null});
			});
			return function () {
				form.submit();
			};
		},
		updateRows:     function (columns, target, rows, settings) {
			var column, tr, td, i, rowind, row, text, cont;
			for (rowind = 0; rowind < rows.length; rowind++) {
				row = rows[rowind];
				tr = document.createElement('tr');
				for (i = 0; i < columns.length; i++) {
					column = columns[i];
					td = document.createElement('td');
					tr.appendChild(td);
					td.className = 'col-' + column.name + ' type-' + column.type;
					text = row[column.name];

					cont = null;
					if (column.type == 'string' || column.type == 'html') {
						cont = document.createElement('div');
						cont.className = 'cont';
						td.appendChild(cont);
					}
					cont = (cont ? cont : td);

					if (column.setter) {
						text = column.setter.call(row, text, cont, td, tr);
						if (typeof text == 'string') {
							cont.innerHTML = text;
						}
					} else {
						switch (column.type) {
							case 'html':
								cont.innerHTML = text;
								continue;
							case 'int':
								text = Number(text);
								break;
							case 'bool':
								var val = !!Number(text);
								cont.className += ' bool-' + (val ? 'true' : 'false');
								text = settings.bools[val ? 'btrue' : 'bfalse'];
								break;
						}
						cont.textContent = text;
					}
				}
				target.appendChild(tr);
			}
		}
	};

	var classes = {
		Column: function (element, name, sorter) {
			this.element = $(element);
			this.name = name;
			this.title = this.element.text();
			this.sorter = sorter;
			this.isDesc = false;
			this.isSortable = this.element.hasClass('sortable');
			if (this.isSortable) {
				this.obj = $('<button></button>').addClass('flist-sorter');
				this.obj.click(function (that) {
					return function () {
						that.sorter.call(that);
					};
				}(this));
			} else {
				this.obj = $('<span></span>');
			}
			this.obj.text(this.title).appendTo(this.element.html(''));

			this.type = this.element.data('flist-type') ? this.element.data('flist-type') : 'string';
		},
		Plugin: function (targetTable, settings) {
			this.target = targetTable.addClass('list-filtered');
			this.form = settings.panel.addClass('list-filtered-panel');

			var tBody = this.target.find('tbody'),
				tHead = this.target.find('thead');

			// Столбцы
			this.columns = function (list, sorter) {
				var ret = [];
				list.each(function () {
					var name;
					if (name = settings.colregex.exp.exec($(this).attr('class'))) {
						ret.push(
							new classes.Column(this, name[settings.colregex.index], sorter)
						);
					}
				});
				return ret;
			}(this.target.find('thead th'), function (that) {
				return function () {
					that.order(this.name, !this.isDesc);
				};
			}(this));
			this.sorters = function (columns) {
				var ret = $();
				$.each(columns, function () {
					if (this.isSortable) {
						ret = ret.add(this.obj);
					}
				});
				return ret;
			}(this.columns);
			this.col = function (columns) {
				return function (name) {
					var ret;
					$.each(columns, function () {
						if (this.name == name) {
							ret = this;
							return false;
						}
					});
					return ret;
				};
			}(this.columns);

			// Фильтры
			this.filters = function (list, update) {
				var ret = $();
				var kdown = function (e) {
					if (e.keyCode == 13) {
						update.call(this, e);
					}
				};
				list.each(function () {
					switch ($(this).attr('type')) {
						case 'checkbox':
							$(this).click(update);
							return;
						case 'text':
							$(this).keydown(kdown);
							return;
					}
					ret.add($(this).change(update));
				});
				return ret;
			}(settings.panel.find('input[name]:not([type="hidden"]), select[name]'), function () {
				$(this).closest('form').submit();
			});

			this.update = f.bindFormUpdate(this, settings);

			// Сортировка
			this.order = function (form, sorters, colgetter) {
				var el = $('<input type="hidden" name="order" />').appendTo(form);
				return function (col, isDesc) {
					this.updateState();
					var dir = isDesc ? 'desc' : 'asc';
					el.attr('value', col + ' ' + dir);
					sorters.removeClass('order-desc').removeClass('order-asc');
					var col = colgetter(col);
					col.isDesc = isDesc;
					col.obj.addClass('order-' + dir);
					form.submit();
					return this;
				}
			}(this.form, this.sorters, this.col);

			this.updateState = function (data) {
				tBody.html('');
				if (typeof data == 'string') {
					$('<tr>').append(
						$('<td>').html(data).addClass('message')
							.attr('colspan', this.columns.length)
					).appendTo(tBody);
				} else if (typeof data == 'undefined') {
					$('<tr>').append(
						$('<td>').html('<img src="' + CONST.webapp_res + '/flist/preloader.gif" alt="preloader" />').addClass('preloader')
							.attr('colspan', this.columns.length)
					).appendTo(tBody);
				} else if (typeof data == 'object' && typeof data.length != 'undefined') {
					if (data.length) {
						f.updateRows(this.columns, tBody.get(0), data);
					} else {
						this.updateState('Ничего не найдено');
					}
				} else {
					this.updateState('Ошибочный статус');
				}
			};

		}
	};

	// Сам плагин
	$.fn[generalClass] = function (options) {
		return this.each(function () {
			var element = $(this);
			if (element.data(generalClass)) return;

			var settings = $.extend({}, defSettings, options || {});
			if (!settings.panel) {
				throw 'Панель не указана';
			}
			element.data(generalClass, new classes.Plugin(element, settings));
		});
	};
}(jQuery, 'flist');