/**
 * Submits the view selection window
 * @param { String } [openerReference] Reference to the opener
 * @param { String } [winID] The window id
 * @function
 * @name $K.yg_submitViews
 */
$K.yg_submitViews = function(openerReference, winID) {
	
	var openerWin = $(openerReference).up('.ywindow').id;
	
	var focusobjs = $K.yg_getFocusObj($('wid_'+winID+'_viewlist'));
	
	if (focusobjs.length > 0) {
		
		focusobjs.each(function(item) {
			var viewID = item.up('li').readAttribute('yg_id').split('-')[0];
			var fileID = $K.windows[openerWin].yg_id.split('-')[0]; 
			
			var data = Array ( 'noevent', {yg_property: 'addFileView', params: {
				file: fileID,
				view: viewID
			} } );
			$K.yg_AjaxCallback( data, 'addFileView' );
		});
	} else if ($K.windows['wid_'+winID].yg_id) {
		var viewID = $K.windows['wid_'+winID].yg_id.split('-')[0];
		var fileID = $K.windows[openerWin].yg_id.split('-')[0]; 
			
		var data = Array ( 'noevent', {yg_property: 'addFileView', params: {
			file: fileID,
			view: viewID,
			openerWin: openerWin
		} } );
		$K.yg_AjaxCallback( data, 'addFileView' );
	}
	
	$K.windows['wid_'+winID].remove();
}


/**
* Callback function for sortable list
* @name $K.viewListSortCallbacks
*/
$K.viewListSortCallbacks = {
	starteffect: function(element) {
		var parentWin = $K.windows[element.up('.ywindow').id];
		parentWin.yg_id = element.readAttribute('yg_id');
	}
};
