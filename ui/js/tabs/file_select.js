/**
 * Initializes the filechooser-dialog
 * @param { String } [openerReference] The reference to the opener
 * @param { String } [winID] The window-id
 * @param { String } [yg_id] The yg_id of the window
 * @function
 * @name $K.yg_initDlgFilechooser
 */
$K.yg_initDlgFilechooser = function( openerReference, winID, yg_id ) {

	// Add openerReference to the window-object and the window itself (in the DOM)
	$K.windows['wid_'+winID].openerReference = openerReference;
	$('wid_'+winID).openerReference = openerReference;

	if ($(openerReference)) {
		$K.windows['wid_'+winID].openerWin = $(openerReference).up('.ywindow').id;
		$('wid_'+winID).openerWin = $(openerReference).up('.ywindow').id;
	}

	// set yg_id
	if (yg_id) {
		$K.windows['wid_'+winID].yg_id = yg_id;
	}
	$K.yg_switchFileView('wid_'+winID, 'listview');
	if (!yg_id) {
		$K.yg_switchFileListView('wid_'+winID, 0.5);
	}

	$K.windows['wid_'+winID].addFileToFolder = function( fileData ) {

		var jsTemplateList = $K.windows['wid_'+winID].jsTemplateList;
		var jsTemplateThumb = $K.windows['wid_'+winID].jsTemplateThumb;

		if ($('wid_'+winID).hasClassName('filelist1') || $('wid_'+winID).hasClassName('filelist2') || $('wid_'+winID).hasClassName('filelist3')) {

			// Create template-chunk for listview
			var item_template = $K.yg_makeTemplate( jsTemplateList );

			var thumbnailData = $K.yg_makeThumb(fileData);

			// Fill template with variables
			var newFile = item_template.evaluate({
				item_objectid: fileData.objectid,
				item_thumbnail: thumbnailData,
				item_color: fileData.color,
				item_identifier: fileData.identifier,
				item_name: fileData.name,
				item_pname: fileData.pname,
				item_tags: fileData.tags,
				item_filesize: $K.yg_filesize( fileData.filesize ),
				item_ref_count: fileData.ref_count,
				item_datum: fileData.datum,
				item_timestamp: fileData.timestamp,
				item_uhrzeit: fileData.uhrzeit,
				item_uid: fileData.uid,
				item_username: fileData.username,
				item_filename: fileData.filename,
				item_width: fileData.width,
				item_height: fileData.height,
				win_no: winID
			});

			fileData.targetTable.down('tbody').insert({bottom:newFile});

		} else if ($('wid_'+winID).hasClassName('thumbview')) {

			// Create template-chunk for thumbview
			var item_template = $K.yg_makeTemplate( jsTemplateThumb );

			// Build imagedata
			var alignment = '';
			if (fileData.width) {
				var ratioPic = (fileData.width / fileData.height);
				if ( (ratioPic > (4/3)) || (fileData.thumb != '1') ) {
					alignment = 'x-scale';
				} else {
					alignment = 'y-scale';
				}
			}
			var full_filename = '';
			if (fileData.width && fileData.height) {
				full_filename = fileData.width + 'x' + fileData.height + ', ';
			}
			full_filename += $K.yg_filesize( fileData.filesize );


			// Build thumbnaildata
			var thumbnailData = '<div class="thumbcnt ';
			if (fileData.thumb != '1') thumbnailData += 'thumbcnt_nothumb';
			thumbnailData += '">';
			if ( (alignment=='x-scale') || (fileData.thumb != '1') ) thumbnailData += '<table cellspacing="0" cellpadding="0"><tr><td>';
			if (fileData.thumb == '1') {
				var randomSuffix = '?rnd=' + parseInt(Math.random()*10000000);
				thumbnailData += '<img class="noload" real_src="'+$K.appdir+'image/'+fileData.objectid+'/yg-thumb/'+randomSuffix+'" onload="$K.yg_setThumbPreviewLoaded(this);">';
			} else {
				thumbnailData += '<div class="noimg">?</div>';
			}
			if ( (alignment=='x-scale') || (fileData.thumb != '1') ) thumbnailData += '</td></tr></table>';
			thumbnailData += '</div>';

			// Fill template with variables
			var newFile = item_template.evaluate({
				item_objectid: fileData.objectid,
				item_thumbnail: thumbnailData,
				item_color: fileData.color,
				item_identifier: fileData.identifier,
				item_name: fileData.name,
				item_pname: fileData.pname,
				item_full_filename: full_filename,
				alignment: alignment,
				win_no: winID
			});

			// Find position to insert the new element (alphabetically)
			var itemsArray = new Array();
			var newItem = fileData.name.toLowerCase() + '<<>>' + 'file_' + winID + '_' + fileData.objectid;
			fileData.targetContainer.select('li').each(function(item){
				itemsArray.push( item.down('.filetitle').innerHTML.toLowerCase() + '<<>>' + item.id );
			});
			itemsArray.push( newItem );
			itemsArray.sort();

			var targetPosition = itemsArray.indexOf(newItem);
			targetPosition--;

			if (targetPosition<0) {
				// Insert at start of list
				fileData.targetContainer.insert({top:newFile});
			} else {
				// Insert after specific element
				var targetId = itemsArray[targetPosition].split('<<>>')[1];
				$(targetId).insert({after:newFile});
			}
		}

		// Update Filecount
		var oldFileCount = parseInt( $('wid_'+winID+'_objcnt').innerHTML );
		$('wid_'+winID+'_objcnt').update( ++oldFileCount );

		// Add to lookuptable
		$K.yg_customAttributeHandler( $('file_'+winID+'_'+fileData.objectid).up() );

		// Refresh Sortables
		$K.initSortable( 'wid_'+winID+'_'+$K.windows['wid_'+winID].yg_id.replace(/-file/,'')+'_files_list' );

		/*// Refresh TableKit when in listview
		if ($('wid_'+winID).hasClassName('filelist1') || $('wid_'+winID).hasClassName('filelist2') || $('wid_'+winID).hasClassName('filelist3')) {
			TableKit.Sortable.sort(null, $K.windows['wid_'+winID].loadparams.pagedir_orderby, $K.windows['wid_'+winID].loadparams.pagedir_orderdir);
			TableKit.Sortable.sort(null, $K.windows['wid_'+winID].loadparams.pagedir_orderby, $K.windows['wid_'+winID].loadparams.pagedir_orderdir);
		}

		// Update Scrollbars
		var isDialog = false;
		if ($('wid_'+winID).hasClassName('ydialog') && ($('wid_'+winID+'_column2'))) isDialog = true;
		if (isDialog) {
			$K.windows['wid_'+winID].refresh("col2");
		} else {
			$K.windows['wid_'+winID].refresh("col1");
		}*/
		$K.yg_refreshFileMgr('wid_'+winID);
	}

	// Submit func
	$K.windows['wid_'+winID].submit = function() {

		$K.yg_hideFileHint();
		if ($K.yg_hoverInt) {
			clearTimeout($K.yg_hoverInt);
		}
		$('yg_fileHint').hide();

		var focusobjs = $K.yg_getFocusObj($(this.id));

		// check if any file selected
		if (focusobjs.length > 0) {

			if (this.loadparams['type'] == 'property') {

				// property field
				var titlefield = $(this.loadparams['opener_reference']).down('.title_txt');
				titlefield.update( focusobjs[0].down('.filetitle').innerHTML );
				var valuefield = $(this.loadparams['opener_reference']).down('input[type=hidden]');

				// Check if favicon (use file-id if favicon)
				if (valuefield.readAttribute('yg_property') == 'favicon') {
					valuefield.value = focusobjs[0].readAttribute('yg_id').split('-')[0];
				} else {
					valuefield.value = focusobjs[0].readAttribute('yg_id').split('-')[0];
					//valuefield.value = '/image/' + focusobjs[0].readAttribute('pname');
				}

				if (!$(this.loadparams['opener_reference']).hasClassName('mk_noautosave')) {
					$K.yg_setObjectProperty(valuefield);
					if ($(this.loadparams['opener_reference']).up('.cntblock')) {
						$K.yg_fadeField($(this.loadparams['opener_reference']).up('.cntblock'));
					}
				}
			}

			if ((this.loadparams['type'] == 'formfield') && (this.loadparams['property'] == 'file')) {

				// formfield file
				var data = new Array();
				data['yg_id'] = focusobjs[0].yg_id;
				data['title'] = focusobjs[0].down('.filetitle').innerHTML;
				data['filecolor'] = focusobjs[0].down('.filetype').className.replace(/filetype/, '').strip();
				data['filetype'] = focusobjs[0].down('.filetype').innerHTML.strip();
				$K.yg_editControl( this.loadparams['opener_reference'] , '6', false, data );
				$K.yg_fadeField( $(this.loadparams['opener_reference']).up('.maskedit') );

			}

			if ((this.loadparams['type'] == 'formfield') && (this.loadparams['property'] == 'link')) {

				// formfield link
				document.forms[this.loadparams.opener_reference].file.value = focusobjs[0].yg_id;
				document.forms[this.loadparams.opener_reference].filecolor.value = focusobjs[0].down('.filetype').className.replace(/filetype/, '').strip();
				document.forms[this.loadparams.opener_reference].filetype.value = focusobjs[0].down('.filetype').innerHTML.strip();
				document.forms[this.loadparams.opener_reference].filetitle.value = focusobjs[0].down('.filetitle').innerHTML.replace(/<(.*)>/, '');
				document.forms[this.loadparams.opener_reference].href.value = $K.webroot + 'download/' + focusobjs[0].readAttribute('pname');

			}

			if (this.loadparams['type'] == 'image') {

				// contenteditor, image
				document.forms[this.loadparams.opener_reference].src.value = $K.webroot + 'image/' + focusobjs[0].readAttribute('pname');

				if ($(this.id+'_viewselect') && ($(this.id+'_viewselect').down().next('input').value != "")) {
					document.forms[this.loadparams.opener_reference].src.value += "/" + $(this.id+'_viewselect').down().next('input').value;
				}

			}

		}
		$K.yg_hideFileHint();
		if ($K.yg_hoverInt) {
			clearTimeout($K.yg_hoverInt);
		}
		$('yg_fileHint').hide();
		$K.windows['wid_'+winID].remove();
	}

	$('wid_'+winID).down('.ywindowfiltercolumn2').hide();
	$('wid_'+winID).down('.ywindowheadcolumn2').hide();
	if ($('wid_'+winID+'_tablecontent')) $('wid_'+winID+'_tablecontent').hide();
	if ($('wid_'+winID+'_uploadbtn')) $('wid_'+winID+'_uploadbtn').hide();
	$K.windows['wid_'+winID].init();
}

$K.yg_resetPageInfo = function (which) {
	which = $(which);
	which.up('form').select('input[type=hidden]').each(function(item) {
		item.value = '';
	});
}
