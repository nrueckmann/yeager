<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];

$template = explode('-', $ygid);
$template = $template[0];
$icons = new Icons();

$sites = sSites()->getList();

$all_pages = array();
foreach($sites as $curr_site) {
    $pageMgr = new PageMgr($curr_site['ID']);
	$pages = $pageMgr->getPagesByTemplate( $template );
	if (count($pages)>0) {
		foreach($pages as $curr_idx => $curr_page) {
			$parents = $pageMgr->getParents($curr_page['ID']);
			$pages[$curr_idx]['PARENTS'] = $parents;
			$pages[$curr_idx]['SITE'] = $curr_site['ID'];
			$pages[$curr_idx]['SITENAME'] = $curr_site['NAME'];

			$tmpPage = $pageMgr->getPage($curr_page['ID']);
			$tmpPageInfo = $tmpPage->get();
			$tmpPageInfo['RWRITE'] = $tmpPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $curr_page['ID'], "RWRITE");
			$tmpPageInfo['RDELETE'] = $tmpPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $curr_page['ID'], "RDELETE");
			$pages[$curr_idx]['PAGEINFO'] = $tmpPageInfo;
			$iconData = getIconForPage($tmpPageInfo);
			$pages[$curr_idx]['ICON'] = $iconData['iconclass'];
			$pages[$curr_idx]['STYLE'] = $iconData['style'];
			$pages[$curr_idx]['HASCHANGED'] = $tmpPageInfo['HASCHANGED'];
		}
		$all_pages = array_merge( $all_pages, $pages );
	}
}

$all_mailings = array();
$mailings = sMailingMgr()->getMailingsByTemplate( $template );

if (count($mailings)>0) {
	foreach($mailings as $curr_idx => $curr_mailing) {
		$tmpMailing = sMailingMgr()->getMailing($curr_mailing['ID']);
		$tmpMailingInfo = $tmpMailing->get();
		$tmpMailingInfo['RWRITE'] = $tmpMailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $curr_mailing['ID'], "RWRITE");
		$tmpMailingInfo['RDELETE'] = $tmpMailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $curr_mailing['ID'], "RDELETE");
		$mailings[$curr_idx]['PAGEINFO'] = $tmpMailingInfo;
		$iconData = getIconForPage($tmpMailingInfo);
		$mailings[$curr_idx]['ICON'] = 'mailing';
		if ($tmpMailingInfo['HASCHANGED']) {
			$mailings[$curr_idx]['STYLE'] = 'changed';
		}
		$mailings[$curr_idx]['HASCHANGED'] = $tmpMailingInfo['HASCHANGED'];
	}
	$all_mailings = array_merge( $all_mailings, $mailings );
}

$smarty->assign("all_pages", $all_pages );
$smarty->assign("all_mailings", $all_mailings );
$smarty->assign("refresh", $refresh );
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>