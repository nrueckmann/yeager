/**
 * @fileoverview Provides functionality to extend dom elements with custom code & attributes and manipulate them
 * @version 1.0
 */

/**
 * Custom Object Attribute functions
 * @class This object contains all custom-attribute functions for functions.
 */
Koala.objectMethods = {
	/**
	 * Used to define a type of a custom "object"
	 * @param { Object } [object] The relevant object
	 * @param { String } [key] The "Key" property of the custom attribute
	 * @param { String } [value] The "Value" property of the custom attribute
	 * @addon Koala
	 * @function
	 * @name Koala.objectMethods.type
	 */
	'type': function(object, key, value) {
				// Add custom class (prefix plus the object type), to make droppables working
				$(object).addClassName('dnd_' + $K.yg_trim(value));
	},
	/**
	 * Used to define which types of other objects are allowed to drop onto "object"
	 * (Comma seperated list)
	 * @param { Object } [object] The relevant object
	 * @param { String } [key] The "Key" property of the custom attribute
	 * @param { String } [value] The "Value" property of the custom attribute
	 * @addon Koala
	 * @function
	 * @name Koala.objectMethods.accepts
	 */
	'accepts': function(object, key, value) {
				var accepts = value.split(',');

				// Add custom prefixes
				for (m=0; m < accepts.length; m++) accepts[m] = 'dnd_' + $K.yg_trim(accepts[m]);

				Droppables.add( object.id, {
					accept: accepts,
					onDrop: function( element, droppableElement ) {
						if ($(droppableElement).callback) {
							var data = Array ( (this.event)?(this.event):'none', $K.yg_getAttributes( element ), $K.yg_getAttributes( droppableElement ) );
		   					$K.yg_AjaxCallback( data, $(droppableElement).callback );
						}
					}
				});
	}
}


/**
 * Custom Widget Attribute functions
 * @class This object contains all custom-attribute functions for widgets.
 */
Koala.widgetMethods = {
	/**
	 * Used to define a type of a custom "widget"
	 * @param { Object } [object] The relevant object
	 * @param { String } [key] The "Key" property of the custom attribute
	 * @param { String } [value] The "Value" property of the custom attribute
	 * @addon Koala
	 * @function
	 * @name Koala.widgetMethods.type
	 */
	'type': function(object, key, value) {
				object = $(object);
				// Check if legal type was used
				switch ( value ) {
					case 'tree':
						// Add custom class (prefix plus the widget type)
						object.addClassName('wdgt_' + $K.yg_trim(value));
						$K.initTree( object );
						break;
					case 'sortable':
						// Add custom class (prefix plus the widget type)
						object.addClassName('wdgt_' + $K.yg_trim(value));
						$K.initSortable( object );
						break;
					case 'collapsable':
					case 'singlelinetext':
					case 'multilinetext':
					case 'checkbox':
					case 'radiobutton':
					case 'dropdown':
					case 'combobox':
					case 'image':
					case 'submitbutton':
						// Add custom class (prefix plus the widget type)
						object.addClassName('wdgt_' + $K.yg_trim(value));
						break;
					default:
						$K.warn( 'WARNING: The type \'' + value + '\' is not a known type of widget!', object, $K.Log.INFO );
						break;
				}

	},
	/**
	 * Used to define the name of the tree.
	 * (Only for widgets of type 'tree')
	 * @param { Object } [object] The relevant object
	 * @param { String } [key] The "Key" property of the custom attribute
	 * @param { String } [value] The "Value" property of the custom attribute
	 * @addon Koala
	 * @function
	 * @name Koala.widgetMethods.treename
	 */
	'treename': function(object, key, value) {
				if (object.className.indexOf('wdgt_tree') != -1) {
					object.treename = value;
				} else {
					$K.warn( 'WARNING: The property \'' + key + '\' may only be used with widgets of type \'tree\'!', object, $K.Log.INFO );
				}
	},
	/**
	 * Used to define the prefix of an CSS style
	 * (Only for widgets of type 'tree')
	 * @param { Object } [object] The relevant object
	 * @param { String } [key] The "Key" property of the custom attribute
	 * @param { String } [value] The "Value" property of the custom attribute
	 * @addon Koala
	 * @function
	 * @name Koala.widgetMethods.styleprefix
	 */
	'styleprefix': function(object, key, value) {
				if (object.className.indexOf('wdgt_tree') != -1) {
					object.styleprefix = value;
				} else {
					$K.warn( 'WARNING: The property \'' + key + '\' may only be used with widgets of type \'tree\'!', object, $K.Log.INFO );
				}
	},
	/**
	 * Used to define which types of other objects are allowed to drag onto "object"
	 * (Comma seperated list; only for widgets of type 'tree')
	 * @param { Object } [object] The relevant object
	 * @param { String } [key] The "Key" property of the custom attribute
	 * @param { String } [value] The "Value" property of the custom attribute
	 * @addon Koala
	 * @function
	 * @name Koala.widgetMethods.accepts
	 */
	'accepts': function(object, key, value) {
				if (object.className.indexOf('wdgt_tree') != -1) {
					object.accepts = value;
				}
	},
	/**
	 * Used to define the type of the contained objects.
	 * (Only for widgets of type 'tree')
	 * @param { Object } [object] The relevant object
	 * @param { String } [key] The "Key" property of the custom attribute
	 * @param { String } [value] The "Value" property of the custom attribute
	 * @addon Koala
	 * @function
	 * @name Koala.widgetMethods.objtype
	 */
	'objtype': function(object, key, value) {
				var styleprefix = object.readAttribute('styleprefix');

				if (styleprefix!=null) {
					object.styleprefix = styleprefix;
				}

				if (object.className.indexOf('wdgt_tree') != -1) {
					if (!object.treename) {
						$K.warn( 'WARNING: The property \'treename\' is mandantory when creating a object of type \'tree\'!', object, $K.Log.INFO );
					}
				} else {
					$K.warn( 'WARNING: The property \'' + key + '\' may only be used with widgets of type \'tree\'!', object, $K.Log.INFO );
				}
	}
}



/**
 * Stub loader for '$K.yg_customAttributeHandler'
 * @param { Element } [root] The element from which to parse custom attributes
 * @function
 * @name $K.yg_customAttributeHandler
 */
$K.yg_customAttributeHandler = function( root ) {

	// Check if we really got a parameter
	if ( root == null ) return;

	// Timer stuff
	var startTimer = new Date();
	$K.log('Getting all elements below given element...', $K.Log.DEBUG);

	if (!root) {
		var elements = document.getElementsByTagName('*');
	} else {
		var elements = root.getElementsByTagName('*');
	}

	// Timer stuff
	var endTimer = new Date();
	var differenceTimer = endTimer.getTime() - startTimer.getTime();

	// Timer stuff
	startTimer = new Date();
	$K.log('Parsing custom attributes...', root, $K.Log.DEBUG);

	$K._yg_customAttributeHandler( elements );

	// Timer stuff
	endTimer = new Date();
	differenceTimer = endTimer.getTime() - startTimer.getTime();
	$K.warn('Finished parsing custom attributes... ('+differenceTimer+' ms)', $K.Log.DEBUG);

}




/**
 * Initializes all custom objects and widgets.
 * This function has to be called after the page has loaded, it
 * dynamically changes every HTML element marked as custom into
 * the desired object or widget. The actual functions are defined
 * in the file yg_widgets_methods.js.
 * @param { Array } [elements] The elements of which to parse custom attributes
 * @param { Integer } [i] The iteration to start with
 * @function
 * @name $K._yg_customAttributeHandler
 */
$K._yg_customAttributeHandler = function ( elements, i ) {

	// Check if first iteration
	if ( i == undefined ) i = 0;

	// Check if there are still elements to process...
	if ( i < elements.length ) {

		var batchSize = 600;

		for (var xx = i; xx < i + batchSize && xx < elements.length; ++xx) {

			obj = elements[xx];

			if (obj.attributes)
			for (var j=0; j < obj.attributes.length; j++) {

				attribute = obj.attributes[j];
				if ( (attribute.nodeName.substring(0,3) == 'yg_') && (obj.yg_done != true) ) {

					// Split fields at ';'
					if (attribute.nodeValue==undefined)
						continue;
					var yg_customProperties = attribute.nodeValue.split(';');

					// Trim and remove empty array entries
					for (k=0; k < yg_customProperties.length; k++) {
						yg_customProperties[k] = $K.yg_trim(yg_customProperties[k]);
						if (yg_customProperties[k]=='') yg_customProperties.splice(k,1);
					}

					// Set marker for IE
					if ( (obj.attributes.length - 1) == j ) {
						obj.yg_done = true;
						$K.log( 'done:', obj, $K.Log.DEBUG );
					}

					// Switch special attribute names
					switch ( attribute.nodeName.toLowerCase() ) {

						// Definition of Yeager-ID (yg_id)
						case 'yg_id':
							// Set id to object
							obj.yg_id = yg_customProperties[0];
							$K.yg_addLookup( obj.yg_id, obj );
						 	break;

						// Definition of Yeager-Property (yg_property)
						case 'yg_property':
							// Set property to object
							obj.yg_property = yg_customProperties[0];
							break;

						// Definition of Yeager-Type (yg_type)
						case 'yg_type':
							// Set type to object
							obj.yg_type = yg_customProperties[0];
							break;

						case 'yg_form':
							// Set type to object
							obj.yg_form = yg_customProperties[0];
							if (obj.yg_form == "dropdown") $K.yg_initDropdown(obj);
							if (obj.yg_form == "textbox") $K.yg_initTextbox(obj);
							if (obj.yg_form == "checkbox") $K.yg_initCheckbox(obj);
							if (obj.yg_form == "radiobox") $K.yg_initRadiobox(obj);
							if ((obj.yg_form == "calendar") && (!obj.inited)) $K.yg_initCalendar(obj);
							break;

						case 'yg_accepts':

							obj.accepts = obj.yg_accepts = yg_customProperties[0];
							$K.yg_clearDropAreas();
							$K.yg_dropAreas.push(obj);
							break;

						case 'yg_reordering':

							obj.yg_reordering = yg_customProperties[0];
							break;

						case 'yg_selectable':

							obj.yg_selectable = Boolean(yg_customProperties[0]);
							if (obj.className.indexOf('mk_selectable') == -1) obj.className += " mk_selectable";
							var handler = function(event) {
								$K.yg_blockSelect(this, event)
							}
							Event.stopObserving(obj, 'click');
							Event.observe( obj, 'click', handler.bindAsEventListener(obj) );
							break;

						case 'yg_onformsubmit':

							var handler = function(e) {
								for (var jxx=0; jxx < this.attributes.length; jxx++) {
									if (this.attributes[jxx].nodeName == "yg_submitform") myFormID = this.attributes[jxx].nodeValue;
									if (this.attributes[jxx].nodeName == "yg_onformsubmit") myActionName = this.attributes[jxx].nodeValue;
							}
  		  					var all_fields = new Object();
  			   				all_fields.isArray = true;
  				  			formelement = document.getElementById(myFormID);
  								all_fields[0] = $K.yg_getAttributes( formelement );
  								for (var i=0;i < formelement.length;i++) {
  									all_fields[i+1] = $K.yg_getAttributes( formelement[i] )
  					   		}
  						  	var data = Array ( "submit", all_fields );
   							  	$K.yg_AjaxCallback( data, myActionName );
   									if (e) Event.stop(e);
  							}
 							Event.observe( obj, 'click', handler.bindAsEventListener(obj) );
							obj.fireSubmit = handler;
							break;

						case 'yg_panel':

							obj.onmouseover = function() {
								$K.yg_ipanelHighlight(this, 'over');
								if (this.down('.actions')) $K.yg_showActions(this);
							}
							obj.onmouseout = function() {
								$K.yg_ipanelHighlight(this, 'out');
							}
							obj.down(1).onclick = function() {
								$K.yg_ipanelSwap(this.up(1));
							}
							obj.down().onselectstart = function() { return false; }
							obj.down().onmousedown = function() { return false; }
							obj.down().onfocus = function() { this.blur(); }
							break;


						// "Normal" Events
						case 'yg_onclick':
						case 'yg_onrightclick':
						case 'yg_onkeydown':
						case 'yg_onkeyup':
						case 'yg_onkeypress':
						case 'yg_onchange':
						case 'yg_onfocus':
						case 'yg_onblur':
						case 'yg_onselect':


						// "Special" Events
						case 'yg_ondrag':
						case 'yg_ondrop':
						case 'yg_oncollapse':
						case 'yg_onuncollapse':
						case 'yg_onsubmit':

							// Walk through custom properties
							for (l=0; l < yg_customProperties.length; l++) {

								//var eventname = attribute.nodeName.toLowerCase().substr(5);
								var eventname = attribute.nodeName.substr(5);
								var properties = yg_customProperties[l].split(':');

								if (typeof obj.callback != 'object') obj.callback = {};
								obj.callback[eventname] = properties[0];

								if (eventname == 'submit') {

									// Submit
									var handler = function(e) {

										var all_fields = new Object();
										all_fields.isArray = true;

										all_fields[0] = $K.yg_getAttributes( this );

										for (var i=0;i < this.length;i++) {
											all_fields[i+1] = $K.yg_getAttributes( this[i] );
										}

										var data = Array ( 'submit', all_fields );
										$K.yg_AjaxCallback( data, this.callback['submit'] );
										if (e) Event.stop(e);
									}
									Event.observe( obj, 'submit', handler.bindAsEventListener(obj) );

									obj.fireSubmit = handler;

								} else if ( (eventname == 'drag') || (eventname == 'drop') ) {

									// Drag and Drop
									$K.warn( 'Drag/Drop callback-events not implemented yet!!', $K.Log.INFO );

								} else if ( (eventname == 'collapse') || (eventname == 'uncollapse') ) {

									// Collapse/Uncollapse
									$K.warn( 'Collapse/Uncollapse callback-events not implemented yet!!', $K.Log.INFO );

								} else if (eventname == 'rightclick') {

									// Rightclick
									var handler = function(e) {
										var data = Array ( 'rightclick', $K.yg_getAttributes( this ) );
										$K.yg_AjaxCallback( data, this.callback['rightclick'] );
										Event.stop(e);
									}
									Event.observe( obj, 'contextmenu', handler.bindAsEventListener(obj) );

								} else {

									// Special handling for checkboxes
									if ($(obj).hasClassName('checkbox')) {

										// Checkbox event
										var handler = function(e) {
											var attrData = $K.yg_getAttributes( this );
											attrData.value = this.down('input').value;
											if (!Prototype.Browser.WebKit && !Prototype.Browser.IE) {
												if (attrData.value == 0) {
													attrData.value = 1;
												} else {
													attrData.value = 0;
												}
											}
											var data = Array ( e.type, attrData );
											$K.yg_AjaxCallback( data, this.callback[e.type] );
										}
										Event.observe( obj, eventname, handler.bindAsEventListener(obj) );

									} else {
										// "Normal" events
										var handler = function(e) {

											if (this.hasClassName('disabled')) return;

											var fieldData = $K.yg_getAttributes( this );

											if ( (this.tagName == 'INPUT') || (this.tagName == 'TEXTAREA') ) {
												fieldData.value = this.value;
											}

											if (this.up('.ywindow')) {
												fieldData.winID = this.up('.ywindow').id;
											}

											var data = Array ( e.type, fieldData );
											$K.yg_AjaxCallback( data, this.callback[e.type] );
										}

										Event.stopObserving( obj, eventname );
										Event.observe( obj, eventname, handler.bindAsEventListener(obj) );
									}

								}

							}
							break;


						case 'yg_object':
							var drag_handle = null;
							var revert_drag = null;

							// Walk through custom properties
							for (l=0; l < yg_customProperties.length; l++) {
								var properties = yg_customProperties[l].split(':');
								var a_key = properties[0];
								var a_value = properties[1];

								// If there a matching function defined, call it
								if (typeof Koala.objectMethods[a_key] == 'function') {
									Koala.objectMethods[a_key](obj, a_key, a_value);
								}

								// Check for drag-handle & drag-revert (seperately handled)
								if (a_key=='handle') {
									drag_handle = $K.yg_trim(a_value);
								}
								if (a_key=='revert') {
									revert_drag = $K.yg_trim(a_value);
								}

							}

							// Make all custom objects draggable by default (and check if a drag-handle is given)
							if (drag_handle) {
								if (revert_drag == 'false') {
									new Draggable( obj.id, { revert:false, handle:drag_handle } );
								} else {
									new Draggable( obj.id, { revert:true, handle:drag_handle } );
								}
							} else {
								if (revert_drag == 'false') {
									new Draggable( obj.id, { revert:false } );
								} else {
									new Draggable( obj.id, { revert:true } );
								}
							}
							break;


						case 'yg_widget':
							// Walk through custom properties
							for (l=0; l < yg_customProperties.length; l++) {
								var properties = yg_customProperties[l].split(':');
								var a_key = properties[0];
								var a_value = properties[1];

								// If there a matching function defined, call it
								if (typeof Koala.widgetMethods[a_key] == 'function') {
									Koala.widgetMethods[a_key](obj, a_key, a_value);
								}
							}
							break;

					}
				}
			}

		}

		// (Re)CURSE !!11
		var nextBitOfQueue = function() { $K._yg_customAttributeHandler( elements, i+batchSize ) }
		setTimeout( nextBitOfQueue, 0 );

	}

}


/**
 * Helper function used to add objects to the global lookuptable
 * characters.
 * @param { String } [yg_id] The yg_id of the object to add.
 * @param { Object } [object] The string to test in.
 * @function
 * @name $K.yg_addLookup
 */
$K.yg_addLookup = function ( yg_id, object ) {

  	if(!$K.yg_idlookuptable[yg_id]) {
		$K.yg_idlookuptable[yg_id] = new Array();
	}

	// Check if already in array
  	var ilt_idx = $K.yg_idlookuptable[yg_id].indexOf(object);
  	if ( (ilt_idx == -1) || ($K.yg_idlookuptable[yg_id][ilt_idx] != object) ) {
		$K.yg_idlookuptable[yg_id].push( object );
	}

}

/**
 * Core function used to add an object under the specified one (tree-item) based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { String } [name] The name of the new object to add.
 * @param { String } [new_yg_type] The yg_type of the new object to add.
 * @param { String } [new_yg_id] The yg_id of the new object to add.
 * @param { String } [new_yg_property] The yg_property of the new object to add.
 * @param { String } [new_icon] (Optional) The icon of the new object.
 * @param { String } [new_cstclass] (Optional) The custom CSS class of the new object.
 * @param { Boolean } [andSelect] (Optional) Specifies if the newly created object is to be selected.
 * @param { String } [url] (Optional) Specifies the URL of a page
 * @function
 * @name $K.yg_addChild
 */
$K.yg_addChild = function(yg_type, yg_id, yg_property, name, new_yg_type, new_yg_id, new_yg_property, new_icon, new_cstclass, andSelect, url) {

	if (!url) url = "";

	if (new_icon==undefined) {
		new_icon = '';
	} else {
		new_icon = $K.imgdir + 'icons/'+ new_icon;
	}

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Create 'already-done'-Object
	var already_done = new Object();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			// Tree item
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				var treeRef = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);

				// Doublecheck if we are on the right site (in case of pages-trees)
				if (nlsTree[treeRef].rt.yg_type == 'page') {
					var tmpTreeSiteId = nlsTree[treeRef].rt.yg_id.split('-')[1];
					var tmpYgIdSiteId = yg_id.split('-')[1];
					if (tmpTreeSiteId != tmpYgIdSiteId) {
						continue;
					}
				}

				// Check if we have already processed this element (we get false positives due to dragging)
				if (!already_done[treeRef]) {
					// No, add to 'already-done'-Object
					already_done[treeRef] = {};
					already_done[treeRef][$K.yg_idlookuptable[yg_id][i].yg_id] = true;

					objid = new_yg_id.split("-");
					var new_node = nlsTree[ treeRef ].add(new_yg_type+"_"+objid[0], $K.yg_idlookuptable[yg_id][i].orgId, name, '', new_icon, '', null, null, '', null, new_yg_id, new_yg_type, new_yg_property, url);

					// Move trash to the end of the tree (when not already sorted)
					if (nlsTree[treeRef].opt.sort=='no') {
						var treeType = nlsTree[treeRef].rt.yg_type;	// Tree type
						var lastChild = nlsTree[treeRef].rt.lc;
						var trashNode = nlsTree[treeRef].getNodeById(treeType+'_trash');

						if (trashNode && (trashNode != lastChild)) {
							nlsTree[treeRef].ctx_moveChild([trashNode], lastChild, 3);
						}
					}

					if (new_cstclass!=undefined) {
						nlsTree[ treeRef ].setNodeStyle (new_node.orgId, new_cstclass, true);
					}

					// Expand & reload parent node
					nlsTree[ treeRef ].expandNode( $K.yg_idlookuptable[yg_id][i].orgId );
					nlsTree[ treeRef ].reloadNode( $K.yg_idlookuptable[yg_id][i].orgId );

					// Select the new node
					if (Boolean(andSelect)) {
						nlsTree[ treeRef ].selectAfterAjax = new_node.orgId;
						nlsTree[ treeRef ].selectNodeById( new_node.orgId );
						$K.yg_selectNode( $(new_node.id) );
					}

					// Expand all parent nodes
					prnt = $K.yg_idlookuptable[yg_id][i].pr;
					while (prnt != null) {
						nlsTree[ $K.yg_getTreeReference(prnt) ].expandNode( prnt.orgId );
						prnt = prnt.pr;
					}

					// Refresh actionbuttons (if possible)
					if ( typeof(nlsTree[ treeRef ].remapAction)=='function' ) {
						nlsTree[ treeRef ].remapAction();
					}

					// Refresh scrollbars
					$K.windows[$($K.yg_idlookuptable[yg_id][i].id).up('.ywindow').id].refresh();

				} else if (already_done[treeRef][$K.yg_idlookuptable[yg_id][i].yg_id]) {
					// Delete this false positive
					delete $K.yg_idlookuptable[yg_id][i];
				}

			}

		}

	}

	// Delete 'already-done'-Object
	delete already_done;

	return false;
}


/**
 * Core function used to add a new entry to a dropdown based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { String } [value] The value of the new entry.
 * @param { String } [title] The title of the new entry.
 * @function
 * @name $K.yg_addEntry
 */
$K.yg_addEntry = function (yg_type, yg_id, yg_property, value, title) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			if ($K.yg_idlookuptable[yg_id][i].hasClassName('dropdownbox')) {
				var prnt = $($K.yg_idlookuptable[yg_id][i].id+"_ddlist").down();
				prnt.innerHTML = '<div value="'+value+'">'+title+'</div>' + prnt.innerHTML;
				$K.yg_initDropdown( $K.yg_idlookuptable[yg_id][i] );
			}

		}

	}
	return false;
}


/**
 * Core function used to clear all entrie in a dropdown based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_clearEntries
 */
$K.yg_clearEntries = function(yg_type, yg_id, yg_property) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			if ($K.yg_idlookuptable[yg_id][i].hasClassName('dropdownbox')) {
				$K.yg_idlookuptable[yg_id][i].down('input').value = '';
				var prnt = $($K.yg_idlookuptable[yg_id][i].id+"_ddlist").down();
				prnt.innerHTML = '';
			}

		}

	}
	return false;
}


/**
 * Core function used to select an entry from a dropdown based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { String } [value] The value of the new entry.
 * @function
 * @name $K.yg_selectEntry
 */
$K.yg_selectEntry = function(yg_type, yg_id, yg_property, value) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			if ($K.yg_idlookuptable[yg_id][i].hasClassName('dropdownbox')) {
				$K.yg_idlookuptable[yg_id][i].down('input').value = value;
			}

		}

	}
	return false;
}


/**
 * Core function used to show a Yes/No Dialog (used from the PHP-Backend; this is actually a wrapper for promptbox)
 * @type Boolean
 * @param { String } [text] The String to display
 * @param { Mixed } [data] The data to carry back and forth
 * @param { Mixed } [userdata] The userdata to carry back and forth
 * @param { Mixed } [handler] The PHP-backend handler to call
 * @function
 * @name $K.yg_confirm
 */
$K.yg_confirm = function(title, text, action, parameters) {
	var paramObject = parameters.evalJSON();

	$K.yg_promptbox( title, text, 'standard',
		function() {
			paramObject['confirmed'] = 'true';
			paramObject['positive'] = 'true';
			var data = Array ( 'noevent', {yg_property: action, params: paramObject } );
			$K.yg_AjaxCallback( data, action );

		}, function() {
			paramObject['confirmed'] = 'true';
			paramObject['positive'] = 'false';
			var data = Array ( 'noevent', {yg_property: action, params: paramObject } );
			$K.yg_AjaxCallback( data, action );

		}
	);
}


/**
 * Core function used to collapse a collapsable widget
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_collapse
 */
$K.yg_collapse = function(yg_type, yg_id, yg_property) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
	 	 	($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

	 	 	// Toggle the collapsable
			if ( typeof $K.yg_idlookuptable[yg_id][i].down('.handle').onclick == 'function' ) {
				$K.yg_idlookuptable[yg_id][i].down('.handle').onclick();
			}

		}

	}
	return false;
}


/**
 * Core function used to check a radiobutton or a checkbox based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { String } [check] True/False -> On/Off.
 * @function
 * @name $K.yg_check
 */
$K.yg_check = function(yg_type, yg_id, yg_property, check, which) {

	if (!which) which = '';

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			if ($K.yg_idlookuptable[yg_id][i].hasClassName('radiogroup')) {
				if (check == true) {
					var prnt = $K.yg_idlookuptable[yg_id][i].down('.radioarray');
					var chldNodes = $A(prnt.childNodes);
					chldNodes.each( function(x) {
						if ( (x.nodeName == 'DIV') && (x.id == which) ) {
							$K.yg_radioboxSelect(x);
						}
					} );

				}
			} else
			if ($K.yg_idlookuptable[yg_id][i].hasClassName('checkbox')) {
				$K.yg_checkboxSelect($K.yg_idlookuptable[yg_id][i], check);
			}

		}

	}
	return false;
}


/**
 * Core function used to add objects under the specified one from xml (tree-items) based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { String } [url] The url of the xml data to load.
 * @function
 * @name $K.yg_loadFromURL
 */
$K.yg_loadFromURL = function(yg_type, yg_id, yg_property, url) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		var handlerFunc = new Array();
		var errFunc = new Array();

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			// Tree item
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {


				// Define Handler functions
				handlerFunc[i] = {
					func: function(t) {
						nlsTree[ $K.yg_getTreeReference($K.yg_idlookuptable[this.yg_id][this.iterator]) ].addNodesXML($K.yg_idlookuptable[this.yg_id][this.iterator].orgId, t.responseXML.documentElement, true);
						nlsTree[ $K.yg_getTreeReference($K.yg_idlookuptable[this.yg_id][this.iterator]) ].expandNode( $K.yg_idlookuptable[this.yg_id][this.iterator].orgId );
					},
					yg_id: yg_id,
					iterator: i
				}
				errFunc[i] = function(t) {
					$K.error('Error ' + t.status + ' -- ' + t.statusText, $K.Log.INFO);
				}

				new Ajax.Request( url, {
					onSuccess:handlerFunc[i].func.bind(handlerFunc[i]),
					onFailure:errFunc[i],
					parameters: {
						us: document.body.id,
						lh: $K.yg_getLastGuiSyncHistoryId()
					}
				} );

				// Expand all parent nodes
				prnt = $K.yg_idlookuptable[yg_id][i].pr;
				while (prnt != null) {
					nlsTree[ $K.yg_getTreeReference(prnt) ].expandNode( prnt.orgId );
					prnt = prnt.pr;
				}
			}

		}

	}
	return false;
}


/**
 * Core function used to select an object (tree-item) based on type, yg_id and property
 * @type Boolean
 * @param { String }  [yg_type] The type of the element.
 * @param { String }  [yg_id] The id of the element.
 * @param { String }  [yg_property] The property for adressing.
 * @function
 * @name $K.yg_select
 */
$K.yg_select = function(yg_type, yg_id, yg_property) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Select all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			// Tree item
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				var srcnode = $K.yg_idlookuptable[yg_id][i];
				var src = new Array(srcnode);
				var treeRef = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);

				// Select the node
				nlsTree[ treeRef ].selectNodeById(srcnode.orgId);
			}

		}

	}
	return false;
}

/**
 * Adds a file in the GUI.
 * @type Boolean
 * @param { String }  [yg_type] The type of the element.
 * @param { String }  [yg_id] The id of the element.
 * @param { String }  [yg_property] The property for adressing.
 * @param { Int } 	 [count] How many entries should it be moved.
 * @param { Boolean } [reselect] True if the moved entry should be reselected.
 * @function
 * @name $K.yg_addFile
 */
$K.yg_addFile = function(yg_type, yg_id, objectid, thumb, color, identifier, name, pname, tags, filesize, ref_count, timestamp, datum, uhrzeit, uid, username, filename, width, height) {

	$K.yg_cleanLookupTable();

	for( windowItem in $K.windows ) {
		if ($K.windows[windowItem].yg_id == yg_id) {

			if ( ($K.windows[windowItem].yg_type == 'file') || ($K.windows[windowItem].yg_type == 'filefolder') &&
				 (($K.windows[windowItem].tab == 'FOLDERCONTENT') || (($K.windows[windowItem].tab == 'FILES_TREE') && ($K.windows[windowItem].type == 'dialog'))) ) {

				var targetTable = targetContainer = null;
				if ($($K.windows[windowItem].id+'_tablecontent')) targetTable = $($K.windows[windowItem].id+'_tablecontent');
				if ($($K.windows[windowItem].id+'_thumbcontainer')) targetContainer = $($K.windows[windowItem].id+'_thumbcontainer').down('ul');

				// Build tagInfo
				if (typeof tags.evalJSON == 'function') {
					tags = tags.evalJSON();
				} else {
					tags = [];
				}

				var all_tags = '';
				if (tags.length > 0) {
					all_tags = '<div class="related_tags"><span>';
					tags.each(function(tagItem, tagIndex){
						var tagParents = '';
						for (var i=(tagItem.PARENTS.length-1);i>=0;i--) {
							tagParents += tagItem.PARENTS[i].NAME;
							if (i!=0) tagParents += '||';
						}
						all_tags += '<span onmouseover="$K.yg_hoverTagHint(this,'+tagItem.ID+');" path="'+tagParents+'">'+tagItem.NAME+'</span>';
						if (tagIndex != (tags.length-1)) all_tags += ', ';
					});
					all_tags += '</span></div>';
				}

				var fileData = {
					targetTable: targetTable,
					targetContainer: targetContainer,
					yg_type: yg_type,
					yg_id: yg_id,
					objectid: objectid,
					thumb: thumb,
					classname: 'noload',
					color: color,
					identifier: identifier,
					name: name,
					pname: pname,
					all_tags: all_tags,
					filesize: filesize,
					ref_count: ref_count,
					datum: datum,
					uhrzeit: uhrzeit,
					timestamp: timestamp,
					uid: uid,
					username: username,
					filename: filename,
					width: width,
					height: height
				}

				if ( typeof $K.windows[windowItem].addFileToFolder == 'function' ) {
					$K.windows[windowItem].addFileToFolder( fileData );
				}

			}

		}
	}

}

/**
 * Core function used to move an object upwards based on type, yg_id and property
 * @type Boolean
 * @param { String }  [yg_type] The type of the element.
 * @param { String }  [yg_id] The id of the element.
 * @param { String }  [yg_property] The property for adressing.
 * @param { Int } 	 [count] How many entries should it be moved.
 * @param { Boolean } [reselect] True if the moved entry should be reselected.
 * @function
 * @name $K.yg_moveup
 */
$K.yg_moveUp = function(yg_type, yg_id, yg_property, count, reselect) {

	if (!count) count = 1;
	if (!reselect) reselect = true;

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			// Tree item
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {

				// Get previous x-Element
				var x = $K.yg_idlookuptable[yg_id][i].pv;
				for (k=0;k<count-1;k++) {
					x = x.pv;
				}
				var prev = x;

				if (prev) {
					var srcnode = $K.yg_idlookuptable[yg_id][i];
					var src = new Array(srcnode);
					var treeRef = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);

					// Check if parentnode is expanded and expand it if not
					nlsTree[ treeRef ].expandNode( nlsTree[ treeRef ].getNodeById(srcnode.orgId).pr.orgId );

					nlsTree[ treeRef ].ctx_moveChild(src, prev, 2);

					// Reselect the node (if wanted)
					if (reselect!=false) {
						nlsTree[ treeRef ].selectNodeById(srcnode.orgId);
					}

					// Refresh actionbutons (if possible)
					if (typeof nlsTree[treeRef].remapAction == 'function') {
						nlsTree[treeRef].remapAction();
					}

					// Restore tree state (if possible)
					$K.yg_restoreTreeState(treeRef);
				}

			}

		}

	}
	return false;
}


/**
 * Core function used to move a contentblock up
 * @type Boolean
 * @param { object } [which] Reference to related object
 * @function
 * @name $K.yg_moveCBlockUp
 */
$K.yg_moveCBlockUp = function( which ) {
	which = $(which);
	if ( which.previous() != null ) {
		which.insert( { after:which.previous() });
	}
}


/**
 * Core function used to move a contentblock down
 * @type Boolean
 * @param { object } [which] Reference to related object
 * @function
 * @name $K.yg_moveCBlockDown
 */
$K.yg_moveCBlockDown = function( which ) {
	which = $(which);
	if ( which.next() != null ) {
		which.insert( { before:which.next() });
	}
}

/**
 * Core function used to move an object downwards based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { Int } 	[count] How many entries should it be moved.
 * @param { Boolean } [reselect] True if the moved entry should be reselected.
 * @function
 * @name $K.yg_movedown
 */
$K.yg_moveDown = function(yg_type, yg_id, yg_property, count, reselect) {

	if (!count) count = 1;
	if (!reselect) reselect = 1;

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			// Tree item
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {

				// Get next x-Element
				var x = $K.yg_idlookuptable[yg_id][i].nx;
				for (k=0;k<count-1;k++) {
					x = x.nx;
				}
				var next = x;

				if (next) {
					var srcnode = $K.yg_idlookuptable[yg_id][i];
					var src = new Array(srcnode);
					var treeRef = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);

					// Check if parentnode is expanded and expand it if not
					nlsTree[ treeRef ].expandNode( nlsTree[ treeRef ].getNodeById(srcnode.orgId).pr.orgId );

					nlsTree[ treeRef ].ctx_moveChild(src, next, 3);

					// Reselect the node (if wanted)
					if (reselect!=false) {
						nlsTree[ treeRef ].selectNodeById(srcnode.orgId);
					}

					// Refresh actionbutons (if possible)
					if (typeof nlsTree[treeRef].remapAction == 'function') {
						nlsTree[treeRef].remapAction();
					}

					// Restore tree state (if possible)
					$K.yg_restoreTreeState(treeRef);

				}

			}

		}

	}
	return false;
}


/**
 * Core function used to delete an object) based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { String } [windowType] The property for adressing. (optional)
 * @param { String } [window_yg_id] The property for adressing. (optional)
 * @function
 * @name $K.yg_del
 */
$K.yg_del = function(yg_type, yg_id, yg_property, windowType, window_yg_id) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			// Check if the optional windowType and window_yg_id are present
			if (windowType && window_yg_id) {
				var windowRef = null;
				if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
					if (nlsTree[ $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]) ]) {
						var treeRef = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);
						if (treeRef) {
							windowRef = $(treeRef).up('.ywindow');
						}
					}
				} else {
					windowRef = $K.yg_idlookuptable[yg_id][i].up('.ywindow');
				}
				if (windowRef) {
					if (($K.windows[windowRef.id].yg_type != windowType) ||
						($K.windows[windowRef.id].yg_id != window_yg_id)) {
						continue;
					}
				}

			}

			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				// Tree item
				if (nlsTree[ $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]) ]) {

					var treeRef = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);
					var windowRef = $(treeRef).up('.ywindow');

					nlsTree[ treeRef ].remove( $K.yg_idlookuptable[yg_id][i].orgId );
					var selNodeCount = nlsTree[ treeRef ].getSelNodes().length;

					if (selNodeCount == 0) {
						tmparr = $(windowRef.id+'_buttons').descendants();
						tmparr.each(function(item) {
							btn = item.down('.tree_btn');
							if (btn) btn.addClassName('disabled');
						});
						$(windowRef).removeClassName('mk_multiselect');
					}

					// Refresh actionbutons (if possible)
					if ( typeof(nlsTree[ treeRef ].remapAction)=='function' ) {
						nlsTree[ treeRef ].remapAction();
					}

				}
			} else if ($K.yg_idlookuptable[yg_id][i].hasClassName('filetitle')) {

				var windowRef = $K.yg_idlookuptable[yg_id][i].up('.ywindow');

				if (windowRef) {
					if ($K.yg_idlookuptable[yg_id][i].hasClassName('mk_list')) {
						// File (list view)
						if ($K.yg_idlookuptable[yg_id][i].up('tr')) {
							$K.yg_idlookuptable[yg_id][i].up('tr').remove();
						}
					} else if ($K.yg_idlookuptable[yg_id][i].hasClassName('mk_thumb')) {
						// File (thumb view)
						if ($K.yg_idlookuptable[yg_id][i].up('.mk_file')) {
							$K.yg_idlookuptable[yg_id][i].up('.mk_file').remove();
						}
					}

					if ($(windowRef.id+'_objcnt')) {
						$(windowRef.id+'_objcnt').update( parseInt($(windowRef.id+'_objcnt').innerHTML) -1 );
					}


					// Update Scrollbars
					var isDialog = false;
					if ($(windowRef.id).hasClassName('ydialog') && ($(windowRef.id+'_column2'))) isDialog = true;

					$K.windows[windowRef.id].refresh();
				}

			} else if ($K.yg_idlookuptable[yg_id][i].hasClassName('mk_usergroup')) {

				var windowRef = $K.yg_idlookuptable[yg_id][i].up('.ywindow');

				if (windowRef) {
					$K.yg_idlookuptable[yg_id][i].remove();

					// Update Scrollbars
					$K.windows[windowRef.id].refresh();
				}

			} else if ($K.yg_idlookuptable[yg_id][i].hasClassName('mk_user') && ($K.yg_idlookuptable[yg_id][i].tagName == 'TR')) {

				var windowRef = $K.yg_idlookuptable[yg_id][i].up('.ywindow');

				if (windowRef) {

					var tableRef = $K.yg_idlookuptable[yg_id][i].up('table');
					$K.yg_idlookuptable[yg_id][i].remove();

					$K.windows[$(tableRef).up('.ywindow').id].refresh($(tableRef));
					// Check if it was the last remaining element
					if (tableRef.childElements().length==0) {
						tableRef.up().previous().style.display = 'block';
						tableRef.up().previous().down('.actionbutton').style.display = '';
					}

					for( windowItem in $K.windows ) {
						if ( ($K.windows[windowItem].yg_id == yg_id) &&
							 ($K.windows[windowItem].tab != 'USERLIST') ) {
							$(windowItem).addClassName('boxghost');
							$K.windows[$K.windows[windowItem].boundWindow].tabs.select($K.windows[$K.windows[windowItem].boundWindow].tabs.selected, {refresh: 1});
							$K.windows[windowItem].init();
						}
					}

					// Update Usercount
					$(windowRef.id+'_objcnt').update( parseInt($(windowRef.id+'_objcnt').innerHTML, 10) -1 );

					// Update Scrollbars
					$K.windows[windowRef.id].refresh();
				}

			} else if ($K.yg_idlookuptable[yg_id][i].hasClassName('mk_tag')) {

				var item = $K.yg_idlookuptable[yg_id][i];
				if (!item.orgId) {
					var ulRef = item.up('ul');
					$K.yg_removefromFocus(item);
					if (item.tagName == 'LI') {
						item.remove();
					} else {
						item.up('li').remove();
					}
					$K.windows[$(ulRef).up('.ywindow').id].refresh($(ulRef));
				}

			} else if ( ($K.yg_idlookuptable[yg_id][i].hasClassName('mk_extension')) ||
						($K.yg_idlookuptable[yg_id][i].hasClassName('mk_cblock')) ) {

				if ($K.yg_idlookuptable[yg_id][i].readAttribute('yg_property') == 'listitem') {
					var winId = $K.yg_idlookuptable[yg_id][i].up('.ywindow').id;
					if ( $K.windows[winId].loadparams &&
						 $K.windows[winId].loadparams.pagedir_perpage &&
						 ( ($K.windows[winId].loadparams.pagedir_perpage == -1) ||
						   ($K.windows[winId].loadparams.pagedir_perpage > parseInt($(winId+'_objcnt').innerHTML, 10)) ) ) {
							$K.yg_idlookuptable[yg_id][i].remove();
							$(winId+'_objcnt').update(parseInt($(winId+'_objcnt').innerHTML, 10)-1);
							$K.windows[winId].refresh();
					} else {
						$K.windows[winId].tabs.select($K.windows[winId].tabs.selected, {refresh: 1});
					}
				} else {
					var reference = $K.yg_idlookuptable[yg_id][i].up('li');
					ulRef = reference.up('ul');
					if (reference.parentNode) reference.remove();
					if (ulRef) {
						$K.windows[$(ulRef).up('.ywindow').id].refresh($(ulRef));
						// Check if it was the last remaining element
						/*if (ulRef.childElements().length==0) {
							ulRef.up().previous().style.display = 'block';
							ulRef.up().next().style.display = 'none';
							ulRef.up().previous().down('.actionbutton').style.display = '';
						}*/
					}
				}

			}

		}

	}
	$K.yg_cleanLookupTable();

	return false;
}


/**
 * Core function used to refresh tags on an object based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { String } [tags] The tags to refresh (JSON array)
 * @function
 * @name $K.yg_refreshTags
 */
$K.yg_refreshTags = function(yg_type, yg_id, yg_property, tags) {

	// Garbage-Collection
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				// Tree item
			} else if ($K.yg_idlookuptable[yg_id][i].hasClassName('mk_file')) {
				$K.yg_refreshFileTags( $K.yg_idlookuptable[yg_id][i], tags );
			}

		}

	}
	return false;
}


/**
 * Core function used to refresh an object based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_refresh
 */
$K.yg_refresh = function(yg_type, yg_id, yg_property) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			// Tree item
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				nlsTree[ $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]) ].reloadNode( $K.yg_idlookuptable[yg_id][i].orgId );
			}

		}

	}
	return false;
}


/**
 * Core function used to change an element in the DOM-Tree based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { String } [value] The value to change.
 * @function
 * @name $K.yg_change
 */
$K.yg_change = function(yg_type, yg_id, yg_property, value) {
	// Restore Linebreaks
	if (Prototype.Browser.IE) {
		value = value.replace(/\\n/g,'\n\r');
	} else {
		value = value.replace(/\\n/g,'\n');
	}

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
				($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) &&
				($K.yg_idlookuptable[yg_id][i].yg_id == yg_id) ) ||
			 ( $K.yg_idlookuptable[yg_id][i].readAttribute &&
			   ($K.yg_idlookuptable[yg_id][i].readAttribute('yg_property') == yg_property) &&
			   ($K.yg_idlookuptable[yg_id][i].readAttribute('yg_type') == yg_type) &&
			   ($K.yg_idlookuptable[yg_id][i].readAttribute('yg_id') == yg_id) ) ) {

			// Listitem
			if (yg_property == 'listitem') {
				var targetElement = $K.yg_idlookuptable[yg_id][i];
				var winId = $K.yg_idlookuptable[yg_id][i].up('.ywindow').id;
				var listViewExtensionId = $(winId+'_listview').down('input[type=hidden]').value;
				var coId = yg_id.split('-')[0];
				var coFolderId = $K.windows[winId].yg_id.split('-')[0];
				new Ajax.Request( $K.appdir+'contentblock_listitem', {
					onSuccess: function(t) {
						if (targetElement.hasClassName('cntblockfocus')) {
							t.responseText = t.responseText.replace(/cntblock/, 'cntblockfocus');
						}
						if (targetElement.down('div.listthumb') && targetElement.down('div.listthumb').down('img')) {
							var oldSrc = targetElement.down('div.listthumb').down('img').src;
							var oldImageUrlArray = oldSrc.split('/');
							var oldImageId;
							oldImageUrlArray.each(function(oldImageUrlArrayItem) {
								if (!isNaN(parseInt(oldImageUrlArrayItem, 10))) {
									oldImageId = parseInt(oldImageUrlArrayItem, 10);
								}
							});
							if (oldImageId) {
								var dummyDiv = new Element('table');
								dummyDiv.insert(t.responseText);
								if (dummyDiv.down('div.listthumb')) {
									var realSrc = dummyDiv.down('div.listthumb').down('img').readAttribute('real_src');
									if (!realSrc) {
										realSrc = dummyDiv.down('div.listthumb').down('img').readAttribute('src');
									}
									var newImageUrlArray = realSrc.split('/');
									var newImageId;
									newImageUrlArray.each(function(newImageUrlArrayItem) {
										if (!isNaN(parseInt(newImageUrlArrayItem, 10))) {
											newImageId = parseInt(newImageUrlArrayItem, 10);
										}
									});
									if (newImageId && (newImageId == oldImageId)) {
										var imgRef = dummyDiv.down('div.listthumb').down('img');
										imgRef.writeAttribute('src', oldSrc);
										t.responseText = dummyDiv.down('tr');
									}
								}
							}
						}
						oldId = targetElement.id;
						targetElement.replace(t.responseText);
						$K.yg_currentfocusobj.push($(oldId));

						// Add to lookuptable
						$K.yg_customAttributeHandler( $(winId+'_listcontainer') );

						// Initiate thumbnail loading
						$K.yg_loadThumbPreview( $(winId+'_listcontainer'), '.cntblock img' );
						TableKit.Sortable.sort(winId+'_tablecontent', $K.windows[winId].loadparams.pagedir_orderby, $K.windows[winId].loadparams.pagedir_orderdir);
						TableKit.Sortable.sort(winId+'_tablecontent', $K.windows[winId].loadparams.pagedir_orderby, $K.windows[winId].loadparams.pagedir_orderdir);
					},
					parameters: {
						us: document.body.id,
						lh: $K.yg_getLastGuiSyncHistoryId(),
						listViewExtensionId: listViewExtensionId,
						coId: coId,
						yg_id: coFolderId+'-cblock',
						wid: winId
					}
				});
				continue;
			}

			// Picture
			if ($K.yg_idlookuptable[yg_id][i].src != undefined) {
				$K.yg_idlookuptable[yg_id][i].src = value;
			}

			// Tree item
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				var currTree = nlsTree[ $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]) ];

				currTree.setNodeCaption( $K.yg_idlookuptable[yg_id][i].orgId, value );

				$K.yg_idlookuptable[yg_id][i].capt = value;
				nlsTree[ $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]) ].reloadNode( $K.yg_idlookuptable[yg_id][i].orgId );

				if (typeof currTree.remapAction == 'function') {
					window.setTimeout(function() {
						currTree.remapAction();
					}
					, 0);
				}

			}

			// Normal HTML-Inputfield
			if (typeof $K.yg_idlookuptable[yg_id][i].value=='string') {
				$K.yg_idlookuptable[yg_id][i].value = value;
				if ($K.yg_idlookuptable[yg_id][i].readAttribute('oldvalue')) {
					$K.yg_idlookuptable[yg_id][i].writeAttribute('oldvalue', value)
				}
				var fadedata = $K.yg_idlookuptable[yg_id][i];
				$K.yg_fadeField( fadedata );
			}

			// Normal HTML-Textarea
			if ($K.yg_idlookuptable[yg_id][i].nodeName=='TEXTAREA') {
				$K.yg_idlookuptable[yg_id][i].value = value;
				var fadedata2 = $K.yg_idlookuptable[yg_id][i];
				var fade2 = function() { $K.yg_fadeField( fadedata2 ); }
				fade2.delay(0.1);
			}

			// Normal HTML-Element
			if (($K.yg_idlookuptable[yg_id][i].innerHTML != undefined) && ($K.yg_idlookuptable[yg_id][i].nodeName!='TEXTAREA') && ($K.yg_idlookuptable[yg_id][i].nodeName!='INPUT')) {
				// Check for special case 'email'
				if (yg_property == 'email') {
					var newA = new Element( 'a', {
						className: $K.yg_idlookuptable[yg_id][i].className,
						href: 'mailto:' + value
					});
					newA.update(value);
					var newSpan = new Element( 'span', {
						yg_type: yg_type,
						yg_id: yg_id,
						yg_property: yg_property
					});
					newSpan.insert(newA);

					var newId = newSpan.identify();
					var oldId = $K.yg_idlookuptable[yg_id][i].identify();
					$(oldId).replace( newSpan );
					$K.yg_idlookuptable[yg_id][i] = $(newId);
				} else {
					// First check if an element with class "txt" is available
					if ($K.yg_idlookuptable[yg_id][i].down('.txt')) {
						// Only change the text
						$K.yg_idlookuptable[yg_id][i].down('.txt').update(value);
					} else {
						// Change the complete content
						if ($K.yg_idlookuptable[yg_id][i].nodeName == 'INPUT') {
							$K.yg_idlookuptable[yg_id][i].value = value;
						} else {
							$K.yg_idlookuptable[yg_id][i].update(value);
						}
					}
				}
			}

		}

	}
	return false;
}

/**
 * Core function used to change a class of an element in the DOM-Tree based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { String } [value] The value to change.
 * @function
 * @name $K.yg_changeClass
 */
$K.yg_changeClass = function(yg_type, yg_id, yg_property, value) {

	// Restore Linebreaks
	if (Prototype.Browser.IE) {
		value = value.replace(/\\n/g,'\n\r');
	} else {
		value = value.replace(/\\n/g,'\n');
	}

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( (($($K.yg_idlookuptable[yg_id][i]).yg_property == yg_property) &&
			 ($($K.yg_idlookuptable[yg_id][i]).yg_type == yg_type)) ||
			 ( $($K.yg_idlookuptable[yg_id][i]).readAttribute &&
			   ($($K.yg_idlookuptable[yg_id][i]).readAttribute('yg_property') == yg_property) &&
		 	   ($($K.yg_idlookuptable[yg_id][i]).readAttribute('yg_type') == yg_type) ) ) {

			// Tree item
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				//$K.yg_idlookuptable[yg_id][i].capt = value;
				//nlsTree[ $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]) ].reloadNode( $K.yg_idlookuptable[yg_id][i].orgId );
			} else {
				$K.yg_idlookuptable[yg_id][i].className = value;
			}

		}

	}
	return false;
}

/**
 * Core function used to change the pname of an element in the DOM-Tree based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { String } [value] The value to change.
 * @param { String } [url] Url of a page or file
 * @param { String } [imgurl] Image url of a file
 * @function
 * @name $K.yg_changePName
 */
$K.yg_changePName = function(yg_type, yg_id, yg_property, value, url, imgurl) {

	// Restore Linebreaks
	if (Prototype.Browser.IE) {
		value = value.replace(/\\n/g,'\n\r');
	} else {
		value = value.replace(/\\n/g,'\n');
	}

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($($K.yg_idlookuptable[yg_id][i]).yg_property == yg_property) &&
			 	 ($($K.yg_idlookuptable[yg_id][i]).yg_type == yg_type) ) {
			// Tree item
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				$K.yg_idlookuptable[yg_id][i].pname = value;
				$K.yg_idlookuptable[yg_id][i].ygurl = url;
			}
		}

		if ( (typeof $($K.yg_idlookuptable[yg_id][i]).readAttribute == 'function') &&
			 ($($K.yg_idlookuptable[yg_id][i]).readAttribute('yg_property') == 'pname') &&
		 	 ($($K.yg_idlookuptable[yg_id][i]).readAttribute('yg_type') == yg_type) ) {
			// Normal item
			if ($K.yg_idlookuptable[yg_id][i].tagName == 'INPUT') {
				$K.yg_idlookuptable[yg_id][i].oldvalue = $K.yg_idlookuptable[yg_id][i].value = value;
				$K.yg_idlookuptable[yg_id][i].writeAttribute('value', value);
			}
		}

		// Special case for files
		if ( (typeof $($K.yg_idlookuptable[yg_id][i]).readAttribute == 'function') &&
			 ($($K.yg_idlookuptable[yg_id][i]).readAttribute('yg_property') == yg_property) &&
			 ($($K.yg_idlookuptable[yg_id][i]).readAttribute('yg_type') == 'file') &&
			 ($($K.yg_idlookuptable[yg_id][i]).readAttribute('pname') != '') ) {
			// Normal item
			if ($K.yg_idlookuptable[yg_id][i].up('.mk_file')) {
				$K.yg_idlookuptable[yg_id][i].up('.mk_file').pname = value;
				$K.yg_idlookuptable[yg_id][i].up('.mk_file').writeAttribute('pname', value);
				$K.yg_idlookuptable[yg_id][i].up('.mk_file').writeAttribute('yg_downloadurl', url);
				$K.yg_idlookuptable[yg_id][i].up('.mk_file').writeAttribute('yg_imageurl', imgurl);
			}

		}
	}

	// update Urls
	if (url != '') {
		for (windowItem in $K.windows) {
			if ($K.windows[windowItem].yg_id == yg_id) {
				$K.yg_updatePreviewUrls(windowItem, url);
			}
		}
	}

	return false;
}

/**
 * Core function used to change the backgroundimage of an element in the DOM-Tree based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { String } [value] The value to change.
 * @function
 * @name $K.yg_changeBGImage
 */
$K.yg_changeBGImage = function(yg_type, yg_id, yg_property, value) {

	// Restore Linebreaks
	if (Prototype.Browser.IE) {
		value = value.replace(/\\n/g,'\n\r');
	} else {
		value = value.replace(/\\n/g,'\n');
	}

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($($K.yg_idlookuptable[yg_id][i]).readAttribute('yg_property') == yg_property) &&
		 	 ($($K.yg_idlookuptable[yg_id][i]).readAttribute('yg_type') == yg_type) ) {

			// Tree item
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
			} else {
				$K.yg_idlookuptable[yg_id][i].setStyle({backgroundImage: 'url('+value+')'});
			}

		}
	}
	return false;
}

/**
 * Core function used to disable an element in the DOM-Tree based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_disable
 */
$K.yg_disable = function(yg_type, yg_id, yg_property) {

	if (Object.isUndefined($K.yg_idlookuptable[yg_id])) return;

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

		 	// Disable the element
			// Is it a tree item?
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				$($K.yg_idlookuptable[yg_id][i].id).addClassName('disabledstyle');
			} else
			// A dropdownbox?
			if ($($K.yg_idlookuptable[yg_id][i]).hasClassName('dropdownbox')) {
				$($K.yg_idlookuptable[yg_id][i]).down('input').disabled = 'disabled';
			} else
			// A radiogroup?
			if ($K.yg_idlookuptable[yg_id][i].hasClassName('radiogroup')) {
				var prnt = $K.yg_idlookuptable[yg_id][i].down('.radioarray');
				var chldNodes = $A(prnt.childNodes);
				chldNodes.each( function(x) {
					if ( (x.nodeName == 'DIV') && (x.hasClassName('radiobox')) ) {
						x.setAttribute('disabled', 'disabled');
					}
				} );
			} else
			// A treebutton?
			if ($K.yg_idlookuptable[yg_id][i].hasClassName('tree_btn')) {
				$K.yg_idlookuptable[yg_id][i].addClassName('disabled');
			} else
			// Something else
			{
				// No tree item...
				$K.yg_idlookuptable[yg_id][i].disabled = 'disabled';
				$K.yg_idlookuptable[yg_id][i].setAttribute('disabled', 'disabled');
			}

		}
	}
	return false;
}

/**
 * Core function used to enable an element in the DOM-Tree based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_enable
 */
$K.yg_enable = function(yg_type, yg_id, yg_property) {

	if ($K.yg_idlookuptable[yg_id]==undefined)
		return;

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		var element = $($K.yg_idlookuptable[yg_id][i]);

		if (Object.isUndefined(element.tagName)) {
			element = $($K.yg_idlookuptable[yg_id][i].id);
		}

		if ( (element.yg_property == yg_property) &&
		 	 (element.yg_type == yg_type) ) {

		 	// Enable the element
			// Is it a tree item?
			if (!Object.isUndefined(element.capt)) {
				$(element.id).removeClassName('disabledstyle');
			} else
			// A dropdownbox?
			if (element.hasClassName('dropdownbox')) {
				element.down('input').disabled = '';
			} else
			// A radiogroup?
			if (element.hasClassName('radiogroup')) {
				var prnt = element.down('.radioarray');
				var chldNodes = $A(prnt.childNodes);
				chldNodes.each( function(x) {
					if ( (x.nodeName == 'DIV') && (x.hasClassName('radiobox')) ) {
						x.removeAttribute('disabled');
					}
				} );
			} else
			// A treebutton?
			if (element.hasClassName('tree_btn')) {
				element.removeClassName('disabled')
			} else
			// Something else
			{
				// No tree item...
				element.disabled = '';
				element.removeAttribute('disabled');
			}

		}
	}
	return;
}


/**
 * Core function used to set the focus onto an element in the DOM-Tree based
 * on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_focus
 */
$K.yg_focus = function(yg_type, yg_id, yg_property) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

		 	// Focus the element
	 	 	$K.yg_idlookuptable[yg_id][i].focus();

		}
	}
	return false;
}


/**
 * Core function used to hilite an element in the DOM-Tree based
 * on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @param { Boolean } [notInTree] True when the tree shouldn't be updated
 * @function
 * @name $K.yg_hilite
 */
$K.yg_hilite = function(yg_type, yg_id, yg_property, notInTree) {

	if ( $K.yg_idlookuptable[yg_id] == undefined ) return;

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_id == yg_id) ) {

			$K.log( 'Found matching item: ', $K.yg_idlookuptable[yg_id][i], $K.Log.DEBUG );

		 	// Hilite the element
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				// Check if we are allowed to update the tree
				if (!notInTree) {
					// Tree-item
					var treeName = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);
					var page = yg_id.split('-');
					page = page[0];

					var newClass = '';
					if ($(treeName+yg_type+'_'+page)) {
						newClass = $(treeName+yg_type+'_'+page).down('a').className;
					}
					newClass += ' changed';

					if (nlsTree[treeName]) nlsTree[treeName].setNodeStyle(yg_type+'_'+page, newClass, true);
				}
			} else
			if ($K.yg_idlookuptable[yg_id][i].hasClassName('dropdownbox')) {
				$K.yg_idlookuptable[yg_id][i].down('input').addClassName('changed');
			} else
			if ($K.yg_idlookuptable[yg_id][i].hasClassName('radiogroup')) {
				var prnt = $K.yg_idlookuptable[yg_id][i].down('.radioarray');
				var chldNodes = $A(prnt.childNodes);
				chldNodes.each( function(x) {
					if ( (x.nodeName == 'DIV') && (x.hasClassName('radiobox')) ) {
						x.addClassName('changed');
					}
				} );
			} else {
				// No tree item... (but not for inputfields!)
				if (($K.yg_idlookuptable[yg_id][i].nodeName != 'INPUT') && ($K.yg_idlookuptable[yg_id][i].nodeName != 'LI')) {
					$K.yg_idlookuptable[yg_id][i].addClassName('changed');
				}
			}

		}

	}
	return false;
}

/**
 * Core function used to 'fade' an element in the DOM-Tree based
 * on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_fade
 */
$K.yg_fade = function(yg_type, yg_id, yg_property) {

	$K.log( 'yg_type, yg_id, yg_property', yg_type, yg_id, yg_property, $K.Log.INFO );

	if ( $K.yg_idlookuptable[yg_id] == undefined ) return;

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

				$K.yg_idlookuptable[yg_id][i].select('input', 'textarea').each(function(currField) {
					$K.yg_fadeField( currField );
				});

				/*
				// First check for inputfields
				var real_field = $K.yg_idlookuptable[yg_id][i].down('input');
				// Check for textarea
				if (!real_field) {
					real_field = $K.yg_idlookuptable[yg_id][i].down('textarea');
				}
				// Fade the field, if some type of inputfield was found
				if (real_field) {
					$K.yg_fadeField( real_field );
				}
				$K.log( 'Field to fade is: ', $K.yg_idlookuptable[yg_id][i], $K.Log.DEBUG );
				 */
		}
	}
	return false;
}


/**
 * Core function used to 'unhilite' an element in the DOM-Tree based
 * on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_unHilite
 */
$K.yg_unHilite = function(yg_type, yg_id, yg_property) {

	if ( $K.yg_idlookuptable[yg_id] == undefined ) return;

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

		 	// Unhilite the element
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				// Tree-item
				var treeName = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);
				var page = yg_id.split('-');
				page = page[0];
				var oldNodeStyle = "";
				if ($(treeName+yg_type+'_'+page)) var oldNodeStyle = $(treeName+yg_type+'_'+page).down('a').className;
				var newNodeStyle = 'node';
				if (oldNodeStyle.indexOf('nosub')!=-1) {
					newNodeStyle += ' nosub';
				}
				nlsTree[treeName].setNodeStyle(yg_type+'_'+page, newNodeStyle, true);
			} else if ($K.yg_idlookuptable[yg_id][i].hasClassName('dropdownbox')) {
				$K.yg_idlookuptable[yg_id][i].down('input').removeClassName('changed');
			} else if ($K.yg_idlookuptable[yg_id][i].up().hasClassName('dropdowninner')) {
				$K.yg_fadeField( $K.yg_idlookuptable[yg_id][i] );
			} else if ($K.yg_idlookuptable[yg_id][i].hasClassName('radiogroup')) {
				var prnt = $K.yg_idlookuptable[yg_id][i].down('.radioarray');
				var chldNodes = $A(prnt.childNodes);
				chldNodes.each( function(x) {
					if ( (x.nodeName == 'DIV') && (x.hasClassName('radiobox')) ) {
						x.removeClassName('changed');
					}
				} );
			} else {
				// No tree item... (but not for inputfields!)
				if ($K.yg_idlookuptable[yg_id][i].nodeName != 'INPUT') {
		 	 		$K.yg_idlookuptable[yg_id][i].removeClassName('changed');
				}
			}

		}
	}
	return false;
}

/**
 * Core function used to 'inactivate' an element in the DOM-Tree based
 * on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_deActivate
 */
$K.yg_deActivate = function(yg_type, yg_id, yg_property) {

	if ( $K.yg_idlookuptable[yg_id] == undefined ) return;

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

		 	// Inactivate the element
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				// Tree-item
				var treeName = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);
				var page = yg_id.split('-');
				page = page[0];

				if ( (nlsTree[treeName].nLst[treeName+yg_type+'_'+page].ic==null) ||
					 (nlsTree[treeName].nLst[treeName+yg_type+'_'+page].ic[0].indexOf('hidden')==-1) )
				{
					nlsTree[treeName].nLst[treeName+yg_type+'_'+page].setIcon($K.imgdir+'icons/ico_page_inactive_s.png');
				} else {
					nlsTree[treeName].nLst[treeName+yg_type+'_'+page].setIcon($K.imgdir+'icons/ico_page_inactive_hidden_s.png');
				}
				nlsTree[treeName].reloadNode(yg_type+'_'+page, false);
				nlsTree[treeName].selectNodeById(yg_type+'_'+page);

			} else {

				// No tree item... (but not for inputfields!)
				if ($K.yg_idlookuptable[yg_id][i].nodeName != 'INPUT') {

					var ref = $($K.yg_idlookuptable[yg_id][i]).up();

					switch (ref.className) {
						case 'page':
							ref.className = 'pageinactive';
							break;
						case 'pagehidden':
							ref.className = 'pagehiddeninactive';
							break;
						case 'pageinactive':
							ref.className = 'pageinactive';
							break;
						case 'pagehiddeninactive':
							ref.className = 'pagehiddeninactive';
							break;
					}
				}
			}

		}
	}
	return false;
}


/**
 * Core function used to 'activate' an element in the DOM-Tree based
 * on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_activate
 */
$K.yg_activate = function(yg_type, yg_id, yg_property) {

	if ( $K.yg_idlookuptable[yg_id] == undefined ) return;

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

		 	// Activate the element
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				// Tree-item
				var treeName = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);
				var page = yg_id.split('-');
				page = page[0];

				if ( (nlsTree[treeName].nLst[treeName+yg_type+'_'+page].ic==null) ||
					 (nlsTree[treeName].nLst[treeName+yg_type+'_'+page].ic[0].indexOf('hidden')==-1) )
				{
					nlsTree[treeName].nLst[treeName+yg_type+'_'+page].setIcon($K.imgdir+'icons/ico_page_s.png');
				} else {
					nlsTree[treeName].nLst[treeName+yg_type+'_'+page].setIcon($K.imgdir+'icons/ico_page_hidden_s.png');
				}
				nlsTree[treeName].reloadNode(yg_type+'_'+page, false);
				nlsTree[treeName].selectNodeById(yg_type+'_'+page);

			} else
			{
				// No tree item... (but not for inputfields!)
				if ($K.yg_idlookuptable[yg_id][i].nodeName != 'INPUT') {

					var ref = $($K.yg_idlookuptable[yg_id][i]).up();

					switch (ref.className) {
						case 'page':
							ref.className = 'page';
							break;
						case 'pagehidden':
							ref.className = 'pagehidden';
							break;
						case 'pageinactive':
							ref.className = 'page';
							break;
						case 'pagehiddeninactive':
							ref.className = 'pagehidden';
							break;
					}
				}
			}

		}
	}
	return false;
}


/**
 * Core function used to 'unhide' an element in the DOM-Tree based
 * on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_unHide
 */
$K.yg_unHide = function(yg_type, yg_id, yg_property) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

		 	// Unhide the element
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				// Tree-item
				var treeName = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);
				var page = yg_id.split('-');
				page = page[0];

				if ( (nlsTree[treeName].nLst[treeName+yg_type+'_'+page].ic==null) ||
					 (nlsTree[treeName].nLst[treeName+yg_type+'_'+page].ic[0].indexOf('inactive')==-1) )
				{
					nlsTree[treeName].nLst[treeName+yg_type+'_'+page].setIcon($K.imgdir+'icons/ico_page_s.png');
				} else {
					nlsTree[treeName].nLst[treeName+yg_type+'_'+page].setIcon($K.imgdir+'icons/ico_page_inactive_s.png');
				}
				nlsTree[treeName].reloadNode(yg_type+'_'+page, false);
				nlsTree[treeName].selectNodeById(yg_type+'_'+page);

			} else {

				// No tree item... (but not for inputfields!)
				if ($K.yg_idlookuptable[yg_id][i].nodeName != 'INPUT') {

					var ref = $($K.yg_idlookuptable[yg_id][i]).up();

					switch (ref.className) {
						case 'page':
							ref.className = 'page';
							break;
						case 'pagehidden':
							ref.className = 'page';
							break;
						case 'pageinactive':
							ref.className = 'pageinactive';
							break;
						case 'pagehiddeninactive':
							ref.className = 'pageinactive';
							break;
					}
				}
			}

		}
	}
	return false;
}


/**
 * Core function used to 'hide' an element in the DOM-Tree based
 * on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_hide
 */
$K.yg_hide = function(yg_type, yg_id, yg_property) {

	if ( $K.yg_idlookuptable[yg_id] == undefined ) return;

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

		 	// Hide the element
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				// Tree-item
				var treeName = $K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i]);
				var page = yg_id.split('-');
				page = page[0];

				if ( (nlsTree[treeName].nLst[treeName+yg_type+'_'+page].ic==null) ||
					 (nlsTree[treeName].nLst[treeName+yg_type+'_'+page].ic[0].indexOf('inactive')==-1) )
				{
					nlsTree[treeName].nLst[treeName+yg_type+'_'+page].setIcon($K.imgdir+'icons/ico_page_hidden_s.png');
				} else {
					nlsTree[treeName].nLst[treeName+yg_type+'_'+page].setIcon($K.imgdir+'icons/ico_page_inactive_hidden_s.png');
				}
				nlsTree[treeName].reloadNode(yg_type+'_'+page, false);
				nlsTree[treeName].selectNodeById(yg_type+'_'+page);

			} else {
				// No tree item... (but not for inputfields!)
				if ($K.yg_idlookuptable[yg_id][i].nodeName != 'INPUT') {

					var ref = $($K.yg_idlookuptable[yg_id][i]).up();

					switch (ref.className) {
						case 'page':
							ref.className = 'pagehidden';
							break;
						case 'pagehidden':
							ref.className = 'pagehidden';
							break;
						case 'pageinactive':
							ref.className = 'pagehiddeninactive';
							break;
						case 'pagehiddeninactive':
							ref.className = 'pagehiddeninactive';
							break;
					}
				}
			}

		}
	}
	return false;
}


/**
 * Core function used to mark an element in the DOM-Tree as having an error
 * based on type, yg_id and property
 * @type Boolean
 * @param { String } [yg_type] The type of the element.
 * @param { String } [yg_id] The id of the element.
 * @param { String } [yg_property] The property for adressing.
 * @function
 * @name $K.yg_error
 */
$K.yg_error = function(yg_type, yg_id, yg_property) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {

		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

		 	// Mark the element
			// Is it a tree item?
			if ($K.yg_idlookuptable[yg_id][i].capt != undefined) {
				$($K.yg_idlookuptable[yg_id][i].id).addClassName('error');
			} else
			if ($K.yg_idlookuptable[yg_id][i].hasClassName('dropdownbox')) {
				$K.yg_idlookuptable[yg_id][i].down('input').addClassName('error');
			} else
			if ($K.yg_idlookuptable[yg_id][i].hasClassName('radiogroup')) {
				// NOTHING HERE
			} else
			if ($K.yg_idlookuptable[yg_id][i].hasClassName('checkbox')) {
				// NOTHING HERE
			} else
			{
				// No tree item...
	 	 		$K.yg_idlookuptable[yg_id][i].addClassName('error');
			}

		}
	}
	return false;
}

/**
 * Core function used to perform garbage-collection on the yg_lookuptable
 * @function
 * @name $K.yg_cleanLookupTable
 */
$K.yg_cleanLookupTable = function() {

	var entriesToDelete = new Array();

	var isInBody = function(item) {
		if (!item) return false;
		if (item.orgId) {
			var regex = new RegExp(item.orgId);
			var treeName = item.id.replace(regex, '');
			if (!$(treeName)) {
				if (nlsTree[treeName])
					delete nlsTree[treeName];
				return false;
			} else {
				return true;
			}
		}
		// IE...
		if (Prototype.Browser.IE && item.yg_id && item.yg_id.startsWith('btn-')) return true;
		try {
			// debugging
			if ( (typeof(item.descendantOf)=='function') && (item.descendantOf(document.body))) {
				return true;
			}
		} catch(e) {
			return false;
		}
		return false;
	}

	for (idx in $K.yg_idlookuptable) {
		for (var item_idx=0;item_idx<$K.yg_idlookuptable[idx].length;item_idx++) {
			if (!isInBody($K.yg_idlookuptable[idx][item_idx])) {
				delete $K.yg_idlookuptable[idx][item_idx];
				$K.yg_idlookuptable[idx] = $K.yg_idlookuptable[idx].compact();
				if ($K.yg_idlookuptable[idx].size() == 0) {
					entriesToDelete.push(idx);
				}
			}
		}
	}

	entriesToDelete.each(function(item){
		delete $K.yg_idlookuptable[item];
	});

}
