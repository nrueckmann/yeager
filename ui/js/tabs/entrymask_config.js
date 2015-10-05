/**
 * Function for adding formfields to formfield-list
 * @param { String } [winID] The window-id
 * @param { Element } [element] The target-element
 * @param { Element } [sourceElement] The source-element
 * @function
 * @name $K.yg_addFormfieldToList
 */
$K.yg_addFormfieldToList = function( winID, element, sourceElement ) {

	$K.yg_updateSortables();
	var activeDragInfo = $K.yg_activeDragInfo;

	var target = $K.yg_activeDragInfo.target;
	var position = activeDragInfo.position;
	if (position == 'after') $K.yg_currentdragobj.reverse();

	var formfieldId = sourceElement.up('li').readAttribute('yg_id').split('-')[0];

	// Get current highest index for entry
	var maxIdx = 1;

	element.childElements().each(function(childItem){
		var elemIdx = parseInt(childItem.id.split('_').last());
		if (elemIdx > maxIdx) maxIdx = elemIdx;
	});
	maxIdx++;

	// Create template-chunk
	var item_template = $K.yg_makeTemplate( formfieldTemplates[formfieldId] );

	// Fill template with variables
	var newFormfield = item_template.evaluate( {new_idx: maxIdx, new_id: '__NEW_ID_'+maxIdx+'__'} );

	switch(position) {
		case 'before':
			target.insert({before:newFormfield});
			break;
		case 'after':
			target.insert({after:newFormfield});
			break;
		default:
		case 'into':
			target = element;
			target.insert({bottom:newFormfield});
			break;
	}

	$K.yg_customAttributeHandler( element );

	$K.yg_drawTopborder( null, true );

	newFormfield.evalScripts();

	$K.windows[winID].refresh();

	$K.initSortable( winID+'_formfields_list' );
}


/**
 * Initializes the entrymask-configuration-tab
 * @param { String } [winID] The window-id
 * @function
 * @name $K.yg_initEntrymaskConfigTab
 */
$K.yg_initEntrymaskConfigTab = function( winID ) {
	if (!$( 'wid_'+winID+'_formfields_list' )) return;
	var formfieldTemplates = $K.windows['wid_'+winID].formfieldTemplates;
}


/**
 * Collects all values from inputfields and textareas and submits them to the backend for saving.
 * @param { Element } [which] Reference to the button which was clicked
 */
$K.yg_saveEntryMaskFormfields = function( which ) {
	which = $(which);

	$K.yg_fireLateOnChange();

	var winRef = which.up('.ywindow');
	var winID = winRef.id;
	var hasError = false;
	var identifierBlackList = [
		'URL', 'PAGE_ID', 'SITE_ID', 'FILE_ID',
		'CBLOCK_ID', 'TAG_ID', 'VALUE', 'LINKID',
		'ENTRYMASKID', 'CODE', 'ENTRYMASKNAME', 'IDENTIFIER',
		'TYPE', 'ID', 'FORMFIELD', 'ENTRYMASKFORMFIELD',
		'LNK', 'NAME', 'CBID', 'PRESET',
		'WIDTH', 'MAXLENGTH', 'CONFIG', 'CUSTOM',
		'VALUE01', 'VALUE02', 'VALUE03', 'VALUE04',
		'VALUE05', 'VALUE06', 'VALUE07', 'VALUE08'
	];

	// Get 'unique' values (they should be unique)
	var uniqueValueElements = new Array();
	var frmElements = $(winID+'_ENTRYMASKCONFIG').select('input', 'textarea');
	frmElements.each(function(item) {

		if (item.name == 'code') {
			if (item.value.strip().indexOf(' ')!=-1) {
				item.addClassName('error');
				hasError = true;
			} else {
				item.removeClassName('error');
			}
		}

		if (item.readAttribute('unique') != null) {
			var isNotUnique = false;
			uniqueValueElements.push(item);
			uniqueValueElements.each(function(unique_item){
				if ( ((unique_item != item) && (unique_item.value == item.value)) ||
					 (identifierBlackList.indexOf(item.value.toUpperCase()) != -1) ) {
					isNotUnique = true;
				}
				if (item.value.strip().indexOf(' ')!=-1) {
					isNotUnique = true;
				}
			});
			if (isNotUnique) {
				item.addClassName('error');
				item.isNotUnique = true;
			} else {
				if (item.name != 'code') {
					item.removeClassName('error');
					item.isNotUnique = false;
				}
			}
		}
	});
	frmElements.each(function(item) {
		if ((item.readAttribute('unique') != null) && (item.isNotUnique)) {
			item.addClassName('error');
		}
		if ((item.readAttribute('mandatory') != null) && (item.value.strip() == '')) {
			item.addClassName('error');
		} else if (item.readAttribute('mandatory') && (item.value.strip() != '') && (!item.isNotUnique)) {
			if (item.name != 'code') {
				item.removeClassName('error');
			}
		}
		if ( (item.readAttribute('mandatory') != null) &&
			 !item.name.endsWith('-NAME') &&
			 (item.value.indexOf('-') != -1) ) {
			item.addClassName('error');
		}
		if (item.hasClassName('error')) {
			hasError = true;
			if (item.up('li') && !item.up('li').hasClassName('opened')) {
				$K.yg_listCollapsSwap(item.up('li'));
			}
		}
	});

	if (hasError) {
		$(winID).down('.error').focus();
	}
	var parameters = Form.serializeElements(frmElements, {hash:true, submit:false});

	parameters.objectID = $K.windows[winID].yg_id.split('-')[0];
	parameters.wid = winID;

	if (!hasError) {
		var data = Array ( 'noevent', {yg_property: 'entrymaskSaveConfig', params: parameters } );
		$K.yg_AjaxCallback( data, 'entrymaskSaveConfig' );
	}
}


/**
 * Remove the currently selected Element
 * @param { Element } [which] The element (li) to remove.
 * @function
 * @name $K.yg_removeEntry
 */
$K.yg_removeEntry = function(which) {
	which = $(which);

	var winID = which.up('.ywindow').id;

	which.remove();

	$K.windows[winID].refresh();

	$K.yg_hilite('entrymask', $K.windows[winID].yg_id, 'name');
}


/**
 * Remove the currently selected Element from a List
 * @param { Element } [which] The element (li) to remove.
 * @function
 * @name $K.yg_removeListEntry
 */
$K.yg_removeListEntry = function(which) {
	which = $(which);

	var formfieldContainer = which.up('.gridfixed');
	if (formfieldContainer.down('.dropdownbox')) {
		$K.yg_dropdownRemove(formfieldContainer.down('.dropdownbox'), which.down('input').value);
	}
	which.remove();
	$K.yg_updateInnerContent( formfieldContainer.down('.sortablelist') );
	$K.scrollbars[formfieldContainer.down('.sortlist').id].setBarSize();
}


/**
 * Updates the title of the currently selected element
 * @param { Element } [which] The element which fired the event.
 * @function
 * @name $K.yg_updateTitle
 */
$K.yg_updateTitle = function(which) {
	which = $(which);
	if (which.up('li').down('.desc')) {
		which.up('li').down('.desc').update(which.value);
	} else if (which.up('li').down('.icn')) {
		which.up('li').down('.icn').update(which.value);
	}
}


/**
 * Adds a new entry the "list" formfield
 * @param { Element } [which] The element which fired the event.
 * @function
 * @name $K.yg_addEntryToEntryList
 */
$K.yg_addEntryToEntryList = function (which) {
	which = $(which);

	var formfieldContainer = which.up('.gridfixed');

	var listContainer = formfieldContainer.down('ul');
	var listEntryTemplate = formfieldContainer.down('.entry_template').innerHTML;
	var inputField = which.up('tr').down('input');

	if (inputField.value.strip()=='') {
		inputField.addClassName('error');
		return;
	} else {
		inputField.removeClassName('error');
	}

	var maxEntryIdx = 1;
	var alreadyInList = false;
	listContainer.childElements().each(function(item){
		if (item.down('input').value == inputField.value.strip()) {
			alreadyInList = true;
		}
		var entryIdx = parseInt(item.readAttribute('yg_id').split('-')[0]);
		if (entryIdx > maxEntryIdx) {
			maxEntryIdx = entryIdx;
		}
	});
	maxEntryIdx++;

	if (alreadyInList) {
		inputField.addClassName('error');
		return;
	}

	if (formfieldContainer.down('.dropdownbox')) {
		$K.yg_dropdownInsert(formfieldContainer.down('.dropdownbox'), inputField.value, inputField.value, false);
		if (formfieldContainer.down('.dropdownbox').down('.dropdownlist').childElements().length == 1) {
			$K.yg_dropdownSelect(formfieldContainer.down('.dropdownbox'), formfieldContainer.down('.dropdownbox').down('.dropdownlist').down('div'));
		}
	}

	var listEntryNew = listEntryTemplate.replace(/__ID__/g, maxEntryIdx).replace(/__TITLE__/g, inputField.value).replace(/listitempagefocus/g,'listitempage');
	listContainer.insert({bottom:listEntryNew});
	inputField.value = '';

	$K.scrollbars[formfieldContainer.down('.sortlist').id].setBarSize();
	$K.yg_customAttributeHandler( listContainer );
	$K.initSortable(listContainer);
	$K.yg_updateInnerContent( formfieldContainer.down('.sortablelist') );
}


/**
 * Add child node to the currently selected node
 * @param { Element } [entrymask] The element from which the function was called.
 * @function
 * @name $K.yg_addChildEntrymask
 */
$K.yg_addChildEntrymask = function( entrymaskref ) {

	// Topbar buttons or actionbuttons?
	if (entrymaskref.hasClassName('tree_btn')) {
		if (entrymaskref.hasClassName('disabled')) return;
		var entrymask = $K.windows[entrymaskref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt( entrymaskref.up('.ywindow').id.replace(/wid_/g, '') );
		var nodeid = entrymaskref.id;
		var entrymask = nlsTree['entrymasks_tree'+wid+'_tree'].nLst[nodeid].yg_id;
	}

	entrymask = entrymask.split('-')[0];

	var data = Array ( 'noevent', {yg_property: 'addEntrymask', params: {
		entrymask: entrymask
	} } );
	$K.yg_AjaxCallback( data, 'addEntrymask' );

}


/**
 * Add child node to the currently selected node
 * @param { Element } [entrymaskref] The element from which the function was called.
 * @function
 * @name $K.yg_addChildEntrymaskFolder
 */
$K.yg_addChildEntrymaskFolder = function( entrymaskref ) {

	// Topbar buttons or actionbuttons?
	if (entrymaskref.hasClassName('tree_btn')) {
		if (entrymaskref.hasClassName('disabled')) return;
		var entrymask = $K.windows[entrymaskref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt( entrymaskref.up('.ywindow').id.replace(/wid_/g, '') );
		var nodeid = entrymaskref.id;
		var entrymask = nlsTree['entrymasks_tree'+wid+'_tree'].nLst[nodeid].yg_id;
	}

	entrymask = entrymask.split('-')[0];

	var data = Array ( 'noevent', {yg_property: 'addEntrymaskChildFolder', params: {
		entrymask: entrymask
	} } );
	$K.yg_AjaxCallback( data, 'addEntrymaskChildFolder' );

}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionAddChildEntrymask
 */
$K.yg_actionAddChildEntrymask = function( which ) { $K.yg_addChildEntrymask( which.up(2).reference ); }


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionAddChildEntrymaskFolder
 */
$K.yg_actionAddChildEntrymaskFolder = function( which ) { $K.yg_addChildEntrymaskFolder( which.up(2).reference ); }


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @param { Boolean } [multi] True if multiple items are selected.
 * @function
 * @name $K.yg_actionDeleteEntrymask
 */
$K.yg_actionDeleteEntrymask = function( which, multi ) { $K.yg_deleteElement( which.up(2).reference, multi ); }

