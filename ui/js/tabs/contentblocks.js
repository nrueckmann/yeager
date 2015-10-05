/**
 * Add child node to the currently selected node
 * @param { Element } [cblockref] The element from which the function was called.
 * @function
 * @name $K.yg_addChildCBlock
 */
$K.yg_addChildCBlock = function( cblockref ) {

	// Topbar buttons or actionbuttons?
	if (cblockref.hasClassName('tree_btn')) {
		if (cblockref.hasClassName('disabled')) return;
		var cblock = $K.windows[cblockref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt( cblockref.up('.ywindow').id.replace(/wid_/g, '') );
		var nodeid = cblockref.id;
		var cblock = nlsTree['cblocks_tree'+wid+'_tree'].nLst[nodeid].yg_id;
	}

	var site = cblock.split('-')[1];
	cblock = cblock.split('-')[0];

	var data = Array ( 'noevent', {yg_property: 'addCBlock', params: {
		cblock: cblock,
		site: site
	} } );
	$K.yg_AjaxCallback( data, 'addCBlock' );
}


/**
 * Add child node to the currently selected node
 * @param { Element } [fileref] The element from which the function was called.
 * @function
 * @name $K.yg_addChildCBlockFolder
 */
$K.yg_addChildCBlockFolder = function( cblockref ) {

	// Topbar buttons or actionbuttons?
	if (cblockref.hasClassName('tree_btn')) {
		if (cblockref.hasClassName('disabled')) return;
		var cblock = $K.windows[cblockref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt( cblockref.up('.ywindow').id.replace(/wid_/g, '') );
		var nodeid = cblockref.id;
		var cblock = nlsTree['cblocks_tree'+wid+'_tree'].nLst[nodeid].yg_id;
	}

	cblock = cblock.split('-')[0];

	var data = Array ( 'noevent', {yg_property: 'addCBlockChildFolder', params: {
		cblock: cblock
	} } );
	$K.yg_AjaxCallback( data, 'addCBlockChildFolder' );
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @param { Boolean } [multi] True if multiple items are selected.
 * @function
 * @name $K.yg_actionDeleteCBlock
 */
$K.yg_actionDeleteCBlock = function( which, multi ) { $K.yg_deleteElement( which.up(2).reference, multi ); }


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name yg_actionMoveCBlock
 */
$K.yg_actionMoveCBlock = function( which ) {
	var wid = parseInt( which.up('.ywindow').id.replace(/wid_/g, '') );
	if (which.readAttribute('yg_property') == 'listitem') {
		$K.windows['wid_'+wid].yg_id = which.readAttribute('yg_id');
	} else {
		$K.windows['wid_'+wid].yg_id = nlsTree['cblocks_tree'+wid+'_tree'].nLst[which.up(2).reference.id].yg_id;
	}
	new $K.yg_wndobj({ config: 'CBLOCK_MOVE', loadparams: { opener_reference: which.up('.ywindow').id } });
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionCopyCBlock
 */
$K.yg_actionCopyCBlock = function( which ) {
	var wid = parseInt( which.up('.ywindow').id.replace(/wid_/g, '') );
	if (which.readAttribute('yg_property') == 'listitem') {
		$K.windows['wid_'+wid].yg_id = which.readAttribute('yg_id');
	} else {
		$K.windows['wid_'+wid].yg_id = nlsTree['cblocks_tree'+wid+'_tree'].nLst[which.up(2).reference.id].yg_id;
	}
	new $K.yg_wndobj({ config: 'CBLOCK_COPY', loadparams: { opener_reference: which.up('.ywindow').id } });
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionAddChildCBlock
 */
$K.yg_actionAddChildCBlock = function( which ) { $K.yg_addChildCBlock( which.up(2).reference ); }


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionAddChildCBlockFolder
 */
$K.yg_actionAddChildCBlockFolder = function( which ) { $K.yg_addChildCBlockFolder( which.up(2).reference ); }


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionEditCBlock
 */
$K.yg_actionEditCBlock = function( which ) { which.up(2).reference.onclick(); }


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name yg_actionshowCBlockPreviewTree
 */
$K.yg_actionshowCBlockPreviewTree = function( which ) {
	var wid = parseInt( which.up(2).reference.up('.ywindow').id.replace(/wid_/g, '') );
	var nodeid = which.up(2).reference.id;
	var data = nlsTree['cblocks_tree'+wid+'_tree'].nLst[nodeid].yg_id.split('-');
	var cblock = data[0];

	$K.yg_preview({objecttype: 'cblock', id: cblock, version: 'working' });
}

/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 */
$K.yg_actionsSetCBlockPreviewHref = function( which ) {
	var wid = parseInt( which.up(2).reference.up('.ywindow').id.replace(/wid_/g, '') );
	var nodeid = which.up(2).reference.id;
	var data = nlsTree['cblocks_tree'+wid+'_tree'].nLst[nodeid].yg_id.split('-');
	var cblock = data[0];
	which.href = $K.appdir + "?preview=1&objecttype=cblock&id="+cblock+"&version=working";
}

/**
 * Change the SE-Friendly cblockname (PNAME)
 * @param { Element } [element] The element from which the function was called.
 * @function
 * @name $K.yg_changeCBlockPName
 */
$K.yg_changeCBlockPName = function( element ) {

	// Fix for Safari (firing onchange 2 times in a row)
	if ( Prototype.Browser.WebKit &&
		 $$('.ywindow.pbox.standard').length ) {
		return;
	}

	var value = element.value;
	var yg_id = element.getAttribute('yg_id');

	if (!element.oldvalue) {
		element.oldvalue = element.readAttribute('oldvalue');
	}

	// Check if name has really changed
	if (element.value == element.oldvalue) {
		return;
	}

	if (value.strip()=='') {
		element.value = element.oldvalue;
		$K.yg_promptbox( $K.TXT('TXT_CHANGE_CBLOCK_URL_TITLE'), $K.TXT('TXT_CHANGE_CBLOCK_URL_EMPTY'), 'alert');
		return;
	}

	if (!isNaN(value)) {
		element.value = element.oldvalue;
		$K.yg_promptbox( $K.TXT('TXT_CHANGE_CBLOCK_URL_TITLE'), $K.TXT('TXT_CHANGE_CBLOCK_URL_NUMERIC'), 'alert');
		return;
	}

	$K.yg_promptbox( $K.TXT('TXT_CHANGE_CBLOCK_URL_TITLE'), $K.TXT('TXT_CHANGE_CBLOCK_URL'), 'standard',
		function() {
			$K.yg_setEdited( element );
			element.setAttribute( 'yg_previous', value );

			var cblock = yg_id.split('-')[0];

			var data = Array ( 'noevent', { yg_property: 'setCBlockPName', params: {
				value: value,
				cblock: cblock
			} } );
			$K.yg_AjaxCallback( data, 'setCBlockPName' );
		},
		function() {
			element.value = element.getAttribute('yg_previous');
		}
	);

}


/**
 * Approves a Contentblock
 * @param { Element } [which] The element from which the function is called.
 * @function
 * @name $K.yg_approveCBlock
 */
$K.yg_approveCBlock = function( which ) {
	which = $(which);
	var winID = which.up('.ywindow').id.replace(/wid_/,'');
	var ygId = $K.windows['wid_'+winID].yg_id;

	var data = Array ( 'noevent', {yg_property: 'approveCBlock', params: {
		cblock: ygId.split('-')[0],
		winID: winID
	} } );
	$K.yg_AjaxCallback( data, 'approveCBlock' );
}

