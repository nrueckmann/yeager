<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The File class, which represents an instance of a File.
 */
class File extends Versionable {
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
	public $comments;
	public $scheduler;

	private $propertyFields = array();

	/**
	 * Constructor of the File class
	 *
	 * @param int $fileId File Id
	 * @param int $version Version
	 */
	public function __construct($fileId = 0, $version = 0) {
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->_id = $fileId;
		$this->initTables();
		$this->permissions = new Permissions($this->_table_permissions, $this);
		parent::__construct($this->_id, $version, $this->_table_object, $this->_table_tree, $this->permissions);
		$this->history = new History($this, HISTORYTYPE_FILE, $this->permissions);
		$this->tags = new Tags($this);
		$this->comments = new Comments($this);
		$this->properties = new Properties($this->_table_properties, $this->getPropertyId(), $this);
		$this->scheduler = new Scheduler($this->_table_scheduler, SCHEDULER_FILE);
		$this->views = new Views($this);
		$this->filetypes = new Filetypes();
	}

/// @cond DEV

	/**
	 * Initializes internal class members
	 */
	private function initTables() {
		$this->_table_object = "yg_files_properties";
		$this->_table_permissions = "yg_files_permissions";
		$this->_table_history = "yg_history";
		$this->_table_tree = "yg_files_tree";
		$this->_table_properties = "yg_files_props";
		$this->_table_taglinks = "yg_tags_lnk_files";
		$this->_table_commentlinks = "yg_comments_lnk_files";
		$this->_table_scheduler = "yg_cron";
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the object prefix, used for table names in database queries
	 *
	 * @return string Objectprefix
	 */
	function getObjectPrefix() {
		return "files";
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
		return "yg_files_properties";
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table which contains links between Comments and Files
	 *
	 * @return string Tablename
	 */
	function getCommentsLinkTable() {
		return $this->_table_commentlinks;
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table which contains the permissions for Files
	 *
	 * @return string Tablename
	 */
	function getPermissionsTable() {
		return $this->_table_permissions;
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
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
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
		return "yg_files_tree";
	}

/// @endcond

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

	/**
	 * Generates a new version of this File by copying the currently instanced version
	 * and updates the currently instanced Object to the new version
	 *
	 * @return int|false New version of this File or FALSE in case of an error
	 */
	public function newVersion() {
		$fileID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $fileID, "RWRITE")) {
			$sourceVersion = $this->getVersion();
			$sourceObject = sFileMgr()->getFile($this->_id, $sourceVersion);
			if ($sourceVersion == $this->getLatestVersion()) {
				$historyIdentifier = 'TXT_FILE_H_NEWVERSION';
				$historySourceVersion = NULL;
			} else {
				$historyIdentifier = 'TXT_FILE_H_NEWVERSION_FROM';
				$historySourceVersion = $sourceVersion;
			}
			$newVersion = parent::newVersion();
			$this->properties = new Properties($this->_table_properties, $this->getPropertyId(), $this);
			$this->tags->copyTo($fileID, $sourceVersion, $fileID, $newVersion);
			$this->views->copyTo($fileID, $sourceVersion, $fileID, $newVersion, false);
			$this->views->copyGeneratedViewInfo($sourceVersion, $newVersion);
			$this->approveVersion($newVersion);
			$this->copyExtensionsFrom($sourceObject);

			sFileMgr()->callExtensionHook('onVersionNew', (int)$this->_id, $this->getVersion());

			// Add to history
			$newVersion = $this->getLatestApprovedVersion();
			$this->history->add(HISTORYTYPE_FILE, $historySourceVersion, $newVersion, $historyIdentifier);

			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/FILE_PUBLISH") == "true") {
				Singleton::FC()->emptyBucket();
			}

			return $newVersion;
		} else {
			return false;
		}
	}

	/**
	 * Copies Properties, Extensions, Permissions, Views, Tags from another File into this File
	 *
	 * @param object $sourceObject Source File object
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function copyFrom(&$sourceObject) {
		$sourceID = $sourceObject->getID();
		$sourceVersion = $sourceObject->getVersion();
		$sourceInfo = $sourceObject->get();
		$targetID = (int)$this->_id;
		$targetVersion = $this->getVersion();
		$targetInfo = $this->get();
		$targetThumbVersion = ((int)$targetInfo['VIEWVERSION']) + 1;
		$filedir = sConfig()->getVar('CONFIG/DIRECTORIES/FILESDIR') . "/";
		parent::copyFrom($sourceObject);

		$this->setFileType($sourceInfo['FILETYPE']);
		$this->setFilename($sourceInfo['FILENAME']);
		$this->setFilesize($sourceInfo['FILESIZE']);

		if ($sourceInfo["FOLDER"] == 0) {
			$fprefix = $sourceObject->getID() . "-" . $sourceInfo["VIEWVERSION"];
			$filename = $sourceInfo["FILENAME"];
			$tmpfile = $filedir . $fprefix . $filename;
			copy($tmpfile, $filedir . $targetID . "-" . $targetThumbVersion . $sourceInfo["FILENAME"]);
			$tmpfile = tempnam();
			copy($filedir . $fprefix . $filename, $tmpfile);
			$this->updateFile($filename, $sourceInfo["FILETYPE"], $tmpfile, false);
			unlink($tmpfile);
		}
		$this->setViewVersion($targetThumbVersion);
		$this->views->copyTo($sourceID, $sourceVersion, $targetID, $this->getLatestApprovedVersion());
		$this->views->copyGeneratedViewInfo($sourceVersion, $targetVersion, $sourceID);
		$this->views->scheduleUpdate();
		$this->copyExtensionsFrom($sourceObject);
	}

	/**
	 * Copies Extensions from another File to this File
	 *
	 * @param object $sourceObject Source File object
	 */
	function copyExtensionsFrom(&$sourceObject) {
		$sourceId = $sourceObject->getID();
		$sourceVersion = $sourceObject->getVersion();
		$targetId = $this->getID();
		$targetVersion = $this->getVersion();
		$extensions = new ExtensionMgr(sYDB(), $this->_uid);
		$all_file_extensions = $extensions->getList(EXTENSION_FILE, true);
		foreach ($all_file_extensions as $all_file_extension) {
			$extension = $extensions->getExtension($all_file_extension['CODE']);
			if ($extension && ($extension->usedByFile($sourceId, $sourceVersion) === true)) {
				if ($extension->usedByFile($targetId, $targetVersion) !== true) {
					$newfid = $extension->addToFileInternal($targetId, $targetVersion);
				}
				$extension = $extensions->getExtension($all_file_extension['CODE'], $targetId, $targetVersion);
				$sourceext = $extensions->getExtension($all_file_extension['CODE'], $sourceId, $sourceVersion);
				if ($extension && $sourceext) {
					$newfid = $extension->getPropertyId();
					$oldfid = $sourceext->getPropertyId();
					$extension->properties->copyTo($oldfid, $newfid);
				}
			}
		}
	}

	/**
	 * Copies Views from another File to this File
	 *
	 * @param object $sourceObject Source File object
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function copyViewsFrom(&$sourceObject) {
		$sourceID = $sourceObject->getID();
		$sourceVersion = $sourceObject->getVersion();
		$targetID = (int)$this->_id;
		$targetVersion = $this->getVersion();
		if ($this->permissions->checkInternal($this->_uid, $targetID, "RWRITE")) {
			if ($sourceObject->views->copyTo($sourceID, $sourceVersion, $targetID, $targetVersion, true)) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets basic information about this File
	 *
	 * @return array|false Array containing information about this File or FALSE in case of an error
	 */
	public function get() {
		$fileID = $this->_id;
		$version = (int)$this->getVersion();
		if ($fileID < 1) {
			return false;
		}
		if (strlen($version) < 1) {
			return false;
		}
		$sql = "SELECT * FROM yg_files_props WHERE READONLY = 1;";
		$ra = $this->cacheExecuteGetArray($sql);
		$dynIdentifers = array();
		foreach ($ra as $raItem) {
			$dynIdentifers[] = "\npv.`" . $raItem['IDENTIFIER'] . '` AS `' . $raItem['IDENTIFIER'] . '`';
		}
		$dynIdentifers = implode(', ', $dynIdentifers);
		if ($this->permissions->checkInternal($this->_uid, $fileID, "RREAD")) {
			$sql = "SELECT
						p.ID AS ID,
						p.OBJECTID AS OBJECTID,
						p.FOLDER AS FOLDER,
						p.FILENAME AS FILENAME,
						RIGHT(p.FILENAME,3) AS EXTENSION,
						p.FILETYPE AS FILETYPE,
						p.FILESIZE AS FILESIZE,
						p.VERSION AS VERSION,
						p.LOCKED AS LOCKED,
						p.CREATEDTS AS CREATEDTS,
						p.CHANGEDTS AS CHANGEDTS,
						p.FILETS AS FILETS,
						p.VIEWVERSION AS VIEWVERSION,
						p.CREATEDBY AS CREATEDBY,
						p.CHANGEDBY AS CHANGEDBY,
						p.DELETED AS DELETED,
						t.LEVEL AS LEVEL,
						t.PARENT AS PARENT,
						t.PNAME AS PNAME,
						types.NAME AS TYPENAME,
						types.CODE AS CODE,
						types.IDENTIFIER AS IDENTIFIER,
						types.COLOR AS COLOR,
						$dynIdentifers
					FROM
						(yg_files_properties AS p, yg_files_tree AS t, yg_filetypes_properties AS types)
					LEFT JOIN
						yg_files_propsv AS pv ON pv.OID = p.ID
					WHERE
						(p.OBJECTID = $fileID) AND
						(p.VERSION = $version) AND
						(p.OBJECTID = t.ID) AND
						(types.OBJECTID = p.FILETYPE);";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra[0];
		} else {
			return false;
		}
	}

	/**
	 * Sets the permanent name of this File
	 *
	 * @param string $pname Permanent name
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function setPName($pname) {
		$fileID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $fileID, "RWRITE")) {
			$pname = sYDB()->escape_string($this->filterPName($pname));

			if (is_numeric($pname)) {
				return false;
			}

			$checkpinfo = sFileMgr()->getFileIdByPname($pname);
			if (($checkpinfo["ID"] != $fileID) && ($checkpinfo["ID"] > 0)) {
				$pname = $pname . $fileID;
			}

			$sql = "SELECT PNAME AS STATE FROM yg_files_tree WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $fileID);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			$sql = "UPDATE yg_files_tree SET PNAME = ? WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $pname, $fileID);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Calculates a unique permanent name for this File
	 *
	 * @param string $iteration (optional) Iteration
	 * @return string Permanent name
	 */
	public function calcPName($iteration = '') {
		$fileID = $this->_id;
		$pinfo = $this->get();
		$filename = $pinfo["NAME"];
		if ((int)sConfig()->getVar("CONFIG/CASE_SENSITIVE_URLS") == 0) {
			$filename = strtolower($filename);
		}
		$pname = $this->filterPName($filename);
		if (is_numeric($pname)) {
			$pname = 'file_'.$pname;
		}
		if ($iteration != '') {
			$checkpinfo = sFileMgr()->getFileIdByPname($pname . '_' . $iteration);
		} else {
			$checkpinfo = sFileMgr()->getFileIdByPname($pname);
		}
		if ($checkpinfo["ID"] == $fileID) {
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
	 * Moves this File to the trashcan
	 *
	 * @return array Array with all elements which were successfully deleted
	 */
	function delete() {
		$fileID = (int)$this->_id;
		$rootNode = sFileMgr()->tree->getRoot();
		if ($fileID == $rootNode) {
			return array();
		}

		// Check if object is a folder
		$successNodes = array();
		$currFile = sFileMgr()->getFile($fileID);
		$fileInfo = $currFile->get();
		if ($fileInfo['FOLDER'] == 1) {
			$subNodes = sFileMgr()->getList($fileID, array('SUBNODES'), 'group2.LFT', 1000);
			if (count($subNodes) > 0) {
				foreach($subNodes as $subNode) {
					$file = sFileMgr()->getFile($subNode['ID']);
					$successfullyDeleted = $file->delete();
					if (in_array($subNode['ID'], $successfullyDeleted) === true) {
						foreach($successfullyDeleted as $successfullyDeletedItem) {
							$successNodes[] = $successfullyDeletedItem;
						}
					}
				}
			}
		}

		if ($this->permissions->checkInternal($this->_uid, $fileID, "RDELETE")) {
			// Move to root level
			sFileMgr()->tree->moveTo($fileID, $rootNode);

			$sql = "UPDATE yg_files_properties SET DELETED = 1 WHERE OBJECTID = ?;";
			sYDB()->Execute($sql, $fileID);

			$successNodes[] = $fileID;
			sFileMgr()->callExtensionHook('onDelete', (int)$this->_id, (int)$this->_version);
		}

		if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/FILE_DELETE") == "true") {
			Singleton::FC()->emptyBucket();
		}

		return $successNodes;
	}

	/**
	 * Restores this File from the trashcan
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function undelete() {
		$fileID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $fileID, "RDELETE")) {
			$sql = "UPDATE yg_files_properties SET DELETED = 0 WHERE OBJECTID = ?";
			sYDB()->Execute($sql, $fileID);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets Filetype of this File
	 *
	 * @param int $type Filetype Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function setFileType($type) {
		$mo = (int)$this->_id;
		$type = (int)$type;
		if ($this->permissions->checkInternal($this->_uid, $mo, "RWRITE")) {
			$version = (int)$this->getVersion();
			$sql = "UPDATE yg_files_properties SET FILETYPE = ? WHERE (OBJECTID = ?) AND VERSION = ?;";
			$result = sYDB()->Execute($sql, $type, $mo, $version);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets Filename of this File
	 *
	 * @param string $filename File name
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function setFilename($filename) {
		$mo = (int)$this->_id;
		$filename = sYDB()->escape_string(sanitize($filename));

		if ($this->permissions->checkInternal($this->_uid, $mo, "RWRITE")) {
			$version = (int)$this->getVersion();
			$sql = "UPDATE yg_files_properties SET FILENAME = ? WHERE (OBJECTID = ?) AND VERSION = ?;";
			$result = sYDB()->Execute($sql, $filename, $mo, $version);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets filesize of this File
	 *
	 * @param int $filesize Filesize
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function setFilesize($filesize) {
		$mo = (int)$this->_id;
		$filesize = (int)$filesize;

		if ($this->permissions->checkInternal($this->_uid, $mo, "RWRITE")) {
			$version = (int)$this->getVersion();
			$sql = "UPDATE yg_files_properties SET FILESIZE = ? WHERE (OBJECTID = ?) AND VERSION = ?;";
			$result = sYDB()->Execute($sql, $filesize, $mo, $version);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets version of the Views of this File
	 *
	 * @param int $version View version
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function setViewVersion($version) {
		$mo = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $mo, "RWRITE")) {
			$version = (int)$version;
			if (!$version) $version = (int)$this->getVersion();
			$sql = "UPDATE yg_files_properties SET VIEWVERSION = ? WHERE (OBJECTID = ?) AND VERSION = ?;";
			$result = sYDB()->Execute($sql, $version, $mo, $version);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
		} else {
			return false;
		}
	}

	/**
	 * Updates filename, filetype and source file of this File
	 *
	 * @param string $filename New filename
	 * @param int $filetypeId New Filetype Id
	 * @param $tmpfile Temporary file
	 * @param $produceNewVersion (optional) TRUE if a new version should be produced
	 * @return int|false New File version or FALSE in case of an error
	 */
	public function updateFile($filename, $filetypeId, $tmpfile, $produceNewVersion = true) {
		if ($this->permissions->checkInternal($this->_uid, $this->getID(), "RWRITE")) {
			if ($produceNewVersion == true) {
				$newVersion = $this->newVersion();
			} else {
				$newVersion = $this->getVersion();
			}
			$filetypeId = (int)$filetypeId;
			if ($filetypeId == 0) {
				return false;
			}
			if (!file_exists($tmpfile)) {
				return false;
			}
			$filedir = sConfig()->getVar('CONFIG/DIRECTORIES/FILESDIR') . "/";
			$fprefix = $this->getID() . "-" . $newVersion;
			copy($tmpfile, $filedir . $fprefix . $filename);
			unlink($tmpfile);
			$fileSize = filesize($filedir . $fprefix . $filename);
			$this->setFilename($filename);
			$this->setFileSize($fileSize);
			$this->setFileType($filetypeId);
			$this->setViewVersion($newVersion);
			$this->views->scheduleUpdate();
			sFileMgr()->callExtensionHook('onUpdate', (int)$this->_id, $newVersion);
			return $newVersion;
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
	 * Gets the URL of this File
	 *
	 * @return string URL of this File
	 */
	function getUrl() {
		$fileID = $this->_id;
		if ($this->permissions->checkInternal($this->_uid, $fileID, "RREAD")) {
			$info = $this->get();
			$url = sApp()->webroot."download/".$info['PNAME']."/";
			return $url;
		} else {
			return false;
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
						$this->publishVersion($latestfinal);
					} else {
						$this->publishVersion($params["VERSION"]);
					}
					$this->scheduler->removeJob($todo[$i]["ID"]);
					break;
			}
		}
	}
}

?>