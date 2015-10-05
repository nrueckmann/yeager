<?php

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();
$adminAllowed = $tmpUser->checkPermission('RCOMMENTCONFIG');

if ($adminAllowed) {
	$commentsObject = new Comments();
	$commentsSettings = $commentsObject->getSettings();
}

$smarty->assign('commentsSettings', $commentsSettings);
$smarty->assign('adminAllowed', $adminAllowed);

$smarty->assign('refresh', $this->request->parameters['refresh']);
$smarty->assign('win_no', $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>