<?php

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();
$adminAllowed = $tmpUser->checkPermission('RUPDATER');

if ($adminAllowed) {
	session_start();

	//sApp()
	$this->yeager_version = $versionp;
	//$this->yeager_revision = YEAGER_REVISION;
	//$this->yeager_date = YEAGER_DATE;
	$databaseInfo = sConfig()->getVars('CONFIG/DB');
	$databaseHost = $databaseInfo[0]['host'];
	$databaseHost = explode(':', $databaseHost);
	if (count($databaseHost) > 1) {
		$databasePort = $databaseHost[1];
		$databaseHost = $databaseHost[0];
	} else {
		$databasePort = '3306';
		$databaseHost = $databaseHost[0];
	}

	$_SESSION['APPINFO'] = array(
		'VERSION'	=>	$this->yeager_version,
		'REVISION'	=>	$this->yeager_revision,
		'DATABASE_SERVER'	=>	$databaseHost,
		'DATABASE_PORT'		=>	$databasePort,
		'DATABASE_USER'		=>	$databaseInfo[0]['user'],
		'DATABASE_PASSWORD'	=>	$databaseInfo[0]['password'],
		'DATABASE_NAME'		=>	$databaseInfo[0]['db'],
		'PATH_BASE'			=>	dirname($_SERVER['SCRIPT_FILENAME']).'/'
	);

	$updateMgr = new Updater();
	$updates = $updateMgr->getUpdates();
	$currVersion = $updateMgr->current_version_string;
	$currRevision = $this->yeager_revision;
	//$currDate = explode('-', substr(YEAGER_DATE, 6, 11));
	//$currDate = gmmktime(0, 0, 0, $currDate[1], $currDate[2], $currDate[0]);
	$currDate = '';
}

$winID = $this->request->parameters['win_no'];
$smarty->assign("adminAllowed", $adminAllowed);
$smarty->assign("win_no", $winID);
$smarty->assign("updates", $updates);
$smarty->assign("current_version", $currVersion);
$smarty->assign("current_revision", $currRevision);
$smarty->assign("current_date", $currDate);
$smarty->display('file:'.$this->page_template);

?>