<?php
    namespace com\yg;
    
    class ExamplePage extends \PageExtension {
	
        public $info = array(
			"NAME" => "Example Page Extension",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "Example Page Extension implementation",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_PAGE,
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