<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$initload = $this->request->parameters['initload'];

$sortby = $this->request->parameters['sortby'];
$show = $this->request->parameters['show'];
$view = $this->request->parameters['view'];

$template = explode('-', $ygid);
$site = $template[1];
$template = $template[0];

$templateMgr = new Templates();
$templateInfo = $templateMgr->getTemplate($template);

$objectparents = $templateMgr->getParents($template);
$objectparents[count($objectparents)-1][0]['NAME'] = ($itext['TXT_TEMPLATES']!='')?($itext['TXT_TEMPLATES']):('$TXT_TEMPLATES');

// Get all contentareas
$contentareas = $templateMgr->getContentareas( $template );

// Get all navigations
$navigations = $templateMgr->getNavis( $template );
$hasdefault = false;
foreach ($navigations as $idx => $navigation) {
	if ($navigation['DEFAULT']==1) {
		$hasdefault = true;
		$defaultnavi = $navigation['CODE'];
	}
}
if (!$hasdefault) {
	$navigations[0]['DEFAULT'] = 1;
	$defaultnavi = $navigations[0]['CODE'];
}

// Check for template preview
$templatepreviewdir = getRealpath( sConfig()->getVar( 'CONFIG/DIRECTORIES/TEMPLATEPREVIEWDIR' ) ).'/';
$found_files = glob($templatepreviewdir.$template.'-*');
if (($found_files !== false) && (count($found_files)>0)) {
	$previewfile = explode('/', $found_files[0]);
	$previewfile = $previewfile[count($previewfile)-1];
	$previewdir = sConfig()->getVar( 'CONFIG/DIRECTORIES/TEMPLATEPREVIEWDIR' );
}

// Add template for Contentareas to Array
$contentareas[] = array(
	'CODE'		=> '__FIELDTITLE__',
	'NAME'		=> '__FIELDNAME__'
);

// Add template for Navigations to Array
$navigations[] = array(
	'CODE'		=> '__FIELDTITLE__',
	'NAME'		=> '__FIELDNAME__'
);

$templateInfo['PREVIEWPATH'] = $previewdir.$previewfile;
$templateInfo['PREVIEW'] = $previewfile;

$smarty->assign('templateInfo', $templateInfo );
$smarty->assign('timestamp', time() );
$smarty->assign("object", $template);
$smarty->assign("objecttype", "template");
$smarty->assign("objectparents", $objectparents);
$smarty->assign("contentareas", $contentareas);
$smarty->assign("rfilecontentareas", $rfilecontentareas);
$smarty->assign("afilecontentareas", $afilecontentareas);

$smarty->assign("navigations", $navigations);
$smarty->assign("rfilenavis", $rfilenavis);
$smarty->assign("afilenavis", $afilenavis);
$smarty->assign("defaultnavi", $defaultnavi);

$smarty->assign('site', $site );
$smarty->assign('refresh', $refresh );
$smarty->assign('initload', $initload );
$smarty->assign('win_no', $this->request->parameters['win_no']);

$smarty->display('file:'.$this->page_template);

?>