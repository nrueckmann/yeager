<?php

	// gd file processor
	class GDProc extends FileProc {

		public function process ($objectid, $params) {
			$view = $params["VIEW"];
			$fileMgr = sFileMgr();
			$view = $fileMgr->views->get($view["ID"]);
			$fileinfo = $params["FILEINFO"];
			$filedir = getrealpath(getcwd()."/".sConfig()->getVar('CONFIG/DIRECTORIES/FILESDIR'))."/";
			$filename = $objectid.'-'.$fileinfo['VIEWVERSION'].$fileinfo['FILENAME'];
			$file = $filedir.$filename;
			if ($params["FROMTMPFILE"]) {
				$file = $params["FROMTMPFILE"];
			}
			$imgsize = getimagesize($file);
			$info = $this->generateThumbnail($filename, $imgsize, $view['IDENTIFIER'], $view['WIDTH'], $view['HEIGHT'], $filedir, $file, $view['CONSTRAINWIDTH'], $view['CONSTRAINHEIGHT'], $view['WIDTHCROP'], $view['HEIGHTCROP']);
			if ($info) {
				$file = new File($objectid, $fileinfo['VIEWVERSION']);
				$file->views->addGenerated($view["ID"], $info["WIDTH"], $info["HEIGHT"], $info["VIEWTYPE"]);
			}
			return true;
		}

		public function generateThumbnail ($filename, $imgsize, $prefix, $desired_width = 0, $desired_height = 0, $filedir, $file, $constrain_w, $constrain_h, $widthcrop, $heightcrop) {
			$imgsize = getimagesize($file);
			switch ($imgsize[2]) {
				case 1:
					// gif
					$source_image = imagecreatefromgif($file);
					break;
				case 2:
					// jpg
					$source_image = imagecreatefromjpeg($file);
					break;
				case 3:
					// png
					$source_image = imagecreatefrompng($file);
					break;
				default:
					return 0;
			}

			// Calculate source aspect ratio
			$source_width = $imgsize[0];
			$source_height = $imgsize[1];
			$source_aspect = $source_width / $source_height;

			if ($desired_width == 0) {
				$desired_width = $desired_height * $source_aspect;
			}
			if ($desired_height == 0) {
				$desired_height = $desired_width / $source_aspect;
			}

			$target_width = $desired_width;
			$target_height = $desired_height;

			if (($constrain_w == 0) && ($constrain_h == 0)) {
				// Both horizontal & vertical size are set to "fluid"
				if ($source_aspect == 1) {
					/* Do nothing */
				} elseif ($source_aspect < 1) {
					$target_width = $target_height * $source_aspect;
				} else {
					$target_height = $target_width / $source_aspect;
				}
				if (($target_width > $desired_width) && ($desired_width > 0)) {
					$target_width = $desired_width;
					$target_height = $target_width / $source_aspect;
				}
				if (($target_height > $desired_height) && ($desired_height > 0)) {
					$target_height = $desired_height;
					$target_width = $target_height * $source_aspect;
				}
			} else {
				// One dimension (or both) are set to "fixed"
				if (($constrain_w == 1) && ($constrain_h == 0)) {
					$target_height = $target_width / $source_aspect;
				}
				if (($constrain_w == 0) && ($constrain_h == 1)) {
					$target_width = $target_height * $source_aspect;
				}
			}

			if (($desired_width == 0) && ($desired_height == 0)) {
				// Use source size if no desired target size is set
				$target_width = $source_width;
				$target_height = $source_height;
			}

			// Calculate target aspect ratio (and check for division by zero)
			$target_aspect = $target_width / $target_height;
			if ($target_aspect === false) {
				$target_aspect = 1;
				$target_height = $target_width;
			}

			$cropfrom_x = 0;
			$cropfrom_y = 0;
			$cropto_x = $source_width;
			$cropto_y = $source_height;
			$intermediate_width = $target_width;
			$intermediate_height = $target_height;

			if (($constrain_w == 0) && ($constrain_h == 0)) {
				// Both horizontal & vertical size are set to "fluid"
				$cropto_x = $target_width;
				$cropto_y = $target_height;
			} else {
				// One dimension (or both) are set to "fixed"
				if (($constrain_w == 1) && ($constrain_h == 0)) {
					$cropto_x = $target_width;
					$cropto_y = $desired_height;
					if ($cropto_y > $target_height) {
						$cropto_y = $target_height;
					}
					$target_height = $cropto_y;
				}
				if (($constrain_w == 0) && ($constrain_h == 1)) {
					$cropto_x = $desired_width;
					if ($cropto_x > $target_width) {
						$cropto_x = $target_width;
					}
					$cropto_y = $target_height;
					$target_width = $cropto_x;
				}

				if (($constrain_w == 1) && ($constrain_h == 1)) {
					if ($source_width > $source_height) {
						// Source is wider than high
						if ($source_aspect > $target_aspect) {
							$intermediate_height = $target_height;
							$intermediate_width = $intermediate_height * $source_aspect;
						} else {
							$intermediate_width = $target_width;
						}
					} else {
						// Source is higher than wide
						if ($source_aspect > $target_aspect) {
							$intermediate_height = $target_height;
						} else {
							$intermediate_width = $target_width;
							$intermediate_height = $intermediate_width / $source_aspect;
						}
					}
				}

				$cropto_x = $target_width;
				$cropto_y = $target_height;
				if ($widthcrop == 0) {			// left
					$cropfrom_x = 0;
				} elseif ($widthcrop == 1) {	// center
					$cropfrom_x = ($intermediate_width / 2) - ($target_width / 2);
				} elseif ($widthcrop == 2) {	// right
					$cropfrom_x = ($intermediate_width) - ($target_width);
				}
				if ($heightcrop == 0) {			// top
					$cropfrom_y = 0;
				} elseif ($heightcrop == 1) {	// middle
					$cropfrom_y = ($intermediate_height / 2) - ($target_height / 2);
				} elseif ($heightcrop == 2) {	// bottom
					$cropfrom_y = ($intermediate_height) - ($target_height);
				}
			}

			$intermediate_image = imagecreatetruecolor($intermediate_width, $intermediate_height);
			imagecopyresampled($intermediate_image, $source_image, 0, 0, 0, 0, $intermediate_width, $intermediate_height, $source_width, $source_height);
			$ff = getrealpath($filedir."tmp-".$prefix.$filename);
			imagejpeg($intermediate_image, $ff, 100);

			$final_image = imagecreatetruecolor($target_width, $target_height);
			imagecopyresampled($final_image, $intermediate_image, 0, 0, $cropfrom_x, $cropfrom_y, $target_width, $target_height, $cropto_x, $cropto_y);
			$ff = getrealpath($filedir.$prefix.pathinfo($filename, PATHINFO_FILENAME).'.jpg');
			imagejpeg($final_image, $ff, 100);
			unlink($filedir."tmp-".$prefix.$filename);

			if(is_resource($source_image)) {
				imagedestroy($source_image);
			}
			if(is_resource($intermediate_image)) {
				imagedestroy($intermediate_image);
			}
			if(is_resource($final_image)) {
				imagedestroy($final_image);
			}

			$info["WIDTH"] = $target_width;
			$info["HEIGHT"] = $target_height;
			$info["VIEWTYPE"] = FILE_TYPE_WEBIMAGE;

			return $info;
		}

		public function cropFile ($filename, $x, $y, $width, $height) {
			$imgsize = getimagesize($filename);
			switch ($imgsize[2]) {
				case 1:
				// gif
				$img_in = imagecreatefromgif($filename);
				break;
				case 2:
				// jpg
				$img_in = imagecreatefromjpeg($filename);
				break;
				case 3:
				// png
				$img_in = imagecreatefrompng($filename);
				break;
				default:
				return 0;
			}
			$img_out = imagecreatetruecolor($width, $height);
			imagecopyresampled($img_out, $img_in, 0, 0, $x, $y, $width, $height, $width, $height);
			imagejpeg($img_out, $filename, 100);

			if(is_resource($img_in)) {
				imagedestroy($img_in);
			}
			if(is_resource($img_out)) {
				imagedestroy($img_out);
			}
		}

	}

?>