/**
 * @fileoverview functions for mailings
 *
 * @version 0.2.0
 */


/**
 * Initializes mailing properties tab
 * @param { String } [mailingID] The mailingID-object
 * @param { String } [winID] The contentblock-object which should be opened/closed
 * @function
 * @name $K.yg_initMailingPropertyTab
 */
$K.yg_initMailingPropertyTab = function( mailingID, winID ) {

	$K.windows['wid_'+winID].changeTemplate = function( templateId ) {
		var mailingId = this.yg_id.split('-');
		mailingId = mailingId[0];

		var data = Array ( 'noevent', {yg_property: 'setMailingTemplate', params: {
			mailingId: mailingId,
			templateId: templateId,
			wid: winID
		} } );
		$K.yg_AjaxCallback( data, 'setMailingTemplate' );
	}

	$K.windows['wid_'+winID].refreshTemplate = function(templateId) {
		var tabRef = $('wid_'+winID+'_MAILING_PROPERTIES');

		if ( !Object.isUndefined(tabRef) ) {
			var panelRef = tabRef.down('.cntblockselection');
			panelRef.writeAttribute('yg_preselected', templateId);

			new Ajax.Updater(panelRef, $K.appdir+'template_info/',
			{
				onComplete: function() { $K.windows['wid_'+winID].init(); },
				asynchronous: true,
				evalScripts: true,
				method: 'post',
				parameters: {
					template: templateId,
					mode: 'details',
					us: document.body.id,
					lh: $K.yg_getLastGuiSyncHistoryId()
				}
			});
		} else {
			$K.log( 'Panel not there, nothing to update...', $K.Log.INFO );
		}
	}
}


/**
 * Approves a mailing
 * @param { Element } [which] The element from which the function is called.
 * @function
 * @name $K.yg_approveMailing
 */
$K.yg_approveMailing = function( which ) {
	which = $(which);
	var winID = which.up('.ywindow').id.replace(/wid_/,'');
	var ygId = $K.windows['wid_'+winID].yg_id;

	var data = Array ( 'noevent', {yg_property: 'approveMailing', params: {
		mailing: ygId.split('-')[0],
		winID: winID
	} } );
	$K.yg_AjaxCallback( data, 'approveMailing' );
}


/**
 * Inits the mailings tab
 * @param { String } [win] window id
 * @function
 * @name $K.yg_initMailings
 */
$K.yg_initMailings = function(win) {
	$K.yg_initMailingFilter(win);

	// Check if already set
	if (!$K.windows[win].periodicTimer) {
		$K.windows[win].periodicMail = false;
		$K.windows[win].periodicTimer = new PeriodicalExecuter(function() {
			// Collect all ids of currently displayed mailings
			if ($K.windows[win].periodicMail == true) return;
			$K.windows[win].periodicMail = true;
			var mailingIds = new Array();
			$(win+'_MAILINGS').down('.mk_contentgroup').select('.cntblockcontainer').each(function(item){
				mailingIds.push(parseInt(item.readAttribute('yg_id').split('-')[0], 10));
			});

			var data = Array ( 'noevent', {yg_property: 'updateMailingStatus', params: {
				mailingIds: mailingIds
			} } );
			$K.yg_AjaxCallback( data, 'updateMailingStatus' );
		}, 3);
	}
}


/**
 * Inits version filter onload/reload of tab
 * @param { String } [win] window id
 * @function
 * @name $K.yg_initMailingFilter
 */
$K.yg_initMailingFilter = function(win) {
	if (!$K.windows[win].loadparams.mailingfilter_status) {
		$K.windows[win].loadparams.mailingfilter_status = 'ALL';
	} else {
		for (var filtername in $K.windows[win].loadparams) {
			if (filtername.substring(0,19) == "mailingfilter") {
				tmpobj = $(win+"_"+filtername);
				if (tmpobj) tmparr = tmpobj.immediateDescendants();
				for (var k=0; k<tmparr.length;k++) {
					if (tmparr[k].readAttribute("value") == $K.windows[win].loadparams[filtername]) {
						tmpobj.previous().down('span').innerHTML = tmparr[k].readAttribute("shortname");
					}
				}
			}
		}
	}
}


/**
 * Sets filter
 * @param { String } [col] may be set to 'tab', 'action' or 'timeframe'
 * @param { String } [value] filter value
 * @param { String } [shorttitle] Shorttitle of selected filter
 * @param { String } [win] window id
 * @function
 * @name $K.yg_filterMailings
 */
$K.yg_filterMailings = function(col, value, shorttitle, win) {

	// Reset pagedir to first page
	$K.yg_pageDirReset(win);

	$(win+'_mailingfilter_'+col).previous().down().innerHTML = shorttitle;

	$K.windows[win].loadparams['mailingfilter_'+col] = value;

	var params = new Object();
	params.refresh = 1;

	var window = $K.windows[win];

	window.tabs.select(window.tabs.selected, params);
	$K.yg_toggleTreeButtons(win);

}


/**
 * Sends out a configured mailings
 * @param { Element } [which] Reference to clicked element
 * @function
 * @name $K.yg_sendMailing
 */
$K.yg_sendMailing = function(which) {
	which = $(which);

	$K.yg_promptbox( $K.TXT('TXT_APPROVE_MAILING_TITLE'), $K.TXT('TXT_APPROVE_MAILING_SEND'), 'standard',
		function() {
			var mailingId = which.up('.cntblockcontainer').readAttribute('yg_id').split('-')[0];
			var data = Array ( 'noevent', {yg_property: 'sendMailing', params: {
				mailingId: mailingId
			} } );
			$K.yg_AjaxCallback( data, 'sendMailing' );
		},
		function() {}
	);
}


/**
 * Pause the processing of an mailing
 * @param { Element } [which] Reference to clicked element
 * @function
 * @name $K.yg_pauseMailing
 */
$K.yg_pauseMailing = function(which) {
	which = $(which);

	$K.yg_promptbox( $K.TXT('TXT_APPROVE_MAILING_TITLE'), $K.TXT('TXT_APPROVE_MAILING_PAUSE'), 'standard',
		function() {
			// Update status
			which.up('.status_info').className = 'status_info paused justchanged';

			var mailingId = which.up('.cntblockcontainer').readAttribute('yg_id').split('-')[0];
			var data = Array ( 'noevent', {yg_property: 'pauseMailing', params: {
				mailingId: mailingId
			} } );
			$K.yg_AjaxCallback( data, 'pauseMailing' );
		},
		function() {}
	);
}


/**
 * Resume the processing of an mailing
 * @param { Element } [which] Reference to clicked element
 * @function
 * @name $K.yg_resumeMailing
 */
$K.yg_resumeMailing = function(which) {
	which = $(which);

	$K.yg_promptbox( $K.TXT('TXT_APPROVE_MAILING_TITLE'), $K.TXT('TXT_APPROVE_MAILING_RESUME'), 'standard',
		function() {
			// Update status
			which.up('.status_info').className = 'status_info inprogress justchanged';

			var mailingId = which.up('.cntblockcontainer').readAttribute('yg_id').split('-')[0];
			var data = Array ( 'noevent', {yg_property: 'resumeMailing', params: {
				mailingId: mailingId
			} } );
			$K.yg_AjaxCallback( data, 'resumeMailing' );
		},
		function() {}
	);
}


/**
 * Change the SE-Friendly mailing name (PNAME)
 * @param { Element } [element] The element from which the function was called.
 * @function
 * @name $K.yg_changeMailingPName
 */
$K.yg_changeMailingPName = function( element ) {

	// Fix for Safari (firing onchange 2 times in a row)
	if ( Prototype.Browser.WebKit &&
		 $$('.ywindow.pbox.standard').length ) {
		return;
	}

	var value = element.value;
	var yg_id = element.getAttribute('yg_id');

	if (!element.oldvalue) {
		element.oldvalue = element.readAttribute('oldvalue');
	}

	// Check if name has really changed
	if (element.value == element.oldvalue) {
		return;
	}

	if (value.strip()=='') {
		element.value = element.oldvalue;
		$K.yg_promptbox( $K.TXT('TXT_CHANGE_MAILING_URL_TITLE'), $K.TXT('TXT_CHANGE_MAILING_URL_EMPTY'), 'alert');
		return;
	}

	if (!isNaN(value)) {
		element.value = element.oldvalue;
		$K.yg_promptbox( $K.TXT('TXT_CHANGE_MAILING_URL_TITLE'), $K.TXT('TXT_CHANGE_MAILING_URL_NUMERIC'), 'alert');
		return;
	}

	$K.yg_promptbox( $K.TXT('TXT_CHANGE_MAILING_URL_TITLE'), $K.TXT('TXT_CHANGE_MAILING_URL'), 'standard',
		function() {
			$K.yg_setEdited( element );
			element.setAttribute( 'yg_previous', value );

			var mailing = yg_id.split('-')[0];

			var data = Array ( 'noevent', { yg_property: 'setMailingPName', params: {
				value: value,
				mailing: mailing
			} } );
			$K.yg_AjaxCallback( data, 'setMailingPName' );
		},
		function() {
			element.value = element.getAttribute('yg_previous');
		}
	);
}



/**
 * Cancel the processing of an mailing
 * @param { Element } [which] Reference to clicked element
 * @function
 * @name $K.yg_cancelMailing
 */
$K.yg_cancelMailing = function(which) {
	which = $(which);

	$K.yg_promptbox( $K.TXT('TXT_APPROVE_MAILING_TITLE'), $K.TXT('TXT_APPROVE_MAILING_CANCEL'), 'standard',
		function() {
			// Update status
			which.up('.status_info').className = 'status_info cancelled justchanged';

			var mailingId = which.up('.cntblockcontainer').readAttribute('yg_id').split('-')[0];
			var data = Array ( 'noevent', {yg_property: 'cancelMailing', params: {
				mailingId: mailingId
			} } );
			$K.yg_AjaxCallback( data, 'cancelMailing' );
		},
		function() {}
	);
}


/**
 * Updates the status of the mailings in the overview
 * @param { Object } [data] Data which contains status-information from the scheduler
 * @function
 * @name $K.yg_updateMailingStatus
 */
$K.yg_updateMailingStatus = function(data) {
	data = data.evalJSON();

	if (data && (data.length > 0)) {
		for (winId in $K.windows) {
			if ($K.windows[winId].tab == 'MAILINGS') {

				$K.windows[winId].periodicMail = false;
				// mailing window found, update status
				$(winId+'_MAILINGS').down('.mk_contentgroup').select('.cntblockcontainer').each(function(item){
					data.each(function(dataItem){
						if (dataItem.MAILING_ID == parseInt(item.readAttribute('yg_id').split('-')[0], 10)) {
							// Update
							if ( (dataItem.STATUS == 'UNSENT') || (dataItem.STATUS == 'SENT') ) {
								item.down('span.status_num').update('0 / ' + dataItem.RECEIPIENTS);
								var progressPercent = 0;
							} else {
								item.down('span.status_num').update((dataItem.RECEIPIENTS - dataItem.JOBCOUNT) + ' / ' + dataItem.RECEIPIENTS);
								var progressPercent = ((dataItem.RECEIPIENTS - dataItem.JOBCOUNT) / dataItem.RECEIPIENTS) * 100;
							}
							item.down('td.receipientstd').update(dataItem.RECEIPIENTS);
							item.down('div.progress').setStyle({width: progressPercent + '%'});

							// Check if the job was just started
							//if (item.down('.status_info').hasClassName('justchanged') && (dataItem.JOBCOUNT == 0)) {
							if (item.down('.status_info').hasClassName('justchanged')) {
								item.down('.status_info').removeClassName('justchanged');
							} else {
								item.down('.status_info').className = 'status_info ' + dataItem.STATUS.toLowerCase();
							}

						}
					});
					//mailingIds.push(parseInt(item.readAttribute('yg_id').split('-')[0], 10));
				});
			}
		}
	}
}


/**
 * Submits mailing test dialog
 * @param { String } [winID] The contentblock-object which should be opened/closed
 * @param { String } [ygId] The mailing ygId
 * @function
 * @name $K.yg_submitMailingTest
 */
$K.yg_submitMailingTest = function(winId, ygId) {
	var mailingId = ygId.split('-')[0];
	var emailAddress = $(winId+'_send_to').value;

	// Check for valid Email-address
	$(winId+'_send_to').removeClassName('error');
	var validationError = false;
	if (emailAddress.indexOf(',')!=-1) {
		var emailAdresses = emailAddress.split(',');
		emailAdresses.each(function(item){
			item = item.strip();
			if (!Validator.isEmail(item) || (item.strip() == '')) {
				validationError = true;
			}
		});
	} else {
		if (!Validator.isEmail(emailAddress) || (emailAddress.strip() == '')) {
			validationError = true;
		}
	}
	if (validationError) {
		$(winId+'_send_to').addClassName('error');
	} else {
		var data = Array ( 'noevent', {yg_property: 'sendMailing', params: {
			testRecipient: emailAddress,
			testOnly: true,
			mailingId: mailingId
		} } );
		$K.yg_AjaxCallback( data, 'sendMailing' );

		// Close window
		$K.windows[winId].remove();
	}
}


/**
 * Add a new mailing
 * @param { Element } [which] Reference to the button which was clicked on
 * @function
 * @name $K.yg_addMailing
 */
$K.yg_addMailing = function(which) {
	which = $(which);
	var winId = which.up('.ywindow').id;

	var data = Array ( 'noevent', {yg_property: 'addMailing', params: {
		winId: winId
	} } );
	$K.yg_AjaxCallback( data, 'addMailing' );
}


/**
 * Refreshes the mailing window
 * @param { Boolean } [onlyRefresh] Refresh only (do not jump to first page)
 * @function
 * @name $K.yg_refreshMailingsWindow
 */
$K.yg_refreshMailingsWindow = function (onlyRefresh) {
	for (winId in $K.windows) {
		if (($K.windows[winId].yg_type == 'mailing') && ($K.windows[winId].tab == 'MAILINGS')) {
			if (!onlyRefresh) {
				// Jump to first page
				$K.yg_pageDirReset(winId);
				$K.windows[winId].tabs.select($K.windows[winId].tabs.selected, $K.windows[winId].tabs.params);
			} else if ($(winId).down('.panelcontent').select('.cntblockcontainer').length <= 1) {
				// Jump one page back, in case there's only one element left on page
				$K.yg_pageDirPrevious( $(winId+'_pagedir_MAILINGS') );
			} else {
				$K.windows[winId].tabs.select($K.windows[winId].tabs.selected, $K.windows[winId].tabs.params);
			}
		}
	}
}


/**
 * Edits the selected mailings
 * @param { Element } [which] Reference to the button which was clicked on
 * @function
 * @name $K.yg_refreshMailingsWindow
 */
$K.yg_editMailing = function (which) {
	which = $(which);

	var winId = which.up('.ywindow').id;
	var ygId = $K.windows[winId].yg_id;
	var mailingId = ygId.split('-')[0];

	var tmpOnclick = new Function($('mailings_' + winId.split('_')[1] + '_' + mailingId).readAttribute('ondblclick'));
	tmpOnclick();
}


/**
 * Inititalzes the mailing config-tab
 * @param { String } [parentWinID] The window id
 * @function
 * @name $K.yg_initMailingConfig
 */
$K.yg_initMailingConfig = function( parentWinID ) {

	$K.windows['wid_'+parentWinID].changeTemplate = function (templateId) {

		var data = Array ( 'noevent', {yg_property: 'setMailingConfigTemplate', params: {
			templateId: templateId,
			wid: parentWinID
		} } );

		$K.yg_AjaxCallback( data, 'setMailingConfigTemplate' );
	}

	$K.windows['wid_'+parentWinID].refreshMailingTemplate = function(name, filename, templateid, preview) {
		var panelRef = $('wid_'+parentWinID+'_templatepanel').down('.mk_templatecontainer');
		if (panelRef) {
			panelRef.up('.cntblockcontainer').down('input').value = templateid;
			new Ajax.Updater(panelRef, $K.appdir+'template_info/',
			{
				onComplete: function() {
					panelRef.next().hide();
					panelRef.show();
					$K.windows['wid_'+parentWinID].refresh();
				},
				asynchronous: true,
				evalScripts: true,
				method: 'post',
				parameters: {
					template: templateid,
					mode: 'details',
					us: document.body.id,
					lh: $K.yg_getLastGuiSyncHistoryId()
				}
			});
		} else {
			$K.log( 'Panel not there, nothing to update...', $K.Log.INFO );
		}
	}

	$K.windows['wid_'+parentWinID].changeTemplateRoot = function (templateId) {

		var data = Array ( 'noevent', {yg_property: 'setMailingConfigTemplateRoot', params: {
			templateId: templateId,
			wid: parentWinID
		} } );
		$K.yg_AjaxCallback( data, 'setMailingConfigTemplateRoot' );
	}

	$K.windows['wid_'+parentWinID].refreshMailingTemplateRoot = function(templateId, name) {

		var panelRef = $('wid_'+parentWinID+'_templaterootpanel');

		if (panelRef) {
			panelRef.down('input').value = templateId;
			panelRef.down('.icn').className = "icn iconfolder"
			panelRef.down('.titlecnt').update(name);

			$K.windows['wid_'+parentWinID].refresh();
		} else {
			$K.log( 'Panel not there, nothing to update...', $K.Log.INFO );
		}
	}

}

/**
 * Saves all mailing config information
 * @param { String } [winRef] Id of the parentwindow
 */
$K.yg_mailingSaveInfo = function( winRef ) {
	winRef = $(winRef);

	var inputFields = $(winRef.id+'_innercontent').select('input');

	var parameters = {
		wid: winRef.id
	};

	var srch = new RegExp(winRef.id+'_');
	inputFields.each(function(item){
		var idxname = item.name.replace(srch,'');
		if (idxname) parameters[idxname] = item.value;
	});

	var data = Array ( 'noevent', {yg_property: 'saveMailingInfo', params: parameters } );
	$K.yg_AjaxCallback( data, 'saveMailingInfo' );
}


/**
 * Copies the selected mailing
 * @param { String } [mailingId] Id of the mailing
 */
$K.yg_duplicateMailing = function(mailingId) {
	var data = Array ( 'noevent', {yg_property: 'duplicateMailing', params: {
		mailingId: mailingId
	} } );
	$K.yg_AjaxCallback( data, 'duplicateMailing' );
}
