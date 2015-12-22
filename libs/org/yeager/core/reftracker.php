<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Reftracker Types
 */
define("REFTYPE_PAGE",       1);
define("REFTYPE_CO",         2);
define("REFTYPE_FORMFIELD",  3);
define("REFTYPE_FILE",       4);
define("REFTYPE_EMAIL",      5);
define("REFTYPE_EXTERNAL",   6);
define("REFTYPE_IMAGE",      7);
define("REFTYPE_MAILING",    8);

/// @cond DEV

/**
 * The Reftracker class, which represents the Reference Tracker.
 */
class Reftracker extends \framework\Error {
	var $_db;

	/**
	 * Constructor of the Reftracker class
	 */
	function __construct() {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->_internPrefix = str_replace("/", '\/', sConfig()->getVar('CONFIG/REFTRACKER/INTERNALPREFIX'));
	}

	/**
	 * Removes all References for an Object
	 *
	 * @param int $srcType Source Reference Type
	 * @param int srcObjectId Source Object Id
	 * @param int $srcVersion Source Object version
	 * @throws Exception
	 */
	private function emptyRef($srcType, $srcObjectId, $srcVersion) {
		$srcType = (int)$srcType;
		$srcObjectId = (int)$srcObjectId;
		$srcVersion = (int)$srcVersion;

		$sql = "DELETE FROM yg_references WHERE SRCTYPE = ? AND SRCOID = ? AND SRCVER = ?;";
		$result = sYDB()->Execute($sql, $srcType, $srcObjectId, $srcVersion);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
	}

	/**
	 * Adds a new Reference to an Object
	 *
	 * @param int $srcType Source Reference Type
	 * @param int $srcObjectId Source Object Id
	 * @param int $srcVersion Source Object version
	 * @param int $trgtType Target Reference Type
	 * @param string $trgtObjectId Target Object Id
	 * @param string $trgtAdditionalId (optional) Target additional Id
	 * @throws Exception
	 */
	private function addRef($srcType, $srcObjectId, $srcVersion, $trgtType, $trgtObjectId, $trgtAdditionalId = "") {
		$srcType = (int)$srcType;
		$srcObjectId = (int)$srcObjectId;
		$srcVersion = (int)$srcVersion;
		$trgtType = (int)$trgtType;
		$trgtObjectId = sYDB()->escape_string($trgtObjectId);
		$trgtAdditionalId = sYDB()->escape_string($trgtAdditionalId);
		if ($trgtType > 0) {
			$sql = "INSERT INTO yg_references (SRCTYPE, SRCOID, SRCVER, TGTTYPE, TGTOID, TGTAID) VALUES (?, ?, ?, ?, ?, ?);";
			$result = sYDB()->Execute($sql, $srcType, $srcObjectId, $srcVersion, $trgtType, $trgtObjectId , $trgtAdditionalId);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
		}
	}

	/**
	 * Updates References to an URL
	 *
	 * @param int $srcType Source Reference Type
	 * @param int $srcObjectId Source Object Id
	 * @param int $srcVersion Source Object version
	 * @param string $url URL Reference
	 */
	function updateUrlRef($srcType, $srcObjectId, $srcVersion, $url) {
		$srcType = (int)$srcType;
		$srcObjectId = (int)$srcObjectId;
		$srcVersion = (int)$srcVersion;
		$this->emptyRef($srcType, $srcObjectId, $srcVersion);
		$regexp = "/(.*)" . $this->_internPrefix . "([a-z]*)\/([0-9]*)(\/*)(.*)/";

		$special_url = resolveSpecialURL($url);
		if ($special_url !== false) {
			$trgtAdditionalId = "";
			$trgtObjectId = "";
			$trgtType = 0;

			$special_url_info = getSpecialURLInfo($url);
			switch ($special_url_info['TYPE']) {
				case 'DOWN':
					$trgtType = REFTYPE_FILE;
					$trgtObjectId = $special_url_info['ID'];
					break;
				case 'IMG':
					$trgtType = REFTYPE_IMAGE;
					$trgtObjectId = $special_url_info['ID'];
					break;
				case 'PAGE':
					$trgtType = REFTYPE_PAGE;
					$trgtAdditionalId = $special_url_info['SITE'];
					$trgtObjectId = $special_url_info['ID'];
					break;
			}
			if (($trgtObjectId != '') && ($trgtType != 0)) {
				$this->addRef($srcType, $srcObjectId, $srcVersion, $trgtType, $trgtObjectId, $trgtAdditionalId);
			}
		} else {
			if (preg_match_all($regexp, $url, $internal) > 0) {
				$trgtAdditionalId = "";
				$trgtObjectId = "";
				$trgtType = 0;
				// Yeager
				if ($internal[2][0] == "download") {
					$trgtType = REFTYPE_FILE;
					$trgtObjectId = $internal[3][0];
				} else {
					if ($internal[2][0] == "page") {
						preg_match_all("/(.*)" . $this->_internPrefix . "([a-z]*)\/([0-9]*)\/([0-9]*)(\/*)(.*)/", $url, $linkinfo);
						$trgtType = REFTYPE_PAGE;
						$trgtAdditionalId = $linkinfo[3][0];
						$trgtObjectId = $linkinfo[4][0];
					} else {
						if ($internal[2][0] == "image") {
							$trgtType = REFTYPE_IMAGE;
							$trgtObjectId = $internal[3][0];
						}
					}
				}
				$this->addRef($srcType, $srcObjectId, $srcVersion, $trgtType, $trgtObjectId, $trgtAdditionalId);
			}
		}
	}

	/**
	 * Updates Reference to a File
	 *
	 * @param int $srcType Source Reference Type
	 * @param int $srcObjectId Source Object Id
	 * @param int $srcVersion Source Object version
	 * @param int $fileId File Reference
	 */
	function updateFileRef($srcType, $srcObjectId, $srcVersion, $fileId) {
		$srcType = (int)$srcType;
		$srcObjectId = (int)$srcObjectId;
		$srcVersion = (int)$srcVersion;
		$fileId = (int)$fileId;
		$this->emptyRef($srcType, $srcObjectId, $srcVersion);
		$trgtType = REFTYPE_FILE;
		$this->addRef($srcType, $srcObjectId, $srcVersion, $trgtType, $fileId);
	}

	/**
	 * Updates Reference to a Cblock
	 *
	 * @param int $srcType Source Reference Type
	 * @param int $srcObjectId Source Object Id
	 * @param int $srcVersion Source Object version
	 * @param int $cblockId Cblock Reference
	 */
	function updateCblockRef($srcType, $srcObjectId, $srcVersion, $cblockId) {
		$srcType = (int)$srcType;
		$srcObjectId = (int)$srcObjectId;
		$srcVersion = (int)$srcVersion;
		$cblockId = (int)$cblockId;
		$this->emptyRef($srcType, $srcObjectId, $srcVersion);
		$trgtType = REFTYPE_CO;
		$this->addRef($srcType, $srcObjectId, $srcVersion, $trgtType, $cblockId);
	}

	/**
	 * Updates Reference to an Email address from HTML Code
	 *
	 * @param int $srcType Source Reference Type
	 * @param int $srcObjectId Source Object Id
	 * @param int $srcVersion Source Object version
	 * @param string $html HTML Code
	 */
	private function updateMailHrefFromHtml($srcType, $srcObjectId, $srcVersion, $html) {
		$srcType = (int)$srcType;
		$srcObjectId = (int)$srcObjectId;
		$srcVersion = (int)$srcVersion;
		$regexp_href = '<a\s[^>]*href=("??)([^" >]*?)\\1[^>]*>(.*)<\/a>';
		if ((preg_match_all("/$regexp_href/siU", stripslashes($html), $matches, PREG_SET_ORDER) > 0)) {
			foreach ($matches as $match) {
				$targetUrl = $match[2];
				$targetid = "";
				$trgtType = 0;
				if (preg_match_all("/(.*)mailto:(.*)/", $targetUrl, $emails) > 0) {
					// Email
					$trgtObjectId = $emails[2][0];
					$trgtType = REFTYPE_EMAIL;
					$this->addRef($srcType, $srcObjectId, $srcVersion, $trgtType, $trgtObjectId);
				}
			}
		}
	}

	/**
	 * Updates References to Pages (internal Links)
	 *
	 * @param int $srcType Source Reference Type
	 * @param int $srcObjectId Source Object Id
	 * @param int $srcVersion Source Object version
	 * @param string $html HTML Code
	 */
	private function updateIntHrefFromHtml($srcType, $srcObjectId, $srcVersion, $html) {
		$srcType = (int)$srcType;
		$srcObjectId = (int)$srcObjectId;
		$srcVersion = (int)$srcVersion;
		$regexp_href = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
		if ((preg_match_all("/$regexp_href/siU", stripslashes($html), $matches, PREG_SET_ORDER) > 0)) {
			foreach ($matches as $match) {
				$targetUrl = $match[2];
				$trgtAdditionalId = "";
				$trgtObjectId = "";
				$trgtType = 0;

				$special_url = resolveSpecialURL($targetUrl);
				if ($special_url !== false) {
					$trgtAdditionalId = "";
					$trgtObjectId = "";
					$trgtType = 0;

					$special_url_info = getSpecialURLInfo($targetUrl);
					switch ($special_url_info['TYPE']) {
						case 'DOWN':
							$trgtType = REFTYPE_FILE;
							$trgtObjectId = $special_url_info['ID'];
							break;
						case 'IMG':
							$trgtType = REFTYPE_IMAGE;
							$trgtObjectId = $special_url_info['ID'];
							break;
						case 'PAGE':
							$trgtType = REFTYPE_PAGE;
							$trgtAdditionalId = $special_url_info['SITE'];
							$trgtObjectId = $special_url_info['ID'];
							break;
					}
				} else {
					if (preg_match_all("/(.*)" . $this->_internPrefix . "([a-z]*)\/([0-9]*)(\/*)(.*)/", $targetUrl, $internal) > 0) {
						// Yeager
						if ($internal[2][0] == "download") {
							$trgtType = REFTYPE_FILE;
							$trgtObjectId = $internal[3][0];
						} else {
							if ($internal[2][0] == "page") {
								preg_match_all("/(.*)" . $this->_internPrefix . "([a-z]*)\/([0-9]*)\/([0-9]*)(\/*)(.*)/", $targetUrl, $linkinfo);
								$trgtType = REFTYPE_PAGE;
								$trgtAdditionalId = $linkinfo[3][0];
								$trgtObjectId = $linkinfo[4][0];
							} else {
								if ($internal[2][0] == "image") {
									$trgtType = REFTYPE_IMAGE;
									$trgtObjectId = $internal[3][0];
								}
							}
						}
					}
				}
				if (($trgtType != 0) && ($trgtObjectId != '')) {
					$this->addRef($srcType, $srcObjectId, $srcVersion, $trgtType, $trgtObjectId, $trgtAdditionalId);
				}
			}
		}
	}

	/**
	 * Updates References to Images (internal Links)
	 *
	 * @param int $srcType Source Reference Type
	 * @param int $srcObjectId Source Object Id
	 * @param int $srcVersion Source Object version
	 * @param string $html HTML Code
	 */
	private function updateImgHrefFromHtml($srcType, $srcObjectId, $srcVersion, $html) {
		$srcType = (int)$srcType;
		$srcObjectId = (int)$srcObjectId;
		$srcVersion = (int)$srcVersion;
		$regexp_img = "<img\s[^>]*src=(\"??)([^\" >]*?)[^>]*(.*)>";
		if (preg_match_all("/$regexp_img/siU", stripslashes($html), $matches, PREG_SET_ORDER) > 0) {
			foreach ($matches as $match) {
				$targetUrl = $match[2];
				$trgtObjectId = "";
				$trgtType = 0;
				$special_url = resolveSpecialURL($targetUrl);
				if ($special_url !== false) {
					$special_url_info = getSpecialURLInfo($targetUrl);
					switch ($special_url_info['TYPE']) {
						case 'DOWN':
							$trgtType = REFTYPE_FILE;
							break;
						case 'IMG':
							$trgtType = REFTYPE_IMAGE;
							break;
						case 'PAGE':
							$trgtType = REFTYPE_PAGE;
							break;
					}
					$trgtObjectId = $special_url_info['ID'];
					if ($trgtType != 0) {
						$this->addRef($srcType, $srcObjectId, $srcVersion, $trgtType, $trgtObjectId);
					}
				} else {
					$linkType = checkLinkInternalExternal($targetUrl);
					$targetUrlArray = explode('/', $targetUrl);
					switch($linkType['TYPE']) {
						case 'internal':
							$trgtType = REFTYPE_PAGE;
							$trgtObjectId = $linkType['INFO']['ID'];
							break;
						case 'file':
							if ($targetUrlArray[1] == 'image') {
								$trgtType = REFTYPE_IMAGE;
							} else {
								$trgtType = REFTYPE_FILE;
							}
							$trgtObjectId = $linkType['INFO']['FILE_ID'];
							break;
					}
					$this->addRef($srcType, $srcObjectId, $srcVersion, $trgtType, $trgtObjectId);
				}
			}
		}
	}

	/**
	 * Updates References to Pages (external Links)
	 *
	 * @param int $srcType Source Reference Type
	 * @param int $srcObjectId Source Object Id
	 * @param int $srcVersion Source Object version
	 * @param string $html HTML Code
	 */
	private function updateExtHrefFromHtml($srcType, $srcObjectId, $srcVersion, $html) {
		$srcType = (int)$srcType;
		$srcObjectId = (int)$srcObjectId;
		$srcVersion = (int)$srcVersion;
		$regexp_href = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
		if ((preg_match_all("/$regexp_href/siU", stripslashes($html), $matches, PREG_SET_ORDER) > 0)) {
			foreach ($matches as $match) {
				$targetUrl = $match[2];

				$special_url = resolveSpecialURL($targetUrl);
				if ((preg_match_all("/(.*)" . $this->_internPrefix . "([a-z]*)\/([0-9]*)(\/*)(.*)/", $targetUrl, $internal) == 0) && ($special_url === false)) {
					if (preg_match_all("/(.*)mailto:(.*)/", $targetUrl, $internal) == 0) {
						$targetid = "";
						$trgtType = 0;
						$trgtType = REFTYPE_EXTERNAL;
						$trgtObjectId = $targetUrl;
						if (strlen($trgtObjectId) > 0) {
							$this->addRef($srcType, $srcObjectId, $srcVersion, $trgtType, $trgtObjectId);
						}
					}
				}
			}
		}
	}

	/**
	 * Updates References from HTML Code
	 *
	 * @param int $srcType Source Reference Type
	 * @param int $srcObjectId Source Object Id
	 * @param int $srcVersion Source Object version
	 * @param string $html HTML Code
	 */
	function updateReferencesFromHtml($srcType, $srcObjectId, $srcVersion, $html) {
		$srcType = (int)$srcType;
		$srcObjectId = (int)$srcObjectId;
		$srcVersion = (int)$srcVersion;

		$this->emptyRef($srcType, $srcObjectId, $srcVersion);
		$this->updateMailHrefFromHtml($srcType, $srcObjectId, $srcVersion, $html);
		$this->updateIntHrefFromHtml($srcType, $srcObjectId, $srcVersion, $html);
		$this->updateImgHrefFromHtml($srcType, $srcObjectId, $srcVersion, $html);
		$this->updateExtHrefFromHtml($srcType, $srcObjectId, $srcVersion, $html);
	}

	/**
	 * Gets all outgoing References for a Page
	 *
	 * @param int $siteId Site Id
	 * @param int $pageId Page Id
	 * @param int $version Page version
	 * @return array Array of References
	 * @throws Exception
	 */
	function getOutgoingForPage($siteId, $pageId, $version) {
		$siteId = (int)$siteId;
		$pageId = (int)$pageId;
		$version = (int)$version;
		$sql = "SELECT * FROM `yg_site_" . $siteId . "_lnk_cb` AS pco
				WHERE (pco.PID = ? AND pco.PVERSION = ?);";
		$result = sYDB()->Execute($sql, $pageId, $version);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		for ($i = 0; $i < count($ra); $i++) {
			if ($ra[$i]["CBVERSION"] == ALWAYS_LATEST_APPROVED_VERSION) {
				$cb = sCblockMgr()->getCblock($ra[$i]["CBID"]);
				$ra[$i]["CBVERSION"] = $cb->getLatestVersion();
			}

			if ($ra[$i]["CBVERSION"] > 0) {
				$sql = "SELECT
							ref.*,
							cowi.CBID AS CBID,
							cowi.CBVERSION AS CBVERSION
						FROM
							`yg_contentblocks_lnk_entrymasks` AS cowi,
							`yg_contentblocks_lnk_entrymasks_c` AS wic,
							`yg_references` AS ref
						WHERE
							(cowi.CBID = ? AND
							cowi.CBVERSION = ?) AND
							(cowi.ID = wic.LNK) AND
							(ref.SRCVER = ?) AND
							(ref.SRCOID = wic.ID) AND
							(ref.SRCTYPE = ?);";
				$result = sYDB()->Execute($sql, $ra[$i]["CBID"], $ra[$i]["CBVERSION"], $ra[$i]["CBVERSION"], REFTYPE_FORMFIELD);
				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$refs = $result->GetArray();
				for ($j = 0; $j < count($refs); $j++) {
					$ofp[] = $refs[$j];
				}
			}
		}
		return ($ofp);
	}

	/**
	 * Gets all outgoing References for a Mailing
	 *
	 * @param int $mailingId Mailing Id
	 * @param int $version Mailing version
	 * @return array Array of References
	 * @throws Exception
	 */
	function getOutgoingForMailing($mailingId, $version) {
		$mailingId = (int)$mailingId;
		$version = (int)$version;
		$sql = "SELECT * FROM `yg_mailing_lnk_cb` AS nco
				WHERE (nco.PID = ? AND nco.PVERSION = ?);";
		$result = sYDB()->Execute($sql, $mailingId, $version);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$ra = $result->GetArray();
		for ($i = 0; $i < count($ra); $i++) {
			if ($ra[$i]["CBVERSION"] == ALWAYS_LATEST_APPROVED_VERSION) {
				$cb = sCblockMgr()->getCblock($ra[$i]["CBID"]);
				$ra[$i]["CBVERSION"] = $cb->getLatestApprovedVersion();
			}
			if ($ra[$i]["CBVERSION"] > 0) {
				$sql = "SELECT ref.*,
							cowi.CBID AS CBID,
							cowi.CBVERSION AS CBVERSION
						FROM
							`yg_contentblocks_lnk_entrymasks` AS cowi,
							`yg_contentblocks_lnk_entrymasks_c` AS wic,
							`yg_references` AS ref
						WHERE
							(cowi.CBID = ? AND
							cowi.CBVERSION = ?) AND
							(cowi.ID = wic.LNK) AND
							-- (ref.SRCVER = " . $version . ") AND
							(ref.SRCOID = wic.ID) AND
							(ref.SRCTYPE = ?);";
				$result = sYDB()->Execute($sql, $ra[$i]["CBID"], $ra[$i]["CBVERSION"], REFTYPE_FORMFIELD);

				if ($result === false) {
					throw new Exception(sYDB()->ErrorMsg());
				}
				$refs = $result->GetArray();
				for ($j = 0; $j < count($refs); $j++) {
					$ofp[] = $refs[$j];
				}
			}
		}
		return ($ofp);
	}

	/**
	 * Gets all outgoing References for a Cblock
	 *
	 * @param int $cblockId Cblock Id
	 * @param int $version Cblock version
	 * @return array Array of References
	 * @throws Exception
	 */
	function getOutgoingForCblock($cblockId, $version) {
		$cblockId = (int)$cblockId;
		$version = (int)$version;

		if ($version > 0) {
			$sql = "SELECT ref.*, cowi.CBID AS CBID, cowi.CBVERSION AS CBVERSION FROM
				`yg_contentblocks_lnk_entrymasks` AS cowi,
				`yg_contentblocks_lnk_entrymasks_c` AS wic,
				`yg_references` AS ref
			WHERE
				(cowi.CBID = ? AND cowi.CBVERSION = ?) AND
				(cowi.ID = wic.LNK) AND
				(ref.SRCVER = ?) AND (ref.SRCOID = wic.ID) AND (ref.SRCTYPE = ?)";
			$result = sYDB()->Execute($sql, $cblockId, $version, $version, REFTYPE_FORMFIELD);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$refs = $result->GetArray();
			for ($j = 0; $j < count($refs); $j++) {
				$ofp[] = $refs[$j];
			}
		}
		return ($ofp);
	}

	/**
	 * Gets all incoming References for a Page
	 *
	 * @param int $siteId Site Id
	 * @param int $pageId Page Id
	 * @return array Array of References
	 * @throws Exception
	 */
	function getIncomingForPage($siteId, $pageId) {
		$siteId = (int)$siteId;
		$pageId = (int)$pageId;
		$version = (int)$version;
		$sql = "SELECT ref.* FROM `yg_references` AS ref
			WHERE
			  (ref.TGTTYPE = ?) AND (ref.TGTOID = ?) AND (ref.TGTAID = ?);";
		$result = sYDB()->Execute($sql, REFTYPE_PAGE, $pageId, $siteId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$refs = $result->GetArray();
		return ($refs);
	}

	/**
	 * Gets all incoming References for a File
	 *
	 * @param int $fileId File Id
	 * @return array Array of References
	 * @throws Exception
	 */
	function getIncomingForFile($fileId) {
		$fileId = (int)$fileId;

		$sql = "SELECT ref.* FROM `yg_references` AS ref WHERE
				( (ref.TGTTYPE = ?) OR
				  (ref.TGTTYPE = ?) )
				AND (ref.TGTOID = ?);";

		$result = sYDB()->Execute($sql, REFTYPE_IMAGE, REFTYPE_FILE, $fileId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$refs = $result->GetArray();
		return ($refs);
	}

}

/// @endcond

?>