<?php
    namespace com\yg;
    
    class ExampleDynamicContent extends \pageExtension {
	
        public $info = array(
			"NAME" => "Dynamic content example",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "Assigns a list of Content Blocks which got the same Tags assigned as the Page",
			"PAGEDESCRIPTION" => "Dynamic assignment of Content Blocks.",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_PAGE,
			"ASSIGNMENT" => EXTENSION_ASSIGNMENT_EXT_CONTROLLED
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
            // get page
            $page = $this->getPage();
            
            // get tags assigned to this page
            $tags = $page->tags->getAssigned();

            $dynamicContent = array();

            // loop through tags
            for ($i = 0; $i < count($tags); $i++) {
                
                // get all content blocks which got a tag assigned
                $cbs = sCblockMgr()->tags->getByTag($tags[$i]['ID']);
                for ($j = 0; $j < count($cbs); $j++) {
                    // instance content block object
                    $cb = sCblockMgr()->getCblock($cbs[$j]['OBJECTID']);
                    if ($cb) {
                        // get additional information like name and push it to the $dynamicContent
                        $info = $cb->get(); 
                        $cbs[$j] = array_merge($cbs[$j], $info);
                        /* optionally get the full content of each content block
                           gets slow if performed for a large number of content blocks 

                           $cbs[$j]["CONTENT"] = $cb->getContent();*/
                        array_push($dynamicContent, $cbs[$j]);
                    }                 
                }
            }
            // assign array
            sSmarty()->assign("dynamicContent", $dynamicContent);
      	}
    }
?>