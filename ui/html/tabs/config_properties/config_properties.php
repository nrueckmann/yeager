<?php

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();
$adminAllowed = $tmpUser->checkPermission('RPROPERTIES');

if ($adminAllowed) {
	switch(strtolower($this->page)) {
		case 'tab_config_page-properties':
			// Page
			// Get first site
			$siteMgr = new Sites();
			$sites = $siteMgr->getList();
			$pageMgr = new PageMgr($sites[0]['ID']);
			$properties_object = $pageMgr->properties;
			$object_type = 'page';
			break;

		case 'tab_config_cblock-properties':
			// Contentblocks
			$properties_object = sCblockMgr()->properties;
			$object_type = 'cblock';
			break;

		case 'tab_config_file-properties':
			// File
			$fileMgr = sFileMgr();
			$properties_object = $fileMgr->properties;
			$object_type = 'file';
			break;

		case 'tab_config_user-properties':
			// User
			$properties_object = sUserMgr()->properties;
			$object_type = 'user';
			break;
	}

	$object_properties = $properties_object->getList('LISTORDER');

	foreach($object_properties as $object_properties_idx => $object_properties_item) {
		if ($object_properties_item['TYPE']=='LIST') {
			$listentries = $properties_object->getListValues( $object_properties_item['IDENTIFIER'] );
			$object_properties[$object_properties_idx]['LVALUES'] = $listentries;
		}
	}

	$empty_infos = array(
		1 => array(
			'TYPE'		=> 'TEXT',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> '',
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		2 => array(
			'TYPE'		=> 'TEXTAREA',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		3 => array(
			'TYPE'		=> 'RICHTEXT',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		4 => array(
			'TYPE'		=> 'CHECKBOX',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		5 => array(
			'TYPE'		=> 'LINK',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		6 => array(
			'TYPE'		=> 'FILE',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		7 => array(
			'TYPE'		=> 'TAG',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		8 => array(
			'TYPE'		=> 'CBLOCK',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		9 => array(
			'TYPE'		=> 'LIST',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		10 => array(
			'TYPE'		=> 'PAGE',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		11 => array(
			'TYPE'		=> 'DATE',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		12 => array(
			'TYPE'		=> 'DATETIME',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		13 => array(
			'TYPE'		=> 'HEADLINE',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		),
		14 => array(
			'TYPE'		=> 'PASSWORD',
			'ID'		=> '#<<new_id>>',
			'NAME' 		=> $itext['TXT_NEW_OBJECT'],
			'IDENTIFIER'	=> '',
			'LVALUES'	=> '',
		)
	);

	$user = new User(sUserMgr()->getCurrentUserID());
	$smarty->assign("RPROPERTIES", $user->checkPermission( "RPROPERTIES"));
}

$smarty->assign('adminAllowed', $adminAllowed);
$smarty->assign('empty_infos', $empty_infos);
$smarty->assign('object_properties', $object_properties);
$smarty->assign('object_type', $object_type);

$smarty->assign('win_no', $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>