<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

\framework\import("org.phpass.PasswordHash");

/**
 * The User class, which represents a User object.
 */
class User extends \framework\Error {
	var $_db;
	var $_isvalidated;
	var $_usergroups;
	var $_uid;
	var $properties;
	private $id;

	/**
	 * Constructor of the User class
	 *
	 * @param int $uid User Id
	 */
	function __construct($uid) {
		$this->_db = sYDB();
		$this->_isvalidated = false;
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->id = (int)$uid;
		$this->properties = new Properties("yg_user_props", $this->getPropertyId());
		$this->_usergroups = false;
	}

/// @cond DEV

	/**
	 * Gets the name of the database table containing the User tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return '';
	}

/// @endcond

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
	 * Gets the Property Id of this User
	 *
	 * @return int Property Id
	 */
	function getPropertyId() {
		return (int)$this->id;
	}

/// @endcond


	/**
	 * Generates a token for this User which expires on the given timestamp
	 *
	 * @param int $expireTS Timestamp
	 * @return string|false Token or FALSE in case of an error
	 */
	function generateToken($expireTS) {
		$uid = (int)$this->id;
		$expireTS = (int)$expireTS;
		$token = md5(GetRandomString(11));

		$sql = "INSERT INTO `yg_user_tokens` (`ID`, `UID`, `TOKEN`, `TS` ) VALUES (NULL, ?, ?, ?);";
		$result = sYDB()->Execute($sql, $uid, $token, $expireTS);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return $token;
	}

	/**
	 * Removes all tokens for this User
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function removeToken() {
		$uid = (int)$this->id;
		$sql = "DELETE FROM `yg_user_tokens` WHERE UID = ?;";
		$result = sYDB()->Execute($sql, $uid);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Gets the dateformat for this User
	 *
	 * @param bool $javascript TRUE if the format should be returned in Javascript notation
	 * @return string Dateformat
	 */
	function getDateFormat($javascript = false) {
		$user_dateformat = $this->properties->getValueInternal('DATEFORMAT');

		switch ($user_dateformat) {
			case 'mm/dd/YYYY':
				if ($javascript) {
					$user_dateformat = 'MM/dd/yyyy';
				} else {
					$user_dateformat = 'm/d/Y';
				}
				break;
			case 'dd/mm/YYYY':
				if ($javascript) {
					$user_dateformat = 'dd/MM/yyyy';
				} else {
					$user_dateformat = 'd/m/Y';
				}
				break;
			default:
			case 'dd.mm.YYYY':
				if ($javascript) {
					$user_dateformat = 'dd.MM.yyyy';
				} else {
					$user_dateformat = 'd.m.Y';
				}
				break;
		}
		return $user_dateformat;
	}

	/**
	 * Gets the timeformat for this User
	 *
	 * @return string Timeformat
	 */
	function getTimeFormat() {
		$user_timeformat = $this->properties->getValueInternal('TIMEFORMAT');
		switch ($user_timeformat) {
			case '12':
				$user_timeformat = 'h:i a';
				break;
			default:
			case '24':
				$user_timeformat = 'H:i';
				break;
		}
		return $user_timeformat;
	}

	/**
	 * Gets the Language for this User
	 *
	 * @return int|false Language Id or FALSE in case of an error
	 */
	function getLanguage() {
		$uid = (int)$this->id;
		if ($uid > 0) {
			$sql = "SELECT
					u.LANG AS LANG
				FROM
					yg_user as u
				WHERE
					(u.ID = ?);";
			$result = sYDB()->Execute($sql, $uid);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
			return $resultarray[0]["LANG"];
		}
	}

	/**
	 * Sets the Language for this User
	 *
	 * @param int $language Language Id
	 */
	function setLanguage($language) {
		$uid = (int)$this->id;
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS') ||
			($uid == $this->_uid)
		) {
			$language = (int)$language;
			$sql = "UPDATE `yg_user` SET LANG = ? WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $language, $uid);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets basic information about this User
	 *
	 * @return array|false Array containing information about the user or FALSE in case of an error
	 */
	function get() {
		$uid = $this->id;
		if ($uid > 0) {
			$sql = "SELECT
					u.LOGIN AS LOGIN,
					u.PASSWORD AS PASSWORD,
					u.ID AS ID,
					yg_user_propsv.*
				FROM
					yg_user as u
				LEFT JOIN
					yg_user_propsv ON u.ID = yg_user_propsv.OID
				WHERE
					(u.ID = ?);";
			$result = sYDB()->Execute($sql, $uid);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
		}
		return $resultarray[0];
	}

	/**
	 * Sets the Password of this User
	 *
	 * @param string $password Password
	 */
	function setPassword($password) {
		$uid = (int)$this->id;
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS') || ($uid == $this->_uid)) {
			$passwordHash = new PasswordHash(8, false);

			$password = sYDB()->escape_string(sanitize($password));
			$hash = $passwordHash->HashPassword($password);

			$sql = "UPDATE `yg_user` SET PASSWORD = ? WHERE (ID = ?);";
			sYDB()->Execute($sql, $hash, $uid);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the Login of this User
	 *
	 * @param string $login Login
	 */
	function setLogin($login) {
		$uid = (int)$this->id;
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS') ||
			($uid == $this->_uid)
		) {
			$login = sYDB()->escape_string(sanitize($login));
			$sql = "UPDATE `yg_user` SET LOGIN = ? WHERE (ID = ?);";
			sYDB()->Execute($sql, $login, $uid);
			$this->properties->setValue('EMAIL', $login);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets information about all Usergroups assigned to this User
	 *
	 * @return array|false Array of Usergroups or FALSE in case of an error
	 */
	function getUsergroups() {
		if ($this->_usergroups) {
			return $this->_usergroups;
		}

		$uid = $this->id;
		if ($uid < 1) {
			return;
		}

		$sql = "SELECT
					r.NAME AS NAME, r.ID AS ID
				FROM
					yg_user_lnk_usergroups as lnk, yg_usergroups as r
				WHERE
					(lnk.UID = ".(int)$this->_uid.") AND (lnk.USERGROUPID = r.ID)
				ORDER BY r.NAME;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$allRoles = $result->GetArray();

		if ($this->_uid == $uid) {
			$this->_usergroups = $allRoles;
			return $this->_usergroups;
		}

		// SQL for permissions
		$perm_SQL_SELECT = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RSEND) AS RSEND";
		$perm_SQL_FROM = " LEFT JOIN yg_usergroups_permissions AS perm ON perm.OID = r.ID";
		$perm_SQL_WHERE = " AND (";
		for ($r = 0; $r < count($allRoles); $r++) {
			$perm_SQL_WHERE .= "(perm.USERGROUPID = " . (int)$allRoles[$r]["ID"] . ") ";
			if ((count($allRoles) - $r) > 1) {
				$perm_SQL_WHERE .= " OR ";
			}
		}
		$perm_SQL_WHERE .= ") ";

		$sql = "SELECT
					r.NAME AS NAME, r.ID AS ID
					$perm_SQL_SELECT
				FROM
					(yg_user_lnk_usergroups as lnk, yg_usergroups as r)
				$perm_SQL_FROM
				WHERE
					(lnk.UID = ?) AND (lnk.USERGROUPID = r.ID)
					$perm_SQL_WHERE
				GROUP BY
					r.ID
				HAVING
					(r.ID IS NOT NULL)
				ORDER BY r.NAME;";
		$result = sYDB()->Execute($sql, $uid);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();

		$this->_usergroups = $resultarray;

		return $this->_usergroups;
	}

	/**
	 * Gets Ids of all Usergroups assigned to this User
	 *
	 * @return array|false Array of UsergroupIds or FALSE in case of an error
	 */
	function getUsergroupIds() {
		$uid = $this->id;
		if ($uid < 1) {
			return;
		}
		$sql = "SELECT
					r.ID AS ID
				FROM
					yg_user_lnk_usergroups as lnk, yg_usergroups as r
				WHERE
					(lnk.UID = ?) AND (lnk.USERGROUPID = r.ID)
				ORDER BY r.NAME;";
		$result = sYDB()->Execute($sql, $uid);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return array_values($resultarray);
	}


	/**
	 * Checks if this User is part of a specific Usergroup
	 *
	 * @param string $usergroupId Usergroup Id
	 * @return bool TRUE if this User is part of the Usergroup FALSE if not
	 */
	function hasUsergroup($usergroupId) {
		$usergroups = $this->getUsergroups();
		for ($i = 0; $i < count($usergroups); $i++) {
			if ($usergroups[$i]["ID"] == (int)$usergroupId) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Checks if a permission is set for this User
	 *
	 * @param string $permission Permission to check (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @return bool TRUE if the permission is set or FALSE if the permission is not set
	 */
	function checkPermission($permission) {
		$userroles = $this->getUsergroups($this->id);
		for ($i = 0; $i < count($userroles); $i++) {
			$privinfo = sUsergroups()->permissions->getByUsergroup($userroles[$i]["ID"]);
			if ($privinfo[$permission] > 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Adds this User to a Usergroup
	 *
	 * @param int $usergroupId Usergroup Id
	 */
	function addUsergroup($usergroupId) {
		if ($this->hasUsergroup($usergroupId)) return true;
		$this->_usergroups = false;
		$uid = (int)$this->id;
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS') ||
			($uid == $this->_uid)
		) {
			$usergroupId = (int)$usergroupId;
			if ($usergroupId > 0) {
				$sql = "INSERT INTO `yg_user_lnk_usergroups` (`UID`, `USERGROUPID`) VALUES (?, ?);";
				sYDB()->Execute($sql, $uid, $usergroupId);
			}

			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/PERMISSION_CHANGE") == "true") {
				Singleton::FC()->emptyBucket();
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes this User from a Usergroup
	 *
	 * @param int $usergroupId Usergroup Id
	 */
	function removeUsergroup($usergroupId) {
		$uid = (int)$this->id;
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS') ||
			($uid == $this->_uid)
		) {
			$usergroupId = (int)$usergroupId;
			if ($usergroupId > 0) {
				// admin user requires admin role, cannot be removed
				if (sUserMgr()->getAdministratorID() == $uid) {
					if ($usergroupId == (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID")) return false;
				}
				$sql = "DELETE FROM
				`yg_user_lnk_usergroups`
				WHERE
				UID = ? AND USERGROUPID = ?;";
				sYDB()->Execute($sql, $uid, $usergroupId);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Saves all Usergroups for this User (removes all Usergroups first)
	 *
	 * @param array $usergroupIds Array of Usergroup Ids
	 */
	function saveUsergroups($usergroupIds) {
		$uid = (int)$this->id;
		$sql = "DELETE FROM `yg_user_lnk_usergroups` WHERE UID = ?;";
		$result = sYDB()->Execute($sql, $uid);

		for ($i = 0; $i < count($usergroupIds); $i++) {
			$usergroupId = $usergroupIds[$i];
			$sql = "INSERT INTO	`yg_user_lnk_usergroups` (`UID`, `USERGROUPID`) VALUES (?, ?);";
			sYDB()->Execute($sql, $uid, $usergroupId);
		}
	}

}

?>