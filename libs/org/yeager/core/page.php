<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The Page class, which represents an instance of a Page.
 */
class Page extends Versionable {
	private $_site;

	private $_table_object;
	private $_table_permissions;
	private $_table_history;
	private $_table_tree;
	private $_table_properties;
	private $_table_taglinks;
	private $_table_scheduler;

	public $history;
	public $properties;
	public $tags;
	public $scheduler;
	public $comments;

	private $propertyFields = array();

	/**
	 * Constructor of the Page class
	 *
	 * @param int $siteID Site Id
	 * @param int $pageID Page Id
	 * @param int $version Version
	 */
	public function __construct($siteID = 1, $pageID = 0, $version = 0) {
		$this->_uid = &sUserMgr()->getCurrentUserID();
		if ($siteID < 1) {
			return false;
		}
		$this->_site = $siteID;
		$this->_id = $pageID;
		$this->initTables();
		$this->permissions = new Permissions($this->_table_permissions, $this);
		parent::__construct($this->_id, $version, $this->_table_object, $this->_table_tree, $this->permissions);
		$this->history = new History($this, $this->_id_history, $this->permissions, $this->_site);
		$this->tags = new Tags($this);
		$this->comments = new Comments($this);
		$this->properties = new Properties($this->_table_properties, $this->getPropertyId(), $this);
		$this->scheduler = new Scheduler($this->_table_scheduler, SCHEDULER_PAGE);
	}

/// @cond DEV

	/**
	 * Initializes internal class members
	 */
	private function initTables() {
		$this->_table_object = "yg_site_" . $this->_site . "_properties";
		$this->_table_permissions = "yg_site_" . $this->_site . "_permissions";
		$this->_id_history = "site_" . $this->_site;
		$this->_table_tree = "yg_site_" . $this->_site . "_tree";
		$this->_table_properties = "yg_site_" . $this->_site . "_props";
		$this->_table_taglinks = "yg_site_" . $this->_site . "_lnk_articles";
		$this->_table_commentlinks = "yg_comments_lnk_pages_" . $this->_site;
		$this->_table_scheduler = "yg_site_" . $this->_site . "_cron";
	}

/// @endcond

	/**
	 * Gets the Id of the Site of this Page
	 *
	 * @return int
	 */
	public function getSite() {
		return $this->_site;
	}

/// @cond DEV

	/**
	 * Gets the object prefix, used for table names in database queries
	 *
	 * @return string Objectprefix
	 */
	function getObjectPrefix() {
		return "pages";
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets additional identifier
	 *
	 * @return array Additional identifier
	 */
	function getAdditionalIdentifier() {
		$identifier = array("SITEID");
		return $identifier;
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets additional identifier value
	 *
	 * @return int
	 */
	function getAdditionalIdentifierValue() {
		return $this->getSite();
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table which contains the properties of Pages
	 *
	 * @return string Tablename
	 */
	function getPropertyTable() {
		return "yg_site_" . $this->_site . "_properties";
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table which contains links between Comments and Pages
	 *
	 * @return string Tablename
	 */
	function getCommentsLinkTable() {
		return $this->_table_commentlinks;
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table which contains the permissions for Pages
	 *
	 * @return string Tablename
	 */
	function getPermissionsTable() {
		return $this->_table_permissions;
	}

/// @endcond

	/**
	 * Callback method which is executed when a Usergroup permission on a Page changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 */
	public function onPermissionChange($usergroupId, $permission, $value) {
		// Also set these permissions to the all blinds Cblocks in this Page
		$objectid = (int)$this->_id;
		$templateMgr = new Templates();

		$pageVersions = $this->getVersions();

		foreach ($pageVersions as $pageVersions_item) {
			$tmpPage = new Page($this->getSite(), $objectid, $pageVersions_item['VERSION']);

			$colist = $tmpPage->getCblockList('', false, true);
			foreach ($colist as $colistItem) {
				$coid = $colistItem['OBJECTID'];
				if ($coid > 0) {
					array_push($blindCos, $coid);
				}
			}
		}

		$blindCos = array_unique($blindCos);

		$pagePermissions = $this->permissions->getByUsergroup($usergroupId, $objectid);
		if ($pagePermissions) {
			foreach ($blindCos as $coid) {
				$bcb = sCblockMgr()->getCblock($coid);
				$bcb->permissions->setByUsergroup($usergroupId, $permission, $coid, $value);
			}
		}
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on a Page change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permissions (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 */
	public function onPermissionsChange($usergroupId, $permissions, $value) {
		// Also set these permissions to the all blinds Cblocks in this Page
		$objectid = (int)$this->_id;
		$templateMgr = new Templates();

		$pageVersions = $this->getVersions();

		$blindCos = array();
		foreach ($pageVersions as $pageVersions_item) {
			$tmpPage = new Page($this->getSite(), $objectid, $pageVersions_item['VERSION']);

			$colist = $tmpPage->getCblockList('', false, true);
			foreach ($colist as $colistItem) {
				$coid = $colistItem['OBJECTID'];
				if ($coid > 0) {
					array_push($blindCos, $coid);
				}
			}
		}

		$blindCos = array_unique($blindCos);

		$pagePermissions = $this->permissions->getByUsergroup($usergroupId, $objectid);
		if ($pagePermissions) {
			foreach ($blindCos as $coid) {
				$bcb = sCblockMgr()->getCblock($coid);
				$bcb->permissions->setPermissions($permissions, $coid);
			}
		}
		return true;
	}

/// @cond DEV

	/**
	 * Gets the name of the database table containing the Pages tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return "yg_site_" . $this->_site . "_tree";
	}

/// @endcond

	/**
	 * Calls a specific Extension hook Callback method
	 *
	 * @param string $method
	 * @param int $siteId Site Id
	 * @param int $pageId Page Id
	 * @param int $version Page version
	 * @param mixed $args Arbitrary arguments
	 */
	function callExtensionHook($method, $siteId, $pageId, $version, $args) {
		$extensions = new ExtensionMgr($this->_db, $this->_uid);
		$all_page_extensions = $extensions->getList(EXTENSION_PAGE, true);
		$extarr = array();
		foreach ($all_page_extensions as $all_page_extension) {
			$extension = $extensions->getExtension($all_page_extension["CODE"]);
			if ($extension && $extension->usedByPage($pageId, $version, $siteId) === true) {
				$extension = $extensions->getExtension($all_page_extension["CODE"], $pageId, $version, $siteId);
				if ($extension) {
					array_push($extarr, $extension);
				}
			}
		}
		foreach ($extarr as $extension) {
			$extension->callExtensionHook($method, $args);
		}
	}

/// @cond DEV

	/**
	 * Helper method for querying the database
	 *
	 * @param string $sql SQL query
	 * @return array|false Result of SQL query or FALSE in case of an error
	 * @throws Exception
	 */
	function cacheExecuteGetArray($sql) {
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
		}
		$blaetter = $dbr->GetArray();
		return $blaetter;
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets all Contentareas of this Page including Cblocks and content
	 *
	 * @return array
	 */
	function getContentInternal() {
		$pageId = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageId, "RREAD")) {
			$templateMgr = new Templates();
			$pageInfo = $this->get();

			$contentareas = $templateMgr->getContentareas($pageInfo['TEMPLATEID']);
			for ($i = 0; $i < count($contentareas); $i++) {
				$colist = $this->getCblockList($contentareas[$i]['CODE']);
				for ($x = 0; $x < count($colist); $x++) {
					if ($colist[$x]['OBJECTID'] > 0) {
						if ($colist[$x]['EMBEDDED'] == 0) {
							$cb = sCblockMgr()->getPublishedCblock($colist[$x]['OBJECTID']);
							if ($cb) {
								$cbInfo = $cb->get();
								$colist[$x]['CBVERSION'] = $colist[$x]['VERSION'] = $cbInfo['VERSIONPUBLISHED'];
							}
						} else {
							$cb = sCblockMgr()->getCblock($colist[$x]['OBJECTID'], $colist[$x]['VERSION']);
							$colist[$x]['CBVERSION'] = $colist[$x]['VERSION'];
						}
						if ($cb) {
							$colist[$x]['ENTRYMASKS'] = $cb->getEntrymasks();
							for ($c = 0; $c < count($colist[$x]['ENTRYMASKS']); $c++) {
								$controlFormfields = $cb->getFormfieldsInternal($colist[$x]['ENTRYMASKS'][$c]['LINKID']);
								for ($w = 0; $w < count($controlFormfields); $w++) {
									if (($controlFormfields[$w]['FORMFIELD'] == 6) || ($controlFormfields[$w]['FORMFIELD'] == 16)) {
										if (trim($controlFormfields[$w]['VALUE01'])) {
											$file = sFileMgr()->getFile($controlFormfields[$w]['VALUE01']);
											if ($file) {
												$fileInfo = $file->get();
												$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
											}
										}
									}
									if ($controlFormfields[$w]['FORMFIELD'] == 7) {
										if (trim($controlFormfields[$w]['VALUE01'])) {
											$icb = sCblockMgr()->getCblock($controlFormfields[$w]['VALUE01']);
											if ($icb) {
												$info = $icb->get();
												$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
											}
										}
									}
									if ($controlFormfields[$w]['FORMFIELD'] == 8) {
										if (trim($controlFormfields[$w]['VALUE01'])) {
											$info = $this->tags->get($controlFormfields[$w]['VALUE01']);
											$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
										}
									}
								}
								$colist[$x]['ENTRYMASKS'][$c]['FORMFIELDS'] = $controlFormfields;
							}
						} else {
							// dummy
							$colist[$x] = NULL;
						}
					}
				}
				// Clear all "NULL" entries
				$realCoList = array();
				foreach ($colist as $colistIdx => $colistItem) {
					if ($colistItem !== NULL) {
						$realCoList[] = $colistItem;
					}
				}
				$contentareas[$i]['LIST'] = $realCoList;
			}
			return $contentareas;
		} else {
			return false;
		}
	}



	/**
	 * Gets all Contentareas of this Page including Cblocks and content
	 *
	 * @return array Array of Contentareas including Cblocks, Entrymasks and Formfields
	 */
	function getContentLiveEdit() {
		$pageId = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageId, "RREAD")) {
			$templateMgr = new Templates();
			$pageInfo = $this->get();

			$contentareas = $templateMgr->getContentareas($pageInfo['TEMPLATEID']);
			$namedContentareas = array();
			for ($i = 0; $i < count($contentareas); $i++) {
				$colist = $this->getCblockList($contentareas[$i]['CODE']);
				for ($x = 0; $x < count($colist); $x++) {
					if ($colist[$x]['OBJECTID'] > 0) {
						if ($colist[$x]['EMBEDDED'] == 0) {
							$cb = sCblockMgr()->getPublishedCblock($colist[$x]['OBJECTID']);
							$cbInfo = $cb->get();
							$colist[$x]['CBLOCKVERSION'] = $colist[$x]['VERSION'] = $cbInfo['VERSIONPUBLISHED'];
						} else {
							$cb = sCblockMgr()->getCblock($colist[$x]['OBJECTID'], $colist[$x]['VERSION']);
							$colist[$x]['CBLOCKVERSION'] = $colist[$x]['VERSION'];
						}
						if ($cb) {
							$colist[$x]['ENTRYMASKS'] = $cb->getEntrymasks();
							for ($c = 0; $c < count($colist[$x]['ENTRYMASKS']); $c++) {
								$controlFormfields = $cb->getFormfields($colist[$x]['ENTRYMASKS'][$c]['LINKID']);
								$namedFormfields = array();
								for ($w = 0; $w < count($controlFormfields); $w++) {
									foreach ($controlFormfields[$w] as $singleFieldIdx => $singleField) {
										if (substr($singleFieldIdx, 0, 6) == 'VALUE0') {
											unset($controlFormfields[$w][$singleFieldIdx]);
										}
									}
									$namedFormfields[$controlFormfields[$w]['IDENTIFIER']] = $controlFormfields[$w];
								}
								$colist[$x]['ENTRYMASKS'][$c]['FORMFIELDS'] = $namedFormfields;
							}
						}
					}
				}
				$namedContentareas[$contentareas[$i]['CODE']]['CBLOCKS'] = $colist;
			}
			return $namedContentareas;
		} else {
			return false;
		}
	}



/// @endcond

	/**
	 * Gets all Contentareas of this Page including Cblocks and content
	 *
	 * @return array Array of Contentareas including Cblocks, Entrymasks and Formfields
	 */
	function getContent() {
		$pageId = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageId, "RREAD")) {
			$templateMgr = new Templates();
			$pageInfo = $this->get();

			$contentareas = $templateMgr->getContentareas($pageInfo['TEMPLATEID']);
			$namedContentareas = array();
			for ($i = 0; $i < count($contentareas); $i++) {
				$colist = $this->getCblockList($contentareas[$i]['CODE']);
				for ($x = 0; $x < count($colist); $x++) {
					if ($colist[$x]['OBJECTID'] > 0) {
						if ($colist[$x]['EMBEDDED'] == 0) {
							$cb = sCblockMgr()->getPublishedCblock($colist[$x]['OBJECTID']);
							if ($cb) {
								$cbInfo = $cb->get();
								$colist[$x]['CBLOCKVERSION'] = $colist[$x]['VERSION'] = $cbInfo['VERSIONPUBLISHED'];
							}
						} else {
							$cb = sCblockMgr()->getCblock($colist[$x]['OBJECTID'], $colist[$x]['VERSION']);
							$colist[$x]['CBLOCKVERSION'] = $colist[$x]['VERSION'];
						}
						if ($cb) {
							$colist[$x]['ENTRYMASKS'] = $cb->getEntrymasks();
							for ($c = 0; $c < count($colist[$x]['ENTRYMASKS']); $c++) {
								$controlFormfields = $cb->getFormfields($colist[$x]['ENTRYMASKS'][$c]['LINKID']);
								$namedFormfields = array();
								for ($w = 0; $w < count($controlFormfields); $w++) {
									foreach ($controlFormfields[$w] as $singleFieldIdx => $singleField) {
										if (substr($singleFieldIdx, 0, 6) == 'VALUE0') {
											unset($controlFormfields[$w][$singleFieldIdx]);
										}
									}
									$namedFormfields[$controlFormfields[$w]['IDENTIFIER']] = $controlFormfields[$w];
								}
								$colist[$x]['ENTRYMASKS'][$c]['FORMFIELDS'] = $namedFormfields;
							}
						}
					}
				}
				$namedContentareas[$contentareas[$i]['CODE']]['CBLOCKS'] = $colist;
			}
			return $namedContentareas;
		} else {
			return false;
		}
	}

	/**
	 * Gets all versions of the Page which contains the specified Cblock
	 *
	 * @param int $cbId Cblock Id
	 * @return array|false Array of versions or FALSE in case of an error
	 */
	function getVersionsByCblockId($cbId) {
		$pageID = $this->_id;
		$cbId = (int)$cbId;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RREAD")) {
			$sql = "SELECT prop.VERSION AS VERSION FROM `yg_site_" . $this->_site . "_properties` as prop, `yg_site_" . $this->_site . "_lnk_cb` as lnk WHERE (prop.OBJECTID = $pageID) AND (lnk.PID = $pageID) AND (lnk.CBID = $cbId) AND (lnk.PVERSION = prop.VERSION) ORDER BY VERSION DESC";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Generates a new version of this Page by copying the currently instanced version
	 * and updates the currently instanced Object to the new version
	 *
	 * @return int|false New version of this Page or FALSE in case of an error
	 */
	public function newVersion() {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			$colist = $this->getCblockList();
			$sourceVersion = $this->getVersion();
			$sourceObject = sPageMgr($this->_site)->getPage($this->_id, $sourceVersion);
			if ($sourceVersion == $this->getLatestVersion()) {
				$historyIdentifier = 'TXT_PAGE_H_NEWVERSION';
				$historySourceVersion = NULL;
			} else {
				$historyIdentifier = 'TXT_PAGE_H_NEWVERSION_FROM';
				$historySourceVersion = $sourceVersion;
			}
			$newVersion = parent::newVersion();
			$this->properties = new Properties($this->_table_properties, $this->getPropertyId(), $this);
			$this->tags->copyTo($pageID, $sourceVersion, $pageID, $newVersion);
			$this->copyCblockLinks($sourceVersion, $newVersion);
			$this->copyExtensionsFrom($sourceObject);

			// Check if there are blind entrymasks in this page (and add a version)
			foreach ($colist as $colist_item) {
				if ($colist_item['EMBEDDED'] == 1) {
					$tmpCb = sCblockMgr()->getCblock($colist_item['OBJECTID'], $colist_item['VERSION']);
					$version = $tmpCb->newVersion();
					$CoLnkInfo = $this->getCblockLinkById($colist_item['LINKID']);
					$this->addCblockVersion($colist_item['OBJECTID'], $CoLnkInfo[0]['TEMPLATECONTENTAREA'], $version);
				}
			}
			$pageMgr = new PageMgr($this->getSite());
			$pageMgr->callExtensionHook('onVersionNew', $this->getSite(), (int)$this->_id, $this->getVersion());

			// Add to history
			$this->history->add(HISTORYTYPE_PAGE, $historySourceVersion, $newVersion, $historyIdentifier);

			return $newVersion;
		} else {
			return false;
		}
	}

	/**
	 * Copies the Cblock Page links from one version to another
	 *
	 * @param int $sourceVersion Source version
	 * @param int $targetversion Target version
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	private function copyCblockLinks($sourceVersion, $targetversion) {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			// co links rueberziehen
			/*
			$sql = "INSERT INTO `yg_site_".$this->_site."_lnk_cb`
			(PVERSION, PID, CBVERSION, CBID, ORDERPROD, TEMPLATECONTENTAREA)
			SELECT $targetversion, PID, CBVERSION, CBID, ORDERPROD, TEMPLATECONTENTAREA
			FROM `yg_site_".$this->_site."_lnk_cb` WHERE (PID = '$pageID') AND (PVERSION = '$sourceVersion');";
			*/

			$sql = "INSERT INTO `yg_site_" . $this->_site . "_lnk_cb`
						(PVERSION, PID, CBVERSION, CBID, ORDERPROD, TEMPLATECONTENTAREA)
					SELECT
						$targetversion, lnk.PID AS PID, lnk.CBVERSION AS CBVERSION, lnk.CBID AS CBID,
						lnk.ORDERPROD AS ORDERPROD, lnk.TEMPLATECONTENTAREA AS TEMPLATECONTENTAREA
					FROM
						`yg_site_" . $this->_site . "_lnk_cb` AS lnk
					JOIN
						`yg_contentblocks_properties` AS cprop ON (lnk.CBID = cprop.OBJECTID)
					WHERE
						(lnk.PID = '$pageID') AND (lnk.PVERSION = '$sourceVersion') AND (cprop.EMBEDDED = 0)
					GROUP BY
						lnk.CBID, lnk.TEMPLATECONTENTAREA;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			// co links rueberziehen (neue version fuer blinde cos erzeugen, diese dann zu neuer pageversion zuweisen)
			$sql = "SELECT
						$targetversion, lnk.PID AS PID, lnk.CBVERSION AS CBVERSION, lnk.CBID AS CBID,
						lnk.ORDERPROD AS ORDERPROD, lnk.TEMPLATECONTENTAREA AS TEMPLATECONTENTAREA
					FROM
						`yg_site_" . $this->_site . "_lnk_cb` AS lnk
					JOIN
						`yg_contentblocks_properties` AS cprop ON (lnk.CBID = cprop.OBJECTID) AND (lnk.CBVERSION = cprop.VERSION)
					WHERE
						(lnk.PID = '$pageID') AND (lnk.PVERSION = '$sourceVersion') AND (cprop.EMBEDDED = 1);";
			$blindCOs = $this->cacheExecuteGetArray($sql);

			foreach ($blindCOs as $blindCO) {
				$currCO = sCblockMgr()->getCblock($blindCO['CBID'], $blindCO['CBVERSION']);

				$sql = "INSERT INTO `yg_site_" . $this->_site . "_lnk_cb`
							( `CBID`, `CBVERSION`, `PID`, `PVERSION`, `TEMPLATECONTENTAREA`, `ORDERPROD` )
						VALUES
							( '" . $blindCO['CBID'] . "', '" . $blindCO['CBVERSION'] . "', '$pageID', '$targetversion', '" . $blindCO['TEMPLATECONTENTAREA'] . "', '" . $blindCO['ORDERPROD'] . "' );";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Copies Properties, Extensions, Permissions, Tags, assigned and embedded Cblocks
	 * from another Page into a new version of this Page
	 *
	 * @param object $sourcePage Source Page object
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function copyFrom(&$sourcePage) {
		if ($this->permissions->checkInternal($this->_uid, $this->_id, "RWRITE")) {
			$sourceID = $sourcePage->getID();
			$sourceSite = $sourcePage->getSite();
			$sourceVersion = $sourcePage->getVersion();
			$targetID = (int)$this->_id;
			$targetSite = $this->getSite();
			$targetVersion = $this->getVersion();
			$sourceVersionID = $sourcePage->getPropertyId();
			$targetVersionID = $this->getPropertyId();
			$sourceInfo = $sourcePage->get();
			$this->copyExtensionsFrom($sourcePage);

			if ($sourceSite == $this->_site) {
				$this->permissions->copyTo($sourceID, $targetID);
				$this->properties->copyTo($sourceVersionID, $targetVersionID);
				$this->tags->copyTo($sourceID, $sourceVersion, $targetID, $targetVersion);
			} else {
				// Permissions
				$sourcePermissions = $sourcePage->permissions->getPermissions();
				$this->permissions->setPermissions($sourcePermissions);

				// Properties
				$pageMgr = new PageMgr($this->getSite());
				$propstocopy = $pageMgr->properties->getList();
				$p = new Page($sourceSite, $sourceID, $sourceVersion);
				for ($i = 0; $i < count($propstocopy); $i++) {
					$scpd = $p->getPropertyId();
					$sourcevalue = $p->properties->getValueInternal($propstocopy[$i]["IDENTIFIER"]);
					$this->properties->setValue($propstocopy[$i]["IDENTIFIER"], $sourcevalue);
				}

				// Tags
				$sourceTags = $sourcePage->tags->getAssigned();
				foreach ($sourceTags as $sourceTag) {
					$this->tags->assign($sourceTag['ID']);
				}
			}

			$this->setTemplate($sourceInfo['TEMPLATEID']);
			$this->setNavigation($sourceInfo['NAVIGATIONID']);
			$this->setActive($sourceInfo['ACTIVE']);
			$this->setHidden($sourceInfo['HIDDEN']);

			// Clear contentareas
			$pageInfo = $this->get();
			$templateMgr = new Templates();
			$contentareas = $templateMgr->getContentareas($pageInfo['TEMPLATEID']);
			for ($i = 0; $i < count($contentareas); $i++) {
				$cblockList = $this->getCblockList($contentareas[$i]['CODE']);
				for ($x = 0; $x < count($cblockList); $x++) {
					$this->removeCblock($cblockList[$x]['ID'], $contentareas[$i]['CODE']);
				}
			}

			$pageInfo = $sourcePage->get();
			$contentareas = $templateMgr->getContentareas($pageInfo['TEMPLATEID']);

			$finalCblockListLinkOrder = array();
			for ($i = 0; $i < count($contentareas); $i++) {
				$cblockList = $sourcePage->getCblockList($contentareas[$i]['CODE']);
				for ($x = 0; $x < count($cblockList); $x++) {
					$coid = $cblockList[$x]['OBJECTID'];

					// Check if we have a blind contentblock
					if ($cblockList[$x]['EMBEDDED'] == 1) {
						// Yes, we have to copy it to the blind folder

						// Check which entrymasks are contained
						$sourcecb = sCblockMgr()->getCblock($coid, $cblockList[$x]['VERSION']);
						if ($sourcecb) {
							$src_entrymasks = $sourcecb->getEntrymasks();

							// Create blind contentblocks with these entrymasks
							foreach ($src_entrymasks as $src_entrymask_item) {
								// Add new contentblock to folder
								$contentblockID = $this->addCblockEmbedded($contentareas[$i]['CODE']);
								$newcb = sCblockMgr()->getCblock($contentblockID);
								$newcb->properties->setValue("NAME", $src_entrymask_item['ENTRYMASKNAME']);

								// Get the Link Id of the newly created contentblock (and save it)
								$finalCblockListLinkOrder[] = $this->getEmbeddedCblockLinkId($contentblockID);

								// Add requested control to contentblock
								$new_control = $newcb->addEntrymask($src_entrymask_item['ENTRYMASKID']);

								// Loop through all formfields
								$controlFormfields = $sourcecb->getFormfieldsInternal($src_entrymask_item['LINKID']);
								$newControlFormfields = $newcb->getFormfieldsInternal($new_control);

								// Fill all formfield parameter values with content from the source formfield
								for ($c = 0; $c < count($newControlFormfields); $c++) {
									$newcb->setFormfield($newControlFormfields[$c]['ID'],
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
						}
					} else {
						// No, it's a normal one, just link it to the page (and save the Link Id)
						$finalCblockListLinkOrder[] = $this->addCblockLink($coid, $contentareas[$i]['CODE']);
					}
				}
			}

			$this->setCblockLinkOrder($finalCblockListLinkOrder);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Copies Extensions from another Page to this Page
	 *
	 * @param object $sourceObject Source Mailing object
	 */
	function copyExtensionsFrom(&$sourceObject) {
		$sourceSite = $sourceObject->getSite();
		$sourceId = $sourceObject->getID();
		$sourceVersion = $sourceObject->getVersion();
		$targetId = $this->getID();
		$targetVersion = $this->getVersion();
		$targetSite = $this->getSite();
		$extensions = new ExtensionMgr(sYDB(), $this->_uid);
		$all_extensions = $extensions->getList(EXTENSION_PAGE, true);
		foreach ($all_extensions as $all_extension) {
			$extension = $extensions->getExtension($all_extension['CODE']);
			if ($extension && ($extension->usedByPage($sourceId, $sourceVersion, $sourceSite) === true)) {
				if ($extension->usedByPage($targetId, $targetVersion, $targetSite) !== true) {
					$extension->addToPageInternal($targetId, $targetVersion, $targetSite);
				}
				$extension = $extensions->getExtension($all_extension['CODE'], $targetId, $targetVersion, $targetSite);
				$sourceext = $extensions->getExtension($all_extension['CODE'], $sourceId, $sourceVersion, $sourceSite);
				if ($extension && $sourceext) {
					$newfid = $extension->getPropertyId();
					$oldfid = $sourceext->getPropertyId();
					$extension->properties->copyTo($oldfid, $newfid);
				}
			}
		}
	}

	/**
	 * Copies Tags from another Page to this Page
	 *
	 * @param object $sourceObject Source Page object
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function copyTagsFrom(&$sourceObject) {
		$targetID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $targetID, "RWRITE")) {
			$sourceTags = $sourceObject->tags->getAssigned();
			foreach ($sourceTags as $sourceTag) {
				$this->tags->assign($sourceTag['ID']);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Copies Permissions from another Page to this Page
	 *
	 * @param object $sourceObject Source Page object
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function copyPermissionsFrom(&$sourceObject) {
		$sourceID = $sourceObject->getID();
		$targetID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $targetID, "RWRITE")) {
			$allPermissions = $sourceObject->permissions->getPermissions();
			$this->permissions->clear();
			$this->permissions->setPermissions($allPermissions);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Moves this Page to the trashcan
	 *
	 * @return array Array with all elements which were successfully deleted
	 */
	function delete() {
		$pageID = $this->_id;
		$pageMgr = new PageMgr($this->getSite());
		$rootNode = $pageMgr->tree->getRoot();
		if ($pageID == $rootNode) {
			return array();
		}

		// Check if object is a folder
		$successNodes = array();
		$subNodes = $pageMgr->getTree($pageID, 1000, false, true);
		if (count($subNodes) > 0) {
			array_shift($subNodes);
			foreach($subNodes as $subNode) {
				if ($this->permissions->checkInternal($this->_uid, $subNode['ID'], "RDELETE")) {
					$page = $pageMgr->getPage($subNode['ID']);
					$successfullyDeleted = $page->delete();
					if (in_array($subNode['ID'], $successfullyDeleted) === true) {
						foreach($successfullyDeleted as $successfullyDeletedItem) {
							$successNodes[] = $successfullyDeletedItem;
						}
					}
				}
			}
		}

		if ($this->permissions->checkInternal($this->_uid, $pageID, "RDELETE")) {
			// Move to root level
			$pageMgr->tree->moveTo($pageID, $rootNode);

			// Set to "DELETED"
			$sql = "UPDATE yg_site_" . $this->_site . "_properties SET DELETED = 1 WHERE OBJECTID = $pageID;";
			$result = sYDB()->Execute($sql);

			$successNodes[] = $pageID;
			$pageMgr->callExtensionHook("onDelete", $this->getSite(), $this->_id, $version);
		}

		if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/PAGE_DELETE") == "true") {
			Singleton::FC()->emptyBucket();
		}

		return $successNodes;
	}

	/**
	 * Restores this Page from the trashcan
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function undelete() {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RDELETE")) {
			// restore from trashcan
			$sql = "UPDATE yg_site_" . $this->_site . "_properties SET DELETED = 0 WHERE OBJECTID = $pageID;";
			sYDB()->Execute($sql);
			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/TAG_RENAME") == "true") {
				Singleton::FC()->emptyBucket();
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the state of a specific version of this Page to "published"
	 *
	 * @param int $version (optional) Specific version or ALWAYS_LATEST_APPROVED_VERSION (constant) to always publish the lastest approved version
	 * @return int|false New version of the Page or FALSE in case of an error
	 */
	function publishVersion($version = 0) {
		if ($version == 0) {
			$version = $this->getVersion();
		}
		$result = parent::publishVersion($version);
		if ($result) {
			$published = false;
			if ($version == ALWAYS_LATEST_APPROVED_VERSION) {
				$version = $this->getLatestApprovedVersion();
				if ($version) $published = true;
			} else {
				$published = true;
			}
			if ($published) {
				$this->history->add(HISTORYTYPE_PAGE, NULL, $version, 'TXT_PAGE_H_PUBLISH');
				$pageMgr = new PageMgr($this->getSite());
				$pageMgr->callExtensionHook("onPublish", $this->getSite(), $this->_id, $version);
				if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/PAGE_PUBLISH") == "true") {
					Singleton::FC()->emptyBucket();
				}
			}
		}
		return $result;
	}

	/**
	 * Approves the current version of this Page and all embedded Cblocks and creates a new working version
	 *
	 * @return int|false New version or FALSE in case of an error
	 */
	function approve() {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RSTAGE")) {
			$this->approveVersion();
			return $this->newVersion();
		} else {
			return false;
		}
	}

	/**
	 * Approves the specified version of this Page and all embedded Cblocks
	 *
	 * @param int $version (optional) Page version
	 * @return int|false New version or FALSE in case of an error
	 */
	public function approveVersion($version = 0) {
		$result = parent::approveVersion($version);

		// Check if there are blind entrymasks in this page (and add a version)
		$colist = $this->getCblockList();

		foreach ($colist as $colist_item) {
			if ($colist_item['EMBEDDED'] == 1) {
				$tmpCb = sCblockMgr()->getCblock($colist_item['OBJECTID'], $colist_item['VERSION']);
				$tmpCb->approveVersion();
			}
		}

		if ((int)$version == 0) $version = (int)$this->getVersion();

		$this->history->add(HISTORYTYPE_PAGE, NULL, $version, 'TXT_PAGE_H_APPROVE');

		$pageMgr = new PageMgr($this->getSite());

		if ($this->getPublishedVersion()==ALWAYS_LATEST_APPROVED_VERSION) {
			$this->history->add(HISTORYTYPE_PAGE, NULL, $version, 'TXT_PAGE_H_AUTOPUBLISH');
			$pageMgr->callExtensionHook("onPublish", $this->getSite(), $this->_id, $version);
			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/PAGE_PUBLISH") == "true") {
				Singleton::FC()->emptyBucket();
			}
		}

		$pageMgr->callExtensionHook('onApprove', $this->getSite(), (int)$this->_id, (int)$version);
		return $result;
	}

	/**
	 * Gets basic information about this Page
	 *
	 * @return array|false Array containing information about this Page or FALSE in case of an error
	 */
	function get() {
		$pageID = $this->_id;
		$version = (int)$this->getVersion();
		if ($pageID < 1) {
			return false;
		}
		if ($this->_site < 1) {
			return false;
		}
		if (strlen($version) < 1) {
			return false;
		}
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RREAD")) {
			$sql = "SELECT * FROM yg_site_" . $this->_site . "_props WHERE READONLY = 1;";
			$ra = $this->cacheExecuteGetArray($sql);
			$dynIdentifers = array();
			foreach ($ra as $raItem) {
				$dynIdentifers[] = "\npv.`" . $raItem['IDENTIFIER'] . '` AS `' . $raItem['IDENTIFIER'] . '`';
			}
			$dynIdentifers = implode(', ', $dynIdentifers);
			$sql = "SELECT
						p.ID AS ID,
						p.OBJECTID AS OBJECTID,
						p.VERSION AS VERSION,
						p.APPROVED AS APPROVED,
						p.CREATEDBY AS CREATEDBY,
						p.CHANGEDBY AS CHANGEDBY,
						p.HASCHANGED AS HASCHANGED,
						p.TEMPLATEID AS TEMPLATEID,
						p.COMMENTSTATUS AS COMMENTSTATUS,
						p.COMMENTSTATUS_AUTO AS COMMENTSTATUS_AUTO,
						p.NAVIGATION AS NAVIGATIONID,
						p.ACTIVE AS ACTIVE,
						p.HIDDEN AS HIDDEN,
						p.LOCKED AS LOCKED,
						p.LOCKUID AS LOCKUID,
						p.TOKEN AS TOKEN,
						p.DELETED AS DELETED,
						p.CREATEDTS AS CREATEDTS,
						p.CHANGEDTS AS CHANGEDTS,
						t.VERSIONPUBLISHED AS VERSIONPUBLISHED,
						t.LEVEL AS LEVEL,
						t.PARENT AS PARENT,
						t.PNAME AS PNAME,
						navi.NAME AS NAVIGATIONNAME,
						navi.CODE AS NAVIGATIONCODE,
						$dynIdentifers
					FROM
						(yg_site_" . $this->_site . "_properties as p, yg_site_" . $this->_site . "_tree as t)
					LEFT JOIN
						yg_templates_navis AS navi ON navi.ID = p.NAVIGATION
					LEFT JOIN
						yg_site_" . $this->_site . "_propsv as pv ON pv.OID = p.ID
					WHERE
						(p.OBJECTID = $pageID AND p.VERSION = $version) AND
						(t.ID = p.OBJECTID);";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra[0];
		} else {
			return false;
		}
	}

	/**
	 * Adds an embedded Cblock to this Page
	 *
	 * @param string $contentarea Contentarea code
	 * @return int Cblock Id
	 */
	function addCblockEmbedded($contentarea) {
		$pageId = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageId, "RWRITE")) {
			// Get folder for embedded cblocks
			$embeddedCblockFolder = (int)sConfig()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");

			// Add a Cblock
			$embeddedCblockId = sCblockMgr()->add($embeddedCblockFolder);
			$newEmbeddedCblock = sCblockMgr()->getCblock($embeddedCblockId);

			// Set it to "embedded"
			$newEmbeddedCblock->setEmbedded();

			// Add this Cblock to this page
			$this->addCblockLink($embeddedCblockId, $contentarea);

			// Get an instance of this new embedded Cblock and inherit rights from this page to embedded contentblock
			$usergroups = sUsergroups()->getList();
			foreach ($usergroups as $usergroupItem) {
				$usergroupId = $usergroupItem['ID'];
				$objectPermissions = $this->permissions->getByUsergroup($usergroupId, $pageId);
				if ($objectPermissions) {
					$permissionsArray = array('RREAD', 'RWRITE', 'RDELETE', 'RSUB', 'RSTAGE');
					foreach ($permissionsArray as $permissionsItem) {
						if ($objectPermissions[$permissionsItem]) {
							$newEmbeddedCblock->permissions->setByUsergroup($usergroupId, $permissionsItem, $embeddedCblockId, 1);
						} else {
							$newEmbeddedCblock->permissions->setByUsergroup($usergroupId, $permissionsItem, $embeddedCblockId, 0);
						}
					}
				}
			}

			// Set the first version of the embedded cblock to "published"
			$newEmbeddedCblock->publishVersion();

			return $embeddedCblockId;
		} else {
			return false;
		}
	}

	/**
	 * Gets the link Id of an embedded Cblock
	 *
	 * @param int $cbId Embedded Cblock Id
	 * @return int|false Link Id of the embedded Cblock or FALSE in case of an error
	 */
	function getEmbeddedCblockLinkId($cbId) {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RREAD")) {
			$version = (int)$this->getVersion();
			if ($pageID < 1) {
				return false;
			}
			$sql = "SELECT ID FROM `yg_site_" . $this->_site . "_lnk_cb` WHERE PID = $pageID AND PVERSION = $version;";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra[0]['ID'];
		} else {
			return false;
		}
	}

	/**
	 * Assigns a Cblock to a specific Contentarea in this Page
	 *
	 * @param int $cbId Cblock Id
	 * @param string $contentarea Contentarea code
	 * @return int|false Link Id of the newly assigned Cblock or FALSE in case of an error
	 * @throws Exception
	 */
	function addCblockLink($cbId, $contentarea) {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			$cbId = (int)$cbId;
			$contentarea = mysql_real_escape_string(sanitize($contentarea));

			// Check if contentblock is blind or not
			$sql = "SELECT
			*, pv.*
			FROM (yg_contentblocks_properties, yg_contentblocks_tree)
			LEFT JOIN yg_contentblocks_propsv AS pv ON pv.OID = yg_contentblocks_properties.ID
			WHERE OBJECTID = $cbId AND yg_contentblocks_properties.OBJECTID = yg_contentblocks_tree.ID";
			$ra = $this->cacheExecuteGetArray($sql);
			if ((count($ra) > 0) && ($ra[0]['EMBEDDED'] == 1)) {
				// Blind contentblock
				$tmpCblock = sCblockMgr()->getCblock($cbId);
				$cbVersion = $tmpCblock->getVersion();
			} else {
				// Normal contentblock
				$cbVersion = ALWAYS_LATEST_APPROVED_VERSION;
			}

			$version = (int)$this->getVersion();

			$sql = "INSERT INTO `yg_site_" . $this->_site . "_lnk_cb`
			(`CBID`, `CBVERSION`, `PID`, `PVERSION`, `TEMPLATECONTENTAREA`)
			VALUES
			('$cbId', '$cbVersion', '$pageID', '$version', '$contentarea' );";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$insertid = sYDB()->Insert_ID();

			$this->markAsChanged();
			return $insertid;
		} else {
			return false;
		}
	}

	/**
	 * Adds a specific version of a Cblock to a Contentarea in this Page
	 *
	 * @param int $cbId Cblock Id
	 * @param string $contentarea Contentarea code
	 * @param int $version Cblock version
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function addCblockVersion($cbId, $contentarea, $version) {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			$cbId = (int)$cbId;
			$version = (int)$version;
			$contentarea = mysql_real_escape_string(sanitize($contentarea));
			$pageVersion = (int)$this->getVersion();
			$sql = "UPDATE
						`yg_site_" . $this->_site . "_lnk_cb`
					SET
						CBVERSION = $version
					WHERE
						PID = $pageID AND
						PVERSION = $pageVersion AND
						CBID = $cbId AND
						TEMPLATECONTENTAREA = '$contentarea';";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes an assignment of a Cblock to this Page
	 *
	 * @param int $linkId Link Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function removeCblockByLinkId($linkId) {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			$linkId = (int)$linkId;
			$version = (int)$this->getVersion();

			$sql = "DELETE FROM `yg_site_" . $this->_site . "_lnk_cb` WHERE (ID = '$linkId');";

			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->markAsChanged();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes a specific Cblock from a Contentarea in this Page
	 *
	 * @param int $cbId Cblock Id
	 * @param string $contentarea Contentarea code
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function removeCblock($cbId, $contentarea) {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			$cbId = (int)$cbId;
			$contentarea = mysql_real_escape_string(sanitize($contentarea));
			$version = (int)$this->getVersion();

			$sql = "DELETE FROM `yg_site_" . $this->_site . "_lnk_cb` WHERE (PID = $pageID) AND (PVERSION = $version) AND (CBID = $cbId) AND (TEMPLATECONTENTAREA = '$contentarea');";

			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Saves the order of Cblocks in this Page
	 *
	 * @param array $cbListOrder Array of link Ids
	 * @return bool TRUE on success, FALSE in case of an error
	 * @throws Exception
	 */
	function setCblockLinkOrder($cbListOrder = array()) {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			$contentarea = mysql_real_escape_string(sanitize($contentarea));
			$version = (int)$this->getVersion();
			for ($i = 0; $i < count($cbListOrder); $i++) {
				$sql = "UPDATE `yg_site_" . $this->_site . "_lnk_cb`
						SET ORDERPROD = $i WHERE (ID = " . $cbListOrder[$i] . ");";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the order of Cblocks in a specific Contentarea in this Page
	 *
	 * @param array $cbListOrder Array of Cblock Ids
	 * @param string $contentarea Contentarea code
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setCblockOrder($cbListOrder = array(), $contentarea) {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			$contentarea = mysql_real_escape_string(sanitize($contentarea));
			$version = (int)$this->getVersion();
			for ($i = 0; $i < count($cbListOrder); $i++) {
				$sql = "UPDATE `yg_site_" . $this->_site . "_lnk_cb`
				SET ORDERPROD = $i WHERE (PID = $pageID) AND (CBID = " . $cbListOrder[$i] . ") AND (PVERSION = $version) AND (TEMPLATECONTENTAREA = '$contentarea')";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
			}
			$this->markAsChanged();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets Cblock Page link for a specific link id
	 *
	 * @param int $id Link Id
	 * @return array Link
	 */
	function getCblockLinkById($id) {
		$id = (int)$id;
		$sql = "SELECT
					ID,
					CBID AS CBLOCKID,
					CBVERSION AS CBLOCKVERSION,
					CBPID,
					PID AS PAGEID,
					PVERSION AS PAGEVERSION,
					ORDERPROD,
					TEMPLATECONTENTAREA
				FROM
					yg_site_" . $this->_site . "_lnk_cb
				WHERE
					ID = $id;";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra;
	}

	/**
	 * Gets the version of a Cblock linked to a specific version and Contentarea of this Page
	 *
	 * @param int $version Page version
	 * @param string $contentarea (optional) Contentarea code
	 * @return int Cblock version
	 */
	function getCblockLinkVersion($version, $contentarea = "") {
		$pageID = $this->_id;
		$version = (int)$version;
		$contentarea = mysql_real_escape_string(sanitize($contentarea));
		if (strlen($contentarea) > 0) {
			$filter_contentarea = " AND TEMPLATECONTENTAREA = '$contentarea' ";
		}
		$sql = "SELECT
					CBVERSION
				FROM
					yg_site_" . $this->_site . "_lnk_cb
				WHERE
					PID = $pageID
					$filter_contentarea AND
					PVERSION = $version;";
		$ra = $this->cacheExecuteGetArray($sql);
		$v = $ra[0]["CBVERSION"];
		if (strlen($v) == 0) {
			$v = 0;
		}
		return $v;
	}

	/**
	 * Gets a list of Cblocks in a Contentarea for the version of this Page
	 */
	function getEmbeddedCblocksOfAllVersions() {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RREAD")) {
			$sql = "SELECT
			co.OBJECTID as OBJECTID, co.FOLDER as FOLDER, co.VERSION as VERSION, co.EMBEDDED AS EMBEDDED, co.HASCHANGED AS HASCHANGED, yg_contentblocks_tree.PNAME as PNAME, lnk.ID AS LINKID
			FROM
			(yg_site_" . $this->_site . "_lnk_cb AS lnk, yg_contentblocks_properties as co)
			LEFT JOIN yg_contentblocks_tree ON co.OBJECTID = yg_contentblocks_tree.ID
			WHERE
			(
				(lnk.CBID = co.OBJECTID) AND
				(yg_contentblocks_tree.ID = co.OBJECTID) AND
				(
					(lnk.CBVERSION = co.VERSION) OR
					(
						(lnk.CBVERSION = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND
						(co.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (co.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
					)
				) AND
				(lnk.PID = $pageID) AND
				(co.DELETED = 0) AND co.EMBEDDED = 1
			) ";
			$sql .= " GROUP BY co.OBJECTID ORDER BY ORDERPROD ASC";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Gets a list of Cblocks in a Contentarea for the version of this Page
	 *
	 * @param string $contentarea (optional) Contentarea code
	 * @param bool $workingCopy (optional) Set to TRUE to return the latest version (working copy)
	 * @param bool $embedded (optional) Set to TRUE to return only return embedded Contenblocks
	 * @return array Array of Cblocks
	 */
	function getCblockList($contentarea = "", $workingCopy = false, $embedded = false) {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RREAD")) {
			$version = $this->getVersion();

			if ($contentarea != "") {
				$contentarea = mysql_real_escape_string(sanitize($contentarea));
				$contentarea_sql = " AND (lnk.TEMPLATECONTENTAREA = '$contentarea')";
			}

			if ($embedded) {
				$embedded_sql = " AND co.EMBEDDED = 1";
			}

			if (($workingCopy == true) || ($version == $this->getLatestVersion())) {
				$maxcoversion = true;
			}

			$perm_sql_select = ", coperm.RREAD AS RREAD ";
			$perm_sql_from = " LEFT JOIN yg_contentblocks_permissions AS coperm ON ";
			$perm_sql_where = "  (coperm.OID = co.OBJECTID) AND (coperm.RREAD > 0) AND (";
			$roles = $this->permissions->getUsergroups();
			for ($r = 0; $r < count($roles); $r++) {
				$perm_sql_where .= "(coperm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
				if ((count($roles) - $r) > 1) {
					$perm_sql_where .= " OR ";
				}
			}
			$perm_sql_where .= ") ";

			if ($maxcoversion == true) {
				$sql = "SELECT
				co.OBJECTID as OBJECTID, co.FOLDER as FOLDER, co.VERSION as VERSION, co.EMBEDDED AS EMBEDDED, co.HASCHANGED AS HASCHANGED, yg_contentblocks_tree.PNAME as PNAME, lnk.ID AS LINKID
				$perm_sql_select
				FROM
				(yg_site_" . $this->_site . "_lnk_cb AS lnk, yg_contentblocks_properties as co)
				LEFT JOIN yg_contentblocks_tree ON co.OBJECTID = yg_contentblocks_tree.ID
				$perm_sql_from $perm_sql_where
				WHERE
				(
					(co.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (co.OBJECTID = rgt.OBJECTID))) AND
					(lnk.CBID = co.OBJECTID) AND
					(yg_contentblocks_tree.ID = co.OBJECTID) AND
					(lnk.PID = $pageID) AND
					(co.DELETED = 0) AND
					(lnk.PVERSION = $version)
					$contentarea_sql $embedded_sql
				) ";
			} else {
				$sql = "SELECT
				co.OBJECTID as OBJECTID, co.FOLDER as FOLDER, co.VERSION as VERSION, co.EMBEDDED AS EMBEDDED, co.HASCHANGED AS HASCHANGED, yg_contentblocks_tree.PNAME as PNAME, lnk.ID AS LINKID
				$perm_sql_select
				FROM
				(yg_site_" . $this->_site . "_lnk_cb AS lnk, yg_contentblocks_properties as co)
				LEFT JOIN yg_contentblocks_tree ON co.OBJECTID = yg_contentblocks_tree.ID
				$perm_sql_from $perm_sql_where
				WHERE
				(
					(lnk.CBID = co.OBJECTID) AND
					(yg_contentblocks_tree.ID = co.OBJECTID) AND
					(
						(lnk.CBVERSION = co.VERSION) OR
						(
							(lnk.CBVERSION = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND
							(co.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (co.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
						)
					) AND
					(lnk.PID = $pageID) AND
					(co.DELETED = 0) AND
					(lnk.PVERSION = $version)
				$contentarea_sql $embedded_sql
				) ";
			}
			$sql .= " GROUP BY co.OBJECTID ORDER BY ORDERPROD ASC";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Assigns a Template to this Page by using the Template identifier
	 *
	 * @param string $identifier Template identifier
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setTemplateByIdentifier($identifier) {
		$templates = new Templates();
		$templateInfo = $templates->getByIdentifier($identifier);
		return $this->setTemplate($templateInfo['OBJECTID']);
	}

	/**
	 * Assigns a Template to this Page
	 *
	 * @param int $templateId Template Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setTemplate($templateId) {
		$pageID = $this->_id;
		$templateId = (int)$templateId;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {

			// Get old navigation
			$pageMgr = new PageMgr($siteID);
			$templateMgr = new Templates();
			$pageInfo = $this->get();
			$oldTemplateId = $pageInfo['TEMPLATEID'];
			$oldNaviId = $pageInfo['NAVIGATIONID'];
			$navis = $templateMgr->getNavis($oldTemplateId);
			for ($i = 0; $i < count($navis); $i++) {
				if ($navis[$i]["ID"] == $oldNaviId) {
					$naviInfo = $navis[$i];
				}
			}
			$oldNaviInfo = $naviInfo;

			// Get navigations for new template
			$navis = $templateMgr->getNavis($templateId);
			for ($i = 0; $i < count($navis); $i++) {
				if ($navis[$i]['CODE'] == $oldNaviInfo['CODE']) {
					$matchingNavi = $navis[$i]['ID'];
				}
			}

			$version = (int)$this->getVersion();
			//get current state
			$sql = "SELECT TEMPLATEID AS STATE FROM yg_site_" . $this->_site . "_properties WHERE (OBJECTID = $pageID) AND VERSION = $version;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$ra = $result->GetArray();
			$state = $ra[0]["STATE"];

			$sql = "UPDATE yg_site_" . $this->_site . "_properties SET TEMPLATEID = '$templateId' WHERE (OBJECTID = $pageID) AND VERSION = $version;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			if ($matchingNavi) {
				// Matching new navigation was found, set to matching navi
				$this->setNavigation($matchingNavi);
			} else {
				// Get all navigations and check if a default navigation is set (if no matching navi was found)
				$navigations = $templateMgr->getNavis($templateId);
				$hasdefaultnavi = false;
				foreach ($navigations as $idx => $navigation) {
					if ($navigation['DEFAULT'] == 1) {
						$hasdefaultnavi = true;
						$defaultnavi = $navigation['ID'];
					}
				}
				if ($hasdefaultnavi) {
					$this->setNavigation($defaultnavi);
				} else {
					// No default navigation was found, set to nothing (and thus hide the hide page)
					$this->setNavigation(0);
				}
			}

			$this->markAsChanged();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets Navigation of this Page
	 *
	 * @param string $navigation Navigation code
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setNavigation($navigation) {
		$pageID = $this->_id;
		$navigation = mysql_real_escape_string(sanitize($navigation));
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			$version = (int)$this->getVersion();
			//get current state
			$sql = "SELECT NAVIGATION AS STATE FROM yg_site_" . $this->_site . "_properties WHERE (OBJECTID = $pageID) AND VERSION = $version;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$ra = $result->GetArray();
			$state = $ra[0]["STATE"];

			$sql = "UPDATE yg_site_" . $this->_site . "_properties SET NAVIGATION = '$navigation' WHERE (OBJECTID = $pageID) AND VERSION = $version;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			if ($navigation == 0) {
				$this->setHidden(1);
			} else {
				$this->setHidden(0);
			}

			$this->markAsChanged();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the "active" state of this Page
	 *
	 * @param int $value Active status (0 or 1)
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setActive($value) {
		$pageID = $this->_id;
		$value = (int)$value;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			$version = (int)$this->getVersion();
			//get current state
			$sql = "SELECT ACTIVE AS STATE FROM yg_site_" . $this->_site . "_properties WHERE (OBJECTID = $pageID) AND VERSION = $version;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$ra = $result->GetArray();
			$state = $ra[0]["STATE"];

			$sql = "UPDATE yg_site_" . $this->_site . "_properties SET ACTIVE = '$value' WHERE (OBJECTID = $pageID) AND VERSION = $version;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->markAsChanged();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set "hidden" state of this Page
	 *
	 * @param int $value Hidden state (0 or 1)
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setHidden($value) {
		$pageID = $this->_id;
		$value = (int)$value;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			$version = (int)$this->getVersion();
			//get current state
			$sql = "SELECT HIDDEN AS STATE FROM yg_site_" . $this->_site . "_properties WHERE (OBJECTID = $pageID) AND VERSION = $version;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$ra = $result->GetArray();
			$state = $ra[0]["STATE"];

			$sql = "UPDATE yg_site_" . $this->_site . "_properties SET HIDDEN = '$value' WHERE (OBJECTID = $pageID) AND VERSION = $version;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->markAsChanged();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Cleans up the provided permanent name
	 *
	 * @param string $pname Permanent name
	 * @return string Clean permanent name
	 */
	function filterPName($pname) {
		$pname = sanitize($pname);
		$pname = str_replace(" ", "_", $pname);
		$pname = str_replace("&", "_", $pname);
		$pname = str_replace("/", "_", $pname);
		$pname = str_replace("\\", "_", $pname);
		$pname = str_replace("?", "_", $pname);
		$pname = str_replace("#", "_", $pname);
		$pname = str_replace(":", "_", $pname);
		$pname = str_replace("%", "_", $pname);
		$pname = str_replace("'", "", $pname);
		$pname = str_replace('"', "", $pname);
		return $pname;
	}

	/**
	 * Gets the URL of this Page
	 *
	 * @return string URL of this Page
	 */
	function getUrl() {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RREAD")) {
			$pageMgr = new PageMgr($this->_site);
			$pnames = $pageMgr->getParents($pageID);
			$pi = count($pnames);
			while ($pi > 0) {
				if ($pnames[$pi - 1][0]["PNAME"] != "") $url .= $pnames[$pi - 1][0]["PNAME"] . "/";
				$pi--;
			}
			$pinfo = $this->get();
			return sApp()->webroot . $url . $pinfo["PNAME"] . "/";
		} else {
			return false;
		}
	}

	/**
	 * Sets the permanent name of this Page
	 *
	 * @param string $pname Permanent name
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function setPName($pname) {
		$pageID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $pageID, "RWRITE")) {
			$pname = $this->filterPName($pname);

			if (is_numeric($pname)) {
				return false;
			}

			$pageMgr = new PageMgr($this->getSite());
			$checkpinfo = $pageMgr->getPageIdByPname($pname);
			if (($checkpinfo["ID"] != $pageID) && ($checkpinfo["ID"] > 0)) {
				$pname = $pname . $page;
			}
			$version = (int)$this->getVersion();

			$sql = "SELECT PNAME AS STATE FROM yg_site_" . $this->_site . "_tree WHERE (ID = $pageID);";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$ra = $result->GetArray();
			$state = $ra[0]["STATE"];

			$sql = "UPDATE yg_site_" . $this->_site . "_tree SET PNAME = '$pname' WHERE (ID = $pageID);";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/PNAME_CHANGE") == "true") {
				Singleton::FC()->emptyBucket();
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Calculates a unique permanent name for this Page
	 *
	 * @param string $iteration (optional) Iteration
	 * @return string Permanent name
	 */
	function calcPName($iteration = "") {
		$pageID = $this->_id;
		$pinfo = $this->get();
		$pagename = $pinfo["NAME"];
		if ((int)sConfig()->getVar("CONFIG/CASE_SENSITIVE_URLS") == 0) {
			$pagename = strtolower($pagename);
		}
		$pname = $this->filterPName($pagename);
		if (is_numeric($pname)) {
			$pname = 'page_'.$pname;
		}
		$pageMgr = new PageMgr($this->getSite());
		if ($iteration != '') {
			$checkpinfo = $pageMgr->getPageIdByPname($pname . '_' . $iteration);
		} else {
			$checkpinfo = $pageMgr->getPageIdByPname($pname);
		}
		if ($checkpinfo["ID"] == $pageID) {
			if ($iteration != '') {
				return $pname . '_' . $iteration;
			} else {
				return $pname;
			}
		} else {
			if ($checkpinfo["ID"] == NULL) {
				if ($iteration != '') {
					return $pname . '_' . $iteration;
				} else {
					return $pname;
				}
			} else {
				if ($iteration == "") {
					$iteration = 1;
				}
				return $this->calcPName(++$iteration);
			}
		}
	}

	/**
	 * Helper method for processing of scheduled approvals
	 */
	function processSchedule() {
		$todo = $this->scheduler->getJobs();
		for ($i = 0; $i < count($todo); $i++) {
			$params = $todo[$i]["PARAMETERS"];
			$pid = $todo[$i]["OBJECTID"];
			switch ($todo[$i]["ACTIONCODE"]) {
				case "SCH_AUTOPUBLISH";
					if ($params["VERSION"] == ALWAYS_LATEST_APPROVED_VERSION) {
						$latestfinal = $this->getLatestApprovedVersion($pid);
						$this->publishversion($pid, $latestfinal);
					} else {
						$this->publishversion($pid, $params["VERSION"]);
					}
					$this->scheduler->removeJob($todo[$i]["ID"]);
					break;
			}
		}
	}
}

?>