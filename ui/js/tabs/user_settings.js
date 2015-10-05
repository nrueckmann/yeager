/**
 * Submits the user settings dialog
 * @param { Integer } [winID] The window id
 * @function
 * @name $K.yg_submitUserSettings
 */
$K.yg_submitUserSettings = function( winID ) {
	var inputError = false;
	var inputFields = $('wid_'+winID+'_innercontent').select('input[name]');

	var paramData = {winID: winID};
	var prefixFilter = new RegExp('wid_'+winID+'_', 'g');
	inputFields.each(function(item){
		item.removeClassName('error');
		paramData[item.name.replace(prefixFilter, '')] = item.value;
	});
	
	if (paramData.firstname.strip().length == 0) {
		$('wid_'+winID+'_firstname').addClassName('error');
		inputError = true;
	}
	if (paramData.lastname.strip().length == 0) {
		$('wid_'+winID+'_lastname').addClassName('error');
		inputError = true;
	}
	if (paramData.email.strip().length == 0) {
		$('wid_'+winID+'_email').addClassName('error');
		inputError = true;
	}
	
	if (paramData.email != paramData.oldemail) {
		var emailError = false;
		if (paramData.email != paramData.emailconfirm) {
			$('wid_'+winID+'_email').addClassName('error');
			$('wid_'+winID+'_emailconfirm').addClassName('error');
			inputError = true;
			emailError = true;
		}
		if (!emailError) {
			paramData['emailChanged'] = true;
		}
	}
	if ( (paramData.password != paramData.oldpassword) && (paramData.password.strip().length > 0) ) {
		if (paramData.password.strip().length == 0) {
			$('wid_'+winID+'_password').addClassName('error');
			$('wid_'+winID+'_passwordconfirm').addClassName('error');
			inputError = true;
		} else if (paramData.password != paramData.passwordconfirm) {
			$('wid_'+winID+'_password').addClassName('error');
			$('wid_'+winID+'_passwordconfirm').addClassName('error');
			inputError = true;
			$K.yg_promptbox( $K.TXT('TXT_NOTIFICATION'), $K.TXT('TXT_PASSWORD_ERROR_NOT_IDENTICAL'), 'alert');
		} else {
			paramData['passwordChanged'] = true;
		}
	}

	if (paramData.language != paramData.oldlanguage) {
		paramData['guiDataChanged'] = true;
	}
	if (paramData.timezone != paramData.oldtimezone) {
		paramData['guiDataChanged'] = true;
	}
	if (paramData.dateformat != paramData.olddateformat) {
		paramData['guiDataChanged'] = true;
	}
	if (paramData.timeformat != paramData.oldtimeformat) {
		paramData['guiDataChanged'] = true;
	}
	if (paramData.weekstart != paramData.oldweekstart) {
		paramData['guiDataChanged'] = true;
	}

	if (inputError) {
		$('wid_'+winID+'_USER_SETTINGS').down('.error').focus();
	} else {
		var data = Array ( 'noevent', {yg_property: 'saveUserProfile', params: paramData } );
		$K.yg_AjaxCallback( data, 'saveUserProfile' );

		/*
		if (!paramData['guiDataChanged']) {
			$K.windows['wid_'+winID].remove();
		}
		*/
	}
}


/**
 * Initializes the user settings dialog
 * @param { Integer } [winID] The window id
 * @function
 * @name $K.yg_initDlgUserSettings
 */
$K.yg_initDlgUserSettings = function( winID ) {
	
	$K.yg_loadDlgUpload();

	if ($K.uploadFramework == 'swfuploader') {
		var swfuploadSettings = Object.clone( $K.yg_SWFUploadSettings );
		Object.extend(swfuploadSettings, {
			upload_url: $K.appdir + 'responder?handler=uploadUserProfilePicture',
			flash_url: $K.jsdir + '3rd/swfupload/swfupload.swf',
			button_placeholder_id: 'wid_'+winID+'_uploadbutton',
			button_height: "70",
			file_types: "*.jpg;*.jpeg;*.gif;*.png", 
			file_types_description: "Images",
			custom_settings: {
				uploadButtonId: 'wid_'+winID+'_uploadbutton',
				uploadTitle: 'N/A',
				uploadType: 'userProfilePicture',
				fileID: 'wid_'+winID+'_profilepicture',
				winID: winID,
				timestamp: ($('wid_'+winID+'_timestamp'))?($('wid_'+winID+'_timestamp').value):(0),
				autoUpload: true,
				userProfilePictureUpload: true
			},
			button_action: SWFUpload.BUTTON_ACTION.SELECT_FILE
		} );
		$K.yg_SWFUploadObjects['wid_'+winID] = new SWFUpload( swfuploadSettings );
	}

	if ($K.uploadFramework == 'plupload') {
		/* Init Upload */
		var customSettings = {
				uploadButtonId: 'wid_'+winID+'_uploadbutton',
				uploadTitle: 'N/A',
				uploadType: 'userProfilePicture',
				fileID: 'wid_'+winID+'_profilepicture',
				winID: winID,
				timestamp: ($('wid_'+winID+'_timestamp'))?($('wid_'+winID+'_timestamp').value):(0),
				userProfilePictureUpload: true,
				autoUpload: true
		};
		$K.yg_UploadInit( 'wid_'+winID, 'wid_'+winID+'_uploadbutton', null, {handler: 'uploadUserProfilePicture'}, customSettings, [{title: 'Images', extensions: 'jpg,jpeg,gif,png'}], false );
	}
	
}


/**
* Changes the user profile picture after an upload
* @param { String } [itemID] The id of the item in the uplaod queue window
* @param { String } [pictureSrc] The full path to the new previewpicture
* @function
* @name yg_setUserProfilePreviewPicture
*/
$K.yg_setUserProfilePreviewPicture = function( itemID, pictureSrc ) {
	var winRef = $('wid_'+$(itemID).srcWindow);
	var tempUserPic = winRef.down('img.mk_tempuserpic');
	
	tempUserPic.src = pictureSrc + '?rnd=' + (Math.random()*10000000);
}


/**
* Initializes the userpicture uploadpanel
* @param { String } [itemID] The id of the item in the uplaod queue window
* @param { String } [pictureSrc] The full path to the new previewpicture
* @function
* @name yg_initUserPropertiesPictureupload
*/
$K.yg_initUserPropertiesPictureupload = function( winID, userID ) {
	$K.yg_loadDlgUpload();
	
	if ($K.uploadFramework == 'swfuploader') {
		var swfuploadSettings = Object.clone( $K.yg_SWFUploadSettings );
		Object.extend(swfuploadSettings, {
			upload_url: $K.appdir + 'responder?handler=uploadUserProfilePicture',
			flash_url: $K.jsdir + '3rd/swfupload/swfupload.swf',
			button_placeholder_id: 'wid_'+winID+'_uploadbutton',
			button_height: "70",
			file_types: "*.jpg;*.jpeg;*.gif;*.png", 
			file_types_description: "Images",
			custom_settings: {
				uploadButtonId: 'wid_'+winID+'_uploadbutton',
				uploadTitle: 'N/A',
				uploadType: 'userProfilePicture',
				fileID: 'wid_'+winID+'_profilepicture',
				userID: userID,
				winID: winID,
				timestamp: ($('wid_'+winID+'_timestamp'))?($('wid_'+winID+'_timestamp').value):(0),
				autoUpload: true,
				userProfilePictureUpload: true
			},
			button_action: SWFUpload.BUTTON_ACTION.SELECT_FILE
		} );
		$K.yg_SWFUploadObjects['wid_'+winID] = new SWFUpload( swfuploadSettings );
	}

	if ($K.uploadFramework == 'plupload') {
		/* Init Upload */
		var customSettings = {
				uploadButtonId: 'wid_'+winID+'_uploadbutton',
				uploadTitle: 'N/A',
				uploadType: 'userProfilePicture',
				fileID: 'wid_'+winID+'_profilepicture',
				userID: userID,
				winID: winID,
				timestamp: ($('wid_'+winID+'_timestamp'))?($('wid_'+winID+'_timestamp').value):(0),
				userProfilePictureUpload: true,
				autoUpload: true
		};
		$K.yg_UploadInit( 'wid_'+winID, 'wid_'+winID+'_uploadbutton', null, {handler: 'uploadUserProfilePicture'}, customSettings, [{title: 'Images', extensions: 'jpg,jpeg,gif,png'}], false, true );
	}
}
