<?php

$smarty->assign('displaymode', $this->request->parameters['display']);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->assign("opener_reference", $this->request->parameters['opener_reference']);
$smarty->display('file:'.$this->page_template);

?>