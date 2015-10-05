<?php

// Check if the folder for blind contentblocks already exists and create it if it doesn't exist
$embeddedCblockFolder = (int)sConfig()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");

$siteMgr = new Sites();
$sites = $siteMgr->getList();

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];

$entrymask = explode('-', $ygid);
$entrymask = $entrymask[0];
$icons = new Icons();
$mailingMgr = new MailingMgr();
$entrymaskMgr = new Entrymasks();

$entrymaskInfo = $entrymaskMgr->get( $entrymask );

// Get all contentblocks with this entrymask
$contentblock_ids = sCblockMgr()->getCblockLinkByEntrymaskId( $entrymask );
$contentblocks = array();
$contentblocks_blind = array();

foreach($contentblock_ids as $contentblock_id) {
	$dcb = sCblockMgr()->getCblock($contentblock_id['CBLOCKID']);
	if ($dcb) {
		$co_data = $dcb->get();
		$objectparents = sCblockMgr()->getParents($contentblock_id['CBLOCKID']);
		array_pop( $objectparents );
		$co_data['PARENTS'] = $objectparents;
		$dcb = sCblockMgr()->getCblock($contentblock_id['CBLOCKID']);
		$co_data['CBLOCKINFO'] = $dcb->get();
		$co_data['CBLOCKINFO']['RWRITE'] = $dcb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $contentblock_id['CBLOCKID'], "RWRITE");
		$co_data['CBLOCKINFO']['RDELETE'] = $dcb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $contentblock_id['CBLOCKID'], "RDELETE");
		$styleData = getStyleForContentblock($co_data['CBLOCKINFO']);
		$co_data['STYLE'] = $styleData;
		$co_data['HASCHANGED'] = $co_data['CBLOCKINFO']['HASCHANGED'];

		if ( ($objectparents[0][0]['ID'] == $embeddedCblockFolder) ||
			 ($co_data['CBLOCKINFO']['EMBEDDED'] == 1) ) {
			$contentblocks_blind[] = $co_data;
		} else {
			$contentblocks[] = $co_data;
		}
	}
}

// Get all pages & mailings for all normal contentblocks
foreach($contentblocks as $idx => $contentblock) {
	// Get links to pages
	$a = 0;
	$linkedpages = array();
	$cbb = sCblockMgr()->getCblock($contentblock['OBJECTID']);
	for ($i = 0; $i < count($sites); $i++) {
		$linkedtosite = $cbb->getLinkedPageVersions($sites[$i]["ID"], true);
		if (count($linkedtosite)>0) {
			for ($x = 0; $x < count($linkedtosite);$x++) {
				$linkpageid = $linkedtosite[$x]["ID"];
				$lThePageMgr = new PageMgr($sites[$i]["ID"]);
				$linkpageparents = $lThePageMgr->getParents($linkpageid);
				array_pop( $linkpageparents );
				$lPage = $lThePageMgr->getPage($linkpageid);
				$pageversions = $lPage->getVersionsByCblockId($contentblock['ID']);
				$linkedPageInfo = $lPage->get();
				$addToArray = true;
				foreach($linkedpages as $linkedpagesItem) {
					if ( ($linkedpagesItem['PAGEID'] == $linkpageid) &&
						 ($linkedpagesItem['SITEID'] == $sites[$i]["ID"]) ) {
						$addToArray = false;
					}
				}
				if ($addToArray) {
					$linkedpages[$a]["SITEID"] = $sites[$i]["ID"];
					$linkedpages[$a]["SITENAME"] = $sites[$i]["NAME"];
					$linkedpages[$a]["PAGEID"] = $linkpageid;
					$linkedpages[$a]["HASH"] = $sites[$i]["ID"].'-'.$linkpageid;
					$linkedpages[$a]["PAGENAME"] = $linkedPageInfo["NAME"];
					$linkedpages[$a]["HASCHANGED"] = $linkedtosite[$x]["HASCHANGED"];
					$linkedpages[$a]["PARENTS"] = $linkpageparents;
					$linkedpages[$a]["VERSIONS"] = $pageversions;
					$linkedPageInfo['RWRITE'] = $lPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkpageid, "RWRITE");
					$linkedPageInfo['RDELETE'] = $lPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkpageid, "RDELETE");
					$linkedpages[$a]['PAGEINFO'] = $linkedPageInfo;
					$iconData = getIconForPage($linkedPageInfo);
					$linkedpages[$a]['ICON'] = $iconData['iconclass'];
					$linkedpages[$a]['STYLE'] = $iconData['style'];
					$a++;
				}
			}
		}
	}
	$pagesHash = '';
	foreach($linkedpages as $linkedpagesItem) {
		$pagesHash .= $linkedpagesItem['HASH'].'#';
	}
	$contentblocks[$idx]['PAGES'] = $linkedpages;
	$contentblocks[$idx]['SITENAME'] = $linkedpages[0]['SITENAME'];
	$contentblocks[$idx]['PAGESHASH'] = $pagesHash;

	// Get links to mailings
	$a = 0;
	$linkedmailings = array();
	$linkedtomailings = $cbb->getLinkedMailingVersions(true);
	if (count($linkedtomailings)>0) {
		for ($x = 0; $x < count($linkedtomailings);$x++) {
			$linkmailingid = $linkedtomailings[$x]['ID'];
			$lMailing = $mailingMgr->getMailing($linkmailingid);
			$mailingversions = $lMailing->getVersionsByCblockId($contentblock['ID']);
			$linkedMailingInfo = $lMailing->get();
			$linkedmailings[$a]['MAILINGID'] = $linkmailingid;
			$linkedmailings[$a]['HASH'] = $linkmailingid;
			$linkedmailings[$a]['MAILINGNAME'] = $linkedMailingInfo['NAME'];
			$linkedmailings[$a]['HASCHANGED'] = $linkedtomailings[$x]['HASCHANGED'];
			$linkedmailings[$a]['VERSIONS'] = $pageversions;
			$linkedMailingInfo['RWRITE'] = $lMailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkmailingid, 'RWRITE');
			$linkedMailingInfo['RDELETE'] = $lMailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkmailingid, 'RDELETE');
			$linkedmailings[$a]['MAILINGINFO'] = $linkedMailingInfo;
			$linkedmailings[$a]['ICON'] = 'mailing';
			if ($linkedmailings[$a]['HASCHANGED']) {
				$linkedmailings[$a]['STYLE'] = 'changed';
			}
			$a++;
		}
	}
	$mailingsHash = '';
	foreach($linkedmailings as $linkedmailingsItem) {
		$mailingsHash .= $linkedmailingsItem['HASH'].'#';
	}
	$contentblocks[$idx]['MAILINGS'] = $linkedmailings;
	$contentblocks[$idx]['MAILINGSHASH'] = $mailingsHash;
}

// Get all pages & mailings for all blind contentblocks
$contentblocks_blind_final = array();
foreach($contentblocks_blind as $idx => $contentblock_blind) {
	// Get links to pages
	$a = 0;
	$linkedpages = array();
	$cbb = sCblockMgr()->getCblock($contentblock_blind['OBJECTID']);
	$cbbVersions = $cbb->getVersions();
	foreach ($cbbVersions as $cbbVersion) {
		$cbv = sCblockMgr()->getCblock($contentblock_blind['OBJECTID'], $cbbVersion['VERSION']);
		for ($i = 0; $i < count($sites); $i++) {
			$linkedtosite = $cbv->getLinkedPageVersions($sites[$i]["ID"], false, true);
			if (count($linkedtosite)>0) {
				for ($x = 0; $x < count($linkedtosite);$x++) {
					$linkpageid = $linkedtosite[$x]["ID"];
					$lThePageMgr = new PageMgr($sites[$i]["ID"]);
					$linkpageparents = $lThePageMgr->getParents($linkpageid);
					array_pop( $linkpageparents );
					$lPage = $lThePageMgr->getPage($linkpageid);
					if ($lPage) {
						$pageversions = $lPage->getVersionsByCblockId($co);
						$linkedPageInfo = $lPage->get();
						$addToArray = true;
						foreach($linkedpages as $linkedpagesItem) {
							if ( ($linkedpagesItem['PAGEID'] == $linkpageid) &&
								 ($linkedpagesItem['SITEID'] == $sites[$i]["ID"]) ) {
								$addToArray = false;
							}
						}
						if ($addToArray) {
							$linkedpages[$a]["SITEID"] = $sites[$i]["ID"];
							$linkedpages[$a]["SITENAME"] = $sites[$i]["NAME"];
							$linkedpages[$a]["PAGEID"] = $linkpageid;
							$linkedpages[$a]["HASH"] = $sites[$i]["ID"].'-'.$linkpageid;
							$linkedpages[$a]["PAGENAME"] = $linkedPageInfo["NAME"];
							$linkedpages[$a]["HASCHANGED"] = $linkedtosite[$x]["HASCHANGED"];
							$linkedpages[$a]["PARENTS"] = $linkpageparents;
							$linkedpages[$a]["VERSIONS"] = $pageversions;
							$linkedPageInfo['RWRITE'] = $lPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkpageid, "RWRITE");
							$linkedPageInfo['RDELETE'] = $lPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkpageid, "RDELETE");
							$linkedpages[$a]['PAGEINFO'] = $linkedPageInfo;
							$iconData = getIconForPage($linkedPageInfo);
							$linkedpages[$a]['ICON'] = $iconData['iconclass'];
							$linkedpages[$a]['STYLE'] = $iconData['style'];
							$a++;
						}
					}
				}
			}
		}
	}
	$pagesHash = '';
	foreach($linkedpages as $linkedpagesItem) {
		$pagesHash .= $linkedpagesItem['HASH'].'#';
	}
	foreach($linkedpages as $linkedpagesItem) {
		$contentblocks_blind[$idx]['PAGES'][] = $linkedpagesItem;
	}
	$contentblocks_blind[$idx]['SITENAME'] = $linkedpages[0]['SITENAME'];
	$contentblocks_blind[$idx]['PAGESHASH'] = $pagesHash;
	if (count($linkedpages) > 0) {
		$contentblocks_blind_final[] = $contentblocks_blind[$idx];
	}

	// Get links to mailings
	$a = 0;
	$linkedmailings = array();
	$linkedtomailings = $cbb->getLinkedMailingVersions(true);
	if (count($linkedtomailings)>0) {
		for ($x = 0; $x < count($linkedtomailings);$x++) {
			$linkmailingid = $linkedtomailings[$x]['ID'];
			$lMailing = $mailingMgr->getMailing($linkmailingid);
			$mailingversions = $lMailing->getVersionsByCblockId($contentblock['ID']);
			$linkedMailingInfo = $lMailing->get();
			$linkedmailings[$a]['MAILINGID'] = $linkmailingid;
			$linkedmailings[$a]['HASH'] = $linkmailingid;
			$linkedmailings[$a]['MAILINGNAME'] = $linkedMailingInfo['NAME'];
			$linkedmailings[$a]['HASCHANGED'] = $linkedtomailings[$x]['HASCHANGED'];
			$linkedmailings[$a]['VERSIONS'] = $pageversions;
			$linkedMailingInfo['RWRITE'] = $lMailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkmailingid, 'RWRITE');
			$linkedMailingInfo['RDELETE'] = $lMailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkmailingid, 'RDELETE');
			$linkedmailings[$a]['MAILINGINFO'] = $linkedMailingInfo;
			$linkedmailings[$a]['ICON'] = 'mailing';
			if ($linkedmailings[$a]['HASCHANGED']) {
				//$linkedmailings[$a]['STYLE'] = 'changed';
			}
			$a++;
		}
	}
	$mailingsHash = '';
	foreach($linkedmailings as $linkedmailingsItem) {
		$mailingsHash .= $linkedmailingsItem['HASH'].'#';
	}
	foreach($linkedmailings as $linkedmailingsItem) {
		$contentblocks_blind[$idx]['MAILINGS'][] = $linkedmailingsItem;
	}
	$contentblocks_blind[$idx]['MAILINGSHASH'] = $mailingsHash;

	if (count($linkedmailings) > 0) {
		$contentblocks_blind_final[] = $contentblocks_blind[$idx];
	}
}

$addedPages = array();
$addedMailings = array();
foreach($contentblocks as $contentblocksIdx => $contentblocksItem) {
	if (in_array($contentblocks[$contentblocksIdx]['PAGESHASH'], $addedPages)) {
		/*
		unset($contentblocks[$contentblocksIdx]['PAGES']);
		unset($contentblocks[$contentblocksIdx]['SITENAME']);
		unset($contentblocks[$contentblocksIdx]['PAGESHASH']);
		*/
	} else {
		$addedPages[] = $contentblocks[$contentblocksIdx]['PAGESHASH'];
	}
	if (in_array($contentblocks[$contentblocksIdx]['MAILINGSHASH'], $addedMailings)) {
		unset($contentblocks[$contentblocksIdx]['MAILINGS']);
		unset($contentblocks[$contentblocksIdx]['MAILINGSHASH']);
	} else {
		$addedMailings[] = $contentblocks[$contentblocksIdx]['MAILINGSHASH'];
	}
}
foreach($contentblocks_blind_final as $contentblocksBlindIdx => $contentblocksBlindItem) {
	if (in_array($contentblocks_blind_final[$contentblocksBlindIdx]['PAGESHASH'], $addedPages)) {
		/*
		unset($contentblocks_blind_final[$contentblocksBlindIdx]['PAGES']);
		unset($contentblocks_blind_final[$contentblocksBlindIdx]['SITENAME']);
		unset($contentblocks_blind_final[$contentblocksBlindIdx]['PAGESHASH']);
		*/
	} else {
		$addedPages[] = $contentblocks_blind_final[$contentblocksBlindIdx]['PAGESHASH'];
	}
	if (in_array($contentblocks_blind_final[$contentblocksBlindIdx]['MAILINGSHASH'], $addedMailings)) {
		unset($contentblocks_blind_final[$contentblocksBlindIdx]['MAILINGS']);
		unset($contentblocks_blind_final[$contentblocksBlindIdx]['MAILINGSHASH']);
	} else {
		$addedMailings[] = $contentblocks_blind_final[$contentblocksBlindIdx]['MAILINGSHASH'];
	}
}

$contentblocksFinal = array();
$contentblocksBlindFinal = array();
foreach($contentblocks as $contentblockItem) {
	$addItem = true;
	if ( (count($contentblockItem['PAGES']) == 0) &&
		 (count($contentblockItem['MAILINGS']) == 0) &&
		 ($contentblockItem['CBLOCKINFO']['EMBEDDED'] == 1) ) {
		$addItem = false;
	}
	if ($addItem) {
		$contentblocksFinal[] = $contentblockItem;
	}
}
foreach($contentblocks_blind_final as $contentblockBlindItem) {
	$addItem = true;
	if ( (count($contentblockBlindItem['PAGES']) == 0) &&
		 (count($contentblockBlindItem['MAILINGS']) == 0) &&
		 ($contentblockBlindItem['CBLOCKINFO']['EMBEDDED'] == 1) ) {
		$addItem = false;
	}
	if ($addItem) {
		$contentblocksBlindFinal[] = $contentblockBlindItem;
	}
}

$contentblocksFinal = $contentblocks;
$contentblocksBlindFinal = $contentblocks_blind_final;

$smarty->assign("contentblocks", $contentblocksFinal );
$smarty->assign("contentblocks_blind", $contentblocksBlindFinal );

$smarty->assign("refresh", $refresh );
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>