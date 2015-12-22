<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Gets an instance of the Template manager
 *
 * @return object Template manager object
 */
function sTemplates() {
	return Singleton::templates();
}

/**
 * The Templates class, which represents an instance of the Template manager.
 */
class Templates extends \framework\Error {

	var $_uid;
	var $table;
	var $tree;
	var $permissions;

	/**
	 * Constructor of the Templates class
	 */
	function __construct() {
		$this->_uid = sUserMgr()->getCurrentUserID();
		$this->table = "yg_templates_tree";
		$this->tree = new tree($this);
		$this->permissions = new Permissions("yg_templates_permissions", $this);
		$this->setDir((string)sConfig()->getVar('CONFIG/DIRECTORIES/TEMPLATEDIR'));
	}

/// @cond DEV

	/**
	 * Gets the name of the database table containing the Templates tree
	 *
	 * @return string Tablename
	 */
	function getTreeTable() {
		return $this->table;
	}

/// @endcond

	/**
	 * Callback method which is executed when Usergroup permissions on the specified Template changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param string $permission Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param bool $value TRUE when the permission is granted, FALSE when it is removed
	 * @param int $objectId Template Id
	 */
	public function onPermissionChange($usergroupId, $permission, $value, $objectId) {
		return true;
	}

	/**
	 * Callback method which is executed when Usergroup permissions on a Template changes
	 *
	 * @param int $usergroupId Usergroup Id
	 * @param array $permissions Permission (RREAD, RWRITE, RDELETE, RSUB, RSTAGE, RMODERATE, RCOMMENT, RSEND)
	 * @param int $objectId Template Id
	 */
	public function onPermissionsChange($usergroupId, $permissions, $objectId) {
		return true;
	}

	/**
	 * Sets the Templates folder in filesystem
	 *
	 * @param string Templates path
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setDir($path) {
		$this->_path = getRealpath($path) . "/";
		return true;
	}

	/**
	 * Gets the Templates folder in filesystem
	 *
	 * @return string Templates path
	 */
	function getDir() {
		return $this->_path;
	}

	/**
	 * Gets the full path to the specified Template
	 *
	 * @param int $templateId Template Id
	 * @return string Full path to Template
	 */
	function getFullPath($templateId) {
		$templateId = (int)$templateId;
		$sql = "SELECT PATH, FILENAME FROM yg_templates_properties WHERE OBJECTID = ?;";
		$result = sYDB()->Execute($sql, $templateId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $this->getDir() . $ra[0]["FILENAME"];
	}

	/**
	 * Sets the name of this Template
	 *
	 * @param int $templateId Template Id
	 * @param string $value Template name
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setName($templateId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE yg_templates_properties SET NAME = ? WHERE (OBJECTID = ?);";
			$result = sYDB()->Execute($sql, $value, $templateId);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Cleans up the provided identifier
	 *
	 * @param string $identifier Identifier
	 * @return string Clean identifier
	 */
	function filterIdentifier($identifier) {
		$identifier = sanitize($identifier);
		$identifier = str_replace(" ", "_", $identifier);
		$identifier = str_replace("&", "", $identifier);
		$identifier = str_replace("/", "_", $identifier);
		return $identifier;
	}

	/**
	 * Calculates a unique identifier for the specified Template
	 *
	 * @param int $templateId Template Id
	 * @param string $iteration (optional) Iteration
	 * @param string $name (optional) Name to calculate the PName from
	 * @return string Permanent name
	 */
	function calcIdentifier($templateId, $iteration = '', $name = '') {
		$templateId = (int)$templateId;
		$templateinfo = $this->getTemplate($templateId);
		$templateIdentifier = $templateinfo["NAME"];
		if ($name) {
			$templateIdentifier = $name;
		}
		$templateIdentifier = $this->filterIdentifier($templateIdentifier);
		if ($iteration != '') {
			$checktinfo = $this->getByIdentifier($templateIdentifier . '_' . $iteration);
		} else {
			$checktinfo = $this->getByIdentifier($templateIdentifier);
		}
		if ($checktinfo["OBJECTID"] == $templateId) {
			if ($iteration != '') {
				return $templateIdentifier . '_' . $iteration;
			} else {
				return $templateIdentifier;
			}
		} else {
			if ($checktinfo["OBJECTID"] == NULL) {
				if ($iteration != '') {
					return $templateIdentifier . '_' . $iteration;
				} else {
					return $templateIdentifier;
				}
			} else {
				if ($iteration == "") {
					$iteration = 1;
				}
				return $this->calcIdentifier($templateId, ++$iteration);
			}
		}
	}

	/**
	 * Sets the Template identifier
	 *
	 * @param int $templateId Template Id
	 * @param string $identifier Template identifier
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setIdentifier($templateId, $identifier) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$identifier = sYDB()->escape_string(sanitize($identifier));
			$identifier = $this->filterIdentifier($identifier);

			$checkidentifier = $this->getByIdentifier($identifier);
			if (($checkidentifier["OBJECTID"] != $templateId) && ($checkidentifier["OBJECTID"] > 0)) {
				$identifier = $this->calcIdentifier($templateId);
			}

			$sql = "UPDATE yg_templates_properties SET IDENTIFIER = ? WHERE (OBJECTID = ?);";
			$result = sYDB()->Execute($sql, $identifier, $templateId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets Template by identifier
	 *
	 * @param string $value Template identifier
	 * @return array Template information
	 */
	function getByIdentifier($value) {
		$value = sYDB()->escape_string(sanitize($value));
		$sql = "SELECT * FROM yg_templates_properties WHERE IDENTIFIER = ?;";
		$result = sYDB()->Execute($sql, $value);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $ra[0];
	}

	/**
	 * Sets Template description
	 *
	 * @param int $templateId Template Id
	 * @param string $value Template description
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setDescription($templateId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE yg_templates_properties SET DESCRIPTION = ? WHERE (OBJECTID = ?);";
			sYDB()->Execute($sql, $value, $templateId);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets Template path
	 *
	 * @param int $templateId Template Id
	 * @param string $value Template absolute path
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setPath($templateId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE yg_templates_properties SET PATH = ? WHERE (OBJECTID = ?);";
			sYDB()->Execute($sql, $value, $templateId);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets Template filename
	 *
	 * @param int $templateId Template Id
	 * @param string $value Template filename
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setFilename($templateId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE yg_templates_properties SET FILENAME = ? WHERE (OBJECTID = ?);";
			sYDB()->Execute($sql, $value, $templateId);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the preview picture of the specified Template
	 *
	 * @param int $templateId Template Id
	 * @param int $value File Id
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setPreview($templateId, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE yg_templates_properties SET FILE = ? WHERE (OBJECTID = ?);";
			sYDB()->Execute($sql, $value, $templateId);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adds a new Template
	 *
	 * @param int $parentTemplateId Parent Template Id
	 * @param int $folder (optional) Specifies if the new node should be a folder
	 * @return int|false The new Template Id or FALSE in case of an error
	 */
	function add($parentTemplateId, $folder = 0) {
		$parentTemplateId = (int)$parentTemplateId;
		$folder = (int)$folder;

		$rread = $this->permissions->checkInternal($this->_uid, $parentTemplateId, "RSUB");
		if ($rread && sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			// Knoten im Pagestree erzeugen
			$templateId = $this->tree->add($parentTemplateId);
			$itext = Singleton::itext();
			$text = sYDB()->escape_string($itext['TXT_NEW_OBJECT']);
			$sql = "INSERT INTO `yg_templates_properties` (`OBJECTID`, `FOLDER`, `NAME`) VALUES (?, ?, ?);";
			$result = sYDB()->Execute($sql, $templateId, $folder, $text);

			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$this->permissions->copyTo($parentTemplateId, $templateId);
			return $templateId;
		} else {
			return false;
		}
	}

	/**
	 * Gets a list of Templates
	 *
	 * @param int $templateId Template Id of the parent node - will use the root node in case it's not specified
	 * @return array Template List
	 */
	function getList($templateId) {
		$templateId = (int)$templateId;

		if ($templateId > 0) {
			$myinfo = $this->tree->getAll($templateId);
			$myleft = $myinfo["LFT"];
			$myrgt = $myinfo["RGT"];
			$subnodesql = " AND (group1.LFT >= $myleft AND group1.RGT <= $myrgt)";
			if (!$myinfo) {
				return false;
			}
		}

		$sql = "SELECT
					group2.LFT,
					group2.RGT,
					group2.LEVEL AS LEVEL,
					group2.PARENT AS PARENT,
					prop.NAME AS NAME,
					prop.IDENTIFIER AS IDENTIFIER,
					prop.FOLDER AS FOLDER,
					prop.OBJECTID AS ID
					$perm_sql_select
				FROM
					($this->table AS group1, $this->table AS group2, yg_templates_properties AS prop)
					$perm_sql_from
				WHERE
					((group2.LFT >= group1.LFT) AND (group2.LFT <= group1.RGT)) AND
					(group2.ID = prop.OBJECTID)
					$subnodesql
					$perm_sql_where
				GROUP BY
					group2.LFT, group2.RGT, group2.ID
				ORDER BY group2.LFT;";

		$dbr = sYDB()->Execute($sql);
		$blaetter = $dbr->GetArray();
		return $blaetter;
	}

	/**
	 * Gets the Template Tree
	 *
	 * @param int $templateId (optional) From which Template Id the tree should be returned
	 * @param int $maxLevels (optional) Specifies the maximum level of nodes to get
	 * @return array Template tree
	 */
	function getTree($templateId = NULL, $maxLevels = 2) {
		$maxLevels = (int)$maxLevels;
		if ($templateId > 0) {
			$currentlevel = $this->tree->getLevel($templateId);
		} else {
			$currentlevel = 1;
		}
		$tree = $this->tree->get($templateId, $currentlevel + $maxLevels);
		$ntree = array();
		for ($n = 0; $n < count($tree); $n++) {
			$props = $this->getTemplate($tree[$n]["ID"]);
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
	}

	/**
	 * Gets the parents of the specified Template
	 *
	 * @param int $templateId Template Id
	 * @return array Array of parents
	 */
	function getParents($templateId) {
		$templateId = (int)$templateId;
		$parentId = $this->tree->getParent($templateId);
		$i = 0;
		while ($parentId > 0) {
			$sql = "SELECT
					group2.LFT, group2.RGT, group2.ID AS ID, group2.LEVEL AS LEVEL, group2.PARENT AS PARENT
				FROM
					($this->table AS group2, yg_templates_properties AS prop)
				WHERE
					(group2.ID = prop.OBJECTID) AND (group2.ID = ?)
				GROUP BY
					group2.LFT, group2.RGT, group2.ID ORDER BY group2.LFT;";
			$dbr = sYDB()->Execute($sql, $parentId);
			$parents[$i] = $dbr->GetArray();
			$templateId = $parents[$i][0]['ID'];
			$parentId = $this->tree->getParent($templateId);
			$i++;
		}
		foreach ($parents as $parent_idx => $parent_item) {
			$templateInfo = $this->getTemplate($parent_item[0]['ID']);
			$parents[$parent_idx][0]['NAME'] = $templateInfo['NAME'];
			$parents[$parent_idx][0]['FOLDER'] = $templateInfo['FOLDER'];
		}
		return $parents;
	}

	/**
	 * Gets basic information about the specified Template by identifier
	 *
	 * @param string $identifier Template identifier
	 * @return array|false Array containing information about the Template or FALSE in case of an error
	 */
	function getTemplateByIdentifier($identifier) {
		$templateInfo = $this->getByIdentifier($identifier);
		return $this->getTemplate($templateInfo['OBJECTID']);
	}

	/**
	 * Gets basic information about the specified Template
	 *
	 * @param int $templateId Template Id
	 * @return array|false Array containing information about the Template or FALSE in case of an error
	 */
	function getTemplate($templateId) {
		$templateId = (int)$templateId;
		$sql = "SELECT * FROM yg_templates_properties WHERE OBJECTID = ?;";
		$result = sYDB()->Execute($sql, $templateId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $ra[0];
	}

	/**
	 * Gets the path of the Template preview
	 *
	 * @param int $templateId Template Id
	 * @return string Template preview path
	 */
	function getPreviewPath($templateId) {
		// Check for template preview
		$templatePreviewDir = getRealpath(sConfig()->getVar('CONFIG/DIRECTORIES/TEMPLATEPREVIEWDIR')) . '/';
		$foundFiles = glob($templatePreviewDir . $templateId . '-*');
		if (($foundFiles !== false) && count($foundFiles) > 0) {
			$previewFile = explode('/', $foundFiles[0]);
			$previewFile = $previewFile[count($previewFile) - 1];
			$previewDir = sConfig()->getVar('CONFIG/DIRECTORIES/TEMPLATEPREVIEWDIR');
		}
		return $previewDir . $previewFile;
	}

	/**
	 * Removes the specified Template
	 *
	 * @param int $templateId Template Id
	 *
	 * @return array Array with all elements which were successfully deleted
	 */
	function remove($templateId) {
		$templateId = $origTemplateId = (int)$templateId;
		$rootNode = $this->tree->getRoot();

		if ($templateId == $rootNode) {
			return array();
		}

		// Get all nodes
		$successNodes = array();
		$allNodes = $this->tree->get($templateId, 1000);
		foreach($allNodes as $allNodesItem) {
			$templateId = (int)$allNodesItem['ID'];

			if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES') && $this->permissions->checkInternal($this->_uid, $templateId, "RDELETE")) {
				// Remove template
				$templateFile = $this->getFullPath($templateId);

				$sql = "UPDATE `yg_mailing_properties` SET TEMPLATEID = 0 WHERE TEMPLATEID = ?;";
				sYDB()->Execute($sql, $templateId);

				$sites = sSites()->getList();
				foreach ($sites as $curr_site) {
					$sql = "UPDATE `yg_site_" . (int)$curr_site['ID'] . "_properties` SET TEMPLATEID = 0 WHERE TEMPLATEID = ?;";
					sYDB()->Execute($sql, $templateId);
				}

				$sql = "UPDATE `yg_site` SET TEMPLATEROOT = 0 WHERE TEMPLATEROOT = ?;";
				sYDB()->Execute($sql, $templateId);

				$sql = "UPDATE `yg_site` SET DEFAULTTEMPLATE = 0 WHERE DEFAULTTEMPLATE = ?;";
				sYDB()->Execute($sql, $templateId);

				$sql = "UPDATE `yg_mailing_settings` SET TEMPLATEROOT = 0 WHERE TEMPLATEROOT = ?;";
				sYDB()->Execute($sql, $templateId);

				$sql = "UPDATE `yg_mailing_settings` SET DEFAULTTEMPLATE = 0 WHERE DEFAULTTEMPLATE = ?;";
				sYDB()->Execute($sql, $templateId);

				$sql = "DELETE FROM `yg_templates_properties` WHERE OBJECTID = ?;";
				sYDB()->Execute($sql, $templateId);

				$sql = "DELETE FROM `yg_templates_contentareas` WHERE TEMPLATE = ?;";
				sYDB()->Execute($sql, $templateId);

				$sql = "DELETE FROM `yg_templates_navis` WHERE TEMPLATE = ?;";
				sYDB()->Execute($sql, $templateId);

				$this->permissions->clear($templateId);

				@unlink($templateFile);

				$successNodes[] = $templateId;
			}
		}
		if (in_array($origTemplateId, $successNodes)) {
			$this->tree->remove($origTemplateId);
		}
		return $successNodes;
	}

	/**
	 * Gets Contentareas of the specified Template
	 *
	 * @param int $templateId Template Id
	 * @return array|false Array containing Contentareas or FALSE in case of an error
	 */
	function getContentareas($templateId) {
		$templateId = (int)$templateId;
		$sql = "SELECT * FROM yg_templates_contentareas WHERE TEMPLATE = ? ORDER BY `ORDER`;";
		$result = sYDB()->Execute($sql, $templateId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $ra;
	}

	/**
	 * Gets basic information about the specified Contentarea
	 *
	 * @param int $contentareaId Contentarea Id
	 * @return array|false Contentarea information or FALSE in case of an error
	 */
	function getContentareaById($contentareaId) {
		$contentareaId = (int)$contentareaId;
		$sql = "SELECT * FROM yg_templates_contentareas WHERE ID = ?;";
		$result = sYDB()->Execute($sql, $contentareaId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $ra[0];
	}

	/**
	 * Helper method to match Contentareas in Content
	 *
	 * @param string $content Reference to Content
	 * @param array $matches Reference to Matches
	 */
	private function matchContentareas(&$content, &$matches) {
		preg_match_all("/(?U)(.*)(<!-- contentarea begin:).(.*).(-->)(.*)/", $content, $matches);
	}

/// @cond DEV

	/**
	 * Helper method to match Entrymasks in Contentareas
	 *
	 * @param string $contentarea Contentarea code
	 * @param string $content Reference to Content
	 * @return array Matches for Entrymasks in the specified Contentarea
	 */
	private function matchEntrymasks($contentarea, &$content) {
		preg_match_all("/<!--\s*contentarea begin:\s*$contentarea\s*-->(.*)<!--\s*contentarea end:\s*$contentarea\s*-->/sU", $content, $contentareacontent);
		$contentareaInner = $contentareacontent[1][0];
		preg_match_all("/<!--\s*contentarea accepts:\s*(.*)\s*-->/sU", $contentareaInner, $accepts);
		$accepts_array = explode(',', $accepts[1][0]);
		array_walk($accepts_array, (function (&$value) {
			$value = trim($value);
		}));
		if (count($accepts_array) > 0) {
			return $accepts_array;
		} else {
			preg_match_all("/CODE.+==.+\"(.*)\"/sU", $contentareaInner, $matches);
			return $matches[1];
		}
	}

/// @endcond

	/**
	 * Resolves mapping between Contentareas and Entrymasks
	 *
	 * @param int $templateId Template Id
	 * @return array|false Array of Contentareas with Entrymasks or FALSE in case of an error
	 */
	function resolveContentareaEntrymaskMapping($templateId) {
		$templateId = (int)$templateId;
		$sql = "SELECT PATH, FILENAME FROM yg_templates_properties WHERE OBJECTID = ?;";
		$result = sYDB()->Execute($sql, $templateId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		$filename = $ra[0]["FILENAME"];
		$path = $ra[0]["PATH"];
		$templatelines = @file($this->_path . $path . $filename);
		if ($templatelines && (count($templatelines) > 0)) {
			$templatecontent = implode('', $templatelines);
		} else {
			return false;
		}
		$contentareas = array();
		$this->matchContentareas($templatecontent, $matches);
		for ($i = 0; $i < count($matches[0]); $i++) {
			$contentareas[$i]["CODE"] = $matches[3][$i];
			$contentareas[$i]["ENTRYMASKS"] = $this->matchEntrymasks($contentareas[$i]["CODE"], $templatecontent);
		}
		return $contentareas;
	}

	/**
	 * Gets Contentareas from Template file
	 *
	 * @param int $templateId Template Id
	 * @return array|false Array containing Contentareas or FALSE in case of an error
	 */
	function getContentareasFromFile($templateId) {
		$templateId = (int)$templateId;
		$sql = "SELECT PATH, FILENAME FROM yg_templates_properties WHERE OBJECTID = ?;";
		$result = sYDB()->Execute($sql, $templateId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		$filename = $ra[0]["FILENAME"];
		$path = $ra[0]["PATH"];
		$templatelines = @file($this->_path . $path . $filename);
		if (count($templatelines) > 0) {
			$templatecontent = implode('', $templatelines);
		} else {
			return false;
		}
		$contentareas = array();
		$this->matchContentareas($templatecontent, $matches);
		for ($i = 0; $i < count($matches[0]); $i++) {
			$contentareas[$i]["CODE"] = $matches[3][$i];
		}
		return $contentareas;
	}

	/**
	 * Gets Contentareas from a temporary file
	 *
	 * @param string $filename Filename
	 * @return array|false Array containing Contentareas or FALSE in case of an error
	 */
	function getContentareasFromTempFile($filename) {
		$templatelines = @file($filename);
		if (count($templatelines) > 0) {
			$templatecontent = implode('', $templatelines);
		} else {
			return false;
		}
		$contentareas = array();
		$this->matchContentareas($templatecontent, $matches);
		for ($i = 0; $i < count($matches[0]); $i++) {
			$contentareas[$i]["CODE"] = $matches[3][$i];
		}
		return $contentareas;
	}

	/**
	 * Adds a Contentarea to a specific Template
	 *
	 * @param int $templateId Template Id
	 * @param string $code Contentarea code
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function addContentarea($templateId, $code) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$code = sYDB()->escape_string(sanitize($code));
			$sql = "INSERT INTO `yg_templates_contentareas` (`TEMPLATE` , `CODE`) VALUES (?, ?);";
			$result = sYDB()->Execute($sql, $templateId, $code);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes a Contentarea from a specific Template
	 *
	 * @param int $templateId Template Id
	 * @param string $code Contentarea code
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function removeContentarea($templateId, $code) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$code = sYDB()->escape_string(sanitize($code));
			$sql = "DELETE FROM `yg_templates_contentareas` WHERE TEMPLATE = ? AND CODE = ?;";
			$result = sYDB()->Execute($sql, $templateId, $code);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set the order of Contentareas in a Template
	 *
	 * @param int $templateId Template Id
	 * @param array $orderList Order List
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setContentareasOrder($templateId, $orderList) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$orderId = 1;
			foreach ($orderList as $orderListItem) {
				$orderList = (int)$orderList;
				$sql = "UPDATE `yg_templates_contentareas` SET `ORDER` = ? WHERE TEMPLATE = ? AND ID = ?;";
				$result = sYDB()->Execute($sql, $orderId, $templateId, $orderListItem);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$orderId++;
			}
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Sets the name of a Contentarea
	 *
	 * @param int $templateId Template Id
	 * @param string $code Contentarea code
	 * @param string $value Template name
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setContentareaName($templateId, $code, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$code = sYDB()->escape_string(sanitize($code));
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE yg_templates_contentareas SET NAME = ? WHERE (TEMPLATE = ?) AND (CODE = ?);";
			sYDB()->Execute($sql, $value, $templateId, $code);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets all Navigations of a Template
	 *
	 * @param int $templateId Template Id
	 * @return array|false Array of Navigations or FALSE in case of an error
	 */
	function getNavis($templateId) {
		$templateId = (int)$templateId;
		$sql = "SELECT * FROM yg_templates_navis WHERE TEMPLATE = ?;";
		$result = sYDB()->Execute($sql, $templateId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		return $ra;
	}

	/**
	 * Gets all Navigations from a Template file
	 *
	 * @param int $templateId Template Id
	 * @return array|false Array of Navigations or FALSE in case of an error
	 */
	function getNavisFromFile($templateId) {
		$templateId = (int)$templateId;
		$sql = "SELECT PATH, FILENAME FROM yg_templates_properties WHERE OBJECTID = ?;";
		$result = sYDB()->Execute($sql, $templateId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		$filename = $ra[0]["FILENAME"];
		$path = $ra[0]["PATH"];
		$templatelines = @file($this->_path . $path . $filename);
		if (count($templatelines) > 0) {
			$templatecontent = implode('', $templatelines);
		} else {
			return;
		}
		$navis = array();
		preg_match_all("/(?U)(.*)(<!-- navi:).(.*).(-->)(.*)/", $templatecontent, $matches);
		for ($i = 0; $i < count($matches[0]); $i++) {
			$navis[$i]["CODE"] = $matches[3][$i];
		}
		return $navis;
	}

	/**
	 * Gets all Navigations from a temporary Template file
	 *
	 * @param string $filename Filename
	 * @return array|false Array of Navigations or FALSE in case of an error
	 */
	function getNavisFromTempFile($filename) {
		$templatelines = @file($filename);
		if (count($templatelines) > 0) {
			$templatecontent = implode('', $templatelines);
		} else {
			return;
		}
		$navis = array();
		preg_match_all("/(?U)(.*)(<!-- navi:).(.*).(-->)(.*)/", $templatecontent, $matches);
		for ($i = 0; $i < count($matches[0]); $i++) {
			$navis[$i]["CODE"] = $matches[3][$i];
		}
		return $navis;
	}

	/**
	 * Adds a Navigation to a Template
	 *
	 * @param int $templateId Template Id
	 * @param string $code Navigation code
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function addNavi($templateId, $code) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$code = sanitize(sYDB()->escape_string($code));
			$sql = "INSERT INTO `yg_templates_navis` (`TEMPLATE`, `CODE`) VALUES (?, ?);";
			$result = sYDB()->Execute($sql, $templateId, $code);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes a Navigation from a Template
	 *
	 * @param int $templateId Template Id
	 * @param string $code Navigation code
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function removeNavi($templateId, $code) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$code = sYDB()->escape_string(sanitize($code));
			$sql = "DELETE FROM `yg_templates_navis` WHERE TEMPLATE = ? AND CODE = ?;";
			$result = sYDB()->Execute($sql, $templateId, $code);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the name of a Navigation
	 *
	 * @param int $templateId Template Id
	 * @param string $code Navigation code
	 * @param string Navigation name
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setNaviName($templateId, $code, $value) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$code = sYDB()->escape_string(sanitize($code));
			$value = sYDB()->escape_string(sanitize($value));
			$sql = "UPDATE yg_templates_navis SET NAME = ? WHERE (TEMPLATE = ?) AND (CODE = ?);";
			sYDB()->Execute($sql, $value, $templateId, $code);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets the default Navigation
	 *
	 * @param int $templateId Template Id
	 * @param string $code Navigation code
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setDefaultNavi($templateId, $code) {
		if (sUsergroups()->permissions->check($this->_uid, 'RTEMPLATES')) {
			$templateId = (int)$templateId;
			$code = sYDB()->escape_string(sanitize($code));
			$sql = "UPDATE yg_templates_navis SET `DEFAULT` = 0 WHERE (TEMPLATE = ?) AND (CODE <> ?);";
			sYDB()->Execute($sql, $templateId, $code);
			$sql = "UPDATE yg_templates_navis SET `DEFAULT` = 1 WHERE (TEMPLATE = ?) AND (CODE = ?);";
			sYDB()->Execute($sql, $templateId, $code);
			return true;
		} else {
			return false;
		}
	}

}

?>