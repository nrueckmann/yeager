<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$data = explode('-',$ygid);
$fileID = $data[0];

$fileMgr = sFileMgr();
$file = sFileMgr()->getFile($fileID);
$latestVersion = $file->getLatestApprovedVersion();
$file = sFileMgr()->getFile($fileID, $latestVersion);

$fileInfo = $file->get();
$url = $file->getUrl();

$fileTypes = $fileMgr->getFiletypes();

$fileInfo['RDELETE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $fileID, 'RDELETE');
$fileInfo['RWRITE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $fileID, 'RWRITE');

if ($fileInfo['DELETED']==1) {
	$fileInfo['RDELETE'] = false;
	$fileInfo['RWRITE'] = false;
}

// Get current locks for this token (and unlock them)
$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
$lockedObjects = $fileMgr->getLocksByToken($lockToken);
foreach($lockedObjects as $lockedObject) {
	$currentObject = sFileMgr()->getFile($lockedObject['OBJECTID']);
	$currentObject->releaseLock($lockedObject['TOKEN']);
}

// Check for lock, and lock if not locked
$lockStatus = $file->getLock();
if ($lockStatus['LOCKED'] == 0) {
	$lockedFailed = !$file->acquireLock($lockToken);
} else {
	$lockedFailed = true;
}


$views = $file->views->getAssigned(true);
$viewInfo = $file->views->getGeneratedViewInfo($views[0]["ID"]);
$fileInfo['WIDTH'] = $viewInfo[0]["WIDTH"];
$fileInfo['HEIGHT'] = $viewInfo[0]["HEIGHT"];


foreach($fileTypes as $fileTypes_item) {
	if ($fileTypes_item['ID'] == $fileInfo['FILETYPE']) {
		$fileInfo['FILETYPE_TXT'] = $fileTypes_item['NAME'];
	}
}

$fileInfo['THUMB'] = 0;
$hiddenViews = $file->views->getHiddenViews();
foreach($hiddenViews as $view) {
	if ($view['IDENTIFIER'] == 'yg-preview') {
		$tmpviewinfo = $file->views->getGeneratedViewInfo($view["ID"]);
		if ($tmpviewinfo[0]["TYPE"] == FILE_TYPE_WEBIMAGE) {
			$fileInfo['THUMB'] = 1;
			$fileInfo['PREVIEWWIDTH'] = $tmpviewinfo[0]["WIDTH"];
			$fileInfo['PREVIEWHEIGHT'] = $tmpviewinfo[0]["HEIGHT"];
		}
	}
}

if ($lockedFailed) {
	// Get user who locked this object
	$userWithLock = new User( $lockStatus['LOCKUID'] );
	$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
	$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
	$smarty->assign('lockedByUser', $lockedByUser );
	$fileInfo['RDELETE'] = false;
	$fileInfo['RWRITE'] = false;
}

$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

$koala->queueScript('Koala.yg_setFileInfoLinks(\''.$this->request->parameters['win_no'].'\', \''.$fileInfo['OBJECTID'].'\', \''.$url.'\');');
$koala->queueScript('Koala.yg_initReUploadButton(\''.$this->request->parameters['win_no'].'\',\''.$fileInfo['NAME'].'\',\''.$fileInfo['FILETYPE'].'\', \''.$fileInfo['OBJECTID'].'\');');

$smarty->assign("refresh", $refresh );
$smarty->assign("file", $fileID );
$smarty->assign("fileinfo", $fileInfo );
$smarty->assign("filetypes", $fileTypes );
$smarty->assign("yg_id", $ygid );

$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>