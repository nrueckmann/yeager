/**
 * @fileoverview Provides drag'n'drop related functions
 * @version 1.0
 */
$K.yg_currentdragobj = new Array();
$K.yg_activeDragInfo = new Object();
$K.yg_currentHover = null;
$K.yg_overDroppable = null;
$K.yg_mouseCoords = new Object();
$K.yg_mousedown = false;
$K.yg_dropAreas = new Array();


/**
 * Helper function to re-create sortables
 * Only used internally.
 * @param { Element } [which] The sortables element
 * @function
 * @name $K.yg_recreateSortables
 */
$K.yg_recreateSortables = function( which ) {
	if ($(which)) {

		if (!Sortable.sortables[which]) return;
		var containments = Sortable.sortables[which].containment;

		var containments_clean = new Array();

		if (typeof(containments.each) == 'function') {
			containments.each( function(item){if($(item)) containments_clean.push(item);} );
		} else {
			if ($(containments)) containments_clean.push($(containments).id);
		}
		containments_clean.each( function(item) {
			Sortable.create(item, {
				accepts: Sortable.sortables[item].accepts,
				objectType: Sortable.sortables[item].objectType,
				dropOnEmpty: Sortable.sortables[item].dropOnEmpty,
				containment: Sortable.sortables[item].containment,
				handle: Sortable.sortables[item].handle,
				constraint: Sortable.sortables[item].constraint,
				ghosting: Sortable.sortables[item].ghosting,
				yg_clone: Sortable.sortables[item].yg_clone,
				starteffect: Sortable.sortables[item].starteffect,
				reverteffect: Sortable.sortables[item].reverteffect,
				endeffect: Sortable.sortables[item].endeffect,
				onUpdate: Sortable.sortables[item].onUpdate
			} );
		} );

	}
}


/**
 * Helper function to clear placeholders.
 * Only used internally.
 * @function
 * @name $K.yg_clearPlaceHolder
 */
$K.yg_clearPlaceHolder = function() {
	if ( Sortable._marker ) {
		if ( Sortable._marker.hoverOverMarker )
			return;
	}
	if ( $('placeHolder') ) {
		if ( $('placeHolder').hoverOverMarker )
			return;
	}
	if (Sortable) Sortable.unmark();
	//$K.yg_activeDragInfo.dropAllowed = false;
	$K.yg_activeDragInfo.hoverOverTree = false;
	//$K.yg_activeDragInfo.hoverOverSortable = false;
}


/**
 * clears drag session, resets ghost icon
 * @function
 * @name $K.yg_clearDragSession
 */
$K.yg_clearDragSession = function() {

	$K.yg_activeDragInfo.dragging = false;
	$K.yg_activeDragInfo.hoverOverTree = false;
	$K.yg_activeDragInfo.hoverOverSortable = false;
	$K.yg_activeDragInfo.target = null;
	$K.yg_activeDragInfo.position = null;
	with ($('yg_ddGhost')) {
		setStyle({display: 'none'});
		dataTxt = null;
		dataXtra = null;
		_originalParent = null;
	}
	if ($('yg_ddGhost').srcReference) {
		$('yg_ddGhost').srcReference.finishDrag(null, false);
	}
	$('yg_ddGhostTree').hide();
	if (nlsddSession) nlsddSession.consume();

	//disable all DD related events (nlstree)
	document.onmousemove = null;
	document.onmouseup = null;
	document.onmousedown = function() { return true;};
	document.onselectstart = function() { return true;};
	document.ondragstart = function() { return true;};

	$K.yg_clearPlaceHolder();

  	// Remove Dragging-Style
	document.body.removeClassName('drag');
}


/**
 * Helper function which is used for trees and sortables to start scrolling
 * while a drag'n'drop operation is in progress.
 * Only used internally.
 * @param { Element } [element] The element to be scrolled.
 * @param { String } [direction] The direction of the scroll.
 * @function
 * @name $K.yg_ddScrollOver
 */
$K.yg_ddScrollOver = function( element, direction ) {
	if (nlsddSession && nlsddSession.action) {
		$K.yg_scroll_initScroll( element, direction );
	}

	if (Draggables.activeDraggable) {
		if(Sortable._marker) {
			Sortable._marker.hide();
		}
		$K.yg_scroll_initScroll( element, direction );
	}
}

/**
 * Helper function which is used for trees and sortables to stop scrolling .
 * Only used internally.
 * @param { Element } [element] The element to be stopped.
 * @function
 * @name $K.yg_ddScrollOut
 */
$K.yg_ddScrollOut = function( element ) {
	if (nlsddSession && nlsddSession.action) {
		$K.yg_scroll_stopScroll( element );
	}

	if (Draggables.activeDraggable) {
		$K.yg_scroll_stopScroll( element );
	}
}

/**
 * Helper function to update all scrollbars of the dynamic tree and/or sortables,
 * for example when a element has been added/moved or deleted.
 * Only used internally.
 * @function
 * @name $K.yg_updateSortables
 */
$K.yg_updateSortables = function() {
	for (var i=0;i<$K.yg_treecount;i++) {
		//$K.yg_dynTreesWNDO[i].setBarSize();
		if ( $K.yg_dynTreesWNDO[i] != undefined ) {
			$K.yg_dynTreesWNDO[i].setBarSize();
		}
	}

	if ($('placeHolder')) $('placeHolder').remove();
}

/**
 * Helper function to delete an element of a sortable.
 * This function is usually mapped to the close/delete button of
 * an element in a sortable.
 * Only used internally.
 * @param { Element } [element] The element which should be removed.
 * @function
 * @name $K.yg_removeSortable
 */
$K.yg_removeSortable = function( element ) {
	element.parentNode.parentNode.removeChild(element.parentNode);
	$K.yg_updateSortables();
}


/**
 * configures currentdragobj array when starting to drag
 * @param { Element } [dragobj] Dragged object
 * @function
 * @name $K.yg_startDragging
 */
$K.yg_startDragging = function( dragobj, ghosting ) {
	$K.yg_currentdragobj = new Array();
	document.body.addClassName('drag');
	$K.yg_activeDragInfo.dragging = true;
	$('yg_ddGhost').srcReference = dragobj;
	// check for selectable element
	focuselem = dragobj.element.down('.mk_selectable');
	if (!(focuselem)) focuselem  = dragobj.element.up('.mk_selectable');
	if (!(focuselem)) focuselem = dragobj.element;

	focusobjs = $K.yg_getFocusObj(focuselem.up('.mk_contentgroup'));

	if (focusobjs.length > 1) {
		$K.yg_currentdragobj = focusobjs;
	} else {
		$K.yg_currentdragobj.push(focuselem);
	}

	if (ghosting) $K.yg_setGhostIcon(dragobj.element);
}


/**
 * sets Ghost icon depending on dragged sortable element
 * @param { Element } [element] Dragged element
 * @function
 * @name $K.yg_setGhostIcon
 */
$K.yg_setGhostIcon = function( element ) {

	var yg_ddGhost = $('yg_ddGhost');

	yg_ddGhost.setStyle({display: 'block'});

	if ( ($(element.id+'_txt') != null) || element.down('.mk_txt') ) {

		if ( $K.yg_currentdragobj.length > 1 ) {
			yg_ddGhost.dataTxt = '('+$K.yg_currentdragobj.length+' '+$K.TXT('TXT_OBJECTS')+')';
		} else {
			if ($(element.id+'_txt')) {
				yg_ddGhost.dataTxt = $(element.id+'_txt').innerHTML;
			} else if (element.down('.mk_txt')) {
				yg_ddGhost.dataTxt = element.down('.mk_txt').innerHTML;
			} else {
				yg_ddGhost.dataTxt = '[UNKNOWN]';
			}
		}

	}

	yg_ddGhost.down('a').innerHTML = yg_ddGhost.dataTxt;

	icoURL = null;

	for (var i = 0; i < $K.yg_currentdragobj.length; i++) {

		// Check if we have an icon..
		var icon =  $($K.yg_currentdragobj[i]).down('.icn');

		if (icon) {
			newIcoURL = icon.getStyle('backgroundImage').strip();
			if (newIcoURL != 'none') {
				newIcoURL = newIcoURL.substring( newIcoURL.lastIndexOf('/')+1 );
				newIcoURL = newIcoURL.substring( 0, newIcoURL.indexOf('.png')+4 );
			}
		} else {
			newIcoURL = 'none';
		}
		if (icoURL == null) icoURL = newIcoURL;
		if (icoURL != newIcoURL) { icoURL = 'none'; break; }

	}
	if ( (icoURL == 'none') ||
		 (icoURL == 'icon.png') ) {
		yg_ddGhost.down('img').src = $K.imgdir+'tree/blank.gif';
		yg_ddGhost.down('img').setStyle({display: 'inline-block', width: '2px', height: '0px', overflow: 'hidden' });
	} else {
		yg_ddGhost.down('img').src = $K.imgdir+'icons/'+icoURL;
		yg_ddGhost.down('img').setStyle({display: 'inline-block', width: 'auto', height: 'auto', overflow: 'visible' });
	}

}


/**
 * Helper function to clear DropArea array
 * Only used internally.
 * @function
 * @name $K.yg_clearDropAreas
 */
$K.yg_clearDropAreas = function() {
	var newDropArr = new Array();
	for (var k = 0; k < $K.yg_dropAreas.length; k++) {
		elem = $($K.yg_dropAreas[k]);
		if (elem) newDropArr.push($K.yg_dropAreas[k]);
	}
	$K.yg_dropAreas = newDropArr;
}


/**
 * Helper function to determine if a drop is currently possible and/or allowed.
 * Only used internally.
 * @param { Event } [eventRef] Reference to the event
 * @function
 * @name $K.yg_checkIfOverDroppable
 */
$K.yg_checkIfOverDroppable = function( eventRef ) {

	if (!eventRef.target) eventRef.target = eventRef.srcElement;

	var overDroppables = new Array();
	var inDroppables = new Array();

	for (var k = $K.yg_dropAreas.length - 1; k > -1 ; k--) {
		elem = $($K.yg_dropAreas[k]);
		if (elem == null) continue;
		if ($K.yg_checkDroppableOffset(elem)) {
			// Check if element is really under the cursor
			var currElement = document.elementFromPoint($K.yg_mouseCoords.X, $K.yg_mouseCoords.Y);
			if ( (currElement == elem) ||
				 (elem && currElement.descendantOf(elem)) ||
				 (elem.hasClassName('page_contentarea')) ) {
				overDroppables.push(elem);
			}
		}
	}

	if (overDroppables.length == 0) {
		return false;
	} else {
		zIndex = -1;
		for (var i = 0; i < overDroppables.length; i++) {
			curZIndex = parseInt(overDroppables[i].up('.ywindow').getStyle('zIndex'));
			if (isNaN(curZIndex)) curZIndex = 0;
			if (curZIndex == zIndex) {
				inDroppables.push(overDroppables[i]);
			} else if (curZIndex > zIndex) {
				// reset array and zindex
				inDroppables = new Array();
				inDroppables.push(overDroppables[i]);
				zIndex = curZIndex;
			}
		}
	}

	for (var i = 0; i < inDroppables.length; i++) {
		elem = inDroppables[i];
		// Check if we are really over the sortable
		if (typeof $(eventRef.target).descendantOf == 'function') {
			var isDesc = $(eventRef.target).descendantOf(elem);
			if ($(eventRef.target) == $(elem)) isDesc = true;
		} else {
			var isDesc = false;
		}

		var overMarker = false;
		if ($(eventRef.target) && typeof($(eventRef.target).hasClassName == 'function')) {
			if ($(eventRef.target).hasClassName('noselection') ||
				$(eventRef.target).hasClassName('selectionmarker') ||
				(isDesc && $(elem).hasClassName('cntblockadd'))) {
					overMarker = true;
			}
		}

		if (isDesc || overMarker) {
			return elem.id;
		} else {
			return false;
		}
	}

}



/**
 * sets sortable marker zindex and style
 * @param { String } [winId] ID of related window
 * @function
 * @name $K.yg_setSortableMarkerIndex
 */
$K.yg_setSortableMarkerIndex = function(winId) {
	var winZIndex = parseInt( $(winId).getStyle('z-index'), 10 );
	if (isNaN(winZIndex)) winZIndex = 1;
	Sortable._marker.style.zIndex = (winZIndex+3);
	Sortable._marker.style.cursor = 'pointer';

	// Check if dialog
	if ($(winId).hasClassName('ydialog')) {
		Sortable._marker.addClassName('dropmarker_dialog');
	} else {
		Sortable._marker.removeClassName('dropmarker_dialog');
	}
}




/**
 * Helper function to check for the offsets of a droppable
 * Only used internally.
 * @param { Element } [element] The element to the check the offset from
 * @function
 * @name $K.yg_checkDroppableOffset
 */
$K.yg_checkDroppableOffset = function(element) {

	var offset = element.cumulativeOffset();
	var mouseX = $K.yg_mouseCoords.X;
	var mouseY = $K.yg_mouseCoords.Y;
	var tempHeight = element.offsetHeight;
	/*
	if (Prototype.Browser.IE && element.down('ul')) {
		if (element.down('ul').childElements().length == 0) {
			tempHeight = 0;
		}
	}
	*/
	var result = (
		mouseY >= offset[1]-1 &&
		mouseY <  offset[1]-1 + tempHeight &&
		mouseX >= offset[0]-1 &&
		mouseX <  offset[0]-1 + element.offsetWidth
	);
	return result;
}


Event.observe( document, 'mousemove', function(e) {
	$K.yg_mouseCoords.X = Event.pointerX(e);
	$K.yg_mouseCoords.Y = Event.pointerY(e);
} );

/**
 * Event listener for "mousemove"; saves the current mouse coordinates in a public
 * property.
 * Only used internally.
 * NOTE: This function will be initialized automatically after the DOM has loaded.
 * @function
 * @name $K.yg_attachMouseListener
 */
$K.yg_attachMouseListener = function() {

	Event.observe( document, 'mousedown', function(e) {	$K.yg_mousedown = true; } );
	Event.observe( document, 'mouseup', function(e) { $K.yg_mousedown = false;	} );
	Event.observe( document, 'mousemove', function(e) {
		if (!($K.yg_activeDragInfo.dragging)) return;
		setTimeout("$K.yg_clearTextSelection();", 0);

		$K.log("IN ATTACH MOUSE LISTENER", $K.Log.DEBUG);
		$K.log($K.yg_activeDragInfo.dragging, $K.Log.DEBUG);

		$K.yg_mouseCoords.X = Event.pointerX(e);
		$K.yg_mouseCoords.Y = Event.pointerY(e);

		$K.yg_overDroppable = $K.yg_checkIfOverDroppable(e);

		if ((!$K.yg_overDroppable && !$K.yg_activeDragInfo.overTreeNode && !$K.yg_activeDragInfo.overNeedle) && (!Sortable._marker || !Sortable._marker.hoverOverMarker) && (!$('placeHolder') || !$('placeHolder').hoverOverMarker)) {
			// Not over droppable area;
			$K.log("NOT OVER DROPPABLE", $K.Log.DEBUG);
			$K.yg_currentHover = null;
			$K.yg_drawTopborder( null, true );
			$K.yg_clearPlaceHolder();

		} else {
			$K.yg_currentHover = $K.yg_overDroppable;

			//var targetAccepts = false;
			var sourceObject = false;
			var objInAcceptList = false;
			var forceNotAllow = false;

			if (nlsddSession && nlsddSession.action && nlsddSession.srcData) {
				sourceObject = nlsddSession.srcData[0].yg_type;
			}

			var targetAccepts = $K.yg_getTargetAcceptList();

			if (targetAccepts && (targetAccepts.split(',').indexOf(sourceObject)!=-1)) {
				objInAcceptList = true;
			}

			// take target/sourceinfo from ghost (if list -> list)
			if ($('yg_ddGhost').srcReference) {
				var yg_ddGhost = $('yg_ddGhost');
				if ((yg_ddGhost.srcReference.element.parentNode) && (yg_ddGhost._originalParent!=undefined)) {
					var target_tree = Sortable.sortables[$K.yg_currentHover];

					if (target_tree) {
						var targetAccepts = target_tree.accepts;
						if ( target_tree.element.hasClassName('page_contentarea') && (source_tree) &&
					 		source_tree.element.hasClassName('page_contentarea') &&
							($K.yg_currentdragobj.length > 1) ) {
								forceNotAllow = true;
						}
					}

					if ($K.yg_currentdragobj[0] && $K.yg_currentdragobj[0].up('li') && ($K.yg_currentdragobj[0].up('li').readAttribute('yg_type') != null)) {
						for (var i = 0; i < $K.yg_currentdragobj.length; i++) {
							if ($K.yg_currentdragobj[i].up('li') && ($K.yg_currentdragobj[i].up('li').readAttribute('yg_type') != null) &&
								targetAccepts && (targetAccepts.split(',').indexOf($K.yg_currentdragobj[i].up('li').readAttribute('yg_type'))!=-1)) {
									sourceObject = $K.yg_currentdragobj[i].up('li').readAttribute('yg_type');
									objInAcceptList = true;
									break;
							}
						}
					} else if (typeof($('yg_ddGhost').srcReference.element.readAttribute == 'function') && ($('yg_ddGhost').srcReference.element.readAttribute('yg_type'))) {
						sourceObject = $('yg_ddGhost').srcReference.element.readAttribute('yg_type');
					} else {
						var source_tree = Sortable.sortables[yg_ddGhost._originalParent.id];
						if (source_tree) sourceObject = source_tree.objectType;
					}

				}
			}

			if ((!objInAcceptList) && (sourceObject)) {
				if (targetAccepts && (targetAccepts.split(',').indexOf(sourceObject)!=-1)) objInAcceptList = true;
			}

			$K.log( 'Accepts: '+ targetAccepts + " : " + sourceObject, (Math.random()*1000), $K.Log.DEBUG);
			$K.log( 'forceNotAllow: '+forceNotAllow, $K.Log.DEBUG);
			$K.log( 'objInAcceptList: '+objInAcceptList, $K.Log.DEBUG);

			if (!sourceObject) {
				$K.yg_setDropAllowed(false);
				return;
			}

			if (!objInAcceptList ||
				 ((target_tree) && (target_tree.element) && (target_tree.element.hasClassName('dialog_lst'))) ||
				 forceNotAllow) {
					if ((!Sortable._marker || !Sortable._marker.hoverOverMarker) && (!$('placeHolder') || !$('placeHolder').hoverOverMarker) && (!$K.yg_activeDragInfo.overNeedle)) {
						$K.yg_setDropAllowed(false);
						return;
					}
			} else {
				$K.yg_setDropAllowed(true);
			}

			if ($($K.yg_currentHover)) {

				// if sorting
				if ($('yg_ddGhost') && ($('yg_ddGhost').srcReference) && ($($K.yg_overDroppable).up('.ywindow') == $('yg_ddGhost').srcReference.element.up('.ywindow'))) {
					$K.yg_activeDragInfo.reordering = true;
					if ($($K.yg_currentHover).yg_reordering == "false") {
						$K.yg_activeDragInfo.reordering = false;
						$K.yg_setDropAllowed(false);
						return;
					}
				} else {
					$K.yg_activeDragInfo.reordering = false;
				}

				// over tree in sortable?
				if ($K.yg_activeDragInfo.hoverOverTree) { return; }

				if ( ($($K.yg_currentHover).hasClassName('mk_nodrop') ||  $($K.yg_currentHover).hasClassName('mk_nowrite')) ) {
					$K.yg_setDropAllowed(false);
					return;
				}

				if ( $($K.yg_currentHover).hasClassName('cntblockadd') && ($K.yg_activeDragInfo.dropAllowed) && ($($K.yg_currentHover).yg_reordering != "false")) {
					//$K.yg_activeDragInfo.position = 'into';
					//$K.yg_activeDragInfo.target = null;
					list = $($K.yg_currentHover).up(2).down('ul');
					if (list) {
						if (list.immediateDescandants) { listitems = list.immediateDescandants(); } else { listitems = new Array; }
						if (listitems.length > 0) {
							$K.yg_activeDragInfo.target = listitems[listitems.length-1];
							$K.yg_activeDragInfo.position = "after";
						} else {
							$K.yg_activeDragInfo.target = $($K.yg_currentHover).up(2).down('ul');
							$K.yg_activeDragInfo.before = null;
							$K.yg_activeDragInfo.position = "into";
						}
					}
					$K.yg_drawTopborder( $($K.yg_currentHover) );
				} else {
					$K.yg_drawTopborder(null, true);
				}

				if (($($K.yg_currentHover).yg_type == 'formfield') && (sourceObject != 'formfield')) {
					$K.yg_activeDragInfo.hoverOverFormfield = true;
					$K.yg_drawTopborder(null, true);
					if (Sortable._marker) {
						Sortable._marker.hide();
					}
				} else {
					$K.yg_activeDragInfo.hoverOverFormfield = false;
				}
			}
		}
	});
}
$K.addOnDOMReady( $K.yg_attachMouseListener );


/**
 * Repositions the DD ghost when hovering over Iframe and checks if drop allowed (tinymce)
 * @param { String } [winID] ID of the iframe
 * @param { Integer } [x] X position of the mouse in the iframe
 * @param { Integer } [y] Y position of the mouse in the iframe
 * @function
 * @name $K.yg_checkIframeDrop
 */
$K.yg_checkIframeDrop = function (winID, x, y) {
	if (!($K.yg_activeDragInfo.dragging)) return;

	tce = tinymce.get('textarea_'+winID);
	offset = tinymce.DOM.getViewPort(tce.getWin());
	y = y - offset.y;
	x = x - offset.x;

	offset = $('textarea_'+winID+'_ifr').viewportOffset();
	$K.yg_mouseCoords.X = offset[0] + x + 10;
	$K.yg_mouseCoords.Y = offset[1] + y + 10;
	yg_ddGhost = $("yg_ddGhost");
	if (yg_ddGhost.visible() == false) {
		yg_ddGhost = $("yg_ddGhostTree");
	}
	if (yg_ddGhost.visible()) {
		yg_ddGhost.setStyle({left:$K.yg_mouseCoords.X+'px'});
		yg_ddGhost.setStyle({top:$K.yg_mouseCoords.Y+'px'});
    }

	$K.yg_overDroppable = window;
	$K.yg_activeDragInfo.hoverOverFormfield = false;
	$K.yg_activeDragInfo.reordering = false;

	sourceObject = "";
	if ($('yg_ddGhost').srcReference) {
		yg_ddGhost = $("yg_ddGhost");
		if ($K.yg_currentdragobj[0] && $K.yg_currentdragobj[0].up('li') && ($K.yg_currentdragobj[0].up('li').readAttribute('yg_type') != null)) {
			sourceObject = $K.yg_currentdragobj[0].up('li').readAttribute('yg_type');
		} else if (typeof($('yg_ddGhost').srcReference.element.readAttribute == 'function') && ($('yg_ddGhost').srcReference.element.readAttribute('yg_type'))) {
			sourceObject = $('yg_ddGhost').srcReference.element.readAttribute('yg_type');
		} else {
			if (nlsddSession && nlsddSession.action && nlsddSession.srcData) {
				sourceObject = nlsddSession.srcData[0].yg_type;
			}
		}
		targetAccepts = "page,file";
		if (targetAccepts.split(',').indexOf(sourceObject) == -1) {
			$K.yg_setDropAllowed(false);
		} else {
			$K.yg_setDropAllowed(true);
		}
	} else {
		$K.yg_setDropAllowed(false);
	}

}


/**
 * Helper function used for drag/drop on panes. Mapped to the "mouseover" event.
 * Sets the state of the current drag'n'drop operation to "droppable".
 * @param { Boolean } [value] Sets drop allowed to yes/no
 * @function
 * @name $K.yg_setDropAllowed
 */
$K.yg_setDropAllowed = function( value ) {

	if (value) {
		$K.log( '$K.yg_setDropAllowed(true);', $K.Log.DEBUG );
		$K.yg_activeDragInfo.dropAllowed = true;
		$K.yg_showNoDropMarker(false);
	} else {
		if ((!Sortable._marker || !Sortable._marker.hoverOverMarker) || (!$('placeHolder') || !$('placeHolder').hoverOverMarker)) {
			$K.log( '$K.yg_setDropAllowed(false);', $K.Log.DEBUG );
			$K.yg_activeDragInfo.dropAllowed = false;
			$K.yg_showNoDropMarker(true);
		}
	}
}


/**
 * Returns list of accepted objects of currently hovered droparea
 * @function
 * @name $K.yg_getTargetAcceptList
 */
$K.yg_getTargetAcceptList = function() {

	var targetAccepts = false;

	// take target/source info from tree (if tree -> list/tree)
	if (nlsddSession && nlsddSession.action) {
		var sData, dData, sObj, dObj;
		with (nlsddSession) {
			sData=srcData;
			sObj=srcObj;
			dData=destData;
			dObj=destObj;
		}

		if (dObj && $K.yg_activeDragInfo.overTreeNode) {

			// check targettree properties
			if (dObj.tId.endsWith('_tree')) {
				var target_tree = $(dObj.tId).up(1);
			} else {
				var target_tree = $(dObj.tId);
			}
			targetAccepts = target_tree.accepts;

			// dont allow when nodrop marker set
			if ($K.yg_activeDragInfo.treeNodeLink.hasClassName('nosub')) {
				if (dObj.yg_type == sObj.yg_type) targetAccepts = false;
			}

			// dont allow sorting if sourcetree != targettree
			if ((dObj.yg_type == sObj.yg_type) && (dObj.tId != sObj.tId)) targetAccepts = false;
		}
	}

	// take targetinfo from currentHover
	if ((!targetAccepts) && ($($K.yg_currentHover))) {
		targetAccepts = $($K.yg_currentHover).accepts;
	} else if ((!targetAccepts) && ($K.yg_activeDragInfo.target) && ($K.yg_activeDragInfo.target.accepts)) {
		targetAccepts = $K.yg_activeDragInfo.target.accepts;
	}

	return targetAccepts;
}


/**
 * Helper function used show a "nodrop" marker next to mouse cursor while dragging
 * based on type, yg_id and property
 * @param { Boolean } [hide] If true, the "nodrop" marker will be hidden.
 * @function
 * @name $K.yg_showNoDropMarker
 */
$K.yg_showNoDropMarker = function( nodrop ) {

	ghost = $('yg_ddGhost');
	treeghost = $('yg_ddGhostTree');

	if (treeghost && (treeghost.nodrop != nodrop)) {
		if (nodrop) {
			if (treeghost.down('.nodrop')) treeghost.down('.nodrop').setStyle({display: 'block'});
		} else {
			if (treeghost.down('.nodrop')) treeghost.down('.nodrop').setStyle({display: 'none'});
		}
		treeghost.nodrop = nodrop;
	}

	if (ghost && (ghost.nodrop != nodrop)) {
		if (nodrop) {
			ghost.down('.nodrop').setStyle({display: 'block'});
		} else {
			ghost.down('.nodrop').setStyle({display: 'none'});
		}
		ghost.nodrop = nodrop;
	}

}


/**
 * Draws "needles" between elements in sortables.
 * Only used internally.
 * @param { Array of Int } [point] An array of two integers; the current coordinates
 * of the mousecursor.
 * @param { Element } [element] The element which is currently affected (usually the
 * element directly under the mousecursor)
 * @function
 * @name $K.yg_drawNeedles
 */
$K.yg_drawNeedles = function(point, element) {

	$K.log( '!!! Drawing needle on ', element, $K.Log.DEBUG );

	$K.yg_drawTopborder(null, true);

	if ($('yg_ddGhost')) {
		if( $K.yg_activeDragInfo.dropAllowed ) {
			$K.log( 'dropAllowed', $K.Log.DEBUG );
			$('yg_ddGhost').down('img').src = $K.yg_cachedImages['page_small'].src;
		} else {
			$K.log( 'dropNOTAllowed', $K.Log.DEBUG );
			if (Sortable._marker) {
				if (!Sortable._marker.hoverOverMarker) {
					Sortable._marker.hide();
					if( $('placeHolder') ) {
						if ( !$('placeHolder').hoverOverMarker ) {
							$('placeHolder').remove();
						}
					}
					// NODROP
					$K.yg_showNoDropMarker(true);
				}
			}
		}
	}

	if (!element) {
		return;
	}

	var elementPos = element.cumulativeOffset();
	var elementOffsetTop = (point[1] - elementPos[1]);
	var overlap = (elementOffsetTop/element.offsetHeight);
	var dropon = element;

	if(overlap<0.5) {
		if (($K.yg_activeDragInfo.target!=dropon) ||
		   (Sortable._marker && Sortable._marker.style.display=='none') ||
		   (($K.yg_activeDragInfo.target==dropon) && ($K.yg_activeDragInfo.position!='before'))) {
				Sortable.mark(dropon, 'before');
				$K.yg_activeDragInfo.target = dropon;
				$K.yg_activeDragInfo.position = 'before';

		}
	} else {
		if (($K.yg_activeDragInfo.target!=dropon) ||
		   (Sortable._marker && Sortable._marker.style.display=='none') ||
		   (($K.yg_activeDragInfo.target==dropon) && ($K.yg_activeDragInfo.position!='after'))) {
				Sortable.mark(dropon, 'after');
				$K.yg_activeDragInfo.target = dropon;
				$K.yg_activeDragInfo.position = 'after';
		} /* else {
			$K.yg_activeDragInfo.target = null;
			$K.yg_activeDragInfo.position = null;

		} */
	}

}


/**
 * Helper function used draw a black line ("top-border") on a defined element
 * @param { Element } [which] The element involved.
 * @param { Boolean } [remove] If true, remove the border
 * @function
 * @name $K.yg_drawTopborder
 */
$K.yg_drawTopborder = function( which, remove ) {

	if (remove==true) {
		if ($('$treeNeedle')==undefined) return;
		$('$treeNeedle').hide();

		$K.log( 'Needle hidden!!', (Math.random()*1000), $K.Log.DEBUG );
	} else {

		$K.log( 'Drawing topborder on ', which, $K.Log.DEBUG );

		// Protect against extra needle in taglist
		var whichParent = which.up();

/*		if (
			(which.hasClassName('dropmarker')) ||
			(which.hasClassName('listitempage')) ||
			(which.hasClassName('icn')) ||
			(which.hasClassName('listitempagefocus')) ||
			(which.hasClassName('cbheader')) ||
			(which.className.indexOf('cbcontainer')!=-1) ||
			(which.hasClassName('emcontainer')) ||
			(which.hasClassName('emcontainerfocus')) ||
			(which.hasClassName('emcontaineredit')) ||
			(which.hasClassName('emheader')) ||
			(which.hasClassName('maskdisplay')) ||
			(which.className.indexOf('emcontent')!=-1) ||
			(which.className.indexOf('cbcontent')!=-1) ||
			(which.className.indexOf('contentcontainer')!=-1) ||
			(which.hasClassName('embordertbc')) ||
			(whichParent.hasClassName('listitempage')) ||
			(whichParent.hasClassName('listitempagefocus')) ||
			(whichParent.hasClassName('title')) ||
			(whichParent.id.indexOf('item_')!=-1)  ||
			(which.id.endsWith('_templatepanel')) ||
			(which.up('li')) ||
			((which.up('.cntblockselection') && which.up('.cntblockselection').id.endsWith('_templatepanel')))) {
			return;
		}*/

		if (!(which.hasClassName('cntblockadd')) && !(which.hasClassName('selectionmarker')) && !(which.hasClassName('noselection')) && !$K.yg_activeDragInfo.hoverOverTree) return;

		if (Object.isUndefined(which)) return;
		$K.log( '...######## Drawing topborder on: ', which.className, $K.Log.DEBUG );

		which = $(which);

		// Check if window is dialog
		var isDialog = false;

		if (which.up('.ywindow') && which.up('.ywindow').hasClassName('ydialog')) {
			isDialog = true;
		}

		// Get z-index of the window
		if (which.up('.ywindow')) {
			var newZIndex = parseInt(which.up('.ywindow').getStyle('zIndex'));
			if (isNaN(newZIndex)) newZIndex = 0;
			newZIndex+=2;
		}

		if ($('$treeNeedle')==undefined) {
			var needle = $(document.createElement('div'));
			needle.onmouseover = function () { $K.yg_activeDragInfo.overNeedle = true; $K.yg_setDropAllowed(true); this.show(); };
			needle.onmouseout = function () { $K.yg_activeDragInfo.overNeedle = false; };
			needle.onmouseup = function (ev) { $K.yg_customSortableOnDrop($($K.yg_activeDragInfo.target).readAttribute('id'), ev); }
			needle.id = '$treeNeedle';
			needle.addClassName('needle');
			document.body.appendChild(needle);
		} else {
			needle = $('$treeNeedle');
		}
		var pos = which.cumulativeOffset();
		if (Object.isUndefined( which.up('.innercontent') )) {
			return;
		}

		var leftoffset = which.up('.innercontent').positionedOffset();
		leftoffset = leftoffset[0];
		pos.left -= leftoffset;

		pos.width = which.up('.ywindowinner').getWidth();
		pos.width -= 10;
		if (which.hasClassName('cntblockadd')) {
			pos.top -= 1;
			pos.width += 10;
			if ((which.up('.mk_contentarea'))) {
				if (which.up('.mk_contentarea').down('li')) {
					pos.top += 6;
				}
			}
		}

		if (which.hasClassName('cntblockselection')) {
			$K.log('match', $K.Log.DEBUG);
			pos.top += 10;
			pos.width += 10;
		}

		needle.setStyle({left: pos.left+'px', top: (pos.top) + 'px', width: pos.width+'px', zIndex: newZIndex});
		if (Sortable._marker) {
			Sortable._marker.hide();
		}

		if (isDialog) {
			needle.addClassName('needle_dialog');
		} else {
			needle.removeClassName('needle_dialog');
		}

		needle.show();

		$K.log( 'Needle shown ('+(pos.left)+', '+(pos.top)+') at index: ' + newZIndex + '   >', (Math.random()*1000), needle, $K.Log.DEBUG );
	}
	return;
}
