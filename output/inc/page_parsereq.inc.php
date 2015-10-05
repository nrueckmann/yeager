<?php

function getRequestPathString($request_path) {
	$request_path_string = implode('/', $request_path);
	if (strpos($request_path_string, "page/")===0) {
		$request_path_string = substr($request_path_string, strlen("page/"));
	}
	if (strpos($request_path_string, sApp()->webroot)===0) {
		$request_path_string = substr($request_path_string, strlen(sApp()->webroot));
	}
	if ($colonPos = strpos($request_path_string, ':')) {
		$request_path_string = substr($request_path_string, 0, $colonPos);
	}
	return $request_path_string;
}

function getRequestPathArray($request_path_string) {
	$request_path = explode('/', 'page/'.$request_path_string);
	if (strlen($request_path[count($request_path)-1]) < 1) {
		array_pop($request_path);
	}
	if ($request_path[count($request_path)-1][0] == ':') {
		array_pop($request_path);
	}
	return $request_path;
}

?>