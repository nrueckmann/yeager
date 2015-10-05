<?php

header('Content-type: application/x-javascript');

$icons = new Icons();
$user = new User(sUserMgr()->getCurrentUserID());
$userinfo = $user->get();
$userinfo['PROPS'] = $user->properties->getValues( sUserMgr()->getCurrentUserID() );

// This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
function let_to_num($v) {
	$l = substr($v, -1);
	$ret = substr($v, 0, -1);
	switch(strtoupper($l)){
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
			break;
	}
	return $ret;
}

$max_uploadsize = 0;

$max_uploadsize = min(let_to_num(ini_get('post_max_size')), let_to_num(ini_get('upload_max_filesize')));
$max_uploadsize = ($max_uploadsize/(1024*1024)).'mb';

// Check if a custom login-url is set
$customLoginURL = (string)$this->config->getVar('CONFIG/DIRECTORIES/LOGINURL');
if (trim($customLoginURL) != '') {
	$smarty->assign("base", trim($customLoginURL).basename($this->request->script_name).'/');
}

$smarty->assign("webroot", sApp()->webroot);
$smarty->assign("cookiedomain", (string)$this->config->getVar("CONFIG/SESSION/COOKIES/DOMAIN"));
$smarty->assign("devmode", sConfig()->getVar("CONFIG/DEVMODE"));
$smarty->assign("max_uploadsize", $max_uploadsize);
$smarty->assign("guiSyncInterval", sConfig()->getVar("CONFIG/GUISYNC_INTERVAL"));
$smarty->assign("guiSyncTimeout", sConfig()->getVar("CONFIG/GUISYNC_TIMEOUT"));
$smarty->assign("objectRelockInterval", sConfig()->getVar("CONFIG/OBJECTRELOCK_INTERVAL"));
$smarty->assign("userinfo", $userinfo);
$smarty->assign("icon", $icons->icon);

$smarty->display('file:'.$this->page_template);

?>