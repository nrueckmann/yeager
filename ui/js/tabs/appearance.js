/**
* Initializes appearance tab
* @param { String } [pageID] The contentblock-object which should be opened/closed
* @param { String } [winID] The contentblock-object which should be opened/closed
* @function
* @name $K.yg_initAppearanceTab
*/
$K.yg_initAppearanceTab = function( pageID, winID ) {
	
	$K.windows['wid_'+winID].changeTemplate = function (templateId) {

		$K.yg_changeTemplate(templateId,this.yg_id);

	}

	$K.windows['wid_'+winID].refreshTemplate = function(templateId) {
		
		var tabRef = $('wid_'+winID+'_APPEARANCE');
		
		if ( !Object.isUndefined(tabRef) ) {
			
			var panelRef = tabRef.down('.cntblockselection');
			panelRef.writeAttribute('yg_preselected', templateId);
			
			new Ajax.Updater(panelRef, $K.appdir+'template_info/',
			{
				onComplete: function() { $K.windows['wid_'+winID].init(); },
				asynchronous: true,
				evalScripts: true,
				method: 'post',
				parameters: {
					template: templateId,
					mode: 'details',
					us: document.body.id,
					lh: $K.yg_getLastGuiSyncHistoryId()
				}
			});

		} else {
			$K.log( 'Panel not there, nothing to update...', $K.Log.INFO );
		}
	}
	
	$K.windows['wid_'+winID].changeNavigation = function (navigationId) {
		
		var tabRef = $('wid_'+winID+'_APPEARANCE');
		var panelRef = tabRef.down('.navipanel');
		var site = this.yg_id.split('-');
		
		site = site[1];
		navigationId = navigationId.split('-');
		navigationId = navigationId[0];
		panelRef.writeAttribute('yg_preselected', navigationId);
		
		window.$navigationId = navigationId;
		
		var data = Array ( 'noevent', {yg_property: 'setPageNavigation', params: {
			page: pageID,
			site: site,
			wid: winID,
			navigationId: navigationId
		} } );
		$K.yg_AjaxCallback( data, 'setPageNavigation' );
	}
	
	$K.windows['wid_'+winID].refreshNavigation = function( or_navi, or_template) {
		
		var tabRef = $('wid_'+winID+'_APPEARANCE');
		
		var navId = parseInt(window.$navigationId);
		
		// Check if we are overridden by parameters
		if (or_navi) {
			navId = or_navi;
		}
		if (or_template) {
			var templatePanelRef = tabRef.down('.cntblockselection');
			templatePanelRef.writeAttribute('yg_preselected', or_template);
		}
		
		if ( !Object.isUndefined(tabRef) ) {
			
			var panelRef = tabRef.down('.navipanel');
			var templatePanelRef = tabRef.down('.cntblockselection');
			var templateId = templatePanelRef.readAttribute('yg_preselected');
			
			new Ajax.Updater(panelRef, $K.appdir+'navigation_info/',
			{
				onComplete: function() { $K.windows['wid_'+winID].init(); },
				asynchronous: true,
				evalScripts: true,
				method: 'post',
				parameters: {
					navigation: navId,
					template: templateId,
					us: document.body.id,
					lh: $K.yg_getLastGuiSyncHistoryId()
				}
			});
	
		} else {
			$K.log( 'Panel not there, nothing to update...', $K.Log.INFO );
		}
	}
	
	if ($("wid_" + winID + "_templatepanel")) $('wid_'+winID+'_templatepanel').observe('mouseleave', function() { $K.yg_setDropAllowed(false); });

	if ( $('sortlist'+winID) ) {
		$K.yg_initSortable('sortlist'+winID);
	}
	
}


/**
* Callback function for sortable list
* @name $K.subpagesSortCallbacks
*/
$K.subpagesSortCallbacks = {
	onUpdate: function(element) {
		var listArray = Array();
		for (var i=0;i<element.childNodes.length;i++) {
			var tagId = element.childNodes[i].readAttribute('yg_id').split('-');
			tagId = tagId[0];
			listArray.push( tagId );
		}

		var parentWin = $K.windows[this.element.up('.ywindow').id];
		var siteID = parentWin.yg_id.split('-')[1];
		var pageID = parentWin.yg_id.split('-')[0];
		
		var data = Array ( 'noevent', {yg_property: 'orderPageSubpages', params: {
			page: pageID,
			site: siteID,
			listArray: listArray
		} } );
		$K.yg_AjaxCallback( data, 'orderPageSubpages' );
	
		$K.yg_updateSortables();
	}
};
