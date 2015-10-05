<?php

header('Content-type: text/xml');

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();
$adminAllowed = $tmpUser->checkPermission('RENTRYMASKS');

if ( $adminAllowed ||
	 ($this->request->parameters['selectiondialog']=='1') ||
	 ($this->request->parameters['action']=='insertcontent') ) {
	$icons = new Icons();
	$selectiondialog = $this->request->parameters['selectiondialog'];
	if ( ($selectiondialog) ||
		 ($selectiondialog == "1") ||
		 ($this->request->parameters['action']=='insertcontent')) {
		$noclick = 'noclick';
	} else {
		$noclick = '';
	}

	// All entrymasks
	$entrymaskMgr = new Entrymasks();
	$entrymasks = $entrymaskMgr->getList();

	$yo = $entrymaskMgr->tree->nest($entrymasks);

	$xml_tree = array();
	$tree_id = 0;
	$tree_level = 0;

	$props = array( 'TREE_ID' => $tree_id,
					'TREE_LEVEL' => $tree_level,
					'ID' => 'root_1',
					'CAPTION' => ($itext['TXT_ENTRYMASKS']!='')?($itext['TXT_ENTRYMASKS']):('$TXT_ENTRYMASKS'),
					'URL' => $root_link,
					'IC' => $this->imgpath.'/window/x.gif',
					'EXP' => 'true',
					'CHK' => 'false',
					'CSTSTYLE' => 'root nodelete',
					'TARGET' => '',
					'TITLE' => '',
					'YG_ID' => $entrymasks[0]['ID'].'-entrymask',
					'YG_TYPE' => 'entrymask',
					'YG_PROPERTY' => 'name',
					'DND' => 'false',
					'XTRA' => $noclick );

	array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => ($tree_level++), 'PROPS' => $props ) );

	gen_tree($yo[1]['CHILDREN'], $icons->icon, $this->imgpath, $xml_tree, $tree_id, $tree_level, $noclick);
}

function gen_tree ($current, $icons, $imgpath, &$xml_tree, &$tree_id, &$tree_level, $noclick) {
	while ( list( $key, $value ) = each( $current ) ) {

		if ($current[$key]['FOLDER']) {
			$img = $imgpath.'icons/ico_folder_s.png,'.$imgpath.'icons/ico_folderopen_s.png';
			$cststyle = '';
			$dnd = 'false';
			$curr_noclick = $noclick;
		} else {
			$img = $icons['entrymask_small'];
			$cststyle = 'nosub nodrop';
			$dnd = '';
			$curr_noclick = '';
			// Add path
			if ($img != '') $img = $imgpath.'icons/'.$img;
		}

		$props = array( 'TREE_ID' => (++$tree_id),
						'TREE_LEVEL' => $tree_level,
						'ID' => 'entrymask_'.$current[$key]['ID'],
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
						'YG_ID' => $current[$key]['ID'].'-entrymask',
						'YG_TYPE' => 'entrymask',
						'YG_PROPERTY' => 'name',
						'DND' => $dnd,
						'XTRA' => $curr_noclick );

		array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );

		if (is_array($current[$key]['CHILDREN'])) {
			$tree_level++;
			gen_tree( $current[$key]['CHILDREN'], $icons, $imgpath, $xml_tree, $tree_id, $tree_level, $noclick );
		}
	}
	$tree_level--;
}

$smarty->assign("site", $site);
$smarty->assign("tree", $tree);

$smarty->assign("xml_tree", $xml_tree);

$smarty->display("file:".getrealpath($this->page_template));

?>