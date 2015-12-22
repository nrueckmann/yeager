<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Gets an instance of the Entrymask manager
 *
 * @return object Entrymask manager object
 */
function sEntrymasks() {
	return Singleton::entrymasks();
}


/**
 * The Entrymasks class, which represents an instance of the Entrymask manager.
 */
class Entrymasks extends \framework\Error {
	var $_db;
	var $_uid;
	var $id;

	var $permissions;
	var $tree;

	/**
	 * Constructor of the Entrymasks class
	 */
	function __construct() {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->table = "yg_entrymasks_tree";
		$this->tree = new Tree($this);
		$this->permissions = new Permissions("yg_entrymasks_permissions", $this);
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

/// @cond DEV

	/**
	 * Gets the name of the database table containing the Entrymask tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return $this->table;
	}

/// @endcond

	/**
	 * Callback method which is executed when a Usergroup permissions on an Entrymask changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId Entrymask Id
	 */
	public function onPermissionChange($usergroupId, $permission, $value, $objectId) {
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on an Entrymask change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId Entrymask Id
	 */
	public function onPermissionsChange($usergroupId, $permissions, $objectId) {
		return true;
	}

	/**
	 * Sets the Entrymask identifier
	 *
	 * @param int $entrymaskId Entrymask Id
	 * @param string $identifier Entrymask identifier
	 * @throws Exception
	 */
	function setIdentifier($entrymaskId, $identifier) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$entrymaskId = (int)$entrymaskId;
			$identifier = sYDB()->escape_string(sanitize($identifier));
			$sql = "UPDATE `yg_entrymasks_properties` SET CODE = ? WHERE OBJECTID = ?;";
			$result = sYDB()->Execute($sql, $identifier, $entrymaskId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adds an Entrymask
	 *
	 * @param int $parentEntrymaskId Parent Entrymask Id
	 * @param int $folder Specifies if a folder should be created (1 for folder, 0 for Entrymask)
	 * @param string $name (optional) Entrymask name
	 * @return int|false New Entrymask Id or FALSE in case of an error
	 * @throws Exception
	 */
	function add($parentEntrymaskId, $folder = 0, $name = 'New Entrymask') {
		$name = sYDB()->escape_string($name);
		$parentEntrymaskId = (int)$parentEntrymaskId;
		$folder = (int)$folder;
		$rread = $this->permissions->checkInternal($this->_uid, $parentEntrymaskId, "RSUB");
		if ($rread && sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			// Create treenode
			$entrymaskId = $this->tree->add($parentEntrymaskId);
			// Create version
			$sql = "INSERT INTO
						`yg_entrymasks_properties`
					(`OBJECTID`, `FOLDER`, `NAME`)
						VALUES
					(?, ?, ?);";
			$result = sYDB()->Execute($sql, $entrymaskId, $folder, $name);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->permissions->copyTo($parentEntrymaskId, $entrymaskId);
			return $entrymaskId;
		} else {
			return false;
		}
	}

	/**
	 * Gets the parent nodes of the Entrymask
	 *
	 * @param int $entrymaskId Entrymask Id
	 * @return array Array of parent Entrymask nodes
	 */
	function getParents($entrymaskId) {
		$entrymaskId = (int)$entrymaskId;

		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$parentid = $this->tree->getParent($entrymaskId);
			$i = 0;
			while ($parentid > 0) {
				$sql = "SELECT
					group2.LFT, group2.RGT, group2.ID AS ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT
					FROM
					($this->table AS group2, yg_entrymasks_properties AS prop)
					WHERE
					(group2.ID = prop.OBJECTID) AND (group2.ID = ?)
					GROUP BY
					group2.LFT, group2.RGT, group2.ID order by group2.LFT;";
				$dbr = sYDB()->Execute($sql, $parentid);
				$parents[$i] = $dbr->GetArray();
				$entrymaskId = $parents[$i][0]["ID"];
				$parentid = $this->tree->getParent($entrymaskId);
				$i++;
			}
			foreach ($parents as $parent_idx => $parent_item) {
				$entrymaskInfo = $this->get($parent_item[0]['ID']);
				$parents[$parent_idx][0]['NAME'] = $entrymaskInfo['NAME'];
				$parents[$parent_idx][0]['FOLDER'] = $entrymaskInfo['FOLDER'];
			}
			return $parents;
		} else {
			return false;
		}
	}

	/**
	 * Removes an Entrymask
	 *
	 * @param int $entrymaskId Entrymask Id
	 *
	 * @return array Array with all elements which were successfully deleted
	 */
	function remove($entrymaskId) {
		$entrymaskId = $origEntrymaskId = (int)$entrymaskId;
		$rootNode = $this->tree->getRoot();
		if ($entrymaskId == $rootNode) {
			return array();
		}

		// Get all nodes
		$successNodes = array();
		$allNodes = $this->tree->get($entrymaskId, 1000);
		foreach($allNodes as $allNodesItem) {
			$entrymaskId = $allNodesItem['ID'];

			if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
				$sql = "DELETE FROM yg_entrymasks_properties WHERE OBJECTID = ?;";
				sYDB()->Execute($sql, $entrymaskId);

				$sql = "DELETE FROM yg_history WHERE OID = ? AND (SOURCEID = ?);";
				sYDB()->Execute($sql, $entrymaskId, HISTORYTYPE_ENTRYMASK);

				$sql = "DELETE FROM yg_entrymasks_lnk_formfields WHERE ENTRYMASK = ?;";
				sYDB()->Execute($sql, $entrymaskId);

				$sql = "SELECT * FROM yg_contentblocks_lnk_entrymasks WHERE (ENTRYMASK = ?);";
				$result = sYDB()->Execute($sql, $entrymaskId);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$resultarray = $result->GetArray();
				foreach ($resultarray as $resultarrayItem) {
					$sql = "DELETE FROM yg_contentblocks_lnk_entrymasks_c WHERE LNK = ?;";
					$result = sYDB()->Execute($sql, $resultarrayItem['ID']);
				}

				$sql = "DELETE FROM yg_contentblocks_lnk_entrymasks WHERE ENTRYMASK = ?;";
				sYDB()->Execute($sql, $entrymaskId);

				$successNodes[] = $entrymaskId;
			}
		}
		if (in_array($origEntrymaskId, $successNodes)) {
			$this->tree->remove($origEntrymaskId);
		}
		return $successNodes;
	}

	/**
	 * Gets basic information about the Entrymask
	 *
	 * @param int $entrymaskId Entrymask Id
	 * @return array Array containing information about the Entrymask
	 * @throws Exception
	 */
	function get($entrymaskId) {
		$entrymaskId = (int)$entrymaskId;
		if (strlen($entrymaskId) > 0) {
			$sql = "SELECT * FROM yg_entrymasks_properties WHERE (OBJECTID = ?);";
			$result = sYDB()->Execute($sql, $entrymaskId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
		}
		return $resultarray[0];
	}

	/**
	 * Assigns a Formfield to an Entrymask
	 *
	 * @param int $entrymaskId Entrymask Id
	 * @param int $formfieldType Formfield type
	 * @param string $name (optional)
	 * @return int New Entrymask Formfield Link Id
	 * @throws Exception
	 */
	function addFormfield($entrymaskId, $formfieldType, $name = '') {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$entrymaskId = (int)$entrymaskId;
			$formfieldType = (int)$formfieldType;
			$name = sYDB()->escape_string($name);
			$formfieldType = sYDB()->escape_string(sanitize($formfieldType));
			$sql = "INSERT INTO `yg_entrymasks_lnk_formfields` (FORMFIELD, ENTRYMASK, NAME) VALUES (?, ?, ?);";
			$result = sYDB()->Execute($sql, $formfieldType, $entrymaskId, $name);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$formfieldid = sYDB()->Insert_ID();

			$sql = "INSERT INTO `yg_contentblocks_lnk_entrymasks_c`
						(FORMFIELD, ENTRYMASKFORMFIELD, LNK)
					SELECT '$formfieldType', '$formfieldid', ID
					FROM `yg_contentblocks_lnk_entrymasks` WHERE ENTRYMASK = ?;";
			$result = sYDB()->Execute($sql, $entrymaskId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return $formfieldid;
		} else {
			return false;
		}
	}

	/**
	 * Gets information about the Cblock-Entrymask-Link
	 *
	 * @param int $linkId Cblock-Entrymask-Link Id
	 * @return array|false Cblock-Entrymask-Link or FALSE in case of an error
	 */
	function getLinkInfo($linkId) {
		$linkId = (int)$linkId;
		$sql = "SELECT * FROM `yg_contentblocks_lnk_entrymasks` WHERE ID = ?;";
		$result = sYDB()->Execute($sql, $linkId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
			return false;
		}
		$resultarray = $result->GetArray();
		return $resultarray[0];
	}

	/**
	 * Removes a Formfield from an Entrymask
	 *
	 * @param int $linkId Entrymask Formfield Link Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function removeFormfield($linkId) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$linkId = (int)$linkId;
			$sql = "DELETE FROM `yg_entrymasks_lnk_formfields` WHERE ID = ?;";
			$result = sYDB()->Execute($sql, $linkId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			$sql = "DELETE FROM `yg_entrymasks_lnk_formfields_lv` WHERE LID = ?;";
			$result = sYDB()->Execute($sql, $linkId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			$sql = "DELETE FROM `yg_contentblocks_lnk_entrymasks_c` WHERE ENTRYMASKFORMFIELD = ?;";
			$result = sYDB()->Execute($sql, $linkId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets Entrymask tree nodes
	 *
	 * @param int $entrymaskId (optional) From which Entrymask Id the tree should be returned
	 * @param int $maxLevels (optional) Specifies the maximum level of nodes to get
	 * @return array Array of Entrymask nodes
	 */
	function getTree($entrymaskId = NULL, $maxLevels = 2) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$maxLevels = (int)$maxLevels;
			if ($entrymaskId > 0) {
				$currentlevel = $this->tree->getLevel($entrymaskId);
			} else {
				$currentlevel = 1;
			}
			$tree = $this->tree->get($entrymaskId, $currentlevel + $maxLevels);
			$ntree = array();
			for ($n = 0; $n < count($tree); $n++) {
				$props = $this->get($tree[$n]["ID"]);
				$tree[$n]["FOLDER"] = $props["FOLDER"];
				if ($tree[$n]["FOLDER"] != 0) {
					$tree[$n]["RREAD"] = true;
					$tree[$n]["RWRITE"] = true;
					$tree[$n]["RDELETE"] = true;
					$tree[$n]["RSUB"] = true;
					$tree[$n]["NAME"] = $props["NAME"];
					$ntree[] = $tree[$n];
				}
			}
			return $ntree;
		} else {
			return false;
		}
	}

	/**
	 * Gets all list values of a Formfield
	 *
	 * @param int $linkId Entrymask Formfield Link Id
	 * @return array Array of list values
	 */
	function getListValuesByLinkID($linkId) {
		$linkId = (int)$linkId;
		$sql = "SELECT ID, VALUE FROM `yg_entrymasks_lnk_formfields_lv` WHERE LID = ? ORDER BY LISTORDER ASC;";
		$result = sYDB()->Execute($sql, $linkId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
			return false;
		}
		$resultarray = $result->GetArray();
		return $resultarray;
	}

	/**
	 * Removes all list values of a Formfield
	 *
	 * @param int $linkId Entrymask Formfield Link Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function clearListValues($linkId) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$linkId = (int)$linkId;
			$sql = "DELETE FROM yg_entrymasks_lnk_formfields_lv WHERE LID = ?;";
			$result = sYDB()->Execute($sql, $linkId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
				return false;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes a list value from a Formfield (by list Id)
	 *
	 * @param int $listId List Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function removeListValue($listId) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$listId = (int)$listId;
			$sql = "DELETE FROM yg_entrymasks_lnk_formfields_lv WHERE ID = ?;";
			$result = sYDB()->Execute($sql, $listId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
				return false;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adds a list value to a Formfield
	 *
	 * @param int $linkId Entrymask Formfield Link Id
	 * @param string $value List value
	 * @return int New list Id
	 */
	function addListValue($linkId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$linkId = (int)$linkId;
			$value = sYDB()->escape_string($value);
			$sql = "INSERT INTO yg_entrymasks_lnk_formfields_lv (LID, VALUE, LISTORDER) VALUES (?, ?, 0);";
			$result = sYDB()->Execute($sql, $linkId, $value);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
				return false;
			}
			return sYDB()->Insert_ID();
		} else {
			return false;
		}
	}

	/**
	 * Gets basic information about the Entrymask (by identifier)
	 *
	 * @param string $identifier Entrymask identifier
	 * @return array Array containing information about the Entrymask
	 * @throws Exception
	 */
	function getByIdentifier($identifier) {
		$identifier = sYDB()->escape_string(sanitize($identifier));
		$sql = "SELECT * FROM `yg_entrymasks_properties` WHERE CODE = ?;";
		$result = sYDB()->Execute($sql, $identifier);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray[0];
	}

	/**
	 * Gets all Formfields of the Entrymask
	 *
	 * @param int $entrymaskId Entrymask Id
	 * @return array Array of Formfields
	 * @throws Exception
	 */
	function getEntrymaskFormfields($entrymaskId) {
		$entrymaskId = (int)$entrymaskId;
		$sql = "SELECT
					lnk.ID,
					lnk.FORMFIELD,
					lnk.ENTRYMASK,
					lnk.ORDER,
					lnk.NAME,
					lnk.IDENTIFIER,
					lnk.PRESET,
					lnk.WIDTH,
					lnk.MAXLENGTH,
					lnk.CONFIG,
					lnk.CUSTOM,
					w.TYPE AS FORMFIELDTYPE
				FROM
					`yg_entrymasks_lnk_formfields` AS lnk,
					`yg_formfields` AS w
				WHERE
					ENTRYMASK = ? AND
					(lnk.FORMFIELD = w.ID)
				ORDER BY `ORDER` ASC;";
		$result = sYDB()->Execute($sql, $entrymaskId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return $result->GetArray();
	}

	/**
	 * Gets a Formfield
	 *
	 * @param int $linkId Entrymask Formfield Link Id
	 * @return array Formfield
	 * @throws Exception
	 */
	function getFormfield($linkId) {
		$linkId = (int)$linkId;
		$sql = "SELECT
					lnk.*,
					w.TYPE AS FORMFIELDNAME,
					w.TYPE AS FORMFIELDTYPE
				FROM
					`yg_entrymasks_lnk_formfields` AS lnk,
					`yg_formfields` AS w
				WHERE
					lnk.ID = ? AND
					(lnk.FORMFIELD = w.ID)
				ORDER BY `ORDER` ASC;";
		$result = sYDB()->Execute($sql, $linkId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ret = $result->GetArray();
		return $ret[0];
	}

	/**
	 * Moves a Formfield up in the Entrymask
	 *
	 * @param int $entrymaskId Entrymask Id
	 * @param int $linkId Entrymask Formfield Link Id
	 */
	function moveFormfieldUp($entrymaskId, $linkId) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$entrymaskId = (int)$entrymaskId;
			$linkId = (int)$linkId;
			$formfields = $this->getEntrymaskFormfields($entrymaskId);
			for ($i = 0; $i < count($formfields); $i++) {
				$order = $i + 1;
				if (($formfields[$i]["ID"] == $linkId) && ($formfields[$i - 1]["ID"] > 0)) {
					$this->setFormfieldOrder($formfields[$i]["ID"], $order - 1);
					$this->setFormfieldOrder($formfields[$i - 1]["ID"], $order);
				} else {
					if ($formfields[$i]["ORDER"] != $order) {
						$this->setFormfieldOrder($formfields[$i]["ID"], $order);
					}
				}
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Moves a Formfield down in the Entrymask
	 *
	 * @param int $entrymaskId Entrymask Id
	 * @param int $linkId Entrymask Formfield Link Id
	 */
	function moveFormfieldDown($entrymaskId, $linkId) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$entrymaskId = (int)$entrymaskId;
			$linkId = (int)$linkId;
			$formfields = $this->getEntrymaskFormfields($entrymaskId);
			for ($i = 0; $i < count($formfields); $i++) {
				$order = $i + 1;
				if (($formfields[$i]["ID"] == $linkId) && ($formfields[$i + 1]["ID"] > 0)) {
					$this->setFormfieldOrder($formfields[$i]["ID"], $order + 1);
					$this->setFormfieldOrder($formfields[$i + 1]["ID"], $order);
					$i++;
				} else {
					if ($formfields[$i]["ORDER"] != $order) {
						$this->setFormfieldOrder($formfields[$i]["ID"], $order);
					}
				}
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the order of Formfields in the Entrymask
	 *
	 * @param int $linkId Entrymask Formfield Link Id
	 * @param int $order Order position
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setFormfieldOrder($linkId, $order) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$order = (int)$order;
			$linkId = (int)$linkId;
			$sql = "UPDATE `yg_entrymasks_lnk_formfields` SET `ORDER` = ? WHERE ID = ?;";
			$result = sYDB()->Execute($sql, $order, $linkId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the parameters (content) of a Formfield
	 *
	 * @param int $linkId Entrymask Formfield Link Id
	 * @param array $parameters Array of parameters (content)
	 * @throws Exception
	 */
	function setFormfieldParameters($linkId, $parameters = array()) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$linkId = (int)$linkId;
			$sql = "UPDATE `yg_entrymasks_lnk_formfields` SET
			IDENTIFIER = ?,
			NAME = ?,
			PRESET = ?,
			WIDTH = ?,
			MAXLENGTH = ?,
			CONFIG = ?,
			CUSTOM = ?
			WHERE ID = ?;";

			$result = sYDB()->Execute($sql, $parameters['IDENTIFIER'], $parameters['NAME'], $parameters['PRESET'], $parameters['WIDTH'], $parameters['MAXLENGTH'], $parameters['CONFIG'], $parameters['CUSTOM'], $linkId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the Entrymask name and code
	 *
	 * @param int $emId Entrymask Id
	 * @param string $name Entrymask name
	 * @param string $code Entrymask code
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setInfo($emId, $name, $code) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$emId = (int)$emId;
			$name = sYDB()->escape_string(sanitize($name));
			$code = sYDB()->escape_string(sanitize($code));

			// Check if type already exists
			$sql = "SELECT * FROM `yg_entrymasks_properties` WHERE CODE = ?;";
			$result = sYDB()->Execute($sql, $code);
			$resultarray = $result->GetArray();
			if (((count($resultarray) > 1) || ((count($resultarray) == 1) && ($resultarray[0]['OBJECTID'] != $emId))) &&
				($code != 'NONE')
			) {
				// Already existing
				return false;
			}

			$sql = "UPDATE `yg_entrymasks_properties` SET NAME = ?, CODE = ? WHERE OBJECTID = ?;";
			sYDB()->Execute($sql, $name, $code, $emId);

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the Formfield name
	 *
	 * @param int $linkId Entrymask Formfield Link Id
	 * @param string $name Formfield name
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setFormfieldName($linkId, $name) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$linkId = (int)$linkId;
			$name = sYDB()->escape_string(sanitize($name));
			$sql = "UPDATE `yg_entrymasks_lnk_formfields` SET `NAME` = ? WHERE ID = ?;";
			$result = sYDB()->Execute($sql, $name, $linkId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the name of an Entrymask
	 *
	 * @param int $emId Entrymask Id
	 * @param string $name Entrymask name
	 * @throws Exception
	 */
	function setName($emId, $name) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$emId = (int)$emId;
			$name = sYDB()->escape_string(sanitize($name));
			$sql = "UPDATE `yg_entrymasks_properties` SET NAME = ? WHERE OBJECTID = ?;";
			$result = sYDB()->Execute($sql, $name, $emId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets a list of Entrymasks
	 *
	 * @return array|false Array of Entrymasks
	 */
	function getList() {
		$sql = "SELECT
					group2.LFT, group2.RGT, group2.ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT,
					prop.NAME AS NAME, prop.CODE AS CODE, prop.FOLDER AS FOLDER
				FROM
					($this->table AS group1, $this->table AS group2, yg_entrymasks_properties AS prop)
				WHERE
					((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
					(group2.ID = prop.OBJECTID)
				GROUP BY
					group2.LFT, group2.RGT, group2.ID
				ORDER BY group2.LFT;";

		$result = sYDB()->Execute($sql);
		return $result->GetArray();
	}

	/**
	 * Sets the order of list values of a Formfield
	 *
	 * @param array $orderArray Array of list Ids
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setListOrder($orderArray) {
		if (sUsergroups()->permissions->check($this->_uid, 'RENTRYMASKS')) {
			$order = 0;
			foreach ($orderArray as $order_array_item) {
				$order_array_item = (int)$order_array_item;
				$sql = "UPDATE `yg_entrymasks_lnk_formfields_lv` SET `LISTORDER` = ? WHERE ID = ?;";
				$result = sYDB()->Execute($sql, $order, $order_array_item);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$order++;
			}
			return true;
		} else {
			return false;
		}
	}

}

?>