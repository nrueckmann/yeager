<?php

/// @cond DEV

class Singleton {

	static $instances = array();

	static private function initSingleton ($name, $class) {
		if (array_key_exists($name, self::$instances)) {
			return false;
		} else {
			self::$instances[$name] = &$class;
			return true;
		}
	}

	static function __callStatic($method, $args ) {
		if (preg_match("/unregister(.*)/", $method, $found)) {
			if (array_key_exists($args[0], self::$instances)) {
				unset(self::$instances[$args[0]]);
				return true;
			}
		} elseif (preg_match("/register(.*)/", $method, $found)) {
			return self::initSingleton($args[0], $args[1]);
		} elseif (preg_match("/(.*)/", $method, $found ) ) {
			if (array_key_exists($found[1], self::$instances)) {
				$instance =& self::$instances[$method];
				return $instance;
			}
		}
		return false;
	}

}

function sSession() {
	return Singleton::session();
}

function sRequest() {
	return Singleton::request();
}

function get_execution_time() {
	static $microtime_start = null;
	if($microtime_start === null)
	{
		$microtime_start = microtime(true);
		return 0.0;
	}
	return microtime(true) - $microtime_start;
}

/// @endcond

function sConfig() {
	return Singleton::config();
}

function sApp() {
	return Singleton::app();
}

function sYDB() {
	return Singleton::YDB();
}

?>