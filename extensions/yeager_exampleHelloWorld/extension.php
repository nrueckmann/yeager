<?php
    namespace com\yg;
    
    class ExampleHelloWorld extends \PageExtension {
	
        public $info = array(
			"NAME" => "Hello world!",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "Say hello.",
			"PAGEDESCRIPTION" => "Say hello.",
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

		public function onRender() {
			echo("Hello world.");
			die();	
		}
    }
?>