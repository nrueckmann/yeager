<?php

/**
 * Scheduler types
 */
define("SCHEDULER_PAGE", 1);
define("SCHEDULER_CO", 2);
define("SCHEDULER_FILE", 3);
define("SCHEDULER_MAILING", 4);

/**
 * Scheduler states
 */
define("SCHEDULER_STATE_PENDING", 0);
define("SCHEDULER_STATE_RUNNING", 1);
define("SCHEDULER_STATE_FAILED", 2);
define("SCHEDULER_STATE_PAUSED", 3);
define("SCHEDULER_STATE_QUEUED", 4);

/// @cond DEV

/**
 * @file
 * @author  Next Tuesday GmbH <office@nexttuesday.de>
 * @version 1.0
 *
 */

/**
 * The Scheduler class, which represents an instance of the Scheduler.
 */
class Scheduler extends \framework\Error {
	var $_db;
	var $_table;
	var $_objecttype;
	var $_uid;

	/**
	 * Constructor of the Scheduler class
	 *
	 * @param string $table Database table for the Scheduler instance
	 * @param int $objecttype Object type
	 */
	function __construct($table, $objecttype) {
		$this->_uid = sUserMgr()->getCurrentUserID();
		$objecttype = (int)$objecttype;
		$this->_table = $table;
		$this->_objecttype = $objecttype;
		if (strlen($table) < 2) {
			return false;
		}
	}

	/**
	 * Schedules a new job for the Object
	 *
	 * @param int $objectId Object Id
	 * @param string $actioncode Actioncode for the job
	 * @param int $timestamp Timestamp (when the job was created)
	 * @param array $parameters Parameters for the job
	 * @param int $timeout Timestamp on which the job expires
	 * @return int Scheduler Id
	 * @throws Exception
	 */
	function schedule($objectId, $actioncode, $timestamp, $parameters, $timeout = 172800) {
		$objectId = (int)$objectId;
		$timestamp = (int)$timestamp;
		$timeout = (int)$timeout;
		$actioncode = sYDB()->escape_string(sanitize($actioncode));
		$parameters = serialize($parameters);

		if ($objectId < 1) {
			return false;
		}
		if (strlen($actioncode) < 2) {
			return false;
		}

		$sql = "SELECT * FROM " . $this->_table . " WHERE
			(OBJECTTYPE = ?) AND
			(OBJECTID = ?) AND
			(ACTIONCODE = ?) AND
			(PARAMETERS = ?) AND
			(USERID = ?) AND
			(STATUS = ?);";
		$result = sYDB()->Execute($sql, $this->_objecttype, $objectId, $actioncode, $parameters, $this->_uid, SCHEDULER_STATE_PENDING);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();

		if (count($resultarray) === 0) {
			$sql = "INSERT INTO " . $this->_table . " (ID, OBJECTTYPE, OBJECTID, ACTIONCODE, `TIMESTAMP`, `EXPIRES`, PARAMETERS, USERID, STATUS)
						VALUES
					(NULL, ?, ?, ?, ?, ?, ?, ?, ?)";
			$result = sYDB()->Execute($sql, $this->_objecttype, $objectId, $actioncode, $timestamp, ($timestamp + $timeout), $parameters, $this->_uid , SCHEDULER_STATE_PENDING);
			if ($result === false) {
				throw new Exception(sYDB()->ErrorMsg());
			}
			$pid = sYDB()->Insert_ID();
			return $pid;
		} else {
			return false;
		}
	}

	/**
	 * Gets scheduled jobs for the Object
	 *
	 * @param int $objectId Object Id
	 * @param string $actioncodeFilter (optional) Returns only jobs which match the filter
	 * @param int $timestamp (optional) Returns only jobs older than the timestamp
	 * @return array List of Scheduler jobs
	 * @throws Exception
	 */
	function getSchedule($objectId, $actioncodeFilter = '', $timestamp = 0) {
		$objectId = (int)$objectId;
		$timestamp = (int)$timestamp;
		$actioncodeFilter = sYDB()->escape_string(sanitize($actioncodeFilter));
		if ($objectId < 1) {
			return false;
		}
		if (strlen($actioncodeFilter) > 2) {
			$filter_sql .= " AND (ACTIONCODE like '%" . $actioncodeFilter . "%')";
		}
		if ($timestamp > 0) {
			$filter_sql .= " AND (TIMESTAMP <= $timestamp)";
		}
		$sql = "SELECT * FROM " . $this->_table . " WHERE (OBJECTTYPE = ?) AND (OBJECTID = ?) $filter_sql ORDER BY `TIMESTAMP` DESC;";
		$result = sYDB()->Execute($sql, $this->_objecttype, $objectId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		for ($i = 0; $i < count($resultarray); $i++) {
			$resultarray[$i]["PARAMETERS"] = unserialize($resultarray[$i]["PARAMETERS"]);
		}
		return $resultarray;
	}

	/**
	 * Gets basic information about the specified job
	 *
	 * @param int $jobId Scheduler job Id
	 * @return array Job information
	 * @throws Exception
	 */
	function get($jobId) {
		$jobId = (int)$jobId;
		$sql = "SELECT * FROM " . $this->_table . " WHERE ID = ?;";
		$result = sYDB()->Execute($sql, $jobId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		if (count($resultarray) > 0) {
			$resultarray[0]["PARAMETERS"] = unserialize($resultarray[0]["PARAMETERS"]);
		}
		return $resultarray[0];
	}

	/**
	 * Gets Scheduler jobs for the Object with a specific status
	 *
	 * @param string $status Job status
	 * @return array List of Scheduler jobs
	 * @throws Exception
	 */
	function getJobs($status) {
		$currentTS = time();
		$status = (int)$status;
		if ($status > 0) {
			$statsql = " AND STATUS = '" . $status . "'";
		}
		$sql = "SELECT * FROM " . $this->_table . " WHERE (OBJECTTYPE = ?) AND (TIMESTAMP <= ?) $statsql ORDER BY `TIMESTAMP` DESC;";
		$result = sYDB()->Execute($sql,  $this->_objecttype, $currentTS);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		for ($i = 0; $i < count($resultarray); $i++) {
			$resultarray[$i]["PARAMETERS"] = unserialize($resultarray[$i]["PARAMETERS"]);
		}
		return $resultarray;
	}

	/**
	 * Gets all pending jobs for the Object
	 *
	 * @param bool $includePausedJobs TRUE if paused Scheduler jobs should also be returned
	 * @return array List of Scheduler jobs
	 * @throws Exception
	 */
	function getPendingJobs($includePausedJobs = false) {
		$currentTS = time();
		if ($includePausedJobs) {
			$sqlPausedJobs = "OR (STATUS = " . SCHEDULER_STATE_PAUSED . ")";
		}
		$sql = "SELECT * FROM " . $this->_table . " WHERE (OBJECTTYPE = ?) AND (TIMESTAMP <= ?) AND ( (STATUS = ?) $sqlPausedJobs ) ORDER BY `TIMESTAMP` DESC;";
		$result = sYDB()->Execute($sql, $this->_objecttype, $currentTS, SCHEDULER_STATE_PENDING);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		for ($i = 0; $i < count($resultarray); $i++) {
			$resultarray[$i]["PARAMETERS"] = unserialize($resultarray[$i]["PARAMETERS"]);
		}
		return $resultarray;
	}

	/**
	 * Removes the specified Scheduler job and releases the lock
	 *
	 * @param int $jobId Scheduler job Id
	 * @param string $status (optional) Sets the Scheduler job to the specified state
	 */
	function finishJob($jobId, $status = '') {
		$jobId = (int)$jobId;

		if (!$status) {
			$this->removeJob($jobId);
		} else {
			$this->setJobState($jobId, $status);
		}
		$this->releaseLock($jobId);
	}

	/**
	 * Picks a Scheduler job (and sets the job status to "running")
	 *
	 * @param int $jobId
	 * @return bool TRUE on success or FALSE if status is wrong or when the job is already locked
	 */
	function pickJob($jobId) {
		$jobId = (int)$jobId;
		if (!$this->isLocked($jobId)) {
			if ($this->getJobState($jobId) == SCHEDULER_STATE_QUEUED) {
				if ($this->getLock($jobId, 10)) {
					$this->setJobState($jobId, SCHEDULER_STATE_RUNNING);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Gets all "pending" Scheduler jobs and sets them to "queued"
	 *
	 * @param bool $includePausedJobs TRUE if paused Scheduler jobs should also be returned
	 * @param int $timeout Amount of seconds after which the job should be treated as "outdated"
	 * @return array List of Scheduler jobs
	 * @throws Exception
	 */
	function getPendingJobsAndSetQueued($includePausedJobs = false, $timeout) {
		// Transact -> Get Jobs and set QUEUED status,
		// reset expiry for expired but queued jobs
		$timeout = (int)$timeout;

		$currentTS = time();
		if ($includePausedJobs) {
			$sqlPausedJobs = "OR (STATUS = ". SCHEDULER_STATE_PAUSED .")";
		}

		$sql = "SET autocommit=0;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "START TRANSACTION;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "UPDATE " . $this->_table . " SET EXPIRES = ? WHERE (OBJECTTYPE = ?) AND (EXPIRES <= ?) AND ( (STATUS =  ?) $sqlPausedJobs );";
		$result = sYDB()->Execute($sql, ($timeout + $currentTS), $this->_objecttype, $currentTS, SCHEDULER_STATE_QUEUED);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "UPDATE " . $this->_table . " SET STATUS =  ? WHERE (OBJECTTYPE = ?) AND (TIMESTAMP <= ?) AND ( (STATUS = ?) $sqlPausedJobs );";
		$result = sYDB()->Execute($sql, SCHEDULER_STATE_QUEUED, $this->_objecttype, $currentTS, SCHEDULER_STATE_PENDING);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "COMMIT;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}

		$sql = "SELECT * FROM " . $this->_table . " WHERE (OBJECTTYPE = ?) AND (TIMESTAMP <= ?) AND ( (STATUS = ?) $sqlPausedJobs ) ORDER BY `TIMESTAMP` DESC;";
		$result = sYDB()->Execute($sql, $this->_objecttype, $currentTS, SCHEDULER_STATE_QUEUED);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();

		for ($i = 0; $i < count($resultarray); $i++) {
			$resultarray[$i]["PARAMETERS"] = unserialize($resultarray[$i]["PARAMETERS"]);
		}

		return $resultarray;
	}

	/**
	 * Gets all "pending" Scheduler jobs for a specific Object
	 *
	 * @param int $objectId Object Id
	 * @param bool $includePausedJobs (optional) TRUE if paused Scheduler jobs should also be returned
	 * @return array List of Scheduler jobs
	 * @throws Exception
	 */
	function getPendingJobsForObject($objectId, $includePausedJobs = false) {
		$objectId = (int)$objectId;
		$currentTS = time();
		if ($includePausedJobs) {
			$sqlPausedJobs = "OR (STATUS = " . SCHEDULER_STATE_PAUSED . ")";
		}
		$sql = "SELECT * FROM " . $this->_table . " WHERE (OBJECTTYPE = ?) AND (OBJECTID = ?) AND (TIMESTAMP <= ?) AND ( (STATUS = ?) $sqlPausedJobs ) ORDER BY `TIMESTAMP` DESC;";
		$result = sYDB()->Execute($sql, $this->_objecttype, $objectId, $currentTS, SCHEDULER_STATE_PENDING);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		for ($i = 0; $i < count($resultarray); $i++) {
			$resultarray[$i]["PARAMETERS"] = unserialize($resultarray[$i]["PARAMETERS"]);
		}
		return $resultarray;
	}

	/**
	 * Gets all "queued" Scheduler jobs for a specific Object
	 *
	 * @param int $objectId Object Id
	 * @param bool $includePausedJobs (optional) TRUE if paused Scheduler jobs should also be returned
	 * @param string $filterJobType (optional) Filters the jobs by job type
	 * @return array List of Scheduler jobs
	 * @throws Exception
	 */
	function getQueuedJobsForObject($objectId, $includePausedJobs = false, $includeRunningJobs = false, $filterJobType = '') {
		$objectId = (int)$objectId;
		$currentTS = time();
		$sqlJobs = "";
		$filterJobType = sYDB()->escape_string(sanitize($filterJobType));
		if ($includePausedJobs) {
			$sqlJobs .= "OR (STATUS = " . SCHEDULER_STATE_PAUSED . ")";
		}
		if ($includeRunningJobs) {
			$sqlJobs .= "OR (STATUS = " . SCHEDULER_STATE_RUNNING . ")";
		}
		if ($filterJobType != '') {
			$sqlFilterJobType = "AND (ACTIONCODE = '" . $filterJobType . "')";
		}
		$sql = "SELECT * FROM " . $this->_table . " WHERE (OBJECTTYPE = ?) AND (OBJECTID = ?) AND (TIMESTAMP <= ?) AND ( (STATUS = ?) $sqlJobs ) $sqlFilterJobType ORDER BY `TIMESTAMP` DESC;";
		$result = sYDB()->Execute($sql, $this->_objecttype, $objectId, $currentTS, SCHEDULER_STATE_QUEUED);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		for ($i = 0; $i < count($resultarray); $i++) {
			$resultarray[$i]["PARAMETERS"] = unserialize($resultarray[$i]["PARAMETERS"]);
		}
		return $resultarray;
	}

	/**
	 * Pauses all "queued" Scheduler jobs for a specific Object
	 *
	 * @param int $objectId Object Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function pauseAllQueuedJobsForObject($objectId) {
		$objectId = (int)$objectId;
		$currentTS = time();
		$sql = "UPDATE " . $this->_table . " SET STATUS = ? WHERE (OBJECTTYPE = ?) AND (OBJECTID = ?) AND (STATUS = ?) AND (TIMESTAMP <= ?);";
		$result = sYDB()->Execute($sql, SCHEDULER_STATE_PAUSED, $this->_objecttype, $objectId, SCHEDULER_STATE_QUEUED, $currentTS);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Resumes all "queued" Scheduler jobs for a specific Object
	 *
	 * @param int $objectId Object Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function resumeAllQueuedJobsForObject($objectId) {
		$objectId = (int)$objectId;
		$currentTS = time();
		$sql = "UPDATE " . $this->_table . " SET STATUS = ? WHERE (OBJECTTYPE = ?) AND (OBJECTID = ?) AND (STATUS = ?) AND (TIMESTAMP <= ?);";
		$result = sYDB()->Execute($sql, SCHEDULER_STATE_PENDING, $this->_objecttype, $objectId, SCHEDULER_STATE_PAUSED, $currentTS);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Cancels all "queued" Scheduler jobs for a specific Object
	 *
	 * @param int $objectId Object Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function cancelAllQueuedJobsForObject($objectId) {
		$objectId = (int)$objectId;
		$currentTS = time();
		$sql = "DELETE FROM " . $this->_table . " WHERE (OBJECTTYPE = ?) AND (OBJECTID = ?) AND (TIMESTAMP <= ?);";
		$result = sYDB()->Execute($sql, $this->_objecttype, $objectId, $currentTS);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Gets the state of a specfific Job
	 *
	 * @param int $jobId Scheduler job Id
	 * @return int State of Scheduler job
	 * @throws Exception
	 */
	function getJobState($jobId) {
		$jobId = (int)$jobId;
		if ($jobId < 1) {
			return false;
		}

		$sql = "SELECT STATUS FROM " . $this->_table . " WHERE ID = ?;";
		$result = sYDB()->Execute($sql, $jobId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		return $resultarray[0]['STATUS'];
	}

	/**
	 * Sets the state of a specfific Job
	 *
	 * @param int $jobId Scheduler job Id
	 * @param int $state Scheduler job state
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function setJobState($jobId, $state) {

		$jobId = (int)$jobId;
		$state = (int)$state;
		if ($jobId < 1) {
			return false;
		}

		$sql = "UPDATE " . $this->_table . " SET STATUS = ? WHERE ID = ?;";
		$result = sYDB()->Execute($sql, $state, $jobId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Updates the action of a specific Scheduler job
	 *
	 * @param int $jobId Scheduler job Id
	 * @param string $actioncode Scheduler action code
	 * @param int $timestamp Timestamp (when the job was created)
	 * @param array $parameters Parameters for the job
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function updateAction($jobId, $actioncode, $timestamp, $parameters) {

		$jobId = (int)$jobId;
		$timestamp = (int)$timestamp;
		$actioncode = sYDB()->escape_string(sanitize($actioncode));
		$parameters = serialize($parameters);

		if (strlen($actioncode) < 2) {
			return false;
		}

		$sql = "UPDATE " . $this->_table . " SET ACTIONCODE = ?,
					`TIMESTAMP` = ?,
					PARAMETERS = ?,
					USERID = ?
				WHERE
					ID = ?;";
		$result = sYDB()->Execute($sql, $actioncode, $timestamp, $parameters, $this->_uid, $jobId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Removes the specified Scheduler job
	 *
	 * @param int $jobId Scheduler job Id
	 * @return bool TRUE on success or FALSE in case of an error
	 * @throws Exception
	 */
	function removeJob($jobId) {
		$jobId = (int)$jobId;
		if ($jobId < 1) {
			return false;
		}
		$sql = "DELETE FROM `" . $this->_table . "` WHERE ID = ?;";
		$result = sYDB()->Execute($sql, $jobId);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		return true;
	}

	/**
	 * Aquires a lock on the specified Scheduler job
	 *
	 * @param int $jobId Scheduler job Id
	 * @param int $timeout Time in seconds for which the job will be locked
	 * @return bool TRUE if the lock could be aquired or FALSE if not
	 * @throws Exception
	 */
	function getLock($jobId, $timeout) {
		$jobId = (int)$jobId;
		$timeout = (int)$timeout;

		$sql = "SELECT GET_LOCK('scheduler_lock_" . $jobId . "', " . $timeout . ") AS GOT_LOCK;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		if ($resultarray[0]['GOT_LOCK'] == 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Releases a lock on the specified Scheduler job
	 *
	 * @param int $jobId Scheduler job Id
	 * @return bool TRUE if the lock could be released or FALSE if not
	 * @throws Exception
	 */
	function releaseLock($jobId) {
		$jobId = (int)$jobId;

		$sql = "SELECT RELEASE_LOCK('scheduler_lock_" . $jobId . "') AS RELEASE_LOCK;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		if ($resultarray[0]['RELEASE_LOCK'] == 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if a specified job has been locked
	 *
	 * @param int $jobId Scheduler job Id
	 * @return bool TRUE if the job is locked or FALSE if not
	 * @throws Exception
	 */
	function isLocked($jobId) {
		$jobId = (int)$jobId;

		$sql = "SELECT IS_FREE_LOCK('scheduler_lock_" . $jobId . "') AS IS_LOCKED;";
		$result = sYDB()->Execute($sql);
		if ($result === false) {
			throw new Exception(sYDB()->ErrorMsg());
		}
		$resultarray = $result->GetArray();
		if (($resultarray[0]['IS_LOCKED'] == 1)) {
			return false;
		} else {
			return true;
		}
	}

}

/// @endcond

?>