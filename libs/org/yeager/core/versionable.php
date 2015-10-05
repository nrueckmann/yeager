<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The Versionable class, which represents an abtract of versionable Objects.
 */
abstract class Versionable {
	private $_db;
	private $_table;
	private $_treetable;
	private $_uid;
	private $_lockToken;

	protected $_id;
	protected $_version;

	public $permissions;
	public $_property_id;

	/**
	 * Constructor of the Versionable class
	 *
	 * @param int $id Object Id
	 * @param int $version Version
	 * @param string $table Object table
	 * @param string $treetable Tree table
	 * @param object $permissions Permissions Object (optional)
	 */
	public function __construct($id, $version, $table, $treetable, $permissions = null) {
		$this->_id = (int)$id;
		$this->_table = $table;
		$this->_treetable = $treetable;
		$this->_uid = sUserMgr()->getCurrentUserID();
		$this->_property_id = 0;
		$this->permissions = &$permissions;
		$this->_version = (int)$version;
		if (($this->_version == 0) || ($version == ALWAYS_LATEST_APPROVED_VERSION)) {
			$this->_version = $this->getLatestVersion();
		}
	}

	/**
	 * Gets the Object Id of the current instance
	 *
	 * @return int|false Object Id or FALSE if permission-check for RREAD fails
	 */
	public function getID() {
		$objectid = (int)$this->_id;
		if ($this->permissions) {
			if (!$this->permissions->checkInternal($this->_uid, $objectid, "RREAD")) {
				return false;
			}
		}
		return $objectid;
	}

	/**
	 * Gets the version of the Object instance
	 *
	 * @return int|false Version or FALSE if permission-check for RREAD fails
	 */
	public function getVersion() {
		$objectid = (int)$this->_id;
		if ($this->permissions) {
			if (!$this->permissions->checkInternal($this->_uid, $objectid, "RREAD")) {
				return false;
			}
		}
		return $this->_version;
	}

	/**
	 * Gets the latest version of the Object instance
	 *
	 * @return int|false Version or FALSE if permission-check for RREAD fails
	 * @throws Exception
	 */
	public function getLatestVersion() {
		$objectid = (int)$this->_id;
		if ($this->permissions) {
			if (!$this->permissions->checkInternal($this->_uid, $objectid, "RREAD")) {
				return false;
			}
		}
		$sql = "SELECT MAX(VERSION) AS VERSION FROM " . $this->_table . " WHERE OBJECTID = $objectid;";
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
		}
		$ra = $dbr->GetArray();
		return $ra[0]["VERSION"];
	}

	/**
	 * Gets the latest approved version of the Object instance
	 *
	 * @return int|false Version or FALSE if permission-check for RREAD fails
	 * @throws Exception
	 */
	public function getLatestApprovedVersion() {
		$objectid = (int)$this->_id;
		if ($this->permissions) {
			if (!$this->permissions->checkInternal($this->_uid, $objectid, "RREAD")) {
				return false;
			}
		}
		$sql = "SELECT MAX(VERSION) AS VERSION FROM " . $this->_table . " WHERE OBJECTID = $objectid AND APPROVED = 1;";
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
		}
		$ra = $dbr->GetArray();
		return $ra[0]["VERSION"];
	}

	/**
	 * Gets the first approved version of the Object instance
	 *
	 * @return int|false Version or FALSE if permission-check for RREAD fails
	 * @throws Exception
	 */
	public function getFirstApprovedVersion() {
		$objectid = (int)$this->_id;
		if ($this->permissions) {
			if (!$this->permissions->checkInternal($this->_uid, $objectid, "RREAD")) {
				return false;
			}
		}
		$sql = "SELECT MIN(VERSION) AS VERSION FROM " . $this->_table . " WHERE OBJECTID = $objectid AND APPROVED = 1;";
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
		}
		$ra = $dbr->GetArray();
		return $ra[0]["VERSION"];
	}

	/**
	 * Gets the currently published version of the Object instance
	 *
	 * @param bool (optional) TRUE if always the specific published version should be returned FALSE if 999999 (ALWAYS_LATEST_APPROVED_VERSION) should be returned in case the Object is automatically published on approval
	 *
	 * @return int|false Version or FALSE if permission-check for RREAD fails
	 * @throws Exception
	 */
	public function getPublishedVersion($realVersion = false) {
		$objectid = (int)$this->_id;
		if ($this->permissions) {
			if (!$this->permissions->checkInternal($this->_uid, $objectid, "RREAD")) {
				return false;
			}
		}
		$sql = "SELECT `VERSIONPUBLISHED` FROM " . $this->_treetable . " WHERE `ID` = $objectid;";
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
		}
		$ra = $dbr->GetArray();
		if (($realVersion) && ($ra[0]["VERSIONPUBLISHED"] == ALWAYS_LATEST_APPROVED_VERSION)) {
			return $this->getLatestApprovedVersion();
		}
		return $ra[0]["VERSIONPUBLISHED"];
	}

	/**
	 * Checks if the published version of the Object instance is 999999 (ALWAYS_LATEST_APPROVED_VERSION) and
	 * thus the latest approved version of the Object will get published automatically on approval.
	 *
	 * @return bool TRUE if the published version of the object is set to 999999 (ALWAYS_LATEST_APPROVED_VERSION) FALSE if not
	 * @throws Exception
	 */
	public function isAutoPublished() {
		$objectid = (int)$this->_id;
		$sql = "SELECT `VERSIONPUBLISHED` FROM " . $this->_treetable . " WHERE `ID` = $objectid;";
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
		}
		$ra = $dbr->GetArray();
		if ($ra[0]["VERSIONPUBLISHED"] == ALWAYS_LATEST_APPROVED_VERSION) {
			return true;
		}
		return false;
	}

	/**
	 * Gets all versions of the Object instance
	 *
	 * @return int|false Version or FALSE if permission-check for RREAD fails
	 * @throws Exception
	 */
	public function getVersions() {
		$objectid = (int)$this->_id;
		if ($this->permissions) {
			if (!$this->permissions->checkInternal($this->_uid, $objectid, "RREAD")) {
				return false;
			}
		}
		$sql = "SELECT VERSION, APPROVED, CREATEDBY, CHANGEDBY, CHANGEDTS FROM " . $this->_table . " WHERE OBJECTID = $objectid ORDER BY VERSION DESC;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $ra;
	}

	/**
	 * Approves the current/specified version of the Object instance
	 *
	 * @param int $version Version (optional)
	 * @return int|false Version or FALSE if permission-check for RREAD fails
	 * @throws Exception
	 */
	public function approveVersion($version = 0) {
		$objectid = (int)$this->_id;
		$version = (int)$version;

		if ($version == 0) {
			$version = (int)$this->getVersion();
		}


		if ($this->permissions) {
			if (!$this->permissions->checkInternal($this->_uid, $objectid, "RSTAGE")) {
				return false;
			}
		}

		$sql = "UPDATE " . $this->_table . " SET APPROVED = 1 WHERE (OBJECTID = $objectid) AND (VERSION = $version);";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		return true;
	}

	/**
	 * Sets the specified version of the Object instance to "published"
	 *
	 * @param int $version (optional) Specific version or ALWAYS_LATEST_APPROVED_VERSION (constant) to always publish the lastest approved version
	 * @return int|false Version or FALSE if permission-check for RREAD fails
	 * @throws Exception
	 */
	public function publishVersion($version) {
		$objectid = (int)$this->_id;
		$version = (int)$version;

		if ($this->permissions) {
			if (!$this->permissions->checkInternal($this->_uid, $objectid, "RSTAGE")) {
				return false;
			}
		}

		// Check if version to be published is already approved
		$sql = "SELECT `APPROVED` FROM " . $this->_table . " WHERE `OBJECTID` = $objectid AND `VERSION` = " . $version . ";";
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
		}
		$ra = $dbr->GetArray();
		if (!$ra[0]['APPROVED'] && ($version != ALWAYS_LATEST_APPROVED_VERSION)) {
			return false;
		}

		$sql = "UPDATE " . $this->_treetable . " SET VERSIONPUBLISHED = '$version' WHERE (ID = $objectid);";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		return true;
	}

	/**
	 * Generates a new version of this Object by copying the currently instanced version
	 * and updates the currently instanced Object to the new version
	 *
	 * @return int|false New version of this Object or FALSE in case of an error
	 * @throws Exception
	 */
	public function newVersion() {
		$sourceVersion = $this->getVersion();
		$objectid = (int)$this->_id;

		if ($this->permissions) {
			if (!$this->permissions->checkInternal($this->_uid, $objectid, "RWRITE")) {
				return false;
			}
		}

		$changedts = time();
		$newVersion = $this->getLatestVersion() + 1;

		$sql = "SELECT * FROM (" . $this->_table . ") WHERE (OBJECTID = $objectid AND VERSION = $sourceVersion);";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		if (count($ra) < 1) {
			return false;
		}

		$ra = $ra[0];
		unset($ra["ID"]);
		$ra["VERSION"] = $newVersion;
		$ra["CHANGEDTS"] = $changedts;
		$ra["APPROVED"] = 0;
		$ra["CHANGEDBY"] = $this->_uid;
		$ra["HASCHANGED"] = 0;

		$props = array_keys($ra);
		$sql = "INSERT INTO " . $this->_table . " (";
		for ($p = 0; $p < count($props); $p++) {
			if (is_string($props[$p])) { // workaround php long <-> string comparison bug
				$sql .= $props[$p] . ",";
			}
		}
		$sql .= ") VALUES (";
		for ($p = 0; $p < count($props); $p++) {
			if (is_string($props[$p])) { // workaround php long <-> string comparison bug
				$sql .= "'" . $ra[$props[$p]] . "',";
			}
		}
		$sql .= ")";

		// HACK
		$sql = str_replace(",)", ")", $sql);

		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sourceVersionID = $this->getPropertyId();
		$this->_property_id = 0;
		$this->_version = $newVersion;
		$this->_property_id = 0;
		$newVersionID = sYDB()->Insert_ID();
		$this->properties->copyTo($sourceVersionID, $newVersionID);

		return $newVersion;
	}

/// @cond DEV

	/**
	 * Marks the current version of this Object as "changed"
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function markAsChanged() {
		$objectid = (int)$this->_id;
		$version = (int)$this->getVersion();
		$ts = time();

		if ($this->permissions) {
			if (!$this->permissions->checkInternal($this->_uid, $objectid, "RWRITE")) {
				return false;
			}
		}

		$sql = "UPDATE
					" . $this->_table . "
				SET
					CHANGEDTS = $ts,
					CHANGEDBY = " . $this->_uid . ",
					HASCHANGED = 1
				WHERE
					(OBJECTID = '$objectid') AND
					(VERSION = $version);";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the Property Id of the current Object instance
	 *
	 * @return int|bool Property Id or FALSE in case of an error
	 */
	public function getPropertyId() {
		$objectid = (int)$this->_id;
		$version = (int)$this->getVersion();

		if ($this->permissions && !$this->permissions->checkInternal($this->_uid, $objectid, "RREAD")) {
			return false;
		}

		if ($this->_property_id == 0) {
			$sql = "SELECT prop.ID AS ID FROM " . $this->_table . " as prop WHERE (prop.OBJECTID = $objectid) AND (prop.VERSION = $version);";
			$ra = $this->cacheExecuteGetArray($sql);
			$this->_property_id = $ra[0]['ID'];
		}
		return $this->_property_id;
	}

/// @endcond

	/**
	 * Gets the Property Id of the current Object instance
	 *
	 * @param object $sourceObject Source Object
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function copyFrom(&$sourceObject) {
		if ($this->permissions->checkInternal($this->_uid, $this->_id, "RWRITE")) {
			$sourceID = $sourceObject->getID();
			$sourceVersion = $sourceObject->getVersion();
			$targetID = (int)$this->_id;
			$targetVersion = $this->getVersion();
			$sourceVersionID = $sourceObject->getPropertyId();
			$targetVersionID = $this->getPropertyId();
			$this->permissions->copyTo($sourceID, $targetID);
			$this->properties->copyTo($sourceVersionID, $targetVersionID);
			$this->tags->copyTo($sourceID, $sourceVersion, $targetID, $targetVersion);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Copies Tags from another Object to this Object
	 *
	 * @param object $sourceObject Source File object
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function copyTagsFrom(&$sourceObject) {
		$sourceID = $sourceObject->getID();
		$sourceVersion = $sourceObject->getVersion();
		$targetID = (int)$this->_id;
		$targetVersion = $this->getVersion();
		if ($this->permissions->checkInternal($this->_uid, $targetID, "RWRITE")) {
			if ($this->tags->copyTo($sourceID, $sourceVersion, $targetID, $targetVersion)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Copies Permissions from another Object to this Object
	 *
	 * @param object $sourceObject Source File object
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function copyPermissionsFrom(&$sourceObject) {
		$sourceID = $sourceObject->getID();
		$targetID = (int)$this->_id;
		if ($this->permissions->checkInternal($this->_uid, $targetID, "RWRITE")) {
			if ($this->permissions->copyTo($sourceID, $targetID)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Copies Properties from another Object to this Object
	 *
	 * @param object $sourceObject Source Cblock object
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function copyPropertiesFrom(&$sourceObject) {
		$sourceVersionID = $sourceObject->getPropertyId();
		$targetID = (int)$this->_id;
		$targetVersionID = $this->getPropertyId();
		if ($this->permissions->checkInternal($this->_uid, $targetID, "RWRITE")) {
			if ($this->properties->copyTo($sourceVersionID, $targetVersionID)) {

				if (get_class($this) === get_class($sourceObject)) {
					$sourcePropertiesTable = $sourceObject->getPropertiesTable();
					$currrentPropertiesTable = $this->getPropertiesTable();

					$sql = "SELECT
								*
							FROM
								" . $sourcePropertiesTable . "
							WHERE
								OBJECTID = " . $sourceObject->getID() . " AND
								VERSION = " . $sourceObject->getVersion() . ";";
					$dbr = sYDB()->Execute($sql);
					if ($dbr === false) {
						throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
					}
					$objectPropertyData = $dbr->GetArray();
					$objectPropertyData = $objectPropertyData[0];

					$sql = "DESCRIBE " . $sourcePropertiesTable . ";";
					$dbr = sYDB()->Execute($sql);
					if ($dbr === false) {
						throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
					}
					$ra = $dbr->GetArray();

					if (count($ra) > 0) {
						$fields = array();
						foreach ($ra as $raItem) {
							if ((strtoupper($raItem['Field']) != 'ID') &&
								(strtoupper($raItem['Field']) != 'OBJECTID') &&
								(strtoupper($raItem['Field']) != 'VERSION')
							) {
								if (strstr($raItem['Type'], 'int')) {
									$fields[] = $raItem['Field'] . " = " . $objectPropertyData[$raItem['Field']];
								} else {
									$fields[] = $raItem['Field'] . " = '" . $objectPropertyData[$raItem['Field']] . "'";
								}
							}
						}
						$fields = implode(', ', $fields);
					}

					$sql = "UPDATE $currrentPropertiesTable
							SET $fields
							WHERE
								OBJECTID = " . $this->getID() . " AND
								VERSION = " . $this->getVersion() . ";";
					$dbr = sYDB()->Execute($sql);
					if ($dbr === false) {
						throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
					}
				}
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets the name of the Properties Table for this Object
	 *
	 * @return string Name of the Propertytable
	 */
	public function getPropertiesTable() {
		return $this->_table;
	}

	/**
	 * Acquires a lock on the current instance of this Object
	 *
	 * @param string $token Lock token, only needed when re-acquiring a lock (optional)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function acquireLock($token = '') {

		if ((int)$this->_uid == (int)sUserMgr()->getAnonymousID()) {
			return false;
		}

		$objectid = (int)$this->_id;
		$token = mysql_real_escape_string($token);
		$lockInfo = $this->getLock();

		if ($lockInfo["LOCKED"] > 0 && ($lockInfo["TOKEN"] != $token)) {
			return false;
		} else {
			if (($lockInfo["LOCKED"] > 0 && ($lockInfo["TOKEN"] == $token)) || ($lockInfo["LOCKED"] == 0) || (time() - $lockInfo["LOCKED"] > (int)sConfig()->getVar("/CONFIG/OBJECTLOCK_TIMEOUT"))) {
				$ts = time() + (int)sConfig()->getVar("/CONFIG/OBJECTLOCK_TIMEOUT");
				$sql = "UPDATE " . $this->_table . " SET LOCKED = $ts, TOKEN = '" . $token . "', LOCKUID = '" . $this->_uid . "' WHERE (OBJECTID = $objectid);";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Releases a lock on the current instance of this Object
	 *
	 * @param string $token Lock token
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function releaseLock($token) {
		$objectid = (int)$this->_id;
		$token = mysql_real_escape_string($token);
		$lockInfo = $this->getLock();
		if ($lockInfo["LOCKED"] > 0 && ($lockInfo["TOKEN"] != $token)) {
			return false;
		} else {
			if (($lockInfo["LOCKED"] > 0 && ($lockInfo["TOKEN"] == $token)) || ($lockInfo["LOCKED"] == 0)) {
				$sql = "UPDATE " . $this->_table . " SET LOCKED = 0, TOKEN = '' WHERE (OBJECTID = $objectid);";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				return true;
			}
		}
	}

	/**
	 * Checks if the specified lock token is set on the current instance of this Object
	 *
	 * @param string $token Lock token
	 * @return bool TRUE if the lock has the specified token or FALSE if not
	 */
	public function checkToken($token) {
		$objectid = (int)$this->_id;
		$token = mysql_real_escape_string($token);
		$lockInfo = $this->getLock();
		$lockts = time() - (int)sConfig()->getVar("/CONFIG/OBJECTLOCK_TIMEOUT");
		if ($lockInfo["LOCKED"] >= $lockts && ($lockInfo["TOKEN"] != $token)) {
			return false;
		}
		return true;
	}

	/**
	 * Checks if the current instance of this Object is locked
	 *
	 * @return bool TRUE if the Object currently has a lock or FALSE if not
	 */
	public function getLock() {
		$objectid = (int)$this->_id;
		$lockts = time() - (int)sConfig()->getVar("/CONFIG/OBJECTLOCK_TIMEOUT");
		$sql = "SELECT LOCKED, TOKEN, LOCKUID FROM " . $this->_table . " WHERE OBJECTID = $objectid AND LOCKED >= $lockts;";
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
			return false;
		}
		$ra = $dbr->GetArray();
		return $ra[0];
	}

	/**
	 * Calls a specific Extension hook Callback method
	 *
	 * @param string $callbackName
	 * @param mixed ... (any type of parameters)
	 */
	function callExtensionHook() {
		$args = func_get_args();
		$extensionMgr = new ExtensionMgr();
		$all_extensions = $extensionMgr->getList(EXTENSION_ALL, true);
		foreach ($all_extensions as $all_extension_item) {
			$extension = $extensionMgr->getExtension($all_extension_item['CODE']);
			if ($extension) {
				return call_user_func_array(array($extension, 'callExtensionHook'), $args);
			}
		}
	}

}

?>