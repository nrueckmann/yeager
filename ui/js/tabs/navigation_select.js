/**
 * Submits a contenteditor-link-chooser-window
 * @param { String } [winID] The window id
 * @param { Element } [openerReference] The reference to the opener
 * @function
 * @name $K.yg_submitNavigation
 */
$K.yg_submitNavigation = function(winID, openerReference) {
	// For actionbutton
	var selectedNavigation = $K.windows['wid_'+winID].yg_id;

	if (!selectedNavigation) {
		// For submitbutton
		selectedNavigation = $K.yg_getFocusObj($('wid_'+winID))[0].up('li').readAttribute('yg_id');
	}

	$K.windows[openerReference].changeNavigation( selectedNavigation );
	$K.windows['wid_'+winID].remove();
}

/**
 * Initializes a navigation-chooser-window
 * @param { String } [winID] The window id
 * @param { Element } [preselected] The navigation to preselect
 * @function
 * @name $K.yg_initDlgNavigations
 */
$K.yg_initDlgNavigations = function( winID, preselected ) {
	if (!preselected) preselected = 0;
	preselitem = $('item_'+winID+'_'+preselected+'_selector');
	$K.yg_blockSelect(preselitem);
}

/**
 * Callback function for sortable list
 * @name $K.navigationListSortCallbacks
 */
$K.navigationListSortCallbacks = {
	starteffect: function(element) {
		var parentWin = $K.windows[element.up('.ywindow').id];
		parentWin.yg_id = element.readAttribute('yg_id');
	}
};
