<?php

/// @cond DEV

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The Languages class, which represents an instance of the Language manager.
 */
class Languages {
	var $_db;
	var $_uid;

	/**
	 * Constructor of the JSQueue class
	 */
	function __construct() {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();
	}

	/**
	 * Adds a Language
	 *
	 * @param int $name Language name
	 * @return int Language Id of new Language
	 */
	function add($name) {
		$name = mysql_real_escape_string(sanitize($name));
		$sql = "INSERT INTO
					`yg_languages`
				( `NAME`  )
					VALUES
				('" . $name . "');";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return sYDB()->Insert_ID();
	}

	/**
	 * Sets the Language name
	 *
	 * @param int $languageId Language Id
	 * @param string $name Language Name
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setName($languageId, $name) {
		$languageId = (int)$languageId;
		$name = mysql_real_escape_string(sanitize($name));
		$sql = "UPDATE
					`yg_languages`
				 SET NAME = '" . $name . "' WHERE ID = $languageId;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Sets the Language Code
	 *
	 * @param int $languageId Language Id
	 * @param string $code Language Code
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setCode($languageId, $code) {
		$languageId = (int)$languageId;
		$code = mysql_real_escape_string(sanitize($code));
		$sql = "UPDATE
					`yg_languages`
				 SET CODE = '" . $code . "' WHERE ID = $languageId;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Gets basic information about the Language
	 *
	 * @param int $languageId Language Id
	 * @return array Array containing information about the Language
	 */
	function get($languageId) {
		$languageId = (int)$languageId;
		$sql = "SELECT * FROM yg_languages WHERE ID = $languageId;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray[0];
	}

	/**
	 * Get list of all languages
	 *
	 * @return array List of Languages
	 */
	function getList() {
		$sql = "SELECT * FROM yg_languages";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray;
	}

	/**
	 * Removes a Language
	 *
	 * @param int $languageId Language Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function remove($languageId) {
		$languageId = (int)$languageId;
		$sql = "DELETE FROM yg_languages WHERE ID = $languageId;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		} else {
			return true;
		}
	}
}

/// @endcond

?>