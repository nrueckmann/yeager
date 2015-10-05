<?php

$siteMgr = new Sites();
$sites = $siteMgr->getList();

if ($this->request->parameters['site'] && ($this->request->parameters['site'] != "")) {
	$site = $this->request->parameters['site'];
} else {
	$site = $sites[0]["ID"];
}

$smarty->assign("site", $site);
$smarty->assign("action", $this->request->parameters['action']);
$smarty->assign("opener_reference", $this->request->parameters['opener_reference']);
$smarty->assign("sites", $sites);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>