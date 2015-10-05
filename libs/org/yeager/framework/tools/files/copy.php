<?php

function cp($wf, $wto){        // it moves $wf to $wto
               if (!file_exists($wto)){//the improvement
               mkdir($wto,0777);
               }
               $arr=ls_a($wf);
               foreach ($arr as $fn){
                           if($fn){
                               $fl="$wf/$fn";
                               $flto="$wto/$fn";
                           if(is_dir($fl))    cp($fl,$flto);
                               else copy($fl,$flto);
                       }
               }
       }

///////////////////////////////////////////////////
/// ls_a function////////////////////////
       // This function lists a directory.
       // ANd is needed for the cp function.

       function ls_a($wh){
         if ($handle = opendir($wh)) {
               while (false !== ($file = readdir($handle))) {
                       if ($file != "." && $file != ".." && (substr($file, 0, 1) != ".") ) {
                                     if(!$files) $files="$file";
                                     else $files="$file\n$files";

                       }
               }
               closedir($handle);
         }
         $arr=explode("\n",$files);
         for($i=0;$i<count($arr);$i++){
         //echo "<br>$arr[$i]";
         }
         return $arr;
       }

?>