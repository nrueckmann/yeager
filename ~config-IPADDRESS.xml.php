<?xml version="1.0"?>
<!-- <?php die(); ?> -->
<CONFIG>
	<DB>
		<DSN id="yeager" driver="mysql" host="[__DATABASE_SERVER__]:[__DATABASE_PORT__]" user="[__DATABASE_USER__]" password="[__DATABASE_PASSWORD__]" db="[__DATABASE_NAME__]" />
	</DB>
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
			<BUCKET TYPE="FILE" URI="[SYSTMP]/nas-[YEAR][MONTH][DAY].log" LEVEL="1"/>
			<BUCKET TYPE="PLUGIN" URI="Echo" LEVEL="0"/>
			<BUCKET TYPE="PLUGIN" URI="FireBug" LEVEL="1"/>
		</BUCKETS>
	</LOGGING>
	<CACHE>
		<FRONTEND>false</FRONTEND>
	</CACHE>
	<DIRECTORIES>
		<WEBROOT>[__CFG_WEB_ROOT__]</WEBROOT>
		<DOCPATH>[__CFG_WEB_ROOT__]yeager/</DOCPATH>
		<USERPICDIR>../[__CFG_FILES_DIR__]/</USERPICDIR>
		<UPDATES>updates/</UPDATES>
		<TEMPLATEPREVIEWDIR>../[__CFG_FRONTEND_DIR__]/preview/</TEMPLATEPREVIEWDIR>
		<TEMPLATEDIR>../[__CFG_FRONTEND_DIR__]/</TEMPLATEDIR>
		<TEMPLATEDOC>[__CFG_WEB_ROOT__][__CFG_FRONTEND_DIR__]/</TEMPLATEDOC>
		<FILESDIR>../[__CFG_FILES_DIR__]/</FILESDIR>
		<FILESDOC>[__CFG_WEB_ROOT__][__CFG_FILES_DIR__]/</FILESDOC>
		<EXTENSIONSDIR>../[__CFG_EXTENSIONS_DIR__]/</EXTENSIONSDIR>
		<EXTENSIONSDOC>[__CFG_WEB_ROOT__][__CFG_EXTENSIONS_DIR__]/</EXTENSIONSDOC>
		<PROCESSORSDIR>../[__CFG_PROCESSORS_DIR__]/</PROCESSORSDIR>
		<LOGINURL></LOGINURL>
	</DIRECTORIES>
	<ERRORPAGES>
		<ERROR_404></ERROR_404>
		<ERROR_403></ERROR_403>
	</ERRORPAGES>
	<FILE_PROCESSORS>
		<!-- File Processors go here //-->
	</FILE_PROCESSORS>
	<MAILINGS>
		<!--FORCE_RECIPIENT>admin@example.com</FORCE_RECIPIENT-->
		<!--SMTP>mail.example.com</SMTP-->
	</MAILINGS>
	<RESERVED_SITENAMES>[__CFG_FRONTEND_PARENTDIR__],yeager,download,image</RESERVED_SITENAMES>
	<PAGEDIR>
		<DEFAULT_PER_PAGE>40</DEFAULT_PER_PAGE>
	</PAGEDIR>
	<PATH>
		<TMP>[__CFG_TMP_DIR__]/</TMP>
	</PATH>
	<REFTRACKER>
		<INTERNALPREFIX>[__CFG_WEB_ROOT__]yeager/y.php/</INTERNALPREFIX>
	</REFTRACKER>
	<EMBEDDED_CBLOCKFOLDER>2</EMBEDDED_CBLOCKFOLDER>
	<SYSTEMUSERS>
		<ROOTUSERID>1</ROOTUSERID>
		<ROOTGROUPID>1</ROOTGROUPID>
		<ANONUSERID>2</ANONUSERID>
		<ANONGROUPID>2</ANONGROUPID>
	</SYSTEMUSERS>
	<TIMEZONES>
		<SERVER>[__SERVER_TIMEZONE__]</SERVER>
		<FRONTEND>[__DEFAULT_TIMEZONE__]</FRONTEND>
	</TIMEZONES>
	<CASE_SENSITIVE_URLS>0</CASE_SENSITIVE_URLS>
	<GUISYNC_INTERVAL>125</GUISYNC_INTERVAL>
	<GUISYNC_TIMEOUT>160</GUISYNC_TIMEOUT>
	<OBJECTLOCK_TIMEOUT>60</OBJECTLOCK_TIMEOUT>
	<OBJECTRELOCK_INTERVAL>10</OBJECTRELOCK_INTERVAL>
</CONFIG>