(function () {
	tinymce.create('tinymce.plugins.ImagesPlugin', {
		init:   function (ed, url, a, b) {
			ed.addCommand('mceImage', function () {
				ed.windowManager.open({
					file:     '/admin/images',
					width:    532 + parseInt(ed.getLang('images.delta_width', 0)),
					height:   430 + parseInt(ed.getLang('images.delta_height', 0)),
					inline:   true,
					popup_css:false
				}, {
					plugin_url:url
				});
			});

			var button = ed.addButton('images', {
				title:'Изображение',
				cmd:  'mceImage',
				image:'/res/img/icons/image.png'
			});

			var first = true;

			ed.onNodeChange.add(function (ed, cm, n) {
				if (first) {
					first = false;
					return;
				}
				cm.setActive('images', n.nodeName == 'IMG' && !$(n).hasClass('mcePageBreak'));
			});
		},
		getInfo:function () {
			return {
				longname: 'Изображение',
				author:   'Bogdan Yurov',
				authorurl:'http://yurov.me',
				infourl:  'http://yurov.me',
				version:  '0.1'
			};
		}
	});
	tinymce.PluginManager.add('images', tinymce.plugins.ImagesPlugin);
})();