<?php

/**
 * History types
 */
define("HISTORYTYPE_PAGE", 1);
define("HISTORYTYPE_CO", 2);
define("HISTORYTYPE_ENTRYMASK", 3);
define("HISTORYTYPE_FILE", 4);
define("HISTORYTYPE_EXTERNAL", 5);
define("HISTORYTYPE_IMAGE", 6);
define("HISTORYTYPE_TEMPLATE", 7);
define("HISTORYTYPE_TAG", 8);
define("HISTORYTYPE_FILETYPES", 10);
define("HISTORYTYPE_FILEVIEWS", 11);
define("HISTORYTYPE_JSQUEUE", 12);
define("HISTORYTYPE_SITE", 13);
define("HISTORYTYPE_USER", 14);
define("HISTORYTYPE_USERGROUP", 15);
define("HISTORYTYPE_PERMISSION", 16);
define("HISTORYTYPE_MAILING", 17);

/// @cond DEV

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The JSQueue class, which represents an instance of the JSQueue manager.
 */
class JSQueue extends \framework\Error {
	var $_table;
	var $_uid;
	var $_sourceid;
	var $_siteID;
	var $permissions;

	/**
	 * Constructor of the JSQueue class
	 *
	 * @param object $permissions Permissions Object
	 * @param int $siteId Site Id
	 */
	function __construct($permissions, $siteId = 0) {
		$this->_table = "yg_jsqueue";
		$this->_sourceid = HISTORYTYPE_JSQUEUE;
		$this->_siteID = (int)$siteId;
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->permissions = &$permissions;
	}

	/**
	 * Adds a JSQueue entry
	 *
	 * @param int $objectId Object Id
	 * @param string $targetType Target type
	 * @param string $oldValue Old Value
	 * @param string $newValue New value
	 * @param string $text Text
	 * @param int $targetId Target Id
	 * @param int $from From Id
	 * @param string $value1 General purpose value 1
	 * @param string $value2 General purpose value 2
	 * @param string $value3 General purpose value 3
	 * @param string $value4 General purpose value 4
	 * @param string $value5 General purpose value 5
	 * @param string $value6 General purpose value 6
	 * @param string $value7 General purpose value 7
	 * @param string $value8 General purpose value 8
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function add($objectId, $targetType, $oldValue, $newValue, $text, $targetId = 0, $from = 0, $value1, $value2, $value3, $value4, $value5, $value6, $value7, $value8) {
		$objectId = (int)$objectId;
		$targetType = (int)$targetType;
		$targetId = (int)$targetId;
		$from = (int)$from;
		$oldValue = mysql_real_escape_string($oldValue);
		$newValue = mysql_real_escape_string($newValue);
		$text = mysql_real_escape_string($text);
		$value1 = mysql_real_escape_string($value1);
		$value2 = mysql_real_escape_string($value2);
		$value3 = mysql_real_escape_string($value3);
		$value4 = mysql_real_escape_string($value4);
		$value5 = mysql_real_escape_string($value5);
		$value6 = mysql_real_escape_string($value6);
		$value7 = mysql_real_escape_string($value7);
		$value8 = mysql_real_escape_string($value8);
		$time = time();
		$sql = "INSERT INTO " . $this->_table . "
					(SOURCEID, `OID` , `DATETIME`, `TEXT` , `UID`, `TYPE`, `OLDVALUE`, `NEWVALUE`, `TARGETID`, `SITEID`, `FROM`, `VALUE1`, `VALUE2`, `VALUE3`, `VALUE4`, `VALUE5`, `VALUE6`, `VALUE7`, `VALUE8`)
				VALUES
					('" . $this->_sourceid . "', '$objectId', '$time', '" . $text . "', '" . $this->_uid . "', $targetType, '" . $oldValue . "', '" . $newValue . "', $targetId, '" . $this->_siteID . "', $from, '" . $value1 . "', '" . $value2 . "', '" . $value3 . "', '" . $value4 . "', '" . $value5 . "', '" . $value6 . "', '" . $value7 . "', '" . $value8 . "');";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Gets the JSQueue
	 *
	 * @param int $queueId Last queue Id from which the entries should be returned
	 * @return array List of JSQueue entries
	 * @throws Exception
	 */
	function getQueue($queueId = 0) {
		$queueId = (int)$queueId;
		$sourcesql = "AND SOURCEID = '" . $this->_sourceid . "'";
		$sql = "SELECT * FROM " . $this->_table . " WHERE ID > " . $queueId . " $sourcesql ORDER BY DATETIME ASC, ID ASC";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray;
	}

	/**
	 * Gets the last queue entry
	 *
	 * @return int Last queue Id
	 * @throws Exception
	 */
	function getLastQueueId() {
		$sql = "SELECT MAX(ID) AS ID FROM " . $this->_table . ";";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray[0]['ID'];
	}

	/**
	 * Gets a list of JSQueue entries for the Object
	 *
	 * @param int $objectId Object Id
	 * @return array List of JSQueue entries
	 * @throws Exception
	 */
	function getList($objectId) {
		$objectId = (int)$objectId;
		if ($this->_sourceid != "") {
			$sourcesql = "AND SOURCEID = '" . $this->_sourceid . "'";
		}
		$sql = "SELECT * FROM " . $this->_table . " WHERE OID = $objectId $sourcesql ORDER BY DATETIME DESC, ID DESC";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray;
	}

	/**
	 * Gets the first n JSQueue entries for the Object
	 *
	 * @param int $objectId Object Id
	 * @param int $max (optional) Maximum number of entries
	 * @return array List of JSQueue entries
	 * @throws Exception
	 */
	function getFirstChanges($objectId, $max = 1) {
		$objectId = (int)$objectId;
		$max = (int)$max;
		if ($this->_sourceid != "") {
			$sourcesql = "AND SOURCEID = '" . $this->_sourceid . "'";
		}
		$sql = "SELECT ID, OID, MIN(DATETIME) AS DATETIME, TEXT, UID FROM " . $this->_table . " WHERE OID = $objectId $sourcesql GROUP BY OID ORDER BY DATETIME ASC LIMIT 0, $max";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		for ($i = 0; $i < count($resultarray); $i++) {
			$oid = $resultarray[$i]["OID"];
			$c = 0;
			$rread = false;
			if ($this->permissions == NULL) {
				$rread = true;
			}
			if ($this->permissions->checkInternal($this->_uid, $oid, "RREAD")) {
				$rread = true;
			}
			if ($rread) {
				$ra[] = $resultarray[$i];
				$user = new User($resultarray[$i]["UID"]);
				$uinfo = $user->get();
				$uinfo['PROPS'] = $user->properties->getValues($resultarray[$i]["UID"]);
				$username = $uinfo['PROPS']["FIRSTNAME"] . " " . $uinfo['PROPS']["LASTNAME"];
				$userid = $uinfo["ID"];
				$ra[$c]["USERNAME"] = $username;
				$ra[$c]["USERID"] = $userid;
				$c++;
			}
		}
		return $ra[0];
	}

	/**
	 * Clears the JSQueue for the Object
	 *
	 * @param int $objectId
	 */
	function clear($objectId) {
		$objectId = (int)$objectId;
		if ($this->_sourceid != "") {
			$sourcesql = "AND SOURCEID = '" . $this->_sourceid . "'";
		}
		$sql = "DELETE FROM " . $this->_table . " WHERE OID = $objectId $sourcesql ";
		$result = sYDB()->Execute($sql);
	}

	/**
	 * Gets n last JSQueue entries
	 *
	 * @param int $max (optional) Maximum number of entries
	 * @param string|array $text (optional) One or more Text filters
	 * @return array List of JSQueue entries
	 * @throws Exception
	 */
	function getLastChanges($max = 8, $text = '') {
		$max = (int)$max;
		$sql = "SELECT *, (SELECT MAX(DATETIME) FROM " . $this->_table . " AS h2 WHERE h2.OID = lft.OID) AS MAXDATETIME FROM " . $this->_table . " AS lft WHERE ";

		if (!is_array($text) && strlen($text) > 1) {
			$text = mysql_real_escape_string($text);
			$sql .= "TEXT='$text'";
		} else {
			if (is_array($text) && count($text) > 0) {
				for ($t = 0; $t < count($text); $t++) {
					$textitem = mysql_real_escape_string($text[$t]);
					$sql .= "TEXT = '" . $textitem . "' ";
					if ($t < count($text) - 1) {
						$sql .= " OR ";
					}
				}
			} else {
				$sql .= "1";
			}
		}
		if ($this->_sourceid != "") {
			$sourcesql = "AND SOURCEID = '" . $this->_sourceid . "'";
		}
		$sql .= " $sourcesql GROUP BY OID ORDER BY DATETIME DESC LIMIT 0, $max";

		$result = sYDB()->Execute($sql);

		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		for ($i = 0; $i < count($resultarray); $i++) {
			$oid = $resultarray[$i]["OID"];
			$rread = false;
			if ($this->permissions == NULL) {
				$rread = true;
			} else {
				if ($this->permissions->checkInternal($this->_uid, $oid, "RREAD")) {
					$rread = true;
				}
			}
			if ($rread) {
				$ra[] = $resultarray[$i];
			} else {
			}
		}
		return $ra;
	}
}

/// @endcond

?>