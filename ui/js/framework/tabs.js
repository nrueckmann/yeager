/**
 * @fileoverview Supplies the tab object and functions to handle and navigate the tabs.
 */


/**
 * The tab object
 * @class This is the tab-object
 * @param { Element } [parent] Id of the div. The tabs will be rendered as child of this div.
 * @param { Array of String and Int } [elements] A multidimensional array of all tab-elements. Every element
 * has to have a title(String), sel(Boolean): 0 or 1 depending on if the tab-element is selected, workflow(Boolean): 0 or 1
 * renders the workflow-marker, cache(Boolean): 0 or 1 defines if tab will be refreshed with every impression or not
 * @param { String } [id] Id of the window object.
 */
$K.tabs = function(parent,elements,id,wndobj) {
	this.id=id+'tabs';

	this.elements=elements;

	this.baseid=id;
	this.wndobj=wndobj;

	/**
	 * Sets the margins for different browsers.
	 * @type Int
	 */
	this.marginright = 45;

	$(parent).appendChild(base = document.createElement('div'));
	base.className='tabs';
	base.id=this.id;

	base.appendChild(tmpdiv=document.createElement('div'));
	tmpdiv.className='taboverflow';
	tmpdiv.id=this.id+'taboverflow';

	tmpdiv.appendChild(tmpdivcont=document.createElement('div'));
	tmpdivcont.className='tabcontainer';
	tmpdivcont.id=this.id+'tabcontainer';

	for (i=0;i<elements.length;i++) {
		tmpdivcont.appendChild(tmpdiv=document.createElement('div'));
		if (elements[i]["WORKFLOW"]=="1") {
			tmpdiv.className='tabwf';
		} else {
			tmpdiv.className='tab';
		}
		tmpdiv.appendChild(tmpa=document.createElement('a'));


		if (elements[i]["SELECTED"]=="1") tmpa.className='sel';
		$(tmpa).observe('click', (function(id, i) {
			$K.windows[id].tabs.select(i);
		}).bind(tmpa, id, i) );

		tmpa.id=this.id+"tabbt"+i;
		tmpa.appendChild(tmpnobr=document.createElement('nobr'));
		tmpnobr.appendChild(document.createTextNode(elements[i]["TITLE"]));
		this.elements[i]["WIDTH"]=$(tmpa).getWidth();
	}
	base.appendChild(tmpdiv=document.createElement('div'));
	tmpdiv.className='tabdown';
	tmpdiv.id=this.id+'tabdown';
	tmpdiv.style.display='none';
	tmpdiv.appendChild(tmpa=document.createElement('a'));
	tmpa.setAttribute('href','javascript:$K.windows["'+id+'"].tabs.showContext();');
	tmpa.setAttribute('onfocus','this.blur();');
	base.appendChild(tmpdiv=document.createElement('div'));
	tmpdiv.className='tableft';
	tmpdiv.id=this.id+'tableft';
	tmpdiv.style.display='none';
	tmpdiv.appendChild(tmpa=document.createElement('a'));
	tmpa.setAttribute('href','javascript:$K.windows["'+id+'"].tabs.move("left");');
	tmpa.setAttribute('onfocus','this.blur();');

	base.appendChild(tmpdiv=document.createElement('div'));
	tmpdiv.className='tabright';
	tmpdiv.id=this.id+'tabright';
	tmpdiv.style.display='none';
	tmpdiv.appendChild(tmpa=document.createElement('a'));
	tmpa.setAttribute('onclick','$K.windows["'+id+'"].tabs.move("right");');
	tmpa.setAttribute('onfocus','this.blur();');

	base.appendChild(tmpdiv=document.createElement('div'));
	tmpdiv.className='tabroundleft';
	tmpdiv.id=this.id+'tabroundleft';

	base.appendChild(tmpdiv=document.createElement('div'));
	tmpdiv.className='tabroundright';
	tmpdiv.id=this.id+'tabroundright';

	base.appendChild(tmpdiv=document.createElement('div'));
	tmpdiv.className='tabnavnone';
	tmpdiv.id=this.id+'tabnavnone';

	$(parent).appendChild(tmpdiv=document.createElement('div'));
	tmpdiv.className='tabcontext';
	tmpdiv.id=this.id+'tabcontext';
	tmpdiv.onmouseup = function() {
		this.hide();
	}

	for (var i=0;i<elements.length;i++) {
		tmpdiv.appendChild(tmpa=document.createElement('a'));
		tmpa.setAttribute('onclick','$K.windows["'+id+'"].tabs.select('+i+');');
		tmpa.appendChild(document.createTextNode(elements[i]["TITLE"]));
	}

	this.init();

	selvar = false;
	for (var i=0;i<this.elements.length;i++) {
		if (this.elements[i]["SELECTED"]=="1") {
			this.select(i, null, true);
			selvar = true;
		}
	}
	if (!selvar) {
		this.select(0, null, true);
	}

	if (elements.length == 1) {
		$(base.id).hide();
	}

}


/**
 * Initialises the tabs
 */
$K.tabs.prototype.init = function() {
	base=$(this.id);
	this.tabdown=$(this.id+'tabdown');
	this.tabright=$(this.id+'tabright');
	this.tableft=$(this.id+'tableft');
	this.tabroundright=$(this.id+'tabroundright');
	this.tabroundleft=$(this.id+'tabroundleft');
	this.tabnavnone=$(this.id+'tabnavnone');
	this.taboverflow=$(this.id+'taboverflow');
	this.tabcontext=$(this.id+'tabcontext');
	this.tabcontainer=$(this.id+'tabcontainer');
	this.ywindowouter=$(this.baseid+'_ywindowouter')

	this.tabdown.hide();
	this.tabright.hide();
	this.tableft.hide();

	tabdivs = this.tabcontainer.immediateDescendants();

	// hide tabs if folder
	numvisibletabs = 0;
	for (var k = 0; k < this.elements.length; k++) {
		if (($K.windows[this.baseid]) && ($K.windows[this.baseid].folderselected == 1)) {
			if ((this.elements[k]["FOLDER"] == "0") || (this.elements[k]["TRASHCAN"])) {
				tabdivs[k].setStyle({display: 'none'});
			} else if (this.elements[k]["FOLDER"] == "2") {
				tabdivs[k].setStyle({display: 'block'});
				numvisibletabs++;
			} else if (this.elements[k]["FOLDER"] == "1") {
				numvisibletabs++;
			}
		} else if (($K.windows[this.baseid]) && ($K.windows[this.baseid].trashcanselected == 1)) {
			numvisibletabs = 1;
		} else {
			if ((this.elements[k]["FOLDER"] == "0") && (!(this.elements[k]["TRASHCAN"]))) {
				tabdivs[k].setStyle({display: 'block'});
				numvisibletabs++;
			} else if ((this.elements[k]["FOLDER"] == "2") || (this.elements[k]["TRASHCAN"])) {
				tabdivs[k].setStyle({display: 'none'});
			} else if ((this.elements[k]["FOLDER"] == "1") && (!(this.elements[k]["TRASHCAN"]))) {
				numvisibletabs++;
			}
		}
	}

	if (numvisibletabs < 2) {
		$(this.baseid).addClassName("hidetabs");
	} else if ($K.windows[this.baseid]) {
		$(this.baseid).removeClassName("hidetabs");
	}

	rightpos=0;

	basewidth=this.wndobj.boxwidth;

	tabroundleftwidth=15;
	tabroundrightwidth=15;

	this.twidth=0;
	for (i=0;i<this.elements.length;i++)  {
		if (tabdivs[i].visible()) this.twidth+=this.elements[i]["WIDTH"];
	}
	this.twidth += 2;

	this.tabcontainer.setStyle({width: this.twidth+'px'});

	if (this.twidth>basewidth-this.marginright-tabroundleftwidth/2-tabroundrightwidth/2) {
		tableftwidth=21;
		tabrightwidth=21;
		tabdownwidth=21;
		this.tabdown.show();
		downpos=basewidth-this.marginright-tabdownwidth;
		rightpos=downpos-tabrightwidth-1;
		leftpos=tableftwidth+tabroundleftwidth+1;
		leftroundpos=tableftwidth+1;
		rightroundpos=rightpos-tabroundrightwidth-1;
		this.overflowwidth=basewidth-this.marginright-tableftwidth/2-1-tabrightwidth/2-1-tabdownwidth-1-tabroundleftwidth-tabroundrightwidth-tableftwidth;
		this.tabright.setStyle({left: rightpos+'px'});
		this.tabdown.setStyle({left: downpos+'px'});
		this.tabnavnone.setStyle({display: 'block'});
		this.tabnavnone.setStyle({left: '0px'});
		this.tableft.hide();
		this.tabright.show();
	} else {
		this.tabdown.hide();
		this.tabright.hide();
		this.tableft.hide();
		leftpos=tabroundleftwidth;
		leftroundpos=0;
		rightroundpos=this.twidth;
		this.overflowwidth=this.twidth - tabroundrightwidth;
		this.tabnavnone.setStyle({display: 'none'});
	}
	this.tabcontainer.setStyle({left: -8+'px'});
	this.tabroundleft.setStyle({left: leftroundpos+'px'});
	this.tabroundright.setStyle({left: rightroundpos+'px'});
	this.taboverflow.setStyle({left: leftpos+'px'});

	if (this.overflowwidth>0) this.taboverflow.setStyle({width: this.overflowwidth+'px'});

	this.tabcontext.setStyle({display: 'none'});
	this.tabcontext.setStyle({top: '8px'});
	this.tabcontext.setStyle({left: (rightpos-142)+'px'});

}


/**
 * Shows the context menu
 */
$K.tabs.prototype.showContext = function() {
	// check if folder selected and hide unrelated stuff
	tabdivs = this.tabcontext.immediateDescendants();
	for (var k = 0; k < this.elements.length; k++) {
		if (($K.windows[this.baseid]) && ($K.windows[this.baseid].folderselected == 1)) {
			if ((this.elements[k]["FOLDER"] == "0") || (this.elements[k]["TRASHCAN"])) {
				tabdivs[k].setStyle({display: 'none'});
			} else if (this.elements[k]["FOLDER"] == "2") {
				tabdivs[k].setStyle({display: 'block'});
				numvisibletabs++;
			} else if (this.elements[k]["FOLDER"] == "1") {
				numvisibletabs++;
			}
		} else if (($K.windows[this.baseid]) && ($K.windows[this.baseid].trashcanselected == 1)) {
			numvisibletabs = 1;
		} else {
			if ((this.elements[k]["FOLDER"] == "0") && (!(this.elements[k]["TRASHCAN"]))) {
				tabdivs[k].setStyle({display: 'block'});
				numvisibletabs++;
			} else if ((this.elements[k]["FOLDER"] == "2") || (this.elements[k]["TRASHCAN"])) {
				tabdivs[k].setStyle({display: 'none'});
			} else if ((this.elements[k]["FOLDER"] == "1") && (!(this.elements[k]["TRASHCAN"]))) {
				numvisibletabs++;
			}
		}
	}
	this.tabcontext.setStyle({display: 'block'});
	$K.bCloser[this.tabcontext.id] = $K.yg_clickCloser.bindAsEventListener(this.tabcontext);
	Event.observe(document,'click',$K.bCloser[this.tabcontext.id]);
}


/**
 * Moves the tabbar one item left or right
 * @param { String } [dir] must be 'left' or 'right'
 */
$K.tabs.prototype.move = function(dir) {

	minpos = -8;
	maxpos = this.overflowwidth-this.twidth+2;

	tmpos = this.tabcontainer.positionedOffset();
	tmpos = tmpos[0];

	firstpos=false;

	if (tmpos==minpos) {
		firstpos=0;
	} else {
		tmpwid=0;
		for (i=0;i<this.elements.length;i++) {
			tmpwid+=this.elements[i]["WIDTH"];
			if ((tmpwid*-1)==tmpos) firstpos=i+1;
		}
		if (firstpos==false) {
			tmpwid=0;
			for (i=(this.elements.length-1);i>-1;i--) {
				tmpwid+=this.elements[i]["WIDTH"];
				if (tmpwid<this.overflowwidth) firstpos=i;
			}
		}
	}

	atend=false;

	if (dir=="right") {
		newpos=tmpos-this.elements[firstpos]["WIDTH"];
		if (newpos<maxpos) {
			newpos=maxpos;
			atend=true;
		}
		firstpos++;
		this.tabcontainer.setStyle({left: newpos+'px'});
	}
	if (dir=="left") {
		newpos=0;
		firstpos--;
		if (tmpos == maxpos) firstpos--;
		for (i=0;i<firstpos;i++) {
			newpos-=this.elements[i]["WIDTH"];
		}
		if (newpos == 0) newpos = minpos;

		this.tabcontainer.setStyle({left: newpos+'px'});
	}
	if (firstpos==0) {
		this.tabnavnone.setStyle({display: 'block'});
		this.tabnavnone.setStyle({left: '0px'});
		this.tableft.hide();
		this.tabright.show();
	} else if (atend==true) {
		tmpos=$(this.id).getWidth()-this.marginright-this.tabdown.getWidth()-1-this.tabright.getWidth();
		this.tabnavnone.setStyle({display: 'block'});
		this.tabnavnone.setStyle({left: tmpos+'px'});
		this.tableft.show();
		this.tabright.hide();
	} else {
		this.tabnavnone.setStyle({display: 'none'});
		this.tableft.show();
		this.tabright.show();
	}

}


/**
 * Selects an element
 * @param { Int } [elem] num of the element to select
 * @param { Array } [params] extra parameters
 * @param { Boolean } [initload] initial select
 * @param { Boolean } [nolateonchange] indicates that no late onchange-event should be fired
 */
$K.tabs.prototype.select = function(elem, params, initload, nolateonchange) {
	// Save 'this'
	var which = this;

	// Check for open windows with this window as "parent"
	$K.yg_checkOpenWindows( $K.windows[this.baseid].yg_id, $K.windows[this.baseid].yg_type, {
		onBeforeShow: Prototype.emptyFunction,
		onSuccess: function() {
			if (!nolateonchange) {
				// Fire late events
				$K.yg_fireLateOnChange(false, $(which.baseid));
			}

			// Show activity indicators
			var bgPosX = (which.ywindowouter.offsetWidth/2)-16;
			var bgPosY = (which.ywindowouter.offsetHeight/2)-16;

			if ( (bgPosX<=0)||(bgPosY<=0) ) {
				bgPosX = 262;
				bgPosY = 241;
			}

			var act_timeout = 1;
			window.activity_indicator = window.setTimeout("$('"+which.baseid+"_ywindowinner').addClassName('tab_loading');$('"+which.baseid+"_ywindowinner').setStyle({backgroundPosition:'"+bgPosX+"px "+bgPosY+"px'});", act_timeout);

			if ($(which.id+'tabbt'+elem)) $(which.id+'tabbt'+elem).addClassName('sel');

			bottomdiff = 0;
			topdiff = 0;

			for (i=0;i<which.elements.length;i++) {

				tmpobj=$(which.id+"tabbt"+i);
				if (i != elem) tmpobj.removeClassName('sel');

				if ((i != elem) && (which.elements[i]["NAME"]!=undefined)) {

					// hide tab related stuff

					// hide content
					tmpobj=$(which.baseid+"_"+which.elements[i]["NAME"]);
					if (tmpobj) {
						tmpobj.hide();
						tmpobj.removeClassName("mk_activetab");
						if (which.elements[i]["CACHE"] != 1) tmpobj.innerHTML = "";
					}

					// hide head
					tmphead=$(which.baseid+"_head_"+which.elements[i]["NAME"]);
					if (tmphead) tmphead.hide();

					// hide pagedir
					tmppagedir=$(which.baseid+"_pagedir_"+which.elements[i]["NAME"]);
					if (tmppagedir) tmppagedir.hide();


					// hide head column2
					tmphead=$(which.baseid+"_column2head_"+which.elements[i]["NAME"]);
					if (tmphead) tmphead.hide();

					// hide bottom
					tmpbottom=$(which.baseid+"_bottom_"+which.elements[i]["NAME"]);
					if ((tmpbottom) && (tmpbottom.visible())) {
						tmpbottom.hide();
						bottomdiff += 16;
					}

					// hide bottom column2
					tmpbottom=$(which.baseid+"_column2bottom_"+which.elements[i]["NAME"]);
					if ((tmpbottom) && (tmpbottom.visible())) {
						tmpbottom.hide();
					}

					// hide special bottom
					tmpspcbottom=$(which.baseid+"_spcbottom_"+which.elements[i]["NAME"]);
					if ((tmpspcbottom) && (tmpspcbottom.visible())) {
						tmpspcbottom.hide();
					}

					// hide filter
					tmpfilter=$(which.baseid+"_filter_"+which.elements[i]["NAME"]);
					if ((tmpfilter) && (tmpfilter.visible())) {
						tmpfilter.hide();
					}

					// hide filter col2
					tmpfilter=$(which.baseid+"_column2filter_"+which.elements[i]["NAME"]);
					if ((tmpfilter) && (tmpfilter.visible())) {
						tmpfilter.hide();
					}

				} else if ((i == elem) && (which.elements[i]["NAME"]!=undefined)) {
					tmpfilter=$(which.baseid+"_filter_"+which.elements[i]["NAME"]);
					if ((tmpfilter) && (tmpfilter.visible())) {
						topdiff = tmpfilter.getHeight();
					}
				}
			}

			if (bottomdiff == 0) bottomdiff = 16 + $(which.baseid+"_bottom").getHeight();
			if ((Prototype.Browser.IE) && (bottomdiff == 32)) bottomdiff = 16;

			tmphei = which.wndobj.boxheight-which.wndobj.topheight-bottomdiff-topdiff;

			$(which.baseid+'_container').setStyle({height: tmphei+'px'});
			$(which.baseid+'_ywindowinner').setStyle({height: tmphei+'px'});

			// Clear column 2
			if ($(which.baseid+'_column2')) {
				$(which.baseid+'_column2innercontentinner').innerHTML = "";
				which.wndobj.init();
			} else {
				which.wndobj.refresh("col1");
			}

			// show itz
			if (which.elements[elem] && which.elements[elem]["NAME"]!=undefined) {
				tmpobj=$(which.baseid+"_"+which.elements[elem]["NAME"]);

				if (!(tmpobj)) {
					$(which.baseid+"_innercontent").appendChild(tmpobj=$(document.createElement('div')));
					tmpobj.id = which.baseid+"_"+which.elements[elem]["NAME"];
					tmpobj.addClassName('tabcontent');
					if (which.elements[elem]["CLASS"]) {
						tmpobj.addClassName(which.elements[elem]["CLASS"]);
					}
				}
				tmpobj.show();
				which.load(tmpobj,elem,params,initload);
			}
		}
	});
}


/**
 * Loads window content via AJAX
 * @param { Element } [targetdiv] the div to load data into
 * @param { Element } [elem] the tab element
 * @param { Array } [params] additional parameters
 */
$K.tabs.prototype.load = function(targetdiv,elem,params,initload) {
	var completed = function() {

		if (!$(this.baseid + "_" + this.elements[elem]["NAME"])) return;

		// head
		tmpheadid = this.baseid+"_head_"+this.elements[elem]["NAME"];
		tmphead = $(this.baseid + "_" + this.elements[elem]["NAME"]).down('.ywindowhead');

		if (tmphead) {
			if ((this.elements[elem]["LOADED"]!=true) || ((this.elements[elem]["CACHE"] == "0") && (this.elements[elem]["LOADED"]==true))) {
				if ($(tmpheadid)) $(tmpheadid).remove();
				$(this.baseid+"_headers").appendChild( tmphead );
				tmphead.id = tmpheadid;
			} else {
				tmphead.remove();
			}
		}
		if ($(tmpheadid)) $(tmpheadid).show();
		$K.yg_customAttributeHandler( tmphead );

		// pagedir
		tmppagedirid = this.baseid+"_pagedir_"+this.elements[elem]["NAME"];
		tmppagedir = false;
		if ($(this.baseid + "_" + this.elements[elem]["NAME"])) tmppagedir = $(this.baseid + "_" + this.elements[elem]["NAME"]).down('.ywindowpagedir');

		if (tmppagedir) {
			if (this.elements[elem]["LOADED"]!=true) {
				if ($(tmppagedirid)) $(tmppagedirid).remove();
				$(this.baseid+"_pagedirs").appendChild( tmppagedir );
				tmppagedir.id = tmppagedirid;
			} else {
				tmppagedir.remove();
			}
		}
		if ($(tmppagedirid)) {
			$(tmppagedirid).show();
			$(this.baseid + "_" + this.elements[elem]["NAME"]).setStyle({paddingBottom:($(tmppagedirid).getHeight()-2)+'px'});
		}
		$K.yg_customAttributeHandler( tmppagedir );

		// lock
		if ((this.wndobj.lockedByUser) && (this.wndobj.locking)) {
			this.wndobj.locked = true;
			$(this.wndobj.id).addClassName('mk_lock');
			$(this.wndobj.id+'_lock').writeAttribute('yg_id', this.wndobj.lockedByUser+'-user');
			$(this.wndobj.id+'_lock').onmouseover = function() {
				$K.yg_hoverUserHint(this);
			}
			$(this.wndobj.id+'_lock').onmouseout = function() {
				$K.yg_hideUserHint();
			}
			$(this.wndobj.id+'_lock').onclick = function() {
				$K.yg_hideUserHint();
				$K.yg_openUserInfo(this.readAttribute('yg_id').split('-')[0], this);
			}
		} else if (this.wndobj.locking) {
			this.wndobj.locked = false;
			$(this.wndobj.id).removeClassName('mk_lock');
		}

		// head column2

		tmpheadid = this.baseid+"_column2head_"+this.elements[elem]["NAME"];
		tmphead = $(this.baseid + "_" + this.elements[elem]["NAME"]).down('.ywindowheadcolumn2');

		if (tmphead!=undefined) {
			if (this.elements[elem]["LOADED"]!=true) {
				if ($(tmpheadid)) $(tmpheadid).remove();
				$(this.baseid+"_column2headers").appendChild( tmphead );
				tmphead.id = tmpheadid;
			} else {
				tmphead.remove();
			}
		}
		if ($(tmpheadid)) $(tmpheadid).show();
		$K.yg_customAttributeHandler( tmphead );
		$K.windows[this.baseid].submit = this.elements[elem].submit;

		// buttons
		tmpbuttondiv = $(this.baseid + "_" + this.elements[elem]["NAME"]).down('.ywindowbuttons');
		if (tmpbuttondiv) {
			curbuttons = $(this.baseid + "_buttons").immediateDescendants();
			for (var i = 0; i < curbuttons.length; i++) {
				if ((i > 1) && (i != (curbuttons.length-1))) {
					curbuttons[i].remove();
				}
			}
			tmpbuttons = tmpbuttondiv.down('tr').immediateDescendants();

			tmpbuttons.each(function(button) {
				curbuttons[curbuttons.length-1].insert({'before': button});
			});
			tmpbuttondiv.remove();
		}

		// bottom
		tmpbottomid = this.baseid + "_bottom_" + this.elements[elem]["NAME"];
		tmpbottom = $(this.baseid + "_" + this.elements[elem]["NAME"]).down('.ywindowbottom');
		if (tmpbottom!=undefined) {
			if (!(tmpbottom.descendantOf(this.baseid+"_bottom")) && !$(this.baseid).hasClassName('mk_noexec')) {
				if ($(tmpbottomid)) $(tmpbottomid).remove();
				$(this.baseid+"_bottom").appendChild( tmpbottom );
				tmpbottom.id = tmpbottomid;
			} else {
				tmpbottom.remove();
			}
		}
		if ($(tmpbottomid)) $(tmpbottomid).show();
		$K.yg_customAttributeHandler( tmpbottom );

		// bottom column2
		tmpbottomid = this.baseid + "_column2bottom_" + this.elements[elem]["NAME"];
		tmpbottom = $(this.baseid + "_" + this.elements[elem]["NAME"]).down('.ywindowbottomcolumn2');

		if (tmpbottom!=undefined) {
			if (!(tmpbottom.descendantOf(this.baseid+"_column2bottom"))) {
				if ($(tmpbottomid)) $(tmpbottomid).remove();
				$(this.baseid+"_column2bottom").appendChild( tmpbottom );
				tmpbottom.id = tmpbottomid;
			} else {
				tmpbottom.remove();
			}
		}
		if ($(tmpbottomid)) $(tmpbottomid).show();
		$K.yg_customAttributeHandler( tmpbottom );

		// special bottom
		tmpspcbottomid = this.baseid + "_spcbottom_" + this.elements[elem]["NAME"];
		tmpspcbottom = $(this.baseid + "_" + this.elements[elem]["NAME"]).down('.ywindowspecialbottom');
		if (tmpspcbottom!=undefined) {
			if (!(tmpspcbottom.descendantOf(this.baseid+"_spcbottom"))) {
				if ($(tmpspcbottomid)) $(tmpspcbottomid).remove();
				$(this.baseid+"_spcbottom").appendChild( tmpspcbottom );
				tmpspcbottom.id = tmpspcbottomid;
			} else {
				tmpspcbottom.remove();
			}
			tmpspcbottom.show();
		}
		if ($(tmpspcbottomid)) $(tmpspcbottomid).show();
		$K.yg_customAttributeHandler( tmpspcbottom );

		// filter
		tmpfilterid = this.baseid + "_filter_" + this.elements[elem]["NAME"];
		tmpfilter = $(this.baseid + "_" + this.elements[elem]["NAME"]).down('.ywindowfilter');

		if (tmpfilter!=undefined) {
			if (!(tmpfilter.descendantOf(this.baseid+"_filter"))) {
				if ($(tmpfilterid)) $(tmpfilterid).remove();
				$(this.baseid+"_filter").appendChild( tmpfilter );
				tmpfilter.id = tmpfilterid;
			}
		}
		if ($(tmpfilterid)) $(tmpfilterid).show();
		$K.yg_customAttributeHandler( tmpfilter );

		// filter column2
		tmpfilterid = this.baseid + "_column2filter_" + this.elements[elem]["NAME"];
		tmpfilter = $(this.baseid + "_" + this.elements[elem]["NAME"]).down('.ywindowfiltercolumn2');

		if (tmpfilter!=undefined) {
			if (!(tmpfilter.descendantOf(this.baseid+"_filtercolumn2"))) {
				if ($(tmpfilterid)) $(tmpfilterid).remove();
				$(this.baseid+"_column2filter").appendChild( tmpfilter );
				tmpfilter.id = tmpfilterid;
			}
		}
		if ($(tmpfilterid)) $(tmpfilterid).show();
		$K.yg_customAttributeHandler( tmpfilter );

		// win init etc.
		if (this.wndobj!=undefined) {
			this.wndobj.tab=this.elements[elem]["NAME"];
			this.wndobj.init(this.wndobj.boxwidth,this.wndobj.boxheight,false,true);
			targetdiv.setStyle({visibility: ''});
		}

		$K.yg_customAttributeHandler( $(this.baseid + "_" + this.elements[elem]["NAME"]) );
		$K.yg_cleanLookupTable();

		// Hide activity indicators
		if (window.activity_indicator) {
			if (typeof(window.activity_indicator)=='number') {
				window.clearTimeout(window.activity_indicator);
			}
			window.activity_indicator = undefined;
		}
		$(this.baseid+"_ywindowinner").removeClassName('tab_loading');  // big

		// Set this tab as selected
		this.selected = elem;
		// Set 'already loaded' flag

		this.elements[elem]["LOADED"] = true;

		if (params && (parseInt(params.refresh) == 1) && (typeof this.elements[elem].refresh == 'function')) {
			this.elements[elem].refresh();
		} else if (typeof this.elements[elem].init == 'function') {
			this.elements[elem].init();
		}
	}

	if ((this.elements[elem]["LOADED"]!=true) || ((this.elements[elem]["CACHE"] == "0") && (this.elements[elem]["LOADED"]==true))) {

		targetdiv.setStyle({visibility: 'hidden'});
		winno = this.wndobj.num;
		var ygid = '';
		var ygtype = '';
		var wid = '';

		if ((this.wndobj) && (this.wndobj.loadparams)) {
			loadparams = this.wndobj.loadparams;
		} else {
			loadparams = new Array();
		}

		if (this.wndobj!=undefined) {
			if (!loadparams["yg_id"]) ygid = this.wndobj.yg_id;
			if (!loadparams["yg_type"]) {
				ygtype = this.wndobj.yg_type;
			}
			wid =  this.wndobj.id;
		}
		if (ygid==undefined) ygid = '';

		window.xxx_time = new Date();

		if (this.elements[elem]["LOADED"] == true) {
			refreshvar = 1;
		} else {
			refreshvar = 0
		}

		var parameters = {win_no: winno, yg_id: ygid, yg_type: ygtype, wid: wid, refresh: refreshvar, initload: initload, us: document.body.id, lh: $K.yg_getLastGuiSyncHistoryId() };

		if ((this.wndobj) && (this.wndobj.loadparams)) {
			Object.extend(parameters, this.wndobj.loadparams);
		}

		this.yg_id = ygid;
		$K.tab_loading = ygid;

		if (params!=undefined) {
			Object.extend(parameters, params);
		}

		new Ajax.Updater(targetdiv.id, $K.appdir+"tab_"+this.elements[elem]["NAME"],
		{
			asynchronous: true,
			evalScripts: true,
			method: 'post',
			parameters: parameters,
			onComplete: (function() {
				window.setTimeout( completed.bind(this), 10);
			}).bind(this),
			onSuccess: function(transport, wid) {
				if (Ajax.currentRequests[wid+'_TAB_CONTENT'].transport.aborted) return;
			},
			onlyLatestOfClass: wid+'_TAB_CONTENT'
		});
	} else {
		(completed.bind(this))();
	}

	// right column
	if (this.wndobj.verticalsplit) {

		if (!wid) {
			wid = this.wndobj.id;
		}

		$(this.baseid+"_column2innercontentinner").addClassName('tab_loading');

		var completed2 = function() {

			$K.yg_customAttributeHandler( $(wid+"_column2innercontentinner") );
			$K.yg_cleanLookupTable();
			$(this.baseid+"_column2innercontentinner").removeClassName('tab_loading');  // big
			if (typeof this.elements[elem].initcontentright == 'function') this.elements[elem].initcontentright();
			this.wndobj.refresh("col2");

		}

		new Ajax.Updater($(wid+"_column2innercontentinner"), $K.appdir+"tab_"+this.elements[elem]["CONTENTRIGHT"],
		{
			asynchronous: true,
			evalScripts: true,
			method: 'post',
			parameters: parameters,
			onComplete: completed2.bind(this),
			onSuccess: function(transport, wid) {
				if (Ajax.currentRequests[wid+'_TAB_CONTENTRIGHT'].transport.aborted) return;
			},
			onlyLatestOfClass: wid+'_TAB_CONTENTRIGHT'
		});

	}

}
