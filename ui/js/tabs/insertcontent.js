/**
 * Submits the entrymasks window
 * @param { String } [winID] The window-id
 * @param { String } [openerReference] The reference to the opener
 * @function
 * @name $K.yg_submitEntrymasks
 */
$K.yg_submitEntrymasks = function(winID, openerReference) {

	if ( $K.windows['wid_'+winID].yg_id == undefined ) {
		$K.windows['wid_'+winID].remove();
		return;
	}

	var selectedNodes = nlsTree['entrymasks_tree'+winID+'_tree'].getSelNodes();
	var opener_wid = $(openerReference).up('.ywindow').id;
	var contentarea = $(openerReference).up('.mk_contentarea').id;

	for (var i=0;i<selectedNodes.length;i++) {
		var emask_id = selectedNodes[i].yg_id.split('-')[0];
		if (typeof $K.windows[opener_wid].addEntryMask == 'function') {
			$K.windows[opener_wid].addEntryMask( emask_id, contentarea, true );
		}
	}

	$('wid_'+winID).hide();
}


/**
 * Submits the cblocks window
 * @param { String } [winID] The window-id
 * @param { String } [openerReference] The reference to the opener
 * @function
 * @name $K.yg_submitCBlocks
 */
$K.yg_submitCBlocks = function(winID, openerReference) {

	if ( $K.windows['wid_'+winID].yg_id == undefined ) {
		$K.windows['wid_'+winID].remove();
		return;
	}

	var opener_wid = $(openerReference).up('.ywindow').id;
	var selectedNodes = nlsTree['cblocks_tree'+winID+'_tree'].getSelNodes();
	var contentarea = $(openerReference).up('.mk_contentarea').id;

	for (var i=0;i<selectedNodes.length;i++) {
		var coblock_id = selectedNodes[i].yg_id.split('-')[0];
		if (typeof $K.windows[opener_wid].addContentBlock == 'function') {
			$K.windows[opener_wid].addContentBlock( coblock_id, contentarea, true );
		}
	}
	$('wid_'+winID).hide();
}


/**
 * Submits the copy-content window
 * @param { String } [winID] The window-id
 * @param { String } [openerReference] The reference to the opener
 * @function
 * @name $K.yg_submitCopyContent
 */
$K.yg_submitCopyContent = function(winID, openerReference) {

	var focusobjs = $K.yg_getFocusObj($('wid_'+winID+'_column2innercontent'));

	if (focusobjs.length == 0) {
		$K.log( 'Nothing selected!', $K.Log.INFO );
		return;
	} else {
		for (var i=0;i<focusobjs.length;i++) {
			var currentFocusObj = focusobjs[i];
			var backendFunction;

			var openerRefID = openerReference.split('_')[1];
			var contentareaID = openerReference.split('_')[3];
			var pageID = $K.windows['wid_'+openerRefID].yg_id.split('-')[0];
			var siteID = $K.windows['wid_'+openerRefID].yg_id.split('-')[1];
			var objectID = currentFocusObj.up('li').readAttribute('yg_id').split('-')[0];

			var treeMode = $K.windows['wid_'+winID].loadparams.treemode;
			if ((treeMode == 'pages')||(treeMode == 'pages_with_cblocks')) {
				objectID = currentFocusObj.id.split('_')[3];
			}

			// Check if entrymask or contentblock should be copies
			backendFunction = 'addPageContentblock';
			if (currentFocusObj.id.endsWith('_cblock__')) {
				backendFunction = 'addPositionedPageEntrymask';
			}

			var data = Array ( 'noevent', {yg_property: backendFunction, params: {
				page: pageID,
				site: siteID,
				copymode: true,
				entrymaskId: objectID,
				contentblockId: objectID,
				contentareaID: contentareaID,
				openerRefID: openerRefID,
				targetId: null,
				targetPosition: 'into',
				refresh: 'true'
			} } );

			$K.yg_AjaxCallback( data, backendFunction );
		}
	}

	$('wid_'+winID).hide();
}
