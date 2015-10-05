/**
 * @fileoverview functions for version tab
 *
 * @version 0.2.0
 */

/**
 * Inits version filter onload/reload of tab
 * @param { String } [win] window id
 * @function
 */
$K.yg_initVersionFilter = function(win) {
	if (!$K.windows[win].loadparams.versionfilter_tab) {
		// Reset normal filters
		$K.windows[win].loadparams.versionfilter_tab = 'ALL';
		$K.windows[win].loadparams.versionfilter_action = 'ALL';
		$K.windows[win].loadparams.versionfilter_timeframe = 'LAST_WEEK';
	} else {
		for (var filtername in $K.windows[win].loadparams) {
			if (filtername.startsWith('versionfilter')) {
				tmpobj = $(win+"_"+filtername);
				if (tmpobj) tmparr = tmpobj.immediateDescendants();
				for (var k=0; k<tmparr.length;k++) {
					if (tmparr[k].readAttribute('value') == $K.windows[win].loadparams[filtername]) {
						tmpobj.previous().down('span').innerHTML = tmparr[k].readAttribute('shortname');
					}
				}
			}
		}
	}
	if (!$K.windows[win].loadparams.trashcanfilter_created) {
		// Reset trashcan filters
		$K.windows[win].loadparams.trashcanfilter_created = 'ALL';
		$K.windows[win].loadparams.trashcanfilter_removed = 'ALL';
	} else {
		for (var filtername in $K.windows[win].loadparams) {
			if (filtername.startsWith('trashcanfilter')) {
				tmpobj = $(win+"_"+filtername);
				if (tmpobj) tmparr = tmpobj.immediateDescendants();
				for (var k=0; k<tmparr.length;k++) {
					if (tmparr[k].readAttribute('value') == $K.windows[win].loadparams[filtername]) {
						tmpobj.previous().down('span').innerHTML = tmparr[k].readAttribute('shortname');
					}
				}
			}
		}
	}
	$K.windows[win].loadparams.refresh = undefined;
}


/**
 * Sets filter
 * @param { String } [col] may be set to 'tab', 'action' or 'timeframe'
 * @param { String } [value] filter value
 * @param { Element } [shorttitle] Shorttitle of selected filter
 * @param { String } [win] window id
 * @function
 */
$K.yg_filterVersions = function(col, value, shorttitle, win) {
	if ((col == "timeframe") && (value == "CUSTOM")) {
		new $K.yg_wndobj({ config: 'VERSIONS_TIMEFRAME', loadparams: { opener_reference: win, action: 'versions', shorttitle: shorttitle } } );
	} else {
		$(win+'_versionfilter_'+col).previous().down().innerHTML = shorttitle;

		$K.windows[win].loadparams['versionfilter_'+col] = value;
		$K.windows[win].loadparams.refresh = true;
		$K.windows[win].tabs.select($K.windows[win].tabs.selected, {
			versionfilter_tab: $K.windows[win].loadparams.versionfilter_tab,
			versionfilter_action: $K.windows[win].loadparams.versionfilter_action,
			versionfilter_timeframe: $K.windows[win].loadparams.versionfilter_timeframe,
			refresh: 1
		});
	}
}


/**
 * Submits custom timeframe to versions tab
 * @param { String } [winID] window id
 * @param { String } [openerReference] id of opener window
 * @param { String } [shorttitle] Shorttitle of selected filter
 * @function
 */
$K.yg_submitVersionTimeframe = function(winID, openerReference, shorttitle) {
	var timeframevar = $('wid_'+winID+'_timeframe_from').value + "###" + $('wid_'+winID+'_timeframe_till').value;
	$K.yg_filterVersions('timeframe', timeframevar, shorttitle, openerReference);
	$K.windows['wid_'+winID].remove();
}


/**
 * Restores the selected version of the page (from Versions)
 * @param { Element } [element] The element from which the function was called.
 * @param { String } [version] The version of the page to restore to.
 * @function
 * @name $K.yg_restorePageVersion
 */
$K.yg_restorePageVersion = function( element, version ) {
	var yg_id = $K.windows[element.up('.ywindow').id].yg_id;
	var page = yg_id.split('-')[0];
	var site = yg_id.split('-')[1];
	var wid = element.up('.ywindow').id.replace(/wid_/g, '');

	var data = Array ( 'noevent', {yg_property: 'restorePageVersion', params: {
		page: page,
		site: site,
		version: version,
		wid: wid
	} } );
	$K.yg_AjaxCallback( data, 'restorePageVersion' );
}


/**
 * Restores the selected version of the page (from Versions)
 * @param { Element } [element] The element from which the function was called.
 * @param { String } [version] The version of the page to restore to.
 * @function
 * @name $K.yg_restoreMailingVersion
 */
$K.yg_restoreMailingVersion = function( element, version ) {
	var yg_id = $K.windows[element.up('.ywindow').id].yg_id;
	var mailingId = yg_id.split('-')[0];
	var wid = element.up('.ywindow').id.replace(/wid_/g, '');

	var data = Array ( 'noevent', {yg_property: 'restoreMailingVersion', params: {
		mailingId: mailingId,
		version: version,
		wid: wid
	} } );
	$K.yg_AjaxCallback( data, 'restoreMailingVersion' );
}


/**
 * Restores the selected version of the contentblock (from Versions)
 * @param { Element } [element] The element from which the function was called.
 * @param { String } [version] The version of the contentblock to restore to.
 * @function
 * @name $K.yg_restoreCBlockVersion
 */
$K.yg_restoreCBlockVersion = function( element, version ) {
	var yg_id = $K.windows[element.up('.ywindow').id].yg_id;
	var cblock = yg_id.split('-')[0];
	var wid = element.up('.ywindow').id.replace(/wid_/g, '');

	var data = Array ( 'noevent', {yg_property: 'restoreCBlockVersion', params: {
		cblock: cblock,
		version: version,
		wid: wid
	} } );
	$K.yg_AjaxCallback( data, 'restoreCBlockVersion' );
}


/**
 * Restores the selected version of the file (from Versions)
 * @param { Element } [element] The element from which the function was called.
 * @param { String } [version] The version of the file to restore to.
 * @function
 * @name $K.yg_restoreFileVersion
 */
$K.yg_restoreFileVersion = function( element, version ) {
	var yg_id = $K.windows[element.up('.ywindow').id].yg_id;
	var file = yg_id.split('-')[0];
	var site = yg_id.split('-')[1];
	var wid = element.up('.ywindow').id.replace(/wid_/g, '');

	var data = Array ( 'noevent', {yg_property: 'restoreFileVersion', params: {
		file: file,
		site: site,
		version: version,
		wid: wid
	} } );
	$K.yg_AjaxCallback( data, 'restoreFileVersion' );
}
