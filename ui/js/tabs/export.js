/**
* Initializes the export window
* @param { String } [winID] The window-id
* @param { String } [openerRef] The reference to the opener
* @function
* @name $K.yg_initDlgExport
*/
$K.yg_initDlgExport = function( winID, openerRef ) {

	var mode = $K.windows['wid_'+winID].loadparams['mode'];
	var openerWinRef = $($K.windows['wid_'+winID].loadparams['opener_reference']);
	var uploadStatusPanel = $('wid_'+winID).down('.mk_uploadpanel');
	var uploadTemplateOrig = $('wid_'+winID).down('.mk_upload_templates').innerHTML;
	
	switch (mode) {
		case 'import':
			if ($K.uploadFramework == 'swfuploader') {
				for (swfUploadObjectIdx in $K.yg_SWFUploadObjects) {
					if (swfUploadObjectIdx.startsWith(openerWinRef.id)) {
						var currSWFUploadObject = $K.yg_SWFUploadObjects[swfUploadObjectIdx];
						
						currSWFUploadObject.customSettings.uploadWinId = 'wid_'+winID;
						
						// Add entry to global view window
						var swfUploadFile = currSWFUploadObject.getFile( '!!!YG-ID!!!' );
						
						if (swfUploadFile) {
							uploadTemplate = uploadTemplateOrig;
							uploadTemplate = uploadTemplate.replace(/__TEMPLATEID__/g, swfUploadFile.id);		// Replace ID
							uploadTemplate = uploadTemplate.replace(/__TEMPLATEINDEX__/g, swfUploadFile.index);	// Replace Index
							uploadTemplate = uploadTemplate.replace(/__TEMPLATENAME__/g, swfUploadFile.name);	// Replace Name

							uploadStatusPanel.insert({ bottom: uploadTemplate });
							currSWFUploadObject.addFileParam(swfUploadFile.id, 'uploadWinId', winID );
							currSWFUploadObject.addFileParam(swfUploadFile.id, 'extensionId', $K.windows[openerWinRef.id].yg_id );
							
							// Add to global file object container
							$K.yg_SWFUploadGlobalFileInfo[swfUploadFile.id] = swfUploadFile;

							$K.windows['wid_'+winID].refresh();

							if (!$K.yg_SWFUploadQueueProcessing) {
								$K.yg_SWFUploadProcessQueue();
							}
							
							currSWFUploadObject.startUpload();
						}
					}
				}
			}

			if ($K.uploadFramework == 'plupload') {
				for (plUploadObjectIdx in $K.yg_PLUploadObjects) {
					if (plUploadObjectIdx.startsWith(openerWinRef.id)) {
						var currPLUploadObject = $K.yg_PLUploadObjects[plUploadObjectIdx];
						
						currPLUploadObject.settings.custom_settings.uploadWinId = 'wid_'+winID;
						
						// Add entry to global view window
						var plUploadFile = currPLUploadObject.files[0];
						
						if (plUploadFile) {
							uploadTemplate = uploadTemplateOrig;
							uploadTemplate = uploadTemplate.replace(/__TEMPLATEINDEX__/g, currPLUploadObject.files.length+1);	// Replace Index
							uploadTemplate = uploadTemplate.replace(/__TEMPLATEID__/g, plUploadFile.id);		// Replace ID
							uploadTemplate = uploadTemplate.replace(/__TEMPLATENAME__/g, plUploadFile.name);	// Replace Name

							uploadStatusPanel.insert({ bottom: uploadTemplate });
							
							var addFileParam = Object.toQueryString({
								uploadWinId: winID,
								extensionId: $K.windows[openerWinRef.id].yg_id
							});
							plUploadFile.addFileParam += '&'+addFileParam;
							
							// Add to global file object container
							$K.yg_PLUploadGlobalFileInfo[plUploadFile.id] = plUploadFile;

							$K.windows['wid_'+winID].refresh();

							if (!$K.yg_PLUploadQueueProcessing) {
								$K.yg_PLUploadProcessQueue();
							}
							
							currPLUploadObject.start();
						}
					}
				}
			}
			break;
		case 'export':
			// Invoke Export
			var extensionId = $K.windows[openerWinRef.id].yg_id.split('-')[0];
			var data = Array ( 'noevent', {yg_property: 'extensionExportData', params: {
				extensionId: extensionId,
				uploadWinId: winID,
				openerWinId: openerWinRef.id
			} } );
			$K.yg_AjaxCallback( data, 'extensionExportData' );
			break;
	}

}


/**
* Starts the export process of an extension
* @param { String } [winID] Id of the export window
* @param { String } [fileName] The filename of the exported file
* @param { String } [mimeType] The mime-type of the exported file
*/
$K.yg_addExtensionExportFile = function(winID, extensionId, fileIndex, fileName, mimeType) {

	var winRef = $('wid_'+winID);
	var uploadStatusPanel = winRef.down('.mk_uploadpanel');
	var uploadTemplateOrig = winRef.down('.mk_upload_templates').innerHTML;
	
	if (fileIndex == 1) {
		uploadStatusPanel.update('');
	}
	
	uploadTemplate = uploadTemplateOrig;
	uploadTemplate = uploadTemplate.replace(/__TEMPLATEID__/g, fileIndex);		// Replace ID
	uploadTemplate = uploadTemplate.replace(/__TEMPLATEINDEX__/g, fileIndex);	// Replace Index
	uploadTemplate = uploadTemplate.replace(/__TEMPLATENAME__/g, fileName);		// Replace Name

	uploadStatusPanel.insert({ bottom: uploadTemplate });
	
	var downloadFunc = function(ev) {
		Event.stop(ev);
		window.open( $K.appdir+'fetchExport?extensionId='+extensionId+'&fileName='+fileName+'&mimeType='+mimeType );
	};

	$('item_'+winID+'_'+fileIndex).down('a.download').observe('click', downloadFunc);
	$('item_'+winID+'_'+fileIndex).observe('click', downloadFunc);
	
	$K.windows[winRef.id].refresh();
}
