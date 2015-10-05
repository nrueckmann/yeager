/* Functions for the advlink plugin popup */

var yeagerCE_templates = {
	"window.open" : "window.open('${url}','${target}','${options}')"
};


function yeagerCE_checkPrefix(n) {
	if (n.value && Validator.isEmail(n) && !/^\s*mailto:/i.test(n.value))
		n.value = 'mailto:' + n.value;

	if (/^\s*www./i.test(n.value))
		n.value = 'http://' + n.value;
}

function yeagerCE_setFormValue(name, value, formObj) {
	formObj.elements[name].value = value;
}

function yeagerCE_parseWindowOpen(onclick, formObj) {

	var onClickData = yeagerCE_parseLink(onclick);
	
	if (onClickData != null) {
		formObj.ispopup.value = 1;
		winPrefix = formObj.up('.ywindow').id;
		$K.yg_checkboxClick($(winPrefix+'_chk_ispopup'));
		$K.yg_checkboxSelect($(winPrefix+'_chk_ispopup'));
		$(winPrefix+'_javascriptdetails').toggle();	
		$K.windows[winPrefix].refresh();
		
		var onClickWindowOptions = yeagerCE_parseOptions(onClickData['options']);
		var url = onClickData['url'];

		formObj.popupname.value = onClickData['target'];
		formObj.href.value = url;
		formObj.popupwidth.value = yeagerCE_getOption(onClickWindowOptions, 'width');
		formObj.popupheight.value = yeagerCE_getOption(onClickWindowOptions, 'height');

		if (yeagerCE_getOption(onClickWindowOptions, 'location') == "yes") {
			formObj.popuplocation.value = 1;
			$K.yg_checkboxClick($(winPrefix+'_chk_popuplocation'));
			$K.yg_checkboxSelect($(winPrefix+'_chk_popuplocation'));
		} else {
			formObj.popuplocation.value = 0;
		}
		if (yeagerCE_getOption(onClickWindowOptions, 'scrollbars') == "yes") {
			formObj.popupscrollbars.value = 1;
			$K.yg_checkboxClick($(winPrefix+'_chk_popupscrollbars'));
			$K.yg_checkboxSelect($(winPrefix+'_chk_popupscrollbars'));
		} else {
			formObj.popupscrollbars.value = 0;
		}
		if (yeagerCE_getOption(onClickWindowOptions, 'menubar') == "yes") {
			formObj.popupmenubar.value = 1;
			$K.yg_checkboxClick($(winPrefix+'_chk_popupmenubar'));
			$K.yg_checkboxSelect($(winPrefix+'_chk_popupmenubar'));
		} else {
			formObj.popupmenubar.value = 0;
		}
		if (yeagerCE_getOption(onClickWindowOptions, 'resizable') == "yes") {
			formObj.popupresizable.value = 1;
			$K.yg_checkboxClick($(winPrefix+'_chk_popupresizable'));
			$K.yg_checkboxSelect($(winPrefix+'_chk_popupresizable'));
		} else {
			formObj.popupresizable.value = 0
		}
		if (yeagerCE_getOption(onClickWindowOptions, 'toolbar') == "yes") {
			formObj.popuptoolbar.value = 1;
			$K.yg_checkboxClick($(winPrefix+'_chk_popuptoolbar'));
			$K.yg_checkboxSelect($(winPrefix+'_chk_popuptoolbar'));
		} else {
			formObj.popuptoolbar.value = 0;
		}
		if (yeagerCE_getOption(onClickWindowOptions, 'status') == "yes") {
			formObj.popupstatus.value = 1;
			$K.yg_checkboxClick($(winPrefix+'_chk_popupstatus'));
			$K.yg_checkboxSelect($(winPrefix+'_chk_popupstatus'));
		} else {
			formObj.popupstatus.value = 0;
		}
		yeagerCE_buildOnClick(formObj);
	}
}

function yeagerCE_parseFunction(onclick, formObj) {
	var onClickData = yeagerCE_parseLink(onclick);
}

function yeagerCE_getOption(opts, name) {
	return typeof(opts[name]) == "undefined" ? "" : opts[name];
}

function yeagerCE_parseLink(link) {
	
	link = link.replace(new RegExp('&#39;', 'g'), "'");

	var fnName = link.replace(new RegExp("\\s*([A-Za-z0-9\.]*)\\s*\\(.*", "gi"), "$1");

	// Is function yeagerCE_name a template function
	var template = yeagerCE_templates[fnName];
	if (template) {
		// Build regexp
		var variableNames = template.match(new RegExp("'?\\$\\{[A-Za-z0-9\.]*\\}'?", "gi"));
		var regExp = "\\s*[A-Za-z0-9\.]*\\s*\\(";
		var replaceStr = "";
		for (var i=0; i<variableNames.length; i++) {
			// Is string value
			if (variableNames[i].indexOf("'${") != -1)
				regExp += "'(.*)'";
			else // Number value
				regExp += "([0-9]*)";

			replaceStr += "$" + (i+1);

			// Cleanup variable name
			variableNames[i] = variableNames[i].replace(new RegExp("[^A-Za-z0-9]", "gi"), "");

			if (i != variableNames.length-1) {
				regExp += "\\s*,\\s*";
				replaceStr += "<delim>";
			} else
				regExp += ".*";
		}

		regExp += "\\);?";

		// Build variable array
		var variables = [];
		variables["_function"] = fnName;
		var variableValues = link.replace(new RegExp(regExp, "gi"), replaceStr).split('<delim>');
		for (var i=0; i<variableNames.length; i++) {
			variables[variableNames[i]] = variableValues[i];
		}
		return variables;
	}

	return null;
}

function yeagerCE_parseOptions(opts) {
	if (opts == null || opts == "")
		return [];

	// Cleanup the options
	opts = opts.toLowerCase();
	opts = opts.replace(/;/g, ",");
	opts = opts.replace(/[^0-9a-z=,]/g, "");

	var optionChunks = opts.split(',');
	var options = [];

	for (var i=0; i<optionChunks.length; i++) {
		var parts = optionChunks[i].split('=');

		if (parts.length == 2)
			options[parts[0]] = parts[1];
	}

	return options;
}

function yeagerCE_buildOnClick(formObj) {

	if (formObj.ispopup.value != '1') {
		formObj.onclick.value = '';
		return;
	}
	
	var onclick = "window.open('";
	var url = formObj.href.value;

	onclick += url + "','";
	onclick += formObj.popupname.value + "','";

	if (formObj.popuplocation.value == '1')
		onclick += "location=yes,";

	if (formObj.popupscrollbars.value == '1')
		onclick += "scrollbars=yes,";

	if (formObj.popupmenubar.value == '1')
		onclick += "menubar=yes,";

	if (formObj.popupresizable.value == '1')
		onclick += "resizable=yes,";

	if (formObj.popuptoolbar.value == '1')
		onclick += "toolbar=yes,";

	if (formObj.popupstatus.value == '1')
		onclick += "status=yes,";

	/*
	if (formObj.popupdependent.value == '1')
		onclick += "dependent=yes,";
	*/

	if (formObj.popupwidth.value != "")
		onclick += "width=" + formObj.popupwidth.value + ",";

	if (formObj.popupheight.value != "")
		onclick += "height=" + formObj.popupheight.value + ",";

	/*
	if (formObj.popupleft.value != "") {
		if (formObj.popupleft.value != "c")
			onclick += "left=" + formObj.popupleft.value + ",";
		else
			onclick += "left='+(screen.availWidth/2-" + (formObj.popupwidth.value/2) + ")+',";
	}

	if (formObj.popuptop.value != "") {
		if (formObj.popuptop.value != "c")
			onclick += "top=" + formObj.popuptop.value + ",";
		else
			onclick += "top='+(screen.availHeight/2-" + (formObj.popupheight.value/2) + ")+',";
	}
	*/

	if (onclick.charAt(onclick.length-1) == ',')
		onclick = onclick.substring(0, onclick.length-1);

	onclick += "');";

	/*
	if (formObj.popupreturn.checked)
		onclick += "return false;";
	*/

	// tinyMCE.debug(onclick);
	
	formObj.onclick.value = onclick;
	
	if (formObj.href.value == "")
		formObj.href.value = url;
}

function yeagerCE_setAttrib(elm, attrib, value, formObj, editor) {

	var dom = tinyMCE.editors[editor].dom;

	if (typeof(value) == 'undefined' || value == null) {
		value = "";

		if (formObj[attrib] && formObj[attrib].value != '') value = formObj[attrib].value;
	}

	// Clean up the style
	if (attrib == 'style')
		value = dom.serializeStyle(dom.parseStyle(value));

	dom.setAttrib(elm, attrib, value);
}


function yeagerCE_setAllAttribs(elm, formObj, editor) {
	
	if (!editor) editor = tinyMCE.activeEditor.editorId;

	var href = formObj.href.value;

	//var target = formObj.dd_targets.value;
	var winPrefix = $(formObj).up('.ywindow').id; 
	if (formObj[winPrefix+'_dd_targets']) { var target = formObj[winPrefix+'_dd_targets'].value; } else { var target = "" }
	if ((href.toLowerCase().indexOf('http://') > -1) || (href.toLowerCase().indexOf('https://') > -1)) { var rel = 'nofollow'; } else { var rel = '' }

	yeagerCE_setAttrib(elm, 'href', href, formObj, editor);
	yeagerCE_setAttrib(elm, 'rel', rel, formObj, editor);
	yeagerCE_setAttrib(elm, 'title', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'target', target == '_self' ? '' : target, formObj, editor);
	yeagerCE_setAttrib(elm, 'id', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'style', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'class', yeagerCE_getSelectValue(formObj, 'classlist'), formObj, editor);
	yeagerCE_setAttrib(elm, 'rev', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'charset', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'hreflang', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'dir', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'lang', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'tabindex', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'accesskey', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'type', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'onfocus', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'onblur', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'onclick', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'ondblclick', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'onmousedown', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'onmouseup', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'onmouseover', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'onmousemove', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'onmouseout', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'onkeypress', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'onkeydown', null, formObj, editor);
	yeagerCE_setAttrib(elm, 'onkeyup', null, formObj, editor);

	// Refresh in old MSIE
	if (tinyMCE.isMSIE5)
		elm.outerHTML = elm.outerHTML;
}

function yeagerCE_getSelectValue(form_obj, field_name) {
	var elm = form_obj.elements[field_name];

	if (!elm || elm.options == null || elm.selectedIndex == -1)
		return "";

	return elm.options[elm.selectedIndex].value;
}



/**
 * $Id: validate.js 758 2008-03-30 13:53:29Z spocke $
 *
 * Various form validation methods.
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

/**
	// String validation:

	if (!Validator.isEmail('myemail'))
		alert('Invalid email.');

	// Form validation:

	var f = document.forms['myform'];

	if (!Validator.isEmail(f.myemail))
		alert('Invalid email.');
*/

var Validator = {
	isEmail : function(s) {
		return this.test(s, '^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+@[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$');
	},

	isAbsUrl : function(s) {
		return this.test(s, '^(news|telnet|nttp|file|http|ftp|https)://[-A-Za-z0-9\\.]+\\/?.*$');
	},

	isSize : function(s) {
		return this.test(s, '^[0-9]+(%|in|cm|mm|em|ex|pt|pc|px)?$');
	},

	isId : function(s) {
		return this.test(s, '^[A-Za-z_]([A-Za-z0-9_])*$');
	},

	isEmpty : function(s) {
		var nl, i;

		if (s.nodeName == 'SELECT' && s.selectedIndex < 1)
			return true;

		if (s.type == 'checkbox' && !s.checked)
			return true;

		if (s.type == 'radio') {
			for (i=0, nl = s.form.elements; i<nl.length; i++) {
				if (nl[i].type == "radio" && nl[i].name == s.name && nl[i].checked)
					return false;
			}

			return true;
		}

		return new RegExp('^\\s*$').test(s.nodeType == 1 ? s.value : s);
	},

	isNumber : function(s, d) {
		return !isNaN(s.nodeType == 1 ? s.value : s) && (!d || !this.test(s, '^-?[0-9]*\\.[0-9]*$'));
	},

	test : function(s, p) {
		s = s.nodeType == 1 ? s.value : s;

		return s == '' || new RegExp(p).test(s);
	}
};

var AutoValidator = {
	settings : {
		id_cls : 'id',
		int_cls : 'int',
		url_cls : 'url',
		number_cls : 'number',
		email_cls : 'email',
		size_cls : 'size',
		required_cls : 'required',
		invalid_cls : 'invalid',
		min_cls : 'min',
		max_cls : 'max'
	},

	init : function(s) {
		var n;

		for (n in s)
			this.settings[n] = s[n];
	},

	validate : function(f) {
		var i, nl, s = this.settings, c = 0;

		nl = this.tags(f, 'label');
		for (i=0; i<nl.length; i++)
			this.removeClass(nl[i], s.invalid_cls);

		c += this.validateElms(f, 'input');
		c += this.validateElms(f, 'select');
		c += this.validateElms(f, 'textarea');

		return c == 3;
	},

	invalidate : function(n) {
		this.mark(n.form, n);
	},

	reset : function(e) {
		var t = ['label', 'input', 'select', 'textarea'];
		var i, j, nl, s = this.settings;

		if (e == null)
			return;

		for (i=0; i<t.length; i++) {
			nl = this.tags(e.form ? e.form : e, t[i]);
			for (j=0; j<nl.length; j++)
				this.removeClass(nl[j], s.invalid_cls);
		}
	},

	validateElms : function(f, e) {
		var nl, i, n, s = this.settings, st = true, va = Validator, v;

		nl = this.tags(f, e);
		for (i=0; i<nl.length; i++) {
			n = nl[i];

			this.removeClass(n, s.invalid_cls);

			if (this.hasClass(n, s.required_cls) && va.isEmpty(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.number_cls) && !va.isNumber(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.int_cls) && !va.isNumber(n, true))
				st = this.mark(f, n);

			if (this.hasClass(n, s.url_cls) && !va.isAbsUrl(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.email_cls) && !va.isEmail(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.size_cls) && !va.isSize(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.id_cls) && !va.isId(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.min_cls, true)) {
				v = this.getNum(n, s.min_cls);

				if (isNaN(v) || parseInt(n.value) < parseInt(v))
					st = this.mark(f, n);
			}

			if (this.hasClass(n, s.max_cls, true)) {
				v = this.getNum(n, s.max_cls);

				if (isNaN(v) || parseInt(n.value) > parseInt(v))
					st = this.mark(f, n);
			}
		}

		return st;
	},

	hasClass : function(n, c, d) {
		return new RegExp('\\b' + c + (d ? '[0-9]+' : '') + '\\b', 'g').test(n.className);
	},

	getNum : function(n, c) {
		c = n.className.match(new RegExp('\\b' + c + '([0-9]+)\\b', 'g'))[0];
		c = c.replace(/[^0-9]/g, '');

		return c;
	},

	addClass : function(n, c, b) {
		var o = this.removeClass(n, c);
		n.className = b ? c + (o != '' ? (' ' + o) : '') : (o != '' ? (o + ' ') : '') + c;
	},

	removeClass : function(n, c) {
		c = n.className.replace(new RegExp("(^|\\s+)" + c + "(\\s+|$)"), ' ');
		return n.className = c != ' ' ? c : '';
	},

	tags : function(f, s) {
		return f.getElementsByTagName(s);
	},

	mark : function(f, n) {
		var s = this.settings;

		this.addClass(n, s.invalid_cls);
		this.markLabels(f, n, s.invalid_cls);

		return false;
	},

	markLabels : function(f, n, ic) {
		var nl, i;

		nl = this.tags(f, "label");
		for (i=0; i<nl.length; i++) {
			if (nl[i].getAttribute("for") == n.id || nl[i].htmlFor == n.id)
				this.addClass(nl[i], ic);
		}

		return null;
	}
};

function yeagerCE_Undo( which ) {
	which = $(which);
	if (!which.hasClassName('disabled')) {
		tinyMCE.execCommand('Undo');
	}
}

function yeagerCE_Redo( which ) {
	which = $(which);
	if (!which.hasClassName('disabled')) {
		tinyMCE.execCommand('Redo');
	}
}

function yeagerCE_searchNext( a, win_id ) {
	
	var ed = tinyMCE.activeEditor, se = ed.selection, r = se.getRng(), f, m = $K.windows['wid_'+win_id].tabs.elements[$K.windows['wid_'+win_id].tabs.selected]["NAME"], s, b, fl = 0, w = ed.getWin(), fo = 0;

	// Get input
	f = document.forms['wid_'+win_id+'_linkForm_'+m];
	s = f[m + '_panel_searchstring'].value;
	
	//b = f[m + '_panel_backwardsu'].checked;
	if (f[m + '_panel_backwards'].value == m+'_panel_backwardsu') {
		b = true;
	} else {
		b = false;
	}
	//ca = f[m + '_panel_casesensitivebox'].checked;
	if (f[m + '_panel_casesensitivebox'].value == '1') {
		ca = true;
	} else {
		ca = false;
	}

	if (f['CONTENTEDITOR_REPLACE_panel_replacestring']) rs = f['CONTENTEDITOR_REPLACE_panel_replacestring'].value;

	if (s == '') {
		return;
	}

	var fix = function() {
		// Correct Firefox graphics glitches
		r = se.getRng().cloneRange();
		ed.getDoc().execCommand('SelectAll', false, null);
		se.setRng(r);
	};

	var replace = function() {
		if (tinymce.isIE)
			ed.selection.getRng().duplicate().pasteHTML(rs); // Needs to be duplicated due to selection bug in IE
		else
			ed.getDoc().execCommand('InsertHTML', false, rs);
	};

	// IE flags
	if (ca)
		fl = fl | 4;

	switch (a) {
		case 'all':
			// Move caret to beginning of text
			ed.execCommand('SelectAll');
			ed.selection.collapse(true);

			if (tinymce.isIE) {
				while (r.findText(s, b ? -1 : 1, fl)) {
					r.scrollIntoView();
					r.select();
					replace();
					fo = 1;
				}

				yeagerCE_saveSelection();
				
			} else {
				while (w.find(s, ca, b, false, false, false, false)) {
					replace();
					fo = 1;
				}
			}

			if (fo) {
				$K.yg_promptbox('', $K.TXT('CE_SEARCHREPLACE_ALL_REPLACED'), 'alert');
			} else {
				$K.yg_promptbox('', $K.TXT('CE_SEARCHREPLACE_NOT_FOUND'), 'alert');
			}

			return;

		case 'current':
			if (!ed.selection.isCollapsed())
				replace();

			break;
	}

	se.collapse(b);
	r = se.getRng();

	// Whats the point
	if (!s)
		return;

	if (tinymce.isIE) {
		if (r.findText(s, b ? -1 : 1, fl)) {
			r.scrollIntoView();
			r.select();
		} else {
			$K.yg_promptbox('', $K.TXT('CE_SEARCHREPLACE_NOT_FOUND'), 'alert');
		}

		yeagerCE_saveSelection();
	} else {
		if (!w.find(s, ca, b, false, false, false, false)) {
			$K.yg_promptbox('', $K.TXT('CE_SEARCHREPLACE_NOT_FOUND'), 'alert');
		} else {
			fix();
		}
	}

}

function yeagerCE_makeAttrib(attrib, value, formObj) {
	var valueElm = formObj.elements[attrib];

	if (typeof(value) == "undefined" || value == null) {
		value = "";

		if (valueElm)
			value = valueElm.value;
	}

	if (value == "")
		return "";

	// XML encode it
	value = value.replace(/&/g, '&amp;');
	value = value.replace(/\"/g, '&quot;');
	value = value.replace(/</g, '&lt;');
	value = value.replace(/>/g, '&gt;');

	return ' ' + attrib + '="' + value + '"';
}

function yeagerCE_trimSize(size) {
	return size.replace(/([0-9\.]+)px|(%|in|cm|mm|em|ex|pt|pc)/, '$1$2');
}

function yeagerCE_getStyle(elm, attrib, style) {
	var val = tinyMCE.activeEditor.dom.getAttrib(elm, attrib);

	if (val != '')
		return '' + val;

	if (typeof(style) == 'undefined')
		style = attrib;

	return tinyMCE.activeEditor.dom.getStyle(elm, style);
}

function yeagerCE_convertRGBToHex(col) {
	var re = new RegExp("rgb\\s*\\(\\s*([0-9]+).*,\\s*([0-9]+).*,\\s*([0-9]+).*\\)", "gi");

	var rgb = col.replace(re, "$1,$2,$3").split(',');
	if (rgb.length == 3) {
		r = parseInt(rgb[0]).toString(16);
		g = parseInt(rgb[1]).toString(16);
		b = parseInt(rgb[2]).toString(16);

		r = r.length == 1 ? '0' + r : r;
		g = g.length == 1 ? '0' + g : g;
		b = b.length == 1 ? '0' + b : b;

		return "#" + r + g + b;
	}

	return col;
}

function yeagerCE_convertHexToRGB(col) {
	if (col.indexOf('#') != -1) {
		col = col.replace(new RegExp('[^0-9A-F]', 'gi'), '');

		r = parseInt(col.substring(0, 2), 16);
		g = parseInt(col.substring(2, 4), 16);
		b = parseInt(col.substring(4, 6), 16);

		return "rgb(" + r + "," + g + "," + b + ")";
	}

	return col;
}

function yeagerCE_getCSSSize(size) {
	size = yeagerCE_trimSize(size);

	if (size == "")
		return "";

	// Add px
	if (/^[0-9]+$/.test(size))
		size += 'px';

	return size;
}


function yeagerCE_getAttrib(e, at) {
	var ed = tinyMCE.activeEditor, dom = ed.dom, v, v2;

	if (ed.settings.inline_styles) {
		switch (at) {
			case 'align':
				if (v = dom.getStyle(e, 'float'))
					return v;

				if (v = dom.getStyle(e, 'vertical-align'))
					return v;

				break;

			case 'hspace':
				v = dom.getStyle(e, 'margin-left')
				v2 = dom.getStyle(e, 'margin-right');
				if (v && v == v2)
					return parseInt(v.replace(/[^0-9]/g, ''));

				break;

			case 'vspace':
				v = dom.getStyle(e, 'margin-top')
				v2 = dom.getStyle(e, 'margin-bottom');
				if (v && v == v2)
					return parseInt(v.replace(/[^0-9]/g, ''));

				break;

			case 'border':
				v = 0;

				tinymce.each(['top', 'right', 'bottom', 'left'], function(sv) {
					sv = dom.getStyle(e, 'border-' + sv + '-width');

					// False or not the same as prev
					if (!sv || (sv != v && v !== 0)) {
						v = 0;
						return false;
					}

					if (sv)
						v = sv;
				});

				if (v)
					return parseInt(v.replace(/[^0-9]/g, ''));

				break;
		}
	}

	if (v = dom.getAttrib(e, at))
		return v;

	return '';
}

function yeagerCE_updateStyle(styleVal, form) {
	var dom = tinyMCE.activeEditor.dom, st, v, f = form;

	if (tinyMCE.activeEditor.settings.inline_styles) {
		st = tinyMCE.activeEditor.dom.parseStyle(styleVal);

		// Handle align
		v = f.dd_aligns.value;
		if (v) {
			if (v == 'left' || v == 'right') {
				st['float'] = v;
				delete st['vertical-align'];
			} else {
				st['vertical-align'] = v;
				delete st['float'];
			}
		} else {
			delete st['float'];
			delete st['vertical-align'];
		}

		// Handle hspace
		v = f.hspace.value;
		if (v) {
			delete st['margin'];
			st['margin-left'] = v + 'px';
			st['margin-right'] = v + 'px';
		} else {
			delete st['margin-left'];
			delete st['margin-right'];
		}

		// Handle vspace
		v = f.vspace.value;
		if (v) {
			delete st['margin'];
			st['margin-top'] = v + 'px';
			st['margin-bottom'] = v + 'px';
		} else {
			delete st['margin-top'];
			delete st['margin-bottom'];
		}

		// Merge
		st = tinyMCE.activeEditor.dom.parseStyle(dom.serializeStyle(st));
		return dom.serializeStyle(st);
	}
}

function yeagerCE_restoreSelection() {
	var inst = tinyMCE.selectedInstance;

	inst.getWin().focus();

	if (inst.selectionBookmark) {
		inst.selection.moveToBookmark(inst.selectionBookmark);
	}
}

function yeagerCE_saveSelection() {
	var inst = tinyMCE.selectedInstance;

	inst.selectionBookmark = inst.selection.getBookmark(true);
}

function yeagerCEnodeChange(inst_id, n) {
	var inst = tinyMCE.activeEditor,
		dom = inst.dom;

	if (c = inst.controlManager.controls[inst_id+"_formatselect"]) {
		p = dom.getParent(n, dom.isBlock);

		if (p) {
			c.select(p.nodeName.toLowerCase());
		} else {
			c.select('');
		}
	}

	if (d = inst.controlManager.controls[inst_id+"_styleselect"]) {
		p = dom.getParent(n);
		if (p) {
			clsn = p.className;
			if (d.items && (d.items.length > 0)) {
				for (i = 0; i < d.items.length; i++) {
					if (d.items[i].title == clsn) {
						window.setTimeout(function () { d.selectByIndex(i); },70);
						return;
					}
				}
			}
		} else {
			d.select('');
		}
	}

}