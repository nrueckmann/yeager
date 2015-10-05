/**
 * @fileoverview Provides functionality for managing the page object
 * @version 1.0
 */

/**
 * Sort the currently selected Subpage up
 * @param { Element } [which] The page-element (div) to move.
 * @function
 * @name $K.yg_sortSubPageUp
 */
$K.yg_sortSubPageUp = function(which) {
	if (which.previous() != null) {
		var page = which.yg_id.split('-')[0];
		var site = which.yg_id.split('-')[1];

		var data = Array ( 'noevent', {yg_property: 'moveUpPage', params: {
			page: page,
			site: site,
			reload: false.toString()
		} } );
		$K.yg_AjaxCallback( data, 'moveUpPage' );
		which.previous().insert({before:which});
	}
}


/**
 * Sort the currently selected Subpage down
 * @param { Element } [which] The page-element (div) to move.
 * @function
 * @name yg_sortSubPageDown
 */
$K.yg_sortSubPageDown = function(which) {
	if (which.next() != null) {
		var page = which.yg_id.split('-')[0];
		var site = which.yg_id.split('-')[1];

		//var data = Array ( 'noevent', {yg_property: 'moveDownPage', yg_id: which.yg_id+'-false'} );
		var data = Array ( 'noevent', {yg_property: 'moveDownPage', params: {
			page: page,
			site: site,
			reload: false.toString()
		} } );
		$K.yg_AjaxCallback( data, 'moveDownPage' );
		which.next().insert({after:which});
	}
}


/**
 * Add child node to the currently selected node
 * @param { Element } [pageref] The element from which the function was called.
 * @function
 * @name $K.yg_addChildPage
 */
$K.yg_addChildPage = function( pageref ) {

	// Topbar buttons or actionbuttons?
	if (pageref.hasClassName('tree_btn')) {
		if (pageref.hasClassName('disabled')) return;
		var page = $K.windows[pageref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt( pageref.up('.ywindow').id.replace(/wid_/g, '') );
		var nodeid = pageref.id;
		var page = nlsTree['pages_tree'+wid+'_tree'].nLst[nodeid].yg_id;
	}

	var site = page.split('-')[1];
	page = page.split('-')[0];

	var data = Array ( 'noevent', {yg_property: 'addPage', params: {
		page: page,
		site: site
	} } );
	$K.yg_AjaxCallback( data, 'addPage' );

}


/**
 * Move the currently selected page up
 * @param { Element } [pageref] The element from which the function was called.
 * @function
 * @name $K.yg_moveUpPage
 */
$K.yg_moveUpPage = function( pageref ) {

	// Topbar buttons or actionbuttons?
	if (pageref.hasClassName('tree_btn')) {
		if (pageref.hasClassName('disabled')) return;
		var page = $K.windows[pageref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt( pageref.up('.ywindow').id.replace(/wid_/g, '') );
		var nodeid = pageref.id;
		var page = nlsTree['pages_tree'+wid+'_tree'].nLst[nodeid].yg_id;
	}
	var site = page.split('-')[1];
	page = page.split('-')[0];

	var data = Array ( 'noevent', {yg_property: 'moveUpPage', params: {
		page: page,
		site: site
	} } );

	$K.yg_AjaxCallback( data, 'moveUpPage' );
}


/**
 * Change the SE-Friendly pagename (PNAME)
 * @param { Element } [element] The element from which the function was called.
 * @function
 * @name $K.yg_changePagePName
 */
$K.yg_changePagePName = function( element ) {

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
		$K.yg_promptbox( $K.TXT('TXT_CHANGE_PAGE_URL_TITLE'), $K.TXT('TXT_CHANGE_PAGE_URL_EMPTY'), 'alert');
		return;
	}

	if (!isNaN(value)) {
		element.value = element.oldvalue;
		$K.yg_promptbox( $K.TXT('TXT_CHANGE_PAGE_URL_TITLE'), $K.TXT('TXT_CHANGE_PAGE_URL_NUMERIC'), 'alert');
		return;
	}

	$K.yg_promptbox( $K.TXT('TXT_CHANGE_PAGE_URL_TITLE'), $K.TXT('TXT_CHANGE_PAGE_URL'), 'standard',
		function() {
			$K.yg_setEdited( element );
			element.setAttribute( 'yg_previous', value );

			var page = yg_id.split('-')[0];
			var site = yg_id.split('-')[1];

			var data = Array ( 'noevent', { yg_property: 'setPagePName', params: {
				value: value,
				page: page,
				site: site
			} } );
			$K.yg_AjaxCallback( data, 'setPagePName' );
		},
		function() {
			element.value = element.getAttribute('yg_previous');
		}
	);

}


/**
 * Move the currently selected page down
 * @param { Element } [pageref] The element from which the function was called.
 * @function
 * @name $K.yg_moveDownPage
 */
$K.yg_moveDownPage = function( pageref ) {
	// Topbar buttons or actionbuttons?
	var wid = parseInt( pageref.up('.ywindow').id.replace(/wid_/g, '') );
	if (pageref.hasClassName('tree_btn')) {
		if (pageref.hasClassName('disabled')) return;
		var page = $K.windows[pageref.up('.ywindow').id].yg_id;
		var nodeid = 'pages_tree'+wid+'_treepage'+pageref.id;
	} else {
		var nodeid = pageref.id;
		var page = nlsTree['pages_tree'+wid+'_tree'].nLst[nodeid].yg_id;
	}

	// Check if the next node is the trash
	if ( nlsTree['pages_tree'+wid+'_tree'].nLst[nodeid].nx &&
		 (nlsTree['pages_tree'+wid+'_tree'].nLst[nodeid].nx.orgId == 'page_trash') ) {
		return;
	}

	var site = page.split('-')[1];
	page = page.split('-')[0];

	var data = Array ( 'noevent', {yg_property: 'moveDownPage', params: {
		page: page,
		site: site
	} } );
	$K.yg_AjaxCallback( data, 'moveDownPage' );
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionAddChildPage
 */
$K.yg_actionAddChildPage = function( which ) { $K.yg_addChildPage( which.up(2).reference ); }


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @param { Boolean } [multi] True if multiple items are selected.
 * @function
 * @name $K.yg_actionDeletePage
 */
$K.yg_actionDeletePage = function( which, multi ) { $K.yg_deleteElement( which.up(2).reference, multi ); }


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionMoveUpPage
 */
$K.yg_actionMoveUpPage = function( which ) {
	$K.yg_moveUpPage( which.up(2).reference );
	which.up('.actions').hide();
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionMoveDownPage
 */
$K.yg_actionMoveDownPage = function( which ) {
	$K.yg_moveDownPage( which.up(2).reference );
	which.up('.actions').hide();
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionEditPage
 */
$K.yg_actionEditPage = function( which ) { which.up(2).reference.onclick(); }


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionCopyPage
 */
$K.yg_actionCopyPage = function( which ) {
	var wid = parseInt( which.up('.ywindow').id.replace(/wid_/g, '') );
	$K.windows['wid_'+wid].yg_id = nlsTree['pages_tree'+wid+'_tree'].nLst[which.up(2).reference.id].yg_id;
	new $K.yg_wndobj({ config: 'PAGE_COPY', loadparams: { action: 'copy', opener_reference: which.up('.ywindow').id, site: $(which.up('.ywindow').id+'_dd_site').value } });
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name yg_actionMovePage
 */
$K.yg_actionMovePage = function( which ) {
	var wid = parseInt( which.up('.ywindow').id.replace(/wid_/g, '') );
	$K.windows['wid_'+wid].yg_id = nlsTree['pages_tree'+wid+'_tree'].nLst[which.up(2).reference.id].yg_id;
	new $K.yg_wndobj({ config: 'PAGE_MOVE', loadparams: { action: 'move', opener_reference: which.up('.ywindow').id, site: $(which.up('.ywindow').id+'_dd_site').value } });
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name yg_actionshowPagePreviewTree
 */
$K.yg_actionshowPagePreviewTree = function( which ) {
	var wid = parseInt( which.up(2).reference.up('.ywindow').id.replace(/wid_/g, '') );
	var nodeid = which.up(2).reference.id;
	var data = nlsTree['pages_tree'+wid+'_tree'].nLst[nodeid].yg_id.split('-');
	var url =  nlsTree['pages_tree'+wid+'_tree'].nLst[nodeid].ygurl;
	var page = data[0];
	var site = data[1];
	$K.yg_preview({objecttype: 'page', id: page, site: site, version: 'working', url: url });
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 */
$K.yg_actionsSetPagePreviewHref = function( which ) {
	var wid = parseInt( which.up(2).reference.up('.ywindow').id.replace(/wid_/g, '') );
	var nodeid = which.up(2).reference.id;
	var url = nlsTree['pages_tree'+wid+'_tree'].nLst[nodeid].ygurl;
	which.href = url + "?version=working";
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name yg_actionMoveDownCBlock
 */
$K.yg_actionMoveDownCBlock = function( which ) {
	$K.log( 'actionMoveDownCBlock', which.up('li'), $K.Log.DEBUG );
	var cblock = which.up('li').id.split('_')[4];
	var winno = which.up('.ywindow').id.split('_')[1];
	var page = $K.windows['wid_'+winno].yg_id.split('-')[0];
	var site = $K.windows['wid_'+winno].yg_id.split('-')[1];
	var data = Array ( 'noevent', {yg_property: 'moveDownPageContentblock', params: {
		cblock: cblock,
		page: page,
		site: site,
		win_no: winno
	} } );
	$K.yg_AjaxCallback( data, 'moveDownPageContentblock' );
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name yg_actionMoveDownCBlock
 */
$K.yg_actionMoveUpCBlock = function( which ) {
	$K.log( 'actionMoveUpCBlock', which.up('li'), $K.Log.DEBUG );
	var cblock = which.up('li').id.split('_')[4];
	var winno = which.up('.ywindow').id.split('_')[1];
	var page = $K.windows['wid_'+winno].yg_id.split('-')[0];
	var site = $K.windows['wid_'+winno].yg_id.split('-')[1];
	var data = Array ( 'noevent', {yg_property: 'moveUpPageContentblock', params: {
		cblock: cblock,
		page: page,
		site: site,
		win_no: winno
	} } );
	$K.yg_AjaxCallback( data, 'moveUpPageContentblock' );
}


/**
 * Preview a new Entrymask
 * @param { Element } [which] The element from which the function is called.
 * @param { String } [wid] The id of the relevant window.
 * @function
 * @name $K.yg_selectEntrymask
 */
$K.yg_selectEntrymask = function( which, wid ) {

	if ($('wid_'+wid+'_column2innercontentinner')) {
		$('wid_'+wid+'_column2innercontentinner').innerHTML = "";
		$('wid_'+wid+'_column2innercontentinner').addClassName('tab_loading');

		new Ajax.Updater('wid_'+wid+'_column2innercontentinner', $K.appdir+'cblocks',
		{
			evalScripts: true,
			asynchronous: true,
			method: 'post',
			parameters: {
				site: 'dummy',
				page: 'dummy',
				co: which,
				win_no: wid,
				us: document.body.id,
				lh: $K.yg_getLastGuiSyncHistoryId()
			},
			onComplete: function() {
				$K.windows["wid_"+wid].refresh("col2");
				$('wid_'+wid+'_column2innercontentinner').removeClassName('tab_loading');
			},
			onSuccess: function(transport) {

			}
		});

	}

}


/**
 * Preview a new Contentblock
 * @param { Element } [which] The element from which the function is called.
 * @param { String } [wid] The id of the relevant window.
 * @function
 * @name $K.yg_selectContentblock
 */
$K.yg_selectContentblock = function( which, wid ) {

	if (!($K.windows['wid_'+wid])) return;

	if ($K.windows['wid_'+wid].tab == 'copy') {
		var mode = 'cblock_copy';
	} else {
		var mode = 'cblock';
	}

	if ($($K.windows['wid_'+wid].openerReference)) {
		var openerSelectedNode = $K.windows[$($K.windows['wid_'+wid].openerReference).up('.ywindow').id].yg_id;
		var openerPage = openerSelectedNode.split('-')[0];
		var openerSite = openerSelectedNode.split('-')[1];
	}

	if ($('wid_'+wid+'_column2innercontentinner')) {
		$('wid_'+wid+'_column2innercontentinner').innerHTML = "";
		$('wid_'+wid+'_column2innercontentinner').addClassName('tab_loading');

		new Ajax.Updater('wid_'+wid+'_column2innercontentinner', $K.appdir+'cblocks', {
			evalScripts: true,
			asynchronous: true,
			method: 'post',
			parameters: {
				site: mode,
				page: mode,
				win_no: wid,
				co: which,
				co_site: openerSite,
				co_page: openerPage,
				us: document.body.id,
				lh: $K.yg_getLastGuiSyncHistoryId() /*,
				displaymode: 'dialog'
				*/
			},
			onComplete: function() {
				$K.windows["wid_"+wid].refresh("col2");
				$('wid_'+wid+'_column2innercontentinner').removeClassName('tab_loading');
			},
			onSuccess: function(transport) {

			}
		});
	}

}


/**
 * Updates pages tree scrollbars
 * @param { Element } [which] The element from which the function is called.
 * @function
 * @name $K.yg_updatePagesTreeScrolls
 */
$K.yg_updatePagesTreeScrolls = function( which ) {
	$K.windows[$(which).up('.ywindow').id].init();
}


/**
 * Approves a page
 * @param { Element } [which] The element from which the function is called.
 * @function
 * @name $K.yg_approvePage
 */
$K.yg_approvePage = function( which ) {
	which = $(which);
	var winID = which.up('.ywindow').id.replace(/wid_/,'');
	var ygId = $K.windows['wid_'+winID].yg_id;


	var data = Array ( 'noevent', {yg_property: 'approvePage', params: {
		page: ygId.split('-')[0],
		site: ygId.split('-')[1],
		winID: winID
	} } );
	$K.yg_AjaxCallback( data, 'approvePage' );
}


/**
 * Changes the "active" state of a page
 * @param { Element } [which] The element from which the function is called.
 * @function
 * @name $K.yg_setPageState
 */
$K.yg_setPageState = function( which ) {
	which = $(which);

	var winID = which.up('.ywindow').id.replace(/wid_/,'');
	var ygId = $K.windows['wid_'+winID].yg_id;

	var data = Array ( 'noevent', {yg_property: 'setPageState', params: {
		page: ygId.split('-')[0],
		site: ygId.split('-')[1],
		active: which.down('input').value
	} } );
	$K.yg_AjaxCallback( data, 'setPageState' );
}
