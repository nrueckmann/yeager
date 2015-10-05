/**
 * Opens the Linkchooser for the control
 * @param { Element } [which] Element to edit
 * @param { String } [objectid] ID of the formfield to edit
 */
$K.yg_openLinkChooserWindow = function( which, objectid, formfield ) {

	if (which.up('.maskedit')) which = $(which.up('.maskedit'));
	site = $K.windows[which.up('.ywindow').id].yg_id.split('-')[1];
	if (site != parseInt(site)) site = "";

	var src_data = {
		param01: which.readAttribute('param01'),	// HREF
		param02: which.readAttribute('param02'),	// TARGET
		param03: which.readAttribute('param03'),	// TEXT
		param04: which.readAttribute('param04')		// ONCLICK
	};

	if ( !src_data.param01 &&
		 which.up('.selectionmarker') &&
		 which.up('.selectionmarker').next('input') ) {
		src_data.param01 = which.up('.selectionmarker').next('input').value;
	}

	var openerYgId = $K.windows[which.up('.ywindow').id].yg_id;
	var wndobj = new $K.yg_wndobj({ config: 'LINK_SELECT', openerYgId: openerYgId, loadparams: { site: site, opener_reference: which.identify(), special_url: src_data.param01 } });

	var href = src_data.param01;
	var onclick = src_data.param04;

	sel = "LINK_SELECT_PAGE";
	if (which.up('.editlink')) {
		var iconElement = which.up('.editlink').down('div.icn');
	} else {
		var iconElement = which.down('div.icn');
	}
	if ((iconElement == undefined) || (iconElement && (iconElement.style.display == 'none'))) {
		// Is File
		sel = "LINK_SELECT_FILE";
	} else {
		// Is Link/Email
		if (src_data.param01.startsWith('mailto:')) {
			sel = "LINK_SELECT_EMAIL";
		}
	}

	// select tab
	for (var i = 0; i < wndobj.tabs.elements.length; i++) {
		if (wndobj.tabs.elements[i]['NAME'] == sel) {
			wndobj.tabs.select(i);
			wndobj.linkType = sel;
		}
	}
}


/**
 * Inits a link-chooser-window
 * @param { String } [winID] The window-id
 * @param { String } [openerReference] The reference to the opener
 * @function
 * @name $K.yg_initLink
 */
$K.yg_initLink = function( winID, openerRef ) {

	if (openerRef.endsWith('property')) {
		// For properties
		var src_data = {
			param01: $(openerRef).readAttribute('param01'),		// HREF
			param02: $(openerRef).readAttribute('param02'),		// TARGET
			param03: $(openerRef).readAttribute('param03'),		// TEXT
			param04: $(openerRef).readAttribute('param04')		// ONCLICK
		};

		formObj = document.forms['wid_'+winID+'_'+$K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME']];
		var href = src_data.param01;
		var onclick = src_data.param04;

		if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_PAGE') &&
			 ($K.windows['wid_'+winID].linkType == 'LINK_SELECT_PAGE') ) {

			if ($(openerRef).hasClassName('mk_property')) {
				var openerBlock = $(openerRef);
			} else {
				var openerBlock = $(openerRef).up('.mk_property');
			}

			if (openerBlock.down('.icn') && openerBlock.down('.icn').hasClassName('iconlink')) {
				formObj.pagename.value = '';
			} else {
				formObj.pagename.value = openerBlock.down('.title_txt').innerHTML.strip();
			}

			if ($(formObj.elements['href']).readAttribute('preset') != 'true') {
				yeagerCE_setFormValue('href', href, formObj);
			}
			yeagerCE_setFormValue('onclick', onclick, formObj);

			if (onclick != null && onclick.indexOf('window.open') != -1) {
				yeagerCE_parseWindowOpen(onclick, formObj);
			} else if (onclick != null) {
				yeagerCE_parseFunction(onclick);
			}
			if (formObj.dd_target) {
				$K.yg_dropdownSelect(formObj.dd_target, false, src_data.param02, true);
			}

		} else if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_EMAIL') &&
					($K.windows['wid_'+winID].linkType == 'LINK_SELECT_EMAIL') ) {

			// email
			if ($(formObj.elements['email']).readAttribute('preset') != 'true') {
				formObj.email.value = href.replace(/mailto:/,'');
				formObj.href.value = '';
			}

		} else if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_FILE') &&
					($K.windows['wid_'+winID].linkType == 'LINK_SELECT_FILE') ) {

			if ($(formObj.elements['href']).readAttribute('preset') != 'true') {
				formObj.href.value = href;
			}
			formObj.filetitle = $(openerRef).readAttribute('param03');

		}
	} else {
		// For controls
		var src_data = {
			param01: $(openerRef).readAttribute('param01'),		// HREF
			param02: $(openerRef).readAttribute('param02'),		// TARGET
			param03: $(openerRef).readAttribute('param03'),		// TEXT
			param04: $(openerRef).readAttribute('param04')		// ONCLICK
		};

		formObj = document.forms['wid_'+winID+'_'+$K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME']];
		var href = src_data.param01;
		var onclick = src_data.param04;

		if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_PAGE') &&
			 ($K.windows['wid_'+winID].linkType == 'LINK_SELECT_PAGE') ) {

			if ($(openerRef).down('.icn') && $(openerRef).down('.icn').hasClassName('iconlink')) {
				formObj.pagename.value = '';
			} else {
				formObj.pagename.value = $(openerRef).down('.title_txt').innerHTML;
			}

			if ($(formObj.elements['href']).readAttribute('preset') != 'true') {
				yeagerCE_setFormValue('href', href, formObj);
			}
			yeagerCE_setFormValue('onclick', onclick, formObj);

			if (onclick != null && onclick.indexOf('window.open') != -1) {
				yeagerCE_parseWindowOpen(onclick, formObj);
			} else {
				yeagerCE_parseFunction(onclick);
			}
			if (formObj.dd_target) {
				$K.yg_dropdownSelect(formObj.dd_target, false, src_data.param02, true);
			}

		} else if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_EMAIL') &&
					($K.windows['wid_'+winID].linkType == 'LINK_SELECT_EMAIL') ) {

			// email
			if ($(formObj.elements['email']).readAttribute('preset') != 'true') {
				formObj.email.value = href.replace(/mailto:/,'');
				formObj.href.value = '';
			}

		} else if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_FILE') &&
					($K.windows['wid_'+winID].linkType == 'LINK_SELECT_FILE') ) {

			if ($(formObj.elements['href']).readAttribute('preset') != 'true') {
				formObj.href.value = href;
			}

			if ($(openerRef).down('span.filetype')) {
				formObj.filecolor.value = $(openerRef).down('span.filetype').className.replace(/filetype/, '').strip();
				formObj.filetype.value = $(openerRef).down('span.filetype').innerHTML.strip();
				formObj.filetitle.value = $(openerRef).down('.title_txt').innerHTML.replace(/<(.*)>/, '');
			} else {
				formObj.filecolor.value = '';
				formObj.filetype.value = '';
				formObj.filetitle.value = '';
			}
		}
	}

}


/**
 * Submits a link-chooser-window
 * @param { String } [winID] The window-id
 * @param { String } [openerRef] The reference to the opener
 * @function
 * @name $K.yg_submitLink
 */
$K.yg_submitLink = function( winID, openerRef ) {

	var src_content = $(openerRef).down('.title_txt');
	var data = new Object();

	if ($('wid_'+winID+'_LINK_SELECT_PAGE') && ($('wid_'+winID+'_LINK_SELECT_PAGE').style.display != 'none')) {

		// page
		formObj = document.forms['wid_'+winID+'_LINK_SELECT_PAGE'];
		if (formObj.ispopup) {
			formObj.ispopup.value = '0';
		}

		if (formObj.pagename.value.strip() == $K.TXT('TXT_SELECTOR_LINK')) {
			formObj.pagename.value = '';
		}

		var link_matches = formObj.href.value.match($K.urlParseRegEx);
		if ( (link_matches!=null) ||
			 ((formObj.pagename.value) && (formObj.href.value)) ) {
			// internal Link
			data['objecttype'] = 'page';
			if (formObj.pagename.value) {
				data['title'] = formObj.pagename.value;
			} else {
				data['title'] = formObj.href.value;
			}
		} else {
			// external link
			data['objecttype'] = 'link';
			data['title'] = formObj.href.value;
		}
		if (formObj['wid_'+winID+'_dd_targets']) {
			data['target'] = formObj['wid_'+winID+'_dd_targets'].value;
		}
		if (formObj.onclick) {
			data['onclick'] = formObj.onclick.value;
		}

	} else if ($('wid_'+winID+'_LINK_SELECT_EMAIL') && ($('wid_'+winID+'_LINK_SELECT_EMAIL').style.display != 'none')) {

		data['objecttype'] = 'email';

		// email
		formObj = document.forms['wid_'+winID+'_LINK_SELECT_EMAIL'];
		if (formObj.email.value!='') {
			formObj.href.value = 'mailto:' + formObj.email.value;
		} else {
			formObj.href.value = '';
		}
		data['title'] = formObj.href.value.replace(/mailto:/,'');

	} else if ($('wid_'+winID+'_LINK_SELECT_FILE') && ($('wid_'+winID+'_LINK_SELECT_FILE').style.display != 'none')) {

		data['objecttype'] = 'file';

		// download
		formObj = document.forms['wid_'+winID+'_LINK_SELECT_FILE'];
		data['yg_id'] = formObj.file.value;
		data['filecolor'] =  formObj.filecolor.value;
		data['filetype'] =  formObj.filetype.value;
		data['title'] =  formObj.filetitle.value;
	}

	data['href'] = formObj.href.value;

	if (openerRef.endsWith('property')) {
		// For properties
		var updateFunc = function(pageName) {
			if(pageName) {
				data.objecttype = 'page';
				data.title = pageName;
			}

			$(openerRef).update(data.title);
			$(openerRef).up('.selectionmarker').next().value = data.href;

			$K.yg_setObjectProperty( $(openerRef), data.href );
			$K.yg_fadeField( $(openerRef).up('.cntblock') );
		}

		if (data && data['objecttype'] && data['objecttype'] == 'link') {
			// Special case for links, call to backend is required first to check internal/external
			$(openerRef).up('.title').previous().className = 'icn iconlink';
			$(openerRef).up('.title').previous().show();

			new Ajax.Request( $K.appdir+'responder', {
				asynchronous: true,
				method: 'post',
				parameters: {
					data: Object.toJSON([
						'noevent',
						{
							yg_property: 'checkLinkExternal',
							params: {
								url: formObj.href.value,
								us:	document.body.id,
								lh: $K.yg_getLastGuiSyncHistoryId()
							}
						}
					]),
					handler: 'checkLinkExternal',
					us: document.body.id,
					lh: $K.yg_getLastGuiSyncHistoryId()
				},
				onSuccess: function(transport) {
					if (transport.responseText.stripScripts().strip() != 'external') {
						updateFunc(transport.responseText.stripScripts().strip());
					} else {
						updateFunc();
					}
				}
			});
		} else {
			if (data && data['objecttype'] && data['objecttype'] == 'page') {
				$(openerRef).up('.title').previous().className = 'icn iconpage';
				$(openerRef).up('.title').previous().show();
			} else if (data && data['objecttype'] && data['objecttype'] == 'email') {
				$(openerRef).up('.title').previous().className = 'icn iconemail';
				$(openerRef).up('.title').previous().show();
			} else if (data && data['objecttype'] && data['objecttype'] == 'file') {
				$(openerRef).up('.title').previous().hide();
			}
			if ((!data['title']) || (data['title'] == '')) {
				$(openerRef).up('.title').previous().className = 'icn iconlink';
				$(openerRef).up('.title').previous().show();
			}
			updateFunc();
		}

	} else {
		// For controls
		var updateFunc = function(pageName) {
			if (pageName) {
				data.objecttype = 'page';
				data.title = pageName;
			}
			$K.yg_editControl( $(openerRef).down('.title_txt'), '5', false, data );
			$K.yg_fadeField( $(openerRef) );
		}

		if (data && data['objecttype'] && data['objecttype'] == 'link') {
			// Special case for links, call to backend is required first to check internal/external

			new Ajax.Request( $K.appdir+'responder', {
				asynchronous: true,
				method: 'post',
				parameters: {
					data: Object.toJSON([
						'noevent',
						{
							yg_property: 'checkLinkExternal',
							params: {
								url: formObj.href.value,
								us:	document.body.id,
								lh: $K.yg_getLastGuiSyncHistoryId()
							}
						}
					]),
					handler: 'checkLinkExternal',
					us: document.body.id,
					lh: $K.yg_getLastGuiSyncHistoryId()
				},
				onSuccess: function(transport) {
					if (transport.responseText.stripScripts().strip() != 'external') {
						updateFunc(transport.responseText.stripScripts());
					} else {
						updateFunc();
					}
				}
			});
		} else {
			updateFunc();
		}
	}

	$K.windows['wid_'+winID].remove();
}


/**
 * Opens a contenteditor-link-chooser-window
 * @param { Instance } [inst] The instance of the contenteditor
 * @function
 * @name $K.yg_openCELinkChooserWindow
 */
$K.yg_openCELinkChooserWindow = function( inst ) {

	yeagerCE_saveSelection();

	var site = $K.windows[ $( $K.windows[ $(inst.editorId).up('.ywindow').id ].loadparams['formfield']).up('.ywindow').id ].yg_id.split('-')[1];
	if (site != parseInt(site)) site = "";

	var elm = inst.selection.getNode();
	var action = 'insert';
	elm = inst.dom.getParent(elm, 'A');
	if (elm != null && elm.nodeName == 'A') action = 'update';

	var origLink = inst.dom.getAttrib(elm, 'href');

	var openFunc = function(linkInfo) {

		var wndobj = new $K.yg_wndobj( { config: 'CONTENTEDITOR_LINK_SELECT', loadparams: { opener_reference: inst.editorId, site: site, special_url: origLink } });

		sel = "LINK_SELECT_PAGE";
		if (action == 'update') {
			var href = inst.dom.getAttrib(elm, 'href');
			var onclick = inst.dom.getAttrib(elm, 'onclick');

			// Check if email-link
			if (href.startsWith('mailto:')) {
				sel = "LINK_SELECT_EMAIL";
			}
			// Check if anchor link
			if (href.charAt(0) == '#') {
				sel = "LINK_SELECT_ANCHOR";
			}
			// check if download link
			if ((linkInfo.TYPE == 'DOWN') || (linkInfo.TYPE == 'IMG')) {
				sel = "LINK_SELECT_FILE";
			}
		}

		// select tab
		for (var i = 0; i < wndobj.tabs.elements.length; i++) {
			if (wndobj.tabs.elements[i]['NAME'] == sel) {
				wndobj.tabs.select(i);
				wndobj.linkType = sel;
			}
		}
	}

	new Ajax.Request( $K.appdir+'responder', {
		asynchronous: true,
		method: 'post',
		parameters: {
			data: Object.toJSON([
				'noevent',
				{
					yg_property: 'checkSpecialLinkType',
					params: {
						url: inst.dom.getAttrib(elm, 'href'),
						us:	document.body.id,
						lh: $K.yg_getLastGuiSyncHistoryId()
					}
				}
			]),
			handler: 'checkSpecialLinkType',
			us: document.body.id,
			lh: $K.yg_getLastGuiSyncHistoryId()
		},
		onSuccess: function(transport) {
			openFunc(transport.responseText.stripScripts().evalJSON());
		}
	});

}


/**
 * Opens a contenteditor-link-chooser-window
 * @param { String } [winID] The window id
 * @param { Instance } [editor] The instance of the contenteditor
 * @function
 * @name $K.yg_initCELink
 */
$K.yg_initCELink = function( winID, editor ) {

	formObj = document.forms['wid_'+winID+'_'+$K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME']];

	var inst = tinyMCE.editors[editor];
	var elm = inst.selection.getNode();
	var action = 'insert';
	elm = inst.dom.getParent(elm, 'A');
	if (elm != null && elm.nodeName == 'A') action = 'update';

	if ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_ANCHOR') {

		// fill anchor dd
		var nodes = inst.dom.select('a.mceItemAnchor,img.mceItemAnchor'), name, i;
		for (i=0; i<nodes.length; i++) {
			if ((name = inst.dom.getAttrib(nodes[i], "name")) != "") {
				$K.yg_dropdownInsert('wid_'+winID+'_dd_anchors', name, '#'+name, 0);
			}
		}
	}

	if (action == 'update') {
		var href = inst.dom.getAttrib(elm, 'href');
		var onclick = inst.dom.getAttrib(elm, 'onclick');

		// Setup form data
		if ($(formObj.elements['href']).readAttribute('preset') != 'true') {
			yeagerCE_setFormValue('href', href, formObj);
		}

		if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_PAGE') &&
			 ($K.windows['wid_'+winID].linkType == 'LINK_SELECT_PAGE') ) {

			// page
			yeagerCE_setFormValue('onclick', onclick, formObj);

			if (onclick != null && onclick.indexOf('window.open') != -1) {
				yeagerCE_parseWindowOpen(onclick, formObj);
			} else {
				yeagerCE_parseFunction(onclick, formObj);
			}
			if (formObj.dd_target) {
				$K.yg_dropdownSelect(formObj.dd_target, false, inst.dom.getAttrib(elm, 'target'), true);
			}

		} else if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_EMAIL') &&
					($K.windows['wid_'+winID].linkType == 'LINK_SELECT_EMAIL') ) {

			// email
			formObj.email.value = href.replace(/mailto:/,'');

		} else if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_ANCHOR') &&
					($K.windows['wid_'+winID].linkType == 'LINK_SELECT_ANCHOR') ) {

			// anchor
			$K.yg_dropdownSelect(formObj.dd_anchor, false, href, true);

		} else if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_FILE') &&
					($K.windows['wid_'+winID].linkType == 'LINK_SELECT_FILE') ) {

			// file
			formObj.file.value = href;

		}

		// Clear inputfield if needed
		if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_PAGE') &&
			 ($K.windows['wid_'+winID].linkType != 'LINK_SELECT_PAGE') ) {
			// page
			formObj.href.value = '';
		} else if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_EMAIL') &&
					($K.windows['wid_'+winID].linkType != 'LINK_SELECT_EMAIL') ) {
			// email
			formObj.email.value = '';
		} else if ( ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_FILE') &&
					($K.windows['wid_'+winID].linkType != 'LINK_SELECT_FILE') ) {
			// file
			formObj.href.value = '';
			formObj.file.value = '';
		}

	} else if (action == "insert") {

		if (formObj.href) formObj.href.value = '';
		if (formObj.email) formObj.email.value = '';
		if (formObj.file) formObj.file.value = '';

	}

}


/**
 * Submits a contenteditor-link-chooser-window
 * @param { String } [winID] The window id
 * @param { Instance } [editor] The instance of the contenteditor
 * @function
 * @name $K.yg_submitCE_link
 */
$K.yg_submitCE_link = function( winID, editor ) {
	var inst = tinyMCE.editors[editor];
	var elm, elementArray, i;

	yeagerCE_restoreSelection();

	elm = inst.selection.getNode();

	formObj = document.forms['wid_'+winID+'_'+$K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME']];

	yeagerCE_checkPrefix( formObj.href );

	elm = inst.dom.getParent(elm, 'A');

	if ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_PAGE') {
		// link
		formObj.ispopup.value = '0';

	} else if ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_EMAIL') {

		// email
		if (formObj.email.value!='') {
			formObj.href.value = 'mailto:' + formObj.email.value;
		} else {
			formObj.href.value = '';
		}

	} else if ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_ANCHOR') {

		// anchor
		if (formObj['wid_'+winID+'_dd_anchors'].value!='') {
			formObj.href.value = formObj['wid_'+winID+'_dd_anchors'].value;
		} else {
			formObj.href.value = '';
		}

	} else if ($K.windows['wid_'+winID].tabs.elements[$K.windows['wid_'+winID].tabs.selected]['NAME'] == 'LINK_SELECT_FILE') {

		// file

	}

	// Remove element if there is no href
	if (!formObj.href.value) {
		tinyMCE.execCommand('mceBeginUndoLevel');
		i = inst.selection.getBookmark();
		inst.dom.remove(elm, 1);
		inst.selection.moveToBookmark(i);
		tinyMCE.execCommand('mceEndUndoLevel');
		$K.windows['wid_'+winID].remove();
		return;
	}

	tinyMCE.execCommand('mceBeginUndoLevel');

	// Create new anchor elements
	if (elm == null) {
		tinyMCE.execCommand('CreateLink', false, '#mce_temp_url#', {skip_undo : 1});
		elementArray = tinymce.grep(inst.dom.select('a'), function(n) {return inst.dom.getAttrib(n, 'href') == '#mce_temp_url#';});
		for (i=0; i<elementArray.length; i++) {
			yeagerCE_setAllAttribs(elm = elementArray[i], formObj, editor);
		}
	} else {
		yeagerCE_setAllAttribs(elm, formObj, editor);
	}

	// Don't move caret if selection was image
	if (elm.childNodes.length != 1 || elm.firstChild.nodeName != 'IMG') {
		inst.focus();
		inst.selection.select(elm);
		inst.selection.collapse(0);
		//tinyMCE.storeSelection();
	}

	tinyMCE.execCommand('mceEndUndoLevel');
	$K.windows['wid_'+winID].remove();
}
