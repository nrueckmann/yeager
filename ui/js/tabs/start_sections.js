/**
 * Initializes the start-sections
 * @param { String } [winID] The window id
 * @function
 * @name $K.yg_initStartSections
 */
$K.yg_initStartSections = function( winID ) {
	// Check if Updater is shown
	if ($(winID).down('.mk_updater')) {
		var data = Array ( 'noevent', {yg_property: 'checkUpdates', params: {
			winID: winID
		} } );
		$K.yg_AjaxCallback( data, 'checkUpdates', true );
	}
}
