<?php

	$pageID = $this->params['page'];
	$siteID = $this->params['site'];

	if ($pageID && $siteID && is_numeric($siteID) ) {
		$PageMgr = new PageMgr($siteID);
		$page = $PageMgr->getPage($pageID);
		$pageInfo = $page->get();
	}

	$jsQueue = new JSQueue(NULL);
	$extensionMgr = new ExtensionMgr();

	switch ($action) {

		case 'extensionExportData':

			$extensionid = $this->params['extensionId'];
			$uploadwinid = $this->params['uploadWinId'];
			$openerwinid = $this->params['openerWinId'];

			$extensioninfo = $extensionMgr->get( $extensionid );

			if ($extensioninfo['CODE']) {
				$extension = $extensionMgr->getExtension($extensioninfo['CODE']);

				if ($extension) {
					$filelist = array();
					$extension->export( $filelist );

					$koala->callJSFunction( 'Koala.windows[\'wid_'.$uploadwinid.'\'].setCaption', $itext['TXT_EXPORT_FILES'], 'extensionexport' );
					$koala->queueScript( 'Koala.windows[\''.$openerwinid.'\'].tabs.select(0, Koala.windows[\''.$openerwinid.'\'].tabs.params);' );

					$fileIdx = 1;
					foreach($filelist as $file) {
						$koala->callJSFunction( 'Koala.yg_addExtensionExportFile', $uploadwinid, $extensionid, $fileIdx, $file['FILENAME'], $file['MIME'] );
						$fileIdx++;
					}

					// Clear all properties after export
					$properties = $extension->exportProperties->getList();
					foreach($properties as $property) {
						$extension->exportProperties->setValue($property['IDENTIFIER'], '');
					}
				}

			}
			break;

		case 'processImportExtensionData':

			$tempname = $this->params['filePrefix'];
			$filename = $this->params['realFilename'];
			$extensionid = $this->params['extensionId'];
			$extensionid = explode('-', $extensionid);
			$extensionid = $extensionid[0];
			$uploadwinid = $this->params['uploadWinId'];
			$openerwinid = $this->params['openerWinId'];
			$lastfile = $this->params['lastFile'];

			$extensioninfo = $extensionMgr->get( $extensionid );

			if ($extensioninfo['CODE']) {
				$extension = $extensionMgr->getExtension($extensioninfo['CODE']);
				$statusmessage = '';
				if ($extension) {
					$result = $extension->importFile(sys_get_temp_dir().'/'.$tempname, $filename, $statusmessage);

					if ($result === true) {
						$koala->callJSFunction( 'Koala.yg_setImportExtensionStatusOK', $tempname, $uploadwinid );
					} else {
						$koala->callJSFunction( 'Koala.yg_setImportExtensionStatusERROR', $tempname, $uploadwinid, $statusmessage );
					}

					if ($openerwinid && ($lastfile=='true')) {
						// Clear all properties after export
						$properties = $extension->importProperties->getList();
						foreach($properties as $property) {
							$extension->importProperties->setValue($property['IDENTIFIER'], '');
						}
						$koala->queueScript( 'Koala.windows[\'wid_'.$openerwinid.'\'].tabs.select(0, Koala.windows[\'wid_'.$openerwinid.'\'].tabs.params);' );
					}
				}
			}
			break;

		case 'uploadExtensionImportData':

			$filetype = $this->params['type'];
			$filetitle = $this->params['title'];
			if ($_FILES['Filedata']['tmp_name']) {
				$fileTmpName = $_FILES['Filedata']['tmp_name'];
				$filename = $_FILES['Filedata']['name'];
			} else {
				$fileTmpName = fixAndMovePLUploads();
				$filename = $_REQUEST['name'];
			}
			$filesize = filesize($fileTmpName);
			$uploadID = $this->params['uploadID'];
			$uploadwinid = $this->request->parameters['uploadWinId'];
			$extensionid = $this->request->parameters['extensionId'];

			$window_id = $this->params['winID'];

			if (($fileTmpName != '') && ($fileTmpName != 'none')) {
				$koala->queueScript( "window.hadUploadError = false;window.hadUploadErrorMsg = undefined;" );
				$extension = explode('.', $filename);

				$temp_filename = tempnam(sys_get_temp_dir(), 'EXT');
				copy($fileTmpName, $temp_filename);
				$koala->callJSFunction( 'Koala.yg_addImportExtensionFileId', basename($temp_filename), $uploadID, $extensionid, $uploadwinid );
			}
			break;

		case 'removeObjectExtension':

			$extension_id = $this->params['contentblockLnkId'];
			$extension_id = explode('-', $extension_id);
			$extension_id = $extension_id[1];

			$objectInfo = $extensionMgr->get( $extension_id );

			if ($objectInfo["CODE"]) {
				$extension = $extensionMgr->getExtension($objectInfo["CODE"]);

				if ($extension) {
					$jsQueue = new JSQueue(NULL);
					switch($siteID) {
						case 'cblock':
							$removeFunc = 'removeFromCBlock';
							$historyType = HISTORYTYPE_CO;
							$historySuffix = 'CBLOCK';
							$cblock = sCblockMgr()->getCblock($pageID);
							$pageInfo = $cblock->get();
							$object = $cblock;
							break;
						case 'file':
							$removeFunc = 'removeFromFile';
							$historyType = HISTORYTYPE_FILE;
							$fileMgr = sFileMgr();
							$file = $fileMgr->getFile($pageID);
							$pageInfo = $file->get();
							$object = $file;
							break;
						case 'mailing':
							$removeFunc = 'removeFromMailing';
							$historyType = HISTORYTYPE_MAILING;
							$historySuffix = 'MAILING';
							$mailingMgr = new MailingMgr();
							$mailing = $mailingMgr->getMailing($pageID);
							$pageInfo = $mailing->get();
							$object = $mailing;
							break;
						default:
							$removeFunc = 'removeFromPage';
							$historyType = HISTORYTYPE_PAGE;
							$historySuffix = 'PAGE';
							$jsQueue = new JSQueue(NULL, $siteID);
							$object = $page;
							break;
					}

					if ($extension->$removeFunc($pageID, $pageInfo['VERSION'], $siteID) === false) {
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					} else {
						// Add to history
						$object->history->add( $historyType, '', $objectInfo['NAME'], 'TXT_OBJECT_H_EXTREMOVE', $objectInfo['ID'] );

						if ($historySuffix) {
							$jsQueue->add ($pageID, $historyType, 'HIGHLIGHT_'.$historySuffix, sGuiUS(), 'name');
						}
						if ($historySuffix == 'PAGE') {
							$jsQueue->add ($pageID, $historyType, 'OBJECT_DELETE', sGuiUS(), 'extpage', NULL, NULL, $extension_id.'-extpage', 'block', 'page', $pageID.'-'.$siteID);
						} else {
							$jsQueue->add ($pageID, $historyType, 'OBJECT_DELETE', sGuiUS(), 'ext'.$siteID, NULL, NULL, $extension_id.'-ext'.$siteID, 'block', $siteID, $pageID.'-'.$siteID);
						}
					}
				}

			}
			break;

		case 'saveExtensionProperties':
			$data = json_decode( $this->params['allData'], true );

			$extension_id = $data['id'];
			$formfieldid = $data['formfieldid'];
			$value = $data[$formfieldid.'-VALUE01'];
			$objectID = $data['page'];
			$siteID = $data['site'];
			$objectType = $data['objecttype'];

			switch($objectType) {
				case 'page':
				default:
					$ObjectMgr = new PageMgr($siteID);
					$object = $ObjectMgr->getPage($objectID);
					$historyType = HISTORYTYPE_PAGE;
					$jsQueue = new JSQueue(NULL, $siteID);
					$jsQueueType = 'PAGE';
					break;
				case 'cblock':
					$ObjectMgr = sCblockMgr();
					$object = $ObjectMgr->getCblock($objectID);
					$historyType = HISTORYTYPE_CO;
					$jsQueue = new JSQueue(NULL);
					$jsQueueType = 'CBLOCK';
					break;
				case 'file':
					$ObjectMgr = sFileMgr();
					$object = $ObjectMgr->getFile($objectID);
					$latestFinalVersion = $object->getLatestApprovedVersion();
					$object = $ObjectMgr->getFile($objectID, $latestFinalVersion);
					$historyType = HISTORYTYPE_FILE;
					$jsQueue = new JSQueue(NULL);
					break;
				case 'mailing':
					$ObjectMgr = new MailingMgr();
					$object = $ObjectMgr->getMailing($objectID);
					$historyType = HISTORYTYPE_MAILING;
					$jsQueue = new JSQueue(NULL);
					$jsQueueType = 'MAILING';
					break;
			}
			$objectInfo = $object->get();

			$extensioninfo = $extensionMgr->get( $extension_id );
			$extension = $extensionMgr->getExtension($extensioninfo['CODE'], $objectID, $objectInfo['VERSION'], $siteID);

			if ($extension) {
				$props = $extension->propertySettings->getList('LISTORDER');
				foreach($props as $prop_idx => $prop) {
					if ($prop['ID'].$extensioninfo['ID'] == $formfieldid) {
						$value = trim($value);
						for ($j=1;$j<=8;$j++) {
							$data[$formfieldid.'-VALUE0'.$j] = trim($data[$formfieldid.'-VALUE0'.$j]);
						}

						// Special cases
						if ($prop['TYPE']=='PAGE') {
							$value = array(
								'page' => $data[$formfieldid.'-VALUE01'],
								'site' => $data[$formfieldid.'-VALUE02']
							);
						}
						if ($prop['TYPE']=='LINK') {
							$value = $data[$formfieldid.'-VALUE01'];
						}
						if (($prop['TYPE']=='DATE')||($prop['TYPE']=='DATETIME')) {
							if ($prop['TYPE']=='DATETIME') {
								$dateFrac = explode('||', $value);
								$timeFrac = $dateFrac[1];
								$date = explode('.', $dateFrac[0]);
								$time   = explode(':',$timeFrac);
								$hour   = (int)$time[0];
								$minute = (int)$time[1];
								$ampm = explode(' ',$time[1]);
								if ($ampm[1]) {
									$ampm = $ampm[1];
								}
								if ( (strtoupper($ampm) == 'PM') && ($hour != 12) ) {
									$hour += 12;
								}
								if ( (strtoupper($ampm) == 'AM') && ($hour == 12) ) {
									$hour -= 12;
								}
							} else {
								$hour = $minute = 0;
								$date = explode('.', $value);
							}
							$day	= (int)$date[0];
							$month  = (int)$date[1];
							$year   = (int)$date[2];
							$version = substr($autopublish_data_item['version'], 8);
							$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
							$value = $timestamp;
						}
						$extension->properties->setValue($prop['IDENTIFIER'], $value);

						switch($prop['TYPE']) {
							case 'TEXT':
								$object->history->add( $historyType, $extensioninfo['ID'], $value, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_1', $prop['ID'] );
								break;
							case 'TEXTAREA':
								$object->history->add( $historyType, $extensioninfo['ID'], $value, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_2', $prop['ID'] );
								break;
							case 'CHECKBOX':
								if ($value > 0) {
									$object->history->add( $historyType, $extensioninfo['ID'], NULL, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_4_ON', $prop['ID'] );
								} else {
									$object->history->add( $historyType, $extensioninfo['ID'], NULL, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_4_OFF', $prop['ID'] );
								}
								break;
							case 'FILE':
								$object->history->add( $historyType, $extensioninfo['ID'], $value, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_6', $prop['ID'] );
								break;
							case 'LIST':
								$object->history->add( $historyType, $extensioninfo['ID'], $value, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_9', $prop['ID'] );
								break;
							case 'RICHTEXT':
								$object->history->add( $historyType, $extensioninfo['ID'], $value, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_3', $prop['ID'] );
								break;
							case 'LINK':
								$object->history->add( $historyType, $extensioninfo['ID'], $value['href'], 'TXT_OBJECT_H_EXTEDIT_FRMFLD_5', $prop['ID'] );
								break;
							case 'CBLOCK':
								$object->history->add( $historyType, $extensioninfo['ID'], $value, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_7', $prop['ID'] );
								break;
							case 'TAG':
								$object->history->add( $historyType, $extensioninfo['ID'], $value, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_8', $prop['ID'] );
								break;
							case 'PASSWORD':
								$object->history->add( $historyType, $extensioninfo['ID'], $value, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_10', $prop['ID'] );
								break;
							case 'DATE':
								$object->history->add( $historyType, $extensioninfo['ID'], $value, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_11', $prop['ID'] );
								break;
							case 'DATETIME':
								$object->history->add( $historyType, $extensioninfo['ID'], $value, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_12', $prop['ID'] );
								break;
							case 'HEADLINE':
								$object->history->add( $historyType, $extensioninfo['ID'], $value, 'TXT_OBJECT_H_EXTEDIT_FRMFLD_13', $prop['ID'] );
								break;
							case 'PAGE':
								$object->history->add( $historyType, $extensioninfo['ID'], $value['page'].'-'.$value['site'], 'TXT_OBJECT_H_EXTEDIT_FRMFLD_15', $prop['ID'] );
								break;
						}
					}
				}
				$koala->callJSFunction( 'Koala.yg_fade', 'formfield', $formfieldid.'-formfield', 'value' );

				if ($jsQueueType) {
					$jsQueue->add ($objectID, $historyType, 'HIGHLIGHT_'.$jsQueueType, sGuiUS(), 'name');
				}
			}
			break;

		case 'addObjectExtension':

			$extension = $this->params['extensionId'];
			$parentwindow = $this->params['openerRefID'];
			$refresh = $this->params['refresh'];
			$target_id = $this->params['targetId'];
			$target_pos = $this->params['targetPosition'];

			$objectInfo = $extensionMgr->get( $extension );

			if ($objectInfo["CODE"]) {
				$extension = $extensionMgr->getExtension($objectInfo["CODE"]);

				if ($extension) {
					$jsQueue = new JSQueue(NULL);
					switch($siteID) {
						case 'cblock':
							$addFunc = 'addToCBlock';
							$isUsedFunc = 'usedByCblock';
							$historySuffix = 'CBLOCK';
							$historyType = HISTORYTYPE_CO;
							$extensionType = EXTENSION_CBLOCK;
							$cblock = sCblockMgr()->getCblock($pageID);
							$pageInfo = $cblock->get();
							$object = $cblock;
							break;
						case 'file':
							$addFunc = 'addToFile';
							$isUsedFunc = 'usedByFile';
							$historyType = HISTORYTYPE_FILE;
							$extensionType = EXTENSION_FILE;
							$fileMgr = sFileMgr();
							$file = $fileMgr->getFile($pageID);
							$pageInfo = $file->get();
							$object = $file;
							break;
						case 'mailing':
							$addFunc = 'addToMailing';
							$isUsedFunc = 'usedByMailing';
							$historySuffix = 'MAILING';
							$historyType = HISTORYTYPE_MAILING;
							$extensionType = EXTENSION_MAILING;
							$mailingMgr = new MailingMgr();
							$mailing = $mailingMgr->getMailing($pageID);
							$pageInfo = $mailing->get();
							$object = $mailing;
							break;
						default:
							$addFunc = 'addToPage';
							$isUsedFunc = 'usedByPage';
							$historySuffix = 'PAGE';
							$historyType = HISTORYTYPE_PAGE;
							$extensionType = EXTENSION_PAGE;
							$jsQueue = new JSQueue(NULL, $siteID);
							$object = $page;
							break;
					}

					if ($extension->$addFunc($pageID, $pageInfo['VERSION'], $siteID) === false) {
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					} else {
						// Add to history
						$object->history->add( $historyType, '', $objectInfo['NAME'], 'TXT_OBJECT_H_EXTADD', $objectInfo['ID'] );

						/* Re-get all extensions in object */
						$all_page_extensions = $extensionMgr->getList( $extensionType, true );

						$used_extensions_ids = array();
						foreach($all_page_extensions as $all_page_extension) {
							$extension = $extensionMgr->getExtension($all_page_extension['CODE']);

							if( $extension && $extension->$isUsedFunc($pageID, $pageInfo['VERSION'], $siteID) === true ) {
								array_push($used_extensions_ids, $all_page_extension['ID']);
							}
						}

						$dta_cnt = 0;
						$js_array = '[ ';
						foreach ($used_extensions_ids as $extension_list_item) {
							$dta_cnt++;
							$js_array .= "[ '".$pageID."', '".(($itext['TXT_EXTENSIONS']!='')?($itext['TXT_EXTENSIONS']):('$TXT_EXTENSIONS'))."', '0', 'extension-".$extension_list_item."'  ], ";
						}
						$js_array = substr($js_array, 0, strlen($js_array)-2);
						$js_array .= ' ]';

						if ($js_array == ' ]') {
							$js_array = '[]';
						}
						if ($parentwindow != '') {
							$koala->queueScript( "Koala.windows['wid_".$parentwindow."'].getContentareaDataFuncs( ".$js_array.", true );" );
						}
						$koala->queueScript( "if ($('clone%%cblock__')) { $('clone%%cblock__').remove(); }" );

						if ($historySuffix) {
							$jsQueue->add ($pageID, $historyType, 'HIGHLIGHT_'.$historySuffix, sGuiUS(), 'name');
						}
					}
				}
			}
			break;

		case 'setExtensionProperties':
			$extension_id = $this->params['extension'];
			$wid = $this->params['wid'];
			$propertiesData = $this->params['propertiesData'];

			$objectInfo = $extensionMgr->get($extension_id);

			if ($objectInfo["CODE"]) {
				$extension = $extensionMgr->getExtension($objectInfo["CODE"]);
				if ($extension) {
					switch ($objectInfo['TYPE']) {
						case EXTENSION_IMPORT:
							if ($this->params['fromDataAdmin']) {
								$propertyObject = $extension->importProperties;
							} else {
								$propertyObject = $extension->extensionProperties;
							}
							break;
						case EXTENSION_EXPORT:
							if ($this->params['fromDataAdmin']) {
								$propertyObject = $extension->exportProperties;
							} else {
								$propertyObject = $extension->extensionProperties;
							}
							break;
						default:
							$propertyObject = $extension->extensionProperties;
							break;
					}
					$properties = $propertyObject->getList();
					foreach($properties as $property) {
						if (isset($propertiesData['prop_'.strtolower($property['IDENTIFIER'])])) {
							// Special handling for dates
							if (($property['TYPE']=='DATE')||($property['TYPE']=='DATETIME')) {
								if (strlen($propertiesData['prop_'.strtolower($property['IDENTIFIER'])]) > 0) {
									if ($property['TYPE']=='DATETIME') {
										$dateFrac = $propertiesData['prop_'.strtolower($property['IDENTIFIER'])];
										$timeFrac = $propertiesData['prop_'.strtolower($property['IDENTIFIER']).'2'];
										$date = explode('.', $dateFrac);
										$time   = explode(':',$timeFrac);
										$hour   = (int)$time[0];
										$minute = (int)$time[1];
										$ampm = explode(' ',$time[1]);
										if ($ampm[1]) {
											$ampm = $ampm[1];
										}
										if ( (strtoupper($ampm) == 'PM') && ($hour != 12) ) {
											$hour += 12;
										}
										if ( (strtoupper($ampm) == 'AM') && ($hour == 12) ) {
											$hour -= 12;
										}
									} else {
										$hour = $minute = 0;
										$date = explode('.', $propertiesData['prop_'.strtolower($property['IDENTIFIER'])]);
									}
									$day	= (int)$date[0];
									$month  = (int)$date[1];
									$year   = (int)$date[2];
									$version = substr($autopublish_data_item['version'],8);
									$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
									$value = TSfromLocalTS($timestamp);
								}
							} else {
								$value = $propertiesData['prop_'.strtolower($property['IDENTIFIER'])];
							}
							$propertyObject->setValue($property['IDENTIFIER'], $value);
						}
					}
					$koala->queueScript("Koala.yg_fadeFields(\$('".$wid."'), '.changed');");
				}
			}
			break;

		case 'installExtension':
			$extension = $this->params['extension'];
			if ($extension) {
				$wid = $this->params['wid'];
				$objectInfo = $extensionMgr->get($extension);

				if ($objectInfo["CODE"]) {
					$extension = $extensionMgr->getExtension($objectInfo["CODE"]);
					if ($extension) {
						if (!$objectInfo["INSTALLED"]) {
							if ($extension->install() === false) {
								$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
							}
						}

						$koala->queueScript( 'Koala.yg_setExtensionInstalled( \''.$objectInfo['ID'].'\', Koala.windows[\''.$wid.'\'].boundWindow, true );' );
						$koala->queueScript( 'Koala.windows[\''.$wid.'\'].tabs.select(0,Koala.windows[\''.$wid.'\'].tabs.params);' );
					}
				}
			}
			break;

		case 'uninstallExtension':
			$extension = $this->params['extension'];
			$wid = $this->params['wid'];

			$confirmed = $this->params['confirmed'];
			$positive = $this->params['positive'];

			if ($confirmed != 'true') {
				$parameters = array(
					'extension'	=> $extension,
					'wid'	=> $wid
				);
				$koala->callJSFunction( 'Koala.yg_confirm',
					($itext['TXT_EXTENSION_DEL']!='')?($itext['TXT_EXTENSION_DEL']):('$TXT_EXTENSION_DEL'),
					($itext['TXT_EXTENSION_DEL_TEXT']!='')?($itext['TXT_EXTENSION_DEL_TEXT']):('$TXT_EXTENSION_DEL_TEXT'),
					$action, json_encode($parameters)
				);
			} else if (($confirmed == 'true') && ($positive == 'true')) {
				$objectInfo = $extensionMgr->get($extension);

				if ($objectInfo["CODE"]) {
					$extension = $extensionMgr->getExtension($objectInfo["CODE"]);
					if ($extension) {
						if ($objectInfo["INSTALLED"]) {
							if ($extension->uninstall() === false) {
								$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
							}
						}
						switch ($objectInfo["TYPE"]) {
							case EXTENSION_PAGE:
								$frontendType = 'page';
								break;
							case EXTENSION_IMPORT:
								$frontendType = 'import';
								break;
							case EXTENSION_EXPORT:
								$frontendType = 'export';
								break;
						}

						$koala->queueScript( 'Koala.yg_setExtensionInstalled( \''.$objectInfo['ID'].'\', \''.$wid.'\', false );' );
						$koala->queueScript( 'Koala.windows[Koala.windows[\''.$wid.'\'].boundWindow].tabs.select(0, Koala.windows[Koala.windows[\''.$wid.'\'].boundWindow].tabs.params)' );
					}
				}
			}
			break;

		case 'extensionSelectNode':
			$node = $this->params['node'];
			$wid = $this->params['wid'];
			$objectInfo = $extensionMgr->get( $node );

			// Finally enable/Disable them
			if ($objectInfo['INSTALLED']) {
				$koala->callJSFunction( 'Koala.yg_enable', 'tree_btn_delete', 'btn-'.$wid, 'tree_btn' );
			} else {
				$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_delete', 'btn-'.$wid, 'tree_btn' );
			}
			break;
	}

?>