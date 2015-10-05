<?php

	\framework\import('org.phpmailer.phpmailer');

	$jsQueue = new JSQueue(NULL);
	$templateMgr = new Templates();
	$siteMgr = new Sites();

	switch ($action) {

			case 'addSite':
				$wid = $this->params['wid'];
				$newSiteId = $siteMgr->add(($itext['TXT_NEW_OBJECT'])?($itext['TXT_NEW_OBJECT']):('$TXT_NEW_OBJECT'));

				// Set permissions for newly created site (but only for roles with "RSITES" privilege)
				$pageMgr = new PageMgr($newSiteId);

				$roles = sUsergroups()->getList();

				$pagesList = $pageMgr->getList(0, array(), 1, $roleID);

				$koala->callJSFunction( 'Koala.yg_addSiteItem', $wid, $newSiteId, ($itext['TXT_NEW_OBJECT'])?($itext['TXT_NEW_OBJECT']):('$TXT_NEW_OBJECT') );
				break;

			case 'deleteSite':
				$wid = $this->params['wid'];
				$siteID = $this->params['siteID'];
				$winID = explode('_', $wid);
				$winID = $winID[1];

				$siteMgr->remove( $siteID );

				$koala->queueScript( 'if ($(\'sites_'.$winID.'_'.$siteID.'\')) $(\'sites_'.$winID.'_'.$siteID.'\').remove();' );
				$koala->queueScript( 'Koala.windows[\'wid_'.$winID.'\'].refresh();' );
				$koala->queueScript( '$(Koala.windows[\'wid_'.$winID.'\'].boundWindow).addClassName(\'boxghost\');' );
				$koala->queueScript( 'Koala.windows[Koala.windows[\'wid_'.$winID.'\'].boundWindow].init();' );

				$koala->callJSFunction( 'Koala.yg_disable', 'tree_btn_delete', 'btn-'.$winID, 'tree_btn' );
				break;

			case 'setSiteTemplateRoot':
				$siteID = $this->params['siteID'];
				$template = $this->params['templateId'];
				$parentwindow = $this->params['openerRefID'];

				// Check if we really got sane values
				if ($template!='template') {
					$templatePreview = '';
					$templateInfo = $templateMgr->getTemplate($template);
					$koala->callJSFunction( 'Koala.windows[\'wid_'.$parentwindow.'\'].refreshSiteTemplateRoot', $template, $templateInfo['NAME'] );
				}
				break;

			case 'setSiteTemplate':
				$siteID = $this->params['siteID'];
				$template = $this->params['templateId'];
				$parentwindow = $this->params['openerRefID'];

				// Check if we really got sane values
				if ($template!='template') {
					$templatePreview = '';
					$templateInfo = $templateMgr->getTemplate($template);
					$templateInfo['PREVIEWPATH'] = $templateMgr->getPreviewPath( $template );
					if ($templateInfo['FILE'] > 0) {
						$templatePreview = $templateInfo['PREVIEWPATH'];
					}
					$koala->callJSFunction( 'Koala.windows[\'wid_'.$parentwindow.'\'].refreshSiteTemplate', $templateInfo['NAME'], $templateInfo['FILENAME'], $template, $templatePreview );
				}
				break;

			case 'saveSiteInfo':
				$wid = $this->params['wid'];
				$objectID = $this->params['objectID'];
				$name = $this->params['site_name'];
				$pname = $this->params['site_pname'];
				$pname = $siteMgr->filterPName($pname);
				$favicon = $this->params['site_favicon'];

				$defaulttemplate = $this->params['site_defaulttemplate'];
				$templateroot = $this->params['site_templateroot'];

				// Check if PNAME is already used or in blacklist
				$siteList = $siteMgr->getList();
				$siteBlackList = explode(',', (string)sConfig()->getVar("CONFIG/RESERVED_SITENAMES"));
				$isError = false;
				$errorType = null;
				foreach($siteList as $siteListItem) {
					if (($pname != '') && ($siteListItem['PNAME'] == $pname) && ($objectID != $siteListItem['ID'])) {
						$isError = true;
						$errorType = 1;
						$koala->queueScript( "if ($('".$wid."_site_pname')) $('".$wid."_site_pname').addClassName('error');" );
					}
				}
				foreach($siteBlackList as $siteBlackListItem) {
					if (($pname != '') && (strtolower($siteBlackListItem) == strtolower($pname))) {
						$isError = true;
						$errorType = 2;
						$koala->queueScript( "if ($('".$wid."_site_pname')) $('".$wid."_site_pname').addClassName('error');" );
					}
				}
				if ($isError) {
					switch ($errorType) {
						case 1:
							$koala->alert( $itext['TXT_PNAME_ALREADY_USED'] );
							break;
						case 2:
							$koala->alert( $itext['TXT_PNAME_BLACKLISTED'] );
							break;
					}

					// Reset PName to old PName
					/*
					$oldPname = $siteMgr->getPName($objectID);
					$jsQueue->add ($objectID, HISTORYTYPE_SITE, 'OBJECT_CHANGE', sGuiUS(), 'site', NULL, NULL, $objectID.'-site', 'pname', $oldPname);
					*/
				} else {
					$siteMgr->setName($objectID, $name);
					$siteMgr->setPName($objectID, $pname);
					$siteMgr->setFavicon($objectID, $favicon);
					$siteMgr->setDefaultTemplate($objectID, $defaulttemplate);
					$siteMgr->setTemplateRoot($objectID, $templateroot);

					$jsQueue->add ($objectID, HISTORYTYPE_SITE, 'OBJECT_CHANGE', sGuiUS(), 'site', NULL, NULL, $objectID.'-site', 'name', $name);
					$jsQueue->add ($objectID, HISTORYTYPE_SITE, 'OBJECT_CHANGE', sGuiUS(), 'page', NULL, NULL, $objectID.'-site', 'name', $name);
					$jsQueue->add ($objectID, HISTORYTYPE_SITE, 'OBJECT_CHANGE', sGuiUS(), 'site', NULL, NULL, $objectID.'-site', 'pname', $pname);

					// Re-sort the list
					$koala->queueScript( 'if (Koala.windows[Koala.windows[\''.$wid.'\'].boundWindow].sortList) Koala.windows[Koala.windows[\''.$wid.'\'].boundWindow].sortList();' );

					$jsQueue->add ($objectID, HISTORYTYPE_SITE, 'REFRESH_WINDOW', sGuiUS(), 'name');

					// Fade all green fields
					$koala->queueScript( "Koala.yg_fadeFields(\$('".$wid."'), 'input.changed');" );

					$jsQueue->add ($objectID, HISTORYTYPE_SITE, 'UNHIGHLIGHT_SITE', sGuiUS(), 'name');
				}
				break;

			case 'siteSelectNode':
				$node = $this->params['node'];
				$wid = $this->params['wid'];

				$koala->callJSFunction( 'Koala.yg_enable', 'tree_btn_delete', 'btn-'.$wid, 'tree_btn' );
				break;

			case 'siteCalcPname':
				// Get window-ID
				$winID = $data[1]['winID'];

				// Split ObjectID and SiteID
				$siteId = explode('-', $this->reponsedata['name']->yg_id);
				$siteId = $siteId[0];

				$siteInfo = $siteMgr->get($siteId);
				if (strlen(trim($siteInfo['PNAME'])) == 0) {
					$PName = $siteMgr->calcPName($siteId, '', $this->reponsedata['name']->value);

					$koala->queueScript( '$(\''.$winID.'_site_pname\').value=\''.$PName.'\';Koala.yg_setEdited( $(\''.$winID.'_site_pname\') );' );
				}
				break;

	}

?>