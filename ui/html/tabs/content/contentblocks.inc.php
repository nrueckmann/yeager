<?php

$siteID = $this->request->parameters['site'];
$pageID = $this->request->parameters['page'];
$objectType = $this->request->parameters['objecttype'];
$data = json_decode( $this->request->parameters['data'], true );

$userinfo = $user->get();
$userinfo['PROPS'] = $user->properties->getValues( sUserMgr()->getCurrentUserID() );

// Is Page/Contentblock
$pageMgr = new PageMgr($siteID);

if ( (!$siteID && !$pageID) && ($this->request->parameters['yg_id'] && $this->request->parameters['yg_type']) ) {
	$siteID = $pageID = 'dummy';
	$yg_id = explode('-', $this->request->parameters['yg_id']);
	$this->request->parameters['co'] = $yg_id[0];
}
if ( (!$siteID && !$pageID) && ($this->request->parameters['co']) ) {
	$siteID = $pageID = 'cblock';
}

if (substr($objectType, 0, 3) == 'ext') {
	switch ($objectType) {
		case 'extcblock':
			$page = sCblockMgr()->getCblock($pageID);
			$pageInfo = $page->get();
			break;

		case 'extfile':
			$fileMgr = sFileMgr();
			$page = $fileMgr->getFile($pageID);
			$latestFinalVersion = $page->getLatestApprovedVersion();
			$page = $fileMgr->getFile($pageID, $latestFinalVersion);
			$pageInfo = $page->get();
			break;

		case 'extmailing':
			$mailingMgr = new MailingMgr();
			if ($pageID) {
				$page = $mailingMgr->getMailing($pageID);
				$pageInfo = $page->get();
			}
			break;

		case 'extpage':
		default:
			$page = $pageMgr->getPage($pageID);
			$pageInfo = $page->get();
			break;
	}

	$pageInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RWRITE");
	$smarty->assign('extension_editmode', true);
	if ($pageInfo['DELETED']) {
		$pageInfo['RWRITE'] = false;
		$pageInfo['READONLY'] = true;
		$pageInfo['RSTAGE'] = false;
		$smarty->assign('extension_editmode', false);
	}

} elseif ($siteID == 'cblock') {
	$tmpId = $this->request->parameters['co'];
	if (!$tmpId) {
		$tmpId = $pageID;
	}
	if ($tmpId) {
		$pcb = sCblockMgr()->getCblock($tmpId);
		$pageInfo = $pcb->get();
		$pageInfo['RWRITE'] = $pcb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $tmpId, "RWRITE");
		$smarty->assign('cblock_editmode', true);
		if ($pageInfo['DELETED']==1) {
			$pageInfo['RWRITE'] = false;
			$pageInfo['RSTAGE'] = false;
			$smarty->assign('cblock_editmode', false);
		}
	}
} elseif ($siteID == 'mailing') {
	// Is emailing
	$mailingMgr = new MailingMgr();
	if ($pageID) {
		$page = $mailingMgr->getMailing($pageID);
		$pageInfo = $page->get();
		$pageInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RWRITE");
		$smarty->assign('extension_editmode', true);
		if ($pageInfo['DELETED']) {
			$pageInfo['RWRITE'] = false;
			$pageInfo['READONLY'] = true;
			$pageInfo['RSTAGE'] = false;
			$smarty->assign('extension_editmode', false);
		}
	}
} elseif ( ($siteID!='dummy') && ($pageID!='dummy') && ($pageID) && ($pageID != 'cblock_copy')) {
	$page = $pageMgr->getPage($pageID);
	$pageInfo = $page->get();
	$pageInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RWRITE");
	if ($pageInfo['DELETED']==1) {
		$pageInfo['RWRITE'] = false;
		$pageInfo['RSTAGE'] = false;
	}
}

$selectiondialog = $this->request->parameters['selectiondialog'];
if (($selectiondialog!=null) || ($selectiondialog == "1")) {
	$smarty->assign("displaymode", 'dialog');
}
if ($this->request->parameters['displaymode']!='') {
	$smarty->assign("displaymode", $this->request->parameters['displaymode']);
}

$smarty->assign('extensiondir', $this->extensiondir);

if ( ($siteID=='dummy') && ($pageID=='dummy') ) {

	// Entrymask Preview Mode
	$entrymaskMgr = new Entrymasks();
	$co_id = $this->request->parameters['co'];
	$co_info = $entrymaskMgr->get($co_id);

	if (!$co_info['FOLDER']) {
		$cos = array();
		$cos[0]['CONTENTAREA'] = 'dummy';
		$cos[0]['OBJECTID'] = 'dummy'.$co_id;
		$cos[0]['EMBEDDED'] = 1;
		$cos[0]['ENTRYMASKS'][0]['FORMFIELDS'] = $entrymaskMgr->getEntrymaskFormfields($co_id);
		$cos[0]['ENTRYMASKS'][0]['ENTRYMASKNAME'] = $co_info['NAME'];
		foreach ($cos[0]['ENTRYMASKS'][0]['FORMFIELDS'] as $formfield_idx => $formfield) {
			if ($formfield['FORMFIELD'] == 9) {
				$cos[0]['ENTRYMASKS'][0]['FORMFIELDS'][$formfield_idx]['LVALUES'] = $entrymaskMgr->getListValuesByLinkID(  $formfield['ID'] );
			}
		}
	}
	$output = $cos;
	$smarty->assign('dummymode', true);

} elseif ( ($siteID=='cblock') && ($pageID=='cblock') ) {

	// Contentblock Preview Mode
	$co_id = $this->request->parameters['co'];

	if ($co_id) {
		$cb = sCblockMgr()->getCblock($co_id);
		$co = $cb->get();

		if (!$co['FOLDER']) {
			$co_site = $this->request->parameters['co_site'];
			$co_page = $this->request->parameters['co_page'];

			$cos = array();
			$cos[0] = $co;
			$cos[0]['CONTENTAREA'] = 'cblock';
			if ($cos[0]['OBJECTID']) {
				$cb = sCblockMgr()->getCblock($cos[0]['OBJECTID']);
				$cos[0]['ENTRYMASKS'] = $cb->getEntrymasks();

				for ($c = 0; $c < count($cos[0]['ENTRYMASKS']); $c++) {

					$controlFormfields = $cb->getFormfieldsInternal($cos[0]['ENTRYMASKS'][$c]['LINKID']);

					for ($w = 0; $w < count($controlFormfields); $w++) {
						if (($controlFormfields[$w]['FORMFIELD'] == 6) || ($controlFormfields[$w]['FORMFIELD'] == 16)) {
							if ($controlFormfields[$w]['VALUE01']) {
								$file = sFileMgr()->getFile($controlFormfields[$w]['VALUE01']);
								if ($file) {
									$fileInfo = $file->get();
									$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
								}
							}
						}

						if ($controlFormfields[$w]['FORMFIELD'] == 7) {
							if ($controlFormfields[$w]['VALUE01']) {
								$lcb = sCblockMgr()->getCblock($controlFormfields[$w]['VALUE01']);
								if ($lcb) {
									$info = $lcb->get();
									$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
								}
							}
						}
						if ($controlFormfields[$w]['FORMFIELD'] == 8) {
							$pageMgr = new PageMgr($co_site);
							$info = $pageMgr->tags->get($controlFormfields[$w]['VALUE01']);
							$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
						}
						if (($controlFormfields[$w]['FORMFIELD'] == 11) || (($controlFormfields[$w]['FORMFIELD'] == 12))) {
							$controlFormfields[$w]['VALUE01'] = TStoLocalTS($controlFormfields[$w]['VALUE01']);
						}
					}
					$cos[0]['ENTRYMASKS'][$c]['FORMFIELDS'] = $controlFormfields;
				}
			}
		}
		$output = $cos;
	}

} elseif ( ($siteID=='cblock_copy') && ($pageID=='cblock_copy') ) {

	// Contentblock Copy Mode
	$co_id = $this->request->parameters['co'];
	if ($co_id) {
		$cb = sCblockMgr()->getCblock($co_id);
		$co = $cb->get();

		$co_site = $this->request->parameters['co_site'];
		$co_page = $this->request->parameters['co_page'];

		$cos = array();
		$cos[0] = $co;
		$cos[0]['CONTENTAREA'] = 'cblock_copy';
		if ($cos[0]['OBJECTID']) {
			$cb = sCblockMgr()->getCblock($cos[0]['OBJECTID']);
			$cos[0]['ENTRYMASKS'] = $cb->getEntrymasks();

			for ($c = 0; $c < count($cos[0]['ENTRYMASKS']); $c++) {
				$controlFormfields = $cb->getFormfieldsInternal($cos[0]['ENTRYMASKS'][$c]['LINKID']);
				for ($w = 0; $w < count($controlFormfields); $w++) {
					if (($controlFormfields[$w]['FORMFIELD'] == 6) || ($controlFormfields[$w]['FORMFIELD'] == 16)) {
						if ($controlFormfields[$w]['VALUE01']) {
							if ($controlFormfields[$w]['VALUE01']) {
								$file = sFileMgr()->getFile($controlFormfields[$w]['VALUE01']);
								$fileInfo = $file->get();
								$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
							}
						}
					}
					if ($controlFormfields[$w]['FORMFIELD'] == 7) {
						if ($controlFormfields[$w]['VALUE01']) {
							$lcb = sCblockMgr()->getCblock($controlFormfields[$w]['VALUE01']);
							$info = $lcb->get();
							$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
						}
					}
					if ($controlFormfields[$w]['FORMFIELD'] == 8) {
						$pageMgr = new PageMgr($co_site);
						$info = $pageMgr->tags->get($controlFormfields[$w]['VALUE01']);
						$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
					}
				}
				$cos[0]['ENTRYMASKS'][$c]['FORMFIELDS'] = $controlFormfields;
			}
			$cos[0]['LINKID'] = $cos[0]['ENTRYMASKS'][0]['LINKID'];
			$output = $cos;
		}
	}

} elseif (substr($objectType, 0, 3) == 'ext') {

	// extension mode
	$extensionMgr = new ExtensionMgr();
	$filetypeMgr = new Filetypes();

	$loop = 0;
	foreach ($data as $item) {
		$coid = $item[0];
		$contentarea = $item[1];
		$contentarea_id = $item[2];
		$colnkid = $item[3];

		$extension_id = explode( '-', $item[3] );
		$extension_id = $extension_id[1];

		$ex = $extensioninfo = $extensionMgr->get( $extension_id );

		$extension = $extensionMgr->getExtension($ex["CODE"], $pageID, $pageInfo['VERSION'], $siteID);
		if ($extension) {
			$props = $extension->properties->getList('LISTORDER');
			foreach($props as $prop_idx => $prop) {
				$props[$prop_idx]['VALUE'] = $extension->properties->getValueInternal($prop['IDENTIFIER']);

				if ($props[$prop_idx]['TYPE']=='LIST') {
					$props[$prop_idx]['LIST_VALUES'] = $extension->propertySettings->getListValues( $prop['IDENTIFIER'] );
				}
				if ($props[$prop_idx]['TYPE']=='FILE') {
					if (strlen(trim($props[$prop_idx]['VALUE']))>0) {
						$file = sFileMgr()->getFile($props[$prop_idx]['VALUE']);
						if ($file) {
							$fileInfo = $file->get();
							$props[$prop_idx]['COLOR'] = $fileInfo['COLOR'];
							$props[$prop_idx]['TYPECODE'] = $fileInfo['CODE'];
							$props[$prop_idx]['DISPLAYNAME'] = $fileInfo['NAME'];
						}
					}
				}
				if ($props[$prop_idx]['TYPE']=='FILEFOLDER') {
					if (strlen(trim($props[$prop_idx]['VALUE']))>0) {
						$file = sFileMgr()->getFile($props[$prop_idx]['VALUE']);
						if ($file) {
							$fileInfo = $file->get();
							$props[$prop_idx]['DISPLAYNAME'] = $fileInfo['NAME'];
						}
					}
				}
				if ($props[$prop_idx]['TYPE']=='DATETIME') {
					if (strlen(trim($props[$prop_idx]['VALUE']))>0) {
						$props[$prop_idx]['VALUE'] = TStoLocalTS($props[$prop_idx]['VALUE']);
					}
				}
			}
			$ex['OBJECTID'] = $extension_id;
			$ex['CO_CONTENTAREA'] = $contentarea_id;
			$ex['LINKID'] = $colnkid;
			$ex['EMBEDDED'] = 1;
			if ($extension->info['ASSIGNMENT'] == EXTENSION_ASSIGNMENT_EXT_CONTROLLED) {
				$ex['NODELETE'] = true;
			} else {
				$ex['NODELETE'] = false;
			}
			$ex['ENTRYMASKS'] = array();
			$ex['ENTRYMASKS'][0]['ENTRYMASKNAME'] = $ex['NAME'];
			$ex['ENTRYMASKS'][0]['EXTENSIONINFO'] = $extensioninfo;
			$ex['ENTRYMASKS'][0]['LINKID'] = $extensioninfo['ID'];

			// Remap control from properties to formfields
			$tempformfields = array();
			for ($c = 0; $c < count($props); $c++) {
				if ($props[$c]['VISIBLE']) {
					/*
					 LINKID
					ENTRYMASKID
					CODE
					ENTRYMASKNAME
					ID
					FORMFIELD
					ENTRYMASKFORMFIELD
					LNK
					VALUE01
					...
					VALUE021
					LINKID
					NAME
					PRESETTING01
					...
					PRESETTING08
					IDENTIFIER
					ALIAS02
					...
					ALIAS06

					ENTRYMASK_NAME,
					IDENTIFIER,
					TYPE,
					c.*,
					NAME,
					CBID,
					PRESET,
					WIDTH,
					MAXLENGTH,
					CONFIG,
					CUSTOM

					LVALUES = NULL
					DISPLAYNAME = NULL
					*/

					$tempformfield['LINKID'] = '0000';
					$tempformfield['ENTRYMASKID'] = '0000';
					$tempformfield['CODE'] = '0000';
					$tempformfield['LNK'] = '0000';
					$tempformfield['LINKID'] = '0000';
					$tempformfield['ID'] = $props[$c]['ID'].$extensioninfo['ID'];
					$tempformfield['NAME'] = $props[$c]['NAME'];
					$tempformfield['ENTRYMASKNAME'] = $props[$c]['NAME'];
					$tempformfield['PRESET'] = $props[$c]['NAME'];
					$tempformfield['VALUE'] = $props[$c]['VALUE'];
					$tempformfield['IDENTIFIER'] = 'ALIAS01';
					$tempformfield['LIST_VALUES'] = NULL;
					$tempformfield['DISPLAYNAME'] = '';

					switch ($props[$c]['TYPE']) {
						case 'TEXT':
							$tempformfield['FORMFIELD'] = 1;
							$tempformfield['VALUE'] = replaceSpecialURLs($props[$c]['VALUE']);
							break;
						case 'TEXTAREA':
							$tempformfield['FORMFIELD'] = 2;
							$tempformfield['VALUE'] = replaceSpecialURLs($props[$c]['VALUE']);
							break;
						case 'RICHTEXT':
							$tempformfield['FORMFIELD'] = 3;
							$tempformfield['VALUE'] = replaceSpecialURLs($props[$c]['VALUE']);
							break;
						case 'CHECKBOX':
							$tempformfield['FORMFIELD'] = 4;
							break;
						case 'LINK':
							$tempformfield['FORMFIELD'] = 5;
							$special_url = resolveSpecialURL($props[$c]['VALUE']);
							if ($special_url !== false) {
								$special_url_info = getSpecialURLInfo($props[$c]['VALUE']);
								if ( ($special_url_info['TYPE'] == 'IMG') || ($special_url_info['TYPE'] == 'DOWN') ) {
									$tempformfield['IS_FILE'] = true;
									if ($special_url_info['ID']) {
										$file = sFileMgr()->getFile($special_url_info['ID']);
										$link_fileinfo = $file->get();
										$tempformfield['LINKTITLE'] = $link_fileinfo['NAME'];
										$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
										$tempformfield['IDENTIFIER'] = $link_filetype['IDENTIFIER'];
										$tempformfield['TYPECODE'] = $link_filetype['TYPECODE'];
										$tempformfield['COLOR'] = $link_filetype['COLOR'];
										$tempformfield['FILE_ID'] = $special_url_info['ID'];
									}
								} else {
									$pageMgr = new PageMgr($special_url_info['SITE']);
									$page = $pageMgr->getPage($special_url_info['ID']);
									$link_pageInfo = $page->get();
									if ($siteID != 'cblock') {
									}
									$tempformfield['LINKTITLE'] = $link_pageInfo['NAME'];
									$tempformfield['IS_INTERNAL'] = true;
								}
							} else if (preg_match_all($this->URLRegEx1, $props[$c]['VALUE'], $internal) > 0) {
								$tempformfield['VALUE'] = $props[$c]['VALUE'];
								if ($internal[2][0]=='download') {
									$tempformfield['IS_FILE'] = true;
									$link_file = str_replace('/','',$internal[3][0]);
									if ($link_file) {
										$file = sFileMgr()->getFile($link_file);
										$link_fileinfo = $file->get();
										$tempformfield['LINKTITLE'] = $link_fileinfo['NAME'];
										$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
										$tempformfield['IDENTIFIER'] = $link_filetype['IDENTIFIER'];
										$tempformfield['TYPECODE'] = $link_filetype['CODE'];
										$tempformfield['COLOR'] = $link_filetype['COLOR'];
										$tempformfield['FILE_ID'] = $link_file;
									}
								} else {
									$link_site = $internal[3][0];
									$link_page = str_replace('/','',$internal[5][0]);
									$pageMgr = new PageMgr($link_site);
									$page = $pageMgr->getPage($link_page);
									$link_pageInfo = $page->get();
									if ($siteID != 'cblock') {
									}
									$tempformfield['LINKTITLE'] = $link_pageInfo['NAME'];
									$tempformfield['IS_INTERNAL'] = true;
								}
							} elseif (substr($props[$c]['VALUE'], 0, 7)=='mailto:') {
								$tempformfield['IS_EMAIL'] = true;
								$tempformfield['LINKTITLE'] = substr($props[$c]['VALUE'], 7);
							} else {
								$linkInfo = checkLinkInternalExternal( $props[$c]['VALUE'] );
								switch($linkInfo['TYPE']) {
									case 'external':
										$tempformfield['LINKTITLE'] = $props[$c]['VALUE'];
										break;
									case 'internal':
										$tempformfield['LINKTITLE'] = $linkInfo['NAME'];
										$tempformfield['IS_INTERNAL'] = true;
										break;
									case 'file':
										$tempformfield['IS_FILE'] = true;
										$tempformfield['LINKTITLE'] = $linkInfo['NAME'];
										$tempformfield['IDENTIFIER'] = $linkInfo['INFO']['IDENTIFIER'];
										$tempformfield['TYPECODE'] =  $linkInfo['INFO']['CODE'];
										$tempformfield['COLOR'] =  $linkInfo['INFO']['COLOR'];
										$tempformfield['FILE_ID'] =  $linkInfo['INFO']['FILE_ID'];
										break;
								}
							}
							$tempformfield['DISPLAYNAME'] = $tempformfield['LINKTITLE'];
							break;
						case 'FILE':
							$tempformfield['FORMFIELD'] = 6;
							$tempformfield['COLOR'] = $props[$c]['COLOR'];
							$tempformfield['TYPECODE'] = $props[$c]['TYPECODE'];
							$tempformfield['DISPLAYNAME'] = $props[$c]['DISPLAYNAME'];
							break;
						case 'FILEFOLDER':
							$tempformfield['FORMFIELD'] = 16;
							$tempformfield['DISPLAYNAME'] = $props[$c]['DISPLAYNAME'];
							break;
						case 'CBLOCK':
							$tempformfield['FORMFIELD'] = 7;
							if ($props[$c]['VALUE']) {
								$tcb = sCblockMgr()->getCblock($props[$c]['VALUE']);
								$info = $tcb->get();
								$tempformfield['DISPLAYNAME'] = $info['NAME'];
							}
							break;
						case 'TAG':
							$tempformfield['FORMFIELD'] = 8;
							$info = $pageMgr->tags->get($props[$c]['VALUE']);
							$tempformfield['DISPLAYNAME'] = $info['NAME'];
							break;
						case 'LIST':
							$tempformfield['FORMFIELD'] = 9;
							$tempformfield['LIST_VALUES'] = $props[$c]['LIST_VALUES'];
							break;
						case 'PASSWORD':
							$tempformfield['FORMFIELD'] = 10;
							break;
						case 'DATE':
							$tempformfield['FORMFIELD'] = 11;
							break;
						case 'DATETIME':
							$tempformfield['FORMFIELD'] = 12;
							break;
						case 'HEADLINE':
							$tempformfield['FORMFIELD'] = 13;
							break;
						case 'CUTLINE':
							$tempformfield['FORMFIELD'] = 14;
							break;
						case 'PAGE':
							$tempformfield['FORMFIELD'] = 15;
							if ($props[$c]['VALUE']['site'] && $props[$c]['VALUE']['page']) {
								$tmpPageMgr = new PageMgr($props[$c]['VALUE']['site']);
								$tmpPage = $tmpPageMgr->getPage($props[$c]['VALUE']['page']);
								$info = $tmpPage->get();
								$info['RWRITE'] = $tmpPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $props[$c]['VALUE']['page'], "RWRITE");
								$info['RDELETE'] = $tmpPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $props[$c]['VALUE']['page'], "RDELETE");
								$iconData = getIconForPage($info);
								$tempformfield['ICON'] = $iconData['iconclass'];
								$tempformfield['STYLE'] = $iconData['style'];
								$tempformfield['DISPLAYNAME'] = $info['NAME'];
							}
							break;
					}
					$tempformfield['ENTRYMASKFORMFIELD'] = $tempformfield['FORMFIELD'];
					array_push( $tempformfields, $tempformfield );
				}
			}
			$ex['ENTRYMASKS'][0]['FORMFIELDS'] = $tempformfields;

			$output[] = $ex;
			$loop++;
		}

	}

} else {

	$templateMgr = new Templates();

	// Normal Mode
	$contentareasEntryMasks = $templateMgr->resolveContentareaEntrymaskMapping( $pageInfo['TEMPLATEID'] );

	$loop = 0;
	foreach ($data as $item) {
		$coid = $item[0];
		$contentarea = $item[1];
		$contentarea_id = $item[2];
		$colnkid = $item[3];

		if ($coid) {
			$cb = sCblockMgr()->getCblock($coid);

			if ($siteID == 'cblock') {
				$co = $cb->get();
				$co['ENTRYMASKS'] = $cb->getEntrymasks();
			} else {
				$contentareas = $templateMgr->getContentareas( $pageInfo['TEMPLATEID'] );
				for ($i = 0; $i < count($contentareas); $i++) {
					if ( $contentareas[$i]['ID']==$contentarea_id) {
						if ($siteID == 'mailing') {
							// For mailings
							$page = $mailingMgr->getMailing($pageID);
						} else {
							// For pages
							$page = $pageMgr->getPage($pageID);
						}
						$colist = $page->getCblockList($contentareas[$i]['CODE'], true);
						for ($j = 0; $j < count($colist); $j++) {
							if ($colist[$j]['OBJECTID'] == $coid) {
								$new_co = $colist[$j];
								if ($new_co['OBJECTID']) {
									$newcb = sCblockMgr()->getCblock($colist[$j]['OBJECTID']);
									if ($newcb) {
										$publishedCoVersion = $newcb->getPublishedVersion(true);
										// Get last published version when version=ALWAYS_LATEST_APPROVED_VERSION (and only on contentblocks, not entrymasks)
										if ($colist[$j]['EMBEDDED'] == 1) {
											$latestFinalVersion = $colist[$j]['VERSION'];
										} else {
											$latestFinalVersion = $publishedCoVersion;
										}
										if ($colist[$j]['EMBEDDED'] == 0) {
											$newcb = sCblockMgr()->getCblock($colist[$j]['OBJECTID'], $latestFinalVersion);
											$tmpInfo = $newcb->get();
											$new_co['NAME'] = $tmpInfo['NAME'];
										}
										$new_co['VERSION'] = $latestFinalVersion;
										$new_co['ENTRYMASKS'] = $newcb->getEntrymasks($latestFinalVersion);
									} else {
										// No rights
										$new_co = NULL;
									}
								}
							}
						}

					}
				}
				$co = $new_co;
			}
			$co['CO_CONTENTAREA'] = $contentarea_id;
			$co['LINKID'] = $colnkid;

			if ($siteID == 'cblock') {
				$co['ENTRYMASKS'] = array( $co['ENTRYMASKS'][$loop] );
				$co['EMBEDDED'] = 1;
			}
			$childNotAllowed = false;
			for ($c = 0; $c < count($co['ENTRYMASKS']); $c++) {
				$co['ENTRYMASKS'][$c]['ALLOWED'] = false;
				foreach($contentareasEntryMasks as $contentareasEntryMask_item) {
					if ($contentareasEntryMask_item['CODE'] == $contentarea) {
						foreach($contentareasEntryMask_item['ENTRYMASKS'] as $entrymask_title_item) {
							if ($co['ENTRYMASKS'][$c]['CODE'] == $entrymask_title_item) {
								$co['ENTRYMASKS'][$c]['ALLOWED'] = true;
							}
						}
					}
				}
				if (!$co['ENTRYMASKS'][$c]['ALLOWED']) {
					$childNotAllowed = true;
				}

				// Get last published version when version=ALWAYS_LATEST_APPROVED_VERSION (and only on contentblocks, not entrymasks)
				if (($co['EMBEDDED'] != 1) && ($co['VERSION'] == ALWAYS_LATEST_APPROVED_VERSION)) {
					$co['VERSION'] = $cb->getLatestApprovedVersion();
				}

				$cb = sCblockMgr()->getCblock($coid, $co['VERSION']);
				if ($cb) {
					$controlFormfields = $cb->getFormfieldsInternal($co['ENTRYMASKS'][$c]['LINKID']);
					getAdditionalFormfieldData($controlFormfields);

					$co['ENTRYMASKS'][$c]['FORMFIELDS'] = $controlFormfields;
				}
			}

			$co['ALLOWED'] = true;
			if ($childNotAllowed) {
				$co['ALLOWED'] = false;
			}
			$output[] = $co;
			$loop++;
		}
	}
}

if ( ($siteID && $pageID) && ($siteID != 'dummy') && ($pageID != 'dummy') &&
	 ($siteID != 'cblock_copy') && ($pageID != 'cblock_copy') ) {

	switch ($siteID) {
		case 'cblock':
			// For contentblocks
			$contentblockMgr = sCblockMgr();
			$page = $contentblockMgr->getCblock($pageID);

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $contentblockMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				$currentObject = $contentblockMgr->getCblock($lockedObject['OBJECTID']);
				$currentObject->releaseLock($lockedObject['TOKEN']);
			}
			break;

		case 'file':
			// For files
			$fileMgr = sFileMgr();
			$page = $fileMgr->getFile($pageID);

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $fileMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				$currentObject = $fileMgr->getFile($lockedObject['OBJECTID']);
				$currentObject->releaseLock($lockedObject['TOKEN']);
			}
			break;

		case 'mailing':
			// For mailings
			$page = $mailingMgr->getMailing($pageID);

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $mailingMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				$currentObject = $mailingMgr->getMailing($lockedObject['OBJECTID']);
				$currentObject->releaseLock($lockedObject['TOKEN']);
			}
			break;
		default:
			// For pages
			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $pageMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				$currentObject = $pageMgr->getPage($lockedObject['OBJECTID']);
				$currentObject->releaseLock($lockedObject['TOKEN']);
			}
			break;
	}

	// Check for lock, and lock if not locked
	if ($page) {
		$lockStatus = $page->getLock();
		if ($lockStatus['LOCKED'] == 0) {
			$lockedFailed = !$page->acquireLock($lockToken);
		} else {
			$lockedFailed = true;
		}
	}
	if ($lockedFailed) {
		// Get user who locked this object
		$userWithLock = new User( $lockStatus['LOCKUID'] );
		$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
		$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
		$smarty->assign('lockedByUser', $lockedByUser );
		$pageInfo['RWRITE'] = false;
	}

	if ($siteID == 'mailing') {
		// Check if a send is in progress (and lock if true)
		$mailingStatus = $page->getStatus();
		if ($mailingStatus['STATUS'] == 'INPROGRESS') {
			$userWithLock = new User( $mailingStatus['UID'] );
			$lockedByUser = $userWithLock->get( $mailingStatus['UID'] );
			$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $mailingStatus['UID'] );
			$smarty->assign('lockedByUser', $lockedByUser );
			$pageInfo['RWRITE'] = false;
		}
	}

}

$smarty->assign('pageInfo', $pageInfo);
$smarty->assign('userinfo', $userinfo);
$smarty->assign('objecttype', $objectType);

$smarty->assign('page_id', $pageID);
$smarty->assign('site_id', $siteID);
$smarty->assign('cos', $output);
if (($output == null) && ($selectiondialog!=null)) $smarty->assign('render_no_selection', true);
$smarty->assign('win_no', $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>