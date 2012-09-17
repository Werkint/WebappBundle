/* Functions for the mylink plugin popup */

tinyMCEPopup.requireLangPack();

var templates = {
	"window.open":"window.open('${url}','${target}','${options}')"
};

function preinit() {
	var url;

	if (url = tinyMCEPopup.getParam("external_link_list_url"))
		document.write('<script language="javascript" type="text/javascript" src="' + tinyMCEPopup.editor.documentBaseURI.toAbsolute(url) + '"></script>');
}

function changeClass() {
	var f = document.forms[0];

	f.classes.value = getSelectValue(f, 'classlist');
}

function init() {
	tinyMCEPopup.resizeToInnerSize();

	var formObj = document.forms[0];
	var inst = tinyMCEPopup.editor;
	var elm = inst.selection.getNode();
	var action = "insert";
	var html;

	document.getElementById('hrefbrowsercontainer').innerHTML = getBrowserHTML('hrefbrowser', 'href', 'file', 'mylink');

	// Link list
	html = getLinkListHTML('linklisthref', 'href');
	if (html == "")
		document.getElementById("linklisthrefrow").style.display = 'none';
	else
		document.getElementById("linklisthrefcontainer").innerHTML = html;

	// Anchor list
	html = getAnchorListHTML('anchorlist', 'href');
	if (html == "")
		document.getElementById("anchorlistrow").style.display = 'none';
	else
		document.getElementById("anchorlistcontainer").innerHTML = html;

	// Resize some elements
	if (isVisible('hrefbrowser'))
		document.getElementById('href').style.width = '200px';

	elm = inst.dom.getParent(elm, "A");
	if (elm == null) {
		var prospect = inst.dom.create("p", null, inst.selection.getContent());
		if (prospect.childNodes.length === 1) {
			elm = prospect.firstChild;
		}
	}

	if (elm != null && elm.nodeName == "A")
		action = "update";

	formObj.insert.value = tinyMCEPopup.getLang(action, 'Insert', true);

	if (action == "update") {
		var href = inst.dom.getAttrib(elm, 'href');
		var onclick = inst.dom.getAttrib(elm, 'onclick');

		// Setup form data
		setFormValue('href', href);
		setFormValue('title', inst.dom.getAttrib(elm, 'title'));
		/*setFormValue('id', inst.dom.getAttrib(elm, 'id'));*/

		if (href.charAt(0) == '#')
			selectByValue(formObj, 'anchorlist', href);

		addClassesToList('classlist', 'mylink_styles');
	} else
		addClassesToList('classlist', 'mylink_styles');
}

function checkPrefix(n) {
	if (n.value && Validator.isEmail(n) && !/^\s*mailto:/i.test(n.value) && confirm(tinyMCEPopup.getLang('mylink_dlg.is_email')))
		n.value = 'mailto:' + n.value;

	if (/^\s*www\./i.test(n.value) && confirm(tinyMCEPopup.getLang('mylink_dlg.is_external')))
		n.value = 'http://' + n.value;
}

function setFormValue(name, value) {
	document.forms[0].elements[name].value = value;
}

function setAttrib(elm, attrib, value) {
	var formObj = document.forms[0];
	var valueElm = formObj.elements[attrib.toLowerCase()];
	var dom = tinyMCEPopup.editor.dom;

	if (typeof(value) == "undefined" || value == null) {
		value = "";

		if (valueElm)
			value = valueElm.value;
	}

	// Clean up the style
	if (attrib == 'style')
		value = dom.serializeStyle(dom.parseStyle(value), 'a');

	dom.setAttrib(elm, attrib, value);
}

function getAnchorListHTML(id, target) {
	var ed = tinyMCEPopup.editor, nodes = ed.dom.select('a'), name, i, len, html = "";

	for (i = 0, len = nodes.length; i < len; i++) {
		if ((name = ed.dom.getAttrib(nodes[i], "name")) != "")
			html += '<option value="#' + name + '">' + name + '</option>';
	}

	if (html == "")
		return "";

	html = '<select id="' + id + '" name="' + id + '" class="mceAnchorList"'
		+ ' onchange="this.form.' + target + '.value=this.options[this.selectedIndex].value"'
		+ '>'
		+ '<option value="">---</option>'
		+ html
		+ '</select>';

	return html;
}

function insertAction() {
	var inst = tinyMCEPopup.editor;
	var elm, elementArray, i;

	elm = inst.selection.getNode();
	checkPrefix(document.forms[0].href);

	elm = inst.dom.getParent(elm, "A");

	// Remove element if there is no href
	if (!document.forms[0].href.value) {
		i = inst.selection.getBookmark();
		inst.dom.remove(elm, 1);
		inst.selection.moveToBookmark(i);
		tinyMCEPopup.execCommand("mceEndUndoLevel");
		tinyMCEPopup.close();
		return;
	}

	// Create new anchor elements
	if (elm == null) {
		inst.getDoc().execCommand("unlink", false, null);
		tinyMCEPopup.execCommand("mceInsertLink", false, "#mce_temp_url#", {skip_undo:1});

		elementArray = tinymce.grep(inst.dom.select("a"), function (n) {
			return inst.dom.getAttrib(n, 'href') == '#mce_temp_url#';
		});
		for (i = 0; i < elementArray.length; i++)
			setAllAttribs(elm = elementArray[i]);
	} else
		setAllAttribs(elm);

	// Don't move caret if selection was image
	if (elm.childNodes.length != 1 || elm.firstChild.nodeName != 'IMG') {
		inst.focus();
		inst.selection.select(elm);
		inst.selection.collapse(0);
		tinyMCEPopup.storeSelection();
	}

	tinyMCEPopup.execCommand("mceEndUndoLevel");
	tinyMCEPopup.close();
}

function setAllAttribs(elm) {
	var formObj = document.forms[0];
	var href = formObj.href.value.replace(/ /g, '%20');

	setAttrib(elm, 'href', href);
	setAttrib(elm, 'title');
	setAttrib(elm, 'id');

	// Refresh in old MSIE
	if (tinyMCE.isMSIE5)
		elm.outerHTML = elm.outerHTML;
}

function getSelectValue(form_obj, field_name) {
	var elm = form_obj.elements[field_name];

	if (!elm || elm.options == null || elm.selectedIndex == -1)
		return "";

	return elm.options[elm.selectedIndex].value;
}

function getLinkListHTML(elm_id, target_form_element, onchange_func) {
	if (typeof(tinyMCELinkList) == "undefined" || tinyMCELinkList.length == 0)
		return "";

	var html = "";

	html += '<select id="' + elm_id + '" name="' + elm_id + '"';
	html += ' class="mceLinkList" onchange="this.form.' + target_form_element + '.value=';
	html += 'this.options[this.selectedIndex].value;';

	if (typeof(onchange_func) != "undefined")
		html += onchange_func + '(\'' + target_form_element + '\',this.options[this.selectedIndex].text,this.options[this.selectedIndex].value);';

	html += '"><option value="">---</option>';

	for (var i = 0; i < tinyMCELinkList.length; i++)
		html += '<option value="' + tinyMCELinkList[i][1] + '">' + tinyMCELinkList[i][0] + '</option>';

	html += '</select>';

	return html;

	// tinyMCE.debug('-- image list start --', html, '-- image list end --');
}

// While loading
preinit();
tinyMCEPopup.onInit.add(init);
