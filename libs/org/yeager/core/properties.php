<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The Properties class, which represents Properties of an Object.
 */
class Properties extends \framework\Error {
	var $_table;
	var $_property_id;
	var $_object;
	var $_uid;

	/**
	 * Constructor of the Properties class
	 *
	 * @param string $table Name of the table into which the Properties will be saved
	 * @param int $propertyId Property Id (versioned Object Id)
	 */
	function __construct($table = '', $propertyId = NULL, &$object) {
		$this->_table = $table;
		$this->_uid = &sUserMgr()->getCurrentUserID();
		if ($propertyId == NULL) {
			return;
		}
		$this->_property_id = $propertyId;
		if ($object) {
			$this->_object = &$object;
		}
	}

/// @cond DEV

	/**
	 * Helper method for querying the database
	 *
	 * @param string $sql SQL query
	 * @return array|bool Result of SQL query or FALSE in case of an error
	 * @throws Exception
	 */
	function cacheExecuteGetArray($sql) {
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
		}
		$blaetter = $dbr->GetArray();
		return $blaetter;
	}

/// @endcond

	/**
	 * Sets a Property value
	 *
	 * @param string $identifier Property identifier
	 * @param mixed $value Property value
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setValue($identifier, $value) {
		if (($this->_object == NULL) ||
			($this->_object->permissions->checkInternal($this->_uid, $this->_object->getID(), 'RWRITE'))
		) {
			$oid = (int)$this->_property_id;
			$identifier = mysql_real_escape_string(sanitize($identifier));
			$sql = "SELECT OID FROM `" . $this->_table . "v` WHERE OID = $oid;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
				return false;
			}
			$resultarray = $result->GetArray();

			$sql = "SELECT TYPE FROM `" . $this->_table . "` WHERE IDENTIFIER = '" . $identifier . "';";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
				return false;
			}
			$typeresultarray = $result->GetArray();
			if ($typeresultarray[0]['TYPE'] == 'PAGE') {
				if (is_array($value)) {
					$value = json_encode($value);
				}
			}

			$value = mysql_real_escape_string($value);

			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/PROPERTY_CHANGE") == "true") {
				Singleton::FC()->emptyBucket();
			}

			if ($this->_object) {
				// Mark object as changed
				$this->_object->markAsChanged();
			}

			if (count($resultarray) > 0) {
				$sql = "UPDATE `" . $this->_table . "v` SET `$identifier` = '$value' WHERE OID = $oid;";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				return true;
			} else {
				$sql = "INSERT INTO `" . $this->_table . "v` ( `OID` , `$identifier` ) VALUES ($oid, '$value');";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				return true;
			}
		} else {
			return false;
		}
	}

/// @cond DEV

	/**
	 * Gets a Property value
	 *
	 * @param string $identifier Property identifier
	 * @return string|false Property value or FALSE in case of an error
	 */
	function getValueInternal($identifier) {
		if (($this->_object == NULL) ||
			($this->_object->permissions->checkInternal($this->_uid, $this->_object->getID(), 'RREAD'))
		) {
			$oid = (int)$this->_property_id;
			$identifier = mysql_real_escape_string($identifier);
			if ($identifier === false) {
				return false;
			}
			$sql = "SELECT `$identifier` AS VALUE FROM `" . $this->_table . "v` WHERE OID = $oid;";

			$result = sYDB()->Execute($sql);
			$resultarray = $this->cacheExecuteGetArray($sql);

			$value = $resultarray[0]["VALUE"];

			$sql = "SELECT TYPE FROM `" . $this->_table . "` WHERE IDENTIFIER = '" . $identifier . "';";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
				return false;
			}
			$typeresultarray = $result->GetArray();
			if ($typeresultarray[0]['TYPE'] == 'PAGE') {
				$value = json_decode($value, true);
			}
			return $value;
		} else {
			return false;
		}
	}

/// @endcond

	/**
	 * Gets basic information about the Property
	 *
	 * @param string $identifier Property identifier
	 * @return array Array containing information about the Property
	 */
	function getProperty($identifier) {
		if (($this->_object == NULL) ||
			($this->_object->permissions->checkInternal($this->_uid, $this->_object->getID(), 'RREAD'))
		) {
			$identifier = mysql_real_escape_string($identifier);
			$sql = "SELECT NAME, ID, IDENTIFIER, VISIBLE, READONLY, TYPE FROM " . $this->_table . " WHERE IDENTIFIER = '" . $identifier . "'";
			$resultarray = $this->cacheExecuteGetArray($sql);
			return $resultarray;
		} else {
			return false;
		}
	}

	/**
	 * Gets a list of all Properties of the Object
	 *
	 * @param string $order (optional) "ORDER BY" SQL clause
	 * @return array Array Properties
	 */
	function getList($order = 'NAME') {
		if (($this->_object == NULL) ||
			($this->_object->permissions->checkInternal($this->_uid, $this->_object->getID(), 'RREAD'))
		) {
			$prefix_sql = "1";
			$sql = "SELECT NAME, READONLY, ID, IDENTIFIER, VISIBLE, TYPE FROM " . $this->_table . " ORDER BY $order;";
			$resultarray = $this->cacheExecuteGetArray($sql);
			return $resultarray;
		} else {
			return false;
		}
	}

	/**
	 * Clears all Property values of this Object
	 *
	 * @param integer $propertyId (optional) Property Id (versioned Object Id), if provided only property values for this specific version will be cleared
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function clear($propertyId = 0) {
		if (($this->_object == NULL) ||
			($this->_object->permissions->checkInternal($this->_uid, $this->_object->getID(), 'RWRITE'))
		) {
			if ($propertyId == 0) {
				$propertyId = $this->_property_id;
			}
			$proplist = $this->getList();
			for ($i = 0; $i < count($proplist); $i++) {
				$sql = "DELETE FROM `" . $this->_table . "v` WHERE OID = $propertyId;";
				$result = sYDB()->Execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets all the pure Property values for the Object (fast, without postprocessing)
	 *
	 * @return array Array of Property values
	 */
	function getValues() {
		if (($this->_object == NULL) ||
			($this->_object->permissions->checkInternal($this->_uid, $this->_object->getID(), 'RREAD'))
		) {
			$oid = (int)$this->_property_id;
			$sql = "SELECT o.OID AS ID, o.* FROM `" . $this->_table . "v` AS o WHERE o.OID = $oid;";
			$resultarray = $this->cacheExecuteGetArray($sql);
			return $resultarray[0];
		} else {
			return false;
		}
	}


	/**
	 * Gets a Property value (post processed)
	 *
	 * @param string $identifier Property identifier
	 * @return mixed|false Property value or FALSE in case of an error
	 */
	function getValue($identifier) {
		if (($this->_object == NULL) || ($this->_object->permissions->checkInternal($this->_uid, $this->_object->getID(), 'RREAD'))) {
			$oid = (int)$this->_property_id;
			$identifier = mysql_real_escape_string($identifier);
			if ($identifier === false) {
				return false;
			}
			$sql = "SELECT `$identifier` AS VALUE FROM `" . $this->_table . "v` WHERE OID = $oid;";

			sYDB()->Execute($sql);
			$resultarray = $this->cacheExecuteGetArray($sql);

			$value = $resultarray[0]["VALUE"];

			$sql = "SELECT NAME, ID, IDENTIFIER, VISIBLE, READONLY, TYPE FROM `" . $this->_table . "` WHERE IDENTIFIER = '" . $identifier . "';";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
				return false;
			}
			$typeresultarray = $result->GetArray();
			return $this->postProcessValue($typeresultarray[0]['TYPE'], $value);
		} else {
			return false;
		}
	}

	/**
	 * Post processes a property value (resolves urls, permanent names, etc.)
	 *
	 * @param string $type Property type
	 * @param string $value Property value
	 * @return mixed Post processed property value
	 */
	function postProcessValue($type, $value) {
		switch($type) {
			case 'LINK':
				$linkInfo = checkLinkInternalExternal(resolveSpecialURL($value));
				if ($linkInfo['TYPE'] == 'internal') {
					return resolveSpecialURL($linkInfoJSON['href']);
				} elseif ($linkInfo['TYPE'] == 'file') {
					$pname = sFileMgr()->getPNameByFileId($linkInfo['INFO']['FILE_ID']);
					if ($pname) {
						return sApp()->webroot.'download/'.$pname;
					}
				} else if ($value != '') {
					return $value;
				} else {
					return NULL;
				}
			case 'PAGE':
				$pageInfo = json_decode($value, true);
				$tmpPageMgr = sPageMgr($pageInfo['site']);
				$tmpPage = $tmpPageMgr->getPage($pageInfo['page']);
				if ($tmpPage) {
					$tmpUrl = $tmpPage->getUrl();
					$tmpPname = $tmpPageMgr->getPNameByPageId($pageInfo['page']);
					return array(
						'SITE_ID' => $pageInfo['site'],
						'PAGE_ID' => $pageInfo['page'],
						'URL' => $tmpUrl,
						'PNAME' => $tmpPname,
						'VALUE' => $value
					);
				} else {
					return NULL;
				}
			case 'FILE':
				$pname = sFileMgr()->getPNameByFileId($value);
				if ($pname) {
					return array(
						'FILE_ID' => $value,
						'URL' => sApp()->webroot.'download/'.$pname,
						'IMAGE_URL' => sApp()->webroot.'image/'.$pname,
						'PNAME' => $pname,
						'VALUE' => $value
					);
				} else {
					return NULL;
				}
			case 'RICHTEXT':
				return replaceSpecialURLs($value);
				break;
			case 'CBLOCK':
				$pname = sCblockMgr()->getPNameByCblockId($value);
				if ($pname) {
					return array(
						'CBLOCK_ID' => $value,
						'PNAME' => $pname,
						'VALUE' => $value
					);
				} else {
					return NULL;
				}
			case 'TAG':
				$tagInfo = sTags()->get($value);
				if ($tagInfo) {
					return array(
						'TAG_ID' => $value,
						'NAME' => $tagInfo['NAME'],
						'VALUE' => $value
					);
				} else {
					return NULL;
				}
			default:
				return $value;
		}
	}


	/**
	 * Gets all Property values for the Object (all Properties resolved, post processed)
	 *
	 * @return array Array of Property values
	 */
	function get() {
		if (($this->_object == NULL) || ($this->_object->permissions->checkInternal($this->_uid, $this->_object->getID(), 'RREAD'))) {
			$oid = (int)$this->_property_id;
			$sql = "SELECT o.OID AS ID, o.* FROM `" . $this->_table . "v` AS o WHERE o.OID = $oid;";
			$resultArray = $this->cacheExecuteGetArray($sql);

			$sql = "SELECT NAME, ID, IDENTIFIER, VISIBLE, READONLY, TYPE FROM " . $this->_table . ";";
			$propertiesResultArray = $this->cacheExecuteGetArray($sql);

			foreach($propertiesResultArray as $propertiesResultArrayItem) {
				if (isset($resultArray[0][$propertiesResultArrayItem['IDENTIFIER']])) {
					$resultArray[0][$propertiesResultArrayItem['IDENTIFIER']] = $this->postProcessValue($propertiesResultArrayItem['TYPE'], $resultArray[0][$propertiesResultArrayItem['IDENTIFIER']]);
				}
			}
			return $resultArray[0];
		} else {
			return false;
		}
	}

	/**
	 * Copies all Property values from one Object to another
	 *
	 * @param int $sourcePropertyId Source Property Id (versioned Object Id)
	 * @param int $targetPropertyId Target Property Id (versioned Object Id)
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function copyTo($sourcePropertyId, $targetPropertyId) {
		$sourcePropertyId = (int)$sourcePropertyId;
		$targetPropertyId = (int)$targetPropertyId;

		$this->clear($targetPropertyId);
		$properties = $this->getList();

		if (count($properties) == 0) {
			return true;
		}

		$tsql = '';
		for ($p = 0; $p < count($properties); $p++) {
			$tsql .= '`' . $properties[$p]["IDENTIFIER"] . '`';
			if (($p + 1) < count($properties)) {
				$tsql .= ",";
			}
		}

		$sql = "INSERT INTO `" . $this->_table . "v`
					(OID, $tsql)
				SELECT $targetPropertyId, $tsql
				FROM `" . $this->_table . "v` WHERE (OID = '$sourcePropertyId');";

		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

}

?>