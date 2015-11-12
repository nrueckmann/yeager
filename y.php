<?php

	/***************************************************************************/
	/*** yeager CMS                                                          ***/
	/***************************************************************************/

	define('YEAGER_REVISION', '$Id:$');
	//define('YEAGER_DATE', '$Date$');

	// Sets maximum execution time to 1 hour (3600 seconds)
	ini_set("max_execution_time", "3600");
	ini_set('magic_quotes_gpc', 0);
	ini_set('magic_quotes_runtime', 0);

	require("framework.php");


/// @cond DEV

	class Yeager extends \framework\Application {

		public function hitCache() {

			$this->base = $this->request->script_name."/".strtolower($this->applicationname);
			$this->cached = false;
            $this->cache_config = new \framework\Config("config/cache.xml.php");
            Singleton::register("cache_config", $this->cache_config);


            $reverse_proxy = Singleton::cache_config()->getVar("CONFIG/REVERSE_PROXY/ENABLED");
            if ($reverse_proxy == "true") {
                $reverse_proxy_purge = Singleton::cache_config()->getVar("CONFIG/REVERSE_PROXY/PURGE_COMMAND");
            } else {
                $reverse_proxy_purge = "";
            }

			Singleton::register("FC", new \framework\Cache("page",0,$this->tmpdir,$reverse_proxy_purge));
			if ($this->session->getCookie('version') == 'working') {
				return false;
			}
			if ($this->frontendMode == "true") {
				$frontendCache = (string)$this->config->getVar('CONFIG/CACHE/FRONTEND');
				if ($frontendCache == "true") {
					//\framework\import("org.yeager.framework.cache.controller");
					$cacheBucket = (string)$this->config->getVar('CONFIG/PAGES/'.strtoupper($this->page).'/CACHE');
					if ($cacheBucket != "") {
						$requestHash = md5($_SERVER['REQUEST_URI']);
						if (strstr($_SERVER['REQUEST_URI'], "nocache=true")) {
							return;
						}
						if (strstr($_SERVER['REQUEST_URI'], "version=working")) {
							return;
						}
						if (implode("",$_POST) != "") {
							return;
						}
						$pattern = $this->cache_config->getVars("CONFIG/".strtoupper($cacheBucket)."/URIS");
						foreach ($pattern as $p) {
							if (preg_match($p["URI"], $_SERVER['REQUEST_URI'])) {
								$ttl = $p["TTL"];
								$key = $p["KEY"];
								break;
							}
						}
						if (($ttl == 0) || (strtolower($key) == 'none')) return; // zero ttl means no-cache

						if ($key == "UserGroup") {
							$roles = $this->session->getSessionVar("userroles");
							$roleHash = "";
							foreach ($roles as $r) {
								$roleHash .= $r["ID"]."-";
							}
							
						} elseif ($key == "ProtoUserGroup") {
							$roles = $this->session->getSessionVar("userroles");
							$roleHash = $this->request->prefix."-";
							foreach ($roles as $r) {
								$roleHash .= $r["ID"]."-";
							}
						} else {
							$userID = $this->session->getCookie("yg-userid");
							if ($userID < 1) {
								$userID = (int)$this->config->getVar("CONFIG/SYSTEMUSERS/ANONUSERID");
							}
							$roleHash = $userID; // per user cache
						}
						$cacheId = $requestHash."-".$roleHash;
						Singleton::FC()->setCacheId($cacheId);
						Singleton::FC()->setTTL($ttl);
						$cached_OB = Singleton::FC()->getvalue("output");
						$this->cached = true;
						if ($cached_OB != false) {
							header("X-YG-CACHEHIT: true");
                            header("X-YG-CACHEKEY: $key");
							ob_end_clean();
							ob_start();
							echo $cached_OB;
							return true;
						}
					}
				}
			}
		}

		public function go () {

			header("Content-Type: text/html; charset=UTF-8");

			// Import Libraries
			\framework\import("org.yeager.framework.tools.password");

			\framework\import("org.yeager.ui.common");
			\framework\import("org.yeager.ui.koala");
			\framework\import("org.yeager.ui.icons");

			\framework\import("org.yeager.framework.tools.password");
			\framework\import("org.yeager.framework.tools.http.redirect");
			\framework\import("org.yeager.framework.tools.http.byteserve");

			\framework\import("org.yeager.core.versionable");
			\framework\import("org.yeager.core.tree");
			\framework\import("org.yeager.core.permissions");
			\framework\import("org.yeager.core.privileges");
			\framework\import("org.yeager.core.propertysettings");
			\framework\import("org.yeager.core.properties");
			\framework\import("org.yeager.core.history");
			\framework\import("org.yeager.core.jsqueue");
			\framework\import("org.yeager.core.tags");
			\framework\import("org.yeager.core.comments");
			\framework\import("org.yeager.core.cblock");
			\framework\import("org.yeager.core.cblockmgr");
			\framework\import("org.yeager.core.entrymasks");
			\framework\import("org.yeager.core.page");
			\framework\import("org.yeager.core.pagemgr");
			\framework\import("org.yeager.core.file");
			\framework\import("org.yeager.core.filemgr");
			\framework\import("org.yeager.core.filetypes");
			\framework\import("org.yeager.core.views");
			\framework\import("org.yeager.core.mailing");
			\framework\import("org.yeager.core.mailingmgr");
			\framework\import("org.yeager.core.templates");
			\framework\import("org.yeager.core.usergroups");
			\framework\import("org.yeager.core.sites");
			\framework\import("org.yeager.core.languages");
			\framework\import("org.yeager.core.reftracker");
			\framework\import("org.yeager.core.scheduler");
			\framework\import("org.yeager.core.extensionmgr");
			\framework\import("org.yeager.core.extensions");
			\framework\import("org.yeager.core.fileprocessor");
			\framework\import("org.yeager.core.cblockprocessor");
			\framework\import("org.yeager.core.emailprocessor");
			\framework\import("org.yeager.core.pageprocessor");
			\framework\import("org.yeager.core.user");
			\framework\import("org.yeager.core.usermgr");
			\framework\import("org.yeager.core.tree");
			\framework\import('org.yeager.core.updater');
			\framework\import('org.yeager.core.archive');

			// Set UTF8 for DB
			Singleton::YDB()->Execute("SET NAMES 'utf8';");

			// Set ADODB-Fetchmode to ADODB_FETCH_ASSOC
			Singleton::YDB()->SetFetchMode( ADODB_FETCH_ASSOC );

			// Create instance of Koala class
			$koala = new Koala($this->yeager);

			$username = $this->session->getSessionVar("username");
			$password =	$this->session->getSessionVar("password");

			Singleton::register("session", $this->session);
			Singleton::register("request", $this->request);
			Singleton::register("config", $this->config);

			Singleton::register("UserMgr", new UserMgr());
			Singleton::register("guiUS", $this->request->parameters['us'] );
			Singleton::register("guiLH", $this->request->parameters['lh'] );

			// Get frontend timezone
			$this->frontendTimezone = (string)Singleton::config()->getVar('CONFIG/TIMEZONES/FRONTEND');
			if (!$this->frontendTimezone) {
				$this->frontendTimezone = 'Europe/Berlin';
			}

			$userID = Singleton::UserMgr()->validate($username, $password);

			Singleton::register("Usergroups", new Usergroups() );

			if (!$userID) {
				$userID = Singleton::UserMgr()->getAnonymousID();
				$this->authenticated = false;
			} else{
				$this->authenticated = true;
                if ($userID <> Singleton::UserMgr()->getAnonymousID()) {
                    $this->session->setPSessionVar("username", $username);
                    $this->session->setPSessionVar("password", $password);
				    $this->session->setPSessionVar("userid", $userID);  
				    $this->session->setPSessionVar("isvalidated", true);
                }

				if ($this->session->getSessionVar('keepLoggedIn')) {
					$this->session->cookie_time = time()+60*60*24*365;
				} else {
					$cookie_time = (int)Singleton::config()->getVar("CONFIG/SESSION/COOKIES/TIME");
					$this->session->cookie_time = $cookie_time;
				}
			}

			// write roles to sessions for cachekey
			$user = new User($userID);
			$roles = $user->getUsergroupIDs();
			if ($userID <> Singleton::UserMgr()->getAnonymousID()) {
				$this->session->setPSessionVar("userroles", $roles);
                $this->session->setPSessionVar("userid", $userID);
                $this->session->setCookie("yg-userid", $userID);
				$roleHash = "";
				foreach ($roles as $r) {
					$roleHash .= $r["ID"]."x";
				}
                $this->session->setCookie("yg-userroles", $roleHash);
            } elseif ($_COOKIE['yg-userid']) { // remove cookie if set
                $this->session->removeCookie("yg-userid");
                $this->session->removeCookie("yg-userroles");
            }

			$backendAllowed = $user->checkPermission('RBACKEND');
			if ( ((!$this->authenticated) || (!$backendAllowed)) &&
				 ($this->frontendMode != 'true') ) {
				if ( ($this->page != 'responder') &&
					 (($this->request->parameters['handler'] != 'userLogin') ||
					  ($this->request->parameters['handler'] != 'recoverLogin') ||
					  ($this->request->parameters['handler'] != 'setNewPassword')) ) {
					$header = $_SERVER['SERVER_PROTOCOL'].' 403 Forbidden';
					header($header);
					echo $header;
					die();
				}
			}

			$user_timezone = $user->properties->getValue('TIMEZONE');
			$timezoneAbbreviations = timezone_abbreviations_list();
			foreach($timezoneAbbreviations as $timezoneAbbreviations_item) {
				foreach($timezoneAbbreviations_item as $timezone_item) {
					if ($timezone_item['timezone_id'] == $user_timezone) {
						global $tz;
						$tz = $timezone_item;
					}
				}
			}

			Singleton::register("Tags", new Tags());
			Singleton::register("cbMgr", new CblockMgr());
			Singleton::register("fileMgr", new FileMgr());
			Singleton::register("sites", new Sites());
			Singleton::register("templates", new Templates());
			Singleton::register("entrymasks", new Entrymasks());
			Singleton::register("mailingMgr", new MailingMgr());
			Singleton::register("comments", new Comments());
			Singleton::register("filetypes", new Filetypes());
			Singleton::register("views", new Views());
			Singleton::register("app", $this);
			Singleton::register("koala", $koala);

			$versioninfo = new Updater();
			$versionp = $versioninfo->current_version_string;

			$this->yeager_version = $versionp;
			$this->yeager_revision = substr(YEAGER_REVISION, 4, 7);
			//$this->yeager_date = YEAGER_DATE;

			// get page to display
			if (empty($this->page)) {
				$this->page = "default";
			}

			if (empty($this->action)) {
				$this->action = $this->request->parameters['action'];
			}

			$this->base = $this->request->script_name."/".strtolower($this->applicationname);

			/* yeager */
			$this->docpath = Singleton::config()->getVar('CONFIG/DIRECTORIES/DOCPATH');
			$this->docabsolut = $this->baseabsolut = $this->request->prefix."://".$this->request->http_host.$this->docpath;
			$this->imgpath = $this->request->prefix."://".$this->request->http_host.$this->docpath."ui/img/";
			$this->doc = $this->app_httproot;


			$this->sid = $this->session->id;
			$this->sidparam = "sid=".$this->sid;

			// Regular Expressions for URL parsing
			$internalprefix = str_replace('/', '\/', Singleton::config()->getVar('CONFIG/REFTRACKER/INTERNALPREFIX'));

			$this->URLRegEx1 = '/(.*)'.$internalprefix.'([a-z]*)\/([0-9]*)(\/*)(.*)/';
			$this->URLRegEx2 = '/(.*)'.$internalprefix.'([a-z]*)\/([0-9]*)\/([0-9]*)(\/*)(.*)/';

			$filesdir = Singleton::config()->getVar('CONFIG/DIRECTORIES/FILESDIR');
			$filesdoc = Singleton::config()->getVar('CONFIG/DIRECTORIES/FILESDOC');
			$userpicdir = Singleton::config()->getVar('CONFIG/DIRECTORIES/USERPICDIR');
			$embeddedCblockFolder = (int)Singleton::config()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");
			if (strlen($filesdir) < 1) {
				$filesdir = "files/";
			}
			if (strlen($userpicdir) < 1) {
				$userpicdir = $filesdir;
			}
			if (strlen($filesdoc) < 1) {
				$filesdoc = "/yeager/files/";
			}
			if ((strlen($embeddedCblockFolder) < 1)||($embeddedCblockFolder==99999)) {
				throw new Exception("No or wrong blindfolder configured!");
			}
			$this->filesdir = $filesdir;
			$this->filesdoc = $filesdoc;
			$this->userpicdir = $userpicdir;
			$this->modules = Singleton::config()->getVars("CONFIG/MODULES");
			$this->files_procs =  array_merge(Singleton::config()->getVars("CONFIG/FILES_PROCESSORS"), Singleton::config()->getVars("CONFIG/FILE_PROCESSORS"));
			$this->page_procs = Singleton::config()->getVars("CONFIG/PAGE_PROCESSORS");
			$this->cblock_procs = Singleton::config()->getVars("CONFIG/CBLOCK_PROCESSORS");
			$this->email_procs = Singleton::config()->getVars("CONFIG/EMAIL_PROCESSORS");
			$this->filesprocdir = (string)Singleton::config()->getVar("CONFIG/DIRECTORIES/FILES_PROCS");
			$this->pageprocdir = (string)Singleton::config()->getVar("CONFIG/DIRECTORIES/PAGE_PROCS");
			$this->cblockprocdir = (string)Singleton::config()->getVar("CONFIG/DIRECTORIES/CBLOCK_PROCS");
			$this->emailprocdir = (string)Singleton::config()->getVar("CONFIG/DIRECTORIES/EMAIL_PROCS");
			$this->templates = new Templates();
			$this->templatedir = $this->approot.(string)Singleton::config()->getVar('CONFIG/DIRECTORIES/TEMPLATEDIR');
			$this->templatedoc = (string)Singleton::config()->getVar('CONFIG/DIRECTORIES/TEMPLATEDOC');
			$this->templatedirabsolut = $this->request->prefix."://".$this->request->http_host.$this->templatedoc;
			$this->extensiondir = (string)Singleton::config()->getVar('CONFIG/DIRECTORIES/EXTENSIONSDIR');
			$this->extensiondoc = (string)Singleton::config()->getVar('CONFIG/DIRECTORIES/EXTENSIONSDOC');
			$this->processordir = (string)Singleton::config()->getVar('CONFIG/DIRECTORIES/PROCESSORSDIR');
			$this->webroot = "/".rtrim(ltrim((string)Singleton::config()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/').'/';
			if ($this->webroot == "//") $this->webroot = "/";
			$this->devmode = (string)Singleton::config()->getVar('CONFIG/DEVMODE');
			$this->languages = new Languages();

			$forceLangInclude = (string)Singleton::config()->getVar('CONFIG/PAGES/'.strtoupper($this->page).'/FORCE_LANG_INCLUDE');

			if ( ($this->frontendMode != 'true') || ($forceLangInclude == 'true') ) {
				// Read default language from config-file
				if ($this->authenticated) {
					$user = new User(Singleton::UserMgr()->getCurrentUserID());
					$langid = $user->getLanguage();
					$langinfo = $this->languages->get($langid);
					$lang = $langinfo["CODE"];
				} else {
					// Check if we have a language which matches the browser-language
					$browserLanguages = array();
					$tmpBrowserLanguages = explode(',', strtoupper(str_replace(' ', '', $_SERVER["HTTP_ACCEPT_LANGUAGE"])));
					foreach($tmpBrowserLanguages as $tmpBrowserLanguage) {
						array_push($browserLanguages, substr($tmpBrowserLanguage, 0, 2));
					}
					$browserLanguages = array_values(array_unique($browserLanguages));
					foreach($browserLanguages as $browserLanguage) {
						if (!$lang) {
							if (file_exists($this->approot."ui/lang/".$browserLanguage.".php")) {
								$lang = $browserLanguage;
							}
						}
					}
				}

				// When everything fails, fallback to default language
				if (strlen($lang) < 1) {
					$defaultLanguage = Singleton::config()->getVar('CONFIG/DEFAULT_LANGUAGE');
					$lang = ($defaultLanguage)?($defaultLanguage):('DE');
				}

				require_once($this->approot."ui/lang/".$lang.".php");
			}

			$this->itext = &$itext;
			Singleton::register("itext", $itext);

			if ((!is_readable($this->page_file)) || (is_dir($this->page_file))) {
				$this->error->raise("Page ".$this->page."'s code (".$this->page_file.") not found.", ERR_DEBUG);
			} else {
				$this->error->raise("loading ".$this->page_file, ERR_DEBUG);
				if ($this->page_template != "") {
					require_once("libs/org/smarty/libs/Smarty.class.php");
					$smarty = new Smarty;
					$this->smarty = $smarty;
					$smarty->compile_check = true;
					$smarty->debugging = false;
					$smarty->use_sub_dirs = false;
					// FIXME move to installer
					@mkdir($this->tmpdir.'templates_compile', 0700);
					@mkdir($this->tmpdir.'templates_cache', 0700);
					$smarty->compile_dir = $this->tmpdir.'templates_compile';
					$smarty->cache_dir = $this->tmpdir.'templates_cache';
					$smarty->force_compile = (string)$this->config->getVar('CONFIG/CACHE/SMARTY_FORCECOMPILE');
					$smarty->caching = 0;
					$smarty->load_filter('output','trimwhitespace');

					$smarty->assign("yeager_version",$this->yeager_version);
					$smarty->assign("yeager_revision",$this->yeager_revision);
					//$smarty->assign("yeager_date",$this->yeager_date);
					$smarty->assign("lang",$lang);
					$smarty->assign("docabsolut",$this->docabsolut);
					$smarty->assign("baseabsolut",$this->baseabsolut);
					$smarty->assign("imgpath",$this->imgpath);
					$smarty->assign("internalprefix",(string)Singleton::config()->getVar('CONFIG/REFTRACKER/INTERNALPREFIX'));
                    $smarty->assign("request_prefix",$this->request->prefix);
					$smarty->assign("extensiondoc",$this->extensiondoc);
					$smarty->assign("extensiondir",$this->extensiondir);
					$smarty->assign("is_authenticated",$this->authenticated);
					$smarty->assign("base",$this->base);
					$smarty->assign("page",$this->page);
					$smarty->assign("sid",$this->sid);
					$smarty->assign("sidparam",$this->sidparam);
					$smarty->assign("templatedir",$this->templatedir);
					$smarty->assign("templatedoc",$this->templatedoc);
					$smarty->assign("templatedirabsolut",$this->templatedirabsolut);
					$smarty->assign("approot",getRealpath($this->approot));
					$smarty->assign("devmode",$this->devmode);
					$smarty->assign("webroot",$this->webroot);
					$smarty->assign("URLRegEx1",$this->URLRegEx1);

					require_once($this->approot."libs/org/yeager/ui/smarty_modifiers.php");
				}
				$smarty->assign("itext", $itext);
				Singleton::register("smarty", $smarty);

				if ($_SERVER['HTTP_X_YEAGER_AUTHENTICATION'] == 'suppress') {
					$authHeader = 'X-Yeager-Authenticated: delayed';
				} else {
					$authHeader = 'X-Yeager-Authenticated: '. (($this->authenticated)?('true'):('false'));
				}
				header( $authHeader );

				if ($this->frontendMode == "true" && $this->cached) { // capture ob
					include_once ($this->page_file);
					$output = ob_get_clean();
					Singleton::FC()->write("output",$output);
					Singleton::FC()->flush();
					echo $output;
				} else {
					include_once ($this->page_file);
				}

				if ($this->frontendMode != 'true') {
					$koala->getQueuedCommands();
					$koala->go();
				}

			}

		}

		public function shutdown() {
			if ($this->config->getVar("CONFIG/SCHEDULER_HOST") == "") {
				$this->schedulerurl = $this->request->prefix."://".$this->request->http_host.$this->base."scheduler/?bsu=".$this->session->getSessionVar("userid");
			} else {
				$this->schedulerurl =  $this->config->getVar("CONFIG/SCHEDULER_HOST").$this->base."scheduler/?bsu=".$this->session->getSessionVar("userid");
			}

            $scheduler_pages_str = $this->config->getVar("CONFIG/SCHEDULER_PAGES");
            $scheduler_pages = explode(",",$scheduler_pages_str);
            if ((in_array($this->page, $scheduler_pages)) || ($scheduler_pages_str == "")) {
                if ((int)$this->config->getVar("CONFIG/SCHEDULER") == 1 && $this->page != "scheduler") {
                    register_shutdown_function('framework\propagateShutdown', $this->schedulerurl);
                }
            }
			parent::shutdown();
		}
	}

	// set up config file
	$config = array("filename" => "config/config.xml.php");

	// instantiate
	ob_start();
	$app = new Yeager($config);

	$app->boot();

	$app->frontendMode = (string)$app->config->getVar('CONFIG/PAGES/'.strtoupper($app->page).'/FRONTEND');
	$hit = $app->hitCache();

	if (!$hit) {
		Singleton::register("Log", $app->error);
		$app->initdb();
		Singleton::register("YDB", $app->yeager);
		$app->go();
	}
	$app->shutdown();

	function sLog() {
		return Singleton::Log();
	}

	function sGuiUS() {
		return Singleton::guiUS();
	}

	function sGuiLH() {
		return Singleton::guiLH();
	}

	function sItext() {
		return Singleton::itext();
	}

/// @endcond

	/**
	 * Gets the current instance of Smarty
	 *
	 * @return object Smarty instance
	 */
	function sSmarty() {
		return Singleton::smarty();
	}

?>
