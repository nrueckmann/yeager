/**
* Sync the GUI with in backend in regular intervals
* @function
* @name $K.yg_startGUIUpdateTimer
*/
$K.yg_startGUIUpdateTimer = function() {
	if ($K.yg_guiUpdateTimer) {
		window.clearInterval($K.yg_guiUpdateTimer);
	}
	$K.yg_guiUpdateTimer = window.setInterval(function(){
		if (Ajax.activeRequestCount == 0) {
			var data = Array ( 'noevent', {yg_property: 'ping', params: {} } );
			$K.yg_AjaxCallback( data, 'ping' );
		}
	}, ($K.guiSyncInterval * 1000) );
}


/**
* Returns the current gui-session (or false if another request is currently running)
* @function
* @name $K.yg_getLastGuiSyncHistoryId
*/
$K.yg_getLastGuiSyncHistoryId = function() {
	return $K.currentGuiSyncHistoryId;
}


$K.yg_lastExecutedGuiJSQueueId = 0;
/**
* Executes the received gui-js-queue from the backend
* @param { Object } [queueData] The object which contains the queue to execute
* @function
* @name $K.yg_executeGuiJSQueue
*/
$K.yg_executeGuiJSQueue = function(queueData) {
	for (var queueIdx in queueData) {
		if (queueData.hasOwnProperty(queueIdx)) {
			if (parseInt(queueIdx, 10) > $K.yg_lastExecutedGuiJSQueueId) {
				new Function(queueData[queueIdx])();
				$K.yg_lastExecutedGuiJSQueueId = parseInt(queueIdx, 10);
			}
		}
	}
}
