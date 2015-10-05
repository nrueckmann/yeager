$K.yg_dropStackWindow = false;


/**
* Inits dropstack window
* @param { String } [win] window id
*/
$K.yg_initDropStack = function(win) {

	$K.windows['wid_'+win].addFunction = function ( id, type, name, target, position, filetype, filecolor ) {

		openerRef = $('wid_'+win+'_list');

		var newIdSuffix = openerRef.childNodes.length;

		// Create template-chunk
		
		if (type == "file") {
			
			tmparr = name;
			name = tmparr.split('</span>')[1].stripTags().strip();
			filetype = tmparr.split('</span>')[0].stripTags().strip();
			filecolor = tmparr.split('</span>')[0].split('filetype ')[1];
			filecolor = filecolor.substring(0,filecolor.indexOf('"'));
					
			var item_template = $K.yg_makeTemplate( $K.windows['wid_'+win].jsTemplateFile );
			// Fill template with variables
			var newItem = item_template.evaluate( {	item_index: newIdSuffix,
												item_id: id,
												item_type: type,
												item_name: name,
												item_color: filecolor,
												item_filetype: filetype } );		
		} else {
			var item_template = $K.yg_makeTemplate( $K.windows['wid_'+win].jsTemplate );
			// Fill template with variables
            var newItem = item_template.evaluate( {	item_index: newIdSuffix,
												item_id: id,
												item_type: type,
												item_name: name } );		
		}

		var items = openerRef.childNodes;
		
		// check if already in dropstack and abort
		for (var i=0; i<items.length;i++) {
			var actItem = items[i].readAttribute('yg_id');
			if (actItem == id) return;
		}

		if ( (position!='') && (position!=undefined) && (position!='into') && (target != 0)) {
			
			// insert			
			for (var i=0; i<items.length;i++) {
				var actItem = items[i].readAttribute('yg_id');
				if (actItem==target) {
					if (position == 'before') {
						openerRef.childNodes[i].insert({before:newItem});
						break;
					}
					if (position == 'after') {
						openerRef.childNodes[i].insert({after:newItem});
						break;
					}
				}
			}
		} else {		
			openerRef.insert(newItem);
		}

		$K.yg_customAttributeHandler(openerRef);
		$K.initSortable(openerRef);		
		$K.windows[$(openerRef).up('.ywindow').id].refresh($(openerRef));

	}
}


/**
* Loads dropstack window or reopnes in case it's already there
*/
$K.yg_openDropStack = function() {
	if (!$K.yg_dropStackWindow) {
		$K.yg_dropStackWindow = new $K.yg_wndobj({ config: 'DROPSTACK' });
	} else {
		$($K.yg_dropStackWindow.id).show();
		$($K.yg_dropStackWindow.id).setStyle( { zIndex:$K.yg_incrementTopZIndex() } );
	}
}


/**
* Filters list of objects in dropstack by type
* @param { String } [objecttype] filter value
* @param { Element } [shorttitle] Shorttitle of selected filter
* @param { String } [win] window id
*/
$K.yg_filterDropStack = function(objecttype, shorttitle, win) {
	$(win+'_filterlist').innerHTML = shorttitle;
	
	if (objecttype != "ALL") {
		// filter specific
		objs = $(win+'_list').immediateDescendants();

		objs.each(function(item) {
			if (item.readAttribute("class").indexOf('mk_'+objecttype.toLowerCase()) == -1) {
				item.hide();
				$K.yg_removefromFocus(item.down('.mk_selectable'));
			} else {
				item.show();
			}
		});
	} else {
		// remove filter
		objs = $(win+'_list').immediateDescendants();
		objs.each(function(item) {
			item.show();
		});
	}
	$K.windows[win].refresh();
}