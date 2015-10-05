<?php

/// @cond DEV

/***
 * extended ArchiveTar to transparently work with attached archives
 */
class PayloadTar extends ArchiveTar {
	var $selfextracting = false;
	var $development = false;
	var $compiler_halt_offset = 0;

	function getCompilerHaltOffset() {
		$fh = fopen($this->_tarname, 'rb');
		// Read first 128kb of SFX and search for "__halt_compiler();"
		$fileContents = fread($fh, 128 * 1024);
		fclose($fh);
		return strpos($fileContents, '__halt_compiler();') + 18;
	}

	function __construct($p_tarname, $selfextracting = false, $p_compress = null, $development = False) {
		$this->development = $development;
		$this->selfextracting = $selfextracting;
		$this->_tarname = $p_tarname;
		$this->compiler_halt_offset = $this->getCompilerHaltOffset();
		$this->PEAR();
		$this->_compress = false;
		$this->_compress_type = 'none';
		if (($p_compress === null) || ($p_compress == '')) {
			if (@file_exists($p_tarname)) {
				if ($fp = @fopen($p_tarname, "rb")) {
					// look for gzip magic cookie
					if ($this->selfextracting) {
						fseek($fp, $this->compiler_halt_offset);
					}
					$data = fread($fp, 2);
					fclose($fp);
					if ($data == "\37\213") {
						$this->_compress = true;
						$this->_compress_type = 'gz';
						// No sure it's enought for a magic code ....
					} elseif ($data == "BZ") {
						$this->_compress = true;
						$this->_compress_type = 'bz2';
					}
				}
			} else {
				// probably a remote file or some file accessible
				// through a stream interface
				if (substr($p_tarname, -2) == 'gz') {
					$this->_compress = true;
					$this->_compress_type = 'gz';
				} elseif ((substr($p_tarname, -3) == 'bz2') || (substr($p_tarname, -2) == 'bz')) {
					$this->_compress = true;
					$this->_compress_type = 'bz2';
				}
			}
		} else {
			if (($p_compress === true) || ($p_compress == 'gz')) {
				$this->_compress = true;
				$this->_compress_type = 'gz';
			} elseif ($p_compress == 'bz2') {
				$this->_compress = true;
				$this->_compress_type = 'bz2';
			} else {
				$this->error("Unsupported compression type '$p_compress'\n" . "Supported types are 'gz' and 'bz2'.\n");
				return false;
			}
		}
		if ($this->_compress) {
			// assert zlib or bz2 extension support
			if ($this->_compress_type == 'gz') {
				$extname = 'zlib';
			} elseif ($this->_compress_type == 'bz2') {
				$extname = 'bz2';
			}

			if (!extension_loaded($extname)) {
				PEAR::loadExtension($extname);
			}
			if (!extension_loaded($extname)) {
				$this->error("The extension '$extname' couldn't be found.\n" . "Please make sure your version of PHP was built " . "with '$extname' support.\n");
				return false;
			}
		}
	}

	function PEAR() {
		return true;
	}

	function setErrorHandling() {
		return true;
	}

	function raiseError($message) {
		print $message;
	}

	function openRead() {
		if (strtolower(substr($this->_tarname, 0, 7)) == 'http://') {

			// ----- Look if a local copy need to be done
			if ($this->_temp_tarname == '') {
				$this->_temp_tarname = uniqid('tar') . '.tmp';
				if (!$v_file_from = @fopen($this->_tarname, 'rb')) {
					$this->error('Unable to open in read mode \'' . $this->_tarname . '\'');
					$this->_temp_tarname = '';
					return false;
				}
				if (!$v_file_to = @fopen($this->_temp_tarname, 'wb')) {
					$this->error('Unable to open in write mode \'' . $this->_temp_tarname . '\'');
					$this->_temp_tarname = '';
					return false;
				}
				while ($v_data = @fread($v_file_from, 1024))
					@fwrite($v_file_to, $v_data);
				@fclose($v_file_from);
				@fclose($v_file_to);
			}

			// ----- File to open if the local copy
			$v_filename = $this->_temp_tarname;

		} else
			// ----- File to open if the normal Tar file
		{
			$v_filename = $this->_tarname;
		}

		if ($this->_compress_type == 'gz') {
			$fp = fopen($v_filename, 'r');
			fseek($fp, $this->compiler_halt_offset);
			$offset = ftell($fp);
			fclose($fp);

			$this->_file = gzopen($v_filename, "rb");
			if ($this->selfextracting) {
				fseek($this->_file, $offset);

				$tfp = gzopen("tester.tar", "w");
				while ($string = gzread($this->_file, 4096)) {
					fwrite($tfp, $string, strlen($string));
				}
				fclose($tfp);
				fseek($this->_file, $offset);
			}

		} elseif ($this->_compress_type == 'bz2') {
			$this->_file = @bzopen($v_filename, "r");
			if ($this->selfextracting) {
				fseek($this->_file, $this->compiler_halt_offset);
			}
		} elseif ($this->_compress_type == 'none') {
			$this->_file = @fopen($v_filename, "rb");
			if ($this->selfextracting) {
				fseek($this->_file, $this->compiler_halt_offset);
			}
		} else {
			$this->error('Unknown or missing compression type (' . $this->_compress_type . ')');
		}

		if ($this->_file == 0) {
			$this->error('Unable to open in read mode \'' . $v_filename . '\'');
			return false;
		}

		return true;
	}

	function readBlock() {
		$v_block = null;
		if (is_resource($this->_file)) {
			if ($this->_compress_type == 'gz') {
				$v_block = gzread($this->_file, 512);
			} elseif ($this->_compress_type == 'bz2') {
				$v_block = @bzread($this->_file, 512);
			}
			elseif ($this->_compress_type == 'none') {
				$v_block = @fread($this->_file, 512);
			}
			else {
				$this->error('Unknown or missing compression type (' . $this->_compress_type . ')');
			}
		}
		return $v_block;
	}

	function extractInString($filename) {
		if ($this->development == true) {
			return @file_get_contents($filename);
		}
		return parent::extractInString($filename);
	}

	function extractList($p_filelist, $p_path = '', $p_remove_path = '') {
		if ($this->development == true) {
			$v_result = true;
			$v_list_detail = array();

			if (is_array($p_filelist)) {
				$v_list = $p_filelist;
			} elseif (is_string($p_filelist)) {
				$v_list = explode($this->_separator, $p_filelist);
			} else {
				$this->error('Invalid string list');
				return false;
			}

			foreach ($v_list as $v_list_item) {
				if ((strlen($p_remove_path) > 0) && (strpos($v_list_item, $p_remove_path) === 0)) {
					$v_list_item = substr($v_list_item, strlen($p_remove_path));
				}

				$pathPrefix = substr($v_list_item, 0, strrpos($v_list_item, basename($v_list_item)));
				$targetPath = $p_path . '/' . $pathPrefix;

				if (!is_dir($targetPath)) {
					$v_result = mkdir($targetPath, 0777, true);
					if (!$v_result) {
						break;
					}
				}

				$v_result = copy(realpath(dirname(realpath($this->_tarname)) . '/app/' . $v_list_item), $targetPath . basename($v_list_item));
				if (!$v_result) {
					break;
				}
			}
			return $v_result;
		} else {
			return parent::extractList($p_filelist, $p_path, $p_remove_path);
		}
	}

	function listContent() {
		if ($this->development == true) {
			$files = getFilesFromDir(realpath(dirname(realpath($this->_tarname)) . '/'));

			// remove absolute prefix
			$currDir = realpath(dirname(realpath($this->_tarname)) . '/');
			foreach ($files as $tmp_item) {
				$tmp[] = substr($tmp_item, (strlen($currDir) + 1));
			}

			$finalArray = array();
			foreach ($tmp as $tmp_item) {
				$statInfo = stat($currDir . '/' . $tmp_item);
				$finalArray[] = array(
					'filename' => $tmp_item,
					'size' => $statInfo['size'],
					'mtime' => $statInfo['mtime'],
					'mode' => $statInfo['mode'],
					'uid' => $statInfo['uid'],
					'gid' => $statInfo['gid'],
					'typeflag' => NULL
				);
			}
			return $finalArray;
		} else {
			return parent::listContent();
		}
	}
}

/**
 * File::CSV
 *
 * PHP versions 4 and 5
 *
 * Copyright (c) 1997-2008,
 * Vincent Blavet <vincent@phpconcept.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * @category    File_Formats
 * @package     ArchiveTar
 * @author      Vincent Blavet <vincent@phpconcept.net>
 * @copyright   1997-2008 The Authors
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version     CVS: $Id: Tar.php 295988 2010-03-09 08:39:37Z mrook $
 * @link        http://pear.php.net/package/ArchiveTar
 */

//require_once 'PEAR.php';

define ('ARCHIVE_TAR_ATT_SEPARATOR', 90001);
define ('ARCHIVE_TAR_END_BLOCK', pack("a512", ''));

/**
 * Creates a (compressed) Tar archive
 *
 * @author   Vincent Blavet <vincent@phpconcept.net>
 * @version  $Revision: 295988 $
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package  ArchiveTar
 */
class ArchiveTar {
	/**
	 * @var string Name of the Tar
	 */
	var $_tarname = '';

	/**
	 * @var boolean if true, the Tar file will be gzipped
	 */
	var $_compress = false;

	/**
	 * @var string Type of compression : 'none', 'gz' or 'bz2'
	 */
	var $_compress_type = 'none';

	/**
	 * @var string Explode separator
	 */
	var $_separator = ' ';

	/**
	 * @var file descriptor
	 */
	var $_file = 0;

	/**
	 * @var string Local Tar name of a remote Tar (http:// or ftp://)
	 */
	var $_temp_tarname = '';

	/**
	 * @var string regular expression for ignoring files or directories
	 */
	var $_ignore_regexp = '';


	/**
	 * ArchiveTar Class constructor. This flavour of the constructor only
	 * declare a new ArchiveTar object, identifying it by the name of the
	 * tar file.
	 * If the compress argument is set the tar will be read or created as a
	 * gzip or bz2 compressed TAR file.
	 *
	 * @param    string  $p_tarname  The name of the tar archive to create
	 * @param    string  $p_compress can be null, 'gz' or 'bz2'. This
	 *                   parameter indicates if gzip or bz2 compression
	 *                   is required.  For compatibility reason the
	 *                   boolean value 'true' means 'gz'.
	 * @access public
	 */
	function __construct($p_tarname, $p_compress = null) {
		$this->PEAR();
		$this->_compress = false;
		$this->_compress_type = 'none';
		if (($p_compress === null) || ($p_compress == '')) {
			if (@file_exists($p_tarname)) {
				if ($fp = @fopen($p_tarname, "rb")) {
					// look for gzip magic cookie
					$data = fread($fp, 2);
					fclose($fp);
					if ($data == "\37\213") {
						$this->_compress = true;
						$this->_compress_type = 'gz';
						// No sure it's enought for a magic code ....
					} elseif ($data == "BZ") {
						$this->_compress = true;
						$this->_compress_type = 'bz2';
					}
				}
			} else {
				// probably a remote file or some file accessible
				// through a stream interface
				if (substr($p_tarname, -2) == 'gz') {
					$this->_compress = true;
					$this->_compress_type = 'gz';
				} elseif ((substr($p_tarname, -3) == 'bz2') || (substr($p_tarname, -2) == 'bz')) {
					$this->_compress = true;
					$this->_compress_type = 'bz2';
				}
			}
		} else {
			if (($p_compress === true) || ($p_compress == 'gz')) {
				$this->_compress = true;
				$this->_compress_type = 'gz';
			} elseif ($p_compress == 'bz2') {
				$this->_compress = true;
				$this->_compress_type = 'bz2';
			} else {
				$this->error("Unsupported compression type '$p_compress'\n" . "Supported types are 'gz' and 'bz2'.\n");
				return false;
			}
		}
		$this->_tarname = $p_tarname;
		if ($this->_compress) {
			// assert zlib or bz2 extension support
			if ($this->_compress_type == 'gz') {
				$extname = 'zlib';
			} elseif ($this->_compress_type == 'bz2') {
				$extname = 'bz2';
			}

			if (!extension_loaded($extname)) {
				PEAR::loadExtension($extname);
			}
			if (!extension_loaded($extname)) {
				$this->error("The extension '$extname' couldn't be found.\n" . "Please make sure your version of PHP was built " . "with '$extname' support.\n");
				return false;
			}
		}
	}

	function __destruct() {
		$this->close();
		// ----- Look for a local copy to delete
		if ($this->_temp_tarname != '') {
			@unlink($this->_temp_tarname);
		}
		//$this->_PEAR();
	}


	/**
	 * This method creates the archive file and add the files / directories
	 * that are listed in $p_filelist.
	 * If a file with the same name exist and is writable, it is replaced
	 * by the new tar.
	 * The method return false and a PEAR error text.
	 * The $p_filelist parameter can be an array of string, each string
	 * representing a filename or a directory name with their path if
	 * needed. It can also be a single string with names separated by a
	 * single blank.
	 * For each directory added in the archive, the files and
	 * sub-directories are also added.
	 * See also createModify() method for more details.
	 *
	 * @param array  $p_filelist An array of filenames and directory names, or a
	 *                           single string with names separated by a single
	 *                           blank space.
	 * @return                   true on success, false on error.
	 * @see createModify()
	 * @access public
	 */
	function create($p_filelist) {
		return $this->createModify($p_filelist, '', '');
	}


	/**
	 * This method add the files / directories that are listed in $p_filelist in
	 * the archive. If the archive does not exist it is created.
	 * The method return false and a PEAR error text.
	 * The files and directories listed are only added at the end of the archive,
	 * even if a file with the same name is already archived.
	 * See also createModify() method for more details.
	 *
	 * @param array  $p_filelist An array of filenames and directory names, or a
	 *                           single string with names separated by a single
	 *                           blank space.
	 * @return                   true on success, false on error.
	 * @see createModify()
	 * @access public
	 */
	function add($p_filelist) {
		return $this->addModify($p_filelist, '', '');
	}

	function extract($p_path = '') {
		return $this->extractModify($p_path, '');
	}

	function listContent() {
		$v_list_detail = array();
		if ($this->openRead()) {
			if (!$this->extractListInternal('', $v_list_detail, "list", '', '')) {
				unset($v_list_detail);
				$v_list_detail = 0;
			}
			$this->close();
		}
		return $v_list_detail;
	}

	/**
	 * This method creates the archive file and add the files / directories
	 * that are listed in $p_filelist.
	 * If the file already exists and is writable, it is replaced by the
	 * new tar. It is a create and not an add. If the file exists and is
	 * read-only or is a directory it is not replaced. The method return
	 * false and a PEAR error text.
	 * The $p_filelist parameter can be an array of string, each string
	 * representing a filename or a directory name with their path if
	 * needed. It can also be a single string with names separated by a
	 * single blank.
	 * The path indicated in $p_remove_dir will be removed from the
	 * memorized path of each file / directory listed when this path
	 * exists. By default nothing is removed (empty path '')
	 * The path indicated in $p_add_dir will be added at the beginning of
	 * the memorized path of each file / directory listed. However it can
	 * be set to empty ''. The adding of a path is done after the removing
	 * of path.
	 * The path add/remove ability enables the user to prepare an archive
	 * for extraction in a different path than the origin files are.
	 * See also addModify() method for file adding properties.
	 *
	 * @param array  $p_filelist     An array of filenames and directory names,
	 *                               or a single string with names separated by
	 *                               a single blank space.
	 * @param string $p_add_dir      A string which contains a path to be added
	 *                               to the memorized path of each element in
	 *                               the list.
	 * @param string $p_remove_dir   A string which contains a path to be
	 *                               removed from the memorized path of each
	 *                               element in the list, when relevant.
	 * @return boolean               true on success, false on error.
	 * @access public
	 * @see addModify()
	 */
	function createModify($p_filelist, $p_add_dir, $p_remove_dir = '') {
		$v_result = true;

		if (!$this->openWrite()) {
			return false;
		}

		if ($p_filelist != '') {
			if (is_array($p_filelist)) {
				$v_list = $p_filelist;
			} elseif (is_string($p_filelist)) {
				$v_list = explode($this->_separator, $p_filelist);
			}
			else {
				$this->cleanFile();
				$this->error('Invalid file list');
				return false;
			}

			$v_result = $this->addList($v_list, $p_add_dir, $p_remove_dir);
		}

		if ($v_result) {
			$this->writeFooter();
			$this->close();
		} else {
			$this->cleanFile();
		}

		return $v_result;
	}

	/**
	 * This method add the files / directories listed in $p_filelist at the
	 * end of the existing archive. If the archive does not yet exists it
	 * is created.
	 * The $p_filelist parameter can be an array of string, each string
	 * representing a filename or a directory name with their path if
	 * needed. It can also be a single string with names separated by a
	 * single blank.
	 * The path indicated in $p_remove_dir will be removed from the
	 * memorized path of each file / directory listed when this path
	 * exists. By default nothing is removed (empty path '')
	 * The path indicated in $p_add_dir will be added at the beginning of
	 * the memorized path of each file / directory listed. However it can
	 * be set to empty ''. The adding of a path is done after the removing
	 * of path.
	 * The path add/remove ability enables the user to prepare an archive
	 * for extraction in a different path than the origin files are.
	 * If a file/dir is already in the archive it will only be added at the
	 * end of the archive. There is no update of the existing archived
	 * file/dir. However while extracting the archive, the last file will
	 * replace the first one. This results in a none optimization of the
	 * archive size.
	 * If a file/dir does not exist the file/dir is ignored. However an
	 * error text is send to PEAR error.
	 * If a file/dir is not readable the file/dir is ignored. However an
	 * error text is send to PEAR error.
	 *
	 * @param array      $p_filelist     An array of filenames and directory
	 *                                   names, or a single string with names
	 *                                   separated by a single blank space.
	 * @param string     $p_add_dir      A string which contains a path to be
	 *                                   added to the memorized path of each
	 *                                   element in the list.
	 * @param string     $p_remove_dir   A string which contains a path to be
	 *                                   removed from the memorized path of
	 *                                   each element in the list, when
	 *                                   relevant.
	 * @return                           true on success, false on error.
	 * @access public
	 */
	function addModify($p_filelist, $p_add_dir, $p_remove_dir = '') {
		$v_result = true;

		if (!$this->isArchive()) {
			$v_result = $this->createModify($p_filelist, $p_add_dir, $p_remove_dir);
		} else {
			if (is_array($p_filelist)) {
				$v_list = $p_filelist;
			} elseif (is_string($p_filelist)) {
				$v_list = explode($this->_separator, $p_filelist);
			}
			else {
				$this->error('Invalid file list');
				return false;
			}

			$v_result = $this->append($v_list, $p_add_dir, $p_remove_dir);
		}

		return $v_result;
	}

	/**
	 * This method add a single string as a file at the
	 * end of the existing archive. If the archive does not yet exists it
	 * is created.
	 *
	 * @param string     $p_filename     A string which contains the full
	 *                                   filename path that will be associated
	 *                                   with the string.
	 * @param string     $p_string       The content of the file added in
	 *                                   the archive.
	 * @return                           true on success, false on error.
	 * @access public
	 */
	function addString($p_filename, $p_string) {
		$v_result = true;

		if (!$this->isArchive()) {
			if (!$this->openWrite()) {
				return false;
			}
			$this->close();
		}

		if (!$this->openAppend()) {
			return false;
		}

		// Need to check the get back to the temporary file ? ....
		$v_result = $this->addStringInternal($p_filename, $p_string);

		$this->writeFooter();

		$this->close();

		return $v_result;
	}

	/**
	 * This method extract all the content of the archive in the directory
	 * indicated by $p_path. When relevant the memorized path of the
	 * files/dir can be modified by removing the $p_remove_path path at the
	 * beginning of the file/dir path.
	 * While extracting a file, if the directory path does not exists it is
	 * created.
	 * While extracting a file, if the file already exists it is replaced
	 * without looking for last modification date.
	 * While extracting a file, if the file already exists and is write
	 * protected, the extraction is aborted.
	 * While extracting a file, if a directory with the same name already
	 * exists, the extraction is aborted.
	 * While extracting a directory, if a file with the same name already
	 * exists, the extraction is aborted.
	 * While extracting a file/directory if the destination directory exist
	 * and is write protected, or does not exist but can not be created,
	 * the extraction is aborted.
	 * If after extraction an extracted file does not show the correct
	 * stored file size, the extraction is aborted.
	 * When the extraction is aborted, a PEAR error text is set and false
	 * is returned. However the result can be a partial extraction that may
	 * need to be manually cleaned.
	 *
	 * @param string $p_path         The path of the directory where the
	 *                               files/dir need to by extracted.
	 * @param string $p_remove_path  Part of the memorized path that can be
	 *                               removed if present at the beginning of
	 *                               the file/dir path.
	 * @return boolean               true on success, false on error.
	 * @access public
	 * @see extractList()
	 */
	function extractModify($p_path, $p_remove_path) {
		$v_result = true;
		$v_list_detail = array();

		if ($v_result = $this->openRead()) {
			$v_result = $this->extractListInternal($p_path, $v_list_detail, "complete", 0, $p_remove_path);
			$this->close();
		}

		return $v_result;
	}

	/**
	 * This method extract from the archive one file identified by $p_filename.
	 * The return value is a string with the file content, or NULL on error.
	 * @param string $p_filename     The path of the file to extract in a string.
	 * @return                       a string with the file content or NULL.
	 * @access public
	 */
	function extractInString($p_filename) {
		if ($this->openRead()) {
			$v_result = $this->extractInStringInternal($p_filename);
			$this->close();
		} else {
			$v_result = NULL;
		}

		return $v_result;
	}

	/**
	 * This method extract from the archive only the files indicated in the
	 * $p_filelist. These files are extracted in the current directory or
	 * in the directory indicated by the optional $p_path parameter.
	 * If indicated the $p_remove_path can be used in the same way as it is
	 * used in extractModify() method.
	 * @param array  $p_filelist     An array of filenames and directory names,
	 *                               or a single string with names separated
	 *                               by a single blank space.
	 * @param string $p_path         The path of the directory where the
	 *                               files/dir need to by extracted.
	 * @param string $p_remove_path  Part of the memorized path that can be
	 *                               removed if present at the beginning of
	 *                               the file/dir path.
	 * @return                       true on success, false on error.
	 * @access public
	 * @see extractModify()
	 */
	function extractList($p_filelist, $p_path = '', $p_remove_path = '') {
		$v_result = true;
		$v_list_detail = array();

		if (is_array($p_filelist)) {
			$v_list = $p_filelist;
		} elseif (is_string($p_filelist)) {
			$v_list = explode($this->_separator, $p_filelist);
		}
		else {
			$this->error('Invalid string list');
			return false;
		}

		if ($v_result = $this->openRead()) {
			$v_result = $this->extractListInternal($p_path, $v_list_detail, "partial",
				$v_list, $p_remove_path);
			$this->close();
		}

		return $v_result;
	}

	/**
	 * This method set specific attributes of the archive. It uses a variable
	 * list of parameters, in the format attribute code + attribute values :
	 * $arch->setAttribute(ARCHIVE_TAR_ATT_SEPARATOR, ',');
	 * @param mixed $argv            variable list of attributes and values
	 * @return                       true on success, false on error.
	 * @access public
	 */
	function setAttribute() {
		$v_result = true;

		// ----- Get the number of variable list of arguments
		if (($v_size = func_num_args()) == 0) {
			return true;
		}

		// ----- Get the arguments
		$v_att_list = &func_get_args();

		// ----- Read the attributes
		$i = 0;
		while ($i < $v_size) {

			// ----- Look for next option
			switch ($v_att_list[$i]) {
				// ----- Look for options that request a string value
				case ARCHIVE_TAR_ATT_SEPARATOR :
					// ----- Check the number of parameters
					if (($i + 1) >= $v_size) {
						$this->error('Invalid number of parameters for ' . 'attribute ARCHIVE_TAR_ATT_SEPARATOR');
						return false;
					}

					// ----- Get the value
					$this->_separator = $v_att_list[$i + 1];
					$i++;
					break;

				default :
					$this->error('Unknow attribute code ' . $v_att_list[$i] . '');
					return false;
			}

			// ----- Next attribute
			$i++;
		}

		return $v_result;
	}

	/**
	 * This method sets the regular expression for ignoring files and directories
	 * at import, for example:
	 * $arch->setIgnoreRegexp("#CVS|\.svn#");
	 * @param string $regexp         regular expression defining which files or directories to ignore
	 * @access public
	 */
	function setIgnoreRegexp($regexp) {
		$this->_ignore_regexp = $regexp;
	}

	/**
	 * This method sets the regular expression for ignoring all files and directories
	 * matching the filenames in the array list at import, for example:
	 * $arch->setIgnoreList(array('CVS', '.svn', 'bin/tool'));
	 * @param array $list         a list of file or directory names to ignore
	 * @access public
	 */
	function setIgnoreList($list) {
		$regexp = str_replace(array('#', '.', '^', '$'), array('\#', '\.', '\^', '\$'), $list);
		$regexp = '#/' . join('$|/', $list) . '#';
		$this->setIgnoreRegexp($regexp);
	}

	function error($p_message) {
		// ----- To be completed
		$this->raiseError($p_message);
	}

	function warning($p_message) {
		// ----- To be completed
		$this->raiseError($p_message);
	}

	function isArchive($p_filename = NULL) {
		if ($p_filename == NULL) {
			$p_filename = $this->_tarname;
		}
		clearstatcache();
		return @is_file($p_filename) && !@is_link($p_filename);
	}

	function openWrite() {
		if ($this->_compress_type == 'gz') {
			$this->_file = @gzopen($this->_tarname, "wb9");
		} elseif ($this->_compress_type == 'bz2') {
			$this->_file = @bzopen($this->_tarname, "w");
		}
		elseif ($this->_compress_type == 'none') {
			$this->_file = @fopen($this->_tarname, "wb");
		}
		else {
			$this->error('Unknown or missing compression type (' . $this->_compress_type . ')');
		}

		if ($this->_file == 0) {
			$this->error('Unable to open in write mode \'' . $this->_tarname . '\'');
			return false;
		}

		return true;
	}

	function openRead() {
		if (strtolower(substr($this->_tarname, 0, 7)) == 'http://') {

			// ----- Look if a local copy need to be done
			if ($this->_temp_tarname == '') {
				$this->_temp_tarname = uniqid('tar') . '.tmp';
				if (!$v_file_from = @fopen($this->_tarname, 'rb')) {
					$this->error('Unable to open in read mode \'' . $this->_tarname . '\'');
					$this->_temp_tarname = '';
					return false;
				}
				if (!$v_file_to = @fopen($this->_temp_tarname, 'wb')) {
					$this->error('Unable to open in write mode \'' . $this->_temp_tarname . '\'');
					$this->_temp_tarname = '';
					return false;
				}
				while ($v_data = @fread($v_file_from, 1024))
					@fwrite($v_file_to, $v_data);
				@fclose($v_file_from);
				@fclose($v_file_to);
			}

			// ----- File to open if the local copy
			$v_filename = $this->_temp_tarname;

		} else
			// ----- File to open if the normal Tar file
		{
			$v_filename = $this->_tarname;
		}

		if ($this->_compress_type == 'gz') {
			$this->_file = @gzopen($v_filename, "rb");
		} elseif ($this->_compress_type == 'bz2') {
			$this->_file = @bzopen($v_filename, "r");
		}
		elseif ($this->_compress_type == 'none') {
			$this->_file = @fopen($v_filename, "rb");
		}
		else {
			$this->error('Unknown or missing compression type (' . $this->_compress_type . ')');
		}

		if ($this->_file == 0) {
			$this->error('Unable to open in read mode \'' . $v_filename . '\'');
			return false;
		}

		return true;
	}

	function openReadWrite() {
		if ($this->_compress_type == 'gz') {
			$this->_file = @gzopen($this->_tarname, "r+b");
		} elseif ($this->_compress_type == 'bz2') {
			$this->error('Unable to open bz2 in read/write mode \'' . $this->_tarname . '\' (limitation of bz2 extension)');
			return false;
		} elseif ($this->_compress_type == 'none') {
			$this->_file = @fopen($this->_tarname, "r+b");
		}
		else {
			$this->error('Unknown or missing compression type (' . $this->_compress_type . ')');
		}

		if ($this->_file == 0) {
			$this->error('Unable to open in read/write mode \'' . $this->_tarname . '\'');
			return false;
		}

		return true;
	}

	function close() {
		//if (isset($this->_file)) {
		if (is_resource($this->_file)) {
			if ($this->_compress_type == 'gz') {
				@gzclose($this->_file);
			} elseif ($this->_compress_type == 'bz2') {
				@bzclose($this->_file);
			}
			elseif ($this->_compress_type == 'none') {
				@fclose($this->_file);
			}
			else {
				$this->error('Unknown or missing compression type (' . $this->_compress_type . ')');
			}

			$this->_file = 0;
		}

		// ----- Look if a local copy need to be erase
		// Note that it might be interesting to keep the url for a time : ToDo
		if ($this->_temp_tarname != '') {
			@unlink($this->_temp_tarname);
			$this->_temp_tarname = '';
		}

		return true;
	}

	function cleanFile() {
		$this->close();

		// ----- Look for a local copy
		if ($this->_temp_tarname != '') {
			// ----- Remove the local copy but not the remote tarname
			@unlink($this->_temp_tarname);
			$this->_temp_tarname = '';
		} else {
			// ----- Remove the local tarname file
			@unlink($this->_tarname);
		}
		$this->_tarname = '';

		return true;
	}

	function writeBlock($p_binary_data, $p_len = null) {
		if (is_resource($this->_file)) {
			if ($p_len === null) {
				if ($this->_compress_type == 'gz') {
					@gzputs($this->_file, $p_binary_data);
				} elseif ($this->_compress_type == 'bz2') {
					@bzwrite($this->_file, $p_binary_data);
				}
				elseif ($this->_compress_type == 'none') {
					@fputs($this->_file, $p_binary_data);
				}
				else {
					$this->error('Unknown or missing compression type (' . $this->_compress_type . ')');
				}
			} else {
				if ($this->_compress_type == 'gz') {
					@gzputs($this->_file, $p_binary_data, $p_len);
				} elseif ($this->_compress_type == 'bz2') {
					@bzwrite($this->_file, $p_binary_data, $p_len);
				}
				elseif ($this->_compress_type == 'none') {
					@fputs($this->_file, $p_binary_data, $p_len);
				}
				else {
					$this->error('Unknown or missing compression type (' . $this->_compress_type . ')');
				}

			}
		}
		return true;
	}

	function readBlock() {
		$v_block = null;
		if (is_resource($this->_file)) {
			if ($this->_compress_type == 'gz') {
				$v_block = @gzread($this->_file, 512);
			} elseif ($this->_compress_type == 'bz2') {
				$v_block = @bzread($this->_file, 512);
			}
			elseif ($this->_compress_type == 'none') {
				$v_block = @fread($this->_file, 512);
			}
			else {
				$this->error('Unknown or missing compression type (' . $this->_compress_type . ')');
			}
		}
		return $v_block;
	}

	function jumpBlock($p_len = null) {
		if (is_resource($this->_file)) {
			if ($p_len === null) {
				$p_len = 1;
			}

			if ($this->_compress_type == 'gz') {
				@gzseek($this->_file, gztell($this->_file) + ($p_len * 512));
			} elseif ($this->_compress_type == 'bz2') {
				// ----- Replace missing bztell() and bzseek()
				for ($i = 0; $i < $p_len; $i++)
					$this->readBlock();
			} elseif ($this->_compress_type == 'none') {
				@fseek($this->_file, $p_len * 512, SEEK_CUR);
			}
			else {
				$this->error('Unknown or missing compression type (' . $this->_compress_type . ')');
			}

		}
		return true;
	}

	function writeFooter() {
		if (is_resource($this->_file)) {
			// ----- Write the last 0 filled block for end of archive
			$v_binary_data = pack('a1024', '');
			$this->writeBlock($v_binary_data);
		}
		return true;
	}

	function addList($p_list, $p_add_dir, $p_remove_dir) {
		$v_result = true;
		$v_header = array();

		// ----- Remove potential windows directory separator
		$p_add_dir = $this->translateWinPath($p_add_dir);
		$p_remove_dir = $this->translateWinPath($p_remove_dir, false);

		if (!$this->_file) {
			$this->error('Invalid file descriptor');
			return false;
		}

		if (sizeof($p_list) == 0) {
			return true;
		}

		foreach ($p_list as $v_filename) {
			if (!$v_result) {
				break;
			}

			// ----- Skip the current tar name
			if ($v_filename == $this->_tarname) {
				continue;
			}

			if ($v_filename == '') {
				continue;
			}

			// ----- ignore files and directories matching the ignore regular expression
			if ($this->_ignore_regexp && preg_match($this->_ignore_regexp, '/' . $v_filename)) {
				$this->warning("File '$v_filename' ignored");
				continue;
			}

			if (!file_exists($v_filename)) {
				$this->warning("File '$v_filename' does not exist");
				continue;
			}

			// ----- Add the file or directory header
			if (!$this->addFile($v_filename, $v_header, $p_add_dir, $p_remove_dir)) {
				return false;
			}

			if (@is_dir($v_filename) && !@is_link($v_filename)) {
				if (!($p_hdir = opendir($v_filename))) {
					$this->warning("Directory '$v_filename' can not be read");
					continue;
				}
				while (false !== ($p_hitem = readdir($p_hdir))) {
					if (($p_hitem != '.') && ($p_hitem != '..')) {
						if ($v_filename != ".") {
							$p_temp_list[0] = $v_filename . '/' . $p_hitem;
						} else {
							$p_temp_list[0] = $p_hitem;
						}

						$v_result = $this->addList($p_temp_list,
							$p_add_dir,
							$p_remove_dir);
					}
				}

				unset($p_temp_list);
				unset($p_hdir);
				unset($p_hitem);
			}
		}

		return $v_result;
	}

	function addFile($p_filename, &$p_header, $p_add_dir, $p_remove_dir) {
		if (!$this->_file) {
			$this->error('Invalid file descriptor');
			return false;
		}

		if ($p_filename == '') {
			$this->error('Invalid file name');
			return false;
		}

		// ----- Calculate the stored filename
		$p_filename = $this->translateWinPath($p_filename, false);

		$v_stored_filename = $p_filename;
		if (strcmp($p_filename, $p_remove_dir) == 0) {
			return true;
		}
		if ($p_remove_dir != '') {
			if (substr($p_remove_dir, -1) != '/') {
				$p_remove_dir .= '/';
			}

			if (substr($p_filename, 0, strlen($p_remove_dir)) == $p_remove_dir) {
				$v_stored_filename = substr($p_filename, strlen($p_remove_dir));
			}
		}
		$v_stored_filename = $this->translateWinPath($v_stored_filename);
		if ($p_add_dir != '') {
			if (substr($p_add_dir, -1) == '/') {
				$v_stored_filename = $p_add_dir . $v_stored_filename;
			} else {
				$v_stored_filename = $p_add_dir . '/' . $v_stored_filename;
			}
		}

		$v_stored_filename = $this->pathReduction($v_stored_filename);

		if ($this->isArchive($p_filename)) {
			if (($v_file = @fopen($p_filename, "rb")) == 0) {
				$this->warning("Unable to open file '" . $p_filename . "' in binary read mode");
				return true;
			}

			if (!$this->writeHeader($p_filename, $v_stored_filename)) {
				return false;
			}

			while (($v_buffer = fread($v_file, 512)) != '') {
				$v_binary_data = pack("a512", "$v_buffer");
				$this->writeBlock($v_binary_data);
			}

			fclose($v_file);

		} else {
			// ----- Only header for dir
			if (!$this->writeHeader($p_filename, $v_stored_filename)) {
				return false;
			}
		}

		return true;
	}

	function addStringInternal($p_filename, $p_string) {
		if (!$this->_file) {
			$this->error('Invalid file descriptor');
			return false;
		}

		if ($p_filename == '') {
			$this->error('Invalid file name');
			return false;
		}

		// ----- Calculate the stored filename
		$p_filename = $this->translateWinPath($p_filename, false);

		if (!$this->writeHeaderBlock($p_filename, strlen($p_string), time(), 384, "", 0, 0)) {
			return false;
		}

		$i = 0;
		while (($v_buffer = substr($p_string, (($i++) * 512), 512)) != '') {
			$v_binary_data = pack("a512", $v_buffer);
			$this->writeBlock($v_binary_data);
		}

		return true;
	}

	function writeHeader($p_filename, $p_stored_filename) {
		if ($p_stored_filename == '') {
			$p_stored_filename = $p_filename;
		}
		$v_reduce_filename = $this->pathReduction($p_stored_filename);

		if (strlen($v_reduce_filename) > 99) {
			if (!$this->writeLongHeader($v_reduce_filename)) {
				return false;
			}
		}

		$v_info = lstat($p_filename);
		$v_uid = sprintf("%07s", DecOct($v_info[4]));
		$v_gid = sprintf("%07s", DecOct($v_info[5]));
		$v_perms = sprintf("%07s", DecOct($v_info['mode'] & 000777));

		$v_mtime = sprintf("%011s", DecOct($v_info['mtime']));

		$v_linkname = '';

		if (@is_link($p_filename)) {
			$v_typeflag = '2';
			$v_linkname = readlink($p_filename);
			$v_size = sprintf("%011s", DecOct(0));
		} elseif (@is_dir($p_filename)) {
			$v_typeflag = "5";
			$v_size = sprintf("%011s", DecOct(0));
		} else {
			$v_typeflag = '0';
			clearstatcache();
			$v_size = sprintf("%011s", DecOct($v_info['size']));
		}

		$v_magic = 'ustar ';

		$v_version = ' ';

		if (function_exists('posix_getpwuid')) {
			$userinfo = posix_getpwuid($v_info[4]);
			$groupinfo = posix_getgrgid($v_info[5]);

			$v_uname = $userinfo['name'];
			$v_gname = $groupinfo['name'];
		} else {
			$v_uname = '';
			$v_gname = '';
		}

		$v_devmajor = '';

		$v_devminor = '';

		$v_prefix = '';

		$v_binary_data_first = pack("a100a8a8a8a12a12",
			$v_reduce_filename, $v_perms, $v_uid,
			$v_gid, $v_size, $v_mtime);
		$v_binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12",
			$v_typeflag, $v_linkname, $v_magic,
			$v_version, $v_uname, $v_gname,
			$v_devmajor, $v_devminor, $v_prefix, '');

		// ----- Calculate the checksum
		$v_checksum = 0;
		// ..... First part of the header
		for ($i = 0; $i < 148; $i++)
			$v_checksum += ord(substr($v_binary_data_first, $i, 1));
		// ..... Ignore the checksum value and replace it by ' ' (space)
		for ($i = 148; $i < 156; $i++)
			$v_checksum += ord(' ');
		// ..... Last part of the header
		for ($i = 156, $j = 0; $i < 512; $i++, $j++)
			$v_checksum += ord(substr($v_binary_data_last, $j, 1));

		// ----- Write the first 148 bytes of the header in the archive
		$this->writeBlock($v_binary_data_first, 148);

		// ----- Write the calculated checksum
		$v_checksum = sprintf("%06s ", DecOct($v_checksum));
		$v_binary_data = pack("a8", $v_checksum);
		$this->writeBlock($v_binary_data, 8);

		// ----- Write the last 356 bytes of the header in the archive
		$this->writeBlock($v_binary_data_last, 356);

		return true;
	}

	function writeHeaderBlock($p_filename, $p_size, $p_mtime = 0, $p_perms = 0, $p_type = '', $p_uid = 0, $p_gid = 0) {
		$p_filename = $this->pathReduction($p_filename);

		if (strlen($p_filename) > 99) {
			if (!$this->writeLongHeader($p_filename)) {
				return false;
			}
		}

		if ($p_type == "5") {
			$v_size = sprintf("%011s", DecOct(0));
		} else {
			$v_size = sprintf("%011s", DecOct($p_size));
		}

		$v_uid = sprintf("%07s", DecOct($p_uid));
		$v_gid = sprintf("%07s", DecOct($p_gid));
		$v_perms = sprintf("%07s", DecOct($p_perms & 000777));

		$v_mtime = sprintf("%11s", DecOct($p_mtime));

		$v_linkname = '';

		$v_magic = 'ustar ';

		$v_version = ' ';

		if (function_exists('posix_getpwuid')) {
			$userinfo = posix_getpwuid($p_uid);
			$groupinfo = posix_getgrgid($p_gid);

			$v_uname = $userinfo['name'];
			$v_gname = $groupinfo['name'];
		} else {
			$v_uname = '';
			$v_gname = '';
		}

		$v_devmajor = '';

		$v_devminor = '';

		$v_prefix = '';

		$v_binary_data_first = pack("a100a8a8a8a12A12",
			$p_filename, $v_perms, $v_uid, $v_gid,
			$v_size, $v_mtime);
		$v_binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12",
			$p_type, $v_linkname, $v_magic,
			$v_version, $v_uname, $v_gname,
			$v_devmajor, $v_devminor, $v_prefix, '');

		// ----- Calculate the checksum
		$v_checksum = 0;
		// ..... First part of the header
		for ($i = 0; $i < 148; $i++)
			$v_checksum += ord(substr($v_binary_data_first, $i, 1));
		// ..... Ignore the checksum value and replace it by ' ' (space)
		for ($i = 148; $i < 156; $i++)
			$v_checksum += ord(' ');
		// ..... Last part of the header
		for ($i = 156, $j = 0; $i < 512; $i++, $j++)
			$v_checksum += ord(substr($v_binary_data_last, $j, 1));

		// ----- Write the first 148 bytes of the header in the archive
		$this->writeBlock($v_binary_data_first, 148);

		// ----- Write the calculated checksum
		$v_checksum = sprintf("%06s ", DecOct($v_checksum));
		$v_binary_data = pack("a8", $v_checksum);
		$this->writeBlock($v_binary_data, 8);

		// ----- Write the last 356 bytes of the header in the archive
		$this->writeBlock($v_binary_data_last, 356);

		return true;
	}

	function writeLongHeader($p_filename) {
		$v_size = sprintf("%11s ", DecOct(strlen($p_filename)));

		$v_typeflag = 'L';

		$v_linkname = '';

		$v_magic = '';

		$v_version = '';

		$v_uname = '';

		$v_gname = '';

		$v_devmajor = '';

		$v_devminor = '';

		$v_prefix = '';

		$v_binary_data_first = pack("a100a8a8a8a12a12", '././@LongLink', 0, 0, 0, $v_size, 0);
		$v_binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12",
			$v_typeflag, $v_linkname, $v_magic,
			$v_version, $v_uname, $v_gname,
			$v_devmajor, $v_devminor, $v_prefix, '');

		// ----- Calculate the checksum
		$v_checksum = 0;
		// ..... First part of the header
		for ($i = 0; $i < 148; $i++)
			$v_checksum += ord(substr($v_binary_data_first, $i, 1));
		// ..... Ignore the checksum value and replace it by ' ' (space)
		for ($i = 148; $i < 156; $i++)
			$v_checksum += ord(' ');
		// ..... Last part of the header
		for ($i = 156, $j = 0; $i < 512; $i++, $j++)
			$v_checksum += ord(substr($v_binary_data_last, $j, 1));

		// ----- Write the first 148 bytes of the header in the archive
		$this->writeBlock($v_binary_data_first, 148);

		// ----- Write the calculated checksum
		$v_checksum = sprintf("%06s ", DecOct($v_checksum));
		$v_binary_data = pack("a8", $v_checksum);
		$this->writeBlock($v_binary_data, 8);

		// ----- Write the last 356 bytes of the header in the archive
		$this->writeBlock($v_binary_data_last, 356);

		// ----- Write the filename as content of the block
		$i = 0;
		while (($v_buffer = substr($p_filename, (($i++) * 512), 512)) != '') {
			$v_binary_data = pack("a512", "$v_buffer");
			$this->writeBlock($v_binary_data);
		}

		return true;
	}

	function readHeader($v_binary_data, &$v_header) {
		if (strlen($v_binary_data) == 0) {
			$v_header['filename'] = '';
			return true;
		}

		if (strlen($v_binary_data) != 512) {
			$v_header['filename'] = '';
			$this->error('Invalid block size : ' . strlen($v_binary_data));
			return false;
		}

		if (!is_array($v_header)) {
			$v_header = array();
		}
		// ----- Calculate the checksum
		$v_checksum = 0;
		// ..... First part of the header
		for ($i = 0; $i < 148; $i++)
			$v_checksum += ord(substr($v_binary_data, $i, 1));
		// ..... Ignore the checksum value and replace it by ' ' (space)
		for ($i = 148; $i < 156; $i++)
			$v_checksum += ord(' ');
		// ..... Last part of the header
		for ($i = 156; $i < 512; $i++)
			$v_checksum += ord(substr($v_binary_data, $i, 1));

		$v_data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/" . "a8checksum/a1typeflag/a100link/a6magic/a2version/" . "a32uname/a32gname/a8devmajor/a8devminor", $v_binary_data);

		// ----- Extract the checksum
		$v_header['checksum'] = OctDec(trim($v_data['checksum']));
		if ($v_header['checksum'] != $v_checksum) {
			$v_header['filename'] = '';

			// ----- Look for last block (empty block)
			if (($v_checksum == 256) && ($v_header['checksum'] == 0)) {
				return true;
			}

			$this->error('Invalid checksum for file "' . $v_data['filename'] . '" : ' . $v_checksum . ' calculated, ' . $v_header['checksum'] . ' expected');
			return false;
		}

		// ----- Extract the properties
		$v_header['filename'] = $v_data['filename'];
		if ($this->maliciousFilename($v_header['filename'])) {
			$this->error('Malicious .tar detected, file "' . $v_header['filename'] . '" will not install in desired directory tree');
			return false;
		}
		$v_header['mode'] = OctDec(trim($v_data['mode']));
		$v_header['uid'] = OctDec(trim($v_data['uid']));
		$v_header['gid'] = OctDec(trim($v_data['gid']));
		$v_header['size'] = OctDec(trim($v_data['size']));
		$v_header['mtime'] = OctDec(trim($v_data['mtime']));
		if (($v_header['typeflag'] = $v_data['typeflag']) == "5") {
			$v_header['size'] = 0;
		}
		$v_header['link'] = trim($v_data['link']);
		/* ----- All these fields are removed form the header because
		 they do not carry interesting info
		$v_header[magic] = trim($v_data[magic]);
		$v_header[version] = trim($v_data[version]);
		$v_header[uname] = trim($v_data[uname]);
		$v_header[gname] = trim($v_data[gname]);
		$v_header[devmajor] = trim($v_data[devmajor]);
		$v_header[devminor] = trim($v_data[devminor]);
		*/

		return true;
	}

	/**
	 * Detect and report a malicious file name
	 *
	 * @param string $file
	 * @return bool
	 * @access private
	 */
	function maliciousFilename($file) {
		if (strpos($file, '/../') !== false) {
			return true;
		}
		if (strpos($file, '../') === 0) {
			return true;
		}
		return false;
	}

	function readLongHeader(&$v_header) {
		$v_filename = '';
		$n = floor($v_header['size'] / 512);
		for ($i = 0; $i < $n; $i++) {
			$v_content = $this->readBlock();
			$v_filename .= $v_content;
		}
		if (($v_header['size'] % 512) != 0) {
			$v_content = $this->readBlock();
			$v_filename .= trim($v_content);
		}

		// ----- Read the next header
		$v_binary_data = $this->readBlock();

		if (!$this->readHeader($v_binary_data, $v_header)) {
			return false;
		}

		$v_filename = trim($v_filename);
		$v_header['filename'] = $v_filename;
		if ($this->maliciousFilename($v_filename)) {
			$this->error('Malicious .tar detected, file "' . $v_filename . '" will not install in desired directory tree');
			return false;
		}

		return true;
	}

	/**
	 * This method extract from the archive one file identified by $p_filename.
	 * The return value is a string with the file content, or NULL on error.
	 * @param string $p_filename     The path of the file to extract in a string.
	 * @return                       a string with the file content or NULL.
	 * @access private
	 */
	function extractInStringInternal($p_filename) {
		$v_result_str = "";

		While (strlen($v_binary_data = $this->readBlock()) != 0) {
			if (!$this->readHeader($v_binary_data, $v_header)) {
				return NULL;
			}

			if ($v_header['filename'] == '') {
				continue;
			}

			// ----- Look for long filename
			if ($v_header['typeflag'] == 'L') {
				if (!$this->readLongHeader($v_header)) {
					return NULL;
				}
			}

			if ($v_header['filename'] == $p_filename) {
				if ($v_header['typeflag'] == "5") {
					$this->error('Unable to extract in string a directory ' . 'entry {' . $v_header['filename'] . '}');
					return NULL;
				} else {
					$n = floor($v_header['size'] / 512);
					for ($i = 0; $i < $n; $i++) {
						$v_result_str .= $this->readBlock();
					}
					if (($v_header['size'] % 512) != 0) {
						$v_content = $this->readBlock();
						$v_result_str .= substr($v_content, 0,
							($v_header['size'] % 512));
					}
					return $v_result_str;
				}
			} else {
				$this->jumpBlock(ceil(($v_header['size'] / 512)));
			}
		}

		return NULL;
	}

	function extractListInternal($p_path, &$p_list_detail, $p_mode, $p_file_list, $p_remove_path) {
		$v_result = true;
		$v_nb = 0;
		$v_extract_all = true;
		$v_listing = false;

		$p_path = $this->translateWinPath($p_path, false);
		if ($p_path == '' || (substr($p_path, 0, 1) != '/' && substr($p_path, 0, 3) != "../" && !strpos($p_path, ':'))) {
			$p_path = "./" . $p_path;
		}
		$p_remove_path = $this->translateWinPath($p_remove_path);

		// ----- Look for path to remove format (should end by /)
		if (($p_remove_path != '') && (substr($p_remove_path, -1) != '/')) {
			$p_remove_path .= '/';
		}
		$p_remove_path_size = strlen($p_remove_path);

		switch ($p_mode) {
			case "complete" :
				$v_extract_all = TRUE;
				$v_listing = FALSE;
				break;
			case "partial" :
				$v_extract_all = FALSE;
				$v_listing = FALSE;
				break;
			case "list" :
				$v_extract_all = FALSE;
				$v_listing = TRUE;
				break;
			default :
				$this->error('Invalid extract mode (' . $p_mode . ')');
				return false;
		}

		clearstatcache();

		while (strlen($v_binary_data = $this->readBlock()) != 0) {
			$v_extract_file = FALSE;
			$v_extraction_stopped = 0;

			if (!$this->readHeader($v_binary_data, $v_header)) {
				return false;
			}

			if ($v_header['filename'] == '') {
				continue;
			}

			// ----- Look for long filename
			if ($v_header['typeflag'] == 'L') {
				if (!$this->readLongHeader($v_header)) {
					return false;
				}
			}

			if ((!$v_extract_all) && (is_array($p_file_list))) {
				// ----- By default no unzip if the file is not found
				$v_extract_file = false;

				for ($i = 0; $i < sizeof($p_file_list); $i++) {
					// ----- Look if it is a directory
					if (substr($p_file_list[$i], -1) == '/') {
						// ----- Look if the directory is in the filename path
						if ((strlen($v_header['filename']) > strlen($p_file_list[$i]))
							&& (substr($v_header['filename'], 0, strlen($p_file_list[$i]))
								== $p_file_list[$i])
						) {
							$v_extract_file = TRUE;
							break;
						}
					} elseif ($p_file_list[$i] == $v_header['filename']) {
						// ----- It is a file, so compare the file names
						$v_extract_file = TRUE;
						break;
					}
				}
			} else {
				$v_extract_file = TRUE;
			}

			// ----- Look if this file need to be extracted
			if (($v_extract_file) && (!$v_listing)) {
				if (($p_remove_path != '') && (substr($v_header['filename'], 0, $p_remove_path_size) == $p_remove_path)) {
					$v_header['filename'] = substr($v_header['filename'], $p_remove_path_size);
				}
				if (($p_path != './') && ($p_path != '/')) {
					while (substr($p_path, -1) == '/')
						$p_path = substr($p_path, 0, strlen($p_path) - 1);

					if (substr($v_header['filename'], 0, 1) == '/') {
						$v_header['filename'] = $p_path . $v_header['filename'];
					} else {
						$v_header['filename'] = $p_path . '/' . $v_header['filename'];
					}
				}
				if (file_exists($v_header['filename'])) {
					if ((@is_dir($v_header['filename'])) && ($v_header['typeflag'] == '')) {
						$this->error('File ' . $v_header['filename'] . ' already exists as a directory');
						return false;
					}
					if (($this->isArchive($v_header['filename'])) && ($v_header['typeflag'] == "5")) {
						$this->error('Directory ' . $v_header['filename'] . ' already exists as a file');
						return false;
					}
					if (!is_writeable($v_header['filename'])) {
						$this->error('File ' . $v_header['filename'] . ' already exists and is write protected');
						return false;
					}
					if (filemtime($v_header['filename']) > $v_header['mtime']) {
						// To be completed : An error or silent no replace ?
					}
				} elseif (($v_result = $this->dirCheck(($v_header['typeflag'] == "5" ? $v_header['filename'] : dirname($v_header['filename'])))) != 1) {
					// ----- Check the directory availability and create it if necessary
					$this->error('Unable to create path for ' . $v_header['filename']);
					return false;
				}

				if ($v_extract_file) {
					if ($v_header['typeflag'] == "5") {
						if (!@file_exists($v_header['filename'])) {
							if (!@mkdir($v_header['filename'], 0777)) {
								$this->error('Unable to create directory {' . $v_header['filename'] . '}');
								return false;
							}
						}
					} elseif ($v_header['typeflag'] == "2") {
						if (@file_exists($v_header['filename'])) {
							@unlink($v_header['filename']);
						}
						if (!@symlink($v_header['link'], $v_header['filename'])) {
							$this->error('Unable to extract symbolic link {' . $v_header['filename'] . '}');
							return false;
						}
					} else {
						// Check for gzipped data
						if (substr($v_header['filename'], -3) == '.GZ') {
							$isGZipped = true;
							$realFileName = substr($v_header['filename'], 0, strlen($v_header['filename']) - 3);
						} else {
							$isGZipped = false;
							$realFileName = $v_header['filename'];
						}
						if (($v_dest_file = @fopen($realFileName, "wb")) == 0) {
							$this->error('Error while opening {' . $realFileName . '} in write binary mode');
							return false;
						} else {
							$n = floor($v_header['size'] / 512);
							$outputData = '';
							$outputDataLength = 0;
							for ($i = 0; $i < $n; $i++) {
								$v_content = $this->readBlock();
								//fwrite($v_dest_file, $v_content, 512);
								$outputData .= $v_content;
								$outputDataLength += 512;
							}
							if (($v_header['size'] % 512) != 0) {
								$v_content = $this->readBlock();
								//fwrite($v_dest_file, $v_content, ($v_header['size'] % 512));
								$outputData .= $v_content;
								$outputDataLength += ($v_header['size'] % 512);
							}
							if ($isGZipped) {
								$outputData = $this->decompress($outputData);
								fwrite($v_dest_file, $outputData);
							} else {
								fwrite($v_dest_file, $outputData, $outputDataLength);
							}
							@fclose($v_dest_file);

							// ----- Change the file mode, mtime
							@touch($realFileName, $v_header['mtime']);
							if ($v_header['mode'] & 0111) {
								// make file executable, obey umask
								$mode = fileperms($realFileName) | (~umask() & 0111);
								@chmod($realFileName, $mode);
							}
						}

						// ----- Check the file size
						clearstatcache();
						if (!$isGZipped && (filesize($realFileName) != $v_header['size'])) {
							$this->error('Extracted file ' . $realFileName . ' does not have the correct file size \'' . filesize($realFileName)
								. '\' (' . $v_header['size'] . ' expected). Archive may be corrupted.');
							return false;
						}
					}
				} else {
					$this->jumpBlock(ceil(($v_header['size'] / 512)));
				}
			} else {
				$this->jumpBlock(ceil(($v_header['size'] / 512)));
			}

			if ($v_listing || $v_extract_file || $v_extraction_stopped) {
				// ----- Log extracted files
				if (($v_file_dir = dirname($v_header['filename'])) == $v_header['filename']) {
					$v_file_dir = '';
				}
				if ((substr($v_header['filename'], 0, 1) == '/') && ($v_file_dir == '')) {
					$v_file_dir = '/';
				}

				$p_list_detail[$v_nb++] = $v_header;
				if (is_array($p_file_list) && (count($p_list_detail) == count($p_file_list))) {
					return true;
				}
			}
		}

		return true;
	}

	function openAppend() {
		if (filesize($this->_tarname) == 0) {
			return $this->openWrite();
		}

		if ($this->_compress) {
			$this->close();

			if (!@rename($this->_tarname, $this->_tarname . ".tmp")) {
				$this->error('Error while renaming \'' . $this->_tarname . '\' to temporary file \'' . $this->_tarname . '.tmp\'');
				return false;
			}

			if ($this->_compress_type == 'gz') {
				$v_temp_tar = @gzopen($this->_tarname . ".tmp", "rb");
			} elseif ($this->_compress_type == 'bz2') {
				$v_temp_tar = @bzopen($this->_tarname . ".tmp", "r");
			}

			if ($v_temp_tar == 0) {
				$this->error('Unable to open file \'' . $this->_tarname . '.tmp\' in binary read mode');
				@rename($this->_tarname . ".tmp", $this->_tarname);
				return false;
			}

			if (!$this->openWrite()) {
				@rename($this->_tarname . ".tmp", $this->_tarname);
				return false;
			}

			if ($this->_compress_type == 'gz') {
				while (!@gzeof($v_temp_tar)) {
					$v_buffer = @gzread($v_temp_tar, 512);
					if ($v_buffer == ARCHIVE_TAR_END_BLOCK) {
						// do not copy end blocks, we will re-make them
						// after appending
						continue;
					}
					$v_binary_data = pack("a512", $v_buffer);
					$this->writeBlock($v_binary_data);
				}

				@gzclose($v_temp_tar);
			} elseif ($this->_compress_type == 'bz2') {
				while (strlen($v_buffer = @bzread($v_temp_tar, 512)) > 0) {
					if ($v_buffer == ARCHIVE_TAR_END_BLOCK) {
						continue;
					}
					$v_binary_data = pack("a512", $v_buffer);
					$this->writeBlock($v_binary_data);
				}

				@bzclose($v_temp_tar);
			}

			if (!@unlink($this->_tarname . ".tmp")) {
				$this->error('Error while deleting temporary file \'' . $this->_tarname . '.tmp\'');
			}

		} else {
			// ----- For not compressed tar, just add files before the last
			//       one or two 512 bytes block
			if (!$this->openReadWrite()) {
				return false;
			}

			clearstatcache();
			$v_size = filesize($this->_tarname);

			// We might have zero, one or two end blocks.
			// The standard is two, but we should try to handle
			// other cases.
			fseek($this->_file, $v_size - 1024);
			if (fread($this->_file, 512) == ARCHIVE_TAR_END_BLOCK) {
				fseek($this->_file, $v_size - 1024);
			} elseif (fread($this->_file, 512) == ARCHIVE_TAR_END_BLOCK) {
				fseek($this->_file, $v_size - 512);
			}
		}

		return true;
	}

	function append($p_filelist, $p_add_dir = '', $p_remove_dir = '') {
		if (!$this->openAppend()) {
			return false;
		}

		if ($this->addList($p_filelist, $p_add_dir, $p_remove_dir)) {
			$this->writeFooter();
		}

		$this->close();

		return true;
	}

	/**
	 * Check if a directory exists and create it (including parent
	 * dirs) if not.
	 *
	 * @param string $p_dir directory to check
	 *
	 * @return bool TRUE if the directory exists or was created
	 */
	function dirCheck($p_dir) {
		clearstatcache();
		if ((@is_dir($p_dir)) || ($p_dir == '')) {
			return true;
		}

		$p_parent_dir = dirname($p_dir);

		if (($p_parent_dir != $p_dir) && ($p_parent_dir != '') && (!$this->dirCheck($p_parent_dir))) {
			return false;
		}

		if (!@mkdir($p_dir, 0777)) {
			$this->error("Unable to create directory '$p_dir'");
			return false;
		}

		return true;
	}


	/**
	 * Compress path by changing for example "/dir/foo/../bar" to "/dir/bar",
	 * rand emove double slashes.
	 *
	 * @param string $p_dir path to reduce
	 *
	 * @return string reduced path
	 *
	 * @access private
	 *
	 */
	function pathReduction($p_dir) {
		$v_result = '';

		// ----- Look for not empty path
		if ($p_dir != '') {
			// ----- Explode path by directory names
			$v_list = explode('/', $p_dir);

			// ----- Study directories from last to first
			for ($i = sizeof($v_list) - 1; $i >= 0; $i--) {
				// ----- Look for current path
				if ($v_list[$i] == ".") {
					// ----- Ignore this directory
					// Should be the first $i=0, but no check is done
				} elseif ($v_list[$i] == "..") {
					// ----- Ignore it and ignore the $i-1
					$i--;
				} elseif (($v_list[$i] == '') && ($i != (sizeof($v_list) - 1)) && ($i != 0)) {
					// ----- Ignore only the double '//' in path,
					// but not the first and last /
				} else {
					$v_result = $v_list[$i] . ($i != (sizeof($v_list) - 1) ? '/' . $v_result : '');
				}
			}
		}
		$v_result = strtr($v_result, '\\', '/');
		return $v_result;
	}

	function translateWinPath($p_path, $p_remove_disk_letter = true) {
		if (defined('OS_WINDOWS') && OS_WINDOWS) {
			// ----- Look for potential disk letter
			if (($p_remove_disk_letter) && (($v_position = strpos($p_path, ':')) != false)) {
				$p_path = substr($p_path, $v_position + 1);
			}
			// ----- Change potential windows directory separator
			if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0, 1) == '\\')) {
				$p_path = strtr($p_path, '\\', '/');
			}
		}
		return $p_path;
	}

	function decompress($compressed) {
		if (false !== ($decompressed = @gzinflate($compressed))) {
			return $decompressed;
		}
		if (false !== ($decompressed = $this->compatibleGzInflate($compressed))) {
			return $decompressed;
		}
		if (false !== ($decompressed = @gzuncompress($compressed))) {
			return $decompressed;
		}
		if (function_exists('gzdecode')) {
			$decompressed = @gzdecode($compressed);
			if (false !== $decompressed) {
				return $decompressed;
			}
		}
		return $compressed;
	}

	function compatibleGzInflate($gzData) {
		if (substr($gzData, 0, 3) == "\x1f\x8b\x08") {
			$i = 10;
			$flg = ord(substr($gzData, 3, 1));
			if ($flg > 0) {
				if ($flg & 4) {
					list($xlen) = unpack('v', substr($gzData, $i, 2));
					$i = $i + 2 + $xlen;
				}
				if ($flg & 8) {
					$i = strpos($gzData, "\0", $i) + 1;
				}
				if ($flg & 16) {
					$i = strpos($gzData, "\0", $i) + 1;
				}
				if ($flg & 2) {
					$i = $i + 2;
				}
			}
			return @gzinflate(substr($gzData, $i, -8));
		} else {
			return false;
		}
	}

}

/// @endcond

?>