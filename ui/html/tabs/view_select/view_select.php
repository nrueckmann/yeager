<?php

// Get all available views
$viewMgr = new Views();
$views = $viewMgr->getList('prop.NAME');

// Remove Root-Node from views
$realViews = array();
foreach($views as $view) {
	if ($view['LEVEL'] > 1) {
		array_push( $realViews, $view );
	}
}
$views = $realViews;

$smarty->assign('views', $views );$smarty->assign('viewcount', count($views));$smarty->assign('win_no', $this->request->parameters['win_no'] );
$smarty->display('file:'.$this->page_template);

?>