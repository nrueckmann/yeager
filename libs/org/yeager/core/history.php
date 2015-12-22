<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * History types
 */
define("HISTORYTYPE_PAGE",        1);
define("HISTORYTYPE_CO",          2);
define("HISTORYTYPE_ENTRYMASK",   3);
define("HISTORYTYPE_FILE",        4);
define("HISTORYTYPE_EXTERNAL",    5);
define("HISTORYTYPE_IMAGE",       6);
define("HISTORYTYPE_TEMPLATE",    7);
define("HISTORYTYPE_TAG",         8);
define("HISTORYTYPE_FILETYPES",  10);
define("HISTORYTYPE_FILEVIEWS",  11);
define("HISTORYTYPE_JSQUEUE",    12);
define("HISTORYTYPE_SITE",       13);
define("HISTORYTYPE_USER",       14);
define("HISTORYTYPE_USERGROUP",  15);
define("HISTORYTYPE_PERMISSION", 16);
define("HISTORYTYPE_MAILING",    17);

/// @cond DEV

/**
 * The History class, which represents an instance of the History manager.
 */
class History extends \framework\Error {
	var $_table;
	var $_uid;
	var $_sourceid;
	var $_siteID;
	var $permissions;

	/**
	 * Constructor of the History class
	 *
	 * @param int $sourceId Source Id
	 * @param object $permissions Permissions Object
	 * @param int $siteId Site Id
	 */
	function __construct(&$object = NULL, $sourceId, $permissions, $siteId = 0) {
		$this->_table = "yg_history";
		$this->_sourceid = $sourceId;
		$this->_siteID = (int)$siteId;
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->_object = &$object;
		$this->permissions = &$permissions;
	}

	/**
	 * Adds a History entry to this Object
	 *
	 * @param string $targetType Target type
	 * @param string $oldValue Old Value
	 * @param string $newValue New value
	 * @param string $text Text
	 * @param int $targetId Target Id
	 * @param int $from From Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function add($targetType, $oldValue, $newValue, $text, $targetId = 0, $from = 0) {
		$objectId = (int)$this->_object->getID();
		$targetType = (int)$targetType;
		$targetId = (int)$targetId;
		$from = (int)$from;
		$oldValue = sYDB()->escape_string(sanitize($oldValue));
		$newValue = sYDB()->escape_string(sanitize($newValue));
		$text = sYDB()->escape_string(sanitize($text));
		$time = time();
		$sql = "INSERT INTO " . $this->_table . "
					(SOURCEID, `OID` , `DATETIME`, `TEXT` , `UID`, `TYPE`, `OLDVALUE`, `NEWVALUE`, `TARGETID`, `SITEID`, `FROM`)
				VALUES
					(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$result = sYDB()->Execute($sql, $this->_sourceid, $objectId, $time, $text, $this->_uid, $targetType, $oldValue, $newValue, $targetId, $this->_siteID, $from);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Gets the History queue
	 *
	 * @param int $queueId Last queue Id from which the entries should be returned
	 * @return array List of History entries
	 * @throws Exception
	 */
	function getQueue($queueId = 0) {
		$queueId = (int)$queueId;
		$sourcesql = "AND SOURCEID = '" . (int)$this->_sourceid . "'";
		$sql = "SELECT * FROM " . $this->_table . " WHERE ID > ? ". $sourcesql . "ORDER BY DATETIME ASC, ID ASC";
		$result = sYDB()->Execute($sql, $queueId);
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
		$sql = "SELECT MAX(ID) AS MAX_ID FROM " . $this->_table . ";";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();

		return $resultarray[0]['MAX_ID'];
	}

	/**
	 * Gets a list of History entries of the Object
	 *
	 * @param int $objectId Object Id
	 * @param string|array $text (optional) One or multiple text filters
	 * @return array List of History entries
	 * @throws Exception
	 */
	function getList($objectId, $text = '') {
		$objectId = (int)$objectId;
		$sqlargs = array();
		array_push($sqlargs, $objectId);
		if ($this->_sourceid != "") {
			$sourcesql = "AND SOURCEID = ? AND ";
			array_push($sqlargs, $this->_sourceid);
		}
		if (!is_array($text) && strlen($text) > 1) {
			$sourcesql .= "TEXT = ?";
			array_push($sqlargs, $text);
		} else {
			if (is_array($text) && count($text) > 0) {
				for ($t = 0; $t < count($text); $t++) {
					$sourcesql .= "TEXT = ? ";
					array_push($sqlargs, $text[$t]);
					if ($t < count($text) - 1) {
						$sourcesql .= " OR ";
					}
				}
			} else {
				$sourcesql .= "1";
			}
		}

		$sql = "SELECT * FROM " . $this->_table . " WHERE OID = ? ". $sourcesql ." ORDER BY DATETIME DESC, ID DESC";
		array_unshift($sqlargs, $sql);
		$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);
		if ($dbr === false) {
            throw new Exception(sYDB()->ErrorMsg() . ':: ' . $sql);
        }
		$resultarray = $dbr->GetArray();
		return $resultarray;
	}

	/**
	 * Gets the first n History entries of the Object
	 *
	 * @param int $objectId Object Id
	 * @param int $max (optional) Maximum number of entries
	 * @return array List of History entries
	 * @throws Exception
	 */
	function getFirstChanges($objectId, $max = 1) {
		$objectId = (int)$objectId;
		$max = (int)$max;
		if ($this->_sourceid != "") {
			$sourcesql = "AND SOURCEID = '" . (int)$this->_sourceid . "'";
		}
		$sql = "SELECT ID, OID, MIN(DATETIME) AS DATETIME, TEXT, UID FROM " . $this->_table . " WHERE OID = ? ". $sourcesql ." GROUP BY OID ORDER BY DATETIME ASC LIMIT 0, $max";
		$result = sYDB()->Execute($sql, $objectId);
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
	 * Gets n History entries of this Object
	 *
	 * @param int $max (optional) Maximum number of entries
	 * @return array List of History entries
	 * @throws Exception
	 */
	function getChanges($max = 1) {
		$objectId = $this->_object->getID();
		$max = (int)$max;
		if ($this->_sourceid != "") {
			$sourcesql = "AND SOURCEID = '" . (int)$this->_sourceid . "'";
		}
		$sql = "SELECT ID, OID, DATETIME AS DATETIME, TEXT, UID FROM " . $this->_table . " WHERE OID = ? ". $sourcesql ." ORDER BY DATETIME DESC LIMIT 0, $max";
		$result = sYDB()->Execute($sql, $objectId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		$c = 0;
		for ($i = 0; $i < count($resultarray); $i++) {
			$oid = $resultarray[$i]["OID"];
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
	 * Clears the History of this Object
	 *
	 */
	function clear() {
		$objectId = $this->_object->getID();
		if ($this->_sourceid != '') {
			$sourcesql = "AND SOURCEID = ". (int)$this->_sourceid;
		}
		$sql = "DELETE FROM " . $this->_table . " WHERE OID = ? $sourcesql;";
		sYDB()->Execute($sql, $objectId);
	}

	/**
	 * Gets the last History entry
	 *
	 * @param const $type History object type constant
	 * @return array List of History entries
	 * @throws Exception
	 */
	function getLastChange($type, $siteId) {
		$type = (int)$type;
		$siteId = (int)$siteId;
		if ($siteId) {
			$siteSQL = " AND SITEID = $siteId";
		}
		$sql = "SELECT *
			FROM `".$this->_table."`
			WHERE TYPE = ? $siteSQL
			ORDER BY `DATETIME` DESC
			LIMIT 1;";
		$result = sYDB()->Execute($sql, $type);

		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray;
	}

	/**
	 * Gets n last History entries
	 *
	 * @param int $max (optional) Maximum number of entries
	 * @param string|array $text (optional) One or multiple text filters
	 * @return array List of History entries
	 * @throws Exception
	 */
	function getLastChanges($max = 8, $text = '') {
		$max = (int)$max;

		$tmpTableName = 'TMP_'.strtoupper(sApp()->request->parameters['us']).'_'.rand().'_HISTORY';

		$sql = "DROP TEMPORARY TABLE IF EXISTS `$tmpTableName`;";
		$result = sYDB()->Execute($sql);

		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "CREATE TEMPORARY TABLE `$tmpTableName` (
					`ID` int(11) NOT NULL,
					`SOURCEID` varchar(20) NOT NULL,
					`OID` int(11) NOT NULL DEFAULT '0',
					`DATETIME` int(11) DEFAULT NULL,
					`TEXT` text NOT NULL,
					`UID` int(11) NOT NULL DEFAULT '0',
					`TYPE` int(11) NOT NULL,
					`TARGETID` int(11) NOT NULL,
					`OLDVALUE` text NOT NULL,
					`NEWVALUE` text NOT NULL,
					`SITEID` int(11) NOT NULL,
					`FROM` int(11) DEFAULT '0',
					`TYPE_OID` int(11) DEFAULT NULL,
					PRIMARY KEY (`ID`),
					KEY `OID` (`OID`)
				);";
		$result = sYDB()->Execute($sql);

		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sqlargs = array();
		if (!is_array($text) && strlen($text) > 1) {
			$wheresql .= "TEXT=?";
			array_push($sqlargs, $text);
		} else {
			if (is_array($text) && count($text) > 0) {
				for ($t = 0; $t < count($text); $t++) {
					$wheresql .= "TEXT = ? ";
					array_push($sqlargs, $text[$t]);
					if ($t < count($text) - 1) {
						$wheresql .= " OR ";
					}
				}
			} else {
				$wheresql .= "1";
			}
		}
		if ($this->_sourceid != "") {
			$sourcesql = "AND SOURCEID = ?";
			array_push($sqlargs, $this->_sourceid);
		}

		$sql = "INSERT INTO `$tmpTableName`
				SELECT
					*,
					((TYPE *1000000) + OID) AS `TYPE_OID`
				FROM " . $this->_table . "
				WHERE $wheresql $sourcesql
				ORDER BY `DATETIME` DESC
				LIMIT 0, 2000;";

		array_unshift($sqlargs, $sql);
		$dbr = call_user_func_array(array(sYDB(), 'Execute'), $sqlargs);

		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		// Get folder for embedded cblocks
		$embeddedCblockFolder = (int)sConfig()->getVar('CONFIG/EMBEDDED_CBLOCKFOLDER');

		// Remove all embedded Cblocks from temporary table
		$sql = "DELETE
				FROM
					`$tmpTableName`
				USING
					`$tmpTableName`
				INNER JOIN
					`yg_contentblocks_tree`
				WHERE
					(`$tmpTableName`.OID = `yg_contentblocks_tree`.ID) AND
					(`yg_contentblocks_tree`.PARENT = " . $embeddedCblockFolder . ") AND
					(TYPE = " . HISTORYTYPE_CO . ");";
		$result = sYDB()->Execute($sql);

		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "SELECT *, (SELECT
						MAX(`DATETIME`)
					FROM
						" . $this->_table . " AS `h2`
					WHERE
						`h2`.`OID` = `lft`.`OID`) AS `MAXDATETIME`
				FROM `$tmpTableName` AS `lft`
				GROUP BY `TYPE_OID`
				ORDER BY `DATETIME` DESC
				LIMIT 0, $max;";
		$result = sYDB()->Execute($sql);

		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();

		for ($i = 0; $i < count($resultarray); $i++) {
			$oid = $resultarray[$i]['OID'];
			$rread = false;
			if ($this->permissions == NULL) {
				if ($resultarray[$i]['SITEID'] && ($resultarray[$i]['TYPE'] == HISTORYTYPE_PAGE)) {
					$tmpPageMgr = new PageMgr($resultarray[$i]['SITEID']);
					if ($tmpPageMgr->permissions->checkInternal($this->_uid, $oid, "RREAD")) {
						$rread = true;
					}
				}
				if ($resultarray[$i]['TYPE'] == HISTORYTYPE_CO) {
					if (sCblockMgr()->permissions->checkInternal($this->_uid, $oid, "RREAD")) {
						$rread = true;
					}
				}
				if ($resultarray[$i]['TYPE'] == HISTORYTYPE_FILE) {
					if (sFileMgr()->permissions->checkInternal($this->_uid, $oid, "RREAD")) {
						$file = sFileMgr()->getFile($oid);
						if ($file) {
							$fileinfo = $file->get();
							if ($fileinfo["FOLDER"] == 0) $rread = true;
						}
					}
				}
			} else {
				if ($this->permissions->checkInternal($this->_uid, $oid, "RREAD")) {
					$rread = true;
				}
			}
			if ($rread) {
				if ($resultarray[$i]['TYPE'] == HISTORYTYPE_CO) {
					$tmpCblock = sCblockMgr()->getCblock($resultarray[$i]['OID']);
					if ($tmpCblock) {
						$tmpCblockInfo = $tmpCblock->get();
						$embeddedCblockFolder = (int)sConfig()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");
						if ($tmpCblockInfo['PARENT'] != $embeddedCblockFolder) {
							$ra[] = $resultarray[$i];
						}
					}
				} else {
					$ra[] = $resultarray[$i];
				}
			}
		}
		return $ra;
	}
}

/// @endcond

?>