/**
 * @fileoverview Provides functionality for managing javascript tree's
 */


// Dataholders for dynamic generated trees
$K.yg_treecount = 0;
$K.yg_dynTrees = new Array();
$K.yg_dynTreesXML = new Array();
$K.yg_dynTreesDD = new Array();
$K.yg_dynTreesWNDO = new Array();


/**
 * Supplemental function to initialize a tree
 * @param { Element } [elementRef] Reference to the element
 * @function
 * @name $K.initTree
 */
$K.initTree = function(elementRef) {
	elementRef = $(elementRef);

	var widgetData = {};
	var widgetInfo = elementRef.readAttribute('yg_widget');
	widgetInfo.split(';').each(function(item){
		var itemData = item.split(':');
		if (itemData[0] && itemData[1]) {
			widgetData[itemData[0]] = itemData[1];
		}
	});

	$K.yg_createDynTree(
			elementRef,
			widgetData.treename,
			widgetData.objtype,
			widgetData.accepts,
			widgetData.sort,
			widgetData.nosamelevel,
			widgetData.nodrag,
			false,
			widgetData.noclick,
			widgetData.site,
			widgetData.action,
			widgetData.editable,
			widgetData.sortable,
			widgetData.preselected
	);
}


/**
 * Creates a tree completely automatically including custom scrollbars.
 * This function is used to create a tree from a specially marked DIV
 * Only used internally.
 * @param { Element } [element] The element which will be converted to a tree.
 * @param { String } [treename] The name of the tree to be created.
 * @param { String } [styleprefix] (Optional) If specified, this prefix will
 * be applied to the tree elements.
 * @param { String } [type] The type of the objects contained in this tree,
 * Is used to detect if drag'n'drop operations are allowed, for example.
 * @param { String } [accepts] The type of elements this tree is allowed to
 * receive.
 */
$K.yg_createDynTree = function ( element, treename, type, accepts, sort, nosamelevel, nodrag, scrolls, noclick, site, action, editable, sortable, preselected ) {

	if (site == "") var site = 1;
	var treeContainer = $(document.createElement('DIV'));
	treeContainer.id = treename;
	treeContainer.accepts = accepts;
	treeContainer.sort = sort;
	treeContainer.yg_type = type;
	treeContainer.writeAttribute("yg_type", type);
	treeContainer.className = element.className;

	if (scrolls==true) {
		treeContainer.innerHTML =	'<div id="'+treename+'_inner"></div>' +
										'<div id="'+treename+'_sv" class="scrollbar_v">' +
											'<div class="scroll_up"><a onclick="return false" onmouseover="$K.yg_ddScrollOver\''+treename+'_inner\',\'up\')" onmouseout="$K.yg_ddScrollOut(\''+treename+'_inner\')" onmousedown="$K.yg_scroll_initScroll(\''+treename+'_inner\',\'up\')" onmouseup="$K.yg_scroll_stopScroll(\''+treename+'_inner\')"><img src="'+$K.imgdir+'scrollbars/btn-up.gif" width="12" height="12" alt="" /></a></div>' +
											'<div id="'+treename+'_tv" class="track_v"><div id="'+treename+'_dbv" class="dragBar_v"></div></div>' +
											'<div class="scroll_down"><a onclick="return false" onmouseover="$K.yg_ddScrollOver\''+treename+'_inner\',\'down\')" onmouseout="$K.yg_ddScrollOut(\''+treename+'_inner\')" onmousedown="$K.yg_scroll_initScroll(\''+treename+'_inner\',\'down\')" onmouseup="$K.yg_scroll_stopScroll(\''+treename+'_inner\')"><img src="'+$K.imgdir+'scrollbars/btn-dn.gif" width="12" height="12" alt="" /></a></div>' +
										'</div>' +
										'<div id="'+treename+'_sh" class="scrollbar_h">' +
											'<div class="scroll_left"><a onclick="return false" onmouseover="$K.yg_ddScrollOver\''+treename+'_inner\',\'left\')" onmouseout="$K.yg_ddScrollOut(\''+treename+'_inner\')" onmousedown="$K.yg_scroll_initScroll(\''+treename+'_inner\',\'left\')" onmouseup="$K.yg_scroll_stopScroll(\''+treename+'_inner\')"><img src="'+$K.imgdir+'scrollbars/btn-lft.gif" width="12" height="12" alt="" /></a></div>' +
											'<div id="'+treename+'_th" class="track_h"><div id="'+treename+'_dbh" class="dragBar_h"></div></div>' +
											'<div class="scroll_right"><a onclick="return false" onmouseover="$K.yg_ddScrollOver\''+treename+'_inner\',\'right\')" onmouseout="$K.yg_ddScrollOut(\''+treename+'_inner\')" onmousedown="$K.yg_scroll_initScroll(\''+treename+'_inner\',\'right\')" onmouseup="$K.yg_scroll_stopScroll(\''+treename+'_inner\')"><img src="'+$K.imgdir+'scrollbars/btn-rt.gif" width="12" height="12" alt="" /></a></div>' +
											'<div class="scroll_blank"></div>' +
										'</div>';
	} else {
		treeContainer.innerHTML =	'<div id="'+treename+'_inner"></div>';
	}

	if (!element.next()) {
		element.up().appendChild(treeContainer);
	} else {
		element.up().insertBefore(treeContainer, element.next());
	}

	$K.yg_dynTrees[$K.yg_treecount] = new NlsTree(treename+'_tree');
	$K.yg_dynTrees[$K.yg_treecount].yg_type = type;
	$K.yg_dynTrees[$K.yg_treecount].sortable = sortable;
	$K.yg_dynTreesXML[$K.yg_treecount] = null;

	// fix for IE
	Event.observe(element.up(), 'mousedown', function() { setTimeout("$K.yg_clearTextSelection()", 0); });

	if (treeContainer.up('.ydialog')) {
		$K.yg_dynTrees[$K.yg_treecount].opt.stlprf = "dark";
	}

	// Enable load on demand
	$K.yg_dynTrees[$K.yg_treecount].chUrl = 'about:blank';

	// Enable node-state maintaining (in cookies)
	$K.yg_dynTrees[$K.yg_treecount].opt.mntState = true;

	// Enable live-editing
	if (editable) {
		$K.yg_dynTrees[$K.yg_treecount].opt.editable = eval(editable);
	}

	// Enable sorting
	if (sortable) {
		$K.yg_dynTrees[$K.yg_treecount].opt.sortable = eval(sortable);
	}

	// Set sorting (if requested)
	if (sort) {
		$K.yg_dynTrees[$K.yg_treecount].opt.sort = sort;
	}

	$K.yg_dynTrees[$K.yg_treecount].treeOnNodeChange = $K.yg_customOnChange;

	var params = element.readAttribute('yg_widget').split(';');
	params.each(function(item){
		if (item.startsWith('site:')) {
			site = item.split(':')[1];
		}
	});
	rootnodename = 'root_1';

	var xtra = '';
	if (noclick) {
		xtra = 'noclick';
	}
	$K.yg_dynTrees[$K.yg_treecount].add(rootnodename, 0, '', '', $K.imgdir+'window/x.gif', true, false, xtra);

	// Disable editing on the root node
	$K.yg_dynTrees[$K.yg_treecount].setEditablity(rootnodename, false);
	if (!nodrag) $K.yg_dynTreesDD[$K.yg_treecount] = new NlsTreeDD(treename+'_tree');
	if (!nodrag) $K.yg_dynTreesDD[$K.yg_treecount].onNodeDrop = $K.yg_customOnDrop;

	// Set nosamelevel (if requested)
	if (nosamelevel) {
		$K.yg_dynTrees[$K.yg_treecount].opt.nosamelevel = nosamelevel;
		if (!nodrag) $K.yg_dynTreesDD[$K.yg_treecount].shiftToReorder = true;
	} else {
		if (!nodrag) $K.yg_dynTreesDD[$K.yg_treecount].shiftToReorder = false;
	}
	$K.yg_dynTrees[$K.yg_treecount].setDrag(rootnodename, false, false);
	$K.yg_dynTrees[$K.yg_treecount].render(treename+'_inner');

	if (scrolls==true) {
		$K.yg_dynTreesWNDO[$K.yg_treecount] = new $K.yg_scrollObj(treename+'_inner', treename+'_tree', treename+'_tree');
		$K.yg_dynTreesWNDO[$K.yg_treecount].setUpScrollbar(treename+'_dbv', treename+'_tv', 'v', 1, 1);
		$K.yg_dynTreesWNDO[$K.yg_treecount].setUpScrollbar(treename+'_dbh', treename+'_th', 'h', 1, 1);
		$K.yg_dynTreesWNDO[$K.yg_treecount].setBarSize();

	}

	var hlper_function = function(id) {
		if ( !Object.isUndefined(nlsTree[treename+'_tree']) )
			if (typeof nlsTree[treename+'_tree'].remapAction == 'function')
				nlsTree[treename+'_tree'].remapAction();

		$K.windows[$(treename+'_tree').up('.ywindow').id].refresh('col1');
	}

	$K.yg_dynTrees[$K.yg_treecount].treeOnExpand = $K.yg_dynTrees[$K.yg_treecount].treeOnCollapse = hlper_function;

	element.up().observe('mouseenter', function() { if ($(treename+'_actionbutton')) $(treename+'_actionbutton').show(); });
	element.up().observe('mouseleave', function() { if ((!$K.actionhover) && $(treename+'_actionbutton')) $(treename+'_actionbutton').hide(); } );

	element.remove();

	$K.yg_fillTree($(treename+'_tree').up('.ywindow').id.split("_")[1], type, treename, site, action, preselected);

	$K.yg_treecount++;
}


/**
 * Fills page tree
 * @param { String } [winID] window ID
 * @param { Integer } [site] site ID
 * @param { String } [action] 'copy', 'move', 'choose' or nothing
 */
$K.yg_fillTree = function(winID, objecttype, treename, site, action, preselected) {

	// Fill first tree (main)
	foo = {
		showMenu: function (clientX, clientY) {
			//rightClickHandler( {target: { tagName: 'DIV', id: ''}, clientX: clientX, clientY: clientY } );
		}
	}

	var yeagerTreeIconSet = function(path,iconstr) {
		this.pnb	= path + 'plusnb.gif';
		this.pb		= path + 'plusb.gif';
		this.pnl	= path + 'plusnl.gif';
		this.mnb	= path + 'minusnb.gif';
		this.mb		= path + 'minusb.gif';
		this.mnl	= path + 'minusnl.gif';
		this.opf	= path + '../icons/ico_' + iconstr + '_s.png';
		this.clf	= path + '../icons/ico_' + iconstr + '_s.png';
		this.chd	= path + '../icons/ico_' + iconstr + '_s.png';
		this.rot	= path + '../icons/ico_' + iconstr + '_s.png';
		this.lnb	= path + 'lineang.gif';
		this.lb		= path + 'lineints.gif';
		this.lin	= path + 'line.gif';
		this.bln	= path + 'blank.gif';
		this.lod	= path + 'blank.gif';
		this.toString = function() { return 'yeager tree icons';}
		return this;
	}

	window.mapAction = function() {

		// For Action-Buttons
		for (var property in nlsTree[treename+'_tree'].nLst) {

			if (nlsTree[treename+'_tree'].nLst[property]!=null) {
				var tree_actChild = $(nlsTree[treename+'_tree'].nLst[property].id);

				if (tree_actChild!=undefined) {
					if (
						 (tree_actChild.down('.actions')==undefined)
						) {

						if (!tree_actChild.alreadyUpdated) {
							tree_actChild._onmouseover = function(e) {
								$K.yg_showActions(this, treename+'_tree');
								if ($(treename+'_actionbutton')) $(treename+'_actionbutton').show();
								$K.yg_currentHover = this;
							}

							tree_actChild.alreadyUpdated = true;
						}

					}
				}

			}

		}

		if (window.mapAction!=undefined) {
			nlsTree[treename+'_tree'].remapAction = window.mapAction;
		}
		window.mapAction = undefined;
	}

	with (nlsTree[treename+'_tree']) {

		useIconSet(new yeagerTreeIconSet($K.imgdir+'tree/',objecttype));
		defImgPath = $K.imgdir+'nlstree/';
		opt.selRow = true;
		chUrl = $K.appdir + objecttype+'s_tree_nodes';

		if (site) chUrl += "?site="+site;

		rootnodename = 'root_1';

		reloadNode(rootnodename);
		expandNode(rootnodename);

		// XML-Tree dynamisch laden
		var handlerFunc = function(t) {

			nlsTree[treename+'_tree'].addChildNodesXML(t.responseXML.documentElement, true, true);

			window.mapAction();

			var nodes = nlsTree[treename+'_tree'].nLst;

			// Hide activity indicator
			$(treename).up(3).removeClassName('tab_loading');

			// Update scrollbars
			$K.windows["wid_"+winID].refresh("col1");

			if ((preselected != undefined) && (preselected != "")) {
				nlsTree[treename+'_tree'].selectNodeById( objecttype+'_'+preselected );
			} else {
				if ($K.windows["wid_"+winID].boundWindow) $K.yg_selectNode($(nlsTree[treename+'_tree'].rt.id), false, false);
			}
		}
		var errFunc = function(t) {
			$K.yg_promptbox($K.TXT('TXT_ERROR'), '<center>Error ' + t.status + ' -- ' + t.statusText + '</center>', 'alert');
		}

		// Show activity indicator
		var treebg = $(treename).up(3);
		treebg.addClassName('tab_loading');

		var treeParameters = { action: action };
		Object.extend(treeParameters, $K.windows['wid_'+winID].loadparams);

		treeParameters.us = document.body.id;
		treeParameters.lh = $K.yg_getLastGuiSyncHistoryId();

		new Ajax.Request( chUrl,
		{
			parameters: treeParameters,
			/* onSuccess: handlerFunc, */
			onComplete: handlerFunc,
			onFailure: errFunc,
			onlyLatestOfClass: winID+'_tree'
		});

	}

	nlsTree[treename+'_tree'].setGlobalCtxMenu(foo); //setting global context menu

}


/**
 * Called when tree is loaded successfully
 * @param { String } [window_ref] The reference to the window.
 * @param { String } [objecttype] objecttype
 * @param { Boolean } [dragndrop] drag and drop
 * @param { Object } [response] request response
 * @function
 */
$K.yg_treeFinishedLoading = function(window_ref, objecttype, dragndrop, response) {

	var oldRootNodeId = nlsTree[objecttype+'_tree'+window_ref+'_tree'].rt.orgId;

	nlsTree[objecttype+'_tree'+window_ref+'_tree'].removeChilds(oldRootNodeId, true);
	nlsTree[objecttype+'_tree'+window_ref+'_tree'].addChildNodesXML(response.responseXML.documentElement, true, true);

	// Refresh/remap actionbuttons
	if ( typeof(nlsTree[objecttype+'_tree'+window_ref+'_tree'].remapAction)=='function' ) {
		nlsTree[objecttype+'_tree'+window_ref+'_tree'].remapAction();
	}

	// Disable Drag'n'drop if not needed/wanted
	if (!dragndrop) {
		for (var nodeid in nlsTree[objecttype+'_tree'+window_ref+'_tree'].nLst) {
			var nD = nlsTree[objecttype+'_tree'+window_ref+'_tree'].nLst[nodeid];
			if(nD==null) continue;
			if (nD.orgId!='root_1') {
				nlsTree[objecttype+'_tree'+window_ref+'_tree'].setDrag(nD.orgId, false, false);
				nlsTree[objecttype+'_tree'+window_ref+'_tree'].setDrop(nD.orgId, false, false);
			}
		}
	}

	// Hide activity indicator
	treebg = $(objecttype+'_tree'+window_ref).up(3);
	treebg.removeClassName('tab_loading');
	treebg.setStyle({width:'auto'});
	$(objecttype+'_tree'+window_ref).show();
	// select first node if manager
	if ($K.windows["wid_"+window_ref].boundWindow) $K.yg_selectNode($(nlsTree[objecttype+'_tree'+window_ref+'_tree'].rt.id), false, false);
	// Update Scrollbars
	$K.windows["wid_"+window_ref].refresh("col1");
}


/**
 * Called when tree starts loading
 * @param { String } [window_ref] The reference to the window.
 * @param { String } [objecttype] objecttype
 * @function
 */
$K.yg_treeStartLoading = function(window_ref, objecttype) {
	treebg = $(objecttype+'_tree'+window_ref).up(3);
	treebg.addClassName('tab_loading');
	$(objecttype+'_tree'+window_ref).hide();
	$K.windows["wid_"+window_ref].refresh("col1");
}


/**
 * Switch the currently selected Site
 * @param { Element } [newSite] The id of the new site to load.
 * @param { String } [window_ref] The reference to the window.
 * @function
 * @name $K.yg_switchSite
 */
$K.yg_switchSite = function ( newSite, window_ref ) {

	// Reset buttons over tree
	$('wid_'+window_ref+'_buttons').select('.tree_btn').each(function(item){
		item.addClassName('disabled');
	});

	var objecttype = "pages";

	// XML-Tree dynamisch laden
	var handlerFunc = function(t) {
		window.setTimeout(function() { $K.yg_treeFinishedLoading(window_ref, objecttype, true, t) }, 1);
	}
	var errFunc = function(t) {
		$K.yg_promptbox($K.TXT('TXT_ERROR'), '<center>Error ' + t.status + ' -- ' + t.statusText + '</center>', 'alert');
	}

	$K.yg_treeStartLoading(window_ref, objecttype);

	var ajaxParams = {};
	Object.extend(ajaxParams, $K.windows['wid_'+window_ref].loadparams);
	ajaxParams.site = newSite;
	ajaxParams.us = document.body.id;
	ajaxParams.lh = $K.yg_getLastGuiSyncHistoryId();

	new Ajax.Request( $K.appdir+objecttype+'_tree_nodes', {
			parameters: ajaxParams,
			onComplete: handlerFunc,
			onFailure: errFunc,
			onlyLatestOfClass: window_ref+'_tree'
	});

}


/**
 * Switch the currently selected Site (View in Tab ContentBlocks)
 * @param { Element } [newSite] The id of the new site to load.
 * @param { String } [window_ref] The reference to the window.
 * @function
 * @name $K.yg_switchSiteContentBlocks
 */
$K.yg_switchSiteContentBlocks = function ( newSite, window_ref ) {

	var objecttype = "cblocks";

	var url = '';
	var site = 0;

	if ( newSite.indexOf('structure_site') != -1 ) {
		url = $K.appdir+'cblocksextras_tree_nodes_extras';
		site = newSite.substr(newSite.lastIndexOf('_')+1);
		nlsTree['cblocks_tree'+window_ref+'_tree'].opt.sort = "no";
	} else if ( newSite.indexOf('structure') != -1 ) {
		url = $K.appdir+'cblocks_tree_nodes?displayMode=dialog';
		site = 0;
		nlsTree['cblocks_tree'+window_ref+'_tree'].opt.sort = "asc";
	}

	url += '?site=' + site;

	nlsTree['cblocks_tree'+window_ref+'_tree'].chUrl = url;

	// XML-Tree dynamisch laden
	var handlerFunc = function(t) {
		window.setTimeout(function() { $K.yg_treeFinishedLoading(window_ref, objecttype, true, t) }, 1);
	}
	var errFunc = function(t) {
		$K.yg_promptbox($K.TXT('TXT_ERROR')+'!', '<center>Error ' + t.status + ' -- ' + t.statusText + '</center>', 'alert');
	}

	// Show activity indicator
	$K.yg_treeStartLoading(window_ref, objecttype);

	new Ajax.Request( url,
	{
			parameters: {
				site: site,
				us: document.body.id,
				lh: $K.yg_getLastGuiSyncHistoryId()
			},
			onComplete: handlerFunc,
			onFailure: errFunc,
			onlyLatestOfClass: window_ref+'_tree'
	});

}


/**
 * Switch the currently selected Site (View in Tab CopyFrom)
 * @param { Element } [newSite] The id of the new site to load.
 * @param { String } [window_ref] The reference to the window.
 * @function
 * @name $K.yg_switchSiteCopyFrom
 */
$K.yg_switchSiteCopyFrom = function ( newSite, window_ref ) {

	var objecttype = "pagesextras";

	var url = '';
	var site = 0;
	var dragndrop = true;

	if ( newSite.indexOf('pages_with_cblocks') != -1 ) {
		site = newSite.substr(newSite.lastIndexOf('_')+1);
		url = $K.appdir+'pagesextras_tree_nodes_extras?site=' + site;
		nlsTree['pagesextras_tree'+window_ref+'_tree'].opt.sort = "no";
		$K.windows['wid_'+window_ref].loadparams.treemode = 'pages_with_cblocks';
	} else if ( newSite.indexOf('pages') != -1 ) {
		site = newSite.substr(newSite.lastIndexOf('_')+1);
		url = $K.appdir+'pages_tree_nodes?site=' + site + '&dnd=false';
		dragndrop = false;
		nlsTree['pagesextras_tree'+window_ref+'_tree'].opt.sort = "no";
		$K.windows['wid_'+window_ref].loadparams.treemode = 'pages';
	} else if ( newSite.indexOf('cblocks') != -1 ) {
		site = 0;
		url = $K.appdir+'cblocks_tree_nodes?displayMode=dialog&site=' + site;
		nlsTree['pagesextras_tree'+window_ref+'_tree'].opt.sort = "asc";
		$K.windows['wid_'+window_ref].loadparams.treemode = 'cblocks';
	}

	nlsTree['pagesextras_tree'+window_ref+'_tree'].chUrl = url;

	// XML-Tree dynamisch laden
	var handlerFunc = function(t) {
		window.setTimeout(function() { $K.yg_treeFinishedLoading(window_ref, objecttype, false, t) }, 1);
	}

	var errFunc = function(t) {
		$K.yg_promptbox($K.TXT('TXT_ERROR')+'!', '<center>Error ' + t.status + ' -- ' + t.statusText + '</center>', 'alert');
	}

	// Show activity indicator
	$K.yg_treeStartLoading(window_ref, objecttype);

	new Ajax.Request( url,
	{
			parameters: {
				site: site,
				us: document.body.id,
				lh: $K.yg_getLastGuiSyncHistoryId()
			},
			onComplete: handlerFunc,
			onFailure: errFunc,
			onlyLatestOfClass: window_ref+'_tree'
	});

}


/**
 * Expands all treenodes in the list
 * @param { String } [win_id] The id of the window.
 * @param { String } [nodelist] The list of nodes, comma seperated.
 * @function
 * @name $K.yg_expandTreeNodes
 */
$K.yg_expandTreeNodes = function ( win_id, yg_type, nodelist, select_id, reloadtree ) {
	var queueFunc = function() {
		nodelist = $A(nodelist.split(','));

		win_id = win_id.replace(/wid_/g, '');
		var objectid = yg_type+'_tree'+win_id+'_tree';

		nodelist.each(function(item, idx){
			nodelist[idx] = 'page_' + item;
		});
		nlsTree[objectid].expandNodes( nodelist );
	}
	if (reloadtree && (reloadtree=='true')) {
		$K.yg_reloadTree( win_id, yg_type, select_id, queueFunc );
	} else {
		queueFunc();
	}
}


/**
 * Reload the tree in the specified window
 * @param { String } [win_id] The id of the window.
 * @function
 * @name $K.yg_reloadTree
 */
$K.yg_reloadTree = function ( win_id, yg_type, node_id, onSuccess ) {

	org_win_id = win_id;
	win_id = win_id.replace(/wid_/g, '');
	var objectid = yg_type+'s_tree'+win_id+'_tree';
	var site = nlsTree[objectid].rt.yg_id.split('-')[1];

	// XML-Tree dynamisch laden
	var handlerFunc = function(t) {

		var oldRootNodeId = nlsTree[objectid].rt.orgId;

		nlsTree[objectid].removeChilds(oldRootNodeId, false);

		nlsTree[objectid].addChildNodesXML(t.responseXML.documentElement, true, true);

		// Remap actions (if possible)
		if (typeof nlsTree[objectid].remapAction == 'function') {
			nlsTree[objectid].remapAction();
		}

		if (onSuccess && (typeof onSuccess == 'function')) {
			onSuccess();
		}

		// Try to restore tree state
		$K.yg_restoreTreeState(objectid);

		if (node_id) {
			$K.yg_selectTreeNode( org_win_id, yg_type.substring(0,yg_type.length-1), node_id );
		}
	}
	var errFunc = function(t) {
		$K.yg_promptbox($K.TXT('TXT_ERROR')+'!', '<center>Error ' + t.status + ' -- ' + t.statusText + '</center>', 'alert');
	}

	new Ajax.Request( $K.appdir+yg_type+'s_tree_nodes',
	{
		parameters: {
			site: site,
			us: document.body.id,
			lh: $K.yg_getLastGuiSyncHistoryId()
		},
		onComplete: handlerFunc,
		onFailure: errFunc
	});
}


/**
 * Restores the state of a tree (if it was previously stored in a cookie)
 * Only used internally.
 * @param { String } [objectid] The id of the tree whose state should be restored
 */
$K.yg_restoreTreeState = function ( objectid ) {
	var sid = null;
	if (nlsTree[objectid] && nlsTree[objectid].opt.mntState && nls_getCookie) {
	  var sid=nls_getCookie(nlsTree[objectid].tId+"_selnd");
	  nls_maintainNodeState(nlsTree[objectid].tId, true);
	}
	if(sid && sid!="") nlsTree[objectid].selectNodeById(sid);
}


/**
 * (Re)selects a node in a tree
 * @param { String } [win_id] The window-id of the tree in question
 * @param { String } [node] The id of the node which should be selected
 */
$K.yg_selectTreeNode = function ( win_id, yg_type, nodeid ) {
	win_id = win_id.replace(/wid_/g, '');
	var objectid = yg_type+'s_tree'+win_id+'_tree';
	$K.yg_restoreTreeState( objectid );
	if (nlsTree[objectid]) nlsTree[objectid].selectNodeById(yg_type+'_'+nodeid);

	$K.yg_selectNode( $(objectid+yg_type+'_'+nodeid) );
}


/**
 * Helper function used to get the name of the tree the specified object is included in
 * @type Boolean
 * @param { Object } [object] The tree object to get treename from
 */
$K.yg_getTreeReference = function ( object ) {
	if (object.pr) {
		return $K.yg_getTreeReference( object.pr );
	} else {
		var regex = new RegExp( object.orgId );
		var treeName = object.id.replace(regex, '');
		return treeName;
	}
}


/**
 * Helper function used to move nodes in a tree (including subnodes) based on yg_ids
 * @param { String } [treeType] The type of the tree
 * @param { String } [sourceYgId] The yg_id of the source node
 * @param { String } [targetYgId] The yg_id of the target node
 * @param { Integer } [moveType] The type of the move (1: append child, 2: insert before, 3: insert after)
 */
$K.yg_moveTreeNode = function(treeType, sourceYgId, targetYgId, moveType) {
	if (!moveType) moveType = 1;

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Find matching trees
	for (currWinId in $K.windows) {
		var winID = currWinId.split('_')[1];
		var objectid = treeType+'s_tree'+winID+'_tree';

		// Also check for correct site if tree is pages-tree
		var rightSite = true;
		if ((treeType == 'page') && nlsTree[objectid]) {
			var targetSite = targetYgId.split('-')[1];
			var currTreeSite = nlsTree[objectid].rt.yg_id.split('-')[1];
			if (targetSite != currTreeSite) {
				rightSite = false;
			}
		}

		if (nlsTree[objectid] && rightSite) {
			var currentTree = nlsTree[objectid];
			var sourceNode = null;
			var targetNode = null;

			// Find matching source node
			if ($K.yg_idlookuptable[sourceYgId])
			for (var i=0; i < $K.yg_idlookuptable[sourceYgId].length; i++) {
				if ( ($K.yg_idlookuptable[sourceYgId][i].yg_property == 'name') &&
				 	 ($K.yg_idlookuptable[sourceYgId][i].yg_type == treeType) ) {
					// Check if node belongs to tree
					if ($K.yg_idlookuptable[sourceYgId][i].id.indexOf(currentTree.tId) === 0) {
						sourceNode = $K.yg_idlookuptable[sourceYgId][i];
					}
				}
			}

			// Find matching target node
			if ($K.yg_idlookuptable[targetYgId])
			for (var i=0; i < $K.yg_idlookuptable[targetYgId].length; i++) {
				if ( ($K.yg_idlookuptable[targetYgId][i].yg_property == 'name') &&
				 	 ($K.yg_idlookuptable[targetYgId][i].yg_type == treeType) ) {
					// Check if node belongs to tree
					if ($K.yg_idlookuptable[targetYgId][i].id.indexOf(currentTree.tId) === 0) {
						targetNode = $K.yg_idlookuptable[targetYgId][i];
					}
				}
			}

			if (sourceNode && !sourceNode.pr) {
				sourceNode = null;
			}

			if (sourceNode && targetNode) {
				currentTree.moveChild([sourceNode], targetNode, moveType);
			} else if (sourceNode && !targetNode) {
				currentTree.remove( sourceNode.orgId );
			} else if (!sourceNode && targetNode) {
				var origTreeId = targetYgId.split('-')[1]+'_'+targetYgId.split('-')[0];
				if (treeType == 'page') {
					var origTreeId = 'page_'+targetYgId.split('-')[0];
				}
				if (targetYgId.split('-')[0] == 1) {
					var origTreeId = 'root_1';
				}
				nlsTree[objectid].ajaxLoadChildNodes(origTreeId);
			}

			nlsTree[objectid].remapAction();
		}
	}
}


/**
 * OnChange Handler for trees
 * Only used internally.
 * @param { String } [orgId] The orgId of the Node which was changed.
 * @param { String } [tId] The Id of the Tree involved.
 * @function
 * @name $K.yg_customOnChange
 */
$K.yg_customOnChange = function( orgId, tId ) {
	var node = nlsTree[tId].nLst[tId+orgId];

	if ( node != undefined ) {
		switch(node.yg_type) {
			case 'page':
				var data = Array ( 'change', { name: 'pagename',
											   yg_id: node.yg_id,
											   yg_property: node.yg_property,
											   yg_type: node.yg_type,
								   			   value: node.capt
											 } );
				$K.yg_AjaxCallback( data, 'setPageName' );
				break;
			case 'file':
				var data = Array ( 'change', { name: 'filename',
											   yg_id: node.yg_id,
											   yg_property: node.yg_property,
											   yg_type: node.yg_type,
								   			   value: node.capt
											 } );
				$K.yg_AjaxCallback( data, 'setFileName' );
				break;
			case 'cblock':
				var data = Array ( 'change', { name: 'cblockname',
											   yg_id: node.yg_id,
											   yg_property: node.yg_property,
											   yg_type: node.yg_type,
								   			   value: node.capt
											 } );
				$K.yg_AjaxCallback( data, 'setCBlockName' );
				break;
			case 'tag':
				var data = Array ( 'change', { name: 'tagname',
											   yg_id: node.yg_id,
											   yg_property: node.yg_property,
											   yg_type: node.yg_type,
								   			   value: node.capt
											 } );
				$K.yg_AjaxCallback( data, 'setTagName' );
				break;
			case 'template':
			case 'templatefolder':
				var data = Array ( 'change', { name: 'templatename',
											   yg_id: node.yg_id,
											   yg_property: node.yg_property,
											   yg_type: node.yg_type,
								   			   value: node.capt
											 } );
				$K.yg_AjaxCallback( data, 'setTemplateName' );
				break;
			case 'entrymask':
				var data = Array ( 'change', { name: 'entrymaskname',
											   yg_id: node.yg_id,
											   yg_property: node.yg_property,
											   yg_type: node.yg_type,
								   			   value: node.capt
											 } );
				$K.yg_AjaxCallback( data, 'setEntrymaskName' );
				break;
		}

	}
}

