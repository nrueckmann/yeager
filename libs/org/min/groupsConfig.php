<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/**
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 *
 * See http://code.google.com/p/minify/wiki/CustomSource for other ideas
 **/

return array(
	'js' => array(
		'//../../ui/js/framework/debug.js',
//		'//../../ui/js/3rd/firebug/firebug-lite.js',

		// Third Party Libraries
		'//../../ui/js/3rd/prototype/prototype.js',
		'//../../ui/js/3rd/prototype/prototype_patch.js',
		'//../../ui/js/3rd/scriptaculous/scriptaculous.js',
		'//../../ui/js/3rd/scriptaculous/builder.js',
		'//../../ui/js/3rd/scriptaculous/effects.js',
		'//../../ui/js/3rd/scriptaculous/dragdrop.js',
		'//../../ui/js/3rd/scriptaculous/controls.js',
		'//../../ui/js/3rd/scriptaculous/slider.js',
		'//../../ui/js/3rd/scriptaculous/sound.js',
		'//../../ui/js/3rd/nlstree/nlstree.js',
		'//../../ui/js/3rd/nlstree/nlsctxmenu.js',
		'//../../ui/js/3rd/nlstree/nlstreeext_ctx.js',
		'//../../ui/js/3rd/nlstree/nlstreeext_dd.js',
		'//../../ui/js/3rd/nlstree/nlstreeext_sel.js',
		'//../../ui/js/3rd/nlstree/nlstreeext_xml.js',
		'//../../ui/js/3rd/nlstree/nlstreeext_state.js',
		'//../../ui/js/3rd/nlstree/nlstreeext_inc.js',
		'//../../ui/js/3rd/calendar/calendarpopup.js',
		'//../../ui/js/3rd/cropper/cropper.js',
		'//../../ui/js/3rd/tablekit/tablekit_patched.js',
		'//../../ui/js/3rd/tablekit/tablekit_customsort.js',
		'//../../ui/js/3rd/flashdetect/flash_detect.js',
		'//../../ui/js/3rd/swfupload/swfupload.js',
		'//../../ui/js/3rd/swfupload/swfupload_callbacks.js',
		'//../../ui/js/3rd/plupload/js/plupload.full.js',
		'//../../ui/js/3rd/plupload/js/plupload_callbacks.js',

		// Yeager Framework
		'//../../ui/js/framework/logging.js',
		'//../../ui/js/framework/browser.js',
		'//../../ui/js/framework/tabs.js',
		'//../../ui/js/framework/scrollbars.js',
		'//../../ui/js/framework/helper.js',
		'//../../ui/js/framework/keyboard.js',
		'//../../ui/js/framework/windows.js',
		'//../../ui/js/framework/actionbuttons.js',
		'//../../ui/js/framework/ipanels.js',
		'//../../ui/js/framework/forms.js',
		'//../../ui/js/framework/hints.js',
		'//../../ui/js/framework/prompt.js',
		'//../../ui/js/framework/tree.js',
		'//../../ui/js/framework/controls.js',
		'//../../ui/js/framework/selectnode.js',
		'//../../ui/js/framework/sortable.js',
		'//../../ui/js/framework/pagedir.js',
		'//../../ui/js/framework/draganddrop.js',
		'//../../ui/js/framework/draganddrop_cases.js',
		'//../../ui/js/framework/domwrapper.js',
		'//../../ui/js/framework/locking.js',

		// Yeager Tabs
		'//../../ui/js/tabs/start_sections.js',
		'//../../ui/js/tabs/pages.js',
		'//../../ui/js/tabs/files.js',
		'//../../ui/js/tabs/filetypes.js',
		'//../../ui/js/tabs/properties.js',
		'//../../ui/js/tabs/mailings.js',
		'//../../ui/js/tabs/contentblock_list.js',
		'//../../ui/js/tabs/contentblocks.js',
		'//../../ui/js/tabs/frameset.js',
		'//../../ui/js/tabs/comments.js',
		'//../../ui/js/tabs/versions.js',
		'//../../ui/js/tabs/publishing.js',
		'//../../ui/js/tabs/views.js',
		'//../../ui/js/tabs/tags.js',
		'//../../ui/js/tabs/dropstack.js',
		'//../../ui/js/tabs/login.js',
		'//../../ui/js/tabs/appearance.js',
		'//../../ui/js/tabs/entrymask_config.js',
		'//../../ui/js/tabs/users.js',
		'//../../ui/js/tabs/usergroups.js',
		'//../../ui/js/tabs/extensions.js',
		'//../../ui/js/tabs/content.js',
		'//../../ui/js/tabs/preview.js',
		'//../../ui/js/tabs/trashcan.js',
		'//../../ui/js/tabs/templates.js',
		'//../../ui/js/tabs/sites.js',
		'//../../ui/js/tabs/contenteditor.js',
		'//../../ui/js/tabs/insertcontent.js',
		'//../../ui/js/tabs/navigation_select.js',
		'//../../ui/js/tabs/extension_select.js',
		'//../../ui/js/tabs/export.js',
		'//../../ui/js/tabs/formfield_select.js',
		'//../../ui/js/tabs/view_select.js',
		'//../../ui/js/tabs/upload.js',
		'//../../ui/js/tabs/link_select.js',
		'//../../ui/js/tabs/page_select.js',
		'//../../ui/js/tabs/contentblock_select.js',
		'//../../ui/js/tabs/filefolder_select.js',
		'//../../ui/js/tabs/file_select.js',
		'//../../ui/js/tabs/user_settings.js',
		'//../../ui/js/tabs/updates.js',
		'//../../ui/js/tabs/template_select.js'
	),
	'css1' => array(
		'//../../ui/css/onthefly.css',
		'//../../ui/css/tabs.css',
		'//../../ui/css/main.css',
		'//../../ui/css/tabcontent.css',
		'//../../ui/css/contentblocks.css',
		'//../../ui/css/scroll.css',
		'//../../ui/css/files.css'
	),
	'css2' => array(
		'//../../ui/css/actions.css',
		'//../../ui/css/nlstree.css',
		'//../../ui/css/nlsctxmenu.css',
		'//../../ui/css/calendar.css',
		'//../../ui/css/window.css',
		'//../../ui/css/dialog.css',
		'//../../ui/css/cropper.css'
	),
	'nlstree' => array('//../../ui/js/3rd/nlstree/nlstree.js'),
	'nlsctxmenu' => array('//../../ui/js/3rd/nlstree/nlsctxmenu.js'),
	'nlstreeext_ctx' => array('//../../ui/js/3rd/nlstree/nlstreeext_ctx.js'),
	'nlstreeext_dd' => array('//../../ui/js/3rd/nlstree/nlstreeext_dd.js'),
	'nlstreeext_sel' => array('//../../ui/js/3rd/nlstree/nlstreeext_sel.js'),
	'nlstreeext_xml' => array('//../../ui/js/3rd/nlstree/nlstreeext_xml.js'),
	'nlstreeext_state' => array('//../../ui/js/3rd/nlstree/nlstreeext_state.js'),
	'nlstreeext_inc' => array('//../../ui/js/3rd/nlstree/nlstreeext_inc.js')
);
