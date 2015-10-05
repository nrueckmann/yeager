<?php

	$jsQueue = new JSQueue(NULL);
	$templateMgr = new Templates();
	$tagMgr = new Tags();

	switch ($action) {

		case 'deleteTemplate':
			$template = $this->params['template'];
			$confirmed = $this->params['confirmed'];
			$positive = $this->params['positive'];

			// Check if template is still used
			$stillInUse = false;
			$sites = sSites()->getList();
			foreach($sites as $curr_site) {
				$pageMgr = new PageMgr($curr_site['ID']);
				$pages = $pageMgr->getPagesByTemplate($template);
				if (count($pages)>0) {
					$stillInUse = true;
				}
			}
			$mailings = sMailingMgr()->getMailingsByTemplate($template);
			if (count($pages)>0) {
				$stillInUse = true;
			}
			$tmpTemplateInfo = $templateMgr->getTemplate($template);

			if ($stillInUse) {
				// Still used!
				if ($confirmed != 'true') {
					$parameters = array(
						'template'	=> $template
					);
					$koala->callJSFunction( 'Koala.yg_confirm',
						($itext['TXT_DELETE_USED_TEMPLATE_TITLE']!='')?($itext['TXT_DELETE_USED_TEMPLATE_TITLE']):('$TXT_DELETE_USED_TEMPLATE_TITLE'),
						($itext['TXT_DELETE_USED_TEMPLATE']!='')?($itext['TXT_DELETE_USED_TEMPLATE']):('$TXT_DELETE_USED_TEMPLATE'),
						$action, json_encode($parameters)
					);
				} else if (($confirmed == 'true') && ($positive == 'true')) {
					$successfullyDeleted = $templateMgr->remove($template);
					if (in_array($template, $successfullyDeleted)) {
						if ($tmpTemplateInfo['FOLDER']) {
							$jsQueue->add ($template, HISTORYTYPE_TEMPLATE, 'OBJECT_DELETE', sGuiUS(), 'templatefolder', NULL, NULL, $template.'-template', 'name');
						} else {
							$jsQueue->add ($template, HISTORYTYPE_TEMPLATE, 'OBJECT_DELETE', sGuiUS(), 'template', NULL, NULL, $template.'-template', 'name');
						}
					}
				}
			} else {
				// Not used, delete template
				$successfullyDeleted = $templateMgr->remove($template);
				if (in_array($template, $successfullyDeleted)) {
					if ($tmpTemplateInfo['FOLDER']) {
						$jsQueue->add ($template, HISTORYTYPE_TEMPLATE, 'OBJECT_DELETE', sGuiUS(), 'templatefolder', NULL, NULL, $template.'-template', 'name');
					} else {
						$jsQueue->add ($template, HISTORYTYPE_TEMPLATE, 'OBJECT_DELETE', sGuiUS(), 'template', NULL, NULL, $template.'-template', 'name');
					}
				}
			}
			break;

		case 'addTemplate':
			$template = $this->params['template'];

			// Check if root node is selected
			if ($template==='root') {
				// Get real Page-ID of Root-Node
				$templates = $templateMgr->getList();
				$template = $templates[0]['ID'];
			}

			// Check if a folder is selected
			$templateInfo = $templateMgr->getTemplate( $template );
			if ($templateInfo['FOLDER']==1) {
				// Add new child node
				$new_id = $templateMgr->add( $template );
				$templateMgr->setName( $new_id, $itext['TXT_NEW_OBJECT'] );
				//$templateMgr->setIdentifier( $new_id, $itext['TXT_NEW_OBJECT'] );

				if ( $new_id != false ) {
					$icons = new Icons();
					if ($template == 1) {
						// Rootfolder
						$koala->callJSFunction( 'Koala.yg_addChild', 'template', $template.'-template', 'name', $itext['TXT_NEW_OBJECT'], 'template', $new_id.'-template', 'name', $icons->icon['template_small'], 'nosub', true );
					} else {
						// Other folder
						$koala->callJSFunction( 'Koala.yg_addChild', 'templatefolder', $template.'-template', 'name', $itext['TXT_NEW_OBJECT'], 'template', $new_id.'-template', 'name', $icons->icon['template_small'], 'nosub', true );
					}
				} else {
					$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
				}
			}
			break;

		case 'addTemplateChildFolder':
			$template = $this->params['template'];

			// Check if root node is selected
			if ($template==='root') {
				// Get real Page-ID of Root-Node
				$templates = $templateMgr->getList();
				$template = $templates[0]['ID'];
			}

			// Add new child node
			$new_id = $templateMgr->add( $template, 1 );
			$templateMgr->setName( $new_id, $itext['TXT_NEW_OBJECT'] );

			if ( $new_id != false ) {
				$icons = new Icons();
				if ($template == 1) {
					// Rootfolder
					$koala->callJSFunction( 'Koala.yg_addChild', 'template', $template.'-template', 'name', $itext['TXT_NEW_OBJECT'], 'templatefolder', $new_id.'-template', 'name', $icons->icon['folder'], '', true );

				} else {
					$koala->callJSFunction( 'Koala.yg_addChild', 'templatefolder', $template.'-template', 'name', $itext['TXT_NEW_OBJECT'], 'templatefolder', $new_id.'-template', 'name', $icons->icon['folder'], '', true );
				}

			} else {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			}
			break;

		case 'moveTemplate':
			$source = $this->params['source'];
			$target = $this->params['target'];
			$parentwindow = $this->params['openerRef'];
			$before = $this->params['before'];
			$confirmed = $this->params['confirmed'];
			$positive = $this->params['positive'];

			if ($source == $target) {
				break;
			}

			if ($templateMgr->tree->moveTo($source, $target)) {
				$parent_ids = array();
				$parents = $tagMgr->getParents($source);
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

		case 'uploadTemplate':
			$filetype = $this->params['type'];
			$filetitle = $this->params['title'];
			if ($_FILES['Filedata']['tmp_name']) {
				$fileTmpName = $_FILES['Filedata']['tmp_name'];
				$filename = basename($_FILES['Filedata']['name']);
			} else {
				$fileTmpName = fixAndMovePLUploads();
				$filename = basename($_REQUEST['name']);
			}
			$filesize = filesize($fileTmpName);
			$uploadID = $this->params['uploadID'];
			$templateid = $this->params['fileID'];
			$templatedir = $templateMgr->getDir();
			$window_id = $this->params['winID'];
			$timestamp = (int)$this->params['timestamp'];

			// Output something (needed for Flash-Player Mac)
			echo " ";
			while (@ob_end_flush());

			$alreadyExists = false;
			if (file_exists($templatedir.$filename)) {
				$templateInfo = $templateMgr->getTemplate($templateid);
				if ($filename != $templateInfo['FILENAME']) {
					$alreadyExists = true;
				}
			}

			if (!file_exists($templatedir) || !is_writable($templatedir) || $alreadyExists ) {
				$koala->queueScript( "window.hadUploadError = true;" );
				$koala->queueScript( "window.hadUploadErrorMsg = '".$itext['TXT_NO_TEMPLATEFOLDER_OR_ACCESS_DENIED']."';" );
			} else {
				if (($fileTmpName != '') && ($fileTmpName != 'none')) {
					$koala->queueScript( "window.hadUploadError = false;window.hadUploadErrorMsg = undefined;" );

					$koala->queueScript( "Koala.yg_hilite('template', Koala.windows['wid_".$window_id."'].yg_id, 'name', true);" );

					copy($fileTmpName, $templatedir.'TMP_'.$templateid.'-'.time().'-'.$filename);

					$old_template_files = array();
					$template_files = glob($templatedir.'TMP_'.$templateid.'-*');

					// Remove all entries in array before submitted timestamp
					foreach($template_files as $template_file) {
						$tmp_filename = str_replace( 'TMP_'.$templateid.'-', '', basename($template_file) );
						$filetimestamp = explode( '-', $tmp_filename );
						$filetimestamp = (int)$filetimestamp[0];
						if ($filetimestamp > $timestamp) {
							array_push( $old_template_files, $template_file );
						}
					}

					// Check for added/removed contentareas
					$filecontentareas = $templateMgr->getContentareasFromTempFile( $old_template_files[count($old_template_files)-1] );
					if (count($old_template_files)>1) {
						$contentareas = $templateMgr->getContentareasFromTempFile( $old_template_files[count($old_template_files)-2] );
					} else {
						$contentareas = $templateMgr->getContentareas($templateid);
					}

					$a = 0;
					$r = 0;
					$afilecontentareas = array();
					$rfilecontentareas = array();
					for ($s = 0; $s < count($filecontentareas); $s++) {
						$alreadythere = false;
						for ($s2 = 0; $s2 < count($contentareas); $s2++) {
							if ($filecontentareas[$s]['CODE'] == $contentareas[$s2]['CODE']) {
								$alreadythere = true;
							}
						}
						if ($alreadythere == false) {
							$afilecontentareas[$a]['CODE'] = $filecontentareas[$s]['CODE'];
							$a++;
						}
					}

					for ($s = 0; $s < count($contentareas); $s++) {
						$found = false;
						for ($s2 = 0; $s2 < count($filecontentareas); $s2++) {
							if ($filecontentareas[$s2]['CODE'] == $contentareas[$s]['CODE']) {
								$found = true;
							}
						}
						if ($found == false) {
							$rfilecontentareas[]['CODE'] = $contentareas[$s]['CODE'];
						}
					}

					// Check for added/removed navigations
					$filenavis = $templateMgr->getNavisFromTempFile( $old_template_files[count($old_template_files)-1] );
					if (count($old_template_files)>1) {
						$navis = $templateMgr->getNavisFromTempFile( $old_template_files[count($old_template_files)-2] );
					} else {
						$navis = $templateMgr->getNavis($templateid);
					}

					$a = 0;
					$r = 0;
					$afilenavis = array();
					$rfilenavis = array();
					for ($s = 0; $s < count($filenavis); $s++) {
						$alreadythere = false;
						for ($s2 = 0; $s2 < count($navis); $s2++) {
							if ($filenavis[$s]['CODE'] == $navis[$s2]['CODE']) {
								$alreadythere = true;
							}
						}
						if ($alreadythere == false) {
							$afilenavis[$a]['CODE'] = $filenavis[$s]['CODE'];
							$a++;
						}
					}

					for ($s = 0; $s < count($navis); $s++) {
						$found = false;
						for ($s2 = 0; $s2 < count($filenavis); $s2++) {
							if ($filenavis[$s2]['CODE'] == $navis[$s]['CODE']) {
								$found = true;
							}
						}
						if ($found == false) {
							$rfilenavis[]['CODE'] = $navis[$s]['CODE'];
						}
					}

					if ((count($rfilecontentareas)>0) || (count($rfilenavis)>0)) {
						$messagetext = '';

						if (count($rfilecontentareas)>0) {
							$rfilecontentarea_txt = array();
							foreach($rfilecontentareas as $rfilecontentarea) {
								array_push( $rfilecontentarea_txt, $rfilecontentarea['CODE'] );
							}
							$messagetext .= $itext['TXT_CONTENTAREAS_NOT_AVAILABLE'].'<br />';
							$messagetext .= '<strong>'.implode(', ', $rfilecontentarea_txt).'</strong>.';
						}
						if (count($rfilenavis)>0) {
							if (count($rfilecontentareas)>0) {
								$messagetext .= '<br /><br />';
							}
							$rfilenavi_txt = array();
							foreach($rfilenavis as $rfilenavi) {
								array_push( $rfilenavi_txt, $rfilenavi['CODE'] );
							}
							$messagetext .= $itext['TXT_NAVIGATIONS_NOT_AVAILABLE'].'<br />';
							$messagetext .= '<strong>'.implode(', ', $rfilenavi_txt).'</strong>.';
						}
						$koala->alert( $messagetext );
					}

					// Add new contentareas in frontend
					if (count($afilecontentareas)>0) {
						for ($i = 0; $i < count($afilecontentareas); $i++) {
							$koala->callJSFunction( 'Koala.yg_addTemplateContentareaField', $window_id, $afilecontentareas[$i]['CODE'] );
						}
					}
					// Add new navigations in frontend
					if (count($afilenavis)>0) {
						for ($i = 0; $i < count($afilenavis); $i++) {
							if ($i == 0) {
								$koala->callJSFunction( 'Koala.yg_addTemplateNavigationField', $window_id, $afilenavis[$i]['CODE'], true );
							} else {
								$koala->callJSFunction( 'Koala.yg_addTemplateNavigationField', $window_id, $afilenavis[$i]['CODE'] );
							}
						}
					}
					// Remove old contentareas in frontend
					if (count($rfilecontentareas)>0) {
						for ($i = 0; $i < count($rfilecontentareas); $i++) {
							$koala->callJSFunction( 'Koala.yg_removeTemplateContentareaField', $window_id, $rfilecontentareas[$i]['CODE'] );
						}
					}
					// Remove old navigations in frontend
					if (count($rfilenavis)>0) {
						for ($i = 0; $i < count($rfilenavis); $i++) {
							$koala->callJSFunction( 'Koala.yg_removeTemplateNavigationField', $window_id, $rfilenavis[$i]['CODE'] );
						}
					}

					$koala->queueScript( "Koala.yg_setTemplateFileName( '".$window_id."', '".$filename."' );" );
					$koala->queueScript( "window.noprocessing = true;" );

					if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/TEMPLATE_CHANGE") == "true") Singleton::FC()->emptyBucket();

				}
			}
			break;

		case 'uploadTemplatePreview':
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
			$templateid = $this->params['fileID'];
			$frontend_previewdir = sConfig()->getVar( 'CONFIG/DIRECTORIES/TEMPLATEPREVIEWDIR' );
			$templatepreviewdir = getRealpath( $frontend_previewdir ).'/';
			$window_id = $this->params['winID'];

			if (!file_exists($templatepreviewdir) || !is_writable($templatepreviewdir) ) {
				$koala->queueScript( "window.hadUploadError = true;window.hadUploadErrorMsg = '".$itext['TXT_NO_TEMPLATEPREVIEWFOLDER_OR_ACCESS_DENIED']."';" );
			} else {
				if (($fileTmpName != '') && ($fileTmpName != 'none')) {
					$koala->queueScript( "window.hadUploadError = false;window.hadUploadErrorMsg = undefined;" );
					$extension = explode('.', $filename);

					// Delete old files
					$old_template_files = glob($templatepreviewdir.'TMP_'.$templateid.'-*');
					foreach($old_template_files as $old_template_file) {
						unlink( $old_template_file );
					}

					copy($fileTmpName, $templatepreviewdir.'TMP_'.$templateid.'-'.$filename);
					$koala->queueScript( "Koala.yg_setTemplatePreviewPicture( '".$window_id."', '".$templateid.'-'.$filename."', '".$frontend_previewdir.'TMP_'.$templateid."-".$filename."' );" );
					$koala->queueScript( "Koala.yg_hilite('template', Koala.windows['wid_".$window_id."'].yg_id, 'name', true);" );
					$koala->queueScript( "window.noprocessing = true;" );
				}
			}
			break;

		case 'saveTemplateInfo':

			$objectID = $this->params['objectID'];
			$template_name = $this->params['template_name'];
			$template_identifier = $this->params['template_identifier'];
			$removepreview = $this->params['removepreview'];
			$previewchanged = $this->params['previewchanged'];
			$templatechanged = $this->params['templatechanged'];

			$timestamp = $this->params['timestamp'];

			$description = $this->params['description'];
			$wid = $this->params['wid'];
			$default_navigation = $this->params['default_navigation'];

			$templatedir = $templateMgr->getDir();
			$templatepreviewdir = getRealpath( sConfig()->getVar( 'CONFIG/DIRECTORIES/TEMPLATEPREVIEWDIR' ) ).'/';

			$found_temp_template_files = array();
			$temp_template_files = glob($templatedir.'TMP_'.$objectID.'-*');

			// Remove all entries in array before submitted timestamp
			foreach($temp_template_files as $template_file) {
				$tmp_filename = str_replace( 'TMP_'.$objectID.'-', '', basename($template_file) );
				$filetimestamp = explode( '-', $tmp_filename );
				$filetimestamp = (int)$filetimestamp[0];
				if ($filetimestamp > $timestamp) {
					array_push( $found_temp_template_files, $template_file );
				}
			}

			$found_temp_preview_files = glob($templatepreviewdir.'TMP_'.$objectID.'-*');

			if ($removepreview) {
				// Remove preview
				$old_preview_files = glob($templatepreviewdir.$objectID.'-*');
				foreach($old_preview_files as $old_preview_file) {
					unlink( $old_preview_file );
				}
			}

			if ((count($found_temp_template_files)>0) && ($templatechanged)) {
				// Delete old files
				$old_template_files = glob($templatedir.$objectID.'-*');
				foreach($old_template_files as $old_template_file) {
					unlink( $old_template_file );
				}
				// Rename temp file to real filename
				$real_filename = basename(str_replace('TMP_'.$objectID.'-', '', $found_temp_template_files[count($found_temp_template_files)-1]));
				$real_filename = explode( '-', $real_filename );
				array_shift( $real_filename );
				$real_filename = implode( '', $real_filename );
				rename( $found_temp_template_files[count($found_temp_template_files)-1], $templatedir.$real_filename );

				// Get new file mode to set from configfile (and set it)
				$newFileMode = octdec(sConfig()->getVar( 'CONFIG/UPLOAD_PERMISSIONS' ));
				if ($newFileMode) {
					chmod( $templatedir.$real_filename, $newFileMode );
				}

				$found_temp_template_files_to_del = glob($templatedir.'TMP_'.$objectID.'-*');
				foreach($found_temp_template_files_to_del as $found_temp_template_file_to_del) {
					unlink( $found_temp_template_file_to_del );
				}

				$templateMgr->setPath($objectID, '');
				$templateMgr->setFilename($objectID, basename($real_filename));

				// Check for added/removed contentareas
				$filecontentareas = $templateMgr->getContentareasFromFile($objectID);
				$contentareas = $templateMgr->getContentareas($objectID);
				$a = 0;
				$r = 0;
				$afilecontentareas = array();
				$rfilecontentareas = array();
				for ($s = 0; $s < count($filecontentareas); $s++) {
					$alreadythere = false;
					for ($s2 = 0; $s2 < count($contentareas); $s2++) {
						if ($filecontentareas[$s]['CODE'] == $contentareas[$s2]['CODE']) {
							$alreadythere = true;
						}
					}
					if ($alreadythere == false) {
						$afilecontentareas[$a]['CODE'] = $filecontentareas[$s]['CODE'];
						$a++;
					}
				}

				for ($s = 0; $s < count($contentareas); $s++) {
					$found = false;
					for ($s2 = 0; $s2 < count($filecontentareas); $s2++) {
						if ($filecontentareas[$s2]['CODE'] == $contentareas[$s]['CODE']) {
							$found = true;
						}
					}
					if ($found == false) {
						$rfilecontentareas[]['CODE'] = $contentareas[$s]['CODE'];
					}
				}

				// Check for added/removed navigations
				$filenavis = $templateMgr->getNavisFromFile($objectID);
				$navis = $templateMgr->getNavis($objectID);
				$a = 0;
				$r = 0;
				$afilenavis = array();
				$rfilenavis = array();
				for ($s = 0; $s < count($filenavis); $s++) {
					$alreadythere = false;
					for ($s2 = 0; $s2 < count($navis); $s2++) {
						if ($filenavis[$s]['CODE'] == $navis[$s2]['CODE']) {
							$alreadythere = true;
						}
					}
					if ($alreadythere == false) {
						$afilenavis[$a]['CODE'] = $filenavis[$s]['CODE'];
						$a++;
					}
				}
				for ($s = 0; $s < count($navis); $s++) {
					$found = false;
					for ($s2 = 0; $s2 < count($filenavis); $s2++) {
						if ($filenavis[$s2]['CODE'] == $navis[$s]['CODE']) {
							$found = true;
						}
					}
					if ($found == false) {
						$rfilenavis[]['CODE'] = $navis[$s]['CODE'];
					}
				}

				// Add new contentareas
				if (count($afilecontentareas)>0) {
					for ($i = 0; $i < count($afilecontentareas); $i++) {
						$templateMgr->addContentarea($objectID, $afilecontentareas[$i]['CODE']);
					}
				}
				// Add new navigations
				if (count($afilenavis)>0) {
					for ($i = 0; $i < count($afilenavis); $i++) {
						$templateMgr->addNavi($objectID, $afilenavis[$i]['CODE']);
					}
				}
				// Remove old contentareas
				if (count($rfilecontentareas)>0) {
					for ($i = 0; $i < count($rfilecontentareas); $i++) {
						$templateMgr->removeContentarea($objectID, $rfilecontentareas[$i]['CODE']);
					}
				}
				// Remove old navigations
				if (count($rfilenavis)>0) {
					for ($i = 0; $i < count($rfilenavis); $i++) {
						$templateMgr->removeNavi($objectID, $rfilenavis[$i]['CODE']);
					}
				}

			}
			if ((count($found_temp_preview_files)>0) && ($previewchanged)) {
				// Delete old files
				$old_preview_files = glob($templatepreviewdir.$objectID.'-*');
				foreach($old_preview_files as $old_preview_file) {
					unlink( $old_preview_file );
				}
				// Rename temp file to real filename
				rename( $found_temp_preview_files[0], str_replace('TMP_','',$found_temp_preview_files[0]) );
			}

			// Set the new name & identifier
			if ( $templateMgr->setName( $objectID, $template_name ) === false ) {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			} else {
				$templateMgr->setIdentifier($objectID, $template_identifier);
				$jsQueue->add ($objectID, HISTORYTYPE_TEMPLATE, 'OBJECT_CHANGE', sGuiUS(), 'template', NULL, NULL, $objectID.'-template', 'name', $template_name);
				$jsQueue->add ($objectID, HISTORYTYPE_TEMPLATE, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $objectID.'-template', 'name', $template_name);
				$jsQueue->add ($objectID, HISTORYTYPE_TEMPLATE, 'REFRESH_WINDOW', sGuiUS(), 'name');
			}

			// Set the new description
			if ( $templateMgr->setDescription($objectID, $description) === false ) {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			}

			// Set contentarea names & order
			$contentareas = $templateMgr->getContentareas( $objectID );
			$contentareasOrder = array();
			foreach($this->params as $paramIdx => $paramItem) {
				foreach($contentareas as $contentarea) {
					$paramname = 'contentarea_'.strtolower($contentarea['CODE']).'_name';
					if ($paramIdx == $paramname) {
						$templateMgr->setContentareaName($objectID, $contentarea['CODE'], $this->params[$paramname]);
						$contentareasOrder[] = $contentarea['ID'];
					}
				}
			}
			$templateMgr->setContentareasOrder($objectID, $contentareasOrder);

			// Set navigation names
			$navigations = $templateMgr->getNavis( $objectID );
			foreach($navigations as $navigation) {
				$paramname = 'navigation_'.strtolower($navigation['CODE']).'_name';
				if ($this->params[$paramname]) {
					$templateMgr->setNaviName($objectID, $navigation['CODE'], $this->params[$paramname]);
				}
			}
			if (!$default_navigation) {
				$default_navigation = $navigations[0]['CODE'];
			}
			// Set default navigation
			$templateMgr->setDefaultNavi($objectID, $default_navigation);

			// Fade all green fields
			$koala->queueScript( "Koala.yg_fadeFields(\$('".$wid."'), '.changed');" );
			$jsQueue->add ($objectID, HISTORYTYPE_TEMPLATE, 'UNHIGHLIGHT_TEMPLATE', sGuiUS(), 'name');

			$koala->queueScript( 'Koala.windows[\''.$wid.'\'].tabs.select(0,Koala.windows[\''.$wid.'\'].tabs.params);' );
			break;

		case 'setTemplateName':

			// Cannot use new style of parameters here (function is called by a custom-attribute event - and therefore does not know about named parameters)
			// Split PageID and SiteID
			$data = explode('-', $this->reponsedata['name']->yg_id );

			// Get Template information
			$templateInfo = $templateMgr->getTemplate($data[0]);

			// Set the new name
			if ( $templateMgr->setName( $data[0], $this->reponsedata['name']->value ) === false ) {
				$koala->alert( $itext['TXT_ERROR_ACCESS_DENIED'] );
			} else {
				if (strlen(trim($templateInfo['IDENTIFIER'])) == 0) {
					$PName = $templateMgr->calcIdentifier($templateId, '', $this->reponsedata['name']->value);
					$jsQueue->add ($data[0], HISTORYTYPE_TEMPLATE, 'OBJECT_CHANGE', sGuiUS(), 'template', NULL, NULL, $this->reponsedata['name']->yg_id, 'identifier', $PName);
				}

				$jsQueue->add ($data[0], HISTORYTYPE_TEMPLATE, 'OBJECT_CHANGE', sGuiUS(), 'template', NULL, NULL, $this->reponsedata['name']->yg_id, 'name', $this->reponsedata['name']->value);
				$jsQueue->add ($data[0], HISTORYTYPE_TEMPLATE, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $this->reponsedata['name']->yg_id, 'name', $this->reponsedata['name']->value);
				$jsQueue->add ($data[0], HISTORYTYPE_TEMPLATE, 'REFRESH_WINDOW', sGuiUS(), 'name');
			}
			break;

		case 'templateSelectNode':

			$node = $this->params['node'];
			$wid = $this->params['wid'];

			$root_node = $templateMgr->getTree(NULL, 0);

			// Template

			// 1 = rsub
			// 2 = rread
			// 3 = rdelete
			// 4 = parent -> rsub & rwrite
			// 5 = parent -> rsub & rwrite
			// 6 = rdelete
			$buttons = array();

			$templateInfo = $templateMgr->getTemplate( $node );

			$rread = $rwrite = $rdelete = $prsub = $prwrite = true;
			$rsub = ($templateInfo['FOLDER'] == 1);

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

		case 'templateCalcPname':

			// Get window-ID
			$winID = $data[1]['winID'];

			// Split ObjectID and SiteID
			$templateId = explode('-', $this->reponsedata['name']->yg_id);
			$templateId = $templateId[0];

			$templateInfo = $templateMgr->getTemplate($templateId);

			if (strlen(trim($templateInfo['IDENTIFIER'])) == 0) {
				$PName = $templateMgr->calcIdentifier($templateId, '', $this->reponsedata['name']->value);
				$koala->queueScript( '$(\''.$winID.'_template_identifier\').value=\''.$PName.'\';Koala.yg_setEdited( $(\''.$winID.'_template_identifier\') );' );
			}
			break;

	}

?>