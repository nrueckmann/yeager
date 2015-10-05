<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$type = $this->request->parameters['yg_type'];
$trashcanfilterCreated = $this->request->parameters['trashcanfilter_created'];
$trashcanfilterRemoved = $this->request->parameters['trashcanfilter_removed'];

if (($trashcanfilterCreated != '')&&($trashcanfilterCreated != 'ALL')) {
	$createdToTS = time();
	if ($trashcanfilterCreated=='LAST_WEEK') {
		$createdFromTS = $createdToTS - 604800;		// Minus one week
	} elseif ($trashcanfilterCreated=='LAST_2_WEEKS') {
		$createdFromTS = $createdToTS - 1209600;	// Minus two weeks
	} elseif ($trashcanfilterCreated=='LAST_4_WEEKS') {
		$createdFromTS = $createdToTS - 2419200;	// Minus four weeks
	} elseif ($trashcanfilterCreated=='LAST_8_WEEKS') {
		$createdFromTS = $createdToTS - 4838400;	// Minus eight weeks
	} else {
		// Individual
		list ($createdFromTS, $createdToTS) = explode("###", $trashcanfilterCreated);
		$createdFromTS = TSfromLocalTS(strtotime($createdFromTS));
		$createdToTS = TSfromLocalTS(strtotime($createdToTS) + 24*60*60);
	}
}

if (!$trashcanfilterRemoved) $trashcanfilterRemoved = 'ALL';

if (($trashcanfilterRemoved != '')&&($trashcanfilterRemoved != 'ALL')) {
	$removedToTS = time();
	if ($trashcanfilterRemoved=='LAST_WEEK') {
		$removedFromTS = $removedToTS - 604800;	// Minus one week
	} elseif ($trashcanfilterRemoved=='LAST_2_WEEKS') {
		$removedFromTS = $removedToTS - 1209600;	// Minus two weeks
	} elseif ($trashcanfilterRemoved=='LAST_4_WEEKS') {
		$removedFromTS = $removedToTS - 2419200;	// Minus four weeks
	} elseif ($trashcanfilterRemoved=='LAST_8_WEEKS') {
		$removedFromTS = $removedToTS - 4838400;	// Minus eight weeks
	} else {
		// Individual
		list ($removedFromTS, $removedToTS) = explode("###", $trashcanfilterRemoved);
		$removedFromTS = TSfromLocalTS(strtotime($removedFromTS));
		$removedToTS = TSfromLocalTS(strtotime($removedToTS) + 24*60*60);
	}
}

// Check for filter and build filter array if needed
$filterArray = array();
if ($createdFromTS && $createdToTS) {
	$filterArray[] = array(
		'TYPE' 		=> 'CREATEDTS',
		'OPERATOR'	=> 'is_bigger',
		'VALUE'		=>	$createdFromTS
	);
	$filterArray[] = array(
		'TYPE' 		=> 'CREATEDTS',
		'OPERATOR'	=> 'is_smaller',
		'VALUE'		=>	$createdToTS
	);
}
if ($removedFromTS && $removedToTS) {
	$filterArray[] = array(
		'TYPE' 		=> 'CHANGEDTS',
		'OPERATOR'	=> 'is_bigger',
		'VALUE'		=>	$removedFromTS
	);
	$filterArray[] = array(
		'TYPE' 		=> 'CHANGEDTS',
		'OPERATOR'	=> 'is_smaller',
		'VALUE'		=>	$removedToTS
	);
}



switch($type) {
	case 'page':
		$siteID = explode('-', $ygid);
		$siteID = $siteID[1];

		$pageMgr = new PageMgr($siteID);

		$trashedObjects = $pageMgr->getList(0, array('TRASHCAN'), 0, 0, $filterArray);

		// for paging
		$pageDirInfo = calcPageDir(count($trashedObjects), 'prop.CHANGEDTS', 'DESC');
		$pageDirOrderBy = $pageDirInfo['pageDirOrderBy'];
		$pageDirOrderDir = $pageDirInfo['pageDirOrderDir'];
		$pageDirLimit = explode(',', $pageDirInfo['pageDirLimit']);
		$pageDirLimitFrom = $pageDirLimit[0];
		$pageDirLimitLength = $pageDirLimit[1];
		// END for paging

		$filterArray[] = array(
			'TYPE' 		=> 'LIMITER',
			'VALUE'		=>	$pageDirLimitFrom,
			'VALUE2'	=>	$pageDirLimitLength
		);
		$filterArray[] = array(
			'TYPE' 		=> 'ORDER',
			'VALUE'		=>	$pageDirOrderBy,
			'VALUE2'	=>	$pageDirOrderDir
		);

		$trashedObjects = $pageMgr->getList(0, array('TRASHCAN'), 0, 0, $filterArray);

		foreach($trashedObjects as $trashedObjectIdx => $trashedObject) {
			$page = $pageMgr->getPage($trashedObject['ID']);
			$lastHistory = $page->history->getChanges(1);
			$trashedObjects[$trashedObjectIdx]['PARENTS'] = $pageMgr->getParents($trashedObject['ID']);
			$trashedObjects[$trashedObjectIdx]['SITE'] = $siteID;
			$trashedObjects[$trashedObjectIdx]['LASTCHANGE'] = $lastHistory;

			// Get permissions and style and icon for page
			$tPageInfo = $page->get();
			$tPageInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['OID'], "RWRITE");
			$tPageInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['OID'], "RDELETE");
			$trashedObjects[$trashedObjectIdx]['PAGEINFO'] = $tPageInfo;
			$iconData = getIconForPage($tPageInfo);
			$trashedObjects[$trashedObjectIdx]['ICON'] = $iconData['iconclass'];
			$trashedObjects[$trashedObjectIdx]['STYLE'] = $iconData['style'];

			// Get information for creator
			if ($trashedObject['CREATEDBY']) {
				$user = new User($trashedObject['CREATEDBY']);
				$uinfo = $user->get();
				$uinfo['PROPS'] = $user->properties->getValues($trashedObject['CREATEDBY']);
				$trashedObjects[$trashedObjectIdx]['CREATEDBYINFO'] = $uinfo;
				$trashedObjects[$trashedObjectIdx]['CREATEDBYUSERNAME'] = trim($uinfo['PROPS']['FIRSTNAME'].' '.$uinfo['PROPS']['LASTNAME']);
			}

			// Get information for trasher
			if ($lastHistory['USERID']) {
				$user = new User($lastHistory['USERID']);
				$uinfo = $user->get();
				$uinfo['PROPS'] = $user->properties->getValues($lastHistory['USERID']);
				$trashedObjects[$trashedObjectIdx]['TRASHERINFO'] = $uinfo;
				$trashedObjects[$trashedObjectIdx]['TRASHERUSERNAME'] = trim($uinfo['PROPS']['FIRSTNAME'].' '.$uinfo['PROPS']['LASTNAME']);
			}
		}
		// Get current locks for this token (and unlock them)
		$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
		$lockedObjects = $pageMgr->getLocksByToken($lockToken);
		foreach($lockedObjects as $lockedObject) {
			$currentObject = $pageMgr->getPage($lockedObject['OBJECTID']);
			$currentObject->releaseLock($lockedObject['TOKEN']);
		}
		break;

	case 'cblock':
		$trashedObjects = sCblockMgr()->getList(0, array('TRASHCAN'), 0, 0, $filterArray);

		// for paging
		$pageDirInfo = calcPageDir(count($trashedObjects), 'prop.CHANGEDTS', 'DESC');
		$pageDirOrderBy = $pageDirInfo['pageDirOrderBy'];
		$pageDirOrderDir = $pageDirInfo['pageDirOrderDir'];
		$pageDirLimit = explode(',', $pageDirInfo['pageDirLimit']);
		$pageDirLimitFrom = $pageDirLimit[0];
		$pageDirLimitLength = $pageDirLimit[1];
		// END for paging

		$filterArray[] = array(
			'TYPE' 		=> 'LIMITER',
			'VALUE'		=>	$pageDirLimitFrom,
			'VALUE2'	=>	$pageDirLimitLength
		);
		$filterArray[] = array(
			'TYPE' 		=> 'ORDER',
			'VALUE'		=>	$pageDirOrderBy,
			'VALUE2'	=>	$pageDirOrderDir
		);

		$trashedObjects = sCblockMgr()->getList(0, array('TRASHCAN'), 0, 0, $filterArray);

		foreach($trashedObjects as $trashedObjectIdx => $trashedObject) {
			$cb = sCblockMgr()->getCblock($trashedObject['ID']);
			$lastHistory = $cb->history->getChanges(1);
			$trashedObjects[$trashedObjectIdx]['PARENTS'] = sCblockMgr()->getParents($trashedObject['ID']);
			$trashedObjects[$trashedObjectIdx]['LASTCHANGE'] = $lastHistory;

			// Get information for creator
			$user = new User($trashedObject['CREATEDBY']);
			$uinfo = $user->get();
			$uinfo['PROPS'] = $user->properties->getValues($trashedObject['CREATEDBY']);
			$trashedObjects[$trashedObjectIdx]['CREATEDBYINFO'] = $uinfo;
			$trashedObjects[$trashedObjectIdx]['CREATEDBYUSERNAME'] = trim($uinfo['PROPS']['FIRSTNAME'].' '.$uinfo['PROPS']['LASTNAME']);

			// Get information for trasher
			$user = new User($lastHistory['USERID']);
			$uinfo = $user->get();
			$uinfo['PROPS'] = $user->properties->getValues($lastHistory['USERID']);
			$trashedObjects[$trashedObjectIdx]['TRASHERINFO'] = $uinfo;
			$trashedObjects[$trashedObjectIdx]['TRASHERUSERNAME'] = trim($uinfo['PROPS']['FIRSTNAME'].' '.$uinfo['PROPS']['LASTNAME']);
		}
		/*
		// Get current locks for this token (and unlock them)
		$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
		$lockedObjects = $pageMgr->getLocksByToken($lockToken);
		foreach($lockedObjects as $lockedObject) {
			$currentObject = $pageMgr->getPage($lockedObject['OBJECTID']);
			$currentObject->releaseLock($lockedObject['TOKEN']);
		}
		*/
		break;
	case 'filefolder':
		$fileMgr = sFileMgr();

		$trashedObjects = $fileMgr->getList(0, array('TRASHCAN'), "group2.LFT", 0, 0, $filterArray);

		// for paging
		$pageDirInfo = calcPageDir(count($trashedObjects), 'prop.CHANGEDTS', 'DESC');
		$pageDirOrderBy = $pageDirInfo['pageDirOrderBy'];
		$pageDirOrderDir = $pageDirInfo['pageDirOrderDir'];
		$pageDirLimit = explode(',', $pageDirInfo['pageDirLimit']);
		$pageDirLimitFrom = $pageDirLimit[0];
		$pageDirLimitLength = $pageDirLimit[1];
		// END for paging

		$filterArray[] = array(
			'TYPE' 		=> 'LIMITER',
			'VALUE'		=>	$pageDirLimitFrom,
			'VALUE2'	=>	$pageDirLimitLength
		);
		$filterArray[] = array(
			'TYPE' 		=> 'ORDER',
			'VALUE'		=>	$pageDirOrderBy,
			'VALUE2'	=>	$pageDirOrderDir
		);

		$trashedObjects = $fileMgr->getList(0, array('TRASHCAN'), "group2.LFT", 0, 0, $filterArray);

		foreach($trashedObjects as $trashedObjectIdx => $trashedObject) {
			$file = new File($trashedObject['ID']);
			$trashedObjects[$trashedObjectIdx]['FILEINFO'] = $file->get();
			$lastHistory = $file->history->getChanges(1);
			$parents = $fileMgr->getParents($trashedObject['ID']);
			array_pop($parents);
			$trashedObjects[$trashedObjectIdx]['PARENTS'] = $parents;
			$trashedObjects[$trashedObjectIdx]['LASTCHANGE'] = $lastHistory;

			// Get information for creator
			if ($trashedObject['CREATEDBY']) {
				$user = new User($trashedObject['CREATEDBY']);
				$uinfo = $user->get();
				$uinfo['PROPS'] = $user->properties->getValues($trashedObject['CREATEDBY']);
				$trashedObjects[$trashedObjectIdx]['CREATEDBYINFO'] = $uinfo;
				$trashedObjects[$trashedObjectIdx]['CREATEDBYUSERNAME'] = trim($uinfo['PROPS']['FIRSTNAME'].' '.$uinfo['PROPS']['LASTNAME']);
			}

			// Get information for trasher
			if($lastHistory['USERID']) {
				$user = new User($lastHistory['USERID']);
				$uinfo = $user->get();
				$uinfo['PROPS'] = $user->properties->getValues($lastHistory['USERID']);
				$trashedObjects[$trashedObjectIdx]['TRASHERINFO'] = $uinfo;
				$trashedObjects[$trashedObjectIdx]['TRASHERUSERNAME'] = trim($uinfo['PROPS']['FIRSTNAME'].' '.$uinfo['PROPS']['LASTNAME']);
			}
		}
		// Get current locks for this token (and unlock them)
		$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
		$lockedObjects = $fileMgr->getLocksByToken($lockToken);
		foreach($lockedObjects as $lockedObject) {
			$currentObject = new File($lockedObject['OBJECTID']);
			$currentObject->releaseLock($lockedObject['TOKEN']);
		}
		break;
}

$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

$smarty->assign("trashedObjects", $trashedObjects);
$smarty->assign("type", $type);
$smarty->assign("refresh", $refresh );
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>
