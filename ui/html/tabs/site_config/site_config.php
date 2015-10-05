<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$initload = $this->request->parameters['initload'];
$siteID = explode('-', $ygid);
$siteID = $siteID[0];

$siteMgr = new Sites();
$siteinfo = $siteMgr->get($siteID);

if ($siteinfo['FAVICON']) {
	// Favicon is set, get all information about file
	$file = sFileMgr()->getFile($siteinfo['FAVICON']);
	if ($file) {
		$favicon = $file->get();
		$smarty->assign('favicon', $favicon );
	}
}

$templateMgr = new Templates();
$templateInfo = $templateMgr->getTemplate($siteinfo['DEFAULTTEMPLATE']);
$templateInfo['PREVIEWPATH'] = $templateMgr->getPreviewPath($siteinfo['DEFAULTTEMPLATE']);

if ($siteinfo['TEMPLATEROOT']) {
	$templaterootinfo = $templateMgr->getTemplate($siteinfo['TEMPLATEROOT']);
}

$smarty->assign('object', $siteID );
$smarty->assign('siteinfo', $siteinfo );
$smarty->assign('templateInfo', $templateInfo );
$smarty->assign('templaterootinfo', $templaterootinfo );
$smarty->assign('mode', 1 );
$smarty->assign('refresh', $refresh );
$smarty->assign('initload', $initload );
$smarty->assign('win_no', $this->request->parameters['win_no']);

$smarty->display('file:'.$this->page_template);

?>