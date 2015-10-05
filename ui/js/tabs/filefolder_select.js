/**
* Submits the file-chooser window
* @param { String } [winID] The window-id
* @param { String } [yg_id] The yg_id of the window
* @param { String } [openerReference] The reference to the opener
* @function
* @name $K.yg_submitFileWindow
*/
$K.yg_submitFileWindow = function(winID, action, openerReference) {
	var selectedNode = nlsTree['files_tree'+winID+'_tree'].getSelNode();
	var target = selectedNode.yg_id;
	if ((action!='choose')&&(action!='restore')&&(action!='choosefolder')) {
		var opener = openerReference;
		var source = $K.windows['wid_'+winID].loadparams.sourceYgId;
		if (!source) {
			source = nlsTree['files_tree'+opener.split('_')[1]+'_tree'].selNd.yg_id;
		}
	}
	if (action=='copy') {			
		// For copy
		var data = Array ( 'noevent', {yg_property: 'copyFile', params: {
			source: source.split('-')[0],
			target: target.split('-')[0],
			openerRef: opener,
			orgAction: action
		} } );
		$K.yg_AjaxCallback( data, 'copyFile' );
	}
	if (action=='move') {
		// For move
		var data = Array ( 'noevent', {yg_property: 'moveFile', params: {
			source: source.split('-')[0],
			target: target.split('-')[0],
			openerRef: opener,
			orgAction: action
		} } );
		$K.yg_AjaxCallback( data, 'moveFile' );
	}
	if (action=='restore') {
		// For restore
		var opener = openerReference;
		source = $K.windows[openerReference].restore_yg_id;
		source.each(function(sourceItem, idx){
			var lastItem = 'false';
			if (idx == source.length-1) {
				lastItem = 'true';
			}
			var data = Array ( 'noevent', {yg_property: 'moveFile', params: {
				source: sourceItem.split('-')[0],
				target: target.split('-')[0],
				openerRef: opener,
				orgAction: action,
				lastItem: lastItem
			} } );
			$K.yg_AjaxCallback( data, 'moveFile' );
		});
	}
	if (action=='choosefolder') {
		// formfield filefolder
		var data = new Array();
		data['yg_id'] = selectedNode.yg_id;
		data['title'] = selectedNode.capt;
		$K.yg_editControl($K.windows['wid_'+winID].loadparams['opener_reference'] , '16', false, data);
		$K.yg_fadeField($($K.windows['wid_'+winID].loadparams['opener_reference']).up('.maskedit'));
	}

	$K.windows['wid_'+winID].remove();
}
