<?php

$siteMgr = new Sites();
$sites = $siteMgr->getList();

$user = new User(sUserMgr()->getCurrentUserID());

// Last changes from pages per site into an array
$lastchanges['PAGES'] = array();
$startx = 0;
for ($i = 0; $i < count($sites); $i++) {
	$msites[] = $sites[$i];
	$PageMgr = sPageMgr($sites[$i]["ID"]);
	$changeslist = $PageMgr->history->getLastChange(HISTORYTYPE_PAGE, $sites[$i]["ID"]);
	for ($x = $startx; $x < count($changeslist)+$startx; $x++) {
		$lastchanges['PAGES'][$x] = $changeslist[$x-$startx];
		$lastchanges['PAGES'][$x]['SITE'] = $sites[$i]['ID'];
	}
	$startx = $x;
}

// Last changes from content, files, mailings and comments
$lastchanges['CONTENT'] = sCblockMgr()->history->getLastChange(HISTORYTYPE_CO);

$fileMgr = sFileMgr();
$lastchanges['FILES'] = $fileMgr->history->getLastChange(HISTORYTYPE_FILE);

$mailingMgr = new MailingMgr();
$lastchanges['MAILING'] = $mailingMgr->history->getLastChange(HISTORYTYPE_MAILING);

/*
$commentMgr = new Comments();
$lastchanges['COMMENT'] = $commentMgr->getAllComments( $filterArray, $pageDirLimit );
*/

// Enrich information for pages, content, files, mailing
foreach(array('PAGES', 'CONTENT', 'FILES', 'MAILING') as $item) {
	foreach($lastchanges[$item] as $lastchanges_idx => $lastchanges_item) {
		$lastchanges[$item][$lastchanges_idx]['DATETIME'] = TStoLocalTS($lastchanges_item['DATETIME']);
		$user = new User($lastchanges_item['UID']);
		$userinfo = $user->get();
		if ($userinfo['ID']) {
			$userinfo['FIRSTNAME'] = $user->properties->getValue('FIRSTNAME');
			$userinfo['LASTNAME'] = $user->properties->getValue('LASTNAME');
		}
		$lastchanges[$item][$lastchanges_idx]['USERINFO'] = $userinfo;
	}
}

// Enrich information for comments
foreach($lastchanges['COMMENT'] as $lastchanges_idx => $lastchanges_item) {
	$lastchanges['COMMENT'][$lastchanges_idx]['CREATEDTS'] = TStoLocalTS($lastchanges['COMMENT'][$lastchanges_idx]['CREATEDTS']);
	$lastchanges['COMMENT'][$lastchanges_idx]['CHANGEDTS'] = TStoLocalTS($lastchanges['COMMENT'][$lastchanges_idx]['CHANGEDTS']);
	if ($lastchanges_item['USERID']) {
		$user = new User($lastchanges_item['USERID']);
		$userinfo = $user->get();
		if ($userinfo['ID']) {
			$userinfo['FIRSTNAME'] = $user->properties->getValue('FIRSTNAME');
			$userinfo['LASTNAME'] = $user->properties->getValue('LASTNAME');
		}
		$lastchanges['COMMENT'][$lastchanges_idx]['USERINFO'] = $userinfo;
	}
}

$user = new User(sUserMgr()->getCurrentUserID());
$smarty->assign("RPAGES", $user->checkPermission("RPAGES"));
$smarty->assign("RCONTENTBLOCKS", $user->checkPermission("RCONTENTBLOCKS"));
$smarty->assign("RFILES", $user->checkPermission("RFILES"));
$smarty->assign("RCOMMENTS", $user->checkPermission("RCOMMENTS"));
$smarty->assign("RMAILINGS", $user->checkPermission( "RMAILINGS"));
$smarty->assign("RTAGS", $user->checkPermission("RTAGS"));
$smarty->assign("RUSERS", $user->checkPermission("RUSERS"));
$smarty->assign("RUSERGROUPS", $user->checkPermission("RUSERGROUPS"));
$smarty->assign("RDATA", $user->checkPermission("RDATA"));
$smarty->assign("RSITES", $user->checkPermission("RSITES"));
$smarty->assign("RTEMPLATES", $user->checkPermission("RTEMPLATES"));
$smarty->assign("REXTENSIONS_PAGE", $user->checkPermission("REXTENSIONS_PAGE"));
$smarty->assign("REXTENSIONS_MAILING", $user->checkPermission("REXTENSIONS_MAILING"));
$smarty->assign("REXTENSIONS_FILE", $user->checkPermission("REXTENSIONS_FILE"));
$smarty->assign("REXTENSIONS_CBLOCK", $user->checkPermission("REXTENSIONS_CBLOCK"));
$smarty->assign("RENTRYMASKS", $user->checkPermission("RENTRYMASKS"));
$smarty->assign("REXPORT", $user->checkPermission("REXPORT"));
$smarty->assign("RIMPORT", $user->checkPermission("RIMPORT"));
$smarty->assign("REXTENSIONS_CBLISTVIEW", $user->checkPermission("REXTENSIONS_CBLISTVIEW"));
$smarty->assign("RUPDATER", $user->checkPermission("RUPDATER"));
$smarty->assign("RPROPERTIES", $user->checkPermission("RPROPERTIES"));
$smarty->assign("RFILETYPES", $user->checkPermission("RFILETYPES"));
$smarty->assign("RCOMMENTCONFIG", $user->checkPermission("RCOMMENTCONFIG"));
$smarty->assign("RMAILINGCONFIG", $user->checkPermission("RMAILINGCONFIG"));
$smarty->assign("RVIEWS", $user->checkPermission("RVIEWS"));
$smarty->assign('lastchanges_pages', $lastchanges['PAGES']);
$smarty->assign('lastchanges_content', $lastchanges['CONTENT']);
$smarty->assign('lastchanges_files', $lastchanges['FILES']);
$smarty->assign('lastchanges_mailing', $lastchanges['MAILING']);
$smarty->assign('lastchanges_comments', $lastchanges['COMMENT']);


$smarty->assign('sites', $sites);
$smarty->display('file:'.$this->page_template);

?>