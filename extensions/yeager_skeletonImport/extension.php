<?php
    namespace com\yg;
    
    class ExampleImport extends \ImportExtension {
	
        public $info = array(
			"NAME" => "Example Import Extension",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "Example Import Extension implementation",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_IMPORT
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