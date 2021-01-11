<?php
// Default English language file for
// Projects and VirtualHosts sub-menus
// Settings and Tools right-click sub-menus
// 3.0.7 add $w_listenForApache - $w_AddListenPort - $w_deleteListenPort - $w_settings['SupportMariaDB']
// $w_settings['DaredevilOptions']
// $w_Size - $w_EnterSize - $w_Time - $w_EnterTime - $w_Integer - $w_EnterInteger - $w_add_VirtualHost
// 3.0.8 $w_settings['SupportMySQL'] - $w_portUsedMaria - $w_testPortMariaUsed
// 3.0.9 $w_ext_zend
// 3.1.1 $w_defaultDBMS - $w_invertDefault - $w_changeCLI - $w_misc
// $w_settings['ShowphmyadMenu'] - $w_settings['ShowadminerMenu']
// 3.1.2 $w_reinstallServices - $w_settings['mariadbUseConsolePrompt'] - $w_settings['mysqlUseConsolePrompt']
// $w_enterServiceNameAll - $w_settings['NotVerifyPATH'] - $w_MysqlMariaUser
// 3.1.4 $w_settings 'NotVerifyTLD' 'Cleaning' 'AutoCleanLogs' 'AutoCleanLogsMax' 'AutoCleanLogsMax' 'AutoCleanTmp' 'AutoCleanTmpMax' 'iniCommented'
// $w_wampReport - $w_dowampReport
// 3.1.9 $w_settings 'BackupHosts'
// 3.2.0 $w_verifySymlink  - $w_settings['NotVerifyHosts']
// 3.2.1 $w_addingVer - $w_addingVerTxt - $w_goto - $w_FileRepository
// 3.2.2 $w_MysqlMariaUser and $w_EnterSize modified -  - $w_MySQLsqlmodeInfo $w_mysql_mode $w_phpMyAdminHelp $w_PhpMyAdMinHelpTxt

// Projects sub-menu
$w_projectsSubMenu = 'Your projects';
// VirtualHosts sub-menu
$w_virtualHostsSubMenu = 'Your VirtualHosts';
$w_add_VirtualHost = 'VirtualHost Management';
$w_aliasSubMenu = 'Your Aliases';
$w_portUsed = 'Port used by Apache: ';
$w_portUsedMysql = 'Port used by MySQL: ';
$w_portUsedMaria = 'Port used by MariaDB : ';
$w_testPortUsed = 'Test port used: ';
$w_portForApache = 'Port for Apache';
$w_listenForApache = 'Listen Port to add to Apache';
$w_portForMysql = 'Port for MySQL';
$w_testPortMysql = 'Test port 3306';
$w_testPortMysqlUsed = 'Test MySQL port used: ';
$w_testPortMariaUsed = 'Test MariaDB port used: ';

// Right-click Settings
$w_wampSettings = 'Wamp Settings';
$w_settings = array(
	'urlAddLocalhost' => 'Add localhost in url',
	'VirtualHostSubMenu' => 'VirtualHosts sub-menu',
	'AliasSubmenu' => 'Alias sub-menu',
	'ProjectSubMenu' => 'Projects sub-menu',
	'HomepageAtStartup' => 'Wampserver Homepage at startup',
	'MenuItemOnline' => 'Menu item: Online / Offline',
	'ItemServicesNames' => 'Tools menu item: Change services names',
	'NotCheckVirtualHost' => 'Don\'t check VirtualHost definitions',
	'NotCheckDuplicate' => 'Don\'t check duplicate ServerName',
	'VhostAllLocalIp' => 'Allow VirtualHost local IP\'s others than 127.*',
	'SupportMySQL' => 'Allow MySQL',
	'SupportMariaDB' => 'Allow MariaDB',
	'DaredevilOptions' => 'Caution: Risky! Only for experts.',
	'ShowphmyadMenu' => 'Show PhpMyAdmin in Menu',
	'ShowadminerMenu' => 'Show Adminer in Menu',
	'mariadbUseConsolePrompt' => 'Modify default Mariadb console prompt',
	'mysqlUseConsolePrompt' => 'Modify default Mysql console prompt',
	'NotVerifyPATH' => 'Do not verify PATH',
	'NotVerifyTLD' => 'Do not verify TLD',
	'NotVerifyHosts' => 'Do not verify hosts file',
	'Cleaning' => 'Automatic Cleaning',
	'AutoCleanLogs' => 'Clean log files automatically',
	'AutoCleanLogsMax' => 'Number of lines before cleaning',
	'AutoCleanLogsMin' => 'Number of lines after cleaning',
	'AutoCleanTmp' => 'Clean tmp directory automatically',
	'AutoCleanTmpMax' => 'Number of files before cleaning',
	'ForTestOnly' => 'Only for test purpose',
	'iniCommented' => 'Commented php.ini directives (; at the beginning of the line)',
	'BackupHosts' => 'Backup hosts file',
);

// Right-click Tools
$w_wampTools = 'Tools';
$w_restartDNS = 'Restart DNS';
$w_testConf = 'Check httpd.conf syntax';
$w_testServices = 'Check state of services';
$w_changeServices = 'Change the names of services';
$w_enterServiceNameApache = "Enter an index number for the Apache service. It will be added to 'wampapache'";
$w_enterServiceNameMysql = "Enter an index number for the Mysql service. It will be added to 'wampmysqld'";
$w_enterServiceNameAll = "Enter a number for the suffix of service names (empty to return original services)";
$w_compilerVersions = 'Check Compiler VC, compatibility and ini files';
$w_UseAlternatePort = 'Use a port other than %s';
$w_AddListenPort = 'Add a Listen port for Apache';
$w_vhostConfig = 'Show VirtualHost examined by Apache';
$w_apacheLoadedModules = 'Show Apache loaded Modules';
$w_empty = 'Empty';
$w_misc = 'Miscellaneous';
$w_emptyAll = 'Empty ALL';
$w_dnsorder = 'Check DNS search order';
$w_deleteVer = 'Delete unused versions';
$w_addingVer = 'Add Apache, PHP, MySQL, MariaDB, etc. versions.';
$w_deleteListenPort = 'Delete a Listen port Apache';
$w_delete = 'Delete';
$w_defaultDBMS = 'Default DBMS:';
$w_invertDefault = 'Invert default DBMS ';
$w_changeCLI = 'Change PHP CLI version';
$w_reinstallServices = 'Reinstall all services';
$w_wampReport = 'Wampserver Configuration Report';
$w_dowampReport = 'Create '.$w_wampReport;
$w_verifySymlink = 'Verify symbolic links';
$w_goto = 'Go to:';
$w_FileRepository = 'Links to Wampserver repositories files & addons';

//miscellaneous
$w_ext_spec = 'Special extensions';
$w_ext_zend = 'Zend extensions';
$w_phpparam_info = 'For information only';
$w_ext_nodll = 'No dll file';
$w_ext_noline = "No 'extension='";
$w_mod_fixed = "Irreversible module";
$w_no_module = 'No module file';
$w_no_moduleload = "No 'LoadModule'";
$w_mysql_none = "none";
$w_mysql_user = "user mode";
$w_mysql_default = "by default";
$w_mysql_mode = "Explanations of sql-mode";
$w_Size = "Size";
$w_Time = "Time";
$w_Integer = "Integer Value";
$w_phpMyAdminHelp = "Help PhpMyAdmin";

// PromptText for Aestan Tray Menu type: prompt variables
// May have \r\n for multilines
$w_EnterInteger = "Enter an integer";
$w_enterPort = "Enter the desired port number";
$w_EnterSize = "Enter Size: xxxx followed by M for Mega or G for Giga\r\nThe symbol M or G must be attached to the number.\r\nFor example : 64M ; 256M ; 1G";
$w_EnterTime = "Enter time in seconds";
$w_MysqlMariaUser = "Enter a valid username. If you don't know, keep 'root' by default.\r\nIf you have set a password for either root or the chosen user, you will need to type that password when prompted for 'Enter password:' from the console. Without password, Enter key";

// Long texts
// Quotation marks " in texts must be escaped: \" - May have \r\n for multilines
$w_addingVerTxt ="All \"addons\", i.e. all installers of Apache, PHP, MySQL or MariaDB versions as well as installers of updates (Wampserver, Aestan Tray Menu, xDebug, etc.) and web applications (PhpMyAdmin, Adminer) are on\r\n\r\n'https://sourceforge.net/projects/wampserver/'\r\n\r\nJust download the installer files you want and launch them by right-clicking on the name of the downloaded file then \"Run as administrator\" to have the addon or application added to your version of Wampserver.\r\n\r\nThen, changing Apache, PHP, MySQL or MariaDB version is a matter of three clicks:\r\nLeft-Click -> PHP|Apache|MySQL|MariaDB -> Version -> Choose version\r\n\r\nThe version change does not include any parameter changes you might have made, nor does it transfer databases from the old version to the new one.\r\n\r\nA much better organized and always up to date repository than Sourceforge exists:\r\n\r\n'http://wampserver.aviatechno.net'.\r\n\r\nThe links to the repositories are in Right-Click -> Help\r\n";
$w_MySQLsqlmodeInfo = "MySQL/MariaDB sql-mode\r\nThe SQL server may run in different SQL modes depending on the value of the sql-mode directive.\r\nSetting one or more modes restricts certain possibilities and requires greater rigor in SQL syntax and data validation.\r\nThe operation of the sql-mode directive in the my.ini file is as follows.\r\n\r\n- sql-mode: by default\r\nThe sql-mode directive does not exist or is commented out (;sql-mode=\"...\")\r\nThe default modes of the MySQL/MariaDB version are applied\r\n\r\n- sql-mode: user mode\r\nThe sql-mode directive is populated with user-defined modes, for example :\r\nsql-mode=\"NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_AUTO_CREATE_USER\"\r\n\r\n- sql-mode: none\r\nThe sql-mode directive is empty but must exist:\r\nsql-mode=\"\"\r\nno SQL mode is applied.";
$w_PhpMyAdMinHelpTxt = "-- PhpMyAdmin\r\nWhen starting phpMyAdmin, you will be asked for a user name and password.\r\nAfter installing Wampserver 3, the default username is \"root\" (without quotes) and there is no password, which means that you must leave the form Password box empty.\r\n\r\nPhpMyAdmin is configured to allow you access to either MySQL or MariaDB depending on which ones are active.\r\nIf both DBMS's are activated, you will see a dropdown on the Login screen, called \"Server Choice\", the default server will be shown first in the dropdown list. Select the DBMS you want to use here as part of the login process.\r\nREMEMBER, if you have different user accounts you must use the correct one for the selected DBMS.\r\nALSO: If you have the same account i.e. `root` on both DBMS's, if you have set different passwords, you need to use the right password for the account and DBMS.\r\n";

?>