<?php

/*
	Company			COMPANY
	Department		DEPARTMENT
	Firstname		FIRSTNAME
	Lastname		LASTNAME
	Phone			PHONE
	Fax				FAX
	Mobile			MOBILE
	Website			WEBSITE
	Profilepicture	PROFILEPICTURE

	Email			EMAIL
X	Passwort		PASSWORD

	Timezone		TIMEZONE
	Weekstart		WEEKSTART
	Dateformat		DATEFORMAT
	Timeformat		TIMEFORMAT

	// For first initialziation of the new userproperties

*/

    $user = new User(sUserMgr()->getCurrentUserID());
	$userinfo = $user->get();
	$userinfo['PROPS'] = $user->properties->getValues( sUserMgr()->getCurrentUserID() );
	$userinfo['LANGUAGE'] = $user->getLanguage();
	$languageMgr = new Languages();
	$languages = $languageMgr->getList();

	if (file_exists($this->approot.$this->userpicdir.sUserMgr()->getCurrentUserID().'-picture.jpg')) {
		$internPrefix = (string)sConfig()->getVar('CONFIG/REFTRACKER/INTERNALPREFIX');
		$userpicture = $internPrefix.'userimage/'.sUserMgr()->getCurrentUserID().'/48x48?rnd='.rand();
	} else {
		$userpicture = $this->imgpath.'content/temp_userpic.png';
	}

	$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setUserHeader(\''.$userpicture.'\',\''.$userinfo['PROPS']['FIRSTNAME'].' '.$userinfo['PROPS']['LASTNAME'].'\',\''.$userinfo['PROPS']['COMPANY'].'\', \''.$userinfo['ID'].'\');');

	$smarty->assign('timezones', getTimezones() );
	$smarty->assign('userinfo', $userinfo);
	$smarty->assign('userpicture', $userpicture);
	$smarty->assign("languages", $languages);
	$smarty->assign("win_no", $this->request->parameters['win_no']);
	$smarty->display("file:".getrealpath($this->page_template));

?>