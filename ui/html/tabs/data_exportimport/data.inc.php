<?php

$extensiontype = $this->request->parameters['et'];
$objecttype = $this->request->parameters['ot'];
$mode = $this->request->parameters['mode'];

$smarty->assign('extensiontype', $extensiontype);
$smarty->assign('objecttype', $objecttype);
$smarty->assign('mode', $mode);
$smarty->assign('displaymode', $this->request->parameters['wt']);

?>