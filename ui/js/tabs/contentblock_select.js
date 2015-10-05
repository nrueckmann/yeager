/**
* Submits the window
* @param { String } [winID] The displaymode (dialog or empty)
* @param { String } [action] The action
* @param { String } [openerReference] The opener-reference
* @function
* @name $K.yg_submitCBlockWindow
*/
$K.yg_submitCBlockWindow = function(winID, action, openerReference) {

	var selectedNode = nlsTree['cblocks_tree'+winID+'_tree'].getSelNode();

	if (selectedNode.yg_id) {
		var target = selectedNode.yg_id;
	} else {
		var target = false;
	}
	
	if (target != false) {
		
		if (action!='choose') {
			var source = $K.windows[openerReference].yg_id;
			var opener = openerReference;
		}
		if (action=='copy') {			
			// For copy
			var data = Array ( 'noevent', {yg_property: 'copyCBlock', params: {
				sourceSite: source.split('-')[1],
				source: source.split('-')[0],
				targetSite: target.split('-')[1],
				target: target.split('-')[0],
				//recursive: recursive,
				openerRef: opener,
				orgAction: action
			} } );
			$K.yg_AjaxCallback( data, 'copyCBlock' );
		}
		if (action=='move') {
			// Check if function was fired from listview
			if ($K.windows[opener].tab == 'CONTENTBLOCK_LIST') {
				// Reset opener (set to tree-window)
				opener = $K.windows[opener].boundWindow;
			}
			
			// For move
			var data = Array ( 'noevent', {yg_property: 'moveCBlock', params: {
				sourceSite: source.split('-')[1],
				source: source.split('-')[0],
				targetSite: target.split('-')[1],
				target: target.split('-')[0],
				openerRef: opener,
				orgAction: action
			} } );
			$K.yg_AjaxCallback( data, 'moveCBlock' );
		}
		if (action=='restore') {
			// For restore
			source = $K.windows[openerReference].restore_yg_id;
			source.each(function(sourceItem, idx){
				var lastItem = 'false';
				if (idx == source.length-1) {
					lastItem = 'true';
				}
				var data = Array ( 'noevent', {yg_property: 'moveCBlock', params: {
					sourceSite: sourceItem.split('-')[1],
					source: sourceItem.split('-')[0],
					targetSite: target.split('-')[1],
					target: target.split('-')[0],
					openerRef: opener,
					orgAction: action,
					lastItem: lastItem
				} } );
				$K.yg_AjaxCallback( data, 'moveCBlock' );
			});
		}
		if (action=='choose') {
			if ($K.windows['wid_'+winID].loadparams.opener_reference.endsWith('property')) {
				// For properties
				$($K.windows['wid_'+winID].loadparams.opener_reference).update(selectedNode.capt);
				$K.yg_setObjectProperty( $($K.windows['wid_'+winID].loadparams.opener_reference), selectedNode.yg_id.split('-')[0] );
				$($K.windows['wid_'+winID].loadparams.opener_reference).up('.selectionmarker').next().value = selectedNode.yg_id.split('-')[0];
				$K.yg_fadeField( $($K.windows['wid_'+winID].loadparams.opener_reference).up('.cntblock') );
			} else {
				// For controls
				$K.yg_editControl( $($K.windows['wid_'+winID].loadparams.opener_reference).down('.title_txt'), '7', false, { yg_id: selectedNode.yg_id, title: selectedNode.capt } );	// 7 = Contentblock
				$K.yg_fadeField( $($K.windows['wid_'+winID].loadparams.opener_reference) );
			}
		}
	}
	
	$K.windows['wid_'+winID].remove();
}
