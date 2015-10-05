<?php

    // page publishing processor
    class PagePublish extends PageProc {

        public function process ($siteId, $objectId, $params) {
			$siteId = (int)$siteId;
			$objectId = (int)$objectId;
			$publishVersion = (int)$params['VERSION'];
			$pageMgr = new PageMgr($siteId);
			$page = $pageMgr->getPage($objectId);
			$objectInfo = $page->get();

			if (count($objectInfo) > 0) {
				$page->publishVersion($publishVersion);

				// Add to history
				if ($publishVersion != ALWAYS_LATEST_APPROVED_VERSION) {
					$page->history->add(HISTORYTYPE_PAGE, NULL, $publishVersion, 'TXT_PAGE_H_AUTOPUBLISH');
				} else {
					$lastfinalversion = $page->getLatestApprovedVersion();
					$page->history->add(HISTORYTYPE_PAGE, NULL, $lastfinalversion, 'TXT_PAGE_H_AUTOPUBLISH');
				}
				return true;
			} else {
				return false;
			}
        }

    }

?>