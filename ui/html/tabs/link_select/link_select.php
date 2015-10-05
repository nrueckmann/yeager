<?php

$prefix = '/';
$special_url = substr($this->request->parameters['special_url'], count($prefix));
$special_url = resolveSpecialURL($special_url);

if ($special_url !== false) {
	$smarty->assign("presetURL", $special_url);
} else {
	$smarty->assign("presetURL", trim(prettifyUrl($this->request->parameters['special_url'])));
}

$siteMgr = new Sites();

// Check if coming from Contenteditor
if (substr($this->request->parameters['opener_reference'], 0, 8) == 'textarea') {
	$smarty->assign("fromContentEditor", true);
}

$smarty->assign("action", $this->request->parameters['action']);
$smarty->assign("opener_reference", $this->request->parameters['opener_reference']);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->assign("sites", $siteMgr->getList());
$smarty->assign("site", $this->request->parameters['site']);
$smarty->display('file:'.$this->page_template);

?>