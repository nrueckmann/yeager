<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The Cblock class, which represents an instance of a Content Block.
 */
class Cblock extends Versionable {
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

	/**
	 * Constructor of the Cblock class
	 *
	 * @param int $cbId Cblock Id
	 * @param int $version Version
	 */
	public function __construct($cbId = 0, $version = 0) {
		$this->_uid = &sUserMgr()->getCurrentUserID();

		// object descriptor
		$this->_id = $cbId;

		$this->initTables();
		$this->permissions = new Permissions($this->_table_permissions, $this);
		parent::__construct($this->_id, $version, $this->_table_object, $this->_table_tree, $this->permissions);

		$this->history = new History($this, HISTORYTYPE_CO, $this->permissions);
		$this->tags = new Tags($this);
		$this->properties = new Properties($this->_table_properties, $this->getPropertyId(), $this);
		$this->scheduler = new Scheduler($this->_table_scheduler, SCHEDULER_CO);
		$this->control = new Entrymasks();
		$this->comments = new Comments($this);
	}

/// @cond DEV

	/**
	 * Initializes internal class members
	 */
	private function initTables() {
		$this->_table_object = "yg_contentblocks_properties";
		$this->_table_permissions = "yg_contentblocks_permissions";
		$this->_table_history = "yg_history";
		$this->_table_tree = "yg_contentblocks_tree";
		$this->_table_properties = "yg_contentblocks_props";
		$this->_table_taglinks = "yg_co_lnk_articles";
		$this->_table_scheduler = "yg_cron";
		$this->_table_commentlinks = "yg_comments_lnk_cb";
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the object prefix, used for table names in database queries
	 *
	 * @return string Objectprefix
	 */
	function getObjectPrefix() {
		return "cb";
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
	 * Gets the name of the database table which contains the properties of Cblocks
	 *
	 * @return string Tablename
	 */
	function getPropertyTable() {
		return "yg_contentblocks_properties";
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table which contains the permissions for Cblocks
	 *
	 * @return string Tablename
	 */
	function getPermissionsTable() {
		return "yg_contentblocks_permissions";
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table containing the Cblock tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return "yg_contentblocks_tree";
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table which contains links between Comments and Cblocks
	 *
	 * @return string Tablename
	 */
	function getCommentsLinkTable() {
		return $this->_table_commentlinks;
	}

/// @endcond

	/**
	 * Callback method which is executed when a Usergroup permission on a Cblock changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 */
	public function onPermissionChange($usergroupId, $permission, $value) {
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on a Cblock change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permissions (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 */
	public function onPermissionsChange($usergroupId, $permissions, $value) {
		return true;
	}

	/**
	 * Calls a specific Extension hook Callback method
	 *
	 * @param string $method Method name
	 * @param int $cbId Cblock Id
	 * @param int $version Cblock version
	 * @param mixed $args Arbitrary arguments
	 */
	function callExtensionHook($method, $cbId, $version, $args) {
		$extensions = new ExtensionMgr($this->_db, $this->_uid);
		$all_cblock_extensions = $extensions->getList(EXTENSION_CBLOCK, true);
		$extarr = array();
		foreach ($all_cblock_extensions as $all_cblock_extension) {
			$extension = $extensions->getExtension($all_cblock_extension['CODE']);
			if ($extension && $extension->usedByCblock($cbId, $version) === true) {
				$extension = $extensions->getExtension($all_cblock_extension['CODE'], $cbId, $version);
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
	 * @return array|bool Result of SQL query or FALSE in case of an error
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
	 * Gets all content of this Cblock
	 *
	 * @return array
	 */
	function getContentInternal() {
		$cbId = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			$cblockInfo = $this->get();

			if ($cblockInfo['FOLDER']) {
				return false;
			} else {
				$entrymasks = $this->getEntrymasks();
				for ($c = 0; $c < count($entrymasks); $c++) {
					$controlFormfields = $this->getFormfieldsInternal($entrymasks[$c]['LINKID']);
					for ($w = 0; $w < count($controlFormfields); $w++) {
						if (($controlFormfields[$w]['FORMFIELD'] == 6) || ($controlFormfields[$w]['FORMFIELD'] == 16)) {
							if (trim($controlFormfields[$w]['VALUE01'])) {
								$file = new File($controlFormfields[$w]['VALUE01']);
								if ($file) {
									$fileInfo = $file->get();
									$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
									$controlFormfields[$w]['FILEINFO'] = $fileInfo;
								}
							}
						}
						if ($controlFormfields[$w]['FORMFIELD'] == 7) {
							if (trim($controlFormfields[$w]['VALUE01'])) {
								$lcb = sCblockMgr()->getCblock($controlFormfields[$w]['VALUE01']);
								if ($lcb) {
									$info = $lcb->get();
									$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
								}
							}
						}
						if ($controlFormfields[$w]['FORMFIELD'] == 8) {
							if (trim($controlFormfields[$w]['VALUE01'])) {
								$tagMgr = new Tags();
								$info = $tagMgr->get($controlFormfields[$w]['VALUE01']);
								if ($info) {
									$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
								}
							}

						}
					}
					$entrymasks[$c]['FORMFIELDS'] = $controlFormfields;
				}
			}
			return $entrymasks;
		} else {
			return false;
		}
	}

/// @endcond

	/**
	 * Gets all content of this Cblock
	 *
	 * @return array Array of Entrymasks and Formfields
	 */
	function getContent() {
		$cbId = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			$cblockInfo = $this->get();

			if ($cblockInfo['FOLDER']) {
				return false;
			} else {
				$entrymasks = $this->getEntrymasks();
				for ($c = 0; $c < count($entrymasks); $c++) {
					$controlFormfields = $this->getFormfields($entrymasks[$c]['LINKID']);
					for ($w = 0; $w < count($controlFormfields); $w++) {
						foreach ($controlFormfields[$w] as $singleFieldIdx => $singleField) {
							if (substr($singleFieldIdx, 0, 6) == 'VALUE0') {
								unset($controlFormfields[$w][$singleFieldIdx]);
							}
						}
					}
					$entrymasks[$c]['FORMFIELDS'] = $controlFormfields;
				}
			}
			return $entrymasks;
		} else {
			return false;
		}
	}

	/**
	 * Gets basic information about this Cblock
	 *
	 * @return array|false Array containing information about this Cblock or FALSE in case of an error
	 */
	function get() {
		$cbId = (int)$this->_id;
		$version = (int)$this->getVersion();

		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			$sql = "SELECT * FROM yg_contentblocks_props WHERE READONLY = 1;";
			$ra = $this->cacheExecuteGetArray($sql);
			$dynIdentifers = array();
			foreach ($ra as $raItem) {
				$dynIdentifers[] = "\npv.`" . $raItem['IDENTIFIER'] . '` AS `' . $raItem['IDENTIFIER'] . '`';
			}
			$dynIdentifers = implode(', ', $dynIdentifers);
			$sql = "SELECT
						p.ID AS ID,
						p.OBJECTID AS OBJECTID,
						p.FOLDER AS FOLDER,
						p.EMBEDDED AS EMBEDDED,
						p.VERSION AS VERSION,
						p.APPROVED AS APPROVED,
						p.CREATEDBY AS CREATEDBY,
						p.CHANGEDBY AS CHANGEDBY,
						p.HASCHANGED AS HASCHANGED,
						p.COMMENTSTATUS AS COMMENTSTATUS,
						p.COMMENTSTATUS_AUTO AS COMMENTSTATUS_AUTO,
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
						(yg_contentblocks_properties AS p, yg_contentblocks_tree AS t)
					LEFT JOIN
						yg_contentblocks_propsv AS pv ON pv.OID = p.ID
					WHERE
						p.OBJECTID = t.ID AND
						OBJECTID = ? AND
						VERSION = ?;";
			$ra = $this->cacheExecuteGetArray($sql, $cbId, $version);
			return $ra[0];
		} else {
			return false;
		}
	}

	/**
	 * Gets all versions of this Cblock
	 *
	 * @return array|false Array of versions or FALSE in case of an error
	 */
	function getVersions() {
		$cbId = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			$sql = "SELECT *, pv.*
						FROM
						yg_contentblocks_properties AS prop
						LEFT JOIN yg_contentblocks_propsv AS pv ON pv.OID = prop.ID
						WHERE OBJECTID = ? AND prop.VERSION > 0 ORDER BY VERSION DESC";
			$ra = $this->cacheExecuteGetArray($sql, $cbId);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Calculates a unique permanent name for this Cblock
	 *
	 * @param string $iteration (optional) Iteration
	 * @return string Permanent name
	 */
	function calcPName($iteration = "") {
		$cbId = (int)$this->_id;
		$cblockInfo = $this->get();
		$coname = $cblockInfo["NAME"];
		if ((int)sConfig()->getVar("CONFIG/CASE_SENSITIVE_URLS") == 0) {
			$coname = strtolower($coname);
		}
		$pname = $this->filterPName($coname);
		if (is_numeric($pname)) {
			$pname = 'cblock_'.$pname;
		}
		if ($iteration != '') {
			$checkpinfo = sCblockMgr()->getCblockIdByPName($pname . '_' . $iteration);
		} else {
			$checkpinfo = sCblockMgr()->getCblockIdByPName($pname);
		}
		if ($checkpinfo["ID"] == $cbId) {
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
	 * Sets the state of a specific version of this Cblock to "published"
	 *
	 * @param int $version (optional) Specific version or ALWAYS_LATEST_APPROVED_VERSION (constant) to always publish the lastest approved version
	 * @return int|false New version of the Cblock or FALSE in case of an error
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
				$this->history->add (HISTORYTYPE_CO, NULL, $version, 'TXT_CBLOCK_H_PUBLISH');
				sCblockMgr()->callExtensionHook("onPublish", $this->_id, $version);
				if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/CBLOCK_PUBLISH") == "true") {
					Singleton::FC()->emptyBucket();
				}
			}
		}
		return $result;
	}

	/**
	 * Generates a new version of this Cblock
	 *
	 * @return int|false New version of this Cblock or FALSE in case of an error
	 */
	function newVersion() {
		$cbId = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RWRITE")) {
			$sourceVersion = $this->getVersion();
			$sourceObject = sCblockMgr()->getCblock($this->_id, $sourceVersion);
			if ($sourceVersion == $this->getLatestVersion()) {
				$historyIdentifier = 'TXT_CBLOCK_H_NEWVERSION';
				$historySourceVersion = NULL;
			} else {
				$historyIdentifier = 'TXT_CBLOCK_H_NEWVERSION_FROM';
				$historySourceVersion = $sourceVersion;
			}
			$newVersion = (int)parent::newVersion();
			$this->properties = new Properties($this->_table_properties, $this->getPropertyId(), $this);
			$this->tags->copyTo($cbId, $sourceVersion, $cbId, $newVersion);
			$this->copyExtensionsFrom($sourceObject);
			if ($sourceVersionLinks > 0) {
				$sql = "SELECT * FROM yg_contentblocks_lnk_entrymasks WHERE CBID = ? AND CBVERSION = ?";
				$dbr = sYDB()->Execute($sql, $cbId, $sourceVersion);
				$links = $dbr->GetArray();
				for ($l = 0; $l < count($links); $l++) {
					$sql = "INSERT INTO yg_contentblocks_lnk_entrymasks (ENTRYMASK, CBID, CBVERSION, ORDERPROD)
						VALUES (?, ?, ?, ?)";
					sYDB()->Execute($sql, $links[$l]["ENTRYMASK"], $links[$l]["CBID"], $newVersion, $links[$l]["ORDERPROD"]);
					$linkId = (int)sYDB()->Insert_ID();

					$sql = "INSERT INTO `yg_contentblocks_lnk_entrymasks_c`
								(FORMFIELD, ENTRYMASKFORMFIELD, LNK, VALUE01, VALUE02, VALUE03, VALUE04, VALUE05, VALUE06, VALUE07, VALUE08)
							SELECT FORMFIELD, ENTRYMASKFORMFIELD, $linkId, VALUE01, VALUE02, VALUE03, VALUE04, VALUE05, VALUE06, VALUE07, VALUE08
							FROM yg_contentblocks_lnk_entrymasks_c WHERE (LNK = ?);";
					sYDB()->Execute($sql, $links[$l]["ID"]);

					$sql = "SELECT * FROM yg_contentblocks_lnk_entrymasks_c AS c WHERE c.LNK = ?";
					$dbr = sYDB()->Execute($sql, $linkId);
					$newcs = $dbr->GetArray();

					$sql = "SELECT * FROM yg_contentblocks_lnk_entrymasks_c AS c WHERE c.LNK = ?";
					$dbr = sYDB()->Execute($sql, $links[$l]["ID"]);
					$oldcs = $dbr->GetArray();

					for ($r = 0; $r < count($oldcs); $r++) {
						$sql = "INSERT INTO yg_references (SRCTYPE, SRCOID, SRCVER, TGTTYPE, TGTOID, TGTAID)
								SELECT SRCTYPE, " . (int)$newcs[$r]["ID"] . ", " . $newVersion . ", TGTTYPE, TGTOID, TGTAID
								FROM yg_references WHERE (SRCOID =  ?);";
						sYDB()->Execute($sql, $oldcs[$r]["ID"]);
					}
				}
			} else {
				$sql = "SELECT * FROM yg_contentblocks_lnk_entrymasks WHERE CBID = ? AND CBVERSION = ?";
				$dbr = sYDB()->Execute($sql, $cbId, $sourceVersion);
				if (!$dbr) {
					return;
				}
				$links = $dbr->GetArray();
				for ($l = 0; $l < count($links); $l++) {
					$sql = "INSERT INTO yg_contentblocks_lnk_entrymasks (ENTRYMASK, CBID, CBVERSION, ORDERPROD)
							VALUES
							(?, ?, ?, ?)";
					sYDB()->Execute($sql, $links[$l]["ENTRYMASK"], $links[$l]["CBID"], $newVersion, $links[$l]["ORDERPROD"]);
					$linkId = (int)sYDB()->Insert_ID();

					$sql = "INSERT INTO `yg_contentblocks_lnk_entrymasks_c`
								(FORMFIELD, ENTRYMASKFORMFIELD, LNK, VALUE01, VALUE02, VALUE03, VALUE04, VALUE05, VALUE06, VALUE07, VALUE08)
							SELECT FORMFIELD, ENTRYMASKFORMFIELD, $linkId, VALUE01, VALUE02, VALUE03, VALUE04, VALUE05, VALUE06, VALUE07, VALUE08
							FROM yg_contentblocks_lnk_entrymasks_c WHERE (LNK = ?);";
					sYDB()->Execute($sql, $links[$l]["ID"]);

					$sql = "SELECT * FROM yg_contentblocks_lnk_entrymasks_c AS c WHERE c.LNK = ?";
					$dbr = sYDB()->Execute($sql, $linkId);
					$newcs = $dbr->GetArray();

					$sql = "SELECT * FROM yg_contentblocks_lnk_entrymasks_c AS c WHERE c.LNK = ?";
					$dbr = sYDB()->Execute($sql, $links[$l]["ID"]);
					$oldcs = $dbr->GetArray();

					for ($r = 0; $r < count($oldcs); $r++) {
						$sql = "INSERT INTO yg_references (SRCTYPE, SRCOID, SRCVER, TGTTYPE, TGTOID, TGTAID)
								SELECT SRCTYPE, " . (int)$newcs[$r]["ID"] . ", " . $newVersion . ", TGTTYPE, TGTOID, TGTAID
								FROM yg_references WHERE (SRCOID = ?);";
						sYDB()->Execute($sql, $oldcs[$r]["ID"]);
					}
				}
			}

			$extensions = new ExtensionMgr(sYDB(), $this->_uid);
			$all_cblock_extensions = $extensions->getList(EXTENSION_CBLOCK, true);
			foreach ($all_cblock_extensions as $all_cblock_extension) {
				$extension = $extensions->getExtension($all_cblock_extension['CODE']);
				if ($extension && ($extension->usedByCblock($cbId, $sourceVersion) === true)) {
					if ($extension->usedByCblock($cbId, $newVersion) !== true) {
						$extension->addToCBlockInternal($cbId, $newVersion);
					}
					$extension = $extensions->getExtension($all_cblock_extension['CODE'], $cbId, $newVersion);
					$sourceext = $extensions->getExtension($all_cblock_extension['CODE'], $cbId, $sourceVersion);
					if ($extension && $sourceext) {
						$newCbId = $extension->getPropertyId();
						$oldcid = $sourceext->getPropertyId();
						$extension->properties->copyTo($oldcid, $newCbId);
					}
				}
			}
			sCblockMgr()->callExtensionHook('onVersionNew', (int)$this->_id, $this->getVersion());

			// Add to history
			$new_version = $this->getLatestVersion();
			$this->history->add(HISTORYTYPE_CO, $historySourceVersion, $new_version, $historyIdentifier);

			return $newVersion;
		} else {
			return false;
		}
	}



	/**
	 * Gets Formfields of this Cblock including content, optionally of one specific Entrymask
	 *
	 * @param array $codes (optional) Array of Entrymask Codes
	 * @return array Array of Formfields
	 */
	function getFormfieldsByEntrymaskCode($codes) {
		$version = (int)$this->getVersion();
		$cbId = (int)$this->_id;

		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			if ($cbId > 0) {
				$sql_where = "CBID = $cbId AND CBVERSION = $version";
			}

			$webroot = (string)Singleton::config()->getVar('CONFIG/DIRECTORIES/WEBROOT');
			$sql = "SELECT
					l.ID AS LINKID,
					l.ENTRYMASK AS ENTRYMASKID,
					p.CODE AS CODE,
					p.NAME AS ENTRYMASKNAME,
					w.IDENTIFIER AS IDENTIFIER,
					f.TYPE AS TYPE,
					c.ID,
					c.FORMFIELD,
					c.ENTRYMASKFORMFIELD,
					c.VALUE01,
					c.VALUE02,
					c.VALUE03,
					c.VALUE04,
					c.VALUE05,
					c.VALUE06,
					c.VALUE07,
					c.VALUE08,
					w.NAME AS NAME,
					l.CBID AS CBLOCKID,
					w.PRESET AS PRESET,
					w.WIDTH AS WIDTH,
					w.MAXLENGTH AS MAXLENGTH,
					w.CONFIG AS CONFIG,
					w.CUSTOM AS CUSTOM
				FROM
					yg_contentblocks_lnk_entrymasks_c AS c,
					yg_contentblocks_lnk_entrymasks AS l,
					yg_entrymasks_lnk_formfields as w,
					yg_entrymasks_properties AS p,
					yg_formfields as f
				WHERE
					(c.LNK IN (
						SELECT * FROM
						(
						SELECT lnk.ID as LINKID
						FROM	yg_contentblocks_lnk_entrymasks as lnk,
							yg_entrymasks_properties as ctrl
						WHERE	(CBID = " . $cbId . " AND CBVERSION = " . $version . ") AND
							(lnk.ENTRYMASK = ctrl.OBJECTID) AND";
			for ($i = 0; $i < count($codes); $i++) {
				if ($i == 0) $sql .= "(";
				$sql .= "CODE = '" . $codes[$i] . "'";
				if ($i == (count($codes)-1)) {
					$sql .= ")";
				} else {
					$sql .= " OR ";
				}
			}
				$sql .= "ORDER BY ORDERPROD
						) AS subquery )git
					) AND
					(c.LNK = l.ID) AND
					(c.ENTRYMASKFORMFIELD = w.ID) AND
					(w.ENTRYMASK = p.OBJECTID) AND
					(l.ENTRYMASK = p.OBJECTID) AND
					(c.FORMFIELD = f.ID)
				ORDER BY w.ORDER ASC;";
				$wc = $this->cacheExecuteGetArray($sql);
				for ($w = 0; $w < count($wc); $w++) {
					switch ($wc[$w]['TYPE']) {
						case 'TEXT':
						case 'CHECKBOX':
						case 'PASSWORD':
						case 'DATE':
						case 'DATETIME':
						case 'HEADLINE':
							$wc[$w]['VALUE'] = $wc[$w]['VALUE01'];
							break;
						case 'TEXTAREA':
						case 'WYSIWYG':
							$wc[$w]['VALUE'] = replaceSpecialURLs($wc[$w]['VALUE01']);
							break;
						case 'LINK':
							$resolvedUrl = resolveSpecialURL($wc[$w]['VALUE01']);
							if ($resolvedUrl) {
								$wc[$w]['URL'] = $resolvedUrl;
							} else {
								$wc[$w]['URL'] = $wc[$w]['VALUE01'];
							}
							break;
						case 'PAGE':
							$wc[$w]['PAGE_ID'] = $wc[$w]['VALUE01'];
							$wc[$w]['SITE_ID'] = $wc[$w]['VALUE02'];
							$wc[$w]['URL'] = $wc[$w]['VALUE03'];
							$wc[$w]['PNAME'] = $wc[$w]['VALUE04'];
							$wc[$w]['SITE_PNAME'] = $wc[$w]['VALUE05'];
							break;
						case 'FILE':
						case 'FILEFOLDER':
							$wc[$w]['FILE_ID'] = $wc[$w]['VALUE01'];
							$pname = '';
							if ($wc[$w]['FILE_ID'] ) {
								$pname = sFileMgr()->getPNameByFileId($wc[$w]['FILE_ID']);
							}
							$wc[$w]['URL'] = '';
							if ($pname != "") {
								$wc[$w]['URL'] = $webroot . 'download/' . $pname;
								$wc[$w]['IMAGE_URL'] = $webroot . 'image/' . $pname;
								$wc[$w]['PNAME'] = $pname;
							}
							break;
						case 'CO':
							$wc[$w]['CBLOCK_ID'] = $wc[$w]['VALUE01'];
							break;
						case 'TAG':
							$wc[$w]['TAG_ID'] = $wc[$w]['VALUE01'];
							break;
						case 'LIST':
							$wc[$w]['VALUE'] = $wc[$w]['VALUE01'];
							break;
					}
					$cleanArray = array();
					foreach ($wc[$w] as $arrKey => $arrValue) {
						if (!is_numeric($arrKey) && (substr($arrKey, 0, 6) != 'VALUE0')) {
							$cleanArray[$arrKey] = $arrValue;
						}
					}
					$wc[$w] = $cleanArray;
				}
			return $wc;
		} else {
			return false;
		}
	}


	/**
	 * Gets Formfields of this Cblock including content, optionally of one specific Entrymask
	 *
	 * @param int $linkId (optional) Cblock Entrymask Link Id
	 * @return array Array of Formfields
	 */
	function getFormfields($linkId = 0) {
		$version = (int)$this->getVersion();
		$linkId = (int)$linkId;
		$cbId = (int)$this->_id;

		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			if ($cbId > 0) {
				$sql_where = "CBID = $cbId AND CBVERSION = $version ";
			}
			if ($linkId > 0) {
				$linksql = "AND lnk.ID = ?";
			}
			$sql = "SELECT
				lnk.ID AS LINKID,
				lnk.ENTRYMASK AS ENTRYMASKID,
				ctrl.CODE AS CODE,
				ctrl.NAME AS ENTRYMASKNAME,
				CBID AS CBLOCKID,
				CBVERSION AS CBLOCKVERSION
			FROM
				`yg_contentblocks_lnk_entrymasks` as lnk,
				yg_entrymasks_properties as ctrl
			WHERE
				($sql_where $linksql) AND
				(lnk.ENTRYMASK = ctrl.OBJECTID)
			ORDER BY ORDERPROD;";
			$ra = $this->cacheExecuteGetArray($sql, $linkId);

			$webroot = (string)Singleton::config()->getVar('CONFIG/DIRECTORIES/WEBROOT');
			for ($l = 0; $l < count($ra); $l++) {
				$sql = "SELECT
							l.ID AS LINKID,
							l.ENTRYMASK AS ENTRYMASKID,
							p.CODE AS CODE,
							p.NAME AS ENTRYMASKNAME,
							w.IDENTIFIER AS IDENTIFIER,
							f.TYPE AS TYPE,
							c.ID,
							c.FORMFIELD,
							c.ENTRYMASKFORMFIELD,
							c.VALUE01,
							c.VALUE02,
							c.VALUE03,
							c.VALUE04,
							c.VALUE05,
							c.VALUE06,
							c.VALUE07,
							c.VALUE08,
							w.NAME AS NAME,
							l.CBID AS CBLOCKID,
							w.PRESET AS PRESET,
							w.WIDTH AS WIDTH,
							w.MAXLENGTH AS MAXLENGTH,
							w.CONFIG AS CONFIG,
							w.CUSTOM AS CUSTOM
						FROM
							yg_contentblocks_lnk_entrymasks_c AS c,
							yg_contentblocks_lnk_entrymasks AS l,
							yg_entrymasks_lnk_formfields as w,
							yg_entrymasks_properties AS p,
							yg_formfields as f
						WHERE
							(c.LNK = ?) AND
							(c.LNK = l.ID) AND
							(c.ENTRYMASKFORMFIELD = w.ID) AND
							(w.ENTRYMASK = p.OBJECTID) AND
							(l.ENTRYMASK = p.OBJECTID) AND
							(c.FORMFIELD = f.ID)
						ORDER BY w.ORDER ASC;";
				$wc = $this->cacheExecuteGetArray($sql, $ra[$l]["LINKID"]);
				for ($w = 0; $w < count($wc); $w++) {
					switch ($wc[$w]['TYPE']) {
						case 'TEXT':
						case 'CHECKBOX':
						case 'PASSWORD':
						case 'DATE':
						case 'DATETIME':
						case 'HEADLINE':
							$wc[$w]['VALUE'] = $wc[$w]['VALUE01'];
							break;
						case 'TEXTAREA':
						case 'WYSIWYG':
							$wc[$w]['VALUE'] = replaceSpecialURLs($wc[$w]['VALUE01']);
							break;
						case 'LINK':
							$resolvedUrl = resolveSpecialURL($wc[$w]['VALUE01']);
							if ($resolvedUrl) {
								$wc[$w]['URL'] = $resolvedUrl;
							} else {
								$wc[$w]['URL'] = $wc[$w]['VALUE01'];
							}
							break;
						case 'PAGE':
							$wc[$w]['PAGE_ID'] = $wc[$w]['VALUE01'];
							$wc[$w]['SITE_ID'] = $wc[$w]['VALUE02'];
							$wc[$w]['URL'] = $wc[$w]['VALUE03'];
							$wc[$w]['PNAME'] = $wc[$w]['VALUE04'];
							$wc[$w]['SITE_PNAME'] = $wc[$w]['VALUE05'];
							break;
						case 'FILE':
						case 'FILEFOLDER':
							$wc[$w]['FILE_ID'] = $wc[$w]['VALUE01'];
							$pname = '';
							if ($wc[$w]['FILE_ID'] ) {
								$pname = sFileMgr()->getPNameByFileId($wc[$w]['FILE_ID']);
							}
							$wc[$w]['URL'] = '';
							if ($pname != "") {
								$wc[$w]['URL'] = $webroot . 'download/' . $pname;
								$wc[$w]['IMAGE_URL'] = $webroot . 'image/' . $pname;
								$wc[$w]['PNAME'] = $pname;
							}
							break;
						case 'CO':
							$wc[$w]['CBLOCK_ID'] = $wc[$w]['VALUE01'];
							break;
						case 'TAG':
							$wc[$w]['TAG_ID'] = $wc[$w]['VALUE01'];
							break;
						case 'LIST':
							$wc[$w]['VALUE'] = $wc[$w]['VALUE01'];
							break;
					}
					$cleanArray = array();
					foreach ($wc[$w] as $arrKey => $arrValue) {
						if (!is_numeric($arrKey) && (substr($arrKey, 0, 6) != 'VALUE0')) {
							$cleanArray[$arrKey] = $arrValue;
						}
					}
					$wc[$w] = $cleanArray;
				}
			}
			return $wc;
		} else {
			return false;
		}
	}

/// @cond DEV

	/**
	 * Gets Formfields of this Cblock including content, optionally of one specific Entrymask (internal method)
	 *
	 * @param int $linkId (optional) Cblock Entrymask Link Id
	 * @return array Array of Formfields
	 */
	function getFormfieldsInternal($linkId = 0) {
		$version = (int)$this->getVersion();
		$linkId = (int)$linkId;
		$cbId = (int)$this->_id;

		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			if ($cbId > 0) {
				$sql_where = "CBID = $cbId AND CBVERSION = $version ";
			}
			if ($linkId > 0) {
				$linksql = "AND lnk.ID = $linkId";
			}
			$sql = "SELECT
				lnk.ID AS LINKID,
				lnk.ENTRYMASK AS ENTRYMASKID,
				ctrl.CODE AS CODE,
				ctrl.NAME AS ENTRYMASKNAME,
				CBID,
				CBVERSION
			FROM
				`yg_contentblocks_lnk_entrymasks` as lnk,
				yg_entrymasks_properties as ctrl
			WHERE
				($sql_where $linksql) AND
				(lnk.ENTRYMASK = ctrl.OBJECTID)
			ORDER BY ORDERPROD;";
			$ra = $this->cacheExecuteGetArray($sql);

			$webroot = (string)Singleton::config()->getVar('CONFIG/DIRECTORIES/WEBROOT');
			for ($l = 0; $l < count($ra); $l++) {
				$sql = "SELECT
							l.ID AS LINKID,
							l.ENTRYMASK AS ENTRYMASKID,
							p.CODE AS CODE,
							p.NAME AS ENTRYMASKNAME,
							w.IDENTIFIER AS IDENTIFIER,
							f.TYPE AS TYPE,
							c.ID,
							c.FORMFIELD,
							c.ENTRYMASKFORMFIELD,
							c.LNK,
							c.VALUE01,
							c.VALUE02,
							c.VALUE03,
							c.VALUE04,
							c.VALUE05,
							c.VALUE06,
							c.VALUE07,
							c.VALUE08,
							w.NAME AS NAME,
							l.CBID AS CBID,
							w.PRESET AS PRESET,
							w.WIDTH AS WIDTH,
							w.MAXLENGTH AS MAXLENGTH,
							w.CONFIG AS CONFIG,
							w.CUSTOM AS CUSTOM
						FROM
							yg_contentblocks_lnk_entrymasks_c AS c,
							yg_contentblocks_lnk_entrymasks AS l,
							yg_entrymasks_lnk_formfields as w,
							yg_entrymasks_properties AS p,
							yg_formfields as f
						WHERE
							(c.LNK = ?) AND
							(c.LNK = l.ID) AND
							(c.ENTRYMASKFORMFIELD = w.ID) AND
							(w.ENTRYMASK = p.OBJECTID) AND
							(l.ENTRYMASK = p.OBJECTID) AND
							(c.FORMFIELD = f.ID)
						ORDER BY w.ORDER ASC;";
				$wc = $this->cacheExecuteGetArray($sql, $ra[$l]["LINKID"]);
				for ($w = 0; $w < count($wc); $w++) {
					switch ($wc[$w]['TYPE']) {
						case 'TEXT':
						case 'CHECKBOX':
						case 'PASSWORD':
						case 'DATE':
						case 'DATETIME':
						case 'HEADLINE':
							$wc[$w]['VALUE'] = $wc[$w]['VALUE01'];
							break;
						case 'TEXTAREA':
						case 'WYSIWYG':
							$wc[$w]['VALUE'] = replaceSpecialURLs($wc[$w]['VALUE01']);
							break;
						case 'LINK':
							$resolvedUrl = resolveSpecialURL($wc[$w]['VALUE01']);
							if ($resolvedUrl) {
								$wc[$w]['URL'] = $resolvedUrl;
							} else {
								$wc[$w]['URL'] = $wc[$w]['VALUE01'];
							}
							break;
						case 'PAGE':
							$wc[$w]['PAGE_ID'] = $wc[$w]['VALUE01'];
							$wc[$w]['SITE_ID'] = $wc[$w]['VALUE02'];
							$wc[$w]['URL'] = $webroot . $wc[$w]['VALUE03'];
							$wc[$w]['PNAME'] = $wc[$w]['VALUE04'];
							$wc[$w]['SITE_PNAME'] = $wc[$w]['VALUE05'];
							break;
						case 'FILE':
						case 'FILEFOLDER':
							$wc[$w]['FILE_ID'] = $wc[$w]['VALUE01'];
							if ($wc[$w]['FILE_ID'] ) {
								$wc[$w]['VALUE03'] = sFileMgr()->getPNameByFileId($wc[$w]['FILE_ID']);
							}
							$wc[$w]['URL'] = '';
							if ($wc[$w]['VALUE03'] != "") {
								$wc[$w]['URL'] = $webroot . 'download/' . $wc[$w]['VALUE03'];
								$wc[$w]['IMAGE_URL'] = $webroot . 'image/' . $wc[$w]['VALUE03'];
								$wc[$w]['PNAME'] = $wc[$w]['VALUE03'];
							}
							break;
						case 'CO':
							$wc[$w]['CBLOCK_ID'] = $wc[$w]['VALUE01'];
							break;
						case 'TAG':
							$wc[$w]['TAG_ID'] = $wc[$w]['VALUE01'];
							break;
						case 'LIST':
							$wc[$w]['VALUE'] = $wc[$w]['VALUE01'];
							break;
					}
				}
			}
			return $wc;
		} else {
			return false;
		}
	}

/// @endcond

	/**
	 * Gets all Entrymasks of this Cblock
	 *
	 * @return array|false or FALSE in case of an error
	 */
	function getEntrymasks() {
		$cbId = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			$version = (int)$this->getVersion();
			$sql = "SELECT
						lnk.ID AS LINKID,
						lnk.ENTRYMASK AS ENTRYMASKID,
						ctrl.CODE AS CODE,
						ctrl.NAME AS ENTRYMASKNAME
					FROM
						`yg_contentblocks_lnk_entrymasks` as lnk,
						yg_entrymasks_properties as ctrl
					WHERE
						(CBID = ? AND CBVERSION = ?) AND
						(lnk.ENTRYMASK = ctrl.OBJECTID)
					ORDER BY ORDERPROD;";
			$ra = $this->cacheExecuteGetArray($sql, $cbId, $version);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Gets all Page versions linked to this Cblock
	 *
	 * @param int $site Site Id
	 * @param bool $published (optional) Gets only the published versions
	 * @param bool $latestversion (optional) Gets the latest approved version
	 * @return array|false Array of Page versions or FALSE in case of an error
	 */
	function getLinkedPageVersions($site, $published = false, $latestversion = false) {
		$cbId = (int)$this->_id;

		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			$site = (int)$site;
			if ($published == true) {
				$pfinalsql = "(
				(page.VERSION = ptree.VERSIONPUBLISHED) OR
				(
					(ptree.VERSIONPUBLISHED = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND
					(page.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_site_" . $site . "_properties AS rgt WHERE (page.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
					)
				)";
			} else {
				if ($latestversion == true) {
					$pfinalsql = "(page.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_site_" . $site . "_properties AS rgt WHERE (page.OBJECTID = rgt.OBJECTID)))";
				} else {
					$pfinalsql = "(page.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_site_" . $site . "_properties AS rgt WHERE (page.OBJECTID = rgt.OBJECTID) AND (ptree.VERSIONPUBLISHED = rgt.VERSION)))";
				}
			}
			$sql = "SELECT
						page.OBJECTID as ID, lnk.PID AS PID, lnk.PVERSION AS PVERSION, lnk.CBVERSION AS CBVERSION, page.HASCHANGED AS HASCHANGED, pv.*
					FROM
						(yg_site_" . $site . "_lnk_cb AS lnk, yg_site_" . $site . "_properties as page, yg_contentblocks_properties AS co, yg_site_" . $site . "_tree AS ptree)
					LEFT JOIN yg_site_" . $site . "_propsv AS pv ON pv.OID = page.ID
					WHERE
						(
							(lnk.CBID = $cbId) AND (page.OBJECTID = lnk.PID) AND (page.VERSION = lnk.PVERSION) AND (ptree.ID = page.OBJECTID) AND
							$pfinalsql
							AND (co.OBJECTID = lnk.CBID) AND ((lnk.CBVERSION = co.VERSION) OR
							(
							(lnk.CBVERSION = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND
							(co.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (co.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
							))
						)
					GROUP BY PID ORDER BY ORDERPROD ASC;";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Gets all Mailing versions linked to this Cblock
	 *
	 * @param bool $published (optional) Gets only the published versions
	 * @param bool $latestversion (optional) Gets the latest approved version
	 * @return array|false Array of Mailing versions or FALSE in case of an error
	 */
	function getLinkedMailingVersions($published = false, $latestversion = false) {
		$cbId = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			if ($published == true) {
				$pfinalsql = "(
						(mailing.VERSION = mtree.VERSIONPUBLISHED) OR
						(
						(mtree.VERSIONPUBLISHED = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND
						(mailing.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_mailing_properties AS rgt WHERE (mailing.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
						)
						)";
			} else {
				if ($latestversion == true) {
					$pfinalsql = "(mailing.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_mailing_properties AS rgt WHERE (mailing.OBJECTID = rgt.OBJECTID)))";
				} else {
					$pfinalsql = "(mailing.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_mailing_properties AS rgt WHERE (mailing.OBJECTID = rgt.OBJECTID) AND (mtree.VERSIONPUBLISHED = rgt.VERSION)))";
				}
			}
			$sql = "SELECT
					mailing.OBJECTID as ID, lnk.PID AS PID, lnk.PVERSION AS PVERSION, lnk.CBVERSION AS CBVERSION, mailing.HASCHANGED AS HASCHANGED, pv.*
					FROM
					(yg_mailing_lnk_cb AS lnk, yg_mailing_properties as mailing, yg_contentblocks_properties AS co, yg_mailing_tree AS mtree)
					LEFT JOIN yg_mailing_propsv AS pv ON pv.OID = mailing.ID
					WHERE
					(
						(lnk.CBID = $cbId) AND (mailing.OBJECTID = lnk.PID) AND (mtree.ID = mailing.OBJECTID) AND (mailing.DELETED = 0) AND
						$pfinalsql
						AND (co.OBJECTID = lnk.CBID) AND ((lnk.CBVERSION = co.VERSION) OR
						(
							(lnk.CBVERSION = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND
							(co.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (co.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
						))
					)
					GROUP BY PID ORDER BY ORDERPROD ASC;";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Gets all published Mailings linked to this Cblock
	 *
	 * @param bool $onlyPublished TRUE if only published Pageversions should be returned (optional)
	 * @return array|false Array of linked Mailings or FALSE in case of an error
	 */
	function getLinkedMailings($onlyPublished = false) {
		$cbId = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			$mailingMgr = sMailingMgr();
			$a = 0;
			$linkedtomailing = $this->getLinkedMailingVersions(true);
			if (count($linkedtomailing) > 0) {
				for ($x = 0; $x < count($linkedtomailing); $x++) {
					$linkmailingid = $linkedtomailing[$x]['ID'];
					$n = $mailingMgr->getMailing($linkmailingid);
					$mailingversions = $n->getVersionsByCblockId($cbId);
					$linkedMailingInfo = $n->get();
					$linkedmailings[$a]['MAILINGID'] = $linkmailingid;
					$linkedmailings[$a]['MAILINGNAME'] = $linkedMailingInfo['NAME'];
					$linkedmailings[$a]['HASCHANGED'] = $linkedtomailing[$x]['HASCHANGED'];
					if ($onlyPublished) {
						$tmpVersion = $n->getPublishedVersion(true);
						$linkedpages[$a]['VERSION'] = $tmpVersion;

						$linkedInPublishedVersion = false;
						foreach ($mailingversions as $mailingversionsItem) {
							if ($mailingversionsItem['VERSION'] == $tmpVersion) {
								$linkedInPublishedVersion = true;
							}
						}
						if (!$linkedInPublishedVersion) {
							unset($linkedmailings[$a]);
						} else {
							$a++;
						}
					} else {
						$linkedmailings[$a]['VERSIONS'] = $mailingversions;
						$a++;
					}
				}
			}
			return $linkedmailings;
		} else {
			return false;
		}
	}

	/**
	 * Gets all Pages of a specific Site which are linked to this Cblock
	 *
	 * @param int $siteId Site Id
	 * @return array|false Array of linked Pages or FALSE in case of an error
	 */
	function getLinkedPagesBySite($siteId) {
		$cbId = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			$siteId = (int)$siteId;
			$sql = "SELECT
					page.OBJECTID as ID, lnk.PID AS PID, lnk.PVERSION AS PVERSION, page.HASCHANGED AS HASCHANGED, pv.*
				FROM
					(yg_site_" . $siteId . "_lnk_cb AS lnk, yg_contentblocks_properties as co, yg_site_" . $siteId . "_properties as page )
				LEFT JOIN
					yg_site_" . $siteId . "_propsv AS pv ON pv.OID = page.ID
				WHERE
				(
					(co.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (co.OBJECTID = rgt.OBJECTID))) AND
					(lnk.CBID = $cbId) AND (co.OBJECTID = lnk.CBID) AND (page.OBJECTID = lnk.PID) AND
					(lnk.PVERSION = (SELECT MAX( rgt.PVERSION ) FROM yg_site_" . $siteId . "_lnk_cb AS rgt WHERE (lnk.PID = rgt.PID)) AND
					(lnk.PVERSION = page.VERSION))
				)
				GROUP BY PID
				ORDER BY ORDERPROD ASC";

			$ra = $this->cacheExecuteGetArray($sql);
			return $ra;
		} else {
			return false;
		}
	}

	/**
	 * Gets all published Pages linked to this Cblock
	 *
	 * @param bool $onlyPublished TRUE if only published Pageversions should be returned (optional)
	 * @return array|false Array of linked Pages or FALSE in case of an error
	 */
	function getLinkedPages($onlyPublished = false) {
		$cbId = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			$siteMgr = new Sites();
			$sites = $siteMgr->getList();
			$a = 0;
			for ($i = 0; $i < count($sites); $i++) {
				$linkedtosite = $this->getLinkedPageVersions($sites[$i]['ID'], true);
				if (count($linkedtosite) > 0) {
					for ($x = 0; $x < count($linkedtosite); $x++) {
						$linkpageid = $linkedtosite[$x]['ID'];
						$PageMgr = new PageMgr($sites[$i]['ID']);
						$linkpageparents = $PageMgr->getParents($linkpageid);
						$p = $PageMgr->getPage($linkpageid);
						$pageversions = $p->getVersionsByCblockId($cbId);
						$linkedPageInfo = $p->get();
						$linkedpages[$a]['SITEID'] = $sites[$i]['ID'];
						$linkedpages[$a]['SITENAME'] = $sites[$i]['NAME'];
						$linkedpages[$a]['PAGEID'] = $linkpageid;
						$linkedpages[$a]['PAGENAME'] = $linkedPageInfo['NAME'];
						$linkedpages[$a]['HASCHANGED'] = $linkedtosite[$x]['HASCHANGED'];
						$linkedpages[$a]['PARENTS'] = $linkpageparents;
						if ($onlyPublished) {
							$tmpVersion = $p->getPublishedVersion(true);
							$linkedpages[$a]['VERSION'] = $tmpVersion;
							$linkedInPublishedVersion = false;
							foreach ($pageversions as $pageversionsItem) {
								if ($pageversionsItem['VERSION'] == $tmpVersion) {
									$linkedInPublishedVersion = true;
								}
							}
							if (!$linkedInPublishedVersion) {
								unset($linkedpages[$a]);
							} else {
								$a++;
							}
						} else {
							$linkedpages[$a]['VERSIONS'] = $pageversions;
							$a++;
						}
					}
				}
			}
			return $linkedpages;
		} else {
			return false;
		}
	}

	/**
	 * Moves this Cblock to the trashcan
	 *
	 * @return array Array with all elements which were successfully deleted
	 */
	function delete() {
		$cbId = (int)$this->_id;
		$rootNode = sCblockMgr()->tree->getRoot();
		if ($cbId == $rootNode) {
			return array();
		}

		// Get folder for embedded cblocks
		$embeddedCblockFolder = (int)sConfig()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");

		// Check if object is a folder
		$successNodes = array();
		$cb = sCblockMgr()->getCblock($cbId);
		if ($cb) {
			$cblockInfo = $cb->get();
			if ($cblockInfo['FOLDER'] == 1) {
				$subNodes = sCblockMgr()->getList($cbId, array('SUBNODES'));
				if (count($subNodes) > 0) {
					foreach($subNodes as $subNode) {
						if ( $this->permissions->checkInternal($this->_uid, $subNode['ID'], "RDELETE") &&
								($subNode['ID'] != $embeddedCblockFolder) ) {
							$subCb = sCblockMgr()->getCblock($subNode['ID']);
							$successfullyDeleted = $subCb->delete();
							if (in_array($subNode['ID'], $successfullyDeleted) === true) {
								foreach($successfullyDeleted as $successfullyDeletedItem) {
									$successNodes[] = $successfullyDeletedItem;
								}
							}
						}
					}
				}
			}
		}

		if ($this->permissions->checkInternal($this->_uid, $cbId, "RDELETE") && ($cbId != $embeddedCblockFolder)) {
			// Move to root level
			sCblockMgr()->tree->moveTo($cbId, $rootNode);

			// Set to "DELETED"
			$sql = "UPDATE yg_contentblocks_properties SET DELETED = 1 WHERE OBJECTID = ?;";
			$result = sYDB()->Execute($sql, $cbId);
			$successNodes[] = $cbId;

			sCblockMgr()->callExtensionHook('onDelete', (int)$this->_id, (int)$this->_version);
		}

		if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/CBLOCK_DELETE") == "true") {
			Singleton::FC()->emptyBucket();
		}

		return $successNodes;
	}

	/**
	 * Restores this Cblock from the trashcan
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function undelete() {
		$cbId = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RDELETE")) {
			// restore from trashcan
			$sql = "UPDATE yg_contentblocks_properties SET DELETED = 0 WHERE OBJECTID = ?";
			sYDB()->Execute($sql, $cbId);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Approves the current version of this Cblock and creates a new working version
	 *
	 * @return int|false New version or FALSE in case of an error
	 */
	function approve() {
		$cbId = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RSTAGE")) {
			$this->approveVersion();
			sCblockMgr()->callExtensionHook('onApprove', (int)$this->_id, (int)$this->_version);
			return $this->newVersion();
		} else {
			return false;
		}
	}

	/**
	 * Approves the specified version of this Cblock
	 *
	 * @param int $version (optional) Cblock version
	 * @return int|false New version or FALSE in case of an error
	 */
	public function approveVersion($version = 0) {
		$result = parent::approveVersion($version);

		if ((int)$version == 0) $version = (int)$this->getVersion();

		$this->history->add(HISTORYTYPE_CO, NULL, $version, 'TXT_CBLOCK_H_APPROVE');

		if ($this->getPublishedVersion()==ALWAYS_LATEST_APPROVED_VERSION) {
			$this->history->add(HISTORYTYPE_CO, NULL, $version, 'TXT_CBLOCK_H_AUTOPUBLISH');
			sCblockMgr()->callExtensionHook("onPublish", $this->_id, $version);
			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/CBLOCK_PUBLISH") == "true") {
				Singleton::FC()->emptyBucket();
			}
		}
		return $result;
	}

	/**
	 * Copies Properties, Permissions, Tags and content from another Cblock into a new version of this Cblock
	 *
	 * @param object $sourceObject Source Cblock object
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function copyFrom(&$sourceObject) {
		$sourceID = $sourceObject->getID();
		$sourceVersion = $sourceObject->getVersion();
		$sourceInfo = $sourceObject->get();
		$targetID = (int)$this->_id;
		$targetVersion = $this->getVersion();
		parent::copyFrom($sourceObject);

		if ($sourceInfo["FOLDER"] == 0) {
			$sql = "DELETE FROM yg_contentblocks_lnk_entrymasks WHERE CBID = ? AND CBVERSION = ?;";
			sYDB()->Execute($sql, $targetID, $targetVersion);
			$sql = "SELECT * FROM yg_contentblocks_lnk_entrymasks WHERE CBID = ? AND CBVERSION = ?;";
			$dbr = sYDB()->Execute($sql, $sourceID, $sourceVersion);
			$links = $dbr->GetArray();
			for ($l = 0; $l < count($links); $l++) {
				$sql = "INSERT INTO yg_contentblocks_lnk_entrymasks (ENTRYMASK, CBID, CBVERSION, ORDERPROD) VALUES (?, ?, ?, ?)";
				sYDB()->Execute($sql, $links[$l]["ENTRYMASK"], $targetID, $targetVersion, $links[$l]["ORDERPROD"]);
				$linkId = (int)sYDB()->Insert_ID();
				$sql = "INSERT INTO `yg_contentblocks_lnk_entrymasks_c`
							(FORMFIELD, ENTRYMASKFORMFIELD, LNK, VALUE01, VALUE02, VALUE03, VALUE04, VALUE05, VALUE06, VALUE07, VALUE08)
						SELECT FORMFIELD, ENTRYMASKFORMFIELD, $linkId, VALUE01, VALUE02, VALUE03, VALUE04, VALUE05, VALUE06, VALUE07, VALUE08
						FROM yg_contentblocks_lnk_entrymasks_c WHERE (LNK = ?);";
				sYDB()->Execute($sql, $links[$l]["ID"]);
			}
		}
		$this->copyExtensionsFrom($sourceObject);
		$this->markAsChanged();
	}

	/**
	 * Copies Extensions from another Cblock to this Cblock
	 *
	 * @param object $sourceObject Source Cblock object
	 */
	function copyExtensionsFrom(&$sourceObject) {
		$sourceId = $sourceObject->getID();
		$sourceVersion = $sourceObject->getVersion();
		$targetId = $this->getID();
		$targetVersion = $this->getVersion();
		$extensions = new ExtensionMgr(sYDB(), $this->_uid);
		$all_extensions = $extensions->getList(EXTENSION_CBLOCK, true);
		foreach ($all_extensions as $all_extension) {
			$extension = $extensions->getExtension($all_extension['CODE']);
			if ($extension && ($extension->usedByCblock($sourceId, $sourceVersion) === true)) {
				if ($extension->usedByCblock($targetId, $targetVersion) !== true) {
					$newfid = $extension->addToCblockInternal($targetId, $targetVersion);
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
	 * Sets the permission of an embedded Entrymasks to the same permissions a page has
	 *
	 * @param object $page Page object
	 */
	function setPagePermissions($page) {
		// Get an instance of this new embedded Cblock and inherit rights from this page to embedded contentblock
		$usergroups = sUsergroups()->getList();
		foreach ($usergroups as $usergroupItem) {
			$usergroupId = $usergroupItem['ID'];
			$objectPermissions = $page->permissions->getByUsergroup($usergroupId, $page->getID());
			if ($objectPermissions) {
				$permissionsArray = array('RREAD', 'RWRITE', 'RDELETE', 'RSUB', 'RSTAGE');
				foreach ($permissionsArray as $permissionsItem) {
					if ($objectPermissions[$permissionsItem]) {
						$this->permissions->setByUsergroup($usergroupId, $permissionsItem, $this->getID(), 1);
					} else {
						$this->permissions->setByUsergroup($usergroupId, $permissionsItem, $this->getID(), 0);
					}
				}
			}
		}
	}

	/**
	 * Sets the order of Entrymasks in this Cblock
	 *
	 * @param array $entrymaskOrder
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setEntrymaskOrder($entrymaskOrder = array()) {
		$cbId = $this->_id;
		$version = (int)$this->getVersion();

		if ($this->permissions->checkInternal($this->_uid, $cbId, "RWRITE")) {
			for ($i = 0; $i < count($entrymaskOrder); $i++) {
				$sql = "UPDATE `yg_contentblocks_lnk_entrymasks`
				SET ORDERPROD = ? WHERE (CBID = ?) AND (ID = ?) AND (CBVERSION = ?);";
				$result = sYDB()->Execute($sql, $i, $cbId, $entrymaskOrder[$i], $version);
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
	 * Flags this Cblock as an "embedded" Cblock
	 *
	 * @param int $value (optional) 1 or 0: 1 sets the "embedded" flag, 0 removes it
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setEmbedded($value = 1) {
		$cbId = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RWRITE")) {
			$value = (int)$value;
			$sql = "UPDATE yg_contentblocks_properties SET EMBEDDED = ? WHERE (OBJECTID = ?);";
			$result = sYDB()->Execute($sql, $value, $cbId);
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
	 * Adds an Entrymask to this Cblock (by identifier)
	 *
	 * @param string $identifier Entrymask identifier
	 * @return int Entrymask Cblock Link Id
	 */
	function addEntrymaskByIdentifier($identifier) {
		$entrymasks = new Entrymasks();
		$entrymaskInfo = $entrymasks->getByIdentifier($identifier);
		$this->markAsChanged();
		return $this->addEntrymask($entrymaskInfo['OBJECTID']);
	}

	/**
	 * Adds an Entrymask to this version of the Cblock
	 *
	 * @param int $entrymaskId Entrymask identifier
	 * @return int Entrymask Cblock Link Id
	 * @throws Exception
	 */
	function addEntrymask($entrymaskId) {
		$cbId = $this->_id;
		$entrymaskId = (int)$entrymaskId;
		$version = (int)$this->getVersion();

		if ($this->permissions->checkInternal($this->_uid, $cbId, "RWRITE")) {
			$sql = "INSERT INTO `yg_contentblocks_lnk_entrymasks`
						(`CBID`, `CBVERSION`, `ENTRYMASK`, `ORDERPROD`)
					VALUES
						(?, ?, ?, 9999);";
			$result = sYDB()->Execute($sql, $cbId, $version, $entrymaskId);
			$insertid = sYDB()->Insert_ID();

			$controlFormfields = $this->control->getEntrymaskFormfields($entrymaskId);
			for ($cw = 0; $cw < count($controlFormfields); $cw++) {
				$sql = "INSERT INTO `yg_contentblocks_lnk_entrymasks_c`
							(FORMFIELD, ENTRYMASKFORMFIELD, LNK, VALUE01)
						VALUES
							(?, ?, ?, ?)";
				$result = sYDB()->Execute($sql, $controlFormfields[$cw]["FORMFIELD"], $controlFormfields[$cw]["ID"], $insertid, $controlFormfields[$cw]["PRESET"]);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
			}

			$this->markAsChanged();
			return $insertid;
		} else {
			return false;
		}
	}

	/**
	 * Removes an Entrymask from this Cblock
	 *
	 * @param int $linkId Entrymask Link Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function removeEntrymask($linkId) {
		$cbId = $this->getID();
		$linkId = (int)$linkId;
		$version = $this->getVersion();

		if ($this->permissions->checkInternal($this->_uid, $cbId, "RWRITE")) {
			$sql = "SELECT p.*,w.* FROM yg_entrymasks_properties AS p, yg_contentblocks_lnk_entrymasks AS w WHERE (w.ID = ?) AND (w.ENTRYMASK = p.OBJECTID);";
			$dbr = sYDB()->Execute($sql, $linkId);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$sql = "DELETE FROM `yg_contentblocks_lnk_entrymasks` WHERE (CBID = ? AND CBVERSION = ? AND ID = ?);";
			$dbr = sYDB()->Execute($sql, $cbId, $version, $linkId);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$sql = "DELETE FROM `yg_contentblocks_lnk_entrymasks_c` WHERE (LNK = ?);";
			$dbr = sYDB()->Execute($sql, $linkId);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->markAsChanged();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Saves content to a Formfield
	 *
	 * @param int $linkId Entrymask Formfield Link Id
	 * @param string $value01 Content for Formfield parameter 1
	 * @param string $value02 Content for Formfield parameter 2
	 * @param string $value02 Content for Formfield parameter 3
	 * @param string $value04 Content for Formfield parameter 4
	 * @param string $value05 Content for Formfield parameter 5
	 * @param string $value06 Content for Formfield parameter 6
	 * @param string $value07 Content for Formfield parameter 7
	 * @param string $value08 Content for Formfield parameter 8
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setFormfield($linkId, $value01, $value02, $value03, $value04, $value05, $value06, $value07, $value08) {
		$cbId = $this->_id;
		$linkId = (int)$linkId;

		if ($this->permissions->checkInternal($this->_uid, $cbId, "RWRITE")) {
			$value01 = sYDB()->escape_string($value01);
			$value02 = sYDB()->escape_string($value02);
			$value03 = sYDB()->escape_string($value03);
			$value04 = sYDB()->escape_string($value04);
			$value05 = sYDB()->escape_string($value05);
			$value06 = sYDB()->escape_string($value06);
			$value07 = sYDB()->escape_string($value07);
			$value08 = sYDB()->escape_string($value08);

			// Check if an URL needs to be generated
			$sql = "SELECT
						t.TYPE
					FROM
						`yg_contentblocks_lnk_entrymasks_c` AS c,
						`yg_formfields` AS t
					WHERE
						(c.FORMFIELD = t.ID) AND
						(c.ID = ?);";
			$ra = $this->cacheExecuteGetArray($sql, $linkId);
			$webRoot = (string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT");
			switch ($ra[0]['TYPE']) {
				case 'PAGE':
					if (strlen(trim($value02)) && strlen(trim($value01))) {
						$siteMgr = new Sites();
						$sitePName = $siteMgr->getPName($value02);
						$pageMgr = sPageMgr($value02);
						$tmpPage = $pageMgr->getPage($value01);
						$tmpPageInfo = $tmpPage->get();
						$value03 = $webRoot . $sitePName . '/' . $tmpPageInfo['PNAME'] . '/';
						$value04 = $tmpPageInfo['PNAME'];
						$value05 = $sitePName;
					} else {
						$value01 = $value02 = $value03 = $value04 = '';
					}
					break;
				case 'FILE':
					if (strlen(trim($value01))) {
						$tmpFile = sFileMgr()->getFile($value01);
						if ($tmpFile) {
							$tmpFileInfo = $tmpFile->get();
							$value02 = $webRoot . 'download/' . $tmpFileInfo['PNAME'] . '/';
							$value03 = $tmpFileInfo['PNAME'];
							$value04 = $webRoot . 'image/' . $tmpFileInfo['PNAME'] . '/';
						}
					} else {
						$value01 = $value02 = $value03 = '';
					}
					break;
			}

			$sql = "UPDATE `yg_contentblocks_lnk_entrymasks_c` SET
						VALUE01 = ?,
						VALUE02 = ?,
						VALUE03 = ?,
						VALUE04 = ?,
						VALUE05 = ?,
						VALUE06 = ?,
						VALUE07 = ?,
						VALUE08 = ?
					WHERE
						(ID = ?);";
			$result = sYDB()->Execute($sql, $value01, $value02, $value03, $value04, $value05, $value06, $value07, $value08, $linkId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			// Check to which object this entrymask belongs to
			$CblockInfo = $this->get();

			// Check if it is an embedded entrymask
			if ($CblockInfo['EMBEDDED']) {
				$sql = "SELECT PID, PVERSION FROM yg_mailing_lnk_cb WHERE CBID = " . $CblockInfo['OBJECTID'] . " AND CBVERSION = " . $CblockInfo['VERSION'] . ";";
				$linkedMailings = $this->cacheExecuteGetArray($sql);

				if (count($linkedMailings) > 0) {
					// Yes, it links to a Mailing
					$mailingMgr = new MailingMgr();
					foreach ($linkedMailings as $linkedMailing) {
						$mailing = $mailingMgr->getMailing($linkedMailing['PID'], $linkedMailing['PVERSION']);
						if ($mailing) {
							$mailing->markAsChanged();
						}
					}
				} else {
					// Check if it is related to a Page
					$sites = sSites()->getList(true, false);
					for ($i = 0; $i < count($sites); $i++) {
						$sql = "SELECT PID, PVERSION FROM yg_site_" . (int)$sites[$i]['ID'] . "_lnk_cb WHERE CBID = " . (int)$CblockInfo['OBJECTID'] . " AND CBVERSION = " . (int)$CblockInfo['VERSION'] . ";";
						$linkedPages = $this->cacheExecuteGetArray($sql);
						if (count($linkedPages) > 0) {
							// Yes, it links to a Pages
							$pageMgr = sPageMgr($sites[$i]['ID']);
							foreach ($linkedPages as $linkedPage) {
								$page = $pageMgr->getPage($linkedPage['PID'], $linkedPage['PVERSION']);
								if ($page) {
									$page->markAsChanged();
								}
							}
						}
					}
				}
			} else {
				// Entrymask is NOT embedded, so mark this Cblock as changed
				$this->markAsChanged();
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the permanent name of this Cblock
	 *
	 * @param string $pname Permanent name
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function setPName($pname) {
		$cbId = $this->_id;
		$pname = sYDB()->escape_string(sanitize($pname));

		if ($this->permissions->checkInternal($this->_uid, $cbId, "RWRITE")) {
			$pname = $this->filterPName($pname);

			if (is_numeric($pname)) {
				return false;
			}

			$checkpinfo = sCblockMgr()->getCblockIdByPName($pname);
			if (($checkpinfo["ID"] != $cbId) && ($checkpinfo["ID"] > 0)) {
				$pname = $pname . $cbId;
			} else {
				if (($checkpinfo["ID"] > 0) && ($checkpinfo["ID"] == $cbId)) {
				} else {
				}
			}

			$sql = "SELECT PNAME AS STATE FROM yg_contentblocks_tree WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $cbId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			$sql = "UPDATE yg_contentblocks_tree SET PNAME = '$pname' WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $cbId);
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

}

?>