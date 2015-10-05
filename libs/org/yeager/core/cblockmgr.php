<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Gets an instance of the Cblock manager
 * @return object Cblock manager Object
 */
function sCblockMgr() {
	return Singleton::cbMgr();
}

/**
 * The CblockMgr class, which represents an instance of the Cblock manager.
 */
class CblockMgr extends \framework\Error {
	var $_db;
	var $_uid;

	var $db;
	var $baum;
	var $table;
	var $uid;

	var $properties;
	var $history;
	var $permissions;
	var $scheduler;

	/**
	 * Constructor of the CblockMgr class
	 */
	function __construct() {
		$this->_db = sYDB();
		$this->_uid = sUserMgr()->getCurrentUserID();

		$this->table = "yg_contentblocks_tree";
		$this->tree = new tree($this);
		$this->permissions = new Permissions($this->getPermissionsTable(), $this);
		$this->history = new History($this, HISTORYTYPE_CO, $this->permissions);
		$this->tags = new Tags($this);
		$this->properties = new PropertySettings("yg_contentblocks_props");
		$this->tags->_dobjectpropertytable = "yg_contentblocks_propsv";
		$this->scheduler = new Scheduler("yg_cron", SCHEDULER_CO);
		$this->control = new Entrymasks();
	}

/// @cond DEV

	/**
	 * Helper method for querying the database
	 *
	 * @param string $sql SQL query
	 * @return array|bool Result of SQL query or FALSE in case of an error
	 * @throws Exception
	 */
	function cacheExecuteGetArray($sql) {
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ':: ' . $sql);
		}
		$blaetter = $dbr->GetArray();
		return $blaetter;
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the object prefix, used for table names in database queries
	 *
	 * @return string
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

	/**
	 * Callback method which is executed when a Usergroup permission on a Cblock changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId Cblock Id
	 */
	public function onPermissionChange($usergroupId, $permission, $value, $objectId) {
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on a Cblock change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId Cblock Id
	 */
	public function onPermissionsChange($usergroupId, $permissions, $objectId) {
		return true;
	}

/// @cond DEV

	/**
	 * Gets the name of the database table containing the Cblock tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return $this->table;
	}

/// @endcond

/// @cond DEV

	/**
	 * Calls a specific Extension hook Callback method
	 *
	 * @param string $method
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

/// @endcond

	/**
	 * Gets additional information for Tree nodes
	 *
	 * @param int $cbId (optional) Id of the parent Cblock from which the list will be created
	 * @param array $objects Array of Tree nodes
	 * @return array Array of Tree nodes
	 */
	function getAdditionalTreeInfo($cbId, $objects) {
		$selectdefault = true;
		if ($cbId < 1) {
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
			if (($selectdefault == true) && ($mylevel > 1)) {
				$cbId = $currentid;
				$selectdefault = false;
			}
			$shadowmenue[$currentid]["LEVEL"] = $mylevel;
			$shadowmenue[$currentid]["PARENT"] = $myparent;
			if (($lastlevel + 1 == $mylevel)) {
				$parents[$mylevel] = $lastid;
			} else {
				$lastparent = $currentid;
			}
			$shadowmenue[$myparent]["CHILDREN"] += 1;
			$shadowmenue[$myparent]["LASTNODE"] = $currentid;
			if ($currentid == $cbId) {
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
			$objects[$i]["SHOW"] = $shadowmenue[$currentid]["SHOW"];
			$objects[$i]["CHILDREN"] = $shadowmenue[$currentid]["CHILDREN"];
			if ($shadowmenue[$preid]["LEVEL"] < $objects[$i]["LEVEL"]) {
				$objects[$i]["FIRST"] = 1;
			}
			if (($shadowmenue[$postid]["LEVEL"] < $objects[$i]["LEVEL"])) {
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
			if (($shadowmenue[$myparent]["SUBOPEN"] == 1)) {
				$objects[$i]["SHOW"] = 1;
			}
			if ($shadowmenue[$postid]["LEVEL"] > $objects[$i]["LEVEL"]) {
				$objects[$i]["HASSUBNODES"] = 1;
			} else {
				$objects[$i]["HASSUBNODES"] = 0;
			}
		}
		return ($objects);
	}

	/**
	 * Get Cblock tree nodes
	 *
	 * @param int $cbId (optional) From which Cblock Id the tree should be returned
	 * @param int $maxLevels (optional) Specifies the maximum level of nodes to get
	 * @param bool $onlyFolders (optional) FALSE when item from the Trash should also be returned
	 * @param int $skipNode (optional) Specifies a node to be skipped (should be used to skip the embedded Content Block folder)
	 * @param bool $noTrash (optional) FALSE when item from the Trash should also be returned
	 * @return array Array of Page nodes
	 */
	function getTree($cbId = NULL, $maxLevels = 2, $onlyFolders = false, $skipNode = NULL, $noTrash = true) {
		$maxLevels = (int)$maxLevels;

		if ($cbId > 0) {
			$currentLevel = $this->tree->getLevel($cbId);
		} else {
			$currentLevel = 1;
			$cbId = $this->tree->getRoot();
		}

		if ($noTrash) {
			$filterSQL_WHERE = " AND prop.DELETED = 0";
		}

		if ($skipNode) {
			$filterSQL_WHEREG .= " AND group2.PARENT != $skipNode AND group2.ID != $skipNode";
		}

		if ($onlyFolders) {
			$filterSQL_WHERE .= " AND prop.FOLDER = 1";
		}

		$maxLevelSQL = " AND (group2.LEVEL <= " . ($maxLevels + $currentLevel) . ") AND (group2.LEVEL <= " . ($maxLevels + $currentLevel) . ")";

		$myinfo = $this->tree->getAll($cbId);
		$subnodeSQL = " AND (group2.LFT >= " . $myinfo["LFT"] . ") AND (group2.RGT <= " . $myinfo["RGT"] . ")";

		// SQL for permissions
		$perm_SQL_SELECT = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RMODERATE) AS RMODERATE, MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_SQL_FROM = " LEFT JOIN " . $this->getPermissionsTable() . " AS perm ON perm.OID = group2.ID";

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
					group2.ID, group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT, group2.PNAME AS PNAME,
					prop.VERSION AS VERSION, prop.FOLDER AS FOLDER, prop.HASCHANGED AS HASCHANGED, prop.EMBEDDED AS EMBEDDED, pv.NAME as NAME
					$perm_SQL_SELECT
				FROM
					(yg_contentblocks_properties AS prop)
				LEFT JOIN $this->table AS group2 ON ((group2.ID = prop.OBJECTID) $maxLevelSQL $subnodeSQL $filterSQL_WHEREG)
				$perm_SQL_FROM
				LEFT JOIN yg_contentblocks_propsv AS pv ON pv.OID = prop.ID
				WHERE
					(prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID)))
					$perm_SQL_WHERE $maxLevelSQL $subnodeSQL $filterSQL_WHERE
					AND (RREAD > 0)
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID;";
		$tree = $this->cacheExecuteGetArray($sql);

		return $tree;
	}

	/**
	 * Gets a list of Cblocks
	 *
	 * @param int $cbId (optional) Id of the parent Cblock from which the list will be created
	 * @param array $filter (optional, may be combined) If SUBNODES, only subnodes of the specified Cblock will be returned<br>
	 *                                 if PSUBNODES, only subnodes including the specified Cblock will be returned<br>
	 *                                 if TRASHCAN, only items in the Trash will be returned<br>
	 *                                 if PUBLISHED, only live/published versions will be returned
	 * @param int $maxLevel (optional) Specifies the maximum level of nodes to get
	 * @param int $permissionsForRoleId (optional) If '1' then return all Usergroups and Permissions for this node
	 * @param array $filterArray Array of filters for the SQL query
	 * @return array|false Array of Cblocks or FALSE in case of an error
	 */
	function getList($cbId = 0, $filter = array(), $maxlevel = 0, $permissionsForRoleId = 0, $filterArray) {
		$cbId = (int)$cbId;
		$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");

		if ($cbId == 0) {
			$cbId = $this->tree->getRoot();
		}

		// Surpress items in trashcan if not explicitly asked for
		if (in_array("TRASHCAN", $filter)) {
			$filterSQL_WHERE = " AND prop.DELETED = 1 AND prop.FOLDER = 0";
		} else {
			$filterSQL_WHERE = " AND prop.DELETED = 0";
		}

		if (in_array("PUBLISHED", $filter)) {
			$filterSQL_WHERE .= " AND (
										(group2.VERSIONPUBLISHED = prop.VERSION) OR
										(
											(group2.VERSIONPUBLISHED = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND
											(prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
										)
									) ";
		} else {
			$filterSQL_WHERE .= " AND (prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID)))";
		}

		// Check if special filter was suppplied
		$filterOrder = 'order by group2.LFT';
		if ($filterArray) {
			$filterSelect = $filterFrom = $filterWhere = $filterLimit = $filterOrder = '';
			buildBackendFilter('CBlocksSearchCB', $filterArray, $filterSelect, $filterFrom, $filterWhere, $filterLimit, $filterOrder);
			$filterSQL_WHERE .= $filterWhere;
		}

		if ($maxlevel > 0) {
			$maxLevelSQL = " AND (group2.LEVEL <= $maxlevel) AND (group1.LEVEL <= $maxlevel)";
		}
		if (in_array("SUBNODES", $filter)) {
			$myinfo = $this->tree->getAll($cbId);
			$myleft = $myinfo["LFT"];
			$myright = $myinfo["RGT"];
			$subnodeSQL = " AND (group1.LFT > $myleft) AND (group1.RGT < $myright)";
			if (!$myinfo) {
				return false;
			}
		}
		if (in_array("PSUBNODES", $filter)) {
			$myinfo = $this->tree->getAll($cbId);
			$myleft = $myinfo["LFT"];
			$myright = $myinfo["RGT"];
			$subnodeSQL = " AND (group1.LFT >= $myleft) AND (group1.RGT =< $myright)";
			if (!$myinfo) {
				return false;
			}
		}

		// SQL for permissions
		$perm_SQL_SELECT = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RMODERATE) AS RMODERATE, MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_SQL_FROM = " LEFT JOIN yg_contentblocks_permissions AS perm ON perm.OID = group2.ID";

		if ($permissionsForRoleId > 0) {
			$perm_SQL_FROM .= " AND (perm.USERGROUPID = " . $permissionsForRoleId . ")";
		} else {
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
			$perm_SQL_WHERE .= " AND ((RREAD >= 1) OR (perm.USERGROUPID = $rootGroupId)) ";
		}

		$sql = "SELECT
					group2.LFT,
					group2.RGT,
					group2.VERSIONPUBLISHED,
					group2.ID, group2.LEVEL AS LEVEL,
					group2.PARENT AS PARENT,
					group2.PNAME AS PNAME,
					prop.VERSION AS VERSION,
					prop.FOLDER AS FOLDER,
					prop.HASCHANGED AS HASCHANGED,
					prop.EMBEDDED AS EMBEDDED,
					pv.*,
					prop.CREATEDTS,
					prop.CHANGEDTS,
					prop.CREATEDBY,
					prop.CHANGEDBY
					$perm_SQL_SELECT
				FROM
					($this->table AS group1, $this->table AS group2, yg_contentblocks_properties AS prop)
					$perm_SQL_FROM
				LEFT JOIN yg_contentblocks_propsv AS pv
					ON pv.OID = prop.ID
				WHERE
					((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
					(group2.ID = prop.OBJECTID)
					$perm_SQL_WHERE
					$maxLevelSQL
					$subnodeSQL
					$filterSQL_WHERE
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID
				$filterOrder $filterLimit;";

		$blaetter = $this->cacheExecuteGetArray($sql);

		return ($blaetter);
	}

	/**
	 * Gets the subnodes of the specified Cblock
	 *
	 * @param int $cbId Cblock Id of the parent Cblock
	 * @return array|false Array of subnodes or FALSE in case of an error
	 */
	function getSubnodes($cbId) {
		$cbId = (int)$cbId;
		return $this->tree->get($cbId);
	}

	/**
	 * Gets Cblocks by searchtext
	 *
	 * @param string $searchText Cblock Id of the parent Cblock
	 * @param string $filter (optional) Filter (If "TRASHCAN" only returns nodes in the trash)
	 * @return array|false Array of Cblocks or FALSE in case of an error
	 */
	function getCblocksByText($searchText, $filter = "") {
		$searchText = mysql_real_escape_string(sanitize($searchText));
		$sql = "DROP FUNCTION HTMLEncode;";
		$result = sYDB()->Execute($sql);

		$sql = "CREATE FUNCTION
					HTMLEncode(x text CHARACTER SET utf8) returns text CHARACTER SET utf8 DETERMINISTIC
				RETURN
					REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(x, 'Ä', '&Auml;'), 'Ö', '&Ouml;'), 'Ü', '&Uuml;'), 'ä', '&auml;'), 'ö', '&ouml;'), 'ü', '&uuml;'), 'ß', '&szlig;');";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		// surpress items in trashcan if not explicitly asked for
		if ($filter != "TRASHCAN") {
			$filterSQL_WHERE = " AND prop.DELETED = 0";
		} else {
			$filterSQL_WHERE = " AND prop.DELETED = 1";
		}

		$searchText = "+" . str_replace(" ", " +", $searchText);

		// sql for permissions
		$perm_SQL_SELECT = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE,  MAX(perm.RSTAGE) AS RSTAGE,  MAX(perm.RMODERATE) AS RMODERATE,  MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_SQL_FROM = " LEFT JOIN yg_contentblocks_permissions AS perm ON perm.OID = group2.ID";
		$perm_SQL_WHERE = " AND (";
		$roles = $this->permissions->getUsergroups();
		for ($r = 0; $r < count($roles); $r++) {
			$perm_SQL_WHERE .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
			if ((count($roles) - $r) > 1) {
				$perm_SQL_WHERE .= " OR ";
			}
		}
		$perm_SQL_WHERE .= ") ";

		$sql = "SELECT
		prop.*, group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT,
		prop.VERSION AS VERSION, prop.FOLDER AS FOLDER, prop.HASCHANGED AS HASCHANGED,
		MATCH (c.VALUE01, c.VALUE02, c.VALUE03, c.VALUE04, c.VALUE05, c.VALUE06, c.VALUE07, c.VALUE08) AGAINST ('$searchText' IN BOOLEAN MODE) AS SCORE, pv.*
		$perm_SQL_SELECT
		FROM
		($this->table AS group1, $this->table AS group2, yg_contentblocks_properties AS prop, yg_contentblocks_lnk_entrymasks AS lnk, yg_contentblocks_lnk_entrymasks_c AS c)
		$perm_SQL_FROM
		LEFT JOIN yg_contentblocks_propsv AS pv ON pv.OID = prop.ID
		WHERE
		((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
		(group2.ID = prop.OBJECTID) AND
		(prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
		$perm_SQL_WHERE $filterSQL_WHERE AND
		(lnk.CBVERSION = prop.VERSION) AND (lnk.CBID = prop.OBJECTID) AND (c.LNK = lnk.ID) AND
		(
		(MATCH (c.VALUE01, c.VALUE02, c.VALUE03, c.VALUE04, c.VALUE05, c.VALUE06, c.VALUE07, c.VALUE08) AGAINST ('$searchText' IN BOOLEAN MODE))
		OR
		(HTMLEncode(pv.NAME) LIKE REPLACE('%$searchText%','+',''))
		OR
		(HTMLEncode(pv.DESCRIPTION) LIKE REPLACE('%$searchText%','+',''))
		)
		GROUP BY
		group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID order by SCORE DESC;";

		// 20080818 - unrolled between
		//	 (group2.LFT BETWEEN group1.LFT AND group1.RGT) AND

		$blaetter = $this->cacheExecuteGetArray($sql);
		return ($blaetter);
	}

	/**
	 * Adds a new Cblock to the specified parent Cblock (folder)
	 *
	 * @param int $parentCbId Parent Cblock Id
	 * @param int $folder "0" for a Cblock or "1" if a folder should be created
	 * @param string $name (optional) Cblock name
	 * @return int|false New Cblock Id or FALSE in case of an error
	 * @throws Exception
	 */
	function add($parentCbId, $folder = 0, $name = "New object") {
		$parentCbId = (int)$parentCbId;
		$folder = (int)$folder;
		$name = mysql_real_escape_string($name);
		$rread = $this->permissions->checkInternal($this->_uid, $parentCbId, "RSUB");
		if ($rread) {
			// Create new node in Cblock Tree
			$cbId = $this->tree->add($parentCbId);

			// Create new version
			$ts = time();
			$sql = "INSERT INTO `yg_contentblocks_properties`
						(`OBJECTID`, `FOLDER`, `APPROVED`, `TEXT`, `VERSION`, `LOCKED`, `CREATEDTS`, `CHANGEDTS`, `CREATEDBY`, `CHANGEDBY`)
					VALUES
						('$cbId', '$folder', '$folder', '', '1', '0', '$ts', '$ts', '" . $this->_uid . "', '" . $this->_uid . "');";
			$result = sYDB()->Execute($sql);

			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			$this->permissions->copyTo($parentCbId, $cbId);

			$tmpCblock = $this->getCblock($cbId);
			$tmpCblock->properties->setValue("NAME", $name);
			$tmpCblock->publishVersion(ALWAYS_LATEST_APPROVED_VERSION);

			$tmpCblock = $this->getCblock($cbId);
			$tmpCblockInfo = $tmpCblock->get();
			$this->callExtensionHook("onAdd", $cbId, $tmpCblockInfo['VERSION']);

			// Add to history
			$tmpCblock->history->add(HISTORYTYPE_CO, NULL, 1, 'TXT_CBLOCK_H_NEWVERSION');

			// Return new Id
			return $cbId;
		} else {
			return false;
		}
	}

	/**
	 * Gets a specific Cblock instance
	 *
	 * @param int $cbId Cblock Id
	 * @param int $version (optional) Cblock version
	 * @return Cblock|false New instance of a Cblock or FALSE if an error has occured
	 */
	function getCblock($cbId, $version = 0) {
		$cbId = (int)$cbId;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			try {
				return new Cblock($cbId, $version);
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets an instance of a published Cblock
	 *
	 * @param int $cbId Cblock Id
	 * @return Cblock|false New instance of a Cblock or FALSE if an error has occured
	 */
	public function getPublishedCblock($cbId) {
		$tmpCblock = $this->getCblock($cbId);
		if ($tmpCblock) {
			$tmpCblockVersion = $tmpCblock->getPublishedVersion(true);
			return $this->getCblock($cbId, $tmpCblockVersion);
		} else {
			return false;
		}
	}

	/**
	 * Removes a Cblock from the Trash
	 *
	 * @param int $cbId Cblock Id
	 *
	 * @return array Array with all elements which were successfully removed
	 */
	function remove($cbId) {
		$cbId = $origCbId = (int)$cbId;
		$rootNode = $this->tree->getRoot();
		if ($cbId == $rootNode) {
			return array();
		}

		// Get folder for embedded cblocks
		$embeddedCblockFolder = (int)sConfig()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");

		// Get all nodes
		$hadError = false;
		$allNodes = $this->tree->get($cbId, 1000);
		foreach($allNodes as $allNodesItem) {
			$cbId = $allNodesItem['ID'];

			// Check if object is really in trash
			$cb = $this->getCblock($cbId);
			$cblockInfo = $cb->get();

			if ( ($cbId != $embeddedCblockFolder) &&
				 $cb->permissions->checkInternal($this->_uid, $cbId, "RDELETE") &&
				 $cblockInfo['DELETED'] ) {
				$lc = $cb->getEntrymasks();

				$sql = "DELETE FROM yg_contentblocks_properties WHERE OBJECTID = $cbId;";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}

				$s = new Sites();
				$sites = $s->getList();
				for ($i = 0; $i < count($sites); $i++) {
					$this->removeAllPageLinks($cbId, $sites[$i]["ID"]);
				}

				for ($i = 0; $i < count($lc); $i++) {
					$lnkid = $lc[$i]["LINKID"];
					$sql = "DELETE FROM yg_contentblocks_lnk_entrymasks WHERE ID = $lnkid;";
					$result = sYDB()->Execute($sql);
					if ($result === false) {
						throw new Exception(sYDB()->ErrorMsg());
					}
					$sql = "DELETE FROM yg_contentblocks_lnk_entrymasks_c WHERE LNK = $lnkid;";
					$result = sYDB()->Execute($sql);
					if ($result === false) {
						throw new Exception(sYDB()->ErrorMsg());
					}
				}

				$cb->history->clear();
				$cb->tags->clear();
				$cb->permissions->clear();

				$this->callExtensionHook("onRemove", $cbId, 0, $cblockInfo);
			} else {
				$hadError = true;
			}
		}
		if ($hadError) {
			return array();
		} else {
			$this->tree->remove($origCbId);
			return array($origCbId);
		}
	}

	/**
	 * Gets the parents of the specified Cblock
	 *
	 * @param int $cbId Cblock Id
	 * @return array Array of parent Cblocks
	 */
	function getParents($cbId) {
		$cbId = (int)$cbId;
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			$parentnodes = $this->tree->getParents($cbId, $this->tree->getRoot());
			$parentnodeidsql = implode(',', $parentnodes);
			if (strlen($parentnodeidsql) == 0) {
				$parentnodeidsql = 0;
			}
			$sql = "SELECT
			group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID AS ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT, prop.FOLDER AS FOLDER,
			MAX(prop.VERSION) AS VERSION, prop.LOCKED AS LOCKED, pv.*
			FROM
			($this->table AS group2, yg_contentblocks_properties AS prop)
			LEFT JOIN yg_contentblocks_propsv AS pv ON pv.OID = prop.ID
			WHERE
			(group2.ID = prop.OBJECTID) AND (group2.ID IN ($parentnodeidsql))
			GROUP BY
			group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID order by group2.LEVEL DESC;";
			$parentsO = $this->cacheExecuteGetArray($sql);

			//pepare weird array dimension
			$parents = array();
			for ($i = 0; $i < count($parentsO); $i++) {
				$parents[$i][] = $parentsO[$i];
			}

			return $parents;
		} else {
			return false;
		}
	}

	/**
	 * Gets Cblocks by Site and Page
	 *
	 * @param int $siteId Site Id
	 * @param int $pageId Page Id
	 * @return array|false Array of Cblock Ids or FALSE in case of an error
	 */
	function getByPage($siteId, $pageId) {
		$siteId = (int)$siteId;
		$pageId = (int)$pageId;
		$sql = "SELECT * FROM `yg_site_" . $siteId . "_lnk_cb` WHERE `PID` = $pageId;";
		$resulta = $this->cacheExecuteGetArray($sql);
		return $resulta;
	}

	/**
	 * Removes all Cblock-Page-Links for a specific Site
	 *
	 * @param int $cbId Cblock Id
	 * @param int $siteId Site Id
	 */
	function removeAllPageLinks($cbId, $siteId) {
		$cbId = (int)$cbId;
		$siteId = (int)$siteId;
		$sql = "DELETE FROM `yg_site_" . $siteId . "_lnk_cb` where CBID = '$cbId';";
		sYDB()->Execute($sql);
	}

	/**
	 * Gets a Cblock Entrymask Link by Link Id
	 *
	 * @param int $lnk Cblock Entrymask Link Id
	 * @return array|false Array of Cblock-Entrymask-Links or FALSE in case of an error
	 */
	function getEntrymaskLinkByEntrymaskLinkId($lnk) {
		$lnk = (int)$lnk;
		$sql = "SELECT
					coc.*
				FROM
					`yg_contentblocks_lnk_entrymasks_c` as coc, `yg_contentblocks_lnk_entrymasks` as lnk
				WHERE
					(coc.ID = $lnk) AND (coc.LNK = lnk.ID);";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra;
	}

	/**
	 * Gets a Cblock Link by Link Id
	 *
	 * @param int $lnk Cblock Entrymask Link Id
	 */
	function getCblockLinkByLinkId($lnk) {
		$lnk = (int)$lnk;
		$sql = "SELECT
					ID,
					ENTRYMASK,
					CBID AS CBLOCKID,
					CBVERSION AS CBLOCKVERSION,
					ORDERPROD
				FROM
					`yg_contentblocks_lnk_entrymasks` as lnk
				WHERE
					(lnk.ID = $lnk);";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra;
	}

/// @cond DEV

	/**
	 * Gets Cblocks by filtering by Entrymask content
	 *
	 * @param bool $cbFinal If true, returns only final Entrymasks
	 * @param array $filterArray Array of filters for the SQL query
	 * @param array $limitArray Array of query-limits for the SQL query
	 * @param bool $count If TRUE, only returns the count of matches
	 * @return array|false Array of Cblocks or FALSE in case of an error
	 */
	function filterEntrymasks($cbFinal = true, $filterArray = array(), $limitArray = array(), $count = false) {
		$foldersql = "";
		if (($limitArray["FOLDER"] > 0)) {
			$foldersql = "AND (tree.PARENT = " . (int)$limitArray["FOLDER"] . ")";
		}

		$coidsql = "";
		if (($limitArray["CBID"] > 0)) {
			$coidsql = "AND (tree.ID = " . (int)$limitArray["CBID"] . ")";
		}

		if (($limitArray["DELETED"] > 0)) {
			$deletedsql = "AND (co_p.DELETED = 1)";
			$deletedsqlf = "AND (props.DELETED = 1)";
		} else {
			$deletedsql = "AND (co_p.DELETED = 0)";
			$deletedsqlf = "AND (props.DELETED = 0)";
		}

		if ($cbFinal == true) {
			$versionsql = " (tree.VERSIONPUBLISHED = l.CBVERSION OR ((tree.VERSIONPUBLISHED = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND (l.CBVERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (tree.ID = rgt.OBJECTID) AND (rgt.APPROVED = 1))))) ";
		} else {
			$versionsql = " (l.CBVERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (tree.ID = rgt.OBJECTID))) ";
		}

		$perm_SQL_SELECT = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RMODERATE) AS RMODERATE, MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_SQL_FROM = " LEFT JOIN yg_contentblocks_permissions AS perm ON perm.OID = tree.ID";
		$perm_SQL_WHERE = " AND (";
		$roles = $this->permissions->getUsergroups();
		for ($r = 0; $r < count($roles); $r++) {
			$perm_SQL_WHERE .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
			if ((count($roles) - $r) > 1) {
				$perm_SQL_WHERE .= " OR ";
			}
		}
		$perm_SQL_WHERE .= ") ";

		foreach ($filterArray as $filterEntry) {
			$sql_em_where = array();
			$sql_formfields_where = array();
			$fe = 0;
			$filterEntries = array();
			while (list($filterEntryKey, $filterEntryValue) = each($filterEntry)) {
				if ($filterEntryKey == "ENTRYMASKIDENTIFIER") {
					$filterEntries[$fe]["SQL"] = "(p.CODE = '" . $filterEntryValue . "')";
				} else {
					if ($filterEntryKey == "COMB") {
						if ($filterEntryValue == "OR") {
							$filterEntries[$fe]["COMB"] = "OR";
						}
					} else {
						if ($filterEntryKey == "VALUE") {
							for ($wi = 0; $wi < count($filterEntryValue); $wi++) {
								$formfieldValueFilter = $filterEntryValue[$wi];
								$v = -1;
								foreach ($formfieldValueFilter as $filterFormfieldKey => $filterFormfieldValue) {
									if (preg_match("/^ALIAS/", $filterFormfieldKey) || preg_match("/^IDENTIFIER/", $filterFormfieldKey)) {
										$v++;
										$formfieldsql = "(_w_.$filterFormfieldKey = '" . $filterFormfieldValue . "')";
										$filterEntries[$fe]["FILTER"]["FORMFIELD"][$v]["SQL"] = $formfieldsql;
									}
									if ($filterFormfieldKey == "FILTER") {
										for ($wf = 0; $wf < count($filterFormfieldValue); $wf++) {
											$wFilter = $filterFormfieldValue[$wf];
											$param = $wFilter["PARAM"];
											$op = $wFilter["OP"];
											$value = $wFilter["VALUE"];
											if (is_numeric($value)) {
												$valuesql = $value;
											} else {
												$valuesql = "'" . $value . "'";
											}
											$valuesql = "_wc_.$param $op $valuesql";
											$filterEntries[$fe]["FILTER"]["FORMFIELD"][$v]["VALUES"][] = $valuesql;
										}
									}
								}
							}
							$fe++;
						}
					}
				}
			}
			$comb = "";
			$w = 0;
			foreach ($filterEntries as $fe) {
				$w++;
				$comb_select .= ", w" . $w . ".ENTRYMASK AS W$w ";
				$emjoin .= " LEFT JOIN yg_entrymasks_lnk_formfields as w$w ON (" . $fe["SQL"] . " AND w$w.ENTRYMASK = p.OBJECTID) \n";
				$cop = "AND";
				$comb_where .= " AND (w" . $w . ".ENTRYMASK > 1) ";
				$comb_have .= " ((W$w > 1)  ";
				$c = 0;
				if (count($fe["FILTER"]["FORMFIELD"]) > 0) {
					foreach ($fe["FILTER"]["FORMFIELD"] as $wes) {
						$c++;
						$sqlsnippet = str_replace("_w_", "w" . $w, $wes["SQL"]);
						$emjoin .= " LEFT JOIN yg_contentblocks_lnk_entrymasks_c as w" . $w . "c" . $c . " ON (w" . $w . "c" . $c . ".LNK = l.ID AND " . $sqlsnippet . " AND w" . $w . "c" . $c . ".ENTRYMASKFORMFIELD = w$w.ID  ";
						//$comb_select .= ", SUM(w".$w."c".$c.".LNK) AS W".$w."C".$c." ";
						$comb_have .= " AND W" . $w . "C" . $c . " > 1 ";
						$comb_where .= " AND (w" . $w . "c" . $c . ".LNK > 1)";
						if (count($wes["VALUES"])) {
							foreach ($wes["VALUES"] as $sqlsnippet) {
								$sqlsnippet = str_replace("_w_", "w" . $w, $sqlsnippet);
								$sqlsnippet = str_replace("_wc_", "w" . $w . "c" . $c, $sqlsnippet);
								$emjoin .= " AND (" . $sqlsnippet . ")";
							}
							$emjoin .= " ) \n";
						} else {
							$emjoin .= " ) \n";
						}
					}
				}
				$comb_have .= " ) ";
			}

			/* query each filter entry */
			$sql = "SELECT
			l.ID AS LINKID, l.CBID, l.CBVERSION, tree.VERSIONPUBLISHED, p.FOLDER $comb_select $perm_SQL_SELECT
			FROM (yg_entrymasks_properties AS p, yg_contentblocks_tree as tree)
			$perm_SQL_FROM
			LEFT JOIN yg_contentblocks_lnk_entrymasks AS l on ((l.ENTRYMASK = p.OBJECTID) AND (l.CBID = tree.ID))
			$emjoin
			JOIN yg_contentblocks_properties AS co_p ON (tree.ID = co_p.OBJECTID AND l.CBID = co_p.OBJECTID)
			WHERE
			$versionsql
			AND
			(perm.RREAD > 0) $comb_where
			$foldersql $coidsql $deletedsql $perm_SQL_WHERE
			GROUP BY l.ID";

			$emjoin = "";
			$comb_where = "";
			$ra = $this->cacheExecuteGetArray($sql);
			if (count($ra) > 0) {
				$matches[] = $ra;
			} else {
				$matches[] = array(array("LINKID" => -1, "CBID" => -1, "CBVERSION" => -1));
			}
		}

		if (count($filterArray) > 0) {
			$linkIds = array();
			$linkIds_sql = " AND ";
			$coids_sql = " AND ";
			for ($r = 0; $r < count($matches); $r++) {
				$linkset = $matches[$r];
				$linkcohash = array();
				$linkIds_sql .= "lnk.ID IN (";
				$coids_sql .= "(";
				for ($l = 0; $l < count($linkset); $l++) {
					$linkIds[$linkset[$l]["CBID"]][] = $linkset[$l]["LINKID"];
					$linkIds_sql .= $linkset[$l]["LINKID"];
					if ($linkset[$l]["CBID"] < 1) {
						$coids_sql .= "(lnk.CBID = -1 AND lnk.CBVERSION = -1) ";
					} else {
						$coids_sql .= "(lnk.CBID = " . $linkset[$l]["CBID"] . " AND lnk.CBVERSION = " . $linkset[$l]["CBVERSION"] . ") ";
					}
					if ($l != count($linkset) - 1) {
						$linkIds_sql .= ",";
					}
					if ($l != count($linkset) - 1) {
						$coids_sql .= " OR ";
					}
				}
				$linkIds_sql .= ")";
				$coids_sql .= ")";
				if ($r <> count($matches) - 1) {
					if ($filterArray[0]["COMB"] == "OR") {
						$linkIds_sql .= " OR ";
						$coids_sql .= " OR ";
					} else {
						$linkIds_sql .= " AND ";
						$coids_sql .= " OR ";
					}
				}
			}
		}

		$limitsql = "";
		if (($limitArray["FROM"] > 0) && ($limitArray["COUNT"] > 0)) {
			$limitsql = "LIMIT " . (int)$limitArray["FROM"] . ", " . (int)$limitArray["COUNT"];
		}

		if ($count == true) {
			$limitsql = "";
			$countsql = " AND props.FOLDER = 0 ";
		}

		if ($cbFinal == true) {
			$versionsql = " AND (tree.VERSIONPUBLISHED = lnk.CBVERSION OR ((tree.VERSIONPUBLISHED = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND (lnk.CBVERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (tree.ID = rgt.OBJECTID) AND (rgt.APPROVED = 1))))) ";
		} else {
			$versionsql = " AND (props.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_contentblocks_properties AS rgt WHERE (tree.ID = rgt.OBJECTID))) ";
		}

		$perm_SQL_SELECT = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RMODERATE) AS RMODERATE, MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_SQL_FROM = " LEFT JOIN yg_contentblocks_permissions AS perm ON perm.OID = tree.ID";
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

		if (count($filterArray) == 0) {
			$comb_have = "l.CBID > 1";
		}

		$sql = "SELECT
		props.OBJECTID AS CBID, props.VERSION AS CBVERSION, lnk.ID AS LINKID, lnk.ENTRYMASK AS ENTRYMASKID, props.CREATEDBY, props.FOLDER, props.CHANGEDBY, props.HASCHANGED, props.CREATEDTS, props.CHANGEDTS, propsv.* $perm_SQL_SELECT
		FROM
		(`yg_contentblocks_properties` as props,
		`yg_contentblocks_propsv` as propsv,
		`yg_contentblocks_tree` as tree)
		LEFT JOIN `yg_contentblocks_lnk_entrymasks` as lnk ON ((lnk.CBID = props.OBJECTID AND lnk.CBVERSION = props.VERSION))
		$perm_SQL_FROM
		WHERE
		(props.OBJECTID = tree.ID) AND
		(props.ID = propsv.OID)
		$coids_sql $coidsql $linkIds_sql $foldersql $deletedsqlf $versionsql $countsql
		$perm_SQL_WHERE
		GROUP BY props.OBJECTID
		ORDER BY ORDERPROD
		$limitsql ";

		$ra = $this->cacheExecuteGetArray($sql);
		if ($count == true) {
			return count($ra);
		}
		for ($c = 0; $c < count($ra); $c++) {
			$ra[$c]["LNKMATCHES"] = $linkIds[$ra[$c]["CBID"]];
			$cb = $this->getCblock($ra[$c]["CBID"], $ra[$c]["CBVERSION"]);
			$ra[$c]["ENTRYMASKS"] = $cb->getEntrymasks();
		}
		return $ra;
	}

/// @endcond

	/**
	 * Gets a Cblock Entrymask Link by Entrymask Link Id
	 *
	 * @param int $lnk Cblock Entrymask Link Id
	 * @return array|false Cblock Entrymask Link or FALSE in case of an error
	 */
	function getCblockLinkByEntrymaskLinkId($lnk) {
		$lnk = (int)$lnk;
		$sql = "SELECT
					lnk.ID,
					lnk.ENTRYMASK,
					lnk.CBID AS CBLOCKID,
					lnk.CBVERSION AS CBLOCKVERSION,
					lnk.ORDERPROD
				FROM
					`yg_contentblocks_lnk_entrymasks_c` as coc,
					`yg_contentblocks_lnk_entrymasks` as lnk
				WHERE
					(coc.ID = $lnk) AND
					(coc.LNK = lnk.ID);";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra;
	}

	/**
	 * Gets a Cblock Entrymask Link by Entrymask Id
	 *
	 * @param int $lnk Cblock Entrymask Link Id
	 * @return array|false Cblock Entrymask Link or FALSE in case of an error
	 */
	function getCblockLinkByEntrymaskId($entrymaskid, $noTrash = true) {
		$entrymaskid = (int)$entrymaskid;

		if ($noTrash) {
			$deleted_sql = 0;
		}

		$sql = "SELECT
					coc.CBID AS CBLOCKID,
					coc.CBVERSION AS CBLOCKVERSION,
					tree.PARENT
				FROM
					`yg_contentblocks_lnk_entrymasks` AS coc
				JOIN
					`yg_contentblocks_tree` AS tree ON coc.CBID = tree.ID
				JOIN
					`yg_contentblocks_properties` AS props ON coc.CBID = props.OBJECTID
				WHERE
					(coc.ENTRYMASK = $entrymaskid) AND
					((coc.CBVERSION = tree.VERSIONPUBLISHED) OR (tree.VERSIONPUBLISHED = " . ALWAYS_LATEST_APPROVED_VERSION . ")) AND
					(props.DELETED = $deleted_sql)
				GROUP BY
					coc.CBID;";

		$ra = $this->cacheExecuteGetArray($sql);
		return $ra;
	}

	/**
	 * Gets a Cblock Id by permanent name
	 *
	 * @param string $pname Cblock permanent name
	 * @return int|false Cblock Id or FALSE in case of an error
	 */
	function getCblockIdByPName($pname) {
		$pname = mysql_real_escape_string(sanitize($pname));
		$sql = "SELECT t.ID AS ID FROM yg_contentblocks_tree as t JOIN yg_contentblocks_properties AS p ON (t.ID = p.OBJECTID) WHERE (t.PNAME = '$pname') AND (p.DELETED = 0);";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra[0]['ID'];
	}

	/**
	 * Gets permanent name by Cblock Id
	 *
	 * @param int $cbId Cblock Id
	 * @return string Permanent name
	 */
	function getPNameByCblockId($cbId) {
		$cbId = mysql_real_escape_string(sanitize((int)$cbId));
		if ($this->permissions->checkInternal($this->_uid, $cbId, "RREAD")) {
			$sql = "SELECT PNAME FROM yg_contentblocks_tree as t WHERE (t.ID = $cbId);";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra[0]['PNAME'];
		}
		return false;
	}

	/**
	 * Gets Locks for the specific Token
	 *
	 * @param string $token Lock Token
	 * @return array Array of Cblock locks
	 * @throws Exception
	 */
	public function getLocksByToken($token) {
		$token = mysql_real_escape_string($token);
		if ($token == "") {
			return false;
		}
		$sql = "SELECT OBJECTID, LOCKED, TOKEN FROM yg_contentblocks_properties WHERE TOKEN = '" . $token . "';";
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
		}
		$ra = $dbr->GetArray();
		return $ra;
	}

}

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
function CBlocksSearchCB(&$list, $type, $operator, $value1 = 0, $value2 = 0) {
	$op = GetContainsOperators($operator);
	switch ($type) {
		case "CREATEDTS":
			if (0 < $value1) {
				$list["WHERE"][] = "prop.CREATEDTS " . $op . " " . (int)$value1;
			}
			break;

		case "CHANGEDTS":
			if (0 < $value1) {
				$list["WHERE"][] = "prop.CHANGEDTS " . $op . " " . (int)$value1;
			}
			break;

		case "LIMITER":
			if ((int)$value2 > 0) {
				$list["LIMIT"][] = "LIMIT " . (int)$value1 . "," . (int)$value2;
			}
			break;

		case "ORDER":
			$list["ORDER"][] = "ORDER BY " . $value1 . " " . $value2;
			break;
	}
}

/// @endcond

?>