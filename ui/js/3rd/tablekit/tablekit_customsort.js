/**
 * Custom sort functions for TableKit (for Yeager Filename Column)
 */

TableKit.Sortable.addSortType(
	new TableKit.Sortable.Type('sort_filename', {
		pattern: /^[pattern_dummy]$/,
		normal: function(v) {
			var cleaned = v.split(' ');
			cleaned.shift();
			cleaned = cleaned.join(' ').toLowerCase();
			return cleaned;
		}
	}
));


var dateSortFunc = function(v) {

	// Cut away everything after first \n
	if(v.indexOf('\n') != -1) {
		v = v.substring(0, v.indexOf('\n'));
	}
	
	var cleaned = v.split(' ');
	
	// Check if v is empty
	if (v == '') {
		return '1970-01-01 00:00';
	}
	
	// Check for dateformat
	var isoDate;
	if ((cleaned.length > 0) && cleaned[0].indexOf('.')!=-1) {
		// European date, such as:
		// 23.02.09
		// 23.02.2009
		var dateField = cleaned[0].split('.');
		if (dateField.length > 0) {
			if (dateField[0].length < 2) {
				dateField[0].length = '0'+dateField[0].length;
			}
			if (dateField[1].length < 2) {
				dateField[1].length = '0'+dateField[1].length;
			}
			if (dateField[2].length == 2) {
				dateField[2] = (parseInt(dateField[2], 10) + 2000).toString();
			}
			isoDate = dateField[2]+'-'+dateField[1]+'-'+dateField[0]+' ';
		}
	} else if ((cleaned.length > 0) && cleaned[0].indexOf('/')!=-1) {
		// American date, such as:
		// 05/23/09
		// 05/23/2009
		var dateField = cleaned[0].split('/');
		if (dateField.length > 0) {
			if (dateField[0].length < 2) {
				dateField[0] = '0'+dateField[0].toString();
			}
			if (dateField[1].length < 2) {
				dateField[1] = '0'+dateField[1].toString();
			}
			if (dateField[2].length == 2) {
				dateField[2] = (parseInt(dateField[2], 10) + 2000).toString();
			}
			isoDate = dateField[2]+'-'+dateField[0]+'-'+dateField[1]+' ';
		}
	}
	// Check for timeformat
	if ((cleaned.length > 2) && ((cleaned[2].indexOf('am')!=-1) || (cleaned[2].indexOf('pm')!=-1)) ) {
		// American time (12-hours), such as:
		// 02:26pm
		var timeField = cleaned[1];
		var isAmPm;
		if (cleaned[2].indexOf('am') != -1) {
			isAmPm = 'am';
		}
		if (cleaned[2].indexOf('pm') != -1) {
			isAmPm = 'pm';
		}
		timeField = timeField.split(':');
		if (timeField.length > 0) {
			if ((isAmPm == 'pm') && (parseInt(timeField[0], 10) == 12)) {
				timeField[0] = parseInt(timeField[0], 10) - 12;
			} else if((isAmPm == 'pm') && (parseInt(timeField[0], 10) < 12)) {
				timeField[0] = parseInt(timeField[0], 10) + 12;
			}
			if (timeField[0].length < 2) {
				timeField[0] = '0'+timeField[0].toString();
			}
			if (timeField[1].length < 2) {
				timeField[1] = '0'+timeField[1].toString();
			}
			isoDate += timeField[0]+':'+timeField[1];
		}
	} else {
		// European time (24-hours), such as:
		// 14:26
		var timeField = cleaned[1].split(':');
		if (timeField.length > 0) {
			if (timeField[0].length < 2) {
				timeField[0] = '0'+timeField[0].toString();
			}
			if (timeField[1].length < 2) {
				timeField[1] = '0'+timeField[1].toString();
			}
			isoDate += timeField[0]+':'+timeField[1];
		}
	}
	return isoDate;
}

TableKit.Sortable.addSortType(
	new TableKit.Sortable.Type('sort_date', {
		pattern: /^[pattern_dummy]$/,
		normal: dateSortFunc
	}
));

TableKit.Sortable.addSortType(
	new TableKit.Sortable.Type('sort_termin', {
		pattern: /^[pattern_dummy]$/,
		normal: dateSortFunc
	}
));

TableKit.Sortable.addSortType(
	new TableKit.Sortable.Type('sort_filesize', {
		pattern : /^[-+]?[\d]*\.?[\d]+(?:[eE][-+]?[\d]+)?\s?[k|m|g|t]b$/i,
		normal : function(v) {
			v = v.replace(/,/, '.');
			var r = v.match(/^([-+]?[\d]*\.?[\d]+([eE][-+]?[\d]+)?)\s?([k|m|g|t]?b)?/i);
			var b = r[1] ? Number(r[1]).valueOf() : 0;
			var m = r[3] ? r[3].substr(0,1).toLowerCase() : '';
			var result = b;
			switch(m) {
				case  'k':
					result = b * 1024;
					break;
				case  'm':				
					result = b * 1024 * 1024;
					break;
				case  'g':
					result = b * 1024 * 1024 * 1024;
					break;
				case  't':
					result = b * 1024 * 1024 * 1024 * 1024;
					break;
			}
			return result;
		}
	}
));
