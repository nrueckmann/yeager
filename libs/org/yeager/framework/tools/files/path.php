<?php

if (checkCurrentOS( "Win" )) {
	define( "_PL_OS_SEP", "\\" );
} else {
	define( "_PL_OS_SEP", "/" );
}

if ( !function_exists('sys_get_temp_dir') )
{
    // Based on http://www.phpit.net/
    // article/creating-zip-tar-archives-dynamically-php/2/
    function sys_get_temp_dir()
    {
        // Try to get from environment variable
        if ( !empty($_ENV['TMP']) )
        {
            return realpath( $_ENV['TMP'] );
        }
        else if ( !empty($_ENV['TMPDIR']) )
        {
            return realpath( $_ENV['TMPDIR'] );
        }
        else if ( !empty($_ENV['TEMP']) )
        {
            return realpath( $_ENV['TEMP'] );
        }

        // Detect by creating a temporary file
        else
        {
            // Try to use system's temporary directory
            // as random name shouldn't exist
            $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
            if ( $temp_file )
            {
                $temp_dir = realpath( dirname($temp_file) );
                unlink( $temp_file );
                return $temp_dir;
            }
            else
            {
                return FALSE;
            }
        }
    }
}

function checkCurrentOS( $_OS )
{
   $_CUR_OS = substr( php_uname( ), 0, 7 ) == "Windows" ? "Win" : "_Nix";
   if ( strcmp( $_OS, $_CUR_OS ) == 0 ) {
       return true;
   }
   return false;
}

function isRelative( $_dir )
{
   if ( checkCurrentOS( "Win" ) ) {
       return ( preg_match( "/^\w+:/", $_dir ) <= 0 );
   }
   else {
       return ( preg_match( "/^\//", $_dir ) <= 0 );
   }
}

function unifyPath( $_path )
{
   if ( checkCurrentOS( "Win" ) ) {
   	   $rs = str_replace('\\', _PL_OS_SEP, $_path );
       return $rs;
   }
   return $_path;
}

function getRealpath( $_path )
{


   /*
     * This is the starting point of the system root.
     * Left empty for UNIX based and Mac.
     * For Windows this is drive letter and semicolon.
     */
   $__path = $_path;
   if ( isRelative( $_path ) ) {
       $__curdir = unifyPath( realpath( "." ) . _PL_OS_SEP );
       $__path = $__curdir . $__path;
   } elseif ( checkCurrentOS( "Win" ) ) {
		$__path = unifyPath($__path);
   }
   $__startPoint = "";
   if ( checkCurrentOS( "Win" ) ) {
       list( $__startPoint, $__path ) = explode( ":", $__path, 2 );
       $__startPoint .= ":";
   }
   # From now processing is the same for WIndows and Unix, and hopefully for others.
   $__realparts = array( );
   $__parts = explode( _PL_OS_SEP, $__path );
   for ( $i = 0; $i < count( $__parts ); $i++ ) {
       if ( strlen( $__parts[ $i ] ) == 0 || $__parts[ $i ] == "." ) {
           continue;
       }
       if ( $__parts[ $i ] == ".." ) {
           if ( count( $__realparts ) > 0 ) {
               array_pop( $__realparts );
           }
       }
       else {
           array_push( $__realparts, $__parts[ $i ] );
       }
   }
   return $__startPoint . _PL_OS_SEP . implode( _PL_OS_SEP, $__realparts );
}

function resolve_path($base, $path)
{
   $base = str_replace('\\', '/', $base);
   $path = str_replace('\\', '/', $path);

   if (substr($base, -1) == '/')
     $base = substr($base, 0, -1);

   $base = explode('/', $base);
   $path = explode('/', $path);

   // del unnecessary elements
   $path = array_diff($path, array('', '.'));

   while ($dir = array_shift($path))
   {
     if ($dir == '..')
     {
         array_pop($base);
         continue;
     }
     $base[] = $dir;
   }

   if ( checkCurrentOS( "Win" ) ) {
   	 array_shift($base);
 	   return implode('/',$base).'/';
	 } else {
	   return implode('/', $base).'/';
	 }
}

function getsubdirectories ($path, $mask = "") {
	clearstatcache();
	$dirHandle = @opendir($path);
	$j=0;

	while(false !== ($file = readdir($dirHandle))) {
		$filenamearray[$j] = $file;
		$j++;
	}

	$i = 0;
	foreach ($filenamearray as $key => $file) {
		if ( (is_dir($path."/".$file)) && ($file != ".") && ($file != "..") && (substr($file, 0, 1) != ".")) {
			if (strlen($mask) > 0) {
				if (preg_match("/$mask/i", $file)) {
					$directory[$i] = ($file);
					$i++;
				}
			} else {
				$directory[$i] = ($file);
				$i++;
			}
		}
	}
	natcasesort($directory);
	return $directory;
}

function getfilenames ($path, $mask = "") {
	clearstatcache();
    $path = getrealpath($path);
	$dirHandle = @opendir($path);
	$j=0;

	while(false !== ($file = readdir($dirHandle))) {
		$filenamearray[$j] = $file;
		$j++;
	}

	$i = 0;
	foreach ($filenamearray as $key => $file) {
		if ( (!is_dir($path."/".$file)) && ($file != ".") && ($file != "..")) {
			if (strlen($mask) > 0) {
				if (preg_match("/$mask/i", $file)) {
					$directory[$i] = ($file);
					$i++;
				}
			} else {
				$directory[$i] = ($file);
				$i++;
			}
		}
	}
	@natcasesort($directory);
    $directory = array_values($directory);
	return $directory;
}

function getdirlist ($root) {
    $root = realpath($root);
    $content = array();
    if ($handle = opendir($root)) {
        while (false !== ($file = readdir($handle))) {
            if (($file != ".") && ($file != "..")) {
                if (is_dir($root.'/'.$file)) {
                    $content[] = $file;
                }
            }
        }
        closedir($handle);
    }
    sort($content);
    return $content;
}

function getfilelist ($root) {
    $content = array();
    if ($handle = opendir($root)) {
        while (false !== ($file = readdir($handle))) {
            if (($file != ".") && ($file != "..")) {
                if (!is_dir($root.'/'.$file)) {
                    $content[] = $file;
                }
            }
        }
        closedir($handle);
    }
    sort($content);
    return $content;
}

function getFileExtension ($filename) {
    $filename = strtolower($filename) ;
    $exts = split("\.", $filename);
    $n = count($exts)-1;
    $exts = $exts[$n];
    return $exts;
}

function getdirectorysize($root) {

    $root = getRealpath($root);
    $size = 0;

    $fileList = getfilelist($root);
    foreach($fileList as $file) {
        $size += filesize($root."/".$file);
    }

    $dirList = getdirlist($root);
    foreach($dirList as $dir) {
        $size += getdirectorysize($root."/".$dir);
    }

    return $size;
}

?>