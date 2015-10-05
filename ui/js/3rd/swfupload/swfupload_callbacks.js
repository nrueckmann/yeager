	$K.yg_SWFUploadQueue = {};
	$K.yg_SWFUploadGlobalFileInfo = {};
	$K.yg_SWFUploadQueueProcessing = false;
	$K.yg_SWFUploadGlobalDone = 0;

	$K.yg_SWFUploadSettings = {
		flash_url: '__REPLACEME__',
		upload_url: $K.appdir+'responder?handler=uploadFile',
		post_params: {
			'flashcookie' : document.cookie
		},
		file_size_limit: $K.maxUploadSize.toUpperCase().replace(/MB/g,' MB'),
		file_types: "*.*",
		file_types_description: "All Files",
		file_upload_limit: 100,
		file_queue_limit: 0,
		custom_settings: {
			uploadButtonId: '__REPLACEME__',
			statusPanelTemplate: '__REPLACEME__'
		},
		debug: false,
		//debug_handler: SWFUploadFirebugHandler,	// Queue plugin event

		// Button settings
		button_cursor: SWFUpload.CURSOR.HAND,
		button_width: "2000",
		button_height: "56",
		button_placeholder_id: "__REPLACEME__",
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_action: SWFUpload.BUTTON_ACTION.SELECT_FILES,
		
		// The event handler functions are defined in handlers.js
		file_queued_handler: fileQueued,
		file_queue_error_handler: fileQueueError,
		file_dialog_complete_handler: fileDialogComplete,
		upload_start_handler: uploadStart,
		upload_progress_handler: uploadProgress,
		upload_error_handler: uploadError,
		upload_success_handler: uploadSuccess,
		upload_complete_handler: uploadComplete,
		queue_complete_handler: queueComplete
	};


	$K.yg_SWFUploadProcessQueue = function() {
		
		$K.yg_SWFUploadQueueProcessing = true;

		var rest = 0;	
		for (uploadObject in $K.yg_SWFUploadQueue) {
			rest++;
			$K.yg_SWFUploadObjects[uploadObject].startUpload();
			delete $K.yg_SWFUploadQueue[uploadObject];
			continue;
		}
		
		// Check if global upload is complete
		if (rest==0) {
			// Set to 100%
			if ($K.windows['wid_uploadprogress']) $K.windows['wid_uploadprogress'].setCaption( $K.TXT('TXT_UPLOAD_STATUS') + ' <strong>100 %</strong>' );
			
			// Reset global arrays
			$K.yg_SWFUploadQueue = {};
			$K.yg_SWFUploadGlobalFileInfo = {};
			$K.yg_SWFUploadQueueProcessing = false;
			$K.yg_SWFUploadGlobalDone = 0;
		}
	}


/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */
function fileQueued(file) {
	try {
		if (!this.customSettings.autoUpload && !this.customSettings.extensionDataUpload) {
			var queueEntryTemplate = $(this.customSettings.statusPanelTemplate).innerHTML;
			queueEntryTemplate = queueEntryTemplate.replace(/__TEMPLATEID__/g, file.id);		// Replace ID
			queueEntryTemplate = queueEntryTemplate.replace(/__TEMPLATEINDEX__/g, file.index);	// Replace Index
			queueEntryTemplate = queueEntryTemplate.replace(/__TEMPLATENAME__/g, file.name);	// Replace Name
			
			$(this.customSettings.statusPanelTemplate.replace(/_template/,'')).insert({ bottom: queueEntryTemplate });
			
			$K.windows[$(this.customSettings.statusPanelTemplate).up('.ywindow').id].refresh("col1");
		}
	} catch (ex) {
		this.debug(ex);
	}

}

function fileQueueError(file, errorCode, message) {
	try {

		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
			return;
		}

		// Set fileprocess to "error" and disable cancel for that element
		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			// progress.setStatus("File is too big.");
			this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			$K.yg_promptbox('', $K.TXT('TXT_UPLOAD_ERROR_FILE_TOO_BIG'), 'alert');
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			// progress.setStatus("Cannot upload Zero Byte files.");
			this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			// progress.setStatus("Invalid File Type.");
			this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		default:
			if (file !== null) {
				// progress.setStatus("Unhandled Error");
			}
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesSelected > 0) {
			if (this.customSettings.extensionDataUpload) {
				
				var uploadStats = swfUploadFile = this.getStats();
				
				// Add entry to global view window
				var swfUploadFile = this.getFile( '!!!YG-ID!!!' );

				// Update title in uploadpanel
				$(this.movieName).up('.cntblockcontainer').down('.title_txt').update( swfUploadFile.name );
				
				// Add post parameters to file
				var data = Array ( 'noevent', {yg_property: 'uploadFile', params: {
					fileID: this.customSettings.fileID, 
					uploadID: swfUploadFile.id,
					filesQueued: uploadStats.files_queued,
					title: this.customSettings.uploadTitle,
					type: this.customSettings.uploadType,
					winID: this.customSettings.winID,
					timestamp: this.customSettings.timestamp
				} } );
				this.addFileParam(swfUploadFile.id, 'data', Object.toJSON(data) );
				
				// Add to global file object container
				$K.yg_SWFUploadGlobalFileInfo[swfUploadFile.id] = swfUploadFile;
		
				if (!$K.yg_SWFUploadQueueProcessing) {
					$K.yg_SWFUploadProcessQueue();
				}

			}
			if (this.customSettings.autoUpload) {
				
				// Show global upload window
				$('wid_uploadprogress').show();
				$('wid_uploadprogress').style.zIndex = $K.yg_incrementTopZIndex();
				$K.windows['wid_uploadprogress'].init();
				
				var uploadStats = swfUploadFile = this.getStats();
				
		 		var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
		 		var uploadTemplateOrig = $('wid_uploadprogress').down('.mk_upload_templates').innerHTML;
				
				// Add entry to global view window
				var swfUploadFile = this.getFile( '!!!YG-ID!!!' );
				
				// Add post parameters to file
				var data = Array ( 'noevent', {yg_property: 'uploadFile', params: {
					fileID: this.customSettings.fileID,
					userID: this.customSettings.userID, 
					uploadID: swfUploadFile.id,
					filesQueued: uploadStats.files_queued,
					title: this.customSettings.uploadTitle,
					type: this.customSettings.uploadType,
					winID: this.customSettings.winID,
					timestamp: this.customSettings.timestamp
				} } );
				this.addFileParam(swfUploadFile.id, 'data', Object.toJSON(data) );
				
				if ($('wid_'+this.customSettings.winID+'_previewchanged') && (this.customSettings.uploadType == 'templatePreview')) {
					$('wid_'+this.customSettings.winID+'_previewchanged').value = 'true';
				}
				if ($('wid_'+this.customSettings.winID+'_templatechanged') && (this.customSettings.uploadType == 'template')) {
					$('wid_'+this.customSettings.winID+'_templatechanged').value = 'true';
				}
				
				uploadTemplate = uploadTemplateOrig;
				uploadTemplate = uploadTemplate.replace(/__TEMPLATEID__/g, swfUploadFile.id);		// Replace ID
				uploadTemplate = uploadTemplate.replace(/__TEMPLATEINDEX__/g, swfUploadFile.index);	// Replace Index
				uploadTemplate = uploadTemplate.replace(/__TEMPLATENAME__/g, swfUploadFile.name);	// Replace Name
	
				uploadStatusPanel.insert({ bottom: uploadTemplate });
				
				// Add to global file object container
				$K.yg_SWFUploadGlobalFileInfo[swfUploadFile.id] = swfUploadFile;
		
				$K.windows['wid_uploadprogress'].refresh();
		
				//$K.yg_SWFUploadQueue['wid_'+winID] = $K.yg_SWFUploadObjects['wid_'+winID].movieName;
				if (!$K.yg_SWFUploadQueueProcessing) {
					$K.yg_SWFUploadProcessQueue();
				}
 				
				this.startUpload();
			}
		}
	} catch (ex)  {
        this.debug(ex);
	}
}

function uploadStart(file) {
	try {
		/*
 		var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
 		var uploadTemplate = $('wid_uploadprogress').down('.mk_upload_templates').innerHTML;
		uploadTemplate = uploadTemplate.replace(/__TEMPLATEID__/g, file.id);		// Replace ID
		uploadTemplate = uploadTemplate.replace(/__TEMPLATEINDEX__/g, file.index);	// Replace Index
		uploadTemplate = uploadTemplate.replace(/__TEMPLATENAME__/g, file.name);	// Replace Name
		
		uploadStatusPanel.insert({ bottom: uploadTemplate });
		
		var winID = 'wid_uploadprogress';
		*/

		// Start timeout timer
		$('wid_uploadprogress').timeoutId = window.setTimeout($K.yg_onUploadTimeout, $K.uploadTimeout);
	}
	catch (ex) {}
	
	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		var currentFile;
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
		if (this.customSettings.extensionDataUpload) {
			var uploadStatusPanel = $(this.customSettings.uploadWinId).down('.mk_uploadpanel');
		} else {
			var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
		}
		
		uploadStatusPanel.select('li').each(function(item){
			if (item.readAttribute('yg_id').replace(/-file/,'') == file.id) {
				currentFile = item;
			}
		});
		
		if (isNaN(percent) || !isFinite(percent)) {
			percent = 100;
		}
		
		currentFile.addClassName('uploading');
		currentFile.down('.uploadpercent').update(percent+' %');
		currentFile.down('.uploadstatusbg').down().setStyle({width:percent+'%'} );
		
		// Calc global percentage
		var globPercent = Math.ceil(( ($K.yg_SWFUploadGlobalDone + bytesLoaded) / $K.yg_calcSWFUploadGlobalFilesizes() ) * 100);

		if (isNaN(globPercent) || !isFinite(globPercent)) {
			globPercent = 100;
		}
		
		if ($K.windows['wid_uploadprogress']) $K.windows['wid_uploadprogress'].setCaption( $K.TXT('TXT_UPLOAD_STATUS') + ' <strong>' + globPercent + ' %</strong>' );
		
		// Restart timeout handler if needed
		if ($('wid_uploadprogress').timeoutId) {
			window.clearTimeout($('wid_uploadprogress').timeoutId);
			$('wid_uploadprogress').timeoutId = window.setTimeout($K.yg_onUploadTimeout, $K.uploadTimeout);
		}
		
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
	try {
		var currentFile;
		if (this.customSettings.extensionDataUpload) {
			var uploadStatusPanel = $(this.customSettings.uploadWinId).down('.mk_uploadpanel');
			var backendFunction = 'processImportExtensionData';
		} else if (this.customSettings.userProfilePictureUpload) {
			var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
			var backendFunction = 'processUserProfilePicture';
		} else {
			var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
			var backendFunction = 'processUpload';
		}
		var uploadWinId = uploadStatusPanel.up('.ywindow').id;
		var openerWinId = this.customSettings.winID;
		
		uploadStatusPanel.select('li').each(function(item){
			if (item.readAttribute('yg_id').replace(/-file/,'') == file.id) {
				currentFile = item;
			}
		});
	
		if (serverData) {
			serverData.evalScripts();
			var controller = this;
			
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
				currentFile.srcWindow = this.customSettings.winID;
				currentFile.userID = this.customSettings.userID;

				// Check if last file
				var lastFile = false;
				var uploadingFiles = currentFile.up('ul').select('li.uploading');
				if (uploadingFiles.length == 0) {
					lastFile = true;
				}

				// Reset timeout handler if needed
				if ($('wid_uploadprogress').timeoutId) {
					window.clearTimeout($('wid_uploadprogress').timeoutId);
					$('wid_uploadprogress').timeoutId = undefined;
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
		}
		
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
	try {
	
		// Set fileprocess to "error" and disable cancel for that element
		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			// progress.setStatus("Upload Error: " + message);
			this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			// progress.setStatus("Upload Failed.");
			this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			// progress.setStatus("Server (IO) Error");
			this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			// progress.setStatus("Security Error");
			this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			// progress.setStatus("Upload limit exceeded.");
			this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			// progress.setStatus("Failed Validation.  Upload skipped.");
			this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			// If there aren't any files left (they were all cancelled) disable the cancel button
			if (this.getStats().files_queued === 0) {
				// Cancel button disabled = true
			}
			// progress.setStatus("Cancelled");
			// progress.setCancelled();
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			// progress.setStatus("Stopped");
			break;
		default:
			// progress.setStatus("Unhandled Error: " + errorCode);
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {
	if (this.getStats().files_queued === 0) {
		var winID = this.customSettings.uploadButtonId.replace(/_okbutton/,'');

		// Refresh the tab after extensionDataUpload
		if (this.customSettings.extensionDataUpload) {
			/*
			console.warn( '###############################' );
			console.warn( this.customSettings.winID );
			console.warn( '###############################' );
			*/
			if ($K.windows['wid_'+this.customSettings.winID]) {
				$K.windows['wid_'+this.customSettings.winID].tabs.select(0,Koala.windows['wid_'+this.customSettings.winID].tabs.params);
			}
		}

		// Now close window and finally destroy swfobject
		if (!this.customSettings.autoUpload && !this.customSettings.extensionDataUpload) {
			$K.windows[winID].remove();
			$K.yg_SWFUploadObjects[winID].destroy();
			delete $K.yg_SWFUploadObjects[winID];
			if ($K.yg_SWFUploadObjects[winID+'_2']) {
				$K.yg_SWFUploadObjects[winID+'_2'].destroy();
				delete $K.yg_SWFUploadObjects[winID+'_2'];
			}		
		}
		
		// And process next queue
		$K.yg_SWFUploadProcessQueue();

		$K.yg_SWFUploadQueueProcessing = false;
		
	} else {
		$K.yg_SWFUploadGlobalDone += file.size;
		
		if (this.customSettings.extensionDataUpload) {
			var uploadStatusPanel = $(this.customSettings.uploadWinId).down('.mk_uploadpanel');
		} else {
			var uploadStatusPanel = $('wid_uploadprogress').down('.mk_uploadpanel');
		}
		
		var currentFile;
		uploadStatusPanel.select('li').each(function(item){
			if (item.readAttribute('yg_id').replace(/-file/,'') == file.id) {
				currentFile = item;
			}
		});
		
		if (currentFile.next()) {
			currentFile.next().addClassName('uploading');
		}
		
		this.startUpload();
	}
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
	var status = document.getElementById("divStatus");
	status.innerHTML = numFilesUploaded + " file" + (numFilesUploaded === 1 ? "" : "s") + " uploaded.";
}


// This custom debug method sends all debug messages to the Firebug console.  If debug is enabled it then sends the debug messages
// to the built in debug console.  Only JavaScript message are sent to the Firebug console when debug is disabled (SWFUpload won't send the messages
// when debug is disabled).
function SWFUploadFirebugHandler(message) {
	try {
		if (window.console && typeof(window.console.error) === "function" && typeof(window.console.log) === "function") {
			if (typeof(message) === "object" && typeof(message.name) === "string" && typeof(message.message) === "string") {
				window.console.error(message);
			} else {
				window.console.log(message);
			}
		}
	} catch (ex) {
	}
	try {
		if (this.settings.debug) {
			this.debugMessage(message);
		}
	} catch (ex1) {
	}
}
