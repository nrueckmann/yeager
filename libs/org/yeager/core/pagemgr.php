<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Gets an instance of the PageMgr
 *
 * @param int $siteId Site Id
 * @return object PageMgr object
 */
function sPageMgr($siteId) {
	$signature = "PageMGR-" . $siteId;
	if (!Singleton::$instances[$signature]) {
		$return = new PageMgr($siteId);
		Singleton::register($signature, $return);
	}
	$return = Singleton::$instances[$signature];
	return $return;
}

/**
 * The PageMgr class, which represents an instance of the Page manager.
 */
class PageMgr extends \framework\Error {
	var $_db;
	var $_site;
	var $_uid;

	var $db;
	var $baum;
	var $table;

	var $properties;
	var $history;
	var $permissions;
	var $scheduler;

	/**
	 * Constructor of the PageMgr class
	 *
	 * @param int $siteId Site Id
	 */
	public function __construct($siteId) {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->_config = $config;
		$this->_site = (int)$siteId;
		$this->table = "yg_site_" . $this->_site . "_tree";
		$this->tree = new tree($this);
		$this->permissions = new Permissions("yg_site_" . $this->_site . "_permissions", $this);
		$this->history = new History($this, "site_" . $this->_site, $this->permissions, $this->_site);
		$this->tags = new Tags($this);
		$this->properties = new PropertySettings("yg_site_" . $this->_site . "_props");
		$this->scheduler = new Scheduler("yg_site_" . $this->_site . "_cron", SCHEDULER_PAGE);
		$this->templates = new Templates();
	}

	/**
	 * Gets a specific Page instance
	 *
	 * @param int $pageId Page Id
	 * @param int $version (optional) Page version
	 * @return Page|false New instance of Page object or FALSE if an error has occured
	 */
	public function getPage($pageId, $version = 0) {
		if (sSites()->siteExists($this->_site) == false) return false;
		if ($this->permissions->checkInternal($this->_uid, $pageId, "RREAD")) {
			try {
				return new Page($this->_site, $pageId, $version);
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets an instance of a published Page
	 *
	 * @param int $pageId Page Id
	 * @return Page|false New instance of Page object or FALSE if an error has occured
	 */
	public function getPublishedPage($pageId) {
		$tmpPage = $this->getPage($pageId);
		if ($tmpPage) {
			$tmpPageVersion = $tmpPage->getPublishedVersion(true);
			return $this->getPage($pageId, $tmpPageVersion);
		} else {
			return false;
		}
	}

/// @cond DEV

	/**
	 * Gets the object prefix, used for table names in database queries
	 *
	 * @return string
	 */
	function getObjectPrefix() {
		return 'pages';
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets value of the additional identifier
	 *
	 * @return int Site Id
	 */
	function getAdditionalIdentifierValue() {
		return $this->_site;
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets additional identifier
	 *
	 * @return array Additional identifier ('SITEID')
	 */
	function getAdditionalIdentifier() {
		return array('SITEID');
	}

/// @endcond

/// @cond DEV

	/**
	 * Gets the name of the database table which contains the properties of Pages
	 *
	 * @return string Tablename
	 */
	function getPropertyTable() {
		return 'yg_site_' . $this->_site . '_properties';
	}

/// @endcond

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

/// @cond DEV

	/**
	 * Gets the name of the database table containing the Pages tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return 'yg_site_' . $this->_site . '_tree';
	}

/// @endcond

	/**
	 * Callback method which is executed when a Usergroup permission on a Page changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId Page Id
	 */
	public function onPermissionChange($usergroupId, $permission, $value, $objectId) {
		// Also set these permissions to the all embedded Cblocks in this Page
		$page = $this->getPage($objectId);
		if ($page) {
			$pageVersions = $page->getVersions();

			foreach ($pageVersions as $pageVersions_item) {
				$tmpPage = $this->getPage($objectId, $pageVersions_item['VERSION']);
				$blindCos = array();
				$colist = $tmpPage->getCblockList('', false, true);
				foreach ($colist as $colistItem) {
					if ($colistItem['ID'] > 0) {
						array_push($blindCos, $colistItem['ID']);
					}
				}
			}

			$blindCos = array_unique($blindCos);

			$pagePermissions = $page->permissions->getByUsergroup($usergroupId, $objectId);
			if ($pagePermissions) {
				foreach ($blindCos as $coid) {
					$bcb = sCblockMgr()->getCblock($coid);
					$bcb->permissions->setByUsergroup($usergroupId, $permission, $coid, $value);
				}
			}
		}
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on a Page change
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permissions (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 */
	public function onPermissionsChange($usergroupId, $permissions, $objectId) {
		// Also set these permissions to the all blinds Cblocks in this Page
		$page = $this->getPage($objectId);
		if ($page) {

			$pageVersions = $page->getVersions();

			$blindCos = array();
			foreach ($pageVersions as $pageVersions_item) {
				$tmpPage = $this->getPage($objectId, $pageVersions_item['VERSION']);
				$colist = $tmpPage->getCblockList('', false, true);
				foreach ($colist as $colistItem) {
					$coid = $colistItem['OBJECTID'];
					if ($coid > 0) {
						array_push($blindCos, $coid);
					}
				}
			}

			$blindCos = array_unique($blindCos);
			$pagePermissions = $this->permissions->getByUsergroup($usergroupId, $objectId);
			if ($pagePermissions) {
				foreach ($blindCos as $coid) {
					$bcb = sCblockMgr()->getCblock($coid);
					$bcb->permissions->setPermissions(array($permissions), $coid);
				}
			}
		}
		return true;
	}

/// @cond DEV

	/**
	 * Calls a specific Extension hook Callback method
	 *
	 * @param string $method
	 * @param int $siteId Site Id
	 * @param int $pageId Page Id
	 * @param int $version Page version
	 * @param mixed $args Arbitrary arguments
	 */
	function callExtensionHook($method, $siteId, $pageId, $version, $args) {
		$extensions = new ExtensionMgr($this->_db, $this->_uid);
		$all_page_extensions = $extensions->getList(EXTENSION_PAGE, true);
		$extarr = array();
		foreach ($all_page_extensions as $all_page_extension) {
			$extension = $extensions->getExtension($all_page_extension["CODE"]);
			if ($extension && $extension->usedByPage($pageId, $version, $siteId) === true) {
				$extension = $extensions->getExtension($all_page_extension["CODE"], $pageId, $version, $siteId);
				if ($extension) {
					array_push($extarr, $extension);
				}
			}
		}
		foreach ($extarr as $extension) {
			$extension->callExtensionHook($method, $args);
		}
	}

/// @endcond

	/**
	 * Adds a new Page to the specified parent Page
	 *
	 * @param int $parentPageId Parent Page Id
	 * @param int $templateId (optional) Template Id
	 * @param string $name (optional) Page name
	 * @return int|false New Page Id or FALSE in case of an error
	 * @throws Exception
	 */
	function add($parentPageId, $templateId = 0, $name = 'New Page') {
		$parentPageId = (int)$parentPageId;
		$name = mysql_real_escape_string($name);
		$templateId = (int)$templateId;
		if ($this->permissions->checkInternal($this->_uid, $parentPageId, "RSUB")) {

			$parentPage = $this->getPage($parentPageId);
			$pinfo = $parentPage->get();

			// Create node in Pages tree
			$pageId = $this->tree->add($parentPageId);

			// Create new version
			$ts = time();
			$sql = "INSERT INTO
						`yg_site_" . $this->_site . "_properties`
					(`OBJECTID`, `VERSION`, `TEMPLATEID`, `NAVIGATION`, `ACTIVE`, `HIDDEN`, `LOCKED`, `CREATEDTS`, `CHANGEDTS`, `CREATEDBY`, `CHANGEDBY`)
						VALUES
					('$pageId', '1', '$templateId', '0', '1', '0', '0', '$ts', '$ts', '" . $this->_uid . "', '" . $this->_uid . "');";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			// Copy permissions from parent node
			$this->permissions->copyTo($parentPageId, $pageId);

			$newPage = $this->getPage($pageId);
			$newPage->properties->setValue("NAME", $name);
			$newPage->publishVersion(ALWAYS_LATEST_APPROVED_VERSION);

			// Get default template from site (if set) and assign to newly created page
			$siteMgr = new Sites();
			$siteInfo = $siteMgr->get($this->_site);
			if ($siteInfo['DEFAULTTEMPLATE'] > 0) {
				$newPage->setTemplate($siteInfo['DEFAULTTEMPLATE']);
			}

			$newPage = $this->getPage($pageId);
			$newPageInfo = $newPage->get();
			$this->callExtensionHook("onAdd", $this->_site, $pageId, $newPageInfo['VERSION']);

			// Add to history
			$newPage->history->add(HISTORYTYPE_PAGE, NULL, 1, 'TXT_PAGE_H_NEWVERSION');

			return $pageId;
		} else {
			return false;
		}
	}

	/**
	 * Removes a Page from the Trash
	 *
	 * @param int $pageId Page Id
	 *
	 * @return array Array with all elements which were successfully removed
	 */
	function remove($pageId) {
		$pageId = $origPageId = (int)$pageId;
		$rootNode = $this->tree->getRoot();
		if ($pageId == $rootNode) {
			return array();
		}

		// Get all nodes
		$hadError = false;
		$allNodes = $this->tree->get($pageId, 1000);
		foreach($allNodes as $allNodesItem) {
			$pageId = $allNodesItem['ID'];

			// Check if object is really in trash
			$page = new Page($this->_site, $pageId);
			$pageInfo = $page->get();

			if ($page->permissions->checkInternal($this->_uid, $pageId, "RDELETE") && $pageInfo['DELETED']) {
				// Collect and remove all linked blind contentblocks
				$sql = "SELECT * FROM `yg_site_" . $this->_site . "_lnk_cb` WHERE PID = $pageId";
				$linked_cos = $this->cacheExecuteGetArray($sql);

				foreach ($linked_cos as $linked_co) {
					$cb = sCblockMgr()->getCblock($linked_co['CBID']);
					if ($cb) {
						$coInfo = $cb->get();
						// Embedded contentblock?
						if ($coInfo['EMBEDDED'] == 1) {
							$cb->delete();
							sCblockMgr()->remove($linked_co['CBID']);
						}
					}
				}

				// Remove page
				$sql = "DELETE FROM `yg_site_" . $this->_site . "_properties` WHERE OBJECTID = $pageId";
				$result = sYDB()->Execute($sql);

				// Remove content object links
				$sql = "DELETE FROM `yg_site_" . $this->_site . "_lnk_cb` WHERE PID = $pageId";
				$result = sYDB()->Execute($sql);

				$page->tags->clear();
				$page->history->clear();

				$this->callExtensionHook("onRemove", $this->_site, $pageId, 0, $pageInfo);
			} else {
				$hadError = true;
			}
		}
		if ($hadError) {
			return array();
		} else {
			$this->tree->remove($origPageId);
			return array($origPageId);
		}
	}

	/**
	 * Get Pages tree nodes
	 *
	 * @param int $pageId (optional) From which Page Id the tree should be returned
	 * @param int $maxLevels (optional) Specifies the maximum level of nodes to get
	 * @param bool $resolveCblocks (optional) TRUE when the contained Cblocks should also be returned
	 * @param bool $noTrash (optional) FALSE when item from the Trash should also be returned
	 * @return array Array of Page nodes
	 */
	function getTree($pageId = NULL, $maxLevels = 2, $resolveCblocks = false, $noTrash = true) {
		$maxLevels = (int)$maxLevels;
		$resolveCblocks = (bool)$resolveCblocks;

		if ($pageId > 0) {
			$currentLevel = $this->tree->getLevel($pageId);
		} else {
			$currentLevel = 1;
			$pageId = $this->tree->getRoot();
		}
		if ($currentLevel < 1) {
			return;
		}

		if ($noTrash) {
			$filterSQL_WHERE = " AND prop.DELETED = 0";
		}

		$maxLevelSQL = " AND (group2.LEVEL <= " . ($maxLevels + $currentLevel) . ") AND (group2.LEVEL <= " . ($maxLevels + $currentLevel) . ")";

		$myinfo = $this->tree->getAll($pageId);
		if (!$myinfo) {
			return array();
		}
		$subnodeSQL = " AND (group2.LFT >= " . $myinfo["LFT"] . ") AND (group2.RGT <= " . $myinfo["RGT"] . ")";

		// SQL for permissions
		$perm_SQL_SELECT = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RMODERATE) AS RMODERATE, MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_SQL_FROM = " LEFT JOIN yg_site_" . $this->_site . "_permissions AS perm ON perm.OID = group2.ID";

		$perm_SQL_WHERE = " AND (";
		$roles = $this->permissions->getUsergroups();
		for ($r = 0; $r < count($roles); $r++) {
			$perm_SQL_WHERE .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
			if ((count($roles) - $r) > 1) {
				$perm_SQL_WHERE .= " OR ";
			}
		}
		$perm_SQL_WHERE .= ") ";

		$sql = "SELECT
					group2.ID, group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.TITLE, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT, group2.PNAME AS PNAME,
					prop.VERSION AS VERSION, prop.HASCHANGED AS HASCHANGED, pv.NAME as NAME, prop.ACTIVE, prop.NAVIGATION AS NAVIGATIONID, prop.TEMPLATEID
					$perm_SQL_SELECT
				FROM
					(yg_site_" . $this->_site . "_properties AS prop)
				LEFT JOIN
					$this->table AS group2 ON ((group2.ID = prop.OBJECTID) $maxLevelSQL $subnodeSQL)
				$perm_SQL_FROM
				LEFT JOIN
					yg_site_" . $this->_site . "_propsv AS pv ON pv.OID = prop.ID
				WHERE
					(prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_site_" . $this->_site . "_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID)))
					$perm_SQL_WHERE $maxLevelSQL $subnodeSQL $filterSQL_WHERE
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID;";
		$tree = $this->cacheExecuteGetArray($sql);

		if ($resolveCblocks == true) {
			for ($n = 0; $n < count($tree); $n++) {
				$page = $this->getPage($tree[$n]["ID"]);
				$contentareas = $this->templates->getContentareas($tree[$n]["TEMPLATEID"]);
				if ($page == true) {
					for ($i2 = 0; $i2 < count($contentareas); $i2++) {
						$colist = $page->getCblockList($contentareas[$i2]["CODE"]);
						$contentareas[$i2]["LIST"] = $colist;
					}
				}
				$tree[$n]["CONTENTAREAS"] = $contentareas;
			}
		}

		return $tree;
	}

	/**
	 * Gets a list of Pages
	 *
	 * @param int $pageId (optional) Id of the parent Page from which the list will be created
	 * @param array $filter (optional, may be combined) If SUBNODES, only subnodes of the specified Page will be returned<br>
	 *                                 if TRASHCAN, only items in the Trash will be returned<br>
	 *                                 if PUBLISHED, only live/published versions will be returned
	 * @param int $maxLevel (optional) Specifies the maximum level of nodes to get
	 * @param int $permissionsForRoleId (optional) If '1' then return all Usergroups and Permissions for this node
	 * @param array $filterArray Array of filters for the SQL query
	 * @return array|false Array of Pages or FALSE in case of an error
	 */
	function getList($pageId = 0, $filter = array(), $maxLevel = 0, $permissionsForRoleId = 0, $filterArray = array()) {
		$pageId = (int)$pageId;
		$resolveCblocks = (bool)$resolveCblocks;
		$rootGroupId = (int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTGROUPID");

		if (($this->_uid > 0) || ($permissionsForRoleId > 0)) {
			$privileges_from = ", yg_site_" . $this->_site . "_permissions as priv";
		}
		if ($pageId == 0) {
			$selectdefault = true;
			$pageId = $this->tree->getRoot();
		}
		if ($maxLevel > 0) {
			$maxlevelsql = " AND (group2.LEVEL <= $maxLevel) AND (group1.LEVEL <= $maxLevel)";
		}
		if (in_array("SUBNODES", $filter)) {
			$myinfo = $this->tree->getAll($pageId);
			$myleft = $myinfo["LFT"];
			$myrgt = $myinfo["RGT"];
			$subnodesql = " AND (group1.LFT > $myleft AND group1.RGT < $myrgt)";
			if (!$myinfo) {
				return false;
			}
		}
		// Surpress items in trashcan if not explicitly asked for
		if (in_array("TRASHCAN", $filter)) {
			$filtersql_where = " AND prop.DELETED = 1";
		} else {
			$filtersql_where = " AND prop.DELETED = 0";
		}

		$filterOrder = 'ORDER BY group2.LFT';
		// Check if special filter was suppplied
		if ($filterArray) {
			$filterSelect = $filterFrom = $filterWhere = $filterLimit = $filterOrder = '';
			buildBackendFilter('PagesSearchCB', $filterArray, $filterSelect, $filterFrom, $filterWhere, $filterLimit, $filterOrder);
			$filtersql_where .= $filterWhere;
		}

		if (in_array("PUBLISHED", $filter)) {
			$filtersql_where .= " AND (
										(group2.VERSIONPUBLISHED = prop.VERSION) OR
										(
											(group2.VERSIONPUBLISHED = " . ALWAYS_LATEST_APPROVED_VERSION . ") AND
											(prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_site_" . $this->_site . "_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
										)
									) ";
		} else {
			$filtersql_where .= " AND (prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_site_" . $this->_site . "_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID))) ";
			$lastfinal = ", (SELECT MAX(VERSION) FROM yg_site_" . $this->_site . "_properties AS p2 WHERE p2.APPROVED = 1 AND p2.OBJECTID = prop.OBJECTID ) AS LASTAPPROVED ";
		}

		$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE, MAX(perm.RSUB) AS RSUB, MAX(perm.RSTAGE) AS RSTAGE, MAX(perm.RMODERATE) AS RMODERATE, MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_sql_from = " LEFT JOIN yg_site_" . $this->_site . "_permissions AS perm ON perm.OID = group2.ID";

		if ($permissionsForRoleId > 0) {
			$perm_sql_from .= " AND (perm.USERGROUPID = " . $permissionsForRoleId . ")";
		} else {
			$perm_sql_where = " AND (";
			$roles = $this->permissions->getUsergroups();
			for ($r = 0; $r < count($roles); $r++) {
				$perm_sql_where .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
				if ((count($roles) - $r) > 1) {
					$perm_sql_where .= " OR ";
				}
			}
			$perm_sql_where .= ") ";
			$perm_sql_where .= " AND ((RREAD >= 1) OR (perm.USERGROUPID = $rootGroupId)) ";
		}

		$sql = "SELECT
					group2.LFT,
					group2.RGT,
					group2.VERSIONPUBLISHED AS VERSIONPUBLISHED,
					group2.ID,
					group2.LEVEL AS LEVEL,
					group2.PARENT AS PARENT,
					group2.PNAME AS PNAME,
					MAX(prop.VERSION) AS VERSION,
					prop.ACTIVE AS ACTIVE,
					prop.HIDDEN AS HIDDEN,
					prop.LOCKED AS LOCKED,
					prop.NAVIGATION AS NAVIGATIONID,
					prop.HASCHANGED AS HASCHANGED,
					prop.TEMPLATEID AS TEMPLATEID,
					prop.CREATEDTS,
					prop.CHANGEDTS,
					prop.CREATEDBY,
					prop.CHANGEDBY,
					navi.NAME AS NAVIGATIONNAME,
					navi.CODE AS NAVIGATIONCODE,
					pv.*
					$perm_sql_select
					$lastfinal
				FROM
					($this->table AS group1, $this->table AS group2, yg_site_" . $this->_site . "_properties AS prop)
					$perm_sql_from
				LEFT JOIN yg_templates_navis AS navi
					ON navi.ID = prop.NAVIGATION
				LEFT JOIN yg_site_" . $this->_site . "_propsv AS pv
					ON pv.OID = prop.ID
				WHERE
					((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
					(group2.ID = prop.OBJECTID) $subnodesql $filtersql_where
					$perm_sql_where
					$maxlevelsql
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID
				$filterOrder $filterLimit;";

		$blaetter = $this->cacheExecuteGetArray($sql);

		return ($blaetter);
	}

	/**
	 * Gets additional information for Tree nodes
	 *
	 * @param int $pageId (optional) Id of the parent Page from which the list will be created
	 * @param array $objects Array of Tree nodes
	 * @param bool $skipInactive (optional) TRUE if inactive nodes and subnodes should get skipped
	 * @return array Array of Tree nodes
	 */
	function getAdditionalTreeInfo($pageId, $objects, $skipInactive = false) {
		if ($pageId < 1) {
			$selectdefault = true;
		}
		$shadowTree = array();
		$parentMap = array();
		$inactiveNodes = array();
		$objectsDupe = array();
		for ($i = 0; $i < count($objects); $i++) {
			$currentId = $objects[$i]["ID"];

			$parentMap[$currentId] = $objects[$i]['PARENT'];

			if (($selectdefault == true) && ($objects[$i]["LEVEL"] > 1)) {
				$pageId = $currentId;
				$selectdefault = false;
			}

			if ($skipInactive) {
				if (!$objects[$i]["ACTIVE"]) {
					array_push($inactiveNodes, $objects[$i]["ID"]);
					continue;
				}
				if (in_array($objects[$i]["PARENT"], $inactiveNodes)) {
					array_push($inactiveNodes, $objects[$i]["ID"]);
					continue;
				}
				if ($objects[$i]["LEVEL"] == 1) continue;
			}

			$shadowTree[$currentId]["LEVEL"] = $objects[$i]["LEVEL"];
			$shadowTree[$currentId]['PARENT'] = $parentMap[$currentId];
			$shadowTree[$currentId]["HIDDEN"] = $objects[$i]["HIDDEN"];
			$shadowTree[$currentId]["ACTIVE"] = $objects[$i]["ACTIVE"];

			$shadowTree[$parentMap[$currentId]]['CHILDREN'] += 1;
			$shadowTree[$parentMap[$currentId]]['LASTNODE'] = $currentId;

			if ($objects[$i]["NAVIGATIONID"] != 0) {
				$shadowTree[$parentMap[$currentId]]['SHOWSUB'] = 1;
			} else {
				if ($objects[$i]["ACTIVE"] == 1) {
					$shadowTree[$parentMap[$currentId]]['SHOWSUB'] = 1;
				}
			}
			if ($currentId == $pageId) {
				$objects[$i]["SELECTED"] = 1;
				$objects[$i]["SHOW"] = 1;
				$objects[$i]["SUBOPEN"] = 1;
				if ($objects[$i]["NAVIGATIONID"] == 0) {
					$shadowTree[$parentMap[$currentId]]['SELECTED'] = 1;
				} else {
					$shadowTree[$parentMap[$currentId]]['SHOWSUB'] = 1;
				}
				$shadowTree[$currentId]["SHOW"] = 1;
				$shadowTree[$currentId]["SUBOPEN"] = 1;
				$shadowTree[$currentId]["SELECTED"] = 1;
				$shadowTree[$parentMap[$currentId]]['SHOW'] = 1;
				$shadowTree[$parentMap[$currentId]]['SUBOPEN'] = 1;
			}
			if (($shadowTree[$parentMap[$currentId]]['SUBOPEN'] == 1)) {
				$objects[$i]["SHOW"] = 1;
				$shadowTree[$parentMap[$currentId]]['SHOW'] = 1;
			}
			if ($objects[$i]["SHOW"] == 1) {
				$currParent = $parentMap[$currentId];
				while ($currParent) {
					$shadowTree[$currParent]['SHOW'] = 1;
					$shadowTree[$currParent]['SUBOPEN'] = 1;
					$currParent = $parentMap[$currParent];
					if (($shadowTree[$currParent]['SHOW'] == 1) && ($shadowTree[$currParent]['SUBOPEN'] == 1)) {
						$currParent = false;
					}
				}
			}
			if (($objects[$i]["ACTIVE"] == 1) && ($objects[$i]["NAVIGATIONID"] != 0)) {
				$shadowTree[$parentMap[$currentId]]['HASVISIBLESUBNODES'] = 1;
			}

			array_push($objectsDupe, $objects[$i]);

			/*
			if ($resolveCblocks == true) {
				$contentareas = $this->templates->getContentareas($objects[$i]["TEMPLATEID"]);
				for ($i2 = 0; $i2 < count($contentareas); $i2++) {
					$tmpcb = $this->getCblock($objects[$i]["ID"]);
					$colist = $tmpcb->getCblockList($contentareas[$i2]["CODE"]);
					$contentareas[$i2]["LIST"] = $colist;
				}
				$objects[$i]["CONTENTAREAS"] = $contentareas;
			}
			*/
		}

		$objects = $objectsDupe;
		unset($objectsDupe);

		for ($i = 0; $i < count($objects); $i++) {
			$currentId = $objects[$i]["ID"];

			$preid = $objects[$i - 1]["ID"];
			$postid = $objects[$i + 1]["ID"];
			$objects[$i]["SUBOPEN"] = $shadowTree[$currentId]["SUBOPEN"];
			$objects[$i]["SHOWSUB"] = $shadowTree[$currentId]["SHOWSUB"];
			$objects[$i]["CHILDREN"] = $shadowTree[$currentId]["CHILDREN"];

			$objects[$i]["SHOW"] = $shadowTree[$currentId]["SHOW"];
			if ($shadowTree[$preid]["LEVEL"] < $objects[$i]["LEVEL"]) {
				$objects[$i]["FIRST"] = 1;
			}
			/*
			if ($objects[$i]["FIRST"] == 1) {
				if (($shadowTree[$currentId]["NAVIGATIONID"] == 0) && ($shadowTree[$currentId]["PARENT"] != 0)) {
					$objects[$i]["FIRST"] = 0;
					if ($shadowTree[$postid]["LEVEL"] == $objects[$i]["LEVEL"]) {
						$objects[$i+1]["FIRST"] = 1;
					}
				}
			}
			*/
			if (($shadowTree[$postid]["LEVEL"] < $objects[$i]["LEVEL"])) {
				$objects[$i]["LAST"] = 1;
			}
			if ($shadowTree[$postid]["LEVEL"] == "") {
				$objects[$i]["LAST"] = 1;
			}
			if ($shadowTree[$parentMap[$currentId]]['LASTNODE'] == $currentId) {
				$objects[$i]["LAST"] = 1;
			}
			if (($objects[$i]["SHOW"] == 1)) {
				$shadowTree[$parentMap[$currentId]]['SHOW'] = 1;
				$shadowTree[$parentMap[$currentId]]['SUBOPEN'] = 1;

				$currParent = $parentMap[$currentId];
				while ($currParent) {
					$shadowTree[$currParent]['SHOW'] = 1;
					$shadowTree[$currParent]['SUBOPEN'] = 1;
					$currParent = $parentMap[$currParent];
				}
				if (($shadowTree[$currParent]['SHOW'] == 1) && ($shadowTree[$currParent]['SUBOPEN'] == 1)) {
					$currParent = false;
				}
			}
			if ($shadowTree[$currentId]["HASVISIBLESUBNODES"] == 1) {
				$objects[$i]["HASVISIBLESUBNODES"] = 1;
			}
			if (($shadowTree[$parentMap[$currentId]]['SUBOPEN'] == 1)) {
				$objects[$i]["SHOW"] = 1;
				foreach ($allMyParents as $allMyParentsItem) {
					$objects[$allMyParentsItem]['SHOW'] = 1;
					$objects[$allMyParentsItem]['SUBOPEN'] = 1;
				}
			}
			if ($shadowTree[$postid]["LEVEL"] > $objects[$i]["LEVEL"]) {
				$objects[$i]["HASSUBNODES"] = 1;
			} else {
				$objects[$i]["HASSUBNODES"] = 0;
			}
		}

		/*$cleanList = array();
		foreach ($objects as $blaetterItem) {
			$x = $blaetterItem;
			unset($x['SHOW']);
			unset($x['SHOWSUB']);
			$cleanList[] = $x;
		}*/

		return $objects;
	}

	/**
	 * Gets the subnodes of the specified Page
	 *
	 * @param int $pageId Page Id of the parent Page
	 * @param bool $noTrash (optional) FALSE when item from the Trash should also be returned
	 * @return array|false Array of subnodes or FALSE in case of an error
	 */
	function getSubnodes($pageId, $noTrash = true) {

		$pageId = (int)$pageId;
		if ($pageId == 0) {
			return;
		}

		if ($noTrash) {
			$no_trash_sql = "AND (prop.DELETED = 0)";
		}

		$filtersql_where = " AND (prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_site_" . $this->_site . "_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID)))";
		$sql = "SELECT
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT, group2.PNAME AS PNAME,
					MAX(prop.VERSION) AS VERSION, prop.ACTIVE AS ACTIVE, prop.HIDDEN AS HIDDEN, prop.LOCKED AS LOCKED, pv.NAME AS NAME
				FROM
					$this->table AS group2, yg_site_" . $this->_site . "_properties AS prop, yg_site_" . $this->_site . "_propsv as pv
				WHERE
					(group2.ID = prop.OBJECTID) AND (group2.PARENT = $pageId) $filtersql_where AND
					(pv.OID = prop.ID)
					$no_trash_sql
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID order by group2.LFT;";
		$subnodes = $this->cacheExecuteGetArray($sql);
		return $subnodes;
	}

	/**
	 * Gets the parents of the specified Page
	 *
	 * @param int $pageId Page Id
	 * @return array Array of parent Pages
	 */
	function getParents($pageId) {
		if ($this->permissions->checkInternal($this->_uid, $pageId, "RREAD")) {
			$parentnodes = $this->tree->getParents($pageId, $this->tree->getRoot());
			$parentnodeidsql = implode(',', $parentnodes);

			if (strlen($parentnodeidsql) == 0) {
				$parentnodeidsql = 0;
			}
			$sql = "SELECT
						group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID AS ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT, group2.PNAME AS PNAME,
						MAX(prop.VERSION) AS VERSION, prop.ACTIVE AS ACTIVE, prop.HIDDEN AS HIDDEN, prop.LOCKED AS LOCKED, prop.TEMPLATEID AS TEMPLATEID, pv.NAME AS NAME
					FROM
						$this->table AS group2, yg_site_" . $this->_site . "_properties AS prop, yg_site_" . $this->_site . "_propsv as pv
					WHERE
						(group2.ID = prop.OBJECTID) AND (group2.ID IN ($parentnodeidsql)) AND (prop.VERSION = (SELECT MAX(VERSION) AS VERSION FROM yg_site_" . $this->_site . "_properties AS rgt WHERE rgt.OBJECTID = prop.OBJECTID)) AND
						(pv.OID = prop.ID)
					GROUP BY
						group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID ORDER BY group2.LEVEL DESC;";

			$parents0 = $this->cacheExecuteGetArray($sql);

			// Prepare weird array dimension
			$parents = array();
			for ($i = 0; $i < count($parents0); $i++) {
				$parents[$i][] = $parents0[$i];
			}
			$siteMgr = new Sites();
			$siteName = $siteMgr->getName($this->_site);
			if (count($parents) > 0) {
				$parents[count($parents) - 1][0]['NAME'] = $siteName;
			} else {
				$parents[0][0]['NAME'] = $siteName;
			}
			return $parents;
		} else {
			return false;
		}
	}

	/**
	 * Gets Cblock Page Link
	 *
	 * @param int $linkId Cblock Page Link Id
	 * @return array Information about Cblock Page Link
	 */
	function getCblockLinkById($linkId) {
		$linkId = (int)$linkId;
		$sql = "SELECT
					ID,
					CBID AS CBLOCKID,
					CBVERSION AS CBLOCKVERSION,
					CBPID,
					PID AS PAGEID,
					PVERSION AS PAGEVERSION,
					ORDERPROD,
					TEMPLATECONTENTAREA
				FROM
					yg_site_" . $this->_site . "_lnk_cb
				WHERE
					ID = $linkId;";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra;
	}

	/**
	 * Gets Page Id by permanent name
	 *
	 * @param string $PName permanent name
	 * @return int Page Id
	 */
	function getPageIdByPname($PName) {
		$PName = mysql_real_escape_string(sanitize($PName));
		$sql = "SELECT ID FROM yg_site_" . $this->_site . "_tree as t WHERE (t.PNAME = '$PName');";
		$ra = $this->cacheExecuteGetArray($sql);
		return $ra[0]['ID'];
	}

	/**
	 * Gets permanent name by Page Id
	 *
	 * @param int $pageId Page Id
	 * @return string Permanent name
	 */
	function getPNameByPageId($pageId) {
		$pageId = mysql_real_escape_string(sanitize((int)$pageId));
		if ($this->permissions->checkInternal($this->_uid, $pageId, "RREAD")) {
			$sql = "SELECT PNAME FROM yg_site_" . $this->_site . "_tree as t WHERE (t.ID = $pageId);";
			$ra = $this->cacheExecuteGetArray($sql);
			return $ra[0]['PNAME'];
		}
		return false;
	}

	/**
	 * Gets Pages by Template Id
	 *
	 * @param int $templateId Template Id
	 * @return array Page nodes
	 */
	function getPagesByTemplate($templateId) {
		$templateId = (int)$templateId;
		$filter = mysql_real_escape_string(sanitize($filter));
		if ($this->_uid > 0) {
			$privileges_from = ", yg_site_" . $this->_site . "_permissions as priv";
		}
		/*
		$filtersql_where .= " AND (
									(group2.VERSIONPUBLISHED = prop.VERSION) OR
									(
										(group2.VERSIONPUBLISHED = ".ALWAYS_LATEST_APPROVED_VERSION.") AND
										(prop.VERSION = (SELECT MAX( rgt.VERSION ) FROM yg_site_".$this->_site."_properties AS rgt WHERE (prop.OBJECTID = rgt.OBJECTID) AND (rgt.APPROVED = 1)))
									)
			   ) ";
		*/
		$filtersql_where = '';
		$perm_sql_select = ", MAX(perm.RREAD) AS RREAD,  MAX(perm.RWRITE) AS RWRITE,  MAX(perm.RDELETE) AS RDELETE,  MAX(perm.RSTAGE) AS RSTAGE,  MAX(perm.RMODERATE) AS RMODERATE,  MAX(perm.RCOMMENT) AS RCOMMENT";
		$perm_sql_from = " LEFT JOIN yg_site_" . $this->_site . "_permissions AS perm ON perm.OID = group2.ID";
		$perm_sql_where = " AND (";
		$roles = $this->permissions->getUsergroups();
		for ($r = 0; $r < count($roles); $r++) {
			$perm_sql_where .= "(perm.USERGROUPID = " . $roles[$r]["ID"] . ") ";
			if ((count($roles) - $r) > 1) {
				$perm_sql_where .= " OR ";
			}
		}
		$perm_sql_where .= ") ";
		$sql = "SELECT
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED AS VERSIONPUBLISHED, group2.ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT, group2.PNAME AS PNAME,
					MAX(prop.VERSION) AS VERSION, prop.ACTIVE AS ACTIVE, prop.HIDDEN AS HIDDEN, prop.LOCKED AS LOCKED,
					prop.NAVIGATION AS NAVIGATIONID, prop.HASCHANGED AS HASCHANGED, prop.TEMPLATEID AS TEMPLATEID,
					navi.NAME AS MENUNAME, pv.*
					$perm_sql_select
				FROM
					($this->table AS group1, $this->table AS group2, yg_site_" . $this->_site . "_properties AS prop)
					$perm_sql_from
					LEFT JOIN yg_templates_navis AS navi ON navi.ID = prop.NAVIGATION
					LEFT JOIN yg_site_" . $this->_site . "_propsv AS pv ON pv.OID = prop.ID
				WHERE
					((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
					(prop.DELETED = 0) AND
					(group2.ID = prop.OBJECTID) AND (prop.TEMPLATEID = $templateId)  $filtersql_where
					$perm_sql_where
				GROUP BY
					group2.LFT, group2.RGT, group2.VERSIONPUBLISHED, group2.ID ORDER BY group2.LFT;";
		$blaetter = $this->cacheExecuteGetArray($sql);
		return $blaetter;
	}

	/**
	 * Helper method for processing of scheduled approvals
	 */
	function processSchedule() {
		$todo = $this->scheduler->getJobs();
		for ($i = 0; $i < count($todo); $i++) {
			$params = $todo[$i]["PARAMETERS"];
			$pid = $todo[$i]["OBJECTID"];
			switch ($todo[$i]["ACTIONCODE"]) {
				case "SCH_AUTOPUBLISH";
					if ($params["VERSION"] == ALWAYS_LATEST_APPROVED_VERSION) {
						$latestfinal = $this->getLatestApprovedVersion($pid);
						$this->publishversion($pid, $latestfinal);
					} else {
						$this->publishversion($pid, $params["VERSION"]);
					}
					$this->scheduler->removeJob($todo[$i]["ID"]);
					break;
			}
		}
	}

	/**
	 * Gets Locks for the specific Token
	 *
	 * @param string $token Lock Token
	 * @return array Array of Page Locks
	 * @throws Exception
	 */
	public function getLocksByToken($token) {
		$token = mysql_real_escape_string($token);
		if ($token == '') {
			return false;
		}
		$sql = "SELECT OBJECTID, LOCKED, TOKEN FROM yg_site_" . $this->_site . "_properties WHERE TOKEN = '" . $token . "';";
		$dbr = sYDB()->Execute($sql);
		if ($dbr === false) {
			throw new Exception(sYDB()->ErrorMsg() . ":: " . $sql);
		}
		$ra = $dbr->GetArray();
		return $ra;
	}

}

/// @cond DEV

/**
 * Generates a random String
 *
 * @param int $length Length of the String
 * @return string Random String
 */
settype($template, "string");
function GetRandomString($length) {
	// You could repeat the alphabet to get more randomness
	$template = "1234567890abcdefghijklmnopqrstuvwxyz";
	settype($length, "integer");
	settype($rndstring, "string");
	settype($a, "integer");
	settype($b, "integer");
	for ($a = 0; $a <= $length; $a++) {
		$b = rand(0, strlen($template) - 1);
		$rndstring .= $template[$b];
	}
	return $rndstring;
}

/// @endcond

/// @cond DEV

/**
 * Callback function dynamic creation of filters for the buildBackendFilter function
 *
 * @param array $list Reference to the list of WHERE conditions from the buildBackendFilter function
 * @param string $type Type of filter for SQL query
 * @param string $operator Operator for SQL query
 * @param int $value1 (optional) General purpose parameter for SQL query
 * @param int $value2 (optional) General purpose parameter for SQL query
 */
function PagesSearchCB(&$list, $type, $operator, $value1 = 0, $value2 = 0) {
	$op = GetContainsOperators($operator);
	switch ($type) {
		case 'CREATEDTS':
			if (0 < $value1) {
				$list['WHERE'][] = 'prop.CREATEDTS ' . $op . ' ' . (int)$value1;
			}
			break;

		case 'CHANGEDTS':
			if (0 < $value1) {
				$list['WHERE'][] = 'prop.CHANGEDTS ' . $op . ' ' . (int)$value1;
			}
			break;

		case 'LIMITER':
			if ((int)$value2 > 0) {
				$list['LIMIT'][] = 'LIMIT ' . (int)$value1 . ',' . (int)$value2;
			}
			break;

		case 'ORDER':
			$list['ORDER'][] = 'ORDER BY ' . $value1 . ' ' . $value2;
			break;
	}
}

/// @endcond

?>