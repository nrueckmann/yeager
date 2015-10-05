
$K.yg_updatesToInstall = new Array();

/**
 * Triggers the installation of the selected update (and possibly previous updates)
 * @param { String } [which] The element which was clicked on
 * @function
 * @name $K.yg_installUpdate
 */
$K.yg_installUpdate = function(which) {
	which = $(which);

	var allRevisions = new Array();
	var allRevisionContainers = which.up('.mk_contentgroup').select('.mk_update');
	allRevisionContainers.each(function(item) {
		if (!item.down('.status_info').hasClassName('installed')) {
			allRevisions.push({
				rev: item.down('.mk_revision').innerHTML,
				ver: item.down('.versioninfo').innerHTML.replace(/v/, ''),
				url: item.readAttribute('update_url')
			});
		}
	});

	var selectedVersion = which.up('.mk_update').down('.versioninfo').innerHTML.replace(/v/, '');

	$K.yg_updatesToInstall = new Array();
	allRevisions.each(function(item) {
		if ($K.yg_versionCompare(selectedVersion, item.ver) >= 0) {
			$K.yg_updatesToInstall.push(item);
		}
	});

	if ($K.yg_updatesToInstall.length > 0) {
		allRevisionContainers.each(function(item) {
			if (item.down('.mk_revision').innerHTML == $K.yg_updatesToInstall[0].rev) {
				item.down('.status_info').addClassName('inprogress');
			}
		});
		var data = Array ( 'noevent', {yg_property: 'installUpdate', params: {
			installRevision: $K.yg_updatesToInstall[0]
		} } );
		$K.yg_AjaxCallback( data, 'installUpdate', false, null );
	}
};


/**
 * Starts the installation package (in the iframe of the updatemanager)
 * @param { String } [url] The url of the update to be invoked
 * @param { Integer } [updateRevision] The revision of the update to install
 * @function
 * @name $K.yg_startUpdate
 */
$K.yg_startUpdate = function(url, updateRevision) {
	var allRevisionContainers = $('wid_updates').select('.mk_update');
	if (url.endsWith('.update')) {
		url.replace(/\.update/, '.php');
	}
	allRevisionContainers.each(function(item) {
		if ( item.down('.mk_revision') &&
			 !item.hasClassName('mk_currentversion') &&
			 (item.down('.mk_revision').innerHTML == $K.yg_updatesToInstall[0].rev) ) {
			item.down('iframe').src = url + '?r=' + updateRevision;
		}
	});
};


/**
 * OnSuccess callback for the updater. Gets called after a successful update
 * @param { Integer } [updateRevision] The revision of the update to install
 * @function
 * @name $K.yg_updaterOnSuccess
 */
$K.yg_updaterOnSuccess = function(updateRevision) {
	var allRevisionContainers = $('wid_updates').select('.mk_update');
	allRevisionContainers.each(function(item) {
		if ( item.down('.mk_revision') &&
			 !item.hasClassName('mk_currentversion') &&
			 (item.down('.mk_revision').innerHTML == $K.yg_updatesToInstall[0].rev) ) {
			var currVer = item.down('.versioninfo').innerHTML.strip();
			var currRev = item.down('.mk_revision').innerHTML.strip();
			var currDate = item.down('.mk_versiondate').innerHTML.strip();
			item.down('.status_info').removeClassName('inprogress');
			item.down('.status_info').addClassName('installed');
			item.down('iframe').src = 'about:blank';
			// Update current version, revision & date
			$('wid_updates').down('.mk_currentversion').down('.versioninfo').update(currVer);
			$('wid_updates').down('.mk_currentversion').down('.mk_revision').update(currRev);
			$('wid_updates').down('.mk_currentversion').down('.mk_versiondate').update(currDate);

			var data = Array ( 'noevent', {yg_property: 'updateInstalled', params: {
				revision: $K.yg_updatesToInstall[0].rev
			} } );
			$K.yg_AjaxCallback( data, 'updateInstalled', false, null );
		}
	});
	$K.yg_updatesToInstall.shift();
	if ($K.yg_updatesToInstall.length > 0) {
		allRevisionContainers.each(function(item) {
			if ( item.down('.mk_revision') &&
				 !item.hasClassName('mk_currentversion') &&
				 (item.down('.mk_revision').innerHTML == $K.yg_updatesToInstall[0].rev) ) {
				item.down('.status_info').addClassName('inprogress');
			}
		});
		var data = Array ( 'noevent', {yg_property: 'installUpdate', params: {
			installRevision: $K.yg_updatesToInstall[0]
		} } );
		$K.yg_AjaxCallback( data, 'installUpdate', false, null );
	} else {
		$K.yg_updatesToInstall = new Array();
	}
};


/**
 * OnStart callback for the updater. Gets called after the updating process has started
 * @param { Integer } [updateRevision] The revision of the update to install
 * @function
 * @name $K.yg_updaterOnStart
 */
$K.yg_updaterOnStart = function(data) {

};

// returns positive number if v1 > v2, else neg num, 0 if equal
$K.yg_versionCompare = function(v1, v2, options) {
    var lexicographical = options && options.lexicographical,
        zeroExtend = options && options.zeroExtend,
        v1parts = v1.split('.'),
        v2parts = v2.split('.');

    function isValidPart(x) {
        return (lexicographical ? /^\d+[A-Za-z]*$/ : /^\d+$/).test(x);
    }

    if (!v1parts.every(isValidPart) || !v2parts.every(isValidPart)) {
        return NaN;
    }

    if (zeroExtend) {
        while (v1parts.length < v2parts.length) v1parts.push("0");
        while (v2parts.length < v1parts.length) v2parts.push("0");
    }

    if (!lexicographical) {
        v1parts = v1parts.map(Number);
        v2parts = v2parts.map(Number);
    }

    for (var i = 0; i < v1parts.length; ++i) {
        if (v2parts.length == i) {
            return 1;
        }

        if (v1parts[i] == v2parts[i]) {
            continue;
        }
        else if (v1parts[i] > v2parts[i]) {
            return 1;
        }
        else {
            return -1;
        }
    }

    if (v1parts.length != v2parts.length) {
        return -1;
    }
    return 0;
}


/**
 * OnSkip callback for the updater. Gets called if the current update doesn't need to be installed
 * @param { Integer } [updateRevision] The revision of the update to install
 * @function
 * @name $K.yg_updaterOnSkip
 */
$K.yg_updaterOnSkip = function(data) {
	var allRevisionContainers = $('wid_updates').select('.mk_update');
	allRevisionContainers.each(function(item) {
		if ( item.down('.mk_revision') &&
			 !item.hasClassName('mk_currentversion') &&
			 (item.down('.mk_revision').innerHTML == data) ) {
			item.down('.status_info').removeClassName('inprogress');
			item.down('.status_info').addClassName('skipped');
			item.down('.progress').setStyle({width: '0%'});
			item.down('iframe').src = 'about:blank';
		}
	});

	$K.yg_updatesToInstall.shift();
	if ($K.yg_updatesToInstall.length > 0) {
		allRevisionContainers.each(function(item) {
			if ( item.down('.mk_revision') &&
				 !item.hasClassName('mk_currentversion') &&
				 (item.down('.mk_revision').innerHTML == $K.yg_updatesToInstall[0].rev) ) {
				item.down('.status_info').addClassName('skipped');
			}
		});
		var data = Array ( 'noevent', {yg_property: 'installUpdate', params: {
			installRevision: $K.yg_updatesToInstall[0]
		} } );
		$K.yg_AjaxCallback( data, 'installUpdate', false, null );
	} else {
		$K.yg_updatesToInstall = new Array();
	}
};


/**
 * OnError callback for the updater. Gets called in case of an error while the update process is running
 * @param { Integer } [updateRevision] The revision of the update to install
 * @param { String } [message] The message to be displayed
 * @function
 * @name $K.yg_updaterOnError
 */
$K.yg_updaterOnError = function(data, message) {
	var allRevisionContainers = $('wid_updates').select('.mk_update');
	allRevisionContainers.each(function(item) {
		if ( item.down('.mk_revision') &&
			 !item.hasClassName('mk_currentversion') &&
			 (item.down('.mk_revision').innerHTML == data) ) {
			item.down('.status_info').removeClassName('inprogress');
			item.down('.status_info').addClassName('cancelled');
			item.down('.progress').setStyle({width: '0%'});
			item.down('iframe').src = 'about:blank';
		}
	});

	$K.yg_promptbox('Error', message, 'alert');
};


/**
 * SetProgress callback for the updater. Gets called to update the progress indicator
 * @param { Integer } [updateRevision] The revision of the update to install
 * @param { Integer } [percent] The progress in percent
 * @function
 * @name $K.yg_updaterSetProgress
 */
$K.yg_updaterSetProgress = function(data, percent) {
	var allRevisionContainers = $('wid_updates').select('.mk_update');
	allRevisionContainers.each(function(item) {
		if ( item.down('.mk_revision') &&
			 !item.hasClassName('mk_currentversion') &&
			 (item.down('.mk_revision').innerHTML == data) ) {
			item.down('.progress').setStyle({width: percent+'%'});
			if (item.down('.status_info').hasClassName('cancelled')) {
				item.down('.status_info').addClassName('inprogress');
				item.down('.status_info').removeClassName('cancelled');
			}
		}
	});
};


/**
 * ReloadAll callback for the updater. Gets called if the updater needs to reload the complete application
 * @param { Integer } [updateRevision] The revision of the update to install
 * @function
 * @name $K.yg_updaterReloadAll
 */
$K.yg_updaterReloadAll = function(data) {
	$K.yg_promptbox($K.TXT('TXT_PICKDATE'), $K.TXT('TXT_UPDATER_RELOAD_REQUIRED'), 'alert', function() {
		window.location.reload();
	});
};


/**
 * ReloadAll callback for the updater. Gets called if the updater needs to reload the complete application
 * @param { String } [winID] Window-ID
 * @param { String } [newVersionNo] Version number of new update
 * @param { String } [newVersionDate] Date of new update
 * @param { String } [newVersionText] Text for updater button
 * @function
 * @name $K.yg_updaterNewVersion
 */
$K.yg_updaterNewVersion = function(winID, newVersionNo, newVersionDate, newVersionText) {
	if ($(winID) && $(winID).down('.mk_updater')) {
		$(winID).down('.mk_updater').removeClassName('nosubline');
		var noBr = new Element('nobr').update(newVersionText+': v'+newVersionNo+', '+newVersionDate);
		$(winID).down('.mk_updater').insert({bottom: noBr});
	}
}
