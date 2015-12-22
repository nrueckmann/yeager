<?php

namespace framework;

/**
* neptun request class
*
* @author  Next Tuesday GmbH <office@nexttuesday.de>
*
*/
class Request {
	/**
	* @access public
	* @var string protocol
	*/
	var $protocol;

	/**
	* @access public
	* @var string http host
	*/
	var $http_host;

	/**
	* @access public
	* @var string http hostname
	*/
	var $http_hostname;

	/**
	* @access public
	* @var string client's ip address
	*/
	var $client_ip;

	/**
	* @access public
	* @var string this script's name
	*/
	var $script_name;

	/**
	* @access public
	* @var string path info to access x.php/a/b/c style urls
	*/
	var $path;

	/**
	* @access public
	* @var array contains all parameters
	*/
	var $parameters;

	/**
	* @access public
	* @var string protocol prefix
	*/
	var $prefix;

	public function __construct () {
		// detect protocol - static for now
		$this->protocol = "HTTP";

		if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')) {
            $this->protocol = "HTTPS";
		}

		$this->client_ip = $_SERVER['REMOTE_ADDR'];
		$this->http_host = $_SERVER['HTTP_HOST'];
		$this->http_hostname = $_SERVER['SERVER_NAME'];
		$this->script_name = $_SERVER['SCRIPT_NAME'];
        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
            $_SERVER['HTTPS']='on';
        }
		if($_SERVER['HTTPS']=='on'){ $this->prefix = "https"; } else { $this->prefix = "http"; }

		// parse path info

		if (strlen($_SERVER['PATH_INFO']) < 1) {
			$path_info = $_SERVER["ORIG_PATH_INFO"];
			$path_info = str_replace( $_SERVER['SCRIPT_NAME'], '', $path_info);
		} else {
			$path_info = $_SERVER['PATH_INFO'];
		}

		if (!empty($path_info)) {
			$params = explode('/',$path_info);
			for ($i=1; $i<sizeof($params); $i++) {
				$variable_name = $params[$i];
				if (isset($variable_name)) {
					$this->path[] = $variable_name;
				}
			}
		}

		if ($_GET) {
			while (list ($variable_name, $variable_value) = each ($_GET)) {
				$this->_addVariable($variable_name, $variable_value);
			}
		}

		if ($_POST) {
			while (list ($variable_name, $variable_value) = each ($_POST)) {
				$this->_addVariable($variable_name, $variable_value);
			}
		}

	}

	function getParam ($param) {
		if (isset($_GET[$param])) {
			return $_GET[$param];
		}
		if (isset($_POST[$param])) {
			return $_POST[$param];
		}
		return "";
	}

	function getHost () {
		return $this->http_host;
	}

	function getHostname () {
		return $this->http_hostname;
	}

	function _addVariable(&$variable_name,&$variable_value){
		if(is_array($variable_value)){
			$this->_addArrayVariable($variable_name,$variable_value);
		}
		else{
			$this->_addNonArrayVariable($variable_name,$variable_value);
		}
	}

	function _addNonArrayVariable(&$variable_name,&$variable_value){
//			$this->{$variable_name} = $this->_getProcessedString($variable_value);
			$this->parameters[$variable_name] = $variable_value;
	}

	function _addArrayVariable(&$variable_name,&$variable_value){
		while (list($arrayKey,$arrayValue)=each($variable_value)){
			$variable_value[$arrayKey]=$this->_getProcessedString($arrayValue);
		}
//		$this->{$variable_name}=$variable_value;
		$this->parameters[$variable_name] = $variable_value;
		//array_push($this->parameters[$variable_name],$variable_value);
	}

	function _getProcessedString(&$variable_value){
		$value=trim($variable_value);
		$value=htmlspecialchars($variable_value,ENT_QUOTES);
		$value=stripcslashes($variable_value);
		return $variable_value;
	}
}

?>
