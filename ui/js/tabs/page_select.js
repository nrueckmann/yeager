/**
* Submits a page-chooser-window
* @param { String } [winID] The window id
* @param { String } [action] The action
* @param { Element } [opernerReference] The reference to the opener
* @function
* @name $K.yg_submitPages
*/
$K.yg_submitPages = function(winID, action, openerReference) {

	var selectedNode = nlsTree['pages_tree'+winID+'_tree'].getSelNode();

	var recursive = 0;
	if (action=='copy') {
		if ($('wid_'+winID+'_copyrecursive').value==1) {
			recursive = 1;
		}
	}

	if (selectedNode) {
		var target = selectedNode.yg_id;
	} else {
		var target = false;
	}
	
	if (target != false) {
		if (action!='choose') {
			var source = $K.windows[openerReference].yg_id;
		}
		if (action=='copy') {			
			// For copy
			var data = Array ( 'noevent', {yg_property: 'copyPage', params: {
				sourceSite: source.split('-')[1],
				source: source.split('-')[0],
				targetSite: target.split('-')[1],
				target: target.split('-')[0],
				recursive: recursive,
				openerRef: openerReference,
				orgAction: action
			} } );
			$K.yg_AjaxCallback( data, 'copyPage' );
		}
		if (action=='move') {
			// For move
			var data = Array ( 'noevent', {yg_property: 'movePage', params: {
				sourceSite: source.split('-')[1],
				source: source.split('-')[0],
				targetSite: target.split('-')[1],
				target: target.split('-')[0],
				openerRef: openerReference,
				orgAction: action
			} } );
			$K.yg_AjaxCallback( data, 'movePage' );
		}
		if (action=='restore') {
			// For restore
			source = $K.windows[openerReference].restore_yg_id;
			source.each(function(sourceItem, idx){
				var lastItem = 'false';
				if (idx == source.length-1) {
					lastItem = 'true';
				}
				var data = Array ( 'noevent', {yg_property: 'movePage', params: {
					sourceSite: sourceItem.split('-')[1],
					source: sourceItem.split('-')[0],
					targetSite: target.split('-')[1],
					target: target.split('-')[0],
					openerRef: openerReference,
					orgAction: action,
					lastItem: lastItem
				} } );
				$K.yg_AjaxCallback( data, 'movePage' );
			});
		}
		if (action=='choose') {
			if (document.forms[openerReference+'_LINK_SELECT_PAGE']) {
				// For choose (from link-chooser)
				document.forms[openerReference+'_LINK_SELECT_PAGE'].pagename.value = selectedNode.capt;
			} else if (openerReference.endsWith('property')) {
				// For properties
				$($K.windows['wid_'+winID].loadparams.opener_reference).update(selectedNode.capt);
				var pageInfo = {page: target.split('-')[0], site: target.split('-')[1]};
				$K.yg_setObjectProperty( $($K.windows['wid_'+winID].loadparams.opener_reference), pageInfo );
				$($K.windows['wid_'+winID].loadparams.opener_reference).up('.selectionmarker').next().value = Object.toJSON(pageInfo); 
				$K.yg_fadeField( $($K.windows['wid_'+winID].loadparams.opener_reference).up('.cntblock') );
			} else {
				// For controls
				$K.yg_editControl( $($K.windows['wid_'+winID].loadparams.opener_reference).down('.title_txt'), '15', false, { yg_id: selectedNode.yg_id, title: selectedNode.capt } );	// 7 = Contentblock
				$K.yg_fadeField( $($K.windows['wid_'+winID].loadparams.opener_reference) );
			}
			
			fieldReference = $K.windows['wid_'+winID].loadparams['field_reference'];
			if ($(fieldReference)) {
				var site = target.split('-')[1];
				var page = target.split('-')[0];
				
				var currentNode = selectedNode;
				var fullPath = currentNode.pname;
				while(currentNode.pr) {
					currentNode = currentNode.pr;
					fullPath = currentNode.pname + '/' + fullPath;
				}
				fullPath = $K.webroot + fullPath + '/';
				
				//$(fieldReference).value = $K.appdir+'page/'+site+'/'+page+'/';
				$(fieldReference).value = fullPath;
			}
		}
	}
	
	$K.windows['wid_'+winID].remove();
}
