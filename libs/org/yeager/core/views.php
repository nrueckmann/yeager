<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Gets an instance of the View manager
 *
 * @return object View manager object
 */
function sViews() {
	return Singleton::views();
}

/**
 * The Views class, which represents an instance of the View manager.
 */
class Views extends \framework\Error {
	var $_db;
	var $_uid;
	var $id;
	var $permissions;

	/**
	 * Constructor of the Views class
	 */
	function __construct(&$file = NULL) {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->table = "yg_views_tree";
		$this->tree = new Tree($this);
		$this->permissions = new Permissions("yg_views_permissions", $this);
		$this->file = &$file;
	}

/// @cond DEV

	/**
	 * Gets the name of the database table containing the Views tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return $this->table;
	}

/// @endcond

	/**
	 * Callback method which is executed when a Usergroup permission on a View changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId View Id
	 */
	public function onPermissionChange($usergroupId, $permission, $value, $objectId) {
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on a View change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param int $objectId File Id
	 */
	public function onPermissionsChange($usergroupId, $permissions, $objectId) {
		return true;
	}

	/**
	 * Sets the Code for a specific View
	 *
	 * @param int $viewId View Id
	 * @param string $value View code
	 * @return bool TRUE on success or FALSE when an error has occured
	 * @throws Exception
	 */
	function setCode($viewId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RVIEWS')) {
			$viewId = (int)$viewId;
			$value = mysql_real_escape_string(sanitize($value));
			$sql = "UPDATE `yg_views_properties` SET CODE = '$value' WHERE OBJECTID = $viewId;";
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
	 * Sets the Name for a specific View
	 *
	 * @param int $viewId View Id
	 * @param string $value View Name
	 * @return bool TRUE on success or FALSE when an error has occured
	 * @throws Exception
	 */
	function setName($viewId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RVIEWS')) {
			$viewId = (int)$viewId;
			$value = mysql_real_escape_string(sanitize($value));
			$sql = "UPDATE `yg_views_properties` SET NAME = '$value' WHERE OBJECTID = $viewId;";
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
	 * Sets the identifier for a specific View
	 *
	 * @param int $viewId View Id
	 * @param string $value View identifier
	 * @return bool TRUE on success or FALSE when an error has occured
	 * @throws Exception
	 */
	function setIdentifier($viewId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RVIEWS')) {
			$viewId = (int)$viewId;
			$value = mysql_real_escape_string(sanitize($value));
			$sql = "UPDATE `yg_views_properties` SET IDENTIFIER = '$value' WHERE OBJECTID = $viewId;";
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
	 * Sets the width for a specific View
	 *
	 * @param int $viewId View Id
	 * @param string $value View width
	 * @return bool TRUE on success or FALSE when an error has occured
	 * @throws Exception
	 */
	function setWidth($viewId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RVIEWS')) {
			$viewId = (int)$viewId;
			$value = (int)$value;
			$sql = "UPDATE `yg_views_properties` SET WIDTH = '$value' WHERE OBJECTID = $viewId;";
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
	 * Sets the width-cropping for a specific View
	 *
	 * @param int $viewId View Id
	 * @param string $value View width-cropping
	 * @return bool TRUE on success or FALSE when an error has occured
	 * @throws Exception
	 */
	function setWidthCrop($viewId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RVIEWS')) {
			$viewId = (int)$viewId;
			$value = (int)$value;
			$sql = "UPDATE `yg_views_properties` SET WIDTHCROP = '$value' WHERE OBJECTID = $viewId;";
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
	 * Sets the width-constrain for a specific View
	 *
	 * @param int $viewId View Id
	 * @param string $value View width-constrain
	 * @return bool TRUE on success or FALSE when an error has occured
	 * @throws Exception
	 */
	function setWidthConstrain($viewId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RVIEWS')) {
			$viewId = (int)$viewId;
			$value = (int)$value;
			$sql = "UPDATE `yg_views_properties` SET CONSTRAINWIDTH = '$value' WHERE OBJECTID = $viewId;";
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
	 * Sets the height for a specific View
	 *
	 * @param int $viewId View Id
	 * @param string $value View height
	 * @return bool TRUE on success or FALSE when an error has occured
	 * @throws Exception
	 */
	function setHeight($viewId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RVIEWS')) {
			$viewId = (int)$viewId;
			$value = (int)$value;
			$sql = "UPDATE `yg_views_properties` SET HEIGHT = '$value' WHERE OBJECTID = $viewId;";
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
	 * Sets the height-cropping for a specific View
	 *
	 * @param int $viewId View Id
	 * @param string $value View height-cropping
	 * @return bool TRUE on success or FALSE when an error has occured
	 * @throws Exception
	 */
	function setHeightCrop($viewId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RVIEWS')) {
			$viewId = (int)$viewId;
			$value = (int)$value;
			$sql = "UPDATE `yg_views_properties` SET HEIGHTCROP = '$value' WHERE OBJECTID = $viewId;";
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
	 * Sets the height-constrain for a specific View
	 *
	 * @param int $viewId View Id
	 * @param string $value View height-constrain
	 * @return bool TRUE on success or FALSE when an error has occured
	 * @throws Exception
	 */
	function setHeightConstrain($viewId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RVIEWS')) {
			$viewId = (int)$viewId;
			$value = (int)$value;
			$sql = "UPDATE `yg_views_properties` SET CONSTRAINHEIGHT = '$value' WHERE OBJECTID = $viewId;";
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
	 * Adds a new View to the specified parent View
	 *
	 * @param int $parentViewId View Id
	 * @param int $folder "0" for a View or "1" if a folder should be created
	 * @param int $hidden "0" for a normal View or "1" for a hidden view
	 * @return int|bool New View Id or FALSE when an error has occured
	 * @throws Exception
	 */
	function add($parentViewId, $folder = 0, $hidden = 0) {
		$parentViewId = (int)$parentViewId;
		$folder = (int)$folder;
		$rread = $this->permissions->checkInternal($this->_uid, $parentViewId, "RSUB");
		if ($rread && sUsergroups()->permissions->check($this->_uid, 'RVIEWS')) {
			$viewId = $this->tree->add($parentViewId);
			$sql = "INSERT INTO `yg_views_properties` (`OBJECTID`, `FOLDER`, `HIDDEN`, `NAME`)
			VALUES
			('$viewId', '$folder', '$hidden', 'New Type');";
			$result = sYDB()->Execute($sql);

			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->permissions->copyTo($parentViewId, $viewId);
			return $viewId;
		} else {
			return false;
		}
	}

	/**
	 * Removes the specified View
	 *
	 * @param int $viewId View Id
	 * @return bool TRUE on success or FALSE when an error has occured
	 */
	function remove($viewId) {
		if (sUsergroups()->permissions->check($this->_uid, 'RVIEWS')) {
			$viewId = (int)$viewId;

			$sql = "SELECT DISTINCT FILEID FROM `yg_views_lnk_files` WHERE VIEWID = $viewId;";
			$result = sYDB()->Execute($sql);
			$resultarray = $result->GetArray();

			$viewInfo = $this->get($viewId);
			$fileDir = getrealpath(sApp()->app_root . sApp()->filesdir);
			foreach ($resultarray as $resultitem) {
				$fileMask = $viewInfo['IDENTIFIER'] . $resultitem['FILEID'] . '-*';
				$files = glob($fileDir . '/' . $fileMask);
				foreach ($files as $file) {
					@unlink($file);
				}
			}

			$this->tree->remove($viewId);

			$sql = "DELETE FROM `yg_views_lnk_files` WHERE VIEWID = $viewId;";
			$result = sYDB()->Execute($sql);

			$sql = "DELETE FROM `yg_views_properties` WHERE OBJECTID = $viewId;";
			$result = sYDB()->Execute($sql);

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets basic information about the specified View
	 *
	 * @param int $viewId View Id
	 * @return array|false Array containing information about this File or FALSE in case of an error
	 * @throws Exception
	 */
	function get($viewId) {
		$viewId = (int)$viewId;
		if (strlen($viewId) > 0) {
			$sql = "SELECT * FROM yg_views_properties WHERE (OBJECTID = $viewId);";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
		}
		return $resultarray[0];
	}

	/**
	 * Gets basic information about the specified View (by identifier)
	 *
	 * @param string $value View identifier
	 * @return array|false Array containing information about this File or FALSE in case of an error
	 * @throws Exception
	 */
	function getByIdentifier($value) {
		$value = mysql_real_escape_string(sanitize($value));
		$sql = "SELECT * FROM `yg_views_properties` WHERE IDENTIFIER = '" . $value . "'";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return $result->GetArray();
	}

	/**
	 * Gets a List of all Views
	 *
	 * @return array|false Array of Views or FALSE in case of an error
	 * @throws Exception
	 */
	function getList() {
		$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE,  MAX(perm.RSTAGE) AS RSTAGE";
		$perm_sql_from = " LEFT JOIN yg_views_permissions AS perm ON perm.OID = group2.ID";
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
		group2.LFT, group2.RGT, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT,
		prop.*
		$perm_sql_select
		FROM
		($this->table AS group1, $this->table AS group2, yg_views_properties AS prop)
		$perm_sql_from
		WHERE
		((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
		(group2.ID = prop.OBJECTID) AND
		(prop.HIDDEN = 0)
		$perm_sql_where $filtersql_where
		GROUP BY
		group2.LFT, group2.RGT, group2.ID ORDER BY prop.NAME;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return $result->GetArray();
	}

	/**
	 * Gets a List of all hidden Views
	 *
	 * @return array|false Array of Views or FALSE in case of an error
	 * @throws Exception
	 */
	function getHiddenViews() {
		$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE,  MAX(perm.RSTAGE) AS RSTAGE";
		$perm_sql_from = " LEFT JOIN yg_views_permissions AS perm ON perm.OID = group2.ID";
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
		group2.LFT, group2.RGT, group2.ID AS VIEWID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT,
		prop.*
		$perm_sql_select
		FROM
		($this->table AS group1, $this->table AS group2, yg_views_properties AS prop)
		$perm_sql_from
		WHERE
		((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
		(group2.ID = prop.OBJECTID) AND (prop.HIDDEN = 1)
		$perm_sql_where $filtersql_where
		GROUP BY
		group2.LFT, group2.RGT, group2.ID  order by group2.LFT;";

		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$blaetter = $result->GetArray();
		$sourceView = array();
		$sourceView["VIEWID"] = 0;
		$sourceView["IDENTIFIER"] = "YGSOURCE";
		$sourceView["WIDTH"] = "0";
		$sourceView["HEIGHT"] = "0";
		$sourceView["HIDDEN"] = "0";
		$sourceView["NAME"] = "Source";
		array_unshift($blaetter, $sourceView);
		$blaetter = array_values($blaetter);
		return ($blaetter);
	}

/// @cond DEV

	/**
	 * Helper function to assign a view to the specified File
	 *
	 * @param int $viewId View-Id
	 * @param bool $generateThumbs TRUE is new thumbnails should be generated for this view
	 * @return bool TRUE if the assignment was successful or FALSE if an error has occured
	 * @throws Exception
	 */
	public function assignToFile($viewId, $generateThumbs = true) {
		if ($this->file != '') {
			$fileId = (int)$this->file->getID();
			if ($this->file->permissions->checkInternal($this->_uid, $fileId, 'RWRITE')) {
				$fileVersion = (int)$this->file->getVersion();
				$fileInfo = $this->file->get();
				$viewId = (int)$viewId;
				$views = $this->getAssigned($fileId, $fileVersion);
				for ($v = 0; $v < count($views); $v++) {
					if ($views[$v]["VIEWID"] == $viewId) {
						return true;
					}
				}
				$sql = "INSERT INTO yg_views_lnk_files SET FILEID = $fileId, FILEVERSION = $fileVersion, VIEWID = $viewId;";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				if ($generateThumbs && ($fileVersion > 1)) {
					$this->scheduleUpdate(true, $viewId);
				}
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception("call via file->views");
		}
	}

/// @endcond

	/**
	 * Assigns the specified View to a File(-folder) (recursive on folders)
	 *
	 * @param int $viewId View-Id
	 * @return bool TRUE if the assignment was successful or FALSE if an error has occured
	 * @throws Exception
	 */
	public function assign($viewId) {
		if ($this->file != '') {
			$fileId = (int)$this->file->getID();
			if ($this->file->permissions->checkInternal($this->_uid, $fileId, 'RWRITE')) {
				$fileInfo = $this->file->get();
				$folder = $fileInfo["FOLDER"];
				$viewId = (int)$viewId;
				if ($folder == 1) {
					if ($this->file->views->assignToFile($viewId)) {
						// inherit
						$fileMgr = sFileMgr();
						$children = $fileMgr->tree->get($fileId);
						for ($c = 0; $c < count($children); $c++) {
							$file = $fileMgr->getFile($children[$c]["ID"]);
							$latestVersion = $file->getLatestApprovedVersion();
							$file = $fileMgr->getFile($children[$c]["ID"], $latestVersion);
							$file->views->assignToFile($viewId);
						}
					}
				} else {
					$this->file->views->assignToFile($viewId);
				}
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception("call via file->views");
		}
	}

/// @cond DEV

	/**
	 * Helper function to unassign a view from the specified File
	 *
	 * @param int $viewId View-Id
	 * @return bool TRUE if the unassignment was successful or FALSE if an error has occured
	 * @throws Exception
	 */
	public function unassignFromFile($viewId) {
		if ($this->file != '') {
			$fileId = (int)$this->file->getID();
			if ($this->file->permissions->checkInternal($this->_uid, $fileId, 'RWRITE')) {
				$fileVersion = (int)$this->file->getVersion();
				$viewId = (int)$viewId;
				$sql = "DELETE FROM yg_views_lnk_files WHERE FILEID = $fileId AND FILEVERSION = $fileVersion AND VIEWID = $viewId";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				// remove generated thumbnails, etc.
				$fileInfo = $this->file->get();
				$viewInfo = $this->get($viewId);
				$fileMask = $viewInfo['IDENTIFIER'] . $fileInfo['OBJECTID'] . '-*' . $fileInfo['FILENAME'];
				$fileDir = getrealpath(sApp()->app_root . sApp()->filesdir);
				$files = glob($fileDir . '/' . $fileMask);
				foreach ($files as $file) {
					@unlink($file);
				}
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception("call via mo->views");
		}
	}

/// @endcond

	/**
	 * Unassigns the specified View from a File(-folder) (recursive on folders)
	 *
	 * @param int $viewId View-Id
	 * @param bool $removeFromChildren Also remove the views from all children
	 * @return bool TRUE if the assignment was successful or FALSE if an error has occured
	 * @throws Exception
	 */
	public function unassign($viewId, $removeFromChildren = false) {
		if ($this->file != '') {
			$fileId = (int)$this->file->getID();
			if ($this->file->permissions->checkInternal($this->_uid, $fileId, 'RWRITE')) {
				$viewId = (int)$viewId;
				$this->file->views->unassignFromFile($viewId);
				if ($removeFromChildren) {
					$fileMgr = sFileMgr();
					$children = $fileMgr->tree->get($fileId);
					for ($c = 0; $c < count($children); $c++) {
						$file = $fileMgr->getFile($children[$c]["ID"]);
						$latestFinalVersion = $file->getLatestApprovedVersion();
						$file = $fileMgr->getFile($children[$c]["ID"], $latestFinalVersion);
						$file->views->unassignFromFile($viewId);
					}
				}
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception("call via mo->views");
		}
	}

	/**
	 * Removes all Views from a File (from all versions of the specified File)
	 *
	 * @return bool TRUE if the removal was successful or FALSE if
	 * @throws Exception
	 */
	public function clear() {
		if ($this->file != '') {
			$fileId = (int)$this->file->getID();
			if ($this->file->permissions->checkInternal($this->_uid, $fileId, 'RWRITE')) {
				$sql = "DELETE FROM yg_views_lnk_files WHERE FILEID = $fileId;";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				// remove generated thumbnails, etc.
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception("call via mo->views");
		}
	}

	/**
	 * Gets all assigned Views from a File
	 *
	 * @return array|false Array of Views
	 * @throws Exception
	 */
	public function getAssigned() {
		if ($this->file != '') {
			$fileId = (int)$this->file->getID();
			$fileVersion = (int)$this->file->getVersion();

			$fileinfo = $this->file->get();
			$sql = "SELECT lnk.VIEWID, lnk.VIEWID AS ID, prop.*
			FROM yg_views_lnk_files AS lnk
			LEFT JOIN yg_views_properties AS prop ON prop.OBJECTID = lnk.VIEWID
			WHERE lnk.FILEID = $fileId AND lnk.FILEVERSION = $fileVersion AND prop.HIDDEN = 0 ORDER BY prop.NAME";

			$result = sYDB()->Execute($sql);

			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$views = $result->GetArray();

			if ($fileinfo["FOLDER"] == 0 && $includeYGSOURCE = true) {
				$sourceView = array();
				$sourceView["VIEWID"] = 0;
				$sourceView["VIEWTYPE"] = FILE_TYPE_WEBIMAGE;
				$sourceView["ID"] = 0;
				$sourceView["IDENTIFIER"] = "YGSOURCE";
				$sourceView["WIDTH"] = "0";
				$sourceView["HEIGHT"] = "0";
				$sourceView["HIDDEN"] = "0";
				$sourceView["NAME"] = "Source";
				array_unshift($views, $sourceView);
				$views = array_values($views);
			}
			return ($views);
		} else {
			throw new Exception("call via mo->views");
		}
	}

	/**
	 * Copies all assigned Views from a File to another
	 *
	 * @param int $fromFileId File-Id to copy the Views from
	 * @param int $fromFileVersion File-Version to copy the Views from
	 * @param int $toFileId File-Id to copy the Views to
	 * @param int $toFileVersion File-Version to copy the Views to
	 *
	 * @return bool TRUE on success or FALSE if an error has occured
	 * @throws Exception
	 */
	public function copyTo($fromFileId, $fromFileVersion, $toFileId, $toFileVersion) {
		if ($this->file != "") {
			$fromFileId = (int)$fromFileId;
			$fromFileVersion = (int)$fromFileVersion;
			$sourceFile = sFileMgr()->getFile($fromFileId, $fromFileVersion);
			$toFileId = (int)$toFileId;
			$toFileVersion = (int)$toFileVersion;
			$toFile = sFileMgr()->getFile($toFileId, $toFileVersion);
			if ($toFileId == 0) {
				return false;
			}
			$views = $sourceFile->views->getAssigned();
			for ($v = 0; $v < count($views); $v++) {
				$toFile->views->assign($views[$v]["VIEWID"]);
			}
			return true;
		} else {
			throw new Exception("call via mo->views");
		}
		return true;
	}

	/**
	 * Copies all generated View Infos from a File to another
	 *
	 * @param int $fromFileVersion File-Version to copy the Views from
	 * @param int $toFileVersion File-Version to copy the Views to
	 * @param int $sourceFileId (optional) File-Id of the source File
	 *
	 * @return bool TRUE on success or FALSE if an error has occured
	 */
	public function copyGeneratedViewInfo($fromFileVersion, $toFileVersion, $sourceFileId = 0) {
		if (($this->file) || $sourceFileId) {
			if ($sourceFileId) {
				$sourceFile = sFileMgr()->getFile($sourceFileId, $fromFileVersion);
				$sourceFileInfo = $sourceFile->get();
			} else {
				$sourceFileInfo = $this->file->get();
			}
			$targetFileInfo = $this->file->get();
			if ($targetFileInfo['OBJECTID']) {
				$sql = "INSERT INTO `yg_views_generated`
				SELECT NULL, " . $targetFileInfo['OBJECTID'] . ", " . $toFileVersion . ", VIEWID, WIDTH, HEIGHT, TYPE
				FROM `yg_views_generated`
				WHERE FILEID = " . $sourceFileInfo['OBJECTID'] . " AND FILEVERSION = " . $fromFileVersion . ";";
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
	 * Updates all Views
	 *
	 * @param bool $hidden If TRUE updates hidden views
	 *
	 * @return bool TRUE on success or FALSE if an error has occured
	 */
	public function updateViews($hidden = false) {
		if ($this->file != '') {
			$fileId = (int)$this->file->getID();
			if ($this->file->permissions->checkInternal($this->_uid, $fileId, 'RWRITE')) {
				if ($hidden === true) {
					$views = $this->getHiddenViews();
				} else {
					$views = $this->getAssigned();
				}
				$fileinfo = $this->file->get();
				$procs = sApp()->files_procs;
				$ygsource = 0;

				$procPathInternal = getcwd()."/".sConfig()->getVar("CONFIG/DIRECTORIES/FILES_PROCS");
				$procPath = getcwd()."/".sConfig()->getVar("CONFIG/DIRECTORIES/PROCESSORSDIR");

				for ($t = 0; $t < count($views); $t++) {
					$fileproc = $this->file->filetypes->getProcessor($fileinfo['FILETYPE']);
					for ($p = 0; $p < count($procs); $p++) {
						if ($procs[$p]["name"] == $fileproc || ($procs[$p]["name"] == "YGSOURCE" && $ygsource == 0)) {
							$view = $views[$t];
							if (($procs[$p]["name"] != "YGSOURCE") && $view["IDENTIFIER"] == "YGSOURCE") {
								continue;
							}
							$ygsource++;

							if (file_exists(getrealpath($procPathInternal.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php"))) {
								require_once(getrealpath($procPathInternal.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php"));
							} elseif (file_exists(getrealpath($procPath.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php"))) {
								require_once(getrealpath($procPath.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php"));
							} else {
								continue;
							}
							$classname = (string)$procs[$p]["classname"];
							$namespace = (string)$procs[$p]["namespace"];
							if (strlen($namespace)) {
								$classname = $namespace."\\".$classname;
							}
							$moduleclass = new $classname();

							$params = array();
							$params["FILEINFO"] = $fileinfo;
							$params["VIEW"] = $view;

							$moduleclass->process($fileinfo['OBJECTID'], $params);
						}
					}
				}
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception("call via file->views");
		}
	}

	/**
	 * Schedules an Update for all Views
	 *
	 * @param bool $hidden (optional) If FALSE schedules no hidden views
	 *
	 * @return bool TRUE on success or FALSE if an error has occured
	 */
	public function scheduleUpdate($hidden = true, $viewId = false) {
		if ($this->file != '') {
			$fileId = (int)$this->file->getID();
			if ($this->file->permissions->checkInternal($this->_uid, $fileId, 'RWRITE')) {
				$views = $this->getAssigned();
				if ($hidden) {
					$hiddenviews = $this->getHiddenViews();
					$views = array_merge($views, $hiddenviews);
				}
				$fileinfo = $this->file->get();
				$procs = sApp()->files_procs;
				$fileproc = $this->file->filetypes->getProcessor($fileinfo['FILETYPE']);

				for ($p = 0; $p < count($procs); $p++) {
					if ($procs[$p]['name'] == $fileproc) {
						for ($v = 0; $v < count($views); $v++) {
							if (($viewId && ($views[$v]["ID"] != $viewId)) || ($views[$v]["IDENTIFIER"] == "YGSOURCE")) continue;
							$params = array(
								'FILEINFO' => $fileinfo,
								'VIEW' => $views[$v]
							);
							$this->file->scheduler->schedule($this->file->getID(), $procs[$p]['dir'], time(), $params);
						}
					}
				}
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception("call via file->views");
		}
	}

	/**
	 * Adds generated View information for a specific View
	 *
	 * @param int $viewId View Id
	 * @param int $width Width
	 * @param int $height Height
	 * @param int $type Filetype
	 *
	 * @return bool TRUE on success or FALSE if an error has occured
	 */
	public function addGenerated($viewId, $width, $height, $type) {

		$viewId = (int)$viewId;
		$width = (int)$width;
		$height = (int)$height;
		$type = (int)$type;
		$fileID = $this->file->getID();

		//error_log($fileID."-".$viewId.":".$type."\r\n",3,'/tmp/erlog.log');

		if ($this->file != '') {
			$fileId = (int)$this->file->getID();
			if ($this->file->permissions->checkInternal($this->_uid, $fileId, 'RWRITE')) {
				$fileVersion = (int)$this->file->getVersion();

				$sql = "SELECT * FROM yg_views_generated WHERE FILEID = $fileID AND FILEVERSION = $fileVersion AND VIEWID = $viewId";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$generated = $result->GetArray();
				if (count($generated) > 0) {
					$sql = "UPDATE yg_views_generated SET HEIGHT = $height, WIDTH = $width, TYPE = $type WHERE FILEID = $fileID AND FILEVERSION = $fileVersion AND VIEWID = $viewId";
					$result = sYDB()->Execute($sql);
					if ($result === false) {
						throw new Exception(sYDB()->ErrorMsg());
					}
				} else {
					$sql = "INSERT INTO `yg_views_generated` (`FILEID`, `FILEVERSION`, `VIEWID`, `WIDTH`, HEIGHT, TYPE)
					VALUES
					( $fileID, $fileVersion, $viewId, $width, $height, $type);";
					$result = sYDB()->Execute($sql);
					if ($result === false) {
						throw new Exception(sYDB()->ErrorMsg());
					}
				}
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception("call via file->views");
		}
	}

	/**
	 * Gets generated View information for a specific View
	 *
	 * @param int $viewId View Id
	 *
	 * @return array View information
	 */
	public function getGeneratedViewInfo($viewId) {
		$viewId = (int)$viewId;
		$fileId = (int)$this->file->getID();
		$fileInfo = $this->file->get();

		$sql = "SELECT * FROM yg_views_generated WHERE (FILEID = $fileId AND FILEVERSION = " . $fileInfo['VIEWVERSION'] . " AND VIEWID = $viewId)";
		$result = sYDB()->Execute($sql);

		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();

		return $resultarray;
	}

	/**
	 * Gets dimensions for a specific View
	 *
	 * @param int $viewId View Id
	 *
	 * @return array View dimensions
	 */
	public function getDimensions($viewId) {
		$viewId = (int)$viewId;
		$fileId = (int)$this->file->getID();

		$fileInfo = $this->file->get();
		$viewInfo = $this->get($viewId);
		$viewIdentifier = $viewInfo['IDENTIFIER'];
		if ($viewIdentifier == 'YGSOURCE') {
			$viewIdentifier = '';
		}
		$filedir = sApp()->approot.sApp()->filesdir;
		$filename = $viewIdentifier.$fileInfo['OBJECTID'].'-'.$fileInfo['VIEWVERSION'].$fileInfo['FILENAME'];

		$filepath = getrealpath($filedir.$filename);
		$filedimensions = getimagesize($filepath);

		if (is_array($filedimensions)) {
			return array(
				'WIDTH' => $filedimensions[0],
				'HEIGHT' => $filedimensions[1]
			);
		}
		return false;
	}

	/**
	 * Gets filesize for a specific View
	 *
	 * @param int $viewId View Id
	 *
	 * @return array View filesize
	 */
	public function getFilesize($viewId) {
		$viewId = (int)$viewId;
		$fileId = (int)$this->file->getID();

		$fileVersion = (int)$this->file->getVersion();
		$fileInfo = $this->file->get();
		$viewInfo = $this->get($viewId);
		$viewIdentifier = $viewInfo['IDENTIFIER'];
		if ($viewIdentifier == 'YGSOURCE') {
			$viewIdentifier = '';
		}
		$filedir = sApp()->approot.sApp()->filesdir;
		$filename = $viewIdentifier.$fileInfo['OBJECTID'].'-'.$fileInfo['VIEWVERSION'].$fileInfo['FILENAME'];

		$filepath = getrealpath($filedir.$filename);
		$filesize = (int)filesize($filepath);

		return $filesize;
	}

}

?>