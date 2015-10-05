// script.aculo.us dragdrop.js v1.9.0, Thu Dec 23 16:54:48 -0500 2010

// Copyright (c) 2005-2010 Thomas Fuchs (http://script.aculo.us, http://mir.aculo.us)
//
// script.aculo.us is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/

if(Object.isUndefined(Effect))
  throw("dragdrop.js requires including script.aculo.us' effects.js library");

var Droppables = {
  drops: [],

  remove: function(element) {
    this.drops = this.drops.reject(function(d) { return d.element==$(element) });
  },

  add: function(element) {
    element = $(element);
    var options = Object.extend({
      greedy:     true,
      hoverclass: null,
      tree:       false
    }, arguments[1] || { });

    // cache containers
    if(options.containment) {
      options._containers = [];
      var containment = options.containment;
      if(Object.isArray(containment)) {
        containment.each( function(c) { options._containers.push($(c)) });
      } else {
        options._containers.push($(containment));
      }
    }

    if(options.accept) options.accept = [options.accept].flatten();

	if ((element.tagName != 'TR') && (element.tagName != 'TBODY'))
    Element.makePositioned(element); // fix IE
    options.element = element;

    this.drops.push(options);
  },

  findDeepestChild: function(drops) {
    deepest = drops[0];

    for (i = 1; i < drops.length; ++i)
      if (Element.isParent(drops[i].element, deepest.element))
        deepest = drops[i];

    return deepest;
  },

  isContained: function(element, drop) {
    var containmentNode;
    if(drop.tree) {
      containmentNode = element.treeNode;
    } else {
      containmentNode = element.parentNode;
    }
    // return $K.yg_sortable_elements.detect(function(c) { return containmentNode == c });
	// always returning true
  	return true;
  },

  isAffected: function(point, element, drop) {
     return ((
      (drop.element!=element) ||
        this.isContained(element, drop)) &&
      ((!drop.accept) ||
        (Element.classNames(element).detect(
          function(v) { return drop.accept.include(v) } ) )) &&
      Position.within(drop.element, point[0], point[1]) );
  },

  deactivate: function(drop) {
    if(drop.hoverclass)
      Element.removeClassName(drop.element, drop.hoverclass);
    this.last_active = null;
  },

  activate: function(drop) {
    if(drop.hoverclass)
      Element.addClassName(drop.element, drop.hoverclass);
    this.last_active = drop;
  },

  show: function(point, element) {
    if(!this.drops.length) return;
    var drop, affected = [];

    this.drops.each( function(drop) {
      if(Droppables.isAffected(point, element, drop)) {
        if (drop.element.id==($K.yg_currentHover)) {
            affected.push(drop);
        } else if (drop.element.parentNode.id==($K.yg_currentHover)) {
        affected.push(drop);
        }
      }
    });

    if(affected.length>0)
      drop = Droppables.findDeepestChild(affected);

    if(this.last_active && this.last_active != drop) this.deactivate(this.last_active);
    if (drop) {
      Position.within(drop.element, point[0], point[1]);
      if(drop.onHover)
        drop.onHover(element, drop.element, Position.overlap(drop.overlap, drop.element));

      if (drop != this.last_active) Droppables.activate(drop);
    }
  },

  fire: function(event, element) {
    if(!this.last_active) return;
    Position.prepare();

    if (this.isAffected([Event.pointerX(event), Event.pointerY(event)], element, this.last_active))
      if (this.last_active.onDrop) {
        this.last_active.onDrop(element, this.last_active.element, event);
        return true;
      }
  },

  reset: function() {
    if(this.last_active)
      this.deactivate(this.last_active);
  }
};

var Draggables = {
  drags: [],
  observers: [],

  register: function(draggable) {
    if(this.drags.length == 0) {
      this.eventMouseUp   = this.endDrag.bindAsEventListener(this);
      this.eventMouseMove = this.updateDrag.bindAsEventListener(this);
      this.eventKeypress  = this.keyPress.bindAsEventListener(this);

      Event.observe(document, "mouseup", this.eventMouseUp);
      Event.observe(document, "mousemove", this.eventMouseMove);
      Event.observe(document, "keypress", this.eventKeypress);
    }
    this.drags.push(draggable);
  },

  unregister: function(draggable) {
    this.drags = this.drags.reject(function(d) { return d==draggable });
    if(this.drags.length == 0) {
      Event.stopObserving(document, "mouseup", this.eventMouseUp);
      Event.stopObserving(document, "mousemove", this.eventMouseMove);
      Event.stopObserving(document, "keypress", this.eventKeypress);
    }
  },

  activate: function(draggable) {
    if(draggable.options.delay) {
      this._timeout = setTimeout(function() {
        Draggables._timeout = null;
        window.focus();
        Draggables.activeDraggable = draggable;
      }.bind(this), draggable.options.delay);
    } else {
      window.focus(); // allows keypress events if window isn't currently focused, fails for Safari
      this.activeDraggable = draggable;
    }
  },

  deactivate: function() {
    this.activeDraggable = null;
  },

  updateDrag: function(event) {
    if(!this.activeDraggable) return;
    var pointer = [Event.pointerX(event), Event.pointerY(event)];
    // Mozilla-based browsers fire successive mousemove events with
    // the same coordinates, prevent needless redrawing (moz bug?)
    if(this._lastPointer && (this._lastPointer.inspect() == pointer.inspect())) return;
    this._lastPointer = pointer;

    this.activeDraggable.updateDrag(event, pointer);
  },

  endDrag: function(event) {
    if(this._timeout) {
      clearTimeout(this._timeout);
      this._timeout = null;
    }
    if(!this.activeDraggable) return;
    this._lastPointer = null;
    this.activeDraggable.endDrag(event);
    this.activeDraggable = null;
  },

  keyPress: function(event) {
    if(this.activeDraggable)
      this.activeDraggable.keyPress(event);
  },

  addObserver: function(observer) {
    this.observers.push(observer);
    this._cacheObserverCallbacks();
  },

  removeObserver: function(element) {  // element instead of observer fixes mem leaks
    this.observers = this.observers.reject( function(o) { return o.element==element });
    this._cacheObserverCallbacks();
  },

  notify: function(eventName, draggable, event) {  // 'onStart', 'onEnd', 'onDrag'
    if(this[eventName+'Count'] > 0)
      this.observers.each( function(o) {
        if(o[eventName]) o[eventName](eventName, draggable, event);
      });
    if(draggable.options[eventName]) draggable.options[eventName](draggable, event);
  },

  _cacheObserverCallbacks: function() {
    ['onStart','onEnd','onDrag'].each( function(eventName) {
      Draggables[eventName+'Count'] = Draggables.observers.select(
        function(o) { return o[eventName]; }
      ).length;
    });
  }
};

/*--------------------------------------------------------------------------*/

var Draggable = Class.create({
  initialize: function(element) {
    var defaults = {
      handle: false,
      reverteffect: function(element, top_offset, left_offset) {
        var dur = Math.sqrt(Math.abs(top_offset^2)+Math.abs(left_offset^2))*0.02;
        new Effect.Move(element, { x: -left_offset, y: -top_offset, duration: dur,
          queue: {scope:'_draggable', position:'end'}
        });
      },
      endeffect: function(element) {
        var toOpacity = Object.isNumber(element._opacity) ? element._opacity : 1.0;
        // patch / fixed manually
        new Effect.Opacity(element, {duration:0.0, from:1.0, to:toOpacity,
          queue: {scope:'_draggable', position:'end'},
          afterFinish: function(){
            Draggable._dragging[element] = false
          }
        });
      },
      zindex: 2,
      revert: false,
      quiet: false,
      scroll: false,
      scrollSensitivity: 20,
      scrollSpeed: 15,
      snap: false,  // false, or xy or [x,y] or function(x,y){ return [x,y] }
      delay: 0
    };

    if(!arguments[1] || Object.isUndefined(arguments[1].endeffect))
      Object.extend(defaults, {
        starteffect: function(element) {
          element._opacity = Element.getOpacity(element);
          Draggable._dragging[element] = true;
        }
      });

    var options = Object.extend(defaults, arguments[1] || { });

    this.element = $(element);

    if(options.handle && Object.isString(options.handle))
      this.handle = this.element.down('.'+options.handle, 0);

    if(!this.handle) this.handle = $(options.handle);
    if(!this.handle) this.handle = this.element;

    if(options.scroll && !options.scroll.scrollTo && !options.scroll.outerHTML) {
      options.scroll = $(options.scroll);
      this._isScrollChild = Element.childOf(this.element, options.scroll);
    }

	if (element.tagName != 'TR')
    Element.makePositioned(this.element); // fix IE

    this.options  = options;
    this.dragging = false;

    this.eventMouseDown = this.initDrag.bindAsEventListener(this);
    Event.observe(this.handle, "mousedown", this.eventMouseDown);

    Draggables.register(this);
  },

  destroy: function() {
    Event.stopObserving(this.handle, "mousedown", this.eventMouseDown);
    Draggables.unregister(this);
  },

  currentDelta: function() {
    return([
      parseInt(Element.getStyle(this.element,'left') || '0'),
      parseInt(Element.getStyle(this.element,'top') || '0')]);
  },

  initDrag: function(event) {
    if(!Object.isUndefined(Draggable._dragging[this.element]) &&
      Draggable._dragging[this.element]) return;
    if(Event.isLeftClick(event)) {
      // abort on form elements, fixes a Firefox issue
      var src = Event.element(event);
      if((tag_name = src.tagName.toUpperCase()) && (
        tag_name=='INPUT' ||
        tag_name=='SELECT' ||
        tag_name=='OPTION' ||
        tag_name=='BUTTON' ||
        tag_name=='TEXTAREA')) return;

      var pointer = [Event.pointerX(event), Event.pointerY(event)];
      var pos     = this.element.cumulativeOffset();
      this.offset = [0,1].map( function(i) { return (pointer[i] - pos[i]) });
 	  this.startpointer = pointer;

      Draggables.activate(this);
      Event.stop(event);
    }
  },

  startDrag: function(event) {

	if (this.element.hasClassName('mk_nodrag')) return;

	$K.yg_startDragging(this, this.options.ghosting);

	$K.log("IN Startdrag", $K.Log.DEBUG);

	this.dragging = true;

    if(!this.delta) this.delta = this.currentDelta();

    if(this.options.zindex) {
      this.originalZ = parseInt(Element.getStyle(this.element,'z-index') || 0);
      this.element.style.zIndex = this.options.zindex;
    }

	this.element._originalParent = this.element.parentNode;

    if(this.options.scroll) {
      if (this.options.scroll == window) {
        var where = this._getWindowScroll(this.options.scroll);
        this.originalScrollLeft = where.left;
        this.originalScrollTop = where.top;
      } else {
        this.originalScrollLeft = this.options.scroll.scrollLeft;
        this.originalScrollTop = this.options.scroll.scrollTop;
      }
    }

    Draggables.notify('onStart', this, event);

    if(this.options.starteffect) this.options.starteffect(this.element);
  },

  updateDrag: function(event, pointer) {

    var pointer = [Event.pointerX(event), Event.pointerY(event)];

   	if (this.startpointer && ((pointer[0] == this.startpointer[0]) || (pointer[1] == this.startpointer[1]))) {
   		return;
   	} else {
   		this.startpointer = false;
   	}
 	$K.log("IN Updatedrag", $K.Log.DEBUG);
 	$K.log(this.dragging, $K.Log.DEBUG);

    if(!this.dragging) this.startDrag(event);

    if(!this.options.quiet){
      Position.prepare();
      Droppables.show(pointer, this.element);
    }

    Draggables.notify('onDrag', this, event);

    this.draw(pointer);
    if(this.options.change) this.options.change(this);

    if(this.options.scroll) {
      this.stopScrolling();

      var p;
      if (this.options.scroll == window) {
        with(this._getWindowScroll(this.options.scroll)) { p = [ left, top, left+width, top+height ]; }
      } else {
        p = Position.page(this.options.scroll).toArray();
        p[0] += this.options.scroll.scrollLeft + Position.deltaX;
        p[1] += this.options.scroll.scrollTop + Position.deltaY;
        p.push(p[0]+this.options.scroll.offsetWidth);
        p.push(p[1]+this.options.scroll.offsetHeight);
      }
      var speed = [0,0];
      if(pointer[0] < (p[0]+this.options.scrollSensitivity)) speed[0] = pointer[0]-(p[0]+this.options.scrollSensitivity);
      if(pointer[1] < (p[1]+this.options.scrollSensitivity)) speed[1] = pointer[1]-(p[1]+this.options.scrollSensitivity);
      if(pointer[0] > (p[2]-this.options.scrollSensitivity)) speed[0] = pointer[0]-(p[2]-this.options.scrollSensitivity);
      if(pointer[1] > (p[3]-this.options.scrollSensitivity)) speed[1] = pointer[1]-(p[3]-this.options.scrollSensitivity);
      this.startScrolling(speed);
    }

    // fix AppleWebKit rendering
    if(Prototype.Browser.WebKit) window.scrollBy(0,0);

    Event.stop(event);
  },

  finishDrag: function(event, success) {

	if ($('$treeNeedle')) $('$treeNeedle').setStyle({display: 'none'});
    document.body.removeClassName('drag');
    this.dragging = false;
    $K.yg_activeDragInfo.dragging = false;

	if ((!$K.yg_activeDragInfo.dropAllowed) && ((!Sortable._marker) || (!Sortable._marker.hoverOverMarker))) {
   	  success = false;
	}

    if(this.options.quiet){
      Position.prepare();
      var pointer = [Event.pointerX(event), Event.pointerY(event)];
      Droppables.show(pointer, this.element);
    }

    if(this.options.ghosting) {
      if (success) {
        // Für den "selected" style
		var tempvar = this.element.down('.listitempage');
		if (tempvar!=undefined) {
			if ( typeof(tempvar.onclick) == 'function' ) {
				tempvar.onclick();
			}
		}
      } else {
        Draggables.notify('onEnd', this, event);
        if(this.options.zIndex) this.element.style.zIndex = this.originalZ;
        Draggables.deactivate(this);
        Droppables.reset();
	  }
    }

	if (this.element.tagName != 'TR') this.element.style.display = 'block';
    var dropped = false;

	// Is there a custom ghost?
	$('yg_ddGhost').setStyle({display: 'none'});

    if (this.options.yg_clone) {

        // Clone the element
		var clone = new Element(this.element.tagName);

		$K.log( 'this.element: ', this.element, $K.Log.DEBUG );

		$A(this.element.attributes).each(function(attribute) {
			if ( (attribute.name != 'style') && (attribute.name != 'id') ) {
				// For DOM
				clone[attribute.name] = attribute.value;
				// For HTML
				clone.setAttribute(attribute.name, attribute.value);
			}
		});

		clone.id = 'clone%%'+this.element.id;

		clone.style.display = 'none';
		var cl_innerHTML = this.element.innerHTML + '';

		clone = $(clone);

		// Remove 'dummy' suffix(es) from id
		cl_innerHTML = cl_innerHTML.replace(/_dummy/g, '');

		if (Prototype.Browser.IE) {
			cl_innerHTML = cl_innerHTML.replace(/ id=/g, ' id=clone%%');
		} else {
			cl_innerHTML = cl_innerHTML.replace(/ id="/g, ' id="clone%%');
		}

		clone = Element.update( clone, cl_innerHTML );

		$K.yg_activeDragInfo.element = clone;
		$K.yg_activeDragInfo.origElement = this.element;
	}

    if ($K.yg_activeDragInfo.reordering && success) {
      // if reordering
      dropped = Droppables.fire(event, this.element);

      if ($K.yg_activeDragInfo.target != undefined) {

		if ($K.yg_currentdragobj.length > 0) {
			$K.yg_currentdragobj.each(function(item) {
				var listItem = item.up('li');
				$K.log( 'Item is:', item, $K.Log.INFO );
				if ($K.yg_activeDragInfo.position=='before') {
		      		$K.yg_activeDragInfo.target.parentNode.insertBefore(listItem, $K.yg_activeDragInfo.before);
		      	} else if ($K.yg_activeDragInfo.position=='into') {
		      		$K.yg_activeDragInfo.target.insertBefore(listItem, $K.yg_activeDragInfo.before);
		      	} else {
		      		if ($K.yg_activeDragInfo.before != listItem) {
		      			$K.yg_activeDragInfo.target.parentNode.insertBefore(listItem, $K.yg_activeDragInfo.before);
		      		}
				}
			});
		}

		if ($K.yg_activeDragInfo.element) $K.yg_activeDragInfo.element.disabled = '';

      }
    }

    if(dropped && this.options.onDropped) this.options.onDropped(this.element);

    if(this.options.zindex)
      this.element.style.zIndex = this.originalZ;

    Draggables.notify('onEnd', this, event);

    var revert = this.options.revert;
    if(revert && Object.isFunction(revert)) revert = revert(this.element);

    var d = this.currentDelta();
    if(revert && this.options.reverteffect) {
      if (dropped == 0 || revert != 'failure')
        this.options.reverteffect(this.element,
          d[1]-this.delta[1], d[0]-this.delta[0]);
    } else {
      this.delta = d;
    }

    if(this.options.endeffect)
      this.options.endeffect(this.element);

    Draggables.deactivate(this);
    Droppables.reset();

    if (this.options.yg_clone && $K.yg_activeDragInfo) {
		if (typeof Sortable.sortables[this.element.up().id].onUpdate == 'function') {
			Sortable.sortables[this.element.up().id].onUpdate( this.element.up() );
		}
	}

  },

  keyPress: function(event) {
    if(event.keyCode!=Event.KEY_ESC) return;
    this.finishDrag(event, false);
    Event.stop(event);
  },

  endDrag: function(event) {

	$K.log( 'EndDrag', $K.Log.DEBUG );

    if(!this.dragging) return;
    this.stopScrolling();
	this.finishDrag(event, $K.yg_activeDragInfo.dropAllowed);
    this.dragging = false;
    $K.yg_clearDragSession();
    Event.stop(event);
  },

  draw: function(point) {

    // Check object types
    var target_tree = Sortable.sortables[$K.yg_currentHover];

    // Special case for filelists
    if (!target_tree && $K.yg_currentHover && $K.yg_currentHover.id && $K.yg_currentHover.id.endsWith('_list')) {
    	target_tree = Sortable.sortables[$K.yg_currentHover.id];
    }

	if ($('yg_ddGhost')) $('yg_ddGhost')._originalParent = this.element._originalParent;

    if ($('yg_ddGhost')) {
      if( $K.yg_activeDragInfo.dropAllowed ) {
        // NODROP
        $K.yg_showNoDropMarker(false);
		$K.log('Painting a tag...', $K.Log.DEBUG);
      } else {
        if (Sortable._marker) {
          if (!Sortable._marker.hoverOverMarker) {
            Sortable._marker.hide();
          }
          if( $('placeHolder') ) {
            if ( !$('placeHolder').hoverOverMarker ) {
              $('placeHolder').remove();
            }
          }
        }
        // NODROP
        $K.yg_showNoDropMarker(true);
      }
    }

    var pos = this.element.cumulativeOffset();
    if(this.options.ghosting) {
      var r   = Position.realOffset(this.element);
      pos[0] += r[0] - Position.deltaX; pos[1] += r[1] - Position.deltaY;
    }

    var d = this.currentDelta();
    pos[0] -= d[0]; pos[1] -= d[1];

    if(this.options.scroll && (this.options.scroll != window && this._isScrollChild)) {
      pos[0] -= this.options.scroll.scrollLeft-this.originalScrollLeft;
      pos[1] -= this.options.scroll.scrollTop-this.originalScrollTop;
    }

    var p = [0,1].map(function(i){
      return (point[i]-pos[i]-this.offset[i])
    }.bind(this));

    if(this.options.snap) {
      if(Object.isFunction(this.options.snap)) {
        p = this.options.snap(p[0],p[1],this);
      } else {
      if(Object.isArray(this.options.snap)) {
        p = p.map( function(v, i) {
          return (v/this.options.snap[i]).round()*this.options.snap[i] }.bind(this));
      } else {
        p = p.map( function(v) {
          return (v/this.options.snap).round()*this.options.snap }.bind(this));
      }
    }}


    // No-Clone
    // Allow real moving of an object if one of the following classes match
    var drawAllowed = (this.element.readAttribute('yg_draggable') == "true");
    if (drawAllowed) {
    var style = this.element.style;
    if((!this.options.constraint) || (this.options.constraint=='horizontal'))
      style.left = p[0] + "px";
    if((!this.options.constraint) || (this.options.constraint=='vertical'))
      style.top  = p[1] + "px";
    if(style.visibility=="hidden") style.visibility = ""; // fix gecko rendering
    }


    // Is there a custom ghost?
    if ($('yg_ddGhost')) {
      var yg_ddGhost = $('yg_ddGhost');

      if((!this.options.constraint) || (this.options.constraint=='horizontal'))
        yg_ddGhost.setStyle({left:(point[0]+18)+'px'});
      if((!this.options.constraint) || (this.options.constraint=='vertical'))
        yg_ddGhost.setStyle({top:(point[1]+2)+'px'});
    }

  },

  stopScrolling: function() {
    if(this.scrollInterval) {
      clearInterval(this.scrollInterval);
      this.scrollInterval = null;
      Draggables._lastScrollPointer = null;
    }
  },

  startScrolling: function(speed) {
    if(!(speed[0] || speed[1])) return;
    this.scrollSpeed = [speed[0]*this.options.scrollSpeed,speed[1]*this.options.scrollSpeed];
    this.lastScrolled = new Date();
    this.scrollInterval = setInterval(this.scroll.bind(this), 10);
  },

  scroll: function() {
    var current = new Date();
    var delta = current - this.lastScrolled;
    this.lastScrolled = current;
    if(this.options.scroll == window) {
      with (this._getWindowScroll(this.options.scroll)) {
        if (this.scrollSpeed[0] || this.scrollSpeed[1]) {
          var d = delta / 1000;
          this.options.scroll.scrollTo( left + d*this.scrollSpeed[0], top + d*this.scrollSpeed[1] );
        }
      }
    } else {
      this.options.scroll.scrollLeft += this.scrollSpeed[0] * delta / 1000;
      this.options.scroll.scrollTop  += this.scrollSpeed[1] * delta / 1000;
    }

    Position.prepare();
    Droppables.show(Draggables._lastPointer, this.element);
    Draggables.notify('onDrag', this);
    if (this._isScrollChild) {
      Draggables._lastScrollPointer = Draggables._lastScrollPointer || $A(Draggables._lastPointer);
      Draggables._lastScrollPointer[0] += this.scrollSpeed[0] * delta / 1000;
      Draggables._lastScrollPointer[1] += this.scrollSpeed[1] * delta / 1000;
      if (Draggables._lastScrollPointer[0] < 0)
        Draggables._lastScrollPointer[0] = 0;
      if (Draggables._lastScrollPointer[1] < 0)
        Draggables._lastScrollPointer[1] = 0;
      this.draw(Draggables._lastScrollPointer);
    }

    if(this.options.change) this.options.change(this);
  },

  _getWindowScroll: function(w) {
    var T, L, W, H;
    with (w.document) {
      if (w.document.documentElement && documentElement.scrollTop) {
        T = documentElement.scrollTop;
        L = documentElement.scrollLeft;
      } else if (w.document.body) {
        T = body.scrollTop;
        L = body.scrollLeft;
      }
      if (w.innerWidth) {
        W = w.innerWidth;
        H = w.innerHeight;
      } else if (w.document.documentElement && documentElement.clientWidth) {
        W = documentElement.clientWidth;
        H = documentElement.clientHeight;
      } else {
        W = body.offsetWidth;
        H = body.offsetHeight;
      }
    }
    return { top: T, left: L, width: W, height: H };
  }
});

Draggable._dragging = { };

/*--------------------------------------------------------------------------*/

var SortableObserver = Class.create({
  initialize: function(element, observer) {
    this.element   = $(element);
    this.observer  = observer;
    this.lastValue = Sortable.serialize(this.element);
  },

  onStart: function() {
    this.lastValue = Sortable.serialize(this.element);
  },

  onEnd: function() {
    Sortable.unmark();
    if(this.lastValue != Sortable.serialize(this.element))
      this.observer(this.element)
  }
});

var Sortable = {
  SERIALIZE_RULE: /^[^_\-](?:[A-Za-z0-9\-\_]*)[_](.*)$/,

  sortables: { },

  _findRootElement: function(element) {
    while (element.tagName.toUpperCase() != "BODY") {
      if(element.id && Sortable.sortables[element.id]) return element;
      element = element.parentNode;
    }
  },

  options: function(element) {
    element = Sortable._findRootElement($(element));
    if(!element) return;
    return Sortable.sortables[element.id];
  },

  destroy: function(element){
    element = $(element);
    var s = Sortable.sortables[element.id];

    if(s) {
      Draggables.removeObserver(s.element);
      s.droppables.each(function(d){ Droppables.remove(d) });
      s.draggables.invoke('destroy');

      delete Sortable.sortables[s.element.id];
    }
  },

  create: function(element) {
    element = $(element);
    var options = Object.extend({
      element:     element,
      tag:         'li',       // assumes li children, override with tag: 'tagname'
      dropOnEmpty: false,
      tree:        false,
      treeTag:     'ul',
      overlap:     'vertical', // one of 'vertical', 'horizontal'
      constraint:  'vertical', // one of 'vertical', 'horizontal', false
      containment: element,    // also takes array of elements (or id's); or false
      handle:      false,      // or a CSS class
      only:        false,
      delay:       0,
      hoverclass:  null,
      ghosting:    false,
      quiet:       false,
      scroll:      false,
      yg_clone:    false,
      scrollSensitivity: 20,
      scrollSpeed: 15,
      format:      this.SERIALIZE_RULE,

      // these take arrays of elements or ids and can be
      // used for better initialization performance
      elements:    false,
      handles:     false,

      onChange:    Prototype.emptyFunction,
      onUpdate:    Prototype.emptyFunction
    }, arguments[1] || { });

    // clear any old sortable with same element
    this.destroy(element);

    // build options for the draggables
    var options_for_draggable = {
      revert:      true,
      quiet:       options.quiet,
      scroll:      options.scroll,
      scrollSpeed: options.scrollSpeed,
      scrollSensitivity: options.scrollSensitivity,
      yg_clone:    options.yg_clone,
      delay:       options.delay,
      ghosting:    options.ghosting,
      constraint:  options.constraint,
      handle:      options.handle };

    if(options.starteffect)
      options_for_draggable.starteffect = options.starteffect;

    if(options.reverteffect)
      options_for_draggable.reverteffect = options.reverteffect;
    else
      if(options.ghosting) options_for_draggable.reverteffect = function(element) {
        element.style.top  = 0;
        element.style.left = 0;
      };

    if(options.endeffect)
      options_for_draggable.endeffect = options.endeffect;

    if(options.zindex)
      options_for_draggable.zindex = options.zindex;

    // build options for the droppables
    var options_for_droppable = {
      overlap:     options.overlap,
      containment: options.containment,
      tree:        options.tree,
      yg_clone:    options.yg_clone,
      hoverclass:  options.hoverclass,
      onHover:     Sortable.onHover
    };

    var options_for_tree = {
      onHover:      Sortable.onEmptyHover,
      overlap:      options.overlap,
      containment:  options.containment,
      yg_clone:    options.yg_clone,
      hoverclass:   options.hoverclass
    };

    // fix for gecko engine
    Element.cleanWhitespace(element);

    options.draggables = [];
    options.droppables = [];

    // drop on empty handling
    if(options.dropOnEmpty || options.tree) {
      Droppables.add(element, options_for_tree);
      options.droppables.push(element);
    }

    (options.elements || this.findElements(element, options) || []).each( function(e,i) {
      var handle = options.handles ? $(options.handles[i]) :
        (options.handle ? $(e).select('.' + options.handle)[0] : e);
      options.draggables.push(
        new Draggable(e, Object.extend(options_for_draggable, { handle: handle })));
      Droppables.add(e, options_for_droppable);
      if(options.tree) e.treeNode = element;
      options.droppables.push(e);
    });

    if(options.tree) {
      (Sortable.findTreeElements(element, options) || []).each( function(e) {
        Droppables.add(e, options_for_tree);
        e.treeNode = element;
        options.droppables.push(e);
      });
    }

    // keep reference
    this.sortables[element.identify()] = options;

    // for onupdate
    Draggables.addObserver(new SortableObserver(element, options.onUpdate));

  },

  // return all suitable-for-sortable elements in a guaranteed order
  findElements: function(element, options) {
    return Element.findChildren(
      element, options.only, options.tree ? true : false, options.tag);
  },

  findTreeElements: function(element, options) {
    return Element.findChildren(
      element, options.only, options.tree ? true : false, options.treeTag);
  },

  onHover: function(element, dropon, overlap) {

	$K.log( 'in onHover', $K.Log.DEBUG );
    if(Element.isParent(dropon, element)) return;

    if (!Position.within(dropon.parentNode.parentNode, Draggables._lastPointer[0], Draggables._lastPointer[1])) {
      if (dropon.parentNode.parentNode.parentNode) {
        if (typeof dropon.parentNode.parentNode.parentNode.onmouseout == 'function') {
          dropon.parentNode.parentNode.parentNode.onmouseout();
      	  return;
        }
      }
    }

    //$('yg_ddGhost').dropAllowed = true;
    $K.yg_activeDragInfo.hoverOverSortable = true;

    //if(overlap > .33 && overlap < .66 && Sortable.options(dropon).tree) {
    //  return;
    //} else
    if(overlap>0.5) {
      if (!$K.yg_activeDragInfo.hoverOverTree && ($($K.yg_currentHover).yg_reordering != "false")) {
		if (element.next()!=dropon) {
			Sortable.mark(dropon, 'before');
		}
      } else {
		if (Sortable._marker) Sortable._marker.hide();
      }

      if(element.next()!=dropon) {

        var oldParentNode = element.parentNode;
        // No-Clone
        $K.yg_activeDragInfo.position = 'before';
        $K.yg_activeDragInfo.element = element;
        $K.yg_activeDragInfo.target = dropon;
        $K.yg_activeDragInfo.before = dropon;

        if ((dropon.parentNode!=oldParentNode) && (Sortable.options(oldParentNode))) Sortable.options(oldParentNode).onChange(element);
        Sortable.options(dropon.parentNode).onChange(element);
      }
    } else {
      if (!$K.yg_activeDragInfo.hoverOverTree && ($($K.yg_currentHover).yg_reordering != "false")) {
        if (element.previous()!=dropon) {
          Sortable.mark(dropon, 'after');
        }
      }

      var nextElement = dropon.nextSibling || null;
      if(nextElement != element) {
        var oldParentNode = element.parentNode;
        // No-Clone
        $K.yg_activeDragInfo.position = 'after';
        $K.yg_activeDragInfo.element = element;

		$K.yg_activeDragInfo.target = dropon;
		$K.yg_activeDragInfo.before = nextElement;

        if ((dropon.parentNode!=oldParentNode) && Sortable.options(oldParentNode)) Sortable.options(oldParentNode).onChange(element);
        Sortable.options(dropon.parentNode).onChange(element);
      }
    }
  },

  onEmptyHover: function(element, dropon, overlap) {

	/*if (element.up()==dropon) {
		//$K.yg_activeDragInfo = { };
		Sortable._marker.hide();

		$K.yg_setDropAllowed(false);
		return;
	}*/

	$K.log("in onEmptyhover", $K.Log.DEBUG);

    if (!Sortable._marker) _createSortableMarker();

	var oldTopOffset = Sortable._marker.getStyle('top');

	var offsets = Position.cumulativeOffset(dropon);
	Sortable._marker.setStyle({left: offsets[0]+'px', top: offsets[1] + 'px'});
	Sortable._marker.setStyle({top: (offsets[1]+dropon.clientHeight) + 'px'});

	// Check if a scrollbar is visible, shorten needle if it is
	var ywindowRef = dropon.up('.ywindow').id;
	if ( $($K.windows[ywindowRef].id+'_scrollbar_v').getStyle('visibility') == 'hidden' ) {
		Sortable._marker.setStyle({width: ($K.windows[ywindowRef].boxwidth-37) + 'px'});
	} else {
		Sortable._marker.setStyle({width: ($K.windows[ywindowRef].boxwidth-(37 + $K.yg_scrollObjAttr.scrollBtnVWidth) ) + 'px'});
	}

    var sortable = Sortable.options(dropon);

	// Check if the draggables are contentblocks and reposition the needle if needed
	if ( sortable.element.id.indexOf('_scp_')!=-1 ) {
		var topMargin = 6;
		var chldElements = $(dropon).childElements();
		if ( (chldElements.length!=0) ) {

			if (chldElements.length==1) {
				topMargin += (dropon.firstDescendant().getHeight());
			} else {
				$K.log( '$actDragInfo:', $K.yg_activeDragInfo, $K.Log.DEBUG );
				var offsetTop = ($K.yg_mouseCoords.Y-offsets[1]);
				var offsetBottom = (dropon.getHeight()-offsetTop);

				if ( (offsetBottom<15) ) {
					topMargin += (dropon.getHeight()-10);
				}
			}

		}
		$K.yg_setSortableMarkerIndex(ywindowRef);
		Sortable._marker.setStyle({left: offsets[0]+'px', top: (offsets[1]+topMargin) + 'px'});
	}


	var leftoffset = (parseInt(dropon.up('.innercontent').getStyle('left').replace(/px/g, ''))||0);

	if (leftoffset<0) {
		var oldoffset = (parseInt(Sortable._marker.getStyle('left').replace(/px/g, ''))||0);
		Sortable._marker.setStyle({left: (oldoffset-leftoffset) + 'px' });
	}


    var oldParentNode = element.parentNode;
    var droponOptions = Sortable.options(dropon);

	Sortable._marker.show();

	//$K.yg_activeDragInfo.dropAllowed = true;

    if (dropon.id) {
      if ( (dropon.tagName == 'UL') ) {

		if (element._originalParent) {
	        if (dropon.id != element._originalParent.id) {

	          if( $('placeHolder') ) {
		            $('placeHolder').remove();
	          }

        	  if( (Sortable._marker) && (sortable.element.id.indexOf('_scp_')==-1) ) {
		            Sortable._marker.hide();
	          }

	          // Protect againgst Placeholder in Contentblocks & IE7 Bug (Patch does not affect IE8)
	          if (dropon.id.indexOf('_scp_')==-1) {
	        	  var newPH = document.createElement('div');
	        	  newPH.className = 'dropmarker';
	        	  newPH.style.position = 'relative';
	        	  newPH.style.top = '-1px';
	        	  newPH.style.left = '0px';
	        	  newPH.placeHolder = true;
	        	  newPH.id = 'placeHolder';
	        	  newPH.onmouseover = function() {
		        	  if ( $('placeHolder') ) {
			        	  $('placeHolder').hoverOverMarker = true;
			        	  $K.yg_setDropAllowed(true);
			        	  $('placeHolder').show();
		        	  }
	        	  }
	        	  newPH.onmouseout = function() {
		        	  if ( $('placeHolder') ) {
			        	  $('placeHolder').hoverOverMarker = false;
		        	  }
	        	  }

	        	  // Protect IE7 Bug (Patch does not affect IE8)
	        	  if (!(Prototype.Browser.IE && element.hasClassName('mk_formfield'))) {
	        		  dropon.appendChild(newPH);
	        	  }
	          }

	        }
    	}

      }
    }

    if(!Element.isParent(dropon, element)) {
      var index;

      var children = Sortable.findElements(dropon, {tag: droponOptions.tag, only: droponOptions.only});
      var child = null;

      if(children) {
        var offset = Element.offsetSize(dropon, droponOptions.overlap) * (1.0 - overlap);

        for (index = 0; index < children.length; index += 1) {
          if (offset - Element.offsetSize (children[index], droponOptions.overlap) >= 0) {
            offset -= Element.offsetSize (children[index], droponOptions.overlap);
          } else if (offset - (Element.offsetSize (children[index], droponOptions.overlap) / 2) >= 0) {
            child = index + 1 < children.length ? children[index + 1] : null;
            break;
          } else {
            child = children[index];
            break;
          }
        }
      }


      // No-Clone
      $K.yg_activeDragInfo.position = 'into';
	  $K.yg_activeDragInfo.element = element;

	  $K.yg_activeDragInfo.target = dropon;
	  $K.yg_activeDragInfo.before = child;

      if (Sortable.options(oldParentNode)) Sortable.options(oldParentNode).onChange(element);
      droponOptions.onChange(element);
    }
  },

  unmark: function() {
    if(Sortable._marker) {
      Sortable._marker.hide();
    }
    if(Sortable._guide && Sortable._guide.parentNode){
      Sortable._guide.parentNode.removeChild(Sortable._guide);
    }
    if(Sortable._emptyPlaceMarker)
      Element.hide(Sortable._emptyPlaceMarker);
  },

  mark: function(dropon, position) {

  	$K.log( 'Entering "MARK"', $K.Log.DEBUG );
  	if (!$K.yg_activeDragInfo.dropAllowed) return;
  	if ($K.yg_activeDragInfo.hoverOverFormfield) return;

  	var win_prefix = dropon.up('.ywindow').id;

  	var topoffset = dropon.positionedOffset();
  	topoffset = topoffset[1];
  	var scrolled = parseInt($(win_prefix+'_innercontent').style.top.replace(/px/g, ''));
  	var hght = $(win_prefix+'_innercontent').up().clientHeight;

  	if (position=='after') topoffset = topoffset + dropon.clientHeight;

	if (dropon.id.indexOf('subpage')!=-1) {
		scrolled = 0;
	}
  	if ((hght-(topoffset+scrolled)) < 0) {
  		// out of bounds?
  		$K.yg_setDropAllowed(false);
  		return;
  	}

	$K.log( 'Drawing marks on ', dropon, $K.Log.DEBUG );

    // mark on ghosting only

    var sortable = Sortable.options(dropon.parentNode);
    if(sortable && !sortable.ghosting) return;

    if(!Sortable._marker)
		_createSortableMarker();

    var offsets = dropon.cumulativeOffset();

	// Get related parentNode
	var droponParent = dropon.parentNode;

	// Do not check bounds for contentblocks (maybe needs a fix)
	if ( (sortable.element.id.indexOf('_scp_')==-1)) {

	    if(position=='after') {
	      if ( (dropon.offsetTop + droponParent.offsetTop + dropon.offsetHeight) < 0) {
			return;
		  }
	      if ( (dropon.offsetTop + droponParent.offsetTop + dropon.offsetHeight) < 0) {
			return;
		  }
	    } else {
	      if ( (dropon.offsetTop + droponParent.offsetTop) > (sortable.element.parentNode.offsetHeight + 30) ){
			return;
		  }
	      if ( (dropon.offsetTop + droponParent.offsetTop) < 0){
			return;
		  }
	    }
	}

	$K.yg_setSortableMarkerIndex(dropon.up('.ywindow').id);

	Sortable._marker.setStyle({left: offsets[0]+'px', top: (offsets[1]-1) + 'px'});

    if(position=='after')
      if(sortable.overlap == 'horizontal')
    	Sortable._marker.setStyle({left: (offsets[0]+dropon.getWidth()) + 'px'});
      else
    	Sortable._marker.setStyle({top: (offsets[1]+dropon.getHeight()-1) + 'px'});


	// Check if marker really should be shown (and is not out of bounds of the sortable)
	if ( sortable.element.id.indexOf('_scp_')==-1 ) {

		var marker_top = parseInt(Sortable._marker.style.top.replace(/px/g, ''))+1;
		var sortable_top = parseInt(dropon.up(2).cumulativeOffset().top);
		var sortable_bottom = sortable_top+parseInt(dropon.up(2).getHeight());

		$K.log( 'MK_TOP: ', marker_top, 'SRT_TOP: ', sortable_top, 'SRT_BOT: ', sortable_bottom, $K.Log.DEBUG );

		if ( (marker_top<sortable_top) || (marker_top>sortable_bottom) ) {
			$K.log('Out of Bounds...', $K.Log.INFO);
			Sortable._marker.hide();
			return;
		}
	}

	// Which is the containing parent element?
	if (dropon.up('.entries_list')) {
    	var containerRef = dropon.up('.entries_list');
		var baseId = containerRef.identify();
	} else {
    	var containerRef = dropon.up('.ywindowinnerie');
		var baseId = dropon.up('.ywindow').identify();
	}

	Sortable._marker.setStyle({width: (containerRef.getWidth() - 4 + $K.yg_scrollObjAttr.scrollBtnVWidth) + 'px'});

	// Check if the draggables are contentblocks and reposition the needle if needed
	if (sortable.element.id.indexOf('_scp_')!=-1) {
	    if(position=='after') {
			$K.log( 'After', $K.Log.DEBUG );
			Sortable._marker.setStyle({top: (offsets[1]+dropon.clientHeight+5) + 'px'});
	    } else {
			$K.log( 'Before', $K.Log.DEBUG );
			Sortable._marker.setStyle({left: offsets[0]+'px', top: (offsets[1]+5) + 'px'});
		}
	}

	var leftoffset = (parseInt(dropon.up('.innercontent').getStyle('left').replace(/px/g, ''))||0);

	if (leftoffset<0) {
		var oldoffset = (parseInt(Sortable._marker.getStyle('left').replace(/px/g, ''))||0);
		Sortable._marker.setStyle({left: (oldoffset-leftoffset) + 'px' });
	}

    Sortable._marker.show();

    // flicker protection
    var nextItem = dropon.next();
    if (nextItem) {
      if ( (nextItem.id!='placeHolder') && (!nextItem.isClone) ) {
        if( $('placeHolder') ) {
          if ( !$('placeHolder').hoverOverMarker ) {
            $('placeHolder').remove();
          }
        }
      } else {
        if (position=='before') {
          if( $('placeHolder') ) {
            if ( !$('placeHolder').hoverOverMarker ) {
              $('placeHolder').remove();
            }
          }
        }
      }
    }
  },

  _tree: function(element, options, parent) {
    var children = Sortable.findElements(element, options) || [];

    for (var i = 0; i < children.length; ++i) {
      var match = children[i].id.match(options.format);

      if (!match) continue;

      var child = {
        id: encodeURIComponent(match ? match[1] : null),
        element: element,
        parent: parent,
        children: [],
        position: parent.children.length,
        container: $(children[i]).down(options.treeTag)
      };

      /* Get the element containing the children and recurse over it */
      if (child.container)
        this._tree(child.container, options, child);

      parent.children.push (child);
    }

    return parent;
  },

  tree: function(element) {
    element = $(element);
    var sortableOptions = this.options(element);
    var options = Object.extend({
      tag: sortableOptions.tag,
      treeTag: sortableOptions.treeTag,
      only: sortableOptions.only,
      name: element.id,
      format: sortableOptions.format
    }, arguments[1] || { });

    var root = {
      id: null,
      parent: null,
      children: [],
      container: element,
      position: 0
    };

    return Sortable._tree(element, options, root);
  },

  /* Construct a [i] index for a particular node */
  _constructIndex: function(node) {
    var index = '';
    do {
      if (node.id) index = '[' + node.position + ']' + index;
    } while ((node = node.parent) != null);
    return index;
  },

  sequence: function(element) {
    element = $(element);
    var options = Object.extend(this.options(element), arguments[1] || { });

    return $(this.findElements(element, options) || []).map( function(item) {
      return item.id.match(options.format) ? item.id.match(options.format)[1] : '';
    });
  },

  setSequence: function(element, new_sequence) {
    element = $(element);
    var options = Object.extend(this.options(element), arguments[2] || { });

    var nodeMap = { };
    this.findElements(element, options).each( function(n) {
        if (n.id.match(options.format))
            nodeMap[n.id.match(options.format)[1]] = [n, n.parentNode];
        n.parentNode.removeChild(n);
    });

    new_sequence.each(function(ident) {
      var n = nodeMap[ident];
      if (n) {
        n[1].appendChild(n[0]);
        delete nodeMap[ident];
      }
    });
  },

  serialize: function(element) {
    element = $(element);
    var options = Object.extend(Sortable.options(element), arguments[1] || { });
    var name = encodeURIComponent(
      (arguments[1] && arguments[1].name) ? arguments[1].name : element.id);

    if (options.tree) {
      return Sortable.tree(element, arguments[1]).children.map( function (item) {
        return [name + Sortable._constructIndex(item) + "[id]=" +
                encodeURIComponent(item.id)].concat(item.children.map(arguments.callee));
      }).flatten().join('&');
    } else {
      return Sortable.sequence(element, arguments[1]).map( function(item) {
        return name + "[]=" + encodeURIComponent(item);
      }).join('&');
    }
  },

  // Patch start
  createGuide : function (element) {
    if(!Sortable._guide) {
      Sortable._guide = $('_guide') || document.createElement('DIV');
      Sortable._guide.style.position = 'relative';
      Sortable._guide.style.width = '1px';
      Sortable._guide.style.height = '0px';
      Sortable._guide.style.cssFloat = 'left';
      Sortable._guide.id = 'guide';

      document.getElementsByTagName("body").item(0).appendChild(Sortable._guide);
    }
  },

  markEmptyPlace: function(element) {
    if(!Sortable._emptyPlaceMarker) {
      Sortable._emptyPlaceMarker = $('emptyPlaceMarker') || document.createElement('DIV');
      Element.hide(Sortable._emptyPlaceMarker);
      Element.addClassName(Sortable._emptyPlaceMarker, 'emptyPlaceMarker');
      Sortable._emptyPlaceMarker.style.position = 'absolute';
      document.getElementsByTagName("body").item(0).appendChild(Sortable._emptyPlaceMarker);
    }

    var pos = Position.cumulativeOffset(Sortable._guide);
    Sortable._emptyPlaceMarker.style.left = (pos[0] + 5)+ 'px';
    Sortable._emptyPlaceMarker.style.top = (pos[1] + 5) + 'px';

    var dim = {};

    dim.width = (Element.getDimensions(element).width-5) + 'px';
    dim.height = (Element.getDimensions(element).height-5) + 'px';

    Sortable._emptyPlaceMarker.setStyle(dim);

    var mg = Element.getStyle(element, 'margin');
    if(mg && mg != '') {
      Sortable._emptyPlaceMarker.setStyle({margin : mg});
    } else  {
      Sortable._emptyPlaceMarker.setStyle({ margin : ''});
    }
    Element.show(Sortable._emptyPlaceMarker);
  }
};

// Returns true if child is contained within element
Element.isParent = function(child, element) {
  if (!child.parentNode || child == element) return false;
  if (child.parentNode == element) return true;
  return Element.isParent(child.parentNode, element);
};

Element.findChildren = function(element, only, recursive, tagName) {
  if(!element.hasChildNodes()) return null;
  tagName = tagName.toUpperCase();
  if(only) only = [only].flatten();
  var elements = [];
  $A(element.childNodes).each( function(e) {
    if(e.tagName && e.tagName.toUpperCase()==tagName &&
      (!only || (Element.classNames(e).detect(function(v) { return only.include(v) }))))
        elements.push(e);
    if(recursive) {
      var grandchildren = Element.findChildren(e, only, recursive, tagName);
      if(grandchildren) elements.push(grandchildren);
    }
  });

  return (elements.length>0 ? elements.flatten() : []);
};

Element.offsetSize = function (element, type) {
  return element['offset' + ((type=='vertical' || type=='height') ? 'Height' : 'Width')];
};

function _createSortableMarker() {
	Sortable._marker =
		($('dropmarker') || Element.extend(document.createElement('DIV'))).hide().addClassName('dropmarker').setStyle({position:'absolute'});

	Sortable._marker.onmouseover = function() {
		Sortable._marker.hoverOverMarker = true;
		this.show();
		$K.yg_setDropAllowed(true);
	}
	Sortable._marker.onmouseout = function() {
		Sortable._marker.hoverOverMarker = false;
	}
	Sortable._marker.onmouseup = function() {
		if ($K.yg_activeDragInfo.target && $K.yg_activeDragInfo.target.parentNode) {
			var x = $K.yg_activeDragInfo.target.parentNode.id;
			$K.yg_customSortableOnDrop(x);
		}
	}
	document.getElementsByTagName("body").item(0).appendChild(Sortable._marker);
};
