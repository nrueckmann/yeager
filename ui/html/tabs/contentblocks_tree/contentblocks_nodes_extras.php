<?php

header('Content-type: text/xml');

$newsite = (int)$this->request->parameters['site'];
$action = $this->request->parameters['action'];
$node_id =  $this->request->parameters['nid'];
if ($node_id) {
	$node_id = str_replace( 'page_', '', $node_id );
}
$maxlevels = 1;

// Check if we need special handling for the root-nodes
if ( ($action=='copy') || ($action=='move') ) {
	$root_link = 'javascript:void(0);';
}

if ($newsite!=0) {
	$siteID = $newsite;
}

$icons = new Icons();
$PageMgr = new PageMgr($siteID);

// All pages
$siteMgr = new Sites();
$sites = $siteMgr->getList();
$pages = $PageMgr->getTree($node_id, 2, 1);

$xml_tree = array();
$tree_id = 0;
$tree_level = 0;

if ($node_id) {
	$root_id = 'page_'.$pages[0]['ID'];
	$root_name = htmlspecialchars($pages[0]['NAME']);
	$root_cststyle = '';
} else {
	$node_id = 1;
	$root_id = 'root_1';
	$root_name = ($itext['TXT_PAGES']!='')?($itext['TXT_PAGES']):('$TXT_PAGES');
	$root_cststyle = 'root';
}

$props = array( 'TREE_ID' => $tree_id,
				'TREE_LEVEL' => $tree_level,
				'ID' => $root_id,
				'CAPTION' => $root_name,
				'URL' => $root_link,
				'IC' => $this->baseabsolut.'ui/img/window/x.gif',
				'EXP' => 'true',
				'CHK' => 'false',
				'CSTSTYLE' => $root_cststyle,
				'TARGET' => '',
				'TITLE' => '',
				'SVRLOAD' => 'false',
				'YG_ID' => $pages[$node_id]['ID'].'-'.$siteID,
				'YG_TYPE' => 'page',
				'YG_PROPERTY' => 'name',
				'XTRA' => 'noclick' );

array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => ($tree_level++), 'PROPS' => $props ) );

$siteMgr = new Sites();
for ($i = 0; $i < count($sites); $i++) {
	$sitename = $siteMgr->getName($sites[$i]['ID']);

	if ($sites[$i]['ID'] == $siteID) {
        $PageMgr = new PageMgr($sites[$i]['ID']);
		$yo = $PageMgr->tree->nest($pages);
		gen_tree($yo[$node_id]['CHILDREN'], $sites[$i]['ID'], $icons->icon, $this->imgpath, $xml_tree, $tree_id, $tree_level, $itext, $maxlevels, $yo[$node_id]);
	}

}

function gen_tree ($current, $site=1, $icons, $imgpath, &$xml_tree, &$tree_id, &$tree_level, $itext, $maxlevels, $currentnode) {

	if ($tree_level > $maxlevels) {
		$tree_level--;
		return;
	}

	$svrload = 'false';

	if ($tree_level == (int)$maxlevels) {
		$svrload = 'true';
	}

	if (is_array($currentnode['CONTENTAREAS']) && (count($currentnode['CONTENTAREAS'])>0) && ($currentnode['ID'] != 1) ) {

		$props = array( 'TREE_ID' => (++$tree_id),
						'TREE_LEVEL' => $tree_level,
						'ID' => 'contentareacontainer_'.$currentnode['ID'],
						'CAPTION' => '&lt;i&gt;'.($itext['TXT_CONTENTAREAS']!='')?($itext['TXT_CONTENTAREAS']):('$TXT_CONTENTAREAS').'&lt;/i&gt;',
						'URL' => $url,
						'IC' => $imgpath.'/icons/'.$icons['contentareas_small'],
						'EXP' => 'false',
						'CHK' => 'false',
						'CSTSTYLE' => '',
						'TARGET' => '',
						'TITLE' => '',
						'YG_ID' => $currentnode['ID'].'-contentareacontainer',
						'YG_TYPE' => 'contentareacontainer',
						'YG_PROPERTY' => 'name',
						'DND' => 'false',
						'XTRA' => 'noclick' );

		array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );

		$tree_level++;

		foreach ($currentnode['CONTENTAREAS'] as $contentarea) {

			$props = array( 'TREE_ID' => (++$tree_id),
							'TREE_LEVEL' => $tree_level,
							'ID' => 'contentarea'.$currentnode['ID'].'_'.$contentarea['ID'],
							// Normal Mode
							'CAPTION' => '&lt;i&gt;'.htmlspecialchars($contentarea['NAME']).'&lt;/i&gt;',
							// For Debugging
							//'CAPTION' => '&lt;i&gt;'.htmlspecialchars($contentarea['NAME']).'&lt;/i&gt;',
							'URL' => $url,
							'IC' => $imgpath.'/icons/'.$icons['contentarea_small'],
							'EXP' => 'false',
							'CHK' => 'false',
							'CSTSTYLE' => '',
							'TARGET' => '',
							'TITLE' => '',
							'YG_ID' => $contentarea['ID'].'-contentarea'.$current[$key]['ID'],
							'YG_TYPE' => 'contentarea',
							'YG_PROPERTY' => 'name',
							'DND' => 'false',
							'XTRA' => 'noclick' );

			array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );

			foreach ($contentarea['LIST'] as $co_item) {
				if ($co_item['EMBEDDED']!=1) {
					$tmpCblock = sCblockMgr()->getCblock($co_item['OBJECTID']);
					if ($tmpCblock) {
						$cBlockInfo = $tmpCblock->get();
						$co_item['NAME'] = $cBlockInfo['NAME'];
					}
					$props = array( 'TREE_ID' => (++$tree_id),
									'TREE_LEVEL' => $tree_level,
									'ID' => 'co'.$current[$key]['ID'].'_'.$co_item['OBJECTID'].'_'.$co_item['LINKID'],
									'CAPTION' => htmlspecialchars($co_item['NAME']),
									'URL' => $url,
									'IC' => $imgpath.'/icons/'.$icons['cblock_small'],
									'EXP' => 'false',
									'CHK' => 'false',
									'CSTSTYLE' => '',
									'TARGET' => '',
									'TITLE' => '',
									'YG_ID' => $co_item['OBJECTID'].'-cblock'.$current[$key]['ID'],
									'YG_TYPE' => 'cblock',
									'YG_PROPERTY' => 'name' );
					array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );
				}
			}

		}
		$tree_level--;
	}

	while ( list( $key, $value ) = each( $current ) ) {

		if ($current[$key]['RREAD'] > 0) {

			$iconData = getIconForPage($current[$key]);

			$img = $icons[$iconData['img']];
			$cststyle = $iconData['style'];

			// Add path
			if (($img == '') || (img != undefined)) $img = $imgpath.'icons/'.$img;

			$node_svrload = 'false';
			if ( ($svrload == 'true') && ($current[$key]['CHILDREN'] != NULL) ) {
				$node_svrload = $svrload;
			}

			$props = array( 'TREE_ID' => (++$tree_id),
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
							'SVRLOAD' => $node_svrload,
							'YG_ID' => $current[$key]["ID"].'-'.$site,
							'YG_TYPE' => 'page',
							'YG_PROPERTY' => 'name',
							'XTRA' => 'noclick' );

			array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );

			if ( is_array($current[$key]['CONTENTAREAS']) && (count($current[$key]['CONTENTAREAS'])>0) ) {

				$tree_level++;

				$props = array( 'TREE_ID' => (++$tree_id),
								'TREE_LEVEL' => $tree_level,
								'ID' => 'contentareacontainer_'.$current[$key]['ID'],
								'CAPTION' => '&lt;i&gt;'.($itext['TXT_CONTENTAREAS']!='')?($itext['TXT_CONTENTAREAS']):('$TXT_CONTENTAREAS').'&lt;/i&gt;',
								'URL' => $url,
								'IC' => $imgpath.'/icons/'.$icons['contentareas_small'],
								'EXP' => 'false',
								'CHK' => 'false',
								'CSTSTYLE' => '',
								'TARGET' => '',
								'TITLE' => '',
								'YG_ID' => $current[$key]['ID'].'-contentareacontainer',
								'YG_TYPE' => 'contentareacontainer',
								'YG_PROPERTY' => 'name',
								'XTRA' => 'noclick' );

				array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );

				$tree_level++;

				foreach ($current[$key]['CONTENTAREAS'] as $contentarea) {

					$props = array( 'TREE_ID' => (++$tree_id),
									'TREE_LEVEL' => $tree_level,
									'ID' => 'contentarea'.$current[$key]['ID'].'_'.$contentarea['ID'],
									// Normal Mode
									//	'CAPTION' => htmlspecialchars($current[$key]["NAME"]),
									// For Debugging
									'CAPTION' => '&lt;i&gt;'.htmlspecialchars($contentarea['NAME']).'&lt;/i&gt;',
									'URL' => $url,
									'IC' => $imgpath.'/icons/'.$icons['contentarea_small'],
									'EXP' => 'false',
									'CHK' => 'false',
									'CSTSTYLE' => '',
									'TARGET' => '',
									'TITLE' => '',
									'YG_ID' => $contentarea['ID'].'-contentarea'.$current[$key]['ID'],
									'YG_TYPE' => 'contentarea',
									'YG_PROPERTY' => 'name',
									'XTRA' => 'noclick' );

					array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );

					foreach ($contentarea['LIST'] as $co_item) {
						if ($co_item['EMBEDDED']!=1) {
							$tmpCblock = sCblockMgr()->getCblock($co_item['OBJECTID']);
							if ($tmpCblock) {
								$cBlockInfo = $tmpCblock->get();
								$co_item['NAME'] = $cBlockInfo['NAME'];
							}
							$props = array( 'TREE_ID' => (++$tree_id),
											'TREE_LEVEL' => $tree_level,
											'ID' => 'co'.$current[$key]['ID'].'_'.$co_item['OBJECTID'].'_'.$co_item['LINKID'],
											'CAPTION' => htmlspecialchars($co_item['NAME']),
											'URL' => $url,
											'IC' => $imgpath.'/icons/'.$icons['cblock_small'],
											'EXP' => 'false',
											'CHK' => 'false',
											'CSTSTYLE' => '',
											'TARGET' => '',
											'TITLE' => '',
											'YG_ID' => $co_item['OBJECTID'].'-cblock'.$current[$key]['ID'],
											'YG_TYPE' => 'cblock',
											'YG_PROPERTY' => 'name' );
/*
											'YG_PROPERTY' => 'name',
											 );
*/
							array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );
						}
					}

				}
				$tree_level--;
				$tree_level--;
			}

			if (is_array($current[$key]['CHILDREN'])) {
				$tree_level++;
				gen_tree( $current[$key]['CHILDREN'], $site, $icons, $imgpath, $xml_tree, $tree_id, $tree_level, $itext, $maxlevels, $current[$key] );
			}
		}
	}
	$tree_level--;
}

$smarty->assign("site",$siteID);
$smarty->assign("action",$action);
$smarty->assign("tree",$tree);

//var_dump( $xml_tree );
//die();

$smarty->assign("xml_tree",$xml_tree);

$smarty->display("file:".getrealpath($this->page_template));

?>