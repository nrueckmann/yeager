<?php
	namespace com\nt;

	class DefaultCblockListView extends \CblockListviewExtension {

		public $info = array(
			"NAME" => "Default CblockListView",
			"DEVELOPERNAME" => "Next Tuesday GmbH",
			"VERSION" => "1.0",
			"API" => "1.0",
			"DESCRIPTION" => "Default CblockListView Extension",
			"URL" => "http://www.yeager.cm/",
			"TYPE" => EXTENSION_CBLOCKLISTVIEW,
			"ASSIGNMENT" => EXTENSION_ASSIGNMENT_USER_CONTROLLED
		);

		public function install () {
			if (parent::install() ) {
				$this->propertySettings->add('TXT_EX_DEFAULT_CONTENTBLOCK', 'DEFAULT_CO', 'CBLOCK');
				return parent::setInstalled();
			} else {
				return false;
			}
		}

		public function uninstall () {
			if (parent::uninstall()) {
				return parent::setUnInstalled();
			} else {
				return false;
			}
		}

		public function getListColumns () {
			$itext = \sItext();
			return array(
			'COLUMNS' => array(
				array(
					'TITLE' => $itext['TXT_NAME'],
					'RESIZEABLE' => true,
					'WIDTH' => 300,
					'MINWIDTH' => 300,
					'SORT' => true,
					'SORTFUNC' => 'sort_NAME'
				),
				array(
					'TITLE' => $itext['TXT_USER'],
					'RESIZEABLE' => true,
					'WIDTH' => 125,
					'MINWIDTH' => 125,
					'SORTFUNC' => 'sort_USER'
				),
				array(
					'TITLE' => $itext['TXT_LAST_CHANGE'],
					'RESIZEABLE' => false,
					'WIDTH' => 125,
					'MINWIDTH' => 125,
					'SORTFUNC' => 'sort_DATE'
				)
			),
			'ORDERBY' => 'NAME' );
		}

		public function getCblockListCount ($id = 0) {
			$limit = array('FOLDER' => $id);

			return \sCblockMgr()->filterEntrymasks(false, $this->getFilter(), $limit, true);
		}

		public function getCblockList ($id = 0, $maxlevel = 0, $roleid = 0, $filterArray) {

			if ($filterArray[0]['CBID']) {
				$limit = array('CBID' => $filterArray[0]['CBID']);
				$coList = \sCblockMgr()->filterEntrymasks(false, $this->getFilter(), $limit, false);
			} else {
				$limit = array('FOLDER' => $id);
				$coList = \sCblockMgr()->filterEntrymasks(false, $this->getFilter(), $limit, false);
			}

			// Get additional data for each formfield (and strip folders)
			$finalCoList = array();
			foreach($coList as $coListItem) {
				if ($coListItem['FOLDER'] == 0) {

					// get last modifier
					$history = \sCblockMgr()->history->getList($coListItem['CBID']);

					if ($allMailingsItem['CHANGEDBY']) {
						$userObj = new \user($history[0]['UID']?$history[0]['UID']:$coListItem['CHANGEDBY']);
					} else {
						$userObj = new \user($history[0]['UID']?$history[0]['UID']:$coListItem['CREATEDBY']);
					}

					$userInfo = $userObj->get();
					$userProps = $userObj->properties->getValues($userInfo['ID']);
					$userInfo['PROPS'] = $userProps;

					// Get controls
					$cb = new \Cblock ($coListItem['CBID']);
					$coListItem['ENTRYMASKS'] = $cb->getEntrymasks();

					// Get additional control info
					$col1Data = array(
						array(
							'CO_NAME' => $coListItem['NAME'],
							'FORMFIELD' => 101,
							'OBJECTIDENTIFIER' => true
						)
					);
					$col2Data = array(
						array(
							'USER_NAME' => trim($userInfo['PROPS']['FIRSTNAME'].' '.$userInfo['PROPS']['LASTNAME']),
							'USER_ID' => $userInfo['ID'],
							'FORMFIELD' => 100
						)
					);
					$col3Data = array(
						array(
							'CHANGEDTS' => TStoLocalTS($coListItem['CHANGEDTS']),
							'FORMFIELD' => 103
						)
					);

					$result[0] = $col1Data;
					$result[1] = $col2Data;
					$result[2] = $col3Data;

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
						'RCOMMENT'		=> $coListItem['RCOMMENT']
					);

					array_push($finalCoList, $data);
				}
			}

			if (!$filterArray[0]['CBID']) {
				$pageDirOrderBy = $filterArray[1]['VALUE'];
				$pageDirOrderDir = $filterArray[1]['VALUE2'];
				if (strlen($pageDirOrderBy) && strlen($pageDirOrderDir)) {
					$listColumns = $this->getListColumns();
					usort($finalCoList, array('com\nt\DefaultCblockListView', $listColumns['COLUMNS'][$pageDirOrderBy]['SORTFUNC']));
					if ($pageDirOrderDir == -1) {
						$finalCoList = array_reverse($finalCoList);
					}
				}

				$pageDirFrom = $filterArray[0]['VALUE'];
				$pageDirCount = $filterArray[0]['VALUE2'];
				if (strlen($pageDirFrom) && strlen($pageDirCount)) {
					$finalCoList = array_slice( $finalCoList, $pageDirFrom, $pageDirCount);
				}
			}

			return $finalCoList;
		}

		private function getFilter () {
			return array();
		}

		private function sort_NAME ($a, $b) {
			return strcmp($a['NAME'], $b['NAME']);
		}

		private function sort_USER ($a, $b) {
			return strcmp($a['FIELDS'][1][0]['USER_NAME'], $b['FIELDS'][1][0]['USER_NAME']);
		}

		private function sort_DATE ($a, $b) {
			return strcmp($a['FIELDS'][2][0]['CHANGEDTS'], $b['FIELDS'][2][0]['CHANGEDTS']);
		}

	}

?>