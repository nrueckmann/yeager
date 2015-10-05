<?php

/***************************
 * Custom smarty modifiers
 */

function formatFileSize($filesize) {

	$filesize = (int)$filesize;

	if ($filesize >= 1000000000) {
		$filesize = $filesize / (1024 * 1024 * 1024);
		$unit = 'GB';
	} else if ($filesize >= 1000000) {
		$filesize = $filesize / (1024 * 1024);
		$unit = 'MB';
	} else if ($filesize >= 1000) {
		$filesize = $filesize / 1024;
		$unit = 'KB';
	}

	if (($filesize >= 100) || ($unit == 'KB')) {
		$filesize = round($filesize);
	} else if ($unit != '') {
		$filesize = number_format($filesize, 1, ',', '.');
	}

	if (!$unit) {
		$unit = 'Bytes';
	}

	$fmt_filesize = $filesize . ' ' . $unit;

	return $fmt_filesize;
}

function escapeAllQuotes($str) {
	return str_replace('"', '&quot;', str_replace("'", "\'", $str));
}

function formatDate($string, $format) {
	return date($format, $string);
}

function getArrayFirstIndex($arr) {
	foreach ($arr as $key => $value)
	return $key;
}

function nestTree(&$nodes, $sortKey, $i) {
	$new = array();
	if (count($nodes) < 1) return;
	$i = (int)$i;
	while (list(, $node) = each($nodes)) {
		$new[$node['ID']] = $node;
		$next_id = key($nodes);
		if (($node['LEVEL'] < $nodes[$next_id]['LEVEL'])) {
			$ncc = nestTree($nodes, $sortKey, $i);
			if (($ncc[getArrayFirstIndex($ncc)]['LFT']) > 0) {
				$new[$node['ID']]['CHILDREN'] = $ncc;
			}
		}
		$next_id = key($nodes);
		if ($next_id && $nodes[$next_id]['PARENT'] != $node['PARENT']) {
			/* SORTING */
			if($sortKey != '') {
				usort($new, function($a, $b) {
					return strcasecmp($a['NAME'], $b['NAME']);
				});
			}
			/* SORTING */
			return $new;
		}
	}
	/* SORTING */
	if($sortKey != '') {
		usort($new, function($a, $b) {
			return strcasecmp($a['NAME'], $b['NAME']);
		});
	}
	/* SORTING */
	$i++;
	return $new;
}

/**
 * Purpose: Smarty resource plugin fetches template from a global variable
 * Version: 1.0 [Sep 28, 2002 boots]
 */
if (!function_exists("smarty_resource_var_source")) {
	function smarty_resource_var_source($tpl_name, &$tpl_source, &$smarty) {
		global $$tpl_name;
		$tpl_source = $tpl_name;
		return true;
	}
	function smarty_resource_var_timestamp($tpl_name, &$tpl_timestamp, &$smarty) {
		$tpl_timestamp = '';
		return true;
	}
	function smarty_resource_var_secure($tpl_name, &$smarty) {
		return true;
	}
	function smarty_resource_var_trusted($tpl_name, &$smarty) {
		return;
	}
}
$smarty->register_resource('var', array('smarty_resource_var_source', 'smarty_resource_var_timestamp', 'smarty_resource_var_secure', 'smarty_resource_var_trusted'));
$smarty->register_modifier('filesize', 'formatFileSize');
$smarty->register_modifier('date_format_php', 'formatDate');
$smarty->register_modifier('nest_tree', 'nestTree');
$smarty->register_modifier('escape_quotes', 'escapeAllQuotes');

?>