<?php
// French language file for
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
// $w_enterServiceNameAll $w_settings['NotVerifyPATH' -> $w_MysqlMariaUse,
// 3.1.4 $w_settings 'NotVerifyTLD' 'Cleaning' 'AutoCleanLogs' 'AutoCleanLogsMax' 'AutoCleanLogsMax' 'AutoCleanTmp' 'AutoCleanTmpMax' 'iniCommented'
// $w_wampReport - $w_dowampReport
// 3.1.9 $w_settings 'BackupHosts'
// 3.2.0 $w_verifySymlink  - $w_settings['NotVerifyHosts']
// 3.2.1 $w_addingVer - $w_addingVerTxt - $w_goto - $w_FileRepository
// 3.2.2 $w_MysqlMariaUser et $w_EnterSize modifiés - $w_MySQLsqlmodeInfo $w_mysql_mode $w_phpMyAdminHelp $w_PhpMyAdMinHelpTxt
// 3.2.3 https for wampserver.aviatechno

// Projects sub-menu
$w_projectsSubMenu = 'Vos projets';
// VirtualHosts sub-menu
$w_virtualHostsSubMenu = 'Vos VirtualHosts';
$w_add_VirtualHost = 'Gestion VirtualHost';
$w_aliasSubMenu = 'Vos Alias';
$w_portUsed = 'Port utilisé par Apache : ';
$w_portUsedMysql = 'Port utilisé par MySQL : ';
$w_portUsedMaria = 'Port utilisé par MariaDB : ';
$w_testPortUsed = 'Tester port utilisé : ';
$w_portForApache = 'Port pour Apache';
$w_listenForApache = 'Listen Port à ajouter à Apache';
$w_portForMyql = 'Port pour MySQL';
$w_testPortMysql = 'Tester le port 3306';
$w_testPortMysqlUsed = 'Tester port MySQL utilisé : ';
$w_testPortMariaUsed = 'Tester port MariaDB utilisé : ';

// Right-click Settings
$w_wampSettings = 'Paramètres Wamp';
$w_settings = array(
	'urlAddLocalhost' => 'Ajouter localhost dans url',
	'VirtualHostSubMenu' => 'Sous-menu VirtualHosts',
	'AliasSubmenu' => 'Sous-menu Alias',
	'ProjectSubMenu' => 'Sous-menu Projets',
	'HomepageAtStartup' => 'Page d\'accueil Wampserver au démarrage',
	'MenuItemOnline' => 'Item menu : En Ligne/Hors Ligne',
	'ItemServicesNames' => 'Item menu Outils : Changer noms des services',
	'NotCheckVirtualHost' => 'Ne pas vérifier les définitions des VirtualHost',
	'NotCheckDuplicate' => 'Ne pas vérifier ServerName dupliqués',
	'VhostAllLocalIp' => 'Autoriser IP locales VirtualHost autres que 127.*',
	'SupportMySQL' => 'Autoriser MySQL',
	'SupportMariaDB' => 'Autoriser MariaDB',
	'DaredevilOptions' => 'Attention: risqué ! Uniquement pour expert',
	'ShowphmyadMenu' => 'Afficher PhpMyAdmin dans Menu',
	'ShowadminerMenu' => 'Afficher Adminer dans Menu',
	'mariadbUseConsolePrompt' => 'Changer prompt console MariaDB',
	'mysqlUseConsolePrompt' => 'Changer prompt console MySQL',
	'NotVerifyPATH' => 'Ne pas vérifier PATH',
	'NotVerifyTLD' => 'Ne pas vérifier TLD',
	'NotVerifyHosts' => 'Ne pas vérifier fichier hosts',
	'Cleaning' => 'Netoyage automatique',
	'AutoCleanLogs' => 'Nettoyer fichiers de logs automatiquement',
	'AutoCleanLogsMax' => 'Nombre de lignes avant nettoyage',
	'AutoCleanLogsMin' => 'Nombre de lignes après nettoyage',
	'AutoCleanTmp' => 'Nettoyer dossier tmp automatiquement',
	'AutoCleanTmpMax' => 'Nombre de fichiers avant nettoyage',
	'ForTestOnly' => 'Only for test purpose',
	'iniCommented' => 'Directives php.ini commentées (; au début de la ligne)',
	'BackupHosts' => 'Sauvegarde fichier hosts',
);

// Right-click Tools
$w_wampTools = 'Outils';
$w_restartDNS = 'Redémarrage DNS';
$w_testConf = 'Vérifier syntaxe httpd.conf';
$w_testServices = 'Vérifier l\'état des services';
$w_changeServices = 'Changer le nom des services';
$w_enterServiceNameApache = "Entrer un nombre d'index pour le service Apache. Il sera ajouté à 'wampapache'";
$w_enterServiceNameMysql = "Entrer un nombre d'index pour le service Mysql. Il sera ajouté à 'wampmysqld'";
$w_enterServiceNameAll = "Entrer un nombre pour le suffixe des noms des services (Vide pour remettre services d'origine)";
$w_compilerVersions = 'Vérifier compilateur VC, compatibilités et fichiers ini';
$w_UseAlternatePort = 'Utiliser un port autre que %s';
$w_AddListenPort = 'Ajouter un Listen port à Apache';
$w_vhostConfig = 'Afficher les VirtualHost examinés par Apache';
$w_apacheLoadedModules = 'Afficher les modules Apache chargés';
$w_misc = 'Divers';
$w_empty = 'Vider';
$w_emptyAll = 'Vider TOUS les';
$w_dnsorder = 'Vérifier l\'ordre de recherche DNS';
$w_deleteVer = 'Supprimer versions inutilisées';
$w_addingVer = 'Ajouter version Apache, PHP, MySQL, MariaDB, etc.';
$w_deleteListenPort = 'Supprimer un Listen port Apache';
$w_delete = 'Supprimer';
$w_defaultDBMS = 'SGBD par défaut :';
$w_invertDefault = 'Inverser SGBD par defaut ';
$w_changeCLI = 'Changer  version PHP CLI';
$w_reinstallServices = 'Réinstaller tous les services';
$w_wampReport = 'Rapport de configuration Wampserver';
$w_dowampReport = 'Créer '.$w_wampReport;
$w_verifySymlink = 'Vérifier les liens symboliques';
$w_goto = 'Aller à :';
$w_FileRepository = 'Liens dépôts fichiers & addons Wampserver';

//Divers
$w_ext_spec = 'Extensions spéciales';
$w_ext_zend = 'Extensions Zend';
$w_phpparam_info = 'Pour information';
$w_ext_nodll = 'Pas de fichier dll';
$w_ext_noline = "Pas de 'extension='";
$w_mod_fixed = "Module irréversible";
$w_no_module = 'Pas de fichier module';
$w_no_moduleload = "Pas de 'LoadModule'";
$w_mysql_none = "aucun";
$w_mysql_user = "mode utilisateur";
$w_mysql_default = "par défaut";
$w_mysql_mode = "Explications de sql-mode";
$w_Size = "Taille";
$w_Time = "Temps";
$w_Integer = "Nombre entier";
$w_phpMyAdminHelp = "Aide PhpMyAdmin";

// PromptText pour Aestan Tray Menu variables de type: prompt
// Peuvent contenir \r\n pour plusieurs lignes
$w_EnterInteger = "Entrez un nombre entier";
$w_enterPort = "Entrer le numéro de port souhaité";
$w_EnterSize = "Entrez la taille : xxxx suivie par M pour Mega ou G pour Giga.\r\nLe symbole M ou G doit être accolé au nombre.\r\nPar exemple : 64M ; 256M ; 1G";
$w_EnterTime = "Entrez le temps en secondes";
$w_MysqlMariaUser = "Entrer un nom utilisateur valide. Si vous ne savez pas, laissez 'root' par défaut.\r\nSi vous avez mis un mot de passe à root ou à l'utilisateur choisi, il faudra taper ce mot de passe lors de la demande de la console\r\n'Enter password:'\r\nSans mot de passe, uniquement touche Entrée.";

// Textes longs
// Les guillemets " dans les textes doivent être échappés : \" - Peuvent contenir \r\n pour plusieurs lignes
$w_addingVerTxt = "Tous les \"addons\", c'est-à-dire tous les installeurs de versions Apache, PHP, MySQL ou MariaDB ainsi que les installeurs des mises à jour (Wampserver, Aestan Tray Menu, xDebug, etc.) et des applications Web (PhpMyAdmin, Adminer) sont sur:\r\n\r\n'https://sourceforge.net/projects/wampserver/'\r\n\r\nIl suffit de télécharger les fichiers-installeurs que vous voulez et de les lancer par un clic-droit sur le nom du fichier téléchargé puis \"Exécuter en tant qu'administrateur\" pour que l'addon ou l'application soit ajouté à votre version de Wampserver.\r\n\r\nEnsuite, changer de version Apache, PHP, MySQL ou MariaDB est l'affaire de trois clics:\r\nClic-Gauche -> PHP|Apache|MySQL|MariaDB -> Version -> Choisir la version\r\nLe changement de version ne reprend pas les modifications de paramètres que vous auriez pu faire ni ne transfère les bases de données de l'ancienne version vers la nouvelle.\r\n\r\nUn dépôt bien mieux organisé que Sourceforge et toujours à jour existe :\r\n\r\n'https://wampserver.aviatechno.net'\r\n\r\nLes liens sur les dépôts sont dans Clic-Droit -> Aide\r\n";
$w_MySQLsqlmodeInfo = "MySQL/MariaDB sql-mode\r\nLe serveur SQL peut fonctionner dans différents modes SQL en fonction de la valeur de la directive sql-mode.\r\nDéfinir un ou plusieurs modes restreint certaines possibilités et exige une plus grande rigeur dans la syntaxe SQL et la validation des données.\r\nLe fonctionnement de la directive sql-mode du fichier my.ini est le suivant.\r\n\r\n- sql-mode: par défaut\r\nLa directive sql-mode n'existe pas ou est commentée (;sql-mode=\"....\")\r\nLes modes par défaut de la version de MySQL/MariaDB sont appliqués\r\n\r\n- sql-mode: mode utilisateur\r\nLa directive sql-mode est remplie avec des modes définis par l'utilisateur, par exemple :\r\nsql-mode=\"NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_AUTO_CREATE_USER\"\r\n\r\n- sql-mode: aucun\r\nLa directive sql-mode est vide mais doit exister :\r\nsql-mode=\"\"\r\naucun mode SQL n'est appliqué.\r\n";
$w_PhpMyAdMinHelpTxt = "-- PhpMyAdmin\r\nAu lancement de PhpMyAdmin, il vous sera demandé un nom utilisateur et un mot de passe.\r\nAprès installation de Wampserver 3, le nom utilisateur par défaut est \"root\" (Sans les guillemets) et il n'y a pas de mot de passe, ce qui veut dire que vous devez laisser la case Mot de passe du formulaire d'accès vide.\r\n\r\nPhpMyAdmin est configuré pour vous permettre d'accéder à MySQL ou à MariaDB selon les SGBD qui sont actifs.\r\nSi les deux SGBD sont activés, vous verrez un menu déroulant sur l'écran de connexion, appelé \"Choix du serveur\", le serveur par défaut sera affiché en premier dans la liste déroulante. Sélectionnez ici le SGBD que vous souhaitez utiliser dans le cadre de la procédure de connexion.\r\nN'oubliez pas que si vous avez différents comptes d'utilisateurs, vous devez utiliser le bon pour le SGBD sélectionné.\r\nAUSSI : Si vous avez le même compte, c'est-à-dire \"root\" sur les deux SGBD, si vous avez défini des mots de passe différents, vous devez utiliser le bon mot de passe pour le compte et le SGBD.\r\n";

?>