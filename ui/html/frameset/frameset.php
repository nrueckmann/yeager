<?php

// Check if a custom login-url is set
$customLoginURL = (string)$this->config->getVar('CONFIG/DIRECTORIES/LOGINURL');
if (trim($customLoginURL) != '') {
	if ((strpos($_SERVER['REDIRECT_URL'],$customLoginURL) === false) &&
		 (strpos($_SERVER['REQUEST_URI'],$customLoginURL) === false)) {
		$header = $_SERVER['SERVER_PROTOCOL'].' 403 Forbidden';
		header($header);
		echo $header;
		die();
	}
}

$jsQueue = new JSQueue(NULL);
$queueid = $jsQueue->getLastQueueId();

// check browser
$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

if (preg_match('/opera/i', $userAgent)) {
	$browser = 'opera';
} elseif (preg_match('/safari/i', $userAgent)) {
	$browser = 'safari';
} elseif (preg_match('/chrome/i', $userAgent)) {
	$browser = 'chrome';
} elseif (preg_match('/msie/i', $userAgent) || preg_match('/trident/i', $userAgent)) {
	$browser = 'msie';
} elseif (preg_match('/firefox/i', $userAgent)) {
	$browser = 'firefox';
} else {
	$browser = 'unrecognized';
}

// check version
// finally get the correct version number
$known = array('version', $browser, 'other');
$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
if (!preg_match_all($pattern, $userAgent, $matches)) {
	// try testing for IE >= 11
	$pattern = '/trident(.*)rv:(?<version>.*)\)/';
	if (!preg_match_all($pattern, $userAgent, $matches)) {
		// we have no matching number just continue
	}
}

// see how many we have
$i = count($matches['browser']);
if (($i != 1) && ($i != NULL)) {
	//we will have two since we are not using 'other' argument yet
	//see if version is before or after the name
	if (strripos($userAgent,"version") < strripos($userAgent, $browser)){
		$version = $matches['version'][0];
	} else {
		$version = $matches['version'][1];
	}
} else {
	$version = $matches['version'][0];
}

// check if we have a number
if ($version == null || $version == "") { $version = "unrecognized"; }

$baseversion = explode(".", $version);
$baseversion = intval($baseversion[0]);

// check os
if (preg_match('/linux/', $userAgent)) {
	$platform = 'linux';
} elseif (preg_match('/macintosh|mac os x/', $userAgent)) {
	$platform = 'mac';
} elseif (preg_match('/windows|win32/', $userAgent)) {
	$platform = 'windows';
} else {
	$platform = 'unrecognized';
}

// redirect in case deprecated browser
if ((($browser == "msie") && ($baseversion < 8)) ||
	(($browser == "safari") && ($baseversion < 4)) ||
	(($browser == "chrome") && ($baseversion < 10)) ||
	(($browser == "opera") && ($baseversion < 10)) ||
	(($browser == "firefox") && ($baseversion < 3)) ||
	($browser == "unrecognized")) {
		http_redirect($this->base.'deprecated_browser');
}

$action = $this->request->parameters['action'];
if ($action == 'passwordreset') {
	$userToken = sRequest()->parameters['token'];

	if ($userId = sUserMgr()->getUserIdByToken($userToken)) {
		$user = new User($userId);

		$smarty->assign('passwordreset', true);
		$smarty->assign('passwordreset_token', $userToken);
		if (sRequest()->parameters['newuser'] == '1') {
			$smarty->assign('newuser', true);
		}

	}
}

$windowcfgxml = simplexml_load_string($smarty->fetch('file:'.getrealpath($this->approot)."/ui/html/windows/windows.xml"));
$smarty->assign("windowconfig", json_encode($windowcfgxml));
$smarty->assign('itext_js', $itext_js);
$smarty->assign('lang', $lang);

$user = new User(sUserMgr()->getCurrentUserID());
$userinfo = $user->get();
$backendAllowed = $user->checkPermission('RBACKEND');
if (!$backendAllowed) {
	$this->session->setPSessionVar('username', '');
	$this->session->setPSessionVar('password', '');
	$this->session->setPSessionVar('isvalidated', false);
}
$userinfo['PROPS'] = $user->properties->getValues( sUserMgr()->getCurrentUserID() );
$smarty->assign("RPAGES", $user->checkPermission( "RPAGES"));
$smarty->assign("RCONTENTBLOCKS", $user->checkPermission( "RCONTENTBLOCKS"));
$smarty->assign("RCOMMENTS", $user->checkPermission( "RCOMMENTS"));
$smarty->assign("RMAILINGS", $user->checkPermission( "RMAILINGS"));
$smarty->assign("RFILES", $user->checkPermission( "RFILES"));
$smarty->assign("RTAGS", $user->checkPermission( "RTAGS"));
$smarty->assign("RUSERS", $user->checkPermission( "RUSERS"));
$smarty->assign("RUSERGROUPS", $user->checkPermission( "RUSERGROUPS"));
$smarty->assign("RDATA", $user->checkPermission( "RDATA"));
$smarty->assign("RSITES", $user->checkPermission( "RSITES"));
$smarty->assign("RTEMPLATES", $user->checkPermission( "RTEMPLATES"));
$smarty->assign("REXTENSIONS_PAGE", $user->checkPermission( "REXTENSIONS_PAGE"));
$smarty->assign("REXTENSIONS_MAILING", $user->checkPermission( "REXTENSIONS_MAILING"));
$smarty->assign("REXTENSIONS_FILE", $user->checkPermission( "REXTENSIONS_FILE"));
$smarty->assign("REXTENSIONS_CBLOCK", $user->checkPermission( "REXTENSIONS_CBLOCK"));
$smarty->assign("RENTRYMASKS", $user->checkPermission( "RENTRYMASKS"));
$smarty->assign("REXPORT", $user->checkPermission( "REXPORT"));
$smarty->assign("RIMPORT", $user->checkPermission( "RIMPORT"));
$smarty->assign("REXTENSIONS_CBLISTVIEW", $user->checkPermission("REXTENSIONS_CBLISTVIEW"));
$smarty->assign("RUPDATER", $user->checkPermission( "RUPDATER"));
$smarty->assign("RPROPERTIES", $user->checkPermission( "RPROPERTIES"));
$smarty->assign("RFILETYPES", $user->checkPermission( "RFILETYPES"));
$smarty->assign("RCOMMENTCONFIG", $user->checkPermission( "RCOMMENTCONFIG"));
$smarty->assign("RMAILINGCONFIG", $user->checkPermission( "RMAILINGCONFIG"));
$smarty->assign("RVIEWS",$user->checkPermission( "RVIEWS"));
$smarty->assign("browser", $browser);
$smarty->assign("browserversion", $browserversion);
$smarty->assign("platform", $platform);
$smarty->assign("user_session", $koala->genSequence() );
$smarty->assign("username", $userinfo['PROPS']['FIRSTNAME'].' '.$userinfo['PROPS']['LASTNAME']);
$smarty->assign("userid", sUserMgr()->getCurrentUserID());
$smarty->assign("preview", $this->request->parameters['preview']);
$smarty->assign("objecttype", $this->request->parameters['objecttype']);
$smarty->assign("objectid", $this->request->parameters['id']);
$smarty->assign("objectsite", $this->request->parameters['site']);
$smarty->assign("objectview", $this->request->parameters['view']);
$smarty->assign("previewversion", $this->request->parameters['version']);
$smarty->assign("devmode", (string)sApp()->devmode);
$smarty->assign("queueid", $queueid);
$smarty->display('file:'.$this->page_template);

?>