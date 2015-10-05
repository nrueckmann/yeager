/**
 * @fileoverview Provides functionality for managing filetypes
 * @version 1.0
 */


/**
 * Saves all filetypes
 * @param { String } [wndid] Id of parent window
 */
$K.yg_saveFileTypes = function( which, winID ) {
	which = $(which);

	var hasError = false;
	var winRef = which.up('.ywindow');

	var listRef = $(winRef.id + '_CONFIG_FILE-TYPES');

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

	// Check for double identifiers/abbreviations/filetypes
	var codes = new Array();
	var abbreviations = new Array();
	var extensions = new Array();
	var legalChars = /^[a-zA-Z0-9\-\.\_]*$/;

	for(parameter_idx in parameters) {
		if (parameter_idx.indexOf('___NEW___')!=-1) {
			continue;
		}
		if (parameter_idx.endsWith('_title')) {
			if (parameters[parameter_idx].strip() == '') {
				var errField = $(document.forms['wid_'+winID+'_form'][parameter_idx]);
				errField.addClassName('error');
				if (errField.up('li').hasClassName('closed')) {
					$K.yg_listCollapsSwap(errField.up('li'), null);
				}
				hasError = true;
			} else {
				$(document.forms[0][parameter_idx]).removeClassName('error');
			}
		}
		if (parameter_idx.endsWith('_code')) {
			if ( (codes.indexOf(parameters[parameter_idx]) != -1) ||
				 (!legalChars.test(parameters[parameter_idx])) ||
				 (parameters[parameter_idx].strip() == '') ) {
				var errField = $(document.forms[0][parameter_idx]);
				errField.addClassName('error');
				if (errField.up('li').hasClassName('closed')) {
					$K.yg_listCollapsSwap(errField.up('li'), null);
				}
				hasError = true;
			} else {
				$(document.forms['wid_'+winID+'_form'][parameter_idx]).removeClassName('error');
			}
			codes.push(parameters[parameter_idx]);
		}
		if (parameter_idx.endsWith('_abbreviation')) {
			if ( (abbreviations.indexOf(parameters[parameter_idx]) != -1) ||
				 (!legalChars.test(parameters[parameter_idx])) ||
				 (parameters[parameter_idx].strip() == '') ) {
				var errField = $(document.forms['wid_'+winID+'_form'][parameter_idx]);
				errField.addClassName('error');
				if (errField.up('li').hasClassName('closed')) {
					$K.yg_listCollapsSwap(errField.up('li'), null);
				}
				hasError = true;
			} else {
				$(document.forms[0][parameter_idx]).removeClassName('error');
			}
			abbreviations.push(parameters[parameter_idx]);
		}
		if (parameter_idx.endsWith('_extensions')) {
			if (parameters[parameter_idx].indexOf(',') == -1) {
				parameters[parameter_idx] += ',';
			}
			var extensions_array = parameters[parameter_idx].toLowerCase().split(',');

			extensions_array.each(function(item,idx){
				extensions_array[idx] = extensions_array[idx].strip();
			});

			var hasExtError = false;
			extensions_array.each(function(item) {
				if (item != '') {
					if (extensions.indexOf(item) != -1) {
						var errField = $(document.forms['wid_'+winID+'_form'][parameter_idx]);
						errField.addClassName('error');
						if (errField.up('li').hasClassName('closed')) {
							$K.yg_listCollapsSwap(errField.up('li'), null);
						}
						hasError = true;
						hasExtError = true;
					}
				}
			});
			extensions = extensions.concat(extensions_array);
			if (!hasExtError) {
				$(document.forms['wid_'+winID+'_form'][parameter_idx]).removeClassName('error');
			}
		}
	}

	if (!hasError) {
		var data = Array ( 'noevent', {yg_property: 'saveFileTypes', params: parameters } );
		$K.yg_AjaxCallback( data, 'saveFileTypes' );
	}

}


/**
 * Adds a new empty filetype
 * @param { Element } [ref] The element where was clicked on.
 * @function
 * @name yg_addNewFileType
 */
$K.yg_addNewFileType = function( ref ) {
	ref = $(ref);

	var wid = ref.up('.ywindow').id;
	var newElement = $K.windows[wid].jsTemplate;

	var newId = 0;

	if (!$(wid+'_filetypes_list')) {
		return;
	}

	$(wid+'_filetypes_list').select('input[type=hidden]').each(function(item){
		if (item.name.endsWith('_filetype_ids[]') && item.value.startsWith('NEW_')) {
			newId = parseInt(item.value.replace(/NEW_/,''));
		}
	});
	newId++;
	newElement = newElement.replace(/__NEW__/g, 'NEW_'+newId);
	$(wid+'_filetypes_list').insert({bottom:newElement});

	var listChildren = $(wid+'_filetypes_list').childElements();
	newElement = listChildren[listChildren.length-1];

	$K.yg_customAttributeHandler( newElement );

	$K.windows[wid].refresh();
}


/**
 * Removes a filetype
 * @param { Element } [which] The element where was clicked on.
 * @function
 * @name yg_removeFileTypeEntry
 */
$K.yg_removeFileTypeEntry = function(which) {
	which = $(which);

	var winID = which.up('.ywindow').id;
	if (which.id.indexOf('_NEW_')==-1) {
		var hiddenFields = which.select('input[type=hidden]');
		var newElement = new Element('input', {
			type:	'hidden',
			name:	winID+'_filetype_del_ids[]',
			value:	hiddenFields[hiddenFields.length-1].value
		});
		$(winID+'_filetypes_list').insert({bottom:newElement});
	}
	which.remove();
	$K.windows[winID].refresh();
}
