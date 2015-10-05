/**
 * Inititalzes the tags tab
 * @param { String } [pageID] The id of the parent page
 * @param { String } [parentWinID] The window id
 * @function
 * @name $K.yg_initTagsTab
 */
$K.yg_initTagsTab = function( pageID, parentWinID ) {

	var jsTemplate = $K.windows['wid_'+parentWinID].jsTemplate;

	$K.windows['wid_'+parentWinID].addToSortable = function ( tag_id, tag_name, tag_path, tag_target, tag_position ) {

		openerRef = $('wid_'+parentWinID+'_'+pageID+'_list');

		var newIdSuffix = openerRef.childNodes.length+1;
		tag_path = tag_path.join(' <span class="traceicon"></span> ');

		// Create template-chunk
		var item_template = $K.yg_makeTemplate( jsTemplate );

		// Fill template with variables
		var newTag = item_template.evaluate( {	item_index: newIdSuffix,
												item_id: tag_id,
												item_name: tag_name,
												item_trace: tag_path } );

		if ( (tag_position!='') && (tag_position!='into')) {
			var x = openerRef.childNodes;
			for (var i=0;i<x.length;i++) {
				var actTag = x[i].readAttribute('yg_id').split('-');
				actTag = actTag[0];
				if (actTag==tag_target) {
					if (tag_position == 'before') {
						openerRef.childNodes[i].insert({before:newTag});
						i++;
					}
					if (tag_position == 'after') {
						openerRef.childNodes[i].insert({after:newTag});
					}
				}
			}
		} else {
			openerRef.insert(newTag);
		}

		$K.yg_customAttributeHandler( openerRef );

		$K.initSortable( openerRef );

		$K.windows[$(openerRef).up('.ywindow').id].refresh($(openerRef));
	}

	$K.windows['wid_'+parentWinID].addFunction = $K.windows['wid_'+parentWinID].addTag = function (tagId, targetTagId, targetPosition) {

		var targetSuffix = '';
		if ( (targetTagId!=undefined) && (targetPosition!=undefined) ) {
			targetSuffix = '-' + targetTagId + '-' + targetPosition;
		}

		var site = $K.windows['wid_'+parentWinID].yg_id.split('-')[1];
		var objectID = pageID;

		var data = Array ( 'noevent', {yg_property: 'addObjectTag', params: {
			objectID: objectID,
			objectType: $K.windows['wid_'+parentWinID].yg_type,
			site: site,
			tagId: tagId.split('-')[0],
			targetId: (targetTagId)?(targetTagId):(''),
			targetPosition: (targetPosition)?(targetPosition):('')
		} } );
		$K.yg_AjaxCallback( data, 'addObjectTag' );
	}

}


/**
* Core function used to refresh tags on an object based on type, yg_id and property
* @type Boolean
* @param { String } [yg_type] The type of the element.
* @param { String } [yg_id] The id of the element.
* @param { String } [yg_property] The property for adressing.
* @param { String } [tags] The tags to refresh (JSON array)
*/
$K.yg_addTag = function(yg_type, yg_id, yg_property, newId, newName, parentObjs, targetId, targetPosition) {

	// Garbage-Collection
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				// Tree item
			} else {
				var winID = $K.yg_idlookuptable[yg_id][i].up('.ywindow').id;
				if ($K.windows[winID] && (typeof $K.windows[winID].addToSortable == 'function' )) {
					$K.windows[winID].addToSortable( newId, newName, parentObjs, targetId, targetPosition );
				}

			}
		}
	}
	return false;
}


/**
* Add child node to the currently selected node
* @param { Element } [tagref] The element from which the function was called.
* @function
* @name $K.yg_addChildTag
*/
$K.yg_addChildTag = function( tagref ) {

	// Topbar buttons or actionbuttons?
	if (tagref.hasClassName('tree_btn')) {
		if (tagref.hasClassName('disabled')) return;
		var tag = $K.windows[tagref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt( tagref.up('.ywindow').id.replace(/wid_/g, '') );
		var nodeid = tagref.id;
		var tag = nlsTree['tags_tree'+wid+'_tree'].nLst[nodeid].yg_id;
	}

	tag = tag.split('-')[0];

	var data = Array ( 'noevent', {yg_property: 'addTagChildFolder', params: {
		tag: tag
	} } );
	$K.yg_AjaxCallback( data, 'addTagChildFolder' );
}


/**
* Wrapper for above functions when mapped in actionbuttons
* @param { Element } [which] The element from which the function was called.
* @function
* @name $K.yg_actionAddChildTag
*/
$K.yg_actionAddChildTag = function( which ) { $K.yg_addChildTag( which.up(2).reference ); }


/**
* Wrapper for above functions when mapped in actionbuttons
* @param { Element } [which] The element from which the function was called.
* @param { Boolean } [multi] True if multiple items are selected.
* @function
* @name $K.yg_actionDeleteTag
*/
$K.yg_actionDeleteTag = function( which, multi ) { $K.yg_deleteElement( which.up(2).reference, multi ); }


/**
 * Submits the tags chooser
 * @param { String } [winID] The window id
 * @param { String } [action] The action
 * @param { String } [openerWin] The opener window
 * @function
 * @name $K.yg_initTagsTab
 */
$K.yg_submitTags = function(winID, action, openerWin) {

	if ($K.windows['wid_'+winID].yg_id == undefined) {
		$K.windows['wid_'+winID].remove();
		return;
	}

	var selectedNodes = nlsTree['tags_tree'+winID+'_tree'].getSelNodes();

	// Protect against selection of rootnode
	if ( ($K.windows['wid_'+winID].yg_id == '1-tag') ||
		 ($K.windows['wid_'+winID].yg_id == '-tag') ) {
		return;
	}

	if (action=='choose') {
		// Only Choose
		if (!selectedNodes.capt) {
			title = selectedNodes[0].capt;
		} else {
			title = selectedNodes.capt;
		}

		if ($K.windows['wid_'+winID].loadparams.opener_reference.endsWith('property')) {
			// For properties
			$($K.windows['wid_'+winID].loadparams.opener_reference).update(title);
			$K.yg_setObjectProperty( $($K.windows['wid_'+winID].loadparams.opener_reference), $K.windows['wid_'+winID].yg_id.split('-')[0] );
			$($K.windows['wid_'+winID].loadparams.opener_reference).up('.selectionmarker').next().value = $K.windows['wid_'+winID].yg_id.split('-')[0];
			$K.yg_fadeField( $($K.windows['wid_'+winID].loadparams.opener_reference).up('.cntblock') );
		} else {
			// For controls
			$K.yg_editControl( $K.windows['wid_'+winID].loadparams.element, $K.windows['wid_'+winID].loadparams.formfield, false, { yg_id: $K.windows['wid_'+winID].yg_id, title: title });
			$K.yg_fadeField( $K.windows['wid_'+winID].loadparams.element.up('.maskedit') );
		}

		$K.windows['wid_'+winID].remove();
	} else {
		// Add Tag to Tagpanel
		if ($(openerWin)) {
			var tagListRef = $(openerWin).down('.tag_list').id;
			for (var i=0;i<selectedNodes.length;i++) {
				// Protect against selection of rootnode
				if ( (selectedNodes[i].yg_id != '1-tag') &&
					 (selectedNodes[i].yg_id != '-tag') ) {
					$K.windows[openerWin].addTag( selectedNodes[i].yg_id, tagListRef );
				}
			}
			if ( (selectedNodes.length > 1) || (selectedNodes[0].yg_id != '1-tag') ) {
				$K.windows['wid_'+winID].remove();
			}
		}
	}

}


/**
 * Submits the add-tag window
 * @param { String } [winID] The window id
 * @param { String } [openerReference] The reference to the opener
 * @function
 * @name $K.yg_submitAddTag
 */
$K.yg_submitAddTag = function( winID, openerReference ) {

	if (!$('tags_tree'+openerReference.replace(/wid_/,'')+'_actionbutton')) {
		$K.windows['wid_'+winID].remove();
		return;
	}

	var tagtitle = $('wid_'+winID+'_tagname').value.strip();
	var openerWin = $(openerReference);
	var openerTree = nlsTree['tags_tree'+openerReference.replace(/wid_/,'')+'_tree'];

	if (tagtitle.length<1) {
		$('wid_'+winID+'_tagname').addClassName('error');
		return;
	} else {
		$('wid_'+winID+'_tagname').removeClassName('error');
	}

	if (openerTree.getSelNode()) {
		var parentNode = openerTree.getSelNode().yg_id.split('-')[0];
	} else {
		var refId = $('tags_tree'+openerReference.replace(/wid_/,'')+'_actionbutton').reference.id;
		var parentNode = nlsTree['tags_tree'+openerReference.replace(/wid_/,'')+'_tree'].nLst[refId].yg_id.split('-')[0];
	}

	$('wid_'+winID+'_tagname').value = '';

	var data = Array ( 'noevent', {yg_property: 'addTagChildFolder', params: {
		tag: parentNode,
		tagName: tagtitle
	} } );
	$K.yg_AjaxCallback( data, 'addTagChildFolder' );

	$K.yg_fadeField($('wid_'+winID+'_tagname'));

}


/**
* Callback function for sortable list
* @name $K.tagsSortCallbacks
*/
$K.tagsSortCallbacks = {
	onUpdate: function(element) {
		var listArray = Array();
		for (var i=0;i<element.childNodes.length;i++) {
			var tagId = element.childNodes[i].readAttribute('yg_id').split('-');
			tagId = tagId[0];
			listArray.push( tagId );
		}

		var parentWin = $K.windows[this.element.up('.ywindow').id];
		if (parentWin.yg_id) {
			var siteID = parentWin.yg_id.split('-')[1];
			var objectID = parentWin.yg_id.split('-')[0];
			var objectType = parentWin.yg_type;

			var data = Array ( 'noevent', {yg_property: 'orderObjectTag', params: {
				objectID: objectID,
				objectType: objectType,
				site: siteID,
				listArray: listArray
			} } );
			$K.yg_AjaxCallback( data, 'orderObjectTag' );

			$K.yg_updateSortables();
		}
	}
};
