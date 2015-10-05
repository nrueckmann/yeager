<?php

	\framework\import('org.phpmailer.phpmailer');

	$jsQueue = new JSQueue( NULL );
	$siteMgr = new Sites();

	switch ($action) {

			case 'shredderObject':

				$objectType = $this->params['yg_type'];
				$objectYGID = $this->params['yg_id'];
				$confirmed = $this->params['confirmed'];
				$positive = $this->params['positive'];
				$winID = $this->params['winID'];

				$currObject = explode('-', $objectYGID);
				$currObject = $currObject[0];

				switch($objectType) {
					case 'page':
						$currSite = explode('-', $objectYGID);
						$currSite = $currSite[1];
						$pageMgr = new PageMgr($currSite);

						// Check if object has subnodes
						$subnodes = $pageMgr->getSubnodes($currObject, false);
						if (count($subnodes) > 0) {
							// Move subnodes to parent
							$currPage = $pageMgr->getPage($currObject);
							$pageInfo = $currPage->get();
							$currentLevel = $pageInfo['LEVEL'];
							$currentParent = $pageInfo['PARENT'];
							foreach($subnodes as $subnode) {
								if ($subnode['LEVEL'] == ($currentLevel+1)) {
									if (!$pageMgr->tree->moveTo($subnode['ID'], $currentParent)) {
										$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
									}
								}
							}
						}
						if (!in_array($currObject, $pageMgr->remove($currObject))) {
							$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
						}
						break;
					case 'cblock':
						// Check if object has subnodes
						$subnodes = sCblockMgr()->getSubnodes($currObject);

						// Check if contentblock is still used
						$stillInUse = false;
						$cb = sCblockMgr()->getCblock($currObject);
						$linkedObjects = $cb->getLinkedPages();
						if (count($linkedObjects) > 0) {
							$stillInUse = true;
						}
						$linkedObjects = $cb->getLinkedMailings();
						if (count($linkedObjects) > 0) {
							$stillInUse = true;
						}

						if (!$stillInUse && (count($subnodes) > 0)) {
							foreach($subnodes as $subnode) {
								$currCBlock = sCblockMgr()->getCblock($subnode['ID']);
								$linkedObjects = $currCBlock->getLinkedPages();
								if (count($linkedObjects) > 0) {
									$stillInUse = true;
								}
								$linkedObjects = $currCBlock->getLinkedMailings();
								if (count($linkedObjects) > 0) {
									$stillInUse = true;
								}
							}
						}

						if ($stillInUse) {
							// Still used!
							if ($confirmed != 'true') {
								$parameters = array(
									'yg_type'	=> $objectType,
									'yg_id'	=> $objectYGID,
									'winID'	=> $winID
								);
								$koala->callJSFunction( 'Koala.yg_confirm',
									($itext['TXT_DELETE_USED_TEMPLATE_TITLE']!='')?($itext['TXT_DELETE_USED_TEMPLATE_TITLE']):('$TXT_DELETE_USED_TEMPLATE_TITLE'),
									($itext['TXT_DELETE_USED_TEMPLATE']!='')?($itext['TXT_DELETE_USED_TEMPLATE']):('$TXT_DELETE_USED_TEMPLATE'),
									$action, json_encode($parameters)
								);
							} else if (($confirmed == 'true') && ($positive == 'true')) {
								if (count($subnodes) > 0) {
									// Move subnodes to parent
									$currCBlock = sCblockMgr()->getCblock($currObject);
									$cblockInfo = $currCBlock->get();
									$currentLevel = $cblockInfo['LEVEL'];
									$currentParent = $cblockInfo['PARENT'];
									foreach($subnodes as $subnode) {
										if ($subnode['LEVEL'] == ($currentLevel+1)) {
											if (!sCblockMgr()->tree->moveTo($subnode['ID'], $currentParent)) {
												$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
											}
										}
									}
								}
								if (!in_array($currObject, sCblockMgr()->remove($currObject))) {
									$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
								}
							}
						} else {
							// Not used, delete contentblock
							if (count($subnodes) > 0) {
								// Move subnodes to parent
								$currCBlock = sCblockMgr()->getCblock($currObject);
								$cblockInfo = $currCBlock->get();
								$currentLevel = $cblockInfo['LEVEL'];
								$currentParent = $cblockInfo['PARENT'];
								foreach($subnodes as $subnode) {
									if ($subnode['LEVEL'] == ($currentLevel+1)) {
										if (!sCblockMgr()->tree->moveTo($subnode['ID'], $currentParent)) {
											$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
										}
									}
								}
							}
							if (!in_array($currObject, sCblockMgr()->remove($currObject))) {
								$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
							}
						}
						break;
					case 'file':
						// Check if object has subnodes
						$subnodes = sFileMgr()->getSubnodes($currObject);
						if (count($subnodes) > 0) {
							// Move subnodes to parent
							$currFile = sFileMgr()->getFile($currObject);
							$fileInfo = $currFile->get();
							$currentLevel = $fileInfo['LEVEL'];
							$currentParent = $fileInfo['PARENT'];
							foreach($subnodes as $subnode) {
								if ($subnode['LEVEL'] == ($currentLevel+1)) {
									if (!sFileMgr()->tree->moveTo($subnode['ID'], $currentParent)) {
										$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
									}
								}
							}
						}
						if (!in_array($currObject, sFileMgr()->remove($currObject, $this->filesdir))) {
							$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
						}
						break;
				}

				$koala->queueScript( 'Koala.windows[\''.$winID.'\'].tabs.select($K.windows[\''.$winID.'\'].tabs.selected, {refresh: 1});' );
				break;

			case 'aquireLock':
				$currentObjects = json_decode($this->params['currentObjects'], true);

				$unlockedObjects = array();

				foreach($currentObjects as $currentObjectItem) {
					$objectData = explode('-', $currentObjectItem['winYgId']);

					if ($objectData[0] == 'trash') break;

					if ($objectData[0]) {
						switch($currentObjectItem['objectType']) {
							case 'file':
							case 'filefolder':
								$currentObject = sFileMgr()->getFile($objectData[0]);
								break;

							case 'page':
								$objectMgr = new PageMgr($objectData[1]);
								$currentObject = $objectMgr->getPage($objectData[0]);
								break;

							case 'cblock':
								$currentObject = sCblockMgr()->getCblock($objectData[0]);
								break;

							case 'mailing':
								$currentObject = sMailingMgr()->getMailing($objectData[0]);
								break;
						}

						// Check if I already have a lock on this object
						$lockToken = sGuiUS().'_'.$currentObjectItem['winID'];
						$lockStatus = $currentObject->getLock();
						if ($lockStatus['TOKEN'] == $lockToken) {
							// I have already locked this object, so unlock (and relock in next step)
							$currentObject->releaseLock($lockToken);
						}
						// I have to try to aquire a lock on this object
						$lockedSucceeded = $currentObject->acquireLock($lockToken);

						// For mailings, additionally check if a mailing is in progress
						if ($currentObjectItem['objectType'] == 'mailing') {
							$mailingStatus = $currentObject->getStatus();
							if ($mailingStatus['STATUS'] == 'INPROGRESS') {
								$lockedSucceeded = false;
							}
						}
						if ($lockedSucceeded) {
							// Unlock in GUI for current user
							$koala->queueScript( "Koala.changeWindowLockState( '".$currentObjectItem['winID']."', false);" );
						} else {
							// Lock in GUI for current user
							$koala->queueScript( "Koala.changeWindowLockState( '".$currentObjectItem['winID']."', true);" );
						}
					}

				}
				break;

			case 'releaseLock':
				$winID = $this->params['winID'];
				$objectType = $this->params['objectType'];
				$objectYgId = $this->params['objectYgId'];

				$lockToken = sGuiUS().'_'.$winID;

				switch($objectType) {
					case 'mailing':
						$mailingMgr = new MailingMgr();
						$lockedObjects = $mailingMgr->getLocksByToken($lockToken);
						foreach($lockedObjects as $lockedObject) {
							$currentObject = sMailingMgr()->getMailing($lockedObject['OBJECTID']);
							$currentObject->releaseLock($lockedObject['TOKEN']);
						}
						break;
					case 'file':
					case 'filefolder':
						$lockedObjects = sFileMgr()->getLocksByToken($lockToken);
						foreach($lockedObjects as $lockedObject) {
							$currentObject = sFileMgr()->getFile($lockedObject['OBJECTID']);
							$currentObject->releaseLock($lockedObject['TOKEN']);
						}
						break;
					case 'page':
						$siteID = explode('-', $objectYgId);
						$siteID = $siteID[1];
						if ($siteID) {
							$pageMgr = new PageMgr($siteID);
							$lockedObjects = $pageMgr->getLocksByToken($lockToken);
							foreach($lockedObjects as $lockedObject) {
								$currentObject = $pageMgr->getPage($lockedObject['OBJECTID']);
								$currentObject->releaseLock($lockedObject['TOKEN']);
							}
						}
						break;
					case 'cblock':
						$cblockMgr = sCblockMgr();
						$lockedObjects = $cblockMgr->getLocksByToken($lockToken);
						foreach($lockedObjects as $lockedObject) {
							$currentObject = $cblockMgr->getCblock($lockedObject['OBJECTID']);
							$currentObject->releaseLock($lockedObject['TOKEN']);
						}
						break;
				}

				break;

			case 'ping':
				break;

			case 'saveProperties':

				$widprefix = $this->params['wid'];
				$property_type = $this->params[ $widprefix.'_prop_objecttype' ];
				$properties_ids = $this->params[ $widprefix.'_properties_'.$property_type.'_ids[]'];
				$properties_ids = explode(',',$properties_ids);
				$properties_del_tsuffixes = $this->params[ $widprefix.'_properties_'.$property_type.'_del_tsuffixes[]'];
				$properties_del_tsuffixes = explode(',',$properties_del_tsuffixes);

				switch($property_type) {
					default:
					case 'page':
						/* Intentionally left blank here (handling of pages is further down) */
						break;
					case 'cblock':
						$properties_object = sCblockMgr()->properties;
						break;
					case 'file':
						$properties_object = sFileMgr()->properties;
						break;
					case 'user':
						$properties_object = sUserMgr()->properties;
						break;
				}

				function saveProperties($properties_object, $properties_ids, $properties_del_tsuffixes, $params, $widprefix, $property_type) {
					foreach($properties_del_tsuffixes as $properties_del_tsuffix) {
						if (strlen($properties_del_tsuffix) > 0) {
							$properties_object->remove($properties_del_tsuffix);
						}
					}

					// Get all properties and set initial order-offset (to respect READONLY properties)
					$orderid = 1;
					$old_props = $properties_object->getList();
					foreach ($old_props as $old_prop) {
						if ($old_prop['READONLY']==1) {
							$orderid++;
						}
					}

					$properties_info = array();
					foreach($properties_ids as $properties_id) {
						$properties_info_item['ID'] = $properties_id;
						$properties_info_item['TYPE'] = $params[ $widprefix.'_prop_'.$property_type.'_'.$properties_id.'_type'];
						$properties_info_item['NAME'] = $params[ $widprefix.'_prop_'.$property_type.'_'.$properties_id.'_name'];
						$properties_info_item['IDENTIFIER'] = $params[ $widprefix.'_prop_'.$property_type.'_'.$properties_id.'_oldtsuffix'];
						$properties_info_item['NEWIDENTIFIER'] = $params[ $widprefix.'_prop_'.$property_type.'_'.$properties_id.'_tsuffix'];
						if ($properties_info_item['TYPE'] == 'LIST') {
							$properties_info_item['LVALUES'] = $params[ $widprefix.'_fld_'.$properties_id.'-ENTRIES[]'];
						}
						$properties_info_item['LISTORDER'] = $orderid++;
						array_push($properties_info, $properties_info_item);
					}

					foreach($properties_info as $properties_info_item) {
						if (strpos($properties_info_item['ID'], '__NEW_ID_')===0) {
							$properties_object->add($properties_info_item['NAME'], $properties_info_item['NEWIDENTIFIER'], $properties_info_item['TYPE'], 1, $properties_info_item['LISTORDER']);
						} else {
							if ($properties_info_item['IDENTIFIER'] != $properties_info_item['NEWIDENTIFIER']) {
								$properties_object->setIdentifier($properties_info_item['IDENTIFIER'], $properties_info_item['NEWIDENTIFIER']);
							} else {
								$properties_info_item['IDENTIFIER'] = $properties_info_item['NEWIDENTIFIER'];
							}
							$properties_object->setOrder($properties_info_item['NEWIDENTIFIER'], $properties_info_item['LISTORDER']);
							$properties_object->setName($properties_info_item['NEWIDENTIFIER'], $properties_info_item['NAME']);
						}
						if ($properties_info_item['TYPE'] == 'LIST') {
							$listentries = $properties_info_item['LVALUES'];
							$listentries = explode(',', $listentries);

							$listorder = 1;
							$properties_object->clearListValues($properties_info_item['NEWIDENTIFIER']);
							foreach ($listentries as $listentry) {
								if ($listentry != '__TITLE__') {
									$properties_object->addListValue($properties_info_item['NEWIDENTIFIER'], $listentry, $listorder++);
								}
							}
						}
					}
				}

				if ($property_type == "page") {
					$sites = $siteMgr->getList();
					for ($s = 0; $s < count($sites); $s++) {
						$pageMgr = new PageMgr($sites[$s]["ID"]);
						$properties_object = $pageMgr->properties;
						saveProperties($properties_object, $properties_ids, $properties_del_tsuffixes, $this->params, $widprefix, $property_type);
					}
				} else {
					saveProperties($properties_object, $properties_ids, $properties_del_tsuffixes, $this->params, $widprefix, $property_type);
				}

				// Reload Tab
				switch($property_type) {
					case 'page':
						$koala->queueScript('Koala.windows[\''.$widprefix.'\'].tabs.select(0,Koala.windows[\''.$widprefix.'\'].tabs.params);');
						break;
					case 'cblock':
						$koala->queueScript('Koala.windows[\''.$widprefix.'\'].tabs.select(1,Koala.windows[\''.$widprefix.'\'].tabs.params);');
						break;
					case 'file':
						$koala->queueScript('Koala.windows[\''.$widprefix.'\'].tabs.select(2,Koala.windows[\''.$widprefix.'\'].tabs.params);');
						break;
					case 'user':
						$koala->queueScript('Koala.windows[\''.$widprefix.'\'].tabs.select(3,Koala.windows[\''.$widprefix.'\'].tabs.params);');
						break;
				}
				break;

			case 'setObjectProperty':

				// Cannot use new style of parameters here (function is called by a custom-attribute event - and therefore does not know about named parameters)
				// Get property name
				$property = $data[1]['yg_property'];

				// Get window-ID
				$winID = $data[1]['winID'];

				// Split ObjectID and SiteID
				$data = explode('-', $this->reponsedata[$property]->yg_id);

				$obj_type = $this->reponsedata[$property]->type;

				switch($obj_type) {

					case 'mailing':
						$object = $data[0];

						$mailingMgr = new MailingMgr();
						$jsQueue = new JSQueue(NULL);

						$mailing = $mailingMgr->getMailing($object);
						$objectInfo = $mailing->get();

						// Get old property value & check if change is needed
						$oldvalue = $mailing->properties->getValueInternal(strtoupper($property));

						$value = str_replace("\r", '', str_replace("\n", '\n', $this->reponsedata[$property]->value));
						if ($value == ' ') $value = '';

						if ($oldvalue == $value) {
							// No update needed, henceforth break
							break;
						}

						// Check for empty name
						if (($property=='name') && (trim($value) == '')) {
							$jsQueue->add ($object, HISTORYTYPE_MAILING, 'OBJECT_CHANGE', sGuiUS(), 'mailing', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $oldvalue);
							$jsQueue->add ($object, HISTORYTYPE_MAILING, 'REFRESH_WINDOW', sGuiUS(), 'name');

							$koala->alert($itext['TXT_CANT_CHANGE_PAGETITLE_TO_EMPTY_VALUE']);
							break;
						}

						// Check if property is a readonly property
						$propertyInfo = $mailing->properties->getProperty(strtoupper($property));
						$isReadOnlyProperty = $propertyInfo[0]['READONLY'];

						// Special handling for dates
						if (($propertyInfo[0]['TYPE']=='DATE')||($propertyInfo[0]['TYPE']=='DATETIME')) {
							if ($propertyInfo[0]['TYPE']=='DATETIME') {
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
							$timestamp = TSfromLocalTS($timestamp);
							$value = $timestamp;
						}

						// Special handling for files
						if ($propertyInfo[0]['TYPE']=='FILE') {
							if (substr($value, 0, 7) == '/image/') {
								$realValue = sFileMgr()->getFileIdByPname( substr($value, 7) );
								if ($realValue) $value = $realValue;
							}
						}

						// Special handling for links
						if ($propertyInfo[0]['TYPE']=='LINK') {
							$value = trim(prettifyUrl($value));
							$result = checkLinkInternalExternal($value);
							if ($result['TYPE']!='external') {
								$value = createSpecialURLfromShortURL($value);
							}
						}

						// Special handling for textareas
						if ($propertyInfo[0]['TYPE']=='TEXTAREA') {
							$value = str_replace('\n', "\n", $value);
						}

						// Special handling for wysiwyg
						if ($propertyInfo[0]['TYPE']=='RICHTEXT') {
							$value = str_replace('\n', "\n", $value);
							$value = convertShortURLsToSpecialURLs($value);
						}

						// Set the new property
						if ($mailing->properties->setValue(strtoupper($property), $value) === false) {
							$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
						} else {
							$property_data = $mailing->properties->getProperty(strtoupper($property));

							// Do not use Koala::change for some elements
							if ($property_data[0]['TYPE']=='LINK') {
								/* Do nothing, everything already done from frontend */
							} elseif ( ($property_data[0]['TYPE']!='CHECKBOX') &&
								 ($property_data[0]['TYPE']!='RICHTEXT') &&
								 ($property_data[0]['TYPE']!='TAG') &&
								 ($property_data[0]['TYPE']!='CBLOCK') &&
								 ($property_data[0]['TYPE']!='PAGE') &&
								 ($property_data[0]['TYPE']!='DATE') &&
								 ($property_data[0]['TYPE']!='DATETIME') ) {
								$value = str_replace("\n", '\n', $value);
								$jsQueue->add ($object, HISTORYTYPE_MAILING, 'OBJECT_CHANGE', sGuiUS(), 'mailing', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $value);
							}

							// For Lists: Check if empty title was set
							if (($property_data[0]['TYPE']=='LIST') && (trim($value) == $itext['TXT_NO_SELECTION'])) {
								$mailing->properties->setValue(strtoupper($property), '');
							}

							// Special case for name
							if ($property=='name') {
								if ($objectInfo['PNAME'] == NULL) {
									$PName = $mailing->calcPName();
									$mailing->setPName($PName);
									$jsQueue->add ($object, HISTORYTYPE_MAILING, 'OBJECT_CHANGE', sGuiUS(), 'mailing', NULL, NULL, $object.'-mailing', 'pname', $PName);
									$jsQueue->add ($object, HISTORYTYPE_MAILING, 'OBJECT_CHANGEPNAME', sGuiUS(), 'mailing', NULL, NULL, $object.'-mailing', 'name', $PName);
									$jsQueue->add ($object, HISTORYTYPE_MAILING, 'REFRESH_WINDOW', sGuiUS(), 'pname');
								}

								$jsQueue->add ($object, HISTORYTYPE_MAILING, 'REFRESH_WINDOW', sGuiUS(), 'name');
							}

							// Add to history
							if ($property_data[0]['TYPE']=='CHECKBOX') {
								if ($value==1) {
									$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP_CHECKON');
								} else {
									$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP_CHECKOFF');
								}
							} else if ($property_data[0]['TYPE']=='FILE') {
								if ($value) {
									$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP_FILE');
								} else {
									$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP_FILE_REMOVED');
								}
							} else if ($property_data[0]['TYPE']=='RICHTEXT') {
								$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP_RICHTEXT');
							} else if ($property_data[0]['TYPE']=='TAG') {
								$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP_TAG');
							} else if ($property_data[0]['TYPE']=='CBLOCK') {
								$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP_CBLOCK');
							} else if ($property_data[0]['TYPE']=='LINK') {
								$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP_LINK');
							} else if ($property_data[0]['TYPE']=='PAGE') {
								$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value['page'], 'TXT_MAILING_H_PROP_PAGE', $value['site']);
							} else if ($property_data[0]['TYPE']=='DATE') {
								$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP_DATE');
							} else if ($property_data[0]['TYPE']=='DATETIME') {
								$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP_DATETIME');
							} else if ($property_data[0]['TYPE']=='PASSWORD') {
								$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP_PASSWORD');
							} else {
								if ($isReadOnlyProperty) {
									$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($propertyInfo[0]['NAME']), $value, 'TXT_MAILING_H_PROP');
								} else {
									$mailing->history->add (HISTORYTYPE_MAILING, strtoupper($property), $value, 'TXT_MAILING_H_PROP');
								}
							}

							//$mailing->setStatus('UNSENT');
							$jsQueue->add ($object, HISTORYTYPE_MAILING, 'HIGHLIGHT_MAILING', sGuiUS(), 'name');
						}
						break;

					case 'extension':
						$extensionMgr = new ExtensionMgr();
						$value = str_replace("\r", '', str_replace("\n", '\n', $this->reponsedata[$property]->value));
						$extensionInfo = $extensionMgr->get((int)$data[0]);
						$extension = $extensionMgr->getExtension($extensionInfo["CODE"]);
						if ($extension) {
							switch($data[1]) {
								default:
								case 'extensions':
									$properties = $extension->extensionProperties;
									break;
								case 'data':
									switch($extensionInfo['TYPE']) {
										case EXTENSION_IMPORT:
											// Import
											$properties = $extension->importProperties;;
											break;
										case EXTENSION_EXPORT:
											// Export
											$properties = $extension->exportProperties;
											break;
									}
									break;
							}

							$propertyList = $properties->getList();
							foreach($propertyList as $propertyItem) {
								if (strtolower($propertyItem['IDENTIFIER']) == $property) {
									// Special handling for dates
									if (($propertyItem['TYPE']=='DATE')||($propertyItem['TYPE']=='DATETIME')) {
										if ($propertyItem['TYPE']=='DATETIME') {
											$dateArray = explode('||', $value);
											$dateFrac = $dateArray[0];
											$timeFrac = $dateArray[1];
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
											$date = explode('.', $value);
										}
										$day	= (int)$date[0];
										$month  = (int)$date[1];
										$year   = (int)$date[2];

										$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
										$timestamp = TSfromLocalTS($timestamp);
										$value = $timestamp;
									}
									// Special handling for textareas
									if ($propertyInfo[0]['TYPE']=='TEXTAREA') {
										$value = str_replace('\n', "\n", $value);
									}
									//$properties->setValue($propertyItem['IDENTIFIER'], $value);
								}

							}
						}
						break;

					case 'user':
						// Get the user
						$user = new User($data[0]);
						$objectInfo = $user->get();

						// Get old property value & check if change is needed
						if (strtoupper($property) == 'PASSWORD') {
							$oldvalue = $objectInfo['PASSWORD'];
						} elseif (strtoupper($property) == 'LANGUAGE') {
							$oldvalue = $user->getLanguage();
						} else {
							$oldvalue = $user->properties->getValueInternal(strtoupper($property));
						}

						$value = str_replace("\r", '', str_replace("\n", '\n', $this->reponsedata[$property]->value));
						if ($value == ' ') $value = '';

						if ($oldvalue == $value) {
							// No update needed, henceforth break
							break;
						}

						// Special case for email (check for uniqueness)
						if ($property=='email') {
							$tmpUserinfo = sUserMgr()->getByEmail($value, true);
							if ($tmpUserinfo) {
								$koala->alert( $itext['TXT_EMAIL_ALREADY_USED_CHOOSE_ANOTHER'] );
								break;
							}
						}

						// Special case for password (not a real property)
						if ($property=='password') {
							$user->setPassword($value);

							// Check if user is current user and re-validate if needed
							if ($data[0] == sUserMgr()->getCurrentUserID()) {
								$this->session->setPSessionVar('password', $value);
							}
							$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, $objectInfo['ID'].'-user', $property, $value);
							break;
						}

						// Special case for language (not a real property)
						if ($property=='language') {
							$user->setLanguage($value);
							break;
						}

						// Check if property is a readonly property
						$propertyInfo = $user->properties->getProperty(strtoupper($property));
						$isReadOnlyProperty = $propertyInfo[0]['READONLY'];

						// Special handling for dates
						if (($propertyInfo[0]['TYPE']=='DATE')||($propertyInfo[0]['TYPE']=='DATETIME')) {
							if ($propertyInfo[0]['TYPE']=='DATETIME') {
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
							$day    = (int)$date[0];
							$month  = (int)$date[1];
							$year   = (int)$date[2];

							$version = substr($autopublish_data_item['version'],8);
							$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
							$timestamp = TSfromLocalTS($timestamp);
							$value = $timestamp;
						}

						// Special handling for files
						if ($propertyInfo[0]['TYPE']=='FILE') {
							if (substr($value, 0, 7) == '/image/') {
								$realValue = sFileMgr()->getFileIdByPname( substr($value, 7) );
								if ($realValue) $value = $realValue;
							}
						}

						// Special handling for links
						if ($propertyInfo[0]['TYPE']=='LINK') {
							$value = trim(prettifyUrl($value));
							$result = checkLinkInternalExternal($value);
							if ($result['TYPE']!='external') {
								$value = createSpecialURLfromShortURL($value);
							}
						}

						// Special handling for textareas
						if ($propertyInfo[0]['TYPE']=='TEXTAREA') {
							$value = str_replace('\n', "\n", $value);
						}

						// Special handling for wysiwyg
						if ($propertyInfo[0]['TYPE']=='RICHTEXT') {
							$value = str_replace('\n', "\n", $value);
							$value = convertShortURLsToSpecialURLs($value);
						}

						// Set the new property
						if ( $user->properties->setValue(strtoupper($property), $value) === false ) {
							$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
						} else {
							$property_data = $user->properties->getProperty( strtoupper($property));

							$specialupdate = false;
							// Do not use Koala::change for some elements
							if ($property_data[0]['TYPE']=='LINK') {
								/* Do nothing, everything already done from frontend */
							} elseif ( ($property_data[0]['IDENTIFIER']=='TIMEZONE') ||
									   ($property_data[0]['IDENTIFIER']=='DATEFORMAT') ||
									   ($property_data[0]['IDENTIFIER']=='TIMEFORMAT') ||
									   ($property_data[0]['IDENTIFIER']=='WEEKSTART') ) {
								/* Do nothing, everything already done from frontend */
								$specialupdate = true;
								$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'UNHIGHLIGHT', sGuiUS(), 'user', NULL, NULL, $objectInfo['ID'].'-user', $property, $value);
							} elseif ( ($property_data[0]['TYPE']!='CHECKBOX') &&
								 ($property_data[0]['TYPE']!='RICHTEXT') &&
								 ($property_data[0]['TYPE']!='TAG') &&
								 ($property_data[0]['TYPE']!='CBLOCK') &&
								 ($property_data[0]['TYPE']!='PAGE') &&
								 ($property_data[0]['TYPE']!='DATE') &&
								 ($property_data[0]['TYPE']!='DATETIME') ) {
								$value = str_replace("\n", '\n', $value);
								$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $value);
							}

							// Special case for name
							if ( ($property=='firstname') || ($property=='lastname') ) {
								$tmp_firstname = $user->properties->getValueInternal('FIRSTNAME');
								$tmp_lastname = $user->properties->getValueInternal('LASTNAME');

								if ($property=='firstname') {
									$tmp_firstname = $value;
									$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, $objectInfo['ID'].'-user', 'firstname', $tmp_firstname);
								}
								if ($property=='lastname') {
									$tmp_lastname = $value;
									$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, $objectInfo['ID'].'-user', 'lastname', $tmp_lastname);
								}

								$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, $objectInfo['ID'].'-user', 'name', $tmp_firstname.' '.$tmp_lastname);
								$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $objectInfo['ID'].'-user', 'name', $tmp_firstname.' '.$tmp_lastname);
								$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'CLEAR_USERINFOS', sGuiUS(), NULL);

								$koala->queueScript( 'Koala.yg_refreshUserTable(Koala.windows[\''.$winID.'\'].boundWindow, \''.$property.'\');');
								$specialupdate = true;
							}

							// Special case for email (set login)
							if ($property=='email') {
								$user->setLogin($value);
								if ($objectInfo['ID'] == sUserMgr()->getCurrentUserID()) {
									$this->session->setPSessionVar('username', $value);
									$this->session->setPSessionVar('isvalidated', true);
                                    $this->session->refrehSessionCookie();
								}
								$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, $objectInfo['ID'].'-user', 'email', $value);
								$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'CLEAR_USERINFOS', sGuiUS(), NULL);

								$koala->queueScript( 'Koala.yg_refreshUserTable(Koala.windows[\''.$winID.'\'].boundWindow, \''.$property.'\');');
								$specialupdate = true;
							}

							// Special case for company (update company in gui)
							if ($property=='company') {
								$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'CLEAR_USERINFOS', sGuiUS(), NULL);
							}

							if ( !$specialupdate &&
								 ($property_data[0]['TYPE']!='TAG') &&
								 ($property_data[0]['TYPE']!='PAGE') &&
								 ($property_data[0]['TYPE']!='LINK') &&
								 ($property_data[0]['TYPE']!='CBLOCK') &&
								 ($property_data[0]['TYPE']!='RICHTEXT') &&
								 ($property_data[0]['TYPE']!='CHECKBOX') &&
								 ($property_data[0]['TYPE']!='DATE') &&
								 ($property_data[0]['TYPE']!='DATETIME') ) {
								//$jsQueue->add ($objectInfo['ID'], HISTORYTYPE_USER, 'OBJECT_CHANGE', sGuiUS(), 'user', NULL, NULL, $objectInfo['ID'].'-user', $property, $value);
							}

						}
						break;

					case 'usergroup':
						// Get the usergroup
						$objectInfo = sUsergroups()->get( $data[0] );

						$oldname = $objectInfo['NAME'];
						$value = str_replace("\r", '', str_replace("\n", '\n', $this->reponsedata[$property]->value));

						if ($oldname == $value) {
							// No update needed, henceforth break
							break;
						}

						// Check for empty name
						if ( ($property=='name') && (trim($value) == '')) {
							$jsQueue->add ($data[0], HISTORYTYPE_USERGROUP, 'OBJECT_CHANGE', sGuiUS(), 'usergroup', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $oldvalue);
							$koala->alert( $itext['TXT_CANT_CHANGE_USERGROUPTITLE_TO_EMPTY_VALUE'] );
							break;
						}

						// Special handling for files
						if ($propertyInfo[0]['TYPE']=='FILE') {
							if (substr($value, 0, 7) == '/image/') {
								$realValue = sFileMgr()->getFileIdByPname( substr($value, 7) );
								if ($realValue) $value = $realValue;
							}
						}

						// Special handling for links
						if ($propertyInfo[0]['TYPE']=='LINK') {
							$value = trim(prettifyUrl($value));
							$result = checkLinkInternalExternal($value);
							if ($result['TYPE']!='external') {
								$value = createSpecialURLfromShortURL($value);
							}
						}

						// Special handling for textareas
						if ($propertyInfo[0]['TYPE']=='TEXTAREA') {
							$value = str_replace('\n', "\n", $value);
						}

						// Special handling for wysiwyg
						if ($propertyInfo[0]['TYPE']=='RICHTEXT') {
							$value = str_replace('\n', "\n", $value);
							$value = convertShortURLsToSpecialURLs($value);
						}

						// Set the new property
						if ( sUsergroups()->setName( $data[0], $value ) === false ) {
							$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
						} else {
							$value = str_replace("\n", '\n', $value);
							$jsQueue->add ($data[0], HISTORYTYPE_USERGROUP, 'OBJECT_CHANGE', sGuiUS(), 'usergroup', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $value);
							$jsQueue->add ($data[0], HISTORYTYPE_USERGROUP, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $value);

							$jsQueue->add ($data[0], HISTORYTYPE_USERGROUP, 'Koala.yg_resortRolesList(\''.$winID.'\');', sGuiUS());
						}
						break;

					case 'cblock':
						$cb = sCblockMgr()->getCblock($data[0]);
						// Get the versioned contentblockid
						$cblockInfo = $cb->get();

						// Get old property value & check if change is needed
						$oldvalue = $cb->properties->getValueInternal(strtoupper($property));

						$value = str_replace("\r", '', str_replace("\n", '\n', $this->reponsedata[$property]->value));
						if ($value == ' ') $value = '';

						if ($oldvalue == $value) {
							// No update needed, henceforth break
							break;
						}

						// Check for empty name
						if (($property=='name') && (trim($value) == '')) {
							$jsQueue->add ($data[0], HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $oldvalue);
							$jsQueue->add ($data[0], HISTORYTYPE_CO, 'REFRESH_WINDOW', sGuiUS(), 'name');

							$koala->alert($itext['TXT_CANT_CHANGE_CBLOCKTITLE_TO_EMPTY_VALUE']);
							break;
						}

						// Check if property is a readonly property
						$propertyInfo = sCblockMgr()->properties->getProperty(strtoupper($property));
						$isReadOnlyProperty = $propertyInfo[0]['READONLY'];

						// Special handling for dates
						if (($propertyInfo[0]['TYPE']=='DATE')||($propertyInfo[0]['TYPE']=='DATETIME')) {
							if ($propertyInfo[0]['TYPE']=='DATETIME') {
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

							$version = substr($autopublish_data_item['version'],8);
							$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
							$timestamp = TSfromLocalTS($timestamp);
							$value = $timestamp;
						}

						// Special handling for files
						if ($propertyInfo[0]['TYPE']=='FILE') {
							if (substr($value, 0, 7) == '/image/') {
								$realValue = sFileMgr()->getFileIdByPname( substr($value, 7) );
								if ($realValue) $value = $realValue;
							}
						}

						// Special handling for links
						if ($propertyInfo[0]['TYPE']=='LINK') {
							$value = trim(prettifyUrl($value));
							$result = checkLinkInternalExternal($value);
							if ($result['TYPE']!='external') {
								$value = createSpecialURLfromShortURL($value);
							}
						}

						// Special handling for textareas
						if ($propertyInfo[0]['TYPE']=='TEXTAREA') {
							$value = str_replace('\n', "\n", $value);
						}

						// Special handling for wysiwyg
						if ($propertyInfo[0]['TYPE']=='RICHTEXT') {
							$value = str_replace('\n', "\n", $value);
							$value = convertShortURLsToSpecialURLs($value);
						}

						// Set the new property
						if ($cb->properties->setValue(strtoupper($property), $value) === false) {
							$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
						} else {
							$property_data = sCblockMgr()->properties->getProperty(strtoupper($property));

							// Do not use Koala::change for some elements
							if ($property_data[0]['TYPE']=='LINK') {
								/* Do nothing, everything already done from frontend */
							} elseif ( ($property_data[0]['TYPE']!='CHECKBOX') &&
								 ($property_data[0]['TYPE']!='RICHTEXT') &&
								 ($property_data[0]['TYPE']!='TAG') &&
								 ($property_data[0]['TYPE']!='CBLOCK') &&
								 ($property_data[0]['TYPE']!='PAGE') &&
								 ($property_data[0]['TYPE']!='DATE') &&
								 ($property_data[0]['TYPE']!='DATETIME') ) {
								$value = str_replace("\n", '\n', $value);
								$jsQueue->add ($data[0], HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $value);
								if ($property == 'name') {
									// Trigger refresh of list entries
									$jsQueue->add ($data[0], HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $data[0].'-cblock', 'listitem');
								}
							}

							// Special case for name
							if ($property=='name') {
								if ($cblockInfo['PNAME'] == NULL) {
									$PName = $cb->calcPName();
									$cb->setPName($PName);
									$jsQueue->add ($data[0], HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $data[0].'-cblock', 'pname', $PName);
									$jsQueue->add ($data[0], HISTORYTYPE_CO, 'OBJECT_CHANGEPNAME', sGuiUS(), 'cblock', NULL, NULL, $data[0].'-cblock', 'name', $PName);
									$jsQueue->add ($data[0], HISTORYTYPE_CO, 'REFRESH_WINDOW', sGuiUS(), 'pname');
								}

								$jsQueue->add ($data[0], HISTORYTYPE_CO, 'REFRESH_WINDOW', sGuiUS(), 'name');
							}

							// Add to history
							if ($property_data[0]['TYPE']=='CHECKBOX') {
								if ($value==1) {
									$cb->history->add (HISTORYTYPE_CO, $property_data[0]['NAME'], $value, 'TXT_CBLOCK_H_PROP_CHECKON');
								} else {
									$cb->history->add (HISTORYTYPE_CO, $property_data[0]['NAME'], $value, 'TXT_CBLOCK_H_PROP_CHECKOFF');
								}
							} elseif ($property_data[0]['TYPE']=='FILE') {
								$cb->history->add (HISTORYTYPE_CO, strtoupper($property), $value, 'TXT_CBLOCK_H_PROP_FILE');
							} else if ($property_data[0]['TYPE']=='RICHTEXT') {
								$page->history->add (HISTORYTYPE_CO, strtoupper($property), $value, 'TXT_CBLOCK_H_PROP_RICHTEXT');
							} else if ($property_data[0]['TYPE']=='TAG') {
								$page->history->add (HISTORYTYPE_CO, strtoupper($property), $value, 'TXT_CBLOCK_H_PROP_TAG');
							} else if ($property_data[0]['TYPE']=='CBLOCK') {
								$page->history->add (HISTORYTYPE_CO, strtoupper($property), $value, 'TXT_CBLOCK_H_PROP_CBLOCK');
							} else if ($property_data[0]['TYPE']=='LINK') {
								$page->history->add (HISTORYTYPE_CO, strtoupper($property), $value, 'TXT_CBLOCK_H_PROP_LINK');
							} else if ($property_data[0]['TYPE']=='PAGE') {
								$page->history->add (HISTORYTYPE_CO, strtoupper($property), $value['page'], 'TXT_CBLOCK_H_PROP_PAGE', $value['site']);
							} else if ($property_data[0]['TYPE']=='DATE') {
								$page->history->add (HISTORYTYPE_CO, strtoupper($property), $value, 'TXT_CBLOCK_H_PROP_DATE');
							} else if ($property_data[0]['TYPE']=='DATETIME') {
								$page->history->add (HISTORYTYPE_CO, strtoupper($property), $value, 'TXT_CBLOCK_H_PROP_DATETIME');
							} else if ($property_data[0]['TYPE']=='PASSWORD') {
								$page->history->add (HISTORYTYPE_CO, strtoupper($property), $value, 'TXT_CBLOCK_H_PROP_PASSWORD');
							} else {
								if ($isReadOnlyProperty) {
									$cb->history->add (HISTORYTYPE_CO, strtoupper($propertyInfo[0]['NAME']), $value, 'TXT_CBLOCK_H_PROP');
								} else {
									$cb->history->add (HISTORYTYPE_CO, strtoupper($property), $value, 'TXT_CBLOCK_H_PROP');
								}
							}

							if ($cblockInfo['FOLDER']!=1) {
								$jsQueue->add ($data[0], HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');
							}

							// Get Parent Id
							$parentId = sCblockMgr()->getParents($data[0]);
							$parentId = $parentId[0][0]['ID'];

							// Trigger refresh of list entries
							$jsQueue->add ($data[0], HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $parentId.'-cblock', 'listitem');

						}
						break;

					case 'page':
						$siteID = $data[1];
						$object = $data[0];

						$pageMgr = new PageMgr($siteID);
						$jsQueue = new JSQueue(NULL, $siteID);

						$page = $pageMgr->getPage($object);
						$objectInfo = $page->get();
						$objectparents = $pageMgr->getParents($object);

						// Get old property value & check if change is needed
						$oldvalue = $page->properties->getValueInternal(strtoupper($property));

						$value = str_replace("\r", '', str_replace("\n", '\n', $this->reponsedata[$property]->value));
						if ($value == ' ') $value = '';

						if ($oldvalue == $value) {
							// No update needed, henceforth break
							break;
						}

						// Check for empty name
						if (($property=='name') && (trim($value) == '')) {
							$jsQueue->add ($object, HISTORYTYPE_PAGE, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $oldvalue);
							$jsQueue->add ($object, HISTORYTYPE_PAGE, 'REFRESH_WINDOW', sGuiUS(), 'name');

							$koala->alert($itext['TXT_CANT_CHANGE_PAGETITLE_TO_EMPTY_VALUE']);
							break;
						}

						// Check if property is a readonly property
						$propertyInfo = $page->properties->getProperty(strtoupper($property));
						$isReadOnlyProperty = $propertyInfo[0]['READONLY'];

						// Special handling for dates
						if (($propertyInfo[0]['TYPE']=='DATE')||($propertyInfo[0]['TYPE']=='DATETIME')) {
							if ($propertyInfo[0]['TYPE']=='DATETIME') {
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

							$version = substr($autopublish_data_item['version'],8);
							$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
							$timestamp = TSfromLocalTS($timestamp);
							$value = $timestamp;
						}

						// Special handling for files
						if ($propertyInfo[0]['TYPE']=='FILE') {
							if (substr($value, 0, 7) == '/image/') {
								$realValue = sFileMgr()->getFileIdByPname( substr($value, 7) );
								if ($realValue) $value = $realValue;
							}
						}

						// Special handling for links
						if ($propertyInfo[0]['TYPE']=='LINK') {
							$value = trim(prettifyUrl($value));
							$result = checkLinkInternalExternal($value);
							if ($result['TYPE']!='external') {
								$value = createSpecialURLfromShortURL($value);
							}
						}

						// Special handling for textareas
						if ($propertyInfo[0]['TYPE']=='TEXTAREA') {
							$value = str_replace('\n', "\n", $value);
						}

						// Special handling for wysiwyg
						if ($propertyInfo[0]['TYPE']=='RICHTEXT') {
							$value = str_replace('\n', "\n", $value);
							$value = convertShortURLsToSpecialURLs($value);
						}

						// Set the new property
						if ($page->properties->setValue(strtoupper($property), $value) === false) {
							$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
						} else {
							$property_data = $page->properties->getProperty(strtoupper($property));

							// Do not use Koala::change for some elements
							if ($property_data[0]['TYPE']=='LINK') {
								/* Do nothing, everything already done from frontend */
							} elseif ( ($property_data[0]['TYPE']!='CHECKBOX') &&
								 ($property_data[0]['TYPE']!='RICHTEXT') &&
								 ($property_data[0]['TYPE']!='TAG') &&
								 ($property_data[0]['TYPE']!='CBLOCK') &&
								 ($property_data[0]['TYPE']!='PAGE') &&
								 ($property_data[0]['TYPE']!='DATE') &&
								 ($property_data[0]['TYPE']!='DATETIME') ) {
								$value = str_replace("\n", '\n', $value);
								$jsQueue->add ($object, HISTORYTYPE_PAGE, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $value);
							}

							// For Lists: Check if empty title was set
							if (($property_data[0]['TYPE']=='LIST') && (trim($value) == $itext['TXT_NO_SELECTION'])) {
								$page->properties->setValue(strtoupper($property), '');
							}

							// Special case for name
							if ($property=='name') {
								if ($objectInfo['PNAME'] == NULL) {
									$PName = $page->calcPName();
									$page->setPName($PName);
									$jsQueue->add ($object, HISTORYTYPE_PAGE, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $object.'-'.$siteID, 'pname', $PName);
									$jsQueue->add ($object, HISTORYTYPE_PAGE, 'OBJECT_CHANGEPNAME', sGuiUS(), 'page', NULL, NULL, $object.'-'.$siteID, 'name', $PName);
									$jsQueue->add ($object, HISTORYTYPE_PAGE, 'REFRESH_WINDOW', sGuiUS(), 'pname');
								}

								$jsQueue->add ($object, HISTORYTYPE_PAGE, 'REFRESH_WINDOW', sGuiUS(), 'name');
							}

							// Add to history
							if ($property_data[0]['TYPE']=='CHECKBOX') {
								if ($value==1) {
									$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP_CHECKON');
								} else {
									$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP_CHECKOFF');
								}
							} else if ($property_data[0]['TYPE']=='FILE') {
								if ($value) {
									$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP_FILE');
								} else {
									$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP_FILE_REMOVED');
								}
							} else if ($property_data[0]['TYPE']=='RICHTEXT') {
								$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP_RICHTEXT');
							} else if ($property_data[0]['TYPE']=='TAG') {
								$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP_TAG');
							} else if ($property_data[0]['TYPE']=='CBLOCK') {
								$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP_CBLOCK');
							} else if ($property_data[0]['TYPE']=='LINK') {
								$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP_LINK');
							} else if ($property_data[0]['TYPE']=='PAGE') {
								$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value['page'], 'TXT_PAGE_H_PROP_PAGE', $value['site']);
							} else if ($property_data[0]['TYPE']=='DATE') {
								$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP_DATE');
							} else if ($property_data[0]['TYPE']=='DATETIME') {
								$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP_DATETIME');
							} else if ($property_data[0]['TYPE']=='PASSWORD') {
								$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP_PASSWORD');
							} else {
								if ($isReadOnlyProperty) {
									$page->history->add (HISTORYTYPE_PAGE, strtoupper($propertyInfo[0]['NAME']), $value, 'TXT_PAGE_H_PROP');
								} else {
									$page->history->add (HISTORYTYPE_PAGE, strtoupper($property), $value, 'TXT_PAGE_H_PROP');
								}
							}

							$jsQueue->add ($object, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');
						}
						break;
					case 'file':
					case 'filefolder':
						// Get the versioned fileid
						$file = sFileMgr()->getFile($data[0]);
						$finalVersion = $file->getLatestApprovedVersion();
						$file = sFileMgr()->getFile($data[0], $finalVersion);
						$fileInfo = $file->get();

						// Get old property value & check if change is needed
						$oldvalue = $file->properties->getValueInternal(strtoupper($property));

						$value = str_replace("\r", '', $this->reponsedata[$property]->value);
						if ($value == ' ') $value = '';

						if ($oldvalue == $value) {
							// No update needed, henceforth break
							break;
						}

						// Check for empty name
						if (($property=='name') && (trim($value) == '')) {
							$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'file', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, str_replace("\n", '\n', $oldvalue));
							$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'filefolder', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, str_replace("\n", '\n', $oldvalue));
							$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'REFRESH_WINDOW', sGuiUS(), 'name');

							$koala->alert($itext['TXT_CANT_CHANGE_FILETITLE_TO_EMPTY_VALUE']);
							break;
						}

						// Check if property is a readonly property
						$propertyInfo = $file->properties->getProperty(strtoupper($property));
						$isReadOnlyProperty = $propertyInfo[0]['READONLY'];

						// Special handling for dates
						if (($propertyInfo[0]['TYPE']=='DATE')||($propertyInfo[0]['TYPE']=='DATETIME')) {
							if ($propertyInfo[0]['TYPE']=='DATETIME') {
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

							$version = substr($autopublish_data_item['version'],8);
							$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
							$timestamp = TSfromLocalTS($timestamp);
							$value = $timestamp;
						}

						// Special handling for files
						if ($propertyInfo[0]['TYPE']=='FILE') {
							if (substr($value, 0, 7) == '/image/') {
								$realValue = sFileMgr()->getFileIdByPname( substr($value, 7) );
								if ($realValue) $value = $realValue;
							}
						}

						// Special handling for links
						if ($propertyInfo[0]['TYPE']=='LINK') {
							$value = trim(prettifyUrl($value));
							$result = checkLinkInternalExternal($value);
							if ($result['TYPE']!='external') {
								$value = createSpecialURLfromShortURL($value);
							}
						}

						// Special handling for textareas
						if ($propertyInfo[0]['TYPE']=='TEXTAREA') {
							$value = str_replace('\n', "\n", $value);
						}

						// Special handling for wysiwyg
						if ($propertyInfo[0]['TYPE']=='RICHTEXT') {
							$value = str_replace('\n', "\n", $value);
							$value = convertShortURLsToSpecialURLs($value);
						}

						// Set the new property
						if ($file->properties->setValue(strtoupper($property), $value) === false) {
							$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
						} else {
							$property_data = $file->properties->getProperty(strtoupper($property));

							// Do not use Koala::change for some elements
							if ($property_data[0]['TYPE']=='LINK') {
								/* Do nothing, everything already done from frontend */
							} elseif ( ($property_data[0]['TYPE']!='CHECKBOX') &&
								 ($property_data[0]['TYPE']!='RICHTEXT') &&
								 ($property_data[0]['TYPE']!='TAG') &&
								 ($property_data[0]['TYPE']!='CBLOCK') &&
								 ($property_data[0]['TYPE']!='PAGE') &&
								 ($property_data[0]['TYPE']!='DATE') &&
								 ($property_data[0]['TYPE']!='DATETIME') ) {
								$value = str_replace("\n", '\n', $value);
								$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'file', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, str_replace("\n", '\n', $value));
								$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'filefolder', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, str_replace("\n", '\n', $value));
							}

							// Special case for name
							if ($property=='name') {
								if ($fileInfo['PNAME'] == NULL) {
									$PName = $file->calcPName();
									$file->setPName($PName);
									$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'file', NULL, NULL, $data[0].'-file', 'pname', $PName);
									$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'filefolder', NULL, NULL, $data[0].'-file', 'pname', $PName);
									$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'OBJECT_CHANGEPNAME', sGuiUS(), 'file', NULL, NULL, $data[0].'-file', 'name', $PName);
									$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'REFRESH_WINDOW', sGuiUS(), 'pname');
								}

								$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'REFRESH_WINDOW', sGuiUS(), 'name');
							}

							// Add to history
							if ($property_data[0]['TYPE']=='CHECKBOX') {
								if ($value==1) {
									$file->history->add (HISTORYTYPE_FILE, strtoupper($property), $value, 'TXT_FILE_H_PROP_CHECKON');
								} else {
									$file->history->add (HISTORYTYPE_FILE, strtoupper($property), $value, 'TXT_FILE_H_PROP_CHECKOFF');
								}
							} else if ($property_data[0]['TYPE']=='RICHTEXT') {
								$file->history->add (HISTORYTYPE_FILE, strtoupper($property), $value, 'TXT_FILE_H_PROP_RICHTEXT');
							} else if ($property_data[0]['TYPE']=='TAG') {
								$file->history->add (HISTORYTYPE_FILE, strtoupper($property), $value, 'TXT_FILE_H_PROP_TAG');
							} else if ($property_data[0]['TYPE']=='CBLOCK') {
								$file->history->add (HISTORYTYPE_FILE, strtoupper($property), $value, 'TXT_FILE_H_PROP_CBLOCK');
							} else if ($property_data[0]['TYPE']=='LINK') {
								$file->history->add (HISTORYTYPE_FILE, strtoupper($property), $value, 'TXT_FILE_H_PROP_LINK');
							} else if ($property_data[0]['TYPE']=='PAGE') {
								$file->history->add (HISTORYTYPE_FILE, strtoupper($property), $value['page'], 'TXT_FILE_H_PROP_PAGE', $value['site']);
							} else if ($property_data[0]['TYPE']=='DATE') {
								$file->history->add (HISTORYTYPE_FILE, strtoupper($property), $value, 'TXT_FILE_H_PROP_DATE');
							} else if ($property_data[0]['TYPE']=='DATETIME') {
								$file->history->add (HISTORYTYPE_FILE, strtoupper($property), $value, 'TXT_FILE_H_PROP_DATETIME');
							} else if ($property_data[0]['TYPE']=='PASSWORD') {
								$file->history->add (HISTORYTYPE_FILE, strtoupper($property), $value, 'TXT_FILE_H_PROP_PASSWORD');
							} else {
								if ($isReadOnlyProperty) {
									$file->history->add (HISTORYTYPE_FILE, strtoupper($propertyInfo[0]['NAME']), $value, 'TXT_FILE_H_PROP');
								} else {
									$file->history->add (HISTORYTYPE_FILE, strtoupper($property), $value, 'TXT_FILE_H_PROP');
								}
							}

							if ($fileInfo['FOLDER'] === '0') {
								$fileversion = $file->newVersion();
								$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'CLEAR_FILEINFOS', sGuiUS(), NULL);
							}
						}
						break;

					case 'tag':
						// Get the tag
						$tagMgr = new Tags();
						$tagInfo = $tagMgr->get($data[0]);

						$oldname = $tagInfo['NAME'];
						$value = str_replace("\r", '', str_replace("\n", '\n', $this->reponsedata[$property]->value));

						if ($oldname == $value) {
							// No update needed, henceforth break
							break;
						}

						// Check for empty name
						if (($property=='name') && (trim($value) == '')) {
							$jsQueue->add ($data[0], HISTORYTYPE_TAG, 'OBJECT_CHANGE', sGuiUS(), 'tag', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $oldvalue);
							$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'REFRESH_WINDOW', sGuiUS(), 'name');

							$koala->alert($itext['TXT_CANT_CHANGE_TAGTITLE_TO_EMPTY_VALUE']);
							break;
						}

						// Special handling for files
						if ($propertyInfo[0]['TYPE']=='FILE') {
							if (substr($value, 0, 7) == '/image/') {
								$realValue = sFileMgr()->getFileIdByPname( substr($value, 7) );
								if ($realValue) $value = $realValue;
							}
						}

						// Special handling for links
						if ($propertyInfo[0]['TYPE']=='LINK') {
							$value = trim(prettifyUrl($value));
							$result = checkLinkInternalExternal($value);
							if ($result['TYPE']!='external') {
								$value = createSpecialURLfromShortURL($value);
							}
						}

						// Special handling for textareas
						if ($propertyInfo[0]['TYPE']=='TEXTAREA') {
							$value = str_replace('\n', "\n", $value);
						}

						// Special handling for wysiwyg
						if ($propertyInfo[0]['TYPE']=='RICHTEXT') {
							$value = str_replace('\n', "\n", $value);
							$value = convertShortURLsToSpecialURLs($value);
						}

						// Set the new property
						if ($tagMgr->setName($data[0], $value) === false) {
							$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
						} else {
							$value = str_replace("\n", '\n', $value);
							$jsQueue->add ($data[0], HISTORYTYPE_TAG, 'OBJECT_CHANGE', sGuiUS(), 'tag', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $value);
							$jsQueue->add ($data[0], HISTORYTYPE_TAG, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $value);

							// Special case for name
							if ($property=='name') {
								$jsQueue->add ($data[0], HISTORYTYPE_TAG, 'REFRESH_WINDOW', sGuiUS(), 'name');
								$jsQueue->add ($data[0], HISTORYTYPE_PAGE, 'REFRESH_WINDOW', sGuiUS(), 'name');
							}
						}
						break;

					case 'template':
						// Get the template
						$templateMgr = new Templates();
						$templateInfo = $templateMgr->getTemplate($data[0]);

						$oldname = $templateInfo['NAME'];
						$value = str_replace("\r", '', str_replace("\n", '\n', $this->reponsedata[$property]->value));

						if ($oldname == $value) {
							// No update needed, henceforth break
							break;
						}

						// Check for empty name
						if (trim($value) == '') {
							//$jsQueue->add ($data[0], HISTORYTYPE_TEMPLATE, 'OBJECT_CHANGE', sGuiUS(), 'tag', NULL, NULL, $this->reponsedata[$property]->yg_id, $property, $oldvalue);
							//$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'REFRESH_WINDOW', sGuiUS(), 'name');

							$koala->alert($itext['TXT_CANT_CHANGE_TEMPLATETITLE_TO_EMPTY_VALUE']);
							break;
						}

						// Special handling for files
						if ($propertyInfo[0]['TYPE']=='FILE') {
							if (substr($value, 0, 7) == '/image/') {
								$realValue = sFileMgr()->getFileIdByPname( substr($value, 7) );
								if ($realValue) $value = $realValue;
							}
						}

						// Special handling for links
						if ($propertyInfo[0]['TYPE']=='LINK') {
							$value = trim(prettifyUrl($value));
							$result = checkLinkInternalExternal($value);
							if ($result['TYPE']!='external') {
								$value = createSpecialURLfromShortURL($value);
							}
						}

						// Special handling for textareas
						if ($propertyInfo[0]['TYPE']=='TEXTAREA') {
							$value = str_replace('\n', "\n", $value);
						}

						// Special handling for wysiwyg
						if ($propertyInfo[0]['TYPE']=='RICHTEXT') {
							$value = str_replace('\n', "\n", $value);
							$value = convertShortURLsToSpecialURLs($value);
						}

						// Set the new property
						if ($templateMgr->setName($data[0], $value) === false) {
							$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
						} else {
							$value = str_replace("\n", '\n', $value);
							$jsQueue->add ($data[0], HISTORYTYPE_TEMPLATE, 'OBJECT_CHANGE', sGuiUS(), 'template', NULL, NULL, $this->reponsedata[$property]->yg_id, 'name', $value);
							$koala->queueScript( "Koala.yg_fadeFields(\$('".$winID."'), '.changed');" );

							// Special case for name
							$jsQueue->add ($data[0], HISTORYTYPE_TEMPLATE, 'REFRESH_WINDOW', sGuiUS(), 'name');
						}
						break;

					case 'entrymask':
						$entrymaskMgr = new Entrymasks();

						// Get the entrymask
						$entrymask = $entrymaskMgr->get($data[0]);

						$oldname = $entrymask['NAME'];
						$value = str_replace("\r", '', str_replace("\n", '\n', $this->reponsedata[$property]->value));

						if ($oldname == $value) {
							// No update needed, henceforth break
							break;
						}

						// Check for empty name
						if (trim($value) == '') {
							//$jsQueue->add ($data[0], HISTORYTYPE_ENTRYMASK, 'OBJECT_CHANGE', sGuiUS(), 'entrymask', NULL, NULL, $this->reponsedata[$property]->yg_id, 'name', $oldvalue);
							//$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'REFRESH_WINDOW', sGuiUS(), 'name');

							$koala->alert($itext['TXT_CANT_CHANGE_ENTRYMASKTITLE_TO_EMPTY_VALUE']);
							break;
						}

						// Special handling for files
						if ($propertyInfo[0]['TYPE']=='FILE') {
							if (substr($value, 0, 7) == '/image/') {
								$realValue = sFileMgr()->getFileIdByPname( substr($value, 7) );
								if ($realValue) $value = $realValue;
							}
						}

						// Special handling for links
						if ($propertyInfo[0]['TYPE']=='LINK') {
							$value = trim(prettifyUrl($value));
							$result = checkLinkInternalExternal($value);
							if ($result['TYPE']!='external') {
								$value = createSpecialURLfromShortURL($value);
							}
						}

						// Special handling for textareas
						if ($propertyInfo[0]['TYPE']=='TEXTAREA') {
							$value = str_replace('\n', "\n", $value);
						}

						// Special handling for wysiwyg
						if ($propertyInfo[0]['TYPE']=='RICHTEXT') {
							$value = str_replace('\n', "\n", $value);
							$value = convertShortURLsToSpecialURLs($value);
						}

						// Set the new property
						if ($entrymaskMgr->setName($data[0], $value) === false) {
							$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
						} else {
							$value = str_replace("\n", '\n', $value);
							$jsQueue->add ($data[0], HISTORYTYPE_ENTRYMASK, 'OBJECT_CHANGE', sGuiUS(), 'entrymask', NULL, NULL, $this->reponsedata[$property]->yg_id, 'name', $value);
							$jsQueue->add ($data[0], HISTORYTYPE_ENTRYMASK, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $this->reponsedata[$property]->yg_id, 'name', $value);

							// Special case for name
							$jsQueue->add ($data[0], HISTORYTYPE_ENTRYMASK, 'REFRESH_WINDOW', sGuiUS(), 'name');
							$jsQueue->add ($data[0], HISTORYTYPE_PAGE, 'REFRESH_WINDOW', sGuiUS(), 'name');
						}
						break;

				}
				break;

	}

?>