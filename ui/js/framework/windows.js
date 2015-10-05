/**
 * @fileoverview Supplies the window object and functions to handle it.
 */

/**
 * Holds id's of all scrollbars.
 * @type Array of Objects.
 */
$K.scrollbars = new Array();


/**
 * Holds id's of all windows.
 * @type Array of Objects.
 */
$K.windows = {};
$K.topZIndex = 100;
$K.wndid = 0;
$K.windowTopDiff = 43;
$K.winhorizontalDiff = 28;
$K.panelwidth = new Array();
$K.panelminwidth = 980;
$K.panelheight = 0;
$K.maincontwid = 0;
$K.inherithover = false;
$K.temptimer = new Array();
$K.yg_templates = new Array();



/**
 * Increments and returns the topZIndex
 */
$K.yg_incrementTopZIndex = function() {
	$K.topZIndex += 4;
	return $K.topZIndex;
}


/**
 * Checks if window requires new ZIndex
 * @param { String } [winId] id of related window
 */
$K.yg_windowToTop = function(winId) {
	$K.activeWindow = $K.windows[winId];
	if ($K.windows[winId] && ($K.windows[winId].type=="dialog")) {
		if ($(winId).style.zIndex != $K.topZIndex) {
			if ($(winId).style.zIndex >= $K.topZIndex) {
				$K.topZindex = $(winId).style.zIndex;
			} else {
				$(winId).style.zIndex = $K.yg_incrementTopZIndex();
			}
		}
	}
}

/**
 * The wndbox window object
 * @class This is the ywindow window object
 * @param { Array } [params] all params
 */
$K.yg_wndobj = function(params) {

	this.loadparams = new Object();
	// INIT
	if (params.config) {

		($K.yg_windowdata[params.config]["WIDTH"]) ? boxwidth = $K.yg_windowdata[params.config]["WIDTH"] : boxwidth = $K.yg_windowdata["DEFAULT"]["WIDTH"];
		($K.yg_windowdata[params.config]["HEIGHT"]) ? boxheight = $K.yg_windowdata[params.config]["HEIGHT"]	: boxheight = $K.yg_windowdata["DEFAULT"]["WIDTH"];

		($K.yg_windowdata[params.config]["MINWIDTH"]) ? minwidth = $K.yg_windowdata[params.config]["MINWIDTH"] : minwidth = $K.yg_windowdata["DEFAULT"]["MINWIDTH"];
		($K.yg_windowdata[params.config]["MINHEIGHT"]) ? minheight = $K.yg_windowdata[params.config]["MINHEIGHT"] : minheight = $K.yg_windowdata["DEFAULT"]["MINHEIGHT"];

		if (boxheight > (document.viewport.getHeight() - 54)) boxheight = document.viewport.getHeight() - 54;

		if (minwidth > boxwidth) minwidth = boxwidth;
		if (minheight > boxheight) minheight = boxheight;

		// create tab array
		i = 0;
		tabs = new Array();

		for (var element in $K.yg_windowdata[params.config]["TABS"]) {
			tabs[i] = new Array()
			tabs[i]["NAME"] = element;
			if ($K.yg_windowdata[params.config]["TABS"][element]["INIT"]) {
				tabs[i].init = function() { initFunc = new Function($K.yg_windowdata[params.config]["TABS"][this.element]["INIT"]).bind({ wndobj: this.wndobj, element: this.element }); initFunc(); }.bind({ wndobj: this, element: element });
			} else {
				tabs[i].init = false;
			}
			if ($K.yg_windowdata[params.config]["TABS"][element]["REFRESH"]) {
				tabs[i].refresh = function() { refreshFunc = new Function($K.yg_windowdata[params.config]["TABS"][this.element]["REFRESH"]).bind({ wndobj: this.wndobj, element: this.element }); refreshFunc(); }.bind({ wndobj: this, element: element });
			} else {
				tabs[i].refresh = false;
			}

			if ($K.yg_windowdata[params.config]["TABS"][element]["SUBMIT"]) {
				tabs[i].submit = function() {
					submitFunc = new Function($K.yg_windowdata[params.config]["TABS"][this.element]["SUBMIT"]).bind({ wndobj: this.wndobj, element: this.element });
					if (!$(this.wndobj.id).hasClassName('mk_noexec')) {
						submitFunc();
					}
				}.bind({ wndobj: this, element: element })
			} else {
				tabs[i].submit = false
			}
			if ($K.yg_windowdata[params.config]["TABS"][element]["CONTENTRIGHT"]) {
				for (var elemright in $K.yg_windowdata[params.config]["TABS"][element]["CONTENTRIGHT"]) {
					tabs[i]["CONTENTRIGHT"] = elemright;
					if ($K.yg_windowdata[params.config]["TABS"][element]["CONTENTRIGHT"][elemright]["INIT"]) {
						tabs[i].initcontentright = function() { initFunc = new Function($K.yg_windowdata[params.config]["TABS"][this.element]["CONTENTRIGHT"][this.elemright]["INIT"]).bind({ wndobj: this.wndobj, element: this.element, elemright: this.elemright }); initFunc(); }.bind({ wndobj: this, element: element, elemright: elemright });
					} else {
						tabs[i].initcontentright = false;
					}
				}
			} else {
				tabs[i]["CONTENTRIGHT"] = false
			}
			if ($K.yg_windowdata[params.config]["TABS"][element]["CLASS"]) { tabs[i]["CLASS"] = $K.yg_windowdata[params.config]["TABS"][element]["CLASS"] } else { tabs[i]["CLASS"] = false }
			if ($K.yg_windowdata[params.config]["TABS"][element]["CACHE"]) { tabs[i]["CACHE"] = $K.yg_windowdata[params.config]["TABS"][element]["CACHE"] } else { tabs[i]["CACHE"] = false }
			if ($K.yg_windowdata[params.config]["TABS"][element]["TITLE"]) { tabs[i]["TITLE"] = $K.yg_windowdata[params.config]["TABS"][element]["TITLE"] } else { tabs[i]["TITLE"] = "" }
			if ($K.yg_windowdata[params.config]["TABS"][element]["FOLDER"]) { tabs[i]["FOLDER"] = parseInt($K.yg_windowdata[params.config]["TABS"][element]["FOLDER"]) } else { tabs[i]["FOLDER"] = false }
			if ($K.yg_windowdata[params.config]["TABS"][element]["TRASHCAN"]) { tabs[i]["TRASHCAN"] = Boolean($K.yg_windowdata[params.config]["TABS"][element]["TRASHCAN"]) } else { tabs[i]["TRASHCAN"] = false }
			if ($K.yg_windowdata[params.config]["TABS"][element]["WORKFLOW"]) { tabs[i]["WORKFLOW"] = $K.yg_windowdata[params.config]["TABS"][element]["WORKFLOW"] } else { tabs[i]["WORKFLOW"] = false }
			if ($K.yg_windowdata[params.config]["TABS"][element]["SELECTED"]) { tabs[i]["SELECTED"] = $K.yg_windowdata[params.config]["TABS"][element]["SELECTED"] } else { tabs[i]["SELECTED"] = false }
			i++;
		}

		if ($K.yg_windowdata[params.config]["LOADPARAMS"]) this.loadparams = $K.yg_windowdata[params.config]["LOADPARAMS"].evalJSON();
		container = $K.yg_windowdata[params.config]["CONTAINER"];
		customid = $K.yg_windowdata[params.config]["CUSTOMID"];
		locking = $K.yg_windowdata[params.config]["LOCKING"];
		objecttype = $K.yg_windowdata[params.config]["OBJECTTYPE"];
		type = $K.yg_windowdata[params.config]["TYPE"];
		windowClass = $K.yg_windowdata[params.config]["CLASS"];
		windowStyle = $K.yg_windowdata[params.config]["STYLE"];
		stayInBackground = $K.yg_windowdata[params.config]["STAY_IN_BACKGROUND"];
		if ($K.yg_windowdata[params.config]["VERTICALSPLIT"]) { verticalsplit = $K.yg_windowdata[params.config]["VERTICALSPLIT"]; } else { verticalsplit = false; }
		icon = $K.yg_windowdata[params.config]["ICON"];
		titleclass = $K.yg_windowdata[params.config]["TITLE_CLASS"];
		title = $K.yg_windowdata[params.config]["TITLE"];
		buttons = $K.yg_windowdata[params.config]["BUTTONS"];

	}
	boundWindow = false;
	yg_id = false;

	if (params.customid) customid = params.customid;
	if (params.objecttype) objecttype = params.objecttype;
	if (params.width) boxwidth = params.width;
	if (params.height) boxheight = params.height;
	if (params.minwidth) minwidth = params.minwidth;
	if (params.minheight) minheight = params.minheight;
	if (params.tabs) {
		tabs = params.tabs;
		var me = this;
		tabs.each(function(item, itemID) {
			if (item.INIT) {
				tabs[itemID].init = function() {initFunc = new Function(item.INIT).bind({wndobj: me, element: element});initFunc();}
			} else {
				tabs[itemID].init = false;
			}
			if (item.REFRESH) {
				tabs[itemID].refresh = function() {refreshFunc = new Function(item.REFRESH).bind({wndobj: me, element: element});refreshFunc();}
			} else {
				tabs[itemID].refresh = false;
			}
		});
	}
	if (params.type) type = params.type;
	if (params.container) container = params.container;
	if (params['class']) windowClass = params['class'];
	if (params.style) windowStyle = params.style;
	if (params.stayInBackground) stayInBackground = params.stayInBackground;
	if (params.boundWindow) boundWindow = params.boundwindow;
	if (params.verticalsplit) verticalsplit = params.verticalsplit;
	if (params.icon) icon = params.icon;
	if (params.titleclass) titleclass = params.titleclass;
	if (params.title) title = params.title;
	if (params.locking) locking = params.locking;
	if (params.buttons) buttons = params.buttons;
	if (params.openerYgId) this.openerYgId = params.openerYgId;
	if (this.loadparams == undefined) this.loadparams = new Object();
	if (params.loadparams) Object.extend(this.loadparams, params.loadparams);
	if ((this.loadparams) && (this.loadparams["yg_id"])) yg_id = this.loadparams["yg_id"];

	if (customid == undefined) {
		$K.wndid++;
		var ywindownum = $K.wndid;
	} else {
		var ywindownum = customid;
	}
	var ywindowid = "wid_"+ywindownum;
	$K.windows[ywindowid] = this;

	var windowhtml = $K.yg_templates["window"].evaluate({
		win_no: ywindownum
	});

	// check container
	if (container == undefined) {
		if (type == "dialog") {
			container = "dialogcontainer";
		} else {
			container = "maincontainer";
		}
	}

	$(container).insert({top:windowhtml});

	if (type == "dialog") {
		$(ywindowid).addClassName("ydialog");
	} else {
		if ($(ywindowid+"_closelink")) {
			$(ywindowid+"_closelink").addClassName("disabled");
			$(ywindowid+"_closelink").onclick = function() { return false; };
		}
	}



	this.minwidth = minwidth;
	this.minheight = minheight;

	// TEMP coz TREE not existing
	if (type == "tree") {
		this.basetype = "tree";
		type = "content";
	}
	if (type!=undefined) {
		this.type=type;
	}

	ywindowobj=$(ywindowid);
	this.id=ywindowobj.id;
	this.num=ywindownum;
	this.locking=Boolean(locking);
	if (!this.locking) {
		this.locked=false;
	}
	this.stayInBackground = Boolean(stayInBackground);

	// render buttons
	curbuttons = $(ywindowid + "_buttons").immediateDescendants();
	if (buttons) {
		for (var element in buttons) {
			var tmpbutton = $(document.createElement('td'));
			buttons[element].obj = $(document.createElement('a'));

			tmpbutton.appendChild(buttons[element].obj);
			tmpbutton.addClassName("ywindow_bt");
			if (buttons[element]["TDCLASS"]) tmpbutton.addClassName(buttons[element]["TDCLASS"]);
			if (buttons[element]["ID"]) buttons[element].obj.id = ywindowid+"_"+buttons[element]["ID"];
			if (buttons[element]["PROPERTY"]) buttons[element].obj.writeAttribute("yg_property",buttons[element]["PROPERTY"]);
			if (buttons[element]["TYPE"]) buttons[element].obj.writeAttribute("yg_type",buttons[element]["TYPE"]);
			if (buttons[element]["ONCLICK"]) {
				buttons[element].obj.tmpOnclick = buttons[element]["ONCLICK"];
				buttons[element].obj.wndobj = this;
				buttons[element].obj.onclick = (function(event) {
					this.event = event;
					if (this.hasClassName('disabled') == false) {
						var tmpFunc = new Function(this.tmpOnclick).bindAsEventListener(this);
						tmpFunc();
					}
				}).bindAsEventListener(buttons[element].obj);
			}
			if (buttons[element]["ONMOUSEOVER"]) {
				buttons[element].obj.tmpOnMouseOver = buttons[element]["ONMOUSEOVER"];
				buttons[element].obj.wndobj = this;
			}
			if (buttons[element]["HELP"]) {
				buttons[element].obj.helptext = buttons[element]["HELP"];
				buttons[element].obj.writeAttribute("title", buttons[element]["HELP"]);
			}
			buttons[element].obj.onmouseover = (function() {
				if (this.hasClassName('disabled') == false) {
					var tmpFunc = new Function(this.tmpOnMouseOver).bindAsEventListener(this);
					tmpFunc();
				}
				if (this.helptext) {
					$K.yg_showHelp(this.helptext);
				}
			}).bindAsEventListener(buttons[element].obj);

			buttons[element].obj.onmouseout = function() {
				$K.yg_showHelp(false);
			}

			if (buttons[element]["YG_ONCLICK"]) buttons[element].obj.writeAttribute("yg_onclick",buttons[element]["YG_ONCLICK"]);
			buttons[element].obj.writeAttribute("yg_id", "btn-"+ywindownum);
			buttons[element].obj.className = element.toLowerCase();

			if (buttons[element]["ACLASS"]) buttons[element].obj.className += " "+(buttons[element]["ACLASS"]);
			if (buttons[element]["FOLDER"] && (buttons[element]["FOLDER"] == "1")) buttons[element].obj.addClassName("folder");

			if (buttons[element].obj.className.indexOf('tree_btn') != -1) {
				buttons[element].obj.className += " tree_btn disabled";
				if (!(buttons[element]["PROPERTY"])) buttons[element].obj.writeAttribute("yg_property", "tree_btn");
			}
			curbuttons[curbuttons.length-1].insert({'before': tmpbutton});
		}
	}

	// set windowclass
	if (windowClass != undefined) ywindowobj.addClassName(windowClass);
	if (windowStyle != undefined) ywindowobj.writeAttribute('style', windowStyle);

	// set window icon
	if (icon != undefined) {
		if (typeof(icon) == 'object') {
			$(ywindowid+"_header").addClassName('file');
			$K.windows[ywindowid].setFileType('<span class="filetype '+icon.color+'" yg_type="file" yg_id="'+icon.objectid+'-file" yg_property="type">'+icon.typecode.toUpperCase()+'</span>');
			$K.yg_customAttributeHandler($(ywindowid+"_klops"));
		} else {
			$(ywindowid+"_header").addClassName(icon);
			if ($(ywindowid+"_klops") && $(ywindowid+"_klops").down()) {
				$(ywindowid+"_klops").down().addClassName(icon);
			}
		}
	}

	if (objecttype) {
		this.yg_type = ywindowobj.yg_type = objecttype;
	}
	if (boundWindow) {
		this.boundWindow = boundWindow;
	}
	if (yg_id) {
		this.yg_id = yg_id;
	}

	// set title
	if (titleclass) $(ywindowid+"_title").addClassName(titleclass);

	if (title) {
		this.setCaption(title, objecttype);
	}

	this.tabs=tabs;
	$K.activeWindow = this;

	// any click selects this box for mousewheel-scrolling
	$(ywindowid+"_container").observe('mouseover', function(ev) {
		tmpid = this.id.substring(0,this.id.indexOf("_container"));
		if (($K.windows[tmpid]) && ($K.windows[tmpid].fullsearch==true)) {
			$K.yg_scrollObjAttr.activeArea=tmpid+"_searchcontent";
		} else {
			if ($K.yg_preactive == false) $K.yg_scrollObjAttr.activeArea=tmpid;
		}
	});

	// and column2
	if ($(ywindowid+"_column2content")) {
		$(ywindowid+"_column2content").observe('mouseover', function(ev) {
			$K.yg_scrollObjAttr.activeArea=this.id;
		});
	}

	if (verticalsplit == false) {
		if ($(this.id+"_vertcut")) $(this.id+"_vertcut").remove();
		if ($(this.id+"_vertcutbot")) $(this.id+"_vertcutbot").remove();
		if ($(this.id+"_vertcuttop")) $(this.id+"_vertcuttop").remove();
		if ($(this.id+"_column2")) $(this.id+"_column2").remove();
		if ($(this.id+"_column2bot")) $(this.id+"_column2bot").remove();
		if ($(this.id+"_column2top")) $(this.id+"_column2top").remove();
	}
	this.verticalsplit=verticalsplit;

	// if not refreshmode
	if (type=="tree") {
		$K.yg_renderScroll($(this.id+"_searchscroll"),this.id+"_searchcontent");
		$K.scrollbars[this.id+"_searchcontent"]=$K.yg_initScrollbars($(this.id+"_searchcontent"), $(this.id+'_searchinnercontent'), $(this.id+'_searchinnercontentinner'), $(this.id+'_searchcontent_dragbar_v'), $(this.id+'_searchcontent_track_v'), $(this.id+'_searchcontent_dragbar_h'), $(this.id+'_searchcontent_track_h'), 0);
	}
	$K.yg_renderScroll($(this.id+"_scrollbars"),this.id);

	$K.scrollbars[this.id]=$K.yg_initScrollbars(ywindowobj, $(this.id+'_ywindowinner'), $(this.id+'_innercontent'), $(this.id+'_dragbar_v'), $(this.id+'_track_v'), $(this.id+'_dragbar_h'), $(this.id+'_track_h'), $(this.id+'_scrollblank'));

	if ($(this.id+"_tablecols")) this.tablecols = true;

	if (this.type=="dialog") {
		// if dialog
		$('minwins').insert($(this.id+'_klops'));
		$(this.id+'_klops').hide();
		$(this.id+'_klops').setStyle({visibility:'visible'});

		if ($(this.id+'_modalbg')) $(this.id+'_modalbg').setStyle( { zIndex:$K.yg_incrementTopZIndex() } );

		$(this.id).setStyle({ zIndex: $K.yg_incrementTopZIndex()});
		setTimeout("$('"+this.id+"').setStyle({zIndex: $K.yg_incrementTopZIndex()});",0);

		var tmpWinId = this.id;
		new Draggable(this.id,{handle:this.id+'_header', onStart: function() {
			$(tmpWinId).setStyle({ zIndex:$K.yg_incrementTopZIndex()});
		}, onEnd: function() {
			if ($(this.handle)) {
				pos = $(this.handle).up('.ywindow').viewportOffset();
				if (pos[1] < 5) {
					$(this.handle).up('.ywindow').setStyle({top: '5px'});
				}
			}
		} });
	}
	// any click selects this box
	zindexfunc = function(ev) {
		$K.yg_windowToTop(this.id);
	}
	ywindowobj.observe('click', zindexfunc);

	// draggable corner
	tmpdrag=$(this.id+'_dragcorner');
	new Draggable(tmpdrag,{change:$K.yg_boxResize.bind(this),starteffect:$K.yg_boxResizeStart.bind(this),endeffect:$K.yg_boxResizeEnd.bind(this)});

	if (verticalsplit > 0) {
		$K.yg_renderScroll($(this.id+"_column2scrollbars"),this.id+"_column2content");
		$K.scrollbars[this.id+"_column2"]=$K.yg_initScrollbars($(this.id+"_column2content"), $(this.id+'_column2innercontent'), $(this.id+'_column2innercontentinner'), $(this.id+'_column2content_dragbar_v'), $(this.id+'_column2content_track_v'), $(this.id+'_column2content_dragbar_h'), $(this.id+'_column2content_track_h'), $(this.id+'_scrollblankcolumn2'));
		tmpdrag=$(this.id+'_vertcutdrag');
		new Draggable(tmpdrag,{constraint:'horizontal',change:$K.yg_vertResize.bind(this),starteffect:$K.yg_vertResizeStart.bind(this),endeffect:$K.yg_vertResizeEnd.bind(this)});
	}
	this.init(boxwidth,boxheight,true,false);
}



/**
 * (Re-)Renders the window (e.g. after resizing)
 * @param { Int } [boxwidth] Width of the window.
 * @param { Int } [boxheight] Height of the window.
 * @param { Boolean } [firstrun] Is true if initialization is fired the first time.
 * @param { Boolean } [refreshmode] Is true if just refreshing the window.
 */
$K.yg_wndobj.prototype.init = function (boxwidth,boxheight,firstrun,refreshmode) {

	this.firstrun=firstrun;

	if (boxwidth!=undefined) { this.boxwidth=boxwidth; } else { boxwidth=this.boxwidth; }
	if (boxheight!=undefined) { this.boxheight=boxheight; } else { boxheight=this.boxheight; }

	if (firstrun==true) {
		if (this.tabs.length > 1) {
			tmpobj=$(this.id+"_topleft");
			if (tmpobj) {
				tmpobj.removeClassName('ywindow_tl');
				tmpobj.addClassName('ywindow_tltabs');
			}
		} else {
			$(this.id).addClassName('hidetabs');
		}
	}

	dialogtop=0;
	dialogbottom=0;
	filterheight=0;

	if ((this.tabs.elements) && (this.tab)) {
		if ($(this.id+'_filter_'+this.tab) && ($(this.id+'_filter_'+this.tab).getStyle('display') != 'none')) {
			filterheight = $(this.id+'_filter_'+this.tab).getHeight();
		}
	}
	if ($(this.id+'_dialogtop')) dialogtop += $(this.id+'_dialogtop').getHeight();
	if ($(this.id+'_dialogbottom')) dialogbottom += $(this.id+'_dialogbottom').getHeight();

	// box
	ywindowobj=$(this.id);
	ywindowobj.setStyle({width:boxwidth+'px'});

	this.topheight=$(this.id+'_tabs').getHeight()+$(this.id+'_buttons').getHeight();

	bottomdiff=16+$(this.id+'_bottom').getHeight()+$(this.id+'_spcbottom').getHeight();

	if ((bottomdiff==32) && (Prototype.Browser.IE)) bottomdiff=16;

	this.contentwidth=boxwidth-41;

	// div table
	tmpobj=$(this.id+'_cnttable');
	tmpobj.setStyle({display:'block'});

	contentheight = boxheight-this.topheight-bottomdiff-dialogtop-dialogbottom-filterheight;

	// div container
	tmpobj=$(this.id+'_container');
	tmpobj.setStyle({height:contentheight+'px'});

	// div ywindowinner
	tmpobj=$(this.id+'_ywindowinner');
	tmpobj.setStyle({height:contentheight+'px'});

	// Dirty safari 3 display hack
	if ((this.firstrun==true) && (Prototype.Browser.WebKit)) {
		tmpcntwid=$(this.id+'_innercontent').getWidth();
	}

	min=0;

	if ($(tmpobj).down()) {
		tmpobjd = $(tmpobj).down();
	}
	if (tmpobjd) {
		if ((tmpobjd.getHeight()>contentheight) && !(tmpobjd.getWidth()>(this.contentwidth-11))) min=9;
	}

	// width
	this.column1width=this.contentwidth;
	this.column2width=0;
	vertcutwidth=0;

	if (this.verticalsplit>0) {
		vertcutwidth=13;
		this.column2width=Math.round((this.contentwidth-1)/100*(100-this.verticalsplit))-vertcutwidth-1;
		this.column1width=this.contentwidth-2-this.column2width-vertcutwidth;

		if ((Prototype.Browser.IE) && (BrowserDetect.version > 7)) {
			this.column1width -= 1;
			this.column2width += 4;
		}

	}

	// div ywindowouter
	tmpobj=$(this.id+'_ywindowouter');
	tmpobj.setStyle({width:(this.column1width-1)+'px'});

	if (this.verticalsplit>0) {
		tmpobj=$(this.id+'_column2');
		tmpobj.setStyle({width:(this.column2width)+'px'});

		// IE Fixes
		if (Prototype.Browser.IE) {
			col2wid = this.column2width;
			tmpobj=$(this.id+'_column2bot');
			tmpobj.setStyle({width:(col2wid)+'px'});
			tmpobj=$(this.id+'_column2top');
			tmpobj.setStyle({width:(col2wid)+'px'});
		}

		column2contentheight = contentheight;

		if ($(this.id+"_column2filter")) column2contentheight -= $(this.id+"_column2filter").getHeight();
		if ($(this.id+"_column2bottom") && ($(this.id+"_column2bottom").getStyle('display') != "none")) column2contentheight -= $(this.id+"_column2bottom").getHeight();

		tmpobj=$(this.id+"_column2content");

		tmpobj.setStyle({height:column2contentheight+'px'});
		tmpobj.setStyle({width:(this.column2width-9)+'px'});

		tmpobj=$(this.id+"_column2outerbox");
		tmpobj.setStyle({height:column2contentheight+'px'});
		tmpobj=$(this.id+"_column2outercontent");
		tmpobj.setStyle({height:column2contentheight+'px'});
		tmpobj.setStyle({width:this.column2width+'px'});

		tmpobj=$(this.id+"_column2innercontent");
		tmpobj.setStyle({height:column2contentheight+'px'});
		tmpobj.setStyle({width:this.column2width+'px'});

		tmpdrag=$(this.id+'_vertcutdrag');
		tmpdrag.setStyle({height:contentheight+'px'});
	}
	blankleft = this.column1width+5;
	blanktop = boxheight - (9+bottomdiff+dialogbottom);
	if (this.type=="dialog") {
		blanktop += 1;
		blankleft += 1;
	}
	if ($(this.id+"_column2")) {
		blanktop2 = blanktop;
		tmpobj=$(this.id+"_column2bottom");
		if ((tmpobj) && (tmpobj.getStyle('display') != "none")) {
			blanktop2 -= tmpobj.getHeight();
		}
	}
	tmpobj=$(this.id+'_scrollblank');
	tmpobj.setStyle({top:blanktop+'px'});
	tmpobj.setStyle({left:blankleft+'px'});

	if ($(this.id+'_scrollblankcolumn2')) {
		tmpobj = $(this.id+'_scrollblankcolumn2');
		tmpobj.setStyle({top:(blanktop2)+'px'});
		tmpobj.setStyle({left:(this.column1width+21+this.column2width)+'px'});
	}

	// render tabs
	if ((firstrun==true) && (this.tabs!='notabs')) {
		this.tabs=new $K.tabs($(this.id+'_tabs'),this.tabs,this.id,this);
	}

	if ((this.tabs) && (refreshmode!=true)) {
		if (typeof(this.tabs.init) == 'function') this.tabs.init(true);
	}

	// Dirty safari 3 display hack
	if ((this.firstrun==true) && (Prototype.Browser.WebKit)) {
		$(this.id+'_innercontent').rightWidth=$(this.id+'_innercontent').clientWidth=tmpcntwid;
	}

	this.refresh();

	// add ghost if nothing selected
	if (($K.windows[this.id]) && ($K.windows[this.id].yg_id == undefined)) {
		if (!((this.basetype != "tree") && (this.type == "content"))) {
			$(this.id).removeClassName('boxghost');
		}
	}
	if (firstrun==true) {
		$K.yg_customAttributeHandler( $(this.id) );
		if ((this.type == "content") && (this.basetype != "tree")) {

			if (this.yg_id != -1) {
				$(this.id).addClassName('boxghost');
			}
		}

		if (this.type=="dialog") {
			// FIXME PARAM POSITION
			if ($(this.id).descendantOf($('previewcontainer'))) {
				$(this.id).setStyle({top:'5px'});
				$(this.id).setStyle({left:'5px'});
			} else {
				$K.yg_centerWindow($(this.id));
			}
		}
		ywindowobj.setStyle({visibility:'visible'});
		this.firstrun = false;
	}

}


/**
 * Removes the window
 */
$K.yg_wndobj.prototype.remove = function(force) {
	if (($K.windows[this.id].stayInBackground != true) || (force)) {

		var successFunc = function() {
			// Check if a matching SWFUpload object is instantianted
			if ($K.yg_SWFUploadObjects[this.id]) {
				$K.yg_SWFUploadObjects[this.id].destroy();
			}

			// Check if a timer is attached to this window
			if ($K.windows[this.id].periodicTimer) {
				$K.windows[this.id].periodicTimer.stop();
				$K.windows[this.id].periodicTimer = undefined;
			}

			// release locks for this window
			$K.yg_releaseLock(this.id.split('_')[1], $K.windows[this.id].yg_type, $K.windows[this.id].yg_id);

			// remove from dom
			if ($(this.id)) $(this.id).remove();
			if ($(this.id + "_klops")) $(this.id + "_klops").remove();
			if ($(this.id + "_modalbg")) $(this.id + "_modalbg").remove();
			delete $K.scrollbars[this.id];
			delete $K.windows[this.id];
		}

		// Check for open windows with this window as "parent"
		if (force) {
			successFunc.bind(this)();
		} else {
			$K.yg_checkOpenWindows( $K.windows[this.id].yg_id, $K.windows[this.id].yg_type, {onSuccess: successFunc.bind(this)} );
		}

	} else {

		// hide
		$(this.id).hide();
		$(this.id + "_klops").hide();

	}
}


/**
 * Minimizes the window
 */
$K.yg_wndobj.prototype.min = function() {
	$(this.id).setStyle({display:'none'});
	$(this.id+"_klops").setStyle({display:'block'});

	// if horizontal split
	if ($('maincontainer').hasClassName("mk_horizontal") && ($(this.id).descendantOf($('maincontainer')))) {
		$K.windows[$K.windows[this.id].boundWindow].init($K.windows[$K.windows[this.id].boundWindow].boxwidth, (document.viewport.getHeight() - $(this.id+"_klops").getHeight() - $K.windowTopDiff - $('toolbar').getHeight() - $K.winhorizontalDiff),false,false);
		if (!($(this.id+"_klops").down(".wrapper"))) {
			$(this.id+"_klops").innerHTML = "<div class='wrapperouter'><div class='wrapper'>"+$(this.id+"_klops").innerHTML +"</div></div>";
			$(this.id+"_klops").down(2).innerHTML = $(this.id+"_title").innerHTML;
			$(this.id+"_klops").down(2).writeAttribute("yg_property","name");
			$(this.id+"_klops").down(2).writeAttribute("yg_id",$(this.id+"_title").readAttribute("yg_id"));
			$(this.id+"_klops").down(2).writeAttribute("yg_type",$(this.id+"_title").readAttribute("yg_type"));
		}
		this.resizeCollapsed();
	}
	$K.windows[this.id].minimized = true;
	$K.yg_checkStatusBar();
}


/**
 * Maximizes the window
 */
$K.yg_wndobj.prototype.max = function() {
	tmpwid = 0;
	// if frameset
	if ($(this.id).descendantOf('maincontainer')) {
		tmparrx = $('maincontainer').childElements();

		for (var k = 0; k < tmparrx.length; k++) {
			if ((tmparrx[k].id != this.id+"_klops") && ((tmparrx[k].id == this.id) || (tmparrx[k].getStyle('display') != 'none'))) {
				tmpwid += tmparrx[k].getWidth();
			}
		}
		if (tmpwid > $K.maincontwid) {
			diff = tmpwid - ($K.maincontwid - 5);
			for (var k = 0; k < tmparrx.length; k++) {
				if ((tmparrx[k].getStyle('display') != 'none') && (tmparrx[k].id != this.id) && (tmparrx[k].id != this.id+"_klops")) {
					if (!($('maincontainer').hasClassName("mk_horizontal") && ($(this.id).descendantOf($('maincontainer'))))) {
						$K.windows[tmparrx[k].id].init(($K.windows[tmparrx[k].id].boxwidth - diff), $K.windows[tmparrx[k].id].boxheight, false, false);
					}
				}
			}
		}
	}

	$(this.id+"_klops").setStyle({display:'none'});
	$(this.id).setStyle({display:'block'});

	if ($('maincontainer').hasClassName("mk_horizontal") && ($(this.id).descendantOf($('maincontainer')))) {
		// if horizontal split
		newheight = this.boxheight;
		traceheight = $('toolbar').getHeight();
		boundheight = (document.viewport.getHeight() - this.boxheight - $K.windowTopDiff - traceheight - $K.winhorizontalDiff);

		if (((newheight + boundheight) > (document.viewport.getHeight() - $K.windowTopDiff - traceheight - $K.winhorizontalDiff)) && ($(this.boundWindow).visible()) || ((boundheight < 100) && $(this.boundWindow).visible())) {
			newheight = boundheight = (document.viewport.getHeight() - $K.windowTopDiff - traceheight  - $K.winhorizontalDiff) / 2;
		}
		if ($(this.boundWindow).visible()) {
			$K.windows[this.boundWindow].init($K.windows[this.boundWindow].boxwidth, boundheight,false,false);
		}
		this.init($K.windows[this.boundWindow].boxwidth, newheight,false,false);

	} else if (tmpwid > $K.maincontwid) {
		// if width > browser
		$K.windows[this.id].init(this.boxwidth, this.boxheight, false, false);
	} else {
		$K.windows[this.id].init();
	}
	$K.windows[this.id].minimized = false;
	$K.yg_checkStatusBar();
}


/**
 * Centers window
 * @param { obj } [obj] reference to dom element of window
 */
$K.yg_centerWindow = function(obj) {
	var tmpleft = Math.round((document.viewport.getWidth()-$(obj).getWidth())/2);
	var tmptop = Math.round((document.viewport.getHeight()-$(obj).getHeight())/2)-5;

	if (tmptop < 5) tmptop = 5;
	var posfree = false;

	while (posfree == false) {
		posfree = true;
		for (winID in $K.windows) {
			if ($K.windows[winID].type == "dialog") {
				dim = $(winID).positionedOffset();
				if ((dim[0] == tmpleft) && (dim[1] == tmptop)) {
					posfree = false;
					tmptop += 6;
					tmpleft += 6;
				}
			}
		}
	}

	$(obj).setStyle({
		top: tmptop+'px',
		left: tmpleft+'px'
	});
}


/**
 * Resizes collapsed window header
 */
$K.yg_wndobj.prototype.resizeCollapsed = function() {
	if ($(this.id+"_klops").visible()) $(this.id+"_klops").setStyle( { width: ($K.windows[$K.windows[this.id].boundWindow].boxwidth-25) + "px"} );
}


/**
 * Sets the window to "locked" state
 * @param { String } [userId] The ID of the user holduing the lock
 */
$K.yg_wndobj.prototype.setLocked = function(userID) {
	this.lockedByUser = userID;
}


/**
 * Sets the caption of the window
 * @param { String } [name] New caption of the window.
 */
$K.yg_wndobj.prototype.setCaption = function(name, type) {
	if (!type) type = 'page';

	this.caption = name;
	$(this.id+'_title').update(name);

	if (this.type == "dialog") {
		if ($(this.id+'_klops').down(3)) $(this.id+'_klops').down(3).update(name);
		if ($(this.id+'_klops').down('span')) $(this.id+'_klops').down('.titlespan').update(name);
	}

	$(this.id+'_klops').writeAttribute('title', name);

	$(this.id+'_title').yg_id = this.yg_id;
	$(this.id+'_title').yg_type = type;
	$(this.id+'_title').yg_property = 'name';
	$(this.id+'_title').writeAttribute('yg_id', this.yg_id);
	$(this.id+'_title').writeAttribute('yg_type', type);
	$(this.id+'_title').writeAttribute('yg_property', 'name');

	$(this.id+'_header').setStyle({width:'auto'});
	$(this.id+'_title').setStyle({width:'auto'});

	$K.yg_addLookup( this.yg_id, $(this.id+'_title') );
	if ($(this.id+'_klops').down('span')) $K.yg_addLookup( this.yg_id, $(this.id+'_klops') );
}


/**
 * Sets filetype of the window
 * @param { String } [filetype] filetype html
 */
$K.yg_wndobj.prototype.setFileType = function(filetype) {
	if (!$(this.id+'_headerfiletype')) {
		$(this.id+'_header').insert({top: filetype});
		$(this.id+'_header').down('.filetype').id = this.id+'_headerfiletype';
	} else {
		$(this.id+'_headerfiletype').update(filetype);
	}

	if (!$(this.id+'_klops_filetype')) {
		$(this.id+'_klops').down().addClassName('file');
		$(this.id+'_klops').down().insert({top: filetype});
		$(this.id+'_klops').down('.filetype').id = this.id+"_klops_filetype";
	} else {
		$(this.id+'_klops_filetype').update(filetype);
	}

	//DEBUG
	$K.yg_addLookup( this.yg_id, $(this.id+'_klops_filetype') );
	$K.yg_addLookup( this.yg_id, $(this.id+'_headerfiletype') );
}


/**
 * Sets the style of the caption of the window
 * @param { String } [name] New style of the window caption.
 */
$K.yg_wndobj.prototype.setCaptionStyle = function(name) {
	$(this.id+'_title').className = name;
}


/**
 * Sets the icon of the window
 * @param { String } [name] New icon of the window.
 */
$K.yg_wndobj.prototype.setIcon = function(name) {
	if ($(this.id+'_header')!=undefined) {
		$(this.id+'_header').className = name;
	}
	if (($(this.id+'_klops')!=undefined) && ($(this.id+'_klops').down())) {
		$(this.id+'_klops').down().className = name;
	}
}


/**
 * Sets extension icon
 * @param { String } [style] New icon of the window.
 */
$K.yg_wndobj.prototype.setExtensionIcon = function(style) {
	if ($(this.id+'_header')!=undefined) {
		if (!($(this.id+'_header').down('.iconextension'))) {
			$(this.id+'_header').insert({top: '<div class="iconextension"></div>'});
		}
		$(this.id+'_header').down('.iconextension').writeAttribute("style", style);
	}
	if (($(this.id+'_klops')!=undefined)) {
		$(this.id+'_klops').down(0).writeAttribute("style", style);
	}
}


/**
 * Sets icon URL
 * @param { String } [style] path to profile picture
 * @param { String } [name] name of the user
 * @param { String } [company] company
 * @param { String } [userid] User-ID
 */
$K.yg_wndobj.prototype.setUserHeader = function(url, name, company, userid) {

	if ($(this.id+'_header')!=undefined) {
		$(this.id+'_header').className = 'noicon';
		if (!($(this.id+'_header').down('.userpic'))) {
			$(this.id+'_header').insert({top: '<div class="userpic" yg_property="picture" yg_id="'+userid+'-user" yg_type="user" style="background-image:url(\''+url+'?rnd='+(Math.random()*1000)+'\')"></div><span class="username" yg_property="name" yg_id="'+userid+'-user" yg_type="user">'+name+'</span><br /><span class="usercompany" yg_type="user" yg_id="'+userid+'-user" yg_property="company">'+company+'</span>'});
			$K.yg_customAttributeHandler( $(this.id+'_header') );
			$(this.id+'_title').hide();
			$K.windows[this.id].init();
		} else {
			$(this.id+'_header').down('.userpic').replace('<div class="userpic" yg_property="picture" yg_id="'+userid+'-user" yg_type="user" style="background-image:url(\''+url+'?rnd='+(Math.random()*1000)+'\')"></div>');
			$(this.id+'_header').down('.username').replace('<span class="username" yg_property="name" yg_id="'+userid+'-user" yg_type="user">'+name+'</span>');
			$(this.id+'_header').down('.usercompany').replace('<span class="usercompany" yg_type="user" yg_id="'+userid+'-user" yg_property="company">'+company+'</span>');
			$K.yg_customAttributeHandler( $(this.id+'_header') );
		}
	}
	if (($(this.id+'_klops')!=undefined) && $(this.id).hasClassName('ydialog')) {
		$(this.id+'_klops').down(0).update('<span class="titlespan" yg_property="name" yg_id="'+userid+'-user" yg_type="user">'+name+'</span>');
		$K.yg_customAttributeHandler( $(this.id+'_klops') );
	}
}


/**
* Changes the disabled state of the stagebutton for pages and cblocks
* @param { String } [status] The status of the stagebutton
* @function
*/
$K.yg_wndobj.prototype.setStageButton = function(status) {
	if ($(this.id+'_stagebtn')) {
		if (status == '1') {
			$(this.id+'_stagebtn').removeClassName('disabled');
		} else {
			$(this.id+'_stagebtn').addClassName('disabled');
		}
	}
}


/**
 * Scrolls horizontal in case a table header exists
 * @param { Integer } [num] offset in pixel
 * @param { Integer } [col] window column
 */
$K.yg_wndobj.prototype.scrollh = function (num, col) {
	var scrollit = false;

	if (this.tablecols) {
		scrollit = true;

		if ($(this.id+"_tablecols") && ($(this.id+"_tablecols").descendantOf(this.id+"_column2")) && (col != "col2")) {
			scrollit = false;
		}
	}

	if (scrollit) {
		if ($(this.id+"_tablecols")) $(this.id+"_tablecols").setStyle({left:num+'px'});
	}
}


/**
 * Refreshs window's scrollbars
 * @param { String } [frame] if scrollbars of a particular frame needs to be refrehed (col1 or col2). If not provided all scrollbars will be refreshed
 *
 */
$K.yg_wndobj.prototype.refresh = function (frame) {

	if (typeof frame == 'object') {
		if ($(frame).descendantOf($(this.id+'_innercontent'))) {
			frame = "col1";
		} else if ($(frame).descendantOf($(this.id+'_column2innercontentinner'))) {
			frame = "col2";
		}
	}

	if (!(frame)) frame = "all";

	if ((((frame == "all") || (frame == "col1")) && (this.verticalsplit > 0)) || ((frame == "col2") && (this.verticalsplit > 0))) {
		if ($(this.id+'_column2innercontentinner').down('.mk_verticalcenter')) {
			$K.yg_centerContent($(this.id+'_column2innercontentinner').down('.mk_verticalcenter'),$(this.id+'_column2innercontentinner'));
		}
		if ($(this.id+'_column2innercontentinner').down('.mk_fillcontentspace')) {
			$K.yg_fillContentSpace($(this.id+'_column2innercontentinner').down('.mk_fillcontentspace'),$(this.id+'_column2innercontentinner'));
		}
		$K.yg_updateInnerContent($(this.id+'_column2innercontentinner'));
		$K.scrollbars[this.id+"_column2"].setBarSize(this.firstrun);
	}

	if ((frame == "all") || (frame == "col1")) {
		if ($(this.id+'_'+this.tab) && $(this.id+'_'+this.tab).down('.mk_verticalcenter')) {
			$K.yg_centerContent($(this.id+'_'+this.tab).down('.mk_verticalcenter'),$(this.id+'_'+this.tab));
		}
		if ($(this.id+'_'+this.tab) && $(this.id+'_'+this.tab).down('.mk_fillcontentspace')) {
			$K.yg_fillContentSpace($(this.id+'_'+this.tab).down('.mk_fillcontentspace'),$(this.id+'_'+this.tab));
		}
		$K.yg_updateInnerContent($(this.id+'_innercontent'));
		$K.scrollbars[this.id].setBarSize(this.firstrun);
		if ($(this.id+'_scrollbar_h').getStyle('visibility') != 'hidden') {
			$(this.id+'_pagedirs').setStyle({bottom:$(this.id+'_scrollbar_h').getHeight()+'px'});
		} else {
			$(this.id+'_pagedirs').setStyle({bottom:'0px'});
		}
	}

}


/**
 * Function for executing the submitfunction
 */
$K.yg_wndobj.prototype.submit = function() {
	if (typeof this._submitFunction == 'function') {
		this._submitFunction();
	}
}


/**
 * Called when starting to resize a window's columns. Needs to be binded to the window-object.
 */
$K.yg_vertResizeStart = function() {
	this.basepos = Position.page($(this.id));
}


/**
 * Called while resizing a window's columns. Needs to be binded to the window-object.
 */
$K.yg_vertResize = function() {
	tmpdrag=$(this.id+'_vertcutdrag');
	dragpos=Position.page(tmpdrag);

	this.verticalsplit  = (dragpos[0] - this.basepos[0] - 10) / this.contentwidth * 100;

	if (this.verticalsplit <= 10.5) {
		this.verticalsplit = 10;
		minwidth = this.contentwidth*(this.verticalsplit/100);
		tmpdrag.setStyle({left:(minwidth-this.column1width+1)+'px'});
	} else if (this.verticalsplit >= 90) {
		this.verticalsplit = 90;
		maxwidth = this.contentwidth*this.verticalsplit/100;
		tmpdrag.setStyle({left:(maxwidth-this.column1width-1)+'px'});
	}
	this.verticalsplit = Math.round(this.verticalsplit);
}


/**
 * Called when finishing to resize a window's columns. Needs to be binded to the window-object.
 */
$K.yg_vertResizeEnd = function() {
	this.init(this.boxwidth,this.boxheight,false,false);
	$(this.id+'_vertcutdrag').setStyle({left:'0px'});
}


/**
 * Centers content
 * @param { Object } [obj] obj to be vertically centered
 * @param { Object } [container] container
 */
$K.yg_centerContent = function(obj, container) {
	if (obj.down()) {
		objheight = obj.down().getHeight();
	} else {
		if (obj.tagName == 'IMG') {
			var tmpImage = new Image();
			tmpImage.src = obj.src;
			objheight = tmpImage.height;
			if (obj.readAttribute('zoomlevel')) objheight = objheight * (parseInt(obj.readAttribute('zoomlevel')) / 100);
		} else {
			objheight = obj.getHeight();
		}
	}
	delete tmpImage;
	containerheight = container.up(1).getHeight();
	margincontent = container.getStyle('marginTop');
	if (!(margincontent)) {
		margincontent = 0;
	} else {
		margincontent = parseInt(margincontent.substring(0,margincontent.length-2));
	}
	margintop = Math.round((containerheight - objheight) / 2) - margincontent;
	if (margintop < 0) margintop = 0;
	obj.setStyle({paddingTop:margintop+'px'});
}


/**
 * Resizes addmarker to fill the whole page
 * @param { Object } [obj] obj (addmarker)
 * @param { Object } [container] container
 */
$K.yg_fillContentSpace = function(obj, container) {
	obj.setStyle({height:'auto'});
	margintop = container.getStyle('margin-top');
	if ((margintop) && (margintop != "")) {
		margintop = parseInt(margintop.slice(0,-2));
	} else {
		margintop = 0;
	}
	contentheight = container.up(1).getHeight() - margintop;
	listheight = container.getHeight();
	fillheight = Math.round(contentheight - listheight);
	buttonheight = 0;
	if (obj.down()) buttonheight = obj.down().getHeight();
	if (fillheight > 0) obj.setStyle({height:(fillheight+buttonheight)+'px'});
}


/**
 * Called when starting to resize the window. Needs to be binded to the window-object.
 */
$K.yg_boxResizeStart = function() {

	$(this.id).appendChild(tmpdiv=document.createElement('div'));
	tmpdiv.className="resizeghost";
	tmpdiv.id="ywindowghost";

	dragcorner=$(this.id+'_dragcorner');

	if ($(this.id).descendantOf('maincontainer')) {
		tmparrx = $('maincontainer').childElements();
		tmpwid = 0;
		tmphei = 0;
		for (var k = 0; k < tmparrx.length; k++) {
			if ((tmparrx[k].id != this.id) && (tmparrx[k].getStyle('display') != 'none')) {
				//tmpwid += tmparrx[k].getWidth();
				tmphei += tmparrx[k].getHeight();
				if (k == 0) { tmpwid = 360; } else { tmpwid = 620; }
			}
		}

		traceheight = $('toolbar').getHeight();

		if ($('maincontainer').hasClassName('mk_horizontal')) {
			// horizontal
			this.maxwidth = this.minwidth = document.viewport.getWidth() - 7;
			this.maxheight = Math.round(document.viewport.getHeight() / 4 * 3) - $K.windowTopDiff - traceheight;
		} else {
			this.maxwidth = document.viewport.getWidth() - tmpwid - 7;
			if (Prototype.Browser.IE) tmpdiv.maxwidth -= 2;
			this.maxheight = this.minheight = document.viewport.getHeight() - $K.windowTopDiff - traceheight;
		}

} else {
		this.maxwidth = 0;
		this.maxheight = 0;
	}
}


/**
 * Called when finishing resizing of the window. Needs to be binded to the window-object.
 */
$K.yg_boxResizeEnd = function() {
	dragcorner=$(this.id+'_dragcorner');
	base=$(this.id);
	ghost = $('ywindowghost');

	dragpos=Position.page(dragcorner);
	basepos=Position.page(base);
	finalwidth=dragpos[0]-basepos[0]+26;
	finalheight=dragpos[1]-basepos[1]+13;

	if (finalwidth<=this.minwidth) finalwidth=this.minwidth;
	if ((finalwidth>=this.maxwidth) && (this.maxwidth != 0)) finalwidth=this.maxwidth;
	if (finalheight<=this.minheight) finalheight=this.minheight;
	if ((finalheight>=this.maxheight) && (this.maxheight != 0))finalheight=this.maxheight;

	if (ghost) {
		if ((finalwidth>ghost.maxwidth) && (ghost.maxwidth != 0)) finalwidth=ghost.maxwidth;
		if ((finalheight>ghost.maxheight) && (ghost.maxheight != 0)) finalheight=ghost.maxheight;
	}

	if ($('ywindowghost')) base.removeChild($('ywindowghost'));

	this.init(finalwidth,finalheight,false,false);

	if ($K.windows[this.boundWindow]) {
		if ($('maincontainer').hasClassName('mk_horizontal')) {
			$K.windows[this.boundWindow].resizeCollapsed();
			$K.windows[this.id].resizeCollapsed();
			$K.windows[this.boundWindow].init(finalwidth, (document.viewport.getHeight() - finalheight - $K.windowTopDiff - $('toolbar').getHeight() - $K.winhorizontalDiff),false,false);
		} else {
			$K.windows[this.boundWindow].init((document.viewport.getWidth() - 7 - finalwidth),finalheight,false,false);
		}
	}

	dragcorner.setStyle({left:'0px'});
	dragcorner.setStyle({top:'0px'});

	// Check if onResize-Handler was set
	if (typeof this.onResize == 'function') {
		this.onResize(this);
	}
}


/**
 * Called while resizing the window. Needs to have the window-object binded.
 */
$K.yg_boxResize = function() {
	base=$(this.id);
	dragcorner=$(this.id+'_dragcorner');

	dragpos=Position.page(dragcorner);
	basepos=Position.page(base);
	ghostwidth=dragpos[0]-basepos[0]+5;
	ghostheight=dragpos[1]-basepos[1]+5;
	ghost=$('ywindowghost');

	if (ghostwidth<(this.minwidth-22)) ghostwidth=this.minwidth-22;
	if ((ghostwidth>(this.maxwidth-22)) && (this.maxwidth != 0)) ghostwidth=this.maxwidth-22;
	if (ghostheight<(this.minheight-5)) ghostheight=this.minheight-5;
	if ((ghostheight>(this.maxheight-5)) && (this.maxheight != 0)) ghostheight=this.maxheight-5;
	if ((ghostwidth>ghost.maxwidth-22) && (ghost.maxwidth != 0)) ghostwidth=ghost.maxwidth-22;
	if ((ghostheight>ghost.maxheight-5) && (ghost.maxwidth != 0)) ghostheight=ghost.maxheight-5;

	ghost.setStyle({width:ghostwidth+'px'});
	ghost.setStyle({height:ghostheight+'px'});
}


/**
 * Called when mainwindow is resized
 */
$K.yg_windowResized = function() {
	$K.maincontwid = document.viewport.getWidth() - 2;
	if (Prototype.Browser.IE) $K.maincontwid -= 4;
	if ($K.maincontwid < $K.panelminwidth) $K.maincontwid = $K.panelminwidth;

	tmparrx = $('maincontainer').childElements();
	tmparrwins = new Array();
	tmparrsizes = new Array();
	num = 0;
	for (var k = 0; k < tmparrx.length; k++) {
		if (tmparrx[k].getStyle('display') != 'none') {
			tmparrwins.push(tmparrx[k]);
			tmparrsizes[num] = new Array();
			tmparrsizes[num][0] = 0;
			tmparrsizes[num][1] = 0;
			num++;
		}
	}

	var tmpwid = 0;
	if ($('toolbar')) { traceheight = $('toolbar').getHeight(); } else { traceheight = 0; }
	for (var k = 0; k < tmparrwins.length; k++) {
		if (($K.windows[tmparrwins[k].id]) /*&& ($K.windows[tmparrwins[k].id].boxheight > (document.viewport.getHeight() - $K.windowTopDiff - traceheight))*/) {
			tmparrsizes[k][1] = document.viewport.getHeight() - $K.windowTopDiff - traceheight;
			$K.panelheight = document.viewport.getHeight() - $K.windowTopDiff - traceheight;
		}
	}

	var tmpwidvar = 0;
	var tmpheivar = 0;
	for (var k = 0; k < tmparrwins.length; k++) {
		if ((tmparrwins[k].getStyle('display') != 'none') && ($K.windows[tmparrwins[k].id])) {
			tmparrsizes[k][0] = $K.windows[tmparrwins[k].id].boxwidth;
			tmpwidvar += $K.windows[tmparrwins[k].id].boxwidth;
			tmpheivar += $K.windows[tmparrwins[k].id].boxheight;
		} else if (!($K.windows[tmparrwins[k].id])) {
			return false;
		}
	}

	if ($('maincontainer').hasClassName('mk_horizontal')) {
		// horizontal, startpage
		for (var k = 0; k < tmparrwins.length; k++) {
			if (tmparrwins[k].getStyle('display') != 'none') {
				if (($K.windows[tmparrwins[k].id]) /*&& ($K.windows[tmparrwins[k].id].boxwidth > $K.maincontwid)*/) {
					tmparrsizes[k][0] = $K.maincontwid - 5;
				} else if (!($K.windows[tmparrwins[k].id])) {
					// IE7 fix
					tmparrsizes[k][0] = $K.maincontwid - 5;
				}
			}
		}
		//if (tmpheivar > (document.viewport.getHeight() - $K.windowTopDiff - traceheight - $K.winhorizontalDiff)) {
			for (var k = 0; k < tmparrwins.length; k++) {
				tmparrsizes[k][1] = Math.round((document.viewport.getHeight() - $K.windowTopDiff - traceheight - $K.winhorizontalDiff)/tmparrwins.length);
			}
			$K.panelheight = document.viewport.getHeight() - $K.windowTopDiff - traceheight - $K.winhorizontalDiff;
		//}
		//if (($K.panelwidth[0] + $K.panelwidth[1]) > ($K.maincontwid - 5)) {
			diff = Math.round((($K.panelwidth[0] + $K.panelwidth[1]) - ($K.maincontwid - 5))/2);
			$K.panelwidth[0] -= diff;
			$K.panelwidth[1] -= diff;
		//}

	} else {
		// vertical
		//if (tmpwidvar > $K.maincontwid) {
			diff = tmpwidvar - ($K.maincontwid - 5);

			if (tmparrsizes.length > 1) {
				tmparrsizes[1][0] -= diff;
				diff = 0;
				if (tmparrsizes[1][0] < 620) {
					diff = 620 - tmparrsizes[1][0];
					tmparrsizes[1][0] = 620;
				}
				tmparrsizes[1][1] = $K.panelheight;
			}
			if (tmparrsizes.length > 0) {
				tmparrsizes[0][0] -= diff;
				diff = 0;
				if (tmparrsizes[0][0] < 360) {
					diff = 360 - tmparrsizes[0][0];
					tmparrsizes[0][0] = 360;
				}
				tmparrsizes[0][1] = $K.panelheight;
			}
		//}
		}

	for (var k = 0; k < tmparrwins.length; k++) {
		if ((tmparrsizes[k][0] > 0) || (tmparrsizes[k][1] > 0)) {
			if ($K.windows[tmparrwins[k].id]) {

				if (tmparrsizes[k][0] == 0) tmparrsizes[k][0] = $K.windows[tmparrwins[k].id].boxwidth;
				if (tmparrsizes[k][1] == 0) tmparrsizes[k][1] = $K.windows[tmparrwins[k].id].boxheight;

				$K.windows[tmparrwins[k].id].init(tmparrsizes[k][0], tmparrsizes[k][1], false, false);
			}
		}
	}

	// check centered windows loginwindow
	var tmparr = $('logincontainer').immediateDescendants();
	tmparr.each(function(item) {
		$K.yg_centerWindow($(item));
	});

	$('maincontainer').setStyle({width:($K.maincontwid)+'px'});
	$K.yg_checkStatusBar();
}


/**
 * Called onLoad
 */
$K.yg_initWindow = function() {
	$K.maincontwid = document.viewport.getWidth() - 2;
	if ($K.maincontwid < $K.panelminwidth) $K.maincontwid = $K.panelminwidth;
	if (Prototype.Browser.IE) $K.maincontwid -= 2;

	$('maincontainer').setStyle({width:$K.maincontwid+'px'});

	if (document.viewport.getWidth() < $K.panelminwidth) {
		panwidth = $K.panelminwidth;
	} else {
		panwidth = $K.maincontwid - 5;
	}

	$K.panelwidth[0] = Math.round(panwidth/3);
	if ($K.panelwidth[0] < 360) $K.panelwidth[0] = 360;
	$K.panelwidth[1] = panwidth - $K.panelwidth[0];
	if ($K.panelwidth[1] < 620) $K.panelwidth[1] = 620;
	/*if ($K.panelwidth[1] > 918) {
		$K.panelwidth[1] = 918;
		pandiff = panwidth - $K.panelwidth[0] - $K.panelwidth[1];
		$K.panelwidth[0] += pandiff;
		if ($K.panelwidth[0] > 447) $K.panelwidth[0] = 447;
	}*/
	$K.panelheight = document.viewport.getHeight() - $K.windowTopDiff - $('toolbar').getHeight();

	window.onresize = function() {
		$K.yg_windowResized();
		$K.yg_centerPreview();
	}
}


/**
 * Opens/closes treeresult-Panels
 * @param { Object } [obj] The panel which should be opened/closed
 */
$K.yg_treeresultSwap = function(obj) {
	obj=$(obj);
	if (obj.hasClassName('opened')) {
		obj.removeClassName('opened');
		obj.addClassName('closed');
		obj.next().setStyle({display:'none'});
	} else {
		obj.removeClassName('closed');
		obj.addClassName('opened');
		obj.next().setStyle({display:'block'});
	}
	$K.windows[obj.up('.ywindow').id].refresh(obj);
}


/**
 * Updates the width of the window content.
 * @param { Object } [obj] Window-Div to update.
 */
$K.yg_updateInnerContent = function(obj) {

	if (Object.isUndefined(obj))
		return;

	obj.setStyle({width:'auto'});
	objdim=obj.getDimensions();

	boxobj=obj.up();
	boxdim=boxobj.up().getDimensions();

	innerwidth=obj.up(2).getWidth();

	if (objdim.height>boxdim.height) {
		tmpwidth=innerwidth-9;
	} else {
		tmpwidth=innerwidth;
	}

	// subtract 2 pixels if sortablelist
	if (obj.hasClassName('sortablelist')) {
		tmpwidth -= 2;
	}

	boxobj.setStyle({width:tmpwidth+'px'});

	// Set dropdown-width in tree-view
	if (($(obj)) && ($(obj).up(1).next())) {

		tmparr=$(obj).up(1).next().childElements();

		for (i=0;i<tmparr.length;i++) {
			tmparr[i].setStyle({width:tmpwidth+'px'});
		}
	}

	if (obj.hasClassName('sortablelist')) {
		obj.setStyle({width: (tmpwidth)+'px'});
	} else {
		obj.setStyle({width: obj.getWidth()+'px'});
	}
}


/**
 * Function for initialization of a dialog tree.
 * @param { Element/String } [obj] Id/Element of the list to initialize.
 */
$K.yg_initDialogTree = function(obj) {
	obj=$(obj);

	swid=obj.up().getWidth()+1;
	shei=obj.up().getHeight()+3;

	obj.setStyle({width:swid+'px'});
	obj.setStyle({height:shei+'px'});
	obj.setStyle({marginTop:'-1px'});
	obj.setStyle({marginLeft:'-1px'});

	tmpcont=obj.down();

	tmpcont.setStyle({width:swid+'px'});
	tmpcont.setStyle({height:shei+'px'});

	tmplist=tmpcont.down();

	if (tmplist.getHeight()>shei) {
		tmplist.setStyle({width:(swid-$K.yg_scrollObjAttr.scrollBtnVWidth)+'px'})
	} else {
		tmplist.setStyle({width:'100%'});
	}

}


/**
 * Function for updating the scrollbars/cntwidth of the dialog tree.
 * @param { Element/String } [obj] Id/Element of the list to initialize.
 */
$K.yg_updateContentArea = function(obj) {
	swid=obj.up().getWidth()+1;
	shei=obj.up().getHeight()+3;
	tmpcont=obj.down();
	tmplist=tmpcont.down();
	if (tmplist.getHeight()>shei) {
		tmplist.setStyle({width:(swid-$K.yg_scrollObjAttr.scrollBtnVWidth)+'px'})
	} else {
		tmplist.setStyle({width:'100%'});
	}
	$K.windows[obj.id].refresh(obj);
}


/**
 * Function used to refresh window-contents when objects are updated
 * @param { String } [yg_id] yg_id of the object
 */
$K.yg_clearRefresh = function(yg_id) {
	for (var i in $K.windows) {
		if ($K.windows[i].yg_id == yg_id) {

			// if versions or publishing tab visible or file
			if ( ($K.windows[i].tab == "VERSIONS") ||
				 ($K.windows[i].tab == "PUBLISHING") ||
				 (yg_id.endsWith('-file') && ($K.windows[i].tab != "PREVIEW"))) {
				for (var k=0;k < $K.windows[i].tabs.elements.length;k++) {
					if ($K.windows[i].tabs.elements[k].NAME == $K.windows[i].tab) {
						$K.windows[i].tabs.select(k);
					}
				}
			}

		}
	}
}


/**
* Refreshes the window after the page name has changed
* @param { String } [yg_type] The yg_type of the relevant window.
* @param { String } [yg_id] The yg_id of the relevant window.
* @param { String } [yg_property] The yg_property of the relevant window.
* @function
* @name $K.yg_refreshWin
*/
$K.yg_refreshWin = function(yg_type, yg_id, yg_property) {

	// "Garbage-Collection"
	$K.yg_cleanLookupTable();

	// Change all elements with this id and matching yg_property
	if ($K.yg_idlookuptable[yg_id])
	for (var i=0; i < $K.yg_idlookuptable[yg_id].length; i++) {
		if ( ($K.yg_idlookuptable[yg_id][i].yg_property == yg_property) &&
		 	 ($K.yg_idlookuptable[yg_id][i].yg_type == yg_type) ) {

			// Normal HTML-Element
			if ($K.yg_idlookuptable[yg_id][i].innerHTML) {
				if (!$K.yg_idlookuptable[yg_id][i]) {
					return;
				}
				var winRef = $K.yg_idlookuptable[yg_id][i].up('.ywindow').id;

				if ($K.windows[winRef] && ($K.windows[winRef].basetype!='tree')) {
					$K.windows[winRef].setCaption( $(winRef+'_title').innerHTML, yg_type );
					$K.windows[winRef].init(undefined,undefined,false,true,0);
				}
			}

			// Tree item
			if (($K.yg_idlookuptable[yg_id][i].capt != undefined) && ($($K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i])))) {
				$K.scrollbars[$($K.yg_getTreeReference($K.yg_idlookuptable[yg_id][i])).up('.ywindow').id].setBarSize();
			}

		}

	}

}


/**
* Show column2 bottom
* @function
* @param { String } [winid] window id.
*/
$K.yg_showColumn2Bottom = function(winid) {
	if ($(winid+"_column2bottom")) {
		$(winid+"_column2bottom").setStyle({display: 'block'});
		$K.windows[winid].init(undefined,undefined,false,true,0);
	}
}


/**
* Hide column2 bottom
* @function
* @param { String } [winid] window id.
*/
$K.yg_hideColumn2Bottom = function(winid) {
	if ($(winid+"_column2bottom")) {
		$(winid+"_column2bottom").setStyle({display: 'none'});
		$K.windows[winid].init($K.windows[winid].boxwidth,$K.windows[winid].boxheight,false,false,0);
	}
}


/**
* Cleans the global window-array
* @function
* @name $K.yg_cleanWindows
*/
$K.yg_cleanWindows = function() {
	$H($K.windows).each(function(win_item){
		// Check if it is really a window
		if (typeof win_item[1] == 'object') {
			var checkWinObj = win_item[1];
			var checkWin = $(checkWinObj.id);

			if ( (checkWinObj.minimized !== true) && (checkWin) && (checkWin.style.display == 'none') ) {
				checkWinObj.remove();
			}
		}
	});
}


/**
* Checks for open windows with the specified ygId as "openerYgId"
* @function
* @param { String } [ygId] YgId
* @param { String } [ygType] YgType
* @param { Function } [callBack] A callback to call before showing the confirm-dialog
* @name $K.yg_checkOpenWindows
*/
$K.yg_checkOpenWindows = function(ygId, ygType, options) {

	if (!ygId) {
		if (options && typeof options.onSuccess == 'function') {
			options.onSuccess();
		}
		return;
	}
	var isNotBlocked = true;
	var showWarning = false;
	var blockedWindows = new Array();
	for (winID in $K.windows) {
		if ($K.windows[winID].openerYgId == ygId) {
			isNotBlocked = false;
			blockedWindows.push(winID);

			$K.log( 'YG_TYPE is:', $K.windows[winID].yg_type, $K.Log.WARN );

			if ( ($K.windows[winID].yg_type!='navigation') &&
				 ($K.windows[winID].yg_type!='template') &&
				 ($K.windows[winID].yg_type!='filefolder') &&
				 ($K.windows[winID].yg_type!='cblock') &&
				 ($K.windows[winID].yg_type!='tag') &&
				 ($K.windows[winID].yg_type!='view') &&
				 ($K.windows[winID].yg_type!='insertcontent') ) {
				showWarning = true;
			}
		}
	}

	if (!isNotBlocked) {
		if (options && typeof options.onBeforeShow == 'function') {
			options.onBeforeShow();
		}

		var processBlockedWindows = function() {
			blockedWindows.each(function(blockedWinId){

				if ( ($K.windows[blockedWinId].type == 'dialog') &&
					 (
						( ($K.windows[blockedWinId].tab == 'FILES_TREE') && ($K.windows[blockedWinId].yg_type == 'filefolder') ) ||
						( ($K.windows[blockedWinId].tab == 'FILES_TREE') && ($K.windows[blockedWinId].yg_type == 'file') ) ||
						( ($K.windows[blockedWinId].tab == 'CONTENTBLOCKS_TREE') && ($K.windows[blockedWinId].yg_type == 'cblock') ) ||
						( ($K.windows[blockedWinId].tab == 'TAGS_TREE') && ($K.windows[blockedWinId].yg_type == 'tag') ) ||
						( ($K.windows[blockedWinId].tab == 'TEMPLATES_TREE') && ($K.windows[blockedWinId].yg_type == 'template') ) ||
						($K.windows[blockedWinId].yg_type == 'insertcontent') ||
						($K.windows[blockedWinId].yg_type == 'view')
					 )
				   ) {
					if ($(blockedWinId+'_bottom') && $(blockedWinId+'_bottom').down()) {
						$(blockedWinId).addClassName('mk_noexec');
						$(blockedWinId+'_bottom').down().remove();
						$K.windows[blockedWinId].refresh();
					}
					if ($(blockedWinId+'_bottom_'+$K.windows[blockedWinId].tab)) {
						$(blockedWinId).addClassName('mk_noexec');
						$(blockedWinId+'_bottom_'+$K.windows[blockedWinId].tab).down().remove();
						$K.windows[blockedWinId].refresh();
					}
					$K.windows[winID].openerYgId = null;
				} else {
					$K.windows[blockedWinId].remove(true);
				}
			});
			if (options && typeof options.onSuccess == 'function') {
				options.onSuccess();
			}
		}

		if (showWarning) {
			$K.yg_promptbox($K.TXT('TXT_WARNING'), $K.TXT('TXT_CHANGES_NOT_SAVED'), 'standard', processBlockedWindows, Prototype.emptyFunction);
		} else {
			processBlockedWindows();
		}

	} else {
		if (options && typeof options.onSuccess == 'function') {
			options.onSuccess();
		}
	}
}
