<?php

	$ygid = $this->request->parameters['yg_id'];
	$refresh = $this->request->parameters['refresh'];
	$wid = $this->request->parameters['wid'];
	$objecttype = $this->request->parameters['yg_type'];
	$data = explode('-', $ygid);
	$icons = new Icons();

	$reftracker = new Reftracker();
	$siteMgr = new Sites();

	switch ($objecttype) {
		case 'mailing':

			$mailingMgr = new MailingMgr();
			$mailingID = $data[0];

			$mailing = $mailingMgr->getMailing($mailingID);
			$mailingInfo = $mailing->get();
			$mailingInfo['RSTAGE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $mailingID, 'RSTAGE');
			$nversion = $mailing->getLatestApprovedVersion();
			$oref = $reftracker->getOutgoingForMailing($mailingID, $nversion);
			$oc = 0;
			$outgoing = array();
			$refcohash = array();
			$reftargethash = array();

			for ($i = 0; $i < count($oref); $i++) {
				if (!(isset($reftargethash[$oref[$i]["CBID"].$oref[$i]["TGTOID"].$oref[$i]["TGTAID"]]))) {
					$cov = $oref[$i]["CBVERSION"];
					if ($cov == ALWAYS_LATEST_APPROVED_VERSION) $cov = 0;
					$cb = sCblockMgr()->getCblock($oref[$i]["CBID"], $cov);
					$cblockInfo = $cb->get();
					$hash = $cblockInfo["ID"];
					if (!(isset($refcohash[$hash]))) {
						$outgoing[$oc]["ID"] = $cblockInfo["ID"];
						$outgoing[$oc]["VIATYPE"] = $oref[$i]["SRCTYPE"];
						$outgoing[$oc]["VIANAME"] = $cblockInfo["NAME"];
						$outgoing[$oc]["EMBEDDED"] = $cblockInfo["EMBEDDED"];
						$cop = sCblockMgr()->getParents($oref[$i]["CBID"]);
						array_pop($cop);
						$outgoing[$oc]["PARENTS"] = $cop;
						$viacnt = 0;
						$refcohash[$hash] = $oc;
					} else {
						$viacnt = count($outgoing[$refcohash[$hash]]["VIATARGETS"]);
					}
					if ($oref[$i]["TGTTYPE"] == REFTYPE_PAGE) {
						$lThePageMgr = new PageMgr($oref[$i]["TGTAID"]);

						$siteInfo = $siteMgr->get($oref[$i]["TGTAID"]);
						if ($siteInfo['ID'] == $oref[$i]["TGTAID"]) {
							$pr = $lThePageMgr->getParents($oref[$i]["TGTOID"]);
							$pr[count($pr)-1][0]['NAME'] = $siteMgr->getname($oref[$i]["TGTAID"]);
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
							$refPage = $lThePageMgr->getPage($oref[$i]["TGTOID"]);
							if ($refPage) {
								$refPageVersion = $refPage->getPublishedVersion(true);
								//$refPage = $lThePageMgr->getPage($oref[$i]["TGTOID"], $refPageVersion);
								$refPage = $lThePageMgr->getPage($oref[$i]["TGTOID"]);
								$refinfo = $refPage->get();
								$refinfo['RWRITE'] = $refPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]["TGTOID"], "RWRITE");
								$refinfo['RDELETE'] = $refPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]["TGTOID"], "RWRITE");
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["SITEID"] = $oref[$i]["TGTAID"];
								$iconData = getIconForPage($refinfo);
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["ICON"] = $iconData['iconclass'];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["STYLE"] = $iconData['style'];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["HASCHANGED"] = $refinfo['HASCHANGED'];

								if ($refinfo["OBJECTID"] < 1) {
									$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["BAD"] = 1;
								} else {
									$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $refinfo["NAME"];
								}
								$skippage = false;
							}
						} else {
							$skippage = true;
						}
					}
					else if ($oref[$i]["TGTTYPE"] == REFTYPE_FILE) {
						$pr = sFileMgr()->getParents($oref[$i]["TGTOID"]);
						array_pop( $pr );
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
						$file = sFileMgr()->getFile($oref[$i]["TGTOID"]);
						if ($file) {
							$refinfo = $file->get();
							if ($refinfo["OBJECTID"] < 1) {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["BAD"] = 1;
							} else {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $refinfo["NAME"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["IDENTIFIER"] = $refinfo["IDENTIFIER"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["CODE"] = $refinfo["CODE"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["COLOR"] = $refinfo["COLOR"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["ID"] = $oref[$i]["TGTOID"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["SITE"] = 'file';
							}
						}
					}
					else if ($oref[$i]["TGTTYPE"] == REFTYPE_IMAGE) {
						$pr = sFileMgr()->getParents($oref[$i]["TGTOID"]);
						array_pop( $pr );
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
						$file = sFileMgr()->getFile($oref[$i]["TGTOID"]);
						if ($file) {
							$refinfo = $file->get();
							if ($refinfo["OBJECTID"] < 1) {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["BAD"] = 1;
							} else {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $refinfo["NAME"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["IDENTIFIER"] = $refinfo["IDENTIFIER"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["COLOR"] = $refinfo["COLOR"];
							}
						}
					}
					else if ($oref[$i]["TGTTYPE"] == REFTYPE_EMAIL) {
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $oref[$i]["TGTOID"];
					}
					else if ($oref[$i]["TGTTYPE"] == REFTYPE_EXTERNAL) {
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $oref[$i]["TGTOID"];
					}
					if (!$skippage) {
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["TARGETTYPE"] = $oref[$i]["TGTTYPE"];
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["ID"] = $oref[$i]["TGTOID"];
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["AID"] = $oref[$i]["TGTAID"];
						$reftargethash[$oref[$i]["CBID"].$oref[$i]["TGTOID"].$oref[$i]["TGTAID"]] = true;
					} else {
						$skippage = false;
					}
					$oc++;
				}
			}

			$mailing = $mailingMgr->getMailing($mailingID);
			$mailingInfo = $mailing->get();

			$mailingInfo['RWRITE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $mailingID, "RWRITE");
			$mailingInfo['RSTAGE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $mailingID, "RSTAGE");
			$mailingInfo['READONLY'] = !$mailingInfo['RWRITE'];

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $mailingMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				$currentObject = $mailingMgr->getMailing($lockedObject['OBJECTID']);
				$currentObject->releaseLock($lockedObject['TOKEN']);
			}

			// Check for lock, and lock if not locked
			$lockStatus = $mailing->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$mailing->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}
			if ($lockedFailed) {
				// Get user who locked this object
				$userWithLock = new User( $lockStatus['LOCKUID'] );
				$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
				$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
				$smarty->assign('lockedByUser', $lockedByUser );
				$mailingInfo['RWRITE'] = false;
				$mailingInfo['READONLY'] = true;
			}

			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$mailingInfo['RSTAGE'].'\' );');

			$smarty->assign("outgoing", array_values($outgoing));
			break;

		case 'cblock':
			$cblockID = $data[0];

			$cb = sCblockMgr()->getCblock($cblockID);
			$cversion = $cb->getLatestApprovedVersion();
			$cb = sCblockMgr()->getCblock($cblockID, $cversion);
			$oref = $reftracker->getOutgoingForCblock($cblockID, $cversion);

			$pageInfo['RSTAGE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $cblockID, "RSTAGE");

			$oc = 0;
			$outgoing = array();
			$refcohash = array();
			$reftargethash = array();

			for ($i = 0; $i < count($oref); $i++) {
				if (!(isset($reftargethash[$oref[$i]["CBID"].$oref[$i]["TGTOID"].$oref[$i]["TGTAID"]]))) {
					$cov = $oref[$i]["CBVERSION"];
					if ($cov == ALWAYS_LATEST_APPROVED_VERSION) $cov = 0;
					$ocb = sCblockMgr()->getCblock($oref[$i]["CBID"]);
					$cblockInfo = $ocb->get();
					$hash = $cblockInfo["ID"];
					if (!(isset($refcohash[$hash]))) {
						$outgoing[$oc]["ID"] = $cblockInfo["ID"];
						$outgoing[$oc]["VIATYPE"] = $oref[$i]["SRCTYPE"];
						$outgoing[$oc]["VIANAME"] = $cblockInfo["NAME"];
						$outgoing[$oc]['ID'] = $oref[$i]["CBID"];
						$cblockInfo['RWRITE'] = $ocb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]["CBID"], "RWRITE");
						$cblockInfo['RDELETE'] = $ocb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]["CBID"], "RDELETE");
						$styleData = getStyleForContentblock($cblockInfo, true);
						$outgoing[$oc]['STYLE'] = $styleData;
						$outgoing[$oc]['HASCHANGED'] = $cblockInfo['HASCHANGED'];
						$outgoing[$oc]["EMBEDDED"] = $cblockInfo["EMBEDDED"];
						$cop = sCblockMgr()->getParents($oref[$i]["CBID"]);
						array_pop( $cop );
						$outgoing[$oc]["PARENTS"] = $cop;
						$viacnt = 0;
						$refcohash[$hash] = $oc;
					} else {
						$viacnt = count($outgoing[$refcohash[$hash]]["VIATARGETS"]);
					}
					if ($oref[$i]["TGTTYPE"] == REFTYPE_CO) {
						$pr = sCblockMgr()->getParents($oref[$i]["TGTOID"]);
						array_pop( $pr );
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
						$tcb = sCblockMgr()->getCblock($oref[$i]["TGTOID"]);
						$refinfo = $tcb->get();
						if ($refinfo['ID'] < 1) {
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["BAD"] = 1;
						} else {
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $refinfo["NAME"];
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["ID"] = $oref[$i]["TGTOID"];
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["SITE"] = 'cblock';
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]['ID'] = $oref[$i]["TGTOID"];
						$refinfo['RWRITE'] = $tcb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]["TGTOID"], "RWRITE");
						$refinfo['RDELETE'] = $tcb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]["TGTOID"], "RDELETE");
							$styleData = getStyleForContentblock($refinfo, true);
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]['STYLE'] = $styleData;
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]['HASCHANGED'] = $cblockInfo['HASCHANGED'];
						}

					}
					else if ($oref[$i]["TGTTYPE"] == REFTYPE_PAGE) {
						$lThePageMgr = new PageMgr($oref[$i]["TGTAID"]);

						$siteInfo = $siteMgr->get($oref[$i]["TGTAID"]);
						if ($siteInfo['ID'] == $oref[$i]["TGTAID"]) {
						   	$pr = $lThePageMgr->getParents($oref[$i]["TGTOID"]);
							$pr[count($pr)-1][0]['NAME'] = $siteMgr->getname($oref[$i]["TGTAID"]);
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
							$refPage = $lThePageMgr->getPage($oref[$i]["TGTOID"]);
							if ($refPage) {
								$refinfo = $refPage->get();
								if ($refinfo["OBJECTID"] < 1) {
									$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["BAD"] = 1;
								} else {
									$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $refinfo["NAME"];
									$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["ID"] = $oref[$i]["TGTOID"];
									$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["SITE"] = $oref[$i]["TGTAID"];
									$refinfo['RWRITE'] = $refPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]["TGTOID"], "RWRITE");
									$refinfo['RDELETE'] = $refPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]["TGTOID"], "RDELETE");
									$iconData = getIconForPage($refinfo);
									$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]['ICON'] = $iconData['iconclass'];
									$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]['STYLE'] = $iconData['style'];
									$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]['HASCHANGED'] = $refinfo['HASCHANGED'];
								}
								$skippage = false;
							} else {
								$skippage = true;
							}
						} else {
							$skippage = true;
						}
					}
					else if ($oref[$i]["TGTTYPE"] == REFTYPE_FILE) {
						$pr = sFileMgr()->getParents($oref[$i]["TGTOID"]);
						array_pop( $pr );
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
						$file = sFileMgr()->getFile($oref[$i]["TGTOID"]);
						if ($file) {
							$refinfo = $file->get();
							if ($refinfo["OBJECTID"] < 1) {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["BAD"] = 1;
							} else {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $refinfo["NAME"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["IDENTIFIER"] = $refinfo["IDENTIFIER"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["CODE"] = $refinfo["CODE"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["COLOR"] = $refinfo["COLOR"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["ID"] = $oref[$i]["TGTOID"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["SITE"] = 'file';
							}
						}
					}
					else if ($oref[$i]["TGTTYPE"] == REFTYPE_IMAGE) {
						$pr = sFileMgr()->getParents($oref[$i]["TGTOID"]);
						array_pop( $pr );
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
						$file = sFileMgr()->getFile($oref[$i]["TGTOID"]);
						if ($file) {
							$refinfo = $file->get();
							if ($refinfo["OBJECTID"] < 1) {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["BAD"] = 1;
							} else {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $refinfo["NAME"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["IDENTIFIER"] = $refinfo["IDENTIFIER"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["COLOR"] = $refinfo["COLOR"];
							}
						}
					}
					else if ($oref[$i]["TGTTYPE"] == REFTYPE_EMAIL) {
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $oref[$i]["TGTOID"];
					}
					else if ($oref[$i]["TGTTYPE"] == REFTYPE_EXTERNAL) {
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $oref[$i]["TGTOID"];
					}
					if (!$skippage) {
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["TARGETTYPE"] = $oref[$i]["TGTTYPE"];
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["ID"] = $oref[$i]["TGTOID"];
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["AID"] = $oref[$i]["TGTAID"];
						$reftargethash[$oref[$i]["CBID"].$oref[$i]["TGTOID"].$oref[$i]["TGTAID"]] = true;
					} else {
							$skippage = false;
					}
					$oc++;
				}
			}


			$incoming = array();

			// For pages
			$oref = $cb->getLinkedPages();
			for ($i = 0; $i < count($oref); $i++) {
				$PageMgr = new PageMgr($oref[$i]['SITEID']);
				$tmpPage = $PageMgr->getPage($oref[$i]['PAGEID']);
				$pageInfo = $tmpPage->get();

				$pageInfo['RWRITE'] = $tmpPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]['PAGEID'], "RWRITE");
				$pageInfo['RDELETE'] = $tmpPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]['PAGEID'], "RDELETE");
				$iconData = getIconForPage($pageInfo);

				$pr = $PageMgr->getParents($oref[$i]['PAGEID']);
				$pr[count($pr)-1][0]['NAME'] = $siteMgr->getname($oref[$i]['SITEID']);
				$pageparents = $pr;
				$item = array(
					'ID'			=> $oref[$i]['PAGEID'],
					'VIATYPE'		=> REFTYPE_CO,
					'VIANAME'		=> $oref[$i]['PAGENAME'],
					'EMBEDDED'	=> 1,
					'PARENTS'		=> array(),
					'VIATARGETS'	=> array(
						array(
							'ID'			=> $oref[$i]['PAGEID'],
							'SITE'			=> $oref[$i]['SITEID'],
							'TARGETTYPE'	=> REFTYPE_PAGE,
							'NAME'			=> $oref[$i]['PAGENAME'],
							'PARENTS'		=> $pageparents,
							'EMBEDDED'	=> 1,
							'ICON'			=> $iconData['iconclass'],
							'STYLE'			=> $iconData['style'],
						),
					)
				);
				array_push( $incoming, $item);
			}

			// For mailings
			$oref = $cb->getLinkedMailings();
			$mailingMgr = new MailingMgr();
			for ($i = 0; $i < count($oref); $i++) {
				$tmpMailing = $mailingMgr->getMailing($oref[$i]['MAILINGID']);
				$mailingInfo = $tmpMailing->get();

				$mailingInfo['RWRITE'] = $tmpMailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]['MAILINGID'], "RWRITE");
				$mailingInfo['RDELETE'] = $tmpMailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]['MAILINGID'], "RDELETE");

				if ($mailingInfo['HASCHANGED']) {
					$style = 'changed';
				} else {
					$style = '';
				}

				$item = array(
					'ID'			=> $oref[$i]['MAILINGID'],
					'VIATYPE'		=> REFTYPE_CO,
					'VIANAME'		=> $oref[$i]['MAILINGNAME'],
					'EMBEDDED'	=> 1,
					'PARENTS'		=> array(),
					'VIATARGETS'	=> array(
						array(
							'ID'			=> $oref[$i]['MAILINGID'],
							'TARGETTYPE'	=> REFTYPE_MAILING,
							'NAME'			=> $oref[$i]['MAILINGNAME'],
							'EMBEDDED'	=> 1,
							'ICON'			=> 'mailing',
							'STYLE'			=> $style,
						),
					)
				);
				array_push( $incoming, $item);
			}

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = sCblockMgr()->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				$currentObject = sCblockMgr()->getCblock($lockedObject['OBJECTID']);
				$currentObject->releaseLock($lockedObject['TOKEN']);
			}

			// Check for lock, and lock if not locked
			$lockStatus = $cb->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$cb->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}
			if ($lockedFailed) {
				// Get user who locked this object
				$userWithLock = new User( $lockStatus['LOCKUID'] );
				$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
				$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
				$smarty->assign('lockedByUser', $lockedByUser );
				$mailingInfo['RWRITE'] = false;
				$mailingInfo['READONLY'] = true;
			}

			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$mailingInfo['RSTAGE'].'\' );');

			$smarty->assign("outgoing", array_values($outgoing));
			$smarty->assign("incoming", array_values($incoming));
			break;

		case 'file':
			$fileID = $data[0];
			$oref = $reftracker->getIncomingForFile($fileID);
			$sites = $siteMgr->getList();
			$incoming = array();

			$refcohash = array();
			$reftargethash = array();

			$ic = 0;
			$viacnt = 0;
			for ($i = 0; $i < count($oref); $i++) {
				if (!(isset($reftargethash[$oref[$i]["SRCOID"]]))) {
					$links = sCblockMgr()->getCblockLinkByEntrymaskLinkId($oref[$i]["SRCOID"]);
					$linkedto = array();
					for ($c = 0; $c < count($links); $c++) {
						$lcb = sCblockMgr()->getCblock($links[$c]["CBLOCKID"], $links[$c]["CBLOCKVERSION"]);
						if ($lcb) {
							$lcoinfo = $lcb->get();
							$lcdcurrversion = $lcb->getPublishedVersion(true);
							if ($links[$c]["CBLOCKVERSION"] == $lcdcurrversion) {
								for ($s = 0; $s < count($sites); $s++) {
									$linkedtosite = array();
									$linkedtosite = $lcb->getLinkedPageVersions($sites[$s]["ID"], true);
									$lThePageMgr = new PageMgr($sites[$s]["ID"]);
									for ($p = 0; $p < count($linkedtosite); $p++) {
										$linkpageid = $linkedtosite[$p]["ID"];
										$page = $lThePageMgr->getPage($linkedtosite[$p]["PID"]);
										$pageversions = $page->getVersionsByCblockId($links[$c]["CBLOCKID"]);
										$lpv = $page->getPublishedVersion(true);
										$page = $lThePageMgr->getPage($linkedtosite[$p]["PID"], $lpv);
										$lpv++;
										for ($pv = 0; $pv < count($pageversions); $pv++) {
											if ($lpv == $pageversions[$pv]["VERSION"]) {
												$showEntry = false;
												$tmpCblockList = $page->getCblockList();
												foreach($tmpCblockList as $tmpCblockListItem) {
													if ( ($tmpCblockListItem['OBJECTID'] == $links[$c]["CBLOCKID"]) &&
														 (($tmpCblockListItem['VERSION']) == $lcdcurrversion) ) {
														$showEntry = true;
													}
												}
												if ($showEntry) {
													$lcb = sCblockMgr()->getCblock($links[$c]["CBLOCKID"]);
													$cblockInfo = $lcb->get();
													if (!(isset($refcohash[$cblockInfo["ID"]]))) {
														$incoming[$ic]["ID"] = $links[$c]["CBLOCKID"];
														$incoming[$ic]["VIATYPE"] = REFTYPE_FORMFIELD;
														$incoming[$ic]["VIANAME"] = $cblockInfo["NAME"];
														$cop = sCblockMgr()->getParents($links[$c]["CBLOCKID"]);
														array_pop( $cop );
														$incoming[$ic]["PARENTS"] = $cop;
														$incoming[$ic]["EMBEDDED"] = $cblockInfo["EMBEDDED"];

														$incoming[$ic]['ID'] = $links[$c]["CBLOCKID"];
														$cblockInfo['RWRITE'] = $lcb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $links[$c]["CBLOCKID"], "RWRITE");
														$cblockInfo['RDELETE'] = $lcb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $links[$c]["CBLOCKID"], "RDELETE");
														$styleData = getStyleForContentblock($cblockInfo, true);
														$incoming[$ic]['STYLE'] = $styleData;
														$incoming[$ic]['HASCHANGED'] = $cblockInfo['HASCHANGED'];

														$viacnt = 0;
														$refcohash[$cblockInfo["ID"]] = $ic;
														$ic++;
													} else {
														$viacnt = count($incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"]);
													}
													if (!(isset($refphash[$sites[$s]["ID"].$linkedtosite[$p]["ID"]]))) {
														$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["TARGETTYPE"] = REFTYPE_PAGE;
														$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["NAME"] = $linkedtosite[$p]["NAME"];
														$lThePagrMgr = new PageMgr($sites[$s]["ID"]);

														$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["ID"] = $linkedtosite[$p]["ID"];
														$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["SITE"] = $sites[$s]["ID"];

														$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO'] = $page->get();
														$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO']['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkedtosite[$p]["ID"], "RWRITE");
														$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO']['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkedtosite[$p]["ID"], "RDELETE");
														$iconData = getIconForPage($incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO']);
														$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['ICON'] = $iconData['iconclass'];
														//$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['STYLE'] = $iconData['style'];
														$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['HASCHANGED'] = $incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO']['HASCHANGED'];

														$pr = $lThePagrMgr->getParents($linkedtosite[$p]["ID"]);
														$pr[count($pr)-1][0]['NAME'] = $siteMgr->getname($sites[$s]["ID"]);
														$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
														$refphash[$sites[$s]["ID"].$linkedtosite[$p]["ID"]] = $p;
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
			$smarty->assign("incoming", array_values($incoming));

			// Get current locks for this token (and unlock them)
			$file = sFileMgr()->getFile($fileID);
			if ($file) {
				$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
				$lockedObjects = sFileMgr()->getLocksByToken($lockToken);
				foreach($lockedObjects as $lockedObject) {
					$currentObject = sFileMgr()->getFile($lockedObject['OBJECTID']);
					if ($currentObject) {
						$currentObject->releaseLock($lockedObject['TOKEN']);
					}
				}
				// Check for lock, and lock if not locked
				$lockStatus = $file->getLock();
				if ($lockStatus['LOCKED'] == 0) {
					$lockedFailed = !$file->acquireLock($lockToken);
				} else {
					$lockedFailed = true;
				}
			}
			break;

		case 'page':
			$pageID = $data[0];
			$siteID = $data[1];

			$pageMgr = new PageMgr($siteID);
			$page = $pageMgr->getPage($pageID);
			$pageInfo = $page->get();
			$pageInfo['RSTAGE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RSTAGE");
			$pversion = $page->getPublishedVersion(true);
			$oref = $reftracker->getOutgoingForPage($siteID, $pageID, $pversion);
			$oc = 0;
			$outgoing = array();
			$refcohash = array();
			$reftargethash = array();
			for ($i = 0; $i < count($oref); $i++) {
				if (!(isset($reftargethash[$oref[$i]["CBID"].$oref[$i]["TGTOID"].$oref[$i]["TGTAID"]]))) {
					$cov = $oref[$i]["CBVERSION"];
					if ($cov == ALWAYS_LATEST_APPROVED_VERSION) $cov = 0;
					$ocb = sCblockMgr()->getCblock($oref[$i]["CBID"], $cov);
					$cblockInfo = $ocb->get();
					$hash = $cblockInfo["ID"];
					if (!(isset($refcohash[$hash]))) {
						$outgoing[$oc]["ID"] = $cblockInfo["ID"];
						$outgoing[$oc]["VIATYPE"] = $oref[$i]["SRCTYPE"];
						$outgoing[$oc]["VIANAME"] = $cblockInfo["NAME"];
						$outgoing[$oc]["EMBEDDED"] = $cblockInfo["EMBEDDED"];
						$cop = sCblockMgr()->getParents($oref[$i]["CBID"]);
						array_pop($cop);
						$outgoing[$oc]["PARENTS"] = $cop;
						$viacnt = 0;
						$refcohash[$hash] = $oc;
					} else {
						$viacnt = count($outgoing[$refcohash[$hash]]["VIATARGETS"]);
					}
					if ($oref[$i]["TGTTYPE"] == REFTYPE_PAGE) {
						$lThePageMgr = new PageMgr($oref[$i]["TGTAID"]);

						$siteInfo = $siteMgr->get($oref[$i]["TGTAID"]);
						if ($siteInfo['ID'] == $oref[$i]["TGTAID"]) {
							$pr = $lThePageMgr->getParents($oref[$i]["TGTOID"]);
							$pr[count($pr)-1][0]['NAME'] = $siteMgr->getname($oref[$i]["TGTAID"]);
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
							$refPage = $lThePageMgr->getPage($oref[$i]["TGTOID"]);
							if ($refPage) {
								$refPageVersion = $refPage->getPublishedVersion(true);
							}
							//$refPage = $lThePageMgr->getPage($oref[$i]["TGTOID"], $refPageVersion);
							$refPage = $lThePageMgr->getPage($oref[$i]["TGTOID"]);
							if ($refPage) {
								$refinfo = $refPage->get();
							}
							$refinfo['RWRITE'] = $refPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]["TGTOID"], "RWRITE");
							$refinfo['RDELETE'] = $refPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $oref[$i]["TGTOID"], "RWRITE");
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["SITEID"] = $oref[$i]["TGTAID"];
							$iconData = getIconForPage($refinfo);
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["ICON"] = $iconData['iconclass'];
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["STYLE"] = $iconData['style'];
							$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["HASCHANGED"] = $refinfo['HASCHANGED'];

							if ($refinfo["OBJECTID"] < 1) {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["BAD"] = 1;
							} else {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $refinfo["NAME"];
							}
							$skippage = false;
						} else {
							$skippage = true;
						}
					} elseif ($oref[$i]["TGTTYPE"] == REFTYPE_FILE) {
						$pr = sFileMgr()->getParents($oref[$i]["TGTOID"]);
						array_pop($pr );
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
						$file = sFileMgr()->getFile($oref[$i]["TGTOID"]);
						if ($file) {
							$refinfo = $file->get();
							if ($refinfo["OBJECTID"] < 1) {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["BAD"] = 1;
							} else {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $refinfo["NAME"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["IDENTIFIER"] = $refinfo["IDENTIFIER"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["CODE"] = $refinfo["CODE"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["COLOR"] = $refinfo["COLOR"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["ID"] = $oref[$i]["TGTOID"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["SITE"] = 'file';
							}
						}
					} elseif ($oref[$i]["TGTTYPE"] == REFTYPE_IMAGE) {
						$pr = sFileMgr()->getParents($oref[$i]["TGTOID"]);
						array_pop($pr );
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
						$file = sFileMgr()->getFile($oref[$i]["TGTOID"]);
						if ($file) {
							$refinfo = $file->get();
							if ($refinfo["OBJECTID"] < 1) {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["BAD"] = 1;
							} else {
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $refinfo["NAME"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["IDENTIFIER"] = $refinfo["IDENTIFIER"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["CODE"] = $refinfo["CODE"];
								$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["COLOR"] = $refinfo["COLOR"];
							}
						}
					} elseif ($oref[$i]["TGTTYPE"] == REFTYPE_EMAIL) {
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $oref[$i]["TGTOID"];
					} elseif ($oref[$i]["TGTTYPE"] == REFTYPE_EXTERNAL) {
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["NAME"] = $oref[$i]["TGTOID"];
					}
					if (!$skippage) {
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["TARGETTYPE"] = $oref[$i]["TGTTYPE"];
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["ID"] = $oref[$i]["TGTOID"];
						$outgoing[$refcohash[$hash]]["VIATARGETS"][$viacnt]["AID"] = $oref[$i]["TGTAID"];
						$reftargethash[$oref[$i]["CBID"].$oref[$i]["TGTOID"].$oref[$i]["TGTAID"]] = true;
					} else {
						$skippage = false;
					}
					$oc++;
				}
			}

			$oref = $reftracker->getIncomingForPage($siteID, $pageID);

			$sites = $siteMgr->getList();
			$incoming = array();

			$refcohash = array();
			$reftargethash = array();

			$ic = 0;
			$viacnt = 0;
			for ($i = 0; $i < count($oref); $i++) {
				if (!(isset($reftargethash[$oref[$i]["SRCOID"]]))) {
					$links = sCblockMgr()->getCblockLinkByEntrymaskLinkId($oref[$i]["SRCOID"]);
					$linkedto = array();
					for ($c = 0; $c < count($links); $c++) {
						if ($oref[$i]["SRCVER"] > 0) {
							$lcb = sCblockMgr()->getCblock($links[$c]["CBLOCKID"]);
							if ($lcb) {

								$lcv = $lcb->getPublishedVersion(true);

								$lcb = sCblockMgr()->getCblock($links[$c]["CBLOCKID"], $lcv);
								$lcoinfo = $lcb->get();

								$linkedtomailings = $lcb->getLinkedMailingVersions(true);

								for ($m = 0; $m < count($linkedtomailings); $m++) {
									$linkmailingid = $linkedtomailings[$m]['ID'];
									$lMailing = sMailingMgr()->getMailing($linkmailingid);
									$mailingversions = $lMailing->getVersionsByCblockId($links[$c]['CBLOCKID']);
									$referredMailing = sMailingMgr()->getMailing($linkedtomailings[$m]["PID"]);
									$lmv = $referredMailing->getPublishedVersion(true);
									// $lmv++;
									for ($mv = 0; $mv < count($mailingversions); $mv++) {
										if ($lmv == $mailingversions[$mv]["VERSION"]) {
											$cb = sCblockMgr()->getCblock($links[$c]['CBLOCKID']);
											$cblockInfo = $cb->get();
											if (!(isset($refcohash[$cblockInfo["ID"]]))) {
												$incoming[$ic]["ID"] = $links[$c]["CBLOCKID"];
												$incoming[$ic]["VIATYPE"] = REFTYPE_FORMFIELD;
												$incoming[$ic]["VIANAME"] = $cblockInfo["NAME"];
												$cop = sCblockMgr()->getParents($links[$c]["CBLOCKID"]);
												array_pop($cop );
												$incoming[$ic]["PARENTS"] = $cop;
												$incoming[$ic]["EMBEDDED"] = $cblockInfo["EMBEDDED"];

												$incoming[$ic]['ID'] = $links[$c]["CBLOCKID"];
												$cblockInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $links[$c]["CBLOCKID"], "RWRITE");
												$cblockInfo['RDELETE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $links[$c]["CBLOCKID"], "RDELETE");
												$styleData = getStyleForContentblock($cblockInfo, true);
												$incoming[$ic]['STYLE'] = $styleData;
												$incoming[$ic]['HASCHANGED'] = $cblockInfo['HASCHANGED'];

												$viacnt = 0;
												$refcohash[$cblockInfo["ID"]] = $ic;
												$ic++;
											} else {
												$viacnt = count($incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"]);
											}
											if (!(isset($refphash[$linkedtomailings[$m]["ID"]]))) {
												$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["TARGETTYPE"] = REFTYPE_MAILING;
												$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["NAME"] = $linkedtomailings[$m]["NAME"];
												$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["ID"] = $linkedtomailings[$m]["PID"];
												$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['HASCHANGED'] = $incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO']['HASCHANGED'];
												$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['STYLE'] = '';
												if ($incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['HASCHANGED']) {
													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['STYLE'] = 'changed';
												}
												$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['ICON'] = 'mailing';
												$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['HASCHANGED'] = $incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO']['HASCHANGED'];
												$refphash[$linkedtomailings[$m]["ID"]] = $m;
											}
										}
									}
								}

								for ($s = 0; $s < count($sites); $s++) {
									$pageMgr = new PageMgr($sites[$s]["ID"]);
									$linkedtosite = array();
									$linkedtosite = $lcb->getLinkedPageVersions($sites[$s]["ID"], true);

									for ($p = 0; $p < count($linkedtosite); $p++) {
										$linkpageid = $linkedtosite[$p]["ID"];
										$lPage = $pageMgr->getPage($linkpageid);
										$pageversions = $lPage->getVersionsByCblockId($links[$c]["CBLOCKID"]);
										$referredPage = $pageMgr->getPage($linkedtosite[$p]["PID"]);
										$lpv = $referredPage->getPublishedVersion(true);
										// $lpv++;
										for ($pv = 0; $pv < count($pageversions); $pv++) {
											if ($lpv == $pageversions[$pv]["VERSION"]) {
												$cb = sCblockMgr()->getCblock($links[$c]["CBLOCKID"]);
												$cblockInfo = $cb->get();
												if (!(isset($refcohash[$cblockInfo["ID"]]))) {
													$incoming[$ic]["ID"] = $links[$c]["CBLOCKID"];
													$incoming[$ic]["VIATYPE"] = REFTYPE_FORMFIELD;
													$incoming[$ic]["VIANAME"] = $cblockInfo["NAME"];
													$cop = sCblockMgr()->getParents($links[$c]["CBLOCKID"]);
													array_pop($cop );
													$incoming[$ic]["PARENTS"] = $cop;
													$incoming[$ic]["EMBEDDED"] = $cblockInfo["EMBEDDED"];

													$incoming[$ic]['ID'] = $links[$c]["CBLOCKID"];
													$cblockInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $links[$c]["CBLOCKID"], "RWRITE");
													$cblockInfo['RDELETE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $links[$c]["CBLOCKID"], "RDELETE");
													$styleData = getStyleForContentblock($cblockInfo, true);
													$incoming[$ic]['STYLE'] = $styleData;
													$incoming[$ic]['HASCHANGED'] = $cblockInfo['HASCHANGED'];

													$viacnt = 0;
													$refcohash[$cblockInfo["ID"]] = $ic;
													$ic++;
												} else {
													$viacnt = count($incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"]);
												}
												if (!(isset($refphash[$sites[$s]["ID"].$linkedtosite[$p]["ID"]]))) {
													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["TARGETTYPE"] = REFTYPE_PAGE;
													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["NAME"] = $linkedtosite[$p]["NAME"];

													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["ID"] = $linkedtosite[$p]["PID"];
													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["SITE"] = $sites[$s]["ID"];

													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO'] = $referredPage->get();
													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO']['RWRITE'] = $referredPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkedtosite[$p]["PID"], "RWRITE");
													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO']['RDELETE'] = $referredPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkedtosite[$p]["PID"], "RDELETE");
													$iconData = getIconForPage($incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO']);
													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['ICON'] = $iconData['iconclass'];
													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['STYLE'] = $iconData['style'];
													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['HASCHANGED'] = $incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]['PAGEINFO']['HASCHANGED'];

													$lPageMgr = new PageMgr($sites[$s]["ID"]);
													$pr = $lPageMgr->getParents($linkedtosite[$p]["ID"]);
													$pr[count($pr)-1][0]['NAME'] = $siteMgr->getname($sites[$s]["ID"]);
													$incoming[$refcohash[$cblockInfo["ID"]]]["VIATARGETS"][$viacnt]["PARENTS"] = $pr;
													$refphash[$sites[$s]["ID"].$linkedtosite[$p]["ID"]] = $p;
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}

			$pageMgr = new PageMgr($siteID);
			$page = $pageMgr->getPage($pageID);

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $pageMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				$currentObject = $pageMgr->getPage($lockedObject['OBJECTID']);
				$currentObject->releaseLock($lockedObject['TOKEN']);
			}
			// Check for lock, and lock if not locked
			$lockStatus = $page->getLock();

			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$page->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}

			$smarty->assign("outgoing", array_values($outgoing));
			$smarty->assign("incoming", array_values($incoming));
			break;
	}

	if ($lockedFailed) {
		// Get user who locked this object
		$userWithLock = new User( $lockStatus['LOCKUID'] );
		$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
		$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
		$smarty->assign('lockedByUser', $lockedByUser );
		$object_permissions['RWRITE'] = false;
		if (($objecttype == 'cblock') || ($objecttype == 'page')) {
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
		}
	} else {
		if (($objecttype == 'cblock') || ($objecttype == 'page')) {
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$pageInfo['RSTAGE'].'\' );');
		}
	}

	if ($objecttype == 'mailing') {
		// Check if a send is in progress (and lock if true)
		$mailingStatus = $mailing->getStatus();
		if ($mailingStatus['STATUS'] == 'INPROGRESS') {
			$userWithLock = new User( $mailingStatus['UID'] );
			$lockedByUser = $userWithLock->get( $mailingStatus['UID'] );
			$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $mailingStatus['UID'] );
			$smarty->assign('lockedByUser', $lockedByUser );
			$object_permissions['RWRITE'] = false;
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\' );');
		} else {
			if (!$lockedFailed) {
				$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$pageInfo['RSTAGE'].'\' );');
			}
		}
	}

	$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

	$smarty->assign("site", $siteID);
	$smarty->assign("refresh", $refresh);
	$smarty->assign("objecttype", $objecttype);
	$smarty->assign("page", $pageID);
	$smarty->assign("pageInfo", $pageInfo);
	$smarty->assign("win_no", $this->request->parameters['win_no']);
	$smarty->display('file:'.$this->page_template);

?>