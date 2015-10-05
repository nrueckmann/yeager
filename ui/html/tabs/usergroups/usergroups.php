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

switch(strtolower($this->page)) {
	case 'tab_usergroup_pages':
	case 'tab_usergroup_pages_inner':
		$icons = new Icons();
		$objecttype = 'pages';
		$siteMgr = new Sites();
		$sites = $siteMgr->getList(true);
		$siteID = $this->request->parameters['site'];
		if (!$siteID) {
			$siteID = $sites[0]['ID'];
		}
		foreach($sites as $sites_item) {
			if ($sites_item['ID'] == $siteID) {
				$sitename = $sites_item['NAME'];
			}
		}

		if ($siteID) {
			$pageMgr = new PageMgr($siteID);
			$objects = $pageMgr->getList(0, array(), $maxlevels, $roleid);
			$objects = $pageMgr->getAdditionalTreeInfo(false, $objects);

			foreach($objects as $objectIndex => $object_item) {
				$iconData = getIconForPage($object_item);
				$objects[$objectIndex]['ICON'] = $iconData['iconclass'];
				$objects[$objectIndex]['STYLE'] = $iconData['style'];
				$objects[$objectIndex]['SITEID'] = $siteID;
			}

			// Use translated name for root-node
			$objects[0]['NAME'] = ($itext['TXT_PAGES']!='')?($itext['TXT_PAGES']):('$TXT_PAGES');

			$smarty->assign("sites", $sites);
			$smarty->assign("sitename", $sitename);
			$smarty->assign("sites", $sites);
		}
		break;

	case 'tab_usergroup_cblocks':
		$objecttype = $site = 'cblocks';
		$objects = sCblockMgr()->getList(0, array(), $maxlevels, $roleid);
		$objects = sCblockMgr()->getAdditionalTreeInfo(false, $objects);

		// Check if the folder for blind contentblocks already exists and create it if it doesn't exist
		$embeddedCblockFolder = (int)sConfig()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");

		$real_contentblocks = array();
		foreach($objects as $object_item) {
			if ( ($object_item['ID']!=$embeddedCblockFolder) &&
				 (($object_item['PARENT']!=$embeddedCblockFolder) ||
				  ($embeddedCblockFolder==''))
				) {
				array_push( $real_contentblocks, $object_item );
			}
		}
		$objects = $real_contentblocks;
		if ($embeddedCblockFolder) {
			$objects[0]['CHILDREN'] = $objects[0]['CHILDREN']-1;
		}

		// Use translated name for root-node
		$objects[0]['NAME'] = ($itext['TXT_CONTENTBLOCKS']!='')?($itext['TXT_CONTENTBLOCKS']):('$TXT_CONTENTBLOCKS');

		$smarty->assign("embeddedCblockFolder", $embeddedCblockFolder);
		break;

	case 'tab_usergroup_files':
		$objecttype = $site = 'files';
		$fileMgr = sFileMgr();
		$filetypeMgr = new Filetypes();
		$objects = $fileMgr->getList(0, array(), 'group2.LFT', $maxlevels, $roleid);
		$objects = $fileMgr->getAdditionalTreeInfo(false, $objects);
		$filetypes = $filetypeMgr->getList();
		foreach($objects as $objects_idx => $objects_item) {
			foreach($filetypes as $filetypes_item) {
				if ($objects_item['FILETYPE'] == $filetypes_item['OBJECTID']) {
					$objects[$objects_idx]['TYPEINFO'] = $filetypes_item;
				}
			}
		}

		// Use translated name for root-node
		$objects[0]['NAME'] = ($itext['TXT_FILES']!='')?($itext['TXT_FILES']):('$TXT_FILES');
		break;

	case 'tab_usergroup_tags':
		$objecttype = $site = 'tags';
		$tagMgr = new Tags();
		$objects = $tagMgr->getList(0, array(), true, $maxlevels, $roleid);
		$objects = $tagMgr->getAdditionalTreeInfo(false, $objects);

		// Use translated name for root-node
		$objects[0]['NAME'] = ($itext['TXT_TAGS']!='')?($itext['TXT_TAGS']):('$TXT_TAGS');
		break;

	case 'tab_usergroup_mailings':
		$objecttype = $site = 'mailings';
		$mailingMgr = new MailingMgr();
		$objects = $mailingMgr->getList(0, array(), $maxlevels, $roleid);
		$objects = $mailingMgr->getAdditionalTreeInfo(false, $objects);
		$objects = array_reverse($objects, false);
		$objects[0]['FOLDER'] = 1;

		// Use translated name for root-node
		$objects[0]['NAME'] = ($itext['TXT_MAILINGS']!='')?($itext['TXT_MAILINGS']):('$TXT_MAILINGS');
		break;

	case 'tab_usergroup_usergroups':
		$objecttype = $site = 'usergroups';
		$objects = sUsergroups()->getList(true);
		foreach($objects as $objectIndex => $object_item) {
			$objects[$objectIndex]['LEVEL'] = 1;
			$objects[$objectIndex]['PARENT'] = 0;
			$usergroupPermissions = sUsergroups()->usergroupPermissions->getByUsergroup($roleid, $objects[$objectIndex]['ID']);
			$objects[$objectIndex]['RREAD'] = $usergroupPermissions['RREAD'];
			$objects[$objectIndex]['RWRITE'] = $usergroupPermissions['RWRITE'];
			$objects[$objectIndex]['RDELETE'] = $usergroupPermissions['RDELETE'];
		}
		break;

	case 'tab_usergroup_system':
		$objecttype = $site = 'system';

		$privileges = sUsergroups()->permissions->getByUsergroup($roleid, 1);

		$objects = array(
			array('ID' =>  99, 'NAME' => ($itext['TXT_PERM_YEAGER_LOGIN'])?($itext['TXT_PERM_YEAGER_LOGIN']):('$TXT_PERM_YEAGER_LOGIN'), 'LEVEL' => 1, 'PARENT' => 0,
				'RREAD' => $privileges['RBACKEND'], 'CHILDREN' => 24
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
			array('ID' => 14, 'NAME' => ($itext['TXT_PERM_PROPERTIES'])?($itext['TXT_PERM_PROPERTIES']):('$TXT_PERM_PROPERTIES'), 'LEVEL' => 2, 'PARENT' => 99,
				'RREAD' => $privileges['RPROPERTIES']
			),
			array('ID' => 15, 'NAME' => ($itext['TXT_PERM_FILETYPES'])?($itext['TXT_FILETYPES']):('$TXT_PERM_FILETYPES'), 'LEVEL' => 2, 'PARENT' => 99,
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
			array('ID' => 10, 'NAME' => ($itext['TXT_PERM_DATA'])?($itext['TXT_PERM_DATA']):('$TXT_PERM_DATA'), 'LEVEL' => 2, 'PARENT' => 99,
				'RREAD' => $privileges['RDATA']
			),
			array('ID' =>  5, 'NAME' => ($itext['TXT_PERM_USER'])?($itext['TXT_PERM_USER']):('$TXT_PERM_USER'), 'LEVEL' => 2, 'PARENT' => 99,
				'RREAD' => $privileges['RUSERS']
			),
			array('ID' =>  6, 'NAME' => ($itext['TXT_PERM_USERGROUPS'])?($itext['TXT_PERM_USERGROUPS']):('$TXT_PERM_USERGROUPS'), 'LEVEL' => 2, 'PARENT' => 99,
				'RREAD' => $privileges['RUSERGROUPS']
			)
		);
		break;
}

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
$smarty->assign("objecttype", $objecttype);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>