(function () {
	var active = false;
	var element = false;
	tinymce.create('tinymce.plugins.video', {
		init:   function (ed, url) {
			ed.addCommand('mceVideo', function () {
				if (active) {
					element.parentNode.removeChild(element);
					element = false;
					active = false;
					ed.nodeChanged();
				} else {
					var cur = element;
					while (cur.parentNode.tagName != 'BODY') {
						cur = cur.parentNode;
					}
					var el = $('<div class="video">youtube vide0c0de</div>');
					$(el).insertAfter(cur);
				}
			});

			ed.addButton('video', {
				title:'Видео',
				cmd:  'mceVideo',
				image:CONST.webapp_res + '/tinymce/tiny_mce/plugins/video/video.png'
			});

			ed.onNodeChange.add(function (ed, cm, n) {
				active = (n.nodeName == 'DIV' && n.className == 'video');
				element = n;
				cm.setActive('video', active);
			});
		},
		getInfo:function () {
			return {
				longname: 'Видео',
				author:   'Bogdan Yurov',
				authorurl:'http://yurov.me',
				infourl:  'http://yurov.me',
				version:  '0.1'
			};
		}
	});
	tinymce.PluginManager.add('video', tinymce.plugins.video);
})();