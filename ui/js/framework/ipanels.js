/**
 * Opens/closes an iPanel  
 * @param { Object } [obj] The iPanel-div which should be opened/closed
 * @function
 * @name $K.yg_ipanelSwap
 */
$K.yg_ipanelSwap = function(obj) {
	if (($K.inherithover==false) && ($K.actionhover==false)) {
		tmpobjhead=obj.down('.panelheader').down();
		tmpobjcnt=obj.down('.panelcontent');
		if (tmpobjcnt.getStyle('display')=='none') {
			tmpobjcnt.setStyle({display:'inline'});
			tmpobjhead.removeClassName('closed');	
			tmpobjhead.addClassName('opened');	
			tmpobjhead.up().removeClassName('panelheaderclosed');
			tmpobjcnt.setStyle({display:'block'});
		} else {
			tmpobjcnt.setStyle({display:'none'});
			tmpobjhead.removeClassName('opened');	
			tmpobjhead.addClassName('closed');	
			tmpobjhead.up().addClassName('panelheaderclosed');
		}
		$K.windows[obj.up('.ywindow').id].refresh(obj);
		$K.yg_showActions(obj);
	}
}


/**
 * Switches the inherit-button of an iPanel  
 * @param { Object } [obj] Regarding iPanel-div.
 * @function
 * @name $K.yg_ipanelInherit
 */
$K.yg_ipanelInherit = function(obj) {
	if (obj.hasClassName('inherit')) {
		obj.removeClassName('inherit');
		obj.addClassName('noinherit');	
	} else {
		obj.removeClassName('noinherit');
		obj.addClassName('inherit');	
	}
}


/**
 * Highlights/blurs an iPanel and subelements
 * @param { Object } [obj] Regarding iPanel-div.  
 * @param { String } [action] Defines the action. May be set to over or out.
 * @function
 * @name $K.yg_ipanelHighlight
 */
$K.yg_ipanelHighlight = function(obj,action) {
	if (action=="over") {
		obj.addClassName('ipanelhighlight');
		$K.actionhover=false;
	} else {
		if ($K.actionhover!=true) obj.removeClassName('ipanelhighlight');
	}
}


/**
 * Opens/closes an ListCollaps Panel  
 * @param { Object } [obj] iPanel-div which should be opened/closed 
 * @param { Event } [ev] event to stop
 * @function
 * @name $K.yg_listCollapsSwap
 */
$K.yg_listCollapsSwap = function(obj, ev) {
	
	$K.yg_fireLateOnChange( true );
	
	if (ev) Event.stop(ev);
	if (obj.hasClassName("closed")) {
		obj.removeClassName('closed');
		obj.addClassName('opened');
	} else {
		obj.removeClassName('opened');
		obj.addClassName('closed');
	}
	
	$K.windows[obj.up('.ywindow').id].refresh(obj);
	$K.yg_showActions(obj);
}
