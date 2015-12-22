<?php

	\framework\import('org.phpmailer.phpmailer');

	$mailingMgr = new MailingMgr();
	$templateMgr = new Templates();

	$jsQueue = new JSQueue(NULL);

	switch ($action) {

		case 'duplicateMailing':
			$mailingId = (int)$this->params['mailingId'];
			$parentwindow = $this->params['wid'];

			$sourceMailing = $mailingMgr->getMailing($mailingId);
			$oldMailingInfo = $sourceMailing->get();
			$oldMailingId = $oldMailingInfo['ID'];

			$newMailingId = $mailingMgr->add($mailingMgr->tree->getRoot());

			$newMailing = $mailingMgr->getMailing($newMailingId);
			$newMailing->copyFrom($sourceMailing);

			$jsQueue->add ($newMailingId, HISTORYTYPE_MAILING, 'MAILING_ADD', sGuiUS(), NULL);

			// Inherit permissions of the parent of the newly created copy
			$allPerms = $sourceMailing->permissions->getPermissions();
			//$newMailing->permissions->clear();
			$newMailing->permissions->setPermissions($allPerms);
			break;

		case 'saveMailingInfo':
			$defaulttemplate = (int)$this->params['mailing_defaulttemplate'];
			$templateroot = (int)$this->params['mailings_templateroot'];

			if ($defaulttemplate == -1) {
				$mailingMgr->setDefaultTemplate(0);
			} elseif ($defaulttemplate > 0) {
				$mailingMgr->setDefaultTemplate($defaulttemplate);
			}
			if ($templateroot == -1) {
				$mailingMgr->setTemplateRoot(0);
			} elseif ($templateroot > 0) {
				$mailingMgr->setTemplateRoot($templateroot);
			}
			break;

		case 'setMailingConfigTemplateRoot':
			$template = $this->params['templateId'];
			$parentwindow = $this->params['wid'];

			// Check if we really got sane values
			if ($template!='template') {
				$templatePreview = '';
				$templateInfo = $templateMgr->getTemplate($template);
				$koala->callJSFunction( 'Koala.windows[\'wid_'.$parentwindow.'\'].refreshMailingTemplateRoot', $template, $templateInfo['NAME'] );
			}
			break;

		case 'setMailingConfigTemplate':
			$template = $this->params['templateId'];
			$parentwindow = $this->params['wid'];

			// Check if we really got sane values
			if ($template!='template') {

				$templatePreview = '';
				$templateInfo = $templateMgr->getTemplate($template);
				$templateInfo['PREVIEWPATH'] = $templateMgr->getPreviewPath( $template );
				if ($templateInfo['FILE'] > 0) {
					$templatePreview = $templateInfo['PREVIEWPATH'];
				}
				$koala->callJSFunction( 'Koala.windows[\'wid_'.$parentwindow.'\'].refreshMailingTemplate', $templateInfo['NAME'], $templateInfo['FILENAME'], $template, $templatePreview );
			}
			break;

		case 'saveMailingVersion':
			// Cannot use new style of parameters here (function is called by a custom-attribute event - and therefore does not know about named parameters)

			$data = explode('-', $this->reponsedata['null']->yg_id);
			$wid = $data[2];
			$mailingId = $data[0];
			$mailing = $mailingMgr->getMailing($mailingId);

			$new_version = $mailing->newVersion();

			$koala->queueScript('Koala.windows[\'wid_'.$wid.'\'].tabs.select(Koala.windows[\'wid_'.$wid.'\'].tabs.selected, Koala.windows[\'wid_'.$wid.'\'].tabs.params);');
			break;

		case 'restoreMailingVersion':
			$mailingId = $this->params['mailingId'];
			$version = $this->params['version'];
			$wid = $this->params['wid'];

			// Get mailing
			$mailing = $mailingMgr->getMailing($mailingId, $version);

			$oldinfo = $mailing->get();
			$new_version = $mailing->newVersion();

			$jsQueue->add ($mailingId, HISTORYTYPE_MAILING, 'OBJECT_CHANGE', sGuiUS(), 'mailing', NULL, NULL, $mailingId.'-mailing', 'name', $oldinfo['NAME']);
			$jsQueue->add ($mailingId, HISTORYTYPE_MAILING, 'HIGHLIGHT_MAILING', sGuiUS(), 'name');

			$koala->queueScript('Koala.windows[\'wid_'.$wid.'\'].tabs.select(Koala.windows[\'wid_'.$wid.'\'].tabs.selected, Koala.windows[\'wid_'.$wid.'\'].tabs.params);');
			break;

		case 'mailingSelectNode':
			$node = $this->params['node'];
			$wid = $this->params['wid'];

			$root_node = $mailingMgr->tree->getRoot();
			$mailing = $mailingMgr->getMailing($node);

			if ($node == 'root') {
				$node = $root_node;
			}

			// 0 = rsub		-> add
			// 1 = rread	-> copy
			// 2 = rsend	-> send
			// 3 = rdelete	-> delete
			$buttons = array();

			// Check rights
			$rread = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $node, "RREAD");
			$rsend = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $node, "RSEND");
			$rsub = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $root_node, "RWRITE");
			$rdelete = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $node, "RDELETE");
			$rstage = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $node, "RSTAGE");

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

			// Check permissions for button "send"
			if ($rsend) {
				$buttons[2] = true;
			} else {
				$buttons[2] = false;
			}

			// Check permissions for button "delete"
			if ($rdelete) {
				$buttons[3] = true;
			} else {
				$buttons[3] = false;
			}

			// Finally enable/Disable them
			if ($buttons[0]===true) {
				$koala->callJSFunction('Koala.yg_enable', 'tree_btn_add', 'btn-'.$wid, 'tree_btn');
			} else {
				$koala->callJSFunction('Koala.yg_disable', 'tree_btn_add', 'btn-'.$wid, 'tree_btn');
			}

			if ($buttons[1]===true) {
				$koala->callJSFunction('Koala.yg_enable', 'tree_btn_copy', 'btn-'.$wid, 'tree_btn');
			} else {
				$koala->callJSFunction('Koala.yg_disable', 'tree_btn_copy', 'btn-'.$wid, 'tree_btn');
			}

			/* FOR SEND (unused)
			if ($buttons[2]===true) {
				$koala->callJSFunction('Koala.yg_enable', 'tree_btn_send', 'btn-'.$wid, 'tree_btn');
			} else {
				$koala->callJSFunction('Koala.yg_disable', 'tree_btn_send', 'btn-'.$wid, 'tree_btn');
			}
			*/

			if ($buttons[3]===true) {
				$koala->callJSFunction('Koala.yg_enable', 'tree_btn_delete', 'btn-'.$wid, 'tree_btn');
			} else {
				$koala->callJSFunction('Koala.yg_disable', 'tree_btn_delete', 'btn-'.$wid, 'tree_btn');
			}

			// Always enable "Preview" and "Edit"
			$koala->callJSFunction('Koala.yg_enable', 'tree_btn_preview', 'btn-'.$wid, 'tree_btn');
			$koala->callJSFunction('Koala.yg_enable', 'tree_btn_edit', 'btn-'.$wid, 'tree_btn');
			break;

		case 'setMailingPName':

			$mailingID = $this->params['mailing'];
			$mailing = sMailingMgr()->getMailing($mailingID);

			$value = $mailing->filterPName($this->params['value']);

			if (sMailingMgr()->getMailingIdByPName($value)) {
				$koala->callJSFunction('Koala.yg_promptbox', $itext['TXT_ERROR'], $itext['TXT_PNAME_ALREADY_USED_CHOOSE_ANOTHER'], 'alert');
				$mailingInfo = $mailing->get();
				$jsQueue->add ($mailingID, HISTORYTYPE_MAILING, 'OBJECT_CHANGE', sGuiUS(), 'mailing', NULL, NULL, $mailingID.'-mailing', 'pname', $mailingInfo['PNAME']);
				break;
			}

			$mailing->setPName($value);
			$newMailingInfo = $mailing->get();

			$jsQueue->add ($mailingID, HISTORYTYPE_MAILING, 'OBJECT_CHANGE', sGuiUS(), 'mailing', NULL, NULL, $mailingID.'-mailing', 'pname', $newMailingInfo['PNAME']);
			$jsQueue->add ($mailingID, HISTORYTYPE_MAILING, 'OBJECT_CHANGEPNAME', sGuiUS(), 'mailing', NULL, NULL, $mailingID.'-mailing', 'name', $newMailingInfo['PNAME']);
			$jsQueue->add ($mailingID, HISTORYTYPE_MAILING, 'REFRESH_WINDOW', sGuiUS(), 'pname');

			// Add to history
			$mailing->history->add (HISTORYTYPE_MAILING, NULL, $value, 'TXT_MAILING_H_PNAME');
			break;

		case 'addMailing':
			$winId = $this->params['winId'];

			// Add new child node
			$new_id = $mailingMgr->add($mailingMgr->tree->getRoot());
			$mailing = $mailingMgr->getMailing($new_id);
			$mailing->properties->setValue('NAME',  $itext['TXT_NEW_OBJECT']);

			// Get default template
			$defaultTemplate = $mailingMgr->getDefaultTemplate();
			if ($defaultTemplate > 0) {
				$mailing->setTemplate($defaultTemplate);
			}

			if ($new_id != false) {
				$jsQueue->add($new_id, HISTORYTYPE_MAILING, 'MAILING_ADD', sGuiUS(), NULL);
			} else {
				$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
			}
			break;

		case 'deleteMailing':
			$mailingIds = $this->params['mailingIds'];

			foreach($mailingIds as $mailingId) {
				$successfullyDeleted = $mailingMgr->remove($mailingId);
				if (in_array($mailingId, $successfullyDeleted)) {
					$jsQueue->add ($mailingId, HISTORYTYPE_MAILING, 'OBJECT_DELETE', sGuiUS(), 'mailing', NULL, NULL, $mailingId.'-mailing', 'name');
					$jsQueue->add ($mailingId, HISTORYTYPE_MAILING, 'MAILING_DELETE', sGuiUS(), NULL);
				} else {
					$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
				}
			}
			break;

		case 'updateMailingStatus':
			$mailingIds = $this->params['mailingIds'];

			$mailingsStatus = array();
			foreach($mailingIds as $mailingId) {
				// Get # of receipients for this mailing
				$mailing = $mailingMgr->getMailing($mailingId);
				if ($mailing) {
					$mailingInfo = $mailing->get();

					// Check if mailing really exists
					if ($mailingInfo) {
						//$latestFinalVersion = $mailing->getLatestApprovedVersion();
						//$mailing = $mailingMgr->getMailing($mailingId, $latestFinalVersion);
						$mailingGroups = $mailing->getUsergroups();
						$mailingStatus = $mailing->getStatus();

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

						$queuedJobs = $mailingMgr->scheduler->getQueuedJobsForObject($mailingId, true, true, 'SCH_EMAILSEND');

						if (count($queuedJobs) > 0) {
							// There are scheduled or running jobs for this mailing
							$jobCount = count($queuedJobs);
						} else {
							// No jobs scheduled
							if ($mailingStatus['STATUS'] == 'INPROGRESS') {
								$jobCount = $receipients;
							} else {
								$jobCount = 0;
							}
						}
						array_push($mailingsStatus,
						array(
						'MAILING_ID'	=> $mailingId,
						'JOBCOUNT'		=> $jobCount,
						'RECEIPIENTS'	=> $receipients,
						'STATUS'		=> $mailingStatus['STATUS']
						)
						);
					}
				}
			}
			$koala->queueScript("Koala.yg_updateMailingStatus('".json_encode($mailingsStatus)."');");
			break;

		case 'pauseMailing':
			$mailingId = $this->params['mailingId'];

			// Get mailing
			$mailing = $mailingMgr->getMailing($mailingId);

			// Get # of pending jobs
			$queuedJobs = $mailingMgr->scheduler->getQueuedJobsForObject($mailingId, true, true, 'SCH_EMAILSEND');

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

			// Pause all pending jobs
			$mailingMgr->scheduler->pauseAllQueuedJobsForObject($mailingId);

			// Set status of mailing
			$mailing->setStatus('PAUSED');

			// Log history
			$mailing->history->add(HISTORYTYPE_MAILING, $latestFinalVersion, ((int)$receipients - (int)$queuedJobs).' / '.$receipients, 'TXT_MAILING_H_PAUSED');
			break;

		case 'cancelMailing':
			$mailingId = $this->params['mailingId'];

			// Get mailing
			$mailing = $mailingMgr->getMailing($mailingId);

			// Get # of pending jobs
			$queuedJobs = $mailingMgr->scheduler->getQueuedJobsForObject($mailingId, true, true, 'SCH_EMAILSEND');

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
			$mailing->setStatus('CANCELLED');

			// Log history
			$mailing->history->add(HISTORYTYPE_MAILING, $latestFinalVersion, ((int)$receipients - (int)$queuedJobs).' / '.$receipients, 'TXT_MAILING_H_CANCELLED');
			break;

		case 'resumeMailing':
			$mailingId = $this->params['mailingId'];

			// Get mailing
			$mailing = $mailingMgr->getMailing($mailingId);

			// Get # of pending jobs
			$queuedJobs = $mailingMgr->scheduler->getQueuedJobsForObject($mailingId, true, false, 'SCH_EMAILSEND');

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
			$mailingMgr->scheduler->resumeAllQueuedJobsForObject($mailingId);

			// Set status of mailing
			$mailing->setStatus('INPROGRESS');

			// Log history
			$mailing->history->add(HISTORYTYPE_MAILING, $latestFinalVersion, ((int)$receipients - (int)$queuedJobs).' / '.$receipients, 'TXT_MAILING_H_RESUMED');
			break;

		case 'sendMailing':
			$testRecipient = $this->params['testRecipient'];
			$testOnly = $this->params['testOnly'];
			$mailingId = $this->params['mailingId'];

			// Check permissions
			if ($mailingMgr->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $mailingId, 'RSEND')) {
				// Get mailing
				$mailing = $mailingMgr->getMailing($mailingId);
				$mailingInfo = $mailing->get();

				if ($mailingInfo['TEMPLATEID'] == 0) {
					// No template assigned, show error message
					$koala->alert($itext['TXT_MAILING_ERROR_NO_TEMPLATE_SET']);
					break;
				}

				// Check if test
				if ($testOnly) {
					$user = new User(sUserMgr()->getCurrentUserID());
					$userInfo = $user->get();
					$userInfo['PROPERTIES'] = $user->properties->getValues($currUserId);

					$emailData = array(
						'MAILING_ID'	=> $mailingId,
						'USER_ID'		=> $userInfo['ID'],
						'IS_TEST'		=> true,
						'TO' => array(),
						'CC' => array(),
						'BCC' => array(),
						'ATTACHMENTS'	=> array(),
						'ENCODING'		=> 'base64',
						'CHARSET'		=> 'utf-8'
					);

					if (strpos($testRecipient, ',') !== false) {
						$toArray = explode(',', $testRecipient);
						foreach ($toArray as $toArrayItem) {
							$emailData['TO'][] = array(
								'EMAIL'		 => trim($toArrayItem),
								'EMAIL_NAME' => trim($toArrayItem)
							);
						}
					} else {
						$emailData['TO'][] = array(
							'EMAIL'		 => $testRecipient,
							'EMAIL_NAME' => $testRecipient
						);
					}

					$mailingData = array('USERINFO' => $userInfo, 'DATA' => $emailData);
					$mailingData = sMailingMgr()->callExtensionHook('beforeSend', $mailingId, $mailing->getVersion(), $mailingData);

					$scheduleId = $mailingMgr->scheduler->schedule($mailingId, 'SCH_EMAILSEND', time(), $mailingData['DATA']);
					$scheduleId = $mailingMgr->scheduler->schedule($mailingId, 'SCH_EMAILCHECKFINISH', time(), array('MAILING_ID' => $mailingId, 'IS_TEST' => true));

					// No logging here
				} else {
					// Get latest final version of mailing
					$emailData['FROM'] = $mailingInfo['FROM_EMAIL'];
					$emailData['FROM_NAME'] = $mailingInfo['FROM_NAME'];
					$emailData['FROM_REPLYTO'] = $mailingInfo['FROM_REPLYTO'];
					$emailData['FROM_SENDER'] = $mailingInfo['FROM_SENDER'];
					$emailData['ENCODING'] = $mailingInfo['ENCODING'];
					$emailData['ATTACHMENTS'] = array();
					if ($emailData['ENCODING'] == '') $emailData['ENCODING'] = 'base64';
					$emailData['SUBJECT'] = $mailingInfo['SUBJECT'];
					$emailStatus = $mailing->getStatus();

					// Check if version has changed
					if ($emailStatus['STATUS'] != 'UNSENT') {
						$koala->alert($itext['TXT_MAILING_ERROR_STATUS_NOT_OK']);
					} elseif($mailingInfo['HASCHANGED']) {
						$koala->alert($itext['TXT_MAILING_ERROR_NOT_APPROVED']);
					} elseif(!$mailingInfo['FROM_EMAIL']) {
						$koala->alert($itext['TXT_MAILING_ERROR_NO_SENDER_SET']);
					} elseif(!$mailingInfo['SUBJECT']) {
						$koala->alert($itext['TXT_MAILING_ERROR_NO_SUBJECT_SET']);
					} else {
						$latestFinalVersion = $mailing->getLatestApprovedVersion();
						$mailing = $mailingMgr->getMailing($mailingId, $latestFinalVersion);
						$mailingInfo = $mailing->get();

						// Get assigned groups
						$userGroups = $mailing->getUsergroups();

						// Get all receipients
						$userIds = array();
						foreach($userGroups as $currUserGroups) {
							// Get # of users in this group
							$usersInRole = sUserMgr()->getByUsergroup($currUserGroups['ID']);
							foreach($usersInRole as $usersInRoleItem) {
								array_push($userIds, $usersInRoleItem['UID']);
							}
						}
						$userIds = array_unique($userIds);

						// Check if # of recipients > 0
						if (count($userIds) > 0) {
							// Queue emails
							foreach($userIds as $currUserId) {
								$user = new User($currUserId);
								$userInfo = $user->get();
								$userInfo['PROPERTIES'] = $user->properties->getValues($currUserId);

								$userId = $userInfo['ID'];
								$userEmail = $userInfo['PROPERTIES']['EMAIL'];
								$userFirstName = $userInfo['PROPERTIES']['FIRSTNAME'];
								$userLastName = $userInfo['PROPERTIES']['LASTNAME'];
								$userName = trim($userFirstName.' '.$userLastName);
								$userCompany = $userInfo['PROPERTIES']['COMPANY'];
								$userDepartment = $userInfo['PROPERTIES']['COMPANY'];

								$emailData = array(
									'MAILING_ID'	=> $mailingId,
									'USER_ID'			=> $userInfo['ID'],
									'IS_TEST'			=> false,
									'TO' => array(
										array(
											'EMAIL'			=> $userEmail,
											'EMAIL_NAME'	=> $userName
										)
									),
									'CC' => array(),
									'BCC' => array(),
									'ATTACHMENTS'	=> array(),
									'ENCODING'		=> $emailData['ENCODING'],
									'CHARSET'		=> 'utf-8'
								);
								$mailingData = array('USERINFO' => $userInfo, 'DATA' => $emailData);
								$mailingData = sMailingMgr()->callExtensionHook('beforeSend', $mailingId, $latestFinalVersion, $mailingData);
								$scheduleId = $mailingMgr->scheduler->schedule($mailingId, 'SCH_EMAILSEND', time(), $mailingData['DATA']);
							}
							$scheduleId = $mailingMgr->scheduler->schedule($mailingId, 'SCH_EMAILCHECKFINISH', time(), array('MAILING_ID' => $mailingId));

							// Set status of mailing
							$mailing->setStatus('INPROGRESS');

							// Set status in gui
							$jsQueue->add($mailingId, HISTORYTYPE_MAILING, 'OBJECT_CHANGECLASS', sGuiUS(), 'mailing', NULL, NULL, $mailingId.'-mailing', 'statusinfo', 'status_info inprogress');

							// Trigger locking of the current mailing
							$jsQueue->add($mailingId, HISTORYTYPE_MAILING, 'OBJECT_CHANGE_LOCK_STATE', sGuiUS(), 'mailing', NULL, NULL, $mailingId.'-mailing', 'true');

							// Log history
							$mailing->history->add(HISTORYTYPE_MAILING, $latestFinalVersion, $latestFinalVersion, 'TXT_MAILING_H_SENDING');
						} else {
							$koala->alert($itext['TXT_MAILING_ERROR_NO_RECIPIENTS']);
						}

					}
				}

			} else {
				// No permissions, show error message
				$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
			}
			break;

		case 'setMailingTemplate':
			$wid = $this->params['wid'];
			$templateId = $this->params['templateId'];
			$mailingId = $this->params['mailingId'];

			$templateInfo =  $templateMgr->getTemplate($templateId);

			// Check if we really got sane values
			if ($templateId != 'template') {

				$mailing = $mailingMgr->getMailing($mailingId);
				$mailingInfo = $mailing->get();

				$mailing->setTemplate($templateId);

				// Add to history
				$mailing->history->add(HISTORYTYPE_MAILING, NULL, $templateInfo['NAME'], "TXT_MAILING_H_TEMPLATE");

				//$mailing->setStatus('UNSENT');
				$jsQueue->add ($mailingId, HISTORYTYPE_MAILING, 'HIGHLIGHT_MAILING', sGuiUS(), 'name');

				$koala->queueScript("Koala.windows['wid_".$wid."'].refreshTemplate('".$templateId."');");
			}
			break;

		case 'approveMailing':

			$wid = $this->params['winID'];

			$mailingId = $this->params['mailing'];
			$mailing = $mailingMgr->getMailing($mailingId);
			$mailingVersion = $mailing->getLatestVersion();
			$mailingInfo = $mailing->get();

			$mailing->approve();

			$koala->queueScript('if (Koala.windows[\'wid_'.$wid.'\'].tab==\'CONTENT\') Koala.windows[\'wid_'.$wid.'\'].tabs.select(1, Koala.windows[\'wid_'.$wid.'\'].tabs.params);');

			// Do not reset to UNSENT when mailing is currently paused
			$mailingStatus = $mailing->getStatus();
			if ($mailingStatus['STATUS'] != 'PAUSED') {
				$mailing->setStatus('UNSENT');
			}

			$jsQueue->add($mailingId, HISTORYTYPE_MAILING, 'CLEAR_REFRESH', sGuiUS(), 'mailing');
			$jsQueue->add($mailingId, HISTORYTYPE_MAILING, 'UNHIGHLIGHT_MAILING', sGuiUS(), 'name');
			break;

	}

?>