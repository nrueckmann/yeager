<?php

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();
$adminAllowed = $tmpUser->checkPermission('RFILETYPES');

if ($adminAllowed) {
	$fileMgr = sFileMgr();
	$filetypes = $fileMgr->filetypes->getList();
}

$empty_info = array(
	'NAME'		=> '',
	'OBJECTID'	=> '__NEW__',
	'COLOR'		=> 'black',
	'PROCESSOR'	=> 'NONE'
);

$user = new User(sUserMgr()->getCurrentUserID());
$smarty->assign("RFILETYPES", $user->checkPermission( "RFILETYPES"));

$smarty->assign("processors", $this->files_procs);
$smarty->assign('filetypes', $filetypes);
$smarty->assign('empty_info', $empty_info);
$smarty->assign('win_no', $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>