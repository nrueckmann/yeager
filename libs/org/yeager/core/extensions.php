<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The Extension class, which represents an instance of an Extension.
 */
class Extension extends \framework\Error {
	var $extensionProperties;
	var $extensionPropertySettings;
	var $_code;
	var $_db;
	var $_uid;

	public $history;

	/**
	 * Constructor of the Extension class
	 *
	 * @param string $code Extension code
	 */
	public function __construct($code) {
		$code = mysql_real_escape_string($code);
		$this->_code = $code;
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->extensionProperties = new Properties("yg_ext_" . $this->_code . "_props", 1);
		$this->extensionPropertySettings = new PropertySettings("yg_ext_" . $this->_code . "_props");
	}

	/**
	 * Gets basic information about this Extension
	 *
	 * @return array Extension Info
	 */
	public function getInfo() {
		$info = $this->info;
		$info["CODE"] = $this->_code;
		return $info;
	}

	/**
	 * Gets the Extension type
	 *
	 * @return int|false Extension Type or FALSE in case of an error
	 */
	public function getType() {
		if ($this->info['TYPE']) {
			return $this->info['TYPE'];
		}
		return false;
	}

	/**
	 * Gets the path in the filesystem for the current Extension instance
	 *
	 * @return int|false Extension Type or FALSE in case of an error
	 * @throws Exception
	 */
	function getPath() {
		$sql = "SELECT PATH FROM yg_extensions WHERE (CODE = '" . $this->_code . "');";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray[0]["PATH"];
	}

	/**
	 * Gets the Object Id of the current Object instance
	 *
	 * @return int|false Object Id or FALSE if an error has occured
	 */
	public function getID() {
		if ($this->_object) {
			return $this->_object->getID();
		}
		return false;
	}

	/**
	 * Sets the Extension to "installed"
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setInstalled() {
		$sql = "UPDATE `yg_extensions` SET INSTALLED = '1' WHERE CODE = '" . $this->_code . "';";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Sets the Extension to "uninstalled"
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setUnInstalled() {
		$sql = "UPDATE `yg_extensions` SET INSTALLED = '0' WHERE CODE = '" . $this->_code . "';";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

/// @cond DEV

	/**
	 * Removes all property tables related to this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function uninstallPropertyTables($tablePrefix) {
		$sql = "DROP TABLE IF EXISTS " . $tablePrefix . "_props;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "DROP TABLE IF EXISTS " . $tablePrefix . "_propslv;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "DROP TABLE IF EXISTS " . $tablePrefix . "_propsv;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Creates all property tables related to this Extension
	 *
	 * @param string $tablePrefix Prefix of the property tables to create
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function installPropertyTables($tablePrefix) {
		$sql = "DROP TABLE IF EXISTS " . $tablePrefix . "_props;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "CREATE TABLE IF NOT EXISTS " . $tablePrefix . "_props (
		`ID` int(11) NOT NULL AUTO_INCREMENT,
		`NAME` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`IDENTIFIER` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`VISIBLE` int(11) NOT NULL DEFAULT '1',
		`READONLY` int(11) NOT NULL DEFAULT '0',
		`TYPE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`LISTORDER` int(11) NOT NULL DEFAULT '9999',
		PRIMARY KEY (`ID`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
			$this->_isvalidated = false;
			return false;
		}

		$sql = "DROP TABLE IF EXISTS " . $tablePrefix . "_propslv;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "CREATE TABLE IF NOT EXISTS " . $tablePrefix . "_propslv (
		`ID` int(11) NOT NULL AUTO_INCREMENT,
		`PID` int(11) NOT NULL,
		`VALUE` varchar(200) NOT NULL,
		`LISTORDER` int(11) NOT NULL DEFAULT '9999',
		PRIMARY KEY (`ID`),
		KEY `LISTORDER` (`LISTORDER`,`PID`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "DROP TABLE IF EXISTS " . $tablePrefix . "_propsv;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "CREATE TABLE IF NOT EXISTS " . $tablePrefix . "_propsv (
		`OID` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`OID`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		return true;
	}

/// @endcond

	/**
	 * Adds a custom permission for this Extension
	 *
	 * @param string $code Permission code (Identifier)
	 * @param string $name Name (displayed in the Usergroup administration)
	 */
	public function addPermission($code, $name) {
		$code = strtolower(preg_replace("/[^A-Za-z0-9]/","_",$code));
		sUsergroups()->permissions->addPrivilege($this->_code."_".$code, $name, $this->_code);
	}

	/**
	 * Removes a custom permission for this Extension
	 *
	 * @param string $code Permission code (Identifier)
	 */
	public function removePermission($code) {
		$code = strtolower(preg_replace("/[^A-Za-z0-9]/","_",$code));
		sUsergroups()->permissions->removePrivilege($this->_code."_".$code, $this->_code);
	}

	/**
	 * Checks if a User owns a custom permission
	 *
	 * @param int $userId User Id
	 * @param string $code Permission code (Identifier)
	 */
	public function checkPermission($userId, $code) {
		$code = strtolower(preg_replace("/[^A-Za-z0-9]/","_",$code));
		sUsergroups()->permissions->check($userId, $this->_code."_".$code);
	}

	/**
	 * Installs this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function install() {
		$hasRights = false;
		switch ($this->info['TYPE']) {
			case EXTENSION_PAGE:
				if (sUsergroups()->permissions->check($this->_uid, 'REXTENSIONS_PAGE')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_MAILING:
				if (sUsergroups()->permissions->check($this->_uid, 'REXTENSIONS_MAILING')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_FILE:
				if (sUsergroups()->permissions->check($this->_uid, 'REXTENSIONS_FILE')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_CBLOCK:
				if (sUsergroups()->permissions->check($this->_uid, 'REXTENSIONS_CBLOCK')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_CBLOCKLISTVIEW:
				if (sUsergroups()->permissions->check($this->_uid, 'REXTENSIONS_CBLISTVIEW')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_IMPORT:
				if (sUsergroups()->permissions->check($this->_uid, 'RIMPORT')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_EXPORT:
				if (sUsergroups()->permissions->check($this->_uid, 'REXPORT')) {
					$hasRights = true;
				}
				break;
		}
		if ($hasRights) {
			return $this->installPropertyTables("yg_ext_" . $this->_code);
		} else {
			return false;
		}
	}

	/**
	 * Uninstalls this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function uninstall() {
		$hasRights = false;
		switch ($this->info['TYPE']) {
			case EXTENSION_PAGE:
				if (sUsergroups()->permissions->check($this->_uid, 'REXTENSIONS_PAGE')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_MAILING:
				if (sUsergroups()->permissions->check($this->_uid, 'REXTENSIONS_MAILING')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_FILE:
				if (sUsergroups()->permissions->check($this->_uid, 'REXTENSIONS_FILE')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_CBLOCK:
				if (sUsergroups()->permissions->check($this->_uid, 'REXTENSIONS_CBLOCK')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_CBLOCKLISTVIEW:
				if (sUsergroups()->permissions->check($this->_uid, 'REXTENSIONS_CBLISTVIEW')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_IMPORT:
				if (sUsergroups()->permissions->check($this->_uid, 'RIMPORT')) {
					$hasRights = true;
				}
				break;
			case EXTENSION_EXPORT:
				if (sUsergroups()->permissions->check($this->_uid, 'REXPORT')) {
					$hasRights = true;
				}
				break;
		}
		if ($hasRights) {
			sUsergroups()->permissions->removeAllExtensionPrivileges($this->_code);
			if ($this->uninstallPropertyTables("yg_ext_" . $this->_code)) {
				return $this->setUnInstalled();
			}
		}
		return false;
	}

	/**
	 * Calls a specific Extension hook Callback method
	 *
	 * @param string $callbackName
	 * @param mixed ... (any type of parameters)
	 */
	public function callExtensionHook() {
		$args = func_get_args();
		$callbackName = array_shift($args);
		if (method_exists($this, $callbackName)) {
			try {
				return call_user_func_array(array($this, $callbackName), $args);
			} catch (Exception $e) {
				$msg = $e->getMessage();
				if (strlen($msg) == 0) {
					$msg = $itext['TXT_EXCEPTION_HAS_OCCURED'] . "<br />";
					$msg .= $itext['TXT_EXCEPTION_FILE'] . ": " . $e->getFile() . "<br />";
					$msg .= $itext['TXT_EXCEPTION_LINE'] . ": " . $e->getLine();
				}
				if ($this->frontendMode != 'true') {
					sKoala()->alert(addslashes($msg));
				} else {
					sLog()->error($msg);
				}
				return false;
			}
		}
	}

}

/**
 * The CblockListviewExtension class, which represents an instance of a Cblock List Extension.
 */
class CblockListviewExtension extends Extension {
	var $_code;
	var $_db;

	/**
	 * Constructor of the Cblock-List Extension class
	 *
	 * @param string $code Extension code
	 */
	public function __construct($code) {
		$code = mysql_real_escape_string($code);
		$this->_code = $code;
		parent::__construct($code);
	}

	/**
	 * Installs this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function install() {
		if (parent::install()) {
			$this->extensionProperties = new Properties("yg_ext_" . $this->_code . "_props", 1);
			$this->extensionPropertySettings = new PropertySettings("yg_ext_" . $this->_code . "_props");
			return $this->installPropertyTables("yg_ext_" . $this->_code . "_colistview");
		} else {
			return false;
		}
	}

	/**
	 * Uninstalls this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function uninstall() {
		if (parent::uninstall()) {
			return $this->uninstallPropertyTables("yg_ext_" . $this->_code . "_colistview");
		} else {
			return false;
		}
	}

	/**
	 * Prototype function which returns the columns which should be rendered in the Cblock List
	 *
	 * @return array Array of Columns
	 */
	public function getListColumns() {
		return array();
	}

	/**
	 * Prototype function which returns the actual content of the Cblock List
	 *
	 * @return array|false Array of Cblocks or FALSE in case of an error
	 */
	public function getCblockList($id = 0, $maxlevel = 0, $roleid = 0, $filterArray) {
		return false;
	}

	/**
	 * Prototype callback function which is called when this Extension is rendered in the Extension Admin
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRenderExtensionAdmin($args = NULL) {
		return true;
	}
}

/**
 * The ImportExtension class, which represents an instance of an Import Extension.
 */
class ImportExtension extends Extension {
	var $importProperties;
	var $importPropertySettings;
	var $extensionProperties;
	var $extensionPropertySettings;
	var $_code;
	var $_db;

	/**
	 * Constructor of the ImportExtension class
	 *
	 * @param string $code ImportExtension code
	 */
	public function __construct($code) {
		$code = mysql_real_escape_string($code);
		$this->_code = $code;
		parent::__construct($code);
		$this->extensionProperties = new Properties("yg_ext_" . $this->_code . "_props", 1);
		$this->extensionPropertySettings = new PropertySettings("yg_ext_" . $this->_code . "_props");
		$this->importProperties = new Properties("yg_ext_" . $this->_code . "_import_props", 1);
		$this->importPropertySettings = new PropertySettings("yg_ext_" . $this->_code . "_import_props");
	}

	/**
	 * Installs this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function install() {
		if (parent::install()) {
			$this->extensionProperties = new Properties("yg_ext_" . $this->_code . "_props", 1);
			$this->extensionPropertySettings = new PropertySettings("yg_ext_" . $this->_code . "_props");
			$this->importProperties = new Properties("yg_ext_" . $this->_code . "_import_props", 1);
			$this->importPropertySettings = new PropertySettings("yg_ext_" . $this->_code . "_import_props");
			return $this->installPropertyTables("yg_ext_" . $this->_code . "_import");
		} else {
			return false;
		}
	}

	/**
	 * Uninstalls this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function uninstall() {
		if (parent::uninstall()) {
			return $this->uninstallPropertyTables("yg_ext_" . $this->_code . "_import");
		} else {
			return false;
		}
	}

	/**
	 * Prototype callback function which is called when this Extension is rendered in the Data Admin
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRenderDataAdmin() {
		return true;
	}


	/**
	 * Throws an alert
	 *
	 * @param string $message Message
	 * @param string $title (optional) Title
	 */
	public function alert($message, $title = '') {
		sUI()->alert($message, $title);
	}


}

/**
 * The ExportExtension class, which represents an instance of an Export Extension.
 */
class ExportExtension extends Extension {
	var $exportProperties;
	var $exportPropertySettings;
	var $extensionProperties;
	var $extensionPropertySettings;
	var $_code;
	var $_db;

	/**
	 * Constructor of the ExportExtension class
	 *
	 * @param string $code ExportExtension code
	 */
	public function __construct($code) {
		$code = mysql_real_escape_string($code);
		$this->_code = $code;
		parent::__construct($code);
		$this->extensionProperties = new Properties("yg_ext_" . $this->_code . "_props", 1);
		$this->extensionPropertySettings = new PropertySettings("yg_ext_" . $this->_code . "_props");
		$this->exportProperties = new Properties("yg_ext_" . $this->_code . "_export_props", 1);
		$this->exportPropertySettings = new PropertySettings("yg_ext_" . $this->_code . "_export_props");
	}

	/**
	 * Installs this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function install() {
		if (parent::install()) {
			$this->extensionProperties = new Properties("yg_ext_" . $this->_code . "_props", 1);
			$this->extensionPropertySettings = new PropertySettings("yg_ext_" . $this->_code . "_props");
			$this->exportProperties = new Properties("yg_ext_" . $this->_code . "_export_props", 1);
			$this->exportPropertySettings = new PropertySettings("yg_ext_" . $this->_code . "_export_props");
			return $this->installPropertyTables("yg_ext_" . $this->_code . "_export");
		} else {
			return false;
		}
	}

	/**
	 * Uninstalls this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function uninstall() {
		if (parent::uninstall()) {
			return $this->uninstallPropertyTables("yg_ext_" . $this->_code . "_export");
		} else {
			return false;
		}
	}

	/**
	 * Helper function for delivery of the exported file
	 *
	 * @param string $filename Filename (including full absolute path)
	 * @param string $mimetype MIME-type of the file (optional)
	 * @param string $extensiondir Extension directory
	 */
	public function fetchFile($filename, $mimetype = '', $extensiondir) {
		session_write_close();
		while (@ob_end_clean()) ;
		ini_set("zlib.output_compression", "Off");
		$path = $this->getPath();
		$filename = basename($filename);
		$filepath = $extensiondir . $path . "/exports/" . $filename;
		$filestring = getrealpath($filepath); // combine the path and file
		if (file_exists($filestring)) {
			$filesize = filesize($filestring);
			if ($mimetype == '') {
				$mimetype = "application/octet-stream";
			}
			if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
				$filename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
			}
			if (!$fdl = @fopen($filestring, 'r')) {
				die("Cannot Open File!");
			} else {
				header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 24 * 60 * 60) . ' GMT');
				header("Cache-Control: "); // leave blank to avoid IE errors
				header("Pragma: "); // leave blank to avoid IE errors
				header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
				header("Content-Transfer-Encoding: binary");
				header("Content-Type: $mimetype");
				header("Content-length: " . (string)($filesize));
				while (!feof($fdl)) {
					$buffer = fread($fdl, 4096);
					print $buffer;
				}
				fclose($fdl);
				exit();
			}
		}
	}


	/**
	 * Throws an alert
	 *
	 * @param string $message Message
	 * @param string $title (optional) Title
	 */
	public function alert($message, $title = '') {
		sUI()->alert($message, $title);
	}


	/**
	 * Prototype callback function which is called when this Extension is rendered in the Data Admin
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRenderDataAdmin() {
		return true;
	}
}

/**
 * The PageExtension class, which represents an instance of a Page Extension.
 */
class PageExtension extends Extension {
	var $properties;
	var $propertySettings;
	var $history;
	var $_code;
	var $_db;
	var $_page_object;

	/**
	 * Constructor of the PageExtension class
	 *
	 * @param string $code PageExtension code
	 * @param string $pageId Page Id (optional)
	 * @param string $pageVersion Page version (optional)
	 * @param string $siteId Site Id (optional)
	 */
	public function __construct($code, $pageId = NULL, $pageVersion = NULL, $siteId = NULL) {
		$code = mysql_real_escape_string($code);
		$this->_code = $code;
		parent::__construct($code);
		$uid = NULL;
		if ($pageId && $pageVersion && $siteId) {
			$this->_page_object = new Page($siteId, $pageId, $pageVersion);
			$uid = $this->getPropertyId();
			if (!$uid && ($this->info['ASSIGNMENT'] == EXTENSION_ASSIGNMENT_EXT_CONTROLLED)) {
				$uid = $this->addToPageInternal($pageId, $pageVersion, $siteId);
			}
			$this->properties = new Properties("yg_ext_" . $this->_code . "_pages_props", $uid, $this->_page_object);
		}
		$this->propertySettings = new PropertySettings("yg_ext_" . $this->_code . "_pages_props");
		$this->history = new History($this->_page_object, $this->_page_object->_id_history, NULL);
	}

/// @cond DEV

	/**
	 * Gets the Property Id of the current Extension-Object instance
	 *
	 * @return int Property Id
	 * @throws Exception
	 */
	public function getPropertyId() {
		$page = $this->getPage();
		if (!$page) {
			return false;
		}
		$siteID = $page->getSite();
		$pageID = $page->getID();
		$version = $page->getVersion();

		$sql = "SELECT ID FROM yg_extensions_lnk_pages WHERE CODE = '" . $this->_code . "' AND SITEID = $siteID AND PAGEID = $pageID AND PAGEVERSION = $version;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $ra[0]["ID"];
	}

/// @endcond

	/**
	 * Gets the the current instance of the Object this Extension is added to
	 *
	 * @return object Page Object
	 */
	public function getPage() {
		return $this->_page_object;
	}

	/**
	 * Adds this Extension to the specified Page
	 *
	 * @param string $pageID Page Id
	 * @param string $version Page version
	 * @param string $siteID Site Id
	 * @return int Property Id
	 */
	public function addToPage($pageID, $version, $siteID) {
		$pid = $this->realAddToPage($pageID, $version, $siteID);
		$pageMgr = new PageMgr($siteID);
		$page = $pageMgr->getPage($pageID, $version);
		if ($page) {
			$page->markAsChanged();
		}
		return $pid;
	}

/// @cond DEV

	/**
	 * Adds this Extension to the specified Page (for internal Yeager use only)
	 *
	 * @param string $pageID Page Id
	 * @param string $version Page version
	 * @param string $siteID Site Id
	 * @return int Property Id
	 */
	public function addToPageInternal($pageID, $version, $siteID) {
		return $this->realAddToPage($pageID, $version, $siteID);
	}

/// @endcond

	/**
	 * Helper function for adding this Extension to the specified Page
	 *
	 * @param string $pageID Page Id
	 * @param string $version Page version
	 * @param string $siteID Site Id
	 * @return int Property Id
	 * @throws Exception
	 */
	private function realAddToPage($pageID, $version, $siteID) {
		$siteID = (int)$siteID;
		$pageID = (int)$pageID;
		$version = (int)$version;

		$sql = "INSERT INTO yg_extensions_lnk_pages
		(ID, CODE, SITEID, PAGEID, PAGEVERSION)
		VALUES
		(NULL, '" . $this->_code . "', '$siteID', '$pageID', $version);";
		$result = sYDB()->execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$pid = sYDB()->Insert_ID();
		return $pid;
	}

	/**
	 * Removes this Extension from the specified Page
	 *
	 * @param string $pageID Page Id
	 * @param string $version Page version
	 * @param string $siteID Site Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function removeFromPage($pageID, $version, $siteID) {
		$siteID = (int)$siteID;
		$pageID = (int)$pageID;
		$version = (int)$version;
		$sql = "DELETE FROM yg_extensions_lnk_pages WHERE CODE = '" . $this->_code . "' AND SITEID = $siteID AND PAGEID = $pageID AND PAGEVERSION = $version;";
		$result = sYDB()->execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$pageMgr = new PageMgr($siteID);
		$page = $pageMgr->getPage($pageID, $version);
		if ($page) {
			$page->markAsChanged();
		}
		return true;
	}

	/**
	 * Checks if this Extension is used by the specified Page
	 *
	 * @param string $pageID Page Id
	 * @param string $version Page version
	 * @param string $siteID Site Id
	 * @return bool TRUE if the Extension is used by the Page or FALSE in case of an error
	 * @throws Exception
	 */
	public function usedByPage($pageID, $version, $siteID) {
		if ($this->info['ASSIGNMENT'] == EXTENSION_ASSIGNMENT_EXT_CONTROLLED) {
			return true;
		} else {
			$siteID = (int)$siteID;
			$pageID = (int)$pageID;
			$version = (int)$version;

			if ($version > 0) {
				$versionSQL = "AND (PAGEVERSION = $version)";
			} else {
				$versionSQL = " AND
				(pagestree.ID = $pageID) AND
				(
					(pagestree.VERSIONPUBLISHED = $version) OR
					(
						(pagestree.VERSIONPUBLISHED = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND
						($version = (SELECT MAX(rgt.VERSION) FROM yg_site_" . $siteID . "_properties AS rgt WHERE ($pageID = rgt.OBJECTID) AND (rgt.APPROVED = 1)) )
					)
				) ";
			}

			$sql = "SELECT
						yg_extensions_lnk_pages.ID AS ID
					FROM
						yg_extensions_lnk_pages,
						yg_site_" . $siteID . "_tree AS pagestree
					WHERE
						CODE = '" . $this->_code . "' AND
						SITEID = $siteID AND
						PAGEID = $pageID
						$versionSQL;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$ra = $result->GetArray();
			if ($ra[0]["ID"] > 0) {
				return true;
			}
			return false;
		}
	}

	/**
	 * Installs this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function install() {
		if (parent::install()) {
			return $this->installPropertyTables("yg_ext_" . $this->_code . "_pages");
		}
		return false;
	}

	/**
	 * Uninstalls this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function uninstall() {
		if (parent::uninstall()) {
			if ($this->uninstallPropertyTables("yg_ext_" . $this->_code . "_pages")) {
				$sql = "DELETE FROM yg_extensions_lnk_pages WHERE CODE = '" . $this->_code . "'";
				$result = sYDB()->execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Logs a message to the History of the Page
	 *
	 * @param string $message Message
	 */
	public function log($message) {
		if ($this->history) {
			$extensionManager = new ExtensionMgr();
			$extensionInfo = $this->getInfo();
			$this->history->add(HISTORYTYPE_PAGE, $extensionManager->getIdByCode($extensionInfo['CODE']), $message, 'TXT_EXTENSION_H_LOGENTRY');
		}
	}


	/**
	 * Throws an alert
	 *
	 * @param string $message Message
	 * @param string $title (optional) Title
	 */
	public function alert($message, $title = '') {
		sUI()->alert($message, $title);
	}


	/**
	 * Prototype callback function which is called when this Extension gets added to a Page
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onAdd($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a Page gets removed
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRemove($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a Page gets moved to the Trashcan
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onDelete($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Page gets published
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onPublish($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Page gets a new version
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onVersionNew($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Page gets approved
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onApprove($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a Property of the Page changes
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onPropertyChange($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Page gets rendered
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRender($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when this Extension is rendered in the Extension Admin
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRenderExtensionAdmin($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the access to the Page gets denied
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onAccessDenied($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a user successfully logs in on the Page
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onLoginSuccessful($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the login to the Page fails
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onLoginFailed($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Extension Tab of the Page gets rendered
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRenderExtensionTab($args = NULL) {
		return true;
	}

}

/**
 * The MailingExtension class, which represents an instance of a Mailing Extension.
 */
class MailingExtension extends Extension {
	var $properties;
	var $propertySettings;
	var $history;
	var $_code;
	var $_db;
	var $_mailing_object;

	/**
	 * Constructor of the MailingExtension class
	 *
	 * @param string $code Mailing Extension code
	 * @param string $mailingId Mailing Id (optional)
	 * @param string $mailingVersion Mailing version (optional)
	 */
	public function __construct($code, $mailingId = NULL, $mailingVersion = NULL) {
		$code = mysql_real_escape_string($code);
		$this->_code = $code;
		parent::__construct($code);
		$uid = NULL;
		if ($mailingId && $mailingVersion) {
			$this->_mailing_object = sMailingMgr()->getMailing($mailingId, $mailingVersion);
			$uid = $this->getPropertyId();
			if (!$uid && ($this->info['ASSIGNMENT'] == EXTENSION_ASSIGNMENT_EXT_CONTROLLED)) {
				$uid = $this->addToMailingInternal($mailingId, $mailingVersion);
			}
			$this->properties = new Properties("yg_ext_" . $this->_code . "_mailings_props", $uid, $this->_mailing_object);
			$this->history = new History($this->_mailing_object, $this->_mailing_object->_id_history, NULL);
		}
		$this->propertySettings = new PropertySettings("yg_ext_" . $this->_code . "_mailings_props");
	}

/// @cond DEV

	/**
	 * Gets the Property Id of the current Extension-Object instance
	 *
	 * @return int Property Id
	 * @throws Exception
	 */
	public function getPropertyId() {
		$mailing = $this->getMailing();
		if (!$mailing) {
			return false;
		}
		$mailingID = $mailing->getID();
		$version = $mailing->getVersion();

		$sql = "SELECT ID FROM yg_extensions_lnk_mailings WHERE CODE = '" . $this->_code . "' AND MAILINGID = $mailingID AND MAILINGVERSION = $version;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $ra[0]["ID"];
	}

/// @endcond

	/**
	 * Gets an instance of the Object this Extension is added to
	 *
	 * @return object Mailing Object
	 */
	public function getMailing() {
		return $this->_mailing_object;
	}

	/**
	 * Adds this Extension to the specified Mailing
	 *
	 * @param string $mailingID Mailing Id
	 * @param string $version Mailing version
	 * @return int Property Id
	 */
	public function addToMailing($mailingID, $version) {
		$pid = $this->realAddToMailing($mailingID, $version);
		$mailing = sMailingMgr()->getMailing($mailingID, $version);
		if ($mailing) {
			$mailing->markAsChanged();
		}
		return $pid;
	}

/// @cond DEV

	/**
	 * Adds this Extension to the specified Mailing (for internal Yeager use only)
	 *
	 * @param string $mailingID Mailing Id
	 * @param string $version Mailing version
	 * @return int Property Id
	 */
	public function addToMailingInternal($mailingID, $version) {
		return $this->realAddToMailing($mailingID, $version);
	}

/// @endcond

	/**
	 * Helper function for adding this Extension to the specified Mailing
	 *
	 * @param string $mailingID Mailing Id
	 * @param string $version Mailing version
	 * @return int Property Id
	 * @throws Exception
	 */
	private function realAddToMailing($mailingID, $version) {
		$mailingID = (int)$mailingID;
		$version = (int)$version;

		$sql = "INSERT INTO yg_extensions_lnk_mailings (ID, CODE, MAILINGID, MAILINGVERSION) VALUES (NULL, '" . $this->_code . "', '$mailingID', $version);";
		$result = sYDB()->execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$pid = sYDB()->Insert_ID();
		return $pid;
	}

	/**
	 * Removes this Extension from the specified Mailing
	 *
	 * @param string $pageID Mailing Id
	 * @param string $version Mailing version
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function removeFromMailing($mailingID, $version) {
		$mailingID = (int)$mailingID;
		$version = (int)$version;
		$sql = "DELETE FROM yg_extensions_lnk_mailings WHERE CODE = '" . $this->_code . "' AND MAILINGID = $mailingID AND MAILINGVERSION = $version;";
		$result = sYDB()->execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$mailing = sMailingMgr()->getMailing($mailingID, $version);
		if ($mailing) {
			$mailing->markAsChanged();
		}
		return true;
	}

	/**
	 * Checks if this Extension is used by the specified Mailing
	 *
	 * @param string $mailingID Mailing Id
	 * @param string $version Mailing version
	 * @return bool TRUE if the Extension is used by the Mailing or FALSE in case of an error
	 * @throws Exception
	 */
	public function usedByMailing($mailingID, $version) {
		if ($this->info['ASSIGNMENT'] == EXTENSION_ASSIGNMENT_EXT_CONTROLLED) {
			return true;
		} else {
			$mailingID = (int)$mailingID;
			$version = (int)$version;
			$sql = "SELECT
						ID
					FROM
						yg_extensions_lnk_mailings
					WHERE
						CODE = '" . $this->_code . "' AND
						MAILINGID = $mailingID AND
						MAILINGVERSION = $version";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$ra = $result->GetArray();
			if ($ra[0]["ID"] > 0) {
				return true;
			}
			return false;
		}
	}

	/**
	 * Installs this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function install() {
		if (parent::install()) {
			return $this->installPropertyTables("yg_ext_" . $this->_code . "_mailings");
		}
		return false;
	}

	/**
	 * Uninstalls this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function uninstall() {
		if (parent::uninstall()) {
			if ($this->uninstallPropertyTables("yg_ext_" . $this->_code . "_mailings")) {
				$sql = "DELETE FROM yg_extensions_lnk_mailings WHERE CODE = '" . $this->_code . "'";
				$result = sYDB()->execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Logs a message to the History of the Mailing
	 *
	 * @param string $message Message
	 */
	public function log($message) {
		if ($this->history) {
			$extensionManager = new ExtensionMgr();
			$extensionInfo = $this->getInfo();
			$this->history->add(HISTORYTYPE_MAILING, $extensionManager->getIdByCode($extensionInfo['CODE']), $message, 'TXT_EXTENSION_H_LOGENTRY');
		}
	}


	/**
	 * Throws an alert
	 *
	 * @param string $message Message
	 * @param string $title (optional) Title
	 */
	public function alert($message, $title = '') {
		sUI()->alert($message, $title);
	}


	/**
	 * Prototype callback function which is called when this Extension gets added to a Mailing
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onAdd($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a Mailing gets removed
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRemove($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a Mailing gets moved to the Trashcan
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onDelete($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Mailing gets published
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onPublish($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Mailing gets a new version
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onVersionNew($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Mailing gets approved
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onApprove($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a Property of the Mailing changes
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onPropertyChange($args = NULL) {
		return true;
	}

	/// @cond DEV

	/**
	 * Prototype callback function which is called when the Mailing gets rendered
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRender($args = NULL) {
		return true;
	}

	 /**
	 * Prototype callback function which is called before the Mailing gets scheduled. This allows processing of the emailData before it sending gets scheduled.
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function beforeSend($args = NULL) {
		return $args;
	}


	/**
	 * Prototype callback function which is called when the Mailing is send
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onSend($args = NULL) {
		return true;
	}


	/**
	 * Prototype callback function which is called when this Extension is rendered in the Extension Admin
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRenderExtensionAdmin($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onAccessDenied($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onLoginSuccessful($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onLoginFailed($args = NULL) {
		return true;
	}

	/// @endcond

	/**
	 * Prototype callback function which is called when the Extension Tab of the Mailing gets rendered
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRenderExtensionTab($args = NULL) {
		return true;
	}

}

/**
 * The FileExtension class, which represents an instance of a File Extension.
 */
class FileExtension extends Extension {
	var $properties;
	var $propertySettings;
	var $history;
	var $_code;
	var $_db;
	var $_file_object;

	/**
	 * Constructor of the MailingExtension class
	 *
	 * @param string $code FileExtension code
	 * @param string $fileId File Id (optional)
	 * @param string $fileVersion File version (optional)
	 */
	public function __construct($code, $fileId = NULL, $fileVersion = NULL) {
		$code = mysql_real_escape_string($code);
		$this->_code = $code;
		parent::__construct($code);
		$uid = NULL;
		if ($fileId && $fileVersion) {
			$this->_file_object = new File($fileId, $fileVersion);
			$uid = $this->getPropertyId();
			if (!$uid && ($this->info['ASSIGNMENT'] == EXTENSION_ASSIGNMENT_EXT_CONTROLLED)) {
				$uid = $this->addToFileInternal($fileId, $fileVersion);
			}
			$this->properties = new Properties("yg_ext_" . $this->_code . "_files_props", $uid, $this->_file_object);
			$this->history = new History($this->_file_object, HISTORYTYPE_FILE, NULL);
		}
		$this->propertySettings = new PropertySettings("yg_ext_" . $this->_code . "_files_props");
	}

/// @cond DEV

	/**
	 * Gets the Property Id of the current Extension-Object instance
	 *
	 * @return int Property Id
	 * @throws Exception
	 */
	public function getPropertyId() {
		$file = $this->getFile();
		if (!$file) {
			return false;
		}
		$fileID = $file->getID();
		$version = $file->getVersion();

		$sql = "SELECT ID FROM yg_extensions_lnk_files WHERE CODE = '" . $this->_code . "' AND FILEID = $fileID AND FILEVERSION = $version";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $ra[0]["ID"];
	}

/// @endcond

	/**
	 * Gets the instance of the Object this Extension is added to
	 *
	 * @return object File Object
	 */
	public function getFile() {
		return $this->_file_object;
	}

	/**
	 * Adds this Extension to the specified File
	 *
	 * @param string $fileID File Id
	 * @param string $version File version
	 * @return int Property Id
	 */
	public function addToFile($fileID, $version) {
		$pid = $this->realAddToFile($fileID, $version);
		/*
		 $file = sFileMgr()->getFile($fileID, $version);
		if ($file) $file->markAsChanged();
		*/
		return $pid;
	}

/// @cond DEV

	/**
	 * Adds this Extension to the specified File (for internal yeager use only)
	 *
	 * @param string $fileID File Id
	 * @param string $version File version
	 * @return int Property Id
	 */
	public function addToFileInternal($fileID, $version) {
		return $this->realAddToFile($fileID, $version);
	}

/// @endcond

	/**
	 * Helper function for adding this Extension to the specified File
	 *
	 * @param string $fileID File Id
	 * @param string $version File version
	 * @return int Property Id
	 * @throws Exception
	 */
	private function realAddToFile($fileID, $version) {
		$fileID = (int)$fileID;
		$version = (int)$version;

		$sql = "INSERT INTO yg_extensions_lnk_files (ID, CODE, FILEID, FILEVERSION) VALUES (NULL, '" . $this->_code . "', '$fileID', $version);";
		$result = sYDB()->execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$pid = sYDB()->Insert_ID();
		return $pid;
	}

	/**
	 * Removes this Extension from the specified File
	 *
	 * @param string $fileID Mailing Id
	 * @param string $version Mailing version
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function removeFromFile($fileID, $version) {
		$fileID = (int)$fileID;
		$version = (int)$version;
		$sql = "DELETE FROM yg_extensions_lnk_files WHERE CODE = '" . $this->_code . "' AND FILEID = $fileID AND FILEVERSION = $version;";
		$result = sYDB()->execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		/*
		 $file = sFileMgr()->getFile($fileID, $version);
		if ($file) $file->markAsChanged();
		*/
		return true;
	}

	/**
	 * Checks if this Extension is used by the specified File
	 *
	 * @param string $fileID File Id
	 * @param string $version File version
	 * @return bool TRUE if the Extension is used by the File or FALSE in case of an error
	 * @throws Exception
	 */
	public function usedByFile($fileID, $version) {
		if ($this->info['ASSIGNMENT'] == EXTENSION_ASSIGNMENT_EXT_CONTROLLED) {
			return true;
		} else {
			$fileID = (int)$fileID;
			$version = (int)$version;
			$sql = "SELECT
						ID
					FROM
						yg_extensions_lnk_files
					WHERE
						CODE = '" . $this->_code . "' AND
						FILEID = $fileID AND
						FILEVERSION = $version";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$ra = $result->GetArray();
			if ($ra[0]["ID"] > 0) {
				return true;
			}
			return false;
		}
	}

	/**
	 * Installs this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function install() {
		if (parent::install()) {
			return $this->installPropertyTables("yg_ext_" . $this->_code . "_files");
		}
		return false;
	}

	/**
	 * Uninstalls this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function uninstall() {
		if (parent::uninstall()) {
			if ($this->uninstallPropertyTables("yg_ext_" . $this->_code . "_files")) {
				$sql = "DELETE FROM yg_extensions_lnk_files WHERE CODE = '" . $this->_code . "'";
				$result = sYDB()->execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Logs a message to the History of the File
	 *
	 * @param string $message Message
	 */
	public function log($message) {
		if ($this->history) {
			$extensionManager = new ExtensionMgr();
			$extensionInfo = $this->getInfo();
			$this->history->add(HISTORYTYPE_FILE, $extensionManager->getIdByCode($extensionInfo['CODE']), $message, 'TXT_EXTENSION_H_LOGENTRY');
		}
	}


	/**
	 * Throws an alert
	 *
	 * @param string $message Message
	 * @param string $title (optional) Title
	 */
	public function alert($message, $title = '') {
		sUI()->alert($message, $title);
	}


	/**
	 * Prototype callback function which is called when this Extension gets added to a File
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onAdd($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the source file gets updated
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onUpdate($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a File gets removed
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRemove($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a File gets moved to the Trashcan
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onDelete($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the File gets published
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onPublish($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the File gets a new version
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onVersionNew($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the File gets approved
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onApprove($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a Property of the File gets changed
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onPropertyChange($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the File gets rendered
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRender($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the File gets downloaded
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onDownload($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when this Extension is rendered in the Extension Admin
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRenderExtensionAdmin($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the access to the File gets denied
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onAccessDenied($args = NULL) {
		return true;
	}

	/// @cond DEV

	/**
	 * Prototype callback function
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onLoginSuccessful($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a login to the File this Extension is added to has failed
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onLoginFailed($args = NULL) {
		return true;
	}

	/// @endcond

	/**
	 * Prototype callback function which is called when the Extension tab of the File gets rendered
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRenderExtensionTab($args = NULL) {
		return true;
	}

}

/**
 * The CblockExtension class, which represents an instance of a Cblock Extension.
 */
class CblockExtension extends Extension {
	var $properties;
	var $propertySettings;
	var $history;
	var $_code;
	var $_db;
	var $_cblock_object;

	/**
	 * Constructor of the CblockExtension class
	 *
	 * @param string $code CblockExtension code
	 * @param string $cbId Cblock Id (optional)
	 * @param string $cblockVersion Cblock version (optional)
	 */
	public function __construct($code, $cbId = NULL, $cblockVersion = NULL) {
		$code = mysql_real_escape_string($code);
		$this->_code = $code;
		parent::__construct($code);
		$uid = NULL;
		if ($cbId && $cblockVersion) {
			$this->_cblock_object = sCblockMgr()->getCblock($cbId, $cblockVersion);
			$uid = $this->getPropertyId();
			if (!$uid && ($this->info['ASSIGNMENT'] == EXTENSION_ASSIGNMENT_EXT_CONTROLLED)) {
				$uid = $this->addToCBlockInternal($cbId, $cblockVersion);
			}
			$this->properties = new Properties("yg_ext_" . $this->_code . "_cblocks_props", $uid, $this->_cblock_object);
			$this->history = new History($this->_cblock_object, HISTORYTYPE_CO, NULL);
		}
		$this->propertySettings = new PropertySettings("yg_ext_" . $this->_code . "_cblocks_props");
	}

/// @cond DEV

	/**
	 * Gets the Property Id of the current Extension instance
	 *
	 * @return int Property Id
	 * @throws Exception
	 */
	public function getPropertyId() {
		$cb = $this->getCblock();
		if (!$cb) {
			return false;
		}
		$cbId = $cb->getID();
		$version = $cb->getVersion();

		$sql = "SELECT ID FROM yg_extensions_lnk_cblocks WHERE CODE = '" . $this->_code . "' AND CBID = $cbId AND CBVERSION = $version";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $ra[0]["ID"];
	}

/// @endcond

	/**
	 * Gets the instance of the Object this Extension is added to
	 *
	 * @return object Cblock Object
	 */
	public function getCblock() {
		return $this->_cblock_object;
	}

	/**
	 * Adds this Extension to the specified Cblock
	 *
	 * @param string $cbId Cblock Id
	 * @param string $version Cblock version
	 * @return int Property Id
	 */
	public function addToCBlock($cbId, $version) {
		$pid = $this->realAddToCBlock($cbId, $version);
		$cblock = sCblockMgr()->getCblock($cbId, $version);
		if ($cblock) {
			$cblock->markAsChanged();
		}
		return $pid;
	}

/// @cond DEV

	/**
	 * Adds this Extension to the specified Cblock (for internal yeager use only)
	 *
	 * @param string $cbId Cblock Id
	 * @param string $version Cblock version
	 * @return int Property Id
	 */
	public function addToCBlockInternal($cbId, $version) {
		return $this->realAddToCBlock($cbId, $version);
	}

/// @endcond

	/**
	 * Helper function for adding this Extension to the specified Cblock
	 *
	 * @param string $cbId Cblock Id
	 * @param string $version Cblock version
	 * @return int Property Id
	 * @throws Exception
	 */
	private function realAddToCBlock($cbId, $version) {
		$cbId = (int)$cbId;
		$version = (int)$version;

		$sql = "INSERT INTO yg_extensions_lnk_cblocks (ID, CODE, CBID, CBVERSION) VALUES (NULL, '" . $this->_code . "', '$cbId', $version);";
		$result = sYDB()->execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$pid = sYDB()->Insert_ID();
		return $pid;
	}

	/**
	 * Removes this Extension from the specified Cblock
	 *
	 * @param string $cbId Cblock Id
	 * @param string $version Cblock version
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function removeFromCBlock($cbId, $version) {
		$cbId = (int)$cbId;
		$version = (int)$version;
		$sql = "DELETE FROM yg_extensions_lnk_cblocks WHERE CODE = '" . $this->_code . "' AND CBID = $cbId AND CBVERSION = $version;";
		$result = sYDB()->execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$cblock = sCblockMgr()->getCblock($cbId, $version);
		if ($cblock) {
			$cblock->markAsChanged();
		}
		return true;
	}

	/**
	 * Checks if this Extension is used by the specified Cblock
	 *
	 * @param string $cbId Cblock Id
	 * @param string $version Cblock version
	 * @return bool TRUE if the Extension is used by the Cblock or FALSE in case of an error
	 * @throws Exception
	 */
	public function usedByCblock($cbId, $version) {
		if ($this->info['ASSIGNMENT'] == EXTENSION_ASSIGNMENT_EXT_CONTROLLED) {
			return true;
		} else {
			$cbId = (int)$cbId;
			$version = (int)$version;

			$sql = "SELECT
						ID
					FROM
						yg_extensions_lnk_cblocks
					WHERE
						CODE = '" . $this->_code . "' AND
						CBID = $cbId AND
						CBVERSION = $version";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$ra = $result->GetArray();
			if ($ra[0]["ID"] > 0) {
				return true;
			}
			return false;
		}
	}

	/**
	 * Installs this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function install() {
		if (parent::install()) {
			$result = $this->installPropertyTables("yg_ext_" . $this->_code . "_cblocks");
			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/EXTENSION_INSTALL") == "true") {
				Singleton::FC()->emptyBucket();
			}
			return $result;
		}
		return false;
	}

	/**
	 * Uninstalls this Extension
	 *
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	public function uninstall() {
		if (parent::uninstall()) {
			if ($this->uninstallPropertyTables("yg_ext_" . $this->_code . "_cblocks")) {
				$sql = "DELETE FROM yg_extensions_lnk_cblocks WHERE CODE = '" . $this->_code . "'";
				$result = sYDB()->execute($sql);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/EXTENSION_UNINSTALL") == "true") {
					Singleton::FC()->emptyBucket();
				}
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Logs a message to the History tab of the Cblock
	 *
	 * @param string $message Message
	 */
	public function log($message) {
		if ($this->history) {
			$extensionManager = new ExtensionMgr();
			$extensionInfo = $this->getInfo();
			$this->history->add(HISTORYTYPE_CO, $extensionManager->getIdByCode($extensionInfo['CODE']), $message, 'TXT_EXTENSION_H_LOGENTRY');
		}
	}


	/**
	 * Throws an alert
	 *
	 * @param string $message Message
	 * @param string $title (optional) Title
	 */
	public function alert($message, $title = '') {
		sUI()->alert($message, $title);
	}


	/**
	 * Prototype callback function which is called when this Extension gets added to a Cblock
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onAdd($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a Cblock gets removed
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRemove($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a Cblock gets moved to the Trashcan
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onDelete($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Cblock gets published
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onPublish($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Cblock gets a new version
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onVersionNew($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Cblock gets approved
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onApprove($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when a Property of the Cblock gets changed
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onPropertyChange($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when the Cblock gets rendered
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRender($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function which is called when this Extension is rendered in the Extension Admin
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRenderExtensionAdmin($args = NULL) {
		return true;
	}

	/// @cond DEV

	/**
	 * Prototype callback function
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onAccessDenied($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onLoginSuccessful($args = NULL) {
		return true;
	}

	/**
	 * Prototype callback function
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onLoginFailed($args = NULL) {
		return true;
	}

	/// @endcond

	/**
	 * Prototype callback function which is called when the Extension Tab of the Cblock gets rendered
	 *
	 * @param mixed $args (any type of parameters)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function onRenderExtensionTab($args = NULL) {
		return true;
	}

}

?>