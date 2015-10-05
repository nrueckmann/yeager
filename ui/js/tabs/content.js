/**
 * Initializes content tab
 * @param { String } [ygID] The yg_id of the current object
 * @param { String } [displayMode] The displaymode (dialog or empty)
 * @param { String } [winID] The displaymode (dialog or empty)
 * @param { String } [objectType] The object type
 * @function
 * @name $K.yg_initContentTab
 */
$K.yg_initContentTab = function( ygID, displayMode, winID, objectType ) {

	if (!ygID) return;

	var pageID = ygID.split('-')[0];
	var siteID = ygID.split('-')[1];

	var contentareaIDList = $K.windows[winID].contentareaIDList;
	var contentareaIDListExt = $K.windows[winID].contentareaIDListExt;

	if (!contentareaIDList || !contentareaIDListExt) return;

	if (contentareaIDListExt.length == 0) {
		// Check if contentareas are present, if not, remove the loading indicator
		if ($(winID+'_column2innercontentinner')) {
			$K.windows[winID].refresh('col2');
			$(winID+'_column2innercontentinner').removeClassName('tab_loading');
		}
		if ($(winID+'_nocontentareas')) $(winID+'_nocontentareas').show();

		return;
	}

	// check if we run in insert content dialog and add load indicator
	if ($(winID+'_column2innercontentinner')) {
		$(winID+'_column2innercontentinner').addClassName('tab_loading');
	} else {
		$(winID+'_ywindowinner').addClassName('tab_loading');
	}

	var containments = new Array();

	$K.windows[winID].makeContentareasSortable = function() {

		// Build array of containments
		contentareaIDList.each(function(contentareaID){
			containments.push(winID+'_scp_'+contentareaID+'_list');
		});

		var newcontainments = containments;

		//Push extra for content-chooser window
		containments.push(winID+'_co_contentarea_');

		if (displayMode == 'dialog') {

			// When the page overview is displayed in a popup-window, push also the opener sortables to the list
			$A(Sortable.sortables[$K.windows[winID].loadparams.opener_reference].containment).each(function(cont_item){
				containments.push(cont_item);
			});

		}

		// Clean from duplicates
		containments = $A(containments).uniq();

		$K.log( 'Containments are now: ', containments, $K.Log.DEBUG );

		contentareaIDList.each(function(contentareaID){

			var srtId = winID+'_scp_'+contentareaID+'_list';

			var sortableAccepts = 'cblock,entrymask,pagesextras';
			var sortableObjectType = 'cblock';
			if (objectType == 'cblock') {
				sortableAccepts = 'cblock,entrymask,pagesextras';
			} else if (objectType == 'extpage') {
				sortableAccepts = 'extpage';
				sortableObjectType = 'extpage';
			} else if (objectType == 'extcblock') {
				sortableAccepts = 'extcblock';
				sortableObjectType = 'extcblock';
			} else if (objectType == 'extfile') {
				sortableAccepts = 'extfile';
				sortableObjectType = 'extfile';
			} else if (objectType == 'extmailing') {
				sortableAccepts = 'extmailing';
				sortableObjectType = 'extmailing';
			}

			Sortable.create( srtId, {
		  		accepts: sortableAccepts,
		  		objectType: sortableObjectType,
				dropOnEmpty: true,
				constraint: false,
				yg_clone: (displayMode=='dialog')?(true):(false),
				ghosting: true,
				starteffect: function(element) {
					Draggable._dragging[element] = true;
					window.$ordersubmitted = false;
				},
				endeffect: function(element) {
					Draggable._dragging[element] = false
				},
				onUpdate: function (element) {

					if (element.select('li.loading_placeholder').length > 0) {
						return;
					}

					// Fire late events
					$K.yg_fireLateOnChange();

					if (displayMode!='dialog') {

						var chldElements = $A($(element).childNodes);
						var realChldElements = new Array();
						$(chldElements).each(function(item) {
							if (item.nodeType==1) realChldElements.push( $(item) );
						});
						chldElements = realChldElements;

						$K.log( '99########', chldElements, $K.Log.DEBUG );

						/*if (chldElements && chldElements.length==0) {
							element.up().previous().show();
							element.up().next().hide();
						} else {
							element.up().previous().hide();
							element.up().next().show();
						}*/

						// Get all contentareas and the corresponding contained contentblocks (if not already submitted)
						if (!window.$ordersubmitted) {

							var cnt_scp_list = new Object();
							var hasDupes = false;

							containments.each(function(carea) {
								var cnt_scp_items = new Array();
								if ( (!Object.isUndefined($(carea))) && ($(carea)!=null) ) {
									var chldElems = $A($(carea).childNodes);
									if (chldElems) {
										chldElems.each(function(cblock) {
											if (cblock.nodeName == 'LI' && cblock.readAttribute('yg_id')) {
												if (cblock.id.startsWith('clone%%emblock')) {
													cnt_scp_items.push( ['emblock', cblock.readAttribute('yg_id').split('-')[0]] );
												} else {
													cnt_scp_items.push( [cblock.readAttribute('yg_id').split('-')[0], cblock.id.split('_')[4]] );
												}
											}
										});
									}
								}
								if (carea!=winID+'_co_contentarea_') {
									// Check for duplicate cblocks
									var cblockIdList = new Array();
									cnt_scp_items.each(function(itm) {
										cblockIdList.push(itm[0]);
									});
									var firstCblockCount = cblockIdList.length;
									cblockIdList = cblockIdList.uniq();
									var secondCblockCount = cblockIdList.length;
									if (firstCblockCount != secondCblockCount) {
										hasDupes = true;
									}
									cnt_scp_list[carea.split('_')[3]] = cnt_scp_items;
								}
							});

							$K.log( $K.yg_activeDragInfo, $K.Log.DEBUG );

							if (objectType == 'page') {
								var backendAction = 'orderPageContentblock';
							} else if (objectType == 'mailing') {
								var backendAction = 'orderMailingContentblock';
							} if (objectType == 'cblock') {
								var backendAction = 'orderEditContentblock';
							}

							// Check if we got the original source element
							if ( $K.yg_activeDragInfo.origElement && $K.yg_activeDragInfo.origElement.up('.ywindow') && $K.yg_activeDragInfo.origElement.up('.ywindow').hasClassName('ydialog') ) {
								var origElement = $K.yg_activeDragInfo.origElement;

								if ($K.windows[origElement.up('.ywindow').id] && $K.windows[origElement.up('.ywindow').id].tab == 'copy') {
									var data = Array ( 'noevent', {yg_property: backendAction, params: {
										page: pageID,
										site: siteID,
										winID: winID.split('_')[1],
										mode: 'copy',
										newcolists: Object.toJSON(cnt_scp_list)
									} } );
								} else {
									var data = Array ( 'noevent', {yg_property: backendAction, params: {
										page: pageID,
										site: siteID,
										winID: winID.split('_')[1],
										mode: '',
										newcolists: Object.toJSON(cnt_scp_list)
									} } );
								}
							} else {
								var data = Array ( 'noevent', {yg_property: backendAction, params: {
									page: pageID,
									site: siteID,
									winID: winID.split('_')[1],
									mode: '',
									newcolists: Object.toJSON(cnt_scp_list)
								} } );
							}

							if ((objectType != 'page') && (objectType != 'mailing')) hasDupes = false;
							if (hasDupes) {
								$K.yg_promptbox('Error', $K.TXT('TXT_DUPLICATE_CBLOCK'), 'alert', function() {
									Koala.windows[winID].tabs.select(Koala.windows[winID].tabs.selected,{refresh:1});
								});
								window.$ordersubmitted = true;
							} else {
								$K.log( 'data:', cnt_scp_list, $K.Log.DEBUG );

								$K.yg_AjaxCallback( data, backendAction );

								window.$ordersubmitted = true;
							}

							return;
						}

						// Update scrollbars
						$K.windows[winID].refresh("col1");

					}

				}
			});

		});

		$K.log( 'Makesortable finished!', $K.Log.DEBUG );

	}

	var contentData = new Array();

	contentareaIDListExt.each(function(contentareaInfo){

		contentareaInfo.list.each(function(contentareaInfoList){
			contentData.push( Array( contentareaInfoList.id, contentareaInfo.code, contentareaInfo.id, contentareaInfoList.linkid) );
			$K.log( 'Loading contentblock "'+contentareaInfoList.name+' ('+contentareaInfoList.id+' ['+contentareaInfoList.linkid+'])" into contentarea "'+contentareaInfo.name+' ('+contentareaInfo.code+')"...', $K.Log.DEBUG );
		});

	});

	// Check if contentareas are present, if not, remove the loading indicator
	if ($(winID+'_column2innercontentinner') && (contentareaIDListExt.length == 0)) {
		$(winID+'_column2innercontentinner').removeClassName('tab_loading');
		$K.windows[winID].refresh('col2');
	}

	var data_cnt = contentData.length;

	if ( (siteID!='') && (pageID!='') ) {

		$K.windows[winID].allContentareasLoaded = function(objects, onFinish, real_parent) {

			if ($(winID+'_column2innercontentinner')) {
				$(winID+'_column2innercontentinner').removeClassName('tab_loading');
			} else {
				$(winID+'_ywindowinner').removeClassName('tab_loading');
			}

			// Show all panels & Show/hide infotext when area is empty
			var temp_element = temp_element2 = '';

			if (displayMode!='dialog') {
				contentareaIDList.each(function(contentareaID){
					temp_element = $(winID+'_panel'+contentareaID);
					temp_element2 = temp_element.down('ul');

					temp_element.setStyle({display:'block'});
					$K.yg_customAttributeHandler( temp_element );

					temp_element2.cleared = undefined;

					/*var chldElements = temp_element2.childElements();
					if (chldElements.length==0) {
						temp_element2.up().previous().show();
						temp_element2.up().next().hide();
					} else {
						temp_element2.up().previous().hide();
						temp_element2.up().next().show();
					}*/
				});
			} else {

				winID = real_parent.up('.ywindow').id.replace(/wid_/,'');

				if (!$(winID+'_column2innercontentinner')) {

					$(winID+'_innercontent').down('.mk_contentgroup').childElements().each(function(elem){
						temp_element = elem;
						temp_element2 = temp_element.down('ul');

						temp_element.setStyle({display:'block'});
						$K.yg_customAttributeHandler( temp_element );

						temp_element2.cleared = undefined;

						/*var chldElements = temp_element2.childElements();
						if (chldElements.length==0) {
							temp_element2.up().previous().show();
							temp_element2.up().next().hide();
						} else {
							temp_element2.up().previous().hide();
							temp_element2.up().next().show();
						}*/
					});

				} else {
					$(winID+'_column2innercontentinner').down().childElements().each(function(elem){
						elem.show();
						$K.yg_customAttributeHandler( elem );
					});

					$K.windows[winID].refresh("col2");
					$(winID+'_column2innercontentinner').removeClassName('tab_loading');
				}

			}

			if (real_parent) {
				if ( !real_parent.hasClassName('mk_nowrite') && (real_parent.up('.ywindow').down('.ywindowinner')) &&
					 (typeof $K.windows[real_parent.up('.ywindow').id].makeContentareasSortable == 'function') ) {
					$K.windows[real_parent.up('.ywindow').id].makeContentareasSortable();
				}
			} else {
				$K.windows[winID].makeContentareasSortable();
			}

			// Refresh scrollbars
			$K.windows[winID].refresh("col1");

			if (real_parent) {
				$K.windows[real_parent.up('.ywindow').id].refresh("col1");
			}

			// Map onload for images, so that scrollbars are refreshed when image is completely loaded
			if (real_parent) {
				var innerContent = real_parent.up('.innercontent');
				var contentImages = innerContent.select('td.elemcontent img');
				contentImages.each(function(item){
					var handleImage = true;
					var hasWidth = false;
					var hasHeight = false;
					for (var j=0;j<item.attributes.length;j++) {
						if (item.attributes[j].nodeName=='width') hasWidth = true;
						if (item.attributes[j].nodeName=='height') hasHeight = true;
					}
					if (item.style.width != '') hasWidth = true;
					if (item.style.height != '') hasHeight = true;
					if (hasWidth && hasHeight) {
						handleImage = false;
					}
					if (handleImage) {
						$(item).observe('load', function(event){

							$K.windows[winID].refresh("col1");

							if (real_parent) {
								$K.windows[real_parent.up('.ywindow').id].refresh("col1");
							}
						});
					}
				});
			}

			if (typeof onFinish == 'function') onFinish();
		}

		$K.windows[winID].getContentareaDataFuncs = function ( objects, backend, onFinish ) {

			data_cnt = objects.length;

			// Get all relevant contentareas...
			var rel_contentareas = new Object;
			for (var k=0;k<objects.length;k++) {
				rel_contentareas[objects[k][2]] = objects[k][2];
			}

			// Check if coming from backend
			if (backend) {
				var displaymode = '';
			} else {
				if (displayMode=='dialog') {
					var displaymode = 'dialog';
				} else {
					var displaymode = '';
				}
			}

			// Request cblocks
			new Ajax.Request( $K.appdir+'cblocks', {
				asynchronous: true,
				method: 'post',
				parameters: {
					site: siteID,
					page: pageID,
					objecttype: objectType,
					win_no: this.num,
					displaymode: displaymode,
					data: Object.toJSON(objects),
					us: document.body.id,
					lh: $K.yg_getLastGuiSyncHistoryId()
				},
				onComplete: function(transport) {
					var contentarea_id = 0;
					var real_parent = null;

					var dataHolder = document.createElement('DIV');
					dataHolder.innerHTML = transport.responseText;
					dataHolder = $(dataHolder);

					var children = dataHolder.childElements();

					for (var j=0;j<children.length;j++) {

						if (children[j].tagName != 'UL') continue;

						contentarea_id = parseInt(children[j].readAttribute('yg_id').split('-')[0]);

						// Switch between inserting into a UL located in a dialogwindow and a UL located in a normal window
						real_parent = $(winID+'_scp_'+contentarea_id+'_list');

						// Check if want to insert into a normal window from a dialog window
						if (real_parent==null) {
							$K.log( 'Inserting from Dialog into normal Window...', $K.Log.DEBUG );
							if ($(winID)) {
								var openerWin = $(winID).openerWin;
							} else {
								var openerWin = window.lastOpenerWin;
							}
							real_parent = $(openerWin+'_scp_'+contentarea_id+'_list');
						}

						if (!real_parent.cleared) {
							real_parent.cleared = true;
						}

						real_parent.select('li.loading_placeholder').each(function(liItem) {
							liItem.remove();
						});

						// Check if contentblock is already there
						var item = children[j].firstDescendant();

						$K.log( 'before check of skipitems', $K.Log.DEBUG );
						var skip_item = false;
						var curr_items = real_parent.childElements();
						for (var c=0;c<curr_items.length;c++) {
							var old_lnkid = curr_items[c].id.split('_')[4];
							var new_lnkid = item.id.split('_')[4];
							if (old_lnkid == new_lnkid) {
								skip_item = curr_items[c];
							}
						}

						if (skip_item==false) {
							$K.log( 'Trying to insert ', item, ' into ', real_parent, $K.Log.DEBUG );
							real_parent.insert( item );

							if ( (Prototype.Version == '1.6.0.2') && ((Prototype.Browser.IE)||(Prototype.Browser.WebKit)) && item.down('script') ) {
								$K.warn( 'WORKAROUND FOR SAFARI/IE "evalScripts()" Bug.. (1)', $K.Log.DEBUG );
								item.select('script').each(function(script_item){
									eval( script_item.innerHTML );
								});
							}

						} else {
							$K.log( 'Trying to insert (skip_item) ', skip_item, ' into ', real_parent, $K.Log.DEBUG );
							real_parent.insert( skip_item );

							if ( (Prototype.Version == '1.6.0.2') && ((Prototype.Browser.IE)||(Prototype.Browser.WebKit)) && skip_item.down('script') ) {
								$K.warn( 'WORKAROUND FOR SAFARI/IE "evalScripts()" Bug.. (2)', $K.Log.DEBUG );
								skip_item.select('script').each(function(script_item){
									eval( script_item.innerHTML );
								});
							}

						}

						data_cnt--;
						$K.log( 'data_cnt: ', data_cnt, item.getAttribute('yg_id'), $K.Log.DEBUG );

						if (!backend) {
							if (data_cnt==0) {
								$K.windows[winID].allContentareasLoaded(objects, onFinish, real_parent);
							}
						}
					}
					if (!backend) {
						if (children.length<=1)  {
							if (real_parent) {
								$K.windows[winID].allContentareasLoaded(objects, onFinish, real_parent);
							} else {
								$K.windows[winID].allContentareasLoaded(objects, onFinish, $(winID).down('ul'));
							}
						}
					} else {
						$K.windows[winID].allContentareasLoaded(objects, onFinish, real_parent);
					}
				}
			});

		}

		// Non chunked
		$K.windows[winID].getContentareaDataFuncs( contentData );

		$K.windows[winID].addContentBlock = function (contentblockId, contentareaID, refresh) {

			var site = this.yg_id.split('-')[1];
			contentblockId = contentblockId.split('-')[0];

			var data = Array ( 'noevent', {yg_property: 'addPageContentblock', params: {
				page: pageID,
				site: site,
				contentblockId: contentblockId,
				contentareaID: contentareaID,
				openerRefID: this.num,
				refresh: refresh.toString()
			} } );

			$K.yg_AjaxCallback( data, 'addPageContentblock' );
		}

		$K.windows[winID].addEntryMask = function (entrymaskId, contentareaID, refresh) {

			var site = this.yg_id.split('-')[1];
			entrymaskId = entrymaskId.split('-')[0];

			var backendAction = 'addPageEntrymask';
			if (objectType == 'cblock') {
				backendAction = 'addCBlockEntrymask';
			}

			var data = Array ( 'noevent', {yg_property: backendAction, params: {
				page: pageID,
				site: site,
				entrymaskId: entrymaskId,
				contentareaID: contentareaID,
				openerRefID: this.num,
				refresh: refresh.toString()
			} } );
			$K.yg_AjaxCallback( data, backendAction );
		}

		$K.windows[winID].addFunction = $K.windows[winID].addContentPositioned = function (objectID, targetId, targetPosition, refresh, openerRef, type, sourceElement) {
			if (Object.isUndefined(refresh)) refresh = false;
			if (objectID.substring(0,5) == "dummy") objectID = objectID.substring(5, objectID.length);
			var contentareaID = openerRef.split('_')[3];
			var site = this.yg_id.split('-')[1];

			var copymode = false;
			if (type == "cblock") {
				var backendFunction = 'addPageContentblock';
				if (sourceElement) {
					var winId = sourceElement.up('.ywindow').id;
					var treeMode = $K.windows[winId].loadparams.treemode;

					if ((treeMode == 'pages')||(treeMode == 'pages_with_cblocks')) {
						objectID = sourceElement.id.split('_')[3];
					}

					var srcWinId = 'wid_'+sourceElement.id.split('_')[1];
					if ($K.windows[srcWinId] && $K.windows[srcWinId].tabs.selected == 2) {
						copymode = true;
					}
				}
				if (site == 'cblock') {
					backendFunction = 'addEditContentblock';
				}
			} else if (type == "entrymask") {
				var backendFunction = 'addPositionedPageEntrymask';
				if (objectID.split('-')[1] == 'emblock') {
					copymode = true;
				}
				if (site == 'cblock') {
					var backendFunction = 'addPositionedControlEntrymask';
				}
			}

			var data = Array ( 'noevent', {yg_property: backendFunction, params: {
				page: pageID,
				site: site,
				copymode: copymode,
				entrymaskId: objectID.split('-')[0],
				contentblockId: objectID.split('-')[0],
				contentareaID: contentareaID,
				openerRefID: this.num,
				targetId: targetId,
				targetPosition: targetPosition,
				refresh: refresh.toString()
			} } );
			$K.yg_AjaxCallback( data, backendFunction );

		}


	}

	if (contentareaIDList.length == 0) {
		// Hide loading indicators
		$(winID+'_ywindowinner').removeClassName('tab_loading');  // Big
	}

	$K.windows[winID].addTagtoFormfield = function( targetFormfield, sourceYgID, sourceName ) {
		$K.yg_editControl( $(targetFormfield).down('.title_txt'), '8', true, { yg_id: sourceYgID, title: sourceName, href: '', target: '' } );	// 8 = Tag
		$K.yg_fadeField( $(targetFormfield) );
	};

	$K.windows[winID].addPagetoFormfield = function( targetFormfield, sourceYgID, sourceName ) {
		if ($(targetFormfield).hasClassName('mk_page')) {
			// Page formfield
			$K.yg_editControl( $(targetFormfield).down('.title_txt'), '15', true, { yg_id: sourceYgID, title: sourceName, objecttype: 'page' } );	// 15 = Page
		} else {
			// Link formfield
			$K.yg_editControl( $(targetFormfield).down('.title_txt'), '5', true, { yg_id: sourceYgID, title: sourceName, objecttype: 'page' } );	// 5 = Link
		}
		$K.yg_fadeField( $(targetFormfield) );
	};

	$K.windows[winID].addContentblocktoFormfield = function( targetFormfield, sourceYgID, sourceName ) {
		$K.yg_editControl( $(targetFormfield).down('.title_txt'), '7', true, { yg_id: sourceYgID, title: sourceName } );	// 7 = Contentblock
		$K.yg_fadeField( $(targetFormfield).down().up('.maskedit') );
	};

	// Init preview buttons if in dialog-detail-mode
	if ($K.windows[winID].type == 'dialog') {
		$K.yg_updatePreviewUrls(winID, $K.windows[winID].url);
	}

}


/**
 * Creates the sortable contentareas
 * @param { String } [winID] The displaymode (dialog or empty)
 * @param { String } [coContentarea] The contentarea area to create a sortable from
 * @function
 * @name $K.yg_createSortableContentareas
 */
$K.yg_createSortableContentareas = function( winID, coContentarea ) {
	coContentarea = winID + '_co_contentarea_' + coContentarea;

	Sortable.create( coContentarea, {
		accepts: 'none',
		objectType: 'cblock',
		dropOnEmpty: true,
		constraint: false,
		ghosting: true,
		yg_clone: true,
		starteffect: function(element) {
			Draggable._dragging[element] = true;
			window.$ordersubmitted = false;
		},
		endeffect: function(element) {
			Draggable._dragging[element] = false
		},
		onUpdate: function (element) {
			var temp_wid = $(coContentarea).id.split('_')[1];
			var temp_selNode = $K.windows['wid_'+coContentarea.split('_')[1]].yg_id;
		}
	});
}

/**
 * Removes all loading placeholders
 * @function
 * @name $K.yg_removeLoadingPlaceholder
 */
$K.yg_removeLoadingPlaceholder = function () {
	$$('li.loading_placeholder').each(function(liItem) {
		liItem.remove();
	});
}
