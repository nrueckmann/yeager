<?php

	function getTimezones() {
		return array(
			'Pacific/Majuro',			'Etc/GMT-11',						'Pacific/Apia',
			'Pacific/Honolulu',			'America/Anchorage',				'America/Tijuana',
			'America/Los_Angeles',		'America/Phoenix',					'America/Chihuahua',
			'America/Phoenix',			'America/Chicago',					'America/Mexico_City',
			'America/Regina',			'America/Regina',					'America/Bogota',
			'America/New_York',			'America/Indianapolis',				'America/Caracas',
			'America/Asuncion',			'America/Halifax',					'America/Manaus',
			'America/Caracas',			'America/Santiago',					'America/St_Johns',
			'America/Sao_Paulo',		'America/Argentina/Buenos_Aires',	'America/Buenos_Aires',
			'America/Godthab',			'America/Montevideo',				'Etc/GMT-2',
			'Atlantic/South_Georgia',	'Atlantic/Azores',					'Atlantic/Cape_Verde',
			'Africa/Casablanca',		'Europe/London',					'UTC',
			'GMT',						'Europe/Berlin',					'Europe/Prague',
			'Europe/Paris',				'Europe/Belgrade',					'Africa/Luanda',
			'Asia/Amman',				'Europe/Athens',					'Asia/Beirut',
			'Africa/Harare',			'Europe/Helsinki',					'Asia/Jerusalem',
			'Africa/Cairo',				'Europe/Minsk',						'Africa/Windhoek',
			'Asia/Baghdad',				'Asia/Riyadh',						'Europe/Moscow',
			'Africa/Nairobi',			'Asia/Tehran',						'Asia/Muscat',
			'Asia/Baku',				'Asia/Yerevan',						'Indian/Mauritius',
			'Asia/Tbilisi',				'Asia/Kabul',						'Asia/Karachi',
			'Asia/Yekaterinburg',		'Asia/Karachi',						'Asia/Calcutta',
			'Asia/Colombo',				'Asia/Katmandu',					'Asia/Dhaka',
			'Asia/Novosibirsk',			'Asia/Rangoon',						'Asia/Bangkok',
			'Asia/Krasnoyarsk',			'Asia/Irkutsk',						'Asia/Singapore',
			'Asia/Shanghai',			'Australia/Perth',					'Asia/Taipei',
			'Asia/Ulaanbaatar',			'Asia/Yakutsk',						'Asia/Tokyo',
			'Asia/Seoul',				'Australia/Adelaide',				'Australia/Darwin',
			'Australia/Brisbane',		'Australia/Sydney',					'Pacific/Guam',
			'Australia/Hobart',			'Asia/Vladivostok',					'Pacific/Guadalcanal',
			'Pacific/Auckland',			'Pacific/Fiji',						'Etc/GMT+12',
			'Asia/Kamchatka',			'Pacific/Tongatapu'
		);
	}

	function TStoLocalTS($ts) {
		$ts = (int)$ts;
		$uid = (int)sUserMgr()->getCurrentUserID();
		$user = new User($uid);

		$user_timezone = $user->properties->getValue('TIMEZONE');
		global $tz;

		$offset = null;
		$offset = $tz['offset'];

		//Windows special fallback
		if (!$tz) {
			switch($user_timezone) {
				case 'Etc/GMT-11':
					$offset = -39600;
					break;
				case 'Etc/GMT-2':
					$offset = -7200;
					break;
				case 'Atlantic/South_Georgia':
					$offset = -7200;
					break;
				case 'GMT':
					$offset = 0;
					break;
				case 'Etc/GMT+12':
					$offset = 43200;
					break;
			}
		}

		// Save original timezone
		$currentTimeZone = date_default_timezone_get();

		// Get offset of user timezone
		date_default_timezone_set( $user_timezone );
		$realOffset = date('Z', $ts);

		// Reset original timezone
		date_default_timezone_set( $currentTimeZone );

		return ($ts + $realOffset);
	}

	function TSfromLocalTS($ts) {
		$ts = (int)$ts;
		$uid = (int)sUserMgr()->getCurrentUserID();
		$user = new User($uid);

		$user_timezone = $user->properties->getValue('TIMEZONE');

		$tz = null;
		$offset = null;
		$timezoneAbbreviations = timezone_abbreviations_list();
		foreach($timezoneAbbreviations as $timezoneAbbreviations_item) {
			foreach($timezoneAbbreviations_item as $timezone_item) {
				if ($timezone_item['timezone_id'] == $user_timezone) {
					$tz = $timezone_item;
					$offset = $timezone_item['offset'];
				}
			}
		}
		//Windows special fallback
		if (!$tz) {
			switch($user_timezone) {
				case 'Etc/GMT-11':
					$offset = -39600;
					break;
				case 'Etc/GMT-2':
					$offset = -7200;
					break;
				case 'Atlantic/South_Georgia':
					$offset = -7200;
					break;
				case 'GMT':
					$offset = 0;
					break;
				case 'Etc/GMT+12':
					$offset = 43200;
					break;
			}
		}

		// Save original timezone
		$currentTimeZone = date_default_timezone_get();

		// Get offset of user timezone
		date_default_timezone_set( $user_timezone );
		$realOffset = date('Z', $ts);

		// Reset original timezone
		date_default_timezone_set( $currentTimeZone );

		return ($ts - $realOffset);
	}

	function escapeJavaScriptText( $string ) {
		return str_replace("\n", '\n', str_replace('"', '\"', addcslashes(str_replace("\r", '', (string)$string), "\0..\37'\\")));
	}

	function fixAndMovePLUploads() {

		/*
		$ineinfile = print_r(file_get_contents("php://input"),true);
		file_put_contents('out.txt',$ineinfile);
		*/

		// HTTP headers for no cache etc
		header('Content-type: text/plain; charset=UTF-8');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		// Settings
		$tmpdir = ini_get("upload_tmp_dir");
		if (!$tmpdir) {
			$tmpdir = sConfig()->getVar("CONFIG/PATH/TMP");
			if (!$tmpdir) {
				$tmpdir = sys_get_temp_dir();
			}
		}

		$targetDir = $tmpdir . DIRECTORY_SEPARATOR . "plupload";
		$cleanupTargetDir = false; // Remove old files
		$maxFileAge = 60 * 60; // Temp file age in seconds

		// 5 minutes execution time
		@set_time_limit(5 * 60);
		// usleep(5000);

		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
		$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);

		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);

		// Remove old temp files
		if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
			while (($foundFile = readdir($dir)) !== false) {
				$filePath = $targetDir . DIRECTORY_SEPARATOR . $foundFile;

				// Remove temp files if they are older than the max age
				if (preg_match('/\\.tmp$/', $foundFile) && (filemtime($filePath) < time() - $maxFileAge))
					@unlink($filePath);
			}

			closedir($dir);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory. '.$targetDir.'"}, "id" : "id"}');

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];

		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				// Open temp file
				$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

					fclose($out);
					unlink($_FILES['file']['tmp_name']);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
		return $targetDir . DIRECTORY_SEPARATOR . $fileName;
	}

	function getIconForPage( $pageInfo ) {
		$img = 'page_small';
		$iconclass = 'page';
		$cststyle = '';

		$inactive = false;
		if ($pageInfo["ACTIVE"] == "0") {
			$img = 'page_inactive_small';
			$iconclass = 'pageinactive';
			$inactive = true;
		}

		$naviinfo = NULL;

		$templateMgr = new Templates();
		$navis = $templateMgr->getNavis($pageInfo["TEMPLATEID"]);
		for ($i = 0; $i < count($navis); $i++) {
		  if ($navis[$i]["ID"] == $pageInfo["NAVIGATIONID"]) {
			  $naviinfo = $navis[$i];
		  }
		}

		if (($pageInfo["HIDDEN"] == "1") || ($pageInfo["TEMPLATEID"]=="0") || (!$naviinfo['ID'])) {
			$img = 'page_hidden_small';
			$iconclass = 'pagehidden';
			if ($inactive==true) {
				$img = 'page_inactive_hidden_small';
				$iconclass = 'pagehiddeninactive';
			}
		}

		if (($pageInfo["VERSIONPUBLISHED"]+2 != $pageInfo["VERSION"]) && ($pageInfo["VERSIONPUBLISHED"]!=ALWAYS_LATEST_APPROVED_VERSION) && ($pageInfo["HASCHANGED"] == "1")) {
			// Editiert (grün)
			$cststyle = "changed";
		} elseif ($pageInfo["HASCHANGED"] == "1") {
			$cststyle = "changed";
		}
		if ($pageInfo["RWRITE"] == "0") {
			// Nur Leserecht (hellgrau)
			$cststyle .= " nowrite";
		}
		if ($pageInfo["RDELETE"] == "0") {
			// Nur Leserecht (hellgrau)
			$cststyle .= " nodelete";
		}
		if ($pageInfo["RSUB"] == "0") {
			$cststyle .= " nosub";
		}

		return array('img' => $img, 'iconclass' => $iconclass, 'style' => $cststyle);
	}

	function getStyleForContentblock( $cblockInfo, $getChanged ) {
		$cststyle = '';
		if ($getChanged) {
			if ( ($cblockInfo["VERSIONPUBLISHED"]+2 != $cblockInfo["VERSION"]) && ($cblockInfo["VERSIONPUBLISHED"]!=ALWAYS_LATEST_APPROVED_VERSION) && ($cblockInfo["HASCHANGED"] == "1") ) {
				// Editiert (grün)
				$cststyle = "changed changed1";
			} elseif ($cblockInfo["HASCHANGED"] == "1") {
				$cststyle = "changed changed2";
			}
		}
		if ($cblockInfo['RWRITE'] == '0') {
			// Nur Leserecht (hellgrau)
			$cststyle .= ' nowrite';
		}
		if ($cblockInfo["RDELETE"] == "0") {
			// Nur Leserecht (hellgrau)
			$cststyle .= " nodelete";
		}
		return $cststyle;
	}

	function calcPageDir ($dataCount, $defaultSortCol, $defaultSortOrder = 'ASC') {
		$pageDirPage = (int)sRequest()->parameters['pagedir_page'];
		$pageDirPerPage = sRequest()->parameters['pagedir_perpage'];

		$pageDirOrderBy = sRequest()->parameters['pagedir_orderby'];
		$pageDirOrderDir = sRequest()->parameters['pagedir_orderdir'];
		$pageDirFrom = sRequest()->parameters['pagedir_from'];

		if (!$pageDirFrom) $pageDirFrom = 1;

		if ($pageDirPerPage=='ALL') {
			$pageDirPerPage = -1;
		} else if (!$pageDirPerPage) {
			$pageDirPerPage = (int)sConfig()->getVar('CONFIG/PAGEDIR/DEFAULT_PER_PAGE');
		}
		$pageDirMaxPages = 1;
		if (!$pageDirPage) $pageDirPage = 1;

		if ($pageDirPerPage > 0) {
			$pageDirMaxPages = ceil($dataCount / $pageDirPerPage);
		}

		if ($pageDirMaxPages < 1) {
			$pageDirMaxPages = 1;
		}

		if ($pageDirPage > $pageDirMaxPages) {
			$pageDirPage = $pageDirMaxPages;
			$pageDirFrom = $pageDirPage * $pageDirPerPage - ($pageDirPerPage - 1);
		}

		if (!$pageDirOrderBy) $pageDirOrderBy = $defaultSortCol;
		if (!$pageDirOrderDir) {
			$pageDirOrderDir = $defaultSortOrder;
		} else {
			if ($pageDirOrderDir == 1) { $pageDirOrderDir = "ASC"; } else { $pageDirOrderDir = "DESC"; }
		}

		if ($pageDirPerPage > 0) {
			$pageDirLimit = ($pageDirFrom-1).','.$pageDirPerPage;
		}

		sKoala()->queueScript('$K.yg_pageDirUpdate(\''.sRequest()->parameters['win_no'].'\', '.$pageDirPage.', '.$pageDirPerPage.', '.$pageDirMaxPages.', \''.$pageDirOrderBy.'\', \''.$pageDirOrderDir.'\', '.$pageDirFrom.', \''.$pageDirLimit.'\');');

		if ($pageDirPerPage == -1) {
			$itext = sItext();
			$pageDirPerPage = ($itext['TXT_PAGEDIR_SHOWALL_SHORT']!='')?($itext['TXT_PAGEDIR_SHOWALL_SHORT']):('$TXT_PAGEDIR_SHOWALL_SHORT');
		}

		sSmarty()->assign('pageDirPage', $pageDirPage );
		sSmarty()->assign('pageDirPerPage', $pageDirPerPage );
		sSmarty()->assign('pageDirMaxPages', $pageDirMaxPages );

		return array(
			'pageDirOrderBy' => $pageDirOrderBy,
		 	'pageDirOrderDir' => $pageDirOrderDir,
			'pageDirLimit' => $pageDirLimit,
			'pageDirMaxPages' => $pageDirMaxPages
		);
	}

	function resolveSpecialURL ($specialURL) {
		$siteMgr = new Sites();
		$specialURL = urldecode($specialURL);
		$webRoot = rtrim(ltrim((string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/');
		if (strpos($specialURL, $webRoot) === 0) {
			$specialURL = substr($specialURL, strlen($webRoot.'/'));
		}
		if (strpos($specialURL, '/'.$webRoot) === 0) {
			$specialURL = substr($specialURL, strlen('/'.$webRoot.'/'));
		}
		$specialURL = mb_substr($specialURL, 2, mb_strlen($specialURL, 'UTF-8')-2, 'UTF-8');
		$specialArray = explode(':', $specialURL);
		if (strlen($webRoot) > 0){
			$webRoot = '/'.$webRoot;
		}

		// For Links
		if ($specialArray[0] == 'LINKTO') {
			switch($specialArray[1]) {
				case 'PAGE':
					// Try to find nice pagename
					$niceURL = '';
					if ($siteMgr->siteExists((int)$specialArray[2])) {
						$pageMgr = sPageMgr((int)$specialArray[2]);
						$parentNodes = $pageMgr->getParents( (int)$specialArray[3] );
						foreach($parentNodes as $parentNode) {
							$niceURL = '/'.$parentNode[0]['PNAME'].$niceURL;
						}
						$pagepname = $pageMgr->getPnameByPageId((int)$specialArray[3]);
						if ($pagepname) {
							$niceURL .= '/'.$pagepname.'/';
							return $webRoot.$niceURL;
						} else {
							return '';
						}
					} else {
						return false;
					}
					break;
				case 'IMG':
				case 'DOWN':
					// Try to find nice image-/downloadname
					$fileData = explode('§§', $specialArray[2]);
					$niceURL = '';
					$filepname = sFileMgr()->getPnameByFileId((int)$fileData[0]);
					if ($specialArray[1] == 'IMG') {
						$niceURL .= '/image/'.$filepname;
					} else {
						$niceURL .= '/download/'.$filepname;
					}
					if ($fileData[1]) {
						$niceURL .= '/'.$fileData[1];
					}
					return $webRoot.$niceURL;
					break;
			}
		}

		return false;
	}

	function checkLinkInternalExternal ($link) {
		$siteMgr = new Sites();
		$webRoot = rtrim(ltrim((string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/');
		$link = urldecode($link);
		$type = 'external';
		$name = $link;
		$info = NULL;

		// Remove webroot prefix, if present
		if ( (strpos($link, $webRoot) === 0) || (strpos($link, '/'.$webRoot) === 0) ) {
			$link = substr($link, strlen($webRoot.'/'));
		}
		$link = '/'.$link;
		$link = str_replace ('//', '/', $link);

		$link = explode('/', $link);
		$sinfo = $siteMgr->getByPName($link[1]);
		if (!$sinfo || (strlen($link[2]) == 0)) {
			// Site does not exist
			$type = 'external';
		} else {
			$pageMgr = new PageMgr($sinfo['ID']);
			$pageID = $pageMgr->getPageIdByPname($link[2]);

			if (!$pageID) {
				$type = 'external';
			} else {
				$page = $pageMgr->getPage($pageID);
				$info = $page->get();

				$type = 'internal';
				$name = $info['NAME'];
			}
		}

		// Check for file
		if ($type == 'external') {
			if ( (strtolower($link[1]) == 'download') || (strtolower($link[1]) == 'image') ) {
				$fileMgr = sFileMgr();
				$fileID = $fileMgr->getFileIdByPname($link[2]);
				if ($fileID) {
					$file = $fileMgr->getFile($fileID);
					$fileInfo = $file->get();
					$type = 'file';
					$name = $fileInfo['NAME'];
					$info = array(
						'IDENTIFIER' => $fileInfo['IDENTIFIER'],
						'CODE' => $fileInfo['CODE'],
						'COLOR' => $fileInfo['COLOR'],
						'FILE_ID' => $fileID
					);
				}
			}
		}

		return array(
			'TYPE' => $type,
			'NAME' => $name,
			'INFO' => $info
		);
	}

	function createSpecialURLfromShortURL ($shortURL) {
		$siteMgr = new Sites();
		$webRoot = rtrim(ltrim((string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/');
		$shortURL = urldecode($shortURL);

		// Remove webroot prefix, if present
		if ((strlen($webRoot)) && (strpos($shortURL, $webRoot) === 0)) {
			$shortURL = substr($shortURL, strlen($webRoot.'/'));
		}
		if ((strlen($webRoot)) &&  (strpos($shortURL, '/'.$webRoot) === 0)) {
			$shortURL = substr($shortURL, strlen('/'.$webRoot.'/'));
		}

		$urlArray = explode('/', ltrim(rtrim($shortURL,'/'),'/'));
		$fileMgr = sFileMgr();

		switch(strtolower($urlArray[0])) {
			case 'image':
				$specialType = 'IMG';
			case 'download':
				if(!$specialType) $specialType = 'DOWN';
				// Check for file
				$fileID = $fileMgr->getFileIdByPname($urlArray[1]);
				if ($fileID) {
					$specialURL = '§§LINKTO:'.$specialType.':'.$fileID.'§§';
					if (count($urlArray) > 2) {
						array_shift($urlArray);
						array_shift($urlArray);
						$specialURL .= '/'.implode('/', $urlArray);
					}
					return $specialURL;
				}
				break;
			default:
				// Check for page
				$siteInfo = $siteMgr->getByPName($urlArray[0]);
				if ($siteInfo) {
					$pageMgr = new PageMgr($siteInfo['ID']);
					$lastElement = $urlArray[count($urlArray)-1];
					if (substr($lastElement, 0,1) == ':') {
						$lastElement = $urlArray[count($urlArray)-2];
						$suffix = '/'.$urlArray[count($urlArray)-1];
					}
					$pName = $lastElement;
					$pageID = $pageMgr->getPageIdByPname($pName);
					if ($pageID) {
						$specialURL = '§§LINKTO:PAGE:'.$siteInfo['ID'].':'.$pageID.'§§'.$suffix;
						return $specialURL;
					}
				}
				break;
		}

		return false;
	}

	function getSpecialURLInfo ($specialURL) {
		$specialURL = urldecode($specialURL);
		$webRoot = rtrim(ltrim((string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/');
		if (strpos($specialURL, $webRoot) === 0) {
			$specialURL = substr($specialURL, strlen($webRoot.'/'));
		}
		if (strpos($specialURL, '/'.$webRoot) === 0) {
			$specialURL = substr($specialURL, strlen('/'.$webRoot.'/'));
		}
		$specialURL = mb_substr($specialURL, 2, mb_strlen($specialURL, 'UTF-8')-4, 'UTF-8');

		$specialArray = explode(':', $specialURL);

		// For Links
		if ($specialArray[0] == 'LINKTO') {
			switch($specialArray[1]) {
				case 'PAGE':
					// Try to find nice pagename
					return array(
						'TYPE' => 'PAGE',
						'SITE' => (int)$specialArray[2],
						'ID' => (int)$specialArray[3]
					);
					break;
				case 'IMG':
				case 'DOWN':
					// Try to find nice image-/downloadname
					return array(
						'TYPE' => $specialArray[1],
						'ID' => (int)$specialArray[2]
					);
					break;
			}
		}
		return false;
	}

	function replaceSpecialURLs ($html, $absolute = false) {
		$regexp = '(?<=["\'=(])\/.*§§([^§]*)§§';
		$prefix = rtrim(ltrim((string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/');
		$prefix = '/'.$prefix;
		if ($absolute) {
			if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
				$absoluteprefix = 'https://';
			} else {
				$absoluteprefix = 'http://';
			}
			$absoluteprefix .= $_SERVER['SERVER_NAME'];
		}
		// Check and replace URLs
		if (preg_match_all("/$regexp/iU", stripslashes($html), $matches, PREG_SET_ORDER)>0) {
			foreach($matches as $match) {
				if (strpos( $match[0], $prefix) === 0) {
					$specialURL = substr($match[0], count($prefix));
					$niceURL = resolveSpecialURL($specialURL);
					if ($niceURL !== false) {
						if ($absolute) {
							$niceURL = $absoluteprefix.$niceURL;
						}
						$html = str_replace($match[0], $niceURL, $html);
					}
				}
			}
		}
		return $html;
	}

	function convertShortURLsToSpecialURLs ($html) {
		$regexpHREF = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
		$regexpIMG = "<img\s[^>]*src=(\"??)([^\" >]*?)\"(\S*)\"";
		$prefix = rtrim(ltrim((string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/');
		$prefix = '/'.$prefix;
		if (strlen($prefix) > 1) {
			$prefix .= '/';
		}

		// Check and replace hrefs
		if ((preg_match_all("/$regexpHREF/siU", stripslashes($html), $matches, PREG_SET_ORDER)>0)) {
			foreach($matches as $match) {
				if (strpos( $match[2], $prefix) === 0) {
					$shortURL = substr($match[2], count($prefix));
					$specialURL = createSpecialURLfromShortURL($shortURL);
					if ($specialURL !== false) {
						$html = str_replace($match[2], $prefix.$specialURL, $html);
					}
				}
			}
		}
		// Check and replace images
		if ((preg_match_all("/$regexpIMG/siU", stripslashes($html), $matches, PREG_SET_ORDER)>0)) {
			foreach($matches as $match) {
				if (strpos( $match[3], $prefix) === 0) {
					$shortURL = substr($match[3], count($prefix));
					$specialURL = createSpecialURLfromShortURL($shortURL);
					if ($specialURL !== false) {
						$html = str_replace($match[3], $prefix.$specialURL, $html);
					}
				}
			}
		}
		return $html;
	}

	function createPassword($length) {
		$chars = "234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$i = 0;
		$password = "";
		while ($i <= $length) {
			$password .= $chars{mt_rand(0,strlen($chars))};
			$i++;
		}
		return $password;
	}

	function prettifyUrl($url) {
		// First remove prefix
		$internalPrefix = (string)sConfig()->getVar('CONFIG/REFTRACKER/INTERNALPREFIX');

		// Check if we have an internal URL
		if (strpos($url, $internalPrefix) === 0) {
			$cleanURL = substr($url, strlen($internalPrefix));
			$urlArray = explode('/', $cleanURL);
			if ($urlArray[0] == 'download') {
				return $url;
			} else {
				$pageMgr = new PageMgr($urlArray[1]);
				$page = $pageMgr->getPage($urlArray[2]);
				return $page->getUrl();
			}
		} else {
			return $url;
		}
	}

	function getAdditionalFormfieldData ( &$controlFormfields ) {
		$entrymaskMgr = new Entrymasks();
		$tagMgr = new Tags();
		$filetypeMgr = new Filetypes();
		for ($w = 0; $w < count($controlFormfields); $w++) {

			$controlFormfields[$w]['LVALUES'] = NULL;
			$controlFormfields[$w]['DISPLAYNAME'] = NULL;

			// Date & Datetime
			if (($controlFormfields[$w]['TYPE'] == 'DATE')||($controlFormfields[$w]['TYPE'] == 'DATETIME')) {
				if ($controlFormfields[$w]['VALUE']) {
					$controlFormfields[$w]['VALUE'] = TStoLocalTS($controlFormfields[$w]['VALUE']);
				}
			}

			// Link
			if ($controlFormfields[$w]['TYPE'] == 'LINK') {
				$special_url = resolveSpecialURL($controlFormfields[$w]['URL']);
				if ($special_url !== false) {
					$special_url_info = getSpecialURLInfo($controlFormfields[$w]['VALUE01']);
					if ( ($special_url_info['TYPE'] == 'IMG') || ($special_url_info['TYPE'] == 'DOWN') ) {
						$controlFormfields[$w]['IS_FILE'] = true;
						$file = sFileMgr()->getFile($special_url_info['ID']);
						$link_fileinfo = $file->get();
						$controlFormfields[$w]['DISPLAYNAME'] = $link_fileinfo['NAME'];
						$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
						$controlFormfields[$w]['IDENTIFIER'] = $link_filetype['IDENTIFIER'];
						$controlFormfields[$w]['TYPECODE'] = $link_filetype['CODE'];
						$controlFormfields[$w]['COLOR'] = $link_filetype['COLOR'];
						$controlFormfields[$w]['FILE_ID'] = $special_url_info['ID'];
					} else {
						$pageMgr = new PageMgr($special_url_info['SITE']);
						$page = $pageMgr->getPage($special_url_info['ID']);
						$link_pageInfo = $page->get();
						$controlFormfields[$w]['DISPLAYNAME'] = $link_pageInfo['NAME'];
						$controlFormfields[$w]['IS_INTERNAL'] = true;
					}
				} else if (preg_match_all(sApp()->URLRegEx1, $controlFormfields[$w]['VALUE01'], $internal) > 0) {
					if ($internal[2][0]=='download') {
						$controlFormfields[$w]['IS_FILE'] = true;
						$link_file = str_replace('/','',$internal[3][0]);
						$file = sFileMgr()->getFile($link_file);
						$link_fileinfo = $file->get();
						$controlFormfields[$w]['DISPLAYNAME'] = $link_fileinfo['NAME'];
						$link_filetype = $filetypeMgr->get($link_fileinfo['FILETYPE']);
						$controlFormfields[$w]['IDENTIFIER'] = $link_filetype['IDENTIFIER'];
						$controlFormfields[$w]['TYPECODE'] = $link_filetype['CODE'];
						$controlFormfields[$w]['COLOR'] = $link_filetype['COLOR'];
						$controlFormfields[$w]['FILE_ID'] = $link_file;
					} else {
						$link_site = $internal[3][0];
						$link_page = str_replace('/','',$internal[5][0]);
						$pageMgr = new PageMgr($link_site);
						$page = $pageMgr->getPage($link_page);
						$link_pageInfo = $page->get();
						$controlFormfields[$w]['DISPLAYNAME'] = $link_pageInfo['NAME'];
						$controlFormfields[$w]['IS_INTERNAL'] = true;
					}
				} elseif (substr($controlFormfields[$w]['URL'], 0, 7)=='mailto:') {
					$controlFormfields[$w]['IS_EMAIL'] = true;
				} else {
					$linkInfo = checkLinkInternalExternal( $controlFormfields[$w]['URL'] );
					switch($linkInfo['TYPE']) {
						case 'external':
							$controlFormfields[$w]['DISPLAYNAME'] = $controlFormfields[$w]['URL'];
							break;
						case 'internal':
							$controlFormfields[$w]['DISPLAYNAME'] = $linkInfo['NAME'];
							$controlFormfields[$w]['IS_INTERNAL'] = true;
							break;
						case 'file':
							$controlFormfields[$w]['IS_FILE'] = true;
							$controlFormfields[$w]['DISPLAYNAME'] = $linkInfo['NAME'];
							$controlFormfields[$w]['IDENTIFIER'] = $linkInfo['INFO']['IDENTIFIER'];
							$controlFormfields[$w]['TYPECODE'] = $linkInfo['INFO']['CODE'];
							$controlFormfields[$w]['COLOR'] = $linkInfo['INFO']['COLOR'];
							$controlFormfields[$w]['FILE_ID'] = $linkInfo['INFO']['FILE_ID'];
							break;
					}
				}
			}
			// File
			if ($controlFormfields[$w]['TYPE'] == 'FILE') {
				if (trim($controlFormfields[$w]['FILE_ID'])) {
					$file = sFileMgr()->getFile($controlFormfields[$w]['FILE_ID']);
					if ($file) {
						$fileInfo = $file->get();
						$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
						$controlFormfields[$w]['IDENTIFIER'] = $fileInfo['IDENTIFIER'];
						$controlFormfields[$w]['TYPECODE'] = $fileInfo['CODE'];
						$controlFormfields[$w]['COLOR'] = $fileInfo['COLOR'];
					}
				}
			}
			// File
			if ($controlFormfields[$w]['TYPE'] == 'FILEFOLDER') {
				if (trim($controlFormfields[$w]['FILE_ID'])) {
					$file = sFileMgr()->getFile($controlFormfields[$w]['FILE_ID']);
					if ($file) {
						$fileInfo = $file->get();
						$controlFormfields[$w]['DISPLAYNAME'] = $fileInfo['NAME'];
					}
				}
			}
			// Contentblock
			if ($controlFormfields[$w]['TYPE'] == 'CO') {
				if (trim($controlFormfields[$w]['CBLOCK_ID'])) {
					$cb = sCblockMgr()->getCblock($controlFormfields[$w]['CBLOCK_ID']);
					$info = $cb->get();
					$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
				}
			}
			// Tag
			if ($controlFormfields[$w]['TYPE'] == 'TAG') {
				if (trim($controlFormfields[$w]['TAG_ID'])) {
					$info = $tagMgr->get($controlFormfields[$w]['TAG_ID']);
					$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
				}
			}
			// Page
			if ($controlFormfields[$w]['TYPE'] == 'PAGE') {
				if (trim($controlFormfields[$w]['SITE_ID']) && trim($controlFormfields[$w]['PAGE_ID'])) {
					$tmpPageMgr = new PageMgr($controlFormfields[$w]['SITE_ID']);
					$tmpPage = $tmpPageMgr->getPage($controlFormfields[$w]['PAGE_ID']);
					$info = $tmpPage->get();
					$info['RWRITE'] = $tmpPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $controlFormfields[$w]['PAGE_ID'], "RWRITE");
					$info['RDELETE'] = $tmpPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $controlFormfields[$w]['PAGE_ID'], "RDELETE");
					$iconData = getIconForPage($info);
					$controlFormfields[$w]['ICON'] = $iconData['iconclass'];
					$controlFormfields[$w]['STYLE'] = $iconData['style'];
					$controlFormfields[$w]['DISPLAYNAME'] = $info['NAME'];
				}
			}
			// List
			if ($controlFormfields[$w]['TYPE'] == 'LIST') {
				if (trim($controlFormfields[$w]['ENTRYMASKFORMFIELD'])) {
					$controlFormfields[$w]['LIST_VALUES'] = $entrymaskMgr->getListValuesByLinkID( $controlFormfields[$w]['ENTRYMASKFORMFIELD'] );
				}
			}
		}
	}

	function generate_thumbnail ($filename, $imgsize, $prefix, $maxwidth = 0, $maxheight = 0, $filedir, $datei, $constrain = false) {
		switch ($imgsize[2]) {
			case 1:	 // gif
				$img_in = imagecreatefromgif($datei);
				break;
			case 2:	 // jpg
				$img_in = imagecreatefromjpeg($datei);
				break;
			case 3:	 // png
				$img_in = imagecreatefrompng($datei);
				break;
			default:
				return 0;
		}

		$i_width = $imgsize[0];
		$i_height = $imgsize[1];
		$scale_X = $i_width / $i_height;
		$scale_Y = $i_height / $i_width;

		if ($constrain == true) {
			if ($scale_X > 1) {
				// querformat
				$out_w = $maxwidth;
				$out_h = $out_w * $scale_Y;
				if ($out_h > $maxheight) {
					$out_h = $maxheight;
					$out_w = $i_width * ($out_h / $i_height);
				}
			} else { // hochformat
				$out_h = $maxheight;
				$out_w = $out_h * $scale_X;
				if ($out_w > $maxwidth) {
					$out_w = $maxwidth;
					$out_h = $i_height * ($out_w / $i_width);
				}
			}
		} else {
			if (($maxheight > 0) || ($maxwidth > 0)) {
				if (($maxwidth > 0) and ($maxheight > 0)) {
					$out_w = $maxwidth;
					$out_h = $maxheight;
				} elseif (($maxwidth > 0) and ($maxheight == 0)) {
					$out_w = $maxwidth;
					$out_h = $imgsize[1] * ($maxwidth / $imgsize[0]);
				} elseif (($maxwidth == 0) and ($maxheight > 0)) {
					$out_w = $imgsize[0] * ($maxheight / $imgsize[1]);
					$out_h = $maxheight;
				} else {
					$out_w = $maxwidth;
					$out_h = $maxheight;
				}
			}
		}

		$out_w = floor($out_w);
		$out_h = floor($out_h);
		$img_out = imagecreatetruecolor($out_w, $out_h);
		$result = imagecopyresampled($img_out, $img_in, 0, 0, 0, 0, $out_w, $out_h, $imgsize[0], $imgsize[1]);
		$ff = getrealpath($filedir.$prefix.$filename);
		$result = imagejpeg($img_out, $ff, 100);

		if(is_resource($img_in)) {
			imagedestroy($img_in);
		}
		if(is_resource($img_out)) {
			imagedestroy($img_out);
		}
	}

	function stripCDATA ($string) {
		preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $string, $matches);
		return str_replace($matches[0], $matches[1], $string);
	}

	if(!function_exists('image_type_to_extension')) {
		function image_type_to_extension($imagetype, $include_dot=true) {
			if(empty($imagetype)) return false;
			$dot = $include_dot ? $dot.'' : '';
			switch($imagetype) {
				case IMAGETYPE_GIF:		return $dot.'gif';
				case IMAGETYPE_JPEG:	return $dot.'jpg';
				case IMAGETYPE_PNG:		return $dot.'png';
				case IMAGETYPE_SWF:		return $dot.'swf';
				case IMAGETYPE_PSD:		return $dot.'psd';
				case IMAGETYPE_WBMP:	return $dot.'wbmp';
				case IMAGETYPE_XBM:		return $dot.'xbm';
				case IMAGETYPE_TIFF_II:	return $dot.'tiff';
				case IMAGETYPE_TIFF_MM:	return $dot.'tiff';
				case IMAGETYPE_IFF:		return $dot.'aiff';
				case IMAGETYPE_JB2:		return $dot.'jb2';
				case IMAGETYPE_JPC:		return $dot.'jpc';
				case IMAGETYPE_JP2:		return $dot.'jp2';
				case IMAGETYPE_JPX:		return $dot.'jpf';
				case IMAGETYPE_SWC:		return $dot.'swc';
				default:				return false;
			}
		}
	}

	function GetContainsOperators($op) {
		$CompareOperators = array("is" => "=", "is_not" => "!=", "is_bigger" => ">=", "is_smaller" => "<=");
		return $CompareOperators[$op];
	}

	function buildBackendFilter($callback, &$frontendfilter, &$select, &$from, &$where, &$limit, &$order, &$having) {
		$fl = array();
		if(count($frontendfilter) > 0) {
			foreach($frontendfilter as &$monster) {
				$filterOperator = $monster["OPERATOR"];
				$filterType = $monster["TYPE"];
				$filterValue1 = $monster["VALUE"];
				$filterValue2 = $monster["VALUE2"];

				$callback($fl, $filterType, $filterOperator, $filterValue1, $filterValue2);
			}
		}

		if (count($fl["SELECT"]) > 0) {
			foreach($fl["SELECT"] as &$s) {
				$select .= ", ".$s;
			}
		}

		if (count($fl["TABLE"]) > 0) {
			foreach($fl["TABLE"] as &$f) {
				$from .= ", ".$f;
			}
		}

		if (count($fl["WHERE"]) > 0) {
			foreach($fl["WHERE"] as &$w) {
				$where .= " AND ".$w;
			}
		}

		if (count($fl["LIMIT"]) > 0) {
			$limit .= $fl["LIMIT"][0];
		}

		if (count($fl["ORDER"]) > 0) {
			$order .= $fl["ORDER"][0];
		}

		if (count($fl["HAVING"]) > 0) {
			$having .= $fl["HAVING"][0];
		}

	}

	function downloadFromURL($sourceURL, $targetFile) {
		$rh = fopen($sourceURL, 'rb');
		$wh = fopen($targetFile, 'wb');
		if ($rh===false || $wh===false) {
			// error reading or opening file
			return false;
		}
		while (!feof($rh)) {
			if (fwrite($wh, fread($rh, 1024)) === FALSE) {
				// 'Download error: Cannot write to file ('.$targetFile.')';
				return false;
			}
		}
		fclose($rh);
		fclose($wh);
		// No error
		return true;
	}

	function getStringFromURL($sourceURL, $timeout) {
		$outputString = '';

		if ($timeout) {
			$oldTimeout = ini_set('default_socket_timeout', $timeout);
		}

		$rh = fopen($sourceURL, 'rb');
		if ($rh===false) {
			// error reading file
			return false;
		}
		while (!feof($rh)) {
			$outputString .= fread($rh, 1024);
		}

		if ($timeout) {
			ini_set('default_socket_timeout', $oldTimeout);
			stream_set_timeout($rh, $timeout);
			stream_set_blocking($rh, 0);
		}

		fclose($rh);
		// No error
		return $outputString;
	}

	function prettifyVersionString($versionString) {
		$versionStringArray = explode('.', $versionString);
		for($i=4;$i>=2;$i--) {
			if ($versionStringArray[$i] === '0') {
				array_pop($versionStringArray);
			} else {
				break;
			}
		}
		return implode('.', $versionStringArray);
	}

	/**
	 *
	 * Extension for SimpleXMLElement
	 * @author Alexandre FERAUD
	 *
	 */
	class ExSimpleXMLElement extends SimpleXMLElement {
		/**
		 * Add CDATA text in a node
		 * @param string $cdata_text The CDATA value  to add
		 */
		private function addCData($cdata_text) {
			$node= dom_import_simplexml($this);
			$no = $node->ownerDocument;
			$node->appendChild($no->createCDATASection($cdata_text));
		}

		/**
		 * Create a child with CDATA value
		 * @param string $name The name of the child element to add.
		 * @param string $cdata_text The CDATA value of the child element.
		 */
		public function addChildCData($name, $cdata_text) {
			$child = $this->addChild($name);
			$child->addCData($cdata_text);
		}

		/**
		 * Add SimpleXMLElement code into a SimpleXMLElement
		 * @param SimpleXMLElement $append
		 */
		public function appendXML($append) {
			if ($append) {
				if (strlen(trim((string) $append))==0) {
					$xml = $this->addChild($append->getName());
					foreach($append->children() as $child) {
						$xml->appendXML($child);
					}
				} else {
					$xml = $this->addChild($append->getName(), (string) $append);
				}
				foreach($append->attributes() as $n => $v) {
					$xml->addAttribute($n, $v);
				}
			}
		}
	}

?>