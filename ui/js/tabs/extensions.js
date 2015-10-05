/**
 * Switch between different types of extensions
 * @param { String } [wndid] Id of parent window
 * @param { String } [value] 'page', 'import' or 'export'
 */
$K.yg_switchExtensionType = function(wndid, value, objecttype) {

	// AJAX Update content
	container = $(wndid+'_innercontent').down(0);
	container.update('')
	container.setStyle({width:'auto'})
	container.addClassName( 'tab_loading' );
	$K.windows[wndid].refresh();

	new Ajax.Updater(container, $K.appdir+'extension_info', {
		asynchronous: true,
		evalScripts: true,
		method: 'post',
		insertion: 'bottom',
		onComplete: function() {
			$K.yg_customAttributeHandler(container);
			container.removeClassName( 'tab_loading' );
			$K.windows[wndid].refresh();
		},
		parameters: {
			extensiontype: value,
			objecttype: objecttype,
			mode: 'refresh',
			us: document.body.id,
			lh: $K.yg_getLastGuiSyncHistoryId(),
			win_no: wndid.replace(/wid_/,'')
		}
	});

}


/**
 * Switch between different types of extensions
 * @param { Element } [winRef] Reference to the window
 * @param { Integer } [extensionType] Reference to the button
 * @param { Boolean } [fromDataAdmin] TRUE if fired from Data-Admin
 */
$K.yg_saveExtensionProperties = function( winRef, extensionType, fromDataAdmin ) {

	var contentRef = $(winRef.id+'_PROPERTIES');
	var yg_id = $K.windows[winRef.id].yg_id.split('-')[0];
	var inputFields = contentRef.select('input', 'textarea');

	var propertiesData = {}
	inputFields.each(function(item){
		if (item.up('.checkbox')) {
			var tSuffix = item.up('.checkbox').readAttribute('yg_property');
		} else {
			var tSuffix = item.readAttribute('yg_property');
		}
		if (tSuffix) {
			propertiesData['prop_'+tSuffix] = item.value;
		}
	});

	var data = Array ( 'noevent', {yg_property: 'setExtensionProperties', params: Object.extend({
		extension: yg_id,
		wid: winRef.id,
		propertiesData: propertiesData,
		extensionType: extensionType,
		fromDataAdmin: fromDataAdmin
	}, propertiesData) } );
	$K.yg_AjaxCallback( data, 'setExtensionProperties' );
}


/**
 * Installs a extensions
 * @param { Element } [buttonReference] Reference to the button
 */
$K.yg_installExtension = function( buttonReference ) {
	var yg_id = $K.windows[$(buttonReference).up('.ywindow').id].yg_id.split('-')[0];
	var data = Array ( 'noevent', {yg_property: 'installExtension', params: {
		extension: yg_id,
		wid: $(buttonReference).up('.ywindow').id
	} } );
	$K.yg_AjaxCallback( data, 'installExtension' );
}


/**
 * Uninstalls a extensions
 * @param { Element } [buttonReference] Reference to the button
 */
$K.yg_uninstallExtension = function( buttonReference ) {

	if ( buttonReference.hasClassName('tree_btn_extension') ) {
		if (buttonReference.hasClassName('disabled')) {
			return;
		}
		var yg_id = $K.windows[$(buttonReference).up('.ywindow').id].yg_id.split('-')[0];
	} else {
		var itemReference = $(buttonReference).up('li');
		var yg_id = itemReference.readAttribute('yg_id').split('-')[0];
	}

	var data = Array ( 'noevent', {yg_property: 'uninstallExtension', params: {
		extension: yg_id,
		wid: $(buttonReference).up('.ywindow').id
	} } );
	$K.yg_AjaxCallback( data, 'uninstallExtension' );
}


/**
 * Updates the extension in the list to the un-/installed state
 * @param { Element } [buttonReference] Reference to the button
 */
$K.yg_setExtensionInstalled = function( extensionId, windowId, setInstalled ) {
	windowId = windowId.replace(/wid_/,'');

	if (setInstalled) {
		$('item_'+windowId+'_'+extensionId).down('.actionhover').down('.del').show();
	} else {
		$('item_'+windowId+'_'+extensionId).down('.actionhover').down('.del').hide();
	}
}


/**
 * open Extension dialog
 * @param { String } [openerReference] Id of parent window
 */
$K.yg_addExtensionDialog = function ( openerReference ) {

	new Ajax.Updater('dialogcontainer', $K.appdir+'window',
	{
		asynchronous: true,
		evalScripts: true,
		method: 'post',
		insertion: 'bottom',
		onComplete: function() {},
		parameters: {
			display: 'extensions',
			opener_reference: $(openerReference).id,
			yg_id: $K.windows[openerReference.id].yg_id,
			wt: 'dialog',
			ot: 'extension',
			selectiondialog: true,
			us: document.body.id,
			lh: $K.yg_getLastGuiSyncHistoryId()
		}
	});

}


/**
 * Inits the upload buttons for templates and previews in the template-details-tab
 * @param { String } [wndId] Id of parent window
 * @param { String } [objectID] Id of the extension
 */
$K.yg_initExtensionDataUploadButtons = function(winID, objectID) {

	var uploadFieldIndex = 1;
	var uploadFields = $('wid_'+winID+'_PROPERTIES').select('.mk_uploadpanel');

	uploadFields.each(function(item){

		if ($K.uploadFramework == 'swfuploader') {
			var swfuploadSettings = Object.clone( $K.yg_SWFUploadSettings );
			Object.extend(swfuploadSettings, {
				upload_url: $K.appdir + 'responder?handler=uploadExtensionImportData',
				flash_url: $K.jsdir + '3rd/swfupload/swfupload.swf',
				button_placeholder_id: item.down().id,
				button_height: "50",
				file_types: "*.*",
				file_types_description: "ImportData",
				custom_settings: {
					uploadButtonId: 'wid_'+winID+'_okbutton',
					uploadTitle: 'N/A',
					uploadType: 'importData',
					fileID: objectID+'-'+item.readAttribute('yg_property'),
					winID: winID,
					timestamp: ($('wid_'+winID+'_timestamp'))?($('wid_'+winID+'_timestamp').value):(0),
					autoUpload: false,
					extensionDataUpload: true
				},
				button_action: SWFUpload.BUTTON_ACTION.SELECT_FILE
			} );
			$K.yg_SWFUploadObjects['wid_'+winID+'_'+uploadFieldIndex] = new SWFUpload( swfuploadSettings );
		}

		if ($K.uploadFramework == 'plupload') {
			/* Init Upload */
			var customSettings = {
					uploadButtonId: 'wid_'+winID+'_okbutton',
					uploadTitle: 'N/A',
					uploadType: 'importData',
					fileID: objectID+'-'+item.readAttribute('yg_property'),
					winID: winID,
					timestamp: ($('wid_'+winID+'_timestamp'))?($('wid_'+winID+'_timestamp').value):(0),
					autoUpload: false,
					extensionDataUpload: true
			};
			$K.yg_UploadInit( 'wid_'+winID+'_'+uploadFieldIndex, item.down().identify(), null, {handler: 'uploadExtensionImportData'}, customSettings, [], false, true );
		}

		uploadFieldIndex++;
	});

}


/**
 * Saves all Properties (and opens the upload dialog if needed)
 * @param { Element } [buttonReference] Reference to the button
 */
$K.yg_saveExtensionImportProperties = function(winRef) {
	// First save normal properties
	$K.yg_saveExtensionProperties(winRef, 'data', true);

	new $K.yg_wndobj({ config: 'DATA_IMPORT', loadparams: { opener_reference: winRef.id } });
}


/**
 * Saves all Properties (and opens the export dialog)
 * @param { Element } [openerRef] Reference to the opener window
 */
$K.yg_saveExtensionExportProperties = function( winRef ) {
	// First save normal properties
	$K.yg_saveExtensionProperties( winRef, 'data', true);

	// Then upload files (if needed)
	new $K.yg_wndobj({ config: 'DATA_EXPORT', loadparams: { opener_reference: winRef.id } });
}


/**
 * Adds a fileid from the backend to an element in the uploadmanager
 * @param { String } [filePrefix] The prefix of the file.
 * @param { String } [uploadID] The SWFUpload-id of the uploaded element.
 * @param { String } [uploadWinId] The id of the upload-progress-window.
 * @function
 * @name yg_addImportExtensionFileId
 */
$K.yg_addImportExtensionFileId = function( filePrefix, uploadID, extensionId, uploadWinId ) {
	$('wid_'+uploadWinId+'_innercontent').select('li').each(function(item){
		if (item.readAttribute('yg_id') == uploadID+'-file') {
			item.filePrefix = filePrefix;
			item.extensionId = extensionId;
		}
	});
}


/**
 * Changes the status of a file in the uploadqueue window to "OK"
 * @param { String } [filePrefix] The prefix of the file.
 * @function
 * @name yg_setImportExtensionStatusOK
 */
$K.yg_setImportExtensionStatusOK = function( filePrefix, uploadWinId ) {
	$(uploadWinId+'_innercontent').select('li').each(function(item){
		if (item.filePrefix == filePrefix) {
			item.removeClassName('uploadprocessing');
			item.addClassName('uploaddone');
			item.down('.uploadpercent').update('OK');
		}
	});
}

/**
 * Changes the status of a file in the uploadqueue window to "ERROR"
 * @param { String } [filePrefix] The prefix of the file.
 * @param { String } [uploadWinId] The id of the upload progress window.
 * @param { String } [errorMessage] The errormessage from the extension.
 * @function
 * @name yg_setImportExtensionStatusERROR
 */
$K.yg_setImportExtensionStatusERROR = function( filePrefix, uploadWinId, errorMessage ) {
	$(uploadWinId+'_innercontent').select('li').each(function(item){
		if (item.filePrefix == filePrefix) {
			item.removeClassName('uploadprocessing');
			item.addClassName('uploaderror');
			item.down('.uploadpercent').update('ERROR');
			item.observe('mouseover', function(ev) {
				$K.yg_showHelp( errorMessage );
			} );
			item.observe('mouseout', function(ev) {
				$K.yg_showHelp(false);
			} );
		}
	});
}
