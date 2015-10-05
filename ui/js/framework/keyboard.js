/** 
 * @fileoverview Used to map and unmap a keyboard key or a
 * combination of a key and (a) modifier(s) to a javascript function.
 */

if (typeof Koala=="undefined") var Koala = new Object;

/**
 * Holds the handles to the mapped key/function pair.
 * This is required to be able to correctly unmap a previously
 * mapped key AND thus to prevent memory leaks.
 * @type Array of Int
 * @addon Koala
 */
Koala.mappedKeys = {
	maps: {},
	amount: 0
};

/**
 * Helper function which is called when the keypress event is fired.
 * This functions then calls the desired user defined function when
 * the combination of keys and modifiers match the values previously
 * defined when mapping the key.
 * Only used internally.
 * @type Bool
 * @param { Object } [event] The event object
 * @param { Function } [keyfunction] The user defined function to call
 * (also gets the keycode passed)
 * @param { String } [modifier] The desired modifier ('shift', 'ctrl' or 'alt')
 * @param { Int } [keycodestart] The start of keycode range to map
 * @param { Int } [keycodeend] (Optional) The end of keycode range to map
 * @param { Boolean } [donotstopevent] (Optional) Don't stop the event propagation if true
 * @addon Koala
 * @function
 * @name Koala.mapKeyHelper
 */
Koala.mapKeyHelper = function (event) {
	
	var targetNodeName = event.target.nodeName;
	if ( (targetNodeName=='INPUT') ||
		 (targetNodeName=='TEXTAREA') ) {
		donotstopevent = true;
		return;
	}
	
	var kc = (event.which)?(event.which):(event.keyCode);
	
	// Call the desired function
	for (keymap in Koala.mappedKeys.maps) {
		var checkMap = Koala.mappedKeys.maps[keymap];
		
		if (checkMap.event == event.type) {
			// Check if keycode is in range
			if ( (kc >= checkMap.kc_start) && (kc <= checkMap.kc_end) ) {
			
				// Make modifiers os-independent
				switch (checkMap.mod) {
					case 'ctrl':
						realmodifier = event.ctrlKey;
						if (BrowserDetect.OS=="Mac")
							realmodifier = event.metaKey;
						break;
					case 'alt':
						realmodifier = event.altKey;
						break;
					case 'shift':
						realmodifier = event.shiftKey;
						break;
					case 'ctrl-meta':
						realmodifier = (event.ctrlKey && event.metaKey);
						break;
					default:
						realmodifier = true;
						break;
				}
				
				// Check if modifier is the right one
				if (realmodifier) {
				
					// Prevent default browser behaviour
					if (!checkMap.dontstop && event && event.preventDefault) {
						event.preventDefault();
					}
					if (!checkMap.dontstop && event && event.stopPropagation) {
						event.stopPropagation();
					}
					
					// And run the mapped function
					checkMap.func( kc, event );

					if (!checkMap.dontstop) {
						Event.stop(event);
					}

				}
				
			}
		}
	}
	
};


// Initalize Keyboard-Handler
$K.defaultKeyEvent = 'keypress';
if ( ((BrowserDetect.OS=='Windows') || (navigator.appVersion.indexOf('Chrome')!=-1)) &&
	 (Prototype.Browser.WebKit || Prototype.Browser.IE) ) {
	$K.defaultKeyEvent = 'keydown';
}
Event.observe(document, 'keypress',  Koala.mapKeyHelper );
Event.observe(document, 'keydown',  Koala.mapKeyHelper );


/**
 * Used to map a desired key (or range) and modifier to a user
 * defined function. 
 * @type Int
 * @param { Function } [keyfunction] The user defined function to call
 * (also gets the keycode passed)
 * @param { String } [modifier] The desired modifier ('shift', 'ctrl' or 'alt')
 * @param { Int } [keycodestart] The start of keycode range to map
 * @param { Int } [keycodeend] (Optional) The end of keycode range to map
 * @param { Boolean } [donotstopevent] (Optional) Don't stop the event propagation if true
 * @returns A handle to be able to unmap the key later on
 * @addon Koala
 * @function
 * @name Koala.mapKey
 */
Koala.mapKey = function ( keyfunction, modifier, keycodestart, keycodeend, donotstopevent, eventToCheck ) {
	
	// Check if eventToCheck was defined
	if (!eventToCheck) {
		eventToCheck = $K.defaultKeyEvent;
	}

	// Check if a range was defined
	if (!keycodeend)
		keycodeend = keycodestart;

	var mapping = {
		event:		eventToCheck,
		func:		keyfunction,
		mod:		modifier,
		kc_start:	keycodestart,
		kc_end:		keycodeend,
		dontstop:	donotstopevent
	}
	
	Koala.mappedKeys.maps['keymap_'+Koala.mappedKeys.amount] = mapping;
	Koala.mappedKeys.amount++;
	
	return 'keymap_'+(Koala.mappedKeys.amount-1);
}

/**
 * Used to unmap a previously defined key (or range) and modifier
 * @param { Function } [keyfunction] The user defined function to call
 * (also gets the keycode passed)
 * @param { Int } [handle] The handle which was previously returned from mapKey
 * on mapping
 * @addon Koala
 * @function
 * @name Koala.unMapKey
 */
Koala.unMapKey = function ( handle ) {
	$K.log( 'Koala.mappedKeys', Koala.mappedKeys, $K.Log.DEBUG );
	if (Koala.mappedKeys.maps[handle]) {
		delete Koala.mappedKeys.maps[handle];
		Koala.mappedKeys.amount--;
	}
	$K.log( 'Koala.mappedKeys (2)', Koala.mappedKeys, $K.Log.DEBUG );
}
