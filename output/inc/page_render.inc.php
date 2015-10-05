<?php

function renderPage($page, $version, $pageInfo, $pageMgr, $siteInfo) {

	$templateMgr = new Templates();
	$pageID = $pageInfo["OBJECTID"];
	$templateInfo = $templateMgr->getTemplate($pageInfo["TEMPLATEID"]);
	$templatefilename = $templateInfo["FILENAME"];
	$templatefullpath = $templateMgr->getDir().$templateInfo["PATH"].$templatefilename;
	$content = $page->getContent();

	// navi
	$tree = $pageMgr->getList($pageID, array($filter));
	$tree = $pageMgr->getAdditionalTreeInfo($pageID, $tree, true);

	$oldlevel = 0;

	for ($xt = 0; $xt < count($tree); $xt++) {
		if ($oldlevel != $tree[$xt]["LEVEL"] || !$pnames) {
			$tree[$xt]["PARENTS"] = $pnames = $pageMgr->getParents($tree[$xt]["ID"]);
			$oldlevel = $tree[$xt]["LEVEL"];
		} else {
			$tree[$xt]["PARENTS"] = $pnames;
		}
		if ($pageID == $tree[$xt]["ID"]) {
			$pageInfo["PARENTS"] = $pnames;
		}
		$url = "";
		$pi = count($pnames);
		while ($pi > 0) {
			$url .= $pnames[$pi-1][0]["PNAME"]."/";
			$pi--;
		}
		$tree[$xt]["PURL"] = $url.$tree[$xt]["PNAME"]."/";
		$tree[$xt]["URL"] = sApp()->webroot.$url.$tree[$xt]["PNAME"]."/";
		if ($tree[$xt]["HIDDEN"] == 0 && $tree[$xt]["SHOW"] == 1) {
			$xtc = $xtc + 1;
		}
	}
	$pageMgr->callExtensionHook("onRender", $siteInfo["ID"], $pageID, $version, array("FILTER" => $filter, "CONTENTAREAS" => &$content));

	// Fill userinfo with data from current user
	$currUser = new User(sUserMgr()->getCurrentUserID());
	$userInfo = $currUser->get();
	$userInfo['FULLNAME'] = trim($userInfo['FIRSTNAME'].' '.$userInfo['LASTNAME']);

	sApp()->smarty->assign("user", $userInfo);
	sApp()->smarty->assign("devmode", (string)sApp()->devmode);
	sApp()->smarty->assign("pageinfo", $pageInfo);
	sApp()->smarty->assign("contentareas", $content);
	sApp()->smarty->assign("tree", $tree);
	sApp()->smarty->assign("site", $siteInfo["ID"]);
	sApp()->smarty->assign("siteinfo", $siteInfo);
	sApp()->smarty->assign("sitename", $siteInfo["PNAME"]);
	sApp()->smarty->assign("untitledparams", sApp()->request->untitled_parameters);

	if (!sApp()->output_tmp) {
		if ($templateInfo == NULL) {
			sApp()->output_tmp = "";
		} else {
			sApp()->output_tmp = sApp()->smarty->fetch("file:".$templatefullpath);
		}
	}

	//2nd pass
	sApp()->smarty->left_delimiter = '[!';
	sApp()->smarty->right_delimiter = '!]';
	sApp()->output = sApp()->smarty->fetch("var:".sApp()->output_tmp);

	//3rd pass (replace special urls with normal urls)
	sApp()->output = replaceSpecialURLs(sApp()->output);
	echo sApp()->output;	
}	

?>