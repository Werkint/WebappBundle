app.fn.postiframe = (function (href, optionsin, data) {
	var options = {
		fitToView:false,
		autoSize: true,
		href:     app.queryPrefix + '/' + href
	};
	$.extend(options, optionsin);
	$.extend(options, {
		type:     'iframe',
		afterLoad:(function () {
			var frame = $(this.content[0]);
			var form = $('<form action="' + options.href + '" method="POST"></form>');
			for (var name in data) {
				form.append(
					$('<input type="hidden" name value />').
						attr('name', name).
						attr('value', (typeof data[name] == 'object') ? $.toJSON(data[name]) : data[name])
				);
			}

			frame.contents().find('body').html('').append(form);
			form.submit();
		})
	});
	$.fancybox(options);
});