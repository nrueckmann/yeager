<?php

	// email send processor
	\framework\import('org.phpmailer.phpmailer');

	class Emailsend extends EmailProc {

		public function process ($objectId, $params) {
			return $this->sendEmail( $params );
		}

		public function checkFinish ($objectId, $params) {

			// Check if job is finished and set status accordingly
			$mailingMgr = new MailingMgr();
			$mailing = $mailingMgr->getMailing($params['MAILING_ID']);
			$queuedJobs = $mailingMgr->scheduler->getQueuedJobsForObject($params['MAILING_ID'], true, true, 'SCH_EMAILSEND');
			if (count($queuedJobs) == 0) {
				// No more jobs scheduled
				if ($params['IS_TEST']) {
					$mailing->setStatus('UNSENT');
				} else {
					$mailing->setStatus('SENT');
				}
				return true;
			}
			return false;
		}

		public function sendEmail ($emailData) {
			$mail = new PHPMailer();
			if ((bool)sConfig()->getVar('CONFIG/MAILINGS/DISABLE')) {
				return true;
			}

			// Obtain userdata
			$user = new User($emailData['USER_ID']);
			$userInfo = $user->get();
			$userInfo['PROPERTIES'] = $user->properties->getValues($emailData['USER_ID']);
			// Obtain mailingdata
			$mailingMgr = new MailingMgr();
			$mailing = $mailingMgr->getMailing($emailData['MAILING_ID']);

			if ($emailData['IS_TEST']) {
				$mailingInfo = $mailing->get();
				$mailingVersion = $mailingInfo['VERSION'];
			} else {
				$mailingVersion = $mailing->getLatestApprovedVersion();
				$mailing = $mailingMgr->getMailing($emailData['MAILING_ID'], $mailingVersion);
				$mailingInfo = $mailing->get();
			}
			$templateMgr = new Templates();
			$userId = $userInfo['ID'];
			$userEmail = $userInfo['PROPERTIES']['EMAIL'];
			$userFirstName = $userInfo['PROPERTIES']['FIRSTNAME'];
			$userLastName = $userInfo['PROPERTIES']['LASTNAME'];
			$userName = trim($userFirstName.' '.$userLastName);
			$userCompany = $userInfo['PROPERTIES']['COMPANY'];
			$userDepartment = $userInfo['PROPERTIES']['COMPANY'];
			$templateInfo = $templateMgr->getTemplate($mailingInfo['TEMPLATEID']);
			$userInfo['PROPERTIES']['FULLNAME'] = trim($userFirstName.' '.$userLastName);
			sSmarty()->clear_assign('recipient');
			sSmarty()->assign('recipient', $userInfo);
			sSmarty()->clear_assign('user');
			sSmarty()->assign('user', $userInfo['PROPERTIES']);
			sApp()->output = '';
			$emailData['FROM'] = $mailingInfo['FROM_EMAIL'];
			$emailData['FROM_NAME'] = $mailingInfo['FROM_NAME'];
			$emailData['FROM_REPLYTO'] = $mailingInfo['FROM_REPLYTO'];
			$emailData['FROM_SENDER'] = $mailingInfo['FROM_SENDER'];
			$emailData['ENCODING'] = $mailingInfo['ENCODING'];
			if ($emailData['ENCODING'] == '') $emailData['ENCODING'] = 'base64';
			$emailData['SUBJECT'] = $this->replaceUserdataVars($mailingInfo['SUBJECT'], $emailData['USER_ID']);
			$emailData['BODY_TEXT'] = $this->replaceUserdataVars($mailingInfo['FALLBACK_TEXT'], $emailData['USER_ID']);
			// Set special smarty delimiters
			sSmarty()->left_delimiter = '[!';
			sSmarty()->right_delimiter = '!]';
			// Parse subject with smarty
			$emailData['SUBJECT'] = sSmarty()->fetch('var:'.$emailData['SUBJECT']);
			//$emailData['BODY_TEXT'] = sSmarty()->fetch('var:'.$emailData['BODY_TEXT']);

			// Reset smarty delimiters
			sSmarty()->left_delimiter = '{';
			sSmarty()->right_delimiter = '}';
			$mailingId = $emailData['MAILING_ID'];
			include(getrealpath(dirname(__FILE__).'/../../output/mailing.php'));
			if ($templateInfo['FILENAME']) {
				$emailhtml = sApp()->output;
				$emailhtml = str_replace("\"/neptun/neptun.php", "\"".$this->request->prefix."://".$this->request->http_host.$this->request->script_name, $emailhtml);
				$emailhtml = $this->replaceUserdataVars($emailhtml, $emailData['USER_ID']);
				$emailData['BODY_HTML'] = $emailhtml;
			} else {
				$emailData['BODY_HTML'] = NULL;
			}
			sApp()->output = '';
			$smtpServer = (string)sConfig()->getVar('CONFIG/MAILINGS/SMTP');
			if ($smtpServer) {
				$mail->IsSMTP();
				$mail->Host = $smtpServer;
			}
			if ($emailData['ENCODING'])			$mail->Encoding = $emailData['ENCODING'];
			if ($emailData['CHARSET'])			$mail->CharSet  = $emailData['CHARSET'];
			if ($emailData['FROM'])				$mail->From = $emailData['FROM'];
			if ($emailData['FROM_NAME'])		$mail->FromName = $emailData['FROM_NAME'];
			if ($emailData['FROM_REPLYTO'])		$mail->AddReplyTo($emailData['FROM_REPLYTO']);
			if ($emailData['FROM_SENDER'])		$mail->Sender = $emailData['FROM_SENDER'];
			if ($emailData['SUBJECT'])			$mail->Subject = $emailData['SUBJECT'];
			if ($emailData['BODY_HTML']) {		$mail->Body = $emailData['BODY_HTML'];$mail->IsHTML(true); }
			if ($emailData['BODY_TEXT'] && !$emailData['BODY_HTML']) {
				$mail->Body = $emailData['BODY_TEXT']; $mail->IsHTML(false);
			}
			if ($emailData['BODY_TEXT'] && $emailData['BODY_HTML']) {
				$mail->AltBody = $emailData['BODY_TEXT'];
			}

			$forcedRecipient = (string)sConfig()->getVar('CONFIG/MAILINGS/FORCE_RECIPIENT');
			foreach($emailData['TO'] as $emailToItem) {
				if ($forcedRecipient) {
					$mail->AddAddress($forcedRecipient, $emailToItem['EMAIL_NAME']);
				} else {
					$mail->AddAddress($emailToItem['EMAIL'], $emailToItem['EMAIL_NAME']);
				}
			}
			foreach($emailData['CC'] as $emailCcItem) {
				$mail->AddCC($emailCcItem['EMAIL'], $emailCcItem['EMAIL_NAME']);
			}
			foreach($emailData['BCC'] as $emailBccItem) {
				$mail->AddBCC($emailBccItem['EMAIL'], $emailBccItem['EMAIL_NAME']);
			}
			foreach($emailData['ATTACHMENTS'] as $emailAttachmentItem) {
				$mail->AddAttachment($emailAttachmentItem['PATH'], $emailAttachmentItem['NAME']);
			}
			$result = $mail->Send();
			$mail->ClearAddresses();
			$mailingData = array('USERINFO' => $userInfo, 'DATA' => $emailData);
			$mailingMgr->callExtensionHook('onSend', $mailingId, $mailingVersion, $mailingData);
			return $result;
		}

		private function replaceUserdataVars ($text, $userId) {
			$user = new User($userId);
			$userInfo = $user->get();
			$userInfo['PROPERTIES'] = $user->properties->getValues($userId);
			$userInfo['PROPERTIES']['FULLNAME'] = trim($userInfo['PROPERTIES']['FIRSTNAME'].' '.$userInfo['PROPERTIES']['LASTNAME']);

			foreach ($userInfo['PROPERTIES'] as $userProperty => $userPropertyValue) {
				$text = str_replace('__'.$userProperty.'__', $userPropertyValue, $text);
			}

			return $text;
		}

	}

?>