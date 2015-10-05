<?php
    namespace com\yg;
    
    class ExampleMailing extends \MailingExtension {
	
        public $info = array(
			"NAME" => "Example Mailing Extension",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "Example Mailing Extension implementation",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_MAILING,
			"ASSIGNMENT" => EXTENSION_ASSIGNMENT_USER_CONTROLLED
        );
		
        public function install() {
        	if (parent::install()) {
                return parent::setInstalled();
            } else {
                return false;
            }
        }

        public function uninstall() {
            if (parent::uninstall()) {
	            return parent::setUnInstalled();
            } else {
                return false;
            }
        }

        // callback methods go here

    }
?>