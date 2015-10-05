<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$data = explode('-',$ygid );
$mailingID = $data[0];

$templateMgr = new Templates();
$mailingMgr = new MailingMgr();
$mailing = $mailingMgr->getMailing($mailingID);
if ($mailing) {
	$mailingInfo = $mailing->get();
	$mailingInfo['RWRITE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $mailingID, "RWRITE");
	$mailingInfo['RSTAGE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $mailingID, "RSTAGE");
	$mailingInfo['READONLY'] = !$mailingInfo['RWRITE'];

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
		$mailingInfo['RWRITE'] = false;
		$mailingInfo['READONLY'] = true;
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
	} else {
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$mailingInfo['RSTAGE'].'\' );');
	}

	// Check if a send is in progress (and lock if true)
	$mailingStatus = $mailing->getStatus();
	if ($mailingStatus['STATUS'] == 'INPROGRESS') {
		$userWithLock = new User( $mailingStatus['UID'] );
		$lockedByUser = $userWithLock->get( $mailingStatus['UID'] );
		$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $mailingStatus['UID'] );
		$smarty->assign('lockedByUser', $lockedByUser );
		$mailingInfo['RWRITE'] = false;
		$mailingInfo['READONLY'] = true;
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
	} else {
		if (!$lockedFailed) {
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$mailingInfo['RSTAGE'].'\' );');
		}
	}

	$templateid = $mailingInfo["TEMPLATEID"];
	$templateInfo = $templateMgr->getTemplate($templateid);
	$templateInfo["PREVIEWPATH"] = $templateMgr->getPreviewPath( $templateid );

	$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');
}

$smarty->assign("mailing_id", $mailingID );
$smarty->assign("cached", $cached );
$smarty->assign("mode", 1 );
$smarty->assign("templateInfo", $templateInfo);
$smarty->assign("mailingInfo", $mailingInfo);
$smarty->assign("win_no", $this->request->parameters['win_no']);

$smarty->display('file:'.$this->page_template);

?>