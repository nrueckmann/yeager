/**
 * @fileoverview Provides all actions for customized form elements.
 * radiobuttons, checkboxes, dropdowns, textfields, multilines
 */

$K.yg_clickindropdown = new Array();
$K.yg_dropdownkeydown = false;
$K.yg_preactive = false;
$K.yg_currentfocusobj = new Array();
$K.lastActiveContent = false;

// Map global Ctrl-A (Keycode ctrl-97/65)
var ctrlA_Keycode = 97;
if ( ((BrowserDetect.OS=='Windows') || (navigator.appVersion.indexOf('Chrome')!=-1)) &&
	 (Prototype.Browser.WebKit || Prototype.Browser.IE) ) {
	ctrlA_Keycode = 65;
}
Koala.mapKey( function() {
	$K.yg_blockSelectComplete();
}, 'ctrl', ctrlA_Keycode, ctrlA_Keycode );


var deleteEvent = $K.defaultKeyEvent;
if ((BrowserDetect.OS=='Mac') && Prototype.Browser.WebKit) {
	deleteEvent = 'keydown';
}

// Map global enter button (Keycode 13)
Koala.mapKey( function() {
	if (($K.activeWindow) && ($K.activeWindow.id)) {
		var activeWindowObj = $K.windows[$K.activeWindow.id];
		if (typeof(activeWindowObj.submit) == 'function') {
			activeWindowObj.submit();
		} else {
			return false;
		}
	}
}, '', 13, 13 );


// Map global delete button (Keycode 46)
Koala.mapKey( function(event) {
	if (!$K.activeWindow) return;
	var checkArea, actionRef, actionFunction;
	var activeWindowObj = $K.windows[$K.activeWindow.id];

	// Check if multi-tabbed window
	if ((activeWindowObj.basetype == 'tree') && ($(activeWindowObj.id+'_'+activeWindowObj.tab.toUpperCase()).down('.wdgt_tree'))) {

		// Check if treewindow
		checkArea = $($K.activeWindow.id+'_innercontent');
		if (activeWindowObj.basetype == 'tree') {
			actionFunction = $K.yg_deleteElement;
			actionRef = checkArea.down('.selrow');
		} else {
			$K.warn( 'Not implemented yet', $K.Log.INFO );
		}

	} else {
		// Non-Treewindow
		focusobjs = $K.yg_getFocusObj($($K.activeWindow.id+'_'+activeWindowObj.tab.toUpperCase()));

		// Check if elements is descendent of the tab
		if (focusobjs.length > 0) {
			actionFunction = $K.yg_deleteElement;

			if (focusobjs[0].up('li')) {
				actionRef = focusobjs[0].up('li');
			} else if (focusobjs[0].tagName == 'TR') {
				actionRef = focusobjs[0];
			} else if (focusobjs[0].tagName == 'DIV') {
				actionRef = focusobjs[0];
			}

		} else {
			$K.log( 'Not in tab of objects', $K.Log.INFO );
		}
	}

	if (actionFunction && actionRef) {
		$K.yg_fireAction(actionFunction, actionRef, event );
	}
}, null, 46, 46, true, deleteEvent );


$K.yg_keylistenerRadio = {
	/**
	 * Helper function to detect if the spacebar was
	 * used to check a radiobutton.
	 * @param { Event } [event] The event which has been fired.
	 */
	keydown: function(event) {
		if (event.keyCode==32) {
			$K.yg_radioboxSelect(this);
		}
	}
}

$K.yg_keylistenerCheck = {
	/**
	 * Helper function to detect if the spacebar was
	 * used to check a checkbox.
	 * @param { Event } [event] The event which has been fired.
	 */
	keydown: function(event) {
		if (event.keyCode==32) {
			$K.yg_checkboxSelect(this);
		}
	}
}


/**
 * Fixes a problem when a non visible ("display:none;") custom control,
 * such as radiobuttons, checkboxes or dropdowns are focused.
 * @obj { Element } [obj] Formfield object
 * @obj { Element } [input] input field
 * @function
 * @name $K.yg_fixDiff
 */
$K.yg_fixDiff = function(input, obj) {

	if (input.up('.ywindow')) {
		boxid=input.up('.ywindow').id;
		objpos=Position.page(obj);
		objheight=obj.getHeight();
		/*
		if (input.up('.tcboxinner')) {
			searchtop = true;
		} */
	} else {
		return;
	}

	innerbox=$(boxid+'_ywindowinner');
	innerboxie=$(boxid+'_ywindowinnerie');
	innercontentobj=$(boxid+'_innercontent');

	/*	if (searchtop) {
		innerbox=$(boxid+'_searchinnercontent');
		innerboxie=$(boxid+'_searchinnercontentie');
		innercontentobj=$(boxid+'_searchinnercontentinner');
	} */

	if (innerbox) {
		//webkit fix
		//setTimeout('$("'+innercontentobj.id+'").scrollTop = 0;',1);
		innerbox.scrollTop = 0;

		scrollOffset = innercontentobj.positionedOffset()[1];
		objOffset = obj.cumulativeOffset()[1] - innercontentobj.cumulativeOffset()[1];

		if ((scrollOffset + objOffset) < 0) {
			scrollOffset = objOffset * -1 + 8;
		} else if ((scrollOffset + objOffset) > innerboxie.getHeight() - 8) {
			scrollOffset = objOffset * -1 - 8 - objheight + innerboxie.getHeight();
		}

		currentScroll = scrollOffset * -1;

		if ((scrollOffset == 0) ||
			($K.scrollbars[boxid].y == scrollOffset) ||
			($(boxid+'_dialogbottom') && (input.descendantOf($(boxid+'_dialogbottom')))) ||
			(input.up('.ywindowfilter')) ||
			(input.up('.ywindowspecialbottom')) ||
			(input.up('.ywindowfiltercolumn2'))) {
				return;
		} else {
			/*if (searchtop==true) {
				$K.scrollbars[boxid+"_searchcontent"].glideTo(0,currentScroll,1);
			} */
			$K.scrollbars[boxid].glideTo(0,currentScroll,100);
		}
	}
}


/**
 * Used to focus a custom form element.
 * @param { Element } [input] The element which has been given "focus"
 * @param { String } [type] The type of the form element involved.
 * @function
 * @name $K.yg_formFocus
 */
$K.yg_formFocus = function(input,type) {

	// Set focus-flag
	input.hasFocus = true;

	if (input.effect!=undefined) {
		input.effect.cancel();
		input.style.backgroundColor = '';
		input.removeClassName( 'changed' );
		input.effect = undefined;
	}

	input=$(input);
	if (type=="textbox") {
		parentobj=input.up();
		input.setStyle({backgroundColor:''});
		parentobj.removeClassName('textboxhover');
		parentobj.addClassName('textboxfocus');
		obj=input.up();
	} else if (type=="radiobox") {
		input.removeClassName('radioboxhover');
		input.addClassName('radioboxfocus');
		obj=input;
		$K.yg_keylistenerRadio.bkeydown=$K.yg_keylistenerRadio.keydown.bindAsEventListener(input);
		Event.observe(document, "keydown", $K.yg_keylistenerRadio.bkeydown);
	} else if (type=="checkbox") {
		input.removeClassName('checkboxhover');
		input.addClassName('checkboxfocus');
		obj=input;
		$K.yg_keylistenerCheck.bkeydown=$K.yg_keylistenerCheck.keydown.bindAsEventListener(input);
		Event.observe(document, "keydown", $K.yg_keylistenerCheck.bkeydown);
	} else if (type=="dropdownbox") {
		parentobj=input.up(2);

		if (!(Prototype.Browser.Gecko)) {
			$K.yg_setCaretToEnd(input);
		}
		if (!(parentobj.hasClassName('dropdownboxfocus'))) {
			parentobj.removeClassName('dropdownboxhover');
			parentobj.addClassName('dropdownboxfocus');
		}
		obj=input;
	}

	$K.yg_fixDiff(input,obj);

}


/**
 * Used to blur a custom form element.
 * @param { Element } [input] The element which has been given "focus"
 * @param { String } [type] The type of the form element involved.
 * @function
 * @name $K.yg_formBlur
 */
$K.yg_formBlur = function(input,type) {
	if (type=="textbox") {
		inputobj=input.up();
	} else if (type=="radiobox") {
		inputobj=input;
		Event.stopObserving(document, "keydown", $K.yg_keylistenerRadio.bkeydown);
	} else if (type=="checkbox") {
		inputobj=input;
		Event.stopObserving(document, "keydown", $K.yg_keylistenerCheck.bkeydown);
	} else if (type=="dropdownbox") {
		if ($K.yg_clickindropdown[input.up(2).id]!=true) {
			inputobj=input.up(2);
			tmpobj=$(inputobj.id+"_ddlist");
			tmpobj.hide();
		} else {
			inputobj=false;
		}
		if ($K.yg_preactive != false) {
			$K.yg_scrollObjAttr.activeArea = $K.yg_preactive;
			$K.yg_preactive = false;
		}
	}

	if (inputobj!=false) {
		inputobj.removeClassName(type+'focus');
		inputobj.addClassName(type);
	}

	// Set focus-flag
	input.hasFocus = false;
}


/**
 * Helper function for radioboxes; this function is mapped to
 * the element's onclick handler.
 * @param { Element } [obj] The element which has been clicked
 * @function
 * @name $K.yg_radioboxSelect
 */
$K.yg_radioboxSelect = function(obj) {
	obj=$(obj);

	if (obj.readAttribute('disabled')==null) {
		group=obj.up('.radiogroup');
		groupid=group.readAttribute('value');
		allradios=group.getElementsByClassName('radiobox');
		for (i=0;i<allradios.length;i++) {
			tmpobj=allradios[i].down();
			if (allradios[i].id==obj.id) {
				if (tmpobj.hasClassName('radioboxicon')) {
					tmpobj.addClassName('radioboxiconsel');
					tmpobj.removeClassName('radioboxicon');
				}
			} else {
				if (tmpobj.hasClassName('radioboxiconsel')) {
					tmpobj.addClassName('radioboxicon');
					tmpobj.removeClassName('radioboxiconsel');
				}
			}
		}
		$(groupid).value=obj.id;
	}

}


/**
 * Helper function for checkboxes; this function is mapped to
 * the element's onclick handler.
 * @param { Element } [obj] The element which has been clicked
 * @param { Element } [check] Specifies if the box is checked/unchecked
 * @function
 * @name $K.yg_checkboxSelect
 */
$K.yg_checkboxSelect = function(obj, check) {
	if (obj.readAttribute('disabled')==null) {
		input=obj.down('input');
		tmpobj=obj.down();

		if (check != undefined) {
			if (check == true) {
				tmpobj.removeClassName('checkboxicon');
				tmpobj.addClassName('checkboxiconsel');
				input.value=1;

			} else {
				tmpobj.removeClassName('checkboxiconsel');
				tmpobj.addClassName('checkboxicon');
				input.value=0;

			}
		} else {
			if (tmpobj.hasClassName('checkboxicon')) {
				tmpobj.removeClassName('checkboxicon');
				tmpobj.addClassName('checkboxiconsel');
				input.value=1;
			} else {
				tmpobj.removeClassName('checkboxiconsel');
				tmpobj.addClassName('checkboxicon');
				input.value=0;
			}
		}

		if (input.getAttribute('onchange')) {
			var func = new Function( input.getAttribute('onchange') );
			func.bind(obj)();
		}

	}
}


/**
 * Function for initialization of a dropdown form element.
 * @param { Element/String } [obj] Id/Element of the dropdown to initialize.
 * @function
 * @name $K.yg_initDropdown
 */
$K.yg_initDropdown = function(obj) {
	obj = $(obj);
	if (!obj) return;

	sel = obj.down('.dropdownlist').down('.selected');
	if (sel) {
		obj.down('input').value = $K.yg_stripFileTag(sel.innerHTML);
		if (sel.value != undefined) {
			hiddeninput = obj.down('input', 1);
			if (hiddeninput) hiddeninput.value = sel.value;
		}
	}
	if (obj.readAttribute("readonly") == "readonly") return;

	obj.stopObserving();
	obj.down('.dropdownlist').observe("mouseout", function() {
		$K.yg_dropdownActivateScroll(this,'out');
	});
	obj.down('.dropdownlist').observe("mouseover", function() {
		$K.yg_dropdownActivateScroll(this,'over');
	});
	innerobj = obj.down('.dropdowninner');
	innerobj.stopObserving();
	innerobj.observe("mouseup", function(ev) {
		if (ev) Event.stop(ev);
		$K.yg_clickindropdown[this.up(1).id] = false;
	});
	innerobj.observe("mousedown", function(ev) {
		$K.yg_clickindropdown[this.up(1).id] = true;
		$K.yg_dropdownClick(this.up(1), ev);
		$K.yg_formHover(this.down(),'dropdownclick','dropdownbox');
	});
	innerobj.observe("click", function(ev) {
		if (ev) Event.stop(ev);
	});
	inputobj = obj.down('input');
	inputobj.writeAttribute("readonly", "true");
	inputobj.stopObserving();
	inputobj.observe("blur", function() {
		$K.yg_clickindropdown[this.up(1).id] = true;
		$K.yg_formBlur(this,'dropdownbox');
	});
	inputobj.observe("focus", function() {
		$K.yg_formFocus(this,'dropdownbox');
	});
	inputobj.observe("keydown", function(ev) {
		if (!ev) ev = window.event;
		$K.yg_dropdownKey(this,ev.keyCode,1);
		if (ev.keyCode!=9) return false;
	});
	inputobj.observe("keypress", function(ev) {
		if (!ev) ev = window.event;
		$K.yg_dropdownKey(this,ev.keyCode,0);
		if (ev.keyCode!=9) return false;
	});
	$K.yg_clickindropdown[obj.id] = false;

	listcontainer = obj.down('.dropdownlistcontainer');
	if (listcontainer == undefined) return;
	listcontainer.stopObserving();
	listcontainer.observe("mouseup", function() {
		$K.yg_clickindropdown[this.id.substring(0,this.id.length-7)] = false;
		return false;
	});
	listcontainer.observe("mousedown", function() {
		$K.yg_clickindropdown[this.id.substring(0,this.id.length-7)] = true;
		return false;
	});
	if (($(obj.id + "_ddlist")) && ($(obj.id + "_ddlist") != listcontainer)) {
		$(obj.id + "_ddlist").remove();
	}
	if (obj.up('.emeditmode')) listcontainer.addClassName('editmode');
	listcontainer.id = obj.id + "_ddlist";
	listcontainer.setStyle({visibility:'visible'});
	listcontainer.hide();

	hiddeninput = obj.down('input',1);
	hiddeninput.name = obj.id;
	hiddeninput.writeAttribute( 'name', obj.id );

	list = listcontainer.down();

	tmparr = list.childElements();
	for(i=0;i<tmparr.length;i++) {
		tmparr[i].onclick = function() {
			inputobj = $($(this).up('.dropdownlistcontainer').id.substring(0,$(this).up('.dropdownlistcontainer').id.length-7)).down('input');
			$K.yg_dropdownSelect(inputobj.up('.dropdownbox'),this);
			$K.yg_formHover(inputobj,'dropdownclickclose','dropdown');
		}.bind(tmparr[i]);
	}
	if (obj.down('.mk_scrollbars')) {
		$K.yg_renderScroll(obj.down('.mk_scrollbars'),obj.id);
		$K.scrollbars[''+obj.id]=$K.yg_initScrollbars(obj, obj.down('.dropdownlistcontainer'), obj.down('.dropdownlist'), $(obj.id+'_dragbar_v'), $(obj.id+'_track_v'), $(obj.id+'_dragbar_h'), $(obj.id+'_track_h'), 0);
	}
}


/**
 * Function for initialization of a text form element.
 * @param { Element/String } [obj] Id/Element of the input textfield to initialize.
 * @function
 * @name $K.yg_initTextbox
 */
$K.yg_initTextbox = function(obj) {
	obj = $(obj)
	obj.observe("click", function(ev) {
		$K.yg_formHover(this,'click','textbox', ev);
	});
	field = obj.down('input');
	if (!(field) || (field.readAttribute("type")=="hidden")) {
		field = obj.down('textarea');
		if (field) {
			if (field.readAttribute('yg_autogrow') != "false") $K.yg_setTextareaAutogrow( field.identify() );
			if (field.readAttribute('maxlength') != null) {
				var evListener = function(ev) {
					return $K.yg_limitTextArea(this, this.readAttribute('maxlength'), ev);
				};
				field.observe('keydown', evListener);
				field.observe('keyup', evListener);
				field.observe('keypress', evListener);
			}
		} else {
			return;
		}
	}
	field.observe("focus", function() {
		$K.yg_formFocus(this,'textbox');
	});
	field.observe("blur", function() {
		$K.yg_formBlur(this,'textbox');
	});
}


/**
 * Function for initialization of a checkbox form element.
 * @param { Element/String } [obj] Id/Element of the checkbox to initialize.
 * @function
 * @name $K.yg_initCheckbox
 */
$K.yg_initCheckbox = function(obj) {
	obj = $(obj);
	obj.onselectstart = function() {
		return false;
	}
	obj.mousedown = function() {
		return false;
	}
	obj.onclick = function(ev) {
		if (ev) Event.stop(ev);
		if ($(this).readAttribute("readonly") == null) {
			$K.yg_checkboxClick(this);
			$K.yg_checkboxSelect(this);
			if ($(this).readAttribute("onchange") != null) {
				eval($(this).readAttribute("onchange"));
			}
		}
	}
	obj.onfocus = function() {
		$K.yg_formFocus(this,'checkbox');
	}
	obj.onblur = function() {
		$K.yg_formBlur(this,'checkbox');
	}
}


/**
 * Function for initialization of a radiobox form element.
 * @param { Element/String } [obj] Id/Element of the radiobox to initialize.
 * @function
 * @name $K.yg_initRadiobox
 */
$K.yg_initRadiobox = function(obj) {
	obj = $(obj);
	obj.onselectstart = function() {
		return false;
	}
	obj.observe("mousedown", function() {
		return false;
	});
	obj.observe("click", function(ev) {
		if (ev) Event.stop(ev);
		if ($(this).readAttribute("readonly") == null) {
			$K.yg_radioboxClick(this);
			$K.yg_radioboxSelect(this);
			if ($(this).readAttribute("onchange") != null) {
				eval($(this).readAttribute("onchange"));
			}
		}
	});
	obj.observe("focus", function() {
		$K.yg_formFocus(this,'radiobox');
	});
	obj.observe("blur", function() {
		$K.yg_formBlur(this,'radiobox');
	});
}


/**
 * Strips file tag from string
 * @param { String } [string] The clicked element.
 * @function
 * @name $K.yg_stripFileTag
 */
$K.yg_stripFileTag = function(string) {
	// check for filetypemarker
	pos=string.toUpperCase().indexOf('/DIV></DIV>');

	if (pos != -1) {
		return string.substring(pos+11, string.length).stripTags();
	} else {
		return string.stripTags();
	}
}


/**
 * Function for key-controlling a dropdown form element.
 * @param { Element } [obj] The element of the dropdown control.
 * @param { Int } [code] Keycode of the key pressed.
 * @param { Int } [downswitch] Flag which denotes if we were
 * called from the "onkeydown" or the "onkeypress" event handler.
 * @function
 * @name $K.yg_dropdownKey
 */
$K.yg_dropdownKey = function(obj,code,downswitch) {

	if (downswitch==1) $K.yg_dropdownkeydown=true;
	if (code==32) $K.yg_dropdownkeydown=false;

	if (Prototype.Browser.WebKit) {
		if ((downswitch==1) && (code==32)) {
			$K.yg_dropdownkeydown=true;
		} else {
			$K.yg_dropdownkeydown=false;
		}
	}

	if (Prototype.Browser.IE) $K.yg_dropdownkeydown=false;

	if ($K.yg_dropdownkeydown==false) {

		switch(code) {
			case 32:	$K.yg_formHover(obj,'dropdownclick','dropdown'); 			// spacebar
						break;
			case 13:	if ($(obj.up('.dropdownbox').id+"_ddlist").visible()) {	 // enter
							cursel=$(obj.up(2).id+"_ddlist").down('.hovered');
							if (!(cursel)) {
								cursel=$(obj.up(2).id+"_ddlist").down('.dropdownlist').down();
							}
							$K.yg_dropdownSelect(obj.up(2),cursel);
							$K.yg_formHover(obj,'dropdownclickclose','dropdown');
						}
						break;
			case 38:	if ($(obj.up('.dropdownbox').id+"_ddlist").visible()) {		// keyup
							$K.yg_dropdownMove(obj,'up');
						} else {
							$K.yg_dropdownSelect(obj.up(2),'up');
						}
						break;
			case 40:	if ($(obj.up('.dropdownbox').id+"_ddlist").visible()) {		// keydown
							$K.yg_dropdownMove(obj,'down');
						} else {
							$K.yg_dropdownSelect(obj.up(2),'down');
						}
						break;
		}
	} else {
		$K.yg_dropdownkeydown=false;
	}
}


/**
 * Focuses the dropdown by clicking.
 * @param { Element } [obj] The clicked element.
 * @param { Event } [ev] Event to stop.
 * @function
 * @name $K.yg_dropdownClick
 */
$K.yg_dropdownClick = function(obj, ev) {
	if (ev) Event.stop(ev);
	inputobj=obj.down('input');
	if (!inputobj.readAttribute('disabled')) inputobj.focus();
}


/**
 * Focuses the checkbox by clicking.
 * @param { Element } [obj] The clicked element.
 * @param { Event } [ev] Event to stop.
 * @function
 * @name $K.yg_checkboxClick
 */
$K.yg_checkboxClick = function(obj, ev) {
	if (ev) Event.stop(ev);
	inputobj=$(obj);

	// try/catch nedded for IE.. :(
	try {
		if (!inputobj.readAttribute('disabled')) inputobj.focus();
	} catch(e) {}
}


/**
 * Focuses the radiobox by clicking.
 * @param { Element } [obj] The clicked element.
 * @function
 * @name $K.yg_radioboxClick
 */
$K.yg_radioboxClick = function(obj) {
	inputobj=$(obj);
	// try/catch nedded for IE.. :(
	try {
		if (inputobj.readAttribute('disabled')==null) inputobj.focus();
	} catch(e) {}
}


/**
 * Used to select the currently focused element in the dropdown.
 * @param { Element } [obj] The element of the dropdown control.
 * @param { String } [dir] Keycode of the key pressed.
 * @param { String } [value] Value of the entry to be selected.
 * @param { Boolean } [nofunction] Set to true if callback shouldn't be called.
 * @function
 * @name $K.yg_dropdownSelect
 */
$K.yg_dropdownSelect = function(obj, dir, value, nofunction) {
	var cursel, nusel, identifier, color, oldflag;
	obj = $(obj);
	if (obj.nodeName == "INPUT") {
		obj = obj.up('.dropdownbox');
	}
	$K.inDropdownSelect = true;
	cursel = $($(obj).id+"_ddlist").down('.selected');
	oldflag = $(obj).down('span.filetype');

	if (value) {
		var list = $($(obj).id+"_ddlist").down('.dropdownlist').childElements();
		list.each(function(item) {
			if ((item.getAttribute('value')==value) && (item.getStyle("display") != "none")) {
				nusel = item;
				throw $break;
			}
		});
	} else {
		if (dir == 'up') {
			if ((cursel.previous()) && (cursel.previous().getStyle("display") != "none")) nusel = cursel.previous();
		} else if (dir == 'down') {
			if ((cursel.next()) && (cursel.next().getStyle("display") != "none")) nusel = cursel.next();
		} else {
			nusel = dir;
		}
	}
	hiddeninput = obj.down('input', 1);
	inputobj = obj.down('input');

	if (nusel) {

		// Check for flag
		if (nusel.down('span')) {
			identifier = nusel.down('span').innerHTML;
			color = nusel.down('span').className;
		} else {
			identifier = " ";
			color = "filetype";
		}

		if (cursel) cursel.removeClassName('selected');
		nusel.removeClassName('onhover');
		nusel.addClassName('selected');

		// check for filetypemarker
		inputobj.value = $K.yg_stripFileTag(nusel.innerHTML);

		if (identifier && color && oldflag) {
			oldflag.update(identifier);
			oldflag.className = color;
		}

		hiddeninput.value = nusel.readAttribute('value');
		inputobj.focus();
		if (inputobj.onchange && (typeof inputobj.onchange == 'function') && (nofunction!=true) ) {
			inputobj.onchange(inputobj);
		}
		if ( (inputobj.readAttribute('yg_onchange') != undefined) && (nofunction!=true) ) {
			window.temp_obj = inputobj;
			eval( inputobj.readAttribute('yg_onchange')+'("'+hiddeninput.value+'", "'+inputobj.readAttribute('yg_seq')+'", window.temp_obj);' );
			window.temp_obj = null;
		}
		returnvar = true;
	} else {
		returnvar = false;
	}
	$K.inDropdownSelect = false;

	if (typeof $K.callStack == 'function') {
		$K.log( 'Calling delayed function...', $K.Log.INFO );
		$K.callStack();
		$K.callStack = null;
	}
	return returnvar;
}


/**
 * Used to add entries to the dropdown
 * @param { Element } [obj] The element of the dropdown control.
 * @param { String } [title] Title of the value
 * @param { String } [value] Title of the value
 * @param { Boolean } [selected] True if entry is selected
 * @param { String } [position] top or bottom
 * @function
 * @name $K.yg_dropdownInsert
 */
$K.yg_dropdownInsert = function(obj,title,value,selected,position) {
	tmpobj = $($(obj).id+"_ddlist").down('.dropdownlist');
	dentry = document.createElement('div');

	if (position != 'top') {
		tmpobj.insert({ bottom: dentry });
	} else {
		tmpobj.insert({ top: dentry });
	}
	if (selected == 1) {
		if ($($(obj).id+"_ddlist").down('.dropdownlist').down('.selected')) $($(obj).id+"_ddlist").down('.dropdownlist').down('.selected').removeClassName('selected');
		obj.down('.dropdowninner').down('input').value = title;
		obj.down().next('input').value = value;
		dentry.className='selected';
	}
	dentry.value=value;
	dentry.setAttribute('value', value);
	dentry.appendChild(document.createTextNode(title));
	dentry.onclick = function() {
		inputobj = $($(this).up('.dropdownlistcontainer').id.substring(0,$(this).up('.dropdownlistcontainer').id.length-7)).down('input');
		$K.yg_dropdownSelect(inputobj.up('.dropdownbox'),this);
		$K.yg_formHover(inputobj,'dropdownclick','dropdown');
	}.bind(dentry);
}


/**
 * Used to remove entries from a dropdown
 * @param { Element } [obj] The element of the dropdown control.
 * @param { String } [value] Value to be removed
 * @function
 * @name $K.yg_dropdownRemove
 */
$K.yg_dropdownRemove = function(obj, value) {
	tmpobj = $($(obj).id+"_ddlist").down('.dropdownlist');
	tmpobj.childElements().each(function(item){
		if (item.readAttribute('value')==value) {
			if (item.hasClassName('selected')) {
				if (item.next()) {
					item.next().addClassName('selected');
					$(obj).down('input').value = item.next().readAttribute('value');
				} else if (item.previous()) {
					item.previous().addClassName('selected');
					$(obj).down('input').value = item.previous().readAttribute('value');
				} else {
					$(obj).down('input').value = '';
				}
			}
			item.remove();
		}
	});
	$K.yg_initDropdown($(obj).id);
}


/**
 * Used to move the focus to another selection in the dropdown control.
 * @param { Element } [obj] The element of the dropdown control.
 * @param { String } [dir] The direction the focus should move ("up" or "down").
 * @function
 * @name $K.yg_dropdownRemove
 */
$K.yg_dropdownMove = function(obj,dir) {
	boxobj=obj.up('.dropdownbox');
	listcontainer=$(boxobj.id+"_ddlist");

	cursel=listcontainer.down('.hovered');
	if (!(cursel)) {
		cursel=listcontainer.down('.selected');
		nusel=cursel;
	} else {
		if (dir=="up") {
			nusel=cursel.previous();
		} else if (dir=="down") {
			nusel=cursel.next();
		}
	}
	if (nusel) {
		cursel.removeClassName('hovered');
		nusel.addClassName('hovered');

		nuselpos=Position.page(nusel);
		listpos=Position.page(listcontainer);

		nuselheight=nusel.getHeight();
		listheight=listcontainer.getHeight();

		offset=listcontainer.down().getStyle('top');
		offset=parseInt(offset.substring(0,(offset.length-2)));

		if ((nuselpos[1]+nuselheight)>(listpos[1]+listheight)) {
			$K.scrollbars[boxobj.id].glideTo(0,(nuselpos[1]+nuselheight-listpos[1]-listheight-offset),1);
		} else if (nuselpos[1]<listpos[1]) {
			$K.scrollbars[boxobj.id].glideTo(0,(nuselpos[1]-listpos[1]-offset),1);
		}
	}
}


/**
 * Used to highlight ("hover") a custom form element.
 * @param { Element } [input] The focused element.
 * @param { String } [action] The action ("over", "out", "click" or "dropdownclick").
 * @param { String } [type] The type of the form element involved.
 * @param { Event } [ev] Event to stop.
 * @function
 * @name $K.yg_formHover
 */
$K.yg_formHover = function(input,action,type,ev) {

	if (ev) Event.stop(ev);
	input=$(input);
	if (action=="over") {
		if (!(input.hasClassName(type+'focus'))) {
			input.addClassName(type+'hover');
		}
	} else if (action=="out") {
		input.removeClassName(type+'hover');
	} else if (action=="click") {
		input.removeClassName(type+'hover');
		focusable = input.down('input');
		if ((focusable) && (focusable.readAttribute('type') != "hidden")) {
			focusable.focus();
		} else {
			tmpobj = input.down('textarea');
			if (tmpobj) { if(tmpobj.readAttribute('disabled')==null) tmpobj.focus(); }
		}
	} else if ((action=="dropdownclick") || (action=="dropdownclickclose")) {
		if (input.readAttribute('disabled')==null) {
			if (input.up(2)) {
				obj=$(input.up(2).id+"_ddlist");

				if ((action == "dropdownclick") && (obj.visible())) action = "dropdownclickclose";

				if ((action == "dropdownclickclose") && (obj)) {
					obj.hide();
					input.up(2).insert(obj);

					cursel=obj.up('.dropdownbox').down('.hovered');
					if (cursel) cursel.removeClassName('hovered');

					if ($K.yg_preactive != false) {
						$K.yg_scrollObjAttr.activeArea = $K.yg_preactive;
						$K.yg_preactive = false;
					}
				} else if (obj) {

					tmpwin = $(obj).up('.ywindow');
					if (tmpwin) {
						curzIndex = $(obj).up('.ywindow').getStyle('z-index');
						tmpcontainer = $(obj).up('.ywindow');
					} else {
						tmpcontainer = document.body;
						curzIndex = 100000;
					}
					obj.setStyle({zIndex:(curzIndex+2)});
					obj.down('.scrollbar_v').setStyle({zIndex:(curzIndex+3)});
					obj.down('.scrollbar_h').setStyle({zIndex:(curzIndex+3)});
					tmpcontainer.insert({bottom: obj});

					pos = input.up().viewportOffset();
					if (input.up('.ywindow')) {
						difpos = $(input.up('.ywindow')).viewportOffset();
						pos[0] -= difpos[0];
						pos[1] -= difpos[1];
					}

					obj.show();
					obj.setStyle({left: pos[0]+'px'});
					obj.setStyle({top: pos[1]+input.getHeight()+'px'});
					if (Prototype.Browser.IE) {
						obj.setStyle({width:(input.getWidth()+13)+'px'});
					} else {
						obj.setStyle({width:(input.getWidth()+14)+'px'});
					}
					list=obj.down('.dropdownlist');
					listcontainer=list.up();
					listcontainer.setStyle({height:'140px'});
					tmparr=list.immediateDescendants();
					listheight=tmparr.length*16;
					if (listheight<listcontainer.getHeight()) {
						if ((Prototype.Browser.IE) && (BrowserDetect.version == 7)) listheight += tmparr.length;
						listcontainer.setStyle({height:listheight+'px'});
					}
					if ($K.scrollbars[input.up(2).id]) $K.scrollbars[input.up(2).id].setBarSize();
					$K.yg_preactive = $K.yg_scrollObjAttr.activeArea;
					setTimeout("$K.yg_scrollObjAttr.activeArea='"+input.up(2).id+"';",50);
					$K.yg_scrollObjAttr.activeArea=input.up(2).id;
					input.up(2).observe('mouseover', function(ev) {
						$K.yg_scrollObjAttr.activeArea=this.id;
						if (ev) Event.stop(ev);
					});

				}
			}
		}
	}
}

/**
 * removes object from array of focused objects
 * @param { Element } [obj] The element which has to be removed
 * @function
 * @name $K.yg_removefromFocus
 */
$K.yg_removefromFocus = function(obj) {
	for (var i=0;i<$K.yg_currentfocusobj.length;i++) {
		if (($K.yg_currentfocusobj[i]==obj) || ($K.yg_currentfocusobj[i].descendantOf(obj))) {
			$K.yg_blockUnmark($K.yg_currentfocusobj[i]);
			$K.yg_currentfocusobj.splice(i,1);
		}
	}
}



/**
 * returns all selectable elements within focused area
 * @param { Element } [obj] any object within focused area
 * @function
 * @name $K.yg_getSelectables
 */
$K.yg_getSelectables = function(obj) {

	var tmparr = new Array();

	if (obj.hasClassName('mk_contentgroup')) { pcontent = obj; } else { pcontent=obj.up('.mk_contentgroup'); }

	contenttab = false;
	dropstack = false;
	list = false;
	table = false;
	tmpobj = true;

	if ((pcontent!=undefined) && (pcontent.readAttribute('yg_multiselect')=="true")) {

		tmparr=pcontent.down('ul');
		var i = 0;

		if (tmparr != undefined) {

			list = true;
			tmparr = new Array();
			tmpobjprev = false;
			tmpcontainer = pcontent.down('ul');

			while(tmpobj != undefined) {
				tmpobj = pcontent.down('ul', i);

				if (tmpobj != undefined) {
					i++;
					if (((tmpobjprev) && (tmpobj.descendantOf(tmpobjprev) == false)) || (tmpobjprev == false)) {
						tmpobjprev = tmpobj;
						tmpobj = tmpobjprev.immediateDescendants();
						tmpobj.each(function(item) {
							if (item!=undefined) {
								if ((item.hasClassName('mk_dummy') == false) && (item.tagName != 'INPUT')) {
									if (tmpcontainer.hasClassName('page_contentarea')) {
										// content tab
										tmparr.push($(item).down());
										contenttab = true;
									} else if (pcontent.hasClassName('mk_thumbcontainer')) {
										// thumblist
										tmparr.push($(item).down().next());
										list = false;
									} else {
										// anything else
										if (item.visible()) tmparr.push($(item).down(1).next());
									}
								}
							}
						});
					}
				}
			}

		} else if (pcontent.down('.ytable') && (pcontent.down('.tableborder').getStyle('display') != "none")) {
			// filemgr table
			tmparr=pcontent.down('.ytable').down('tbody').immediateDescendants();
			table = true;
		} else {
			tmparr=pcontent.immediateDescendants();
			list = false;
		}
		arrOut = new Array();

		for (var i = 0; i < tmparr.length; i++) {
			if ((tmparr[i].nodeName != "INPUT") && (!tmparr[i].hasClassName("mk_dummy")) && (tmparr[i].visible())) {
				arrOut.push(tmparr[i]);
			}
		}
	}

	return arrOut;
}


$K.yg_doubleClickTime = 0;
$K.yg_doubleClickObj = false;

/**
 * Selects a block.
 * @param { Element } [obj] The element which was clicked on.
 * @param { Event } [event] All events while clicking the block.
 * @function
 * @name $K.yg_blockSelect
 */
$K.yg_blockSelect = function(obj,event) {

	if (!obj) return;

	$K.yg_clearTextSelection();

	if (Boolean(obj.readAttribute('yg_selectable')) == false) return false;

	if (event) {
		var clickedElement = Event.findElement(event);
		if ((clickedElement.tagName == 'A') && clickedElement.up('.actionhover')) {
			return;
		}
	}

	$K.yg_fixCurrentFocusObjects();

	var pcontent = obj.up('.mk_contentgroup');

	$K.lastActiveContent = pcontent;

	var focussedobjects = $K.yg_getFocusObj(pcontent);

	obj=$(obj);

	shiftpressed=false;
	controlpressed=false;

	if (event) {
		//Event.stop(event);
		if (event && event.preventDefault) event.preventDefault();

		if (event.shiftKey) shiftpressed=true;
		if (BrowserDetect.OS=="Mac") {
			if (event.metaKey) controlpressed=true;
		} else {
			if (event.ctrlKey) controlpressed=true;
		}

	}

	var d = new Date();
	var now = d.getTime();
	var doubleclick = false;

	if (((now - $K.yg_doubleClickTime) < 400) && ($K.yg_doubleClickObj == obj)) {
		eval(obj.readAttribute('ondoubleclick'));
		doubleclick = true;

	} else {
		$K.yg_doubleClickTime = now;
		$K.yg_doubleClickObj = obj;
	}

	multiselecting=false;
	if (((controlpressed==true) || (shiftpressed==true)) && (focussedobjects.length>0)) {
		if ((pcontent) && (pcontent.readAttribute('yg_multiselect')=="true")) {
			multiselecting=true;
		}
	}

	alreadyselected=false;
	for (i=0;i<focussedobjects.length;i++) {
		if ((focussedobjects[i]==obj) && (doubleclick == false)) {
			focusfield = $K.yg_getActiveElement();
			if (focusfield) focusfield.blur();
			alreadyselected=true;
		}
	}

	if (controlpressed==true) {
		if (multiselecting==true) {

			if (alreadyselected==false) {
				$K.yg_currentfocusobj.push(obj);
				$K.yg_blockMark(obj);
			} else {
				$K.yg_removefromFocus(obj);
			}

		}

	} else if (shiftpressed==true) {


		if (multiselecting==true) {

			var tmparr = $K.yg_getSelectables(obj);

			first=-1;
			for (i=(tmparr.length-1);i>-1;i--) {
				tmpobj=tmparr[i];
				if (first==-1) {
					for (j=0;j<focussedobjects.length;j++) {
						if (tmpobj==focussedobjects[j]) {
							first=i;
						}
					}
				}
			}

			last=-1;
			for (i=0;i<tmparr.length;i++) {
				tmpobj=tmparr[i];
				if (last==-1) {
					for (j=0;j<focussedobjects.length;j++) {
						if (tmpobj==focussedobjects[j]) {
							last=i;
						}
					}
				}
			}

			var clicked;

			for (i=0;i<tmparr.length;i++) {

				tmpobj=tmparr[i];

				if (tmpobj==obj) {
					clicked=i;
				}
			}

			$K.yg_blockdeSelect(pcontent);

			if (clicked<=first) {
				last=first;
				first=clicked;
			} else if (clicked>=last) {
				first=last;
				last=clicked;
			}

			for (var i=first;i<(last+1);i++) {

				tmpobj=tmparr[i];

				if (tmpobj) {
					$K.yg_currentfocusobj.push(tmpobj);
					$K.yg_blockMark(tmpobj);
				}

			}

		}

	}

	if (multiselecting == false) {
		//if ((pcontent2 != undefined) && (pcontent2 != false)) pcontent2.removeClassName('multiselect');
		if (alreadyselected == false) {
			$K.yg_blockdeSelect(pcontent);
			$K.yg_currentfocusobj.push(obj);
			$K.yg_blockMark(obj);
		} else {
			var navList = false;
			if (obj.up('.mk_navpanel')) navList = true;
			if (!navList) {
				$K.yg_blockdeSelect(pcontent);
				if (focussedobjects.length > 1) {
					$K.yg_currentfocusobj.push(obj);
					$K.yg_blockMark(obj);
				}
			}
		}
	}

	if (pcontent && ($K.yg_getFocusObj(pcontent).length > 1)) {
		pcontent.addClassName('mk_multiselect');
	} else if (pcontent && ($K.yg_getFocusObj(pcontent).length > 0)) {
		pcontent.removeClassName('mk_multiselect');
	}

}



/**
 * Returns list of focussed objects within parent object
 * @param { Element } [obj] parent obj
 * @function
 * @name $K.yg_getFocusObj
 */
$K.yg_getFocusObj = function(obj) {
	var focusarr = new Array();
	$K.yg_currentfocusobj.each(function(item) {
		if (item && item.descendantOf(obj)) focusarr.push(item);
	});
	return focusarr;
}



/**
 * Removes text selection
 * @function
 * @name $K.yg_clearTextSelection
 */
$K.yg_clearTextSelection = function() {
	if (document.selection && document.selection.empty) {
		try {
			document.selection.empty();
		} catch (error) {
			// ignore error to as a workaround for bug in IE8
		}
	} else if (window.getSelection) {
		var sel = window.getSelection();
		try { sel.removeAllRanges(); } catch (e) { }
	}
}


/**
 * Selects all elements of the current selected block
 * @function
 * @name $K.yg_blockSelectComplete
 */
$K.yg_blockSelectComplete = function() {
	$K.yg_clearTextSelection();
	var tmparr = new Array();

	pcontent = $($K.lastActiveContent);

	if ((pcontent) && (pcontent.readAttribute('yg_multiselect')=="true")) {

		tmparr = $K.yg_getSelectables(pcontent);
 		$K.yg_blockdeSelect(pcontent);

   		for (var i=0;i<(tmparr.length);i++) {

			tmpobj = tmparr[i];

   			if ((tmpobj) && (tmpobj.readAttribute('yg_selectable')=="true")) {
   				$K.yg_currentfocusobj.push(tmpobj);
   				$K.yg_blockMark(tmpobj);
   			}
   		}

		pcontent.addClassName('multiselect');

		if ($K.windows[obj.up('.ywindow').id]) $K.windows[obj.up('.ywindow').id].refresh(obj);
	}
	$K.yg_toggleTreeButtons(obj.up('.ywindow').id);
}


/**
 * Highlights a block
 * @param { Element } [obj] The element which was clicked on.
 * @function
 * @name $K.yg_blockMark
 */
$K.yg_blockMark = function(obj) {
	if (obj.hasClassName('cntblockcontainer')) obj = obj.down().next();
	if (!obj) return;
	if (obj.hasClassName('cbcontainer')) {
		obj.removeClassName('cbcontainer');
		obj.addClassName('cbcontainerfocus');
	} else if (obj.hasClassName('cntblockselection')) {
		obj.removeClassName('cntblockselection');
		obj.addClassName('cntblockselectionfocus');
	} else if (obj.hasClassName('listitempage')) {
		obj.removeClassName('listitempage');
		obj.addClassName('listitempagefocus');
	} else if (obj.hasClassName('cntblock')) {
		obj.removeClassName('cntblock');
		obj.addClassName('cntblockfocus');
	} else if (obj.hasClassName('cntblockselectionlist')) {
		obj.removeClassName('cntblockselectionlist');
		obj.addClassName('cntblockselectionlistfocus');
	} else if (obj.hasClassName('emcontainer')) {
		obj.removeClassName('emcontainer');
		obj.addClassName('emcontainerfocus');
	} else if (obj.hasClassName('cntblockversionsjump')) {
		obj.removeClassName('cntblockversionsjump');
		obj.addClassName('cntblockversionsjumpfocus');
	}
	if (obj.previous() && obj.previous().hasClassName('actions')) {
		obj.up().addClassName('mk_selected');
	} else {
		obj.addClassName('mk_selected');
	}
}


/**
 * Removes highlight from a block
 * @param { Element } [obj] The element which was clicked on.
 * @function
 * @name $K.yg_blockUnmark
 */
$K.yg_blockUnmark = function(obj) {

	if (obj.hasClassName('cntblockcontainer')) obj = obj.down().next();

	if ((!obj) || (obj == null)) return;
	if (obj.hasClassName('cbcontainerfocus')) {
		obj.removeClassName('cbcontainerfocus');
		obj.addClassName('cbcontainer');
	} else if (obj.hasClassName('cntblockfocus')) {
		obj.removeClassName('cntblockfocus');
		obj.addClassName('cntblock');
	} else if (obj.hasClassName('cntblockselectionfocus')) {
		obj.removeClassName('cntblockselectionfocus');
		obj.addClassName('cntblockselection');
	} else if (obj.hasClassName('listitempagefocus')) {
		obj.removeClassName('listitempagefocus');
		obj.addClassName('listitempage');
	} else if (obj.hasClassName('cntblockselectionlistfocus')) {
		obj.removeClassName('cntblockselectionlistfocus');
		obj.addClassName('cntblockselectionlist');
	} else if (obj.hasClassName('emcontainerfocus')) {
		obj.removeClassName('emcontainerfocus');
		obj.addClassName('emcontainer');
		tmpobj=obj.down('.checkarray');
	} else if (obj.hasClassName('cntblockversionsjumpfocus')) {
		obj.removeClassName('cntblockversionsjumpfocus');
		obj.addClassName('cntblockversionsjump');
	}
	if (obj.previous() && obj.previous().hasClassName('actions')) {
		obj.up().removeClassName('mk_selected');
	} else {
		obj.removeClassName('mk_selected');
	}
}


/**
 * Deselects current selected block.
 * @param { Element } [parentobj] The element which was clicked on.
 * @function
 * @name $K.yg_blockdeSelect
 */
$K.yg_blockdeSelect = function(parentobj) {

	focussedobj = $K.yg_getFocusObj(parentobj);
	for(i=0;i<(focussedobj.length);i++) {
		$K.yg_removefromFocus(focussedobj[i]);
	}

}


/**
 * Tries to find a focusable formfield within the block and selects it.
 * @param { Element } [obj] The element which was clicked on.
 * @function
 * @name $K.yg_blockFocus
 */
$K.yg_blockFocus = function(obj) {

	obj=$(obj);
	focusobj=false;

	focusfield = $K.yg_getActiveElement();

	if ((focusfield==false) || ((focusfield.up('.cntblock')!=obj) && (focusfield.up('.cntblockfocus')!=obj))) {
		if (focusfield) focusfield.blur();
		if (obj.down('input')) {
			tmpobj=obj.down('input');
			if (tmpobj.readAttribute('type')=="hidden") {
				tmptmpobj=tmpobj.up().down('.radiobox');
				if ((tmptmpobj) && (tmptmpobj.readAttribute('disabled')==null)) {
					focusobj=tmptmpobj;
					if (Prototype.Browser.WebKit) {
						obj.removeClassName('cntblock');
						obj.addClassName('cntblockfocus');
					}
				} else {
					tmpobj=tmpobj.up(2).down('.checkbox');
					if ((tmpobj) && (tmpobj.readAttribute('disabled')==null)) {

						focusobj=tmpobj;
					}
				}
			} else {
				tmpobj=obj.down('input');
				if ((tmpobj) && (tmpobj.readAttribute('disabled')==null)) focusobj=tmpobj;
			}
		} else {
			tmpobj=obj.down('textarea');
			if ((tmpobj) && (tmpobj.readAttribute('disabled')==null)) focusobj=tmpobj;
		}
		if (focusobj!=false) {
			if ($K.yg_currentfocusobj.length<2) focusobj.focus();
		}
	}

}


/**
 * Removes entry from detailsearch
 * @param { Element } [obj] The object which will be removed.
 * @param { Element } [boxid] Id of the window object.
 * @function
 * @name $K.yg_removeSearchEntry
 */
$K.yg_removeSearchEntry = function(obj,boxid) {
	obj.remove();
	$K.windows[boxid].init(null,null,false,true,1);
}


/**
 * Used to highlight ("hover") a custom form element.
 * @param { Element } [input] The element which has been given "focus"
 * @param { String } [action] The action ("over", "out", "click" or "dropdownclick")
 * @param { String } [type] The type of the form element involved.
 * @function
 * @name $K.yg_dropdownActivateScroll
 */
$K.yg_dropdownActivateScroll = function(obj, dir) {
	if (dir=="over") {
		obj=$(obj);
		cont=$(obj.id+"_ddlist");
		if ((cont) && (cont.visible())) {
			if ($K.yg_scrollObjAttr.activeArea!=obj.id) {
				$K.yg_preactive = $K.yg_scrollObjAttr.activeArea;
				$K.yg_scrollObjAttr.activeArea = obj.id;
			}
		}
	} else {
		if ($K.yg_scrollObjAttr.activeArea==obj.id) {
			if ($K.yg_preactive != false) {
				$K.yg_scrollObjAttr.activeArea = $K.yg_preactive;
				$K.yg_preactive = false;
			}
		}
	}
}


/**
 * Helper function to programatically select a part of a text
 * @param { Element } [input] The input field involved.
 * @param { Int } [selectionStart] The beginning of the desired selection.
 * @param { Int } [selectionEnd] The end of the desired selection.
 * @function
 * @name $K.yg_setSelectionRange
 */
$K.yg_setSelectionRange = function(input, selectionStart, selectionEnd) {
  	if (input.setSelectionRange) {
		input.focus();
		input.setSelectionRange(selectionStart, selectionEnd);
  	} else if (input.createTextRange) {
		var range = input.createTextRange();
		range.collapse(true);
		range.moveEnd('character', selectionEnd);
		range.moveStart('character', selectionStart);
		range.select();
 	 }
}


/**
 * Helper function to programatically set the input "caret" (the cursor)
 * to the end of the input field
 * @param { Element } [input] The input element involved.
 * @function
 * @name $K.yg_setCaretToEnd
 */
$K.yg_setCaretToEnd = function(input) {
	$K.yg_setSelectionRange(input, input.value.length, input.value.length);
}


/**
 * Fades a textfield-background from green to white
 * @param { Element } [input] The input element involved.
 * @function
 * @name $K.yg_fadeField
 */
$K.yg_fadeField = function(input) {
	input.removeClassName('changed');
	input.setStyle({backgroundColor:'#e6fcb3'});
	if ((input.up('.dropdownbox')) || (input.up('.textboxfocus'))) {
		endcolorvar = '#e6feff';
	} else if ((input.type) && ((input.type == "textarea") || (input.type == "text"))) {
		if ((input.descendantOf('dialogcontainer')) && ((input.up('.emcontentinner') == undefined))) {
			endcolorvar = '#252D23';
		} else if (input.up('.emcontentinner') != undefined) {
			endcolorvar = '#EFF0EB';
		} else {
			endcolorvar = '#ffffff';
		}
	} else if ((input.descendantOf('dialogcontainer')) && ((input.up('.emcontentinner') == undefined))) {
		// dialog
		endcolorvar = '#252D23';
	} else if (input.up('.emcontentinner') != undefined) {
		// editmask
		endcolorvar = '#ffffff';
	} else {
		// window
		endcolorvar = '#F4F4F0';
	}
	input.effect  = new Effect.Highlight(input, {
									startcolor: '#e6fcb3',
									endcolor: endcolorvar,
									restorecolor: 'false',
									duration: 0.5,
									beforeStart: function() { },
									afterFinish: function() {
										input.removeClassName('changed');
										input.setStyle( {backgroundColor: ''} );
									}
	});
}


/**
 * Fades a number of textfield-background from green to white
 * @param { Element } [container] The container, which children should be faded (only inputfields and textfields)
 * @param { String } [cssFilter] The css classes to match
 * @param { String } [cssFilter2] The css classes to match
 * @param { String } [cssFilter3] The css classes to match
 * @function
 * @name $K.yg_fadeFields
 */
$K.yg_fadeFields = function(container, cssFilter, cssFilter2, cssFilter3) {
	if (cssFilter2 && cssFilter3) {
		var fields = container.select(cssFilter, cssFilter2, cssFilter3);
	} else if (cssFilter2) {
		var fields = container.select(cssFilter, cssFilter2);
	} else {
		var fields = container.select(cssFilter);
	}
	fields.each(function(item){
		$K.yg_fadeField( item );
	});
}


/**
 * Saves all the data from a WYSIWYG-Inputfield
 * @param { Element } [input] The input element involved.
 * @function
 * @name $K.yg_saveWYSIWYG
 */
$K.yg_saveWYSIWYG = function( input ) {
	$K.yg_editControl(input, '3');	// 3 stands for WYSIWYG
}


/**
 * Saves all the data from a WYSIWYG-Inputfield
 * @param { Element } [which] The input element involved.
 * @function
 * @name $K.yg_removeWYSIWYGControl
 */
$K.yg_removeWYSIWYGControl = function( which ) {
	if (tinyMCE && tinyMCE.editors[which]) {
		$K.log( tinyMCE.editors[which], $K.Log.INFO );
		delete tinyMCE.editors[which];
	}
}


/**
 * Helper for limiting Textarea lengths
 * @param { Element } [which] The relevant element (the textarea).
 * @param { Integer } [maxlen] The maximum length the textarea is limited to.
 * @param { Event } [event] The event which triggered the functioncall.
 * @function
 * @name $K.yg_limitTextArea
 */
$K.yg_limitTextArea = function (which, maxlen, event) {
	if (!maxlen) maxlen=0;
	maxlen = parseInt(maxlen);

	if ( (maxlen!=0) && (which.value.length > maxlen) ) {
		which.value = which.value.substring(0,maxlen);
	}

	if ( ((event.keyCode >= 37)&&(event.keyCode <= 40)) ||		// Cursor keys
		 (event.keyCode == 8) ||								// Backspace
	 	 (event.keyCode == 46) ||								// Delete
	 	 (event.keyCode == 35) ||								// End
	 	 (event.keyCode == 36) ||								// Home
		 (isNaN(maxlen)) ||
		 (maxlen == 0) ||
		 (event.ctrlKey) ||
		 (event.metaKey) ||
		 (event.altKey) ) {
			return;
	}
	$K.log( 'keyCode was: ', event.keyCode, $K.Log.DEBUG );
	return (which.value.length <= (maxlen-1) );
}


/**
 * Helper function used to reset the style of all formfields (edited, error) when an AJAX-request takes place
 * @function
 * @name $K.yg_cleanStyles
 */
$K.yg_cleanStyles = function () {

	$K.yg_cleanWindows();

	if ($('placeHolder')) $('placeHolder').hide();

	return;

	for (var i in $K.yg_idlookuptable) {
		for (var j=0; j < $K.yg_idlookuptable[i].length; j++) {

			var element = $($K.yg_idlookuptable[i][j]);
			if (Object.isUndefined(element.tagName)) {
				element = $($K.yg_idlookuptable[i][j].id);
			}

			// Tree item
			if (!Object.isUndefined(element.capt)) {
				element.removeClassName('error');
			} else
			if (element.hasClassName('dropdownbox')) {
				element.down('input').removeClassName('error');
			} else
			// No tree item
			element.removeClassName('error');

		}
	}

}


/**
 * Supplemental function to initialize the calendar
 * @param { String } [calendarRef] Id of the calendar
 * @function
 * @name $K.yg_initCalendar
 */
$K.yg_initCalendar = function( calendarRef ) {

	calendarRef = $(calendarRef);

	var pastAvailable = false;
	if (calendarRef.readAttribute('pastavailable') == 'true') {
		pastAvailable = true;
	}

	var calIdx = calendarRef.identify();
	var dateFormat = calendarRef.readAttribute('format');
	var targetFieldRef = $(calendarRef.readAttribute('targetfield'));
	var targetAnchorRef = new Element('a', {id: calIdx+'_anchor', name: calIdx+'_anchor'});
	targetFieldRef.insert({after:targetAnchorRef});

	calendarRef.observe('click', function(e) {
		$K.yg_calendars[calIdx].select(targetFieldRef, targetAnchorRef.identify(), dateFormat);
		Event.stop(e);
		return false;
	});

	$K.yg_calendars[calIdx] = new CalendarPopup('yg_caldiv');
	$K.yg_calendars[calIdx].showNavigationDropdowns();
	$K.yg_calendars[calIdx].setMonthNames(	$K.TXT('TXT_JANUARY'),
											$K.TXT('TXT_FEBRUARY'),
											$K.TXT('TXT_MARCH'),
											$K.TXT('TXT_APRIL'),
											$K.TXT('TXT_MAY'),
											$K.TXT('TXT_JUNE'),
											$K.TXT('TXT_JULY'),
											$K.TXT('TXT_AUGUST'),
											$K.TXT('TXT_SEPTEMBER'),
											$K.TXT('TXT_OCTOBER'),
											$K.TXT('TXT_NOVEMBER'),
											$K.TXT('TXT_DECEMBER')
										);
	$K.yg_calendars[calIdx].setDayHeaders(	$K.TXT('TXT_SUNDAY_ABBR'),
											$K.TXT('TXT_MONDAY_ABBR'),
											$K.TXT('TXT_TUESDAY_ABBR'),
											$K.TXT('TXT_WEDNESDAY_ABBR'),
											$K.TXT('TXT_THURSDAY_ABBR'),
											$K.TXT('TXT_FRIDAY_ABBR'),
											$K.TXT('TXT_SATURDAY_ABBR')
										);

	var weekStart = 0;
	if ($K.userSettings.weekStart) {
		weekStart = $K.userSettings.weekStart;
	}

	$K.yg_calendars[calIdx].setWeekStartDay( weekStart );
	$K.yg_calendars[calIdx].setCssPrefix('yg_');
	$K.yg_calendars[calIdx].offsetX = 0;
	$K.yg_calendars[calIdx].offsetY = 0;
	if (Prototype.Browser.IE) {
		$K.yg_calendars[calIdx].offsetX = -270;
		$K.yg_calendars[calIdx].offsetY = -5;
	}
	$K.yg_calendars[calIdx].yearSelectStartOffset = 15;
	now = new Date();
	yesterday = new Date( Date.parse(now) - (86400 * 1000) );
	if (pastAvailable != true) $K.yg_calendars[calIdx].addDisabledDates(null,formatDate(yesterday, 'yyyy-MM-dd'));

	calendarRef.inited = true;
}


/**
 * Supplemental function to check if a date is valid
 * @param { String } [value] The value to check.
 * @param { String } [format] The format to the value against.
 * @function
 * @name $K.yg_checkDate
 */
$K.yg_checkDate = function( value, format, seperator ) {
	var checkstr = '0123456789';
	var Datevalue = '';
	var DateTemp = '';
	var day;
	var month;
	var year;
	var leap = 0;
	var err = 0;
	var i;
	var dayPosition, monthPosition, yearPosition;

	// First add leading zeros (if not already there)
	var tmpValue = value;
	tmpValue = tmpValue.split(seperator);
	if (tmpValue.length < 2) {
		return '';
	}
	if (tmpValue[0].length != 2) {
		tmpValue[0] = '0'.times(2-tmpValue[0].length) + tmpValue[0];
	}
	if (tmpValue[1].length != 2) {
		tmpValue[1] = '0'.times(2-tmpValue[1].length) + tmpValue[1];
	}
	value = tmpValue.join(seperator);

	format = format.toLowerCase().replace(/[^A-Za-z0-9]/g, '');
	if (format.length == 6) {
		format = format.replace(/yy/, 'yyyy');
	}
	if (format.length != 8) {
		err = 19;
	}

	dayPosition = format.indexOf('dd');
	monthPosition = format.indexOf('mm');
	yearPosition = format.indexOf('yyyy');

	DateValue = value;
	// Delete all chars except 0..9
	for (i = 0; i < DateValue.length; i++) {
		if (checkstr.indexOf(DateValue.substr(i,1)) >= 0) {
			DateTemp = DateTemp + DateValue.substr(i,1);
		}
	}
	DateValue = DateTemp;
	// Always change date to 8 digits - string
	// if year is entered as 2-digit / always assume 20xx
	if (DateValue.length == 6) {
		//DateValue = DateValue.substr(0,4) + '20' + DateValue.substr(4,2);
		DateValue = DateValue.substr(0,yearPosition) + '20' + DateValue.substr(yearPosition,2);
	}
	if (DateValue.length != 8) {
		err = 19;
	}
	// year is wrong if year = 0000
	year = DateValue.substr(yearPosition,4);
	if (year == 0) {
		err = 20;
	}
	// Validation of month
	month = DateValue.substr(monthPosition,2);
	if ((month < 1) || (month > 12)) {
		err = 21;
	}
	// Validation of day
	day = DateValue.substr(dayPosition,2);
	if (day < 1) {
		err = 22;
	}
	// Validation leap-year / february / day
	if ((year % 4 == 0) || (year % 100 == 0) || (year % 400 == 0)) {
		leap = 1;
	}
	if ((month == 2) && (leap == 1) && (day > 29)) {
		err = 23;
	}
	if ((month == 2) && (leap != 1) && (day > 28)) {
		err = 24;
	}
	// Validation of other months
	if ((day > 31) && ((month == '01') || (month == '03') || (month == '05') || (month == '07') || (month == '08') || (month == '10') || (month == '12'))) {
		err = 25;
	}
	if ((day > 30) && ((month == '04') || (month == '06') || (month == '09') || (month == '11'))) {
		err = 26;
	}
	// if 00 ist entered, no error, deleting the entry
	if ((day == 0) && (month == 0) && (year == 00)) {
		err = 0; day = ''; month = ''; year = ''; seperator = '';
	}
	// if no error, write the completed date to Input-Field (e.g. 13.12.2001)
	if (err == 0) {
		return day + seperator + month + seperator + year;
	}
	// Return false if err != 0
	else {
		return false;
	}
}


/**
 * Checks an input field for given parameters
 * @param { Element } [element] The element whose value to check.
 * @param { Object } [parameters] Parameters object.
 * @function
 * @name $K.yg_checkField
 */
$K.yg_checkField = function ( element, parameters ) {
	var result = true;

	element.removeClassName('error');
	switch (parameters.type) {
		case 'integer':
			var min = parameters.min;
			var max = parameters.max;
			if ( (parseInt(element.value) != element.value) ||
				 (parseInt(element.value) < min) ||
				 (parseInt(element.value) > max) ) {
					element.addClassName( 'error' );
					result = false;
			}
			break;
		case 'date':
			if (parameters.format != '') {
				var seperator = parameters.format.replace(/\w+/g, '').substring(0, 1);

				// Check if date is valid
				var dateCheckResult = $K.yg_checkDate( element.value, parameters.format, seperator );
				if (dateCheckResult==false) {
					element.addClassName( 'error' );
					result = false;
				} else {
					// Update field
					element.value = dateCheckResult;

					if (parameters.nodatesinpast==true) {
						// Check if the date lies in the past

						var dayPosition = parameters.format.toLowerCase().split(seperator).indexOf('dd');
						var monthPosition = parameters.format.toLowerCase().split(seperator).indexOf('mm');
						var yearPosition = parameters.format.toLowerCase().split(seperator).indexOf('yy');
						if (yearPosition == -1) {
							yearPosition = parameters.format.toLowerCase().split(seperator).indexOf('yyyy');
						}

						var now = new Date();
						var check_day = parseInt(element.value.split(seperator)[dayPosition],10);
						var check_month = parseInt(element.value.split(seperator)[monthPosition],10);
						if (element.value.split(seperator)[yearPosition].length == 2) {
							var check_year = parseInt(element.value.split(seperator)[yearPosition],10) + 2000;
						} else {
							var check_year = parseInt(element.value.split(seperator)[yearPosition],10);
						}

						// Calculate yesterday (and strip the time)
						var yesterday = new Date( Date.parse(now) - (86400 * 1000) );
						yesterday = new Date( yesterday.getFullYear(), yesterday.getMonth(), yesterday.getDate(), 0, 0, 0 );

						// Strip time from date to check
						var check_date = new Date( check_year, check_month-1, check_day, 0, 0, 0 );

						if (check_date <= yesterday) {
							element.addClassName( 'error' );
							result = false;
						}

					}
				}
			}
			break;
		case 'time':

			switch(parameters.format) {
				case '12':
					var hour = element.value.split(':')[0];
					var minute = element.value.split(':')[1].split(' ')[0];
					var identifier = element.value.split(':')[1].split(' ')[1];
					if ( (hour.length < 1) || (hour.length > 2) ) {
						element.addClassName( 'error' );
						result = false;
					}
					if ( (minute.length < 1) || (minute.length > 2) ) {
						element.addClassName( 'error' );
						result = false;
					}
					if (!$K.yg_IsNumeric(hour) || !$K.yg_IsNumeric(minute)) {
						element.addClassName( 'error' );
						result = false;
					}
					if ( (hour < 1) || (hour > 12) ) {
						element.addClassName( 'error' );
						result = false;
					}
					if ( (minute < 0) || (hour > 59) ) {
						element.addClassName( 'error' );
						result = false;
					}
					if ( (identifier != 'am') && (identifier != 'pm') ) {
						element.addClassName( 'error' );
						result = false;
					}
					break;
				case '24':
					var hour = element.value.split(':')[0];
					var minute = element.value.split(':')[1];
					if ( (hour.length < 1) || (hour.length > 2) ) {
						element.addClassName( 'error' );
						result = false;
					}
					if ( minute && ((minute.length < 1) || (minute.length > 2)) ) {
						element.addClassName( 'error' );
						result = false;
					}
					if (!$K.yg_IsNumeric(hour) || !$K.yg_IsNumeric(minute)) {
						element.addClassName( 'error' );
						result = false;
					}
					if ( (hour < 0) || (hour > 23) ) {
						element.addClassName( 'error' );
						result = false;
					}
					if ( (minute < 0) || (hour > 59) ) {
						element.addClassName( 'error' );
						result = false;
					}
					break;
			}

			break;
	}
	if (parameters.emptyallowed && (element.value == '')) {
		element.removeClassName('error');
	}
	return result;
}

/**
 * Sort the currently selected Element up or down
 * @param { Element } [which] The element (li) to move.
 * @param { Element } [dir] 1 or -1
 * @function
 * @name $K.yg_sortEntry
 */
$K.yg_sortEntry = function(which, dir) {
	var classes = which.className.split(" ");
	var classx = '';
	classes.each(function(item) {
		if (item.indexOf('mk_') > -1) {
			classx = '.'+item;
			throw $break;
		}
	});
	elems = $$('#'+which.up().identify()+' li'+classx);
	for (var i = 0; i < elems.length; i++) {
		if (elems[i] == which) {
			if (elems[i+dir]) {
				if (dir == -1) {
					elems[i+dir].insert({before:which});
				} else {
					elems[i+dir].insert({after:which});
				}
			}
		}
	}
	$K.yg_showHelp(false);
}

/**
 * Helper function for autogrowing Textareas
 * @param { Element } [which] The relevant element (the textarea).
 * @function
 * @name yg_setTextareaAutogrow
 */
$K.yg_setTextareaAutogrow = function( which ) {
	which = $(which);
	if (!which) return;
	if (which.getWidth() <= 0) return;

	which.autogrow = {
		dummy: null,
		interval: null,
		line_height: parseInt(which.getStyle('line-height')),
		min_height: parseInt(which.getStyle('min-height')),
		max_height: parseInt(which.getStyle('max-height'))
	}

	if (isNaN(which.autogrow.line_height)) {
		which.autogrow.line_height = 0;
	}

	var startExpand = function() {
		which.autogrow.interval = window.setInterval(function() { checkExpand() }, 400);
	}

	var stopExpand = function() {
		clearInterval( which.autogrow.interval );
	}

	var checkExpand = function() {

		if (which.autogrow.dummy == null) {

			which.autogrow.dummy = document.createElement('DIV');
			$(document.body).insert({bottom: which.autogrow.dummy});

			$(which.autogrow.dummy).setStyle({
				fontSize	: which.getStyle('font-size'),
				fontFamily	: which.getStyle('font-family'),
				width		: which.getWidth()+'px',
				padding		: which.getStyle('padding'),
				lineHeight	: which.autogrow.line_height + 'px',
				overflowX	: 'hidden',
				position	: 'absolute',
				top			: '0px',
				left		: '-9999px',
				wordWrap	: 'break-word',
				lineBreak	: 'strict',
				backgroundColor: '#cccccc',
				border		: 'solid 2px #ff0000'
			});
		}

		// Strip HTML tags
		var html = which.value.escapeHTML();
		html = html.replace(/\t/g, '    ')
			.replace(/  /g, '&nbsp; ')
			.replace(/  /g, ' &nbsp;');	// second pass
										// handles odd number of spaces, where we
										// end up with "&nbsp;" + " " + " "

		// IE is different, as per usual
		if (Prototype.Browser.IE) {
			html = html.replace(/\n/g, '<BR>new');
		} else {
			html = html.replace(/\n/g, '<br>new');
		}

		if (which.autogrow.dummy.innerHTML != html) {
			which.autogrow.dummy.innerHTML = html;

			if (!which.parentWin) {
				if (which.up('.ywindow')) {
					which.parentWin = which.up('.ywindow');
					var windowId = which.up('.ywindow').id;
					$K.windows[windowId].refresh(which);
				}
			} else {
				if (which.parentWin) {
					var windowId = which.parentWin.id;
					$K.windows[windowId].refresh(which);
				}
			}

			if (which.autogrow.max_height > 0 && (which.autogrow.dummy.getHeight() + which.autogrow.line_height > which.autogrow.max_height)) {
				which.setStyle({overflowY: 'auto'});
			} else {
				which.setStyle({overflowY: 'hidden'});
				if (which.getHeight() < which.autogrow.dummy.getHeight() + which.autogrow.line_height || (which.autogrow.dummy.getHeight() < which.getHeight())) {
					new Effect.Morph(which, {
						style: {height: which.autogrow.dummy.getHeight() + which.autogrow.line_height + 'px'},
						duration: 0.1,
						afterFinish: function() {
							if (which.parentWin) {
								var windowId = which.parentWin.id;
								$K.windows[windowId].refresh(which);
							}
						}
					});
				}
			}

		}
	}

	which.setStyle({overflow: 'hidden', display: 'block'});

	which.stopObserving('focus', startExpand);
	which.stopObserving('blur', stopExpand);
	which.observe('focus', startExpand);
	which.observe('blur', stopExpand);

	// Initial scaling
	checkExpand();

	which.autogrow.checkExpand = checkExpand;
}



/**
 * Open time picker
 * @param { Element } [obj] textfield obj
 * @param { String } [int] 12 or 24
 * @function
 * @name $K.yg_pickTime
 */
$K.yg_pickTime = function(obj, format) {
	$K.yg_customAttributeHandler($('yg_timediv'));

	$K.bCloser['yg_timediv'] = $K.yg_clickCloser.bindAsEventListener($('yg_timediv'));
	Event.observe(document, 'click', function(e) {
		var target = (e.target)?(e.target):(e.srcElement);
		if (!target.descendantOf($('yg_timediv')) && (typeof $K.bCloser['yg_timediv'] == 'function') ) {
			$K.bCloser['yg_timediv'](e);
			$K.yg_pickTimeChange();
		}
	});

	$K.yg_pickTimeObj = obj;

	// pre-populate value of textfield
	timevar = obj.value;
	if (format == 12) {
		$('yg_timediv').addClassName("yg12h");
		$('yg_timediv_hh_ddlist').addClassName("yg12h");
		ampmvar = timevar.split(" ");
		if ((ampmvar[1] == undefined) || (ampmvar[1].toUpperCase() != "AM") && (ampmvar[1].toUpperCase() != "PM")) {
			ampmvar = "am";
		} else {
			timevar = ampmvar[0];
			ampmvar = ampmvar[1];
		}
		$K.yg_dropdownSelect($('yg_timediv_ampm'), false, ampmvar, true);
	} else {
		$('yg_timediv').removeClassName("yg12h");
		$('yg_timediv_hh_ddlist').removeClassName("yg12h");
	}
	timevar = timevar.split(":");
	if (timevar.length != 2) {
		hhvar = mmvar = "";
		mmvar = "";
	} else {
		hhvar = timevar[0];
		mmvar = timevar[1];
	}

	// positioning
	objpos = $(obj).cumulativeOffset();
	$('yg_timediv').setStyle({left:(objpos[0]+70)+'px'});
	$('yg_timediv').setStyle({top:(objpos[1]-18)+'px'});
	$('yg_timediv').setStyle({display:'block'});

	if ($K.yg_dropdownSelect($('yg_timediv_hh'), false, hhvar.toString(), true) == false) {
		if (format == 12) {
			$K.yg_dropdownSelect($('yg_timediv_hh'), false, "12", true);
		} else {
			$K.yg_dropdownSelect($('yg_timediv_hh'), false, "00", true);
		}
	}

	if ($K.yg_dropdownSelect($('yg_timediv_mm'), false, mmvar.toString(), true) == false) {
		$K.yg_dropdownSelect($('yg_timediv_mm'), false, "00", true);
	}

}


/**
 * Change time
 * @function
 * @name $K.yg_pickTimeChange
 */
$K.yg_pickTimeChange = function() {
	if ($('yg_timediv').hasClassName('yg12h')) { var format = 12 } else { var format = 24 }
	var hh = $('yg_timediv_hh').down('input', 1).value;
	var mm = $('yg_timediv_mm').down('input', 1).value;
	var timestring = hh + ":" + mm;
	if (format == 12) {
		timestring += " " + $('yg_timediv_ampm').down('input', 1).value;
	}
	$K.yg_pickTimeObj.value = timestring;
	if (typeof $K.yg_pickTimeObj.onchange == 'function') $K.yg_pickTimeObj.onchange();
	$('yg_timediv_hh_ddlist').hide();
	$('yg_timediv_mm_ddlist').hide();
	$K.yg_pickTimeObj.blur();
}
