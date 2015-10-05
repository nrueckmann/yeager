<?php

$ygid = $this->request->parameters['yg_id'];
$objecttype = $this->request->parameters['yg_type'];
$refresh = $this->request->parameters['refresh'];
$data = explode('-', $ygid);

$user = new User(sUserMgr()->getCurrentUserID());
$userinfo = $user->get();
$userinfo['PROPS'] = $user->properties->getValues( sUserMgr()->getCurrentUserID() );

switch($objecttype) {
	case 'cblock':
		$cblockID = $data[0];
		$cb = sCblockMgr()->getCblock($cblockID);
		$objectInfo = $cb->get();
		$objectInfo['RSTAGE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $cblockID, 'RSTAGE');
		$objectInfo['NOSTAGE'] = !$objectInfo['RSTAGE'];

		if ($objectInfo['DELETED']) {
			$objectInfo['RSTAGE'] = false;
			$objectInfo['NOSTAGE'] = true;
		}

		// Get current locks for this token (and unlock them)
		$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
		$lockedObjects = sCblockMgr()->getLocksByToken($lockToken);
		foreach($lockedObjects as $lockedObject) {
			$currentObject = sCblockMgr()->getCblock($lockedObject['OBJECTID']);
			$currentObject->releaseLock($lockedObject['TOKEN']);
		}
		// Check for lock, and lock if not locked
		$lockStatus = $cb->getLock();
		if ($lockStatus['LOCKED'] == 0) {
			$lockedFailed = !$cb->acquireLock($lockToken);
		} else {
			$lockedFailed = true;
		}

		$versions = $cb->getVersions();
		for ($i = 0; $i < count($versions); $i++) {
			$uid = $versions[$i]["CREATEDBY"];
			if ($uid) {
				$user = new User($uid);
				$uinfo = $user->get();
				$uinfo['PROPS'] = $user->properties->getValues( $uid );
				$versions[$i]['USERNAME'] = $uinfo['PROPS']['LASTNAME'];
				$versions[$i]['VORNAME'] = $uinfo['PROPS']['FIRSTNAME'];
			}
		}

		$autopublish = sCblockMgr()->scheduler->getSchedule( $cblockID, 'SCH_AUTOPUBLISH' );
		$onlineversion = $cb->getPublishedVersion();
		$latestversion = $cb->getLatestApprovedVersion();
		if (!$latestversion) {
			$latestversion = 1;
			$neverpublished = true;
		}
		$latestfinalcb = sCblockMgr()->getCblock($cblockID, $latestversion);
		$latestversioninfo = $latestfinalcb->get();
		break;

	case 'page':
		$pageID = $data[0];
		$siteID = $data[1];
		$pageMgr = new PageMgr($siteID);
		$page = $pageMgr->getPage($pageID);
		$objectInfo = $page->get();
		$objectInfo['RSTAGE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RSTAGE");
		$objectInfo['NOSTAGE'] = !$objectInfo['RSTAGE'];

		if ($objectInfo['DELETED']) {
			$objectInfo['RSTAGE'] = false;
			$objectInfo['NOSTAGE'] = true;
		}

		// Get current locks for this token (and unlock them)
		$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
		$lockedObjects = $pageMgr->getLocksByToken($lockToken);
		foreach($lockedObjects as $lockedObject) {
			$currentObject = $pageMgr->getPage($lockedObject['OBJECTID']);
			$currentObject->releaseLock($lockedObject['TOKEN']);
		}
		// Check for lock, and lock if not locked
		$lockStatus = $page->getLock();
		if ($lockStatus['LOCKED'] == 0) {
			$lockedFailed = !$page->acquireLock($lockToken);
		} else {
			$lockedFailed = true;
		}

		$versions = $page->getVersions();

		for ($i = 0; $i < count($versions); $i++) {
			$user = new User($versions[$i]["CREATEDBY"]);
			$uinfo = &$user->get();
			$uinfo['PROPS'] = $user->properties->getValues( $uid );
			$versions[$i]['USERNAME'] = $uinfo['PROPS']['LASTNAME'];
			$versions[$i]['VORNAME'] = $uinfo['PROPS']['FIRSTNAME'];
		}

		$autopublish = $page->scheduler->getSchedule($pageID, 'SCH_AUTOPUBLISH');
		$onlineversion = $page->getPublishedVersion();

		$latestversion = $page->getLatestApprovedVersion();
		if (!$latestversion) {
			$latestversion = 1;
			$neverpublished = true;
		}
		$pageLatestFinalVersion = $pageMgr->getPage($pageID, $latestversion);
		$latestversioninfo = $pageLatestFinalVersion->get();
		break;
}


// Get timezone offset
$user = new User(sUserMgr()->getCurrentUserID());
foreach($autopublish as $autopublish_idx => $autopublish_item) {
	$autopublish[$autopublish_idx]['TIMESTAMP'] = TStoLocalTS($autopublish[$autopublish_idx]['TIMESTAMP']);
}
foreach($versions as $version_idx => $version_item) {
	$versions[$version_idx]['CHANGEDTS'] = TStoLocalTS($version_item['CHANGEDTS']);
}
$latestversioninfo['CHANGEDTS'] = TStoLocalTS($latestversioninfo['CHANGEDTS']);


if ($lockedFailed) {
	// Get user who locked this object
	$userWithLock = new User( $lockStatus['LOCKUID'] );
	$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
	$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
	$smarty->assign('lockedByUser', $lockedByUser );
	$objectInfo['RSTAGE'] = false;
	$objectInfo['NOSTAGE'] = true;
	$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
} else {
	$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$objectInfo['RSTAGE'].'\' );');
}

$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

$smarty->assign('userinfo', $userinfo);
$smarty->assign("versions", $versions);
$smarty->assign("onlineversion", $onlineversion);
$smarty->assign("objecttype", $objecttype);
$smarty->assign("latestversion", $latestversion);
$smarty->assign("latestversioninfo", $latestversioninfo);
$smarty->assign("autopublish", $autopublish);
$smarty->assign("neverpublished", $neverpublished);

$smarty->assign("objectInfo", $objectInfo);
$smarty->assign("refresh", $refresh );
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>