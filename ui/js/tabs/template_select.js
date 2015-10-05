/**
 * Submits the template-chooser
 * @param { String } [winID] The window id
 * @param { String } [openerReference] The reference to the opener
 * @function
 * @name $K.yg_submitTemplate
 */
$K.yg_submitTemplate = function(winID, openerReference) {
	if ( $K.windows['wid_'+winID].yg_id == undefined ) {
		$K.windows['wid_'+winID].remove();
		return;
	}
	$K.windows[openerReference].changeTemplate( $K.windows['wid_'+winID].yg_id.split('-')[0] );
	$K.windows['wid_'+winID].remove();
}


/**
 * Submits the template-chooser (for folders)
 * @param { String } [winID] The window id
 * @param { String } [openerReference] The reference to the opener
 * @function
 * @name $K.yg_submitTemplateFolder
 */
$K.yg_submitTemplateFolder = function(winID, openerReference) {
	var yg_id = $K.windows['wid_'+winID].yg_id;
	if (yg_id) {
		var openerWin = $(openerReference);
		if ($K.windows[openerWin.id] && $K.windows[openerWin.id].changeTemplateRoot) {
			$K.windows[openerWin.id].changeTemplateRoot(yg_id.split('-')[0]);
		}
	}
	$K.windows['wid_'+winID].remove();
}

/**
 * Changes template
 * @param { String } [templateID] Id of template
 * @param { String } [targetID] yg_id of target object
 * @param { String } [winID] window id, may be false
 * @function
 * @name $K.yg_changeTemplate
 */
$K.yg_changeTemplate = function(templateID, targetID) {
	var pageID = targetID.split('-')[0];
	var siteID = targetID.split('-')[1];
		
	var data = Array ( 'noevent', {yg_property: 'setPageTemplate', params: {
		page: pageID,
		site: siteID,
		templateId: templateID
	} } );
		
	$K.yg_AjaxCallback( data, 'setPageTemplate' );
}

/**
 * Updates template in all open windows
 * @param { String } [yg_id] of target object
 * @param { String } [templateID] if of template
 * @function
 * @name $K.yg_updateTemplate
 */
$K.yg_updateTemplate = function(yg_id, templateID) {
	for (var winID in $K.windows) {
		
		if (($K.windows[winID].yg_id == yg_id) && ($K.windows[winID].tab == 'APPEARANCE') && (typeof($K.windows[winID].refreshTemplate) == 'function')) {
			$K.windows[winID].refreshTemplate(templateID);
		}
	
	}
}


/**
 * Updates template in all open windows
 * @param { String } [yg_id] of target object
 * @param { String } [navigationID] id of navigation
  * @param { String } [templateID] id of related template
 * @function
 * @name $K.yg_updateNavigation
 */
$K.yg_updateNavigation = function(yg_id, navigationID, templateID) {
	for (var winID in $K.windows) {
		
		if (($K.windows[winID].yg_id == yg_id) && ($K.windows[winID].tab == 'APPEARANCE') && (typeof($K.windows[winID].refreshNavigation) == 'function')) {
			$K.windows[winID].refreshNavigation(navigationID, templateID);
		}
	
	}
}