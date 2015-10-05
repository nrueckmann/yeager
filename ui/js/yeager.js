/**
 * @fileoverview The base file of yeager's AJAX framework (Koala).
 * This fiel loads all required javascript files and defines the
 * Koala object including file and imagepaths.
 * {literal}
 */

/**
 * The main Koala object
 * @class This is the basic Koala object/class.
 */
var Koala = {
	/**
	 * Contains the 'roof' version of the complete framework
	 * @type string
	 */
	version: '0.2.0',


	/**
	 * The absolute base path of the application
	 * @type string
	 */
	appdir: '{/literal}{$base}{literal}',


	/**
	 * The webroot of the application
	 * @type string
	 */
	webroot: '{/literal}{$webroot}{literal}',


	/**
	 * The cookie-domain of the application
	 * @type string
	 */
	cookiedomain: '{/literal}{$cookiedomain}{literal}',


	/**
	 * internal prefix including php-file of the application
	 * @type string
	 */
	internalprefix: '{/literal}{$internalprefix}{literal}',


	/**
	 * The base path where of app images
	 * @type string
	 */
	imgdir: '{/literal}{$docabsolut}{literal}ui/img/',


	/**
	 * The base path where the javascript files are located
	 * @type string
	 */
	jsdir: '{/literal}{$docabsolut}{literal}ui/js/',


	/**
	 * Used to include arbitrary javascript files without mentioning them in the HTML file
	 * @param { String } [script_filename] Specifies the filename of the javascript file to include
	 */
	require: function (script_filename) { document.write('<script type="text/javascript" src="' + script_filename + '"></script>'); },


	/**
	 * Constants for icons
	 */
	icons: {
		/** {/literal} **/
		{foreach name='icons' key='iconKey' item='iconImage' from=$icon}
			{$iconKey}: '{$iconImage}'{if !$smarty.foreach.icons.last},{/if}
		{/foreach}
		/** {literal} **/
	},


	/**
	 * User profile settings
	 */
	userSettings: {
		/** {/literal} **/
		weekStart: parseInt('{$userinfo.PROPS.WEEKSTART}', 10),
		dateFormat: '{$userinfo.PROPS.DATEFORMAT}'
		/** {literal} **/
	},


	/**
	 * Constants for use with the Logger
	 */
	Log: {
		DEBUG:	0xF0004,
		ERROR:	0xF0003,
		WARN:	0xF0002,
		INFO:	0xF0001,
		NONE:	0xF0000
	},


	/**
	 * RegEx for URL-Parsing
	 */
	urlParseRegEx: {/literal}{if $URLRegEx1}{$URLRegEx1}{else}null{/if}{literal},

	/**
	 * Specifies Loglevel
	 * @param { $K.Log } [loglevel] The Loglevel (a constant from the the Constant-List)
	 */
	loglevel:	0xF0000,
	ajaxFunctions: {},

	/**
	 * Specifies Upload-Framework
	 */
	uploadFramework: 'plupload',

	/**
	 * Specifies devmode
	 */
	devMode: {/literal}{if $devmode == "true"}true{else}false{/if}{literal},

	/**
	 * Specifies the interval for frontend-gui syncs
	 */
	guiSyncInterval: '{/literal}{$guiSyncInterval}{literal}',

	/**
	 * Specifies the timeout for frontend-gui syncs
	 */
	guiSyncTimeout: '{/literal}{$guiSyncTimeout}{literal}',

	/**
	 * Specifies the timeout for uploads
	 */
	uploadTimeout: 40000,

	/**
	 * Hold the last processed history-id
	 */
	currentGuiSyncHistoryId: false

};


var $K = Koala;
$K.loglevel = $K.Log['INFO'];

$K.require($K.jsdir+'framework/debug.js');							// Koala <=> Firebug Interface

{/literal}{if $devmode == 'true'}{literal}
if (!window.console || !window.console.firebug) {
	//$K.require($K.jsdir+'3rd/firebug/firebug-lite.js');			// Firebug Lite 1.3.1
}

// Third Party Libraries
$K.require($K.jsdir+'3rd/prototype/prototype.js');					// Prototype 1.7.0
$K.require($K.jsdir+'3rd/prototype/prototype_patch.js');			// Patches/Extensions for Prototype 1.7.0
$K.require($K.jsdir+'3rd/scriptaculous/scriptaculous.js');			// Scriptaculous 1.9.0
$K.require($K.jsdir+'3rd/scriptaculous/builder.js');				// 		-"-
$K.require($K.jsdir+'3rd/scriptaculous/effects.js');				// 		-"-
$K.require($K.jsdir+'3rd/scriptaculous/dragdrop.js');				// 		-"-
$K.require($K.jsdir+'3rd/scriptaculous/controls.js');				// 		-"-
$K.require($K.jsdir+'3rd/scriptaculous/slider.js');					// 		-"-
$K.require($K.jsdir+'3rd/scriptaculous/sound.js');					// 		-"-
$K.require($K.jsdir+'3rd/nlstree/nlstree.js');						// NLSTree Pro 2.3
$K.require($K.jsdir+'3rd/nlstree/nlsctxmenu.js');					// 		-"-
$K.require($K.jsdir+'3rd/nlstree/nlstreeext_ctx.js');				// 	 	-"-
$K.require($K.jsdir+'3rd/nlstree/nlstreeext_dd.js');				// 	 	-"-
$K.require($K.jsdir+'3rd/nlstree/nlstreeext_sel.js');				// 	 	-"-
$K.require($K.jsdir+'3rd/nlstree/nlstreeext_xml.js');				// 	 	-"-
$K.require($K.jsdir+'3rd/nlstree/nlstreeext_state.js');				// 	 	-"-
$K.require($K.jsdir+'3rd/nlstree/nlstreeext_inc.js');				// 	 	-"-
$K.require($K.jsdir+'3rd/calendar/calendarpopup.js');				// CalendarPopup
$K.require($K.jsdir+'3rd/cropper/cropper.js');						// Image Cropper
$K.require($K.jsdir+'3rd/tablekit/tablekit_patched.js');			// Tablekit
$K.require($K.jsdir+'3rd/tablekit/tablekit_customsort.js');			// Tablekit Custom Sort Functions
$K.require($K.jsdir+'3rd/flashdetect/flash_detect.js');				// FlashDetect 1.0.4
$K.require($K.jsdir+'3rd/swfupload/swfupload.js');					// SWFUpload 2.2.0.1
$K.require($K.jsdir+'3rd/swfupload/swfupload_callbacks.js');		// SWFUpload Yeager Addon
$K.require($K.jsdir+'3rd/plupload/js/plupload.full.js');			// PLUpload 1.5.2 (minified version)
/*
$K.require($K.jsdir+'3rd/plupload/js/src/plupload.js');				// PLUpload 1.5.2 (source version)
$K.require($K.jsdir+'3rd/plupload/js/src/plupload.gears.js');		// 		-"-
$K.require($K.jsdir+'3rd/plupload/js/src/plupload.silverlight.js');	// 		-"-
$K.require($K.jsdir+'3rd/plupload/js/src/plupload.flash.js');		// 		-"-
$K.require($K.jsdir+'3rd/plupload/js/src/plupload.html4.js');		// 		-"-
$K.require($K.jsdir+'3rd/plupload/js/src/plupload.html5.js');		// 		-"-
*/
$K.require($K.jsdir+'3rd/plupload/js/plupload_callbacks.js');		// PLUpload Yeager Addon

// Yeager Framework
$K.require($K.jsdir+'framework/logging.js');			//
$K.require($K.jsdir+'framework/browser.js');			//
$K.require($K.jsdir+'framework/tabs.js');				//
$K.require($K.jsdir+'framework/scrollbars.js');			//
$K.require($K.jsdir+'framework/helper.js');				//
$K.require($K.jsdir+'framework/keyboard.js');			//
$K.require($K.jsdir+'framework/windows.js');			//
$K.require($K.jsdir+'framework/actionbuttons.js');		//
$K.require($K.jsdir+'framework/ipanels.js');			//
$K.require($K.jsdir+'framework/forms.js');				//
$K.require($K.jsdir+'framework/hints.js');				//
$K.require($K.jsdir+'framework/prompt.js');				//
$K.require($K.jsdir+'framework/tree.js');				//
$K.require($K.jsdir+'framework/controls.js');			//
$K.require($K.jsdir+'framework/selectnode.js');			//
$K.require($K.jsdir+'framework/sortable.js');			//
$K.require($K.jsdir+'framework/pagedir.js');			//
$K.require($K.jsdir+'framework/draganddrop.js');		//
$K.require($K.jsdir+'framework/draganddrop_cases.js');	//
$K.require($K.jsdir+'framework/domwrapper.js');			//
$K.require($K.jsdir+'framework/locking.js');			//


// Yeager Tabs
$K.require($K.jsdir+'tabs/start_sections.js');			//
$K.require($K.jsdir+'tabs/pages.js');					//
$K.require($K.jsdir+'tabs/files.js');					//
$K.require($K.jsdir+'tabs/filetypes.js');				//
$K.require($K.jsdir+'tabs/properties.js');				//
$K.require($K.jsdir+'tabs/mailings.js');				//
$K.require($K.jsdir+'tabs/contentblock_list.js');		//
$K.require($K.jsdir+'tabs/contentblocks.js');			//
$K.require($K.jsdir+'tabs/frameset.js');				//
$K.require($K.jsdir+'tabs/comments.js');				//
$K.require($K.jsdir+'tabs/versions.js');				//
$K.require($K.jsdir+'tabs/publishing.js');				//
$K.require($K.jsdir+'tabs/views.js');					//
$K.require($K.jsdir+'tabs/tags.js');					//
$K.require($K.jsdir+'tabs/dropstack.js');				//
$K.require($K.jsdir+'tabs/login.js');					//
$K.require($K.jsdir+'tabs/appearance.js');				//
$K.require($K.jsdir+'tabs/entrymask_config.js');		//
$K.require($K.jsdir+'tabs/users.js');					//
$K.require($K.jsdir+'tabs/usergroups.js');				//
$K.require($K.jsdir+'tabs/extensions.js');				//
$K.require($K.jsdir+'tabs/content.js');					//
$K.require($K.jsdir+'tabs/preview.js');					//
$K.require($K.jsdir+'tabs/trashcan.js');				//
$K.require($K.jsdir+'tabs/templates.js');				//
$K.require($K.jsdir+'tabs/sites.js');					//
$K.require($K.jsdir+'tabs/contenteditor.js');			//
$K.require($K.jsdir+'tabs/insertcontent.js');			//
$K.require($K.jsdir+'tabs/navigation_select.js');		//
$K.require($K.jsdir+'tabs/extension_select.js');		//
$K.require($K.jsdir+'tabs/export.js');					//
$K.require($K.jsdir+'tabs/formfield_select.js');		//
$K.require($K.jsdir+'tabs/view_select.js');				//
$K.require($K.jsdir+'tabs/upload.js');					//
$K.require($K.jsdir+'tabs/link_select.js');				//
$K.require($K.jsdir+'tabs/page_select.js');				//
$K.require($K.jsdir+'tabs/contentblock_select.js');		//
$K.require($K.jsdir+'tabs/filefolder_select.js');		//
$K.require($K.jsdir+'tabs/file_select.js');				//
$K.require($K.jsdir+'tabs/user_settings.js');			//
$K.require($K.jsdir+'tabs/updates.js');					//
$K.require($K.jsdir+'tabs/template_select.js');			//
{/literal}{/if}{literal}

$K.require($K.jsdir+'3rd/tinymce/tiny_mce.js');						// TinyMCE 3.4
$K.require($K.jsdir+'3rd/tinymce/tiny_mce_yeager_addons.js');		// TinyMCE Yeager Addon

/** {/literal} **/
$K.isAuthenticated = ('{$is_authenticated}' == '1')?(true):(false);
$K.maxUploadSize = '{$max_uploadsize}';
$K.objectRelockInterval = '{$objectRelockInterval}';
/** {literal} **/

/** {/literal} **/
