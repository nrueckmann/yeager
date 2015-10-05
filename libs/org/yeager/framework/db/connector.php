<?php

	namespace framework;

    class Db {
        /**
        * @access public
        * @var string database abstraction driver to use
        */
        var $driver;

        /**
        * @access public
        * @var string database vendor
        */
        var $dbvendor;

        /**
        * @access public
        * @var bool tells if connection is established
        */
        var $isconnected;

        public function __construct ($driver = "ADODB", $dbvendor) {
			$this->driver = $driver;
			$this->dbvendor = $dbvendor;
			if ($this->driver == "ADODB") {
				require_once(getrealpath(dirname(__FILE__)."/../../../../org/adodb/adodb.inc.php"));
            } elseif ($this->driver == "PDOADOCOMPAT") {
				require_once(getrealpath(dirname(__FILE__)."/drivers/pdo.php"));
			} elseif ($this->driver == "ADODBLITE") {
				require_once(getrealpath(dirname(__FILE__)."/../../../../org/adodb_lite/adodb.inc.php"));
			} else {
				require_once(getrealpath(dirname(__FILE__)."/../../../../org/adodb/adodb.inc.php"));
				$this->driver = "ADODB";
            }
            $this->isconnected = false;
        }

        function connect ($db_host, $db_user, $db_pw, $db_name) {
            if ($this->driver == "ADODB") {
                $db = &NewADOConnection($this->dbvendor);
            } else if ($this->driver == "PDOADOCOMPAT") {
				$db = &NewADOConnection($this->dbvendor);
			} else if ($this->driver == "ADODBLITE") {
				$db = &NewADOConnection($this->dbvendor);
			}
			$result = $db->Connect($db_host, $db_user, $db_pw, $db_name);
            if ($result === true) {
                $this->isconnected = true;
            }
            return $db;
        }
    }

?>