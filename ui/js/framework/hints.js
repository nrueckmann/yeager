/**
 * @fileoverview functions for showing/hiding hints
 */


$K.yg_hoverInt = false;
$K.yg_hoverTagInt = false;
$K.yg_hoverTagObj = false;
$K.yg_pickTimeObj = false;
$K.yg_hoverUserObj = false;
$K.bCloser = new Array();


/**
 * Starting timer
 * @param { Element } [obj] hovered tag html element
 * @param { Element } [id] tag id
 * @function
 * @name $K.yg_hoverTagHint
 */
$K.yg_hoverTagHint = function( obj, id ) {
	clearTimeout($K.yg_hoverTagInt);
	$K.yg_hoverTagObj = $(obj);
	$K.yg_hoverTagInt = setTimeout('$K.yg_showTagHint('+id+');',1000);
	Event.observe(obj,'mouseleave', $K.yg_hideTagHint);
}


/**
 * Showing tag hint
 * @param { Element } [id] tag id
 * @function
 * @name $K.yg_showTagHint
 */
$K.yg_showTagHint = function( id ) {
	clearTimeout($K.yg_hoverTagInt);
	var rawPath = $K.yg_hoverTagObj.readAttribute('path');
	var realPath = '';
	rawPath = rawPath.split('||');
	rawPath.each(function(pathItem, pathIdx){
		if (pathIdx != (rawPath.length-1)) {
			pathItem += ' <span class="traceicon"></span> ';
		}
		realPath += '<nobr>' + pathItem + '</nobr> ';
	});
	$('yg_tagHint').innerHTML = realPath;
	$('yg_tagHint').setStyle({display:'block'});
	objpos = $($K.yg_hoverTagObj).cumulativeOffset();
	$('yg_tagHint').setStyle({top:(objpos[1]+18)+'px'});
	$K.yg_positionTagHint()
	Event.observe($($K.yg_hoverTagObj),'mousemove', $K.yg_positionTagHint);
}


/**
 * Hiding tag hint
 * @function
 * @name $K.yg_hideTagHint
 */
$K.yg_hideTagHint = function() {
	clearTimeout($K.yg_hoverTagInt);
	$('yg_tagHint').setStyle({display:'none'});
	Event.stopObserving(obj,'mouseleave', $K.yg_hideTagHint);
	Event.stopObserving($($K.yg_hoverTagObj),'mousemove', $K.yg_positionTagHint);
}


/**
 * Re-position tag hint
 * @function
 */
$K.yg_positionTagHint = function() {
	$('yg_tagHint').setStyle({left:($K.yg_mouseCoords.X+9)+'px'});
}


/**
 * Shows filter context menu
 * @param { Element } [obj] The filter output span
 * @param { Event } [e] The click event
 * @function
 */
$K.yg_showFilterContext = function(obj, e) {
	Event.stop(e);
	context = obj.up().next();
	context.setStyle({display:'block'});
	$K.yg_statusObserve(obj.up().next().identify());
}


/**
 * opens the statuscontextmenu
 */
$K.yg_statusObserve = function(obj) {
	$K.bCloser[obj] = $K.yg_clickCloser.bindAsEventListener($(obj));
	//tmpfunc = $K.yg_clickCloser.bindAsEventListener($(obj));
	Event.observe(document,'click',$K.bCloser[obj]);
}


/**
 * Close context after listener fired
 */
$K.yg_clickCloser = function(ev) {
	if (!ev.findElement().descendantOf(this)) {
		Event.stopObserving(document,'click',$K.bCloser[this.id]);
		delete $K.bCloser[this.id];
		$K.bCloser.compact();
		this.setStyle({display: 'none'});
	}
}


$K.yg_userInfos = new Array();
/**
 * Starting timer
 * @param { Element } [obj] hovered tag html element
 * @function
 * @name $K.yg_hoverUserHint
 */
$K.yg_hoverUserHint = function( obj ) {
	if (typeof obj == 'number') {
		var userID = obj;
	} else {
		obj = $(obj);
		if (obj.hasClassName('unknown')) {
			return;
		}
		var userID = obj.readAttribute('yg_id');
		userID = userID.split('-')[0];
		$K.yg_hoverUserObj = $(obj);
	}

	if (userID) {
		if ($K.yg_userInfos[userID]) {
			// Cached
			$('yg_userHint').down('.username').innerHTML = $K.yg_userInfos[userID].name;
			$('yg_userHint').down('.usercompany').innerHTML = $K.yg_userInfos[userID].company;
			$('yg_userHint').down('.usergrouplist').innerHTML = $K.yg_userInfos[userID].groups;
			$('yg_userHint').down('img').src = $K.yg_userInfos[userID].pic;
			$('yg_userHint').down('img').onload = function(){};
			clearTimeout($K.yg_hoverInt);
			$K.yg_hoverInt = setTimeout('$K.yg_showUserHint(true);', 500);
		} else {
			// Non-cached, retrieve from backend
			var data = Array ( 'noevent', {yg_property: 'getUserInfo', params: {
				userID: userID
			} } );
			$K.yg_AjaxCallback( data, 'getUserInfo' );
		}
	}

}


/**
 * Showing User hint
 * @param { Element } [id] tag id
 * @param { Boolean } [cached] is cached?
 * @function
 * @name $K.yg_showUserHint
 */
$K.yg_showUserHint = function( cached ) {

	// Prevent display of actionbutton while dragging
	if ($K.yg_activeDragInfo.dragging) return;

	clearTimeout($K.yg_hoverInt);
	if (!($K.yg_hoverUserObj)) return;
	if (!cached) {
		$('yg_userHint').down('img').onload = function() {
			this.up(0).setStyle({display:'block'});
		}
	}
	objpos = $($K.yg_hoverUserObj).cumulativeOffset();
	$('yg_userHint').setStyle({top:(objpos[1]-82)+'px'});
	if (cached) {
		$('yg_userHint').setStyle({display:'block'});
	}
	$K.yg_positionUserHint();
	Event.observe(document,'mousemove', $K.yg_positionUserHint);
}


/**
 * Reposition user hint
 * @function
 * @name $K.yg_positionUserHint
 */
$K.yg_positionUserHint = function() {
	vport = document.viewport.getDimensions();
	$('yg_fileHint').hide();
	if ($K.yg_mouseCoords.X > (vport.width/2)) {
		$('yg_userHint').removeClassName('left');
		offset = 330;
	} else {
		$('yg_userHint').addClassName('left');
		offset = 20;
	}
	$('yg_userHint').setStyle({ left: ($K.yg_mouseCoords.X-offset)+'px' });
}


/**
 * Hiding User hint
 * @function
 * @name $K.yg_hideUserHint
 */
$K.yg_hideUserHint = function() {
	clearTimeout($K.yg_hoverInt);
	$K.yg_hoverUserObj = false;
	$('yg_userHint').setStyle({display:'none'});
	Event.stopObserving(document,'mousemove',$K.yg_positionUserHint);
}


$K.yg_fileInfos = new Array();
/**
 * Hovering file
 * @function
 * @param { Element } [obj] obj hovering on, may also be an id
 * @param { Event } [e] event
 * @name $K.yg_hoverFileHint
 */
$K.yg_hoverFileHint = function(obj, e) {

	// Prevent display of actionbutton while dragging
	if ($K.yg_activeDragInfo.dragging) return;

	if (e) {
		var targetElement = Event.findElement(e);
		if (targetElement.hasClassName('user')) {
			return;
		}
	}

	clearTimeout($K.yg_hoverInt);
	// Get fileID
	if ((typeof obj == 'number') || (typeof obj == 'string')) {
		var fileID = parseInt(obj, 10);
		obj = $(Event.element(e));
	} else {
		obj = $(obj);
		if (obj) {
			var fileID = obj.readAttribute('yg_id').split('-')[0];
		}
	}

	// Clear all images
	$('yg_fileHint').down('.mk_thumbnail').select('img').each(function(item){
		item.onload = function(){};
		item.up('.mk_thumbnail').addClassName('loading');
	});

	if (fileID) {
		$K.yg_fileHint = fileID;
		if ($K.yg_fileInfos[fileID]) {
			$K.yg_hoverInt = setTimeout('$K.yg_showFileHint(\''+fileID+'\');', 100);
		} else {
			// Trigger loading of data from backend
			$K.yg_hoverInt = setTimeout((function () {
				var data = Array ( 'noevent', {yg_property: 'getFileInfo', params: {
					fileID: fileID
				} } );
				$K.yg_AjaxCallback( data, 'getFileInfo', null, 'getFileInfo' );
			}), 100);
		}
	}
	Event.observe(obj,'mouseleave', $K.yg_hideFileHint);
}


/**
 * Hovering file (in Properties-Tab)
 * @function
 * @param { Element } [obj] obj hovering on, may also be an id
 * @param { Event } [e] event
 * @name $K.yg_hoverFileHint
 */
$K.yg_hoverPropertyLinkFileHint = function(obj, e) {
	if (obj.value.strip() != '') {
		var data = obj.value.split('/');
		if (data.length > 1) {
			var fileId = parseInt(data.last(), 10);
			if (!isNaN(fileId)) {
				$K.yg_hoverFileHint(fileId, e);
			}
		}
	}
}


/**
 * Showing file hint
 * @param { String } [fileID] The id of the relevant file.
 * @function
 * @name $K.yg_showFileHint
 */
$K.yg_showFileHint = function(fileID) {

	clearTimeout($K.yg_hoverInt);
	if (fileID == $K.yg_fileHint) {

		// Cached
		if ($K.yg_fileInfos[fileID].THUMB == '0') {
			$('yg_fileHint').down('.mk_thumbnail').down('.thumbdesc').hide();
			$('yg_fileHint').down('.mk_thumbnail').down('.thumbcnt_nothumb').show();
			$('yg_fileHint').down('.mk_thumbnail').removeClassName('loading');
			$('yg_fileHint').down('.mk_thumbnail').select('img').each(function(item){
				item.hide();
			});
		} else {
			$('yg_fileHint').down('.mk_thumbnail').down('.thumbcnt_nothumb').hide();
			$('yg_fileHint').down('.mk_thumbnail').down('.thumbdesc').show();
			$('yg_fileHint').down('.mk_thumbnail').select('img').each(function(item){
				item.show();
			});
		}

		$('yg_fileHint').down('.filedesc').down('.title').update($K.yg_fileInfos[fileID].NAME);
		$('yg_fileHint').down('.thumbdesc').down('.filetype').update($K.yg_fileInfos[fileID].IDENTIFIER);
		$('yg_fileHint').down('.thumbdesc').down('.filetype').className = 'filetype '+ $K.yg_fileInfos[fileID].COLOR;
		$('yg_fileHint').down('.filedesc').down('span.filename').update($K.yg_fileInfos[fileID].FILENAME);
		$('yg_fileHint').down('.filedesc').down('.user').update($K.yg_fileInfos[fileID].USERNAME);
		$('yg_fileHint').down('.tags').down('.taglist').update($K.yg_fileInfos[fileID].TAGS);
		if ($K.yg_fileInfos[fileID].TAGS) {
			$('yg_fileHint').down('.tags').show();
		} else {
			$('yg_fileHint').down('.tags').hide();
		}

		if ($K.yg_fileInfos[fileID].THUMB == '0' || $K.yg_fileInfos[fileID].WIDTH == null) {
			$('yg_fileHint').down('.filedesc').down('.dimensions_size').update($K.yg_fileInfos[fileID].FILESIZE);
		} else {
			$('yg_fileHint').down('.filedesc').down('.dimensions_size').update($K.yg_fileInfos[fileID].WIDTH+'x'+$K.yg_fileInfos[fileID].HEIGHT+', '+$K.yg_fileInfos[fileID].FILESIZE);
		}
		$('yg_fileHint').down('.filedesc').down('.filedatetime').update($K.yg_fileInfos[fileID].DATE+', '+$K.yg_fileInfos[fileID].TIME);

		$('yg_fileHint').down('.mk_thumbnail').select('img').each(function(item){
			item.src = $K.appdir+'image/'+fileID+'/yg-bigthumb/?rnd='+Math.random()*1000;
			item.onload = function(){
				$K.log( 'Image loaded', $K.Log.INFO );
				item.up('.mk_thumbnail').removeClassName('loading');
			};
		});

		$K.yg_positionFileHint();
		Event.observe(document,'mousemove', $K.yg_positionFileHint);
		if ($K.actionhover!=true) $('yg_fileHint').show();
	}
}


/**
 * Hiding file hint
 * @function
 * @name $K.yg_hideFileHint
 */
$K.yg_hideFileHint = function() {
	clearTimeout($K.yg_hoverInt);

	$K.yg_fileHint = false;

	$('yg_fileHint').down('.mk_thumbnail').select('img').each(function(item){
		item.src = '';
		item.writeAttribute('src', '');
		item.writeAttribute('loaded', 'false');
		item.onload = function(){};
	});

	$('yg_fileHint').setStyle({display:'none'});
	Event.stopObserving(document,'mousemove', $K.yg_positionFileHint);
	Event.stopObserving(obj,'mouseleave', $K.yg_hideFileHint);
}


/**
 * Reposition file hint
 * @function
 * @name $K.yg_positionFileHint
 */
$K.yg_positionFileHint = function() {

	if ($K.actionhover==true) return;

	vport = document.viewport.getDimensions();
	$('yg_fileHint').className = '';
	if ((($K.yg_mouseCoords.X + 600) > vport.width) && (($K.yg_mouseCoords.X - 600) > 0)) {
		// links
		$('yg_fileHint').addClassName('rgt');
		$('yg_fileHint').setStyle({left:($K.yg_mouseCoords.X-550)+'px'});
	} else {
		// rechts
		$('yg_fileHint').addClassName('lft');
		$('yg_fileHint').setStyle({left:($K.yg_mouseCoords.X+15)+'px'});
	}
	if (($K.yg_mouseCoords.Y + 200) > (vport.height)) {
		$('yg_fileHint').addClassName('bt');
		$('yg_fileHint').setStyle({top:($K.yg_mouseCoords.Y-163)+'px'});
	} else {
		$('yg_fileHint').addClassName('tp');
		$('yg_fileHint').setStyle({top:($K.yg_mouseCoords.Y-14)+'px'});
	}
}


/**
 * Opens the user info-window
 * @param { Integer } [id] The user id
 * @param { Element } [which] The element which fired the event
 * @function
 * @name $K.yg_clearUploadProgress
 */
$K.yg_openUserInfo = function(id, which) {
	which = $(which);
	if (which.hasClassName('unknown')) {
		return;
	}
	$K.yg_hideUserHint();

	new $K.yg_wndobj({ config: 'USER_INFO', loadparams: { userid: id } } );
}

