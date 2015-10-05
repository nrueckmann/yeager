<?php

	$jsQueue = new JSQueue(NULL);

	switch ($action) {
		case 'checkUpdates':
			$winID = $this->params['winID'];
			$updateMgr = new Updater();
			$currentUpdates = $updateMgr->getUpdates();
			if (count($currentUpdates)) {
				$newVersionNo = $currentUpdates[0]['VERSION'];
				$newVersionDate = date($itext['DATE_FORMAT'], $currentUpdates[0]['DATE']);
				$newVersionText = $itext['TXT_NEW_UPDATE_AVAILABLE'];
				$koala->queueScript( 'Koala.yg_updaterNewVersion(\''.$winID.'\', \''.$newVersionNo.'\', \''.$newVersionDate.'\', \''.$newVersionText.'\');' );
			}
			break;

		case 'updateInstalled':
			$updatePath = $this->approot.sConfig()->getVar('CONFIG/DIRECTORIES/UPDATES');
			$revision = $this->params['revision'];
			$matchingUpdates = glob($updatePath.'yeager_*_r'.$revision.'.php');
			if (count($matchingUpdates) > 0) {
				foreach($matchingUpdates as $matchingUpdate) {
					@unlink($matchingUpdate);
				}
			}
			break;

		case 'installUpdate':
			$installRevision = $this->params['installRevision'];
			$sigStart = "-----BEGIN YEAGER SIGNATURE-----\n";
			$sigEnd = "-----END YEAGER SIGNATURE-----";

			// Download, verify and trigger update installation
			$updatesDirectory = $this->approot.sConfig()->getVar('CONFIG/DIRECTORIES/UPDATES');

			if (!is_dir($updatesDirectory)) {
				mkdir($updatesDirectory);
			}

			$hadError = false;

			// Only download file when in online-mode
			if (substr($installRevision['url'], 0, 7) !== 'file://') {
				if (!downloadFromURL($installRevision['url'], $updatesDirectory.basename($installRevision['url']))) {
					$hadError = true;
				}
			}
			if ($hadError) {
				// Download error -> check if updates are already in local directory
				$koala->queueScript( 'Koala.yg_updaterOnError(\''.$installRevision['rev'].'\', \''.$itext['TXT_UPDATER_DOWNLOAD_ERROR'].'\');' );
			} else {
				// Verify update signature
				$data = file_get_contents($updatesDirectory.basename($installRevision['url']));
				$publicKey = file_get_contents(dirname(__FILE__).'/public.pem');

				$sigStartOffset = strpos($data, $sigStart)+strlen($sigStart);
				$sigEndOffset = strpos($data, $sigEnd);
				$asciiSignature = substr($data, $sigStartOffset, $sigEndOffset - $sigStartOffset);
				$binarySignature = base64_decode(str_replace(array($sigStart, $sigEnd, "\n"), '', $asciiSignature));

				// Strip away stub
				$data = substr($data, strpos($data, '__halt_compiler();') + 18);

				if (!(bool)openssl_verify($data, $binarySignature, $publicKey, OPENSSL_ALGO_SHA1)) {
					// Bad package signature
					$koala->queueScript( 'Koala.yg_updaterOnError(\''.$installRevision['rev'].'\', \''.$itext['TXT_UPDATER_BAD_SIGNATURE'].'\');' );
				} else {
					// Read out package information
					$updatePath = $this->approot.sConfig()->getVar('CONFIG/DIRECTORIES/UPDATES');
					$updatePackage = $updatePath.basename($installRevision['url']);
					$currArchive = new PayloadTar($updatePackage, true);
					$metaData = $currArchive->extractInString('installer/config.xml');
					$metaDataXML = new SimpleXMLElement($metaData);

					// Check if all dependencies are installed/available
					$dependencyError = false;
					$versionInfo = new Updater();
					$currVersion = $versionInfo->current_version;
					$dependencies = array();
					foreach ($metaDataXML->dependencies->version as $dependencyItem) {
						$dependencyItemVersion = (int)implode('.', (string)$dependencyItem);
						if ($currVersion < $dependencyItemVersion) {
							$dependencies[] = (string)$dependencyItem;
						}
					}
					foreach($dependencies as $dependency) {
						if (count(glob( $updatePath.'yeager_'.$dependency.'_r*.php' )) == 0) {
							$dependencyError = true;
							$koala->queueScript( 'Koala.yg_updaterOnError(\''.$installRevision['rev'].'\', \''.$itext['TXT_UPDATER_MISSING_DEPENDENCY'].': '.'yeager_'.$dependency.'_r*.php'.'\');' );
						}
					}
					if (!$dependencyError) {
						// Check if update ends with ".update" and rename if necessary
						if (substr($installRevision['url'], strrpos($installRevision['url'], '.')) == '.update') {
							$newName = substr(basename($installRevision['url']), 0, strrpos(basename($installRevision['url']), '.')).'.php';
							rename($updatesDirectory.basename($installRevision['url']), $updatesDirectory.$newName);
						}
						$localUrl = $this->docabsolut.sConfig()->getVar('CONFIG/DIRECTORIES/UPDATES').basename($installRevision['url']);
						$koala->queueScript( 'Koala.yg_startUpdate(\''.$localUrl.'\', \''.$installRevision['rev'].'\');' );
					}
				}
			}
			break;
	}

?>