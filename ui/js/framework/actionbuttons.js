/**
 * Reveals the actionbuttons
 * @param { Object } [obj] Regarding object (div).
 * @param { String } [className] classname to add
 * @function
 * @name $K.yg_revealActionButtons
 */
$K.yg_revealActionButtons = function(which, className) {
	if (!className) className = 'row_hover';
	if (which.parentNode.reference) which.parentNode.reference.addClassName(className);
	$K.actionhover = true;
}


/**
 * Hides the actionbuttons
 * @param { Object } [obj] Regarding object (div).
 * @param { String } [className] classname to add
 * @function
 * @name $K.yg_hideActionButtons
 */
$K.yg_hideActionButtons = function(which, className) {
	if (!className) className = 'row_hover';
	if (which.parentNode.reference) which.parentNode.reference.removeClassName(className);
	$K.actionhover = false;
}


/**
 * Shows actionbutton at the right position after hovering an element
 * @param { Object } [obj] Regarding object (div).
 * @param { Object } [tree] Reference to tree object when used by tree
 * @param { Object } [file] Reference to file actions div
 * @function
 * @name $K.yg_showActions
 */
$K.yg_showActions = function(obj, tree, file) {

	// Prevent display of actionbutton while dragging
	if ($K.yg_activeDragInfo.dragging) return;

	// Check if we are hovering over a Contentblock-Header
	obj = $(obj);
	var ywindowinnerRef = obj.up(13);

	if ((obj.hasClassName('cbheader') || obj.hasClassName('emheader')) &&
		 (ywindowinnerRef.hasClassName('ywindowinner'))) {

		// content tab

		var winRef = ywindowinnerRef.up('.ywindow');
		var obj_width = obj.getWidth();
		var box_width = ywindowinnerRef.getWidth();

		// Check if we have a horizontal scrollbar
		if ($K.scrollbars[winRef.id].maxX > 0) {
			var leftPos = (box_width-22);
		} else {
			var leftPos = (box_width-34);
		}

		// Check if we have a vertical scrollbar
		if ($K.scrollbars[winRef.id].maxY > 0) {
			leftPos -= 11;
		}

		// Check if panel is scrolled
		if (winRef && $K.scrollbars[winRef.id] && ($K.scrollbars[winRef.id].maxX > 9) ) {
			var scrolledH = parseInt(-$K.scrollbars[winRef.id].x);
			leftPos += scrolledH;
		}

		// Check if we go over the max width
		if (leftPos > (obj_width-10)) {
			leftPos = (obj_width-10);
		}

		obj.down('.actions').setStyle({left:leftPos+'px'});

	} else if ( (obj.hasClassName('cbheader') || obj.hasClassName('emheader') ) && (ywindowinnerRef.tagName=='TR') ) {

		var winRef = ywindowinnerRef.up('.ywindow');
		var obj_width = obj.getWidth();
		var box_width = ywindowinnerRef.getWidth();

		// Check if we have a horizontal scrollbar
		if ($K.scrollbars[winRef.id].maxX > 0) {
			var leftPos = (box_width-25);
		} else {
			var leftPos = (box_width-37);
		}

		// Check if we have a vertical scrollbar
		if ($K.scrollbars[winRef.id].maxY > 0) {
			leftPos -= 11;
		}

		// Check if panel is scrolled
		if (winRef && $K.scrollbars[winRef.id] && ($K.scrollbars[winRef.id].maxX > 9) ) {
			var scrolledH = parseInt(-$K.scrollbars[winRef.id].x);
			leftPos += scrolledH;
		}

		// Check if we go over the max width
		if (leftPos > (obj_width-12)) {
			leftPos = (obj_width-12);
		}
		obj.down('.actions').setStyle({left:leftPos+'px'});

	} else if (obj.hasClassName('editblocksortable') || obj.hasClassName('cntblockcontainersortable') ) {

		// Check if scrollbars are shown...
		var leftPos = (obj.getWidth()-18);
		if ($K.scrollbars[obj.up('.sortlist').id].maxX > 0) {
			leftPos -= 9;
		}
		obj.down('.actions').setStyle({left:leftPos+'px'});

	} else if ( obj.hasClassName('cntblockcontainer') ) {
		// Hover over empty cntblock
		var winRef = ywindowinnerRef.up('.ywindow');
		var obj_width = obj.getWidth();
		var box_width = ywindowinnerRef.getWidth();

		var leftPos = (box_width-20);

		// Check if we have a vertical scrollbar
		if ($K.scrollbars[winRef.id].maxY > 0) {
			leftPos -= 11;
		}

		// Check if panel is scrolled
		if (winRef && $K.scrollbars[winRef.id] && ($K.scrollbars[winRef.id].maxX > 9) ) {
			var scrolledH = parseInt(-$K.scrollbars[winRef.id].x);
			leftPos += scrolledH - 5;
		}

		// Check if we go over the max width
		if (leftPos > (obj_width-20)) {
			leftPos = (obj_width-20);
		}

		// Check if scrollbars are shown...
		//var leftPos = (obj.getWidth()-18);

		if (obj.down('.actions')) {
			obj.down('.actions').setStyle({left:leftPos+'px'});
		}

	} else if (obj.hasClassName('panelheader')) {

		var winRef = obj.up('.ywindow');
		var obj_width = obj.getWidth();
		var box_width = obj.up('.ywindowinner').getWidth();
		var leftPos = (box_width-22);

		// Check if we have a vertical scrollbar
		if ($K.scrollbars[winRef.id].maxY > 0) {
			leftPos -= 11;
		}

		// Check if panel is scrolled
		if (winRef && $K.scrollbars[winRef.id] && ($K.scrollbars[winRef.id].maxX > 9) ) {
			var scrolledH = parseInt(-$K.scrollbars[winRef.id].x);
			leftPos += scrolledH;
		}
		if (obj.down('.actions')) {
			obj.down('.actions').setStyle({left:leftPos+'px'});
		}

	} else if ((tree) || (file)) {

		// Special handling for Treenodes
		var topOffset = obj.positionedOffset();
		leftOffset = topOffset[0];
		topOffset = topOffset[1];

		var winWidth = obj.up('.ywindowinner').getWidth();

		// Check if scrollbars are visible
		var winRef = obj.up('.ywindow');
		if (winRef) {

			// Check if a horizontal scrollbar is shown
			if ($(winRef.id+"_column2") && !(tree)) {
				//dialog
				scrid = winRef.id + "_column2";
			} else {
				scrid = winRef.id;
			}

			if (($K.scrollbars[scrid]) && ($K.scrollbars[scrid].maxX!=0)) {
				winWidth -= $K.scrollbars[scrid].x + 9;
			}
		}
		if (tree) {
			var treePrefix = tree.substring(0,tree.length-5);

			if ($(treePrefix+'_actionbutton')) {
				$(treePrefix+'_actionbutton').setStyle({top:(topOffset+2)+'px',left:(winWidth-22)+'px'});
				$(treePrefix+'_actionbutton').reference = obj;
			}
			var hideActionButton = false;

			if ( (obj.id.startsWith('entrymasks_') ||
				  obj.id.startsWith('cblocks_') ||
				  obj.id.startsWith('tags_') ||
				  obj.id.startsWith('files_') ||
				  obj.id.startsWith('pages_')) &&
				 obj.up('.mk_chooser') && obj.id.endsWith('treeroot_1') ) {
				hideActionButton = true;
			}

			if (obj.id.endsWith('_trash')) {
				hideActionButton = true;
			}

			if ((obj.id.startsWith('entrymasks_') || (obj.id.startsWith('cblocks_'))) && obj.up('.mk_chooser')) {

				tmpobj = $(obj.down('a'));

				if (tmpobj.className.indexOf('prnnode') != -1) {
					hideActionButton = true;
				}
				var ico = obj.down('.tree_ico');
				if (ico.src.indexOf('_page')!=-1) {
					hideActionButton = true;
				}
				if (ico.src.indexOf('_contentarea')!=-1) {
					hideActionButton = true;
				}
				if ((ico.src.indexOf('_folder')!=-1) && (!obj.up('.mk_folderchooser'))) {
					hideActionButton = true;
				}
			}

			if (obj.id.startsWith('templates_') && obj.up('.mk_chooser')) {
				tmpobj = $(obj.down('a'));
				if ((tmpobj.hasClassName('nopreview')) || (tmpobj.hasClassName('root'))) {
					hideActionButton = true;
				}
			}

			if (obj.id.startsWith('tags_') && !obj.up('.mk_chooser')) {
				var nodeLnk = obj.down('a');
				if (nodeLnk.hasClassName('nodelete') && nodeLnk.hasClassName('nosub')) {
					hideActionButton = true;
				}
			}

			if (hideActionButton) {
				if ($(treePrefix+'_actionbutton')) $(treePrefix+'_actionbutton').setStyle({visibility:'hidden'});
			} else {
				// map doubleclick
				if ($(treePrefix+'_actionbutton') && $(treePrefix+'_actionbutton').down('.exec') && $(treePrefix+'_actionbutton').down('.exec').getStyle("display") != "none") {
					obj.ondblclick = $(treePrefix+'_actionbutton').down('.exec').onclick;
					obj.observe("dblclick", function(ev) { Event.stop(ev); });
				}
				if ($(treePrefix+'_actionbutton')) $(treePrefix+'_actionbutton').setStyle({visibility:''});
			}
		} else if (file == "thumbview") {
			obj.previous().setStyle({top:(topOffset+3)+'px',left:(leftOffset+obj.getWidth()-22)+'px'});
		} else if (file == "tableview") {
			tablewidth = obj.up('table').getWidth();

			var winRef = ywindowinnerRef.up('.ywindow');
			var obj_width = obj.getWidth();
			var box_width = ywindowinnerRef.getWidth();

			if (tablewidth < winWidth) {
				var leftPos = tablewidth - 20;
			} else {
				// Check if we have a vertical scrollbar
				var leftPos = winWidth - 9;
				if ($K.scrollbars[scrid].maxY > 0) {
					leftPos -= 11;
				}
			}
			obj.down(1).setStyle({top:(topOffset+3)+'px',left:leftPos+'px'});
		}
	}
}


/**
 * Shows actions after hovering the actionbutton
 * @param { Object } [obj] Regarding Action-div.
 * @function
 * @name $K.yg_hoverActions
 */
$K.yg_hoverActions = function(obj) {

	// Check if we have a "normal" actionbutton, or the "floating" actionbutton for trees
	obj = $(obj);

    $('yg_fileHint').hide();

    if ( !Object.isUndefined(obj.up().reference) ) {
		// Floating ActionButton
		// Get reference to link in treenode (to show/hide options based on permissions)
		var lnk = obj.up().reference.down('a');

		if (obj.next().down('.del')) {
			// nowrite
			if (lnk.className.indexOf('nowrite')!=-1) {
				if (obj.next().down('.moveto')) obj.next().down('.moveto').style.display = 'none';
				if (obj.next().down('.up')) obj.next().down('.up').style.display = 'none';
				if (obj.next().down('.down')) obj.next().down('.down').style.display = 'none';
			} else {
				if (obj.next().down('.moveto')) obj.next().down('.moveto').style.display = 'block';
				if (obj.next().down('.up')) obj.next().down('.up').style.display = 'block';
				if (obj.next().down('.down')) obj.next().down('.down').style.display = 'block';
			}
			// nodelete
			if (lnk.className.indexOf('nodelete')!=-1) {
				if (obj.next().down('.del')) obj.next().down('.del').style.display = 'none';
			} else {
				if (obj.next().down('.del')) obj.next().down('.del').style.display = 'block';
			}
			// noedit
			if (lnk.className.indexOf('noedit')!=-1) {
				if (obj.next().down('.edit')) obj.next().down('.edit').style.display = 'none';
			} else {
				if (obj.next().down('.edit')) obj.next().down('.edit').style.display = 'block';
			}
			// nopreview
			if (lnk.className.indexOf('nopreview')!=-1) {
				if (obj.next().down('.preview')) obj.next().down('.preview').style.display = 'none';
			} else {
				if (obj.next().down('.preview')) obj.next().down('.preview').style.display = 'block';
			}
			// folder
			if (lnk.className.indexOf('folder')!=-1) {
				if (obj.next().down('.addfolder')) obj.next().down('.addfolder').style.display = 'block';
				if (obj.next().down('.add')) obj.next().down('.add').style.display = 'block';
			} else {
				if (obj.next().down('.addfolder')) obj.next().down('.addfolder').style.display = 'none';
				if (obj.next().down('.add')) obj.next().down('.add').style.display = 'none';
			}
			// nosub
			if (lnk.className.indexOf('nosub')!=-1) {
				if (obj.next().down('.addfolder')) obj.next().down('.addfolder').style.display = 'none';
				if (obj.next().down('.add')) obj.next().down('.add').style.display = 'none';
			} else {
				if (obj.next().down('.addfolder')) obj.next().down('.addfolder').style.display = 'block';
				if (obj.next().down('.add')) obj.next().down('.add').style.display = 'block';
			}
			// nocopy
			if (lnk.className.indexOf('nocopy')!=-1) {
				if (obj.next().down('.copy')) obj.next().down('.copy').style.display = 'none';
			} else {
				if (obj.next().down('.copy')) obj.next().down('.copy').style.display = 'block';
			}
			// root
			if (lnk.hasClassName('root') || lnk.hasClassName('selroot')) {
				$w('preview edit copy moveto down up del').each(function(classToRemove){
					if (obj.next().down('.'+classToRemove)) {
						obj.next().down('.'+classToRemove).style.display = 'none';
					}
				});
			}
		}

		obj = $(obj).next();
		if (obj.down().hasClassName('actionborder')) {
			subs = obj.childElements();
		} else {
			if ((obj.up('.ywindow').hasClassName('mk_multiselect') && (lnk.up('.selrow') || lnk.up('.darkselrow')))) {
				obj.down('.multiselect').style.display = 'block';
				obj.down('.singleselect').hide();
				subs = obj.down('.multiselect').childElements();
			} else {
				obj.down('.multiselect').hide();
				obj.down('.singleselect').show();
		        subs = obj.down('.singleselect').childElements();
			}
		}

		// Check if all buttons are hidden
		var actionButtonShown = false;
		subs.each(function(item){
			if ( (item.style.display != 'none') &&
				 !item.hasClassName('actionborder') ) {
				actionButtonShown = true;
			}
		});
		if (actionButtonShown) {
			obj.show();
		} else {
			obj.hide();
		}

		acwidth=(subs.length-2)*34+3;
		obj.setStyle({width:acwidth+'px'});
		obj.setStyle({left:(acwidth*-1+15)+'px'});
	} else {
		// Normal ActionButton
		obj.next().show();
	}

}
