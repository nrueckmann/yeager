$K.yg_activeDragInfo.dragging = false;

/**
 * Draws a placeholder when dropping an Object into the content-tab
 * Only used internally.
 * @param { Element } [target] The affected element in this operation.
 * @param { String } [position] The event which triggered the click
 * @function
 * @name $K.yg_drawLoadingPlaceholder
 */
$K.yg_drawLoadingPlaceholder = function(target, position) {
	var liElement = new Element('li', {className: 'loading_placeholder'});
	if (position == 'before') {
		if (!target.up('ul').down('.loading_placeholder')) {
			target.insert({before:liElement});
		}
	} else if (position == 'after') {
		if (!target.up('ul').down('.loading_placeholder')) {
			target.insert({after:liElement});
		}
	} else {
		if ( !target.match('ul') &&
			 target.match('div.ipanel') &&
			 target.down('ul.page_contentarea') ) {
			target = target.down('ul.page_contentarea');
		}
		if (!target.down('.loading_placeholder')) {
			target.insert(liElement);
		}
	}
}




/**
 * Ondrop Handler for sortables.
 * Only used internally.
 * @param { Element } [element] The affected element in this operation.
 * @param { Event } [ev] The event which triggered the click
 * @function
 * @name $K.yg_customSortableOnDrop
 */
$K.yg_customSortableOnDrop = function(element, ev) {

	// INTEGRATE CODE FOR DROP ON CONTENTEDITOR

	if (!$K.yg_activeDragInfo.dragging) {
	//	if (ev) Event.stop(ev);
		return;
	}
	$K.yg_activeDragInfo.dragging = false;
	if (ev) Event.stop(ev);

	if ($('yg_ddGhost') && ($K.yg_activeDragInfo.dropAllowed == false)) {
		$K.yg_clearDragSession();
		return;
	}

	$K.yg_drawTopborder( null, true );

	$K.log( 'In $K.yg_customSortableOnDrop', element, $K.Log.DEBUG );

	// check object types
	var target_tree = Sortable.sortables[$K.yg_currentHover];

	// yg_type of target
	trgt_type = false;
	trgt_property = false;
	trgt_list = false;
	trgt_accepts = $K.yg_getTargetAcceptList();

	if (($(element).tagName == 'UL') || ($(element).up && $(element).up('ul'))) trgt_list = true;

	if ($(element) && (typeof($(element).readAttribute) == 'function')) {
		trgt_type = $(element).readAttribute('yg_type');
		trgt_property = $(element).readAttribute('yg_property');
	}
	if ((trgt_type==null) && $(element).down('ul')) {
		trgt_list = true;
		trgt_type = $(element).down('ul').readAttribute('yg_type');
		trgt_property = $(element).down('ul').readAttribute('yg_property');
	}
	if (trgt_property == "") trgt_property = false;

	// set targetelement and parent
	if (nlsddSession && nlsddSession.action && nlsddSession.srcData) {
		// if tree

		// check if we are over an empty sortable
		if (($K.yg_activeDragInfo.target) && ($K.yg_activeDragInfo.position != 'into')) {
			var targetElementParent = $K.yg_activeDragInfo.target;
		} else {
			var targetElementParent = $($K.yg_currentHover);
		}

		if ($K.yg_activeDragInfo.target) {
			targetElement = $K.yg_activeDragInfo.target;
		} else {
			targetElement = $(element);
		}
		targetPosition = $K.yg_activeDragInfo.position;

		$K.yg_currentdragobj = new Array();

		for (var i=0; i<nlsddSession.srcData.length; i++) {
			treeobj = $(nlsddSession.srcData[i].id)
			treeobj.writeAttribute('yg_id', nlsddSession.srcData[i].yg_id);
			treeobj.writeAttribute('yg_type', nlsddSession.srcData[i].yg_type);
			treeobj.writeAttribute('yg_url', nlsddSession.srcData[i].ygurl);
			treeobj.down('a').addClassName('mk_txt');
			$K.yg_currentdragobj.push(treeobj);
		}

	} else if ($('yg_ddGhost')) {
		// if sortable
		var origParent = $('yg_ddGhost')._originalParent;

		if ($K.yg_activeDragInfo.target && $K.yg_activeDragInfo.target.hasClassName('mk_cblock')) {
			targetElement = $K.yg_activeDragInfo.target;
			if (targetElement) {
				targetElementParent = $K.yg_activeDragInfo.target.parentNode;
			}
			if (!targetElementParent) targetElementParent = $(element);
			if (!targetElement) targetElement = targetElementParent;
		} else {
			targetElement = $(element);
			targetElementParent = $(element).parentNode;
			targetPosition = $K.yg_activeDragInfo.position;
		}
	}

	// get target yg_id
	var trgt_id = null;
	var trgt_objid = null;
	var scr_id = null;
	var scr_objid = null;
	var trgt_win = null;
	var src_win = null;
	if ($(targetElement) && (typeof($(targetElement).readAttribute) == "function") && ($(targetElement).readAttribute('yg_id'))) trgt_id = $(targetElement).readAttribute('yg_id');

	// winid
	if ($(element) && (typeof($(element).up) == 'function')) var trgt_win = $(element).up('.ywindow').id;

	// src winid
	var src_win = false;
	if ($K.yg_currentdragobj[0] && $K.yg_currentdragobj[0].up('.ywindow')) src_win = $K.yg_currentdragobj[0].up('.ywindow').id;

	// re-sorting > abort
	if (src_win == trgt_win) return;

	if (element.editorId && (element.editorId != "")) {
		// tinymce
		//element.execCommand('mceInsertContent', false, "Whatever text");
		trgt_type = "tinymce";
		trgt_accepts = "page,file";
	}

	$K.yg_currentdragobj.each(function(sourceElement, index) {

		src_id = $K.yg_getID(sourceElement);
		src_type = $K.yg_getType(sourceElement);

		if (src_id) var src_objid = src_id.split('-')[0];

		if (trgt_id) var trgt_objid = trgt_id.split('-')[0];

		if (!trgt_accepts || trgt_accepts.split(',').indexOf(src_type)==-1) return;

		// get caption
		if (src_type == "file") {
			if (sourceElement.down('.filetitle')) {
				caption = sourceElement.down('.filetitle').innerHTML;
				captionfull = caption;
				if (caption.lastIndexOf('>') != -1) {
					caption = caption.substring(caption.lastIndexOf('>')+1).strip();
				}
			} else {
				src_type = "filefolder";
				caption = sourceElement.down('.mk_txt').innerHTML;
			}
		} else {
			if (sourceElement.down('.mk_txt')) {
				caption = sourceElement.down('.mk_txt').innerHTML;
			} else if (sourceElement.down('.title')) {
				caption = sourceElement.down('.title').innerHTML;
			}
		}

		//* CASES LIST TO LIST **//

		if (trgt_type == "dropstack") {

			// anything >>> dropstack
			// check if dragging out of page contentarea -> refferer real id
			if ((src_type == "cblock") && (sourceElement.readAttribute("yg_entrymask") != "") && (sourceElement.readAttribute("yg_entrymask") != null)) {
				src_id = sourceElement.readAttribute("yg_entrymask");
				src_type = "entrymask";
			}
			if (src_type == "file") caption = captionfull;

			$K.windows[trgt_win].addFunction( src_id, src_type, caption, trgt_id, targetPosition );

		} else {

			switch(src_type) {

				case "template":

						// template >>> property (default-template, template-panel)
						if (trgt_property == 'template') {
							$K.windows[trgt_win].changeTemplate(src_objid);
						}
						break;

				case "templatefolder":

						// templatefolder >>> property
						if (trgt_property == 'templatefolder') {
							$K.windows[trgt_win].changeTemplateRoot(src_objid);
						}
						break;

				case "navigation":

						// navigation >>> property (navigation-panel)
						if (trgt_property == 'navigation') {
							$K.windows[trgt_win].changeNavigation(src_objid);
							$K.windows[src_win].yg_id = undefined;
						}
						break;

				case "tag":

						// tag >>> taglist
						if (trgt_property == 'yg_taglist') {
							if (!trgt_objid) targetPosition = 'into';
							//$K.windows[trgt_win].addFunction( src_objid, trgt_id, targetPosition, refreshvar, targetElementParent.id, src_type);
							$K.windows[trgt_win].addFunction(src_objid, trgt_objid, targetPosition);
						} else

						// tag >>> formfield
						if (trgt_type == 'formfield') {
							$K.windows[trgt_win].addTagtoFormfield(element, src_id, caption);
						} else

						// tags >>> file, filefolder
						if ( (trgt_type == 'file') && (!$(element).identify().endsWith('property')) ) {
							var data = Array ( 'noevent', {yg_property: 'addObjectTag', params: {
								objectID: trgt_objid,
								objectType: 'file',
								tagId: src_objid,
								targetId: 'norefresh'
							} } );
							$K.yg_AjaxCallback( data, 'addObjectTag' );

							if (typeof $K.windows[trgt_win].addTagToFile == 'function') {
								$K.windows[trgt_win].addTagToFile(element, src_id);
							}
						} else

						// tag >>> property
						if (trgt_type != 'formfield') {
							$(element).update(caption);
							if ( $(element).up('div.selectionmarker') &&
								 $(element).up('div.selectionmarker').next('input[type=hidden]') ) {
								 $(element).up('div.selectionmarker').next('input[type=hidden]').value = src_objid;
							}
							$K.yg_setObjectProperty( $(element), src_objid );
							$K.yg_fadeField( $(element).up('.cntblock') );
						}
						break;

				case "cblock":
				case "entrymask":
				case "extpage":
				case "extcblock":
				case "extfile":
				case "extmailing":

						// cblock >> content
						// entrymask >> content
						// extension >> content
						if (trgt_type == 'contentarea') {
							if (targetElement) {
								// check if reordering and return
								//if (sourceElement.up('.ywindow') == targetElement.up('.ywindow')) return;
								if (targetElement.hasClassName('mk_cblock')) {
									// need to get the LinkID instead of yg_id when inserting contentblocks
									var target_id = targetElement.id.split('_')[4];
								} else {
									var target_id = trgt_objid;
								}
							} else {
								var target_id = null;
							}

							if (index == $K.yg_currentdragobj.length-1) {
								// last entry
								refreshvar = true;
							} else {
								refreshvar = false;
							}

							if ((src_type == "cblock") || (src_type == "entrymask")) {
								if ($(targetElementParent).hasClassName('cntblockadd')) {
									targetElementParent = targetElementParent.previous().down();
								} else if (targetElementParent.tagName != 'UL') {
									if (targetElement.down('ul.page_contentarea')) {
										targetElementParent = targetElement.down('ul.page_contentarea');
									} else {
										targetElementParent = targetElementParent.up('ul.page_contentarea');
									}
								}
								var targetPosition = $K.yg_activeDragInfo.position;
								$K.yg_drawLoadingPlaceholder(targetElement, targetPosition);
								$K.windows[trgt_win].addFunction(src_id, target_id, targetPosition, refreshvar, targetElementParent.id, src_type, sourceElement);
							} else {
								$K.windows[trgt_win].addExtension(src_id, $K.windows[trgt_win].yg_id, src_type, refreshvar);
							}

						} else {

							if (src_type == "cblock") {

								// cblock >>> property
								if (trgt_type != 'formfield') {
									$(element).update(caption);
									if ( $(element).up('div.selectionmarker') &&
										 $(element).up('div.selectionmarker').next('input[type=hidden]') ) {
										 $(element).up('div.selectionmarker').next('input[type=hidden]').value = src_objid;
									}
									$K.yg_setObjectProperty( $(element), src_objid);
									$K.yg_fadeField( $(element).up('.cntblock') );
								} else

								// cblock >>> formfield
								if (trgt_type == 'formfield') {
									$K.windows[trgt_win].addContentblocktoFormfield( element, src_id, caption );
									return;
								}
							}

							if (src_type.substring(0,3) == "ext") {

								if (trgt_type != src_type.substring(3,src_type.length)) break;

								// ext to somewhere
								var data = Array ( 'noevent', {yg_property: 'addObjectExtension', params: {
									page: trgt_objid,
									site: trgt_type,
									extensionId: src_objid,
									openerRefID: false,
									refresh: false
								} } );
								$K.yg_AjaxCallback( data, 'addObjectExtension' );

							}

						}
						break;

				case "page":
						// page >>> formfield
						if (trgt_type == 'formfield') {
							$K.windows[trgt_win].addPagetoFormfield( element, src_id, caption );
						}

						// page >>> tinymce
						if (trgt_type == 'tinymce') {
							element.execCommand('mceInsertContent', false, "<a href='"+sourceElement.readAttribute("yg_url")+"'>"+caption+"</a>");
						}

						// page >>> property
						if ((trgt_type != 'formfield') && (trgt_type != 'tinymce')) {
							var site = src_id.split('-')[1];
							var page = src_id.split('-')[0];

							if ($(element).hasClassName('mk_page')) {
								// page property
								$(element).update(caption);
								var pageInfo = {page: page, site: site};
								$(element).up('td').down('input[type=hidden]').value = Object.toJSON(pageInfo);
								$K.yg_setObjectProperty($(element), {page: page, site: site});

							} else {
								var data = $K.appdir+'page/'+site+'/'+page+'/';

								// Show page icon
								$(element).up('.title').previous().className = 'iconpage icn';
								$(element).up('.title').previous().show();
								$(element).update(caption);
								$(element).up('.selectionmarker').next().value = data;
								$K.yg_setObjectProperty($(element), data);
							}
							$K.yg_fadeField($(element).up('.cntblock'));
						}
						break;

				case "filefolder":
						// filefolder >>> formfield filefolder
						var data =  {
							objecttype: 'file',
							yg_id: src_id,
							title: caption
						};
						if ((trgt_type == 'formfield') && (trgt_property == 'filefolder')) {
							$K.yg_editControl( $(element).down('.title_txt'), '16', true, data);
						}
						break;

				case "file":
						var tmpFiletype = 'FILE';
						var tmpFilecolor = 'red';
						if (sourceElement && sourceElement.down('span.filetype')) {
							tmpFiletype = sourceElement.down('span.filetype').innerHTML.strip();
							tmpFilecolor = sourceElement.down('span.filetype').className.replace(/filetype/,'').strip();
						}

						// files >>> formfield link/file
						if (trgt_type == 'formfield') {
							var data =  {
								objecttype: 'file',
								yg_id: src_id,
								title: caption,
								filetype: tmpFiletype,
								filecolor: tmpFilecolor
							};

							if (trgt_property == 'file') {
								// file formfield
								$K.yg_editControl( $(element).down('.title_txt'), '6', true, data);
							} else if (trgt_property == 'link') {
								// link formfield
								$K.yg_editControl( $(element).down('.title_txt'), '5', true, data);
							}

							$K.yg_fadeField( $(element) );
						} else

						// file >>> tinymce
						if (trgt_type == 'tinymce') {
							if (sourceElement.up('li.mk_file')) sourceElement = sourceElement.up('li.mk_file');
							if (sourceElement.readAttribute("yg_imageurl") == "") {
								element.execCommand('mceInsertContent', false, "<a href='"+sourceElement.readAttribute("yg_downloadurl")+"'>"+caption+"</a>");
							} else {
								element.execCommand('mceInsertContent', false, "<img src='"+sourceElement.readAttribute("yg_imageurl")+"'>");
							}
						}


						// file >>> property
						if ((trgt_type != 'formfield') && (trgt_type != 'tinymce')) {
							if ($(element).hasClassName('mk_file')) {
								// file property
								var fileInfo = sourceElement.down('.filetitle').innerHTML;

								var titlefield = $(element).down('.title_txt');

								titlefield.update('<span class="filetype '+tmpFilecolor+'" yg_type="'+$K.windows[$(element).up('.ywindow').id].yg_type+'" yg_id="'+$K.windows[$(element).up('.ywindow').id].yg_id+'" yg_property="type">'+tmpFiletype+'</span> '+caption);

								var valuefield = $(element).down('input[type=hidden]');
								valuefield.value = src_objid;

								if (!$(element).hasClassName('mk_noautosave')) {
									$K.yg_setObjectProperty(valuefield);
								}
							} else {
								// link property
								var data = $K.appdir+'download/'+ src_objid;

								// Hide icon
								$(element).up('.title').previous().hide();

								$(element).update('<span class="filetype '+tmpFilecolor+'" yg_type="'+$K.windows[$(element).up('.ywindow').id].yg_type+'" yg_id="'+$K.windows[$(element).up('.ywindow').id].yg_id+'" yg_property="type">'+tmpFiletype+'</span> '+caption);
								$(element).up('.selectionmarker').next().value = data;

								$K.yg_setObjectProperty( $(element), data );
							}
							$K.yg_fadeField( $(element).up('.cntblock') );
						}
						break;

				case "formfield":
				case "property":

						// formfield >>> formfield
						// property >>> property
						if (((src_type == 'formfield') && (trgt_type == 'formfield')) ||
						((src_type == 'property') && (trgt_type == 'property'))) {
							if ((typeof($($K.yg_currentHover).hasClassName) == 'function') && $($K.yg_currentHover).hasClassName('cntblockadd')) $K.yg_activeDragInfo.position = 'into';
							$K.yg_addFormfieldToList( trgt_win, $(element), sourceElement );
							$K.yg_hilite('entrymask', $K.windows[trgt_win].yg_id, 'name', true);
						}
						break;

				case "view":

						// view >>> file
						if (trgt_type == 'file') {
							trgt_objid = targetElement.readAttribute('yg_id').split('-')[0];
							var data = Array ( 'noevent', {yg_property: 'addFileView', params: {
								file: trgt_objid,
								view: src_objid
							} } );
							$K.yg_AjaxCallback( data, 'addFileView' );
						}
						break;

				case "usergroup":

						// usergroup >>> user
						// usergroup >>> mailing
						if ((trgt_type == 'user') || (trgt_type == 'mailing')) {
							if (trgt_type == 'mailing') {
								// mailing
								if (targetElement.tagName == 'LI') {
									targetElement = targetElementParent;
									trgt_objid = targetElement.readAttribute('yg_id').split('-')[0];
								} else {
									trgt_objid = $K.windows[targetElement.up('.ywindow').id].yg_id.split('-')[0];
								}
								var params = {
									mode: trgt_type,
									mailingId: trgt_objid,
									roleId: src_objid,
									openerRefId: trgt_win,
									refresh: 'true'
								}
							} else {
								// user
								if (targetElement.tagName == 'LI') {
									targetElement = targetElementParent;
									trgt_objid = targetElement.readAttribute('yg_id').split('-')[0];
								} else {
									trgt_objid = $K.windows[targetElement.up('.ywindow').id].yg_id.split('-')[0];
								}
								var params = {
									mode: trgt_type,
									userId: trgt_objid,
									roleId: src_objid,
									openerRefId: trgt_win,
									refresh: 'true'
								}
							}
							var data = Array ( 'noevent', {yg_property: 'addUsergroup', params: params } );
							$K.yg_AjaxCallback( data, 'addUsergroup' );
						}
						break;

			}
		}
	});

	$K.log( "src_type: "+src_type+"	trgt_type: "+trgt_type+"	 trgt_property: "+trgt_property, $K.Log.INFO );

	$K.yg_clearDragSession();
	if (targetElementParent) {
		$K.yg_recreateSortables( targetElementParent.id );
	}
	return;

}



/**
 * Ondrop Handler for trees.
 * Only used internally.
 * @param { Event } [e] The event which fired on this operation.
 * @function
 * @name $K.yg_customOnDrop
 */
$K.yg_customOnDrop = function(e) {

	$K.yg_activeDragInfo.dragging = false;
	if ($K.yg_activeDragInfo.dropAllowed == false) return;

	// from list to tree
	if( $('yg_ddGhost') && ($('yg_ddGhost').dataTxt) ) {

		if ($('yg_ddGhost').getStyle('display') != 'none') {

			// check & save target-tree properties
			if (this.tId.endsWith('_tree')) {
				var target_tree = $(this.tId).up(1);
			} else {
				var target_tree = $(this.tId);
			}
			var trgt_type = target_tree.yg_type;
			var yg_ddGhost = $('yg_ddGhost');
			var source_tree = Sortable.sortables[yg_ddGhost._originalParent.id];

			var sData, dData, sObj, dObj;
			with (nlsddSession) {
				sData=srcData; sObj=srcObj;
				dData=destData; dObj=destObj;
			}

			$K.log( 'DRAG AND DROP: ', sObj, dObj, $K.Log.INFO );
			$K.log( 'DTARGET: IsType: ' + trgt_type + ', ' + 'Accepting: ' + target_tree.accepts, $K.Log.INFO );


			//* CASES LIST TO TREE **//

			$K.yg_currentdragobj.each(function(sourceElement) {

				src_id = $K.yg_getID(sourceElement);
				src_type = $K.yg_getType(sourceElement);
				if (src_id) var src_objid = src_id.split('-')[0];

				// return if not accepting
				if (target_tree.accepts.indexOf(src_type) == -1) return;

				switch(src_type) {

					case "file":

							// move files >>> tree

							// abort if file was dragged on root node or read-only or was dragged from dropstrack
							if ((dData.orgId == 'root_1') || sourceElement.hasClassName('mk_nowrite') || (source_tree == "dropstack")) return;

							var fileName;
							if (($K.yg_currentdragobj.length > 1) &&
								 (yg_ddGhost.srcReference.element.up('.mk_contentgroup') == $K.yg_currentdragobj[0].up('.mk_contentgroup'))) {
								fileName = $K.yg_currentdragobj.length + ' ' + $K.TXT('TXT_OBJECTS');
							} else {
								fileName = yg_ddGhost.srcReference.element.down('.filetitle').innerHTML;
							}

							var origDragObject = $K.yg_currentdragobj;
							var origDData = dData;
							var origSrcReference = yg_ddGhost.srcReference;
							var origDObj = dObj;

							$K.yg_promptbox( $K.TXT('TXT_APPROVE_MOVE_TITLE'), $K.TXT('TXT_APPROVE_MOVE_P1')+fileName+ $K.TXT('TXT_APPROVE_MOVE_P2'), 'standard',
								function() {
									var fileIDs = new Array();
									if ( (origDragObject.length > 0) &&
										 (origDragObject[0].up('.mk_contentgroup') == origSrcReference.element.up('.mk_contentgroup'))  ) {
										origDragObject.each(function(item) {
											if (!item.hasClassName('mk_nowrite')) {
												if (item.up('li')) {
													// Thumb view
													fileIDs.push( item.up('li').yg_id );
												} else {
													// List view
													fileIDs.push( item.yg_id );
												}
											}
										});
									} else {
										fileIDs.push( origSrcReference.element.yg_id );
									}

									fileIDs.each(function(item) {
										var fileID = item.split('-')[0];
										var objectID = origDData.yg_id.split('-')[0];
										var data = Array ( 'noevent', {yg_property: 'moveFile', params: {
											source: fileID,
											target: objectID,
											openerRef: origSrcReference.element.up('.ywindow').id,
											before: null
										} } );
										$K.yg_AjaxCallback( data, 'moveFile' );
									});

									// Update Actionbutton
									$K.yg_showActions( $(origDData.id), origDObj.tId );
								}, function() {}
							);
							throw $break;

					case "tag":

							// tags >>> tree
							var data = Array ( 'noevent', {yg_property: 'addObjectTag', params: {
								objectID: dData.yg_id.split('-')[0],
								objectType: dData.yg_type,
								site: dData.yg_id.split('-')[1],
								tagId: src_objid,
								targetId: ''
							} } );
							$K.yg_AjaxCallback( data, 'addObjectTag' );
							$K.yg_showActions( $(dData.id), dObj.tId );
							break;

					case "view":

							// view >> tree
							var data = Array ( 'noevent', {yg_property: 'addFileView', params: {
								file: dData.yg_id.split('-')[0],
								view: src_objid
							} } );
							$K.yg_AjaxCallback( data, 'addFileView' );
							$K.yg_showActions( $(dData.id), dObj.tId );
							break;

					case "template":

							// template >> page-tree
							$K.yg_changeTemplate(src_objid, dData.yg_id);
							$K.yg_showActions( $(dData.id), dObj.tId );
							break;

					case "cblock":

							// cblock >> tree

							// abort if file was dragged on root node or read-only or was dragged from dropstrack
							if ((dData.orgId == 'root_1') || sourceElement.hasClassName('mk_nowrite') || (source_tree == "dropstack")) return;

							var objName;
							if (($K.yg_currentdragobj.length > 1) &&
								 (yg_ddGhost.srcReference.element.up('.mk_contentgroup') == $K.yg_currentdragobj[0].up('.mk_contentgroup'))) {
								objName = $K.yg_currentdragobj.length + ' ' + $K.TXT('TXT_OBJECTS');
							} else {
								objName = yg_ddGhost.srcReference.element.down('.mk_txt').innerHTML;
							}

							$K.yg_promptbox( $K.TXT('TXT_APPROVE_MOVE_TITLE'), $K.TXT('TXT_APPROVE_MOVE_P1')+objName+ $K.TXT('TXT_APPROVE_MOVE_P2'), 'standard',
								function() {
									var objIds = new Array();

									if ( ($K.yg_currentdragobj.length > 0) &&
										 ($K.yg_currentdragobj[0].up('.mk_contentgroup') == yg_ddGhost.srcReference.element.up('.mk_contentgroup'))  ) {
										$K.yg_currentdragobj.each(function(item) {
											if (!item.hasClassName('mk_nowrite')) {
												objIds.push( item.yg_id );
											}
										});
									} else {
										objIds.push( yg_ddGhost.srcReference.element.yg_id );
									}

									objIds.each(function(item) {
										var objID = item.split('-')[0];
										var objectID = dData.yg_id.split('-')[0];


										var data = Array ( 'noevent', {yg_property: 'moveCBlock', params: {
											source: objID,
											target: objectID,
											orgAction: 'movefromlist',
											openerRef: yg_ddGhost.srcReference.element.up('.ywindow').id,
											before: null
										} } );
										$K.yg_AjaxCallback( data, 'moveCBlock' );
									});

									// Update Actionbutton
									$K.yg_showActions( $(dData.id), dObj.tId );
								}, function() {}
							);
							throw $break;
							break;

					case "extpage":
					case "extcblock":
					case "extfile":
					case "extmailing":

							if (trgt_type != src_type.substring(3,src_type.length)) break;

							// ext to somewhere
							var data = Array ( 'noevent', {yg_property: 'addObjectExtension', params: {
								page: dData.yg_id.split('-')[0],
								site: dData.yg_id.split('-')[1],
								extensionId: src_objid,
								openerRefID: false,
								refresh: false
							} } );
							$K.yg_AjaxCallback( data, 'addObjectExtension' );
							break;

				}
			});
			$K.yg_clearDragSession();
			return;
		}
	}

	// from tree to tree
	if (!nlsddSession) return;
	var sData, dData, sObj, dObj;
	with (nlsddSession) {
		if (!action) return;
		sData=srcData; sObj=srcObj;
		dData=destData; dObj=destObj;
	}

	// Check target-tree properties
	if (dObj.tId.endsWith('_tree')) {
		var target_tree = $(dObj.tId).up(1);
	} else {
		var target_tree = $(dObj.tId);
	}

	// Check source-tree properties
	if (sObj.tId.endsWith('_tree')) {
		var source_tree = $(sObj.tId).up(1);
	} else {
		var source_tree = $(sObj.tId);
	}

	// Check if we are really allowed to drop (objecttypes) ...
	if ( target_tree.accepts.indexOf(source_tree.yg_type)==-1 ) {
		$K.log( 'dropping not allowed!', $K.Log.DEBUG );
		return;
	}

	var savedNlsddSession = Object.clone(nlsddSession);
	$K.yg_dndOnSuccess = function() {
		if (sObj.tId==dObj.tId) { //drag drop in a tree
			switch (savedNlsddSession.action) {
				case NlsDDAction.DD_INSERT:
					sObj.moveChild(sData, dData, 2);
					break;
				case NlsDDAction.DD_APPEND:
					sObj.moveChild(sData, dData, 1);
					break;
			}
		} else { // drag drop between tree
			switch (savedNlsddSession.action) {
				case NlsDDAction.DD_INSERT:
					for (i=0;i<sData.length;i++) {
						with (sData[i]) {
							var nNd=dObj.addBefore(null, dData.orgId, capt, url, (ic?ic.join(","):ic), exp, chk, xtra, title, sData.add);
							if (fc) duplicateNode(fc, nNd);
						}
					}
					dObj.reloadNode(dData.pr.orgId);
					break;
				case NlsDDAction.DD_APPEND:
					for (i=0;i<sData.length;i++) {
						with (sData[i]) {
							var nNd=dObj.append(null, dData.orgId, capt, url, (ic?ic.join(","):ic), exp, chk, xtra, title, sData.add);
							if (fc) duplicateNode(fc, nNd);
						}
					}
					dObj.reloadNode(nNd.orgId);
					dObj.expandNode(dData.orgId);
					break;
			}
		}

		for (var j=0; j<$K.yg_dynTreesWNDO.length; j++) {
			$K.yg_dynTreesWNDO[j].setBarSize();
		}

		// Refresh action buttons (if possible)
		if ( typeof(nlsTree[source_tree.id+'_tree'].remapAction)=='function' ) {
			nlsTree[source_tree.id+'_tree'].remapAction();
		}
		if (source_tree!=target_tree) {
			if ( typeof(nlsTree[target_tree.id+'_tree'].remapAction)=='function' ) {
				nlsTree[target_tree.id+'_tree'].remapAction();
			}
		}

		// Uninitialize
		$K.yg_clearDragSession();
		$K.yg_dndOnSuccess = function(){};
	};
	/* END $K.yg_dndOnSuccess */





	// For move into
	var source = sData[0].yg_id;
	var target = dData.yg_id;
	var opener = $($K.yg_getTreeReference(sData[0])).up('.ywindow').id;

	//* CASES TREE TO TREE **//
	switch(sObj.yg_type) {

		case "tag":

				// adding tags to object
				if (sObj.tId != dObj.tId) {

					var selectedNodes = nlsTree['tags_tree'+opener.split('_')[1]+'_tree'].getSelNodes();

					for (var i=0;i<selectedNodes.length;i++) {
						// Check if we are on the Tags-Tab (and the target page is the current page)
						var tagId = selectedNodes[i].yg_id.split('-')[0];
						var objectType = dObj.yg_type;
						var objectID = target.split('-')[0];
						var site = target.split('-')[1];

						var data = Array ( 'noevent', {yg_property: 'addObjectTag', params: {
							objectID: objectID,
							objectType: objectType,
							site: site,
							tagId: tagId,
							targetId: ''
						} } );
						$K.yg_AjaxCallback( data, 'addObjectTag' );
					}
					// Update Actionbutton
					$K.yg_showActions( $(dData.id), dObj.tId );
				}

				// moving tags
				if (sObj.tId == dObj.tId) {
					var selectedNodes = nlsTree['tags_tree'+opener.split('_')[1]+'_tree'].getSelNodes();

					var nodeList = new Array();
					selectedNodes.each(function(currNode){
						nodeList.push(currNode.yg_id.split('-')[0]);
					});

					var objectID = target.split('-')[0];
					var site = target.split('-')[1];

					var data = Array ( 'noevent', {yg_property: 'moveTag', params: {
						source: nodeList,
						target: dData.yg_id.split('-')[0],
						openerRef: opener
					} } );
					$K.yg_AjaxCallback( data, 'moveTag' );

					// Update Actionbutton
					$K.yg_showActions( $(dData.id), dObj.tId );
				}
				break;

		case "template":

				// moving template
				if (sObj.tId == dObj.tId) {
					var selectedNodes = nlsTree['templates_tree'+opener.split('_')[1]+'_tree'].getSelNodes();

					var multi = false;
					if (selectedNodes.length > 1) {
						multi = true;
					}
					if (multi) {
						var objectCount = selectedNodes.length;
						objectName = objectCount+' '+$K.TXT('TXT_TEMPLATES');
					} else {
						objectName = selectedNodes[0].capt;
					}

					$K.yg_promptbox( $K.TXT('TXT_APPROVE_MOVE_TITLE'), $K.TXT('TXT_APPROVE_MOVE_P1') + objectName + $K.TXT('TXT_APPROVE_MOVE_P2'), 'standard', function(data) {
						for (var i=0;i<selectedNodes.length;i++) {
							var sourceId = selectedNodes[i].yg_id.split('-')[0];

							var data = Array ( 'noevent', {yg_property: 'moveTemplate', params: {
								source: sourceId,
								target: dData.yg_id.split('-')[0],
								openerRef: opener
							} } );
							$K.yg_AjaxCallback( data, 'moveTemplate' );
						}
					}, function() {
						$K.log( 'Cancelled...', $K.Log.INFO );
					});

					// Update Actionbutton
					$K.yg_showActions( $(dData.id), dObj.tId );
				} else

				// template >> page-tree
				if (dData.yg_type == "page") {
					var selectedNodes = nlsTree['templates_tree'+opener.split('_')[1]+'_tree'].getSelNodes();
					sourceId = selectedNodes[0].yg_id.split('-')[0];
					$K.yg_changeTemplate(sourceId, dData.yg_id);
					$K.yg_showActions( $(dData.id), dObj.tId );
				}
				break;

		case "entrymask":

				// moving entrymasks
				if (sObj.tId == dObj.tId) {
					var selectedNodes = nlsTree['entrymasks_tree'+opener.split('_')[1]+'_tree'].getSelNodes();

					var multi = false;
					if (selectedNodes.length > 1) {
						multi = true;
					}
					if (multi) {
						var objectCount = selectedNodes.length;
						objectName = objectCount+' '+$K.TXT('TXT_ENTRYMASKS');
					} else {
						objectName = selectedNodes[0].capt;
					}

					$K.yg_promptbox( $K.TXT('TXT_APPROVE_MOVE_TITLE'), $K.TXT('TXT_APPROVE_MOVE_P1') + objectName + $K.TXT('TXT_APPROVE_MOVE_P2'), 'standard', function(data) {
						for (var i=0;i<selectedNodes.length;i++) {
							var sourceId = selectedNodes[i].yg_id.split('-')[0];

							var data = Array ( 'noevent', {yg_property: 'moveEntrymask', params: {
								source: sourceId,
								target: dData.yg_id.split('-')[0],
								openerRef: opener
							} } );
							$K.yg_AjaxCallback( data, 'moveEntrymask' );
						}
					}, function() {
						$K.log( 'Cancelled...', $K.Log.INFO );
					});

					// Update Actionbutton
					$K.yg_showActions( $(dData.id), dObj.tId );
				}
				break;

		case "cblock":

				// moving contentblocks
				if (sObj.tId == dObj.tId) {
					var treeNode = $(dData.id);
					var treeNodeLink = treeNode.down('a');

					if (!treeNodeLink.hasClassName('nodrop')) {
						switch (nlsddSession.action) {
							case NlsDDAction.DD_INSERT:
								var data = Array ( 'noevent', {yg_property: 'moveCBlock', params: {
									source: sData[0].yg_id.split('-')[0],
									target: dData.yg_id.split('-')[0],
									openerRef: opener,
									before: true
								} } );
								$K.log( 'Data is:', data, $K.Log.INFO );
								$K.yg_AjaxCallback( data, 'moveCBlock' );
								break;

							case NlsDDAction.DD_APPEND:
								var data = Array ( 'noevent', {yg_property: 'moveCBlock', params: {
									source: sData[0].yg_id.split('-')[0],
									target: dData.yg_id.split('-')[0],
									openerRef: opener,
									before: false
								} } );
								$K.log( 'Data is:', data, $K.Log.INFO );
								$K.yg_AjaxCallback( data, 'moveCBlock' );
								break;
						}
					}
				}
				break;

		case "page":

				// moving pages
				if (sObj.tId == dObj.tId) {
					switch (nlsddSession.action) {
						case NlsDDAction.DD_INSERT:
							for (var i=0;i<sData.length;i++) {
								var data = Array ( 'noevent', {yg_property: 'movePage', params: {
									sourceSite: sData[i].yg_id.split('-')[1],
									source: sData[i].yg_id.split('-')[0],
									targetSite: dData.yg_id.split('-')[1],
									target: dData.yg_id.split('-')[0],
									openerRef: opener,
									before: true
								} } );
								$K.log( 'Data is:', data, $K.Log.INFO );
								$K.yg_AjaxCallback( data, 'movePage' );
							}
							break;

						case NlsDDAction.DD_APPEND:
							for (var i=0;i<sData.length;i++) {
								var data = Array ( 'noevent', {yg_property: 'movePage', params: {
									sourceSite: sData[i].yg_id.split('-')[1],
									source: sData[i].yg_id.split('-')[0],
									targetSite: dData.yg_id.split('-')[1],
									target: dData.yg_id.split('-')[0],
									openerRef: opener,
									before: false
								} } );
								$K.log( 'Data is:', data, $K.Log.INFO );
								$K.yg_AjaxCallback( data, 'movePage' );
							}
							break;
					}
				}
				break;

		case "file":

				// moving files
				if (sObj.tId == dObj.tId) {
					switch (nlsddSession.action) {
						case NlsDDAction.DD_INSERT:
							var data = Array ( 'noevent', {yg_property: 'moveFile', params: {
								source: sData[0].yg_id.split('-')[0],
								target: dData.yg_id.split('-')[0],
								openerRef: opener,
								before: true
							} } );
							$K.log( 'Data is:', data, $K.Log.INFO );
							$K.yg_AjaxCallback( data, 'moveFile' );
							break;

						case NlsDDAction.DD_APPEND:
							var data = Array ( 'noevent', {yg_property: 'moveFile', params: {
								source: sData[0].yg_id.split('-')[0],
								target: dData.yg_id.split('-')[0],
								openerRef: opener,
								before: false
							} } );
							$K.log( 'Data is:', data, $K.Log.INFO );
							$K.yg_AjaxCallback( data, 'moveFile' );
							break;
					}
				}
				break;

	}
	$K.yg_clearDragSession();
}
