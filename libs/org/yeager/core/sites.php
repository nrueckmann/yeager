<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Gets an instance of Sites
 * @return object Sites Object
 */
function sSites() {
	return Singleton::sites();
}

/**
 * The Sites class, which represents an instance of the Site manager.
 */
class Sites {
	var $_db;
	var $_uid;

	/**
	 * Constructor of the Sites class
	 */
	function __construct() {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();
	}

	/**
	 * Adds a new Site
	 *
	 * @param string $name Site name
	 * @return false|int Site Id or FALSE in case of an error
	 * @throws Exception
	 */
	public function add($name) {
		if (sUsergroups()->permissions->check($this->_uid, 'RSITES')) {
			$name = mysql_real_escape_string($name);
			$sql = "INSERT INTO `yg_site` (`ID`, `NAME`) VALUES (NULL, '" . $name . "');";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$siteID = sYDB()->Insert_ID();
			if ($this->createSiteTables($siteID, $name)) {
				$roles = sUsergroups()->getList();
				$tmpPageMgr = new PageMgr($siteID);
				foreach ($roles as $role_idx => $roles_item) {
					$privileges = sUsergroups()->permissions->getByUsergroup($roles_item['ID']);
					if ($privileges['RSITES']) {
						$tmpPageMgr->permissions->setPermissions(array('OID' => 1, 'USERGROUPID' => $roles_item['ID']), 1);
					}
				}
				return $siteID;
			}
			return false;
		} else {
			return false;
		}
	}

	/**
	 * Removes a Site
	 *
	 * @param int $id Site Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function remove($id) {
		if (sUsergroups()->permissions->check($this->_uid, 'RSITES')) {
			$id = (int)$id;
			$sql = "DELETE FROM `yg_site` WHERE ID = $id;";
			$result = sYDB()->Execute($sql);
			$this->removeSiteTables($id);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if a Site exists
	 *
	 * @param int $id Site Id
	 * @return bool TRUE if the Site exists, FALSE if not
	 */
	public function siteExists($id) {
		$id = (int)$id;
		$sql = "SELECT * FROM `yg_site` WHERE ID = $id";
		$result = sYDB()->Execute($sql);
		$resultarray = $result->GetArray();
		return (count($resultarray) > 0);
	}

/// @cond DEV

	/**
	 * Removes all database tables for the specified Site
	 *
	 * @param int $id Site Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	private function removeSiteTables($id) {
		$id = (int)$id;
		$sql = "DROP TABLE IF EXISTS `yg_site_" . $id . "_cron`;";
		$result = sYDB()->Execute($sql);
		$sql = "DROP TABLE IF EXISTS `yg_site_" . $id . "_lnk_cb`;";
		$result = sYDB()->Execute($sql);
		$sql = "DROP TABLE IF EXISTS `yg_comments_lnk_pages_" . $id . "`;";
		$result = sYDB()->Execute($sql);
		$sql = "DROP TABLE IF EXISTS `yg_site_" . $id . "_permissions`;";
		$result = sYDB()->Execute($sql);
		$sql = "DROP TABLE IF EXISTS `yg_site_" . $id . "_properties`;";
		$result = sYDB()->Execute($sql);
		$sql = "DROP TABLE IF EXISTS `yg_site_" . $id . "_props`;";
		$result = sYDB()->Execute($sql);
		$sql = "DROP TABLE IF EXISTS `yg_site_" . $id . "_propslv`;";
		$result = sYDB()->Execute($sql);
		$sql = "DROP TABLE IF EXISTS `yg_site_" . $id . "_propsv`;";
		$result = sYDB()->Execute($sql);
		$sql = "DROP TABLE IF EXISTS `yg_site_" . $id . "_tree`;";
		$result = sYDB()->Execute($sql);
		$sql = "DROP TABLE IF EXISTS `yg_site_" . $id . "_tree_history`;";
		$result = sYDB()->Execute($sql);

		// Cleanup History Table
		$sql = "DELETE FROM `yg_history` WHERE SITEID=" . $id . ";";
		$result = sYDB()->Execute($sql);
		return true;
	}

/// @endcond

/// @cond DEV

	/**
	 * Creates all database tables for the specified Site
	 *
	 * @param int $id Site Id
	 * @param string $name Site name
	 * @param int $sourceSite (optional) Site Id of source Site (for copying properties)
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	private function createSiteTables($id, $name, $sourcesite = false) {
		$id = (int)$id;

		$sql = "CREATE TABLE `yg_site_" . $id . "_lnk_cb` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `CBID` int(11) NOT NULL DEFAULT '0',
		  `CBVERSION` int(11) NOT NULL DEFAULT '0',
		  `CBPID` int(11) NOT NULL DEFAULT '0',
		  `PID` int(11) NOT NULL DEFAULT '0',
		  `PVERSION` int(11) NOT NULL DEFAULT '0',
		  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
		  `TEMPLATECONTENTAREA` varchar(85) NOT NULL DEFAULT '',
		  PRIMARY KEY (`ID`),
		  KEY `CBID` (`CBID`,`CBVERSION`),
		  KEY `CBID_2` (`CBID`,`PID`,`PVERSION`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$result = sYDB()->Execute($sql);

		$sql = "CREATE TABLE `yg_comments_lnk_pages_" . $id . "` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `OID` int(11) NOT NULL DEFAULT '0',
		  `COMMENTID` int(11) NOT NULL DEFAULT '0',
		  `ORDERPROD` int(11) NOT NULL DEFAULT '9999',
		  PRIMARY KEY (`ID`),
		  UNIQUE KEY `OID` (`OID`,`COMMENTID`) USING BTREE
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$result = sYDB()->Execute($sql);

		$sql = "CREATE TABLE `yg_site_" . $id . "_permissions` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `OID` int(11) NOT NULL DEFAULT '0',
		  `USERGROUPID` int(11) NOT NULL DEFAULT '0',
		  `RREAD` smallint(6) NOT NULL DEFAULT '0',
		  `RWRITE` smallint(6) NOT NULL DEFAULT '0',
		  `RDELETE` smallint(6) NOT NULL DEFAULT '0',
		  `RSUB` smallint(6) NOT NULL DEFAULT '0',
		  `RSTAGE` smallint(6) NOT NULL DEFAULT '0',
		  `RMODERATE` smallint(6) NOT NULL DEFAULT '0',
		  `RCOMMENT` smallint(6) NOT NULL DEFAULT '0',
		  `RSEND` smallint(6) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`ID`),
		  KEY `OID` (`OID`,`USERGROUPID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$result = sYDB()->Execute($sql);

		$user = new User(sUserMgr()->getCurrentUserID());
		$anonGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ANONGROUPID");
		$rolesList = $user->getUsergroups();

		$tmpUser = new User(sUserMgr()->getCurrentUserID());
		for ($r = 0; $r < count($rolesList); $r++) {
			if ($tmpUser->checkPermission('RSITES')) {
				if ($rolesList[$r]["ID"] != $anonGroupId) {
					$sql = "INSERT INTO	`yg_site_" . $id . "_permissions`
								(`OID`, `USERGROUPID`, `RREAD`, `RWRITE`, `RDELETE`, `RSUB`, `RSTAGE`, `RMODERATE`, `RCOMMENT`, `RSEND`)
							VALUES
								(1, " . $rolesList[$r]["ID"] . ", 1, 1, 1, 1, 1, 1, 1, 1);";
					$result = sYDB()->Execute($sql);
				}
			}
		}

		$sql = "INSERT INTO	`yg_site_" . $id . "_permissions`
					(`OID`, `USERGROUPID`, `RREAD`, `RWRITE`, `RDELETE`, `RSUB`, `RSTAGE`, `RMODERATE`, `RCOMMENT`, `RSEND`)
				VALUES
					(1, " . $anonGroupId . ", 1, 0, 0, 0, 0, 0, 0, 0);";
		$result = sYDB()->Execute($sql);

		$sql = "CREATE TABLE `yg_site_" . $id . "_properties` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `OBJECTID` int(11) NOT NULL DEFAULT '0',
		  `VERSION` int(11) NOT NULL DEFAULT '0',
		  `APPROVED` smallint(6) NOT NULL DEFAULT '0',
		  `CREATEDBY` int(11) NOT NULL DEFAULT '0',
		  `CHANGEDBY` int(11) NOT NULL DEFAULT '0',
		  `HASCHANGED` int(11) NOT NULL DEFAULT '0',
		  `TEMPLATEID` int(11) NOT NULL DEFAULT '0',
		  `COMMENTSTATUS` int(11) NOT NULL DEFAULT '1',
		  `COMMENTSTATUS_AUTO` int(11) NOT NULL DEFAULT '1',
		  `NAVIGATION` int(11) NOT NULL DEFAULT '0',
		  `ACTIVE` int(11) NOT NULL DEFAULT '0',
		  `HIDDEN` int(11) NOT NULL DEFAULT '0',
		  `LOCKED` int(11) NOT NULL DEFAULT '0',
		  `LOCKUID` text NOT NULL,
		  `TOKEN` text NOT NULL,
		  `DELETED` int(11) NOT NULL DEFAULT '0',
		  `CREATEDTS` int(11) NOT NULL DEFAULT '0',
		  `CHANGEDTS` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`ID`),
		  KEY `OBJECTID` (`OBJECTID`,`VERSION`),
		  KEY `VERSION` (`VERSION`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
		";
		$result = sYDB()->Execute($sql);

		$sql = "INSERT INTO `yg_site_" . $id . "_properties` (`OBJECTID`, `VERSION`, `APPROVED`, `CREATEDBY`, `CHANGEDBY`, `HASCHANGED`, `TEMPLATEID`, `NAVIGATION`, `ACTIVE`, `HIDDEN`, `LOCKED`, `DELETED`, `CREATEDTS`, `CHANGEDTS`) VALUES
				(1, 0, 1, 1, 0, " . sUserMgr()->getCurrentUserID() . ", " . sUserMgr()->getCurrentUserID() . ", 0, 1, 0, 0, 0, 0, 0);";
		$result = sYDB()->Execute($sql);

		// hotfix for #2260 (in principle we want custom properties per site)
		$siteList = $this->getList();
		$sourceSiteId = $siteList[0]["ID"];

		if (count($siteList) > 0) {
			$sql = "CREATE TABLE `yg_site_" . $id . "_props` AS (SELECT * FROM `yg_site_" . $sourceSiteId . "_props`);";
			$result = sYDB()->Execute($sql);
			$sql = "CREATE TABLE `yg_site_" . $id . "_propslv` AS (SELECT * FROM `yg_site_" . $sourceSiteId . "_propslv`);";
			$result = sYDB()->Execute($sql);
			$sql = "CREATE TABLE `yg_site_" . $id . "_propsv` AS (SELECT * FROM `yg_site_" . $sourceSiteId . "_propsv` WHERE OID < 0);";
			$result = sYDB()->Execute($sql);
		} else {
			// first site
			$sql = "CREATE TABLE `yg_site_" . $id . "_props` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `NAME` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `IDENTIFIER` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `VISIBLE` int(11) NOT NULL DEFAULT '1',
			  `READONLY` int(11) NOT NULL DEFAULT '0',
			  `TYPE` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `LISTORDER` int(11) NOT NULL DEFAULT '9999',
			  PRIMARY KEY (`ID`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
			";
			$result = sYDB()->Execute($sql);

			$sql = "INSERT INTO `yg_site_" . $id . "_props` (`ID`, `NAME`, `IDENTIFIER`, `VISIBLE`, `READONLY`, `TYPE`, `LISTORDER`) VALUES
						(1, 'Name', 'NAME', 1, 1, 'TEXT', 1),
						(2, 'Title', 'TITLE', 1, 1, 'TEXT', 2),
						(3, 'Description', 'DESCRIPTION', 1, 1, 'TEXTAREA', 3);";
			$result = sYDB()->Execute($sql);

			$sql = "CREATE TABLE IF NOT EXISTS `yg_site_" . $id . "_propslv` (
						`ID` int(11) NOT NULL AUTO_INCREMENT,
						`PID` int(11) NOT NULL,
	                    `VALUE` varchar(50) NOT NULL,
	                    `LISTORDER` int(11) NOT NULL DEFAULT '9999',
	                    PRIMARY KEY (`ID`),
	                    KEY `LISTORDER` (`LISTORDER`,`PID`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$result = sYDB()->Execute($sql);

			$sql = "CREATE TABLE IF NOT EXISTS `yg_site_" . $id . "_propsv` (
						`OID` int(11) NOT NULL DEFAULT '0',
						`NAME` text,
						`TITLE` text,
						`DESCRIPTION` text,
						PRIMARY KEY (`OID`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$result = sYDB()->Execute($sql);
		}

		$sql = "INSERT INTO `yg_site_" . $id . "_propsv` (`OID`, `NAME`, `TITLE`, `DESCRIPTION`) VALUES
					(1, '" . $name . "', NULL, NULL);";
		$result = sYDB()->Execute($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `yg_site_" . $id . "_tree` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `LFT` int(11) NOT NULL DEFAULT '0',
		  `RGT` int(11) NOT NULL DEFAULT '0',
		  `VERSIONPUBLISHED` int(11) NOT NULL DEFAULT '0',
		  `MOVED` int(11) NOT NULL DEFAULT '0',
		  `TITLE` text,
		  `LEVEL` int(11) NOT NULL DEFAULT '0',
		  `PARENT` int(11) NOT NULL DEFAULT '0',
		  `PNAME` text,
		  PRIMARY KEY (`ID`),
		  KEY `LFT_2` (`LFT`,`RGT`),
		  KEY `LFT` (`LFT`,`RGT`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
		";
		$result = sYDB()->Execute($sql);

		$sql = "INSERT INTO `yg_site_" . $id . "_tree` (`ID`, `LFT`, `RGT`, `VERSIONPUBLISHED`, `MOVED`, `TITLE`, `LEVEL`, `PARENT`, `PNAME`) VALUES
		(1, 1, 2, 0, 0, '', 1, 0, '" . $name . "');";
		$result = sYDB()->Execute($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `yg_site_" . $id . "_tree_history` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `OID` int(11) NOT NULL DEFAULT '0',
		  `DATETIME` int(11) DEFAULT NULL,
		  `TEXT` text NOT NULL,
		  `UID` int(11) NOT NULL DEFAULT '0',
		  `TYPE` int(11) NOT NULL,
		  `TARGETID` int(11) NOT NULL,
		  `OLDVALUE` text NOT NULL,
		  `NEWVALUE` text NOT NULL,
		  PRIMARY KEY (`ID`),
		  KEY `OID` (`OID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8  ;";
		$result = sYDB()->Execute($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `yg_site_" . $id . "_cron` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `OBJECTTYPE` int(11) NOT NULL,
		  `OBJECTID` int(11) NOT NULL,
		  `ACTIONCODE` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
		  `TIMESTAMP` bigint(20) NOT NULL,
		  `EXPIRES` bigint(20) NOT NULL,
		  `PARAMETERS` text COLLATE utf8_unicode_ci NOT NULL,
		  `USERID` int(11) NOT NULL,
		  `STATUS` int(11) NOT NULL,
		  PRIMARY KEY (`ID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$result = sYDB()->Execute($sql);
		return true;
	}

/// @endcond

	/**
	 * Gets basic information about the specified Site
	 *
	 * @param int $site Site Id
	 * @return array|false Array containing information about the Site or FALSE in case of an error
	 * @throws Exception
	 */
	function get($site) {
		$site = (int)$site;
		$sql = "SELECT * FROM yg_site WHERE ID = $site;";
		$result = sYDB()->execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray[0];
	}

	/**
	 * Gets the name of the specified Site
	 *
	 * @param int $site Site Id
	 * @return string|false Site name or FALSE in case of an error
	 * @throws Exception
	 */
	function getName($site) {
		$site = (int)$site;
		$sql = "SELECT NAME FROM yg_site WHERE ID = $site;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray[0]["NAME"];
	}

	/**
	 * Gets the permanent name of the specified Site
	 *
	 * @param int $site Site Id
	 * @return string|false Site permanent name or FALSE in case of an error
	 * @throws Exception
	 */
	function getPName($site) {
		$site = (int)$site;
		$sql = "SELECT PNAME FROM yg_site WHERE ID = $site";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray[0]["PNAME"];
	}

	/**
	 * Gets basic information about the specified Site (by permanent name)
	 *
	 * @param string $name Site permanent name
	 * @return array|false Array containing information about the Site or FALSE in case of an error
	 * @throws Exception
	 */
	function getByPName($name) {
		if (strlen(trim($name)) == 0) {
			return false;
		}
		$name = mysql_real_escape_string(sanitize($name));
		$sql = "SELECT * FROM yg_site WHERE (PNAME = '$name');";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray[0];
	}

	/**
	 * Gets a list of all Sites
	 *
	 * @param bool $skipPermissions (optional) TRUE if no permissions should be checked
	 * @return array|bool Array of Site names with Ids
	 * @throws Exception
	 */
	function getList($skipPermissions = false) {
		$sql = "SELECT
					*
				FROM
					yg_site
				ORDER BY NAME;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$sites = $result->GetArray();
		if ($skipPermissions) {
			return $sites;
		}
		$resultarray = array();
		for ($s = 0; $s < count($sites); $s++) {
			$siteID = $sites[$s]["ID"];
			$pageMgr = new PageMgr($siteID);
			$sitePages = $pageMgr->tree->get(0, 1);

			$pageID = $sitePages[0]["ID"];
			$page = $pageMgr->getPage($pageID);

			if ($page && $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $pageID, "RREAD")) {
				$resultarray[] = $sites[$s];
			}
		}
		return $resultarray;
	}

	/**
	 * Sets the default Template of the specified Site
	 *
	 * @param int $siteId Site Id
	 * @param int $templateId Template Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function setDefaultTemplate($siteId, $templateId) {
		if (sUsergroups()->permissions->check($this->_uid, 'RSITES')) {
			$siteId = (int)$siteId;
			$templateId = (int)$templateId;
			$sql = "UPDATE yg_site SET DEFAULTTEMPLATE = $templateId WHERE ID = $siteId;";
			$result = $this->_db->execute($sql);
			if ($result === false) {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the Template root directory displayed when selecting Templates for Pages of the specified Site
	 *
	 * @param int $siteId Site Id
	 * @param int $templateId Template folder Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function setTemplateRoot($siteId, $templateId) {
		if (sUsergroups()->permissions->check($this->_uid, 'RSITES')) {
			$siteId = (int)$siteId;
			$templateId = (int)$templateId;
			$sql = "UPDATE yg_site SET TEMPLATEROOT = $templateId WHERE ID = $siteId;";
			$result = $this->_db->execute($sql);
			if ($result === false) {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the permanent name of the specified Site
	 *
	 * @param int $siteId Site Id
	 * @param string $PName Permanent name
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function setPName($siteId, $PName) {
		if (sUsergroups()->permissions->check($this->_uid, 'RSITES')) {
			$siteId = (int)$siteId;
			$PName = mysql_real_escape_string(sanitize($PName));

			if (is_numeric($PName)) {
				return false;
			}

			$sql = "UPDATE yg_site SET PNAME = '$PName' WHERE ID = $siteId;";
			$result = $this->_db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "UPDATE `yg_site_" . $siteId . "_tree` SET PNAME = '$PName' WHERE ID = 1;";
			$result = $this->_db->execute($sql);
			if ($result === false) {
				return false;
			}

			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/PNAME_CHANGE") == "true") {
				Singleton::FC()->emptyBucket();
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Cleans up the provided permanent name
	 *
	 * @param string $pname Permanent name
	 * @return string Clean permanent name
	 */
	function filterPName($pname) {
		$pname = sanitize($pname);
		$pname = str_replace(" ", "_", $pname);
		$pname = str_replace("&", "_", $pname);
		$pname = str_replace("/", "_", $pname);
		$pname = str_replace("\\", "_", $pname);
		$pname = str_replace("?", "_", $pname);
		$pname = str_replace("#", "_", $pname);
		$pname = str_replace(":", "_", $pname);
		$pname = str_replace("%", "_", $pname);
		$pname = str_replace("'", "", $pname);
		$pname = str_replace('"', "", $pname);
		return $pname;
	}

	/**
	 * Calculates a unique permanent name for the specified Site
	 *
	 * @param int $id Site Id
	 * @param string $iteration (optional) Iteration
	 * @param string $name (optional) Name to calculate the PName from
	 * @return string Permanent name
	 */
	function calcPName($id, $iteration = "", $name = '') {
		$siteID = (int)$id;
		$siteInfo = $this->get($siteID);
		$siteName = $siteInfo['NAME'];
		if ($name) {
			$siteName = $name;
		}
		if ((int)sConfig()->getVar("CONFIG/CASE_SENSITIVE_URLS") == 0) {
			$siteName = strtolower($siteName);
		}
		$PName = $this->filterPName($siteName);
		if ($iteration != '') {
			$checkPinfo = $this->getByPName($PName . '_' . $iteration);
		} else {
			$checkPinfo = $this->getByPName($PName);
		}
		if ($checkPinfo['ID'] == $siteID) {
			if ($iteration != '') {
				return $PName . '_' . $iteration;
			} else {
				return $PName;
			}
		} else {
			if ($checkPinfo['ID'] == NULL) {
				if ($iteration != '') {
					return $PName . '_' . $iteration;
				} else {
					return $PName;
				}
			} else {
				if ($iteration == '') {
					$iteration = 1;
				}
				return $this->calcPName($siteID, ++$iteration);
			}
		}
	}

	/**
	 * Sets the name of the specified Site
	 *
	 * @param int $siteId Site Id
	 * @param string $name Site name
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function setName($siteId, $name) {
		if (sUsergroups()->permissions->check($this->_uid, 'RSITES')) {
			$siteId = (int)$siteId;
			$name = mysql_real_escape_string(sanitize($name));

			$sql = "UPDATE yg_site SET NAME = '$name' WHERE ID = $siteId;";
			$result = $this->_db->execute($sql);
			if ($result === false) {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the favicon of the specified Site
	 *
	 * @param int $siteId Site Id
	 * @param int $fileId File Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	public function setFavicon($siteId, $fileId) {
		if (sUsergroups()->permissions->check($this->_uid, 'RSITES')) {
			$siteId = (int)$siteId;
			$fileId = (int)$fileId;
			$sql = "UPDATE yg_site SET FAVICON = $fileId WHERE ID = $siteId;";
			$result = $this->_db->execute($sql);
			if ($result === false) {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}

}

?>