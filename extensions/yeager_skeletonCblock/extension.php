<?php
    namespace com\yg;
    
    class ExampleCblock extends \CblockExtension {
	
        public $info = array(
			"NAME" => "Example Content Block Extension",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "Example Content Block Extension implementation",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_CBLOCK,
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