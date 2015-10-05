<?php

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();
$adminAllowed = $tmpUser->checkPermission('RUSERGROUPS');

$mode = $this->request->parameters['mode'];

if ($mode == "details") {
	$objecttype = $this->request->parameters['yg_type'];
	if ($objecttype == 'mailing') {
		// Get usergroups assigned to mailing
		$mailingID = $this->request->parameters['yg_id'];
		$mailingID = explode( '-', $mailingID );
		$mailingID = $mailingID[0];

		$mailingMgr = new MailingMgr();
		$mailing = $mailingMgr->getMailing($mailingID);
		$mailingInfo = $mailing->get();

		// Get assigned groups
		$usergroups = $mailing->getUsergroups();

		foreach($usergroups as $usergroup_idx => $usergroup) {
			$usergroups[$usergroup_idx]['RDELETE'] = true;
			$usergroups[$usergroup_idx]['SHOW_DELETE'] = true;
		}

		$object_permissions['RWRITE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $mailingID, "RWRITE");
		$object_permissions['RSTAGE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $mailingID, "RSTAGE");
		$object_permissions['READONLY'] = !$object_permissions['RWRITE'];

		// Get current locks for this token (and unlock them)
		$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
		$lockedObjects = $mailingMgr->getLocksByToken($lockToken);
		foreach($lockedObjects as $lockedObject) {
			$currentObject = $mailingMgr->getMailing($lockedObject['OBJECTID']);
			$currentObject->releaseLock($lockedObject['TOKEN']);
		}

		// Check for lock, and lock if not locked
		$lockStatus = $mailing->getLock();
		if ($lockStatus['LOCKED'] == 0) {
			$lockedFailed = !$mailing->acquireLock($lockToken);
		} else {
			$lockedFailed = true;
		}
		if ($lockedFailed) {
			// Get user who locked this object
			$userWithLock = new User( $lockStatus['LOCKUID'] );
			$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
			$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
			$smarty->assign('lockedByUser', $lockedByUser );
			$object_permissions['RWRITE'] = false;
			$object_permissions['READONLY'] = true;
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
		} else {
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$object_permissions['RSTAGE'].'\' );');
		}

		// Check if a send is in progress (and lock if true)
		$mailingStatus = $mailing->getStatus();
		if ($mailingStatus['STATUS'] == 'INPROGRESS') {
			$userWithLock = new User( $mailingStatus['UID'] );
			$lockedByUser = $userWithLock->get( $mailingStatus['UID'] );
			$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $mailingStatus['UID'] );
			$smarty->assign('lockedByUser', $lockedByUser );
			$object_permissions['RWRITE'] = false;
			$object_permissions['READONLY'] = true;
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
		} else {
			if (!$lockedFailed) {
				$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$object_permissions['RSTAGE'].'\' );');
			}
		}

		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');
	} else {
		// Get roles assigned to user
		$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");
		$anonGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONGROUPID");

		$userID = $this->request->parameters['yg_id'];
		$userID = explode( '-', $userID );
		$userID = (int)$userID[0];

		$user = new User($userID);
		$usergroups = $user->getUsergroups();
		foreach($usergroups as $usergroup_idx => $usergroup) {
			$usergroups[$usergroup_idx]['RDELETE'] = sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $usergroup['ID'], 'RDELETE');
			if ($userID) {
				if (($userID == sUserMgr()->getAdministratorID()) && ((int)$usergroups[$usergroup_idx]['ID'] == $rootGroupId)) {
					$usergroups[$usergroup_idx]['RDELETE'] = false;
					$usergroups[$usergroup_idx]['SHOW_DELETE'] = false;
				} else {
					$usergroups[$usergroup_idx]['RDELETE'] = true;
					$usergroups[$usergroup_idx]['SHOW_DELETE'] = true;
				}
			}
		}

		// FIXME
		$realUsergroups = array();
		foreach($usergroups as $usergroup_idx => $usergroup) {
			if ( (sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $usergroup['ID'], 'RREAD')) ||
				 ($userID == sUserMgr()->getCurrentUserID()) ) {
				$realUsergroups[] = $usergroup;
			}
		}
		$usergroups = $realUsergroups;

		$rroles = true;
		$smarty->assign("rroles", $rroles );
	}

	$smarty->assign("empty_item", $empty_item );
	$smarty->assign("usergroups", $usergroups );
} else {
	$usergroups = sUsergroups()->getList();

	$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");
	$anonGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONGROUPID");

	foreach($usergroups as $usergroup_idx => $usergroup) {
		$usergroups[$usergroup_idx]['RDELETE'] = sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $usergroup['ID'], 'RDELETE');
	}

	$realUsergroups = array();
	foreach($usergroups as $usergroup_idx => $usergroup) {
		if (sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $usergroup['ID'], 'RREAD')) {
			$realUsergroups[] = $usergroup;
		}
	}
	$usergroups = $realUsergroups;
}

if ($objecttype == 'mailing') {
	// Check if a send is in progress (and lock if true)
	$mailingStatus = $mailing->getStatus();
	if ($mailingStatus['STATUS'] == 'INPROGRESS') {
		$userWithLock = new User( $mailingStatus['UID'] );
		$lockedByUser = $userWithLock->get( $mailingStatus['UID'] );
		$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $mailingStatus['UID'] );
		$smarty->assign( 'lockedByUser', $lockedByUser );
		$object_permissions['RWRITE'] = false;
	}
	$object_permissions['READONLY'] = !$object_permissions['RWRITE'];
} else {
	$object_permissions = Array();
	$object_permissions["RWRITE"] = sUsergroups()->permissions->check( sUserMgr()->getCurrentUserID(), 'RUSERS' );
	if (sUserMgr()->getAnonymousID() == (int)$userID) {
		$object_permissions['RWRITE'] = 0;
		$object_permissions['READONLY'] = 1;
	}
}

$smarty->assign("object_permissions", $object_permissions );

// Check rights
$rroles = sUsergroups()->permissions->check( sUserMgr()->getCurrentUserID(), 1, 'RUSERGROUPS' );
$empty_item = array('ID' => '#<<new_id>>', 'NAME' => '#<<new_name>>', 'SHOW_DELETE' => true);
$smarty->assign("mode", $mode );
$smarty->assign("rootGroupId", $rootGroupId );
$smarty->assign("anonGroupId", $anonGroupId );
$smarty->assign("rroles", $rroles );
$smarty->assign("objecttype", $objecttype );
$smarty->assign("yg_id", $this->request->parameters['yg_id'] );
$smarty->assign("empty_item", $empty_item );
$smarty->assign("usergroups", $usergroups );
if ($this->request->parameters['action'] == 'addrole') {
	$smarty->assign('selectiondialog', true);
}
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->assign("action", $this->request->parameters['action']);
$smarty->assign("opener_reference", $this->request->parameters['opener_reference']);
$smarty->display('file:'.$this->page_template);

?>