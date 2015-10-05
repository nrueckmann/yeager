/**
 * Inititalzes the site list
 * @param { String } [winID] The window id
 * @function
 * @name $K.yg_initSiteList
 */
$K.yg_initSiteList = function( winID ) {

	var winRef = $('wid_'+winID);

	$K.windows['wid_'+winID].sortList = function() {
		var container = $('wid_'+winID+'_tab_SITELIST_list');
		var listEntries = container.select('li');
		var sortList = new Array();
		listEntries.each(function(listItem){
			var itemName = listItem.down('.title.txt').innerHTML.toUpperCase();
			var itemID = listItem.id;
			sortList.push( itemName+'<<||>>'+itemID );
		});
		sortList.sort();
		sortList.each(function(listItem){
			var itemID = listItem.split('<<||>>')[1];
			container.insert({bottom: $(itemID)});
		});
	}
}


/**
 * Inititializes the site config-tab
 * @param { String } [parentWinID] The window id
 * @function
 * @name $K.yg_initSiteConfig
 */
$K.yg_initSiteConfig = function( parentWinID ) {

	$K.windows['wid_'+parentWinID].changeTemplate = function (templateId) {

		var siteID = $K.windows['wid_'+parentWinID].yg_id.split('-')[0];

		var data = Array ( 'noevent', {yg_property: 'setSiteTemplate', params: {
			siteID: siteID,
			templateId: templateId,
			openerRefID: parentWinID
		} } );

		$K.yg_AjaxCallback( data, 'setSiteTemplate' );
	}

	$K.windows['wid_'+parentWinID].refreshSiteTemplate = function(name, filename, templateid, preview) {
		var panelRef = $('wid_'+parentWinID+'_templatepanel').down('.mk_templatecontainer');
		if (panelRef) {
			panelRef.up('.cntblockcontainer').down('input').value = templateid;
			new Ajax.Updater(panelRef, $K.appdir+'template_info/',
			{
				onComplete: function() {
					panelRef.next().hide();
					panelRef.show();
					$K.windows['wid_'+parentWinID].refresh();
				},
				asynchronous: true,
				evalScripts: true,
				method: 'post',
				parameters: {
					template: templateid,
					mode: 'details',
					us: document.body.id,
					lh: $K.yg_getLastGuiSyncHistoryId()
				}
			});
		} else {
			$K.log( 'Panel not there, nothing to update...', $K.Log.INFO );
		}
	}

	$K.windows['wid_'+parentWinID].changeTemplateRoot = function (templateId) {

		var siteID = $K.windows['wid_'+parentWinID].yg_id.split('-')[0];

		var data = Array ( 'noevent', {yg_property: 'setSiteTemplateRoot', params: {
			siteID: siteID,
			templateId: templateId,
			openerRefID: parentWinID
		} } );

		$K.yg_AjaxCallback( data, 'setSiteTemplateRoot' );
	}

	$K.windows['wid_'+parentWinID].refreshSiteTemplateRoot = function(templateId, name) {

		var panelRef = $('wid_'+parentWinID+'_templaterootpanel');

		if (panelRef) {
			panelRef.down('input').value = templateId;
			panelRef.down('.icn').className = "icn iconfolder"
			panelRef.down('.titlecnt').update(name);

			$K.windows['wid_'+parentWinID].refresh();
		} else {
			$K.log( 'Panel not there, nothing to update...', $K.Log.INFO );
		}
	}

}


/**
 * Saves all site information
 * @param { String } [winRef] Id of the parentwindow
 */
$K.yg_siteSaveInfo = function( winRef ) {
	winRef = $(winRef);

	var inputFields = $(winRef.id+'_innercontent').select('input');

	var parameters = {
		objectID: $K.windows[winRef.id].yg_id.split('-')[0],
		wid: winRef.id
	};

	var srch = new RegExp(winRef.id+'_');
	inputFields.each(function(item){
		var idxname = item.name.replace(srch,'');
		if (idxname) parameters[idxname] = item.value;
	});

	var data = Array ( 'noevent', {yg_property: 'saveSiteInfo', params: parameters } );
	$K.yg_AjaxCallback( data, 'saveSiteInfo' );
}


/**
 * Resets the default template of the site
 * @param { Element } [which] Reference to the actionbutton
 */
$K.yg_resetSiteDefaulttemplate = function( which ) {
	which = $(which);
	which.up('.cntblockcontainer').down('.icn').className = "icn noicon";
	which.up('.cntblockcontainer').down('.mk_templatecontainer').hide();
	which.up('.cntblockcontainer').down('.mk_templatecontainer').next().show();
	which.up('.cntblockcontainer').down('input').value = -1;
}


/**
 * Resets the templateroot of the site
 * @param { Element } [which] Reference to the actionbutton
 */
$K.yg_resetSiteTemplateroot = function( which ) {
	which = $(which);
	which.up('.cntblockcontainer').down('.icn').className = "icn noicon";
	which.up('.cntblockcontainer').down('.titlecnt').innerHTML = $K.TXT('TXT_SELECTOR_FOLDER');
	which.up('.cntblockcontainer').down('input').value = -1;
}


/**
 * Adds a site
 * @param { Element } [buttonReference] Reference to the button
 */
$K.yg_addSite = function( buttonReference ) {
	if (buttonReference.hasClassName('disabled')) {
		return;
	}

	var data = Array ( 'noevent', {yg_property: 'addSite', params: {
		wid: $(buttonReference).up('.ywindow').id
	} } );
	$K.yg_AjaxCallback( data, 'addSite' );
}


/**
 * Adds a siteitem to sitelist
 * @param { String } [winID] WindowId of the button
 * @param { String } [siteID] Site id
 * @param { String } [siteName] Site name
 */
$K.yg_addSiteItem = function( winID, siteID, siteName ) {

	if ($K.windows[winID] && $K.windows[winID].jsTemplate) {
		var item_template = $K.yg_makeTemplate( $K.windows[winID].jsTemplate );
		var newSiteItem = item_template.evaluate( {	new_id: siteID, new_name: siteName } );

		$(winID+'_tab_SITELIST_list').insert({bottom:newSiteItem});
		$K.windows[winID].sortList();

		$K.yg_customAttributeHandler( $('sites_'+winID.split('_')[1]+'_'+siteID) );
		$K.windows[winID].refresh();

		$K.yg_selectNode( $('sites_'+siteID+'_selector') );
		$K.yg_blockSelect( $('sites_'+siteID+'_selector'), null );
	}
}


/**
 * Checks the SE-Friendly sitename (PNAME)
 * @param { Element } [element] The element from which the function was called.
 * @function
 * @name $K.yg_checkSitePName
 */
$K.yg_checkSitePName = function( element ) {
	var value = element.value;

	if (!element.oldvalue) {
		element.oldvalue = element.readAttribute('oldvalue');
	}

	if (value.strip()=='') {
		element.value = element.oldvalue;
		$K.yg_promptbox( $K.TXT('TXT_CHANGE_SITE_URL_TITLE'), $K.TXT('TXT_CHANGE_SITE_URL_EMPTY'), 'alert');
		return false;
	}

	if (!isNaN(value)) {
		element.value = element.oldvalue;
		$K.yg_promptbox( $K.TXT('TXT_CHANGE_SITE_URL_TITLE'), $K.TXT('TXT_CHANGE_SITE_URL_NUMERIC'), 'alert');
		return false;
	}

	return true;
}
