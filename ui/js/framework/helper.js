/**
 * @fileoverview Helper functions for yeager/koala framework
 * @version 0.2.0
 */


// Other global dataholders
$K.yg_idlookuptable = new Object();
$K.yg_calendars = new Object();
$K.yg_SWFUploadObjects = {};
$K.yg_PLUploadObjects = {};
$K.yg_guiUpdateTimer = null;

// Global AJAX-Handler for progress indicator
Ajax.Responders.register({
	onCreate: function() {
		if ( (Ajax.activeRequestCount==1) && ($('bottomline')) ) $('bottomline').addClassName('loadingsmall');
	},
	onInteractive: function() {
		$K.yg_startGUIUpdateTimer();
		Ajax.activeRequestCount--;
		if ( (Ajax.activeRequestCount<=0) && ($('bottomline')) ) {
			Ajax.activeRequestCount = 0;
			$('bottomline').removeClassName('loadingsmall');
		}
	}
});

//Global AJAX-Handler for authentication-check
Ajax.Responders.register({
	onInteractive: function(response) {
		var yeagerHeader = (response.getHeader('X-Yeager-Authenticated') || '');
		if (yeagerHeader == 'false') {
			response.abort();
			$K.yg_loginbox();
		}
	}
});

// Autostart helper functions
$K._onDOMReady = new Array();
$K._onLoad = new Array();

$K.addOnDOMReady = function( autorun_func ) { $K._onDOMReady.push( autorun_func ); };
$K.addOnLoad = function( autorun_func ) { $K._onDOMReady.push( function(){window.setTimeout(function(){autorun_func()},0);} ); };

Event.observe(document, 'dom:loaded',  function( event ) {
	$K.log( '-> $K.onDOMReady()', $K.Log.DEBUG );
	$K._onDOMReady.each(function(item){
		if (typeof item == 'function')
			item( event );
	});
	delete $K._onDOMReady;
});

Event.observe(window, 'load',  function( event ) {
	$K.log( '-> $K.onLoad()', $K.Log.DEBUG );
	$K._onLoad.each(function(item){
		if (typeof item == 'function')
			item( event );
	});
	delete $K._onLoad;
});


/**
 * Helper function which returns special properties of an HTML element.
 * This function is used to get special attributes of the specified
 * element for sending them to the backend.
 * Only used internally.
 * @type Array of Strings
 * @param { Element } [element] The element to get the attributes from
 * @function
 * @name $K.yg_getAttributes
 */
$K.yg_getAttributes = function( element ) {
	// Bestimmte Attribute des Elementes auslesen und in Objekt schreiben
	var nodeInfo = new Object();
	for (i=0; i < element.attributes.length; i++) {

		var nn = element.attributes[i].nodeName;
		var nv = element.attributes[i].nodeValue;

		if  (
				(nn == 'id') ||
				(nn == 'name') ||
				(nn == 'value') ||
				(nn == 'yg_type') ||
				(nn == 'yg_id') ||
				(nn == 'yg_property')
			) {
			nodeInfo[nn] = nv;
		}

	}
	if (element.value) nodeInfo['value'] = $F(element);

	return nodeInfo;
}


/**
 * Helper function which initates the actual PHP callback.
 * Only used internally.
 * @param { Element } [element] The element whose properties should be sent to PHP
 * @param { String } [callback] The method on the PHP side which will called.
 * @param { Boolean } [no_auth] Set if authentication is needed (only set to true on login).
 * @param { String } [onlyLatestOfClass] Name of the class of the ajax request.
 * @param { Function } [onSuccess] A callback function which is called on success.
 * @function
 * @name $K.yg_AjaxCallback
 */
$K.yg_AjaxCallback = function( element, callback, no_auth, onlyLatestOfClass, onSuccess ) {

	var options = {
		asynchronous: true,
		evalScripts: true,
		method: 'post',
		requestHeaders: {
			'X-Yeager-Authentication': ( (no_auth)?('suppress'):('allow') )
		},
		parameters: {
			handler: $K.yg_trim(callback),
			us: document.body.id,
			lh: $K.yg_getLastGuiSyncHistoryId(),
			data: Object.toJSON( element )
		}
	};

	if (onlyLatestOfClass) {
		options.onlyLatestOfClass = onlyLatestOfClass;
	}

	if (onSuccess && (typeof onSuccess == 'function')) {
		options.onSuccess = onSuccess;
	}

	// Updater erstellen
	new Ajax.Updater('php_response', $K.appdir+'responder', options );
}


/**
 * Helper function to trim a string.
 * Only used internally.
 * @type String
 * @param { String } [inputString] The string to be trimmed
 * @function
 * @name $K.yg_trim
 */
$K.yg_trim = function( inputString ) {
	if (typeof inputString != 'string') { return inputString; }

	var retValue = inputString;
	var ch = retValue.substring(0, 1);

	while (ch == ' ') { // Check for spaces at the beginning of the string
		retValue = retValue.substring(1, retValue.length);
		ch = retValue.substring(0, 1);
	}
	ch = retValue.substring(retValue.length-1, retValue.length);

	while (ch == ' ') { // Check for spaces at the end of the string
		retValue = retValue.substring(0, retValue.length-1);
		ch = retValue.substring(retValue.length-1, retValue.length);
	}

	while (retValue.indexOf('  ') != -1) { // Look for multiple spaces within the string
		retValue = retValue.substring(0, retValue.indexOf("  ")) + retValue.substring(retValue.indexOf("  ")+1, retValue.length);
	}

	return retValue; // Return the trimmed string back to the user
}


$K.yg_updatePreviewUrls = function(winID, url) {
	// Set Status and buttons for pages (approved/edited)
	type = $K.windows[winID].yg_type;

	if (type == "page") {
		page = $K.windows[winID].yg_id.split('-')[0];
		site = $K.windows[winID].yg_id.split('-')[1];

		if ((url == "") || !url || (url.indexOf('//') > -1)) url = $K.appdir + site + "/" + page + "/";

		// preview
		var btn = $(winID + '_buttons').down('a.preview');
		if (btn) {
			btn.stopObserving('click');
			btn.observe('click', function(ev) {
				Event.stop(ev);
				$K.yg_preview({
					objecttype : 'page',
					id : page,
					site : site,
					version : 'live',
					url: url
				});
			});
			btn.href = url + "?version=live&nocache=true";
		}

		// live
		var btn = $(winID + '_buttons').down('a.previewcurrent');
		if (btn) {
			btn.stopObserving('click');
			btn.observe('click', function(ev) {
				Event.stop(ev);
				$K.yg_preview({
					objecttype : 'page',
					id : page,
					site : site,
					version : 'working',
					url: url
				});
			});
			btn.href = url + "?version=working&nocache=true";
		}
	}

	if (type == "file") {
		var btn = $(winID + '_buttons').down('a.download');
		btn.href = url;
	}
}


/**
 * Helper function to pretty-print a filesize.
 * Only used internally.
 * @type String
 * @param { Integer } [fileSize] The filesize to be beautyfied
 * @function
 * @name $K.yg_filesize
 */
$K.yg_filesize = function( fileSize ) {

	var unit = '';
	fileSize = parseInt(fileSize);

	if (fileSize >= 1000000000) {
		fileSize = fileSize / (1024 * 1024 * 1024);
		unit = 'GB';
	} else if (fileSize >= 1000000) {
		fileSize = fileSize / (1024 * 1024);
		unit = 'MB';
	} else if (fileSize >= 1000) {
		fileSize = fileSize / 1024;
		unit = 'KB';
	}

	if ( (fileSize >= 100) || (unit == 'KB') ) {
		fileSize = Math.round(fileSize);
	} else if (unit != '') {
		fileSize = fileSize.toFixed(1).replace(/\./, ',');
	}

	if (unit == '') {
		unit = 'Bytes';
	}

	return (fileSize+' '+unit);
}


/**
 * Toggles a collapse of a horizontal collapsable panel.
 * This function will be mapped to all horizontal collapsable panels automatically.
 * Only used internally.
 * @param { Element } [yg_object] The element which will be un/collapsed
 * @function
 * @name $K.yg_toggleCollapseH
 */
$K.yg_toggleCollapseH = function( yg_object ) {
	yg_object = $(yg_object);
	if (yg_object.$K.yg_collapsed) {

		// Restore relevant properties
		yg_object.$K.yg_collapsed = false;
		yg_object.setStyle({
					width: yg_object.yg_oldwidth+'px',
					overflow: yg_object.yg_oldoverflow
		});
		$K.yg_event = 'uncollapse';
	} else {

		// Save relevant properties
		yg_object.$K.yg_collapsed = true;
		yg_object.yg_oldwidth = yg_object.clientWidth;
		yg_object.yg_oldoverflow = yg_object.getStyle('overflow');
		yg_object.setStyle({
				overflow: 'hidden',
				width: $K.yg_panel_handle_width+'px'
		});
		$K.yg_event = 'collapse';
	}

	// Call PHP callback if defined
	if (yg_object.callback) {
		if (yg_object.callback['collapse']) {
			var data = Array ( $K.yg_event, $K.yg_getAttributes( yg_object ) );
			$K.yg_AjaxCallback( data, yg_object.callback[$K.yg_event] );
		}
		if (yg_object.callback['uncollapse']) {
			var data = Array ( $K.yg_event, $K.yg_getAttributes( yg_object ) );
			$K.yg_AjaxCallback( data, yg_object.callback[$K.yg_event] );
		}
	}
}


/**
 * Provides methods to attach and remove events to specified elements;
 * also provides a method to prevent default behaviour and/or to
 * prevent event propagation (also known as event bubbling)
 * Only used internally.
 * @function
 * @name $K.yg_event
 */
$K.yg_event = {
	add: function(obj, etype, fp, cap) {
		cap = cap || false;
		if (obj.addEventListener) obj.addEventListener(etype, fp, cap);
		else if (obj.attachEvent) obj.attachEvent("on" + etype, fp);
	},
	remove: function(obj, etype, fp, cap) {
		cap = cap || false;
		if (obj.removeEventListener) obj.removeEventListener(etype, fp, cap);
		else if (obj.detachEvent) obj.detachEvent("on" + etype, fp);
	},
	DOMit: function(e) {
		e = e? e: window.event;
		e.tgt = e.srcElement? e.srcElement: e.target;

		if (!e.preventDefault) e.preventDefault = function () { return false; }
		if (!e.stopPropagation) e.stopPropagation = function () { if (window.event) window.event.cancelBubble = true; }

		return e;
	}
}


/**
 * Toggles a collapse of a vertical collapsable panel.
 * This function will be mapped to all vertical collapsable panels automatically.
 * Only used internally.
 * @param { Element } [yg_object] The element which will be un/collapsed
 * @function
 * @name $K.yg_toggleCollapseV
 */
$K.yg_toggleCollapseV = function( yg_object, yg_self_object ) {
	yg_self_object = $(yg_self_object);
	yg_object = $(yg_object);

	if (yg_object.$K.yg_collapsed) {
		// Restore relevant properties
		yg_object.$K.yg_collapsed = false;
		if (yg_self_object)
			yg_self_object.setStyle({top:yg_self_object.yg_styletop});

		yg_object.setStyle({
				height: yg_object.yg_oldheight+'px',
				overflow: yg_object.yg_oldoverflow
		});
		$K.yg_event = 'uncollapse';
	} else {
		// Save relevant properties
		with (yg_object) {
			$K.yg_collapsed = true;
			yg_oldheight = clientHeight;
			yg_oldoverflow = getStyle('overflow');
			setStyle({
					overflow: 'hidden',
					height: $K.yg_panel_handle_width+'px'
			});
		}
		if (yg_self_object) {
			with (yg_self_object) {
				yg_styletop = getStyle('top');
				setStyle({top:'0px'});
			}
		}
		$K.yg_event = 'collapse';
	}
	// Call PHP callback if defined
	if (yg_object.callback) {
		if (yg_object.callback['collapse']) {
			var data = Array ( $K.yg_event, $K.yg_getAttributes( yg_object ) );
			$K.yg_AjaxCallback( data, yg_object.callback[$K.yg_event] );
		}
		if (yg_object.callback['uncollapse']) {
			var data = Array ( $K.yg_event, $K.yg_getAttributes( yg_object ) );
			$K.yg_AjaxCallback( data, yg_object.callback[$K.yg_event] );
		}
	}
}



/**
 * Helper function used create a Prototype-Template from a Smarty-Template
 * @param { String } [data] The data (HTML-Code) from Smarty
 * @function
 * @name $K.yg_makeTemplate
 */
$K.yg_makeTemplate = function ( data ) {
	// Return prepared template-chunk (and change '<<' & '>>' to '{' & '}' )
	return new Template( data.replace(/<</g,'{').replace(/>>/g,'}') );
}


/**
 * Helper function used set the edited style for an element
 * @param { String } [which] The element to change
 * @function
 * @name $K.yg_setEdited
 */
$K.yg_setEdited = function( which ) {
	which = $(which);
	which.addClassName('changed');
	if (which.hasClassName('error')) which.removeClassName('error');
}


/**
 * Helper function used to generate random unique strings.
 * Only used internally.
 * @type String
 * @param { Int } [length] The length of the string to be generated.
 * @function
 * @name yg_generateRandomID
 * @returns A random string of the specified length.
 */
function yg_generateRandomID(length) {
	if (!length)
		var length = 10;
	var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	var rnd_output = '';

	for(x=0;x<length;x++) {
		i = Math.floor(Math.random() * 62);
		rnd_output += chars.charAt(i);
	}
	return rnd_output;
}


/**
 * Pauses the execution of the script for the specified amount of milliseconds.
 * @param { Int } [milliseconds] time to pause in milliseconds
 * @function
 * @name $K.yg_pause
 */
$K.yg_pause = function(milliseconds) {
	var now = new Date();
	var exitTime = now.getTime() + milliseconds;
	while (true) { now = new Date(); if(now.getTime() > exitTime) return; }
}


/**
 * Check if all windows are loaded and bind them if necessary.
 * @function
 * @name $K.yg_bindWindows
 */
$K.yg_bindWindows = function() {
	tmparrx = $('maincontainer').childElements();
	tmparrwins = new Array();
	for (var k = 0; k < tmparrx.length; k++) {
		if (tmparrx[k].getStyle('display') != 'none') tmparrwins.push(tmparrx[k]);
	}
	if (tmparrwins.length > 1) {
		$K.windows[tmparrwins[0].id].boundWindow = tmparrwins[1].id;
		$K.windows[tmparrwins[1].id].boundWindow = tmparrwins[0].id;
	}
}


/**
 * Updates tags tree scrollbars (tags, templates, contentblocks)
 * @param { Element } [which] The element from which the function is called.
 * @function
 * @name yg_updateTagTreeScrolls
 * @name yg_updateTemplateTreeScrolls
 * @name yg_updateContentblockTreeScrolls
 * @name yg_updatePagesextraTreeScrolls
 * @name yg_updateEntrymaskTreeScrolls
 */
var yg_updateTagTreeScrolls =
	yg_updateTemplateTreeScrolls =
	yg_updateContentblockTreeScrolls =
	yg_updatePagesextraTreeScrolls =
	yg_updateEntrymaskTreeScrolls = function ( which ) {
		$K.windows[$(which).up('.ywindow').id].init();
	}


/**
 * Fires an action
 * @param { Function } [actionFunc] The function to call for the specified object.
 * @param { Object } [obj] The element to fire the action on.
 * @param { Event } [e] The event responsible for the firing (optional).
 * @function
 * @name $K.yg_fireAction
 */
$K.yg_fireAction = function(actionFunc, obj, e) {
	obj = $(obj);

	// check if it was a keyboard event (-> 46)
	var isKeyboardEvent = false;
	if (e == 46) {
		isKeyboardEvent = true;
	} else {
		if (e) Event.stop(e);
	}

	// Check if Tree
	if (obj.up('.ywindow').down('.wdgt_tree')) {
		// Is Tree
		if (isKeyboardEvent) {
			var winRef = $K.windows[obj.up('.ywindow').id];
			var winId = winRef.id.replace(/wid_/g, '');
			var treeId = winRef.yg_type+'s_tree'+winId+'_tree';
			if (nlsTree[treeId] && nlsTree[treeId].selElm && obj.up(2).reference) {
				obj.up(2).reference = nlsTree[treeId].selElm;
			}
		}

		// Check for multiselect
		if (obj.up('.ywindow').hasClassName('mk_multiselect')) {
			actionFunc(obj, true);
		} else {
			actionFunc(obj);
		}

		// Reset multi-/singleselect
		//$(obj).up('.ywindow').removeClassName('mk_multiselect');

	} else {
		// Is NOT Tree

		$K.actionhover = false;

		var multiaction = false;

		var focusobjs = $K.yg_getFocusObj(obj.up('.mk_contentgroup'));

		if (focusobjs.length > 1) {
			for (var k = 0; k < focusobjs.length; k++) {
				if ((focusobjs[k].up(0) == obj) || (focusobjs[k].up(1) == obj) || (focusobjs[k] == obj) || (focusobjs[k].down(0) == obj)) {
					multiaction = true;
				}
			}

			if (multiaction == true) {
				if (focusobjs[0].up('li') && (!obj.up('.mk_thumbcontainer'))) {
					// For everything else (listitem)
					tmparr = focusobjs.clone();
					tmparr.each(function(item) {
						actionFunc(item.up('li'));
					});
				} else if (obj.hasClassName('mk_comment') ||
				   // full featured multi-del for following objects
				   ((obj.readAttribute('yg_type') == 'mailing') || (obj.hasClassName('tree_btn_mailings'))) ||
				   (obj.id.startsWith('users_')) ||
				   (obj.hasClassName('mk_file')) ||
				   (obj.hasClassName('mk_autopublish')) ||
				   (obj.hasClassName('mk_trashitem')) ||
				   (obj.hasClassName('mk_cblock') && (obj.tagName == 'TR'))) { // For contentblocks in list view
						actionFunc(obj, true);
				} else {
					// For everything else
					tmparr = focusobjs.clone();
					tmparr.each(function(item) {
						actionFunc(item.up(0));
					});
				}
			}
		}

		if (multiaction != true) {
			actionFunc(obj);
		}
	}

}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @param { String } [prefix] The prefix of the id of the relevant element.
 * @function
 * @name $K.yg_actionSubmit
 */
$K.yg_actionSubmit = function( which, prefix, e ) {
	if (e) Event.stop(e);
	var wid = parseInt( which.up('.ywindow').id.replace(/wid_/g, '') );
	if (prefix) {
		if (prefix.endsWith('list')) {
			var yg_id = which.up('li').readAttribute('yg_id');
			$K.windows['wid_'+wid].yg_id = $('wid_'+wid).yg_id = yg_id;
		} else if (prefix=='block') {
			$K.yg_blockSelect(which.up('.emcontainer'), undefined);
			return;
		} else {
			refobj = $(prefix+wid+'_actionbutton').reference;
			$K.windows[which.up('.ywindow').id].yg_id = $K.windows['wid_'+wid].yg_id = nlsTree[prefix+wid+'_tree'].nLst[refobj.id].yg_id;
			nlsTree[prefix+wid+'_tree'].selectNode( nlsTree[prefix+wid+'_tree'].nLst[refobj.id].id );
		}
	}
	$K.windows['wid_'+wid].submit();
	$K.actionhover = false;
}

/**
 * Toggle's tree buttons (depending on if nothing/multi-selected
 * @param { String } [wndid] The wnd object
 * @function
 * @name $K.yg_toggleTreeButtons
 */
$K.yg_toggleTreeButtons = function(wndid) {
	if (!$(wndid+'_buttons')) return;
	tmparr = $(wndid+'_buttons').descendants();
	var focusObjCount = $K.yg_getFocusObj($(wndid)).length;

	// don't disable if tree
	if ( (focusObjCount == 0) &&
		 ($(wndid+"_"+$K.windows[wndid].tab.toUpperCase()).down('.wdgt_tree')) ) {
		var treeId = $(wndid+"_"+$K.windows[wndid].tab.toUpperCase()).down('.wdgt_tree').id;
		if (nlsTree[treeId + '_tree']) {
			focusObjCount = nlsTree[treeId + '_tree'].getSelNodes().length;
		}
	}

	if (focusObjCount == 0)  {
		tmparr.each(function(item) {
			btn = item.down('.tree_btn');
			if (btn && (btn.hasClassName('globalfunc') == false)) btn.addClassName('disabled');
		});
	} else if (focusObjCount > 1) {
		tmparr.each(function(item) {
			btn = item.down('.tree_btn');
			if (btn && (btn.hasClassName('globalfunc') == false)) {
				if (btn.hasClassName('multiselect') == false) {
					btn.addClassName('disabled');
				} else {
					btn.removeClassName('disabled');
				}
			}
		});
	}
}


/**
 * Returns elements yg_id
 * @param { Element } [reference] The element from which the function is called.
 * @function
 * @name $K.yg_getID
 */
$K.yg_getID = function(sourceElement) {
	src_id = false;
	if ((sourceElement.readAttribute('yg_id')) && (sourceElement.readAttribute('yg_id') != "")) {
		src_id = sourceElement.readAttribute('yg_id');
	} else {
		if (sourceElement.up('li')) {
			src_id = sourceElement.up('li').readAttribute('yg_id');
		}
	}
	return src_id;
}

/**
 * Returns elements yg_type
 * @param { Element } [reference] The element from which the function is called.
 * @function
 * @name $K.yg_getType
 */
$K.yg_getType = function(sourceElement) {
	src_type = false;
	if ((sourceElement.readAttribute('yg_id')) && (sourceElement.readAttribute('yg_id') != "")) {
		src_type = sourceElement.readAttribute('yg_type');
	} else {
		if (sourceElement.up('li')) {
			src_type = sourceElement.up('li').readAttribute('yg_type');
		}
	}
	return src_type;
}


/**
 * Delete the currently selected element (page/tag)
 * @param { Element } [reference] The element from which the function is called.
 * @param { Boolean } [multi] True if multiple items are selected.
 * @function
 * @name $K.yg_deleteElement
 */
$K.yg_deleteElement = function( reference, multi ) {

	reference = $(reference);

	if (reference.hasClassName('disabled')) {
		return;
	}

	if (reference.up('.ywindow')) {
		var winID = reference.up('.ywindow').id;
		var yg_id = $K.windows[winID].yg_id;
		var wid = parseInt( winID.replace(/wid_/g, '') );
	}

	if (reference.up('.dropstackcontainer')) {

		winId = reference.up('.ywindow').id;

		var focusObjs = $K.yg_getFocusObj(reference.up('.dropstackcontainer'));

		if (focusObjs.indexOf(reference.down() == -1)) {
		   reference.remove();
		} else {
			for (var i=0;i<focusObjs.length;i++) {
				focusObjs[i].up('li').remove();
			}
		}
		$K.windows[winId].refresh();

	} else if (reference.up('.mk_trashcan')) {

		var focusObjs = $K.yg_getFocusObj(reference.up('.mk_contentgroup'));
		// trashcan
		$K.yg_shredderObject(focusObjs[0]);

	} else if (reference.up('.wdgt_tree') ||  (reference.hasClassName('tree_btn') && $(winID+"_"+$K.windows[winID].tabs.elements[$K.windows[winID].tabs.selected].NAME).down('.wdgt_tree'))) {

		if (reference.up('.pages_tree') || (reference.hasClassName('tree_btn') && reference.hasClassName('tree_btn_page'))) {
			// pages
			objtype = "page";
			paramname = "page";
			tree = nlsTree['pages_tree'+wid+'_tree'];
			objname = $K.TXT('TXT_PAGES');
			callback = "deletePage";
		}
		if (reference.up('.cblocks_tree') || (reference.hasClassName('tree_btn') && reference.hasClassName('tree_btn_cblock'))) {
			// cblocks
			objtype = "cblock";
			paramname = "cblock";
			tree = nlsTree['cblocks_tree'+wid+'_tree'];
			objname = $K.TXT('TXT_OBJECTS');
			callback = "deleteCBlock";
		}
		if (reference.up('.files_tree') || (reference.hasClassName('tree_btn') && reference.hasClassName('tree_btn_file'))) {
			// cblocks
			paramname = "file";
			tree = nlsTree['files_tree'+wid+'_tree'];
			objname = $K.TXT('TXT_OBJECTS');
			callback = "deleteFolder";
		}
		if (reference.up('.templates_tree') || (reference.hasClassName('tree_btn') && reference.hasClassName('tree_btn_templates')) ) {
			// templates
			paramname = "template";
			tree = nlsTree['templates_tree'+wid+'_tree'];
			objname = $K.TXT('TXT_OBJECTS');
			callback = "deleteTemplate";
		}
		if (reference.up('.tags_tree') || (reference.hasClassName('tree_btn') && reference.hasClassName('tree_btn_tags')) ) {
			// templates
			paramname = "tagID";
			tree = nlsTree['tags_tree'+wid+'_tree'];
			objname = $K.TXT('TXT_TAGS');
			callback = "deleteTag";
		}
		if (reference.up('.entrymasks_tree') || (reference.hasClassName('tree_btn') && reference.hasClassName('tree_btn_entrymasks')) ) {
			// entrymasks
			paramname = "objectID";
			tree = nlsTree['entrymasks_tree'+wid+'_tree'];
			objname = $K.TXT('TXT_ENTRYMASKS');
			callback = "deleteEntrymask";
		}

		if (reference.hasClassName('tree_btn')) {
			var page = yg_id.split('-')[0];
			var site = yg_id.split('-')[1];
			if (multi) {
				var count = tree.getSelNodes().length;
				title = count +' '+objname;
			} else {
				title = tree.getSelNodes()[0].capt;
			}
		} else {
			selNodes = tree.getSelNodes();
			var inselection = false;
			var multi = false;
			for (var i = 0; i < selNodes.length; i++) {
				if (reference.id == selNodes[i].id) inselection = true;
			}
			if (inselection && selNodes.length > 1) multi = true;

			var nodeid = reference.id;
			var page = tree.nLst[nodeid].yg_id.split('-')[0];
			var site = tree.nLst[nodeid].yg_id.split('-')[1];
			if (multi) {
				var count = tree.getSelNodes().length;
				var title = count +' '+objname;
			} else {
				var title = reference.down('a').innerHTML;
			}
		}

		$K.yg_promptbox( $K.TXT('TXT_APPROVE_DELETE_TITLE'), $K.TXT('TXT_APPROVE_DELETE_P1') + title + $K.TXT('TXT_APPROVE_DELETE_P2'), 'remove', function(data) {
			if (multi) {
				var selectedNodes = tree.getSelNodes();
				for (var i=0;i<selectedNodes.length;i++) {
					var mpage = selectedNodes[i].yg_id.split('-')[0];
					var msite = selectedNodes[i].yg_id.split('-')[1];

					data = { }
					data[paramname] = mpage;
					data['site'] = msite;
					data['wid'] = wid;
					//data['target'] = target;

					var data = Array ( 'noevent', {yg_property: 'deletePage', params: data } );

					$K.yg_AjaxCallback( data, callback );
				}
				$($K.windows["wid_"+wid].id).removeClassName('multiselect');
			} else {

				data = { }
				data[paramname] = page;
				data['site'] = site;
				data['wid'] = wid;
				//data['target'] = target;

				var data = Array ( 'noevent', {yg_property: 'deletePage', params: data } );
				$K.yg_AjaxCallback( data, callback );

				// Check if trash has to be refreshed
				/*if ( (selectedNodes.length == 1) && (selectedNodes[0].orgId == 'page_trash') ) {
					$K.windows[$K.windows["wid_"+wid].boundWindow].tabs.select(Koala.windows[$K.windows["wid_"+wid].boundWindow].tabs.selected,{refresh:1});
				}*/
			}

			if ($K.windows[$K.windows["wid_"+wid].boundWindow].yg_id == (page+"-"+site)) {
				$($K.windows["wid_"+wid].boundWindow).addClassName('boxghost');
				$($K.windows["wid_"+wid].boundWindow+'_buttons').removeClassName('mk_folder');
				$K.windows[$K.windows["wid_"+wid].boundWindow].yg_id = $K.windows["wid_"+wid].yg_id = undefined;
				$K.windows[$K.windows["wid_"+wid].boundWindow].init();
			}

		}, function() {
			$K.log( 'Cancelled...', $K.Log.INFO );
		});

	} else if (reference.hasClassName('mk_cblock') && (reference.readAttribute('yg_property')=='listitem')) {
		// contentblock (in list view)

		// check if multi-delete
		var inselection = false;
		var focusObjs = $K.yg_getFocusObj(reference.up('.mk_contentgroup'));

		if (focusObjs.indexOf(reference) > -1) inselection = true;

		if (inselection) {
			if (focusObjs.length > 1) {
				var cblockName = focusObjs.length+' '+$K.TXT('TXT_OBJECTS');
			} else {
				var cblockName = focusObjs[0].readAttribute('name');
			}

			var cblockIds = new Array();
			focusObjs   .each(function(cblockItem){
				cblockIds.push(cblockItem.readAttribute('yg_id').split('-')[0]);
			});
		} else {
			var cblockName = reference.readAttribute('name');
			var cblockIds = reference.readAttribute('yg_id').split('-')[0];
		}

		$K.yg_promptbox( $K.TXT('TXT_APPROVE_DELETE_TITLE'), $K.TXT('TXT_APPROVE_DELETE_P1') + cblockName + $K.TXT('TXT_APPROVE_DELETE_P2'), 'standard', function(data) {
			var cblock = reference.readAttribute('yg_id').split('-')[0];
			var data = Array ( 'noevent', {yg_property: 'deleteCBlock', params: {
				cblock: cblockIds,
				site: 'cblock',
				multi: multi
			} } );
			$K.yg_AjaxCallback( data, 'deleteCBlock' );
		}, function() {
			$K.log( 'Cancelled...', $K.Log.INFO );
		});
	} else if ( (reference.hasClassName('cntblockcontainer') && reference.readAttribute('yg_type')=='mailing') ||
				(reference.hasClassName('tree_btn_mailings')) ) {
		// mailing

		// check if multi-delete
		var focusobjs = $K.yg_getFocusObj($(reference.up('.ywindow')).id+'_'+$K.windows[$(reference.up('.ywindow')).id].tab.toUpperCase());
		var mailingName;
		var mailingIds = new Array();

		if ((focusobjs.indexOf(reference) > -1) || (reference.hasClassName('tree_btn_mailings') && (focusobjs.length > 1))) multi = true;

		if (multi) {
			mailingName = focusobjs.length+' '+$K.TXT('TXT_OBJECTS');
			mailingIds = new Array();

			focusobjs.each(function(mailing){
				if (!mailing.hasClassName('mk_nodel')) {
					mailingIds.push(mailing.readAttribute('yg_id').split('-')[0]);
				}
			});
		} else {
			if (!reference.hasClassName('mk_nodel')) {
				if (reference.hasClassName('tree_btn_mailings')) {
					var mailingName = focusobjs[0].down('.objheadline ').innerHTML;
					var mailingIds = [focusobjs[0].readAttribute('yg_id').split('-')[0]];
				} else {
					var mailingName = reference.down('.objheadline ').innerHTML;
					var mailingIds = [reference.readAttribute('yg_id').split('-')[0]];
				}
			}
		}

		$K.yg_promptbox( $K.TXT('TXT_APPROVE_DELETE_TITLE'), $K.TXT('TXT_APPROVE_DELETE_P1') + mailingName + $K.TXT('TXT_APPROVE_DELETE_P2'), 'standard', function(data) {
			var data = Array ( 'noevent', {yg_property: 'deleteMailing', params: {
				mailingIds: mailingIds
			} } );
			$K.yg_AjaxCallback( data, 'deleteMailing' );
		}, function() {
			$K.log( 'Cancelled...', $K.Log.INFO );
		});

	} else	if (reference.hasClassName('mk_view') || (reference.up('li') && reference.up('li').hasClassName('mk_view')) ) {

		// view
		if (!reference.hasClassName('mk_nowrite')) {
			if (reference.tagName != 'LI') reference = reference.up('li');
			var view = reference.readAttribute('yg_id').split('-')[0];
			var fileID = yg_id.split('-')[0];
			var data = Array ( 'noevent', {yg_property: 'deleteFileView', params: {
				fileID: fileID,
				viewID: view
			} } );
			$K.yg_AjaxCallback( data, 'deleteFileView' );
		}

	} else if (reference.hasClassName('mk_tag')) {

		// tag
		if (!reference.hasClassName('mk_nowrite')) {
			var objectType = $K.windows[reference.up('.ywindow').id].yg_type;
			var tag = reference.readAttribute('yg_id').split('-')[0];
			var objectID = yg_id.split('-')[0];
			var siteID = yg_id.split('-')[1];
			var data = Array ( 'noevent', {yg_property: 'deleteObjectTag', params: {
				objectID: objectID,
				objectType: objectType,
				siteID: siteID,
				tagId: tag
			} } );

			$K.yg_AjaxCallback( data, 'deleteObjectTag' );
		}

	} else if (reference.hasClassName('mk_autopublish')) {

		if (!reference.hasClassName('mk_nowrite')) {
			// autopublish (for pages and cblocks)

			var focusobjs = $K.yg_getFocusObj(reference.up());
			if (focusobjs.indexOf(reference) == -1) {
				focusobjs = new Array(reference);
			}

			var type = reference.readAttribute('yg_type');
			switch(type) {
				case "cblock":  var action = "removeCBlockAutopublishItem";
								break;
				case "page":    var action = "removePageAutopublishItem";
								break;
			}


			focusobjs.each(function(item) {
				var innercontent = item.up('.innercontent');
				var item_id = item.id.split("_");
				item_id = item_id[item_id.length - 1];
				var page = yg_id.split('-')[0];
				var site = yg_id.split('-')[1];

				item.remove();
				$K.windows[winID].refresh(item);

				if (!item_id.startsWith('dummy')) {
					var data = Array ( 'noevent', {yg_property: action, params: {
						page: page,
						site: site,
						itemID: item_id
					} } );
					$K.yg_AjaxCallback( data, action );
				}

				$K.yg_removefromFocus(reference);
			});
		}

	} else if (reference.hasClassName('mk_usergroup')) {

		if (reference.hasClassName('mk_nodel')) return;

		// usergroups
		var roleId = reference.readAttribute('yg_id').split('-')[0];
		if (!yg_id) yg_id = reference.readAttribute('yg_id');

		if (yg_id.split('-')[1] == 'mailing') {
			// for mailings
			if (!reference.hasClassName('mk_nowrite')) {
				var mailingId = yg_id.split('-')[0];
				var params = {
					mode: 'mailings',
					mailingId: mailingId,
					roleId: roleId,
					wid: winID
				};
				var data = Array ( 'noevent', {yg_property: 'deleteUserGroup', params: params } );
				$K.yg_AjaxCallback( data, 'deleteUserGroup' );
			}
		} else if (yg_id.split('-')[1] == 'user') {
			// for users
			if (!reference.hasClassName('mk_nowrite')) {
				var userId = yg_id.split('-')[0];
				var params = {
					mode: 'users',
					userId: userId,
					roleId: roleId,
					wid: winID
				};
				var data = Array ( 'noevent', {yg_property: 'deleteUserGroup', params: params } );
				$K.yg_AjaxCallback( data, 'deleteUserGroup' );
			}
		} else if ((yg_id.split('-')[1] == 'usergroup') && (!reference.hasClassName('mk_nodel'))) {
			// usergroup admin
			var data = Array ( 'noevent', {yg_property: 'deleteRole', params: {
				roleID: roleId,
				wid: winID
			} } );
			$K.yg_AjaxCallback( data, 'deleteRole' );
			$K.yg_removefromFocus(reference);
		}

/*		// Is user admin
		var item = reference;
		var win_id = reference.up('.ywindow').id;
		var innercontent = reference.up('.innercontent');
		var roleID = item.readAttribute('yg_id').split('-')[0];
		var userID = yg_id.split('-')[0];
		var data = Array ( 'noevent', {yg_property: 'deleteUserGroup', params: {
			userID: userID,
			roleID: roleID,
			wid: win_id
		} } );
		$K.yg_AjaxCallback( data, 'deleteUserGroup' );

		$K.yg_removefromFocus(reference);*/

	} else if (reference.hasClassName('mk_site')) {

		// site
		var win_id = reference.up('.ywindow').id;
		var innercontent = reference.up('.innercontent');

		if (reference.hasClassName('tree_btn')) {
			var siteID = yg_id.split('-')[0];
		} else {
			var siteID = reference.readAttribute('yg_id').split('-')[0];
		}

		var sitename = $('sites_'+win_id.replace(/wid_/g,'')+'_'+siteID).down('.title.txt').innerHTML;

		$K.yg_promptbox( $K.TXT('TXT_APPROVE_DELETE_TITLE'), $K.TXT('TXT_APPROVE_DELETE_P1') + sitename + $K.TXT('TXT_APPROVE_DELETE_P2'), 'standard', function(data) {

			var data = Array ( 'noevent', {yg_property: 'deleteSite', params: {
				siteID: siteID,
				wid: win_id
			} } );
			$K.yg_AjaxCallback( data, 'deleteSite' );

			$K.yg_removefromFocus(reference);

		}, function() {
			$K.log( 'Cancelled...', $K.Log.INFO );
		});

	} else if (reference.hasClassName('mk_user')) {

		// user
		var win_id = reference.up('.ywindow').id;

		if (!reference.hasClassName('mk_nodel')) {
			if (reference.hasClassName('tree_btn')) {
				var userID = yg_id.split('-')[0];

				// topbutton, check if multiselect
				var multiselect = false;
				$K.yg_currentfocusobj.each(function(userItem){
					if ( (userItem.id.split('_')[2] == userID) && ($K.yg_currentfocusobj.length > 1)) {
						multiselect = true;
					}
				});
				if (multiselect) {
					var usercount = $K.yg_currentfocusobj.length;
					var username = usercount+' '+$K.TXT('TXT_USERS');
				} else {
					var username = $('users_'+win_id.replace(/wid_/g,'')+'_'+userID).down('a.user').innerHTML;
				}
			} else {
				// actionbutton
				if (multi) {
					var usercount = $K.yg_currentfocusobj.length;
					var username = usercount+' '+$K.TXT('TXT_USERS');
				} else {
					var username = reference.down('a.user').innerHTML;
				}
			}

			$K.yg_promptbox( $K.TXT('TXT_APPROVE_DELETE_TITLE'), $K.TXT('TXT_APPROVE_DELETE_P1') + username + $K.TXT('TXT_APPROVE_DELETE_P2'), 'standard', function(data) {
				if (multi) {
					var userIDs = new Array();
					$K.yg_currentfocusobj.each(function(userItem){
						if (!userItem.hasClassName('mk_nodel')) {
							userIDs.push(userItem.id.split('_')[2]);
						}
					});
					var data = Array ( 'noevent', {yg_property: 'delUser', params: {
						userID: userIDs,
						winID: win_id
					} } );
					$K.yg_AjaxCallback( data, 'delUser' );
				} else {
					if (reference.hasClassName('tree_btn')) {
						// user
						var item = reference;
						var innercontent = reference.up('.innercontent');
						var userID = yg_id.split('-')[0];

						// check if multiselect
						var multiselect = false;
						$K.yg_currentfocusobj.each(function(userItem){
							if (userItem.id.split('_')[2] ==  userID) {
								multiselect = true;
							}
						});

						var userIDs = new Array();
						if (multiselect) {
							$K.yg_currentfocusobj.each(function(userItem){
								if (!reference.hasClassName('mk_nodel')) {
									userIDs.push(userItem.id.split('_')[2]);
								}
							});
						} else {
							userIDs.push(userID);
						}
						var data = Array ( 'noevent', {yg_property: 'delUser', params: {
							userID: userIDs,
							winID: win_id
						} } );
						$K.yg_AjaxCallback( data, 'delUser' );
					} else {
						var userID = reference.id.split('_')[2];
						if (!reference.hasClassName('mk_nodel')) {
							var data = Array ( 'noevent', {yg_property: 'delUser', params: {
								userID: userID,
								winID: win_id
							} } );
							$K.yg_AjaxCallback( data, 'delUser' );
						}
					}
				}
				$K.yg_removefromFocus(reference);
			}, function() {
				$K.log( 'Cancelled...', $K.Log.INFO );
			});
		}

	} else if (reference.hasClassName('mk_file')) {

		// file
		var wid = parseInt( reference.up('.ywindow').id.replace(/wid_/g, '') );
		var nodeid = reference.id;
		var file = nodeid.split('_')[2];
		var site = nodeid.split('_')[0];

		if (reference.hasClassName('mk_filepreview')) {
			// thumb view
			if (multi) {
				var filecount = $K.yg_currentfocusobj.length;
				var filename = filecount+' '+$K.TXT('TXT_FILES');
			} else {
				var filename = reference.down('.filetitle').innerHTML;
			}

		} else {
			// list view
			if (multi) {
				var filecount = $K.yg_currentfocusobj.length;
				var filename = filecount+' '+$K.TXT('TXT_FILES');
			} else {
				var filename = reference.down('.filetitle').innerHTML;
			}

		}

		$K.yg_promptbox( $K.TXT('TXT_APPROVE_DELETE_TITLE'), $K.TXT('TXT_APPROVE_DELETE_P1') + filename + $K.TXT('TXT_APPROVE_DELETE_P2'), 'standard', function(data) {
			if (multi) {
				var selectedNodes = $K.yg_currentfocusobj;
				for (var i=0;i<selectedNodes.length;i++) {

					if (selectedNodes[i].hasClassName('mk_file')) {
						// list view
						var mfile = selectedNodes[i].id.split('_')[2];
					} else {
						// thumb view
						var mfile = selectedNodes[i].up().id.split('_')[2];
					}

					var data = Array ( 'noevent', {yg_property: 'deleteFile', params: {
						file: mfile,
						site: site
					} } );
					$K.yg_AjaxCallback( data, 'deleteFile' );
				}
				$($K.windows["wid_"+wid].id).removeClassName('multiselect');
			} else {

				var data = Array ( 'noevent', {yg_property: 'deleteFile', params: {
					file: file,
					site: site
				} } );
				$K.yg_AjaxCallback( data, 'deleteFile' );
			}

		}, function() {
			$K.log( 'Cancelled...', $K.Log.INFO );
		});

	} else if (	(reference.down(0).hasClassName('mk_cblock_edit')) ) {

		// contentblock in editmode (contentblock admin)
		var contentblock_lnkid = reference.id.split('_')[4];
		var page = yg_id.split('-')[0];
		var site = yg_id.split('-')[1];

		if (!reference.hasClassName('mk_nodel')) {
			var data = Array ( 'noevent', {yg_property: 'removeCBlockEntrymask', params: {
				contentblockLnkId: contentblock_lnkid,
				cblock: page,
				site: site
			} } );
			$K.yg_AjaxCallback( data, 'removeCBlockEntrymask' );
		}

	} else if (	(reference.down(0).hasClassName('mk_cblock')) ||
				(reference.down(0).hasClassName('mk_entrymask')) ) {

		// blind cblock
		var contentblock_lnkid = reference.id.split('_')[4];
		var page = yg_id.split('-')[0];
		var site = yg_id.split('-')[1];

		if (!reference.hasClassName('mk_nodel')) {
			$K.log( 'Trying to delete contentblock with linkid: ', contentblock_lnkid, $K.Log.INFO );
			var backendAction = 'removePageContentblock';
			if (contentblock_lnkid.startsWith('extension-')) {
				backendAction = 'removeObjectExtension';
			}

			var data = Array ( 'noevent', {yg_property: backendAction, params: {
				contentblockLnkId: contentblock_lnkid,
				page: page,
				site: site
			} } );
			$K.yg_AjaxCallback( data, backendAction );
		}

	} else if ( reference.hasClassName('mk_formfield') ) {

		// formfield
		var winID = reference.up('.ywindow').id;

		reference.remove();
		$K.windows[winID].refresh();

	} else if ( reference.hasClassName('mk_filetype') || reference.up().hasClassName('mk_filetype') ) {

		// filetype
		var winID = reference.up('.ywindow').id;
		var currentTab = $K.windows[winID].tab.toLowerCase();
		var focusobjs = $K.yg_getFocusObj($(winID+'_'+currentTab.toUpperCase()));

		if (focusobjs.indexOf(reference.down('.actions').next()) > -1) {
			delObjs = focusobjs;
		} else {
			delObjs = new Array(reference);
		}

		delObjs.each(function(currItem){
			if (currItem.up('li').id.indexOf('_NEW_')==-1) {
				var hiddenFields = currItem.up('li').select('input[type=hidden]');
				var newElement = new Element('input', {
					type:	'hidden',
					name:	winID+'_filetype_del_ids[]',
					value:	hiddenFields[hiddenFields.length-1].value
				});
				$(winID+'_filetypes_list').insert({bottom:newElement});
			}
			currItem.up('li').remove();
		});
		reference.remove();
		$K.windows[winID].refresh();

	} else if ( reference.up().hasClassName('mk_fileview') || reference.hasClassName('mk_fileview') ) {

		// fileview
		var winID = reference.up('.ywindow').id;
		var currentTab = $K.windows[winID].tab.toLowerCase();
		var focusobjs = $K.yg_getFocusObj($(winID+'_'+currentTab.toUpperCase()));

		if (focusobjs.indexOf(reference.down('.actions').next()) > -1) {
			delObjs = focusobjs;
		} else {
			delObjs = new Array(reference);
		}

		delObjs.each(function(currItem){
			if (currItem.id.indexOf('_NEW_')==-1) {
				var hiddenFields = currItem.up('li').select('input[type=hidden]');
				var newElement = new Element('input', {
					type:	'hidden',
					name:	winID+'_view_del_ids[]',
					value:	hiddenFields[hiddenFields.length-1].value
				});
				$(winID+'_views_list').insert({bottom:newElement});
			}
			currItem.up('li').remove();
		});
		reference.remove();
		$K.windows[winID].refresh();

	} else if ( reference.up().hasClassName('mk_property')  || reference.hasClassName('mk_property')) {

		// property
		var winRef = reference.up('.ywindow');
		var currentTab = $K.windows[winRef.id].tab.toLowerCase();
		switch(currentTab) {
			case 'config_page-properties':
				var propertyType = 'page';
				break;
			case 'config_cblock-properties':
				var propertyType = 'cblock';
				break;
			case 'config_file-properties':
				var propertyType = 'file';
				break;
			case 'config_user-properties':
				var propertyType = 'user';
				break;
		}

		var winID = reference.up('.ywindow').id;
		var focusobjs = $K.yg_getFocusObj($(winID+'_'+currentTab.toUpperCase()));

		if (focusobjs.indexOf(reference.down('.actions').next()) > -1) {
			delObjs = focusobjs;
		} else {
			delObjs = new Array(reference);
		}

		delObjs.each(function(currItem){
			if (currItem.id.indexOf('_NEW_')==-1) {
				var newElement = new Element('input', {
					type:	'hidden',
					name:	winID+'_properties_'+propertyType+'_del_tsuffixes[]',
					value:	currItem.select('input.mk_tsuffix')[0].value
				});
				$(winID+'_formfields_list').insert({bottom:newElement});
			}
			currItem.up('li').remove();
		});
		$K.windows[winID].refresh();

	} else if ( reference.hasClassName('mk_comment') ) {

		if (reference.up('div.cntmain') && !reference.up('div.cntmain').hasClassName('mk_comments_closed')) {
			// comment
			var winID = reference.up('.ywindow').id;
			var yg_id = reference.readAttribute('obj_yg_id');
			var yg_type = reference.readAttribute('obj_yg_type');
			var selCommentIDs = new Array();
			var selectedNodes = $K.yg_getFocusObj(reference.up('.ywindow'));

			var inselection = false;

			if (selectedNodes.indexOf(reference) > -1) inselection = true;

			if (inselection) {
				selectedNodes.each(function(item) {
					if (item.up('.mk_comment')) {
						selCommentIDs.push(item.up('.mk_comment').readAttribute('yg_id').split('-')[0]);
					} else if (item.hasClassName('mk_comment')) {
						selCommentIDs.push(item.readAttribute('yg_id').split('-')[0]);
					}
				});
			} else {
				selCommentIDs.push(reference.readAttribute('yg_id').split('-')[0]);
			}

			if (selCommentIDs.length > 0) {
				if (selCommentIDs.length > 1) {
					var innerText = selCommentIDs.length + ' ' + $K.TXT('TXT_COMMENTS');
				} else {
					var innerText = '1 ' + $K.TXT('TXT_COMMENT');
				}
				$K.yg_promptbox( $K.TXT('TXT_APPROVE_DELETE_TITLE'), $K.TXT('TXT_APPROVE_DELETE_P1') + innerText + $K.TXT('TXT_APPROVE_DELETE_P2'), 'remove', function(data) {
					var data = Array ( 'noevent', {yg_property: 'removeComment', params: {
						winID: winID,
						yg_type: yg_type,
						yg_id: yg_id,
						commentIDs: selCommentIDs
					} } );
					$K.yg_AjaxCallback( data, 'removeComment' );
				}, function() {
					$K.log( 'Cancelled...', $K.Log.INFO );
				});
			}
		}

	} else {

		$K.log( 'Unknown Element to delete...', $K.Log.INFO );
		$K.log( reference, $K.Log.INFO );
		$K.log( reference.down(0), $K.Log.INFO );
		$K.log( reference.up(2), $K.Log.INFO );

	}

}


/**
 * Helper function which returns the currently focused inputfield
 * @function
 * @name $K.yg_getActiveElement
 */
$K.yg_getActiveElement = function() {
	try {
		if (!Prototype.Browser.WebKit) {
			if ( (document.activeElement.tagName == 'INPUT') || (document.activeElement.tagName == 'TEXTAREA') ) {
				return document.activeElement;
			}
		} else {
			var found;
			var activeElements = $$('input:focus','textarea:focus');
			activeElements.each(function(item) {
				if (item.hasFocus) {
					found = item;
				}
			});

			return found;
		}
	}
	catch (ex) {}
	return;
}


/**
 * Helper function to fire late onchange events
 * @param { Boolean } [setEdited] Indicates if a field is set the "edited" state.
 * @param { Element } [context] Specifies the context to look for the active element
 * @param { Boolean } [noblur] Indicates that the field shouldn't be blurred
 * @function
 * @name $K.yg_fireLateOnChange
 */
$K.yg_fireLateOnChange = function(setEdited, context, noblur) {
	var activeElement = $K.yg_getActiveElement();
	if (activeElement) {
		if (context && !activeElement.descendantOf(context)) {
			return;
		}
		if (activeElement && (typeof activeElement.onchange == 'function')) {
			if (activeElement.readAttribute('nolateonchange')=='true') {
				return;
			}
			$K.log( 'late firing of onchange...', $K.Log.INFO );
			var onCh = activeElement.onchange.bind(activeElement);
			onCh();
			activeElement.removeClassName('changed');
			activeElement.setStyle({backgroundColor:''});
		}
		tmpel = activeElement.up(2);
		if (tmpel.hasClassName('dropdownbox')) {
			if ($(tmpel.id+"_ddlist")) {
				tmpel.insert($(tmpel.id+"_ddlist"));
				$(tmpel.id+"_ddlist").hide();
			}
		}
		var yg_onCh = activeElement.readAttribute('yg_onchange');
		if (yg_onCh) {
			var data = Array ( 'change', $K.yg_getAttributes( activeElement ) );
			$K.yg_AjaxCallback( data, yg_onCh );
		}
		if (setEdited) {
			$K.yg_setEdited(activeElement);
		} else if (typeof activeElement.blur == 'function') {
			if (!noblur) activeElement.blur();
			if (activeElement.onblur) {
				var onBl = activeElement.onblur.bind(activeElement);
				onBl();
			}
		}
	}
}


/**
 * Shows context-sensitive help in the statusbar
 * @param { Element } [which] The identifier of the helptext.
 * @function
 * @name $K.yg_showHelp
 */
$K.yg_showHelp = function(which) {
	if (which==false) {
		$('helpdesc').innerHTML = '';
	} else {
		if (which == "") {
			$K.warn('HELP not found');
			$('helpdesc').innerHTML = '$';
		} else {
			$('helpdesc').innerHTML = which
		}
	}
}


/**
 * Refreshes a sortable array of elements. (see: http://dev-answers.blogspot.com/2007/08/firefox-does-not-reflect-input-form.html)
 * @param { Element } [which] The identifier of the helptext.
 * @function
 * @name $K.updateDOM
 */
$K.updateDOM = function( which ) {
	which = $(which);
	if (which) {
		which.select('input','select','textarea').each(function(item){
			switch (item.type) {
				case 'select-one':
					for (var i=0; i<item.options.length; i++) {
						if (i == item.selectedIndex) {
							item.options[item.selectedIndex].writeAttribute('selected', 'selected');
						} else {
							item.options[item.selectedIndex].removeAttribute('selected');
						}
					}
					break;
				case 'text':
					item.writeAttribute('value', item.value);
					break;
				case 'textarea':
					item.innerHTML = item.value;
					item.writeAttribute('value', item.value);
					break;
				case 'checkbox':
				case 'radio':
					if (item.checked) {
						item.writeAttribute('checked', 'checked');
					} else {
						inputField.removeAttribute('checked');
					}
					break;
			}
		});
	}
}


/**
 * Fixes the CurrentFocusObjects Array
 * @function
 * @name $K.yg_fixCurrentFocusObjects
 */
$K.yg_fixCurrentFocusObjects = function() {
	var workArray = $K.yg_currentfocusobj;
	var newArray = new Array();

	workArray.each(function(item) {
		if ($(item) && ($(item).descendantOf($('maincontainer').up(1)))){
			if (!item.parentNode) {
				newArray.push( $(item.id) );
			} else if (item) {
				newArray.push(item);
			}
		}
	});
	$K.yg_currentfocusobj = newArray;
}

// Initializes
$K.addOnDOMReady(function() {
	$K.yg_initWindow();
});


// Prevent droppping on browser (and ghost handling)
$K.addOnDOMReady(function() {
	try {
		$K.yg_googleDesktop = google.gears.factory.create('beta.desktop');
	} catch (e) {}
	document.observe('dragover', function(e) {
		var target = Event.findElement(e);
		var dropAllowed = false;
		var pos = Event.pointer(e);

		$('yg_ddGhost').setStyle({
			display: 'block',
			left: (pos.x+18)+'px',
			top: (pos.y+2)+'px'
		});
		$('yg_ddGhost').down('img').setStyle({display: 'none', width: '0px', height: '0px', overflow: 'hidden' });

		if ((target.tagName == 'INPUT') && (target.type == 'file') || (target.hasClassName('mk_uploadpanel')) || (target.up('.mk_uploadpanel'))) {
			dropAllowed = true;
			$('yg_ddGhost').currentTarget = target;
		}

		if (e.dataTransfer && e.dataTransfer.mozItemCount) {
			var itemCnt = e.dataTransfer.mozItemCount;
			var itemTxt = itemCnt+' '+$K.TXT('TXT_OBJECTS');
			if (itemCnt == 1) {
				itemTxt = itemCnt+' '+$K.TXT('TXT_OBJECT');
			}
			itemTxt = '('+itemTxt+')';
		} else if ($K.yg_googleDesktop) {
			var itemCnt = 0;
			var dragData = $K.yg_googleDesktop.getDragData(e, 'application/x-gears-files');
			if (dragData) {
				itemCnt = dragData.count;
			}
			var itemTxt = itemCnt+' '+$K.TXT('TXT_OBJECTS');
			if (itemCnt == 1) {
				itemTxt = itemCnt+' '+$K.TXT('TXT_OBJECT');
			}
			itemTxt = '('+itemTxt+')';
			if (itemCnt == 0) {
				itemTxt = $K.TXT('TXT_UNKNOWN');
			}
		} else {
			var itemTxt = $K.TXT('TXT_UNKNOWN');
			dropAllowed = false;
		}
		$('yg_ddGhost').down('a.node').update(itemTxt);
		if (dropAllowed) {
			$K.yg_setDropAllowed(true);
		} else {
			$('yg_ddGhost').currentTarget = null;
			$K.yg_setDropAllowed(false);
		}
		e.preventDefault();
		Event.stop(e);
	});
	document.observe('dragleave', function(e) {
		$('yg_ddGhost').setStyle({display:'none'});
		$K.yg_setDropAllowed(false);
		e.preventDefault();
		Event.stop(e);
	});
	document.observe('drop', function(e) {
		$('yg_ddGhost').setStyle({display:'none'});
		$K.yg_setDropAllowed(false);
		e.preventDefault();
		Event.stop(e);
	});
});


$K.yg_urlCallback = function(strUrl, node, on_save, type) {
	//strUrl = strUrl.replace('http://www.yourbaseaddress.com','');
	return strUrl;
}

/**
 * changes CSS styles on the fly
 * @param { String } [classname] classname to change
 * @param { String } [element] element within css class
 * @param { String } [value] value
 * @param { String } [onthefly] just checks within onthefly.css (first one) to avoid huge iterations
 * @function
 * @name $K.yg_changeCSS
 */
$K.yg_changeCSS = function (classname, element, value, onthefly) {
	//Last Updated on June 23, 2009
	//documentation for this script at
	//http://www.shawnolson.net/a/503/altering-css-class-attributes-with-javascript.html
	var cssRules;
	var added = false;

	if (onthefly) {
		maxnum = 1;
	} else {
		maxnum = document.styleSheets.length;
	}

	for (var S = 0; S < maxnum; S++) {

		if (document.styleSheets[S]['rules']) {
			cssRules = 'rules';
		} else if (document.styleSheets[S]['cssRules']) {
	  		cssRules = 'cssRules';
	 	} else {
	  		//no rules found... browser unknown
	 	}

		for (var R = 0; R < document.styleSheets[S][cssRules].length; R++) {
			if (document.styleSheets[S][cssRules][R].selectorText == classname) {
				if(document.styleSheets[S][cssRules][R].style[element]) {
					if (!added) {
						document.styleSheets[S][cssRules][R].style[element] = value;
					}
					added=true;
					break;
				}
	   		}
	  	}
	}

	if(!added) {
		if(document.styleSheets[0].insertRule){
			document.styleSheets[0].insertRule(classname+' { '+element+': '+value+'; }',document.styleSheets[0][cssRules].length);
		} else if (document.styleSheets[0].addRule) {
			document.styleSheets[0].addRule(classname,element+': '+value+';');
		}
	}
}




/**
 * changes CSS styles on the fly
 * @param { String } [winID] Id of the window to reload (windowId or 'null'), optional
 * @param { String } [objId] Id of the object contained in the window to reload
 * @function
 * @name $K.yg_reloadWin
 */
$K.yg_reloadWin =  function (winID, objId) {
	if (winID == 'null') winID = null;

	if (!winID && objId) {
		for (winItem in $K.windows) {
			if ($K.windows[winItem].yg_id == objId) {
				// Reset cache flag for tab, if set
				$K.windows[winItem].tabs.elements[$K.windows[winItem].tabs.selected].cache = 0;
				$K.windows[winItem].tabs.select( $K.windows[winItem].tabs.selected );
			}
		}
	} else if (winID && $K.windows[winID]) {
		$K.windows[winID].tabs.elements[$K.windows[winID].tabs.selected].cache = 0;
		$K.windows[winID].tabs.select( $K.windows[winID].tabs.selected );
	}
}


/**
 * Checks if HTML5 upload is supported by the browser
 * @function
 * @name $K.yg_isHTML5UploadSupported
 */
$K.yg_isHTML5UploadSupported = function() {
	var xhr;
	if (window.XMLHttpRequest) {
		xhr = new XMLHttpRequest();
		return !!(xhr.sendAsBinary || xhr.upload);
	}
	return false;
}


/**
 * Checks if a string is numeric
 * @function
 * @name $K.yg_IsNumeric
 */
$K.yg_IsNumeric = function (sText) {
	var ValidChars = "0123456789";
	var IsNumber=true;
	var Char;

	if (sText) {
		for (i = 0; i < sText.length && IsNumber == true; i++) {
			Char = sText.charAt(i);
			if (ValidChars.indexOf(Char) == -1) {
			 IsNumber = false;
			}
		}
		return IsNumber;
	} else {
		return false;
	}
}


/**
 * Function to add an image to the browsers cache
 * @param { String } [identifier] The identifier for the image (to retrieve it later)
 * @param { String } [imagePath] The full absolute path to the image to be cached.
 * @function
 * @name $K.yg_addImageToCache
 */
$K.yg_addImageToCache = function(identifier, imagePath) {
	if (!$K.yg_cachedImages) {
		$K.yg_cachedImages = new Object();
	}
	$K.yg_cachedImages[identifier] = new Image();
	$K.yg_cachedImages[identifier].src = imagePath;
}

// When DOM is ready, check if there are images to cache
$K.addOnDOMReady(function() {
	for (iconKey in $K.icons) {
		$K.yg_addImageToCache(iconKey, $K.imgdir + 'icons/' + $K.icons[iconKey]);
	}
});
