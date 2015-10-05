	$K.yg_PLUploadQueue = {};
	$K.yg_PLUploadGlobalFileInfo = {};
	$K.yg_PLUploadQueueProcessing = false;
	$K.yg_PLUploadGlobalDone = 0;

	$K.yg_PLUploadSettings = {
		chunk_size : $K.maxUploadSize,
		runtimes: 'html5,html4',
		browse_button: '__REPLACEME__',
		container: '__REPLACEME__',
		drop_element: '__REPLACEME__',
		max_file_size: '10000mb',
		multipart: true,
		multi_selection: true,
		url: $K.appdir + 'responder?handler=uploadFile&flashcookie=' + document.cookie,
		flash_swf_url: $K.jsdir + '3rd/plupload/js/plupload.flash.swf',
		silverlight_xap_url: $K.jsdir + '3rd/plupload/js/plupload.silverlight.xap',
		filters: [],
		dragdrop: true,
		custom_settings: {
			uploadButtonId: '__REPLACEME__',
			statusPanelTemplate: '__REPLACEME__'
		}
	};

	$K.yg_UploadInit = function( uploadID, panelID, panelWrapperID, additionalUploadParams, customSettings, filters, multiSelect, force ) {
		if (!$(panelID)) return;

		var panel = $(panelID);
		if ($K.yg_PLUploadObjects[uploadID] && !force) {
			var pluploadSettings = Object.clone( $K.yg_PLUploadObjects[uploadID].settings );
		} else {
			var pluploadSettings = Object.clone( $K.yg_PLUploadSettings );
		}

		var panelID = panel.up().identify();
		if (!panelWrapperID) {
			panelWrapperID = panel.up().up().identify();
		}
		var uploadParams = { flashcookie: document.cookie };
		Object.extend(uploadParams, additionalUploadParams);
		$(panelWrapperID).addClassName('mk_uploadpanel');

		// Generate form element to wrap the container (and map its ID into the settings-object)
		if ($(panelWrapperID).up(1).tagName != 'FORM') {
			var formID = $(panelWrapperID).up(0).wrap('form', {
				method:'post',
				enctype: 'multipart/form-data'
			}).identify();
			$(formID).name = formID;
			$(formID).writeAttribute('name', formID);
		} else {
			var formID = $(panelWrapperID).up(1).identify();
		}

		pluploadSettings.form = formID;

		Object.extend(pluploadSettings, {
			url: $K.appdir + 'responder?' + Object.toQueryString(uploadParams),
			baseUrl: $K.appdir + 'responder?' + Object.toQueryString(uploadParams),
			browse_button: panelID,
			container: panelWrapperID,
			drop_element: panelWrapperID,
			multi_selection: multiSelect,
			filters: filters,
			custom_settings: customSettings
		} );

		if ($K.yg_PLUploadObjects[uploadID] && !force) {
			$K.yg_PLUploadObjects[uploadID].settings = pluploadSettings;
		} else {
			$K.yg_PLUploadObjects[uploadID] = new plupload.Uploader( pluploadSettings );

			$K.yg_PLUploadMapEventHandlers( $K.yg_PLUploadObjects[uploadID] );
			try {
				$K.yg_PLUploadObjects[uploadID].init();

				// Disable upload timeouts when using the html4 runtime
				if ($K.yg_PLUploadObjects[uploadID].runtime == 'html4') {
					$K.yg_onUploadTimeout = Prototype.emptyFunction;
				}
			}
			catch(ex){
				// console.warn('EXCEPTION:', ex);
			}
		}
	}

	$K.yg_PLUploadProcessQueue = function() {
		$K.yg_PLUploadQueueProcessing = true;

		var rest = 0;
		for (uploadObject in $K.yg_PLUploadQueue) {
			rest++;
			if (!$K.yg_PLUploadObjects[uploadObject].yg_started && ($K.yg_PLUploadObjects[uploadObject].files.length > 0)) {
				$K.yg_PLUploadObjects[uploadObject].start();
				$K.yg_PLUploadObjects[uploadObject].yg_started = true;
			}
			delete $K.yg_PLUploadQueue[uploadObject];
			continue;
		}
	}

	$K.yg_PLUploadEvUploadComplete = function(up, files) {
		var uploadObjectsRest = 0;
		for (currUploadObject in $K.yg_PLUploadObjects) {
			uploadObjectsRest++;
		}

		// Check if global upload is complete
		for (currUploadObject in $K.yg_PLUploadObjects) {
			var fileRest = $K.yg_PLUploadObjects[currUploadObject].files.length;
			var filesToRemove = new Array();

			for(var j=0; j<$K.yg_PLUploadObjects[currUploadObject].files.length; j++) {
				if ($K.yg_PLUploadObjects[currUploadObject].files[j].status == plupload.DONE) {
					filesToRemove.push($K.yg_PLUploadObjects[currUploadObject].files[j]);
					fileRest--;
				}
			}
			filesToRemove.each(function(item) {
				$K.yg_PLUploadObjects[currUploadObject].removeFile(item);
			});
			if (fileRest == 0) {
				uploadObjectsRest--;
				$K.yg_PLUploadObjects[currUploadObject].yg_started = false;
			}
		}

		if (uploadObjectsRest==0) {
			// Set to 100%
			//$K.windows['wid_uploadprogress'].setCaption( $K.TXT('TXT_UPLOAD_STATUS') + ' <strong>100 %</strong>' );

			// Reset global arrays
			$K.yg_PLUploadQueue = {};
			$K.yg_PLUploadGlobalFileInfo = {};
			$K.yg_PLUploadQueueProcessing = false;
			$K.yg_PLUploadGlobalDone = 0;
		}
	}

	$K.yg_PLUploadMapEventHandlers = function( pluploadObject ) {
		pluploadObject.bind('Init', $K.yg_PLUploadEvInit);
		pluploadObject.bind('FilesAdded', $K.yg_PLUploadEvFilesAdded);
		pluploadObject.bind('UploadProgress', $K.yg_PLUploadEvUploadProgress);
		pluploadObject.bind('QueueChanged', $K.yg_PLUploadEvQueueChanged);
		pluploadObject.bind('FileUploaded', $K.yg_PLUploadEvFileUploaded);
		pluploadObject.bind('UploadFile', $K.yg_PLUploadEvUploadFile);
		pluploadObject.bind('UploadComplete', $K.yg_PLUploadEvUploadComplete);

		pluploadObject.bind('Error', function(up, err) {
			var file = err.file, message;
			if (file) {
				message = err.message;
				if (err.details) {
					message += " (" + err.details + ")";
				}
				// console.warn( message );
			}
		});

	}

	/*******************
	/* Callback Events *
	/*******************/
	// Init
	$K.yg_PLUploadEvInit = function(up, params) {
		up.runtime = params.runtime;
	}

	// UploadFile
	$K.yg_PLUploadEvUploadFile = function(up, file) {
		// console.warn('$K.yg_PLUploadEvUploadFile', up, file);

		var fileInfo = $K.yg_PLUploadGlobalFileInfo[file.id];

		// Attach additional parameters to URL
		up.settings.url = up.settings.baseUrl
		if (fileInfo.addFileParam) {
			up.settings.url = up.settings.baseUrl + '&' + fileInfo.addFileParam;
		}

		// Start timeout timer
		if (typeof $('wid_uploadprogress').timeoutId != 'object') {
			$('wid_uploadprogress').timeoutId = {};
		}
		$('wid_uploadprogress').timeoutId[file.id] = window.setTimeout($K.yg_onUploadTimeout, $K.uploadTimeout);
	}

	// QueueChanged
	$K.yg_PLUploadEvQueueChanged = function(up) {
		// console.warn('$K.yg_PLUploadEvQueueChanged', up);

		if (up.settings.custom_settings.autoUpload) {
			var needToStart = false;
			for (file in $K.yg_PLUploadGlobalFileInfo) {
				var currFile = $K.yg_PLUploadGlobalFileInfo[file];
				if (!currFile.processed) {
					needToStart = true;
					currFile.processed = true;
				}
			}
			if (needToStart) {
				up.start();
			}
		}
	}

	// FilesAdded
	$K.yg_PLUploadEvFilesAdded = function(up, files) {
		// console.warn('$K.yg_PLUploadEvFilesAdded', up, files);

		if (!up.settings.multi_selection) {
			up.removeFile(up.files[0]);
		}

		var fileIndex = up.files.length;

		if (!up.settings.multi_selection && (files.length > 1)) {
			var tmp = files[0];
			files = [tmp];
		}

		files.each(function(file) {

			if (file.size > up.settings.max_file_size) {
				Koala.yg_promptbox($K.TXT('TXT_ERROR'), $K.TXT('TXT_FILE_UPLOAD_TOO_BIG'), 'alert');
				return;
			}

			fileIndex++;

			if (up.settings.custom_settings.extensionDataUpload) {
				// Update title in uploadpanel
				$(up.id+'_'+up.runtime).up('.cntblockcontainer').down('.title_txt').update( file.name );

				// Add post parameters to file
				var data = Array ( 'noevent', {yg_property: 'uploadFile', params: {
					fileID: up.settings.custom_settings.fileID,
					uploadID: file.id,
					filesQueued: up.files.length + 1,
					title: up.settings.custom_settings.uploadTitle,
					type: up.settings.custom_settings.uploadType,
					winID: up.settings.custom_settings.winID,
					timestamp: up.settings.custom_settings.timestamp
				} } );
				var addFileParam = Object.toQueryString({ data: Object.toJSON(data) });
				file.addFileParam = addFileParam;

				// Add to global file object container
				$K.yg_PLUploadGlobalFileInfo[file.id] = file;

				if (!$K.yg_PLUploadQueueProcessing) {
					$K.yg_PLUploadProcessQueue();
				}
			} else if (up.settings.custom_settings.autoUpload) {
				// Show global upload window
				$('wid_uploadprogress').show();
				$('wid_uploadprogress').style.zIndex = $K.yg_incrementTopZIndex();
				$K.windows['wid_uploadprogress'].init();

		 		var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
		 		var uploadTemplateOrig = $('wid_uploadprogress').down('.mk_upload_templates').innerHTML;

				// Add post parameters to file
				var data = Array ( 'noevent', {yg_property: 'uploadFile', params: {
					fileID: up.settings.custom_settings.fileID,
					userID: up.settings.custom_settings.userID,
					uploadID: file.id,
					filesQueued: up.files.length + 1,
					title: up.settings.custom_settings.uploadTitle,
					type: up.settings.custom_settings.uploadType,
					winID: up.settings.custom_settings.winID,
					timestamp: up.settings.custom_settings.timestamp
				} } );
				var addFileParam = Object.toQueryString({ data: Object.toJSON(data) });
				file.addFileParam = addFileParam;

				if ($('wid_'+up.settings.custom_settings.winID+'_previewchanged') && (up.settings.custom_settings.uploadType == 'templatePreview')) {
					$('wid_'+up.settings.custom_settings.winID+'_previewchanged').value = 'true';
				}
				if ($('wid_'+up.settings.custom_settings.winID+'_templatechanged') && (up.settings.custom_settings.uploadType == 'template')) {
					$('wid_'+up.settings.custom_settings.winID+'_templatechanged').value = 'true';
				}

				uploadTemplate = uploadTemplateOrig;
				uploadTemplate = uploadTemplate.replace(/__TEMPLATEINDEX__/g, fileIndex);	// Replace Index
				uploadTemplate = uploadTemplate.replace(/__TEMPLATEID__/g, file.id);		// Replace ID
				uploadTemplate = uploadTemplate.replace(/__TEMPLATENAME__/g, file.name);	// Replace Name

				uploadStatusPanel.insert({ bottom: uploadTemplate });

				// Add to global file object container
				$K.yg_PLUploadGlobalFileInfo[file.id] = file;

				$K.windows['wid_uploadprogress'].refresh();

				if (!$K.yg_PLUploadQueueProcessing) {
					$K.yg_PLUploadProcessQueue();
				}
			} else {
				// Show global upload window (if not already shown)
				$('wid_upload').show();
				$('wid_upload').style.zIndex = $K.yg_incrementTopZIndex();
				$K.windows['wid_upload'].init();
				$K.yg_centerWindow('wid_upload');

				// Add to global file object container
				$K.yg_PLUploadGlobalFileInfo[file.id] = file;

				var queueEntryTemplate = $(up.settings.custom_settings.statusPanelTemplate).innerHTML;
				queueEntryTemplate = queueEntryTemplate.replace(/__TEMPLATEINDEX__/g, fileIndex);	// Replace Index
				queueEntryTemplate = queueEntryTemplate.replace(/__TEMPLATEID__/g, file.id);		// Replace ID
				queueEntryTemplate = queueEntryTemplate.replace(/__TEMPLATENAME__/g, file.name);	// Replace Name

				$(up.settings.custom_settings.statusPanelTemplate.replace(/_template/,'')).insert({ bottom: queueEntryTemplate });

				$K.windows[$(up.settings.custom_settings.statusPanelTemplate).up('.ywindow').id].refresh("col1");
			}

		});
	}

	// UploadProgress
	$K.yg_PLUploadEvUploadProgress = function(up, file) {
		// console.warn('$K.yg_PLUploadEvUploadProgress', up, file);

		var currentFile;
		if (up.settings.custom_settings.extensionDataUpload) {
			var uploadStatusPanel = $(up.settings.custom_settings.uploadWinId).down('.mk_uploadpanel');
		} else {
			var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
		}

		uploadStatusPanel.select('li').each(function(item){
			if (item.readAttribute('yg_id').replace(/-file/,'') == file.id) {
				currentFile = item;
			}
		});

		if (isNaN(file.percent) || !isFinite(file.percent)) {
			file.percent = 100;
		}

		if (currentFile &&
			!currentFile.hasClassName('uploadprocessing') &&
			!currentFile.hasClassName('uploaddone') &&
			!currentFile.hasClassName('uploaderror')) {
			currentFile.addClassName('uploading');
			currentFile.down('.uploadpercent').update(file.percent+'%');
			currentFile.down('.uploadstatusbg').down().setStyle({width:file.percent+'%'} );
		}

		if ($K.windows['wid_uploadprogress']) $K.windows['wid_uploadprogress'].setCaption( $K.TXT('TXT_UPLOAD_STATUS') + ' <strong>' + up.total.percent + ' %</strong>' );

		// Restart timeout handler if needed
		if ( (typeof $('wid_uploadprogress').timeoutId == 'object') &&
			 ($('wid_uploadprogress').timeoutId[file.id]) ) {
			window.clearTimeout($('wid_uploadprogress').timeoutId[file.id]);
			$('wid_uploadprogress').timeoutId[file.id] = window.setTimeout($K.yg_onUploadTimeout, $K.uploadTimeout);
		}
	}

	// FileUploaded
	$K.yg_PLUploadEvFileUploaded = function(up, file, response) {
		// console.warn('$K.yg_PLUploadEvFileUploaded', up, file, response);

		var currentFile;
		if (up.settings.custom_settings.extensionDataUpload) {
			var uploadStatusPanel = $(up.settings.custom_settings.uploadWinId).down('.mk_uploadpanel');
			var backendFunction = 'processImportExtensionData';
		} else if (up.settings.custom_settings.userProfilePictureUpload) {
			var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
			var backendFunction = 'processUserProfilePicture';
		} else {
			var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
			var backendFunction = 'processUpload';
		}
		var uploadWinId = uploadStatusPanel.up('.ywindow').id;
		var openerWinId = up.settings.custom_settings.winID;

		uploadStatusPanel.select('li').each(function(item){
			if (item.readAttribute('yg_id').replace(/-file/,'') == file.id) {
				currentFile = item;
			}
		});

		var serverData = response.response;

		if (serverData && currentFile) {
			if (serverData.startsWith('<pre>')) {
				serverData = serverData.stripTags().unescapeHTML();
			}
			serverData.evalScripts();

			if (window.hadUploadError === true) {
				// Set fileprocess to "error" and disable cancel for that element
				currentFile.removeClassName('uploading');
				currentFile.addClassName('uploaderror');
				currentFile.down('.uploadpercent').update('ERROR');
				if (window.hadUploadErrorMsg) {
					var errorMessage = window.hadUploadErrorMsg;
					currentFile.observe('mouseover', function() { $K.yg_showHelp( errorMessage ); });
				} else {
					currentFile.observe('mouseover', function() { $K.yg_showHelp( $K.TXT('TXT_FILE_UPLOAD_ERROR') ); });
				}
				currentFile.observe('mouseout', function() { $K.yg_showHelp( false ); });
				window.hadUploadError = false;
				window.hadUploadErrorMsg = undefined;
			} else if (window.noprocessing) {
				// Set fileprocess to "complete" and disable cancel for that element
				currentFile.removeClassName('uploading');
				currentFile.addClassName('uploaddone');
				currentFile.down('.uploadpercent').update('OK');
				window.noprocessing = false;
			} else {
				// Set fileprocess to "complete" and disable cancel for that element
				currentFile.removeClassName('uploading');
				currentFile.addClassName('uploadprocessing');
				currentFile.down('.uploadpercent').update('<div class="uploadprocessing"><!-- //--></div>');
				currentFile.srcWindow = up.settings.custom_settings.winID;
				currentFile.userID = up.settings.custom_settings.userID;

				// Check if last file
				var lastFile = false;
				var uploadingFiles = currentFile.up('ul').select('li.uploading');
				if (uploadingFiles.length == 0) {
					lastFile = true;
					// Set to 100% if in IE8
					if (Prototype.Browser.IE && (BrowserDetect.version == 8)) {
						$K.windows['wid_uploadprogress'].setCaption( $K.TXT('TXT_UPLOAD_STATUS') + ' <strong>100 %</strong>' );
					}
				}

				uploadStatusPanel.select('li').each(function(item){
					if (item.readAttribute('yg_id') == file.id+'-file') {
						// Start processing
						var data = Array ( 'noevent', {yg_property: backendFunction, params: {
							filePrefix: item.filePrefix,
							realFilename: file.name,
							userID: currentFile.userID,
							extensionId: item.extensionId,
							itemID: item.id,
							uploadWinId: uploadWinId,
							openerWinId: openerWinId,
							reUpload: item.reUpload,
							lastFile: lastFile
						} } );
						$K.yg_AjaxCallback( data, backendFunction );
					}
				});
			}

			// Reset timeout handler if needed
			if ( (typeof $('wid_uploadprogress').timeoutId == 'object') &&
				 ($('wid_uploadprogress').timeoutId[file.id]) ) {
				window.clearTimeout($('wid_uploadprogress').timeoutId[file.id]);
				$('wid_uploadprogress').timeoutId[file.id] = undefined;
			}
		}

		if (up.total.queued === 0) {
			var winID = up.settings.custom_settings.uploadButtonId.replace(/_okbutton/,'');

			// Refresh the tab after extensionDataUpload
			if (up.settings.custom_settings.extensionDataUpload) {
				if ($K.windows['wid_'+up.settings.custom_settings.winID]) {
					$K.windows['wid_'+up.settings.custom_settings.winID].tabs.select(0,Koala.windows['wid_'+up.settings.custom_settings.winID].tabs.params);
				}
			}

			// Now close window and finally destroy swfobject
			if (!up.settings.custom_settings.autoUpload && !up.settings.custom_settings.extensionDataUpload) {
				$K.windows[winID].remove();

				if ($K.windows[winID].stayInBackground) {
					$(winID+'_filetitle').value = '';
					$K.yg_dropdownSelect($(winID+'_filetype'), false, 'automatic', true);
					$(winID+'_filestoupload').update('');
				} else {
					delete $K.yg_PLUploadObjects[winID];
					if ($K.yg_PLUploadObjects[winID+'_2']) {
						delete $K.yg_PLUploadObjects[winID+'_2'];
					}
					if ($K.yg_PLUploadObjects[winID+'_3']) {
						delete $K.yg_PLUploadObjects[winID+'_3'];
					}
				}
			}

			// And process next queue
			$K.yg_PLUploadProcessQueue();

			$K.yg_PLUploadQueueProcessing = false;

		} else {
			$K.yg_PLUploadGlobalDone += file.size;

			if (up.settings.custom_settings.extensionDataUpload) {
				var uploadStatusPanel = $(up.settings.custom_settings.uploadWinId).down('.mk_uploadpanel');
			} else {
				var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
			}

			var currentFile;
			uploadStatusPanel.select('li').each(function(item){
				if (item.readAttribute('yg_id').replace(/-file/,'') == file.id) {
					currentFile = item;
				}
			});

			if (currentFile && currentFile.next()) {
				currentFile.next().addClassName('uploading');
			}

			//this.startUpload();
		}

	}
	/***********************
	/* Callback Events End *
	/***********************/
