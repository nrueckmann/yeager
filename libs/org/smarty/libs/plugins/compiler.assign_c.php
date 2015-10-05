<?php

function smarty_compiler_assign_c($tag_args, &$smarty) {
	$_attrs = $smarty->_parse_attrs($tag_args);

	if (isset($_attrs['var'])) {
		/* varname like in assign */
		$_lval = '$this->_tpl_vars['.$_attrs['var'].']';
	} elseif (isset($_attrs['lvar'])) {
		/* variable */
		if ($_attrs['lvar']{0}!='$') {
		$smarty->_syntax_error('attribute "lvar" must be a variable',
		E_USER_WARNING, __FILE__, __LINE__);
		return;
		}
		$_lval = $_attrs['lvar'];
	} else {
		$smarty->_syntax_error('missing attribute "var"',
		E_USER_WARNING, __FILE__, __LINE__);
		return;
	}

	if (isset($_attrs['value'])) {
		/* scalar value */
		$_rval = $_attrs['value'];
	} elseif (isset($_attrs['values'])) {
		/* list of array-values */
		$_delim = (isset($_attrs['delim'])) ? $_attrs['delim'] : "','";
		$_rval = 'explode(' . $_delim . ', ' . $_attrs['values'] . ')';

		if (isset($_attrs['keys'])) {
			/* optional list of array-keys */
			$_code = "\$_values = $_rval; $_lval = array(); ";
			$_code .= " foreach(explode($_delim, ".$_attrs['keys'].") as \$_i=>\
			$_key) {";
			$_code .= " ${_lval}[\$_key] = \$_values[\$_i]; }";
			return $_code;
		}
	} else {
		$smarty->_syntax_error('missing attribute "value"',
		E_USER_WARNING, __FILE__, __LINE__);
		return;
	}
	
	return $_lval . '=' . $_rval . ';';
}
?>