<?php

$objecttype = $this->request->parameters['objecttype'];
$id = $this->request->parameters['objectid'];
$version = $this->request->parameters['version'];
$onload = $this->request->parameters['onload'];

$user = new User(sUserMgr()->getCurrentUserID());
$userinfo = $user->get();
$userinfo['PROPS'] = $user->properties->getValues( sUserMgr()->getCurrentUserID() );

if (($onload == null || $onload == undefined || $onload == '')) $onload = false;

if ($objecttype == "file") {

	$view = $this->request->parameters['view'];
	$zoom = $this->request->parameters['zoom'];
	$crop = $this->request->parameters['crop'];

	if ($zoom <= 0) $zoom = 100;
	if (($crop == null || $crop == undefined)) $crop = false;

	if ($version == '') {
		$file = new File($id);
		$version = $file->getLatestApprovedVersion();
		$file = new File($id, $version);
	} else {
		$file = new File($id, $version);
	}
	$versions = $file->getVersions();

	if ($versions[0]['APPROVED'] == 0) {
		array_shift($versions);
	}

	/*
	$latestVersions = array();
	foreach($versions as $version_item) {
		if ($version_item['APPROVED']) {
			$tmpFile = new File($id, $version_item['VERSION']);
			$tmpFileInfo = $tmpFile->get();
			if ($version_item['VERSION'] == $tmpFileInfo['VIEWVERSION']) {
				array_push($latestVersions, $version_item);
			}
		}
	}
	$versions = $latestVersions;
	*/

	$fileinfo = $file->get();
	$name = $fileinfo["NAME"];
	$url = $file->getUrl();
	$views = $file->views->getAssigned();
	$viewinfo = $file->views->getGeneratedViewInfo($views[0]["ID"]);

	$views[0]['WIDTH'] = $viewinfo[0]["WIDTH"];
	$views[0]['HEIGHT'] = $viewinfo[0]["HEIGHT"];

	$viewid = false;

	if ($view != "") {
		for ($i = 0; $i < count($views); $i++) {
			if ($views[$i]["IDENTIFIER"] == $view) {
				$viewid = $views[$i]["ID"];
			}
		}
	}
	if ($viewid == false) $viewid = $views[0]["ID"];

	for ($i = 0; $i < count($views); $i++) {
		$viewinfo = $file->views->getGeneratedViewInfo($views[$i]["ID"]);
		$views[$i]["VIEWTYPE"] = $viewinfo[0]["TYPE"];
		if ($views[$i]["ID"] == $viewid) {
			$fileinfo["VIEWTYPE"] = $viewinfo[0]["TYPE"];
			$fileinfo["VIEW"] = $views[$i]["IDENTIFIER"];
			$fileinfo["WIDTH"] = $viewinfo[0]["WIDTH"];
			$fileinfo["VIEWWIDTH"] = $views[$i]["WIDTH"];
			$fileinfo["CONSTRAINWIDTH"] = $views[$i]["CONSTRAINWIDTH"];
			$fileinfo["VIEWHEIGHT"] = $views[$i]["HEIGHT"];
			$fileinfo["CONSTRAINHEIGHT"] = $views[$i]["CONSTRAINHEIGHT"];
			$fileinfo["HEIGHT"] = $viewinfo[0]["HEIGHT"];
		}
	}
	$smarty->assign("url", $url);
	$smarty->assign("crop",$crop);
	$smarty->assign('fileinfo', $fileinfo );
	$smarty->assign('zoom', $zoom );
	$smarty->assign('views', $views );

} elseif ($objecttype == "page") {
	$siteID = $this->request->parameters['site'];
	$pagemgr = new PageMgr($siteID);
	$page = $pagemgr->getPage($id);
	$pageInfo = $page->get();
	$name = $pageInfo["NAME"];
	$url = $page->getUrl();
	$versions = $page->getVersions();
	if (!$version || ($version == 'live')) {
		$version = $page->getPublishedVersion(true);
	} else if ($version == "working") {
		$version = $versions[0]["VERSION"];
	}
	$smarty->assign("url",$url);
} elseif ($objecttype == "mailing") {
	$mailingMgr = new MailingMgr();
	$mailing = $mailingMgr->getMailing($id);
	$mailingInfo = $mailing->get();
	$name = $mailingInfo['NAME'];
	$url = $mailing->getUrl();
	$versions = $mailing->getVersions();

	// Remove version '0'
	$realVersions = array();
	foreach($versions as $versionsItem) {
		if ($versionsItem['VERSION'] > 0) {
			array_push($realVersions, $versionsItem);
		}
	}
	$versions = $realVersions;

	if (!$version || ($version == 'live')) {
		$version = $mailing->getPublishedVersion(true);
	} else if ($version == 'working') {
		$version = $versions[0]['VERSION'];
	}
	$smarty->assign('url', $url);
}

foreach($versions as $versionsIdx => $versionItem) {
	$versions[$versionsIdx]['CHANGEDTS'] = TStoLocalTS($versionItem['CHANGEDTS']);
}

$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->assign("onload", $onload);
$smarty->assign("site", $siteID);
$smarty->assign("name", $name);
$smarty->assign("id", $id);
$smarty->assign("userinfo", $userinfo);
$smarty->assign("version", $version);
$smarty->assign("versions", $versions);
$smarty->assign('objecttype', $objecttype );
$smarty->display('file:'.$this->page_template);

?>