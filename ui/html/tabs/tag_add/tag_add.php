<?php

$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>