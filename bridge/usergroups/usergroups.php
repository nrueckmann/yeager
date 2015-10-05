<?php

	\framework\import('org.phpmailer.phpmailer');

	$jsQueue = new JSQueue(NULL);

	switch ($action) {

			case 'deleteUserGroup':
				$mode = $this->params['mode'];
				if ($mode == 'mailings') {
					// remove usergroup from mailing
					$mailingId = $this->params['mailingId'];
					$roleId = $this->params['roleId'];
					$roleInfo = sUsergroups()->get( $roleId );
					$wid = $this->params['wid'];

					$mailingMgr = new MailingMgr();
					$mailing = $mailingMgr->getMailing($mailingId);
					$mailingInfo = $mailing->get();
					$mailingStatus = $mailing->getStatus();

					// First check if mailing is PAUSED
					if ($mailingStatus['STATUS'] == 'PAUSED') {
						// Empty queue for this mailing and reset to UNSENT
						// Get # of pending jobs
						$queuedJobs = $mailingMgr->scheduler->getQueuedJobsForObject($mailingId, true, 'SCH_EMAILSEND');

						// Get # of receipients for this mailing
						$mailing = $mailingMgr->getMailing($mailingId);
						$latestFinalVersion = $mailing->getLatestApprovedVersion();
						$mailing = $mailingMgr->getMailing($mailingId, $latestFinalVersion);
						$mailingGroups = $mailing->getUsergroups();

						$receipients = 0;
						$userIds = array();
						foreach($mailingGroups as $mailingGroup) {
							// Get # of users in this group
							$usersInRole = sUserMgr()->getByUsergroup($mailingGroup['ID']);
							foreach($usersInRole as $usersInRoleItem) {
								array_push($userIds, $usersInRoleItem['UID']);
							}
						}
						$userIds = array_unique($userIds);
						$receipients = count($userIds);

						// Cancel all pending jobs
						$mailingMgr->scheduler->cancelAllQueuedJobsForObject($mailingId);

						// Set status of mailing
						$mailing->setStatus('UNSENT');

						// Log history
						$mailing->history->add(HISTORYTYPE_MAILING, $latestFinalVersion, ((int)$receipients - (int)$queuedJobs).' / '.$receipients, 'TXT_MAILING_H_CANCELLED');
					}
					$mailing->removeUsergroup($roleId);

					// Update usergrouplists in listview
					$usergroups = $mailing->getUsergroups();

					$receipients = 0;
					$userIds = array();
					foreach($usergroups as $mailingGroup) {
						// Get # of users in this group
						$usersInRole = sUserMgr()->getByUsergroup($mailingGroup['ID']);
						foreach($usersInRole as $usersInRoleItem) {
							array_push($userIds, $usersInRoleItem['UID']);
						}
					}
					$userIds = array_unique($userIds);
					$receipients = count($userIds);

					$usergroupList = '';
					foreach($usergroups as $usergroupsIdx => $usergroupsItem) {
						$usergroupList .= $usergroupsItem['NAME'];
						if (($usergroupsIdx+1) != count($usergroups)) {
							$usergroupList .= ', ';
						}
					}

					$koala->queueScript( 'if ($(\''.$wid.'_usergroups_'.$roleId.'\')) { $(\''.$wid.'_usergroups_'.$roleId.'\').remove(); $K.windows[\''.$wid.'\'].refresh(); }' );

					// Set status of page to changed
					$jsQueue->add ($mailingId, HISTORYTYPE_MAILING, 'HIGHLIGHT_MAILING', sGuiUS(), 'name');

					// Add to history
					$mailing->history->add(HISTORYTYPE_MAILING, NULL, $roleInfo['NAME'], "TXT_MAILING_H_GROUPREMOVE");

					$jsQueue->add ($mailingId, HISTORYTYPE_MAILING, 'OBJECT_CHANGE', sGuiUS(), 'mailing', NULL, NULL, $mailingId.'-mailing', 'yg_usergrouplist', $usergroupList);
					$jsQueue->add ($mailingId, HISTORYTYPE_MAILING, 'OBJECT_CHANGE', sGuiUS(), 'mailing', NULL, NULL, $mailingId.'-mailing', 'receipients', $receipients);

				} else {
					// remove usergroup from user
					$userId = $this->params['userId'];
					$roleId = $this->params['roleId'];
					$wid = $this->params['wid'];

					$user = new User($userId);
					$user->removeUsergroup($roleId);

					$koala->queueScript( 'if ($(\''.$wid.'_usergroups_'.$roleId.'\')) { $(\''.$wid.'_usergroups_'.$roleId.'\').remove(); $K.windows[\''.$wid.'\'].refresh(); }; $K.yg_showHelp(false);' );
				}
				break;

			case 'addUsergroup':
				$roleId = $this->params['roleId'];
				$roleInfo = sUsergroups()->get( $roleId );
				$roleName = $roleInfo['NAME'];

				$mode = $this->params['mode'];
				$openerRefId = $this->params['openerRefId'];
				$refresh = $this->params['refresh'];

				if ($mode == 'mailing') {
					// For mailings
					$mailingId = $this->params['mailingId'];

					$mailingMgr = new MailingMgr();
					$mailing = $mailingMgr->getMailing($mailingId);
					$mailingInfo = $mailing->get();
					$mailingStatus = $mailing->getStatus();

					$currentRoles = $mailing->getUsergroups();

					$addRole = true;
					foreach($currentRoles as $currentRoles_item) {
						if ($currentRoles_item['ID'] == $roleId) {
							$addRole = false;
						}
					}
					if ($addRole) {
						// First check if mailing is PAUSED
						if ($mailingStatus['STATUS'] == 'PAUSED') {
							// Empty queue for this mailing and reset to UNSENT
							// Get # of pending jobs
							$queuedJobs = $mailingMgr->scheduler->getQueuedJobsForObject($mailingId, true, 'SCH_EMAILSEND');

							// Get # of receipients for this mailing
							$mailing = $mailingMgr->getMailing($mailingId);
							$latestFinalVersion = $mailing->getLatestApprovedVersion();
							$mailing = $mailingMgr->getMailing($mailingId, $latestFinalVersion);
							$mailingGroups = $mailing->getUsergroups();

							$receipients = 0;
							$userIds = array();
							foreach($mailingGroups as $mailingGroup) {
								// Get # of users in this group
								$usersInRole = sUserMgr()->getByUsergroup($mailingGroup['ID']);
								foreach($usersInRole as $usersInRoleItem) {
									array_push($userIds, $usersInRoleItem['UID']);
								}
							}
							$userIds = array_unique($userIds);
							$receipients = count($userIds);

							// Cancel all pending jobs
							$mailingMgr->scheduler->cancelAllQueuedJobsForObject($mailingId);

							// Set status of mailing
							$mailing->setStatus('UNSENT');

							// Log history
							$mailing->history->add(HISTORYTYPE_MAILING, $latestFinalVersion, ((int)$receipients - (int)$queuedJobs).' / '.$receipients, 'TXT_MAILING_H_CANCELLED');
						}

						$mailing->addUsergroup($roleId, $mailingInfo['VERSION']);
						$koala->queueScript( 'if ($K.windows[\''.$openerRefId.'\'] && (typeof $K.windows[\''.$openerRefId.'\'].addToSortable == \'function\')) $K.windows[\''.$openerRefId.'\'].addToSortable( \''.$roleId.'\', \''.$roleName.'\', \'\', \'\' );' );

						// Update usergrouplists in listview
						$usergroups = $mailing->getUsergroups();

						$receipients = 0;
						$userIds = array();
						foreach($usergroups as $mailingGroup) {
							// Get # of users in this group
							$usersInRole = sUserMgr()->getByUsergroup($mailingGroup['ID']);
							foreach($usersInRole as $usersInRoleItem) {
								array_push($userIds, $usersInRoleItem['UID']);
							}
						}
						$userIds = array_unique($userIds);
						$receipients = count($userIds);

						$usergroupList = '';
						foreach($usergroups as $usergroupsIdx => $usergroupsItem) {
							$usergroupList .= $usergroupsItem['NAME'];
							if (($usergroupsIdx+1) != count($usergroups)) {
								$usergroupList .= ', ';
							}
						}

						// Set status of page to changed
						$jsQueue->add ($mailingId, HISTORYTYPE_MAILING, 'HIGHLIGHT_MAILING', sGuiUS(), 'name');

						// Add to history
						$mailing->history->add(HISTORYTYPE_MAILING, NULL, $roleInfo['NAME'], "TXT_MAILING_H_GROUPADD");

						$jsQueue->add ($mailingId, HISTORYTYPE_MAILING, 'OBJECT_CHANGE', sGuiUS(), 'mailing', NULL, NULL, $mailingId.'-mailing', 'yg_usergrouplist', $usergroupList);
						$jsQueue->add ($mailingId, HISTORYTYPE_MAILING, 'OBJECT_CHANGE', sGuiUS(), 'mailing', NULL, NULL, $mailingId.'-mailing', 'receipients', $receipients);
					}
				} else if ($mode == 'user') {
					// For users
					$userId = $this->params['userId'];
					$user = new User($userId);
					$currentRoles = $user->getUsergroups( $userId );

					$addRole = true;
					foreach($currentRoles as $currentRoles_item) {
						if ($currentRoles_item['ID'] == $roleId) {
							$addRole = false;
						}
					}
					if ($addRole) {
						$user->addUsergroup($roleId);
						$koala->queueScript( 'if ($K.windows[\''.$openerRefId.'\'] && (typeof $K.windows[\''.$openerRefId.'\'].addToSortable == \'function\')) $K.windows[\''.$openerRefId.'\'].addToSortable( \''.$roleId.'\', \''.$roleName.'\', \'\', \'\' );' );
					}
				}
				break;

			case 'savePermissions':
				$objectType = strtolower($this->params['objectType']);
				$winID = $this->params['winID'];
				$roleID = $this->params['roleID'];
				$currentSite = $this->params['currentSite'];
				$templateMgr = new Templates();

				$embeddedCblockFolder = (int)sConfig()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");

				$systemPermissionMapping = array(
					1  => 'RPAGES',
					2  => 'RCONTENTBLOCKS',
					3  => 'RFILES',
					4  => 'RTAGS',
					5  => 'RUSERS',
					6  => 'RUSERGROUPS',
					7  => 'REXTENSIONS_PAGE',
					8  => 'RIMPORT',
					9  => 'REXPORT',
					10 => 'RDATA',
					11 => 'RSITES',
					12 => 'RTEMPLATES',
					13 => 'RENTRYMASKS',
					14 => 'RPROPERTIES',
					15 => 'RFILETYPES',
					16 => 'RVIEWS',
					17 => 'RCOMMENTCONFIG',
					18 => 'REXTENSIONS_CBLISTVIEW',
					19 => 'RMAILINGS',
					20 => 'RMAILINGCONFIG',
					21 => 'RCOMMENTS',
					22 => 'REXTENSIONS_MAILING',
					23 => 'REXTENSIONS_FILE',
					24 => 'REXTENSIONS_CBLOCK',
					25 => 'RUPDATER',
					99 => 'RBACKEND'
				);

				// Extension Privileges
				$extensionMgr = new ExtensionMgr();
				$extensions = $extensionMgr->getList(0, true, true);
				foreach($extensions as $extensionItem) {
					$extPrivileges = sUsergroups()->permissions->getList( $extensionItem['CODE'] );
					if (count($extPrivileges) > 0) {
						foreach($extPrivileges as $extPrivilegeItem) {
							$systemPermissionMapping[(int)$extPrivilegeItem['ID'] + 1000] = $extPrivilegeItem['PRIVILEGE'];
						}
					}
				}

				$backendObject = NULL;
				switch($objectType) {
					case 'pages':
						$pageMgr = new PageMgr($currentSite);
						$backendObject = &$pageMgr;
						$embcbperm = array();
						break;
					case 'mailings':
						$mailingMgr = new MailingMgr();
						$backendObject = &$mailingMgr;
						$embcbperm = array();
						break;
					case 'cblocks':
						$backendObject = sCblockMgr();
						break;
					case 'files';
						$fileMgr = sFileMgr();
						$backendObject = $fileMgr;
						break;
					case 'tags':
						$tagMgr = new Tags();
						$backendObject = $tagMgr;
						break;
					case 'general':
						$objectName = $this->params['objectName'];
						if ( sUsergroups()->setName( $roleID, $objectName ) === false ) {
							$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
						} else {
							$jsQueue->add ($roleID, HISTORYTYPE_USERGROUP, 'OBJECT_CHANGE', sGuiUS(), 'usergroup', NULL, NULL, $roleID.'-usergroup', 'name', $objectName);
							$jsQueue->add ($roleID, HISTORYTYPE_USERGROUP, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $roleID.'-usergroup', 'name', $objectName);
							$jsQueue->add ($roleID, HISTORYTYPE_USERGROUP, 'Koala.yg_resortRolesList(\''.$winID.'\');', sGuiUS());
						}
						break;
				}

				// Special check: if RBACKEND is set, set all rights
				if ( ($objectType == 'general') &&
					 (($this->params['perm_rread_99_'] === '1') || ($this->params['perm_rread_99_'] === '0')) ) {
					foreach($systemPermissionMapping as $currPermission) {
						sUsergroups()->permissions->setByUsergroup($roleID, $currPermission, $this->params['perm_rread_99_']);
					}
				}
				$nodepermissions = array();
				$npi = 0;
				foreach($this->params as $permParamIdx => $permParam) {
					if (strpos($permParamIdx, 'perm_') === 0) {
						$currentPermissionData = strtoupper(str_replace('perm_', '', $permParamIdx));
						$currentPermissionDataArray = explode('_', $currentPermissionData);
						$currentPermission = $currentPermissionDataArray[0];
						$currentObjectId = $currentPermissionDataArray[1];
						$nodepermissions[$currentObjectId]["USERGROUPID"] = $roleID;
						$nodepermissions[$currentObjectId]["OID"] = $currentObjectId;
						$npi = $currentObjectId;

						switch($objectType) {
							case 'usergroups':
								sUsergroups()->usergroupPermissions->setByUsergroup($roleID, $currentPermission, $currentObjectId, $permParam);
								// If RREAD is removed, remove all other permissions too!
								if ( ($currentPermission == 'RREAD') && ($permParam == 0) ) {
									sUsergroups()->usergroupPermissions->setByUsergroup($roleID, 'RWRITE', $currentObjectId, $permParam);
									sUsergroups()->usergroupPermissions->setByUsergroup($roleID, 'RDELETE', $currentObjectId, $permParam);
								}
								// If any right is added, also add RREAD too!
								if ( ($currentPermission != 'RREAD') && ($permParam == 1) ) {
									sUsergroups()->usergroupPermissions->setByUsergroup($roleID, 'RREAD', $currentObjectId, $permParam);
								}
								// If RDELETE is added, also add RWRITE too!
								if ( ($currentPermission == 'RDELETE') && ($permParam == 1) ) {
									sUsergroups()->usergroupPermissions->setByUsergroup($roleID, 'RDELETE', $currentObjectId, $permParam);
								}
								break;
							case 'general':
								sUsergroups()->permissions->setByUsergroup($roleID, $systemPermissionMapping[$currentObjectId], $permParam);

								// Always set RREAD on rootnode if something is set
								if ($permParam == 1) {
									sUsergroups()->permissions->setByUsergroup($roleID, 'RBACKEND', $permParam);
								}
								break;
						}
						if ($backendObject) {
							/*
							if ( ($objectType == 'pages') && ($permParam == 0) ) {
								$page = $backendObject->getPage($currentObjectId);
								if ($page) {
									$pageInfo = $page->get();
									$contentareas = $templateMgr->getContentareas( $pageInfo['TEMPLATEID'] );
								}
							}
							*/
							if (($objectType != 'cblocks') || ($currentObjectId != $embeddedCblockFolder)) {
								$nodepermissions[$npi][$currentPermission] = $permParam;
							}
							/*
							if ( ($objectType == 'pages') && ($permParam == 1) ) {
								$page = $backendObject->getPage($currentObjectId);
								if ($page) {
									$pageInfo = $page->get();
									$contentareas = $templateMgr->getContentareas( $pageInfo['TEMPLATEID'] );
								}
							}
							*/
							// If RREAD is removed, remove all other permissions too!
							if ( ($currentPermission == 'RREAD') && ($permParam == 0) ) {
								if (($objectType != 'cblocks') || ($currentObjectId != $embeddedCblockFolder)) {
									$nodepermissions[$npi]['RWRITE'] = $permParam;
									$nodepermissions[$npi]['RDELETE'] = $permParam;
									if ($objectType != 'usergroups') {
										if ($objectType != 'mailings') {
											$nodepermissions[$npi]['RSUB'] = $permParam;
										}
										if (($objectType == 'pages') || ($objectType == 'cblocks')) {
											$nodepermissions[$npi]['RSTAGE'] = $permParam;
										}
										if (($objectType == 'pages') || ($objectType == 'cblocks') || ($objectType == 'files')) {
											$nodepermissions[$npi]['RMODERATE'] = $permParam;
											$nodepermissions[$npi]['RCOMMENT'] = $permParam;
										}
										if ($objectType == 'mailings') {
											$nodepermissions[$npi]['RSEND'] = $permParam;
										}
									}
								}
							}
							// If any right is added, also add RREAD too!
							if ( ($currentPermission != 'RREAD') && ($permParam == 1) ) {
								if (($objectType != 'cblocks') || ($currentObjectId != $embeddedCblockFolder)) {
									$nodepermissions[$npi]['RREAD'] = $permParam;
									/*
									$rootNode = $backendObject->tree->getRoot();
									$backendObject->permissions->setByUsergroup($roleID, 'RREAD', $rootNode, $permParam);
									*/
								}
							}
							// If RDELETE is added, also add RWRITE
							if ( ($currentPermission == 'RDELETE') &&
								 ($permParam == 1) ) {
								$nodepermissions[$npi]['RWRITE'] = $permParam;
							}
							// For Files, when RWRITE is added, also add RSTAGE permissions
							if ( ($objectType == 'files') && ($currentPermission == 'RWRITE') && ($permParam == 1) ) {
								$nodepermissions[$npi]['RSTAGE'] = $permParam;
							}
							// Always set RREAD on all parent nodes if something is set
							if ($permParam == 1) {
								$parentNodes = $backendObject->tree->getParents($npi);
								foreach($parentNodes as $parentNode) {
									if ($parentNode > 0) {
										$nodepermissions[$parentNode]['RREAD'] = $permParam;
										$nodepermissions[$parentNode]['OID'] = $parentNode;
										$nodepermissions[$parentNode]['USERGROUPID'] = $roleID;
									}
								}
							}

							$nodeAndSubnodes = $backendObject->getList($currentObjectId, array('SUBNODES'));
							if (($objectType != 'cblocks') || ($currentObjectId != $embeddedCblockFolder)) {
								$ni = 0;
								foreach($nodeAndSubnodes as $nodeAndSubnodes_item) {
									// Skip if emebedded contentblock
									if ( ($objectType == 'cblocks') &&
										 ($nodeAndSubnodes_item['PARENT'] == $embeddedCblockFolder) ) {
										continue;
									}
									$ni = $nodeAndSubnodes_item['ID'];
									$nodepermissions[$ni]["USERGROUPID"] = $roleID;
									$nodepermissions[$ni]["OID"] = $nodeAndSubnodes_item['ID'];
									/*
									if ( ($objectType == 'pages') && ($permParam == 0) ) {
										$page = $backendObject->getPage($nodeAndSubnodes_item['ID']);
										$pageInfo = $page->get();
										$contentareas = $templateMgr->getContentareas( $pageInfo['TEMPLATEID'] );
									}
									*/
									if (($objectType != 'cblocks') || ($nodeAndSubnodes_item['ID'] != $embeddedCblockFolder)) {
										$nodepermissions[$ni][$currentPermission] = $permParam;
									}
									if (($objectType == 'pages') || ($objectType == 'mailings')) {
										if ($objectType == 'pages') {
											$object = $backendObject->getPage($nodeAndSubnodes_item['ID']);
										} else if ($objectType == 'mailings') {
											$object = $backendObject->getMailing($nodeAndSubnodes_item['ID']);
										}
										if ($object) {
											$pageCbs = $object->getEmbeddedCblocksOfAllVersions('',true,true);
											foreach($pageCbs as $cb) {
												$embcbperm[$cb['OBJECTID']]['USERGROUPID']	= $roleID;
												$embcbperm[$cb['OBJECTID']]['OID']	= $cb['OBJECTID'];
												$embcbperm[$cb['OBJECTID']][$currentPermission] = $permParam;
											}
										}
									}
									// If RREAD is removed, remove all other permissions too!
									if ( ($currentPermission == 'RREAD') && ($permParam == 0) ) {
										if (($objectType != 'cblocks') || ($nodeAndSubnodes_item['ID'] != $embeddedCblockFolder)) {
											$nodepermissions[$ni]['RWRITE'] = $permParam;
											$nodepermissions[$ni]['RDELETE'] = $permParam;
											if ($objectType != 'usergroups') {
												if ($objectType != 'mailings') {
													$nodepermissions[$ni]['RSUB'] = $permParam;
												}
												if (($objectType == 'pages') || ($objectType == 'cblocks')) {
													$nodepermissions[$ni]['RSTAGE'] = $permParam;
												}
												if (($objectType == 'pages') || ($objectType == 'cblocks') || ($objectType == 'files')) {
													$nodepermissions[$ni]['RMODERATE'] = $permParam;
													$nodepermissions[$ni]['RCOMMENT'] = $permParam;
												}
												if ($objectType == 'mailings') {
													$nodepermissions[$ni]['RSEND'] = $permParam;
												}
											}
										}
									}
									// If any right is added, also add RREAD too!
									if ( ($currentPermission != 'RREAD') && ($permParam == 1) ) {
										if (($objectType != 'cblocks') || ($nodeAndSubnodes_item['ID'] != $embeddedCblockFolder)) {
											$nodepermissions[$ni]['RREAD'] = $permParam;
										}
									}
									// If RDELETE is added, also add RWRITE
									if ( ($currentPermission == 'RDELETE') &&
										 ($permParam == 1) ) {
										 $nodepermissions[$ni]['RWRITE'] = $permParam;
									}
									// For Files, when RWRITE is added, also add RSTAGE permissions
									if ( ($objectType == 'files') && ($currentPermission == 'RWRITE') && ($permParam == 1) ) {
										 $nodepermissions[$ni]['RSTAGE'] = $permParam;
									}
									$ni = $ni + 1;
								}
							}
						}
						$npi = $npi + 1;
					}
				}

				if ($backendObject) {
					$backendObject->permissions->setPermissions($nodepermissions, -1);

					// Always set all permissions for blindfolder
					if ($objectType == 'cblocks') {
						$backendObject->permissions->setByUsergroup($roleID, 'RREAD', $embeddedCblockFolder, 1);
						$backendObject->permissions->setByUsergroup($roleID, 'RWRITE', $embeddedCblockFolder, 1);
						$backendObject->permissions->setByUsergroup($roleID, 'RDELETE', $embeddedCblockFolder, 1);
						$backendObject->permissions->setByUsergroup($roleID, 'RSUB', $embeddedCblockFolder, 1);
						$backendObject->permissions->setByUsergroup($roleID, 'RSTAGE', $embeddedCblockFolder, 1);
						$backendObject->permissions->setByUsergroup($roleID, 'RMODERATE', $embeddedCblockFolder, 1);
						$backendObject->permissions->setByUsergroup($roleID, 'RCOMMENT', $embeddedCblockFolder, 1);
						$backendObject->permissions->setByUsergroup($roleID, 'RSEND', $embeddedCblockFolder, 1);
					}
					if (($objectType == 'pages') || ($objectType == 'mailings')) {
						sCblockMgr()->permissions->setPermissions($embcbperm, -1);
					}
				}

				if (($objectType == 'pages') && ($currentSite)) {
					$koala->queueScript( 'Koala.windows[\''.$winID.'\'].tabs.params = {site:'.$currentSite.'};' );
				}
				$koala->queueScript( 'Koala.windows[\''.$winID.'\'].tabs.select(Koala.windows[\''.$winID.'\'].tabs.selected, Koala.windows[\''.$winID.'\'].tabs.params);' );
				break;

			case 'usergroupsSelectNode':
				$node = $this->params['node'];
				$wid = $this->params['wid'];

				$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");
				$anonGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONGROUPID");

				// Check rights
				$rwrite = sUsergroups()->usergroupPermissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RDELETE" );
				if ($rwrite && ($node != $rootGroupId) && ($node != $anonGroupId)) {
					$koala->callJSFunction( 'Koala.yg_enable', 'tree_btn_delete', 'btn-'.$wid, 'tree_btn' );
				} else {
					$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_delete', 'btn-'.$wid, 'tree_btn' );
				}
				break;

	}

?>