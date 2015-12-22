<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Gets an instance of the Filetypes manager
 *
 * @return object Filetypes manager object
 */
function sFiletypes() {
	return Singleton::filetypes();
}

/**
 * The Filetypes class, which represents an instance of the Filetype manager.
 */
class Filetypes extends \framework\Error {
	var $_db;
	var $_uid;
	var $id;

	/**
	 * Constructor of the Filetypes class
	 */
	function __construct() {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->table = "yg_filetypes_tree";
		$this->tree = new Tree($this);
		$this->permissions = new Permissions("yg_filetypes_permissions", $this);
	}

/// @cond DEV

	/**
	 * Gets the name of the database table containing the Filetypes tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return $this->table;
	}

/// @endcond

	/**
	 * Callback method which is executed when a Usergroup permission on a Cblock changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId Filetype Id
	 */
	public function onPermissionChange($usergroupId, $permission, $value, $objectId) {
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on a Cblock change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId Filetype Id
	 */
	public function onPermissionsChange($usergroupId, $permissions, $objectId) {
		return true;
	}

	/**
	 * Sets the code for a Filetype
	 *
	 * @param int $filetypeId Filetype Id
	 * @param string $value Filetype code
	 * @throws Exception
	 */
	function setCode($filetypeId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RFILETYPES')) {
			$filetypeId = (int)$filetypeId;
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE `yg_filetypes_properties` SET CODE = ? WHERE OBJECTID = ?;";
			$result = sYDB()->Execute($sql, $value, $filetypeId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the name for a Filetype
	 *
	 * @param int $filetypeId Filetype Id
	 * @param string $value Filetype name
	 * @throws Exception
	 */
	function setName($filetypeId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RFILETYPES')) {
			$filetypeId = (int)$filetypeId;
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE `yg_filetypes_properties` SET NAME = ? WHERE OBJECTID = ?;";
			$result = sYDB()->Execute($sql, $value, $filetypeId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets/removes the "readonly" flag of a Filetype
	 *
	 * @param int $filetypeId Filetype Id
	 * @param int $value (optional) "readonly" flag (1 for set, 0 for unset)
	 * @throws Exception
	 */
	function setReadonly($filetypeId, $value = 1) {
		if (sUsergroups()->permissions->check($this->_uid, 'RFILETYPES')) {
			$filetypeId = (int)$filetypeId;
			$value = (int)$value;
			$sql = "UPDATE `yg_filetypes_properties` SET READONLY = ? WHERE OBJECTID = ?;";
			$result = sYDB()->Execute($sql, $value, $filetypeId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the identifier for a Filetype
	 *
	 * @param int $filetypeId Filetype Id
	 * @param string $value Filetype identifier
	 * @throws Exception
	 */
	function setIdentifier($filetypeId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RFILETYPES')) {
			$filetypeId = (int)$filetypeId;
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE `yg_filetypes_properties` SET IDENTIFIER = ? WHERE OBJECTID = ?;";
			$result = sYDB()->Execute($sql, $value, $filetypeId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the color for a Filetype
	 *
	 * @param int $filetypeId Filetype Id
	 * @param string $value Filetype color
	 * @throws Exception
	 */
	function setColor($filetypeId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RFILETYPES')) {
			$filetypeId = (int)$filetypeId;
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE `yg_filetypes_properties` SET COLOR = ? WHERE OBJECTID = ?;";
			$result = sYDB()->Execute($sql, $value, $filetypeId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the processor for a Filetype
	 *
	 * @param int $filetypeId Filetype Id
	 * @param string $value Filetype processor
	 * @throws Exception
	 */
	function setProcessor($filetypeId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RFILETYPES')) {
			$filetypeId = (int)$filetypeId;
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE `yg_filetypes_properties` SET PROCESSOR = ? WHERE OBJECTID = ?;";
			$result = sYDB()->Execute($sql, $value, $filetypeId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Sets the file-extensions for a Filetype
	 *
	 * @param int $filetypeId Filetype Id
	 * @param string $value Filetype file-extensions (comma seperated)
	 * @throws Exception
	 */
	function setExtensions($filetypeId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RFILETYPES')) {
			$filetypeId = (int)$filetypeId;
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE `yg_filetypes_properties` SET EXTENSIONS = ? WHERE OBJECTID = ?;";
			$result = sYDB()->Execute($sql, $value, $filetypeId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adds a new Filetype
	 *
	 * @param int $parentFiletypeId Filetype Id of the parent node
	 * @return int|false New Filetype Id or FALSE in case of an error
	 * @throws Exception
	 */
	function add($parentFiletypeId) {
		$parentFiletypeId = (int)$parentFiletypeId;
		$rread = $this->permissions->checkInternal($this->_uid, $parentFiletypeId, "RSUB");
		if ($rread && sUsergroups()->permissions->check($this->_uid, 'RFILETYPES')) {
			// Knoten im Pagestree erzeugen
			$filetypeId = $this->tree->add($parentFiletypeId);
			// Version anlegen
			$sql = "INSERT INTO `yg_filetypes_properties` (`OBJECTID`, `FOLDER`, `NAME`, `EXTENSIONS`)
						VALUES
					(?, 0, 'New Type', '');";
			$result = sYDB()->Execute($sql, $filetypeId);

			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->permissions->copyTo($parentFiletypeId, $filetypeId);
			// Return new Id
			return $filetypeId;
		} else {
			return false;
		}
	}

	/**
	 * Removes a Filetype
	 *
	 * @param int $filetypeId Filetype Id
	 */
	function remove($filetypeId) {
		$filetypeId = $origFiletypeId = (int)$filetypeId;
		$rootNode = $this->tree->getRoot();

		// Get all nodes
		$hadError = false;
		$allNodes = $this->tree->get($filetypeId, 1000);
		foreach($allNodes as $allNodesItem) {
			$filetypeId = (int)$allNodesItem['ID'];

			if (sUsergroups()->permissions->check($this->_uid, 'RFILETYPES') && ($filetypeId != $rootNode)) {
				$filetypeId = (int)$filetypeId;

				$sql = "DELETE FROM yg_filetypes_properties WHERE OBJECTID = ?;";
				sYDB()->Execute($sql, $filetypeId);
			} else {
				$hadError = true;
			}
		}
		if ($hadError) {
			return false;
		} else {
			$this->tree->remove($origFiletypeId);
			return true;
		}
	}

	/**
	 * Gets basic information about the Filetype
	 *
	 * @param int $filetypeId Filetype Id
	 * @return array|false Array containing information about the Filetype or FALSE in case of an error
	 */
	function get($filetypeId) {
		$filetypeId = (int)$filetypeId;
		if (strlen($filetypeId) > 0) {
			$sql = "SELECT * FROM yg_filetypes_properties WHERE (OBJECTID = ?);";
			$result = sYDB()->Execute($sql, $filetypeId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
		}
		return $resultarray[0];
	}

	/**
	 * Gets the processor for the Filetype
	 *
	 * @param int $filetypeId Filetype Id
	 * @return string Filetype Processor
	 * @throws Exception
	 */
	function getProcessor($filetypeId) {
		$filetypeId = (int)$filetypeId;
		if (strlen($filetypeId) > 0) {
			$sql = "SELECT PROCESSOR FROM yg_filetypes_properties WHERE (OBJECTID = ?);";
			$result = sYDB()->Execute($sql, $filetypeId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
		}
		return $resultarray[0]['PROCESSOR'];
	}

	/**
	 * Gets a Filetype (by code)
	 *
	 * @param string $code Filetype code
	 * @return array Array containing information about the Filetype
	 * @throws Exception
	 */
	function getByCode($code) {
		$code = sYDB()->escape_string(sanitize($code));
		$sql = "SELECT * FROM `yg_filetypes_properties` WHERE CODE = ?;";
		$result = sYDB()->Execute($sql, $code);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return $result->GetArray();
	}

	/**
	 * Gets a Filetype (by identifier)
	 *
	 * @param string $identifier Filetype identifier
	 * @return array Array containing information about the Filetype
	 * @throws Exception
	 */
	function getByIdentifier($identifier) {
		if (sUsergroups()->permissions->check($this->_uid, 'RFILETYPES')) {
			$identifier = sYDB()->escape_string(sanitize($identifier));
			$sql = "SELECT * FROM `yg_filetypes_properties` WHERE IDENTIFIER = ?;";
			$result = sYDB()->Execute($sql, $identifier);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return $result->GetArray();
		} else {
			return false;
		}
	}

	/**
	 * Gets a list of Filetypes
	 * @return array Array of Filetypes
	 */
	function getList() {
		$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");

		$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE,  MAX(perm.RSTAGE) AS RSTAGE";
		$perm_sql_from = " LEFT JOIN yg_filetypes_permissions AS perm ON perm.OID = group2.ID";
		$perm_sql_where = " AND (";
		$roles = $this->permissions->getUsergroups();
		for ($r = 0; $r < count($roles); $r++) {
			$perm_sql_where .= "(perm.USERGROUPID = " . (int)$roles[$r]["ID"] . ") ";
			if ((count($roles) - $r) > 1) {
				$perm_sql_where .= " OR ";
			}
		}
		$perm_sql_where .= ") ";
		$perm_sql_where .= " AND ((RREAD >= 1) OR (perm.USERGROUPID = " . (int)$rootGroupId . ")) ";

		$sql = "SELECT
					group2.LFT,
					group2.RGT,
					group2.LEVEL AS LEVEL,
					group2.PARENT AS PARENT,
					prop.*
					$perm_sql_select
				FROM
					($this->table AS group1, $this->table AS group2, yg_filetypes_properties AS prop)
					$perm_sql_from
				WHERE
					((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
					(group2.ID = prop.OBJECTID)
					$perm_sql_where
					$filtersql_where
				GROUP BY
					group2.LFT, group2.RGT, group2.ID
				ORDER BY prop.NAME;";

		$result = sYDB()->Execute($sql);
		return $result->GetArray();
	}

}

?>