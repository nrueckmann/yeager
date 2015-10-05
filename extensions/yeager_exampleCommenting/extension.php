<?php
	namespace com\yg;
	
	class ExampleCommenting extends \PageExtension {

		public $info = array(
			"NAME" => "Comments!",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "Add, edit and delete Comments",
			"PAGEDESCRIPTION" => "Comments!",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_PAGE,
			"ASSIGNMENT" => EXTENSION_ASSIGNMENT_EXT_CONTROLLED
		);

		public function install () {
			if ( parent::install() ) {
				return parent::setInstalled();
			} else {
				return false;
			}
		}

		public function onRender ($args = NULL) {

			$page = $this->getPage();
			$pageinfo = $page->get();
			$action = sApp()->request->parameters['ACTION'];
			
			// check if commenting user is authenticated
			$anonymousUserID = sUserMgr()->getAnonymousID();
			if ((int)sUserMgr()->getCurrentUserID() == $anonymousUserID) {
				$isLoggedIn = false;
			} else {
				$isLoggedIn = true;
			}

			// get comment infos
			$commentError = NULL;
			$commentObject = $page->comments;
			$commentSettings = $commentObject->getSettings();
			$commentStatus = $commentObject->getStatus($pageinfo['ID']);
			$commentPermission = $page->permissions->check(sUserMgr()->getCurrentUserID(), 'RCOMMENT', $pageinfo['ID']);

			// add comment
			if($action == 'ADD') {
				$commentUser = sApp()->request->parameters['USER'];
				$commentEmail = sApp()->request->parameters['EMAIL'];
				$commentText = sApp()->request->parameters['TEXT'];

				$result = $commentObject->add($commentText, $pageinfo['ID'], $commentUser, $commentEmail);
				if ($result !== ERROR_NONE) {
					switch($result) {
						case ERROR_COMMENTS_MINIMUM_POST_INTERVAL_EXCEEDED:
							$commentError = 'Minimum post interval was exceeded.';
							break;
						case ERROR_COMMENTS_BLACKLISTED_WORD:
							$commentError = 'You have used a blacklisted word.';
							break;
						case ERROR_COMMENTS_AUTHENTICATION_NEEDED:
							$commentError = 'Authentication is needed.';
							break;
						case ERROR_COMMENTS_COMMENTING_IS_CLOSED:
							$commentError = 'Commenting is closed.';
							break;
						case ERROR_COMMENTS_NO_COMMENT_RIGHTS:
							$commentError = 'No permissions to comment.';
							break;
						default:
							$commentError = 'An unknown error has occured.';
							break;
					}
				}
			}

			// get comments - has to be after add functionaly in order for new comments to show up
			$commentCount = $commentObject->getCommentsCount();
			$comments = $commentObject->getComments($pageinfo['ID'], NULL);

			sSmarty()->assign("isLoggedIn", $isLoggedIn);
			sSmarty()->assign("commentError", $commentError);
			sSmarty()->assign("commentCount", $commentCount);
			sSmarty()->assign("commentPermission", $commentPermission);
			sSmarty()->assign("commentSettings", $commentSettings);
			sSmarty()->assign("commentStatus", $commentStatus);
			sSmarty()->assign("comments", $comments);

		}

	}

?>