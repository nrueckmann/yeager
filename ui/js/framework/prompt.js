/**
 * @fileoverview Provides functionality top open, remove and init prompt dialogs
 */

$K.promptfunc = new Array();

/**
 * Function for creating a promptbox
 * @param { String } [txtheadline] headline displayed in the promptbox
 * @param { String } [txtcopy] copytext displayed in the promptbox
 * @param { String } [prompttype] displays individual content. possible types: standard, remove, alert
 * @param { String } [okfunction] function exectued when confirming
 * @param { String } [abortfunction] function exectued when aborting
 * @function
 * @name $K.yg_promptbox
 */
$K.yg_promptbox = function(txtheadline, txtcopy, prompttype, okfunction, abortfunction) {

	$K.wndid++;
	var ywindownum = $K.wndid;
	var funcid = "prompt" + ywindownum;

	var acknowledgetext = $K.TXT('TXT_ACKNOWLEDGE');
	if (prompttype == 'alert') {
		acknowledgetext = $K.TXT('TXT_OK');
	}

	if (txtheadline == "") txtheadline = $K.TXT('TXT_NOTIFICATION');

	var windowhtml = $K.yg_templates["prompt"].evaluate({
		win_no: ywindownum,
		txtheadline: txtheadline,
		txtcopy: txtcopy,
		prompttype: prompttype,
		funcid: funcid,
		acknowledgetext: acknowledgetext,
		canceltext: $K.TXT('TXT_CANCEL')
	});

	$K.promptfunc[funcid] = new Object();
	if (okfunction) {
		$K.promptfunc[funcid].okfunction = okfunction;
	} else {
		$K.promptfunc[funcid].okfunction = function() { return; };
	}
	if (abortfunction) {
		$K.promptfunc[funcid].abortfunction = abortfunction;
	} else {
		$K.promptfunc[funcid].abortfunction = function() { return; };
	}
	$('dialogcontainer').insert({top:windowhtml});
	$K.yg_initPromptWindow( 'wid_'+ywindownum, prompttype, funcid );

}


/**
* Initializes prompt window
* @param { String } [pBoxId] Element id from promptbox
* @param { String } [promptType] type of promptbox
* @param { String } [funcId] type of promptbox
* @function
* @name $K.yg_initPromptWindow
*/
$K.yg_initPromptWindow = function( pBoxId, promptType, funcId ) {

	$(pBoxId).keyMapperOKHandle = Koala.mapKey( function(key) {
		if ($K.promptfunc[funcId] && $K.promptfunc[funcId].okfunction && (typeof $K.promptfunc[funcId].okfunction == 'function')) {
			$K.promptfunc[funcId].okfunction( $(pBoxId+'_form').getInputs() );
		}
		$K.yg_removePrompt( pBoxId );
	}, '', 13, 13 );

	$(pBoxId).keyMapperESCHandle = Koala.mapKey( function(key) {
		$K.promptfunc[funcId].abortfunction();
		$K.yg_removePrompt( pBoxId )
	}, '', 27, 27 );

	$K.promptfunc[funcId]['winno'] = pBoxId;

	if (promptType == "alert") {
		$(pBoxId+'_cancelbutton').remove();
	}

	$K.yg_centerWindow($(pBoxId));

	$(pBoxId+'_modalbg').setStyle({zIndex:100000});
	$(pBoxId).setStyle({
		visibility: 'visible',
		zIndex: 100001
	});

	new Draggable(pBoxId,{handle:pBoxId+'_header', zindex:100001, onStart: function() {
		$(this.handle).up('.pbox').setStyle({zIndex:100001});
	} });

	if (!Prototype.Browser.IE)
		$(pBoxId+'_focus').focus();
}


/**
 * Function for removing promptboxes and cleaning garbage
 * @param { String } [winno] window id
 * @function
 * @name $K.yg_removePrompt
 */
$K.yg_removePrompt = function(winno) {
	for (var i in $K.promptfunc) {
		if ($($K.promptfunc[i].winno == winno)) {
			$K.promptfunc.splice(i, i+1);
		}
	}

	// Unmap keys
	if ($(winno).keyMapperOKHandle)
		Koala.unMapKey( $(winno).keyMapperOKHandle );
	if ($(winno).keyMapperESCHandle)
		Koala.unMapKey( $(winno).keyMapperESCHandle );

	$(winno).remove();
	$(winno+"_modalbg").remove();
}
