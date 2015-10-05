<?php

$siteID = (int)$this->request->parameters['site'];
$pageID = (int)$this->request->parameters['page'];

$templateMgr = new Templates();
$pageMgr = new PageMgr($siteID);
$page = $pageMgr->getPage($pageID);
$pageInfo = $page->get();

// Get assigned templateInfo
$templateid = $pageInfo['TEMPLATEID'];
$preselected = $pageInfo['NAVIGATIONID'];
$navigations = $templateMgr->getNavis($templateid);

$smarty->assign('navigations', $navigations );
$smarty->assign('preselected', $preselected );
$smarty->assign("win_no", $this->request->parameters['win_no']);

$smarty->display('file:'.$this->page_template);

?>