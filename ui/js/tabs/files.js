/**

 - thumbview / scale
 - ddclick

 * @fileoverview Provides functionality for managing files
 * @version 1.0
 */


$K.yg_fileSliders = new Array();

/**
 * Inits the reupload button in the file-details window
 * @param { String } [wndid] Id of parent window
 */
$K.yg_initReUploadButton = function(winID, uploadTitle, uploadType, fileID) {

	var readOnly = true;
	if ($('wid_' + winID + '_reuploadpanel')) {
		readOnly = false;
	}

	if (readOnly) {
		if ($('wid_' + winID + '_buttons') && $('wid_' + winID + '_buttons').down('.moveto')) {
			$('wid_' + winID + '_buttons').down('.moveto').addClassName('disabled');
		}
		return;
	}

	$K.yg_loadDlgUpload();

	if ($K.uploadFramework == 'swfuploader') {
		/* Update swfupload settings */
		var swfuploadSettings = Object.clone($K.yg_SWFUploadSettings);
		Object.extend(swfuploadSettings, {
			upload_url: $K.appdir + 'responder?handler=reUploadFile',
			flash_url: $K.jsdir + '3rd/swfupload/swfupload.swf',
			button_width: "2000",
			button_height: "56",
			button_placeholder_id: 'wid_' + winID + '_reuploadpanel',
			custom_settings: {
				uploadButtonId: 'wid_' + winID + '_okbutton',
				uploadTitle: uploadTitle,
				uploadType: uploadType,
				fileID: fileID,
				autoUpload: true
			},
			button_action: SWFUpload.BUTTON_ACTION.SELECT_FILE
		});
		$K.yg_SWFUploadObjects['wid_' + winID] = new SWFUpload(swfuploadSettings);
	}

	if ($K.uploadFramework == 'plupload') {
		/* Init Upload */
		var customSettings = {
			uploadButtonId: 'wid_' + winID + '_okbutton',
			uploadTitle: uploadTitle,
			uploadType: uploadType,
			fileID: fileID,
			autoUpload: true
		};
		$K.yg_UploadInit('wid_' + winID, 'wid_' + winID + '_reuploadpanel', null, {handler:'reUploadFile'}, customSettings, [], false, true);
	}
}


/**
 * Inits filemgr window
 * @param { String } [wndid] Id of parent window
 */
$K.yg_initFileMgr = function(wndid) {

	if (!$K.windows[wndid].yg_id) return;

	if (!$('wid_upload')) {
		$K.yg_loadDlgUpload($K.windows[wndid].yg_id);
	}

	var fileID = $K.windows[wndid].yg_id.split('-')[0];

	if ($K.uploadFramework == 'plupload') {
		/* Init Upload */
		var customSettings = {
			uploadButtonId: 'wid_upload_okbutton',
			statusPanelTemplate: 'wid_upload_filestoupload_template',
			uploadTitle: 'wid_upload_title',
			uploadType: 'wid_upload_filetype'
		};

		if (!Prototype.Browser.IE || (BrowserDetect.version != 8)) {
			if ($(wndid + '_column2innercontent')) {
				$K.yg_UploadInit('wid_upload', wndid + '_' + fileID + '_fakePanel', wndid + '_column2innercontent', {handler:'uploadFile'}, customSettings, [], true);
			} else {
				$K.yg_UploadInit('wid_upload', wndid + '_' + fileID + '_fakePanel', wndid + '_ywindowinnerie', {handler:'uploadFile'}, customSettings, [], true);
			}
		}
	}

	var jsTemplateList = $K.windows[wndid].jsTemplateList;
	var jsTemplateThumb = $K.windows[wndid].jsTemplateThumb;

	// Disable multiselect if running as choose dialog
	if ($(wndid).hasClassName('mk_filechooser')) {
		if ($(wndid + '_listcontainer')) $(wndid + '_listcontainer').writeAttribute('yg_multiselect', 'false');
		if ($(wndid + '_thumbcontainer')) $(wndid + '_thumbcontainer').writeAttribute('yg_multiselect', 'false');
	}

	// For initial load (no folderview selected yet)
	$K.windows[wndid].tablecols = true;
	if (!($K.windows[wndid].loadparams.view)) $K.windows[wndid].loadparams.view = 'listview';
	if (!($K.windows[wndid].filelist)) $K.windows[wndid].filelist = 2;
	$K.yg_switchFileView(wndid, $K.windows[wndid].loadparams.view);

	$K.windows[wndid].addFileToFolder = function(fileData) {

		if ($(wndid).hasClassName('filelist1') || $(wndid).hasClassName('filelist2') || $(wndid).hasClassName('filelist3')) {

			// Create template-chunk for listview
			var item_template = $K.yg_makeTemplate(jsTemplateList);

			var thumbnailData = $K.yg_makeThumb(fileData);

			// Fill template with variables
			var newFile = item_template.evaluate({
				item_objectid: fileData.objectid,
				item_thumbnail: thumbnailData,
				item_color: fileData.color,
				item_identifier: fileData.identifier,
				item_name: fileData.name,
				item_tags: fileData.tags,
				item_filesize: $K.yg_filesize(fileData.filesize),
				item_ref_count: fileData.ref_count,
				item_timestamp: fileData.timestamp,
				item_datum: fileData.datum,
				item_uhrzeit: fileData.uhrzeit,
				item_uid: fileData.uid,
				item_username: fileData.username,
				item_filename: fileData.filename,
				item_width: fileData.width,
				item_height: fileData.height,
				win_no: wndid.replace(/wid_/, '')
			});

			if (fileData.targetTable) fileData.targetTable.down('tbody').insert({bottom:newFile});

		} else if ($(wndid).hasClassName('thumbview')) {

			// Create template-chunk for thumbview
			var item_template = $K.yg_makeTemplate(jsTemplateThumb);

			// Build imagedata
			var alignment = '';
			if (fileData.width) {
				var ratioPic = (fileData.width / fileData.height);
				if ((ratioPic > (4 / 3)) || (fileData.thumb != '1')) {
					alignment = 'x-scale';
				} else {
					alignment = 'y-scale';
				}
			}
			var full_filename = '';
			if (fileData.width && fileData.height) {
				full_filename = fileData.width + 'x' + fileData.height + ', ';
			}
			full_filename += $K.yg_filesize(fileData.filesize);


			// Build thumbnaildata
			var thumbnailData = '<div class="thumbcnt ';
			if (fileData.thumb != '1') thumbnailData += 'thumbcnt_nothumb';
			thumbnailData += '">';
			if ((alignment == 'x-scale') || (fileData.thumb != '1')) thumbnailData += '<table cellspacing="0" cellpadding="0"><tr><td>';
			if (fileData.thumb == '1') {
				var randomSuffix = '?rnd=' + parseInt(Math.random() * 10000000);
				thumbnailData += '<img class="noload" real_src="' + $K.appdir + 'image/' + fileData.objectid + '/yg-thumb/' + randomSuffix + '" onload="$K.yg_setThumbPreviewLoaded(this);">';
			} else {
				thumbnailData += '<div class="noimg">?</div>';
			}
			if ((alignment == 'x-scale') || (fileData.thumb != '1')) thumbnailData += '</td></tr></table>';
			thumbnailData += '</div>';

			// Fill template with variables
			var newFile = item_template.evaluate({
				item_objectid: fileData.objectid,
				item_thumbnail: thumbnailData,
				item_color: fileData.color,
				item_identifier: fileData.identifier,
				item_name: fileData.name,
				item_full_filename: full_filename,
				alignment: alignment,
				win_no: wndid.replace(/wid_/, '')
			});

			// Find position to insert the new element (alphabetically)
			var itemsArray = new Array();
			var newItem = fileData.name.toLowerCase() + '<<>>' + 'file_' + wndid.replace(/wid_/, '') + '_' + fileData.objectid;
			fileData.targetContainer.select('li').each(function(item) {
				itemsArray.push(item.down('.filetitle').innerHTML.toLowerCase() + '<<>>' + item.id);
			});
			itemsArray.push(newItem);
			itemsArray.sort();

			var targetPosition = itemsArray.indexOf(newItem);
			targetPosition--;

			if (targetPosition < 0) {
				// Insert at start of list
				if (fileData.targetContainer) fileData.targetContainer.insert({top:newFile});
			} else {
				// Insert after specific element
				var targetId = itemsArray[targetPosition].split('<<>>')[1];
				$(targetId).insert({after:newFile});
			}
		}

		// Update Filecount
		var oldFileCount = parseInt($(wndid + '_objcnt').innerHTML);
		$(wndid + '_objcnt').update(++oldFileCount);

		// Add to lookuptable
		if ($('file_' + wndid.replace(/wid_/, '') + '_' + fileData.objectid)) {
			$K.yg_customAttributeHandler($('file_' + wndid.replace(/wid_/, '') + '_' + fileData.objectid).up());
		}

		// Refresh Sortables
		$K.initSortable(wndid + '_' + $K.windows[wndid].yg_id.replace(/-file/, '') + '_files_list');

		$K.yg_refreshFileMgr(wndid);
	}

	$K.windows[wndid].addTagToFile = function(targetFormfield, sourceYgID) {

		var objectID = targetFormfield.split('_')[2];
		var objectType = 'file';
		var tagId = sourceYgID.split('-')[0];

		var data = Array('noevent', {yg_property:'addObjectTag', params:{
			objectID: objectID,
			objectType: objectType,
			site: 'file',
			tagId: tagId
		} });
		$K.yg_AjaxCallback(data, 'addObjectTag');
		$K.yg_fadeField($(targetFormfield));
	};

}


/**
 * Inits filemgr window
 * @param { String } [wndid] Id of parent window
 * @param { Boolean } [switchmode] True if changing zoomlevel in listview
 */
$K.yg_refreshFileMgr = function(wndid, switchmode) {

	if (($(wndid).hasClassName('filelist1') || $(wndid).hasClassName('filelist2') || $(wndid).hasClassName('filelist3')) && (switchmode != true)) {

		// fileSlider listview
		if (!($K.yg_fileSliders[wndid + '_list']) && ($(wndid + "_slider").getWidth() > 0)) {

			$K.yg_fileSliders[wndid + '_list'] = new Control.Slider(wndid + "_sliderhandle", wndid + "_slider", {axis:"horizontal", minimum:0, maximum:($(wndid + "_slider").getWidth() - $(wndid + "_sliderhandle").getWidth()), alignX:0, increment:2, sliderValue:0.5});

			$K.yg_fileSliders[wndid + '_list'].options.onSlide = function(value) {
				$K.yg_switchFileListView(wndid, value);
			}
			$K.yg_fileSliders[wndid + '_list'].options.onChange = function(value) {
				$K.yg_switchFileListView(wndid, value);
			}
		}

		if (!($K.windows[wndid].loadparams.pagedir_orderdir)) $K.windows[wndid].loadparams.pagedir_orderdir = -1;
		if (!($K.windows[wndid].loadparams.pagedir_orderby)) {
			$K.windows[wndid].loadparams.pagedir_orderby = 'title';
			$(wndid + "_tablehead").down('.fcol_name').addClassName("sortcol");
			$(wndid + "_tablehead").down('.fcol_name').addClassName("sortasc");
		}

		TableKit.unloadTable(wndid + "_tablecontent");
		TableKit.unloadTable(wndid + "_tablehead");
		TableKit.Sortable.init(wndid + "_tablehead");
		TableKit.Sortable.init(wndid + "_tablecontent");
		TableKit.Resizable.init(wndid + "_tablehead");

		TableKit.Sortable.sort(wndid + "_tablecontent", $K.windows[wndid].loadparams.pagedir_orderby, $K.windows[wndid].loadparams.pagedir_orderdir * -1);
		TableKit.Sortable.sort(wndid + "_tablecontent", $K.windows[wndid].loadparams.pagedir_orderby, 1);

		// hotfix, not sorting correctly when sorted desc by title
		if (($K.windows[wndid].loadparams.pagedir_orderby == 'title') && ($K.windows[wndid].loadparams.pagedir_orderdir == 1)) {
			TableKit.Sortable.sort(wndid + "_tablecontent", $K.windows[wndid].loadparams.pagedir_orderby, 1);
		}
	}

	// remove load indicators
	if ($(wndid).hasClassName('ydialog')) {
		$K.yg_customAttributeHandler($(wndid + '_ywindowinner'));
		$K.yg_customAttributeHandler($(wndid + '_column2innercontent'));
		$(wndid + '_column2innercontent').removeClassName('tab_loading');
	} else {
		$K.yg_customAttributeHandler($(wndid + '_ywindowinner'));
		$(wndid + '_ywindowinner').removeClassName('tab_loading');
	}

	// Trigger thumbnail loading
	if ($(wndid).hasClassName('filelist3')) {
		if ($(wndid + '_listcontainer')) {
			$K.yg_loadThumbPreview($(wndid + '_listcontainer'), '.mk_filelist img');
		}
	} else if ($(wndid).hasClassName('thumbview')) {
		$K.yg_loadThumbPreview($(wndid + '_thumbcontainer'), '.mk_filepreview img');
	}

	$K.windows[wndid].refresh();
}


/**
 * Switch between preview and list-view
 * @param { String } [wndid] Id of parent window
 * @param { String } [value] 'listview' or 'thumbview'
 */
$K.yg_switchFileView = function(wndid, value) {

	currentview = $K.windows[wndid].loadparams.view;
	$K.windows[wndid].loadparams.view = value;

	$(wndid + '_fileview').down('input').blur();

	if (value == "thumbview") {
		$(wndid).removeClassName('filelist1');
		$(wndid).removeClassName('filelist2');
		$(wndid).removeClassName('filelist3');
		$(wndid).addClassName('thumbview');
		$K.yg_scaleFileThumbs(wndid, 'false');
	} else {
		// check, restore prev view
		if ($K.windows[wndid].filelist == 1) $K.yg_switchFileListView(wndid, 0.1);
		if ($K.windows[wndid].filelist == 2) $K.yg_switchFileListView(wndid, 0.5);
		if ($K.windows[wndid].filelist == 3) $K.yg_switchFileListView(wndid, 1);
	}

	var id = $K.windows[wndid].yg_id;
	if (!id) {
		id = 0;
	} else {
		id = id.split('-')[0];
	}

	dialog = false;
	if ($(wndid).hasClassName('ydialog') && ($(wndid + '_column2'))) dialog = true;

	if ((dialog) && (id != 0)) {

		var action = '';
		if ($K.windows[wndid].loadparams && $K.windows[wndid].loadparams.action) {
			action = $K.windows[wndid].loadparams.action;
		}

		// Show loading indicator
		$(wndid + '_column2innercontent').addClassName('tab_loading');
		new Ajax.Updater(wndid + '_column2innercontentinner', $K.appdir + 'tab_FOLDERCONTENT', {
			asynchronous: true,
			evalScripts: true,
			method: 'post',
			displaymode: dialog,
			onComplete: function() {
				$K.yg_refreshFileMgr(wndid);
			},
			parameters:{
				seq: wndid.replace(/wid_/, ''),
				wid: wndid,
				yg_id: id + '-file',
				yg_type: 'filefolder',
				view: value,
				action: action,
				us: document.body.id,
				lh: $K.yg_getLastGuiSyncHistoryId()
			}
		});

	} else {
		// reload tab
		if (currentview != $K.windows[wndid].loadparams.view) {
			$K.windows[wndid].tabs.select($K.windows[wndid].tabs.selected, {refresh:1});
		}
		$K.yg_customAttributeHandler($(wndid + '_ywindowinner'));
		$(wndid + '_ywindowinner').removeClassName('tab_loading');
		$K.yg_refreshFileMgr(wndid);
	}

}


/**
 * Switch between three different listviews
 * @param { String } [wndid] Id of parent window
 * @param { Int } [value] slider value (0-1)
 */
$K.yg_switchFileListView = function(wndid, value) {

	$(wndid).removeClassName('thumbview');

	if ($(wndid + '_column2')) {
		innerwidth = $(wndid + '_column2innercontent').getWidth();
	} else {
		innerwidth = $(wndid + '_ywindowinner').getWidth();
	}

	var refreshvar = false;

	var slider = $(wndid + "_sliderhandle");
	if (slider) {

		$(wndid + "_tablecols").show();

		if (Math.round(value * 100) < 33) {

			if (($K.windows[wndid].filelist != 1) || (!($(wndid).hasClassName('filelist1')))) {

				$(wndid).removeClassName("filelist3");
				$(wndid).removeClassName("filelist2");
				$(wndid).addClassName("filelist1");
				$(wndid + "_colhead_tags_2nd").hide();
				$(wndid + "_colhead_tags").hide();
				$(wndid + "_colhead_inuse").show();
				$(wndid + "_colhead_inuse_2nd").hide();
				$(wndid + "_colhead_filename").hide();
				$(wndid + "_colhead_filesize").hide();
				$(wndid + "_colhead_preview").hide();
				$(wndid + "_colhead_author").hide();

				namewidth = Math.floor((innerwidth - 38 - 54 - 21) * 0.57);
				lastchangewidth = innerwidth - 38 - 54 - 21 - namewidth - 4;

				$K.yg_changeCSS('#' + wndid + ' .fcol_type', 'width', '34px', true);
				$K.yg_changeCSS('#' + wndid + ' .fcol_name', 'width', namewidth + 'px', true);
				$K.yg_changeCSS('#' + wndid + ' .fcol_size', 'width', '50px', true);
				$K.yg_changeCSS('#' + wndid + ' .fcol_lastchange', 'width', lastchangewidth + 'px', true);

				refreshvar = true;
				$K.windows[wndid].filelist = 1;
			}
			slider.setStyle({left:'0px'});
		} else if (Math.round(value * 100) < 66) {
			if (($K.windows[wndid].filelist != 2) || (!($(wndid).hasClassName('filelist2')))) {

				$(wndid).addClassName("filelist2");
				$(wndid).removeClassName("filelist1");
				$(wndid).removeClassName("filelist3");

				$(wndid + "_colhead_tags_2nd").hide();
				$(wndid + "_colhead_tags").show();
				$(wndid + "_colhead_inuse").hide();
				$(wndid + "_colhead_inuse_2nd").show();
				$(wndid + "_colhead_filename").show();
				$(wndid + "_colhead_filesize").show();
				$(wndid + "_colhead_preview").hide();
				$(wndid + "_colhead_author").show();

				namewidth = Math.floor((innerwidth - 38 - 54 - 25 - 136) * 0.5);
				filenamewidth = innerwidth - 38 - 54 - 25 - 136 - namewidth - 4;

				$K.yg_changeCSS('#' + wndid + ' .fcol_type', 'width', '34px', true);
				$K.yg_changeCSS('#' + wndid + ' .fcol_name', 'width', namewidth + 'px', true);
				$K.yg_changeCSS('#' + wndid + ' .fcol_size', 'width', '50px', true);
				$K.yg_changeCSS('#' + wndid + ' .fcol_lastchange', 'width', '132px', true);
				$K.yg_changeCSS('#' + wndid + ' .fcol_filename', 'width', filenamewidth + 'px', true);

				refreshvar = true;
				$K.windows[wndid].filelist = 2;
			}
			slider.setStyle({left:'74px'});
		} else if (Math.round(value * 100) >= 66) {
			if (($K.windows[wndid].filelist != 3) || (!$(wndid).hasClassName('filelist3'))) {

				$(wndid).addClassName("filelist3");
				$(wndid).removeClassName("filelist1");
				$(wndid).removeClassName("filelist2");

				$(wndid + "_colhead_tags_2nd").show();
				$(wndid + "_colhead_tags").hide();
				$(wndid + "_colhead_inuse").hide();
				$(wndid + "_colhead_inuse_2nd").show();
				$(wndid + "_colhead_filename").show();
				$(wndid + "_colhead_filesize").show();
				$(wndid + "_colhead_preview").show();
				$(wndid + "_colhead_author").show();

				namewidth = Math.floor(innerwidth - 106 - 50 - 21 - 132) * 0.5;
				filenamewidth = innerwidth - 130 - 50 - 21 - 132 - namewidth;

				$K.yg_changeCSS('#' + wndid + ' .fcol_type', 'width', '106px', true);
				$K.yg_changeCSS('#' + wndid + ' .fcol_name', 'width', namewidth + 'px', true);
				$K.yg_changeCSS('#' + wndid + ' .fcol_size', 'width', '50px', true);
				$K.yg_changeCSS('#' + wndid + ' .fcol_lastchange', 'width', '132px', true);
				$K.yg_changeCSS('#' + wndid + ' .fcol_filename', 'width', filenamewidth + 'px', true);

				if ($(wndid).hasClassName('ydialog')) {
					var thumbContainer = $(wndid + '_column2innercontentinner');
				} else {
					var thumbContainer = $(wndid + '_FOLDERCONTENT');
				}

				$K.yg_loadThumbPreview(thumbContainer, '.mk_filelist img');

				refreshvar = true;
				$K.windows[wndid].filelist = 3;
			}
			slider.setStyle({left:'147px'});
		}

	}

	if (refreshvar) $K.yg_refreshFileMgr(wndid, true);
}


/**
 * Creates thumbnail html
 * @param { Object } [fileData] file object including height, width, objectid, thumb
 * @param { String } [targetFolder] Id of folder to upload into
 */
$K.yg_makeThumb = function(fileData) {
	// Build imagedata
	var alignment = '';
	if (fileData.height) {
		var ratioPic = (fileData.width / fileData.height);
		if ((ratioPic > (4 / 3)) || (fileData.thumb != '1')) {
			alignment = 'vimg';
		}
	}
	if (Prototype.Browser.IE) {
		alignment += ' loaded';
	}

	// Build thumbnaildata
	if (fileData.thumb != 1) alignment += ' nothumb';
	var thumbnailData = '<div class="' + alignment + '">';
	if (fileData.thumb == 1) {
		var randomSuffix = '?rnd=' + parseInt(Math.random() * 10000000);
		thumbnailData += '<img class="' + fileData.classname + '" real_src="' + $K.appdir + 'image/' + fileData.objectid + '/yg-list/' + randomSuffix + '" onload="$K.yg_setFileListPreviewLoaded(this);">';
	} else {
		thumbnailData += '<div class="noimg">?</div>';
	}
	thumbnailData += '</div>';
	return thumbnailData;
}


/**
 * Opens add file upload
 * @param { String } [which] Reference to opener
 * @param { String } [targetFolder] Id of folder to upload into
 */
$K.yg_openFileChooserWindow = function(which, targetFolder, objectid, formfield) {
	which = $(which);

	var targetElement;
	var openerRef;
	if (which.up('.maskedit') && which.up('.maskedit').down('.title_txt')) {
		targetElement = which.up('.maskedit').down('.title_txt');
		openerRef = which.up('.maskedit');
	} else {
		targetElement = openerRef = which;
	}

	new $K.yg_wndobj({ config:'FILE_SELECT', loadparams:{ yg_id:targetFolder, element:targetElement.identify(), formfield:formfield, opener_reference:openerRef.identify() } });
}


/**
 * Change the type of the file
 * @param { String } [value] Value of the entry chosen
 * @param { String } [wndid] Id of parent window
 * @param { Element } [which] Reference to the input field.
 */
$K.yg_changeFileType = function(value, wndid, which) {
	var file = $K.windows['wid_' + wndid].yg_id.split('-')[0];
	var data = Array('noevent', {yg_property:'changeFileType', params:{
		file:file,
		wid:wndid,
		type:value
	} });
	$K.yg_AjaxCallback(data, 'changeFileType');
}


/**
 * Selects a file (in list or thumbview) and updates the "yg_id" property
 * of the current window.
 * @param { Element } [which] The element which was clicked
 */
$K.yg_selectFile = function(which) {
	which = $(which);
	var winRef = which.up('.ywindow');
	var openerReference = $($K.windows[winRef.id].openerReference);

	$K.yg_hideColumn2Bottom(winRef.id);
	if ($(winRef.id + '_viewselect')) $(winRef.id + '_viewselect').down().next('input').value = "";

	// check if opened from contenteditor and if selected
	if ($K.windows[winRef.id].loadparams['opener_reference'] &&
		($K.windows[winRef.id].loadparams['type'] == 'image') &&
		(which.hasClassName('cntblock') || which.hasClassName('cntblockcontainer'))
		) {
		params = {};
		params.id = which.readAttribute("yg_id").split("-")[0];
		params.win = winRef.id.split('_')[1];
		var data = Array('noevent', { params:params });
		$K.yg_AjaxCallback(data, 'refreshFileViewDetails');
	}
}


/**
 * Updates file view dropdown in file chooser
 * @param { Array } [views] JSON array
 * @param { Int } [win] window no.
 */
$K.yg_updateFileViewDetails = function(views, win) {

	if ((!($("wid_" + win))) || (!($("wid_" + win + "_viewselect")))) return;

	views = views.evalJSON();

	// clear dropdown views
	$("wid_" + win + "_viewselect_ddlist").down().innerHTML = "";

	var count = 0;
	for (var i = 0; i < views.length; i++) {
		if (views[i]["VIEWTYPE"] == 1) { // FILE_TYPE_WEBIMAGE

			title = views[i]["NAME"];
			value = views[i]["IDENTIFIER"];
			if (views[i]["IDENTIFIER"] == "YGSOURCE") {
				title = $K.TXT('TXT_SOURCEFILE');
				value = "";
			}
			title += ": ";
			if ((views[i]["WIDTH"] != 0) && (views[i]["WIDTH"] != null)) {
				title += views[i]["WIDTH"]
			} else {
				title += "[-]";
			}
			title += " x ";
			if ((views[i]["HEIGHT"] != 0) && (views[i]["HEIGHT"] != null)) {
				title += views[i]["HEIGHT"]
			} else {
				title += "[-]";
			}

			if (count == 0) {
				selected = true;
			} else {
				selected = false;
			}
			$K.yg_dropdownInsert($("wid_" + win + "_viewselect"), title, value, selected);
			count++;
		}
	}
	if (count > 0) {
		$K.yg_initDropdown($("wid_" + win + "_viewselect"));
		$K.yg_showColumn2Bottom("wid_" + win);
	}
}


/**
 * Sets the class of the container div, so that no indicator is displayed anymore.
 * @param { Element } [which] Reference to image
 */
$K.yg_setFileListPreviewLoaded = function(which) {
	which = $(which);
	if ((which.dummy_loaded) || (!which.src.endsWith('x.gif'))) {
		setTimeout(function() {
			which.up('div').addClassName('loaded');
		}, 0);
	} else {
		which.dummy_loaded = true;
	}
}


/**
 * Sets the class of the container div, so that no indicator is displayed anymore.
 * @param { Element } [which] Reference to image
 */
$K.yg_setThumbPreviewLoaded = function(which) {
	which = $(which);
	if ((which.dummy_loaded) || (!which.src.endsWith('x.gif'))) {
		setTimeout(function() {
			if (which.up('div')) which.up('div').addClassName('thumbcnt_loaded');
		}, 0);
	} else {
		which.dummy_loaded = true;
	}
}


/**
 * Load the thumbnails
 * @param { Element } [thumbContainer] Container of the thumbnails
 */
$K.yg_loadThumbPreview = function(thumbContainer, classFilter) {
	thumbContainer = $(thumbContainer);

	if (!classFilter) classFilter = 'img';

	if (thumbContainer) {
		thumbContainer.select(classFilter).each(function(listitem) {
			if ( listitem.readAttribute('real_src') &&
				 !listitem.hasClassName('noload') ) {
				listitem.src = listitem.readAttribute('real_src');
				listitem.removeAttribute('real_src');
			}
		});
	}
}


/**
 * Scale preview thumbnails seamless
 * @param { String } [wndid] Id of parent window
 * @param { Int } [value] slider value (0-1), if 'false' current setting will be used
 */
$K.yg_scaleFileThumbs = function(wndid, value) {

	if (!($K.yg_fileSliders[wndid + '_preview'])) {
		$K.yg_fileSliders[wndid + '_preview'] = new Control.Slider(wndid + "_sliderhandlepreview", wndid + "_sliderpreview", {axis:"horizontal", minimum:0, maximum:($(wndid + "_sliderpreview").getWidth() - $(wndid + "_sliderhandlepreview").getWidth()), alignX:0, increment:2, sliderValue:0.5});
		$K.yg_fileSliders[wndid + '_preview'].options.onSlide = function(value) {
			$K.yg_scaleFileThumbs(wndid, value);
		}
		$K.yg_fileSliders[wndid + '_preview'].options.onChange = function(value) {
			$K.yg_scaleFileThumbs(wndid, value);
		}
	}

	if ($(wndid + '_column2')) {
		dialog = true;
		var scaleGroup = $(wndid + "_column2innercontentinner");
	} else {
		dialog = false;
		var scaleGroup = $(wndid + "_container");
	}

	if (value == 'false') {
		if (scaleGroup.className.indexOf('thumbscale') != -1) {
			value = parseInt(scaleGroup.className.substring(scaleGroup.className.indexOf('thumbscale') + 10, scaleGroup.className.indexOf('thumbscale') + 13));
		} else {
			value = 50;
		}
		$K.yg_fileSliders[wndid + '_preview'].setValue(parseInt(value) / 100);
	} else {
		percent = Math.round(value * 100);
		if (scaleGroup.className.indexOf('thumbscale') != -1) scaleGroup.className = scaleGroup.className.substring(0, scaleGroup.className.indexOf('thumbscale') - 1);
		scaleGroup.addClassName('thumbscale' + percent);
	}
	if (dialog) {
		$K.windows[wndid].refresh("col2");
	} else {
		$K.windows[wndid].refresh("col1");
	}
}


/**
 * Add/substract per click scale/view per click
 * @param { String } [wndid] Id of parent window
 * @param { Int } [value] value to add/substract
 */
$K.yg_fileSlideClick = function(wndid, value) {
	if ($(wndid).hasClassName('filelist1')) {
		if (value > 0) {
			$K.yg_switchFileListView(wndid, 0.5);
		}
	} else if ($(wndid).hasClassName('filelist2')) {
		if (value > 0) {
			$K.yg_switchFileListView(wndid, 0.7);
		} else {
			$K.yg_switchFileListView(wndid, 0);
		}
	} else if ($(wndid).hasClassName('filelist3')) {
		if (value < 0) {
			$K.yg_switchFileListView(wndid, 0.5);
		}
	} else {
		var scaleGroup = $(wndid + "_container");
		var currentvalue = parseInt(scaleGroup.className.replace("container thumbscale", "")) / 100;
		if (isNaN(currentvalue)) currentvalue = 0.5;

		if (value > 0) {
			currentvalue += 0.1;
		} else {
			currentvalue -= 0.1;
		}
		if (currentvalue > 1) currentvalue = 1;
		if (currentvalue < 0) currentvalue = 0;

		$K.yg_fileSliders[wndid + '_preview'].setValue(currentvalue);
	}

}


/**
 * Add child node to the currently selected node
 * @param { Element } [fileref] The element from which the function was called.
 * @function
 * @name $K.yg_addChildFolder
 */
$K.yg_addChildFolder = function(fileref) {

	// Topbar buttons or actionbuttons?
	if (fileref.hasClassName('tree_btn')) {
		if (fileref.hasClassName('disabled')) return;
		var file = $K.windows[fileref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt(fileref.up('.ywindow').id.replace(/wid_/g, ''));
		var nodeid = fileref.id;
		var file = nlsTree['files_tree' + wid + '_tree'].nLst[nodeid].yg_id;
	}

	file = file.split('-')[0];

	var data = Array('noevent', {yg_property:'addFileChildFolder', params:{
		file:file
	} });
	$K.yg_AjaxCallback(data, 'addFileChildFolder');

}


/**
 * Move the currently selected folder up
 * @param { Element } [fileref] The element from which the function was called.
 * @function
 * @name $K.yg_moveUpFolder
 */
$K.yg_moveUpFolder = function(fileref) {

	// Topbar buttons or actionbuttons?
	if (fileref.hasClassName('tree_btn')) {
		if (fileref.hasClassName('disabled')) return;
		var file = $K.windows[fileref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt(fileref.up('.ywindow').id.replace(/wid_/g, ''));
		var nodeid = fileref.id;
		var file = nlsTree['files_tree' + wid + '_tree'].nLst[nodeid].yg_id;
	}
	var site = file.split('-')[1];
	file = file.split('-')[0];

	var data = Array('noevent', {yg_property:'moveUpFolder', params:{
		file:file,
		site:site
	} });

	$K.yg_AjaxCallback(data, 'moveUpFolder');

}


/**
 * Move the currently selected folder down
 * @param { Element } [fileref] The element from which the function was called.
 * @function
 * @name $K.yg_moveDownFolder
 */
$K.yg_moveDownFolder = function(fileref) {

	// Topbar buttons or actionbuttons?
	if (fileref.hasClassName('tree_btn')) {
		if (fileref.hasClassName('disabled')) return;
		var file = $K.windows[fileref.up('.ywindow').id].yg_id;
	} else {
		var wid = parseInt(fileref.up('.ywindow').id.replace(/wid_/g, ''));
		var nodeid = fileref.id;
		var file = nlsTree['files_tree' + wid + '_tree'].nLst[nodeid].yg_id;
	}

	var site = file.split('-')[1];
	file = file.split('-')[0];

	var data = Array('noevent', {yg_property:'moveDownFolder', params:{
		file:file,
		site:site
	} });
	$K.yg_AjaxCallback(data, 'moveDownFolder');

}


/**
 * Refresh a file with new tags
 * @param { Element } [which] The element to update
 * @param { Array } [tags] The array of tags
 * @function
 * @name $K.yg_refreshFileTags
 */
$K.yg_refreshFileTags = function(which, tags) {
	which = $(which);

	if (which && which.down('.mk_tags')) {
		var tagContainer = which.down('.mk_tags');
		var output = '';

		tags = tags.evalJSON();

		if (tags.length > 0) {
			output = '<div class="related_tags"><span>';
			tags.each(function(tagItem, tagIndex) {
				var tagParents = '';
				for (var i = (tagItem.PARENTS.length - 1); i >= 0; i--) {
					tagParents += tagItem.PARENTS[i].NAME;
					if (i != 0) tagParents += '||';
				}
				output += '<span onmouseout="$K.yg_hideTagHint();" onmouseover="$K.yg_hoverTagHint(this,' + tagItem.ID + ');" path="' + tagParents + '">' + tagItem.NAME + '</span>';
				if (tagIndex != (tags.length - 1)) output += ', ';
			});
			output += '</span></div>';
		}
		tagContainer.update(output);
	}
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionAddChildFolder
 */
$K.yg_actionAddChildFolder = function(which) {
	$K.yg_addChildFolder(which.up(2).reference);
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @param { Boolean } [multi] True if multiple items are selected.
 * @function
 * @name $K.yg_actionDeleteFolder
 */
$K.yg_actionDeleteFolder = function(which, multi) {
	$K.yg_deleteElement(which.up(2).reference, multi);
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionMoveUpFolder
 */
$K.yg_actionMoveUpFolder = function(which) {
	$K.yg_moveUpFolder(which.up(2).reference);
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionMoveDownFolder
 */
$K.yg_actionMoveDownFolder = function(which) {
	$K.yg_moveDownFolder(which.up(2).reference);
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionCopyFolder
 */
$K.yg_actionCopyFolder = function(which) {
	var wid = parseInt(which.up('.ywindow').id.replace(/wid_/g, ''));
	$K.windows['wid_' + wid].yg_id = nlsTree['files_tree' + wid + '_tree'].nLst[which.up(2).reference.id].yg_id;
	new $K.yg_wndobj({ config:'FOLDER_COPY', loadparams:{ opener_reference:which.up('.ywindow').id, type:'folder' } });
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name $K.yg_actionCopyFile
 */
$K.yg_actionCopyFile = function(which) {
	var wid = parseInt(which.up('.ywindow').id.replace(/wid_/g, ''));
	var sourceYgId = which.readAttribute('yg_id');
	//$K.windows['wid_'+wid].yg_id = which.readAttribute( 'yg_id' );
	new $K.yg_wndobj({ config:'FILE_COPY', loadparams:{ sourceYgId:sourceYgId, opener_reference:which.up('.ywindow').id, type:'file' } });
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name yg_actionMoveFolder
 */
$K.yg_actionMoveFolder = function(which) {
	var wid = parseInt(which.up('.ywindow').id.replace(/wid_/g, ''));
	var sourceYgId = nlsTree['files_tree' + wid + '_tree'].nLst[which.up(2).reference.id].yg_id;
	$K.windows['wid_' + wid].yg_id = nlsTree['files_tree' + wid + '_tree'].nLst[which.up(2).reference.id].yg_id;
	new $K.yg_wndobj({ config:'FOLDER_MOVE', loadparams:{ sourceYgId:sourceYgId, opener_reference:which.up('.ywindow').id, type:'folder' } });
}


/**
 * Wrapper for above functions when mapped in actionbuttons
 * @param { Element } [which] The element from which the function was called.
 * @function
 * @name yg_actionMoveFile
 */
$K.yg_actionMoveFile = function(which) {
	var wid = parseInt(which.up('.ywindow').id.replace(/wid_/g, ''));
	$K.windows[which.up('.ywindow').id].yg_id = which.readAttribute('yg_id');
	new $K.yg_wndobj({ config:'FILE_MOVE', loadparams:{ opener_reference:which.up('.ywindow').id, type:'file', sourceYgId:which.readAttribute('yg_id') } });
}


/**
 * Adds a fileid from the backend to an element in the uploadmanager
 * @param { String } [filePrefix] The prefix of the file.
 * @param { String } [uploadID] The Upload-id of the uploaded element.
 * @function
 * @name yg_addFileId
 */
$K.yg_addFileId = function(filePrefix, uploadID, reUpload, title, color, typecode) {
	$('wid_uploadprogress').select('li').each(function(item) {
		if (item.readAttribute('yg_id') == uploadID + '-file') {
			item.filePrefix = filePrefix;
			if (reUpload == 'true') {
				item.reUpload = true;
			} else {
				item.reUpload = false;
			}
			item.observe('dblclick', function(ev) {
				$K.yg_openObjectDetails(filePrefix.split('-')[0], 'file', title, { color:color, typecode:typecode });
			});
			item.down('.edit').show().observe('click', function(ev) {
				$K.yg_openObjectDetails(filePrefix.split('-')[0], 'file', title, { color:color, typecode:typecode });
			});
		}
	});
}


/**
 * Changes the status of a file in the uploadqueue window to "OK"
 * @param { String } [filePrefix] The prefix of the file.
 * @function
 * @name yg_setFileStatusOK
 */
$K.yg_setFileStatusOK = function(filePrefix) {
	$('wid_uploadprogress').select('li').each(function(item) {
		if (item.filePrefix == filePrefix) {
			item.removeClassName('uploadprocessing');
			item.addClassName('uploaddone');
			item.down('.uploadpercent').update('OK');
			//item.down(1).next().fade({ duration: 1, from: 0, to: 1 });
		} else if (item.id == filePrefix) {
			item.removeClassName('uploadprocessing');
			item.addClassName('uploaddone');
			item.down('.uploadpercent').update('OK');
			item.removeAttribute('id');
			//item.down(1).next().fade({ duration: 1, from: 0, to: 1 });
		}
	});
}


/**
 * Changes the disabled state of the uploadbutton for folders
 * @param { String } [wid] The window-id.
 * @param { Boolean } [wid] The window-id.
 * @function
 * @name yg_setFileUploadButton
 */
$K.yg_setFileUploadButton = function(wid, disable) {
	if ($($K.windows['wid_' + wid].boundWindow + '_uploadbtn')) {
		if (disable == 'true') {
			$($K.windows['wid_' + wid].boundWindow + '_uploadbtn').addClassName('disabled');
		} else {
			$($K.windows['wid_' + wid].boundWindow + '_uploadbtn').removeClassName('disabled');
		}
	}
}


/**
 * Set the preview and download buttons in the file-info window
 * @param { String } [winID] The window-id.
 * @param { String } [fileID] The file-id.
 * @param { String } [url] The file url.
 * @function
 * @name yg_setFileUploadButton
 */
$K.yg_setFileInfoLinks = function(winID, fileID, url) {
	$('wid_' + winID + '_downloadlink').writeAttribute('href', url);
	$('wid_' + winID + '_previewlink').href = $K.internalprefix + '?preview=1&objecttype=file&id=' + fileID;
	$('wid_' + winID + '_previewlink').writeAttribute('onclick', 'Event.stop(event);$K.yg_preview({objecttype: \'file\', id: ' + fileID + ', fullscreen: true});');
	$('wid_' + winID + '_previewlinkwin').writeAttribute('onclick', '$K.yg_preview({objecttype: \'file\', id: ' + fileID + '});');
}


/**
 * Callback function for sortable list
 * @name $K.filesSortCallbacks
 */
$K.filesSortCallbacks = {
	onUpdate:function(element) {
		var listArray = Array();
		for (var i = 0; i < element.childNodes.length; i++) {
			var fileID = element.childNodes[i].readAttribute('yg_id').split('-');
			fileID = fileID[0];
			listArray.push(fileID);
		}

		var parentWin = $K.windows[this.element.up('.ywindow').id];
		var siteID = parentWin.yg_id.split('-')[1];
		var objectID = parentWin.yg_id.split('-')[0];

		var data = Array('noevent', {yg_property:'orderObjectFile', params:{
			objectID:objectID,
			site:siteID,
			listArray:listArray
		} });
		$K.yg_AjaxCallback(data, 'orderObjectFile');
	}
};

/**
 * Change the SE-Friendly filename (PNAME)
 * @param { Element } [element] The element from which the function was called.
 * @function
 * @name $K.yg_changeFilePName
 */
$K.yg_changeFilePName = function(element) {

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

	if (value.strip() == '') {
		element.value = element.oldvalue;
		$K.yg_promptbox($K.TXT('TXT_CHANGE_FILE_URL_TITLE'), $K.TXT('TXT_CHANGE_FILE_URL_EMPTY'), 'alert');
		return;
	}

	if (!isNaN(value)) {
		element.value = element.oldvalue;
		$K.yg_promptbox($K.TXT('TXT_CHANGE_FILE_URL_TITLE'), $K.TXT('TXT_CHANGE_FILE_URL_NUMERIC'), 'alert');
		return;
	}

	$K.yg_promptbox($K.TXT('TXT_CHANGE_FILE_URL_TITLE'), $K.TXT('TXT_CHANGE_FILE_URL'), 'standard',
		function() {
			$K.yg_setEdited(element);
			element.setAttribute('yg_previous', value);

			var file = yg_id.split('-')[0];

			var data = Array('noevent', { yg_property:'setFilePName', params:{
				value:value,
				file:file
			} });
			$K.yg_AjaxCallback(data, 'setFilePName');
		},
		function() {
			element.value = element.getAttribute('yg_previous');
		}
	);

}
