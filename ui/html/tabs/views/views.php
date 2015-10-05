<?php

$ygid = $this->request->parameters['yg_id'];
$objecttype = $this->request->parameters['yg_type'];
$refresh = $this->request->parameters['refresh'];
$data = explode('-',$ygid );
$object = $data[0];

$fileMgr = sFileMgr();
$file = new File($object);
$latestVersion = $file->getLatestApprovedVersion();
$file = new File($object, $latestVersion);
$objectInfo = $file->get();

$object_permissions = array();
$object_permissions['RWRITE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RWRITE");
$object_permissions['READONLY'] = !$file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RWRITE");

if ($objectInfo['DELETED']==1) {
	$object_permissions['RWRITE'] = false;
	$object_permissions['READONLY'] = true;
}

// Get current locks for this token (and unlock them)
$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
$lockedObjects = $fileMgr->getLocksByToken($lockToken);
foreach($lockedObjects as $lockedObject) {
	$currentObject = new File($lockedObject['OBJECTID']);
	$currentObject->releaseLock($lockedObject['TOKEN']);
}
// Check for lock, and lock if not locked
$lockStatus = $file->getLock();
if ($lockStatus['LOCKED'] == 0) {
	$lockedFailed = !$file->acquireLock($lockToken);
} else {
	$lockedFailed = true;
}

$views = $file->views->getAssigned();

for ($i = 0; $i < count($views); $i++) {
	$viewinfo = $file->views->getGeneratedViewInfo($views[$i]["ID"]);
	$views[$i]["VIEWTYPE"] = $viewinfo[0]["TYPE"];
	if ($views[$i]["IDENTIFIER"] == "YGSOURCE") {
		$views[$i]['WIDTH'] = $viewinfo[0]["WIDTH"];
		$views[$i]['HEIGHT'] = $viewinfo[0]["HEIGHT"];
	}

}

if ($lockedFailed) {
	// Get user who locked this object
	$userWithLock = new User( $lockStatus['LOCKUID'] );
	$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
	$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
	$smarty->assign('lockedByUser', $lockedByUser );
	$object_permissions['RWRITE'] = false;
	$object_permissions['READONLY'] = true;
}

$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

$smarty->assign("page_id", $object);
$smarty->assign("site_id", 'file');

$smarty->assign("refresh", $refresh );
$smarty->assign("object", $object);
$smarty->assign("objecttype", $objecttype);
$smarty->assign("views", $views);
$smarty->assign("object_permissions", $object_permissions);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>