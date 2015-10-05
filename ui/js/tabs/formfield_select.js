/**
* Submits the formfields window
* @param { String } [winID] The window-id
* @param { String } [openerReference] The reference to the opener
* @param { String } [displayMode] The display-mode (dialog or empty)
* @function
* @name $K.yg_submitFormfields
*/
$K.yg_submitFormfields = function(winID, openerReference, displayMode) {

	var listName = '_formfields_list';

	var selectedItems = $K.yg_getFocusObj($('wid_'+winID));
	var openerWin = $(openerReference);

	// Get current highest index for entry
	var maxIdx = 1;
	$(openerWin.id+listName).childElements().each(function(childItem){
		var elemIdx = parseInt(childItem.id.split('_').last());
		if (elemIdx > maxIdx) maxIdx = elemIdx;
	});
	maxIdx++;

	var realSelectedItems = new Array();

	if (selectedItems.length > 0) {
		selectedItems.each(function(item){
			if ( !item || !item.up('li') || !item.up('ul') || (item.up('ul').id != 'wid_'+winID+'_formfieldlist') ) {
				return;
			}
			realSelectedItems.push(item);
		});
	}
	selectedItems = realSelectedItems;

	var formfieldIds = new Array();
	selectedItems.each(function(item) {
		formfieldIds.push(item.up('li').readAttribute('yg_id').split('-')[0]);
	});

	if ($K.windows['wid_'+winID].yg_id) {
		if (formfieldIds.indexOf($K.windows['wid_'+winID].yg_id.split('-')[0]) == -1) {
			formfieldIds = new Array();
			formfieldIds.push( $K.windows['wid_'+winID].yg_id.split('-')[0] );
		}
	}

	formfieldIds.each(function(formfieldId){
		switch (displayMode) {
			case 'properties':
				// Create template-chunk
				var item_template = $K.yg_makeTemplate( $K.windows[openerWin.id].formfieldTemplates[formfieldId] );

				// Fill template with variables
				var newFormfield = item_template.evaluate( {new_idx: maxIdx, new_id: '__NEW_ID_'+maxIdx+'__'} );
				$(openerWin.id+listName).insert({bottom:newFormfield});
				newFormfield.evalScripts();
				$K.yg_customAttributeHandler( $(openerWin.id+'_formfield_'+maxIdx) );
				break;
			case 'normal':
			default:
				// Create template-chunk
				var item_template = $K.yg_makeTemplate( $K.windows[openerWin.id].formfieldTemplates[formfieldId] );

				// Fill template with variables
				var newFormfield = item_template.evaluate( {new_idx: maxIdx, new_id: '__NEW_ID_'+maxIdx+'__'} );
				$(openerWin.id+listName).insert({bottom:newFormfield});
				newFormfield.evalScripts();
				$K.yg_customAttributeHandler( $(openerWin.id+'_formfield_'+maxIdx) );
				$K.yg_hilite('entrymask', $K.windows[openerWin.id].yg_id, 'name', true);
				break;
		}
		maxIdx++;
	});

	$K.initSortable(openerWin.id+listName);
	$K.windows[openerWin.id].refresh();
	$K.windows['wid_'+winID].remove();
};


/**
* Callback function for sortable list
* @name $K.formFieldsSortCallbacks
*/
$K.formFieldsSortCallbacks = {
	onUpdate: function(element) {
		var parentWin = $K.windows[this.element.up('.ywindow').id];

		$K.initSortable($(parentWin.loadparams.opener_reference + '_formfields_list'));
	}
};


/**
* Callback function for sortable list
* @name $K.formFieldListSortCallbacks
*/
$K.formFieldListSortCallbacks = {
	onCreate: function(element) {
		$K.yg_initSortable(this.up('.sortlist').id);
	},
	onUpdate: function(element) {
		$K.yg_updateSortables();
	}
};
