<?php

	namespace framework;

	/**
	 * neptun session class
	 *
	 * @author  Next Tuesday GmbH <office@nexttuesday.de>
	 *
	 */
	class Session {
		/**
		 * @access public
		 * @var session handler
		 */
		var $handler;

		/**
		 * @access public
		 * @var session handler
		 */
		var $appserver;

		/**
		 * @access public
		 * @var session id
		 */
		var $id;

		/**
		 * @access public
		 * @var clients ip address
		 */
		var $ip;

		/**
		 * @access public
		 * @var cookie path
		 */
		var $cookie_scope;

		/**
		 * @access public
		 * @var cookie lifetime
		 */
		var $cookie_time;

		/**
		 * @access public
		 * @var cookie domain
		 */
		var $cookie_domain;

		var $session_name;

		var $variables = array();
		var $handler_driver;
		var $handler_host;
		var $handler_user;
		var $handler_pw;
		var $handler_db;
		var $handler_table;
		var $type;

		var $request;

		function __construct ($session_handler, &$appserver, $cookie_scope, $cookie_time, $secret, $cookie_domain = "") {
			$this->handler = $session_handler;
			$this->appserver = $appserver;
			//$this->id = $appserver->request->parameters['sid'];
			$this->ip = $appserver->request->client_ip;
			$this->cookie_scope = $cookie_scope;
			$this->cookie_time = /*time()+*/$cookie_time;
			$this->cookie_domain = /*time()+*/$cookie_domain;

			$this->session_name = "sid";
			$this->secret = $secret;

			//ob_end_clean();

			// setup
			ini_set('session.use_cookies', 0);
			ini_set('session.use_trans_sid', 0);
			session_name ($this->session_name);

			if (strlen($_POST['flashcookie']) > 1) {
				$fc = $_POST['flashcookie'];
				$this->appserver->error->raise ("SESSION: client ".$this->ip." passed flashcookie $fc", ERR_DEBUG);
				preg_match('/.*sid=([\w]*).*/', $fc, $asMatch);
				$fvalue = $asMatch[1];
				$this->appserver->error->raise ("SESSION: client ".$this->ip." found $fvalue in flashcokkie", ERR_DEBUG);
				if (strlen($fvalue) > 0) {
					$this->appserver->error->raise ("SESSION: client ".$this->ip." overrides sid with $fvalue from flashcookie", ERR_NOTICE);
                    $this->id = session_id($fvalue);
				}
			}

			if ($this->handler == "") {
				return;
			}
			if ($this->handler == "php") {
				$this->appserver->error->raise("SESSION: using php as session handler.", ERR_DEBUG);
			} else if ($this->handler == "adodb") {
				$this->appserver->error->raise("SESSION: adodb php as session handler.", ERR_DEBUG);
				$dbcfg = $this->appserver->config->xmlobject->SESSION->DSN;
				$dbattrs = $dbcfg->attributes();
				$this->handler_driver = (string)$dbattrs["driver"];
				$this->handler_host = (string)$dbattrs["host"];
				$this->handler_user = (string)$dbattrs["user"];
				$this->handler_pw = (string)$dbattrs["password"];
				$this->handler_db = (string)$dbattrs["db"];
				$this->handler_table = (string)$dbattrs["table"];
				//				$ADODB_SESSION_DRIVER = $this->handler_driver;
				//				$ADODB_SESSION_CONNECT = $this->handler_host;
				//				$ADODB_SESSION_USER = $this->handler_user;
				//				$ADODB_SESSION_PWD = $this->handler_pw;
				//				$ADODB_SESSION_DB = $this->handler_db;
				//				$ADODB_SESSION_TBL = $this->handler_table;
				require_once(getrealpath(dirname(__FILE__)."/../../../../org/adodb/session/adodb-session2.php"));
				$options['table'] = $this->handler_table;
				ADOdb_Session::lifetime($this->cookie_time);
				ADOdb_Session::config($this->handler_driver, $this->handler_host, $this->handler_user, $this->handler_pw, $this->handler_db, $options);
				ADOdb_session::Persist('P');
				//				ADOdb_Session::debug(true);
			}
			if ($this->cookie_time == 0) {
				$ct = 0;
			} else {
				$ct = $this->cookie_time + time();
			}
			session_set_cookie_params($ct, $this->cookie_scope, $this->cookie_domain);
			ini_set('session.use_cookies', 0);
			$this->session_started = false;
			if ($_COOKIE[$this->session_name]) {
				$this->id = session_id($_COOKIE[$this->session_name]);
			} else {
				session_start();
				session_regenerate_id();
				$this->id = session_id();
			}
//			setcookie ($this->session_name, $this->id, $ct, $this->cookie_scope, $this->cookie_domain);
			$_SESSION["nptn"] = true;
			$_SESSION["ts"] = time();

			if ($this->id != "") {
				// set session id to goven one
				$this->appserver->error->raise ("SESSION: got session id $this->id", ERR_DEBUG);
			} else {
				// generate session id
				$this->id = session_id();
				if (!$this->id) {
					trigger_error("SESSION: could not generate session id", E_USER_ERROR);
				}
				$this->appserver->error->raise ("SESSION: new session id: $this->id", ERR_DEBUG);
			}

			$this->appserver->error->setSessionId($this->id);

			// if session not empty

		}

		function setSessionVar ($key, $value) {
            if ($this->cookie_time == 0) {
                $ct = 0;
            } else {
                $ct = $this->cookie_time + time();
            }
			if ($this->handler == "php") {
                //$this->setCookie($this->session_name, $this->id);
                //setcookie ($this->session_name, $this->id, $ct, $this->cookie_scope, $this->cookie_domain);
				$this->appserver->error->raise ("SESSION: storing $value as $key with handler php", ERR_DEBUG);
				$_SESSION[$key] = $value;
			}
			else if ($this->handler == "adodb") {
				$this->appserver->error->raise ("SESSION: storing $value as $key with handler php", ERR_DEBUG);
				$_SESSION[$key] = $value;
			}
			else if ($this->handler == "tasdb") {
				$this->appserver->error->raise ("SESSION: storing $value as $key with handler tasdb", ERR_DEBUG);
				$$key = $value;
				session_register($$key);
			} else {
				trigger_error("SESSION: don't know how to store $key in session", ERROR);
			}
		}

		private function sign ($value) {
			$plain = $this->secret.$value;
			return sha1($plain)."|".$value;
		}

		public function setCookie ($name, $value, $time = NULL, $path = "", $domain = "", $sign=True) {
			if ($path == "") $path = $this->cookie_scope;
			if ($time) {
				$time = time()+(int)$this->cookie_time;
			}
			if ($domain == "") $domain = $this->cookie_domain;
            if ($this->getCookie($name) != $value) {
                if ($sign) {
                    $value = $this->sign($value);
                }
                setcookie ($name, $value, $time, $path, $domain);
            }
		}

        public function removeCookie($name) {
            if ($_COOKIE[$name]) {
				$this->id = "";    
				unset($_COOKIE[$name]);
                setcookie($name, null, -1, '/');
            }
        }

		public function getCookie ($name) {
			$value = $_COOKIE[$name];
			$value = explode("|", $value);
			$hash = $value[0];
			$value = $value[1];
			if (sha1($this->secret.$value) == $hash) {
				return $value;
			}
			return;
		}

        public function refrehSessionCookie() {
            $time = (int)$this->cookie_time;
            if ($time == 0) {
                $ct = 0;
            } else {
                $ct = $time + time();
            }
			ini_set('session.use_cookies', true);
			session_regenerate_id();
			$this->id = session_id();
            setcookie ($this->session_name, $this->id, $ct, $this->cookie_scope, $this->cookie_domain);
        }

		function setPSessionVar ($key, $value) {
			$path = $this->cookie_scope;
			$domain = $this->cookie_domain;
			$time = (int)$this->cookie_time;
			if ($time == 0) {
				$ct = 0;
			} else {
				$ct = $time + time();
			}
			if ($this->handler == "php") {
				$this->appserver->error->raise ("SESSION: storing sid in cookie", ERR_DEBUG);
                //$this->setCookie($this->session_name, $this->id);
				if (!$this->session_started) {
					ini_set('session.use_cookies', true);
					session_start($this->id);
					$this->session_started = true;
				}
				if (!($_COOKIE[$this->session_name] == $this->id)) {
					setcookie ($this->session_name, $this->id, $ct, $path, $domain);
                }
				$this->appserver->error->raise ("SESSION: storing $value as $key with handler php", ERR_DEBUG);
				$_SESSION[$key] = $value;
			}
			else if ($this->handler == "adodb") {
				$this->appserver->error->raise ("SESSION: storing sid in cookie", ERR_DEBUG);
				//setcookie("sid", $this->id, $time, $path);
				$this->appserver->error->raise ("SESSION: storing $value as $key with handler php", ERR_DEBUG);
				$_SESSION[$key] = $value;
				$$key = $value;
				session_register($$key);
			}
			else if ($this->handler == "tasdb") {
				$this->appserver->error->raise ("SESSION: storing $value as $key with handler tasdb", ERR_DEBUG);
				$$key = $value;
				session_register($$key);
			} else {
				trigger_error("SESSION: don't know how to store $key in session", ERROR);
			}
		}

		function getSessionVar ($key) {
			if (!$this->session_started) {
				$orgSetting = ini_get('session.use_cookies');
				ini_set('session.use_cookies', false);
				session_start($this->id);
				ini_set('session.use_cookies', $orgSetting);
			}
			$result = "";
			if (isset($_SESSION[$key])) {
				$result = $_SESSION[$key];
			}
			$this->appserver->error->raise ("SESSION: got '$result' from $key", ERR_DEBUG);
			return $result;
		}
	}
?>
