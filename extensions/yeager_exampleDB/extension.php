<?php
    namespace com\yg;
    
    class ExampleDB extends \pageExtension {
	
        public $info = array(
			"NAME" => "Example database operations",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "SQL db operations example",
			"PAGEDESCRIPTION" => "SQL db operations example",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_PAGE,
			"ASSIGNMENT" => EXTENSION_ASSIGNMENT_USER_CONTROLLED
        );
		
        public function install() {
        	if (parent::install()) {
        		// you should use this->_code to prefix your table to make sure it's unique 
				$tablename = "yg_ext_".$this->_code."_exampletable";
				$sql = "CREATE TABLE $tablename (
				   id INT NOT NULL AUTO_INCREMENT,
				   title VARCHAR(100) NOT NULL,
				   author VARCHAR(40) NOT NULL,
				   creation DATE,
				   PRIMARY KEY (id)
				);";
				$dbr = sYDB()->Execute($sql);
				if ($dbr === false) {
					throw new \Exception(sYDB()->ErrorMsg());
					return false;
				}
                return parent::setInstalled();
            } else {
                return false;
            }
        }

        public function uninstall() {
            if (parent::uninstall()) {
				$tablename = "yg_ext_".$this->_code."_exampletable";
				$sql = "DROP TABLE $tablename;";
				$dbr = sYDB()->Execute($sql);
	            if ($dbr === false) {
					throw new \Exception(sYDB()->ErrorMsg());
					return false;
				}
	            return parent::setUnInstalled();
            } else {
                return false;
            }
        }

		public function onRender() {
			$action = sApp()->request->parameters['action'];
			if ($action == "insert") {
				$title = mysql_real_escape_string(sApp()->request->parameters['title']);
				$author = mysql_real_escape_string(sApp()->request->parameters['author']);
				$date = time();

				$tablename = "yg_ext_".$this->_code."_exampletable";
				$sql = "INSERT INTO $tablename 
					(title, author, creation) VALUES 
					('$title', '$author', $date);";
				$dbr = sYDB()->Execute($sql);
				if ($dbr === false) {
					throw new \Exception(sYDB()->ErrorMsg());
					return false;
				} else {
					echo("SUCCESS");
					die();
				}
			}
		}
    }
?>