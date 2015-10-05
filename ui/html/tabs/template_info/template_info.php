<?php

$siteID = $this->request->parameters["site"];
$pageID = $this->request->parameters["page"];
$action = $this->request->parameters["action"];
$template = (int)sanitize($this->request->parameters["template"]);

$templateMgr = new Templates();

if (($template == 0) && $siteID && $pageID) {
	if ($siteID == "mailing") {
		$mailingMgr = new MailingMgr();
		$mailing = $mailingMgr->getMailing($pageID);
		$mailingInfo = $mailing->get();
		$template = $mailingInfo["TEMPLATEID"];
	} else {
		$pageMgr = new PageMgr($siteID);
		$page = $pageMgr->getPage($pageID);
		$pageInfo = $page->get();
		$template = $pageInfo["TEMPLATEID"];
	}
} else {
	$template = (int)sanitize($this->request->parameters["template"]);
}

if ($template != 0) {
	$templateInfo = $templateMgr->getTemplate( $template );
	$templateInfo["PREVIEWPATH"] = $templateMgr->getPreviewPath( $template );
} else {
	$templateInfo = false;
}

$smarty->assign("templateInfo", $templateInfo );
$smarty->assign("mode", sanitize($this->request->parameters["mode"]) );
$smarty->display("file:".getrealpath($this->page_template));

?>