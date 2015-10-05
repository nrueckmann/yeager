$K.yg_delViewArr = new Array();

/**
 * Initializes the view tab
 * @param { String } [pageID] The page id
 * @param { String } [parentWinID] The parent window id
 * @function
 * @name $K.yg_initViewsTab
 */
$K.yg_initViewsTab = function( pageID, parentWinID ) {

	var jsTemplate = $K.windows['wid_'+parentWinID].jsTemplate;

	$K.windows['wid_'+parentWinID].addViewToSortable = function( itemID, itemIdentifier, itemName, itemWidth, itemHeight ) {

		// Check if view is already there
		var alreadyAdded = false;

		if (!$('wid_'+parentWinID+'_tab_VIEWS_viewlist')) {
			return;
		}

		$('wid_'+parentWinID+'_tab_VIEWS_viewlist').select('li').each(function(viewItem){
			if (viewItem.readAttribute('yg_id') == (itemID+'-view')) {
				alreadyAdded = true;
			}
		});
		if (alreadyAdded) return;

		// Create template-chunk for view
		var item_template = $K.yg_makeTemplate( jsTemplate );
		var bracket = '<span class="bracket">[</span> - <span class="bracket">]</span>';

		if (itemWidth==0) itemWidth = bracket;
		if (itemHeight==0) itemHeight = bracket;

		var newView = item_template.evaluate({
			item_index: parentWinID+'_'+(Math.random()*1000),
			item_id: itemID,
			item_identifier: itemIdentifier,
			item_name: itemName,
			item_width: itemWidth,
			item_height: itemHeight
		});

		// Find position to insert the new element (alphabetically)
		var itemsArray = new Array();
		var newItem = itemName.toLowerCase() + '<<>>' + 'item_' + parentWinID + '_' + itemID;

		$('wid_'+parentWinID+'_tab_VIEWS_viewlist').select('li').each(function(item){
			if (item.readAttribute('yg_id') == "0-view") {
				// source gets to the top of the list
				itemsArray.push( '			 <<>>' + item.id );
			} else {
				itemsArray.push( item.down('.label').innerHTML.toLowerCase() + '<<>>' + item.id );
			}
		});
		itemsArray.push( newItem );
		itemsArray.sort();

		var targetPosition = itemsArray.indexOf(newItem);
		targetPosition--;

		if (targetPosition<0) {
			// Insert at start of list
			$('wid_'+parentWinID+'_tab_VIEWS_viewlist').insert({top:newView});
		} else {
			// Insert after specific element
			var targetId = itemsArray[targetPosition].split('<<>>')[1];
			$(targetId).insert({after:newView});
		}

		$K.yg_delViewArr[itemID] = function(delFileId, isFolder) {
			if ($K.yg_idlookuptable[itemID+'-view'] && $K.yg_idlookuptable[itemID+'-view'][0]) {
				$K.yg_cleanLookupTable();
				$K.yg_idlookuptable[itemID+'-view'].each(function(item){
					ulRef = item.up('ul');
					winRef = $(ulRef).up('.ywindow');
					if ( $K.windows[winRef.id].yg_id != (delFileId+'-file')) return;
					$K.yg_removefromFocus(item);
					if (item.tagName == 'LI') {
						item.remove();
					} else {
						item.up('li').remove();
					}
					$K.windows[winRef.id].refresh($(ulRef));
				});

				if (isFolder == true) {
					for (winItem in $K.windows) {
						if ( ($K.windows[winItem].tab == 'VIEWS') &&
									($K.windows[winItem].type == 'dialog') &&
									($K.windows[winItem].yg_type == 'file') ) {
							$K.windows[winItem].tabs.select( $K.windows[winItem].tabs.selected );
						}
					}
				}

			}
			$K.yg_delViewArr[itemID] = undefined;
		}

		$K.yg_customAttributeHandler( $('wid_'+parentWinID+'_tab_VIEWS_viewlist') );
		$K.initSortable('wid_'+parentWinID+'_'+pageID+'_viewlist');

		$K.windows['wid_'+parentWinID].refresh();
	}

	// Map delete functions
	$( 'wid_'+parentWinID+'_'+pageID+'_viewlist' ).select('li').each(function(viewlistItem){

		var view = viewlistItem.readAttribute('yg_id').split('-')[0];

		$K.yg_delViewArr[view] = function(delFileId, isFolder) {
			if ($K.yg_idlookuptable[view+'-view'] && $K.yg_idlookuptable[view+'-view'][0]) {
				$K.yg_cleanLookupTable();
				$K.yg_idlookuptable[view+'-view'].each(function(item){
					ulRef = item.up('ul');
					winRef = $(ulRef).up('.ywindow');
					if ( $K.windows[winRef.id].yg_id != (delFileId+'-file')) return;
					$K.yg_removefromFocus(item);
					if (item.tagName == 'LI') {
						item.remove();
					} else {
						item.up('li').remove();
					}
					$K.windows[winRef.id].refresh($(ulRef));
				});

				if (isFolder == true) {
					for (winItem in $K.windows) {
						if ( ($K.windows[winItem].tab == 'VIEWS') &&
									($K.windows[winItem].type == 'dialog') &&
									($K.windows[winItem].yg_type == 'file') ) {
							$K.windows[winItem].tabs.select( $K.windows[winItem].tabs.selected );
						}
					}
				}

			}
			$K.yg_delViewArr[view] = undefined;
		}

	});

}


/**
 * Add a view
 * @param { String } [fileID] The file id
 * @param { String } [itemID] The view id
 * @param { String } [itemName] The view name
 * @param { Integer } [itemWidth] The view width
 * @param { Integer } [itemHeight] The view height
 * @param { Boolean } [isFolder] True when added to a folder
 * @function
 * @name $K.yg_addView
 */
$K.yg_addView = function( fileID, itemID, itemIdentifier, itemName, itemWidth, itemHeight, isFolder ) {
	for (winItem in $K.windows) {
		if ( ( $K.windows[winItem].yg_id == (fileID+'-file')) && ($K.windows[winItem].tab == 'VIEWS') ) {
			if (typeof $K.windows[winItem].addViewToSortable == 'function') {
				$K.windows[winItem].addViewToSortable( itemID, itemIdentifier, itemName, itemWidth, itemHeight );
			}
		} else if ( ($K.windows[winItem].tab == 'VIEWS') &&
					($K.windows[winItem].yg_type == 'file') &&
					(isFolder == 'true') ) {
			$K.windows[winItem].tabs.select( $K.windows[winItem].tabs.selected );
		}
	}
}


/**
 * Add a generated view
 * @param { String } [fileID] The file id
 * @param { String } [viewIdentifier] The view identifier
 * @param { Integer } [itemWidth] The view width
 * @param { Integer } [itemHeight] The view height
 * @function
 * @name $yg_addGenerated
 */
$K.yg_addGenerated = function( fileID, viewIdentifier, itemWidth, itemHeight ) {
	if ($K.yg_idlookuptable[fileID+'-file'] && $K.yg_idlookuptable[fileID+'-file'][0]) {
		$K.yg_cleanLookupTable();

		$K.yg_idlookuptable[fileID+'-file'].each(function(item){
			var winRef = item.up('.ywindow');
			if (viewIdentifier == 'NULL') {
				if ( ($(winRef.id).hasClassName('filelist1') ||
					  $(winRef.id).hasClassName('filelist2') ||
					  $(winRef.id).hasClassName('filelist3')) &&
					 item.down('div.listthumb') ) {
					item.down('div.listthumb')
						.update('<div class="nothumb"><div class="noimg">?</div></div>');
				} else if ($(winRef.id).hasClassName('thumbview') && item.down('div.thumbcnt')) {
					item.down('div.thumbcnt')
						.removeClassName('thumbcnt_loaded')
						.addClassName('thumbcnt_nothumb')
						.update('<table cellspacing="0" cellpadding="0"><tbody><tr><td><div class="noimg">?</div></td></tr></tbody></table>');
				}
			} else {
				if ( item.down('img') &&
					 item.down('img').readAttribute('real_src') ) {

					if (item.down('img').readAttribute('real_src').indexOf('/'+viewIdentifier+'/')!=-1) {
						item.down('img').src = item.down('img').readAttribute('real_src');
						item.down('img').removeAttribute('real_src');

						if ($(winRef.id).hasClassName('thumbview')) {
							// Check alignment
							var alignment = '';
							if (itemWidth && itemHeight) {
								var ratioPic = (itemWidth / itemHeight);
								if (ratioPic > (4 / 3)) {
									alignment = 'x-scale';
								} else {
									alignment = 'y-scale';
								}
							}
							if (item.down('.mk_thumbnail')) {
								item.down('.mk_thumbnail').addClassName(alignment);
							}
						}
					}

				}
			}
		});
	}
}


/**
 * Saves all views
 * @param { String } [wndid] Id of parent window
 */
$K.yg_saveFileViews = function( which, winID ) {
	which = $(which);

	var hasError = false;
	var winRef = which.up('.ywindow');
	var listRef = $(winRef.id + '_CONFIG_VIEWS');
	var allFields = listRef.select('input');
	var parameters = {
			wid: winRef.id
	};

	allFields.each(function(item){
		if (item.name) {
			if (parameters[item.name] != undefined) {
				parameters[item.name] += ','+item.value;
			} else {
				parameters[item.name] = item.value;
			}
		}
	});

	// Check for double names/identifiers
	var names = new Array();
	var identifiers = new Array();
	var legalChars = /^[a-zA-Z0-9\-\.\_]*$/;

	for(parameter_idx in parameters) {
		if (parameter_idx.indexOf('___NEW___')!=-1) {
			continue;
		}
		if (parameter_idx.endsWith('_name')) {
			if ( (names.indexOf(parameters[parameter_idx]) != -1) ||
				 (parameters[parameter_idx].strip() == '') ) {
				var errField = $(document.forms['wid_'+winID+'_form'][parameter_idx]);
				errField.addClassName('error');
				if (errField.up('li').hasClassName('closed')) {
					$K.yg_listCollapsSwap(errField.up('li'), null);
				}
				hasError = true;
			} else {
				$(document.forms['wid_'+winID+'_form'][parameter_idx]).removeClassName('error');
			}
			names.push(parameters[parameter_idx]);
		}
		if (parameter_idx.endsWith('_identifier')) {
			if ( (identifiers.indexOf(parameters[parameter_idx]) != -1) ||
				 (!legalChars.test(parameters[parameter_idx])) ||
				 (parameters[parameter_idx].strip() == '') ) {
				var errField = $(document.forms['wid_'+winID+'_form'][parameter_idx]);
				errField.addClassName('error');
				if (errField.up('li').hasClassName('closed')) {
					$K.yg_listCollapsSwap(errField.up('li'), null);
				}
				hasError = true;
			} else {
				$(document.forms['wid_'+winID+'_form'][parameter_idx]).removeClassName('error');
			}
			identifiers.push(parameters[parameter_idx]);
		}
	}

	if (!hasError) {
		var data = Array ( 'noevent', {yg_property: 'saveViews', params: parameters } );
		$K.yg_AjaxCallback( data, 'saveViews' );
	}

}


/**
 * Adds a new empty view
 * @param { Element } [ref] The element where was clicked on.
 * @function
 * @name yg_addNewView
 */
$K.yg_addNewView = function( ref ) {
	ref = $(ref);

	var wid = ref.up('.ywindow').id;
	var newElement = $K.windows[wid].jsTemplate;
	var newId = 0;

	if (!$(wid+'_views_list')) {
		return;
	}

	$(wid+'_views_list').select('input[type=hidden]').each(function(item){
		if (item.name.endsWith('_view_ids[]') && item.value.startsWith('NEW_')) {
			newId = parseInt(item.value.replace(/NEW_/,''));
		}
	});
	newId++;
	newElement = newElement.replace(/__NEW__/g, 'NEW_'+newId);
	$(wid+'_views_list').insert({bottom:newElement});

	var listChildren = $(wid+'_views_list').childElements();
	newElement = listChildren[listChildren.length-1];
	$K.yg_customAttributeHandler( newElement );
	$K.windows[wid].refresh();
}



/**
 * Updates the title of the currently selected element
 * @param { Element } [which] The element which fired the event.
 * @function
 * @name $K.yg_updateViewTitle
 */
$K.yg_updateViewTitle = function(which) {
	which = $(which);
	if (which.up('li').down('.handler')) {
		which.up('li').down('.handler').update(which.value);
	}
}


/**
 * Updates the sizes of the currently selected element
 * @param { Element } [which] The element which fired the event.
 * @function
 * @name $K.yg_updateViewSizes
 */
$K.yg_updateViewSizes = function(which) {
	which = $(which);

	var fieldPrefix = which.id.replace(/_width/g,'').replace(/_height/g,'');
	var newDesc = $(fieldPrefix+'_width').value + ' x ' + $(fieldPrefix+'_height').value;

	if (which.up('li').down('.desc')) {
		which.up('li').down('.desc').update(newDesc);
	}
}


/**
 * Opens view-chooser dialog
 * @function
 * @name $K.yg_openAddViewWindow
 */
$K.yg_openAddViewWindow = function ( openerReference, ygId ) {
	new $K.yg_wndobj({ config: 'VIEW_SELECT', openerYgId: ygId, loadparams: { opener_reference: openerReference } } );
}


/**
 * Callback function for sortable list
 * @name $K.viewsSortCallbacks
 */
$K.viewsSortCallbacks = {
	onUpdate: function(element) {
		$K.yg_updateSortables();
	}
};
