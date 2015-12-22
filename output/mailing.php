<?php

	include_once "error.php";

	$mailingMgr = new MailingMgr();
	$previewMode = false;

	// Check if we are in preview-mode
	if (!$mailingId && !$mailingVersion) {

		// Set frontend timezone
		date_default_timezone_set($this->frontendTimezone);

		$mailingVersion = $this->request->parameters['version'];
		if ($mailingVersion == "working") {
			$mailingVersion = "null";
		}
		$this->displaymode = "working";
		$request_path = $this->request->path;
		$request_path_string = implode('/', $request_path);
		$webroot_path_string = implode('/', $this->webroot);

		if (strpos($request_path_string, "/mailing/")>=0) {
			// Remove leading 'mailing/'
			$request_path_string = str_replace('/mailing/','/',$request_path_string);
		}
		if (strpos($request_path_string, "mailing/")===0) {
			// Remove leading 'mailing/'
			$request_path_string = substr($request_path_string, strlen('mailing/'));
		}
		if (strpos($request_path_string, $webroot_path_string.'/')===0) {
			// Remove leading webroot-prefix
			$request_path_string = substr($request_path_string, strlen($webroot_path_string.'/'));
		}
		$request_path = explode('/', $request_path_string);

		$mailingId = $request_path[0];

		if (!is_numeric($mailingId)) {
			$mailingId = sMailingMgr()->getMailingIdByPName($mailingId);
		}

		// Fill userinfo with data from current user
		$user = new User(sUserMgr()->getCurrentUserID());
		$userInfo = $user->get();
		$userInfo['PROPERTIES'] = $user->properties->getValues(sUserMgr()->getCurrentUserID());
		$userInfo['PROPERTIES']['FULLNAME'] = trim($userInfo['PROPERTIES']['FIRSTNAME'].' '.$userInfo['PROPERTIES']['LASTNAME']);

		$previewMode = true;
	} else {
		// Get and set  frontend timezone
		$this->displaymode = "live";
		$frontendTimezone = (string)Singleton::config()->getVar('CONFIG/TIMEZONES/FRONTEND');
		if (!$frontendTimezone) {
			$frontendTimezone = 'Europe/Berlin';
		}
		date_default_timezone_set($frontendTimezone);
	}

	$mailing = $mailingMgr->getMailing($mailingId, $mailingVersion);

	if (!$mailing) {
		sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
		if ($mailingMgr->getMailing($mailingId, $mailingVersion)) {
			throwErrorPage('403');
		} else {
			throwErrorPage('404');
		}
	}

	// init
	if ((strlen($mailingVersion) > 0)) {
		$filter = "";
		$tmpUser = new User(sUserMgr()->getCurrentUserID());
		$tmpUserInfo = $tmpUser->get();
		$backendAllowed = $tmpUser->checkPermission('RBACKEND');
		if ((sUserMgr()->getCurrentUserID() !== sUserMgr()->getAnonymousID()) && $backendAllowed) {
			if ($mailingVersion == "null") {
				$mailingVersion = $mailing->getLatestVersion();
			}
		} else {
			$filter = "PUBLISHED";
			$mailingVersion = $mailing->getPublishedVersion(true);
		}
	} else {
		$filter = "PUBLISHED";
		$mailingVersion = $mailing->getPublishedVersion(true);
	}
	$mailing = $mailingMgr->getMailing($mailingId, $mailingVersion);

	// Mailing Properties
	$mailingInfo = $mailing->get();
	$mailingProperties = $mailing->properties->get();
	$mailingInfo = array_merge($mailingProperties, $mailingInfo);

	// Template
	$templateMgr = new Templates();
	$templateInfo = $templateMgr->getTemplate($mailingInfo['TEMPLATEID']);
	$templatefilename = $templateInfo['FILENAME'];
	$templatefullpath = $templateMgr->getDir().$templateInfo['PATH'].$templatefilename;

	// Content
	$content = $mailing->getContent();
	$mailingcnt = array('USERINFO' => $userInfo, 'FILTER' => $filter, 'CONTENTAREAS' => &$content);
	$mailingMgr->callExtensionHook('onRender', $mailingId, $mailingVersion, $mailingcnt);
	sSmarty()->assign('pageinfo', $mailingInfo);
	sSmarty()->assign('contentareas', $content);

	if (!$output_tmp) {
		$output_tmp = sSmarty()->fetch('file:'.$templatefullpath);
	}

	// 2nd pass
	sSmarty()->left_delimiter = '[!';
	sSmarty()->right_delimiter = '!]';
	sApp()->output = sSmarty()->fetch('var:'.$output_tmp);

	// 3rd pass (replace special urls with normal urls)
	sApp()->output = replaceSpecialURLs(sApp()->output, true);

	// Replace relative URLs with aboslute URLs
	sApp()->output = $mailing->absolutizeURLs(sApp()->output);

	// Output everthing if we are in preview mode
	if ($previewMode) {
		echo sApp()->output;
	}

?>