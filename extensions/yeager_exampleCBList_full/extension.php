<?php
	namespace com\yg;

	class ExampleCBList extends \CblockListviewExtension {

		public $info = array(
			"NAME" => "Example cbListView (full example)",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"DESCRIPTION" => "Example cbListView Extension (small)",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_CBLOCKLISTVIEW
		);

		public function install () {
			if (parent::install() ) {
				return parent::setInstalled();
			} else {
				return false;
			}
		}

		public function uninstall () {
			if (parent::uninstall() ) {
				return parent::setUnInstalled();
			} else {
				return false;
			}
		}

		public function getListColumns() {
			// Declare listcolumns
			return array(
			'COLUMNS' => array(
				array(
					'TITLE' => 'Single line',			// Title
					'RESIZEABLE' => false,				// 'true' if the column should be resizable
					'WIDTH' => 40,						// Initial width of the column
					'MINWIDTH' => 40,					// Minimum width of the column
					'SORT' => true,						// 'true' when the list should be initially sorted by this column
					'SORTFUNC' => 'sort_SINGLELINE'		// Function which is used when the list is sorted by this column (member function of this class)
				),
				array(
					'TITLE' => 'Textarea',
					'RESIZEABLE' => true,
					'WIDTH' => 100,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_TEXTAREA'
				),
				array(
					'TITLE' => 'Wysiwyg',
					'RESIZEABLE' => true,
					'WIDTH' => 200,
					'MINWIDTH' => 100,
					'SORTFUNC' => 'sort_WYSIWYG'
				),
				array(
					'TITLE' => 'Checkbox',
					'RESIZEABLE' => true,
					'WIDTH' => 50,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_CHECKBOX'
				),
				array(
					'TITLE' => 'Link',
					'RESIZEABLE' => true,
					'WIDTH' => 50,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_LINK'
				),
				array(
				'TITLE' => 'File',
					'RESIZEABLE' => false,
					'WIDTH' => 109,
					'MINWIDTH' => 109,
					'SORTFUNC' => 'sort_FILE'
				),
				array(
					'TITLE' => 'Content Block',
					'RESIZEABLE' => true,
					'WIDTH' => 50,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_CO'
				),
				array(
					'TITLE' => 'Tag',
					'RESIZEABLE' => true,
					'WIDTH' => 50,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_TAG'
				),
				array(
					'TITLE' => 'List',
					'RESIZEABLE' => true,
					'WIDTH' => 50,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_LIST'
				),
				array(
					'TITLE' => 'Password',
					'RESIZEABLE' => true,
					'WIDTH' => 50,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_PASSWORD'
				),
				array(
					'TITLE' => 'Date',
					'RESIZEABLE' => true,
					'WIDTH' => 50,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_DATE'
				),
				array(
					'TITLE' => 'Date &amp; time',
					'RESIZEABLE' => true,
					'WIDTH' => 50,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_DATETIME'
				),
				array(
					'TITLE' => 'Page',
					'RESIZEABLE' => true,
					'WIDTH' => 50,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_PAGE'
				),
				array(
					'TITLE' => 'User',
					'RESIZEABLE' => true,
					'WIDTH' => 50,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_USER'
				)
			),
			'ORDERBY' => 'NAME' );
		}

		public function getCblockListCount($id = 0) {
			// Returns the number of found objects (over all pages)
			$limit = array("FOLDER" => $id);
			return sCblockMgr()->filterEntrymasks(false, $this->getFilter(), $limit, true);
		}

		public function getCblockList($id = 0, $maxlevel = 0, $roleid = 0, $filterArray) {

			if ($filterArray[0]['CBID']) {
				// If a CBID filter is set (this is set when a single line is requested from the frontend
				$limit = array('CBID' => $filterArray[0]['CBID']);
				$coList = sCblockMgr()->filterEntrymasks(false, $this->getFilter(), $limit, false);
			} else {
				// Normal mode, return full list (all children of the specified folder)
				$limit = array('FOLDER' => $id);
				$coList = sCblockMgr()->filterEntrymasks(false, $this->getFilter(), $limit, false);

				// Chop array for pagedirectory
				$pageDirFrom = $filterArray[0]['VALUE'];
				$pageDirCount = $filterArray[0]['VALUE2'];
				if ($pageDirFrom && $pageDirCount) {
					$coList = array_slice( $coList, $pageDirFrom, $pageDirCount);
				}

				// Sort array for pagedirectory
				$pageDirOrderBy = $filterArray[1]['VALUE'];
				$pageDirOrderDir = $filterArray[1]['VALUE2'];
				if ($pageDirOrderBy && $pageDirOrderDir) {
					$listColumns = $this->getListColumns();
					usort($coList, array('com\yg\ExampleCBList', $listColumns['COLUMNS'][$pageDirOrderBy]['SORTFUNC']));
					if ($pageDirOrderDir == -1) {
						$coList = array_reverse($coList);
					}
				}
			}

			// Get additional data for each formfield (and strip folders)
			$finalCoList = array();
			foreach($coList as $coListItem) {
				$lcb = sCblockMgr()->getCBlock($coListItem['CBID']);
				$cbInfo = $lcb->get();

				// Get userinfos from the user who latest modified this object
				$userObj = sUserMgr()->getUser($cbInfo["CHANGEDBY"]);
				if ($userObj) {
					$userInfo = $userObj->get();
					$userProps = $userObj->properties->get();
					$userInfo['PROPS'] = $userProps;				
				}

				if ($coListItem['FOLDER'] == 0) {
					// Get controls
					$coListItem['ENTRYMASKS'] = $lcb->getEntrymasks();

					// Get additional control info
					$col1Data = array();
					$col2Data = array();
					$col3Data = array();
					$col4Data = array();
					$col5Data = array();
					$col6Data = array();
					$col7Data = array();
					$col8Data = array();
					$col9Data = array();
					$col10Data = array();
					$col11Data = array();
					$col12Data = array();
					$col13Data = array();
					$col14Data = array();

					for ($c = 0; $c < count($coListItem['ENTRYMASKS']); $c++) {
						$controlFormfields = $coListItem['ENTRYMASKS'][$c]['FORMFIELDS'];

						foreach ($controlFormfields as $cw) {
							// Distribute the different entrymask-formfields into columns
							if ($cw['IDENTIFIER'] == 'ID_SINGLELINE') {
								$tmpcol1Data = array($cw);
								getAdditionalFormfieldData($tmpcol1Data);
								$tmpcol1Data[0]['OBJECTIDENTIFIER'] = true;
								$col1Data[] = $tmpcol1Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_TEXTAREA') {
								$tmpcol2Data = array($cw);
								getAdditionalFormfieldData($tmpcol2Data);
								$col2Data[] = $tmpcol2Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_WYSIWYG') {
								$tmpcol3Data = array($cw);
								getAdditionalFormfieldData($tmpcol3Data);
								$col3Data[] = $tmpcol3Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_CHECKBOX') {
								$tmpcol4Data = array($cw);
								getAdditionalFormfieldData($tmpcol4Data);
								$col4Data[] = $tmpcol4Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_LINK') {
								$tmpcol5Data = array($cw);
								getAdditionalFormfieldData($tmpcol5Data);
								$col5Data[] = $tmpcol5Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_FILE') {
								$tmpcol6Data = array($cw);
								getAdditionalFormfieldData($tmpcol6Data);

								// For thumb view, get some more data
								$tmpcol6Data[0]['THUMBVIEW'] = true;
								$file = sFileMgr()->getFile($tmpcol6Data[0]['VALUE01']);
								$finalVersion = $file->getLatestApprovedVersion();
								$file = sFileMgr()->getFile($tmpcol6Data[0]['VALUE01'], $finalVersion);
								$views = $file->views->getAssigned(true);
								$viewinfo = $file->views->getGeneratedViewInfo($views[0]['ID']);
								$tmpcol6Data[0]['WIDTH'] = $viewinfo[0]['WIDTH'];
								$tmpcol6Data[0]['HEIGHT'] = $viewinfo[0]['HEIGHT'];
								$tmpcol6Data[0]['THUMB'] = 0;
								$hiddenviews = $file->views->getHiddenViews();
								foreach($hiddenviews as $view) {
									if ($view['IDENTIFIER'] == 'yg-thumb') {
										$tmpviewinfo = $file->views->getGeneratedViewInfo($view['ID']);
										if ($tmpviewinfo[0]['TYPE'] == FILE_TYPE_WEBIMAGE) {
											$tmpcol6Data[0]['THUMB'] = 1;
										}
									}
								}

								$col6Data[] = $tmpcol6Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_CO') {
								$tmpcol7Data = array($cw);
								getAdditionalFormfieldData($tmpcol7Data);
								$col7Data[] = $tmpcol7Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_TAG') {
								$tmpcol8Data = array($cw);
								getAdditionalFormfieldData($tmpcol8Data);
								$col8Data[] = $tmpcol8Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_LIST') {
								$tmpcol9Data = array($cw);
								getAdditionalFormfieldData($tmpcol9Data);
								$col9Data[] = $tmpcol9Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_PASSWORD') {
								$tmpcol10Data = array($cw);
								getAdditionalFormfieldData($tmpcol10Data);
								$col10Data[] = $tmpcol10Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_DATE') {
								$tmpcol11Data = array($cw);
								getAdditionalFormfieldData($tmpcol11Data);
								$col11Data[] = $tmpcol11Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_DATE_TIME') {
								$tmpcol12Data = array($cw);
								getAdditionalFormfieldData($tmpcol12Data);
								$col12Data[] = $tmpcol12Data[0];
							}
							if ($cw['IDENTIFIER'] == 'ID_PAGE') {
								$tmpcol13Data = array($cw);
								getAdditionalFormfieldData($tmpcol13Data);
								$col13Data[] = $tmpcol13Data[0];
							}
						}
					}

					// Add an additional custom column which contains the current user information
					$col14Data = array(
						array(
							'USER_NAME' => trim($userInfo['PROPS']['FIRSTNAME'].' '.$userInfo['PROPS']['LASTNAME']),
							'USER_ID' => $userInfo['ID'],
							'FORMFIELD' => 100
						)
					);

					$result[0] = $col1Data;
					$result[1] = $col2Data;
					$result[2] = $col3Data;
					$result[3] = $col4Data;
					$result[4] = $col5Data;
					$result[5] = $col6Data;
					$result[6] = $col7Data;
					$result[7] = $col8Data;
					$result[8] = $col9Data;
					$result[9] = $col10Data;
					$result[10] = $col11Data;
					$result[11] = $col12Data;
					$result[12] = $col13Data;
					$result[13] = $col14Data;

					// Map everything into an array (represents one line)
					$data = array(
						'CBID'			=> $coListItem['CBID'],
						'CBVERSION'		=> $coListItem['CBVERSION'],
						'NAME'			=> $coListItem['NAME'],
						'HASCHANGED'	=> $coListItem['HASCHANGED'],
						'FIELDS'		=> $result,
						'RREAD'			=> $coListItem['RREAD'],
						'RWRITE'		=> $coListItem['RWRITE'],
						'RDELETE'		=> $coListItem['RDELETE'],
						'RSUB'			=> $coListItem['RSUB'],
						'RSTAGE'		=> $coListItem['RSTAGE'],
						'RMODERATE'		=> $coListItem['RMODERATE'],
						'RCOMMENT'		=> $coListItem['RCOMMENT'],
					);

					array_push($finalCoList, $data);
				}
			}

			// Return the whole list
			return $finalCoList;
		}

		private function getFilter() {
			// Return the filter used for 'filterEntrymasks' (used in several places)
			return array(
				array(
					//'ENTRYMASKIDENTIFIER' => 'COLISTTEST' // list only contentblocks including entrymask of type COLISTTEST
				)
			);
		}

		// Callback functions used the sort the different types of columns
		private function sort_SINGLELINE($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][0]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][0]['VALUE01']);
		}

		private function sort_TEXTAREA($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][1]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][1]['VALUE01']);
		}

		private function sort_WYSIWYG($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][2]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][2]['VALUE01']);
		}

		private function sort_CHECKBOX($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][3]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][3]['VALUE01']);
		}

		private function sort_LINK($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][4]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][4]['VALUE01']);
		}

		private function sort_FILE($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][5]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][5]['VALUE01']);
		}

		private function sort_CO($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][6]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][6]['VALUE01']);
		}

		private function sort_TAG($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][7]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][7]['VALUE01']);
		}

		private function sort_LIST($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][8]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][8]['VALUE01']);
		}

		private function sort_PASSWORD($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][9]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][9]['VALUE01']);
		}

		private function sort_DATE($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][10]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][10]['VALUE01']);
		}

		private function sort_DATETIME($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][11]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][11]['VALUE01']);
		}

		private function sort_PAGE($a, $b) {
			return strcmp($a['ENTRYMASKS'][0]['FORMFIELDS'][12]['VALUE01'], $b['ENTRYMASKS'][0]['FORMFIELDS'][12]['VALUE01']);
		}

		private function sort_USER($a, $b) {
			return strcmp($a['CHANGEDBY'], $b['CHANGEDBY']);
		}

	}

?>