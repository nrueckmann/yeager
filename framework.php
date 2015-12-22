<?php

	namespace framework;

	@ini_set('session.use_trans_sid', 0);
	@ini_set('session.auto_start', 0);
	/* hotfix related to http://jira.nt.vc/browse/IT-427 */
	//@ini_set('display_errors', 1);

	define ("VERSION", "2.6.0");

	// define error levels
	define ("ERR_FATAL", 1);
	define ("ERR_NORMAL", 2);
	define ("ERR_WARNING", 3);
	define ("ERR_NOTICE", 4);
	define ("ERR_DEBUG", 5);
	define ("ALWAYS_LATEST_APPROVED_VERSION", 999999);

	function print_stack($stacka = NULL) {
		if ($stacka == NULL) {
			$stacka = debug_backtrace();
		}
	}

	function nullhandler () {
		return;
	}

	function check_phpversion ($version = "5.0.5") {
		if (preg_match("/(^\d+\.\d+\.\d+$)/", $version) == null) {
			return false;
		}
		if (str_replace(".", "", phpversion()) >= str_replace(".", "", $version)) {
			return true;
		}
		return false;
	}

	function import($path) {
		$rsFile = str_replace(".", "/", $path) . ".php";
		$importFile = getcwd()."/libs/".$rsFile;
		if (!file_exists($importFile)) {
			$importFile = str_replace("framework.php", "", __FILE__).$rsFile;
		}
		require_once($importFile);
	}

	import("org.yeager.framework.config.parser");
	import("org.yeager.framework.error.log");
	import("org.yeager.framework.error.handler");
	import("org.yeager.framework.request.handler");
	import("org.yeager.framework.db.connector");
	import("org.yeager.framework.session.handler");

	import("org.yeager.framework.tools.files.path");
	import("org.yeager.framework.cache.controller");
	import("org.yeager.framework.singleton.helper");

	abstract class Application {

		public $config;
		public $error;
		public $request;
		public $session;
		public $sessionhandler;

		public $tmpdir;
		public $app_db_dsns;
		public $db;


		public function __construct($config) {
			$this->config = new Config($config["filename"]);
			\Singleton::register("fmConfig", $this->config);

			// Force server timezone to UTC
			$this->timezone = 'UTC';
			date_default_timezone_set($this->timezone);

			$tmpdir = $this->_gettmpdir($config);
			$this->tmpdir = &$tmpdir;
			$this->error = new Error();
			\Singleton::register("Error", $this->error);
			$this->request = new Request();

			// legacy
			$this->approot = getcwd()."/";
			if ($this->page == "") {
				if (isset($this->request->path[0])) {
					$this->page = $this->request->path[0];
				}
				if (empty($this->page)) {
					$this->page = "default";
				}
			}
			$this->page_file = $this->config->getVar("CONFIG/PAGES/".strtoupper($this->page)."/CODE");
			$this->page_file = getrealpath($this->approot.$this->page_file);
			$this->page_template = getrealpath($this->approot.$this->config->getVar("CONFIG/PAGES/".strtoupper($this->page)."/TEMPLATE"));

			if (isset($this->request->path[1])) {
				$this->action = $this->request->path[1];
			} else {
				$this->action = "";
			}
			if ((empty($this->action)) && (isset($this->request->parameters['action']))) {
				$this->action = $this->request->parameters['action'];
			}
		}

		public function boot() {
			$this->_initSession();
		}

		public function initdb() {
			$this->_initDB();
		}

		public function shutdown() {
			session_write_close();
			$this->_closeDB();
		}

		private function _gettmpdir($config) {
			$tmpdir = $this->config->getVar("CONFIG/PATH/TMP");
			if (!$tmpdir) {
				return sys_get_temp_dir()."/";
			}
			return $tmpdir;
		}

		private function _initSession() {
			$this->sessionhandler = $this->config->getVar("CONFIG/SESSION/HANDLER");
			$cookie_scope = $this->config->getVar("CONFIG/SESSION/COOKIES/SCOPE");
			$cookie_time = $this->config->getVar("CONFIG/SESSION/COOKIES/TIME");
			$cookie_domain = $this->config->getVar("CONFIG/SESSION/COOKIES/DOMAIN");
			$cookie_secret = $this->config->getVar("CONFIG/SESSION/COOKIES/SECRET");
			if ($cookie_scope == "") {
				$cookie_scope = getrealpath($this->request->script_name."/".strtolower($this->applicationname)."/");
			}
			$this->session = new Session($this->sessionhandler, $this, $cookie_scope, $cookie_time, $cookie_secret, $cookie_domain);
		}

		private function _initDB() {
			$this->app_db_dsns = $this->config->getVars("CONFIG/DB");
			if (count($this->app_db_dsns) > 0) {
				for ($i = 0; $i < count($this->app_db_dsns); $i++) {
					$db_dsn = $this->app_db_dsns[$i]['id'];
					if ($db_dsn != "") {
						$db_driver = $this->app_db_dsns[$i]['driver'];
						$db_lib = $this->app_db_dsns[$i]['lib'];
						$db_host = $this->app_db_dsns[$i]['host'];
						$db_user = $this->app_db_dsns[$i]['user'];
						$db_pw = $this->app_db_dsns[$i]['password'];
						$db_name = $this->app_db_dsns[$i]['db'];
						$db_port = $this->app_db_dsns[$i]['port'];
						$db_autoconnect = $this->app_db_dsns[$i]['autoconnect'];
						$this->dbconnector = new Db ($db_lib, $db_driver);
						if ($db_autoconnect != "false") {
							$db = &$this->dbconnector->connect($db_host, $db_user, $db_pw, $db_name);
							$result = $this->dbconnector->isconnected;
							if ($result === true) {
								$$varname = strtolower($db_dsn);
								$dsnid = strtolower($db_dsn);
								$scr = "\$this->$dsnid = \$db;";
								eval($scr);
								$$varname = &$db;
							} else {
								trigger_error("DB: could not connect to $db_dsn($db_driver:$db_user@$db_host/$db_name).", E_USER_ERROR);
							}
						}
					}
				}
			}
		}

		private function _closeDB() {
			if (count($this->app_db_dsns) > 0) {
				for ($i = 0; $i < count($this->app_db_dsns); $i++) {
					if ($db_dsn != "") {
						$db_dsn = $this->app_db_dsns[$i]['id'];
						$db_autoconnect = $this->app_db_dsns[$i]['autodisconnect'] && $this->app_db_dsns[$i]['autoconnect'];
						if ($db_autoconnect != "false") {
							$$varname = strtolower($db_dsn);
							@$this->$$varname->close();
						}
					}
				}
			}
		}



		/**
		* @access public
		* @var application's config object
		*/
		var $app_config;



		/**
		* @access public
		* @var string error level
		*/
		var $error_level;

		/**
		* @access public
		* @var string log filename
		*/
		var $log_filename;

		/**
		* @access public
		* @var string log level
		*/
		var $log_level;

		/**
		* @access public
		* @var string application to load
		*/
		var $applicationname;

		/**
		* @access public
		* @var string application root directory in fs
		*/
		var $approot;

		/**
		* @access public
		* @var string application's http base
		*/
		var $app_httproot;

		/**
		* @access public
		* @var string application file
		*/
		var $app_file;

		/**
		* @access public
		* @var application's config file
		*/
		var $app_config_filename;


		/**
		* @access public
		* @var properties from license file
		*/
		var $license_properties;

	}

	/*
	import("org.yeager.framework.error.handler");
	// load error handler
	// load logger
	import("org.yeager.framework.config.parser");
	// load config parser

	import("org.yeager.framework.tools.timer");
	// load timer
	// load session handler


	//import("org.active-link.xml.XML");   // load xml parser
	//import("org.active-link.xml.XMLDocument");
	*/
	function propagateShutdown($url) {
		$size = ob_get_length();
		header("Content-Length: $size");
		ob_end_flush();
		set_time_limit(0);
		ignore_user_abort(true);

		\Singleton::Error()->enabled = false;

		@header("Connection: close");
		$size = ob_get_length();
		@header("Content-Length: $size");
		ob_end_flush();
		flush();
		session_write_close();

		if (function_exists(fastcgi_finish_request)) fastcgi_finish_request();

		$handle = @fopen($url, "r");
		$buffer = "";
		if ($handle) {
			while (!feof($handle)) {
				$buffer .= fgets($handle, 4096);
			}
			fclose($handle);
		} else {
		}
	}


?>
