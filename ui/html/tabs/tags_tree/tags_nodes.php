<?php

header('Content-type: text/xml');

$newsite = (int)$this->request->parameters['site'];
$newpage = (int)$this->request->parameters['page'];
$node_id =  $this->request->parameters['nid'];
if ($node_id) {
	$node_id = str_replace( 'tag_', '', $node_id );
}
$maxlevels = 1;

if ($newsite!=0) {
	$site = $newsite;
}

$icons = new Icons();
$tagMgr = new Tags();

$tree = $tagMgr->getTree($node_id, 2);
$yo = $tagMgr->tree->nest($tree);

$xml_tree = array();
$tree_id = 0;
$tree_level = 0;

$action = $this->request->parameters['action'];
if ($action == 'addtag') {
	$noclick = 'noclick';
} else {
	$noclick = '';
}

if ($node_id) {
	$root_id = 'tag_'.$yo[$node_id]['ID'];
	$root_name = htmlspecialchars($yo[$node_id]['NAME']);
	$root_cststyle = '';
} else {
	$node_id = 1;
	$root_id = 'root_1';
	$root_name = ($itext['TXT_TAGS']!='')?($itext['TXT_TAGS']):('$TXT_TAGS');
	$root_cststyle = 'root';
	if ($yo[1]['RDELETE'] == "0") {
		// No delete right
		$root_cststyle .= " nodelete";
	}
	if ($yo[1]['RSUB'] == "0") {
		$root_cststyle .= " nosub";
	}
}

$props = array( 'TREE_ID' => $tree_id,
				'TREE_LEVEL' => $tree_level,
				'ID' => $root_id,
				'CAPTION' => $root_name,
				'URL' => '',
				'IC' => $this->imgpath.'/window/x.gif',
				'EXP' => 'true',
				'CHK' => 'false',
				'CSTSTYLE' => $root_cststyle,
				'TARGET' => '',
				'TITLE' => '',
				'SVRLOAD' => 'false',
				'YG_ID' => $tree[0]['ID'].'-tag',
				'YG_TYPE' => 'tag',
				'YG_PROPERTY' => 'name',
				'XTRA' => $noclick );

array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => ($tree_level++), 'PROPS' => $props ) );

gen_tree($yo[$node_id]['CHILDREN'], $this->imgpath, $xml_tree, $tree_id, $tree_level, $maxlevels);

function gen_tree ($current, $imgpath, &$xml_tree, &$tree_id, &$tree_level, $maxlevels) {

	if ($tree_level > $maxlevels) {
		$tree_level--;
		return;
	}

	$svrload = 'false';

	if ($tree_level == (int)$maxlevels) {
		$svrload = 'true';
	}

	while ( list($key,$value)=each($current) ) {

		if ($current[$key]["RREAD"] > 0) {

			$cststyle = '';

			if ($current[$key]["RWRITE"] == "0") {
				// No edit right
				$cststyle .= " nowrite";
			}
			if ($current[$key]["RDELETE"] == "0") {
				// No delete right
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
							'ID' => 'tag_'.$current[$key]["ID"],
							// Normal Mode
							'CAPTION' => htmlspecialchars( $current[$key]["NAME"] ),
							// For Debugging
							//'CAPTION' => htmlspecialchars( $current[$key]["NAME"].' ('.$current[$key]["ID"].')' ),
							'URL' => '',
							'IC' => $imgpath.'icons/ico_tag_s.png',
							'EXP' => 'false',
							'CHK' => 'false',
							'CSTSTYLE' => $cststyle,
							'TARGET' => '',
							'TITLE' => '',
							'SVRLOAD' => $node_svrload,
							'YG_ID' => $current[$key]["ID"].'-tag',
							'YG_TYPE' => 'tag',
							'YG_PROPERTY' => 'name'	);

			array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );

			if (is_array($current[$key]["CHILDREN"])) {
				$tree_level++;
				gen_tree( $current[$key]["CHILDREN"], $imgpath, $xml_tree, $tree_id, $tree_level, $maxlevels );
			}

		}

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