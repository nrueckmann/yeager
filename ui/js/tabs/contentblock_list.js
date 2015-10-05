/**
 * @fileoverview Provides functionality for contentblock lists
 * @version 1.0
 */


/**
 * Inits dynamic list
 * @param { String } [wndid] Id of parent window
 * @param { Array } [listconfig] configuration of columns
 */
$K.yg_initCblockList = function(wndid, listconfig) {

	$K.windows[wndid].tablecols = true;

	var headercontent = new String;
	var fakecntcontent = new String;
	var firstrowcontent = new String;
	var winWidth = $(wndid+'_ywindowinner').getWidth();
	var fixedWidth = 0;
	var totalWidth = 0;
	var resizeableMinWidth = 0;
	var resizeableStandardWidth = 0;

	listconfig.each(function (column, i) {
		minwidth = Math.round(column.TITLE.length*5+25);
		if (column.WIDTH  < minwidth) column.WIDTH = minwidth;
		if (column.MINWIDTH < minwidth ) column.MINWIDTH = minwidth;

		if (column.SORT) {
			$K.windows[wndid].loadparams.pagedir_orderby = i;
			$K.windows[wndid].loadparams.pagedir_orderdir = 1;
		}

		if (column.RESIZEABLE == false) {
			fixedWidth += column.WIDTH;
		} else {
			if (column.MINWIDTH) resizeableMinWidth += column.MINWIDTH;
			if (column.WIDTH) resizeableStandardWidth += column.WIDTH;
		}
	});

	listconfig.each(function (column, i) {

		if (column.RESIZEABLE) {

			if ((resizeableMinWidth + fixedWidth) >= winWidth) {
				column.WIDTH = column.MINWIDTH;
			} else {
				if ((resizeableStandardWidth + fixedWidth) >= winWidth) {
					column.WIDTH = column.MINWIDTH + Math.floor((winWidth - resizeableMinWidth - fixedWidth) * (column.WIDTH / resizeableMinWidth));
				} else {
					column.WIDTH = column.WIDTH + Math.floor((winWidth - resizeableStandardWidth - fixedWidth) * (column.WIDTH / resizeableStandardWidth));
				}
			}

			$K.yg_changeCSS('#'+wndid+' .ccol_'+i, 'width', (column.WIDTH-8)+'px', true);
			$K.yg_changeCSS('#'+wndid+' .ccol_'+i, 'min-width', (column.MINWIDTH-8)+'px', true);

		} else {

			$K.yg_changeCSS('#'+wndid+' .ccol_'+i, 'width', (column.WIDTH-8)+'px', true);
			$K.yg_changeCSS('#'+wndid+' .ccol_'+i, 'min-width', (column.WIDTH-8)+'px', true);

		}

		totalWidth += column.WIDTH;

	});

	for (var i = 0; i < listconfig.length; i++) {
		if (listconfig[i].RESIZEABLE) {

			if ((listconfig[i].WIDTH + winWidth - totalWidth - 1) > listconfig[i].MINWIDTH) {
				listconfig[i].WIDTH += (winWidth - totalWidth - 1);
				$K.yg_changeCSS('#'+wndid+' .ccol_'+i, 'width', (listconfig[i].WIDTH-8)+'px', true);
				break;
			}

		}
	}

	$(wndid+'_tablecols').setStyle({'display':'block'});

	$K.yg_refreshCblockList(wndid);
	TableKit.Sortable.sort(wndid+"_tablehead", $K.windows[wndid].loadparams.pagedir_orderby);

	// Enable add button
	$(wndid+'_buttons').down('a.add.tree_btn_cblock').removeClassName('disabled');
}


/**
 * Refreshs CBlocklist window
 * @param { String } [wndid] Id of parent window
 */
$K.yg_refreshCblockList = function(wndid) {

	var oldSelectedIds = new Array();

	$K.yg_currentfocusobj.each(function(focusObjectItem){
		if (focusObjectItem) {
			oldSelectedIds.push(focusObjectItem.id);
		}
	});

	if (oldSelectedIds.length > 0) {
		$(wndid+'_tablecontent').select('tr.mk_cblock').each(function(trItem){
			oldSelectedIds.each(function(selId){
				if (trItem.id == selId) {
					trItem.removeClassName('cntblock');
					trItem.addClassName('cntblockfocus');
					$K.yg_currentfocusobj.push(trItem);
				}
			});
		});
	}

	// table/list
	TableKit.unloadTable(wndid+"_tablecontent");
	TableKit.unloadTable(wndid+"_tablehead");
	TableKit.Sortable.init(wndid+"_tablehead", {});
	TableKit.Sortable.init(wndid+"_tablecontent", {});
	TableKit.Sortable.sort(wndid+"_tablecontent", $K.windows[wndid].loadparams.pagedir_orderby, $K.windows[wndid].loadparams.pagedir_orderdir);
	TableKit.Resizable.init(wndid+"_tablehead", {});
	TableKit.Resizable.resize(wndid+"_tablehead", 0);
	$K.yg_loadThumbPreview( $(wndid+'_listcontainer'), '.cntblock img' );
	$K.windows[wndid].refresh();
}


/**
 * Add new child node to the currently selected node (for listview mode)
 * @param { Element } [cblockref] The element from which the function was called.
 * @function
 * @name $K.yg_addListCBlock
 */
$K.yg_addListCBlock = function( cblockref ) {
	if (cblockref.hasClassName('disabled')) return;
	var winId = cblockref.up('.ywindow').id;
	var cblock = $K.windows[winId].yg_id;
	var site = cblock.split('-')[1];
	cblock = cblock.split('-')[0];
	var coListExtensionId = $(winId+'_listview').down('input[type=hidden]').value;
	var data = Array ( 'noevent', {yg_property: 'addCBlock', params: {
		cblock: cblock,
		site: site,
		mode: 'list',
		coListExtensionId: coListExtensionId
	} } );
	$K.yg_AjaxCallback( data, 'addCBlock' );
}


/**
 * Add new child node to the currently selected node (for listview mode)
 * @param { String } [coFolderId] YgId of the contentblock to add.
 * @param { String } [coBlockInfo] JSON representation of all information about the contentblock to be added
 * @param { String } [mode] Mode; is 'list' when added in listmode
 * @function
 * @name $K.yg_addListItem
 */
$K.yg_addListItem = function( coFolderId, coBlockInfo, mode ) {
	$K.yg_cleanLookupTable();
	if (coBlockInfo) {
		coBlockInfo = coBlockInfo.evalJSON();
	}

	for( windowItem in $K.windows ) {
		if ( ($K.windows[windowItem].yg_id == coFolderId) && ($K.windows[windowItem].tab == 'CONTENTBLOCK_LIST') ) {
			if ( $K.windows[windowItem].loadparams &&
				 $K.windows[windowItem].loadparams.pagedir_perpage &&
				 ( ($K.windows[windowItem].loadparams.pagedir_perpage == -1) ||
				   ($K.windows[windowItem].loadparams.pagedir_perpage > parseInt($(windowItem+'_objcnt').innerHTML,10))) ) {
				// Per is set to 'all'
				var targetElement = $(windowItem+'__cblock_list');
				var targetWindow = $K.windows[windowItem];
				var listViewExtensionId = $(windowItem+'_listview').down('input[type=hidden]').value;

				new Ajax.Request( $K.appdir+'contentblock_listitem', {
					onSuccess: function(t) {
						targetElement.insert({bottom: t.responseText});
						targetWindow.refresh();

						// Add to lookuptable
						$K.yg_customAttributeHandler( $(targetWindow.id+'_listcontainer') );

						// Update count
						$(targetWindow.id+'_objcnt').update(parseInt($(targetWindow.id+'_objcnt').innerHTML,10)+1);

						// Initiate thumbnail loading
						$K.yg_loadThumbPreview( $(targetWindow.id+'_listcontainer'), '.cntblock img' );

						TableKit.Sortable.sort(targetWindow.id+'_tablecontent', targetWindow.loadparams.pagedir_orderby, targetWindow.loadparams.pagedir_orderdir);
						TableKit.Sortable.sort(targetWindow.id+'_tablecontent', targetWindow.loadparams.pagedir_orderby, targetWindow.loadparams.pagedir_orderdir);
					},
					parameters: {
						us: document.body.id,
						lh: $K.yg_getLastGuiSyncHistoryId(),
						listViewExtensionId: listViewExtensionId,
						coId: coBlockInfo.OBJECTID,
						yg_id: coFolderId+'-cblock'
					}
				});
			} else {
				// Pagedir is active, so do a normal reload of the tab
				$K.windows[windowItem].tabs.select($K.windows[windowItem].tabs.selected, {refresh: 1});
			}

			if (coBlockInfo && (mode=='list')) {
				$K.yg_openObjectDetails(coBlockInfo.OBJECTID, 'cblock', coBlockInfo.NAME, 'iconcblock', 'changed');
			}
		}
	}

}



/**
 * Selects a new listview
 * @param { String } [winId] Window-Id
 * @param { String } [selection] Selected Extension-ID
 * @function
 * @name $K.yg_switchDynamicListView
 */
$K.yg_switchDynamicListView = function(winId, selection) {
	$K.windows[winId].loadparams.listViewExtensionId = selection;
	delete $K.windows[winId].loadparams.pagedir_orderby;
	delete $K.windows[winId].loadparams.pagedir_orderdir;
	$K.windows[winId].tabs.select($K.windows[winId].tabs.selected, {refresh: 0});
}
