<?php

header('Content-type: text/xml');

$node_id = $this->request->parameters['nid'];
if ($node_id == 'root_1') $node_id = '';
if ($node_id) {
	$node_id = str_replace( 'cblock_', '', $node_id );
	$subnodes = true;
}
$maxlevels = 1;

$mode = $this->request->parameters['action'];
if ($mode=='undefined') $mode = '';

$selectiondialog = $this->request->parameters['selectiondialog'];
if ($mode != '') {
	$noclick = 'noclick';
} else {
	$noclick = '';
}

if ($this->request->parameters['displaymode']) {
	$mode = 'choose';
	$noclick = 'noclick';
}

$icons = new Icons();

// Get folder for blinds contentblocks
$embeddedCblockFolder = (int)sConfig()->getVar("CONFIG/EMBEDDED_CBLOCKFOLDER");

$contentblocks = sCblockMgr()->getTree($node_id, 2, false, $embeddedCblockFolder);
$yo = sCblockMgr()->tree->nest($contentblocks);

$xml_tree = array();
$tree_id = 0;
$tree_level = 0;

if (($node_id == 'root_1') || !$node_id) {
	$node_id = 1;
	$root_id = 'root_1';
	$root_name = ($itext['TXT_CONTENTBLOCKS']!='')?($itext['TXT_CONTENTBLOCKS']):('$TXT_CONTENTBLOCKS');
	$root_cststyle = 'root';
} else {
	$root_id = 'cblock_'.$yo[$node_id]['ID'];
	$root_name = htmlspecialchars($yo[$node_id]['NAME']);
	$root_cststyle = '';
	if (!$contentblocks[0]['RSUB']) $root_cststyle .= ' nosub';
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
				'YG_ID' => $contentblocks[0]['ID'].'-cblock',
				'YG_TYPE' => 'cblock',
				'YG_PROPERTY' => 'name',
				'DND' => 'false',
				'XTRA' => $noclick );

array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => ($tree_level++), 'PROPS' => $props ) );

printtree($yo[$node_id]['CHILDREN'], $icons->icon, $this->imgpath, $xml_tree, $tree_id, $tree_level, $maxlevels, $mode, $noclick, $subnodes, $itext);

function printtree ($current, $icons, $imgpath, &$xml_tree, &$tree_id, &$tree_level, $maxlevels, $mode, $noclick, $subnodes, $itext) {

	if ($tree_level > $maxlevels) {
		$tree_level--;
		return;
	}

	$svrload = 'false';

	if ($tree_level == (int)$maxlevels) {
		$svrload = 'true';
	}

	while ( list( $key, $value ) = each( $current ) ) {

		if ( ($current[$key]['LEVEL']>1) && ($current[$key]['EMBEDDED']=='0') && ($current[$key]['NAME']!='__BLIND__') ) {

			$node_svrload = 'false';

			if ($svrload == 'true') {
				if ($current[$key]['CHILDREN'] != NULL) {
					$node_svrload = $svrload;
				}
				if ( ($mode=='copy') || ($mode=='move') || ($mode=='restore') ) {
					// In copy/move/restore mode, only set "svrload" to true, when there are folders as children
					$hasSubFolders = false;
					foreach($current[$key]['CHILDREN'] as $childItem) {
						if ($childItem['FOLDER']==1) {
							$hasSubFolders = true;
						}
					}
					if (!$hasSubFolders) {
						$node_svrload = 'false';
					}
				}
			}

			$img = $icons['cblock_small'];
			$url = '';

			// Add path
			if ($img != '') $img = $imgpath.'icons/'.$img;

			$cststyle = '';
			$dnd = '';

			if ($current[$key]['FOLDER']!=1) {

				// CBLOCK
				if ( ($current[$key]["VERSIONPUBLISHED"]+2 != $current[$key]["VERSION"]) && ($current[$key]["VERSIONPUBLISHED"]!=ALWAYS_LATEST_APPROVED_VERSION) && ($current[$key]["HASCHANGED"] == "1") ) {
					// Editiert (grün)
					$cststyle = "changed changed1";
				} elseif ($current[$key]["HASCHANGED"] == "1") {
					$cststyle = "changed changed2";
				}
				if ($current[$key]['RWRITE'] == '0') {
					// Nur Leserecht (hellgrau)
					$cststyle .= ' nowrite';
				}

				if ($current[$key]["RDELETE"] == "0") {
					// Nur Leserecht (hellgrau)
					$cststyle .= " nodelete";
				}
				$cststyle .= " nosub";
				$curr_noclick = '';
				$dnd = '';
				/*
				if ($mode=='choose') {
					$dnd = 'false';
				}
				*/
			} else {

				// FOLDER
				if ($current[$key]['RWRITE'] == '0') {
					// Nur Leserecht (hellgrau)
					$cststyle = 'nowrite';
				}
				if ($current[$key]["RDELETE"] == "0") {
					// Nur Leserecht (hellgrau)
					$cststyle .= " nodelete";
				}
				if ($current[$key]["RSUB"] == "0") {
					$cststyle .= " nosub";
				}
				$img = $imgpath.'icons/ico_folder_s.png,'.$imgpath.'icons/ico_folderopen_s.png';
				$cststyle .= ' folder';
				$dnd = 'false';
				if ( ($mode == 'choose') || ($mode == 'insertcontent') ) {
					$curr_noclick = 'noclick';
				}

			}

			$props = array( 'TREE_ID' => (++$tree_id),
							'TREE_LEVEL' => $tree_level,
							'ID' => 'cblock_'.$current[$key]['ID'],
							// Normal Mode
							'CAPTION' => htmlspecialchars($current[$key]['NAME']),
							// For Debugging
							//'CAPTION' => htmlspecialchars($current[$key]['NAME']).' ('.$current[$key]['ID'].')',
							'URL' => $url,
							'IC' => $img,
							'EXP' => 'false',
							'CHK' => 'false',
							'CSTSTYLE' => $cststyle,
							'TARGET' => '',
							'TITLE' => '',
							'SVRLOAD' => $node_svrload,
							'YG_ID' => $current[$key]['ID'].'-cblock',
							'YG_TYPE' => 'cblock',
							'YG_PROPERTY' => 'name',
							'DND' => $dnd,
							'XTRA' => $curr_noclick );

			if ( ($mode=='copy') || ($mode=='move') || ($mode=='restore') ) {
				if ($current[$key]['FOLDER']==1) {
					array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );
				}
			} else {
				array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );
			}

			if (is_array($current[$key]['CHILDREN'])) {
				$tree_level++;
				printtree( $current[$key]['CHILDREN'], $icons, $imgpath, $xml_tree, $tree_id, $tree_level, $maxlevels, $mode, $noclick, $subnodes, $itext );
			}

		}

	}

	if (!$mode && !$subnodes && ($tree_level == 1)) {
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
				'YG_ID' => 'trash-cblock',
				'YG_TYPE' => 'cblocktrash',
				'YG_PROPERTY' => 'name'	);

		array_push($xml_tree, array('OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props));
	}

	$tree_level--;
}

$smarty->assign("site",$site);
$smarty->assign("tree",$tree);
$smarty->assign("xml_tree",$xml_tree);

$smarty->display("file:".getrealpath($this->page_template));

?>