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

            if ($this->dbvendor == "mysqli" || $this->dbvendor == "mysql") {
                GLOBAL $ADODB_NEWCONNECTION;
                $ADODB_NEWCONNECTION = '\framework\patch_ado_factory';
            }

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
                $db = NewADOConnection($this->dbvendor);
            } else if ($this->driver == "PDOADOCOMPAT") {
				$db = NewADOConnection($this->dbvendor);
			} else if ($this->driver == "ADODBLITE") {
				$db = NewADOConnection($this->dbvendor);
			}
			$result = $db->Connect($db_host, $db_user, $db_pw, $db_name);
            if ($result === true) {
                $this->isconnected = true;
            }
            return $db;
        }

    }

    error_reporting(E_ALL ^ E_STRICT);
    require_once((dirname(__FILE__)."/../tools/files/path.php"));
    require_once(getrealpath(dirname(__FILE__)."/../../../../org/adodb/adodb.inc.php"));
    require_once(getrealpath(dirname(__FILE__)."/../../../../org/adodb/drivers/adodb-mysqli.inc.php"));
    require_once(getrealpath(dirname(__FILE__)."/../../../../org/adodb/drivers/adodb-mysql.inc.php"));

    function& patch_ado_factory($driver) {
        /***
         * Since dependency injection is not yet booted at this point we patch the mysqli driver
         */
        if (!in_array($driver, array('mysqli','mysql'))) return false;

        $driver = '\framework\patch_'.$driver;
        $obj = new $driver();
        return $obj;
    }

    function refValues($arr) {
        if (strnatcmp(phpversion(),'5.3') >= 0) {
            $refs = array();
            foreach($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }

    class patch_mysql extends \adodb_mysql {

        public function Execute() {
            $args = func_get_args();
            $statement = $args[0];
            $params = array_slice($args, 1);
            if ((count($params) > 0) && ($params[0] !== false)) {
                $statement = $this->prepare($statement);
            } else {
                $params = false;
            }
            return parent::Execute($statement, $params);
        }

        public function escape_string($column_name) {
            return mysql_real_escape_string($column_name);
        }

    }

    class patch_mysqli extends \adodb_mysqli {

        function prepare($sql) {
            $stmt = $this->_connectionID->prepare($sql);
            if (!$stmt) {
                echo $this->ErrorMsg();
                return $sql;
            }
            return array($sql,$stmt);
        }

        function _query($sql, $inputarr) {
            global $ADODB_COUNTRECS;
            if (is_array($sql)) {
                $stmt = $sql[1];
                $a = '';
                foreach($inputarr as $k => $v) {
                    if (is_string($v)) $a .= 's';
                    else if (is_integer($v)) $a .= 'i';
                    else $a .= 'd';
                }

                $ret = call_user_func_array('mysqli_stmt_bind_param', array_merge( array($stmt,$a), refValues($inputarr)));
                $ret = mysqli_stmt_execute($stmt);
                $rs = mysqli_stmt_get_result($stmt);
                if (!$rs && $ret) {
                    return mysqli_stmt_store_result($stmt);
                }
                return $rs;
            }

            if( $rs = mysqli_multi_query($this->_connectionID, $sql.';') )//Contributed by "Geisel Sierote" <geisel#4up.com.br>
            {
                $rs = ($ADODB_COUNTRECS) ? @mysqli_store_result( $this->_connectionID ) : @mysqli_use_result( $this->_connectionID );
                return $rs ? $rs : true; // mysqli_more_results( $this->_connectionID )
            } else {
                if($this->debug)
                    ADOConnection::outp("Query: " . $sql . " failed. " . $this->ErrorMsg());
                return false;
            }
        }

        public function Execute() {
            $args = func_get_args();
            $statement = $args[0];
            $params = array_slice($args, 1);
            if ((count($params) > 0) && ($params[0] !== false)) {
                $statement = $this->prepare($statement);
            } else {
                $params = false;
            }
            return parent::Execute($statement, $params);
        }

        public function escape_string($column_name) {
            return $this->_connectionID->real_escape_string($column_name);
        }

    }

?>