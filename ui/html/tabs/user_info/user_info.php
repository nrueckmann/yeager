<?php

	$userid = $this->request->parameters['userid'];
    $user = new User($userid);
	$userinfo = $user->get();
	$userinfo['PROPS'] = $user->properties->getValues( $userid );
	$userinfo['LANGUAGE'] = $user->getLanguage();
	$userroles = $user->getUsergroups( $userid );
	$languageMgr = new Languages();
	$languages = $languageMgr->getList();

	$userpicture = sUserMgr()->getUserImage($userid, 48, 48);

	if (!$userpicture) $userpicture = $this->imgpath.'content/temp_userpic.png';

	$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setUserHeader(\''.$userpicture.'\',\''.$userinfo['PROPS']['FIRSTNAME'].' '.$userinfo['PROPS']['LASTNAME'].'\',\''.$userinfo['PROPS']['COMPANY'].'\', \''.$userinfo['ID'].'\');');

	$smarty->assign('userinfo', $userinfo);
	$smarty->assign('userroles', $userroles);
	$smarty->assign('userpicture', $userpicture);
	$smarty->assign("win_no", $this->request->parameters['win_no']);
	$smarty->display('file:'.$this->page_template);

?>