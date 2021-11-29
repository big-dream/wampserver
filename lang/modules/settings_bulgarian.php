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
$w_projectsSubMenu = 'Моите проекти';
// VirtualHosts sub-menu
$w_virtualHostsSubMenu = 'Моите виртуални хостове';
$w_add_VirtualHost = 'Управление на виртуалните хостове';
$w_aliasSubMenu = 'Моите псевдоними';
$w_portUsed = 'Използван от Apache порт: ';
$w_portUsedMysql = 'Използван от MySQL порт: ';
$w_portUsedMaria = 'Използван от MariaDB порт: ';
$w_testPortUsed = 'Използван за тестове порт: ';
$w_portForApache = 'Порт за Apache';
$w_listenForApache = 'Порт за слушане, който да се добави в Apache';
$w_portForMysql = 'Порт за MySQL';
$w_testPortMysql = 'Изпробвай порт 3306';
$w_testPortMysqlUsed = 'Изпробвай порт за MySQL, използван: ';
$w_testPortMariaUsed = 'Изпробвай порт за MariaDB, използван: ';

// Right-click Settings
$w_wampSettings = 'Настройки на WAMP';
$w_settings = array(
	'urlAddLocalhost' => 'Добавяй localhost към адреса',
	'VirtualHostSubMenu' => 'Подменю \'Виртуални хостове\'',
	'AliasSubmenu' => 'Подменю \'Псевдоними\'',
	'ProjectSubMenu' => 'Подменю \'Проекти\'',
	'HomepageAtStartup' => 'Страница на WampServer при стартиране',
	'MenuItemOnline' => 'Опция в менюто: \'Постави онлайн/офлайн\'',
	'ItemServicesNames' => 'Опция в менюто \'Инструменти\': Промени имената на услугите',
	'NotCheckVirtualHost' => 'Не проверявай дефинициите на виртуалните хостове',
	'NotCheckDuplicate' => 'Не проверявай за дублиращи се имена на сървъри',
	'VhostAllLocalIp' => 'Позволи местни IP адреси за виртуални хостове, различни от 127.*',
	'SupportMySQL' => 'Използвай MySQL',
	'SupportMariaDB' => 'Използвай MariaDB',
	'DaredevilOptions' => 'Внимание! Само за експерти!',
	'ShowphmyadMenu' => 'Показвай PhpMyAdmin в менюто',
	'ShowadminerMenu' => 'Показвай Adminer в менюто',
	'mariadbUseConsolePrompt' => 'Промени стандартната конзола за MariaDB',
	'mysqlUseConsolePrompt' => 'Промени стандартната конзола за MySQL',
	'NotVerifyPATH' => 'Не проверявай пътя (PATH)',
	'NotVerifyTLD' => 'Не проверявай домейните от първо ниво (TLD)',
	'NotVerifyHosts' => 'Не проверявай файла hosts',
	'Cleaning' => 'Автоматично изчистване',
	'AutoCleanLogs' => 'Изчиствай автоматично файловете на дневниците',
	'AutoCleanLogsMax' => 'Брой редове, при който да се извърши изчистване',
	'AutoCleanLogsMin' => 'Брой редове след почистването',
	'AutoCleanTmp' => 'Изчиствай автоматично директорията tmp',
	'AutoCleanTmpMax' => 'Брой файлове преди изчистване',
	'ForTestOnly' => 'Само за изпитателни цели',
	'iniCommented' => 'Коментирани директиви в php.ini (в началото на реда има точка и запетая)',
	'BackupHosts' => 'Резервно копие на файла hosts',
	'ShowWWWdirMenu' => 'Показвай папката www в менюто',
);

// Right-click Tools
$w_wampTools = 'Инструменти';
$w_restartDNS = 'Рестартирай DNS';
$w_testConf = 'Провери синтаксиса на httpd.conf';
$w_testServices = 'Провери състоянието на услугите';
$w_changeServices = 'Промяна на имената на услугите';
$w_enterServiceNameApache = "Въведи индексно число за услугата Apache. Ще бъде добавено към wampapache.";
$w_enterServiceNameMysql = "Въведи индексно число за услугата MySQL. Ще бъде добавено към wampmysqld.";
$w_enterServiceNameAll = "Въведи число за наставката към имената на услугите (остави празно, за да се възстановят първоначалните имена).";
$w_compilerVersions = 'Провери компилатора за VC, съвместимостта и .ini файловете';
$w_UseAlternatePort = 'Използвай различен от %s порт';
$w_AddListenPort = 'Добави порт за слушане на Apache';
$w_vhostConfig = 'Покажи проверените от Apache виртуални хостове';
$w_apacheLoadedModules = 'Покажи заредените модули на Apache';
$w_apacheLoadedIncludes = 'Покажи заредените от Apache включени файлове';
$w_testAliasDir = 'Провери взаимовръзките между псевдоними и директории';
$w_verifyxDebugdll = 'Провери за неизползвани xDebug dlls';
$w_empty = 'Изчисти';
$w_misc = 'Разни';
$w_emptyAll = 'Изчисти ВСИЧКИ';

$w_emptyLogs = 'Изчисти дневниците';
$w_emptyPHPlog = 'Изчисти дневника за грешки на PHP';
$w_emptyApaErrLog = 'Изчисти дневника за грешки на Apache';
$w_emptyApaAccLog = 'Изчисти дневника за достъп на Apache';
$w_emptyMySQLog = 'Изчисти дневника на MySQL';
$w_emptyMariaLog = 'Изчисти дневника на MariaDB';
$w_emptyAllLog ='Изчисти всички дневници';

$w_dnsorder = 'Провери последовартелността на търсене на DNS';
$w_deleteVer = 'Изтрий неизползваните версии';
$w_addingVer = 'Добави версии на Apache, PHP, MySQL, MariaDB и др.';
$w_deleteListenPort = 'Изтрий порта за слушане на Apache';
$w_delete = 'Изтрий';
$w_defaultDBMS = 'СУБД по подразбиране:';
$w_invertDefault = 'Промени СУБД по подразбиране ';
$w_changeCLI = 'Промени версията на PHP CLI';
$w_reinstallServices = 'Преинсталирай всички услуги';
$w_wampReport = 'отчет за конфигурацията на WampServer';
$w_dowampReport = 'Създай '.$w_wampReport;
$w_verifySymlink = 'Провери символичните връзки';
$w_goto = 'Отиди на';
$w_FileRepository = 'Връзки към хранилището на WampServer за файлове и добавки';

//miscellaneous
$w_ext_spec = 'Специални разширения';
$w_ext_zend = 'Разширения на Zend';
$w_phpparam_info = 'Само за информация';
$w_ext_nodll = 'Липсва dll файл';
$w_ext_noline = "Липсва 'разширение='";
$w_mod_fixed = "Модулът не може да бъде обърнат.";
$w_no_module = 'Файлът на модула липсва.';
$w_no_moduleload = "Липсва LoadModule";
$w_mysql_none = "няма";
$w_mysql_user = "потребителски режим";
$w_mysql_default = "по подразбиране";
$w_mysql_mode = "Обяснение на sql-mode";
$w_Size = "Размер";
$w_Time = "Време";
$w_Integer = "Цяло число";
$w_phpMyAdminHelp = "Помощ за PhpMyAdmin";

// PromptText for Aestan Tray Menu type: prompt variables
// May have \r\n for multilines
$w_EnterInteger = "Въведи цяло число";
$w_enterPort = "Въведи номера на желания порт";
$w_EnterSize = "Въведи размер: xxxx и след него M за \'мега\' и G за \'гига\'.\r\nСимволът M или G трябва да бъде долепен до числото.\r\nНапример: 64M ; 256M ; 1G.";
$w_EnterTime = "Въведи времето в секунди";
$w_MysqlMariaUser = "Въведи валидно потребителско име. Ако не знаеш какво, остави root по подразбиране.\r\nАко си задал парола за root или за избраното от теб потребителско име, ще трябва да я въведеш, когато видиш Enter password: в конзолата. Ако не си задал парола, натисни Enter.";

// Long texts
// Quotation marks " in texts must be escaped: \" - May have \r\n for multilines
$w_addingVerTxt ="Всички \"добавки\", т.е. всички инсталатори на версии на Apache, PHP, MySQL или MariaDB, както и инсталаторите на обновленията (на WampServer, Aestan Tray Menu, xDebug и др.) и на уеб приложенията (PhpMyAdmin, Adminer) се намират на\r\n\r\n'https://sourceforge.net/projects/wampserver/'\r\n\r\nПросто изтегли инсталационните файлове, които искаш, и ги стартирай, като кликнеш с десен бутон върху името на изтегления файл и избереш \"Изпълни като администратор\", за да се стане добавката или приложението част от версията ти на WampServer.\r\n\r\nСлед това промяната на версията на Apache, PHP, MySQL и MariaDB се случва с три кликвания:\r\nс ляв бутон върху PHP|Apache|MySQL|MariaDB -> Версия -> Избери версия.\r\n\r\nПромяната на версията не включва промените на параметрите, които си правил, нито прехвърля базите данни от старата към новата версия.\r\n\r\nСъществува по-добре организирано от Sourceforge и винаги актуално хранилище:\r\n\r\n'https://wampserver.aviatechno.net'.\r\n\r\nЗа да достигнеш връзките към хранилищата, кликни с десен бутон и избери \"Помощ\".\r\n";
$w_MySQLsqlmodeInfo = "MySQL/MariaDB sql-mode\r\nSQL сървърът може да работи в различни SQL режими в зависимост от стойността на директивата на sql-mode.\r\nЗадаването на един или повече режими ограничава някои възможности и изисква по-голямо внимание към синтаксиса на SQL и валидацията на данните.\r\nРаботата на директивата sql-mode във файла my.ini е както следва:\r\n\r\n- sql-mode: by default\r\nДирективата sql-mode не съществува или е откоментирана (;sql-mode=\"...\").\r\nПрилагат се стандартните режими на версията на MySQL/MariaDB.\r\n\r\n- sql-mode: user mode\r\nДирективата sql-mode е попълнена с дефинирани от потребителя режими, например :\r\nsql-mode=\"NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_AUTO_CREATE_USER\"\r\n\r\n- sql-mode: none\r\nДирективата sql-mode е празна, но трябва да съществува:\r\nsql-mode=\"\"\r\nНе се прилага SQL режим.";
$w_PhpMyAdMinHelpTxt = "-- PhpMyAdmin\r\nКогато стартираш phpMyAdmin, ще ти бъдат поискани потребителско име и парола.\r\nКогато инсталираш WampServer 3, потребителското име по подразбиране е \"root\" (без кавичките) и няма парола, което означава, че трябва да оставиш полето Password празно.\r\n\r\nPhpMyAdmin е конфигуриран да позвоялва достъп до MySQL или MariaDB, в зависимост от това кое е активно.\r\nАко са активирани и двете СУБД, на екрана за вход ще има падащ списък, озаглавен \"Server Choice\" (\"Избор на сървър\"), като сървърът по подразбиране ще бъде първи в списъка. Избери СУБД, която искаш да използваш тук като част от процедурата по вход.\r\nНЕ ЗАБРАВЯВАЙ, че ако имаш различни потребителски имена, ще трябва да използваш правилното за съответно избраната СУБД.\r\nОСВЕН ТОВА: Ако имаш едно и също потребителско име, напр. root, и за двете СУБД, но си задал различни пароли, ще трябва да използваш правилната за съответната СУБД.\r\n";

?>