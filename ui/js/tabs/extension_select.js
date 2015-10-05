/**
* Submits the extensions dialog
* @param { String } [winID] The window-id
* @param { String } [openerReference] The reference to the opener
* @function
* @name $K.yg_submitExtensions
*/
$K.yg_submitExtensions = function(winID, openerReference) { 
	if ( $K.windows['wid_'+winID].yg_id == undefined ) {
		$K.windows['wid_'+winID].remove();
		return;
	}
	// For insertion of extensions
	var yg_id = $K.windows['wid_'+winID].yg_id;

	if (typeof $K.windows[openerReference].addExtension == 'function') {
		$K.windows[openerReference].addExtension(yg_id, $K.windows[openerReference].yg_id, yg_id.split('-')[1], true);
	}	
	$K.windows['wid_'+winID].remove();
}


/**
* Initializes the extensions dialog
* @param { String } [winID] The window-id
* @param { String } [openerReference] The reference to the opener
* @function
* @name $K.yg_initDlgExtensions
*/
$K.yg_initDlgExtensions = function( winID, openerReference ) {

	$K.windows[openerReference].addExtension = function (src_id, target_id, src_type, refresh) {
		
		var page = target_id.split('-')[0];
		var site = target_id.split('-')[1];
		extensionId = src_id.split('-')[0];
		
		var data = Array ( 'noevent', {yg_property: 'addObjectExtension', params: {
			page: page,
			site: site,
			extensionId: extensionId,
			openerRefID: this.num,
			refresh: refresh.toString()
		} } );
		$K.yg_AjaxCallback( data, 'addObjectExtension' );
	}

	Position.includeScrollOffsets = true;
}

/**
* Callback function for sortable list
* @name $K.extensionslistSortCallbacks
*/
$K.extensionslistSortCallbacks = {
	starteffect: function(element) {
		var parentWin = $K.windows[element.up('.ywindow').id];
		parentWin.yg_id = element.readAttribute('yg_id');
	}
};
