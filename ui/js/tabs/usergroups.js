/**
 * @fileoverview Provides functionality for managing usergroups
 * @version 1.0
 */

/**
 * Initializes the usergroups tab
 * @param { String } [pageID] The page id
 * @param { String } [parentWinID] The parent window id
 * @function
 * @name $K.yg_initUsergroupsTab
 */
$K.yg_initUsergroupsTab = function( pageID, parentWinID ) {
	var readOnly = true;
	if ($('wid_'+parentWinID+'_'+pageID+'_usergrouplist').hasClassName('perm_RWRITE')) {
		readOnly = false;
	}

	$K.windows['wid_'+parentWinID].addToSortable = function ( role_id, role_name, role_target, role_position ) {

		openerRef = $('wid_'+parentWinID+'_'+pageID+'_usergrouplist');

		var newIdSuffix = openerRef.childNodes.length+1;

		// Create template-chunk
		var item_template = $K.yg_makeTemplate( $K.windows['wid_'+parentWinID].jsTemplate );

		// Fill template with variables
		var newRole = item_template.evaluate( {	item_index: newIdSuffix,
												new_id: role_id.split('-')[0],
												new_name: role_name } );

		if ( (role_position!='') && (role_position!='into') ) {
			var x = openerRef.childNodes;
			for (var i=0;i<x.length;i++) {
				var actUsergroup = x[i].readAttribute('yg_id').split('-');
				actUsergroup = actUsergroup[0];
				if (actUsergroup==role_target) {
					if (role_position == 'before') {
						openerRef.childNodes[i].insert({before:newRole});
						i++;
					}
					if (role_position == 'after') {
						openerRef.childNodes[i].insert({after:newRole});
					}
				}
			}
		} else {
			openerRef.insert(newRole);
		}

		$K.yg_customAttributeHandler( openerRef );

		$K.windows[$(openerRef).up('.ywindow').id].refresh($(openerRef));
		$K.initSortable( openerRef );
	}

	$K.windows['wid_'+parentWinID].addFunction = $K.windows['wid_'+parentWinID].addUserGroup = function (openerRef, roleId, refresh) {
		if ($K.windows[openerRef].loadparams.yg_type == 'mailing') {
			var mailingId = $K.windows[openerRef].yg_id.split('-')[0];
			var params = {
				mode: 'mailing',
				mailingId: mailingId,
				roleId: roleId,
				openerRefId: openerRef,
				refresh: refresh.toString()
			};
		} else {
			var userId = $K.windows[openerRef].yg_id.split('-')[0];
			var params = {
				mode: 'user',
				userId: userId,
				roleId: roleId,
				openerRefId: openerRef,
				refresh: refresh.toString()
			};
		}

		var data = Array ( 'noevent', {yg_property: 'addUsergroup', params: params } );
		$K.yg_AjaxCallback( data, 'addUsergroup' );
	}
}


/**
 * Selects and submits selected usergroups
 * @param { Element } [winID] Window-Id
 * @param { Element } [openerReference] Reference to the opener
 */
$K.yg_selectRole = function(winID, openerReference) {
	var openerWid = $(openerReference).up('.ywindow').id;

	// Check if multiselect
	var focusobjs = $K.yg_getFocusObj($('wid_'+winID));

	var userIDs = new Array();
	if (focusobjs.length > 0) {
		focusobjs.each(function(item) {
			userIDs.push( item.up('li').readAttribute('yg_id').split('-')[0] );
		});
	} else if ($K.windows['wid_'+winID].yg_id != undefined) {
		// For insertion of extensions
		var yg_id = $K.windows['wid_'+winID].yg_id;
		var userID = yg_id.split('-')[0];
		userIDs.push(userID);
	}

	if (userIDs.length == 0) {
		$K.windows['wid_'+winID].remove();
		return;
	}

	if (typeof $K.windows[openerWid].addUserGroup == 'function') {
		userIDs.each(function(userIDItem){
			$K.windows[openerWid].addUserGroup(openerWid, userIDItem, true);
		});
	}

	$K.windows['wid_'+winID].remove();
}


/**
 * Callback function for sortable list
 * @name $K.usergrouplistSortCallbacks
 */
$K.usergrouplistSortCallbacks = {
	onUpdate: function(element) {
		var listArray = Array();
		for (var i=0;i<element.childNodes.length;i++) {
			var roleID = element.childNodes[i].readAttribute('yg_id').split('-');
			roleID = roleID[0];
			listArray.push( roleID );
		}

		var parentWin = $K.windows[this.element.up('.ywindow').id];
		var siteID = parentWin.yg_id.split('-')[1];
		var objectID = parentWin.yg_id.split('-')[0];

		var data = Array ( 'noevent', {yg_property: 'orderObjectUsergroup', params: {
			objectID: objectID,
			site: siteID,
			listArray: listArray
		} } );
		$K.yg_AjaxCallback( data, 'orderObjectUsergroup' );
	}
};
