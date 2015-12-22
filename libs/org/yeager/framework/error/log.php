<?php

	namespace framework;

    abstract class Logger {
        public function __construct ($uri = "") {}
        public function log ($level = 0, $message = "") {}
    }

    \framework\import('org.firephp.fb');
    class FireLog extends Logger {
        public function __construct ($uri = "") {
            $this->fb = \FirePHP::getInstance(true);
            $this->fb->setEnabled(true);
            $this->fb->registerExceptionHandler();
        }
        public function log ($level = 0, $message = "") {
            switch ($level) {
                case 1:
                    $this->fb->log($message);
                    break;
                case 2:
                    $this->fb->Warn($message);
                case 3:
                    $this->fb->Warn($message);
                case 4:
                    $this->fb->info($message);
                default:
                    break;
            }
        }
    }

    class EchoLog extends Logger {
        public function __construct ($uri = "") {
        }
        public function log ($level = 0, $message = "") {
            switch ($level) {
                case 1:
                    echo ($message."\n");
                    break;
                case 2:
                    echo ($message."\n");
                case 3:
                    echo ($message."\n");
                case 4:
                    echo ($message."\n");
                default:
                    break;
            }
        }
    }

    /**
     * neptun log class
     *
     * @author  Next Tuesday GmbH <office@nexttuesday.de>
     *
     */
    class Filelog extends Logger {
        /**
         * @access public
         * @var string log file name
         */
        var $logfilename;

        /**
         * @access public
         * @var string log level
         */
        var $level;

        /**
         * @access public
         * @var string session id
         */
        var $sid;

        /**
         * @access public
         * @var string application name
         */
        var $applicationname;

        public function __construct ($uri = "", $app_name = "", $session_id = "") {
            $this->setLogfile($uri);
            $this->tmpdir = sys_get_temp_dir()."/";
        }

        /**
         * sets log file name
         *
         * @param string $logfile file name
         *
         */
        private function setLogfile ($logfile) {
            $this->logfilename = $logfile;
        }

        /**
         * sets application name
         *
         * @param string $level application name
         *
         */
        function setApplicationName ($name) {
            $this->applicationname = $name;
        }


        /**
         * sets sid
         *
         * @param string $sid session id
         *
         */
        function setSessionId ($sid) {
            $this->sid = $sid;
        }

        public function log ($level = 0, $message = "") {
            $this->write($message, $level);
        }

        /**
         * writes error to log
         *
         * @param string $message message
         *
         */
        function write ($message, $level = 0) {
            $sessionid = $this->sid;
            $logfile = $this->logfilename;
            $conflevel = (int)$this->level;
            $application = $this->applicationname;
            if (($logfile != "")) {
                $logfile = str_replace ("[SYSTMP]", $this->tmpdir, $logfile);
                $logfile = str_replace ("[YEAR]", date('Y'), $logfile);
                $logfile = str_replace ("[MONTH]", date('m'), $logfile);
                $logfile = str_replace ("[DAY]", date('d'), $logfile);
                $logfile = str_replace ("[HOUR]", date('G'), $logfile);
                $date = date('Y-m-d G:i:s');
                $message = $date." (".$level.") "." (".$application.") "."(".$sessionid.") '".$message."'\n";
                $cf = fopen($logfile, 'a');
                fwrite($cf, $message);
                fclose($cf);
            }
        }
    }



    define ('_NT_LOG_ERROR', 1);
    define ('_NT_LOG_WARN', 2);
    define ('_NT_LOG_NORMAL', 3);
    define ('_NT_LOG_DEBUG', 4);

    function sendmail($to, $subject, $text, $header = "") {
        alog("MAIL: to: $to subject: $subject");
        mail($to, $subject, $text, $header);
    }

?>