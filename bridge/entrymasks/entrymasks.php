<?php

	$jsQueue = new JSQueue(NULL);
	$entrymaskMgr = new Entrymasks();
	$reftracker = new Reftracker();

	switch ($action) {

		case 'entrymaskSaveConfig':
			$objectID = $this->params['objectID'];
			$wid = $this->params['wid'];
			$formfields = $this->params['formfields[]'];
			$formfield_types = $this->params['formfield_types[]'];
			$formfield_indexes = $this->params['formfield_indexes[]'];
			$haderror = false;

			// Ensure that $formfields is an array
			if (!is_array($formfields) && isset($formfields)) {
				$formfields = array($formfields);
			}

			// Ensure that $formfield_types is an array
			if (!is_array($formfield_types) && isset($formfield_types)) {
				$formfield_types = array($formfield_types);
			}

			// Ensure that $formfield_indexes is an array
			if (!is_array($formfield_indexes) && isset($formfield_indexes)) {
				$formfield_indexes = array($formfield_indexes);
			}

			$name = $this->params['name'];
			$code = $this->params['code'];

			// Set the new name
			if ( $entrymaskMgr->setInfo($objectID, $name, $code) === false ) {
				$koala->callJSFunction( 'Koala.yg_error', 'entrymask', $objectID.'-entrymask', 'code' );
				$haderror = true;
			} else {
				$jsQueue->add ($objectID, HISTORYTYPE_ENTRYMASK, 'UNHIGHLIGHT_ENTRYMASK', sGuiUS(), 'code');
				$jsQueue->add ($objectID, HISTORYTYPE_ENTRYMASK, 'OBJECT_CHANGE', sGuiUS(), 'entrymask', NULL, NULL, $objectID.'-entrymask', 'name', $name);
			}

			if (!$haderror) {
				// Check which formfields to delete
				$old_formfields = $entrymaskMgr->getEntrymaskFormfields($objectID);
				$to_del = array();
				foreach($old_formfields as $old_formfield) {
					$del = true;
					foreach($formfields as $formfield) {
						if ($formfield == $old_formfield['ID']) {
							$del = false;
						}
					}
					if ($del) {
						array_push( $to_del, $old_formfield['ID'] );
					}
				}
				foreach($to_del as $to_del_item) {
					$entrymaskMgr->removeFormfield($to_del_item);
				}

				// Check which formfields to add
				$new_formfields = array();
				$old_formfields = $entrymaskMgr->getEntrymaskFormfields($objectID);
				foreach($formfields as $formfield_idx => $formfield) {
					$add = true;
					foreach($old_formfields as $old_formfield) {
						if ($formfield == $old_formfield['ID']) {
							$add = false;
						}
					}
					if ($add) {
						$newFormfieldId = $entrymaskMgr->addFormfield($objectID, $formfield_types[$formfield_idx], $itext['TXT_NEW_OBJECT']);
						$formfields[$formfield_idx] = (int)$newFormfieldId;

						if ($formfield_types[$formfield_idx]==9) {
							if ($this->params[$wid.'_fld_'.$formfield.'-ENTRIES[]']) {
								$this->params[$wid.'_fld_'.$newFormfieldId.'-ENTRIES[]'] = $this->params[$wid.'_fld_'.$formfield.'-ENTRIES[]'];
							}
							if ($this->params[$wid.'_fld_'.$formfield.'-ENTRY_IDS[]']) {
								$this->params[$wid.'_fld_'.$newFormfieldId.'-ENTRY_IDS[]'] = $this->params[$wid.'_fld_'.$formfield.'-ENTRY_IDS[]'];
							}
						}
						array_push( $new_formfields, array('IDX' => $formfield_idx, 'ID' => $newFormfieldId) );
					}
				}

				for ($i = 0; $i < count($formfields); $i++) {
					$linkid = $formfields[$i];

					$formfieldinfo = $entrymaskMgr->getFormfield( $linkid );

					// Check if entry is newly added
					$newly_added = false;
					$newly_added_idx = -1;

					foreach($new_formfields as $new_formfield) {
						if ($new_formfield['ID'] == $linkid) {
							$newly_added = true;
							$newly_added_idx = $formfield_indexes[$new_formfield['IDX']];
						}
					}

					// For Checkbox Formfields
					if ($formfieldinfo['FORMFIELD']==4) {
						if ($newly_added) {
							$this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-PRESET'] = (int)$this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-PRESET'];
						} else {
							$this->params[$wid.'_fld_'.$linkid.'-PRESET'] = (int)$this->params[$wid.'_fld_'.$linkid.'-PRESET'];
						}
					}

					// For Date & Datetime Formfields
					if (($formfieldinfo['FORMFIELD']==11) || ($formfieldinfo['FORMFIELD']==12)) {
						if ($newly_added) {
							$preset_value_1 = $this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-PRESET1'];
							$preset_value_2 = $this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-PRESET2'];
						} else {
							$preset_value_1 = $this->params[$wid.'_fld_'.$linkid.'-PRESET1'];
							$preset_value_2 = $this->params[$wid.'_fld_'.$linkid.'-PRESET2'];
						}
						if (strlen(trim($preset_value_1)) > 0) {
							$date   = explode('.', $preset_value_1);
							$day	= (int)$date[0];
							$month  = (int)$date[1];
							$year   = (int)$date[2];
						}

						if (strlen(trim($preset_value_2)) > 0) {
							$time   = explode(':', $preset_value_2);
							$hour   = (int)$time[0];
							$minute = (int)$time[1];
							$ampm = explode(' ', $time[1]);
							if ($ampm[1]) {
								$ampm = $ampm[1];
							}
							if ( (strtoupper($ampm) == 'PM') && ($hour != 12) ) {
								$hour += 12;
							}
							if ( (strtoupper($ampm) == 'AM') && ($hour == 12) ) {
								$hour -= 12;
							}
						}

						if ($hour || $minute || $month || $day || $year) {
							$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
							$timestamp = TSfromLocalTS($timestamp);
						} else {
							$timestamp = 0;
						}

						if ($newly_added) {
							$this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-PRESET'] = $timestamp;
						} else {
							$this->params[$wid.'_fld_'.$linkid.'-PRESET'] = $timestamp;
						}
					}

					// For List Formfields
					if ($formfieldinfo['FORMFIELD']==9) {
						$old_entries = $entrymaskMgr->getListValuesByLinkID( $linkid );
						$entries = $this->params[$wid.'_fld_'.$linkid.'-ENTRIES[]'];
						$entry_ids = $this->params[$wid.'_fld_'.$linkid.'-ENTRY_IDS[]'];
						array_pop($entries);
						array_pop($entry_ids);

						// Check which entries to add
						$to_add = array();
						foreach($entry_ids as $entry_idx => $entry_id) {
							$add = true;
							foreach($old_entries as $old_entry) {
								if ($entry_id == $old_entry['ID']) {
									$add = false;
								}
							}
							if ($add) {
								array_push( $to_add, $entries[$entry_idx] );
							}
						}

						// Check which entries to delete
						$to_del = array();
						foreach($old_entries as $old_entry) {
							$del = true;
							foreach($entry_ids as $entry_id) {
								if ($entry_id == $old_entry['ID']) {
									$del = false;
								}
							}
							if ($del) {
								array_push( $to_del, $old_entry['ID'] );
							}
						}

						// Delete all removed listentries
						foreach($to_del as $to_del_item) {
							$entrymaskMgr->removeListValue( $to_del_item );
						}

						// Add all added listentries
						foreach($to_add as $to_add_item) {
							$new_id = $entrymaskMgr->addListValue($linkid, $to_add_item);
							foreach($entries as $entry_idx => $entry) {
								if ($entry == $to_add_item) {
									$entry_ids[$entry_idx] = $new_id;
								}
							}
						}

						// Set new order of listentries
						$entrymaskMgr->setListOrder( $entry_ids );
					}

					if ($newly_added) {
						$parray = array(
							'IDENTIFIER' => $this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-IDENTIFIER'],
							'NAME' => $this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-NAME'],
							'PRESET' => $this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-PRESET'],
							'WIDTH' => $this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-WIDTH'],
							'MAXLENGTH' => $this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-MAXLENGTH'],
							'CONFIG' => $this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-CONFIG'],
							'CUSTOM' => $this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-CUSTOM']
						);
					} else {
						$parray = array(
							'IDENTIFIER' => $this->params[$wid.'_fld_'.$linkid.'-IDENTIFIER'],
							'NAME' => $this->params[$wid.'_fld_'.$linkid.'-NAME'],
							'PRESET' => $this->params[$wid.'_fld_'.$linkid.'-PRESET'],
							'WIDTH' => $this->params[$wid.'_fld_'.$linkid.'-WIDTH'],
							'MAXLENGTH' => $this->params[$wid.'_fld_'.$linkid.'-MAXLENGTH'],
							'CONFIG' => $this->params[$wid.'_fld_'.$linkid.'-CONFIG'],
							'CUSTOM' => $this->params[$wid.'_fld_'.$linkid.'-CUSTOM']
						);
					}
					$entrymaskMgr->setFormfieldOrder($linkid, $i);
					$entrymaskMgr->setFormfieldParameters($linkid, $parray);

					if ($newly_added) {
						$entrymaskMgr->setFormfieldName($linkid, $this->params[$wid.'_fld___NEW_ID_'.$newly_added_idx.'__-NAME']);
					} else {
						$entrymaskMgr->setFormfieldName($linkid, $this->params[$wid.'_fld_'.$linkid.'-NAME']);
					}
				}

				$koala->queueScript( "Koala.yg_fadeFields(\$('".$wid."'), 'input.changed', 'textarea.changed');" );
				$jsQueue->add ($objectID, HISTORYTYPE_ENTRYMASK, 'UNHIGHLIGHT_ENTRYMASK', sGuiUS(), 'name');
			}
			break;

		case 'entrymaskSelectNode':
			$node = $this->params['node'];
			$wid = $this->params['wid'];

			$root_node = $entrymaskMgr->getTree(NULL, 0);

			// Entrymasks

			// 1 = rsub
			// 2 = rread
			// 3 = rdelete
			// 4 = parent -> rsub & rwrite
			// 5 = parent -> rsub & rwrite
			// 6 = rdelete
			$buttons = array();

			$entrymaskInfo = $entrymaskMgr->get( $node );

			$koala->callJSFunction( 'Koala.yg_selectEntrymask', $node, $wid );

			$rread = $rwrite = $rdelete = $prsub = $prwrite = true;
			$rsub = ($entrymaskInfo['FOLDER'] == 1);

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
				$koala->callJSFunction( 'Koala.yg_enable', 'tree_btn_add', 'btn-'.$wid, 'tree_btn' );
				$koala->callJSFunction( 'Koala.yg_enable', 'tree_btn_addfolder', 'btn-'.$wid, 'tree_btn' );
			} else {
				$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_add', 'btn-'.$wid, 'tree_btn' );
				$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_addfolder', 'btn-'.$wid, 'tree_btn' );
			}

			if ($buttons[1]===true) {
				$koala->callJSFunction( 'Koala.yg_enable', 'tree_btn_copy', 'btn-'.$wid, 'tree_btn' );
			} else {
				$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_copy', 'btn-'.$wid, 'tree_btn' );
			}

			if ($buttons[2]===true) {
				$koala->callJSFunction( 'Koala.yg_enable', 'tree_btn_move', 'btn-'.$wid, 'tree_btn' );
			} else {
				$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_move', 'btn-'.$wid, 'tree_btn' );
			}

			if ( ($buttons[3]===true) && ($buttons[4]===true) ) {
				$koala->callJSFunction( 'Koala.yg_enable', 'tree_btn_up', 'btn-'.$wid, 'tree_btn' );
				$koala->callJSFunction( 'Koala.yg_enable', 'tree_btn_down', 'btn-'.$wid, 'tree_btn' );
			} else {
				$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_up', 'btn-'.$wid, 'tree_btn' );
				$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_down', 'btn-'.$wid, 'tree_btn' );
			}

			if ($buttons[5]===true) {
				$koala->callJSFunction( 'Koala.yg_enable', 'tree_btn_delete', 'btn-'.$wid, 'tree_btn' );
			} else {
				$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_delete', 'btn-'.$wid, 'tree_btn' );
			}
			break;

		case 'addEntrymaskChildFolder':
			$entrymask = $this->params['entrymask'];

			// Check if root node is selected
			if ($entrymask==='root') {
				// Get real Page-ID of Root-Node
				$entrymasks =  $entrymaskMgr->getList();
				$entrymask = $entrymasks[0]['ID'];
			}

			// Add new child node
			$new_id = $entrymaskMgr->add( $entrymask, 1, ($itext['TXT_NEW_OBJECT']!='')?($itext['TXT_NEW_OBJECT']):('$TXT_NEW_OBJECT') );

			if ( $new_id != false ) {
				$icons = new Icons();
				$koala->callJSFunction( 'Koala.yg_addChild', 'entrymask', $entrymask.'-entrymask', 'name', ($itext['TXT_NEW_OBJECT']!='')?($itext['TXT_NEW_OBJECT']):('$TXT_NEW_OBJECT'), 'entrymask', $new_id.'-entrymask', 'name', $icons->icon['folder'], '', true );
			} else {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			}
			break;

		case 'deleteEntrymask':
			$entrymask = $this->params['objectID'];
			$confirmed = $this->params['confirmed'];
			$positive = $this->params['positive'];
			$winID = $this->params['wid'];

			// Check if entrymask is still used
			$contentblockIds = sCblockMgr()->getCblockLinkByEntrymaskId( $entrymask );

			if (count($contentblockIds) > 0) {
				// Still used!
				if ($confirmed != 'true') {
					$parameters = array(
						'objectID'	=> $entrymask,
						'wid'	=> $winID
					);
					$koala->callJSFunction( 'Koala.yg_confirm',
						($itext['TXT_DELETE_USED_ENTRYMASK_TITLE']!='')?($itext['TXT_DELETE_USED_ENTRYMASK_TITLE']):('$TXT_DELETE_USED_ENTRYMASK_TITLE'),
						($itext['TXT_DELETE_USED_ENTRYMASK']!='')?($itext['TXT_DELETE_USED_ENTRYMASK']):('$TXT_DELETE_USED_ENTRYMASK'),
						$action, json_encode($parameters)
					);
				} else if (($confirmed == 'true') && ($positive == 'true')) {
					$successfullyDeleted = $entrymaskMgr->remove($entrymask);
					if (in_array($entrymask, $successfullyDeleted)) {
						foreach($successfullyDeleted as $successfullyDeletedItem) {
							$jsQueue->add ($successfullyDeletedItem, HISTORYTYPE_ENTRYMASK, 'OBJECT_DELETE', sGuiUS(), 'entrymask', NULL, NULL, $successfullyDeletedItem.'-entrymask', 'name');
							$koala->queueScript( '$(Koala.windows[\'wid_'.$winID.'\'].boundWindow).addClassName(\'boxghost\');' );
							$koala->queueScript( 'Koala.windows[Koala.windows[\'wid_'.$winID.'\'].boundWindow].init();' );
						}
					} else {
						$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
					}
				}
			} else {
				// Not used, delete entrymask
				$successfullyDeleted = $entrymaskMgr->remove($entrymask);
				if (in_array($entrymask, $successfullyDeleted)) {
					foreach($successfullyDeleted as $successfullyDeletedItem) {
						$jsQueue->add ($successfullyDeletedItem, HISTORYTYPE_ENTRYMASK, 'OBJECT_DELETE', sGuiUS(), 'entrymask', NULL, NULL, $successfullyDeletedItem.'-entrymask', 'name');
						$koala->queueScript( '$(Koala.windows[\'wid_'.$winID.'\'].boundWindow).addClassName(\'boxghost\');' );
						$koala->queueScript( 'Koala.windows[Koala.windows[\'wid_'.$winID.'\'].boundWindow].init();' );
					}
				} else {
					$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
				}
			}
			break;

		case 'moveEntrymask':
			$source = $this->params['source'];
			$target = $this->params['target'];
			$parentwindow = $this->params['openerRef'];
			$before = $this->params['before'];
			$confirmed = $this->params['confirmed'];
			$positive = $this->params['positive'];

			if ($source == $target) {
				break;
			}

			if ($entrymaskMgr->tree->moveTo($source, $target)) {
				$parent_ids = array();
				$parents = $entrymaskMgr->getParents($source);

				foreach($parents as $parent_item) {
					array_push( $parent_ids, $parent_item[0]['ID'] );
				}
				$parent_ids = array_reverse( $parent_ids );
				array_shift( $parent_ids );
				array_push( $parent_ids, $source );
				$parent_ids = implode(',', $parent_ids);

				$koala->callJSFunction( 'if (typeof(Koala.yg_dndOnSuccess) == "function") Koala.yg_dndOnSuccess' );
				$koala->callJSFunction( 'Koala.yg_selectTreeNode', $parentwindow, 'page', $source );
			} else {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			}
			break;

		case 'addEntrymask':
			$entrymask = $this->params['entrymask'];

			// Check if root node is selected
			if ($entrymask==='root') {
				// Get real ID of Root-Node
				$entrymasks =  $entrymaskMgr->getList();
				$entrymask = $entrymasks[0]['ID'];
			}

			// Check if the parent is really a folder
			$parentinfo = $entrymaskMgr->get( $entrymask );
			if ($parentinfo['FOLDER'] == 1) {
				// Add new child node
				$new_id = $entrymaskMgr->add( $entrymask );

				if ( $new_id != false ) {
					$icons = new Icons();
					$koala->callJSFunction( 'Koala.yg_addChild', 'entrymask', $entrymask.'-entrymask', 'name', ($itext['TXT_NEW_OBJECT']!='')?($itext['TXT_NEW_OBJECT']):('$TXT_NEW_OBJECT'), 'entrymask', $new_id.'-entrymask', 'name', $icons->icon['entrymask_small'], 'nosub', true );
				} else {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				}
			}
			break;

		case 'savePageEntrymask':
			$data = json_decode( $this->params['allData'], true );

			$coid = $data['coid'];
			$id = $data['id'];
			$objectID = $data['page'];
			$ocb = sCblockMgr()->getCblock($coid);
			$controlchanged = false;
			$currentContentarea = 0;
			$templateMgr = new Templates();

			// Check if entrymask is used on pages or on contentblocks (for later creation of history entries)
			if ($data['site'] == 'cblock') {
				// is used in contentblock
				$cb =  sCblockMgr()->getCblock($objectID);
				$history = $cb->history;
				$historyType = HISTORYTYPE_CO;
			} elseif ($data['site'] == 'mailing') {
				// Is used in mailings
				$mailingMgr = new MailingMgr();
				$mailing = $mailingMgr->getMailing($objectID);
				$history = $mailing->history;
				$historyType = HISTORYTYPE_MAILING;

				$mailingInfo = $mailing->get();
				$pageContentarea = $templateMgr->getContentareas($mailingInfo["TEMPLATEID"]);

				for ($i = 0; $i < count($pageContentarea); $i++) {
					$coList = $mailing->getCblockList($pageContentarea[$i]["CODE"]);
					foreach($coList as $coListItem) {
						if ($coListItem['OBJECTID'] == $coid) {
							$currentContentarea = $pageContentarea[$i]['ID'];
						}
					}
				}
			} else {
				// is used in pages
				$pageMgr = new PageMgr($data['site']);
				$page = $pageMgr->getPage($objectID);
				$history = $page->history;
				$historyType = HISTORYTYPE_PAGE;

				$pageInfo = $page->get();
				$pageContentarea = $templateMgr->getContentareas($pageInfo["TEMPLATEID"]);

				for ($i = 0; $i < count($pageContentarea); $i++) {
					$coList = $page->getCblockList($pageContentarea[$i]["CODE"]);
					foreach($coList as $coListItem) {
						if ($coListItem['OBJECTID'] == $coid) {
							$currentContentarea = $pageContentarea[$i]['ID'];
						}
					}
				}
			}
			$linkInfo = sCblockMgr()->getCblockLinkByLinkId($id);

			$cb = sCblockMgr()->getCblock($coid, $linkInfo[0]["CBLOCKVERSION"]);
			$cbInfo = $cb->get();
			$controlFormfields = $cb->getFormfieldsInternal($id);

			for ($c = 0; $c < count($controlFormfields); $c++) {
				$linkid = $controlFormfields[$c]['ID'];
				$formfield = $controlFormfields[$c]['FORMFIELD'];
				$formfieldchanged = false;

				// Then check if new data was submitted and overwrite the previous values
				for($i=1;$i<=8;$i++) {
					$idx = sprintf('%02d', $i);
					$varname = 'param'.$idx;

					// First initialize with old data
					$$varname = $controlFormfields[$c]['VALUE'.$idx];

					// Then check if new data was submitted and overwrite the previous values
					if ( (!is_null($data[$linkid.'-VALUE'.$idx])) &&
						 ($data[$linkid.'-VALUE'.$idx]!=$controlFormfields[$c]['VALUE'.$idx]) ) {
						$formfieldchanged = true;
						$$varname = $data[$linkid.'-VALUE'.$idx];
					}
				}

				// Only proceed if something has changed
				if (!$formfieldchanged) continue;

				$controlchanged = true;

				// Textfeld
				if ($formfield == 1) {
					$param01 = convertShortURLsToSpecialURLs($param01);
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_1', $data['formfieldid'], $currentContentarea );
					}
				}

				// Textarea
				if ($formfield == 2) {
					$param01 = convertShortURLsToSpecialURLs($param01);
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_2', $data['formfieldid'], $currentContentarea );
					}
				}

				// WYSIWYG
				if ($formfield == 3) {
					$param01 = convertShortURLsToSpecialURLs($param01);
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					$reftracker->updateReferencesFromHtml(REFTYPE_FORMFIELD, $linkid, $cbInfo['VERSION'], $param01.$param02.$param03.$param04.$param05.$param06.$param07.$param08);
					if ($controlFormfields[$c]["VALUE01"]<>$param01) {
						$history->add( $historyType, NULL, NULL, 'TXT_COMMON_H_COEDIT_FRMFLD_3', $data['formfieldid'], $currentContentarea );
					}
				}

				// Checkbox
				if ($formfield == 4) {
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						if ($param01 > 0) {
							$history->add( $historyType, NULL, NULL, 'TXT_COMMON_H_COEDIT_FRMFLD_4_ON', $data['formfieldid'], $currentContentarea );
						} else {
							$history->add( $historyType, NULL, NULL, 'TXT_COMMON_H_COEDIT_FRMFLD_4_OFF', $data['formfieldid'], $currentContentarea );
						}
					}
				}

				// Link
				if ($formfield == 5) {
					// Check if link is an integer (then we only link a file)
					if (is_numeric($param01)) {
						$param01 = trim($this->base.'download/'.$param01);
					} else {
						$param01 = trim(prettifyUrl($param01));
						$result = checkLinkInternalExternal($param01);
						if ($result['TYPE']!='external') {
							$param01 = createSpecialURLfromShortURL($param01);
						}
					}
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					$reftracker->updateUrlRef(REFTYPE_FORMFIELD, $linkid, $cbInfo['VERSION'], $param01);

					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_5', $data['formfieldid'], $currentContentarea );
					}
				}

				// File
				if ($formfield == 6) {
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					$reftracker->updateFileRef(REFTYPE_FORMFIELD, $linkid, $cbInfo['VERSION'], $param01);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_6', $data['formfieldid'], $currentContentarea );
					}
				}

				// Filefolder
				if ($formfield == 16) {
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					$reftracker->updateFileRef(REFTYPE_FORMFIELD, $linkid, $cbInfo['VERSION'], $param01);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_16', $data['formfieldid'], $currentContentarea );
					}
				}

				// Contentblock
				if ($formfield == 7) {
					$reftracker->updateCblockRef(REFTYPE_FORMFIELD, $linkid, $cbInfo['VERSION'], $param01);
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_7', $data['formfieldid'], $currentContentarea );
					}
				}

				// Tag
				if ($formfield == 8) {
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_8', $data['formfieldid'], $currentContentarea );
					}
				}

				// Dropdown-List
				if ($formfield == 9) {
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_9', $data['formfieldid'], $currentContentarea );
					}
				}

				// Password
				if ($formfield == 10) {
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_10', $data['formfieldid'], $currentContentarea );
					}
				}

				// Date
				if ($formfield == 11) {
					$hour = $minute = 0;
					$date = explode('.', $param01);
					$day	= (int)$date[0];
					$month  = (int)$date[1];
					$year   = (int)$date[2];

					$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
					$timestamp = TSfromLocalTS($timestamp);
					$param01 = $timestamp;

					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_11', $data['formfieldid'], $currentContentarea );
					}
				}

				// Datetime
				if ($formfield == 12) {
					$dateFrac = explode('||', $param01);
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
					$day	= (int)$date[0];
					$month  = (int)$date[1];
					$year   = (int)$date[2];

					$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
					$timestamp = TSfromLocalTS($timestamp);
					$param01 = $timestamp;

					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_12', $data['formfieldid'], $currentContentarea );
					}
				}

				// Cutline
				if ($formfield == 14) {
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01, 'TXT_COMMON_H_COEDIT_FRMFLD_14', $data['formfieldid'], $currentContentarea );
					}
				}

				// Page
				if ($formfield == 15) {
					$internPrefix = (string)sConfig()->getVar('CONFIG/REFTRACKER/INTERNALPREFIX');
					$reftracker->updateUrlRef(REFTYPE_FORMFIELD, $linkid, $cbInfo['VERSION'], $internPrefix.'page/'.$param02.'/'.$param01.'/');
					$cb->setFormfield($linkid, $param01, $param02, $param03, $param04, $param05, $param06, $param07, $param08);
					if ($controlFormfields[$c]['VALUE01']<>$param01) {
						$history->add( $historyType, NULL, $param01.'-'.$param02, 'TXT_COMMON_H_COEDIT_FRMFLD_15', $data['formfieldid'], $currentContentarea );
					}
				}

			}

			if ($controlchanged) {
				if ($data['site'] == 'cblock') {
					// Trigger refresh of list entries
					$jsQueue->add ($objectID, HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $objectID.'-cblock', 'listitem');
					$jsQueue->add ($objectID, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');
				} elseif ($data['site'] == 'mailing') {
					$jsQueue = new JSQueue(NULL);
					$jsQueue->add ($data['page'], HISTORYTYPE_MAILING, 'HIGHLIGHT_MAILING', sGuiUS(), 'name');
				} else {
					$jsQueue = new JSQueue(NULL, $data['site']);
					$jsQueue->add ($data['page'], HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');
				}
				$koala->callJSFunction( 'Koala.yg_fade', 'formfield', $data['formfieldid'].'-formfield', 'value' );
			}
			break;

		case 'addCBlockEntrymask':
			$cblockID = $this->params['page'];
			$cb = sCblockMgr()->getCblock($cblockID);
			$entrymask = $this->params['entrymaskId'];
			$contentarea = $this->params['contentareaID'];
			$parentwindow = $this->params['openerRefID'];
			$refresh = $this->params['refresh'];
			// Workaround for frontend-refresh
			$refresh = 'true';

			$eminfo = $entrymaskMgr->get( $entrymask );

			// Add requested control to contentblock
			if (($new_control = $cb->addEntrymask($entrymask)) === false) {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			} else {

				if ($refresh==='true') {
					$emlist = $cb->getEntrymasks();
					$js_array = '[ ';
					foreach ($emlist as $entrymask_list_item) {
						$js_array .= "[ '".$cblockID."', '".(($itext['TXT_CONTENT']!='')?($itext['TXT_CONTENT']):('$TXT_CONTENT'))."', '0', '".$entrymask_list_item['LINKID']."'  ], ";
					}
					$js_array = substr($js_array, 0, strlen($js_array)-2);
					$js_array .= ' ]';

					// Add to history
					$cb->history->add( HISTORYTYPE_CO, 0, $eminfo['NAME'], 'TXT_CBLOCK_H_EMADD', $new_control );
					$koala->queueScript( "Koala.windows['wid_".$parentwindow."'].getContentareaDataFuncs( ".$js_array.", true, (function() { if ($('wid_".$parentwindow."_cblock_0_".$new_control."_emcontentinner')) { $('wid_".$parentwindow."_cblock_0_".$new_control."_emcontentinner').down('.maskdisplay').onclick(); }; }) );" );

					$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');
				}

			}
			break;

		case 'addPageEntrymask':

			$pageID = $this->params['page'];
			$siteID = $this->params['site'];

			$entrymask = $this->params['entrymaskId'];
			$contentarea = $this->params['contentareaID'];
			$parentwindow = $this->params['openerRefID'];
			$refresh = $this->params['refresh'];
			$templateMgr = new Templates();
			$tagMgr = new Tags();
			// Workaround for frontend-refresh
			$refresh = 'true';

			if ($siteID == 'mailing') {
				// For mailings
				$myMgr = new MailingMgr();
				$myObject = $myMgr->getMailing($pageID);
				$myObjectInfo = $myObject->get();
				$historyType = HISTORYTYPE_MAILING;
				$historyStr = "MAILING";
				$myQueue = new JSQueue(NULL);
			} else {
				// For pages
				$myMgr = new PageMgr($siteID);
				$myObject = $myMgr->getPage($pageID);
				$myObjectInfo = $myObject->get();
				$historyType = HISTORYTYPE_PAGE;
				$historyStr = "PAGE";
				$myQueue = new JSQueue(NULL, $siteID);
			}

			$contentarea = str_replace( 'wid_'.$parentwindow.'_ca_', '', $contentarea );

			// Add new contentblock to folder
			$contentblockID = $myObject->addCblockEmbedded($contentarea);
			$newcb = sCblockMgr()->getCblock($contentblockID);
			$control_props = $entrymaskMgr->get($entrymask);
			$newcb->properties->setValue ("NAME", $control_props['NAME']);

			// Add requested control to contentblock
			$new_control = $newcb->addEntrymask($entrymask);

			if ($contentblockID > 0) {

				// Get the LinkId of the newly created contentblock
				$new_lnkid = $myObject->getEmbeddedCblockLinkId($contentblockID);

				$myObject->addCblockVersion($contentblockID, $contentarea, 1);

				$contentareas = $templateMgr->getContentareas( $myObjectInfo['TEMPLATEID'] );

				for ($i = 0; $i < count($contentareas); $i++) {
					if ($contentareas[$i]['CODE']==$contentarea) {

						$target_contentarea = $contentareas[$i]['ID'];

						$colist = $myObject->getCblockList($contentareas[$i]['CODE']);

						for ($x = 0; $x < count($colist);$x++) {
							$coid = $colist[$x]['OBJECTID'];
							$lcb = sCblockMgr()->getCblock($coid, $colist[$x]['VERSION']);
							if ($coid > 0) {
								$colist[$x]['CBVERSION'] = $colist[$x]['VERSION'];
								$colist[$x]['ENTRYMASKS'] = $lcb->getEntrymasks();

								for ($c = 0; $c < count($colist[$x]['ENTRYMASKS']); $c++) {
									$controlFormfields = $lcb->getFormfieldsInternal($colist[$x]['ENTRYMASKS'][$c]['LINKID']);
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
												$cbw = sCblockMgr()->getCblock($controlFormfields[$w]['VALUE01']);
												$info = $cbw->get();
												$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
											}
										}
										if ($controlFormfields[$w]['FORMFIELD'] == 8) {
											if ($controlFormfields[$w]['VALUE01']) {
												$info = $tagMgr->get($controlFormfields[$w]['VALUE01']);
												$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
											}
										}
									}
									$colist[$x]['ENTRYMASKS'][$c]['FORMFIELDS'] = $controlFormfields;
								}

							}
						}
						$contentareas[$i]['LIST'] = $colist;

					}
				}

				// Save order here
				$colnkorder = array();
				foreach ($contentareas as $contentareas_item) {
					if ($contentareas_item['CODE']==$contentarea) {
						$curr_contentarea_list = $contentareas_item['LIST'];
					}
				}
				foreach ($curr_contentarea_list as $curr_contentarea_list_item) {
					$colnkorder[] = $curr_contentarea_list_item['LINKID'];
				}
				$myObject->setCblockLinkOrder( $colnkorder );

				if ($refresh==='true') {

					$dta_cnt = 0;
					$js_array = '[ ';
					foreach ($contentareas as $contentarea_item) {
						foreach ($contentarea_item['LIST'] as $contentarea_list_item) {
							$js_array .= "[ '".$contentarea_list_item['OBJECTID']."', '".$contentarea_item['CODE']."', '".$contentarea_item['ID']."', '".$contentarea_list_item['LINKID']."' ], ";
							$dta_cnt++;
						}
					}

					if (strlen($js_array)>2) {
						$js_array = substr($js_array, 0, strlen($js_array)-2);
					}
					$js_array .= ' ]';

					// Add to history
					$myObject->history->add( $historyType, $contentarea, $control_props['NAME'], 'TXT_'.$historyStr.'_H_EMADD', $contentblockID );

					$koala->queueScript( "Koala.windows['wid_".$parentwindow."'].getContentareaDataFuncs( ".$js_array.", true, (function() { if ($('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."_emcontentinner')) { $('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."_emcontentinner').down('.maskdisplay').onclick(); }; }) );" );

					$myQueue->add ($pageID, $historyType, 'HIGHLIGHT_'.$historyStr, sGuiUS(), 'name');
				}
				// END GET CONTENTAREADATA
			}
			break;

		case 'addPositionedPageEntrymask':

			$pageID = $this->params['page'];
			$siteID = $this->params['site'];

			if ($siteID == 'mailing') {
				// For mailings
				$myMgr = new MailingMgr();
				$myObject = $myMgr->getMailing($pageID);
				$myObjectInfo = $myObject->get();
				$historyType = HISTORYTYPE_MAILING;
				$historyStr = "MAILING";
				$myQueue = new JSQueue(NULL);
				//$myObject->setStatus('UNSENT');
			} else {
				// For pages
				$myMgr = new PageMgr($siteID);
				$myObject = $myMgr->getPage($pageID);
				$myObjectInfo = $myObject->get();
				$historyType = HISTORYTYPE_PAGE;
				$historyStr = "PAGE";
				$myQueue = new JSQueue(NULL, $siteID);
			}

			$entrymask = $this->params['entrymaskId'];
			$contentarea_id = $this->params['contentareaID'];
			$parentwindow = $this->params['openerRefID'];
			$target_id = $this->params['targetId'];
			$target_pos = $this->params['targetPosition'];
			$refresh = $this->params['refresh'];
			$copymode = $this->params['copymode'];
			$templateMgr = new Templates();
			// Workaround for frontend-refresh
			$refresh = 'true';

			$contentarea_id = str_replace( 'wid_'.$parentwindow.'_ca_', '', $contentarea_id );

			// Get code for contentarea_id
			$contentarea = '';
			$contentareas = $templateMgr->getContentareas($myObjectInfo['TEMPLATEID']);
			foreach($contentareas as $pagecontentarea) {
				if($pagecontentarea['ID']==$contentarea_id) {
					$contentarea = $pagecontentarea['CODE'];
				}
			}

			// Add new contentblock to folder
			$contentblockID = $myObject->addCblockEmbedded($contentarea);
			$newcb = sCblockMgr()->getCblock($contentblockID);

			if ($copymode) {
				// In Copymode, the entrymask is really the link between co<->entrymasks
				$sourceContentBlockInfo = sCblockMgr()->getCblockLinkByEntrymaskLinkId( $entrymask );
				$sourcecb = sCblockMgr()->getCblock($sourceContentBlockInfo['CBLOCKID']);
				$control_props = $entrymaskMgr->get($sourceContentBlockInfo['ENTRYMASK']);
			} else {
				$control_props = $entrymaskMgr->get($entrymask);
			}

			$newcb->properties->setValue("NAME", $control_props['NAME']);

			// Add requested control to contentblock
			if ($copymode) {
				// In Copymode, the entrymask is really the link between co<->entrymasks
				$new_control = $newcb->addEntrymask($sourceContentBlockInfo['ENTRYMASK']);
			} else {
				$new_control = $newcb->addEntrymask($entrymask);
			}

		  	if ($contentblockID > 0) {
				$myObject->addCblockVersion($contentblockID, $contentarea, 1);

				// Get the LinkId of the newly created contentblock
				$new_cblock_lnkid = $myObject->getEmbeddedCblockLinkId($contentblockID);

				if ($copymode) {
					// In copymode, copy all the contents of the formfields
					$oldFormfieldContent = $sourcecb->getFormfieldsInternal($entrymask);
					$newFormfieldContent = $newcb->getFormfieldsInternal($new_control);
					for ($c = 0; $c < count($newFormfieldContent); $c++) {
						$newcb->setFormfield($newFormfieldContent[$c]['LINKID'],
								$oldFormfieldContent[$c]['VALUE01'],
								$oldFormfieldContent[$c]['VALUE02'],
								$oldFormfieldContent[$c]['VALUE03'],
								$oldFormfieldContent[$c]['VALUE04'],
								$oldFormfieldContent[$c]['VALUE05'],
								$oldFormfieldContent[$c]['VALUE06'],
								$oldFormfieldContent[$c]['VALUE07'],
								$oldFormfieldContent[$c]['VALUE08']
						);
					}
				}

				for ($i = 0; $i < count($contentareas); $i++) {
					if ($contentareas[$i]['CODE']==$contentarea) {

						$target_contentarea = $contentareas[$i]['ID'];

						$colist = $myObject->getCblockList($contentareas[$i]['CODE']);
						for ($x = 0; $x < count($colist);$x++) {
							if ($colist[$x]['OBJECTID'] > 0) {
								$lcb = sCblockMgr()->getCblock($colist[$x]['OBJECTID'], $colist[$x]['VERSION']);
								$colist[$x]['CBVERSION'] = $colist[$x]['VERSION'];
								$colist[$x]['ENTRYMASKS'] = $lcb->getEntrymasks();

								for ($c = 0; $c < count($colist[$x]['ENTRYMASKS']); $c++) {
									$controlFormfields = $lcb->getFormfieldsInternal($colist[$x]['ENTRYMASKS'][$c]['LINKID']);
									for ($w = 0; $w < count($controlFormfields); $w++) {
										if (($controlFormfields[$w]['FORMFIELD'] == 6) || ($controlFormfields[$w]['FORMFIELD'] == 16)) {
											$file = sFileMgr()->getFile($controlFormfields[$w]['VALUE01']);
											if ($file) {
												$fileInfo = $file->get();
												$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
											}
										}
										if ($controlFormfields[$w]['FORMFIELD'] == 7) {
											$cocb = sCblockMgr()->getCblock($controlFormfields[$w]['VALUE01']);
											if ($cocb) {
												$info = $cocb->get();
												$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
											}
										}
										if ($controlFormfields[$w]['FORMFIELD'] == 8) {
											$info = $myObject->tags->get($controlFormfields[$w]['VALUE01']);
											$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
										}
									}
									$colist[$x]['ENTRYMASKS'][$c]['FORMFIELDS'] = $controlFormfields;
								}

							}
						}
						$contentareas[$i]['LIST'] = $colist;
					}
				}

				// Move contentblock to target position
				$colist = $myObject->getCblockList($contentarea);

				$colist_new = array();
				for ($i = 0; $i < count($colist); $i++) {
					$colist_new[$i]['OBJECTID'] = $colist[$i]['OBJECTID'];
					$colist_new[$i]['LINKID'] = $colist[$i]['LINKID'];
					if ($target_id == $colist[$i]['LINKID']) {
						if ($target_pos==='before') {
							$targetpos = $i;
						} else {
							$targetpos = $i+1;
						}
					}
				}
				$colist = $colist_new;

				if ( ($target_pos !== 'into') && ($target_pos !== NULL) ) {
					$removed = array_splice($colist, $targetpos);
					$temp_id = count($colist);
					$colist[$temp_id]['OBJECTID'] = $removed[count($removed)-1]['OBJECTID'];
					$colist[$temp_id]['LINKID'] = $removed[count($removed)-1]['LINKID'];
					$colist = array_merge($colist, $removed);
					array_pop($colist);
				}

				// Create new array for saveorder...
				foreach($colist as $colist_item) {
					$finalcolist[] = $colist_item['LINKID'];
				}

				$myObject->setCblockLinkOrder( $finalcolist );

				$dta_cnt = 0;

				if ($refresh==='true') {
					$js_array = '[ ';
					foreach ($contentareas as $contentarea_item) {
						if ($contentarea_item['ID']==$contentarea_id) {
							foreach ($colist as $contentarea_list_item) {
								$dta_cnt++;
								$js_array .= "[ '".$contentarea_list_item['OBJECTID']."', '".$contentarea_item['CODE']."', '".$contentarea_item['ID']."', '".$contentarea_list_item['LINKID']."' ], ";
							}
						}
					}

					$js_array = substr($js_array, 0, strlen($js_array)-2);

					$js_array .= ' ]';

					// Add to history
					$myObject->history->add( $historyType, $contentarea, $control_props['NAME'], 'TXT_'.$historyStr.'_H_EMADD', $contentblockID );

					$koala->queueScript( "Koala.windows['wid_".$parentwindow."'].getContentareaDataFuncs( ".$js_array.", true, (function() { if ($('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_cblock_lnkid."_emcontentinner')) { $('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_cblock_lnkid."_emcontentinner').down('.maskdisplay').onclick(); }; }) );" );

					$myQueue->add ($pageID, $historyType, 'HIGHLIGHT_'.$historyStr, sGuiUS(), 'name');
				}

		  	}
			break;

		case 'addPositionedControlEntrymask':

			$contentblockID = $this->params['page'];
			$cb = sCblockMgr()->getCblock($contentblockID);
			$entrymask = $this->params['entrymaskId'];
			$parentwindow = $this->params['openerRefID'];
			$target_id = $this->params['targetId'];
			$target_pos = $this->params['targetPosition'];
			$refresh = $this->params['refresh'];
			// Workaround for frontend-refresh
			$refresh = 'true';

			$eminfo = $entrymaskMgr->get( $entrymask );

			// Add requested control to contentblock
			$new_control = $cb->addEntrymask($entrymask);

			// Move entrymask to target position
			$emlist = $cb->getEntrymasks();
			$emlist_new = array();
			for ($i = 0; $i < count($emlist); $i++) {
				$emlist_new[$i]['ID'] = $emlist[$i]['ID'];
				$emlist_new[$i]['LINKID'] = $emlist[$i]['LINKID'];
				if ($target_id == $emlist[$i]['LINKID']) {
					if ($target_pos==='before') {
						$targetpos = $i;
					} else {
						$targetpos = $i+1;
					}
				}
			}


			if ( ($target_pos !== 'into') && ($target_pos !== NULL) ) {
				$emlist = $emlist_new;
				$removed = array_splice($emlist, $targetpos);
				$temp_id = count($emlist);
				$emlist[$temp_id]['ID'] = $contentblockID;
				$emlist[$temp_id]['LINKID'] = $new_control;
				$emlist = array_merge($emlist, $removed);
				array_pop($emlist);
			}

			// Create new array for saveorder...
			foreach($emlist as $emlist_item) {
				$finalemlist[] = $emlist_item['LINKID'];
			}

			if ($cb->setEntrymaskOrder( $finalemlist ) === false) {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			} else {

				if ($refresh==='true') {
					$dta_cnt = 0;
					$js_array = '[ ';
					foreach ($emlist as $entrymask_list_item) {
						$dta_cnt++;
						$js_array .= "[ '".$contentblockID."', '".(($itext['TXT_CONTENT']!='')?($itext['TXT_CONTENT']):('$TXT_CONTENT'))."', '0', '".$entrymask_list_item['LINKID']."'  ], ";
					}
					$js_array = substr($js_array, 0, strlen($js_array)-2);
					$js_array .= ' ]';

					// Add to history
					$cb->history->add( HISTORYTYPE_CO, 0, $eminfo['NAME'], 'TXT_CBLOCK_H_EMADD', $new_control );
					$koala->queueScript( "Koala.windows['wid_".$parentwindow."'].getContentareaDataFuncs( ".$js_array.", true, (function() { if ($('wid_".$parentwindow."_cblock_0_".$new_control."_emcontentinner')) { $('wid_".$parentwindow."_cblock_0_".$new_control."_emcontentinner').down('.maskdisplay').onclick(); }; }) );" );

					$jsQueue->add ($contentblockID, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');
				}

			}
			break;

		case 'setEntrymaskName':

			// Cannot use new style of parameters here (function is called by a custom-attribute event - and therefore does not know about named parameters)
			// Split PageID and SiteID
			$data = explode('-', $this->reponsedata['name']->yg_id );

			$entrymaskInfo = $entrymaskMgr->get($data[0]);
			//$entrymaskCode = $entrymaskInfo['']

			// Set the new name
			if ( $entrymaskMgr->setInfo($data[0], $this->reponsedata['name']->value, $entrymaskInfo['CODE']) === false ) {
				$koala->callJSFunction( 'Koala.yg_error', 'entrymask', $objectID.'-entrymask', 'code' );
				$haderror = true;
			} else {
				$jsQueue->add ($data[0], HISTORYTYPE_ENTRYMASK, 'UNHIGHLIGHT_ENTRYMASK', sGuiUS(), 'code');
				$jsQueue->add ($data[0], HISTORYTYPE_ENTRYMASK, 'OBJECT_CHANGE', sGuiUS(), 'entrymask', NULL, NULL, $data[0].'-entrymask', 'name', $this->reponsedata['name']->value);
			}
			break;

	}

?>