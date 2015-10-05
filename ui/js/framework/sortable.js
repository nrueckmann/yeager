/**
* Supplemental function to initialize a sortable
* @param { Element } [elementRef] Reference to the element
* @function
* @name $K.initSortable
*/
$K.initSortable = function(elementRef) {
	elementRef = $(elementRef);
	if (!elementRef) return false;
	
	var widgetData = {};
	var widgetInfo = elementRef.readAttribute('yg_widget');
	widgetInfo.split(';').each(function(item){
		var itemData = item.split(':');
		if (itemData[0] && itemData[1]) {
			widgetData[itemData[0]] = itemData[1];			
		}
	});

	var defaultCallbacks = {
		starteffect: function(element, widgetData) {
			Draggable._dragging[element] = true;
			
			if (widgetData.callbacks && $K[widgetData.callbacks] && (typeof $K[widgetData.callbacks].starteffect == 'function')) {
				$K[widgetData.callbacks].starteffect.bind(this)(element);
			}
		},
		endeffect: function(element, widgetData) {
			Draggable._dragging[element] = false;
			
			if (widgetData.callbacks && $K[widgetData.callbacks] && (typeof $K[widgetData.callbacks].endeffect == 'function')) {
				$K[widgetData.callbacks].endeffect.bind(this)(element);
			}
		},
		onUpdate: function(element, widgetData) {
			if (widgetData.callbacks && $K[widgetData.callbacks] && (typeof $K[widgetData.callbacks].onUpdate == 'function')) {
				$K[widgetData.callbacks].onUpdate.bind(this)(element);
			}		
		}
	};
	
	var sortableOptions = {
		dropOnEmpty: true,
		constraint: false,
		ghosting: true,
		accepts: widgetData.accepts,
		objectType: widgetData.objecttype,
		containment: [elementRef.id],
		starteffect: function(element) { defaultCallbacks.starteffect.bindAsEventListener(this, widgetData)(element); },
		endeffect: function(element) { defaultCallbacks.endeffect.bindAsEventListener(this, widgetData)(element); },
		onUpdate: function(element) { defaultCallbacks.onUpdate.bindAsEventListener(this, widgetData)(element); }
	};
	// Optional parameters
	if (widgetData.yg_clone != undefined) sortableOptions.yg_clone = widgetData.yg_clone;
	if (widgetData.tag != undefined) sortableOptions.tag = widgetData.tag;
	if (widgetData.tagTree != undefined) sortableOptions.tag = widgetData.tagTree;
	
	if (widgetData.callbacks &&
		$K[widgetData.callbacks] &&
		(typeof $K[widgetData.callbacks].onCreate == 'function')) {
		$K[widgetData.callbacks].onCreate.bind(elementRef)(elementRef);
	}
	
	Sortable.create( elementRef.id, sortableOptions );
	
}



/**
 * Function for initialization of a sortable list (content box)
 * @param { Element/String } [obj] Id/Element of the list to initialize.
 * @function
 * @name $K.yg_initSortable
 */
$K.yg_initSortable = function(obj) {
	obj = $(obj);
	
	if (!obj.down('.mk_scrollbars')) return;
	
	$K.yg_renderScroll(obj.down('.mk_scrollbars'), obj.id);
	
	var swid = obj.up().getWidth()-2;
	var shei = obj.up().getHeight()-2;
		
	if ((shei>0) && (swid>0)) {
		obj.setStyle({
			width: swid+'px',
			height: shei+'px'
		});
	}

	tmpcont = obj.down('.sortablelistcontainer');
	if ((shei>0) && (swid>0)) {
		tmpcont.setStyle({
			width: swid+'px',
			height: shei+'px'
		});
	}
	
	tmplist = tmpcont.down();
	if ( (tmplist.getHeight() > shei) && ((shei>0) && (swid>0)) ) {
		tmplist.setStyle({width: (swid-$K.yg_scrollObjAttr.scrollBtnVWidth)+'px'})
	} else {
		tmplist.setStyle({width: '100%'});
	}
	$K.scrollbars[obj.id] = $K.yg_initScrollbars(obj, obj.down('.sortablelistcontainer'), obj.down('.sortablelist'), $(obj.id+'_dragbar_v'), $(obj.id+'_track_v'), $(obj.id+'_dragbar_h'), $(obj.id+'_track_h'), 0);
    $(obj.id).observe('mouseover', function(ev) {
        if ($K.yg_scrollObjAttr.activeArea!=this.id) {
            $K.yg_preactive = $K.yg_scrollObjAttr.activeArea;
            $K.yg_scrollObjAttr.activeArea = obj.id;
        }
    });
    $(obj.id).observe('mouseout', function(ev) {
        if ($K.yg_scrollObjAttr.activeArea != $K.yg_preactive) {
			if ($K.yg_preactive != false) {
                $K.yg_scrollObjAttr.activeArea = $K.yg_preactive;
                $K.yg_preactive = false;
            }
		}
	});
	$K.scrollbars[obj.id].setBarSize();
}
