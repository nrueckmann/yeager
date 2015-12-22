<?php

	namespace framework;

	/**
	 * neptun config file parser class
	 *
	 * @author Thomas Einsporn <te@nexttuesday.de>
	 * @param string $configfile path to config file
	 *
	 */
	class Config {
		/**
		 * @access public
		 * @var string file name
		 */
		private $filename;

		/**
		 * @access private
		 * @var xmldata
		 */
		private $_xml;

		/**
		 * @access private
		 * @var xmldata
		 */
		private $_xml_hash = array();

		public function __construct ($configfile, $skipFile = false) {
			$this->filename = rtrim(realpath($configfile),"/");
			$this->_id = "cfg-".$this->filename;
			if (!file_exists($this->filename) && $skipFile == false) {
				trigger_error("could not load configfile: $this->filename");
			} else {
				if ($skipFile) {
					$this->filename = "string";
					$this->_xml_hash[$filename] = $configfile;
				}
			}
		}

		private function getValues ($key, $branch = false) {
			$result = array();
			if (check_phpversion("5.0.0")) {
				$this->xmlobject = new \SimpleXMLElement($this->_xml);
				if ($branch != true) {
					$values = @$this->xmlobject->xpath("/".$key);
					foreach ((array)$values as $value) {
						return $value;
					}
				} else {
					$i = 0;
					$branches = $this->xmlobject->xpath("/".$key);
					foreach ($branches as $value) {
						$subbranches = $value->children();
						foreach ($subbranches as $subvalue) {
							$attributes = $subvalue->attributes();
							foreach ($attributes as $attributeName => $attributeValue) {
								$attribName = trim((string)$attributeName);
								$attribVal = trim((string)$attributeValue);
								$result[$i][$attribName] = $attribVal;
							}
							$i++;
						}
					}
					return $result;
				}
			}
		}

		function loadXML ($filename) {
			if (isset($this->_xml_hash[$filename]) && $this->_xml_hash[$filename]) {
				$this->_xml = $this->_xml_hash[$filename];
				return true;
			}
            $contents = file($filename);
			if (is_array($contents) && (!$this->_xml = @implode($contents))) {
				return false;
			} else {
				$this->_xml_hash[$filename] = $this->_xml;
				return true;
			}
		}

		function getVars ($branch) {
			$result = array();
			if (!$this->loadXML($this->filename)) {
				trigger_error("could not load configfile: $this->filename");
			} else {
				$result = $this->getValues($branch, true);
				$fileName = basename($this->filename);
				$pathName = dirname($this->filename);
				$fileNameWithIP = $pathName.'/'.substr($fileName, 0, strpos($fileName, '.')).'-'.$_SERVER['SERVER_ADDR'].substr($fileName, strpos($fileName, '.'));
				$fileNameWithDomain = $pathName.'/'.substr($fileName, 0, strpos($fileName, '.')).'-'.$_SERVER['HTTP_HOST'].substr($fileName, strpos($fileName, '.'));
				if (file_exists($fileNameWithIP)) {
					$fileName = $fileNameWithIP;
				} else {
					$fileName = $fileNameWithDomain;
				}
				if (!$this->loadXML($fileName)) {
					trigger_error("Could not load config file: $fileNameWithIP or $fileNameWithDomain");
				} else {
					$hostvalue = $this->getValues($branch, true);
					if (count($hostvalue) > 0) {
						$result = $hostvalue;
					}
				}
			}
			return $result;
		}

		function getVar ($key) {
			if (!$this->loadXML($this->filename)) {
				trigger_error("could not load configfile: $this->filename");
			} else {
				$value = $this->getValues($key, false);
				$fileName = basename($this->filename);
				$pathName = dirname($this->filename);
				$fileNameWithIP = $pathName.'/'.substr($fileName, 0, strpos($fileName, '.')).'-'.$_SERVER['SERVER_ADDR'].substr($fileName, strpos($fileName, '.'));
				$fileNameWithDomain = $pathName.'/'.substr($fileName, 0, strpos($fileName, '.')).'-'.$_SERVER['HTTP_HOST'].substr($fileName, strpos($fileName, '.'));
				if (file_exists($fileNameWithIP)) {
					$fileName = $fileNameWithIP;
				} else {
					$fileName = $fileNameWithDomain;
				}
				if (!$this->loadXML($fileName)) {
					 trigger_error("Could not load config file: $fileNameWithIP or $fileNameWithDomain");
				} else {
					$hostvalue = $this->getValues($key, false);
					if (strlen($hostvalue) > 0) {
						$value = $hostvalue;
					}
				}
			}
			return (string)$value;
		}
	}

?>