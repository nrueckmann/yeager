/**
 * Refreshes the object-lock regularly
 */
$K.addOnDOMReady(function() {
	new PeriodicalExecuter(function(pe) {
		$K.yg_refreshLocks();
	}, $K.objectRelockInterval);
});

/**
 * Requests a release for a lock for an object after closing a window
 * @param { String } [winID] Window-ID
 * @param { String } [objectType] The type of the object
 * @param { String } [objectYgId] YG-ID of the object
 * @function
 * @name $K.yg_releaseLock
 */
$K.yg_releaseLock = function(winID, objectType, objectYgId) {
	if ($K.windows['wid_'+winID].locking) {
		var data = Array ('noevent', { yg_property: 'releaseLock', params: {
			winID: winID,
			objectType: objectType,
			objectYgId: objectYgId,
			us: document.body.id
		} } );
		$K.yg_AjaxCallback(data, 'releaseLock');
	}
}

/**
 * Re-locks all currently opened objects
 * @function
 * @name $K.yg_refreshLocks
 */
$K.yg_refreshLocks = function() {
	var currentObjects = new Array();
	for (currWindow in $K.windows) {
		var currentWindow = $K.windows[currWindow];
		if (currentWindow.locking) {
			var isLocked = false;
			if (currentWindow.locked) {
				isLocked = true;
			}
			currentObjects.push( {
				winID: currentWindow.id.replace(/wid_/, ''),
				objectType: currentWindow.yg_type,
				winYgId: currentWindow.yg_id,
				locked: isLocked
			} );
		}
	}

	if ( (currentObjects.length > 0) && ($K.isAuthenticated) ) {
		var data = Array ('noevent', { yg_property: 'aquireLock', params: {
			currentObjects: Object.toJSON(currentObjects),
			us: document.body.id,
			lh: $K.yg_getLastGuiSyncHistoryId()
		} } );
		$K.yg_AjaxCallback(data, 'aquireLock');
	}
}

/**
 * Un-/locks a window (and reloads the currently opened tab)
 * @param { String } [winID] Window-ID
 * @param { Boolean } [lockStatus] State of the lock
 * @function
 * @name $K.changeWindowLockState
 */
$K.changeWindowLockState = function(winID, lockStatus) {
	if (!$K.windows['wid_'+winID]) return;
	var oldStatus = $K.windows['wid_'+winID].locked;
	if (lockStatus == true) {
		$K.windows['wid_'+winID].locked = true;
	} else if (lockStatus == false) {
		$K.windows['wid_'+winID].locked = false;
	}
	// Reload currently opened tab (if status has changed)
	if ((oldStatus != lockStatus) && ($K.windows['wid_'+winID].tab.toLowerCase() != 'foldercontent')) {
		$K.windows['wid_'+winID].tabs.select($K.windows['wid_'+winID].tabs.selected);
	}
}

/**
 * Un-/locks a object (and trigger $K.changeWindowLockState when a matching window is found)
 * @param { String } [ygId] YgId
 * @param { String } [ygType] YgType
 * @param { Boolean } [lockStatus] State of the lock
 * @function
 * @name $K.yg_changeWindowLockStateForObject
 */
$K.yg_changeWindowLockStateForObject = function(ygType, ygId, lockStatus) {
	if (lockStatus == 'true') {
		lockStatus = true;
	} else {
		lockStatus = false;
	}
	for(winId in $K.windows) {
		if ( ($K.windows[winId].yg_id == ygId) || ($K.windows[winId].yg_type == ygType) ) {
			$K.changeWindowLockState(winId, lockStatus);
		}
	}
}
