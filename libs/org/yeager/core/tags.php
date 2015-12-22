<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Gets an instance of the Tag manager
 * @return object Tag manager object
 */
function sTags() {
	return Singleton::Tags();
}

/**
 * The Tags class, which represents the Tag manager.
 */
class Tags extends \framework\Error {
	var $_db;
	var $_object;
	var $_objectprefix;
	var $_objectidentifier;
	var $_uid;
	var $permissions;

	/**
	 * Constructor of the Tag class
	 *
	 * @param object $object Object from which the Tag class was instantiated
	 */
	function __construct(&$object = NULL) {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->table = "yg_tags_tree";
		$this->tree = new tree($this);
		$this->permissions = new Permissions("yg_tags_permissions", $this);
		$this->_object = &$object;
		$this->fetchIdentifiers();
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
	 * Gets the name of the database table containing the Tags tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return $this->table;
	}

/// @endcond

	/**
	 * Callback method which is executed when a Usergroup permissions on a Tag changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId Tag Id
	 */
	public function onPermissionChange($usergroupId, $permission, $value, $objectId) {
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on a Tag change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param int $objectId Tag Id
	 */
	public function onPermissionsChange($usergroupId, $permissions, $objectId) {
		return true;
	}

/// @cond DEV

	/**
	 * Fetches the identifiers from the object where the Tags class is attached to
	 * and maps them into private members
	 */
	function fetchIdentifiers() {
		if (is_object($this->_object)) {
			$this->_objectprefix = @$this->_object->getObjectPrefix();
			$this->_objectidentifier = @$this->_object->getAdditionalIdentifier();
			$this->_objectpropertytable = @$this->_object->getPropertyTable();
			$this->_objecttreetable = @$this->_object->getTreeTable();
		}
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets an array of all identifier names
	 *
	 * @param string $oidname Object Id name
	 * @param string $vname Value name
	 * @return array Array of identifier names
	 */
	function getIdentifierNameArray($oidname, $vname) {
		$namelist[] = $oidname;
		$namelist[] = $vname;
		if (count($this->_objectidentifier) > 0) {
			$namelistx = array_merge($namelist, $this->_objectidentifier);
		} else {
			$namelistx = $namelist;
		}
		return $namelistx;
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets an array of all identifier values
	 *
	 * @param string $oidvalue Object id value
	 * @param int $oversion Object version
	 * @return array Array of identifier values
	 */
	function getIdentifierValueArray($oidvalue, $oversion) {
		$vararray[] = $oidvalue;
		$vararray[] = $oversion;
		$customidentifier = @implode(",", $this->_objectidentifier);
		if (strlen($customidentifier) > 0) {
			$vararray[] = @$this->_object->getAdditionalIdentifierValue();
		}
		return $vararray;
	}

/// @endcond

	/**
	 * Gets the parents of the specified Tag
	 *
	 * @param int $tagId Tag Id
	 * @return array Array of parent Tags
	 */
	function getParents($tagId) {
		$tagId = (int)$tagId;
		if ($this->permissions->checkInternal($this->_uid, $tagId, "RREAD")) {
			$parentId = $this->tree->getParent($tagId);
			$i = 0;
			while ($parentId > 0) {
				$sql = "SELECT
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID AS ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT
					FROM
					($this->table AS group2, yg_tags_properties AS prop)
					WHERE
					(group2.ID = prop.OBJECTID) AND (group2.ID = ?)
					GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID order by group2.LFT;";
				$dbr = sYDB()->Execute($sql, $parentId);
				$parents[$i] = $dbr->GetArray();
				$coid = $parents[$i][0]["ID"];
				$parentId = $this->tree->getParent($coid);
				$i++;
			}
			foreach ($parents as $parent_idx => $parent_item) {
				$tagInfo = $this->get($parent_item[0]['ID']);
				$parents[$parent_idx][0]['NAME'] = $tagInfo['NAME'];
			}
			return $parents;
		} else {
			return false;
		}
	}

	/**
	 * Gets basic information about the specified Tag
	 *
	 * @param int $tagId Tag Id
	 * @return array|false Array containing information about the Tag or FALSE in case of an error
	 */
	function get($tagId) {
		$tagId = (int)$tagId;
		if ($this->permissions->checkInternal($this->_uid, $tagId, "RREAD")) {
			$sql = "SELECT
						ID,
						OBJECTID,
						NAME
					FROM
						yg_tags_properties WHERE OBJECTID = ?;";
			$ra = $this->cacheExecuteGetArray($sql, $tagId);
			return $ra[0];
		} else {
			return false;
		}
	}

	/**
	 * Gets Tags by name
	 *
	 * @param string $name Tag name
	 * @return array Array with information about the Tags or FALSE in case of an error
	 */
	function getByName ($name) {
		$name = sYDB()->escape_string(sanitize($name));
		$sql = "SELECT
					prop.*,
					tree.PARENT AS PARENT
				FROM
					yg_tags_properties AS prop,
					yg_tags_tree AS tree
				WHERE
					(prop.NAME = ?) AND
					(prop.OBJECTID = tree.ID);";
		$ra = $this->cacheExecuteGetArray($sql, $name);
		if (count($ra)) {
			return $ra;
		}
		return false;
	}

	/**
	 * Checks if the Tag is assigned to a specific version of an Object
	 *
	 * @param int $tagId Tag Id
	 * @return bool TRUE if the Tag is assigned, FALSE if not
	 */
	function isAssigned($tagId) {
		$tagId = (int)$tagId;
		$oid = (int)$this->_object->getID();
		$version = (int)$this->_object->getVersion();
		$varlist = $this->getIdentifierValueArray($oid, $version);
		$ids = $this->getIdentifierNameArray("OID", "OVERSION");
		$sqlargs = array();
		array_push($sqlargs, $oid, $tagId);
		$sql = "SELECT
			object.*, object.OBJECTID AS ID, cat.OBJECTID AS TAGID, objecttree.LFT AS OBJECTORDER
			FROM
			" . $this->_objectpropertytable . " AS object, `yg_tags_lnk_" . $this->_objectprefix . "` AS lnk, yg_tags_properties AS cat, " . $this->_objecttreetable . " AS objecttree
			WHERE
			(object.OBJECTID = lnk.OID) AND (object.OBJECTID = ?) AND (lnk.TAGID = cat.OBJECTID) AND
			(cat.OBJECTID = ?) AND ";

		for ($i = 0; $i < count($ids); $i++) {
			$sql .= "( ";
			$sql .= "lnk." . $ids[$i] . " = ?";
			$sql .= ") AND ";
			array_push($sqlargs, $varlist[$i]);
		}
		$sql .= "1 GROUP BY OBJECTID;";

		array_unshift($sqlargs, $sql);
		$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
		}
		$ra = $dbr->GetArray();
		if (count($ra) > 0) {
			return true;
		}
		return false;
	}

	/**
	 * Assigns a Tag to a specific version of this Object
	 *
	 * @param int $tagId Tag Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function assign($tagId) {
		$tagId = (int)$tagId;
		$oid = $this->_object->getID();
		$version = $this->_object->getVersion();
		if ($this->isAssigned($tagId)) {
			return false;
		}

		if ($this->_object->permissions->checkInternal($this->_uid, $oid, 'RWRITE')) {
			$ids = $this->getIdentifierNameArray("OID", "OVERSION");
			$varlist = $this->getIdentifierValueArray($oid, $version);
			$sqlargs = array();
			$sql = "SELECT g.OBJECTID AS ID, g.NAME AS NAME, lnk.ORDERPROD AS ORDERPROD FROM
			`yg_tags_lnk_" . $this->_objectprefix . "` as lnk,
			yg_tags_properties as g
			WHERE ";
			for ($i = 0; $i < count($ids); $i++) {
				$id = sYDB()->escape_string($ids[$i]);
				$value = sYDB()->escape_string($varlist[$i]);
				$sql .= "( ";
				$sql .= "lnk." . $id . " = ?";
				$sql .= ") AND ";
				array_push($sqlargs, $value);
			}
			$sql .= "(lnk.TAGID = g.OBJECTID) ORDER BY ORDERPROD;";
			array_unshift($sqlargs, $sql);
			$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$tags = $dbr->GetArray();
			for ($t = 0; $t < count($tags); $t++) {
				$this->setOrder($tags[$t]["ID"], $t);
			}

			$sqlargs = array();
			$sql = "INSERT INTO `yg_tags_lnk_" . $this->_objectprefix . "` (";
			for ($i = 0; $i < count($ids); $i++) {
				$id = sYDB()->escape_string(sanitize($ids[$i]));
				$sql .= "`". $id . "`";
				if ($i < count($ids) - 1) {
					$sql .= ",";
				}
			}
			$sql .= ", TAGID) VALUES (";
			for ($i = 0; $i < count($ids); $i++) {
				$value = sYDB()->escape_string($varlist[$i]);
				array_push($sqlargs, $value);
				$sql .= "?";
				if ($i < count($ids) - 1) {
					$sql .= ",";
				}
			}
			$sql .= ", ?);";
			array_push($sqlargs, $tagId);
			array_unshift($sqlargs, $sql);
			$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
			if ($dbr === false) {
				echo $this->_errormsg . "<br>";
				return false;
			}
			$this->_object->markAsChanged();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes all Tag assignments from all versions of the instanced Object
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function clear() {
		$oid = $this->_object->getID();
		$ids = $this->getIdentifierNameArray("OID", "OVERSION");
		$varlist = $this->getIdentifierValueArray($oid, "-1");

		if ($this->_object->permissions->checkInternal($this->_uid, $oid, 'RWRITE')) {
			$sqlargs = array();
			$sql = "DELETE FROM `yg_tags_lnk_" . $this->_objectprefix . "` WHERE ";
			for ($i = 0; $i < count($ids); $i++) {
				$sql .= "( ";
				$value = sYDB()->escape_string($varlist[$i]);
				$id = sYDB()->escape_string(sanitize($ids[$i]));

				array_push($sqlargs, $value);

				if ($ids[$i] != "OVERSION") {
					$sql .= "`" . $id . "` = ?";
				} else {
					$sql .= "`" . $id . "` > ?";
				}
				$sql .= ") AND ";
			}
			$sql .= " 1;";
			array_unshift($sqlargs, $sql);
			$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes a Tag from a specific version of an Object
	 *
	 * @param int $tagId
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function unassign($tagId) {
		$tagId = (int)$tagId;
		$oid = $this->_object->getID();
		$version = $this->_object->getVersion();

		if ($this->_object->permissions->checkInternal($this->_uid, $oid, 'RWRITE')) {
			$ids = $this->getIdentifierNameArray("OID", "OVERSION");
			$varlist = $this->getIdentifierValueArray($oid, $version);
			$sqlargs = array();
			$sql = "DELETE FROM `yg_tags_lnk_" . $this->_objectprefix . "` WHERE ";
			for ($i = 0; $i < count($ids); $i++) {
				$value = sYDB()->escape_string($varlist[$i]);
				$id = sYDB()->escape_string(sanitize($ids[$i]));
				$sql .= "( ";
				$sql .= "`" . $id . "` = ?";
				$sql .= ") AND ";
				array_push($sqlargs, $value);
			}
			$sql .= " (TAGID = $tagId);";

			array_unshift($sqlargs, $sql);
			$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->_object->markAsChanged();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Copies the assignment of Tags from an Object to a specific version of another Object
	 *
	 * @param int $sourceObjectId Source Object Id
	 * @param int $sourceVersion Source Object version
	 * @param int $targetObjectId Target Object Id
	 * @param int $targetVersion Target Object version
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function copyTo($sourceObjectId, $sourceVersion, $targetObjectId, $targetVersion) {
		$sourceObjectId = (int)$sourceObjectId;
		$sourceVersion = (int)$sourceVersion;
		$targetVersion = (int)$targetVersion;
		$targetObjectId = (int)$targetObjectId;
		$ids = $this->getIdentifierNameArray("OID", "OVERSION");
		$varlist = $this->getIdentifierValueArray($sourceObjectId, $sourceVersion);
		$customidentifier = @implode(",", $this->_objectidentifier);
		if (strlen($customidentifier) > 0) {
			$customidentifier = "," . $customidentifier;
		}
		$sql = "DELETE FROM `yg_tags_lnk_" . $this->_objectprefix . "` WHERE (OID = ?) AND (OVERSION = ?);";
		sYDB()->Execute($sql, $targetObjectId, $targetVersion);

		$sqlargs = array();
		$sql = "INSERT INTO `yg_tags_lnk_" . $this->_objectprefix . "`
			(OVERSION, OID, TAGID, ORDERPROD $customidentifier)
			SELECT $targetVersion, $targetObjectId, TAGID, ORDERPROD $customidentifier
			FROM `yg_tags_lnk_" . $this->_objectprefix . "` WHERE ";
		for ($i = 0; $i < count($ids); $i++) {
			$value = sYDB()->escape_string($varlist[$i]);
			$id = sYDB()->escape_string(sanitize($ids[$i]));
			$sql .= "( ";
			$sql .= "`" . $id . "` =  ?";
			$sql .= ") AND ";
			array_push($sqlargs, $value);
		}
		$sql .= " 1;";
		array_unshift($sqlargs, $sql);
		$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Gets a list of Objects which got the specified Tags assigned
	 *
	 * @param int|array $list List of Tag Ids or single Tag Id
	 * @param string $sort (optional) Specifies the order column and direction of the SQL query (ID, OBJECTORDER, NAME) and (DESC / ASC)
	 * @param string $concat (optional) Specifies which operator should be used in the SQL query (default is OR)
	 * @param bool $published (optional) Specifies if only published versions should be returned
	 * @param $filterArray Array of filters used in the SQL query
	 * @param bool $noTrash TRUE when Objects in the trash shouldn't be returned
	 * @return array|false
	 */
	function getByTag($list, $sort, $concat = "OR", $published = false, $filterArray, $noTrash = true) {
		if ($concat != "OR" AND $concat != "AND") {
			$concat = "OR";
		}
		$sortdir = "ASC";
		$sortcol = "OBJECTORDER";

		$sortarr = explode(" ", $sort);
		if ($sortarr[count($sortarr)-1] == "DESC") {
			$sortdir = "DESC";
		}

		if (in_array($sortarr[0], array("ID", "OBJECTORDER", "NAME", "PNAME"))) {
			$sortcol = $sortarr[0];
		}

		if ($noTrash) {
			$noTrashSQL = "(object.DELETED = 0) AND ";
		}
		if (!is_array($list)) {
			$id = (int)$list;
			if ($id < 1) {
				return;
			}
			if (!$this->permissions->checkInternal($this->_uid, $id, "RREAD")) {
				return;
			}
			$sqls = "(lnk.TAGID = $id) ";
		} else {
			if ($concat == 'OR') {
				for ($i = 0; $i < count($list); $i++) {
					if ($this->permissions->checkInternal($this->_uid, (int)$list[$i], "RREAD")) {
						if ($sqls != '') {
							$sqls .= " OR ";
						}
						$sqls .= " (lnk.TAGID = " . (int)$list[$i] . ") ";
					}
				}
			} elseif ($concat == 'AND') {
				$tagIds = array();
				for ($i = 0; $i < count($list); $i++) {
					if ($this->permissions->checkInternal($this->_uid, (int)$list[$i], "RREAD")) {
						$tagIds[] = (int)$list[$i];
					} else {
						return false;
					}
				}
				foreach ($tagIds as $tagId) {
					if ($sqls != '') {
						$sqls .= " AND ";
					}
					$sqls .= " ((SELECT COUNT(*) FROM `yg_tags_lnk_" . $this->_objectprefix . "` AS SUBQ WHERE SUBQ.TAGID = " . $tagId . " AND OID = object.OBJECTID AND SUBQ.OVERSION = (SELECT MAX( rgt2.VERSION ) FROM " . $this->_objectpropertytable . " AS rgt2 WHERE (object.OBJECTID = rgt2.OBJECTID)))) > 0 ";
				}
				$sqls = " (lnk.TAGID IN (" . implode(', ', $tagIds) . ")) AND " . $sqls;
			}
		}

		if ($published === true) {
			$sql_final_w = " ((objecttree.VERSIONPUBLISHED = lnk.OVERSION) OR
			(
				(objecttree.VERSIONPUBLISHED = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND (object.APPROVED = 1) AND
				(
					(lnk.OVERSION = (SELECT MAX( rgt.VERSION ) FROM " . $this->_objectpropertytable . " AS rgt WHERE (object.OBJECTID = rgt.OBJECTID))) OR
					(lnk.OVERSION = (SELECT MAX( rgt.VERSION -1) FROM " . $this->_objectpropertytable . " AS rgt WHERE (object.OBJECTID = rgt.OBJECTID)))
				)
			))";
		} else {
			$sql_final_w = " (lnk.OVERSION = (SELECT MAX( rgt.VERSION ) FROM " . $this->_objectpropertytable . " AS rgt WHERE (object.OBJECTID = rgt.OBJECTID)))";
		}

		if ($filterArray) {
			$filterSelect = $filterFrom = $filterWhere = $filterLimit = $filterOrder = '';
			buildBackendFilter('TagGetByTagCB', $filterArray, $filterSelect, $filterFrom, $filterWhere, $filterLimit, $filterOrder);
			$sql_final_w .= $filterWhere;
		}

		if ($this->_objectprefix == "pages") {
			$sqlsite = " (lnk.SITEID = " . $this->_object->_site . ") AND ";
		}

		if ($sqls == '') {
			return;
		}

		$sql = "SELECT
			object.*, $dynpropw object.OBJECTID AS ID, cat.OBJECTID AS TAGID, objecttree.LFT AS OBJECTORDER, objecttree.*
			FROM
			(" . $this->_objectpropertytable . " AS object, `yg_tags_lnk_" . $this->_objectprefix . "` AS lnk, yg_tags_properties AS cat, " . $this->_objecttreetable . " AS objecttree)
			$dynpropsql
			WHERE
			(object.OBJECTID = lnk.OID) AND (lnk.TAGID = cat.OBJECTID) AND
			$noTrashSQL
			($sqls) AND $sqlsite
			(object.OBJECTID = objecttree.ID) AND (lnk.OVERSION = object.VERSION) AND $sql_final_w
			GROUP BY OBJECTID ORDER BY " . $sortcol . " " . $sortdir . " " . $filterLimit;
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra;
	}

	/**
	 * Gets assigned Tags for a specific version of this Object
	 *
	 * @return array|false Array of Tag Object links, FALSE in case of an error
	 */
	function getAssigned() {
		$oid = $this->_object->getID();
		$version = $this->_object->getVersion();

		$ids = $this->getIdentifierNameArray("OID", "OVERSION");
		$varlist = $this->getIdentifierValueArray($oid, $version);

		$sqlargs = array();

		// SQL for permissions
		$perm_SQL_SELECT = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RMODERATE) AS RMODERATE, MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_SQL_FROM = " LEFT JOIN yg_tags_permissions AS perm ON perm.OID = g.OBJECTID";
		$perm_SQL_WHERE = " ";
		for ($i = 0; $i < count($ids); $i++) {
			$perm_SQL_WHERE .= "( ";
			$perm_SQL_WHERE .= "lnk." . $ids[$i] . " = ? " ;
			$perm_SQL_WHERE .= ") AND ";
			array_push($sqlargs, $varlist[$i]);
		}
		$perm_SQL_WHERE .= "	(lnk.TAGID = g.OBJECTID) AND (";
		$roles = $this->permissions->getUsergroups();
		for ($r = 0; $r < count($roles); $r++) {
			$perm_SQL_WHERE .= "(perm.USERGROUPID = ?) ";
			if ((count($roles) - $r) > 1) {
				$perm_SQL_WHERE .= " OR ";
			}
			array_push($sqlargs, $roles[$r]['ID']);
		}
		$perm_SQL_WHERE .= ") ";

		$sql = "SELECT
					g.OBJECTID AS ID,
					g.NAME AS NAME,
					lnk.ORDERPROD AS ORDERPROD
					$perm_SQL_SELECT
				FROM
					`yg_tags_lnk_" . $this->_objectprefix . "` as lnk,
					yg_tags_properties as g
					$perm_SQL_FROM
				WHERE $perm_SQL_WHERE
				GROUP BY ID
				ORDER BY ORDERPROD;";

		array_unshift($sqlargs, $sql);
		$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
		$resultarray = $dbr->GetArray();
		return $resultarray;
	}

	/**
	 * Sets the order of the Tag assignment for a specific version of an Object
	 *
	 * @param int $tagId Tag Id
	 * @param int $position New position of the Tag Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setOrder($tagId, $position) {
		$tagId = (int)$tagId;
		$position = (int)$position;
		$oid = $this->_object->getID();
		$version = $this->_object->getVersion();

		if ($this->_object->permissions->checkInternal($this->_uid, $oid, 'RWRITE')) {
			$ids = $this->getIdentifierNameArray("OID", "OVERSION");
			$varlist = $this->getIdentifierValueArray($oid, $version);

			$sqlargs = array();
			$sql = "UPDATE `yg_tags_lnk_" . $this->_objectprefix . "` SET ORDERPROD = ? WHERE ";
			array_push($sqlargs, $position);
			for ($i = 0; $i < count($ids); $i++) {
				$value = sYDB()->escape_string($varlist[$i]);
				$id = sYDB()->escape_string(sanitize($ids[$i]));
				$sql .= "( ";
				$sql .= "`" . $id . "` =  ?";
				array_push($sqlargs, $value);
				$sql .= ") AND ";
			}
			$sql .= "(TAGID = ?);";
			array_push($sqlargs, $tagId);
			array_unshift($sqlargs, $sql);
			$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);

			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets additional information for specified tree nodes
	 *
	 * @param int $tagId Tag Id of the currently selected tree node
	 * @param array $objects List of tree nodes
	 * @return array List of tree nodes
	 */
	function getAdditionalTreeInfo($tagId, $objects) {
		if ($tagId < 1) {
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
				$tagId = $currentid;
				$selectdefault = false;
			}
			$shadowmenue[$currentid]["LEVEL"] = $mylevel;
			$shadowmenue[$currentid]["PARENT"] = $myparent;
			$shadowmenue[$currentid]["HIDDEN"] = $objects[$i]["HIDDEN"];

			if (($lastlevel + 1 == $mylevel)) {
				$parents[$mylevel] = $lastid;
			} else {
				$lastparent = $currentid;
			}
			$shadowmenue[$myparent]["CHILDREN"] += 1;
			$shadowmenue[$myparent]["LASTNODE"] = $currentid;
			if ($currentid == $tagId) {
				$objects[$i]["SELECTED"] = 1;
				$objects[$i]["SHOW"] = 1;
				$objects[$i]["SUBOPEN"] = 1;
				if ($objects[$i]["HIDDEN"] == 1) {
					$shadowmenue[$myparent]["SELECTED"] = 1;
				}
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
			$objects[$i]["CHILDREN"] = $shadowmenue[$currentid]["CHILDREN"];

			$objects[$i]["SHOW"] = $shadowmenue[$currentid]["SHOW"];
			if ($shadowmenue[$preid]["LEVEL"] < $objects[$i]["LEVEL"]) {
				$objects[$i]["FIRST"] = 1;
			}
			if ($objects[$i]["FIRST"] == 1) {
				if ($shadowmenue[$currentid]["HIDDEN"] == 1) {
					$objects[$i]["FIRST"] = 0;
					if ($shadowmenue[$postid]["LEVEL"] == $objects[$i]["LEVEL"]) {
						$objects[$i + 1]["FIRST"] = 1;
					}
				}
			}
			if (($shadowmenue[$postid]["LEVEL"] < $objects[$i]["LEVEL"])) {
				$objects[$i]["LAST"] = 1;
			}
			if ($shadowmenue[$postid]["LEVEL"] == "") {
				$objects[$i]["LAST"] = 1;
			}
			if (($objects[$i]["SHOW"] == 1)) {
				$shadowmenue[$myparent]["SHOW"] = 1;
				$shadowmenue[$myparentparent]["SHOW"] = 1;
				$shadowmenue[$myparentparent]["SUBOPEN"] = 1;
			}
			if ($shadowmenue[$myparent]["LASTNODE"] == $currentid) {
				$objects[$i]["LAST"] = 1;
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
		return $objects;
	}

	/**
	 * Gets Tag tree nodes
	 *
	 * @param int $tagId (optional) Id of the parent Tag from which the tree should be returned
	 * @param int $maxLevels (optional) Specifies the maximum level of nodes to get
	 * @return array Array of Tag nodes
	 */
	function getTree($tagId = NULL, $maxLevels = 2) {
		$maxLevels = (int)$maxLevels;
		$tagId = (int)$tagId;

		if ($tagId > 0) {
			$currentLevel = $this->tree->getLevel($tagId);
		} else {
			$currentLevel = 1;
			$tagId = $this->tree->getRoot();
		}

		$maxLevelSQL = " AND (group2.LEVEL <= " . ($maxLevels + $currentLevel) . ") AND (group2.LEVEL <= " . ($maxLevels + $currentLevel) . ")";

		$myinfo = $this->tree->getAll($tagId);
		if (!$myinfo) {
			return array();
		}

		$subnodeSQL = " AND (group2.LFT >= " . $myinfo["LFT"] . ") AND (group2.RGT <= " . $myinfo["RGT"] . ")";

		// SQL for permissions
		$perm_SQL_SELECT = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RMODERATE) AS RMODERATE, MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_SQL_FROM = " LEFT JOIN yg_tags_permissions AS perm ON perm.OID = group2.ID";
		$perm_SQL_WHERE = " AND (";
		$roles = $this->permissions->getUsergroups();
		for ($r = 0; $r < count($roles); $r++) {
			$perm_SQL_WHERE .= "(perm.USERGROUPID = " . (int)$roles[$r]["ID"] . ") ";
			if ((count($roles) - $r) > 1) {
				$perm_SQL_WHERE .= " OR ";
			}
		}
		$perm_SQL_WHERE .= ") ";

		$sql = "SELECT
					group2.ID,
					group2.LFT,
					group2.RGT,
					group2.VERSIONPUBLISHED,
					group2.TITLE,
					group2.LEVEL AS LEVEL,
					group2.PARENT AS PARENT,
					group2.PNAME AS PNAME,
					prop.NAME as NAME
					$perm_SQL_SELECT
				FROM
					(yg_tags_properties AS prop)
				LEFT JOIN $this->table AS group2 ON ((group2.ID = prop.OBJECTID) $maxLevelSQL $subnodeSQL)
					$perm_SQL_FROM
				WHERE
					1
					$perm_SQL_WHERE $maxLevelSQL $subnodeSQL
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID;";
		$tree = $this->cacheExecuteGetArray($sql);
		return $tree;
	}

	/**
	 * Gets a list of Tags
	 *
	 * @param int $tagId (optional) Id of the parent Tag from which the list will be created
	 * @param array $filter (optional) If SUBNODES, only subnodes of the specified Tag will be returned
	 * @param bool $usergroups (optional) If TRUE then also returns all Usergroups and Permissions for this node
	 * @param int $maxLevel (optional) Specifies the maximum level of nodes to get
	 * @param int $usergroupId (optional) If specified, only returns the list for the specific Usergroup Id
	 * @return array|false Array of Tags or FALSE in case of an error
	 */
	function getList($tagId = 0, $filter = array(), $usergroups = true, $maxLevel = 0, $usergroupId = 0) {
		$tagId = (int)$tagId;
		$maxLevel = (int)$maxLevel;
		$usergroupId = (int)$usergroupId;
		$selectdefault = true;
		$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");

		if ($tagId < 1) {
			$selectdefault = true;
			$tagId = $this->tree->getRoot();
		}
		if (in_array("SUBNODES", $filter)) {
			$myinfo = $this->tree->getAll($tagId);
			$myleft = $myinfo["LFT"];
			$myrgt = $myinfo["RGT"];
			$subnodesql = " AND (group2.LFT > $myleft AND group2.RGT < $myrgt) ";
			if (!$myinfo) {
				return false;
			}
		}
		if ($maxLevel > 0) {
			$maxLevelSQL = " AND (group2.LEVEL <= $maxLevel) AND (group1.LEVEL <= $maxLevel)";
		}
		if ($usergroups == true) {
			$perm_sql_select = ", MAX(perm.RREAD) AS RREAD, MAX(perm.RWRITE) AS RWRITE, MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE";
			$perm_sql_from = " LEFT JOIN yg_tags_permissions AS perm ON perm.OID = group2.ID";

			if ($usergroupId > 0) {
				$perm_sql_from .= " AND (perm.USERGROUPID = " . $usergroupId . ")";
			} else {
				$perm_sql_where = " AND (";
				$usergroups = $this->permissions->getUsergroups();
				for ($r = 0; $r < count($usergroups); $r++) {
					$perm_sql_where .= "(perm.USERGROUPID = " . (int)$usergroups[$r]["ID"] . ") ";
					if ((count($usergroups) - $r) > 1) {
						$perm_sql_where .= " OR ";
					}
				}
				$perm_sql_where .= ") ";
				$perm_sql_where .= " AND ((RREAD >= 1) OR (perm.USERGROUPID = " . $rootGroupId . ")) ";
			}
		}
		$sql = "SELECT
					group2.LFT,
					group2.RGT,
					group2.VERSIONPUBLISHED,
					group2.ID,
					group2.LEVEL AS LEVEL,
					group2.PARENT AS PARENT,
					prop.NAME AS NAME
					$perm_sql_select
				FROM
					($this->table AS group1, $this->table AS group2, yg_tags_properties AS prop)
					$perm_sql_from
				WHERE
					(group2.LFT BETWEEN group1.LFT AND group1.RGT) AND
					(group2.ID = prop.OBJECTID)
					$subnodesql
					$perm_sql_where
					$maxLevelSQL
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID
				ORDER BY group2.LFT;";

		$blaetter = $this->cacheExecuteGetArray($sql);
		$shadowmenue = array();
		$parents = array();
		for ($i = 0; $i < count($blaetter); $i++) {
			$currentid = $blaetter[$i]["ID"];
			$mylevel = $blaetter[$i]["LEVEL"];
			$myparent = $blaetter[$i]["PARENT"];
			$myparentparent = $shadowmenue[$myparent]["PARENT"];
			if (($selectdefault == true) && ($mylevel > 1)) {
				$tagId = $currentid;
				$selectdefault = false;
			}
			$shadowmenue[$currentid]["LEVEL"] = $mylevel;
			$shadowmenue[$currentid]["PARENT"] = $myparent;
			if (($lastlevel + 1 == $mylevel)) {
				$parents[$mylevel] = $lastid;
			}
			if ($currentid == $tagId) {
				$blaetter[$i]["SELECTED"] = 1;
				$blaetter[$i]["SHOW"] = 1;
				$blaetter[$i]["SUBOPEN"] = 1;
				$shadowmenue[$currentid]["SHOW"] = 1;
				$shadowmenue[$currentid]["SUBOPEN"] = 1;
				$shadowmenue[$currentid]["SELECTED"] = 1;
				$shadowmenue[$myparent]["SHOW"] = 1;
				$shadowmenue[$myparent]["SUBOPEN"] = 1;
			}
			if (($shadowmenue[$myparent]["SUBOPEN"] == 1)) {
				$blaetter[$i]["SHOW"] = 1;
				$shadowmenue[$myparent]["SHOW"] = 1;
			}
			if ($blaetter[$i]["SHOW"] == 1) {
				$shadowmenue[$myparent]["SHOW"] = 1;
				$shadowmenue[$myparent]["SUBOPEN"] = 1;
				$shadowmenue[$myparentparent]["SHOW"] = 1;
				$shadowmenue[$myparentparent]["SUBOPEN"] = 1;
			}
			$lastlevel = $mylevel;
			$lastid = $currentid;
		}

		for ($i = 0; $i < count($blaetter); $i++) {
			$currentid = $blaetter[$i]["ID"];
			$myparent = $blaetter[$i]["PARENT"];
			$myparentparent = $shadowmenue[$myparent]["PARENT"];
			$preid = $blaetter[$i - 1]["ID"];
			$postid = $blaetter[$i + 1]["ID"];
			$blaetter[$i]["SUBOPEN"] = $shadowmenue[$currentid]["SUBOPEN"];
			$blaetter[$i]["SHOW"] = $shadowmenue[$currentid]["SHOW"];
			if ($shadowmenue[$preid]["LEVEL"] < $blaetter[$i]["LEVEL"]) {
				$blaetter[$i]["FIRST"] = 1;
			}
			if (($shadowmenue[$postid]["LEVEL"] < $blaetter[$i]["LEVEL"])) {
				$blaetter[$i]["LAST"] = 1;
			}
			if ($shadowmenue[$postid]["LEVEL"] == "") {
				$blaetter[$i]["LAST"] = 1;
			}
			if (($blaetter[$i]["SHOW"] == 1)) {
				$shadowmenue[$myparent]["SHOW"] = 1;
				$shadowmenue[$myparentparent]["SHOW"] = 1;
				$shadowmenue[$myparentparent]["SUBOPEN"] = 1;
			}
			if (($shadowmenue[$myparent]["SUBOPEN"] == 1)) {
				$blaetter[$i]["SHOW"] = 1;
			}
			if ($shadowmenue[$postid]["LEVEL"] > $blaetter[$i]["LEVEL"]) {
				$blaetter[$i]["HASSUBNODES"] = 1;
			} else {
				$blaetter[$i]["HASSUBNODES"] = 0;
			}
		}
		return ($blaetter);
	}

	/**
	 * Checks if the specified Tag has subnodes
	 *
	 * @param int $tagId Tag Id
	 * @return bool TRUE if the Tag has subnodes, FALSE if not
	 */
	function hasSubnodes($tagId) {
		$tagId = (int)$tagId;
		$myinfo = ($this->tree->getAll($tagId));
		if ($myinfo["LFT"] + 1 == $myinfo["RGT"]) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Sets the name of a Tag
	 *
	 * @param int $tagId Tag Id
	 * @param string $name Tag name
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setName($tagId, $name) {
		$tagId = (int)$tagId;
		if ($this->permissions->checkInternal($this->_uid, $tagId, 'RWRITE')) {
			$name = sYDB()->escape_string($name);
			$sql = "UPDATE yg_tags_properties SET NAME = ? WHERE (OBJECTID = ?);";
			$result = sYDB()->Execute($sql, $name, $tagId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adds a new Tag
	 *
	 * @param int $parentTagId Parent Tag Id
	 * @param string $name (optional) Tag name
	 * @return int Tag Id of new Tag
	 * @throws Exception
	 */
	function add($parentTagId, $name = "New Tag") {
		$parentTagId = (int)$parentTagId;
		if ($this->permissions->checkInternal($this->_uid, $parentTagId, 'RSUB')) {
			$name = sYDB()->escape_string($name);
			// create node in Tagstree
			$tagId = (int)$this->tree->add($parentTagId);

			// create version
			$sql = "INSERT INTO `yg_tags_properties` (`OBJECTID`, `NAME`) VALUES (?, ?);";
			$result = sYDB()->Execute($sql, $tagId, $name);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->permissions->copyTo($parentTagId, $tagId);
			return $tagId;
		} else {
			return false;
		}
	}

	/**
	 * Removes a specific Tag
	 *
	 * @param int $tagId Tag Id
	 *
	 * @return array Array with all elements which were successfully deleted
	 */
	function remove($tagId) {
		$tagId = $origTagId = (int)$tagId;
		$rootNode = $this->tree->getRoot();
		if ($tagId == $rootNode) {
			return array();
		}

		// Get all nodes
		$successNodes = array();
		$allNodes = $this->tree->get($tagId, 1000);
		foreach($allNodes as $allNodesItem) {
			$tagId = (int)$allNodesItem['ID'];
			if ($this->permissions->checkInternal($this->_uid, $tagId, "RDELETE")) {
				$sql = "DELETE FROM yg_tags_properties WHERE OBJECTID = ?;";
				sYDB()->Execute($sql, $tagId);

				$successNodes[] = $tagId;
			}
		}
		if (in_array($origTagId, $successNodes)) {
			$this->tree->remove($origTagId);
		}

		if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/TAG_DELETE") == "true") {
			Singleton::FC()->emptyBucket();
		}

		return $successNodes;
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
function TagGetByTagCB(&$list, $type, $operator, $value1 = 0, $value2 = 0) {
	$op = GetContainsOperators($operator);
	switch ($type) {
		case "DELETED":
			if (0 < $value1) {
				$list["WHERE"][] = "object.DELETED " . $op . " " . (int)$value1;
			}
			break;
		case "LIMITER":
			if ((int)$value2 > 0) {
				$list["LIMIT"][] = "LIMIT " . (int)$value1 . "," . (int)$value2;
			}
			break;
	}
}

/// @endcond

?>