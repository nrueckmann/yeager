<?php

$refresh = $this->request->parameters['refresh'];

$roleid = $this->request->parameters['yg_id'];
$roleid = explode( '-', $roleid );
$roleid = $roleid[0];

$isRORole = false;

// Check if role is rootrole
$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");
$anonGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONGROUPID");
if ($rootGroupId == $roleid) {
	$isRORole = true;
}

$maxlevels = 3;

$objecttype = $site = 'system';
$privileges = sUsergroups()->permissions->getByUsergroup($roleid);

$objects = array(
	array('ID' =>  99, 'NAME' => ($itext['TXT_PERM_YEAGER_LOGIN'])?($itext['TXT_PERM_YEAGER_LOGIN']):('$TXT_PERM_YEAGER_LOGIN'), 'LEVEL' => 1, 'PARENT' => 0,
		'RREAD' => $privileges['RBACKEND'], 'CHILDREN' => 25
	),
	array('ID' =>  1, 'NAME' => ($itext['TXT_PERM_PAGES'])?($itext['TXT_PERM_PAGES']):('$TXT_PERM_PAGES'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RPAGES']
	),
	array('ID' =>  2, 'NAME' => ($itext['TXT_PERM_CONTENTBLOCKS'])?($itext['TXT_PERM_CONTENTBLOCKS']):('TXT_PERM_CONTENTBLOCKS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RCONTENTBLOCKS']
	),
	array('ID' =>  3, 'NAME' => ($itext['TXT_PERM_FILES'])?($itext['TXT_PERM_FILES']):('$TXT_PERM_FILES'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RFILES']
	),
	array('ID' =>  4, 'NAME' => ($itext['TXT_PERM_TAGS'])?($itext['TXT_PERM_TAGS']):('$TXT_PERM_TAGS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RTAGS']
	),
	array('ID' => 21, 'NAME' => ($itext['TXT_PERM_COMMENTS'])?($itext['TXT_PERM_COMMENTS']):('$TXT_PERM_COMMENTS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RCOMMENTS']
	),
	array('ID' => 19, 'NAME' => ($itext['TXT_PERM_MAILINGS'])?($itext['TXT_PERM_MAILINGS']):('$TXT_PERM_MAILINGS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RMAILINGS']
	),
	array('ID' => 12, 'NAME' => ($itext['TXT_PERM_TEMPLATES'])?($itext['TXT_PERM_TEMPLATES']):('$TXT_PERM_TEMPLATES'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RTEMPLATES']
	),
	array('ID' => 13, 'NAME' => ($itext['TXT_PERM_ENTRYMASKS'])?($itext['TXT_PERM_ENTRYMASKS']):('$TXT_PERM_ENTRYMASKS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RENTRYMASKS']
	),
	array('ID' => 11, 'NAME' => ($itext['TXT_PERM_SITES'])?($itext['TXT_PERM_SITES']):('$TXT_PERM_SITES'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RSITES']
	),
	array('ID' => 10, 'NAME' => ($itext['TXT_PERM_DATA'])?($itext['TXT_PERM_DATA']):('$TXT_PERM_DATA'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RDATA']
	),
	array('ID' =>  5, 'NAME' => ($itext['TXT_PERM_USER'])?($itext['TXT_PERM_USER']):('$TXT_PERM_USER'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RUSERS']
	),
	array('ID' =>  6, 'NAME' => ($itext['TXT_PERM_USERGROUPS'])?($itext['TXT_PERM_USERGROUPS']):('$TXT_PERM_USERGROUPS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RUSERGROUPS']
	),
	array('ID' => 14, 'NAME' => ($itext['TXT_PERM_PROPERTIES'])?($itext['TXT_PERM_PROPERTIES']):('$TXT_PERM_PROPERTIES'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RPROPERTIES']
	),
	array('ID' => 15, 'NAME' => ($itext['TXT_PERM_FILETYPES'])?($itext['TXT_PERM_FILETYPES']):('$TXT_PERM_FILETYPES'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RFILETYPES']
	),
	array('ID' => 16, 'NAME' => ($itext['TXT_PERM_VIEWS'])?($itext['TXT_PERM_VIEWS']):('$TXT_PERM_VIEWS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RVIEWS']
	),
	array('ID' => 17, 'NAME' => ($itext['TXT_PERM_COMMENTS_CONFIG'])?($itext['TXT_PERM_COMMENTS_CONFIG']):('$TXT_PERM_COMMENTS_CONFIG'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RCOMMENTCONFIG']
	),
	array('ID' => 20, 'NAME' => ($itext['TXT_PERM_MAILINGS_CONFIG'])?($itext['TXT_PERM_MAILINGS_CONFIG']):('$TXT_PERM_MAILINGS_CONFIG'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RMAILINGCONFIG']
	),
	array('ID' =>  7, 'NAME' => ($itext['TXT_PERM_PAGE_EXTENSIONS'])?($itext['TXT_PERM_PAGE_EXTENSIONS']):('$TXT_PERM_PAGE_EXTENSIONS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['REXTENSIONS_PAGE']
	),
	array('ID' =>  22, 'NAME' => ($itext['TXT_PERM_MAILING_EXTENSIONS'])?($itext['TXT_PERM_MAILING_EXTENSIONS']):('$TXT_PERM_MAILING_EXTENSIONS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['REXTENSIONS_MAILING']
	),
	array('ID' =>  23, 'NAME' => ($itext['TXT_PERM_FILE_EXTENSIONS'])?($itext['TXT_PERM_FILE_EXTENSIONS']):('$TXT_PERM_FILE_EXTENSIONS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['REXTENSIONS_FILE']
	),
	array('ID' =>  24, 'NAME' => ($itext['TXT_PERM_CBLOCK_EXTENSIONS'])?($itext['TXT_PERM_CBLOCK_EXTENSIONS']):('$TXT_PERM_CBLOCK_EXTENSIONS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['REXTENSIONS_CBLOCK']
	),
	array('ID' =>  8, 'NAME' => ($itext['TXT_PERM_IMPORT_EXTENSIONS'])?($itext['TXT_PERM_IMPORT_EXTENSIONS']):('$TXT_PERM_IMPORT_EXTENSIONS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RIMPORT']
	),
	array('ID' =>  9, 'NAME' => ($itext['TXT_PERM_EXPORT_EXTENSIONS'])?($itext['TXT_PERM_EXPORT_EXTENSIONS']):('$TXT_PERM_EXPORT_EXTENSIONS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['REXPORT']
	),
	array('ID' =>  18, 'NAME' => ($itext['TXT_PERM_COLISTVIEW_EXTENSIONS'])?($itext['TXT_PERM_COLISTVIEW_EXTENSIONS']):('$TXT_PERM_COLISTVIEW_EXTENSIONS'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['REXTENSIONS_CBLISTVIEW']
	),
	array('ID' =>  25, 'NAME' => ($itext['TXT_PERM_UPDATER'])?($itext['TXT_PERM_UPDATER']):('$TXT_PERM_UPDATER'), 'LEVEL' => 2, 'PARENT' => 99,
		'RREAD' => $privileges['RUPDATER']
	)
);

$extensionMgr = new ExtensionMgr();
$extensions = $extensionMgr->getList(0, true, true);
foreach($extensions as $extensionItem) {
	$extPrivileges = sUsergroups()->permissions->getList( $extensionItem['CODE'] );
	if (count($extPrivileges) > 0) {
		foreach($extPrivileges as $extPrivilegeItem) {
			$permissions = sUsergroups()->permissions->getByUsergroup($roleid, $extensionItem['CODE']);
			$permValue = $permissions[$extPrivilegeItem['PRIVILEGE']];
			$objects[] = array(
				'ID' =>  (int)$extPrivilegeItem['ID'] + 1000,
				'NAME' => $extPrivilegeItem['NAME'],
				'LEVEL' => 2,
				'PARENT' => 99,
				'RREAD' => $permValue
			);
		}
	}
}
$objects[0]['CHILDREN'] = count($objects)-1;

$objectInfo = sUsergroups()->get($roleid);
$object_permissions = Array();
$object_permissions["RWRITE"] = sUsergroups()->usergroupPermissions->checkInternal( sUserMgr()->getCurrentUserID(), $roleid, "RWRITE" );

$smarty->assign('isRORole', $isRORole);
$smarty->assign("maxlevels", $maxlevels );
$smarty->assign("site", $siteID );
$smarty->assign("objects", $objects);
$smarty->assign("page", $objectID);
$smarty->assign("refresh", $refresh );
$smarty->assign("tags", $tags);
$smarty->assign("ygid", $ygid);
$smarty->assign("object_permissions", $object_permissions);
$smarty->assign("objectInfo", $objectInfo);
$smarty->assign("objecttype", $objecttype);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>