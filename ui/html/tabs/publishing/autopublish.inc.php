<?php

$objectID = $this->request->parameters['objectID'];
$siteID = $this->request->parameters['site'];
//$item_index = $this->request->parameters['item_index'];
//$item_version = $this->request->parameters['item_version'];

$date = getdate();
$item_timestamp = gmmktime($date['hours']+1, 0, 0, $date['mon'], $date['mday'], $date['year']) + 86400;
$item_version = ALWAYS_LATEST_APPROVED_VERSION;
$parameters = array( 'VERSION' => $item_version );

$user = new User(sUserMgr()->getCurrentUserID());
$userinfo = $user->get();
$userinfo['PROPS'] = $user->properties->getValues( sUserMgr()->getCurrentUserID() );

switch ($siteID) {
	case 'cblock':
		// Contentblocks
		$objecttype = 'cblock';
		$cb = sCblockMgr()->getCblock($objectID);
		$versions = $cb->getVersions();
		$item_index = sCblockMgr()->scheduler->schedule($objectID, 'SCH_AUTOPUBLISH', $item_timestamp, $parameters);

		// Add to history
		$lastfinalversion = $cb->getLatestApprovedVersion();
		$cb->history->add( HISTORYTYPE_CO, $item_timestamp, $lastfinalversion, 'TXT_CBLOCK_H_AUTOPUBLISH_ADDED', $item_index );
		break;

	default:
	case 'page':
		// Pages
		$objecttype = 'page';

		$PageMgr = new PageMgr($siteID);
		$page = $PageMgr->getPage($objectID);
		$versions = $page->getVersions( $objectID );
		$item_index = $page->scheduler->schedule($objectID, 'SCH_AUTOPUBLISH', $item_timestamp, $parameters);

		// Add to history
		$lastfinalversion = $page->getLatestApprovedVersion( $objectID );
		$page->history->add( HISTORYTYPE_PAGE, $item_timestamp, $lastfinalversion, 'TXT_PAGE_H_AUTOPUBLISH_ADDED', $item_index );
		break;
}

for ($i = 0; $i < count($versions); $i++) {
	$uid = $versions[$i]['CREATEDBY'];
	$user = new User($uid);
	$uinfo = $user->get();
	$uinfo['PROPS'] = $user->properties->getValues( $uid );
	$versions[$i]['USERNAME'] = $uinfo['PROPS']['LASTNAME'];
	$versions[$i]['VORNAME'] = $uinfo['PROPS']['FIRSTNAME'];
}

$smarty->assign('userinfo', $userinfo);
$smarty->assign('objecttype', $objecttype);
$smarty->assign('versions', $versions);
$smarty->assign('item_index', $item_index);
$smarty->assign('item_version', $item_version);
$smarty->assign('item_timestamp', TStoLocalTS($item_timestamp));
$smarty->assign('item_added', true);

$smarty->display('file:'.$this->page_template);

?>