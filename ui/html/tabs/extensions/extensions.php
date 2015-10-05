<?php

$selectiondialog = $this->request->parameters['selectiondialog'];
if (($selectiondialog) || ($selectiondialog == "1")) {
	$smarty->assign("displaymode", 'dialog');
}

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$data = explode('-', $ygid);
$objectID = $data[0];
$siteID = $data[1];

switch ($this->request->parameters['yg_type']) {
	case 'page':
		$objecttype = 'extpage';
		break;
	case 'cblock':
		$objecttype = 'extcblock';
		break;
	case 'file':
		$objecttype = 'extfile';
		break;
	case 'mailing':
		$objecttype = 'extmailing';
		break;
}

$extensionMgr = new ExtensionMgr();

switch($objecttype) {
	case 'extcblock':
		if ($objectID) {
			$cblock = sCblockMgr()->getCblock($objectID);
			$cblockInfo = $cblock->get();
			$cblockInfo['RWRITE'] = $cblock->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
			$cblockInfo['RSTAGE'] = $cblock->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

			if ($cblockInfo['DELETED']) {
				$cblockInfo['RWRITE'] = false;
				$cblockInfo['READONLY'] = true;
				$cblockInfo['RSTAGE'] = false;
			}

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = sCblockMgr()->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				if ($lockedObject['OBJECTID']) {
					$currentObject = sCblockMgr()->getCblock($lockedObject['OBJECTID']);
					$currentObject->releaseLock($lockedObject['TOKEN']);
				}
			}

			// Check for lock, and lock if not locked
			$lockStatus = $cblock->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$cblock->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}

			$all_cblock_extensions = $extensionMgr->getList( EXTENSION_CBLOCK, true );
			$used_extensions = array();
			$used_extensions_info = array();
			foreach($all_cblock_extensions as $all_cblock_extension) {
				$extension = $extensionMgr->getExtension($all_cblock_extension["CODE"]);
				if( $extension && $extension->usedByCblock($objectID, $cblockInfo['VERSION']) === true ) {
					$extension = $extensionMgr->getExtension($all_cblock_extension["CODE"], $objectID, $cblockInfo['VERSION']);
					if ($extension) {
						array_push($used_extensions_info, $all_cblock_extension);
						array_push($used_extensions, $extension);
					}
				}
			}

			$object_property_ids = array();
			foreach($used_extensions as $used_extension_idx => $used_extension) {
				// Call callback
				$used_extension->callExtensionHook('onRenderExtensionTab');
				$used_extensions_info[$used_extension_idx]['PROPERTIES'] = $used_extension->properties->getList();
			}

			$pageInfo = $cblockInfo;
		}
		break;

	case 'extfile':
		$fileMgr = sFileMgr();
		if ($objectID) {
			$file = $fileMgr->getFile($objectID);
			$fileInfo = $file->get();
			$fileInfo['RWRITE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
			$fileInfo['RSTAGE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

			if ($fileInfo['DELETED']) {
				$fileInfo['RWRITE'] = false;
				$fileInfo['READONLY'] = true;
				$fileInfo['RSTAGE'] = false;
			}

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $fileMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				if ($lockedObject['OBJECTID']) {
					$currentObject = $fileMgr->getFile($lockedObject['OBJECTID']);
					$currentObject->releaseLock($lockedObject['TOKEN']);
				}
			}

			// Check for lock, and lock if not locked
			$lockStatus = $file->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$file->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}

			$all_file_extensions = $extensionMgr->getList( EXTENSION_FILE, true );
			$used_extensions = array();
			$used_extensions_info = array();
			foreach($all_file_extensions as $all_file_extension) {
				$extension = $extensionMgr->getExtension($all_file_extension["CODE"]);
				if( $extension && $extension->usedByFile($objectID, $fileInfo['VERSION']) === true ) {
					$extension = $extensionMgr->getExtension($all_file_extension["CODE"], $objectID, $fileInfo['VERSION']);
					if ($extension) {
						array_push($used_extensions_info, $all_file_extension);
						array_push($used_extensions, $extension);
					}
				}
			}

			$object_property_ids = array();
			foreach($used_extensions as $used_extension_idx => $used_extension) {
				// Call callback
				$used_extension->callExtensionHook('onRenderExtensionTab');
				$used_extensions_info[$used_extension_idx]['PROPERTIES'] = $used_extension->properties->getList();
			}

			$pageInfo = $fileInfo;
		}
		break;

	case 'extmailing':
		$mailingMgr = new MailingMgr();
		if ($objectID) {
			$mailing = $mailingMgr->getMailing($objectID);
			$mailingInfo = $mailing->get();
			$mailingInfo['RWRITE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
			$mailingInfo['RSTAGE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

			if ($mailingInfo['DELETED']) {
				$mailingInfo['RWRITE'] = false;
				$mailingInfo['READONLY'] = true;
				$mailingInfo['RSTAGE'] = false;
			}

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $mailingMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				if ($lockedObject['OBJECTID']) {
					$currentObject = $mailingMgr->getMailing($lockedObject['OBJECTID']);
					$currentObject->releaseLock($lockedObject['TOKEN']);
				}
			}

			// Check for lock, and lock if not locked
			$lockStatus = $mailing->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$mailing->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}

			$all_mailing_extensions = $extensionMgr->getList( EXTENSION_MAILING, true );
			$used_extensions = array();
			$used_extensions_info = array();
			foreach($all_mailing_extensions as $all_mailing_extension) {
				$extension = $extensionMgr->getExtension($all_mailing_extension['CODE']);
				if( $extension && $extension->usedByMailing($objectID, $mailingInfo['VERSION']) === true ) {
					$extension = $extensionMgr->getExtension($all_mailing_extension['CODE'], $objectID, $mailingInfo['VERSION']);
					if ($extension) {
						array_push($used_extensions_info, $all_mailing_extension);
						array_push($used_extensions, $extension);
					}
				}
			}

			$object_property_ids = array();
			foreach($used_extensions as $used_extension_idx => $used_extension) {
				// Call callback
				$used_extension->callExtensionHook('onRenderExtensionTab');
				$used_extensions_info[$used_extension_idx]['PROPERTIES'] = $used_extension->properties->getList();
			}

			$pageInfo = $mailingInfo;
		}
		break;

	case 'extpage':
	default:
		$pageMgr = new PageMgr($siteID);
		if ($objectID) {
			$page = $pageMgr->getPage($objectID);
			$pageInfo = $page->get();
			$pageInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
			$pageInfo['RSTAGE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

			if ($pageInfo['DELETED']) {
				$pageInfo['RWRITE'] = false;
				$pageInfo['READONLY'] = true;
				$pageInfo['RSTAGE'] = false;
			}

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $pageMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				if ($lockedObject['OBJECTID']) {
					$currentObject = $pageMgr->getPage($lockedObject['OBJECTID']);
					$currentObject->releaseLock($lockedObject['TOKEN']);
				}
			}

			// Check for lock, and lock if not locked
			$lockStatus = $page->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$page->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}

			$all_page_extensions = $extensionMgr->getList( EXTENSION_PAGE, true );
			$used_extensions = array();
			$used_extensions_info = array();
			foreach($all_page_extensions as $all_page_extension) {
				$extension = $extensionMgr->getExtension($all_page_extension["CODE"]);
				if( $extension && $extension->usedByPage($objectID, $pageInfo['VERSION'], $siteID) === true ) {
					$extension = $extensionMgr->getExtension($all_page_extension["CODE"], $objectID, $pageInfo['VERSION'], $siteID);
					if ($extension) {
						array_push($used_extensions_info, $all_page_extension);
						array_push($used_extensions, $extension);
					}
				}
			}

			$object_property_ids = array();
			foreach($used_extensions as $used_extension_idx => $used_extension) {
				// Call callback
				$used_extension->callExtensionHook('onRenderExtensionTab');
				$used_extensions_info[$used_extension_idx]['PROPERTIES'] = $used_extension->properties->getList();
			}
		}
		break;
}

$new_list = array();
$contentareas = array();
$empty_contentarea = array(
	'ID' => '0',		// FAKE CONTENTAREAID
	'TEMPLATE' => '0',	// FAKE TEMPLATEID
	'CODE' => ($itext['TXT_EXTENSIONS']!='')?($itext['TXT_EXTENSIONS']):('$TXT_EXTENSIONS'),
	'NAME' => ($itext['TXT_EXTENSIONS']!='')?(strtolower($itext['TXT_EXTENSIONS'])):(strtolower('$TXT_EXTENSIONS')),
	'LIST' => array()
);
foreach($used_extensions_info as $used_extensions_info_item) {
	$new_contentarea = $empty_contentarea;
	$new_contentarea['LIST'] = array(
		0 => array(
			'ID' => $objectID,											// PAGE_ID
			'LINKID' => 'extension-'.$used_extensions_info_item['ID']	// EXTENSION_ID
		)
	);
	array_push( $contentareas, $new_contentarea );
}
if (count($contentareas) == 0) {
	array_push( $contentareas, $empty_contentarea );
}

if ($lockedFailed) {
	// Get user who locked this object
	$userWithLock = new User( $lockStatus['LOCKUID'] );
	$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
	$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
	$smarty->assign('lockedByUser', $lockedByUser );
	$pageInfo['RWRITE'] = false;
	if (($objecttype == 'extcblock') || ($objecttype == 'extpage')) {
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
	}
} else {
	if (($objecttype == 'extcblock') || ($objecttype == 'extpage')) {
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$pageInfo['RSTAGE'].'\' );');
	}
}

$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

$smarty->assign("objecttype", $objecttype);
$smarty->assign("site", $siteID);
$smarty->assign("pageInfo", $pageInfo);
$smarty->assign("refresh", $refresh );
$smarty->assign("page", $objectID);
$smarty->assign("contentareas", $contentareas);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>