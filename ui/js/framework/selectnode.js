/**
 * Select the  Node (and check permissions, etc.)
 * @param { Element } [node] Selected node.
 * @param { Event } [e] mouse event
 * @param { Boolean } [desel] True if deselecting.
 * @function
 * @name $K.yg_selectNode
 */
$K.yg_selectNode = function(node, e, init) {

	if(!node || node.nodeName == 'A')
		return false;

	var wid = parseInt($(node).up('.ywindow').id.replace(/wid_/g, ''));

	if( typeof (node) == 'object') {

		var nodeid = node.id;
		var prefix = node.id.substring(0, node.id.indexOf('_'));

		if(node.up('.nlstree') || node.up('.darknlstree')) {
			tree = true;
		} else {
			tree = false;
		}

		if((tree) && (nlsTree[prefix + '_tree' + wid + '_tree'] == undefined))
			return false;

		if(tree == false) {
			// list entry selected
			if(node.hasClassName('mk_user') || node.hasClassName('mk_mailing')) {
				var data = node.readAttribute("yg_id").split('-');
			} else {
				var data = node.up(1).readAttribute("yg_id").split('-');
			}
			var page = data[0];
			var site = data[1];
		} else if(nlsTree[prefix+'_tree'+wid+'_tree'].nLst[nodeid].yg_id != null) {
			// tree node is selected
			var data = nlsTree[prefix+'_tree'+wid+'_tree'].nLst[nodeid].yg_id.split('-');
			var page = data[0];
			var site = data[1];
			var url =nlsTree[prefix+'_tree'+wid+'_tree'].nLst[nodeid].ygurl;
		} else {
			// root node is selected
			var data = nlsTree[prefix+'_tree'+wid+'_tree'].nLst[nodeid].orgId.split('_')
			var page = data[0];
			var site = data[1];
			var url = nlsTree[prefix+'_tree'+wid+'_tree'].nLst[nodeid].ygurl;
		}
		if(tree) {
			var type = nlsTree[prefix + '_tree' + wid + '_tree'].yg_type;
		} else {
			var type = node.readAttribute('yg_type');
			if(node.up('li')) {
				type = node.up('li').readAttribute('yg_type');
			} else {
				type = node.readAttribute('yg_type');
			}
			if(type == undefined || type == '') {
				type = $(node).up('.ywindow').yg_type;
			}
		}
	}
	selNode = page + '-' + site;

	var selectOldNode = Prototype.emptyFunction;
	if(( typeof (node) == 'object') && ($K.windows['wid_' + wid].yg_id) && nlsTree[prefix + '_tree' + wid + '_tree']) {
		var objectType = nlsTree[prefix+'_tree'+wid+'_tree'].nLst[prefix + '_tree' + wid + '_treeroot_1'].yg_type;
		var selNodeId = prefix + '_tree' + wid + '_tree' + objectType + '_' + $K.windows['wid_'+wid].yg_id.split('-')[0];
		selectOldNode = function() {
			nlsTree[prefix + '_tree' + wid + '_tree'].selectNode(selNodeId);
		}
	}

	var notDelayed = function() {
		var boundwin = false;
		if($K.windows['wid_' + wid] && ($K.windows['wid_' + wid].boundWindow != undefined) && ($K.windows['wid_' + wid].boundWindow != '') && ($K.windows[$K.windows['wid_' + wid].boundWindow] != undefined))
			boundwin = true;
		var tmparr = new Array();
		if(tree) {
			if (nlsTree[prefix + '_tree' + wid + '_tree']) {
				var tmparr = nlsTree[prefix + '_tree' + wid + '_tree'].getSelNodes();
				if(tmparr[0]) {
					var tmparr = tmparr[0].id.split("_");
				}
				var selNodeCount = nlsTree[prefix + '_tree' + wid + '_tree'].getSelNodes().length;
			}
		} else {
			selNodeCount = $K.yg_getFocusObj($("wid_" + wid)).length;
		}
		if(boundwin && ((selNodeCount > 1) || (selNodeCount == 0) || (tmparr[2] == "treeroot"))) {
			$($K.windows['wid_' + wid].boundWindow).addClassName('boxghost');
			$K.yg_windowResized();
		} else if(boundwin && $($K.windows['wid_' + wid].boundWindow).hasClassName('boxghost')) {
			$($K.windows['wid_' + wid].boundWindow).removeClassName('boxghost');
			$K.yg_windowResized();
		}
		if(selNodeCount < 2)
			if($('wid_' + wid))
				$('wid_' + wid).removeClassName('mk_multiselect');
		if(boundwin)
			$K.windows[$K.windows['wid_' + wid].boundWindow].tabs.init();

		if (selNodeCount == 0) {
			tmparr = $('wid_'+wid+'_buttons').descendants();
			tmparr.each(function(item) {
				btn = item.down('.tree_btn');
				if (btn && (btn.hasClassName('globalfunc') == false)) btn.addClassName('disabled');
			});
		} else if (selNodeCount > 1) {
			$K.yg_toggleTreeButtons('wid_'+wid);
		}
	}

	window.setTimeout(notDelayed, 0);

	if(e) {
		if(e && (e.ctrlKey || e.metaKey || e.shiftKey)) {
			// Is multiselect
			$('wid_' + wid).addClassName('mk_multiselect');
			$K.yg_clearTextSelection();
			return true;
		}
	}

	// Check for open windows with this window as "parent"
	if ( ($K.windows['wid_' + wid].boundWindow != undefined) ||
		 ($('wid_' + wid + '_column2innercontentinner')) ||
		 ($K.windows['wid_' + wid].tab == 'MAILINGS') ||
		($K.windows['wid_' + wid].tab == 'TAGS_TREE')) {
		$K.yg_checkOpenWindows($K.windows['wid_' + wid].yg_id, $K.windows['wid_' + wid].yg_type, {
			onBeforeShow : selectOldNode,
			onSuccess : function() {

				if(nlsTree[prefix + '_tree' + wid + '_tree']) {
					var objectType = nlsTree[prefix+'_tree'+wid+'_tree'].nLst[prefix + '_tree' + wid + '_treeroot_1'].yg_type;

					if(objectType != 'templatefolder') {
						try {
							if(node.down('a').hasClassName('root') || node.down('a').hasClassName('selroot')) {
								nlsTree[prefix + '_tree' + wid + '_tree'].selectNode(prefix + '_tree' + wid + '_treeroot_1');
							} else if(selNode != 'trash-file') {
								//nlsTree[prefix + '_tree' + wid + '_tree'].selectNode(prefix + '_tree' + wid + '_tree' + objectType + '_' + page);
								nlsTree[prefix + '_tree' + wid + '_tree'].selectNode(node.id);
							}
						} catch(e) {
						}
					}
				}

				// Fire late events
				$K.yg_fireLateOnChange();

				$K.windows['wid_' + wid].yg_id = selNode;

				// Check if we have a bound window
				if(($K.windows['wid_' + wid].boundWindow != undefined) && ($K.windows['wid_' + wid].boundWindow != '') && ($K.windows[$K.windows['wid_' + wid].boundWindow] != undefined)) {

					// Update its properties too
					$K.windows[$K.windows['wid_' + wid].boundWindow].yg_id = $K.windows['wid_' + wid].yg_id;
					origWin = $K.windows[$K.windows['wid_' + wid].boundWindow];
					origNo = parseInt($K.windows['wid_' + wid].boundWindow.replace(/wid_/g, ''));

					var completed = function() {
						// Parse custom attributes
						$K.yg_customAttributeHandler($($K.windows['wid_' + wid].boundWindow));
					}
					// Refresh it (do not reload it)!!
					var tabInner = $($K.windows['wid_' + wid].boundWindow + '_innercontent');
					var tabInnerChildren = tabInner.childElements();

					// Empty all Tabs
					for(var k = 0; k < tabInnerChildren.length; k++) {
						tabInnerChildren[k].innerHTML = '';
					}

					// Empty all fixed headers (only if files?)
					//$($K.windows['wid_'+wid].boundWindow+'_headers').innerHTML = '';

					// Empty all bottoms
					var tabBottom = $($K.windows['wid_' + wid].boundWindow + '_bottom');
					var tabBottomChildren = tabBottom.childElements();
					for(var k = 0; k < tabBottomChildren.length; k++) {
						if(!tabBottomChildren[k].hasClassName('minibottom')) {
							tabBottomChildren[k].remove();
							tmphei = $($K.windows['wid_' + wid].boundWindow + '_container').getHeight() + 19;
							$($K.windows['wid_' + wid].boundWindow + '_container').setStyle({
								height : tmphei + 'px'
							});
							$($K.windows['wid_' + wid].boundWindow + '_ywindowinner').setStyle({
								height : tmphei + 'px'
							});
						}
					}

					// Clear 'alreadyloaded'-flag for all tabs
					var tabs = $K.windows[$K.windows['wid_' + wid].boundWindow].tabs.elements;
					for(var k = 0; k < tabs.length; k++) {
						tabs[k]["LOADED"] = false;
					}

					// Select last selected tab
					var lastselected = $K.windows[$K.windows['wid_' + wid].boundWindow].tabs.selected;

					// Set Name & Icon
					var iconstyle = 'page';
					var titlestyle = 'title';
					var icon = '';
					if(tree) {
						var title = node.down('a').innerHTML;
						// Clean from leading and trailing &nbsp;s
						title = title.replace(/&nbsp;/g, ' ').strip();
						var style = node.down('a').className;
						var icon = node.down('.tree_ico').src.split("/");
						var icon = icon[icon.length - 1];
					} else {
						if(node.down('.title')) {
							if(node.down('.title').down('b')) {
								title = node.down('.title').down('b').innerHTML;
							} else if(node.down('.title').down('strong')) {
								title = node.down('.title').down('strong').innerHTML;
							} else {
								title = node.down('.title').innerHTML;
							}
						}
					}

					$K.windows[$K.windows['wid_' + wid].boundWindow].folderselected = 0;
					$K.windows[$K.windows['wid_' + wid].boundWindow].trashcanselected = 0;
					$($K.windows['wid_' + wid].boundWindow + '_buttons').removeClassName('mk_folder');
					$($K.windows['wid_' + wid].boundWindow + '_buttons').removeClassName('mk_trashcan');

					if((icon.indexOf('folder') > -1)) {
						// Folder
						if($K.windows[ $K.windows['wid_'+wid].boundWindow ].tabs.elements[lastselected]["FOLDER"] != 1) {
							lastselected = -1;
							k = 0;
							while(lastselected == -1) {
								if(!($K.windows[ $K.windows['wid_'+wid].boundWindow ].tabs.elements[k]))
									break;
								if($K.windows[ $K.windows['wid_'+wid].boundWindow ].tabs.elements[k]["FOLDER"] > 0)
									lastselected = k;
								k++;
							}
						}
						$K.windows[$K.windows['wid_' + wid].boundWindow].folderselected = 1;
						$K.windows[$K.windows['wid_' + wid].boundWindow].setIcon('folder');
						$($K.windows['wid_' + wid].boundWindow + '_buttons').addClassName('mk_folder');
					} else if((icon.indexOf('trashcan') > -1)) {
						// Trashcan
						lastselected = -1;
						delete $K.windows[ $K.windows['wid_'+wid].boundWindow ].loadparams.pagedir_orderby;
						delete $K.windows[ $K.windows['wid_'+wid].boundWindow ].loadparams.pagedir_orderdir;
						k = 0;
						while(lastselected == -1) {
							if(!($K.windows[ $K.windows['wid_'+wid].boundWindow ].tabs.elements[k]))
								break;
							if($K.windows[ $K.windows['wid_'+wid].boundWindow ].tabs.elements[k]["TRASHCAN"])
								lastselected = k;
							k++;
						}
						$K.windows[$K.windows['wid_' + wid].boundWindow].setIcon('trashcan');
						$K.windows[$K.windows['wid_' + wid].boundWindow].trashcanselected = 1;
						$($K.windows['wid_' + wid].boundWindow + '_buttons').addClassName('mk_trashcan');
					} else if(type == 'page') {
						// Page
						if(icon.indexOf('hidden') != -1)
							iconstyle += 'hidden';
						if(icon.indexOf('inactive') != -1)
							iconstyle += 'inactive';
						if(icon.indexOf('softsync') != -1)
							iconstyle += 'softsync';
						if(style.indexOf('changed') != -1)
							titlestyle += ' changed';
						if(style.indexOf('nowrite') != -1)
							titlestyle += ' nowrite';
						$K.windows[$K.windows['wid_' + wid].boundWindow].setIcon(iconstyle);
					} else if(type == 'cblock') {
						// Contentblocks
						if(style.indexOf('changed') != -1)
							titlestyle += ' changed';
						if(style.indexOf('nowrite') != -1)
							titlestyle += ' nowrite';
						$K.windows[$K.windows['wid_' + wid].boundWindow].setIcon(type);
					} else if((type == 'extpage') || (type == 'extcblock') || (type == 'extfile') || (type == 'extmailing') || (type == 'extimport') || (type == 'extexport') || (type == 'extcolistview')) {
						// Extension
						$K.windows[$K.windows['wid_' + wid].boundWindow].setExtensionIcon(node.down('.iconextension').readAttribute('style'));
						$K.windows[$K.windows['wid_' + wid].boundWindow].setIcon(type);
					} else if(type == 'user') {
						// User
						//$K.windows[$K.windows['wid_' + wid].boundWindow].setIcon('noicon');
						var userpicture;
						// Check if userpicture is empty
						if(!userpicture) {
							userpicture = $K.imgdir + 'content/temp_userpic.png';
						}
						$K.windows[$K.windows['wid_' + wid].boundWindow].setUserHeader(userpicture, $(node).readAttribute('user_name'), $(node).readAttribute('user_company'), nodeid.split('_')[2]);
					} else {
						// Anything else
						$K.windows[$K.windows['wid_' + wid].boundWindow].setIcon(type);
					}

					$K.windows[$K.windows['wid_' + wid].boundWindow].setCaption(title, type);
					$K.windows[$K.windows['wid_' + wid].boundWindow].setCaptionStyle(titlestyle);
					$('wid_' + wid + '_title').writeAttribute('yg_id', $K.windows['wid_' + wid].yg_id);

					if((lastselected != undefined) && (lastselected >= 0) && (($K.windows[ $K.windows['wid_'+wid].boundWindow ].tabs.elements[$K.windows[ $K.windows['wid_'+wid].boundWindow ].tabs.selected ]["TRASHCAN"] == false) || ($K.windows[$K.windows['wid_' + wid].boundWindow].trashcanselected == 1)) && (($K.windows[ $K.windows['wid_'+wid].boundWindow ].tabs.elements[lastselected]["FOLDER"] != 2) && ($K.windows[$K.windows['wid_' + wid].boundWindow].folderselected != 1)) || (($K.windows[ $K.windows['wid_'+wid].boundWindow ].tabs.elements[lastselected]["FOLDER"] > 0) && ($K.windows[$K.windows['wid_' + wid].boundWindow].folderselected == 1))) {

						if (init != false) $K.windows[$K.windows['wid_' + wid].boundWindow].tabs.select(lastselected);

					} else {

						for(var i = 0; i < $K.windows[$K.windows['wid_' + wid].boundWindow].tabs.elements.length; i++) {
							if($K.windows[ $K.windows['wid_'+wid].boundWindow ].tabs.elements[i]["FOLDER"] < 2) {
								if (init != false) $K.windows[$K.windows['wid_' + wid].boundWindow].tabs.select(i);
								break;
							}
						}

					}

					if(type == 'page') {

						$K.yg_updatePreviewUrls($K.windows['wid_' + wid].boundWindow, url);

						// Set Status and buttons for pages (approved/edited)
						var btn = $($K.windows['wid_' + wid].boundWindow + '_buttons').down('.ywindow_bt');
						var btn2 = $($K.windows['wid_' + wid].boundWindow + '_buttons').down('.ywindow_btl');

						// Add button to lookuptable
						btn.firstChild.setAttribute('yg_id', $K.windows['wid_' + wid].yg_id);
						btn.firstChild.yg_id = $K.windows['wid_' + wid].yg_id;
						btn.firstChild.yg_done = undefined;

						if(btn2 && btn2.firstChild) {
							btn2.firstChild.setAttribute('yg_id', $K.windows['wid_' + wid].yg_id + '-' + $K.windows['wid_' + wid].boundWindow.replace(/wid_/, ''));
							btn2.firstChild.yg_id = $K.windows['wid_' + wid].yg_id + '-' + $K.windows['wid_' + wid].boundWindow.replace(/wid_/, '');
						}

					}

					if(type == 'cblock') {
						// Set Status and buttons for bclocks (approved/edited)
						var btn = $($K.windows['wid_' + wid].boundWindow + '_buttons').down('.ywindow_bt');
						var btn2 = $($K.windows['wid_' + wid].boundWindow + '_buttons').down('.ywindow_btl');

						// Set path to previews
						if(btn.down().hasClassName('clear') == false) {
							btn.next().down().onclick = function(ev) {
								Event.stop(ev);
								$K.yg_preview({
									objecttype : 'cblock',
									id : page
								});
							}
							btn.next().down().href = $K.appdir + "?preview=1&objecttype=cblock&id=" + page;
							btn.down().onclick = function(ev) {
								Event.stop(ev);
								$K.yg_preview({
									objecttype : 'cblock',
									id : page,
									version : 'working'
								});
							}
							btn.down().href = $K.appdir + "?preview=1&objecttype=cblock&id=" + page + "&version=working";
						}

						// Add button to lookuptable
						btn.firstChild.setAttribute('yg_id', $K.windows['wid_' + wid].yg_id);
						btn.firstChild.yg_id = $K.windows['wid_' + wid].yg_id;
						btn.firstChild.yg_done = undefined;

						if(btn2 && btn2.firstChild) {
							btn2.firstChild.setAttribute('yg_id', $K.windows['wid_' + wid].yg_id + '-' + $K.windows['wid_' + wid].boundWindow.replace(/wid_/, ''));
							btn2.firstChild.yg_id = $K.windows['wid_' + wid].yg_id + '-' + $K.windows['wid_' + wid].boundWindow.replace(/wid_/, '');
							$K.log('FC: ', btn2.firstChild.yg_done, $K.Log.DEBUG);
						}
					}

					$K.yg_customAttributeHandler($($K.windows['wid_' + wid].boundWindow + '_buttons'));

				}

				// Contentblocks Insertcontent
				if(($K.windows['wid_' + wid].tab == 'PAGES_TREE_EXTRAS') && (selNode.split('-')[1].startsWith('cblock'))) {

					$('wid_' + wid + '_column2innercontentinner').innerHTML = '';
					$('wid_' + wid + '_column2innercontentinner').addClassName('tab_loading');

					var objectType = 'page';
					new Ajax.Updater('wid_' + wid + '_column2innercontentinner', $K.appdir + 'cblocks', {
						evalScripts : true,
						asynchronous : true,
						method : 'post',
						parameters : {
							win_no : wid,
							co : selNode,
							displaymode : 'dialog',
							us : document.body.id,
							lh : $K.yg_getLastGuiSyncHistoryId(),
							site : 'cblock_copy',
							page : 'cblock_copy'
						},
						onComplete : function() {
							$K.windows["wid_" + wid].refresh("col2");
							$('wid_' + wid + '_column2innercontentinner').removeClassName('tab_loading');
							$K.yg_customAttributeHandler($('wid_' + wid + '_column2innercontentinner'))
						},
						onSuccess : function(transport) {

						}
					});
					return true;
				}

				// Pages Insertcontent
				if(($K.windows['wid_' + wid].tab == 'PAGES_TREE_EXTRAS') && !isNaN(parseInt(selNode.split('-')[1]))) {

					$('wid_' + wid + '_column2innercontentinner').innerHTML = '';
					$('wid_' + wid + '_column2innercontentinner').addClassName('tab_loading');

					var objectType = 'page';
					new Ajax.Updater('wid_' + wid + '_column2innercontentinner', $K.appdir + 'tab_CONTENT', {
						asynchronous : true,
						evalScripts : true,
						method : 'post',
						parameters : {
							yg_id : selNode,
							yg_type : objectType,
							win_no : wid,
							displaymode : 'dialog',
							us : document.body.id,
							lh : $K.yg_getLastGuiSyncHistoryId()
						},
						onComplete : function() {
							$K.yg_initContentTab.defer($K.windows['wid_' + wid].yg_id, '', $K.windows['wid_' + wid].id, 'page');
						}
					});
					return true;
				}

				// Files
				if((node.up('.ywindow').hasClassName('ydialog')) && !isNaN(parseInt(selNode.split('-')[0])) && (selNode.split('-')[1] == 'file')) {

					if($('wid_' + wid + '_column2innercontentinner'))
						$('wid_' + wid + '_column2innercontentinner').update('');
					$K.yg_hideColumn2Bottom('wid_' + wid);

					if($('wid_' + wid + '_column2innercontentinner'))
						$('wid_' + wid + '_column2innercontentinner').addClassName('tab_loading');

					var view = 'listview';
					if($('wid_' + wid).hasClassName('thumbview')) {
						view = 'thumbview';
					}

					if(!$('wid_' + wid + '_column2innercontentinner'))
						return;
					parameters = $K.windows['wid_' + wid].loadparams;
					parameters['yg_id'] = selNode;
					parameters['wid'] = 'wid_' + wid;
					parameters['displaymode'] = 'dialog';
					parameters['view'] = view;
					parameters['us'] = document.body.id;
					parameters['lh'] = $K.yg_getLastGuiSyncHistoryId();

					new Ajax.Updater('wid_' + wid + '_column2innercontentinner', $K.appdir + 'tab_FOLDERCONTENT', {
						asynchronous : true,
						evalScripts : true,
						method : 'post',
						parameters : parameters,
						onComplete : function() {

							$K.yg_customAttributeHandler($('wid_' + wid + '_ywindowinner'));
							$K.yg_customAttributeHandler($('wid_' + wid + '_column2innercontent'));

							var thumbContainer = $('wid_' + wid + '_column2innercontentinner');

							if($('wid_' + wid).hasClassName('filelist3')) {
								$K.yg_loadThumbPreview(thumbContainer, '.mk_filelist img');
							} else if($('wid_' + wid).hasClassName('thumbview')) {
								$K.yg_loadThumbPreview(thumbContainer, '.mk_filepreview img');
							}

							$K.windows['wid_' + wid].refresh();
							$('wid_' + wid + '_column2innercontentinner').removeClassName('tab_loading');

						}
					});
					return true;
				}

				// Templates
				if((node.up('.ywindow').hasClassName('ydialog')) && !isNaN(parseInt(selNode.split('-')[0])) && (selNode.split('-')[1] == 'template')) {

					if($('wid_' + wid + '_column2innercontentinner')) {
						$('wid_' + wid + '_column2innercontentinner').addClassName('tab_loading');

						$('wid_' + wid + '_column2innercontentinner').innerHTML = "";

						new Ajax.Updater('wid_' + wid + '_column2innercontentinner', $K.appdir + 'template_info/', {
							onComplete : function() {
								$('wid_' + wid + '_column2innercontentinner').removeClassName('tab_loading');
								$K.windows['wid_' + wid].init();
							},
							asynchronous : true,
							evalScripts : true,
							method : 'post',
							onlyLatestOfClass : wid + '_TAB_CONTENT',
							parameters : {
								template : selNode.split('-')[0],
								us : document.body.id,
								lh : $K.yg_getLastGuiSyncHistoryId()
							}
						});
					}
					return true;
				}

				// FIXME --> no background request when only one column
				// Roles in dialog (supress backend request)
				if((node.up('.ywindow').hasClassName('ydialog')) && (type == 'usergroup')) {
					return true;
				}
				// Return in file manager (boundwindow) has not finished loading/init
				if((type == 'file') && ($($K.windows['wid_' + wid].boundWindow)) && (!$($K.windows['wid_' + wid].boundWindow).down('.ywindowfilter'))) {
					return false;
				}

				// Return if we are in a Page-Chooser-Dialogue
				if((type == 'page') && (!($K.windows[node.up('.ywindow').id].boundWindow))) {
					return false;
				}

				var backendAction;

				switch (type) {
					default:
					case 'site':
						backendAction = 'siteSelectNode';
						break;
					case 'page':
						backendAction = 'pageSelectNode';
						break;
					case 'file':
						backendAction = 'fileSelectNode';
						break;
					case 'tag':
						backendAction = 'tagSelectNode';
						break;
					case 'template':
						backendAction = 'templateSelectNode';
						break;
					case 'entrymask':
						backendAction = 'entrymaskSelectNode';
						break;
					case 'extension':
						backendAction = 'extensionSelectNode';
						break;
					case 'cblock':
						backendAction = 'contentblockSelectNode';
						break;
					case 'usergroup':
						backendAction = 'usergroupsSelectNode';
						break;
					case 'user':
						backendAction = 'userSelectNode';
						break;
					case 'mailing':
						backendAction = 'mailingSelectNode';
						break;
				}

				var data = Array('noevent', {
					yg_property : backendAction,
					params : {
						node : selNode.split('-')[0],
						siteID : selNode.split('-')[1],
						wid : wid
					}
				});
				var backendDelayed = function() {
					if(!$K.windows["wid_" + wid])
						return;
					if(($K.yg_getFocusObj($("wid_" + wid + "_" + $K.windows["wid_" + wid].tab.toUpperCase())).indexOf(node) > -1) || $("wid_" + wid + "_" + $K.windows["wid_" + wid].tab.toUpperCase()).down('.wdgt_tree')) {
						$K.yg_AjaxCallback(data, backendAction);
					}
				}
				window.setTimeout(backendDelayed, 0);
			}
		});
	} else {
		$K.windows['wid_' + wid].yg_id = selNode;
	}

	$(node).up('.ywindow').removeClassName('mk_multiselect');

}
