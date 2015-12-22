<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Gets an instance of the MailingMgr
 * @return object MailingMgr object
 */
function sMailingMgr() {
	return Singleton::mailingMgr();
}

/**
 * The MailingMgr class, which represents an instance of the Mailing manager.
 */
class MailingMgr extends \framework\Error {
	var $_db;
	var $_uid;

	var $db;
	var $baum;
	var $table;

	var $properties;
	var $history;
	var $permissions;
	var $scheduler;

	/**
	 * Constructor of the PageMgr class
	 */
	public function __construct() {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->_config = $config;
		$this->table = "yg_mailing_tree";
		$this->tree = new tree($this);
		$this->permissions = new Permissions("yg_mailing_permissions", $this);
		$this->history = new History($this, "mailing", $this->permissions);
		$this->tags = new Tags($this);
		$this->properties = new PropertySettings("yg_mailing_props");
		$this->scheduler = new Scheduler("yg_cron", SCHEDULER_MAILING);
		$this->templates = new Templates();
	}

	/**
	 * Gets a specific Mailing instance
	 *
	 * @param int $mailingId Mailing Id
	 * @param int $version (optional) Mailing version
	 * @return Mailing|false New instance of Mailing object or FALSE if an error has occured
	 */
	public function getMailing($mailingId, $version = 0) {
		try {
			return new Mailing($mailingId, $version);
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Gets an instance of a published Mailing
	 *
	 * @param int $mailingId Mailing Id
	 * @return Mailing|false New instance of Mailing object or FALSE if an error has occured
	 */
	public function getPublishedMailing($mailingId) {
		$tmpMailing = $this->getMailing($mailingId);
		if ($tmpMailing) {
			$tmpMailingVersion = $tmpMailing->getPublishedVersion(true);
			return $this->getMailing($mailingId, $tmpMailingVersion);
		} else {
			return false;
		}
	}

/// @cond DEV

	/**
	 * Gets the object prefix, used for table names in database queries
	 *
	 * @return string
	 */
	function getObjectPrefix() {
		return 'mailings';
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets value of the additional identifier
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

	/**
	 * Callback method which is executed when Usergroup permissions on a Mailing changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId Mailing Id
	 */
	public function onPermissionChange($usergroupId, $permission, $value, $objectId) {
		// Also set these permissions to the all blinds Cblocks in this Mailing
		$templateMgr = new Templates();

		$mailing = $this->getMailing($objectId);
		if ($mailing) {
			$mailingVersions = $mailing->getVersions();

			foreach ($mailingVersions as $mailingVersions_item) {
				$tmpMailing = $this->getMailing($objectId, $mailingVersions_item['VERSION']);
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

			$mailingPermissions = $mailing->permissions->getByUsergroup($usergroupId, $objectId);
			if ($mailingPermissions) {
				foreach ($blindCos as $coid) {
					$bcb = sCblockMgr()->getCblock($coid);
					$bcb->permissions->setByUsergroup($usergroupId, $permission, $coid, $value);
				}
			}
		}
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on a Mailing change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param int $objectId Mailing Id
	 */
	public function onPermissionsChange($usergroupId, $permissions, $objectId) {
		return true;
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
	 * Gets the name of the database table containing the Mailings tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return "yg_mailing_tree";
	}

/// @endcond

/// @cond DEV

	/**
	 * Calls a specific Extension hook Callback method
	 *
	 * @param string $method
	 * @param int $mailingId Mailing Id
	 * @param int $version Mailing version
	 * @param mixed $args Arbitrary arguments
	 */
	function callExtensionHook($method, $mailingId, $version, $args) {
		$extensions = new ExtensionMgr($this->_db, $this->_uid);
		$all_mailing_extensions = $extensions->getList(EXTENSION_MAILING, true);
		$extarr = array();
		foreach ($all_mailing_extensions as $all_mailing_extension) {
			$extension = $extensions->getExtension($all_mailing_extension['CODE']);
			if ($extension && $extension->usedByMailing($mailingId, $version) === true) {
				$extension = $extensions->getExtension($all_mailing_extension['CODE'], $mailingId, $version);
				if ($extension) {
					array_push($extarr, $extension);
				}
			}
		}
		$origargs = $args;
		$ext_result = false;
		foreach ($extarr as $extension) {
            $ext_result = $extension->callExtensionHook($method, $args);
            if ($ext_result && ($method == "beforeSend")) {
                $args = $ext_result;
            }
        }
		if ($ext_result === false) {
			return $origargs;
		} else {
	        return $ext_result;
		}
	}

/// @endcond

	/**
	 * Adds a new Mailing to the specified parent Mailing
	 *
	 * @param int $parentMailingId Parent Mailing Id
	 * @param int $templateId Template Id
	 * @param string $name (optional) Mailing name
	 * @return int|false New Mailing Id or FALSE in case of an error
	 * @throws Exception
	 */
	function add($parentMailingId, $templateId = 0, $name = "New Mailing") {
		$parentMailingId = (int)$parentMailingId;
		$name = sYDB()->escape_string($name);
		$templateId = (int)$templateId;
		if ($this->permissions->checkInternal($this->_uid, $parentMailingId, "RSUB")) {
			$mailingId = $this->tree->add($parentMailingId);

			// Version anlegen
			$ts = time();
			$sql = "INSERT INTO
						`yg_mailing_properties`
					(`OBJECTID`, `VERSION`, `TEMPLATEID`, `NAVIGATION`, `ACTIVE`, `HIDDEN`, `LOCKED`, `CREATEDTS`, `CHANGEDTS`, `CREATEDBY`, `CHANGEDBY`)
						VALUES
					(?, '1', ?, '0', '0', '0', '0', ?, ?, ?, ?);";
			$result = sYDB()->Execute($sql, $mailingId, $templateId, $ts, $ts, $this->_uid, $this->_uid);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			sYDB()->Insert_ID();

			// rechte vom parent kopieren
			$this->permissions->copyTo($parentMailingId, $mailingId);

			$tmpMailing = $this->getMailing($mailingId);
			$tmpMailing->properties->setValue("NAME", $name);
			$tmpMailing->publishVersion(ALWAYS_LATEST_APPROVED_VERSION);

			$tmpMailingInfo = $tmpMailing->get();
			$this->callExtensionHook("onAdd", $mailingId, $tmpMailingInfo['VERSION']);

			$tmpMailing->markAsChanged();

			// Add to history
			$tmpMailing->history->add(HISTORYTYPE_MAILING, NULL, 1, 'TXT_MAILING_H_NEWVERSION');

			return $mailingId;
		} else {
			return false;
		}
	}

	/**
	 * Removes a Mailing from the Trash
	 *
	 * @param int $mailingId Mailing Id
	 *
	 * @return array Array with all elements which were successfully deleted
	 */
	function remove($mailingId) {
		$mailingId = $origMailingId = (int)$mailingId;
		$rootNode = $this->tree->getRoot();

		if ($mailingId == $rootNode) {
			return array();
		}

		// Get all nodes
		$successNodes = array();
		$allNodes = $this->tree->get($mailingId, 1000);
		foreach($allNodes as $allNodesItem) {
			$mailingId = (int)$allNodesItem['ID'];

			if ($this->permissions->checkInternal($this->_uid, $mailingId, "RDELETE")) {
				// Collect and remove all linked blind contentblocks
				$sql = "SELECT * FROM `yg_mailing_lnk_cb` WHERE PID = $mailingId;";
				$linked_cos = $this->cacheExecuteGetArray($sql);

				$c = sCblockMgr();
				foreach ($linked_cos as $linked_co) {
					$cblock = $c->getCblock($linked_co['CBID']);
					if ($cblock) {
						$coInfo = $cblock->get();
						// Blind contentblock?
						if ($coInfo['EMBEDDED'] == 1) {
							$cblock->delete();
							$c->remove($linked_co['CBID']);
						}
					}
				}

				$tmpMailing = $this->getMailing($mailingId);
				$mailingInfo = $tmpMailing->get();
				$tmpMailing->tags->clear();
				$tmpMailing->history->clear();

				// Remove mailing
				$sql = "DELETE FROM `yg_mailing_properties` WHERE OBJECTID = ?;";
				sYDB()->Execute($sql, $mailingId);

				// Remove content object links
				$sql = "DELETE FROM `yg_mailing_lnk_cb` WHERE PID = ?;";
				sYDB()->Execute($sql, $mailingId);

				// Remove statusinfo
				$sql = "DELETE FROM `yg_mailing_status` WHERE OID = ?;";
				sYDB()->Execute($sql, $mailingId);

				$this->callExtensionHook('onRemove', $mailingId, 0, $mailingInfo);

				$successNodes[] = $mailingId;
			}
		}
		if (in_array($origMailingId, $successNodes)) {
			$this->tree->remove($origMailingId);
		}
		if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/MAILING_DELETE") == "true") {
			Singleton::FC()->emptyBucket();
		}
		return $successNodes;
	}

	/**
	 * Get Mailing tree nodes
	 *
	 * @param int $mailingId (optional) From which Mailing Id the tree should be returned
	 * @param int $maxLevels (optional) Specifies the maximum level of nodes to get
	 * @return array Array of Mailing nodes
	 */
	function getTree($mailingId = NULL, $maxLevels = 2) {
		$maxLevels = (int)$maxLevels;

		if ($mailingId > 0) {
			$currentLevel = $this->tree->getLevel($mailingId);
		} else {
			$currentLevel = 1;
			$mailingId = $this->tree->getRoot();
		}
		if ($currentLevel < 1) {
			return;
		}

		/*****/
		//if ($noTrash) {
		//	$filterSQL_WHERE = " AND prop.DELETED = 0";
		//}

		$maxLevelSQL = " AND (group2.LEVEL <= " . ($maxLevels + $currentLevel) . ") AND (group2.LEVEL <= " . ($maxLevels + $currentLevel) . ")";

		$myinfo = $this->tree->getAll($mailingId);
		$subnodeSQL = " AND (group2.LFT >= " . $myinfo["LFT"] . ") AND (group2.RGT <= " . $myinfo["RGT"] . ")";

		// SQL for permissions
		$perm_SQL_SELECT = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RMODERATE) AS RMODERATE, MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_SQL_FROM = " LEFT JOIN yg_mailing_permissions AS perm ON perm.OID = group2.ID";

		$perm_SQL_WHERE = " AND (";
		$roles = $this->permissions->getUsergroups();
		for ($r = 0; $r < count($roles); $r++) {
			$perm_SQL_WHERE .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
			$roles = $this->permissions->getUsergroups();
			if ((count($roles) - $r) > 1) {
				$perm_SQL_WHERE .= " OR ";
			}
		}
		$perm_SQL_WHERE .= ") ";

		$sql = "SELECT
					group2.ID, group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.TITLE, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT, group2.PNAME AS PNAME,
					prop.VERSION AS VERSION, prop.HASCHANGED AS HASCHANGED, pv.NAME as NAME, prop.ACTIVE, prop.TEMPLATEID
					$perm_SQL_SELECT
				FROM
					(yg_mailing_properties AS prop)
				LEFT JOIN
					$this->table AS group2 ON ((group2.ID = prop.OBJECTID) $maxLevelSQL $subnodeSQL)
					$perm_SQL_FROM
				LEFT JOIN
					yg_mailing_propsv AS pv ON pv.OID = prop.ID
				WHERE
					(prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_mailing_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID)))
					$perm_SQL_WHERE $maxLevelSQL $subnodeSQL $filterSQL_WHERE
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID;";
		$tree = $this->cacheExecuteGetArray($sql);

		return $tree;
	}

	/**
	 * Gets the Default-Template for Mailings
	 *
	 * @param int $templateId Template Id
	 * @return bool TRUE on success or FALSE if an error has occured
	 */
	public function setDefaultTemplate($templateId) {
		if (sUsergroups()->permissions->check($this->_uid, 'RMAILINGCONFIG')) {
			$templateId = (int)$templateId;
			$sql = "UPDATE yg_mailing_settings SET DEFAULTTEMPLATE = ? WHERE ID = 1;";
			$result = $this->_db->execute($sql, $templateId);
			if ($result === false) {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets the Default-Template for Mailings
	 *
	 * @return string Default-Template-Id for Mailings
	 */
	public function getDefaultTemplate() {
		$sql = "SELECT DEFAULTTEMPLATE FROM yg_mailing_settings WHERE ID = 1;";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra[0]['DEFAULTTEMPLATE'];
	}

	/**
	 * Sets the Template-Root for Mailings
	 * @param int $templateId Template Id
	 * @return string Template-Root-Id for Mailings
	 */
	public function setTemplateRoot($templateId) {
		if (sUsergroups()->permissions->check($this->_uid, 'RMAILINGCONFIG')) {
			$templateId = (int)$templateId;
			$sql = "UPDATE yg_mailing_settings SET TEMPLATEROOT = ? WHERE ID = 1;";
			$result = $this->_db->execute($sql, $templateId);
			if ($result === false) {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets the Template-Root for Mailings
	 *
	 * @return string Template-Root-Id for Mailings
	 */
	public function getTemplateRoot() {
		$sql = "SELECT TEMPLATEROOT FROM yg_mailing_settings WHERE ID = 1;";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra[0]['TEMPLATEROOT'];
	}

	/**
	 * Gets a list of Mailings
	 *
	 * @param int $mailingId (optional) Id of the parent Mailing from which the list will be created
	 * @param array $filter (optional, may be combined) If SUBNODES, only subnodes of the specified Mailing will be returned<br>
	 *                                 if TRASHCAN, only items in the Trash will be returned<br>
	 *                                 if PUBLISHED, the working copy will be returned
	 * @param int $maxLevel (optional) Specifies the maximum level of nodes to get
	 * @param int $permissionsForRoleId (optional) If '1' then return all Usergroups and Permissions for this Usergroup
	 * @param array $filterArray Array of filters for the SQL query
	 * @return array|false Array of Mailings or FALSE in case of an error
	 */
	function getList($mailingId = 0, $filter = array(), $maxLevel = 0, $permissionsForRoleId = 0, $filterArray) {
		$mailingId = (int)$mailingId;
		$maxLevel = (int)$maxLevel;
		$permissionsForRoleId = (int)$permissionsForRoleId;
		$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");

		if ($mailingId == 0) {
			$mailingId = $this->tree->getRoot();
		}
		if ($maxLevel > 0) {
			$maxLevelsql = " AND (group2.LEVEL <= $maxLevel) AND (group1.LEVEL <= $maxLevel)";
		}
		if (in_array("SUBNODES", $filter)) {
			$myinfo = $this->tree->getAll($mailingId);
			$myleft = $myinfo["LFT"];
			$myrgt = $myinfo["RGT"];
			$subnodesql = " AND (group1.LFT > $myleft AND group1.RGT < $myrgt)";
			if (!$myinfo) {
				return false;
			}
		}
		// surpress items in trashcan if not explicitly asked for
		if (in_array("TRASHCAN", $filter)) {
			$filtersql_where = " AND prop.DELETED = 1";
		} else {
			$filtersql_where = " AND prop.DELETED = 0";
		}
		$filterOrder = 'ORDER BY prop.CHANGEDTS DESC';

		// Check if special filter was suppplied
		if ($filterArray) {
			$filterSelect = $filterFrom = $filterWhere = $filterLimit = $filterOrder = '';
			buildBackendFilter('MailingsSearchCB', $filterArray, $filterSelect, $filterFrom, $filterWhere, $filterLimit, $filterOrder);
			$filtersql_where .= $filterWhere;
		}

		//  AND (prop.ACTIVE = 1) - 20070313
		if (in_array("PUBLISHED", $filter)) {
			$filtersql_where .= "
				AND (
						(group2.VERSIONPUBLISHED = prop.VERSION)
					OR
						(
							(group2.VERSIONPUBLISHED = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND
							(prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_mailing_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
						)
					) ";
		} else {
			$filtersql_where .= " AND (prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_mailing_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID))) ";
			$lastfinal = ", (SELECT MAX(VERSION) FROM yg_mailing_properties AS p2 WHERE p2.APPROVED = 1 AND p2.OBJECTID = prop.OBJECTID ) AS LASTAPPROVED ";
		}

		$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RMODERATE) AS RMODERATE, MAX(perm.RCOMMENT) AS RCOMMENT, MAX(perm.RSEND) AS RSEND";
		$perm_sql_from = " LEFT JOIN yg_mailing_status AS stat ON stat.OID = group2.ID";
		$perm_sql_from .= " LEFT JOIN yg_mailing_permissions AS perm ON perm.OID = group2.ID";

		if ($permissionsForRoleId > 0) {
			$perm_sql_from .= " AND (perm.USERGROUPID = " . $permissionsForRoleId . ")";
		} else {
			$perm_sql_where = " AND (";
			$roles = $this->permissions->getUsergroups();
			for ($r = 0; $r < count($roles); $r++) {
				$perm_sql_where .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
				if ((count($roles) - $r) > 1) {
					$perm_sql_where .= " OR ";
				}
			}
			$perm_sql_where .= ") ";
			$perm_sql_where .= " AND ((RREAD >= 1) OR (perm.USERGROUPID = $rootGroupId)) ";
		}

		$sql = "SELECT
					group2.LFT,
					group2.RGT,
					group2.VERSIONPUBLISHED AS VERSIONPUBLISHED,
					group2.ID,
					group2.LEVEL AS LEVEL,
					group2.PARENT AS PARENT,
					group2.PNAME AS PNAME,
					MAX(prop.VERSION) AS VERSION,
					prop.ACTIVE AS ACTIVE,
					prop.HIDDEN AS HIDDEN,
					prop.LOCKED AS LOCKED,
					prop.HASCHANGED AS HASCHANGED,
					prop.TEMPLATEID AS TEMPLATEID,
					prop.CREATEDTS,
					prop.CHANGEDTS,
					prop.CREATEDBY,
					prop.CHANGEDBY,
					pv.*
					$perm_sql_select
					$lastfinal
				FROM
					($this->table AS group1, $this->table AS group2, yg_mailing_properties AS prop)
					$perm_sql_from
				LEFT JOIN yg_mailing_propsv AS pv
					ON pv.OID = prop.ID
				WHERE
					((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
					(group2.ID = prop.OBJECTID) $subnodesql $filtersql_where
					$perm_sql_where
					$maxLevelsql
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID
				$filterOrder $filterLimit;";


		$blaetter = $this->cacheExecuteGetArray($sql);

		return ($blaetter);
	}

	/**
	 * Gets additional information for Tree nodes
	 *
	 * @param int $mailingId (optional) Id of the parent Mailing from which the list will be created
	 * @param array $objects Array of Tree nodes
	 * @return array Array of Tree nodes
	 */
	function getAdditionalTreeInfo($mailingId, $objects) {
		$selectdefault = true;
		if ($mailingId < 1) {
			$selectdefault = true;
		}
		$lastparent = "";

		$shadowmenue = array();
		$parents = array();
		for ($i = 0; $i < count($objects); $i++) {
			$currentid = $objects[$i]["ID"];
			$mylevel = $objects[$i]["LEVEL"];
			$myparent = $objects[$i]["PARENT"];
			$myparentparent = $shadowmenue[$myparent]["PARENT"];
			$myparentparentparent = $shadowmenue[$myparentparent]["PARENT"];
			$myparentparentparentparent = $shadowmenue[$myparentparentparent]["PARENT"];

			if (($selectdefault == true) && ($mylevel > 1)) {
				$mailingId = $currentid;
				$selectdefault = false;
			}
			$shadowmenue[$currentid]["LEVEL"] = $mylevel;
			$shadowmenue[$currentid]["PARENT"] = $myparent;
			$shadowmenue[$currentid]["HIDDEN"] = $objects[$i]["HIDDEN"];
			$shadowmenue[$currentid]["ACTIVE"] = $objects[$i]["ACTIVE"];

			if (($lastlevel + 1 == $mylevel)) {
				$parents[$mylevel] = $lastid;
			} else {
				$lastparent = $currentid;
			}
			$shadowmenue[$myparent]["CHILDREN"] += 1;
			$shadowmenue[$myparent]["LASTNODE"] = $currentid;
			if ($currentid == $mailingId) {
				$objects[$i]["SELECTED"] = 1;
				$objects[$i]["SHOW"] = 1;
				$objects[$i]["SUBOPEN"] = 1;
				$shadowmenue[$currentid]["SHOW"] = 1;
				$shadowmenue[$currentid]["SUBOPEN"] = 1;
				$shadowmenue[$currentid]["SELECTED"] = 1;
				$shadowmenue[$myparent]["SHOW"] = 1;
				$shadowmenue[$myparent]["SUBOPEN"] = 1;
			}
			if (($shadowmenue[$myparent]["SUBOPEN"] == 1)) {
				$objects[$i]["SHOW"] = 1;
				$shadowmenue[$myparent]["SHOW"] = 1;
			}
			if ($objects[$i]["SHOW"] == 1) {
				$shadowmenue[$myparent]["SHOW"] = 1;
				$shadowmenue[$myparent]["SUBOPEN"] = 1;
				$shadowmenue[$myparentparent]["SHOW"] = 1;
				$shadowmenue[$myparentparent]["SUBOPEN"] = 1;
				$shadowmenue[$myparentparentparent]["SHOW"] = 1;
				$shadowmenue[$myparentparentparent]["SUBOPEN"] = 1;
				$shadowmenue[$myparentparentparentparent]["SHOW"] = 1;
				$shadowmenue[$myparentparentparentparent]["SUBOPEN"] = 1;
			}
			if ($objects[$i]["ACTIVE"] == 1) {
				$shadowmenue[$myparent]["HASVISIBLESUBNODES"] = 1;
			}
			$lastlevel = $mylevel;
			$lastid = $currentid;
		}

		for ($i = 0; $i < count($objects); $i++) {
			$currentid = $objects[$i]["ID"];
			$myparent = $objects[$i]["PARENT"];
			$myparentparent = $shadowmenue[$myparent]["PARENT"];
			$preid = $objects[$i - 1]["ID"];
			$postid = $objects[$i + 1]["ID"];
			$objects[$i]["SUBOPEN"] = $shadowmenue[$currentid]["SUBOPEN"];
			$objects[$i]["SHOWSUB"] = $shadowmenue[$currentid]["SHOWSUB"];
			$objects[$i]["CHILDREN"] = $shadowmenue[$currentid]["CHILDREN"];

			$objects[$i]["SHOW"] = $shadowmenue[$currentid]["SHOW"];
			if ($shadowmenue[$preid]["LEVEL"] < $blaetter[$i]["LEVEL"]) {
				$objects[$i]["FIRST"] = 1;
			}
			if (($shadowmenue[$postid]["LEVEL"] < $blaetter[$i]["LEVEL"])) {
				$objects[$i]["LAST"] = 1;
			}
			if ($shadowmenue[$postid]["LEVEL"] == "") {
				$objects[$i]["LAST"] = 1;
			}
			if ($shadowmenue[$myparent]["LASTNODE"] == $currentid) {
				$objects[$i]["LAST"] = 1;
			}
			if (($objects[$i]["SHOW"] == 1)) {
				$shadowmenue[$myparent]["SHOW"] = 1;
				$shadowmenue[$myparentparent]["SHOW"] = 1;
				$shadowmenue[$myparentparent]["SUBOPEN"] = 1;
			}
			if ($shadowmenue[$currentid]["HASVISIBLESUBNODES"] == 1) {
				$objects[$i]["HASVISIBLESUBNODES"] = 1;
			}
			if (($shadowmenue[$myparent]["SUBOPEN"] == 1)) {
				$objects[$i]["SHOW"] = 1;
			}
			/*
			if ($shadowmenue[$postid]["LEVEL"] > $blaetter[$i]["LEVEL"]) {
				$objects[$i]["HASSUBNODES"] = 1;
			} else {
				$objects[$i]["HASSUBNODES"] = 0;
			}
			*/
		}
		return ($objects);
	}

	/**
	 * Completely removes a Usergroup from the Mailing-Usergroups Link table
	 *
	 * @param int $usergroupId Usergroup Id
	 * @return bool TRUE if successful, or FALSE in case of an error
	 * @throws Exception
	 */
	public function removeUsergroupFromMailings($usergroupId) {
		$usergroupId = (int)$usergroupId;
		$sql = "DELETE FROM `yg_mailing_lnk_usergroups` WHERE RID = ?;";
		$result = sYDB()->Execute($sql, $usergroupId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg() . "<br>" . $sql);
		}
		return true;
	}

	/**
	 * Gets the parents of the specified Mailing
	 *
	 * @param int $mailingId Mailing Id
	 * @return array Array of parent Mailings
	 */
	function getParents($mailingId) {
		if ($this->permissions->checkInternal($this->_uid, $mailingId, "RREAD")) {
			$mailingId = (int)$mailingId;
			$parentID = $this->tree->getParent($mailingId);
			$parentMailing = $this->getMailing($parentID);
			$i = 0;
			$rootlevelID = $this->tree->getRoot();

			while ($parentID >= $rootlevelID) { // FIXME: this should be probably done on LEVEL not ID
				$objectVersion = $parentMailing->getVersion();
				$sql = "SELECT
				group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID AS ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT, group2.PNAME AS PNAME,
				MAX(prop.VERSION) AS VERSION, prop.ACTIVE AS ACTIVE, prop.HIDDEN AS HIDDEN, prop.LOCKED AS LOCKED, prop.TEMPLATEID AS TEMPLATEID, pv.NAME AS NAME
				FROM
				$this->table AS group2, yg_mailing_properties AS prop, yg_mailing_propsv as pv
				WHERE
				(group2.ID = prop.OBJECTID) AND (group2.ID = $parentID) AND (prop.VERSION = $objectVersion) AND
				(pv.OID = prop.ID)
				GROUP BY
				group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID order by group2.LFT;";
				$parents[$i] = $this->cacheExecuteGetArray($sql);
				$parentID = $parents[$i][0]["PARENT"];
				if ($parentID > 0) {
					$parentMailing = $this->getMailing($parentID);
				}
				$i++;
			}
			return $parents;
		} else {
			return false;
		}
	}

	/**
	 * Gets Cblock Mailing Link
	 *
	 * @param int $linkId Cblock Mailing Link Id
	 * @return array Information about Cblock Mailing Link
	 */
	function getCblockLinkById($id) {
		$id = (int)$id;
		$sql = "SELECT
					ID,
					CBID AS CBLOCKID,
					CBVERSION AS CBLOCKVERSION,
					CBPID,
					PID AS MAILINGID,
					PVERSION AS MAILINGVERSION,
					ORDERPROD,
					TEMPLATECONTENTAREA
				FROM
					yg_mailing_lnk_cb
				WHERE
					ID = $id;";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra;
	}

	/**
	 * Gets Mailing Id by permanent name
	 *
	 * @param string $PName permanent name
	 * @return int Page Id
	 */
	function getMailingIdByPName($pname) {
		$pname = sYDB()->escape_string(sanitize($pname));
		$sql = "SELECT ID FROM yg_mailing_tree as t WHERE (t.PNAME = '$pname')";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra[0]["ID"];
	}

	/**
	 * Gets Mailings by Template Id
	 *
	 * @param int $templateId Template Id
	 * @return array Mailing nodes
	 */
	function getMailingsByTemplate($templateId) {
		$templateId = (int)$templateId;
		$resolveCblocks = (int)$resolveCblocks;
		if ($this->_uid > 0) {
			$privileges_from = ", yg_mailing_permissions as priv";
		}
		if ($id < 1) {
			$selectdefault = true;
		}
		$filtersql_where .= " AND (
								(group2.VERSIONPUBLISHED = prop.VERSION)
								  OR
								  (
				  (group2.VERSIONPUBLISHED = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND
				  (prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_mailing_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
								  )
			   ) ";
		$perm_sql_select = ", MAX(perm.RREAD) AS RREAD, MAX(perm.RWRITE) AS RWRITE, MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RMODERATE) AS RMODERATE, MAX(perm.RCOMMENT) AS RCOMMENT, MAX(perm.RSEND) AS RSEND";
		$perm_sql_from = " LEFT JOIN yg_mailing_permissions AS perm ON perm.OID = group2.ID";
		$perm_sql_where = " AND (";
		$roles = $this->permissions->getUsergroups();
		for ($r = 0; $r < count($roles); $r++) {
			$perm_sql_where .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
			if ((count($roles) - $r) > 1) {
				$perm_sql_where .= " OR ";
			}
		}
		$perm_sql_where .= ") ";
		$sql = "SELECT
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED AS VERSIONPUBLISHED, group2.ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT, group2.PNAME AS PNAME,
					MAX(prop.VERSION) AS VERSION, prop.ACTIVE AS ACTIVE, prop.HIDDEN AS HIDDEN, prop.LOCKED AS LOCKED,
					prop.HASCHANGED AS HASCHANGED, prop.TEMPLATEID AS TEMPLATEID,
					pv.*
					$perm_sql_select
				FROM
					($this->table AS group1, $this->table AS group2, yg_mailing_properties AS prop)
					$perm_sql_from
					LEFT JOIN yg_mailing_propsv AS pv ON pv.OID = prop.ID
				WHERE
					((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
					(prop.DELETED = 0) AND
					(group2.ID = prop.OBJECTID) AND (prop.TEMPLATEID = $templateId)  $filtersql_where
					$perm_sql_where
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID  order by group2.LFT;";

		$blaetter = $this->cacheExecuteGetArray($sql);
		return $blaetter;
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

	/**
	 * Gets Locks for the specific Token
	 *
	 * @param string $token Lock Token
	 * @return array Array of Mailing Locks
	 * @throws Exception
	 */
	public function getLocksByToken($token) {
		$token = sYDB()->escape_string($token);
		if ($token == "") {
			return false;
		}
		$sql = "SELECT OBJECTID, LOCKED, TOKEN FROM yg_mailing_properties WHERE TOKEN = ?;";
		$dbr = sYDB()->Execute($sql, $token);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . "<br>" . $sql);
		}
		$ra = $dbr->GetArray();
		return $ra;
	}
}

settype($template, "string");

/// @cond DEV

/**
 * Callback function dynamic creation of filters for the buildBackendFilter function
 *
 * @param array $list Reference to the list of WHERE conditions from the buildBackendFilter function
 * @param string $type Type of filter for SQL query
 * @param string $operator Operator for SQL query
 * @param int $value1 (optional) General purpose parameter for SQL query
 * @param int $value2 (optional) General purpose parameter for SQL query
 */
function MailingsSearchCB(&$list, $type, $operator, $value1 = 0, $value2 = 0) {
	$op = GetContainsOperators($operator);
	switch ($type) {
		case "STATUS":
			if ($value1 != 'ALL') {
				if (!in_array($value1, array('INPROGRESS', 'UNSENT', 'SENT', 'PAUSED', 'CANCELLED'))) break;
				$list["WHERE"][] = "stat.STATUS = '" . sYDB()->escape_string($value1) . "'";
			}
			break;

		case "CREATEDTS":
			if ($value1 > 0) {
				$list["WHERE"][] = "prop.CREATEDTS " . $op . " " . (int)$value1;
			}
			break;

		case "CHANGEDTS":
			if ($value1 > 0) {
				$list["WHERE"][] = "prop.CHANGEDTS " . $op . " " . (int)$value1;
			}
			break;

		case "LIMITER":
			if ((int)$value2 > 0) {
				$list["LIMIT"][] = "LIMIT " . (int)$value1 . "," . (int)$value2;
			}
			break;

		case 'ORDER':
			$colarr = explode(".", sYDB()->escape_string(sanitize($value1)));
			$value1 = "`".implode("`.`", $colarr)."`";
			if ($value2 != "DESC") $value2 = "ASC";
			$list['ORDER'][] = 'ORDER BY ' . $value1 . ' ' . $value2;
			break;
	}
}

/// @endcond

?>