<?php

$ygid = $this->request->parameters['yg_id'];
$objecttype = $this->request->parameters['yg_type'];
$refresh = $this->request->parameters['refresh'];
$data = explode('-',$ygid );
$objectID = $data[0];
$siteID = $data[1];

switch ($objecttype) {
	case 'page':
		$pageMgr = new PageMgr($siteID);
		$page = $pageMgr->getPage($objectID);
		$objectInfo = $page->get();

		$tags = $page->tags->getAssigned();

		$object_permissions = array();
		$object_permissions['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
		$object_permissions['READONLY'] = !$page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
		$object_permissions['RSTAGE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

		if ($objectInfo['DELETED']) {
			$object_permissions['RWRITE'] = false;
			$object_permissions['READONLY'] = true;
			$object_permissions['RSTAGE'] = false;
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

		for ($i = 0; $i < count($tags); $i++) {
			$myparents = $page->tags->tree->getParents($tags[$i]['ID']);
			$parents = array();
			for ($p = 0; $p < count($myparents)-1; $p++) {
				$parents[$p]['INFO'] = $page->tags->get($myparents[$p]);
			}
			$tags[$i]['PARENTS'] = $parents;
		}
		break;
	case 'mailing':
		$mailing = sMailingMgr()->getMailing($objectID);
		$objectInfo = $mailing->get();

		$tags = $mailing->tags->getAssigned();

		$object_permissions = array();
		$object_permissions['RWRITE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
		$object_permissions['READONLY'] = !$mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
		$object_permissions['RSTAGE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

		if ($objectInfo['DELETED']) {
			$object_permissions['RWRITE'] = false;
			$object_permissions['READONLY'] = true;
			$object_permissions['RSTAGE'] = false;
		}

		// Get current locks for this token (and unlock them)
		$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
		$lockedObjects = sMailingMgr()->getLocksByToken($lockToken);
		foreach($lockedObjects as $lockedObject) {
			$currentObject = sMailingMgr()->getMailing($lockedObject['OBJECTID']);
			$currentObject->releaseLock($lockedObject['TOKEN']);
		}
		// Check for lock, and lock if not locked
		$lockStatus = $mailing->getLock();
		if ($lockStatus['LOCKED'] == 0) {
			$lockedFailed = !$mailing->acquireLock($lockToken);
		} else {
			$lockedFailed = true;
		}

		for ($i = 0; $i < count($tags); $i++) {
			$myparents = $mailing->tags->tree->getParents($tags[$i]['ID']);
			$parents = array();
			for ($p = 0; $p < count($myparents)-1; $p++) {
				$parents[$p]['INFO'] = $mailing->tags->get($myparents[$p]);
			}
			$tags[$i]['PARENTS'] = $parents;
		}
		break;
	case 'file':
	case 'filefolder':
		$file = new File($objectID);
		$latestFinalVersion = $file->getLatestApprovedVersion();
		$file = new File($objectID, $latestFinalVersion);
		$objectInfo = $file->get();

		$tags = $file->tags->getAssigned();

		$object_permissions = array();
		$object_permissions['RWRITE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
		$object_permissions['READONLY'] = !$file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");

		if ($objectInfo['DELETED']==1) {
			$object_permissions['RWRITE'] = false;
			$object_permissions['READONLY'] = true;
		}

		// Get current locks for this token (and unlock them)
		$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
		$lockedObjects = sFileMgr()->getLocksByToken($lockToken);
		foreach($lockedObjects as $lockedObject) {
			$currentObject = new File($lockedObject['OBJECTID']);
			$currentObject->releaseLock($lockedObject['TOKEN']);
		}
		// Check for lock, and lock if not locked
		$lockStatus = $file->getLock();
		if ($lockStatus['LOCKED'] == 0) {
			$lockedFailed = !$file->acquireLock($lockToken);
		} else {
			$lockedFailed = true;
		}

		for ($i = 0; $i < count($tags); $i++) {
			$myparents = $file->tags->tree->getParents($tags[$i]['ID']);
			$parents = array();
			for ($p = 0; $p < count($myparents)-1; $p++) {
				$parents[$p]['INFO'] = $file->tags->get($myparents[$p]);
			}
			$tags[$i]['PARENTS'] = $parents;
		}
		break;
	case 'cblock':
		$cb = sCblockMgr()->getCblock($objectID);
		$tags = $cb->tags->getAssigned();
		$objectInfo = $cb->get();

		$object_permissions = array();
		$object_permissions['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
		$object_permissions['READONLY'] = !$cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
		$object_permissions['RSTAGE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

		if ($objectInfo['DELETED']) {
			$object_permissions['RWRITE'] = false;
			$object_permissions['READONLY'] = true;
			$object_permissions['RSTAGE'] = false;
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

		for ($i = 0; $i < count($tags); $i++) {
			$myparents = sCblockMgr()->tags->tree->getParents($tags[$i]['ID']);
			$parents = array();
			for ($p = 0; $p < count($myparents)-1; $p++) {
				$parents[$p]['INFO'] = sCblockMgr()->tags->get($myparents[$p]);
			}
			$tags[$i]['PARENTS'] = $parents;
		}
		break;
}

if ($lockedFailed) {
	// Get user who locked this object
	$userWithLock = new User( $lockStatus['LOCKUID'] );
	$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
	$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
	$smarty->assign('lockedByUser', $lockedByUser );
	$object_permissions['RWRITE'] = false;
	$object_permissions['READONLY'] = true;
	if (($objecttype=='cblock') || ($objecttype=='page')) {
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
	}
} else {
	if (($objecttype=='cblock') || ($objecttype=='page')) {
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$object_permissions['RSTAGE'].'\' );');
	}
}

$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

$smarty->assign('page_id', $objectID);
$smarty->assign('site_id', $siteID);
$smarty->assign("page", $objectID);
$smarty->assign("objecttype", $objecttype);
$smarty->assign("refresh", $refresh );
$smarty->assign("tags", $tags);
$smarty->assign("ygid", $ygid);
$smarty->assign("object_permissions", $object_permissions);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>