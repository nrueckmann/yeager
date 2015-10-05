<?php
	namespace com\yg;

	class PwdrecoveryLogin extends \PageExtension {
		public $info = array(
			"NAME" => "Password Recovery & Login Extension",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "Password Recovery & Login Extension",
			"PAGEDESCRIPTION" => "Password Recovery & Login Extension",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_PAGE,
			"ASSIGNMENT" => EXTENSION_ASSIGNMENT_EXT_CONTROLLED
		);

		public function install () {
			if ( parent::install() ) {

				// add properties for password recovery
				$this->extensionPropertySettings->add("Password recovery settings", "HEADLINE_RECOVERY", "HEADLINE");
				$this->extensionPropertySettings->add("Sender E-Mail address", "FROM_EMAIL", "TEXT");
				$this->extensionPropertySettings->add("Sender name", "FROM_NAME", "TEXT");
				$this->extensionPropertySettings->add("E-Mail Subject", "SUBJECT", "TEXT");
				$this->extensionPropertySettings->add("E-Mail content displayed before recovery link", "BODY_PT1", "TEXTAREA");
				$this->extensionPropertySettings->add("E-Mail content displayed after recovery link", "BODY_PT2", "TEXTAREA");

				// add properties for login
				$this->extensionPropertySettings->add("Login settings", "HEADLINE_LOGIN", "HEADLINE");
				$this->extensionPropertySettings->add("Login Page", "STANDARD_LOGIN", "PAGE");
				$this->extensionPropertySettings->add("Fallback target Page after successful login", "STANDARD_REFERRER", "PAGE");

				// add properties for logout
				$this->extensionPropertySettings->add("Logout settings", "HEADLINE_LOGOUT", "HEADLINE");
				$this->extensionPropertySettings->add("Page which will be loaded after logout", "STANDARD_LOGOUT_REFERRER", "PAGE");
				$this->extensionPropertySettings->add("Logout action", "LOGOUT_ACTION", "TEXT");

				// set standardvalues for password recovery
				$this->extensionProperties->setValue('FROM_EMAIL', 'no-reply@example.com');
				$this->extensionProperties->setValue('FROM_NAME', 'Password service');
				$this->extensionProperties->setValue('SUBJECT', 'Your password');
				$this->extensionProperties->setValue('BODY_PT1', 'Dear user,\n\nplease click following link in order to reset your password:\n');
				$this->extensionProperties->setValue('BODY_PT2', '\n\nBest,\nXXX');
				$this->extensionProperties->setValue('LOGOUT_ACTION', 'logout');

				return parent::setInstalled();
			} else {
				return false;
			}
		}

		public function uninstall () {
			if ( parent::uninstall() ) {
				return parent::setUnInstalled();
			} else {
				return false;
			}
		}

		public function onRender ($args = NULL) {
			$action = sApp()->request->parameters["action"];
			sSmarty()->assign("action", $action);

			/* LOGIN */

			if ($action == "login") {

				$email = sApp()->request->parameters['email'];
				$password = sApp()->request->parameters['password'];
				$yg_login_referrer = sApp()->request->parameters["yg_login_referrer"];

				sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
				$extproperties = $this->extensionProperties->get();

				if ($yg_login_referrer == '') $yg_login_referrer = $extproperties['STANDARD_REFERRER']['URL'];
				sSmarty()->assign("yg_login_referrer", $yg_login_referrer);

				$userid = sUserMgr()->validate($email, $password);

				sUserMgr()->unimpersonate();

				if ($userid === false) {
					sSmarty()->assign("yg_login_error_code", "1");
					sSmarty()->assign('action', $action);
					sSmarty()->assign("email", $email);
				} else {
					sApp()->session->setPSessionVar("username", $email);
					sApp()->session->setPSessionVar("password", $password);
                    sApp()->session->refrehSessionCookie();
					sApp()->session->setPSessionVar("isvalidated", true);
					sApp()->session->setPSessionVar("keepLoggedIn", true);
					sApp()->session->cookie_time = time()+60*60*24*365;
					http_redirect($yg_login_referrer);
				}
			}

			/* LOGOUT */
			if ($action == $this->extensionProperties->getValue('LOGOUT_ACTION')) {
				$extproperties = $this->extensionProperties->get();
				$logout_referrer = $extproperties['STANDARD_LOGOUT_REFERRER']['URL'];
				sApp()->session->setPSessionVar('username', '');
				sApp()->session->setPSessionVar('password', '');
				sApp()->session->setPSessionVar('isvalidated', false);
				sApp()->session->setPSessionVar('keepLoggedIn', false);
				http_redirect($logout_referrer);
			}

			/* PASSWORD RECOVERY */

			if ($action == "pwd_step1") {

				sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
				$email = sApp()->request->parameters["user_email"];
				$user_info = sUserMgr()->getByLogin($email);

				if ($user_info) {
					$user = sUserMgr()->getUser($user_info['ID']);
					sUserMgr()->unimpersonate();
					$expireTS = time() + 60*60*24;
					$token = $user->generateToken($expireTS);

					$page = $this->getPage();
					$pageUrl = $page->getUrl();

					// Generate path for recovery URL
					$passwordResetUrl = sApp()->request->prefix.'://'.sApp()->request->http_host;
					$passwordResetUrl .= $pageUrl.'?action=pwd_step2&user_token='.urlencode($token);

					echo($passwordResetUrl);

					\framework\import("org.phpmailer.phpmailer");
					$mail = new \PHPMailer();
					$mail->Encoding = '8bit';
					$mail->CharSet  = 'utf-8';
					$mail->From	 = $this->extensionProperties->getValue("FROM_EMAIL");
					$mail->FromName = $this->extensionProperties->getValue("FROM_NAME");
					$mail->Subject  = $this->extensionProperties->getValue("SUBJECT");
					$mail->Body	 = $this->extensionProperties->getValue("BODY_PT1");
					$mail->Body	.= "\n".$passwordResetUrl."\n";
					$mail->Body	.= $this->extensionProperties->getValue("BODY_PT2");
					$mail->AddAddress($email);
					$smtpServer = (string)sapp()->config->getVar('CONFIG/MAILINGS/SMTP');
					if ($smtpServer) {
						$mail->IsSMTP();
						$mail->Host = $smtpServer;
					}
					$mail->Send();
					$mail->ClearAddresses();
					sSmarty()->assign('recovery_mail_sent', true);
				} else if(strlen(trim($email)) > 0) {
					sSmarty()->assign('error_step1', true);
				}
			}

			if ($action == "pwd_step2") {
				$error_step2 = false;
				$form_send = sApp()->request->parameters['form_send'];
				sSmarty()->assign('form_send', $form_send);
				$token = sApp()->request->parameters['user_token'];
				sSmarty()->assign('user_token', $token);

				$valid_token = \sUserMgr()->getUserIdByToken($token);
				if (!$valid_token) {
					$error_step2 = true;
					$error_token = true;
					sSmarty()->assign('error_step2', $error_step2);
					sSmarty()->assign('error_token', $error_token);
				}

				if ($form_send == true) {
					$user_password = sapp()->request->parameters['user_password'];
					$user_password_repeat = sapp()->request->parameters['user_password_repeat'];
					$user_id = sUserMgr()->getUserIdByToken($token);
					sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
					$user = sUserMgr()->getUser($user_id);
					sUserMgr()->unimpersonate();
					$user_info = $user->get();

					if ($user_info) {
						// Check if password is repeated correctly
						if($user_password != $user_password_repeat) {
							$error_repeat = true;
							sSmarty()->assign('error_repeat', $error_repeat);
							$error_step2 = true;
						}

						// Check if password is secure enough
						$password_ok = sUserMgr()->verifyPasswordStrength($user_password);
						if (!$password_ok) {
							$error_chars = true;
							sSmarty()->assign('error_chars', $error_chars);
							$error_step2 = true;
						}

						if (!$error_step2) {
							sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
							$user = sUserMgr()->getUser($user_info['ID']);
							$user->setPassword($user_password);
							$user->removeToken();
							sUserMgr()->unimpersonate();
						} else {
							sSmarty()->assign('error_step2', $error_step2);
						}
					} else {
						$error_step2 = true;
						sSmarty()->assign('error_step2', $error_step2);
					}
				}
			}
			return true;
		}

		public function onAccessDenied ($args = NULL) {

			sUserMgr()->impersonate(sUserMgr()->getAdministratorID());
			$extproperties = $this->extensionProperties->get();
			$loginUrl = $extproperties['STANDARD_LOGIN']['URL'];
			sUserMgr()->unimpersonate();

			$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
			$protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
			$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
			$referrer = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
			$loginUrl .= "?yg_login_referrer=".urlencode($referrer);

			http_redirect($loginUrl);

		}

	}

?>