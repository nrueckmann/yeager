<?php

header('Content-type: text/xml');

$fileMgr = sFileMgr();

$action = $this->request->parameters['action'];
$node_id =  $this->request->parameters['nid'];
$dnd =  $this->request->parameters['dnd'];
if ($node_id) {
	$node_id = str_replace( 'file_', '', $node_id );
	$subnodes = true;
} else {
	$rootNode = $fileMgr->tree->get("",1);
	$node_id = $rootNode[0]["ID"];
	$isRoot = true;
}
$maxlevels = 1;

// Check if we need special handling for the root-nodes
if ( ($action=='choose') || ($action=='restore') ) {
	$root_link = 'javascript:void(0);';
}

$icons = new Icons();

// All files
$files = $fileMgr->getTree($node_id, 2);

$xml_tree = array();
$tree_id = 0;
$tree_level = 0;

$root_id = ($files[0]['ID'] == 1)?('root_1'):('file_'.$files[0]['ID']);
$root_cststyle = ($files[0]['ID'] == 1)?('root'):('');
if (!$isRoot) {
	$root_name = htmlspecialchars($files[0]['NAME']);
} else {
	$root_name = ($itext['TXT_FILES']!='')?($itext['TXT_FILES']):('$TXT_FILES');
	if (!$files[0]['RSUB']) $root_cststyle .= ' nosub';
}

$props = array( 'TREE_ID' => $tree_id,
				'TREE_LEVEL' => $tree_level,
				'ID' => $root_id,
				'CAPTION' => $root_name,
				'URL' => $root_link,
				'IC' => $this->imgpath.'/window/x.gif',
				'EXP' => 'true',
				'CHK' => 'false',
				'CSTSTYLE' => $root_cststyle,
				'TARGET' => '',
				'TITLE' => '',
				'SVRLOAD' => 'false',
				'DND' => $dnd,
				'YG_ID' => $files[0]['ID'].'-file',
				'YG_TYPE' => 'file',
				'YG_PROPERTY' => 'name'	);

array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => ($tree_level++), 'PROPS' => $props ) );

//$cms->setsite($sites[$i]["ID"]);
$yo = $fileMgr->tree->nest($files);

gen_tree($yo[$node_id]['CHILDREN'], $site, $icons->icon, $this->imgpath, $xml_tree, $tree_id, $tree_level, $maxlevels, $dnd, $subnodes, $itext, $action);


function gen_tree ($current, $site=1, $icons, $imgpath, &$xml_tree, &$tree_id, &$tree_level, $maxlevels, $dnd, $subnodes, $itext, $action) {

	if ($tree_level > $maxlevels) {
		$tree_level--;
		return;
	}

	$svrload = 'false';

	if ($tree_level == (int)$maxlevels) {
		$svrload = 'true';
	}

	while ( list( $key, $value ) = each( $current ) ) {

		if ($current[$key]["RREAD"] > 0) {

			$img = $imgpath.'icons/ico_folder_s.png,'.$imgpath.'icons/ico_folderopen_s.png';

			$cststyle = '';

			if ( ($current[$key]["VERSIONPUBLISHED"]+2 != $current[$key]["VERSION"]) && ($current[$key]["VERSIONPUBLISHED"]!=ALWAYS_LATEST_APPROVED_VERSION) && ($current[$key]["HASCHANGED"] == "1") ) {
				// Editiert (gr?n)
				$cststyle = "changed";
			} elseif ($current[$key]["HASCHANGED"] == "1") {
				$cststyle = "changed";
			}
			if ($current[$key]["RWRITE"] == "0") {
				// Nur Leserecht (hellgrau)
				$cststyle .= " nowrite";
			}
			if ($current[$key]["RDELETE"] == "0") {
				// Nur Leserecht (hellgrau)
				$cststyle .= " nodelete";
			}
			if ($current[$key]["RSUB"] == "0") {
				$cststyle .= " nosub";
			}

			$node_svrload = 'false';
			if ( ($svrload == 'true') && ($current[$key]['CHILDREN'] != NULL) ) {
				$node_svrload = $svrload;
			}

			$props = array( 'TREE_ID' => (++$tree_id),
							'TREE_LEVEL' => $tree_level,
							//'ID' => 'file_'.$current[$key]["ID"],
							'ID' => ($current[$key]["ID"] == 1)?('root_1'):('file_'.$current[$key]["ID"]),
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
							'YG_ID' => $current[$key]["ID"].'-file',
							'YG_TYPE' => 'file',
							'YG_PROPERTY' => 'name'	);

			array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );

			if (is_array($current[$key]["CHILDREN"])) {
				$tree_level++;
				gen_tree( $current[$key]["CHILDREN"], $site, $icons, $imgpath, $xml_tree, $tree_id, $tree_level, $maxlevels, $dnd, $subnodes, $itext, $action );
			}
		}
	}

	if (!$action && !$subnodes && ($tree_level == 1)) {
		$props = array('TREE_ID' => (++$tree_id),
				'TREE_LEVEL' => $tree_level,
				'ID' => 'cblock_trash',
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
				'YG_ID' => 'trash-file',
				'YG_TYPE' => 'filetrash',
				'YG_PROPERTY' => 'name'	);

		array_push($xml_tree, array('OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props));
	}

	$tree_level--;
}

$smarty->assign("page",$page);
$smarty->assign("site",$site);
$smarty->assign("action",$action);
$smarty->assign("tree",$tree);

$smarty->assign("xml_tree",$xml_tree);

$smarty->display("file:".getrealpath($this->page_template));

?>