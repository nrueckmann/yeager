/**
 * Initializes the upload dialog
 * @param { String } [winID] The window id
 * @param { String } [preselected] The upload to preselect
 * @function
 * @name $K.yg_initDlgUpload
 */
$K.yg_initDlgUpload = function( winID, preselected ) {

	// Add features for SWFUpload and Plupload to top button
	var innerDiv = new Element('div', {id:'wid_'+winID+'_addfilebutton_top'}).update('&nbsp;');
	var outerDiv = new Element('div', {style:'padding-top:5px;'});
	outerDiv.insert({top:innerDiv});
	$('wid_'+winID+'_addfilebutton_top_container').insert({top:outerDiv});

	var innerDiv2 = new Element('div').update('&nbsp;');
	var outerDiv2 = new Element('div', {id:'wid_'+winID+'_addfilebutton_top_template', style:'display:none;'});
	outerDiv2.insert({top:innerDiv2});
	$('wid_'+winID+'_addfilebutton_top_container').insert({after:outerDiv2});

	if (preselected) {
		$K.yg_loadDlgUpload(preselected);
	}
}


/**
 * Initializes the upload progress window
 * @param { String } [preselected] The upload to preselect
 * @function
 * @name $K.yg_initDlgUpload
 */
$K.yg_initDlgUploadProgress = function( show ) {
	if ($K.uploadFramework == 'plupload') {
		$('wid_uploadprogress').addClassName('plupload');
	}
	if (show) {
		$('wid_uploadprogress').show();
	}
}


/**
* Opens the upload dialog
* @param { Boolean } [show] display window after loading
*/
$K.yg_openUploadProgress = function(show) {
	if (!$K.windows['wid_uploadprogress']) {
		new $K.yg_wndobj({ config: 'UPLOAD_PROGRESS', loadparams: { show: show } });
	} else if (show) {
		$('wid_uploadprogress').show();
	}
}


/**
 * Loads the upload dialog
 * @param { String } [yg_id] The yg_id
 * @param { Boolean } [show] TRUE if the window should be shown initially
 * @function
 * @name $K.yg_loadDlgUpload
 */
$K.yg_loadDlgUpload = function(yg_id, show) {

	if (!$K.windows['wid_upload']) {
		new $K.yg_wndobj({config: 'UPLOAD', loadparams: {yg_id: yg_id} });
		$K.yg_openUploadProgress();
		if (show) {
			$K.windows['wid_upload'].initShow = true;
		}
		return;
	}

	if (!yg_id) {
		return;
	}

	$K.windows['wid_upload'].yg_id = yg_id.split('-')[0];;

	// Show global upload window
	$('wid_upload').show();
	$('wid_upload').style.zIndex = $K.yg_incrementTopZIndex() + 10;
	$K.windows['wid_upload'].init();
	$K.yg_centerWindow($('wid_upload'));
	$('wid_upload_filetitle').value = '';
	$K.yg_dropdownSelect($('wid_upload_filetype'), false, 'automatic', true);
	$('wid_upload_filestoupload').update('');

	if ($K.uploadFramework == 'swfuploader') {
		/* Update swfupload settings */
		var swfuploadSettings = Object.clone( $K.yg_SWFUploadSettings );
		Object.extend(swfuploadSettings, {
			upload_url: $K.appdir+'responder?handler=uploadFile',
			flash_url: $K.jsdir + '3rd/swfupload/swfupload.swf',
			button_placeholder_id: 'wid_upload_addfilebutton',
			custom_settings: {
				uploadButtonId: 'wid_upload_okbutton',
				statusPanelTemplate: 'wid_upload_filestoupload_template',
				uploadTitle: 'wid_upload_filetitle',
				uploadType: 'wid_upload_filetype'
			}
		} );

		var swfuploadSettings2 = Object.clone( $K.yg_SWFUploadSettings );
		Object.extend(swfuploadSettings2, {
			upload_url: $K.appdir+'responder?handler=uploadFile',
			flash_url: $K.jsdir + '3rd/swfupload/swfupload.swf',
			button_placeholder_id: 'wid_upload_addfilebutton_top',
			button_width: "23",
			button_height: "23",
			custom_settings: {
				uploadButtonId: 'wid_upload_okbutton',
				statusPanelTemplate: 'wid_upload_filestoupload_template',
				uploadTitle: 'wid_upload_filetitle',
				uploadType: 'wid_upload_filetype'
			}
		} );

		if (!$('wid_upload_addfilebutton')) {
			var tmpTemplate = $('wid_upload_addfilebutton_template');
			tmpTemplate.previous().update( tmpTemplate.innerHTML );
			tmpTemplate.previous().down().id = 'wid_upload_addfilebutton';
			tmpTemplate.previous().down().writeAttribute('id', 'wid_upload_addfilebutton');
		}

		if (!$('wid_upload_addfilebutton_top')) {
			var tmpTemplate = $('wid_upload_addfilebutton_top_template');
			tmpTemplate.previous().down().update( tmpTemplate.innerHTML );
			tmpTemplate.previous().down().down().id = 'wid_upload_addfilebutton_top';
			tmpTemplate.previous().down().down().writeAttribute('id', 'wid_upload_addfilebutton_top');
		}
		$K.yg_SWFUploadObjects['wid_upload'] = new SWFUpload( swfuploadSettings );
		$K.yg_SWFUploadObjects['wid_upload_2'] = new SWFUpload( swfuploadSettings2 );

		if (Prototype.Browser.WebKit || Prototype.Browser.IE && $('wid_upload_UPLOAD').down('div.selectionmarker')) {
			$('wid_upload_UPLOAD').down('div.selectionmarker').remove();
		}
	}

	if ($K.uploadFramework == 'plupload') {
		/* Init Upload Button */
		var customSettings = {
			uploadButtonId: 'wid_upload_okbutton',
			statusPanelTemplate: 'wid_upload_filestoupload_template',
			uploadTitle: 'wid_upload_filetitle',
			uploadType: 'wid_upload_filetype'
		};
		//$K.yg_UploadInit( 'wid_upload_2', 'wid_upload_addfilebutton', null, {handler: 'uploadFile'}, customSettings, [], true );
		$K.yg_UploadInit( 'wid_upload_2', 'wid_upload_addfilebutton', 'wid_upload_ywindowinnerie', {handler: 'uploadFile'}, customSettings, [], true );
		$K.yg_UploadInit( 'wid_upload_3', 'wid_upload_addfilebutton_top', 'wid_upload_addfilebutton_top', {handler: 'uploadFile'}, customSettings, [], true );

		//$('wid_upload_addfilebutton').up().previous.setStyle({zIndex: '2'});
		if (Prototype.Browser.WebKit || Prototype.Browser.IE) {
			$('wid_upload_addfilebutton').up().setStyle({marginTop:'-37px'});
		} else if($(document.body).hasClassName('MAC')) {
			$('wid_upload_addfilebutton').up().setStyle({marginTop:'-39px'});
		} else {
			$('wid_upload_addfilebutton').up().setStyle({marginTop:'-39px'});
		}

		if ( Prototype.Browser.IE &&
			 $('wid_upload_addfilebutton_template').next()  &&
			 ($('wid_upload_addfilebutton_template').next().tagName == 'FORM') ) {
			$('wid_upload_addfilebutton_template').next().setStyle({top:'0px', width:'100%', height:'57px'});
		}
		if ( Prototype.Browser.IE &&
			 (($K.yg_PLUploadObjects['wid_upload_2'].runtime == 'html4') || ($K.yg_PLUploadObjects['wid_upload_2'].runtime == 'html5')) ) {
			if ($('wid_upload_addfilebutton').previous().down('.selectionmarker')) {
				$('wid_upload_addfilebutton').previous().down('.selectionmarker').remove();
			}
			if ($('wid_upload_cnttable').down('form') && $('wid_upload_cnttable').down('form').down('form')) {
				$('wid_upload_cnttable').down('form').down('form').setStyle({top:'0px', width:'100%', height:'57px'});
			}
			// For IE10
			if ($('wid_upload_cnttable').down('div.plupload.html5')) {
				$('wid_upload_cnttable').down('div.plupload.html5').setStyle({top:'0px', width:'100%', height:'57px'});
			}
		}
	}
}


/**
 * Removes an item from the upload-queue
 * @param { Element } [which] The element to remove
 * @function
 * @name $K.yg_removeUploadItem
 */
$K.yg_removeUploadItem = function(which) {
	which = $(which);
	var file_id = which.up('li').readAttribute('yg_id').replace(/-file/, '');
	var winID = which.up('.ywindow').id;

	if ($K.uploadFramework == 'swfuploader') {
		// Look for original SWFUpload instance
		for (SWFUploadInstance in $K.yg_SWFUploadObjects) {
			if (file_id.indexOf($K.yg_SWFUploadObjects[SWFUploadInstance].movieName)==0) {
				try {
					$K.yg_SWFUploadObjects[SWFUploadInstance].cancelUpload(file_id, true);
				} catch(ex) { }
			}
		}
	}
	if ($K.uploadFramework == 'plupload') {
		// Look for original SWFUpload instance
		for (PLUploadInstance in $K.yg_PLUploadObjects) {
			if ($K.yg_PLUploadObjects[PLUploadInstance].files.length > 0) {
				for (var i=0;i<$K.yg_PLUploadObjects[PLUploadInstance].files.length;i++) {
					//$K.yg_PLUploadObjects[PLUploadInstance].stop();
					$K.yg_PLUploadObjects[PLUploadInstance].removeFile($K.yg_PLUploadObjects[PLUploadInstance].files[i]);
					//$K.yg_PLUploadObjects[PLUploadInstance].start();
				}
			}
		}
	}

	// Remove from progress window
	which.up('li').remove();

	if ($K.uploadFramework == 'swfuploader') {
		// Remove from global files object
		$K.yg_SWFUploadGlobalFileInfoRemove(file_id);
	}
	$K.windows[winID].refresh();
}


/**
** Removes a file from the global uploadinfo object
**/
$K.yg_SWFUploadGlobalFileInfoRemove = function(which) {
	for (FileInfoItemIdx in $K.yg_SWFUploadGlobalFileInfo) {
		if ($K.yg_SWFUploadGlobalFileInfo[FileInfoItemIdx].id == which) {
			delete $K.yg_SWFUploadGlobalFileInfo[FileInfoItemIdx];
		}
	}
}


/**
** Gets the acuumulated filesizes for all uploaditems
**/
$K.yg_calcSWFUploadGlobalFilesizes = function() {
	// Calc size of all files
	var globalSize = 0;
	for (FileInfoIdx in $K.yg_SWFUploadGlobalFileInfo) {
		globalSize += $K.yg_SWFUploadGlobalFileInfo[FileInfoIdx].size;
	}
	return globalSize;
}

/**
 * Handles upload timeouts
 * @function
 * @name $K.yg_onUploadTimeout
 */
$K.yg_onUploadTimeout = function () {
	var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
	uploadStatusPanel.select('li').each(function(item){
		// Set fileprocess to "error" and disable cancel for that element
		item.removeClassName('uploading');
		item.addClassName('uploaderror');
		item.down('.uploadpercent').update('ERROR');
		item.observe('mouseover', function() { $K.yg_showHelp( $K.TXT('TXT_FILE_UPLOAD_TIMEOUT') ); });
		item.observe('mouseout', function() { $K.yg_showHelp( false ); });
		window.hadUploadError = false;
		window.hadUploadErrorMsg = undefined;
	});
}

/**
 * Starts an upload
 * @param { Element } [winID] The window id
 * @function
 * @name $K.yg_startUpload
 */
$K.yg_startUpload = function(winID) {
	// Do not close windows, only move it to top and left
	$('wid_'+winID).setStyle({top: '-10000px', left: '-10000px'});

	// Show global upload window
	$('wid_uploadprogress').show();
	$('wid_uploadprogress').style.zIndex = $K.yg_incrementTopZIndex();
	$K.windows['wid_uploadprogress'].init();

	if ($K.uploadFramework == 'swfuploader') {

		var uploadStats;

		if ($K.yg_SWFUploadObjects['wid_upload']) {
			uploadStats = $K.yg_SWFUploadObjects['wid_upload'].getStats();
		}
		if ($K.yg_SWFUploadObjects['wid_upload_2'] && (!uploadStats || (uploadStats.files_queued == 0)) ) {
			uploadStats = $K.yg_SWFUploadObjects['wid_upload_2'].getStats();
		}

 		var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
 		var uploadTemplateOrig = $('wid_uploadprogress').down('.mk_upload_templates').innerHTML;

 		pageID = $K.windows['wid_upload'].yg_id;

		// Add all entries to global view window
		$('wid_'+winID).down('.mk_uploadpanel').select('li').each(function(item){

			var swfUploadFile = $K.yg_SWFUploadObjects['wid_upload'].getFile( item.readAttribute('yg_id').replace(/-file/,'') );
			if (!swfUploadFile) {
				swfUploadFile = $K.yg_SWFUploadObjects['wid_upload_2'].getFile( item.readAttribute('yg_id').replace(/-file/,'') );
			}

			// Add post parameters to file
			var data = Array ( 'noevent', {yg_property: 'uploadFile', params: {
				folderId: pageID,
				uploadID: swfUploadFile.id,
				uploadIndex: swfUploadFile.id,
				filesQueued: uploadStats.files_queued,
				title: $('wid_' + winID + '_filetitle').value,
				type: $('wid_' + winID + '_filetype').down('input[type=hidden]').value
			} } );
			$K.yg_SWFUploadObjects['wid_upload'].addFileParam(swfUploadFile.id, 'data', Object.toJSON(data) );
			if ($K.yg_SWFUploadObjects['wid_upload_2']) {
				$K.yg_SWFUploadObjects['wid_upload_2'].addFileParam(swfUploadFile.id, 'data', Object.toJSON(data) );
			}

			uploadTemplate = uploadTemplateOrig;
			uploadTemplate = uploadTemplate.replace(/__TEMPLATEID__/g, swfUploadFile.id);		// Replace ID
			uploadTemplate = uploadTemplate.replace(/__TEMPLATEINDEX__/g, swfUploadFile.index);	// Replace Index
			uploadTemplate = uploadTemplate.replace(/__TEMPLATENAME__/g, swfUploadFile.name);	// Replace Name

			uploadStatusPanel.insert({ bottom: uploadTemplate });

			// Add to global file object container
			$K.yg_SWFUploadGlobalFileInfo[swfUploadFile.id] = swfUploadFile;

		});

		$K.windows['wid_uploadprogress'].refresh();

		$K.yg_SWFUploadQueue['wid_'+winID] = $K.yg_SWFUploadObjects['wid_upload'].movieName;

		if ($K.yg_SWFUploadObjects['wid_upload_2']) {
			$K.yg_SWFUploadQueue['wid_'+winID+'_2'] = $K.yg_SWFUploadObjects['wid_upload_2'].movieName;
		}

		if (!$K.yg_SWFUploadQueueProcessing) {
			$K.yg_SWFUploadProcessQueue();
		}
	}

	if ($K.uploadFramework == 'plupload') {
 		var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
 		var uploadTemplateOrig = $('wid_uploadprogress').down('.mk_upload_templates').innerHTML;

 		var uploadObject = $K.yg_PLUploadObjects['wid_'+winID];
 		var uploadObject2 = $K.yg_PLUploadObjects['wid_'+winID+'_2'];
 		var uploadObject3 = $K.yg_PLUploadObjects['wid_'+winID+'_3'];
		if (uploadObject) {
			var fileIndex = uploadObject.files.length;
		} else if (uploadObject2) {
			var fileIndex = uploadObject2.files.length;
		} else if (uploadObject3) {
			var fileIndex = uploadObject3.files.length;
		}

		pageID = $K.windows['wid_upload'].yg_id;

		// Add all entries to global view window
		$('wid_'+winID+'_innercontent').down('.mk_uploadpanel').select('li').each(function(item){

			fileIndex++;

			var uploadFile, filesQueued;
			if (uploadObject) {
				uploadFile = uploadObject.getFile( item.readAttribute('yg_id').replace(/-file/,'') );
				filesQueued = uploadObject.files.length + 1;
			}
			if (!uploadFile && uploadObject2) {
				uploadFile = uploadObject2.getFile( item.readAttribute('yg_id').replace(/-file/,'') );
				filesQueued = uploadObject2.files.length + 1;
			}
			if (!uploadFile && uploadObject3) {
				uploadFile = uploadObject3.getFile( item.readAttribute('yg_id').replace(/-file/,'') );
				filesQueued = uploadObject3.files.length + 1;
			}
			if (!uploadFile) {
				return;
			}

			// Add post parameters to file
			var data = Array ( 'noevent', {yg_property: 'uploadFile', params: {
				folderId: pageID,
				uploadID: uploadFile.id,
				uploadIndex: fileIndex,
				filesQueued: filesQueued,
				title: $('wid_' + winID + '_filetitle').value,
				type: $('wid_' + winID + '_filetype').down('input[type=hidden]').value
			} } );
			var addFileParam = Object.toQueryString({ data: Object.toJSON(data) });
			uploadFile.addFileParam = addFileParam;

			uploadTemplate = uploadTemplateOrig;
			uploadTemplate = uploadTemplate.replace(/__TEMPLATEINDEX__/g, fileIndex);		// Replace Index
			uploadTemplate = uploadTemplate.replace(/__TEMPLATEID__/g, uploadFile.id);		// Replace ID
			uploadTemplate = uploadTemplate.replace(/__TEMPLATENAME__/g, uploadFile.name);	// Replace Name

			uploadStatusPanel.insert({ bottom: uploadTemplate });

			// Add to global file object container
			$K.yg_PLUploadGlobalFileInfo[uploadFile.id] = uploadFile;

		});

		$K.windows['wid_uploadprogress'].refresh();

		if (uploadObject) $K.yg_PLUploadQueue['wid_'+winID] = uploadObject.id;
		if (uploadObject2) $K.yg_PLUploadQueue['wid_'+winID+'_2'] = uploadObject2.id;
		if (uploadObject3) $K.yg_PLUploadQueue['wid_'+winID+'_3'] = uploadObject3.id;
		if (!$K.yg_PLUploadQueueProcessing) {
			$K.yg_PLUploadProcessQueue();
		}
	}
}


/**
 * Clears the upload progress
 * @param { Element } [winID] The window id
 * @function
 * @name $K.yg_clearUploadProgress
 */
$K.yg_clearUploadProgress = function(winID) {
	$('wid_'+winID).down('.mk_uploadpanel').select('li').each(function(item){
		if ( item.hasClassName('uploaddone') || item.hasClassName('uploaderror') ) {
			item.remove();
		}
	});
	$K.windows['wid_'+winID].refresh();
}
