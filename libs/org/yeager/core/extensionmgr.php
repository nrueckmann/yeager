<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Extension-API version
 */
define('EXTENSION_VERSION_MAJOR',  1);
define('EXTENSION_VERSION_MINOR',  0);

/**
 * Extension types
 */
define('EXTENSION_ALL',            0);
define('EXTENSION_PAGE',           1);
define('EXTENSION_IMPORT',         2);
define('EXTENSION_EXPORT',         3);
define('EXTENSION_CBLOCKLISTVIEW', 4);
define('EXTENSION_MAILING',        5);
define('EXTENSION_FILE',           6);
define('EXTENSION_CBLOCK',         7);

/**
 * Extension assignment types
 */
define('EXTENSION_ASSIGNMENT_USER_CONTROLLED', 1);
define('EXTENSION_ASSIGNMENT_EXT_CONTROLLED',  2);

/**
 * The ExtensionMgr class, which represents an instance of the Extension manager.
 */
class ExtensionMgr extends \framework\Error {
	var $_db;
	var $_uid;
	var $id;

	/**
	 * Constructor of the ExtensionMgr class
	 */
	public function __construct() {
		$this->_uid = &sUserMgr()->getCurrentUserID();
	}

	/**
	 * Adds a new Extension
	 *
	 * @param string $code Extension code
	 * @param string $path Extension path
	 * @param string $name Extension name
	 * @param string $developerName Name of the Extension developer
	 * @param string $version Extension version
	 * @param string $description Extension Description
	 * @param string $url Extension url
	 * @param int $type Extension type
	 * @return int|false New Extension Id or FALSE in case of an error
	 * @throws Exception
	 */
	function add($code, $path, $name, $developerName, $version, $description, $url, $type) {
		$code = mysql_real_escape_string(sanitize($code));
		$path = mysql_real_escape_string($path);
		$name = mysql_real_escape_string(sanitize($name));
		$developerName = mysql_real_escape_string(sanitize($developerName));
		$version = mysql_real_escape_string($version);
		$description = mysql_real_escape_string(sanitize($description));
		$url = mysql_real_escape_string(sanitize($url));
		$type = (int)$type;

		$sql = "INSERT INTO `yg_extensions` (`CODE`, `PATH`, `NAME`, `DEVELOPERNAME`, `VERSION`, `DESCRIPTION`, `URL`, `TYPE`, `INSTALLED`)
		VALUES
		('" . $code . "', '" . $path . "', '" . $name . "', '" . $developerName . "', '" . $version . "', '" . $description . "', '" . $url . "', '" . $type . "', '0');";
		$result = sYDB()->execute($sql);

		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return sYDB()->Insert_ID();
	}

	/**
	 * Removes an Extension from the database
	 *
	 * @param string $code Extension code
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function remove($code) {
		$code = mysql_real_escape_string(sanitize($code));
		$sql = "DELETE FROM `yg_extensions` WHERE `CODE` = '".$code."';";
		$result = sYDB()->execute($sql);

		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Gets basic information about a Extension
	 *
	 * @param int $extId Extension Id
	 * @return Array|false Extension Information
	 */
	function get($extId) {
		$extId = (int)$extId;
		if (strlen($extId) > 0) {
			$sql = "SELECT * FROM yg_extensions WHERE (ID = $extId);";
			$result = sYDB()->execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
		}
		return $resultarray[0];
	}

	/**
	 * Gets Extension Id by Extension code
	 *
	 * @param string $code Extension Id
	 * @return int|false Extension Id or FALSE in case of an error
	 */
	function getIdByCode($code) {
		$code = mysql_real_escape_string($code);
		if (strlen($code) > 0) {
			$sql = "SELECT ID FROM yg_extensions WHERE (CODE = '" . $code . "');";
			$result = sYDB()->execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
		}
		return $resultarray[0]["ID"];
	}

	/**
	 * Gets Extension Id by Extension path
	 *
	 * @param string $path Extension path
	 * @return int|false Extension Id or FALSE in case of an error
	 */
	function getIdByPath($path) {
		$path = mysql_real_escape_string($path);
		if (strlen($path) > 0) {
			$sql = "SELECT ID FROM yg_extensions WHERE (PATH = '" . $path . "')";
			$result = sYDB()->execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$resultarray = $result->GetArray();
		}
		return $resultarray[0]["ID"];
	}

	/**
	 * Gets a list of Extensions
	 *
	 * @param int $type Extension type constant
	 * @param bool $onlyInstalled If TRUE, only return installed Extensions
	 * @param bool $hideInternal If TRUE, only return Extensions which are not marked as "internal"
	 * @return array|false Array of Extensions or FALSE in case of an error
	 */
	function getList($type = 0, $onlyInstalled = false, $hideInternal = false) {
		$type = (int)$type;
		$addsql = '';
		$installFilter = " INSTALLED != 2";
		if ($onlyInstalled === true) {
			$installFilter = " INSTALLED = 1";
		}
		if ($hideInternal === true) {
			$installFilter .= " AND (INTERNAL = 0)";
		}
		if ($type > 0) {
			$typeFilter = " (TYPE = '" . $type . "') ";
		} else {
			$typeFilter = " 1 ";
		}
		$sql = "SELECT * FROM `yg_extensions` WHERE $typeFilter AND " . $installFilter . " ORDER BY NAME ASC";
		$result = sYDB()->execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return $result->GetArray();
	}

	/**
	 * Refreshes the internal list of Extensions
	 *
	 * @param string $dir Extension directory
	 */
	function refreshList($dir) {
		$moduledirs = getdirlist($dir);
		for ($m = 0; $m < count($moduledirs); $m++) {
			$path = $moduledirs[$m];
			if (file_exists($dir . $path . "/extension.php") && file_exists($dir . $path . "/extension.xml")) {
				if (!$this->getIdByPath($path)) {

					$extConfig = new \framework\Config($dir . $path . "/extension.xml");
					$extApiVersion = explode('.', (string)$extConfig->getVar("extension/api"));

					if ($extApiVersion[0] != EXTENSION_VERSION_MAJOR) {
						sLog()->error('Extension: API Version mismatch. Expected v'.EXTENSION_VERSION_MAJOR.'.x, Extension has v'.$extApiVersion[0].'.x!');
						return false;
					}

					require_once($dir . $path . "/extension.php");
					$namespace = (string)$extConfig->getVar("extension/namespace");
					$classname = $namespace . "\\" . (string)$extConfig->getVar("extension/class");

					$code = strtolower(preg_replace("/[^A-Za-z0-9]/","_",$classname));

					try {
						$extension = new $classname();
					} catch (Exception $e) {
						return;
					}
					$info = $extension->getInfo();
					if ($this->getIdByCode($code) == NULL) {
						$this->add($code, $path, $info["NAME"], $info["DEVELOPERNAME"], $info["VERSION"], $info["DESCRIPTION"], $info["URL"], $info["TYPE"]);
					}
				}
			}
		}

		// Remove orphaned extensions from database
		$currentExtensions = $this->getList(0, false, true);
		foreach($currentExtensions as $currentExtensionItem) {
			if ( !$currentExtensionItem['INSTALLED'] &&
				 !in_array($currentExtensionItem['PATH'], $moduledirs) ) {
				$currExtension = new Extension($currentExtensionItem['CODE']);
				$currExtension->uninstall();
				$extMgr = new ExtensionMgr();
				$extMgr->remove($currentExtensionItem['CODE']);
			}
		}

	}

	/**
	 * Returns an instance of the specified Extension
	 *
	 * @param string $code Extension code
	 * @param int $objectId (optional) Object Id
	 * @param int $objectVersion (optional) Object version
	 * @param int $objectSite (optional) Object Site
	 * @return Extension|false Extension or FALSE in case of an error
	 */
	function getExtension($code, $objectId = NULL, $objectVersion = NULL, $objectSite = NULL) {
		$id = $this->getIdByCode($code);
		$extInfo = $this->get($id);

		if ($extInfo['INTERNAL'] == 1) {
			// Internal extension, search in yeager/extensions
			$dir = getrealpath(sApp()->app_root) . '/extensions/';
		} else {
			// Normal extension, search in configured extension directory
			$dir = getrealpath(sApp()->app_root . sApp()->extensiondir) . '/';
		}

		$path = $extInfo["PATH"];
		if (file_exists($dir . $path . "/extension.php") && file_exists($dir . $path . "/extension.xml")) {

			$extConfig = new \framework\Config($dir . $path . "/extension.xml");
			$extApiVersion = explode('.', (string)$extConfig->getVar("extension/api"));
			if ($extApiVersion[0] != EXTENSION_VERSION_MAJOR) {
				sLog()->error('Extension-API Version mismatch. Expected v'.EXTENSION_VERSION_MAJOR.'.x, Extension has v'.$extApiVersion[0].'.x!');
				return false;
			}

			require_once($dir . $path . "/extension.php");
			$namespace = (string)$extConfig->getVar("extension/namespace");
			$classname = $namespace . "\\" . (string)$extConfig->getVar("extension/class");
			try {
				return new $classname($code, $objectId, $objectVersion, $objectSite);
			} catch (Exception $e) {
				$msg = $e->getMessage();
				if (strlen($msg) == 0) {
					$msg = $itext['TXT_EXCEPTION_HAS_OCCURED'] . "<br />";
					$msg .= $itext['TXT_EXCEPTION_FILE'] . ": " . $e->getFile() . "<br />";
					$msg .= $itext['TXT_EXCEPTION_LINE'] . ": " . $e->getLine();
				}
				sLog()->error($msg);
				return false;
			}
		}
	}
}

?>