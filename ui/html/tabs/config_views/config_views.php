<?php

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();
$adminAllowed = $tmpUser->checkPermission('RVIEWS');

if ($adminAllowed) {
	$viewMgr = new Views();
	$views = $viewMgr->getList();
}

$empty_info = array(
	'NAME'				=> '',
	'ID'				=> '__NEW__',
	'WIDTH'				=> '0',
	'HEIGHT'			=> '0',
	'WIDTHCROP'			=> 0,
	'HEIGHTCROP'		=> 0,
	'CONSTRAINHEIGHT'	=> 0,
	'CONSTRAINWIDTH'	=> 0
);

$user = new User(sUserMgr()->getCurrentUserID());
$smarty->assign("RVIEWS", $user->checkPermission( "RVIEWS"));

$smarty->assign('views', $views);
$smarty->assign('empty_info', $empty_info);

$smarty->assign('win_no', $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>