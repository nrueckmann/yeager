/**
 * @fileoverview Provides functionality for preview mode
 * @version 1.0
 */
$K.previewRefreshInt = false;

/**
 * Open preview-mode
 * @param { Array } [params] Parameters: fullscreen, objecttype, version, date, view, onload
 */
$K.yg_preview = function(params) {

	if (params.onload) {
		if ($('ygpreviewclosediv')) $('ygpreviewclosediv').remove();
		params.fullscreen = true;
	}

	// filepreview
	if (params.objecttype == "file") {

		if (params.fullscreen) {
			$('previewcontainer').setStyle({display:'block'});
			$('filepreview').setStyle({display:'block'});
			$('filepreview').addClassName("tab_loading");
			$('filepreviewcontainer').setStyle({visibility:'visible'});
			new Draggable($('filepreviewcontainer'),{});
			$('filepreviewwrap').innerHTML = "";
			$('filepreviewwrap').appendChild(previmg=document.createElement('img'));
			config = "PREVIEW_FULLSCREEN_FILE";
			//url = $K.appdir+'window?wt=dialog&display=preview';
		} else {
			config = "PREVIEW_WINDOW_FILE";
		}
		if (!params.crop) params.crop = false;
		if (!params.onload) params.onload = false;

		var data = {
			objecttype: params.objecttype,
			objectid: params.id,
			view: params.view,
			version: params.version,
			crop: params.crop,
			zoom: params.zoom,
			onload: params.onload
		}

		wndobj = new $K.yg_wndobj({ config: config, loadparams: data });
		if (!params.fullscreen) $(wndobj.id).hide();

	}

	// pagepreview
	if (params.objecttype == "page") {
		data = {
			objecttype: params.objecttype,
			site: params.site,
			objectid: params.id,
			version: params.version
		}
		wndobj = new $K.yg_wndobj({ config: 'PREVIEW_FULLSCREEN_PAGE', loadparams: data });
		$(wndobj.id).hide();

		$('previewcontainer').setStyle({display:'block'});
		$('previewcontainer').addClassName("page");
		$('previewiframe').setStyle({display:'block'});
		previewurl = params.url;
		if (params.version) {
			previewurl += "?version="+params.version+"&";
		} else {
			previewurl += "?";
		}
		previewurl += "nocache=true";
		$('previewiframe').src = previewurl;
	}

	// mailingpreview
	if (params.objecttype == "mailing") {

		data = {
			objecttype: params.objecttype,
			objectid: params.id,
			version: params.version
		}
		wndobj = new $K.yg_wndobj({ config: 'PREVIEW_FULLSCREEN_MAILING', loadparams: data });
		$(wndobj.id).hide();

		$('previewcontainer').setStyle({display:'block'});
		$('previewcontainer').addClassName("page");
		$('previewiframe').setStyle({display:'block'});
		previewurl = $K.appdir + "mailing/"+params.id+"?";
		if (params.version) previewurl += "&version="+params.version;
		previewurl += "&nocache=true";

		$('previewiframe').src = previewurl;
	}

	if (params.objecttype == "cblock") {
		alert("PREVIEWING CBLOCK");
	}

}


/**
 * Switch version
 * @param { Array } [params] Parameters: objecttype, id, site, version, view, zoom, win, fullscreen
 */
$K.yg_switchPreviewVersion = function(params) {
	window.clearTimeout($K.previewRefreshInt);
	switch (params.objecttype) {
		case 'mailing':
			previewurl = $K.appdir + "mailing/"+params.id;
			if (params.version) previewurl += "?version="+params.version;
			$('previewiframe').src = previewurl;
			break;

		case 'page':
			if ($('previewiframe').src.indexOf('?') > -1) {
				previewurl = $('previewiframe').src.split("?")[0];
			} else {
				previewurl = $('previewiframe').src;
			}
			if (params.version) previewurl += "?version="+params.version;
			$('previewiframe').src = previewurl;
			break;

		case 'file':
			var data = Array ( 'noevent', { params: params } );
			if (params.fullscreen) {
				$('filepreviewwrap').innerHTML = "";
				$('filepreview').addClassName('tab_loading');
			} else {
				$('wid_'+params.win+'_innercontent').down().innerHTML = "";
				$K.windows['wid_'+params.win].refresh();
				$('wid_'+params.win+'_innercontent').addClassName('tab_loading');
			}
			$K.yg_AjaxCallback( data, 'refreshFileVersionDetails' );
			break;
	}
}


/**
 * Switch view
 * @param { Array } [params] Parameters: id, version, view, zoom, win, fullscreen
 */
$K.yg_switchPreviewView = function(params) {
	window.clearTimeout($K.previewRefreshInt);
	view = params.view.split('#!#!#')[0];
	viewtype = params.view.split('#!#!#')[1];
	if ((viewtype == null) || (viewtype == "null") || (viewtype == "")) {
		$K.yg_switchPreviewVersion({objecttype: 'file', win: params.win, id: params.id, fullscreen: params.fullscreen, crop: params.crop, zoom: params.zoom, view: view, version: params.version, url: params.url});
	} else {
		$K.yg_renderFilePreview({id: params.id, view: view, version: params.version, viewtype: viewtype, zoom: params.zoom, win: params.win, fullscreen: params.fullscreen, url: params.url});
	}
}


/**
 * Switch zoom
 * @param { Array } [params] Parameters: win, zoom, fullscreen
 */
$K.yg_switchPreviewZoom = function(params) {
	if (params.fullscreen) {
		obj = $('filepreviewwrap').down('img');
	} else {
		obj = $('wid_'+params.win+'_innercontent').down(1);
	}
	obj.writeAttribute('zoomlevel',params.zoom);
	newwidth = Math.round(parseInt(obj.readAttribute('origwidth'))*params.zoom/100);
	newheight = Math.round(parseInt(obj.readAttribute('origheight'))*params.zoom/100);
	obj.setStyle({width:newwidth+'px'});
	obj.setStyle({height:newheight+'px'});
	if (params.fullscreen) {
		if ($K.yg_cropInstance != false) {
			$K.yg_cropPreview(params.win,false,params.zoom);
			$K.yg_cropPreview(params.win,false,params.zoom);
			$K.yg_cropZoom = params.zoom;
		}
		$K.yg_centerPreview();
	} else {
		$K.windows['wid_'+params.win].refresh();
	}
}


/**
 * Update file preview (after versionswitch)
 * @param { Int } [id] object id
 * @param { Int } [version] version
 * @param { Array } [views] JSON array
 * @param { Int } [win] window no.
 * @param { Boolean } [fullscreen] fullscreen mode
 */
$K.yg_updateFilePreview = function(id, version, views, zoom, win, fullscreen, versions, url) {

	if (!($("wid_"+win))) return;

	views = views.evalJSON();
	if (versions && (versions!='false') && (versions!='null')) {
		versions = versions.evalJSON();
		// insert new version
		if (versions[0]) {
			$K.yg_dropdownInsert($("wid_"+win+"_ddversion"),versions[0]["TITLE"],versions[0]["VERSION"],true,'top');
		}
	}

	// clear dropdown views
	$("wid_"+win+"_ddview_ddlist").down().innerHTML = "";

	for (var i = 0; i < views.length; i++) {

		title = views[i]["NAME"];
		if (views[i]["IDENTIFIER"] == "YGSOURCE") title = $K.TXT('TXT_SOURCEFILE');
		title += ": ";
		if ((views[i]["WIDTH"] != 0) && (views[i]["WIDTH"] != null)) { title += views[i]["WIDTH"] } else { title += "[-]"; }
		title += " x ";
		if ((views[i]["HEIGHT"] != 0) && (views[i]["HEIGHT"] != null)) { title += views[i]["HEIGHT"] } else { title += "[-]"; }

		value = views[i]["IDENTIFIER"] + "#!#!#" + views[i]["VIEWTYPE"] + "#!#!#" + views[i]["WIDTH"] + "#!#!#" + views[i]["CONSTRAINWIDTH"] + "#!#!#" + views[i]["HEIGHT"] + "#!#!#" + views[i]["CONSTRAINHEIGHT"];
		selected = false;
		if (views[i]["SEL"]) selected = true;

		$K.yg_dropdownInsert($("wid_"+win+"_ddview"),title,value,selected);
		if (views[i]["SEL"]) {
			$K.yg_renderFilePreview({id: id, url: url, view: views[i]["IDENTIFIER"], version: version, viewtype: views[i]["VIEWTYPE"], zoom: $("wid_"+win+"_ddzoom").down().next('input').value, win: win, fullscreen: fullscreen, viewwidth: views[i]["WIDTH"], constrainwidth: views[i]["CONSTRAINWIDTH"], viewheight: views[i]["HEIGHT"], constrainheight: views[i]["CONSTRAINHEIGHT"]});
		}
	}
	$K.yg_initDropdown($("wid_"+win+"_ddview"));
}


/**
 * Create image preview
 * @param { Int } [id] object id
 * @param { String } [view] view identfier
 * @param { Int } [version] version
 * @param { Int } [viewtype] view type
 * @param { Int } [win] window id
 * @param { Boolean } [fullscreen] fullscreen mode
 * @param { Int } [viewwidth] views width, 0 if none
 * @param { Boolean } [contrainwidth] fixed width
 * @param { Int } [viewheight] views height, 0 if none
 * @param { Boolean } [contrainheight] fixed height
 * @param { Boolean } [crop] cropmode
 */
$K.yg_renderFilePreview = function(params) {

	if ((params.fullscreen == undefined) || (params.fullscreen == null)) params.fullscreen = false;

	if (params.fullscreen) {
		$('filepreviewwrap').innerHTML = "";
		$('filepreview').addClassName('tab_loading');
		$('filepreviewcontainer').writeAttribute('yg_draggable','false');
		$('filepreviewcontainer').addClassName('nodrag');
	} else {
		$('wid_'+params.win+'_innercontent').down().innerHTML = "";
		$('wid_'+params.win+'_innercontent').addClassName('tab_loading');
	}
	if ($("wid_"+params.win+"_croplink")) {
		$("wid_"+params.win+"_croplink").removeClassName("disabled");
	}
	if ($("wid_"+params.win+"_downloadlink") && params.url) {
		$("wid_"+params.win+"_downloadlink").removeClassName("disabled");
		$("wid_"+params.win+"_downloadlink").onclick = function() { };
		$("wid_"+params.win+"_downloadlink").writeAttribute("href",params.url + "?version="+params.version);
	}
	if ((params.viewtype == null) || (params.viewtype == "null") || (params.viewtype == "")) { // NOT GENERATED
		if (params.fullscreen) {
			$('filepreviewwrap').innerHTML = $('wid_'+params.win+'_notgenerated').innerHTML;
			$('filepreviewwrap').addClassName('nopreview');
			$('filepreview').removeClassName('tab_loading');
			$K.yg_centerPreview();
		} else {
			$('wid_'+params.win+'_innercontent').down().innerHTML = $('wid_'+params.win+'_notgenerated').innerHTML;
			$('wid_'+params.win+'_innercontent').removeClassName('tab_loading');
		}
		if ($("wid_"+params.win+"_downloadlink")) {
			$("wid_"+params.win+"_downloadlink").onclick = function(ev) { Event.stop(ev); }
			$("wid_"+params.win+"_downloadlink").addClassName("disabled");
			$("wid_"+params.win+"_downloadlink").writeAttribute("href","");
		}
		if ($("wid_"+params.win+"_croplink")) {
			$("wid_"+params.win+"_croplink").addClassName("disabled");
		}
		params.objecttype = 'file';
		$K.previewRefreshInt = $K.yg_switchPreviewVersion.delay(1, params);

	} else if (parseInt(params.viewtype) == 0) { // FILE_TYPE_WEBNONE

		if (params.fullscreen) {
			$('filepreviewwrap').innerHTML = $('wid_'+params.win+'_nopreview').innerHTML;
			$('filepreviewwrap').addClassName('nopreview');
			$('filepreview').removeClassName('tab_loading');
			$K.yg_centerPreview();
		} else {
			$('wid_'+params.win+'_innercontent').down().innerHTML = $('wid_'+params.win+'_nopreview').innerHTML;
			$('wid_'+params.win+'_innercontent').removeClassName('tab_loading');
		}
		if ($("wid_"+params.win+"_downloadlink")) {
			$("wid_"+params.win+"_downloadlink").onclick = function(ev) { Event.stop(ev); }
			$("wid_"+params.win+"_downloadlink").addClassName("disabled");
			$("wid_"+params.win+"_downloadlink").writeAttribute("href","");
		}
		if ($("wid_"+params.win+"_croplink")) {
			$("wid_"+params.win+"_croplink").addClassName("disabled");
		}

	} else if (parseInt(params.viewtype) == 1) { // FILE_TYPE_WEBIMAGE

		if ((params.view != "YGSOURCE") && ($("wid_"+params.win+"_ddversion_ddlist")) && (params.version != $("wid_"+params.win+"_ddversion_ddlist").down().down().readAttribute("value"))) {
			if ($("wid_"+params.win+"_croplink")) {
				$("wid_"+params.win+"_croplink").addClassName("disabled");
			}
		}

		previewurl = $K.appdir + "image/"+params.id;
		if ((params.view != "YGSOURCE") && (params.crop != true)) previewurl += "/"+params.view;
		if (params.version) previewurl += "/?version="+params.version+"&rnd="+Math.random()*10000000;

		if (params.fullscreen) {
			$('filepreviewwrap').innerHTML = "";
			$('filepreviewwrap').appendChild(tmpimg=document.createElement('img'));
		} else {
			$('wid_'+params.win+'_innercontent').down().innerHTML = "";
			$('wid_'+params.win+'_innercontent').down().addClassName("filepreviewcontainer");
			$('wid_'+params.win+'_innercontent').down().appendChild(tmpimg=document.createElement('img'));
		}

		tmpimg.src = previewurl;
		tmpimg.writeAttribute("zoomlevel",params.zoom);
		tmpimg.writeAttribute("fullscreen",params.fullscreen);
		tmpimg.writeAttribute("win",params.win);

		if (params.crop == undefined) params.crop = false;

		tmpimg.writeAttribute("crop",params.crop);

		tmpimg.onload = function() {
			$K.yg_filePreviewLoaded(this, this.readAttribute('win'), this.readAttribute('zoomlevel'), this.readAttribute('fullscreen') );
			if (this.readAttribute('crop')) $K.yg_cropPreview(this.readAttribute('win'),true);
		}
		tmpimg.hide();
		tmpimg.addClassName("mk_verticalcenter");
	}

	$K.windows['wid_'+params.win].refresh();
}


/**
 * Loading finished: Show+center image, hide load indicator
 * @param { Object } [obj] object
 * @param { Object } [win] window id
 * @param { Object } [zoom] zoomlevel
 * @param { Boolean } [fullscreen] fullscreen mode
 */
$K.yg_filePreviewLoaded = function(obj, win, zoom, fullscreen) {

	var tmpImage = new Image();
	tmpImage.src = obj.src;
	obj.writeAttribute("origwidth", tmpImage.width);
	obj.writeAttribute("origheight", tmpImage.height);

	dim = {
		width: Math.round(tmpImage.width*zoom/100),
		height: Math.round(tmpImage.height*zoom/100)
	};

	obj.setStyle({width:dim.width+'px'});
	obj.setStyle({height:dim.height+'px'});

	delete tmpImage;

	obj.show();
	if (fullscreen) {
		$('filepreview').removeClassName('tab_loading');
		$K.yg_centerPreview();
		$('filepreviewcontainer').writeAttribute('yg_draggable','true');
		$('filepreviewwrap').removeClassName('nopreview');
		$('filepreviewcontainer').removeClassName('nodrag');
	} else {
		if ($('wid_'+win+'_innercontent')) $('wid_'+win+'_innercontent').removeClassName('tab_loading');
		$K.windows['wid_'+win].refresh();
	}
	if ($('wid_'+win+'_ddzoom')) { $('wid_'+win+'_ddzoom').down(0).next('input').value = zoom }
}


/**
 * Open and init filepreview window
 */
$K.yg_initDlgFilePreview = function( winID, filename, fileid, filecolor, filetypecode, filewidth, fileheight, fileview, fileversion, crop ) {
	$K.yg_showHelp('');
	viewportdims = document.viewport.getDimensions();
	if (filewidth != 0) {
		filewidth += 50;
		fileheight += 150;
		if (filewidth > (viewportdims.width-200)) filewidth = viewportdims.width-200;
		if (filewidth < $K.windows['wid_'+winID].minwidth) filewidth = $K.windows['wid_'+winID].minwidth;
		if (fileheight < $K.windows['wid_'+winID].minheight) fileheight = $K.windows['wid_'+winID].minheight;
		if (fileheight > (viewportdims.height-160)) fileheight = viewportdims.height-160;
		$K.windows['wid_'+winID].boxwidth = filewidth;
		$K.windows['wid_'+winID].boxheight = fileheight;
		$K.windows['wid_'+winID].init();
	}
	if ($('wid_'+winID).descendantOf($('previewcontainer')) == false) $K.yg_centerWindow($('wid_'+winID));
	$('wid_'+winID).show();

	$K.windows['wid_'+winID].cancel = function() {
		$K.yg_cropPreview(winID);
	}
	//$K.windows['wid_'+winID] = new $K.yg_wndobj( 'wid_'+winID, filewidth, fileheight, 'notabs', 'dialog', '', 'file', 0, okfunction, false, cancelfunction );
	$K.windows['wid_'+winID].yg_id = fileid+"-file";
	$K.windows['wid_'+winID].setCaption(filename, 'file');
	$K.windows['wid_'+winID].setFileType('<span class="filetype '+filecolor+'" yg_type="file" yg_id="'+fileid+'-file" yg_property="type">'+filetypecode+'</span>');

}


/**
 * Close preview-mode
 */
$K.yg_closePreview = function() {
	if ($K.cookiedomain != '') {
		top.document.cookie = 'version=; domain=' + $K.cookiedomain + '; path=' + $K.webroot + '; expires=Thu, 01-Jan-1970 00:00:01 GMT';
	} else {
		top.document.cookie = 'version=; path=' + $K.webroot + '; expires=Thu, 01-Jan-1970 00:00:01 GMT';
	}

	$('previewcontainer').setStyle({display:'none'});
	$('previewcontainer').removeClassName("page");

	var wins = $('previewcontainer').immediateDescendants();
	wins.each(function(item) {
		if ($(item).hasClassName('ywindow')) {
			if ($K.windows[item.id]) $K.windows[item.id].remove();
		}
	});

	if ($('filepreview').getStyle("display") == "block") {
		$('filepreview').setStyle({display:'none'});
		if ($('yg_filepreviewimg')) $('yg_filepreviewimg').remove();
	}
	if ($('previewiframe').getStyle("display") == "block") {
		$('previewiframe').src="";
		$('previewiframe').setStyle({display:'none'});
	}

}


/**
 * Centers preview image
 */
$K.yg_centerPreview = function() {
	if ($('previewcontainer') && ($('previewcontainer').getStyle('display') == 'block')) {
		obj = $('filepreviewcontainer');
		objdims = obj.getDimensions();
		viewportdims = document.viewport.getDimensions();
		if ((objdims.width > viewportdims.width) || (objdims.height > viewportdims.height)) {
			//scaler
		}
		leftpos = Math.round((viewportdims.width - objdims.width) / 2);
		toppos = Math.round((viewportdims.height - objdims.height) / 2);
		obj.setStyle({left:leftpos+'px'});
		obj.setStyle({top:toppos+'px'});
	}
}

$K.yg_cropInstance = false;
$K.yg_cropCoords = new Array();
$K.yg_cropZoom = 100;


/**
 * Enters crop mode
 */
$K.yg_cropPreview = function(win,cropping,zooming) {

	if ($K.yg_cropInstance == false) {

		$('wid_'+win).removeClassName('mk_filepreviewer');
		$('wid_'+win).addClassName('mk_filecropper');

		$('croppreviewarea').hide();

		obj = $('filepreviewwrap').down();

		viewdetails = $('wid_'+win+'_ddview').down(0).next('input').value.split('#!#!#');

		viewwidth = $K.yg_cropCoords["width"] = viewdetails[2];
		constrainwidth = $K.yg_cropCoords["constrainwidth"] = viewdetails[3];
		viewheight = $K.yg_cropCoords["height"] = viewdetails[4];
		constrainheight = $K.yg_cropCoords["constrainheight"] = viewdetails[5];

		if (($('wid_'+win+'_ddview').down(0).next('input').value.split('#!#!#')[0] != "YGSOURCE") && (cropping != true)) {
			$K.yg_renderFilePreview({
				id: $K.windows['wid_'+win].yg_id.split('-')[0],
				view: $('wid_'+win+'_ddview').down(0).next('input').value.split('#!#!#')[0],
				version: $('wid_'+win+'_ddversion').down(0).next('input').value,
				viewtype: $('wid_'+win+'_ddview').down(0).next('input').value.split('#!#!#')[1],
				zoom: $('wid_'+win+'_ddzoom').down(0).next('input').value,
				win: win,
				fullscreen: true,
				crop: true,
				viewwidth: viewwidth,
				constrainwidth: constrainwidth,
				viewheight: viewheight,
				constrainheight: constrainheight
			});
			return;
		}

		minwidth = 10;
		minheight = 10;

		function onEndCrop( coords ) {
			$K.yg_cropCoords["x1"] = coords.x1;
			$K.yg_cropCoords["y1"] = coords.y1;
			$K.yg_cropCoords["x2"] = coords.x2;
			$K.yg_cropCoords["y2"] = coords.y2;
			$K.yg_renderCropCoords();
		}

		ratiodimarr = new Array();
		maxratiodimarr = new Array();
		loadcoordsarr = false;

		cropparams = new Array();
		cropparams.displayOnInit = true;
		cropparams.onEndCrop = onEndCrop;
		cropparams.minWidth = minwidth;
		cropparams.minHeight = minheight;
		previewwidth = previewheight = 0;

		if (zooming > 0) {
			cropparams.onloadCoords = new Array();
			cropparams.onloadCoords.x1 = parseInt($K.yg_cropCoords["x1"])/(parseInt($K.yg_cropZoom)/100)*zooming/100;
			cropparams.onloadCoords.y1 = parseInt($K.yg_cropCoords["y1"])/(parseInt($K.yg_cropZoom)/100)*zooming/100;
			cropparams.onloadCoords.x2 = parseInt($K.yg_cropCoords["x2"])/(parseInt($K.yg_cropZoom)/100)*zooming/100;
			cropparams.onloadCoords.y2 = parseInt($K.yg_cropCoords["y2"])/(parseInt($K.yg_cropZoom)/100)*zooming/100;
			$K.windows["wid_"+win].boxheight = $K.windows["wid_"+win].boxheight;
		}
		if ((constrainwidth == 1) && (constrainheight == 1)) {
			// fixed width & height
			cropparams.ratioDim = new Array();
			cropparams.ratioDim.x = parseInt(viewwidth);
			cropparams.ratioDim.y = parseInt(viewheight);

			previewwidth = viewwidth;
			previewheight = viewheight;
			if ((previewwidth > 150) || (previewheight > 150)) {
				if (previewwidth > previewheight) {
					previewwidth = 150;
					previewheight = previewheight * 150 / previewwidth;
				} else {
					previewheight = 150;
					previewwidth = previewwidth * 150 / previewheight;
				}
			}
			cropparams.previewWidth = previewwidth;
			cropparams.previewHeight = previewheight;
			cropparams.previewWrap = 'previewArea';
			$('croppreviewarea').show();

		} else if (((constrainwidth == 0) && (constrainheight == 1)) || ((constrainwidth == 1) && (constrainheight == 0))) {
			// fixed width or fixed height
			cropparams.maxRatioDim = new Array();
			cropparams.maxRatioDim.x = parseInt(viewwidth);
			cropparams.maxRatioDim.y = parseInt(viewheight);

		} else {
			// flex
			if (!(zooming)) {

				zoom = $('wid_'+win+'_ddzoom').down(0).next('input').value;
				conwidth = viewwidth*(zoom/100) - 60;
				conheight = viewheight*(zoom/100) - 60;
				viewportdims = document.viewport.getDimensions();

				if (conwidth >= (viewportdims.width - 60)) conwidth = viewportdims.width - 60;
				if (conheight >= (viewportdims.height - 60)) conheight = viewportdims.height - 60;

				if (conwidth <= minwidth) conwidth = minwidth;
				if (conheight <= minheight) conheight = minheight;
				if (conwidth > (viewwidth*(zoom/100) - 60)) conwidth = viewwidth*(zoom/100);
				if (conheight > (viewheight*(zoom/100) - 60)) conheight = viewheight*(zoom/100);

				cropparams.onloadCoords = new Array();
				cropparams.onloadCoords.x1 = ((viewwidth*(zoom/100) - conwidth) / 2);
				cropparams.onloadCoords.y1 = ((viewheight*(zoom/100) - conheight) / 2);
				cropparams.onloadCoords.x2 = ((viewwidth*(zoom/100) - conwidth) / 2) + conwidth;
				cropparams.onloadCoords.y2 = ((viewheight*(zoom/100) - conheight) / 2) + conheight;
			}
		}

		if ((!(zooming)) && (previewheight > 0)) {

			$K.windows["wid_"+win].boxheight = parseInt($K.windows["wid_"+win].minheight) + parseInt(previewheight);
			$K.windows["wid_"+win].init();
		}

		$K.yg_cropInstance = new Cropper.ImgWithPreview(obj, cropparams);
		$K.yg_renderCropCoords();

	} else {

		$('wid_'+win).addClassName('mk_filepreviewer');
		$('wid_'+win).removeClassName('mk_filecropper');
		$K.windows["wid_"+win].boxheight = $K.windows["wid_"+win].minheight;
		$K.windows["wid_"+win].init();
		$K.yg_cropInstance.remove();
		$K.yg_cropInstance = false;
		if (($('wid_'+win+'_ddview').down(0).next('input').value.split('#!#!#')[0] != "YGSOURCE") && (!(zooming))) {
			$K.yg_renderFilePreview({
				id: $K.windows['wid_'+win].yg_id.split('-')[0],
				view: $('wid_'+win+'_ddview').down(0).next('input').value.split('#!#!#')[0],
				version: $('wid_'+win+'_ddversion').down(0).next('input').value,
				viewtype: $('wid_'+win+'_ddview').down(0).next('input').value.split('#!#!#')[1],
				zoom: $('wid_'+win+'_ddzoom').down(0).next('input').value,
				win: win,
				fullscreen: true,
				crop: false
			});
		}
	}
}


/**
 * Submits crop
 */
$K.yg_submitCrop = function(winID) {

	$('filepreview').addClassName('tab_loading');
	$('filepreviewcontainer').writeAttribute('yg_draggable','false');
	$('filepreviewcontainer').addClassName('nodrag');

	$K.yg_cropPreview(winID);
	$('filepreviewwrap').innerHTML = "";
	var data = Array ( 'noevent', { params: {
		objecttype: 'file',
		win:  winID,
		id: $K.windows['wid_'+winID].yg_id.split('-')[0],
		view: $('wid_'+winID+'_ddview').down(0).next('input').value.split('#!#!#')[0],
		version: $('wid_'+winID+'_ddversion').down(0).next('input').value,
		zoom: $('wid_'+winID+'_ddzoom').down(0).next('input').value,
		x1: $K.yg_cropCoords.x1,
		y1: $K.yg_cropCoords.y1,
		x2: $K.yg_cropCoords.x2,
		y2: $K.yg_cropCoords.y2
	} } );
	$K.yg_AjaxCallback( data, 'cropFile' );
}


/**
 * Renders crop coordinates
 */
$K.yg_renderCropCoords = function( ) {
	if ($("yg_cropdim_dim")) {

		zoom = $($("yg_cropdim_dim").up('.ywindow').id+'_ddzoom').down(0).next('input').value;

		width = Math.round((parseInt($K.yg_cropCoords["x2"]) - parseInt($K.yg_cropCoords["x1"])) / zoom * 100);
		height = Math.round((parseInt($K.yg_cropCoords["y2"]) - parseInt($K.yg_cropCoords["y1"])) / zoom * 100);

		if (($K.yg_cropCoords["constrainwidth"] == "1") && ($K.yg_cropCoords["constrainheight"] == "0")) {
			height = Math.round(parseInt($K.yg_cropCoords["width"])/width * height);
			width = Math.round(parseInt($K.yg_cropCoords["width"]));
		} else if (($K.yg_cropCoords["constrainwidth"] == "0") && ($K.yg_cropCoords["constrainheight"] == "1")) {
			width = Math.round(parseInt($K.yg_cropCoords["height"])/height * width);
			height = Math.round(parseInt($K.yg_cropCoords["height"]));
		} else if (($K.yg_cropCoords["constrainwidth"] == "1") && ($K.yg_cropCoords["constrainheight"] == "1")) {
			width = Math.round(parseInt($K.yg_cropCoords["width"]));
			height = Math.round(parseInt($K.yg_cropCoords["height"]));
		}

		$("yg_cropdim_dim").innerHTML = width + "x" + height;
		$("yg_cropdim_xy1").innerHTML = Math.round(parseInt($K.yg_cropCoords["x1"])/zoom*100) + ":" +  Math.round(parseInt($K.yg_cropCoords["y1"])/zoom*100);
		$("yg_cropdim_xy2").innerHTML = Math.round(parseInt($K.yg_cropCoords["x2"])/zoom*100) + ":" +  Math.round(parseInt($K.yg_cropCoords["y2"])/zoom*100);

	}
}
