<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$wid = $this->request->parameters['wid'];
$type = $this->request->parameters['yg_type'];
$tag_id = explode('-',$ygid );
$tag_id = $tag_id[0];

switch ($type) {
	case 'tag':
		/*
		Medien,
		Mailings,
		Contentblocks,
		Seiten (auch seiten die blinde contentblocks enthalten mit control typ tag),
		*/

		$incoming_files = array();
		$incoming_cos = array();
		$incoming_mailings = array();
		$incoming_pages = array();

		// Get all files with this tag
		$filterArray = array();
		$filterArray[] = array(
			'TYPE' 		=> 'DELETED',
			'OPERATOR'	=> 'is_not',
			'VALUE'		=>	1
		);
		$tagged_files = sFileMgr()->tags->getByTag( $tag_id, "OBJECTORDER DESC", "OR", false, $filterArray );
		foreach ($tagged_files as $tagged_file_item) {
			$file = sFileMgr()->getFile($tagged_file_item['OBJECTID']);
			if ($file) {
				$objectInfo = $file->get();
				$pr = sFileMgr()->getParents( $tagged_file_item['OBJECTID'] );
				array_pop( $pr );
				$objectparents = $pr;
				array_push( $incoming_files, array(
					'ID' => $tagged_file_item['OBJECTID'],
					'NAME' => $objectInfo['NAME'],
					'PARENTS' => $objectparents,
					'IDENTIFIER' => $objectInfo['IDENTIFIER'],
					'CODE' => $objectInfo['CODE'],
					'COLOR' => $objectInfo['COLOR'],
					'FOLDER' => $objectInfo['FOLDER']
				) );
			}
		}

		// Get all cos with this tag
		$filterArray = array();
		$filterArray[] = array(
			'TYPE' 		=> 'DELETED',
			'OPERATOR'	=> 'is_not',
			'VALUE'		=>	1
		);
		$tagged_cos = sCblockMgr()->tags->getByTag($tag_id, "OBJECTORDER DESC", "OR", false, $filterArray);

		foreach ($tagged_cos as $tagged_co_item) {
			$pr = sCblockMgr()->getParents( $tagged_co_item['OBJECTID'] );
			$cb = sCblockMgr()->getCblock($tagged_co_item['OBJECTID']);
			$cblockInfo = $cb->get();
			$cblockInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $tagged_co_item['OBJECTID'], "RWRITE");
			$cblockInfo['RDELETE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $tagged_co_item['OBJECTID'], "RDELETE");
			$styleData = getStyleForContentblock($cblockInfo, true);
			array_pop( $pr );
			$objectparents = $pr;
			array_push( $incoming_cos, array(
				'ID' => $cblockInfo['OBJECTID'],
				'STYLE' => $styleData,
				'NAME' => $cblockInfo['NAME'],
				'FOLDER' => $cblockInfo['FOLDER'],
				'PARENTS' => $objectparents
			) );
		}

		// Get all mailings with this tag
		$filterArray = array();
		$filterArray[] = array(
			'TYPE' 		=> 'DELETED',
			'OPERATOR'	=> 'is_not',
			'VALUE'		=>	1
		);
		$tagged_mailings = sMailingMgr()->tags->getByTag($tag_id, "OBJECTORDER DESC", "OR", false, $filterArray);

		foreach ($tagged_mailings as $tagged_mailing_item) {
			$pr = sMailingMgr()->getParents( $tagged_mailing_item['OBJECTID'] );
			$mailing = sMailingMgr()->getMailing($tagged_mailing_item['OBJECTID']);
			$mailingInfo = $mailing->get();
			$mailingInfo['RWRITE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $tagged_mailing_item['OBJECTID'], "RWRITE");
			$mailingInfo['RDELETE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $tagged_mailing_item['OBJECTID'], "RDELETE");
			$styleData = getStyleForContentblock($mailingInfo, true);
			array_pop( $pr );
			$objectparents = $pr;
			array_push( $incoming_mailings, array(
				'ID' => $mailingInfo['OBJECTID'],
				'STYLE' => $styleData,
				'NAME' => $mailingInfo['NAME'],
				'PARENTS' => $objectparents
			) );
		}

		// Get all sites
		$sites = sSites()->getList();
		foreach($sites as $currentSite) {
			// Get all pages with this tag
			$pageMgr = new PageMgr($currentSite['ID']);
			$filterArray = array();
			$filterArray[] = array(
				'TYPE' 		=> 'DELETED',
				'OPERATOR'	=> 'is_not',
				'VALUE'		=>	1
			);
			$tagged_pages = $pageMgr->tags->getByTag( $tag_id, "OBJECTORDER DESC", "OR", false, $filterArray );
			foreach ($tagged_pages as $tagged_page_item) {
				$page = $pageMgr->getPage( $tagged_page_item['OBJECTID'] );
				if ($page) {
					$lpv = $page->getPublishedVersion(true);
					$page = $pageMgr->getPage($tagged_page_item['OBJECTID'], $lpv);
					$pageInfo = $page->get();
					$pr = $pageMgr->getParents($tagged_page_item['OBJECTID']);
					$pageInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $tagged_page_item['OBJECTID'], "RWRITE");
					$pageInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $tagged_page_item['OBJECTID'], "RDELETE");
					$iconData = getIconForPage($pageInfo);
					array_pop( $pr );
					$objectparents = $pr;
					array_push( $incoming_pages, array(
						'ID' => $tagged_page_item['OBJECTID'],
						'SITEID' => $currentSite['ID'],
						'ICON' => $iconData['iconclass'],
						'STYLE' => $iconData['style'],
						'NAME' => $pageInfo['NAME'],
						'PARENTS' => $objectparents,
						'SITENAME' => $currentSite['NAME']
					) );
				}
			}
		}

		$smarty->assign("incoming_files", $incoming_files );
		$smarty->assign("incoming_cos", $incoming_cos );
		$smarty->assign("incoming_mailings", $incoming_mailings );
		$smarty->assign("incoming_pages", $incoming_pages );
		break;
}

$smarty->assign("refresh", $refresh );
$smarty->assign("pageInfo", $pageInfo);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>