/**
 * @fileoverview Provides all functions for controlling and maintaining content-controls (aka entrymasks) and reusable contentblocks
 */


/**
 * Opens/closes an entrymask
 *
 * @param { Object } [obj] The entrymask-object which should be opened/closed
 * @function
 * @name $K.yg_maskSwap
 */
$K.yg_maskSwap = function(obj) {
	tmpobjhead=obj.down('.emheader').down('a');
	tmpobjcnt=obj.down('.emcontentouter');
	tmpobjerror=tmpobjhead.next(2);
	if (tmpobjcnt.getStyle('display')=="none") {
		tmpobjcnt.setStyle({display:'block'});
		if (obj.hasClassName('emerror')) {
			tmpobjerror.setStyle({display:'block'});
			obj.removeClassName('emcollapsed');
		}
		tmpobjhead.removeClassName('closed');
		tmpobjhead.addClassName('opened');
		tmpobjhead.up().removeClassName('closed');
		obj.select('textarea').each(function(textareaItem) {
			$K.yg_setTextareaAutogrow( textareaItem.identify() );
		});
	} else {
		if (obj.hasClassName('emerror')) {
			tmpobjerror.setStyle({display:'none'});
			obj.addClassName('emcollapsed');
		}
		tmpobjcnt.setStyle({display:'none'});
		tmpobjhead.removeClassName('opened');
		tmpobjhead.addClassName('closed');
		tmpobjhead.up().addClassName('closed');
	}

	$K.windows[obj.up('.ywindow').id].refresh(obj);

	if (obj.up('.cbcontent')) {
		$K.yg_showActions(obj.up('.cbcontent').down());
	}

	$K.yg_showActions(tmpobjhead.up());
}


/**
 * Opens/closes a contentblock
 *
 * @param { Object } [obj] The contentblock-object which should be opened/closed
 * @function
 * @name $K.yg_cblockSwap
 */
$K.yg_cblockSwap = function(obj) {
	obj = $(obj);
	tmpobjhead = $( obj.id.replace(/_selector/,'_cbheader') );
	tmpobjcnt = $( obj.id.replace(/_selector/,'_cbcontentinner') );
	tmpobjerror=tmpobjhead.next(2);
	if (tmpobjcnt.getStyle('display')=="none") {
		tmpobjcnt.setStyle({display:'block'});
		if (obj.hasClassName('cberror')) tmpobjerror.setStyle({display:'block'});
		tmpobjhead.removeClassName('closed');
		tmpobjhead.addClassName('opened');
		tmpobjhead.up().removeClassName('closed');
		obj.select('textarea').each(function(textareaItem) {
			$K.yg_setTextareaAutogrow( textareaItem.identify() );
		});
	} else {
		if (obj.hasClassName('cberror')) tmpobjerror.setStyle({display:'none'});
		tmpobjcnt.setStyle({display:'none'});
		tmpobjhead.removeClassName('opened');
		tmpobjhead.addClassName('closed');
		tmpobjhead.up().addClassName('closed');
	}
	$K.windows[obj.up('.ywindow').id].refresh(obj);
	$K.yg_showActions(tmpobjhead.up());
}


/**
 * Toggles the title-column of content controls on/off
 *
 * @param { Object } [obj] Regarding object which contains all elements.
 * @function
 * @name $K.yg_toggleElemTitles
 */
$K.yg_toggleElemTitles = function(obj) {
	$K.log(obj, $K.Log.INFO);
	obj=$(obj);
	if (obj.hasClassName('noelemtitles')) {
		obj.removeClassName('noelemtitles');
	} else {
		obj.addClassName('noelemtitles');
	}
	$K.windows[obj.up('.ywindow').id].refresh(obj);
	$K.yg_showActions($(obj));
}

/**
 * Highlights/blurs control element
 *
 * @param { Object } [obj] Regarding iPanel-div.
 * @param { String } [action] Defines the action. May be set to over or out.
 * @function
 * @name $K.yg_maskHighlight
 */
$K.yg_maskHighlight = function(obj,action) {
	if (action=="over") {
		$(obj).addClassName('emcontentinnerhover');
	} else {
		if ($K.actionhover!=true) $(obj).removeClassName('emcontentinnerhover');
	}
}


/**
 * Switches the control into editmode
 *
 * @param { Object } [obj] Regarding obj (div)
 * @param { Event } [event] event
 * @function
 * @name $K.yg_goEditMode
 */
$K.yg_goEditMode = function(obj, event) {
	if (event) Event.stop(event);
	if (Boolean(obj.readAttribute('yg_selectable'))) {

		if ( !(obj.hasClassName('emeditmode')) ) {
			obj.addClassName('emeditmode');
			$K.currentEditMode = obj;

			var textboxes = obj.select('textarea');

			textboxes.each(function(item){
				if (item && item.autogrow && item.autogrow.dummy) {
					item.autogrow.dummy.innerHTML = '';
					var newWidth = item.getWidth() + 'px';
					item.autogrow.dummy.setStyle({width:newWidth});
					item.autogrow.checkExpand.defer();
				}
			});
		}
	}

	$K.yg_customAttributeHandler(obj);
	$K.windows[obj.up('.ywindow').id].refresh(obj);
}


/**
 * Closes editmode
 *
 * @param { Object } [obj] Regarding obj (div)
 * @function
 * @name $K.yg_closeEditMode
 */
$K.yg_closeEditMode = function(obj) {
	$K.yg_fireLateOnChange();

	if (Boolean(obj.readAttribute('yg_selectable'))) {
		obj.removeClassName('emeditmode');
	}
	$K.yg_customAttributeHandler(obj);
	$K.windows[obj.up('.ywindow').id].refresh(obj);
}


/**
 * Clears the selected control
 *
 * @param { Element } [which] Element to edit
 * @param { String } [objectid] ID of the formfield to edit
 * @param { Element } [formfield] Reference to the formfield to clear
 * @function
 * @name $K.yg_clearControl
 */
$K.yg_clearControl = function( which, objectid, formfield ) {
	which = $(which);
	var site = $K.windows[which.up('.ywindow').id].yg_id.split('-')[1];

	entrymask = false;
	if (which.up('.emcontainerfocus') || which.up('.emcontainer')) {
		// entrymask
		entrymask = true;
		parentobj = which.up('.maskedit');
		$K.yg_submitControl( which.up('.maskedit').down('.title_txt'), formfield );
	} else {
		// property
		parentobj = which.up('.mk_property');
		var targetField = parentobj.down('input[type=hidden]');
		var infoContainer = parentobj.down('span.title_txt');
		targetField.value = '';
		if (infoContainer) {
			infoContainer.writeAttribute('param01', '');
			infoContainer.writeAttribute('param02', '');
			infoContainer.writeAttribute('param03', '');
			infoContainer.writeAttribute('param04', '');
		}
		$K.yg_setObjectProperty( targetField );
	}
	if (formfield=='5') {
		parentobj.down('.title').previous().className = 'icn noicon';
	}
	if (parentobj.hasClassName('mk_file') || parentobj.down('.mk_file')) {
		// type file
		parentobj.down('.title_txt').innerHTML = $K.TXT('TXT_SELECTOR_FILE');
		parentobj.down('div.title').writeAttribute('onmouseover', '');
		if (parentobj.down('.filetype')) parentobj.down('.filetype').remove();
	} else if (parentobj.hasClassName('mk_link')) {
		// type link
		parentobj.writeAttribute('param01','');
		parentobj.writeAttribute('param02','');
		parentobj.writeAttribute('param03','');
		parentobj.writeAttribute('param04','');
		if (parentobj.down('.elemtitle').next()) {
			parentobj.down('.elemtitle').next().down().className = "icn noicon";
			parentobj.down('.title_txt').innerHTML = $K.TXT('TXT_SELECTOR_LINK');
			parentobj.down('.elemtitle').next().down().setStyle({display:'block'});
		}
	} else {
		// type tag or contentblock
		if (parentobj.down('.icn')) parentobj.down('.icn').className = 'icn noicon';
		if (parentobj.down('.filetype')) parentobj.down('.filetype').remove();
		if (parentobj.down().hasClassName('mk_link') || parentobj.hasClassName('mk_link')) emptyText = $K.TXT('TXT_SELECTOR_LINK');
		if (parentobj.down().hasClassName('mk_tag') || parentobj.hasClassName('mk_tag')) emptyText = $K.TXT('TXT_SELECTOR_TAG');
		if (parentobj.down().hasClassName('mk_cblock') || parentobj.hasClassName('mk_cblock')) emptyText = $K.TXT('TXT_SELECTOR_CBLOCK');
		if (parentobj.down().hasClassName('mk_page') || parentobj.hasClassName('mk_page')) emptyText = $K.TXT('TXT_SELECTOR_PAGE');
		if (parentobj.down().hasClassName('mk_file') || parentobj.hasClassName('mk_file')) emptyText = $K.TXT('TXT_SELECTOR_FILE');
		parentobj.down('.title_txt').innerHTML = emptyText;
	}

	$K.yg_fadeField( parentobj );
}


/**
 * Helper function used to clear (and submit) inputfields, textareas, checkboxes and wysiwyg (tinyMCE) controls
 *
 * @param { Element } [which] The relevant element.
 * @param { Integer } [formfieldtype] The type of the formfield (an Yeager-Type).
 * @function
 * @name yg_submitControl
 */
$K.yg_submitControl = function( which, formfieldtype ) {

	//var id = which.up('.emcontainer.mk_cblock').id.split('_')[3];
	var id = which.up('.emcontainer').id.split('_')[3];
	var coid = which.up('li').getAttribute('yg_id').split('-')[0];
	var page = $K.windows[which.up('.ywindow').id].yg_id.split('-')[0];
	var site = $K.windows[which.up('.ywindow').id].yg_id.split('-')[1];
	var objecttype = $K.windows[which.up('.ywindow').id].yg_type;
	var formfieldid = which.up('.maskedit').getAttribute('yg_id').split('-')[0];
	var all_data = {
		id: id,
		coid: coid,
		page: page,
		site: site,
		objecttype: objecttype,
		formfieldid: formfieldid
	};

	all_data[formfieldid+'-VALUE01'] = ' ';
	all_data[formfieldid+'-VALUE02'] = ' ';
	all_data[formfieldid+'-VALUE03'] = ' ';
	all_data[formfieldid+'-VALUE04'] = ' ';
	all_data[formfieldid+'-VALUE05'] = ' ';
	all_data[formfieldid+'-VALUE06'] = ' ';
	all_data[formfieldid+'-VALUE07'] = ' ';
	all_data[formfieldid+'-VALUE08'] = ' ';

	var winID = which.up('.ywindow').id;
	var backendAction = 'savePageEntrymask';

	if ($K.windows[winID].tab == 'EXTENSIONS') {
		backendAction = 'saveExtensionProperties';
			all_data.id = which.up('.mk_extension').readAttribute('yg_id').split('-')[0];
	}

	var data = Array ( 'noevent', {yg_property: backendAction, params: {
		allData: Object.toJSON(all_data)
	} } );
	$K.yg_AjaxCallback( data, backendAction );

}


/**
 * Helper function used to edit (and submit) changes in inputfields, textareas, checkboxes and wysiwyg (tinyMCE) controls
 *
 * @param { Element } [which] The relevant element.
 * @param { Integer } [formfieldtype] The type of the formfield (yeager type).
 * @param { Boolean } [dragndrop] True if action was triggered by dragndrop.
 * @function
 * @name $K.yg_editControl
 */
$K.yg_editControl = function( which, formfieldtype, dragndrop, data ) {

	which = $(which);
	if (!(which)) return;

	if (which.up('.emcontainer.mk_cblock')) {
		var id = which.up('.emcontainer.mk_cblock').id.split('_')[3];
	} else if (which.up('.mk_cblock')) {
		var id = which.up('.mk_cblock').id.split('_')[3];
	} else if (which.up('.emcontainer.mk_extension')) {
		var id = which.up('.emcontainer.mk_extension').id.split('_')[3];
	}
	var coid = which.up('li').getAttribute('yg_id').split('-')[0];
	var page = $K.windows[which.up('.ywindow').id].yg_id.split('-')[0];
	var site = $K.windows[which.up('.ywindow').id].yg_id.split('-')[1];
	var objecttype = $K.windows[which.up('.ywindow').id].yg_type;
	var formfieldid = which.up('.maskedit').getAttribute('yg_id').split('-')[0];
	var havedata = false;
	var all_data = {
		id: id,
		coid: coid,
		page: page,
		site: site,
		objecttype: objecttype,
		formfieldid: formfieldid
	};

	$K.log( 'which: ', which, $K.Log.INFO );
	$K.log( 'id: ', id, $K.Log.INFO );
	$K.log( 'coid: ', coid, $K.Log.INFO );
	$K.log( 'site: ', site, $K.Log.INFO );
	$K.log( 'objecttype: ', objecttype, $K.Log.INFO );
	$K.log( 'formfieldid: ', formfieldid, $K.Log.INFO );

	switch (parseInt(formfieldtype)) {
		// Single line text
		case 1:
			$K.log( 'Single Line Text', $K.Log.INFO );
			all_data[formfieldid+'-VALUE01'] = which.value;
			//which.up('.maskedit').previous().down('.elemcontent').update(which.value);
			havedata = true;
			break;
		// Multi line text
		case 2:
			$K.log( 'Multi Line Text', $K.Log.INFO );
			all_data[formfieldid+'-VALUE01'] = which.value;
			//which.up('.maskedit').previous().down('.elemcontent').update( which.value.replace(/\n/g, '<br />') );
			havedata = true;
			break;
		// WYSIWYG
		case 3:
			$K.log( 'WYSIWYG', $K.Log.INFO );
			if (which.realHTML) {
				all_data[formfieldid+'-VALUE01'] = which.realHTML;
			} else {
				all_data[formfieldid+'-VALUE01'] = which.innerHTML;
			}
			//which.up('.maskedit').previous().down('.elemcontent').update( which.innerHTML );
			havedata = true;
			break;
		// Checkbox
		case 4:
			$K.log( 'Checkbox', $K.Log.INFO );
			all_data[formfieldid+'-VALUE01'] = which.down('input').value;
			/*if (which.down('input').value == '1') {
				which.up('.maskedit').previous().down('.elemcontent').down('.checkboxdisplay').update( $K.TXT('TXT_CHECKBOX_ON') );
			} else {
				which.up('.maskedit').previous().down('.elemcontent').down('.checkboxdisplay').update( $K.TXT('TXT_CHECKBOX_OFF') );
			}*/
			havedata = true;
			break;
		// Link
		case 5:
			$K.log( 'Link', $K.Log.INFO );
			if (data && data['objecttype'] && data['objecttype'] == 'page') {

				$(which).up('.selectionmarker').down('.icn').className = 'icn iconpage';
				//which.up('.maskedit').previous().down('.elemcontent').down().show();
				//which.up('.maskedit').previous().down('.elemcontent').down('.title').setStyle({marginLeft:'',paddingLeft:''});
				which.up('.title').previous().show();

				if (data['yg_id'] && !data['href']) {
					all_data[formfieldid+'-VALUE01'] = $K.appdir+'page/'+ data['yg_id'].split('-')[1] +'/'+ data['yg_id'].split('-')[0];
					all_data[formfieldid+'-VALUE02'] = '';
					all_data[formfieldid+'-VALUE03'] = '';
					all_data[formfieldid+'-VALUE04'] = '';
				} else {
					all_data[formfieldid+'-VALUE01'] = data['href'];									// HREF
					all_data[formfieldid+'-VALUE02'] = data['target'];									// TARGET
					all_data[formfieldid+'-VALUE03'] = '';												// TEXT
					all_data[formfieldid+'-VALUE04'] = data['onclick'];								// ONCLICK
				}

			} else if (data && data['objecttype'] && data['objecttype'] == 'link') {

				$(which).up('.selectionmarker').down('.icn').className = 'icn iconlink';
				//which.up('.maskedit').previous().down('.elemcontent').down().show();
				//which.up('.maskedit').previous().down('.elemcontent').down('.title').setStyle({marginLeft:'',paddingLeft:''});
				which.up('.title').previous().show();

				all_data[formfieldid+'-VALUE01'] = data['href'];		 								// HREF
				all_data[formfieldid+'-VALUE02'] = data['target'];										// TARGET
				all_data[formfieldid+'-VALUE03'] = '';													// TEXT
				all_data[formfieldid+'-VALUE04'] = data['onclick'];									// ONCLICK

			} else if (data && data['objecttype'] && data['objecttype'] == 'email') {

				$(which).up('.selectionmarker').down('.icn').className = 'icn iconemail';
				//which.up('.maskedit').previous().down('.elemcontent').down().show();
				//which.up('.maskedit').previous().down('.elemcontent').down('.title').setStyle({marginLeft:'',paddingLeft:''});
				which.up('.title').previous().show();

				all_data[formfieldid+'-VALUE01'] = data['href'];		 								// HREF
				all_data[formfieldid+'-VALUE02'] = '';													// TARGET
				all_data[formfieldid+'-VALUE03'] = '';													// TEXT
				all_data[formfieldid+'-VALUE04'] = '';													// ONCLICK

			} else if (data && data['objecttype'] && data['objecttype'] == 'file') {

				which.up('.title').previous().hide();

				if (data['yg_id'] && !data['href']) {
					all_data[formfieldid+'-VALUE01'] = $K.appdir+'download/'+ data['yg_id'].split('-')[0];	// HREF
				} else {
					all_data[formfieldid+'-VALUE01'] = data['href'];										// HREF
				}
				all_data[formfieldid+'-VALUE02'] = '';												// TARGET
				all_data[formfieldid+'-VALUE03'] = '';												// TEXT
				all_data[formfieldid+'-VALUE04'] = '';												// ONCLICK

				if ( data['filecolor'].strip().length &&
					 data['filetype'].strip().length &&
					 data['title'].strip().length ) {
					data['title'] = '<span class="filetype '+data['filecolor']+'" yg_type="'+$K.windows[which.up('.ywindow').id].yg_type+'" yg_id="'+$K.windows[which.up('.ywindow').id].yg_id+'" yg_property="type">'+data['filetype']+'</span>'+data['title'];
				}

			}

			if ((!data['title']) || (data['title'] == '')) {
				data['title'] = $K.TXT('TXT_SELECTOR_LINK');
			}

			// here:
			//which.up('.maskedit').previous().down('.elemcontent').down('.title').removeClassName('file');
			//which.up('.maskedit').previous().down('.elemcontent').down('.titlecnt').update(data['title']);
			which.update(data['title']);

			which.up('.maskedit').writeAttribute('param01', all_data[formfieldid+'-VALUE01']);			// HREF
			which.up('.maskedit').writeAttribute('param02', all_data[formfieldid+'-VALUE02']);			// TARGET
			which.up('.maskedit').writeAttribute('param03', all_data[formfieldid+'-VALUE03']);			// TEXT
			which.up('.maskedit').writeAttribute('param04', all_data[formfieldid+'-VALUE04']);			// ONCLICK
			havedata = true;
			break;
		// File
		case 6:
			$K.log( 'File', $K.Log.INFO );
			if (data.title.toLowerCase().startsWith('<span class="')) {
				which.update(data.title);
			} else {
				which.update('<span class="filetype ' + data.filecolor + '">' + data.filetype + '</span>' + data.title);
			}
			which.up('div.title').writeAttribute('onmouseover', '$K.yg_hoverFileHint(\''+data['yg_id'].split('-')[0]+'\', event);');
			all_data[formfieldid+'-VALUE01'] = data['yg_id'].split('-')[0];
			havedata = true;
			break;
		// Contentblock
		case 7:
			$K.log( 'Contentblock', $K.Log.INFO );
			$(which).innerHTML = data["title"];
			//$(which).up('.maskedit').previous().down('.elemcontent').down('.titlecnt').update(data["title"]);
			$(which).up('.selectionmarker').down('.icn').className = 'icn iconcblock';
			all_data[formfieldid+'-VALUE01'] = data['yg_id'].split('-')[0];
			havedata = true;
			break;
		// Tag
		case 8:
			$K.log( 'Tag', $K.Log.INFO );
			$(which).innerHTML = data["title"];
			//$(which).up('.maskedit').previous().down('.elemcontent').down('.titlecnt').update(data["title"]);
			$(which).up('.selectionmarker').down('.icn').className = 'icn icontag';
			all_data[formfieldid+'-VALUE01'] = data['yg_id'].split('-')[0];
			havedata = true;
			break;
		// List
		case 9:
			$K.log( 'List', $K.Log.INFO );
			all_data[formfieldid+'-VALUE01'] = which.value;
			//which.up('.maskedit').previous().down('.elemcontent').update(which.value);
			havedata = true;
			break;
		// Password
		case 10:
			$K.log( 'Password', $K.Log.INFO );
			all_data[formfieldid+'-VALUE01'] = which.value;
			//which.up('.maskedit').previous().down('.elemcontent').update(which.value);
			havedata = true;
			break;
		// Date
		case 11:
			$K.log( 'Date', $K.Log.INFO );
			all_data[formfieldid+'-VALUE01'] = which.value;
			//which.up('.maskedit').previous().down('.elemcontent').update(which.value);
			havedata = true;
			break;
		// Date & time
		case 12:
			$K.log( 'Datetime', $K.Log.INFO );
			var fieldId = which.id;
			if (fieldId.endsWith('2')) {
				fieldId = fieldId.substring(0, fieldId.length-1);
			}

			var newValue = $(fieldId).value + '||' + $(fieldId+'2').value;
			all_data[formfieldid+'-VALUE01'] = newValue;
			//which.up('.maskedit').previous().down('.elemcontent').update($(fieldId).value + ' ' + $(fieldId+'2').value);
			havedata = true;
			break;
		// Page
		case 15:
			$K.log( 'Page', $K.Log.INFO );
			$(which).innerHTML = data["title"];
			//$(which).up('.maskedit').previous().down('.elemcontent').down('.titlecnt').update(data["title"]);
			$(which).up('.selectionmarker').down('.icn').className = 'icn iconpage';
			all_data[formfieldid+'-VALUE01'] = data['yg_id'].split('-')[0];
			all_data[formfieldid+'-VALUE02'] = data['yg_id'].split('-')[1];
			havedata = true;
			break;
		// Filefolder
		case 16:
			$K.log( 'Filefolder', $K.Log.INFO );
			$(which).innerHTML = data["title"];
			//$(which).up('.maskedit').previous().down('.elemcontent').down('.titlecnt').update(data["title"]);
			$(which).up('.selectionmarker').down('.icn').className = 'icn iconfolder';
			all_data[formfieldid+'-VALUE01'] = data['yg_id'].split('-')[0];
			havedata = true;
			break;
		default:
			$K.warn('Not implemented yet! (Type: '+formfieldtype+')', $K.Log.INFO);
			break;
	}
	if (havedata) {
		$K.log( 'aquired data is:', all_data, $K.Log.INFO );

		var winID = which.up('.ywindow').id;
		var backendAction = 'savePageEntrymask';
		var ajaxParams = { };

		if ($K.windows[winID].tab == 'EXTENSIONS') {
			var yg_id = $K.windows[winID].yg_id;
			backendAction = 'saveExtensionProperties';
			ajaxParams.page = yg_id.split('-')[0];
			ajaxParams.site = yg_id.split('-')[1];
			all_data.id = which.up('.emcontentinner').id.split('_')[4].split('-')[1];
		}
		ajaxParams.allData = Object.toJSON(all_data);

		var data = Array ( 'noevent', {yg_property: backendAction, params: ajaxParams } );
		$K.yg_AjaxCallback( data, backendAction );
	}

}
