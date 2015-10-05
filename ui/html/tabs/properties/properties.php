<?php

$objecttype = $this->request->parameters['yg_type'];
$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$data = explode('-',$ygid);
$object = $data[0];
$siteID = $data[1];
$icons = new Icons();

$siteMgr = new Sites();
$sitename = $siteMgr->getname($siteID);
$sites = $siteMgr->getList();
$user = new User(sUserMgr()->getCurrentUserID());
$tagMgr = new Tags();
$filetypeMgr = new Filetypes();

$userinfo = $user->get();
$userinfo['PROPS'] = $user->properties->getValues( sUserMgr()->getCurrentUserID() );

switch ($objecttype) {

	case 'user':
		$no_traceline = true;
		$autosave = true;

		if ($object) {
			$currentUser = new User($object);
			$currentUserInfo = $currentUser->get();
			$password = $currentUserInfo['PASSWORD'];

			$object_properties = sUserMgr()->properties->getList('LISTORDER');

			$object_permissions = array();
			$object_permissions['RWRITE'] = sUsergroups()->permissions->check(sUserMgr()->getCurrentUserID(), 'RUSERS');

			if (sUserMgr()->getAnonymousID() == (int)$object) $object_permissions['RWRITE'] = 0;

			$objectdynprops = array();
			$objectdynprops_cnt = 0;

			$visibleProps = 0;
			foreach ($object_properties as $object_property) {
				$objectdynprops[$objectdynprops_cnt] = $object_property;
				$objectdynprops[$objectdynprops_cnt]['VALUE'] = $currentUser->properties->getValueInternal($object_property['IDENTIFIER']);

				if ($object_property['TYPE']=='LIST') {
					$listentries = sUserMgr()->properties->getListValues( $object_property['IDENTIFIER'] );
					$objectdynprops[$objectdynprops_cnt]['LVALUES'] = $listentries;
				}
				if ($object_property['TYPE']=='FILE') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$tmpFile = sFileMgr()->getFile($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ($tmpFile) {
							$fileInfo = $tmpFile->get();
							$objectdynprops[$objectdynprops_cnt]['FILETITLE'] = $fileInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $fileInfo['COLOR'];
							$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $fileInfo['IDENTIFIER'];
							$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $fileInfo['CODE'];
						}
					}
				}
				if ($object_property['TYPE']=='TAG') {
					$tagInfo = $tagMgr->get($objectdynprops[$objectdynprops_cnt]['VALUE']);
					$objectdynprops[$objectdynprops_cnt]['TAGTITLE'] = $tagInfo['NAME'];
				}
				if ($object_property['IDENTIFIER'] == 'EMAIL') {
					$email = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'COMPANY') {
					$company = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'DEPARTMENT') {
					$department = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'FIRSTNAME') {
					$firstname = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'LASTNAME') {
					$lastname = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'PHONE') {
					$phone = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'FAX') {
					$fax = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'MOBILE') {
					$mobile = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'WEBSITE') {
					$website = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'TIMEZONE') {
					$timezone = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'DATEFORMAT') {
					$dateformat = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'TIMEFORMAT') {
					$timeformat = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['IDENTIFIER'] == 'WEEKSTART') {
					$weekstart = $objectdynprops[$objectdynprops_cnt]['VALUE'];
				}
				if ($object_property['VISIBLE'] && ($object_property['TYPE'] != 'HEADLINE')) {
					$visibleProps++;
				}
				if (($object_property['TYPE']=='DATETIME')||($object_property['TYPE']=='DATE')) {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$objectdynprops[$objectdynprops_cnt]['VALUE'] = TStoLocalTS($objectdynprops[$objectdynprops_cnt]['VALUE']);
					}
				}
				if ($object_property['TYPE']=='CBLOCK') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$tmpCb = sCblockMgr()->getCblock($objectdynprops[$objectdynprops_cnt]['VALUE']);
						$cblockInfo = $tmpCb->get();
						$objectdynprops[$objectdynprops_cnt]['CBLOCKTITLE'] = $cblockInfo['NAME'];
					}
				}
				if ($object_property['TYPE']=='PAGE') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']['site'] && $objectdynprops[$objectdynprops_cnt]['VALUE']['page']) {
						$pageMgr = new PageMgr($objectdynprops[$objectdynprops_cnt]['VALUE']['site']);
						$currPage = $pageMgr->getPage($objectdynprops[$objectdynprops_cnt]['VALUE']['page']);
						$pageInfo = $currPage->get();
						$objectdynprops[$objectdynprops_cnt]['PAGETITLE'] = $pageInfo['NAME'];
					}
				}
				if ($object_property['TYPE']=='LINK') {
					$special_url = resolveSpecialURL($objectdynprops[$objectdynprops_cnt]['VALUE']);
					if ($special_url !== false) {
						$special_url_info = getSpecialURLInfo($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ( ($special_url_info['TYPE'] == 'IMG') || ($special_url_info['TYPE'] == 'DOWN') ) {
							$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
							if ($special_url_info['ID']) {
								$tmpFile = sFileMgr()->getFile($special_url_info['ID']);
								if ($tmpFile) {
									$link_fileinfo = $tmpFile->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
									$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
									$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
									$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
									$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
									$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $special_url_info['ID'];
								}
							}
						} else {
							$link_page = str_replace('/','',$internal[5][0]);
							$pageMgr = new PageMgr($special_url_info['SITE']);
							$tmpPage = $pageMgr->getPage($special_url_info['ID']);
							$link_pageInfo = $tmpPage->get();
							if ($siteID != 'cblock') {
							}
							$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
						}
					} else if (preg_match_all($this->URLRegEx1, $objectdynprops[$objectdynprops_cnt]['VALUE'], $internal) > 0) {
						if ($internal[2][0]=='download') {
							$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
							$link_file = str_replace('/','',$internal[3][0]);
							if ($link_file) {
								$tmpFile = sFileMgr()->getFile($link_file);
								if ($tmpFile) {
									$link_fileinfo = $tmpFile->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
									$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
									$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
									$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
									$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
									$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $link_file;
								}
							}
						} else {
							$link_site = $internal[3][0];
							$link_page = str_replace('/','',$internal[5][0]);
								$pageMgr = new PageMgr($link_site);
								$tmpPage = $pageMgr->getPage($link_page);
								$link_pageInfo = $tmpPage->get();
							if ($siteID != 'cblock') {
							}
							$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
						}
					} elseif (substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 0, 7)=='mailto:') {
						$objectdynprops[$objectdynprops_cnt]['IS_EMAIL'] = true;
						$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 7);
					} else {
						$linkInfo = checkLinkInternalExternal( $objectdynprops[$objectdynprops_cnt]['VALUE'] );
						switch($linkInfo['TYPE']) {
							case 'external':
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $objectdynprops[$objectdynprops_cnt]['VALUE'];
								break;
							case 'internal':
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
								$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
								break;
							case 'file':
								$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
								$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $linkInfo['INFO']['IDENTIFIER'];
								$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $linkInfo['INFO']['CODE'];
								$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $linkInfo['INFO']['COLOR'];
								$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $linkInfo['INFO']['FILE_ID'];
								break;
						}
					}
				}
				$objectdynprops_cnt++;
			}

			if ( file_exists($this->approot.$this->userpicdir.$object.'-picture.jpg') ) {
				$internPrefix = (string)sConfig()->getVar('CONFIG/REFTRACKER/INTERNALPREFIX');
				$userpicture = $internPrefix.'userimage/'.$object.'/48x48?rnd='.rand();
			} else {
				$userpicture = $this->imgpath.'content/temp_userpic.png';
			}

			$titlename = trim($firstname.' '.$lastname);
			if (strlen($titlename)==0) $titlename = $itext['TXT_UNKNOWN'];
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setUserHeader(\''.$userpicture.'\', \''.$titlename.'\', \''.$company.'\', \''.$object.'\');');

			$languageMgr = new Languages();
			$languages = $languageMgr->getList();

			$smarty->assign( 'languages', $languages );
			$smarty->assign( 'timezones', getTimezones() );
			$smarty->assign( 'visibleProps', $visibleProps );
			$smarty->assign( 'timezone', $timezone );
			$smarty->assign( 'language', $currentUser->getLanguage() );
			$smarty->assign( 'dateformat', $dateformat );
			$smarty->assign( 'timeformat', $timeformat );
			$smarty->assign( 'weekstart', $weekstart );
			$smarty->assign( 'userpicture', $userpicture.'?rnd='.rand() );
			$smarty->assign( 'email', $email );
			$smarty->assign( 'company', $company );
			$smarty->assign( 'department', $department );
			$smarty->assign( 'firstname', $firstname );
			$smarty->assign( 'lastname', $lastname );
			$smarty->assign( 'phone', $phone );
			$smarty->assign( 'fax', $fax );
			$smarty->assign( 'mobile', $mobile );
			$smarty->assign( 'website', $website );
			$smarty->assign( 'password', $password );
		}
		break;

	case 'usergroup':
		$no_traceline = true;
		$autosave = true;

		$object_permissions['RWRITE'] = sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, 'RWRITE');
		$objectInfo = sUsergroups()->get($object);

		$objectdynprops = array(
			0 => array(
				0 => 'Name',				'NAME' => 'Name',
				1 => 1,						'ID' => 1,
				2 => 'NAME',				'IDENTIFIER' => 'NAME',
				3 => 1,						'VISIBLE' => 1,
				4 => 'TEXT',				'TYPE' => 'TEXT',
				5 => $objectInfo['NAME'],	'VALUE' => $objectInfo['NAME']
			)
		);
		break;

	case 'extension':
		$extensionMgr = new ExtensionMgr();
		$autosave = false;

		$objectInfo = $extensionMgr->get($object);

		if ($objectInfo["CODE"]) {

			$extension = $extensionMgr->getExtension($objectInfo["CODE"]);

			if ($extension && $objectInfo["INSTALLED"]) {

				switch($siteID) {
					case 'extpage':
						if ($siteID=='extpage') $object_permissions['RWRITE'] = $user->checkPermission("REXTENSIONS_PAGE");
					case 'extcblock':
						if ($siteID=='extcblock') $object_permissions['RWRITE'] = $user->checkPermission("REXTENSIONS_CBLOCK");
					case 'extfile':
						if ($siteID=='extfile') $object_permissions['RWRITE'] = $user->checkPermission("REXTENSIONS_FILE");
					case 'extmailing':
						if ($siteID=='extmailing') $object_permissions['RWRITE'] = $user->checkPermission("REXTENSIONS_MAILING");
					case 'extimport':
						if ($siteID=='extimport') $object_permissions['RWRITE'] = $user->checkPermission("RIMPORT");
					case 'extexport':
						if ($siteID=='extexport') $object_permissions['RWRITE'] = $user->checkPermission("REXPORT");
					case 'extcolistview':
						// Call callback
						$extension->callExtensionHook('onRenderExtensionAdmin');
						$properties = $extension->extensionProperties;
						$propertySettings = $extension->extensionPropertySettings;
						if ($siteID=='extcolistview') $object_permissions['RWRITE'] = $user->checkPermission("REXTENSIONS_CBLISTVIEW");
						break;
					case 'data':
						// Call callback
						$extension->callExtensionHook('onRenderDataAdmin');
						switch($objectInfo['TYPE']) {
							case EXTENSION_IMPORT:
								// Import
								$properties = $extension->importProperties;
								$propertySettings = $extension->importPropertySettings;

								break;
							case EXTENSION_EXPORT:
								// Export
								$properties = $extension->exportProperties;
								$propertySettings = $extension->exportPropertySettings;
								break;
						}
						$object_permissions['RWRITE'] = $user->checkPermission("RDATA");
						break;
				}

				$object_properties = $properties->getList('LISTORDER');

				$objectdynprops = array();
				$objectdynprops_cnt = 0;

				foreach ($object_properties as $object_property) {

					$objectdynprops[$objectdynprops_cnt] = $object_property;
					$objectdynprops[$objectdynprops_cnt]['VALUE'] = $properties->getValueInternal($object_property['IDENTIFIER']);

					if ($object_property['TYPE']=='LIST') {
						$listentries = $propertySettings->getListValues( $object_property['IDENTIFIER'] );
						$objectdynprops[$objectdynprops_cnt]['LVALUES'] = $listentries;
					}
					if ($object_property['TYPE']=='FILE') {
						$specialURL = createSpecialURLfromShortURL($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ($specialURL) {
							$specialURLInfo = getSpecialURLInfo($specialURL);
							if ($specialURLInfo['TYPE'] == 'IMG') {
								$objectdynprops[$objectdynprops_cnt]['VALUE'] = $specialURLInfo['ID'];
							}
						}
						if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
							$tmpFile = sFileMgr()->getFile($objectdynprops[$objectdynprops_cnt]['VALUE']);
							if ($tmpFile) {
								$fileInfo = $tmpFile->get();
								$objectdynprops[$objectdynprops_cnt]['FILETITLE'] = $fileInfo['NAME'];
								$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $fileInfo['COLOR'];
								$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $fileInfo['IDENTIFIER'];
								$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $fileInfo['ABBREVIATION'];
							}
						}
					}
					if ($object_property['TYPE']=='TAG') {
						$tagInfo = $tagMgr->get($objectdynprops[$objectdynprops_cnt]['VALUE']);
						$objectdynprops[$objectdynprops_cnt]['TAGTITLE'] = $tagInfo['NAME'];
					}
					if ($object_property['TYPE']=='CBLOCK') {
						if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
							$tmpCb = sCblockMgr()->getCblock($objectdynprops[$objectdynprops_cnt]['VALUE']);
							if ($tmpCb) {
								$cblockInfo = $tmpCb->get();
								$objectdynprops[$objectdynprops_cnt]['CBLOCKTITLE'] = $cblockInfo['NAME'];
							}
						}
					}
					if ($object_property['TYPE']=='PAGE') {
						if ($objectdynprops[$objectdynprops_cnt]['VALUE']['site'] && $objectdynprops[$objectdynprops_cnt]['VALUE']['page']) {
							$pageMgr = sPageMgr($objectdynprops[$objectdynprops_cnt]['VALUE']['site']);
							$currPage = $pageMgr->getPage($objectdynprops[$objectdynprops_cnt]['VALUE']['page']);
							if ($currPage) {
								$pageInfo = $currPage->get();
								$objectdynprops[$objectdynprops_cnt]['PAGETITLE'] = $pageInfo['NAME'];
							}
						}
					}
					if (($object_property['TYPE']=='DATETIME')||($object_property['TYPE']=='DATE')) {
						if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
							$objectdynprops[$objectdynprops_cnt]['VALUE'] = TStoLocalTS($objectdynprops[$objectdynprops_cnt]['VALUE']);
						}
					}
					if ($object_property['TYPE']=='LINK') {
						$special_url = resolveSpecialURL($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ($special_url !== false) {
							$special_url_info = getSpecialURLInfo($objectdynprops[$objectdynprops_cnt]['VALUE']);
							if ( ($special_url_info['TYPE'] == 'IMG') || ($special_url_info['TYPE'] == 'DOWN') ) {
								$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
								if ($special_url_info['ID']) {
									$tmpFile = sFileMgr()->getFile($special_url_info['ID']);
									if ($tmpFile) {
										$link_fileinfo = $tmpFile->get();
										$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
										$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
										$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
										$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
										$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
										$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $special_url_info['ID'];
									}
								}
							} else {
								$link_page = str_replace('/','',$internal[5][0]);
								$pageMgr = new PageMgr($special_url_info['SITE']);
								$tmpPage = $pageMgr->getPage($special_url_info['ID']);
								if ($tmpPage) {
									$link_pageInfo = $tmpPage->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
									$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
								}
							}
						} else if (preg_match_all($this->URLRegEx1, $objectdynprops[$objectdynprops_cnt]['VALUE'], $internal) > 0) {
							if ($internal[2][0]=='download') {
								$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
								$link_file = str_replace('/','',$internal[3][0]);
								if ($link_file) {
									$tmpFile = sFileMgr()->getFile($link_file);
									if ($tmpFile) {
										$link_fileinfo = $tmpFile->get();
										$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
										$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
										$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
										$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
										$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
										$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $link_file;
									}
								}
							} else {
								$link_site = $internal[3][0];
								$link_page = str_replace('/','',$internal[5][0]);
								$pageMgr = new PageMgr($link_site);
								$tmpPage = $pageMgr->getPage($link_page);
								if ($tmpPage) {
									$link_pageInfo = $tmpPage->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
									$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
								}
							}
						} elseif (substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 0, 7)=='mailto:') {
							$objectdynprops[$objectdynprops_cnt]['IS_EMAIL'] = true;
							$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 7);
						} else {
							$linkInfo = checkLinkInternalExternal( $objectdynprops[$objectdynprops_cnt]['VALUE'] );
							switch($linkInfo['TYPE']) {
								case 'external':
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $objectdynprops[$objectdynprops_cnt]['VALUE'];
									break;
								case 'internal':
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
									$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
									break;
								case 'file':
									$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
									$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $linkInfo['INFO']['IDENTIFIER'];
									$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $linkInfo['INFO']['CODE'];
									$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $linkInfo['INFO']['COLOR'];
									$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $linkInfo['INFO']['FILE_ID'];
									break;
							}
						}
					}
					$objectdynprops_cnt++;
				}
			}
		}
		break;

	case 'cblock':
		$autosave = true;
		$cb = sCblockMgr()->getCblock($object);
		if ($cb) {
			$objectInfo = $cb->get();
			$objectInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RWRITE");
			$objectInfo['RDELETE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RDELETE");
			$styleData = getStyleForContentblock($objectInfo, true);
			$objectInfo['STYLE'] = $styleData;

			// Remove changed style if object is a folder
			if ($objectInfo['FOLDER']==1) {
				$objectInfo['STYLE'] = '';
			}

			$objectparents = sCblockMgr()->getParents($object);

			// Gather all required info from parents
			foreach($objectparents as $objectparent_idx => $objectparent_item) {
				if ($objectparent_item[0]['ID']) {
					$tmpCb = sCblockMgr()->getCblock($objectparent_item[0]['ID']);
					$tmpObjectinfo = $tmpCb->get();
					$objectparents[$objectparent_idx][0]['HASCHANGED'] = $tmpObjectinfo['HASCHANGED'];
					$objectparents[$objectparent_idx][0]['RWRITE'] = $tmpCb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectparent_item[0]['ID'], "RWRITE");
					$objectparents[$objectparent_idx][0]['RDELETE'] = $tmpCb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectparent_item[0]['ID'], "RDELETE");
					$styleData = getStyleForContentblock($objectparents[$objectparent_idx][0]);
					$objectparents[$objectparent_idx][0]['STYLE'] = $styleData['style'];
				}
			}
			$objectparents[count($objectparents)-1][0]['NAME'] = ($itext['TXT_CONTENTBLOCKS']!='')?($itext['TXT_CONTENTBLOCKS']):('$TXT_CONTENTBLOCKS');

			$object_properties = sCblockMgr()->properties->getList('LISTORDER');

			$object_permissions = array();
			$object_permissions['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RWRITE");
			$object_permissions['RSTAGE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RSTAGE");

			if ($objectInfo['DELETED']==1) {
				$object_permissions['RWRITE'] = false;
				$object_permissions['RSTAGE'] = false;
			}

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = sCblockMgr()->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				$currentObject = sCblockMgr()->getCblock($lockedObject['OBJECTID']);
				$currentObject->releaseLock($lockedObject['TOKEN']);
			}
			// Check for lock, and lock if not locked
			$lockStatus = $cb->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$cb->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}

			$objectdynprops = array();
			$objectdynprops_cnt = 0;
			foreach ($object_properties as $object_property) {
				$objectdynprops[$objectdynprops_cnt] = $object_property;
				$objectdynprops[$objectdynprops_cnt]['VALUE'] = $cb->properties->getValueInternal($object_property['IDENTIFIER']);
				if ($object_property['TYPE']=='LIST') {
					$listentries = sCblockMgr()->properties->getListValues( $object_property['IDENTIFIER'] );
					$objectdynprops[$objectdynprops_cnt]['LVALUES'] = $listentries;
				}
				if ($object_property['TYPE']=='FILE') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$tmpFile = sFileMgr()->getFile($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ($tmpFile) {
							$fileInfo = $tmpFile->get();
							$objectdynprops[$objectdynprops_cnt]['FILETITLE'] = $fileInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $fileInfo['COLOR'];
							$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $fileInfo['IDENTIFIER'];
							$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $fileInfo['CODE'];
						}
					}
				}
				if ($object_property['TYPE']=='TAG') {
					$tagInfo = $tagMgr->get($objectdynprops[$objectdynprops_cnt]['VALUE']);
					$objectdynprops[$objectdynprops_cnt]['TAGTITLE'] = $tagInfo['NAME'];
				}
				if ($object_property['TYPE']=='CBLOCK') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$tmpCb = sCblockMgr()->getCblock($objectdynprops[$objectdynprops_cnt]['VALUE']);
						$cblockInfo = $tmpCb->get();
						$objectdynprops[$objectdynprops_cnt]['CBLOCKTITLE'] = $cblockInfo['NAME'];
					}
				}
				if ($object_property['TYPE']=='PAGE') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']['site'] && $objectdynprops[$objectdynprops_cnt]['VALUE']['page']) {
						$pageMgr = new PageMgr($objectdynprops[$objectdynprops_cnt]['VALUE']['site']);
						$currPage = $pageMgr->getPage($objectdynprops[$objectdynprops_cnt]['VALUE']['page']);
						$pageInfo = $currPage->get();
						$objectdynprops[$objectdynprops_cnt]['PAGETITLE'] = $pageInfo['NAME'];
					}
				}
				if (($object_property['TYPE']=='DATETIME')||($object_property['TYPE']=='DATE')) {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$objectdynprops[$objectdynprops_cnt]['VALUE'] = TStoLocalTS($objectdynprops[$objectdynprops_cnt]['VALUE']);
					}
				}
				if ($object_property['TYPE']=='LINK') {
					$special_url = resolveSpecialURL($objectdynprops[$objectdynprops_cnt]['VALUE']);
					if ($special_url !== false) {
						$special_url_info = getSpecialURLInfo($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ( ($special_url_info['TYPE'] == 'IMG') || ($special_url_info['TYPE'] == 'DOWN') ) {
							$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
							if ($special_url_info['ID']) {
								$tmpFile = sFileMgr()->getFile($special_url_info['ID']);
								if ($tmpFile) {
									$link_fileinfo = $tmpFile->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
									$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
									$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
									$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
									$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
									$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $special_url_info['ID'];
								}
							}
						} else {
							$pageMgr = new PageMgr($special_url_info['SITE']);
							$tmpPage = $pageMgr->getPage($special_url_info['ID']);
							$link_pageInfo = $tmpPage->get();
							if ($siteID != 'cblock') {
							}
							$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
						}
					} else if (preg_match_all($this->URLRegEx1, $objectdynprops[$objectdynprops_cnt]['VALUE'], $internal) > 0) {
						if ($internal[2][0]=='download') {
							$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
							$link_file = str_replace('/','',$internal[3][0]);
							if ($link_file) {
								$tmpFile = sFileMgr()->getFile($link_file);
								if ($tmpFile) {
									$link_fileinfo = $tmpFile->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
									$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
									$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
									$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
									$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
									$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $link_file;
								}
							}
						} else {
							$link_site = $internal[3][0];
							$link_page = str_replace('/','',$internal[5][0]);
								$pageMgr = new PageMgr($link_site);
								$tmpPage = $pageMgr->getPage($link_page);
								$link_pageInfo = $tmpPage->get();
							if ($siteID != 'cblock') {
							}
							$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
						}
					} elseif (substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 0, 7)=='mailto:') {
						$objectdynprops[$objectdynprops_cnt]['IS_EMAIL'] = true;
						$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 7);
					} else {
						$linkInfo = checkLinkInternalExternal( $objectdynprops[$objectdynprops_cnt]['VALUE'] );
						switch($linkInfo['TYPE']) {
							case 'external':
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $objectdynprops[$objectdynprops_cnt]['VALUE'];
								break;
							case 'internal':
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
								$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
								break;
							case 'file':
								$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
								$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $linkInfo['INFO']['IDENTIFIER'];
								$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $linkInfo['INFO']['CODE'];
								$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $linkInfo['INFO']['COLOR'];
								$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $linkInfo['INFO']['FILE_ID'];
								break;
						}
					}
				}
				$objectdynprops_cnt++;
			}

			if (strlen($objectInfo['PNAME']) < 1) {
				$objectInfo['PNAME'] = $cb->calcPName();
			}
		}
		break;

	case 'page':
		$autosave = true;
		$pageMgr = new PageMgr($siteID);

		$page = $pageMgr->getPage($object);
		if ($page) {
			$objectInfo = $page->get();
			$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RWRITE");
			$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RDELETE");
			$iconData = getIconForPage($objectInfo);
			$objectInfo['ICON'] = $iconData['iconclass'];
			$objectInfo['STYLE'] = $iconData['style'];
			$objectparents = $pageMgr->getParents($object);
			array_pop($objectparents);
			// Gather all required info from parents
			foreach($objectparents as $objectparent_idx => $objectparent_item) {
				$tmpPage = $pageMgr->getPage($objectparent_item[0]['ID']);
				$tmpObjectinfo = $tmpPage->get();
				$objectparents[$objectparent_idx][0]['NAVIGATIONID'] = $tmpObjectinfo['NAVIGATIONID'];
				$objectparents[$objectparent_idx][0]['HASCHANGED'] = $tmpObjectinfo['HASCHANGED'];

				$objectparents[$objectparent_idx][0]['RWRITE'] = $tmpPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectparent_item[0]['ID'], "RWRITE");
				$objectparents[$objectparent_idx][0]['RDELETE'] = $tmpPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectparent_item[0]['ID'], "RDELETE");

				$iconData = getIconForPage($objectparents[$objectparent_idx][0]);
				$objectparents[$objectparent_idx][0]['ICON'] = $iconData['iconclass'];
				$objectparents[$objectparent_idx][0]['STYLE'] = $iconData['style'];
			}

			// Get page properties
			$object_properties = $pageMgr->properties->getList('LISTORDER');

			$object_permissions = array();
			$object_permissions['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RWRITE");
			$object_permissions['RSTAGE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RSTAGE");

			if ($objectInfo['DELETED']==1) {
				$object_permissions['RWRITE'] = false;
				$object_permissions['RSTAGE'] = false;
			}

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $pageMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				$currentObject = $pageMgr->getPage($lockedObject['OBJECTID']);
				$currentObject->releaseLock($lockedObject['TOKEN']);
			}
			// Check for lock, and lock if not locked
			$lockStatus = $page->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$page->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}

			$objectdynprops = array();
			$objectdynprops_cnt = 0;
			foreach ($object_properties as $object_property) {
				$objectdynprops[$objectdynprops_cnt] = $object_property;
				$objectdynprops[$objectdynprops_cnt]['VALUE'] = $page->properties->getValueInternal($object_property['IDENTIFIER']);
				if ($object_property['TYPE']=='LIST') {
					$listentries = $pageMgr->properties->getListValues( $object_property['IDENTIFIER'] );
					$objectdynprops[$objectdynprops_cnt]['LVALUES'] = $listentries;
				}
				if ($object_property['TYPE']=='FILE') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$tmpFile = sFileMgr()->getFile($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ($tmpFile) {
							$fileInfo = $tmpFile->get();
							$objectdynprops[$objectdynprops_cnt]['FILETITLE'] = $fileInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $fileInfo['COLOR'];
							$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $fileInfo['IDENTIFIER'];
							$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $fileInfo['ABBREVIATION'];
						}
					}
				}
				if ($object_property['TYPE']=='TAG') {
					$tagInfo = $tagMgr->get($objectdynprops[$objectdynprops_cnt]['VALUE']);
					$objectdynprops[$objectdynprops_cnt]['TAGTITLE'] = $tagInfo['NAME'];
				}
				if ($object_property['TYPE']=='CBLOCK') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$tmpCb = sCblockMgr()->getCblock($objectdynprops[$objectdynprops_cnt]['VALUE']);
						$cblockInfo = $tmpCb->get();
						$objectdynprops[$objectdynprops_cnt]['CBLOCKTITLE'] = $cblockInfo['NAME'];
					}
				}
				if ($object_property['TYPE']=='PAGE') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']['site'] && $objectdynprops[$objectdynprops_cnt]['VALUE']['page']) {
						$pageMgr = new PageMgr($objectdynprops[$objectdynprops_cnt]['VALUE']['site']);
						$currPage = $pageMgr->getPage($objectdynprops[$objectdynprops_cnt]['VALUE']['page']);
						$pageInfo = $currPage->get();
						$objectdynprops[$objectdynprops_cnt]['PAGETITLE'] = $pageInfo['NAME'];
					}
				}
				if (($object_property['TYPE']=='DATETIME')||($object_property['TYPE']=='DATE')) {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$objectdynprops[$objectdynprops_cnt]['VALUE'] = TStoLocalTS($objectdynprops[$objectdynprops_cnt]['VALUE']);
					}
				}
				if ($object_property['TYPE']=='LINK') {
					$special_url = resolveSpecialURL($objectdynprops[$objectdynprops_cnt]['VALUE']);
					if ($special_url !== false) {
						$special_url_info = getSpecialURLInfo($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ( ($special_url_info['TYPE'] == 'IMG') || ($special_url_info['TYPE'] == 'DOWN') ) {
							$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
							if ($special_url_info['ID']) {
								$tmpFile = sFileMgr()->getFile($special_url_info['ID']);
								if ($tmpFile) {
									$link_fileinfo = $tmpFile->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
									$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
									$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
									$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
									$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
									$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $special_url_info['ID'];
								}
							}
						} else {
							$pageMgr = new PageMgr($special_url_info['SITE']);
							$tmpPage = $pageMgr->getPage($special_url_info['ID']);
							$link_pageInfo = $tmpPage->get();
							if ($siteID != 'cblock') {
							}
							$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
						}
					} else if (preg_match_all($this->URLRegEx1, $objectdynprops[$objectdynprops_cnt]['VALUE'], $internal) > 0) {
						if ($internal[2][0]=='download') {
							$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
							$link_file = str_replace('/','',$internal[3][0]);
							if ($link_file) {
								$tmpFile = sFileMgr()->getFile($link_file);
								if ($tmpFile) {
									$link_fileinfo = $tmpFile->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
									$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
									$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
									$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
									$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
									$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $link_file;
								}
							}
						} else {
							$link_site = $internal[3][0];
							$link_page = str_replace('/','',$internal[5][0]);
								$pageMgr = new PageMgr($link_site);
								$tmpPage = $pageMgr->getPage($link_page);
								$link_pageInfo = $tmpPage->get();
							if ($siteID != 'cblock') {
							}
							$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
						}
					} elseif (substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 0, 7)=='mailto:') {
						$objectdynprops[$objectdynprops_cnt]['IS_EMAIL'] = true;
						$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 7);
					} else {
						$linkInfo = checkLinkInternalExternal( $objectdynprops[$objectdynprops_cnt]['VALUE'] );
						switch($linkInfo['TYPE']) {
							case 'external':
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $objectdynprops[$objectdynprops_cnt]['VALUE'];
								break;
							case 'internal':
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
								$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
								break;
							case 'file':
								$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
								$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $linkInfo['INFO']['IDENTIFIER'];
								$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $linkInfo['INFO']['CODE'];
								$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $linkInfo['INFO']['COLOR'];
								$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $linkInfo['INFO']['FILE_ID'];
								break;
						}
					}
				}
				$objectdynprops_cnt++;
			}

			if (strlen($objectInfo['PNAME']) < 1) {
				$objectInfo['PNAME'] = $page->calcPName();
			}
		}
		break;

	case 'filefolder':
		$autosave = true;
		$file = sFileMgr()->getFile($object);
		if ($file) {
			$objectInfo = $file->get();
			$objectparents = sFileMgr()->getParents($object);

			$object_properties = sFileMgr()->properties->getList('LISTORDER');

			$object_permissions = array();
			$object_permissions['RWRITE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RWRITE");


			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = sFileMgr()->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				if ($lockedObject['OBJECTID']) {
					$currentObject = sFileMgr()->getFile($lockedObject['OBJECTID']);
					if ($currentObject) {
						$currentObject->releaseLock($lockedObject['TOKEN']);
					}
				}
			}
			// Check for lock, and lock if not locked
			$lockStatus = $file->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$file->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}

			$objectdynprops = array();
			$objectdynprops_cnt = 0;
			foreach ($object_properties as $object_property) {
				$objectdynprops[$objectdynprops_cnt] = $object_property;
				$objectdynprops[$objectdynprops_cnt]['VALUE'] = $file->properties->getValueInternal($object_property['IDENTIFIER']);
				if ($object_property['TYPE']=='LIST') {
					$listentries = sFileMgr()->properties->getListValues( $object_property['IDENTIFIER'] );
					$objectdynprops[$objectdynprops_cnt]['LVALUES'] = $listentries;
				}
				if ($object_property['TYPE']=='FILE') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$tmpFile = sFileMgr()->getFile($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ($tmpFile) {
							$fileInfo = $tmpFile->get();
							$objectdynprops[$objectdynprops_cnt]['FILETITLE'] = $fileInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $fileInfo['COLOR'];
							$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $fileInfo['IDENTIFIER'];
							$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $fileInfo['CODE'];
						}
					}
				}
				if ($object_property['TYPE']=='TAG') {
					$tagInfo = $tagMgr->get($objectdynprops[$objectdynprops_cnt]['VALUE']);
					$objectdynprops[$objectdynprops_cnt]['TAGTITLE'] = $tagInfo['NAME'];
				}
				if ($object_property['TYPE']=='CBLOCK') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$tmpCb = sCblockMgr()->getCblock($objectdynprops[$objectdynprops_cnt]['VALUE']);
						$cblockInfo = $tmpCb->get();
						$objectdynprops[$objectdynprops_cnt]['CBLOCKTITLE'] = $cblockInfo['NAME'];
					}
				}
				if ($object_property['TYPE']=='PAGE') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']['site'] && $objectdynprops[$objectdynprops_cnt]['VALUE']['page']) {
						$pageMgr = new PageMgr($objectdynprops[$objectdynprops_cnt]['VALUE']['site']);
						$currPage = $pageMgr->getPage($objectdynprops[$objectdynprops_cnt]['VALUE']['page']);
						$pageInfo = $currPage->get();
						$objectdynprops[$objectdynprops_cnt]['PAGETITLE'] = $pageInfo['NAME'];
					}
				}
				if (($object_property['TYPE']=='DATETIME')||($object_property['TYPE']=='DATE')) {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$objectdynprops[$objectdynprops_cnt]['VALUE'] = TStoLocalTS($objectdynprops[$objectdynprops_cnt]['VALUE']);
					}
				}
				if ($object_property['TYPE']=='LINK') {
					$special_url = resolveSpecialURL($objectdynprops[$objectdynprops_cnt]['VALUE']);
					if ($special_url !== false) {
						$special_url_info = getSpecialURLInfo($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ( ($special_url_info['TYPE'] == 'IMG') || ($special_url_info['TYPE'] == 'DOWN') ) {
							$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
							if ($special_url_info['ID']) {
								$tmpFile = sFileMgr()->getFile($special_url_info['ID']);
								if ($tmpFile) {
									$link_fileinfo = $tmpFile->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
									$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
									$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
									$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
									$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
									$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $special_url_info['ID'];
								}
							}
						} else {
							$pageMgr = new PageMgr($special_url_info['SITE']);
							$tmpPage = $pageMgr->getPage($special_url_info['ID']);
							$link_pageInfo = $tmpPage->get();
							if ($siteID != 'cblock') {
							}
							$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
						}
					} else if (preg_match_all($this->URLRegEx1, $objectdynprops[$objectdynprops_cnt]['VALUE'], $internal) > 0) {
						if ($internal[2][0]=='download') {
							$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
							$link_file = str_replace('/','',$internal[3][0]);
							if ($link_file) {
								$tmpFile = sFileMgr()->getFile($link_file);
								if ($tmpFile) {
									$link_fileinfo = $tmpFile->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
									$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
									$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
									$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
									$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
									$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $link_file;
								}
							}
						} else {
							$link_site = $internal[3][0];
							$link_page = str_replace('/','',$internal[5][0]);
								$pageMgr = new PageMgr($link_site);
								$tmpPage = $pageMgr->getPage($link_page);
								$link_pageInfo = $tmpPage->get();
							if ($siteID != 'cblock') {
							}
							$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
						}
					} elseif (substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 0, 7)=='mailto:') {
						$objectdynprops[$objectdynprops_cnt]['IS_EMAIL'] = true;
						$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 7);
					} else {
						$linkInfo = checkLinkInternalExternal( $objectdynprops[$objectdynprops_cnt]['VALUE'] );
						switch($linkInfo['TYPE']) {
							case 'external':
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $objectdynprops[$objectdynprops_cnt]['VALUE'];
								break;
							case 'internal':
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
								$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
								break;
							case 'file':
								$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
								$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $linkInfo['INFO']['IDENTIFIER'];
								$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $linkInfo['INFO']['CODE'];
								$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $linkInfo['INFO']['COLOR'];
								$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $linkInfo['INFO']['FILE_ID'];
								break;
						}
					}
				}
				$objectdynprops_cnt++;
			}

			if (strlen($objectInfo['PNAME']) < 1) {
				$objectInfo['PNAME'] = $file->calcPName();
			}
		}
		break;

	case 'file':
		$autosave = true;
		$file = sFileMgr()->getFile($object);
		if ($file) {
			$objectInfo = $file->get();
			$objectparents = sFileMgr()->getParents($object);
			$filetypes = sFileMgr()->getFiletypes();

			$object_properties = sFileMgr()->properties->getList('LISTORDER');
			$object_permissions = array();
			$object_permissions['RWRITE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RWRITE");

			if ($objectInfo['DELETED']==1) {
				$object_permissions['RWRITE'] = false;
			}

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = sFileMgr()->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				if ($lockedObject['OBJECTID']) {
					$currentObject = sFileMgr()->getFile($lockedObject['OBJECTID']);
					if ($currentObject) {
						$currentObject->releaseLock($lockedObject['TOKEN']);
					}
				}
			}
			// Check for lock, and lock if not locked
			$lockStatus = $file->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$file->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}

			$objectdynprops = array();
			$objectdynprops_cnt = 0;

			foreach($filetypes as $filetypes_item) {
				if ($filetypes_item['ID'] == $objectInfo['FILETYPE']) {
					$objectInfo['FILETYPE_TXT'] = $filetypes_item['NAME'];
				}
			}

			if ($objectInfo['COLOR'] == 'NONE') {
				$objectInfo['COLOR'] = 'black';		// red, purple
			}

			foreach ($object_properties as $object_property) {
				$objectdynprops[$objectdynprops_cnt] = $object_property;
				$objectdynprops[$objectdynprops_cnt]['VALUE'] = $file->properties->getValueInternal($object_property['IDENTIFIER']);
				if ($object_property['TYPE']=='LIST') {
					$listentries = sFileMgr()->properties->getListValues( $object_property['IDENTIFIER'] );
					$objectdynprops[$objectdynprops_cnt]['LVALUES'] = $listentries;
				}
				if ($object_property['TYPE']=='FILE') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$tmpFile = sFileMgr()->getFile($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ($tmpFile) {
							$fileInfo = $tmpFile->get();
							$objectdynprops[$objectdynprops_cnt]['FILETITLE'] = $fileInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $fileInfo['COLOR'];
							$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $fileInfo['IDENTIFIER'];
							$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $fileInfo['ABBREVIATION'];
						}
					}
				}
				if ($object_property['TYPE']=='TAG') {
					$tagInfo = $tagMgr->get($objectdynprops[$objectdynprops_cnt]['VALUE']);
					$objectdynprops[$objectdynprops_cnt]['TAGTITLE'] = $tagInfo['NAME'];
				}
				if ($object_property['TYPE']=='CBLOCK') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$tmpCb = sCblockMgr()->getCblock($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ($tmpCb) {
							$cblockInfo = $tmpCb->get();
							$objectdynprops[$objectdynprops_cnt]['CBLOCKTITLE'] = $cblockInfo['NAME'];
						}
					}
				}
				if ($object_property['TYPE']=='PAGE') {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']['site'] && $objectdynprops[$objectdynprops_cnt]['VALUE']['page']) {
						$pageMgr = new PageMgr($objectdynprops[$objectdynprops_cnt]['VALUE']['site']);
						$currPage = $pageMgr->getPage($objectdynprops[$objectdynprops_cnt]['VALUE']['page']);
						$pageInfo = $currPage->get();
						$objectdynprops[$objectdynprops_cnt]['PAGETITLE'] = $pageInfo['NAME'];
					}
				}
				if (($object_property['TYPE']=='DATETIME')||($object_property['TYPE']=='DATE')) {
					if ($objectdynprops[$objectdynprops_cnt]['VALUE']) {
						$objectdynprops[$objectdynprops_cnt]['VALUE'] = TStoLocalTS($objectdynprops[$objectdynprops_cnt]['VALUE']);
					}
				}
				if ($object_property['TYPE']=='LINK') {
					$special_url = resolveSpecialURL($objectdynprops[$objectdynprops_cnt]['VALUE']);
					if ($special_url !== false) {
						$special_url_info = getSpecialURLInfo($objectdynprops[$objectdynprops_cnt]['VALUE']);
						if ( ($special_url_info['TYPE'] == 'IMG') || ($special_url_info['TYPE'] == 'DOWN') ) {
							$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
							if ($special_url_info['ID']) {
								$tmpFile = sFileMgr()->getFile($special_url_info['ID']);
								if ($tmpFile) {
									$link_fileinfo = $tmpFile->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
									$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
									$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
									$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
									$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
									$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $special_url_info['ID'];
								}
							}
						} else {
							$pageMgr = new PageMgr($special_url_info['SITE']);
							$tmpPage = $pageMgr->getPage($special_url_info['ID']);
							$link_pageInfo = $tmpPage->get();
							if ($siteID != 'cblock') {
							}
							$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
						}
					} else if (preg_match_all($this->URLRegEx1, $objectdynprops[$objectdynprops_cnt]['VALUE'], $internal) > 0) {
						if ($internal[2][0]=='download') {
							$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
							$link_file = str_replace('/','',$internal[3][0]);
							if ($link_file) {
								$tmpFile = sFileMgr()->File($link_file);
								if ($tmpFile) {
									$link_fileinfo = $tmpFile->get();
									$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_fileinfo['NAME'];
									$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
									$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $link_filetype['IDENTIFIER'];
									$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $link_filetype['CODE'];
									$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $link_filetype['COLOR'];
									$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $link_file;
								}
							}
						} else {
							$link_site = $internal[3][0];
							$link_page = str_replace('/','',$internal[5][0]);
							$pageMgr = new PageMgr($link_site);
							$tmpPage = $pageMgr->getPage($link_page);
							$link_pageInfo = $tmpPage->get();
							if ($siteID != 'cblock') {
							}
							$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $link_pageInfo['NAME'];
							$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
						}
					} elseif (substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 0, 7)=='mailto:') {
						$objectdynprops[$objectdynprops_cnt]['IS_EMAIL'] = true;
						$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = substr($objectdynprops[$objectdynprops_cnt]['VALUE'], 7);
					} else {
						$linkInfo = checkLinkInternalExternal( $objectdynprops[$objectdynprops_cnt]['VALUE'] );
						switch($linkInfo['TYPE']) {
							case 'external':
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $objectdynprops[$objectdynprops_cnt]['VALUE'];
								break;
							case 'internal':
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
								$objectdynprops[$objectdynprops_cnt]['IS_INTERNAL'] = true;
								break;
							case 'file':
								$objectdynprops[$objectdynprops_cnt]['IS_FILE'] = true;
								$objectdynprops[$objectdynprops_cnt]['LINKTITLE'] = $linkInfo['NAME'];
								$objectdynprops[$objectdynprops_cnt]['FILEIDENTIFIER'] = $linkInfo['INFO']['IDENTIFIER'];
								$objectdynprops[$objectdynprops_cnt]['FILEABBREVIATION'] = $linkInfo['INFO']['CODE'];
								$objectdynprops[$objectdynprops_cnt]['FILECOLOR'] = $linkInfo['INFO']['COLOR'];
								$objectdynprops[$objectdynprops_cnt]['FILE_ID'] = $linkInfo['INFO']['FILE_ID'];
								break;
						}
					}
				}
				$objectdynprops_cnt++;
			}
		}
		break;

	case 'tag':
		$autosave = true;
		$objectInfo = $tagMgr->get($object);
		$objectparents = $tagMgr->getParents($object);
		$objectparents[count($objectparents)-1][0]['NAME'] = ($itext['TXT_TAGS']!='')?($itext['TXT_TAGS']):('$TXT_TAGS');

		$object_permissions = array();
		$object_permissions['RWRITE'] = $tagMgr->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $object, "RWRITE");

		$objectdynprops = array(
			0 => array(
				0 => 'Name',				'NAME' => 'Name',
				1 => 1,						'ID' => 1,
				2 => 'NAME',				'IDENTIFIER' => 'NAME',
				3 => 1,						'VISIBLE' => 1,
				4 => 'TEXT',				'TYPE' => 'TEXT',
				5 => $objectInfo['NAME'],	'VALUE' => $objectInfo['NAME']
			)
		);
		break;
}

if ($lockedFailed) {
	// Get user who locked this object
	$userWithLock = new User( $lockStatus['LOCKUID'] );
	$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
	$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
	$smarty->assign('lockedByUser', $lockedByUser );
	$object_permissions['RWRITE'] = false;
	if (($objecttype == 'cblock') || ($objecttype == 'page')) {
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
	}
} else {
	if (($objecttype == 'cblock') || ($objecttype == 'page')) {
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$object_permissions['RSTAGE'].'\' );');
	}
}

$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

if ($objecttype == 'user') {
	$koala->queueScript('Koala.yg_initUserPropertiesPictureupload( \''.$this->request->parameters['win_no'].'\', \''.$object.'\' );');
	if ($userpicture) {
		$koala->queueScript('Koala.yg_changeBGImage( \'user\', \''.$object.'-user\', \'picture\', \''.$userpicture.'\' );');
	}
}
if ($siteID == 'data') {
	$koala->queueScript('Koala.yg_initExtensionDataUploadButtons( \''.$this->request->parameters['win_no'].'\', \''.$object.'\' );');
}

$smarty->assign('userinfo', $userinfo);
$smarty->assign('no_traceline', $no_traceline );
$smarty->assign('autosave', $autosave );
$smarty->assign('objectdynprops', $objectdynprops );
$smarty->assign('site', $siteID );
$smarty->assign('sitename', $sitename );
$smarty->assign('refresh', $refresh );
$smarty->assign('object', $object );
$smarty->assign('objectInfo', $objectInfo );
$smarty->assign('objectpermissions', $object_permissions );
$smarty->assign('objecttype', $objecttype );
$smarty->assign('objectparents', $objectparents );

$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>