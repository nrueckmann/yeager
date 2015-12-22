<?php

\framework\import('org.phpmailer.phpmailer');

$jsQueue = new JSQueue(NULL);
$viewMgr = new Views();

switch ($action) {

	case 'userSelectNode':
		$node = $this->params['node'];
		$wid = $this->params['wid'];

		$rootUserId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTUSERID");
		$anonUserId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONUSERID");

		// Check rights
		$rusers = sUsergroups()->permissions->check(sUserMgr()->getCurrentUserID(), "RUSERS");
		if ($rusers && ($node != $rootUserId) && ($node != $anonUserId)) {
			$koala->callJSFunction('Koala.yg_enable', 'tree_btn_delete', 'btn-' . $wid, 'tree_btn');
		} else {
			$koala->callJSFunction('Koala.yg_disable', 'tree_btn_delete', 'btn-' . $wid, 'tree_btn');
		}
		$user = new User($node);
		$userinfo = $user->get();
		$userinfo['PROPS'] = $user->properties->getValues($node);
		$username = $userinfo['PROPS']['FIRSTNAME'] . ' ' . $userinfo['PROPS']['LASTNAME'];
		if ($username == ' ') {
			$username = ($itext['TXT_UNKNOWN']) ? ($itext['TXT_UNKNOWN']) : ('$TXT_UNKNOWN');
		}
		break;

	case 'addUser':
		$wid = $this->params['wid'];

		$newUserId = sUserMgr()->add(($itext['TXT_UNKNOWN']) ? ($itext['TXT_UNKNOWN']) : ('$TXT_UNKNOWN'));
		$user = new User($newUserId);
		$user->properties->setValue('FIRSTNAME', ($itext['TXT_UNKNOWN']) ? ($itext['TXT_UNKNOWN']) : ('$TXT_UNKNOWN'));
		$user->properties->setValue('TIMEZONE', 'Europe/Berlin');
		$user->properties->setValue('DATEFORMAT', 'dd.mm.YYYY');
		$user->properties->setValue('TIMEFORMAT', '24');
		$user->properties->setValue('WEEKSTART', '1');

		$koala->callJSFunction('Koala.yg_addUserItem', $wid, $newUserId, ($itext['TXT_UNKNOWN']) ? ($itext['TXT_UNKNOWN']) : ('$TXT_UNKNOWN'));

		$koala->queueScript('$(\'' . $wid . '_objcnt\').update( parseInt($(\'' . $wid . '_objcnt\').innerHTML, 10) +1 );');
		break;

	case 'delUser':
		$userID = $this->params['userID'];
		$wid = $this->params['winID'];
		$wid = explode('_', $wid);
		$wid = $wid[1];

		if (is_array($userID)) {
			foreach ($userID as $userID_Item) {
				sUserMgr()->remove($userID_Item);
				$jsQueue->add($userID_Item, HISTORYTYPE_USER, 'OBJECT_DELETE', sGuiUS(), 'user', NULL, NULL, $userID_Item . '-user', 'usergroups');
			}
		} else {
			sUserMgr()->remove($userID);
			$jsQueue->add($userID, HISTORYTYPE_USER, 'OBJECT_DELETE', sGuiUS(), 'user', NULL, NULL, $userID . '-user', 'usergroups');
		}
		$koala->queueScript('Koala.yg_disable(\'tree_btn_delete\', \'btn-' . $wid . '\', \'tree_btn\');');
		break;

	case 'addRole':
		$wid = $this->params['wid'];
		$newRoleId = sUsergroups()->add($itext['TXT_NEW_OBJECT']);

		// Add permissions for all views
		$views = $viewMgr->getList();
		$hiddenviews = $viewMgr->getHiddenViews();

		$permissionArray = array(
			array(
				'USERGROUPID' => $newRoleId,
				'RREAD' => 1,
				'RWRITE' => 1,
				'RDELETE' => 1,
				'RSUB' => 1,
				'RSTAGE' => 1,
				'RMODERATE' => 1,
				'RCOMMENT' => 1,
				'RSEND' => 1
			)
		);
		foreach ($views as $view) {
			if ($view['OBJECTID']) {
				$viewMgr->permissions->setPermissions($permissionArray, $view['OBJECTID']);
			}
		}
		foreach ($hiddenviews as $hiddenview) {
			if ($hiddenview['VIEWID']) {
				$viewMgr->permissions->setPermissions($permissionArray, $hiddenview['VIEWID']);
			}
		}

		// Add permissions for all filetypes
		$fileMgr = sFileMgr();
		$filetypes = $fileMgr->filetypes->getList();

		foreach ($filetypes as $filetype) {
			if ($filetype['OBJECTID']) {
				$fileMgr->filetypes->permissions->setPermissions($permissionArray, $filetype['OBJECTID']);
			}
		}

		$koala->callJSFunction('Koala.yg_addRoleItem', $wid, $newRoleId, ($itext['TXT_NEW_OBJECT']) ? ($itext['TXT_NEW_OBJECT']) : ('$TXT_NEW_OBJECT'));
		break;

	case 'deleteRole':
		$roleID = $this->params['roleID'];
		$wid = $this->params['wid'];

		$confirmed = $this->params['confirmed'];
		$positive = $this->params['positive'];

		if ($confirmed != 'true') {
			$parameters = array(
				'roleID' => $roleID,
				'wid' => $wid
			);
			$koala->callJSFunction('Koala.yg_confirm',
				($itext['TXT_USERGROUP_DELETE'] != '') ? ($itext['TXT_USERGROUP_DELETE']) : ('$TXT_USERGROUP_DELETE'),
				($itext['TXT_USERGROUP_DELETE_TEXT'] != '') ? ($itext['TXT_USERGROUP_DELETE_TEXT']) : ('$TXT_USERGROUP_DELETE_TEXT'),
				$action, json_encode($parameters)
			);
		} else if (($confirmed == 'true') && ($positive == 'true')) {
			// Remove all assignments to this role in mailings
			$mailingMgr = new MailingMgr();
			$mailingMgr->removeUsergroupFromMailings($roleID);

			sUsergroups()->remove($roleID);
			$koala->queueScript('$(Koala.windows[\'' . $wid . '\'].boundWindow).addClassName(\'boxghost\')');
			$koala->queueScript('Koala.windows[Koala.windows[\'' . $wid . '\'].boundWindow].init();');
			$jsQueue->add($roleID, HISTORYTYPE_USERGROUP, 'OBJECT_DELETE', sGuiUS(), 'usergroup', NULL, NULL, $roleID . '-usergroup', 'item');
		}
		break;

	case 'setNewPassword':
		$userPassword = $this->params['userPassword'];
		$userToken = $this->params['userToken'];
		$winID = $this->params['winID'];

		if ($userID = sUserMgr()->getUserIdByToken($userToken)) {
			$user = new User($userID);
			$userinfo = $user->get();

			if ($userinfo) {
				// Check if password is secure enough
				$pwok = sUserMgr()->verifyPasswordStrength($userPassword);

				if ($pwok) {

					sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
					$user = new User($userinfo['ID']);
					$user->setPassword($userPassword);
					$user->removeToken();
					sUserMgr()->unimpersonate();

					if ($winID) {
						$koala->callJSFunction('Koala.yg_setNewPasswordSuccess', $winID);
					}
				} else {
					if ($winID) {
						$koala->callJSFunction('Koala.yg_showNewPasswordError', $winID);
					}
				}
			}

		}
		break;

	case 'recoverLogin':
		$userEmail = $this->params['userEmail'];
		$winID = $this->params['winID'];
		$newUser = $this->params['newUser'];

		// Impersonate as Administrator (Anonymous-User doesn't have the rights)
		sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
		$user = new User(sUserMgr()->getCurrentUserID());
		$userinfo = sUserMgr()->getByEmail($userEmail, true);
		if ($userinfo) {
			$user = new User($userinfo['ID']);

			// Generate path for recovery URL
			$webroot_path = rtrim(ltrim((string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/');
			if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
				$absoluteprefix = 'https://';
			} else {
				$absoluteprefix = 'http://';
			}
			$absoluteprefix .= $_SERVER['SERVER_NAME'];

			$docpath = (string)sConfig()->getVar('CONFIG/DIRECTORIES/LOGINURL');
			if ($docpath == "") $docpath = (string)sConfig()->getVar('CONFIG/DIRECTORIES/DOCPATH');

			// Generate a token for this user
			$expireTS = time() + 60 * 60 * 24;
			$token = $user->generateToken($expireTS);

			if ($newUser) {
				$passwordResetUrl = $absoluteprefix . $docpath . '?action=passwordreset&newuser=1&token=' . urlencode($token);
			} else {
				$passwordResetUrl = $absoluteprefix . $docpath . '?action=passwordreset&token=' . urlencode($token);
			}

			$mail = new PHPMailer();
			$mail->Encoding = '8bit';
			$mail->CharSet = 'utf-8';
			$mail->From = 'yeager@yeager.cm';
			$mail->FromName = 'yeager CMS';
			$mail->Subject = $itext['TXT_PASSWORD_RECOVERY'];
			$mail->Body = $itext['TXT_PASSWORD_RECOVERY_EMAIL'] . "\n\n" . $passwordResetUrl;
			$mail->AddAddress($userinfo['LOGIN']);
			$smtpServer = (string)sConfig()->getVar('CONFIG/MAILINGS/SMTP');
			if ($smtpServer) {
				$mail->IsSMTP();
				$mail->Host = $smtpServer;
			}
			$mail->Send();
			$mail->ClearAddresses();
		}
		if ($winID) {
			$koala->callJSFunction('Koala.yg_userPasswordSent', $userEmail);
		}

		sUserMgr()->unimpersonate();
		break;

	case 'userLogin':
		$userName = $this->params['userName'];
		$userPassword = $this->params['userPassword'];
		$keepLoggedIn = $this->params['keepLoggedIn'];
		$winID = $this->params['winID'];

		// Check if keeopLoggedIn is set
		if ($keepLoggedIn) {
			$this->session->setPSessionVar('keepLoggedIn', true);
		}

		// Check if a custom login-url is set
		$customLoginURL = (string)$this->config->getVar('CONFIG/DIRECTORIES/LOGINURL');
		if (trim($customLoginURL) != '') {
			if ( (strpos($_SERVER['REDIRECT_URL'], trim($customLoginURL)) != 0) &&
				 (strpos($_SERVER['REQUEST_URI'], trim($customLoginURL)) != 0) ) {
				$header = $_SERVER['SERVER_PROTOCOL'].' 403 Forbidden';
				header($header);
				echo $header;
				die();
			}
		}

		$isValidated = false;
		if ($userID = sUserMgr()->validate($userName, $userPassword)) {
			// Check permissions
			$tmpUser = new User(sUserMgr()->getCurrentUserID());
			$userinfo = $tmpUser->get();

			$perm = array();
			$perm['RPAGES'] = $tmpUser->checkPermission("RPAGES");
			$perm['RCONTENTBLOCKS'] = $tmpUser->checkPermission("RCONTENTBLOCKS");
			$perm['RFILES'] = $tmpUser->checkPermission("RFILES");
			$perm['RTAGS'] = $tmpUser->checkPermission("RTAGS");
			$perm['RUSERS'] = $tmpUser->checkPermission("RUSERS");
			$perm['RUSERGROUPS'] = $tmpUser->checkPermission("RUSERGROUPS");
			$perm['REXTENSIONS_PAGE'] = $tmpUser->checkPermission("REXTENSIONS_PAGE");
			$perm['REXTENSIONS_MAILING'] = $tmpUser->checkPermission("REXTENSIONS_MAILING");
			$perm['REXTENSIONS_FILE'] = $tmpUser->checkPermission("REXTENSIONS_FILE");
			$perm['REXTENSIONS_CBLOCK'] = $tmpUser->checkPermission("REXTENSIONS_CBLOCK");
			$perm['RIMPORT'] = $tmpUser->checkPermission("RIMPORT");
			$perm['REXPORT'] = $tmpUser->checkPermission("REXPORT");
			$perm['REXTENSIONS_CBLISTVIEW'] = $tmpUser->checkPermission("REXTENSIONS_CBLISTVIEW");
			$perm['RUPDATER'] = $tmpUser->checkPermission("RUPDATER");
			$perm['RDATA'] = $tmpUser->checkPermission("RDATA");
			$perm['RSITES'] = $tmpUser->checkPermission("RSITES");
			$perm['RTEMPLATES'] = $tmpUser->checkPermission("RTEMPLATES");
			$perm['RENTRYMASKS'] = $tmpUser->checkPermission("RENTRYMASKS");
			$perm['RPROPERTIES'] = $tmpUser->checkPermission("RPROPERTIES");
			$perm['RFILETYPES'] = $tmpUser->checkPermission("RFILETYPES");
			$perm['RCOMMENTCONFIG'] = $tmpUser->checkPermission("RCOMMENTCONFIG");
			$perm['RVIEWS'] = $tmpUser->checkPermission("RVIEWS");
			$perm['RBACKEND'] = $tmpUser->checkPermission("RBACKEND");
			if ($perm['RBACKEND']) {
				$isValidated = true;
			}
		}
		if ($isValidated) {
			$this->session->setPSessionVar('username', $userName);
			$this->session->setPSessionVar('password', $userPassword);
			$this->session->setPSessionVar('isvalidated', true);
            $this->session->refrehSessionCookie();
			$koala->callJSFunction('Koala.yg_doLogin', $winID, $userID);
			$this->frontendMode = 'false';
		} else {
			$this->session->setPSessionVar('username', $userName);
			$this->session->setPSessionVar('password', '');
			$this->session->setPSessionVar('isvalidated', false);
			$koala->callJSFunction('Koala.yg_showLoginError', $winID);
		}
		break;

	case 'userLogout':
        $this->session->setPSessionVar('version', '');
		$this->session->setPSessionVar('username', '');
		$this->session->setPSessionVar('password', '');
		$this->session->setPSessionVar('userroles', '');
		$this->session->setPSessionVar('isvalidated', false);
        $this->session->removeCookie("version");
        $this->session->removeCookie("sid");
		break;

	case 'getUserInfo':
		$userID = $this->params['userID'];
		$jsQueue->add($userID, HISTORYTYPE_USER, 'SET_USERINFOS', sGuiUS(), NULL);

		$koala->queueScript("Koala.yg_hoverUserHint(" . $userID . ")");
		break;

	case 'processUserProfilePicture':
		$realFilename = $this->params['realFilename'];
		$realExtension = explode('.', $realFilename);
		$realExtension = $realExtension[count($realExtension) - 1];
		$itemID = $this->params['itemID'];
		$userID = $this->params['userID'];
		$saveAsRealPicture = false;
		if ($userID) {
			$user = new User($userID);
			if ($user->properties->setValue('PROFILEPICTURE', $userID) === false) {
				$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
			} else {
				$saveAsRealPicture = true;
			}
		} else {
			$userID = sUserMgr()->getCurrentUserID();
		}
		$filedir = $this->approot . $this->userpicdir;
		$fileTmpName = $filedir . $userID . '-temp_picture.' . $realExtension;
		$filesize = filesize($fileTmpName);

		if ($filesize) {
			$imagesize = getimagesize($fileTmpName);
			$constrain_w = false;
			$constrain_h = false;
			if ($imagesize[0] > $imagesize[1]) {
				// Wider than tall
				$constrain_w = false;
				$constrain_h = true;
			}
			if ($imagesize[1] > $imagesize[0]) {
				// Taller than wide
				$constrain_w = true;
				$constrain_h = false;
			}

			$procs = $this->files_procs;
			$fileproc = 'GD';
			for ($p = 0; $p < count($procs); $p++) {
				if ($procs[$p]["name"] == $fileproc) {
					if (file_exists($this->approot.$this->filesprocdir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php")) {
						require_once($this->approot.$this->filesprocdir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php");
					} elseif (file_exists($this->approot.$this->processordir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php")) {
						require_once($this->approot.$this->processordir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php");
					} else {
						continue;
					}
					$classname = (string)$procs[$p]["classname"];
					$namespace = (string)$procs[$p]["namespace"];
					if (strlen($namespace)) {
						$classname = $namespace."\\".$classname;
					}
					$moduleclass = new $classname();
					$moduleclass->generateThumbnail($userID . '-temp_picture.jpg', $imagesize, '', 48, 48, $filedir, $fileTmpName, $constrain_w, $constrain_h, 1, 1);
				}
			}
		}

		$internPrefix = (string)sConfig()->getVar('CONFIG/REFTRACKER/INTERNALPREFIX');
		if ($saveAsRealPicture) {
			if (file_exists($this->approot . $this->userpicdir . $userID . '-temp_picture.jpg')) {
				unlink($this->approot . $this->userpicdir . $userID . '-picture.jpg');
				rename($this->approot . $this->userpicdir . $userID . '-temp_picture.jpg', $this->approot . $this->userpicdir . $userID . '-picture.jpg');
				$cachedImages = glob($this->approot . $this->userpicdir . $userID . '-*x*.jpg');
				foreach($cachedImages as $cachedImagesItem) {
					@unlink($cachedImagesItem);
				}
				$koala->callJSFunction('Koala.yg_setUserProfilePreviewPicture', $itemID, $internPrefix . '/userimage/' . $userID . '/48x48?rnd=' . rand());
				$jsQueue->add($userID, HISTORYTYPE_USER, 'OBJECT_CHANGEBGIMAGE', sGuiUS(), 'user', NULL, NULL, $userID . '-user', 'picture', $internPrefix . '/userimage/' . $userID . '/48x48?rnd=' . rand());
				$jsQueue->add($userID, HISTORYTYPE_USER, 'CLEAR_USERINFOS', sGuiUS(), NULL);
			}
		} else {
			$koala->callJSFunction('Koala.yg_setUserProfilePreviewPicture', $itemID, $internPrefix . '/userimage/' . $userID . '/48x48?tmp=true&rnd=' . rand());
		}
		$koala->callJSFunction('Koala.yg_setFileStatusOK', $itemID);
		break;

	case 'uploadUserProfilePicture':
		$filetype = $this->params['type'];
		$filetitle = $this->params['title'];
		if ($_FILES['Filedata']['tmp_name']) {
			$fileTmpName = $_FILES['Filedata']['tmp_name'];
			$filename = basename($_FILES['Filedata']['name']);
		} else {
			$fileTmpName = fixAndMovePLUploads();
			$filename = basename($_REQUEST['name']);
		}
		$filesize = filesize($fileTmpName);
		$uploadID = $this->params['uploadID'];
		$extensionid = $this->params['fileID'];
		$userid = $this->params['userID'];
		$extensionid = explode('-', $extensionid);
		$tsuffix = $extensionid[1];
		$extensionid = $extensionid[0];
		$uploadwinid = $this->request->parameters['uploadWinId'];
		$extensionid = $this->request->parameters['extensionId'];
		$realExtension = explode('.', $filename);
		$realExtension = $realExtension[count($realExtension) - 1];
		$window_id = $this->params['winID'];

		if (($fileTmpName != '') && ($fileTmpName != 'none')) {
			$koala->queueScript("window.hadUploadError = false;window.hadUploadErrorMsg = undefined;");
			$extension = explode('.', $filename);

			$filedir = $this->approot . $this->userpicdir;

			if ($userid) {
				$temp_filename = $filedir . $userid . '-temp_picture.' . $realExtension;
				$original_filename = $filedir . $userid . '-picture-original';
			} else {
				$temp_filename = $filedir . sUserMgr()->getCurrentUserID() . '-temp_picture.' . $realExtension;
				$original_filename = $filedir . sUserMgr()->getCurrentUserID() . '-picture-original';
			}
			copy($fileTmpName, $temp_filename);
			copy($fileTmpName, $original_filename);
		}
		break;

	case 'saveUserProfile':
		$winid = $this->params['winID'];

		$company = $this->params['company'];
		$department = $this->params['department'];
		$firstname = $this->params['firstname'];
		$lastname = $this->params['lastname'];
		$phone = $this->params['phone'];
		$fax = $this->params['fax'];
		$mobile = $this->params['mobile'];
		$website = $this->params['website'];
		$email = $this->params['email'];
		$password = $this->params['password'];
		$timezone = $this->params['timezone'];
		$weekstart = $this->params['weekstart'];
		$dateformat = $this->params['dateformat'];
		$timeformat = $this->params['timeformat'];
		$language = $this->params['language'];
		$emailChanged = $this->params['emailChanged'];
		$passwordChanged = $this->params['passwordChanged'];

		$guiDataChanged = $this->params['guiDataChanged'];

		$positive = $this->params['positive'];
		$confirmed = $this->params['confirmed'];
		$hadError = $passwordInsecure = $duplicateEmail = false;
		$errorFields = array();

		if ($guiDataChanged && ($confirmed != 'true')) {
			$parameters = array(
				'winID' => $winid,
				'company' => $company,
				'department' => $department,
				'firstname' => $firstname,
				'lastname' => $lastname,
				'phone' => $phone,
				'fax' => $fax,
				'mobile' => $mobile,
				'website' => $website,
				'email' => $email,
				'password' => $password,
				'emailChanged' => $emailChanged,
				'passwordChanged' => $passwordChanged,
				'guiDataChanged' => $guiDataChanged,
				'language' => $language,
				'timezone' => $timezone,
				'dateformat' => $dateformat,
				'timeformat' => $timeformat,
				'weekstart' => $weekstart
			);
			$koala->callJSFunction('Koala.yg_confirm',
				($itext['TXT_WARNING'] != '') ? ($itext['TXT_WARNING']) : ('$TXT_WARNING'),
				($itext['TXT_WARNING_GUICONFIG_CHANGED'] != '') ? ($itext['TXT_WARNING_GUICONFIG_CHANGED']) : ('$TXT_WARNING_GUICONFIG_CHANGED'),
				$action, json_encode($parameters)
			);
		} else if (($confirmed == 'true') && (($positive == 'true'))) {
			$user = new User(sUserMgr()->getCurrentUserID());
			$user->setLanguage($language);
			$user->properties->setValue('TIMEZONE', $timezone);
			$user->properties->setValue('DATEFORMAT', $dateformat);
			$user->properties->setValue('TIMEFORMAT', $timeformat);
			$user->properties->setValue('WEEKSTART', $weekstart);

			$user->properties->setValue('COMPANY', $company);
			$user->properties->setValue('DEPARTMENT', $department);
			$user->properties->setValue('FIRSTNAME', $firstname);
			$user->properties->setValue('LASTNAME', $lastname);
			$user->properties->setValue('PHONE', $phone);
			$user->properties->setValue('FAX', $fax);
			$user->properties->setValue('MOBILE', $mobile);
			$user->properties->setValue('WEBSITE', $website);

			if ($emailChanged) {
				// Check if email-address is valid and really exists
				if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
					// Check if email address is already used
					$user = new User(sUserMgr()->getCurrentUserID());
					$userinfo = sUserMgr()->getByEmail($email, true);
					if (!$userinfo || ($userinfo['ID'] == sUserMgr()->getCurrentUserID())) {
						$user->setLogin($email);
						$this->session->setPSessionVar('username', $email);
					} else {
						$hadError = true;
						$errorFields[] = 'email';
						$errorFields[] = 'emailconfirm';
						$duplicateEmail = true;
					}
				} else {
					$hadError = true;
					$errorFields[] = 'email';
					$errorFields[] = 'emailconfirm';
				}
			}
			if ($passwordChanged) {
				// Check if password is secure enough
				$pwok = sUserMgr()->verifyPasswordStrength($password);

				if ($pwok) {
					$user->setPassword($password);
					$this->session->setPSessionVar('password', $password);
				} else {
					$hadError = true;
					$errorFields[] = 'password';
					$errorFields[] = 'passwordconfirm';
					$passwordInsecure = true;
				}
			}
			if ($emailChanged || $passwordChanged) {
				$this->session->setPSessionVar('isvalidated', true);
                $this->session->refrehSessionCookie();
			}

			if ($hadError) {
				foreach ($errorFields as $errorField) {
					$koala->queueScript('$(\'wid_' . $winid . '_' . $errorField . '\').addClassName(\'error\');');
				}
			} else {
				$koala->queueScript('window.location.reload();');
			}
		} else {
			$user->properties->setValue('COMPANY', $company);
			$user->properties->setValue('DEPARTMENT', $department);
			$user->properties->setValue('FIRSTNAME', $firstname);
			$user->properties->setValue('LASTNAME', $lastname);
			$user->properties->setValue('PHONE', $phone);
			$user->properties->setValue('FAX', $fax);
			$user->properties->setValue('MOBILE', $mobile);
			$user->properties->setValue('WEBSITE', $website);

			if ($emailChanged) {
				// Check if email-address is valid and really exists
				if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
					// Check if email address is already used
					$user = new User(sUserMgr()->getCurrentUserID());
					$userinfo = sUserMgr()->getByEmail($email, true);
					if (!$userinfo || ($userinfo['ID'] == sUserMgr()->getCurrentUserID())) {
						$user->setLogin($email);
						$this->session->setPSessionVar('username', $email);
					} else {
						$duplicateEmail = true;
						$hadError = true;
						$errorFields[] = 'email';
						$errorFields[] = 'emailconfirm';
					}
				} else {
					$hadError = true;
					$errorFields[] = 'email';
					$errorFields[] = 'emailconfirm';
				}
			}
			if ($passwordChanged) {
				// Check if password is secure enough
				$pwok = sUserMgr()->verifyPasswordStrength($password);
				if ($pwok) {
					$user->setPassword($password);
					$this->session->setPSessionVar('password', $password);
				} else {
					$hadError = true;
					$errorFields[] = 'password';
					$errorFields[] = 'passwordconfirm';
					$passwordInsecure = true;
				}
			}
			if ($emailChanged || $passwordChanged) {
				$this->session->setPSessionVar('isvalidated', true);
                $this->session->refrehSessionCookie();
			}

			if (file_exists($this->approot . $this->userpicdir . sUserMgr()->getCurrentUserID() . '-temp_picture.jpg')) {
				$cachedImages = glob($this->approot . $this->userpicdir . sUserMgr()->getCurrentUserID() . '-*x*.jpg');
				foreach($cachedImages as $cachedImagesItem) {
					@unlink($cachedImagesItem);
				}
				rename($this->approot . $this->userpicdir . sUserMgr()->getCurrentUserID() . '-temp_picture.jpg', $this->approot . $this->userpicdir . sUserMgr()->getCurrentUserID() . '-picture.jpg');
				$user->properties->setValue('PROFILEPICTURE', sUserMgr()->getCurrentUserID());
			}

			$internPrefix = (string)sConfig()->getVar('CONFIG/REFTRACKER/INTERNALPREFIX');
			$userpic = $internPrefix . '/userimage/' . sUserMgr()->getCurrentUserID() . '/48x48?rnd=' . rand();

			if (($confirmed == 'true') && ($positive == 'false') || !$hadError) {
				$koala->queueScript('Koala.windows[\'wid_' . $winid . '\'].remove();');

				$jsQueue->add(sUserMgr()->getCurrentUserID(), HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, sUserMgr()->getCurrentUserID() . '-user', 'firstname', $firstname);
				$jsQueue->add(sUserMgr()->getCurrentUserID(), HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, sUserMgr()->getCurrentUserID() . '-user', 'lastname', $lastname);
				$jsQueue->add(sUserMgr()->getCurrentUserID(), HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, sUserMgr()->getCurrentUserID() . '-user', 'name', $firstname . ' ' . $lastname);
				$jsQueue->add(sUserMgr()->getCurrentUserID(), HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, sUserMgr()->getCurrentUserID() . '-user', 'email', $email);
				$jsQueue->add(sUserMgr()->getCurrentUserID(), HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, sUserMgr()->getCurrentUserID() . '-user', 'company', $company);
				$jsQueue->add(sUserMgr()->getCurrentUserID(), HISTORYTYPE_USER, 'OBJECT_CHANGEBGIMAGE', sGuiUS(), 'user', NULL, NULL, sUserMgr()->getCurrentUserID() . '-user', 'picture', $userpic);
				$jsQueue->add(sUserMgr()->getCurrentUserID(), HISTORYTYPE_USER, 'CLEAR_USERINFOS', sGuiUS(), NULL);

				$koala->queueScript('Koala.userSettings.weekStart = \'' . $weekstart . '\';');
			} elseif ($hadError) {
				foreach ($errorFields as $errorField) {
					$koala->queueScript('$(\'wid_' . $winid . '_' . $errorField . '\').addClassName(\'error\');');
				}
				if ($passwordInsecure || $duplicateEmail) {
					if ($passwordInsecure) {
						$errorMsg .= $itext['TXT_PASSWORD_ERROR_WEAK'] . '<br /><br />';
					}
					if ($duplicateEmail) {
						$errorMsg .= $itext['TXT_EMAIL_ALREADY_USED_CHOOSE_ANOTHER'] . '<br />';
					}
					$koala->alert($errorMsg);
				}
			}
		}
		break;

}

?>