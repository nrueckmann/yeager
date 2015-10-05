<?php

$initload = $this->request->parameters['initload'];

$newRole = (int)$this->request->parameters['newRole'];
$newSearchText = $this->request->parameters['newSearchText'];
$roles = sUsergroups()->getList();

// FIXME
$realUsergroups = array();
foreach($roles as $usergroup_idx => $usergroup) {
	if (sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $usergroup['ID'], 'RREAD')) {
		$realUsergroups[] = $usergroup;
	}
}
$roles = $realUsergroups;

$rootUserId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTUSERID");
$anonUserId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONUSERID");

if ($newRole) {
	if ($newRole > 0) {
		// Users in special usergroups
		$userCountFiltered = sUserMgr()->getUsergroupCount($newRole, $newSearchText);
		$userCount = sUserMgr()->getUsergroupCount($newRole);
	} else {
		// Users without usergroups
		$userCountFiltered = sUserMgr()->getWithoutUsergroupCount($newSearchText);
		$userCount = sUserMgr()->getWithoutUsergroupCount();
	}
} else {
	// All Users
	$userCountFiltered = sUserMgr()->getListCount($newSearchText);
	$userCount = sUserMgr()->getListCount();
}

// for paging
$pageDirInfo = calcPageDir($userCountFiltered, 'lastname', 'ASC');
$pageDirOrderBy = $pageDirInfo['pageDirOrderBy'];
$pageDirOrderDir = $pageDirInfo['pageDirOrderDir'];
$pageDirLimit = $pageDirInfo['pageDirLimit'];
// END for paging

if ($newRole) {
	if ($newRole > 0) {
		// Users in special usergroups
		$users = sUserMgr()->getByUsergroup($newRole, $pageDirOrderBy, $pageDirOrderDir, $pageDirLimit, $newSearchText);
	} else {
		// Users without usergroups
		$users = sUserMgr()->getWithoutUsergroup($pageDirOrderBy, $pageDirOrderDir, $pageDirLimit, $newSearchText);
	}
} else {
	// All Users
	$users = sUserMgr()->getList($pageDirOrderBy, $pageDirOrderDir, $pageDirLimit, $newSearchText);
}

foreach($users as $users_idx => $users_item) {
	$user = new User($users_item['ID']);
	$props = $user->properties->getValues($users_item['ID']);
	$users[$users_idx]['PROPS'] = $props;

	if (file_exists($this->approot.$this->userpicdir.$users_item['ID'].'-picture.jpg')) {
		$internPrefix = (string)sConfig()->getVar('CONFIG/REFTRACKER/INTERNALPREFIX');
		$users[$users_idx]['USERPICTURE'] = $internPrefix.'userimage/'.$users_item['ID'].'/48x48?rnd='.rand();
	} else {
		$users[$users_idx]['USERPICTURE'] = $this->imgpath.'content/temp_userpic.png';
	}
}

// Check rights
$rusers = sUsergroups()->permissions->check( sUserMgr()->getCurrentUserID(), "RUSERS" );

$smarty->assign('rootUserId', $rootUserId );
$smarty->assign('anonUserId', $anonUserId );
$smarty->assign('newRole', $newRole );
$smarty->assign('rusers', $rusers );
$smarty->assign('roles', $roles );
$smarty->assign('userlist', $users );
$smarty->assign('usercount', $userCount );
$smarty->assign('usercountfiltered', $userCountFiltered );
$smarty->assign('site', $site );
$smarty->assign('refresh', $refresh );
$smarty->assign('initload', $initload );
$smarty->assign('chooser', $chooser );
$smarty->assign('displaymode', $displaymode );
$smarty->assign('win_no', $this->request->parameters['win_no']);

$smarty->display('file:'.$this->page_template);

?>