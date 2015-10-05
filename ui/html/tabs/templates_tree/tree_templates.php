<?php

$siteID = $this->request->parameters['site'];
$pageID = $this->request->parameters['page'];
$action = $this->request->parameters['action'];

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();
$adminAllowed = $tmpUser->checkPermission('RPAGES');
if (!$adminAllowed) $adminAllowed = $tmpUser->checkPermission('RTEMPLATES');

if ($adminAllowed) {
	if (($action == 'choose') && $siteID && $pageID) {
		if ($siteID == 'mailing') {
			$mailingMgr = new MailingMgr();
			$mailing = $mailingMgr->getMailing($pageID);
			$mailingInfo = $mailing->get();
			$preselected = $mailingInfo["TEMPLATEID"];
		} else {
			$pageMgr = new PageMgr($siteID);
			$page = $pageMgr->getPage($pageID);
			$pageInfo = $page->get();
			$preselected = $pageInfo["TEMPLATEID"];
		}
	}

	if ($action == 'choosefolder') {
		// Check if folders are available
		$templateMgr = new Templates();
		$siteMgr = new Sites();
		$siteinfo = $siteMgr->get($siteID);
		$templatesTree = $templateMgr->getList();
		$hasNoFolders = true;
		foreach ($templatesTree as $templatesTreeItem) {
			if ($templatesTreeItem['LEVEL'] > 1) {
				if ($templatesTreeItem['FOLDER'] == 1) {
					$hasNoFolders = false;
				}
			}
		}
		$smarty->assign("hasNoFolders", $hasNoFolders);
	}
}


$smarty->assign("site", $siteID);
$smarty->assign("preselected", $preselected);
$smarty->assign("action", $action);
$smarty->assign("opener_reference", $this->request->parameters['opener_reference']);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>