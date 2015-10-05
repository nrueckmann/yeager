<?php

$extensiontype = $this->request->parameters['extensiontype'];
$objecttype = $this->request->parameters['objecttype'];
$mode = $this->request->parameters['mode'];
$selectiondialog = $this->request->parameters['selectiondialog'];

$rexport = $user->checkPermission("REXPORT");
$rimport = $user->checkPermission("RIMPORT");
$rextensions_cblistview = $user->checkPermission("REXTENSIONS_CBLISTVIEW");
$rextensions_page = $user->checkPermission("REXTENSIONS_PAGE");
$rextensions_mailing = $user->checkPermission("REXTENSIONS_MAILING");
$rextensions_file = $user->checkPermission("REXTENSIONS_FILE");
$rextensions_cblock = $user->checkPermission("REXTENSIONS_CBLOCK");

if ($objecttype == 'extensions') {
	if ($rextensions_page) {
		$objecttype = "extpage";
	} else if ($rextensions_mailing) {
		$objecttype = "extmailing";
	} else if ($rextensions_file) {
		$objecttype = "extfile";
	} else if ($rextensions_cblock) {
		$objecttype = "extcblock";
	} else if ($rimport) {
		$objecttype = "extimport";
	} else if ($rexport) {
		$objecttype = "extexport";
	} else if ($rextensions_cblistview) {
		$objecttype = "extcolistview";
	}
}

$only_installed = false;
if (($selectiondialog) || ($objecttype == 'data')) {
	$only_installed = true;
}

$tmpUser = new User(sUserMgr()->getCurrentUserID());
$tmpUserInfo = $tmpUser->get();

switch ($objecttype) {
	case 'extpage':
		$adminAllowed = $tmpUser->checkPermission('REXTENSIONS_PAGE');
		$extensiontype = EXTENSION_PAGE;
		break;
	case 'extcblock':
		$adminAllowed = $tmpUser->checkPermission('REXTENSIONS_CBLOCK');
		$extensiontype = EXTENSION_CBLOCK;
		break;
	case 'extfile':
		$adminAllowed = $tmpUser->checkPermission('REXTENSIONS_FILE');
		$extensiontype = EXTENSION_FILE;
		break;
	case 'extmailing':
		$adminAllowed = $tmpUser->checkPermission('REXTENSIONS_MAILING');
		$extensiontype = EXTENSION_MAILING;
		break;
	case 'extimport':
		$adminAllowed = $tmpUser->checkPermission('RIMPORT');
		$extensiontype = EXTENSION_IMPORT;
		break;
	case 'extexport':
		$adminAllowed = $tmpUser->checkPermission('REXPORT');
		$extensiontype = EXTENSION_EXPORT;
		break;
	case 'extcolistview':
		$adminAllowed = $tmpUser->checkPermission('REXTENSIONS_CBLISTVIEW');
		$extensiontype = EXTENSION_CBLOCKLISTVIEW;
		break;
	case 'data':
		$adminAllowed = $tmpUser->checkPermission('RDATA');
		switch($extensiontype) {
			default:
			case 'import':
				$extensiontype = EXTENSION_IMPORT;
				break;
			case 'export':
				$extensiontype = EXTENSION_EXPORT;
				break;
		}
		break;
}

if ($adminAllowed) {
	$extensionMgr = new ExtensionMgr();
	$extensionMgr->refreshList($this->approot.$this->extensiondir);
	$extensions = $extensionMgr->getList( $extensiontype, $only_installed, true );

	// Filter out self-controlled extensions
	if (($selectiondialog) || ($objecttype == 'data')) {
		$real_extensions = array();
		foreach($extensions as $extensionItem) {
			$currExtension = $extensionMgr->getExtension( $extensionItem['CODE'] );
			if ($currExtension && $currExtension->info['ASSIGNMENT'] != EXTENSION_ASSIGNMENT_EXT_CONTROLLED) {
				$real_extensions[] = $extensionItem;
			}
		}
		$extensions = $real_extensions;
	}
}

$smarty->assign('mode', $mode);
$smarty->assign('extensioncount', count($extensions));
$smarty->assign('extensions', $extensions);
$smarty->assign('extensiontype', $extensiontype);
$smarty->assign('extensiondir', $this->extensiondir);
$smarty->assign('objecttype', $objecttype);
$smarty->assign('opener_reference', $this->request->parameters['opener_reference']);
$smarty->assign('selectiondialog', $selectiondialog);
$smarty->assign('win_no', $this->request->parameters['win_no']);
$smarty->assign("REXPORT", $rexport);
$smarty->assign("RIMPORT", $rimport);
$smarty->assign("REXTENSIONS_CBLISTVIEW", $rextensions_cblistview);
$smarty->assign("REXTENSIONS_PAGE", $rextensions_page);
$smarty->assign("REXTENSIONS_MAILING", $rextensions_mailing);
$smarty->assign("REXTENSIONS_FILE", $rextensions_file);
$smarty->assign("REXTENSIONS_CBLOCK", $rextensions_cblock);
$smarty->display("file:".getrealpath($this->page_template));

?>