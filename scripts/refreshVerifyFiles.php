<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}
clearstatcache(true);

//*************************************************************
//****** Verify some files before generate wampmanager.ini file
//Check all lines are DOS (CR/LF) ending - Modify if not - Don't return contents
// Apache httpd-vhosts.conf file
file_get_contents_dos($c_apacheVhostConfFile, false);
// PhpForApache.ini and php.ini file of used PHP
file_get_contents_dos($c_phpConfFile, false);
file_get_contents_dos($c_phpConfFileIni, false);
// php.ini file of CLI php
file_get_contents_dos($c_phpCliConfFile, false);

// Verify DO NOT EDIT in wamp(64)/bin/php/phpx.y.z/php.ini file's - Insert if not
$phpVersionList = listDir($c_phpVersionDir,'checkPhpConf','php',true);
//For wamp(64)/bin/php/phpx.y.z/php.ini file
$search = "EDIT THIS FILE for PHP CLI or FCGI used with fcgid_module";
$replace = <<< NOTEDITEOF
[PHP]
; **************************************************************
; ***PHPVERSIONZZ - php.ini file used for FCGI or CLI **********
; *** DO NOT EDIT THIS FILE for PHP used as an Apache module ***
; *** EDIT Wampmanager Icon -> PHP-> php.ini [apache module] ***
; *** that is wamp(64)/bin/apache/apache2.4.x/bin/php.ini    ***
; **************************************************************
; * EDIT THIS FILE for PHP CLI or FCGI used with fcgid_module  *
;***************************************************************

;;;;;;;;;;;;;;;;;;;
; About php.ini   ;
NOTEDITEOF;
$typeIni = $wampConf['phpConfFile'];
foreach($phpVersionList as $value) {
	$inifile = $c_phpVersionDir."/php".$value."/".$typeIni;
	$iniFileContents = file_get_contents($inifile);
	if(strpos($iniFileContents,$search) === false) {
		//Extract [PHP]... ; About php.ini   ;
		$mask = "~(\[PHP\].*^; About php\.ini   ;)\r?$~ms";
		if(preg_match($mask,$iniFileContents,$matches) > 0) {
			$iniFileContents = str_replace(array($matches[1],'PHPVERSIONZZ'), array($replace,str_pad(' PHP '.$value,12)), $iniFileContents, $count);
			if($count > 0) write_file($inifile,$iniFileContents);
		}
	}
}
//For wamp(64)/bin/php/phpx.y.z/phpForApache.ini file
$search = "EDIT THIS FILE for PHP used as an Apache module";
$replace = <<< NOTEDITEOF
[PHP]
; **************************************************************
; *PHPVERSIONZZ - phpForApache.ini file used as Apache module **
; ** DO NOT EDIT THIS FILE for PHP CLI or FCGI (fcgid_module) **
; ** EDIT Wampmanager Icon -> PHP-> php.ini [CLI -- FCGI]     **
; ********** that is wamp(64)/bin/php/phpx.y.z/php.ini *********
; **************************************************************
; ****** EDIT THIS FILE for PHP used as an Apache module *******
; **************************************************************

;;;;;;;;;;;;;;;;;;;
; About php.ini   ;
NOTEDITEOF;
$typeIni = 'phpForApache.ini';
foreach($phpVersionList as $value) {
	$inifile = $c_phpVersionDir."/php".$value."/".$typeIni;
	$iniFileContents = file_get_contents($inifile);
	if(strpos($iniFileContents,$search) === false) {
		//Extract [PHP]... ; About php.ini   ;
		$mask = "~(\[PHP\].*^; About php\.ini   ;)\r?$~ms";
		if(preg_match($mask,$iniFileContents,$matches) > 0) {
			$iniFileContents = str_replace(array($matches[1],'PHPVERSIONZZ'), array($replace,str_pad(' PHP '.$value,12)), $iniFileContents, $count);
			if($count > 0) write_file($inifile,$iniFileContents);
		}
	}
}
unset($iniFileContents);

//Check if the file wamp/bin/php/DO_NOT_DELETE_x.y.z.txt match CLI php version used
if(!file_exists($c_phpVersionDir."/DO_NOT_DELETE_".$c_phpCliVersion.".txt")) {
	$do_not_delete_txt = "This PHP version ".$c_phpCliVersion." is used by WampServer in CLI mode.\r\nIf you delete it, WampServer won't work anymore.";
	if($handle = opendir($c_phpVersionDir))	{
		while (false !== ($file = readdir($handle)))	{
			if($file != "." && $file != ".." && !is_dir($c_phpVersionDir.'/'.$file)) {
				$list[] = $file;
			}
		}
		closedir($handle);
	}
	if(!empty($list)) {
		foreach($list as $value) {
			if(strpos($value,"DO_NOT_DELETE") !== false)
				unlink($c_phpVersionDir."/".$value);
		}
	}
	write_file($c_phpVersionDir."/DO_NOT_DELETE_".$c_phpCliVersion.".txt",$do_not_delete_txt);
}

//Verify some Apache variables into httpd.conf - Add if not
$c_ApacheDefineVerif = array();
$ApacheDefineError = false;
//--------------------------------
$tryfind = 'Define VERSION_APACHE';
$search = 'Define APACHE24 Apache2.4
';
	$replace = <<< EOF
# Apache variable names used by Apache conf files:
# The names and contents of variables:
# APACHE24, VERSION_APACHE, INSTALL_DIR, APACHE_DIR, SRVROOT
# should never be changed.
Define APACHE24 Apache2.4
Define VERSION_APACHE {$c_apacheVersion}
Define INSTALL_DIR {$c_installDir}
Define APACHE_DIR \${INSTALL_DIR}/bin/apache/apache\${VERSION_APACHE}
Define SRVROOT \${INSTALL_DIR}/bin/apache/apache\${VERSION_APACHE}

EOF;
$httpdFileContents = file_get_contents_dos($c_apacheConfFile);
$count = $counts = 0;
if(strpos($httpdFileContents,$tryfind) === false) {
	$httpdFileContents = str_replace($search, $replace, $httpdFileContents, $count);
	$counts += $count;
}
else { // Variables exists - Verify contents
	$search = array(
		'Define APACHE24',
		'Define VERSION_APACHE',
		'Define INSTALL_DIR',
		'Define APACHE_DIR',
		'Define SRVROOT',
	);
	$verify = array(
		'Apache2.4',
		$c_apacheVersion,
		$c_installDir,
		'${INSTALL_DIR}/bin/apache/apache${VERSION_APACHE}',
		'${INSTALL_DIR}/bin/apache/apache${VERSION_APACHE}',
	);
	$LastLineFound = '';
	for($i = 0 ; $i < count($search) ; $i++) {
		$searchpreg = '~^('.$search[$i].'[ \t]*)(.*)\r$~m';
		$res_preg = preg_match($searchpreg,$httpdFileContents,$matches);
		if($res_preg === false || $res_preg === 0) {
			if(!empty($LastLineFound)){
				$httpdFileContents = str_replace($LastLineFound, $LastLineFound."\r\n".$search[$i].' '.$verify[$i],$httpdFileContents,$count);
				$counts += $count;
			}
		}
		else {
			$LastLineFound = $matches[0];
			if($matches[2] != $verify[$i]) {
				$httpdFileContents = preg_replace($searchpreg,'${1}'.$verify[$i],$httpdFileContents,1,$count);
				$counts += $count;
			}
		}
	}
}
//Modify ServerRoot and move it after Define's ServerRoot "j:/wamp/bin/apache/apache2.4.xx"
if(preg_match('~^ServerRoot[ \t]*"'.$c_installDir.'.*$~m',$httpdFileContents,$matches) > 0) {
	$search = array(
		$matches[0],
		'Define SRVROOT ${INSTALL_DIR}/bin/apache/apache${VERSION_APACHE}',
	);
	$replace = array(
		'',
		'Define SRVROOT ${INSTALL_DIR}/bin/apache/apache${VERSION_APACHE}

ServerRoot "${SRVROOT}"
',
		);
	$httpdFileContents = str_replace($search,$replace,$httpdFileContents,$count);
	$counts += $count;
}

//Replace all install paths like "c:/wamp(64) by "${INSTALL_DIR}
$httpdFileContents = str_replace('"'.$c_installDir,'"${INSTALL_DIR}',$httpdFileContents, $count);
$counts += $count;

//Check ThreadStackSize
if(preg_match('~^ThreadStackSize[ \t]+[0-9]+.*\r?$~mi',$httpdFileContents,$matches) === 0) {
	$search = "AcceptFilter https none
";
	$replace = <<< EOF

# The ThreadStackSize directive sets the size of the stack (for autodata)
# of threads which handle client connections and call modules to help process
# those connections. In most cases the operating system default for stack size
# is reasonable, but there are some conditions where it may need to be adjusted.
# Apache httpd may crash when using some third-party modules which use a
# relatively large amount of autodata storage or automatically restart with
# message like: child process 12345 exited with status 3221225725 -- Restarting.
# This type of crash is resolved by setting ThreadStackSize to a value higher
# than the operating system default.
ThreadStackSize 8388608

EOF;
	$httpdFileContents = str_replace($search,$search.$replace,$httpdFileContents,$count);
	$counts += $count;
}

//Check LoadModule fcgid_module modules/mod_fcgid.so
$load_fcgid_module = true;
if(strpos($httpdFileContents,'LoadModule fcgid_module modules/mod_fcgid.so') === false) {
	$replace = <<< 'EOF'
#LoadModule fcgid_module modules/mod_fcgid.so
<IfModule fcgid_module>
  FcgidMaxProcessesPerClass 300
  FcgidConnectTimeout 10
  FcgidProcessLifeTime 1800
  FcgidMaxRequestsPerProcess 0
  FcgidMinProcessesPerClass 0
  FcgidFixPathinfo 0
  FcgidZombieScanInterval 20
  FcgidMaxRequestLen 536870912
  FcgidBusyTimeout 120
  FcgidIOTimeout 120
  FcgidTimeScore 3
  FcgidPassHeader Authorization
  Define PHPROOT ${INSTALL_DIR}/bin/php/php
</IfModule>


EOF;
	$search = '<IfModule unixd_module>';
	$httpdFileContents = str_replace($search,$replace.$search,$httpdFileContents,$count);
	$load_fcgid_module = false;
	$counts += $count;
}

//Check if fcgid_module exists
$mod_fcgid_exists = true;
$mod_fcgid_file = $c_wampMode == '64bit' ? 'mod_fcgid64.so' : 'mod_fcgid32.so';
if(!file_exists($c_apacheModulesDir.'/mod_fcgid.so')) {
	$mod_fcgid_exists = false;
	//Check if wamp(64)/bin/apache/modules_sup/mod_fcgid.so exists
	if(file_exists($c_apacheVersionDir.'/modules_sup/'.$mod_fcgid_file)) {
		$copy_OK = copy($c_apacheVersionDir.'/modules_sup/'.$mod_fcgid_file,$c_apacheModulesDir.'/mod_fcgid.so');
	}
}
if((!$mod_fcgid_exists && $copy_OK) || !$load_fcgid_module) {
	$httpdFileContents = str_replace('#LoadModule fcgid_module modules/mod_fcgid.so','LoadModule fcgid_module modules/mod_fcgid.so',$httpdFileContents,$count);
	$counts += $count;
}

//Verify PHPIniDir "${APACHE_DIR}/bin into httpd.conf
if(strpos($httpdFileContents,'PHPIniDir "${APACHE_DIR}/bin"') === false) {
	$insert = 'PHPIniDir "${APACHE_DIR}/bin"
';
	$replace = 'LoadModule php';
	$httpdFileContents = str_replace($replace,$insert.$replace,$httpdFileContents,$count);
	$counts += $count;
}

if($counts > 0) {
	if(WAMPTRACE_PROCESS) error_log("write ".$c_apacheConfFile." in ".__FILE__." line ". __LINE__."\n",3,WAMPTRACE_FILE);
 	write_file($c_apacheConfFile,$httpdFileContents);
}

//Retrieve Apache variables from file wamp(64)\bin\apache\apache2.4.xx\wampdefineapache.conf
$w_wampbase = base64_decode($c_wampserverBase);
$c_ApacheDefine = retrieve_apache_define($c_apacheDefineConf);
//Retrieve Apache variables from Apache itself (Define)
$c_ApacheDefineVerif = retrieve_apache_define($c_apacheDefineConf,true);

if($c_ApacheDefineVerif != $c_ApacheDefine) {
	//Variables from wampdefineapache.conf are different of Apache variables
	//recreate wampdefineapache.conf file
	if(WAMPTRACE_PROCESS) error_log("write ".$c_apacheDefineConf." in ".__FILE__." line ". __LINE__."\n",3,WAMPTRACE_FILE);
	$defineVar = "; Variables defined by Apache - To be used by some PHP scripts.\n\n";
	if(count($c_ApacheDefineVerif) > 0) {
		foreach($c_ApacheDefineVerif as $key => $value)
			$defineVar .= $key.' = "'.$value.'"'."\n";
	}
	else {
		$ApacheDefineError = true;
		$errorTxt = "; Unable to find Apache variables.\n\n";
		error_log($errorTxt);
		$defineVar .= "; ".$errorTxt;
		if(WAMPTRACE_PROCESS) error_log("script ".__FILE__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
	}
	write_file($c_apacheDefineConf,$defineVar);
	$c_ApacheDefine = retrieve_apache_define($c_apacheDefineConf);
}
// Verification of restart_wampserver.bat, quit_wampserver.bat, uninstall_services.bat
$file =array();
$file[0]['file'] = $c_installDir.'/quit_wampserver.bat';
$file[0]['content'] = <<< EOF
echo off
net stop {$c_apacheService}
net stop {$c_mysqlService}
net stop {$c_mariadbService}
wampmanager.exe -quit -id={$c_wampserverID}

EOF;
$file[1]['file'] = $c_installDir.'/restart_wampserver.bat';
$file[1]['content'] = <<< EOF
@echo off
ping -n 1 -w 500 127.255.255.255 > nul
wampmanager.exe -quit -id={$c_wampserverID}
ping -n 1 -w 4000 127.255.255.255 > nul
wampmanager.exe
exit

EOF;
$file[2]['file'] = $c_installDir.'/uninstall_services.bat';
$file[2]['content'] =  <<< EOF
net stop {$c_apacheService}
sc delete {$c_apacheService}
net stop {$c_mysqlService}
sc delete {$c_mysqlService}
net stop {$c_mariadbService}
sc delete {$c_mariadbService}
wampmanager.exe -quit -id={$c_wampserverID}

EOF;

foreach($file as $key => $value) {
	$writeFile = false;
	if(!file_exists($file[$key]['file'])) $writeFile = true;
	else {
		$Content = file_get_contents($file[$key]['file']) or die ($file." file not found");
		if($Content <> $file[$key]['content']) $writeFile = true;
	}
	if($writeFile) write_file($file[$key]['file'],$file[$key]['content']);
}

//***************** End of verify files ***********************
//*************************************************************

?>