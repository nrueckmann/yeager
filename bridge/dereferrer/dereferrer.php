<?php
    $smarty->assign("queryString", urldecode($_SERVER['QUERY_STRING']));
	$smarty->display('file:'.$this->page_template);
?>