/**
 * @fileoverview renderes scrollbars
 */

/**
 * Holds information about all (dynamically generated & static)
 * scrollbars and their layers.
 * @type Array of Objects
 */
$K.yg_scrollObjs = {};

/**
 * The default scrolling speed
 * @type Int
 */

$K.yg_scrollObjAttr = {};
$K.yg_scrollObjAttr.speed = 100;
/**
 * The default slide duration
 * @type Int
 */
$K.yg_scrollObjAttr.slideDur = 500; // duration of glide
/**
 * Holds the id of the currently active area (for the mousewheel functionality)
 * @type String
 */
$K.yg_scrollObjAttr.activeArea = null;
/**
 * Is "true" when an element is currently sliding, else "false"
 * @type Bool
 */
$K.yg_scrollObjAttr.sliding = false;
/**
 * Holds the width in pixels for a vertical scrollbar button.
 * @type Int
 */
$K.yg_scrollObjAttr.scrollBtnVWidth  =  9;
/**
 * Holds the height in pixels for a vertical scrollbar button.
 * @type Int
 */
$K.yg_scrollObjAttr.scrollBtnVHeight = 13;
/**
 * Holds the width in pixels for a horizontal scrollbar button.
 * @type Int
 */
$K.yg_scrollObjAttr.scrollBtnHWidth  = 13;
/**
 * Holds the height in pixels for a horizontal scrollbar button.
 * @type Int
 */
$K.yg_scrollObjAttr.scrollBtnHHeight =  9;

/**
 * The $K.yg_scrollObj class.
 * @class This is the basic $K.yg_scrollObj class.
 * It contains all properties related to a scrollable area.
 * @param { String } [boxId] The id scrollbar container.
 * @param { String } [wnId] The id of the box in which the scrollbars are located.
 * @param { String } [lyrId] The id of the scrollable layer.
 * @param { String } [cntId] The id of the content.
 * @param { String } [cntId] The id of the blank corner.
 * @param { String } [frameReference] (optional) Reference to the IFrame (if scrollbars are in iframe)
 * @name $K.yg_scrollObj
 */
$K.yg_scrollObj = function ( boxId, wnId, lyrId, cntId, blankcorner, frameReference) {
	this._ = new Object;
	if ( frameReference && (frameReference.tagName=='IFRAME') ) {
		this._ = frameReference;
	} else {
		this._.$ = $;
	}

	this.blank = blankcorner;
	this.id = boxId;
	this.boxid = boxId;

	this.wnid = wnId;
	$K.yg_scrollObjs[this.id] = this;
	this.animString = "$K.yg_scrollObjs['" + this.id + "']";
	this.load(lyrId, cntId, blankcorner);

	if (cntId) {
		$(cntId).onclick = function () {
			$K.yg_scrollObjAttr.activeArea = this.id;
		}.bind(this);
	}

	$K.yg_scrollObjAttr.activeArea = this.id;
};


/**
 * The loadLayer function is used to exchange a layer inside
 * a scrollable area.
 * @param { String } [wnId] "ScrollObj-id" of the involved element.
 * @param { String } [id] Id of the involved element.
 * @param { String } [cntId] Id of the new element which will be loaded.
 * into the container.
 * @function
 * @name $K.yg_scroll_loadLayer
 */
$K.yg_scroll_loadLayer = function( wnId, id, cntId) {
	if ( $K.yg_scrollObjs[wnId] )
		$K.yg_scrollObjs[wnId].load(id,cntId);
};


/**
 * The actual loadLayer function.
 * Only used internally.
 * @param { String } [lyrId] Id of the involved element.
 * @param { String } [cntId] Id of the new element which will be loaded.
 * @function
 * @name $K.yg_scrollObj.prototype.load
 */
$K.yg_scrollObj.prototype.load = function( lyrId, cntId ) {
	var wndo, lyr;
	if ( this.lyrId ) {
		lyr = $( this.lyrId );
		lyr.style.visibility = "hidden";
	}
	lyr = $( lyrId );
	wndo = $( this.wnid );
	lyr.style.top = this.y = 0;
	lyr.style.left = this.x = 0;
	this.maxY = ( lyr.offsetHeight - wndo.offsetHeight > 0 ) ? lyr.offsetHeight - wndo.offsetHeight : 0;
	this.wd = cntId ? $( cntId ).offsetWidth : lyr.offsetWidth;
	this.maxX = ( this.wd - wndo.offsetWidth > 0 ) ? (this.wd-wndo.offsetWidth) : 0;
	this.lyrId = lyrId;
	lyr.style.visibility = "visible";
	this.on_load();
	this.ready = true;
};


/**
 * Dummy onload function which will be executed when a new layer is loaded
 * into a scrollable area.
 * @function
 * @name $K.yg_scrollObj.prototype.on_load
 */
$K.yg_scrollObj.prototype.on_load = function() {};


/**
 * Used to shift a layer inside a scrollable area to the coordinates
 * specified.
 * @param { Element } [lyr] The involved layer element.
 * @param { Int } [x] X coordinates.
 * @param { Int } [y] Y coordinates.
 * @function
 * @name $K.yg_scrollObj.prototype.shiftTo
 */
$K.yg_scrollObj.prototype.shiftTo = function( lyr, x, y ) {
	if ( !lyr.style )
		return;
	lyr.style.left = ( this.x = x ) + "px";

	// check if fixed column header is there and scroll if necessary
	if ($K.windows[this.boxid]) {
		$K.windows[this.boxid].scrollh(x);
	} else {
		tmpid = this.boxid.split('_');
		tmpid = tmpid[0] + "_" + tmpid[1];
		if ($K.windows[tmpid]) {
			$K.windows[tmpid].scrollh(x,"col2");
		}
	}

	// check if dropdown is opened
	focusfield = $K.yg_getActiveElement();
	if ((focusfield) && (focusfield != undefined)) {
		focusfield = focusfield.up(2);
		if ((focusfield) && (focusfield.id != this.id) && (focusfield.hasClassName('dropdownbox')) && ($(focusfield.id+"_ddlist"))) {
			focusfield.insert($(focusfield.id+"_ddlist"));
			$(focusfield.id+"_ddlist").hide();
		}
	}

	lyr.style.top = ( this.y = y ) + "px";
};


/**
 * Fixes a problem with older Gecko browsers (pre-Firefox)
 * Only used internally.
 * @function
 * @name yg_scroll_GeckoTableBugFix
 */
yg_scroll_GeckoTableBugFix = function() {
	var ua = navigator.userAgent;
	if( ua.indexOf("Gecko") > -1 && ua.indexOf("Firefox") == -1 && ua.indexOf("Safari") == -1 && ua.indexOf("Konqueror") == -1) {
		$K.yg_scrollObj.hold = [];
		for (var i=0; arguments[i]; i++) {
			if ( $K.yg_scrollObjs[ arguments[i] ] ) {
				var wndo = $( arguments[i] );
				//var holderId = wndo.up().id;
				var holderId = wndo.parentNode.id;
				var holder = $( holderId );
				document.body.appendChild( holder.removeChild( wndo ) );
				wndo.style.zIndex = 1000;
				var pos = $K.yg_scroll_getPageOffsets( holder );
				wndo.style.left = pos.x + "px";
				wndo.style.top = pos.y + "px";
				$K.yg_scrollObj.hold[i] = [arguments[i], holderId];
			}
		}
		window.addEventListener( "resize", $K.yg_scroll_rePositionGecko, true );
	}
};


/**
 * Fixes a problem with older Gecko browsers (pre-Firefox)
 * Only used internally.
 * @function
 * @name $K.yg_scroll_rePositionGecko
 */
$K.yg_scroll_rePositionGecko = function() {
	if ($K.yg_scrollObj.hold) {
		for (var i=0; $K.yg_scrollObj.hold[i]; i++) {
			var wndo = $( $K.yg_scrollObj.hold[i][0] );
			var holder = $( $K.yg_scrollObj.hold[i][1] );
			var pos = $K.yg_scroll_getPageOffsets(holder);
			wndo.style.left = pos.x + "px";
			wndo.style.top = pos.y + "px";
		}
	}
};


/**
 * Gets the current pageoffset (cross-browser)
 * Only used internally.
 * @param { Element } [el] The element involved.
 * @type Object
 * @returns Returns an object which contains the x and y coordinates of
 * the requested element.
 * @function
 * @name $K.yg_scroll_getPageOffsets
 */
$K.yg_scroll_getPageOffsets = function ( el ) {
	var left = el.offsetLeft;
	var top = el.offsetTop;
	if ( el.offsetParent && el.offsetParent.clientLeft || el.offsetParent.clientTop ) {
		left += el.offsetParent.clientLeft;
		top += el.offsetParent.clientTop;
	}
	while ( el = el.offsetParent ) {
		left += el.offsetLeft;
		top += el.offsetTop;
	}
	return { x:left, y:top };
};


/**
 * Stops the scrolling of the specified scrolling layer.
 * Only used internally.
 * @param { String } [wnId] The id of the scrollable layer.
 * @function
 * @name $K.yg_scroll_stopScroll
 */
$K.yg_scroll_stopScroll = function(wnId) {
  if ( $K.yg_scrollObjs[wnId] ) $K.yg_scrollObjs[wnId].endScroll();
}


/**
 * Doubles the speed of scrolling
 * @param { String } [wnId] The id of the scrollable layer.
 * @function
 * @name $K.yg_scroll_doubleSpeed
 */
$K.yg_scroll_doubleSpeed = function(wnId) {
  if ( $K.yg_scrollObjs[wnId] ) $K.yg_scrollObjs[wnId].speed *= 2;
}


/**
 * Resets the speed of scrolling to the original value.
 * @param { String } [wnId] The id of the scrollable layer.
 * @function
 * @name $K.yg_scroll_resetSpeed
 */
$K.yg_scroll_resetSpeed = function(wnId) {
  if ( $K.yg_scrollObjs[wnId] ) $K.yg_scrollObjs[wnId].speed /= 2;
}


/**
 * Algorithms for time-based scrolling and scrolling onmouseover at
 * any angle adapted from youngpup.net
 * @param { String } [wnId] The id of the scrollable layer.
 * @param { Int } [deg] The direction in degrees to scroll into.
 * @param { Int } [sp] The speed of scrolling.
 * @function
 * @name $K.yg_scroll_initScroll
 */
$K.yg_scroll_initScroll = function( wnId, deg, sp ) {
  if ( $K.yg_scrollObjs[wnId] ) {
	var cosine, sine;
	if (typeof deg == "string") {
	  switch (deg) {
		case "up"	: deg = 90;  break;
		case "down"  : deg = 270; break;
		case "left"  : deg = 180; break;
		case "right" : deg = 0;   break;
		default:
		  $K.log("Direction of scroll in mouseover scroll links should be 'up', 'down', 'left', 'right' or number: 0 to 360.", $K.Log.WARN);
	   }
	}
	deg = deg % 360;
	if (deg % 90 == 0) {
	  cosine = (deg == 0)? -1: (deg == 180)? 1: 0;
	  sine = (deg == 90)? 1: (deg == 270)? -1: 0;
	} else {
	  var angle = deg * Math.PI/180;
	  cosine = -Math.cos(angle); sine = Math.sin(angle);
	}
	$K.yg_scrollObjs[wnId].fx = cosine / ( Math.abs(cosine) + Math.abs(sine) );
	$K.yg_scrollObjs[wnId].fy = sine / ( Math.abs(cosine) + Math.abs(sine) );
	$K.yg_scrollObjs[wnId].endX = (deg == 90 || deg == 270)? $K.yg_scrollObjs[wnId].x:
	  (deg < 90 || deg > 270)? -$K.yg_scrollObjs[wnId].maxX: 0;
	$K.yg_scrollObjs[wnId].endY = (deg == 0 || deg == 180)? $K.yg_scrollObjs[wnId].y:
	  (deg < 180)? 0: -$K.yg_scrollObjs[wnId].maxY;
	$K.yg_scrollObjs[wnId].startScroll(sp);
  }
}


/**
 * Function for scrolling while dragging
 * @param { String } [wnId] The id of the scrollable layer.
 * @param { Int } [deg] The direction in degrees to scroll into.
 * @param { Int } [sp] The speed of scrolling.
 * @function
 * @name $K.yg_scroll_hoverScroll
 */
$K.yg_scroll_hoverScroll = function( wnId, deg, sp ) {
	if ($K.yg_activeDragInfo.dragging)
		$K.yg_scroll_initScroll( wnId, deg, sp );
}


/**
 * Function for stopping scrolling while dragging
 * @param { String } [wnId] The id of the scrollable layer.
 * @param { Int } [deg] The direction in degrees to scroll into.
 * @param { Int } [sp] The speed of scrolling.
 * @function
 * @name $K.yg_scroll_stopHoverScroll
 */
$K.yg_scroll_stopHoverScroll = function( wnId ) {
	if ($K.yg_activeDragInfo.dragging)
		$K.yg_scroll_stopScroll( wnId );
}


/**
 * Starts scrolling of the involved layer
 * @param { Int } [speed] (Optional) The speed of the scrolling
 * (when not set the default will be used)
 * @function
 * @name $K.yg_scrollObj.prototype.startScroll
 */
$K.yg_scrollObj.prototype.startScroll = function(speed) {
  if (!this.ready) return; if (this.timerId) clearInterval(this.timerId);
  this.speed = speed || $K.yg_scrollObjAttr.speed;
  this.lyr = $(this.lyrId);
  this.lastTime = ( new Date() ).getTime();
  this.on_scroll_start();
  this.timerId = setInterval(this.animString + ".scroll();", 10);
}


/**
 * The actual function for scrolling. Is controlled by a timer.
 * Only used internally.
 * @function
 * @name $K.yg_scrollObj.prototype.scroll
 */
$K.yg_scrollObj.prototype.scroll = function() {
  var now = ( new Date() ).getTime();
  var d = (now - this.lastTime)/1000 * this.speed;
  if (d > 0) {
	var x = this.x + this.fx * d; var y = this.y + this.fy * d;
	if (this.fx == 0 || this.fy == 0) { // for horizontal or vertical scrolling
	  if ( ( this.fx == -1 && x > -this.maxX ) || ( this.fx == 1 && x < 0 ) ||
		( this.fy == -1 && y > -this.maxY ) || ( this.fy == 1 && y < 0 ) ) {
		this.lastTime = now;
		this.shiftTo(this.lyr, x, y);
		this.on_scroll(x, y);
	  } else {
		clearInterval(this.timerId); this.timerId = 0;
		this.shiftTo(this.lyr, this.endX, this.endY);
		this.on_scroll_end(this.endX, this.endY);
	  }
	} else { // for scrolling at an angle (stop when reach end on one axis)
	  if ( ( this.fx < 0 && x >= -this.maxX && this.fy < 0 && y >= -this.maxY ) ||
		( this.fx > 0 && x <= 0 && this.fy > 0 && y <= 0 ) ||
		( this.fx < 0 && x >= -this.maxX && this.fy > 0 && y <= 0 ) ||
		( this.fx > 0 && x <= 0 && this.fy < 0 && y >= -this.maxY ) ) {
		this.lastTime = now;
		this.shiftTo(this.lyr, x, y);
		this.on_scroll(x, y);
	  } else {
		clearInterval(this.timerId); this.timerId = 0;
		this.on_scroll_end(this.x, this.y);
	  }
	}
  }
}

/**
 * The actual function for stopping scrolling.
 * Only used internally.
 * @function
 * @name $K.yg_scrollObj.prototype.endScroll
 */
$K.yg_scrollObj.prototype.endScroll = function() {
  if (!this.ready) return;
  if (this.timerId) clearInterval(this.timerId);
  this.timerId = 0;  this.lyr = null;
}


/**
 * Dummy function for pseudo event 'on_scroll'
 * May be replaced on runtime with custom code.
 * @function
 * @name $K.yg_scrollObj.prototype.on_scroll
 */
$K.yg_scrollObj.prototype.on_scroll = function() {}


/**
 * Dummy function for pseudo event 'on_scroll_start'
 * May be replaced on runtime with custom code.
 * @function
 * @name $K.yg_scrollObj.prototype.on_scroll_start
 */
$K.yg_scrollObj.prototype.on_scroll_start = function() {}


/**
 * Dummy function for pseudo event 'on_scroll_end'
 * May be replaced on runtime with custom code.
 * @function
 * @name $K.yg_scrollObj.prototype.on_scroll_end
 */
$K.yg_scrollObj.prototype.on_scroll_end = function() {}


/**
 * Scrolls the specified layer by the specified amount of
 * pixels in the given duration.
 * Only used internally.
 * @param { String } [wnId] The scrolling layer involved.
 * @param { Int } [x] The horizontal amount of pixels to scroll.
 * @param { Int } [y] The vertical amount of pixels to scroll.
 * @param { Int } [dur] The duration of the scrolling.
 * @function
 * @name $K.yg_scroll_scrollBy
 */
$K.yg_scroll_scrollBy = function(wnId, x, y, dur) {
  if ($K.yg_scrollObjs[wnId]) {
  	// don't scroll horizontal if horizontal scrollbar hidden
  	if ($K.yg_scrollObjs[wnId].hbar) {
  		if ((x != 0) && ($K.yg_scrollObjs[wnId].hbar.getStyle("visibility") == "hidden")) x = 0;
  	} else {
  		x = 0;
  	}
  	$K.yg_scrollObjs[wnId].glideBy(x, y, dur);
  }
}


/**
 * Scrolls the specified layer to the coordinates specified
 * in the given duration.
 * Only used internally.
 * @param { String } [wnId] The scrolling layer involved.
 * @param { Int } [x] The target x coordinates.
 * @param { Int } [y] The target y coordinates.
 * @param { Int } [dur] The duration of the scrolling.
 * @function
 * @name $K.yg_scroll_scrollTo
 */
$K.yg_scroll_scrollTo = function(wnId, x, y, dur) {
  if ( $K.yg_scrollObjs[wnId] ) $K.yg_scrollObjs[wnId].glideTo(x, y, dur);
}


/**
 * Resources for time-based slide algorithm:
 * DHTML chaser tutorial at DHTML Lab - www.webreference.com/dhtml
 * and cbe_slide.js from www.cross-browser.com by Mike Foster
 *
 * Scrolls the specified layer by the specified amount of
 * pixels in the given duration.
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @param { Int } [dx] The horizontal amount of pixels to scroll.
 * @param { Int } [dy] The vertical amount of pixels to scroll.
 * @param { Int } [dur] The duration of the scrolling.
 * @function
 * @name $K.yg_scrollObj.prototype.glideBy
 */
$K.yg_scrollObj.prototype.glideBy = function(dx, dy, dur) {
  if ( this.sliding ) return;
  this.slideDur = dur || $K.yg_scrollObjAttr.slideDur;
  this.destX = this.destY = this.distX = this.distY = 0;
  this.lyr = $(this.lyrId);
  this.startX = this.x; this.startY = this.y;
  if (dy < 0) this.distY = (this.startY + dy >= -this.maxY)? dy: -(this.startY  + this.maxY);
  else if (dy > 0) this.distY = (this.startY + dy <= 0)? dy: -this.startY;
  if (dx < 0) this.distX = (this.startX + dx >= -this.maxX)? dx: -(this.startX + this.maxX);
  else if (dx > 0) this.distX = (this.startX + dx <= 0)? dx: -this.startX;
  this.destX = this.startX + this.distX; this.destY = this.startY + this.distY;
  this.slideTo(this.destX, this.destY);
}


/**
 * Scrolls the layer to the coordinates specified in the given duration.
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @param { Int } [destX] The target x coordinates.
 * @param { Int } [destY] The target y coordinates.
 * @param { Int } [dur] The duration of the scrolling.
 * @function
 * @name $K.yg_scrollObj.prototype.glideTo
 */
$K.yg_scrollObj.prototype.glideTo = function(destX, destY, dur) {
	if ( this.sliding) return;
	this.slideDur = dur || $K.yg_scrollObjAttr.slideDur;
	this.lyr = $(this.lyrId);
	this.startX = this.x; this.startY = this.y;
	this.destX = -Math.max( Math.min(destX, this.maxX), 0);
	this.destY = -Math.max( Math.min(destY, this.maxY), 0);
	this.distY = this.destY - this.startY;
	this.distX =  this.destX - this.startX;
	this.slideTo(this.destX, this.destY);
}


/**
 * Sets up the actual scrolling mechanism by setting up a timer
 * with an interval. Also calculates the values for accelerating
 * and breaking ("ease in and ease out")
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @param { Int } [destX] The target x coordinates.
 * @param { Int } [destY] The target y coordinates.
 * @function
 * @name $K.yg_scrollObj.prototype.slideTo
 */
$K.yg_scrollObj.prototype.slideTo = function(destX, destY) {
	this.per = Math.PI/(2 * this.slideDur); this.sliding = true; $K.yg_scrollObjAttr.sliding = true;
	this.slideStart = (new Date()).getTime();
	this.aniTimer = setInterval(this.animString + '.doSlide();', 10);
	this.on_slide_start(this.startX, this.startY);
}


/**
 * The actual scrolling mechanism which is called by the timer
 * set up by "slideTo".
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @function
 * @name $K.yg_scrollObj.prototype.doSlide
 */
$K.yg_scrollObj.prototype.doSlide = function() {
	var elapsed = (new Date()).getTime() - this.slideStart;
	if (elapsed < this.slideDur) {
		var x = this.startX + this.distX * Math.sin(this.per*elapsed);
		var y = this.startY + this.distY * Math.sin(this.per*elapsed);
		this.shiftTo(this.lyr, x, y); this.on_slide(x, y);
	} else {	// if time's up
		clearInterval(this.aniTimer); this.sliding = false; $K.yg_scrollObjAttr.sliding = false;
		this.shiftTo(this.lyr, this.destX, this.destY);
		this.lyr = null; this.on_slide_end(this.destX, this.destY);
	}
}


/**
 * Dummy function for pseudo event 'on_slide_start'
 * May be replaced on runtime with custom code.
 * @function
 * @name $K.yg_scrollObj.prototype.on_slide_start
 */
$K.yg_scrollObj.prototype.on_slide_start = function() {

}


/**
 * Dummy function for pseudo event 'on_slide'
 * May be replaced on runtime with custom code.
 * @function
 * @name $K.yg_scrollObj.prototype.on_slide
 */
$K.yg_scrollObj.prototype.on_slide = function() {}


/**
 * Dummy function for pseudo event 'on_slide_end'
 * May be replaced on runtime with custom code.
 * @function
 * @name $K.yg_scrollObj.prototype.on_slide_end
 */
$K.yg_scrollObj.prototype.on_slide_end = function() {

}


/**
 * Helper function for drag'n'drop of the dragbar.
 * Model: Aaron Boodman's dom drag at www.youngpup.net
 * @function
 * @name $K.yg_slidebar
 */
$K.yg_slidebar = {
  obj: null,
  slideDur: 500,  // duration of glide onclick of track
  init: function (bar, track, axis, x, y) {
	x = x || 0; y = y || 0;
	bar.style.left = x + "px"; bar.style.top = y + "px";
	bar.axis = axis; track.bar = bar;

	bar.alignment = axis;

	if (axis == "h") {
	  bar.trkWd = track.offsetWidth; // hold for setBarSize
	  bar.maxX = bar.trkWd - bar.offsetWidth - x;
	  bar.minX = x; bar.maxY = y; bar.minY = y;
	} else {
	  bar.trkHt = track.offsetHeight;
	  bar.maxY = bar.trkHt - bar.offsetHeight - y;
	  bar.maxX = x; bar.minX = x; bar.minY = y;
	}
	bar.on_drag_start =  bar.on_drag =   bar.on_drag_end =
	bar.on_slide_start = bar.on_slide =  bar.on_slide_end = function() {}
	bar.onmousedown = this.startDrag; track.onmousedown = this.startSlide;
 	bar.observe("click", function(e) { Event.stop(e); });
  },

  startSlide: function(e) { // called onmousedown of track
	if ( $K.yg_slidebar.aniTimer ) clearInterval($K.yg_slidebar.aniTimer);
	e = e? e: window.event;
	var bar = $K.yg_slidebar.obj = this.bar; // i.e., track's bar
	e.offX = (typeof e.layerX != "undefined")? e.layerX: e.offsetX;
	e.offY = (typeof e.layerY != "undefined")? e.layerY: e.offsetY;
	bar.startX = parseInt(bar.style.left); bar.startY = parseInt(bar.style.top);
	if (bar.axis == "v") {
	  bar.destX = bar.startX;
	  bar.destY = (e.offY < bar.startY)? e.offY: e.offY - bar.offsetHeight;
	  bar.destY = Math.min( Math.max(bar.destY, bar.minY), bar.maxY );
	} else {
	  bar.destX = (e.offX < bar.startX)? e.offX: e.offX - bar.offsetWidth;
	  bar.destX = Math.min( Math.max(bar.destX, bar.minX), bar.maxX );
	  bar.destY = bar.startY;
	}
	bar.distX = bar.destX - bar.startX; bar.distY = bar.destY - bar.startY;
	$K.yg_slidebar.per = Math.PI/(2 * $K.yg_slidebar.slideDur);
  	$K.yg_slidebar.slideStart = (new Date()).getTime();
	bar.on_slide_start(bar.startX, bar.startY);
  	$K.yg_slidebar.aniTimer = setInterval("$K.yg_slidebar.doSlide()",10);
  },

  doSlide: function() {
	if ( !$K.yg_slidebar.obj ) { clearInterval($K.yg_slidebar.aniTimer); return; }
	var bar = $K.yg_slidebar.obj;
	var elapsed = (new Date()).getTime() - this.slideStart;
	if (elapsed < this.slideDur) {
	  	var x = bar.startX + bar.distX * Math.sin(this.per*elapsed);
	  	var y = bar.startY + bar.distY * Math.sin(this.per*elapsed);
		bar.style.left = x + "px"; bar.style.top = y + "px";
		bar.on_slide(x, y);
	} else {	// if time's up
		clearInterval(this.aniTimer);
		bar.style.left = bar.destX + "px"; bar.style.top = bar.destY + "px";
		bar.on_slide_end(bar.destX, bar.destY);
		this.obj = null;
	}
  },

  startDrag: function (e) { // called onmousedown of bar

	// Add special class to body to prevent displaying of actionbuttons while dragging
	document.body.addClassName('drag');
	$K.yg_activeDragInfo.dragging = true;

	// Safari Marking fix
	if (e && e.preventDefault)
		e.preventDefault();
	e = $K.yg_event.DOMit(e);
	if ( $K.yg_slidebar.aniTimer ) clearInterval($K.yg_slidebar.aniTimer);
	var bar = $K.yg_slidebar.obj = this;
	bar.downX = e.clientX; bar.downY = e.clientY;
	bar.startX = parseInt(bar.style.left);
	bar.startY = parseInt(bar.style.top);
	bar.on_drag_start(bar.startX, bar.startY);
	$K.yg_event.add( document, "mousemove", $K.yg_slidebar.doDrag, true );
	$K.yg_event.add( document, "mouseup",   $K.yg_slidebar.endDrag,  true );
	e.stopPropagation();
	// dropdowns
	if (this.id) $K.yg_clickindropdown[this.id.substring(0,(this.id.length-10))]=true;
  },

  doDrag: function (e) {
	e = e? e: window.event;
	if (!$K.yg_slidebar.obj) return;
	var bar = $K.yg_slidebar.obj;
	var nx = bar.startX + e.clientX - bar.downX;
	var ny = bar.startY + e.clientY - bar.downY;
	nx = Math.min( Math.max( bar.minX, nx ), bar.maxX);
	ny = Math.min( Math.max( bar.minY, ny ), bar.maxY);
	bar.style.left = nx + "px"; bar.style.top  = ny + "px";
	if ( (bar.id.indexOf('dropdownbox') != -1) || (bar.id.indexOf('sortlist') != -1) ) {
		bar.style.top  = (ny-1) + "px";
	}
	bar.on_drag(nx,ny);
	return false;
  },

  endDrag: function () {
	$K.yg_event.remove( document, "mousemove", $K.yg_slidebar.doDrag, true );
	$K.yg_event.remove( document, "mouseup",   $K.yg_slidebar.endDrag,  true );
	if ( !$K.yg_slidebar.obj ) return; // avoid errors in ie if inappropriate selections
	$K.yg_slidebar.obj.on_drag_end( parseInt($K.yg_slidebar.obj.style.left), parseInt($K.yg_slidebar.obj.style.top) );
	$K.yg_slidebar.obj = null;
	// dropdowns
	if (this.id) $K.yg_clickindropdown[this.id.substring(0,(this.id.length-10))]=false;

	// Remove special class to body which prevented displaying of actionbuttons while dragging
	document.body.removeClassName('drag');
	$K.yg_activeDragInfo.dragging = false;
  }

}

// Size dragBar according to layer size?
$K.yg_scrollObj.prototype.bSizeDragBar = true;

/**
 * Used to set up the seperate horizontal and vertical scrollbars
 * @param { String } [id] The id of the scrolling layer.
 * @param { String } [trkId] The id of the track for the scrollbar.
 * @param { String } [axis] The axis of the scrollbar ("H" or "V").
 * @param { String } [offx] The offset from the x-axis.
 * @param { String } [offy] The offset from the y-axis.
 * @param { String } [boxid] The id of the surrounding box.
 * @function
 * @name $K.yg_scrollObj.prototype.setUpScrollbar
 */
$K.yg_scrollObj.prototype.setUpScrollbar = function(id, trkId, axis, offx, offy, boxid) {
  var bar = $(id);
  var trk = $(trkId);
  $K.yg_slidebar.init(bar, trk, axis, offx, offy);
  // connect $K.yg_slidebar with $K.yg_scrollObj
  bar.wn = $K.yg_scrollObjs[this.id]; // scroll area object this bar connected to
  if (axis == "v") this.vBarId = id; else this.hBarId = id;
  // also called on_load (i.e., when layer loaded), but in case h and v scrollbars, need to call here too
  //if (this.bSizeDragBar) this.setBarSize();
  bar.on_drag_start = bar.on_slide_start = $K.yg_scroll_getWndoLyrRef;
  bar.on_drag_end =   bar.on_slide_end =   $K.yg_scroll_tossWndoLyrRef;
  bar.on_drag =	   bar.on_slide =	   yg_scroll_UpdateWndoLyrPos;

  // select this area when clickin on the bars
  $(id).onclick = function () {
	 $K.yg_scrollObjAttr.activeArea = this;
  }.bind(boxid);

  $(trkId).onclick = function () {
	 $K.yg_scrollObjAttr.activeArea = this;
  }.bind(boxid);

  this._.$(this.id+'_scrollup').onclick = function () {
	 $K.yg_scrollObjAttr.activeArea = this;
  }.bind(boxid);

  this._.$(this.id+'_scrolldown').onclick = function () {
	 $K.yg_scrollObjAttr.activeArea = this;
  }.bind(boxid);

  this._.$(this.id+'_scrollleft').onclick = function () {
	 $K.yg_scrollObjAttr.activeArea = this;
  }.bind(boxid);

  this._.$(this.id+'_scrollright').onclick = function () {
	 $K.yg_scrollObjAttr.activeArea = this;
  }.bind(boxid);

}

/**
 * Get the reference to the layer visible in the scroll area
 * (assigned to bar.on_drag/slide...)
 * NOTE: For this function, "this" refers to bar
 * @function
 * @name $K.yg_scroll_getWndoLyrRef
 */
$K.yg_scroll_getWndoLyrRef = function()  { this.wnLyr = $(this.wn.lyrId); }


/**
 * Discard the reference to the layer visible in the scroll area
 * (assigned to bar.on_drag/slide...)
 * NOTE: For this function, "this" refers to bar
 * @function
 * @name $K.yg_scroll_tossWndoLyrRef
 */
$K.yg_scroll_tossWndoLyrRef = function() { this.wnLyr = null; }


/**
 * Used to keep the position of the scrolling layer in sync
 * with the slide/drag of the bar
 * NOTE: For this function, "this" refers to bar
 * @param { Int } [x] X coordinate
 * @param { Int } [y] Y coordinate
 * @function
 * @name yg_scroll_UpdateWndoLyrPos
 */
var yg_scroll_UpdateWndoLyrPos = function(x, y) {
  var nx, ny;
  if (this.axis == "v") {
	nx = this.wn.x; // floating point values for loaded layer's position held in shiftTo method
	ny = -(y - this.minY) * ( this.wn.maxY / (this.maxY - this.minY) ) || 0;
  } else {
	ny = this.wn.y;
	nx = -(x - this.minX) * ( this.wn.maxX / (this.maxX - this.minX) ) || 0;
  }
  this.wn.shiftTo(this.wnLyr, nx, ny);
}


/**
 * Used to keep the position of the dragbar in sync
 * with the position of the scrollable layer
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @param { Int } [x] X coordinate
 * @param { Int } [y] Y coordinate
 * @function
 * @name $K.yg_scrollObj.prototype.updateScrollbar
 */
$K.yg_scrollObj.prototype.updateScrollbar = function(x, y) {
  var nx, ny;
  if ( this.vBarId ) {
	if (!this.vbar) this.vbar = this.vBarId
	if (this.maxY) {
		ny = -( y * ( (this.vbar.maxY - this.vbar.minY) / this.maxY ) - this.vbar.minY );
		ny = Math.min( Math.max(ny, this.vbar.minY), this.vbar.maxY);
		nx = parseInt(this.vbar.style.left);
		this.vbar.style.left = nx + "px"; this.vbar.style.top = ny + "px";
		if ( (this.vbar.id.indexOf('dropdownbox') != -1) || (this.vbar.id.indexOf('sortlist') != -1) ) {
			this.vbar.style.top = (ny-1) + "px";
		}
	}
  }
	if ( this.hBarId ) {
	if (!this.hbar) this.hbar = this.hBarId
	if (this.maxX) {
		nx = -( x * ( (this.hbar.maxX - this.hbar.minX) / this.maxX ) - this.hbar.minX );
		nx = Math.min( Math.max(nx, this.hbar.minX), this.hbar.maxX);
		ny = parseInt(this.hbar.style.top);
		this.hbar.style.left = nx + "px"; this.hbar.style.top = ny + "px";
	}
  }
}


/**
 * Restores the dragbar to original start position when
 * loading a new layer.
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @function
 * @name $K.yg_scrollObj.prototype.restoreScrollbars
 */
$K.yg_scrollObj.prototype.restoreScrollbars = function() {
  var bar;
  if (this.vBarId) {
	bar = $(this.vBarId);
	bar.style.left = bar.minX + "px"; bar.style.top = bar.minY + "px";
  }
  if (this.hBarId) {
	bar = $(this.hBarId);
	bar.style.left = bar.minX + "px"; bar.style.top = bar.minY + "px";
  }
}


/**
 * Fixes the button position for Internet Explorer 7
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @function
 * @name $K.yg_scrollObj.prototype.fixBar
 */
$K.yg_scrollObj.prototype.fixBar = function() {

	if((this.vBarId)&&(Prototype.Browser.IE)) {
		// Fix for IE (Buttons verrutschen...)
		// Slider
		$(this.vBarId).style.position = 'relative';
		$(this.vBarId).style.position = 'absolute';

		// Button unten
		$(this.id+'_scrolldown').style.position = 'relative';
		$(this.id+'_scrolldown').style.position = 'absolute';
	}
}


/**
 * Sizes dragbar in proportion to the size of the content
 * inside the scrollable layer; is called on_load of layer
 * if the bSizeDragBar propierty is set to true
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @function
 * @name $K.yg_scrollObj.prototype.setBarSize
 */
$K.yg_scrollObj.prototype.setBarSize = function( firstrun ) {

	var bar;
	var lyr = $(this.lyrId);

	var wn = $(this.wnid);

	var addMaxY = 0;
	var addMaxX = 0;

	var hShown = false;
	var vShown = false;

	if (!wn) return;

	// Dirty safari 3 display hack
	if ((firstrun==true) && (Prototype.Browser.WebKit)) {
		lyrwidth=$(this.lyrId).rightWidth;
	} else {
		lyrwidth=$(this.lyrId).offsetWidth;
	}

	// If inner layer (lyr) is wider than current window (wn)
	if ( (wn.clientWidth - lyrwidth) < 0 ) {
		var offsetX = lyr.offsetLeft;						// No Prototype (!)

		var gapX = (wn.clientWidth - lyrwidth - offsetX);
		// If there's a gap between the right of window (wn) and the right of the layer (lyr)
		if ( gapX > 0 ) {
			// Clear it
			var lyrOffsetLeft = lyr.offsetLeft;
			lyr.style.left = (lyrOffsetLeft + gapX) +'px';

			// check if fixed column header is there and scroll if necessary
			if ($K.windows[this.boxid]) $K.windows[this.boxid].scrollh(lyrOffsetLeft + gapX);

			this.x = (lyrOffsetLeft + gapX);
			this.updateScrollbar(this.x, this.y);
		}
	}

	// If inner layer (lyr) is larger than current window (wn)
	if ( (wn.clientHeight - lyr.clientHeight) < 0 ) {
		var offsetY = lyr.offsetTop;						// No Prototype (!)

		var gapY = (wn.clientHeight - lyr.clientHeight - offsetY);
		// If there's a gap between the bottom of window (wn) and the bottom of the layer (lyr)
		if ( gapY > 0 ) {
			// Clear it
			var lyrOffsetTop = lyr.offsetTop;
			lyr.style.top = (lyrOffsetTop + gapY) +'px';
			this.y = (lyrOffsetTop + gapY);
			this.updateScrollbar(this.x, this.y);
		}
	}

	// Calculate maxX & maxY values for scrollbars
	this.maxY = ( lyr.offsetHeight - wn.offsetHeight > 0 ) ? (lyr.offsetHeight - wn.offsetHeight) : 0;
	this.maxX = ( lyrwidth - wn.offsetWidth > 0 ) ? (lyrwidth - wn.offsetWidth) : 0;

	twoscroll=false;

	// Do we have a horizontal scrollbar?
	if (this.hBarId) {

		// Save reference to scrollbar
		bar = $(this.hBarId);

		// Save track width (from outer container)
		bar.trkWd = wn.offsetWidth-($K.yg_scrollObjAttr.scrollBtnHWidth*2);

		bar.minX = bar.maxY = bar.minY = 1;

		if ( (!isNaN(lyrwidth / wn.offsetWidth)) && ((bar.trkWd / (lyrwidth / wn.offsetWidth)) != 0) && (bar.trkWd>0) ) {
			if (lyrwidth > wn.offsetWidth) {
				bar.style.width = ((bar.trkWd / (lyrwidth / wn.offsetWidth)) + 'px');
			} else {
				bar.style.width = (bar.trkWd - 2*bar.minX + 'px');
			}
		}

		if(Prototype.Browser.IE) {
			addMaxX = 0;
		}
		bar.maxX = bar.trkWd - bar.offsetWidth - bar.minX + addMaxX;

		if(this.vBarId) {
			bar.maxX -= 8;
		}

		// Fix Bars (IE)
		this.fixBar();

		if(!(lyrwidth > wn.offsetWidth)) {
			// No scrollbars necessary, we hide it

			if (this._.$(this.id+'_scrollbar_h')) this._.$(this.id+'_scrollbar_h').style.visibility = 'hidden';
			hShown = false;

			// Scroll to max left, if no more scrollbars are displayed
			bar.style.left = '0px';
			lyr.style.left = '0px';

			// check if fixed column header is there and scroll if necessary
			if ($K.windows[this.boxid] && (typeof($K.windows[this.boxid].scrollh) == 'function')) $K.windows[this.boxid].scrollh(0);

			this.x = 0;
			if (this.vBarId) {
				if (this._.$(this.id+'_scrollbar_v')) this._.$(this.id+'_scrollbar_v').style.bottom = '0px';
			}
		} else {
			// Scrollbar necessary, we show them
			twoscroll=true;

			this._.$(this.id+'_scrollbar_h').style.visibility = 'visible';
			hShown = true;

			// Do we have a vertical Bar?
			if (this.vBarId) {
				// Delete style of it..
				this._.$(this.id+'_scrollbar_v').style.bottom = '';
			}
		}
	}

	// Do we have a vertical scrollbar?
	if (this.vBarId) {

		// Save reference to scrollbar
		bar = $(this.vBarId);

		// Safari does not expand the track correctly and does not position the lower button on the right position -> this is the fix
		if(Prototype.Browser.WebKit) {
			this._.$(this.id+'_track_v').style.height = (wn.offsetHeight-$K.yg_scrollObjAttr.scrollBtnVHeight)+'px';
			// temporary disabled
			//$(this.id+'_scrolldown').style.top = (wn.offsetHeight-$K.yg_scrollObjAttr.scrollBtnVHeight)+'px';
		}

		// Save track height (from outer container)
		bar.trkHt = wn.offsetHeight-($K.yg_scrollObjAttr.scrollBtnVHeight*2);

		bar.maxX = bar.minX = bar.minY = 1;

		if (!isNaN(lyr.offsetHeight / wn.offsetHeight)) {
			if (lyr.offsetHeight > wn.offsetHeight) {
				if ((bar.trkHt / ( lyr.offsetHeight / wn.offsetHeight )) > 0) bar.style.height = (bar.trkHt / ( lyr.offsetHeight / wn.offsetHeight )) + 'px';
			} else {
				bar.style.height = (bar.trkHt - 2*bar.minY + 'px');
			}
		}

		if (Prototype.Browser.IE) {
			addMaxY = -4;
		} else {
			addMaxY = -5;
		}
		bar.maxY = bar.trkHt - bar.offsetHeight - bar.minY + addMaxY;

		if(!(lyr.offsetHeight > wn.offsetHeight)) {

			// No scrollbars necessary, we hide it
			if (this._.$(this.id+'_scrollbar_v')) this._.$(this.id+'_scrollbar_v').style.visibility = 'hidden';

			vShown = false;

			// Scroll to max top, if no more scrollbars are displayed
			bar.style.top = '0px';
			lyr.style.top = '0px';
			this.y = 0;
			if (this.hBarId) {
				if (this._.$(this.hBarId)) this._.$(this.hBarId).maxX += $K.yg_scrollObjAttr.scrollBtnHWidth;
				if (this._.$(this.id+'_scrollright')) this._.$(this.id+'_scrollright').style.right = '0px';
				if (this._.$(this.id+'_track_h')) this._.$(this.id+'_track_h').style.right = $K.yg_scrollObjAttr.scrollBtnHWidth+'px';
				if (this.blank!=0) this.blank.style.display = 'none';
			}
		} else {

			this.maxX += $K.yg_scrollObjAttr.scrollBtnVWidth;
			this._.$(this.id+'_scrollbar_v').style.visibility = 'visible';
			if (twoscroll) this.maxY+=$K.yg_scrollObjAttr.scrollBtnHHeight;
			vShown = true;

			if (this.hBarId) {
				this._.$(this.id+'_scrollright').style.right = '';
				this._.$(this.id+'_track_h').style.right = '';

				if (twoscroll==true) {
					if (this.blank!=0) this.blank.style.display = 'block';
					if(Prototype.Browser.WebKit) {
						bar.maxY += -1;
					}
				} else {
					if (this.blank!=0) this.blank.style.display = 'none';
					bar.maxY += $K.yg_scrollObjAttr.scrollBtnVHeight-3;
					if (bar.id.indexOf('dropdownbox') != -1) {
						if (Prototype.Browser.Gecko || Prototype.Browser.IE)
							bar.maxY -= 6;
						else
							bar.maxY -= 5;
					}
					if (bar.id.indexOf('sortlist') != -1) {
						if (Prototype.Browser.Gecko || Prototype.Browser.IE)
							bar.maxY -= 3;
						else
							bar.maxY -= 2;
					}
					if(Prototype.Browser.WebKit) {
						bar.maxY += -1;
					} else if (Prototype.Browser.IE) {
						bar.maxY += -1;
					}
				}

			}
		}
	}

	// Check here if one, two or both scrollbars are shown (and do stuff for sev. Browsers)
	if(hShown && !vShown) {
		$(this.hBarId).maxX += -$K.yg_scrollObjAttr.scrollBtnVWidth+5;
	}
}


/**
 * Called from the load method; restores the position and
 * sets the size of the scrollbar.
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @function
 * @name $K.yg_scrollObj.prototype.on_load
 */
$K.yg_scrollObj.prototype.on_load = function() {
	this.restoreScrollbars();
}


/**
 * Called when the on_scroll or on_slide event is fired;
 * updates the position of the dragbars.
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @param { Int } [x] X coordinate.
 * @param { Int } [y] Y coordinate.
 * @function
 * @name $K.yg_scrollObj.prototype.on_scroll
 */
$K.yg_scrollObj.prototype.on_scroll = $K.yg_scrollObj.prototype.on_slide = function(x,y) { this.updateScrollbar(x,y); }


/**
 * Called when the on_scroll_start or on_slide_start event is fired;
 * obtains the references to the relevant dragbar.
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @function
 * @name $K.yg_scrollObj.prototype.on_scroll_start
 */
$K.yg_scrollObj.prototype.on_scroll_start = $K.yg_scrollObj.prototype.on_slide_start = function() {
	if ( this.vBarId ) this.vbar = $(this.vBarId);
	if ( this.hBarId ) this.hbar = $(this.hBarId);
}


/**
 * Called when the on_scroll_start or on_slide_start event is fired;
 * discards the references to the relevant dragbar.
 * NOTE: This is the actual prototype mapped to the element itself.
 * Only used internally.
 * @param { Int } [x] X coordinates of the scrollable layer
 * @param { Int } [y] Y coordinates of the scrollable layer
 * @function
 * @name $K.yg_scrollObj.prototype.on_scroll_end
 */
$K.yg_scrollObj.prototype.on_scroll_end = $K.yg_scrollObj.prototype.on_slide_end = function(x, y) {
	this.updateScrollbar(x,y);
	this.lyr = null; this.bar = null;
}


/**
 * Renders the scrollbars into a page
 * @param { Element } [obj] The element which should get scrollbars.
 * @param { String } [id] The prefix of all ids in the newly created
 * scrollbars.
 * @function
 * @name $K.yg_renderScroll
 */
$K.yg_renderScroll = function( obj, id, iframe ) {
	var obj_ref;
	var scrollcnt = '';
	var parentDIV = obj.parentNode;
	var vScrollDIV = $(document.createElement('div'));
	vScrollDIV.id = id + '_scrollbar_v';
	vScrollDIV.className = 'scrollbar_v mceNonEditable';
	vScrollDIV.setStyle('display', 'none');
	vScrollDIV.onselectstart = function(){return false;};
	vScrollDIV.onmousedown = function(){return false;};
	vScrollDIV.onmouseup = function(){return false;};
	vScrollDIV.onclick = function(){return false;};
	vScrollDIV.contentEditable = false;
	obj_ref = '';

	scrollcnt+="<div id=\""+id+"_scrollup\" class=\"scroll_up mceNonEditable\">";
	scrollcnt+="  <a onclick=\"Event.stop(event);\" onfocus=\"this.blur();\" onmousedown=\""+obj_ref+"$K.yg_scroll_initScroll('"+id+"','up');\" onmouseup=\""+obj_ref+"$K.yg_scroll_stopScroll('"+id+"');\" onmouseover=\""+obj_ref+"$K.yg_scroll_hoverScroll('"+id+"','up');\" onmouseout=\""+obj_ref+"$K.yg_scroll_stopHoverScroll('"+id+"');\"><img src=\""+$K.imgdir+"window/x.gif\" width=\"9\" height=\"13\" alt=\"\" border=\"0\" /></a>";
	scrollcnt+="</div>";
	scrollcnt+="<div id=\""+id+"_track_v\" class=\"track_v mceNonEditable\" onselectstart=\"return false;\" onclick=\"Event.stop(event);\" onmousedown=\"Event.stop(event);\">";
	scrollcnt+="  <div id=\""+id+"_dragbar_v\" class=\"dragbar_v mceNonEditable\"></div>";
	scrollcnt+="</div>";
	scrollcnt+="<div id=\""+id+"_scrolldown\" class=\"scroll_down mceNonEditable\" onselectstart=\"return false;\" onmousedown=\"return false;\" onmouseup=\"return false;\">";
	scrollcnt+="  <a onclick=\"Event.stop(event);\" onfocus=\"this.blur();\" onmousedown=\""+obj_ref+"$K.yg_scroll_initScroll('"+id+"','down')\" onmouseup=\""+obj_ref+"$K.yg_scroll_stopScroll('"+id+"');\" onmouseover=\""+obj_ref+"$K.yg_scroll_hoverScroll('"+id+"','down');\" onmouseout=\""+obj_ref+"$K.yg_scroll_stopHoverScroll('"+id+"');\"><img src=\""+$K.imgdir+"window/x.gif\" width=\"9\" height=\"13\" alt=\"\" border=\"0\" /></a>";
	scrollcnt+="</div>";

	vScrollDIV.innerHTML = scrollcnt;
	parentDIV.replaceChild(vScrollDIV, obj);

	var scrollcnt = '';
	var hScrollDIV = $(document.createElement('div'));
	hScrollDIV.id = id + '_scrollbar_h';
	hScrollDIV.className = 'scrollbar_h mceNonEditable';
	vScrollDIV.setStyle('display', 'none');
	hScrollDIV.onselectstart = function(){return false;};
	hScrollDIV.onmousedown = function(){return false;};
	hScrollDIV.onmouseup = function(){return false;};
	hScrollDIV.contentEditable = false;

	scrollcnt+="<div id=\""+id+"_scrollleft\" class=\"scroll_left mceNonEditable\">";
	scrollcnt+="  <a onfocus=\"this.blur();\" onclick=\"return false\" onmousedown=\""+obj_ref+"$K.yg_scroll_initScroll('"+id+"','left')\" onmouseup=\""+obj_ref+"$K.yg_scroll_stopScroll('"+id+"');\" onmouseover=\""+obj_ref+"$K.yg_scroll_hoverScroll('"+id+"','left');\" onmouseout=\""+obj_ref+"$K.yg_scroll_stopHoverScroll('"+id+"');\"><img src=\""+$K.imgdir+"window/x.gif\" width=\"13\" height=\"9\" alt=\"\" border=\"0\" /></a>";
	scrollcnt+="</div>";
	scrollcnt+="<div id=\""+id+"_track_h\" class=\"track_h mceNonEditable\" onselectstart=\"return false;\" onmousedown=\"return false;\">";
	scrollcnt+="  <div id=\""+id+"_dragbar_h\" class=\"dragbar_h mceNonEditable\"></div>";
	scrollcnt+="</div>";
	scrollcnt+="<div id=\""+id+"_scrollright\" class=\"scroll_right mceNonEditable\" onselectstart=\"return false;\" onmousedown=\"return false;\">";
	scrollcnt+="  <a onclick=\"return false;\" onfocus=\"this.blur();\" onmousedown=\""+obj_ref+"$K.yg_scroll_initScroll('"+id+"','right')\" onmouseup=\""+obj_ref+"$K.yg_scroll_stopScroll('"+id+"');\" onmouseover=\""+obj_ref+"$K.yg_scroll_hoverScroll('"+id+"','right');\" onmouseout=\""+obj_ref+"$K.yg_scroll_stopHoverScroll('"+id+"');\"><img src=\""+$K.imgdir+"window/x.gif\" width=\"13\" height=\"9\" alt=\"\" border=\"0\" /></a>";
	scrollcnt+="</div>";

	hScrollDIV.innerHTML = scrollcnt;
	parentDIV.appendChild(hScrollDIV);
}


/**
 * Initializes a set of scrollbars.
 * @param { Element } [box] The element in which the scrollbars are located.
 * @param { Element } [outer] The outer element of the scrollable area (the "mask").
 * @param { Element } [inner] The inner element of the scrollable area (the scrollable layer).
 * @param { Element } [dragbarv] The id of the vertical dragbar.
 * @param { Element } [trackv] The id of the vertical track.
 * @param { Element } [dragbarh] The id of the horizontal dragbar.
 * @param { Element } [trackh] The id of the horizontal track.
 * @param { Element } [blank] The id of the blank corner in the bottom right (between
 * the scrollbars).
 * @param { String } [frameReference] (optional) Reference to the IFrame (if scrollbars are in iframe)
 * @function
 * @name $K.yg_initScrollbars
 */
$K.yg_initScrollbars = function (box, outer, inner, dragbarv, trackv, dragbarh, trackh, blank, frameReference) {
	sobj = new $K.yg_scrollObj(box.id, outer, inner, inner, blank, frameReference);
	sobj.setUpScrollbar(dragbarv, trackv, "v", 1, 1, box.id);
	sobj.setUpScrollbar(dragbarh, trackh, "h", 1, 1, box.id);

	$K.yg_scrollObjAttr.speed = 200;
	sobj.setBarSize();
	return sobj;
}


/**
 * This is high-level function for the handling of the scrollwheel event.
 * It must react to delta being more/less than zero.
 * Only used internally.
 * @param { Int } [delta] The delta value received from the mousewheel.
 * @param { Int } [axis] 1 = vertical, 2 = horizontal
 * @function
 * @name $K.yg_mouseWheelHandle
 */
$K.yg_mouseWheelHandle = function(delta, axis) {
	if ((delta < 0) && ($K.yg_scrollObjAttr.activeArea != null)) {
		if (axis == 1) {
			$K.yg_scroll_scrollBy($K.yg_scrollObjAttr.activeArea, -30, 0, 1);
		} else {
			$K.yg_scroll_scrollBy($K.yg_scrollObjAttr.activeArea, 0, -30, 1);
		}
	} else if ($K.yg_scrollObjAttr.activeArea != null) {
		if (axis == 1) {
			$K.yg_scroll_scrollBy($K.yg_scrollObjAttr.activeArea, +30, 0, 1);
		} else {
			$K.yg_scroll_scrollBy($K.yg_scrollObjAttr.activeArea, 0, +30, 1);
		}
	}
}


/**
 * Event handler for mouse wheel event.
 * Only used internally.
 * @param { Event } [event] The event which was fired.
 * @function
 * @name $K.yg_wheel
 */
$K.yg_wheel = function(event) {
	var delta = 0;

	if (!event) /* For IE. */
		event = window.event;
	if (event.wheelDelta) { /* IE/Opera. */
		delta = event.wheelDelta/120;
		/** In Opera 9, delta differs in sign as compared to IE.
		*/
		if (window.opera) delta = -delta;
	} else if (event.detail) { /** Mozilla case. */
		/** In Mozilla, sign of delta is different than in IE.
		* Also, delta is multiple of 3.
		*/
		delta = -event.detail/3;
	}
	/** If delta is nonzero, handle it.
	* Basically, delta is now positive if wheel was scrolled up,
	* and negative, if wheel was scrolled down.
	*/

	if (delta) $K.yg_mouseWheelHandle(delta, event.axis);
	/** Prevent default actions caused by mouse wheel.
	* That might be ugly, but we handle scrolls somehow
	* anyway, so don't bother here..
	*/

	if (event.preventDefault) event.preventDefault();
	event.returnValue = false;
}

// Initialization code
if (window.addEventListener) {
	/** DOMMouseScroll is for mozilla. */
	window.addEventListener('DOMMouseScroll', $K.yg_wheel, false);
}
// IE/Opera
window.onmousewheel = document.onmousewheel = $K.yg_wheel;
