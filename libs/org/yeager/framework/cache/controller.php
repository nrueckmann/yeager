<?php
     
    namespace framework;
     
    class Cache extends Error {
        var $tmpdir;
        var $depth;
        var $cache_storage_checkfile;
        var $cache_id;
        var $cache_object = array();
         
        var $cached_object_exists;
        var $cached_object_expires;
        var $cached_object = array();
        var $cached_object_timestamp;

        var $reverse_proxy_purge;

        function __construct ($bucket = "page", $expiry, $tempdir, $reverse_proxy_purge = "") {
            if (strlen($tempdir) < 1) {
            	$tempdir = sys_get_temp_dir();
                $tempdir = $tempdir."cache/";
            }
            $this->bucket = $bucket;
            $this->tmpdir = $tempdir.$bucket."/";//."cache/";
            $this->cache_storage_checkfile = $this->tmpdir."ok";
            $this->depth = 1;
            $this->expiry = $expiry;
            $this->init($this->tmpdir);

            if (strlen($reverse_proxy_purge) > 0) {
                $this->reverse_proxy_purge = $reverse_proxy_purge;
            }
        }
        
        /**
        * set TTL for cache object
        * 
        * @param int $ttl timestamp
        */
        public function setTTL($ttl) {
            $this->expiry = $ttl;
        }
        
        /***
        * set unique cache key
        * 
        * @param string $id cache-key
        */
        public function setCacheId($id) {
            $this->cache_id = md5($_SERVER['SCRIPT_FILENAME']).$id;
            $this->cache_object["expires"] = $this->expiry;
            $this->parseFile();
        }
         
        function init ($tempdir) {
            if (!@file_exists($this->cache_storage_checkfile)) {
                $failed = 0;
                $failed|= !@mkdir($tempdir, 0777, true);
                for ($a = 0; $a < $this->depth; $a++) {
                    $thedir = $tempdir . "/$a/";
                    $failed|= !@mkdir($thedir, 0700);
                    for ($b = 0; $b < $this->depth; $b++) {
                        $thedir = $tempdir . "/$a/$b/";
                        $failed|= !@mkdir($thedir, 0700);
                        for ($c = 0; $c < $this->depth; $c++) {
                            $thedir = $tempdir . "/$a/$b/$c/";
                            $failed|= !@mkdir($thedir, 0700);
                        }
                    }
                }
                touch($this->cache_storage_checkfile);
            }
        }
         
        function write ($key, $value) {
            $this->cache_object["timestamp"] = strtotime(gmdate('r'));
            $this->cache_object["types"][$key] = gettype($value);
            $this->cache_object["payload"][] = array ($key => serialize($value));
        }
         
        function parseFile () {
            $buffer = "";
            $cachefilename = $this->getCacheFilename($this->cache_id);
            $this->cached_object_exists = false;
             
            if (!file_exists($cachefilename)) {
                // check if cache file exists
                $this->cached_object_exists = false;
            } else {
                $this->cached_object_exists = true;
                $fp = fopen($cachefilename, "r");
                if (!$fp) {
                    $this->cached_object_exists = false;
                    return false;
                }
                flock($fp, 1);
                while (($tmp = fread($fp, 4096))) {
                    $buffer .= $tmp;
                }
                fclose($fp);
                $this->cached_object = unserialize($buffer);
                //   echo "<br>\n".$this->cache_id."<br>\n";
                //   print_r($buffer);
                $this->cached_object_expires = $this->cached_object["expires"];
                $this->cached_object_timestamp = $this->cached_object["timestamp"];
                 
            }
        }
         
        function read () {
            $toexecute = "";
            if ($this->cached_object_exists == false) {
                return false;
            }
            $actualtime = strtotime(gmdate('r'));
            if (($actualtime >= $this->cached_object_expires) && ($this->cached_object_expires > 0)) {
                return false;
            }
            $cached_object_variables = $this->cached_object["payload"];
            for ($i = 0; $i <= count($cached_object_variables)-1; $i++) {
                $keys = @array_keys($cached_object_variables[$i]);
                $values = @array_values($cached_object_variables[$i]);
                $key = $keys[0];
                $value = $values[0];
                $toexecute .= '$'.$key.' = unserialize(\''.$value.'\'); ';
            }
            return $toexecute;
        }
         
        function getValue ($val) {
            if ($this->cached_object_exists == false) {
                return false;
            }
            $actualtime = strtotime(gmdate('r'));
            if (($actualtime >= $this->cached_object_expires) && ($this->cached_object_expires > 0)) {
                return false;
            }
            $cached_object_variables = $this->cached_object["payload"];
            for ($i = 0; $i <= count($cached_object_variables); $i++) {
                if (count($cached_object_variables[$i][$val]) > 0) {
                    return unserialize($cached_object_variables[$i][$val]);
                }
            }
        }
         
        function getTimestamp () {
            if ($this->cached_object_exists == false) {
                return 0;
            }
            $actualtime = strtotime(gmdate('r'));
            return $this->cached_object_expires;
        }
         
        function flush () {
            $myfilename = $this->getCacheFilename($this->cache_id);
            $mydata = serialize($this->cache_object);
            $fp = @fopen($myfilename, "w");
            if (!$fp) {
                return false;
            }
            @flock($fp, LOCK_EX | LOCK_NB);
            fwrite($fp, $mydata, strlen($mydata));
            @flock($fp, 3);
            fclose($fp);
            $this->parseFile();
        }
        
        /***
        * calculate file to store cached values in
        *  
        * @param string $id cachekey
        */
        private function getCacheFilename ($id) {
            $thedir = "";
            $temp = "";
            $temp_filename = preg_replace("/[^A-Z,0-9,=]/", "_", $id);
            if (strlen($temp_filename) >= 10) {
                $temp_filename = md5($temp_filename);
            }
            $cacheobject = "nas." . $temp_filename;
             
            $chunksize = 10;
            $ustr = md5($cacheobject);
            for ($i = 0; $i < 3; $i++) {
                $thenum = abs(crc32(substr($ustr, $i, 4)))%$this->depth;
                $thedir .= "$thenum/";
            }
             
            $theloc = $this->tmpdir.$thedir.$cacheobject;
            return $theloc;
        }
        
        /***
        * purge this cache object
        *  
        */
        public function purge () {
            $filetopurge = $this->getCacheFilename($this->cache_id);
            $result = @unlink($filetopurge);
        }
        
        private function rrmdir($dir) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object);
                             else unlink($dir."/".$object);
                    }
                }
                reset($objects);
                rmdir($dir);
            }            
        }
        
        /***
        * empty this cache
        *  
        */
        function emptyBucket() {
	    if (@file_exists($this->cache_storage_checkfile)) { // check for ok file before purge 
		$this->rrmdir($this->tmpdir);
	    }
            if ($this->reverse_proxy_purge) {
                system($this->reverse_proxy_purge);
            }
        }
         
    }
     
     
?>
