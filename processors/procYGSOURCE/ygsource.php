<?php

	// gd file processor
	class YGSource extends FileProc {

		public function process ($objectid, $params) {
			$view = $params["VIEW"];
			$fileMgr = sFileMgr();
			$view = $fileMgr->views->get($view["ID"]);
			$fileinfo = $params["FILEINFO"];
			$filedir = getrealpath(getcwd()."/".sConfig()->getVar('CONFIG/DIRECTORIES/FILESDIR'))."/";
			$filename = $fileinfo['OBJECTID'].'-'.$fileinfo['VIEWVERSION'].$fileinfo['FILENAME'];
			$file = $filedir.$filename;
			$imgsize = getimagesize($file);
			$info = $this->generateThumbnail($filename, $imgsize, $view['IDENTIFIER'], $view['WIDTH'], $view['HEIGHT'], $filedir, $file, $view['CONSTRAINWIDTH'], $view['CONSTRAINHEIGHT'], $view['WIDTHCROP'], $view['HEIGHTCROP'] );
			if ($info) {
				$file = new File($fileinfo['OBJECTID'], $fileinfo['VERSION']);
				$file->views->addGenerated($view["ID"], $info["WIDTH"], $info["HEIGHT"], $info["VIEWTYPE"]);
			}
			return true;
		}

		public function generateThumbnail ($filename, $imgsize, $prefix, $desired_width = 0, $desired_height = 0, $filedir, $file, $constrain_w, $constrain_h, $widthcrop, $heightcrop) {

			$imgsize = getimagesize($file);
			$source_width = $imgsize[0];
			$source_height = $imgsize[1];

			$ext = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
			if (in_array($ext, array("JPG", "JPEG", "GIF", "PNG"))) {
				$info["VIEWTYPE"] = FILE_TYPE_WEBIMAGE;
			} else {
				$info["VIEWTYPE"] = FILE_TYPE_WEBNONE;
			}

			$info["WIDTH"] = $source_width;
			$info["HEIGHT"] = $source_height;
			return $info;
		}

	}

?>