<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$data = explode('-',$ygid );
$pageID = $data[0];
$siteID = $data[1];

$templateMgr = new Templates();
$pageMgr = new PageMgr($siteID);
$page = $pageMgr->getPage($pageID);

$pageInfo = $page->get();
$pageInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RWRITE");
$pageInfo['RSTAGE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RSTAGE");

if ($pageInfo['DELETED']) {
	$pageInfo['RWRITE'] = false;
	$pageInfo['READONLY'] = true;
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
if ($lockedFailed) {
	// Get user who locked this object
	$userWithLock = new User( $lockStatus['LOCKUID'] );
	$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
	$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
	$smarty->assign('lockedByUser', $lockedByUser );
	$pageInfo['RWRITE'] = false;
	$pageInfo['READONLY'] = true;
	$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
} else {
	$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$pageInfo['RSTAGE'].'\' );');
}

if (strlen($pageInfo['PNAME']) < 1) {
	$pageInfo['PNAME'] = $page->calcPName();
}

$templateid = $pageInfo["TEMPLATEID"];
$naviid = $pageInfo["NAVIGATIONID"];

$templateInfo = $templateMgr->getTemplate($templateid);
$templateInfo["PREVIEWPATH"] = $templateMgr->getPreviewPath( $templateid );

$navis = $templateMgr->getNavis($templateid);
for ($i = 0; $i < count($navis); $i++) {
  if ($navis[$i]["ID"] == $naviid) {
	  $naviinfo = $navis[$i];
  }
}

$pageList = $pageMgr->getTree($pageID, 1);
for ($i = 0; $i < count($pageList); $i++) {
  if ($pageList[$i]["PARENT"] == $pageID) {
	$currentPage = $pageMgr->getPage($pageList[$i]["ID"]);
	$pageList[$i]["INFO"] = $currentPage->get();
	if ($pageList[$i]["VERSIONPUBLISHED"] == $pageList[$i]["VERSION"]-1) {
	  $pageList[$i]["APPROVED"] = 1;
	}
	$children[] = $pageList[$i];
  }
}

$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

$smarty->assign("children", $children);

$smarty->assign("page_id", $pageID );
$smarty->assign("site_id", $siteID );

$smarty->assign("site", $siteID );
$smarty->assign("cached", $cached );
$smarty->assign("page", $pageID );
$smarty->assign("mode", 1 );
$smarty->assign("templateInfo", $templateInfo);
$smarty->assign("naviinfo", $naviinfo);
$smarty->assign("pageInfo", $pageInfo);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>