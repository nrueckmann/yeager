<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The Permissions class, which represents the Permissions manager.
 */
class Permissions {
	private $_table;
	private $_roles;
	private $_user;
	private $_object;

	/**
	 * Constructor of the Permissions class
	 *
	 * @param string $table Name of the table into which the Permissions will be saved
	 * @param object $object Object from which the Tag class was instantiated
	 */
	public function __construct($table, &$object) {
		$this->_table = $table;
		$this->_user = new User(sUserMgr()->getCurrentUserID());
		$this->_object = &$object;
	}

	/**
	 * Gets all Usergroups for the current User
	 *
	 * @return array Array of Usergroups
	 */
	public function getUsergroups() {
		return $this->_user->getUsergroups();
	}

/// @cond DEV

	/**
	 * Adds a new privilege
	 *
	 * @param string $code Privilege code
	 */
	public function addPrivilege($code) {
		$code = sYDB()->escape_string(sanitize($code));
		$sql = "ALTER TABLE " . $this->_table . " ADD `" . $code . "` SMALLINT NOT NULL DEFAULT '0';";
		sYDB()->Execute($sql);
	}

	/**
	 * Removes a privilege
	 *
	 * @param string $code Privilege code
	 */
	public function removePrivilege($code) {
		$code = sYDB()->escape_string(sanitize($code));
		$sql = "ALTER TABLE " . $this->_table . " DROP `" . $code . "`;";
		sYDB()->Execute($sql);
	}

/// @endcond

	/**
	 * Gets Permissions for the current Object
	 *
	 * @param int $objectId (optional) Object Id
	 * @return array Array of Permissions
	 */
	public function getPermissions($objectId = 0) {
		if ($objectId == 0) {
			$objectId = $this->_object->getID();
		}
		if (($objectId > 0)) {
			$sql = "SELECT * FROM " . $this->_table . " WHERE OID = ?;";
			$result = sYDB()->Execute($sql, $objectId);
			$resultarray = @$result->GetArray();
			return $resultarray;
		}
	}

	/**
	 * Gets permissions in bulk for an array of objects
	 *
	 * @param int $objectIds Array of object Ids
	 * @return array Array of Permissions
	 */
	public function getPermissionsBulk($permission, $objectIds = array()) {
		if ($permission && (count($objectIds) > 0)) {
			$permission = sYDB()->escape_string($permission);
			$sql = "SELECT ID, OID, `".$permission."` FROM " . $this->_table . " WHERE (";
			for ($i = 0; $i < count($objectIds); $i++) {
				if ($i != 0) {
					$sql .= " OR ";
				}
				$sql .= "(OID = ".(int)$objectIds[$i].")";
			}
			$sql .= ") AND (";

			$user = new User(sUserMgr()->getCurrentUserID());
			$usergroups = $user->getUsergroups();
			for ($r = 0; $r < count($usergroups); $r++) {
				if ($r != 0) {
					$sql .= " OR ";
				}
				$sql .= "(USERGROUPID = ".(int)$usergroups[$r]["ID"].")";
			}
			$sql .= ");";
			$result = sYDB()->Execute($sql);
			$resultarray = @$result->GetArray();

			$output = array();
			foreach ($resultarray as $item) {
			    $key = $item['OID'];
			    if (!isset($output[$key])) {
			        $output[$key] = filter_var($item[$permission], FILTER_VALIDATE_BOOLEAN);
			    } else if ($item[$permission] == "1") {
				    $output[$key] = true;
			    }
			}
			return $output;
		}
	}

	/**
	 * Sets Permissions for the current Object
	 *
	 * @param array $permissions Array of Permissions
	 * @param int $objectId (optional) Object Id
	 * @throws Exception
	 */
	public function setPermissions($permissions, $objectId = NULL) {
		$origObjectId = $objectId;

		foreach ($permissions as $perm) {
			$roleid = (int)$perm["USERGROUPID"];
			$robjectId = (int)$perm["OID"];
			if ($origObjectId < 1) {
				if ($robjectId < 1) {
					if ($objectId < 1) {
						$objectId = (int)$this->_object->getID();
					}
				} else {
					$objectId = (int)$robjectId;
				}
			}
			// Check if current user has permissions to change usergroup-permissions
			if (sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $roleid, 'RWRITE')) {
				$pinfo = $this->getByUsergroup($roleid, $objectId);
				if (count($pinfo) > 0) {
					if (isset($perm["RREAD"])) {
						$value_read = (int)$perm["RREAD"];
					} else {
						$value_read = $pinfo["RREAD"];
					}
					if (isset($perm["RWRITE"])) {
						$value_write = (int)$perm["RWRITE"];
					} else {
						$value_write = $pinfo["RWRITE"];
					}
					if (isset($perm["RDELETE"])) {
						$value_delete = (int)$perm["RDELETE"];
					} else {
						$value_delete = $pinfo["RDELETE"];
					}
					if (isset($perm["RSUB"])) {
						$value_sub = (int)$perm["RSUB"];
					} else {
						$value_sub = $pinfo["RSUB"];
					}
					if (isset($perm["RSTAGE"])) {
						$value_stage = (int)$perm["RSTAGE"];
					} else {
						$value_stage = $pinfo["RSTAGE"];
					}
					if (isset($perm["RMODERATE"])) {
						$value_moderate = (int)$perm["RMODERATE"];
					} else {
						$value_moderate = $pinfo["RMODERATE"];
					}
					if (isset($perm["RCOMMENT"])) {
						$value_comment = (int)$perm["RCOMMENT"];
					} else {
						$value_comment = $pinfo["RCOMMENT"];
					}
					if (isset($perm["RSEND"])) {
						$value_send = (int)$perm["RSEND"];
					} else {
						$value_send = $pinfo["RSEND"];
					}

					// Update
					$sql = "UPDATE " . $this->_table . " SET
								RREAD = ?,
								RWRITE = ?,
								RDELETE = ?,
								RSUB = ?,
								RSTAGE = ?,
								RMODERATE = ?,
								RCOMMENT = ?,
								RSEND = ?
							WHERE OID = ? AND USERGROUPID = ?;";
					$result = sYDB()->Execute($sql, $value_read, $value_write, $value_delete, $value_sub, $value_stage, $value_moderate, $value_comment, $value_send, $objectId, $roleid);
				} else {
					$value_read = (int)$perm["RREAD"];
					$value_write = (int)$perm["RWRITE"];
					$value_delete = (int)$perm["RDELETE"];
					$value_sub = (int)$perm["RSUB"];
					$value_stage = (int)$perm["RSTAGE"];
					$value_moderate = (int)$perm["RMODERATE"];
					$value_comment = (int)$perm["RCOMMENT"];
					$value_send = (int)$perm["RSEND"];

					// Insert
					$sql = "INSERT INTO " . $this->_table . " SET USERGROUPID = $roleid,
								RREAD = ?,
								RWRITE = ?,
								RDELETE = ?,
								RSUB = ?,
								RSTAGE = ?,
								RMODERATE = ?,
								RCOMMENT = ?,
								RSEND = ?,
								OID = ?;";
					$result = sYDB()->Execute($sql, $value_read, $value_write, $value_delete, $value_sub, $value_stage, $value_moderate, $value_comment, $value_send, $objectId);
				}

				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				sUsergroups()->setByUsergroupHashPermission($this->_table, $roleid, $objectId, "RREAD", $value_read);
				sUsergroups()->setByUsergroupHashPermission($this->_table, $roleid, $objectId, "RWRITE", $value_write);
				sUsergroups()->setByUsergroupHashPermission($this->_table, $roleid, $objectId, "RDELETE", $value_delete);
				sUsergroups()->setByUsergroupHashPermission($this->_table, $roleid, $objectId, "RSUB", $value_sub);
				sUsergroups()->setByUsergroupHashPermission($this->_table, $roleid, $objectId, "RSTAGE", $value_stage);
				sUsergroups()->setByUsergroupHashPermission($this->_table, $roleid, $objectId, "RMODERATE", $value_moderate);
				sUsergroups()->setByUsergroupHashPermission($this->_table, $roleid, $objectId, "RCOMMENT", $value_comment);
				sUsergroups()->setByUsergroupHashPermission($this->_table, $roleid, $objectId, "RSEND", $value_send);

				// Call callback, if present
				if ($this->_object) {
					$permA = array();
					$permA["RREAD"] = $value_read;
					$permA["RWRITE"] = $value_write;
					$permA["RDELETE"] = $value_delete;
					$permA["RSUB"] = $value_sub;
					$permA["RSTAGE"] = $value_stage;
					$permA["RMODERATE"] = $value_moderate;
					$permA["RCOMMENT"] = $value_comment;
					$permA["RSEND"] = $value_send;
					$permA["RREAD"] = $value_read;
					$this->_object->onPermissionsChange($roleid, $permA, $objectId);
				}
			}
		}
	}

	/**
	 * Gets all Permissions for the current Object for a specific Usergroup
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param int $objectId Object Id
	 * @return array Array of Permissions
	 */
	public function getByUsergroup($usergroupId, $objectId) {
		$usergroupId = (int)$usergroupId;
		$objectId = (int)$objectId;
		if (sUsergroups()->getByUsergroupHash($this->_table, $usergroupId, $objectId)) {
			return sUsergroups()->getByUsergroupHash($this->_table, $usergroupId, $objectId);
		}
		if (($objectId > 0) && ($usergroupId > 0)) {
			$sql = "SELECT * FROM " . $this->_table . " WHERE OID = ?;";
			$result = sYDB()->Execute($sql, $objectId);
			$resultarray = @$result->GetArray();
			foreach ($resultarray as $row) {
				sUsergroups()->setByUsergroupHash($this->_table, $row["USERGROUPID"], $row["OID"], $row);
			}
			return sUsergroups()->getByUsergroupHash($this->_table, $usergroupId, $objectId);
		}
	}

	/**
	 * Sets a Permission for a specific Usergroup
	 *
	 * @param int $usergroupId
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param $objectId Object Id
	 * @param int $value Permission value (1 for allowed, 0 for not allowed)
	 * @return bool TRUE on success or FALSE if not allowed
	 * @throws Exception
	 */
	public function setByUsergroup($usergroupId, $permission, $objectId, $value) {
		$usergroupId = (int)$usergroupId;
		$objectId = (int)$objectId;
		$permission = sYDB()->escape_string(sanitize($permission));
		$value = sYDB()->escape_string(sanitize($value));
		if ($value < 1) {
			$value = 0;
		}

		// Check if current user has permissions to change usergroup-permissions
		if (!sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $usergroupId, 'RWRITE')) {
			return false;
		}

		$pinfo = $this->getByUsergroup($usergroupId, $objectId);
		if (count($pinfo) > 0) {
			// Update
			$sql = "UPDATE " . $this->_table . " SET `$permission` = ? WHERE OID = ? AND USERGROUPID = ?;";
			$result = sYDB()->Execute($sql, $value, $objectId, $usergroupId);
		} else {
			// Insert
			$sql = "INSERT INTO " . $this->_table . " SET USERGROUPID = ?, `$permission` = ?, OID = ?;";
			$result = sYDB()->Execute($sql, $usergroupId, $value, $objectId);
		}

		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		sUsergroups()->setByUsergroupHashPermission($this->_table, $usergroupId, $objectId, $permission, $value);

		// Call callback, if present
		if ($this->_object) {
			$this->_object->onPermissionChange($usergroupId, $permission, $value, $objectId);
		}

		if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/PERMISSION_CHANGE") == "true") {
			Singleton::FC()->emptyBucket();
		}

		return true;
	}

	/**
	 * Copies Permissions from one Object to another
	 *
	 * @param int $oldObjectId Object Id to copy from
	 * @param int $newObjectId Object Id to copy to
	 * @throws Exception
	 */
	public function copyTo($oldObjectId, $newObjectId) {
		$oldObjectId = (int)$oldObjectId;
		$newObjectId = (int)$newObjectId;

		if ($oldObjectId == $newObjectId) {
			return true;
		}

		$sql = "DELETE FROM " . $this->_table . " WHERE OID = ?;";
		sYDB()->Execute($sql, $newObjectId);

		$sql = "INSERT INTO " . $this->_table . "
					(OID, USERGROUPID, RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
				SELECT $newObjectId, USERGROUPID, RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND
				FROM " . $this->_table . " WHERE OID = ?;";
		$result = sYDB()->Execute($sql, $oldObjectId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		} else {
			sUsergroups()->emptyUsergroupHash($this->_table);
			return true;
		}
	}

	/**
	 * Clears Permissions of the current specific Object
	 *
	 * @param int $objectId (optional) Object Id
	 * @throws Exception
	 */
	public function clear($objectId = NULL) {
		if ($objectId == NULL) {
			$objectId = $this->_object->getID();
		} // oid is optional
		$sql = "DELETE FROM " . $this->_table . " WHERE OID = ?;";
		$result = sYDB()->Execute($sql, $objectId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		sUsergroups()->emptyUsergroupHash($this->_table);
	}

/// @cond DEV

	/**
	 * Checks if a User owns a specific Permission for a specific Object
	 *
	 * @param int $userId User Id
	 * @param int $objectId Object Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @return bool TRUE if the User has Permissions, false if not
	 */
	public function checkInternal($userId, $objectId, $permission) {
		$userId = (int)$userId;
		$objectId = (int)$objectId;
		$permission = sYDB()->escape_string(sanitize($permission));
		if (($userId == 0) && ($permission == "RREAD")) {
			return true;
		}
		if ($userId == $this->_user->_uid) { // reuse user object
		    $user = $this->_user;
		} else {
		    $user = new User($userId);
		}
		$userroles = $user->getUsergroups($userId);
		for ($r = 0; $r < count($userroles); $r++) {
			$permissions = $this->getByUsergroup($userroles[$r]["ID"], $objectId);
			$privinfo = $privinfo + $permissions[$permission];
            if ($privinfo > 0) { // early exit
                return true;
            }
		}
		if ($privinfo > 0) {
			return true;
		} else {
			return false;
		}
		return false;
	}

/// @endcond

	/**
	 * Checks if a User owns a specific Permission for a specific Object
	 *
	 * @param int $userId User Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param int $objectId (optional) Object Id
	 * @return bool TRUE if the User has Permissions, false if not
	 */
	public function check($userId, $permission, $objectId) {
		if ($objectId == NULL) {
			$objectId = $this->_object->getID();
		}
		return $this->checkInternal($userId, $objectId, $permission);
	}
}

?>