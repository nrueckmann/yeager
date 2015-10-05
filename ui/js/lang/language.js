{literal}
$K.TXT = function(txt_const) {
	if ($K.yg_texts[txt_const]) {
		return $K.yg_texts[txt_const];
	} else {
		return '$'+txt_const;
	}
}
{/literal}

$K.yg_texts = {literal}{{/literal}

{foreach from=$itext_js key='itext_key' item='itext_item' name='itext'}
	{$itext_key}: '{$itext_item}'{if !$smarty.foreach.itext.last},{/if}	
{/foreach}

{literal}}{/literal};