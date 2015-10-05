<?php

session_write_close();
while (@ob_end_clean());

ini_set("zlib.output_compression", "Off");

$request_path = $this->request->path;
$webroot_path = explode('/', rtrim(ltrim((string)sConfig()->getVar("CONFIG/DIRECTORIES/WEBROOT"), '/'), '/'));
$request_path_string = implode('/', $request_path);
$webroot_path_string = implode('/', $webroot_path);
if (strpos($request_path_string, "userimage/")===0) {
	// Remove leading 'page/'
	$request_path_string = substr($request_path_string, strlen("userimage/"));
}
if (strpos($request_path_string, $webroot_path_string.'/')===0) {
	// Remove leading webroot-prefix
	$request_path_string = substr($request_path_string, strlen($webroot_path_string.'/'));
}
$request_path = explode('/', "userimage/".$request_path_string);
if ( ($request_path[0] == 'userimage') &&
	 ($request_path[1] == 'userimage') ) {
	 array_shift($request_path);
}

$userId = (int)$request_path[1];
$imageSize = trim($request_path[2]);

$filesDir = $this->approot.$this->userpicdir;

if ($this->request->parameters['tmp'] === 'true') {
	$fileName = $userId.'-temp_picture.jpg';
	$fileString = realpath($filesDir.$fileName);
} elseif (strlen($imageSize)>0) {
	$fileName = $userId.'-picture-'.$imageSize.'.jpg';
	$fileString = realpath($filesDir.$fileName);
} else {
	$fileName = $userId.'-picture.jpg';
	$fileString = realpath($filesDir.$fileName);
}

if ($userId > 0) {
	if (file_exists(realpath($filesDir.$fileName))) {
		$mime = "application/octet-stream";
		$imgsize = getimagesize($fileString);
		if ($imgsize !== false) {
			$mime = $imgsize["mime"];
		}
		if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")){
			$fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
		}

		if(!$fdl=@fopen($fileString,'rb')){
			die("Cannot Open File!");
		} else {
			header('Expires: ' . gmdate('D, d M Y H:i:s', time()+24*60*60) . ' GMT');
			header("Cache-Control: "); // leave blank to avoid IE errors
			header("Pragma: "); // leave blank to avoid IE errors
			header("Content-type: $mime");
			header("Content-Disposition: inline; filename=\"".$fileName."\"");
			header("Content-length:".(string)(filesize($fileString)));
			while(!feof($fdl)) {
				$buffer = fread($fdl, 4096);
				print $buffer;
			}
			fclose($fdl);
			exit();
		}
	} elseif ( (strlen($imageSize)>0) &&
			   (file_exists(realpath($filesDir.$userId.'-picture-original')))) {
		$imageSize = explode('x', $imageSize);
		$sourceImage = realpath($filesDir.$userId.'-picture-original');
		if ( (count($imageSize) == 2) &&
			 is_numeric($imageSize[0]) &&
			 is_numeric($imageSize[1]) ) {
			$sourceImageSize = getimagesize($sourceImage);
			$desiredWidth = (int)$imageSize[0];
			$desiredHeight = (int)$imageSize[1];
			switch ($sourceImageSize[2]) {
				case 1: // gif
					$gdSourceImage = imagecreatefromgif($sourceImage);
					break;
				case 2: // jpg
					$gdSourceImage = imagecreatefromjpeg($sourceImage);
					break;
				case 3: // png
					$gdSourceImage = imagecreatefrompng($sourceImage);
					break;
				default:
					exit();
			}

			// Calculate source aspect ratio
			$sourceWidth = $sourceImageSize[0];
			$sourceHeight = $sourceImageSize[1];
			$sourceAspect = $sourceWidth / $sourceHeight;

			if ($desiredWidth == 0) $desiredWidth = $desiredHeight * $sourceAspect;
			if ($desiredHeight == 0) $desiredHeight = $desiredWidth / $sourceAspect;

			$targetWidth = $desiredWidth;
			$targetHeight = $desiredHeight;

			$constrainW = 1;
			$constrainH = 1;
			$widthCrop = 1;					// Crop to center
			$heightCrop = 1;				// Crop to center

			if (($constrainW == 0) && ($constrainH == 0)) {
				// Both horizontal & vertical size are set to "fluid"
				if ($sourceAspect == 1) {
					// Do nothing
				} elseif ($sourceAspect < 1) {
					$targetWidth = $targetHeight * $sourceAspect;
				} else {
					$targetHeight = $targetWidth / $sourceAspect;
				}
				if (($targetWidth > $desiredWidth) && ($desiredWidth > 0)) {
					$targetWidth = $desiredWidth;
					$targetHeight = $targetWidth / $sourceAspect;
				}
				if (($targetHeight > $desiredHeight) && ($desiredHeight > 0)) {
					$targetHeight = $desiredHeight;
					$targetWidth = $targetHeight * $sourceAspect;
				}
			} else {
				// One dimension (or both) are set to "fixed"
				if (($constrainW == 1) && ($constrainH == 0)) {
					$targetHeight = $targetWidth / $sourceAspect;
				}
				if (($constrainW == 0) && ($constrainH == 1)) {
					$targetWidth = $targetHeight * $sourceAspect;
				}
			}

			if (($desiredWidth == 0) && ($desiredHeight == 0)) {
				// Use source size if no desired target size is set
				$targetWidth = $sourceWidth;
				$targetHeight = $sourceHeight;
			}

			// Calculate target aspect ratio (and check for division by zero)
			$targetAspect = $targetWidth / $targetHeight;
			if ($targetAspect === false) {
				$targetAspect = 1;
				$targetHeight = $targetWidth;
			}

			$cropFromX = 0;
			$cropFromY = 0;
			$cropToX = $sourceWidth;
			$cropToY = $sourceHeight;
			$intermediateWidth = $targetWidth;
			$intermediateHeight = $targetHeight;

			if (($constrainW == 0) && ($constrainH == 0)) {
				// Both horizontal & vertical size are set to "fluid"
				$cropToX = $targetWidth;
				$cropToY = $targetHeight;
			} else {
				// One dimension (or both) are set to "fixed"
				if (($constrainW == 1) && ($constrainH == 0)) {
					$croptoX = $targetWidth;
					$croptoY = $desiredHeight;
					if ($croptoY > $targetHeight) {
						$croptoY = $targetHeight;
					}
					$targetHeight = $croptoY;
				}
				if (($constrainW == 0) && ($constrainH == 1)) {
					$croptoX = $desiredWidth;
					if ($croptoX > $targetWidth) {
						$croptoX = $targetWidth;
					}
					$croptoY = $targetHeight;
					$targetWidth = $croptoX;
				}

				if (($constrainW == 1) && ($constrainH == 1)) {
					if ($sourceWidth > $sourceHeight) {
						// Source is wider than high
						$intermediateHeight = $targetHeight;
						$intermediateWidth = $targetWidth * $sourceAspect;
					} else {
						// Source is higher than wide
						$intermediateWidth = $targetWidth;
						$intermediateHeight = $targetHeight / $sourceAspect;
					}
				}

				$cropToX = $targetWidth;
				$cropToY = $targetHeight;
				if ($widthCrop == 0) {			// left
					$cropFromX = 0;
				} elseif ($widthCrop == 1) {	// center
					$cropFromX = ($intermediateWidth - $targetWidth) / 2;
				} elseif ($widthCrop == 2) {	// right
					$cropFromX = ($intermediateWidth) - ($targetWidth);
				}
				if ($heightCrop == 0) {			// top
					$cropFromY = 0;
				} elseif ($heightCrop == 1) {	// middle
					$cropFromY = ($intermediateHeight - $targetHeight) / 2;
				} elseif ($heightCrop == 2) {	// bottom
					$cropFromY = ($intermediateHeight) - ($targetHeight);
				}
			}

			$gdIntermediateImage = imagecreatetruecolor($intermediateWidth, $intermediateHeight);
			$result = imagecopyresampled($gdIntermediateImage, $gdSourceImage, 0, 0, 0, 0, $intermediateWidth, $intermediateHeight, $sourceWidth, $sourceHeight);
			$fileString = getrealpath($filesDir."tmp-".$fileName);
			$result = imagejpeg($gdIntermediateImage, $fileString, 100);
			$gdFinalImage = imagecreatetruecolor($targetWidth, $targetHeight);
			$result = imagecopyresampled($gdFinalImage, $gdIntermediateImage, 0, 0, $cropFromX, $cropFromY, $targetWidth, $targetHeight, $cropToX, $cropToY);
			$fileString = getrealpath($filesDir.$fileName);
			$result = imagejpeg($gdFinalImage, $fileString, 100);
			unlink($filesDir."tmp-".$fileName);

			if(is_resource($gdSourceImage)) {
				imagedestroy($gdSourceImage);
			}
			if(is_resource($gdIntermediateImage)) {
				imagedestroy($gdIntermediateImage);
			}
			if(is_resource($gdFinalImage)) {
				imagedestroy($gdFinalImage);
			}

			$mime = "application/octet-stream";
			$imgsize = getimagesize($fileString);
			if ($imgsize !== false) {
				$mime = $imgsize["mime"];
			}
			if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")){
				$fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
			}

			if(!$fdl=@fopen($fileString,'rb')){
				die("Cannot Open File!");
			} else {
				header('Expires: ' . gmdate('D, d M Y H:i:s', time()+24*60*60) . ' GMT');
				header("Cache-Control: "); // leave blank to avoid IE errors
				header("Pragma: "); // leave blank to avoid IE errors
				header("Content-type: $mime");
				header("Content-Disposition: inline; filename=\"".$fileName."\"");
				header("Content-length:".(string)(filesize($fileString)));
				while(!feof($fdl)) {
					$buffer = fread($fdl, 4096);
					print $buffer;
				}
				fclose($fdl);
				exit();
			}
		}
	}
}

?>