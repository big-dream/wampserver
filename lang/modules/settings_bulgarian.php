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
// 3.2.3 https for wampserver.aviatechno
// 3.2.5 $w_emptyLogs - $w_emptyPHPlog - $w_emptyApaErrLog - $w_emptyApaAccLog - $w_emptyMySQLog - $w_emptyMariaLog - $w_emptyAllLog
//       $w_testAliasDir - $w_verifyxDebugdll - $w_apacheLoadedIncludes - $w_settings 'ShowWWWdirMenu'

// Projects sub-menu
$w_projectsSubMenu = '����� �������';
// VirtualHosts sub-menu
$w_virtualHostsSubMenu = '����� ��������� �������';
$w_add_VirtualHost = '���������� �� ����������� �������';
$w_aliasSubMenu = '����� ����������';
$w_portUsed = '��������� �� Apache ����: ';
$w_portUsedMysql = '��������� �� MySQL ����: ';
$w_portUsedMaria = '��������� �� MariaDB ����: ';
$w_testPortUsed = '��������� �� ������� ����: ';
$w_portForApache = '���� �� Apache';
$w_listenForApache = '���� �� �������, ����� �� �� ������ � Apache';
$w_portForMysql = '���� �� MySQL';
$w_testPortMysql = '��������� ���� 3306';
$w_testPortMysqlUsed = '��������� ���� �� MySQL, ���������: ';
$w_testPortMariaUsed = '��������� ���� �� MariaDB, ���������: ';

// Right-click Settings
$w_wampSettings = '��������� �� WAMP';
$w_settings = array(
	'urlAddLocalhost' => '������� localhost ��� ������',
	'VirtualHostSubMenu' => '������� \'��������� �������\'',
	'AliasSubmenu' => '������� \'����������\'',
	'ProjectSubMenu' => '������� \'�������\'',
	'HomepageAtStartup' => '�������� �� WampServer ��� ����������',
	'MenuItemOnline' => '����� � ������: \'������� ������/������\'',
	'ItemServicesNames' => '����� � ������ \'�����������\': ������� ������� �� ��������',
	'NotCheckVirtualHost' => '�� ���������� ����������� �� ����������� �������',
	'NotCheckDuplicate' => '�� ���������� �� ��������� �� ����� �� �������',
	'VhostAllLocalIp' => '������� ������ IP ������ �� ��������� �������, �������� �� 127.*',
	'SupportMySQL' => '��������� MySQL',
	'SupportMariaDB' => '��������� MariaDB',
	'DaredevilOptions' => '��������! ���� �� ��������!',
	'ShowphmyadMenu' => '�������� PhpMyAdmin � ������',
	'ShowadminerMenu' => '�������� Adminer � ������',
	'mariadbUseConsolePrompt' => '������� ������������ ������� �� MariaDB',
	'mysqlUseConsolePrompt' => '������� ������������ ������� �� MySQL',
	'NotVerifyPATH' => '�� ���������� ���� (PATH)',
	'NotVerifyTLD' => '�� ���������� ��������� �� ����� ���� (TLD)',
	'NotVerifyHosts' => '�� ���������� ����� hosts',
	'Cleaning' => '����������� ����������',
	'AutoCleanLogs' => '��������� ����������� ��������� �� ����������',
	'AutoCleanLogsMax' => '���� ������, ��� ����� �� �� ������� ����������',
	'AutoCleanLogsMin' => '���� ������ ���� ������������',
	'AutoCleanTmp' => '��������� ����������� ������������ tmp',
	'AutoCleanTmpMax' => '���� ������� ����� ����������',
	'ForTestOnly' => '���� �� ����������� ����',
	'iniCommented' => '����������� ��������� � php.ini (� �������� �� ���� ��� ����� � �������)',
	'BackupHosts' => '�������� ����� �� ����� hosts',
	'ShowWWWdirMenu' => '�������� ������� www � ������',
);

// Right-click Tools
$w_wampTools = '�����������';
$w_restartDNS = '����������� DNS';
$w_testConf = '������� ���������� �� httpd.conf';
$w_testServices = '������� ����������� �� ��������';
$w_changeServices = '������� �� ������� �� ��������';
$w_enterServiceNameApache = "������ �������� ����� �� �������� Apache. �� ���� �������� ��� wampapache.";
$w_enterServiceNameMysql = "������ �������� ����� �� �������� MySQL. �� ���� �������� ��� wampmysqld.";
$w_enterServiceNameAll = "������ ����� �� ���������� ��� ������� �� �������� (������ ������, �� �� �� ����������� �������������� �����).";
$w_compilerVersions = '������� ����������� �� VC, �������������� � .ini ���������';
$w_UseAlternatePort = '��������� �������� �� %s ����';
$w_AddListenPort = '������ ���� �� ������� �� Apache';
$w_vhostConfig = '������ ����������� �� Apache ��������� �������';
$w_apacheLoadedModules = '������ ���������� ������ �� Apache';
$w_apacheLoadedIncludes = '������ ���������� �� Apache �������� �������';
$w_testAliasDir = '������� �������������� ����� ���������� � ����������';
$w_verifyxDebugdll = '������� �� ������������ xDebug dlls';
$w_empty = '�������';
$w_misc = '�����';
$w_emptyAll = '������� ������';

$w_emptyLogs = '������� ����������';
$w_emptyPHPlog = '������� �������� �� ������ �� PHP';
$w_emptyApaErrLog = '������� �������� �� ������ �� Apache';
$w_emptyApaAccLog = '������� �������� �� ������ �� Apache';
$w_emptyMySQLog = '������� �������� �� MySQL';
$w_emptyMariaLog = '������� �������� �� MariaDB';
$w_emptyAllLog ='������� ������ ��������';

$w_dnsorder = '������� ������������������� �� ������� �� DNS';
$w_deleteVer = '������ �������������� ������';
$w_addingVer = '������ ������ �� Apache, PHP, MySQL, MariaDB � ��.';
$w_deleteListenPort = '������ ����� �� ������� �� Apache';
$w_delete = '������';
$w_defaultDBMS = '���� �� ������������:';
$w_invertDefault = '������� ���� �� ������������ ';
$w_changeCLI = '������� �������� �� PHP CLI';
$w_reinstallServices = '������������� ������ ������';
$w_wampReport = '����� �� �������������� �� WampServer';
$w_dowampReport = '������ '.$w_wampReport;
$w_verifySymlink = '������� ������������ ������';
$w_goto = '����� ��';
$w_FileRepository = '������ ��� ����������� �� WampServer �� ������� � �������';

//miscellaneous
$w_ext_spec = '��������� ����������';
$w_ext_zend = '���������� �� Zend';
$w_phpparam_info = '���� �� ����������';
$w_ext_nodll = '������ dll ����';
$w_ext_noline = "������ '����������='";
$w_mod_fixed = "������� �� ���� �� ���� �������.";
$w_no_module = '������ �� ������ ������.';
$w_no_moduleload = "������ LoadModule";
$w_mysql_none = "����";
$w_mysql_user = "������������� �����";
$w_mysql_default = "�� ������������";
$w_mysql_mode = "��������� �� sql-mode";
$w_Size = "������";
$w_Time = "�����";
$w_Integer = "���� �����";
$w_phpMyAdminHelp = "����� �� PhpMyAdmin";

// PromptText for Aestan Tray Menu type: prompt variables
// May have \r\n for multilines
$w_EnterInteger = "������ ���� �����";
$w_enterPort = "������ ������ �� ������� ����";
$w_EnterSize = "������ ������: xxxx � ���� ���� M �� \'����\' � G �� \'����\'.\r\n�������� M ��� G ������ �� ���� ������� �� �������.\r\n��������: 64M ; 256M ; 1G.";
$w_EnterTime = "������ ������� � �������";
$w_MysqlMariaUser = "������ ������� ������������� ���. ��� �� ����� �����, ������ root �� ������������.\r\n��� �� ����� ������ �� root ��� �� ��������� �� ��� ������������� ���, �� ������ �� � �������, ������ ����� Enter password: � ���������. ��� �� �� ����� ������, ������� Enter.";

// Long texts
// Quotation marks " in texts must be escaped: \" - May have \r\n for multilines
$w_addingVerTxt ="������ \"�������\", �.�. ������ ����������� �� ������ �� Apache, PHP, MySQL ��� MariaDB, ����� � ������������� �� ������������ (�� WampServer, Aestan Tray Menu, xDebug � ��.) � �� ��� ������������ (PhpMyAdmin, Adminer) �� ������� ��\r\n\r\n'https://sourceforge.net/projects/wampserver/'\r\n\r\n������ ������� ��������������� �������, ����� �����, � �� ���������, ���� ������� � ����� ����� ����� ����� �� ���������� ���� � ������� \"������� ���� �������������\", �� �� �� ����� ��������� ��� ������������ ���� �� �������� �� �� WampServer.\r\n\r\n���� ���� ��������� �� �������� �� Apache, PHP, MySQL � MariaDB �� ������ � ��� ���������:\r\n� ��� ����� ����� PHP|Apache|MySQL|MariaDB -> ������ -> ������ ������.\r\n\r\n��������� �� �������� �� ������� ��������� �� �����������, ����� �� ������, ���� ��������� ������ ����� �� ������� ��� ������ ������.\r\n\r\n���������� ��-����� ������������ �� Sourceforge � ������ �������� ���������:\r\n\r\n'https://wampserver.aviatechno.net'.\r\n\r\n�� �� ��������� �������� ��� �����������, ������ � ����� ����� � ������ \"�����\".\r\n";
$w_MySQLsqlmodeInfo = "MySQL/MariaDB sql-mode\r\nSQL �������� ���� �� ������ � �������� SQL ������ � ���������� �� ���������� �� ����������� �� sql-mode.\r\n���������� �� ���� ��� ������ ������ ���������� ����� ����������� � ������� ��-������ �������� ��� ���������� �� SQL � ����������� �� �������.\r\n�������� �� ����������� sql-mode ��� ����� my.ini � ����� ������:\r\n\r\n- sql-mode: by default\r\n����������� sql-mode �� ���������� ��� � ������������� (;sql-mode=\"...\").\r\n�������� �� ������������ ������ �� �������� �� MySQL/MariaDB.\r\n\r\n- sql-mode: user mode\r\n����������� sql-mode � ��������� � ���������� �� ����������� ������, �������� :\r\nsql-mode=\"NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_AUTO_CREATE_USER\"\r\n\r\n- sql-mode: none\r\n����������� sql-mode � ������, �� ������ �� ����������:\r\nsql-mode=\"\"\r\n�� �� ������� SQL �����.";
$w_PhpMyAdMinHelpTxt = "-- PhpMyAdmin\r\n������ ��������� phpMyAdmin, �� �� ����� �������� ������������� ��� � ������.\r\n������ ���������� WampServer 3, ��������������� ��� �� ������������ � \"root\" (��� ���������) � ���� ������, ����� ��������, �� ������ �� ������� ������ Password ������.\r\n\r\nPhpMyAdmin � ������������ �� ��������� ������ �� MySQL ��� MariaDB, � ���������� �� ���� ��� � �������.\r\n��� �� ���������� � ����� ����, �� ������ �� ���� �� ��� ����� ������, ��������� \"Server Choice\" (\"����� �� ������\"), ���� �������� �� ������������ �� ���� ����� � �������. ������ ����, ����� ����� �� ��������� ��� ���� ���� �� ����������� �� ����.\r\n�� ����������, �� ��� ���� �������� ������������� �����, �� ������ �� ��������� ���������� �� ��������� ��������� ����.\r\n����� ����: ��� ���� ���� � ���� ������������� ���, ����. root, � �� ����� ����, �� �� ����� �������� ������, �� ������ �� ��������� ���������� �� ����������� ����.\r\n";

?>