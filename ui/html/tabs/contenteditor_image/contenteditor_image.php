<?php

// §§LINKTO:PAGE:9:99§§
// §§LINKTO:IMG:578§§
// §§LINKTO:DOWN:578§§
$prefix = '/';
$special_url = substr($this->request->parameters['special_url'], count($prefix));
$special_url = resolveSpecialURL($special_url);

if ($special_url !== false) {
	$smarty->assign("presetURL", $special_url);
}

$siteMgr = new Sites();

$smarty->assign("action", $this->request->parameters['action']);
$smarty->assign("opener_reference", $this->request->parameters['opener_reference']);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->assign("sites", $siteMgr->getList());
$smarty->assign("site", $this->request->parameters['site']);
$smarty->display('file:'.$this->page_template);

?>