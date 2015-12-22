<?php

	// Includes
	include_once "error.php";

	// Set frontend timezone
	date_default_timezone_set($this->frontendTimezone);

	// Normalize and remove webroot-prefix
	$webroot_path_string = implode('/', sApp()->webroot);
	$request_path_string = getRequestPathString(sApp()->request->path);
	$request_path = getRequestPathArray($request_path_string);
	$psite = $request_path[1];
	$ppage = (int)$request_path[2]; 
	$action = sYDB()->escape_string($this->request->parameters['action']);

	// Default
	if (strlen($psite) < 1) {
		if (strlen($request_path_string) === 0) {
			// Webroot was requested, use first site and first page from that site
			$sites = sSites()->getList();
			$siteID = $sites[0]['ID'];
			if ($siteID) {
				$pageMgr = new PageMgr($siteID);
				$pagesList = $pageMgr->getTree($pageMgr->tree->getRoot(), 2);
				foreach($pagesList as $currPage) {
					if (($currPage['LEVEL'] == 2) && ($pageID == 0)) {
						$pageID = $currPage['ID'];
						$pagePName = $currPage['PNAME'];
					}
				}
				$newUrl = $webroot_path_string.'/'.$sites[0]['PNAME'].'/'.$pagePName.'/';

				// Throw status 301 and redirect
				$header = $_SERVER['SERVER_PROTOCOL'].' 301 Moved Permanently';
				header($header);
				header('Location: '.$newUrl);
				die();
			}
		} else {
			// Throw status 404
			throwErrorPage('404');
		}
	} elseif (strlen($psite) > 1) {					
		$sinfo = sSites()->getByPName($psite);
		if ((int)$psite > 0) {
			$siteID = (int)$psite;
		} else {
			$siteID = $sinfo['ID'];
		}
		if ($siteID > 0) {
			$pageMgr = new PageMgr($siteID);
			$pname = $request_path[count($request_path)-1];
			if ((strlen($pname) >0) && (count($request_path) > 2) && (strlen($request_path[count($request_path)-1]) > 0)) {
				$pageID = $pageMgr->getPageIdByPname($pname);
			}

			if (($pageID < 1) && ($sinfo['ID'] > 0) && (count($request_path)==2)) {
				// Use first page in that site
				$pagesList = $pageMgr->getTree($pageMgr->tree->getRoot(), 2);
				foreach($pagesList as $currPage) {
					$tmpPage = $pageMgr->getPage($currPage['ID']);
					if ($tmpPage) {
						$pageInfo = $tmpPage->get();
						if ($pageInfo && ($pageInfo['DELETED'] == 0) && ($currPage['LEVEL'] == 2) && ($pageID == 0)) {
							$pageID = $currPage['ID'];
							$pagePName = $currPage['PNAME'];
						}
					}
				}
				if ($pagePName) {
					// Throw status 301 and redirect
					$header = $_SERVER['SERVER_PROTOCOL'].' 301 Moved Permanently';
					$qs = "";
					if ($_SERVER['QUERY_STRING'] != "") $qs = "?".$_SERVER['QUERY_STRING'];
					header($header);
					header('Location: '.$webroot_path_string.'/'.$sinfo['PNAME'].'/'.$pagePName.'/'.$qs);
					die();
				} else {
					// Throw status 403
					sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
					$pageMgr = new PageMgr($siteID);
					$pagesList = $pageMgr->getTree($pageMgr->tree->getRoot(), 2);
					foreach($pagesList as $currPage) {
						$tmpPage = $pageMgr->getPage($currPage['ID']);
						if ($tmpPage) {
							$pageInfo = $tmpPage->get();
							if ($pageInfo && ($pageInfo['DELETED'] == 0) && ($currPage['LEVEL'] == 2) && ($pageID == 0)) {
								$pageID = $currPage['ID'];
							}
						}
					}

					$tmpPage = $pageMgr->getPublishedPage($pageID);
					if ($tmpPage) $tmpPageinfo = $tmpPage->get();

					// Call callback
					$pageMgr->callExtensionHook("onAccessDenied", $siteID, $pageID, $tmpPageinfo['VERSION'], array("FILTER" => $filter, "CONTENTAREAS" => &$inhalte));

					sUserMgr()->unimpersonate();
					// Throw status 403
					throwErrorPage('403');
				}
			} elseif (($pageID > 0) && ($sinfo['ID'] > 0)) {
				$pageID;
			} else {
				$pageID = (int)$ppage;
			}
		} else {
			// Throw status 404
			throwErrorPage('404');
		}
	} else {
		$siteID = (int)$psite;
		$pageID = $ppage;
	}
	if ($pageID < 1) {
		// Throw status 404
		throwErrorPage('404');
	}
	$pageMgr = new PageMgr($siteID);
	$version = $this->request->parameters['version'];

	// content manager browse cookie management
	if ($version == "live") {
		// Delete Cookie & reset version
		$this->session->setCookie('version', '');
	}
	$versionCookie = $this->session->getCookie('version');

	if (($version == 'working') || (($versionCookie == 'working')&&($version != 'live'))) {
		if (($version == 'working')||(!isset($version))) {
			$version = 'null';
		}
		// Set Cookie
		$this->session->setCookie('version', 'working');
		$this->displaymode = "working";
	} else {
		// Delete Cookie
        $this->session->removeCookie("version");
		if ($version == "live") unset($version);
		$this->displaymode = "live";
	}
	$page = $pageMgr->getPage($pageID, $version);

	// check if no permissions
	if (!$page) {
		sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
		$tmpPageMgr = new PageMgr($siteID);
		$tmpPage = $tmpPageMgr->getPublishedPage($pageID);
		if (!$tmpPage) {
			throwErrorPage('404');
		}
		$tmpPageinfo = $tmpPage->get();
		// Call callback
		$pageMgr->callExtensionHook("onAccessDenied", $siteID, $pageID, $tmpPageinfo['VERSION'], array("FILTER" => $filter, "CONTENTAREAS" => &$inhalte));

		sUserMgr()->unimpersonate();

		// Throw status 403
		throwErrorPage('403');
	}

	if ((int)$siteInfo['FAVICON'] > 0) {
		$sinfo['FAVICON_URL'] = $webroot_path_string.'/image/'.$sinfo['FAVICON'];
	} else {
		$sinfo['FAVICON_URL'] = '';
	}

	// check version
	if ((strlen($version) > 0)) {
		$filter = "";
		$tmpUser = new User(sUserMgr()->getCurrentUserID());
		$tmpUserInfo = $tmpUser->get();
		$backendAllowed = $tmpUser->checkPermission('RBACKEND');
		if ((sUserMgr()->getCurrentUserID() !== sUserMgr()->getAnonymousID()) && $backendAllowed) {
			if ($version == "null") {
				$version = $page->getLatestVersion();
			}
		} else {
			$filter = "PUBLISHED";
			$version = $page->getPublishedVersion(true);
		}
	} else {
		$filter = "PUBLISHED";
		$version = $page->getPublishedVersion(true);
	}
	$page = $pageMgr->getPage($pageID, $version);

	// login
	$yg_login_referrer = $this->request->parameters['yg_login_referrer'];
	$smarty->assign("yg_login_referrer", $yg_login_referrer);
	if ($action == "yg_login") {
		$username = $this->request->parameters['username'];
		$password = $this->request->parameters['password'];
		$userid = sUserMgr()->validate($username, $password);
		if ($userid === false) {
			$pageMgr->callExtensionHook("onLoginFailed", $siteID, $pageID, $version, array("FILTER" => $filter, "CONTENTAREAS" => &$inhalte));
		} else {
			$this->session->setPSessionVar("username", $username);
			$this->session->setPSessionVar("password", $password);
			$yg_login_referrer = urldecode($yg_login_referrer);
			$targetpage = $yg_login_referrer;
			$pageMgr->callExtensionHook("onLoginSuccessful", $siteID, $pageID, $version, array("FILTER" => $filter, "CONTENTAREAS" => &$inhalte));

			if ((strrpos($yg_login_referrer, "http://") === false) && (strrpos($yg_login_referrer, "https://") === false)) {
				$target = "http://".$this->request->http_host.$webroot_path_string."/".$sinfo["PNAME"]."/".$targetpage."/";
			} else {
				$target = $yg_login_referrer;
			}

			for ($modulesi = 0; $modulesi <= count($this->modules); $modulesi++) {
				if (strlen($this->modules[$modulesi]["logincode"]) > 0) {
					require_once($this->approot.$this->moduledir.$this->modules[$modulesi]["id"]."/".$this->modules[$modulesi]["logincode"]);
				}
			}
			http_redirect($target);
		}
	}
	$username = $this->session->getSessionVar("username");
	$password = $this->session->getSessionVar("password");
	$userID = sUserMgr()->validate($username, $password);

	if ($userID < 1) {
		$userID = sUserMgr()->getAnonymousID();
	}

	// Page Properties
	$pageInfo = $page->get();
	$pageProperties = $page->properties->get();
	$pageInfo = array_merge($pageProperties, $pageInfo);

	$pageInfo["URL"] = $page->getUrl();

	// 404 if in trash
	if ($pageInfo['DELETED'] == 1) {
		// Throw status 404
		throwErrorPage('404');
	}

	// Access Control
	$user = new User($userID);
	$userroles = $user->getUsergroups();

	// Map untitled parameters into app::request object
	$fullpath = implode('/', sApp()->request->path);
	if ($colonPos = strpos($fullpath, ':')) {
		$untitledParams = substr($fullpath, $colonPos+1);
		$untitledparams = explode(':', $untitledParams);

		foreach ($untitledparams as $key => $value) {
			if (is_null($value) || $value=='') {
				unset($untitledparams[$key]);
			}
		}

		$untitledparams = array_values($untitledparams);
		sApp()->request->untitled_parameters = $untitledparams;
	}

	// Cache Management
	if (($_SERVER["CACHE_BROWSER"] == 1)) {
		$cachetimes = array();
		$cachetimes[] = $cms->pages->cache->cached_object["timestamp"];
		$cachetimes[] = sCblockMgr()->cache->cached_object["timestamp"];
		$cachetimes[] = $cms->pages->tags->cache->cached_object["timestamp"];
		$cachetimes[] = $cms->fileobjects->cache->cached_object["timestamp"];
		$cachetimes[] = $cms->users->cache->cached_object["timestamp"];
		for ($modulesi = 0; $modulesi <= count($this->modules); $modulesi++) {
			if (strlen($this->modules[$modulesi]["frontendcode"]) > 0) {
				$modulecache = new \framework\Cache("", $this->modules[$modulesi]["id"].$this->_db->databaseName, 0);
				$cachetimes[] = $modulecache->cached_object["timestamp"];
			}
		}
		$lmt = $cachetimes[count($cachetimes)-1];

		$browserversion = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);

		header('Last-Modified: ' .gmdate('D, d M Y H:i:s', $lmt). ' GMT' );
		header('Pragma: no-cache');
		header('Cache-Control: cache, must-revalidate');
		if ($lmt <= $browserversion) {
			//  header('Cache-Control: post-check=0, pre-check=1, max-age=1', FALSE);
			header('Cache-Control: max-age=1');
			$last_modified = substr(gmdate('r', $lmt), 0, -5).'GMT';
			$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) :
			false;
			$nm = true;
			if (!$if_modified_since && !$if_none_match) {
				$nm = false;
			}
			if ($if_modified_since && $if_modified_since != $last_modified) {
				$nm = false;
			}
			if (($nm)) {
				header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
				exit;
			}
		}
	}

	// render page if active, otherwise 404
	if (($pageInfo["ACTIVE"] > 0) && ($version !== NULL)) {
		renderPage($page, $version, $pageInfo, $pageMgr, $sinfo);
	} else {
		throwErrorPage('404');
	}

?>