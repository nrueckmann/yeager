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
		$username = mysql_real_escape_string(sanitize($username));
		$password = mysql_real_escape_string(sanitize($password));

		$sql = "SELECT ID, PASSWORD FROM yg_user WHERE LOGIN = '$username';";
		$result = sYDB()->Execute($sql);
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

		$ts = time();
		$sql = "SELECT * FROM `yg_user_tokens` WHERE `TOKEN` = '" . $token . "' AND TS >= " . $ts . ";";
		$result = sYDB()->Execute($sql);
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

			$orderby = mysql_real_escape_string(sanitize($orderby));
			$sort = mysql_real_escape_string(sanitize($sort));
			$limit = mysql_real_escape_string(sanitize($limit));
			$searchSQL = '';
			if (strlen($searchText)) {
				$searchText = mysql_real_escape_string(sanitize($searchText));
				$searchSQL = "AND (
								(yg_user_propsv.LASTNAME LIKE '%".$searchText."%') OR
								(yg_user_propsv.FIRSTNAME LIKE '%".$searchText."%') OR
								(yg_user_propsv.EMAIL LIKE '%".$searchText."%') OR
								(yg_user_propsv.COMPANY LIKE '%".$searchText."%') OR
								(yg_user_propsv.DEPARTMENT LIKE '%".$searchText."%') OR
								(yg_user_propsv.FAX LIKE '%".$searchText."%') OR
								(yg_user_propsv.PHONE LIKE '%".$searchText."%')
							)";
			}
			if (strlen($orderby) < 1) {
				$orderby = "LASTNAME";
			}
			if (strlen($sort) < 1) {
				$sort = "ASC";
			}
			if ($limit) {
				$limit = "LIMIT " . $limit;
			}

			$currUser = new User(sUserMgr()->getCurrentUserID());
			$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE, MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RSEND) AS RSEND";
			$perm_sql_from = " LEFT JOIN yg_usergroups_permissions AS perm ON perm.OID = ug.USERGROUPID";

			$perm_sql_where = " AND (";
			$roles = $currUser->getUsergroups();
			for ($r = 0; $r < count($roles); $r++) {
				$perm_sql_where .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
				if ((count($roles) - $r) > 1) {
					$perm_sql_where .= " OR ";
				}
			}
			$perm_sql_where .= ") ";
			$perm_sql_where .= " AND ((RREAD >= 1) OR (perm.USERGROUPID = $rootGroupId)) ";

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
					ORDER BY $orderby $sort
					$limit;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();

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

			$searchSQL = '';
			if (strlen($searchText)) {
				$searchText = mysql_real_escape_string(sanitize($searchText));
				$searchSQL = "AND (
								(yg_user_propsv.LASTNAME LIKE '%".$searchText."%') OR
								(yg_user_propsv.FIRSTNAME LIKE '%".$searchText."%') OR
								(yg_user_propsv.EMAIL LIKE '%".$searchText."%') OR
								(yg_user_propsv.COMPANY LIKE '%".$searchText."%') OR
								(yg_user_propsv.DEPARTMENT LIKE '%".$searchText."%') OR
								(yg_user_propsv.FAX LIKE '%".$searchText."%') OR
								(yg_user_propsv.PHONE LIKE '%".$searchText."%')
							)";
			}

			$currUser = new User(sUserMgr()->getCurrentUserID());
			$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE, MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RSEND) AS RSEND";
			$perm_sql_from = " LEFT JOIN yg_usergroups_permissions AS perm ON perm.OID = ug.USERGROUPID";

			$perm_sql_where = " AND (";
			$roles = $currUser->getUsergroups();
			for ($r = 0; $r < count($roles); $r++) {
				$perm_sql_where .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
				if ((count($roles) - $r) > 1) {
					$perm_sql_where .= " OR ";
				}
			}
			$perm_sql_where .= ") ";
			$perm_sql_where .= " AND ((RREAD >= 1) OR (perm.USERGROUPID = $rootGroupId)) ";

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
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();

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
			$name = mysql_real_escape_string(sanitize($name));
			$sql = "INSERT INTO yg_user (LOGIN, PASSWORD) VALUES ('$name', '');";
			sYDB()->Execute($sql);
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
			$login = mysql_real_escape_string(sanitize($login));
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
				(LOWER(u.LOGIN) = LOWER('$login'));";

				$result = sYDB()->Execute($sql);
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
			$salt = mysql_real_escape_string(sanitize($salt));
			$hash = mysql_real_escape_string(sanitize($hash));
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
				MD5(CONCAT(LOWER(u.LOGIN), '$salt')) = '$hash';";
				$result = sYDB()->Execute($sql);
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
			$property = mysql_real_escape_string(sanitize($property));
			$value = mysql_real_escape_string(sanitize($value));
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
					$sql .= "yg_user_propsv.$property = '$value';";
				} else {
					$sql .= "LOWER(yg_user_propsv.$property) = LOWER('$value');";
				}
				$result = sYDB()->Execute($sql);
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
			if (strlen($searchText)) {
				$searchText = mysql_real_escape_string(sanitize($searchText));
				$searchSQL = "AND (
								(yg_user_propsv.LASTNAME LIKE '%".$searchText."%') OR
								(yg_user_propsv.FIRSTNAME LIKE '%".$searchText."%') OR
								(yg_user_propsv.EMAIL LIKE '%".$searchText."%') OR
								(yg_user_propsv.COMPANY LIKE '%".$searchText."%') OR
								(yg_user_propsv.DEPARTMENT LIKE '%".$searchText."%') OR
								(yg_user_propsv.FAX LIKE '%".$searchText."%') OR
								(yg_user_propsv.PHONE LIKE '%".$searchText."%')
							)";
			}

			$currUser = new User(sUserMgr()->getCurrentUserID());
			$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE, MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RSEND) AS RSEND";
			$perm_sql_from = " LEFT JOIN yg_usergroups_permissions AS perm ON perm.OID = lnk.USERGROUPID";

			$perm_sql_where = " AND (";
			$roles = $currUser->getUsergroups();
			for ($r = 0; $r < count($roles); $r++) {
				$perm_sql_where .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
				if ((count($roles) - $r) > 1) {
					$perm_sql_where .= " OR ";
				}
			}
			$perm_sql_where .= ") ";

			$sql = "SELECT
						COUNT(*) AS CNT
					FROM
						yg_user as u
					LEFT JOIN
						yg_user_lnk_usergroups as lnk ON u.ID = lnk.UID
					LEFT JOIN
						yg_user_propsv ON u.ID = yg_user_propsv.OID
					WHERE
						(lnk.USERGROUPID = $usergroupId) $searchSQL;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
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
			if (strlen($searchText)) {
				$searchText = mysql_real_escape_string(sanitize($searchText));
				$searchSQL = "AND (
								(yg_user_propsv.LASTNAME LIKE '%".$searchText."%') OR
								(yg_user_propsv.FIRSTNAME LIKE '%".$searchText."%') OR
								(yg_user_propsv.EMAIL LIKE '%".$searchText."%') OR
								(yg_user_propsv.COMPANY LIKE '%".$searchText."%') OR
								(yg_user_propsv.DEPARTMENT LIKE '%".$searchText."%') OR
								(yg_user_propsv.FAX LIKE '%".$searchText."%') OR
								(yg_user_propsv.PHONE LIKE '%".$searchText."%')
							)";
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
						yg_user_lnk_usergroups as lnk2 ON (u.ID = lnk2.UID) AND ((lnk2.USERGROUPID = $anonGroupId) OR (lnk2.USERGROUPID = NULL))
					LEFT JOIN
						yg_user_propsv ON u.ID = yg_user_propsv.OID
					WHERE
						1 $searchSQL
					GROUP BY
						u.ID
					HAVING
						(GROUPCOUNT = 1 AND lnk2.USERGROUPID = $anonGroupId) OR
						(GROUPCOUNT = 0);";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();

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
			$sql = "DELETE FROM yg_user WHERE ID = $uid";
			$result = sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_user_lnk_usergroups WHERE UID = $uid";
			$result = sYDB()->Execute($sql);
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
	function getByUsergroup($usergroupId, $order = '', $sort = '', $limit = '', $searchText = NULL) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$usergroupId = (int)$usergroupId;
			$order = mysql_real_escape_string(sanitize($order));
			$sort = mysql_real_escape_string(sanitize($sort));
			$limit = mysql_real_escape_string(sanitize($limit));
			$currUser = new User(sUserMgr()->getCurrentUserID());
			$searchSQL = '';
			if (strlen($searchText)) {
				$searchText = mysql_real_escape_string(sanitize($searchText));
				$properties = $currUser->properties->getList();
				$searchSQL = "AND (";
				for ($i = 0; $i < count($properties); $i++) {
					if ($i != 0) $searchSQL .= " OR ";
					$searchSQL .= "(yg_user_propsv.".$properties[$i]["IDENTIFIER"]." LIKE '%".$searchText."%')";
				}
				$searchSQL .= ")";
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
			if (strlen($sort) < 1) {
				$sortsql = "ASC";
			}
			if (strlen($sort) > 0) {
				$sortsql = $sort;
			}
			if ($limit) {
				$limitsql = "LIMIT " . $limit;
			}


			$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE, MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RSEND) AS RSEND";
			$perm_sql_from = " LEFT JOIN yg_usergroups_permissions AS perm ON perm.OID = lnk.USERGROUPID";

			$perm_sql_where = " AND (";
			$roles = $currUser->getUsergroups();
			for ($r = 0; $r < count($roles); $r++) {
				$perm_sql_where .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
				if ((count($roles) - $r) > 1) {
					$perm_sql_where .= " OR ";
				}
			}
			$perm_sql_where .= ") ";

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
						(lnk.USERGROUPID = $usergroupId)
						$perm_sql_where
						$searchSQL
					GROUP BY
						u.ID
					HAVING
						(RREAD >= 1)
					ORDER BY $ordersql $sortsql $limitsql;";

			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
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
	function getWithoutUsergroup($order = '', $sort = '', $limit = '', $searchText = NULL) {
		if (sUsergroups()->permissions->check($this->_uid, 'RUSERS')) {
			$order = mysql_real_escape_string(sanitize($order));
			$sort = mysql_real_escape_string(sanitize($sort));
			$limit = mysql_real_escape_string(sanitize($limit));
			$searchSQL = '';
			if (strlen($searchText)) {
				$searchText = mysql_real_escape_string(sanitize($searchText));
				$searchSQL = "AND (
								(yg_user_propsv.LASTNAME LIKE '%".$searchText."%') OR
								(yg_user_propsv.FIRSTNAME LIKE '%".$searchText."%') OR
								(yg_user_propsv.EMAIL LIKE '%".$searchText."%') OR
								(yg_user_propsv.COMPANY LIKE '%".$searchText."%') OR
								(yg_user_propsv.DEPARTMENT LIKE '%".$searchText."%') OR
								(yg_user_propsv.FAX LIKE '%".$searchText."%') OR
								(yg_user_propsv.PHONE LIKE '%".$searchText."%')
							)";
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
			if (strlen($sort) < 1) {
				$sortsql = "ASC";
			}
			if ($limit) {
				$limitsql = "LIMIT " . $limit;
			}

			$anonGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONGROUPID");
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
						yg_user_lnk_usergroups as lnk2 ON (u.ID = lnk2.UID) AND ((lnk2.USERGROUPID = $anonGroupId) OR  (lnk2.USERGROUPID = NULL))
					LEFT JOIN
						yg_user_propsv ON u.ID = yg_user_propsv.OID
					WHERE
						1 $searchSQL
					GROUP BY
						u.ID
					HAVING
						(GROUPCOUNT = 1 AND lnk2.USERGROUPID = $anonGroupId) OR
						(GROUPCOUNT = 0)
					ORDER BY $ordersql $sortsql $limitsql;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
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
			$email = mysql_real_escape_string(sanitize($email));
			if (strlen($email) > 0) {
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
							(yg_user_propsv.EMAIL like \"%" . $email . "%\");";
				$result = sYDB()->Execute($sql);
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
			$email = mysql_real_escape_string(sanitize($email));
			if ($exact === true) {
				$equation = "like \"" . $email . "\"";
			} else {
				$equation = "like \"%" . $email . "%\"";
			}
			if (strlen($email) > 0) {
				$sql = "SELECT u.LOGIN AS LOGIN,
				u.PASSWORD AS PASSWORD,
				u.ID AS ID
				FROM
				yg_user as u
				LEFT JOIN yg_user_propsv ON u.ID = yg_user_propsv.OID
				WHERE
				(yg_user_propsv.EMAIL " . $equation . ");";
				$result = sYDB()->Execute($sql);
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