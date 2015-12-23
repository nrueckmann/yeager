<?php

$ygid = $this->request->parameters['yg_id'];
$refresh = $this->request->parameters['refresh'];
$objecttype = $this->request->parameters['yg_type'];

$data = explode('-',$ygid );
$objectID = $data[0];
$siteID = $data[1];

$filterTab = $this->request->parameters['versionfilter_tab'];
$filterAction = $this->request->parameters['versionfilter_action'];
$filterTimeframe = $this->request->parameters['versionfilter_timeframe'];
$filterObject = $this->request->parameters['versionfilter_objecttype'];
$icons = new Icons();

if ($filterTab == '') $filterTab = 'ALL';
if ($filterAction == '') $filterAction = 'ALL';
if ($filterTimeframe == '') $filterTimeframe = 'LAST_WEEK';

$entrymaskMgr = new Entrymasks();
$viewMgr = new Views();
$tagMgr = new Tags();
$filetypeMgr = sFiletypes();

$tab_mappings = array(
	// MAILINGS
	'TXT_MAILING_H_TEMPLATE'				=>	'P_PROPERTIES',
	'TXT_MAILING_H_COREMOVE'				=>	'P_CONTENT',
	'TXT_MAILING_H_EMREMOVE'				=>	'P_CONTENT',
	'TXT_MAILING_H_COADD'					=>	'P_CONTENT',
	'TXT_MAILING_H_EMADD'					=>	'P_CONTENT',
	'TXT_MAILING_H_EMCOPY'					=>	'P_CONTENT',
	'TXT_MAILING_H_COORDER'					=>	'P_CONTENT',
	'TXT_MAILING_H_PROP'					=>	'P_PROPERTIES',
	'TXT_MAILING_H_PROP_FILE'				=>	'P_PROPERTIES',
	'TXT_MAILING_H_PROP_FILE_REMOVED'		=>	'P_PROPERTIES',
	'TXT_MAILING_H_PROP_RICHTEXT'			=>	'P_PROPERTIES',
	'TXT_MAILING_H_PROP_TAG'				=>	'P_PROPERTIES',
	'TXT_MAILING_H_PROP_CBLOCK'				=>	'P_PROPERTIES',
	'TXT_MAILING_H_PROP_PAGE'				=>	'P_PROPERTIES',
	'TXT_MAILING_H_PROP_DATE'				=>	'P_PROPERTIES',
	'TXT_MAILING_H_PROP_DATETIME'			=>	'P_PROPERTIES',
	'TXT_MAILING_H_PROP_LINK'				=>	'P_PROPERTIES',
	'TXT_MAILING_H_AUTOPUBLISH_CHANGED'		=>	'P_PUBLISHING',
	'TXT_MAILING_H_AUTOPUBLISH_ADDED'		=>	'P_PUBLISHING',
	'TXT_MAILING_H_GROUPADD'				=>	'P_USERGROUPS',
	'TXT_MAILING_H_GROUPREMOVE'				=>	'P_USERGROUPS',
	'TXT_MAILING_H_NEWVERSION_FROM'			=>	'P_VERSIONS',
	'TXT_MAILING_H_NEWVERSION'				=>	'P_VERSIONS',
	'TXT_MAILING_H_APPROVE'					=>	'P_VERSIONS',
	'TXT_MAILING_H_AUTOPUBLISH'				=>	'P_VERSIONS',
	'TXT_MAILING_H_PAUSED'					=>	'P_VERSIONS',
	'TXT_MAILING_H_CANCELLED'				=>	'P_VERSIONS',
	'TXT_MAILING_H_RESUMED'					=>	'P_VERSIONS',
	'TXT_MAILING_H_SENDING'					=>	'P_VERSIONS',

	// CBLOCKS
	'TXT_CBLOCK_H_EMADD'					=>	'P_CONTENT',
	'TXT_CBLOCK_H_EMREMOVE'					=>	'P_CONTENT',
	'TXT_CBLOCK_H_EMORDER'					=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_1'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_2'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_3'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_4_ON'		=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_4_OFF'		=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_5'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_6'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_7'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_8'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_9'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_10'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_11'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_12'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_15'			=>	'P_CONTENT',
	'TXT_CBLOCK_H_PROP'						=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PROP_CHECKON'				=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PROP_CHECKOFF'			=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PROP_FILE'				=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PROP_RICHTEXT'			=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PROP_TAG'					=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PROP_CBLOCK'				=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PROP_LINK'				=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PROP_PAGE'				=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PROP_DATE'				=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PROP_DATETIME'			=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PROP_PASSWORD'			=>	'P_PROPERTIES',
	'TXT_CBLOCK_H_PNAME'					=>	'P_PROPERTIES',
	'TXT_TAG_H_ASSIGN'						=>	'P_TAGS',
	'TXT_TAG_H_REMOVE'						=>	'P_TAGS',
	'TXT_TAG_H_TAGORDER'					=>	'P_TAGS',
	'TXT_CBLOCK_H_NEWVERSION'				=>	'P_VERSIONS',
	'TXT_CBLOCK_H_NEWVERSION_FROM'			=>	'P_VERSIONS',
	'TXT_CBLOCK_H_TRASHED'					=>	'P_VERSIONS',
	'TXT_CBLOCK_H_RESTORED'					=>	'P_VERSIONS',
	'TXT_CBLOCK_H_APPROVE'					=>	'P_VERSIONS',
	'TXT_CBLOCK_H_PUBLISH'					=>	'P_VERSIONS',
	'TXT_CBLOCK_H_PUBLISHED_UPDATED'		=>	'P_VERSIONS',
	'TXT_CBLOCK_H_AUTOPUBLISH'				=>	'P_VERSIONS',
	'TXT_CBLOCK_H_AUTOPUBLISH_ADDED'		=>	'P_PUBLISHING',
	'TXT_CBLOCK_H_AUTOPUBLISH_CHANGED'		=>	'P_PUBLISHING',
	'TXT_CBLOCK_H_AUTOPUBLISH_DELETED'		=>	'P_PUBLISHING',

	// FILES
	'TXT_FILE_H_ADDVIEW'					=>	'P_VIEWS',
	'TXT_FILE_H_REMOVEVIEW'					=>	'P_VIEWS',
	'TXT_FILE_H_CROP'						=>	'P_VIEWS',
	'TXT_FILE_H_PROP'						=>	'P_PROPERTIES',
	'TXT_FILE_H_PROP_CHECKON'				=>	'P_PROPERTIES',
	'TXT_FILE_H_PROP_CHECKOFF'				=>	'P_PROPERTIES',
	'TXT_FILE_H_PROP_FILE'					=>	'P_PROPERTIES',
	'TXT_FILE_H_PROP_RICHTEXT'				=>	'P_PROPERTIES',
	'TXT_FILE_H_PROP_TAG'					=>	'P_PROPERTIES',
	'TXT_FILE_H_PROP_CBLOCK'				=>	'P_PROPERTIES',
	'TXT_FILE_H_PROP_LINK'					=>	'P_PROPERTIES',
	'TXT_FILE_H_PROP_PAGE'					=>	'P_PROPERTIES',
	'TXT_FILE_H_PROP_DATE'					=>	'P_PROPERTIES',
	'TXT_FILE_H_PROP_DATETIME'				=>	'P_PROPERTIES',
	'TXT_FILE_H_PROP_PASSWORD'				=>	'P_PROPERTIES',
	'TXT_FILE_H_APPROVE'					=>	'P_VERSIONS',
	'TXT_FILE_H_NEWVERSION'					=>	'P_VERSIONS',
	'TXT_FILE_H_NEWVERSION_FROM'			=>	'P_VERSIONS',
	'TXT_FILE_H_TRASHED'					=>	'P_VERSIONS',
	'TXT_FILE_H_RESTORED'					=>	'P_VERSIONS',
	'TXT_FILE_H_TYPECHANGE'					=>	'P_FILEINFO',
	'TXT_FILE_H_REUPLOAD'					=>	'P_FILEINFO',
	'TXT_FILE_H_UPLOAD'						=>	'P_FILEINFO',
	'TXT_TAG_H_ASSIGN'						=>	'P_TAGS',
	'TXT_TAG_H_REMOVE'						=>	'P_TAGS',
	'TXT_TAG_H_TAGORDER'					=>	'P_TAGS',

	// PAGES
	'TXT_PAGE_H_COORDER'					=>	'P_CONTENT',
	'TXT_OBJECT_H_EXTREMOVE'				=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTADD'					=>	'P_EXTENSIONS',
	'TXT_PAGE_H_COREMOVE'					=>	'P_CONTENT',
	'TXT_PAGE_H_EMREMOVE'					=>	'P_CONTENT',
	'TXT_PAGE_H_COADD'						=>	'P_CONTENT',
	'TXT_PAGE_H_EMADD'						=>	'P_CONTENT',
	'TXT_PAGE_H_COORDER'					=>	'P_CONTENT',
	'TXT_PAGE_H_EMCOPY'						=>	'P_CONTENT',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_1'			=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_2'			=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_3'			=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_4_ON'		=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_4_OFF'		=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_5'			=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_6'			=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_7'			=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_8'			=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_9'			=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_10'		=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_11'		=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_12'		=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_13'		=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_14'		=>	'P_EXTENSIONS',
	'TXT_OBJECT_H_EXTEDIT_FRMFLD_15'		=>	'P_EXTENSIONS',
	'TXT_COMMON_H_COEDIT_FRMFLD_1'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_2'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_3'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_4_ON'		=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_4_OFF'		=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_5'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_6'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_7'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_8'			=>	'P_CONTENT',
	'TXT_COMMON_H_COEDIT_FRMFLD_9'			=>	'P_CONTENT',
	'TXT_PAGE_H_PROP'						=>	'P_PROPERTIES',
	'TXT_PAGE_H_PROP_CHECKON'				=>	'P_PROPERTIES',
	'TXT_PAGE_H_PROP_CHECKOFF'				=>	'P_PROPERTIES',
	'TXT_PAGE_H_PROP_FILE'					=>	'P_PROPERTIES',
	'TXT_PAGE_H_PROP_RICHTEXT'				=>	'P_PROPERTIES',
	'TXT_PAGE_H_PROP_TAG'					=>	'P_PROPERTIES',
	'TXT_PAGE_H_PROP_CBLOCK'				=>	'P_PROPERTIES',
	'TXT_PAGE_H_PROP_LINK'					=>	'P_PROPERTIES',
	'TXT_PAGE_H_PROP_PAGE'					=>	'P_PROPERTIES',
	'TXT_PAGE_H_PROP_DATE'					=>	'P_PROPERTIES',
	'TXT_PAGE_H_PROP_DATETIME'				=>	'P_PROPERTIES',
	'TXT_PAGE_H_PROP_PASSWORD'				=>	'P_PROPERTIES',
	'TXT_PAGE_H_PNAME'						=>	'P_PROPERTIES',
	'TXT_TAG_H_ASSIGN'						=>	'P_TAGS',
	'TXT_TAG_H_REMOVE'						=>	'P_TAGS',
	'TXT_TAG_H_TAGORDER'					=>	'P_TAGS',
	'TXT_PAGE_H_HIDDEN_0'					=>	'P_APPEARANCE',
	'TXT_PAGE_H_HIDDEN_1'					=>	'P_APPEARANCE',
	'TXT_PAGE_H_ACTIVE_0'					=>	'P_APPEARANCE',
	'TXT_PAGE_H_ACTIVE_1'					=>	'P_APPEARANCE',
	'TXT_PAGE_H_TEMPLATE'					=>	'P_APPEARANCE',
	'TXT_PAGE_H_NAVIGATION'					=>	'P_APPEARANCE',
	'TXT_PAGE_H_NONAVIGATION'				=>	'P_APPEARANCE',
	'TXT_PAGE_H_ACTIVE_0'					=>	'P_APPEARANCE',
	'TXT_PAGE_H_ACTIVE_1'					=>	'P_APPEARANCE',
	'TXT_PAGE_H_SUBPAGEORDER'				=>	'P_APPEARANCE',
	'TXT_PAGE_H_NEWVERSION'					=>	'P_VERSIONS',
	'TXT_PAGE_H_NEWVERSION_FROM'			=>	'P_VERSIONS',
	'TXT_PAGE_H_APPROVE'					=>	'P_VERSIONS',
	'TXT_PAGE_H_PUBLISH'					=>	'P_VERSIONS',
	'TXT_PAGE_H_PUBLISHED_UPDATED'			=>	'P_VERSIONS',
	'TXT_PAGE_H_AUTOPUBLISH'				=>	'P_VERSIONS',
	'TXT_PAGE_H_PUBLISH'					=>	'P_VERSIONS',
	'TXT_PAGE_H_TRASHED'					=>	'P_VERSIONS',
	'TXT_PAGE_H_RESTORED'					=>	'P_VERSIONS',
	'TXT_PAGE_H_AUTOPUBLISH_ADDED'			=>	'P_PUBLISHING',
	'TXT_PAGE_H_AUTOPUBLISH_CHANGED'		=>	'P_PUBLISHING',
	'TXT_PAGE_H_AUTOPUBLISH_DELETED'		=>	'P_PUBLISHING',
	'TXT_COMMON_H_COMMENT_ADD'				=>	'P_COMMENTS',
	'TXT_COMMON_H_COMMENT_CHANGE'			=>	'P_COMMENTS',
	'TXT_COMMON_H_COMMENT_REMOVE'			=>	'P_COMMENTS',
	'TXT_COMMON_H_COMMENT_APPROVED'			=>	'P_COMMENTS',
	'TXT_COMMON_H_COMMENT_MARKED_AS_SPAM'	=>	'P_COMMENTS',

	// EXTENSIONS
	'TXT_EXTENSION_H_LOGENTRY'				=>	'P_EXTENSIONS'
);

$tab_mappings_reverse = array();
foreach($tab_mappings as $tab_mappings_idx => $tab_mappings_item) {
	if (!is_array($tab_mappings_reverse[$tab_mappings_item])) {
		$tab_mappings_reverse[$tab_mappings_item] = array( $tab_mappings_idx );
	} else {
		array_push( $tab_mappings_reverse[$tab_mappings_item], $tab_mappings_idx );
	}
}

function sumchanges ($changeslist, $newchangeslist, $objecttype = "NONE", $cms) {
	$addwhere = count($changeslist);
	for ($i = 0; $i < count($newchangeslist); $i++) {
		if ($objecttype == "PAGES") {
			$PageMgr = new PageMgr($newchangeslist[$i]["SITE"]);
			if ($newchangeslist[$i]["OID"]) {
				$page = $PageMgr->getPage($newchangeslist[$i]["OID"]);
				if ($page) {
					$oidinfo = $page->get();
					$name = $oidinfo["NAME"];
				}
			}
		}
		if ($objecttype == "CONTENT") {
			if ($newchangeslist[$i]["OID"]) {
				$cb = sCblockMgr()->getCblock($newchangeslist[$i]["OID"]);
				$oidinfo = $cb->get();
				$name = $oidinfo["NAME"];
			}
		}
		if ($objecttype == "FILES") {
			if ($newchangeslist[$i]["OID"]) {
				$file = sFileMgr()->getFile($newchangeslist[$i]["OID"]);
				if ($file) {
					$oidinfo = $file->get();
					$name = $oidinfo["NAME"];
				}
			}
		}

		if (strlen($name) > 0) {
			$changeslist[$addwhere + $i] = $newchangeslist[$i];
			$changeslist[$addwhere + $i]["TYPE"] = $objecttype;
			$changeslist[$addwhere + $i]["NAME"] = $name;
		}
	}
	return $changeslist;
}
function sumchanges_new ($newchangeslist) {
	for ($i = 0; $i < count($newchangeslist); $i++) {
		if ($newchangeslist[$i]['TYPE'] == HISTORYTYPE_PAGE) {
			if ($newchangeslist[$i]["SITEID"] && $newchangeslist[$i]["OID"]) {
				try {
					$PageMgr = new PageMgr($newchangeslist[$i]["SITEID"]);
					$page = $PageMgr->getPage($newchangeslist[$i]["OID"]);
					if ($page) {
						$oidinfo = $page->get();
						$name = $oidinfo["NAME"];
					}
				}
				catch(Exception $ex){}
			}
		}
		if ($newchangeslist[$i]['TYPE'] == HISTORYTYPE_CO) {
			try {
				$cb = sCblockMgr()->getCblock($newchangeslist[$i]["OID"]);
				if ($cb) {
					$oidinfo = $cb->get();
					$name = $oidinfo["NAME"];
				}
			}
			catch(Exception $ex) {}
		}
		if ($newchangeslist[$i]['TYPE'] == HISTORYTYPE_FILE) {
			try {
				$file = sFileMgr()->getFile($newchangeslist[$i]["OID"]);
				if ($file) {
					$oidinfo = $file->get();
					$name = $oidinfo["NAME"];
				}
			}
			catch(Exception $ex) {}
		}
		if ($newchangeslist[$i]['TYPE'] == HISTORYTYPE_MAILING) {
			try {
				$mailing = sMailingMgr()->getMailing($newchangeslist[$i]["OID"]);
				if ($mailing) {
					$oidinfo = $mailing->get();
					$name = $oidinfo["NAME"];
				}
			}
			catch(Exception $ex) {}
		}

		if (strlen($name) > 0) {
			$changeslist[$i] = $newchangeslist[$i];
			$changeslist[$i]["NAME"] = $name;
		}
	}
	return $changeslist;
}

switch($objecttype) {
	case 'recent':
		// Number of entries
		$entries = 15;

		function timeStampSorter($a, $b) {
			if ($a['DATETIME'] < $b['DATETIME']) {
				return 1;
			} else if ($a['DATETIME'] > $b['DATETIME']) {
				return -1;
			} else {
				return 0;
			}
		}

		/*
		// Last changes from pages per site into an array
		$siteMgr = new Sites();
		$sites = $siteMgr->getList();
		$lastchanges['PAGES'] = array();
		$startx = 0;
		for ($i = 0; $i < count($sites); $i++) {
			$msites[] = $sites[$i];
			$PageMgr = new PageMgr($sites[$i]["ID"]);
			$changeslist = $PageMgr->history->getLastChanges(10);
			for ($x = $startx; $x < count($changeslist)+$startx; $x++) {
				$lastchanges['PAGES'][$x] = $changeslist[$x-$startx];
				$lastchanges['PAGES'][$x]['SITE'] = $sites[$i]['ID'];
			}
			$startx = $x;
		}

		// Sort pages by timestamp
		usort($lastchanges['PAGES'], 'timeStampSorter');

		$tmp_lastchanges_pages = $lastchanges['PAGES'];
		$tmp_entries = $entries;
		if ($tmp_entries > count($tmp_lastchanges_pages)) {
			$tmp_entries = count($tmp_lastchanges_pages);
		}
		$lastchanges['PAGES'] = array();

		for ($i=0;$i<$tmp_entries;$i++) {
			array_push($lastchanges['PAGES'], $tmp_lastchanges_pages[$i]);
		}

		// Last changes from content and files
		$lastchanges['CONTENT'] = sCblockMgr()->history->getLastChanges($entries);
		$lastchanges['FILES'] = sFileMgr()->history->getLastChanges($entries);

		$history = array();
		$history = sumchanges($history, $lastchanges['PAGES'], 'PAGES', $cms);
		$history = sumchanges($history, $lastchanges['CONTENT'], 'CONTENT', $cms);
		$history = sumchanges($history, $lastchanges['FILES'], 'FILES', $cms);
		*/

		$historyMgr = new History();
		$history_new = $historyMgr->getLastChanges($entries);
		$history = sumchanges_new($history_new);

		// Sort everything by timestamp
		usort($history, 'timeStampSorter');

		$real_history = array();
		for ($i = 0; $i < count($history); $i++) {

			$history[$i]['TAB'] = $tab_mappings[$history[$i]['TEXT']];

			$show_entry = false;
			if ($filterAction=='ONLY_VERSIONS') {
				if ($history[$i]['TAB'] == 'P_VERSIONS') {
					$show_entry = true;
				} else {
					$show_entry = false;
				}
			} else {
				$show_entry = true;
			}
			if ( $filterObject && ($filterObject!='ALL') ) {
				switch($filterObject) {
					case 'PAGES':
						$filterObjectTypeId = HISTORYTYPE_PAGE;
						break;
					case 'CONTENT':
						$filterObjectTypeId = HISTORYTYPE_CO;
						break;
					case 'FILES':
						$filterObjectTypeId = HISTORYTYPE_FILE;
						break;
				}
				if ( $filterObjectTypeId==$history[$i]['TYPE'] ) {
					$show_entry = true;
				} else {
					$show_entry = false;
				}
			}

			$user = new User($history[$i]['UID']);
			if ($user) {
				$uinfo = $user->get();
				$uinfo['PROPS'] = $user->properties->getValues( $history[$i]['UID'] );
				$history[$i]['USERNAME'] = trim($uinfo['PROPS']['FIRSTNAME'].' '.$uinfo['PROPS']['LASTNAME']);
				$history[$i]['USERID'] = $uinfo['ID'];
			}

			if ( ($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP') ||
				 ($history[$i]['TEXT'] == 'TXT_MAILING_H_PROP') ||
				 ($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP') ||
				 ($history[$i]['TEXT'] == 'TXT_FILE_H_PROP') ) {
				if (strpos($history[$i]['OLDVALUE'], 'TXT_') === 0) {
					if ($itext[$history[$i]['OLDVALUE']]) {
						$history[$i]['OLDVALUE'] = $itext[$history[$i]['OLDVALUE']];
					}
				}
			}

			if ($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP_FILE') {
				if ($history[$i]['NEWVALUE']) {
					$file = sFileMgr()->getFile($history[$i]['NEWVALUE']);
					if ($file) {
						$fileInfo = $file->get();
						if ($fileInfo) {
							$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$history[$i]['NEWVALUE'].'\', event);"><span style="display:inline-block;" class="filetype '.$fileInfo['COLOR'].'">'.$fileInfo['CODE'].'</span> '.$fileInfo['NAME'].'</span>';
						} else {
							$history[$i]['NEWVALUE'] = $itext['TXT_PAGE_H_PROP_FILE_REMOVED'];
						}
					}
				}
			}
			if ( ($history[$i]['TEXT'] == 'TXT_FILE_H_ADDVIEW') ||
				 ($history[$i]['TEXT'] == 'TXT_FILE_H_REMOVEVIEW') ) {
				$viewinfo = $viewMgr->get( $history[$i]['NEWVALUE'] );
				$history[$i]['NEWVALUE'] = $viewinfo['NAME'];
			}
			if ($history[$i]['TEXT'] == 'TXT_FILE_H_TYPECHANGE') {
				$filetypeinfo = $filetypeMgr->get( $history[$i]['NEWVALUE'] );
				$name = $filetypeinfo['NAME'];
				if ($name == 'DEFAULT') {
					$name = $itext['TXT_DEFAULT_FILETYPE'];
				}
				$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$history[$i]['NEWVALUE'].'\', event);"><span style="display:inline-block;" class="filetype '.$filetypeinfo['COLOR'].'">'.$filetypeinfo['CODE'].'</span>&nbsp;'.$name.'</span>';
			}
			if (($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP_RICHTEXT') ||
				($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP_RICHTEXT') ||
				($history[$i]['TEXT'] == 'TXT_FILE_H_PROP_RICHTEXT')) {
				$history[$i]['NEWVALUE'] = $itext['TXT_COMMON_H_COEDIT_FRMFLD_3'];
			}

			if ($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP_LINK') {
				$special_url = resolveSpecialURL($history[$i]['NEWVALUE']);
				if ($special_url !== false) {
					$target_aid = '';
					$target_id = '';
					$target_type = 0;

					$special_url_info = getSpecialURLInfo($history[$i]['NEWVALUE']);
					if ($special_url_info['TYPE']=='DOWN') {
						$target_type = 'FILE';
						$target_id = $special_url_info['ID'];
						if ($target_id) {
							$file = sFileMgr()->getFile($target_id);
							if ($file) {
								$objectInfo = $file->get();
								$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
							}
						}
					} else if ($special_url_info['TYPE']=='IMG') {
						$target_type = 'IMAGE';
						$target_id = $special_url_info['ID'];
						if ($target_id) {
							$file = sFileMgr()->getFile($target_id);
							if ($file) {
								$objectInfo = $file->get();
								$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
							}
						}
					} else {
						$target_type = 'PAGE';
						$oldsite = $siteID;
						if ($special_url_info['SITE'] && $special_url_info['ID']) {
							$iPageMgr = new PageMgr($special_url_info['SITE']);
							$iPage = $iPageMgr->getPage($special_url_info['ID']);
							if ($iPage) {
								$objectInfo = $iPage->get();
								$objectInfo['RWRITE'] = $iPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RWRITE");
								$objectInfo['RDELETE'] = $iPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RDELETE");
								$iconData = getIconForPage($objectInfo);
								$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$special_url_info['ID'].'-'.$special_url_info['SITE']."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
							}
						}
					}
				} else if (preg_match_all($this->URLRegEx1, $history[$i]['NEWVALUE'], $internal) > 0) {
					$target_aid = '';
					$target_id = '';
					$target_type = 0;

					if ($internal[2][0] == 'download') {
						$target_type = 'FILE';
						$target_id = $internal[3][0];
						if ($target_id) {
							$file = sFileMgr()->getFile($target_id);
							if ($file) {
								$objectInfo = $file->get();
								$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
							}
						}
					} else if ($internal[2][0] == 'page') {
						preg_match_all($this->URLRegEx2, $history[$i]['NEWVALUE'], $linkinfo);
						$target_type = 'PAGE';
						$oldsite = $siteID;
						if ($linkinfo[3][0] && $linkinfo[4][0]) {
							$iPageMgr = new PageMgr($linkinfo[3][0]);
							$iPage = $iPageMgr->getPage($linkinfo[4][0]);
							if ($iPage) {
								$objectInfo = $iPage->get();
								$objectInfo['RWRITE'] = $iPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RWRITE");
								$objectInfo['RDELETE'] = $iPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RDELETE");
								$iconData = getIconForPage($objectInfo);
								$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$linkinfo[4][0].'-'.$linkinfo[3][0]."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
							}
						}
					} else if ($internal[2][0] == 'image') {
						$target_type = 'IMAGE';
						$target_id = $internal[3][0];
					}
				} else if (strpos($history[$i]['NEWVALUE'], 'mailto:') === 0) {
					$history[$i]['NEWVALUE'] = '<div class="iconemail"></div>'.str_replace( 'mailto:', '', $history[$i]['NEWVALUE'] );
				} else {
					$linkInfo = checkLinkInternalExternal( $history[$i]['NEWVALUE'] );
					switch($linkInfo['TYPE']) {
						case 'external':
							$history[$i]['NEWVALUE'] = '<div class="iconlink"></div>'.$history[$i]['NEWVALUE'];
							break;
						case 'internal':
							$target_type = 'PAGE';
							$history[$i]['NEWVALUE'] = '<div class="iconpage"></div>'.$linkInfo['NAME'];
							break;
						case 'file':
							$target_type = 'FILE';
							$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$linkInfo['INFO']['FILE_ID'].'\', event);"><span style="display:inline-block;" class="filetype '.$linkInfo['INFO']['COLOR'].'">'.$linkInfo['INFO']['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$linkInfo['INFO']['FILE_ID']."', 'file', '".$linkInfo['NAME']."', {color:'".$linkInfo['INFO']['COLOR']."',typecode:'".$linkInfo['INFO']['CODE']."'});\">".$linkInfo['NAME']."</a></span>";
							break;
					}
				}
			}

			// For Formfieldinfo (entrymasks)
			if ( ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_1') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_2') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_3') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_4_ON') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_4_OFF') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_5') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_6') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_7') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_8') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_9') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_10') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_11') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_12') ||
				 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_15') ) {

				// Get formfield <-> control link
				$formfield_lnk = sCblockMgr()->getCblockLinkByEntrymaskLinkId( $history[$i]['TARGETID'] );
				$emblock_id = $formfield_lnk[0]['CBLOCKID'];

				// Get name for contentarea
				$contentareaInfo = sTemplates()->getContentareaById( $history[$i]['FROM'] );
				$contentarea_name = $contentareaInfo['NAME'];

				// Get Name of Formfield
				$lnkInfo = sCblockMgr()->getEntrymaskLinkByEntrymaskLinkId( $history[$i]['TARGETID'] );
				$coFormfield = $entrymaskMgr->getFormfield( $lnkInfo[0]['ENTRYMASKFORMFIELD'] );
				$formfield_name = $coFormfield['NAME'];

				// Get Name of entrymask
				$tmpCb = sCblockMgr()->getCblock($formfield_lnk[0]['CBLOCKID']);
				if ($tmpCb) {
					$control_name = $tmpCb->properties->getValue('NAME');

					if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_5') {
						$special_url = resolveSpecialURL($history[$i]['NEWVALUE']);
						if ($special_url !== false) {
							$target_aid = '';
							$target_id = '';
							$target_type = 0;

							$special_url_info = getSpecialURLInfo($history[$i]['NEWVALUE']);
							if ($special_url_info['TYPE']=='DOWN') {
								$target_type = 'FILE';
								$target_id = $special_url_info['ID'];
								if ($target_id) {
									$file = sFileMgr()->getFile($target_id);
									if ($file) {
										$objectInfo = $file->get();
										$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
									}
								}
							} else if ($special_url_info['TYPE']=='IMG') {
								$target_type = 'IMAGE';
								$target_id = $special_url_info['ID'];
								if ($target_id) {
									$file = sFileMgr()->getFile($target_id);
									if ($file) {
										$objectInfo = $file->get();
										$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
									}
								}
							} else {
								$target_type = 'PAGE';
								$oldsite = $siteID;
								if ($special_url_info['SITE'] && $special_url_info['ID']) {
									$iPageMgr = new PageMgr($special_url_info['SITE']);
									$iPage = $iPageMgr->getPage($special_url_info['ID']);
									if ($iPage) {
										$objectInfo = $iPage->get();
										$objectInfo['RWRITE'] = $iPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RWRITE");
										$objectInfo['RDELETE'] = $iPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RDELETE");
										$iconData = getIconForPage($objectInfo);
										$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$special_url_info['ID'].'-'.$special_url_info['SITE']."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
									}
								}
							}
						} else if (preg_match_all($this->URLRegEx1, $history[$i]['NEWVALUE'], $internal) > 0) {
							$target_aid = '';
							$target_id = '';
							$target_type = 0;

							if ($internal[2][0] == 'download') {
								$target_type = 'FILE';
								$target_id = $internal[3][0];
								if ($target_id) {
									$file = sFileMgr()->getFile($target_id);
									if ($file) {
										$objectInfo = $file->get();
										$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
									}
								}
							} else if ($internal[2][0] == 'page') {
								preg_match_all($this->URLRegEx2, $history[$i]['NEWVALUE'], $linkinfo);
								$target_type = 'PAGE';
								$oldsite = $siteID;
								if ($linkinfo[3][0] && $linkinfo[4][0]) {
									$iPageMgr = new PageMgr($linkinfo[3][0]);
									$iPage = $iPageMgr->getPage($linkinfo[4][0]);
									if ($iPage) {
										$objectInfo = $iPage->get();
										$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RWRITE");
										$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RDELETE");
										$iconData = getIconForPage($objectInfo);
										$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$linkinfo[4][0].'-'.$linkinfo[3][0]."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
									}
								}
							} else if ($internal[2][0] == 'image') {
								$target_type = 'IMAGE';
								$target_id = $internal[3][0];
							}
						} else if (strpos($history[$i]['NEWVALUE'], 'mailto:') === 0) {
							$history[$i]['NEWVALUE'] = '<div class="iconemail"></div>'.str_replace( 'mailto:', '', $history[$i]['NEWVALUE'] );
						} else {
							$linkInfo = checkLinkInternalExternal( $history[$i]['NEWVALUE'] );
							switch($linkInfo['TYPE']) {
								case 'external':
									$history[$i]['NEWVALUE'] = '<div class="iconlink"></div>'.$history[$i]['NEWVALUE'];
									break;
								case 'internal':
									$target_type = 'PAGE';
									$history[$i]['NEWVALUE'] = '<div class="iconpage"></div>'.$linkInfo['NAME'];
									break;
								case 'file':
									$target_type = 'FILE';
									$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$linkInfo['INFO']['FILE_ID'].'\', event);"><span style="display:inline-block;" class="filetype '.$linkInfo['INFO']['COLOR'].'">'.$linkInfo['INFO']['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$linkInfo['INFO']['FILE_ID']."', 'file', '".$linkInfo['NAME']."', {color:'".$linkInfo['INFO']['COLOR']."',typecode:'".$linkInfo['INFO']['CODE']."'});\">".$linkInfo['NAME']."</a></span>";
									break;
							}
						}
					}
				}

				$history[$i]['CONTENTAREA'] = $contentarea_name;
				$history[$i]['FORMFIELD'] = $formfield_name;
				$history[$i]['EMBLOCK'] = $control_name;
				$history[$i]['TYPE'] = 'COEDIT';

			}

			switch($history[$i]['TYPE']) {
				case 'COEDIT':
				case HISTORYTYPE_PAGE:
					try {
						if ($history[$i]['SITEID']) {
							$PageMgr = new PageMgr( $history[$i]['SITEID']);
							if ($PageMgr) {
								$history[$i]['PARENTS'] = $PageMgr->getParents($history[$i]['OID']);
								$hPage = $PageMgr->getPage($history[$i]['OID']);

								if ($hPage && $hPage->permissions) {
									$hPageInfo = $hPage->get();
									$hPageInfo['RWRITE'] = $hPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['OID'], "RWRITE");
									$hPageInfo['RDELETE'] = $hPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['OID'], "RDELETE");
									$history[$i]['PAGEINFO'] = $hPageInfo;
									$iconData = getIconForPage($hPageInfo);
									$history[$i]['ICON'] = $iconData['iconclass'];
									$history[$i]['STYLE'] = $iconData['style'];
									$history[$i]['HASCHANGED'] = $history[$i]['PAGEINFO']['HASCHANGED'];
								}
							}
						}
					}
					catch(Exception $ex) {}
					break;
				case HISTORYTYPE_CO:
					try {
						$cb = sCblockMgr()->getCblock($history[$i]['OID']);
						$history[$i]['PARENTS'] = sCblockMgr()->getParents( $history[$i]['OID'] );
						$history[$i]['CBLOCKINFO'] = $cb->get();
						$history[$i]['CBLOCKINFO']['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['OID'], "RWRITE");
						$history[$i]['CBLOCKINFO']['RDELETE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['OID'], "RDELETE");
						$styleData = getStyleForContentblock($history[$i]['CBLOCKINFO']);
						$history[$i]['STYLE'] = $styleData;
						$history[$i]['HASCHANGED'] = $history[$i]['CBLOCKINFO']['HASCHANGED'];
						array_pop( $history[$i]['PARENTS'] );
					}
					catch(Exception $ex) {}
					break;
				case HISTORYTYPE_FILE:
					try {
						$history[$i]['PARENTS'] = sFileMgr()->getParents( $history[$i]['OID'] );
						array_pop( $history[$i]['PARENTS'] );
						$file = sFileMgr()->getFile($history[$i]['OID']);
						if ($file) {
							$history[$i]['FILEINFO'] = $file->get();
						}
					}
					catch(Exception $ex) {}
					break;
			}
			if ($show_entry) {
				array_push( $real_history, $history[$i] );
			}
		}
		break;

	case 'mailing':
		$templateMgr = new Templates();
		$mailingMgr = new MailingMgr();
		if ($objectID) {
			$mailing = $mailingMgr->getMailing($objectID);
			$objectInfo = $mailing->get();
			$objectInfo['RWRITE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
			$objectInfo['RSTAGE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

			$versions = $mailing->getVersions();
			for ($i = 0; $i < count($versions); $i++) {
				$uid = $versions[$i]["CREATEDBY"];
				$user = new User($uid);
				$uinfo = &$user->get();
				$uinfo['PROPS'] = $user->properties->getValues( $uid );
				$versions[$i]["USERNAME"] = $uinfo['PROPS']["LASTNAME"];
				$versions[$i]["VORNAME"] = $uinfo['PROPS']["FIRSTNAME"];
			}
			$smarty->assign("versions", $versions);

			$history = $mailing->history->getList($objectID);

			$real_history = array();
			$real_history_cnt = 0;
			for ($i = 0; $i < count($history); $i++) {
				if ($lastuserid <> $history[$i]["UID"]) {
					$user = new User($history[$i]["UID"]);
					$uinfo = &$user->get();
					$uinfo['PROPS'] = $user->properties->getValues( $history[$i]["UID"] );
				}
				$history[$i]["USERNAME"] = trim($uinfo['PROPS']["FIRSTNAME"]." ".$uinfo['PROPS']["LASTNAME"]);
				$history[$i]["USERID"] = $uinfo["ID"];
				$history[$i]['TAB'] = $tab_mappings[$history[$i]['TEXT']];

				if ($history[$i]['TAB'] == 'P_TAGS') {
					if ( ($history[$i]['TEXT'] == 'TXT_TAG_H_ASSIGN') ||
						 ($history[$i]['TEXT'] == 'TXT_TAG_H_REMOVE') ) {
						 	$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['TARGETID']."', 'tag', '".$history[$i]['NEWVALUE']."', 'tag', '');\">".$history[$i]['NEWVALUE']."</a>";
					}
				}

				if ($history[$i]['TAB'] == 'P_CONTENT') {
					if ( ($history[$i]['TEXT'] == 'TXT_MAILING_H_COREMOVE') ||
					 	 ($history[$i]['TEXT'] == 'TXT_MAILING_H_EMREMOVE') ||
						 ($history[$i]['TEXT'] == 'TXT_MAILING_H_COADD') ||
						 ($history[$i]['TEXT'] == 'TXT_MAILING_H_EMADD') ||
						 ($history[$i]['TEXT'] == 'TXT_MAILING_H_EMCOPY') ||
						 ($history[$i]['TEXT'] == 'TXT_MAILING_H_COORDER') ) {

	 					 	$Tmp_TheMailingMgr = new MailingMgr();
	 					 	if ($objectID) {
	 					 		$Tmp_mailing = $Tmp_TheMailingMgr->getMailing($objectID);
	 					 		$Tmp_mailingInfo = $Tmp_mailing->get();
	 					 		$templateId = $Tmp_mailingInfo["TEMPLATEID"];
	 					 		$Tmp_TheTemplateMgr = new Templates();
	 					 		$tmp_contentareas = $Tmp_TheTemplateMgr->getContentareas( $templateId );
	 					 		$history[$i]['CONTENTAREA'] = $history[$i]['OLDVALUE'];
	 					 		unset($realContentareaName);
	 					 		foreach($tmp_contentareas as $tmp_contentarea) {
	 					 			if ($tmp_contentarea['CODE'] == $history[$i]['CONTENTAREA']) {
	 					 				$realContentareaName = $tmp_contentarea['NAME'];
	 					 			}
	 					 		}
	 					 		if ($realContentareaName) {
	 					 			$history[$i]['CONTENTAREA'] = $realContentareaName;
	 					 		}

	 					 		$history[$i]['TYPE'] = 'COACTION';

	 					 		// For contentblocks
	 					 		if ( ($history[$i]['TEXT'] == 'TXT_MAILING_H_COREMOVE') ||
	 					 		($history[$i]['TEXT'] == 'TXT_MAILING_H_COADD') ) {
	 					 			if ($history[$i]['TARGETID']) {
	 					 				$cb = sCblockMgr()->getCblock($history[$i]['TARGETID']);
	 					 				$cblockInfo = $cb->get();
	 					 				$cblockInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['TARGETID'], "RWRITE");
	 					 				$cblockInfo['RDELETE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['TARGETID'], "RDELETE");
	 					 				$styleData = getStyleForContentblock($cblockInfo, true);
	 					 				$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['TARGETID']."', 'cblock', '".$history[$i]['NEWVALUE']."', 'cblock', '".$styleData."');\">".$history[$i]['NEWVALUE']."</a>";
	 					 			}
	 					 		}
	 					 	}

					}

					// For Formfieldinfo (entrymasks)
					if ( ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_1') ||
					 	 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_2') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_3') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_4_ON') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_4_OFF') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_5') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_6') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_7') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_8') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_9') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_10') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_11') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_12') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_15') ) {

							// Get formfield <-> control link
							$formfield_lnk = sCblockMgr()->getCblockLinkByEntrymaskLinkId( $history[$i]['TARGETID'] );
							$emblock_id = $formfield_lnk[0]['CBLOCKID'];

							// Get Name of entrymask
							if ($formfield_lnk[0]['CBLOCKID']) {
								$tmpCb = sCblockMgr()->getCblock($formfield_lnk[0]['CBLOCKID']);
								$control_name = $tmpCb->properties->getValue('NAME');

								// Get Name of Formfield
								$lnkInfo = sCblockMgr()->getEntrymaskLinkByEntrymaskLinkId( $history[$i]['TARGETID'] );
								$coFormfield = $entrymaskMgr->getFormfield( $lnkInfo[0]['ENTRYMASKFORMFIELD'] );
								$formfield_name = $coFormfield['NAME'];

								// Get contentblock <-> page link
								$page_lnk = $mailing->getCblockLinkById( $formfield_lnk[0]['CBLOCKID'] );

								// Get name for contentarea
								$contentareaInfo = $templateMgr->getContentareaById( $history[$i]['FROM'] );
								$contentarea_name = $contentareaInfo['NAME'];

								// Special Case for Files
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_6') {
									if ($history[$i]['NEWVALUE']) {
										$file = sFileMgr()->getFile($history[$i]['NEWVALUE']);
										if ($file) {
											$objectInfo = $file->get();
											$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$history[$i]['NEWVALUE'].'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
										}
									}
								}

								// Special Case for Links
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_5') {
									$special_url = resolveSpecialURL($history[$i]['NEWVALUE']);
									if ($special_url !== false) {
										$target_aid = '';
										$target_id = '';
										$target_type = 0;

										$special_url_info = getSpecialURLInfo($history[$i]['NEWVALUE']);
										if ($special_url_info['TYPE']=='DOWN') {
											$target_type = 'FILE';
											$target_id = $special_url_info['ID'];
											if ($target_id) {
												$file = sFileMgr()->getFile($target_id);
												if ($file) {
													$objectInfo = $file->get();
													$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
												}
											}
										} else if ($special_url_info['TYPE']=='IMG') {
											$target_type = 'IMAGE';
											$target_id = $special_url_info['ID'];
											if ($target_id) {
												$file = sFileMgr()->getFile($target_id);
												if ($file) {
													$objectInfo = $file->get();
													$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
												}
											}
										} else {
											$target_type = 'PAGE';
											$oldsite = $siteID;
											if ($special_url_info['SITE'] && $special_url_info['ID']) {
												$iPageMgr = new PageMgr($special_url_info['SITE']);
												$iPage = $iPageMgr->getPage($special_url_info['ID']);
												if ($iPage) {
													$objectInfo = $iPage->get();
													$objectInfo['RWRITE'] = $iPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RWRITE");
													$objectInfo['RDELETE'] = $iPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RDELETE");
													$iconData = getIconForPage($objectInfo);
													$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$special_url_info['ID'].'-'.$special_url_info['SITE']."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
												}
											}
										}
									} else if (preg_match_all($this->URLRegEx1, $history[$i]['NEWVALUE'], $internal) > 0) {
										$target_aid = '';
										$target_id = '';
										$target_type = 0;

										if ($internal[2][0] == 'download') {
											$target_type = 'FILE';
											$target_id = $internal[3][0];
											if ($target_id) {
												$file = sFileMgr()->getFile($target_id);
												if ($file) {
													$objectInfo = $file->get();
													$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
												}
											}
										} else if ($internal[2][0] == 'page') {
											preg_match_all($this->URLRegEx2, $history[$i]['NEWVALUE'], $linkinfo);
											$target_type = 'PAGE';
											$oldsite = $siteID;
											if ($linkinfo[3][0] && $linkinfo[4][0]) {
												$iPageMgr = new PageMgr($linkinfo[3][0]);
												$iPage = $iPageMgr->getPage($linkinfo[4][0]);
												if ($iPage) {
													$objectInfo = $iPage->get();
													$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RWRITE");
													$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RDELETE");
													$iconData = getIconForPage($objectInfo);
													$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$linkinfo[4][0].'-'.$linkinfo[3][0]."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
												}
											}
										} else if ($internal[2][0] == 'image') {
											$target_type = 'IMAGE';
											$target_id = $internal[3][0];
										}
									} else if (strpos($history[$i]['NEWVALUE'], 'mailto:') === 0) {
										$history[$i]['NEWVALUE'] = '<div class="iconemail"></div>'.str_replace( 'mailto:', '', $history[$i]['NEWVALUE'] );
									} else {
										$linkInfo = checkLinkInternalExternal( $history[$i]['NEWVALUE'] );
										switch($linkInfo['TYPE']) {
											case 'external':
												$history[$i]['NEWVALUE'] = '<div class="iconlink"></div>'.$history[$i]['NEWVALUE'];
												break;
											case 'internal':
												$target_type = 'PAGE';
												$history[$i]['NEWVALUE'] = '<div class="iconpage"></div>'.$linkInfo['NAME'];
												break;
											case 'file':
												$target_type = 'FILE';
												$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$linkInfo['INFO']['FILE_ID'].'\', event);"><span style="display:inline-block;" class="filetype '.$linkInfo['INFO']['COLOR'].'">'.$linkInfo['INFO']['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$linkInfo['INFO']['FILE_ID']."', 'file', '".$linkInfo['NAME']."', {color:'".$linkInfo['INFO']['COLOR']."',typecode:'".$linkInfo['INFO']['CODE']."'});\">".$linkInfo['NAME']."</a></span>";
												break;
										}
									}

								}

								// Special Case for Contentblocks
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_7') {
									if ($history[$i]['NEWVALUE']) {
										$cb = sCblockMgr()->getCblock($history[$i]['NEWVALUE']);
										$cblockInfo = $cb->get();
										$cblockInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
										$cblockInfo['RDELETE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");
										$styleData = getStyleForContentblock($cblockInfo, true);
										$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'cblock', '".$cblockInfo['NAME']."', 'cblock', '".$styleData."');\">".$cblockInfo['NAME']."</a>";
									}
								}

								// Special Case for Tags
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_8') {
									$tmpTags = new Tags();
									$tagInfo = $tmpTags->get($history[$i]['NEWVALUE']);
									$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'tag', '".$tagInfo['NAME']."', 'tag', '');\">".$tagInfo['NAME']."</a>";
								}

								// Special Case for Date
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_11') {
									$history[$i]['NEWVALUE'] = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
								}

								// Special Case for Datetime
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_12') {
									$dateString = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
									$timeString = date('H', TStoLocalTS($history[$i]['NEWVALUE'])).':'.date('i', TStoLocalTS($history[$i]['NEWVALUE']));
									$history[$i]['NEWVALUE'] = $dateString.' '.$timeString;
								}

								// Special Case for Pages
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_15') {
									$currSiteID = explode( '-', $history[$i]['NEWVALUE'] );
									$currPageID = $currSiteID[0];
									$currSiteID = $currSiteID[1];
									if ($currPageID && $currSiteID) {
										$currPageMgr = new PageMgr($currSiteID);
										$currPage = $currPageMgr->getPage($currPageID);
										if ($currPage) {
											$currPageInfo = $currPage->get();
											$currPageInfo['RWRITE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
											$currPageInfo['RDELETE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");
											$iconData = getIconForPage($currPageInfo);
											$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."-".$history[$i]['TARGETID']."', 'page', '".$currPageInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$currPageInfo['NAME']."</a>";
										}
									}
								}

								$history[$i]['CONTENTAREA'] = $contentarea_name;
								$history[$i]['FORMFIELD'] = $formfield_name;
								$history[$i]['EMBLOCK'] = $control_name;
								$history[$i]['TYPE'] = 'COEDIT';
							}
					}
				}
				/*
				if ($history[$i]['TAB'] == 'P_PROPERTIES') {
					if ( ($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP') ) {
						$history[$i]['CONTENTAREA'] = $history[$i]['OLDVALUE'];
					}
				}
				*/

				if ($history[$i]['TEXT'] == 'TXT_MAILING_H_PROP_FILE') {
					if ($history[$i]['NEWVALUE']) {
						$file = sFileMgr()->getFile($history[$i]['NEWVALUE']);
						if ($file) {
							$fileInfo = $file->get();
							if ($fileInfo) {
								$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$history[$i]['NEWVALUE'].'\', event);"><span style="display:inline-block;" class="filetype '.$fileInfo['COLOR'].'">'.$fileInfo['CODE'].'</span> '.$fileInfo['NAME'].'</span>';
							} else {
								$history[$i]['NEWVALUE'] = $itext['TXT_MAILING_H_PROP_FILE_REMOVED'];
							}
						}
					}
				}

				if ($history[$i]['TEXT'] == 'TXT_MAILING_H_PROP') {
					if (strpos($history[$i]['OLDVALUE'], 'TXT_') === 0) {
						if ($itext[$history[$i]['OLDVALUE']]) {
							$history[$i]['OLDVALUE'] = $itext[$history[$i]['OLDVALUE']];
						}
					}
				}
				if ($history[$i]['TEXT'] == 'TXT_MAILING_H_PROP_RICHTEXT') {
					$history[$i]['NEWVALUE'] = $itext['TXT_COMMON_H_COEDIT_FRMFLD_3'];
				}
				if 	($history[$i]['TEXT'] == 'TXT_MAILING_H_PROP_TAG') {
					$tagInfo = $tagMgr->get($history[$i]['NEWVALUE']);
					$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'tag', '".$tagInfo['NAME']."', 'tag', '');\">".$tagInfo['NAME']."</a>";
				}
				if 	($history[$i]['TEXT'] == 'TXT_MAILING_H_PROP_CBLOCK') {
					if ($history[$i]['NEWVALUE']) {
						$cb = sCblockMgr()->getCblock($history[$i]['NEWVALUE']);
						$cblockInfo = $cb->get();
						$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'cblock', '".$cblockInfo['NAME']."', 'cblock', '');\">".$cblockInfo['NAME']."</a>";
					}
				}
				if 	($history[$i]['TEXT'] == 'TXT_MAILING_H_PROP_PAGE') {
					if ($history[$i]['TARGETID'] && $history[$i]['NEWVALUE']) {
						$currPageMgr = new PageMgr($history[$i]['TARGETID']);
						$currPage = $currPageMgr->getPage($history[$i]['NEWVALUE']);
						if ($currPage) {
							$currPageInfo = $currPage->get();
							$currPageInfo['RWRITE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
							$currPageInfo['RDELETE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");
							$iconData = getIconForPage($currPageInfo);
							$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."-".$history[$i]['TARGETID']."', 'page', '".$currPageInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$currPageInfo['NAME']."</a>";
						}
					}
				}
				if 	($history[$i]['TEXT'] == 'TXT_MAILING_H_PROP_DATE') {
					$history[$i]['NEWVALUE'] = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
				}
				if 	($history[$i]['TEXT'] == 'TXT_MAILING_H_PROP_DATETIME') {
					$dateString = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
					$timeString = date('H', TStoLocalTS($history[$i]['NEWVALUE'])).':'.date('i', TStoLocalTS($history[$i]['NEWVALUE']));
					$history[$i]['NEWVALUE'] = $dateString.' '.$timeString;
				}
				if 	($history[$i]['TEXT'] == 'TXT_MAILING_H_PROP_LINK') {
					$special_url = resolveSpecialURL($history[$i]['NEWVALUE']);
					if ($special_url !== false) {
						$target_aid = '';
						$target_id = '';
						$target_type = 0;

						$special_url_info = getSpecialURLInfo($history[$i]['NEWVALUE']);
						if ($special_url_info['TYPE']=='DOWN') {
							$target_type = 'FILE';
							$target_id = $special_url_info['ID'];
							if ($target_id) {
								$file = sFileMgr()->getFile($target_id);
								if ($file) {
									$objectInfo = $file->get();
									$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
								}
							}
						} else if ($special_url_info['TYPE']=='IMG') {
							$target_type = 'IMAGE';
							$target_id = $special_url_info['ID'];
							if ($target_id) {
								$file = sFileMgr()->getFile($target_id);
								if ($file) {
									$objectInfo = $file->get();
									$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
								}
							}
						} else {
							$target_type = 'PAGE';
							$oldsite = $siteID;
							if ($special_url_info['SITE'] && $special_url_info['ID']) {
								$iPageMgr = new PageMgr($special_url_info['SITE']);
								$iPage = $iPageMgr->getPage($special_url_info['ID']);
								if ($iPage) {
									$objectInfo = $iPage->get();
									$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RWRITE");
									$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RDELETE");
									$iconData = getIconForPage($objectInfo);
									$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$special_url_info['ID'].'-'.$special_url_info['SITE']."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
								}
							}
						}
					} else if (preg_match_all($this->URLRegEx1, $history[$i]['NEWVALUE'], $internal) > 0) {
						$target_aid = '';
						$target_id = '';
						$target_type = 0;

						if ($internal[2][0] == 'download') {
							$target_type = 'FILE';
							$target_id = $internal[3][0];
							if ($target_id) {
								$file = sFileMgr()->getFile($target_id);
								if ($file) {
									$objectInfo = $file->get();
									$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
								}
							}
						} else if ($internal[2][0] == 'page') {
							preg_match_all($this->URLRegEx2, $history[$i]['NEWVALUE'], $linkinfo);
							$target_type = 'PAGE';
							$oldsite = $siteID;
							if ($linkinfo[3][0] && $linkinfo[4][0]) {
								$iPageMgr = new PageMgr($linkinfo[3][0]);
								$iPage = $iPageMgr->getPage($linkinfo[4][0]);
								if ($iPage) {
									$objectInfo = $iPage->get();
									$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RWRITE");
									$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RDELETE");
									$iconData = getIconForPage($objectInfo);
									$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$linkinfo[4][0].'-'.$linkinfo[3][0]."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
								}
							}
						} else if ($internal[2][0] == 'image') {
							$target_type = 'IMAGE';
							$target_id = $internal[3][0];
						}
					} else if (strpos($history[$i]['NEWVALUE'], 'mailto:') === 0) {
						$history[$i]['NEWVALUE'] = '<div class="iconemail"></div>'.str_replace( 'mailto:', '', $history[$i]['NEWVALUE'] );
					} else {
						$linkInfo = checkLinkInternalExternal( $history[$i]['NEWVALUE'] );
						switch($linkInfo['TYPE']) {
							case 'external':
								$history[$i]['NEWVALUE'] = '<div class="iconlink"></div>'.$history[$i]['NEWVALUE'];
								break;
							case 'internal':
								$target_type = 'PAGE';
								$history[$i]['NEWVALUE'] = '<div class="iconpage"></div>'.$linkInfo['NAME'];
								break;
							case 'file':
								$target_type = 'FILE';
								$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$linkInfo['INFO']['FILE_ID'].'\', event);"><span style="display:inline-block;" class="filetype '.$linkInfo['INFO']['COLOR'].'">'.$linkInfo['INFO']['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$linkInfo['INFO']['FILE_ID']."', 'file', '".$linkInfo['NAME']."', {color:'".$linkInfo['INFO']['COLOR']."',typecode:'".$linkInfo['INFO']['CODE']."'});\">".$linkInfo['NAME']."</a></span>";
								break;
						}
					}
				}

				// For Extension-Logging
				if ($history[$i]['TEXT'] == 'TXT_EXTENSION_H_LOGENTRY') {
					$extensionManager = new ExtensionMgr();
					$extensionInfo = $extensionManager->get($history[$i]['OLDVALUE']);
					$history[$i]['NEWVALUE'] = '<div class="modified">'.$extensionInfo['NAME'].' <em>'.$history[$i]['NEWVALUE'].'</em></div>';
				}

				$lastuserid = $history[$i]["UID"];

				$tKey = $history[$i]['TEXT'];
				$pKey = $history[$i]['TAB'];
				$dKey = $history[$i]['DATETIME'];

				// Only get history entries for Yeager
				if ( strpos($tKey, 'TXT_') === 0 ) {

					$show_entry = false;

					if ($filterAction=='ONLY_VERSIONS') {
						if ($pKey == 'P_VERSIONS') {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else if ($filterTab!='ALL') {
						if ($pKey==$filterTab) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else {
						$show_entry = true;
					}

					if ($filterTimeframe=='LAST_WEEK') {
						if ( ((time()-604800) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_2_WEEKS') {
						if ( ((time()-1209600) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_4_WEEKS') {
						if ( ((time()-2419200) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_8_WEEKS') {
						if ( ((time()-4838400) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else {

						// custom timeframe
						list ($timefrom, $timetill) = explode("###", $filterTimeframe);

						$timefrom = TSfromLocalTS(strtotime($timefrom));
						$timetill = TSfromLocalTS(strtotime($timetill) + 24*60*60);

						if ( ($dKey > $timefrom) && ($dKey < $timetill)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}

					}

					// Check for autopublish changes in a row (within 300 secs, and reduce them to one entry)
					if ( (($tKey=='TXT_MAILING_H_AUTOPUBLISH_CHANGED') || ($tKey=='TXT_MAILING_H_AUTOPUBLISH_ADDED')) &&
						 (($last_entry_type=='TXT_MAILING_H_AUTOPUBLISH_ADDED') || ($last_entry_type=='TXT_MAILING_H_AUTOPUBLISH_CHANGED')) &&
						 ($last_entry_item_id==$history[$i]['TARGETID']) &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {

								if (!$test)
								$show_entry = false;

							}
							if ($tKey=='TXT_MAILING_H_AUTOPUBLISH_ADDED') {
								$add_in_row = true;
							}
					}

					// Check for tagorder changes in a row (within 300 secs, and reduce them to one entry)
					if ( ($tKey=='TXT_TAG_H_TAGORDER') &&
						 ($last_entry_type=='TXT_TAG_H_TAGORDER') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					// Check for contentblock order changes in a row (within 300 secs, and reduce them to one entry)
					if ( ($tKey=='TXT_MAILING_H_COORDER') &&
						 ($last_entry_type=='TXT_MAILING_H_COORDER') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					// Check for datetime changes in a row (within 300 secs, and reduce them to one entry) (Properties)
					if ( ($tKey=='TXT_MAILING_H_PROP_DATETIME') &&
						 ($last_entry_type=='TXT_MAILING_H_PROP_DATETIME') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					// Check for datetime changes in a row (within 300 secs, and reduce them to one entry) (Controls)
					if ( ($tKey=='TXT_COMMON_H_COEDIT_FRMFLD_12') &&
						 ($last_entry_type=='TXT_COMMON_H_COEDIT_FRMFLD_12') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					// Check for datetime changes in a row (within 300 secs, and reduce them to one entry) (Extensions)
					if ( ($tKey=='TXT_MAILING_H_EXTEDIT_FRMFLD_12') &&
						 ($last_entry_type=='TXT_MAILING_H_EXTEDIT_FRMFLD_12') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					$last_entry_type = $tKey;
					$last_entry_item_id = $history[$i]['TARGETID'];
					$last_entry_timestamp = $dKey;
					$last_entry_userid = $history[$i]['USERID'];

					if ($show_entry) {
						$add_in_row = false;
						$real_history[$real_history_cnt] = $history[$i];
						$real_history_cnt++;
					}

					if (!$test)
					if ($add_in_row) {
						$index = $real_history_cnt-1;
						if ($index >= 0) {
							if ($real_history[$index]['TEXT']=='TXT_MAILING_H_AUTOPUBLISH_CHANGED') {
								$real_history[$index]['TEXT'] = 'TXT_MAILING_H_AUTOPUBLISH_ADDED';
							}
						}
					}
				}
			}
			$objectInfo = $mailing->get();
			$objectInfo['RWRITE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
			$objectInfo['RSTAGE'] = $mailing->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

			if ($objectInfo['DELETED']) {
				$objectInfo['RWRITE'] = false;
				$objectInfo['READONLY'] = true;
				$objectInfo['RSTAGE'] = false;
			}

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $mailingMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				$currentObject = $mailingMgr->getMailing($lockedObject['OBJECTID']);
				$currentObject->releaseLock($lockedObject['TOKEN']);
			}
			// Check for lock, and lock if not locked
			$lockStatus = $mailing->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$mailing->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}
		}
		break;

	case 'cblock':
		if ($objectID) {
			$cb = sCblockMgr()->getCblock($objectID);
			$objectInfo = $cb->get();
			$objectInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
			$objectInfo['RSTAGE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

			$versions = $cb->getVersions();
			for ($i = 0; $i < count($versions); $i++) {
				$uid = $versions[$i]["CREATEDBY"];
				if ($uid) {
					$user = new User($uid);
					$uinfo = $user->get();
					$uinfo['PROPS'] = $user->properties->getValues( $uid );
					$versions[$i]["USERNAME"] = $uinfo['PROPS']["LASTNAME"];
					$versions[$i]["VORNAME"] = $uinfo['PROPS']["FIRSTNAME"];
				}
			}
			$smarty->assign("versions", $versions);

			$history = $cb->history->getList($objectID);

			$real_history = array();
			$real_history_cnt = 0;
			for ($i = 0; $i < count($history); $i++) {
				if ($lastuserid <> $history[$i]["UID"]) {
					$user = new User($history[$i]["UID"]);
					$uinfo = $user->get();
					$uinfo['PROPS'] = $user->properties->getValues( $history[$i]["UID"] );
				}
				$history[$i]["USERNAME"] = trim($uinfo['PROPS']["FIRSTNAME"]." ".$uinfo['PROPS']["LASTNAME"]);
				$history[$i]["USERID"] = $uinfo["ID"];
				$history[$i]['TAB'] = $tab_mappings[$history[$i]['TEXT']];

				if ($history[$i]['TAB'] == 'P_TAGS') {
					if ( ($history[$i]['TEXT'] == 'TXT_TAG_H_ASSIGN') ||
						 ($history[$i]['TEXT'] == 'TXT_TAG_H_REMOVE') ) {
						 	$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['TARGETID']."', 'tag', '".$history[$i]['NEWVALUE']."', 'tag', '');\">".$history[$i]['NEWVALUE']."</a>";
					}
				}

				if ($history[$i]['TAB'] == 'P_CONTENT') {
					if ( ($history[$i]['TEXT'] == 'TXT_PAGE_H_COREMOVE') ||
					 	 ($history[$i]['TEXT'] == 'TXT_PAGE_H_EMREMOVE') ||
						 ($history[$i]['TEXT'] == 'TXT_PAGE_H_COADD') ||
						 ($history[$i]['TEXT'] == 'TXT_PAGE_H_EMADD') ||
						 ($history[$i]['TEXT'] == 'TXT_PAGE_H_EMCOPY') ||
						 ($history[$i]['TEXT'] == 'TXT_PAGE_H_COORDER') ) {
							$history[$i]['CONTENTAREA'] = $history[$i]['OLDVALUE'];
							$history[$i]['TYPE'] = 'COACTION';
					}

					// For Formfieldinfo
					if ( ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_1') ||
					 	 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_2') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_3') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_4_ON') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_4_OFF') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_5') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_6') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_7') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_8') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_9') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_10') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_11') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_12') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_15') ) {

						 	$lnkInfo = sCblockMgr()->getEntrymaskLinkByEntrymaskLinkId( $history[$i]['TARGETID'] );

						 	$coFormfield = $entrymaskMgr->getFormfield( $lnkInfo[0]['ENTRYMASKFORMFIELD'] );
						 	$formfield_name = $coFormfield['NAME'];

						 	$coLinkInfo = sCblockMgr()->getCblockLinkByLinkId( $lnkInfo[0]['LNK'] );
						 	$controlInfo = $entrymaskMgr->get( $coLinkInfo[0]['ENTRYMASK'] );

						 	$control_name = $controlInfo['NAME'];

							// Special Case for Files
							if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_6') {
								if ($history[$i]['NEWVALUE']) {
									$file = sFileMgr()->getFile($history[$i]['NEWVALUE']);
									if ($file) {
										$objectInfo = $file->get();
										$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$history[$i]['NEWVALUE'].'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
									}
								}
							}

							// Special Case for Links
							if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_5') {
								$special_url = resolveSpecialURL($history[$i]['NEWVALUE']);
								if ($special_url !== false) {
									$target_aid = '';
									$target_id = '';
									$target_type = 0;

									$special_url_info = getSpecialURLInfo($history[$i]['NEWVALUE']);
									if ($special_url_info['TYPE']=='DOWN') {
										$target_type = 'FILE';
										$target_id = $special_url_info['ID'];
										if ($target_id) {
											$file = sFileMgr()->getFile($target_id);
											if ($file) {
												$objectInfo = $file->get();
												$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
											}
										}
									} else if ($special_url_info['TYPE']=='IMG') {
										$target_type = 'IMAGE';
										$target_id = $special_url_info['ID'];
										if ($target_id) {
											$file = sFileMgr()->getFile($target_id);
											if ($file) {
												$objectInfo = $file->get();
												$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
											}
										}
									} else {
										$target_type = 'PAGE';
										$oldsite = $siteID;
										if ($special_url_info['SITE'] && $special_url_info['ID']) {
											$lPageMgr = new PageMgr($special_url_info['SITE']);
											$lPage = $lPageMgr->getPage($special_url_info['ID']);
											if ($lPage) {
												$objectInfo = $lPage->get();
												$objectInfo['RWRITE'] = $lPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RWRITE");
												$objectInfo['RDELETE'] = $lPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RDELETE");
												$iconData = getIconForPage($objectInfo);
												$iconClass = $iconData['iconclass'];
												$history[$i]['NEWVALUE'] = '<div class="icon'.$iconClass.'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$special_url_info['ID']."-".$special_url_info['SITE']."', 'page', '".$objectInfo['NAME']."', '".$iconClass."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
											}
										}
									}
								} else if (preg_match_all($this->URLRegEx1, $history[$i]['NEWVALUE'], $internal) > 0) {
									$target_aid = '';
									$target_id = '';
									$target_type = 0;

									if ($internal[2][0] == 'download') {
										$target_type = 'FILE';
										$target_id = $internal[3][0];
										if ($target_id) {
											$file = sFileMgr()->getFile($target_id);
											if ($file) {
												$objectInfo = $file->get();
												$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
											}
										}
									} else if ($internal[2][0] == 'page') {
										preg_match_all($this->URLRegEx2, $history[$i]['NEWVALUE'], $linkinfo);
										$target_type = 'PAGE';
										$oldsite = $siteID;
										if ($linkinfo[3][0] && $linkinfo[4][0]) {
											$lPageMgr = new PageMgr($linkinfo[3][0]);
											$lPage = $lPageMgr->getPage($linkinfo[4][0]);
											if ($lPage) {
												$objectInfo = $lPage->get();
												$objectInfo['RWRITE'] = $lPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RWRITE");
												$objectInfo['RDELETE'] = $lPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RDELETE");
												$iconData = getIconForPage($objectInfo);
												$iconClass = $iconData['iconclass'];
												$history[$i]['NEWVALUE'] = '<div class="icon'.$iconClass.'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$linkinfo[4][0]."-".$linkinfo[3][0]."', 'page', '".$objectInfo['NAME']."', '".$iconClass."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
											}
										}
									} else if ($internal[2][0] == 'image') {
										$target_type = 'IMAGE';
										$target_id = $internal[3][0];
									}
								} else if (strpos($history[$i]['NEWVALUE'], 'mailto:') === 0) {
									$history[$i]['NEWVALUE'] = '<div class="iconemail"></div>'.str_replace( 'mailto:', '', $history[$i]['NEWVALUE'] );
								} else {
									$linkInfo = checkLinkInternalExternal( $history[$i]['NEWVALUE'] );
									switch($linkInfo['TYPE']) {
										case 'external':
											$history[$i]['NEWVALUE'] = '<div class="iconlink"></div>'.$history[$i]['NEWVALUE'];
											break;
										case 'internal':
											$target_type = 'PAGE';
											$history[$i]['NEWVALUE'] = '<div class="iconpage"></div>'.$linkInfo['NAME'];
											break;
										case 'file':
											$target_type = 'FILE';
											$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$linkInfo['INFO']['FILE_ID'].'\', event);"><span style="display:inline-block;" class="filetype '.$linkInfo['INFO']['COLOR'].'">'.$linkInfo['INFO']['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$linkInfo['INFO']['FILE_ID']."', 'file', '".$linkInfo['NAME']."', {color:'".$linkInfo['INFO']['COLOR']."',typecode:'".$linkInfo['INFO']['CODE']."'});\">".$linkInfo['NAME']."</a></span>";
											break;
									}
								}

							}

							// Special Case for Contentblocks
							if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_7') {
								if ($history[$i]['NEWVALUE']) {
									$cb = sCblockMgr()->getCblock($history[$i]['NEWVALUE']);
									$cblockInfo = $cb->get();

									$cblockInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
									$cblockInfo['RDELETE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");

									$styleData = getStyleForContentblock($cblockInfo, true);
									$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'cblock', '".$cblockInfo['NAME']."', 'cblock', '".$styleData."');\">".$cblockInfo['NAME']."</a>";
								}
							}

							// Special Case for Tags
							if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_8') {
								$tagInfo = $tagMgr->get($history[$i]['NEWVALUE']);
								$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'tag', '".$tagInfo['NAME']."', 'tag', '');\">".$tagInfo['NAME']."</a>";
							}

							// Special Case for Date
							if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_11') {
								$history[$i]['NEWVALUE'] = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
							}

							// Special Case for Datetime
							if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_12') {
								$dateString = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
								$timeString = date('H', TStoLocalTS($history[$i]['NEWVALUE'])).':'.date('i', TStoLocalTS($history[$i]['NEWVALUE']));
								$history[$i]['NEWVALUE'] = $dateString.' '.$timeString;
							}

							// Special Case for Pages
							if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_15') {
								$currSiteID = explode( '-', $history[$i]['NEWVALUE'] );
								$currPageID = $currSiteID[0];
								$currSiteID = $currSiteID[1];
								if ($currSiteID && $currSiteID) {
									$currPageMgr = new PageMgr($currSiteID);
									$currPage = $currPageMgr->getPage($currPageID);
									if ($currPage) {
										$currPageInfo = $currPage->get();
										$currPageInfo['RWRITE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
										$currPageInfo['RDELETE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");
										$iconData = getIconForPage($currPageInfo);
										$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."-".$history[$i]['TARGETID']."', 'page', '".$currPageInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$currPageInfo['NAME']."</a>";
									}
								}
							}

							/*
							- CONTENTAREA
							- EMBLOCK
							- FORMFIELD
							*/
							$history[$i]['FORMFIELD'] = $formfield_name;
							$history[$i]['EMBLOCK'] = $control_name;
							$history[$i]['TYPE'] = 'COEDIT';
					}
				}

				if ($history[$i]["TAB"] == "P_PROPERTIES") {
					if ($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP_FILE') {
						if ($history[$i]['NEWVALUE']) {
							$file = sFileMgr()->getFile($history[$i]['NEWVALUE']);
							if ($file) {
								$fileInfo = $file->get();
								if ($fileInfo) {
									$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$history[$i]['NEWVALUE'].'\', event);"><span style="display:inline-block;" class="filetype '.$fileInfo['COLOR'].'">'.$fileInfo['CODE'].'</span> '.$fileInfo['NAME'].'</span>';
								} else {
									$history[$i]['NEWVALUE'] = $itext['TXT_PAGE_H_PROP_FILE_REMOVED'];
								}
							}
						}
					}
					if 	($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP_RICHTEXT') {
						$history[$i]['NEWVALUE'] = $itext['TXT_COMMON_H_COEDIT_FRMFLD_3'];
					}
					if 	($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP_RICHTEXT') {
						$history[$i]['NEWVALUE'] = $itext['TXT_COMMON_H_COEDIT_FRMFLD_3'];
					}
					if 	($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP_TAG') {
						$tagInfo = $tagMgr->get($history[$i]['NEWVALUE']);
						$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'tag', '".$tagInfo['NAME']."', 'tag', '');\">".$tagInfo['NAME']."</a>";
					}
					if 	($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP_CBLOCK') {
						if ($history[$i]['NEWVALUE']) {
							$cb = sCblockMgr()->getCblock($history[$i]['NEWVALUE']);
							$cblockInfo = $cb->get();
							$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'cblock', '".$cblockInfo['NAME']."', 'cblock', '');\">".$cblockInfo['NAME']."</a>";

						}
					}
					if 	($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP_PAGE') {
						if ($history[$i]['TARGETID'] && $history[$i]['TARGETID']) {
							$currPageMgr = new PageMgr($history[$i]['TARGETID']);
							$currPage = $currPageMgr->getPage($history[$i]['NEWVALUE']);
							if ($currPage) {
								$currPageInfo = $currPage->get();
								$currPageInfo['RWRITE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
								$currPageInfo['RDELETE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");
								$iconData = getIconForPage($currPageInfo);
								$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."-".$history[$i]['TARGETID']."', 'page', '".$currPageInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$currPageInfo['NAME']."</a>";
							}
						}
					}
					if 	($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP_DATE') {
						$history[$i]['NEWVALUE'] = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
					}
					if 	($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP_DATETIME') {
						$dateString = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
						$timeString = date('H', TStoLocalTS($history[$i]['NEWVALUE'])).':'.date('i', TStoLocalTS($history[$i]['NEWVALUE']));
						$history[$i]['NEWVALUE'] = $dateString.' '.$timeString;
					}
					if 	($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP_LINK') {
						$special_url = resolveSpecialURL($history[$i]['NEWVALUE']);
						if ($special_url !== false) {
							$target_aid = '';
							$target_id = '';
							$target_type = 0;

							$special_url_info = getSpecialURLInfo($history[$i]['NEWVALUE']);
							if ($special_url_info['TYPE']=='DOWN') {
								$target_type = 'FILE';
								$target_id = $special_url_info['ID'];
								if ($target_id) {
									$file = sFileMgr()->getFile($target_id);
									if ($file) {
										$objectInfo = $file->get();
										$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
									}
								}
							} else if ($special_url_info['TYPE']=='IMG') {
								$target_type = 'IMAGE';
								$target_id = $special_url_info['ID'];
								if ($target_id) {
									$file = sFileMgr()->getFile($target_id);
									if ($file) {
										$objectInfo = $file->get();
										$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
									}
								}
							} else {
								$target_type = 'PAGE';
								$oldsite = $siteID;
								if ($special_url_info['SITE'] && $special_url_info['ID']) {
									$iPageMgr = new PageMgr($special_url_info['SITE']);
									$iPage = $iPageMgr->getPage($special_url_info['ID']);
									if ($iPage) {
										$objectInfo = $iPage->get();
										$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RWRITE");
										$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RDELETE");
										$iconData = getIconForPage($objectInfo);
										$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$special_url_info['ID'].'-'.$special_url_info['SITE']."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
									}
								}
							}
						} else if (preg_match_all($this->URLRegEx1, $history[$i]['NEWVALUE'], $internal) > 0) {
							$target_aid = '';
							$target_id = '';
							$target_type = 0;

							if ($internal[2][0] == 'download') {
								$target_type = 'FILE';
								$target_id = $internal[3][0];
								if ($target_id) {
									$file = sFileMgr()->getFile($target_id);
									if ($file) {
										$objectInfo = $file->get();
										$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
									}
								}
							} else if ($internal[2][0] == 'page') {
								preg_match_all($this->URLRegEx2, $history[$i]['NEWVALUE'], $linkinfo);
								$target_type = 'PAGE';
								$oldsite = $siteID;
								if ($linkinfo[3][0] && $linkinfo[4][0]) {
									$iPageMgr = new PageMgr($linkinfo[3][0]);
									$iPage = $iPageMgr->getPage($linkinfo[4][0]);
									if ($iPage) {
										$objectInfo = $iPage->get();
										$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RWRITE");
										$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RDELETE");
										$iconData = getIconForPage($objectInfo);
										$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$linkinfo[4][0].'-'.$linkinfo[3][0]."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
									}
								}
							} else if ($internal[2][0] == 'image') {
								$target_type = 'IMAGE';
								$target_id = $internal[3][0];
							}
						} else if (strpos($history[$i]['NEWVALUE'], 'mailto:') === 0) {
							$history[$i]['NEWVALUE'] = '<div class="iconemail"></div>'.str_replace( 'mailto:', '', $history[$i]['NEWVALUE'] );
						} else {
							$history[$i]['NEWVALUE'] = '<div class="iconlink"></div>'.$history[$i]['NEWVALUE'];
						}
					}
				}

				if ($history[$i]['TEXT'] == 'TXT_CBLOCK_H_PROP') {
					if (strpos($history[$i]['OLDVALUE'], 'TXT_') === 0) {
						if ($itext[$history[$i]['OLDVALUE']]) {
							$history[$i]['OLDVALUE'] = $itext[$history[$i]['OLDVALUE']];
						}
					}
				}

				// For Extension-Logging
				if ($history[$i]['TEXT'] == 'TXT_EXTENSION_H_LOGENTRY') {
					$extensionManager = new ExtensionMgr();
					$extensionInfo = $extensionManager->get($history[$i]['OLDVALUE']);
					$history[$i]['NEWVALUE'] = '<div class="modified">'.$extensionInfo['NAME'].' <em>'.$history[$i]['NEWVALUE'].'</em></div>';
				}

				$lastuserid = $history[$i]['UID'];

				$tKey = $history[$i]['TEXT'];
				$pKey = $history[$i]['TAB'];
				$dKey = $history[$i]['DATETIME'];

				// Only get history entries for Yeager
				if ( strpos($tKey, 'TXT_') === 0 ) {

					$show_entry = false;

					if ($filterAction=='ONLY_VERSIONS') {
						if ($pKey == 'P_VERSIONS') {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else if ($filterTab!='ALL') {
						if ($pKey==$filterTab) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else {
						$show_entry = true;
					}

					if ($filterTimeframe=='LAST_WEEK') {
						if ( ((time()-604800) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_2_WEEKS') {
						if ( ((time()-1209600) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_4_WEEKS') {
						if ( ((time()-2419200) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_8_WEEKS') {
						if ( ((time()-4838400) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else {

						// custom timeframe
						list ($timefrom, $timetill) = explode("###", $filterTimeframe);

						$timefrom = TSfromLocalTS(strtotime($timefrom));
						$timetill = TSfromLocalTS(strtotime($timetill) + 24*60*60);

						if ( ($dKey > $timefrom) && ($dKey < $timetill)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}

					}

					if ( ($history[$i]['TEXT'] == 'TXT_FILE_H_ADDVIEW') ||
						 ($history[$i]['TEXT'] == 'TXT_FILE_H_REMOVEVIEW') ) {
						$viewinfo = $viewMgr->get( $history[$i]['NEWVALUE'] );
						$history[$i]['NEWVALUE'] = $viewinfo['NAME'];
					}

					if ($history[$i]['TEXT'] == 'TXT_FILE_H_TYPECHANGE') {
						$filetypeinfo = $filetypeMgr->get( $history[$i]['NEWVALUE'] );
						$name = $filetypeinfo['NAME'];
						if ($name == 'DEFAULT') {
							$name = $itext['TXT_DEFAULT_FILETYPE'];
						}
						$history[$i]['NEWVALUE'] = '<span style="display:inline-block;" class="filetype '.$filetypeinfo['COLOR'].'">'.$filetypeinfo['CODE'].'</span>&nbsp;'.$name;
					}

					// Check for tagorder changes in a row (within 300 secs, and reduce them to one entry)
					if ( ($tKey=='TXT_TAG_H_TAGORDER') &&
						 ($last_entry_type=='TXT_TAG_H_TAGORDER') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					// Check for entrymask order changes in a row (within 300 secs, and reduce them to one entry)
					if ( ($tKey=='TXT_CBLOCK_H_EMORDER') &&
						 ($last_entry_type=='TXT_CBLOCK_H_EMORDER') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					// Check for autopublish changes in a row (within 300 secs, and reduce them to one entry)
					if ( (($tKey=='TXT_CBLOCK_H_AUTOPUBLISH_CHANGED') || ($tKey=='TXT_CBLOCK_H_AUTOPUBLISH_ADDED')) &&
						 (($last_entry_type=='TXT_CBLOCK_H_AUTOPUBLISH_ADDED') || ($last_entry_type=='TXT_CBLOCK_H_AUTOPUBLISH_CHANGED')) &&
						 ($last_entry_item_id==$history[$i]['TARGETID']) &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								if (!$test)
									$show_entry = false;
							}
							if ($tKey=='TXT_CBLOCK_H_AUTOPUBLISH_ADDED') {
								$add_in_row = true;
							}
					}

					// Check for datetime changes in a row (within 300 secs, and reduce them to one entry)
					if ( ($tKey=='TXT_CBLOCK_H_PROP_DATETIME') &&
						 ($last_entry_type=='TXT_CBLOCK_H_PROP_DATETIME') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					$last_entry_type = $tKey;
					$last_entry_item_id = $history[$i]['TARGETID'];
					$last_entry_timestamp = $dKey;
					$last_entry_userid = $history[$i]['USERID'];

					if ($show_entry) {
						$real_history[$real_history_cnt] = $history[$i];
						$real_history_cnt++;
					}

				}
			}

			if ($objectID) {
				$cb = sCblockMgr()->getCblock($objectID);
				$objectInfo = $cb->get();
				$objectInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
				$objectInfo['RSTAGE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

				if ($objectInfo['DELETED']) {
					$objectInfo['RWRITE'] = false;
					$objectInfo['READONLY'] = true;
					$objectInfo['RSTAGE'] = false;
				}

				// Get current locks for this token (and unlock them)
				$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
				$lockedObjects = sCblockMgr()->getLocksByToken($lockToken);
				foreach($lockedObjects as $lockedObject) {
					if ($lockedObject['OBJECTID']) {
						$currentObject = sCblockMgr()->getCblock($lockedObject['OBJECTID']);
						$currentObject->releaseLock($lockedObject['TOKEN']);
					}
				}
				// Check for lock, and lock if not locked
				$lockStatus = $cb->getLock();
				if ($lockStatus['LOCKED'] == 0) {
					$lockedFailed = !$cb->acquireLock($lockToken);
				} else {
					$lockedFailed = true;
				}
			}

		}
		break;

	case 'file':
		$file = sFileMgr()->getFile($objectID);
		if ($file) {
			$objectInfo = $file->get();
			$objectInfo['RWRITE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");

			$versions = $file->getVersions();
			for ($i = 0; $i < count($versions); $i++) {
				$uid = $versions[$i]["CREATEDBY"];
				$user = new User($uid);
				$uinfo = $user->get();
				$uinfo['PROPS'] = $user->properties->getValues( $uid );
				$versions[$i]["USERNAME"] = $uinfo['PROPS']["LASTNAME"];
				$versions[$i]["VORNAME"] = $uinfo['PROPS']["FIRSTNAME"];
			}
			$smarty->assign("versions",$versions);
			$smarty->assign("fileurl",$file->getUrl());

			$history = $file->history->getList($objectID);

			$real_history = array();
			$real_history_cnt = 0;
			for ($i = 0; $i < count($history); $i++) {
				if ($lastuserid <> $history[$i]["UID"]) {
					$user = new User($history[$i]["UID"]);
					$uinfo = $user->get();
					$uinfo['PROPS'] = $user->properties->getValues( $history[$i]["UID"] );
				}
				$history[$i]["USERNAME"] = trim($uinfo['PROPS']["FIRSTNAME"]." ".$uinfo['PROPS']["LASTNAME"]);
				$history[$i]["USERID"] = $uinfo["ID"];
				$history[$i]['TAB'] = $tab_mappings[$history[$i]['TEXT']];

				$lastuserid = $history[$i]['UID'];

				$tKey = $history[$i]['TEXT'];
				$pKey = $history[$i]['TAB'];
				$dKey = $history[$i]['DATETIME'];

				// Only get history entries for Yeager
				if ( strpos($tKey, 'TXT_') === 0 ) {

					$show_entry = false;

					if ($filterAction=='ONLY_VERSIONS') {
						if ($pKey == 'P_VERSIONS') {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else if ($filterTab!='ALL') {
						if ($pKey==$filterTab) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else {
						$show_entry = true;
					}

					if ($filterTimeframe=='LAST_WEEK') {
						if ( ((time()-604800) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_2_WEEKS') {
						if ( ((time()-1209600) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_4_WEEKS') {
						if ( ((time()-2419200) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_8_WEEKS') {
						if ( ((time()-4838400) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else {

						// custom timeframe
						list ($timefrom, $timetill) = explode("###", $filterTimeframe);

						$timefrom = TSfromLocalTS(strtotime($timefrom));
						$timetill = TSfromLocalTS(strtotime($timetill) + 24*60*60);

						if ( ($dKey > $timefrom) && ($dKey < $timetill)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}

					}

					if ( ($history[$i]['TEXT'] == 'TXT_FILE_H_ADDVIEW') ||
						 ($history[$i]['TEXT'] == 'TXT_FILE_H_REMOVEVIEW') ) {
						$viewinfo = $viewMgr->get( $history[$i]['NEWVALUE'] );
						$history[$i]['NEWVALUE'] = $viewinfo['NAME'];
					}

					if ($history[$i]['TEXT'] == 'TXT_FILE_H_TYPECHANGE') {
						$filetypeinfo = $filetypeMgr->get( $history[$i]['NEWVALUE'] );
						$name = $filetypeinfo['NAME'];
						if ($name == 'DEFAULT') {
							$name = $itext['TXT_DEFAULT_FILETYPE'];
						}
						$history[$i]['NEWVALUE'] = '<span style="display:inline-block;" class="filetype '.$filetypeinfo['COLOR'].'">'.$filetypeinfo['CODE'].'</span>&nbsp;'.$name;
					}

					if ( ($history[$i]['TEXT'] == 'TXT_TAG_H_ASSIGN') ||
						 ($history[$i]['TEXT'] == 'TXT_TAG_H_REMOVE') ) {
						 	$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['TARGETID']."', 'tag', '".$history[$i]['NEWVALUE']."', 'tag', '');\">".$history[$i]['NEWVALUE']."</a>";
					}

					// Check for tagorder changes in a row (within 300 secs, and reduce them to one entry)
					if ( ($tKey=='TXT_TAG_H_TAGORDER') &&
						 ($last_entry_type=='TXT_TAG_H_TAGORDER') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					// Check for datetime changes in a row (within 300 secs, and reduce them to one entry)
					if ( ($tKey=='TXT_FILE_H_PROP_DATETIME') &&
						 ($last_entry_type=='TXT_FILE_H_PROP_DATETIME') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					if ($history[$i]['TEXT'] == 'TXT_FILE_H_PROP') {
						if (strpos($history[$i]['OLDVALUE'], 'TXT_') === 0) {
							if ($itext[$history[$i]['OLDVALUE']]) {
								$history[$i]['OLDVALUE'] = $itext[$history[$i]['OLDVALUE']];
							}
						}
					}
					if 	($history[$i]['TEXT'] == 'TXT_FILE_H_PROP_RICHTEXT') {
						$history[$i]['NEWVALUE'] = $itext['TXT_COMMON_H_COEDIT_FRMFLD_3'];
					}
					if 	($history[$i]['TEXT'] == 'TXT_FILE_H_PROP_TAG') {
						$tagInfo = $tagMgr->get($history[$i]['NEWVALUE']);
						$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'tag', '".$tagInfo['NAME']."', 'tag', '');\">".$tagInfo['NAME']."</a>";
					}
					if 	($history[$i]['TEXT'] == 'TXT_FILE_H_PROP_CBLOCK') {
						if ($history[$i]['NEWVALUE']) {
							$cb = sCblockMgr()->getCblock($history[$i]['NEWVALUE']);
							$cblockInfo = $cb->get();
							$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'cblock', '".$cblockInfo['NAME']."', 'cblock', '');\">".$cblockInfo['NAME']."</a>";
						}
					}
					if 	($history[$i]['TEXT'] == 'TXT_FILE_H_PROP_PAGE') {
						if ($history[$i]['TARGETID'] && $history[$i]['NEWVALUE']) {
							$currPageMgr = new PageMgr($history[$i]['TARGETID']);
							$currPage = $currPageMgr->getPage($history[$i]['NEWVALUE']);
							if ($currPage) {
								$currPageInfo = $currPage->get();
								$currPageInfo['RWRITE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
								$currPageInfo['RDELETE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");
								$iconData = getIconForPage($currPageInfo);
								$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."-".$history[$i]['TARGETID']."', 'page', '".$currPageInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$currPageInfo['NAME']."</a>";
							}
						}
					}
					if 	($history[$i]['TEXT'] == 'TXT_FILE_H_PROP_DATE') {
						$history[$i]['NEWVALUE'] = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
					}
					if 	($history[$i]['TEXT'] == 'TXT_FILE_H_PROP_DATETIME') {
						$dateString = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
						$timeString = date('H', TStoLocalTS($history[$i]['NEWVALUE'])).':'.date('i', TStoLocalTS($history[$i]['NEWVALUE']));
						$history[$i]['NEWVALUE'] = $dateString.' '.$timeString;
					}
					if 	($history[$i]['TEXT'] == 'TXT_FILE_H_PROP_LINK') {
						$special_url = resolveSpecialURL($history[$i]['NEWVALUE']);
						if ($special_url !== false) {
							$target_aid = '';
							$target_id = '';
							$target_type = 0;

							$special_url_info = getSpecialURLInfo($history[$i]['NEWVALUE']);
							if ($special_url_info['TYPE']=='DOWN') {
								$target_type = 'FILE';
								$target_id = $special_url_info['ID'];
								if ($target_id) {
									$file = sFileMgr()->getFile($target_id);
									if ($file) {
										$objectInfo = $file->get();
										$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
									}
								}
							} else if ($special_url_info['TYPE']=='IMG') {
								$target_type = 'IMAGE';
								$target_id = $special_url_info['ID'];
								if ($target_id) {
									$file = sFileMgr()->getFile($target_id);
									if ($file) {
										$objectInfo = $file->get();
										$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
									}
								}
							} else {
								$target_type = 'PAGE';
								$oldsite = $siteID;
								if ($special_url_info['SITE'] && $special_url_info['ID']) {
									$iPageMgr = new PageMgr($special_url_info['SITE']);
									$iPage = $iPageMgr->getPage($special_url_info['ID']);
									if ($iPage) {
										$objectInfo = $iPage->get();
										$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RWRITE");
										$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RDELETE");
										$iconData = getIconForPage($objectInfo);
										$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$special_url_info['ID'].'-'.$special_url_info['SITE']."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
									}
								}
							}
						} else if (preg_match_all($this->URLRegEx1, $history[$i]['NEWVALUE'], $internal) > 0) {
							$target_aid = '';
							$target_id = '';
							$target_type = 0;

							if ($internal[2][0] == 'download') {
								$target_type = 'FILE';
								$target_id = $internal[3][0];
								if ($target_id) {
									$file = sFileMgr()->getFile($target_id);
									if ($file) {
										$objectInfo = $file->get();
										$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
									}
								}
							} else if ($internal[2][0] == 'page') {
								preg_match_all($this->URLRegEx2, $history[$i]['NEWVALUE'], $linkinfo);
								$target_type = 'PAGE';
								$oldsite = $siteID;
								if ($linkinfo[3][0] && $linkinfo[3][0]) {
									$iPageMgr = new PageMgr($linkinfo[3][0]);
									$iPage = $iPageMgr->getPage($linkinfo[4][0]);
									if ($iPage) {
										$objectInfo = $iPage->get();
										$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RWRITE");
										$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RDELETE");
										$iconData = getIconForPage($objectInfo);
										$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$linkinfo[4][0].'-'.$linkinfo[3][0]."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
									}
								}
							} else if ($internal[2][0] == 'image') {
								$target_type = 'IMAGE';
								$target_id = $internal[3][0];
							}
						} else if (strpos($history[$i]['NEWVALUE'], 'mailto:') === 0) {
							$history[$i]['NEWVALUE'] = '<div class="iconemail"></div>'.str_replace( 'mailto:', '', $history[$i]['NEWVALUE'] );
						} else {
							$history[$i]['NEWVALUE'] = '<div class="iconlink"></div>'.$history[$i]['NEWVALUE'];
						}
					}

					// For Extension-Logging
					if ($history[$i]['TEXT'] == 'TXT_EXTENSION_H_LOGENTRY') {
						$extensionManager = new ExtensionMgr();
						$extensionInfo = $extensionManager->get($history[$i]['OLDVALUE']);
						$history[$i]['NEWVALUE'] = '<div class="modified">'.$extensionInfo['NAME'].' <em>'.$history[$i]['NEWVALUE'].'</em></div>';
					}

					$last_entry_type = $tKey;
					$last_entry_item_id = $history[$i]['TARGETID'];
					$last_entry_timestamp = $dKey;
					$last_entry_userid = $history[$i]['USERID'];

					if ($show_entry) {
						$real_history[$real_history_cnt] = $history[$i];
						$real_history_cnt++;
					}

				}
			}
			if ($objectID) {
				$file = sFileMgr()->getFile($objectID);
				if ($file) {
					$objectInfo = $file->get();
					$objectInfo['RWRITE'] = $file->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");

					if ($objectInfo['DELETED']==1) {
						$objectInfo['RWRITE'] = false;
						$objectInfo['READONLY'] = true;
					}

					// Get current locks for this token (and unlock them)
					$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
					$lockedObjects = sFileMgr()->getLocksByToken($lockToken);
					foreach($lockedObjects as $lockedObject) {
						$currentObject = sFileMgr()->getFile($lockedObject['OBJECTID']);
						if ($currentObject) {
							$currentObject->releaseLock($lockedObject['TOKEN']);
						}
					}
					// Check for lock, and lock if not locked
					$lockStatus = $file->getLock();
					if ($lockStatus['LOCKED'] == 0) {
						$lockedFailed = !$file->acquireLock($lockToken);
					} else {
						$lockedFailed = true;
					}
				}
			}
		}

		break;

	case 'page':
		$extensionMgr = new ExtensionMgr();
		$templateMgr = new Templates();
		if ($siteID && $objectID) {
			$pageMgr = new PageMgr($siteID);
			$page = $pageMgr->getPage($objectID);
			$objectInfo = $page->get();
			$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
			$objectInfo['RSTAGE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

			$versions = $page->getVersions();
			for ($i = 0; $i < count($versions); $i++) {
				$uid = $versions[$i]["CREATEDBY"];
				$user = new User($uid);
				$uinfo = &$user->get();
				$uinfo['PROPS'] = $user->properties->getValues( $uid );
				$versions[$i]["USERNAME"] = $uinfo['PROPS']["LASTNAME"];
				$versions[$i]["VORNAME"] = $uinfo['PROPS']["FIRSTNAME"];
			}
			$smarty->assign("versions",$versions);
			$smarty->assign("pageurl",$page->getUrl());

			$history = $page->history->getList($objectID);
			$real_history = array();
			$real_history_cnt = 0;
			for ($i = 0; $i < count($history); $i++) {
				if ($lastuserid <> $history[$i]["UID"]) {
					$user = new User($history[$i]["UID"]);
					$uinfo = &$user->get();
					$uinfo['PROPS'] = $user->properties->getValues( $history[$i]["UID"] );
				}
				$history[$i]["USERNAME"] = trim($uinfo['PROPS']["FIRSTNAME"]." ".$uinfo['PROPS']["LASTNAME"]);
				$history[$i]["USERID"] = $uinfo["ID"];
				$history[$i]['TAB'] = $tab_mappings[$history[$i]['TEXT']];

				if ($history[$i]['TAB'] == 'P_TAGS') {
					if ( ($history[$i]['TEXT'] == 'TXT_TAG_H_ASSIGN') ||
						 ($history[$i]['TEXT'] == 'TXT_TAG_H_REMOVE') ) {
						 	$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['TARGETID']."', 'tag', '".$history[$i]['NEWVALUE']."', 'tag', '');\">".$history[$i]['NEWVALUE']."</a>";
					}
				}

				if ($history[$i]['TAB'] == 'P_EXTENSIONS') {
					// For Formfieldinfo (extensions)
					if ( ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_1') ||
					 	 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_2') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_3') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_4_ON') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_4_OFF') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_5') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_6') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_7') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_8') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_9') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_10') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_11') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_12') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_13') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_14') ||
						 ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_15') ) {

							$extensioninfo = $extensionMgr->get( $history[$i]['OLDVALUE'] );
							$control_name = $extensioninfo['NAME'];

							$methodName = '';
							switch($extensioninfo['TYPE']) {
								case EXTENSION_PAGE:
									$methodName = 'usedByPage';
									break;
								case EXTENSION_MAILING:
									$methodName = 'usedByMailing';
									break;
								case EXTENSION_FILE:
									$methodName = 'usedByFile';
									break;
								case EXTENSION_CBLOCK:
									$methodName = 'usedByCblock';
									break;
							}
							$extension = $extensionMgr->getExtension($extensioninfo['CODE']);
							if( ($extension) && ($methodName != '') && ($extension->$methodName($objectID, $objectInfo['VERSION'], $siteID) === true) ) {
								$extension = $extensionMgr->getExtension($extensioninfo['CODE'], $objectID, $objectInfo['VERSION'], $siteID);

								if (($extension) && ($extensioninfo['TYPE'] == EXTENSION_PAGE)) {
									$props = $extension->properties->getList('LISTORDER');
								}

								foreach($props as $prop) {
									if ($prop['ID']==$history[$i]['TARGETID']) {
										$formfield_name = $prop['NAME'];
									}
								}

								// Get contentblock <-> page link
								$page_lnk = $page->getCblockLinkById( $formfield_lnk[0]['CBLOCKID'] );

								// Special Case for Files
								if ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_6') {
									if ($history[$i]['NEWVALUE']) {
										$file = sFileMgr()->getFile($history[$i]['NEWVALUE']);
										if ($file) {
											$objectInfo_file = $file->get();
											$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$history[$i]['NEWVALUE'].'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo_file['COLOR'].'">'.$objectInfo_file['CODE'].'</span> '.$objectInfo_file['NAME'].'</span>';
										}
									}
								}

								// Special Case for Links
								if ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_5') {
									$special_url = resolveSpecialURL($history[$i]['NEWVALUE']);
									if ($special_url !== false) {
										$target_aid = '';
										$target_id = '';
										$target_type = 0;

										$special_url_info = getSpecialURLInfo($history[$i]['NEWVALUE']);
										if ($special_url_info['TYPE']=='DOWN') {
											$target_type = 'FILE';
											$target_id = $special_url_info['ID'];
											if ($target_id) {
												$file = sFileMgr()->getFile($target_id);
												if ($file) {
													$objectInfo = $file->get();
													$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
												}
											}
										} else if ($special_url_info['TYPE']=='IMG') {
											$target_type = 'IMAGE';
											$target_id = $special_url_info['ID'];
											if ($target_id) {
												$file = sFileMgr()->getFile($target_id);
												if ($file) {
													$objectInfo = $file->get();
													$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
												}
											}
										} else {
											$target_type = 'PAGE';
											$oldsite = $siteID;
											if ($special_url_info['SITE'] && $special_url_info['ID']) {
												$iPageMgr = new PageMgr($special_url_info['SITE']);
												$iPage = $iPageMgr->getPage($special_url_info['ID']);
												if ($iPage) {
													$objectInfo = $iPage->get();
													$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RWRITE");
													$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RDELETE");
													$iconData = getIconForPage($objectInfo);
													$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$special_url_info['ID'].'-'.$special_url_info['SITE']."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
												}
											}
										}
									} else if (preg_match_all($this->URLRegEx1, $history[$i]['NEWVALUE'], $internal) > 0) {
										$target_aid = '';
										$target_id = '';
										$target_type = 0;

										if ($internal[2][0] == 'download') {
											$target_type = 'FILE';
											$target_id = $internal[3][0];
											if ($target_id) {
												$file = sFileMgr()->getFile($target_id);
												if ($file) {
													$objectInfo = $file->get();
													$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
												}
											}
										} else if ($internal[2][0] == 'page') {
											preg_match_all($this->URLRegEx2, $history[$i]['NEWVALUE'], $linkinfo);
											$target_type = 'PAGE';
											$oldsite = $siteID;
											if ($linkinfo[3][0] && $linkinfo[4][0]) {
												$iPageMgr = new PageMgr($linkinfo[3][0]);
												$iPage = $iPageMgr->getPage($linkinfo[4][0]);
												if ($iPage) {
													$objectInfo = $iPage->get();
													$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RWRITE");
													$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RDELETE");
													$iconData = getIconForPage($objectInfo);
													$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$linkinfo[4][0].'-'.$linkinfo[3][0]."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
												}
											}
										} else if ($internal[2][0] == 'image') {
											$target_type = 'IMAGE';
											$target_id = $internal[3][0];
										}
									} else if (strpos($history[$i]['NEWVALUE'], 'mailto:') === 0) {
										$history[$i]['NEWVALUE'] = '<div class="iconemail"></div>'.str_replace( 'mailto:', '', $history[$i]['NEWVALUE'] );
									} else {
										$linkInfo = checkLinkInternalExternal( $history[$i]['NEWVALUE'] );
										switch($linkInfo['TYPE']) {
											case 'external':
												$history[$i]['NEWVALUE'] = '<div class="iconlink"></div>'.$history[$i]['NEWVALUE'];
												break;
											case 'internal':
												$target_type = 'PAGE';
												$history[$i]['NEWVALUE'] = '<div class="iconpage"></div>'.$linkInfo['NAME'];
												break;
											case 'file':
												$target_type = 'FILE';
												$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$linkInfo['INFO']['FILE_ID'].'\', event);"><span style="display:inline-block;" class="filetype '.$linkInfo['INFO']['COLOR'].'">'.$linkInfo['INFO']['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$linkInfo['INFO']['FILE_ID']."', 'file', '".$linkInfo['NAME']."', {color:'".$linkInfo['INFO']['COLOR']."',typecode:'".$linkInfo['INFO']['CODE']."'});\">".$linkInfo['NAME']."</a></span>";
												break;
										}
									}
								}

								// Special Case for Contentblocks
								if ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_7') {
									if ($history[$i]['NEWVALUE']) {
										$cb = sCblockMgr()->getCblock($history[$i]['NEWVALUE']);
										if ($cb) {
											$cblockInfo = $cb->get();
											$cblockInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
											$cblockInfo['RDELETE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");
											$styleData = getStyleForContentblock($cblockInfo, true);
											$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'cblock', '".$cblockInfo['NAME']."', 'cblock', '".$styleData."');\">".$cblockInfo['NAME']."</a>";
										}
									}
								}

								// Special Case for Tags
								if ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_8') {
									$tagInfo = $page->tags->get($history[$i]['NEWVALUE']);
									$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'tag', '".$tagInfo['NAME']."', 'tag', '');\">".$tagInfo['NAME']."</a>";
								}

								// Special Case for Date
								if ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_11') {
									$history[$i]['NEWVALUE'] = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
								}

								// Special Case for Datetime
								if ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_12') {
									$dateString = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
									$timeString = date('H', TStoLocalTS($history[$i]['NEWVALUE'])).':'.date('i', TStoLocalTS($history[$i]['NEWVALUE']));
									$history[$i]['NEWVALUE'] = $dateString.' '.$timeString;
								}

								// Special Case for Pages
								if ($history[$i]['TEXT'] == 'TXT_OBJECT_H_EXTEDIT_FRMFLD_15') {
									$currSiteID = explode( '-', $history[$i]['NEWVALUE'] );
									$currPageID = $currSiteID[0];
									$currSiteID = $currSiteID[1];
									if ($currPageID && $currSiteID) {
										$currPageMgr = new PageMgr($currSiteID);
										$currPage = $currPageMgr->getPage($currPageID);
										if ($currPage) {
											$currPageInfo = $currPage->get();
											$currPageInfo['RWRITE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
											$currPageInfo['RDELETE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");
											$iconData = getIconForPage($currPageInfo);
											$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."-".$history[$i]['TARGETID']."', 'page', '".$currPageInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$currPageInfo['NAME']."</a>";
										}
									}
								}

								$history[$i]['FORMFIELD'] = $formfield_name;
								$history[$i]['EMBLOCK'] = $control_name;
								$history[$i]['TYPE'] = 'COEDIT';
							}
					}
				}

				if ($history[$i]['TAB'] == 'P_CONTENT') {
					if ( ($history[$i]['TEXT'] == 'TXT_PAGE_H_COREMOVE') ||
					 	 ($history[$i]['TEXT'] == 'TXT_PAGE_H_EMREMOVE') ||
						 ($history[$i]['TEXT'] == 'TXT_PAGE_H_COADD') ||
						 ($history[$i]['TEXT'] == 'TXT_PAGE_H_EMADD') ||
						 ($history[$i]['TEXT'] == 'TXT_PAGE_H_EMCOPY') ||
						 ($history[$i]['TEXT'] == 'TXT_PAGE_H_COORDER') ) {

							if ($siteID && $objectID) {
								$Tmp_ThePageMgr = new PageMgr($siteID);
								$Tmp_page = $Tmp_ThePageMgr->getPage($objectID);
								if ($Tmp_page) {
									$Tmp_pageInfo = $Tmp_page->get();
									$templateId = $Tmp_pageInfo["TEMPLATEID"];
									$Tmp_TheTemplateMgr = new Templates();
									$tmp_contentareas = $Tmp_TheTemplateMgr->getContentareas( $templateId );
									$history[$i]['CONTENTAREA'] = $history[$i]['OLDVALUE'];
									unset($realContentareaName);
									foreach($tmp_contentareas as $tmp_contentarea) {
										if ($tmp_contentarea['CODE'] == $history[$i]['CONTENTAREA']) {
											$realContentareaName = $tmp_contentarea['NAME'];
										}
									}
									if ($realContentareaName) {
										$history[$i]['CONTENTAREA'] = $realContentareaName;
									}

									$history[$i]['TYPE'] = 'COACTION';

									// For contentblocks
									if ( ($history[$i]['TEXT'] == 'TXT_PAGE_H_COREMOVE') ||
											($history[$i]['TEXT'] == 'TXT_PAGE_H_COADD') ) {
										if ($history[$i]['TARGETID']) {
											$cb = sCblockMgr()->getCblock($history[$i]['TARGETID']);
											if ($cb) {
												$cblockInfo = $cb->get();
												$cblockInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['TARGETID'], "RWRITE");
												$cblockInfo['RDELETE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['TARGETID'], "RDELETE");
												$styleData = getStyleForContentblock($cblockInfo, true);
												$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['TARGETID']."', 'cblock', '".$history[$i]['NEWVALUE']."', 'cblock', '".$styleData."');\">".$history[$i]['NEWVALUE']."</a>";
											}
										}
									}
								}
							}

					}

					// For Formfieldinfo (entrymasks)
					if ( ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_1') ||
					 	 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_2') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_3') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_4_ON') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_4_OFF') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_5') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_6') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_7') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_8') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_9') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_10') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_11') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_12') ||
						 ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_15') ) {

							// Get formfield <-> control link
							$formfield_lnk = sCblockMgr()->getCblockLinkByEntrymaskLinkId( $history[$i]['TARGETID'] );
							$emblock_id = $formfield_lnk[0]['CBLOCKID'];

							// Get Name of entrymask
							if ($formfield_lnk[0]['CBLOCKID']) {
								$tmpCb = sCblockMgr()->getCblock($formfield_lnk[0]['CBLOCKID']);
								$control_name = $tmpCb->properties->getValue('NAME');

								// Get Name of Formfield
								$lnkInfo = sCblockMgr()->getEntrymaskLinkByEntrymaskLinkId( $history[$i]['TARGETID'] );
								$coFormfield = $entrymaskMgr->getFormfield( $lnkInfo[0]['ENTRYMASKFORMFIELD'] );
								$formfield_name = $coFormfield['NAME'];

								// Get contentblock <-> page link
								$page_lnk = $page->getCblockLinkById( $formfield_lnk[0]['CBLOCKID'] );

								// Get name for contentarea
								$contentareaInfo = $templateMgr->getContentareaById( $history[$i]['FROM'] );
								$contentarea_name = $contentareaInfo['NAME'];

								// Special Case for Files
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_6') {
									if ($history[$i]['NEWVALUE']) {
										$file = sFileMgr()->getFile($history[$i]['NEWVALUE']);
										if ($file) {
											$objectInfo = $file->get();
											$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$history[$i]['NEWVALUE'].'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
										}
									}
								}

								// Special Case for Links
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_5') {
									$special_url = resolveSpecialURL($history[$i]['NEWVALUE']);
									if ($special_url !== false) {
										$target_aid = '';
										$target_id = '';
										$target_type = 0;

										$special_url_info = getSpecialURLInfo($history[$i]['NEWVALUE']);
										if ($special_url_info['TYPE']=='DOWN') {
											$target_type = 'FILE';
											$target_id = $special_url_info['ID'];
											if ($target_id) {
												$file = sFileMgr()->getFile($target_id);
												if ($file) {
													$objectInfo = $file->get();
													$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
												}
											}
										} else if ($special_url_info['TYPE']=='IMG') {
											$target_type = 'IMAGE';
											$target_id = $special_url_info['ID'];
											if ($target_id) {
												$file = sFileMgr()->getFile($target_id);
												if ($file) {
													$objectInfo = $file->get();
													$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
												}
											}
										} else {
											$target_type = 'PAGE';
											$oldsite = $siteID;
											if ($special_url_info['SITE'] && $special_url_info['ID']) {
												$iPageMgr = new PageMgr($special_url_info['SITE']);
												$iPage = $iPageMgr->getPage($special_url_info['ID']);
												if ($iPage) {
													$objectInfo = $iPage->get();
													$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RWRITE");
													$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RDELETE");
													$iconData = getIconForPage($objectInfo);
													$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$special_url_info['ID'].'-'.$special_url_info['SITE']."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
												}
											}
										}
									} else if (preg_match_all($this->URLRegEx1, $history[$i]['NEWVALUE'], $internal) > 0) {
										$target_aid = '';
										$target_id = '';
										$target_type = 0;

										if ($internal[2][0] == 'download') {
											$target_type = 'FILE';
											$target_id = $internal[3][0];
											if ($target_id) {
												$file = sFileMgr()->getFile($target_id);
												if ($file) {
													$objectInfo = $file->get();
													$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
												}
											}
										} else if ($internal[2][0] == 'page') {
											preg_match_all($this->URLRegEx2, $history[$i]['NEWVALUE'], $linkinfo);
											$target_type = 'PAGE';
											$oldsite = $siteID;
											if ($linkinfo[3][0] && $linkinfo[4][0]) {
												$iPageMgr = new PageMgr($linkinfo[3][0]);
												$iPage = $iPageMgr->getPage($linkinfo[4][0]);
												if ($iPage) {
													$objectInfo = $iPage->get();
													$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RWRITE");
													$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RDELETE");
													$iconData = getIconForPage($objectInfo);
													$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$linkinfo[4][0].'-'.$linkinfo[3][0]."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
												}
											}
										} else if ($internal[2][0] == 'image') {
											$target_type = 'IMAGE';
											$target_id = $internal[3][0];
										}
									} else if (strpos($history[$i]['NEWVALUE'], 'mailto:') === 0) {
										$history[$i]['NEWVALUE'] = '<div class="iconemail"></div>'.str_replace( 'mailto:', '', $history[$i]['NEWVALUE'] );
									} else {
										$linkInfo = checkLinkInternalExternal( $history[$i]['NEWVALUE'] );
										switch($linkInfo['TYPE']) {
											case 'external':
												$history[$i]['NEWVALUE'] = '<div class="iconlink"></div>'.$history[$i]['NEWVALUE'];
												break;
											case 'internal':
												$target_type = 'PAGE';
												$history[$i]['NEWVALUE'] = '<div class="iconpage"></div>'.$linkInfo['NAME'];
												break;
											case 'file':
												$target_type = 'FILE';
												$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$linkInfo['INFO']['FILE_ID'].'\', event);"><span style="display:inline-block;" class="filetype '.$linkInfo['INFO']['COLOR'].'">'.$linkInfo['INFO']['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$linkInfo['INFO']['FILE_ID']."', 'file', '".$linkInfo['NAME']."', {color:'".$linkInfo['INFO']['COLOR']."',typecode:'".$linkInfo['INFO']['CODE']."'});\">".$linkInfo['NAME']."</a></span>";
												break;
										}
									}

								}

								// Special Case for Contentblocks
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_7') {
									if ($history[$i]['NEWVALUE']) {
										$cb = sCblockMgr()->getCblock($history[$i]['NEWVALUE']);
										if ($cb) {
											$cblockInfo = $cb->get();
											$cblockInfo['RWRITE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
											$cblockInfo['RDELETE'] = $cb->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");
											$styleData = getStyleForContentblock($cblockInfo, true);
											$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'cblock', '".$cblockInfo['NAME']."', 'cblock', '".$styleData."');\">".$cblockInfo['NAME']."</a>";
										}
									}
								}

								// Special Case for Tags
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_8') {
									$tagInfo = $page->tags->get($history[$i]['NEWVALUE']);
									$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'tag', '".$tagInfo['NAME']."', 'tag', '');\">".$tagInfo['NAME']."</a>";
								}

								// Special Case for Date
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_11') {
									$history[$i]['NEWVALUE'] = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
								}

								// Special Case for Datetime
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_12') {
									$dateString = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
									$timeString = date('H', TStoLocalTS($history[$i]['NEWVALUE'])).':'.date('i', TStoLocalTS($history[$i]['NEWVALUE']));
									$history[$i]['NEWVALUE'] = $dateString.' '.$timeString;
								}

								// Special Case for Pages
								if ($history[$i]['TEXT'] == 'TXT_COMMON_H_COEDIT_FRMFLD_15') {
									$currSiteID = explode( '-', $history[$i]['NEWVALUE'] );
									$currPageID = $currSiteID[0];
									$currSiteID = $currSiteID[1];
									if ($currPageID && $currSiteID) {
										$currPageMgr = new PageMgr($currSiteID);
										$currPage = $currPageMgr->getPage($currPageID);
										if ($currPage) {
											$currPageInfo = $currPage->get();
											$currPageInfo['RWRITE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
											$currPageInfo['RDELETE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");
											$iconData = getIconForPage($currPageInfo);
											$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."-".$history[$i]['TARGETID']."', 'page', '".$currPageInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$currPageInfo['NAME']."</a>";
										}
									}
								}

								$history[$i]['CONTENTAREA'] = $contentarea_name;
								$history[$i]['FORMFIELD'] = $formfield_name;
								$history[$i]['EMBLOCK'] = $control_name;
								$history[$i]['TYPE'] = 'COEDIT';
							}

					}
				}
				/*
				if ($history[$i]['TAB'] == 'P_PROPERTIES') {
					if ( ($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP') ) {
						$history[$i]['CONTENTAREA'] = $history[$i]['OLDVALUE'];
					}
				}
				*/

				if ($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP_FILE') {
					if ($history[$i]['NEWVALUE']) {
						$file = sFileMgr()->getFile($history[$i]['NEWVALUE']);
						if ($file) {
							$fileInfo = $file->get();
							if ($fileInfo) {
								$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$history[$i]['NEWVALUE'].'\', event);"><span style="display:inline-block;" class="filetype '.$fileInfo['COLOR'].'">'.$fileInfo['CODE'].'</span> '.$fileInfo['NAME'].'</span>';
							} else {
								$history[$i]['NEWVALUE'] = $itext['TXT_PAGE_H_PROP_FILE_REMOVED'];
							}
						}
					}
				}

				if ($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP') {
					if (strpos($history[$i]['OLDVALUE'], 'TXT_') === 0) {
						if ($itext[$history[$i]['OLDVALUE']]) {
							$history[$i]['OLDVALUE'] = $itext[$history[$i]['OLDVALUE']];
						}
					}
				}
				if ($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP_RICHTEXT') {
					$history[$i]['NEWVALUE'] = $itext['TXT_COMMON_H_COEDIT_FRMFLD_3'];
				}
				if 	($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP_TAG') {
					$tagInfo = $tagMgr->get($history[$i]['NEWVALUE']);
					$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'tag', '".$tagInfo['NAME']."', 'tag', '');\">".$tagInfo['NAME']."</a>";
				}
				if 	($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP_CBLOCK') {
					if ($history[$i]['NEWVALUE']) {
						$cb = sCblockMgr()->getCblock($history[$i]['NEWVALUE']);
						if ($cb) {
							$cblockInfo = $cb->get();
							$history[$i]['NEWVALUE'] = "<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."', 'cblock', '".$cblockInfo['NAME']."', 'cblock', '');\">".$cblockInfo['NAME']."</a>";
						}
					}
				}
				if 	($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP_PAGE') {
					if ($history[$i]['TARGETID'] && $history[$i]['NEWVALUE']) {
						$currPageMgr = new PageMgr($history[$i]['TARGETID']);
						$currPage = $currPageMgr->getPage($history[$i]['NEWVALUE']);
						if ($currPage) {
							$currPageInfo = $currPage->get();
							$currPageInfo['RWRITE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RWRITE");
							$currPageInfo['RDELETE'] = $currPage->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $history[$i]['NEWVALUE'], "RDELETE");
							$iconData = getIconForPage($currPageInfo);
							$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$history[$i]['NEWVALUE']."-".$history[$i]['TARGETID']."', 'page', '".$currPageInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$currPageInfo['NAME']."</a>";
						}
					}
				}
				if 	($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP_DATE') {
					$history[$i]['NEWVALUE'] = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
				}
				if 	($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP_DATETIME') {
					$dateString = date($itext['DATE_FORMAT'], TStoLocalTS($history[$i]['NEWVALUE']));
					$timeString = date('H', TStoLocalTS($history[$i]['NEWVALUE'])).':'.date('i', TStoLocalTS($history[$i]['NEWVALUE']));
					$history[$i]['NEWVALUE'] = $dateString.' '.$timeString;
				}
				if 	($history[$i]['TEXT'] == 'TXT_PAGE_H_PROP_LINK') {
					$special_url = resolveSpecialURL($history[$i]['NEWVALUE']);
					if ($special_url !== false) {
						$target_aid = '';
						$target_id = '';
						$target_type = 0;

						$special_url_info = getSpecialURLInfo($history[$i]['NEWVALUE']);
						if ($special_url_info['TYPE']=='DOWN') {
							$target_type = 'FILE';
							$target_id = $special_url_info['ID'];
							if ($target_id) {
								$file = sFileMgr()->getFile($target_id);
								if ($file) {
									$objectInfo = $file->get();
									$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
								}
							}
						} else if ($special_url_info['TYPE']=='IMG') {
							$target_type = 'IMAGE';
							$target_id = $special_url_info['ID'];
							if ($target_id) {
								$file = sFileMgr()->getFile($target_id);
								if ($file) {
									$objectInfo = $file->get();
									$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
								}
							}
						} else {
							$target_type = 'PAGE';
							$oldsite = $siteID;
							if ($special_url_info['SITE'] && $special_url_info['ID']) {
								$iPageMgr = new PageMgr($special_url_info['SITE']);
								$iPage = $iPageMgr->getPage($special_url_info['ID']);
								if ($iPage) {
									$objectInfo = $iPage->get();
									$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RWRITE");
									$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $special_url_info['ID'], "RDELETE");
									$iconData = getIconForPage($objectInfo);
									$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$special_url_info['ID'].'-'.$special_url_info['SITE']."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
								}
							}
						}
					} else if (preg_match_all($this->URLRegEx1, $history[$i]['NEWVALUE'], $internal) > 0) {
						$target_aid = '';
						$target_id = '';
						$target_type = 0;

						if ($internal[2][0] == 'download') {
							$target_type = 'FILE';
							$target_id = $internal[3][0];
							if ($target_id) {
								$file = sFileMgr()->getFile($target_id);
								if ($file) {
									$objectInfo = $file->get();
									$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$target_id.'\', event);"><span style="display:inline-block;" class="filetype '.$objectInfo['COLOR'].'">'.$objectInfo['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$target_id."', 'file', '".$objectInfo['NAME']."', {color:'".$objectInfo['COLOR']."',typecode:'".$objectInfo['CODE']."'});\">".$objectInfo['NAME']."</a></span>";
								}
							}
						} else if ($internal[2][0] == 'page') {
							preg_match_all($this->URLRegEx2, $history[$i]['NEWVALUE'], $linkinfo);
							$target_type = 'PAGE';
							$oldsite = $siteID;
							if ($linkinfo[3][0] && $linkinfo[4][0]) {
								$iPageMgr = new PageMgr($linkinfo[3][0]);
								$iPage = $iPageMgr->getPage($linkinfo[4][0]);
								if ($iPage) {
									$objectInfo = $iPage->get();
									$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RWRITE");
									$objectInfo['RDELETE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $linkinfo[4][0], "RDELETE");
									$iconData = getIconForPage($objectInfo);
									$history[$i]['NEWVALUE'] = '<div class="icon'.$iconData['iconclass'].'"></div>'."<a onclick=\"\$K.yg_openObjectDetails('".$linkinfo[4][0].'-'.$linkinfo[3][0]."', 'page', '".$objectInfo['NAME']."', '".$iconData['iconclass']."', '".$iconData['style']."');\">".$objectInfo['NAME']."</a>";
								}
							}
						} else if ($internal[2][0] == 'image') {
							$target_type = 'IMAGE';
							$target_id = $internal[3][0];
						}
					} else if (strpos($history[$i]['NEWVALUE'], 'mailto:') === 0) {
						$history[$i]['NEWVALUE'] = '<div class="iconemail"></div>'.str_replace( 'mailto:', '', $history[$i]['NEWVALUE'] );
					} else {
						$linkInfo = checkLinkInternalExternal( $history[$i]['NEWVALUE'] );
						switch($linkInfo['TYPE']) {
							case 'external':
								$history[$i]['NEWVALUE'] = '<div class="iconlink"></div>'.$history[$i]['NEWVALUE'];
								break;
							case 'internal':
								$target_type = 'PAGE';
								$history[$i]['NEWVALUE'] = '<div class="iconpage"></div>'.$linkInfo['NAME'];
								break;
							case 'file':
								$target_type = 'FILE';
								$history[$i]['NEWVALUE'] = '<span onmouseover="$K.yg_hoverFileHint(\''.$linkInfo['INFO']['FILE_ID'].'\', event);"><span style="display:inline-block;" class="filetype '.$linkInfo['INFO']['COLOR'].'">'.$linkInfo['INFO']['CODE'].'</span> '."<a onclick=\"\$K.yg_openObjectDetails('".$linkInfo['INFO']['FILE_ID']."', 'file', '".$linkInfo['NAME']."', {color:'".$linkInfo['INFO']['COLOR']."',typecode:'".$linkInfo['INFO']['CODE']."'});\">".$linkInfo['NAME']."</a></span>";
								break;
						}
					}
				}

				// For Extension-Logging
				if ($history[$i]['TEXT'] == 'TXT_EXTENSION_H_LOGENTRY') {
					$extensionManager = new ExtensionMgr();
					$extensionInfo = $extensionManager->get($history[$i]['OLDVALUE']);
					$history[$i]['NEWVALUE'] = '<div class="modified">'.$extensionInfo['NAME'].' <em>'.$history[$i]['NEWVALUE'].'</em></div>';
				}

				$lastuserid = $history[$i]["UID"];

				$tKey = $history[$i]['TEXT'];
				$pKey = $history[$i]['TAB'];
				$dKey = $history[$i]['DATETIME'];

				// Only get history entries for Yeager
				if ( strpos($tKey, 'TXT_') === 0 ) {

					$show_entry = false;

					if ($filterAction=='ONLY_VERSIONS') {
						if ($pKey == 'P_VERSIONS') {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else if ($filterTab!='ALL') {
						if ($pKey==$filterTab) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else {
						$show_entry = true;
					}

					if ($filterTimeframe=='LAST_WEEK') {
						if ( ((time()-604800) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_2_WEEKS') {
						if ( ((time()-1209600) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_4_WEEKS') {
						if ( ((time()-2419200) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} elseif ($filterTimeframe=='LAST_8_WEEKS') {
						if ( ((time()-4838400) <  $dKey) && ($show_entry)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}
					} else {

						// custom timeframe
						list ($timefrom, $timetill) = explode("###", $filterTimeframe);

						$timefrom = TSfromLocalTS(strtotime($timefrom));
						$timetill = TSfromLocalTS(strtotime($timetill) + 24*60*60);

						if ( ($dKey > $timefrom) && ($dKey < $timetill)) {
							$show_entry = true;
						} else {
							$show_entry = false;
						}

					}

					// Check for autopublish changes in a row (within 300 secs, and reduce them to one entry)
					if ( (($tKey=='TXT_PAGE_H_AUTOPUBLISH_CHANGED') || ($tKey=='TXT_PAGE_H_AUTOPUBLISH_ADDED')) &&
						 (($last_entry_type=='TXT_PAGE_H_AUTOPUBLISH_ADDED') || ($last_entry_type=='TXT_PAGE_H_AUTOPUBLISH_CHANGED')) &&
						 ($last_entry_item_id==$history[$i]['TARGETID']) &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								if (!$test)
								$show_entry = false;

							}
							if ($tKey=='TXT_PAGE_H_AUTOPUBLISH_ADDED') {
								$add_in_row = true;
							}
					}

					// Check for tagorder changes in a row (within 300 secs, and reduce them to one entry)
					if ( ($tKey=='TXT_TAG_H_TAGORDER') &&
						 ($last_entry_type=='TXT_TAG_H_TAGORDER') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					// Check for contentblock order changes in a row (within 300 secs, and reduce them to one entry)
					if ( ($tKey=='TXT_PAGE_H_COORDER') &&
						 ($last_entry_type=='TXT_PAGE_H_COORDER') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					// Check for datetime changes in a row (within 300 secs, and reduce them to one entry) (Properties)
					if ( ($tKey=='TXT_PAGE_H_PROP_DATETIME') &&
						 ($last_entry_type=='TXT_PAGE_H_PROP_DATETIME') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					// Check for datetime changes in a row (within 300 secs, and reduce them to one entry) (Controls)
					if ( ($tKey=='TXT_COMMON_H_COEDIT_FRMFLD_12') &&
						 ($last_entry_type=='TXT_COMMON_H_COEDIT_FRMFLD_12') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					// Check for datetime changes in a row (within 300 secs, and reduce them to one entry) (Extensions)
					if ( ($tKey=='TXT_OBJECT_H_EXTEDIT_FRMFLD_12') &&
						 ($last_entry_type=='TXT_OBJECT_H_EXTEDIT_FRMFLD_12') &&
						 ($last_entry_userid==$history[$i]['USERID']) ) {
							$timediff = $last_entry_timestamp - $dKey;
							if ($timediff<300) {
								$show_entry = false;
							}
					}

					$last_entry_type = $tKey;
					$last_entry_item_id = $history[$i]['TARGETID'];
					$last_entry_timestamp = $dKey;
					$last_entry_userid = $history[$i]['USERID'];

					if ($show_entry) {
						$add_in_row = false;
						$real_history[$real_history_cnt] = $history[$i];
						$real_history_cnt++;
					}

					if (!$test)
					if ($add_in_row) {
						$index = $real_history_cnt-1;
						if ($index >= 0) {
							if ($real_history[$index]['TEXT']=='TXT_PAGE_H_AUTOPUBLISH_CHANGED') {
								$real_history[$index]['TEXT'] = 'TXT_PAGE_H_AUTOPUBLISH_ADDED';
							}
						}
					}
				}
			}
			$objectInfo = $page->get();
			$objectInfo['RWRITE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RWRITE");
			$objectInfo['RSTAGE'] = $page->permissions->checkInternal(sUserMgr()->getCurrentUserID(), $objectID, "RSTAGE");

			if ($objectInfo['DELETED']) {
				$objectInfo['RWRITE'] = false;
				$objectInfo['READONLY'] = true;
				$objectInfo['RSTAGE'] = false;
			}

			// Get current locks for this token (and unlock them)
			$lockToken = sGuiUS().'_'.$this->request->parameters['win_no'];
			$lockedObjects = $pageMgr->getLocksByToken($lockToken);
			foreach($lockedObjects as $lockedObject) {
				if ($lockedObject['OBJECTID']) {
					$currentObject = $pageMgr->getPage($lockedObject['OBJECTID']);
					if ($currentObject) {
						$currentObject->releaseLock($lockedObject['TOKEN']);
					}
				}
			}
			// Check for lock, and lock if not locked
			$lockStatus = $page->getLock();
			if ($lockStatus['LOCKED'] == 0) {
				$lockedFailed = !$page->acquireLock($lockToken);
			} else {
				$lockedFailed = true;
			}

		}
		break;
}

foreach($real_history as $real_history_idx => $real_history_item) {
	$real_history[$real_history_idx]['DATETIME'] = TStoLocalTS($real_history_item['DATETIME']);
}

if ($lockedFailed) {
	// Get user who locked this object
	$userWithLock = new User( $lockStatus['LOCKUID'] );
	$lockedByUser = $userWithLock->get( $lockStatus['LOCKUID'] );
	$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $lockStatus['LOCKUID'] );
	$smarty->assign('lockedByUser', $lockedByUser );
	$objectInfo['RWRITE'] = false;
	if (($objecttype=='cblock') || ($objecttype=='page')) {
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\');');
	}
} else {
	if (($objecttype=='cblock') || ($objecttype=='page')) {
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$objectInfo['RSTAGE'].'\');');
	}
}

if ($objecttype == 'mailing') {
	// Check if a send is in progress (and lock if true)
	$mailingStatus = $mailing->getStatus();
	if ($mailingStatus['STATUS'] == 'INPROGRESS') {
		$userWithLock = new User( $mailingStatus['UID'] );
		$lockedByUser = $userWithLock->get( $mailingStatus['UID'] );
		$lockedByUser['PROPS'] = $userWithLock->properties->getValues( $mailingStatus['UID'] );
		$smarty->assign('lockedByUser', $lockedByUser );
		$objectInfo['RWRITE'] = false;
		$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \'0\');');
	} else {
		if (!$lockedFailed) {
			$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setStageButton( \''.$objectInfo['RSTAGE'].'\');');
		}
	}
}

$koala->queueScript('Koala.windows[\'wid_'.$this->request->parameters['win_no'].'\'].setLocked( \''.$lockedByUser['ID'].'\' );');

$smarty->assign("type", $objecttype);
$smarty->assign("history", $real_history);
$smarty->assign("site", $siteID );
$smarty->assign("refresh", $refresh );
$smarty->assign("objectid", $objectID );
$smarty->assign("objectInfo", $objectInfo);
$smarty->assign("win_no", $this->request->parameters['win_no']);
$smarty->display('file:'.$this->page_template);

?>