/**
 * @fileoverview Provides navigation functionality + taskbar
 * @version 1.0
 */

Position.includeScrollOffsets = true;

$K.yg_launchFuncs = function() {
	if (document.body.hasClassName('unauthorized') == false) {
		$K.switchAdmin('start');
	}
}

if ($K.isAuthenticated) {
	// Is authenticated, show startpage
	$K.addOnDOMReady(function() {
		$K.yg_launchFuncs();
	});
} else {
	// Is NOT authenticated, show loginpage
	$K.addOnDOMReady(function() {
		$K.yg_loginbox();
	});
}

$K.addOnDOMReady(function() {
	$K.yg_customAttributeHandler($('mainnav'));
	if ((BrowserDetect.browser == 'Firefox') ||
		 (BrowserDetect.browser == 'Chrome') ) {
		// Chrome & Firefox
		$K.uploadFramework = 'plupload';
	} else if ( FlashDetect.installed && FlashDetect.majorAtLeast(8) ) {
		// Safari, Internet Explorer
		if (Prototype.Browser.IE && ((navigator.userAgent.indexOf('MSIE 8.0') > -1) || (navigator.userAgent.indexOf('MSIE 9.0') > -1))) {
			$K.uploadFramework = 'swfuploader';
		} else {
			$K.uploadFramework = 'plupload';
		}
	} else {
		// Everything else
		$K.uploadFramework = 'plupload';
	}
});


/**
 * Switch to admin
 * @param { String } [admin] admin to switch to.
 * @param { Integer } [siteID] optional, the site to switch to (only for pageadmin).
 * @function
 */
$K.switchAdmin = function(admin, siteID) {

	if (!$('maincontainer')) return;

	currwins = $('maincontainer').immediateDescendants();
	num = 0;
	var tmparrwids = new Array();
	for (var i = 0; i < currwins.length; i++) {
		if (currwins[i].hasClassName('ywindow')) {
			tmpid = currwins[i].readAttribute('id');
			tmparrwids[num] = new Object();
			tmparrwids[num].id = tmpid;
			tmparrwids[num].width = $K.windows[tmpid].boxwidth;
			tmparrwids[num].height = $K.windows[tmpid].boxheight;
			num++;
		}
	}
	var windowsToClose = new Array();
	for (var i = 0; i < tmparrwids.length; i++) {
		if (!($('maincontainer').hasClassName('mk_horizontal'))) {
			if ((tmparrwids.length > 1) && (!($('maincontainer').hasClassName('mk_ignorepanelwidth')))) {
				$K.panelwidth[i] = tmparrwids[i].width;
			}
			$K.panelheight = tmparrwids[i].height;
		}
		windowsToClose.push(tmparrwids[i].id);
	}
	if (($('maincontainer').hasClassName('mk_horizontal')) && ($K.panelheight != (document.viewport.getHeight() - $K.windowTopDiff - $('toolbar').getHeight()))) {
		$K.panelheight += $K.winhorizontalDiff;
	}
	var ygIdsToCheck = new Array();
	windowsToClose.each(function(winToClose){
		if($K.windows[winToClose].yg_id && $K.windows[winToClose].yg_type) {
			ygIdsToCheck.push($K.windows[winToClose].yg_id+'|'+$K.windows[winToClose].yg_type);
		}
	});
	ygIdsToCheck = ygIdsToCheck.uniq();

	var successFunc = function() {

		windowsToClose.each(function(winToClose){
			$K.windows[winToClose].remove(true);
		});

		$('maincontainer').removeClassName('mk_horizontal');
		$('maincontainer').removeClassName('mk_ignorepanelwidth');

		switch (admin) {
			case "start":			$('maincontainer').addClassName('mk_horizontal');
									new $K.yg_wndobj({ config: 'START_VERSIONS', width: $K.panelwidth[0]+$K.panelwidth[1], height: ($K.panelheight - 28)/2, loadparams: { yg_id: '-1', yg_type: 'recent' } });
									new $K.yg_wndobj({ config: 'START_SECTIONS', width: $K.panelwidth[0]+$K.panelwidth[1], height: ($K.panelheight - 28)/2, loadparams: { yg_id: '-1' } });
									$K.yg_loadDlgUpload();
									break;
			case "tags":			new $K.yg_wndobj({ config: 'TAG_DETAILS', width: $K.panelwidth[1], height: $K.panelheight });
									new $K.yg_wndobj({ config: 'TAGS_TREE', width: $K.panelwidth[0], height: $K.panelheight  });
									break;
			case "pages":			new $K.yg_wndobj({ config: 'PAGE_DETAILS', width: $K.panelwidth[1], height: $K.panelheight });
									if (siteID) {
										new $K.yg_wndobj({ config: 'PAGES_TREE', width: $K.panelwidth[0], height: $K.panelheight, loadparams: {site: siteID} });
									} else {
										new $K.yg_wndobj({ config: 'PAGES_TREE', width: $K.panelwidth[0], height: $K.panelheight });
									}
									break;
			case "contentblocks": 	new $K.yg_wndobj({ config: 'CBLOCK_DETAILS', width: $K.panelwidth[1], height: $K.panelheight  });
									new $K.yg_wndobj({ config: 'CBLOCKS_TREE', width: $K.panelwidth[0], height: $K.panelheight  });
									break;
			case "files":			new $K.yg_wndobj({ config: 'FILE_DETAILS', width: $K.panelwidth[1], height: $K.panelheight });
									new $K.yg_wndobj({ config: 'FILES_TREE', width: $K.panelwidth[0], height: $K.panelheight });
									break;
			case "comments":		new $K.yg_wndobj({ config: 'COMMENTS', width: $K.panelwidth[0]+$K.panelwidth[1], yg_id: -1, height: $K.panelheight, loadparams: {yg_id: '-1', type: 'allcomments'} });
									break;
			case "mailings":		new $K.yg_wndobj({ config: 'MAILINGS', width: $K.panelwidth[0]+$K.panelwidth[1], yg_id: -1, height: $K.panelheight, loadparams: {yg_id: '-1'} });
									break;
			case "updates":			new $K.yg_wndobj({ config: 'UPDATES', width: $K.panelwidth[0]+$K.panelwidth[1], yg_id: -1, height: $K.panelheight, loadparams: {yg_id: '-1'} });
									break;
			case "sites":			new $K.yg_wndobj({ config: 'SITE_DETAILS', width: $K.panelwidth[1], height: $K.panelheight });
									new $K.yg_wndobj({ config: 'SITE_LIST', width: $K.panelwidth[0], height: $K.panelheight });
									break;
			case "templates":		new $K.yg_wndobj({ config: 'TEMPLATE_DETAILS', width: $K.panelwidth[1], height: $K.panelheight });
									new $K.yg_wndobj({ config: 'TEMPLATES_TREE', width: $K.panelwidth[0], height: $K.panelheight });
									break;
			case "entrymasks":		new $K.yg_wndobj({ config: 'ENTRYMASK_DETAILS', width: $K.panelwidth[1], height: $K.panelheight });
									new $K.yg_wndobj({ config: 'ENTRYMASKS_TREE', width: $K.panelwidth[0], height: $K.panelheight });
									break;
			case "users":			$('maincontainer').addClassName('mk_ignorepanelwidth');
									new $K.yg_wndobj({ config: 'USER_DETAILS', width: Math.round(($K.panelwidth[0]+$K.panelwidth[1])/2), height: $K.panelheight, loadparams: { mode: 'details' } });
									new $K.yg_wndobj({ config: 'USERS_LIST', width: Math.round(($K.panelwidth[0]+$K.panelwidth[1])/2), height: $K.panelheight });
									break;
			case "usergroups":		new $K.yg_wndobj({ config: 'USERGROUP_DETAILS', width: $K.panelwidth[1], height: $K.panelheight });
									new $K.yg_wndobj({ config: 'USERGROUPS_LIST', width: $K.panelwidth[0], height: $K.panelheight });
									break;
			case "systemconfig":	var tabs = new Array();
									var tabIndex = 0;
									if ($('settitle_systemconfig') && $('settitle_systemconfig').hasClassName('mk_rproperties')) {
										tabs[tabIndex] = new Array();
										tabs[tabIndex]['NAME'] = 'CONFIG_PAGE-PROPERTIES';
										tabs[tabIndex]['CLASS'] = 'header_small';
										tabs[tabIndex]['TITLE'] = $K.TXT('TXT_PAGES');
										tabs[tabIndex]['CACHE'] = 0;
										tabs[tabIndex]['FOLDER'] = 0;
										tabIndex++;
										tabs[tabIndex] = new Array();
										tabs[tabIndex]['NAME'] = 'CONFIG_CBLOCK-PROPERTIES';
										tabs[tabIndex]['CLASS'] = 'header_small';
										tabs[tabIndex]['TITLE'] = $K.TXT('TXT_CONTENTBLOCKS');
										tabs[tabIndex]['CACHE'] = 0;
										tabs[tabIndex]['FOLDER'] = 0;
										tabIndex++;
										tabs[tabIndex] = new Array();
										tabs[tabIndex]['NAME'] = 'CONFIG_FILE-PROPERTIES';
										tabs[tabIndex]['CLASS'] = 'header_small';
										tabs[tabIndex]['TITLE'] = $K.TXT('TXT_FILES');
										tabs[tabIndex]['CACHE'] = 0;
										tabs[tabIndex]['FOLDER'] = 0;
										tabIndex++;
										tabs[tabIndex] = new Array();
										tabs[tabIndex]['NAME'] = 'CONFIG_USER-PROPERTIES';
										tabs[tabIndex]['CLASS'] = 'header_small';
										tabs[tabIndex]['TITLE'] = $K.TXT('TXT_USER');
										tabs[tabIndex]['CACHE'] = 0;
										tabs[tabIndex]['FOLDER'] = 0;
										tabIndex++;
									}
									if ($('settitle_systemconfig') && $('settitle_systemconfig').hasClassName('mk_rfiletypes')) {
										tabs[tabIndex] = new Array();
										tabs[tabIndex]['NAME'] = 'CONFIG_FILE-TYPES';
										tabs[tabIndex]['CLASS'] = 'header_small';
										tabs[tabIndex]['TITLE'] = $K.TXT('TXT_FILE_TYPES');
										tabs[tabIndex]['CACHE'] = 0;
										tabs[tabIndex]['FOLDER'] = 0;
										tabIndex++;
									}
									if ($('settitle_systemconfig') && $('settitle_systemconfig').hasClassName('mk_rviews')) {
										tabs[tabIndex] = new Array();
										tabs[tabIndex]['NAME'] = 'CONFIG_VIEWS';
										tabs[tabIndex]['CLASS'] = 'header_small';
										tabs[tabIndex]['TITLE'] = $K.TXT('TXT_VIEWS');
										tabs[tabIndex]['CACHE'] = 0;
										tabs[tabIndex]['FOLDER'] = 0;
										tabIndex++;
									}
									if ($('settitle_systemconfig') && $('settitle_systemconfig').hasClassName('mk_rcommentconfig')) {
										tabs[tabIndex] = new Array();
										tabs[tabIndex]['NAME'] = 'CONFIG_COMMENTS';
										tabs[tabIndex]['CLASS'] = 'header_small';
										tabs[tabIndex]['TITLE'] = $K.TXT('TXT_COMMENTS');
										tabs[tabIndex]['CACHE'] = 0;
										tabs[tabIndex]['FOLDER'] = 0;
										tabIndex++;
									}
									if ($('settitle_systemconfig') && $('settitle_systemconfig').hasClassName('mk_remailingconfig')) {
										tabs[tabIndex] = new Array();
										tabs[tabIndex]['NAME'] = 'CONFIG_MAILINGS';
										tabs[tabIndex]['CLASS'] = '';
										tabs[tabIndex]['TITLE'] = $K.TXT('TXT_MAILINGS');
										tabs[tabIndex]['CACHE'] = 0;
										tabs[tabIndex]['FOLDER'] = 0;
										tabs[tabIndex]['INIT'] = '$K.yg_initMailingConfig(this.wndobj.id.split(\'_\')[1]);';
										tabIndex++;
									}
									new $K.yg_wndobj({ config: 'SYSTEMCONFIG', width: $K.panelwidth[0]+$K.panelwidth[1], height: $K.panelheight, tabs: tabs });
									break;
			case "extensions":		new $K.yg_wndobj({ config: 'EXTENSION_DETAILS', width: $K.panelwidth[1], height: $K.panelheight });
									new $K.yg_wndobj({ config: 'EXTENSION_LIST', width: $K.panelwidth[0], height: $K.panelheight, loadparams: { objecttype: 'extensions' } });
									break;
			case "data":			$K.yg_openUploadProgress();
									new $K.yg_wndobj({ config: 'EXTENSION_DETAILS', width: $K.panelwidth[1], height: $K.panelheight });
									new $K.yg_wndobj({ config: 'DATA_LIST', width: $K.panelwidth[0], height: $K.panelheight, loadparams: { objecttype: 'data' } });
									break;
		}

		$K.yg_bindWindows();
		$K.yg_toolbarHistory(admin);
		$K.yg_currentAdmin = admin;
		$K.yg_blendNavigation("hide");
	}

	if (ygIdsToCheck.length == 1) {
		$K.yg_checkOpenWindows(ygIdsToCheck[0].split('|')[0], ygIdsToCheck[0].split('|')[1], {onSuccess: successFunc.bind(this)});
	} else {
		successFunc.bind(this)();
	}

}


/**
 * checks and updates the statusbar
 */
$K.yg_checkStatusBar = function() {
	if (!$('minwins')) return;
	statwid = $('minwins').getWidth();
	tmparrstat = $('minwins').childElements();
	nummins = 0;
	for (var k = 0; k < tmparrstat.length; k++) {
		if ((tmparrstat[k].hasClassName("klops")) && (tmparrstat[k].getStyle('display') != 'none')) {
			nummins++;
		}
	}
	maxmins = Math.floor(statwid / 186);
	if (nummins > maxmins) {
		$('winmanager').setStyle({display:'block'});
	} else {
		$('winmanager').setStyle({display:'none'});
	}
}


/**
 * opens the statuscontextmenu
 */
$K.yg_showStatusContext = function() {
	if ($('taskcontext')) $('taskcontext').remove();
	$('winmanager').appendChild(tmpdiv=document.createElement('div'));
	tmpdiv.className='taskcontext';
	tmpdiv.id='taskcontext';

	statwid = $('minwins').getWidth();
	maxmins = Math.floor((statwid - 25) / 145);
	tmparrstat = $('minwins').childElements();

	nummins = 0;
	for (var k = 0; k < tmparrstat.length; k++) {
		if ((tmparrstat[k].hasClassName("klops")) && (tmparrstat[k].getStyle('display') != 'none')) {
			nummins++;
			if (nummins > maxmins) {
				tmpdiv.appendChild(tmpa=document.createElement('a'));
				tmpa.id = tmparrstat[k].id.substring(0,tmparrstat[k].id.length-6);
				tmpa.onclick = function() { $K.windows[this.id].max(); }
				tmpa.appendChild(document.createTextNode(tmparrstat[k].title));
			}
		}
	}
	setTimeout('$K.yg_statusObserve("taskcontext")',100);
}


/**
 * Highlights main menu icon title
 * @param { String } [title] Title of the hovered icon
 */
$K.yg_navHover = function(title) {
	$('icondesc').innerHTML = title;
}


/**
 * Shows/hides main menu
 * @param { String } [action] "show" / "hide"
 * @param { String } [type] "main" / "toolbar"
 */
$K.yg_blendNavigation = function(action, type) {

	$('navextended').setStyle({display:'none'});
	$('navcollapsed').setStyle({display:'block'});
	$('navtoolbarcollapsed').setStyle({display:'block'});
	$('navtoolbarextended').setStyle({display:'none'});

	if (type == "main") {
		if (action == "show") {
			$('navcollapsed').setStyle({display:'none'});
			$('navextended').setStyle({display:'block'});
		}
	} else if (type == "toolbar") {
		if (action == "show") {
			$('navtoolbarcollapsed').setStyle({display:'none'});
			$('navtoolbarextended').setStyle({display:'block'});
		}
	}

}


/**
 * Starts resets timer for navigation show hide
 * @param { String } [timevar] "start" / "stop" / "startoff" / "stopoff"
 * @param { String } [type] "main" / "toolbar"
 */
$K.yg_navTimer = function(timevar, type) {
	if (!type) type= "main";

	if (timevar == "start") {
		$K.navtimer = setTimeout("$K.yg_blendNavigation('show','"+type+"')", 400);
	} else if (timevar == "stop") {
		clearTimeout($K.navtimer);
	} else if (timevar == "startoff") {
		$K.navtimeroff = setTimeout("$K.yg_blendNavigation('hide','"+type+"')", 150);
	} else if (timevar == "stopoff") {
		$K.yg_blendNavigation('show',type);
		clearTimeout($K.navtimeroff);
	}
}



/**
 * Changes the mainnav set
 * @param { String } [id] Id of the selected set
 * @param { Element } [obj] Object
 */
$K.yg_changeSet = function(id, obj) {
	tmparr = $('navsets').childElements();

	for (var k = 0; k < tmparr.length; k++) {
		tmpidstripped = tmparr[k].id.substring(4,tmparr[k].id.length);

		if ((tmparr[k].getStyle('display') != 'none') && (tmpidstripped != id)) {
			$('set_'+tmpidstripped).setStyle({display:'none'});
			$('settitle_'+tmpidstripped).setStyle({display:'none'});
		} else if ((tmparr[k].getStyle('display') != 'block') && (tmpidstripped == id)) {
			$('set_'+tmpidstripped).setStyle({display:'block'});
			$('settitle_'+tmpidstripped).setStyle({display:'block'});
		}
	}

	tmparr = $('sets').childElements();
	for (var k = 0; k < tmparr.length; k++) {
		tmparr[k].removeClassName("sel");
	}
	obj.addClassName("sel");
}


/**
 * Changes the mainnav set
 * @param { String } [id] Id of the selected set
 * @param { Element } [obj] Object
 */
$K.yg_changeSet = function(id, obj) {
	tmparr = $('navsets').childElements();

	for (var k = 0; k < tmparr.length; k++) {
		tmpidstripped = tmparr[k].id.substring(4,tmparr[k].id.length);

		if ((tmparr[k].getStyle('display') != 'none') && (tmpidstripped != id)) {
			$('set_'+tmpidstripped).setStyle({display:'none'});
			$('settitle_'+tmpidstripped).setStyle({display:'none'});
		} else if ((tmparr[k].getStyle('display') != 'block') && (tmpidstripped == id)) {
			$('set_'+tmpidstripped).setStyle({display:'block'});
			$('settitle_'+tmpidstripped).setStyle({display:'block'});
		}
	}

	tmparr = $('sets').childElements();
	for (var k = 0; k < tmparr.length; k++) {
		tmparr[k].removeClassName("sel");
	}
	obj.addClassName("sel");
}


/**
 * Toggle toolbar on / off
 */
$K.yg_toggleToolbar = function() {
	if ($('toolbar').hasClassName('expanded')) {
		tmparrx = $('maincontainer').childElements();
		for (var k = 0; k < tmparrx.length; k++) {
			if (tmparrx[k].getStyle('display') != 'none') {
				$K.windows[tmparrx[k].id].boxheight += 30;
				$K.windows[tmparrx[k].id].init();
			}
		}
		$('toolbar').removeClassName('expanded');
		$('navtoolbarextended').down('.mid').removeClassName('expanded');
	} else {
		$('toolbar').addClassName('expanded');
		$('navtoolbarextended').down('.mid').addClassName('expanded');
	}
	$K.yg_windowResized();
}


/**
 * Update toolbar history
 * @param { String } [location] push location
 */
$K.yg_toolbarHistory = function(location) {
	if (location == 'start') return;
	var traceArr = new Array();
	for (var k = 0; k < 3; k++) {
		tmpvar = $('toolbar').down(3).next(k).readAttribute('value');
		if ((tmpvar != null) && (tmpvar != location)) traceArr.push(tmpvar);
	}
	traceArr.push(location);

	if (traceArr.length > 3) traceArr.splice(0,1);

	for (var k = 0; k < traceArr.length; k++) {
		tmpobj = $('toolbar').down(3).next(k);
		tmpobj.show();
		tmpobj.writeAttribute('value', traceArr[k]);
		tmpobj.down(1).className = "icon" + traceArr[k];
		tmpobj.down(1).next().innerHTML = $K.TXT('TXT_'+traceArr[k].toUpperCase());
	}
}


/**
 * Opens an objects detail view in a dialog
 * @param { Int } [id] yg_id of object
 * @param { String } [objecttype] yg_id of object
 */
$K.yg_openObjectDetails = function(id, objecttype, title, icon, style) {
	var yg_id = id;

	// Check if a new (more current) state is available in the lookuptable
	if (yg_id.split('-').length < 2) {
		// Complete yg_id if necessary
		yg_id = yg_id + '-' + objecttype;
	}

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {
		if ( ( ($K.yg_idlookuptable[yg_id][i].yg_property == 'name') &&
		 	   ($K.yg_idlookuptable[yg_id][i].yg_type == objecttype) ) ) {

			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				// Is Tree element?
				/* do nothing for now */
			} else {
				// Normal element

				// Update changed style
				if ($K.yg_idlookuptable[yg_id][i].hasClassName('changed')) {
					style = 'changed';
				} else if (style == 'changed') {
					style = '';
				}

				// Update title
				if (objecttype == 'file') {
					// Is file
					if ($K.yg_idlookuptable[yg_id][i].down('.filetype')) {
						var color = $K.yg_idlookuptable[yg_id][i].down('.filetype').className.replace(/filetype/,'').strip();
						var typecode = $K.yg_idlookuptable[yg_id][i].down('.filetype').innerHTML.strip();
						if (icon.color) icon.color = color;
						if (icon.typecode) icon.typecode = typecode;
						if ($K.yg_idlookuptable[yg_id][i].innerHTML.lastIndexOf('>') != -1) {
							var titleOffset = $K.yg_idlookuptable[yg_id][i].innerHTML.lastIndexOf('>');
							if ($K.yg_idlookuptable[yg_id][i].innerHTML.substr(titleOffset+1).strip() != '') {
								title = $K.yg_idlookuptable[yg_id][i].innerHTML.substr(titleOffset+1).strip();
							}
						}
					} else {
						if ($K.yg_idlookuptable[yg_id][i].innerHTML.strip() != '') {
							title = $K.yg_idlookuptable[yg_id][i].innerHTML.stripScripts().stripTags().strip();
						}
					}
				} else {
					if ($K.yg_idlookuptable[yg_id][i].innerHTML.strip() != '') {
						title = $K.yg_idlookuptable[yg_id][i].innerHTML.stripScripts().stripTags().strip();
					}
				}
			}
		}
		if ( (objecttype == 'file') &&
			 ($K.yg_idlookuptable[yg_id][i].yg_property == 'type') &&
			 ($K.yg_idlookuptable[yg_id][i].yg_type == 'file') ) {
			if ($K.yg_idlookuptable[yg_id][i].hasClassName('filetype')) {
				var color = $K.yg_idlookuptable[yg_id][i].className.replace(/filetype/,'').strip();
				var typecode = $K.yg_idlookuptable[yg_id][i].innerHTML.strip();
				if (icon.color) icon.color = color;
				if (icon.typecode) icon.typecode = typecode;
			}
		}
	}

	switch (objecttype) {
		case 'file':			icon.objectid = id;
								new $K.yg_wndobj({ title: title, icon: icon, titleclass: style, config: 'FILEINFO', loadparams: { yg_id: id+'-file', ot: objecttype } } );
								break;
		case 'page':			new $K.yg_wndobj({ title: title, icon: icon, titleclass: style, config: 'PAGE_DETAILS', type: 'dialog', loadparams: { yg_id: id, yg_type: objecttype } } );
								break;
		case 'cblock':			new $K.yg_wndobj({ title: title, titleclass: style, config: 'CBLOCK_DETAILS', type: 'dialog', loadparams: { yg_id: id+'-cblock', yg_type: objecttype } } );
								break;
		case 'tag':				new $K.yg_wndobj({ title: title, titleclass: style, config: 'TAG_DETAILS', type: 'dialog', loadparams: { yg_id: id+'-tag', yg_type: objecttype } } );
								break;
		case 'mailing':			new $K.yg_wndobj({ title: title, titleclass: style, config: 'MAILING_DETAILS', type: 'dialog', loadparams: { mode: 'details', yg_id: id+'-mailing', yg_type: objecttype } } );
								break;
	}
}
