<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The Privileges class, which represents the Privileges manager.
 */
class Privileges {
	private $_roles;
	private $_user;
	private $_table;

	/**
	 * Constructor of the Privileges class
	 *
	 * @param string $table Name of the table into which the Privileges will be saved
	 * @param object $object Object from which the Privileges class was instantiated
	 */
	public function __construct() {
		$this->_user = new User(sUserMgr()->getCurrentUserID());
		$this->_table = 'yg_privileges';
		$this->_table_values = 'yg_privileges_values';
	}

/// @cond DEV

	/**
	 * Adds a new Privilege
	 *
	 * @param string $code Privilege code
	 * @param string $name Privilege name
	 * @param string $extcode Extension code (optional)
	 */
	public function addPrivilege($code, $name, $extcode = 'NULL') {
		$code = sYDB()->escape_string(sanitize($code));
		$name = sYDB()->escape_string(sanitize($name));
		$sql = "INSERT INTO `" . $this->_table . "` (`PRIVILEGE`, `NAME`, `EXTCODE`) VALUES (?, ?, ?);";
		sYDB()->Execute($sql, $code, $name, $extcode);
	}

	/**
	 * Removes a Privilege
	 *
	 * @param string $code Privilege code
	 * @param string $extcode Extension code (optional)
	 */
	public function removePrivilege($code, $extcode = NULL) {
		$code = sYDB()->escape_string(sanitize($code));
		$extcode = sYDB()->escape_string(sanitize($extcode));

		$sql = "SELECT ID FROM " . $this->_table . " WHERE PRIVILEGE = ?";
		if ($extcode) {
			$sql .= " AND `EXTCODE` = ?;";
			$result = sYDB()->Execute($sql, $code, $extcode);
		} else {
			$sql .= ";";
			$result = sYDB()->Execute($sql, $code);
		}

		$resultarray = @$result->GetArray();
		$privilegeId = (int)$resultarray[0]['ID'];

		if ($privilegeId) {
			$sql = "DELETE FROM `" . $this->_table_values . "` WHERE PRIVILEGEID = ?;";
			sYDB()->Execute($sql, $privilegeId);

			$sql = "DELETE FROM `" . $this->_table . "` WHERE ID =  ?;";
			sYDB()->Execute($sql, $privilegeId);
		}
	}

/// @endcond

	/**
	 * Gets a list of all Privileges
	 *
	 * @param string $extcode Extension code (optional)
	 * @return array Array of Privileges
	 */
	public function getList($extcode = NULL) {
		$sql = "SELECT * FROM " . $this->_table;
		if ($extcode) {
			$sql .= " WHERE EXTCODE = ?;";
			$result = sYDB()->Execute($sql, $extcode);
		} else {
			$sql .= ";";
			$result = sYDB()->Execute($sql);
		}
		$resultarray = @$result->GetArray();
		return $resultarray;
	}

	/**
	 * Gets all Privileges for a specific Usergroup
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $extcode Extension code (optional)
	 * @return array Array of Permissions
	 */
	public function getByUsergroup($usergroupId, $extcode = NULL) {
		$usergroupId = (int)$usergroupId;

		if ($usergroupId > 0) {
			$sql = "SELECT * FROM `" . $this->_table . "`";
			if ($extcode) {
				$sql .= " WHERE EXTCODE = ?;";
				$result = sYDB()->Execute($sql, $extcode);
			} else {
				$sql .= ";";
				$result = sYDB()->Execute($sql);
			}
			$allPrivileges = $result->GetArray();
			$privilegeCodes = array();
			foreach ($allPrivileges as $allPrivilege) {
				$privilegeCodes[] = $allPrivilege['PRIVILEGE'];
			}

			$returnArray = array();
			foreach ($allPrivileges as $allPrivilege) {
                $sql = "SELECT VALUE FROM `yg_privileges_values` WHERE PRIVILEGEID = ? AND USERGROUPID = ?;";
				$result = sYDB()->Execute($sql, $allPrivilege['ID'], $usergroupId);
				$value = $result->GetArray();
				$value = $value[0]['VALUE'];
				$returnArray[$allPrivilege['PRIVILEGE']] = $value;
			}
			if (count($returnArray) > 0) {
				$returnArray['USERGROUPID'] = $usergroupId;
				return $returnArray;
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	 * Sets a Privilege for a specific Usergroup
	 *
	 * @param int $usergroupId
	 * @param string $privilege Privilege
	 * @param int $value Privilege value (1 for allowed, 0 for not allowed)
	 * @return bool TRUE on success or FALSE if not allowed
	 * @throws Exception
	 */
	public function setByUsergroup($usergroupId, $privilege, $value) {
		$usergroupId = (int)$usergroupId;
		$privilege = sYDB()->escape_string(sanitize($privilege));
		$value = sYDB()->escape_string(sanitize($value));
		if ($value < 1) {
			$value = 0;
		}

		// Check if current user has permissions to change usergroup-permissions
		if (!sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $usergroupId, 'RWRITE')) {
			return false;
		}

		$sql = "SELECT ID FROM " . $this->_table . " WHERE PRIVILEGE = ?;";
		$result = sYDB()->Execute($sql, $privilege);
		$resultarray = @$result->GetArray();
		$privilegeId = (int)$resultarray[0]['ID'];

		if ($privilegeId) {
			$pinfo = $this->getByUsergroup($usergroupId);
			if ($pinfo[$privilege] === NULL) {
				// Insert
				$sql = "INSERT INTO " . $this->_table_values . "
							(`USERGROUPID`, `PRIVILEGEID`, `VALUE`)
						VALUES
							(?, ?, ?);";
				$result = sYDB()->Execute($sql, $usergroupId, $privilegeId, $value);
			} else {
				// Update
				$sql = "UPDATE " . $this->_table_values . " SET VALUE = ? WHERE USERGROUPID = ? AND PRIVILEGEID = ?;";
				$result = sYDB()->Execute($sql, $value, $usergroupId, $privilegeId);
			}

			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/PERMISSION_CHANGE") == "true") {
				Singleton::FC()->emptyBucket();
			}

			return true;
		}
		return false;
	}

	/**
	 * Checks if a User owns a specific Permission for a specific Object
	 *
	 * @param int $userId User Id
	 * @param string $permission Privilege name
	 * @return bool TRUE if the User has Permissions, false if not
	 */
	public function check($userId, $permission) {
		$userId = (int)$userId;
		$permission = sYDB()->escape_string(sanitize($permission));

		$user = new User($userId);
		$userroles = $user->getUsergroups($userId);
		for ($r = 0; $r < count($userroles); $r++) {
			$permissions = $this->getByUsergroup($userroles[$r]["ID"]);
			$privinfo += $permissions[$permission];
			if ($privinfo > 0) {
				return true;
			}
		}
		return false;
	}

/// @cond DEV

	/**
	 * Removes all Privileges for a given Extension
	 *
	 * @param string $extcode Extension-Code
	 */
	public function removeAllExtensionPrivileges($extcode) {
		$extcode = sYDB()->escape_string(sanitize($extcode));

		$sql = "SELECT * FROM " . $this->_table . " WHERE EXTCODE = ?;";
		$result = sYDB()->Execute($sql, $extcode);
		$resultarray = @$result->GetArray();

		foreach ($resultarray as $resultarrayItem) {
			$this->removePrivilege($resultarrayItem['PRIVILEGE'], $extcode);
		}
	}

/// @endcond

}

?>