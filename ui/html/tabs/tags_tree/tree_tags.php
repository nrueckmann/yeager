<?php

$smarty->assign("action", $this->request->parameters['action']);
$smarty->assign("opener_reference", $this->request->parameters['opener_reference']);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>