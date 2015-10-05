<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Gets an instance of the Usergroup manager
 *
 * @return object Usergroup manager object
 */
function sUsergroups() {
	return Singleton::Usergroups();
}

/**
 * The Usergroups class, which represents an instance of the Usergroup manager.
 */
class Usergroups {
	var $_db;
	var $_table;
	var $permissions;
	var $usergroupPermissions;
	var $_getByUsergroupHash;

	private $_uid;

	/**
	 * Constructor of the Usergroups class
	 */
	function __construct() {
		$this->_db = sYDB();
		$this->_table = "yg_usergroups";
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->permissions = new Privileges();
		$this->usergroupPermissions = new Permissions("yg_usergroups_permissions", $this);
		$this->tree = new Tree($this);
	}

	public function getByUsergroupHash($bucket, $usergroupId, $objectId) {
		return $this->_getByUsergroupHash[$bucket][$usergroupId][$objectId];
	}

	public function emptyUsergroupHash($bucket) {
		$this->_getByUsergroupHash[$bucket] = false;
	}

	public function setByUsergroupHash($bucket, $usergroupId, $objectId, $value) {
		$this->_getByUsergroupHash[$bucket][$usergroupId][$objectId] = $value;
	}

	public function setByUsergroupHashPermission($bucket, $usergroupId, $objectId, $permission, $value) {
		$this->_getByUsergroupHash[$bucket][$usergroupId][$objectId][$permission] = $value;
	}

/// @cond DEV

	/**
	 * Gets the name of the database table containing the Usergroups tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return '';
	}

/// @endcond

	/**
	 * Callback method which is executed when Usergroup permissions on the specified Usergroup changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId Usergroup Id
	 */
	public function onPermissionChange($usergroupId, $permission, $value, $objectId) {
		return true;
	}

	/**
	 * Gets a list of Usergroups
	 * @param bool $skipPermissions TRUE when the permissions shouldn't be respected
	 * @return array Usergroup List
	 */
	function getList($skipPermissions) {
		if (!$skipPermissions) {
			$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");

			// SQL for permissions
			$perm_SQL_SELECT = ", MAX(perm.RREAD) AS RREAD, MAX(perm.RWRITE) AS RWRITE, MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RSEND) AS RSEND";
			$perm_SQL_FROM = " INNER JOIN " . $this->_table . "_permissions AS perm ON perm.OID = group2.ID";
			$perm_SQL_WHERE = " AND (";
			$perm_SQL_GROUPBY = "GROUP BY group2.ID ";

			$tmpUser = new User(sUserMgr()->getCurrentUserID());
			$roles = $tmpUser->getUsergroups();
			for ($r = 0; $r < count($roles); $r++) {
				$perm_SQL_WHERE .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
				if ((count($roles) - $r) > 1) {
					$perm_SQL_WHERE .= " OR ";
				}
			}
			$perm_SQL_WHERE .= ")";
			$perm_SQL_WHERE2 = " WHERE ((RREAD >= 1) OR (group2.ID IN (";
			for ($r = 0; $r < count($roles); $r++) {
				$perm_SQL_WHERE2 .= $roles[$r]["ID"];
				if ($r < count($roles)-1) {
					$perm_SQL_WHERE2 .= ', ';
				}
			}
			$perm_SQL_WHERE2 .= ")))";
			$perm_SQL_WHERE2 .= " AND ((RREAD >= 1) OR (perm.USERGROUPID = $rootGroupId))";
		}
		$sql = "SELECT
					group2.ID AS ID,
					group2.NAME AS NAME
					$perm_SQL_SELECT
				FROM
					" . $this->_table . " AS group2
					$perm_SQL_FROM
					$perm_SQL_WHERE
				$perm_SQL_WHERE2
				$perm_SQL_GROUPBY
				ORDER BY NAME;";

		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray;
	}

	/**
	 * Adds a Usergroup
	 *
	 * @param string $name (optional) Usergroup Name
	 * @return int New Usergroup Id
	 */
	function add($name = "new Usergroup") {
		if ($this->permissions->check($this->_uid, 'RUSERGROUPS')) {
			$name = mysql_real_escape_string(sanitize($name));
			$sql = "INSERT INTO " . $this->_table . " VALUES (0, '$name');";
			$result = sYDB()->Execute($sql);
			$newId = sYDB()->Insert_ID();

			if ($newId > 0) {
				$tmpUser = new User(sUserMgr()->getCurrentUserID());
				$userRoles = $tmpUser->getUsergroups();

				foreach ($userRoles as $userRoles_item) {
					$usergroupPermissions = $this->permissions->getByUsergroup($userRoles_item['ID']);
					if ($usergroupPermissions['RUSERGROUPS']) {
						$pinfo = $this->usergroupPermissions->getByUsergroup($userRoles_item['ID'], $newId);
						if (count($pinfo) > 0) {
							// Update
							$sql = "UPDATE yg_usergroups_permissions SET RREAD = 1, RWRITE = 1, RDELETE = 1 WHERE OID = $newId AND USERGROUPID = " . $userRoles_item['ID'] . ";";
							$result = sYDB()->Execute($sql);
							if ($result === false) {
								throw new Exception(sYDB()->ErrorMsg());
							}
						} else {
							// Insert
							$sql = "INSERT INTO yg_usergroups_permissions SET USERGROUPID = " . $userRoles_item['ID'] . ", RREAD = 1, RWRITE = 1, RDELETE = 1, OID = $newId;";
							$result = sYDB()->Execute($sql);
							if ($result === false) {
								throw new Exception(sYDB()->ErrorMsg());
							}
						}
					}
				}

				// Add permissions for Administrator group
				$rootgroupId = (int)sConfig()->getVar('CONFIG/SYSTEMUSERS/ROOTGROUPID');
				$pinfo = $this->usergroupPermissions->getByUsergroup($rootgroupId, $newId);
				if (count($pinfo) > 0) {
					// Update
					$sql = "UPDATE yg_usergroups_permissions SET RREAD = 1, RWRITE = 1, RDELETE = 1 WHERE OID = $newId AND USERGROUPID = " . $rootgroupId . ";";
					$result = sYDB()->Execute($sql);
					if ($result === false) {
						throw new Exception(sYDB()->ErrorMsg());
					}
				} else {
					// Insert
					$sql = "INSERT INTO yg_usergroups_permissions SET USERGROUPID = " . $rootgroupId . ", RREAD = 1, RWRITE = 1, RDELETE = 1, OID = $newId;";
					$result = sYDB()->Execute($sql);
					if ($result === false) {
						throw new Exception(sYDB()->ErrorMsg());
					}
				}
				$this->setDefaultPermissions($newId);
			}
			return $newId;
		} else {
			return false;
		}
	}

	/**
	 * Sets the default permissions for Objects without an Admin
	 *
	 * @param int $usergroupId Usergroup Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setDefaultPermissions($usergroupId) {
		// For Templates
		$templateMgr = new Templates();
		$allTemplates = $templateMgr->getList();
		$permissionList = array();
		foreach ($allTemplates as $allTemplate) {
			$permissionList[] = array(
				'RREAD' => 1,
				'RSUB' => 1,
				'RWRITE' => 1,
				'RDELETE' => 1,
				'OID' => $allTemplate['ID'],
				'USERGROUPID' => $usergroupId
			);
		}
		$templateMgr->permissions->setPermissions($permissionList);

		// For Views
		$viewMgr = new Views();
		$allViews = $viewMgr->getList();
		$permissionList = array();
		foreach ($allViews as $allView) {
			$permissionList[] = array(
				'RREAD' => 1,
				'RSUB' => 1,
				'RWRITE' => 1,
				'RDELETE' => 1,
				'OID' => $allView['ID'],
				'USERGROUPID' => $usergroupId
			);
		}
		$viewMgr->permissions->setPermissions($permissionList);

		// For Entrymasks
		$entrymaskMgr = new Entrymasks();
		$allEntrymasks = $entrymaskMgr->getList();
		$permissionList = array();
		foreach ($allEntrymasks as $allEntrymask) {
			$permissionList[] = array(
				'RREAD' => 1,
				'RSUB' => 1,
				'RWRITE' => 1,
				'RDELETE' => 1,
				'OID' => $allEntrymask['ID'],
				'USERGROUPID' => $usergroupId
			);
		}
		$entrymaskMgr->permissions->setPermissions($permissionList);

		// For Cblock blindfolder
		$embeddedCblockFolder = (int)sConfig()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");
		$permissionList = array();
		$permissionList[] = array(
			'RREAD' => 1,
			'RSUB' => 1,
			'RWRITE' => 1,
			'OID' => $embeddedCblockFolder,
			'USERGROUPID' => $usergroupId
		);
		sCblockMgr()->permissions->setPermissions($permissionList);

		// Remove rights for Cblock root node
		$cblockRootNodeId = sCblockMgr()->tree->getRoot();
		$permissionList = array();
		$permissionList[] = array(
			'RREAD' => 0,
			'RSUB' => 0,
			'RWRITE' => 0,
			'RDELETE' => 0,
			'OID' => $cblockRootNodeId,
			'USERGROUPID' => $usergroupId
		);
		sCblockMgr()->permissions->setPermissions($permissionList);
	}

	/**
	 * Removes a Usergroup
	 *
	 * @param int $usergroupId Usergroup Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function remove($usergroupId) {
		if ($this->permissions->check($this->_uid, 'RUSERGROUPS')) {
			$usergroupId = (int)$usergroupId;
			if ($usergroupId == (int)sConfig()->getVar('CONFIG/SYSTEMUSERS/ROOTGROUPID')) {
				return false;
			} // do not allow root Usergroup to be deleted
			$sql = "DELETE FROM " . $this->_table . " WHERE ID = $usergroupId;";
			sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_user_lnk_usergroups WHERE USERGROUPID = $usergroupId;";
			sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_contentblocks_permissions WHERE USERGROUPID = $usergroupId;";
			sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_entrymasks_permissions WHERE USERGROUPID = $usergroupId;";
			sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_files_permissions WHERE USERGROUPID = $usergroupId;";
			sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_filetypes_permissions WHERE USERGROUPID = $usergroupId;";
			sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_mailing_permissions WHERE USERGROUPID = $usergroupId;";
			sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_tags_permissions WHERE USERGROUPID = $usergroupId;";
			sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_templates_permissions WHERE USERGROUPID = $usergroupId;";
			sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_usergroups_permissions WHERE USERGROUPID = $usergroupId;";
			sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_views_permissions WHERE USERGROUPID = $usergroupId;";
			sYDB()->Execute($sql);
			$sql = "DELETE FROM yg_mailing_lnk_usergroups WHERE RID = $usergroupId;";
			sYDB()->Execute($sql);
			$allSites = sSites()->getList();
			foreach($allSites as $allSitesItem) {
				$sql = "DELETE FROM yg_site_".$allSitesItem['ID']."_permissions WHERE USERGROUPID = $usergroupId;";
				sYDB()->Execute($sql);
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets basic information about the Usergroup
	 *
	 * @param int $usergroupId Usergroup Id
	 * @return array|false Array containing information about this Usergroup or FALSE in case of an error
	 */
	function get($usergroupId) {
		$usergroupId = (int)$usergroupId;
		$sql = "SELECT * FROM " . $this->_table . " WHERE ID = '$usergroupId';";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray[0];
	}

	/**
	 * Sets the name of the Usergroup
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $name Usergroup name
	 */
	function setName($usergroupId, $name) {
		if ($this->permissions->check($this->_uid, 'RUSERGROUPS')) {
			$usergroupId = (int)$usergroupId;
			$name = mysql_real_escape_string(sanitize($name));
			$sql = "UPDATE " . $this->_table . " SET NAME = '$name' WHERE (ID = $usergroupId);";
			$result = sYDB()->Execute($sql);
			return true;
		} else {
			return false;
		}
	}

}

?>