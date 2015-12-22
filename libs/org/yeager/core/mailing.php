<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The Mailing class, which represents an instance of a Mailing.
 */

class Mailing extends Versionable {
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
	 * Constructor of the Mailing class
	 *
	 * @param int $mailingID Mailing Id
	 * @param int $version Version
	 */
	public function __construct($mailingID = 0, $version = 0) {
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->_id = $mailingID;
		$this->initTables();
		$this->permissions = new Permissions($this->_table_permissions, $this);
		parent::__construct($this->_id, $version, $this->_table_object, $this->_table_tree, $this->permissions);
		$this->history = new History($this, $this->_id_history, $this->permissions);
		$this->tags = new Tags($this);
		$this->comments = new Comments($this);
		$this->properties = new Properties($this->_table_properties, $this->getPropertyId(), $this);
		$this->scheduler = new Scheduler($this->_table_scheduler, SCHEDULER_MAILING);
	}

/// @cond DEV

	/**
	 * Initializes internal class members
	 */
	private function initTables() {
		$this->_table_object = "yg_mailing_properties";
		$this->_table_permissions = "yg_mailing_permissions";
		$this->_id_history = "mailing";
		$this->_table_tree = "yg_mailing_tree";
		$this->_table_properties = "yg_mailing_props";
		$this->_table_taglinks = "yg_tags_lnk_mailings";
		$this->_table_commentlinks = "yg_comments_lnk_mailing";
		$this->_table_scheduler = "yg_mailing_cron";
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the object prefix, used for table names in database queries
	 *
	 * @return string Objectprefix
	 */
	function getObjectPrefix() {
		return "mailings";
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets additional identifier
	 *
	 * @return array Additional identifier
	 */
	function getAdditionalIdentifier() {
		return array();
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table which contains the properties of Mailings
	 *
	 * @return string Tablename
	 */
	function getPropertyTable() {
		return "yg_mailing_properties";
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table which contains links between Comments and Mailings
	 *
	 * @return string Tablename
	 */
	function getCommentsLinkTable() {
		return $this->_table_commentlinks;
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table which contains the permissions for Mailings
	 *
	 * @return string Tablename
	 */
	function getPermissionsTable() {
		return $this->_table_permissions;
	}

/// @endcond

	/**
	 * Callback method which is executed when Usergroup permissions on this Mailing change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 */
	public function onPermissionChange($usergroupId, $permission, $value) {
		// Also set these permissions to the all blinds Cblocks in this Mailing
		$objectid = (int)$this->_id;
		$templateMgr = new Templates();

		$mailingVersions = $this->getVersions();

		foreach ($mailingVersions as $mailingVersions_item) {
			$tmpMailing = sMailingMgr()->getMailing($objectid, $mailingVersions_item['VERSION']);
			$mailingInfo = $tmpMailing->get();
			$contentareas = $templateMgr->getContentareas($mailingInfo['TEMPLATEID']);

			$blindCos = array();
			foreach ($contentareas as $contentareaItem) {
				$colist = $tmpMailing->getCblockList($contentareaItem['CODE']);
				foreach ($colist as $colistItem) {
					if ($colistItem['ID'] > 0) {
						$cb = sCblockMgr()->getCblock($colistItem['ID']);
						$coInfo = $cb->get();
						if ($coInfo['EMBEDDED'] == 1) {
							array_push($blindCos, $colistItem['ID']);
						}
					}
				}
			}
		}

		$blindCos = array_unique($blindCos);

		$mailingPermissions = $this->permissions->getByUsergroup($usergroupId, $objectid);
		if ($mailingPermissions) {
			foreach ($blindCos as $coid) {
				$bcb = sCblockMgr()->getCblock($coid);
				$bcb->permissions->setByUsergroup($usergroupId, $permission, $coid, $value);
			}
		}
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on this Mailing change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permissions (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 */
	public function onPermissionsChange($usergroupId, $permissions, $value) {
		$hadError = false;
		foreach($permissions as $permission) {
			if (!$this->onPermissionChange($usergroupId, $permission, $value)) {
				$hadError = true;
			}
		}
		if ($hadError) {
			return false;
		}
		return true;
	}

/// @cond DEV

	/**
	 * Gets the name of the database table containing the Mailings tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return "yg_mailing_tree";
	}

/// @endcond

	/**
	 * Calls a specific Extension hook Callback method
	 *
	 * @param string $method
	 * @param int $mailingID Mailing Id
	 * @param int $version Mailing version
	 * @param mixed $args Arbitrary arguments
	 */
	function callExtensionHook($method, $mailingID, $version, $args) {
		$extensions = new ExtensionMgr($this->_db, $this->_uid);
		$all_mailing_extensions = $extensions->getList(EXTENSION_MAILING, true);
		$extarr = array();
		foreach ($all_mailing_extensions as $all_mailing_extension) {
			$extension = $extensions->getExtension($all_mailing_extension['CODE']);
			if ($extension && $extension->usedByMailing($mailingID, $version) === true) {
				$extension = $extensions->getExtension($all_mailing_extension['CODE'], $mailingID, $version);
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
	function cacheExecuteGetArray() {
        $args = func_get_args();
        $dbr = call_user_func_array(array(sYDB(), 'Execute'), $args);
        if ($dbr === false) {
            throw new Exception(sYDB()->ErrorMsg() . ':: ' . $sql);
        }
        $blaetter = $dbr->GetArray();
        return $blaetter;
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets all Contentareas of this Mailing including Cblocks and content
	 *
	 * @return array
	 */
	function getContentInternal() {
		$mailingId = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingId, "RREAD")) {
			$templateMgr = new Templates();
			$mailingInfo = $this->get();

			$contentareas = $templateMgr->getContentareas($mailingInfo['TEMPLATEID']);
			for ($i = 0; $i < count($contentareas); $i++) {
				$colist = $this->getCblockList($contentareas[$i]['CODE']);
				for ($x = 0; $x < count($colist); $x++) {
					if ($colist[$x]['OBJECTID'] > 0) {
						$cb = sCblockMgr()->getCblock($colist[$x]['OBJECTID'], $colist[$x]['VERSION']);
						$colist[$x]['CBVERSION'] = $colist[$x]['VERSION'];
						$colist[$x]['ENTRYMASKS'] = $cb->getEntrymasks();
						for ($c = 0; $c < count($colist[$x]['ENTRYMASKS']); $c++) {
							$controlFormfields = $cb->getFormfieldsInternal($colist[$x]['ENTRYMASKS'][$c]['LINKID']);
							for ($w = 0; $w < count($controlFormfields); $w++) {
								if ($controlFormfields[$w]['FORMFIELD'] == 6) {
									if (trim($controlFormfields[$w]['VALUE01'])) {
										$file = new File($controlFormfields[$w]['VALUE01']);
										$fileInfo = $file->get();
										$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
									}
								}
								if ($controlFormfields[$w]['FORMFIELD'] == 7) {
									if (trim($controlFormfields[$w]['VALUE01'])) {
										$icb = sCblockMgr()->getCblock($controlFormfields[$w]['VALUE01']);
										$info = $icb->get();
										$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
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
					}
				}
				$contentareas[$i]['LIST'] = $colist;
			}
			return $contentareas;
		} else {
			return false;
		}
	}

/// @endcond

	/**
	 * Gets all Contentareas of this Mailing including Cblocks and content
	 *
	 * @return array Array of Contentareas including Cblocks, Entrymasks and Formfields
	 */
	function getContent() {
		$mailingId = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingId, "RREAD")) {
			$templateMgr = new Templates();
			$mailingInfo = $this->get();

			$contentareas = $templateMgr->getContentareas($mailingInfo['TEMPLATEID']);
			$namedContentareas = array();
			for ($i = 0; $i < count($contentareas); $i++) {
				$colist = $this->getCblockList($contentareas[$i]['CODE']);
				for ($x = 0; $x < count($colist); $x++) {
					if ($colist[$x]['OBJECTID'] > 0) {
						$colist[$x]['CBLOCKVERSION'] = $colist[$x]['VERSION'];
						$cb = sCblockMgr()->getCblock($colist[$x]['OBJECTID'], $colist[$x]['VERSION']);
						$colist[$x]['ENTRYMASKS'] = $cb->getEntrymasks();
						for ($c = 0; $c < count($colist[$x]['ENTRYMASKS']); $c++) {
							$controlFormfields = $cb->getFormfields($colist[$x]['ENTRYMASKS'][$c]['LINKID']);
							$namedFormfields = array();
							for ($w = 0; $w < count($controlFormfields); $w++) {
								// File
								if ($controlFormfields[$w]['FORMFIELD'] == 6) {
									if ($controlFormfields[$w]['FILE_ID']) {
										$file = new File($controlFormfields[$w]['FILE_ID']);
										$fileInfo = $file->get();
										$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
									}
								}
								// Cblock
								if ($controlFormfields[$w]['FORMFIELD'] == 7) {
									if ($controlFormfields[$w]['CBLOCK_ID']) {
										$icb = sCblockMgr()->getCblock($controlFormfields[$w]['CBLOCK_ID']);
										$info = $icb->get();
										$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
									}
								}
								// Tag
								if ($controlFormfields[$w]['FORMFIELD'] == 8) {
									if ($controlFormfields[$w]['TAG_ID']) {
										$info = $this->tags->get($controlFormfields[$w]['TAG_ID']);
										$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
									}
								}
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
				$namedContentareas[$contentareas[$i]['CODE']]['CBLOCKS'] = $colist;
			}
			return $namedContentareas;
		} else {
			return false;
		}
	}

	/**
	 * Gets all versions of the Mailing which contain the specified Cblock
	 *
	 * @param int $cbId Cblock Id
	 * @return array|false Array of versions or FALSE in case of an error
	 */
	function getVersionsByCblockId($cbId) {
		$mailingID = $this->_id;
		$cbId = (int)$cbId;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RREAD")) {
			$sql = "SELECT prop.VERSION AS VERSION FROM `yg_mailing_properties` as prop, `yg_mailing_lnk_cb` as lnk WHERE (prop.OBJECTID = $mailingID) AND (lnk.PID = $mailingID) AND (lnk.CBID = $cbId) AND (lnk.PVERSION = prop.VERSION) ORDER BY VERSION DESC";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Generates a new version of this Mailing by copying the currently instanced version
	 * and updates the currently instanced Object to the new version
	 *
	 * @return int|false New version of this Mailing or FALSE in case of an error
	 */
	public function newVersion() {
		$mailingID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			$colist = $this->getCblockList();
			$sourceVersion = $this->getVersion();
			$sourceObject = sMailingMgr()->getMailing($this->_id, $sourceVersion);
			if ($sourceVersion == $this->getLatestVersion()) {
				$historyIdentifier = 'TXT_MAILING_H_NEWVERSION';
				$historySourceVersion = NULL;
			} else {
				$historyIdentifier = 'TXT_MAILING_H_NEWVERSION_FROM';
				$historySourceVersion = $sourceVersion;
			}
			$newVersion = parent::newVersion();
			$this->properties = new Properties($this->_table_properties, $this->getPropertyId(), $this);
			$this->tags->copyTo($mailingID, $sourceVersion, $mailingID, $newVersion);
			$this->copyCblockLinks($sourceVersion, $newVersion);
			$this->copyUsergroupLinks($sourceVersion, $newVersion);
			$this->copyExtensionsFrom($sourceObject);

			// Check if there are blind entrymasks in this mailing (and add a version)
			foreach ($colist as $colist_item) {
				if ($colist_item['EMBEDDED'] == 1) {
					$tmpCb = sCblockMgr()->getCblock($colist_item['OBJECTID'], $colist_item['VERSION']);
					$version = $tmpCb->newVersion();
					$CoLnkInfo = $this->getCblockLinkById($colist_item['LINKID']);
					$this->addCblockVersion($colist_item['OBJECTID'], $CoLnkInfo[0]['TEMPLATECONTENTAREA'], $version);
				}
			}
			sMailingMgr()->callExtensionHook('onVersionNew', (int)$this->_id, $this->getVersion());

			// Add to history
			$this->history->add(HISTORYTYPE_MAILING, $historySourceVersion, $newVersion, $historyIdentifier);

			return $newVersion;
		} else {
			return false;
		}
	}

	/**
	 * Copies the Cblock Mailing links from one version to another
	 *
	 * @param int $sourceVersion Source version
	 * @param int $targetVersion Target version
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	private function copyCblockLinks($sourceVersion, $targetVersion) {
		$mailingID = (int)$this->_id;
		$sourceVersion = (int)$sourceVersion;
		$targetVersion = (int)$targetVersion;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			// co links rueberziehen
			$sql = "INSERT INTO `yg_mailing_lnk_cb`
			(PVERSION, PID, CBVERSION, CBID, ORDERPROD,TEMPLATECONTENTAREA)
			SELECT $targetVersion,PID, CBVERSION, CBID, ORDERPROD,TEMPLATECONTENTAREA
			FROM `yg_mailing_lnk_cb` WHERE (PID = ?) AND (PVERSION = ?);
			";
			$result = sYDB()->Execute($sql, $mailingID, $sourceVersion);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Copies the linked Usergroups from one version to another
	 *
	 * @param int $sourceVersion Source version
	 * @param int $targetVersion Target version
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	private function copyUsergroupLinks($sourceVersion, $targetVersion) {
		$mailingID = (int)$this->_id;
		$sourceVersion = (int)$sourceVersion;
		$targetVersion = (int)$targetVersion;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			// group links rueberziehen
			$sql = "INSERT INTO `yg_mailing_lnk_usergroups`
				(NVERSION, NID , RID)
				SELECT $targetVersion, NID, RID
				FROM `yg_mailing_lnk_usergroups` WHERE (NID = ?) AND (NVERSION = ?);";
			$result = sYDB()->Execute($sql, $mailingID, $sourceVersion);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Copies Properties, Extensions, Usergroups, Permissions, Tags, assigned and embedded Cblocks
	 * from another Mailing into a new version of this Mailing
	 *
	 * @param object $sourceMailing Source Mailing object
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function copyFrom(&$sourceMailing) {
		$targetID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $targetID, "RWRITE")) {
			$sourceInfo = $sourceMailing->get();
			$targetVersion = $this->getVersion();
			parent::copyFrom($sourceMailing);

			$this->setTemplate($sourceInfo['TEMPLATEID']);

			$assignedGroups = $sourceMailing->getUsergroups();
			foreach ($assignedGroups as $assignedGroup) {
				if (sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $assignedGroup['ID'], 'RREAD')) {
					$this->addUsergroup($assignedGroup['ID'], $targetVersion);
				}
			}
			$this->copyExtensionsFrom($sourceMailing);

			// Clear contentareas
			$mailingInfo = $this->get();
			$templateMgr = new Templates();
			$contentareas = $templateMgr->getContentareas($mailingInfo['TEMPLATEID']);
			for ($i = 0; $i < count($contentareas); $i++) {
				$mailinglist = $this->getCblockList($contentareas[$i]['CODE']);
				for ($x = 0; $x < count($mailinglist); $x++) {
					$this->removeCblock($mailinglist[$x]['ID'], $contentareas[$i]['CODE']);
				}
			}

			$templateMgr = new Templates();
			$mailingInfo = $sourceMailing->get();
			$contentareas = $templateMgr->getContentareas($mailingInfo['TEMPLATEID']);
			for ($i = 0; $i < count($contentareas); $i++) {
				$cbListOrder = array();
				$mailinglist = $sourceMailing->getCblockList($contentareas[$i]['CODE']);

				for ($x = 0; $x < count($mailinglist); $x++) {
					$coid = $mailinglist[$x]['OBJECTID'];

					// Check if we have a blind contentblock
					if ($mailinglist[$x]['EMBEDDED'] == 1) {
						// Yes, we have to copy it to the blind folder

						// Check which entrymasks are contained
						$srcCo = sCblockMgr()->getCblock($coid, $mailinglist[$x]['VERSION']);
						$src_entrymasks = $srcCo->getEntrymasks();

						// Create blind contentblocks with these entrymasks
						foreach ($src_entrymasks as $src_entrymask_item) {
							// Add new contentblock to folder
							$contentblockID = $this->addCblockEmbedded($contentareas[$i]['CODE']);
							$newCo = sCblockMgr()->getCblock($contentblockID);
							$newCo->properties->setValue("NAME", $src_entrymask_item['ENTRYMASKNAME']);

							// Add requested control to contentblock
							$new_control = $newCo->addEntrymask($src_entrymask_item['ENTRYMASKID']);

							// Loop through all formfields
							$controlFormfields = $srcCo->getFormfieldsInternal($src_entrymask_item['LINKID']);
							$newControlFormfields = $newCo->getFormfieldsInternal($new_control);

							// Fill all formfield parameter values with content from the source formfield
							for ($c = 0; $c < count($newControlFormfields); $c++) {
								$newCo->setFormfield($newControlFormfields[$c]['ID'],
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
					} else {
						// No, it's a normal one, just link it to the mailing
						$this->addCblockLink($coid, $contentareas[$i]['CODE']);
					}
					$cbListOrder[] = $coid;
				}
				$this->setCblockOrder($cbListOrder, $contentareas[$i]['CODE']);
			}
			$this->markAsChanged();
		} else {
			return false;
		}
	}

	/**
	 * Copies Extensions from another Mailing to this Mailing
	 *
	 * @param object $sourceObject Source Mailing object
	 */
	function copyExtensionsFrom(&$sourceObject) {
		$sourceId = $sourceObject->getID();
		$sourceVersion = $sourceObject->getVersion();
		$targetId = $this->getID();
		$targetVersion = $this->getVersion();
		$extensions = new ExtensionMgr(sYDB(), $this->_uid);
		$all_extensions = $extensions->getList(EXTENSION_MAILING, true);
		foreach ($all_extensions as $all_extension) {
			$extension = $extensions->getExtension($all_extension['CODE']);
			if ($extension && ($extension->usedByMailing($sourceId, $sourceVersion) === true)) {
				if ($extension->usedByMailing($targetId, $targetVersion) !== true) {
					$newfid = $extension->addToMailingInternal($targetId, $targetVersion);
				}
				$extension = $extensions->getExtension($all_extension['CODE'], $targetId, $targetVersion);
				$sourceext = $extensions->getExtension($all_extension['CODE'], $sourceId, $sourceVersion);
				if ($extension && $sourceext) {
					$newfid = $extension->getPropertyId();
					$oldfid = $sourceext->getPropertyId();
					$extension->properties->copyTo($oldfid, $newfid);
				}
			}
		}
	}

	/**
	 * Sets the state of a specific version of this Mailing to "published"
	 *
	 * @param int $version (optional) Specific version or ALWAYS_LATEST_APPROVED_VERSION (constant) to always publish the lastest approved version
	 * @return int|false New version of the Mailing or FALSE in case of an error
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
				$this->history->add(HISTORYTYPE_MAILING, NULL, $version, 'TXT_PAGE_H_PUBLISH');
				sMailingMgr()->callExtensionHook("onPublish", $this->_id, $version);
				if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/MAILING_PUBLISH") == "true") {
					Singleton::FC()->emptyBucket();
				}
			}
		}
		return $result;
	}

	/**
	 * Approves the current version of this Mailing and all embedded Cblocks and creates a new working version
	 *
	 * @return int|false New version or FALSE in case of an error
	 */
	function approve() {
		$mailingID = $this->_id;
		$this->setStatus('UNSENT');
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RSTAGE")) {
			$this->approveVersion();
			return $this->newVersion();
		} else {
			return false;
		}
	}

	/**
	 * Approves the specified version of this Mailing and all embedded Cblocks and creates a new working version
	 *
	 * @param int $version (optional) Mailing version
	 * @return int|false New version or FALSE in case of an error
	 */
	public function approveVersion($version = 0) {
		$result = parent::approveVersion($version);

		// Check if there are blind entrymasks in this mailing (and add a version)
		$colist = $this->getCblockList();
		foreach ($colist as $colist_item) {
			if ($colist_item['EMBEDDED'] == 1) {
				$tmpCb = sCblockMgr()->getCblock($colist_item['OBJECTID'], $colist_item['VERSION']);
				$tmpCb->approveVersion();
			}
		}

		if ((int)$version == 0) $version = (int)$this->getVersion();

		$this->history->add(HISTORYTYPE_MAILING, NULL, $version, 'TXT_MAILING_H_APPROVE');

		if ($this->getPublishedVersion()==ALWAYS_LATEST_APPROVED_VERSION) {
			$this->history->add(HISTORYTYPE_MAILING, NULL, $version, 'TXT_MAILING_H_AUTOPUBLISH');
			sMailingMgr()->callExtensionHook("onPublish", $this->_id, $version);
			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/MAILING_PUBLISH") == "true") {
				Singleton::FC()->emptyBucket();
			}
		}

		sMailingMgr()->callExtensionHook('onApprove', (int)$this->_id, (int)$this->_version);
		return result;
	}

	/**
	 * Gets basic information about this Mailing
	 *
	 * @return array|false Array containing information about this Mailing or FALSE in case of an error
	 */
	function get() {
		$mailingID = $this->_id;
		$version = (int)$this->getVersion();
		if ($mailingID < 1) {
			return false;
		}
		if (strlen($version) < 1) {
			return false;
		}
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RREAD")) {
			$sql = "SELECT * FROM yg_mailing_props WHERE READONLY = 1;";
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
						$dynIdentifers
					FROM
						(yg_mailing_properties as p, yg_mailing_tree as t)
					LEFT JOIN
						yg_mailing_propsv as pv ON pv.OID = p.ID
					WHERE
						(p.OBJECTID = $mailingID AND p.VERSION = $version) AND
						(t.ID = p.OBJECTID);";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra[0];
		} else {
			return false;
		}
	}

	/**
	 * Gets all assigned Usergroups of this Mailing
	 *
	 * @return array Array of Usergroup Ids
	 */
	function getUsergroups() {
		$mailingID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RREAD")) {
			$version = (int)$this->getVersion();
			$sql = "SELECT groups.* FROM yg_mailing_lnk_usergroups AS lnk
			LEFT JOIN yg_usergroups AS groups ON lnk.RID = groups.ID
			WHERE (NID = $mailingID) AND (NVERSION = $version);";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Adds an embedded Cblock to this Mailing
	 *
	 * @param string $contentarea Contentarea code
	 * @return int Cblock Id
	 */
	function addCblockEmbedded($contentarea) {
		$mailingId = (int)$this->_id;

		if ($this->permissions->checkInternal($this->_uid, $mailingId, "RWRITE")) {
			// Get folder for embedded cblocks
			$embeddedCblockFolder = (int)sConfig()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");

			// Add a Cblock
			$embeddedCblockId = sCblockMgr()->add($embeddedCblockFolder);
			$newEmbeddedCblock = sCblockMgr()->getCblock($embeddedCblockId);

			// Set it to "embedded"
			$newEmbeddedCblock->setEmbedded();

			// Add this Cblock to this page
			$embeddedCblockLinkId = $this->addCblockLink($embeddedCblockId, $contentarea);

			// Get an instance of this new embedded Cblock and inherit rights from this page to embedded contentblock
			$usergroups = sUsergroups()->getList();
			foreach ($usergroups as $usergroupItem) {
				$usergroupId = $usergroupItem['ID'];
				$objectPermissions = $this->permissions->getByUsergroup($usergroupId, $mailingId);
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
	 * Gets the link id of an embedded Cblock
	 *
	 * @param int $cbId Embedded Cblock Id
	 * @return int|false Link Id of the embedded Cblock or FALSE in case of an error
	 */
	function getEmbeddedCblockLinkId($cbId) {
		$mailingID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RREAD")) {
			$version = (int)$this->getVersion();
			if ($mailingID < 1) {
				return false;
			}
			$sql = "SELECT ID FROM `yg_mailing_lnk_cb` WHERE PID = $mailingID AND PVERSION = $version;";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra[0]['ID'];
		} else {
			return false;
		}
	}

	/**
	 * Gets a list of Cblocks in a Contentarea for the version of this Page
	 */
	function getEmbeddedCblocksOfAllVersions() {
		$mailingID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RREAD")) {
			$sql = "SELECT
			co.OBJECTID as OBJECTID, co.FOLDER as FOLDER, co.VERSION as VERSION, co.EMBEDDED AS EMBEDDED, co.HASCHANGED AS HASCHANGED, yg_contentblocks_tree.PNAME as PNAME, lnk.ID AS LINKID
			FROM
			(yg_mailing_lnk_cb AS lnk, yg_contentblocks_properties as co)
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
				(lnk.PID = $mailingID) AND
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
	 * Assigns a Cblock to a specific Contentarea in this Mailing
	 *
	 * @param int $cbId Cblock Id
	 * @param string $contentarea Contentarea code
	 * @return int|false Link Id of the newly assigned Cblock or FALSE in case of an error
	 * @throws Exception
	 */
	function addCblockLink($cbId, $contentarea) {
		$mailingID = (int)$this->_id;

		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			$cbId = (int)$cbId;
			$contentarea = sYDB()->escape_string(sanitize($contentarea));

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

			$sql = "INSERT INTO `yg_mailing_lnk_cb`
			(`CBID`, `CBVERSION`, `PID`, `PVERSION`, `TEMPLATECONTENTAREA`)
			VALUES
			(?, ?, ?, ?, ?);";
			$result = sYDB()->Execute($sql, $cbId, $cbVersion, $mailingID, $version, $contentarea);
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
	 * Adds a specific version of a Cblock to a Contentarea in this Mailing
	 *
	 * @param int $cbId Cblock Id
	 * @param string $contentarea Contentarea code
	 * @param int $version Cblock version
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function addCblockVersion($cbId, $contentarea, $version) {
		$mailingID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			$cbId = (int)$cbId;
			$version = (int)$version;
			$contentarea = sYDB()->escape_string(sanitize($contentarea));
			$mailingVersion = (int)$this->getVersion();
			$sql = "UPDATE
						`yg_mailing_lnk_cb`
					SET
						CBVERSION = ?
					WHERE
						PID = ? AND
						PVERSION = ? AND
						CBID = ? AND
						TEMPLATECONTENTAREA = ?;";
			$result = sYDB()->Execute($sql, $version, $mailingID, $mailingVersion, $cbId, $contentarea);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes an assignment of a Cblock to this Mailing
	 *
	 * @param int $linkId Link Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function removeCblockByLinkId($linkId) {
		$mailingID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			$linkId = (int)$linkId;

			$sql = "DELETE FROM `yg_mailing_lnk_cb` WHERE (ID = ?);";

			$result = sYDB()->Execute($sql, $linkId);
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
	 * Removes a specific Cblock from a Contentarea in this Mailing
	 *
	 * @param int $cbId Cblock Id
	 * @param string $contentarea Contentarea code
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function removeCblock($cbId, $contentarea) {
		$mailingID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			$cbId = (int)$cbId;
			$contentarea = sYDB()->escape_string(sanitize($contentarea));
			$version = (int)$this->getVersion();

			$sql = "DELETE FROM `yg_mailing_lnk_cb` WHERE (PID = ?) AND (PVERSION = ?) AND (CBID = ?) AND (TEMPLATECONTENTAREA = ?)";

			$result = sYDB()->Execute($sql, $mailingID, $version, $cbId, $contentarea);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the order of Cblocks in this Mailing
	 *
	 * @param array $cbListOrder Array of link Ids
	 * @return bool TRUE on success, FALSE in case of an error
	 * @throws Exception
	 */
	function setCblockLinkOrder($cbListOrder = array()) {
		for ($i = 0; $i < count($cbListOrder); $i++) {
			$sql = "UPDATE `yg_mailing_lnk_cb`
			SET ORDERPROD = ? WHERE (ID = ?)";
			$result = sYDB()->Execute($sql, $i, $cbListOrder[$i]);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
		}
		return true;
	}

	/**
	 * Saves the order of Cblocks in a specific Contentarea in this Mailing
	 *
	 * @param array $cbListOrder Array of Cblock Ids
	 * @param string $contentarea Contentarea code
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setCblockOrder($cbListOrder = array(), $contentarea) {
		$mailingID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			$contentarea = sYDB()->escape_string(sanitize($contentarea));
			$version = (int)$this->getVersion();
			for ($i = 0; $i < count($cbListOrder); $i++) {
				$sql = "UPDATE `yg_mailing_lnk_cb`
				SET ORDERPROD = ? WHERE (PID = ?) AND (CBID = ?) AND (PVERSION = ?) AND (TEMPLATECONTENTAREA = ?)";
				$result = sYDB()->Execute($sql, $i, $mailingID, $cbListOrder[$i], $version, $contentarea);
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
	 * Gets the Cblock Mailing link for a specific link id
	 *
	 * @param int $id Link Id
	 * @return array Link
	 */
	function getCblockLinkById($id) {
		$mailingID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RREAD")) {
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
			yg_mailing_lnk_cb
			WHERE
			ID = $id;";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Gets the version of a Cblock linked to a specific version and Contentarea of this Mailing
	 *
	 * @param int $version Mailing version
	 * @param string $contentarea (optional) Contentarea code
	 * @return int Cblock version
	 */
	function getCblockLinkVersion($version, $contentarea = "") {
		$mailingID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RREAD")) {
			$version = (int)$version;
			$contentarea = sYDB()->escape_string(sanitize($contentarea));
			if (strlen($contentarea) > 0) {
				$filter_contentarea = " AND TEMPLATECONTENTAREA = '$contentarea' ";
			}
			$sql = "SELECT
						CBVERSION
					FROM
						yg_mailing_lnk_cb
					WHERE
						PID = $mailingID
						$filter_contentarea AND
						PVERSION = $version";
			$ra = $this->cacheExecuteGetArray($sql);
			$v = $ra[0]["CBVERSION"];
			if (strlen($v) == 0) {
				$v = 0;
			}
			return $v;
		} else {
			return false;
		}
	}

	/**
	 * Assigns a Usergroup to a specific version of this Mailing
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param int $version Mailing version
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function addUsergroup($usergroupId, $version) {
		$mailingID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			$usergroupId = (int)$usergroupId;
			$version = (int)$version;
			if (($mailingID != 0) && ($usergroupId != 0) && ($version != 0)) {
				$sql = "INSERT INTO `yg_mailing_lnk_usergroups` (`ID`, `NID`, `NVERSION`, `RID`)	VALUES (NULL, ?, ?, ?);";
				$result = sYDB()->Execute($sql, $mailingID, $version, $usergroupId);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$this->markAsChanged();
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Removes an assignment of an Usergroup to a specific version of this Mailing
	 *
	 * @param int $usergroupId Usergroup Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function removeUsergroup($usergroupId) {
		$mailingID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			$usergroupId = (int)$usergroupId;
			$version = (int)$this->getVersion();

			$sql = "DELETE FROM `yg_mailing_lnk_usergroups` WHERE (`NID` = ?) AND (`NVERSION` = ?) AND (`RID` = ?);";
			$result = sYDB()->Execute($sql, $mailingID, $version, $usergroupId);
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
	 * Gets a list of Cblocks in a Contentarea for the version of this Mailing
	 *
	 * @param string $contentarea (optional) Contentarea code
	 * @param boolean $workingCopy (optional) Set to TRUE to return the latest version (working copy)
	 * @return array Array of Cblocks
	 */
	function getCblockList($contentarea = "", $workingCopy = false) {
		$mailingID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RREAD")) {
			$version = $this->getVersion();

			if ($contentarea != "") {
				$contentarea = sYDB()->escape_string(sanitize($contentarea));
				$contentarea_sql = " AND (lnk.TEMPLATECONTENTAREA = '$contentarea')";
			}

			if (($workingCopy == true) || ($version == $this->getLatestVersion())) {
				$maxcoversion = true;
			}

			$perm_sql_select = ", coperm.RREAD AS RREAD ";
			$perm_sql_from = " , yg_contentblocks_permissions AS coperm";
			$perm_sql_where = " AND (coperm.OID = co.OBJECTID) AND (coperm.RREAD > 0) AND (";
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
				(yg_mailing_lnk_cb AS lnk, yg_contentblocks_properties as co, yg_contentblocks_tree $perm_sql_from)
				WHERE
				(
					(co.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (co.OBJECTID = rgt.OBJECTID))) AND
					(lnk.CBID = co.OBJECTID) AND
					(yg_contentblocks_tree.ID = co.OBJECTID) AND
					(lnk.PID = $mailingID) AND
					(co.DELETED = 0) AND
					(lnk.PVERSION = $version)
					$contentarea_sql $perm_sql_where
				)";
			} else {
				$sql = "SELECT
				co.OBJECTID as OBJECTID, co.FOLDER as FOLDER, co.VERSION as VERSION, co.EMBEDDED AS EMBEDDED, co.HASCHANGED AS HASCHANGED, yg_contentblocks_tree.PNAME as PNAME, lnk.ID AS LINKID
				$perm_sql_select
				FROM
				(yg_mailing_lnk_cb AS lnk, yg_contentblocks_properties as co, yg_contentblocks_tree $perm_sql_from)
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
					(lnk.PID = $mailingID) AND
					(co.DELETED = 0) AND
					(lnk.PVERSION = $version)
					$contentarea_sql $perm_sql_where
				)";
			}
			$sql .= " GROUP BY co.OBJECTID ORDER BY ORDERPROD ASC;";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Sets the status of this Mailing
	 *
	 * @param string $status Status ("UNSENT", "INPROGRESS", "PAUSED", "CANCELLED" or "SENT")
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setStatus($status) {
		$mailingID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			$status = sYDB()->escape_string(sanitize($status));

			$sql = "SELECT * FROM yg_mailing_status WHERE OID = ?;";
			$result = sYDB()->Execute($sql, $mailingID);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$ra = $result->GetArray();
			if (count($ra) > 0) {
				$sql = "UPDATE yg_mailing_status SET STATUS = ?, UID = ? WHERE OID = ?;";
				$result = sYDB()->Execute($sql, $status, $this->_uid, $mailingID);
			} else {
				$sql = "INSERT INTO yg_mailing_status (OID, STATUS, UID) VALUES (?, ?, ?);";
				$result = sYDB()->Execute($sql, $mailingID, $status, $this->_uid);
			}

			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets the status of this Mailing
	 *
	 * @return array|false The status of this Mailing or FALSE in case of an error
	 * @throws Exception
	 */
	function getStatus() {
		$mailingID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RREAD")) {
			$sql = "SELECT * FROM yg_mailing_status WHERE OID = ?;";
			$result = sYDB()->Execute($sql, $mailingID);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$ra = $result->GetArray();
			if (count($ra) > 0) {
				return $ra[0];
			} else {
				return array('OID' => $mailingID, 'STATUS' => 'UNSENT', 'UID' => 0);
			}
		} else {
			return false;
		}
	}

	/**
	 * Assigns a Template to this Mailing by Template identifier
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
	 * Assigns a Template to this Mailing
	 *
	 * @param int $templateId Template Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setTemplate($templateId) {
		$mailingID = (int)$this->_id;
		$templateId = (int)$templateId;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			$version = (int)$this->getVersion();
			$sql = "UPDATE yg_mailing_properties SET TEMPLATEID = ? WHERE (OBJECTID = ?) AND VERSION = ?;";
			$result = sYDB()->Execute($sql, $templateId, $mailingID, $version);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->markAsChanged();
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
	 * Gets the URL of this Mailing
	 *
	 * @return string URL of this Mailing
	 */
	function getUrl() {
		$mailingID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RREAD")) {
			$mailingMgr = new MailingMgr();
			$pnames = $mailingMgr->getParents($mailingID);
			$pi = count($pnames);
			while ($pi > 0) {
				$url .= $pnames[$pi - 1][0]["PNAME"] . "/";
				$pi--;
			}
			$pinfo = $this->get();
			return sApp()->webroot . $url . $tree[$xt]["PNAME"] . $pinfo["PNAME"] . "/";
		} else {
			return false;
		}
	}

	/**
	 * Sets the permanent name of this Mailing
	 *
	 * @param string $pname Pname
	 * @return bool TRUE on success FALSE in case of an error
	 * @throws Exception
	 */
	public function setPName($pname) {
		$mailingID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mailingID, "RWRITE")) {
			$pname = $this->filterPName($pname);

			if (is_numeric($pname)) {
				return false;
			}

			$mailingMgr = new MailingMgr();
			$checkpinfo = $mailingMgr->getMailingIdByPName($pname);
			if (($checkpinfo["ID"] != $mailingID) && ($checkpinfo["ID"] > 0)) {
				$pname = $pname . $mailing;
			}
			$sql = "UPDATE yg_mailing_tree SET PNAME = ? WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $pname, $mailingID);
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
	 * Generates absolute URLs from relative URLs
	 *
	 * @param string $html Input HTML
	 * @return string Output HTML
	 */
	function absolutizeURLs ($html) {
		$regexpHREF = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
		$regexpIMG = "<img\s[^>]*src=(\"??)([^\" >]*?)\"(\S*)\"";
		$prefix = rtrim(ltrim((string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/');
		$prefix = '/'.$prefix;
		if (strlen($prefix) > 1) {
			$prefix .= '/';
		}

		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
			$absoluteprefix = 'https://';
		} else {
			$absoluteprefix = 'http://';
		}
		$domain = (string)sConfig()->getVar("CONFIG/MAILINGS/ABSOLUTE_DOMAIN");
		if ($domain == "") $domain = $_SERVER['SERVER_NAME'];
		$absoluteprefix .= $domain;

		// Check and replace hrefs
		if ((preg_match_all("/$regexpHREF/siU", stripslashes($html), $matches, PREG_SET_ORDER)>0)) {
			$tmparr = array();
			usort($matches, function($a, $b) {
			    return strlen($a[2]) - strlen($b[2]);
			});
			foreach($matches as $match) {
				if (strpos($match[2], $prefix) === 0) {
					$alreadyReplaced = false;
					foreach($tmparr as $replaced) {
						if (strpos($match[2], $replaced) !== false) {
							$alreadyReplaced = true;
							break;
						}
					}
					if (!$alreadyReplaced) {
						array_push($tmparr, $match[2]);
						$html = str_replace($match[2], $absoluteprefix.$match[2], $html);
					}
				}
			}
		}

		// Check and replace images
		if ((preg_match_all("/$regexpIMG/siU", stripslashes($html), $matches, PREG_SET_ORDER)>0)) {
			$tmparr = array();
			usort($matches, function($a, $b) {
			    return strlen($a[3]) - strlen($b[3]);
			});
			foreach($matches as $match) {
				if (strpos($match[3], $prefix) === 0) {
					$alreadyReplaced = false;
					foreach($tmparr as $replaced) {
						if (strpos($match[3], $replaced) !== false) {
							$alreadyReplaced = true;
							break;
						}
					}
					if (!$alreadyReplaced) {
						array_push($tmparr, $match[3]);
						$html = str_replace($match[3], $absoluteprefix.$match[3], $html);
					}
				}
			}
		}
		return $html;
	}


	/**
	 * Calculates a unique permanent name for this Mailing
	 *
	 * @param string $iteration (optional) Iteration
	 * @return string Permanent name
	 */
	function calcPName($iteration = "") {
		$mailingID = $this->_id;
		$pinfo = $this->get();
		$mailingname = $pinfo["NAME"];
		if ((int)sConfig()->getVar("CONFIG/CASE_SENSITIVE_URLS") == 0) {
			$mailingname = strtolower($mailingname);
		}
		$pname = $this->filterPName($mailingname);
		if (is_numeric($pname)) {
			$pname = 'mailing_'.$pname;
		}
		$mailingMgr = new MailingMgr();
		if ($iteration != '') {
			$checkpinfo = $mailingMgr->getMailingIdByPName($pname . '_' . $iteration);
		} else {
			$checkpinfo = $mailingMgr->getMailingIdByPName($pname);
		}
		if ($checkpinfo["ID"] == $mailingID) {
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