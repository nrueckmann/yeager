/**
 * @fileoverview Provides functionality for preview mode
 * @version 1.0
 */

/**
 * Create login box
 */
$K.yg_loginbox = function() {
	if (($('wid_login').getStyle('visibility') == 'hidden') || ($('wid_login').getStyle('display') == 'none')) {
		$('loginload').hide();
		$(document.body).addClassName("unauthorized");

		var submitFunction = function(event){
			if(event.keyCode == Event.KEY_RETURN) {
				document.forms['loginform-form'].submit();
				$K.yg_userLogin( $('wid_login_okbutton').down() );
				Event.stop(event);
			}
		};

		$('username').value = "";
		$('password').value = "";

		$('username').observe('keypress', submitFunction);
		$('password').observe('keypress', submitFunction);
		$('wid_login_checkbox').observe('keydown', submitFunction);
		$('wid_login_okbutton').observe('keydown', submitFunction);

		$('wid_login').setStyle({
			visibility: 'visible',
			display: 'block',
			zIndex: $K.yg_incrementTopZIndex()
		});

		$K.yg_customAttributeHandler($('wid_login'));
		$K.yg_centerWindow($('wid_login'));
		$('loginform').down('input').focus();
	}
}


/**
 * Toggle between login and recovery form
 */
$K.yg_loginToggleForms= function() {
	if ($("loginform").visible()) {
		$("loginform").hide();
		$("recoveryform").show();
		$("recoveryform").down('input').value = $("loginform").down('input').value;
	} else {
		$("password").value = "";
		$("loginform").show();
		$("recoveryform").hide();
		if ($('newusersetpassword')) {
			$('newusersetpassword').hide();
		}
		if ($('recoverpassword')) {
			$('recoverpassword').hide();
		}
		$('recoverypasswordsuccess').hide();
		$("recoveryformsuccess").hide();
	}
}


/**
 * Function for removing promptboxes and cleaning garbage
 * @param { String } [winID] Element id of login window
 */
$K.yg_removeLogin = function( winID ) {
	// Unmap keys
	$('username').stopObserving('keypress');
	$('password').stopObserving('keypress');

	// remove win
	$(winID).hide();
	document.body.removeClassName('unauthorized');
}


/**
 * Function for removing promptboxes and cleaning garbage
 * @param { Element } [which] Reference to the button
 */
$K.yg_userLogin = function( which ) {
	$('loginload').show();
	if ($('loginerror')) $('loginerror').hide();
	var winRef = which.up('.ywindow');
	var winID = winRef.id;

	if ($('recoverpassword') && $('recoverpassword').getStyle('display') == 'block') {
		// Password recovery active
		$K.yg_userSetNewPassword($('wid_login_recoverbutton').down('a'));
	} else {
		// Normal login active
		var emailField = $('username');
		var passwordField = $('password');
		var keepLoggedIn = $(winID+'_keeploggedin');
		emailField.removeClassName('error');
		passwordField.removeClassName('error');
		$('loginerror').hide();

		var hasError = false;
		if (emailField.value.strip() == '') {
			emailField.addClassName('error');
			hasError = true;
		}
		if (passwordField.value.strip() == '') {
			passwordField.addClassName('error');
			hasError = true;
		}

		if (!hasError) {
			var data = Array ( 'noevent', {yg_property: 'userLogin', params: {
				userName: emailField.value,
				userPassword: passwordField.value,
				keepLoggedIn: keepLoggedIn.value,
				winID: winID
			} } );
			$K.yg_AjaxCallback( data, 'userLogin', true );
		} else {
			$('loginload').hide();
		}
	}
}


/**
 * Function for removing promptboxes and cleaning garbage
 * @param { String } [winID] Window-Id of the loginwindow
 */
$K.yg_showLoginError = function( winID ) {
	if ($('loginerror')) {
		$('loginload').hide();
		$('passwordreseterror').hide();
		$('loginerror').show();
	}
}


/**
 * Function for display error message when using weak passwords
 * @param { String } [winID] Window-Id of the loginwindow
 */
$K.yg_showNewPasswordError = function( winID ) {
	if ($('loginerror')) {
		$('loginload').hide();
		$('loginerror').hide();
		$('passwordreseterror').show();
	}
}


/**
 * Function for user-login
 * @param { Element } [winID] Window-Id of the loginwindow
 * @param { String } [userID] Window-Id of the loginwindow
 */
$K.yg_doLogin = function( winID, userID ) {
	if ($('mainnav')) var oldUserId = $('mainnav').down('.user').readAttribute('yg_id').split('-')[0];

	// Refresh mainmenu & show startpage (if not already authenticated or different user)
	if ( document.body.hasClassName('unauthorized') || (userID != oldUserId) ) {
		$K.isAuthenticated = true;
		$K.yg_loadAuthContent();
	}
	$K.yg_removeLogin( winID );
}


/**
 * Function to reload content which requires authentication
 */
$K.yg_loadAuthContent = function() {
	var wins = $H($K.windows);
	wins.each(function(window) {
		window[1].remove(true);
	});

	$('authcontainer').innerHTML = '';
	new Ajax.Updater('authcontainer', $K.appdir+'authcontent', {
		evalScripts: true,
		method: 'post',
		parameters: {
			us: document.body.id,
			lh: $K.yg_getLastGuiSyncHistoryId()
		}
	});
}


/**
 * Function for user-login
 */
$K.yg_userLogout = function() {
	$K.isAuthenticated = false;
	document.body.addClassName('unauthenticated');
	var data = Array ( 'noevent', {yg_property: 'userLogout', params: {} } );
	for (winID in $K.windows) {
		// release locks for this window
		if (winID.split('_')[1] && $K.windows[winID].yg_type && $K.windows[winID].yg_id) {
			$K.yg_releaseLock(winID.split('_')[1], $K.windows[winID].yg_type, $K.windows[winID].yg_id);
		}
		$K.windows[winID].remove();
	}
	$K.yg_loginbox();
	$K.yg_AjaxCallback( data, 'userLogout', true );
}


/**
 * Function for setting a new password for a user
 * @param { Element } [which] Reference to the button
 */
$K.yg_userSetNewPassword = function(which) {
	which = $(which);

	var winRef = which.up('.ywindow');
	var winID = winRef.id;
	var password = $('wid_login_reset_password').value;
	var passwordConfirm = $('wid_login_reset_password_confirm').value;

	$('wid_login_reset_password').removeClassName('error');
	$('wid_login_reset_password_confirm').removeClassName('error');
	if ((password != '') && (password === passwordConfirm)) {
		$('loginload').show();
		var userPassword = $('wid_login_reset_password').value;
		var userToken = $('wid_login_reset_password_token').value;

		var data = Array ( 'noevent', {yg_property: 'setNewPassword', params: {
			userPassword: userPassword,
			userToken: userToken,
			winID: winID
		} } );
		$K.yg_AjaxCallback( data, 'setNewPassword', true );
	} else {
		$('wid_login_reset_password').addClassName('error');
		$('wid_login_reset_password_confirm').addClassName('error');
		$('loginload').hide();
	}
}


/**
 * Function for showing the "success"-panel after setting a new password
 * @param { Element } [which] Reference to the button
 */
$K.yg_setNewPasswordSuccess = function() {
	if ($('newusersetpassword')) {
		$('newusersetpassword').hide();
	}
	if ($('recoverpassword')) {
		$('recoverpassword').hide();
	}
	if ($('passwordreseterror')) {
		$('passwordreseterror').hide();
	}
	$('username').removeClassName('error');
	$('password').removeClassName('error');

	$('recoverypasswordsuccess').show();
	$('loginload').hide();
}


/**
 * Function for removing promptboxes and cleaning garbage
 * @param { Element } [which] Reference to the button
 */
$K.yg_userForgotPassword = function(which) {
	which = $(which);
	var winRef = which.up('.ywindow');
	var winID = winRef.id;

	var emailField = $(winID+'_forgotemail');
	emailField.removeClassName('error');
	$('recoverloginerror').hide();

	var hasError = false;
	if (emailField.value.strip() == '') {
		emailField.addClassName('error');
		hasError = true;
	}

	if (!hasError) {
		$('loginload').show();
		var email = emailField.value;
		emailField.value = '';
		var data = Array ( 'noevent', {yg_property: 'recoverLogin', params: {
			userEmail: email,
			winID: winID
		} } );
		$K.yg_AjaxCallback( data, 'recoverLogin', true );
	}
}


/**
 * Function for removing promptboxes and cleaning garbage
 * @param { String } [email] The emailaddress the password was sent to
 */
$K.yg_userPasswordSent = function(email) {
	$('recoveryform').hide();
	$('loginload').hide();
	$('recoveryformsuccessmessage').update( $('recoveryformsuccessmessage_default').innerHTML.replace('EMAILADRESS', email) );
	$('recoveryformsuccess').show();
}


/**
 * Function for removing promptboxes and cleaning garbage
 * @param { String } [winID] Window-Id of the loginwindow
 */
$K.yg_showRecoverLoginError = function(winID) {
	if ($('recoverloginerror')) {
		$('loginload').hide();
		$('recoverloginerror').show();
	}
}
