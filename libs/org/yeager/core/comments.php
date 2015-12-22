<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Error codes
 */
define("ERROR_NONE",                                       1);
define("ERROR_COMMENTS_UNKNOWN",                        2001);
define("ERROR_COMMENTS_AUTHENTICATION_NEEDED",          2002);
define("ERROR_COMMENTS_NO_MOD_RIGHTS",                  2003);
define("ERROR_COMMENTS_NO_COMMENT_RIGHTS",              2004);
define("ERROR_COMMENTS_BLACKLISTED_WORD",               2005);
define("ERROR_COMMENTS_MINIMUM_POST_INTERVAL_EXCEEDED", 2006);
define("ERROR_COMMENTS_COMMENTING_IS_CLOSED",           2007);

/**
 * Gets an instance of the Comments manager
 *
 * @return object Comment manager object
 */
function sComments() {
	return Singleton::comments();
}

/**
 * The Comments class, which represents an instance of the Comments manager.
 */
class Comments extends \framework\Error {
	var $_db;
	var $_object;
	var $_objectprefix;
	var $_objectidentifier;
	var $permissions;

	/**
	 * Constructor of the Comments class
	 *
	 * @param object $object Object where this Class is attached
	 */
	public function __construct($object = NULL) {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->table = "yg_comments";
		$this->permissions = $object->permissions;
		$this->_object = $object;
		$this->fetchIdentifiers();
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

/// @cond DEV

	/**
	 * Fetches the identifiers from this object where the Comments class is attached to
	 * and maps them into private members
	 */
	private function fetchIdentifiers() {
		if (is_object($this->_object)) {
			$this->_objectprefix = @$this->_object->getObjectPrefix();
			$this->_objectidentifier = @$this->_object->getAdditionalIdentifier();
			$this->_objectpropertytable = @$this->_object->getPropertyTable();
		}
	}

/// @endcond

	/**
	 * Gets all Comments of this Object
	 *
	 * @param array $filterArray Filter array
	 * @param string $filterLimit Filter limit
	 * @param bool $includeUserImage (optional) TRUE if path to User image should be included FALSE if not
	 * @param bool $width (optional) Width of user image
	 * @param bool $height (optional) Height of user image
	 * @return array Array of Comments
	 */
	function getComments($filterArray, $filterLimit, $includeUserImage = true, $width = 0, $height = 0) {
		$oid = (int)$this->_object->getID();

		$commentsSettings = $this->getSettings();

		if ($filterArray) {
			$filterSelect = $filterFrom = $filterWhere = $filterLimit = $filterOrder = '';
			buildBackendFilter('CommentsFilterCB', $filterArray, $filterSelect, $filterFrom, $filterWhere, $filterLimit, $filterOrder);
		}

		if ($filterLimit) {
			$filterLimit = "LIMIT " . $filterLimit;
		}

		$perm_sql_where = " AND ( ";
		$roles = $this->permissions->getUsergroups();
		for ($r = 0; $r < count($roles); $r++) {
			$perm_sql_where .= "(p.USERGROUPID = " . $roles[$r]["ID"] . ") ";
			if ((count($roles) - $r) > 1) {
				$perm_sql_where .= " OR ";
			}
		}
		$perm_sql_where .= ")
			AND ( ((RREAD > 0) AND (SPAM = 0)) OR (USERID = " . $this->_uid . ") )";

		//if ($commentsSettings['FORCE_APPROVAL']) {
			$perm_sql_where .= " AND ((APPROVED = 1) OR (USERID = " . $this->_uid . ") OR (RMODERATE = 1)) ";
		//}

		$filterOrder = 'DESC';

		$sql = "SELECT
			c.ID AS ID,
			c.COMMENT AS COMMENT,
			c.PARENT AS PARENT,
			c.APPROVED AS APPROVED,
			c.SPAM AS SPAM,
			c.USERID AS USERID,
			c.USERNAME AS ANON_USERNAME,
			c.USEREMAIL AS ANON_USEREMAIL,
			CONCAT(yg_user_propsv.FIRSTNAME, ' ', yg_user_propsv.LASTNAME) AS USERNAME,
			yg_user_propsv.EMAIL AS USEREMAIL,
			lnk.ORDERPROD AS ORDERPROD,
			c.CREATEDTS AS CREATEDTS,
			c.CHANGEDTS AS CHANGEDTS,
			MAX(p.RMODERATE) AS RMODERATE,
			MAX(p.RCOMMENT) AS RCOMMENT
			FROM
			`yg_comments` AS c
			JOIN `" . $this->_object->getCommentsLinkTable() . "` AS lnk ON (lnk.OID = " . $oid . ")
			LEFT JOIN `" . $this->_object->getPermissionsTable() . "` AS p ON (p.OID = lnk.OID)
			LEFT JOIN `yg_user_propsv` AS yg_user_propsv ON (yg_user_propsv.OID = c.USERID)
			WHERE
			(lnk.COMMENTID = c.ID) $filterWhere $perm_sql_where
			GROUP BY ID
			ORDER BY CREATEDTS $filterOrder $filterLimit;";

		$resultarray = $this->cacheExecuteGetArray($sql);

		$anonUserId = (int)sConfig()->getVar('CONFIG/SYSTEMUSERS/ANONUSERID');

		for ($r = 0; $r < count($resultarray); $r++) {
			if ($resultarray[$r]["USERID"] == $anonUserId) {
				$resultarray[$r]["ANONYMOUS"] = 1;
			} else {
				$resultarray[$r]["ANONYMOUS"] = 0;
				if ($includeUserImage) $resultarray[$r]["USERIMAGE"] = sUserMgr()->getUserImage($resultarray[$r]["USERID"], $width, $height);
			}
		}

		if ($resultarray === false) {
			return ERROR_COMMENTS_UNKNOWN;
		}

		return $resultarray;
	}

	/**
	 * Counts all Comments of the Object
	 *
	 * @param array $filterArray (optional) Filter array
	 * @return int Number of Comments
	 */
	function getCommentsCount($filterArray) {
		return count($this->getComments($filterArray, NULL, false));
	}

	/**
	 * Checks if commenting on this Object is allowed
	 *
	 * @return bool TRUE if allowed or FALSE if Comments are closed
	 */
	function getStatus() {
		if ($this->_object) {
			$oid = (int)$this->_object->getID();

			// Check if auto-closing of comments is configured, if yes and over the limit->close commenting
			if ($oid) {
				$currSettings = $this->getSettings();
				if ($currSettings['AUTOCLOSE_AFTER_DAYS']) {
					// Get timestamp of first approval of this object
					$firstVersion = (int)$this->_object->getFirstApprovedVersion($oid);

					$sql = "SELECT CHANGEDTS FROM `" . $this->_objectpropertytable . "`
						WHERE (OBJECTID = " . $oid . ") AND (VERSION = " . $firstVersion . ");";

					$resultarray = $this->cacheExecuteGetArray($sql);

					if ($resultarray === false) {
						return ERROR_COMMENTS_UNKNOWN;
					}

					$firstCreated = (int)$resultarray[0]['CHANGEDTS'];

					$daysDiff = floor((time() - $firstCreated) / (60 * 60 * 24));

					if ($daysDiff > (int)$currSettings['AUTOCLOSE_AFTER_DAYS']) {
						$this->setStatus(false, true);
					}
				}
			}

			$objectVersion = (int)$this->_object->getVersion();

			$sql = "SELECT COMMENTSTATUS FROM `" . $this->_objectpropertytable . "`
				WHERE (OBJECTID = " . $oid . ") AND (VERSION = " . $objectVersion . ");";

			$resultarray = $this->cacheExecuteGetArray($sql);

			if ($resultarray === false) {
				return ERROR_COMMENTS_UNKNOWN;
			}

			return (int)$resultarray['0']['COMMENTSTATUS'];
		}
	}

	/**
	 * Sets commenting on the Object to allowed / closed
	 *
	 * @param bool $status TRUE if commenting is allowed or FALSE if comments are closed
	 * @param bool $autostatus (optional) TRUE if the commenting auto-close time-interval should be reset
	 * @return int ERROR_NONE on success or ERROR_COMMENTS_NO_MOD_RIGHTS if moderator permissions are missing
	 */
	function setStatus($status = false, $autostatus = false) {
		$oid = (int)$this->_object->getID();
		$status = (int)$status;
		$autostatus = (int)$autostatus;
		$autoStatusSQL = '';
		$autoStatusWhereSQL = '';

		// Check permissions (RMODERATE is required)
		if ($this->permissions->checkInternal($this->_uid, $oid, 'RMODERATE') || $autostatus) {
			$objectVersion = (int)$this->_object->getVersion();

			if ($autostatus && !$status) {
				$autoStatusSQL = ", COMMENTSTATUS_AUTO = 0";
				$autoStatusWhereSQL = "AND (COMMENTSTATUS_AUTO = 1)";
			}

			$sql = "UPDATE `" . $this->_objectpropertytable . "`
						SET COMMENTSTATUS = $status $autoStatusSQL
					WHERE
						(OBJECTID = ?) AND (VERSION = ?) $autoStatusWhereSQL;";

			$result = sYDB()->Execute($sql, $oid, $objectVersion);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/COMMENT_CHANGE") == "true") {
				Singleton::FC()->emptyBucket();
			}
			return ERROR_NONE;
		} else {
			return ERROR_COMMENTS_NO_MOD_RIGHTS;
		}
	}

	/**
	 * Marks a specific Comment as spam
	 *
	 * @param int $commentId Comment Id
	 * @return int ERROR_NONE on success or ERROR_COMMENTS_NO_MOD_RIGHTS
	 */
	function setSpam($commentId = 0) {
		$oid = (int)$this->_object->getID();
		$commentId = (int)$commentId;
		$currentTS = time();

		// Check permissions (RMODERATE is required)
		if (($commentId > 0) && $this->permissions->checkInternal($this->_uid, $oid, 'RMODERATE')) {
			$sql = "UPDATE yg_comments SET SPAM = 1, CHANGEDTS = ? WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $currentTS, $commentId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/COMMENT_SETSPAM") == "true") {
				Singleton::FC()->emptyBucket();
			}
			return ERROR_NONE;
		} else {
			return ERROR_COMMENTS_NO_MOD_RIGHTS;
		}
	}

	/**
	 * Removes the spam marker for a specific Comment
	 *
	 * @param int $commentId Comment Id
	 * @return int ERROR_NONE on success or ERROR_COMMENTS_NO_MOD_RIGHTS
	 */
	function setNoSpam($commentId = 0) {
		$oid = (int)$this->_object->getID();
		$commentId = (int)$commentId;
		$currentTS = time();

		// Check permissions (RMODERATE is required)
		if (($commentId > 0) && $this->permissions->checkInternal($this->_uid, $oid, 'RMODERATE')) {
			$sql = "UPDATE yg_comments SET SPAM = 0, CHANGEDTS = ? WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $currentTS, $commentId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/COMMENT_SETNOSPAM") == "true") {
				Singleton::FC()->emptyBucket();
			}
			return ERROR_NONE;
		} else {
			return ERROR_COMMENTS_NO_MOD_RIGHTS;
		}
	}

	/**
	 * Sets the status of a specific Comment to "approved"
	 *
	 * @param int $commentId Comment Id
	 * @return int ERROR_NONE on success or ERROR_COMMENTS_NO_MOD_RIGHTS
	 */
	function setApproved($commentId = 0) {
		$oid = (int)$this->_object->getID();
		$commentId = (int)$commentId;
		$currentTS = time();

		// Check permissions (RMODERATE is required)
		if (($commentId > 0) && $this->permissions->checkInternal($this->_uid, $oid, 'RMODERATE')) {
			$sql = "UPDATE yg_comments SET APPROVED = 1, CHANGEDTS = ? WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $currentTS, $commentId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/COMMENT_APPROVE") == "true") {
				Singleton::FC()->emptyBucket();
			}
			return ERROR_NONE;
		} else {
			return ERROR_COMMENTS_NO_MOD_RIGHTS;
		}
	}

	/**
	 * Sets the status of a specific Comment to "unapproved"
	 *
	 * @param int $commentId Comment Id
	 * @return int ERROR_NONE on success or ERROR_COMMENTS_NO_MOD_RIGHTS
	 */
	function setUnapproved($commentId = 0) {
		$oid = (int)$this->_object->getID();
		$commentId = (int)$commentId;
		$currentTS = time();

		// Check permissions (RMODERATE is required)
		if (($commentId > 0) && $this->permissions->checkInternal($this->_uid, $oid, 'RMODERATE')) {
			$sql = "UPDATE yg_comments SET APPROVED = 0, CHANGEDTS = ? WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $currentTS, $commentId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/COMMENT_UNAPPROVE") == "true") {
				Singleton::FC()->emptyBucket();
			}
			return ERROR_NONE;
		} else {
			return ERROR_COMMENTS_NO_MOD_RIGHTS;
		}
	}

	/**
	 * Changes the content of a specific Comment
	 *
	 * @param int $commentId Comment Id
	 * @param string $commentText Text of the comment
	 * @return int ERROR_NONE on success or ERROR_COMMENTS_NO_MOD_RIGHTS
	 */
	function setComment($commentId = 0, $commentText) {
		$objectId = (int)$this->_object->getID();
		$commentId = (int)$commentId;
		$currentTS = time();
		$currentSettings = $this->getSettings();
		$blacklistWordsArray = explode("\n", $currentSettings['BLACKLIST']);
		$editAllowed = false;

		if ((int)$objectId == 0) {
			$objectId = (int)$this->_object->getID();
		}

		// Check if dereferrer is wanted
		if ($currentSettings['SE_RANK_DENIAL']) {
			$regexp_href = '<a\s[^>]*href=("??)([^" >]*?)\\1[^>]*>(.*)<\/a>';
			if ((preg_match_all("/$regexp_href/siU", stripslashes($commentText), $matches, PREG_SET_ORDER) > 0)) {
				foreach ($matches as $match) {
					$targetUrl = $match[2];
					$commentText = str_replace($targetUrl, sApp()->base . 'dereferrer/?' . urlencode($targetUrl), $commentText);
				}
			}
		}

		// Check if content of the post is blacklisted
		$isBlacklisted = false;
		foreach ($blacklistWordsArray as $blacklistWord) {
			if (stripos($commentText, $blacklistWord) !== false) {
				return ERROR_COMMENTS_BLACKLISTED_WORD;
			}
		}

		// Check if the post contains spam
		$isSpam = 0;
		foreach ($spamWordsArray as $spamWord) {
			if (stripos($commentText, $spamWord) !== false) {
				$isSpam = 1;
			}
		}

		// Check if HTML is allowed (if not, strip all tags - but allow BRs)
		if (!$currentSettings['ALLOW_HTML']) {
			$commentText = strip_tags($commentText, '<br><BR><br /><BR />');
		}

		// Check permissions (RMODERATE is required or you have to be the creator of the comment)
		if ($this->permissions->checkInternal($this->_uid, $objectId, 'RMODERATE')) {
			$editAllowed = true;
		} else {
			$sql = "SELECT USERID FROM `yg_comments`
				WHERE ( ID = " . $commentId . " );";
			$resultarray = $this->cacheExecuteGetArray($sql);

			if ($resultarray === false) {
				return ERROR_COMMENTS_UNKNOWN;
			}

			if ($resultarray[0]['USERID'] == $this->_uid) {
				$editAllowed = true;
			}
		}

		if (($commentId > 0) && $editAllowed) {
			$sql = "UPDATE yg_comments SET COMMENT = ?, CHANGEDTS = ? WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $commentText, $currentTS, $commentId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/COMMENT_CHANGE") == "true") {
				Singleton::FC()->emptyBucket();
			}
			return ERROR_NONE;
		} else {
			return ERROR_COMMENTS_NO_MOD_RIGHTS;
		}
	}

	/**
	 * Adds a Comment to the Object
	 *
	 * @param int $objectId Object Id
	 * @param string $commentText Text of the comment
	 * @param int $parentCommentId (optional) Id of the parent Comment
	 * @param string $userName (optional) Name to be used when user in Anonymous usergroup
	 * @param string $userEmail (optional) Email to be used when user in Anonymous usergroup
	 * @return int ERROR_NONE on success or other error code
	 */
	function add($commentText = '', $parentCommentId = 0, $userName = '', $userEmail = '') {
		$objectId = (int)$this->_object->getID();
		$parentCommentId = (int)$parentCommentId;
		$commentText = sYDB()->escape_string($commentText);
		$userName = sYDB()->escape_string($userName);
		$userEmail = sYDB()->escape_string($userEmail);
		$currentTS = time();
		$currentSettings = $this->getSettings();
		$spamWordsArray = explode("\n", $currentSettings['SPAMLIST']);
		$blacklistWordsArray = explode("\n", $currentSettings['BLACKLIST']);

		if ((int)$objectId == 0) {
			$objectId = (int)$this->_object->getID();
		}

		// Check UserID vs. UserName/UserEmail
		$userID = (int)$this->_uid;

		// Check if approval is needed
		if ($currentSettings['FORCE_APPROVAL']) {
			$approved = 0;
		} else {
			$approved = 1;
		}
		// But only require approval when user is not a moderator
		if ($this->permissions->checkInternal($this->_uid, $objectId, 'RMODERATE')) {
			$approved = 1;
		}

		// Check if dereferrer is wanted
		if ($currentSettings['SE_RANK_DENIAL']) {
			$regexp_href = '<a\s[^>]*href=("??)([^" >]*?)\\1[^>]*>(.*)<\/a>';
			if ((preg_match_all("/$regexp_href/siU", stripslashes($commentText), $matches, PREG_SET_ORDER) > 0)) {
				foreach ($matches as $match) {
					$targetUrl = $match[2];
					$commentText = str_replace($targetUrl, sApp()->base . 'dereferrer/?' . urlencode($targetUrl), $commentText);
				}
			}
		}

		// Check if a minimum time between postings is set
		if ($currentSettings['MINIMUM_INTERVAL']) {
			$anonymousUserID = (int)sConfig()->getVar('CONFIG/SYSTEMUSERS/ANONUSERID');
			$lastTS = NULL;

			if ($this->_uid == $anonymousUserID) {
				// Anonymous user, try to check via sessioncoookie
				$lastTS = (int)sSession()->getSessionVar('last_post');

				// Set to NULL if not set
				if (!$lastTS) {
					$lastTS = NULL;
				}
			} else {
				// Real user, try to check via database
				$lastComment = $this->getLatestCommentByUser($this->_uid);
				$lastTS = (int)$lastComment['CREATEDTS'];
			}

			$currentTS = (int)time();

			if ($lastTS && (($lastTS + $currentSettings['MINIMUM_INTERVAL']) > $currentTS)) {
				return ERROR_COMMENTS_MINIMUM_POST_INTERVAL_EXCEEDED;
			}
		}

		// Check if content of the post is blacklisted
		$isBlacklisted = false;
		foreach ($blacklistWordsArray as $blacklistWord) {
			if (stripos($commentText, $blacklistWord) !== false) {
				return ERROR_COMMENTS_BLACKLISTED_WORD;
			}
		}

		// Check if the post contains spam
		$isSpam = 0;
		foreach ($spamWordsArray as $spamWord) {
			if (stripos($commentText, $spamWord) !== false) {
				$isSpam = 1;
			}
		}

		// Get userid of anonymous user
		$anonymousUserID = (int)sConfig()->getVar('CONFIG/SYSTEMUSERS/ANONUSERID');

		// Check if authentication is needed
		if (($currentSettings['FORCE_AUTHENTICATION']) && ($userID == $anonymousUserID)) {
			return ERROR_COMMENTS_AUTHENTICATION_NEEDED;
		}

		// Check if HTML is allowed (if not, strip all tags - but allow BRs)
		if (!$currentSettings['ALLOW_HTML']) {
			$commentText = strip_tags($commentText, '<br><BR><br /><BR />');
		}

		// Check permissions (RCOMMENT is required)
		if ($this->permissions->checkInternal($this->_uid, $objectId, 'RCOMMENT')) {

			// Check if commenting is allowed
			$commentStatus = $this->getStatus();

			if (($commentStatus != 0) || ($this->permissions->checkInternal($this->_uid, $objectId, 'RMODERATE'))) {
				// Insert into comments-table
				$commentText = sYDB()->escape_string($commentText);
				$sql = "INSERT INTO `yg_comments`
					( `ID` , `COMMENT`, `PARENT`, `USERID`, `USERNAME`, `USEREMAIL`, `APPROVED`, `SPAM`, `CREATEDTS`, `CHANGEDTS`)
					VALUES
					( NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

				$result = sYDB()->Execute($sql, $commentText, $parentCommentId, $userID, $userName, $userEmail, $approved, $isSpam, $currentTS, $currentTS);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$newCommentID = sYDB()->Insert_ID();

				// Insert into link-table
				$sql = "INSERT INTO `" . $this->_object->getCommentsLinkTable() . "`
					( `ID` , `OID`, `COMMENTID`, `ORDERPROD`)
					VALUES
					( NULL, ?, ?, '9999');";
				$result = sYDB()->Execute($sql, $objectId, $newCommentID);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}

				// Write into session
				sSession()->setPSessionVar('last_post', time());

				if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/COMMENT_ADD") == "true") {
					Singleton::FC()->emptyBucket();
				}

				return ERROR_NONE;
			} else {
				return ERROR_COMMENTS_COMMENTING_IS_CLOSED;
			}
		} else {
			return ERROR_COMMENTS_NO_COMMENT_RIGHTS;
		}
	}

	/**
	 * Removes a specific Comment
	 *
	 * @param int $objectId Object Id
	 * @param int $commentId Comment Id
	 * @return int ERROR_NONE on success or ERROR_COMMENTS_NO_MOD_RIGHTS
	 */
	function remove($objectId = 0, $commentId = 0) {
		$objectId = (int)$objectId;
		$commentId = (int)$commentId;

		if ((int)$objectId == 0) {
			$objectId = (int)$this->_object->getID();
		}

		// Check permissions (RCOMMENT is required)
		if (($commentId > 0) && $this->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectId, 'RMODERATE')) {
			$sql = "DELETE FROM yg_comments WHERE (ID = ?);";
			$result = sYDB()->Execute($sql, $commentId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$sql = "DELETE FROM " . $this->_object->getCommentsLinkTable() . " WHERE (COMMENTID = ?);";
			$result = sYDB()->Execute($sql, $commentId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}

			if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/COMMENT_REMOVE") == "true") {
				Singleton::FC()->emptyBucket();
			}
			return ERROR_NONE;
		} else {
			return ERROR_COMMENTS_NO_MOD_RIGHTS;
		}
	}

	/**
	 * Counts all Comments
	 *
	 * @param array $filterArray (optional) Filter array
	 * @return int Number of Comments
	 */
	function getAllCommentsCount($filterArray) {
		return count($this->getAllComments($filterArray));
	}

	/**
	 * Gets all Comments
	 *
	 * @param array $filterArray Filter array
	 * @param string $filterLimit Filter limit
	 * @return array Array of Comments
	 */
	function getAllComments($filterArray, $filterLimit) {

		if ($filterArray) {
			$filterSelect = $filterFrom = $filterWhere = $filterLimit = $filterOrder = $filterHaving = '';
			buildBackendFilter('CommentsFilterCB', $filterArray, $filterSelect, $filterFrom, $filterWhere, $filterLimit, $filterOrder, $filterHaving);
		}

		if ($filterLimit) {
			$filterLimit = "LIMIT " . $filterLimit;
		}

		if ($filterHaving) {
			$filterHaving = "AND " . $filterHaving;
		}

		$filterOrder = 'DESC';

		$siteMgr = new Sites();
		$allSites = $siteMgr->getList();

		$sitesIfExpressionSQL = '';
		$sitesIfExpressionSQL2 = '';
		$sitesJoinExpressionSQL = '';
		$sitesIDCoalesceSQL = '';
		$sitesNAMECoalesceSQL = '';
		$sitesRREADCoalesceSQL = '';
		$sitesRMODERATECoalesceSQL = '';
		$sitesRCOMMENTCoalesceSQL = '';
		$sitesUSERGROUPIDCoalesceSQL = '';
		$sitesUSERGROUPIDHavingSQL = '';

		$currUser = new User($this->_uid);
		$roles = $currUser->getUsergroups();

		foreach ($allSites as $site) {
			$sitesIfExpressionSQL .= "
				IF(yg_site_" . $site['ID'] . "_tree.id IS NOT NULL, 'PAGE', ";

			$sitesIfExpressionSQL2 .= "
				IF(yg_site_" . $site['ID'] . "_tree.id IS NOT NULL, '" . $site['ID'] . "', ";

			$sitesJoinExpressionSQL .= "
				LEFT JOIN yg_comments_lnk_pages_" . $site['ID'] . "
				ON (c.id = yg_comments_lnk_pages_" . $site['ID'] . ".commentid)
				LEFT JOIN yg_site_" . $site['ID'] . "_tree
				ON (yg_site_" . $site['ID'] . "_tree.id = yg_comments_lnk_pages_" . $site['ID'] . ".OID)
				LEFT JOIN yg_site_" . $site['ID'] . "_permissions
				ON (yg_site_" . $site['ID'] . "_permissions.OID = yg_comments_lnk_pages_" . $site['ID'] . ".OID)
				";

			$sitesIDCoalesceSQL .= ", yg_site_" . $site['ID'] . "_tree.ID";
			$sitesNAMECoalesceSQL .= ", yg_site_" . $site['ID'] . "_tree.ID";
			$sitesRREADCoalesceSQL .= ", (yg_site_" . $site['ID'] . "_permissions.RREAD)";
			$sitesRMODERATECoalesceSQL .= ", (yg_site_" . $site['ID'] . "_permissions.RMODERATE)";
			$sitesRCOMMENTCoalesceSQL .= ", (yg_site_" . $site['ID'] . "_permissions.RCOMMENT)";
			$sitesUSERGROUPIDCoalesceSQL .= ", yg_site_" . $site['ID'] . "_permissions.USERGROUPID";
		}

		$sitesUSERGROUPIDHavingSQL .= " AND (";
		for ($r = 0; $r < count($roles); $r++) {
			$sitesUSERGROUPIDHavingSQL .= "(USERGROUPID = " . $roles[$r]["ID"] . ")";
			if ((count($roles) - $r) > 1) {
				$sitesUSERGROUPIDHavingSQL .= " OR ";
			}
		}
		$sitesUSERGROUPIDHavingSQL .= ")";

		$sitesIfExpressionSQL .= "NULL ))" . str_repeat(')', count($allSites));
		$sitesIfExpressionSQL2 .= "NULL " . str_repeat(')', count($allSites));

		$sql = "SELECT
			c.*,
			COALESCE(yg_contentblocks_tree.PNAME, yg_files_tree.TITLE $sitesNAMECoalesceSQL) AS `NAME`,
			IF(yg_contentblocks_tree.id IS NOT NULL, 'CO',
			IF(yg_files_tree.id IS NOT NULL, 'FILE',
			$sitesIfExpressionSQL AS OBJECTTYPE,
			COALESCE(yg_contentblocks_tree.ID, yg_files_tree.ID $sitesIDCoalesceSQL) AS `OBJECTID`,
			$sitesIfExpressionSQL2 AS SITEID,

			COALESCE((yg_contentblocks_permissions.RREAD), (yg_files_permissions.RREAD) $sitesRREADCoalesceSQL) AS `RREAD`,
			COALESCE((yg_contentblocks_permissions.RMODERATE), (yg_files_permissions.RMODERATE) $sitesRMODERATECoalesceSQL) AS `RMODERATE`,
			COALESCE((yg_contentblocks_permissions.RCOMMENT), (yg_files_permissions.RCOMMENT) $sitesRCOMMENTCoalesceSQL) AS `RCOMMENT`,

			COALESCE(yg_contentblocks_permissions.USERGROUPID, yg_files_permissions.USERGROUPID $sitesUSERGROUPIDCoalesceSQL) AS `USERGROUPID`
			FROM
			yg_comments AS c

			LEFT JOIN yg_comments_lnk_cb
				ON (c.id = yg_comments_lnk_cb.commentid)
			LEFT JOIN yg_contentblocks_tree
				ON (yg_contentblocks_tree.id = yg_comments_lnk_cb.oid)

			LEFT JOIN yg_comments_lnk_files
				ON (c.id = yg_comments_lnk_files.commentid)
			LEFT JOIN yg_files_tree
				ON (yg_files_tree.id = yg_comments_lnk_files.oid)

			LEFT JOIN yg_contentblocks_permissions
				ON (yg_contentblocks_permissions.OID = yg_comments_lnk_cb.OID)
			LEFT JOIN yg_files_permissions
				ON (yg_files_permissions.OID = yg_comments_lnk_files.OID)

			$sitesJoinExpressionSQL
			WHERE
				(1)
			$filterWhere
			GROUP BY
				c.ID
			HAVING
				(RREAD > 0)
			$sitesUSERGROUPIDHavingSQL
			$filterHaving
			ORDER BY
			c.CREATEDTS $filterOrder $filterLimit;";
		$resultarray = $this->cacheExecuteGetArray($sql);
		if ($resultarray === false) {
			return ERROR_COMMENTS_UNKNOWN;
		}

		return $resultarray;
	}

	/**
	 * Gets Comment settings
	 *
	 * @return array Array of Comment settings
	 */
	function getSettings() {
		$sql = "SELECT * FROM `yg_comments_settings` WHERE 1;";
		$resultarray = $this->cacheExecuteGetArray($sql);

		if ($resultarray === false) {
			return ERROR_COMMENTS_UNKNOWN;
		}

		return $resultarray[0];
	}

	/**
	 * Sets Comment settings
	 *
	 * @param array Array of settings
	 * @return bool ERROR_NONE on success or error code in case of an error
	 */
	function setSettings($settingsArray) {
		$sql = "UPDATE
					yg_comments_settings
				SET
					ALLOW_HTML = ?,
					AUTOCLOSE_AFTER_DAYS = ?,
					FORCE_APPROVAL = ?,
					FORCE_AUTHENTICATION = ?,
					MINIMUM_INTERVAL = ?,
					SE_RANK_DENIAL = ?,
					BLACKLIST = ?,
					SPAMLIST = ?
				WHERE 1;";

		$result = sYDB()->Execute($sql, $settingsArray['ALLOW_HTML'], $settingsArray['AUTOCLOSE_AFTER_DAYS'], $settingsArray['FORCE_APPROVAL'], $settingsArray['FORCE_AUTHENTICATION'], $settingsArray['MINIMUM_INTERVAL'], $settingsArray['SE_RANK_DENIAL'], $settingsArray['BLACKLIST'], $settingsArray['SPAMLIST']);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		} else {
			return ERROR_NONE;
		}
	}

	/**
	 * Gets the latest Comment of a specific User
	 *
	 * @param int $userId User Id
	 * @return array|int Array containing information about the Comment or ERROR_COMMENTS_UNKNOWN in case of an error
	 */
	function getLatestCommentByUser($userId) {
		$userId = (int)$userId;

		$sql = "SELECT * FROM `yg_comments`
			WHERE (USERID = " . $userId . ") ORDER BY CREATEDTS DESC LIMIT 1;";
		$resultarray = $this->cacheExecuteGetArray($sql);

		if ($resultarray === false) {
			return ERROR_COMMENTS_UNKNOWN;
		}

		return $resultarray[0];
	}

}

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
function CommentsFilterCB(&$list, $type, $operator, $value1 = 0, $value2 = 0) {
	$op = GetContainsOperators($operator);
	switch ($type) {
		case "CREATEDTS":
			if (0 < $value1) {
				$list["WHERE"][] = "c.CREATEDTS " . $op . " " . (int)$value1;
			}
			break;

		case "SPAM":
			$list["WHERE"][] = "c.SPAM " . $op . " " . (int)$value1;
			break;

		case "APPROVED":
			$list["WHERE"][] = "c.APPROVED " . $op . " " . (int)$value1;
			break;

		case "MODRIGHT":
			$list["HAVING"][] = "RMODERATE " . $op . " " . (int)$value1;
			break;

		case "COMMENTRIGHT":
			$list["HAVING"][] = "RCOMMENT " . $op . " " . (int)$value1;
			break;

		case "OBJECTTYPE":
			if (!in_array($value1, array("FILE", "CO", "PAGE", "MAILING"))) break;
			$list["HAVING"][] = "OBJECTTYPE " . $op . " '" . $value1 . "'";
			break;

		case "LIMITER":
			if ((int)$value2 > 0) {
				$list["LIMIT"][] = "LIMIT " . (int)$value1 . "," . (int)$value2;
			}
			break;

		case 'ORDER':
			$colarr = explode(".", sYDB()->escape_string(sanitize($value1)));
			$value1 = "`".implode("`.`", $colarr)."`";
			if ($value2 != "DESC") $value2 = "ASC";
			$list['ORDER'][] = 'ORDER BY ' . $value1 . ' ' . $value2;
			break;
	}
}

/// @endcond

