<?php

$objectType = $this->request->parameters['yg_type'];
$objectYgID = $this->request->parameters['yg_id'];
$displayType = $this->request->parameters['type'];
$winID = $this->request->parameters['win_no'];
$filterStatus = $this->request->parameters['mailingfilter_status'];
if (!$filterStatus) {
	$filterStatus = 'ALL';
}

$mailingMgr = new MailingMgr();

$filterArray = array();
$filterArray[] = array(
	'TYPE' => 'STATUS',
	'VALUE' => $filterStatus
);

$mailingsCount = count($mailingMgr->getList($mailingMgr->tree->getRoot(), array('SUBNODES'), 2, NULL, $filterArray));

// for paging
$pageDirInfo = calcPageDir($mailingsCount, 'prop.CHANGEDTS');
$pageDirOrderBy = $pageDirInfo['pageDirOrderBy'];
$pageDirOrderDir = 'DESC';
$pageDirLimit = explode(',', $pageDirInfo['pageDirLimit']);
$pageDirLimitFrom = $pageDirLimit[0];
$pageDirLimitLength = $pageDirLimit[1];
// END for paging

$filterArray = array();
$filterArray[] = array(
	'TYPE' => 'LIMITER',
	'VALUE' => $pageDirLimitFrom,
	'VALUE2' => $pageDirLimitLength
);
$filterArray[] = array(
	'TYPE' => 'ORDER',
	'VALUE' => $pageDirOrderBy,
	'VALUE2' => $pageDirOrderDir
);
$filterArray[] = array(
	'TYPE' => 'STATUS',
	'VALUE' => $filterStatus
);

$mailings = $mailingMgr->getList($mailingMgr->tree->getRoot(), array('SUBNODES'), 2, NULL, $filterArray);

// Get additional user information
$allMailings = array();
foreach ($mailings as $mailingsIdx => $mailingsItem) {
	$currMailing = $mailingMgr->getMailing($mailingsItem['ID']);
	if ($currMailing) {
		$mailings[$mailingsIdx]['CREATEDTS'] = TStoLocalTS($mailings[$mailingsIdx]['CREATEDTS']);
		$mailings[$mailingsIdx]['CHANGEDTS'] = TStoLocalTS($mailings[$mailingsIdx]['CHANGEDTS']);

		// Get assigned groups
		$currMailingGroups = $currMailing->getUsergroups();
		$mailings[$mailingsIdx]['GROUPS'] = $currMailingGroups;

		// Check groups permissions (and set RWRITE to 0) if one of the groups has no RREAD
		$mailings[$mailingsIdx]['RWRITE'] = $currMailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $mailingsItem['ID'], "RREAD");
		foreach ($currMailingGroups as $currMailingGroupsItem) {
			if (!sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $currMailingGroupsItem['ID'], "RREAD")) {
				// NO RREAD
				$mailings[$mailingsIdx]['RWRITE'] = 0;
			}
		}

		// Get # of receipients
		$receipients = 0;
		$userIds = array();
		foreach ($currMailingGroups as $currMailingGroup) {
			// Get # of users in this group
			$usersInRole = sUserMgr()->getByUsergroup($currMailingGroup['ID']);
			foreach ($usersInRole as $usersInRoleItem) {
				array_push($userIds, $usersInRoleItem['UID']);
			}
		}
		$userIds = array_unique($userIds);
		$receipients = count($userIds);

		$mailings[$mailingsIdx]['RECEIPIENTS'] = $receipients;

		// Get status of mailing
		$mailingStatus = $currMailing->getStatus();
		$scheduledJobs = $mailingMgr->scheduler->getQueuedJobsForObject($mailingsItem['ID'], true, 'SCH_EMAILSEND');
		if (count($scheduledJobs) > 0) {
			// There are scheduled jobs for this mailing
			$jobCount = count($scheduledJobs);
		} else {
			// No jobs scheduled
			$jobCount = 0;
		}

		$mailings[$mailingsIdx]['STATUS'] = $mailingStatus['STATUS'];
		$mailings[$mailingsIdx]['JOBS_DONE'] = $receipients - $jobCount;
		$mailings[$mailingsIdx]['PERCENTAGE'] = ceil((($receipients - $jobCount) / $receipients) * 100);

		if (!$mailingsItem['CHANGEDBY']) {
			$mailings[$mailingsIdx]['CHANGEDBY'] = $mailingsItem['CHANGEDBY'] = $mailingsItem['CREATEDBY'];
		}

		$userObj = new User($mailingsItem['CHANGEDBY']);
		$userInfo = $userObj->get();
		$userProps = $userObj->properties->getValues($mailingsItem['CHANGEDBY']);
		$userInfo['PROPS'] = $userProps;
		$userInfo['USER_NAME'] = trim($userInfo['PROPS']['FIRSTNAME'] . ' ' . $userInfo['PROPS']['LASTNAME']);
		$mailings[$mailingsIdx]['USERINFO'] = $userInfo;
		$allMailings[] = $mailings[$mailingsIdx];
	}
}

// Check if adding mailings is allowed
$rootNodeId = $mailingMgr->tree->getRoot();
$rootNode = $mailingMgr->getMailing($rootNodeId);
if ($rootNode != false) {
	$rsub = $rootNode->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $rootNodeId, "RWRITE");
	$smarty->assign("rsub", $rsub);
} else {
	$smarty->assign("rsub", false);
}

$smarty->assign("filterStatus", $filterStatus);
$smarty->assign("emailList", $allMailings);
$smarty->assign("win_no", $winID);
$smarty->assign("refresh", 0);
$smarty->assign("type", $this->request->parameters['type']);
$smarty->display('file:' . $this->page_template);

?>