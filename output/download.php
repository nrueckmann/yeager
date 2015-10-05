<?php

session_write_close();
while (@ob_end_clean());

ini_set("zlib.output_compression", "Off");


// Normalize everything and remove webroot-prefix
$request_path = $this->request->path;
$webroot_path = explode('/', rtrim(ltrim((string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/'));
$request_path_string = implode('/', $request_path);
$webroot_path_string = implode('/', $webroot_path);
if (strpos($request_path_string, "download/")===0) {
	// Remove leading 'page/'
	$request_path_string = substr($request_path_string, strlen("download/"));
}
if (strpos($request_path_string, $webroot_path_string.'/')===0) {
	// Remove leading webroot-prefix
	$request_path_string = substr($request_path_string, strlen($webroot_path_string.'/'));
}
$request_path = explode('/', "download/".$request_path_string);

$fileId = $request_path[1];
$prefix = $request_path[2];
if ($fileId == 'download') {
	$fileId = $request_path[2];
	$prefix = $request_path[3];
}
if ($prefix == "YGSOURCE") unset($prefix);

// Check if id is pname
if ($request_path[0] == $request_path[1]) {
	$fileMgr = sFileMgr();
	$new_fileId = (int)$fileMgr->getFileIdByPname($fileId);
	if ($new_fileId) {
		$fileId = $new_fileId;
	}
}

$login = $this->session->getSessionVar("username");
$password =	$this->session->getSessionVar("password");
$userId = sUserMgr()->validate($login, $password);
if ($userId < 1) {
	$userId = sUserMgr()->getAnonymousID();
}

$version = $this->request->parameters['version'];
$file = sFileMgr()->getFile($fileId, $version);
$fileinfo = false;

if ($file && is_numeric($fileId)) {
	$fileinfo = $file->get();
}

if ($fileinfo && ($fileinfo["DELETED"] != 1)) {
	if (!$version) $version = $file->getLatestVersion();
	$filedir = $this->approot.$this->filesdir;
	$filepath = $fileinfo["OBJECTID"]."-".$fileinfo["VIEWVERSION"].$fileinfo["FILENAME"];
	sFileMgr()->callExtensionHook("onDownload", $fileId, $version, array("VIEW" => $prefix));
	if ( (strlen($prefix) > 0) && (!is_numeric($prefix)) ) {
		$filepath = $prefix.$filepath;
	}
	$filesize = $fileinfo["FILESIZE"];
	$filename = $fileinfo["FILENAME"];
	$filestring=getrealpath($filedir.$filepath); // combine the path and file
	$filesize = filesize($filestring);

	$mime = "application/octet-stream";
	$mimetype = array(
		'pdf'=>'application/pdf',
		'doc'=>'application/msword',
		'htm'=>'text/html',
		'html'=>'text/html',
		'mp4'=>'video/mp4',
		'ogv'=>'video/ogg',
		'ogg'=>'video/ogg',
		'txt'=>'text/plain',
		'xls'=>'application/vnd.ms-excel'
	);

	$path_parts = pathinfo($filestring);
	$extension = $path_parts['extension'];
	$mime = $mimetype[$extension];
	if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")){
		$fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
	}
	if(!$fdl=@fopen($filestring,'r')){
		$header = $_SERVER['SERVER_PROTOCOL'].' 404 Not found';
		header($header);
		echo $header;
		die();
	} else {
		if (!strstr($mime, "video")) {
			header('Expires: ' . gmdate('D, d M Y H:i:s', time()+24*60*60) . ' GMT');
			header("Cache-Control: ");// leave blank to avoid IE errors
			header("Pragma: ");// leave blank to avoid IE errors
			header("Content-Disposition: attachment; filename=\"".$filename."\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Type: $mime");
			header("Content-length: ".(string)($filesize));
		}
		fclose($fdl);
		byteserve($filestring, $mime);
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