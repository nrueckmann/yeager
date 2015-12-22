<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The PropertySettings class, which represents the PropertySettings manager.
 */
class PropertySettings extends \framework\Error {
	var $_table;

	/**
	 * Constructor of the PropertySettings class
	 *
	 * @param string $table Name of the table into which the Properties will be saved
	 */
	function __construct($table = '') {
		$this->_table = $table;
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

	/**
	 * Adds a new Property to the Object
	 *
	 * @param string $name Property Name
	 * @param string $identifier Property identifier
	 * @param string $type (optional) Property Type (TEXT, TEXTAREA, RICHTEXT, CHECKBOX, LIST, LINK, TAG, CBLOCK, PAGE, FILE PASSWORD, DATE, DATETIME, HEADLINE)
	 * @param int $visible (optional) Specifies if the Property is visible in the UI (1 = visible, 0 = invisible)
	 * @param int $listorder (optional) Order position
	 * @return int|false New Property Id or FALSE in case of an error
	 * @throws Exception
	 */
	function add($name, $identifier, $type = 'TEXT', $visible = 1, $listorder = 9999) {
		$name = sYDB()->escape_string(sanitize($name));
		$identifier = sYDB()->escape_string(sanitize($identifier));
		$visible = (int)$visible;
		$type = sYDB()->escape_string(sanitize($type));
		$listorder = (int)$listorder;

		$sql = "SELECT IDENTIFIER FROM " . $this->_table . " WHERE IDENTIFIER = ?;";
		$result = sYDB()->Execute($sql, $identifier);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
			return false;
		}
		$resultarray = $result->GetArray();
		if (strlen($resultarray[0]['IDENTIFIER']) > 1) {
			return false;
		}

		$sql = "INSERT INTO " . $this->_table . " (NAME, IDENTIFIER, VISIBLE, TYPE, LISTORDER) VALUES (?, ?, ?, ?, ?)";
		$result = sYDB()->Execute($sql, $name, $identifier, $visible, $type, $listorder);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$pid = sYDB()->Insert_ID();

		if ($pid >= 1) {
			$sql = "ALTER TABLE `" . $this->_table . "v` ADD `$identifier` TEXT NULL ;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
		} else {
			return false;
		}
		return $pid;
	}

	/**
	 * Removes a Property from the Object
	 *
	 * @param string $identifier Property identifier
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function remove($identifier) {
		$identifier = sYDB()->escape_string(sanitize($identifier));

		$sql = "DELETE FROM `" . $this->_table . "` WHERE IDENTIFIER = ?;";
		$result = sYDB()->Execute($sql, $identifier);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "ALTER TABLE `" . $this->_table . "v` DROP `$identifier`;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	 * Sets the Property Name
	 *
	 * @param string $identifier Property identifier
	 * @param string $value Property name
	 * @throws Exception
	 */
	function setName($identifier, $value) {
		$identifier = sYDB()->escape_string(sanitize($identifier));
		$value = sYDB()->escape_string($value);
		$sql = "UPDATE `" . $this->_table . "` SET `NAME` = ? WHERE IDENTIFIER = ?;";
		$result = sYDB()->Execute($sql, $value, $identifier);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
	}

	/**
	 * Sets the Property identifier
	 *
	 * @param string $identifier Property identifier
	 * @param string $newIdentifier New Property identifier
	 * @throws Exception
	 */
	function setIdentifier($identifier, $newIdentifier) {
		$identifier = sYDB()->escape_string(sanitize($identifier));
		$newIdentifier = sYDB()->escape_string(sanitize($newIdentifier));
		$sql = "UPDATE `" . $this->_table . "` SET `IDENTIFIER` = ? WHERE IDENTIFIER = ?";
		$result = sYDB()->Execute($sql, $newIdentifier, $identifier);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "ALTER TABLE `" . $this->_table . "v` CHANGE `$identifier` `$newIdentifier` text;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

	}

	/**
	 * Sets the Property order
	 *
	 * @param string $identifier Property identifier
	 * @param int $listorder Order (number)
	 * @throws Exception
	 */
	function setOrder($identifier, $listorder) {
		$identifier = sYDB()->escape_string(sanitize($identifier));
		$listorder = (int)$listorder;
		$sql = "UPDATE `" . $this->_table . "` SET `LISTORDER` = ? WHERE IDENTIFIER = ?;";
		$result = sYDB()->Execute($sql, $listorder, $identifier);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
	}

	/**
	 * Adds a new list value to a Property of the type "LIST")
	 *
	 * @param string $identifier Property identifier
	 * @param int $value Value
	 * @param int $listorder Order position
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function addListValue($identifier, $value, $listorder = 0) {
		$identifier = sYDB()->escape_string(sanitize($identifier));
		$value = sYDB()->escape_string($value);
		$listorder = (int)$listorder;
		$prop_info = $this->getProperty($identifier);
		$sql = "INSERT INTO " . $this->_table . "lv (PID, VALUE, LISTORDER) VALUES (?, ?, ?);";
		$result = sYDB()->Execute($sql, $prop_info[0]['ID'], $value, $listorder);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
			return false;
		}
		return true;
	}

	/**
	 * Removes all list values from a Property of the type "LIST"
	 *
	 * @param string $identifier Property identifier
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function clearListValues($identifier) {
		$identifier = sYDB()->escape_string(sanitize($identifier));
		$prop_info = $this->getProperty($identifier);

		if (count($prop_info) > 0) {
			$sql = "DELETE FROM " . $this->_table . "lv WHERE PID = ?;";
			$result = sYDB()->Execute($sql, $prop_info[0]['ID']);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
				return false;
			}
		}
		return true;
	}

	/**
	 * Gets all list values of a Property of the type "LIST"
	 *
	 * @param string $identifier Property identifier
	 * @return array|false Array of list values or FALSE in case of an error
	 */
	function getListValues($identifier) {
		$identifier = sYDB()->escape_string(sanitize($identifier));
		$prop_info = $this->getProperty($identifier);
		$sql = "SELECT VALUE FROM `" . $this->_table . "lv` WHERE PID = ? ORDER BY LISTORDER ASC";
		$result = sYDB()->Execute($sql, $prop_info[0]['ID']);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
			return false;
		}
		$resultarray = $result->GetArray();
		return $resultarray;
	}

	/**
	 * Gets basic information about the Property
	 *
	 * @param string $identifier Property identifier
	 * @return array Array containing information about the Property
	 */
	function getProperty($identifier) {
		$identifier = sYDB()->escape_string($identifier);
		$sql = "SELECT NAME, ID, IDENTIFIER, VISIBLE, READONLY, TYPE FROM " . $this->_table . " WHERE IDENTIFIER = ?;";
		$resultarray = $this->cacheExecuteGetArray($sql, $identifier);
		return $resultarray;
	}

	/**
	 * Gets list of all Properties for the Object
	 *
	 * @param string $order (optional) "ORDER BY" SQL clause
	 * @param string $identifier (optional) Filters by identifier
	 * @return array Array Properties
	 */
	function getList($order = 'NAME', $identifier) {
		$identifier = sYDB()->escape_string($identifier);
		$order = sYDB()->escape_string(sanitize($order));
		if (strlen($identifier) > 0) {
			$identifier = "%".$identifier."%";
			$prefix_sql = " (IDENTIFIER like ?)  ";
		} else {
			$prefix_sql = "1";
		}
		$sql = "SELECT NAME, READONLY, ID, IDENTIFIER, VISIBLE, TYPE FROM " . $this->_table . " WHERE 1 AND $prefix_sql ORDER BY `$order`;";
		if (strlen($identifier) > 0) {
			$resultarray = $this->cacheExecuteGetArray($sql, "%".$identifier."%");
		} else {
			$resultarray = $this->cacheExecuteGetArray($sql);
		}
		return $resultarray;
	}

}

?>