<?php

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * Update URL
 */
define('YEAGER_UPDATER_URL',      'http://www.yeager.cm/updates/updater.php');	// Updater URL
define('YEAGER_UPDATER_TIMEOUT', 10);											// timeout in seconds

/// @cond DEV

/**
 * The Updater class, which represents an instance of an Update.
 */
class Updater extends \framework\Error {
	var $_db;
	var $_uid;

	var $current_version;
	var $current_version_string;

	/**
	 * Constructor of the Updater class
	 */
	function __construct() {
		$this->_db = sYDB();
		$this->_uid = &sUserMgr()->getCurrentUserID();
		$this->getVersion();
	}

	/**
	 * Gets the current version of yeager
	 *
	 * @return array Array containing current version
	 */
	function getVersion() {
		// Check for actual version
		$sql = "SELECT VERSION FROM `yg_version`;";
		$result = sYDB()->Execute($sql);

		if ($result === false) {
			// Check if table okt_version exists
			$sql = "SELECT VERSION FROM `okt_version`;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				// No versioning yet, hence Oktopus v2.0.0.0.0 and we should create table
				$sql = "CREATE TABLE `yg_version` (
										`VERSION` INT NOT NULL
										) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
				$result = sYDB()->Execute($sql);
				// And insert current version (2.0.0.0.0)
				$sql = "INSERT INTO `yg_version` ( `VERSION` ) VALUES ( 20000 );";
				$result = sYDB()->Execute($sql);
				// Now retry retrieving actual version (should work for sure now)
				$sql = "SELECT VERSION FROM `yg_version`;";
				$result = sYDB()->Execute($sql);
			} else {
				// Now retry retrieving actual version
				$sql = "SELECT VERSION FROM `okt_version`;";
				$result = sYDB()->Execute($sql);
			}
		}
		$resultarray = $result->GetArray();
		if ($resultarray) {
			$this->current_version = $resultarray[0]['VERSION'];
			$this->current_version_string = prettifyVersionString(implode('.', preg_split('#(?<=.)(?=.)#s', $resultarray[0]['VERSION'])));
			return $resultarray[0];
		} else {
			$this->current_version = false;
			$this->current_version_string = false;
			return false;
		}
	}

	/**
	 * Sets the current version of the yeager cms
	 *
	 * @param $version
	 * @return bool TRUE on success or FALSE in case of an error
	 */
	function setVersion($version) {
		$version = str_pad($version, 5, '0', STR_PAD_RIGHT);
		// Set current version
		$sql = "SELECT VERSION FROM `yg_version`;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			$sql = "SELECT VERSION FROM `okt_version`;";
			$result = sYDB()->Execute($sql);
			if ($result === false) {
				return false;
			} else {
				$sql = "UPDATE `okt_version` SET VERSION = " . (int)$version . ";";
				$result = sYDB()->Execute($sql);
			}
		} else {
			$sql = "UPDATE `yg_version` SET VERSION = " . (int)$version . ";";
			$result = sYDB()->Execute($sql);
		}
		if ($result) {
			// Set property 'current_version'
			$this->current_version = $version;
			$this->current_version_string = prettifyVersionString(implode('.', preg_split('#(?<=.)(?=.)#s', $version)));
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets all outstanding updates
	 *
	 * @return array Array containing update information with version/revision/date information
	 */
	function getUpdates() {

		$currVersionNumeric = $this->current_version;
		$postFields = array(
			'INFO' => serialize($_SERVER),
			'VERSION' => $currVersionNumeric
		);

		$xmlString = getStringFromURL(YEAGER_UPDATER_URL.'?'.http_build_query($postFields), (int)YEAGER_UPDATER_TIMEOUT);
		if ($xmlString !== false) {
			// Online Mode
			$updateDataXML = new SimpleXMLElement($xmlString);

			$updatesArray = array();
			foreach ($updateDataXML->update as $updateItem) {
				$currDate = explode('-', (string)$updateItem->date);
				$currDate = gmmktime(0, 0, 0, $currDate[1], $currDate[2], $currDate[0]);
				$updateVersion = prettifyVersionString((string)$updateItem->version);
				$updateVersionNumeric = (int)str_replace('.', '', (string)$updateItem->version);
				$updatesArrayItem = array(
					'TITLE'				=>	stripCDATA((string)$updateItem->title),
					'DATE'				=>	$currDate,
					'VERSION'			=>	$updateVersion,
					'VERSION_NUMERIC'	=>	$updateVersionNumeric,
					'REVISION'			=>	(string)$updateItem->revision,
					'DESCRIPTION'		=>	stripCDATA((string)$updateItem->description),
					'URL'				=>	(string)$updateItem->url_update
				);
				foreach ($updateItem->dependencies->version as $dependencyItem) {
					$updatesArrayItem['DEPENDENCIES'][] = (string)$dependencyItem;
				}
				$updatesArray[] = $updatesArrayItem;
			}
		} else {
			// Offline Mode
			$updatesDirectory = sApp()->approot.sConfig()->getVar('CONFIG/DIRECTORIES/UPDATES');
			$updatePackages = glob($updatesDirectory.'yeager_*_r*.php');

			// Read out package information
			$updatesArray = array();
			foreach($updatePackages as $updatePackage) {
				$currArchive = new PayloadTar($updatePackage, true);
				$metaData = $currArchive->extractInString('installer/config.xml');
				$metaDataXML = new SimpleXMLElement($metaData);
				$currDate = explode('-', (string)$metaDataXML->date);
				$currDate = gmmktime(0, 0, 0, $currDate[1], $currDate[2], $currDate[0]);
				$updateVersion = prettifyVersionString((string)$metaDataXML->version);
				$updateVersionNumeric = (int)str_replace('.', '', (string)$metaDataXML->version);
				$updatesArrayItem = array(
					'TITLE'				=>	stripCDATA((string)$metaDataXML->title),
					'DATE'				=>	$currDate,
					'VERSION'			=>	$updateVersion,
					'VERSION_NUMERIC'	=>	$updateVersionNumeric,
					'REVISION'			=>	(string)$metaDataXML->revision,
					'DESCRIPTION'		=>	str_replace('\n', "\n", stripCDATA((string)$metaDataXML->description)),
					'URL'				=>	'file://'.$updatePackage
				);
				foreach ($metaDataXML->dependencies->version as $dependencyItem) {
					$updatesArrayItem['DEPENDENCIES'][] = (string)$dependencyItem;
				}
				$updatesArray[] = $updatesArrayItem;
			}
		}

		$neededUpdates = array();
		foreach($updatesArray as $allUpdatesItem) {
			if ($allUpdatesItem['VERSION_NUMERIC'] > $currVersionNumeric) {
				$neededUpdates[] = $allUpdatesItem;
			}
		}
		$updatesArray = $neededUpdates;

		usort($updatesArray, function ($a, $b) {
			if ($a['REVISION'] == $b['REVISION']) {
				return 0;
			}
			return (version_compare($b['VERSION'], $a['VERSION'], '>=')) ? -1 : 1;
			/*return true;*/
		});

		return $updatesArray;
	}
}

/// @endcond

?>