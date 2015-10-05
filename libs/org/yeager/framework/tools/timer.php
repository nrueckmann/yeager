<?php

namespace framework;

/**
* neptun timer class
*
* @author  Next Tuesday GmbH <office@nexttuesday.de>
*
*/
class Timer {
	var $startTime, $endTime, $timeDifference;

	function start() {
		$this->startTime = $this->currentTime();
	}

	function finish() {
		$this->endTime = $this->currentTime();
	}

	function getTime() {
		$this->timeDifference = $this->currentTime() - $this->startTime;
		return round($this->timeDifference, 5);
	}

	function currentTime() {
		list($usec, $sec) = explode(' ',microtime()); return ((float)$usec + (float)$sec);
	}

} // End Timer class

?>