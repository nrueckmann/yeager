/**
 * Submits the contenteditor window
 * @param { String } [winID] The window-id
 * @param { String } [formfield] The formfield to submit into
 * @param { Boolean } [saveOnly] TRUE when the content should only be saved
 * @function
 * @name $K.yg_submitContentEditor
 */
$K.yg_submitContentEditor = function( winID, formfield, saveOnly ) {

	tinyMCE.triggerSave();

	if ($(formfield)) {
		if (formfield.endsWith('comment')) {
			var src_content = $(formfield).down('.mk_longcomment');
		} else {
			var src_content = $(formfield).down('.textbox');
		}
		src_content.innerHTML = $('textarea_'+winID).value;
		src_content.realHTML = $('textarea_'+winID).value;

		src_content.select('img').each(function(imageItem){
			$(imageItem).observe('load', function(event){
				$K.windows[src_content.up('.ywindow').id].refresh("col1");
			});
		});
	}

	if ($K.windows['wid_'+winID].loadparams.commentMode) {
		// For comments
		var loadParams = $K.windows['wid_'+winID].loadparams;
		if ($K.windows['wid_'+winID].loadparams.commentMode == 'edit') {
			if ($(formfield)) {
				var longComment = $('textarea_'+winID).value.stripScripts();
				var shortComment = $('textarea_'+winID).value.stripScripts().stripTags().truncate(336, '...');
				//if ($('textarea_'+winID).value.stripScripts().length > 336) {
				if (longComment != shortComment) {
					if ( !$(formfield).down('div.less').down('a.cmtcontrol') ) {
						$(formfield).down('div.less').insert('<a onclick="$K.yg_toggleComment(this, event);" class="cmtcontrol">' + $K.TXT('TXT_MORE') + '</a>');
					}
					if ( !$(formfield).down('div.more').down('a.cmtcontrol') ) {
						$(formfield).down('div.more').insert('<a onclick="$K.yg_toggleComment(this, event);" class="cmtcontrol">' + $K.TXT('TXT_LESS') + '</a>');
					}
				} else {
					if ($(formfield).down('.commentpost').hasClassName('more')) {
						$(formfield).down('.commentpost').removeClassName('more');
						$(formfield).down('.commentpost').addClassName('less');
					}
					if ( $(formfield).down('div.less').down('a.cmtcontrol') ) {
						$(formfield).down('div.less').down('a.cmtcontrol').remove();
					}
					if ( $(formfield).down('div.more').down('a.cmtcontrol') ) {
						$(formfield).down('div.more').down('a.cmtcontrol').remove();
					}
				}

				$(formfield).down('.mk_shortcomment').update( shortComment );
				$(formfield).down('.mk_longcomment').update( longComment );
			}
			var saveOptions = {
				winID:			loadParams.openerWinID,
				yg_type:		loadParams.openerType,
				yg_id:			loadParams.openerYgID,
				commentID:		loadParams.commentID,
				commentText:	$('textarea_'+winID).value
			};
			$K.yg_saveComment(saveOptions);
			$K.yg_fadeField( src_content.up('.cntblockcontainer') );
		} else if ($K.windows['wid_'+winID].loadparams.commentMode == 'add') {
			var saveOptions = {
				winID:				loadParams.openerWinID,
				yg_type:			loadParams.openerType,
				yg_id:				loadParams.openerYgID,
				parentCommentID:	loadParams.parentCommentID,
				commentText:		$('textarea_'+winID).value
			};
			$K.yg_addComment(saveOptions);
		}
	} else if ($(formfield) && formfield.endsWith('property')) {
		// For properties
		$K.yg_setObjectProperty(src_content);
		$K.yg_fadeField( src_content.up('.cntblock') );
		src_content.next('textarea').value = $('textarea_'+winID).value;
	} else if ($(formfield)) {
		// For controls
		$K.yg_editControl(src_content, 3);
		$K.yg_fadeField( src_content.up('.maskedit') );
	}

	if (src_content) {
		$K.windows[src_content.up('.ywindow').id].refresh("col1");
	}
	if (!saveOnly) {
		$K.windows['wid_'+winID].remove();
	}
}

$K.yg_defaultTinyMCEButtonLayout = [
	{
		group1: [
			'bold',
			'italic',
			'underline',
			'strikethrough',
			'sub',
			'sup',
			'removeformat'
		],
		group2: [
	 		'image',
	 		'media'
		],
		group3: [
			'table',
			'row_props',
			'cell_props',
			'merge_cells',
			'split_cells'
		],
		group4: [
			'cut',
			'copy',
			'paste',
			'code',
			'replace'
		],
		group5: [
			'styleselect'
		]
	},
	{
		group1: [
			'justifyleft',
			'justifycenter',
			'justifyright',
			'justifyfull',
			'bullist',
			'numlist',
			'outdent',
			'indent'
		],
		group2: [
			'hr'
		],
		group3: [
			'row_before',
			'row_after',
			'delete_row',
			'col_before',
			'col_after',
			'delete_col'
		],
		group4: [
			'link',
			'unlink',
			'anchor',
			'cleanup'
		],
		group5: [
			'formatselect'
		]
	}
];

/**
 * Default TinyMCE Options
 */
$K.yg_tinyMCEOptions = {
	mode: 'none',

	/**/
	theme: 'yeager',
	plugins: 'safari,style,yeager_table,save,paste,advhr,yeager_advlink,yeager_media,yeager_searchreplace,yeager_contextmenu,noneditable,visualchars,template,inlinepopups',
	/**/

	/*
	theme: 'advanced',
	plugins: 'safari,style,table,save,advhr,advlink,media,searchreplace,contextmenu,paste,noneditable,visualchars,template,inlinepopups',
	*/

	theme_advanced_buttons1: 'bold,italic,underline,strikethrough,sub,sup,|,removeformat,||,image,media,||,table,row_props,cell_props,merge_cells,split_cells,|,||,cut,copy,paste,code,replace,||,styleselect',
	theme_advanced_buttons2: 'justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,outdent,indent,||,hr,||,|,row_before,row_after,delete_row,col_before,col_after,delete_col ,||,link,unlink,anchor,cleanup,||,formatselect',
	theme_advanced_customscrollbars: false,
	doctype : '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
	extended_valid_elements: 'iframe[*],embed[*],object[*],param[*],a[*]',
	media_strict: false,
	add_form_submit_trigger: false,
	submit_patch: false,
	cleanup_on_startup: true,
	force_br_newlines: false,
	force_p_newlines: false,
	forced_root_block: false,
	cleanup: true,
	entity_encoding: 'named',
	entities: '160,nbsp',
	relative_urls: false,
	urlconverter_callback: '$K.yg_urlCallback',
	save_onsavecallback: '$K.yg_saveContenteditor',
	debug: false,
	init_instance_callback: '$K.yg_onInitCE',
	handle_node_change_callback : 'yeagerCEnodeChange'
}

/**
 * Generates the toolbar options for tinyMCE
 * @param { String } [configString] The config string
 * @function
 * @name $K.yg_generateTinyMCEToolbar
 */
$K.yg_generateTinyMCEToolbar = function ( configString ) {
	// Replace group-shortcuts for tables
	if (configString.indexOf('tables') != -1) {
		configString = configString.replace(/tables/, 'table,row_props,cell_props,merge_cells,split_cells,row_before,row_after,delete_row,col_before,col_after,delete_col');
	}

	// Replace group-shortcuts for alignment
	if (configString.indexOf('alignment') != -1) {
		configString = configString.replace(/alignment/, 'justifyleft,justifycenter,justifyright,justifyfull');
	}

	// Replace group-shortcuts for links
	if (configString.indexOf('links') != -1) {
		configString = configString.replace(/links/, 'link,unlink');
	}

	var configStringArray = configString.replace(/ /, '').split(',');

	// Check count of buttons in all groups
	var groupsCount = new Array();
	for (var i=0;i<$K.yg_defaultTinyMCEButtonLayout.length;i++) {
		for (var groupName in $K.yg_defaultTinyMCEButtonLayout[i]) {
			if (groupsCount[i] == undefined) groupsCount[i] = {};
			$K.yg_defaultTinyMCEButtonLayout[i][groupName].each(function(buttonItem) {
				if (configStringArray.indexOf(buttonItem) != -1) {
					if (groupsCount[i][groupName] == undefined) {
						groupsCount[i][groupName] = 0;
					}
					groupsCount[i][groupName]++;
				}
			});
		}
	}

	// Find maximum number of buttons in a group
	var targetGroupsCount = {};
	for (var groupName in groupsCount[0]) {
		var maxButtonsInGroup = 0;
		for (var i=0;i<groupsCount.length;i++) {
			if (groupsCount[i][groupName] > maxButtonsInGroup) {
				maxButtonsInGroup = groupsCount[i][groupName];
			}
		}
		targetGroupsCount[groupName] = maxButtonsInGroup;
	}

	// Generate Line 1
	var buttonsLine1 = new Array();
	for (var groupName in $K.yg_defaultTinyMCEButtonLayout[0]) {
		var buttonsInGroup = 0;
		$K.yg_defaultTinyMCEButtonLayout[0][groupName].each(function(buttonItem) {
			if (configStringArray.indexOf(buttonItem) != -1) {
				buttonsLine1.push(buttonItem);
				buttonsInGroup++;
			}
		});
		if ((buttonsInGroup > 0) && (buttonsInGroup < targetGroupsCount[groupName])) {
			for (var i=0;i< (targetGroupsCount[groupName]-buttonsInGroup);i++) {
				buttonsLine1.push('|');
			}
		}
		if (buttonsInGroup > 0) {
			buttonsLine1.push('||');
		}
	}

	// Generate Line 2
	var buttonsLine2 = new Array();
	for (var groupName in $K.yg_defaultTinyMCEButtonLayout[1]) {
		var buttonsInGroup = 0;
		$K.yg_defaultTinyMCEButtonLayout[1][groupName].each(function(buttonItem) {
			if (configStringArray.indexOf(buttonItem) != -1) {
				buttonsLine2.push(buttonItem);
				buttonsInGroup++;
			}
		});
		if ((buttonsInGroup > 0) && (buttonsInGroup < targetGroupsCount[groupName])) {
			for (var i=0;i< (targetGroupsCount[groupName]-buttonsInGroup);i++) {
				buttonsLine2.push('|');
			}
		}
		if (buttonsInGroup > 0) {
			buttonsLine2.push('||');
		}
	}

	// Remove trailing '||'
	if (buttonsLine1.last() == '||') buttonsLine1.pop();
	if (buttonsLine2.last() == '||')  buttonsLine2.pop();

	buttonsLine1 = buttonsLine1.join(',')
	buttonsLine2 = buttonsLine2.join(',');

	// Loop through blockformats
	var blockFormats = 'p,address,pre,h1,h2,h3,h4,h5,h6';
	var blockFormatsArray = new Array();
	blockFormats.split(',').each(function(optionItem) {
		if (configStringArray.indexOf(optionItem) != -1) {
			blockFormatsArray.push(optionItem);
		}
	});
	blockFormats = blockFormatsArray.join(',');

	var output = {
		theme_advanced_buttons1: buttonsLine1,
		theme_advanced_buttons2: buttonsLine2,
		theme_advanced_blockformats: blockFormats
	};
	return output;
}

/**
 * Initializes the contenteditor window
 * @param { String } [winID] The window-id
 * @param { String } [formfield] The formfield to get the content from
 * @function
 * @name $K.yg_initContenteditor
 */
$K.yg_initContenteditor = function( winID, formfield, configString, customCSS ) {

	var currentTinyMCEOptions = $K.yg_tinyMCEOptions;
	if (!configString) {
		configString = 'bold,italic,underline,strikethrough,sub,sup,removeformat,image,media,tables,code,pasteword,alignment,bullist,numlist,outdent,indent,hr,tables,links,anchor,cleanup,replace,p,address,pre,h1,h2,h3,h4,h5,h6,formatselect,styleselect';
	}

	Object.extend(currentTinyMCEOptions, $K.yg_generateTinyMCEToolbar(configString) );

	if ( customCSS && (customCSS != '') && customCSS.endsWith('.css') ) {
		Object.extend(currentTinyMCEOptions, {content_css: customCSS} );
	}

	if ($K.lang != '') {
		Object.extend(currentTinyMCEOptions, { lang: $K.lang, setup: function(ed) {
			ed.onMouseUp.add(function(ed, e) {
				//ed.selection.select(e.target);
				window.parent.Koala.yg_customSortableOnDrop(ed);
			});
		}});
	}

	tinyMCE.init( currentTinyMCEOptions );

	$K.windows['wid_'+winID].onResize = function() {
		if (tinyMCE.editors['textarea_'+winID] && tinyMCE.editors['textarea_'+winID].theme.YeagerTheme) {
			tinyMCE.editors['textarea_'+winID].theme.resizeTo( (this.boxwidth-44), (this.boxheight-90));
		}
	}
	$('textarea_'+winID).setStyle({width:($K.windows['wid_'+winID].boxwidth-44)+'px'});
	$('textarea_'+winID).setStyle({height:($K.windows['wid_'+winID].boxheight-107)+'px'});

	var src_content, contentHTML;
	if ($(formfield)) {
		if (formfield.endsWith('comment')) {
			src_content = $(formfield).down('.mk_longcomment');
		} else {
			src_content = $(formfield).down('.textbox');
			if (!src_content) src_content = $(formfield).down('.changed');
		}

		contentHTML = src_content.innerHTML;
		contentHTML = contentHTML.replace(/%C2%A7%C2%A7/g, '§§');

		if (contentHTML.toLowerCase() ==  $K.TXT('TXT_CHOOSE_SELECTOR')) contentHTML = '';	//
		$('textarea_'+winID).value = contentHTML;
	}

	tinyMCE.execCommand('mceAddControl', false, 'textarea_'+winID);

	window.setTimeout(function() {
		innerDoc = $('textarea_'+winID+'_ifr').contentDocument || $('textarea_'+winID+'_ifr').contentWindow.document;
		if (innerDoc && innerDoc.addEventListener) {
			innerDoc.addEventListener( 'mousemove', function(e) {
				if (window.parent && window.parent.Koala) window.parent.Koala.yg_checkIframeDrop(winID, Event.pointerX(e), Event.pointerY(e));
			});
		}
	},1000);
}

/**
 * Initializes the contenteditor itself
 * @param { String } [winID] The window-id
 * @function
 * @name $K.yg_initCEEmbedded
 */
$K.yg_initCEEmbedded = function( winID ) {

	var origData = $K.windows['wid_'+winID].ceData;

	var jsEncode = function(s) {
		s = s.replace(new RegExp('\\\\', 'g'), '\\\\');
		s = s.replace(new RegExp('"', 'g'), '\\"');
		s = s.replace(new RegExp("'", 'g'), "\\'");
		return s;
	};

	var setBool = function(pl, p, n) {
		if (typeof(pl[n]) == 'undefined')
			return;

		if (pl[n]) {
			document.forms['wid_'+winID+'_linkForm'].elements[p + "_" + n].value = '1';
			$(document.forms['wid_'+winID+'_linkForm'].elements[p + "_" + n]).up().className = 'checkboxiconsel';
		} else {
			document.forms['wid_'+winID+'_linkForm'].elements[p + "_" + n].value = '0';
			$(document.forms['wid_'+winID+'_linkForm'].elements[p + "_" + n]).up().className = 'checkboxicon';
		}
	};

	var getBool = function(p, n, d, tv, fv) {
		if (!tv) tv = 'true';
		if (!fv) fv = 'false';
		var v = (document.forms['wid_'+winID+'_linkForm'].elements[p + "_" + n].value == 1);

		return (v == d) ? '' : n + (v ? ':' + tv + ',' : ':' + fv + ',');
	};

	var getStr = function(p, n, d) {
		var e = document.forms['wid_'+winID+'_linkForm'].elements[(p != null ? p + "_" : "") + n];

		if ( (e.type == 'text') || (e.type == 'hidden') ) {
			var v =  e.value
		} else {
			var v; //= e.options[e.selectedIndex].value;
		}

		if (n == 'src') {
			v = tinyMCE.activeEditor.convertURL(v, 'src', null);
		}
		return ((n == d || v == '') ? '' : n + ":'" + jsEncode(v) + "',");
	};


	var setStr = function(pl, p, n) {
		var f = document.forms['wid_'+winID+'_linkForm'], e = f.elements[(p != null ? p + "_" : '') + n];

		if (typeof(pl[n]) == 'undefined')
			return;

		if (e && (e.type == "text")) {
			e.value = pl[n];
		} else {
			if (pl[n]!='') {
				if (f.elements[(p != null ? p + "_" : '') + n]) {
					$K.yg_dropdownSelect( f.elements[(p != null ? p + "_" : '') + n], false, pl[n], true);
				}
			}
		}
	};

	var getInt = function (p, n, d) {
		var e = document.forms['wid_'+winID+'_linkForm'].elements[(p != null ? p + "_" : "") + n];
		if ( (e.type == 'text') || (e.type == 'hidden') ) {
			var v = e.value;
		} else {
			var v; //= e.options[e.selectedIndex].value;
		}

		return ((n == d || v == '') ? '' : n + ":" + v.replace(/[^0-9]+/g, '') + ",");
	};

	var changedType = function (t) {
		$('flash_options').hide();
		$('qt_options').hide();
		$('shockwave_options').hide();
		$('wmp_options').hide();
		if (t) {
			$(t + '_options').show();
		}

		$K.windows["wid_"+winID].refresh("col1");
	};
	window.changedType = changedType;

	yeagerCE_saveSelection();

	var pl = '', f, val;
	var type = 'flash', fe, i;

	ed = tinyMCE.activeEditor;

	f = document.forms['wid_'+winID+'_linkForm'];
	fe = ed.selection.getNode();

	type = origData.type;
	switch (type) {
		default:
		case 'flash':
			/*
			myData.type = 'flash';
			myData.params.play = f.flash_play.value;
			myData.params.loop = f.flash_loop.value;
			myData.params.menu = f.flash_menu.value;
			myData.params.swliveconnect = f.flash_swliveconnect.value;
			myData.params.wmode = f.flash_wmode.value;
			myData.params.scale = f.flash_scale.value;
			*/
			$K.yg_dropdownSelect($('dd_types'), false, 'flash', true);
			changedType( 'flash' );
			setBool(origData.params, 'flash', 'play');
			setBool(origData.params, 'flash', 'loop');
			setBool(origData.params, 'flash', 'menu');
			setBool(origData.params, 'flash', 'swliveconnect');
			setStr(origData.params, 'flash', 'quality');
			setStr(origData.params, 'flash', 'scale');
			setStr(origData.params, 'flash', 'salign');
			setStr(origData.params, 'flash', 'wmode');
			setStr(origData.params, 'flash', 'base');
			setStr(origData.params, 'flash', 'flashvars');
			break;
		case 'shockwave':
			/*
			myData.type = 'shockwave';
			myData.params.autostart = f.shockwave_autostart.value;
			myData.params.sound = f.shockwave_sound.value;
			myData.params.progress = f.shockwave_progress.value;
			myData.params.swliveconnect = f.shockwave_swliveconnect.value;
			myData.params.swstretchstyle = f.shockwave_swstretchstyle.value;
			myData.params.swstretchhalign = f.shockwave_swstretchhalign.value;
			myData.params.swstretchvalign = f.shockwave_swstretchhalign.value;
			*/
			$K.yg_dropdownSelect($('dd_types'), false, 'shockwave', true);
			changedType( 'shockwave' );
			setBool(origData.params, 'shockwave', 'sound');
			setBool(origData.params, 'shockwave', 'progress');
			setBool(origData.params, 'shockwave', 'autostart');
			setBool(origData.params, 'shockwave', 'swliveconnect');
			setStr(origData.params, 'shockwave', 'swvolume');
			setStr(origData.params, 'shockwave', 'swstretchstyle');
			setStr(origData.params, 'shockwave', 'swstretchhalign');
			setStr(origData.params, 'shockwave', 'swstretchvalign');
			break;
		case 'quicktime':
			/*
			myData.type = 'quicktime';
			myData.params.autoplay = f.qt_autoplay.value;
			myData.params.loop = f.qt_loop.value;
			myData.params.cache = f.qt_cache.value;
			myData.params.controller = f.qt_controller.value;
			myData.params.kioskmode = f.qt_kioskmode.value;
			myData.params.playeveryframe = f.qt_playeveryframe.value;
			myData.params.targetcache = f.qt_cache.value;
			myData.params.autohref = f.qt_autohref.value;
			*/
			$K.yg_dropdownSelect($('dd_types'), false, 'qt', true);
			changedType( 'qt' );
			setBool(origData.params, 'qt', 'loop');
			setBool(origData.params, 'qt', 'autoplay');
			setBool(origData.params, 'qt', 'cache');
			setBool(origData.params, 'qt', 'controller');
			setBool(origData.params, 'qt', 'correction');
			setBool(origData.params, 'qt', 'enablejavascript');
			setBool(origData.params, 'qt', 'kioskmode');
			setBool(origData.params, 'qt', 'autohref');
			setBool(origData.params, 'qt', 'playeveryframe');
			setBool(origData.params, 'qt', 'tarsetcache');
			setStr(origData.params, 'qt', 'scale');
			setStr(origData.params, 'qt', 'starttime');
			setStr(origData.params, 'qt', 'endtime');
			setStr(origData.params, 'qt', 'tarset');
			setStr(origData.params, 'qt', 'qtsrcchokespeed');
			setStr(origData.params, 'qt', 'volume');
			setStr(origData.params, 'qt', 'qtsrc');
			break;
		case 'windowsmedia':
			/*
			myData.type = 'windowsmedia';
			myData.params.autostart = f.wmp_autostart.value;
			myData.params.enabled = f.wmp_enabled.value;
			myData.params.fullscreen = f.wmp_fullscreen.value;
			myData.params.mute = f.wmp_mute.value;
			myData.params.stretchtofit = f.wmp_stretchtofit.value;
			myData.params.invokeurls = f.wmp_invokeurls.value;
			myData.params.windowlessvideo = f.wmp_windowlessvideo.value;
			myData.params.enablecontextmenu = f.wmp_enablecontextmenu.value;
			*/
			$K.yg_dropdownSelect($('dd_types'), false, 'wmp', true);
			changedType( 'wmp' );
			setBool(origData.params, 'wmp', 'autostart');
			setBool(origData.params, 'wmp', 'enabled');
			setBool(origData.params, 'wmp', 'enablecontextmenu');
			setBool(origData.params, 'wmp', 'fullscreen');
			setBool(origData.params, 'wmp', 'invokeurls');
			setBool(origData.params, 'wmp', 'mute');
			setBool(origData.params, 'wmp', 'stretchtofit');
			setBool(origData.params, 'wmp', 'windowlessvideo');
			setStr(origData.params, 'wmp', 'balance');
			setStr(origData.params, 'wmp', 'baseurl');
			setStr(origData.params, 'wmp', 'captioningid');
			setStr(origData.params, 'wmp', 'currentmarker');
			setStr(origData.params, 'wmp', 'currentposition');
			setStr(origData.params, 'wmp', 'defaultframe');
			setStr(origData.params, 'wmp', 'playcount');
			setStr(origData.params, 'wmp', 'rate');
			setStr(origData.params, 'wmp', 'uimode');
			setStr(origData.params, 'wmp', 'volume');
			break;
	}

	setStr(origData.params, null, 'src');
	setStr(origData, null, 'id');
	setStr(origData, null, 'name');
	setStr(origData, null, 'vspace');
	setStr(origData, null, 'hspace');
	setStr(origData, null, 'bgcolor');
	setStr(origData, null, 'align');
	setStr(origData, null, 'width');
	setStr(origData, null, 'height');

	/***
	***/

	/*
	// Setup form
	if (pl != '') {
		pl = tinyMCE.activeEditor.plugins.yeager_media._parse(pl);

		switch (type) {
			case 'flash':
				setBool(pl, 'flash', 'play');
				setBool(pl, 'flash', 'loop');
				setBool(pl, 'flash', 'menu');
				setBool(pl, 'flash', 'swliveconnect');
				setStr(pl, 'flash', 'quality');
				setStr(pl, 'flash', 'scale');
				setStr(pl, 'flash', 'salign');
				setStr(pl, 'flash', 'wmode');
				setStr(pl, 'flash', 'base');
				setStr(pl, 'flash', 'flashvars');
			break;

			case 'qt':
				setBool(pl, 'qt', 'loop');
				setBool(pl, 'qt', 'autoplay');
				setBool(pl, 'qt', 'cache');
				setBool(pl, 'qt', 'controller');
				setBool(pl, 'qt', 'correction');
				setBool(pl, 'qt', 'enablejavascript');
				setBool(pl, 'qt', 'kioskmode');
				setBool(pl, 'qt', 'autohref');
				setBool(pl, 'qt', 'playeveryframe');
				setBool(pl, 'qt', 'tarsetcache');
				setStr(pl, 'qt', 'scale');
				setStr(pl, 'qt', 'starttime');
				setStr(pl, 'qt', 'endtime');
				setStr(pl, 'qt', 'tarset');
				setStr(pl, 'qt', 'qtsrcchokespeed');
				setStr(pl, 'qt', 'volume');
				setStr(pl, 'qt', 'qtsrc');
			break;

			case 'shockwave':
				setBool(pl, 'shockwave', 'sound');
				setBool(pl, 'shockwave', 'progress');
				setBool(pl, 'shockwave', 'autostart');
				setBool(pl, 'shockwave', 'swliveconnect');
				setStr(pl, 'shockwave', 'swvolume');
				setStr(pl, 'shockwave', 'swstretchstyle');
				setStr(pl, 'shockwave', 'swstretchhalign');
				setStr(pl, 'shockwave', 'swstretchvalign');
			break;

			case 'wmp':
				setBool(pl, 'wmp', 'autostart');
				setBool(pl, 'wmp', 'enabled');
				setBool(pl, 'wmp', 'enablecontextmenu');
				setBool(pl, 'wmp', 'fullscreen');
				setBool(pl, 'wmp', 'invokeurls');
				setBool(pl, 'wmp', 'mute');
				setBool(pl, 'wmp', 'stretchtofit');
				setBool(pl, 'wmp', 'windowlessvideo');
				setStr(pl, 'wmp', 'balance');
				setStr(pl, 'wmp', 'baseurl');
				setStr(pl, 'wmp', 'captioningid');
				setStr(pl, 'wmp', 'currentmarker');
				setStr(pl, 'wmp', 'currentposition');
				setStr(pl, 'wmp', 'defaultframe');
				setStr(pl, 'wmp', 'playcount');
				setStr(pl, 'wmp', 'rate');
				setStr(pl, 'wmp', 'uimode');
				setStr(pl, 'wmp', 'volume');
			break;
		}

		setStr(pl, null, 'src');
		setStr(pl, null, 'id');
		setStr(pl, null, 'name');
		setStr(pl, null, 'vspace');
		setStr(pl, null, 'hspace');
		setStr(pl, null, 'bgcolor');
		setStr(pl, null, 'align');
		setStr(pl, null, 'width');
		setStr(pl, null, 'height');

		if ((val = ed.dom.getAttrib(fe, 'width')) != '')
			pl.width = f.width.value = val;

		if ((val = ed.dom.getAttrib(fe, 'height')) != '')
			pl.height = f.height.value = val;

		oldWidth = pl.width ? parseInt(pl.width) : 0;
		oldHeight = pl.height ? parseInt(pl.height) : 0;
	} else
		oldWidth = oldHeight = 0;

	if (type!='') $K.yg_dropdownSelect($('dd_types'), false, type, true);

	changedType( type );
	*/

	serializeParameters = function() {
		var d = document, f = document.forms['wid_'+winID+'_linkForm'], s = '';

		switch (f.dd_types.value) {
			case 'flash':
				s += getBool('flash', 'play', false);
				s += getBool('flash', 'loop', false);
				s += getBool('flash', 'menu', false);
				s += getBool('flash', 'swliveconnect', false);
				s += getStr('flash', 'scale');
				s += getStr('flash', 'wmode');
			break;

			case 'qt':
				s += getBool('qt', 'loop', false);
				s += getBool('qt', 'autoplay', false);
				s += getBool('qt', 'cache', false);
				s += getBool('qt', 'controller', false);
				s += getBool('qt', 'kioskmode', false);
				s += getBool('qt', 'autohref', false);
				s += getBool('qt', 'playeveryframe', false);
				s += getBool('qt', 'targetcache', true);
			break;

			case 'shockwave':
				s += getBool('shockwave', 'sound', false);
				s += getBool('shockwave', 'progress', false);
				s += getBool('shockwave', 'autostart', false);
				s += getBool('shockwave', 'swliveconnect', false);
				s += getStr('shockwave', 'swstretchstyle');
				s += getStr('shockwave', 'swstretchhalign');
				s += getStr('shockwave', 'swstretchvalign');
			break;

			case 'wmp':
				s += getBool('wmp', 'autostart', false);
				s += getBool('wmp', 'enabled', false);
				s += getBool('wmp', 'enablecontextmenu', false);
				s += getBool('wmp', 'fullscreen', false);
				s += getBool('wmp', 'invokeurls', false);
				s += getBool('wmp', 'mute', false);
				s += getBool('wmp', 'stretchtofit', false);
				s += getBool('wmp', 'windowlessvideo', false);
			break;
		}

		s += getStr(null, 'id');
		s += getStr(null, 'name');
		s += getStr(null, 'src');
		s += getStr(null, 'align');
		s += getInt(null, 'vspace');
		s += getInt(null, 'hspace');
		s += getStr(null, 'width');
		s += getStr(null, 'height');

		s = s.length > 0 ? s.substring(0, s.length - 1) : s;

		return s;
	};


	$K.windows['wid_'+winID].submitCE_embedded = function() {

		var fe, f = document.forms['wid_'+winID+'_linkForm'], h;

		winID = this.num;

		yeagerCE_restoreSelection();

		f.width.value = f.width.value == '' ? 100 : f.width.value;
		f.height.value = f.height.value == '' ? 100 : f.height.value;

		var myData = {
			params: {},
			video: {}
		};

		myData.width = f.width.value;
		myData.height = f.height.value;

		myData.name = f.name.value;
		myData.id = f.id.value;
		myData.align = $(f.dd_align).up('.dropdownbox').down('input[type=hidden]').value;
		myData.hspace = f.hspace.value;
		myData.vspace = f.vspace.value;

		switch (f.dd_types.value) {
			default:
			case 'flash':
				myData.type = 'flash';
				myData.params.play = f.flash_play.value;
				myData.params.loop = f.flash_loop.value;
				myData.params.menu = f.flash_menu.value;
				myData.params.swliveconnect = f.flash_swliveconnect.value;
				myData.params.wmode = f.flash_wmode.value;
				myData.params.scale = f.flash_scale.value;
				break;
			case 'shockwave':
				myData.type = 'shockwave';
				myData.params.autostart = f.shockwave_autostart.value;
				myData.params.sound = f.shockwave_sound.value;
				myData.params.progress = f.shockwave_progress.value;
				myData.params.swliveconnect = f.shockwave_swliveconnect.value;
				myData.params.swstretchstyle = f.shockwave_swstretchstyle.value;
				myData.params.swstretchhalign = f.shockwave_swstretchhalign.value;
				myData.params.swstretchvalign = f.shockwave_swstretchhalign.value;
				break;
			case 'qt':
				myData.type = 'quicktime';
				myData.params.autoplay = f.qt_autoplay.value;
				myData.params.loop = f.qt_loop.value;
				myData.params.cache = f.qt_cache.value;
				myData.params.controller = f.qt_controller.value;
				myData.params.kioskmode = f.qt_kioskmode.value;
				myData.params.playeveryframe = f.qt_playeveryframe.value;
				myData.params.targetcache = f.qt_cache.value;
				myData.params.autohref = f.qt_autohref.value;
				break;
			case 'wmp':
				myData.type = 'windowsmedia';
				myData.params.autostart = f.wmp_autostart.value;
				myData.params.enabled = f.wmp_enabled.value;
				myData.params.fullscreen = f.wmp_fullscreen.value;
				myData.params.mute = f.wmp_mute.value;
				myData.params.stretchtofit = f.wmp_stretchtofit.value;
				myData.params.invokeurls = f.wmp_invokeurls.value;
				myData.params.windowlessvideo = f.wmp_windowlessvideo.value;
				myData.params.enablecontextmenu = f.wmp_enablecontextmenu.value;
				break;
		}

		myData.params.src = f.src.value;
		ed.execCommand('mceRepaint');

		yeagerCE_restoreSelection();

		ed.selection.setNode(ed.plugins.yeager_media.dataToImg(myData));

		$K.windows['wid_'+winID].remove();
	}


}


/**
 * Initializes the contenteditor find-replace window
 * @param { String } [winID] The window-id
 * @function
 * @name $K.yg_initCEFindReplace
 */
$K.yg_initCEFindReplace = function( winID ) {

	var ed = tinyMCE.activeEditor;
	var search_string = ed.selection.getContent({format : 'text'});
	var f = document.forms['wid_'+winID+'_linkForm'];
	var t = winID;
	var m = $K.windows['wid_'+winID].tab;

	yeagerCE_saveSelection();

	var win = $('wid_'+winID);
	var lf, f, lt = $K.windows['wid_'+winID].lastTab;

	yeagerCE_restoreSelection();

	if (lt != m) {
		if (!lt) lt = 'CONTENTEDITOR_FIND';

		f = document.forms['wid_'+winID+'_linkForm_'+m];
		lf = document.forms['wid_'+winID+'_linkForm_'+lt];

		f[m + '_panel_searchstring'].value = lf[lt + '_panel_searchstring'].value;

		if (lf[lt + '_panel_backwards'].value == lt+'_panel_backwardsd') {
			if (f[m + '_panel_backwards'].value != m+'_panel_backwardsd') {
				f[m + '_panel_backwards'].value = m+'_panel_backwardsd';
				$K.yg_radioboxClick( $(m + '_panel_backwardsd') );
				$K.yg_radioboxSelect( $(m + '_panel_backwardsd') );
			}
		} else {
			if (f[m + '_panel_backwards'].value != m+'_panel_backwardsu') {
				f[m + '_panel_backwards'].value = m+'_panel_backwardsu';
				$K.yg_radioboxClick( $(m + '_panel_backwardsu') );
				$K.yg_radioboxSelect( $(m + '_panel_backwardsu') );
			}
		}

		if (lf[lt + '_panel_casesensitivebox'].value=='1') {
			if (f[m + '_panel_casesensitivebox'].value != 1) {
				f[m + '_panel_casesensitivebox'].value = 1;
				$K.yg_checkboxClick($('chk_' + m + '_panel_casesensitivebox'));
				$K.yg_checkboxSelect($('chk_' + m + '_panel_casesensitivebox'));
			}
		} else {
			if (f[m + '_panel_casesensitivebox'].value != 0) {
				f[m + '_panel_casesensitivebox'].value = 0;
				$K.yg_checkboxClick($('chk_' + m + '_panel_casesensitivebox'));
				$K.yg_checkboxSelect($('chk_' + m + '_panel_casesensitivebox'));
			}
		}
		$K.windows['wid_'+winID].lastTab = m;
	}

}


/**
 * Initializes the contenteditor html-source window
 * @param { String } [winID] The window-id
 * @function
 * @name $K.yg_initCEHTML
 */
$K.yg_initCEHTML = function( winID ) {

	var loadParams = $K.windows['wid_'+winID].loadparams;

	if (loadParams && loadParams.commentMode) {
		// For comments
		$K.windows['wid_'+winID].submitCE_html = function() {

			if ($(loadParams.formfield)) {
				if (loadParams.formfield.endsWith('comment')) {
					var src_content = $(loadParams.formfield).down('.mk_longcomment');
				} else {
					var src_content = $(loadParams.formfield).down('.textbox');
				}
				src_content.innerHTML = $('wid_'+winID+'_htmlSource').value.stripScripts().stripTags();

				src_content.select('img').each(function(imageItem){
					$(imageItem).observe('load', function(event){
						$K.windows[src_content.up('.ywindow').id].refresh("col1");
					});
				});
			}

			if ($K.windows['wid_'+winID].loadparams.commentMode == 'edit') {
				if ($(loadParams.formfield)) {
					$(loadParams.formfield).down('.mk_shortcomment').update( $('wid_'+winID+'_htmlSource').value.stripScripts().stripTags().truncate(350,'...') );
				}
				var saveOptions = {
					winID:			loadParams.openerWinID,
					yg_type:		loadParams.openerType,
					yg_id:			loadParams.openerYgID,
					commentID:		loadParams.commentID,
					commentText:	$('wid_'+winID+'_htmlSource').value.stripScripts().stripTags()
				};
				$K.yg_saveComment(saveOptions);
				$K.yg_fadeField( src_content.up('.cntblockcontainer') );
			} else if ($K.windows['wid_'+winID].loadparams.commentMode == 'add') {
				var saveOptions = {
					winID:				loadParams.openerWinID,
					yg_type:			loadParams.openerType,
					yg_id:				loadParams.openerYgID,
					parentCommentID:	loadParams.parentCommentID,
					commentText:		$('wid_'+winID+'_htmlSource').value.stripScripts().stripTags()
				};
				$K.yg_addComment(saveOptions);
			}

			if (src_content) {
				$K.windows[src_content.up('.ywindow').id].refresh("col1");
			}

			this.remove();
		}

		if ($(loadParams.formfield)) {
			if (loadParams.formfield.endsWith('comment')) {
				src_content = $(loadParams.formfield).down('.mk_longcomment');
			} else {
				src_content = $(loadParams.formfield).down('.textbox');
				if (!src_content) src_content = $(loadParams.formfield).down('.changed');
			}

			contentHTML = src_content.innerHTML;
			if (contentHTML.toLowerCase() == $K.TXT('TXT_CHOOSE_SELECTOR')) contentHTML = '';	//

			$('wid_'+winID+'_htmlSource').value = contentHTML;
		}
	} else {
		$K.windows['wid_'+winID].submitCE_html = function() {
			tinyMCE.activeEditor.setContent( $('wid_'+winID+'_htmlSource').value );
			this.remove();
		}

		$('wid_'+winID+'_htmlSource').value = tinyMCE.activeEditor.getContent();
	}
}


/**
 * Initializes the contenteditor image window
 * @param { String } [winID] The window-id
 * @function
 * @name $K.yg_initCEImage
 */
$K.yg_initCEImage = function( winID ) {

	//yeagerCE_saveSelection();

	var f = document.forms['wid_'+winID+'_linkForm'], ed = tinyMCE.activeEditor;
	var allCustomClasses = ed.dom.getClasses();

	for (var i=0;i<allCustomClasses.length;i++) {
		$K.yg_dropdownInsert( $(f.dd_class).up('.dropdownbox'), allCustomClasses[i]['class'], allCustomClasses[i]['class'], false, 'bottom');
	}

	e = ed.selection.getNode();

	if (e.nodeName == 'IMG') {

		if ($(f.src).readAttribute('preset') != 'true') {
			f.src.value = ed.dom.getAttrib(e, 'src');
		}

		f.alt.value = ed.dom.getAttrib(e, 'alt');
		f.title.value = ed.dom.getAttrib(e, 'title');

		f.vspace.value = yeagerCE_getAttrib(e, 'vspace');
		f.hspace.value = yeagerCE_getAttrib(e, 'hspace');

		f.width.value = ed.dom.getAttrib(e, 'width');
		f.height.value = ed.dom.getAttrib(e, 'height');

		f.style.value = ed.dom.getAttrib(e, 'style');

		var align_value = yeagerCE_getAttrib(e, 'align');
		if (align_value!='') $K.yg_dropdownSelect(f.dd_align, false, align_value, true);

		var className = yeagerCE_getAttrib(e, 'class');
		if (className!='') $K.yg_dropdownSelect(f.dd_class, false, className, true);

		f.style.value = yeagerCE_updateStyle(f.style.value, f);
	}


	$K.windows['wid_'+winID].submitCE_image = function() {

		var f = document.forms['wid_'+winID+'_linkForm'], nl = f.elements, ed = tinyMCE.activeEditor, args = {}, el;

		yeagerCE_restoreSelection();

		f.style.value = yeagerCE_updateStyle(f.style.value, f);

		if (f.src.value === '') {
			if (ed.selection.getNode().nodeName == 'IMG') {
				ed.dom.remove(ed.selection.getNode());
				ed.execCommand('mceRepaint');
			}

			$K.windows['wid_'+winID].remove();
			return;
		}

		if (!ed.settings.inline_styles) {
			args = tinymce.extend(args, {
				vspace : nl.vspace.value,
				hspace : nl.hspace.value,
				align : nl.dd_aligns.value
			});
		} else {
			args.style = f.style.value;
		}

		tinymce.extend(args, {
			src : f.src.value,
			alt : f.alt.value,
			width : f.width.value,
			height : f.height.value,
			title : f.title.value
		});

		tinymce.extend(args, {
			'class' : f.dd_classes.value
		});

		el = ed.selection.getNode();

		if (el && el.nodeName == 'IMG') {
			ed.dom.setAttribs(el, args);
		} else {
			ed.execCommand('mceInsertContent', false, '<img id="__mce_tmp" />', {skip_undo : 1});
			ed.dom.setAttribs('__mce_tmp', args);
			ed.dom.setAttrib('__mce_tmp', 'id', '');
			ed.undoManager.add();
		}

		$K.windows['wid_'+winID].remove();
	}

}


/**
 * Initializes the contenteditor anchor window
 * @param { String } [winID] The window-id
 * @function
 * @name $K.yg_initCEAnchor
 */
$K.yg_initCEAnchor = function( winID ) {

	$K.windows['wid_'+winID].submitCE_anchor = function() {
		var ed = tinyMCE.activeEditor;
		var f = document.forms['wid_'+winID+'_linkForm'];

		yeagerCE_restoreSelection();

		if (f.insert.value != 'update')
			ed.selection.collapse(1);

		// Webkit acts weird if empty inline element is inserted so we need to use a image instead
		if (tinymce.isWebKit)
			ed.execCommand('mceInsertContent', 0, ed.dom.createHTML('img', {mce_name : 'a', name : document.forms['wid_'+winID+'_linkForm'].anchorName.value, 'class' : 'mceItemAnchor'}));
		else
			ed.execCommand('mceInsertContent', 0, ed.dom.createHTML('a', {name : document.forms['wid_'+winID+'_linkForm'].anchorName.value, 'class' : 'mceItemAnchor'}, ''));

		$K.windows['wid_'+winID].remove();
	}

	var ed = tinyMCE.activeEditor;
	var action, elm, f = document.forms['wid_'+winID+'_linkForm'];

	yeagerCE_saveSelection();

	elm = ed.dom.getParent(ed.selection.getNode(), 'A,IMG');
	v = ed.dom.getAttrib(elm, 'name');

	if (v) {
		f.insert.value = 'update';
		f.anchorName.value = v;
	}

}


/**
 * Callback which is fired after initialization of a contenteditor instance
 * @param { Object } [instance] Contenteditor instance
 * @function
 * @name $K.yg_onInitCE
 */
$K.yg_onInitCE = function(instance) {
	if (Prototype.Browser.IE) {
		// Set width of last TD to 'auto'
		$(instance.editorId+'_toolbar1').down('td').siblings().last().style.width = 'auto';
		$(instance.editorId+'_toolbar2').down('td').siblings().last().style.width = 'auto';
	}
}


/**
 * Initializes the contenteditor table window
 * @param { String } [winID] The window-id
 * @function
 * @name $K.yg_initCETable
 */
$K.yg_initCETable = function( winID ) {

	var tmpEd = ed = tinyMCE.activeEditor;
	var allCustomClasses = tmpEd.dom.getClasses();

	for (var i=0;i<allCustomClasses.length;i++) {
		$K.yg_dropdownInsert( $('dd_class'), allCustomClasses[i]['class'], allCustomClasses[i]['class'], false, 'bottom');
	}

	$K.windows['wid_'+winID].submitCE_table = function() {
		var formObj = document.forms['wid_'+winID+'_linkForm'];
		var inst = tinyMCE.activeEditor, dom = inst.dom;
		var cols = 2, rows = 2, border = 0, cellpadding = -1, cellspacing = -1, align, width, height, className, caption, frame, rules;
		var html = '', capEl, elm;
		var style = '';

		yeagerCE_restoreSelection();

		var elm = dom.getParent(inst.selection.getNode(), 'table');
		action = elm ? 'update' : 'insert';

		if (!AutoValidator.validate(formObj)) {
			$K.yg_promptbox($K.TXT('TXT_INVALID_DATA'), $K.TXT('TXT_INVALID_DATA'), 'alert');
			return false;
		}

		elm = dom.getParent(inst.selection.getNode(), 'table');

		// Get form data
		cols = formObj.elements['cols'].value;
		rows = formObj.elements['rows'].value;
		border = formObj.elements['border'].value != "" ? formObj.elements['border'].value  : 0;
		cellpadding = formObj.elements['cellpadding'].value != "" ? formObj.elements['cellpadding'].value : "";
		cellspacing = formObj.elements['cellspacing'].value != "" ? formObj.elements['cellspacing'].value : "";
		align = formObj.elements['dd_alignment'].value;
		width = formObj.elements['width'].value;
		height = formObj.elements['height'].value;
		className = formObj.elements['dd_class'].value;

		// Update table
		if (action == "update") {

			inst.execCommand('mceBeginUndoLevel');

			dom.setAttrib(elm, 'cellPadding', cellpadding, true);
			dom.setAttrib(elm, 'cellSpacing', cellspacing, true);
			dom.setAttrib(elm, 'border', border);
			dom.setAttrib(elm, 'align', align);
			dom.setAttrib(elm, 'frame', frame);
			dom.setAttrib(elm, 'rules', rules);
			dom.setAttrib(elm, 'class', className);
			dom.setAttrib(elm, 'style', style);
			dom.setAttrib(elm, 'id', id);
			dom.setAttrib(elm, 'summary', summary);
			dom.setAttrib(elm, 'dir', dir);
			dom.setAttrib(elm, 'lang', lang);

			capEl = inst.dom.select('caption', elm)[0];

			if (capEl && !caption)
				capEl.parentNode.removeChild(capEl);

			if (!capEl && caption) {
				capEl = elm.ownerDocument.createElement('caption');

			if (!tinymce.isIE)
				capEl.innerHTML = '<br mce_bogus="1"/>';
				elm.insertBefore(capEl, elm.firstChild);
			}

			if (width && inst.settings.inline_styles) {
				dom.setStyle(elm, 'width', width);
				dom.setAttrib(elm, 'width', '');
			} else {
				dom.setAttrib(elm, 'width', width, true);
				dom.setStyle(elm, 'width', '');
			}

			// Remove these since they are not valid XHTML
			dom.setAttrib(elm, 'borderColor', '');
			dom.setAttrib(elm, 'bgColor', '');
			dom.setAttrib(elm, 'background', '');

			if (height && inst.settings.inline_styles) {
				dom.setStyle(elm, 'height', height);
				dom.setAttrib(elm, 'height', '');
			} else {
				dom.setAttrib(elm, 'height', height, true);
				dom.setStyle(elm, 'height', '');
	 		}

			if (background != '')
				elm.style.backgroundImage = "url('" + background + "')";
			else
				elm.style.backgroundImage = '';

			if (bordercolor != "") {
				elm.style.borderColor = bordercolor;
				elm.style.borderStyle = elm.style.borderStyle == "" ? "solid" : elm.style.borderStyle;
				elm.style.borderWidth = border == "" ? "1px" : border;
			} else
				elm.style.borderColor = '';

			elm.style.backgroundColor = bgcolor;
			//elm.style.height = yeagerCE_getCSSSize(height);

			inst.addVisual();

			inst.nodeChanged();
			inst.execCommand('mceEndUndoLevel');

			// Repaint if dimensions changed
			if (formObj.width.value != orgTableWidth || formObj.height.value != orgTableHeight)
				inst.execCommand('mceRepaint');

			$K.windows['wid_'+winID].remove();
			return;
		}

		// Create new table
		html += '<table';

		//html += yeagerCE_makeAttrib('id', id);
		html += yeagerCE_makeAttrib('border', border, formObj);
		html += yeagerCE_makeAttrib('cellpadding', cellpadding, formObj);
		html += yeagerCE_makeAttrib('cellspacing', cellspacing, formObj);
		if (width) {
			style += 'width:'+width+'px;';
		}
		if (height) {
			style += 'height:'+height+'px;';
		}
		//html += yeagerCE_makeAttrib('width', width, formObj);
		//html += yeagerCE_makeAttrib('height', height, formObj);
		html += yeagerCE_makeAttrib('align', align, formObj);
		html += yeagerCE_makeAttrib('class', className, formObj);
		html += yeagerCE_makeAttrib('style', style, formObj);
		html += '>';

		for (var y=0; y<rows; y++) {
			html += "<tr>";
			for (var x=0; x<cols; x++) {
				if (!tinymce.isIE)
					html += '<td><br mce_bogus="1"/></td>';
				else
					html += '<td></td>';
			}
			html += "</tr>";
		}

		html += "</table>";

		inst.execCommand('mceBeginUndoLevel');
		inst.execCommand('mceInsertContent', false, html);
		inst.addVisual();
		inst.execCommand('mceEndUndoLevel');

		$K.windows['wid_'+winID].remove();
	}

	yeagerCE_saveSelection();

	var formObj = document.forms['wid_'+winID+'_linkForm'];
	var inst = tinyMCE.activeEditor, dom = inst.dom;
	var elm = dom.getParent(inst.selection.getNode(), 'table');
	var action = elm ? 'update' : 'insert';

	if (action == 'update') {
		// Update form

		var rowsAr = elm.rows;
		var cols = 0;
		for (var i=0; i<rowsAr.length; i++)
			if (rowsAr[i].cells.length > cols)
				cols = rowsAr[i].cells.length;

		rows = rowsAr.length;

		st = dom.parseStyle(dom.getAttrib(elm, "style"));
		border = yeagerCE_trimSize(yeagerCE_getStyle(elm, 'border', 'borderWidth'));
		cellpadding = dom.getAttrib(elm, 'cellpadding', "");
		cellspacing = dom.getAttrib(elm, 'cellspacing', "");
		width = yeagerCE_trimSize(yeagerCE_getStyle(elm, 'width', 'width'));
		height = yeagerCE_trimSize(yeagerCE_getStyle(elm, 'height', 'height'));
		bordercolor = yeagerCE_convertRGBToHex(yeagerCE_getStyle(elm, 'bordercolor', 'borderLeftColor'));
		bgcolor = yeagerCE_convertRGBToHex(yeagerCE_getStyle(elm, 'bgcolor', 'backgroundColor'));
		align = dom.getAttrib(elm, 'align');
		frame = dom.getAttrib(elm, 'frame');
		rules = dom.getAttrib(elm, 'rules');
		className = tinymce.trim(dom.getAttrib(elm, 'class').replace(/mceItem.+/g, ''));
		id = dom.getAttrib(elm, 'id');
		summary = dom.getAttrib(elm, 'summary');
		style = dom.serializeStyle(st);
		dir = dom.getAttrib(elm, 'dir');
		lang = dom.getAttrib(elm, 'lang');
		background = yeagerCE_getStyle(elm, 'background', 'backgroundImage').replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");

		orgTableWidth = width;
		orgTableHeight = height;

		$K.yg_dropdownSelect(formObj.dd_align, false, align, true);

		formObj.cols.value = cols;
		formObj.rows.value = rows;
		formObj.border.value = border;
		formObj.cellpadding.value = cellpadding;
		formObj.cellspacing.value = cellspacing;
		formObj.width.value = width;
		formObj.height.value = height;
		formObj.style.value = style;
		if (className!='') $K.yg_dropdownSelect(formObj.dd_class, false, className, true);


		// Disable some fields in update mode
		if (action == 'update') {
			formObj.cols.disabled = true;
			formObj.rows.disabled = true;
		}

	}

}


/**
 * Initializes the contenteditor table merge-cells window
 * @param { String } [winID] The window-id
 * @function
 * @name $K.yg_initCEMergeCells
 */
$K.yg_initCEMergeCells = function( winID ) {

	yeagerCE_saveSelection();

	var f = document.forms['wid_'+winID+'_linkForm'], v;

	if (window.sp != undefined) {
		f.numcols.value = window.sp.colspan;
		f.numrows.value = window.sp.rowspan;
	}

	$K.windows['wid_'+winID].submitCE_mergecells = function() {
		var args = [], f = document.forms['wid_'+winID+'_linkForm'];

		yeagerCE_restoreSelection();

		if (!AutoValidator.validate(f)) {
			$K.yg_promptbox($K.TXT('TXT_INVALID_DATA'), $K.TXT('TXT_INVALID_DATA'), 'alert');
			return false;
		}

		args['numcols'] = f.numcols.value;
		args['numrows'] = f.numrows.value;

		tinyMCE.execCommand("mceTableMergeCells", false, args);
		$K.windows['wid_'+winID].remove();
	}

}


/**
 * Initializes the contenteditor paste-from-word window
 * @param { String } [winID] The window-id
 * @function
 * @name $K.yg_initCEPasteWord
 */
$K.yg_initCEPasteWord = function( winID ) {

	$K.windows['wid_'+winID].onResize = function() {
		var wHeight = this.boxheight - 109;
		var elm = document.getElementById('frmData');
		if (elm) {
			elm.style.height = Math.abs(wHeight) + 'px';
		}
		$K.windows['wid_'+winID].refresh();
	}

	$K.windows['wid_'+winID].submitCE_pasteWord = function() {

		yeagerCE_restoreSelection();

		var html = document.getElementById("frmData").contentWindow.document.body.innerHTML;

		if (html != '') {
			tinyMCE.execCommand('mcePasteWord', false, html);
		}
		$K.windows['wid_'+winID].remove();
	}

	yeagerCE_saveSelection();
}


/**
 * Initializes the iframe of the contenteditor
 * @param { Element } [doc] The document-object of the iframe
 * @function
 * @name $K.yg_initIframe
 */
$K.yg_initIframe = function (doc) {
	var dir = tinyMCE.activeEditor.settings.directionality;

	doc.body.dir = dir;

	// Remove Gecko spellchecking
	if (tinymce.isGecko)
		doc.body.spellcheck = tinyMCE.activeEditor.getParam("gecko_spellcheck");

	var wHeight = $('frmData').up('.ywindow').clientHeight - 109;
	var elm = document.getElementById('frmData');
	if (elm) {
		elm.style.height = Math.abs(wHeight) + 'px';
	}
}



/**
 * Initializes the table-cell window
 * @param { String } [winID] The window-id
 * @function
 * @name $K.yg_initCETableCell
 */
$K.yg_initCETableCell = function( winID ) {

	yeagerCE_saveSelection();

	ed = tinyMCE.activeEditor;

	var inst = ed;
	var tdElm = ed.dom.getParent(ed.selection.getNode(), "td,th");
	var formObj = document.forms['wid_'+winID+'_linkForm'];
	var st = ed.dom.parseStyle(ed.dom.getAttrib(tdElm, "style"));

	// Get table cell data
	var celltype = tdElm.nodeName.toLowerCase();
	var align = ed.dom.getAttrib(tdElm, 'align');
	var valign = ed.dom.getAttrib(tdElm, 'valign');
	var width = yeagerCE_trimSize(yeagerCE_getStyle(tdElm, 'width', 'width'));
	var height = yeagerCE_trimSize(yeagerCE_getStyle(tdElm, 'height', 'height'));
	var bordercolor = yeagerCE_convertRGBToHex(yeagerCE_getStyle(tdElm, 'bordercolor', 'borderLeftColor'));
	var bgcolor = yeagerCE_convertRGBToHex(yeagerCE_getStyle(tdElm, 'bgcolor', 'backgroundColor'));
	var className = ed.dom.getAttrib(tdElm, 'class');
	var backgroundimage = yeagerCE_getStyle(tdElm, 'background', 'backgroundImage').replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");;
	var id = ed.dom.getAttrib(tdElm, 'id');
	var lang = ed.dom.getAttrib(tdElm, 'lang');
	var dir = ed.dom.getAttrib(tdElm, 'dir');
	var scope = ed.dom.getAttrib(tdElm, 'scope');
	var allCustomClasses = ed.dom.getClasses();

	for (var i=0;i<allCustomClasses.length;i++) {
		$K.yg_dropdownInsert( $(formObj.dd_class).up('.dropdownbox'), allCustomClasses[i]['class'], allCustomClasses[i]['class'], false, 'bottom');
	}

	formObj.width.value = width;
	formObj.height.value = height;


	if (align!='') $K.yg_dropdownSelect(formObj.dd_align, false, align, true);
	if (valign!='') $K.yg_dropdownSelect(formObj.dd_valign, false, valign, true);
	if (celltype!='') $K.yg_dropdownSelect(formObj.dd_celltype, false, celltype, true);
	if (className!='') $K.yg_dropdownSelect(formObj.dd_class, false, className, true);

	$K.windows['wid_'+winID].submitCE_tableCell = function() {

		var ed = tinyMCE.activeEditor;
		var el, inst = ed, tdElm, trElm, tableElm, formObj = document.forms['wid_'+winID+'_linkForm'];

		yeagerCE_restoreSelection();

		el = ed.selection.getNode();
		tdElm = ed.dom.getParent(el, "td,th");
		trElm = ed.dom.getParent(el, "tr");
		tableElm = ed.dom.getParent(el, "table");

		ed.execCommand('mceBeginUndoLevel');

		var nextCell = function(elm) {
			while ((elm = elm.nextSibling) != null) {
				if (elm.nodeName == "TD" || elm.nodeName == "TH")
					return elm;
			}
			return null;
		}

		var updateCell = function(td, skip_id) {
			var inst = ed;
			var formObj = document.forms['wid_'+winID+'_linkForm'];
			var curCellType = td.nodeName.toLowerCase();
			var celltype = formObj.dd_celltypes.value;
			var doc = inst.getDoc();
			var dom = ed.dom;

			if (!skip_id)
				td.setAttribute('id', formObj.id.value);

			td.setAttribute('align', formObj.dd_aligns.value);
			td.setAttribute('vAlign', formObj.dd_valigns.value);
			td.setAttribute('class', formObj.dd_classes.value);
			td.setAttribute('style', ed.dom.serializeStyle(ed.dom.parseStyle(formObj.style.value)));

			// Clear deprecated attributes
			ed.dom.setAttrib(td, 'width', '');
			ed.dom.setAttrib(td, 'height', '');
			ed.dom.setAttrib(td, 'bgColor', '');
			ed.dom.setAttrib(td, 'borderColor', '');
			ed.dom.setAttrib(td, 'background', '');

			// Set styles
			td.style.width = yeagerCE_getCSSSize(formObj.width.value);
			td.style.height = yeagerCE_getCSSSize(formObj.height.value);

			if (curCellType != celltype) {
				// changing to a different node type
				var newCell = doc.createElement(celltype);

				for (var c=0; c<td.childNodes.length; c++)
					newCell.appendChild(td.childNodes[c].cloneNode(1));

				for (var a=0; a<td.attributes.length; a++)
					ed.dom.setAttrib(newCell, td.attributes[a].name, ed.dom.getAttrib(td, td.attributes[a].name));

				td.parentNode.replaceChild(newCell, td);
				td = newCell;
			}

			dom.setAttrib(td, 'style', dom.serializeStyle(dom.parseStyle(td.style.cssText)));

			return td;
		}

		switch (formObj.dd_actions.value) {
			case "cell":
				var celltype = formObj.dd_celltypes.value;

				function doUpdate(s) {
					if (s) {
						updateCell(tdElm);

						ed.addVisual();
						ed.nodeChanged();
						inst.execCommand('mceEndUndoLevel');
						tinyMCEPopup.close();
					}
				};

				updateCell(tdElm);
				break;

			case "row":
				var cell = trElm.firstChild;

				if (cell.nodeName != "TD" && cell.nodeName != "TH")
					cell = nextCell(cell);

				do {
					cell = updateCell(cell, true);
				} while ((cell = nextCell(cell)) != null);

				break;

			case "all":
				var rows = tableElm.getElementsByTagName("tr");

				for (var i=0; i<rows.length; i++) {
					var cell = rows[i].firstChild;

					if (cell.nodeName != "TD" && cell.nodeName != "TH")
						cell = nextCell(cell);

					do {
						cell = updateCell(cell, true);
					} while ((cell = nextCell(cell)) != null);
				}

				break;
		}

		ed.addVisual();
		ed.nodeChanged();
		inst.execCommand('mceEndUndoLevel');

		$K.windows['wid_'+winID].remove();
	}

}


/**
 * Initializes the table-row window
 * @param { String } [winID] The window-id
 * @function
 * @name $K.yg_initCETableRow
 */
$K.yg_initCETableRow = function( winID ) {

	var updateRow = function(tr_elm, skip_id, skip_parent) {
		var inst = tinyMCE.activeEditor;
		var formObj = document.forms['wid_'+winID+'_linkForm'];
		var dom = inst.dom;
		var curRowType = tr_elm.parentNode.nodeName.toLowerCase();
		var rowtype = formObj.dd_rowtypes.value;
		var doc = inst.getDoc();

		// Update row element
		if (!skip_id)
			tr_elm.setAttribute('id', formObj.id.value);

		tr_elm.setAttribute('align', formObj.dd_aligns.value);
		tr_elm.setAttribute('vAlign', formObj.dd_valigns.value);
		tr_elm.setAttribute('class', formObj.dd_classes.value);

		// Clear deprecated attributes
		tr_elm.setAttribute('background', '');
		tr_elm.setAttribute('bgColor', '');
		tr_elm.setAttribute('height', '');

		// Set styles
		tr_elm.style.height = yeagerCE_getCSSSize(formObj.height.value);

		// Setup new rowtype
		if (curRowType != rowtype && !skip_parent) {
			// first, clone the node we are working on
			var newRow = tr_elm.cloneNode(1);

			// next, find the parent of its new destination (creating it if necessary)
			var theTable = dom.getParent(tr_elm, "table");
			var dest = rowtype;
			var newParent = null;
			for (var i = 0; i < theTable.childNodes.length; i++) {
				if (theTable.childNodes[i].nodeName.toLowerCase() == dest)
					newParent = theTable.childNodes[i];
			}

			if (newParent == null) {
				newParent = doc.createElement(dest);

				if (dest == "thead") {
					if (theTable.firstChild.nodeName == 'CAPTION')
						inst.dom.insertAfter(newParent, theTable.firstChild);
					else
						theTable.insertBefore(newParent, theTable.firstChild);
				} else
					theTable.appendChild(newParent);
			}

			// append the row to the new parent
			newParent.appendChild(newRow);

			// remove the original
			tr_elm.parentNode.removeChild(tr_elm);

			// set tr_elm to the new node
			tr_elm = newRow;
		}

		dom.setAttrib(tr_elm, 'style', dom.serializeStyle(dom.parseStyle(tr_elm.style.cssText)));
	}

	yeagerCE_saveSelection();

	var inst = tinyMCE.activeEditor;
	var dom = inst.dom;
	var trElm = dom.getParent(inst.selection.getNode(), "tr");
	var formObj = document.forms['wid_'+winID+'_linkForm'];
	var st = dom.parseStyle(dom.getAttrib(trElm, "style"));

	// Get table row data
	var rowtype = trElm.parentNode.nodeName.toLowerCase();
	var align = dom.getAttrib(trElm, 'align');
	var valign = dom.getAttrib(trElm, 'valign');
	var height = yeagerCE_trimSize(yeagerCE_getStyle(trElm, 'height', 'height'));
	var className = dom.getAttrib(trElm, 'class');
	var bgcolor = yeagerCE_convertRGBToHex(yeagerCE_getStyle(trElm, 'bgcolor', 'backgroundColor'));
	var backgroundimage = yeagerCE_getStyle(trElm, 'background', 'backgroundImage').replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");;
	var id = dom.getAttrib(trElm, 'id');
	var lang = dom.getAttrib(trElm, 'lang');
	var dir = dom.getAttrib(trElm, 'dir');
	var allCustomClasses = dom.getClasses();

	for (var i=0;i<allCustomClasses.length;i++) {
		$K.yg_dropdownInsert( $(formObj.dd_class).up('.dropdownbox'), allCustomClasses[i]['class'], allCustomClasses[i]['class'], false, 'bottom');
	}

	$K.windows['wid_'+winID].submitCE_tableRow = function() {

		var inst = tinyMCE.activeEditor, dom = inst.dom, trElm, tableElm, formObj = document.forms['wid_'+winID+'_linkForm'];
		var action = formObj.dd_actions.value;

		yeagerCE_restoreSelection();

		trElm = dom.getParent(inst.selection.getNode(), "tr");
		tableElm = dom.getParent(inst.selection.getNode(), "table");

		inst.execCommand('mceBeginUndoLevel');

		switch (action) {
			case "row":
				updateRow(trElm);
				break;

			case "all":
				var rows = tableElm.getElementsByTagName("tr");

				for (var i=0; i<rows.length; i++)
					updateRow(rows[i], true);

				break;

			case "odd":
			case "even":
				var rows = tableElm.getElementsByTagName("tr");

				for (var i=0; i<rows.length; i++) {
					if ((i % 2 == 0 && action == "odd") || (i % 2 != 0 && action == "even"))
						updateRow(rows[i], true, true);
				}
				break;
		}

		inst.addVisual();
		inst.nodeChanged();
		inst.execCommand('mceEndUndoLevel');

		$K.windows['wid_'+winID].remove();
	}

	// Setup form
	formObj.height.value = height;
	formObj.style.value = dom.serializeStyle(st);

	if (align!='') $K.yg_dropdownSelect(formObj.dd_align, false, align, true);
	if (valign!='') $K.yg_dropdownSelect(formObj.dd_valign, false, valign, true);
	if (rowtype!='') $K.yg_dropdownSelect(formObj.dd_rowtypes, false, rowtype, true);
	if (className!='') $K.yg_dropdownSelect(formObj.dd_class, false, className, true);
}

/**
 * Handler for the Contenteditor save-function
 * @param { String } [editorReference] Reference to the current TinyMCE editor
 * @function
 * @name $K.yg_saveContenteditor
 */
$K.yg_saveContenteditor = function(editorReference) {
	var editorWinID = $K.windows[$(editorReference.editorId).up('.ywindow').id].num;
	var sourceFormfield = $K.windows[$(editorReference.editorId).up('.ywindow').id].loadparams['formfield'];

	$K.yg_submitContentEditor(editorWinID, sourceFormfield, true);
	return true;
}
