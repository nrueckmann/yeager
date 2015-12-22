<?php

	namespace framework;

	/**
	 * neptun error handler class
	 *
	 */
	class Error  {
		/**
		 * @access public
		 * @var string log level
		 */
		var $level;

		/**
		 * @access public
		 * @var object log
		 */
		var $log;

		var $buckets;

		var $enabled;

		public function __construct() {
			@set_exception_handler(array($this, 'exceptionHandler'));
			// set default error level to all
			$this->level = 1;
			// catch all errors
			$old_error_handler = set_error_handler(array($this, 'errorHandler'));

			$buckets = \Singleton::fmConfig()->getVars("CONFIG/LOGGING/BUCKETS");
			if (count($buckets) == 0) {
				return;
			}
			$this->buckets = array();
			$b = 0;
			foreach ($buckets as $bucket) {
				if ($bucket["TYPE"] == "FILE") {
					$this->buckets[$b]["INSTANCE"] = new Filelog($bucket["URI"]);
					$this->buckets[$b]["LEVEL"] = $bucket["LEVEL"];
				}
				if ($bucket["TYPE"] == "PLUGIN") {
					if ($bucket["URI"] == "FireBug") {
                        if ($bucket["LEVEL"] > 0) {
                            $this->buckets[$b]["INSTANCE"] = new Firelog($bucket["URI"]);
                            $this->buckets[$b]["LEVEL"] = $bucket["LEVEL"];
                        }
					}
					if ($bucket["URI"] == "Echo") {
						$this->buckets[$b]["INSTANCE"] = new EchoLog($bucket["URI"]);
						$this->buckets[$b]["LEVEL"] = $bucket["LEVEL"];
					}
				}
				$b++;
			}

			$this->enabled = true;

		}

		public function exceptionHandler($exception) {
			if ($this->level > 1) {
				print "<b>Exception Caught: ".$exception->getMessage()."</b><br>\n";
				echo "<pre>";
				echo($exception->getTraceAsString());
				echo "</pre>";
			}
		}

		/**
		 * raises error according to loglevel
		 *
		 * @param string $error error message
		 * @param string $level error level
		 *
		 */
		function raise($error, $level) {
			if (count($this->buckets) == 0) return;
			if ($this->enabled == true) {
				foreach($this->buckets as $bucket) {
					$inst = $bucket["INSTANCE"];
					if ($level <= $bucket["LEVEL"]) {
						try {
							$inst->log($level, $error);
						} catch (Exception $e) {
							// whatever
						}
					}
				}
			}
		}

		public function error($error) {
			$this->raise($error, 1);
		}

		public function warn($error) {
			$this->raise($error, 2);
		}

		public function log($error) {
			$this->raise($error, 3);
		}

		public function debug($error) {
			$this->raise($error, 4);
		}

		/**
		 * sets error level
		 *
		 * @param string $level level
		 *
		 */
		function setLevel($level) {
			$this->level = $level;
		}

		/**
		 * sets log file name
		 *
		 * @param string $logfile file name
		 *
		 */
		function setLogfile ($logfile) {
			$this->log->setLogfile($logfile);
		}

		/**
		 * sets log level
		 *
		 * @param string $level level
		 *
		 */
		function setLogLevel($level) {
			$this->log->setLevel($level);
		}

		/**
		 * sets application name
		 *
		 * @param string $level application name
		 *
		 */
		function setApplicationName ($name) {
			$this->log->setApplicationName($name);
		}

		/**
		 * sets sid
		 *
		 * @param string $sid session id
		 *
		 */
		function setSessionId ($sid) {
			//$this->log->setSessionId($sid);
		}

		/**
		 * displays application level error
		 *
		 * @param string $error error message
		 *
		 */
		private function displayError($error) {
			trigger_error($error, E_USER_ERROR);
		}

		/**
		 * php error handler
		 *
		 * @param string $errorno error code
		 * @param string $errstr error string
		 * @param string $errfile error file
		 * @param string $errline error line
		 * @param string $context error context
		 *
		 */
		public function errorHandler ($errno, $errstr, $errfile, $errline, $context) {
			$message = "$errno in $errfile($errline): $errstr";
			if (\Singleton::Error()) {
				switch ($errno) {
					case E_ERROR:
						$level = "FATAL";
						Singleton::Error()->error($message);
						//$this->log("$level $errno in $errfile($errline): $errstr", ERR_FATAL);
						break;
					case ERROR:
						$level = "ERROR";
						\Singleton::Error()->error($message);
						//$this->log("$level $errno in $errfile($errline): $errstr", ERR_NORMAL);
						break;
					case E_WARNING:
						\Singleton::Error()->warn($message);
						$level = "WARNING";
						//$this->log("$level $errno in $errfile($errline): $errstr", ERR_WARNING);
						break;
					case E_NOTICE:
						\Singleton::Error()->debug($message);
						$level = "NOTICE";
						//$this->log("$level $errno in $errfile($errline): $errstr",ERR_NOTICE);
						break;
					case E_STRICT:
						$level = "NOTICE";
						\Singleton::Error()->debug($message);
						//$this->log("$level $errno in $errfile($errline): $errstr",ERR_NOTICE);
						break;
					default:
						/*				echo "<b>".$errstr."</b><br>";
						 print_stack();
						 return;*/
						$level = "NOTICE";
						\Singleton::Error()->debug($message);
						//$this->log("$level $errno in $errfile($errline): $errstr", ERR_NOTICE);
						break;
				}
			}
		}

		/**
		 * displays error page to end user
		 *
		 * @param string $errorno error code
		 * @param string $errstr error string
		 * @param string $errfile error file
		 * @param string $errline error line
		 * @param string $context error context
		 *
		 */
		private function displayErrorPage ($errno, $errstr, $errfile, $errline, $context, $level) {
			//error_log("error: $errno, $errstr, $errfile, $errline, $context, $level.", 0);
			if (file_exists("templates/error.html")) {
				$content = implode(file("templates/error.html"));
				$httproot = $nas_config->httproot;
				$questMark = "?";
				$phpOpenTag = "<${questMark}php";
				$phpCloseTag = "${questMark}>";
				eval("$phpCloseTag" . stripslashes($content) . "$phpOpenTag ");
			} else {
				echo "error: $errno, $errstr, $errfile, $errline, $context, $level.";
				echo "aditionally the error page template could not be found.";
			}
		}
	}

	define ("FATAL", E_USER_ERROR);
	define ("ERROR", E_USER_WARNING);
	define ("WARNING", E_USER_NOTICE);
	define ("SFATAL", E_ERROR);
	define ("SWARNING", E_WARNING);
	define ("PARSE", E_PARSE);
	define ("NOTICE", E_NOTICE);
	define ("CERROR", E_CORE_ERROR);
	define ("CWARNING", E_CORE_WARNING);
	define ("PERROR", E_COMPILE_ERROR);
	define ("PWARNING", E_COMPILE_WARNING);

	/**
	 * set the error reporting level for this script
	 */
	error_reporting (E_ALL ^ E_NOTICE);

?>