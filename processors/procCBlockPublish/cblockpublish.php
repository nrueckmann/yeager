<?php

    // contentblock publishing processor
    class CBlockpublish extends CblockProc {

        public function process ($objectId, $params) {
			$objectId = (int)$objectId;
			$publishVersion = (int)$params['VERSION'];
            $cb = sCblockMgr()->getCblock($objectId);
			if ($cb) {
				$objectInfo = $cb->get();

				if (count($objectInfo) > 0) {
					$cb->publishVersion($publishVersion);

					// Add to history
					if ($publishVersion != ALWAYS_LATEST_APPROVED_VERSION) {
						$cb->history->add( HISTORYTYPE_CO, NULL, $publishVersion, 'TXT_CBLOCK_H_PUBLISH' );
					} else {
						$lastfinalversion = $cb->getLatestApprovedVersion();
						$cb->history->add( HISTORYTYPE_CO, NULL, $lastfinalversion, 'TXT_CBLOCK_H_PUBLISH' );
					}
					return true;
				} else {
					return false;
				}
			}
        }

    }

?>