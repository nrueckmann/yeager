<?php
    namespace com\yg;
    
    class ExampleFile extends \FileExtension {
	
        public $info = array(
			"NAME" => "Example File Extension",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "Example File Extension implementation",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_FILE,
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