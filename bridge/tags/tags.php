<?php

	$jsQueue = new JSQueue(NULL);
	$tagMgr = new Tags();

	switch ($action) {

		case 'moveTag':

			$source = $this->params['source'];
			$target = $this->params['target'];
			$parentwindow = $this->params['openerRef'];
			$before = $this->params['before'];
			$confirmed = $this->params['confirmed'];
			$positive = $this->params['positive'];

			if ($source == $target) {
				break;
			}

			if ($confirmed != 'true') {
				$parameters = array(
					'source'	=> $source,
					'target'	=> $target,
					'openerRef'	=> $parentwindow,
					'before'	=> $before
				);
				$koala->callJSFunction( 'Koala.yg_confirm',
					($itext['TXT_MOVE_TAG']!='')?($itext['$TXT_MOVE_TAG']):('$TXT_MOVE_TAG'),
					($itext['TXT_REALLY_MOVE_TAG']!='')?($itext['TXT_REALLY_MOVE_TAG']):('$TXT_REALLY_MOVE_TAG'),
					$action, json_encode($parameters)
				);
			} else if ( ($confirmed == 'true') && ($positive == 'true') ) {
				$hasMoved = true;
				foreach($source as $source_item) {
					if ($tagMgr->tree->moveTo($source_item, $target)) {
						$jsQueue->add ($source_item, HISTORYTYPE_TAG, 'TAG_MOVE', sGuiUS(), $target);
					} else {
						$hasMoved = false;
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					}
				}
				if ($hasMoved) {
					$parent_ids = array();
					$parents = $tagMgr->getParents($source);

					foreach($parents as $parent_item) {
						array_push( $parent_ids, $parent_item[0]['ID'] );
					}
					$parent_ids = array_reverse( $parent_ids );
					array_shift( $parent_ids );
					array_push( $parent_ids, $source );
					$parent_ids = implode(',', $parent_ids);

					$icons = new Icons();
					$tagInfo = $tagMgr->get($source);

					$koala->callJSFunction( 'Koala.yg_selectTreeNode', $parentwindow, 'page', $source[0] );
				}
			}
			break;

		case 'addTagChildFolder':

			$tag = $this->params['tag'];
			$tagName = $this->params['tagName'];

			if (!$tagName) $tagName = $itext['TXT_TAG_NEW'];

			// Check if root node is selected
			if ($tag==='root') {
				// Get real Page-ID of Root-Node
				$tags = $tagMgr->getList();
				$tag = $tags[0]['ID'];
			}

			// Add new child node
			$new_id = $tagMgr->add($tag, $tagName);

			if ( $new_id != false ) {
				$icons = new Icons();
				$jsQueue->add ($new_id, HISTORYTYPE_TAG, 'TAG_ADD', sGuiUS(), NULL);
			} else {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			}
			break;

		case 'deleteTag':

			$tagID = $this->params['tagID'];
			$siteID = $this->params['site'];

			// Delete tag
			$successfullyDeleted = $tagMgr->remove($tagID);
			if (in_array($tagID, $successfullyDeleted)) {
				foreach($successfullyDeleted as $successfullyDeletedItem) {
					$jsQueue->add ($successfullyDeletedItem, HISTORYTYPE_TAG, 'OBJECT_DELETE', sGuiUS(), 'tag', NULL, NULL, $successfullyDeletedItem.'-'.$siteID, 'name');
				}
			} else {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			}
			break;

		case 'setTagName':

			// Cannot use new style of parameters here (function is called by a custom-attribute event - and therefore does not know about named parameters)
			// Split PageID and SiteID
			$data = explode('-', $this->reponsedata['name']->yg_id );

			// Set the new name
			if ( $tagMgr->setName( $data[0], $this->reponsedata['name']->value ) === false ) {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			} else {
				$jsQueue->add ($data[0], HISTORYTYPE_TAG, 'OBJECT_CHANGE', sGuiUS(), 'tag', NULL, NULL, $this->reponsedata['name']->yg_id, 'name', $this->reponsedata['name']->value);
				$jsQueue->add ($data[0], HISTORYTYPE_TAG, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $this->reponsedata['name']->yg_id, 'name', $this->reponsedata['name']->value);
				$jsQueue->add ($data[0], HISTORYTYPE_TAG, 'REFRESH_WINDOW', sGuiUS(), 'name');
			}
			break;

		case 'tagSelectNode':

			$node = $this->params['node'];
			$wid = $this->params['wid'];

			$root_node = $tagMgr->getTree(NULL, 0);

			// Tags

			// 1 = rsub
			// 2 = rread
			// 3 = rdelete
			// 4 = parent -> rsub & rwrite
			// 5 = parent -> rsub & rwrite
			// 6 = rdelete
			$buttons = array();

			// Get Parents
			$parentid = $tagMgr->getParents($node);
			$parentid = $parentid[0][0]['ID'];

			// Check rights
			$rread = $tagMgr->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RREAD" );
			$rwrite = $tagMgr->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RWRITE" );
			$rsub = $tagMgr->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RSUB" );
			$rdelete = $tagMgr->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RDELETE" );

			// Check rights of parents
			$prsub = $tagMgr->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $parentid, "RSUB" );
			$prwrite = $tagMgr->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $parentid, "RWRITE" );

			// Check permissions for button "add"
			if ($rsub) {
				$koala->queueScript( "if ($('wid_".$wid."_addbutton'))  $('wid_".$wid."_addbutton').removeClassName('disabled')" );
				$buttons[0] = true;
			} else {
				$koala->queueScript( "if ($('wid_".$wid."_addbutton'))  $('wid_".$wid."_addbutton').addClassName('disabled')" );
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
			break;

		case 'addObjectTag':

			$objectid = $this->params['objectID'];
			$objecttype = $this->params['objectType'];
			$siteID = $this->params['site'];
			$newtagid = $this->params['tagId'];
			$targetid = $this->params['targetId'];
			$targetposition = $this->params['targetPosition'];
			$parentwindow = $this->params['openerRefID'];

			switch ($objecttype) {
				case 'cblock':		// For Contentblocks
					if (!$objectid) break;
					$cb = sCblockMgr()->getCblock($objectid);
					$cblockInfo = $cb->get();
					if ( $cb->tags->assign($newtagid) !== false ) {
						if ($targetid > 0) {
							$taglist = $cb->tags->getAssigned();
							for ($i = 0; $i < count($taglist); $i++) {
								if (($targetid == $taglist[$i]['ID']) && ($targetposition == 'after')) {
									$cb->tags->setOrder($taglist[$i]['ID'], $i);
									$i++;
									$cb->tags->setOrder($newtagid, $i);
								} else if (($targetid == $taglist[$i]['ID']) && ($targetposition == 'before')) {
									$cb->tags->setOrder($newtagid, $i);
									$i++;
									$cb->tags->setOrder($taglist[$i]['ID']);
								}
							}
						}
						$tagInfo = $cb->tags->get($newtagid);
						$myparents = $cb->tags->tree->getParents( $newtagid );
						$parents = array();
						for ($p = count($myparents)-2; $p >= 0; $p--) {
							$parents[$p] = $cb->tags->get($myparents[$p]);
							$js_parents .= "'".$parents[$p]['NAME']."'";
							if ($p!=0) $js_parents .= ', ';
						}
						$js_parents = '['.$js_parents.']';

						// Add to history
						$cb->history->add( HISTORYTYPE_CO, NULL, $tagInfo['NAME'], "TXT_TAG_H_ASSIGN", $newtagid );

						// Add tags to sortables (if present)
						$jsQueue->add ($objectid, HISTORYTYPE_CO, 'OBJECT_ADD_TAG', sGuiUS(), $objecttype, NULL, NULL, $objectid.'-cblock', 'yg_taglist', $newtagid, $tagInfo['NAME'], $js_parents, $targetid, $targetposition);

						// Refresh tags for files (if needed)
						$taglist = $cb->tags->getAssigned();

						for ($t = 0; $t < count($taglist); $t++) {
							$tp = array();
							$tp = $cb->tags->tree->getParents($taglist[$t]['ID']);
							$tp2 = array();
							for ($p = 0; $p < count($tp); $p++) {
								$tinfo = $cb->tags->get($tp[$p]);
								$tp2[$p]['ID'] = $tinfo['ID'];
								$tp2[$p]['NAME'] = $tinfo['NAME'];
							}
							array_pop($tp2);
							$taglist[$t]['PARENTS'] = $tp2;
						}
						$taglist = json_encode($taglist);
						$jsQueue->add ($objectid, HISTORYTYPE_CO, 'REFRESH_TAGS', sGuiUS(), $taglist);

						if ($cblockInfo['FOLDER']==0) {
							$jsQueue->add ($objectid, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');
						}
					}
					break;

				case 'file':		// For Files
				case 'filefolder':	// For Filefolder
					if (!$objectid) break;
					$file = sFileMgr()->getFile($objectid);
					$fileinfo = $file->get();
					if ($file->tags->assign($newtagid) === false) {
						//$koala->alert( $itext['UNDEFINED_TAG_ERROR'] );
						//$koala->queueScript( 'window.addToSortable=undefined;' );
					} else {
						if ($targetid > 0) {
							$taglist = $file->tags->getAssigned();
							for ($i = 0; $i < count($taglist); $i++) {
								if (($targetid == $taglist[$i]['ID']) && ($targetposition == 'after')) {
									$file->tags->setOrder($taglist[$i]['ID'], $i);
									$i++;
									$file->tags->setOrder($newtagid, $i);
								} else if (($targetid == $taglist[$i]['ID']) && ($targetposition == 'before')) {
									$file->tags->setOrder($newtagid, $i);
									$i++;
									$file->tags->setOrder($taglist[$i]['ID'], $i);
								}
							}
						}
						$tagInfo = $file->tags->get($newtagid);
						$myparents = $file->tags->tree->getParents( $newtagid );
						$parents = array();
						for ($p = count($myparents)-2; $p >= 0; $p--) {
							$parents[$p] = $file->tags->get($myparents[$p]);
							$js_parents .= "'".$parents[$p]['NAME']."'";
							if ($p!=0) $js_parents .= ', ';
						}
						$js_parents = '['.$js_parents.']';

						// if filefolder change $objecttype
						if ($fileinfo["FOLDER"] == 1) $objecttype = "filefolder";

						// Add to history
						$file->history->add( HISTORYTYPE_FILE, NULL, $tagInfo['NAME'], "TXT_TAG_H_ASSIGN", $newtagid );

						// Add tags to sortables (if present)
						$jsQueue->add ($objectid, HISTORYTYPE_FILE, 'OBJECT_ADD_TAG', sGuiUS(), $objecttype, NULL, NULL, $objectid.'-file', 'yg_taglist', $newtagid, $tagInfo['NAME'], $js_parents, $targetid, $targetposition);

						// Refresh tags for files (if needed)
						$taglist = $file->tags->getAssigned();

						for ($t = 0; $t < count($taglist); $t++) {
							$tp = array();
							$tp = $file->tags->tree->getParents($taglist[$t]['ID']);
							$tp2 = array();
							for ($p = 0; $p < count($tp); $p++) {
								$tinfo = $file->tags->get($tp[$p]);
								$tp2[$p]['ID'] = $tinfo['ID'];
								$tp2[$p]['NAME'] = $tinfo['NAME'];
							}
							array_pop($tp2);
							$taglist[$t]['PARENTS'] = $tp2;
						}
						$taglist = json_encode($taglist);

						$fileversion = $file->getLatestVersion();
						$file->newVersion();

						$jsQueue->add ($objectid, HISTORYTYPE_FILE, 'REFRESH_TAGS', sGuiUS(), $taglist);
						$jsQueue->add ($objectid, HISTORYTYPE_FILE, 'CLEAR_FILEINFOS', sGuiUS(), NULL);
					}
					break;

				case 'page':		// For Pages
					if (!$objectid || !$siteID) break;
					$pageMgr = new PageMgr($siteID);
					$page = $pageMgr->getPage($objectid);
					$pageInfo = $page->get();

					$jsQueue = new JSQueue(NULL, $siteID);

					if ( $page->tags->assign($newtagid) === false ) {
						//$koala->alert( $itext['TXT_UNDEFINED_TAG_ERROR'] );
						//$koala->queueScript( 'window.addToSortable=undefined;' );
					} else {
						if ($targetid > 0) {
							$taglist = $page->tags->getAssigned();
							for ($i = 0; $i < count($taglist); $i++) {
								if (($targetid == $taglist[$i]["ID"]) && ($targetposition == "after")) {
									$page->tags->setOrder($taglist[$i]["ID"], $i);
									$i++;
									$page->tags->setOrder($newtagid, $i);
								} else if (($targetid == $taglist[$i]["ID"]) && ($targetposition == "before")) {
									$page->tags->setOrder($newtagid, $i);
									$i++;
									$page->tags->setOrder($taglist[$i]["ID"], $i);
								}
							}
						}
						$tagInfo = $page->tags->get($newtagid);
						$myparents = $page->tags->tree->getParents( $newtagid );
						$parents = array();
						for ($p = count($myparents)-2; $p >= 0; $p--) {
							$parents[$p] = $page->tags->get($myparents[$p]);
							$js_parents .= "'".$parents[$p]['NAME']."'";
							if ($p!=0) $js_parents .= ', ';
						}
						$js_parents = '['.$js_parents.']';

						// Add to history
						$page->history->add( HISTORYTYPE_PAGE, NULL, $tagInfo['NAME'], "TXT_TAG_H_ASSIGN", $newtagid );
						// Check if refresh needed
						if ($targetid != 'norefresh') {
							$jsQueue->add ($objectid, HISTORYTYPE_PAGE, 'OBJECT_ADD_TAG', sGuiUS(), $objecttype, NULL, NULL, $objectid.'-'.$siteID, 'yg_taglist', $newtagid, $tagInfo['NAME'], $js_parents, $targetid, $targetposition);
						}
						$jsQueue->add ($objectid, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');
					}
					break;

				case 'mailing':		// For Mailings
					if (!$objectid) break;
					$mailing = sMailingMgr()->getMailing($objectid);
					$mailingInfo = $mailing->get();

					if ( $mailing->tags->assign($newtagid) === false ) {
						//$koala->alert( $itext['TXT_UNDEFINED_TAG_ERROR'] );
						//$koala->queueScript( 'window.addToSortable=undefined;' );
					} else {
						if ($targetid > 0) {
							$taglist = $mailing->tags->getAssigned();
							for ($i = 0; $i < count($taglist); $i++) {
								if (($targetid == $taglist[$i]["ID"]) && ($targetposition == "after")) {
									$mailing->tags->setOrder($taglist[$i]["ID"], $i);
									$i++;
									$mailing->tags->setOrder($newtagid, $i);
								} else if (($targetid == $taglist[$i]["ID"]) && ($targetposition == "before")) {
									$mailing->tags->setOrder($newtagid, $i);
									$i++;
									$mailing->tags->setOrder($taglist[$i]["ID"], $i);
								}
							}
						}
						$tagInfo = $mailing->tags->get($newtagid);
						$myparents = $mailing->tags->tree->getParents( $newtagid );
						$parents = array();
						for ($p = count($myparents)-2; $p >= 0; $p--) {
							$parents[$p] = $mailing->tags->get($myparents[$p]);
							$js_parents .= "'".$parents[$p]['NAME']."'";
							if ($p!=0) $js_parents .= ', ';
						}
						$js_parents = '['.$js_parents.']';

						// Add to history
						$mailing->history->add( HISTORYTYPE_MAILING, NULL, $tagInfo['NAME'], "TXT_TAG_H_ASSIGN", $newtagid );
						// Check if refresh needed
						if ($targetid != 'norefresh') {
							$jsQueue->add ($objectid, HISTORYTYPE_MAILING, 'OBJECT_ADD_TAG', sGuiUS(), $objecttype, NULL, NULL, $objectid.'-mailing', 'yg_taglist', $newtagid, $tagInfo['NAME'], $js_parents, $targetid, $targetposition);
						}
						$jsQueue->add ($objectid, HISTORYTYPE_MAILING, 'HIGHLIGHT_MAILING', sGuiUS(), 'name');
					}
					break;
			}
			break;

		case 'deleteObjectTag':

			$tag = $this->params['tagId'];
			$objectid = $this->params['objectID'];
			$objectType = $this->params['objectType'];
			$siteID = $this->params['siteID'];

			switch ($objectType) {
				case 'cblock':		// For Contentblocks
					$cb = sCblockMgr()->getCblock($objectid);
					$cblockInfo = $cb->get();

					$tagInfo = $cb->tags->get( $tag );
					if ($cb->tags->unassign($tag) === false) {
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					} else {
						$jsQueue->add ($objectid, HISTORYTYPE_CO, 'OBJECT_DELETE', sGuiUS(), 'tag', NULL, NULL, $tag.'-tag', 'name', 'cblock', $objectid.'-cblock');

						// Refresh tags for contentblocks (if needed)
						$taglist = $cb->tags->getAssigned();

						for ($t = 0; $t < count($taglist); $t++) {
							$tp = array();
							$tp = $cb->tags->tree->getParents($taglist[$t]['ID']);
							$tp2 = array();
							for ($p = 0; $p < count($tp); $p++) {
								$tinfo = sCblockMgr()->tags->get($tp[$p]);
								$tp2[$p]['ID'] = $tinfo['ID'];
								$tp2[$p]['NAME'] = $tinfo['NAME'];
							}
							array_pop($tp2);
							$taglist[$t]['PARENTS'] = $tp2;
						}
						$taglist = json_encode($taglist);
						$jsQueue->add ($objectid, HISTORYTYPE_CO, 'REFRESH_TAGS', sGuiUS(), $taglist);
						if (!$cblockInfo['FOLDER']) {
							$jsQueue->add ($objectid, HISTORYTYPE_CO, 'HIGHLIGHT_CBLOCK', sGuiUS(), 'name');
						}

						// Add to history
						$cb->history->add( HISTORYTYPE_CO, NULL, $tagInfo[NAME], "TXT_TAG_H_REMOVE", $tag );
					}
					break;

				case 'file':		// For Files
				case 'filefolder':	// For Filefolders
					$file = sFileMgr()->getFile($objectid);
					$fileinfo = $file->get();

					$tagInfo = $file->tags->get( $tag );
					if ($file->tags->unassign($tag) === false) {
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					} else {
						$jsQueue->add ($objectid, HISTORYTYPE_FILE, 'OBJECT_DELETE', sGuiUS(), 'tag', NULL, NULL, $tag.'-tag', 'name', 'file', $objectid.'-file');
						$jsQueue->add ($objectid, HISTORYTYPE_FILE, 'OBJECT_DELETE', sGuiUS(), 'tag', NULL, NULL, $tag.'-tag', 'name', 'filefolder', $objectid.'-file');

						// Refresh tags for files (if needed)
						$taglist = $file->tags->getAssigned();

						for ($t = 0; $t < count($taglist); $t++) {
							$tp = array();
							$tp = $file->tags->tree->getParents($taglist[$t]['ID']);
							$tp2 = array();
							for ($p = 0; $p < count($tp); $p++) {
								$tinfo = $file->tags->get($tp[$p]);
								$tp2[$p]['ID'] = $tinfo['ID'];
								$tp2[$p]['NAME'] = $tinfo['NAME'];
							}
							array_pop($tp2);
							$taglist[$t]['PARENTS'] = $tp2;
						}
						$taglist = json_encode($taglist);

						$fileversion = $file->getLatestVersion();
						$file->newVersion();

						$jsQueue->add ($objectid, HISTORYTYPE_FILE, 'REFRESH_TAGS', sGuiUS(), $taglist);
						$jsQueue->add ($objectid, HISTORYTYPE_FILE, 'CLEAR_FILEINFOS', sGuiUS(), NULL);
					}
					break;

				case 'page':	// For Pages
					$pageMgr = new PageMgr($siteID);
					$page = $pageMgr->getPage($objectid);
					$pageInfo = $page->get();

					$jsQueue = new JSQueue(NULL, $siteID);

					$tagInfo = $page->tags->get( $tag );
					if ($page->tags->unassign($tag) === false) {
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					} else {
						$jsQueue->add ($objectid, HISTORYTYPE_PAGE, 'OBJECT_DELETE', sGuiUS(), 'tag', NULL, NULL, $tag.'-tag', 'name', 'page', $objectid.'-'.$siteID);
						$jsQueue->add ($objectid, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');

						// Add to history
						$page->history->add( HISTORYTYPE_PAGE, NULL, $tagInfo[NAME], "TXT_TAG_H_REMOVE", $tag );
					}
					break;

				case 'mailing':	// For Mailings
					$mailing = sMailingMgr()->getMailing($objectid);
					$mailingInfo = $mailing->get();

					$tagInfo = $mailing->tags->get( $tag );
					if ($mailing->tags->unassign($tag) === false) {
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					} else {
						$jsQueue->add ($objectid, HISTORYTYPE_MAILING, 'OBJECT_DELETE', sGuiUS(), 'tag', NULL, NULL, $tag.'-tag', 'name', 'mailing', $objectid.'-mailing');
						$jsQueue->add ($objectid, HISTORYTYPE_MAILING, 'HIGHLIGHT_MAILING', sGuiUS(), 'name');

						// Add to history
						$mailing->history->add( HISTORYTYPE_PAGE, NULL, $tagInfo[NAME], "TXT_TAG_H_REMOVE", $tag );
					}
					break;

			}
			break;

		case 'orderObjectTag':

			$objectid = $this->params['objectID'];
			$objectType = $this->params['objectType'];
			$siteID = $this->params['site'];
			$taglist = $this->params['listArray'];

			switch ($objectType) {
				case 'cblock':	// For Contentblocks
					$cb = sCblockMgr()->getCblock($objectid);
					$cblockInfo = $cb->get();

					for ($i = 0; $i < count($taglist); $i++) {
						$cb->tags->setOrder($taglist[$i], $i);
					}

					// Add to history
					$cb->history->add( HISTORYTYPE_CO, NULL, NULL, "TXT_TAG_H_TAGORDER", NULL );
					break;

				case 'file':	// For Files
				case 'filefolder':
					$file = sFileMgr()->getFile($objectid);
					$fileinfo = $file->get();

					for ($i = 0; $i < count($taglist); $i++) {
						$file->tags->setOrder($taglist[$i], $i);
					}

					// Add to history
					$file->history->add( HISTORYTYPE_FILE, NULL, NULL, "TXT_TAG_H_TAGORDER", NULL );
					break;

				case 'page':	// For Pages
					$pageMgr = new PageMgr($siteID);
					$page = $pageMgr->getPage($objectid);
					$pageInfo = $page->get();

					$jsQueue = new JSQueue(NULL, $siteID);

					for ($i = 0; $i < count($taglist); $i++) {
						$page->tags->setOrder($taglist[$i], $i);
					}

					// Add to history
					$page->history->add( HISTORYTYPE_PAGE, NULL, NULL, "TXT_TAG_H_TAGORDER", NULL );
					$jsQueue->add ($objectid, HISTORYTYPE_PAGE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');

					break;
			}
			break;

	}

?>