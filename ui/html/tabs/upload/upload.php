<?php

// Get all available filetypes
$fileMgr = sFileMgr();
$filetypes = $fileMgr->getFiletypes();

$smarty->assign('filetypes', $filetypes );

$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->assign("preselected", $this->request->parameters['yg_id']);

$smarty->display('file:'.$this->page_template);

?>