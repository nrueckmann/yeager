<?xml version="1.0"?>
<!-- <?php die(); ?> -->
<CONFIG>
	<ADMIN>
		<EMAIL>wwwadmin@aquarium</EMAIL>
	</ADMIN>
	<PATH>
		<!--TMP>/tmp/</TMP-->
	</PATH>
	<DEVMODE>false</DEVMODE>
	<LOGGING>
		<!--
		***************************************************************
		*
		* Logging
		*
		***************************************************************

		FILE	=	full local path to log file
					leave empty for none

		LEVEL	=	set log level
						ERROR	1
						WARN	2
						NORMAL	3
						DEBUG	4

		WWW		=	full local path to apache style www log file

		Note: If you put the placeholder [YEAR], [MONTH], [DAY] in the
		file name than these will resolve to the corresponding date info.
		-->
		<BUCKETS>
			<BUCKET TYPE="FILE" URI="[SYSTMP]/nas-[YEAR][MONTH][DAY].log" LEVEL="0" />
			<BUCKET TYPE="PLUGIN" URI="Echo" LEVEL="0" />
			<BUCKET TYPE="PLUGIN" URI="FireBug" LEVEL="0" />
		</BUCKETS>
	</LOGGING>
	<SESSION>
		<HANDLER>php</HANDLER>
		<COOKIES>
			<SCOPE>/</SCOPE>
			<TIME></TIME>
			<DOMAIN></DOMAIN>
			<SECRET>asmpfapsmodfim12f032fm89fm</SECRET>
		</COOKIES>
	</SESSION>
	<CACHE>
		<FRONTEND>false</FRONTEND>
		<SMARTY_FORCECOMPILE>false</SMARTY_FORCECOMPILE>
	</CACHE>
	<DB>
		<DSN id="yeager" driver="mysql" host="127.0.0.1:3306" user="root" password="" db="db_yeager" />
	</DB>
	<ERRORPAGES>
		<ERROR_404></ERROR_404>
		<ERROR_403></ERROR_403>
	</ERRORPAGES>
	<SCHEDULER>1</SCHEDULER>
	<SCHEDULER_TIMEOUT>86400</SCHEDULER_TIMEOUT>
    <!-- SCHEDULER_PAGES>responder</SCHEDULER_PAGES --><!-- page,responder,tab_FOLDERCONTENT //-->
	<SCHEDULER_HOST></SCHEDULER_HOST> <!-- http://127.0.0.1/ //-->
	<TIMEZONES>
		<FRONTEND>Europe/Berlin</FRONTEND>
	</TIMEZONES>
	<CASE_SENSITIVE_URLS>0</CASE_SENSITIVE_URLS>
	<GUISYNC_INTERVAL>25</GUISYNC_INTERVAL>
	<GUISYNC_TIMEOUT>60</GUISYNC_TIMEOUT>
	<OBJECTLOCK_TIMEOUT>20</OBJECTLOCK_TIMEOUT>
	<OBJECTRELOCK_INTERVAL>10</OBJECTRELOCK_INTERVAL>
	<PAGEDIR>
		<DEFAULT_PER_PAGE>40</DEFAULT_PER_PAGE>
	</PAGEDIR>
	<SYSTEMUSERS>
		<ROOTUSERID>1</ROOTUSERID>
		<ROOTGROUPID>1</ROOTGROUPID>
		<ANONUSERID>2</ANONUSERID>
		<ANONGROUPID>2</ANONGROUPID>
	</SYSTEMUSERS>
	<DIRECTORIES>
		<WEBROOT>/</WEBROOT>
		<TEMPLATEPREVIEWDIR>../site/frontend/previews/</TEMPLATEPREVIEWDIR>
		<TEMPLATEDIR>../site/frontend/</TEMPLATEDIR>
		<TEMPLATEDOC>/site/frontend/</TEMPLATEDOC>
		<DOCPATH>/yeager/</DOCPATH>
		<FILESDIR>../yeager/files/</FILESDIR>
		<FILESDOC>/yeager/files/</FILESDOC>
		<USERPICDIR>../yeager/files/</USERPICDIR>
		<UPDATES>updates/</UPDATES>
		<MODULES>../yeager/modules/</MODULES>
		<FILES_PROCS>processors/</FILES_PROCS>
		<PAGE_PROCS>processors/</PAGE_PROCS>
		<CBLOCK_PROCS>processors/</CBLOCK_PROCS>
		<EMAIL_PROCS>processors/</EMAIL_PROCS>
		<EXTENSIONSDIR>extensions/</EXTENSIONSDIR>
		<EXTENSIONSDOC>/yeager/extensions/</EXTENSIONSDOC>
		<PROCESSORSDIR>processors/</PROCESSORSDIR>
		<LOGINURL></LOGINURL>
	</DIRECTORIES>
	<MAILINGS>
		<ABSOLUTE_DOMAIN></ABSOLUTE_DOMAIN>
		<DISABLE>0</DISABLE>
		<FORCE_RECIPIENT></FORCE_RECIPIENT>
		<SMTP></SMTP>
	</MAILINGS>
	<RESERVED_SITENAMES>yeager,download,image</RESERVED_SITENAMES>
	<DEFAULT_LANGUAGE>EN</DEFAULT_LANGUAGE>
	<UPLOAD_PERMISSIONS>0666</UPLOAD_PERMISSIONS>
	<MODULES>
	</MODULES>
	<FILES_PROCESSORS>
		<PROCESSOR dir="procGD" name="GD" classname="gdproc" />
		<PROCESSOR dir="procYGSOURCE" name="YGSOURCE" classname="ygsource" />
	</FILES_PROCESSORS>
	<PAGE_PROCESSORS>
		<PROCESSOR dir="procPagePublish" name="PUBLISH" classname="pagepublish" actioncode="SCH_AUTOPUBLISH" />
	</PAGE_PROCESSORS>
	<CBLOCK_PROCESSORS>
		<PROCESSOR dir="procCBlockPublish" name="PUBLISH" classname="cblockpublish" actioncode="SCH_AUTOPUBLISH" />
	</CBLOCK_PROCESSORS>
	<EMAIL_PROCESSORS>
		<PROCESSOR dir="procEmailSend" name="SEND" classname="emailsend" />
	</EMAIL_PROCESSORS>
	<REFTRACKER>
		<INTERNALPREFIX>/yeager/y.php/</INTERNALPREFIX>
	</REFTRACKER>
	<EMBEDDED_CBLOCKFOLDER>99999</EMBEDDED_CBLOCKFOLDER>
	<PAGES>
		<DEFAULT>
			<CODE>ui/html/frameset/frameset.php</CODE>
			<TEMPLATE>ui/html/frameset/frameset.html</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</DEFAULT>
		<CSSTEST>
			<CODE>ui/html/frameset/frameset.php</CODE>
			<TEMPLATE>ui/_vorlagen/csstest_file.html</TEMPLATE>
		</CSSTEST>
		<CSSTEST_DIALOG>
			<CODE>ui/html/frameset/frameset.php</CODE>
			<TEMPLATE>ui/_vorlagen/csstest_dialog.html</TEMPLATE>
		</CSSTEST_DIALOG>
		<UPDATER>
			<CODE>bridge/updater/updater.php</CODE>
			<TEMPLATE>bridge/updater/updater.html</TEMPLATE>
			<FRONTEND>true</FRONTEND>
		</UPDATER>
		<DEREFERRER>
			<CODE>bridge/dereferrer/dereferrer.php</CODE>
			<TEMPLATE>bridge/dereferrer/dereferrer.html</TEMPLATE>
			<FRONTEND>true</FRONTEND>
		</DEREFERRER>
		<HOME>
			<CODE>ui/html/frameset/frameset.php</CODE>
			<TEMPLATE>ui/html/frameset/frameset.html</TEMPLATE>
		</HOME>
		<DEPRECATED_BROWSER>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/windows/deprecated_browser.html</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</DEPRECATED_BROWSER>
		<RESPONDER>
			<CODE>bridge/responder/responder.php</CODE>
			<TEMPLATE>bridge/responder/responder.html</TEMPLATE>
		</RESPONDER>
		<FETCHEXPORT>
			<CODE>bridge/export/fetchexport.php</CODE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</FETCHEXPORT>

		<!-- Yeager CORE functionality -->
		<MAILING>
			<CODE>output/mailing.php</CODE>
			<FRONTEND>true</FRONTEND>
		</MAILING>
		<PAGE>
			<CODE>output/page.php</CODE>
			<FRONTEND>true</FRONTEND>
			<CACHE>page</CACHE>
		</PAGE>
		<LIVEEDIT>
			<CODE>output/liveedit.php</CODE>
			<FRONTEND>true</FRONTEND>
		</LIVEEDIT>
		<IMAGE>
			<CODE>output/image.php</CODE>
			<FRONTEND>true</FRONTEND>
		</IMAGE>
		<USERIMAGE>
			<CODE>output/userimage.php</CODE>
			<FRONTEND>true</FRONTEND>
		</USERIMAGE>
		<DOWNLOAD>
			<CODE>output/download.php</CODE>
			<FRONTEND>true</FRONTEND>
		</DOWNLOAD>
		<SCHEDULER>
			<CODE>output/scheduler.php</CODE>
			<FRONTEND>true</FRONTEND>
		</SCHEDULER>
		<!-- Yeager CORE functionality END -->

		<!-- JAVASCRIPT <> PHP-Bridge -->
		<YEAGER.JS>
			<CODE>ui/js/yeager.php</CODE>
			<TEMPLATE>ui/js/yeager.js</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</YEAGER.JS>
		<!-- JAVASCRIPT <> PHP-Bridge -->

		<!-- WINDOW START -->
		<WINDOW>
			<CODE>ui/html/windows/window.php</CODE>
			<TEMPLATE>ui/html/windows/window.html</TEMPLATE>
		</WINDOW>
		<!-- WINDOW END -->

		<!-- MAINNAV -->
		<AUTHCONTENT>
			<CODE>ui/html/frameset/authcontent.php</CODE>
			<TEMPLATE>ui/html/frameset/authcontent.html</TEMPLATE>
		</AUTHCONTENT>
		<!-- MAINNAV END -->

		<!-- LOGIN START -->
		<LOGIN_EMPTY></LOGIN_EMPTY>
		<!-- LOGIN END -->

		<!-- TABS START -->
		<TAB_PAGES_TREE>
			<CODE>ui/html/tabs/pages_tree/tree_pages.php</CODE>
			<TEMPLATE>ui/html/tabs/pages_tree/tree_pages.html</TEMPLATE>
		</TAB_PAGES_TREE>
		<TAB_PAGES_TREE_EXTRAS>
			<CODE>ui/html/tabs/pages_tree/tree_pages.php</CODE>
			<TEMPLATE>ui/html/tabs/pages_tree/tree_pages_extras.html</TEMPLATE>
		</TAB_PAGES_TREE_EXTRAS>
		<TAB_FILES_TREE>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/files_tree/tree_files.html</TEMPLATE>
		</TAB_FILES_TREE>
		<TAB_TAGS_TREE>
			<CODE>ui/html/tabs/tags_tree/tree_tags.php</CODE>
			<TEMPLATE>ui/html/tabs/tags_tree/tree_tags.html</TEMPLATE>
		</TAB_TAGS_TREE>
		<TAB_TAG_ADD>
			<CODE>ui/html/tabs/tag_add/tag_add.php</CODE>
			<TEMPLATE>ui/html/tabs/tag_add/tag_add.html</TEMPLATE>
		</TAB_TAG_ADD>
		<TAB_TEMPLATES_TREE>
			<CODE>ui/html/tabs/templates_tree/tree_templates.php</CODE>
			<TEMPLATE>ui/html/tabs/templates_tree/tree_templates.html</TEMPLATE>
		</TAB_TEMPLATES_TREE>
		<TAB_ENTRYMASKS_TREE>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/entrymasks_tree/tree_entrymasks.html</TEMPLATE>
		</TAB_ENTRYMASKS_TREE>
		<TAB_CONTENTBLOCKS_TREE>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contentblocks_tree/tree_contentblocks.html</TEMPLATE>
		</TAB_CONTENTBLOCKS_TREE>
		<TAB_CONTENTBLOCK_LIST>
			<CODE>ui/html/tabs/contentblock_list/contentblock_list.php</CODE>
			<TEMPLATE>ui/html/tabs/contentblock_list/contentblock_list.html</TEMPLATE>
		</TAB_CONTENTBLOCK_LIST>
		<TAB_TEMPLATE_INFO>
			<CODE>ui/html/tabs/template_info/template_info.php</CODE>
			<TEMPLATE>ui/html/tabs/template_info/template_info.html</TEMPLATE>
		</TAB_TEMPLATE_INFO>
		<TAB_NAVIGATIONS>
			<CODE>ui/html/tabs/navigations/navigations.php</CODE>
			<TEMPLATE>ui/html/tabs/navigations/navigations.html</TEMPLATE>
		</TAB_NAVIGATIONS>
		<TAB_TIMEFRAME>
			<CODE>ui/html/tabs/timeframe/timeframe.php</CODE>
			<TEMPLATE>ui/html/tabs/timeframe/timeframe.html</TEMPLATE>
		</TAB_TIMEFRAME>
		<TAB_EXTENSION_LIST>
			<CODE>ui/html/tabs/extension_list/extension_list.php</CODE>
			<TEMPLATE>ui/html/tabs/extension_list/extension_list.html</TEMPLATE>
		</TAB_EXTENSION_LIST>
		<TAB_DATA_EXPORTIMPORT>
			<CODE>ui/html/tabs/data_exportimport/data_exportimport.php</CODE>
			<TEMPLATE>ui/html/tabs/data_exportimport/data_exportimport.html</TEMPLATE>
		</TAB_DATA_EXPORTIMPORT>
		<TAB_APPEARANCE>
			<CODE>ui/html/tabs/appearance/appearance.php</CODE>
			<TEMPLATE>ui/html/tabs/appearance/appearance.html</TEMPLATE>
		</TAB_APPEARANCE>
		<TAB_USER_SETTINGS>
			<CODE>ui/html/tabs/user_settings/user_settings.php</CODE>
			<TEMPLATE>ui/html/tabs/user_settings/user_settings.html</TEMPLATE>
		</TAB_USER_SETTINGS>
		<TAB_USER_INFO>
			<CODE>ui/html/tabs/user_info/user_info.php</CODE>
			<TEMPLATE>ui/html/tabs/user_info/user_info.html</TEMPLATE>
		</TAB_USER_INFO>
		<TAB_CLEARING>
			<CODE>ui/html/tabs/clearing/clearing.php</CODE>
			<TEMPLATE>ui/html/tabs/clearing/clearing.html</TEMPLATE>
		</TAB_CLEARING>
		<TAB_CONTENT>
			<CODE>ui/html/tabs/content/content.php</CODE>
			<TEMPLATE>ui/html/tabs/content/content.html</TEMPLATE>
		</TAB_CONTENT>
		<TAB_EXTENSIONS>
			<CODE>ui/html/tabs/extensions/extensions.php</CODE>
			<TEMPLATE>ui/html/tabs/content/content.html</TEMPLATE>
		</TAB_EXTENSIONS>
		<TAB_FILEINFO>
			<CODE>ui/html/tabs/file_info/file_info.php</CODE>
			<TEMPLATE>ui/html/tabs/file_info/file_info.html</TEMPLATE>
		</TAB_FILEINFO>
		<TAB_VIEWS>
			<CODE>ui/html/tabs/views/views.php</CODE>
			<TEMPLATE>ui/html/tabs/views/views.html</TEMPLATE>
		</TAB_VIEWS>
		<TAB_VIEW_SELECT>
			<CODE>ui/html/tabs/view_select/view_select.php</CODE>
			<TEMPLATE>ui/html/tabs/view_select/view_select.html</TEMPLATE>
		</TAB_VIEW_SELECT>
		<TAB_DROPSTACK>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/dropstack/dropstack.html</TEMPLATE>
		</TAB_DROPSTACK>
		<TAB_USERLIST>
			<CODE>ui/html/tabs/user_list/user_list.php</CODE>
			<TEMPLATE>ui/html/tabs/user_list/user_list.html</TEMPLATE>
		</TAB_USERLIST>
		<TAB_FOLDERCONTENT>
			<CODE>ui/html/tabs/files_foldercontent/files_foldercontent.php</CODE>
			<TEMPLATE>ui/html/tabs/files_foldercontent/files_foldercontent.html</TEMPLATE>
		</TAB_FOLDERCONTENT>
		<TAB_VERSIONS>
			<CODE>ui/html/tabs/versions/versions.php</CODE>
			<TEMPLATE>ui/html/tabs/versions/versions.html</TEMPLATE>
		</TAB_VERSIONS>
		<TAB_PROPERTIES>
			<CODE>ui/html/tabs/properties/properties.php</CODE>
			<TEMPLATE>ui/html/tabs/properties/properties.html</TEMPLATE>
		</TAB_PROPERTIES>
		<TAB_HTTP>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/http/http.html</TEMPLATE>
		</TAB_HTTP>
		<TAB_SOFTSYNC>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/softsync/softsync.html</TEMPLATE>
		</TAB_SOFTSYNC>
		<TAB_LINKS>
			<CODE>ui/html/tabs/links/links.php</CODE>
			<TEMPLATE>ui/html/tabs/links/links.html</TEMPLATE>
		</TAB_LINKS>
		<TAB_TAGLINKS>
			<CODE>ui/html/tabs/tag_links/tag_links.php</CODE>
			<TEMPLATE>ui/html/tabs/tag_links/tag_links.html</TEMPLATE>
		</TAB_TAGLINKS>
		<TAB_PUBLISHING>
			<CODE>ui/html/tabs/publishing/publishing.php</CODE>
			<TEMPLATE>ui/html/tabs/publishing/publishing.html</TEMPLATE>
		</TAB_PUBLISHING>
		<TAB_COMMENTS>
			<CODE>ui/html/tabs/comments/comments.php</CODE>
			<TEMPLATE>ui/html/tabs/comments/comments.html</TEMPLATE>
		</TAB_COMMENTS>
		<TAB_MAILINGS>
			<CODE>ui/html/tabs/mailings/mailings.php</CODE>
			<TEMPLATE>ui/html/tabs/mailings/mailings.html</TEMPLATE>
		</TAB_MAILINGS>
		<TAB_MAILING_PROPERTIES>
			<CODE>ui/html/tabs/mailing_properties/mailing_properties.php</CODE>
			<TEMPLATE>ui/html/tabs/mailing_properties/mailing_properties.html</TEMPLATE>
		</TAB_MAILING_PROPERTIES>
		<TAB_MAILING_TEST>
			<CODE>ui/html/tabs/mailing_test/mailing_test.php</CODE>
			<TEMPLATE>ui/html/tabs/mailing_test/mailing_test.html</TEMPLATE>
		</TAB_MAILING_TEST>
		<ELEMENT_AUTOPUBLISHBLOCK>
			<CODE>ui/html/tabs/publishing/autopublish.inc.php</CODE>
			<TEMPLATE>ui/html/tabs/publishing/autopublish.inc.html</TEMPLATE>
		</ELEMENT_AUTOPUBLISHBLOCK>
		<TAB_TAGS>
			<CODE>ui/html/tabs/tags/tags.php</CODE>
			<TEMPLATE>ui/html/tabs/tags/tags.html</TEMPLATE>
		</TAB_TAGS>
		<TAB_CONTENTEDITOR>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor/contenteditor.html</TEMPLATE>
		</TAB_CONTENTEDITOR>
		<TAB_CONTENTEDITOR_EMBEDDED>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor_embedded/contenteditor_embedded.html</TEMPLATE>
		</TAB_CONTENTEDITOR_EMBEDDED>
		<TAB_CONTENTEDITOR_FIND>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor_find/contenteditor_find.html</TEMPLATE>
		</TAB_CONTENTEDITOR_FIND>
		<TAB_CONTENTEDITOR_REPLACE>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor_replace/contenteditor_replace.html</TEMPLATE>
		</TAB_CONTENTEDITOR_REPLACE>
		<TAB_CONTENTEDITOR_HTML>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor_html/contenteditor_html.html</TEMPLATE>
		</TAB_CONTENTEDITOR_HTML>
		<TAB_CONTENTEDITOR_IMAGE>
			<CODE>ui/html/tabs/contenteditor_image/contenteditor_image.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor_image/contenteditor_image.html</TEMPLATE>
		</TAB_CONTENTEDITOR_IMAGE>
		<TAB_CONTENTEDITOR_ANCHOR>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor_anchor/contenteditor_anchor.html</TEMPLATE>
		</TAB_CONTENTEDITOR_ANCHOR>
		<TAB_CONTENTEDITOR_TABLE>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor_table/contenteditor_table.html</TEMPLATE>
		</TAB_CONTENTEDITOR_TABLE>
		<TAB_CONTENTEDITOR_TABLECELL>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor_tablecell/contenteditor_tablecell.html</TEMPLATE>
		</TAB_CONTENTEDITOR_TABLECELL>
		<TAB_CONTENTEDITOR_TABLEROW>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor_tablerow/contenteditor_tablerow.html</TEMPLATE>
		</TAB_CONTENTEDITOR_TABLEROW>
		<TAB_CONTENTEDITOR_MERGECELLS>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor_mergecells/contenteditor_mergecells.html</TEMPLATE>
		</TAB_CONTENTEDITOR_MERGECELLS>
		<TAB_CONTENTEDITOR_PASTEWORD>
			<CODE>ui/html/tabs/common/tabs.php</CODE>
			<TEMPLATE>ui/html/tabs/contenteditor_pasteword/contenteditor_pasteword.html</TEMPLATE>
		</TAB_CONTENTEDITOR_PASTEWORD>
		<TAB_LINK_SELECT_PAGE>
			<CODE>ui/html/tabs/link_select/link_select.php</CODE>
			<TEMPLATE>ui/html/tabs/link_select/link_select_page.html</TEMPLATE>
		</TAB_LINK_SELECT_PAGE>
		<TAB_LINK_SELECT_EMAIL>
			<CODE>ui/html/tabs/link_select/link_select.php</CODE>
			<TEMPLATE>ui/html/tabs/link_select/link_select_email.html</TEMPLATE>
		</TAB_LINK_SELECT_EMAIL>
		<TAB_LINK_SELECT_FILE>
			<CODE>ui/html/tabs/link_select/link_select.php</CODE>
			<TEMPLATE>ui/html/tabs/link_select/link_select_file.html</TEMPLATE>
		</TAB_LINK_SELECT_FILE>
		<TAB_LINK_SELECT_ANCHOR>
			<CODE>ui/html/tabs/link_select/link_select.php</CODE>
			<TEMPLATE>ui/html/tabs/link_select/link_select_anchor.html</TEMPLATE>
		</TAB_LINK_SELECT_ANCHOR>
		<TAB_TEMPLATECONFIG>
			<CODE>ui/html/tabs/template_config/template_config.php</CODE>
			<TEMPLATE>ui/html/tabs/template_config/template_config.html</TEMPLATE>
		</TAB_TEMPLATECONFIG>
		<TAB_TEMPLATEUSAGE>
			<CODE>ui/html/tabs/template_usage/template_usage.php</CODE>
			<TEMPLATE>ui/html/tabs/template_usage/template_usage.html</TEMPLATE>
		</TAB_TEMPLATEUSAGE>
		<TAB_ENTRYMASKCONFIG>
			<CODE>ui/html/tabs/entrymask_config/entrymask_config.php</CODE>
			<TEMPLATE>ui/html/tabs/entrymask_config/entrymask_config.html</TEMPLATE>
		</TAB_ENTRYMASKCONFIG>
		<TAB_ENTRYMASKUSAGE>
			<CODE>ui/html/tabs/entrymask_usage/entrymask_usage.php</CODE>
			<TEMPLATE>ui/html/tabs/entrymask_usage/entrymask_usage.html</TEMPLATE>
		</TAB_ENTRYMASKUSAGE>
		<TAB_ENTRYMASKLIVE>
			<CODE>ui/html/tabs/content/contentblocks.inc.php</CODE>
			<TEMPLATE>ui/html/tabs/content/contentblocks.inc.html</TEMPLATE>
		</TAB_ENTRYMASKLIVE>
		<TAB_CONFIG_PAGE-PROPERTIES>
			<CODE>ui/html/tabs/config_properties/config_properties.php</CODE>
			<TEMPLATE>ui/html/tabs/config_properties/config_properties.html</TEMPLATE>
		</TAB_CONFIG_PAGE-PROPERTIES>
		<TAB_CONFIG_FILE-PROPERTIES>
			<CODE>ui/html/tabs/config_properties/config_properties.php</CODE>
			<TEMPLATE>ui/html/tabs/config_properties/config_properties.html</TEMPLATE>
		</TAB_CONFIG_FILE-PROPERTIES>
		<TAB_CONFIG_CBLOCK-PROPERTIES>
			<CODE>ui/html/tabs/config_properties/config_properties.php</CODE>
			<TEMPLATE>ui/html/tabs/config_properties/config_properties.html</TEMPLATE>
		</TAB_CONFIG_CBLOCK-PROPERTIES>
		<TAB_CONFIG_USER-PROPERTIES>
			<CODE>ui/html/tabs/config_properties/config_properties.php</CODE>
			<TEMPLATE>ui/html/tabs/config_properties/config_properties.html</TEMPLATE>
		</TAB_CONFIG_USER-PROPERTIES>
		<TAB_CONFIG_FILE-TYPES>
			<CODE>ui/html/tabs/config_file-types/config_file-types.php</CODE>
			<TEMPLATE>ui/html/tabs/config_file-types/config_file-types.html</TEMPLATE>
		</TAB_CONFIG_FILE-TYPES>
		<TAB_CONFIG_VIEWS>
			<CODE>ui/html/tabs/config_views/config_views.php</CODE>
			<TEMPLATE>ui/html/tabs/config_views/config_views.html</TEMPLATE>
		</TAB_CONFIG_VIEWS>
		<TAB_CONFIG_MAILINGS>
			<CODE>ui/html/tabs/config_mailings/config_mailings.php</CODE>
			<TEMPLATE>ui/html/tabs/config_mailings/config_mailings.html</TEMPLATE>
		</TAB_CONFIG_MAILINGS>
		<TAB_CONFIG_COMMENTS>
			<CODE>ui/html/tabs/config_comments/config_comments.php</CODE>
			<TEMPLATE>ui/html/tabs/config_comments/config_comments.html</TEMPLATE>
		</TAB_CONFIG_COMMENTS>
		<TAB_START_SECTIONS>
			<CODE>ui/html/tabs/start_sections/start_sections.php</CODE>
			<TEMPLATE>ui/html/tabs/start_sections/start_sections.html</TEMPLATE>
		</TAB_START_SECTIONS>
		<TAB_USERGROUP_LIST>
			<CODE>ui/html/tabs/usergroup_list/usergroup_list.php</CODE>
			<TEMPLATE>ui/html/tabs/usergroup_list/usergroup_list.html</TEMPLATE>
		</TAB_USERGROUP_LIST>
		<TAB_USERGROUP_PAGES>
			<CODE>ui/html/tabs/usergroups/usergroups.php</CODE>
			<TEMPLATE>ui/html/tabs/usergroups/usergroups.html</TEMPLATE>
		</TAB_USERGROUP_PAGES>
		<TAB_USERGROUP_PAGES_INNER>
			<CODE>ui/html/tabs/usergroups/usergroups.php</CODE>
			<TEMPLATE>ui/html/tabs/usergroups/usergroups_inner.inc.html</TEMPLATE>
		</TAB_USERGROUP_PAGES_INNER>
		<TAB_USERGROUP_CBLOCKS>
			<CODE>ui/html/tabs/usergroups/usergroups.php</CODE>
			<TEMPLATE>ui/html/tabs/usergroups/usergroups.html</TEMPLATE>
		</TAB_USERGROUP_CBLOCKS>
		<TAB_USERGROUP_FILES>
			<CODE>ui/html/tabs/usergroups/usergroups.php</CODE>
			<TEMPLATE>ui/html/tabs/usergroups/usergroups.html</TEMPLATE>
		</TAB_USERGROUP_FILES>
		<TAB_USERGROUP_TAGS>
			<CODE>ui/html/tabs/usergroups/usergroups.php</CODE>
			<TEMPLATE>ui/html/tabs/usergroups/usergroups.html</TEMPLATE>
		</TAB_USERGROUP_TAGS>
		<TAB_USERGROUP_MAILINGS>
			<CODE>ui/html/tabs/usergroups/usergroups.php</CODE>
			<TEMPLATE>ui/html/tabs/usergroups/usergroups.html</TEMPLATE>
		</TAB_USERGROUP_MAILINGS>
		<TAB_USERGROUP_USERGROUPS>
			<CODE>ui/html/tabs/usergroups/usergroups.php</CODE>
			<TEMPLATE>ui/html/tabs/usergroups/usergroups.html</TEMPLATE>
		</TAB_USERGROUP_USERGROUPS>
		<TAB_USERGROUP_GENERAL>
			<CODE>ui/html/tabs/usergroup_general/usergroup_general.php</CODE>
			<TEMPLATE>ui/html/tabs/usergroup_general/usergroup_general.html</TEMPLATE>
		</TAB_USERGROUP_GENERAL>
		<PERMISSION_NODES>
			<CODE>ui/html/tabs/usergroups/permission_nodes.php</CODE>
			<TEMPLATE>ui/html/tabs/usergroups/permission_nodes.html</TEMPLATE>
		</PERMISSION_NODES>
		<TAB_SITECONFIG>
			<CODE>ui/html/tabs/site_config/site_config.php</CODE>
			<TEMPLATE>ui/html/tabs/site_config/site_config.html</TEMPLATE>
		</TAB_SITECONFIG>
		<TAB_SITELIST>
			<CODE>ui/html/tabs/site_list/site_list.php</CODE>
			<TEMPLATE>ui/html/tabs/site_list/site_list.html</TEMPLATE>
		</TAB_SITELIST>
		<TAB_FORMFIELDS>
			<CODE>ui/html/tabs/formfields/formfields.php</CODE>
			<TEMPLATE>ui/html/tabs/formfields/formfields.html</TEMPLATE>
		</TAB_FORMFIELDS>
		<TAB_UPLOAD>
			<CODE>ui/html/tabs/upload/upload.php</CODE>
			<TEMPLATE>ui/html/tabs/upload/upload.html</TEMPLATE>
		</TAB_UPLOAD>
		<TAB_UPLOAD_PROGRESS>
			<CODE>ui/html/tabs/upload_progress/upload_progress.php</CODE>
			<TEMPLATE>ui/html/tabs/upload_progress/upload_progress.html</TEMPLATE>
		</TAB_UPLOAD_PROGRESS>
		<TAB_PREVIEW_FILE>
			<CODE>ui/html/tabs/preview/preview.php</CODE>
			<TEMPLATE>ui/html/tabs/preview/preview_file.html</TEMPLATE>
		</TAB_PREVIEW_FILE>
		<TAB_PREVIEW>
			<CODE>ui/html/tabs/preview/preview.php</CODE>
			<TEMPLATE>ui/html/tabs/preview/preview.html</TEMPLATE>
		</TAB_PREVIEW>
		<TAB_TRASHCAN>
			<CODE>ui/html/tabs/trashcan/trashcan.php</CODE>
			<TEMPLATE>ui/html/tabs/trashcan/trashcan.html</TEMPLATE>
		</TAB_TRASHCAN>
		<TAB_UPDATES>
			<CODE>ui/html/tabs/updates/updates.php</CODE>
			<TEMPLATE>ui/html/tabs/updates/updates.html</TEMPLATE>
		</TAB_UPDATES>
		<!-- TABS END -->

		<!-- CONTENTBLOCKS -->
		<CBLOCKS>
			<CODE>ui/html/tabs/content/contentblocks.inc.php</CODE>
			<TEMPLATE>ui/html/tabs/content/contentblocks.inc.html</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</CBLOCKS>
		<TAB_CBLOCKS>
			<CODE>ui/html/tabs/content/contentblocks.inc.php</CODE>
			<TEMPLATE>ui/html/tabs/content/contentblocks.inc.html</TEMPLATE>
		</TAB_CBLOCKS>
		<CONTENTBLOCK_LISTITEM>
			<CODE>ui/html/tabs/contentblock_list/contentblock_list.php</CODE>
			<TEMPLATE>ui/html/tabs/contentblock_list/contentblock_list.inc.html</TEMPLATE>
		</CONTENTBLOCK_LISTITEM>
		<!-- CONTENTBLOCKS END -->

		<!-- TAGS -->
		<TAGS_TREE_NODES>
			<CODE>ui/html/tabs/tags_tree/tags_nodes.php</CODE>
			<TEMPLATE>ui/html/tabs/common/tree_nodes.xml</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</TAGS_TREE_NODES>
		<!-- TAGS END -->

		<!-- CONTENTBLOCKS -->
		<CBLOCKS_TREE_NODES>
			<CODE>ui/html/tabs/contentblocks_tree/contentblocks_nodes.php</CODE>
			<TEMPLATE>ui/html/tabs/common/tree_nodes.xml</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</CBLOCKS_TREE_NODES>
		<!-- CONTENTBLOCKS END -->

		<!-- CONTENTBLOCKS w/EXTRAS -->
		<CBLOCKSEXTRAS_TREE_NODES_EXTRAS>
			<CODE>ui/html/tabs/contentblocks_tree/contentblocks_nodes_extras.php</CODE>
			<TEMPLATE>ui/html/tabs/common/tree_nodes.xml</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</CBLOCKSEXTRAS_TREE_NODES_EXTRAS>
		<!-- CONTENTBLOCKS w/EXTRAS -->

		<!-- ENTRYMASKS -->
		<ENTRYMASKS_TREE_NODES>
			<CODE>ui/html/tabs/entrymasks_tree/entrymasks_nodes.php</CODE>
			<TEMPLATE>ui/html/tabs/common/tree_nodes.xml</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</ENTRYMASKS_TREE_NODES>
		<!-- ENTRYMASKS END -->

		<!-- TEMPLATES -->
		<TEMPLATES_TREE_NODES>
			<CODE>ui/html/tabs/templates_tree/templates_nodes.php</CODE>
			<TEMPLATE>ui/html/tabs/common/tree_nodes.xml</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</TEMPLATES_TREE_NODES>
		<TEMPLATEFOLDERS_TREE_NODES>
			<CODE>ui/html/tabs/templates_tree/templates_nodes.php</CODE>
			<TEMPLATE>ui/html/tabs/common/tree_nodes.xml</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</TEMPLATEFOLDERS_TREE_NODES>
		<!-- TEMPLATES END -->

		<!-- PAGES -->
		<PAGES_TREE_NODES>
			<CODE>ui/html/tabs/pages_tree/pages_nodes.php</CODE>
			<TEMPLATE>ui/html/tabs/common/tree_nodes.xml</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</PAGES_TREE_NODES>
		<!-- PAGES END -->

		<!-- PAGES w/EXTRAS -->
		<PAGESEXTRAS_TREE_NODES_EXTRAS>
			<CODE>ui/html/tabs/pages_tree/pages_nodes_extras.php</CODE>
			<TEMPLATE>ui/html/tabs/common/tree_nodes.xml</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</PAGESEXTRAS_TREE_NODES_EXTRAS>
		<!-- PAGES w/EXTRAS -->

		<!-- PAGES -->
		<FILES_TREE_NODES>
			<CODE>ui/html/tabs/files_tree/files_nodes.php</CODE>
			<TEMPLATE>ui/html/tabs/common/tree_nodes.xml</TEMPLATE>
			<FRONTEND>true</FRONTEND>
			<FORCE_LANG_INCLUDE>true</FORCE_LANG_INCLUDE>
		</FILES_TREE_NODES>
		<!-- PAGES END -->

		<!-- SNIPPETS -->
		<TEMPLATE_INFO>
			<CODE>ui/html/tabs/template_info/template_info.php</CODE>
			<TEMPLATE>ui/html/tabs/template_info/template_info.html</TEMPLATE>
		</TEMPLATE_INFO>
		<NAVIGATION_INFO>
			<CODE>ui/html/tabs/appearance/navigation.inc.php</CODE>
			<TEMPLATE>ui/html/tabs/appearance/navigation.inc.html</TEMPLATE>
		</NAVIGATION_INFO>
		<EXTENSION_INFO>
			<CODE>ui/html/tabs/extension_list/extension_list.php</CODE>
			<TEMPLATE>ui/html/tabs/extension_list/extension_list.html</TEMPLATE>
		</EXTENSION_INFO>
		<!-- SNIPPETS END -->

	</PAGES>

	<AJAXACTIONS>

		<!-- UPDATER -->
		<CHECKUPDATES>
			<CODE>bridge/updater/updater.php</CODE>
		</CHECKUPDATES>
		<UPDATEINSTALLED>
			<CODE>bridge/updater/updater.php</CODE>
		</UPDATEINSTALLED>
		<INSTALLUPDATE>
			<CODE>bridge/updater/updater.php</CODE>
		</INSTALLUPDATE>
		<!-- UPDATER END -->

		<!-- PING -->
		<PING>
			<CODE>bridge/common/common.php</CODE>
		</PING>
		<!-- PING END -->

		<!-- RELEASELOCK -->
		<RELEASELOCK>
			<CODE>bridge/common/common.php</CODE>
		</RELEASELOCK>
		<!-- RELEASELOCK END -->

		<!-- AQUIRELOCK -->
		<AQUIRELOCK>
			<CODE>bridge/common/common.php</CODE>
		</AQUIRELOCK>
		<!-- AQUIRELOCK END -->

		<!-- SITES -->
		<ADDSITE>
			<CODE>bridge/sites/sites.php</CODE>
		</ADDSITE>
		<DELETESITE>
			<CODE>bridge/sites/sites.php</CODE>
		</DELETESITE>
		<SETSITETEMPLATEROOT>
			<CODE>bridge/sites/sites.php</CODE>
		</SETSITETEMPLATEROOT>
		<SETSITETEMPLATE>
			<CODE>bridge/sites/sites.php</CODE>
		</SETSITETEMPLATE>
		<SAVESITEINFO>
			<CODE>bridge/sites/sites.php</CODE>
		</SAVESITEINFO>
		<SITESELECTNODE>
			<CODE>bridge/sites/sites.php</CODE>
		</SITESELECTNODE>
		<SITECALCPNAME>
			<CODE>bridge/sites/sites.php</CODE>
		</SITECALCPNAME>
		<!-- SITES END -->

		<!-- COMMENTS -->
		<SETCOMMENTINGSTATE>
			<CODE>bridge/comments/comments.php</CODE>
		</SETCOMMENTINGSTATE>
		<SETCOMMENTSTATE>
			<CODE>bridge/comments/comments.php</CODE>
		</SETCOMMENTSTATE>
		<REMOVECOMMENT>
			<CODE>bridge/comments/comments.php</CODE>
		</REMOVECOMMENT>
		<SAVECOMMENT>
			<CODE>bridge/comments/comments.php</CODE>
		</SAVECOMMENT>
		<ADDCOMMENT>
			<CODE>bridge/comments/comments.php</CODE>
		</ADDCOMMENT>
		<SAVECOMMENTSSETTINGS>
			<CODE>bridge/comments/comments.php</CODE>
		</SAVECOMMENTSSETTINGS>
		<!-- COMMENTS END -->

		<!-- USERS -->
		<USERSELECTNODE>
			<CODE>bridge/users/users.php</CODE>
		</USERSELECTNODE>
		<ADDUSER>
			<CODE>bridge/users/users.php</CODE>
		</ADDUSER>
		<DELUSER>
			<CODE>bridge/users/users.php</CODE>
		</DELUSER>
		<ADDROLE>
			<CODE>bridge/users/users.php</CODE>
		</ADDROLE>
		<DELETEROLE>
			<CODE>bridge/users/users.php</CODE>
		</DELETEROLE>
		<RECOVERLOGIN>
			<CODE>bridge/users/users.php</CODE>
		</RECOVERLOGIN>
		<SETNEWPASSWORD>
			<CODE>bridge/users/users.php</CODE>
		</SETNEWPASSWORD>
		<USERLOGIN>
			<CODE>bridge/users/users.php</CODE>
		</USERLOGIN>
		<USERLOGOUT>
			<CODE>bridge/users/users.php</CODE>
		</USERLOGOUT>
		<GETUSERINFO>
			<CODE>bridge/users/users.php</CODE>
		</GETUSERINFO>
		<PROCESSUSERPROFILEPICTURE>
			<CODE>bridge/users/users.php</CODE>
		</PROCESSUSERPROFILEPICTURE>
		<UPLOADUSERPROFILEPICTURE>
			<CODE>bridge/users/users.php</CODE>
		</UPLOADUSERPROFILEPICTURE>
		<SAVEUSERPROFILE>
			<CODE>bridge/users/users.php</CODE>
		</SAVEUSERPROFILE>
		<!-- USERS END -->

		<!-- USERGROUPS -->
		<DELETEUSERGROUP>
			<CODE>bridge/usergroups/usergroups.php</CODE>
		</DELETEUSERGROUP>
		<ADDUSERGROUP>
			<CODE>bridge/usergroups/usergroups.php</CODE>
		</ADDUSERGROUP>
		<SAVEPERMISSIONS>
			<CODE>bridge/usergroups/usergroups.php</CODE>
		</SAVEPERMISSIONS>
		<USERGROUPSSELECTNODE>
			<CODE>bridge/usergroups/usergroups.php</CODE>
		</USERGROUPSSELECTNODE>
		<!-- USERGROUPS END -->

		<!-- CONTENTBLOCKS -->
		<SETCBLOCKNAME>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</SETCBLOCKNAME>
		<RESTORECBLOCKVERSION>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</RESTORECBLOCKVERSION>
		<CONTENTBLOCKSELECTNODE>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</CONTENTBLOCKSELECTNODE>
		<REMOVECBLOCKENTRYMASK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</REMOVECBLOCKENTRYMASK>
		<REMOVEPAGECONTENTBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</REMOVEPAGECONTENTBLOCK>
		<MOVEUPPAGECONTENTBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</MOVEUPPAGECONTENTBLOCK>
		<MOVEDOWNPAGECONTENTBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</MOVEDOWNPAGECONTENTBLOCK>
		<ORDERPAGECONTENTBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</ORDERPAGECONTENTBLOCK>
		<ORDERMAILINGCONTENTBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</ORDERMAILINGCONTENTBLOCK>
		<ORDEREDITCONTENTBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</ORDEREDITCONTENTBLOCK>
		<ADDPAGECONTENTBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</ADDPAGECONTENTBLOCK>
		<ADDOBJECTEXTENSION>
			<CODE>bridge/extensions/extensions.php</CODE>
		</ADDOBJECTEXTENSION>
		<ADDEDITCONTENTBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</ADDEDITCONTENTBLOCK>
		<APPROVECBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</APPROVECBLOCK>
		<SAVECBLOCKPUBLISHINGSETTINGS>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</SAVECBLOCKPUBLISHINGSETTINGS>
		<REMOVECBLOCKAUTOPUBLISHITEM>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</REMOVECBLOCKAUTOPUBLISHITEM>
		<ADDCBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</ADDCBLOCK>
		<ADDCBLOCKCHILDFOLDER>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</ADDCBLOCKCHILDFOLDER>
		<DELETECBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</DELETECBLOCK>
		<COPYCBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</COPYCBLOCK>
		<MOVECBLOCK>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</MOVECBLOCK>
		<SAVECBLOCKVERSION>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</SAVECBLOCKVERSION>
		<SETCBLOCKPNAME>
			<CODE>bridge/contentblocks/contentblocks.php</CODE>
		</SETCBLOCKPNAME>
		<!-- CONTENTBLOCKS END -->

		<!-- ENTRYMASKS -->
		<SAVEPAGEENTRYMASK>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</SAVEPAGEENTRYMASK>
		<ADDPAGEENTRYMASK>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</ADDPAGEENTRYMASK>
		<ADDCBLOCKENTRYMASK>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</ADDCBLOCKENTRYMASK>
		<ADDPOSITIONEDPAGEENTRYMASK>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</ADDPOSITIONEDPAGEENTRYMASK>
		<ADDPOSITIONEDCONTROLENTRYMASK>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</ADDPOSITIONEDCONTROLENTRYMASK>
		<!-- ENTRYMASKS END -->

		<!-- FILES -->
		<ADDFILECHILDFOLDER>
			<CODE>bridge/files/files.php</CODE>
		</ADDFILECHILDFOLDER>
		<DELETEFOLDER>
			<CODE>bridge/files/files.php</CODE>
		</DELETEFOLDER>
		<MOVEUPFOLDER>
			<CODE>bridge/files/files.php</CODE>
		</MOVEUPFOLDER>
		<MOVEDOWNFOLDER>
			<CODE>bridge/files/files.php</CODE>
		</MOVEDOWNFOLDER>
		<ADDFILETAG>
			<CODE>bridge/files/files.php</CODE>
		</ADDFILETAG>
		<ADDFILEVIEW>
			<CODE>bridge/files/files.php</CODE>
		</ADDFILEVIEW>
		<DELETEFILEVIEW>
			<CODE>bridge/files/files.php</CODE>
		</DELETEFILEVIEW>
		<DELETEFILE>
			<CODE>bridge/files/files.php</CODE>
		</DELETEFILE>
		<COPYFILE>
			<CODE>bridge/files/files.php</CODE>
		</COPYFILE>
		<MOVEFILE>
			<CODE>bridge/files/files.php</CODE>
		</MOVEFILE>
		<FILESELECTNODE>
			<CODE>bridge/files/files.php</CODE>
		</FILESELECTNODE>
		<SAVEFILEVERSION>
			<CODE>bridge/files/files.php</CODE>
		</SAVEFILEVERSION>
		<CHANGEFILETYPE>
			<CODE>bridge/files/files.php</CODE>
		</CHANGEFILETYPE>
		<SETFILENAME>
			<CODE>bridge/files/files.php</CODE>
		</SETFILENAME>
		<RESTOREFILEVERSION>
			<CODE>bridge/files/files.php</CODE>
		</RESTOREFILEVERSION>
		<UPLOADFILE>
			<CODE>bridge/files/files.php</CODE>
		</UPLOADFILE>
		<REUPLOADFILE>
			<CODE>bridge/files/files.php</CODE>
		</REUPLOADFILE>
		<PROCESSUPLOAD>
			<CODE>bridge/files/files.php</CODE>
		</PROCESSUPLOAD>
		<SAVEFILETYPES>
			<CODE>bridge/files/files.php</CODE>
		</SAVEFILETYPES>
		<SAVEVIEWS>
			<CODE>bridge/files/files.php</CODE>
		</SAVEVIEWS>
		<REFRESHFILEVERSIONDETAILS>
			<CODE>bridge/files/files.php</CODE>
		</REFRESHFILEVERSIONDETAILS>
		<REFRESHFILEVIEWDETAILS>
			<CODE>bridge/files/files.php</CODE>
		</REFRESHFILEVIEWDETAILS>
		<CROPFILE>
			<CODE>bridge/files/files.php</CODE>
		</CROPFILE>
		<GETFILEINFO>
			<CODE>bridge/files/files.php</CODE>
		</GETFILEINFO>
		<SETFILEPNAME>
			<CODE>bridge/files/files.php</CODE>
		</SETFILEPNAME>
		<!-- FILES END -->

		<!-- TAGS -->
		<MOVETAG>
			<CODE>bridge/tags/tags.php</CODE>
		</MOVETAG>
		<ADDTAGCHILDFOLDER>
			<CODE>bridge/tags/tags.php</CODE>
		</ADDTAGCHILDFOLDER>
		<DELETETAG>
			<CODE>bridge/tags/tags.php</CODE>
		</DELETETAG>
		<SETTAGNAME>
			<CODE>bridge/tags/tags.php</CODE>
		</SETTAGNAME>
		<TAGSELECTNODE>
			<CODE>bridge/tags/tags.php</CODE>
		</TAGSELECTNODE>
		<ADDOBJECTTAG>
			<CODE>bridge/tags/tags.php</CODE>
		</ADDOBJECTTAG>
		<DELETEOBJECTTAG>
			<CODE>bridge/tags/tags.php</CODE>
		</DELETEOBJECTTAG>
		<ORDEROBJECTTAG>
			<CODE>bridge/tags/tags.php</CODE>
		</ORDEROBJECTTAG>
		<!-- TAGS END -->

		<!-- COMMON -->
		<SETOBJECTPROPERTY>
			<CODE>bridge/common/common.php</CODE>
		</SETOBJECTPROPERTY>
		<SHREDDEROBJECT>
			<CODE>bridge/common/common.php</CODE>
		</SHREDDEROBJECT>
		<!-- COMMON END -->

		<!-- MAILINGS -->
		<SETMAILINGTEMPLATE>
			<CODE>bridge/mailings/mailings.php</CODE>
		</SETMAILINGTEMPLATE>
		<SETMAILINGPNAME>
			<CODE>bridge/mailings/mailings.php</CODE>
		</SETMAILINGPNAME>
		<APPROVEMAILING>
			<CODE>bridge/mailings/mailings.php</CODE>
		</APPROVEMAILING>
		<SENDMAILING>
			<CODE>bridge/mailings/mailings.php</CODE>
		</SENDMAILING>
		<PAUSEMAILING>
			<CODE>bridge/mailings/mailings.php</CODE>
		</PAUSEMAILING>
		<RESUMEMAILING>
			<CODE>bridge/mailings/mailings.php</CODE>
		</RESUMEMAILING>
		<CANCELMAILING>
			<CODE>bridge/mailings/mailings.php</CODE>
		</CANCELMAILING>
		<UPDATEMAILINGSTATUS>
			<CODE>bridge/mailings/mailings.php</CODE>
		</UPDATEMAILINGSTATUS>
		<ADDMAILING>
			<CODE>bridge/mailings/mailings.php</CODE>
		</ADDMAILING>
		<DELETEMAILING>
			<CODE>bridge/mailings/mailings.php</CODE>
		</DELETEMAILING>
		<MAILINGSELECTNODE>
			<CODE>bridge/mailings/mailings.php</CODE>
		</MAILINGSELECTNODE>
		<RESTOREMAILINGVERSION>
			<CODE>bridge/mailings/mailings.php</CODE>
		</RESTOREMAILINGVERSION>
		<SAVEMAILINGVERSION>
			<CODE>bridge/mailings/mailings.php</CODE>
		</SAVEMAILINGVERSION>
		<SETMAILINGCONFIGTEMPLATE>
			<CODE>bridge/mailings/mailings.php</CODE>
		</SETMAILINGCONFIGTEMPLATE>
		<SETMAILINGCONFIGTEMPLATEROOT>
			<CODE>bridge/mailings/mailings.php</CODE>
		</SETMAILINGCONFIGTEMPLATEROOT>
		<SAVEMAILINGINFO>
			<CODE>bridge/mailings/mailings.php</CODE>
		</SAVEMAILINGINFO>
		<DUPLICATEMAILING>
			<CODE>bridge/mailings/mailings.php</CODE>
		</DUPLICATEMAILING>
		<!-- MAILINGS END -->

		<!-- PAGES -->
		<CHECKSPECIALLINKTYPE>
			<CODE>bridge/pages/pages.php</CODE>
		</CHECKSPECIALLINKTYPE>
		<CHECKLINKEXTERNAL>
			<CODE>bridge/pages/pages.php</CODE>
		</CHECKLINKEXTERNAL>
		<SETPAGETEMPLATE>
			<CODE>bridge/pages/pages.php</CODE>
		</SETPAGETEMPLATE>
		<SETPAGENAVIGATION>
			<CODE>bridge/pages/pages.php</CODE>
		</SETPAGENAVIGATION>
		<ADDPAGE>
			<CODE>bridge/pages/pages.php</CODE>
		</ADDPAGE>
		<DELETEPAGE>
			<CODE>bridge/pages/pages.php</CODE>
		</DELETEPAGE>
		<COPYPAGE>
			<CODE>bridge/pages/pages.php</CODE>
		</COPYPAGE>
		<MOVEPAGE>
			<CODE>bridge/pages/pages.php</CODE>
		</MOVEPAGE>
		<MOVEUPPAGE>
			<CODE>bridge/pages/pages.php</CODE>
		</MOVEUPPAGE>
		<MOVEDOWNPAGE>
			<CODE>bridge/pages/pages.php</CODE>
		</MOVEDOWNPAGE>
		<ORDERPAGESUBPAGES>
			<CODE>bridge/pages/pages.php</CODE>
		</ORDERPAGESUBPAGES>
		<PAGESELECTNODE>
			<CODE>bridge/pages/pages.php</CODE>
		</PAGESELECTNODE>
		<RESTOREPAGEVERSION>
			<CODE>bridge/pages/pages.php</CODE>
		</RESTOREPAGEVERSION>
		<SAVEPAGEPUBLISHINGSETTINGS>
			<CODE>bridge/pages/pages.php</CODE>
		</SAVEPAGEPUBLISHINGSETTINGS>
		<REMOVEPAGEAUTOPUBLISHITEM>
			<CODE>bridge/pages/pages.php</CODE>
		</REMOVEPAGEAUTOPUBLISHITEM>
		<SETPAGENAME>
			<CODE>bridge/pages/pages.php</CODE>
		</SETPAGENAME>
		<SETPAGETITLE>
			<CODE>bridge/pages/pages.php</CODE>
		</SETPAGETITLE>
		<APPROVEPAGE>
			<CODE>bridge/pages/pages.php</CODE>
		</APPROVEPAGE>
		<SETPAGESTATE>
			<CODE>bridge/pages/pages.php</CODE>
		</SETPAGESTATE>
		<SETPAGEPNAME>
			<CODE>bridge/pages/pages.php</CODE>
		</SETPAGEPNAME>
		<SAVEPAGEVERSION>
			<CODE>bridge/pages/pages.php</CODE>
		</SAVEPAGEVERSION>
		<!-- PAGES END -->

		<!-- TEMPLATES -->
		<TEMPLATESELECTNODE>
			<CODE>bridge/templates/templates.php</CODE>
		</TEMPLATESELECTNODE>
		<SETTEMPLATENAME>
			<CODE>bridge/templates/templates.php</CODE>
		</SETTEMPLATENAME>
		<SAVETEMPLATEINFO>
			<CODE>bridge/templates/templates.php</CODE>
		</SAVETEMPLATEINFO>
		<UPLOADTEMPLATE>
			<CODE>bridge/templates/templates.php</CODE>
		</UPLOADTEMPLATE>
		<UPLOADTEMPLATEPREVIEW>
			<CODE>bridge/templates/templates.php</CODE>
		</UPLOADTEMPLATEPREVIEW>
		<MOVETEMPLATE>
			<CODE>bridge/templates/templates.php</CODE>
		</MOVETEMPLATE>
		<ADDTEMPLATE>
			<CODE>bridge/templates/templates.php</CODE>
		</ADDTEMPLATE>
		<ADDTEMPLATECHILDFOLDER>
			<CODE>bridge/templates/templates.php</CODE>
		</ADDTEMPLATECHILDFOLDER>
		<DELETETEMPLATE>
			<CODE>bridge/templates/templates.php</CODE>
		</DELETETEMPLATE>
		<TEMPLATECALCPNAME>
			<CODE>bridge/templates/templates.php</CODE>
		</TEMPLATECALCPNAME>
		<!-- TEMPLATES END -->

		<!-- ENTRYMASKS -->
		<ENTRYMASKSELECTNODE>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</ENTRYMASKSELECTNODE>
		<ENTRYMASKSAVECONFIG>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</ENTRYMASKSAVECONFIG>
		<ADDENTRYMASKCHILDFOLDER>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</ADDENTRYMASKCHILDFOLDER>
		<ADDENTRYMASK>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</ADDENTRYMASK>
		<DELETEENTRYMASK>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</DELETEENTRYMASK>
		<MOVEENTRYMASK>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</MOVEENTRYMASK>
		<SETENTRYMASKNAME>
			<CODE>bridge/entrymasks/entrymasks.php</CODE>
		</SETENTRYMASKNAME>
		<!-- ENTRYMASKS END -->

		<!-- PROPERTIES -->
		<SAVEPROPERTIES>
			<CODE>bridge/common/common.php</CODE>
		</SAVEPROPERTIES>
		<!-- PROPERTIES END -->

		<!-- EXTENSIONS -->
		<EXTENSIONSELECTNODE>
			<CODE>bridge/extensions/extensions.php</CODE>
		</EXTENSIONSELECTNODE>
		<INSTALLEXTENSION>
			<CODE>bridge/extensions/extensions.php</CODE>
		</INSTALLEXTENSION>
		<UNINSTALLEXTENSION>
			<CODE>bridge/extensions/extensions.php</CODE>
		</UNINSTALLEXTENSION>
		<SETEXTENSIONPROPERTIES>
			<CODE>bridge/extensions/extensions.php</CODE>
		</SETEXTENSIONPROPERTIES>
		<SAVEEXTENSIONPROPERTIES>
			<CODE>bridge/extensions/extensions.php</CODE>
		</SAVEEXTENSIONPROPERTIES>
		<REMOVEOBJECTEXTENSION>
			<CODE>bridge/extensions/extensions.php</CODE>
		</REMOVEOBJECTEXTENSION>
		<UPLOADEXTENSIONIMPORTDATA>
			<CODE>bridge/extensions/extensions.php</CODE>
		</UPLOADEXTENSIONIMPORTDATA>
		<PROCESSIMPORTEXTENSIONDATA>
			<CODE>bridge/extensions/extensions.php</CODE>
		</PROCESSIMPORTEXTENSIONDATA>
		<EXTENSIONEXPORTDATA>
			<CODE>bridge/extensions/extensions.php</CODE>
		</EXTENSIONEXPORTDATA>
		<EXTENSIONEXPORTFETCHFILE>
			<CODE>bridge/extensions/extensions.php</CODE>
		</EXTENSIONEXPORTFETCHFILE>
		<!-- EXTENSIONS END -->

	</AJAXACTIONS>
</CONFIG>
