<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$initload = $this->request->parameters['initload'];
$action = $this->request->parameters['action'];
$displaymode = $this->request->parameters['displaymode'];
$tools = $this->request->parameters['tools'];

$wid = $this->request->parameters['wid'];
$wid_id = explode('_',$wid);
$wid_id = $wid_id[1];


$sortby = $this->request->parameters['sortby'];
$show = $this->request->parameters['show'];
$view = $this->request->parameters['view'];

$fileId = explode('-', $ygid);
$fileId = $fileId[0];

// Get timezone offset
$user = new User(sUserMgr()->getCurrentUserID());
$fileMgr = sFileMgr();
$reftracker = new Reftracker();

$filelist = $fileMgr->getFilesFromFolder($fileId, $sortby, $show);

for ($i = 0; $i < count($filelist); $i++) {
	$file = new File($filelist[$i]['OBJECTID']);
	$finalVersion = $file->getLatestApprovedVersion();
	$file = new File($filelist[$i]['OBJECTID'], $finalVersion);
	$fileInfo = $file->get();

	$filelist[$i]['DATETIME'] = TStoLocalTS($fileInfo['CHANGEDTS']);
	$filelist[$i]['NODELETE'] = (!$file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $filelist[$i]['OBJECTID'], 'RDELETE'));
	$filelist[$i]['NOWRITE'] = (!$file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $filelist[$i]['OBJECTID'], 'RWRITE'));

	$views = $file->views->getAssigned(true);
	$viewinfo = $file->views->getGeneratedViewInfo($views[0]["ID"]);
	$filelist[$i]['WIDTH'] = $viewinfo[0]["WIDTH"];
	$filelist[$i]['HEIGHT'] = $viewinfo[0]["HEIGHT"];

	if ($filelist[$i]['COLOR'] == 'NONE') {
		$filelist[$i]['COLOR'] = 'black';
	}

	$tags = $file->tags->getAssigned();

	for ($t = 0; $t < count($tags); $t++) {
		$tp = array();
		$tp = $file->tags->tree->getParents($tags[$t]['ID']);
		$tp2 = array();
		for ($p = 0; $p < count($tp); $p++) {
			$tinfo = $file->tags->get($tp[$p]);
			$tp2[$p]['ID'] = $tinfo['ID'];
			$tp2[$p]['NAME'] = $tinfo['NAME'];
		}
		$tp2[count($tp2)-1]['NAME'] = ($itext['TXT_TAGS']!='')?($itext['TXT_TAGS']):('$TXT_TAGS');
		array_pop($tp2);
		$tags[$t]['PARENTS'] = $tp2;
	}

	$filelist[$i]['TAGS'] = $tags;

	$filelist[$i]['THUMB'] = 0;
	$filelist[$i]['CLASSNAME'] = '';
	$filelist[$i]['IMAGE'] = 0;

	$webroot = sApp()->webroot;

	$filelist[$i]['DOWNLOAD_URL'] = $webroot . 'download/' . $filelist[$i]['PNAME'];

	$scheduledJobs = sFileMgr()->scheduler->getSchedule($filelist[$i]['OBJECTID']);
	$hiddenViewsToProcess = 0;
	foreach($scheduledJobs as $scheduledJob) {
		if ($scheduledJob['PARAMETERS']['VIEW']['HIDDEN']) {
			$hiddenViewsToProcess++;
		}
	}

	$hiddenviews = $file->views->getHiddenViews();
	foreach($hiddenviews as $hiddenview) {
		if ( ($hiddenview['IDENTIFIER'] == 'yg-thumb') ||
			 ($hiddenview['IDENTIFIER'] == 'yg-list') ) {
			$tmpviewinfo = $file->views->getGeneratedViewInfo($hiddenview['ID']);
			if ($tmpviewinfo[0]['TYPE'] == FILE_TYPE_WEBIMAGE) {
				$filelist[$i]['THUMB'] = 1;
			}
			foreach($scheduledJobs as $scheduledJob) {
				if ($scheduledJob['PARAMETERS']['VIEW']['IDENTIFIER'] == $hiddenview['IDENTIFIER']) {
					$filelist[$i]['THUMB'] = 1;
					$filelist[$i]['CLASSNAME'] = 'noload';
				}
			}
		}
		if ($hiddenview['IDENTIFIER'] == "YGSOURCE") {
			$tmpviewinfo = $file->views->getGeneratedViewInfo($hiddenview['ID']);
			if ($tmpviewinfo[0]['TYPE'] == FILE_TYPE_WEBIMAGE) {
				$filelist[$i]['IMAGE_URL'] = $webroot . 'image/' . $filelist[$i]['PNAME'];
			}
		}
	}

/*
	$fileRefs = $reftracker->getIncomingForFile( $filelist[$i]['OBJECTID'] );
	$fileRefCount = 0;
	for ($k = 0; $k < count($fileRefs); $k++) {
		$links = sCblockMgr()->getCblockLinkByEntrymaskLinkId($fileRefs[$k]["SRCOID"]);
		for ($c = 0; $c < count($links); $c++) {
			$lcb = sCblockMgr()->getCblock($links[$c]["CBLOCKID"], $links[$c]["CBLOCKVERSION"]);
			$lcoinfo = $lcb->get();
			$lcopubversion = $lcb->getPublishedVersion(true);
			if ( ($lcopubversion == $links[$c]["CBLOCKVERSION"]) &&
				 ($lcoinfo['DELETED'] == 0) ) {
				$fileRefCount++;
			}
		}
	}
	$filelist[$i]['REFS'] = $fileRefCount;
*/

	$user = new User($filelist[$i]['UID']);
	$userinfo = $user->get();
	$userinfo['PROPS'] = $user->properties->getValues( $filelist[$i]['UID'] );

	$filelist[$i]['USERNAME'] = trim($userinfo['PROPS']['FIRSTNAME'].' '.$userinfo['PROPS']['LASTNAME']);
}

if ($fileId) {
	$folder = new File($fileId);
	$moinfo = $folder->get();
	$writeperm = $folder->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $fileId, 'RWRITE');
	$subperm = $folder->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $fileId, 'RSUB');
	$name = $moinfo['NAME'];
	$description = $moinfo['DESCRIPTION'];
}

$koala->queueScript('if ($(\'wid_'.$wid_id.'_objcnt\')) $(\'wid_'.$wid_id.'_objcnt\').update(\''.count($filelist).'\');');

$koala->queueScript('Koala.windows[\'wid_'.$wid_id.'\'].setLocked( \'\' );');

$smarty->assign('filelist', $filelist);
$smarty->assign('name',$name);
$smarty->assign('description',$description);
$smarty->assign('view', $view);
$smarty->assign('action',$action);
$smarty->assign('fileId',$fileId);
$smarty->assign('site', $site );
$smarty->assign('refresh', $refresh );
$smarty->assign('initload', $initload );
$smarty->assign('displaymode', $displaymode );
$smarty->assign('tools', $tools );
$smarty->assign('win_no', $wid_id);
$smarty->assign('perm_write', $writeperm);
$smarty->assign('perm_sub', $subperm);

$smarty->display('file:'.$this->page_template);

?>