<?php
	namespace com\yg;

	class ExampleCBListSmall extends \CblockListviewExtension {

		public $info = array(
			"NAME" => "Example cbListView (small)",
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
					'TITLE' => 'Name',				// Title
					'RESIZEABLE' => true,			// 'true' if the column should be resizable
					'WIDTH' => 150,					// Initial width of the column
					'MINWIDTH' => 150,				// Minimum width of the column
					'SORT' => true,					// 'true' when the list should be initially sorted by this column
					'SORTFUNC' => 'sort_NAME'		// Function which is used when the list is sorted by this column (member function of this class)
				),
				array(
					'TITLE' => 'Entrymasks',			
					'RESIZEABLE' => true,				
					'WIDTH' => 200,						
					'MINWIDTH' => 200,						
					'SORTFUNC' => 'sort_ENTRYMASKS'		
				),
				array(
					'TITLE' => 'User',
					'RESIZEABLE' => true,
					'WIDTH' => 100,
					'MINWIDTH' => 30,
					'SORTFUNC' => 'sort_USER'
				)
			),
			'ORDERBY' => 'NAME');
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
					usort($coList, array('com\yg\ExampleCBListSmall', $listColumns['COLUMNS'][$pageDirOrderBy]['SORTFUNC']));
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

					$col1Data[] = array(
						'VALUE01' => $cbInfo['NAME'],
						'FORMFIELD' => 1
					);

					// Add an additional custom column which contains the current user information
					$col3Data = array(
						array(
							'USER_NAME' => trim($userInfo['PROPS']['FIRSTNAME'].' '.$userInfo['PROPS']['LASTNAME']),
							'USER_ID' => $userInfo['ID'],
							'FORMFIELD' => 100
						)
					);

					$col2field = '('.count($coListItem['ENTRYMASKS']).') ';
					for ($c = 0; $c < count($coListItem['ENTRYMASKS']); $c++) {
						
						if ($c != 0) $col2field .= ", ";
						$col2field .= $coListItem['ENTRYMASKS'][$c]['CODE'];

					}

					$col2Data[] = array(
						'VALUE01' => $col2field,
						'FORMFIELD' => 1
					);

					$result[0] = $col1Data;
					$result[1] = $col2Data;
					$result[2] = $col3Data;

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
					//'ENTRYMASKIDENTIFIER' => 'COLISTTEST' // list only contentblocks including an entry mask of type COLISTTEST
				)
			);
		}

		// Callback functions used the sort the different types of columns
		private function sort_NAME($a, $b) {
			return strcmp($a['NAME'], $b['NAME']);
		}

		private function sort_ENTRYMASKS($a, $b) {
			return strcmp(count($a['ENTRYMASKS']), count($b['ENTRYMASKS']));
		}

		private function sort_USER($a, $b) {
			return strcmp($a['CHANGEDBY'], $b['CHANGEDBY']);
		}

	}

?>