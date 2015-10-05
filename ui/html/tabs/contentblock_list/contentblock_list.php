<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$initload = $this->request->parameters['initload'];
$action = $this->request->parameters['action'];
$wid = $this->request->parameters['wid'];
$coId = $this->request->parameters['coId'];

$listViewExtensionId = $this->request->parameters['listViewExtensionId'];
$wid_id = explode('_',$wid);
$wid_id = $wid_id[1];
$coFolderID = explode ('-',$ygid);
$coFolderID = $coFolderID[0];
$sortcol = $this->request->parameters['pagedir_orderby'];
$sortorder = $this->request->parameters['pagedir_orderdir'];
if ($sortcol == NULL) {
	$sortcol = 0;
}
if ($sortorder == NULL) {
	$sortorder = 1;
}

// Get all listview extensions
$extensionMgr = new ExtensionMgr();
$listviewExtensions = $extensionMgr->getList( EXTENSION_CBLOCKLISTVIEW, true );

// Check if a special listview was chosen
if ($listViewExtensionId == '') {
	$listViewExtensionId = 0;
}
if ($listViewExtensionId == 0) {
	// Find default extension
	foreach($listviewExtensions as $listviewExtension) {
		if ($listviewExtension['CODE'] == 'defaultCblockListView') {
			$listViewExtensionId = $listviewExtension['ID'];
			$isDefaultExtension = true;
		}
	}
}

if ($listViewExtensionId) {
	// Get entries for special from chosen extension
	$objectInfo = $extensionMgr->get($listViewExtensionId);
	if ($objectInfo["CODE"]) {
		$extension = $extensionMgr->getExtension($objectInfo["CODE"]);
		if ($extension && $objectInfo["INSTALLED"]) {
			$extensionProperties = $extension->properties;
			$listColumns = $extension->getListColumns();

			if (strtoupper($this->page) == 'CONTENTBLOCK_LISTITEM') {
				$filterArray = array(
					array('CBID' => $coId)
				);
			} else {
				// Get additional information about contentblock
				$cb = sCblockMgr()->getCblock($coFolderID);
				$coInfo = $cb->get();
				$coListCount = $extension->getCblockListCount( $coFolderID, "SUBNODES", $coInfo['LEVEL']+1, 0, array() );

				// for paging
				$pageDirInfo = calcPageDir($coListCount, '0', 'ASC');
				//$pageDirOrderBy = $pageDirInfo['pageDirOrderBy'];
				//$pageDirOrderDir = $pageDirInfo['pageDirOrderDir'];
				$pageDirOrderBy = $sortcol;
				$pageDirOrderDir = $sortorder;
				$pageDirLimit = explode(',', $pageDirInfo['pageDirLimit']);
				$pageDirLimitFrom = $pageDirLimit[0];
				$pageDirLimitLength = $pageDirLimit[1];
				// END for paging

				$filterArray = array();
				$filterArray[] = array(
					'TYPE' 		=> 'LIMITER',
					'VALUE'		=>	$pageDirLimitFrom,
					'VALUE2'	=>	$pageDirLimitLength
				);
				$filterArray[] = array(
					'TYPE' 		=> 'ORDER',
					'VALUE'		=>	$pageDirOrderBy,
					'VALUE2'	=>	$pageDirOrderDir
				);
			}

			$coList = $extension->getCblockList( $coFolderID, $coInfo['LEVEL']+1, 0, $filterArray );
		}
	}
}

// Remove Default coListView from array
$realListviewExtensions = array();
foreach($listviewExtensions as $listviewExtension) {
	if ($listviewExtension['CODE'] != 'defaultCblockListView') {
		array_push($realListviewExtensions, $listviewExtension);
	}
}
$listviewExtensions = $realListviewExtensions;

$koala->queueScript('if ($(\'wid_'.$wid_id.'_objcnt\')) $(\'wid_'.$wid_id.'_objcnt\').update(\''.$coListCount.'\');');

if ($isDefaultExtension) {
	$listViewExtensionId = 0;
}

$smarty->assign('coId', $coId);
$smarty->assign('coList', $coList);
$smarty->assign('coListCount', $coListCount);
$smarty->assign('listviewExtensions', $listviewExtensions);
$smarty->assign('listViewExtensionId', $listViewExtensionId);
$smarty->assign('action', $action);
$smarty->assign('coFolderID', $coFolderID);
$smarty->assign('listColumns', $listColumns["COLUMNS"]);
$smarty->assign('listColumnsEncoded', json_encode($listColumns["COLUMNS"]));
$smarty->assign('refresh', $refresh );
$smarty->assign('initload', $initload );
$smarty->assign('win_no', $wid_id);
$smarty->display('file:'.$this->page_template);

?>