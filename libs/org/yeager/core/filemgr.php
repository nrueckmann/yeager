<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * File types
 */
define('FILE_TYPE_WEBNONE', 0);
define('FILE_TYPE_WEBIMAGE', 1);
define('FILE_TYPE_WEBAUDIO', 2);
define('FILE_TYPE_WEBVIDEO', 3);
define('FILE_TYPE_WEBVECTOR', 4);
define('FILE_TYPE_PDF', 5);

/**
 * Gets an instance of the FileMgr
 * @return object FileMgr object
 */
function sFileMgr() {
	return Singleton::fileMgr();
}

/**
 * The FileMgr class, which represents an instance of the File manager.
 */
class FileMgr extends \framework\Error {
	var $_db;
	var $_uid;

	var $db;
	var $baum;
	var $table;

	var $properties;
	var $history;
	var $permissions;
	var $views;
	var $filetypes;
	var $scheduler;


	/**
	 * Constructor of the PageMgr class
	 */
	public function __construct() {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();

		$this->table = "yg_files_tree";
		$this->tree = new tree($this);
		$this->permissions = new Permissions($this->getPermissionsTable(), $this);
		$this->history = new History($this, HISTORYTYPE_FILE, $this->permissions);
		$this->properties = new PropertySettings("yg_files_props");
		$this->tags = new Tags($this);
		$this->tags->_dobjectpropertytable = "yg_files_propsv";
		$this->scheduler = new Scheduler("yg_cron", SCHEDULER_FILE);
		$this->views = new Views($this);
		$this->filetypes = new Filetypes();
	}

/// @cond DEV

	/**
	 * Gets the name of the database table which contains the permissions for Files
	 *
	 * @return string Tablename
	 */
	function getPermissionsTable() {
		return "yg_files_permissions";
	}

/// @endcond

/// @cond DEV

	/**
	 * Helper method for querying the database
	 *
	 * @param string $sql SQL query
	 * @return array|bool Result of SQL query or FALSE in case of an error
	 * @throws Exception
	 */
	function cacheExecuteGetArray($sql) {
		$timestart = microtime();
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . "<br>" . $sql);
		}
		$blaetter = $dbr->GetArray();
		return $blaetter;
	}

/// @endcond

	/**
	 * Gets a specific File instance
	 *
	 * @param int $fileId File Id
	 * @param int $version (optional) Page version
	 * @return Page|false New instance of File object or FALSE if an error has occured
	 */
	public function getFile($fileId, $version = 0) {
		if ($this->permissions->checkInternal($this->_uid, $fileId, "RREAD")) {
			try {
				return new File($fileId, $version);
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
	}


	/**
	 * Checks if a specific File exists
	 *
	 * @param int $fileId File Id
	 * @return bool TRUE if file exists, FALSE if not
	 */
	public function fileExists($fileId) {
		sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
		$check = sFileMgr()->getFile($fileId);
		sUserMgr()->unimpersonate();
		if ($check) {
			return true;
		} else {
			return false;
		}
		sUserMgr()->unimpersonate();
	}


	/**
	 * Gets File Id by permanent name
	 *
	 * @param string $PName Permanent name
	 * @return int File Id
	 */
	public function getFileIdByPname($PName) {
		$PName = mysql_real_escape_string(sanitize($PName));
		$sql = "SELECT ID FROM yg_files_tree as t WHERE (t.PNAME = '$PName');";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra[0]['ID'];
	}

	/**
	 * Gets File permanent name by Id
	 *
	 * @param int $fileId File Id
	 * @return int Permanent name
	 */
	public function getPNameByFileId($fileId) {
		$fileId = mysql_real_escape_string(sanitize((int)$fileId));
		if ($this->permissions->checkInternal($this->_uid, $fileId, "RREAD")) {
			$sql = "SELECT PNAME FROM yg_files_tree as t WHERE (t.ID = $fileId);";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra[0]['PNAME'];
		}
		return false;
	}

/// @cond DEV

	/**
	 * Gets the object prefix, used for table names in database queries
	 *
	 * @return string
	 */
	function getObjectPrefix() {
		return 'files';
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
	 * Gets the name of the database table which contains the properties of Files
	 *
	 * @return string Tablename
	 */
	function getPropertyTable() {
		return 'yg_files_properties';
	}

/// @endcond

	/**
	 * Callback method which is executed when a Usergroup permission on a File changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId File Id
	 */
	public function onPermissionChange($usergroupId, $permission, $value, $objectId) {
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on a File change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param int $objectId File Id
	 */
	public function onPermissionsChange($usergroupId, $permissions, $objectId) {
		return true;
	}

/// @cond DEV

	/**
	 * Gets the name of the database table containing the Files tree
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
	 * @param int $fileId File Id
	 * @param int $version File version
	 * @param mixed $args Arbitrary arguments
	 */
	function callExtensionHook($method, $fileId, $version, $args) {
		$extensions = new ExtensionMgr($this->_db, $this->_uid);
		$all_file_extensions = $extensions->getList(EXTENSION_FILE, true);
		$extarr = array();
		foreach ($all_file_extensions as $all_file_extension) {
			$extension = $extensions->getExtension($all_file_extension['CODE']);
			if ($extension && $extension->usedByFile($fileId, $version) === true) {
				$extension = $extensions->getExtension($all_file_extension['CODE'], $fileId, $version);
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
	 * Gets the parents of the specified File
	 *
	 * @param int $fileId File Id
	 * @return array Array of parent Files
	 */
	function getParents($fileId) {
		if ($this->permissions->checkInternal($this->_uid, $fileId, "RREAD")) {
			$parentnodes = $this->tree->getParents($fileId);

			if ($parentnodes[0] === NULL) {
				return array();
			}

			$parentnodeidsql = implode(",", $parentnodes);

			$sql = "SELECT
				group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID AS ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT, prop.FOLDER AS FOLDER,
				MAX(prop.VERSION) AS VERSION, prop.LOCKED AS LOCKED, pv.*
				FROM
				($this->table AS group2, yg_files_properties AS prop)
				LEFT JOIN yg_files_propsv AS pv ON pv.OID = prop.ID
				WHERE
				(group2.ID = prop.OBJECTID) AND (group2.ID IN ($parentnodeidsql))
				GROUP BY
				group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID order by group2.LEVEL DESC;";

			$dbr = sYDB()->Execute($sql);

			$parentsO = $dbr->GetArray();

			// Prepare weird array dimension
			$parents = array();
			for ($i = 0; $i < count($parentsO); $i++) {
				$parents[$i][] = $parentsO[$i];
			}

			if (count($parents) > 0) {
				$itext = Singleton::itext();
				$parents[count($parents) - 1][0]['NAME'] = $itext['TXT_FILES'];
			}
			return $parents;
		} else {
			return false;
		}
	}

	/**
	 * Gets additional information for Tree nodes
	 *
	 * @param int $fileId (optional) Id of the parent File from which the list will be created
	 * @param array $objects Array of Tree nodes
	 * @return array Array of Tree nodes
	 */
	function getAdditionalTreeInfo($fileId, $objects) {
		if ($fileId < 1) {
			$selectdefault = true;
		}
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
				$fileId = $currentid;
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
			if ($currentid == $fileId) {
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
					} else {
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
	 * Get Files tree nodes
	 *
	 * @param int $fileId (optional) From which File Id the tree should be returned
	 * @param int $maxLevels (optional) Specifies the maximum level of nodes to get
	 * @param bool $noTrash (optional) FALSE when item from the Trash should also be returned
	 * @return array Array of File nodes
	 */
	function getTree($fileId = NULL, $maxLevels = 2, $noTrash = true) {
		$maxLevels = (int)$maxLevels;

		if ($fileId > 0) {
			$currentLevel = $this->tree->getLevel($fileId);
		} else {
			$currentLevel = 1;
			$fileId = $this->tree->getRoot();
		}

		if ($noTrash) {
			$filterSQL_WHERE .= " AND prop.DELETED = 0";
		}

		$maxLevelSQL = " AND (group2.LEVEL <= " . ($maxLevels + $currentLevel) . ") AND (group2.LEVEL <= " . ($maxLevels + $currentLevel) . ")";

		$myinfo = $this->tree->getAll($fileId);
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
			group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT, group2.PNAME AS PNAME,
			prop.VERSION AS VERSION, prop.FOLDER AS FOLDER, prop.FILENAME AS FILENAME, prop.FILETYPE AS FILETYPE, prop.FILESIZE AS FILESIZE, SUBSTRING_INDEX(prop.FILENAME, '.', -1) AS EXTENSION, pv.*,
			prop.CREATEDTS, prop.CHANGEDTS, prop.CREATEDBY, prop.CHANGEDBY
			$perm_SQL_SELECT
			FROM
			(yg_files_properties AS prop)
			LEFT JOIN $this->table AS group2 ON ((group2.ID = prop.OBJECTID) $maxLevelSQL $subnodeSQL)
			LEFT JOIN yg_files_propsv AS pv ON pv.OID = prop.ID
			$perm_SQL_FROM
			WHERE
				(prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_files_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID)))
				AND prop.FOLDER = 1
			$perm_SQL_WHERE $filterSQL_WHERE
			$subnodesql
			GROUP BY
			group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID;";
		$tree = $this->cacheExecuteGetArray($sql);
		return $tree;
	}

	/**
	 * Gets a list of Files
	 *
	 * @param int $fileId (optional) Id of the parent File from which the list will be created
	 * @param array $filter (optional, may be combined) If SUBNODES, only subnodes of the specified File will be returned<br>
	 *                                 if TRASHCAN, only items in the Trash will be returned<br>
	 *                                 if FOLDERS, only folders will be returned
	 * @param string $sort (optional) "ORDER BY" Sql clause
	 * @param int $maxLevel (optional) Specifies the maximum level of nodes to get
	 * @param int $usergroupId (optional) Return List for a specfic Usergroup Id
	 * @param array $filterArray Array of filters for the SQL query
	 * @return array|false Array of Files or FALSE in case of an error
	 * @throws Exception
	 */
	function getList($fileId = 0, $filter = array(), $sort = 'group2.LFT', $maxLevel = 0, $usergroupId = 0, $filterArray) {
		$fileId = (int)$fileId;
		$sort = sanitize($sort);
		$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");

		if ($fileId == 0) {
			$fileId = $this->tree->getRoot();
		}
		if (in_array("SUBNODES", $filter)) {
			$myinfo = $this->tree->getAll($fileId);
			$myleft = $myinfo["LFT"];
			$myrgt = $myinfo["RGT"];
			$subnodesql = " AND (group2.LFT > $myleft AND group2.RGT < $myrgt) ";
			if (!$myinfo) {
				return false;
			}
		}
		if (in_array("FOLDERS", $filter)) {
			$subnodesql .= " AND (prop.FOLDER = 1)";
		}
		// Surpress items in trashcan if not explicitly asked for
		if (in_array("TRASHCAN", $filter)) {
			$subnodesql .= " AND prop.DELETED = 1 AND prop.FOLDER = 0";
		} else {
			$subnodesql .= " AND prop.DELETED = 0";
		}

		// Check if special filter was suppplied
		$filterOrder = 'ORDER BY ' . $sort;
		if ($filterArray) {
			$filterSelect = $filterFrom = $filterWhere = $filterLimit = $filterOrder = '';
			buildBackendFilter('FilesSearchCB', $filterArray, $filterSelect, $filterFrom, $filterWhere, $filterLimit, $filterOrder);
			$subnodesql .= $filterWhere;
		}

		if ($maxLevel > 0) {
			$maxlevelsql = " AND (group2.LEVEL <= $maxLevel) AND (group1.LEVEL <= $maxLevel)";
		}

		$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE,  MAX(perm.RSUB) AS RSUB,  MAX(perm.RSTAGE) AS RSTAGE,  MAX(perm.RMODERATE) AS RMODERATE,  MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_sql_from = " LEFT JOIN yg_files_permissions AS perm ON perm.OID = group2.ID";

		if ($usergroupId > 0) {
			$perm_sql_from .= " AND (perm.USERGROUPID = " . $usergroupId . ")";
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
					group2.VERSIONPUBLISHED,
					group2.ID,
					group2.LEVEL AS LEVEL,
					group2.PARENT AS PARENT,
					MAX(prop.VERSION) AS VERSION,
					prop.FOLDER AS FOLDER,
					prop.FILENAME AS FILENAME,
					prop.FILETYPE AS FILETYPE,
					prop.FILESIZE AS FILESIZE,
					SUBSTRING_INDEX(prop.FILENAME, '.', -1) AS EXTENSION,
					pv.*,
					prop.CREATEDTS,
					prop.CHANGEDTS,
					prop.CREATEDBY,
					prop.CHANGEDBY
					$perm_sql_select
				FROM
					($this->table AS group1, $this->table AS group2, yg_files_properties AS prop)
					$perm_sql_from
				LEFT JOIN yg_files_propsv AS pv
					ON pv.OID = prop.ID
				WHERE
					((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
					(group2.ID = prop.OBJECTID) AND
					(prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_files_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID)))
					$perm_sql_where
					$maxlevelsql
					$subnodesql
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID
				$filterOrder $filterLimit;";

		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$blaetter = $dbr->GetArray();
		return ($blaetter);
	}

	/**
	 * Adds a new File folder to the specified parent File folder
	 *
	 * @param int $parentFileId Parent File folder Id
	 * @param string $name (optional) File folder name
	 * @return int New File folder Id
	 * @throws Exception
	 */
	function addFolder($parentFileId, $name = 'New Folder') {
		$parentFileId = (int)$parentFileId;
		$name = mysql_real_escape_string($name);
		if ($this->permissions->checkInternal($this->_uid, $parentFileId, "RSUB")) {
			// Create node in File Tree
			$fileId = $this->tree->add($parentFileId);
			$this->filetypes = new Filetypes();
			$type = $this->filetypes->getByCode('FLD');
			$ts = time();
			$sql = "INSERT INTO
				`yg_files_properties`
					(`OBJECTID`, `FOLDER`, `APPROVED`, `CREATEDTS`, `CHANGEDTS`, `FILENAME`, `FILETYPE`, `VERSION`)
				VALUES
					('$fileId', '1', '1', '$ts', '$ts', '', '" . $type[0]['OBJECTID'] . "', 1);";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->_db->Insert_ID();
			$this->permissions->copyTo($parentFileId, $fileId);
			$TargetFile = new File($fileId);
			$TargetFile->properties->setValue("NAME", $name);
			$sourceFile = new File($parentFileId);
			$TargetFile->views->copyTo($parentFileId, $sourceFile->getLatestVersion(), $fileId, $TargetFile->getLatestVersion());
			return $fileId;
		} else {
			return false;
		}
	}

	/**
	 * Unlinks multiple file
	 *
	 * @param string $filePath File path (may include wildcards)
	 */
	function unlinkMultipleFiles($filePath) {
		$files = glob($filePath);
		foreach ($files as $file) {
			if (is_file($file)) {
				@unlink($file);
			}
		}
	}

	/**
	 * Removes a File from Trash
	 *
	 * @param int $fileId File Id
	 * @param string $filesDir File path (may include wildcards)
	 *
	 * @return array Array with all elements which were successfully removed
	 * @throws Exception
	 */
	function remove($fileId, $filesDir) {
		$fileId = $origFileId = (int)$fileId;
		$rootNode = $this->tree->getRoot();
		if ($fileId == $rootNode) {
			return array();
		}

		// Check if object is really in trash
		$file = new File($fileId);
		$fileInfo = $file->get();

		// Get all nodes
		$hadError = false;
		$allNodes = $this->tree->get($fileId, 1000);
		foreach($allNodes as $allNodesItem) {
			$fileId = $allNodesItem['ID'];

			if ($file->permissions->checkInternal($this->_uid, $fileId, "RDELETE") && $fileInfo['DELETED']) {
				if (!$fileInfo['FOLDER']) {
					$sql = "SELECT * FROM yg_files_properties WHERE OBJECTID = $fileId;";
					$result = sYDB()->Execute($sql);
					if ($result === false) {
						throw new Exception(sYDB()->ErrorMsg());
					}
					$ra = $result->GetArray();
					for ($i = 0; $i < count($ra); $i++) {
						$filetokill = $ra[$i]["OBJECTID"] . "-" . $ra[$i]["VERSION"] . $ra[$i]["FILENAME"];
						$filetokillp = getrealpath($filesDir . $filetokill); // combine the path and file

						@unlink($filetokillp);
						$this->unlinkMultipleFiles(getrealpath($filesDir) . "/*" . $filetokill);
					}
				}

				$file->history->clear();
				$file->tags->clear();
				$file->views->clear();

				$sql = "DELETE FROM yg_files_properties WHERE OBJECTID = $fileId;";
				sYDB()->Execute($sql);

				$this->callExtensionHook('onRemove', $fileId, 0, $fileInfo);
			} else {
				$hadError = true;
			}
		}
		if ($hadError) {
			return array();
		} else {
			$this->tree->remove($origFileId);
			return array($origFileId);
		}
	}

	/**
	 * Adds a new File to the specified parent File folder
	 *
	 * @param int $parentFileId Parent File Id
	 * @param string $name (optional) File name
	 * @param int $fileType Filetype Id
	 * @return int New File Id
	 * @throws Exception
	 */
	function add($parentFileId, $name, $fileType) {
		$parentFileId = (int)$parentFileId;
		$fileType = (int)$fileType;
		$name = mysql_real_escape_string($name);
		if ($this->permissions->checkInternal($this->_uid, $parentFileId, "RSUB")) {
			// Create node in File Tree
			$fileId = $this->tree->add($parentFileId);

			// Create new version
			$ts = time();
			$sql = "INSERT INTO `yg_files_properties`
					(`OBJECTID`, `FOLDER`, `CREATEDTS`, `CHANGEDTS`, `FILETYPE`, `CREATEDBY`, `CHANGEDBY`, `VERSION`)
				VALUES
					('$fileId', '0', '$ts', '$ts', '$fileType', '" . $this->_uid . "', '" . $this->_uid . "', 1);";
			$result = sYDB()->Execute($sql);

			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$propid = sYDB()->Insert_ID();

			$sql = "UPDATE yg_files_properties SET FILETS = $ts WHERE ID = " . $propid . ";";
			$result = sYDB()->Execute($sql);

			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			$this->permissions->copyTo($parentFileId, $fileId);

			$tmpFile = new File($fileId);
			$tmpFile->properties->setValue("NAME", $name);

			$this->views->copyTo($parentFileId, 0, $fileId, 1);

			$tmpFileInfo = $tmpFile->get();
			$this->callExtensionHook("onAdd", $fileId, $tmpFileInfo['VERSION']);

			// Add to history
			$tmpFile->history->add(HISTORYTYPE_FILE, NULL, 1, 'TXT_FILE_H_NEWVERSION');

			return $fileId;
		} else {
			return false;
		}
	}

	/**
	 * Gets the subnodes of the specified File
	 *
	 * @param int $fileId File Id of the parent File
	 * @return array|false Array of subnodes or FALSE in case of an error
	 */
	function getSubnodes($fileId) {
		$fileId = (int)$fileId;
		return $this->tree->get($fileId);
	}

	/**
	 * Gets a list of Files from a File folder
	 *
	 * @param int $fileId Id of the parent File folder from which the list will be created
	 * @param string $sortby Column to use for sorting: "title", "filename" or "filesize"
	 * @param string $filetype Filetype
	 * @param string $filter (optional) If "TRASHCAN", only items in the Trash will be returned
	 * @return array|false Array of Pages or FALSE in case of an error
	 */
	function getFilesFromFolder($fileId, $sortby = '', $filetype = '', $filter = '') {
		$fileId = (int)$fileId;
		$sortby = mysql_real_escape_string(sanitize($sortby));
		$filetype = mysql_real_escape_string(sanitize($filetype));
		if (strlen($fileId) < 1) {
			return;
		}
		if ($this->permissions->checkInternal($this->_uid, $fileId, "RREAD")) {
			$subnodes = $this->getSubnodes($fileId);
			$file = new File($fileId);
			$moinfo = $file->get();
			$folderlevel = $moinfo["LEVEL"];
			$fileids = array();
			// Find fileIds in this folder
			for ($i = 0; $i < count($subnodes); $i++) {
				if (($subnodes[$i]["FOLDER"] == 0) && ($subnodes[$i]["PARENT"] == $fileId)) {
					$fileids[] = $subnodes[$i]["ID"];
				}
			}
			for ($i = 0; $i < count($fileids); $i++) {
				$fileidsql .= "(lft.OBJECTID = $fileids[$i]) ";
				if ($i < (count($fileids) - 1)) {
					$fileidsql .= " OR";
				}
			}
			if (strlen($sortby) > 0) {
				if ($sortby == "title") {
					$sortsql = " ORDER BY pv.NAME ASC";
				}
				if ($sortby == "filename") {
					$sortsql = " ORDER BY lft.FILENAME ASC";
				}
				if ($sortby == "filesize") {
					$sortsql = " ORDER BY lft.FILESIZE DESC";
				}
			} else {
				$sortsql = " ORDER BY pv.NAME ASC";
			}
			if (strlen($filetype) > 0) {
				$showsql = " AND (lft.FILETYPE = '$filetype')";
			}
			// Surpress items in trashcan if not explicitly asked for
			if ($filter != "TRASHCAN") {
				$filtersql_where = " AND lft.DELETED = 0";
			} else {
				$filtersql_where = " AND lft.DELETED = 1";
			}

			if (count($fileids) > 0) {
				$sql = "
					SELECT
					lft.OBJECTID AS OBJECTID, tree.PNAME AS PNAME, lft.ID as ID, lft.FILENAME AS FILENAME, lft.FILESIZE AS FILESIZE, lft.FILETYPE AS FILETYPE, lft.VERSION AS VERSION, lft.VIEWVERSION AS VIEWVERSION,
					h.TEXT, h.DATETIME as DATETIME, h.UID AS UID,
					types.NAME AS TYPENAME, types.CODE AS CODE, types.IDENTIFIER AS IDENTIFIER, types.COLOR AS COLOR, SUBSTRING_INDEX(lft.FILENAME, '.', -1) AS EXTENSION, pv.*
					$perm_sql_select
					FROM
					(yg_files_properties AS lft)
					LEFT JOIN yg_filetypes_properties AS types ON types.OBJECTID = lft.FILETYPE
					LEFT JOIN yg_files_propsv AS pv ON pv.OID = lft.ID
					LEFT JOIN yg_files_tree AS tree ON tree.ID = lft.OBJECTID
					LEFT JOIN yg_history AS h ON ((h.OID = lft.OBJECTID) AND (h.SOURCEID = '" . HISTORYTYPE_FILE . "') AND
					(h.ID = (SELECT MIN( hrgt.ID ) FROM yg_history AS hrgt WHERE (h.OID = hrgt.OID AND (hrgt.SOURCEID = '" . HISTORYTYPE_FILE . "')))))
					$perm_sql_from
					WHERE
					(lft.FOLDER = 0) AND
					(lft.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_files_properties AS rgt WHERE (lft.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1))) AND
					$perm_sql_where
					($fileidsql) $showsql $filtersql_where $sortsql";

				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$ra = $result->GetArray();
				return $ra;
			}
		}
	}

	/**
	 * Gets a list of Filetypes
	 *
	 * @return array Array of Filetypes
	 * @throws Exception
	 */
	function getFiletypes() {
		$sql = "SELECT OBJECTID AS ID, NAME, CODE, IDENTIFIER, COLOR, PROCESSOR, EXTENSIONS FROM yg_filetypes_properties WHERE (FOLDER = 0) ORDER BY NAME ";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $ra;
	}

	/**
	 * Gets Locks for the specific Token
	 *
	 * @param string $token Lock Token
	 * @return array Array of File Locks
	 * @throws Exception
	 */
	public function getLocksByToken($token) {
		$token = mysql_real_escape_string($token);
		if ($token == "") {
			return false;
		}
		$sql = "SELECT OBJECTID, LOCKED, TOKEN FROM yg_files_properties WHERE TOKEN = '" . $token . "';";
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
function FilesSearchCB(&$list, $type, $operator, $value1 = 0, $value2 = 0) {
	$op = GetContainsOperators($operator);
	switch ($type) {
		case 'CREATEDTS':
			if (0 < $value1) {
				$list['WHERE'][] = 'prop.CREATEDTS ' . $op . ' ' . (int)$value1;
			}
			break;

		case 'CHANGEDTS':
			if (0 < $value1) {
				$list['WHERE'][] = 'prop.CHANGEDTS ' . $op . ' ' . (int)$value1;
			}
			break;

		case 'LIMITER':
			if ((int)$value2 > 0) {
				$list['LIMIT'][] = 'LIMIT ' . (int)$value1 . ',' . (int)$value2;
			}
			break;

		case 'ORDER':
			$list['ORDER'][] = 'ORDER BY ' . $value1 . ' ' . $value2;
			break;
	}
}

/// @endcond

?>