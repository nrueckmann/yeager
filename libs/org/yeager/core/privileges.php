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
	public function addPrivilege($code, $name, $extcode = NULL) {
		$code = mysql_real_escape_string(sanitize($code));
		$name = mysql_real_escape_string(sanitize($name));
		if ($extcode) {
			$extcode = "'" . mysql_real_escape_string(sanitize($extcode)) . "'";
		} else {
			$extcode = 'NULL';
		}
		$sql = "INSERT INTO `" . $this->_table . "`
					(`PRIVILEGE`, `NAME`, `EXTCODE`)
				VALUES
					('" . $code . "', '" . $name . "', $extcode);";
		$result = sYDB()->Execute($sql);
	}

	/**
	 * Removes a Privilege
	 *
	 * @param string $code Privilege code
	 * @param string $extcode Extension code (optional)
	 */
	public function removePrivilege($code, $extcode = NULL) {
		$code = mysql_real_escape_string(sanitize($code));
		$extcode = mysql_real_escape_string(sanitize($extcode));

		if ($extcode) {
			$extSQL = " AND `EXTCODE` = '$extcode' ";
		}

		$sql = "SELECT ID FROM " . $this->_table . " WHERE PRIVILEGE = '" . $code . "' $extSQL;";
		$result = sYDB()->Execute($sql);
		$resultarray = @$result->GetArray();
		$privilegeId = $resultarray[0]['ID'];

		if ($privilegeId) {
			$sql = "DELETE FROM `" . $this->_table_values . "` WHERE PRIVILEGEID = " . $privilegeId . ";";
			$result = sYDB()->Execute($sql);

			$sql = "DELETE FROM `" . $this->_table . "` WHERE ID = " . $privilegeId . ";";
			$result = sYDB()->Execute($sql);
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
		if ($extcode) {
			$extcode = mysql_real_escape_string(sanitize($extcode));
			$extSQL = " AND EXTCODE = '" . $extcode . "' ";
		}
		$sql = "SELECT * FROM " . $this->_table . " WHERE 1 $extSQL;";
		$result = sYDB()->Execute($sql);
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
		if ($extcode) {
			$extcode = mysql_real_escape_string(sanitize($extcode));
			$extSQL = " EXTCODE = '" . $extcode . "' ";
		} else {
			$extSQL = " 1 ";
		}

		if ($usergroupId > 0) {
			$sql = "SELECT * FROM `" . $this->_table . "` WHERE $extSQL;";
			$result = sYDB()->Execute($sql);
			$allPrivileges = $result->GetArray();

			$privilegeCodes = array();
			foreach ($allPrivileges as $allPrivilege) {
				$privilegeCodes[] = $allPrivilege['PRIVILEGE'];
			}

			$returnArray = array();
			foreach ($allPrivileges as $allPrivilege) {
				$sql = "SELECT VALUE FROM `yg_privileges_values` WHERE PRIVILEGEID = '" . $allPrivilege['ID'] . "' AND USERGROUPID = " . $usergroupId . ";";
				$result = sYDB()->Execute($sql);
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
		$privilege = mysql_real_escape_string(sanitize($privilege));
		$value = mysql_real_escape_string(sanitize($value));
		if ($value < 1) {
			$value = 0;
		}

		// Check if current user has permissions to change usergroup-permissions
		if (!sUsergroups()->usergroupPermissions->checkInternal(sUserMgr()->getCurrentUserID(), $usergroupId, 'RWRITE')) {
			return false;
		}

		$sql = "SELECT ID FROM " . $this->_table . " WHERE PRIVILEGE = '" . $privilege . "';";
		$result = sYDB()->Execute($sql);
		$resultarray = @$result->GetArray();
		$privilegeId = $resultarray[0]['ID'];

		if ($privilegeId) {
			$pinfo = $this->getByUsergroup($usergroupId);
			if ($pinfo[$privilege] === NULL) {
				// Insert
				$sql = "INSERT INTO " . $this->_table_values . "
							(`USERGROUPID`, `PRIVILEGEID`, `VALUE`)
						VALUES
							($usergroupId, $privilegeId, $value);";
			} else {
				// Update
				$sql = "UPDATE " . $this->_table_values . " SET VALUE = $value WHERE USERGROUPID = $usergroupId AND PRIVILEGEID = $privilegeId;";
			}
			$result = sYDB()->Execute($sql);
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
		$permission = mysql_real_escape_string(sanitize($permission));

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
		$extcode = mysql_real_escape_string(sanitize($extcode));

		$sql = "SELECT * FROM " . $this->_table . " WHERE EXTCODE = '" . $extcode . "';";
		$result = sYDB()->Execute($sql);
		$resultarray = @$result->GetArray();

		foreach ($resultarray as $resultarrayItem) {
			$this->removePrivilege($resultarrayItem['PRIVILEGE'], $extcode);
		}
	}

/// @endcond

}

?>