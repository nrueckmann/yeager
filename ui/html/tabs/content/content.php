<?php

$selectiondialog = $this->request->parameters['selectiondialog'];
if (($selectiondialog) || ($selectiondialog == "1")) {
	$smarty->assign("displaymode", 'dialog');
}
if ($this->request->parameters['displaymode'] != '') {
	$smarty->assign("displaymode", $this->request->parameters['displaymode']);
}
$objecttype = $this->request->parameters['yg_type'];
$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$data = explode('-', $ygid);
$pageID = $data[0];
$siteID = $data[1];

$templateMgr = new Templates();

if ($pageID) {
	switch ($objecttype) {
		case 'mailing':
			$mailingMgr = new MailingMgr();
			if ($pageID) {
				$mailing = $mailingMgr->getMailing($pageID);
				$mailingInfo = $pageInfo = $mailing->get();

				$pageInfo['RWRITE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RWRITE");
				$pageInfo['RSTAGE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RSTAGE");

				if ($pageInfo['DELETED']==1) {
					$pageInfo['RWRITE'] = false;
					$pageInfo['RSTAGE'] = false;
				}

				// Get current locks for this token (and unlock them)
				$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
				$lockedObjects = $mailingMgr->getLocksByToken($lockToken);
				foreach($lockedObjects as $lockedObject) {
					if ($lockedObject['OBJECTID']) {
						$currentObject = $mailingMgr->getMailing($lockedObject['OBJECTID']);
						$currentObject->releaseLock($lockedObject['TOKEN']);
					}
				}

				// Check for lock, and lock if not locked
				$lockStatus = $mailing->getLock();
				if ($lockStatus['LOCKED'] == 0) {
					$lockedFailed = !$mailing->acquireLock($lockToken);
				} else {
					$lockedFailed = true;
				}

				$contentareas = $mailing->getContentInternal();
				$smarty->assign('mailingInfo', $mailingInfo);
			}
			break;

		case 'cblock':
			$cblockMgr = sCblockMgr();
			if ($pageID) {
				$cb = sCblockMgr()->getCblock($pageID);
				$cblockInfo = $pageInfo = $cb->get();
				$entrymasksinfo = $cb->getEntrymasks();

				$pageInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RWRITE");
				$pageInfo['RSTAGE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RSTAGE");

				if ($cblockInfo['DELETED']==1) {
					$pageInfo['RWRITE'] = false;
					$pageInfo['RSTAGE'] = false;
				}

				// Get current locks for this token (and unlock them)
				$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
				$lockedObjects = $cblockMgr->getLocksByToken($lockToken);
				foreach($lockedObjects as $lockedObject) {
					$currentObject = $cblockMgr->getCblock($lockedObject['OBJECTID']);
					$currentObject->releaseLock($lockedObject['TOKEN']);
				}

				// Check for lock, and lock if not locked
				$lockStatus = $cb->getLock();
				if ($lockStatus['LOCKED'] == 0) {
					$lockedFailed = !$cb->acquireLock($lockToken);
				} else {
					$lockedFailed = true;
				}

				$new_list = array();
				$contentareas = array();
				$empty_contentarea = array(
					'ID' => '0',		// FAKE CONTENTAREAID (REAL NEVER HAVE '0' AS ID)
					'TEMPLATE' => '0',	// FAKE TEMPLATEID (REAL NEVER HAVE '0' AS ID)
					'CODE' => ($itext['TXT_CONTENT']!='')?($itext['TXT_CONTENT']):('$TXT_CONTENT'),
					'NAME' => ($itext['TXT_CONTENT']!='')?(strtolower($itext['TXT_CONTENT'])):(strtolower('$TXT_CONTENT')),
					'LIST' => array()
				);
				foreach($entrymasksinfo as $entrymasksinfo_item) {
					$new_contentarea = $empty_contentarea;
					$new_contentarea['LIST'] = array(
						0 => array(
							'OBJECTID' => $pageID,						// REAL CONTENTBLOCKID
							'LINKID' => $entrymasksinfo_item['LINKID']	// REAL ENTRYMASKINFO
						)
					);
					array_push($contentareas, $new_contentarea);
				}
				if (count($contentareas) == 0) {
					array_push($contentareas, $empty_contentarea);
				}
			}
			break;

		case 'page':
			if (!$siteID || !$pageID) break;

			$pageMgr = new PageMgr($siteID);
			$page = $pageMgr->getPage($pageID);

			$pageInfo = $page->get();

			$url = $page->getUrl();
			$smarty->assign('page_url', $url);

			$pageInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RWRITE");
			$pageInfo['RSTAGE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RSTAGE");

			if ($pageInfo['DELETED']==1) {
				$pageInfo['RWRITE'] = false;
				$pageInfo['RSTAGE'] = false;
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

			$contentareas = $page->getContentInternal();
			break;
	}
}

if ($lockedFailed) {
	// Get user who locked this object
	$userWithLock = new User( $lockStatus['LOCKUID'] );
	$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
	$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
	$smarty->assign('lockedByUser', $lockedByUser );
	$pageInfo['RWRITE'] = false;
	$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
} else {
	$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$pageInfo['RSTAGE'].'\' );');
}

if ($objecttype == 'mailing') {
	// Check if a send is in progress (and lock if true)
	$mailingStatus = $mailing->getStatus();
	if ($mailingStatus['STATUS'] == 'INPROGRESS') {
		$userWithLock = new User( $mailingStatus['UID'] );
		$lockedByUser = $userWithLock->get( $mailingStatus['UID'] );
		$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $mailingStatus['UID'] );
		$smarty->assign('lockedByUser', $lockedByUser );
		$pageInfo['RWRITE'] = false;
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
	} else {
		if (!$lockedFailed) {
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$pageInfo['RSTAGE'].'\' );');
		}
	}
}

$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

$smarty->assign('page_id', $pageID);
$smarty->assign('site_id', $siteID);
$smarty->assign("objecttype", $objecttype);
$smarty->assign("site", $siteID);
$smarty->assign("pageInfo", $pageInfo);
$smarty->assign("refresh", $refresh);
$smarty->assign("page", $pageID);
$smarty->assign("contentareas", $contentareas);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>