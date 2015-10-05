/** 
 * @fileoverview functions for comments
 *
 * @version 0.2.0
 */


/**
 * Inits version filter onload/reload of tab
 * @param { String } [win] window id
 * @function
 */
$K.yg_initCommentFilter = function(win) {
	if (!$K.windows[win].loadparams.commentfilter_timeframe) {
		$K.windows[win].loadparams.commentfilter_timeframe = 'ALL';
		$K.windows[win].loadparams.commentfilter_status = 'ALL';
	} else {
		for (var filtername in $K.windows[win].loadparams) {
			if (filtername.substring(0,13) == "commentfilter") {
				tmpobj = $(win+"_"+filtername);
				if (tmpobj) tmparr = tmpobj.immediateDescendants();
				for (var k=0; k<tmparr.length;k++) {
					if (tmparr[k].readAttribute("value") == $K.windows[win].loadparams[filtername]) {
						tmpobj.previous().down('span').innerHTML = tmparr[k].readAttribute("shortname");
					}
				}
			}
		}
	}
}


/**
 * Collapses / expands comments
 * @param { Element } [obj] object which has been clicked
 * @param { Event } [ev] event
 * @function
 */
$K.yg_toggleComment = function(obj, ev) {
	Event.stop(ev);
	commentdiv = $(obj).up('.commentpost');
	if (commentdiv.hasClassName('less')) {
		commentdiv.removeClassName('less');
		commentdiv.addClassName('more');
	} else {
		commentdiv.removeClassName('more');
		commentdiv.addClassName('less');	
	}
	$K.windows[commentdiv.up('.ywindow').id].refresh();	
}


/**
 * Sets filter
 * @param { String } [col] may be set to 'tab', 'action' or 'timeframe'
 * @param { String } [value] filter value
 * @param { String } [shorttitle] Shorttitle of selected filter
 * @param { String } [win] window id
 * @function
 */
$K.yg_filterComments = function(col, value, shorttitle, win) {
	
	if ((col == "timeframe") && (value == "CUSTOM")) {

		new $K.yg_wndobj({ config: 'COMMENTS_TIMEFRAME', loadparams: { opener_reference: win, action: 'comments', shorttitle: shorttitle } } );

	} else {
	
		// Reset pagedir to first page
		$K.yg_pageDirReset(win);

		$(win+'_commentfilter_'+col).previous().down().innerHTML = shorttitle;

		$K.windows[win].loadparams['commentfilter_'+col] = value;
		
		var params = new Object();
		params.commentfilter_timeframe = $K.windows[win].loadparams.commentfilter_timeframe;
		params.commentfilter_objecttype = $K.windows[win].loadparams.commentfilter_objecttype;
		params.commentfilter_status = $K.windows[win].loadparams.commentfilter_status;
		params.refresh = 1;
				
		var window = $K.windows[win];	
		window.tabs.select(window.tabs.selected, params);	
	}
}


/**
 * Submits custom timeframe to versions tab
 * @param { String } [winID] window id
 * @param { String } [openerReference] id of opener window
 * @param { String } [shorttitle] Shorttitle of selected filter
 * @function
 */
$K.yg_submitCommentTimeframe = function(winID, openerReference, shorttitle) {
	var timeframevar = $('wid_'+winID+'_timeframe_from').value + "###" + $('wid_'+winID+'_timeframe_till').value;				
	$K.yg_filterComments('timeframe', timeframevar, shorttitle, openerReference);
	$K.windows['wid_'+winID].remove();
}


/**
 * Changes status of one or more comments
 * @param { Element } [obj] obj
 * @param { String } [status] ok, spam or unapproved
 * @function
 */
$K.yg_changeCommentStatus = function(obj, status, event) {
	obj = $(obj);
	
	var winID = $(obj).up('.ywindow').id;
	var yg_id = $K.windows[winID].yg_id;
	var yg_type = $K.windows[winID].yg_type;
	var selCommentIDs = new Array();
	var selObjectIDs = new Array();
	var selTypes = new Array();
	var selComments = new Array();
	
	Event.stop(event);
	
	if (obj.down().next().hasClassName('listitempagefocus')) {
		// if selected check if multisel
		obj.up('.mk_contentgroup').immediateDescendants().each(function(comment){
			if (!comment.hasClassName('nowrite') && !comment.hasClassName('cntblockadd') && (comment.down().next().hasClassName('listitempagefocus'))) {
				selComments.push(comment);
				if (yg_type == 'comments') {
					selTypes.push(comment.readAttribute('obj_yg_type'));
				} else {
					selTypes.push(yg_type);	
				}
			}
		});	
	} else {
		// only one
		selComments.push(obj);
		if (yg_type == 'comments') {
			selTypes.push(obj.readAttribute('obj_yg_type'));
		} else {
			selTypes.push(yg_type);
		}
	}
	selComments.each(function(comment) {
		comment.down('.cstatus').className = "cstatus "+status;
		selCommentIDs.push(comment.readAttribute('yg_id').split('-')[0]);
		selObjectIDs.push(comment.readAttribute('obj_yg_id'))
	});	

	var data = Array ( 'noevent', {yg_property: 'setCommentState', params: {
		yg_types: selTypes,
		yg_ids: selObjectIDs,
		winID: winID,
		commentIDs: selCommentIDs,
		newStatus: status
	} } );
	
	$K.yg_AjaxCallback( data, 'setCommentState' );
}


/**
 * Changes commenting (opened/closed) of object
 * @param { Element } [obj] element which got clicked on
 * @function
 */
$K.yg_toggleCommenting = function(obj) {
	var newStatus;
	if (obj.up().hasClassName('copened')) {
		obj.up().removeClassName('copened');
		obj.up().addClassName('cclosed');
		obj.up('.cntmain').addClassName('mk_comments_closed');
		newStatus = 'closed';
	} else {
		obj.up().removeClassName('cclosed');
		obj.up().addClassName('copened');
		obj.up('.cntmain').removeClassName('mk_comments_closed');
		newStatus = 'opened';
	}
	
	var winID = $(obj).up('.ywindow').id;
	var yg_id = $K.windows[winID].yg_id;
	var yg_type = $K.windows[winID].yg_type;
	
	var data = Array ( 'noevent', {yg_property: 'setCommentingState', params: {
		yg_type: yg_type,
		yg_id: yg_id,
		winID: winID,
		newStatus: newStatus
	} } );
	
	$K.yg_AjaxCallback( data, 'setCommentingState' );
}


/**
 * Saves a Comment to the backend
 * @param { Object } [options] Options for saving
 * @function
 */
$K.yg_saveComment = function(options) {
	var data = Array ( 'noevent', {yg_property: 'saveComment', params: options } );
	$K.yg_AjaxCallback( data, 'saveComment' );
}


/**
 * Add a new Comment to an object
 * @param { Object } [options] Options for adding
 * @function
 */
$K.yg_addComment = function(options) {
	var data = Array ( 'noevent', {yg_property: 'addComment', params: options } );
	$K.yg_AjaxCallback( data, 'addComment' );
}


/**
 * Saves the comments settings (admin)
 * @param { Element } [which] Reference to the input field
 * @function
 */
$K.yg_saveCommentsSettings = function(which) {
	which = $(which);
	
	var winID = which.up('.ywindow').id;
	
	var commentSettings = $(winID+'_CONFIG_COMMENTS').select('input', 'textarea');
	
	var params = new Object();
	commentSettings.each(function(item){
		params[item.name] = item.value;
		
	});
	params.winID = winID;
	
	var data = Array ( 'noevent', {yg_property: 'saveCommentsSettings', params: params } );
	$K.yg_AjaxCallback( data, 'saveCommentsSettings' );
}


/**
 * Updates comment-count
 * @param { String } [win] window id
 * @param { Integer } [comment_count] comment count
 * @function
 */
$K.yg_updateCommentCount = function(win, comment_count) {
	if ($(win+'_head_COMMENTS')) {
		$(win+'_head_COMMENTS').down('.mk_comment_count').update(comment_count);
	}
}


/**
 * Set commenting status
 * @param { String } [win] window id
 * @param { String } [status] status (open/closed)
 * @function
 */
$K.yg_setCommentStatus = function(win, status) {
	switch(status) {
		case 'open':
			if ($(win+'_cnttable')) {
				$(win+'_cnttable').down('.cntmain').removeClassName('mk_comments_closed');
			}
			if ($(win+'_head_COMMENTS')) {
				$(win+'_head_COMMENTS').down('.headstatus').removeClassName('cclosed').addClassName('copened');
			}
			break;
		case 'closed':
			if ($(win+'_cnttable')) {
				$(win+'_cnttable').down('.cntmain').addClassName('mk_comments_closed');
			}
			if ($(win+'_head_COMMENTS')) {
				$(win+'_head_COMMENTS').down('.headstatus').removeClassName('copened').addClassName('cclosed');
			}
			break;
	}
}
