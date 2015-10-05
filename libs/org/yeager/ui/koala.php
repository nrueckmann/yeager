<?php

/**
 * Class 'Koala'
 * Supplies methods to communicate with the ajax-clientside.
 *
 */

function sKoala() {
	return Singleton::koala();
}

function sUI() {
	return Singleton::koala();
}

class Koala {

	private $db = '';
	private $responderData = '';
	private $responderHandler = '';
	private $scriptQueue = '';

	public $sequence = '';

	/**
	 * Class constructor
	 */
	function Koala ( $db ) {
		// Fetch and store database handler
		$this->db = $db;
	}

	/**
	 * Function to generate a new sequence number
	 */
	function genSequence() {
		// Get value
		$sql = "SELECT counter FROM yg_koala_sequencer LIMIT 1";
		$result = $this->db->Execute($sql);
		$counter = $result->fields['counter'];

		// Set new value
		$sql = "UPDATE yg_koala_sequencer SET counter = ($counter+1000);";
		$this->db->Execute($sql);

		$this->sequence = $counter;

		return $this->sequence;
	}

	/**
	 * Function to get the queued commands from the history
	 */
	public function getQueuedCommands() {
		$entrymaskMgr = new Entrymasks();
		$jsQueue = new JSQueue(NULL);
		$tagMgr = new Tags();
		$queuedCommands = array();
		$currentQueueId = sGuiLH();

		if (!$currentQueueId || ($currentQueueId == 'false')) {
			return;
			// if running first time (only)
			//$currentQueueId = $jsQueue->getLastQueueId();
		}
		if ($currentQueueId) {
			$queuedCommandsRaw = $jsQueue->getQueue( $currentQueueId, sGuiUS() );
			$templateMgr = new Templates();
			$viewMgr = new Views();

			foreach($queuedCommandsRaw as $queuedCommandRaw) {
				// Check permissions
				$permissionsObj = NULL;
				$objectID = $queuedCommandRaw['OID'];
				$siteID = $queuedCommandRaw['SITEID'];
				$icons = new Icons();
				$url = $imgurl = '';

				switch($queuedCommandRaw['TYPE']) {
					case HISTORYTYPE_MAILING:
						$mailingMgr = new MailingMgr();
						$mailingObj = $mailingMgr->getMailing($objectID);
						$permissionsObj = $mailingObj->permissions;
						break;
					case HISTORYTYPE_PAGE:
						if (($siteID > 0) && ($objectID > 0)) {
							$pageMgr = new PageMgr($siteID);
							$pageObj = $pageMgr->getPage($objectID);
							if ($pageObj) {
								$url = $pageObj->getUrl();
								$permissionsObj = $pageObj->permissions;
							}
						}
						break;
					case HISTORYTYPE_CO:
						if (!$objectID) continue;
						$cb = sCblockMgr()->getCblock($objectID);
						$permissionsObj = $cb->permissions;
						break;
					case HISTORYTYPE_ENTRYMASK:
						$permissionsObj = $entrymaskMgr->permissions;
						break;
					case HISTORYTYPE_FILE:
						$permissionsObj = sFileMgr()->permissions;
						if ($objectID) {
							$file = sFileMgr()->getFile($objectID);
							if ($file) {
								$info = $file->get();
								$url = sApp()->webroot."download/".$info['PNAME']."/";
								$hiddenviews = $file->views->getHiddenViews();
								foreach($hiddenviews as $hiddenview) {
									if ($hiddenview['IDENTIFIER'] == "YGSOURCE") {
										$tmpviewinfo = $file->views->getGeneratedViewInfo($hiddenview['ID']);
										if ($tmpviewinfo[0]['TYPE'] == FILE_TYPE_WEBIMAGE) {
											$imgurl = sApp()->webroot."image/".$info['PNAME']."/";
										}
									}
								}
							}
						}
						break;
					case HISTORYTYPE_TEMPLATE:
						$permissionsObj = $templateMgr->permissions;
						break;
					case HISTORYTYPE_TAG:
						$permissionsObj = $tagMgr->permissions;
						break;
					case HISTORYTYPE_SITE:
						$pageMgr = new PageMgr($siteID);
						$sitePages = $pageMgr->tree->get(0,1);
						$tmpPageID = $sitePages[0]["ID"];
						if ($tmpPageID) {
							$pageObj = $pageMgr->getPage($tmpPageID);
							$permissionsObj = $pageObj->permissions;
						}
						break;
					case HISTORYTYPE_USER:
						$permissionsObj = sUsergroups()->usergroupPermissions;
						break;
					case HISTORYTYPE_USERGROUP:
					case HISTORYTYPE_EXTERNAL:
					case HISTORYTYPE_IMAGE:
					case HISTORYTYPE_FILETYPES:
					case HISTORYTYPE_FILEVIEWS:
					case HISTORYTYPE_JSQUEUE:
					case HISTORYTYPE_PERMISSION:
					default:
						break;
				}

				if ( ($queuedCommandRaw['TEXT'] == 'NOPERMISSIONCHECK') ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'HIGHLIGHT')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'UNHIGHLIGHT')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'PAGE_MOVE')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'PAGE_HIDE')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'PAGE_UNHIDE')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'PAGE_ACTIVATE')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'RELOAD_WINDOW')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'CLEAR_USERINFOS')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'SET_USERINFOS')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'CLEAR_FILEINFOS')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'REFRESH_WINDOW')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'ADD_FILE')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'OBJECT_DELETE')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'OBJECT_ADD_TAG')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'OBJECT_CHANGE')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'OBJECT_CHANGECLASS')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'OBJECT_CHANGEPNAME')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'OBJECT_CHANGEBGIMAGE')===0) ||
					 (strpos($queuedCommandRaw['OLDVALUE'], 'OBJECT_CHANGE_LOCK_STATE')===0) ) {
					$allowed = true;
				} else if ($permissionsObj != NULL) {
					$allowed = $permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RREAD" );
				}

				if (($permissionsObj != NULL) || $allowed) {

					if ($allowed) {
						$itext = sItext();
						switch($queuedCommandRaw['OLDVALUE']) {
							case 'UNHIGHLIGHT':
								if ($queuedCommandRaw['TEXT']) {
									//$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_unHilite(\''.$queuedCommandRaw['TEXT'].'\', \''.$objectID.'-template\', \''.$queuedCommandRaw['TEXT'].'\');';
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_unHilite(\''.$queuedCommandRaw['TEXT'].'\', \''.$queuedCommandRaw['VALUE1'].'\', \''.$queuedCommandRaw['VALUE2'].'\');';
								}
								break;
							case 'OBJECT_CHANGE_LOCK_STATE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_changeWindowLockStateForObject(\''.$queuedCommandRaw['TEXT'].'\', \''.$queuedCommandRaw['VALUE1'].'\', \''.$queuedCommandRaw['VALUE2'].'\');';
								}
								break;
							case 'OBJECT_CHANGEBGIMAGE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_changeBGImage(\''.$queuedCommandRaw['TEXT'].'\', \''.$queuedCommandRaw['VALUE1'].'\', \''.$queuedCommandRaw['VALUE2'].'\', \''.$queuedCommandRaw['VALUE3'].'\');';
								}
								break;
							case 'OBJECT_CHANGECLASS':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_changeClass(\''.$queuedCommandRaw['TEXT'].'\', \''.$queuedCommandRaw['VALUE1'].'\', \''.$queuedCommandRaw['VALUE2'].'\', \''.$queuedCommandRaw['VALUE3'].'\');';
								}
								break;
							case 'OBJECT_CHANGEPNAME':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_changePName(\''.$queuedCommandRaw['TEXT'].'\', \''.$queuedCommandRaw['VALUE1'].'\', \''.$queuedCommandRaw['VALUE2'].'\', \''.$queuedCommandRaw['VALUE3'].'\', \''.$url.'\', \''.$imgurl.'\');';
								}
								break;
							case 'OBJECT_CHANGE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_change(\''.$queuedCommandRaw['TEXT'].'\', \''.addslashes($queuedCommandRaw['VALUE1']).'\', \''.addslashes($queuedCommandRaw['VALUE2']).'\', \''.addslashes($queuedCommandRaw['VALUE3']).'\');';
								}
								break;
							case 'OBJECT_ADD_TAG':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_addTag(\''.$queuedCommandRaw['TEXT'].'\', \''.$queuedCommandRaw['VALUE1'].'\', \''.$queuedCommandRaw['VALUE2'].'\', \''.$queuedCommandRaw['VALUE3'].'\', \''.$queuedCommandRaw['VALUE4'].'\', '.stripslashes($queuedCommandRaw['VALUE5']).', \''.$queuedCommandRaw['VALUE6'].'\', \''.$queuedCommandRaw['VALUE7'].'\');';
								}
								break;
							case 'OBJECT_DELETE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_del(\''.$queuedCommandRaw['TEXT'].'\', \''.$queuedCommandRaw['VALUE1'].'\', \''.$queuedCommandRaw['VALUE2'].'\', \''.$queuedCommandRaw['VALUE3'].'\', \''.$queuedCommandRaw['VALUE4'].'\');';
								}
								break;
							case 'ADD_FILE':
								$file = new File($objectID);
								$latestVersion = $file->getLatestApprovedVersion();
								$file = new File($objectID, $latestVersion);
								$fileInfo = $file->get();
								$reftracker = new Reftracker();

								if ($fileInfo['CREATEDBY']) {
									$user = new User($fileInfo['CREATEDBY']);
									$userInfo = $user->get();
									$userInfo['PROPS'] = $user->properties->getValues( $fileInfo['CREATEDBY'] );
								}

								$fileInfo['CUSTOM_DATE'] = date('d.m.Y', TStoLocalTS($fileInfo['CHANGEDTS']));
								$fileInfo['CUSTOM_TIME'] = date('G:i', TStoLocalTS($fileInfo['CHANGEDTS']));
								$fileInfo['REFS'] = $reftracker->getIncomingForFile( $fileInfo['OBJECTID'] );

								$tags = $file->tags->getAssigned();
								for ($t = 0; $t < count($tags); $t++) {
									$tp = array();
									$tp = $file->tags->tree->getParents($tags[$t]['ID']);
									$tp2 = array();
									for ($p = 0; $p < count($tp); $p++) {
										$tinfo = $file->tags->get($tp[$p]);
										$tp2[$p]['ID'] = $tinfo['ID'];
										$tp2[$p]['NAME'] = $tinfo['NAME'];
									}
									$tp2[count($tp2)-1]['NAME'] = ($itext['TXT_TAGS']!='')?($itext['TXT_TAGS']):('$TXT_TAGS');
									$tags[$t]['PARENTS'] = $tp2;
								}
								$fileInfo['TAGS'] = $tags;

								$fileInfo['THUMB'] = 1;
								if ($queuedCommandRaw['TEXT'] == 'nothumb')  {
									$fileInfo['THUMB'] = 0;
								}

								$views = $file->views->getAssigned();
								foreach($views as $view) {
									if ($view["IDENTIFIER"] == "YGSOURCE") {
										$viewinfo = $file->views->getGeneratedViewInfo($view["ID"]);
										$fileInfo["WIDTH"] = $viewinfo[0]["WIDTH"];
										$fileInfo["HEIGHT"] = $viewinfo[0]["HEIGHT"];
									}
								}

								$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_addFile(\'file\', \''.$fileInfo['PARENT'].'-file\', \''.$objectID.'\', \''.$fileInfo['THUMB'].'\', \''.$fileInfo['COLOR'].'\', \''.$fileInfo['CODE'].'\', \''.$fileInfo['NAME'].'\', \''.$fileInfo['PNAME'].'\', \''.json_encode($fileInfo['TAGS']).'\', \''.$fileInfo['FILESIZE'].'\', \''.count($fileInfo['REFS']).'\', \''.TStoLocalTS($fileInfo['CHANGEDTS']).'\', \''.$fileInfo['CUSTOM_DATE'].'\', \''.$fileInfo['CUSTOM_TIME'].'\', \''.$fileInfo['UID'].'\', \''.$userInfo['PROPS']['FIRSTNAME'].' '.$userInfo['PROPS']['LASTNAME'].'\', \''.$fileInfo['FILENAME'].'\', \''.$fileInfo["WIDTH"].'\', \''.$fileInfo['HEIGHT'].'\');';
								break;
							case 'REFRESH_TAGS':
								if ($queuedCommandRaw['TEXT']) {
									switch($queuedCommandRaw['TYPE']) {
										case HISTORYTYPE_CO:
											$objType = 'cblock';
											break;
										case HISTORYTYPE_FILE:
											$objType = 'file';
											break;
									}
								}
								$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_refreshTags(\''.$objType.'\', \''.$objectID.'-'.$objType.'\', \'tags\', \''.$queuedCommandRaw['TEXT'].'\');';
								break;
							case 'REFRESH_WINDOW':
								if ($queuedCommandRaw['TEXT']) {
									switch($queuedCommandRaw['TYPE']) {
										case HISTORYTYPE_CO:		$objType = 'cblock'; break;
										case HISTORYTYPE_PAGE:		$objType = 'page'; break;
										case HISTORYTYPE_FILE:		$objType = 'file'; break;
										case HISTORYTYPE_TAG:		$objType = 'tag'; break;
										case HISTORYTYPE_TEMPLATE:	$objType = 'template'; break;
										case HISTORYTYPE_ENTRYMASK:	$objType = 'entrymask'; break;
										case HISTORYTYPE_SITE:		$objType = 'site'; break;
									}
									// Special cases
									switch($queuedCommandRaw['TYPE']) {
										case HISTORYTYPE_PAGE:
											$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_refreshWin(\''.$objType.'\',\''.$objectID.'-'.$siteID.'\',\''.$queuedCommandRaw['TEXT'].'\');';
											break;
										case HISTORYTYPE_FILE:
											$queuedCommands[$queuedCommandRaw['ID']]  = 'Koala.yg_refreshWin(\''.$objType.'\',\''.$objectID.'-'.$objType.'\',\''.$queuedCommandRaw['TEXT'].'\');';
											$queuedCommands[$queuedCommandRaw['ID']] .= 'Koala.yg_refreshWin(\''.$objType.'folder\',\''.$objectID.'-'.$objType.'\',\''.$queuedCommandRaw['TEXT'].'\');';
											break;
										default:
											$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_refreshWin(\''.$objType.'\',\''.$objectID.'-'.$objType.'\',\''.$queuedCommandRaw['TEXT'].'\');';
											break;
									}
								}
								break;
							case 'CLEAR_FILEINFOS':
								$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_fileInfos['.$objectID.'] = null;';
								break;
							case 'SET_FILEINFOS':
								$file = sFileMgr()->getFile($objectID);
								if ($file) {
									$latestFinalVersion = $file->getLatestApprovedVersion();
									$file = new File($objectID, $latestFinalVersion);

									$fileInfo = $file->get();
									$fileTypes = sFileMgr()->getFiletypes();

									$user = new User(sUserMgr()->getCurrentUserID());

									$fileInfo['DATE'] = date( $itext['DATE_FORMAT'], TStoLocalTS($fileInfo['CHANGEDTS']) );
									$fileInfo['TIME'] = date( $itext['TIME_FORMAT'], TStoLocalTS($fileInfo['CHANGEDTS']) );

									$fileInfo['FILESIZE'] = formatFileSize($fileInfo['FILESIZE']);

									$views = $file->views->getAssigned(true);
									$viewInfo = $file->views->getGeneratedViewInfo($views[0]["ID"]);
									$fileInfo['WIDTH'] = $viewInfo[0]["WIDTH"];
									$fileInfo['HEIGHT'] = $viewInfo[0]["HEIGHT"];

									$fileInfo['TAGS'] = $file->tags->getAssigned();

									$tags = array();
									foreach( $fileInfo['TAGS'] as $tag) {
										array_push( $tags, $tag['NAME'] );
									}
									$fileTags = implode( ', ', $tags );
									if ( strlen($fileTags) > 40 ) {
										$fileTags = substr($fileTags, 0, 40);
										$fileTags .= '...';
									}
									$fileInfo['TAGS'] = $fileTags;

									if ( strlen($fileInfo['NAME']) > 40 ) {
										$fileInfo['NAME'] = substr($fileInfo['NAME'], 0, 40);
										$fileInfo['NAME'] .= '...';
									}
									if ( strlen($fileInfo['FILENAME']) > 40 ) {
										$fileInfo['FILENAME'] = substr($fileInfo['FILENAME'], 0, 40);
										$fileInfo['FILENAME'] .= '...';
									}

									if ($fileInfo['CREATEDBY']) {
										$user = new User($fileInfo['CREATEDBY']);
										$userInfo = $user->get();
										$userInfo['PROPS'] = $user->properties->getValues( $fileInfo['CREATEDBY'] );
										$fileInfo['USERNAME'] = $userInfo['PROPS']['FIRSTNAME'].' '.$userInfo['PROPS']['LASTNAME'];
									}

									foreach($fileTypes as $fileTypes_item) {
										if ($fileTypes_item['ID'] == $fileInfo['FILETYPE']) {
											$fileInfo['FILETYPE_TXT'] = $fileTypes_item['NAME'];
										}
									}

									$fileInfo['THUMB'] = 0;
									$hiddenViews = $file->views->getHiddenViews();
									foreach($hiddenViews as $view) {
										if ($view['IDENTIFIER'] == 'yg-preview') {
											$tmpviewinfo = $file->views->getGeneratedViewInfo($view["ID"]);
											if ($tmpviewinfo[0]["TYPE"] == FILE_TYPE_WEBIMAGE) {
												$fileInfo['THUMB'] = 1;
												$fileInfo['PREVIEWWIDTH'] = $tmpviewinfo[0]["WIDTH"];
												$fileInfo['PREVIEWHEIGHT'] = $tmpviewinfo[0]["HEIGHT"];
											}
										}
									}
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_fileInfos['.$objectID.'] = '.json_encode($fileInfo).';Koala.yg_showFileHint(\''.$objectID.'\');';
								}
								break;
							case 'SET_USERINFOS':
								$user = new User($objectID);
								$userInfo = $user->get();
								$userInfo['PROPS'] = $user->properties->getValues( $objectID );
								$userInfo['USERGROUPS'] = $user->getUsergroups( $objectID );

								$roles = array();
								foreach( $userInfo['USERGROUPS'] as $role) {
									array_push( $roles, $role['NAME'] );
								}
								$user_roles = implode( ', ', $roles );
								if ( strlen($user_roles) > 30 ) {
									$user_roles = substr($user_roles, 0, 30);
									$user_roles .= '...';
								}

								if (file_exists(sApp()->app_root.sApp()->userpicdir.$objectID.'-picture.jpg')) {
									$internPrefix = (string)sConfig()->getVar('CONFIG/REFTRACKER/INTERNALPREFIX');
									$user_picture = $internPrefix.'userimage/'.$objectID.'/48x48?rnd='.rand();
								} else {
									$user_picture = sApp()->imgpath.'content/temp_userpic.png';
								}

								$user_company = $userInfo['PROPS']['COMPANY'];
								$user_name = $userInfo['PROPS']['FIRSTNAME'].' '.$userInfo['PROPS']['LASTNAME'];

								$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_userInfos['.$objectID.'] = {name: \''.$user_name.'\', groups: \''.$user_roles.'\', pic: \''.$user_picture.'\', company: \''.$user_company.'\'}';
								break;
							case 'CLEAR_USERINFOS':
								$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_userInfos['.$objectID.'] = null;';
								break;
							case 'CLEAR_REFRESH':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_clearRefresh(\''.$objectID.'-'.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'RELOAD_WINDOW':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_reloadWin(null, \''.$objectID.'-'.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'PAGE_DEACTIVATE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_deActivate(\'page\', \''.$objectID.'-'.$siteID.'\', \'name\');';
								}
								break;
							case 'PAGE_ACTIVATE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_activate(\'page\', \''.$objectID.'-'.$siteID.'\', \'name\');';
								}
								break;
							case 'PAGE_UNHIDE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_unHide(\'page\', \''.$objectID.'-'.$siteID.'\', \'name\');';
								}
								break;
							case 'PAGE_HIDE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_hide(\'page\', \''.$objectID.'-'.$siteID.'\', \'name\');';
								}
								break;
							case 'FILE_DELVIEW':
								if ($queuedCommandRaw['TEXT']) {
									$file = sFileMgr()->getFile( $objectID );
									$fileInfo = $file->get();
									if ($fileInfo['FOLDER']==1) {
										$isFolder = 'true';
									} else {
										$isFolder = 'false';
									}
									$queuedCommands[$queuedCommandRaw['ID']] = 'if (Koala.yg_delViewArr['.$queuedCommandRaw['TEXT'].']) Koala.yg_delViewArr['.$queuedCommandRaw['TEXT'].']('.$objectID.', '.$isFolder.');';
								}
								break;
							case 'FILE_CLEAR_DELVIEW':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'if (Koala.yg_delViewArr['.$queuedCommandRaw['TEXT'].']) Koala.yg_delViewArr['.$queuedCommandRaw['TEXT'].']=undefined;';
								}
								break;
							case 'FILE_ADDVIEW':
								if ($queuedCommandRaw['TEXT']) {
									$file = sFileMgr()->getFile( $objectID );
									$fileInfo = $file->get();
									$viewInfo = $viewMgr->get( $queuedCommandRaw['TEXT'] );
									if ($fileInfo['FOLDER']==1) {
										$isFolder = 'true';
									} else {
										$isFolder = 'false';
									}
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_addView(\''.$objectID.'\', \''.$viewInfo['ID'].'\', \''.$viewInfo['IDENTIFIER'].'\', \''.$viewInfo['NAME'].'\', \''.$viewInfo['WIDTH'].'\', \''.$viewInfo['HEIGHT'].'\', \''.$isFolder.'\');';
								}
								break;
							case 'FILE_GENERATEDVIEW':
								if ($queuedCommandRaw['TEXT']) {
									$file = sFileMgr()->getFile($objectID);
									$viewInfo = $viewMgr->get($queuedCommandRaw['TEXT']);
									$generatedViewInfo = $file->views->getGeneratedViewInfo($viewInfo['ID']);
									if ($generatedViewInfo[0]['TYPE'] == FILE_TYPE_WEBIMAGE) {
										$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_addGenerated(\''.$objectID.'\',\''.$viewInfo['IDENTIFIER'].'\', \''.$viewInfo['WIDTH'].'\', \''.$viewInfo['HEIGHT'].'\');';
									} else if ($generatedViewInfo[0]) {
										$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_addGenerated(\''.$objectID.'\',\'NULL\');';
									}
								}
								break;
							case 'UNHIGHLIGHT_TEMPLATE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_unHilite(\'template\', \''.$objectID.'-template\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'HIGHLIGHT_PAGE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_hilite(\'page\', \''.$objectID.'-'.$siteID.'\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'UNHIGHLIGHT_PAGE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_unHilite(\'page\', \''.$objectID.'-'.$siteID.'\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'HIGHLIGHT_CBLOCK':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_hilite(\'cblock\', \''.$objectID.'-cblock\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'UNHIGHLIGHT_CBLOCK':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_unHilite(\'cblock\', \''.$objectID.'-cblock\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'UNHIGHLIGHT_ENTRYMASK':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_unHilite(\'entrymask\', \''.$objectID.'-entrymask\', \''.$queuedCommandRaw['TEXT'].'\');'.
																			   'Koala.yg_unHilite(\'page\', \''.$objectID.'-entrymask\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'UNHIGHLIGHT_SITE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_unHilite(\'page\', \''.$objectID.'-site\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'HIGHLIGHT_SITE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_hilite(\'page\', \''.$objectID.'-site\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'HIGHLIGHT_MAILING':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_hilite(\'mailing\', \''.$objectID.'-mailing'.'\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'UNHIGHLIGHT_MAILING':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_unHilite(\'mailing\', \''.$objectID.'-mailing'.'\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'PAGE_MOVE':
								if ($queuedCommandRaw['TEXT']) {
									if ($queuedCommandRaw['TARGETID'] == 1) {
										$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_moveTreeNode(\'page\', \''.$objectID.'-'.$siteID.'\', \''.$queuedCommandRaw['TEXT'].'\', 2);';
									} else {
										$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_moveTreeNode(\'page\', \''.$objectID.'-'.$siteID.'\', \''.$queuedCommandRaw['TEXT'].'\', 1);';
									}
								}
								break;
							case 'PAGE_MOVEUP':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_moveUp(\'page\', \''.$objectID.'-'.$siteID.'\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'PAGE_MOVEDOWN':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_moveDown(\'page\', \''.$objectID.'-'.$siteID.'\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								break;
							case 'CBLOCK_MOVE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_moveTreeNode(\'cblock\', \''.$objectID.'-cblock\', \''.$queuedCommandRaw['TEXT'].'-cblock\', 1);';
								}
								break;
							case 'FILE_MOVE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_moveTreeNode(\'file\', \''.$objectID.'-file\', \''.$queuedCommandRaw['TEXT'].'-file\', 1);';
								}
								break;
							case 'TAG_MOVE':
								if ($queuedCommandRaw['TEXT']) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_moveTreeNode(\'tag\', \''.$objectID.'-tag\', \''.$queuedCommandRaw['TEXT'].'-tag\', 1);';
								}
								break;
							case 'TAG_ADD':
								$objectInfo = $tagMgr->get($objectID);

								$icon = $icons->icon['tag_small'];
								$statusClass = '';

								if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RWRITE" )) {
									// Nur Leserecht (hellgrau)
									$statusClass .= " nowrite";
								}
								if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RDELETE" )) {
									// Nur Leserecht (hellgrau)
									$statusClass .= " nodelete";
								}
								if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RSUB" )) {
									$statusClass .= " nosub";
								}

								$objectName = $objectInfo['NAME'];
								$objectParents = $tagMgr->getParents($objectID);
								$parentNodeId =	$objectParents[0][0]["ID"];
								if ($queuedCommandRaw['NEWVALUE'] == sGuiUS()) { $andSelect = 'true'; } else { $andSelect = 'false'; }
								$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_addChild(\'tag\', \''.$parentNodeId.'-tag\', \'name\', \''.$objectName.'\', \'tag\', \''.$objectID.'-tag\', \'name\', \''.$icon.'\', \''.$statusClass.'\', '.$andSelect.');';
								break;
							case 'FILE_ADD':
							case 'FILEFOLDER_ADD':
								$file = sFileMgr()->getFile($objectID);
								if ($file) {
									$objectInfo = $file->get();

									$icon = $icons->icon['folder'];
									$statusClass = '';

									if ( ($objectInfo["VERSIONPUBLISHED"]+2 != $objectInfo["VERSION"]) && ($objectInfo["VERSIONPUBLISHED"]!=ALWAYS_LATEST_APPROVED_VERSION) && ($objectInfo["HASCHANGED"] == "1") ) {
										// Editiert (grün)
										$statusClass = "changed";
									} elseif ($objectInfo["HASCHANGED"] == "1") {
										$statusClass = "changed";
									}

									if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RWRITE" )) {
										// Nur Leserecht (hellgrau)
										$statusClass .= " nowrite";
									}
									if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RDELETE" )) {
										// Nur Leserecht (hellgrau)
										$statusClass .= " nodelete";
									}
									if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RSUB" )) {
										$statusClass .= " nosub";
									}

									$objectName = $objectInfo['NAME'];
									$objectParents = sFileMgr()->getParents($objectID);
									$parentNodeId =	$objectParents[0][0]["ID"];
									if ($queuedCommandRaw['NEWVALUE'] == sGuiUS()) { $andSelect = 'true'; } else { $andSelect = 'false'; }
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_addChild(\'file\', \''.$parentNodeId.'-file\', \'name\', \''.$objectName.'\', \'file\', \''.$objectID.'-file\', \'name\', \''.$icon.'\', \''.$statusClass.'\', '.$andSelect.');';
								}
								break;
							case 'CBLOCK_ADD':
								$cb = sCblockMgr()->getCblock($objectID);
								$objectInfo = $cb->get();

								$icon = $icons->icon['cblock_small'];
								$statusClass = '';

								if ($objectInfo['FOLDER']!=1) {
									if ( ($objectInfo["VERSIONPUBLISHED"]+2 != $objectInfo["VERSION"]) && ($objectInfo["VERSIONPUBLISHED"]!=ALWAYS_LATEST_APPROVED_VERSION) && ($objectInfo["HASCHANGED"] == "1") ) {
										// Editiert (grün)
										$statusClass .= "changed changed1 nosub";
									} elseif ($objectInfo["HASCHANGED"] == "1") {
										$statusClass .= "changed changed2 nosub";
									}
								} else {
									if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RSUB" )) {
										$statusClass .= " nosub";
									}
									$icon = $icons->icon['folder'];
									$statusClass .= " folder";
								}
								if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RWRITE" )) {
									// Nur Leserecht (hellgrau)
									$statusClass .= " nowrite";
								}
								if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RDELETE" )) {
									// Nur Leserecht (hellgrau)
									$statusClass .= " nodelete";
								}
								$objectName = $objectInfo['NAME'];
								$objectParents = sCblockMgr()->getParents($objectID);
								$parentNodeId =	$objectParents[0][0]["ID"];
								if ( ($queuedCommandRaw['NEWVALUE'] == sGuiUS()) &&
									 ($queuedCommandRaw['TEXT'] != 'list') ) {
									$andSelect = 'true';
								} else {
									$andSelect = 'false';
								}
								if ($queuedCommandRaw['NEWVALUE'] == sGuiUS()) {
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_addListItem(\''.$parentNodeId.'-cblock\', \''.addslashes(json_encode($objectInfo)).'\', \''.$queuedCommandRaw['TEXT'].'\');';
								}
								$queuedCommands[$queuedCommandRaw['ID']] .= 'Koala.yg_addChild(\'cblock\', \''.$parentNodeId.'-cblock\', \'name\', \''.$objectName.'\', \'cblock\', \''.$objectID.'-cblock\', \'name\', \''.$icon.'\', \''.$statusClass.'\', '.$andSelect.');';
								break;
							case 'PAGE_ADD':
								if ($pageObj) {
									$objectInfo = $pageObj->get();

									$icon = $icons->icon['page_small'];
									$statusClass = '';

									$inactive = false;
									if ($objectInfo["ACTIVE"] == "0") {
										$icon = $icons->icon['page_inactive_small'];
										$inactive = true;
									}

									$naviinfo = NULL;
									$navis = $templateMgr->getNavis($objectInfo["TEMPLATEID"]);
									for ($i = 0; $i < count($navis); $i++) {
									  if ($navis[$i]["ID"] == $objectInfo["NAVIGATIONID"]) {
										  $naviinfo = $navis[$i];
									  }
									}

									if (($objectInfo["HIDDEN"] == "1") || ($objectInfo["TEMPLATEID"]=="0") || (!$naviinfo['ID'])) {
										$icon = $icons->icon['page_hidden_small'];
										if ($inactive==true) {
											$icon = $icons->icon['page_inactive_hidden_small'];
										}
									}

									if (($objectInfo["VERSIONPUBLISHED"]+2 != $objectInfo["VERSION"]) && ($objectInfo["VERSIONPUBLISHED"]!=ALWAYS_LATEST_APPROVED_VERSION) && ($objectInfo["HASCHANGED"] == "1")) {
										// Editiert (grün)
										$statusClass = "changed";
									} elseif ($objectInfo["HASCHANGED"] == "1") {
										$statusClass = "changed";
									}

									if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RWRITE" )) {
										// Nur Leserecht (hellgrau)
										$statusClass .= " nowrite";
									}
									if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RDELETE" )) {
										// Nur Leserecht (hellgrau)
										$statusClass .= " nodelete";
									}
									if (!$permissionsObj->checkInternal( sUserMgr()->getCurrentUserID(), $objectID, "RSUB" )) {
										$statusClass .= " nosub";
									}

									$objectName = $objectInfo['NAME'];
									$objectParents = $pageMgr->getParents($objectID);
									$parentNodeId =	$objectParents[0][0]["ID"];
									if (!$parentNodeId) {
										$parentNodeId = 1;
									}
									$url = $pageObj->getUrl();
									if ($queuedCommandRaw['NEWVALUE'] == sGuiUS()) { $andSelect = 'true'; } else { $andSelect = 'false'; }
									$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_addChild(\'page\', \''.$parentNodeId.'-'.$siteID.'\', \'name\', \''.$objectName.'\', \'page\', \''.$objectID.'-'.$siteID.'\', \'name\', \''.$icon.'\', \''.$statusClass.'\', '.$andSelect.',  \''.$url.'\');'."\n";
								}
								break;
							case 'MAILING_ADD':
								$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_refreshMailingsWindow();'."\n";
								break;
							case 'MAILING_DELETE':
								$queuedCommands[$queuedCommandRaw['ID']] = 'Koala.yg_refreshMailingsWindow(true);'."\n";
								break;
							default:
								$queuedCommands[$queuedCommandRaw['ID']] = stripslashes($queuedCommandRaw['OLDVALUE'])."\n";
								break;
						}
					}
				}
			}

			if (count($queuedCommandsRaw)) {
				$currentQueueId = $queuedCommandsRaw[count($queuedCommandsRaw)-1]['ID'];
			}

		}
		$output  = "\n<script>\n";
		$output .= "parent.Koala.currentGuiSyncHistoryId = ".$currentQueueId.";\n";
		$output .= "parent.Koala.yg_executeGuiJSQueue( ".json_encode($queuedCommands)." );\n";
		$output .= "</script>\n";
		print $output;
	}

	/**
	 * Function to push the script output to the client
	 */
	private function scriptOutput ($script) {
		if (sApp()->frontendMode != 'true') {
			$output  = "<script>\n";
			$output .= "parent.Koala.yg_cleanStyles();\n";
			$output .= $script;
			$output .= "</script>";
			//ob_start();
			print $output;
			//ob_end_flush();
		}
	}

	/**
	 * Set the ResponderData
	 */
	function setResponderData ($userdata) {
		$this->responderData = $userdata;
	}

	/**
	 * Set the ResponderHandler
	 */
	function setResponderHandler ($handler) {
		$this->responderHandler = $handler;
	}

	/**
	 * Queue a scriptlet for later output with "->go();"
	 */
	function queueScript ($scriptlet) {
		$this->scriptQueue .= "\n".$scriptlet;
	}

	/**
	 * Push all queued scriptlets to the client
	 */
	 function go() {
		$this->scriptOutput($this->scriptQueue);
		$this->scriptQueue = '';
	 }

	/**
	 * Function to push raw data to the client
	 */
	function rawrite ( $output ) {
		print $output;
	}

	/**
	 * Pushes data to the debug console
	 */
	function log() {
		$vars = func_get_args();
		sLog()->log($vars);
	}

	/**
	 * Creates an alert on clientside
	 * @param {String} [$text] The text to display.
	 */
	function alert( $text, $title = '') {
		$this->scriptOutput("window.setTimeout(function(){Koala.yg_promptbox('".$title."', '".$text."', 'alert')},0);");
	}

	/**
	 * Creates an dialog on clientside
	 * @param {String} [$text] The text to display.
	 */
	function dialog ( $title, $url, $autocenter, $width, $height, $top, $left, $resizable ) {
		if ($autocenter===true)
			$addscript = "wysiwygWin.showCenter(true);";
		if (!$width)
			$width = 508;
		if (!$height)
			$height = 480;
		if (!$top)
			$top = 100;
		if (!$left)
			$left = 100;
		if ( $resizable===true )
			$resizable = 'true';
		else
			$resizable = 'false';

		$this->scriptOutput("var wysiwygWin=new Window({id:'dialog_'+Koala.yg_generateRandomID(),className:'yeager',title:'".$title."',width:".$width.",height:".$height.",top:".$top.",left:".$left.", resizable:".$resizable.",url:'".$url."',showEffect:Element.show,hideEffect:Element.hide});wysiwygWin.show();wysiwygWin.toFront();wysiwygWin.setDestroyOnClose();".$addscript);
	}

	/**
	 * Calls a JavaScript function from the backend
	 */
	function callJSFunction() {
		$argc = func_num_args(); $argv = func_get_args();
		$func = array_shift( $argv );

		foreach($argv as $argv_index => $argv_item) {
			$argv[$argv_index] = addslashes( str_replace("\r", '\n', str_replace("\n", '\n', $argv_item)) );
		}

		$call = $func . "('" . implode( "', '", $argv) . "');";

		//$this->log( $call );
		$this->queueScript( $call );
	}

}

?>