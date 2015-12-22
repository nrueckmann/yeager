/**
 * Inits usermgr window
 * @param { String } [wndid] Id of parent window
 */
$K.yg_initUserMgr = function(wndid) {

	$K.windows[wndid].tablecols = true;

	innerwidth = $(wndid + "_innercontent").getWidth();
	colwidth = Math.floor((innerwidth) / 3) - 9;

	$K.yg_changeCSS('#' + wndid + ' .fcol_name', 'width', colwidth + 'px', true);
	$K.yg_changeCSS('#' + wndid + ' .fcol_company', 'width', (colwidth + 1) + 'px', true);
	$K.yg_changeCSS('#' + wndid + ' .fcol_email', 'width', (colwidth + 1) + 'px', true);

	$K.yg_refreshUserMgr(wndid);

}

/**
 * Edit the selected user
 * @param { Element } [which] Reference to the edit-actionbutton
 * @param { Event } [e] Click-event
 */
$K.yg_editUser = function(which, e) {
	which = $(which);
	var winId = which.up('.ywindow').id;
	var trItem = which.up('tr');

	trItem.up('tbody').select('tr.cntblockfocus').each(function(item) {
		item.removeClassName('cntblockfocus');
	});

	trItem.removeClassName('cntblock');
	trItem.addClassName('cntblockfocus');
	$K.yg_currentfocusobj = [trItem];
	$K.yg_selectNode(trItem, e);
	$($K.windows[winId].boundWindow).removeClassName('boxghost');
}

/**
 * Refrehs usermgr window
 * @param { String } [wndid] Id of parent window
 */
$K.yg_refreshUserMgr = function(wndid) {
	var count = parseInt($(wndid + '_userlist_table').readAttribute('count'), 10);
	var countFiltered = parseInt($(wndid + '_userlist_table').readAttribute('count_filtered'), 10);
	$(wndid + '_objcnt').update(count);
	if (count != countFiltered) {
		$(wndid + '_objcnt_filtered').update(countFiltered);
		$(wndid + '_objcnt_filtered').up('span').show();
	} else {
		$(wndid + '_objcnt_filtered').up('span').hide();
	}

	if ($K.windows[wndid].yg_id) {
		var userID = $K.windows[wndid].yg_id.split('-')[0];

		if (!$('users_' + wndid.replace(/wid_/g, '') + '_' + userID)) {
			$K.windows[wndid].yg_id = userID + '-user';
			if ($K.windows[$K.windows[wndid].boundWindow].yg_id != userID + '-user') {
				$K.windows[$K.windows[wndid].boundWindow].yg_id = userID + '-user';
				$K.windows[$K.windows[wndid].boundWindow].tabs.select($K.windows[$K.windows[wndid].boundWindow].tabs.selected);
				$($K.windows[wndid].boundWindow).removeClassName('boxghost');
			}
		} else {
			if ($K.windows[$K.windows[wndid].boundWindow].yg_id != userID + '-user') {
				$K.yg_selectNode($('users_' + wndid.replace(/wid_/g, '') + '_' + userID));
			}
			$K.yg_blockSelect($('users_' + wndid.replace(/wid_/g, '') + '_' + userID), null);
		}
	}

	$K.windows[wndid].refresh();

	TableKit.unloadTable(wndid + "_tablecontent");
	TableKit.unloadTable(wndid + "_tablehead");
	TableKit.Sortable.init(wndid + "_tablehead");
	TableKit.Sortable.init(wndid + "_tablecontent");
	TableKit.Resizable.init(wndid + "_tablehead");

	if ($K.windows[wndid].loadparams.pagedir_orderby == 'lastname' && $K.windows[wndid].loadparams.pagedir_orderdir == 1) {
		$(wndid + "_tablehead").down('.fcol_name').addClassName("sortcol");
		$(wndid + "_tablehead").down('.fcol_name').addClassName("sortasc");
		TableKit.Sortable.sort(wndid + "_tablecontent", $K.windows[wndid].loadparams.pagedir_orderby, 1);
	}
	TableKit.Sortable.sort(wndid + "_tablecontent", $K.windows[wndid].loadparams.pagedir_orderby, $K.windows[wndid].loadparams.pagedir_orderdir * -1);
	TableKit.Sortable.sort(wndid + "_tablecontent", $K.windows[wndid].loadparams.pagedir_orderby, 1);


	/*// hotfix, not sorting correctly when sorted desc by title
	 if (($K.windows[wndid].loadparams.pagedir_orderby == 2) && ($K.windows[wndid].loadparams.pagedir_orderdir == 1)) {
	 TableKit.Sortable.sort(wndid+"_tablecontent", $K.windows[wndid].loadparams.pagedir_orderby, 1);
	 }*/
}

$K.yg_focusUserSearch = function(which, e) {
	var preset = which.readAttribute('data-preset');
	if (which.value == preset) {
		which.value = '';
		which.removeClassName('preset');
	}
}

$K.yg_blurUserSearch = function(which, e) {
	var preset = which.readAttribute('data-preset');
	if (!which.hasChanged) {
		which.value = preset;
		which.addClassName('preset');
	}
}

$K.yg_resetUserSearch = function(which, e) {
	which.hasChanged = false;
	$K.yg_blurUserSearch(which, e);
}

$K.yg_changeUserSearch = function(which, e) {
	which.hasChanged = true;
	if (which.value == '') {
		which.hasChanged = false;
	}
	$K.yg_changeUserFilter(which, e);
}


/**
 * Initializes the user-permissions tab
 * @param { String } [pageID] The page id
 * @param { String } [parentWinID] The parent window id
 * @param { Boolean } [readOnly] Specifies read-only mode
 * @param { Integer } [maxLevels] Number of levels to load initially
 * @function
 * @name $K.yg_initUserPermissionsTab
 */
$K.yg_initUserPermissionsTab = function(pageID, parentWinID, readOnly, maxLevels) {

	maxLevels--;

	// Opens a tag-chooser dialog
	window.openAddUserGroupWindow = function(openerReference) {

		var site = $K.windows['wid_' + parentWinID].yg_id.split('-');
		site = site[1];

		new Ajax.Updater('dialogcontainer', $K.appdir + 'window?wt=dialog&display=usergroups',
			{
				asynchronous:true,
				evalScripts:true,
				method:'post',
				insertion:'bottom',
				onComplete:function() {
				},
				parameters:{
					opener_reference:openerReference,
					us:document.body.id,
					lh:$K.yg_getLastGuiSyncHistoryId()
				}
			});

	}

	// Observe clicks & mouseover/out in tree
	$('wid_' + parentWinID).select('div.perm_line_header', 'div.mk_innertable_container').each(function(item) {
		var columnHighlight = function(e) {
			var target = e.findElement();

			if (target.hasClassName('mk_rread') ||
				target.hasClassName('mk_rwrite') ||
				target.hasClassName('mk_rdelete') ||
				target.hasClassName('mk_rsub') ||
				target.hasClassName('mk_rstage') ||
				target.hasClassName('mk_rmoderate') ||
				target.hasClassName('mk_rcomment') ||
				target.hasClassName('mk_rsend') ||
				target.hasClassName('mk_rread_head') ||
				target.hasClassName('mk_rwrite_head') ||
				target.hasClassName('mk_rdelete_head') ||
				target.hasClassName('mk_rsub_head') ||
				target.hasClassName('mk_rstage_head') ||
				target.hasClassName('mk_rmoderate_head') ||
				target.hasClassName('mk_rcomment_head') ||
				target.hasClassName('mk_rsend_head')) {
				var currentColumn;
				var outListContainer = target.up('.usergroup_list');
				target.className.split(' ').each(function(classItem) {
					if (classItem.startsWith('mk_r')) {
						currentColumn = classItem.replace(/_head/g, '');
					}
				});
				switch (e.type) {
					case 'mouseover':
						outListContainer.addClassName('mk_hilite_' + currentColumn.replace(/mk_/g, ''));
						break;
					case 'mouseout':
						outListContainer.removeClassName('mk_hilite_rread')
							.removeClassName('mk_hilite_rwrite')
							.removeClassName('mk_hilite_rdelete')
							.removeClassName('mk_hilite_rsub')
							.removeClassName('mk_hilite_rstage')
							.removeClassName('mk_hilite_rmoderate')
							.removeClassName('mk_hilite_rcomment')
							.removeClassName('mk_hilite_rsend');
						break;
				}
			}
		}
		item.stopObserving('mouseover');
		item.stopObserving('mouseout');
		item.observe('mouseover', columnHighlight);
		item.observe('mouseout', columnHighlight);

		// Observe clicks
		item.stopObserving('click');
		item.observe('click', function(e) {
			var target = e.findElement();

			// Observe clicks in tree
			if (target.hasClassName('perm_tree_minusb') ||
				target.hasClassName('perm_tree_minusnb') ||
				target.hasClassName('perm_tree_minusnl') ||
				target.hasClassName('perm_tree_plusb') ||
				target.hasClassName('perm_tree_plusnb') ||
				target.hasClassName('perm_tree_plusnl')) {

				var currLevel = target.readAttribute('currentlevel');

				if (!target.up('.perm_line').alreadyLoaded) {
					var yg_id = target.up('.perm_line').readAttribute('yg_id');

					// Set node to "loading..."
					var nodeTitle = target.up('.perm_line').down('.perm_title');
					nodeTitle.oldTitle = nodeTitle.innerHTML;
					nodeTitle.update($K.TXT('TXT_LOADING') + '...');

					var leadingLines = '';
					target.up('.perm_line').down('.perm_tree_lines').select('div').each(function(item) {
						if (item.hasClassName('perm_tree_line') ||
							item.hasClassName('perm_tree_minusb') ||
							item.hasClassName('perm_tree_plusb')) {
							leadingLines += '1';
						} else {
							leadingLines += '0';
						}
					});

					var objectType = target.up('.perm_container').readAttribute('yg_type');

					new Ajax.Request($K.appdir + 'permission_nodes', {
						onSuccess:function(t) {
							nodeTitle.update(nodeTitle.oldTitle);
							var subNodes = new Element('div', {className:'perm_line_container' });
							subNodes.update(t.responseText);
							if (target.up('.perm_line').next() && target.up('.perm_line').next().hasClassName('perm_line_container')) {
								subNodes.className = target.up('.perm_line').next().className;
								target.up('.perm_line').next().replace(subNodes);
							} else {
								target.up('.perm_line').insert({after:subNodes});
							}
							var currentLine = target.up('.perm_line');
							if (target.className.startsWith('perm_tree_minus')) {
								target.className = target.className.replace(/minus/g, 'plus');
								if (currentLine.down('.perm_tree_icon_folder_open')) {
									currentLine.down('.perm_tree_icon_folder_open').addClassName('perm_tree_icon_folder');
									currentLine.down('.perm_tree_icon_folder_open').removeClassName('perm_tree_icon_folder_open');
								}
							} else if (target.className.startsWith('perm_tree_plus')) {
								target.className = target.className.replace(/plus/g, 'minus');
								if (currentLine.down('.perm_tree_icon_folder')) {
									currentLine.down('.perm_tree_icon_folder').addClassName('perm_tree_icon_folder_open');
									currentLine.down('.perm_tree_icon_folder').removeClassName('perm_tree_icon_folder');
								}
							}
							target.up('.perm_line').alreadyLoaded = true;
							$K.windows['wid_' + parentWinID].refresh();
						},
						onFailure:function() {
						},
						parameters:{
							yg_type:objectType,
							yg_id:yg_id,
							role_id:$K.windows['wid_' + parentWinID].yg_id,
							leadingLines:leadingLines,
							us:document.body.id,
							lh:$K.yg_getLastGuiSyncHistoryId()
						}
					});
				} else {
					var currentLine = target.up('.perm_line');
					var subNodesBlock = currentLine.next();
					if (target.className.startsWith('perm_tree_minus')) {
						target.className = target.className.replace(/minus/g, 'plus');
						if (currentLine.down('.perm_tree_icon_folder_open')) {
							currentLine.down('.perm_tree_icon_folder_open').addClassName('perm_tree_icon_folder');
							currentLine.down('.perm_tree_icon_folder_open').removeClassName('perm_tree_icon_folder_open');
						}
						subNodesBlock.hide();
					} else if (target.className.startsWith('perm_tree_plus')) {
						target.className = target.className.replace(/plus/g, 'minus');
						subNodesBlock.show();
						if (currentLine.down('.perm_tree_icon_folder')) {
							currentLine.down('.perm_tree_icon_folder').addClassName('perm_tree_icon_folder_open');
							currentLine.down('.perm_tree_icon_folder').removeClassName('perm_tree_icon_folder');
						}
					}
					$K.windows['wid_' + parentWinID].refresh();
				}

			}

			// Observe clicks in checkbox area
			if (target.hasClassName('perm_ok') ||
				target.hasClassName('perm_nok') ||
				target.hasClassName('perm_user_ok') ||
				target.hasClassName('perm_user_nok')) {

				if (target.hasClassName('mk_readonly')) {
					return;
				}

				target.down('input').addClassName('mk_changed');
				var newState, oldState;
				var parentContainer = target.up('.perm_line_container');

				var backgroundStyle = (target.getStyle('backgroundPosition')) || (target.currentStyle.backgroundPositionY);
				if (!backgroundStyle && target.currentStyle) {
					backgroundStyle = target.getStyle('backgroundPositionY');
					if (backgroundStyle == 'bottom') {
						backgroundStyle = '50% 100%';
					} else {
						backgroundStyle = '50% 0%';
					}
				}
				if (Prototype.Browser.IE) {
					if (backgroundStyle == 'bottom') {
						backgroundStyle = '50% 100%';
					} else {
						backgroundStyle = '50% 0%';
					}
				}

				var currentRight = '';
				if (target.hasClassName('mk_rread')) {
					currentRight = 'mk_rread';
				} else if (target.hasClassName('mk_rwrite')) {
					currentRight = 'mk_rwrite';
				} else if (target.hasClassName('mk_rdelete')) {
					currentRight = 'mk_rdelete';
				} else if (target.hasClassName('mk_rsub')) {
					currentRight = 'mk_rsub';
				} else if (target.hasClassName('mk_rstage')) {
					currentRight = 'mk_rstage';
				} else if (target.hasClassName('mk_rmoderate')) {
					currentRight = 'mk_rmoderate';
				} else if (target.hasClassName('mk_rcomment')) {
					currentRight = 'mk_rcomment';
				} else if (target.hasClassName('mk_rsend')) {
					currentRight = 'mk_rsend';
				}
				if (backgroundStyle == '50% 100%') {
					target.down('input').value = '0';
					target.removeClassName('perm_user_ok');
					target.addClassName('perm_user_nok');
					newState = 'removeClassName';
					oldState = 'addClassName';
				} else if (backgroundStyle == '50% 0%') {
					target.down('input').value = '1';
					target.removeClassName('perm_user_nok');
					target.addClassName('perm_user_ok');
					newState = 'addClassName';
					oldState = 'removeClassName';
				}
				var currentLine = target.up('.perm_line');
				switch (currentRight) {
					case 'mk_rread':
						if (newState == 'removeClassName') {
							currentLine.select('.mk_rwrite', '.mk_rdelete', '.mk_rsub', '.mk_rstage', '.mk_rmoderate', '.mk_rcomment', '.mk_rsend').each(function(subItem) {
								subItem.down('input').value = '0';
								subItem.removeClassName('perm_user_ok');
								subItem.addClassName('perm_user_nok');
							});
						}
						break;
					case 'mk_rdelete':
						if (newState == 'addClassName') {
							currentLine.select('.mk_rwrite').each(function(subItem) {
								subItem.down('input').value = '1';
								subItem.removeClassName('perm_user_nok');
								subItem.addClassName('perm_user_ok');
							});
						}
					case 'mk_rstage':
					case 'mk_rmoderate':
					case 'mk_rcomment':
					case 'mk_rsend':
						if (newState == 'addClassName') {
							currentLine.select('.mk_rread').each(function(subItem) {
								subItem.down('input').value = '1';
								subItem.removeClassName('perm_user_nok');
								subItem.addClassName('perm_user_ok');
							});
						}
						break;
				}

				// Always set RREAD on all parent nodes if something is set
				var currObjectType = target.up('.perm_container').readAttribute('yg_type');
				if ((newState == 'addClassName') && (currObjectType != 'usergroups')) {
					//if ((cntRights != cntChecked) && (currObjectType != 'usergroups')) {
					var parentLines = new Array();
					currentLine.ancestors().each(function(item) {
						if (item.up('div.perm_container[yg_type]') && item.previous()) {
							parentLines.push(item.previous());
						}
					});
					parentLines.each(function(item) {
						item.select('.mk_rread').each(function(subItem) {
							subItem.down('input').addClassName('mk_changed');
							subItem.removeClassName('perm_user_nok');
							subItem.addClassName('perm_user_ok');
						});
					});
				}

				var subNodeContainer = target.up('.perm_line').next();
				if (subNodeContainer && subNodeContainer.hasClassName('perm_line_container')) {

					var cleanUserSetPermissions = function(userPermission) {
						// Remove all custom set permissions on subnodes (for this right)
						subNodeContainer.select('.mk_' + userPermission + '.perm_user_ok', '.mk_' + userPermission + '.perm_user_nok').each(function(subItem) {
							subItem.removeClassName('perm_user_ok');
							subItem.removeClassName('perm_user_nok');
						});
						subNodeContainer.select('.mk_hasno' + userPermission, '.mk_has' + userPermission).each(function(subItem) {
							subItem.removeClassName('mk_hasno' + userPermission);
							subItem.removeClassName('mk_has' + userPermission);
						});
						subNodeContainer.select('.mk_' + userPermission + ' input').each(function(subItem) {
							subItem.removeClassName('mk_changed');
							subItem.value = '';
						});
					}

					switch (currentRight) {
						case 'mk_rread':
							cleanUserSetPermissions('rread');
							subNodeContainer[newState]('mk_hasrread');
							subNodeContainer[oldState]('mk_hasnorread');
							if (newState == 'removeClassName') {
								cleanUserSetPermissions('rwrite');
								cleanUserSetPermissions('rdelete');
								cleanUserSetPermissions('rsub');
								cleanUserSetPermissions('rstage');
								cleanUserSetPermissions('rsend');
								subNodeContainer[newState]('mk_hasrwrite');
								subNodeContainer[oldState]('mk_hasnorwrite');
								subNodeContainer[newState]('mk_hasrdelete');
								subNodeContainer[oldState]('mk_hasnordelete');
								subNodeContainer[newState]('mk_hasrsub');
								subNodeContainer[oldState]('mk_hasnorsub');
								subNodeContainer[newState]('mk_hasrstage');
								subNodeContainer[oldState]('mk_hasnorstage');
								subNodeContainer[newState]('mk_hasrsend');
								subNodeContainer[oldState]('mk_hasnorsend');
								subNodeContainer[newState]('mk_hasrmoderate');
								subNodeContainer[oldState]('mk_hasnormoderate');
								subNodeContainer[newState]('mk_hasrcomment');
								subNodeContainer[oldState]('mk_hasnorcomment');
							}
							break;
						case 'mk_rwrite':
							cleanUserSetPermissions('rwrite');
							subNodeContainer[newState]('mk_hasrwrite');
							subNodeContainer[oldState]('mk_hasnorwrite');
							break;
						case 'mk_rdelete':
							cleanUserSetPermissions('rdelete');
							subNodeContainer[newState]('mk_hasrdelete');
							subNodeContainer[oldState]('mk_hasnordelete');
							break;
						case 'mk_rsub':
							cleanUserSetPermissions('rsub');
							subNodeContainer[newState]('mk_hasrsub');
							subNodeContainer[oldState]('mk_hasnorsub');
							break;
						case 'mk_rstage':
							cleanUserSetPermissions('rstage');
							subNodeContainer[newState]('mk_hasrstage');
							subNodeContainer[oldState]('mk_hasnorstage');
							break;
						case 'mk_rmoderate':
							cleanUserSetPermissions('rmoderate');
							subNodeContainer[newState]('mk_hasrmoderate');
							subNodeContainer[oldState]('mk_hasnormoderate');
							break;
						case 'mk_rcomment':
							cleanUserSetPermissions('rcomment');
							subNodeContainer[newState]('mk_hasrcomment');
							subNodeContainer[oldState]('mk_hasnorcomment');
							break;
						case 'mk_rsend':
							cleanUserSetPermissions('rsend');
							subNodeContainer[newState]('mk_hasrsend');
							subNodeContainer[oldState]('mk_hasnorsend');
							break;
					}
					if ((currentRight != 'mk_rread') && (newState == 'addClassName')) {
						cleanUserSetPermissions('rread');
						subNodeContainer[newState]('mk_hasrread');
						subNodeContainer[oldState]('mk_hasnorread');
					}
					if ((currentRight == 'mk_rdelete') && (newState == 'addClassName')) {
						cleanUserSetPermissions('rwrite');
						subNodeContainer[newState]('mk_hasrwrite');
						subNodeContainer[oldState]('mk_hasnorwrite');
					}
				}

			}

		});
	});

}


/**
 * Switches the site in permissions (for pages)
 * @param { Element } [which] Reference to the dropdown
 */
$K.yg_switchPermissionSite = function(which) {
	var winRef = which.up('.ywindow');
	var winID = winRef.id;
	var newSite = which.up('.dropdown').next('input[type=hidden]').value;

	var innerTable = which.up('.perm_line_header').next('.mk_innertable_container');

	var alreadyLoaded = false;
	innerTable.select('.mk_innertable').each(function(item) {
		item.hide();
		if (item.id == (innerTable.id + '_' + newSite)) {
			alreadyLoaded = true;
		}
	});

	if (alreadyLoaded) {
		$(innerTable.id + '_' + newSite).show();
		$K.windows[winID].refresh();
	} else {
		$(winID + '_ywindowinner').addClassName('tab_loading');

		new Ajax.Updater(innerTable, $K.appdir + 'tab_usergroup_pages_inner',
			{
				asynchronous:true,
				evalScripts:true,
				method:'post',
				insertion:'top',
				onComplete:function() {
					$(winID + '_ywindowinner').removeClassName('tab_loading');
					$K.windows[winID].refresh();
				},
				parameters:{
					refresh:0,
					seq:winID.replace(/wid_/g, ''),
					wid:winID,
					yg_id:$K.windows[winID].yg_id,
					yg_type:'usergroup',
					site:newSite,
					us:document.body.id,
					lh:$K.yg_getLastGuiSyncHistoryId()
				}
			});
	}

}


/**
 * Checks alls permissions in a line
 * @param { Element } [which] Reference to the dropdown
 */
$K.yg_checkAllPermissions = function(which) {
	var currentLine = which.up('.perm_line');
	var subNodeContainer = currentLine.next();

	var addClasses = ['mk_hasrread', 'mk_hasrwrite', 'mk_hasrdelete', 'mk_hasrsub', 'mk_hasrstage', 'mk_hasrmoderate', 'mk_hasrcomment', 'mk_hasrsend'];
	var removeClasses = ['mk_hasnorread', 'mk_hasnorwrite', 'mk_hasnordelete', 'mk_hasnorsub', 'mk_hasnorstage', 'mk_hasnormoderate', 'mk_hasnorcomment', 'mk_hasnorsend'];
	var cntRights = currentLine.down('.perm_chkboxes').select('div').length - 1;
	var cntChecked = currentLine.down('.perm_chkboxes').select('input[value=1]').length;

	if ((cntRights == cntChecked)) {
		var tmp = addClasses;
		addClasses = removeClasses;
		removeClasses = tmp;
	}

	currentLine.down('.perm_chkboxes').select('div').each(function(subItem) {
		if (!subItem.hasClassName('perm_select_all')) {
			if ((cntRights == cntChecked)) {
				if (!subItem.hasClassName('mk_readonly')) {
					subItem.down('input').value = '0';
					subItem.removeClassName('perm_user_ok');
					subItem.addClassName('perm_user_nok');
				}
			} else {
				subItem.down('input').value = '1';
				subItem.removeClassName('perm_user_nok');
				subItem.addClassName('perm_user_ok');
			}
			subItem.down('input').addClassName('mk_changed');
		}
	});

	// Always set RREAD on all parent nodes if something is set
	var currObjectType = which.up('.perm_container').readAttribute('yg_type');
	if ((cntRights != cntChecked) && (currObjectType != 'usergroups')) {
		var parentLines = new Array();
		currentLine.ancestors().each(function(item) {
			if (item.up('div.perm_container[yg_type]') && item.previous()) {
				parentLines.push(item.previous());
			}
		});
		parentLines.each(function(item) {
			item.select('.mk_rread').each(function(subItem) {
				subItem.down('input').addClassName('mk_changed');
				subItem.removeClassName('perm_user_nok');
				subItem.addClassName('perm_user_ok');
			});
		});
	}

	if (subNodeContainer && subNodeContainer.hasClassName('perm_line_container')) {

		var cleanUserSetPermissions = function(userPermission) {
			// Remove all custom set permissions on subnodes (for this right)
			subNodeContainer.select('.mk_' + userPermission + '.perm_user_ok', '.mk_' + userPermission + '.perm_user_nok').each(function(subItem) {
				subItem.removeClassName('perm_user_ok');
				subItem.removeClassName('perm_user_nok');
			});
			subNodeContainer.select('.mk_hasno' + userPermission, '.mk_has' + userPermission).each(function(subItem) {
				subItem.removeClassName('mk_hasno' + userPermission);
				subItem.removeClassName('mk_has' + userPermission);
			});
			subNodeContainer.select('.mk_' + userPermission + ' input').each(function(subItem) {
				subItem.removeClassName('mk_changed');
				subItem.value = '';
			});
		}

		cleanUserSetPermissions('rread');
		cleanUserSetPermissions('rwrite');
		cleanUserSetPermissions('rdelete');
		cleanUserSetPermissions('rsub');
		cleanUserSetPermissions('rstage');
		cleanUserSetPermissions('rmoderate');
		cleanUserSetPermissions('rcomment');
		cleanUserSetPermissions('rsend');

		var isReadOnly = (subNodeContainer.previous().down('.mk_rread').hasClassName('mk_readonly'));
		//var isReadOnly = (subNodeContainer.previous().down('.mk_rread').hasClassName('mk_readonly')) && ;
		removeClasses.each(function(currentClass) {
			if (!isReadOnly || (currentClass != 'mk_hasrread')) {
				subNodeContainer.removeClassName(currentClass);
			}
		});
		addClasses.each(function(currentClass) {
			if (!isReadOnly || (currentClass != 'mk_hasnorread')) {
				subNodeContainer.addClassName(currentClass);
			}
		});
	}

}


/**
 * Saves the permissions
 * @param { Element } [which] Reference to the button
 */
$K.savePermissions = function(which) {
	var winRef = which.up('.ywindow');
	var winID = winRef.id;
	var tabContent = $(winID + '_' + $K.windows[winID].tab);
	var objectName = $K.windows[winID].tab.replace(/USERGROUP_/g, '');
	var yg_id = $K.windows[winID].yg_id.split('-')[0];
	if ($(winID + '_newsite')) {
		var currentSite = $(winID + '_newsite').down('input[type=hidden]').value;
	} else {
		var currentSite = 0;
	}

	var allPermissions = {};
	var allPermissionFields = tabContent.select('input.mk_changed').each(function(item) {
		if (item.value !== '') {
			allPermissions[item.name] = item.value;
		}
	});
	allPermissions.objectType = objectName;
	allPermissions.winID = winID;
	allPermissions.roleID = yg_id;
	allPermissions.currentSite = currentSite;

	if (objectName == 'GENERAL') {
		allPermissions.objectName = tabContent.down('input[name=objectname]').value;
	}

	$(winID + '_ywindowinner').addClassName('tab_loading');
	$(winID + '_' + $K.windows[winID].tab).update('');

	var data = Array('noevent', {yg_property:'savePermissions', params:allPermissions });
	$K.yg_AjaxCallback(data, 'savePermissions');
}


/**
 * Deletes a role
 * @param { Element } [buttonReference] Reference to the button
 */
$K.yg_deleteRole = function(buttonReference) {

	if (buttonReference.hasClassName('tree_btn_role')) {
		if (buttonReference.hasClassName('disabled')) {
			return;
		}
		var yg_id = $K.windows[$(buttonReference).up('.ywindow').id].yg_id.split('-')[0];
	} else {
		var itemReference = $(buttonReference).up('li');
		var yg_id = itemReference.readAttribute('yg_id').split('-')[0];
	}

	var data = Array('noevent', {yg_property:'deleteRole', params:{
		roleID:yg_id,
		wid:$(buttonReference).up('.ywindow').id
	} });
	$K.yg_AjaxCallback(data, 'deleteRole');
}


/**
 * Adds a role
 * @param { Element } [buttonReference] Reference to the button
 */
$K.yg_addRole = function(buttonReference) {
	buttonReference = $(buttonReference);
	if (buttonReference.hasClassName('disabled')) {
		return;
	}

	var data = Array('noevent', {yg_property:'addRole', params:{
		wid:$(buttonReference).up('.ywindow').id
	} });
	$K.yg_AjaxCallback(data, 'addRole');
}


/**
 * Adds a role item to rolelist
 * @param { String } [winID] WindowId of the button
 * @param { String } [roleID] Role id
 * @param { String } [roleName] Role name
 */
$K.yg_addRoleItem = function(winID, roleID, roleName) {
	if ($K.windows[winID] && $K.windows[winID].jsTemplate) {
		var item_template = $K.yg_makeTemplate($K.windows[winID].jsTemplate);
		var newRoleItem = item_template.evaluate({    new_id:roleID, new_name:roleName });

		if ($(winID + '__usergrouplist')) {
			$(winID + '__usergrouplist').insert({bottom:newRoleItem});
			$K.yg_customAttributeHandler($(winID + '_usergroups_' + roleID).up());
			$K.windows[winID].refresh();
			$K.yg_resortRolesList(winID);
			$K.yg_selectNode($(winID + '_usergroups_' + roleID + '_selector'));
			$K.yg_blockSelect($(winID + '_usergroups_' + roleID + '_selector'), null);
		}

		if ($(winID + '_tab_USERGROUP_LIST_usergrouplist')) {
			$(winID + '_tab_USERGROUP_LIST_usergrouplist').insert({bottom:newRoleItem});
			$K.yg_customAttributeHandler($(winID + '_usergroups_' + roleID).up());
			$K.windows[winID].refresh();
			$K.yg_resortRolesList(winID);
			$K.yg_selectNode($(winID + '_usergroups_' + roleID + '_selector'));
			$K.yg_blockSelect($(winID + '_usergroups_' + roleID + '_selector'), null);
		}
	}
}


/**
 * Re-sorts all entries in the role-list
 */
$K.yg_resortRolesList = function(winID) {
	for (winID in $K.windows) {
		if ($(winID + '__usergrouplist')) {
			var roleList = $(winID + '__usergrouplist');
		}
		if ($(winID + '_tab_USERGROUP_LIST_usergrouplist')) {
			var roleList = $(winID + '_tab_USERGROUP_LIST_usergrouplist');
		}
		if (roleList) {
			var rolesSortList = new Array();
			roleList.select('li').each(function(roleItem) {
				var id = roleItem.id;
				var txt = $(roleItem.id + '_txt').innerHTML;
				rolesSortList.push(txt.toLowerCase() + '>||<' + id);
			});
			rolesSortList = rolesSortList.sort();
			rolesSortList.each(function(roleItem) {
				var id = roleItem.split('>||<')[1];
				roleList.insert({bottom:$(id)});
			});
		}
	}
}


/**
 * Changes the userfilter (in the userlist)
 * @param { Element } [which] Reference to the dropdown
 * @param { Event } [e] Keyup/Click-Event
 */
$K.yg_changeUserFilter = function(which, e) {
	if (which!=e) Event.stop(e);
	var winRef = which.up('.ywindow');
	var winID = winRef.id;
	var newSearchText = which.up('table').down('input[type=text]').value;
	if (which.up('table').down('input[type=text]').value == which.up('table').down('input[type=text]').readAttribute('data-preset')) {
		newSearchText = '';
	}
	var newRole = which.up('table').down('.dropdown').next('input[type=hidden]').value;
	$K.windows[winID].loadparams.newRole = newRole;
	$K.windows[winID].loadparams.newSearchText = newSearchText;
	$K.windows[winID].loadparams.pagedir_page = 1;
	$K.yg_pageDir($(winID + '_pagedir_USERLIST'));
}


/**
 * Adds a user
 * @param { Element } [buttonReference] Reference to the button
 */
$K.yg_addUser = function(buttonReference) {
	buttonReference = $(buttonReference);
	if (buttonReference.hasClassName('disabled')) {
		return;
	}

	var data = Array('noevent', {yg_property:'addUser', params:{
		wid:$(buttonReference).up('.ywindow').id
	} });
	$K.yg_AjaxCallback(data, 'addUser');
}


/**
 * Adds a useritem to userlist
 * @param { String } [winID] WindowId of the button
 * @param { String } [userID] User id
 * @param { String } [userName] User name
 */
$K.yg_addUserItem = function(winID, userID, userName) {
	if ($K.windows[winID] && $K.windows[winID].jsTemplate) {
		/*var item_template = $K.yg_makeTemplate($K.windows[winID].jsTemplate);
		var newUserItem = item_template.evaluate({ new_id:userID, new_name:userName });*/

		if ($(winID + '_userlist_table')) {
			/*
			 $(winID+'_userlist_table').insert({bottom:newUserItem});
			 $K.yg_customAttributeHandler( $(winID+'_userlist_table') );
			 $K.windows[winID].refresh();
			 */

			$K.windows[winID].yg_id = userID + '-user';

			// Refresh TableKit
			$K.yg_refreshUserTable(winID);

			/*
			 $K.yg_selectNode( $('users_'+winID.replace(/wid_/g,'')+'_'+userID) );
			 $K.yg_blockSelect( $('users_'+winID.replace(/wid_/g,'')+'_'+userID), null );
			 */
		}
	}
}


/**
 * Refreshes the user-table
 * @param { String } [winID] WindowId of the button
 */
$K.yg_refreshUserTable = function(winID, property) {

	//refreshUserTable

	// Refresh TableKit
	TableKit.unloadTable(winID + "_tablecontent");
	TableKit.unloadTable(winID + "_tablehead");

	TableKit.Sortable.init(winID + "_tablehead", {});
	TableKit.Sortable.init(winID + "_tablecontent", {});
	TableKit.Resizable.init(winID + "_tablehead", {});

	if (property) {
		if (property.toLowerCase() != $K.windows[winID].loadparams.pagedir_orderby.toLowerCase()) return;
	}

	$K.windows[winID].tabs.select($K.windows[winID].tabs.selected, {refresh:1});
}


/**
 * Triggers the generation of a new password email
 * @param { String } [userEmail] The email-address of the account to recover password for
 */
$K.yg_generateNewPassword = function(userEmail, element) {
	var data = Array('noevent', {yg_property:'recoverLogin', params:{
		userEmail:userEmail,
		newUser:true
	} });
	$K.yg_AjaxCallback(data, 'recoverLogin', true);
	$(element).previous().show();
	$(element).hide();
}
