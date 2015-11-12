<?php

	$pageID = $this->params['page'];
	$siteID = $this->params['site'];
	$templateMgr = new Templates();
	$siteMgr = new Sites();

	if ($pageID == 0 && $siteID == 0) {
		$data = explode('-', $this->reponsedata['null']->yg_id);
		$pageID = $data[0];
		$siteID = $data[1];
	}

	if ($pageID && $siteID) {
		$pageMgr = new PageMgr($siteID);
		$page = $pageMgr->getPage($pageID);
		$pageInfo = $page->get();

		$jsQueue = new JSQueue(NULL, $siteID);
	}

	switch ($action) {

			case 'checkSpecialLinkType':
				$url = $this->params['url'];
				$result = getSpecialURLInfo($url);
				if (!$result) {
					$result = getSpecialURLInfo(createSpecialURLfromShortURL($url));
				}
				echo json_encode($result);
				break;

			case 'checkLinkExternal':
				$url = $this->params['url'];
				$result = checkLinkInternalExternal($url);
				if ($result['TYPE']=='external') {
					echo $result['TYPE'];
				} else {
					echo $result['NAME'];
				}
				break;

			case 'savePagePublishingSettings':
				$autopublish_data = json_decode($this->params['autopublishData'], true);
				$changed_field = $this->params['changedField'];

				$version = $this->params['version'];
				$wid = $this->params['wid'];

				if ($version=='latest') {
					$version = ALWAYS_LATEST_APPROVED_VERSION;
				}

				$old_autopublish_data = $page->scheduler->getSchedule($pageID, 'SCH_AUTOPUBLISH');

				if ($changed_field == 'PUBLISH') {
					$page->publishVersion($version);
				}

				if (($changed_field == 'VERSION') ||
					 ($changed_field == 'DATE') ||
					 ($changed_field == 'TIME')) {
					// Process autopublish data
					foreach ($autopublish_data as $ap_idx => $autopublish_data_item) {

						$id	 = $autopublish_data_item['id'];
						$time   = explode(':',$autopublish_data_item['time']);
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

						$date   = explode('.',$autopublish_data_item['date']);
						$day	= (int)$date[0];
						$month  = (int)$date[1];
						$year   = (int)$date[2];

						$version = substr($autopublish_data_item['version'], 8);

						if ($version=='latest') {
							$version = ALWAYS_LATEST_APPROVED_VERSION;
						}

						$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
						$timestamp = TSfromLocalTS($timestamp);
						$parameters = array('VERSION'=>$version);

						$haschanged = false;
						foreach($old_autopublish_data as $old_ap_idx => $old_autopublish_data_item) {
							if ($old_autopublish_data_item['ID']==$autopublish_data_item['id']) {

								if (($timestamp!=$old_autopublish_data_item['TIMESTAMP']) ||
								 	 ($version!=$old_autopublish_data_item['PARAMETERS']['VERSION'])) {
									$haschanged = true;
								}
							}
						}

						if (substr($id,0,5) == 'dummy') {
							$schedule_id = $page->scheduler->schedule($pageID, 'SCH_AUTOPUBLISH', $timestamp, $parameters);

							// Add to history
							if ($version != ALWAYS_LATEST_APPROVED_VERSION) {
								$page->history->add(HISTORYTYPE_PAGE, TStoLocalTS($timestamp), $version, 'TXT_PAGE_H_AUTOPUBLISH_ADDED', $schedule_id);
							} else {
								$lastfinalversion = $page->getLatestApprovedVersion();
								$page->history->add(HISTORYTYPE_PAGE, TStoLocalTS($timestamp), $lastfinalversion, 'TXT_PAGE_H_AUTOPUBLISH_ADDED', $schedule_id);
							}
						} elseif ($haschanged) {
							if ($page->scheduler->updateAction($id, 'SCH_AUTOPUBLISH', $timestamp, $parameters) === false) {
								$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
							} else {
								// Add to history
								if ($version != ALWAYS_LATEST_APPROVED_VERSION) {
									$page->history->add(HISTORYTYPE_PAGE, TStoLocalTS($timestamp), $version, 'TXT_PAGE_H_AUTOPUBLISH_CHANGED', $id);
								} else {
									$lastfinalversion = $page->getLatestApprovedVersion();
									$page->history->add(HISTORYTYPE_PAGE, TStoLocalTS($timestamp), $lastfinalversion, 'TXT_PAGE_H_AUTOPUBLISH_CHANGED', $id);
								}
							}
						}

					}

				}

				$koala->queueScript("Koala.yg_resetPublishSettingsEditState('".$wid."');");

				break;

			case 'removePageAutopublishItem':

				$itemID = (int)$this->params['itemID'];

				$item_data = $page->scheduler->get($itemID);
				$page->scheduler->removeJob($itemID);

				// Add to history
				if ($item_data['PARAMETERS']['VERSION'] != ALWAYS_LATEST_APPROVED_VERSION) {
					$page->history->add(HISTORYTYPE_PAGE, $item_data['TIMESTAMP'], $item_data['PARAMETERS']['VERSION'], 'TXT_PAGE_H_AUTOPUBLISH_DELETED', $itemID);
				} else {
					$lastfinalversion = $page->getLatestApprovedVersion();
					$page->history->add(HISTORYTYPE_PAGE, $item_data['TIMESTAMP'], $lastfinalversion, 'TXT_PAGE_H_AUTOPUBLISH_DELETED', $itemID);
				}
				break;

			case 'savePageVersion':
				// Cannot use new style of parameters here (function is called by a custom-attribute event - and therefore does not know about named parameters)

				$data = explode('-', $this->reponsedata['null']->yg_id);
				$wid = $data[2];

				$new_version = $page->newVersion();

				$koala->queueScript('Koala.windows[\'wid_'.$wid.'\'].tabs.select(6,Koala.windows[\'wid_'.$wid.'\'].tabs.params);');
				break;

			case 'restorePageVersion':

				$version = $this->params['version'];
				$wid = $this->params['wid'];
				$page = $pageMgr->getPage($pageID, $version);
				$oldinfo = $page->get();
				$new_version = $page->newVersion();

				$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $pageID.'-'.$siteID, 'name', $oldinfo['NAME']);
				$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');

				$koala->queueScript('Koala.windows[\'wid_'.$wid.'\'].tabs.select(Koala.windows[\'wid_'.$wid.'\'].tabs.selected, Koala.windows[\'wid_'.$wid.'\'].tabs.params);');
				break;

			case 'setPageTemplate':

				$template = $this->params['templateId'];

				$newTemplateInfo = $templateMgr->getTemplate($template);

				// Check if we really got sane values
				if ($template!='template') {

					$page->setTemplate($template);

					$pageInfo = $page->get();
					$newNaviId = $pageInfo['NAVIGATIONID'];

					if ($newNaviId != 0 ) {
						$koala->queueScript("Koala.yg_updateNavigation('".$pageID."-".$siteID."','".$newNaviId."', '".$template."');");
						$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'PAGE_UNHIDE', sGuiUS(), 'name');
					} else {
						$koala->queueScript("Koala.yg_updateNavigation('".$pageID."-".$siteID."','0', '".$template."');");
						$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'PAGE_HIDE', sGuiUS(), 'name');

						// Add to history
						$page->history->add(HISTORYTYPE_PAGE, NULL, NULL, "TXT_PAGE_H_NONAVIGATION");
					}

					// Add to history
					$page->history->add(HISTORYTYPE_PAGE, NULL, $newTemplateInfo['NAME'], "TXT_PAGE_H_TEMPLATE", $newtagid);

					$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');

					$koala->queueScript("Koala.yg_updateTemplate('".$pageID."-".$siteID."','".$template."');");
				}
				break;

			case 'setPageNavigation':

				$wid = $this->params['wid'];
				$navigation = $this->params['navigationId'];

				// Check if we really got sane values
				if ($navigation!='navigation') {
					$page->setNavigation($navigation);
					// Check if we got '0' as navigation (none)
					if ($navigation == 0) {
						$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'PAGE_HIDE', sGuiUS(), 'name');

						// Add to history
						$page->history->add(HISTORYTYPE_PAGE, NULL, NULL, "TXT_PAGE_H_NONAVIGATION");
					} else {
						$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'PAGE_UNHIDE', sGuiUS(), 'name');

						$templatenavis = $templateMgr->getNavis($pageInfo['TEMPLATEID']);

						foreach ($templatenavis as $templatenavi_item) {
							if ($templatenavi_item['ID']==$navigation) {
								$navigation_name = $templatenavi_item['NAME'];
							}
						}

						// Add to history
						$page->history->add(HISTORYTYPE_PAGE, NULL, $navigation_name, "TXT_PAGE_H_NAVIGATION");
					}

					$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');

					$koala->queueScript("Koala.windows['wid_".$wid."'].refreshNavigation();");
				}
				break;

			case 'setPagePName':

				$value = $page->filterPName($this->params['value']);

				if ($pageMgr->getPageIdByPname($value)) {
					$koala->callJSFunction('Koala.yg_promptbox', $itext['TXT_ERROR'], $itext['TXT_PNAME_ALREADY_USED_CHOOSE_ANOTHER'], 'alert');
					$pageInfo = $page->get();

					$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $pageID.'-'.$siteID, 'pname', $pageInfo['PNAME']);
					break;
				}

				$page->setPName($value);
				$newPageInfo = $page->get();

				$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $pageID.'-'.$siteID, 'pname', $newPageInfo['PNAME']);
				$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'OBJECT_CHANGEPNAME', sGuiUS(), 'page', NULL, NULL, $pageID.'-'.$siteID, 'name', $newPageInfo['PNAME']);
				$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'REFRESH_WINDOW', sGuiUS(), 'pname');

				// Add to history
				$page->history->add(HISTORYTYPE_PAGE, NULL, $value, 'TXT_PAGE_H_PNAME');
				break;

			case 'setPageName':

				// Cannot use new style of parameters here (function is called by a custom-attribute event - and therefore does not know about named parameters)
				// Split PageID and SiteID
				$data = explode('-', $this->reponsedata['name']->yg_id );

				// Set the site
				$pageMgr = new PageMgr( $data[1] );
				$page = $pageMgr->getPage( $data[0] );
				$pageInfo = $page->get();
				$jsQueue = new JSQueue(NULL, $data[1]);

				// Set the new name
				if ( $page->properties->setValue('NAME', $this->reponsedata['name']->value) === false ) {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				} else {
					$jsQueue->add ($data[0], HISTORYTYPE_PAGE, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $this->reponsedata['name']->yg_id, 'name', $this->reponsedata['name']->value);
					if ($pageInfo['PNAME'] == NULL) {
						$PName = $page->calcPName();
						$page->setPName($PName);
						$jsQueue->add ($data[0], HISTORYTYPE_PAGE, 'OBJECT_CHANGEPNAME', sGuiUS(), 'page', NULL, NULL, $data[0].'-'.$data[1], 'name', $PName);
						$jsQueue->add ($data[0], HISTORYTYPE_PAGE, 'REFRESH_WINDOW', sGuiUS(), 'pname');
					}

					$jsQueue->add ($data[0], HISTORYTYPE_PAGE, 'REFRESH_WINDOW', sGuiUS(), 'name');

					// Add to history
					$page->history->add(HISTORYTYPE_PAGE, 'NAME', $this->reponsedata['name']->value, 'TXT_PAGE_H_PROP');

					$jsQueue->add ($data[0], HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');
				}
				break;

			case 'setPageState':

				$status = (int)$this->params['active'];

				$pageMgr = new PageMgr($siteID);
				$page = $pageMgr->getPage($pageID);
				$pageInfo = $page->get();
				$jsQueue = new JSQueue(NULL, $siteID);

				$page->setActive($status);

				switch ($status) {
					case 0:
						$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'PAGE_DEACTIVATE', sGuiUS(), 'name');

						// Add to history
						$page->history->add(HISTORYTYPE_PAGE, NULL, NULL, "TXT_PAGE_H_ACTIVE_0");
						break;
					case 1:
						$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'PAGE_ACTIVATE', sGuiUS(), 'name');

						// Add to history
						$page->history->add(HISTORYTYPE_PAGE, NULL, NULL, "TXT_PAGE_H_ACTIVE_1");
						break;
				}

				$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');
				break;

			case 'approvePage':

				$wid = $this->params['winID'];

				$pageversion = $page->getLatestVersion();
				$pageInfo = $page->get();

				if ($pageInfo['PNAME'] == NULL) {
					$PName = $page->calcPName();
					$page->setPName($PName);
				}

				$page->approve();

				$koala->queueScript('if (Koala.windows[\'wid_'.$wid.'\'].tab==\'CONTENT\') Koala.windows[\'wid_'.$wid.'\'].tabs.select(0,Koala.windows[\'wid_'.$wid.'\'].tabs.params);');

				$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'CLEAR_REFRESH', sGuiUS(), $siteID);
				$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'UNHIGHLIGHT_PAGE', sGuiUS(), 'name');
				break;

			case 'addPage':

				$pageID = $this->params['page'];
				$siteID = $this->params['site'];
				$pageMgr = new PageMgr($siteID);
				$pageHidden = true;

				// Check if root node is selected
				if (($pageID==='root')||($pageID==='')) {
					// Get real Page-ID of Root-Node
					$pageID = $pageMgr->tree->getRoot();
				}
				// Add new child node
				$new_id = $pageMgr->add($pageID);
				$page = $pageMgr->getPage($new_id);
				$page->properties->setValue('NAME',  $itext['TXT_NEW_OBJECT']);

				if ($new_id != false) {
					$icons = new Icons();
					$pageicon = $icons->icon['page_inactive_small'];
					if ($pageHidden) {
						$pageicon = $icons->icon['page_inactive_hidden_small'];
					}

					$jsQueue->add ($new_id, HISTORYTYPE_PAGE, 'PAGE_ADD', sGuiUS(), NULL);
				} else {
					$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
				}
				break;

			case 'moveUpPage':

				$reload = $this->params['reload'];
				if ($reload=='false') {	$reload = false; } else { $reload = true; }

				// Move page up
				if ($pageMgr->tree->up($pageID) === true) {
					//$koala->callJSFunction('Koala.yg_moveUp', 'page', $pageID.'-'.$siteID, 'name', NULL, $reload);
					$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'PAGE_MOVEUP', sGuiUS(), 'name');
				} else {
					$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
				}
				break;

			case 'moveDownPage':

				$reload = $this->params['reload'];
				if ($reload=='false') {	$reload = false; } else { $reload = true; }

				// Move page down
				if ($pageMgr->tree->down($pageID) === true) {
					//$koala->callJSFunction('Koala.yg_moveDown', 'page', $pageID.'-'.$siteID, 'name', NULL, $reload);
					$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'PAGE_MOVEDOWN', sGuiUS(), 'name');
				} else {
					$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
				}
				break;

			case 'orderPageSubpages':

				$pagelist = $this->params['listArray'];

				$subnodes = $pageMgr->getSubnodes($pageID);

				foreach($subnodes as $sn_idx => $subnode) {
					if($subnode['ID']!=$pagelist[$sn_idx]) {
						$count++;
						if ($count==1) {
							$firstchanged = array($subnode['ID'], $pagelist[$sn_idx]);
						}
						$lastchanged = array($subnode['ID'], $pagelist[$sn_idx]);
					}
				}

				$count = 0;
				if ($firstchanged && $lastchanged) {
					// Find out which item has been dragged
					if ($firstchanged[0]==$lastchanged[1]) {
						$changed = $firstchanged[0];
					} elseif ($firstchanged[1]==$lastchanged[0]) {
						$changed = $firstchanged[1];
					}
					// Check how it has been moved.. (up/down)
					if ($changed) {
						// Get old position
						$count=0;
						foreach($subnodes as $subnode) {
							$count++;
							if ($subnode['ID']==$changed) {
								$oldpos = $count;
							}
						}
						// Get new position
						$count=0;
						foreach($pagelist as $pagelist_item) {
							$count++;
							if ($pagelist_item==$changed) {
								$newpos = $count;
							}
						}
						// Do we have a old and a new position?
						if ($oldpos && $newpos) {
							$difference = $oldpos - $newpos;
							if ($difference<0) {
								$difference = -$difference;
								for ($i=0;$i<$difference;$i++) {
									$pageMgr->tree->down($changed);
								}
								//$koala->movedown('page', $changed.'-'.$siteID, 'name', $difference, false);
								$koala->callJSFunction('Koala.yg_moveDown', 'page', $changed.'-'.$siteID, 'name', $difference, false);
							} else {
								for ($i=0;$i<$difference;$i++) {
									$pageMgr->tree->up($changed);
								}
								//$koala->moveup('page', $changed.'-'.$siteID, 'name', $difference, false);
								$koala->callJSFunction('Koala.yg_moveUp', 'page', $changed.'-'.$siteID, 'name', $difference, false);
							}
						}

					}
				}

				// Add to history
				$page->history->add(HISTORYTYPE_PAGE, NULL, NULL, "TXT_PAGE_H_SUBPAGEORDER");

				// Reselect the node
				//$koala->select('page', $pageID.'-'.$siteID, 'name');
				$koala->callJSFunction('Koala.yg_select', 'page', $pageID.'-'.$siteID, 'name');

				break;

			case 'deletePage':

				$page = $pageMgr->getPage($pageID);
				$successfullyDeleted = $page->delete();
				if (in_array($pageID, $successfullyDeleted) === true) {
					foreach($successfullyDeleted as $successfullyDeletedItem) {
						$tmpPage = $pageMgr->getPage($successfullyDeletedItem);
						if ($tmpPage) {
							$tmpPage->history->add(HISTORYTYPE_PAGE, NULL, NULL, 'TXT_PAGE_H_TRASHED');
							$jsQueue->add ($successfullyDeletedItem, HISTORYTYPE_PAGE, 'OBJECT_DELETE', sGuiUS(), 'page', NULL, NULL, $successfullyDeletedItem.'-'.$siteID, 'name');
						}
					}
				} else {
					$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
				}
				break;

			case 'copyPage':

				$sourcesite = $this->params['sourceSite'];
				$source = $this->params['source'];
				$targetsite = $this->params['targetSite'];
				$target = $this->params['target'];
				$recursive = $this->params['recursive'];
				$parentwindow = $this->params['openerRef'];
				$crosssite = false;
				if ($targetsite != $sourcesite) {
					$crosssite = true;
				}

				$jsQueue = new JSQueue(NULL, $targetsite);
				$extensionMgr = new ExtensionMgr();

				$permissionDenied = false;

				if ($recursive == 0) {
					// Not recursive
					$oldpage = $source;
					$SourcePageMgr = new PageMgr($sourcesite);
					$sourcePage = $SourcePageMgr->getPage($source);
					$oldPageInfo = $sourcePage->get();
					$oldpagepid = $oldPageInfo["ID"];

					$TargetPageMgr = new PageMgr($targetsite);
					$targetPage = $TargetPageMgr->getPage($target);
					$newPageID = $TargetPageMgr->add($target);
					if (!$newPageID) {
						$permissionDenied = true;
					} else {
						$newPage = $TargetPageMgr->getPage($newPageID);
						$newPage->copyFrom($sourcePage);

						// Copy PName (and generate new, if needed)
						$sourcePName = $oldPageInfo['PNAME'];
						if (!$sourcePName) {
							$sourcePName = $oldPageInfo['NAME'];
						}
						if ($TargetPageMgr->getPageIdByPname($sourcePName)) {
							$sourcePName = $newPage->calcPName();
						}
						$newPage->setPName($sourcePName);
						$newPage->setActive(1);

						$jsQueue->add($newPageID, HISTORYTYPE_PAGE, 'PAGE_ADD', sGuiUS(), NULL);

						// Check for extensions to copy
						$all_page_extensions = $extensionMgr->getList( EXTENSION_PAGE, true );
						$used_extensions = array();
						$used_extensions_info = array();
						foreach($all_page_extensions as $all_page_extension) {
							$extension = $extensionMgr->getExtension($all_page_extension["CODE"]);
							if( $extension && $extension->usedByPage($source, $oldPageInfo['VERSION'], $sourcesite) === true ) {
								$tmpExtension = $extensionMgr->getExtension($all_page_extension["CODE"], $source, $oldPageInfo['VERSION'], $sourcesite);
								if ($tmpExtension) {
									array_push($used_extensions_info, $all_page_extension);
									array_push($used_extensions, $tmpExtension);
								}
							}
						}

						foreach($used_extensions as $used_extension_idx => $used_extension) {
							$used_extensions_info[$used_extension_idx]['PROPERTIES'] = $used_extension->properties->getList();
						}

						$newPageInfo = $newPage->get();
						foreach($used_extensions_info as $used_extensions_info_item) {
							$srcExtension = $extensionMgr->getExtension($used_extensions_info_item["CODE"], $source, $oldPageInfo['VERSION'], $sourcesite);
							$trgtExtension = $extensionMgr->getExtension($used_extensions_info_item["CODE"]);
							if ($srcExtension && $trgtExtension) {
								$tmpOId = $trgtExtension->addToPage($newPageID, $newPageInfo['VERSION'], $targetsite);
								$props = $srcExtension->properties->getList('LISTORDER');

								foreach($props as $prop_idx => $prop) {
									foreach($used_extensions_info_item['PROPERTIES'] as $trgtProp) {
										$value = $srcExtension->properties->getValueInternal($trgtProp['IDENTIFIER']);
										$tmpTrgtExtension = $extensionMgr->getExtension($used_extensions_info_item["CODE"], $newPageID, $newPageInfo['VERSION'], $targetsite);
										if ($tmpTrgtExtension->properties) {
											$tmpTrgtExtension->properties->setValue($trgtProp['IDENTIFIER'], $value);
										}
									}
								}
							}
						}

						$newPage->newVersion();
						$newPage->markAsChanged();
					}
				} else {
					// Recursive
					$oldpage = $source;
					$SourcePageMgr = new PageMgr($sourcesite);
					$sourcePage = $SourcePageMgr->getPage($source);
					$oldPageInfo = $sourcePage->get();
					$oldpagepid = $oldPageInfo["ID"];

					$TargetPageMgr = new PageMgr($targetsite);

					$copyjobs = $SourcePageMgr->getList($oldpage);
					$copyjobs = $SourcePageMgr->getAdditionalTreeInfo($SourcePageMgr, $copyjobs);
					$copystarted = false;
					$idmap = array();
					for ($i = 0; $i < count($copyjobs); $i++) {
						if ($copyjobs[$i]["ID"] == $oldpage) {
							if ($copystarted === false) {
								$rootlevel = $copyjobs[$i]["LEVEL"];
								$copystarted = true;

								$newPageID = $TargetPageMgr->add($target);
								if (!$newPageID) {
									$permissionDenied = true;
									break;
								} else {
									$newPage = $TargetPageMgr->getPage($newPageID);
									$newPage->copyFrom($sourcePage);
									$newPage->setActive(1);

									// Copy PName (and generate new, if needed)
									$sourcePName = $oldPageInfo['PNAME'];
									if (!$sourcePName) {
										$sourcePName = $oldPageInfo['NAME'];
									}
									if ($TargetPageMgr->getPageIdByPname($sourcePName)) {
										$sourcePName = $newPage->calcPName();
									}
									$newPage->setPName($sourcePName);

									$jsQueue->add ($newPageID, HISTORYTYPE_PAGE, 'PAGE_ADD', sGuiUS(), NULL);

									$idmap[$oldpage] = $newPageID;

									// Inherit permissions of the parent of the newly created copy
									/*$allPerms = $sourcePage->permissions->getPermissions();
									foreach($allPerms as $permIdx => $permItem) {
										$allPerms[$permIdx]['OID'] = $newPageID;
									}
									$newPage->permissions->clear();
									$newPage->permissions->setPermissions($allPerms);*/

									// copy Extensions
									$newPage->copyExtensionsFrom($sourcePage);
									$newPage->newVersion();
									$newPage->markAsChanged();
									$i++;
								}
							}
						}
						if (($rootlevel < $copyjobs[$i]["LEVEL"]) && ($copystarted === true)) {

							$myid = $copyjobs[$i]["ID"];
							$sourcePage = $SourcePageMgr->getPage($myid);
							$sourcePageInfo = $sourcePage->get();

							$myoldparent = $copyjobs[$i]["PARENT"];
							$mynewparent = $idmap[$myoldparent];
							$newPageID = $TargetPageMgr->add($mynewparent);
							if (!$newPageID) {
								$permissionDenied = true;
								break;
							} else {
								$newPage = $TargetPageMgr->getPage($newPageID);
								$newPage->copyFrom($sourcePage);
								$newPage->setActive(1);

								// Copy PName (and generate new, if needed)
								$sourcePName = $oldPageInfo['PNAME'];
								if (!$sourcePName) {
									$sourcePName = $oldPageInfo['NAME'];
								}
								if ($TargetPageMgr->getPageIdByPname($sourcePName)) {
									$sourcePName = $newPage->calcPName();
								}
								$newPage->setPName($sourcePName);

								$jsQueue->add ($newPageID, HISTORYTYPE_PAGE, 'PAGE_ADD', sGuiUS(), NULL);

								$idmap[$myid] = $newPageID;

								// Inherit permissions of the parent of the newly created copy
								/*$allPerms = $sourcePage->permissions->getPermissions();
								foreach($allPerms as $permIdx => $permItem) {
									$allPerms[$permIdx]['OID'] = $newPageID;
								}
								$newPage->permissions->clear();
								$newPage->permissions->setPermissions($allPerms);
								*/
								// Check for extensions to copy
	/*							$all_page_extensions = $extensionMgr->getList( EXTENSION_PAGE, true );
								$used_extensions = array();
								$used_extensions_info = array();
								foreach($all_page_extensions as $all_page_extension) {
									$extension = $extensionMgr->getExtension($all_page_extension["CODE"]);
									if( $extension && $extension->usedByPage($myid, $sourcePageInfo['VERSION'], $sourcesite) === true ) {
										array_push($used_extensions_info, $all_page_extension);
										array_push($used_extensions, $extension);
									}
								}

								foreach($used_extensions as $used_extension_idx => $used_extension) {
									if ($used_extension->properties) {
										$used_extensions_info[$used_extension_idx]['PROPERTIES'] = $used_extension->properties->getList();
									}
								}

								$newPageInfo = $newPage->get();
								foreach($used_extensions_info as $used_extensions_info_item) {
									$srcExtension = $extensionMgr->getExtension($used_extensions_info_item["CODE"], $myid, $sourcePageInfo['VERSION'], $sourcesite);
									$trgtExtension = $extensionMgr->getExtension($used_extensions_info_item["CODE"]);
									if ($srcExtension && $trgtExtension) {
										$tmpOId = $extension->addToPage($newPageID, $newPageInfo['VERSION'], $targetsite);
										$props = $srcExtension->properties->getList('LISTORDER');

										foreach($props as $prop_idx => $prop) {
											foreach($used_extensions_info_item['PROPERTIES'] as $trgtProp) {
												$value = $srcExtension->properties->getValueInternal($trgtProp['IDENTIFIER']);
												$tmpTrgtExtension = $extensionMgr->getExtension($used_extensions_info_item["CODE"], $newPageID, $newPageInfo['VERSION'], $targetsite);
												if ($tmpTrgtExtension->properties) {
													$tmpTrgtExtension->properties->setValue($trgtProp['IDENTIFIER'], $value);
												}
											}
										}
									}
								}*/
								$newPage->copyExtensionsFrom($sourcePage);
								$newPage->newVersion();
								$newPage->markAsChanged();
							}
						}
						if ($rootlevel >= $copyjobs[$i]["LEVEL"]) {
							if ($copystarted === true) {
								break;
							}
						}
					}
				}
				if ($permissionDenied) {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				} else {
					if (!$crosssite) {
						$koala->callJSFunction('Koala.yg_reloadTree', $parentwindow, 'page', $source);
					}
				}
				break;

			case 'movePage':

				$sourcesite = $this->params['sourceSite'];
				$source = $this->params['source'];
				$targetsite = $this->params['targetSite'];
				$target = $this->params['target'];
				$parentwindow = $this->params['openerRef'];
				$before = $this->params['before'];
				$crosssite = false;
				$hasRights = true;
				if ($targetsite != $sourcesite) {
					$crosssite = true;
				}

				if ( ($sourcesite == $targetsite) &&
					 ($source == $target) ) {
					break;
				}

				$SourcePageMgr = new PageMgr($sourcesite);
				$sourcePage = $SourcePageMgr->getPage($source);
				$oldPageInfo = $sourcePage->get();
				if ($sourcesite != $targetsite) {

					$oldpage = $source;
					$SourcePageMgr = new PageMgr($sourcesite);
					$sourcePage = $SourcePageMgr->getPage($source);
					$oldPageInfo = $sourcePage->get();
					$oldpagepid = $oldPageInfo["ID"];

					$TargetPageMgr = new PageMgr($targetsite);
					$rSub = $TargetPageMgr->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $target, "RSUB" );
					$rDelete = $SourcePageMgr->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $source, "RDELETE" );
					$rWrite = $SourcePageMgr->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $source, "RWRITE" );
					if ($rSub && $rDelete && $rWrite) {
						$copyjobs = $SourcePageMgr->getList($oldpage);
						$copystarted = false;
						$idmap = array();
						for ($i = 0; $i < count($copyjobs); $i++) {
							if ($copyjobs[$i]["ID"] == $oldpage) {
								if ($copystarted === false) {
									$rootlevel = $copyjobs[$i]["LEVEL"];
									$copystarted = true;
									$newPageID = $TargetPageMgr->add($target);
									$newPage = $TargetPageMgr->getPage($newPageID);
									$newPage->copyFrom($sourcePage);
									$idmap[$oldpage] = $newPageID;

									// Inherit permissions of the parent of the newly created copy
									$allPerms = $sourcePage->permissions->getPermissions();
									$newPage->permissions->clear();
									$newPage->permissions->setPermissions($allPerms, $newPageID);

									// Copy blind contentblocks
									$pageInfo = $sourcePage->get();
									$contentareas = $templateMgr->getContentareas($pageInfo['TEMPLATEID']);
									for ($j = 0; $j < count($contentareas); $j++) {
										$pagelist = $sourcePage->getCblockList($contentareas[$j]['CODE']);
										for ($x = 0; $x < count($pagelist);$x++) {
											$coid = $pagelist[$x]['OBJECTID'];

											// Check if we have a blind contentblock
											if ($pagelist[$x]['EMBEDDED']==1) {
												// Yes, we have to copy it to the blind folder

												// Check which entrymasks are contained
												$sourcecb = sCblockMgr()->getCblock($coid);
												$src_co = $sourcecb->get();
												$src_entrymasks = $sourcecb->getEntrymasks();

												// Create blind contentblocks with these entrymasks
												foreach ($src_entrymasks as $src_entrymask_item) {

													// Add new contentblock to folder
													$contentblockID = $newPage->addCblockEmbedded($contentareas[$j]['CODE']);
													$newcb = sCblockMgr()->getCblock($contentblockID);
													$newcb->properties->setValue ("NAME", $src_entrymask_item['ENTRYMASKNAME']);

													// Add requested control to contentblock
													$new_control = $newcb->addEntrymask($src_entrymask_item['ENTRYMASKID']);

													// Get the LinkId of the newly created contentblock
													$new_colnkid = $newPage->getEmbeddedCblockLinkId($contentblockID);

													// Loop through all formfields
													$controlFormfields = $sourcecb->getFormfieldsInternal($src_entrymask_item['LINKID']);
													$newControlFormfields = $newcb->getFormfieldsInternal($new_control);

													// Fill all formfield parameter values with content from the source formfield
													for ($c = 0; $c < count($newControlFormfields); $c++) {
														$newcb->setFormfield($newControlFormfields[$c]['LINKID'],
																$controlFormfields[$c]['VALUE01'],
																$controlFormfields[$c]['VALUE02'],
																$controlFormfields[$c]['VALUE03'],
																$controlFormfields[$c]['VALUE04'],
																$controlFormfields[$c]['VALUE05'],
																$controlFormfields[$c]['VALUE06'],
																$controlFormfields[$c]['VALUE07'],
																$controlFormfields[$c]['VALUE08']
														);
													}

												}

											} else {
												// No, it's a normal one, just link it to the page
												$newPage->addCblockLink ($coid, $contentareas[$j]['CODE']);
											}

										}
									}

									$i++;
								}
							}
							if (($rootlevel < $copyjobs[$i]["LEVEL"]) && ($copystarted === true)) {

								$myid = $copyjobs[$i]["ID"];
								$sourcePage = $SourcePageMgr->getPage($myid);

								$myoldparent = $copyjobs[$i]["PARENT"];
								$mynewparent = $idmap[$myoldparent];

								$newPageID = $TargetPageMgr->add($mynewparent);
								$newPage = $TargetPageMgr->getPage($newPageID);
								$newPage->copyFrom($sourcePage);

								$idmap[$myid] = $newPageID;

								// Inherit permissions of the parent of the newly created copy
								$allPerms = $sourcePage->permissions->getPermissions();
								$newPage->permissions->clear();
								$newPage->permissions->setPermissions($allPerms);

								// Copy blind contentblocks
								$pageInfo = $sourcePage->get();

								$contentareas = $templateMgr->getContentareas($pageInfo['TEMPLATEID']);

								for ($j = 0; $j < count($contentareas); $j++) {
									$pagelist = $sourcePage->getCblockList($contentareas[$j]['CODE']);
									for ($x = 0; $x < count($pagelist);$x++) {
										$coid = $pagelist[$x]['OBJECTID'];

										// Check if we have a blind contentblock
										if ($pagelist[$x]['EMBEDDED']==1) {
											// Yes, we have to copy it to the blind folder

											// Check which entrymasks are contained
											$sourcecb = sCblockMgr()->getCblock($coid);
											$src_co = $sourcecb->get();
											$src_entrymasks = $sourcecb->getEntrymasks();

											// Create blind contentblocks with these entrymasks
											foreach ($src_entrymasks as $src_entrymask_item) {

												// Add new contentblock to folder
												$contentblockID = $newPage->addCblockEmbedded($contentareas[$j]['CODE']);
												$newcb = sCblockMgr()->getCblock($contentblockID);
												$newcb->properties->setValue ("NAME", $src_entrymask_item['ENTRYMASKNAME']);

												// Add requested control to contentblock
												$new_control = $newcb->addEntrymask($src_entrymask_item['ENTRYMASKID']);

												// Get the LinkId of the newly created contentblock
												$new_colnkid = $newPage->getEmbeddedCblockLinkId($contentblockID);

												// Loop through all formfields
												$controlFormfields = $sourcecb->getFormfieldsInternal($src_entrymask_item['LINKID']);
												$newControlFormfields = $newcb->getFormfieldsInternal($new_control);

												// Fill all formfield parameter values with content from the source formfield
												for ($c = 0; $c < count($newControlFormfields); $c++) {
													$newcb->setFormfield($newControlFormfields[$c]['LINKID'],
															$controlFormfields[$c]['VALUE01'],
															$controlFormfields[$c]['VALUE02'],
															$controlFormfields[$c]['VALUE03'],
															$controlFormfields[$c]['VALUE04'],
															$controlFormfields[$c]['VALUE05'],
															$controlFormfields[$c]['VALUE06'],
															$controlFormfields[$c]['VALUE07'],
															$controlFormfields[$c]['VALUE08']
													);
												}

											}

										} else {
											// No, it's a normal one, just link it to the page
											$newPage->addCblockLink ($coid, $contentareas[$j]['CODE']);
										}

									}
								}
							}
							if ($rootlevel >= $copyjobs[$i]["LEVEL"]) {
								if ($copystarted === true) {
									break;
								}
							}
						}

						// Remove source page
						$sourcePage = $SourcePageMgr->getPage($source);
						$sourcePage->delete();
						$SourcePageMgr->remove($source);
						$jsQueue = new JSQueue(NULL, $sourcesite);
						$jsQueue->add ($source, HISTORYTYPE_PAGE, 'OBJECT_DELETE', sGuiUS(), 'page', NULL, NULL, $source.'-'.$sourcesite, 'name');
					} else {
						$hasRights = false;
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					}

				} elseif (($before != true)||($target=='trash')) {

					// If dragging to folder or trash
					$pageMgr = new PageMgr($sourcesite);

					// Special case for trash
					if ($target=='trash') {
						// Get rootnode and set it as target
						$pagesList = $pageMgr->getTree($node_id, 2);
						$target = $pagesList[0]['ID'];
					}

					// Check if source-parent and target have the same id
					$tmpSource = $pageMgr->getPage($source);
					$tmpSourceInfo = $tmpSource->get();
					if ($tmpSourceInfo['PARENT'] == $target) {
						// Do nothing
					} elseif ($pageMgr->tree->moveTo($source, $target)) {
						// Move object down, so that it is the last element
						$nextChildNodes = $pageMgr->tree->getDirectChildren($target);

						$newTarget = $nextChildNodes[count($nextChildNodes)-1]['ID'];

						// Move element down, so that it is the last element in the target level
						// Check if we are already to the left of the target-node
						$arrived = false;
						while (!$arrived) {
							$iterations++;
							$targetLeft = $pageMgr->tree->getLeft($newTarget);
							if ($targetLeft!=$source) {
								$pageMgr->tree->down($source);
							} else {
								$arrived = true;
								break;
							}
							if ($iterations>400) {
								$arrived = true; break;
							}
						}

						//$koala->callJSFunction('Koala.yg_reloadTree', $parentwindow, 'page');
					} else {
						$hasRights = false;
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					}
				} elseif ($before) {

					$pageMgr = new PageMgr($targetsite);

					// Get parent of target-node
					$targetParent = $pageMgr->tree->getParent($target);

					// Move source to parent of target-node
					if ($pageMgr->tree->moveTo($source, $targetParent)) {
						// Inherit permissions of the parent of the newly created copy
						$sourcePage = $pageMgr->getPage($source);
						$sourcePage->permissions->clear();
						$pageMgr->permissions->copyTo($targetParent, $source);

						// Get left node of target-node
						$targetLeft = $pageMgr->tree->getLeft($target);

						// Check if we have to move down, or do we have to move up?
						$children = $pageMgr->tree->getDirectChildren($targetParent);

						foreach($children as $children_idx => $children_item) {
							if ($children_item['ID'] == $target) {
								$target_idx = $children_idx;
							}
							if ($children_item['ID'] == $source) {
								$source_idx = $children_idx;
							}
						}

						$movedirection = 'up';
						if ($target_idx > $source_idx) {
							$movedirection = 'down';
						}

						// Check if we are already to the left of the target-node
						$arrived = false;
						while (!$arrived) {
							$iterations++;
							// Get left node of target-node
							$targetLeft = $pageMgr->tree->getLeft($target);

							if ($targetLeft!=$source) {
								// We're not to the left of the target, so we have to move our source-node up in the tree
								if ($movedirection == 'up')
									$pageMgr->tree->up($source);
								elseif($movedirection == 'down')
								$pageMgr->tree->down($source);
							} else {
								$arrived = true;
								break;
							}
							// Last resort...
							if ($iterations>400) {
								$arrived = true; break;
							}
						}
					} else {
						$hasRights = false;
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					}
				}

				if ($hasRights) {
					$parent_ids = array();
					$pageMgr = new PageMgr($sourcesite);
					$parents = $pageMgr->getParents($source);

					foreach($parents as $parent_item) {
						array_push($parent_ids, $parent_item[0]['ID']);
					}
					$parent_ids = array_reverse($parent_ids);
					array_shift($parent_ids);
					array_push($parent_ids, $source);
					$parent_ids = implode(',', $parent_ids);

					if ($this->params['orgAction'] == 'restore') {
						if ($crosssite) {
							$restoredPage = $TargetPageMgr->getPage($newPageID);
							$restoredPage->undelete();
							$restoredPageID = $newPageID;
						} else {
							$restorePageMgr = new PageMgr($sourcesite);
							$restoredPage = $restorePageMgr->getPage($source);
							$restoredPage->undelete();
							$restoredPageID = $source;
						}
						$restoredPage->history->add(HISTORYTYPE_PAGE, NULL, NULL, 'TXT_PAGE_H_RESTORED');
						if ($this->params['lastItem']=='true') {
							$koala->queueScript('Koala.windows[\''.$parentwindow.'\'].tabs.select(Koala.windows[\''.$parentwindow.'\'].tabs.selected,{refresh:1});');
						}
					} elseif ($this->params['orgAction'] == 'move') {
						if (!$crosssite) {
							$koala->callJSFunction('Koala.yg_reloadTree', $parentwindow, 'page');
							$koala->callJSFunction('Koala.yg_expandTreeNodes', $parentwindow, 'page', $parent_ids, $source, 'true');
							$koala->callJSFunction('Koala.yg_selectTreeNode', $parentwindow, 'page', $source);
						}
					} else {
						$koala->callJSFunction( 'if (typeof(Koala.yg_dndOnSuccess) == "function") Koala.yg_dndOnSuccess' );
						//$koala->callJSFunction('Koala.yg_expandTreeNodes', $parentwindow, 'pages', $parent_ids);
						$koala->callJSFunction('Koala.yg_selectTreeNode', $parentwindow, 'page', $source);
					}

					$SourcePageMgr = new PageMgr($sourcesite);
					$sourcePage = $SourcePageMgr->getPage($source);

					$icons = new Icons();
					if ($oldPageInfo['ACTIVE']) {
						$pageicon = $icons->icon['page_small'];
						if ($oldPageInfo['HIDDEN']) {
							$pageicon = $icons->icon['page_hidden_small'];
						}
					} else {
						$pageicon = $icons->icon['page_inactive_small'];
						if ($oldPageInfo['HIDDEN']) {
							$pageicon = $icons->icon['page_inactive_hidden_small'];
						}
					}

					$jsQueue = new JSQueue(NULL, $sourcesite);

					if ($before) {
						$jsQueue->add ($source, HISTORYTYPE_PAGE, 'PAGE_MOVE', sGuiUS(), $target.'-'.$targetsite, 1);
					} else {
						$jsQueue->add ($source, HISTORYTYPE_PAGE, 'PAGE_MOVE', sGuiUS(), $target.'-'.$targetsite);
					}
				}
				break;

			case 'pageSelectNode':

				$node = $this->params['node'];
				$siteID = $this->params['siteID'];
				$wid = $this->params['wid'];

				if ($node == 'trash') break;

				// Pages
				$pageMgr = new PageMgr($siteID);

				$root_node = $pageMgr->getTree(NULL, 0);
				$page = $pageMgr->getPage($node);

				// 1 = rsub
				// 2 = rread
				// 3 = rdelete
				// 4 = parent -> rsub & rwrite
				// 5 = parent -> rsub & rwrite
				// 6 = rdelete
				$buttons = array();

				// Get Parents
				$parents = $pageMgr->getParents($node);
				$parentid = $parents[0][0]['ID'];

				// Check rights
				$rread = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $node, "RREAD");
				$rwrite = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $node, "RWRITE");
				$rsub = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $node, "RSUB");
				$rdelete = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $node, "RDELETE");
				$rstage = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $node, "RSTAGE");

				// Check rights of parents
				$prsub = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $parentid, "RSUB");
				$prwrite = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $parentid, "RWRITE");

				// Check permissions for button "add"
				if ($rsub) {
					$buttons[0] = true;
				} else {
					$buttons[0] = false;
				}

				// Check permissions for button "copy"
				if ($rread) {
					$buttons[1] = true;
				} else {
					$buttons[1] = false;
				}

				// Check permissions for button "move"
				if ($rwrite) {
					$buttons[2] = true;
				} else {
					$buttons[2] = false;
				}

				// Check permissions for button "up" & "down"
				if ($prsub && $prwrite) {
					$buttons[3] = true;
					$buttons[4] = true;
				} else {
					$buttons[3] = false;
					$buttons[4] = false;
				}

				// Check permissions for button "delete"
				if ($rdelete) {
					$buttons[5] = true;
				} else {
					$buttons[5] = false;
				}

				// Check if rootnode (and disable copy, move and delete)
				if ($root_node[0]['ID']==$node) {
					$buttons[1] = false;
					$buttons[2] = false;
					$buttons[3] = false;
					$buttons[4] = false;
					$buttons[5] = false;
				}

				// Finally enable/Disable them
				if (($buttons[0]===true)||($node=='root')) {
					$koala->callJSFunction('Koala.yg_enable', 'tree_btn_add', 'btn-'.$wid, 'tree_btn');
				} else {
					$koala->callJSFunction('Koala.yg_disable', 'tree_btn_add', 'btn-'.$wid, 'tree_btn');
				}

				if ($buttons[1]===true) {
					$koala->callJSFunction('Koala.yg_enable', 'tree_btn_copy', 'btn-'.$wid, 'tree_btn');
				} else {
					$koala->callJSFunction('Koala.yg_disable', 'tree_btn_copy', 'btn-'.$wid, 'tree_btn');
				}

				if ($buttons[2]===true) {
					$koala->callJSFunction('Koala.yg_enable', 'tree_btn_move', 'btn-'.$wid, 'tree_btn');
				} else {
					$koala->callJSFunction('Koala.yg_disable', 'tree_btn_move', 'btn-'.$wid, 'tree_btn');
				}

				if (($buttons[3]===true) && ($buttons[4]===true)) {
					$koala->callJSFunction('Koala.yg_enable', 'tree_btn_up', 'btn-'.$wid, 'tree_btn');
					$koala->callJSFunction('Koala.yg_enable', 'tree_btn_down', 'btn-'.$wid, 'tree_btn');
				} else {
					$koala->callJSFunction('Koala.yg_disable', 'tree_btn_up', 'btn-'.$wid, 'tree_btn');
					$koala->callJSFunction('Koala.yg_disable', 'tree_btn_down', 'btn-'.$wid, 'tree_btn');
				}

				if ($buttons[5]===true) {
					$koala->callJSFunction('Koala.yg_enable', 'tree_btn_delete', 'btn-'.$wid, 'tree_btn');
				} else {
					$koala->callJSFunction('Koala.yg_disable', 'tree_btn_delete', 'btn-'.$wid, 'tree_btn');
				}
				break;

	}

?>