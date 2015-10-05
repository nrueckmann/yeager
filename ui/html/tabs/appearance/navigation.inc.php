<?php

$templateid = (int)sanitize($this->request->parameters['template']);
$naviid = (int)sanitize($this->request->parameters['navigation']);

$templateMgr = new Templates();

$navis = $templateMgr->getNavis($templateid);
for ($i = 0; $i < count($navis); $i++) {
  if ($navis[$i]["ID"] == $naviid) {
      $naviinfo = $navis[$i];
  }
}

$smarty->assign('naviinfo', $naviinfo );
$smarty->display("file:".getrealpath($this->page_template));

?>