<?php

$yg_type = $this->request->parameters['yg_type'];
$yg_id = $this->request->parameters['yg_id'];
$roleid = $this->request->parameters['role_id'];
$roleid = explode( '-', $roleid );
$roleid = $roleid[0];

$isRORole = false;

// Check if role is rootrole
$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");
$anonGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONGROUPID");
if ($rootGroupId == $roleid) {
	$isRORole = true;
}

$leadingLines = $this->request->parameters['leadingLines'];
$leadingLinesArray = array();
for ($i=0;$i<strlen($leadingLines);$i++) {
	array_push( $leadingLinesArray, substr($leadingLines, $i, 1) );
}

$yg_id = explode('-', $yg_id);
$obj_id = $yg_id[0];
$siteID = $yg_id[1];

$maxlevels = 5;

switch($yg_type) {
	case 'pages':
		sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
		$icons = new Icons();
		$pageMgr = new PageMgr($siteID);
		$page = $pageMgr->getPage($obj_id);
		if ($page) {
			$base = $page->get();
			$maxlevels = $base['LEVEL'] + 2;
			$objects = $pageMgr->getList($obj_id, array('SUBNODES'), $maxlevels, $roleid);
			$objects = $pageMgr->getAdditionalTreeInfo(false, $objects);

			foreach($objects as $objectIndex => $object_item) {
				$iconData = getIconForPage($object_item);
				$objects[$objectIndex]['ICON'] = $iconData['iconclass'];
				$objects[$objectIndex]['STYLE'] = $iconData['style'];
				$objects[$objectIndex]['SITEID'] = $siteID;
			}
		}
		sUserMgr()->unimpersonate();
		break;

	case 'cblocks':
		sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
		$cb = sCblockMgr()->getCblock($obj_id);
		if ($cb) {
			$base = $cb->get();
			$maxlevels = $base['LEVEL'] + 2;
			$objects = sCblockMgr()->getList($obj_id, array('SUBNODES'), $maxlevels, $roleid);
			$objects = sCblockMgr()->getAdditionalTreeInfo(false, $objects);

			// Check if the folder for blind contentblocks already exists and create it if it doesn't exist
			$embeddedCblockFolder = (int)sConfig()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");
			$smarty->assign("embeddedCblockFolder", $embeddedCblockFolder);
		}
		sUserMgr()->unimpersonate();
		break;

	case 'files':
		sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
		$filetypeMgr = new Filetypes();
		$objects = sFileMgr()->getList($obj_id, array('SUBNODES'), 'group2.LFT', $maxlevels, $roleid);
		$objects = sFileMgr()->getAdditionalTreeInfo(false, $objects);
		$filetypes = $filetypeMgr->getList();
		foreach($objects as $objects_idx => $objects_item) {
			foreach($filetypes as $filetypes_item) {
				if ($objects_item['FILETYPE'] == $filetypes_item['OBJECTID']) {
					$objects[$objects_idx]['TYPEINFO'] = $filetypes_item;
				}
			}
		}
		sUserMgr()->unimpersonate();
		break;

	case 'tags':
		sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
		$tagMgr = new Tags();
		$objects = $tagMgr->getList($obj_id, array('SUBNODES'), true, $maxlevels, $roleid);
		$objects = $tagMgr->getAdditionalTreeInfo(false, $objects);
		sUserMgr()->unimpersonate();
		break;

	case 'usergroups':
		// No subnodes
		break;

	case 'system':
		// No subnodes
		break;
}

$smarty->assign('isRORole', $isRORole);
$smarty->assign("objects", $objects);
$smarty->assign("site", $siteID );
$smarty->assign("objecttype", $yg_type);
$smarty->assign("maxlevels", $maxlevels );
$smarty->assign("leading_lines", $leadingLinesArray );
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>