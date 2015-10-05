<?php

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();
$adminAllowed = $tmpUser->checkPermission('RSITES');

if ($adminAllowed) {
	$siteMgr = new Sites();
	$sites = $siteMgr->getList();
}

$smarty->assign("sites", $sites);

$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>