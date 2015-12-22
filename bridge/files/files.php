<?php

	$jsQueue = new JSQueue(NULL);
	$reftracker = new Reftracker();
	$viewMgr = new Views();
	$filetypeMgr = new Filetypes();

	switch ($action) {

			case 'getFileInfo':
				$fileID = $this->params['fileID'];
				$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'SET_FILEINFOS', sGuiUS(), NULL);
				break;

			case 'saveViews':
				$widprefix = $this->params['wid'];
				$view_ids = $this->params[ $widprefix.'_view_ids[]'];
				$view_ids = explode(',',$view_ids);
				$view_del_ids = $this->params[ $widprefix.'_view_del_ids[]'];
				$view_del_ids = explode(',',$view_del_ids);

				foreach($view_del_ids as $view_del_id) {
					$viewMgr->remove($view_del_id);
				}

				$view_info = array();

				foreach($view_ids as $view_id) {
					$view_info_item['ID'] = $view_id;
					$view_info_item['NAME'] = $this->params[ $widprefix.'_view_'.$view_id.'_name'];
					$view_info_item['IDENTIFIER'] = $this->params[ $widprefix.'_view_'.$view_id.'_identifier'];
					$view_info_item['WIDTH'] = $this->params[ $widprefix.'_view_'.$view_id.'_width'];
					$view_info_item['HEIGHT'] = $this->params[ $widprefix.'_view_'.$view_id.'_height'];
					$view_info_item['CONSTRAINWIDTH'] = $this->params[ $widprefix.'_view_'.$view_id.'_constrainwidth'];
					$view_info_item['CONSTRAINHEIGHT'] = $this->params[ $widprefix.'_view_'.$view_id.'_constrainheight'];
					$view_info_item['WIDTHCROP'] = $this->params[ $widprefix.'_view_'.$view_id.'_widthcrop'];
					$view_info_item['HEIGHTCROP'] = $this->params[ $widprefix.'_view_'.$view_id.'_heightcrop'];
					array_push($view_info, $view_info_item);
				}

				foreach($view_info as $view_info_item) {
					if (strpos( $view_info_item['ID'], 'NEW_') === 0) {
						$view_info_item['ID'] = $viewMgr->add( $viewMgr->tree->getRoot() );
					}
					$viewMgr->setName( $view_info_item['ID'], $view_info_item['NAME'] );
					$viewMgr->setIdentifier( $view_info_item['ID'], $view_info_item['IDENTIFIER'] );
					$viewMgr->setWidth( $view_info_item['ID'], $view_info_item['WIDTH'] );
					$viewMgr->setHeight( $view_info_item['ID'], $view_info_item['HEIGHT'] );
					if ($view_info_item['CONSTRAINWIDTH'] === '1') {
						$viewMgr->setWidthConstrain( $view_info_item['ID'], 1 );
					} else if ($view_info_item['CONSTRAINWIDTH'] === '0') {
						$viewMgr->setWidthConstrain( $view_info_item['ID'], 0 );
					}
					if ($view_info_item['CONSTRAINHEIGHT'] === '1') {
						$viewMgr->setHeightConstrain( $view_info_item['ID'], 1 );
					} else if ($view_info_item['CONSTRAINHEIGHT'] === '0') {
						$viewMgr->setHeightConstrain( $view_info_item['ID'], 0 );
					}
					if ($view_info_item['WIDTHCROP'] === '2') {
						$viewMgr->setWidthCrop( $view_info_item['ID'], 2 );
					} else if ($view_info_item['WIDTHCROP'] === '1') {
						$viewMgr->setWidthCrop( $view_info_item['ID'], 1 );
					} else if ($view_info_item['WIDTHCROP'] === '0') {
						$viewMgr->setWidthCrop( $view_info_item['ID'], 0 );
					}
					if ($view_info_item['HEIGHTCROP'] === '2') {
						$viewMgr->setHeightCrop( $view_info_item['ID'], 2 );
					} else if ($view_info_item['HEIGHTCROP'] === '1') {
						$viewMgr->setHeightCrop( $view_info_item['ID'], 1 );
					} else if ($view_info_item['HEIGHTCROP'] === '0') {
						$viewMgr->setHeightCrop( $view_info_item['ID'], 0 );
					}
				}
				//$koala->queueScript( "Koala.yg_fadeFields(\$('".$widprefix."'), 'input.changed');" );
				$koala->queueScript( 'Koala.windows[\''.$widprefix.'\'].tabs.select(Koala.windows[\''.$widprefix.'\'].tabs.selected,Koala.windows[\''.$widprefix.'\'].tabs.params);' );
				break;

			case 'saveFileTypes':
				$widprefix = $this->params['wid'];
				$filetype_ids = $this->params[ $widprefix.'_filetype_ids[]'];
				$filetype_ids = explode(',',$filetype_ids);
				$filetype_del_ids = $this->params[ $widprefix.'_filetype_del_ids[]'];
				$filetype_del_ids = explode(',',$filetype_del_ids);

				foreach($filetype_del_ids as $filetype_del_id) {
					if ($filetype_del_id !== '') {
						$filetypeMgr->remove($filetype_del_id);
					}
				}

				$filetype_info = array();

				foreach($filetype_ids as $filetype_id) {
					if ($filetype_id !== '') {
						$filetype_info_item['ID'] = $filetype_id;
						$filetype_info_item['ABBREVIATION'] = strtoupper($this->params[ $widprefix.'_filetype_'.$filetype_id.'_abbreviation']);
						$filetype_info_item['CODE'] = $this->params[ $widprefix.'_filetype_'.$filetype_id.'_code'];
						$filetype_info_item['COLOR'] = $this->params[ $widprefix.'_filetype_'.$filetype_id.'_color'];
						$filetype_info_item['EXTENSIONS'] = $this->params[ $widprefix.'_filetype_'.$filetype_id.'_extensions'];
						$filetype_info_item['PROCESSOR'] = $this->params[ $widprefix.'_filetype_'.$filetype_id.'_processor'];
						$filetype_info_item['TITLE'] = $this->params[ $widprefix.'_filetype_'.$filetype_id.'_title'];
						array_push($filetype_info, $filetype_info_item);
					}
				}

				foreach($filetype_info as $filetype_info_item) {
					if (strpos( $filetype_info_item['ID'], 'NEW_') === 0) {
						$filetype_info_item['ID'] = $filetypeMgr->add( $filetypeMgr->tree->getRoot() );
					}
					$filetypeMgr->setName( $filetype_info_item['ID'], $filetype_info_item['TITLE'] );
					$filetypeMgr->setIdentifier( $filetype_info_item['ID'], $filetype_info_item['CODE'] );
					$filetypeMgr->setColor( $filetype_info_item['ID'], $filetype_info_item['COLOR'] );
					$extensions_clean = explode( ',', strtolower($filetype_info_item['EXTENSIONS']) );
					foreach($extensions_clean as $extensions_clean_idx => $extensions_clean_item) {
						$extensions_clean[$extensions_clean_idx] = trim($extensions_clean_item);
					}
					$extensions_clean = implode( ',', $extensions_clean );
					$extensions_clean = trim($extensions_clean, ',');
					$filetypeMgr->setExtensions( $filetype_info_item['ID'], $extensions_clean );
					$filetypeMgr->setProcessor( $filetype_info_item['ID'], $filetype_info_item['PROCESSOR'] );
					$filetypeMgr->setCode( $filetype_info_item['ID'], $filetype_info_item['ABBREVIATION'] );
				}
				//$koala->queueScript( "Koala.yg_fadeFields(\$('".$widprefix."'), 'input.changed');" );
				$koala->queueScript( 'Koala.windows[\''.$widprefix.'\'].tabs.select(Koala.windows[\''.$widprefix.'\'].tabs.selected,Koala.windows[\''.$widprefix.'\'].tabs.params);' );
				break;

			case 'addFileView':
				$fileID = $this->params['file'];
				$viewID = $this->params['view'];

				$file = sFileMgr()->getFile($fileID);
				$latestVersion = $file->getLatestApprovedVersion();
				$file = sFileMgr()->getFile($fileID, $latestVersion);
				$fileinfo = $file->get();
				$viewinfo = $viewMgr->get( $viewID );

				// Check if file is folder
				if ($fileinfo['FOLDER']==1) {
					// For folder
					$file->views->assign($viewID);
					$isFolder = 'true';
				} else {
					// For file
					$file->views->assign($viewID);
					$isFolder = 'false';
					$file->history->add( HISTORYTYPE_FILE, NULL, $viewID, 'TXT_FILE_H_ADDVIEW' );
				}
				$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'FILE_ADDVIEW', sGuiUS(), $viewID, $viewinfo['IDENTIFIER']);
				break;

			case 'deleteFileView':

				$viewID = $this->params['viewID'];
				$fileID = $this->params['fileID'];
				$positive = $this->params['positive'];
				$confirmed = $this->params['confirmed'];
				$file = sFileMgr()->getFile($fileID);
				$latestFinalVersion = $file->getLatestApprovedVersion();
				$file = sFileMgr()->getFile($fileID, $latestFinalVersion);
				$fileInfo = $file->get();

				if (($fileInfo['FOLDER']==1) && ($confirmed != 'true')) {
					$parameters = array(
						'viewID'	=> $viewID,
						'fileID'	=> $fileID
					);
					$viewInfo = $viewMgr->get($viewID);
					$messagePart1 = ($itext['TXT_REMOVE_VIEWS_FROM_FILES_1']!='')?($itext['TXT_REMOVE_VIEWS_FROM_FILES_1']):('$TXT_REMOVE_VIEWS_FROM_FILES_1');
					$messagePart2 = ($itext['TXT_REMOVE_VIEWS_FROM_FILES_2']!='')?($itext['TXT_REMOVE_VIEWS_FROM_FILES_2']):('$TXT_REMOVE_VIEWS_FROM_FILES_2');
					$message = $messagePart1.'<strong>'.$viewInfo['NAME'].'</strong>'.$messagePart2;
					$koala->callJSFunction( 'Koala.yg_confirm',
						($itext['TXT_VIEW_DEL']!='')?($itext['TXT_VIEW_DEL']):('$TXT_VIEW_DEL'),
						$message,
						$action, json_encode($parameters)
					);
				} else if (($fileInfo['FOLDER']==1) && ($confirmed == 'true')) {
					if ($positive == 'true') {
						if ($file->views->unassign($viewID, true) === false) {
							$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
							$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'FILE_CLEAR_DELVIEW', sGuiUS(), $viewID);
						} else {
							$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'FILE_DELVIEW', sGuiUS(), $viewID);
						}
					} else {
						if ($file->views->unassign($viewID, false) === false) {
							$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
							$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'FILE_CLEAR_DELVIEW', sGuiUS(), $viewID);
						} else {
							$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'FILE_DELVIEW', sGuiUS(), $viewID);
						}
					}
				} else {
					if ($file->views->unassign($viewID) === false) {
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
						$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'FILE_CLEAR_DELVIEW', sGuiUS(), $viewID);
					} else {
						$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'FILE_DELVIEW', sGuiUS(), $viewID);
						$file->history->add( HISTORYTYPE_FILE, NULL, $viewID, 'TXT_FILE_H_REMOVEVIEW' );
					}
				}
				break;

			case 'reUploadFile':

				$filetitle = $this->params['title'];
				if ($_FILES['Filedata']['tmp_name']) {
					$filetmpname = $_FILES['Filedata']['tmp_name'];
					$filename = basename($_FILES['Filedata']['name']);
				} else {
					$filetmpname = fixAndMovePLUploads();
					$filename = basename($_REQUEST['name']);
				}
				$filedir = $this->approot.$this->filesdir;
				$uploadID = $this->params['uploadID'];
				$uploadIndex = $this->params['uploadIndex'];
				$fileid = $this->params['fileID'];

				$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
				$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;

				if ( ($chunks!=0) && ($chunk!=($chunks-1)) ) {
					break;
				}

				$file = sFileMgr()->getFile($fileid);
				$fileinfo = $file->get();

				if (($filetmpname != '') && ($filetmpname != 'none')) {
					$updatedVersion = $file->updateFile($filename, $fileinfo['FILETYPE'], $filetmpname, true);
					if ($updatedVersion) {
						$file->properties->setValue("NAME", $filetitle);
						$file->history->add( HISTORYTYPE_FILE, NULL, $filename, 'TXT_FILE_H_REUPLOAD' );
						$koala->callJSFunction( 'Koala.yg_addFileId', $fileid."-".$updatedVersion, $uploadID, 'true', $fileinfo['NAME'], $fileinfo['COLOR'], $fileinfo['IDENTIFIER'] );
						$koala->queueScript( "window.hadUploadError = false;window.hadUploadErrorMsg = undefined;" );
					}
				}
				break;

			case 'uploadFile':

				$filetype = $this->params['type'];
				$filetitle = $this->params['title'];
				setlocale(LC_ALL, 'en_US.UTF8');
				if ($_FILES['Filedata']['tmp_name']) {
					$filetmpname = $_FILES['Filedata']['tmp_name'];
					$filename = basename($_FILES['Filedata']['name']);
				} else {
					$filetmpname = fixAndMovePLUploads();
					$filename = basename($_REQUEST['name']);
				}
				$filedir = $this->approot.$this->filesdir;
				$uploadID = $this->params['uploadID'];
				$uploadIndex = $this->params['uploadIndex'];
				$filesqueued = (int)$this->params['filesQueued'];
				$folderid = $this->params['folderId'];

				$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
				$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;

				if ( ($chunks!=0) && ($chunk!=($chunks-1)) ) {
					break;
				}

				if (!file_exists($filedir) || !is_writable($filedir) ) {
					$koala->queueScript( "parent.window.hadUploadError = true;" );
				} else {
					// Check if folderId is really folder, if not -> get the parent folder
					$folder = sFileMgr()->getFile($folderid);
					$folderinfo = $folder->get();

					if ($folderinfo['FOLDER']==0) {
						$folderid = $folderinfo['PARENT'];
					}

					if (!$filetitle) {
						// No title supplied, used filename (minus extension) as title
						$filetitle = explode('.', $filename);
						array_pop( $filetitle );
						$filetitle = implode('.', $filetitle);
						$hasnofiletitle = true;
					} else {
						$hasnofiletitle = false;
					}

					if ($filetype == "automatic") {
						// Try to get matching filetype
						$filetypes = $filetypeMgr->getList();
						$fileextension = explode('.', $filename);
						$fileextension = strtolower( $fileextension[count($fileextension)-1] );

						foreach($filetypes as $filetype_item) {
							$extensions = explode(',', $filetype_item['EXTENSIONS']);
							if (!is_array($extensions)) $extensions = $filetype_item['EXTENSIONS'];
							foreach($extensions as $extension_item) {
								if ($fileextension == strtolower($extension_item)) {
									$filetype = $filetype_item['OBJECTID'];
								}
							}
						}
					}

					// Check if filetype is still "automatic"
					if ($filetype == "automatic") {
						$defaultTypeInfo = $filetypeMgr->getByIdentifier("FILE");
						$filetype = $defaultTypeInfo[0]["OBJECTID"];
					}

					if (($filesqueued > 1) && (!$hasnofiletitle)) {
						if (strpos($uploadID, '_') != false) {
							$uploadindex = explode('_', $uploadID);
							$uploadindex = ' '.((int)$uploadindex[2]+1);
						} else {
							$uploadindex = ' '.$uploadIndex;
						}
					} else {
						$uploadindex = '';
					}

					if (($filetmpname != '') && ($filetmpname != 'none')) {
						$koala->queueScript( "parent.window.hadUploadError = false;window.hadUploadErrorMsg = undefined;" );
						$newFileId = sFileMgr()->add($folderid, $filetitle.$uploadindex, $filetype);
						$file = sFileMgr()->getFile($newFileId);
						$file->updateFile($filename, $filetype, $filetmpname, false);
						$PName = $file->calcPName();
						$file->setPName($PName);
						$file->newVersion();
						$fileinfo = $file->get();
						$file->history->add( HISTORYTYPE_FILE, NULL, $filename, 'TXT_FILE_H_REUPLOAD' );
						$koala->callJSFunction( 'parent.Koala.yg_addFileId', $newFileId."-".$file->getLatestVersion(), $uploadID , 'true', $fileinfo['NAME'], $fileinfo['COLOR'], $fileinfo['IDENTIFIER'] );
					}
				}
				break;

			case 'processUpload':

				$filePrefix = explode('-', $this->params['filePrefix']);
				$fileID = $filePrefix[0];
				$fileVersion = $filePrefix[1];
				$reUpload = $this->params['reUpload'];
				$filedir = getrealpath($this->approot.$this->filesdir)."/";

				$file = sFileMgr()->getFile($fileID, $fileVersion);
				if ($file) {
					$fileinfo = $file->get();

					$filename = $fileinfo['FILENAME'];
					$filepath = $filedir.$fileinfo['OBJECTID'].'-'.$fileinfo['VERSION'].$filename;
					$format = "unknown";

					if (($filepath != '') && ($filepath != 'none')) {

						$procs = sApp()->files_procs;
						$fileproc = $file->filetypes->getProcessor($fileinfo['FILETYPE']);

						// Check if the file has a processor
						$hasProcessor = false;
						for ($p = 0; $p < count($procs); $p++) {
							if ($procs[$p]['name'] == $fileproc) {
								$hasProcessor = true;
							}
						}

						// Resize
						$koala->callJSFunction( 'Koala.yg_setFileStatusOK', $fileID.'-'.$file->getLatestVersion() );

						$fileinfo['CHANGEDTS'] = $fileinfo['CHANGEDTS'];
						$fileinfo['REFS'] = $reftracker->getIncomingForFile( $fileinfo['OBJECTID'] );

						$tags = $file->tags->getAssigned();
						for ($t = 0; $t < count($tags); $t++) {
							$tp = array();
							$tp = $file->tags->tree->getParents($tags[$t]['ID']);
							$tp2 = array();
							for ($p = 0; $p < count($tp); $p++) {
								$tinfo = $file->tags->get($tp[$p]);
								$tp2[$p]['ID'] = $tinfo['ID'];
								$tp2[$p]['NAME'] = $tinfo['NAME'];
							}
							$tp2[count($tp2)-1]['NAME'] = ($itext['TXT_TAGS']!='')?($itext['TXT_TAGS']):('$TXT_TAGS');
							$tags[$t]['PARENTS'] = $tp2;
						}
						$fileinfo['TAGS'] = $tags;

						$fileinfo['THUMB'] = 0;
						$hiddenviews = $file->views->getHiddenViews();
						foreach($hiddenviews as $view) {
							if ($view['IDENTIFIER'] == 'yg-thumb') {
								$tmpviewinfo = $file->views->getGeneratedViewInfo($view['ID']);
								if ($tmpviewinfo[0]['TYPE'] == FILE_TYPE_WEBIMAGE) {
									$fileinfo['THUMB'] = 1;
								}
							}
						}

						$views = $file->views->getAssigned();
						foreach($views as $view) {
							if ($view["IDENTIFIER"] == "YGSOURCE") {
								$viewinfo = $file->views->getGeneratedViewInfo($view["ID"]);
								$fileinfo[$i]["WIDTH"] = $viewinfo[0]["WIDTH"];
								$fileinfo[$i]["HEIGHT"] = $viewinfo[0]["HEIGHT"];
							}
						}

						$file->approveVersion();
						if (!$reUpload) {
							$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'ADD_FILE', sGuiUS(), ($hasProcessor)?(NULL):('nothumb'));
						} else {
							$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'OBJECT_DELETE', sGuiUS(), 'file', NULL, NULL, $fileID.'-file', 'name');
							$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'ADD_FILE', sGuiUS(), ($hasProcessor)?(NULL):('nothumb'));
							$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'CLEAR_REFRESH', sGuiUS(), 'file');
						}
						$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'CLEAR_FILEINFOS', sGuiUS(), NULL);

					}
				}
				break;

			case 'fileSelectNode':

				$node = $this->params['node'];
				$wid = $this->params['wid'];

				$root_node = sFileMgr()->getTree(NULL, 0);

				// Files

				// 1 = rsub
				// 2 = rread
				// 3 = rdelete
				// 4 = parent -> rsub & rwrite
				// 5 = parent -> rsub & rwrite
				// 6 = rdelete
				$buttons = array();

				// Get Parents
				$parentid = sFileMgr()->getParents($node);
				$parentid = $parentid[0][0]['ID'];

				// Check rights
				$rread = sFileMgr()->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RREAD" );
				$rwrite = sFileMgr()->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RWRITE" );
				$rsub = sFileMgr()->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RSUB" );
				$rdelete = sFileMgr()->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $node, "RDELETE" );

				// Check rights of parents
				$prsub = sFileMgr()->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $parentid, "RSUB" );
				$prwrite = sFileMgr()->permissions->checkInternal( sUserMgr()->getCurrentUserID(), $parentid, "RWRITE" );

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

				if ($rsub) {
					$koala->callJSFunction( 'Koala.yg_setFileUploadButton', $wid, 'false' );
				} else {
					$koala->callJSFunction( 'Koala.yg_setFileUploadButton', $wid, 'true' );
				}
				break;

			case 'addFileChildFolder':

				$fileID = $this->params['file'];

				// Check if root node is selected
				if ($fileID==='root') {
					// Get real Page-ID of Root-Node
					$files = sFileMgr()->getList();
					$fileID = $files[0]['ID'];
				}

				// Add new child node
				$new_id = sFileMgr()->addFolder( $fileID, ($itext['TXT_NEW_FOLDER']!='')?($itext['TXT_NEW_FOLDER']):('$TXT_NEW_FOLDER') );

				// Set name, pname and approve the new folder
				$fileFolder = sFileMgr()->getFile($new_id);
				$fileFolder->properties->setValue('NAME', ($itext['TXT_NEW_FOLDER']!='')?($itext['TXT_NEW_FOLDER']):('$TXT_NEW_FOLDER'));

				if ( $new_id != false ) {
					$icons = new Icons();
					$jsQueue->add ($new_id, HISTORYTYPE_FILE, 'FILEFOLDER_ADD', sGuiUS(), NULL);
				} else {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				}
				break;

			case 'deleteFile':
			case 'deleteFolder':

				$fileID = $this->params['file'];
				$siteID = $this->params['site'];

				// Delete file/folder
				$file = sFileMgr()->getFile($fileID);
				$successfullyDeleted = $file->delete();
				if (in_array($fileID, $successfullyDeleted) === true) {
					foreach($successfullyDeleted as $successfullyDeletedItem) {
						$tmpFile = sFileMgr()->getFile($successfullyDeletedItem);
						if ($tmpFile) {
							$tmpFile->history->add( HISTORYTYPE_FILE, NULL, NULL, 'TXT_FILE_H_TRASHED' );
							$tmpFileInfo = $tmpFile->get();
							$jsQueue->add ($successfullyDeletedItem, HISTORYTYPE_FILE, 'OBJECT_DELETE', sGuiUS(), 'file', NULL, NULL, $successfullyDeletedItem.'-'.$siteID, 'name');
							$jsQueue->add ($successfullyDeletedItem, HISTORYTYPE_FILE, 'CLEAR_FILEINFOS', sGuiUS(), NULL);

							// Remove immediately if we have a folder
							if ($tmpFileInfo['FOLDER'] == 1) {
								sFileMgr()->remove($successfullyDeletedItem);
							}
						}
					}
					$file->history->add( HISTORYTYPE_FILE, NULL, NULL, 'TXT_FILE_H_TRASHED' );
					$fileInfo = $file->get();
					$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'OBJECT_DELETE', sGuiUS(), 'file', NULL, NULL, $fileID.'-'.$siteID, 'name');
					$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'CLEAR_FILEINFOS', sGuiUS(), NULL);

					// Remove immediately if we have a folder
					if ($fileInfo['FOLDER'] == 1) {
						sFileMgr()->remove($fileID);
					}
				} else {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				}

				$koala->queueScript('Koala.yg_hideFileHint();');
				break;

			case 'moveUpFolder':

				$fileID = $this->params['file'];
				$siteID = $this->params['site'];
				$reload = $this->params['reload'];
				if ($reload=='false') {	$reload = false; } else { $reload = true; }

				// Move folder up
				if (sFileMgr()->tree->up( $fileID ) === true) {
					$koala->callJSFunction( 'Koala.yg_moveUp', 'file', $fileID.'-'.$siteID, 'name', NULL, $reload );
				} else {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				}
				break;

			case 'moveDownFolder':

				$fileID = $this->params['file'];
				$siteID = $this->params['site'];
				$reload = $this->params['reload'];
				if ($reload=='false') {	$reload = false; } else { $reload = true; }

				// Move folder down
				if (sFileMgr()->tree->down( $fileID ) === true) {
					$koala->callJSFunction( 'Koala.yg_moveDown', 'file', $fileID.'-'.$siteID, 'name', NULL, $reload );
				} else {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				}
				break;

			case 'saveFileVersion':
				// Cannot use new style of parameters here (function is called by a custom-attribute event - and therefore does not know about named parameters)

				$data = explode('-', $this->reponsedata['null']->yg_id );
				$fileID = $data[0];
				$wid = $data[2];

				$file = sFileMgr()->getFile($fileID);
				$fileinfo = $file->get();
				$file->newVersion();

				$koala->queueScript( 'Koala.windows[\'wid_'.$wid.'\'].tabs.select(Koala.windows[\'wid_'.$wid.'\'].tabs.selected,Koala.windows[\'wid_'.$wid.'\'].tabs.params);' );
				break;

			case 'restoreFileVersion':

				$fileID = $this->params['file'];
				$version = $this->params['version'];
				$wid = $this->params['wid'];

				$file = sFileMgr()->getFile($fileID, $version);
				$oldinfo = $file->get();
				$new_version = $file->newVersion();

				$new_version = $file->getLatestVersion();

				$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'file', NULL, NULL, $fileID.'-file', 'name', $oldinfo['NAME']);
				$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'HIGHLIGHT_PAGE', sGuiUS(), 'name');

				$koala->queueScript( 'Koala.windows[\'wid_'.$wid.'\'].tabs.select(Koala.windows[\'wid_'.$wid.'\'].tabs.selected,Koala.windows[\'wid_'.$wid.'\'].tabs.params);' );

				$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'CLEAR_FILEINFOS', sGuiUS(), NULL);
				break;

			case 'changeFileType':

				$fileID = $this->params['file'];
				$wid = $this->params['wid'];
				$type = $this->params['type'];

				$file = sFileMgr()->getFile($fileID);
				$fileinfo = $file->get();

				$filetypes = sFileMgr()->getFiletypes();
				$newfiletype = $type;

				$file->setFileType($newfiletype);

				foreach($filetypes as $filetype) {
					if ($filetype['ID'] == $type) {
						if ($filetype['IDENTIFIER'] == 'DEFAULT') {
							$name = $itext['TXT_DEFAULT_FILETYPE'];
						} else {
							$name = $filetype['NAME'];
						}
						$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'file', NULL, NULL, $fileinfo['OBJECTID'].'-file', 'typedesc', $name);
						$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'OBJECT_CHANGECLASS', sGuiUS(), 'file', NULL, NULL, $fileinfo['OBJECTID'].'-file', 'type', 'filetype '.$filetype['COLOR']);
						$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'file', NULL, NULL, $fileinfo['OBJECTID'].'-file', 'type', $filetype['CODE']);
					}
				}

				// Add to history
				$file->history->add( HISTORYTYPE_FILE, $oldfiletype, $newfiletype, 'TXT_FILE_H_TYPECHANGE' );
				$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'CLEAR_FILEINFOS', sGuiUS(), NULL);
				break;

			case 'copyFile':

				$sourceID = $this->params['source'];
				$targetID = $this->params['target'];
				$recursive = $this->params['recursive'];
				$parentwindow = $this->params['openerRef'];

				$sourceFile = sFileMgr()->getFile($sourceID);
				$latestFinalVersion = $sourceFile->getLatestApprovedVersion();
				$sourceFile = sFileMgr()->getFile($sourceID, $latestFinalVersion);
				$oldfileinfo = $sourceFile->get();
				$copyjobs = sFileMgr()->getList($sourceID);
				$copyjobs = sFileMgr()->getAdditionalTreeInfo($sourceID, $copyjobs);

				$copystarted = false;
				$idmap = array();
				$haderror = false;
				for ($i = 0; $i < count($copyjobs); $i++) {
					if ($copyjobs[$i]['ID'] == $sourceID) {
						if ($copystarted === false) {
							$rootlevel = $copyjobs[$i]['LEVEL'];
							$copystarted = true;
							if ($copyjobs[$i]['FOLDER']) {
								$newFileID = sFileMgr()->addFolder($targetID);
							} else {
								$newFileID = sFileMgr()->add($targetID, $copyjobs[$i]['FOLDER']);
							}
							if (!$newFileID) {
								$haderror = true;
							} else {
								$newFile = sFileMgr()->getFile($newFileID);
								$newFile->copyFrom($sourceFile);
								$idmap[$sourceID] = $newFileID;

								// Inherit permissions of the parent of the newly created copy
								$allPerms = $sourceFile->permissions->getPermissions();
								//$newFile->permissions->clear();
								$newFile->permissions->setPermissions($allPerms);

								$PName = $newFile->calcPName();
								$newFile->setPName($PName);
								$newFile->approveVersion();

								$jsQueue->add ($newFileID, HISTORYTYPE_FILE, 'ADD_FILE', sGuiUS(), NULL);

								if ((int)$copyjobs[$i]['FOLDER'] == 1) {
									$jsQueue->add ($newFileID, HISTORYTYPE_FILE, 'FILE_ADD', sGuiUS(), NULL);
								}
							}
							$i++;
						}
					}
					if (($rootlevel < $copyjobs[$i]['LEVEL']) && ($copystarted === true)) {
						$sourceID = $copyjobs[$i]['ID'];
						$sourceFile = sFileMgr()->getFile($sourceID);
						$latestFinalVersion = $sourceFile->getLatestApprovedVersion();
						$sourceFile = sFileMgr()->getFile($sourceID, $latestFinalVersion);
						$sourceFileInfo = $sourceFile->get();
						$sourceParentID = $copyjobs[$i]['PARENT'];
						$targetParentID = $idmap[$sourceParentID];
						if ($sourceFileInfo['FOLDER']) {
							$newFileID = sFileMgr()->addFolder($targetParentID);
						} else {
							$newFileID = sFileMgr()->add($targetParentID);
						}
						$newFile = sFileMgr()->getFile($newFileID);
						$newFile->copyFrom($sourceFile);
						$idmap[$sourceID] = $newFileID;

						// Inherit permissions of the parent of the newly created copy
						$allPerms = $sourceFile->permissions->getPermissions();
						//$newFile->permissions->clear();
						$newFile->permissions->setPermissions($allPerms);

						$PName = $newFile->calcPName();
						$newFile->setPName($PName);

						$newFile->newVersion();
						$newFile->approveVersion();

						$jsQueue->add ($newFileID, HISTORYTYPE_FILE, 'ADD_FILE', sGuiUS(), NULL);

						if ($copyjobs[$i]['FOLDER']) {
							$jsQueue->add ($newFileID, HISTORYTYPE_FILE, 'FILE_ADD', sGuiUS(), NULL);
						}
					}
					if ($rootlevel >= $copyjobs[$i]['LEVEL']) {
						if ($copystarted === true) {
							break;
						}
					}
				}

				$parent_ids = array();
				$parents = sFileMgr()->getParents($sourceID);

				foreach($parents as $parent_item) {
					array_push( $parent_ids, $parent_item[0]['ID'] );
				}
				$parent_ids = array_reverse( $parent_ids );
				array_shift( $parent_ids );
				array_push( $parent_ids, $target );
				$parent_ids = implode(',', $parent_ids);

				if ($oldfileinfo['FOLDER']==1) {
					$koala->callJSFunction( 'Koala.yg_reloadTree', $parentwindow, 'file', $sourceID );
					$koala->callJSFunction( 'Koala.yg_expandTreeNodes', $parentwindow, 'file', $parent_ids, $sourceID, 'true' );
				}
				if ($haderror) {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				}
				break;

			case 'moveFile':

				$source = $this->params['source'];
				$target = $this->params['target'];
				$parentwindow = $this->params['openerRef'];
				$before = $this->params['before'];
				$sourcefile = sFileMgr()->getFile($source);

				if ($source == $target) {
					break;
				}
				$sourcefile = sFileMgr()->getFile($source);

				// Check if source-parent and target have the same id
				$tmpSourceInfo = $sourcefile->get();
				if ($tmpSourceInfo['PARENT'] == $target) {
					// Do nothing
					$moveSuccess = true;
				} elseif ($before != true) {
					if (sFileMgr()->tree->moveTo($source, $target)) {
						// Inherit permissions of the parent of the newly created copy
						$moveSuccess = true;
						$sourcefile->permissions->clear();
						$sourcefile->permissions->copyTo($target, $source);
					} else {
						$moveSuccess = false;
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					}
				} elseif ($before) {
					// Get parent of target-node
					$targetParent = sFileMgr()->tree->getParent($target);

					// Move source to parent of target-node
					if (sFileMgr()->tree->moveTo($source, $targetParent)) {
						// Inherit permissions of the parent of the newly created copy
						$moveSuccess = true;
						$sourcefile->permissions->clear();
						$sourcefile->permissions->copyTo($targetParent, $source);

						// Get left node of target-node
						$targetLeft = sFileMgr()->tree->getLeft($target);

						$old_targetLeft = 0;

						// Check if we have to move down, or do we have to move up?
						$children = sFileMgr()->tree->getDirectChildren( $targetParent );

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
						//$arrived = true;
						while (!$arrived) {
							$iterations++;
							switch($movedirection) {
								case 'up':
									// Get left node of target-node
									$targetLeft = sFileMgr()->tree->getLeft($target);

									if ($targetLeft!=$source) {
										// We're not to the left of the target, so we have to move our source-node up in the tree
										sFileMgr()->tree->up($source);
									} else {
										$arrived = true;
										break;
									}
									// Last resort...
									if ($iterations>200) {
										$arrived = true;
										break;
									}
									$old_targetLeft = $targetLeft;
									break;
								case 'down':
									// Get left node of target-node
									$targetLeft = sFileMgr()->tree->getLeft($target);

									if ($targetLeft!=$source) {
										// We're not to the left of the target, so we have to move our source-node up in the tree
										sFileMgr()->tree->down($source);
									} else {
										$arrived = true;
										break;
									}
									// Last resort...
									if ($iterations>200) {
										$arrived = true;
										break;
									}
									$old_targetLeft = $targetLeft;
									break;
							}
						}
					} else {
						$moveSuccess = false;
						$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
					}
				}

				if ($moveSuccess) {
					$parent_ids = array();
					$parents = sFileMgr()->getParents($source);

					foreach($parents as $parent_item) {
						array_push( $parent_ids, $parent_item[0]['ID'] );
					}
					$parent_ids = array_reverse( $parent_ids );
					array_shift( $parent_ids );
					array_push( $parent_ids, $source );
					$parent_ids = implode(',', $parent_ids);

					$sourceFile = sFileMgr()->getFile($source);
					$moinfo = $sourceFile->get();

					if ($moinfo['FOLDER']==1) {
						$jsQueue->add ($source, HISTORYTYPE_FILE, 'FILE_MOVE', sGuiUS(), $target);
					} else {
						if ($this->params['orgAction'] == 'restore') {
							$restoredFile = sFileMgr()->getFile($source);
							$restoredFile->undelete();
							$restoredFile->history->add( HISTORYTYPE_FILE, NULL, NULL, 'TXT_FILE_H_RESTORED');
							if ($this->params['lastItem']=='true') {
								$koala->queueScript('Koala.windows[\''.$parentwindow.'\'].tabs.select(Koala.windows[\''.$parentwindow.'\'].tabs.selected,{refresh:1});');
							}
						}

						$moinfo['TAGS'] = $sourceFile->tags->getAssigned();

						$views = $sourceFile->views->getAssigned();
						foreach($views as $view) {
							$viewinfo = $sourceFile->views->getGeneratedViewInfo($view["ID"]);
							if ($view["IDENTIFIER"] == "YGSOURCE") {
								if ($view["TYPE"] == FILE_TYPE_WEBIMAGE) {
									$moinfo['THUMB'] = 1;
									$moinfo["WIDTH"] = $viewinfo[0]["WIDTH"];
									$moinfo["HEIGHT"] = $viewinfo[0]["HEIGHT"];
								} else {
									$moinfo['THUMB'] = 0;
								}
							}
						}

						$jsQueue->add ($source, HISTORYTYPE_FILE, 'OBJECT_DELETE', sGuiUS(), 'file', NULL, NULL, $source.'-file', 'name');
						$jsQueue->add ($moinfo['OBJECTID'], HISTORYTYPE_FILE, 'ADD_FILE', sGuiUS(), NULL);
					}
				}

				$koala->queueScript('Koala.yg_hideFileHint();');
				break;

			case 'setFileName':

				// Cannot use new style of parameters here (function is called by a custom-attribute event - and therefore does not know about named parameters)
				// Split PageID and SiteID
				$data = explode('-', $this->reponsedata['name']->yg_id );
				$file = sFileMgr()->getFile($data[0]);
				$latestFinalVersion = $file->getLatestApprovedVersion();
				$file = sFileMgr()->getFile($data[0], $latestFinalVersion);
				$fileInfo = $file->get();

				// Set the new name
				if ( $file->properties->setValue("NAME", $this->reponsedata['name']->value) === false ) {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				} else {
					$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'file', NULL, NULL, $this->reponsedata['name']->yg_id, 'name', $this->reponsedata['name']->value);
					$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'filefolder', NULL, NULL, $this->reponsedata['name']->yg_id, 'name', $this->reponsedata['name']->value);
					if ($fileInfo['PNAME'] == NULL) {
						$PName = $file->calcPName();
						$file->setPName($PName);
						$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'OBJECT_CHANGEPNAME', sGuiUS(), 'file', NULL, NULL, $this->reponsedata['name']->yg_id, 'name', $PName);
						$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'filefolder', NULL, NULL, $this->reponsedata['name']->yg_id, 'pname', $PName);
						$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'REFRESH_WINDOW', sGuiUS(), 'pname');
					}

					$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'REFRESH_WINDOW', sGuiUS(), 'name');

					// Add to history
					$file->history->add( HISTORYTYPE_FILE, 'NAME', $this->reponsedata['name']->value, 'TXT_FILE_H_PROP');
					$jsQueue->add ($data[0], HISTORYTYPE_FILE, 'CLEAR_FILEINFOS', sGuiUS(), NULL);
				}
				break;

		case 'setFilePName':
			$fileID = $this->params['file'];
			$file = sFileMgr()->getFile($fileID);
			$value = $file->filterPName($this->params['value']);

			if (sFileMgr()->getFileIdByPname($value)) {
				$koala->callJSFunction('Koala.yg_promptbox', $itext['TXT_ERROR'], $itext['TXT_PNAME_ALREADY_USED_CHOOSE_ANOTHER'], 'alert');
				$fileInfo = $file->get();

				$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'file', NULL, NULL, $fileID.'-file', 'pname', $fileInfo['PNAME']);
				break;
			}

			$file->setPName($value);
			$newFileInfo = $file->get();

			$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'file', NULL, NULL, $fileID.'-file', 'pname', $newFileInfo['PNAME']);
			$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'OBJECT_CHANGE', sGuiUS(), 'filefolder', NULL, NULL, $fileID.'-file', 'pname', $newFileInfo['PNAME']);
			$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'OBJECT_CHANGEPNAME', sGuiUS(), 'file', NULL, NULL, $fileID.'-file', 'name', $newFileInfo['PNAME']);
			$jsQueue->add ($fileID, HISTORYTYPE_FILE, 'REFRESH_WINDOW', sGuiUS(), 'pname');

			// Add to history
			$file->history->add( HISTORYTYPE_FILE, NULL, $value, 'TXT_FILE_H_PNAME' );
			break;

		case 'refreshFileVersionDetails':

				$id = $this->params['id'];
				$version = $this->params['version'];
				$view = $this->params['view'];
				$win = $this->params['win'];
				$zoom = $this->params['zoom'];
				$fullscreen = $this->params['fullscreen'];

				$file = sFileMgr()->getFile($id, $version);
				$views = $file->views->getAssigned();
				$url = $file->getUrl();

				$viewid = false;
				if ($view != "") {
					for ($i = 0; $i < count($views); $i++) {
						if ($views[$i]["IDENTIFIER"] == $view) {
							$viewid = $views[$i]["ID"];
						}
					}
				}
				if ($viewid == false) $viewid = $views[0]["ID"];

				for ($i = 0; $i < count($views); $i++) {
					$viewinfo = $file->views->getGeneratedViewInfo($views[$i]["ID"]);
					$views[$i]["VIEWTYPE"] = $viewinfo[0]["TYPE"];
					if ($views[$i]["IDENTIFIER"] == "YGSOURCE") {
						$views[$i]["WIDTH"] = $viewinfo[0]["WIDTH"];
						$views[$i]["HEIGHT"] = $viewinfo[0]["HEIGHT"];
					}
					if ($views[$i]["ID"] == $viewid) {
						$views[$i]["SEL"] = true;
					}
				}

				$koala->callJSFunction( '$K.yg_updateFilePreview', $id, $version, json_encode($views), $zoom, $win, $fullscreen, false, $url );
				break;

			case 'refreshFileViewDetails':

				$id = $this->params['id'];
				$win = $this->params['win'];
				$file = sFileMgr()->getFile($id);
				$latestVersion = $file->getLatestApprovedVersion();
				$file = sFileMgr()->getFile($id, $latestVersion);
				$views = $file->views->getAssigned();

				for ($i = 0; $i < count($views); $i++) {
					if ($views[$i]['IDENTIFIER'] != 'YGSOURCE') {
						$viewinfo = $file->views->getGeneratedViewInfo($views[$i]["ID"]);
						$views[$i]["WIDTH"] = $viewinfo[0]["WIDTH"];
						$views[$i]["HEIGHT"] = $viewinfo[0]["HEIGHT"];
						$views[$i]["VIEWTYPE"] = $viewinfo[0]["TYPE"];
					}
				}

				$koala->callJSFunction( '$K.yg_updateFileViewDetails', json_encode($views), $win);
				break;

			case 'cropFile':

				$id = $this->params['id'];
				$version = $this->params['version'];
				$view = $this->params['view'];
				$win = $this->params['win'];
				$zoom = $this->params['zoom'] / 100;
				$x1 = $this->params['x1'] / $zoom;
				$y1 = $this->params['y1'] / $zoom;
				$x2 = $this->params['x2'] / $zoom;
				$y2 = $this->params['y2'] / $zoom;

				$file = sFileMgr()->getFile($id, $version);
				$url = $file->getUrl();
				$fileinfo = $file->get();
				$filedir = getrealpath(getcwd()."/".sConfig()->getVar('CONFIG/DIRECTORIES/FILESDIR'))."/";
				$filename = $fileinfo['FILENAME'];
				$views = $file->views->getAssigned(true);

				for ($i = 0; $i < count($views); $i++) {
					if ($views[$i]["IDENTIFIER"] == $view) {
						$width = $views[$i]["WIDTH"];
						$height = $views[$i]["HEIGHT"];
						$constrainwidth = $views[$i]["CONSTRAINWIDTH"];
						$constrainheight = $views[$i]["CONSTRAINHEIGHT"];
					}
				}

				$procs = sApp()->files_procs;

				$fileproc = $file->filetypes->getProcessor($fileinfo['FILETYPE']);

				$procPathInternal = getcwd()."/".sConfig()->getVar("CONFIG/DIRECTORIES/FILES_PROCS");
				$procPath = getcwd()."/".sConfig()->getVar("CONFIG/DIRECTORIES/PROCESSORSDIR");

				for ($p = 0; $p < count($procs); $p++) {
					if ($procs[$p]["name"] == $fileproc) {
						if (file_exists(getrealpath($procPathInternal.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php"))) {
							require_once(getrealpath($procPathInternal.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php"));
						} elseif (getrealpath($procPath.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php")) {
							require_once(getrealpath($procPath.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php"));
						} else {
							continue;
						}
						$classname = (string)$procs[$p]["classname"];
						$namespace = (string)$procs[$p]["namespace"];
						if (strlen($namespace)) {
							$classname = $namespace."\\".$classname;
						}
						$moduleclass = new $classname();

						$filepath = $filedir.$fileinfo['OBJECTID'].'-'.$fileinfo['VIEWVERSION'].$filename;

						// copy file
						$tmpdir = sConfig()->getVar("CONFIG/PATH/TMP");
						if (!$tmpdir) {
							$tmpdir = sys_get_temp_dir();
						}
						$filetmpname = tempnam($tmpdir, "crop");

						copy($filepath, $filetmpname);

						// crop file
						$moduleclass->cropFile($filetmpname, $x1, $y1, ($x2-$x1), ($y2-$y1));

						for ($i = 0; $i < count($views); $i++) {
							if ($views[$i]["IDENTIFIER"] == $view) {
								$viewinfo = $views[$i];
							}
						}

						if ($view == "YGSOURCE") {
							// new version
							$version = $file->updateFile($fileinfo["FILENAME"], $fileinfo["FILETYPE"], $filetmpname, false);
							$file = sFileMgr()->getFile($id, $version);
							$file->views->copyTo($id, ((int)$version-1), $id, $version, true);
							$fileinfo = $file->get();
							$views = $file->views->getAssigned();
							$versions = $file->getVersions();
							$finalVersions = array();
							foreach ($versions as $version_item) {
								if ($version_item['APPROVED']) {
									array_push($finalVersions, $version_item);
								}
							}
							$versions = $finalVersions;
							$timestamp = $versions[0]["CHANGEDTS"];
							$format = $itext["DATETIME_FORMAT"];
							$versions[0]["TITLE"] = "V".$versions[0]["VERSION"]." ".$itext['TXT_VERSION_FROM']." ".date($format, $timestamp);
							$file->history->add(HISTORYTYPE_FILE, NULL, $itext['TXT_SOURCEFILE'], 'TXT_FILE_H_CROP');
							$fileinfo['REFS'] = $reftracker->getIncomingForFile($fileinfo['OBJECTID']);

							$tags = $file->tags->getAssigned();
							for ($t = 0; $t < count($tags); $t++) {
								$tp = array();
								$tp = $file->tags->tree->getParents($tags[$t]['ID']);
								$tp2 = array();
								for ($p = 0; $p < count($tp); $p++) {
									$tinfo = $file->tags->get($tp[$p]);
									$tp2[$p]['ID'] = $tinfo['ID'];
									$tp2[$p]['NAME'] = $tinfo['NAME'];
								}
								$tp2[count($tp2)-1]['NAME'] = ($itext['TXT_TAGS']!='')?($itext['TXT_TAGS']):('$TXT_TAGS');
								$tags[$t]['PARENTS'] = $tp2;
							}
							$fileinfo['TAGS'] = $tags;
							$jsQueue->add ($id, HISTORYTYPE_FILE, 'OBJECT_DELETE', sGuiUS(), 'file', NULL, NULL, $id.'-file', 'name');
							$jsQueue->add ($id, HISTORYTYPE_FILE, 'ADD_FILE', sGuiUS(), NULL);
							$jsQueue->add ($id, HISTORYTYPE_FILE, 'CLEAR_REFRESH', sGuiUS(), 'file');
						} else {
							// replace view
							$version = (int)$version;
							$versions = false;
							$zoom = $zoom * 100;
							$file = sFileMgr()->getFile($id, $version);
							if ($file) {
								$views = $file->views->getAssigned();
								for ($i = 0; $i < count($views); $i++) {
									if ($views[$i]["IDENTIFIER"] == $view) {
										$viewinfo = $views[$i];
									}
								}
								$params = array();
								$params["FILEINFO"] = $fileinfo;
								$params["VIEW"] = $viewinfo;
								$params["FROMTMPFILE"] = $filetmpname;
								//copy($filetmpname, $filedir.$viewinfo["IDENTIFIER"].$fileinfo['OBJECTID'].'-'.$fileinfo['VIEWVERSION'].$filename);
								$moduleclass->process($fileinfo['OBJECTID'], $params);
								//$info = $moduleclass->generateThumbnail($fileinfo['OBJECTID'].'-'.$fileinfo['VIEWVERSION'].$fileinfo['FILENAME'], 0, $viewinfo['IDENTIFIER'], $viewinfo['WIDTH'], $viewinfo['HEIGHT'], $filedir, $filetmpname, $viewinfo['CONSTRAINWIDTH'], $viewinfo['CONSTRAINHEIGHT'], $viewinfo['WIDTHCROP'], $viewinfo['HEIGHTCROP'] );
								//$file->views->addGenerated($viewinfo["ID"], $info["WIDTH"], $info["HEIGHT"], FILE_TYPE_WEBIMAGE);

								$file->history->add( HISTORYTYPE_FILE, NULL, $viewinfo['NAME'], 'TXT_FILE_H_CROP' );
							}
						}

						for ($i = 0; $i < count($views); $i++) {
							$viewinfo = $file->views->getGeneratedViewInfo($views[$i]["ID"]);
							$views[$i]["VIEWTYPE"] = $viewinfo[0]["TYPE"];
							if ($views[$i]["IDENTIFIER"] == "YGSOURCE") {
								$views[$i]["WIDTH"] = $viewinfo[0]["WIDTH"];
								$views[$i]["HEIGHT"] = $viewinfo[0]["HEIGHT"];
							}
							if ($views[$i]["IDENTIFIER"] == $view) {
								$views[$i]["SEL"] = true;
							}
						}

						$koala->callJSFunction( '$K.yg_updateFilePreview', $id, $version, json_encode($views), $zoom, $win, true, json_encode($versions), $url );

					}
				}
				break;

	}

?>