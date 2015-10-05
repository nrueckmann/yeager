<?php

session_write_close();
while (@ob_end_clean());

ini_set("zlib.output_compression", "Off");


// Normalize everything and remove webroot-prefix
$request_path = $this->request->path;
$webroot_path = explode('/', rtrim(ltrim((string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/'));
$request_path_string = implode('/', $request_path);
$webroot_path_string = implode('/', $webroot_path);
if (strpos($request_path_string, "image/")===0) {
	// Remove leading 'page/'
	$request_path_string = substr($request_path_string, strlen("image/"));
}
if (strpos($request_path_string, $webroot_path_string.'/')===0) {
	// Remove leading webroot-prefix
	$request_path_string = substr($request_path_string, strlen($webroot_path_string.'/'));
}
$request_path = explode('/', "image/".$request_path_string);

$fileId = $request_path[1];
$prefix = $request_path[2];
$suffix = $request_path[3];
if ($fileId == 'image') {
	$fileId = $request_path[2];
	$prefix = $request_path[3];
	$suffix = $request_path[4];
}
if ($prefix == "YGSOURCE") unset($prefix);

// Check if id is pname
if ($request_path[0] == $request_path[1]) {
	$new_fileId = sFileMgr()->getFileIdByPname($fileId);
	if ($new_fileId) {
		$fileId = $new_fileId;
	}
}

$login = $this->session->getSessionVar("username");
$password =	$this->session->getSessionVar("password");
$userid = sUserMgr()->validate($login, $password);
if ($userid < 1) {
	$userid = sUserMgr()->getAnonymousID();
}
$version = (int)$this->request->parameters['version'];
$file = sFileMgr()->getFile($fileId, $version);
$fileinfo = false;

if ($file && is_numeric($fileId)) {
	$fileinfo = $file->get();
}

if ($fileinfo && ($fileinfo["DELETED"] != 1)) {
	if (!$version) $version = $file->getLatestVersion();
	sFileMgr()->callExtensionHook("onRender", $fileId, $version, array("VIEW" => $prefix));

	$filedir = $this->approot.$this->filesdir;
	$filepath = $fileinfo["OBJECTID"]."-".$fileinfo["VIEWVERSION"].$fileinfo["FILENAME"];
	if ( (strlen($prefix) > 0) && (!is_numeric($prefix)) && ($prefix != "YGSOURCE")) {
		$filepath = $prefix.$filepath;
	}
	$filename = $fileinfo["FILENAME"];

	if ($suffix!='') {
		$filename = substr($filename, 0, strrpos($filename, '.'))."_".$suffix.substr($filename, strrpos($filename, '.'));
		$filepath = substr($filepath, 0, strrpos($filepath, '.'))."_".$suffix.substr($filename, strrpos($filename, '.'));
	}

	$filestring = getrealpath($filedir.$filepath); // combine the path and file

	// hotfix for generated processor images
	if (!file_exists($filestring)) {
		if (file_exists(substr($filestring, 0, strrpos($filestring, '.')).".jpg")) {
			$filestring = substr($filestring, 0, strrpos($filestring, '.')).".jpg";
		} else if (file_exists(substr($filestring, 0, strrpos($filestring, '.')).".png")) {
			$filestring = substr($filestring, 0, strrpos($filestring, '.')).".png";
		}
	}

	$mime = "application/octet-stream";
	$imgsize = getimagesize($filestring);
	if ($imgsize <> false) {
		$mime = $imgsize["mime"];
	}
	if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")){
		$fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
	}

	$fdl = @fopen($filestring, 'rb');
	$filesize = filesize($filestring);
	if (!$fdl) {
		// Check if we can open the thumbnail with the original extension (deprecated)
		$fdl = @fopen($filestring, 'rb');
		$filesize = filesize($filestring);
	}
	if(!$fdl){
		$header = $_SERVER['SERVER_PROTOCOL'].' 404 Not found';
		header($header);
		echo $header;
		die();
	} else {
		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+7*24*60*60) . ' GMT');
		header("Cache-Control: ");// leave blank to avoid IE errors
		header("Pragma: ");// leave blank to avoid IE errors
		header("Content-type: $mime");
		header("Content-Disposition: inline; filename=\"".$filename."\"");
		header("Content-length:".(string)($filesize));
		while(!feof($fdl)) {
			$buffer = fread($fdl, 4096);
			print $buffer;
		}
		fclose($fdl);
		exit();
	}
} elseif (is_numeric($fileId) && sFileMgr()->fileExists($fileId) && ($fileinfo["DELETED"] != 1)) {
	sFileMgr()->callExtensionHook("onAccessDenied", $fileId, $version);
	$header = $_SERVER['SERVER_PROTOCOL'].' 403 Forbidden';
	header($header);
	echo $header;
	die();
} else {
	$header = $_SERVER['SERVER_PROTOCOL'].' 404 Not found';
	header($header);
	echo $header;
	die();
}

?>