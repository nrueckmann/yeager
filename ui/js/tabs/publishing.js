/**
 * @fileoverview functions for publishing tab
 * @version 1.0
 */

/**
 * Switch the currently published version
 * @param { String } [value] The current value of the dropdown.
 * @param { String } [win_reference] The reference to the window.
 * @param { Element } [element] The element of the dropdown.
 * @function
 * @name $K.yg_switchPublishedVersion
 */
$K.yg_switchPublishedVersion = function( value, win_reference, element ) {
	value = value.substring(8);
	var tabcontent = $('wid_'+win_reference+'_PUBLISHING')
	var default_value = element.getAttribute('yg_previous');
	var objType = $K.windows['wid_'+win_reference].yg_id.split('-')[1];

	switch(objType) {
		case 'cblock':
			$K.yg_promptbox( $K.TXT('TXT_CHANGE_PUB_VERSION_TITLE'), $K.TXT('TXT_CHANGE_PUB_CBLOCK_VERSION'), 'standard',
				function() {
					element.setAttribute('yg_previous', 'version_'+value);
					$K.yg_setEdited( element );
					if (value=='latest') {
						tabcontent.down('.last_approved_version').show();
					} else {
						tabcontent.down('.last_approved_version').hide();
					}
					$K.yg_saveCBlockPublishSettings( $('wid_'+win_reference), 'PUBLISH' );
				},
				function() {
					$K.yg_dropdownSelect( element, null, default_value, true );
				}
			);
			break;

		default:
		case 'page':
			$K.yg_promptbox( $K.TXT('TXT_CHANGE_PUB_VERSION_TITLE'), $K.TXT('TXT_CHANGE_PUB_PAGE_VERSION'), 'standard',
				function() {
					element.setAttribute('yg_previous', 'version_'+value);
					$K.yg_setEdited( element );
					if (value=='latest') {
						tabcontent.down('.last_approved_version').show();
					} else {
						tabcontent.down('.last_approved_version').hide();
					}
					$K.yg_savePagePublishSettings( $('wid_'+win_reference), 'PUBLISH' );
				},
				function() {
					$K.yg_dropdownSelect( element, null, default_value, true );
				}
			);
			break;
	}
}


/**
 * Saves the publish settings of the currently selected page
 * @param { String } [win_reference] The reference to the window.
 * @param { Element } [which] The field to change.
 * @function
 * @name $K.yg_savePagePublishSettings
 */
$K.yg_savePagePublishSettings = function( win_reference, which ) {
	var wid = win_reference.id.replace(/wid_/g, '');
	var win = $K.windows['wid_'+wid];
	var current_tab = win.tab;
	var tabs = win.tabs;
	var yg_id = win.yg_id;
	var page = yg_id.split('-')[0];
	var site = yg_id.split('-')[1];
	var tabcontent = $('wid_'+wid+'_PUBLISHING');
	var currently_published = tabcontent.down('.dropdownbox').down('input[type=hidden]').value.substring(8);

	// Collect Autopublish Data
	var auto_published = new Array();
	$('autopublishitems_container_'+wid).childElements().each(function(item) {
		var version = $(item.down('.dropdownbox').id+"_ddlist").down('.selected').getAttribute('value');
		var date = item.down('.frm_date').down('input').value;
		var time = item.down('.frm_time').down('input').value;
		var id = item.id.replace(/autopublish_item_/g, '').split('_');
		id = id[id.length-1];
		auto_published.push({id: id, version: version, date: date, time: time });
	});

	var data = Array ( 'noevent', {yg_property: 'savePagePublishingSettings', params: {
		autopublishData: Object.toJSON(auto_published),
		changedField: which,
		page: page,
		site: site,
		version: currently_published,
		wid: wid
	} } );
	$K.yg_AjaxCallback( data, 'savePagePublishingSettings' );
}


/**
 * Saves the publish settings of the currently selected contentblock
 * @param { String } [win_reference] The reference to the window.
 * @param { Element } [which] The field to change.
 * @function
 * @name $K.yg_saveCBlockPublishSettings
 */
$K.yg_saveCBlockPublishSettings = function( win_reference, which ) {
	var wid = win_reference.id.replace(/wid_/g, '');
	var win = $K.windows['wid_'+wid];
	var current_tab = win.tab;
	var tabs = win.tabs;
	var yg_id = win.yg_id;
	var cblock = yg_id.split('-')[0];
	var site = yg_id.split('-')[1];
	var tabcontent = $('wid_'+wid+'_PUBLISHING')
	var currently_published = tabcontent.down('.dropdownbox').down('input[type=hidden]').value.substring(8);

	// Collect Autopublish Data
	var auto_published = new Array();
	$('autopublishitems_container_'+wid).childElements().each(function(item) {
		var version = $(item.down('.dropdownbox').id+"_ddlist").down('.selected').getAttribute('value');
		var date = item.down('.frm_date').down('input').value;
		var time = item.down('.frm_time').down('input').value;
		var id = item.id.replace(/autopublish_item_/g, '').split('_');
		id = id[id.length-1];
		auto_published.push({id: id, version: version, date: date, time: time });
	});

	var data = Array ( 'noevent', {yg_property: 'saveCBlockPublishingSettings', params: {
		autopublishData: Object.toJSON(auto_published),
		changedField: which,
		cblock: cblock,
		site: site,
		version: currently_published,
		wid: wid
	} } );
	$K.yg_AjaxCallback( data, 'saveCBlockPublishingSettings' );
}


/**
 * Saves the publish settings of the currently selected page (wrapper for the above when called from Dropdown)
 * @param { Element } [value1] Value No.1
 * @param { Element } [value2] Value No.2
 * @param { Element } [element] The input field to highlight after a change.
 * @function
 * @name $K.yg_ddSavePagePublishSettings
 */
$K.yg_ddSavePagePublishSettings = function( value1, value2, element ) {
	$K.yg_setEdited( element );
	$K.yg_savePagePublishSettings( element.up('.ywindow'), 'VERSION' );
}


/**
 * Saves the publish settings of the currently selected contentblock (wrapper for the above when called from Dropdown)
 * @param { Element } [value1] Value No.1
 * @param { Element } [value2] Value No.2
 * @param { Element } [element] The input field to highlight after a change.
 * @function
 * @name $K.yg_ddSaveCBlockPublishSettings
 */
$K.yg_ddSaveCBlockPublishSettings = function( value1, value2, element ) {
	$K.yg_setEdited( element );
	$K.yg_saveCBlockPublishSettings( element.up('.ywindow'), 'VERSION' );
}


/**
 * Resets the "edited" state of the input fields on the "publish"-tab after changing
 * @param { String } [win_ref] The id of the window to reset.
 * @function
 * @name $K.yg_resetPublishSettingsEditState
 */
$K.yg_resetPublishSettingsEditState = function( win_ref ) {
	var publish_tab = $('wid_'+win_ref+'_PUBLISHING');
	var elements = publish_tab.select('.changed');

	elements.each(function(item){
		$K.log( 'Faded item is:', item, $K.Log.DEBUG );
		$K.yg_fadeField(item);
	});
}


/**
 * Adds a new autopublishpanel to the page (from publication)
 * @param { Element } [element] The element from which the function was called.
 * @param { String } [position] The position where to insert the new panel.
 * @function
 * @name $K.yg_addAutopublishItem
 */
$K.yg_addAutopublishItem = function( element,  position ) {
	element = $(element);

	var wid = element.up('.ywindow').id;
	var innerContent = element.up('.innercontent');
	var objectID = $K.windows[wid].yg_id.split('-')[0];
	var site = $K.windows[wid].yg_id.split('-')[1];
	if (element.counter!=undefined) {
		element.counter++;
	} else {
		element.counter = 0;
	}
	var item_index = 'dummy'+element.counter;

	new Ajax.Updater(element, $K.appdir+'element_autopublishblock/',
	{
		onComplete: function() {
			$K.windows[wid].refresh("col1");
			$K.yg_customAttributeHandler(innerContent);
		},
		asynchronous: true,
		evalScripts: true,
		method: 'post',
		insertion: position,
		parameters: {
			objectID: objectID,
			site: site,
			item_index: item_index,
			item_version: 9999,
			us: document.body.id,
			lh: $K.yg_getLastGuiSyncHistoryId()
		}
	});
}
