<?php

	$jsQueue = new JSQueue( NULL );

	switch ($action) {

			case 'saveCommentsSettings':
				$winID = $this->params['winID'];
				$allow_html = $this->params['allow_html'];
				$autoclose_after_days = $this->params['autoclose_after_days'];
				$force_approval = $this->params['force_approval'];
				$force_authentication = $this->params['force_authentication'];
				$minimum_interval = $this->params['minimum_interval'];
				$se_rank_denial = $this->params['se_rank_denial'];
				$blacklist = $this->params['blacklist'];
				$spamlist = $this->params['spamlist'];

				$commentsSettings = array(
					'ALLOW_HTML' => $allow_html,
					'AUTOCLOSE_AFTER_DAYS' => $autoclose_after_days,
					'FORCE_APPROVAL' => $force_approval,
					'FORCE_AUTHENTICATION' => $force_authentication,
					'MINIMUM_INTERVAL' => $minimum_interval,
					'SE_RANK_DENIAL' => $se_rank_denial,
					'BLACKLIST' => $blacklist,
					'SPAMLIST' => $spamlist
				);

				$commentsObject = new Comments();
				$result = $commentsObject->setSettings($commentsSettings);
				if ($result !== ERROR_NONE) {
					$koala->alert( $itext['TXT_ERROR_COMMENTS_UNKNOWN'] );
				} else {
					$koala->queueScript( "Koala.yg_fadeFields(\$('".$winID."'), '.changed');" );
				}
				break;

			case 'addComment':
				$objectType = $this->params['yg_type'];
				$objectYGID = $this->params['yg_id'];
				$winID = $this->params['winID'];
				$parentCommentID = $this->params['parentCommentID'];
				$commentText = $this->params['commentText'];

				$objectYGID = explode('-', $objectYGID);
				$siteID = $objectYGID[1];
				$objectID = $objectYGID[0];

				switch($objectType) {
					case 'page':
						$pageMgr = new PageMgr($siteID);
						$page = $pageMgr->getPage($objectID);
						$objectInfo = $page->get();
						$commentsObject = $page->comments;
						$historyObject = $page->history;
						$historyType = HISTORYTYPE_PAGE;
						break;
					case 'file':
						$file = sFileMgr()->getFile($objectID);
						$objectInfo = $file->get();
						$commentsObject = $file->comments;
						$historyObject = $file->history;
						$historyType = HISTORYTYPE_FILE;
						break;
					case 'cblock':
						$cb = sCblockMgr()->getCblock($objectID);
						$objectInfo = $cb->get();
						$commentsObject = $cb->comments;
						$historyObject = $cb->history;
						$historyType = HISTORYTYPE_CO;
						break;
				}

				$result = $commentsObject->add($commentText, $parentCommentID);
				if ($result !== ERROR_NONE) {
					switch ($result) {
						case ERROR_COMMENTS_MINIMUM_POST_INTERVAL_EXCEEDED:
							$koala->alert( $itext['TXT_ERROR_COMMENTS_MINIMUM_POST_INTERVAL_EXCEEDED'] );
							break;
						case ERROR_COMMENTS_BLACKLISTED_WORD:
							$koala->alert( $itext['TXT_ERROR_COMMENTS_BLACKLISTED_WORD'] );
							break;
						default:
							$koala->alert( $itext['TXT_ERROR_COMMENTS_UNKNOWN'] );
							break;
					}
				} else {
					$historyObject->add ($objectID, $historyType, NULL, NULL, 'TXT_COMMON_H_COMMENT_ADD');
					$koala->queueScript( 'Koala.windows[\''.$winID.'\'].tabs.select($K.windows[\''.$winID.'\'].tabs.selected, {});' );
				}

				break;

			case 'saveComment':
				$objectType = $this->params['yg_type'];
				$objectYGID = $this->params['yg_id'];
				$winID = $this->params['winID'];
				$commentID = $this->params['commentID'];
				$commentText = $this->params['commentText'];

				$objectYGID = explode('-', $objectYGID);
				$siteID = $objectYGID[1];
				$objectID = $objectYGID[0];

				switch($objectType) {
					case 'page':
						$pageMgr = new PageMgr($siteID);
						$page = $pageMgr->getPage($objectID);
						$objectInfo = $page->get();
						$commentsObject = $page->comments;
						$historyObject = $page->history;
						$historyType = HISTORYTYPE_PAGE;
						break;
					case 'file':
						$file = sFileMgr()->getFile($objectID);
						$objectInfo = $file->get();
						$commentsObject = $file->comments;
						$historyObject = $file->history;
						$historyType = HISTORYTYPE_FILE;
						break;
					case 'cblock':
						$cb = sCblockMgr()->getCblock($objectID);
						$objectInfo = $cb->get();
						$commentsObject = $cb->comments;
						$historyObject = $cb->history;
						$historyType = HISTORYTYPE_CO;
						break;
				}

				$result = $commentsObject->setComment($commentID, $commentText);
				if ($result !== ERROR_NONE) {
					switch($result) {
						case ERROR_COMMENTS_BLACKLISTED_WORD:
							$koala->alert( $itext['TXT_ERROR_COMMENTS_BLACKLISTED_WORD'] );
							break;
						default:
							$koala->alert( $itext['TXT_ERROR_COMMENTS_UNKNOWN'] );
							break;
					}
				} else {
					$historyObject->add ($objectID, $historyType, NULL, NULL, 'TXT_COMMON_H_COMMENT_CHANGE');
				}

				break;

			case 'setCommentState':
				$objectTypes = $this->params['yg_types'];
				$objectYGIDs = $this->params['yg_ids'];
				$winID = $this->params['winID'];
				$newStatus = $this->params['newStatus'];
				$commentIDs = $this->params['commentIDs'];

				$hadError = false;
				$errorCode = NULL;
				foreach($commentIDs as $commentIdx => $commentID) {
					$objectYGID = explode('-', $objectYGIDs[$commentIdx]);
					$siteID = $objectYGID[1];
					$objectID = $objectYGID[0];
					switch($objectTypes[$commentIdx]) {
						case 'page':
							$pageMgr = new PageMgr($siteID);
							$page = $pageMgr->getPage($objectID);
							$objectInfo = $page->get();
							$commentsObject = $page->comments;
							$historyObject = $page->history;
							$historyType = HISTORYTYPE_PAGE;
							break;
						case 'file':
							$file = sFileMgr()->getFile($objectID);
							$objectInfo = $file->get();
							$commentsObject = $file->comments;
							$historyObject = $file->history;
							$historyType = HISTORYTYPE_FILE;
							break;
						case 'cblock':
							$cb = sCblockMgr()->getCblock($objectID);
							$objectInfo = $cb->get();
							$commentsObject = $cb->comments;
							$historyObject = $cb->history;
							$historyType = HISTORYTYPE_CO;
							break;
					}
					switch($newStatus) {
						case 'ok':
							$result = $commentsObject->setNoSpam( $commentID );
							if ($result !== ERROR_NONE) {
								$hadError = true;
								$errorCode = $result;
							}
							$result = $commentsObject->setApproved( $commentID );
							if ($result !== ERROR_NONE) {
								$hadError = true;
								$errorCode = $result;
							}
							if (!$hadError) {
								$historyObject->add ($objectID, $historyType, NULL, NULL, 'TXT_COMMON_H_COMMENT_APPROVED');
							}
							break;
						case 'unapproved':
							$result = $commentsObject->setNoSpam( $commentID );
							if ($result !== ERROR_NONE) {
								$hadError = true;
								$errorCode = $result;
							}
							$result = $commentsObject->setUnapproved( $commentID );
							if ($result !== ERROR_NONE) {
								$hadError = true;
								$errorCode = $result;
							}
							if (!$hadError) {
								$historyObject->add ($objectID, $historyType, NULL, NULL, 'TXT_COMMON_H_COMMENT_APPROVED');
							}
							break;
						case 'spam':
							$result = $commentsObject->setUnapproved( $commentID );
							if ($result !== ERROR_NONE) {
								$hadError = true;
								$errorCode = $result;
							}
							$result = $commentsObject->setSpam( $commentID );
							if ($result !== ERROR_NONE) {
								$hadError = true;
								$errorCode = $result;
							}
							if (!$hadError) {
								$historyObject->add ($objectID, $historyType, NULL, NULL, 'TXT_COMMON_H_COMMENT_MARKED_AS_SPAM');
							}
							break;
					}
				}
				if ($hadError) {
					switch ($errorCode) {
						default:
							$koala->alert( $itext['TXT_ERROR_COMMENTS_UNKNOWN'] );
							break;
					}
				}

				break;

			case 'setCommentingState':
				$objectType = $this->params['yg_type'];
				$objectYGID = $this->params['yg_id'];
				$winID = $this->params['winID'];
				$newStatus = $this->params['newStatus'];

				$objectYGID = explode('-', $objectYGID);
				$siteID = $objectYGID[1];
				$objectID = $objectYGID[0];

				switch($objectType) {
					case 'page':
						$pageMgr = new PageMgr($siteID);
						$page = $pageMgr->getPage($objectID);
						$objectInfo = $page->get();
						$commentsObject = $page->comments;
						break;
					case 'file':
						$file = sFileMgr()->getFile($objectID);
						$objectInfo = $file->get();
						$commentsObject = $file->comments;
						break;
					case 'cblock':
						$cb = sCblockMgr()->getCblock($objectID);
						$objectInfo = $cb->get();
						$commentsObject = $cb->comments;
						break;
				}

				$hadError = false;
				$errorCode = NULL;
				switch($newStatus) {
					case 'opened':
						$result = $commentsObject->setStatus( true );
						if ($result !== ERROR_NONE) {
							$hadError = true;
							$errorCode = $result;
						}
						break;
					case 'closed':
						$result = $commentsObject->setStatus( false );
						if ($result !== ERROR_NONE) {
							$hadError = true;
							$errorCode = $result;
						}
						break;
				}

				if ($hadError) {
					switch($errorCode) {
						default:
							$koala->alert( $itext['TXT_ERROR_COMMENTS_UNKNOWN'] );
							break;
					}
				}

				break;

			case 'removeComment':
				$winID = $this->params['winID'];
				$commentIDs = $this->params['commentIDs'];
				$objectYGID = $this->params['yg_id'];
				$objectType = $this->params['yg_type'];
				$confirmed = $this->params['confirmed'];
				$positive = $this->params['positive'];

				$objectYGID = explode('-', $objectYGID);
				$siteID = $objectYGID[1];
				$objectID = $objectYGID[0];

				switch($objectType) {
					case 'page':
						$pageMgr = new PageMgr($siteID);
						$page = $pageMgr->getPage($objectID);
						$objectInfo = $page->get();
						$commentsObject = $page->comments;
						$historyObject = $page->history;
						$historyType = HISTORYTYPE_PAGE;
						break;
					case 'file':
						$file = sFileMgr()->getFile($objectID);
						$objectInfo = $file->get();
						$commentsObject = $file->comments;
						$historyObject = $file->history;
						$historyType = HISTORYTYPE_FILE;
						break;
					case 'cblock':
						$cb = sCblockMgr()->getCblock($objectID);
						$objectInfo = $cb->get();
						$commentsObject = $cb->comments;
						$historyObject = $cb->history;
						$historyType = HISTORYTYPE_CO;
						break;
				}

				$hadError = false;
				$errorCode = NULL;
				$needFrontEndUpdate = false;
				foreach($commentIDs as $commentID) {
					$result = $commentsObject->remove($objectID, $commentID);
					if ($result !== ERROR_NONE) {
						$hadError = true;
						$errorCode = $result;
					} else {
						$needFrontEndUpdate = true;
					}
				}
				if ($hadError) {
					switch ($errorCode) {
						default:
							$koala->alert( $itext['TXT_ERROR_COMMENTS_UNKNOWN'] );
							break;
					}
				} else if ($needFrontEndUpdate) {
					$historyObject->add ($objectID, $historyType, NULL, NULL, 'TXT_COMMON_H_COMMENT_REMOVE');
					$koala->queueScript( 'Koala.windows[\''.$winID.'\'].tabs.select($K.windows[\''.$winID.'\'].tabs.selected, {});' );
				}
				break;

	}

?>