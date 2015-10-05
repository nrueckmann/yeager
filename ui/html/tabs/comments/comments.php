<?php

$objectType = $this->request->parameters['yg_type'];
$objectYgID = $this->request->parameters['yg_id'];
$displayType = $this->request->parameters['type'];
$winID = $this->request->parameters['win_no'];
$objectID = explode('-', $objectYgID);
$siteID = $objectID[1];
$objectID = $objectID[0];
$object_permissions = array();

$filterObjectType = $this->request->parameters['commentfilter_objecttype'];
$filterStatus = $this->request->parameters['commentfilter_status'];
$filterTimeframe = $this->request->parameters['commentfilter_timeframe'];
$filterRights = $this->request->parameters['commentfilter_rights'];

$userinfo = $user->get();
$userinfo['PROPS'] = $user->properties->getValues( sUserMgr()->getCurrentUserID() );

// Default Filters
if (!$filterObjectType) {
	$filterObjectType = 'ALL';
}
if (!$filterTimeframe) {
	$filterTimeframe = 'ALL';
}
if (!$filterStatus) {
	$filterStatus = 'ALL';
}
if (!$filterRights) {
	$filterRights = 'ALL';
}

$filterArray = array();
if ($displayType == 'allcomments') {
	switch($filterObjectType) {
		case 'ALL':
			/* Nothing to see here */
			break;
		case 'PAGES':
			$filterArray[] = array(
				'TYPE' => 'OBJECTTYPE',
				'OPERATOR' => 'is',
				'VALUE' => 'PAGE'
			);
			break;
		case 'CONTENT':
			$filterArray[] = array(
				'TYPE' => 'OBJECTTYPE',
				'OPERATOR' => 'is',
				'VALUE' => 'CO'
			);
			break;
		case 'FILES':
			$filterArray[] = array(
				'TYPE' => 'OBJECTTYPE',
				'OPERATOR' => 'is',
				'VALUE' => 'FILE'
			);
			break;
	}
	switch($filterRights) {
		case 'ALL':
			/* Nothing to see here */
			break;
		case 'ONLY_MOD':
			$filterArray[] = array(
				'TYPE' => 'MODRIGHT',
				'OPERATOR' => 'is',
				'VALUE' => '1'
			);
			break;
		case 'ONLY_COMMENT':
			$filterArray[] = array(
				'TYPE' => 'COMMENTRIGHT',
				'OPERATOR' => 'is',
				'VALUE' => '1'
			);
			break;
	}
}
switch($filterStatus) {
	case 'ALL':
		/* Nothing to see here */
		break;
	case 'OK':
		$filterArray[] = array(
			'TYPE' => 'APPROVED',
			'OPERATOR' => 'is',
			'VALUE' => 1
		);
		$filterArray[] = array(
			'TYPE' => 'SPAM',
			'OPERATOR' => 'is',
			'VALUE' => 0
		);
		break;
	case 'UNAPPROVED':
		$filterArray[] = array(
			'TYPE' => 'APPROVED',
			'OPERATOR' => 'is',
			'VALUE' => 0
		);
		break;
	case 'SPAM':
		$filterArray[] = array(
			'TYPE' => 'SPAM',
			'OPERATOR' => 'is',
			'VALUE' => 1
		);
		break;
}

switch($filterTimeframe) {
	case 'ALL':
		/* Nothing to see here */
		break;
	case 'LAST_WEEK':
		$filterArray[] = array(
			'TYPE' => 'CREATEDTS',
			'OPERATOR' => 'is_bigger',
			'VALUE' => (time()-604800)
		);
		break;
	case 'LAST_2_WEEKS':
		$filterArray[] = array(
			'TYPE' => 'CREATEDTS',
			'OPERATOR' => 'is_bigger',
			'VALUE' => (time()-1209600)
		);
		break;
	case 'LAST_4_WEEKS':
		$filterArray[] = array(
			'TYPE' => 'CREATEDTS',
			'OPERATOR' => 'is_bigger',
			'VALUE' => (time()-2419200)
		);
		break;
	case 'LAST_8_WEEKS':
		$filterArray[] = array(
			'TYPE' => 'CREATEDTS',
			'OPERATOR' => 'is_bigger',
			'VALUE' => (time()-4838400)
		);
		break;
	default:
		list ($timefrom, $timetill) = explode("###", $filterTimeframe);

		$timefrom = TSfromLocalTS(strtotime($timefrom));
		$timetill = TSfromLocalTS(strtotime($timetill) + 24*60*60);
		$filterArray[] = array(
			'TYPE' => 'CREATEDTS',
			'OPERATOR' => 'is_bigger',
			'VALUE' => $timefrom
		);
		$filterArray[] = array(
			'TYPE' => 'CREATEDTS',
			'OPERATOR' => 'is_smaller',
			'VALUE' => $timetill
		);
		break;
}

switch($objectType) {
	case 'page':
		$pageMgr = new PageMgr($siteID);
		$page = $pageMgr->getPage($objectID);
		$objectInfo = $page->get();
		$commentsObject = $page->comments;

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

		// Check permissions
		$object_permissions['RMODERATE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, 'RMODERATE');
		$object_permissions['RCOMMENT'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, 'RCOMMENT');
		break;
	case 'file':
		$fileMgr = sFileMgr();
		$file = sFileMgr()->getFile($objectID);
		$objectInfo = $file->get();
		$commentsObject = $file->comments;

		// Get current locks for this token (and unlock them)
		$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
		$lockedObjects = $fileMgr->getLocksByToken($lockToken);
		foreach($lockedObjects as $lockedObject) {
			$currentObject = sFileMgr()->getFile($lockedObject['OBJECTID']);
			$currentObject->releaseLock($lockedObject['TOKEN']);
		}
		// Check for lock, and lock if not locked
		$lockStatus = $file->getLock();
		if ($lockStatus['LOCKED'] == 0) {
			$lockedFailed = !$file->acquireLock($lockToken);
		} else {
			$lockedFailed = true;
		}

		// Check permissions
		$object_permissions['RMODERATE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, 'RMODERATE');
		$object_permissions['RCOMMENT'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, 'RCOMMENT');
		break;
	case 'cblock':
		$cb = sCblockMgr()->getCblock($objectID);
		$objectInfo = $cb->get();
		$commentsObject = $cb->comments;

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

		// Check permissions
		$object_permissions['RMODERATE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, 'RMODERATE');
		$object_permissions['RCOMMENT'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, 'RCOMMENT');
		break;
}

if ($displayType == 'allcomments') {
	// Get amount of comments
	$commentsObject = new Comments();
	$commentsCount = $commentsObject->getAllCommentsCount( $filterArray );

	// for paging
	$pageDirInfo = calcPageDir($commentsCount, 'created');	// X
	$pageDirOrderBy = $pageDirInfo['pageDirOrderBy'];
	$pageDirLimit = $pageDirInfo['pageDirLimit'];
	// END for paging

	$assignedComments = $commentsObject->getAllComments( $filterArray, $pageDirLimit );

	$fileMgr = sFileMgr();
	foreach($assignedComments as $assignedCommentIdx => $assignedComment) {
		switch($assignedComment['OBJECTTYPE']) {
			case 'PAGE':
				$PageMgr = new PageMgr( $assignedComment['SITEID'] );
				$assignedComments[$assignedCommentIdx]['PARENTS'] = $PageMgr->getParents($assignedComment['OBJECTID']);
				$cPage = $PageMgr->getPage($assignedComment['OBJECTID']);
				$cPageInfo = $cPage->get();
				$cPageInfo['RWRITE'] = $cPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $assignedComment['OBJECTID'], "RWRITE");
				$cPageInfo['RDELETE'] = $cPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $assignedComment['OBJECTID'], "RDELETE");
				$assignedComments[$assignedCommentIdx]['PAGEINFO'] = $cPageInfo;
				$iconData = getIconForPage($cPageInfo);
				$assignedComments[$assignedCommentIdx]['ICON'] = $iconData['iconclass'];
				$assignedComments[$assignedCommentIdx]['STYLE'] = $iconData['style'];
				$assignedComments[$assignedCommentIdx]['HASCHANGED'] = $assignedComments[$assignedCommentIdx]['HASCHANGED'];
				$assignedComments[$assignedCommentIdx]['RMODERATE'] = $cPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $assignedComment['OBJECTID'], 'RMODERATE');
				$assignedComments[$assignedCommentIdx]['RCOMMENT'] = $cPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $assignedComment['OBJECTID'], 'RCOMMENT');
				$commentsObject = $cPage->comments;
				break;
			case 'CO':
				$assignedComments[$assignedCommentIdx]['PARENTS'] = sCblockMgr()->getParents( $assignedComment['OBJECTID'] );
				array_pop( $assignedComments[$assignedCommentIdx]['PARENTS'] );
				$acb = sCblockMgr()->getCblock($assignedComment['OBJECTID']);
				if ($acb) {
					$assignedComments[$assignedCommentIdx]['CBLOCKINFO'] = $acb->get();
					$assignedComments[$assignedCommentIdx]['CBLOCKINFO']['RWRITE'] = $acb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $assignedComment['OBJECTID'], "RWRITE");
					$assignedComments[$assignedCommentIdx]['CBLOCKINFO']['RDELETE'] = $acb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $assignedComment['OBJECTID'], "RDELETE");
					$styleData = getStyleForContentblock($assignedComments[$assignedCommentIdx]['CBLOCKINFO']);
					$assignedComments[$assignedCommentIdx]['STYLE'] = $styleData;
					$assignedComments[$assignedCommentIdx]['HASCHANGED'] = $assignedComments[$assignedCommentIdx]['CBLOCKINFO']['HASCHANGED'];
					$assignedComments[$assignedCommentIdx]['RMODERATE'] = $acb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $assignedComment['OBJECTID'], 'RMODERATE');
					$assignedComments[$assignedCommentIdx]['RCOMMENT'] = $acb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $assignedComment['OBJECTID'], 'RCOMMENT');
					$commentsObject = $acb->comments;
				}
				break;
			case 'FILE':
				$assignedComments[$assignedCommentIdx]['PARENTS'] = $fileMgr->getParents( $assignedComment['OBJECTID'] );
				array_pop( $assignedComments[$assignedCommentIdx]['PARENTS'] );
				$file = sFileMgr()->getFile($assignedComment['OBJECTID']);
				if ($file) {
					$assignedComments[$assignedCommentIdx]['FILEINFO'] = $file->get();
					$assignedComments[$assignedCommentIdx]['RMODERATE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $assignedComment['OBJECTID'], 'RMODERATE');
					$assignedComments[$assignedCommentIdx]['RCOMMENT'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $assignedComment['OBJECTID'], 'RCOMMENT');
					$commentsObject = $file->comments;
				}
				break;
		}

		if ($assignedComments[$assignedCommentIdx]['CREATEDTS']) {
			$assignedComments[$assignedCommentIdx]['CREATEDTS'] = TStoLocalTS($assignedComments[$assignedCommentIdx]['CREATEDTS']);
			$assignedComments[$assignedCommentIdx]['CHANGEDTS'] = TStoLocalTS($assignedComments[$assignedCommentIdx]['CHANGEDTS']);
		}

		$assignedComments[$assignedCommentIdx]['STATUS'] = $commentsObject->getStatus( $assignedComment['OBJECTID'] );
	}
} else {
	// Get amount of comments
	$commentsCount = $commentsObject->getCommentsCount( $filterArray );

	// for paging
	$pageDirInfo = calcPageDir($commentsCount, 'created');	// X
	$pageDirOrderBy = $pageDirInfo['pageDirOrderBy'];
	$pageDirLimit = $pageDirInfo['pageDirLimit'];
	// END for paging

	$assignedComments = $commentsObject->getComments( $filterArray, $pageDirLimit, false );
	$commentStatus = $commentsObject->getStatus();

	foreach($assignedComments as $assignedCommentIdx => $assignedComment) {
		$assignedComments[$assignedCommentIdx]['CREATEDTS'] = TStoLocalTS($assignedComments[$assignedCommentIdx]['CREATEDTS']);
	}
}

if ($displayType == 'allcomments') {
	foreach($assignedComments as $currentCommentIdx => $currentComment) {
		$currUser = sUserMgr()->getUser($currentComment['USERID']);
		$currUserInfo = $currUser->get();
		$assignedComments[$currentCommentIdx]['USERNAME'] = trim($currUserInfo['FIRSTNAME'].' '.$currUserInfo['LASTNAME']);
	}
} else {
	// Get user information for comments
	foreach($assignedComments as $currentCommentIdx => $currentComment) {
		$assignedComments[$currentCommentIdx]['STATUS'] = $commentStatus;
	}

	// Only lock/unlock if in object-related mode
	if ($lockedFailed) {
		// Get user who locked this object
		$userWithLock = new User( $lockStatus['LOCKUID'] );
		$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
		$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
		$smarty->assign('lockedByUser', $lockedByUser );
	}

	$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

	// Update comment count
	$koala->queueScript('$K.yg_updateCommentCount(\'wid_'.$this->request->parameters['win_no'].'\', \''.$commentsCount.'\');');

	// Update comment status
	if ($commentStatus) {
		$koala->queueScript('$K.yg_setCommentStatus(\'wid_'.$this->request->parameters['win_no'].'\', \'open\');');
	} else {
		$koala->queueScript('$K.yg_setCommentStatus(\'wid_'.$this->request->parameters['win_no'].'\', \'closed\');');
	}
}

$commentsSettings = $commentsObject->getSettings();

$smarty->assign("comments", $assignedComments);
$smarty->assign("commentsCount", $commentsCount);
$smarty->assign("commentStatus", $commentStatus);

$smarty->assign("commentsSettings", $commentsSettings);

$smarty->assign("objectType", $objectType);
$smarty->assign("objectYgID", $objectYgID);

$smarty->assign("objectID", $objectID);
$smarty->assign("siteID", $siteID);
$smarty->assign("userID", sUserMgr()->getCurrentUserID());

$smarty->assign("object_permissions", $object_permissions);

$smarty->assign("win_no", $winID);
$smarty->assign("refresh", 0);
$smarty->assign("type", $this->request->parameters['type']);
$smarty->display('file:'.$this->page_template);

?>