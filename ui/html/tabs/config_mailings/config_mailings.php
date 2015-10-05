<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();
$adminAllowed = $tmpUser->checkPermission('RMAILINGCONFIG');

if ($adminAllowed) {
	$mailingMgr = new MailingMgr();
	$templateMgr = new Templates();
	$defaultTemplate = $mailingMgr->getDefaultTemplate();
	$templateRoot = $mailingMgr->getTemplateRoot();

	if ($defaultTemplate) {
		$templateInfo = $templateMgr->getTemplate($defaultTemplate);
		$templateInfo['PREVIEWPATH'] = $templateMgr->getPreviewPath($defaultTemplate);
	}

	if ($templateRoot) {
		$templateRootInfo = $templateMgr->getTemplate($templateRoot);
	}
}

$smarty->assign('mode', 1);
$smarty->assign('adminAllowed', $adminAllowed);
$smarty->assign('templateInfo', $templateInfo );
$smarty->assign('templaterootinfo', $templateRootInfo );
$smarty->assign('defaultTemplate', $defaultTemplate );
$smarty->assign('templateRoot', $templateRoot );
$smarty->assign('refresh', $refresh );
$smarty->assign('win_no', $this->request->parameters['win_no']);

$smarty->display('file:'.$this->page_template);

?>