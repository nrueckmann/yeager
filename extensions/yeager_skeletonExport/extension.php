<?php
    namespace com\yg;
    
    class ExampleExport extends \ExportExtension {
	
        public $info = array(
			"NAME" => "Example Export Extension",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "Example Export Extension implementation",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_EXPORT
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