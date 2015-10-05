<?php

$scheduleTimeout = (int)sConfig()->getVar("CONFIG/SCHEDULER_TIMEOUT");
if (!$scheduleTimeout) {
	$scheduleTimeout = 60*60*24; //	(86.400 secs = 1 day)
}

$jsQueue = new JSQueue(NULL);

// Files
$fileMgr = sFileMgr();
$jobs = $fileMgr->scheduler->getPendingJobsAndSetQueued(true, $scheduleTimeout);
$procs = $this->files_procs;
for ($j = 0; $j < count($jobs); $j++) {
	for ($p = 0; $p < count($procs); $p++) {
		if ($procs[$p]["dir"] == $jobs[$j]["ACTIONCODE"]) {
			if (file_exists($this->approot.$this->filesprocdir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php")) {
				require_once($this->approot.$this->filesprocdir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php");
			} elseif (file_exists($this->approot.$this->processordir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php")) {
				require_once($this->approot.$this->processordir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php");
			} else {
				continue;
			}
			sUserMgr()->impersonate($jobs[$j]['USERID']);
			$classname = (string)$procs[$p]["classname"];
			$namespace = (string)$procs[$p]["namespace"];
			if (strlen($namespace)) {
				$classname = $namespace."\\".$classname;
			}
			$moduleclass = new $classname();
			if ($fileMgr->scheduler->pickJob($jobs[$j]["ID"])) {
				if ($moduleclass->process($jobs[$j]["OBJECTID"], $jobs[$j]["PARAMETERS"])) {
					if (Singleton::cache_config()->getVar("CONFIG/INVALIDATEON/FILE_PROCESSVIEW") == "true") {
						Singleton::FC()->emptyBucket();
					}
					if ($jobs[$j]['PARAMETERS']['VIEW']['ID'] && $jobs[$j]['PARAMETERS']['VIEW']['ID'] != "") $jsQueue->add($jobs[$j]['PARAMETERS']['FILEINFO']['OBJECTID'], HISTORYTYPE_FILE, 'FILE_GENERATEDVIEW', sGuiUS(), $jobs[$j]['PARAMETERS']['VIEW']['ID'], $jobs[$j]['PARAMETERS']['VIEW']['IDENTIFIER']);

					$fileMgr->scheduler->finishJob($jobs[$j]["ID"]);
				} else {
					$fileMgr->scheduler->finishJob($jobs[$j]["ID"], SCHEDULER_STATE_FAILED);
				}
			}
		}
	}
}

// Pages (for every site)
sUserMgr()->impersonate((int)sConfig()->getVar("CONFIG/SYSTEMUSERS/ROOTUSERID"));
$siteMgr = new Sites();
$allSites = $siteMgr->getList();
foreach($allSites as $currSite) {
	$pageMgr = new PageMgr($currSite['ID']);
	$jobs = $pageMgr->scheduler->getPendingJobsAndSetQueued(true, $scheduleTimeout);
	$procs = $this->page_procs;
	for ($j = 0; $j < count($jobs); $j++) {
		for ($p = 0; $p < count($procs); $p++) {
			if ($procs[$p]["actioncode"] == $jobs[$j]["ACTIONCODE"]) {
				sUserMgr()->impersonate($jobs[$j]['USERID']);
				$pageMgr = new PageMgr($currSite['ID']);
				if (file_exists($this->approot. $this->pageprocdir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php")) {
					require_once($this->approot. $this->pageprocdir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php");
				} elseif (file_exists($this->approot. $this->processordir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php")) {
					require_once($this->approot. $this->processordir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php");
				} else {
					continue;
				}
				$classname = (string)$procs[$p]["classname"];
				$namespace = (string)$procs[$p]["namespace"];
				if (strlen($namespace)) {
					$classname = $namespace."\\".$classname;
				}
				$moduleclass = new $classname();
				if ($pageMgr->scheduler->pickJob($jobs[$j]["ID"])) {
					if ($moduleclass->process($currSite['ID'], $jobs[$j]["OBJECTID"], $jobs[$j]["PARAMETERS"])) {
						$pageMgr->scheduler->finishJob($jobs[$j]["ID"]);
					} else {
						$pageMgr->scheduler->finishJob($jobs[$j]["ID"], SCHEDULER_STATE_FAILED);
					}
				}
			}
		}
	}
}

// Contentblocks
$jobs = sCblockMgr()->scheduler->getPendingJobsAndSetQueued(true, $scheduleTimeout);
$procs = $this->cblock_procs;
for ($j = 0; $j < count($jobs); $j++) {
	for ($p = 0; $p < count($procs); $p++) {
		if ($procs[$p]["actioncode"] == $jobs[$j]["ACTIONCODE"]) {
			sUserMgr()->impersonate($jobs[$j]['USERID']);
			sCblockMgr()->_uid = sUserMgr()->getCurrentUserID();
			if (file_exists($this->approot. $this->cblockprocdir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php")) {
				require_once($this->approot. $this->cblockprocdir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php");
			} elseif (file_exists($this->approot. $this->processordir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php")) {
				require_once($this->approot. $this->processordir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php");
			} else {
				continue;
			}
			$classname = (string)$procs[$p]["classname"];
			$namespace = (string)$procs[$p]["namespace"];
			if (strlen($namespace)) {
				$classname = $namespace."\\".$classname;
			}
			$moduleclass = new $classname();
			if (sCblockMgr()->scheduler->pickJob($jobs[$j]["ID"])) {
				if ($moduleclass->process($jobs[$j]["OBJECTID"], $jobs[$j]["PARAMETERS"])) {
					sCblockMgr()->scheduler->finishJob($jobs[$j]["ID"]);
				} else {
					sCblockMgr()->scheduler->finishJob($jobs[$j]["ID"], SCHEDULER_STATE_FAILED);
				}
			}
		}
	}
}

// Emails
$mailingMgr = new MailingMgr();
$jobs = $mailingMgr->scheduler->getPendingJobsAndSetQueued(false, $scheduleTimeout);
$procs = $this->email_procs;
for ($j = 0; $j < count($jobs); $j++) {
	for ($p = 0; $p < count($procs); $p++) {
		if ( ($jobs[$j]["ACTIONCODE"] == 'SCH_EMAILSEND') || ($jobs[$j]["ACTIONCODE"] == 'SCH_EMAILCHECKFINISH') ) {
			sUserMgr()->impersonate($jobs[$j]['USERID']);
			if (file_exists($this->approot. $this->emailprocdir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php")) {
				require_once($this->approot. $this->emailprocdir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php");
			} elseif (file_exists($this->approot. $this->processordir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php")) {
				require_once($this->approot. $this->processordir.$procs[$p]["dir"]."/".$procs[$p]["classname"].".php");
			} else {
				continue;
			}
			$classname = (string)$procs[$p]["classname"];
			$namespace = (string)$procs[$p]["namespace"];
			if (strlen($namespace)) {
				$classname = $namespace."\\".$classname;
			}
			$moduleclass = new $classname();
			if ($mailingMgr->scheduler->pickJob($jobs[$j]["ID"])) {
				switch ($jobs[$j]["ACTIONCODE"]) {
					case 'SCH_EMAILSEND':
						if ($moduleclass->process($jobs[$j]["OBJECTID"], $jobs[$j]["PARAMETERS"])) {
							$mailingMgr->scheduler->finishJob($jobs[$j]["ID"]);
						} else {
							$mailingMgr->scheduler->finishJob($jobs[$j]["ID"], SCHEDULER_STATE_FAILED);
						}
						break;
					case 'SCH_EMAILCHECKFINISH':
						if ($moduleclass->checkFinish($jobs[$j]["OBJECTID"], $jobs[$j]["PARAMETERS"])) {
							$mailingMgr->scheduler->finishJob($jobs[$j]["ID"]);
						} else {
							$mailingMgr->scheduler->setJobState($jobs[$j]["ID"], SCHEDULER_STATE_PENDING);
						}
						break;
				}
			}
		}
	}
}

?>