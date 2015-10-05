<?php 

include_once "inc/page_parsereq.inc.php";
include_once "inc/page_render.inc.php";

function throwErrorPage($code) {
	if ($code == "404") {
		$header = $_SERVER['SERVER_PROTOCOL'].' 404 Not Found';
	} else if ($code == "403") {
		$header = $_SERVER['SERVER_PROTOCOL'].' 403 Forbidden';
	} else {
		$header = $_SERVER['SERVER_PROTOCOL'].' '.$code;
	}
	header($header);
	$errorPage = sConfig()->getVar('CONFIG/ERRORPAGES/ERROR_'.$code);
	if ($errorPage && $errorPage != '') {
		$request_path_string = getRequestPathString(explode('/', 'page'.$errorPage));
		$request_path = getRequestPathArray($request_path_string);
		$psite = $request_path[1];
		if ((int)$psite > 0) {
			$sinfo = sSites()->get($psite);
			$siteID = (int)$psite;
		} else {
			$sinfo = sSites()->getByPName($psite);
			$siteID = $sinfo['ID'];
		}
		$pageMgr = new PageMgr($siteID);
		$pageID = $pageMgr->getPageIdByPname($request_path[count($request_path)-1]);
		$page = $pageMgr->getPage($pageID);
		$pageInfo = $page->get();
		$version = $page->getLatestVersion();
		renderPage($page, $version, $pageInfo, $pageMgr, $sinfo);
	} else {
		echo $header;
	}
	die();
}

?>