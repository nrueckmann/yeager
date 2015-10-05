<?php

$windowcfgxml = simplexml_load_string($smarty->fetch('file:'.getrealpath($this->approot)."/ui/html/windows/windows.xml"));
//$windowcfgxml = new \framework\Config($smarty->fetch('file:'.getrealpath($this->approot)."/ui/html/windows/windows.xml"), true);
$smarty->assign("windowconfig", json_encode($windowcfgxml));
$smarty->assign('itext_js', $itext_js);
$smarty->assign('lang', $lang);

$user = new User(sUserMgr()->getCurrentUserID());
$userinfo = $user->get();
$userinfo['PROPS'] = $user->properties->getValues( sUserMgr()->getCurrentUserID() );
$smarty->assign("RPAGES", $user->checkPermission( "RPAGES"));
$smarty->assign("RCOMMENTS", $user->checkPermission( "RCOMMENTS"));
$smarty->assign("RMAILINGS", $user->checkPermission( "RMAILINGS"));
$smarty->assign("RCONTENTBLOCKS", $user->checkPermission( "RCONTENTBLOCKS"));
$smarty->assign("RFILES", $user->checkPermission( "RFILES"));
$smarty->assign("RTAGS", $user->checkPermission( "RTAGS"));
$smarty->assign("RUSERS", $user->checkPermission( "RUSERS"));
$smarty->assign("RUSERGROUPS", $user->checkPermission( "RUSERGROUPS"));
$smarty->assign("RDATA", $user->checkPermission( "RDATA"));
$smarty->assign("RSITES", $user->checkPermission( "RSITES"));
$smarty->assign("RTEMPLATES", $user->checkPermission( "RTEMPLATES"));
$smarty->assign("REXTENSIONS_PAGE", $user->checkPermission( "REXTENSIONS_PAGE"));
$smarty->assign("REXTENSIONS_MAILING", $user->checkPermission( "REXTENSIONS_MAILING"));
$smarty->assign("REXTENSIONS_FILE", $user->checkPermission( "REXTENSIONS_FILE"));
$smarty->assign("REXTENSIONS_CBLOCK", $user->checkPermission( "REXTENSIONS_CBLOCK"));
$smarty->assign("RENTRYMASKS", $user->checkPermission( "RENTRYMASKS"));
$smarty->assign("REXPORT", $user->checkPermission( "REXPORT"));
$smarty->assign("REXTENSIONS_CBLISTVIEW", $user->checkPermission( "REXTENSIONS_CBLISTVIEW"));
$smarty->assign("RUPDATER", $user->checkPermission( "RUPDATER"));
$smarty->assign("RIMPORT", $user->checkPermission( "RIMPORT"));
$smarty->assign("RPROPERTIES", $user->checkPermission( "RPROPERTIES"));
$smarty->assign("RFILETYPES", $user->checkPermission( "RFILETYPES"));
$smarty->assign("RCOMMENTCONFIG", $user->checkPermission( "RCOMMENTCONFIG"));
$smarty->assign("RMAILINGCONFIG", $user->checkPermission( "RMAILINGCONFIG"));
$smarty->assign("RVIEWS", $user->checkPermission( "RVIEWS"));
$smarty->assign("username", $userinfo['PROPS']['FIRSTNAME'].' '.$userinfo['PROPS']['LASTNAME']);
$smarty->assign("userid", sUserMgr()->getCurrentUserID());
$smarty->display('file:'.$this->page_template);

?>