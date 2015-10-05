<?php

	$jsQueue = new JSQueue(NULL);
	$siteMgr = new Sites();

	switch ($action) {

		case 'setCBlockName':
			// Cannot use new style of parameters here (function is called by a custom-attribute event - and therefore does not know about named parameters)
			// Split PageID and SiteID
			$data = explode('-', $this->reponsedata['name']->yg_id );
			$cb = sCblockMgr()->getCblock($data[0]);
			$cblockInfo = $cb->get();

			// Set the new name
			if ($cb->properties->setValue('NAME', $this->reponsedata['name']->value) === false) {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			} else {
				$jsQueue->add ($data[0], HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $this->reponsedata['name']->yg_id, 'name', $this->reponsedata['name']->value);
				if ($cblockInfo['PNAME'] == NULL) {
					$PName = $cb->calcPName();
					$cb->setPName($PName);
					//$jsQueue->add ($data[0], HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $data[0].'-cblock', 'pname', $PName);
					$jsQueue->add ($data[0], HISTORYTYPE_CO, 'OBJECT_CHANGEPNAME', sGuiUS(), 'cblock', NULL, NULL, $data[0].'-cblock', 'name', $PName);
					$jsQueue->add ($data[0], HISTORYTYPE_CO, 'REFRESH_WINDOW', sGuiUS(), 'pname');
				}
				$jsQueue->add ($data[0], HISTORYTYPE_CO, 'REFRESH_WINDOW', sGuiUS(), 'name');
			}
			break;

		case 'setCBlockPName':
			$cblockID = $this->params['cblock'];

			$cb = sCblockMgr()->getCblock($cblockID);
			$value = $cb->filterPName($this->params['value']);

			if (sCblockMgr()->getCblockIdByPName($value)) {
				$koala->callJSFunction('Koala.yg_promptbox', $itext['TXT_ERROR'], $itext['TXT_PNAME_ALREADY_USED_CHOOSE_ANOTHER'], 'alert');
				$cblockInfo = $cb->get();
				$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $cblockID.'-cblock', 'pname', $cblockInfo['PNAME']);
				break;
			}

			$cb->setPName($value);
			$newCBInfo = $cb->get();

			$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $cblockID.'-cblock', 'pname', $newCBInfo['PNAME']);
			$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'OBJECT_CHANGEPNAME', sGuiUS(), 'cblock', NULL, NULL, $cblockID.'-cblock', 'name', $newCBInfo['PNAME']);
			$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'REFRESH_WINDOW', sGuiUS(), 'pname');

			// Add to history
			$cb->history->add (HISTORYTYPE_CO, NULL, $value, 'TXT_CBLOCK_H_PNAME');
			break;

		case 'restoreCBlockVersion':
			$version = $this->params['version'];
			$cblockID = $this->params['cblock'];
			$wid = $this->params['wid'];

			$oldcb = sCblockMgr()->getCblock($cblockID, $version);
			$oldinfo = $oldcb->get();

			$new_version = $oldcb->newVersion();

			$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $cblockID.'-cblock', 'name', $oldinfo['NAME']);
			$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');

			$koala->queueScript('Koala.windows[\'wid_'.$wid.'\'].tabs.select($K.windows[\'wid_'.$wid.'\'].tabs.selected,Koala.windows[\'wid_'.$wid.'\'].tabs.params);');
			break;

		case 'saveCBlockVersion':

			// Cannot use new style of parameters here (function is called by a custom-attribute event - and therefore does not know about named parameters)
			$data = explode('-', $this->reponsedata['null']->yg_id );
			$cblockID = $data[0];
			$wid = $data[2];

			$oldcb = sCblockMgr()->getCblock($cblockID);
			$oldinfo = $oldcb->get();
			$oldcb->newVersion();

			$koala->queueScript('Koala.windows[\'wid_'.$wid.'\'].tabs.select(Koala.windows[\'wid_'.$wid.'\'].tabs.selected, Koala.windows[\'wid_'.$wid.'\'].tabs.params);');
			break;

		case 'orderEditContentblock':
			$newcolists = json_decode( $this->params['newcolists'], true );

			$mode = $this->params['mode'];
			$cblockID = $this->params['page'];
			$parentwindow = $this->params['winID'];

			$cb = sCblockMgr()->getCblock($cblockID);
			$cblockInfo = $cb->get( );

			// Get current data for the current contentblock
			$colistids = array();
			$colist = $cb->getEntrymasks( );

			foreach($colist as $colistitem) {
				$colistids[] = array( $cblockID, (string)$colistitem['LINKID'] );
			}
			$oldcolists = array( $colistids );

			// Check if a contentblock was added
			$onlysorted = true;
			if ( (count($oldcolists[0])) != (count($newcolists[0])) ) {

				// Check in which new object was added
				if ( (count($oldcolists[0])) < (count($newcolists[0])) ) {
					$target_contentarea = 0;

					// Check which contentblock was moved
					for($i=0;$i<count($newcolists[0]);$i++) {
						$found_co = false;
						for($j=0;$j<count($oldcolists[0]);$j++) {
							if ($newcolists[0][$i][1] == $oldcolists[0][$j][1])
								$found_co = true;
						}
						if (!$found_co) {
							$moved_co = $newcolists[0][$i][0];
							$moved_colnk = $newcolists[0][$i][1];
						}
					}

				} elseif ( (count($oldcolists[0])) > (count($newcolists[0])) ) {
					$source_contentarea = 0;

					// Check which contentblock was moved
					for($i=0;$i<count($oldcolists[0]);$i++) {
						$found_co = false;
						for($j=0;$j<count($newcolists[0]);$j++) {
							if ($oldcolists[0][$i][1] == $newcolists[0][$j][1])
								$found_co = true;
						}
						if (!$found_co) {
							$moved_co = $oldcolists[0][$i][0];
							$moved_colnk = $oldcolists[0][$i][1];
						}
					}

				}
				$onlysorted = false;
			}

			// Check which contentarea was changed
			$changed_ca = 0;

			// Check if new entrymask (from entrymasktree)
			if (substr($moved_co,0,5)=='dummy') {
				$entrymask = substr($moved_co,5);

				// Add requested control to contentblock
				$new_control = $cb->addEntrymask($entrymask);

				// Change the id in the $newcolist array
				foreach ($newcolists[0] as $idx => $item) {
					if ($item[0]==$moved_co) {
						$newcolists[0][$idx][0] = $cblockID;
						$newcolists[0][$idx][1] = $new_control;
					}
				}

				$moved_co = $new_control;
				$moved_object = 'emblock';

				// Generate a complete new colist (for ordering)
				$newcolist = array();
				foreach ($newcolists[0] as $newcolists_item) {
					array_push( $newcolist, $newcolists_item[1] );
				}

			}

			// Check if new entrymask (from contentblocks-tree -> preview )
			if (($moved_co != 0) || ($moved_co=='emblock')) {
				$moved_cb = sCblockMgr()->getCblock($moved_co);

				// Check which entrymasks are contained
				if ( ($moved_co!='emblock') && ($moved_co != $new_control)) {
					$co_controllinks = $moved_cb->getEntrymasks( );
					$moved_colnk = $co_controllinks[0]['LINKID'];
				} else {
					$co_controllinks = array( array('LINKID' => $moved_colnk) );
				}

				$cos_added = array();
				$tmpCb = sCblockMgr()->getCblock($cblockID);
				foreach($co_controllinks as $co_controllink) {
					$controlinfo = sCblockMgr()->getCblockLinkByEntrymaskLinkId($co_controllink['LINKID']);
					$new_control = $cb->addEntrymask($controlinfo['ENTRYMASK']);
					$ccb = sCblockMgr()->getCblock($controlinfo['CBLOCKID']);
					$controlFormfields = $ccb->getFormfieldsInternal($co_controllink['LINKID']);
					array_push($cos_added, $new_control);

					// Get Formfields for control
					$controlFormfields_new = $cb->getFormfieldsInternal($new_control);
				}

				// Generate a complete new colist (for ordering)
				$newcolist = array();
				foreach ($newcolists[0] as $newcolists_item) {
					if ($newcolists_item[0]==$moved_co) {
						foreach($cos_added as $cos_added_item) {
							array_push( $newcolist, $cos_added_item );
						}
					} else {
						array_push( $newcolist, $newcolists_item[1] );
					}
				}

				$onlysorted = false;
			}

			if ($onlysorted) {
				$newcolist = array();
				foreach ($newcolists[0] as $newcolists_item) {
					array_push( $newcolist, $newcolists_item[1] );
				}
			}

			// Save everything
			if ($cb->setEntrymaskOrder($newcolist) === false) {
				$koala->alert($itext['TXT_ERROR_ACCESS_DENIED']);
			} else {
				// Add to history
				$cb->history->add (HISTORYTYPE_CO, 0, $cblockInfo['NAME'], 'TXT_CBLOCK_H_EMORDER', $cblockInfo['OBJECTID'] );
				$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');
			}

			if (!$onlysorted) {

					/* Re-get all controls in contentblock */
					$finalcolist = array();
					$colist = $cb->getEntrymasks( );
					foreach($colist as $colistitem) {
						$finalcolist[] = (string)$colistitem['LINKID'];
					}

					$dta_cnt = 0;
					$js_array = '[ ';
					foreach ($finalcolist as $entrymask_list_item) {
						$dta_cnt++;
						$js_array .= "[ '".$cblockID."', '".(($itext['TXT_CONTENT']!='')?($itext['TXT_CONTENT']):('$TXT_CONTENT'))."', '0', '".$entrymask_list_item."'  ], ";
					}
					$js_array = substr($js_array, 0, strlen($js_array)-2);
					$js_array .= ' ]';

					if ($js_array == ' ]') {
						$js_array = '[]';
					}
					$koala->queueScript( "Koala.windows['wid_".$parentwindow."'].getContentareaDataFuncs( ".$js_array.", true, (function() { if ($('wid_".$parentwindow."cblock_0_".$new_control."_emcontentinner')) { $('wid_".$parentwindow."cblock_0_".$new_control."_emcontentinner').onclick(); }; }) );" );
			}
			$koala->queueScript( "if ($('clone%%cblock__')) { $('clone%%cblock__').remove(); }" );
			break;

		case 'removeCBlockEntrymask':

			$contentblock_lnkid = $this->params['contentblockLnkId'];
			$cblockID = $this->params['cblock'];
			$cb = sCblockMgr()->getCblock($cblockID);
			$cblockInfo = $cb->get();

			if ($cb->removeEntrymask($contentblock_lnkid) === false) {
				$koala->alert($itext['TXT_ERROR_ACCESS_DENIED'] );
				$koala->queueScript("window.delEditContentblock=undefined;");
			} else {
				// Add to history
				$cb->history->add (HISTORYTYPE_CO, 0, $cblockInfo['NAME'], 'TXT_CBLOCK_H_EMREMOVE', $cblockInfo['OBJECTID']);
				$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'OBJECT_DELETE', sGuiUS(), 'cblock', NULL, NULL, $contentblock_lnkid.'-cblock', 'block');
				$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');
			}
			break;

		case 'copyCBlock':

			$source = $this->params['source'];
			$target = $this->params['target'];
			$parentwindow = $this->params['openerRef'];

			$oldsource = $source;
			$oldcb = sCblockMgr()->getCblock($oldsource);
			$oldcoinfo = $oldcb->get();
			$oldcopid = $oldcoinfo['ID'];

			if ($target < 1) {
				$target = sCblockMgr()->tree->getParent($oldsource);
			}
			if ($oldcoinfo['FOLDER']==1) {
				$parent = $target;
				$oldco = $source;
				$oldcopid = $oldcoinfo['ID'];
				$copyjobs = sCblockMgr()->getList($oldco);
				$copyjobs = sCblockMgr()->getAdditionalTreeInfo($oldco, $copyjobs);
				$copystarted = false;
				$idmap = array();
				for ($i = 0; $i < count($copyjobs); $i++) {
					if ($copyjobs[$i]['ID'] == $oldco) {
						if ($copystarted === false) {
							$rootlevel = $copyjobs[$i]["LEVEL"];
							$copystarted = true;
							$co = sCblockMgr()->add($parent, 1);
							$newcb = sCblockMgr()->getCblock($co);
							$idmap[$oldco] = $co;
							$newcb->copyFrom($oldcb);
							$newcb->properties->setValue ("NAME", $oldcoinfo['NAME']);

							// Copy PName (and generate new, if needed)
							$sourcePName = $oldcoinfo['PNAME'];
							if (!$sourcePName) {
								$sourcePName = $oldcoinfo['NAME'];
							}
							if (sCblockMgr()->getCblockIdByPName($sourcePName)) {
								$sourcePName = $newcb->calcPName();
							}
							$newcb->setPName($sourcePName);

							$i++;

							$jsQueue->add ($co, HISTORYTYPE_CO, 'CBLOCK_ADD', sGuiUS(), NULL);
						}
					}
					if (($rootlevel < $copyjobs[$i]["LEVEL"]) && ($copystarted === true)) {
						$myid = $copyjobs[$i]['ID'];
						$mycb = sCblockMgr()->getCblock($myid);
						$myoldparent = $copyjobs[$i]["PARENT"];
						$mynewparent = $idmap[$myoldparent];
						$co = sCblockMgr()->add($mynewparent, $copyjobs[$i]["FOLDER"]);
						$newcb = sCblockMgr()->getCblock($co);

						$idmap[$myid] = $co;
						if ($copyjobs[$i]["FOLDER"] != "1") {
							$newcb->copyFrom($mycb);
							//$oldPublishedVersion = $mycb->getPublishedVersion();
							//$newcb->publishVersion($oldPublishedVersion);

							$newcb->properties->setValue ("NAME", $copyjobs[$i]['NAME']);

							// Copy PName (and generate new, if needed)
							$sourcePName = $oldcoinfo['PNAME'];
							if (!$sourcePName) {
								$sourcePName = $oldcoinfo['NAME'];
							}
							if (sCblockMgr()->getCblockIdByPName($sourcePName)) {
								$sourcePName = $newcb->calcPName();
							}
							$newcb->setPName($sourcePName);

						} else {
							$newcb->copyFrom($mycb);
						}
						$jsQueue->add ($co, HISTORYTYPE_CO, 'CBLOCK_ADD', sGuiUS(), NULL);
					}
					if ($rootlevel >= $copyjobs[$i]["LEVEL"]) {
						if ($copystarted === true) {
							break;
						}
					}
				}
				$koala->callJSFunction( 'Koala.yg_reloadTree', $parentwindow, 'cblock', $parent );
			} else {
				$source = sCblockMgr()->add( $target );
				if ($source>0) {
					$sourcecb = sCblockMgr()->getCblock($source);
					$sourcecb->copyFrom($oldcb);
					$sourcecb->properties->setValue ("NAME", $oldcoinfo['NAME']);

					// Inherit permissions of the parent of the newly created copy
					$sourcecb->permissions->clear();
					$sourcecb->permissions->copyTo( $target, $source );

					// Copy PName (and generate new, if needed)
					$sourcePName = $oldcoinfo['PNAME'];
					if (!$sourcePName) {
						$sourcePName = $oldcoinfo['NAME'];
					}
					if (sCblockMgr()->getCblockIdByPName($sourcePName)) {
						$sourcePName = $sourcecb->calcPName();
					}
					$sourcecb->setPName($sourcePName);

					$jsQueue->add ($source, HISTORYTYPE_CO, 'CBLOCK_ADD', sGuiUS(), NULL);
				} else {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				}
			}
			break;

		case 'moveCBlock':

			$source = $this->params['source'];
			$target = $this->params['target'];
			$parentwindow = $this->params['openerRef'];

			if ($source == $target) {
				break;
			}

			$sourcecb = sCblockMgr()->getCblock($source);
			$targetcb = sCblockMgr()->getCblock($target);

			// Check if source-parent and target have the same id
			$tmpSourceInfo = $sourcecb->get();
			if ($tmpSourceInfo['PARENT'] == $target) {
				// Check if we are restoring a Contentblock
				if ($this->params['orgAction'] == 'restore') {
					$sourcecb->undelete();
					$sourcecb->history->add (HISTORYTYPE_CO, NULL, NULL, 'TXT_CBLOCK_H_RESTORED');
					if ($this->params['lastItem']=='true') {
						$koala->queueScript('Koala.windows[\''.$parentwindow.'\'].tabs.select(Koala.windows[\''.$parentwindow.'\'].tabs.selected,{refresh:1});');
					}
				}
			} elseif (sCblockMgr()->tree->moveTo($source, $target)) {
				// Inherit permissions of the parent of the newly created copy
				$sourcecb->permissions->clear();
				$targetcb->permissions->copyTo($target, $source);

				$parent_ids = array();
				$parents = sCblockMgr()->getParents($source);

				foreach($parents as $parent_item) {
					array_push( $parent_ids, $parent_item[0]['ID'] );
				}
				$parent_ids = array_reverse( $parent_ids );
				array_shift( $parent_ids );
				array_push( $parent_ids, $source );
				$parent_ids = implode(',', $parent_ids);

				if ($this->params['orgAction'] == 'restore') {
					$tmpCblock = sCblockMgr()->getCblock($source);
					$tmpCblock->undelete();
					$sourcecb->history->add (HISTORYTYPE_CO, NULL, NULL, 'TXT_CBLOCK_H_RESTORED');
					if ($this->params['lastItem']=='true') {
						$koala->queueScript('Koala.windows[\''.$parentwindow.'\'].tabs.select(Koala.windows[\''.$parentwindow.'\'].tabs.selected,{refresh:1});');
					}
				} elseif ($this->params['orgAction'] == 'move') {
					$koala->callJSFunction( 'Koala.yg_reloadTree', $parentwindow, 'cblock' );
					$koala->callJSFunction( 'Koala.yg_expandTreeNodes', $parentwindow, 'cblock', $parent_ids, $source, 'true' );
					$koala->callJSFunction( 'Koala.yg_selectTreeNode', $parentwindow, 'cblock', $source );

					/*
					// Get Parent Id
					$parentId = sCblockMgr()->getParents($source);
					$parentId = $parentid[0][0]['ID'];

					$jsQueue->add ($source, HISTORYTYPE_CO, 'OBJECT_DELETE', sGuiUS(), 'cblock', NULL, NULL, $parentId.'-cblock', 'listitem');
					*/
				} elseif ($this->params['orgAction'] == 'movefromlist') {
					$koala->callJSFunction( 'Koala.windows["'.$parentwindow.'"].tabs.select(Koala.windows["'.$parentwindow.'"].tabs.selected);' );
				} else {
					$koala->callJSFunction( 'if (typeof(Koala.yg_dndOnSuccess) == "function") Koala.yg_dndOnSuccess' );
					//$koala->callJSFunction( 'Koala.yg_expandTreeNodes', $parentwindow, 'cblocks', $parent_ids );
					$koala->callJSFunction( 'Koala.yg_selectTreeNode', $parentwindow, 'cblock', $source );
				}

				$jsQueue->add ($source, HISTORYTYPE_CO, 'CBLOCK_MOVE', sGuiUS(), $target);
			} else {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			}
			break;

		case 'deleteCBlock':

			$cblockIDs = $this->params['cblock'];
			$siteID = $this->params['site'];
			$multi = $this->params['multi'];

			if (!$multi) {
				$cblockIDs = array($cblockIDs);
			}

			foreach($cblockIDs as $cblockID) {
				$cb = sCblockMgr()->getCblock($cblockID);
				$successfullyDeleted = $cb->delete();
				if (in_array($cblockID, $successfullyDeleted) === true) {
					foreach($successfullyDeleted as $successfullyDeletedItem) {
						$tmpCblock = sCblockMgr()->getCblock($successfullyDeletedItem);
						if ($tmpCblock) {
							$tmpCblock->history->add (HISTORYTYPE_CO, NULL, NULL, 'TXT_CBLOCK_H_TRASHED');
							$tmpCblockInfo = $tmpCblock->get();
							$jsQueue->add ($successfullyDeletedItem, HISTORYTYPE_CO, 'OBJECT_DELETE', sGuiUS(), 'cblock', NULL, NULL, $successfullyDeletedItem.'-cblock', 'name');
							$jsQueue->add ($successfullyDeletedItem, HISTORYTYPE_CO, 'OBJECT_DELETE', sGuiUS(), 'cblock', NULL, NULL, $successfullyDeletedItem.'-cblock', 'listitem');

							// Remove immediately if we have a folder
							if ($tmpCblockInfo['FOLDER'] == 1) {
								sCblockMgr()->remove($successfullyDeletedItem);
							}
						}
					}
					$cb->history->add (HISTORYTYPE_CO, NULL, NULL, 'TXT_CBLOCK_H_TRASHED');
					$cblockInfo = $cb->get();
					$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'OBJECT_DELETE', sGuiUS(), 'cblock', NULL, NULL, $cblockID.'-cblock', 'name');
					$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'OBJECT_DELETE', sGuiUS(), 'cblock', NULL, NULL, $cblockID.'-cblock', 'listitem');

					// Remove immediately if we have a folder
					if ($cblockInfo['FOLDER'] == 1) {
						sCblockMgr()->remove($cblockID);
					}
				} else {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				}
			}
			break;

		case 'addCBlockChildFolder':

			$cblockID = $this->params['cblock'];

			// Check if root node is selected
			if ($cblockID==='root') {
				// Get real Page-ID of Root-Node
				$cblocks = sCblockMgr()->getList();
				$cblockID = $cblocks[0]['ID'];
			}

			// Add new child node
			$new_id = sCblockMgr()->add( $cblockID, 1, ($itext['TXT_NEW_FOLDER']!='')?($itext['TXT_NEW_FOLDER']):('$TXT_NEW_FOLDER') );

			if ( $new_id != false ) {
				$jsQueue->add ($new_id, HISTORYTYPE_CO, 'CBLOCK_ADD', sGuiUS(), NULL);
			} else {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			}
			break;

		case 'addCBlock':

			$cblockID = (int)$this->params['cblock'];
			$mode = $this->params['mode'];
			$coListExtensionId = (int)$this->params['coListExtensionId'];
			$extensionMgr = new ExtensionMgr();

			// Check if root node is selected
			if ($cblockID==='root') {
				// Get real CBlock-ID of Root-Node
				$cblocks = sCblockMgr()->getList();
				$cblockID = $cblocks[0]['ID'];
			}

			// Check if copying a template cblock is requested (or creating a new one)
			$copyCBlock = false;
			if ($mode == 'list') {
				if ($coListExtensionId == 0) {
					$listviewExtensions = $extensionMgr->getList( EXTENSION_CBLOCKLISTVIEW, true );
					// Find default extension
					foreach($listviewExtensions as $listviewExtension) {
						if ($listviewExtension['CODE'] == 'defaultCblockListView') {
							$coListExtensionId = $listviewExtension['ID'];
						}
					}
				}
				$extensionInfo = $extensionMgr->get($coListExtensionId);
				if ($extensionInfo['CODE']) {
					$extension = $extensionMgr->getExtension($extensionInfo['CODE']);
					if ($extension && $extensionInfo['INSTALLED']) {
						$defaultCo = $extension->extensionProperties->getValueInternal('DEFAULT_CO');
						$defaultcb = sCblockMgr()->getCblock($defaultCo);
						if ($defaultCo) {
							$copyCBlock = true;
						}
					}
				}
			}
			if ($copyCBlock) {
				// Copy template cblock
				$new_id = sCblockMgr()->add( $cblockID );
				$newcb = sCblockMgr()->getCblock($new_id);
				if ($new_id > 0) {
					$defaultCoInfo = $defaultcb->get();
					$newcb->copyFrom($defaultcb);
					$newcb->properties->setValue('NAME', $defaultCoInfo['NAME']);

					// Inherit permissions of the parent of the newly created copy
					$newcb->permissions->clear();
					$newcb->permissions->copyTo($cblockID, $new_id);

					// Copy PName (and generate new, if needed)
					$sourcePName = $defaultCoInfo['PNAME'];
					if (!$sourcePName) {
						$sourcePName = $defaultCoInfo['NAME'];
					}
					if (sCblockMgr()->getCblockIdByPName($sourcePName)) {
						$sourcePName = $newcb->calcPName();
					}
					$newcb->setPName($sourcePName);
					$jsQueue->add ($new_id, HISTORYTYPE_CO, 'CBLOCK_ADD', sGuiUS(), $mode);
				} else {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				}
			} else {
				// Add new child node
				$parentNode = sCblockMgr()->getCblock($cblockID);
				$parentNodeInfo = $parentNode->get();
				if ($parentNodeInfo['FOLDER']) {
					$new_id = sCblockMgr()->add($cblockID, 0, ($itext['TXT_NEW_OBJECT']!='')?($itext['TXT_NEW_OBJECT']):('$TXT_NEW_OBJECT'));
					$newcb = sCblockMgr()->getCblock($new_id);
					$newcb->properties->setValue('NAME', ($itext['TXT_NEW_OBJECT']!='')?($itext['TXT_NEW_OBJECT']):('$TXT_NEW_OBJECT'));
					if ( $new_id != false ) {
						$jsQueue->add ($new_id, HISTORYTYPE_CO, 'CBLOCK_ADD', sGuiUS(), $mode);
					} else {
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					}
				}
			}
			break;

		case 'saveCBlockPublishingSettings':
			$autopublish_data = json_decode( $this->params['autopublishData'], true );

			$changed_field = $this->params['changedField'];
			$cblockID = $this->params['cblock'];
			$version = $this->params['version'];
			$wid = $this->params['wid'];

			if ($version=='latest') {
				$version = ALWAYS_LATEST_APPROVED_VERSION;
			}
			$cb = sCblockMgr()->getCblock($cblockID);
			$old_autopublish_data = sCblockMgr()->scheduler->getSchedule($cblockID, 'SCH_AUTOPUBLISH');

			if ($changed_field == 'PUBLISH') {
				$cb->publishVersion( $version );
			}

			if ( ($changed_field == 'VERSION') ||
				 ($changed_field == 'DATE') ||
				 ($changed_field == 'TIME') ) {
				// Process autopublish data
				foreach ($autopublish_data as $ap_idx => $autopublish_data_item) {

					$id	 = $autopublish_data_item['id'];
					$time   = explode(':',$autopublish_data_item['time']);
					$hour   = (int)$time[0];
					$minute = (int)$time[1];
					$date   = explode('.',$autopublish_data_item['date']);
					$day	= (int)$date[0];
					$month  = (int)$date[1];
					$year   = (int)$date[2];
					$version = substr($autopublish_data_item['version'],8);

					if ($version=='latest') {
						$version = ALWAYS_LATEST_APPROVED_VERSION;
					}

					$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
					$timestamp = TSfromLocalTS($timestamp);
					$parameters = array('VERSION'=>$version);

					$haschanged = false;
					foreach($old_autopublish_data as $old_ap_idx => $old_autopublish_data_item) {
						if ($old_autopublish_data_item['ID']==$autopublish_data_item['id']) {
							if ( ($timestamp!=$old_autopublish_data_item['TIMESTAMP']) ||
							 	 ($version!=$old_autopublish_data_item['PARAMETERS']['VERSION']) ) {
								$haschanged = true;
							}
						}
					}

					if ( substr($id, 0, 5) == 'dummy' ) {
						$schedule_id = sCblockMgr()->scheduler->schedule($cblockID, 'SCH_AUTOPUBLISH', $timestamp, $parameters);

						// Add to history
						if ($version != ALWAYS_LATEST_APPROVED_VERSION) {
							$cb->history->add (HISTORYTYPE_CO, TStoLocalTS($timestamp), $version, 'TXT_CBLOCK_H_AUTOPUBLISH_ADDED', $schedule_id );
						} else {
							$lastfinalversion = $cb->getLatestApprovedVersion();
							$cb->history->add (HISTORYTYPE_CO, TStoLocalTS($timestamp), $lastfinalversion, 'TXT_CBLOCK_H_AUTOPUBLISH_ADDED', $schedule_id );
						}
					} elseif ($haschanged) {
						if ( sCblockMgr()->scheduler->updateAction($id, 'SCH_AUTOPUBLISH', $timestamp, $parameters) === false ) {
							$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
						} else {
							// Add to history
							if ($version != ALWAYS_LATEST_APPROVED_VERSION) {
								$cb->history->add (HISTORYTYPE_CO, TStoLocalTS($timestamp), $version, 'TXT_CBLOCK_H_AUTOPUBLISH_CHANGED', $id );
							} else {
								$lastfinalversion = $cb->getLatestApprovedVersion();
								$cb->history->add (HISTORYTYPE_CO, TStoLocalTS($timestamp), $lastfinalversion, 'TXT_CBLOCK_H_AUTOPUBLISH_CHANGED', $id );
							}
						}

					}

				}

			}
			$koala->queueScript( "Koala.yg_resetPublishSettingsEditState('".$wid."');" );
			break;

		case 'removeCBlockAutopublishItem':

			$itemID = (int)$this->params['itemID'];
			$cblockID = $this->params['page'];
			$cb = sCblockMgr()->getCblock($cblockID);

			$item_data = sCblockMgr()->scheduler->get( $itemID );
			sCblockMgr()->scheduler->removeJob( $itemID );

			// Add to history
			if ($item_data['PARAMETERS']['VERSION'] != ALWAYS_LATEST_APPROVED_VERSION) {
				$cb->history->add (HISTORYTYPE_CO, $item_data['TIMESTAMP'], $item_data['PARAMETERS']['VERSION'], 'TXT_CBLOCK_H_AUTOPUBLISH_DELETED', $itemID );
			} else {
				$lastfinalversion = $cb->getLatestApprovedVersion();
				$cb->history->add (HISTORYTYPE_CO, $item_data['TIMESTAMP'], $lastfinalversion, 'TXT_CBLOCK_H_AUTOPUBLISH_DELETED', $itemID );
			}
			break;

		case 'approveCBlock':

			$cblockID = (int)$this->params['cblock'];
			$wid = $this->params['winID'];

			$cb = sCblockMgr()->getCblock($cblockID);
			$cblockInfo = $cb->get();
			$cblockversion = $cb->getLatestVersion();

			if ($cblockInfo['PNAME'] == NULL) {
				$PName = $cb->calcPName();
				$cb->setPName($PName);
			}

			$cb->approve();

			$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'CLEAR_REFRESH', sGuiUS(), 'cblock');

			$koala->queueScript( 'if (Koala.windows[\'wid_'.$wid.'\'].tab==\'CONTENT\') Koala.windows[\'wid_'.$wid.'\'].tabs.select(0,Koala.windows[\'wid_'.$wid.'\'].tabs.params);' );

			$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'UNHIGHLIGHT_CBLOCK', sGuiUS(), 'name');

			// Trigger refresh of list entries
			$jsQueue->add ($cblockID, HISTORYTYPE_CO, 'OBJECT_CHANGE', sGuiUS(), 'cblock', NULL, NULL, $cblockID.'-cblock', 'listitem');
			break;

		case 'contentblockSelectNode':

			$node = $this->params['node'];
			$wid = $this->params['wid'];

			if ($node == 'trash') break;

			$root_node = sCblockMgr()->getTree(NULL, 0);

			// 1 = rsub
			// 2 = rread
			// 3 = rdelete
			// 4 = parent -> rsub & rwrite
			// 5 = parent -> rsub & rwrite
			// 6 = rdelete
			// 7 = rsubfolder
			$buttons = array();

			// Get Info
			$cb = sCblockMgr()->getCblock($node);
			$objectInfo = $cb->get();
			$folder = $objectInfo['FOLDER'];

			// Get Parents
			$parentid = sCblockMgr()->getParents( $node );
			$parentid = $parentid[0][0]['ID'];

			// Check rights
			$rread = $cb->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RREAD" );
			$rwrite = $cb->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RWRITE" );
			$rsub = $cb->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RSUB" );
			$rdelete = $cb->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RDELETE" );

			// Check rights of parents
			$prsub = $cb->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $parentid, "RSUB" );
			$prwrite = $cb->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $parentid, "RWRITE" );

			// Check permissions for button "add"
			if ($rsub) {
				$buttons[6] = true;
				$buttons[0] = true;
			} else {
				$buttons[6] = false;
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

			// Check if object has rsub rights
			if ($folder) {
				if ($rsub) {
					$buttons[6] = true;
					$buttons[0] = true;
				} else {
					$buttons[6] = false;
					$buttons[0] = false;
				}
			} else {
					$buttons[6] = false;
					$buttons[0] = false;
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
			} else {
				$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_add', 'btn-'.$wid, 'tree_btn' );
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

			if ($buttons[6]===true) {
				$koala->callJSFunction( 'Koala.yg_enable', 'tree_btn_addfolder', 'btn-'.$wid, 'tree_btn' );
			} else {
				$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_addfolder', 'btn-'.$wid, 'tree_btn' );
			}

			$koala->callJSFunction( 'Koala.yg_selectContentblock', $node, $wid );
			break;

		case 'removePageContentblock':

			$contentblock_lnkid = $this->params['contentblockLnkId'];
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
				$unknownValue = "mailing";
			} else {
				// For pages
				$myMgr = new PageMgr($siteID);
				$myObject = $myMgr->getPage($pageID);
				$myObjectInfo = $myObject->get();
				$historyType = HISTORYTYPE_PAGE;
				$historyStr = "PAGE";
				$myQueue = new JSQueue(NULL, $siteID);
				$unknownValue = "page";
			}

			$colnkinfo = $myObject->getCblockLinkById( $contentblock_lnkid );
			$cb = sCblockMgr()->getCblock($colnkinfo[0]['CBLOCKID']);
			if ($cb) {
				$cblockInfo = $cb->get();
			} else {
				$cblockInfo = false;
			}

			if ($myObject->removeCblockByLinkId($contentblock_lnkid) === false) {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				$koala->queueScript( "window.removeCblockntentblock=undefined;" );
			} else {
				$contentarea = $colnkinfo[0]['TEMPLATECONTENTAREA'];

				// Add to history
				if ($cblockInfo['EMBEDDED'] == 0) {
					$myObject->history->add ($historyType, $contentarea, $cblockInfo['NAME'], 'TXT_'.$historyStr.'_H_COREMOVE', $cblockInfo['OBJECTID'] );
				} else {
					// Get Name of entrymask
					$realName = $cb->properties->getValueInternal('NAME');
					$myObject->history->add ($historyType, $contentarea, $realName, 'TXT_'.$historyStr.'_H_EMREMOVE', $cblockInfo['OBJECTID'] );
				}

				$myQueue->add ($pageID, $historyType, 'HIGHLIGHT_'.$historyStr, sGuiUS(), 'name');
				$myQueue->add ($cblockInfo['OBJECTID'], HISTORYTYPE_CO, 'OBJECT_DELETE', sGuiUS(), 'cblock', NULL, NULL, $contentblock_lnkid.'-cblock', 'block', $unknownValue, $pageID.'-'.$siteID);
			}
			break;

		case 'moveUpPageContentblock':

			$cblockID = $this->params['cblock'];
			$pageID = $this->params['page'];
			$siteID = $this->params['site'];
			$parentwindow = $this->params['win_no'];
			$templateMgr = new Templates();

			if ($siteID == 'cblock') {
				// For contentblocks
				$cb = sCblockMgr()->getCblock($pageID);
				$cblockInfo = $cb->get();
				$colist = $cb->getEntrymasks();

				$cb = sCblockMgr()->getCblock($pageID);

				$cblockInfo = $cb->get( );
				$colist = $cb->getEntrymasks( );

				for ($i = 0; $i < count($colist); $i++) {
					if ($cblockID == $colist[$i]['LINKID']) {
						if ($colist[$i] > 0) {
							$oldid = $newcolist[$i-1];
							$newcolist[$i-1] = $cblockID;
							$newcolist[$i] = $oldid;
						}
					} else {
						$newcolist[$i] = $colist[$i]['LINKID'];
					}
				}

				if (count($colist) > 1) {
					$cb->setEntrymaskOrder( $newcolist );
					$koala->queueScript( "Koala.yg_moveCBlockUp('wid_".$parentwindow."_cblock_0_".$cblockID."');" );
					$jsQueue->add ($pageID, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');

					// Add to history
					$cb->history->add (HISTORYTYPE_CO, 0, $cblockInfo['NAME'], 'TXT_CBLOCK_H_EMORDER', $cblockInfo['OBJECTID'] );
				}

			} else {

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

				$contentareas = $templateMgr->getContentareas( $myObjectInfo['TEMPLATEID'] );
				$colinkinfo = $myObject->getCblockLinkById( $cblockID );
				$contentarea = $colinkinfo[0]['TEMPLATECONTENTAREA'];
				$colist = $myObject->getCblockList($contentarea);
				$parentwindow = $this->params['win_no'];

				foreach ($contentareas as $contentarea_item)  {
					if ($contentarea_item['CODE']==$contentarea) {
						$contentarea_id = $contentarea_item['ID'];
					}
				}

				for ($i = 0; $i < count($colist); $i++) {
					if ($cblockID == $colist[$i]['LINKID']) {
						if ( ($colist[$i]['LINKID'] > 0) && ($i > 0) ) {
							$oldid = $newcolist[$i-1];
							$newcolist[$i-1] = $colist[$i]['OBJECTID'];
							$newcolist[$i] = $oldid;
						} else {
							$newcolist[$i] = $colist[$i]['OBJECTID'];
						}
					} else {
						$newcolist[$i] = $colist[$i]['OBJECTID'];
					}
				}

				$myObject->setCblockOrder($newcolist, $contentarea);
				$koala->queueScript( "Koala.yg_moveCBlockUp('wid_".$parentwindow."_cblock_".$contentarea_id."_".$cblockID."');" );

				$jsQueue->add ($pageID, $historyType, 'HIGHLIGHT_'.$historyStr, sGuiUS(), 'name');
				// Add to history
				$myObject->history->add ($historyType, $contentarea, NULL, 'TXT_'.$historyStr.'_H_COORDER', NULL );
			}
			break;

		case 'moveDownPageContentblock':

			$cblockID = $this->params['cblock'];
			$pageID = $this->params['page'];
			$siteID = $this->params['site'];
			$parentwindow = $this->params['win_no'];
			$templateMgr = new Templates();

			if ($siteID == 'cblock') {

				$cb = sCblockMgr()->getCblock($pageID);
				$cblockInfo = $cb->get( );
				$colist = $cb->getEntrymasks(  );

				$finished = false;
				for ($i = 0; $i < count($colist); $i++) {
					if (($cblockID == $colist[$i]['LINKID']) && ($finished == false)) {
						if ($colist[$i+1] > 0) {
							$oldid = $colist[$i+1]['LINKID'];
							$colist[$i+1]['LINKID'] = $cblockID;
							$newcolist[$i] = $oldid;
							$finished = true;
						}
					} else {
						$newcolist[$i] = $colist[$i]['LINKID'];
					}
				}

				if (count($colist) > 1) {
					$cb->setEntrymaskOrder( $newcolist );
					$koala->queueScript( "Koala.yg_moveCBlockDown('wid_".$parentwindow."_cblock_0_".$cblockID."');" );
					$jsQueue->add ($pageID, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');

					// Add to history
					$cb->history->add (HISTORYTYPE_CO, 0, $cblockInfo['NAME'], 'TXT_CBLOCK_H_EMORDER', $cblockInfo['OBJECTID'] );
				}

			} elseif ($siteID == 'mailing') {
				// For mailings
				$mailingMgr = new MailingMgr();
				$mailing = $mailingMgr->getMailing($pageID);
				$mailingInfo = $mailing->get();
				$contentareas = $templateMgr->getContentareas( $mailingInfo['TEMPLATEID'] );
				$colinkinfo = $mailing->getCblockLinkById( $cblockID );
				$contentarea = $colinkinfo[0]['TEMPLATECONTENTAREA'];
				$colist = $mailing->getCblockList($contentarea);

				foreach ($contentareas as $contentarea_item)  {
					if ($contentarea_item['CODE']==$contentarea) {
						$contentarea_id = $contentarea_item['ID'];
					}
				}
				$finished = false;
				for ($i = 0; $i < count($colist); $i++) {
					if (($cblockID == $colist[$i]['LINKID']) && ($finished == false)) {
						if ($colist[$i+1]['LINKID'] > 0) {
							$oldid = $colist[$i+1]['OBJECTID'];
							$colist[$i+1] = $colist[$i];
							$newcolist[$i] = $oldid;
							$finished = true;
						}
					} else {
						$newcolist[$i] = $colist[$i]['OBJECTID'];
					}
				}

				$mailing->setCblockOrder($newcolist, $contentarea);
				$koala->queueScript( "Koala.yg_moveCBlockDown('wid_".$parentwindow."_cblock_".$contentarea_id."_".$cblockID."');" );
				$jsQueue = new JSQueue(NULL);
				//$mailing->setStatus('UNSENT');
				$jsQueue->add ($pageID, HISTORYTYPE_MAILING, 'HIGHLIGHT_MAILING', sGuiUS(), 'name');

				// Add to history
				$mailing->history->add (HISTORYTYPE_MAILING, $contentarea, NULL, 'TXT_MAILING_H_COORDER', NULL );
			}  else {
				// For pages
				$PageMgr = new PageMgr($siteID);
				$page = $PageMgr->getPage($pageID);
				$pageInfo = $page->get();
				$contentareas = $templateMgr->getContentareas( $pageInfo['TEMPLATEID'] );
				$colinkinfo = $page->getCblockLinkById( $cblockID );
				$contentarea = $colinkinfo[0]['TEMPLATECONTENTAREA'];
				$colist = $page->getCblockList($contentarea);

				foreach ($contentareas as $contentarea_item)  {
					if ($contentarea_item['CODE']==$contentarea) {
						$contentarea_id = $contentarea_item['ID'];
					}
				}
				$finished = false;
				for ($i = 0; $i < count($colist); $i++) {
					if (($cblockID == $colist[$i]['LINKID']) && ($finished == false)) {
						if ($colist[$i+1]['LINKID'] > 0) {
							$oldid = $colist[$i+1]['OBJECTID'];
							$colist[$i+1] = $colist[$i];
							$newcolist[$i] = $oldid;
							$finished = true;
						}
					} else {
						$newcolist[$i] = $colist[$i]['OBJECTID'];
					}
				}

				$page->setCblockOrder($newcolist, $contentarea);
				$koala->queueScript( "Koala.yg_moveCBlockDown('wid_".$parentwindow."_cblock_".$contentarea_id."_".$cblockID."');" );
				$jsQueue = new JSQueue(NULL, $siteID);
				$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');

				// Add to history
				$page->history->add (HISTORYTYPE_PAGE, $contentarea, NULL, 'TXT_PAGE_H_COORDER', NULL );
			}
			break;

		case 'orderMailingContentblock':
		case 'orderPageContentblock':
			$newcolists = json_decode( $this->params['newcolists'], true );

			$mode = $this->params['mode'];
			if ($action == "orderMailingContentblock") {
				$pageID = $this->params['page'];
				$PageMgr = new MailingMgr();
				$page = $PageMgr->getMailing($pageID);
			} else {
				$pageID = $this->params['page'];
				$siteID = $this->params['site'];
				$PageMgr = new PageMgr($siteID);
				$page = $PageMgr->getPage($pageID);
			}

			$parentwindow = $this->params['winID'];
			$templateMgr = new Templates();
			$entrymaskMgr = new Entrymasks();

			// Get current contentarea and contentblock data for the current page

			$pageInfo = $page->get();
			$contentareas = $templateMgr->getContentareas( $pageInfo['TEMPLATEID'] );
			$oldcolists = array();
			foreach ($contentareas as $contentarea) {
				$colistids = array();
				$colist = $page->getCblockList( $contentarea['CODE'] );
				foreach($colist as $colistitem) {
					$colistids[] = array( (int)$colistitem['OBJECTID'], (int)$colistitem['LINKID'] );
				}
				$oldcolists[(int)$contentarea['ID']] = $colistids;
			}

			// Check if a contentblock changed the contentarea
			$onlysorted = true;
			foreach($oldcolists as $ca_id => $oldcolists_items) {

				if ( (count($oldcolists[$ca_id])) != (count($newcolists[$ca_id])) ) {

					// Check in which area the new object was added (and which was added)
					if ( (count($oldcolists[$ca_id])) < (count($newcolists[$ca_id])) ) {
						$target_contentarea = $ca_id;

						// Check which contentblock was moved
						for($i=0;$i<count($newcolists[$ca_id]);$i++) {

							$found_co = false;
							for($j=0;$j<count($oldcolists[$ca_id]);$j++) {
								if ($newcolists[$ca_id][$i][1] == $oldcolists[$ca_id][$j][1])
									$found_co = true;
							}
							if (!$found_co) {
								$moved_co = $newcolists[$ca_id][$i][0];
								$movedcb = sCblockMgr()->getCblock($moved_co);
								$moved_colnk = $newcolists[$ca_id][$i][1];
							}
						}

					} elseif ( (count($oldcolists[$ca_id])) > (count($newcolists[$ca_id])) ) {
						$source_contentarea = $ca_id;

						// Check which contentblock was moved
						for($i=0;$i<count($oldcolists[$ca_id]);$i++) {

							$found_co = false;
							for($j=0;$j<count($newcolists[$ca_id]);$j++) {
								if ($oldcolists[$ca_id][$i][1] == $newcolists[$ca_id][$j][1])
									$found_co = true;
							}
							if (!$found_co) {
								$moved_co = $oldcolists[$ca_id][$i][0];
								$movedcb = sCblockMgr()->getCblock($moved_co);
								$moved_colnk = $oldcolists[$ca_id][$i][1];
							}
						}

					}
					$onlysorted = false;
				}
				// Check which contentarea was changed
				for($i=0;$i<count($oldcolists_items);$i++) {
					if ($oldcolists[$ca_id][$i] != $newcolists[$ca_id][$i]) {
						if (!$changed_ca) {
							$changed_ca = $ca_id;
						}
					}
				}
			}

			// Check if the elements were only sorted within their own contentarea
			if ($onlysorted) {
				// If the contentblock was only moved within its old contentarea

				// Get name of contentarea
				foreach($contentareas as $contentarea)
					if ($contentarea['ID']==$changed_ca)
						$changed_ca_code = $contentarea['CODE'];

				$finalcolist = array();
				foreach ($newcolists[$changed_ca] as $item) {
					$finalcolist[] = $item[1];
				}

				$page->setCblockLinkOrder( $finalcolist );

				// Add to history
				$page->history->add (HISTORYTYPE_PAGE, $changed_ca_code, NULL, 'TXT_PAGE_H_COORDER', NULL );

				// Hilite page
				$jsQueue = new JSQueue(NULL, $siteID);
				$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');

			} elseif ( ($moved_co != 0) || ($moved_co == 'emblock') ) {
				// Contentblock has changed contentarea

				// Get name of contentareas
				foreach($contentareas as $contentarea) {
					if ($contentarea['ID']==$source_contentarea)
						$source_contentarea_code = $contentarea['CODE'];
					if ($contentarea['ID']==$target_contentarea)
						$target_contentarea_code = $contentarea['CODE'];
				}

				unset($changed_ca);

				// Check if a new object was added (so we have NO source_contentarea_code)
				if ($source_contentarea_code=='') {
					// Yes, object is only added (we have to change the id of the newly created contentblock in the frontend)
					$force_new_frontend_id = true;
				} else {
					// Remove from old contentarea
					$page->removeCblockByLinkId( $moved_colnk );

					$cblockInfo = $movedcb->get();
					if ($cblockInfo['EMBEDDED'] == 0) {
						$page->history->add (HISTORYTYPE_PAGE, $source_contentarea_code, $cblockInfo['NAME'], 'TXT_PAGE_H_COREMOVE', $cblockInfo['OBJECTID'] );
					} else {
						$realName = $movedcb->properties->getValueInternal('NAME');
						$page->history->add (HISTORYTYPE_PAGE, $source_contentarea_code, $realName, 'TXT_PAGE_H_EMREMOVE', $cblockInfo['OBJECTID'] );
					}

				}

				// Add to new contentarea & save new order
				if ($moved_co != 'emblock') {
					$new_lnkid = $page->addCblockLink($moved_co, $target_contentarea_code);

					// Add to history
					if ($moved_object == 'emblock') {
						// For entrymasks
						$realName = $movedcb->properties->getValueInternal('NAME');
						$page->history->add (HISTORYTYPE_PAGE, $target_contentarea_code, $realName, 'TXT_PAGE_H_EMADD', $moved_co );
					} else {
						// For contentblocks
						$cblockInfo = $movedcb->get();
						if ($cblockInfo['EMBEDDED']==1) {
							$realName = $movedcb->properties->getValueInternal('NAME');
							$page->history->add (HISTORYTYPE_PAGE, $target_contentarea_code, $realName, 'TXT_PAGE_H_EMADD', $moved_co );
						} elseif( $mode!='copy') {
							$page->history->add (HISTORYTYPE_PAGE, $target_contentarea_code, $cblockInfo['NAME'], 'TXT_PAGE_H_COADD', $moved_co );
						}
					}

				}

				$finalcolist = array();
				foreach ($newcolists[$target_contentarea] as $item) {
					if ( ($item[1]=='') ||
						 ($item[1]==$moved_colnk) ) {

							// Check if copy contentblock is requested
							if ($moved_co == 'emblock') {

								// Add new contentblock to folder
								$contentblockID = $page->addCblockEmbedded($target_contentarea_code);
								$newcb = sCblockMgr()->getCblock($contentblockID);

								if ($mode=='copy') {
									$orig_moved_colnk = $moved_colnk;
									$moved_colnk = (int)str_replace( '_cblock__', '', $moved_colnk);
								}

								// Get content of source formfields
								$controlFormfields = $newcb->getFormfieldsInternal($new_control);

								$newcb->properties->setValue ("NAME", $controlFormfields[0]['ENTRYMASKNAME']);

								// Get the LinkId of the newly created contentblock
								$new_colnkid = $page->getEmbeddedCblockLinkId($contentblockID);

								$control_found = false;
								foreach ($controlFormfields as $contentblock_info_item) {

									if ( ($contentblock_info_item['LNK'] == $moved_colnk) &&
									 	 ($control_found == false) ) {

										// Add controls to contentblock
										$new_control = $newcb->addEntrymask($contentblock_info_item['ENTRYMASKID']);
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
										$control_found = true;
									}

								}

								// Add the newly created contentblock to the output array
								$finalcolist[] = $new_colnkid;

								// Add to history
								$page->history->add (HISTORYTYPE_PAGE, $target_contentarea_code, $controlFormfields[0]['ENTRYMASKNAME'], 'TXT_PAGE_H_EMCOPY', $contentblockID );

								// Delete the newly created contentblock link in frontend
								$koala->queueScript( "if ($('clone%%emblock__".$moved_colnk."')) $('clone%%emblock__".$moved_colnk."').remove();" );

							} elseif ($mode=='copy') {

								// Delete the newly created contentblock link
								$page->removeCblockByLinkId( $new_lnkid );

								// Gathering data for frontend deletion of the newly created contentblock link
								$src_coinfo = $page->getCblockLinkById( $moved_colnk );
								$srcPage = $PageMgr->getPage($src_coinfo[0]['PAGEID']);
								$src_pageInfo = $srcPage->get();
								$src_contentareas = $templateMgr->getContentareas( $src_pageInfo['TEMPLATEID'] );
								foreach ($src_contentareas as $src_contentarea_item) {
									if ($src_contentarea_item['CODE'] == $src_coinfo[0]['TEMPLATECONTENTAREA']) {
										// Delete the newly created contentblock link in frontend
										$koala->queueScript( "$('clone%%cblock_".$src_contentarea_item['ID']."_".$moved_colnk."_dialog').remove();" );
									}
								}

								// Check which entrymasks are contained
								if ($moved_co == 'emblock') {
									$colinkid = explode('_', $moved_colnk);
									$colinkid = $colinkid[2];
									$colnkinfo = $page->getCblockLinkById( $colinkid );
									$moved_co = $colnkinfo[0]['CBLOCKID'];
								}

								$movedcb = sCblockMgr()->getCblock($moved_co, $src_co['VERSION']);
								$src_co = $movedcb->get();
								$src_entrymasks = $movedcb->getEntrymasks();

								// Create blind contentblocks with these entrymasks
								foreach ($src_entrymasks as $src_entrymask_item) {

									// Add new contentblock to folder
									$contentblockID = $page->addCblockEmbedded($target_contentarea_code);
									$newcb = sCblockMgr()->getCblock($contentblockID);
									$newcb->properties->setValue ("NAME", $src_entrymask_item['ENTRYMASKNAME']);

									// Add requested control to contentblock
									$new_control = $newcb->addEntrymask($src_entrymask_item['ENTRYMASKID']);

									// Get the LinkId of the newly created contentblock
									$new_colnkid = $page->getEmbeddedCblockLinkId($contentblockID);

									// Loop through all formfields
									$controlFormfields = $movedcb->getFormfieldsInternal($src_entrymask_item['LINKID']);
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

									// Add the newly created contentblock to the output array
									$finalcolist[] = $new_colnkid;

									// Add to the page history
									$page->history->add (HISTORYTYPE_PAGE, $target_contentarea_code, $src_entrymask_item['ENTRYMASKNAME'], 'TXT_PAGE_H_EMCOPY', $contentblockID );

								}
								$force_new_frontend_id = true;

							} else {
								$finalcolist[] = $new_lnkid;
							}

					} else {
						$finalcolist[] = $item[1];
					}
				}

				$page->setCblockLinkOrder( $finalcolist );

				// Check if a new (set) of frontendids is required
				if ($force_new_frontend_id) {

					$pageInfo = $page->get();

					$contentareas = $templateMgr->getContentareas( $pageInfo['TEMPLATEID'] );

					for ($i = 0; $i < count($contentareas); $i++) {
						if ($contentareas[$i]['CODE']==$target_contentarea_code) {

							$colist = $page->getCblockList($contentareas[$i]['CODE']);
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
												$file = sFileMgr()->getFile($controlFormfields[$w]['VALUE01']);
												$fileInfo = $file->get();
												$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
											}
											if ($controlFormfields[$w]['FORMFIELD'] == 7) {
												$acb = sCblockMgr()->getCblock($controlFormfields[$w]['VALUE01']);
												$info = $acb->get();
												$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
											}
											if ($controlFormfields[$w]['FORMFIELD'] == 8) {
												$info = $page->tags->get($controlFormfields[$w]['VALUE01']);
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

					$dta_cnt = 0;
					$js_array = '[ ';
						foreach ($contentareas as $contentarea_item) {
						foreach ($contentarea_item['LIST'] as $contentarea_list_item) {
							$js_array .= "[ '".$contentarea_list_item['OBJECTID']."', '".$contentarea_item['CODE']."', '".$contentarea_item['ID']."', '".$contentarea_list_item['LINKID']."' ], ";
							$dta_cnt++;
						}
					}
					$js_array = substr($js_array, 0, strlen($js_array)-2);
					$js_array .= ' ]';

					if ($js_array == ' ]') {
						$js_array = '[]';
					}

					$koala->queueScript( "if ( document.getElementById('clone%%cblock__') ) { document.getElementById('clone%%cblock__').parentNode.removeChild(document.getElementById('clone%%cblock__')); };" );
					$koala->queueScript( "Koala.windows['wid_".$parentwindow."'].getContentareaDataFuncs( ".$js_array.", true, (function() { if ($('wid_".$parentwindow."_cblock___cbcontentinner_dummy').getStyle('display')=='block') { $('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."_cbheader').onclick(); }; }) );" );

					$jsQueue = new JSQueue(NULL, $siteID);
					$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');

				} else {
					$koala->queueScript( "Koala.updateDOM($('wid_".$parentwindow."_cblock_".$source_contentarea."_".$moved_colnk."'));" );
					$koala->queueScript( "if ($('wid_".$parentwindow."_cblock_".$source_contentarea."_".$moved_colnk."')) { var _x = $('wid_".$parentwindow."_cblock_".$source_contentarea."_".$moved_colnk."'); _x.setAttribute('id','wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."'); _x.id = 'wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."'; delete _x; }" );
					$koala->queueScript( "if ($('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."')) {
							$('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."').innerHTML = $('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."').innerHTML.replace(/cblock_".$source_contentarea."_".$moved_colnk."/g, 'cblock_".$target_contentarea."_".$new_lnkid."');
							$('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."').innerHTML = $('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."').innerHTML.replace(/".$moved_colnk."-cblock/g, '".$new_lnkid."-cblock');
					};");
					$koala->queueScript( "Koala.yg_recreateSortables( $('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."').up().id );" );
					$koala->queueScript( "Koala.yg_fixCurrentFocusObjects();" );
					$koala->queueScript( "Koala.yg_customAttributeHandler( $('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."') );" );

					// Check if contentblock/entrymask is allowed in this contentarea
					$contentareasEntryMasks = $templateMgr->resolveContentareaEntrymaskMapping( $pageInfo['TEMPLATEID'] );
					$co_controls = $movedcb->getEntrymasks();

					$childNotAllowed = false;
					for ($c = 0; $c < count($co_controls); $c++) {
						$isAllowed = false;
						foreach($contentareasEntryMasks as $contentareasEntryMask_item) {
							if ($contentareasEntryMask_item['CODE'] == $target_contentarea_code) {
								foreach($contentareasEntryMask_item['ENTRYMASKS'] as $entrymask_title_item) {
									if ($co_controls[$c]['CODE'] == $entrymask_title_item) {
										$isAllowed = true;
									}
								}
							}
						}
						if (!$isAllowed) {
							$childNotAllowed = true;
							$koala->queueScript( "if ($('wid_".$parentwindow."_entrymask_".$co_controls[$c]['LINKID']."_cblock_".$target_contentarea."_".$new_lnkid."')) $('wid_".$parentwindow."_entrymask_".$co_controls[$c]['LINKID']."_cblock_".$target_contentarea."_".$new_lnkid."').down('.emcontent').addClassName('emerror');" );
						} else {
							$koala->queueScript( "if ($('wid_".$parentwindow."_entrymask_".$co_controls[$c]['LINKID']."_cblock_".$target_contentarea."_".$new_lnkid."')) $('wid_".$parentwindow."_entrymask_".$co_controls[$c]['LINKID']."_cblock_".$target_contentarea."_".$new_lnkid."').down('.emcontent').removeClassName('emerror');" );
						}
					}
					if ($childNotAllowed) {
						$koala->queueScript( "if ($('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."').down('.cbcontent')) $('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."').down('.cbcontent').addClassName('cberror');" );
					} else {
						$koala->queueScript( "if ($('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."').down('.cbcontent')) $('wid_".$parentwindow."_cblock_".$target_contentarea."_".$new_lnkid."').down('.cbcontent').removeClassName('cberror');" );
					}
					// End check if contentblock/entrymask is allowed in this contentarea
				}

				// Hilite page
				$jsQueue = new JSQueue(NULL, $siteID);
				$jsQueue->add ($pageID, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');
			}
			break;

		case 'addPageContentblock':

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

			$contentblockID = $this->params['contentblockId'];
			$cb = sCblockMgr()->getCblock($contentblockID);
			$contentarea = $this->params['contentareaID'];
			$parentwindow = $this->params['openerRefID'];
			$refresh = $this->params['refresh'];
			$target_id = $this->params['targetId'];
			$target_pos = $this->params['targetPosition'];
			$copymode = $this->params['copymode'];
			$templateMgr = new Templates();
			$entrymaskMgr = new Entrymasks();

			$contentarea = str_replace( 'wid_'.$parentwindow.'_ca_', '', $contentarea );

			if (($target_id!='') && ($target_pos!='')) {

				// Get code for contentarea_id
				$contentarea_id = $contentarea;
				$contentarea = '';
				$contentareas = $templateMgr->getContentareas( $myObjectInfo['TEMPLATEID'] );

				foreach($contentareas as $pagecontentarea) {
					if($pagecontentarea['ID']==$contentarea_id) {
						$contentarea = $pagecontentarea['CODE'];
					}
				}

				if ($contentblockID > 0) {
				  	// Check if already there
					$add_co = true;
					for ($i = 0; $i < count($contentareas); $i++) {
						if ($contentareas[$i]['CODE']==$contentarea) {

							$colist = $myObject->getCblockList($contentareas[$i]['CODE']);
							for ($x = 0; $x < count($colist);$x++) {
								$coid = $colist[$x]['OBJECTID'];
								if ($coid == $contentblockID) {
									$add_co = false;
								}
							}
						}
					}
					if (!$add_co && !$copymode) {
						$koala->queueScript( "Koala.yg_removeLoadingPlaceholder();" );
						$koala->alert( $itext['TXT_DUPLICATE_CONTENTBLOCK'] );
						break;
					}

					$newLinkIds = array();
					if ($copymode) {
						// Get all Contentblock<->entrymask links for contentblock
						$tmpCoInfo = sCblockMgr()->getCblockLinkByLinkId($contentblockID);
						$contentblockLinkID = $contentblockID;
						$cb = sCblockMgr()->getCblock($tmpCoInfo[0]['CBLOCKID']);

						$contentBlockLinks = $cb->getEntrymasks();

						foreach($contentBlockLinks as $contentBlockLink) {
							$contentBlockLinkId = $contentBlockLink['LINKID'];

							// Add new contentblock to folder
							$newContentblock = $myObject->addCblockEmbedded($contentarea);
							$newcb = sCblockMgr()->getCblock($newContentblock);
							$sourcecb = sCblockMgr()->getCblock($contentBlockLinkId);

							$sourceContentBlockInfo = sCblockMgr()->getCblockLinkByEntrymaskLinkId( $contentBlockLinkId );
							$control_props = $entrymaskMgr->get($sourceContentBlockInfo['ENTRYMASK']);

							$newcb->properties->setValue ("NAME", $control_props['NAME']);

							$newControl = $newcb->addEntrymask($sourceContentBlockInfo['ENTRYMASK']);

							if ($newContentblock > 0) {
								// Get the LinkId of the newly created contentblock
								$newLinkId = $myObject->getEmbeddedCblockLinkId($newContentblock);
								array_push( $newLinkIds, array('LINKID' => $newLinkId, 'CBID' => $newContentblock) );

								$oldFormfieldContent = $sourcecb->getFormfieldsInternal($contentBlockLinkId);
								$newFormfieldContent = $newcb->getFormfieldsInternal($newControl);
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

								$cblockInfo = $newcb->get( );

								// Add to history
								$myObject->history->add ($historyType, $contentarea, $cblockInfo['NAME'], "TXT_".$historyStr."_H_COADD", $newContentblock );
							}

						}
					} else {
						$newLinkId = $myObject->addCblockLink($contentblockID, $contentarea);

						// Add to history
						$cblockInfo = $cb->get();
						$myObject->history->add ($historyType, $contentarea, $cblockInfo['NAME'], 'TXT_'.$historyStr.'_H_COADD', $contentblockID );

						array_push( $newLinkIds, array('LINKID' => $newLinkId, 'CBID' => $contentblockID) );
					}

					// Add contentblock to contentarea
					for ($i = 0; $i < count($contentareas); $i++) {
						if ($contentareas[$i]['CODE']==$contentarea) {

							$colist = $myObject->getCblockList($contentareas[$i]['CODE']);
							for ($x = 0; $x < count($colist);$x++) {
								if ($colist[$x]['OBJECTID'] > 0) {
									$colist[$x]['CBVERSION'] = $colist[$x]['VERSION'];
									$clcb = sCblockMgr()->getCblock($colist[$x]['OBJECTID'], $colist[$x]['VERSION']);
									$colist[$x]['ENTRYMASKS'] = $clcb->getEntrymasks();

									for ($c = 0; $c < count($colist[$x]['ENTRYMASKS']); $c++) {
										$controlFormfields = $clcb->getFormfieldsInternal($colist[$x]['ENTRYMASKS'][$c]['LINKID']);
										for ($w = 0; $w < count($controlFormfields); $w++) {
											if (($controlFormfields[$w]['FORMFIELD'] == 6)  || ($controlFormfields[$w]['FORMFIELD'] == 16)) {
												$file = sFileMgr()->getFile($controlFormfields[$w]['VALUE01']);
												if ($file) {
													$fileInfo = $file->get();
													$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
												}
											}
											if ($controlFormfields[$w]['FORMFIELD'] == 7) {
												$lcb = sCblockMgr()->getCblock($controlFormfields[$w]['VALUE01']);
												if ($lcb) {
													$info = $lcb->get();
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
					$removed = array_splice($colist, $targetpos);

					foreach ($newLinkIds as $newLinkId) {
						$temp_id = count($colist);
						$colist[$temp_id]['OBJECTID'] = $newLinkId['CBID'];
						$colist[$temp_id]['LINKID'] = $newLinkId['LINKID'];
					}

					$colist = array_merge($colist, $removed);
					foreach($newLinkIds as $newLinkId) {
						array_pop($colist);
					}

					// Create new array for saveorder...
					foreach($colist as $colist_item) {
						$finalcolist[] = $colist_item['LINKID'];
					}

					$myObject->setCblockLinkOrder( $finalcolist );

					$dta_cnt = 0;

					if ($refresh=='true') {
						$js_array = '[ ';
						foreach ($contentareas as $contentarea_item) {
							if ($contentarea_item['ID']==$contentarea_id) {
								foreach ($colist as $contentarea_list_item) {
									$dta_cnt++;
									$js_array .= "[ '".$contentarea_list_item['OBJECTID']."', '".$contentarea_item['CODE']."', '".$contentarea_item['ID']."', '".$contentarea_list_item['LINKID']."'  ], ";
								}
							}
						}

						$js_array = substr($js_array, 0, strlen($js_array)-2);

						$js_array .= ' ]';

						if ($js_array == ' ]') {
							$js_array = '[]';
						}

						$koala->queueScript( "Koala.windows['wid_".$parentwindow."'].getContentareaDataFuncs( ".$js_array.", true );" );

						$myQueue->add ($pageID, $historyType, 'HIGHLIGHT_'.$historyStr, sGuiUS(), 'name');
					}

				}

			} else {

				if ($contentblockID > 0) {

				  	// Check if already there
					// START GET CONTENTAREADATA
					$myObjectInfo = $myObject->get();

					$contentareas = $templateMgr->getContentareas( $myObjectInfo['TEMPLATEID'] );

					if (is_numeric($contentarea)) {
						foreach($contentareas as $contentarea_item) {
							if ($contentarea_item['ID']==$contentarea) {
								$contentarea = $contentarea_item['CODE'];
							}
						}
					}

					$add_co = true;
					for ($i = 0; $i < count($contentareas); $i++) {
						if ($contentareas[$i]['CODE']==$contentarea) {
							$colist = $myObject->getCblockList($contentareas[$i]['CODE']);
							for ($x = 0; $x < count($colist);$x++) {
								$coid = $colist[$x]['OBJECTID'];
								if ($coid == $contentblockID) {
									$add_co = false;
	 							}
							}
						}
					}
					if (!$add_co && !$copymode) {
						$koala->queueScript( "Koala.yg_removeLoadingPlaceholder();" );
						$koala->alert( $itext['TXT_DUPLICATE_CONTENTBLOCK'] );
						break;
					}

					$newLinkIds = array();
					if ($copymode) {
						// Get all Contentblock<->entrymask links for contentblock
						$tmpCoInfo = sCblockMgr()->getCblockLinkByLinkId($contentblockID);
						$contentblockLinkID = $contentblockID;
						$contentblockID = $tmpCoInfo[0]['CBLOCKID'];
						$cb = sCblockMgr()->getCblock($contentblockID);

						$contentBlockLinks = $cb->getEntrymasks();

						foreach($contentBlockLinks as $contentBlockLink) {
							$contentBlockLinkId = $contentBlockLink['LINKID'];

							// Add new contentblock to folder
							$newContentblock = $myObject->addCblockEmbedded($contentarea);
							$newcb = sCblockMgr()->getCblock($newContentblock);

							$sourceContentBlockInfo = sCblockMgr()->getCblockLinkByEntrymaskLinkId( $contentBlockLinkId );
							$sourcecb = sCblockMgr()->getCblock($sourceContentBlockInfo['CBLOCKID']);
							$control_props = $entrymaskMgr->get($sourceContentBlockInfo['ENTRYMASK']);

							$newcb->properties->setValue ("NAME", $control_props['NAME']);

							$newControl = $newcb->addEntrymask($sourceContentBlockInfo['ENTRYMASK']);

							if ($newContentblock > 0) {
								// Get the LinkId of the newly created contentblock
								$newLinkIds[] = $myObject->getEmbeddedCblockLinkId($newContentblock);

								$oldFormfieldContent = $sourcecb->getFormfieldsInternal($contentBlockLinkId);
								$newFormfieldContent = $newContentblock->getFormfieldsInternal($newControl);
								for ($c = 0; $c < count($newFormfieldContent); $c++) {
									$newContentblock->setFormfield($newFormfieldContent[$c]['LINKID'],
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

						}
					} else {
						$newLinkIds[] = $myObject->addCblockLink($contentblockID, $contentarea);
					}

					$cblockInfo = $cb->get();

					// Add to history
					$myObject->history->add ($historyType, $contentarea, $cblockInfo['NAME'], 'TXT_'.$historyStr.'_H_COADD', $contentblockID );

					for ($i = 0; $i < count($contentareas); $i++) {
						if ($contentareas[$i]['CODE']==$contentarea) {

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
												$file = sFileMgr()->getFile($controlFormfields[$w]['VALUE01']);
												if ($file) {
													$fileInfo = $file->get();
													$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
												}
											}
											if ($controlFormfields[$w]['FORMFIELD'] == 7) {
												$clcb = sCblockMgr()->getCblock($controlFormfields[$w]['VALUE01']);
												if ($clcb) {
													$info = $clcb->get();
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

						$js_array = substr($js_array, 0, strlen($js_array)-2);

						$js_array .= ' ]';

						if ($js_array == ' ]') {
							$js_array = '[]';
						}

						$koala->queueScript( "Koala.windows['wid_".$parentwindow."'].getContentareaDataFuncs( ".$js_array.", true );" );

						$myQueue->add ($pageID, $historyType, 'HIGHLIGHT_'.$historyStr, sGuiUS(), 'name');
					}
					// END GET CONTENTAREADATA
				}
			}
			break;

		case 'addEditContentblock':

			$target_co = $this->params['page'];
			$contentblockID = $this->params['contentblockId'];
			$parentwindow = $this->params['openerRefID'];
			$refresh = $this->params['refresh'];
			$target_id = $this->params['targetId'];
			$target_pos = $this->params['targetPosition'];

			$cb = sCblockMgr()->getCblock($contentblockID);
			$cblockInfo = $cb->get();

			if ($contentblockID > 0) {
				$cb = sCblockMgr()->getCblock($contentblockID);
				$targetcb = sCblockMgr()->getCblock($target_co);

				// Get original order of controls
				$co_oldlink_info = $targetcb->getEntrymasks();
				$co_oldlinks = array();
				foreach($co_oldlink_info as $co_oldlink_info_item) {
					array_push( $co_oldlinks, $co_oldlink_info_item['LINKID'] );
				}

				$co_controllinks = $cb->getEntrymasks();

				$new_controls = array();
				foreach($co_controllinks as $co_controllink) {
					$controlinfo = sCblockMgr()->getCblockLinkByEntrymaskLinkId( $co_controllink['LINKID'] );
					$lcb = sCblockMgr()->getCblock($controlinfo['CBLOCKID']);
					$new_control = $targetcb->addEntrymask($controlinfo['ENTRYMASK']);
					array_push( $new_controls, $new_control );
					$controlFormfields = $lcb->getFormfieldsInternal($co_controllink['LINKID']);

					// Get Formfields for control
					$controlFormfields_new = $targetcb->getFormfieldsInternal($new_control);

					for ($c = 0; $c < count($controlFormfields); $c++) {
						$formfield = $controlFormfields[$c]['FORMFIELD'];
						$targetcb->setFormfield($controlFormfields_new[$c]['LINKID'],
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

					// Change the id in the $newcolist array
					foreach ($newcolists[0] as $idx => $item) {
						if ($item[0]==$moved_co) {
							$newcolists[0][$idx][0] = $target_co;
							$newcolists[0][$idx][1] = $new_control;
						}
					}
				}

				if (($target_id!='') && ($target_pos!='')) {
					// Move new controls to target position
					$new_colist_order = array();
					foreach($co_oldlinks as $co_oldlink) {
						if ($co_oldlink == $target_id) {
							if ($target_pos == 'before') {
								foreach($new_controls as $new_control) {
									array_push( $new_colist_order, $new_control );
								}
								array_push( $new_colist_order, $co_oldlink );
							} elseif ($target_pos == 'after') {
								array_push( $new_colist_order, $co_oldlink );
								foreach($new_controls as $new_control) {
									array_push( $new_colist_order, $new_control );
								}
							}
						} else {
							array_push( $new_colist_order, $co_oldlink );
						}
					}

					if ($targetcb->setEntrymaskOrder($new_colist_order ) === false) {
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					} else {
						// Add to history
						$targetcb->history->add (HISTORYTYPE_CO, 0, $cblockInfo['NAME'], 'TXT_CBLOCK_H_EMORDER', $cblockInfo['OBJECTID'] );
						$jsQueue->add ($target_co, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');
					}

				} else {
					// Add to history
					$targetcb->history->add (HISTORYTYPE_CO, 0, $cblockInfo['NAME'], 'TXT_CBLOCK_H_EMORDER', $cblockInfo['OBJECTID'] );
					$jsQueue->add ($target_co, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');
				}

				/* Re-get all controls in contentblock */
				$finalcolist = array();
				$colist = $targetcb->getEntrymasks();
				foreach($colist as $colistitem) {
					$finalcolist[] = (string)$colistitem['LINKID'];
				}

				$dta_cnt = 0;
				$js_array = '[ ';
				foreach ($finalcolist as $entrymask_list_item) {
					$dta_cnt++;
					$js_array .= "[ '".$target_co."', '".(($itext['TXT_CONTENT']!='')?($itext['TXT_CONTENT']):('$TXT_CONTENT'))."', '0', '".$entrymask_list_item."'  ], ";
				}
				$js_array = substr($js_array, 0, strlen($js_array)-2);
				$js_array .= ' ]';

				if ($js_array == ' ]') {
					$js_array = '[]';
				}

				$koala->queueScript( "Koala.windows['wid_".$parentwindow."'].getContentareaDataFuncs( ".$js_array.", true );" );
				$koala->queueScript( "if ($('clone%%cblock__')) { $('clone%%cblock__').remove(); }" );
			}
			break;

		}

?>