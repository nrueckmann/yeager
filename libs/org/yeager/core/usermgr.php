<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

\framework\import("org.phpass.PasswordHash");

/**
 * Gets an instance of the UserMgr
 * @return object UserMgr object
 */
function sUserMgr() {
	return Singleton::UserMgr();
}

/**
 * The UserMgr class, which represents an instance of the User manager.
 */
class UserMgr extends \framework\Error {
	private $_db;
	private $_uid;
	private $_isvalidated;
	private $anonymous_uid;
	private $anonymous_group_id;
	private $administrator_uid;
	var $properties;

	/**
	 * Constructor of the UserMgr class
	 */
	public function __construct() {
		$this->_db = sYDB();
		$this->_isvalidated = false;
		$this->_impersonateUserStack = array();
		$this->properties = new PropertySettings("yg_user_props");
	}

	/**
	 * Impersonates and validates the current user using the given User Id
	 *
	 * @param string $userId User Id
	 */
	public function impersonate($userId) {
		array_push($this->_impersonateUserStack, $this->getCurrentUserID());
		$userId = (int)$userId;
		$user = new User($userId);
		$userInfo = $user->get();
		if ($userInfo) {
			$this->_isvalidated = true;
			$this->_uid = $userId;
			foreach(Singleton::$instances as $currSignature => $currInstance) {
				if (strpos($currSignature, 'PageMGR-') === 0) {
					Singleton::unregister($currSignature);
				}
			}
			Singleton::unregister("UserMgr");
			Singleton::unregister("Usergroups");
			Singleton::unregister("cbMgr");
			Singleton::unregister("fileMgr");
			Singleton::unregister("sites");
			Singleton::unregister("mailingMgr");
			Singleton::unregister("Tags");
			Singleton::unregister("templates");
			Singleton::unregister("entrymasks");
			Singleton::unregister("comments");
			Singleton::unregister("filetypes");
			Singleton::unregister("views");
			Singleton::register("UserMgr", $this);
			Singleton::register("Usergroups", new Usergroups());
			Singleton::register("cbMgr", new CblockMgr());
			Singleton::register("fileMgr", new FileMgr());
			Singleton::register("sites", new Sites());
			Singleton::register("mailingMgr", new MailingMgr());
			Singleton::register("Tags", new Tags());
			Singleton::register("templates", new Templates());
			Singleton::register("entrymasks", new Entrymasks());
			Singleton::register("comments", new Comments());
			Singleton::register("filetypes", new Filetypes());
			Singleton::register("views", new Views());
		}
	}

	/**
	 * Unimpersonates and switches back to the original user
	 */
	public function unimpersonate() {
		if (count($this->_impersonateUserStack) > 0) {
			$userId = array_pop($this->_impersonateUserStack);
			$user = new User($userId);
			$userInfo = $user->get();
			if ($userInfo) {
				$this->_isvalidated = true;
				$this->_uid = $userId;
			}
			foreach(Singleton::$instances as $currSignature => $currInstance) {
				if (strpos($currSignature, 'PageMGR-') === 0) {
					Singleton::unregister($currSignature);
				}
			}
			Singleton::unregister("UserMgr");
			Singleton::unregister("Usergroups");
			Singleton::unregister("cbMgr");
			Singleton::unregister("fileMgr");
			Singleton::unregister("sites");
			Singleton::unregister("mailingMgr");
			Singleton::unregister("Tags");
			Singleton::unregister("templates");
			Singleton::unregister("entrymasks");
			Singleton::unregister("comments");
			Singleton::unregister("filetypes");
			Singleton::unregister("views");
			Singleton::register("UserMgr", $this);
			Singleton::register("Usergroups", new Usergroups());
			Singleton::register("cbMgr", new CblockMgr());
			Singleton::register("fileMgr", new FileMgr());
			Singleton::register("sites", new Sites());
			Singleton::register("mailingMgr", new MailingMgr());
			Singleton::register("Tags", new Tags());
			Singleton::register("templates", new Templates());
			Singleton::register("entrymasks", new Entrymasks());
			Singleton::register("comments", new Comments());
			Singleton::register("filetypes", new Filetypes());
			Singleton::register("views", new Views());
		}
	}

	/**
	 * Validates the current user using the given username and password
	 *
	 * @param string $username Username
	 * @param string $password Password
	 * @return int|bool User Id or FALSE if an error has occured
	 */
	public function validate($username, $password) {

		$passwordHash = new PasswordHash(8, false);

		$this->_isvalidated = false;
		if (strlen($password) < 1) {
			return false;
		}
		$username = sYDB()->escape_string(sanitize($username));
		$password = sYDB()->escape_string(sanitize($password));

		$sql = "SELECT ID, PASSWORD FROM yg_user WHERE LOGIN = ?;";
		$result = sYDB()->Execute($sql, $username);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		$success = $passwordHash->CheckPassword($password, $resultarray[0]['PASSWORD']);

		if ($success) {
			$this->_isvalidated = true;
			$this->_uid = $resultarray[0]['ID'];
			return $resultarray[0]['ID'];
		}
		$this->_isvalidated = false;
		return false;
	}

	/**
	 * Gets the User Id of the Anonymous-User
	 *
	 * @return int User Id
	 */
	function getAnonymousID() {
		if ($this->anonymous_uid < 1) {
			$this->anonymous_uid = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONUSERID");
		}
		return $this->anonymous_uid;
	}

	/**
	 * Gets the User Id of the Anonymous Usergroup
	 *
	 * @return int User Id
	 */
	function getAnonymousGroupID() {
		if ($this->anonymous_group_id < 1) {
			$this->anonymous_group_id = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONGROUPID");
		}
		return $this->anonymous_group_id;
	}

	/**
	 * Gets the User Id of the Administrator-User
	 *
	 * @return int User Id
	 */
	function getAdministratorID() {
		if ($this->administrator_uid < 1) {
			$this->administrator_uid = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTUSERID");
		}
		return $this->administrator_uid;
	}

	/**
	 * Gets the User Id of the currently logged-in User
	 *
	 * @return int User Id
	 */
	public function getCurrentUserID() {
		if ($this->_isvalidated) {
			return $this->_uid;
		} else {
			return $this->getAnonymousID();
		}
	}

	/**
	 * Gets a User Id by Lock token
	 *
	 * @param string $token Token
	 * @return int|bool User Id or FALSE if no User Id is found for the specified Token
	 */
	function getUserIdByToken($token) {
		$uid = (int)$this->id;
		$token = sYDB()->escape_string(sanitize($token));

		$ts = time();
		$sql = "SELECT * FROM `yg_user_tokens` WHERE `TOKEN` = ? AND TS >= ?;";
		$result = sYDB()->Execute($sql, $token, $ts);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		if (count($resultarray) > 0) {
			return $resultarray[0]['UID'];
		} else {
			return false;
		}
	}

	/**
	 * Gets a List of Users
	 *
	 * @param string $orderby SQL-order-by-clause
	 * @param string $sort SQL-sort-clause
	 * @param string $limit SQL-limit-clause
	 * @param string $searchText (optional) Searchtext
	 * @return array|bool Array or Users or FALSE in case of an error
	 */
	function getList($orderby = '', $sort = '', $limit = '', $searchText = NULL) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");

			$orderby = sYDB()->escape_string(sanitize($orderby));
			$sort = sYDB()->escape_string(sanitize($sort));
			$limit = sYDB()->escape_string(sanitize($limit));
			$limitsql = "";
			$sqlargs = array();
			$currUser = new User(sUserMgr()->getCurrentUserID());
			$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE, MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RSEND) AS RSEND";
			$perm_sql_from = " LEFT JOIN yg_usergroups_permissions AS perm ON perm.OID = ug.USERGROUPID";

			$perm_sql_where = " AND (";
			$roles = $currUser->getUsergroups();
			for ($r = 0; $r < count($roles); $r++) {
				$perm_sql_where .= "(perm.USERGROUPID = ?)";
				array_push($sqlargs, $roles[$r]["ID"]);
				if ((count($roles) - $r) > 1) {
					$perm_sql_where .= " OR ";
				}
			}
			$perm_sql_where .= ") ";
			$perm_sql_where .= " AND ((RREAD >= 1) OR (perm.USERGROUPID = ?)) ";
			array_push($sqlargs, $rootGroupId);

			$searchSQL = '';
			if (strlen($searchText)) {
				$searchText = "%".sYDB()->escape_string(sanitize($searchText))."%";
				$searchSQL = "AND (
								(yg_user_propsv.LASTNAME LIKE ?) OR
								(yg_user_propsv.FIRSTNAME LIKE ?) OR
								(yg_user_propsv.EMAIL LIKE ?) OR
								(yg_user_propsv.COMPANY LIKE ?) OR
								(yg_user_propsv.DEPARTMENT LIKE ?) OR
								(yg_user_propsv.FAX LIKE ?) OR
								(yg_user_propsv.PHONE LIKE ?)
							)";
				array_push($sqlargs, $searchText, $searchText, $searchText, $searchText, $searchText, $searchText, $searchText);
			}
			if (strlen($orderby) < 1) {
				$orderby = "LASTNAME";
			}
			if (strlen($sort) < 1) {
				$sort = "ASC";
			} else if ($sort != "ASC") {
				$sort = "DESC";
			}
			if ($limit) {
				$limitarr = explode(",", $limit);
				$limitsql = "LIMIT " . (int)$limitarr[0]. "," . (int)$limitarr[1];
			}

			$sql = "SELECT
						u.LOGIN AS LOGIN,
						u.PASSWORD AS PASSWORD,
						u.ID AS ID,
						yg_user_propsv.LASTNAME AS LASTNAME,
						yg_user_propsv.FIRSTNAME AS FIRSTNAME,
						yg_user_propsv.EMAIL AS EMAIL
						$perm_sql_select
					FROM
						yg_user as u
					LEFT JOIN
						yg_user_lnk_usergroups AS ug ON u.ID = ug.UID
					LEFT JOIN
						yg_user_propsv ON u.ID = yg_user_propsv.OID
					$perm_sql_from
					WHERE
						1
						$perm_sql_where
						$searchSQL
					GROUP BY
						u.ID
					ORDER BY `$orderby` $sort $limitsql;";

			array_unshift($sqlargs, $sql);
			$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $dbr->GetArray();

			return $resultarray;
		} else {
			return false;
		}
	}

	/**
	 * Gets the count of Users
	 * @param string $searchText (optional) Searchtext
	 *
	 * @return int|bool Usercount or FALSE in case of an error
	 */
	function getListCount($searchText = NULL) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");
			$sqlargs = array();
			$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE, MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RSEND) AS RSEND";
			$perm_sql_from = " LEFT JOIN yg_usergroups_permissions AS perm ON perm.OID = ug.USERGROUPID";

			$currUser = new User(sUserMgr()->getCurrentUserID());
			$perm_sql_where = " AND (";
			$roles = $currUser->getUsergroups();
			for ($r = 0; $r < count($roles); $r++) {
				$perm_sql_where .= "(perm.USERGROUPID = ?) ";
				array_push($sqlargs, $roles[$r]["ID"]);
				if ((count($roles) - $r) > 1) {
					$perm_sql_where .= " OR ";
				}
			}
			$perm_sql_where .= ") ";
			$perm_sql_where .= " AND ((RREAD >= 1) OR (perm.USERGROUPID = ?)) ";
			array_push($sqlargs, $rootGroupId);

			$searchSQL = '';
			if (strlen($searchText)) {
				$searchText = "%".sYDB()->escape_string(sanitize($searchText))."%";
				$searchSQL = "AND (
								(yg_user_propsv.LASTNAME LIKE ?) OR
								(yg_user_propsv.FIRSTNAME LIKE ?) OR
								(yg_user_propsv.EMAIL LIKE ?) OR
								(yg_user_propsv.COMPANY LIKE ?) OR
								(yg_user_propsv.DEPARTMENT LIKE ?) OR
								(yg_user_propsv.FAX LIKE ?) OR
								(yg_user_propsv.PHONE LIKE ?)
							)";
				array_push($sqlargs, $searchText, $searchText, $searchText, $searchText, $searchText, $searchText, $searchText);
			}

			$sql = "SELECT
						u.LOGIN AS LOGIN,
						u.PASSWORD AS PASSWORD,
						u.ID AS ID,
						yg_user_propsv.LASTNAME AS LASTNAME,
						yg_user_propsv.FIRSTNAME AS FIRSTNAME,
						yg_user_propsv.EMAIL AS EMAIL
						$perm_sql_select
					FROM
						yg_user as u
					LEFT JOIN
						yg_user_lnk_usergroups AS ug ON u.ID = ug.UID
					LEFT JOIN
						yg_user_propsv ON u.ID = yg_user_propsv.OID
					$perm_sql_from
					WHERE
						1
						$perm_sql_where
						$searchSQL
					GROUP BY
						u.ID;";

			array_unshift($sqlargs, $sql);
			$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $dbr->GetArray();

			return count($resultarray);
		} else {
			return false;
		}
	}

	/**
	 * Adds a User
	 *
	 * @param string $name (optional) Username
	 * @return int|bool New User Id or FALSE in case of an error
	 */
	function add($name = 'n/a') {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$name = sYDB()->escape_string(sanitize($name));
			$sql = "INSERT INTO yg_user (LOGIN, PASSWORD) VALUES (?, '');";
			sYDB()->Execute($sql, $name);
			$uid = sYDB()->Insert_ID();

			if ($uid < 1) {
				return false;
			} else {
				$newUser = new User($uid);
				$newUser->addUsergroup($this->getAnonymousGroupID());
				return $uid;
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets a User
	 *
	 * @param string $userId User Id
	 * @return User|bool User object or FALSE in case of an error
	 */
	function getUser($userId) {
		$userId = (int)$userId;
		if ($userId == $this->getCurrentUserID() || sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			try {
				return new User($userId);
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets path to User image
	 *
	 * @param string $userId User Id
	 * @param string $width (optional) width in pixels
	 * @param string $height (optional) height in pixels
	 * @return User|bool User image path or FALSE in case of an error
	 */
	function getUserImage($userId, $width = 0, $height = 0) {
		if (file_exists(sApp()->approot.sApp()->userpicdir.$userId.'-picture.jpg')) {
			$imagePath = sApp()->webroot.'userimage/'.$userId;
			if ($width != 0) {
				$imagePath .= '/'.$width."x".$height;
			}
			return $imagePath;
		}
		return false;
	}


	/**
	 * Gets a User by the specified login / email address
	 *
	 * @param string $login Login / email address
	 * @return array|false Array containing User information or FALSE in case of an error
	 */
	function getByLogin($login) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$login = sYDB()->escape_string(sanitize($login));
			if (strlen($login) > 0) {
				$sql = "SELECT
				u.LOGIN AS LOGIN,
				u.PASSWORD AS PASSWORD,
				u.ID AS ID,
				yg_user_propsv.*
				FROM
				yg_user as u
				LEFT JOIN yg_user_propsv ON u.ID = yg_user_propsv.OID
				WHERE
				(LOWER(u.LOGIN) = LOWER(?));";

				$result = sYDB()->Execute($sql, $login);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$resultarray = $result->GetArray();
			}
			return $resultarray[0];
		} else {
			return false;
		}
	}

	/**
	 * Gets a User by a MD5 hash of the specified login/email
	 *
	 * @param string $hash MD5 hashed login / email address
	 * @param string $salt (optional) Salt
	 * @return int|bool Userinfo or FALSE in case of an error
	 */
	function getByHash($hash, $salt) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$salt = sYDB()->escape_string(sanitize($salt));
			$hash = sYDB()->escape_string(sanitize($hash));
			if (strlen($hash) > 0) {
				$sql = "SELECT
				u.LOGIN AS LOGIN,
				u.PASSWORD AS PASSWORD,
				u.ID AS ID,
				yg_user_propsv.*
				FROM
				yg_user as u
				LEFT JOIN yg_user_propsv ON u.ID = yg_user_propsv.OID
				WHERE
				MD5(CONCAT(LOWER(u.LOGIN), ?)) = ?;";
				$result = sYDB()->Execute($sql, $salt, $hash);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$resultarray = $result->GetArray();
			}
			return $resultarray[0];
		} else {
			return false;
		}
	}

	/**
	 * Gets a User by a specific property value
	 *
	 * @param string $property Property identifier
	 * @param string $value Property value
	 * @param bool (optional) $casesensitive TRUE if search should consider case sensitivity FALSE if not
	 * @return int|bool Userinfo or FALSE in case of an error
	 */
	function getByProperty($property, $value, $casesensitive = true) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$property = sYDB()->escape_string(sanitize($property));
			if (strlen($property) > 0) {
				$sql = "SELECT
							u.LOGIN AS LOGIN,
							u.PASSWORD AS PASSWORD,
							u.ID AS ID,
							yg_user_propsv.*
						FROM
							yg_user as u
						LEFT JOIN
							yg_user_propsv ON u.ID = yg_user_propsv.OID
						WHERE ";
				if ($casesensitive) {
					$sql .= "yg_user_propsv.`$property` = ?;";
				} else {
					$sql .= "LOWER(yg_user_propsv.`$property`) = LOWER(?);";
				}
				$result = sYDB()->Execute($sql, $value);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$resultarray = $result->GetArray();
			}
			return $resultarray[0];
		} else {
			return false;
		}
	}

	/**
	 * Gets the count of Users in a specified Usergroup
	 *
	 * @param int $usergroupId (optional) Usergroup Id
	 * @param string $searchText (optional) Searchtext
	 * @return int|bool Count of Users or FALSE in case of an error
	 */
	function getUsergroupCount($usergroupId, $searchText = NULL) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$usergroupId = (int)$usergroupId;
			$searchSQL = '';
			$sqlargs = array();
			array_push($sqlargs, $usergroupId);
			if (strlen($searchText)) {
				$searchText = "%".sYDB()->escape_string(sanitize($searchText))."%";
				$searchSQL = "AND (
								(yg_user_propsv.LASTNAME LIKE ?) OR
								(yg_user_propsv.FIRSTNAME LIKE ?) OR
								(yg_user_propsv.EMAIL LIKE ?) OR
								(yg_user_propsv.COMPANY LIKE ?) OR
								(yg_user_propsv.DEPARTMENT LIKE ?) OR
								(yg_user_propsv.FAX LIKE ?) OR
								(yg_user_propsv.PHONE LIKE ?)
							)";
				array_push($sqlargs, $searchText, $searchText, $searchText, $searchText, $searchText, $searchText, $searchText);
			}
			$sql = "SELECT
						COUNT(*) AS CNT
					FROM
						yg_user as u
					LEFT JOIN
						yg_user_lnk_usergroups as lnk ON u.ID = lnk.UID
					LEFT JOIN
						yg_user_propsv ON u.ID = yg_user_propsv.OID
					WHERE
						(lnk.USERGROUPID = ?) $searchSQL;";

			array_unshift($sqlargs, $sql);
			$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $dbr->GetArray();
			return $resultarray[0]['CNT'];
		} else {
			return false;
		}
	}

/// @cond DEV

	/**
	 * Gets the count of Users without an assigned Usergroup
	 * @param string $searchText (optional) Searchtext
	 *
	 * @return int|bool Count of Users or FALSE in case of an error
	 */
	function getWithoutUsergroupCount($searchText = NULL) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$anonGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONGROUPID");
			$searchSQL = '';
			$sqlargs = array();
			array_push($sqlargs, $anonGroupId);
			if (strlen($searchText)) {
				$searchText = "%".sYDB()->escape_string(sanitize($searchText))."%";
				$searchSQL = "AND (
								(yg_user_propsv.LASTNAME LIKE ?) OR
								(yg_user_propsv.FIRSTNAME LIKE ?) OR
								(yg_user_propsv.EMAIL LIKE ?) OR
								(yg_user_propsv.COMPANY LIKE ?) OR
								(yg_user_propsv.DEPARTMENT LIKE ?) OR
								(yg_user_propsv.FAX LIKE ?) OR
								(yg_user_propsv.PHONE LIKE ?)
							)";
				array_push($sqlargs, $searchText, $searchText, $searchText, $searchText, $searchText, $searchText, $searchText);
			}

			$sql = "SELECT
						u.LOGIN AS LOGIN,
						u.PASSWORD AS PASSWORD,
						u.ID AS ID,
						u.ID AS UID,
						yg_user_propsv.LASTNAME AS LASTNAME,
						yg_user_propsv.FIRSTNAME AS FIRSTNAME,
						yg_user_propsv.EMAIL AS EMAIL,
						yg_user_propsv.COMPANY AS COMPANY,
						yg_user_propsv.DEPARTMENT AS DEPARTMENT,
						yg_user_propsv.FAX AS FAX,
						yg_user_propsv.PHONE AS PHONE,
						count(lnk.UID) AS GROUPCOUNT,
						lnk2.USERGROUPID
					FROM
						yg_user as u
					LEFT JOIN
						yg_user_lnk_usergroups as lnk ON u.ID = lnk.UID
					LEFT JOIN
						yg_user_lnk_usergroups as lnk2 ON (u.ID = lnk2.UID) AND ((lnk2.USERGROUPID = ?) OR (lnk2.USERGROUPID = NULL))
					LEFT JOIN
						yg_user_propsv ON u.ID = yg_user_propsv.OID
					WHERE
						1 $searchSQL
					GROUP BY
						u.ID
					HAVING
						(GROUPCOUNT = 1 AND lnk2.USERGROUPID = ?) OR
						(GROUPCOUNT = 0);";
			array_push($sqlargs, $anonGroupId);
			array_unshift($sqlargs, $sql);
			$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $dbr->GetArray();

			return count($resultarray);
		} else {
			return false;
		}
	}

/// @endcond

	/**
	 * Removes the specified User
	 *
	 * @return bool TRUE if User has been removed or FALSE in case of an error
	 */
	function remove($uid) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$uid = (int)$uid;
			if ($uid == (int)sConfig()->getVar('CONFIG/SYSTEMUSERS/ROOTUSERID')) {
				return false;
			} // do not allow root user to be deleted
			if ($uid == (int)sConfig()->getVar('CONFIG/SYSTEMUSERS/ANONUSERID')) {
				return false;
			} // do not allow anon user to be deleted
			$sql = "DELETE FROM yg_user WHERE ID = ?";
			sYDB()->Execute($sql, $uid);
			$sql = "DELETE FROM yg_user_lnk_usergroups WHERE UID = ?";
			sYDB()->Execute($sql, $uid);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Verifies the minimum required strength of a password
	 *
	 * @param int $password Password to check
	 *
	 * @return bool TRUE if verification suceeded FALSE if not
	 */
	function verifyPasswordStrength($password) {
		if (strlen($password) < 6) {
			return false;
		}
		if (!preg_match("#[0-9]+#", $password)) {
			return false;
		}
		if (!preg_match("#[a-z]+#", $password)) {
			return false;
		}
		return true;
	}

	/**
	 * Gets the Users in a specified Usergroup
	 *
	 * @param int $usergroupId Usergroup-Id
	 * @param string $order SQL-order-by-clause
	 * @param string $sort SQL-sort-clause
	 * @param string $limit SQL-limit-clause
	 * @param string $searchText (optional) Searchtext
	 * @return array|bool Array of Users or FALSE in case of an error
	 */
	function getByUsergroup($usergroupId, $order = '', $sort = 'ASC', $limit = '', $searchText = NULL) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$sqlargs = array();
			$usergroupId = (int)$usergroupId;
			$order = sYDB()->escape_string(sanitize($order));
			$sort = (int)sYDB()->escape_string(sanitize($sort));
			$limit = sYDB()->escape_string(sanitize($limit));
			$limitsql = "";
			$currUser = new User(sUserMgr()->getCurrentUserID());

			if (strlen($order) < 1) {
				$ordersql = "LASTNAME";
			}
			if (strlen($order) > 0) {
				$ordersql = $order;
			}
			if ($order == "FIRSTNAME") {
				$ordersql = "NAME ASC, EMAIL ASC";
			}
			if ($sort == "ASC") {
				$sortsql = "ASC";
			} else {
				$sortsql = "DESC";
			}
			if ($limit) {
				$limitarr = explode(",", $limit);
				$limitsql = "LIMIT " . (int)$limitarr[0]. "," . (int)$limitarr[1];
			}
			$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE, MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RSEND) AS RSEND";
			$perm_sql_from = " LEFT JOIN yg_usergroups_permissions AS perm ON perm.OID = lnk.USERGROUPID";

			array_push($sqlargs, $usergroupId);

			$perm_sql_where = " AND (";
			$roles = $currUser->getUsergroups();
			for ($r = 0; $r < count($roles); $r++) {
				array_push($sqlargs, $roles[$r]["ID"]);
				$perm_sql_where .= "(perm.USERGROUPID = ?) ";
				if ((count($roles) - $r) > 1) {
					$perm_sql_where .= " OR ";
				}
			}
			$perm_sql_where .= ") ";

			$searchSQL = '';
			if (strlen($searchText)) {
				$searchText = "%".sYDB()->escape_string(sanitize($searchText))."%";
				$properties = $currUser->properties->getList();
				$searchSQL = "AND (";
				for ($i = 0; $i < count($properties); $i++) {
					if ($i != 0) $searchSQL .= " OR ";
					$searchSQL .= "(yg_user_propsv.".sYDB()->escape_string(sanitize($properties[$i]["IDENTIFIER"]))." LIKE ?)";
					array_push($sqlargs, $searchText);
				}
				$searchSQL .= ")";
			}

			$sql = "SELECT
						u.LOGIN AS LOGIN,
						u.PASSWORD AS PASSWORD,
						u.ID AS ID,
						u.ID AS UID,
						yg_user_propsv.LASTNAME AS LASTNAME,
						yg_user_propsv.FIRSTNAME AS FIRSTNAME,
						yg_user_propsv.EMAIL AS EMAIL
						$perm_sql_select
					FROM
						yg_user as u
					LEFT JOIN
						yg_user_lnk_usergroups as lnk ON u.ID = lnk.UID
					LEFT JOIN
						yg_user_propsv ON u.ID = yg_user_propsv.OID
					$perm_sql_from
					WHERE
						(lnk.USERGROUPID = ?)
						$perm_sql_where
						$searchSQL
					GROUP BY
						u.ID
					HAVING
						(RREAD >= 1)
					ORDER BY `$ordersql` $sortsql $limitsql;";

			array_unshift($sqlargs, $sql);
			$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $dbr->GetArray();
			return $resultarray;
		} else {
			return false;
		}
	}

	/**
	 * Gets all Users without an assigned Usergroup
	 *
	 * @param string $order SQL-order-by-clause
	 * @param string $sort SQL-sort-clause
	 * @param string $limit SQL-limit-clause
	 * @param string $searchText (optional) Searchtext
	 * @return array|bool Array of Users or FALSE in case of an error
	 */
	function getWithoutUsergroup($order = '', $sort = 'ASC', $limit = '', $searchText = NULL) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$anonGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONGROUPID");
			$order = sYDB()->escape_string(sanitize($order));
			$sort = sYDB()->escape_string(sanitize($sort));
			$limit = sYDB()->escape_string(sanitize($limit));
			$sqlargs = array();
			array_push($sqlargs, $anonGroupId);
			$searchSQL = '';
			if (strlen($searchText)) {
				$searchText = "%".sYDB()->escape_string(sanitize($searchText))."%";
				$searchSQL = "AND (
								(yg_user_propsv.LASTNAME LIKE ?) OR
								(yg_user_propsv.FIRSTNAME LIKE ?) OR
								(yg_user_propsv.EMAIL LIKE ?) OR
								(yg_user_propsv.COMPANY LIKE ?) OR
								(yg_user_propsv.DEPARTMENT LIKE ?) OR
								(yg_user_propsv.FAX LIKE ?) OR
								(yg_user_propsv.PHONE LIKE ?)
							)";
				array_push($sqlargs, $searchText, $searchText, $searchText, $searchText, $searchText, $searchText, $searchText);
			}
			if (strlen($order) < 1) {
				$ordersql = "LASTNAME";
			}
			if (strlen($order) > 0) {
				$ordersql = $order;
			}
			if ($order == "FIRSTNAME") {
				$ordersql = "NAME ASC, EMAIL ASC";
			}
			if ($sort == "ASC") {
				$sortsql = "ASC";
			} else {
				$sortsql = "DESC";
			}
			if ($limit) {
				$limitarr = explode(",", $limit);
				$limitsql = "LIMIT " . (int)$limitarr[0]. "," . (int)$limitarr[1];
			}

			$sql = "SELECT
						u.LOGIN AS LOGIN,
						u.PASSWORD AS PASSWORD,
						u.ID AS ID,
						u.ID AS UID,
						yg_user_propsv.LASTNAME AS LASTNAME,
						yg_user_propsv.FIRSTNAME AS FIRSTNAME,
						yg_user_propsv.EMAIL AS EMAIL,
						yg_user_propsv.COMPANY AS COMPANY,
						yg_user_propsv.DEPARTMENT AS DEPARTMENT,
						yg_user_propsv.FAX AS FAX,
						yg_user_propsv.PHONE AS PHONE,
						count(lnk.UID) AS GROUPCOUNT,
						lnk2.USERGROUPID
					FROM
						yg_user as u
					LEFT JOIN
						yg_user_lnk_usergroups as lnk ON u.ID = lnk.UID
					LEFT JOIN
						yg_user_lnk_usergroups as lnk2 ON (u.ID = lnk2.UID) AND ((lnk2.USERGROUPID = ?) OR  (lnk2.USERGROUPID = NULL))
					LEFT JOIN
						yg_user_propsv ON u.ID = yg_user_propsv.OID
					WHERE
						1 $searchSQL
					GROUP BY
						u.ID
					HAVING
						(GROUPCOUNT = 1 AND lnk2.USERGROUPID = ?) OR
						(GROUPCOUNT = 0)
					ORDER BY `$ordersql` $sortsql $limitsql;";

			array_push($sqlargs, $anonGroupId);
			array_unshift($sqlargs, $sql);
			$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
			if ($dbr === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $dbr->GetArray();
			return $resultarray;
		} else {
			return false;
		}
	}

	/**
	 * Searches for Users by email address
	 *
	 * @param string $email Email to search for
	 * @return array|false Array containing Users or FALSE in case of an error
	 */
	function getAllByEmail($email) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$email = sYDB()->escape_string(sanitize($email));
			if (strlen($email) > 0) {
				$email = "%".$email."%";
				$sql = "SELECT
							u.LOGIN AS LOGIN,
							u.PASSWORD AS PASSWORD,
							u.ID AS ID,
							yg_user_propsv.LASTNAME AS LASTNAME,
							yg_user_propsv.FIRSTNAME AS FIRSTNAME,
							yg_user_propsv.EMAIL AS EMAIL,
							yg_user_propsv.COMPANY AS COMPANY,
							yg_user_propsv.DEPARTMENT AS DEPARTMENT,
							yg_user_propsv.FAX AS FAX,
							yg_user_propsv.PHONE AS PHONE
						FROM
							yg_user as u
						LEFT JOIN
							yg_user_propsv ON u.ID = yg_user_propsv.OID
						WHERE
							(yg_user_propsv.EMAIL like ?);";
				$result = sYDB()->Execute($sql, $email);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$resultarray = $result->GetArray();
			}
			return $resultarray;
		}
	}

	/**
	 * Searches for a single User by email address
	 *
	 * @param string $email Email to search for
	 * @param bool $exact TRUE if an exact search should be performed
	 * @return array|false Array containing User information or FALSE in case of an error
	 */
	function getByEmail($email, $exact = false) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$email = sYDB()->escape_string(sanitize($email));
			if ($exact !== true) {
				$email = "%".$email."%";
			}
			if (strlen($email) > 0) {
				$sql = "SELECT u.LOGIN AS LOGIN,
				u.PASSWORD AS PASSWORD,
				u.ID AS ID
				FROM
				yg_user as u
				LEFT JOIN yg_user_propsv ON u.ID = yg_user_propsv.OID
				WHERE
				(yg_user_propsv.EMAIL LIKE ?);";
				$result = sYDB()->Execute($sql, $email);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$resultarray = $result->GetArray();
			}
			return $resultarray[0];
		} else {
			return false;
		}
	}

}

?>