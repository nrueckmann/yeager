/**
 * Saves the template configuration
 * @param { String } [winRef] Reference to the window
 * @function
 * @name $K.yg_templateSaveInfo
 */
$K.yg_templateSaveInfo = function( winRef ) {
	winRef = $(winRef);

	var inputFields = $(winRef.id+'_innercontent').select('input', 'textarea');

	var parameters = {
		objectID: $K.windows[winRef.id].yg_id.split('-')[0],
		wid: winRef.id
	};

	var srch = new RegExp(winRef.id+'_');
	var hasError = false;
	inputFields.each(function(item){
		item.removeClassName('error');
		if (item.value.indexOf('-') != -1) {
			hasError = true;
			item.addClassName('error');
		}
		var idxname = item.name.replace(srch, '');
		if (idxname) parameters[idxname] = item.value;
		if (item.name == winRef.id+'_template_identifier') {
			if (item.value.trim() == '') {
				hasError = true;
				item.addClassName('error');
			}
		}
	});

	if (!hasError) {
		var data = Array ( 'noevent', {yg_property: 'saveTemplateInfo', params: parameters } );
		$K.yg_AjaxCallback( data, 'saveTemplateInfo' );
	}
}


/**
 * Removes an assigned templatepreview
 * @param { Element } [reference] Reference to the preview
 */
$K.yg_removeTemplatePreview = function(reference) {
	var winRef = reference.up('.ywindow');
	var yg_id = $K.windows[winRef.id].yg_id.split('-')[0];
	var imgRef = reference.down('img');

	// Clear name
	$(winRef.id+'_templatepreviewname').update($K.TXT('TXT_SELECTOR_PREVIEW_UPLOAD'));

	// Reset flag
	$(winRef.id+'_previewchanged').value = '';

	// Hide Image
	imgRef.hide();

	imgRef.next().value = 'true';

	$K.yg_hilite('template', $K.windows[winRef.id].yg_id, 'name', true);
}


/**
 * Inits the upload buttons for templates and previews in the template-details-tab
 * @param { String } [wndId] Id of parent window
 * @param { String } [objectID] Id of the template
 */
$K.yg_initTemplateUploadButtons = function(winID, objectID) {
	if (!$('wid_'+winID+'_addtemplatebutton')) return;
	if ($K.uploadFramework == 'swfuploader') {
		var swfuploadSettings = Object.clone( $K.yg_SWFUploadSettings );
		Object.extend(swfuploadSettings, {
			upload_url: $K.appdir + 'responder?handler=uploadTemplate',
			flash_url: $K.jsdir + '3rd/swfupload/swfupload.swf',
			button_placeholder_id: 'wid_'+winID+'_addtemplatebutton',
			file_types: "*",
			file_types_description: "Templates",
			custom_settings: {
				uploadButtonId: 'wid_'+winID+'_okbutton',
				uploadTitle: 'N/A',
				uploadType: 'template',
				fileID: objectID,
				winID: winID,
				timestamp: ($('wid_'+winID+'_timestamp'))?($('wid_'+winID+'_timestamp').value):(0),
				autoUpload: true
			},
			button_action: SWFUpload.BUTTON_ACTION.SELECT_FILE
		} );

		var swfuploadSettings2 = Object.clone( $K.yg_SWFUploadSettings );
		Object.extend(swfuploadSettings2, {
			upload_url: $K.appdir + 'responder?handler=uploadTemplatePreview',
			flash_url: $K.jsdir + '3rd/swfupload/swfupload.swf',
			button_placeholder_id: 'wid_'+winID+'_addtemplatepreviewbutton',
			file_types: "*.jpg;*.jpeg;*.gif;*.png",
			file_types_description: "Images",
			custom_settings: {
				uploadButtonId: 'wid_'+winID+'_okbutton',
				uploadTitle: 'N/A',
				uploadType: 'templatePreview',
				fileID: objectID,
				winID: winID,
				autoUpload: true
			},
			button_height: '2000',
			button_action: SWFUpload.BUTTON_ACTION.SELECT_FILE
		} );

		$K.yg_SWFUploadObjects['wid_'+winID] = new SWFUpload( swfuploadSettings );
		$K.yg_SWFUploadObjects['wid_'+winID+'_2'] = new SWFUpload( swfuploadSettings2 );
	}

	if ($K.uploadFramework == 'plupload') {
		/* Init Uploads */
		var customSettings = {
				uploadButtonId: 'wid_'+winID+'_okbutton',
				uploadTitle: 'N/A',
				uploadType: 'template',
				fileID: objectID,
				winID: winID,
				timestamp: ($('wid_'+winID+'_timestamp'))?($('wid_'+winID+'_timestamp').value):(0),
				autoUpload: true
		};
		$K.yg_UploadInit( 'wid_'+winID, 'wid_'+winID+'_addtemplatebutton', null, {handler: 'uploadTemplate'}, customSettings, [{title: 'Templates', extensions: 'htm,html'}], false, true );

		var customSettings2 = {
				uploadButtonId: 'wid_'+winID+'_okbutton',
				uploadTitle: 'N/A',
				uploadType: 'templatePreview',
				fileID: objectID,
				winID: winID,
				autoUpload: true
		};
		$K.yg_UploadInit( 'wid_'+winID+'_2', 'wid_'+winID+'_addtemplatepreviewbutton', null, {handler: 'uploadTemplatePreview'}, customSettings2, [{title: 'Images', extensions: 'jpg,jpeg,gif,png'}], false, true );

	}

}


/**
 * Set the picture for the template preview after uploading
 * @param { String } [wndId] Id of parent window
 * @param { String } [objectID] Id of the template
 */
$K.yg_setTemplatePreviewPicture = function(winID, fileName, previewURL) {
	if ($('wid_'+winID+'_templatepreview')) {
		$('wid_'+winID+'_templatepreviewname').update(fileName);
		$('wid_'+winID+'_templatepreview').src = previewURL;
		$('wid_'+winID+'_templatepreview').show();
	}
}


/**
 * Set the filename for the template after uploading
 * @param { String } [wndId] Id of parent window
 * @param { String } [objectID] Id of the template
 */
$K.yg_setTemplateFileName = function(winID, fileName) {
	if ($('wid_'+winID+'_templatename')) {
		$('wid_'+winID+'_templatename').update(fileName);
	}
}


/**
 * Refreshes the templateconfig tab (scrollbars and uploadpanel for images)
 * @param { String } [wndId] Id of parent window
 */
$K.yg_refreshTemplateconfig = function( which ) {
	which = $(which);
	$K.windows[which.up('.ywindow').id].refresh();

	var panelHeight = which.up('.cntblockcontainer').getHeight();
	var flashContainer = which.up('.cntblockcontainer').down('.mk_uploadpanel');
	flashContainer.setStyle({height:panelHeight+'px'});
	if (flashContainer.down('object')) flashContainer.down('object').setStyle({height:panelHeight+'px'});
}


/**
 * Add child node to the currently selected node
 * @param { Element } [fileref] The element from which the function was called.
 * @function
 * @name $K.yg_addTemplateChildFolder
 */
$K.yg_addTemplateChildFolder = function( templateref ) {

	// Topbar buttons or actionbuttons?
	if (templateref.hasClassName('tree_btn')) {
		if (templateref.hasClassName('disabled')) return;
		var template = $K.windows[templateref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt( templateref.up('.ywindow').id.replace(/wid_/g, '') );
		var nodeid = templateref.id;
		var template = nlsTree['templates_tree'+wid+'_tree'].nLst[nodeid].yg_id;
	}

	template = template.split('-')[0];

	var data = Array ( 'noevent', {yg_property: 'addTemplateChildFolder', params: {
		template: template
	} } );
	$K.yg_AjaxCallback( data, 'addTemplateChildFolder' );
}


/**
 * Changes the name of a navigation in the corresponding dropdown
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_changeNavigationName
 */
$K.yg_changeNavigationName = function( which ) {
	which = $(which);

	var newNavValue = which.value;
	var navName = which.name.split('_')[3];

	$(which.up('.mk_contentgroup').down('.dropdownbox').id+"_ddlist").down().select('div').each(function(item){
		if (item.readAttribute('value').toLowerCase() == navName) {
			item.update( newNavValue );
			if (item.hasClassName('selected')) {
				which.up('.mk_contentgroup').down('.dropdowninner').down('input').value = newNavValue;
			}
		}
	});
}


/**
 * Adds a contentarea field to a template
 * @param { String } [winID] The id of the relevant window
 * @param { String } [code] The code of the contentarea
 * @function
 * @name $K.yg_addTemplateContentareaField
 */
$K.yg_addTemplateContentareaField = function( winID, code ) {
	var container = $('wid_'+winID+'_contentareas_list');
	var templateLine = container.down('.mk_template').outerHTML;

	templateLine = templateLine.replace(/__FIELDNAME__/g, code.toLowerCase());
	templateLine = templateLine.replace(/__fieldname__/g, code.toLowerCase());
	templateLine = templateLine.replace(/__FIELDTITLE__/g, code);
	templateLine = templateLine.replace(/__fieldtitle__/g, code);
	templateLine = templateLine.replace(/display:none;/g, '');
	templateLine = templateLine.replace(/display: none;/g, '');

	container.insert(templateLine);

	container.previous().hide();
	container.show();
	$K.windows['wid_'+winID].refresh();
}


/**
 * Removes a contentarea field from a template
 * @param { String } [winID] The id of the relevant window
 * @param { String } [code] The code of the contentarea
 * @function
 * @name $K.yg_removeTemplateContentareaField
 */
$K.yg_removeTemplateContentareaField = function( winID, code ) {
	var container = $('wid_'+winID+'_contentareas_list');
	container.select('input').each(function(item){
		if (item.name == 'wid_'+winID+'_contentarea_'+code.toLowerCase()+'_name') {
			item.up('li').remove();
		}
	});
	if (container.select('li').length == 1) {
		container.hide();
		container.previous().show();
	}
	$K.windows['wid_'+winID].refresh();
}


/**
 * Adds a navigation field to a template
 * @param { String } [winID] The id of the relevant window
 * @param { String } [code] The code of the navigation
 * @function
 * @name $K.yg_addTemplateNavigationField
 */
$K.yg_addTemplateNavigationField = function( winID, code, selected ) {
	var container = $('wid_'+winID+'_navigations');
	var templateLine = container.down('.mk_template').outerHTML;

	templateLine = templateLine.replace(/__FIELDNAME__/g, code.toLowerCase());
	templateLine = templateLine.replace(/__fieldname__/g, code.toLowerCase());
	templateLine = templateLine.replace(/__FIELDTITLE__/g, code);
	templateLine = templateLine.replace(/__fieldtitle__/g, code);
	templateLine = templateLine.replace(/display:none;/g, '');
	templateLine = templateLine.replace(/display: none;/g, '');

	container.insert(templateLine);

	container.previous().hide();
	container.show();

	$K.yg_dropdownInsert( $('wid_'+winID+'_default_navigation'), code, code, false );
	if (selected) {
		$('wid_'+winID+'_default_navigation').down('input').value = code;
		$('wid_'+winID+'_default_navigation').down('input[type=hidden]').value = code;
	}

	$('wid_'+winID+'_navidropdown').show();

	$K.windows['wid_'+winID].refresh();
}


/**
 * Generates a new identifier based on the name
 * @param { Element } [which] Reference to the inputfield
 * @function
 * @name $K.yg_updateTemplateIdentifier
 */
$K.yg_updateTemplateIdentifier = function( which ) {
	which = $(which);
	var winRef = which.up('.ywindow');

	if ($(winRef.id+'_template_identifier').value.strip() == '') {
		var newName = which.value.strip();
		newName = newName.replace(/ /g, '_');
		newName = newName.replace(/&/g, '');
		newName = newName.replace(/\//g, '_');
		$(winRef.id+'_template_identifier').value = newName;
	}
}


/**
 * Removes a navigation field from a template
 * @param { String } [winID] The id of the relevant window
 * @param { String } [code] The code of the navigation
 * @function
 * @name $K.yg_removeTemplateNavigationField
 */
$K.yg_removeTemplateNavigationField = function( winID, code ) {
	var container = $('wid_'+winID+'_navigations');
	container.select('input').each(function(item){
		if (item.name == 'wid_'+winID+'_navigation_'+code.toLowerCase()+'_name') {
			item.up('tr').remove();
		}
	});

	$($('wid_'+winID+'_default_navigation').id+"_ddlist").down('.dropdownlist').select('div').each(function(item) {
		if (item.readAttribute('value').toLowerCase() == code.toLowerCase()) {
			item.remove();
			return;
		}
		if (item.value && item.value.toLowerCase() == code.toLowerCase()) {
			item.remove();
			return;
		}
	});

	if (container.select('tr').length == 1) {
		container.hide();
		$('wid_'+winID+'_navidropdown').hide();
		container.previous().show();
	}
	$K.windows['wid_'+winID].refresh();
}


/**
 * Checks the identifier of a template
 * @param { Element } [element] The element from which the function was called.
 * @function
 * @name $K.yg_checkTemplatePName
 */
$K.yg_checkTemplatePName = function( element ) {
	var value = element.value;

	if (!element.oldvalue) {
		element.oldvalue = element.readAttribute('oldvalue');
	}

	if (value.strip()=='') {
		element.value = element.oldvalue;
		$K.yg_promptbox( $K.TXT('TXT_CHANGE_TEMPLATE_IDENTIFIER'), $K.TXT('TXT_CHANGE_TEMPLATE_IDENTIFIER_EMPTY'), 'alert');
		return false;
	}

	return true;
}


/**
 * Add child node to the currently selected node
 * @param { Element } [fileref] The element from which the function was called.
 * @function
 * @name $K.yg_addTemplate
 */
$K.yg_addTemplate = function( templateref ) {

	// Topbar buttons or actionbuttons?
	if (templateref.hasClassName('tree_btn')) {
		if (templateref.hasClassName('disabled')) return;
		var template = $K.windows[templateref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt( templateref.up('.ywindow').id.replace(/wid_/g, '') );
		var nodeid = templateref.id;
		var template = nlsTree['templates_tree'+wid+'_tree'].nLst[nodeid].yg_id;
	}

	template = template.split('-')[0];

	var data = Array ( 'noevent', {yg_property: 'addTemplate', params: {
		template: template
	} } );
	$K.yg_AjaxCallback( data, 'addTemplate' );
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @param { Boolean } [multi] True if multiple items are selected.
 * @function
 * @name $K.yg_actionDeleteTemplate
 */
$K.yg_actionDeleteTemplate = function( which, multi ) { $K.yg_deleteElement( which.up(2).reference, multi ); }


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionAddTemplateFolder
 */
$K.yg_actionAddTemplateFolder = function( which ) { $K.yg_addTemplateChildFolder( which.up(2).reference ); }


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionAddTemplate
 */
$K.yg_actionAddTemplate = function( which ) { $K.yg_addTemplate( which.up(2).reference ); }
