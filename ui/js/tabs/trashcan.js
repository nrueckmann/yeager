/**
 * @fileoverview functions for trashcan
 *
 * @version 0.2.0
 */

/**
 * Sets filter
 * @param { String } [col] may be set to 'created' or 'removed'
 * @param { String } [value] filter value
 * @param { Element } [shorttitle] Shorttitle of selected filter
 * @param { String } [win] window id
 * @function
 */
$K.yg_filterTrashcan = function(col, value, shorttitle, win, openerRef) {

	// Reset pagedir to first page
	$K.yg_pageDirReset(win);

	if (value == "CUSTOM") {
		new $K.yg_wndobj({ config: 'TRASHCAN_TIMEFRAME', loadparams: { opener_reference: openerRef, shorttitle: shorttitle, col: col } } );
	} else {
		$(win+'_trashcanfilter_'+col).previous().down().innerHTML = shorttitle;

		$K.windows[win].loadparams['trashcanfilter_'+col] = value;

		var params = new Object();
		params.trashcanfilter_created = $K.windows[win].loadparams.trashcanfilter_created;
		params.trashcanfilter_removed = $K.windows[win].loadparams.trashcanfilter_removed;
		params.refresh = 1;

		var window = $K.windows[win];
		var current_tab = window.tab;

		for (var i=0;i<window.tabs.elements.length;i++) {
			if (window.tabs.elements[i]["NAME"]==current_tab) var current_tab_no = i;
		}

		window.tabs.select(current_tab_no, params);
	}
}


/**
 * Submits custom timeframe to versions tab
 * @param { String } [winID] window id
 * @param { String } [openerReference] id of opener window
 * @param { String } [shorttitle] Shorttitle of selected filter
 * @function
 */
$K.yg_submitTrashcanTimeframe = function(winID, openerReference, shorttitle) {
	var timeframevar = $('wid_'+winID+'_timeframe_from').value + "###" + $('wid_'+winID+'_timeframe_till').value;
	$K.yg_filterTrashcan(openerReference.split('_')[3], timeframevar, shorttitle, 'wid_'+openerReference.split('_')[1], 'wid_'+openerReference.split('_')[1]);
	$K.windows['wid_'+winID].remove();
}

/**
 * Function to remove an object from the trash
 * @param { Element } [which] Reference to the opener-actionbutton
 * @function
 * @name $K.yg_shredderObject
 */
$K.yg_shredderObject = function(which) {
	which = $(which);

	var objectType = which.readAttribute('yg_type');
	var winID = which.up('.ywindow').id;

	var focusobjs = $K.yg_getFocusObj(which.up('.mk_contentgroup'));

	var inselection = false;
	focusobjs.each(function(item) {
		if (item == which) {
			inselection = true;
			throw $break;
		}
	});

	if ((focusobjs.length > 0) && inselection) {
		var objectYGIDs = new Array();
		focusobjs.each(function(selObject){
			objectYGIDs.push(selObject.readAttribute('yg_id'));
		});
		var dlgText = focusobjs.length + ' ' +$K.TXT('TXT_OBJECTS');
	} else {
		var objectYGIDs = [which.readAttribute('yg_id')];
		var dlgText = '1 ' +$K.TXT('TXT_OBJECT');
	}

	$K.yg_promptbox( $K.TXT('TXT_APPROVE_DELETE_TITLE'), $K.TXT('TXT_APPROVE_DELETE_P1')+dlgText+$K.TXT('TXT_APPROVE_DELETE_P2'), 'standard',
		function() {
			objectYGIDs.each(function(shredItem){
				var data = Array ('noevent', { yg_property: 'shredderObject', params: {
					yg_id: shredItem,
					yg_type: objectType,
					winID: winID
				} } );
				$K.yg_AjaxCallback(data, 'shredderObject');
			});
		}, function() { }
	);

}

/**
 * Function to restore an object from the trash
 * @param { Element } [which] Reference to the opener-actionbutton
 * @function
 * @name $K.yg_restoreObject
 */
$K.yg_restoreObject = function(which) {
	which = $(which);

	var objectType = which.up('.cntblockcontainer').readAttribute('yg_type');
	var objectYGID = which.up('.cntblockcontainer').readAttribute('yg_id');
	var winID = which.up('.ywindow').id;

	var focusobjs = $K.yg_getFocusObj(which.up('.mk_contentgroup'));

	if (focusobjs.length > 1) {
		var multiYGIDs = new Array();
		focusobjs.each(function(selObject){
			multiYGIDs.push(selObject.up('.cntblockcontainer').readAttribute('yg_id'));
		});
		$K.windows[winID].restore_yg_id = multiYGIDs;
	} else {
		$K.windows[winID].restore_yg_id = [ objectYGID ];
	}

	console.warn( 'SELECTED:', $K.windows[winID].restore_yg_id );

	switch(objectType) {
		case 'page':
			new $K.yg_wndobj({ config: 'PAGE_MOVE', loadparams: {
				action: 'restore',
				opener_reference: winID,
				site: objectYGID.split('-')[1]
			} });
			break;
		case 'cblock':
			new $K.yg_wndobj({ config: 'CBLOCK_MOVE', loadparams: {
				action: 'restore',
				opener_reference: winID
			} });
			break;
		case 'file':
			new $K.yg_wndobj({ config: 'FILE_MOVE', loadparams: {
				action: 'restore',
				opener_reference: winID,
				type: 'file'
			} });
			break;
	}

}
