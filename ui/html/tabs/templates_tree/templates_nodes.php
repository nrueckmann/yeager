<?php

header('Content-type: text/xml');

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();
$adminAllowed = $tmpUser->checkPermission('RPAGES');
if (!$adminAllowed) $adminAllowed = $tmpUser->checkPermission('RTEMPLATES');

if ($adminAllowed) {
	$site = $this->request->parameters['site'];
	if ($site == 'mailing') {
		$mailingMgr = new MailingMgr();
		$templateRoot = $mailingMgr->getTemplateRoot();
	} else {
		$site = (int)$site;
		$siteMgr = new Sites();
		$siteinfo = $siteMgr->get($site);
		if ($siteinfo['TEMPLATEROOT']) {
			$templateRoot = $siteinfo['TEMPLATEROOT'];
		}
	}

	$templateMgr = new Templates();

	$newpage = (int)$this->request->parameters['page'];
	$action = $this->request->parameters['action'];

	if ($action == 'choose') {
		$noclick = 'noclick';
	} else {
		$noclick = '';
	}

	$onlyFolders = false;
	if ($this->page == 'templatefolders_tree_nodes') {
		$onlyFolders = true;
	}

	// Get all templates
	if ($templateRoot > 0) {
		$tree = $templateMgr->getList($templateRoot);
		$rootid = $templateRoot;
	} else {
		$tree = $templateMgr->getList();
		$rootid = 1;
	}
	$yo = $templateMgr->tree->nest($tree);

	$xml_tree = array();
	$tree_id = 0;
	$tree_level = 0;

	$props = array( 'TREE_ID' => $tree_id,
					'TREE_LEVEL' => $tree_level,
					'ID' => 'root_1',
					'CAPTION' => ($itext['TXT_TEMPLATES']!='')?($itext['TXT_TEMPLATES']):('$TXT_TEMPLATES'),
					'URL' => $root_link,
					'IC' => $this->imgpath.'/window/x.gif',
					'EXP' => 'true',
					'CHK' => 'false',
					'CSTSTYLE' => 'root',
					'TARGET' => '',
					'TITLE' => '',
					'YG_ID' => $tree[0]['ID'].'-template',
					'YG_TYPE' => 'template',
					'YG_PROPERTY' => 'name',
					'XTRA' => ($onlyFolders||($noclick!=''))?('noclick'):('') );

	if ($onlyFolders) {
		$props['YG_TYPE'] = 'templatefolder';
	}

	array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => ($tree_level++), 'PROPS' => $props ) );
	gen_tree($yo[$rootid]['CHILDREN'], $this->imgpath, $xml_tree, $tree_id, $tree_level, $noclick, $onlyFolders);
}

function gen_tree ($current, $imgpath, &$xml_tree, &$tree_id, &$tree_level, $noclick, $onlyFolders) {
	while ( list($key,$value)=each($current) ) {

		// Do print
		if ($current[$key]['FOLDER']==1) {
			// We have a folder, don't fill with data
			$props = array( 'TREE_ID' => (++$tree_id),
							'TREE_LEVEL' => $tree_level,
							'ID' => 'template_'.$current[$key]['ID'],
							'CAPTION' => htmlspecialchars( $current[$key]['NAME'] ),
							'URL' => '',
							'IC' => $imgpath.'icons/ico_folder_s.png,'.$imgpath.'icons/ico_folderopen_s.png',
							'EXP' => 'false',
							'CHK' => 'false',
							'CSTSTYLE' => 'nopreview noedit',
							'TARGET' => '',
							'TITLE' => '',
							'YG_ID' => $current[$key]['ID'].'-template',
							'YG_TYPE' => 'templatefolder',
							'YG_PROPERTY' => 'name',
							'XTRA' => $noclick	);

		} else {
			// We have a normal node, fill with data
			$props = array( 'TREE_ID' => (++$tree_id),
							'TREE_LEVEL' => $tree_level,
							'ID' => 'template_'.$current[$key]['ID'],
							'CAPTION' => htmlspecialchars( $current[$key]['NAME'] ),
							'URL' => '',
							'IC' => '',
							'EXP' => 'false',
							'CHK' => 'false',
							'CSTSTYLE' => 'nosub',
							'TARGET' => '',
							'TITLE' => '',
							'YG_ID' => $current[$key]['ID'].'-template',
							'YG_TYPE' => 'template',
							'YG_PROPERTY' => 'name'	);

		}

		if (! ($onlyFolders && ($current[$key]['FOLDER']!=1)) ) {
			array_push( $xml_tree, array( 'OBJECTID' => $tree_id, 'LEVEL' => $tree_level, 'PROPS' => $props ) );
		}
		if (is_array($current[$key]['CHILDREN'])) {
			$tree_level++;
			gen_tree( $current[$key]['CHILDREN'], $imgpath, $xml_tree, $tree_id, $tree_level, $noclick, $onlyFolders );
		}
		$prev_level = $current[$key]['LEVEL'];

	}
	$tree_level--;
}

$smarty->assign("page",$page);
$smarty->assign("site",$site);
$smarty->assign("action",$action);
$smarty->assign("tree",$tree);
$smarty->assign("xml_tree", $xml_tree);

$smarty->display("file:".getrealpath($this->page_template));

?>