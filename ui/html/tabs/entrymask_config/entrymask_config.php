<?php

$ygid = $this->request->parameters['yg_id'];

$entrymask = explode('-', $ygid);
$entrymask = $entrymask[0];

$entrymaskMgr = new Entrymasks();
$entrymaskInfo = $entrymaskMgr->get($entrymask);
$objectparents = $entrymaskMgr->getParents($entrymask);

$objectparents[count($objectparents)-1][0]['NAME'] = ($itext['TXT_ENTRYMASKS']!='')?($itext['TXT_ENTRYMASKS']):('$TXT_ENTRYMASKS');

$entrymasks = $entrymaskMgr->getEntrymaskFormfields($entrymask);
foreach($entrymasks as $entrymask_idx => $entrymask) {
	if ( (($entrymask['FORMFIELD']==11) || ($entrymask['FORMFIELD']==12)) &&
		 $entrymasks[$entrymask_idx]['PRESET'] ) {
		$entrymasks[$entrymask_idx]['PRESET'] = TStoLocalTS($entrymasks[$entrymask_idx]['PRESET']);
	}
	if ($entrymask['FORMFIELD']==9) {
		$entrymasks[$entrymask_idx]['LVALUES'] = $entrymaskMgr->getListValuesByLinkID( $entrymask['ID'] );
	}
}

$smarty->assign("entrymaskInfo", $entrymaskInfo);
$smarty->assign("objectparents", $objectparents);
$smarty->assign("objecttype", "entrymask");
$smarty->assign("entrymasks", $entrymasks);

$smarty->assign('win_no', $this->request->parameters['win_no']);
$smarty->assign("uinfo", $uinfo);
$smarty->assign("controls", $controls);
$smarty->display('file:'.$this->page_template);

?>