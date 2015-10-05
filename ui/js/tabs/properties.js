/**
 * @fileoverview Provides generic functionality for managing properties
 * @version 1.0
 */

/**
 * Saves all filetypes
 * @param { String } [wndid] Id of parent window
 */
$K.yg_saveProperties = function( which, winID ) {
	which = $(which);

	var hasError = false;
	var tooShort = false;
	var winRef = which.up('.ywindow');
	var currentTab = $K.windows[winRef.id].tab.toLowerCase();
	var listRef = $(winRef.id + '_formfields_list');
	var allFields = listRef.select('input');
	var parameters = {
			wid: winRef.id
	};

	allFields.each(function(item){
		if (item.name) {
			if (parameters[item.name] != undefined) {
				parameters[item.name] += ','+item.value;
			} else {
				parameters[item.name] = item.value;
			}
		}
	});

	// Check for double/empty names/identifiers
	var names = new Array();
	var identifiers = new Array();
	var legalChars = /^[a-zA-Z0-9\.\_]*$/;

	for(parameter_idx in parameters) {
		if (parameter_idx.indexOf('___NEW___')!=-1) {
			continue;
		}
		if (parameter_idx.endsWith('_name')) {
			if ( (parameters[parameter_idx].strip() == '') ) {
				var errField = $('wid_'+winID).down('input[name='+parameter_idx+']');
				errField.addClassName('error');
				if (errField.up('li').hasClassName('closed')) {
					$K.yg_listCollapsSwap(errField.up('li'), null);
				}
				hasError = true;
			} else {
				$('wid_'+winID).down('input[name='+parameter_idx+']').removeClassName('error');
			}
			names.push(parameters[parameter_idx]);
		}

		if (parameter_idx.endsWith('_tsuffix')) {
			if ( (identifiers.indexOf(parameters[parameter_idx]) != -1) ||
				 (parameters[parameter_idx].indexOf(' ') != -1) ||
				 (parameters[parameter_idx].indexOf('-') != -1) ||
				 (!legalChars.test(parameters[parameter_idx])) ||
				 (parameters[parameter_idx].strip() == '') ) {
				var errField = $('wid_'+winID).down('input[name='+parameter_idx+']');
				errField.addClassName('error');
				if (errField.up('li').hasClassName('closed')) {
					$K.yg_listCollapsSwap(errField.up('li'), null);
				}
				hasError = true;
			} else {
				$('wid_'+winID).down('input[name='+parameter_idx+']').removeClassName('error');
			}
			identifiers.push(parameters[parameter_idx]);
		}
	}

	if (!hasError) {
		var data = Array ( 'noevent', {yg_property: 'saveProperties', params: parameters } );
		$K.yg_AjaxCallback( data, 'saveProperties' );
	}

}




/**
 * Helper function for changing an object's property
 * @param { Element } [which] The element which was changed.
 * @param { Element } [value] The value (optional, if not set it will be extracted from 'which')
 * @function
 * @name yg_setObjectProperty
 */
$K.yg_setObjectProperty = function( which, value ) {
	which = $(which);
	whichobj = $(which).up('.hoverable');
	if (whichobj && value) {
		if (whichobj.hasClassName('mk_tag')) whichobj.down('.icn').className = "icn icontag";
		if (whichobj.hasClassName('mk_page')) whichobj.down('.icn').className = "icn iconpage";
		if (whichobj.hasClassName('mk_cblock')) whichobj.down('.icn').className = "icn iconcblock";
		if (whichobj.hasClassName('mk_link')) {
			if (value.objecttype == "page")	whichobj.down('.icn').className = "icn iconpage";
			if (value.objecttype == "link")	whichobj.down('.icn').className = "icn iconlink";
			if (value.objecttype == "email") whichobj.down('.icn').className = "icn iconemail";
		}
	}

	if (which.hasClassName('disabled')) return;

	var fieldData = $K.yg_getAttributes( which );

	if (value) {
		fieldData.value = value;
	} else if ( (which.tagName == 'INPUT') || (which.tagName == 'TEXTAREA') ) {
		fieldData.value = which.value;
	} else if (which.tagName == 'DIV') {
		fieldData.value = which.innerHTML;
	}

	var data = Array ( 'change', fieldData );
	$K.yg_AjaxCallback( data, 'setObjectProperty' );
}
