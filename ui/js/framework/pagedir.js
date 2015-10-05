/**
 * Sets pagedir per page and page values
 * @param { Element } [obj] object clicked on
 * @function
 * @name $K.yg_pageDir
 */
$K.yg_pageDir = function(obj) {
	obj = $(obj);

	var winID = obj.up('.ywindow').id;
	if (obj.tagName == 'INPUT') {
		var value = obj.value;
	} else {
		var value = obj.readAttribute('value');
	}

	if (obj.up().hasClassName('textbox')) {
		// pagenum textfield changed
		// + validate field here
		var currMaxPages = parseInt($(winID+'_pagedir_'+$K.windows[winID].tab).down('div.currentpage').down('strong').innerHTML, 10);
		if (value < 1) value = 1;
		if (value > currMaxPages) value = currMaxPages;
		obj.value = value;
		$K.windows[winID].loadparams.pagedir_page = value;
		$K.windows[winID].loadparams.pagedir_from = (value-1) * parseInt($K.windows[winID].loadparams.pagedir_perpage, 10) + 1;
	} else {
		// objects per page changed
		if (obj.readAttribute('shortname')) {
			obj.up().previous().down().innerHTML = obj.readAttribute('shortname');
		}
		$K.windows[winID].loadparams.pagedir_perpage = value;
		$K.windows[winID].loadparams.pagedir_page = 1;
		$K.windows[winID].loadparams.pagedir_from = 1;
		$(winID+'_pagedir_'+$K.windows[winID].tab).down('div.currentpage').down('input').value = 1;
	}

	$K.windows[winID].tabs.select($K.windows[winID].tabs.selected, {refresh: 1}, false, true);
}


/**
 * Reset pageDir
 * @param { Element } [which] The inputfield
 * @param { Event } [e] The event which is fired
 * @function
 * @name $K.yg_pageDirIE
 */
$K.yg_pageDirIE = function(which, e) {
	if (Prototype.Browser.IE) {
		which = $(which);
		if (e.keyCode == 13) {
			$K.yg_pageDir(which);
		}
	}
}


/**
 * Reset pageDir
 * @param { Element } [win] window id
 * @function
 * @name $K.yg_pageDirReset
 */
$K.yg_pageDirReset = function(win) {
	$K.windows[win].loadparams['pagedir_page'] = 1;
	$K.windows[win].loadparams['pagedir_from'] = 1;
	$K.windows[win].loadparams['pagedir_limit'] = '0,'+$K.windows[win].loadparams['pagedir_perpage'];
	if ($(win+'_pagedir_'+$K.windows[win].tab)) {
		$(win+'_pagedir_'+$K.windows[win].tab).down('div.currentpage').down('input').value = 1;
	}
}

/**
 * Loads next page
 * @param { Element } [which] button clicked on
 * @function
 * @name $K.yg_pageDirNext
 */
$K.yg_pageDirNext = function(which) {
	which = $(which);
	var winID = which.up('.ywindow').id;

	var currMaxPages = parseInt($(winID+'_pagedir_'+$K.windows[winID].tab).down('div.currentpage').down('strong').innerHTML, 10);
	var currPage = $K.windows[winID].loadparams.pagedir_page;

	currPage++;
	if (currPage > currMaxPages) currPage = currMaxPages;
	$K.windows[winID].loadparams.pagedir_page = currPage;

	$K.windows[winID].loadparams.pagedir_from = (currPage-1) * parseInt($K.windows[winID].loadparams['pagedir_perpage'], 10) + 1;

	$(winID+'_pagedir_'+$K.windows[winID].tab).down('div.currentpage').down('input').value = currPage;

	$K.windows[winID].tabs.select($K.windows[winID].tabs.selected, {refresh: 1});
}

/**
 * Loads previous page
 * @param { Element } [which] button clicked on
 * @function
 * @name $K.yg_pageDirNext
 */
$K.yg_pageDirPrevious = function(which) {
	which = $(which);
	var winID = which.up('.ywindow').id;

	var currPage = $K.windows[winID].loadparams.pagedir_page;
	currPage--;
	if (currPage < 1) currPage = 1;
	$K.windows[winID].loadparams.pagedir_page = currPage;

	$K.windows[winID].loadparams.pagedir_from = (currPage-1) * parseInt($K.windows[winID].loadparams['pagedir_perpage'], 10) + 1;

	$(winID+'_pagedir_'+$K.windows[winID].tab).down('div.currentpage').down('input').value = currPage;

	$K.windows[winID].tabs.select($K.windows[winID].tabs.selected, {refresh: 1});
}


/*
$K.yg_pageDirOrderBy = function(which, orderBy) {
	which = $(which);
	var winID = which.up('.ywindow').id;

	if (which.hasClassName('sortasc')) {
		$K.windows[winID].loadparams.pagedir_orderdir = 'desc';
	} else {
		$K.windows[winID].loadparams.pagedir_orderdir = 'asc';
	}

/*	if ($K.windows[winID].loadparams.pagedir_orderby.toLowerCase() != orderBy.toLowerCase()) {
		$K.windows[winID].loadparams.pagedir_orderdir == 'desc';
	} else {
		if ($K.windows[winID].loadparams.pagedir_orderdir == 'asc') {
			$K.windows[winID].loadparams.pagedir_orderdir = 'desc';
		} else {
			$K.windows[winID].loadparams.pagedir_orderdir = 'asc';
		}
	}
	$K.windows[winID].loadparams.pagedir_orderby = orderBy;

	//which.removeClassName('sortasc');
	//which.removeClassName('sortdesc');
	//which.addClassName('sort'+$K.windows[winID].loadparams.pagedir_orderdir);

	$K.windows[winID].tabs.select($K.windows[winID].tabs.selected, {refresh: 1});
}
*/

/**
 * Re-orders list
 * @param { Element } [which] The header which was clicked on
 * @param { Event } [event] The event which triggered the click
 * @function
 * @name $K.yg_pageDirOrderBy
 */
$K.yg_pageDirOrderBy = function(which) {
	var winId = which.up('.ywindow').id;

	// Get current column
	var currColNum;
	var columns = $(which).up().immediateDescendants();

	for (var i = 0; i < columns.length; i++) {
		if ($(columns[i]) == $(which)) {
			currColNum = i;
			break;
		}
	}
	currColNum += 1;
	currColIndex = $(which).readAttribute('yg_colindex');

	// Set new sortcol and sortorder in window
	var oldSortColumn = $K.windows[winId].loadparams.pagedir_orderby;

	if (!$K.windows[winId].loadparams.pagedir_orderdir) {
		$K.windows[winId].loadparams.pagedir_orderdir = 1;
	}
	$K.windows[winId].loadparams.pagedir_orderby = currColIndex;
	if ($K.windows[winId].loadparams.pagedir_orderby == oldSortColumn) {
		$K.windows[winId].loadparams.pagedir_orderdir = ($K.windows[winId].loadparams.pagedir_orderdir * -1);
	} else {
		$K.windows[winId].loadparams.pagedir_orderdir = 1;
	}

	// Check if sorting by backend or frontend is needed
	if ($K.windows[winId].loadparams &&
		 ($K.windows[winId].loadparams.pagedir_perpage &&
		 (($K.windows[winId].loadparams.pagedir_perpage == -1) ||
		  ($K.windows[winId].loadparams.pagedir_perpage >= parseInt($(winId+'_objcnt').innerHTML, 10)))) ||
		  (!($K.windows[winId].loadparams.pagedir_perpage))) {

		// Sort by frontend (whole list is visible)
		TableKit.Sortable.sort(winId+'_tablecontent', currColNum );
	} else {
		// Sort by backend (pagedir is active)
		$K.windows[winId].tabs.select($K.windows[winId].tabs.selected, {refresh: 1});
	}
}



/**
 * Updates pagedir
 * @param { Element } [winID] window id
 * @function
 * @name $K.yg_pageDirUpdate
 */
$K.yg_pageDirUpdate = function(winID, currentPage, perPage, maxPages, orderBy, orderDir, from, limit) {
	if ($K.windows['wid_'+winID]) {
		$K.windows['wid_'+winID].loadparams.pagedir_page = currentPage;
		$K.windows['wid_'+winID].loadparams.pagedir_perpage = perPage;
		$K.windows['wid_'+winID].loadparams.pagedir_orderby = orderBy;


		$K.windows['wid_'+winID].loadparams.pagedir_orderdir = orderDir;
		if (orderDir == "ASC") $K.windows['wid_'+winID].loadparams.pagedir_orderdir = 1;
		if (orderDir == "DESC") $K.windows['wid_'+winID].loadparams.pagedir_orderdir = -1;

		$K.windows['wid_'+winID].loadparams.pagedir_from = from;
		$K.windows['wid_'+winID].loadparams.pagedir_limit = limit;

		if ($('wid_'+winID+'_pagedir_'+$K.windows['wid_'+winID].tab)) {
			if (maxPages==0) maxPages = 1;
			$('wid_'+winID+'_pagedir_'+$K.windows['wid_'+winID].tab).down('div.currentpage').down('strong').update(maxPages);
			$('wid_'+winID+'_pagedir_'+$K.windows['wid_'+winID].tab).down('div.currentpage').down('input').value = currentPage;
		}
	}
}

