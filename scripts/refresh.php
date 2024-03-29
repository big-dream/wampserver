<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

$doReport = (isset($_SERVER['argv'][1]) && trim($_SERVER['argv'][1]) == 'doreport') ? true : false;
//$doReport = true;

if($doReport) {
	$wampReportTxt = '';
	$wampReport = array_fill_keys(array('gen1','gen3','mysql','mariadb','gen2','phpConf'), '');
	$ReportStartOn = IntlDateFormatter::formatObject(new DateTime('now'),"eeee d MMMM Y '-' HH:mm");
	$wampReport['gen1'] .= time()."\n------ Wampserver configuration report\n".$ReportStartOn."\n";
}

require 'config.inc.php';
require 'wampserver.lib.php';

// Get Aestan Tray Menu version and some variables
$contents = file_get_contents($wampserverIniFile);
$wamp_versions_here['wamp_aestan'] = '0.0.0.0';
if(preg_match('~^AeTrayVersion=([0-9\.]+)\r?$~mi',$contents,$matches) === 1)
	$wamp_versions_here['wamp_aestan'] = $matches[1];
$wamp_Ini['NumberStart'] = 0;
if(preg_match('~^NumberStart=([0-9]+)\r?$~m',$contents,$matches) === 1)
	$wamp_Ini['NumberStart']=$matches[1];
$wamp_Ini['CountStart'] = 0;
if(preg_match('~^CountStart=([0-9]+)\r?$~m',$contents,$matches) ===1)
	$wamp_Ini['CountStart']=$matches[1];
unset($contents);

//Verify some files
require 'refreshVerifyFiles.php';

// *******************
// language management
// Get current language
$lang = $wampConf['language'];

// List of all supported encodings
$List_Encodings = array_map('mb_strtolower',mb_list_encodings());

// Load default language file if exists
require $langDir.$wampConf['defaultLanguage'].'.lang';
$charset_used = $charset_saved = $file_charset;
$charset_used_valid = in_array(mb_strtolower($file_charset),$List_Encodings) ? true : false;

$utf8_file = '';
$use_utf8 = $convertOK = false;
// Load language file if exists
if(file_exists($langDir.$lang.'.lang')){
	//$Text_Encoding['CodePage'] = '65001';
	if(isset($wampConf['utf8_beta']) && $wampConf['utf8_beta'] == 'on' && $Text_Encoding['CodePage'] == '65001') {
		//Beta utf-8 region option is checked
		$utf8_file = '_utf-8';
		$use_utf8 = true;
		if(!file_exists($langDir.$lang.$utf8_file.'.lang')) {
			require $langDir.$lang.'.lang';
			$charset_saved = $file_charset;
			$charset_used_valid = in_array(mb_strtolower($file_charset),$List_Encodings) ? true : false;
			if($charset_used_valid) {
				$temp = file_get_contents($langDir.$lang.'.lang');
				$temp_utf8 = mb_convert_encoding($temp, "utf-8", $file_charset);
				if($temp_utf8 !== false) {
					$convertOK = true;
					$temp_utf8 = str_replace('$file_charset = \''.$file_charset.'\';','$file_charset = \'utf-8\';',$temp_utf8);
					if(write_file($langDir.$lang.$utf8_file.'.lang',$temp_utf8) === false) {
						$convertOK = false;
					}
				}
			}
			else {
				error_log('Encoding: '.$file_charset." is not accepted by mb_list_encoding\n");
			}
			if($convertOK === false) {
				$utf8_file = '';
				$use_utf8 = false;
			}
		}
	}
	require $langDir.$lang.$utf8_file.'.lang';
	$charset_used = $file_charset;
	$charset_used_valid = in_array(mb_strtolower($file_charset),$List_Encodings) ? true : false;
}
unset($file_charset);

// Load modules default language files - settings_english.php
require $langDir.$modulesDir.'settings_'.$wampConf['defaultLanguage'].'.php';
//Save array $w_settings default language
$w_settings_save = $w_settings;

// Load modules current language files if exists
if(file_exists($langDir.$modulesDir.'settings_'.$lang.'.php')) {
	if($use_utf8) {
		if(!file_exists($langDir.$modulesDir.'settings_'.$lang.$utf8_file.'.php')) {
			$temp = file_get_contents($langDir.$modulesDir.'settings_'.$lang.'.php');
			$temp_utf8 = mb_convert_encoding($temp, "utf-8", $charset_saved);
			if($temp_utf8 !== false) {
				if(write_file($langDir.$modulesDir.'settings_'.$lang.$utf8_file.'.php',$temp_utf8) === false) {
					$utf8_file = '';
				}
			}
		}
	}
	require $langDir.$modulesDir.'settings_'.$lang.$utf8_file.'.php';
	//Merge save array with current language
	$w_settings = array_replace($w_settings_save,$w_settings);
}
unset($temp,$temp_utf8);

//Update string to use alternate port.
$w_AlternatePort = sprintf($w_UseAlternatePort, $c_UsedPort);
if($c_UsedPort == $c_DefaultPort) {
	$UrlPort = '';
	$w_newPort = "8080";
}
else {
	$UrlPort = ':'.$c_UsedPort;
	$w_newPort = "80";
}
//Update string if there are more than one Apache Listen ports
$c_listenPort = listen_ports($c_apacheConfFile);
$TplListenPorts = ';';
$ListenPorts = '';
$ListenPortsExists = false;
if(count($c_listenPort) > 1) {
	$ListenPorts = implode(" ",$c_listenPort);
	$TplListenPorts = '';
	$ListenPortsExists = true;
}
//Update string for add Listen Port
$w_addPort = 8081;
while(in_array($w_addPort, $c_listenPort)) {
	$w_addPort++;
}
$w_addPort = (string)$w_addPort;

//Update string to use alternate MySQL port.
$w_AlternateMysqlPort = sprintf($w_UseAlternatePort, $c_UsedMysqlPort);
if($c_UsedMysqlPort == $c_DefaultMysqlPort) {
	$w_newMysqlPort = "3308";
}
else {
	$w_newMysqlPort = "3306";
}
//Update string to use alternate MariaDB port.
$w_AlternateMariaPort = sprintf($w_UseAlternatePort, $c_UsedMariaPort);
if($c_UsedMariaPort == $c_DefaultMysqlPort) {
	$w_newMariaPort = "3309";
}
else {
	$w_newMariaPort = "3306";
}

// ************************************************
//Before to require wampmanager.tpl ($templateFile)
// we need to change some options, otherwise the variables are replaced by their content.
// Retrieve last start date of Wampserver
$WampStartOnOri = $wampConf['wampStartDate'];
// Wampserver last launched date and hour (formated)
$WampStartOn = IntlDateFormatter::formatObject(new DateTime($WampStartOnOri),$w_FormatDate);
if(!$use_utf8 && $charset_used_valid) {
	$temp = mb_convert_encoding($WampStartOn, $charset_used, "utf-8");
	if($temp !== false) $WampStartOn = $temp;
}

// Option to launch Homepage at startup
$RunAtStart = ($wampConf['HomepageAtStartup'] == 'on' ? '' : ';');
// Option to see www dir in menu
$ShowWWWdir = ($wampConf['ShowWWWdirMenu'] == 'on' ? '' : ';');
// Item submenu Apache Check port used (if not 80)
$ApaTestPortUsed = ($wampConf['apacheUseOtherPort'] == 'on' ? '' : ';');
// Item Tools submenu Check MySQL port used (if not 3306)
$MysqlTestPortUsed = (($wampConf['SupportMySQL'] == 'on' && ($wampConf['mysqlUseOtherPort'] == 'on'  && $wampConf['mysqlPortOptionsMenu'] == 'on')) ? '' : ';');
// Item Tools submenu Check MariaDB port used (if not 3306)
$MariaTestPortUsed = (($wampConf['SupportMariaDB'] == 'on' && ($wampConf['mariaUseOtherPort'] == 'on' && $wampConf['mariadbPortOptionsMenu'] == 'on')) ? '' : ';');
$SupportMysqlAndMariaDB = (($wampConf['SupportMariaDB'] == 'on' && $wampConf['SupportMySQL'] == 'on') ? '' : ';');
$MariadbDefault = (($wampConf['SupportMySQL'] == 'on' && $wampConf['SupportMariaDB'] == 'on' && $wampConf['mariaPortUsed'] == $wampConf['mysqlDefaultPort']) ? '' : ';');
$MysqlDefault = (($wampConf['SupportMySQL'] == 'on' && $wampConf['SupportMariaDB'] == 'on' && $wampConf['mysqlPortUsed'] == $wampConf['mysqlDefaultPort']) ? '' : ';');
if(!empty($MariadbDefault) && !empty($MysqlDefault))
	$DefaultDBMS = 'none';
else
	$DefaultDBMS = (empty($MariadbDefault) ? 'MariaDB '.$c_mariadbVersion : 'MySQL '.$c_mysqlVersion);

// Instructions for use file
$c_useFileExists = ';';
if(file_exists($c_installDir.'/instructions_utilisation.pdf')) {
	$c_useFile = 'instructions_utilisation.pdf';
	$c_useFileExists = '';
}
elseif(file_exists($c_installDir.'/instructions_for_use.pdf')) {
	$c_useFile = 'instructions_for_use.pdf';
	$c_useFileExists = '';
}
//Check if Apache Graceful Restart is supported
$Apache_Graceful_Restart = <<< EOF
Action: run; Filename: "{$c_apacheExe}"; Parameters: "-n {$c_apacheService} -k restart"; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
EOF;
$Apache_Service_Restart = <<< EOF
Action: Service; Service: {$c_apacheService}; ServiceAction: restart; Flags: ignoreerrors waituntilterminated
EOF;
$Apache_Restart = $Apache_Service_Restart;
$Apache_Graceful = ';';
if($wampConf['apacheGracefulRestart'] == 'on') {
	$Apache_Restart = $Apache_Graceful_Restart;
	$Apache_Graceful = '';
}

// Wampmanager.ini common append multi actions
// with : Action: multi; Actions: section_name; Flags:appendsection
// ;WAMPCOMMONAPPENDACTIONS
$GotoMySQLRestart = $GotoMariaDBRestart = '';
if($wampConf['SupportMySQL'] == 'on') {
	$GotoMySQLRestart = <<< EOF
[mysql_refresh_start]
Action: run; FileName: "CMD"; Parameters: "/D /C net start {$c_mysqlService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: multi; Actions: refresh_readconfig; Flags:appendsection

EOF;
}
if($wampConf['SupportMariaDB'] == 'on') {
	$GotoMariaDBRestart = <<< EOF
[mariadb_refresh_start]
Action: run; FileName: "CMD"; Parameters: "/D /C net start {$c_mariadbService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: multi; Actions: refresh_readconfig; Flags:appendsection

EOF;
}

//Check some values about Apache VirtualHost
$virtualHost = check_virtualhost();
//Option to show Edit httpd-vhosts.conf
$EditVhostConf  = (($virtualHost['include_vhosts'] === false || $virtualHost['vhosts_exist'] === false) ? ';' : '');
//Translated by in About
$w_translated_by = (isset($w_translated_by )) ? $w_translated_by : '';

//Add value to Wampserver Report
if($doReport) {
$WinVer = php_uname('s').' '.php_uname('r').' '.php_uname('v');
$TextEncoding = print_r($Text_Encoding,true);
$wampReport['gen1'] .= <<< EOF
- {$WinVer}
- Text_Encoding = {$TextEncoding}
- Windows Charset: {$Windows_Charset}
- Wampserver version {$c_wampVersion} - {$c_wampMode}
- Wampserver install version {$c_wampVersionInstall}
- Update $c_wampVersionUpdate
- Install directory: {$c_installDir}
- Default browser: {$c_navigator} {$c_edge}
- Default text editor: {$c_editor}
- Default log viewer: {$c_logviewer}
- Apache {$c_apacheVersion} - Port {$c_UsedPort}
- Additional Apache listening ports: {$ListenPorts}
- PHP {$c_phpVersion}

EOF;
}

//Update MySQL and/or MariaDB my.ini file
//Replace # comment by ; to be compatible with parse_ini_file
//PHP 5.3.0 Hash marks (#) should no longer be used as comments and will throw a deprecation warning if used.
//PHP 7.0.0 Hash marks (#) are no longer recognized as comments.
// Option to support MySQL
$mysqlVersionList = listDir($c_mysqlVersionDir,'checkMysqlConf','mysql',true);
if($wampConf['SupportMySQL'] == 'on' && count($mysqlVersionList) > 0) {
	create_wamp_versions($mysqlVersionList,'mysql');
	if($doReport)	$wampReport['gen3'] .= "MySQL versions seen by refresh listDir:\n".implode(' - ',$mysqlVersionList)."\n";
	$SupportMySQL = '';
	$EmptyMysqlLog = ' '.$c_installDir.'/'.$logDir.'mysql.log';
	$myIniContents = file_get_contents_dos($c_mysqlConfFile);
	$myIniContents = preg_replace('/^#(.*)$/m',';${1}',$myIniContents,-1,$count);
	if($count > 0) {
		write_file($c_mysqlConfFile,$myIniContents);
	}
	unset ($myIniContents);
	if($doReport)	$wampReport['mysql'] .= "\n- MySQL ".$c_mysqlVersion." Port ".$c_UsedMysqlPort;
}
else {
	$SupportMySQL = ';';
	$EmptyMysqlLog = '';
}

// Option to support MariaDB
$mariadbVersionList = listDir($c_mariadbVersionDir,'checkMariaDBConf','mariadb',true);
if($wampConf['SupportMariaDB'] == 'on' && count($mariadbVersionList) > 0) {
	create_wamp_versions($mariadbVersionList,'mariadb');
	if($doReport)	$wampReport['gen3'] .= "MariaDB versions seen by refresh listDir:\n".implode(' - ',$mariadbVersionList)."\n";
	$SupportMariaDB = '';
	$EmptyMariaLog = ' '.$c_installDir.'/'.$logDir.'mariadb.log';

	$myIniContents = file_get_contents_dos($c_mariadbConfFile);
	$myIniContents = preg_replace('/^#(.*)$/m',';${1}',$myIniContents,-1,$count);
	if($count > 0) {
		write_file($c_mariadbConfFile,$myIniContents);
	}
	unset ($myIniContents);
	if($doReport)	$wampReport['mariadb'] .= "\n- MariaDB ".$c_mariadbVersion." Port ".$c_UsedMariaPort;
}
else {
	$SupportMariaDB = ';';
	$EmptyMariaLog = '';
}

// Support mysql Service with mysqld.exe or windows command sc
$mysqlMysqlService = '';
$mysqlCmdScService = ';';
if(isset($wampConf['mysqlServiceCmd']) && $wampConf['mysqlServiceCmd'] == 'windows') {
	$mysqlMysqlService = ';';
	$mysqlCmdScService = '';
}

// Support mariadb Service with mysqld.exe or windows command sc
$mariaMysqlService = '';
$mariaCmdScService = ';';
if(isset($wampConf['mariadbServiceCmd']) && $wampConf['mariadbServiceCmd'] == 'windows') {
	$mariaMysqlService = ';';
	$mariaCmdScService = '';
}

// Option if neither MySQL nor MariaDB
if($SupportMySQL == ';' && $SupportMariaDB == ';') {
	$noDBMS = true;
	$SupportDBMS = ';';
	if($doReport)	$wampReport['gen1'] .="\n--- No DBMS (nor MySQL, nor MariaDB)";
}
else {
	$noDBMS = false;
	$SupportDBMS = '';
}
if($doReport) {
	$wampReport['gen2'] .= <<< EOF

- PHP {$c_phpCliVersion} for Internal Wampserver PHP scripts
EOF;
	$wampConfSections = @parse_ini_file($configurationFile,true,INI_SCANNER_RAW);
	$wampReport['gen2'] .= "\n------ Wampserver configuration ------\n";
	$sections = array('main','options','apacheoptions','mysqloptions','mariadboptions');
	foreach($sections as $section) {
		$wampReport['gen2'] .= "---------- Section [".$section."]\n";
		$nbbyline = 0;
		foreach($wampConfSections[$section] as $key => $value) {
			$wampReport['gen2'] .= str_pad($key." = ".$value,35);
			if(++$nbbyline >= 2) {
				$wampReport['gen2'] .= "\n";
				$nbbyline = 0;
			}
		}
		$wampReport['gen2'] .= "\n";
	}
	$wampReport['gen2'] .= "---------------------------------------------\n";
	unset($wampConfSections,$sections,$section);
}

//Warnings at the end if needed
$WarningsAtEnd 	= false;
$WarningMenu = ';WAMPMENULEFTEND
';
$WarningText = '';

// Get Alias, PhpMyAdmin, Adminer, PhpSysInfo version's
GetAliasVersions();
if($phmyadOK) {
	$temp = '0.0.0';
	foreach($Alias_Contents['PMyAd'] as $value) {
		if(version_compare($Alias_Contents[$value]['version'], $temp, '>'))
			$temp = $Alias_Contents[$value]['version'];
	}
	$wamp_versions_here += array('wamp_phpmyadmin' => $temp);
}

// Get adminer version
$wamp_versions_here += array('wamp_adminer' => $Alias_Contents['adminer']['version']);

// Show Adminer in Wampmanager menu
$adminerMenu = (($Alias_Contents['adminer']['OK'] && $wampConf['ShowadminerMenu'] == 'on') ? '' : ';');

//*** At this point, $phpVersionList, $phpFcgiVersionList, $phpFcgiVersionListUsed
//    $virtualHost, Alias_Contents are correct
//error_log("Alias_Contents=".print_r($Alias_Contents,true));
//error_log("phpFcgiVersionList=\n".print_r($phpFcgiVersionList,true));
//error_log("phpFcgiVersionList unique=\n".print_r(array_unique($phpFcgiVersionList),true));
//error_log("phpFcgiVersionListUsed=\n".print_r($phpFcgiVersionListUsed,true));
//foreach($phpFcgiVersionListUsed as $key => $value) $phpFcgiVersionListUsed[$key] = array_unique($phpFcgiVersionListUsed[$key]);
//error_log("phpFcgiVersionListUsed unique=\n".print_r($phpFcgiVersionListUsed,true));
//error_log("phpVersionList=\n".print_r(array_unique($phpVersionList),true));
//error_log("virtualHost=\n".print_r($virtualHost,true));
//-------------------------------------------------

//**** Check if there is any PHP version used as FCGI
$phpFcgiVersionList = array_unique($phpFcgiVersionList);
$NoPhpFCGI = (isset($c_ApacheDefine['PHPROOT']) && count($phpFcgiVersionList) > 0) ? '' : ';';

//Warning if hosts file is not writable
if(!$c_hostsFile_writable) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\nThere is problem with the file C:\\Windows\\System32\\drivers\\etc\\hosts\r\nIn order to create or modify VirtualHost,\r\nit is imperative to be able to write to the hosts file.\r\nCheck that your anti-virus allows to write the hosts file.\r\n");
	$message .= "\r\n----------------------------------------\r\n".$WarningMsg;
	$WarningText .= 'Type: item; Caption: "hosts file not writable"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	if($doReport)	$wampReport['gen2'] .= "\nFile C:\\Windows\\System32\\drivers\\etc\\hosts is not writable.";
}
else {
	// Verify hosts file contents
	if($wampConf['NotVerifyHosts'] == 'off') {
		//Cleaning of hosts file
		$rewriteHost = $validServer = $localIPv4 = $localIPv6 = false;
		$myHostsContents = file_get_contents($c_hostsFile);
		$myHostsContents = clean_file_contents($myHostsContents,array(2,1),true);
		$rewriteHost = $clean_count;
		//Verify if there is at least one valid ServerName
		if(preg_match('~^(127\.|10\.|172\.16\.|192\.168\.)[ \t]*.*\s?$~m',$myHostsContents) > 0 )
			$validServer = true;
		//Verify at least 127.0.0.1 localhost and ::1 localhost
		if(preg_match('~^127\.0\.0\.1[ \t]*localhost\s?$~m',$myHostsContents) > 0 )
			$localIPv4 = true;
		if(preg_match('~^::1[ \t]*localhost\s?$~m',$myHostsContents) > 0 )
			$localIPv6 = true;
		//Rewrite host file if necessary
		if(!$validServer) {
			$myHostsContents = "#\r\n";
			$rewriteHost = true;
		}
		if(!$localIPv4) {
			$myHostsContents .= "127.0.0.1 localhost\r\n";
			$rewriteHost = true;
		}
		if(!$localIPv6) {
			$myHostsContents .= "::1 localhost\r\n";
			$rewriteHost = true;
		}
		if($rewriteHost) {
			//Try to do a backup of hosts file
			if($wampConf['BackupHosts'] == 'on') {
				@copy($c_hostsFile,$c_hostsFile."_wampsave.".$next_hosts_save);
				$next_hosts_save++;
			}
			$fp = fopen($c_hostsFile, 'r+b');
			if(flock($fp, LOCK_EX)) { // acquire an exclusive lock
				ftruncate($fp, 0); // truncate file
				fwrite($fp, $myHostsContents);
				fflush($fp); // flush output before releasing the lock
				flock($fp, LOCK_UN); // release the lock
			}
			else {
				$errorTxt = 'Unable to write to '.$c_hostsFile.' file';
			  error_log($errorTxt);
  			if(WAMPTRACE_PROCESS) error_log("script ".__FILE__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
			  if($doReport) {
			  	if(!$localIPv4)
			  		$wampReport['gen2'] .= "\n-- No 127.0.0.1 localhost in hosts file";
			  	if(!$localIPv6)
			  		$wampReport['gen2'] .= "\n-- No ::1 localhost in hosts file";
			  	$wampReport['gen2'] .="\n- Unable to rewrite ".$c_hostsFile." file";
			  }
			}
		fclose($fp);
		}
		//Warning if hosts file is too big
		//Count number of lines in hosts file
		if(($c_hostsFile_toobig = count(file($c_hostsFile, FILE_IGNORE_NEW_LINES))) > $wampConf['HostsLinesLimit']) {
			if($doReport)	$wampReport['gen2'] .= "\nToo more lines in ".$c_hostsFile." file";
			$WarningsAtEnd = true;
			$message = color('red',"\r\nThe C:\\Windows\\System32\\drivers\\etc\\hosts file\r\nhas ".$c_hostsFile_toobig." lines\r\nthat's far too much for proper operation.\r\nThe role of the hosts file is to serve as local DNS,\r\ni. e. to give the connections between local IPs and ServerNames.\r\nIts purpose is absolutely not to be used for filtering unwanted urls.\r\n");
			$WarningText .= 'Type: item; Caption: "Too more lines in hosts"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
		}
	}
}

if($doReport) {
	// hosts file
	$wampReport['gen2'] .="\n------ ".$c_hostsFile." file contents ------\n------ Limited to the first 30 lines ------\n";
	$wampReport['gen2'] .= implode(PHP_EOL, array_slice(file($c_hostsFile), 0, 30));
	$wampReport['gen2'] .="\n----------------------------------------------";
	// httpd-vhosts.conf file
	$wampReport['gen2'] .="\n-- ".$c_apacheVhostConfFile." file contents --\n------ Limited to the first 40 lines ------\n";
	$wampReport['gen2'] .= implode(PHP_EOL, array_slice(file($c_apacheVhostConfFile), 0, 40));
	$wampReport['gen2'] .="\n----------------------------------------------";
}

//Warning if tmp/ folder does not exist or is not writable
$checktmp = checkDir($c_installDir.'/tmp');
if($checktmp !== 'OK') {
	if($doReport)	$wampReport['gen2'] .= "\n-- ".$c_installDir."/tmp/ directory doesn't exists or is not writable";
	$WarningsAtEnd = true;
	$message = color('red',"\r\nFolder ".$c_installDir."/tmp\r\n".$checktmp."\r\n");
	$WarningText .= 'Type: item; Caption: "Error '.$c_installDir.'/tmp"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
}
//Warning if syntax error in Apache config files
$command = $c_apacheExe.'  -t';
$output = proc_open_output($command);
if(!empty($output)) {
	if(stripos($output,'Syntax error') !== false) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\nThere is a syntax error in Apache conf files.\r\n".$output."\r\n");
	$WarningText .= 'Type: item; Caption: "Syntax error Apache conf files"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	if($doReport)	$wampReport['gen2'] .= "\nWARNING:\n".$message;
	}
}

//Warning if not Apache variables
if($ApacheDefineError) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\nUnable to find the Apache variables.\r\nThere may be a syntax error in Apache conf files.\r\nTo be checked by the tool integrated in Wampserver:\r\nRight-click -> Tools -> Check httpd.conf syntax.\r\n");
	$WarningText .= 'Type: item; Caption: "Error Apache Variables"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	if($doReport)	$wampReport['gen2'] .= "\nWARNING: Unable to find Apache variables\nThere may be a syntax error in Apache conf files.\n";
}
//Warning if Edge defined as navigator and not Windows 10
if($c_edgeDefinedError) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\nEdge is defined as default browser\r\nEdge should be defined as default navigator only with Windows 10\r\n");
	if($doReport)	$wampReport['gen2'] .= $message;
	$WarningText .= 'Type: item; Caption: "Edge as browser error"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
}
// Verify that default browser exists
if($c_navigator != "Edge" && $c_navigator != "cmd.exe" && $c_navigator != "iexplore.exe") {
	if(!file_exists($c_navigator)) {
		$WarningsAtEnd = true;
		$message = color('red',"\r\n".$c_navigator." is defined as default browser\r\nThis browser exe file does not exist\r\n");
		if($doReport)	$wampReport['gen2'] .= $message;
		$WarningText .= 'Type: item; Caption: "Default browser does not exist"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}
}
// Verify that default editor exists
if(!file_exists($c_editor)) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\n".$c_editor." is defined as default text editor\r\nThis editor exe file does not exist\r\n");
	if($doReport)	$wampReport['gen2'] .= $message;
	$WarningText .= 'Type: item; Caption: "Default text editor does not exist"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
}
// Verify that default logviewer exists
if(!file_exists($c_logviewer)) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\n".$c_logviewer." is defined as default log viewer\r\nThis log viewer exe file does not exist\r\n");
	if($doReport)	$wampReport['gen2'] .= $message;
	$WarningText .= 'Type: item; Caption: "Default log viewer does not exist"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
}

// Forum for help - linklang
$forum = ($lang == 'french') ? '1' : '2';
$LinkLang = ($lang == 'french') ? 'french' : 'english';

//***************************
// Clean logs files if needed
// Get filesize of log files
$logFilesSize = $logFilesSizeClean = array();
foreach($logFilesList as $value) {
	$size = filesize($value);
	$logFilesSizeClean[$value] = $size;
}

if($wampConf['AutoCleanLogs'] == 'on') {
	if($wampConf['AutoCleanLogsMin'] < 1) $wampConf['AutoCleanLogsMin'] = 1;
	foreach($logFilesSizeClean as $key => $value) {
		// Before counting the number of lines in the file, which consumes resources,
		// we look at the size and count the lines only if the size is > 100000 bytes
		if($value > 100000) {
			$fileArray = file($key);
			$fileCount = count($fileArray);
			if($fileCount > $wampConf['AutoCleanLogsMax']) {
				$newLogFile = clean_file_contents(implode(PHP_EOL, array_slice($fileArray, -($wampConf['AutoCleanLogsMin']))),array(1,0),true);
				write_file($key,$newLogFile);
				unset($newLogFile);
			}
			unset($fileArray);
		}
	}
}
foreach($logFilesList as $value) {
	$size = filesize($value);
	$logFilesSize[str_replace($c_installDir.'/'.$logDir,'',$value)] = FileSizeConvert($size);
}
unset($logFilesSizeClean);
// END of clean log files
//***********************

//************************************
// Load Template file as file contents
// Find all variables assigned to the PromptText fields
// of the Aestan Tray menu's Prompt variables type and
// replace the end of lines with #13 and commas with &#44;
$tpl = file_get_contents($templateFile);
if(preg_match_all('~^.*PromptText:[\t ]*"(\$.+)"[\t ]*;.*$~mi',$tpl,$matches) > 0) {
	foreach($matches[1] as $value) {
		$value = str_replace(array('$','{','}'),'',$value);
		if(isset($$value)) {
			$$value = ReplaceAestan($$value);
		}
	}
}
if(preg_match('~^;WAMPMENULEFTSTART\s?\n(Type: separator; Caption: "(.+)"\s?\n){2}~mi',$tpl,$matches) === 1) {
	if(isset($matches[2]) && $matches[2] !== $w_wampbase) {
		$w_wampbase_new = str_replace($matches[2],$w_wampbase,$matches[0],$count);
		if($count > 0) {
			$tpl = str_replace($matches[0],$w_wampbase_new,$tpl,$count);
			if($count > 0) {
				write_file($templateFile,$tpl);
			}
		}
	}
}
unset($tpl,$matches);
// END of PromptText replacements
//*******************************

//**************************************************
// Create definitions of TextMenu (TextKeyx) for Text items
// From $AesTextMenus in config.inc.php
$TextSubmenuName = $TextSubmenuCaption = $Glyph = array();
$TextMenus = '';
foreach($AesTextMenus as $key => $value) {
	if(strpos($value[0],'$') === 0){
		$temp = substr($value[1],1);
		$TextSubmenuName[] = $$temp;
	}
	else
		$TextSubmenuName[] = $value[0];
	if(strpos($value[1],'$') === 0){
		$temp = substr($value[1],1);
		$CaptionTemp = $$temp;
		// Add space at the end of the variable to avoid duplicate Captions
		$$temp .= ' ';
	}
	else{
		$CaptionTemp = $value[1];
	}
	$TextSubmenuCaption[] = $CaptionTemp;
	$TextMenus .= 'TextKey'.$key.'="'.$CaptionTemp.'",'.$value[2].','.$value[3].','.$value[4].','.$value[5].',';
	if(strpos($value[6],'$') === 0) {
		$temp = substr($value[6],1);
		$tempText = trim($$temp);
	}
	else {
		$tempText = $value[6];
	}
	$tempText = ReplaceAestan($tempText, 'ToSpace');
	$TextMenus .= $tempText.',';
	if(is_array($value[7])) {
		$tempText = '';
		foreach($value[7] as $valueTxt) {
			$temp = substr($valueTxt,1);
			$tempText .= $$temp;
		}
	}
	else {
		if(strpos($value[7],'$') === 0 ){
			$temp = substr($value[7],1);
			$tempText = trim($$temp);
		}
		else
			$tempText = $value[7];
	}
	if($value[8] > 0) $tempText = wordwrap($tempText,$value[8],"\r\n");
	$tempText = ReplaceAestan($tempText);
	$TextMenus .= $tempText."\r\n";
	$Glyph[] = ($value[9] > -1) ? '; Glyph: '.$value[9] : '';
}
// Create submenus definitions - Example below
//[AddingVersions]
//Type: item; Caption: "Add Apache, PHP, MySQL, MariaDB, etc. versions."; Action: none
$i = 0;
$TextSubmenus = '';
reset($Glyph);
foreach($TextSubmenuName as $value) {
	$GlyphM = current($Glyph);
	$TextSubmenus .= <<< EOF
[{$value}]
Type: item; Caption: "{$TextSubmenuCaption[$i]}"; Action: none{$GlyphM}

EOF;
$i++;
next($Glyph);
}
// END of TextMenu ************************
//*****************************************

//************************************
// Create definitions of Custom Prompt
// From $AesPromptCustom in config.inc.php
$PromptCustom = AestanMenuColor($AesPromptCustom,'PromptKey');
// Create definitions of TextMenuColor
// From $AesTextMenuColor in config.inc.php
$TextMenuColor = AestanMenuColor($AesTextMenuColor,'TextKeyColor');
// Create definitions of LeftSeparatorMenuColor
// From $AesSeparatorLeftMenuColor in config.inc.php
$SeparatorLeftMenuColor = AestanMenuColor($AesSeparatorLeftMenuColor,'LeftSeparatorKeyColor');
// Create definitions of RightSeparatorMenuColor
// From $AesSeparatorRightMenuColor in config.inc.php
$SeparatorRightMenuColor = AestanMenuColor($AesSeparatorRightMenuColor,'RightSeparatorKeyColor');

// ***************************************************************
// *** Load Template file as require - $tpl is the template string
require $templateFile;

// Do TextMenus replacements
$search = ';WAMPTEXTMENUSTART
';
$tpl = str_replace($search,$search.$TextMenus,$tpl);
$search = ';WAMPITEMSTEXTSTART
';
$tpl = str_replace($search,$search.$TextSubmenus,$tpl);
unset($TextMenus,$TextSubmenus,$TextSubmenuName,$TextSubmenuCaption,$tempText);

// Do CustomPrompt replacement
$search = ';WAMPPROMPTCUSTOMSTART
';
$tpl = str_replace($search,$search.$PromptCustom,$tpl);

// Do TextMenuColor replacement
$search = ';WAMPTEXTMENUCOLORSTART
';
$tpl = str_replace($search,$search.$TextMenuColor,$tpl);

// Do SeparatorLeftMenuColor replacement
$search = ';WAMPLEFTSEPARATORSTART
';
$tpl = str_replace($search,$search.$SeparatorLeftMenuColor,$tpl);

// Do SeparatorRightMenuColor replacement
$search = ';WAMPRIGHTSEPARATORSTART
';
$tpl = str_replace($search,$search.$SeparatorRightMenuColor,$tpl);

unset($PromptCustom,$PromptTemp,$TextMenuColor,$TextTemp,$SeparatorLeftMenuColor,$SeparatorRightMenuColor);

//******************************
// Create PhpMyAdmin menu item's
if($phmyadOK && empty($SupportDBMS)) {
	$ItemMenuPMA = $SubMenuPMA = '';
	$numPhpMy = 0;
	foreach($Alias_Contents['PMyAdVer'] as $key => $none) {
		if($numPhpMy > 0) {
			$ItemMenuPMA .= <<< EOF
Type: separator

EOF;
		}
		$value = $Alias_Contents['PMyAd'][$key];
		$glyph = ($Alias_Contents[$value]['compat']) ? 39 : 23;
		$ItemMenuPMA .= <<< EOF
Type: item; Caption: "{$w_phpmyadmin}	{$Alias_Contents[$value]['version']}{$Alias_Contents[$value]['fcgiaff']}"; Action: run; FileName: "{$c_navigator}"; Parameters: "{$c_edge}http://localhost{$UrlPort}/{$value}/"; Glyph: {$glyph}

EOF;
		$Glyph = ''; $Action = 'hidedb';
		if($Alias_Contents[$value]['hide'] === true) {
			$Glyph = '; Glyph:13';
			$Action = 'nohidedb';
		}
		$ItemMenuPMA .= <<< EOF
Type: Item; Caption: "{$w_PhpMyAdminGoHidedb}"; Action: multi; Actions: phpmyadminhide{$Alias_Contents[$value]['version']}{$Glyph}

EOF;
		$SubMenuPMA .= <<< EOF
[phpmyadminhide{$Alias_Contents[$value]['version']}]
Action: run; FileName: "{$c_phpExe}";Parameters: "changeMiscParam.php phpmyadmin cfghidedb {$Alias_Contents[$value]['path']}/config.inc.php {$Action}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: refresh_readconfig; Flags:appendsection

EOF;
		$Glyph = ''; $Action = 'password';
		if($Alias_Contents[$value]['nopassword'] === true) {
			$Glyph = '; Glyph:13';
			$Action = 'nopassword';
		}
		$ItemMenuPMA .= <<< EOF
Type: Item; Caption: "{$w_PhpMyAdminGoNoPassword}"; Action: multi; Actions: phpmyadminpassword{$Alias_Contents[$value]['version']}{$Glyph}

EOF;
		$SubMenuPMA .= <<< EOF
[phpmyadminpassword{$Alias_Contents[$value]['version']}]
Action: run; FileName: "{$c_phpExe}";Parameters: "changeMiscParam.php phpmyadmin cfgpassword {$Alias_Contents[$value]['path']}/config.inc.php {$Action}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: refresh_readconfig; Flags:appendsection

EOF;
		//Verify if there is php_admin_value's into phpmyadmin alias
		if(!empty($Alias_Contents[$value]['php_admin_value'])) {
			$ItemMenuPMA .= <<< EOF
Type: submenu; Caption: "php_admin_value - php_admin_flag"; Submenu: phpmyadminvalue{$Alias_Contents[$value]['version']}; Glyph: 9

EOF;
			$SubMenuPMA .= <<< EOF
[phpmyadminvalue{$Alias_Contents[$value]['version']}]

EOF;

			// ***************************************************************
			// *** Creating the PhpMyAdmin alias parameters configuration menu
			$PhpMyAdminVersion = $Alias_Contents[$value]['version'];
			//Put alias PhpMyAdmin specific parameters into $PMA_Alias_Params
			$PMA_Alias_Params = array();
			foreach($Alias_Contents[$value]['php_admin_value'] as $kad => $vad) {
				$PMA_Alias_Params[$vad['param']] = $vad['value'];
			}
			$Alias_Path = $Alias_Contents[$value]['aliaspath'];
			$PMA_ConfText = $PMA_ConfTextInfo = $PMA_ConfForInfo = "";
			$PMAParams = array_combine($PMA_Params,$PMA_Params);
			foreach($PMAParams as $next_param_name => $next_param_text) {
			  if(isset($PMA_Alias_Params[$next_param_text])) {
			  	if(array_key_exists($next_param_name, $PMA_ParamsNotOnOff)) {
			  		if($PMA_ParamsNotOnOff[$next_param_name]['change'] !== true) {
			  	  	$params_for_PMA_alias[$next_param_name] = -2;
			  	  	if(!empty($PMA_ParamsNotOnOff[$next_param_name]['msg']))
			  	  		$PMAErrorMsg[$next_param_name] = "\n".$PMA_ParamsNotOnOff[$next_param_name]['msg']."\n";
			   	  	else
								$PMAErrorMsg[$next_param_name] = "\nThe value of this PhpMyAdmin parameter must be modified in the file: wamp64/alias/phpmyadminxyz.alias\n";
			  		}
			  		else {
			  	  $params_for_PMA_alias[$next_param_name] = -3;
			  	  if($PMA_ParamsNotOnOff[$next_param_name]['title'] == 'Special')
			  	  	$params_for_PMA_alias[$next_param_name] = -4;
			  		}
			  	}
			  	elseif(mb_strtolower($PMA_Alias_Params[$next_param_text]) == "off")
			  		$params_for_PMA_alias[$next_param_name] = 'off';
			  	elseif(mb_strtolower($PMA_Alias_Params[$next_param_text]) == "on")
			  		$params_for_PMA_alias[$next_param_name] = 'on';
			  	elseif($PMA_Alias_Params[$next_param_text] == 0)
			  		$params_for_PMA_alias[$next_param_name] = '0';
			  	elseif($PMA_Alias_Params[$next_param_text] == 1)
			  		$params_for_PMA_alias[$next_param_name] = '1';
			  	else
			  	  $params_for_PMA_alias[$next_param_name] = -2;
			  }
			  else //Parameter in PhpMyAdmin does not exist in alias
			    $params_for_PMA_alias[$next_param_name] = -1;
			}


			$action_sup = array();
			$information_only = false;
			foreach ($params_for_PMA_alias as $paramname => $paramstatus) {
				if($params_for_PMA_alias[$paramname] == '1' || $params_for_PMA_alias[$paramname] == 'on') {
			    $PMA_ConfText .= 'Type: item; Caption: "'.$paramname.'"; Glyph: 13; Action: multi; Actions: '.$PMAParams[$paramname].$PhpMyAdminVersion.'
';
				}
				elseif($params_for_PMA_alias[$paramname] == '0' || $params_for_PMA_alias[$paramname] == 'off') {
			    $PMA_ConfText .= 'Type: item; Caption: "'.$paramname.'"; Action: multi; Actions: '.$PMAParams[$paramname].$PhpMyAdminVersion.'
';
				}
				elseif($params_for_PMA_alias[$paramname] == -2) { // I blue to indicate different from 0 or 1 or On or Off
					if(!$information_only) {
						$PMA_ConfForInfo .= 'Type: separator; Caption: "'.$w_phpparam_info.'"
';
						$information_only = true;
					}
					if($seeInfoMessage) {
			    	$PMA_ConfForInfo .= 'Type: item; Caption: "'.$paramname.'  '.$PMA_Alias_Params[$paramname].'"; Action: multi; Actions: '.$PMAParams[$paramname].$PhpMyAdminVersion.'
';
					}
					else {
			    	$PMA_ConfForInfo .= 'Type: item; Caption: "'.$paramname.'   '.$PMA_Alias_Params[$paramname].'"; Action: none
';
					}
				}
				elseif($params_for_PMA_alias[$paramname] == -3) { // Indicate different from 0 or 1 or On or Off but can be changed
					$action_sup[] = $paramname;
					$text = ($PMA_ParamsNotOnOff[$paramname]['title'] == 'Number' ? ' - '.$PMA_ParamsNotOnOff[$paramname]['text'][$PMA_Alias_Params[$paramname]] : '');
					$PMA_ConfText .= 'Type: submenu; Caption: "'.$paramname.'   '.$PMA_Alias_Params[$paramname].$text.'  "; Submenu: '.$paramname.$PhpMyAdminVersion.'; Glyph: 9
';
				}
				elseif($params_for_PMA_alias[$paramname] == -4) { // Indicate different from 0 or 1 or On or Off but can be changed with Special treatment
					$action_sup[] = $paramname;
					$PMA_ConfText .= 'Type: submenu; Caption: "'.$paramname.'   '.$PMA_Alias_Params[$paramname].'  "; Submenu: '.$paramname.$typebase.$PhpMyAdminVersion.'; Glyph: 9
';
				}
			}
			//Check for supplemtary actions
			$MenuSup = $SubMenuSup = array();
			if(count($action_sup) > 0) {
				$i = 0;
				foreach($action_sup as $action) {
					$MenuSup[$i] = $SubMenuSup[$i] = '';
					if($PMA_ParamsNotOnOff[$action]['title'] == 'Special') {
					}
					else {
						$MenuSup[$i] .= '
['.$action.$PhpMyAdminVersion.']
Type: separator; Caption: "'.$PMA_ParamsNotOnOff[$action]['title'].'"
';
						$c_values = $PMA_ParamsNotOnOff[$action]['values'];
						if($PMA_ParamsNotOnOff[$action]['quoted'])
							$quoted = 'quotes';
						else
							$quoted = 'noquotes';
						foreach($c_values as $value) {
							if($value == $PMA_Alias_Params[$action]) continue;
							$text = ($PMA_ParamsNotOnOff[$action]['title'] == 'Number' ? " - ".$PMA_ParamsNotOnOff[$action]['text'][$value] : "");
							$MenuSup[$i] .= 'Type: item; Caption: "'.$value.$text.'"; Action: multi; Actions: '.$action.$value.$PhpMyAdminVersion.'
';
							if(mb_strtolower($value) == 'choose') {
								$param_value = '%'.$PMA_ParamsNotOnOff[$action]['title'].'%';
								$param_third = ' '.$PMA_ParamsNotOnOff[$action]['title'];
								$c_phpRun = $c_phpExe;
							}
							else {
								$param_value = $value;
								$param_third = '';
								$c_phpRun = $c_phpCli;
							}
							$SubMenuSup[$i] .= <<< EOF
[{$action}{$value}{$PhpMyAdminVersion}]
Action: run; FileName: "{$c_phpRun}";Parameters: "changeMiscParam.php phpmyadmin aliasvalue {$Alias_Path} {$quoted} {$action} {$param_value}{$param_third}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: refresh_readconfig; Flags:appendsection

EOF;
						}
					}
				$i++;
				}
			}
			$PMA_ConfText .= $PMA_ConfTextInfo.$PMA_ConfForInfo;

			foreach ($params_for_PMA_alias as $paramname=>$paramstatus) {
				if($params_for_PMA_alias[$paramname] == '1' || $params_for_PMA_alias[$paramname] == '0' || $params_for_PMA_alias[$paramname] == 'on' || $params_for_PMA_alias[$paramname] == 'off') {
					if($params_for_PMA_alias[$paramname] == '1' || $params_for_PMA_alias[$paramname] == '0')
						$SwitchAction = ($params_for_PMA_alias[$paramname] == '1' ? '0' : '1');
					else
						$SwitchAction = ($params_for_PMA_alias[$paramname] == 'on' ? 'off' : 'on');
			  	$PMA_ConfText .= <<< EOF
[{$PMAParams[$paramname]}{$PhpMyAdminVersion}]
Action: run; FileName: "{$c_phpExe}";Parameters: "changeMiscParam.php phpmyadmin aliasflag {$Alias_Path} {$PMAParams[$paramname]} {$SwitchAction}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: refresh_readconfig; Flags:appendsection

EOF;
				}
			  elseif($params_for_PMA_alias[$paramname] == -2)  {//Parameter is neither 'on' nor 'off'
			  	$PMA_ConfText .= '['.$PMAParams[$paramname].$PhpMyAdminVersion.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 6 '.base64_encode($paramname).' '.base64_encode($PMAErrorMsg[$paramname]).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
				}
			}
			if(count($MenuSup) > 0) {
				for($i = 0 ; $i < count($MenuSup); $i++)
					$PMA_ConfText .= $MenuSup[$i].$SubMenuSup[$i];
			}
			// *** End of PhpMyAdmin alias parameters menu ***
			// ***********************************************

			$SubMenuPMA .= $PMA_ConfText;

		}// End php_admin_value's

		$numPhpMy++;
	}
$ItemMenuPMA .= <<< EOF
Type: separator
Type: submenu; Caption: "{$w_phpMyAdminHelp}"; Submenu: phpmyadmin-help; Glyph: 35

EOF;
	$subPhpMyAdmin = <<< EOF
Type: submenu; Caption: "PhpMyAdmin"; Submenu: MultiplephpMyAdmin; Glyph: 39

EOF;
	// Do PhpMyAdmin replacements
	$search = ';WAMPPHPMYADMIN
';
	$tpl = str_replace($search,$search.$subPhpMyAdmin,$tpl);
	$search = ';WAMPMULTIPLEPHPMYADMINSTART
';
	$tpl = str_replace($search,$search.$ItemMenuPMA,$tpl);
	$search = ';WAMPMULTIPLEPHPMYADMINEND
';
	$tpl = str_replace($search,$search.$SubMenuPMA,$tpl);

	// Add warnings PhpMyAdmin if needed
	if($WarningsPMA) {
		$WarningTextAll = '
Type: separator
';
			$tpl = str_replace(';WAMPMULTIPLEPHPMYADMINEND',$WarningTextAll.$WarningMenuPMA.$WarningTextPMA,$tpl);
	}
	unset($ItemMenuPMA,$SubMenuPMA,$SubPhpMyAdmin);
}
// END of PhpMyAdmin menu
//***********************

// ****************************************
// Create menu with the available languages
if($handle = opendir($langDir)) {
	while (false !== ($file = readdir($handle))) {
		if($file != "." && $file != ".." && strpos($file, '_utf-8') === false && preg_match('|\.lang|',$file))	{
			if($file == $lang.'.lang')
				$langList[$file] = 1;
			else
				$langList[$file] = 0;
		}
	}
	closedir($handle);
}

$langText = ";WAMPLANGUAGESTART
Type: separator; Caption: \"".$w_language."\";
";
ksort($langList);
foreach ($langList as $langname => $langstatus) {
  $cleanLangName = str_replace('.lang','',$langname);
  if($langList[$langname] == 1)
    $langText .= 'Type: item; Caption: "'.$cleanLangName.'"; Glyph: 13; Action: multi; Actions: lang_'.$cleanLangName.'
';
  else
    $langText .= 'Type: item; Caption: "'.$cleanLangName.'"; Action: multi; Actions: lang_'.$cleanLangName.'
';

}

foreach ($langList as $langname=>$langstatus) {
  $cleanLangName = str_replace('.lang','',$langname);
  $langText .= <<< EOF
[lang_{$cleanLangName}]
Action: run; FileName: "{$c_phpCli}";Parameters: "changeLanguage.php {$cleanLangName}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: refresh_readconfig; Flags:appendsection

EOF;
}

$tpl = str_replace(';WAMPLANGUAGESTART',$langText,$tpl);
unset($langText);
// END of menu with the available languages
// ****************************************

//Verify if Apache module fcgid_module is loaded
// If yes, Apache variable PHPROOT must exists
$fcgid_module_loaded = is_apache_var('${PHPROOT}');

//***************************
// Creating PHP versions menu
$phpVersionList = listDir($c_phpVersionDir,'checkPhpConf','php',true);
create_wamp_versions($phpVersionList,'php');
if($doReport)	$wampReport['gen3'] .= "PHP versions seen by refresh listDir:\n".implode(' - ',$phpVersionList)."\n";
$myPattern = ';WAMPPHPVERSIONSTART';
$myreplace = $myPattern."
";
$myreplacemenu = $php_iniFCGI = '';
foreach ($phpVersionList as $onePhpVersion) {
	if($fcgid_module_loaded && in_array($onePhpVersion,$phpFcgiVersionList)) {
		$php_iniFCGI .= <<< EOF
Type: item; Caption: "php.ini PHP {$onePhpVersion} [FCGI - CLI]"; Glyph: 33; Action: run; FileName: "{$c_editor}"; parameters: "{$c_phpVersionDir}/php{$onePhpVersion}/php.ini"

EOF;
	}
  $phpGlyph = '';
  //it checks if the PHP is compatible with the current version of apache
  unset($phpConf);
  include $c_phpVersionDir.'/php'.$onePhpVersion.'/'.$wampBinConfFiles;

  $apacheVersionTemp = $wampConf['apacheVersion'];
  while (!isset($phpConf['apache'][$apacheVersionTemp]) && $apacheVersionTemp != '') {
    $pos = strrpos($apacheVersionTemp,'.');
    $apacheVersionTemp = substr($apacheVersionTemp,0,$pos);
  }

  // Is PHP incompatible with the current version of apache
  $incompatiblePhp = 0;
  if(empty($apacheVersionTemp)) {
    $incompatiblePhp = -1;
    $phpGlyph = '; Glyph: 19';
		$phpErrorMsg = "apacheVersion = empty in wampserver.conf file";
  }
  elseif(empty($phpConf['apache'][$apacheVersionTemp]['LoadModuleFile'])) {
    $incompatiblePhp = -2;
    $phpGlyph = '; Glyph: 19';
		$phpErrorMsg = "\$phpConf['apache']['".$apacheVersionTemp."']['LoadModuleFile'] does not exists or is empty in ".$c_phpVersionDir.'/php'.$onePhpVersion.'/'.$wampBinConfFiles;
  }
  elseif(!file_exists($c_phpVersionDir.'/php'.$onePhpVersion.'/'.$phpConf['apache'][$apacheVersionTemp]['LoadModuleFile'])) {
    $incompatiblePhp = -3;
    $phpGlyph = '; Glyph: 19';
		$phpErrorMsg = $c_phpVersionDir.'/php'.$onePhpVersion.'/'.$phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']." does not exists.";
  }

  if($onePhpVersion === $wampConf['phpVersion'])
    $phpGlyph = '; Glyph: 13';

    $myreplace .= 'Type: item; Caption: "'.$onePhpVersion.'"; Action: multi; Actions:switchPhp'.$onePhpVersion.$phpGlyph.'
';
  if($incompatiblePhp == 0) {
  $myreplacemenu .= <<< EOF
[switchPhp{$onePhpVersion}]
Action: run; FileName: "{$c_phpCli}";Parameters: "switchPhpVersion.php {$onePhpVersion}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: apache_stop_start_refresh; Flags:appendsection

EOF;
  }
  else {
  $myreplacemenu .= '[switchPhp'.$onePhpVersion.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 1 '.base64_encode($onePhpVersion).' '.base64_encode($phpErrorMsg).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
  }
}
if(!empty($php_iniFCGI)) {
	$myreplace .= <<< EOF
Type: submenu; Caption: " "; Submenu: AddingVersions; Glyph: 1

[phpiniFCGICLI]
Type: separator; Caption: "php.ini [FCGI - CLI]"
{$php_iniFCGI}

EOF;
}

$tpl = str_replace($myPattern,$myreplace.$myreplacemenu,$tpl);
unset($myreplace,$myreplacemenu,$myPattern);
// END of PHP versions menu
//*************************

// ********************************
// Creating the PHP extensions menu
$PHP_Apache_Module = false;
$PHP_List_Versions = $phpVersionList;
$PHP_FCGI_Mode = false;
//To be able to manage php.ini and phpForApache.ini extensions
//if a PHP version is used both as an Apache module and in FCGI mode.
if(in_array($c_phpVersion,$phpFcgiVersionList)){
	$PHP_List_Versions[] = $c_phpVersion;
	natsort($PHP_List_Versions);
}

foreach($PHP_List_Versions as $php_version_value) {
	$PHP_submenu_txt = '';
	if(!$PHP_Apache_Module && $php_version_value == $c_phpVersion) {
		//PHP used as Apache module
		$PHP_FCGI_Mode = false;
		$PHP_Apache_Module = true;
		$PHP_ini_File = $c_phpConfFile;
		$PHP_ext_Dir = $c_phpExtDir;
		$PHP_version = $PHP_version_NoFCGI = $PHP_version_space = $c_phpVersion;
		$PHP_extSubmenu = false;
		$PHP_Restart = 'apache_restart_refresh';
	}
	else {
		if($fcgid_module_loaded && in_array($php_version_value,$phpFcgiVersionList)) {
			//PHP used as FCGI
			$PHP_FCGI_Mode = true;
			$PHP_ini_File = $c_phpVersionDir.'/php'.$php_version_value.'/php.ini';
			$PHP_ext_Dir = $c_phpVersionDir.'/php'.$php_version_value.'/ext/';
			$PHP_version_NoFCGI = $php_version_value;
			$PHP_version = 'FCGI'.$php_version_value;
			$PHP_version_space = '[FCGI] '.$php_version_value;
			$PHP_extSubmenu = true;
			$PHP_Restart = 'refresh_readconfig';
		}
		else {
			continue;
		}
	}
	$zend_extensions_ver[$PHP_version] = $zend_extensions;

	$myphpini = file_get_contents_dos($PHP_ini_File);
	$myphpini = clean_file_contents($myphpini,array(2,1),false,false,true,$PHP_ini_File);
	$NBextPHPlines = 0;
	//recovering the extensions loading configuration
	preg_match_all('/^extension\s*=\s*"?([a-z0-9_]+)"?.*\r?$/im',$myphpini,$matchesON);
	preg_match_all('/^;extension\s*=\s*"?([a-z0-9_]+)"?.*\r$/im',$myphpini,$matchesOFF);

	$ext = array_fill_keys($matchesON[1], '1') + array_fill_keys($matchesOFF[1], '0');

	//recovering the zend_extensions loading configuration
	preg_match_all('~^zend_extension\s*=\s*"([a-z0-9_:/\-\.]+)\.dll"?~im',$myphpini,$matchesON);
	preg_match_all('~^;zend_extension\s*=\s*"([a-z0-9_:/\-\.]+)\.dll"?~im',$myphpini,$matchesOFF);
	if(count($matchesON[0]) > 0 ) {
		$i = 0 ;
		foreach($matchesON[0] as $value) {
			foreach($zend_extensions_ver[$PHP_version] as $key => $zend_value) {
				if(stripos($value,$key) !== false) {
					$zend_extensions_ver[$PHP_version][$key]['loaded'] = '1';
					$zend_extensions_ver[$PHP_version][$key]['content'] = $matchesON[1][$i];
					$i++;
				}
			}
		}
	}

	if(count($matchesOFF[0]) > 0 ) {
		$i = 0 ;
		foreach($matchesOFF[0] as $value) {
			foreach($zend_extensions_ver[$PHP_version] as $key => $zend_value) {
				if(stripos($value,$key) !== false) {
					$zend_extensions_ver[$PHP_version][$key]['loaded'] = '0';
					$zend_extensions_ver[$PHP_version][$key]['content'] = $matchesOFF[1][$i];
					$i++;
				}
			}
		}
	}

	if(preg_match('/^.*php_xdebug\-([0-9\.]+[alpha|beta|rc1-9]*)\-.*\s?$/im',$zend_extensions_ver[$PHP_version]['php_xdebug']['content'],$matches) > 0 )
		$zend_extensions_ver[$PHP_version]['php_xdebug']['version'] = $matches[1];
	foreach($zend_extensions_ver[$PHP_version] as $key => $value) {
		$ext[$key] = $zend_extensions_ver[$PHP_version][$key]['loaded'];
	}
	ksort($ext);
	$Extensions_in_php_ini = array_combine(array_keys($ext),array_keys($ext));
	// recovering the extensions list (.dll files) present in the directory ext
	$extDirContents = glob($PHP_ext_Dir.'/*.dll');
	array_walk($extDirContents,function(&$item){$item = str_replace('.dll','',basename($item));});
	$dll_in_php_ext_dir = array_combine($extDirContents,$extDirContents);
	//For PHP 7.2.0+ we have to add php_ at the beginning if not
	array_walk($Extensions_in_php_ini,function(&$item,$key){if(strpos($item,'php_') === false)$item = 'php_'.$item;});
	$Extensions_in_php_ini = array_combine($Extensions_in_php_ini,$Extensions_in_php_ini);
	// both tables are "crossed"
	//DLL extension file exists but no extension= line in phpForApache.ini
	$noExtLine = array_diff_key($dll_in_php_ext_dir,$Extensions_in_php_ini);
	//extension= line exists in phpForApache.ini but no dll file
	$noDllFile = array_diff_key($Extensions_in_php_ini,$dll_in_php_ext_dir);
	foreach($noExtLine as $value) {
		if(array_key_exists($value,$zend_extensions_ver[$PHP_version])) {
			$ext[$value] = -4; //dll must be loaded by zend_extension
		}
		elseif(in_array($value,$phpNotLoadExt)) {
			$ext[$value] = -3; //dll not to be loaded by extension = in php.ini
		}
		else {
			$ext[$value] = -1; //dll file exists but not extension line in php.ini
		}
	}
	foreach($noDllFile as $value) {
		if(array_key_exists($value,$zend_extensions_ver[$PHP_version])) {
			$ext[$value] = -4; //dll must be loaded by zend_extension
		}
		elseif(in_array($value,$phpNotLoadExt)) {
			$ext[$value] = -3; //dll not to be loaded by extension = in php.ini
		}
		else {
			$ext[$value] = -2; //extension line in php.ini but not dll file
		}
	}

	// Check if it is a zend_extension
	foreach($ext as $key => $value) {
		if(array_key_exists($key,$zend_extensions_ver[$PHP_version])) {
			$ext[$key] = -4; //dll must be loaded by zend_extension
			// Check if there is content
			if(empty($zend_extensions_ver[$PHP_version][$key]['content'])) {
				$ext[$key] = -5; //Does not exists
			}
			// Check if dll file exists
			elseif(!file_exists($zend_extensions_ver[$PHP_version][$key]['content'].".dll")) {
				$ext[$key] = -6; //Dll not exists
			}
		}
	}

	//we construct the corresponding menu
	$extText = <<< EOF
;WAMPPHP_EXTSTART
[php_ext_{$PHP_version}]
Type: separator; Caption: "{$w_phpExtensions} {$PHP_version_space}"

EOF;

	$extTextInfo = $extTextNoDll = $extTextNoline = '';
	$notloadExt = $notloadExtZend = $notDll = $notLine = false;
	foreach ($ext as $extname=>$extstatus){
	  if($ext[$extname] == 1) {
	  	$NBextPHPlines++;
	    $extText .= 'Type: item; Caption: "'.$extname.'"; Glyph: 13; Action: multi; Actions: php_ext_'.$PHP_version.$extname.'
';
		}
	  elseif($ext[$extname] == -1) {
			if(!$notLine) {
				$extTextNoline .= 'Type: separator; Caption: "'.$w_ext_noline.'"
';
				$notLine = true;
			}
	   	//Warning icon to indicate problem with this extension: No extension line in php.ini
	    $extTextNoline .= 'Type: item; Caption: "'.$extname.'"; Action: multi; Actions: php_ext_'.$PHP_version.$extname.' ; Glyph: 19;
';
		}
	  elseif($ext[$extname] == -2) {
			if(!$notDll) {
				$extTextNoDll .= 'Type: separator; Caption: "'.$w_ext_nodll.'"
';
				$notDll = true;
			}
	   	//Square red icon to indicate problem with this extension: no dll file in ext directory
	    $extTextNoDll .= 'Type: item; Caption: "'.$extname.'"; Action: multi; Actions: php_ext_'.$PHP_version.$extname.' ; Glyph: 11;
';
		}
	  elseif($ext[$extname] == -3) {
			if(!$notloadExt) {
				$extTextInfo .= 'Type: separator; Caption: "'.$w_ext_spec.'"
';
				$notloadExt = true;
			}
	   	//blue || icon to indicate that the dll must not be loaded by extension = in php.ini
	    $extTextInfo .= 'Type: item; Caption: "'.$extname.'"; Action: multi; Actions: php_ext_'.$PHP_version.$extname.' ; Glyph: 22;
';
		}
	  elseif($ext[$extname] == -4) { //Must be loaded by zend_extension
			if(!$notloadExtZend) {
				$extTextInfo .= 'Type: separator; Caption: "'.$w_ext_zend.'"
';
				$notloadExtZend = true;
			}
			$GlyphZend = '';
			if($zend_extensions_ver[$PHP_version][$extname]['loaded'] == '1')	$GlyphZend = "Glyph: 13;";
			$extname_nophp = str_replace('php_','',$extname);
	   	$extTextInfo .= 'Type: item; Caption: "'.$extname_nophp.' '.$zend_extensions_ver[$PHP_version][$extname]['version'].'"; '.$GlyphZend.'Action: multi; Actions: php_ext_'.$PHP_version.$extname.'
';
		}
	  elseif($ext[$extname] == -5) {
	  	 //Zend extension does not exixts - do nothing
	  }
	  elseif($ext[$extname] == -6) { //Zend extension dll file does not exixts - do nothing
			if(!$notDll) {
				$extTextNoDll .= 'Type: separator; Caption: "'.$w_ext_nodll.'"
';
				$notDll = true;
			}
	   	//Square red icon to indicate problem with this extension: no dll file in ext directory
	    $extTextNoDll .= 'Type: item; Caption: "'.$extname.'"; Action: multi; Actions: php_ext_'.$PHP_version.$extname.' ; Glyph: 11;
';
	  }
	  else
	  {
	  	$NBextPHPlines++;
	    $extText .= 'Type: item; Caption: "'.$extname.'"; Action: multi; Actions: php_ext_'.$PHP_version.$extname.'
';
		}
	}
	$extText .= $extTextNoline.$extTextNoDll.$extTextInfo;

	foreach ($ext as $extname=>$extstatus) {
		if($ext[$extname] == 1 || $ext[$extname] == 0) {
			$SwitchAction = ($ext[$extname] == 1 ? 'off' : 'on');
		$extText .= <<< EOF
[php_ext_{$PHP_version}{$extname}]
Action: run; FileName: "{$c_phpCli}";Parameters: "switchPhpExt.php {$extname} {$SwitchAction} {$PHP_version}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: {$PHP_Restart}; Flags:appendsection

EOF;
		}
		elseif($ext[$extname] == -4) {
			$SwitchAction = ($zend_extensions_ver[$PHP_version][$extname]['loaded'] == 1 ? 'zendoff' : 'zendon');
			$extcontent = $zend_extensions_ver[$PHP_version][$extname]['content'];
		$extText .= <<< EOF
[php_ext_{$PHP_version}{$extname}]
Action: run; FileName: "{$c_phpCli}";Parameters: "switchPhpExt.php {$extcontent} {$SwitchAction} {$PHP_version}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: {$PHP_Restart}; Flags:appendsection

EOF;
		}
		elseif($ext[$extname] == -1 || $ext[$extname] == -2 || $ext[$extname] == -3 || $ext[$extname] == -6) {
			$extname_msg = $extname;
			if($ext[$extname] == -1) $msgNum = 3;
			elseif($ext[$extname] == -2) $msgNum = 4;
			elseif($ext[$extname] == -3) $msgNum = 5;
			elseif($ext[$extname] == -6) {
				$msgNum = 16;
				$extname_msg = $zend_extensions_ver[$PHP_version][$extname]['content'];
			}
	    $extText .= '[php_ext_'.$PHP_version.$extname.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php '.$msgNum.' '.base64_encode($extname_msg).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
		}
	}
	//error_log("NBext=".$NBextPHPlines);
	$NBextPHPlines = ceil(($NBextPHPlines)/2);
	if($PHP_extSubmenu) {
		$AesBigMenu[] = array($w_phpExtensions.' '.$PHP_version_space,'$NBextPHPlines',1);
		$PHP_submenu_txt .= <<< EOF

Type: submenu; Caption: "{$w_phpExtensions} {$PHP_version_space}"; SubMenu: php_ext_{$PHP_version}; Glyph: 4
Type: item; Caption: "{$w_PHPloadedExt} {$php_version_value}"; Action: run; FileName: "{$c_phpExe}"; Parameters: "msg.php phploadedextensions {$PHP_version}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated; Glyph: 24

EOF;
	}
	$tpl = str_replace(';WAMPPHP_EXTSTART',$extText,$tpl);
	if(!empty($PHP_submenu_txt)) {
		$tpl = str_replace(';WAMPPHPEXTALLSTART',';WAMPPHPEXTALLSTART'.$PHP_submenu_txt,$tpl);
	}
	unset($extText,$extTextNoline,$extTextNoDll,$extTextInfo,$PHP_submenu_txt);
}//End foreach $phpVersionList
// *** END of PHP extensions menu
// ******************************

// **********************************************
// Creating the PHP parameters configuration menu
$PHP_Apache_Module = false;
$PHP_List_Versions = $phpVersionList;
$PHP_FCGI_Mode = false;
//To be able to manage php.ini and phpForApache.ini extensions
//if a PHP version is used both as an Apache module and in FCGI mode.
if(in_array($c_phpVersion,$phpFcgiVersionList)){
	$PHP_List_Versions[] = $c_phpVersion;
	natsort($PHP_List_Versions);
}
foreach($PHP_List_Versions as $php_version_value) {
	$PHP_submenu_txt = '';
	if(!$PHP_Apache_Module && $php_version_value == $c_phpVersion) {
		//PHP used as Apache module
		$PHP_FCGI_Mode = false;
		$PHP_Apache_Module = true;
		$PHP_ini_File = $c_phpConfFile;
		$PHP_ext_Dir = $c_phpExtDir;
		$PHP_version = $PHP_version_NoFCGI = $PHP_version_space = $c_phpVersion;
		$PHP_extSubmenu = false;
		$PHP_Restart= 'apache_restart_refresh';
	}
	else {
		if($fcgid_module_loaded && in_array($php_version_value,$phpFcgiVersionList)) {
			//PHP used as FCGI
			$PHP_FCGI_Mode = true;
			$PHP_ini_File = $c_phpVersionDir.'/php'.$php_version_value.'/php.ini';
			$PHP_ext_Dir = $c_phpVersionDir.'/php'.$php_version_value.'/ext/';
			$PHP_version_NoFCGI = $php_version_value;
			$PHP_version = 'FCGI'.$php_version_value;
			$PHP_version_space = '[FCGI] '.$php_version_value;
			$PHP_extSubmenu = true;
			$PHP_Restart= 'refresh_readconfig';
		}
		else {
			continue;
		}
	}

$myphpini = parse_ini_file($PHP_ini_File,false,INI_SCANNER_RAW);
$myphpinitxt = file_get_contents($PHP_ini_File);
$phpReportConf = array();

$phpParams = array_combine($phpParams,$phpParams);
foreach($phpParams as $next_param_name => $next_param_text) {
	if(array_key_exists($next_param_name,$myphpini)) $phpReportConf[$next_param_name] = $myphpini[$next_param_name];
  if(isset($myphpini[$next_param_text])) {
  	if(empty($myphpini[$next_param_text]))
  		$params_for_wampini[$next_param_name] = '0';

  	if((stripos($next_param_name, 'xdebug') !== false) && $zend_extensions_ver[$PHP_version]['php_xdebug']['loaded'] == '0') {
			$params_for_wampini[$next_param_name] = -4; //Extension not loaded - Parameter not to display
		}
  	elseif(array_key_exists($next_param_name, $phpParamsNotOnOff)) {
  		if($phpParamsNotOnOff[$next_param_name]['change'] !== true) {
  	  	$params_for_wampini[$next_param_name] = -2;
  	  	$phpErrorMsg = "\nIf you want to change this value, you can do it directly in the file php.ini\nNot to change the wrong file, the best way to access this file is:\nWampmanager icon->PHP->php.ini\n";
  		}
  		else {
  	  	$params_for_wampini[$next_param_name] = -3;
  	  	if($next_param_name == 'xdebug.mode' && $myphpini[$next_param_name] == false)
  	  		$myphpini[$next_param_name] = 'off';
  		}
  	}
  	elseif(mb_strtolower($myphpini[$next_param_text]) == "off")
  		$params_for_wampini[$next_param_name] = 'off';
  	elseif(mb_strtolower($myphpini[$next_param_text]) == "on")
  		$params_for_wampini[$next_param_name] = 'on';
  	elseif($myphpini[$next_param_text] == 0)
  		$params_for_wampini[$next_param_name] = '0';
  	elseif($myphpini[$next_param_text] == 1)
  		$params_for_wampini[$next_param_name] = '1';
  	else
  	  $params_for_wampini[$next_param_name] = -2;
  }
  else {//Parameter in $phpParams (config.inc.php) does not exist in php.ini or is commented
  	if(strpos($myphpinitxt,';'.$next_param_text) === false) { // Directive doesn't exist in php.ini
  		$params_for_wampini[$next_param_name] = -1;
  	}
  	else { // Directive is commented (; before)
    	$params_for_wampini[$next_param_name] = -5;
 		}
  }
  //Parameter exists in php.ini. Check if it is deprecated, suppressed or new
  if($params_for_wampini[$next_param_name] !== -1 && array_key_exists($next_param_name, $phpParamDepSupNew)) {
  	if(isset($phpParamDepSupNew[$next_param_name]['deprecated'])) {
  		$sinceVersion = $phpParamDepSupNew[$next_param_name]['deprecated'];
  		if(version_compare($php_version_value,$sinceVersion,'>=')) {
  			$message_ObsSupNew[$next_param_name] = "PHP ".$php_version_value."  - Directive: ".$next_param_name." is deprecated as of PHP ".$sinceVersion;
  			$params_for_wampini[$next_param_name] = -6;
  		}
  		if(isset($phpParamDepSupNew[$next_param_name]['suppressed'])) {
  			$sinceVersion = $phpParamDepSupNew[$next_param_name]['suppressed'];
  			if(version_compare($php_version_value,$sinceVersion,'>=')) {
  				$message_ObsSupNew[$next_param_name] .= "\nPHP ".$php_version_value." Directive: ".$next_param_name." is suppressed as of PHP ".$sinceVersion;
  			}
  		}
  	}
  	elseif(isset($phpParamDepSupNew[$next_param_name]['suppressed'])) {
  		$sinceVersion = $phpParamDepSupNew[$next_param_name]['suppressed'];
  		if(version_compare($php_version_value,$sinceVersion,'>=')) {
  			$message_ObsSupNew[$next_param_name] = "PHP ".$php_version_value." Directive: ".$next_param_name." is suppressed as of PHP ".$sinceVersion;
  			$params_for_wampini[$next_param_name] = -6;
  		}
  	}
  	elseif(isset($phpParamDepSupNew[$next_param_name]['new'])) {
  		$sinceVersion = $phpParamDepSupNew[$next_param_name]['new'];
  		if(version_compare($php_version_value,$sinceVersion,'<')) {
  			$message_ObsSupNew[$next_param_name] = "PHP ".$php_version_value." Directive: ".$next_param_name." supported only as of PHP ".$sinceVersion;
  			$params_for_wampini[$next_param_name] = -6;
  		}
  	}
  }

}
ksort($phpReportConf);
if($doReport && $PHP_version == $c_phpVersion) {
	//PHP configuration values
	$wampReport['phpConf'] .= "\n-- PHP Configuration values\n\n";
	$nbbyline = 0;
	foreach($phpReportConf as $key => $value) {
		$wampReport['phpConf'] .= str_pad(' '.$key." = ".$value,40);
		if(++$nbbyline >= 2) {
			$wampReport['phpConf'] .= "\n";
			$nbbyline = 0;
		}
	}
	$wampReport['phpConf'] .= "\n";
	$wampReport['phpConf'] .= "\n--------------------------------------------------\n";
}
unset($phpReportConf);

$phpConfText = <<< EOF
;WAMPPHP_PARAMSSTART
[php_params_{$PHP_version}]
Type: separator; Caption: "{$w_phpSettings} {$PHP_version_space}"

EOF;
$phpConfTextInfo = $phpConfTextComment = $phpConfTextDepSupNew = "";
$action_sup = $seeInfoGlyphException = array();
$information_only = $DepSupNew_only = false;
$xDebugSep = false;
$NBparamPHP = $NBparamPHPinfo = $NBparamPHPobs = $NBparamPHPcomment = $NBparamPHPxdebug = 0;
foreach ($params_for_wampini as $paramname => $paramstatus) {
	$seeInfoGlyphException[$paramname] = false;
	$xdebugParam = (strpos($paramname, 'xdebug') !== false) ? true : false;
	if($xdebugParam && $zend_extensions_ver[$PHP_version]['php_xdebug']['loaded'] == '1') {
		$NBparamPHPxdebug++;
		if(!$xDebugSep) {
			$xDebugSep = true;
			$phpConfText .= 'Type: Separator; Caption: "Extension Zend xdebug '.$zend_extensions_ver[$PHP_version]['php_xdebug']['version'].'"
';
		}
	}
  if($params_for_wampini[$paramname] == '1' || $params_for_wampini[$paramname] == 'on') {
		if(!$xdebugParam) $NBparamPHP++;
	  $phpConfText .= 'Type: item; Caption: "'.$paramname.'"; Glyph: 13; Action: multi; Actions: '.$PHP_version.$phpParams[$paramname].'
';
	}
  elseif($params_for_wampini[$paramname] == '0' || $params_for_wampini[$paramname] == 'off') { //It does not display non-existent settings in php.ini
		if(!$xdebugParam) $NBparamPHP++;
    $phpConfText .= 'Type: item; Caption: "'.$paramname.'"; Action: multi; Actions: '.$PHP_version.$phpParams[$paramname].'
';
	}
	elseif($params_for_wampini[$paramname] == -3) { // Indicate different from 0 or 1 or On or Off but can be changed
		if(!$xdebugParam) $NBparamPHP++;
		$action_sup[] = $paramname;
		$phpConfText .= 'Type: submenu; Caption: "'.$paramname.' = '.$myphpini[$paramname].'"; Submenu: '.$PHP_version.$paramname.'; Glyph: 9
';
	}
	elseif($params_for_wampini[$paramname] == -2) { // Information to indicate different from 0 or 1 or On or Off
		$NBparamPHPinfo++;
		if(!$information_only) {
			$phpConfTextInfo .= 'Type: separator; Caption: "'.$w_phpparam_info.'"
';
			$information_only = true;
		}
		// Tests for 'error_reporting'
		if(($paramname == 'error_reporting') && (version_compare($c_phpVersion, '5.4.0') >= 0)) {
			$seeInfoGlyphException[$paramname] = true;
			$report_err = errorLevel($myphpini[$paramname]);
			$phpConfTextInfo .= 'Type: separator;
';
			$firstReportErr = true;
			foreach($report_err as $key => $value) {
				if($firstReportErr) {
    			$phpConfTextInfo .= 'Type: item; Caption: "'.$paramname.' = '.$report_err[$key]['str'].'"; Glyph: 22; Action: multi; Actions: '.$PHP_version.$phpParams[$paramname].'
';
					$firstReportErr = false;
				}
				else {
    			$phpConfTextInfo .= 'Type: item; Caption: "'.$report_err[$key]['str'].'"; Action: none
';
				}
				if(strpos($report_err[$key]['comment'],'^') !== false) {
					list($err_title, $err_info) = explode('^',$report_err[$key]['comment']);
   				$phpConfTextInfo .= 'Type: item; Caption: "       '.$err_title.'"; Action: none
';
					$phpConfTextInfo .= menu_multi_lines($err_info);
				}
				else {
   				$phpConfTextInfo .= menu_multi_lines($report_err[$key]['comment']);
				}
			}
			$phpConfTextInfo .= 'Type: separator;
';
		} // End tests for 'error_reporting'
		else {
			if($seeInfoMessage) {
    		$phpConfTextInfo .= 'Type: item; Caption: "'.$paramname.' = '.$myphpini[$paramname].'"; Action: multi; Actions: '.$PHP_version.$phpParams[$paramname].' ;
';
			}
			else {
    		$phpConfTextInfo .= 'Type: item; Caption: "'.$paramname.' = '.$myphpini[$paramname].'"; Action: none
';
			}
		}
	} //End for -2
	elseif($params_for_wampini[$paramname] == -4) {
		// Do nothing
	}
	elseif($params_for_wampini[$paramname] == -5) {
		$NBparamPHPcomment++;
    $phpConfTextComment .= 'Type: item; Caption: ";'.$paramname.'"; Action: none
';
	}
	elseif($params_for_wampini[$paramname] == -6) { // Parameter Deprecated, Suppressed or New
		$NBparamPHPobs++;
		if(!$DepSupNew_only) {
			$phpConfTextDepSupNew .= 'Type: separator; Caption: "'.$w_phpparam_obs.'"
';
			$DepSupNew_only = true;
		}
    $phpConfTextDepSupNew .= 'Type: item; Caption: "'.$paramname.'"; Action: multi; Actions: '.$PHP_version.$phpParams[$paramname].';Glyph:19
';

	}
} // end foreach $params_for_wampini
// $NBparamPHPlines used for BigMenus (Aestan Tray Menu columns menus)
//error_log("NBparamPHP=".$NBparamPHP."\nNBparamPHPinfo=".$NBparamPHPinfo."\nNBparaPHPcomment=".$NBparamPHPcomment."\nNBparamPHPxdebug=".$NBparamPHPxdebug."\nNBparamPHPobs=".$NBparamPHPobs);
$NBparamPHPlines = $NBparamPHP + 1;
unset($NBparamPHP,$NBparamPHPinfo,$NBparamPHPcomment,$NBparamPHPxdebug);

//Check for supplemtary actions
$MenuSup = $SubMenuSup = array();
if(count($action_sup) > 0) {
	$i = 0;
	foreach($action_sup as $action) {
		$MenuSup[$i] = $SubMenuSup[$i] = '';
		if($action == 'date.timezone') {
			$RegionCitySelected = $myphpini[$action];
			$RegionSelected = $RegionCitySelected;
			$CitySelected = '';
			if(strpos($RegionCitySelected,"/") !== false) {
				list($RegionSelected,$CitySelected) = explode("/",$RegionCitySelected);
			}
			if($phpParamsNotOnOff[$action]['quoted'])
				$quoted = 'quotes';
			else
				$quoted = 'noquotes';
			$MenuSup[$i] .= '['.$PHP_version.$action.']
Type: separator; Caption: "'.$phpParamsNotOnOff[$action]['title'].'"
';
			$tzs = timezone_identifiers_list();
			sort($tzs);
			$regions = $phpParamsNotOnOff[$action]['values'];
			$group = '';
			foreach ($regions as $value) {
					$Glyph = ($value == $RegionSelected ? '; Glyph: 9' : '');
			    $MenuSup[$i] .= 'Type: submenu; Caption: "'.$value.'"; Submenu: '.$PHP_version.'tz'.$value.$Glyph.'
';
			}
			foreach ($tzs as $tz) {
			  $z = explode('/', $tz, 2);
			  if(count($z) != 2 || !in_array($z[0], $regions)) continue;
			  if($group != $z[0]) {
			    $group = $z[0];
			    $MenuSup[$i] .= <<< EOF
[{$PHP_version}tz{$z[0]}]
Type: Separator; Caption: "{$z[0]}"

EOF;
			  }
			  $Glyph = ($tz == $RegionCitySelected ? '; Glyph: 9' : '');
				$MenuSup[$i] .= 'Type: item; Caption: "'.$tz.'"; Action: multi; Actions: '.$PHP_version.'time_'.$tz.$Glyph.'
';
				$SubMenuSup[$i] .= <<< EOF
[{$PHP_version}time_{$tz}]
Action: run; FileName: "{$c_phpCli}";Parameters: "changePhpParam.php {$quoted} {$action} {$tz} none {$PHP_version}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: {$PHP_Restart}; Flags:appendsection

EOF;
			}
		} // End of date.timezone
		else {
			//If parameter doesn't support 'Apache Graceful Restart' but 'Apache service restart'
			$Apache_Restart_Php_Conf = in_array($action,$phpParamsApacheRestart) ? 'Action: multi; Actions: apache_stop_start_refresh; Flags:appendsection' : 'Action: multi; Actions: apache_restart_refresh; Flags:appendsection';
			$MenuSup[$i] .= '['.$PHP_version.$action.']
Type: separator; Caption: "'.$phpParamsNotOnOff[$action]['title'].'"
';
			$c_values = $c_infos = $phpParamsNotOnOff[$action]['values'];
			if(!empty($phpParamsNotOnOff[$action]['infos'])) $c_infos = $phpParamsNotOnOff[$action]['infos'];
			if($phpParamsNotOnOff[$action]['quoted'])
				$quoted = 'quotes';
			else
				$quoted = 'noquotes';
			foreach($c_values as $key => $value) {
				if($value == $myphpini[$action]) continue;
				$value_caption = $value;
				if($c_infos[$key] != $value) $value_caption .= ' - '.$c_infos[$key];
				$MenuSup[$i] .= 'Type: item; Caption: "'.$value_caption.'"; Action: multi; Actions: '.$PHP_version.$action.$value.'
';
				if(mb_strtolower($value) == 'choose') {
					$param_value = '%'.$phpParamsNotOnOff[$action]['title'].'%';
					$param_third = $phpParamsNotOnOff[$action]['title'];
					if($phpParamsNotOnOff[$action]['title'] == 'Integer')
						$param_third .= $phpParamsNotOnOff[$action]['min'].'^'.$phpParamsNotOnOff[$action]['max'].'^'.$phpParamsNotOnOff[$action]['default'];
					$c_phpRun = $c_phpExe;
				}
				else {
					$param_value = $value;
					$param_third = 'none';
					$c_phpRun = $c_phpCli;
				}
				$SubMenuSup[$i] .= <<< EOF
[{$PHP_version}{$action}{$value}]
Action: run; FileName: "{$c_phpRun}";Parameters: "changePhpParam.php {$quoted} {$action} {$param_value} {$param_third} {$PHP_version}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
{$Apache_Restart_Php_Conf}

EOF;
			}
		}
	$i++;
	}
}
// Is there commented php.ini directives ?
$phpConfTextCommentSub = $phpConfTextCommentSubMenu = "";
if(!empty($phpConfTextComment)) {
	$phpConfTextCommentSub .= 'Type: submenu; Caption: "'.$w_settings['iniCommented'].'"; Submenu: '.$PHP_version.'phpinicommented; Glyph: 9
';
	$phpConfTextCommentSubMenu .= <<< EOF
[{$PHP_version}phpinicommented]
{$phpConfTextComment}

EOF;
}
$phpConfText .= $phpConfTextInfo.$phpConfTextCommentSub.$phpConfTextDepSupNew;

foreach ($params_for_wampini as $paramname=>$paramstatus) {
	if($params_for_wampini[$paramname] == '1' || $params_for_wampini[$paramname] == '0' || $params_for_wampini[$paramname] == 'on' || $params_for_wampini[$paramname] == 'off') {
		if($params_for_wampini[$paramname] == '1' || $params_for_wampini[$paramname] == '0')
			$SwitchAction = ($params_for_wampini[$paramname] == '1' ? '0' : '1');
		else
			$SwitchAction = ($params_for_wampini[$paramname] == 'on' ? 'off' : 'on');
		//If parameter doesn't support 'Apache Graceful Restart' but 'Apache service restart'
		$Apache_Restart_Php_Conf = in_array($paramname,$phpParamsApacheRestart) ? 'Action: multi; Actions: apache_stop_start_refresh; Flags:appendsection' : 'Action: multi; Actions: apache_restart_refresh; Flags:appendsection';
  	$phpConfText .= <<< EOF
[{$PHP_version}{$phpParams[$paramname]}]
Action: run; FileName: "{$c_phpCli}";Parameters: "switchPhpParam.php {$phpParams[$paramname]} {$SwitchAction} {$PHP_version}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
{$Apache_Restart_Php_Conf}

EOF;
	}
  elseif($params_for_wampini[$paramname] == -2)  {//Parameter is neither 'on' nor 'off'
  	if($seeInfoMessage || $seeInfoGlyphException[$paramname]) {
  		$phpConfText .= '['.$PHP_version.$phpParams[$paramname].']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 6 '.base64_encode($paramname).' '.base64_encode($phpErrorMsg).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
		}
	}
  elseif($params_for_wampini[$paramname] == -6 )  {//Parameter is Deprecated or Suppressed or New
 		$phpConfText .= '['.$PHP_version.$phpParams[$paramname].']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 17 '.base64_encode($paramname).' '.base64_encode($message_ObsSupNew[$paramname]).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}
}
if(count($MenuSup) > 0) {
	for($i = 0 ; $i < count($MenuSup); $i++)
		$phpConfText .= $MenuSup[$i].$SubMenuSup[$i];
}
$phpConfText .= $phpConfTextCommentSubMenu;

if($PHP_extSubmenu) {
	$AesBigMenu[] = array($w_phpSettings.' '.$PHP_version_space,'$NBparamPHPlines',1);
	$PHP_submenu_txt .= <<< EOF

Type: submenu; Caption: "{$w_phpSettings} {$PHP_version_space}"; SubMenu:php_params_{$PHP_version}; Glyph: 4

EOF;
}
$tpl = str_replace(';WAMPPHP_PARAMSSTART',$phpConfText,$tpl);
if(!empty($PHP_submenu_txt)) {
	$tpl = str_replace(';WAMPPHPPARAMSALLSTART',';WAMPPHPPARAMSALLSTART'.$PHP_submenu_txt,$tpl);
}
unset($phpConfText,$phpConfTextCommentSubMenu,$PHP_submenu_txt);
}//End foreach
// **** END of PHP parameters configuration menu ****
// **************************************************

// *****************************
// Creating Apache versions menu
$apacheVersionList = listDir($c_apacheVersionDir,'checkApacheConf','apache',true);
create_wamp_versions($apacheVersionList,'apache');
if($doReport)	$wampReport['gen3'] .= "Apache versions seen by refresh listDir:\n".implode(' - ',$apacheVersionList)."\n";
$myPattern = ';WAMPAPACHEVERSIONSTART';
$myreplace = $myPattern."
";
$myreplacemenu = '';

foreach ($apacheVersionList as $oneApacheVersion)
{
  $apacheGlyph = '';
	//we check if Apache is compatible with the current version of PHP
  unset($phpConf);
  include $c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$wampBinConfFiles;
  $apacheVersionTemp = $oneApacheVersion;
  while (!isset($phpConf['apache'][$apacheVersionTemp]) && $apacheVersionTemp != '')
  {
    $pos = strrpos($apacheVersionTemp,'.');
    $apacheVersionTemp = substr($apacheVersionTemp,0,$pos);
  }

  // Apache incompatible with the current version of PHP
  $incompatibleApache = 0;
  if(empty($apacheVersionTemp))
  {
    $incompatibleApache = -1;
    $apacheGlyph = '; Glyph: 19';
		$apacheErrorMsg = "apacheVersion = empty in wampmanager.conf file";
  }
  elseif(!isset($phpConf['apache'][$apacheVersionTemp]['LoadModuleFile'])
      || empty($phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']))
  {
    $incompatibleApache = -2;
    $apacheGlyph = '; Glyph: 19';
		$apacheErrorMsg = "\$phpConf['apache']['".$apacheVersionTemp."']['LoadModuleFile'] does not exists or is empty in ".$c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$wampBinConfFiles;
  }
  elseif(!file_exists($c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']))
  {
    $incompatibleApache = -3;
    $apacheGlyph = '; Glyph: 23';
		$apacheErrorMsg = $c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']." does not exists.".PHP_EOL.PHP_EOL."First switch on a version of PHP that contains ".$phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']." file before you change to Apache version ".$oneApacheVersion.".";
  }

  //File wamp/bin/apache/apachex.y.z/wampserver.conf
  $ApacheConfFile = $c_apacheVersionDir.'/apache'.$oneApacheVersion.'/'.$wampBinConfFiles;
  unset($apacheConf);
  include $ApacheConfFile;

  if($oneApacheVersion === $wampConf['apacheVersion'])
    $apacheGlyph = '; Glyph: 13';

  $myreplace .= 'Type: item; Caption: "'.$oneApacheVersion.'"; Action: multi; Actions:switchApache'.$oneApacheVersion.$apacheGlyph.'
';

  if($incompatibleApache == 0)
  {
    $myreplacemenu .= <<< EOF
[switchApache{$oneApacheVersion}]
Action: closeservices; Flags: ignoreerrors
Action: service; Service: {$c_apacheService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: run; Filename: "CMD"; Parameters: "/D /C sc stop {$c_apacheService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; Filename: "CMD"; Parameters: "/D /C sc delete {$c_apacheService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; Filename: "taskkill"; Parameters: "/FI ""IMAGENAME eq httpd.exe"" /T /F"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; FileName: "{$c_phpExe}";Parameters: "switchApacheVersion.php {$oneApacheVersion}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "{$c_phpCli}";Parameters: "switchPhpVersion.php {$wampConf['phpVersion']}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "{$c_apacheVersionDir}/apache{$oneApacheVersion}/{$apacheConf['apacheExeDir']}/{$apacheConf['apacheExeFile']}"; Parameters: "{$apacheConf['apacheServiceInstallParams']}"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; Filename: "CMD"; Parameters: "/D /C sc config {$c_apacheService} start= demand"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; FileName: "{$c_phpExe}";Parameters: "switchWampPort.php {$c_UsedPort}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "CMD"; Parameters: "/D /C net start {$c_apacheService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: resetservices
Action: multi; Actions: refresh_readconfig; Flags:appendsection

EOF;
  }
  else
  {
    $myreplacemenu .= '[switchApache'.$oneApacheVersion.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 2 '.base64_encode($oneApacheVersion).' '.base64_encode($apacheErrorMsg).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
  }
}
$myreplace .= 'Type: submenu; Caption: " "; Submenu: AddingVersions; Glyph: 1

';
$tpl = str_replace($myPattern,$myreplace.$myreplacemenu,$tpl);
// END of Apache versions menu
//****************************

// ************************************
// Creating the menu for Apache modules
$myhttpdContents = @file_get_contents($c_apacheConfFile);
// Recovering the extensions loading configuration
preg_match_all('~^LoadModule\s+([0-9a-z_]+)\s+(?:modules/|)(.+)\r?$~im',$myhttpdContents,$matchesON);
preg_match_all('~^\#LoadModule\s+([0-9a-z_]+)\s+(?:modules/|)(.+)\\r?$~im',$myhttpdContents,$matchesOFF);
// Key = module_name - Value = Module loaded = 1, not loaded = 0
$mod = array_fill_keys($matchesON[1], '1') + array_fill_keys($matchesOFF[1], '0');
// Key = module_name - Value = file name in modules/ folder
$mod_load = array_combine($matchesON[1],$matchesON[2]) + array_combine($matchesOFF[1],$matchesOFF[2]);
array_walk($mod_load,function(&$item){$item = trim($item);});
ksort($mod);
ksort($mod_load);

// Retrieve list of modules in the /modules/ folder
$modDirContents = glob($c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/modules/*.so');
array_walk($modDirContents,function(&$item){$item = basename($item);});
$mod_in_modules_dir = array_combine($modDirContents,$modDirContents);

// xxxxx.so file exists but no LoadModule line in httpd.conf
$noModLine = array_diff($mod_in_modules_dir,$mod_load);
foreach($noModLine as $value) {
	$value = str_replace(array("mod_",".so"),array("","_module"),$value);
	$mod[$value] = -2 ; //Module file exists but no loadModule line in httpd.conf
}

// LoadModule line exists in httpd.conf but no xxxxx.so file in modules/ folder
$noModFile = array_diff($mod_load,$mod_in_modules_dir);
foreach($noModFile as $key => $value) {
	$mod[$key] = -1 ; // loadModule line in httpd.conf but no file .so in modules dir
}

// LoadModule should not be disabled
foreach($apacheModNotDisable as $value) {
	if(array_key_exists($value,$mod))
		$mod[$value] = -3 ; //Apache modules which should not be disabled
}

// Module should not be unload if $virtualHost['index'] is true
foreach($apacheModuleNotUnload as $key => $value) {
	if($virtualHost[$value['index']] === true) {
		$mod[$key] = -4;
		$modmsg[$key] = $value['msg'];
	}
}

$httpdText = 'Type: separator; Caption: "'.$w_apacheModules.'"
';
$httpdTextInfo ="";
$httpdInfo = false;
$httpdTextNoModule ="";
$httpdNoModule = false;
$httpdTextNoLoad ="";
$httpdNoLoad = false;

foreach ($mod as $modname => $modstatus) {
  if($modstatus == 1 || $modstatus == -4) {
  	$Glyph = ($modstatus == -4) ? '21' : '13';
    $httpdText .= 'Type: item; Caption: "'.$modname.'"; Glyph: '.$Glyph.'; Action: multi; Actions: apache_mod_'.$modname.'
';
}
	elseif($modstatus == -1) { //Red square - No module file
		if(!$httpdNoModule) {
			$httpdTextNoModule .= 'Type: separator; Caption: "'.$w_no_module.'"
';
			$httpdNoModule = true;
		}
		$httpdTextNoModule .= 'Type: item; Caption: "'.$modname.'"; Action: multi; Actions: apache_mod_'.$modname.' ; Glyph: 11;
';
	}
	elseif($modstatus == -2) {// /!\ Warning - Module file exists but no loadModule line in httpd.conf
		if(!$httpdNoLoad) {
			$httpdTextNoLoad .= 'Type: separator; Caption: "'.$w_no_moduleload.'"
';
			$httpdNoLoad = true;
		}
		$httpdTextNoLoad .= 'Type: item; Caption: "'.$modname.'"; Action: multi; Actions: apache_mod_'.$modname.' ; Glyph: 19;
';
	}
	elseif($modstatus == -3) { //Apache modules which should not be disabled
		if(!$httpdInfo) {
			$httpdTextInfo .= 'Type: separator; Caption: "'.$w_mod_fixed.'"
Type: item; Caption: "#';
			$httpdInfo = true;
		}
		$httpdTextInfo .= ' #13 '.$modname;
	}
  else
    $httpdText .= 'Type: item; Caption: "'.$modname.'"; Action: multi; Actions: apache_mod_'.$modname.'
';
}
$httpdTextInfo = str_replace('# #13 ','',$httpdTextInfo);
$httpdTextInfo .= '"; Action: none
Type: item; Caption: "'.$w_mod_not_disable.'"; Action: none; Glyph: 22
';

// Apache Compiled in modules
$ApacheCompiledModules = '';
$command = $c_apacheExe." -l";
$output = proc_open_output($command);
if(!empty($output)) {
	if(stripos($output,'Syntax error') !== false) {
		$ApacheCompiledModules .= 'Type: item; Caption: "*** WARNING - Syntax error ***"; Action: none
';
	}
	else {
		if(preg_match_all('~^[ \t]+([a-zA-Z0-9_]+)\.c\r?$~mi',$output,$matches) > 0) {
			$ApacheCompiledModules .= 'Type: separator; Caption: "'.$w_ApacheCompiledIn.'"
';
			$ApacheCompiledModules .= 'Type: item; Caption: "';
			sort($matches[1]);
			$value = implode(' #13 ',$matches[1]);
				$ApacheCompiledModules .= $value.'"; Action: none
Type: item; Caption: "'.$w_ApacheDoesNotIf.'"; Action: none; Glyph: 22
';
		}
	}
}

$httpdText = ";WAMPAPACHE_MODSTART
".$httpdTextNoLoad.$httpdTextNoModule.$httpdText.$httpdTextInfo.$ApacheCompiledModules;

$NBmodApacheLines = 0;
foreach ($mod as $modname => $modstatus) {
	$NBmodApacheLines++;
	if($mod[$modname] == 1 || $mod[$modname] == 0) {
		$SwitchAction = ($mod[$modname] == 1 ? 'on' : 'off');
    $httpdText .= <<< EOF
[apache_mod_{$modname}]
Action: run; FileName: "{$c_phpCli}";Parameters: "switchApacheMod.php {$modname} {$SwitchAction}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: apache_restart_refresh; Flags:appendsection

EOF;
	}
	elseif($mod[$modname] == -1 || $mod[$modname] == -2 || $mod[$modname] == -4) {
		if($mod[$modname] == -1) $msgNum = 7;
		elseif($mod[$modname] == -2) $msgNum = 8;
		elseif($mod[$modname] == -4) $msgNum = 12;
		$msg_add = '';
		if(isset($modmsg[$modname])) $msg_add = ' - '.$modmsg[$modname];
		$modFile = 'mod_'.str_replace('_module','',$modname).'.so';
    $httpdText .= '[apache_mod_'.$modname.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php '.$msgNum.' '.base64_encode($modname.$msg_add).' '.base64_encode($modFile).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}
}

$NBmodApacheLines = ceil(($NBmodApacheLines+8)/4);

$tpl = str_replace(';WAMPAPACHE_MODSTART',$httpdText,$tpl);
// END of menu for Apache modules
//*******************************

//***************************************************
// Creating Apache configuration compare version menu
if($wampConf['apacheCompareVersion'] == 'on' && count($apacheVersionList) > 1) {
	$httpdTextMenu = '';
	$httpdText = ';WAMPAPACHECOMPARE
Type: submenu; Caption: "'.$w_apache_compare.'"; Submenu: apachecompare-help; Glyph: 22
Type: submenu; Caption: "'.$w_compareApache.'"; Submenu: apache_compare; Glyph: 9
';
	$httpdTextSubMenu = ";WAMPAPACHEITEMCOMPARE
[apache_compare]
";
	foreach($apacheVersionList as $value) {
		if($value == $c_apacheVersion) continue;
    $httpdTextSubMenu .= '
Type: item; Caption: "Apache '.$c_apacheVersion.' '.$w_versus.' '.$value.'"; Action: multi; Actions: apache_comp_'.$value.'; Glyph: 23
';
		$httpdTextMenu .= <<< EOF
[apache_comp_{$value}]
Action: run; FileName: "{$c_phpExe}";Parameters: "switchApacheVersion.php {$c_apacheVersion} {$value} compare";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: apache_restart_refresh; Flags:appendsection

EOF;

	}
	$tpl = str_replace(';WAMPAPACHECOMPARE',$httpdText,$tpl);
	$tpl = str_replace(';WAMPAPACHEITEMCOMPARE',$httpdTextSubMenu,$tpl);
	$tpl = str_replace(';WAMPAPACHEITEMMENUS',$httpdTextMenu,$tpl);
}
// END of Apache configuration compare version menu
//*************************************************

//*******************************************
// Creating Apache restore orignal files menu
//The base directory is Apache conf directory ie $c_apacheConfDir
//File were saved into:
$c_apacheOriginalDir = $c_apacheConfDir.'/original/wampserver/';
//To be restored into $c_apacheConfDir or $c_apacheConfDir/extra
if($wampConf['apacheRestoreFiles'] == 'on' && is_dir($c_apacheOriginalDir)) {
	$httpdTextMenu = '';
	$httpdText = ';WAMPAPACHERESTORE
Type: submenu; Caption: "'.$w_apache_restore.'"; Submenu: apacherestore-help; Glyph: 22
Type: submenu; Caption: "'.$w_restorefile.'"; Submenu: apache_restore; Glyph: 9
';
	$httpdTextSubMenu = ";WAMPAPACHEITEMRESTORE
[apache_restore]
";
$restoreFile = read_dir($c_apacheOriginalDir);
	if(count($restoreFile) > 0) {
		foreach($restoreFile as $value) {
			$info = pathinfo($value);
    	$httpdTextSubMenu .= '
Type: item; Caption: "'.$w_restore.' '.$info['basename'].'"; Action: multi; Actions: apache_rest_'.$info['basename'].'; Glyph: 23
';
			$c_extra = '';
			if($info['basename'] == 'httpd-vhosts.conf') $c_extra = "extra\\";
			$command = " /D /C COPY /Y ".str_replace('/','\\',$value)." ".str_replace('/','\\',$c_apacheConfDir).'\\'.$c_extra.$info['basename'];
			$httpdTextMenu .= <<< EOF
[apache_rest_{$info['basename']}]
Action: run; Filename:"{$c_apacheExe}"; Parameters: "-n {$c_apacheService} -k stop"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; FileName: "CMD";Parameters: "{$command}"; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "{$c_phpCli}";Parameters: "switchPhpVersion.php {$wampConf['phpVersion']}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: run; Filename:"{$c_apacheExe}"; Parameters: "-n {$c_apacheService} -k start"; ShowCmd: hidden; Flags: waituntilterminated
Action: multi; Actions: refresh_readconfig; Flags:appendsection

EOF;
		}
		$tpl = str_replace(';WAMPAPACHERESTORE',$httpdText,$tpl);
		$tpl = str_replace(';WAMPAPACHEITEMRESTORE',$httpdTextSubMenu,$tpl);
		$tpl = str_replace(';WAMPAPACHEMENUSRESTORE',$httpdTextMenu,$tpl);
	}
}
// END of Apache restore original files menu
//******************************************

//***********************************
// Creating Apache configuration menu
$httpdText = ";WAMPAPACHEPARAMSSTART
";
$httpdConfParams = array();
$ApacheDefaultOnlyTxt = '';
$ApacheDefault = false;
foreach($apacheParams as $key => $value ) {
	if(preg_match_all('~^('.$key.')[ \t]+('.$value.').*\r?$~mi',$myhttpdContents,$matches) > 0) {
		foreach($matches[1] as $key1 => $value1) {
			$httpdConfParams[][$matches[1][$key1]] = $matches[2][$key1];
		}
	}
	else {
		if(array_key_exists($key,$apacheParamsDefault)) {
		if(!$ApacheDefault) {
			$ApacheDefaultOnlyTxt .= 'Type: separator; Caption: "'.$w_apacheDefaults.'"
';
			$ApacheDefault = true;
		}
			$ApacheDefaultOnlyTxt .= "Type: item; Caption: \"".$key."  ".$apacheParamsDefault[$key]."\"; Action: none
";
		}
		else {
			$httpdConfParams[][$key] = '>>>ERROR<<<';
		}
	}
}

$tab = "  ";
foreach($httpdConfParams as $key => $value) {
	foreach($value as $key1 => $value1) {
		if($value1 == '>>>ERROR<<<') {
			$readValue = 'unknown';
			if(preg_match('~^('.$key1.')[ \t]+(.+)\r?$~mi',$myhttpdContents,$matches) > 0) {
				$readValue = trim($matches[2]);
			}
			$message = " The value: '".$readValue."' for Apache httpd.conf directive '".$key1."' is not good.\n\n";
			$message .= " Accepted values are:\n".implode("\n",explode('|',$apacheParams[$key1]))."\n";
			$httpdText .= 'Type: item; Caption: "'.$key1.' '.$value1.'"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';

		}
		else {
			$httpdText .= "Type: item; Caption: \"".$key1."  ".$value1."\"; Action: none
";
		}
	}
}

$tpl = str_replace(';WAMPAPACHEPARAMSSTART',$httpdText.$ApacheDefaultOnlyTxt,$tpl);
// END of Apache configuration menu
//*********************************

// **************************
// Creating alias Apache menu
$aliasDirContents = glob($aliasDir.'/*.conf');
array_walk($aliasDirContents,function(&$item){$item = basename($item);});

$myreplace = $myreplacemenu = $mydeletemenu = '';
foreach ($aliasDirContents as $one_alias)
{
  $mypattern = ';WAMPADDALIAS';
  $newalias_dir = str_replace('.conf','',$one_alias);
  $alias_contents = @file_get_contents ($aliasDir.$one_alias);
  preg_match('|^Alias /'.$newalias_dir.'/ "(.+)"|',$alias_contents,$match);
  $newalias_dest = (isset($match[1])  ? $match[1] : NULL);

	$newalias_dir_ori = $newalias_dir;
	$newalias_dir_del = str_replace(' ','-whitespace-',$newalias_dir);
	$newalias_dir = str_replace(' ','_',$newalias_dir);
  $myreplace .= 'Type: submenu; Caption: "http://localhost/'.$newalias_dir.'/"; SubMenu: alias_'.$newalias_dir.'; Glyph: 3
';

  $myreplacemenu .= <<< EOF

[alias_{$newalias_dir}]
Type: separator; Caption: "{$newalias_dir}"
Type: item; Caption: "{$w_editAlias}"; Glyph: 33; Action: multi; Actions: edit_{$newalias_dir}
Type: item; Caption: "{$w_deleteAlias}"; Glyph: 26; Action: multi; Actions: delete_{$newalias_dir}

EOF;

  $mydeletemenu .= <<< EOF

[delete_{$newalias_dir}]
Action: run; FileName: "{$c_phpExe}";Parameters: "deleteAlias.php {$newalias_dir_del}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: apache_restart_refresh; Flags:appendsection
[edit_{$newalias_dir}]
Action: run; FileName: "{$c_editor}"; parameters:"{$c_installDir}/alias/{$newalias_dir_ori}.conf"

EOF;

}

$tpl = str_replace($mypattern,$myreplace.$myreplacemenu.$mydeletemenu,$tpl);
// END of alias Apache menu
// ************************

//**************************************
// Creating DBMS (MySQL - MariaDB) menus
$myDBMSPattern = ';WAMPDBMSMENU';
$myDBMSreplace = $myDBMSPattern."
";
$TestPort3306 = '';
if($noDBMS) {
	$nbDBMS = 0;
	$myDBMSreplace .= <<< EOF
Type: separator; Caption: "{$w_noDBMS}"
Type: separator
EOF;
}
else { // At least one DBMS MySQL and/or MariaDB
	$myDBMSreplacearray = array();
	$DBMSdefault = '';
	$NoDefaultDBMS = true;
	$DBMSList = array();
	// Arrange MariaDB and MySQL tools if both
	$myPattern = ';WAMPMYSQLMARIADBTOOLSORDER';
	$myreplace = $myPattern."
;WAMPMYSQLSUPPORTTOOLS
;WAMPMARIADBSUPPORTTOOLS
";
	// MySQL versions and settings
	if($wampConf['SupportMySQL'] == 'on') {
		$glyph = '28';
		$DBMSList[] = 'refreshMySQL.php';
		if($c_UsedMysqlPort == $c_DefaultMysqlPort) {
			$glyph = '36';
			$DBMSdefault = 'mysql';
			$NoDefaultDBMS = false;
		}
		$myDBMSreplacearray[] = <<< EOF
Type: submenu; Caption: "MySQL		{$c_mysqlVersion}"; SubMenu: mysqlMenu; Glyph: {$glyph}

EOF;
	}
	//MariaDB versions and settings
	if($wampConf['SupportMariaDB'] == 'on') {
		$glyph = '28';
		$DBMSList[] = 'refreshMariadb.php';
		if($c_UsedMariaPort == $c_DefaultMysqlPort) {
			$glyph = '36';
			$DBMSdefault = 'mariadb';
			$NoDefaultDBMS = false;
		}
	$myDBMSreplacearray[] = <<< EOF
Type: submenu; Caption: "MariaDB		{$c_mariadbVersion}"; SubMenu: mariadbMenu; Glyph: {$glyph}

EOF;
	}
	$nbDBMS = count($DBMSList);
	if($nbDBMS > 1 && $DBMSdefault == 'mariadb') {
		$myreplace = $myPattern."
;WAMPMARIADBSUPPORTTOOLS
;WAMPMYSQLSUPPORTTOOLS
";
		krsort($myDBMSreplacearray);
		krsort($DBMSList);
	}
	$tpl = str_replace($myPattern,$myreplace,$tpl); //$w_NoDefaultDBMS
	if($nbDBMS > 0) {
		$CaptionDBMS = $NoDefaultDBMS ? $w_NoDefaultDBMS : $w_defaultDBMS.' '.$DBMSdefault;
		$DBMSHeader = <<< EOF
Type: separator; Caption: "{$CaptionDBMS}"

EOF;
		$DBMSFooter = <<< EOF
Type: submenu; Caption: "{$w_MariaDBMySQLHelp}"; Submenu: mariadb-mysql-help; Glyph: 31

EOF;

		array_unshift($myDBMSreplacearray,$DBMSHeader);
		array_push($myDBMSreplacearray,$DBMSFooter);

		if($NoDefaultDBMS) {
			$WarningsAtEnd = true;
			$message = color('red',"\r\nNeither MariaDB nor MySQL is configured as the default SGDB.\r\nSee Right-Click -> Help -> MariaDB - MySQL\r\n");
			if($doReport)	$wampReport['gen2'] .= $message;
			$WarningText .= 'Type: item; Caption: "No Default DBMS"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
		}
		// Check that MySQL and MariaDB ports are not the same
		if($nbDBMS > 1) {
			if($c_mysqlPortUsed == $c_mariadbPortUsed) {
				$WarningsAtEnd = true;
				$message = color('red',"\r\nMySQL and MariaDB use the same port: ".$c_mysqlPortUsed."\r\nIt is not possible\r\nOne of the ports must be changed in wampmanager.conf and in the related my.ini file.");
				if($doReport)	$wampReport['gen2'] .= $message;
				$WarningText .= 'Type: item; Caption: "Same port for MySQL and MariaDB"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
			}
		}
	}
	foreach($DBMSList as $value) include $value;
	foreach($myDBMSreplacearray as $value) $myDBMSreplace .= $value;
}
$tpl = str_replace($myDBMSPattern,$myDBMSreplace,$tpl);

// END of DBMS (MySQL - MariaDB) menus
//************************************

//*************************
// Creating local test menu
if($wampConf['LocalTest'] == 'on') {
	$LOCALPattern = ';WAMPLOCALTEST';
	$LOCALreplace = $LOCALPattern."
";
	$LOCALreplace .= <<< EOF
Type: separator; Caption: "For local test only"
Type: item; Caption: "For local test only"; Action: run; FileName: "{$c_phpExe}"; Parameters: "test.php";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated; Glyph: 9

EOF;
	$tpl = str_replace($LOCALPattern,$LOCALreplace,$tpl);
}
// END of local test menu
//***********************

//****************************************************************
// Creating tools menu to invert default DBMS if MySQL and MariaDB
if($nbDBMS > 1) {
	if($DBMSdefault == 'mariadb') {
		$myPattern = ';WAMPSWITCHMARIAMYSQLSTART';
		$myreplace = <<< EOF
;WAMPSWITCHMARIAMYSQLSTART
[SwitchMariaToMysql]
Action: service; Service: {$c_mariadbService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: service; Service: {$c_mysqlService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "{$c_phpExe}"; Parameters: "switchMariaPort.php 3307 nocheck";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "{$c_phpExe}"; Parameters: "switchMysqlPort.php 3306 nocheck";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: service; Service: {$c_mariadbService}; ServiceAction: startresume; Flags: ignoreerrors waituntilterminated
Action: service; Service: {$c_mysqlService}; ServiceAction: startresume; Flags: ignoreerrors waituntilterminated
Action: multi; Actions: apache_restart_refresh; Flags:appendsection
EOF;
		$tpl = str_replace($myPattern,$myreplace,$tpl);
	}
	else {
		$myPattern = ';WAMPSWITCHMYSQLMARIASTART';
		$myreplace = <<< EOF
;WAMPSWITCHMYSQLMARIASTART
[SwitchMysqlToMaria]
Action: service; Service: {$c_mysqlService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: service; Service: {$c_mariadbService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "{$c_phpExe}"; Parameters: "switchMysqlPort.php 3308 nocheck";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "{$c_phpExe}"; Parameters: "switchMariaPort.php 3306 nocheck";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: service; Service: {$c_mariadbService}; ServiceAction: startresume; Flags: ignoreerrors waituntilterminated
Action: service; Service: {$c_mysqlService}; ServiceAction: startresume; Flags: ignoreerrors waituntilterminated
Action: multi; Actions: apache_restart_refresh; Flags:appendsection
EOF;
		$tpl = str_replace($myPattern,$myreplace,$tpl);
	}
}
// END of tools menu to invert default DBMS if MySQL and MariaDB
//**************************************************************

//***********************
// Creating Alias submenu
if($wampConf['AliasSubmenu'] == "on") {
	//Add item for submenu
	$myPattern = ';WAMPALIASSUBMENU';
	$myreplace = $myPattern."
";
	$myreplacesubmenu = 'Type: submenu; Caption: "'.$w_aliasSubMenu.'"; Submenu: myAliasMenu; Glyph: 3
';
	$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenu,$tpl);

	//Add submenu
	$myPattern = ';WAMPMENULEFTEND';
	$myreplace = $myPattern."
";
	$myreplacesubmenu = '

[myAliasMenu]
;WAMPALIASMENUSTART
;WAMPALIASMENUEND

';
	$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenu,$tpl);

	//Construct submenu
	$myPattern = ';WAMPALIASMENUSTART';
	$myreplace = $myPattern."
Type: separator; Caption: \"".$w_aliasSubMenu."\"
";
	$myreplacesubmenuAlias = '';
	if(count($Alias_Contents['alias']) > 0)	{
		//$Alias_Contents['alias'] = array_unique($Alias_Contents['alias']);
		//error_log("Alias_Contents=".print_r($Alias_Contents,true));
		foreach($Alias_Contents['alias'] as $AliasValue) {
			$glyph = (strpos($AliasValue,'phpmyadmin') !== false || strpos($AliasValue,'adminer') !== false) ? '28' : '5';
			$myreplacesubmenuAlias .= 'Type: item; Caption: "'.$AliasValue.$Alias_Contents[$AliasValue]['fcgiaff'].'"; Action: run; FileName: "'.$c_navigator.'"; Parameters: "';
			$myreplacesubmenuAlias .= $c_edge.'http://localhost'.$UrlPort.'/'.$Alias_Contents[$AliasValue]['alias'].'/"; Glyph: '.$glyph.'
';
		}
		$myreplacesubmenuAlias = str_replace('//','/',$myreplacesubmenuAlias);
		$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenuAlias,$tpl);
	}
}
// END of Alias submenu
// ********************

//*******************************
// Creating Virtual Hosts submenu
//Add item for submenu
$myPattern = ';WAMPVHOSTSUBMENU';
$myreplace = $myPattern."
";
$myreplacesubmenu = 'Type: submenu; Caption: "'.$w_virtualHostsSubMenu.'"; Submenu: myVhostsMenu; Glyph: 5
';
$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenu,$tpl);
//Add submenu
$myPattern = ';WAMPMENULEFTEND';
$myreplace = $myPattern."
";
$myreplacesubmenu = '

[myVhostsMenu]
;WAMPVHOSTMENUSTART
;WAMPVHOSTMENUEND

';
$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenu,$tpl);
$myPattern = ';WAMPVHOSTMENUSTART';
$myreplace = $myPattern."
Type: separator; Caption: \"".$w_virtualHostsSubMenu."\"
";
$myreplacesubmenuVhosts = $myreplacesubmenuVhostsError = '';

//$virtualHost = check_virtualhost();

//is Include conf/extra/httpd-vhosts.conf uncommented?
if($virtualHost['include_vhosts'] === false) {
	$myreplacesubmenuVhosts .= 'Type: item; Caption: "Virtual Host ERROR"; Action: multi; Actions: server_not_included; Glyph: 21
';
   $myreplacesubmenuVhosts .= '[server_not_included]
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 14";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
}
else {
	if($virtualHost['vhosts_exist'] === false) {
		$myreplacesubmenuVhosts .= 'Type: item; Caption: "Virtual Host ERROR"; Action: multi; Actions: server_not_exists; Glyph: 21
';
   	$myreplacesubmenuVhosts .= '[server_not_exists]
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 15 '.base64_encode($virtualHost['vhosts_file']).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}
	else
	{
		$server_name = $server_no_https = array();
		if($virtualHost['nb_Server'] > 0)	{
			$nb_Server = $virtualHost['nb_Server'];
			$nb_Virtual = $virtualHost['nb_Virtual'];
			$nb_Document = $virtualHost['nb_Document'];
			$nb_Directory = $virtualHost['nb_Directory'];
			$nb_End_Directory = $virtualHost['nb_End_Directory'];

			$port_number = true;
			//Check number of <Directory equals to number of </Directory
			if($nb_End_Directory != $nb_Directory) {
				$value = "ServerName_Directory";
				$server_name[$value] = -2;
				$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
			}
			//Check number of DocumentRoot equals to number of ServerName
			if($nb_Document != $nb_Server) {
				$value = "ServerName_Document";
				$server_name[$value] = -7;
				$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
			}
			//Check validity of DocumentRoot
			$documentPathError = '';
			if($virtualHost['document'] === false) {
				foreach($virtualHost['documentPath'] as $value) {
					if($virtualHost['documentPathValid'][$value] === false) {
						$documentPathError = $value;
						$code = -8;
						break;
					}
					elseif($virtualHost['documentPathNotSlashEnded'][$value] === false) {
						$documentPathError = $value;
						$code = -18;
						break;
					}
				}
				$value = "DocumentRoot_error";
				$server_name[$value] = $code;
				$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
			}
			//Check validity of Directory Path
			$directoryPathError = '';
			if($virtualHost['directory'] === false) {
				foreach($virtualHost['directoryPath'] as $value) {
					if($virtualHost['directoryPathValid'][$value] === false) {
						$directoryPathError = $value;
						break;
					}
				}
				$value = "Directory_Path_error";
				$server_name[$value] = -9;
				$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
			}
			//Check Directory Path ended with a slash /
			$directoryPathErrorSlash = '';
			if($virtualHost['directorySlash'] === false) {
				foreach($virtualHost['directoryPath'] as $value) {
					if($virtualHost['directoryPathSlashEnded'][$value] === false) {
						$directoryPathErrorSlash = $value;
						break;
					}
				}
				$value = "Directory_Path_no_slash";
				$server_name[$value] = -17;
				$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
			}

			//Check number of <VirtualHost equals or > to number of ServerName
			if($nb_Server != $nb_Virtual && $wampConf['NotCheckDuplicate'] == 'off') {
				$value = "ServerName_Virtual";
				$server_name[$value] = -3;
				$port_number = false;
				$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
			}

			//Check number of port definition of <VirtualHost *:xx> equals to number of ServerName
			if($virtualHost['nb_Virtual_Port'] != $nb_Virtual) {
				$value = "VirtualHost_Port";
				$server_name[$value] = -4;
				$port_number = false;
				$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
			}
			//Check validity of port number
			if($port_number && $virtualHost['port_number'] === false) {
				$value = "VirtualHost_PortValue";
				$server_name[$value] = -5;
				$port_number = false;
				$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
			}
			//Check if duplicate ServerName
			if($virtualHost['nb_duplicate'] > 0) {
				$DuplicateNames = '';
				$value = "Duplicate_ServerName";
				$server_name[$value] = -10;
				foreach($virtualHost['duplicate'] as $NameValue)
					$DuplicateNames .= "\r\n\t".$NameValue;
				$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
			}
			foreach($virtualHost['ServerName'] as $key => $value) {
				if($virtualHost['ServerNameValid'][$value] === false || $virtualHost['ServerNameQuoted'][$value] === true) {
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 20
';
					$server_name[$value] = -1;
				}
				elseif($virtualHost['DocRootNotwww'][$value] === false) {
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 20
';
					$server_name[$value] = -14;
				}
				elseif($virtualHost['ServerNameDev'][$value] === true) {
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 20
';
					$server_name[$value] = -15;
				}
				elseif($virtualHost['ServerNameIntoHosts'][$value] === false) {
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 20
';
					$server_name[$value] = -16;
				}
				elseif($virtualHost['ServerNameValid'][$value] === true) {
					$UrlPortVH = ($virtualHost['ServerNamePort'][$value] != '80') ? ':'.$virtualHost['ServerNamePort'][$value] : '';
					if(!$virtualHost['port_listen'] && $virtualHost['ServerNamePortListen'][$value] !== true) {
						$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
						$server_name[$value] = -12;
					}
					elseif($virtualHost['port_listen'] && $virtualHost['ServerNamePortApacheVar'][$value] !== true) {
						$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
						$server_name[$value] = -13;
					}
					else {
						$glyph = ($value == 'localhost') ? '27' : '5';
						$value_url = ((strpos($value, ':') !== false) ? strstr($value,':',true) : $value);
						$value_link = $value;
						$vh_action = 'Action: run; FileName: "'.$c_navigator.'"; Parameters: "'.$c_edge.'http://'.$value_url.$UrlPortVH.'/"; Glyph: '.$glyph;
						$server_name[$value] = 1;
						$value_type = $vh_ip = '';
						if($virtualHost['ServerNameIp'][$value] !== false) {
							$vh_ip = $virtualHost['ServerNameIp'][$value];
							$value_link = $vh_ip.' ('.$value.')';
							$vh_action = 'Action: run; FileName: "'.$c_navigator.'"; Parameters: "'.$c_edge.'http://'.$vh_ip.$UrlPortVH.'/"; Glyph: '.$glyph;
							if($virtualHost['ServerNameIpValid'][$value] === false) {
								$vh_action = "Action: multi; Actions: server_{$value}; Glyph: 20";
								$server_name[$value] = -11;
							}
						}
						if($virtualHost['ServerNameIDNA'][$value] === true && !empty($Windows_Charset)) {
							$value_trans = @iconv("UTF-8",$Windows_Charset."//TRANSLIT",$virtualHost['ServerNameUTF8'][$value]);
							if($value_trans !== false ) {
								$value_type .= ' #13 [IDNA-> '.$value_trans.']';
							}
						}
						$server_fcgi = false;						if(isset($c_ApacheDefine['PHPROOT']) && $virtualHost['ServerNameFcgid'][$value] === true){
							$value_type .= ' #13 [FCGI-> PHP '.$virtualHost['ServerNameFcgidPHP'][$value].']';
							$server_fcgi =true;						}
						if($virtualHost['ServerNameFcgid'][$value] === true && $virtualHost['ServerNameFcgidPHPOK'][$value] !== true) {
							$value_type .= ' #13 FCGI -> PHP '.$virtualHost['ServerNameFcgidPHP'][$value].' '.$w_phpNotExists;
						}
						$server_https = false;
						if(in_array($value,$virtualHost['ServerNameHttps'])) {
							$value_type .= ' #13 [HTTPS]';
							$server_https = true;
							$vh_action = str_replace('http://','https://',$vh_action);
						}
						$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value_link.$value_type.'"; '.$vh_action.'
';					if($key != 'localhost') {
							foreach($virtualHost['Server'] as $key_path => $value_path) {
								if($virtualHost['Server'][$key_path]['ServerName'] == $key) break;
							}
							$server_no_https[] = array(
								'name' => $key,
								'docroot' => $virtualHost['Server'][$key_path]['DocumentRoot'],
								'ip' => (empty($vh_ip) ? '*' : $vh_ip),
								'fcgi' => $virtualHost['ServerNameFcgidPHP'][$key],
								'https' => $server_https,
							);
						}
					}//End
				}
				else {
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 20
';
					$server_name[$value] = -6;
				}
			} //End foreach

			$myreplacesubmenuVhosts .= 'Type: separator
Type: item; Caption: "'.$w_add_VirtualHost.'"; Action: run; FileName: "'.$c_navigator.'"; Parameters: "'.$c_edge.'http://localhost'.$UrlPort.'/add_vhost.php"; Glyph: 33
';
			//Submenu to create https VirtualHost
			$myreplacesubmenuHttps = $myreplaceHttps = '';
			if($wampConf['httpsReady'] == 'on' && count($server_no_https) > 0) {
				$myreplaceHttps .= '
Type: submenu; Caption: "'.$w_ConvertHttps.'"; Submenu: virtualhosthttps; Glyph:33
[virtualhosthttps]
Type: separator; Caption:"'.$w_ConvertHttps.'"
';
				foreach($server_no_https as $key => $value) {
					$nameHttps = $server_no_https[$key]['name'];
					$docrootHttps = $server_no_https[$key]['docroot'];
					$ipHttps = $server_no_https[$key]['ip'];
					$fcgiHttps = $server_no_https[$key]['fcgi'];
					if($server_no_https[$key]['https'] === false){
						$myreplaceHttps .= '
Type:item; Caption:"'.$nameHttps.'"; Action: multi; Actions:'.$nameHttps.'https
';
						$myreplacesubmenuHttps .= <<<EOF
[{$nameHttps}https]
Action: run; FileName: "{$c_phpExe}";Parameters: "changeToHttps.php https {$nameHttps} {$docrootHttps} {$ipHttps} {$fcgiHttps}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: apache_stop_start_refresh; Flags:appendsection

EOF;
					}
					else {
						$myreplaceHttps .= '
Type:item; Caption:"'.$nameHttps.'"; Action: multi; Actions:'.$nameHttps.'nohttps; Glyph:13
';
						$myreplacesubmenuHttps .= <<<EOF
[{$nameHttps}nohttps]
Action: run; FileName: "{$c_phpExe}";Parameters: "changeToHttps.php nohttps {$nameHttps} {$docrootHttps} {$ipHttps} {$fcgiHttps}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: apache_stop_start_refresh; Flags:appendsection

EOF;
					}
				}
			}//End https

			foreach($server_name as $name=>$value) {
				if($server_name[$name] != 1) {
					if($server_name[$name] == -1)
						$message = "In the httpd-vhosts.conf file:\r\n\r\n\tThe ServerName '".$name."' has no correct syntax.\r\n";
					elseif($server_name[$name] == -2)
						$message = "In the httpd-vhosts.conf file:\r\n\r\n\tThe number of\r\n\r\n\t\t<Directory ...>\r\n\t\tis not equal to the number of\r\n\r\n\t\t</Directory>\r\n\r\nThey should be identical.";
					elseif($server_name[$name] == -3)
						$message = "In the httpd-vhosts.conf file:\r\n\r\n\tThe number of\r\n\r\n\t\t<VirtualHost ...>\r\n\tis not equal to the number of\r\n\r\n\t\tServerName\r\n\r\nThey should be identical.\r\n\r\n\tCorrect syntax is: <VirtualHost *:80>\r\n";
					elseif($server_name[$name] == -4)
						$message = "In the httpd-vhosts.conf file:\r\n\r\n\tPort number into <VirtualHost *:port>\r\n\tis not defined for all\r\n\r\n\t\t<VirtualHost...>\r\n\r\n\tCorrect syntax is: <VirtualHost *:xx>\r\n\r\n\t\twith xx = port number [80 by default]\r\n";
					elseif($server_name[$name] == -5)
						$message = "In the httpd-vhosts.conf file:\r\n\r\n\tPort number into <VirtualHost *:port>\r\n\thas not correct value\r\n\r\nValue are:".print_r($virtualHost['virtual_port'],true)."\r\n";
					elseif($server_name[$name] == -6)
						$message = "The httpd-vhosts.conf file has not been cleaned.\r\nThere remain VirtualHost examples like: dummy-host.example.com\r\n";
					elseif($server_name[$name] == -7)
						$message = "In the httpd-vhosts.conf file:\r\n\r\n\tThe number of\r\n\r\n\t\tDocumentRoot\r\n\tis not equal to the number of\r\n\r\n\t\tServerName\r\n\r\nThey should be identical.\r\n";
					elseif($server_name[$name] == -8)
						$message = "In the httpd-vhosts.conf file:\r\n\r\nThe DocumentRoot path\r\n\r\n\t".$documentPathError."\r\n\r\ndoes not exits\r\n";
					elseif($server_name[$name] == -9)
						$message = "In the httpd-vhosts.conf file:\r\n\r\nThe Directory path\r\n\r\n\t".$directoryPathError."\r\n\r\ndoes not exits\r\n";
					elseif($server_name[$name] == -10)
						$message = "In the httpd-vhosts.conf file:\r\n\r\nThere is duplicate ServerName\r\n".$DuplicateNames."\r\n";
					elseif($server_name[$name] == -11)
						$message = "In the httpd-vhosts.conf file:\r\n\r\nThe IP used for the VirtualHost ".$name." is not valid local IP\r\n";
					elseif($server_name[$name] == -12)
						$message = "In the httpd-vhost.conf file:\r\n\r\nThe Port used (".$virtualHost['ServerNamePort'][$name].") for the VirtualHost ".$name." is not a Listen port\r\n";
					elseif($server_name[$name] == -13)
						$message = "In the httpd-vhost.conf file:\r\n\r\nThe Port used (".$virtualHost['ServerNamePort'][$name].") for the VirtualHost ".$name." is not from a Define Apache variable\r\n";
					elseif($server_name[$name] == -14)
						$message = "In the httpd-vhosts.conf file:\r\n\r\nThe DocumentRoot path\r\n\r\n\t'".$wwwDir."'\r\n\r\nused with '".$name."' VirtualHost\r\n\r\nis reserved for 'localhost' and should not be used for another VirtualHost\r\n";
					elseif($server_name[$name] == -15)
						$message = "In the httpd-vhosts.conf file:\r\n\r\nTLD '.dev' used with '".$name."' ServerName\r\n\r\nis monopolized by web browsers and should not be used locally.\r\nYou can use'.test' or'.prog' instead.\r\n";
					elseif($server_name[$name] == -16)
						$message = "In the httpd-vhosts.conf file:\r\n\r\n'".$name."' ServerName\r\n\r\nis not defined into '".$c_hostsFile."' file.\r\n";
					elseif($server_name[$name] == -17)
						$message = "In the httpd-vhosts.conf file:\r\n\r\nThe Directory path\r\n\r\n\t".$directoryPathErrorSlash."\r\n\r\nis not ended with a slash '/'\r\n";
					elseif($server_name[$name] == -18)
						$message = "In the httpd-vhosts.conf file:\r\n\r\nThe DocumentRoot path\r\n\r\n\t".$documentPathError."\r\n\r\nmust not end with a slash\r\n";
 					$myreplacesubmenuVhostsError .= '[server_'.$name.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
				}
			}
		}
	}
}

$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenuVhosts.$myreplaceHttps.$myreplacesubmenuHttps.$myreplacesubmenuVhostsError,$tpl);
// END of Virtual Hosts submenu
// ****************************

//**************************************
// Creating Wampmanager settings submenu
$params_for_wampconf = $wampConfParams = array();
foreach($wamp_Param as $value) {
	$endsub = false;
  if(strpos($value, '###') === 0) { // Last item in SubMenu
  	$value = substr($value,3);
  	$endsub = true;
  }
	$wampConfParams[$value] = $value;
	$wampConfParams['endsub'][$value] = $endsub;
  if(strpos($value, '##') === 0) { // Separator + submenu
  	$value = substr($value,2);
  	$params_for_wampconf[$value] = -6;
		$wampConfParams[$value] = $value;
		$wampConfParams['endsub'][$value] = $endsub;
  }
  elseif(strpos($value, '#-') === 0) { // Separator no Caption+ submenu
  	$value = substr($value,2);
  	$params_for_wampconf[$value] = -7;
		$wampConfParams[$value] = $value;
		$wampConfParams['endsub'][$value] = $endsub;
  }
  elseif(strpos($value, '#') === 0) { // Separator
  	$value = substr($value,1);
  	$params_for_wampconf[$value] = -2;
		$wampConfParams[$value] = $value;
		$wampConfParams['endsub'][$value] = $endsub;
  }
  elseif(empty($wampConf[$value])) {//Parameter does not exist in wampmanager.conf
    $params_for_wampconf[$value] = -1;
  }
	elseif(array_key_exists($value, $wampParamsNotOnOff)) {
		if(!empty($wampParamsNotOnOff[$value]['dependance']) && $wampConf[$wampParamsNotOnOff[$value]['dependance']] !== 'on') {
			$params_for_wampconf[$value] = -10;
		}
		else {
  		if($wampParamsNotOnOff[$value]['change'] !== true) {
  	 		$params_for_wampconf[$value] = -5;
  		 	$wampErrorMsg = "\nIf you want to change this value, you can do it directly in the file:\n".$c_installDir."/wampmanager.conf file\n";
  		}
  		else {
  			if($wampParamsNotOnOff[$value]['title'] == 'OnOff') {
    			if($wampConf[$value] == 'on')
      			$params_for_wampconf[$value] = '1';
    			elseif($wampConf[$value] == 'off')
      			$params_for_wampconf[$value] = '0';
    			else
      			$params_for_wampconf[$value] = '-5';
  			}
	  	 	else {
	  	 		$params_for_wampconf[$value] = -3;
	  	 	}
  		}
  	}
  }
	else {
    if($wampConf[$value] == 'on')
      $params_for_wampconf[$value] = '1';
    elseif($wampConf[$value] == 'off')
      $params_for_wampconf[$value] = '0';
    else
      $params_for_wampconf[$value] = '-5';
  }
}
$wampConfActions = $wampConfTextInfo = $wampConfSub = '';
$action_sup = array();
$information_only = false;
$wampConfInto = 'wampConfText';
$$wampConfInto = ";WAMPSETTINGSSTART
Type: Separator; Caption: \"".$w_wampSettings."\"
";
foreach ($params_for_wampconf as $paramname => $paramstatus) {
  if($paramstatus == -5) {
		if(!$information_only) {
			$wampConfTextInfo .= 'Type: separator; Caption: "'.$w_phpparam_info.'"
';
			$information_only = true;
		}
    	$wampConfTextInfo .= 'Type: item; Caption: "'.$paramname.' = '.$wampConf[$paramname].'"; Glyph: 22; Action: multi; Actions: '.$paramname.'
';
	}
  elseif($paramstatus == -2) { //Separator
    $$wampConfInto .= 'Type: Separator; Caption: "'.$w_settings[$paramname].'"
';
	}
	elseif($paramstatus == -3) { // Indicate different from 0 or 1 or On or Off but can be changed
		$action_sup[] = $paramname;
			$$wampConfInto .= 'Type: submenu; Caption: "'.$w_settings[$paramname].' = '.$wampConf[$paramname].'"; Submenu: '.$paramname.'; Glyph: 9
';
	}
	elseif($paramstatus == -4) { // I blue to indicate different from 0 or 1 or On or Off
		if(!$information_only) {
			$wampConfTextInfo .= 'Type: separator; Caption: "'.$w_phpparam_info.'"
';
			$information_only = true;
		}
    $wampConfTextInfo .= 'Type: item; Caption: "'.$w_settings[$paramname].' = '.$wampConf[$paramname].'"; Action: multi; Actions: '.$paramname.'
';
	}
  elseif($paramstatus == -6) { //Separator + submenu
    $$wampConfInto .= 'Type: Separator; Caption: "'.$w_settings[$paramname].'"
Type: submenu; Caption: "'.$w_settings[$paramname].'"; Submenu: '.$paramname.'; Glyph: 9
';
		$wampConfInto = 'wampConfSub';
		$$wampConfInto .= '['.$paramname.']
';
	}
  elseif($paramstatus == -7) { //Separator no Camption + submenu
    $$wampConfInto .= 'Type: Separator
Type: submenu; Caption: "'.$w_settings[$paramname].'"; Submenu: '.$paramname.'; Glyph: 9
';
		$wampConfInto = 'wampConfSub';
		$$wampConfInto .= '['.$paramname.']
';
	}
	elseif($paramstatus == -10) {
		continue; //do nothing - No menu item
	}
	elseif(($paramstatus == 1 || $paramstatus == 0)) {
		$myGlyph = "";
		$SwitchAction = 'on';
		if($paramstatus == 1) {
			$myGlyph = "Glyph: 13; ";
			$SwitchAction = 'off';
		}
    $$wampConfInto .= 'Type: item; Caption: "'.$w_settings[$paramname].'"; '.$myGlyph.'Action: multi; Actions: '.$wampConfParams[$paramname].'
';
		$php_exe_type = (in_array($paramname,$wamp_ParamPhpExe)) ? $c_phpExe : $c_phpCli ;
  	$wampConfActions .= <<< EOF
[{$wampConfParams[$paramname]}]
Action: run; FileName: "{$php_exe_type}";Parameters: "switchWampParam.php {$wampConfParams[$paramname]} {$SwitchAction}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: apache_restart_refresh; Flags:appendsection

EOF;
	}
	if($wampConfParams['endsub'][$paramname]) $wampConfInto = 'wampConfText';
}
//Check for supplemtary actions
$MenuSup = $SubMenuSup = array();
if(count($action_sup) > 0) {
	$i = 0;
	foreach($action_sup as $action) {
		$MenuSup[$i] = $SubMenuSup[$i] = '';
		$MenuSup[$i] .= '['.$action.']
Type: separator; Caption: "'.$wampParamsNotOnOff[$action]['title'].'"
';
		$c_values = $wampParamsNotOnOff[$action]['values'];
		if($wampParamsNotOnOff[$action]['quoted'])
			$quoted = 'quotes';
		else
			$quoted = 'noquotes';
		foreach($c_values as $value) {
			$MenuSup[$i] .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: '.$action.$value.'
';
			if(mb_strtolower($value) == 'choose') {
				$param_value = '%'.$wampParamsNotOnOff[$action]['title'].'%';
				$param_third = ' '.$wampParamsNotOnOff[$action]['title'];
				if($wampParamsNotOnOff[$action]['title'] == 'Integer')
					$param_third .= ' '.$wampParamsNotOnOff[$action]['min'].'^'.$wampParamsNotOnOff[$action]['max'].'^'.$wampParamsNotOnOff[$action]['default'];
				$c_phpRun = $c_phpExe;
			}
			else {
				$param_value = $value;
				$param_third = '';
				$c_phpRun = $c_phpCli;
			}
			$SubMenuSup[$i] .= <<< EOF
[{$action}{$value}]
Action: run; FileName: "{$c_phpRun}";Parameters: "changeWampParam.php {$quoted} {$action} {$param_value}{$param_third}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: apache_restart_refresh; Flags:appendsection

EOF;
		}
	$i++;
	}
}

$wampConfText .= $wampConfTextInfo;
if(count($MenuSup) > 0) {
	for($i = 0 ; $i < count($MenuSup); $i++)
		$wampConfText .= $MenuSup[$i].$SubMenuSup[$i];
}

$tpl = str_replace(';WAMPSETTINGSSTART',$wampConfText.$wampConfSub.$wampConfActions,$tpl);
unset($wampConfText,$wampConfSub,$wampConfActions);
// END of Wampmanager settings submenu
// **********************************

// ***************************************
// Creating tool change Wampserver browser
if(file_exists('ListBrowsers.txt')) {
	$file = 'ListBrowsers.txt';
	$contents = @file_get_contents($file);
	if($contents !== false) {
		write_file('ListBrowsers.php',$contents);
	}
}
if(file_exists('ListBrowsers.php')) {
	$BrowserChangeMenu = $BrowserChangeSub = '
';
	include 'ListBrowsers.php';
		if(isset($browser) && is_array($browser) && isset($browser['name']) && isset($browser['path'])) {
		foreach($browser['name'] as $key => $value) {
			if(empty($browser['path'][$key])) continue;
			$browserName = $value;
			$browserPath = $browser['path'][$key];
			$glyph = $browserPath == $c_navigator ? '; Glyph: 13' : '';
			$BrowserChangeMenu .= 'Type: Item; Caption: "'.$value.'"; Action: multi; Actions: browser_'.$browserName.$glyph.'
';
			$BrowserChangeSub .= <<< EOF
[browser_{$browserName}]
Action: run; FileName: "{$c_phpExe}";Parameters: "changeWampParam.php quotes navigator ""{$browserPath}""";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: refresh_readconfig; Flags:appendsection

EOF;
		}
		$tpl = str_replace('[WampserverBrowser]','[WampserverBrowser]'.$BrowserChangeMenu.$BrowserChangeSub,$tpl);
		unset($BrowserChangeMenu,$BrowserChangeSub);
	}
}
// END of tool change Wampserver browser
// *************************************

// **************************************
// Creating tool delete old versions menu
$delOldVer = ";WAMPDELETEOLDVERSIONSSTART
Type: separator; Caption: \"".$w_deleteVer."\"
";
$delOldVerMenu = $delOldVerSub = '';
//All versions but USED or CLI OR FCGI
$Versions = ListAllVersions();
$VersionsNotUsed = array_filter_recursive($Versions,function($value){return (strpos($value,'CLI') === false && strpos($value,'USED') === false && strpos($value,'FCGI') === false);});
$counts = 0;
foreach(array_keys($VersionsNotUsed) as $appli) {
	if(count($VersionsNotUsed[$appli]) > 0) {
		$counts++;
		$delOldVerMenu .= "Type: separator; Caption: \" ".mb_strtoupper($appli)." \"
";
		foreach ($VersionsNotUsed[$appli] as $appliVersion) {
  			$delOldVerMenu .= 'Type: item; Caption: "'.$w_delete.' '.$appli.' '.$appliVersion.'"; Glyph: 32; Action: multi; Actions: del_'.$appli.$appliVersion.'
';
				$delOldVerSub .= <<< EOF
[del_{$appli}{$appliVersion}]
Action: run; FileName: "{$c_phpExe}";Parameters: "deleteVersion.php {$appli} {$appliVersion}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: refresh_readconfig; Flags:appendsection

EOF;
		}
	}

}
if($counts > 0) $tpl = str_replace(';WAMPDELETEOLDVERSIONSSTART',$delOldVer.$delOldVerMenu.$delOldVerSub,$tpl);
unset($delOldVer,$delOldVerMenu,$delOldVerSub);
// END of tool delete old versions menu
//*************************************

//*********************************************
// Creating tool delete Listen Port Apache menu
if($ListenPortsExists) {
	$ForbidenToDel = array('80', '8080',$c_DefaultPort, $c_UsedPort);
	$delListenPort = ";WAMPDELETELISTENPORTSTART
Type: separator; Caption: \"".$w_deleteListenPort."\"
";
	$delListenPortMenu = $delListenPortSub = '';
	foreach($c_listenPort as $ListenPort) {
		if(!in_array($ListenPort,$ForbidenToDel)) {
 			$delListenPortMenu .= 'Type: item; Caption: "'.$w_delete.' Listen port Apache '.$ListenPort.'"; Glyph: 32; Action: multi; Actions: del_apache_port'.$ListenPort.'
';
			$delListenPortSub .= <<< EOF
[del_apache_port{$ListenPort}]
Action: run; FileName: "{$c_phpExe}";Parameters: "ListenPortApache.php delete {$ListenPort}";WorkingDir: "{$c_installDir}/scripts"; Flags: waituntilterminated
Action: multi; Actions: apache_restart_refresh; Flags:appendsection

EOF;
		}
	}
$tpl = str_replace(';WAMPDELETELISTENPORTSTART',$delListenPort.$delListenPortMenu.$delListenPortSub,$tpl);
unset($delListenPort,$delListenPortMenu,$delListenPortSub);
}
// END of tool delete Listen Port Apache menu
//*******************************************

//********************************************
// Create definitions of BigMenu (Column menus)
// From $AesBigMenu in config.inc.php
$BigKeys = "[BigMenu]\r\n";
foreach($AesBigMenu as $key => $value) {
	$BigKeys .= 'BigKey'.$key.'=';
	if(strpos($value[0],'$') === 0) {
		$temp = substr($value[0],1);
		$BigKeys .= $$temp.',';
	}
	else {
		$BigKeys .= $value[0].',';
	}
	if(strpos($value[1],'$') === 0) {
		$temp = substr($value[1],1);
		$BigKeys .= $$temp.',';
	}
	else {
		$BigKeys .= $value[1].',';
	}
	$BigKeys .= $value[2]."\r\n";
}
$BigKeys .="\r\n";
$search = ';WAMPBIGMENUSTART
';
$tpl = str_replace($search,$search.$BigKeys,$tpl);
unset($BigKeys);
// END of BigMenu
//***************

//******************************************************
// Create wampserver configuration report file if needed
if($doReport) {
	foreach($wampReport as $value) $wampReportTxt .= $value;
	$wampReportTxt .= "\n--------- wampmanager.ini (Last 4 lines) --------\n";
	$wampReportTxt .= implode(PHP_EOL, array_slice(file($wampserverIniFile), -4));
	$wampReportTxt .= @file_get_contents($c_installDir."/wampConfReportTemp.txt");
	@unlink($c_installDir."/wampConfReportTemp.txt");
	$wampReportTxt .= "\n--------------------------------------------------\n";
	//Error files to add (last 20 lines)
	$wampErrorReportTxt = '';
	$error_files = array(
		'Apache error log' => 'apache_error.log',
		'Apache access log' => 'access.log',
		'PHP error log' => 'php_error.log',
	);
	if($wampConf['SupportMySQL'] == 'on')
		$error_files += array('MySQL error log' => 'mysql.log');
	if($wampConf['SupportMariaDB'] == 'on')
		$error_files += array('MariaDB error log' => 'mariadb.log');
	foreach($error_files as $key => $value) {
		if(file_exists($c_installDir."/".$logDir."/".$value)){
			$wampErrorReportTxt .= "\n-------- ".$key." (Last 30 lines) --------\n";
			$wampErrorReportTxt .= implode(PHP_EOL, array_slice(file($c_installDir."/".$logDir."/".$value), -30));
			$wampErrorReportTxt .= "\n--------------------------------------------------";
		}
	}
	$wampReportTxt .= $wampErrorReportTxt;
	write_file($c_installDir."/wampConfReport.txt",color('clean',clean_file_contents($wampReportTxt,array(1,0)),true));
}

//Check if wampserver report configuration file exists
if(file_exists($c_installDir."/wampConfReport.txt")) {
	//Get timestamp of the report file
	$fp = fopen($c_installDir."/wampConfReport.txt","rb");
	$timestamp = (int)fgets($fp);
	fclose($fp);
	//$timestamp -= (86400*10);
	if((time() - $timestamp)/86400 > 10) {
		//Report file more than ten days old.
		unlink($c_installDir."/wampConfReport.txt");
	}
	else {
		$confFileExists = <<< EOF
;WAMPREPORTCONFFILE
Type: item; Caption: "{$w_wampReport}"; Glyph: 33; Action: run; FileName: "{$c_editor}"; parameters: "{$c_installDir}/wampConfReport.txt"
EOF;
	$tpl = str_replace(';WAMPREPORTCONFFILE',$confFileExists,$tpl);
	}
}

//************************
// Clean tmp dir if needed
if($wampConf['AutoCleanTmp'] == 'on') {
	$fileTmp = glob($c_installDir.'/tmp/*');
	if(count($fileTmp) > $wampConf['AutoCleanTmpMax']) {
		foreach($fileTmp as $file){
   		if(is_file($file)) {
   			if(unlink($file) === false) {
   				error_log("Unable to delete file: ".$file);
   			}
   		}
		}
	}
	unset($fileTmp);
}


//*****************************************************
// Add warnings at the end of Left-Click menu if needed
if($WarningsAtEnd) {
	$WarningTextAll = '
Type: separator; Caption: ">>>>>    '.$w_warning.'    <<<<<"
';
	$tpl = str_replace(';WAMPMENULEFTEND',$WarningMenu.$WarningTextAll.$WarningText,$tpl);
}

//***************************************************************
//The creation of wampmanager.ini file is complete, save the file.
write_file($wampserverIniFile,$tpl);
unset($tpl);
// END of load Template file as require
//*************************************

//Write last_versions_here.txt file
//to check updates from checkUpdates.php script
$writeNewFile = true;
$NewFileContents = '<?php'."\n\n".'$wamp_versions_here = '.var_export($wamp_versions_here, true).';'."\n\n".'?>';
if(file_exists('last_versions_here.txt')) {
	$FileContents = file_get_contents('last_versions_here.txt');
	if($NewFileContents == $FileContents)
		$writeNewFile = false;
}
if($writeNewFile) {
	write_file('last_versions_here.txt',$NewFileContents);
}

//Check alias and paths in httpd-autoindex.conf
check_autoindex();

if(!file_exists($c_installDir.'/logs/php_error.log'))
	error_log("No error - Only to create the file");

?>