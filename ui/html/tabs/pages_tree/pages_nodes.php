<?php

header('Content-type: text/xml');

$newsite = (int)$this->request->parameters['site'];
$action = $this->request->parameters['action'];
if ($action=='undefined') $action = '';
$node_id =  $this->request->parameters['nid'];
if ($node_id == 'root_1') $node_id = '';
$dnd =  $this->request->parameters['dnd'];
if ($node_id) {
	$node_id = str_replace('page_', '', $node_id);
	$subnodes = true;
}
$maxlevels = 1;

// Check if we need special handling for the root-nodes
if (($action=='copy') || ($action=='move')) {
	$root_link = 'javascript:void(0);';
}

$siteMgr = new Sites();
$sites = $siteMgr->getList();

if ($newsite!=0) {
	$siteID = $newsite;
	foreach($sites as $currSite) {
		if ($siteID == $currSite['ID']) {
			$sitePNAME = $currSite['PNAME'];
		}
	}
} else {
	$siteID = $sites[0]['ID'];
	$sitePNAME = $sites[0]['PNAME'];
}

$icons = new Icons();
$pageMgr = new PageMgr($siteID);

// All pages
$pagesList = $pageMgr->getTree($node_id, 2);
$xml_tree = array();
$tree_id = 0;
$tree_level = 0;

if ($node_id) {
	$root_id = 'page_'.$pagesList[0]['ID'];
	$root_name = htmlspecialchars($pagesList[0]['NAME']);
	$root_cststyle = '';
	$sitePNAME = $pagesList[0]['PNAME'];
} else {
	$node_id = 1;
	$root_id = 'root_1';
	$root_name = ($itext['TXT_PAGES']!='')?($itext['TXT_PAGES']):('$TXT_PAGES');
	$root_cststyle = 'root';
	if (!$pagesList[0]['RSUB']) $root_cststyle .= ' nosub';
}

$props = array('TREE_ID' => $tree_id,
				'TREE_LEVEL' => $tree_level,
				'ID' => $root_id,
				'CAPTION' => $root_name,
				'URL' => $root_link,
				'IC' => $this->imgpath.'window/x.gif',
				'EXP' => 'true',
				'CHK' => 'false',
				'CSTSTYLE' => $root_cststyle,
				'TARGET' => '',
				'TITLE' => '',
				'SVRLOAD' => 'false',
				'DND' => $dnd,
				'YG_ID' => $pagesList[0]['ID'].'-'.$siteID,
				'YG_TYPE' => 'page',
				'YG_PROPERTY' => 'name',
				'PNAME' => $sitePNAME );

array_push($xml_tree, array('OBJECTID' => $tree_id, 'LEVEL' => ($tree_level++), 'PROPS' => $props));

$siteMgr = new Sites();
for ($i = 0; $i < count($sites); $i++) {
	$sitename = $siteMgr->getName($sites[$i]['ID']);

	if ($sites[$i]["ID"] == $siteID) {

		$tree = $pageMgr->getTree($node_id, 2);
		$yo = $pageMgr->tree->nest($tree);

		// Only print pages, not sites
		gen_tree($yo[$node_id]['CHILDREN'], $sites[$i]['ID'], $icons->icon, $this->imgpath, $xml_tree, $tree_id, $tree_level, $maxlevels, $dnd, $cms, $subnodes, $itext, $action);
	}
}

function gen_tree ($current, $site=1, $icons, $imgpath, &$xml_tree, &$tree_id, &$tree_level, $maxlevels, $dnd, $cms, $subnodes, $itext, $action) {

	if ($tree_level > $maxlevels) {
		$tree_level--;
		return;
	}

	$svrload = 'false';

	if ($tree_level == (int)$maxlevels) {
		$svrload = 'true';
	}

	$oldlevel = 0;
	$urlprefix = "";

	while (list($key, $value) = each($current)) {

		if ($current[$key]["RREAD"] > 0) {
			$iconData = getIconForPage($current[$key]);

			$img = $icons[$iconData['img']];
			$cststyle = $iconData['style'];

			if ($oldlevel != $current[$key]["LEVEL"]) {
				$pnames = sPageMgr($site)->getParents($current[$key]["ID"]);

				$oldlevel = $current[$key]["LEVEL"];
				$urlprefix = "";
				$pi = count($pnames);
				while ($pi > 0) {
					if ($pnames[$pi-1][0]["PNAME"] != "") $urlprefix .= $pnames[$pi-1][0]["PNAME"]."/";
					$pi--;
				}
			}
			$url = sApp()->webroot.$urlprefix.$current[$key]["PNAME"]."/";

			// Add path
			if (($img == '') || (img != undefined)) $img = $imgpath.'icons/'.$img;

			$node_svrload = 'false';
			if (($svrload == 'true') && ($current[$key]['CHILDREN'] != NULL)) {
				$node_svrload = $svrload;
			}

			$props = array('TREE_ID' => (++$tree_id),
							'TREE_LEVEL' => $tree_level,
							'ID' => 'page_'.$current[$key]["ID"],
							// Normal Mode
							'CAPTION' => htmlspecialchars($current[$key]["NAME"]),
							// For Debugging
							//'CAPTION' => htmlspecialchars($current[$key]["NAME"]).' ('.$current[$key]["ID"].')',
							'URL' => $url,
							'IC' => $img,
							'EXP' => 'false',
							'CHK' => 'false',
							'CSTSTYLE' => $cststyle,
							'TARGET' => '',
							'TITLE' => '',
							'DND' => $dnd,
							'SVRLOAD' => $node_svrload,
							'YG_ID' => $current[$key]["ID"].'-'.$site,
							'YG_TYPE' => 'page',
							'YG_PROPERTY' => 'name',
							'PNAME' => $current[$key]["PNAME"]);

			array_push($xml_tree, array('OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props));

			if (is_array($current[$key]["CHILDREN"])) {
				$tree_level++;
				gen_tree($current[$key]["CHILDREN"], $site, $icons, $imgpath, $xml_tree, $tree_id, $tree_level, $maxlevels, $dnd, $cms, $subnodes, $itext, $action);
			}
		}
	}

	if (!$action && !$subnodes && ($tree_level == 1)) {
		$props = array('TREE_ID' => (++$tree_id),
				'TREE_LEVEL' => $tree_level,
				'ID' => 'page_trash',
				'CAPTION' => htmlspecialchars($itext['TXT_TRASHCAN']),
				'URL' => $url,
				'IC' => $imgpath.'icons/ico_trashcan_s.png',
				'EXP' => 'false',
				'CHK' => 'false',
				'CSTSTYLE' => 'nodrag nodrop nosub',
				'TARGET' => '',
				'TITLE' => '',
				'DND' => 'false',
				'SVRLOAD' => 'false',
				'YG_ID' => 'trash-'.$site,
				'YG_TYPE' => 'page',
				'YG_PROPERTY' => 'name');

		array_push($xml_tree, array('OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props));
	}

	$tree_level--;
}


$smarty->assign("page",$pageID);
$smarty->assign("site",$siteID);
$smarty->assign("action",$action);
$smarty->assign("tree",$tree);
$smarty->assign("xml_tree",$xml_tree);

$smarty->display("file:".getrealpath($this->page_template));

?>