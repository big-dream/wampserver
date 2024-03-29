<?php
//3.3.0 Fix ServerName not in hosts file error in check_virtualhost function

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

// Write string ($string) into file ($file)
// If $clipboard == true copy contents into the clipoard
// WARNING In case of clipborad copy, file will be deleted unless $delete = false
function write_file($file, $string, $clipboard = false, $delete = true, $mode = 'wb') {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__.' file='.$file."\n",3,WAMPTRACE_FILE);
	$writeFileOK = true;
	if(is_writable($file) || !file_exists($file)) {
		$nbsize = strlen($string);
		$fp = fopen($file,$mode);
		if($fp !== false) {
			$nbwrite = fwrite($fp,$string);
			fclose($fp);
			if($nbwrite === false) {
				$errorTxt = "**** ERROR while writting file ".$file." ****";
				error_log($errorTxt);
				if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
				$writeFileOK = false;
			}
			else {
				if($nbwrite <> $nbsize) {
					$errorTxt = "**** ERROR ".$nbwrite." bytes written in file ".$file." should have been ".$nbsize." ****";
					error_log($errorTxt);
					if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
					$writeFileOK = false;
				}
				else {
					if(WAMPTRACE_PROCESS) error_log("File ".$file." -+- HAS BEEN WRITTEN ".(($mode == 'ab') ? ' (contents added) ' : '')."-+-\n",3,WAMPTRACE_FILE);
				}
			}
		}
		else {
			$errorTxt = "**** ERROR while open file ".$file.' ****';
			error_log($errorTxt);
			if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
			$writeFileOK = false;
		}
	}
	else {
		$errorTxt = "***** ERROR the file ".$file." is not writable *****";
		error_log($errorTxt);
		if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
		$writeFileOK = false;
	}
	if($clipboard) {
		$command = 'CMD /D /C type '.$file.' | clip';
		`$command`;
		if($delete) {
			$command = 'CMD /D /C del '.$file;
			`$command`;
		}
	}
	return $writeFileOK;
}

//Function to modify an ini file like wampmanager.conf
function wampIniSet($iniFile, $params) {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);
	$iniFileContents = @file_get_contents($iniFile);
	$count = false;
	foreach ($params as $param => $value) {
		if(preg_match('|^'.$param.'[ \t]*=[ \t]*"?([^"]+)"?\r?$|m',$iniFileContents,$matches) > 0) {
			if($matches[1] <> $value) {
				$iniFileContents = preg_replace('|^'.$param.'[ \t]*=.*|m',$param.' = '.'"'.$value.'"',$iniFileContents,-1,$countR);
				if($countR > 0) $count = true;
			}
		}
		else {
			$iniFileContents = preg_replace('|^'.$param.'[ \t]*=.*|m',$param.' = '.'"'.$value.'"',$iniFileContents,-1,$countR);
			if($countR > 0) $count = true;
		}
	}
	if($count) {
		write_file($iniFile,$iniFileContents);
	}
}

function listDir($dir,$toCheck = '',$racine='',$withoutracine = false) {
	$list = array();
	if(is_dir($dir)) {
		if($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if($file != "." && $file != ".." && is_dir($dir.'/'.$file)) {
					if(!empty($toCheck)) {
						if(call_user_func($toCheck,$dir,$file,$racine))
							$list[] = $file;
					}
				}
			}
			closedir($handle);
		}
	}
	else {
		error_log("*** WARNING is_dir(".$dir.") is not a directory");
	}
	if($withoutracine) {
		array_walk($list,function(&$value, $key)use($racine){$value = str_replace($racine,'',$value);});
		natcasesort($list);
	}
	return $list;
}

//Recursive function to completely delete a folder
function rrmdir($dir) {
	if(is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if($object != "." && $object != "..") {
				if(filetype($dir."/".$object) == "dir")
					rrmdir($dir."/".$object);
				else unlink($dir."/".$object);
			}
		}
		reset($objects);
		return rmdir($dir);
	}
}

function checkPhpConf($baseDir,$version,$racine) {
	global $wampBinConfFiles, $phpConfFileForApache;
  if(strpos($version,$racine) === 0)
		return (file_exists($baseDir.'/'.$version.'/'.$wampBinConfFiles) && file_exists($baseDir.'/'.$version.'/'.$phpConfFileForApache));
	else
		return false;
}

function checkApacheConf($baseDir,$version,$racine) {
	global $wampBinConfFiles;
  if(strpos($version,$racine) === 0)
		return file_exists($baseDir.'/'.$version.'/'.$wampBinConfFiles);
	else
		return false;
}

function checkMysqlConf($baseDir,$version,$racine) {
	global $wampBinConfFiles;
  if(strpos($version,$racine) === 0)
		return file_exists($baseDir.'/'.$version.'/'.$wampBinConfFiles);
	else
		return false;
}

function checkMariaDBConf($baseDir,$version,$racine) {
  global $wampBinConfFiles;
  if(strpos($version,$racine) === 0)
		return file_exists($baseDir.'/'.$version.'/'.$wampBinConfFiles);
	else
		return false;
}

function linkPhpDllToApacheBin($php_version) {
	global $phpDllToCopy, $php820_DllToCopy, $phpN820_DllToCopy, $c_phpVersionDir, $c_apacheVersionDir, $wampConf, $phpConfFileForApache;
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__." - php_version=".$php_version."\n",3,WAMPTRACE_FILE);
	$errorTxt = '';
	//Create symbolic link to dll's files
	clearstatcache();
	//Check if new PHP version is >= 8.2.0
	if(version_compare($php_version, '8.2.0', '>=')) {
		$phpDllToCopy = array_unique(array_merge($phpDllToCopy,$php820_DllToCopy));
	}
	else {
		$phpDllToCopy = array_unique(array_merge($phpDllToCopy,$phpN820_DllToCopy));
	}
	$phpDllAdded = array_merge($php820_DllToCopy,$phpN820_DllToCopy);
	foreach($phpDllAdded as $dll) {
		$link = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/'.$wampConf['apacheExeDir'].'/'.$dll;
		if(is_link($link)) unlink($link);
	}
	unset($dll);
	foreach($phpDllToCopy as $dll)	{
		$target = $c_phpVersionDir.'/php'.$php_version.'/'.$dll;
		$link = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/'.$wampConf['apacheExeDir'].'/'.$dll;
		//symlink deleted if exists
		if(is_link($link)) unlink($link);
		//Symlink created if file exists in phpx.y.z directory and is not a file in Apache bin directory
		if(is_file($target) && !is_file($link)) {
			if(symlink($target, $link) === false) {
				$errorTxt .= "Error while creating symlink '".$link."' to '".$target."' using php symlink function\n";
			}
		}
	}
	//Create apache/apachex.y.z/bin/php.ini link to phpForApache.ini file of active version of PHP
	$target = $c_phpVersionDir."/php".$php_version."/".$phpConfFileForApache;
	$link = $c_apacheVersionDir."/apache".$wampConf['apacheVersion']."/".$wampConf['apacheExeDir']."/php.ini";
	//php.ini deleted if exists
	if(is_link($link)) {
		unlink($link);
	}
	if(symlink($target, $link) === false) {
		$errorTxt .= "Error while creating symlink '".$link."' to '".$target."' using php symlink function\n";
	}
	if(empty($errorTxt)) {
		return true;
	}
	else {
		error_log($errorTxt);
		if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
		return $errorTxt;
	}
}

function CheckSymlink($php_version) {
	global $phpDllToCopy, $php820_DllToCopy, $phpN820_DllToCopy, $c_phpVersionDir, $c_apacheVersionDir, $wampConf, $phpConfFileForApache;
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);
	$errorTxt = '';
	//Check if necessary symlinks exists
	clearstatcache();
	//Check if PHP version is >= 8.2.0
	if(version_compare($php_version, '8.2.0', '>=')) {
		$phpDllToCopy = array_unique(array_merge($phpDllToCopy,$php820_DllToCopy));
	}
	else {
		$phpDllToCopy = array_unique(array_merge($phpDllToCopy,$phpN820_DllToCopy));
	}
	foreach ($phpDllToCopy as $dll)	{
		$target = $c_phpVersionDir.'/php'.$php_version.'/'.$dll;
		$link = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/'.$wampConf['apacheExeDir'].'/'.$dll;
		//Check Symlink if file exists in phpx.y.z directory
		if(is_file($target  && !is_file($link))) {
			if(is_link($link)) {
				$real_link = str_replace("\\", "/",readlink($link));
				if(mb_strtolower($real_link) != mb_strtolower($target)) {
					$errorTxt .= "Symbolic link ".$link."\n      is: ".$real_link."\nshould be ".$target."\n\n";
				}
			}
			elseif(is_file($link)) {
				$errorTxt .= "File ".$link." exists.\nShould be a symbolic link\n";
			}
			else {
				$errorTxt .= "Symbolic link ".$link." does not exist\n";
			}
		}
	}

	//Verify apache/apachex.y.z/bin/php.ini link to phpForApache.ini file of active version of PHP
	$target = $c_phpVersionDir."/php".$php_version."/".$phpConfFileForApache;
	$link = $c_apacheVersionDir."/apache".$wampConf['apacheVersion']."/".$wampConf['apacheExeDir']."/php.ini";
	if(is_link($link)) {
		$real_link = str_replace("\\", "/",readlink($link));
		if(mb_strtolower($real_link) != mb_strtolower($target)) {
			$errorTxt .= "Symbolic link: ".$link."\nTarget is       : ".$real_link."\nTarget should be: ".$target."\n";
		}
	}
	elseif(is_file($link)) {
		$errorTxt .= "File ".$link." exists.\nShould be a symbolic link\n";
	}
	else {
		$errorTxt .= "Symbolic link or file ".$link." does not exist\n";
	}

	if(empty($errorTxt)) {
		return true;
	}
	else {
		error_log($errorTxt);
		if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
		return $errorTxt;
	}
}

function switchPhpVersion($newPhpVersion) {
	global $wampConf, $c_installDir, $configurationFile, $c_phpVersionDir, $wampBinConfFiles, $c_apacheConfFile, $phpDllToCopy, $php820_DllToCopy, $phpN820_DllToCopy;
	//require 'config.inc.php';

	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__." ".$newPhpVersion."\n",3,WAMPTRACE_FILE);

	//loading the configuration file of the new version
	require $c_phpVersionDir.'/php'.$newPhpVersion.'/'.$wampBinConfFiles;

	//the httpd.conf texts depending on the version of apache is determined
	$apacheVersion = $wampConf['apacheVersion'];
	while (!isset($phpConf['apache'][$apacheVersion]) && $apacheVersion != '') {
		$pos = strrpos($apacheVersion,'.');
		$apacheVersion = substr($apacheVersion,0,$pos);
	}

	// modifying httpd.conf apache file for LoadModule php5_module or php7_module or php_module
	$httpdFileContents = file_get_contents_dos($c_apacheConfFile);
	$c_phpVersionDirA = str_replace($c_installDir, '${INSTALL_DIR}',$c_phpVersionDir);
	$search = '~^(LoadModule[ \t]+)(php_module|php7_module|php5_module)([ \t]+".+/bin/php/)(.+)(/)(.+\.dll)"~mi';
	preg_match($search,$httpdFileContents,$matches);
	$replacement = $matches[1].$phpConf['apache'][$apacheVersion]['LoadModuleName'].' "'.$c_phpVersionDirA.'/php'.$newPhpVersion.$matches[5].$phpConf['apache'][$apacheVersion]['LoadModuleFile'].'"';
	if($matches[0] <> $replacement) {
		$httpdFileContents = str_replace(trim($matches[0]),$replacement,$httpdFileContents,$count);
		if($count > 0) {
			write_file($c_apacheConfFile,$httpdFileContents);
		}
	}
	unset($httpdFileContents);

	//modifying the conf of WampServer
	$wampIniNewContents['phpIniDir'] = $phpConf['phpIniDir'];
	$wampIniNewContents['phpExeDir'] = $phpConf['phpExeDir'];
	$wampIniNewContents['phpConfFile'] = $phpConf['phpConfFile'];
	$wampIniNewContents['phpVersion'] = $newPhpVersion;
	wampIniSet($configurationFile, $wampIniNewContents);

	//Create symbolic link to php dll's and to phpForApache.ini of new version
	linkPhpDllToApacheBin($newPhpVersion);

}

// Create parameter in $configurationFile file
// $name = parameter name -- $value = parameter value
// $section = name of the section to add parameter after
function createWampConfParam($name, $value, $section, $configurationFile) {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);
	$wampConfFileContents = @file_get_contents($configurationFile) or die ($configurationFile."file not found");
	$addTxt = $name.' = "'.$value.'"';
	$wampConfFileContents = str_replace($section,$section."\r\n".$addTxt,$wampConfFileContents);
	write_file($configurationFile,$wampConfFileContents);
}

//**** Functions to check if IP is valid and/or in a range ****
/*
 * ip_in_range.php - Function to determine if an IP is located in a
 * specific range as specified via several alternative formats.
 *
 * Network ranges can be specified as:
 * 1. Wildcard format:     1.2.3.*
 * 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
 * 3. Start-End IP format: 1.2.3.0-1.2.3.255
 *
 * Return value BOOLEAN : ip_in_range($ip, $range);
 *
 * Copyright 2008: Paul Gregg <pgregg@pgregg.com>
 * 10 January 2008
 * Version: 1.2
 *
 * Source website: http://www.pgregg.com/projects/php/ip_in_range/
 * Version 1.2
 * Please do not remove this header, or source attibution from this file.
 */

// decbin32
// In order to simplify working with IP addresses (in binary) and their
// netmasks, it is easier to ensure that the binary strings are padded
// with zeros out to 32 characters - IP addresses are 32 bit numbers
function decbin32 ($dec) {
  return str_pad(decbin($dec), 32, '0', STR_PAD_LEFT);
}

// ip_in_range
// This function takes 2 arguments, an IP address and a "range" in several
// different formats.
// Network ranges can be specified as:
// 1. Wildcard format:     1.2.3.*
// 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
// 3. Start-End IP format: 1.2.3.0-1.2.3.255
// The function will return true if the supplied IP is within the range.
// Note little validation is done on the range inputs - it expects you to
// use one of the above 3 formats.
function ip_in_range($ip, $range) {
  if(strpos($range, '/') !== false) {
    // $range is in IP/NETMASK format
    list($range, $netmask) = explode('/', $range, 2);
    if(strpos($netmask, '.') !== false) {
      // $netmask is a 255.255.0.0 format
      $netmask = str_replace('*', '0', $netmask);
      $netmask_dec = ip2long($netmask);
      return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
    } else {
      // $netmask is a CIDR size block
      // fix the range argument
      $x = explode('.', $range);
      while(count($x)<4) $x[] = '0';
      list($a,$b,$c,$d) = $x;
      $range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
      $range_dec = ip2long($range);
      $ip_dec = ip2long($ip);

      # Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
      #$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

      # Strategy 2 - Use math to create it
      $wildcard_dec = pow(2, (32-$netmask)) - 1;
      $netmask_dec = ~ $wildcard_dec;

      return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
    }
  } else {
    // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
    if(strpos($range, '*') !==false) { // a.b.*.* format
      // Just convert to A-B format by setting * to 0 for A and 255 for B
      $lower = str_replace('*', '0', $range);
      $upper = str_replace('*', '255', $range);
      $range = "$lower-$upper";
    }

    if(strpos($range, '-')!==false) { // A-B format
      list($lower, $upper) = explode('-', $range, 2);
      $lower_dec = (float)sprintf("%u",ip2long($lower));
      $upper_dec = (float)sprintf("%u",ip2long($upper));
      $ip_dec = (float)sprintf("%u",ip2long($ip));
      return ( ($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
    }

    error_log('Range argument is not in 1.2.3.4/24 or 1.2.3.4/255.255.255.0 format');
    return false;
  }
}
function check_IP_local($ip) {
	global $wampConf;
	$valid = false;
	//Check if valid IPv4
	if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
		$ranges = array('127.0.0.0/8');
		if($wampConf['VhostAllLocalIp'] == 'on')
			$ranges = array_merge($ranges, array('10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'));
		foreach($ranges as $value) {
			if(ip_in_range($ip, $value)) {
				$valid = true;
				break;
			}
		}
	}
	return $valid;
}

//Function to retrieve the Apache variables (Define)
//Default from wamp(64)\bin\apache\apache2.4.xx\wampdefineapache.conf file.
//With $apacheItself true, from command httpd.exe -t -D DUMP_RUN_CFG
function retrieve_apache_define($c_apacheDefineConf,$apacheItself = false) {
	global $c_apacheExe, $c_apacheError;
	$c_apacheError = '';
	$c_ApacheDefine = array();
	if(!$apacheItself) {
		if(file_exists($c_apacheDefineConf)) {
			$c_ApacheDefine = @parse_ini_file($c_apacheDefineConf,false,INI_SCANNER_RAW);
		}
	}
	else{
		//$command = 'CMD /D /C '.$c_apacheExe." -t -D DUMP_RUN_CFG";
		//$output = `$command`;
		$command = $c_apacheExe." -t -D DUMP_RUN_CFG";
		$output = proc_open_output($command);
		if(!empty($output)) {
			if(stripos($output,'Syntax error') !== false)  {
				$c_apacheError = $output;
			}
			else {
				if(preg_match_all("~^Define: (.+)=(.+)~m",$output, $matches) > 0 )
				$c_ApacheDefine = array_combine($matches[1], $matches[2]);
			}
		}
	}
	return $c_ApacheDefine;
}

//Function to check if it is Apache variable
function is_apache_var($a_var) {
	global $c_ApacheDefine;
	if(preg_match('~\${(.+)}~',$a_var,$var) > 0) {
		if(array_key_exists($var[1],$c_ApacheDefine))
			return true;
	}
  return false;
}
//Function to replace Apache variable name by it contents
function replace_apache_var($chemin) {
	global $c_ApacheDefine,$c_apacheService;
	if(preg_match('~\${(.+)}~',$chemin,$var) > 0) {
		if(array_key_exists($var[1],$c_ApacheDefine)) {
			$chemin = str_replace($var[0],trim($c_ApacheDefine[$var[1]]),$chemin);
		}
		else {
			$errorTxt = "Apache variable '".$var[0]."' is not defined.\n";
			error_log($errorTxt);
			if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
		}
	}
	return $chemin;
}
// Function to retrieve Apache Listen ports
function listen_ports($ApacheHttpdConfFile) {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);
	$c_listenPort = array();
	$httpdFileContents = file_get_contents($ApacheHttpdConfFile);
	preg_match_all("~^Listen[ \t]+.*:(\S*)\s*$~m",$httpdFileContents, $matches);
	$c_listenPort = array_values(array_map('replace_apache_var',array_unique($matches[1])));
	sort($c_listenPort);
	return (array)$c_listenPort;
}

// Function to check if VirtualHost exist and are valid
function check_virtualhost($check_files_only = false) {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);
	global $wampConf, $c_apacheConfFile, $c_apacheVhostConfFile, $c_DefaultPort, $c_UsedPort, $wwwDir, $c_phpVersion,
		$c_hostsFile, $c_phpVersionDir, $phpVersionList, $phpFcgiVersionList, $phpFcgiVersionListUsed,$c_apacheConfDir;
	clearstatcache();
	$virtualHost = array(
		'include_vhosts' => true,
		'vhosts_exist' => true,
		'nb_Server' => 0,
		'Server' => array(),
		'DocRootNotwww' => array(),
		'ServerName' => array(),
		'ServerNameDev' => array(),
		'ServerNameIp' => array(),
		'ServerNamePort' => array(),
		'ServerNameValid' => array(),
		'ServerNameQuoted' => array(),
		'ServerNameIDNA' => array(),
		'ServerNameUTF8' => array(),
		'ServerNameIpValid' => array(),
		'ServerNamePortValid' => array(),
		'ServerNamePortListen' => array(),
		'ServerNamePortApacheVar' => array(),
		'ServerNameIntoHosts' => array(),
		'FirstServerName' => '',
		'nb_Alias' => 0,
		'alias' => array(),
		'aliasDir' => array(),
		'nb_Virtual' => 0,
		'nb_Virtual_Port' => 0,
		'virtual_port' => array(),
		'virtual_ip' => array(),
		'nb_Document' => 0,
		'documentPath' => array(),
		'documentRoot' => array(),
		'documentPathValid' => array(),
		'documentPathNotSlashEnded' => array(),
		'document' => true,
		'nb_Directory' => 0,
		'nb_End_Directory' => 0,
		'directoryPath' => array(),
		'directoryPathValid' => array(),
		'directoryPathSlashEnded' => array(),
		'directory' => true,
		'directorySlash' => true,
		'port_number' => true,
		'nb_duplicate' => 0,
		'duplicate' => array(),
		'nb_duplicateIp' => 0,
		'duplicateIp' => array(),
		'nb_NotListenPort' => 0,
		'port_listen' => true,
		'NotListenPort' => array(),
		'ServerNameFcgid' => array(),
		'ServerNameUseFcgid' => false,
		'ServerNameFcgidPHP' => array(),
		'ServerNameFcgidPHPOK' => array(),
		'ServerNameHttps' => array(),
		'ServerNameUseHttps' => false,
		'ServerNameHttpsFcgid' => array(),
		'ServerNameHttpsFcgidPHP' => array(),
		'ServerNameHttpsFcgidPHPOK' => array(),
	);

	$httpConfFileContents = file_get_contents($c_apacheConfFile);
	//is Include conf/extra/httpd-vhosts.conf uncommented?
	if(preg_match("~^[ \t]*#[ \t]*Include[ \t]+conf/extra/httpd-vhosts.conf.*$~m",$httpConfFileContents) > 0) {
		$virtualHost['include_vhosts'] = false;
		return $virtualHost;
	}
	//
	$virtualHost['vhosts_file'] = $c_apacheVhostConfFile;
	if(!file_exists($virtualHost['vhosts_file'])) {
		$virtualHost['vhosts_exist'] = false;
		return $virtualHost;
	}
	if($check_files_only) {
		return $virtualHost;
	}
	$myHostsContents = file_get_contents($c_hostsFile);
	$myVhostsContents = file_get_contents($virtualHost['vhosts_file']);
	//Extract values of Alias into VirtualHost
	$nb_Alias= preg_match_all("~^[ \t]*Alias[ \t]+(.*)[ \t](.*)\R~mi", $myVhostsContents, $Alias_matches);
	if($nb_Alias > 0) {
		$virtualHost['nb_Alias'] = $nb_Alias;
		$virtualHost['alias'] = $Alias_matches[1];
		$virtualHost['aliasDir'] = $Alias_matches[2];
	}
	// Extract values of ServerName (without # at the beginning of the line)
	$nb_Server = preg_match_all("/^[ \t]*ServerName[ \t]+(.*)\R/m", $myVhostsContents, $Server_matches);
	//error_log("Server_matches=".print_r($Server_matches,true));
	foreach($Server_matches[1] as $key => $value) {
		$with_quote = false;
		$value = trim($value);
		if(strpos($value,'"') !== false) {
			$value = str_replace('"','',$value);
			$Server_matches[1][$key] = str_replace('"','',$Server_matches[1][$key]);
			$Server_matches[0][$key] = str_replace('"','',$Server_matches[0][$key]);
			$with_quote = true;
		}
		$virtualHost['ServerNameQuoted'][$value] = $with_quote;
	}
	// Extract values of <VirtualHost *:xx> or <VirtualHost ip:xx> port number
	$nb_Virtual = preg_match_all("/^(?![ \t]*#).*\<VirtualHost[ \t]+(?:\*|([0-9.]*|_default_)):(.*)\>\R/m", $myVhostsContents, $Virtual_matches);
	foreach($Virtual_matches[1] as $key => $value) {
		if($value == '_default_') {
			$Virtual_matches[1][$key] = '';
			break;
		}
	}
	// Extract values of DocumentRoot path
	$nb_Document = preg_match_all("/^(?![ \t]*#).*DocumentRoot[ \t]+(.*?\r?)$/m", $myVhostsContents, $Document_matches);
	// Count number of <Directory that has to match the number of ServerName
	$nb_Directory = preg_match_all("/^(?![ \t]*#).*\<Directory[ \t]+(.*)\>\R/m", $myVhostsContents, $Dir_matches);
	$nb_End_Directory = preg_match_all("~^(?![ \t]*#).*\</Directory.*\R~m", $myVhostsContents, $end_Dir_matches);
	$server_name = array();
	if($nb_Server == 0) {
		$virtualHost['nb_server'] = 0;
		return $virtualHost;
	}
	$virtualHost['nb_Server'] = $nb_Server;
	$virtualHost['nb_Virtual'] = $nb_Virtual;
	$virtualHost['nb_Virtual_Port'] = count($Virtual_matches[2]);
	$virtualHost['nb_Document'] = $nb_Document;
	$virtualHost['nb_Directory'] = $nb_Directory;
	$virtualHost['nb_End_Directory'] = $nb_End_Directory;
	//Check validity of port number
	$virtualHost['virtual_port'] = array_merge($Virtual_matches[2]);

	$virtualHost['virtual_ip'] = array_merge($Virtual_matches[1]);
	for($i = 0 ; $i < count($Virtual_matches[1]) ; $i++) {
		$value_ori = $value = '';
		if(!empty($Server_matches[1][$i]))
			$value_ori = $value = trim($Server_matches[1][$i]);
		$port_ori = $virtualHost['virtual_port'][$i];
		$virtualHost['virtual_port'][$i] = replace_apache_var($virtualHost['virtual_port'][$i]);
		$port = $virtualHost['virtual_port'][$i];
		$virtualHost['Server'][$i]['Port'] = $port;
		//if($port <> '80') $value .= ':'.$port;
		$virtualHost['ServerNamePort'][$value] = $port;
		$virtualHost['ServerNamePortValid'][$value]	= true;
		$virtualHost['ServerNamePortListen'][$value]	= true;
		$virtualHost['ServerNamePortApacheVar'][$value] = true;
		if($port_ori <> $c_DefaultPort  && $port_ori <> $c_UsedPort && !is_apache_var($port_ori))
			$virtualHost['ServerNamePortApacheVar'][$value] = false;
		if(empty($port) || !is_numeric($port) || $port < 80 || $port > 65535) {
			$virtualHost['ServerNamePortValid'][$value]	= false;
			$virtualHost['port_number'] = false;
		}
	}

	//Check validity of DocumentRoot
	for($i = 0 ; $i < $nb_Document ; $i++) {
		$chemin = trim($Document_matches[1][$i], " \t\n\r\0\x0B\"");
		$chemin = replace_apache_var($chemin);
		$virtualHost['Server'][$i]['DocumentRoot'] = $chemin;
		$virtualHost['documentPath'][$i] = $chemin;
		$virtualHost['documentPathValid'][$chemin] = true;
		$virtualHost['documentPathNotSlashEnded'][$chemin] = true;
		if($wampConf['NotCheckVirtualHost'] == 'off') {
			if(!file_exists($chemin) || !is_dir($chemin)) {
				$virtualHost['documentPathValid'][$chemin] = false;
				$virtualHost['document'] = false;
			}
			elseif(substr($chemin,-1) == '/'){
				$virtualHost['documentPathNotSlashEnded'][$chemin] = false;
				$virtualHost['document'] = false;
			}
		}
	}

	//Check validity of Directory path
	for($i = 0 ; $i < $nb_Directory ; $i++) {
		$chemin = trim($Dir_matches[1][$i], " \t\n\r\0\x0B\"");
		$chemin = replace_apache_var($chemin);
		$virtualHost['directoryPath'][$i] = $chemin;
		$virtualHost['Server'][$i]['directoryPath'] = $chemin;
		if((!file_exists($chemin) || !is_dir($chemin)) && $wampConf['NotCheckVirtualHost'] == 'off') {
			$virtualHost['directoryPathValid'][$chemin] = false;
			$virtualHost['directory'] = false;
		}
		else
			$virtualHost['directoryPathValid'][$chemin] = true;
		//Check Directory path ended with slash
		if(substr($chemin,-1) != '/') {
			$virtualHost['directoryPathSlashEnded'][$chemin] = false;
			$virtualHost['directorySlash'] = false;
		}
		else
			$virtualHost['directoryPathSlashEnded'][$chemin] = true;
	}

	//Check validity of ServerName
	$TempServerName = array();
	$TempServerIp = array();
	for($i = 0 ; $i < $nb_Server ; $i++) {
		$value = trim($Server_matches[1][$i]);
		$virtualHost['Server'][$i]['ServerName'] = $value;
		$nameToCheck = $value;
		//First server name
		if($i == 0)	$virtualHost['FirstServerName'] = $value;
		/*if($virtualHost['virtual_port'][$i] <> '80') {
			$value .= ':'.$virtualHost['virtual_port'][$i];
		}*/
		$TempServerName[] = $value;
		$virtualHost['ServerName'][$value] = $value;
		$virtualHost['documentRoot'][$value] = $virtualHost['documentPath'][$i];
		$virtualHost['ServerNameDev'][$value] = false;
		$virtualHost['ServerNameIp'][$value] = false;
		$virtualHost['ServerNameIpValid'][$value] = false;
		$virtualHost['ServerNameIntoHosts'][$value] = true;

		//Validity of ServerName (Like domain name)
		// IDNA (Punycode) - 3.2.3 improve regex
		$regexIDNA = '#^([\w-]+://?|www[\.])?xn--[a-z0-9]+[a-z0-9\-\.]*[a-z0-9]+(\.[a-z]{2,7})?$#';
		// Not IDNA  /^[A-Za-z]+([-.](?![-.])|[A-Za-z0-9]){1,60}[A-Za-z0-9]$/
		if(
			(preg_match($regexIDNA,$nameToCheck,$matchesIDNA) == 0)
			&& (preg_match('/^
			(?=.*[A-Za-z])  # at least one letter somewhere
		  [A-Za-z0-9]+ 		# letter or number in first place
			([-.](?![-.])		#  a . or - not followed by . or -
						|					#   or
			[A-Za-z0-9]			#  a letter or a number
			){0,60}					# this, repeated from 0 to 60 times - at least two characters
			[A-Za-z0-9]			# letter or number at the end
			$/x',$nameToCheck) == 0)
			&& $wampConf['NotCheckVirtualHost'] == 'off') {
			$virtualHost['ServerNameValid'][$value] = false;
			//$virtualHost['ServerNameQuoted'][$value] = false;
			//if(strpos($value,'"') !== false) {
			if($virtualHost['ServerNameQuoted'][$value]) {
				$virtualHost['ServerNameValid'][$value] = false;
				//$virtualHost['ServerNameQuoted'][$value] = true;
				$virtualHost['ServerNameIDNA'][$value] = false;
				$virtualHost['ServerNameUTF8'][$value] = $value;
			}
		}
		elseif(strpos($value,"dummy-host") !== false || strpos($value,"example.com") !== false) {
			$virtualHost['ServerNameValid'][$value] = 'dummy';
		}
		else {
			$virtualHost['ServerNameValid'][$value] = true;
			//$virtualHost['ServerNameQuoted'][$value] = false;
			if(empty($matchesIDNA[0])) {
				$virtualHost['ServerNameIDNA'][$value] = false;
				$virtualHost['ServerNameUTF8'][$value] = $value;
			}
			else {
				$virtualHost['ServerNameIDNA'][$value] = true;
				if(version_compare($c_phpVersion , '5.4.0', '<'))
					$virtualHost['ServerNameUTF8'][$value] = idn_to_utf8($value);
				else
					$virtualHost['ServerNameUTF8'][$value] = idn_to_utf8($value,IDNA_DEFAULT,INTL_IDNA_VARIANT_UTS46);
			}
			//Check optionnal IP
			if(!empty($virtualHost['virtual_ip'][$i])) {
				$Virtual_IP = $virtualHost['virtual_ip'][$i];
				$virtualHost['Server'][$i]['ip'] = $Virtual_IP;
				$virtualHost['ServerNameIp'][$value] = $Virtual_IP;
				if(check_IP_local($Virtual_IP)) {
					$virtualHost['ServerNameIpValid'][$value] = true;
					$TempServerIp[] = $Virtual_IP;
				}
			}
			else {
				$virtualHost['Server'][$i]['ip'] = '';
			}
		}
	//Check ServerName into hosts file
	if(stripos($myHostsContents, $value) === false && $wampConf['NotCheckVirtualHost'] =='off')
		$virtualHost['ServerNameIntoHosts'][$value] = false;
	} //End for

	//Check if tld is .dev
	if($wampConf['NotVerifyTLD'] == 'off') {
		foreach($virtualHost['ServerNameDev'] as $keydev => &$valuedev) {
			$tld = substr($keydev,-4);
			if($tld !== false && (mb_strtolower($tld) == '.dev'))
				$valuedev = true;
		}
	}

	//Check if duplicate ServerName
	if($wampConf['NotCheckDuplicate'] == 'off' && $wampConf['NotCheckVirtualHost'] == 'off') {
		$array_unique = array_unique($TempServerName);
		if(count($TempServerName) - count($array_unique) != 0 ){
			$virtualHost['nb_duplicate'] = count($TempServerName) - count($array_unique);
    	for ($i=0; $i < count($TempServerName); $i++) {
    		if(!array_key_exists($i, $array_unique))
      		$virtualHost['duplicate'][] = $TempServerName[$i];
    	}
		}
		//Check duplicate Ip
		$array_unique = array_unique($TempServerIp);
		if(count($TempServerIp) - count($array_unique) != 0 ){
			$virtualHost['nb_duplicateIp'] = count($TempServerIp) - count($array_unique);
    	for ($i=0; $i < count($TempServerIp); $i++) {
    		if(!array_key_exists($i, $array_unique))
      		$virtualHost['duplicateIp'][] = $TempServerIp[$i];
    	}
		}
	}

	//Check VirtualHost port not Listen port in httpd.conf
	$diffVL = array_diff(array_values(array_unique(array_values($virtualHost['ServerNamePort']))),listen_ports($c_apacheConfFile));
	if(count($diffVL) > 0) {
		$virtualHost['port_listen'] = false;
		$virtualHost['nb_NotListenPort'] = count($diffVL);
	foreach($diffVL as $value)
		$virtualHost['NotListenPort'] += array_fill_keys(array_keys($virtualHost['ServerNamePort'],$value),$value);
	foreach($virtualHost['NotListenPort'] as $key => $value)
		$virtualHost['ServerNamePortListen'][$key] = $value;
	}
	//Check if some VirtualHost use $wwwDir DocumentRoot reserved for localhost
	foreach($virtualHost['ServerName'] as $value) {
		$SerName = $value;
		$DocRoot = $virtualHost['documentRoot'][$value];
		$DocLocal = $virtualHost['documentRoot']['localhost'];
		$virtualHost['DocRootNotwww'][$SerName] = true;
		if(mb_strtolower($DocRoot) == mb_strtolower($DocLocal) && stripos($SerName,'localhost') === false) {
			$virtualHost['DocRootNotwww'][$SerName] = false;
		}
	}
	//Check if VirtualHost use Apache fcgid_module & PHP version used.
	$myVhostsContents = file_get_contents($c_apacheVhostConfFile);
	$phpVersionList = listDir($c_phpVersionDir,'checkPhpConf','php',true);
	if(!isset($phpFcgiVersionList)) GetAliasVersions();
	foreach($virtualHost['ServerName'] as $value) {
		$virtualHost['ServerNameFcgid'][$value] = false;
		$virtualHost['ServerNameFcgidPHP'][$value] = '0.0.0';
		$virtualHost['ServerNameFcgidPHPOK'][$value] = false;
		$p_value = preg_quote($value);
		//Extract <VirtualHost... </VirtualHost>
		$mask = "~
			<VirtualHost                         # beginning of VirtualHost
			[^<]*(?:<(?!/VirtualHost)[^<]*)*     # avoid premature end
			\n\s*ServerName\s+{$p_value}\s*\n    # Test server name
			.*?                                  # we stop as soon as possible
			</VirtualHost\>\s*\n                 # end of VirtualHost
			~isx";
		if(preg_match($mask,$myVhostsContents,$matches) === 1) {
			//Check if VirtualHost use <IfModule fcgid_module> not commented
			//if(strpos($matches[0],'<IfModule fcgid_module>') !== false) {
				if(preg_match("~^(#)?[ \t]*\<IfModule fcgid_module\>\r?$~m",$matches[0],$comment) === 1) {
				if(!isset($comment[1])) {
					$virtualHost['ServerNameFcgid'][$value] = true;
					//Check if VirtualHost use Define FCGIPHPVERSION
					if(strpos($matches[0],'Define FCGIPHPVERSION') !== false) {
						if(preg_match('~Define FCGIPHPVERSION "([0-9\.]+)"~im',$matches[0],$matches_fcgi) === 1) {
							//PHP version used is $matches_fcgi[1]
							$virtualHost['ServerNameFcgidPHP'][$value] = $matches_fcgi[1];
							$phpFcgiVersionList[] = $matches_fcgi[1];
							$phpFcgiVersionListUsed[$matches_fcgi[1]][] = $value;
						}
					}
					//Check ifPHP version used exists as Wampserver addon
					if(in_array($virtualHost['ServerNameFcgidPHP'][$value],$phpVersionList) !== false) {
						$virtualHost['ServerNameFcgidPHPOK'][$value] = true;
					}
				}
			}
		}
	}
	//Are there any https VirtualHosts ?
	if(preg_match("~^Include[ \t]+conf/extra/httpd-ssl.conf.*$~mi",$httpConfFileContents) > 0
		&& preg_match("~^LoadModule[ \t]+ssl_module modules/mod_ssl.so.*$~mi",$httpConfFileContents) > 0
		&& preg_match("~^LoadModule[ \t]+socache_shmcb_module modules/mod_socache_shmcb.so.*$~mi",$httpConfFileContents) > 0) {
		//Requirements for VirtualHost https OK
		$httpdsslFileContents = file_get_contents($c_apacheConfDir.'/extra/httpd-ssl.conf');
		preg_match_all('~^Define SERVERNAMEVHOSTSSL ([a-z0-9\.\-]+).*$~mi',$httpdsslFileContents,$matches);
		foreach($matches[1] as $value) {
			$virtualHost['ServerNameHttps'][] = $value;
		}
	}
	//Check if https VirtualHost use Apache fcgid_module & PHP version used.
	if(count($virtualHost['ServerNameHttps']) > 0){
		$httpdsslFileContents = file_get_contents($c_apacheConfDir.'/extra/httpd-ssl.conf');
		foreach($virtualHost['ServerNameHttps'] as $value) {
			//Extract Define SERVERNAMEVHOSTSSL ... </VirtualHost>
			$p_value = preg_quote($value);
			$mask = "~^Define SERVERNAMEVHOSTSSL {$p_value}.*?</VirtualHost>\r?$~mis";
			if(preg_match($mask,$httpdsslFileContents,$matches) !== 1) continue;
			//Check if there is FCGI PHP used in https vhost
			$virtualHost['ServerNameHttpsFcgid'][$value] = false;
			$virtualHost['ServerNameHttpsFcgidPHP'][$value] = '';
			$virtualHost['ServerNameHttpsFcgidPHPOK'][$value] = false;
			//Check if VirtualHost use <IfModule fcgid_module> not commented
			if(preg_match("~^(#)?[ \t]*\<IfModule fcgid_module\>\r?$~m",$matches[0],$comment) === 1) {
				if(!isset($comment[1])) {
					$virtualHost['ServerNameHttpsFcgid'][$value] = true;
					//Check if VirtualHost use Define FCGIPHPVERSION
					if(strpos($matches[0],'Define FCGIPHPVERSION') !== false) {
						if(preg_match('~Define FCGIPHPVERSION "([0-9\.]+)"~im',$matches[0],$matches_fcgi) === 1) {
							//PHP version used is $matches_fcgi[1]
							$virtualHost['ServerNameHttpsFcgidPHP'][$value] = $matches_fcgi[1];
							$phpFcgiVersionList[] = $matches_fcgi[1];
							$phpFcgiVersionListUsed[$matches_fcgi[1]][] = $value;
						}
					}
					//Check ifPHP version used exists as Wampserver addon
					if(in_array($virtualHost['ServerNameHttpsFcgidPHP'][$value],$phpVersionList) !== false) {
						$virtualHost['ServerNameHttpsFcgidPHPOK'][$value] = true;
					}
				}
			}
		}//End foreach
	}

	if($wampConf['NotCheckVirtualHost'] == 'on') {
		$virtualHost['nb_Server'] = $virtualHost['nb_Virtual'];
		$virtualHost['nb_Document'] = $virtualHost['nb_Virtual'];
		$virtualHost['nb_Directory'] = $virtualHost['nb_Virtual'];
		$virtualHost['nb_End_Directory'] = $virtualHost['nb_Virtual'];
		$virtualHost['nb_duplicateIp'] = 0;
		$virtualHost['nb_duplicate'] = 0;
		$virtualHost['port_number'] = true;
		$virtualHost['port_listen'] = true;
		$virtualHost['nb_NotListenPort'] = 0;
	}
	if(in_array(true,$virtualHost['ServerNameFcgid'],true)) {
		$virtualHost['ServerNameUseFcgid'] = true;
	}
	if(!empty($virtualHost['ServerNameHttps'])) {
		$virtualHost['ServerNameUseHttps'] = true;
	}
	//error_log("virtualHost=\n".print_r($virtualHost, true));
	return $virtualHost;
}

// List all versions PHP, MySQL, MariaDB, Apache into array
// with USED or CLI or FCGI added to version number
// like  5.6.40CLI - 7.3.10USED - 2.4.41USED - 5.7.27USED - 7.4.27FCGI
function ListAllVersions() {
	global $c_phpVersionDir, $c_phpVersion,$c_phpCliVersion,$phpVersionList,$phpFcgiVersionList,$phpFcgiVersionListUsed,
		$c_apacheVersionDir,$c_apacheVersion, $apacheVersionList,
		$c_mysqlVersionDir,$c_mysqlVersion, $mysqlVersionList,
		$c_mariadbVersionDir,$c_mariadbVersion, $mariadbVersionList,
		$wampConf;
	$Versions = array(
		'apache' => array(),
		'php' => array(),
		'mysql' => array(),
		'mariadb' => array(),
	);
	//Apache versions
	if(!isset($apacheVersionList)) {
		$apacheVersionList = listDir($c_apacheVersionDir,'checkApacheConf','apache',true);
	}
	foreach ($apacheVersionList as $oneApacheVersion) {
  	if($oneApacheVersion == $c_apacheVersion)
  		$oneApacheVersion .= 'USED';
  	$Versions['apache'][] = $oneApacheVersion;
	}
	//PHP versions
	if(!isset($phpVersionList)) {
		$phpVersionList = listDir($c_phpVersionDir,'checkPhpConf','php',true);
	}
	if(!isset($phpFcgiVersionList)) {
		GetAliasVersions();
		check_virtualhost();
	}
	$phpFcgiVersionList = array_unique($phpFcgiVersionList);
	foreach ($phpVersionList as $onePhpVersion) {
		$onePhpVersionTemp = $onePhpVersion;
		if($onePhpVersion == $c_phpVersion)
			$onePhpVersionTemp .= 'USED';
		if($onePhpVersion == $c_phpCliVersion)
			$onePhpVersionTemp .= 'CLI';
		if(in_array($onePhpVersion,$phpFcgiVersionList))
			$onePhpVersionTemp .= 'FCGI';
		$Versions['php'][] = $onePhpVersionTemp;
	}
	//MySQL versions
	if(!isset($mysqlVersionList)) {
		$mysqlVersionList = listDir($c_mysqlVersionDir,'checkMysqlConf','mysql',true);
	}
	foreach ($mysqlVersionList as $oneMysqlVersion) {
  	if($wampConf['SupportMySQL'] == 'on' && $oneMysqlVersion == $c_mysqlVersion)
  		$oneMysqlVersion .= 'USED';
  	$Versions['mysql'][] = $oneMysqlVersion;
	}
	//MariaDB versions
	if(!isset($mariadbVersionList)) {
		$mariadbVersionList = listDir($c_mariadbVersionDir,'checkMariaDBConf','mariadb',true);
	}
	foreach ($mariadbVersionList as $oneMariadbVersion) {
  	if($wampConf['SupportMariaDB'] == 'on' && $oneMariadbVersion == $c_mariadbVersion)
  		$oneMariadbVersion .= 'USED';
  	$Versions['mariadb'][] = $oneMariadbVersion;
	}
	return $Versions;
}

// Callback function must exist and return true or false
// False to delete array item - True to not delete
function array_filter_recursive($array, $callback) {
	foreach ($array as $key => &$value) { // Warning, $value is by reference
		if(is_array($value))
			$value = array_filter_recursive($value, $callback);
		elseif(!$callback($value)) unset($array[$key]);
	}
	unset($value); // Suppress the reference
	return $array;
}

// Get content of file and set lines end to DOS (CR/LF) if needed
function file_get_contents_dos($file, $retour = true) {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__.' - '.$file." - return=".($retour ? 'true' : 'false')."\n",3,WAMPTRACE_FILE);
	$check_DOS = @file_get_contents($file) or die ($file."file not found");
	//Check if there is \n without previous \r
	if(preg_match("/(?<!\r)\n/",$check_DOS) > 0) {
		$count = 0;
		$check_DOS = preg_replace(array('/\r\n?/','/\n/'),array("\n","\r\n"), $check_DOS, -1, $count);
		if($count > 0) {
			if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__.' - '.$file." -+- REWRITE FILE ASKED -+-\n",3,WAMPTRACE_FILE);
			write_file($file,$check_DOS);
		}
	}
	if($retour) return $check_DOS;
}

// Clean file contents
function clean_file_contents($contents, $twoToNone = array(2,0), $all_spaces = false, $hashlines = false, $save=false, $file='') {
	global $clean_count;
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__.' '.$file."\n",3,WAMPTRACE_FILE);
	$clean_count = false;
	if($all_spaces) {
		//more than one space into one space
		$contents = preg_replace("~[ \t]{2,}~",' ',$contents,-1, $count);
		if($count > 0) $clean_count = true;
	}
	//suppress spaces or tabs at the end of lines
	$contents = preg_replace('~[ \t]+(\r?)$~m',"$1",$contents, -1, $count);
	if($count > 0) $clean_count = true;
	//suppress more than $twoToNone[0] empty line into $twoToNone[1] empty lines
	// For Unix, Windows, Mac OS X & old Mac OS Classic
	/* "/^(?:[\t ]*(?>\r?\n|\r)){2,}/m" */
	// For Unix, Windows & Mac OS X (Without old Mac OS Classic)
	// "/^(?:[\t\r ]*\n){2,}/m"
	$contents = preg_replace("/^(?:[\t\r ]*\n){".$twoToNone[0].",}/m",str_repeat("\r\n",$twoToNone[1]),$contents,-1, $count);
	if($count > 0) $clean_count = true;
	if($hashlines) {
		//Replace more than 2 lines with # and no comment into only one line
		$contents = preg_replace("/^(?:[\t ]*#[\t \r]*\n){2,}/m",str_repeat("#\r\n",1),$contents,-1, $count);
		if($count > 0) $clean_count = true;
	}
	if($save && $clean_count) {
		if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__.' - '.$file." -+- REWRITE FILE ASKED -+-\n",3,WAMPTRACE_FILE);
		write_file($file,$contents);
	}
	return $contents;
}

//Clean variable string
function clean_string_var($contents, $twoToNone = array(1,0), $all_spaces = false) {
	//Check if there is \n without previous \r
	if(preg_match("/(?<!\r)\n/",$contents) > 0) {
		$contents = preg_replace(array('/\r\n?/','/\n/'),array("\n","\r\n"), $contents, -1);
	}
	//more than one space into one space
	if($all_spaces) $contents = preg_replace("~[ \t]{2,}~",' ',$contents,-1);
	//suppress spaces or tabs at the end of lines
	$contents = preg_replace('~[ \t]+(\r?)$~m',"$1",$contents, -1);
	//suppress more than $twoToNone[0] empty line into $twoToNone[1] empty lines
	// For Unix, Windows, Mac OS X & old Mac OS Classic
	/* "/^(?:[\t ]*(?>\r?\n|\r)){2,}/m" */
	// For Unix, Windows & Mac OS X (Without old Mac OS Classic)
	// "/^(?:[\t\r ]*\n){2,}/m"
	$contents = preg_replace("/^(?:[\t\r ]*\n){".$twoToNone[0].",}/m",str_repeat("\r\n",$twoToNone[1]),$contents,-1);
	return $contents;
}

//Check alias and paths in httpd-autoindex.conf
// Alias /icons/ "c:/Apache24/icons/" => Alias /icons/ "icons/"
// <Directory "c:/Apache24/icons"> => <Directory "icons">
// Don't modify if there is ${SRVROOT} variable (Apache 2.4.35)
function check_autoindex() {
	global $c_apacheAutoIndexConfFile;
	$autoindexContents = @file_get_contents($c_apacheAutoIndexConfFile) or die ("httpd-autoindex.conf file not found");
	if(strpos($autoindexContents, '${SRVROOT}') === false) {
		$autoindexContents = preg_replace("~^(Alias /icons/) (\".+icons/\")\r?$~m","$1 ".'"icons/"',$autoindexContents,1,$count1);
		$autoindexContents = preg_replace("~^(<Directory) (\".+icons\")>\r?$~m","$1 ".'"icons">',$autoindexContents,1,$count2);

		if($count1 == 1 || $count2 == 1) {
			write_file($c_apacheAutoIndexConfFile,$autoindexContents);
		}
	}
}

//Check if a folder exists then create it if not
function checkDir($dir) {
	$message = '';
	if(!file_exists($dir)) {
		if(mkdir($dir) === false) {
			$message = 'Can not create the '.$dir.' folder';
			error_log($message);
			return $message;
		}
	}
	elseif(!is_dir($dir)) {
		if(unlink($dir) === false) {
			$message = 'Can not delete the '.$dir.' file';
			error_log($message);
			return $message;
		}
		else {
			if(mkdir($dir) === false) {
				$message = 'Can not create the '.$dir.' folder';
				error_log($message);
				return $message;
			}
		}
	}
	if(!is_writable($dir)) {
		$message = 'The '.$dir.' folder is not writable';
		error_log($message);
		return $message;
	}
	return 'OK';
}

//Return error_reporting from integer into string
function errorLevel($error_number) {
	$error_description = $error_comment = array();
	if(is_string($error_number)) {
		// To convert error_reporting value from string for example: 'E_ALL & ~E_WARNING'
		// into integer from constant value.
		$newpara = parse_ini_string('error_reporting = '.$error_number);
		$error_number = $newpara['error_reporting'];
	}
	//The ampersand "&" are doubled into strings to be displayed and not to be considered as a key prefix by Aestran Tray Menu.
	$error_codes = array(
	E_ALL => array('str' => "E_ALL", 'comment' => "Development value^Show all errors, warnings and notices including coding standards."),	//32767 - Development value
	(E_ALL & ~E_ERROR) => array('str' => "E_ALL && ~E_ERROR", 'comment' =>'Show all errors, except for fatal run-time errors'), //32766
	(E_ALL & ~E_WARNING)	=> array('str' => "E_ALL && ~E_WARNING", 'comment' => 'Show all errors, except for warnings'), //32765
	(E_ALL & ~E_NOTICE) => array('str' => "E_ALL && ~E_NOTICE",	'comment' => 'Show all errors, except for notices'), //32759
	(E_ALL & ~E_NOTICE & ~E_STRICT)	=> array('str' => "E_ALL && ~E_NOTICE && ~E_STRICT", 'comment' =>'Show all errors, except for notices and coding standards warnings'), //30711
	(E_ALL & ~E_DEPRECATED & ~E_STRICT)	=> array('str' => "E_ALL && ~E_DEPRECATED && ~E_STRICT", 'comment' =>'Production value^Show all errors, except for notices .'), // 22527
	(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED) => array('str' => "E_ALL && ~E_NOTICE && ~E_STRICT && ~E_DEPRECATED", 'comment' => 'Default value^Show all errors, except for notices and coding standards warnings and code that will not work in future versions of PHP'), // 22519 Default value
	E_USER_DEPRECATED => array('str' => "E_USER_DEPRECATED", 'comment' => 'user-generated deprecation warnings'), //16384
	E_DEPRECATED => array('str' => "E_DEPRECATED", 'comment' => 'warn about code that will not work in future versions of PHP'), // 8192
	(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR) => array('str' => 'E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR', 'comment' => 'Show only errors'), //4177
	E_RECOVERABLE_ERROR => array('str' => "E_RECOVERABLE_ERROR", 'comment' => 'almost fatal run-time errors'),// 4096
	E_STRICT => array('str' => "E_STRICT", 'comment' => 'run-time notices, enable to have PHP suggest changes to your code which will ensure the best interoperability and forward compatibility of your code'), // 2048
	E_USER_NOTICE => array('str' => "E_USER_NOTICE", 'comment' => 'user-generated notice message'), // 1024
	E_USER_WARNING => array('str' => "E_USER_WARNING", 'comment' => 'user-generated warning message'), // 512
	E_USER_ERROR => array('str' => "E_USER_ERROR", 'comment' => 'user-generated error message'), // 256
	E_COMPILE_WARNING => array('str' => "E_COMPILE_WARNING", 'comment' => 'compile-time warnings (non-fatal errors)'), // 128
	E_COMPILE_ERROR => array('str' => "E_COMPILE_ERROR", 'comment' => 'fatal compile-time errors'), // 64
	E_CORE_WARNING => array('str' => "E_CORE_WARNING", 'comment' => 'warnings (non-fatal errors) that occur during PHP\'s initial startup'), // 32
	E_CORE_ERROR => array('str' => "E_CORE_ERROR", 'comment' => 'fatal errors that occur during PHP\'s initial startup'), // 16
	E_NOTICE => array('str' => "E_NOTICE", 'comment' => 'run-time notices (these are warnings which often result from a bug in your code, but it\'s possible that it was intentional (e.g., using an uninitialized variable and relying on the fact it is automatically initialized to an empty string)'), // 8
	(E_PARSE | E_ERROR) => array('str' => "E_PARSE | E_ERROR", 'comment' => 'compile-time parse errors - fatal run-time errors'), // 5
	E_PARSE => array('str' => "E_PARSE", 'comment' => 'compile-time parse errors'), // 4
	E_WARNING => array('str' => "E_WARNING", 'comment' => 'run-time warnings (non-fatal errors)'), // 2
	E_ERROR => array('str' => "E_ERROR", 'comment' => 'fatal run-time errors'), // 1
	);
	$i = 0;
	foreach( $error_codes as $number => $description ) {
 		if(($number & $error_number) >= $number ) {
  		$error_description[$i]['str'] = $description['str'];
  		$error_description[$i]['comment'] = $description['comment'];
  		$error_number -= $number;
  		$i++;
 		}
	}
	return $error_description;
}

// Wrap texte into multi lines for Aestran Menu's
function menu_multi_lines($texte, $limit = 70) {
	$ConfTextInfo = '';
	$lines_report = explode('^',wordwrap($texte,$limit,'^'));
	foreach($lines_report as $value) {
 		$ConfTextInfo .= 'Type: item; Caption: "'.$value.'"; Action: none
';
	}
	return $ConfTextInfo;
}

//Function to convert filesize bytes into human units
function FileSizeConvert($bytes) {
	$bytes = floatval($bytes);
	$arBytes = array(
		0 => array('UNIT' => 'TiB','VALUE' => pow(1024,4)),
		1 => array('UNIT' => 'GiB','VALUE' => pow(1024,3)),
		2 => array('UNIT' => 'MiB','VALUE' => pow(1024,2)),
		3 => array('UNIT' => 'KiB','VALUE' => 1024),
		4 => array('UNIT' => 'B','VALUE' => 1),
	);
	$result = '0 B';
	foreach($arBytes as $arItem) {
		if($bytes >= $arItem['VALUE']) {
			$result = $bytes/$arItem['VALUE'];
      $result = strval(round($result, 2))." ".$arItem['UNIT'];
      break;
    }
  }
  return $result;
}
//Send command function via cmd.exe
//retrieve the result of stdout AND stderr
function proc_open_output($command) {
	global $c_apacheError;
	$descriptorspec = array(
		0 => array('pipe', 'rb'), // stdin
		1 => array('pipe', 'wb'), // stdout
		2 => array('pipe', 'wb')  // stderr
	);
	$process = proc_open(escapeshellcmd($command), $descriptorspec, $pipes);
	$output = '';
	while (!feof($pipes[1])) {
		foreach($pipes as $key => $pipe) {
			$line = stream_get_line($pipe,0);
			if($line !== false ) $output .= $line."\n";
		}
	}
	fclose($pipes[0]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	proc_close($process);

	return $output;
}

//Function to create colored string for command windows
//Color supported: black, red, green, yellow, blue, magenta,
//  cyan, white, bold, underline, inverse
//Color 'clean' suppress all color codes and return a cleaned string
function color($color,$string = '') {
	if(php_uname('r') == '6.1' ) return $string;
	$seq = array(
		'black'			=> chr(27).'[30m',
		'red'				=> chr(27).'[91m',
		'green'			=> chr(27).'[92m',
		'yellow'		=> chr(27).'[93m',
		'blue'			=> chr(27).'[94m',
		'magenta'		=> chr(27).'[95m',
		'cyan'			=> chr(27).'[96m',
		'white'			=> chr(27).'[97m',
		'inverse'		=> chr(27).'[7m',
		'bold'			=> chr(27).'[1m',
		'underline'	=> chr(27).'[4m',
		'reset'			=> chr(27).'[0;107;30m',
		'normal'		=> chr(27).'[30m',
	);
	if($color == 'clean') return str_replace(array_values($seq),'',$string);
	if(!array_key_exists($color, $seq)) return $string;
	if(empty($string)) $seq['normal'] = '';
	elseif($color == 'inverse' || $color == 'bold' || $color == 'underline') $seq['normal'] = $seq['reset'];
	return $seq[$color].$string.$seq['normal'];
}

//Function to output a command window, clears it and displays a message
function Command_Windows($message,$nbCols=-1,$nbLines=-1,$linesSup=0,$title='Wampserver',$readLine = '') {
	if($nbCols < 0) {
		$array = explode("\n",$message);
		foreach($array as $value) {
			//Number of escape sequences
			$Cols = strlen($value) - (substr_count($value,chr(27).'[')*5);
			if($Cols > $nbCols) $nbCols = $Cols;
		}
		$nbCols += 2;
		if($nbCols < 60) $nbCols = 60;
		if($nbCols > 132) $nbCols = 132;
	}
	if($nbLines < 0){
		$nbLines = substr_count($message,"\n") + 3;
	}
	$nbLines += $linesSup;
	if($nbLines > 9999) $nbLines = 9999;
	pclose(popen('mode con cols='.$nbCols.' lines='.$nbLines, 'w'));
	pclose(popen('TITLE '.escapeshellcmd($title), 'w'));
	pclose(popen('COLOR F0', 'w'));
	echo $message;
	if(!empty($readLine)) {
		return readline($readLine);
		echo "exit", "\n";
	}

}
//Function to create $wamp_versions_here items with last version
function create_wamp_versions($versionList,$soft) {
	global $wamp_versions_here;
	$racine = '00';
	foreach($versionList as $value) {
		$list = explode('.',$value);
		if($list[0].$list[1] != $racine) {
			$racine = $list[0].$list[1];
			$wamp_versions_here += array($soft.$racine => $value);
		}
		else {
			if(version_compare($value,$wamp_versions_here[$soft.$racine], '>'))
				$wamp_versions_here[$soft.$racine] = $value;
		}
	}
}

//Function to read the content of a dir
	function read_dir($dir) {
		if(substr($dir,-1,1) == '/') $dir = substr($dir,0,-1);
		$array = array();
		$d = dir($dir);
		while (false !== ($entry = $d->read())) {
			if($entry!='.' && $entry!='..') {
				$entry = $dir.'/'.$entry;
				if(is_dir($entry)) {
					$array[] = $entry;
					$array = array_merge($array, read_dir($entry));
				} else {
					$array[] = $entry;
				}
			}
		}
		$d->close();
		return $array;
	}

// Function to create definitions of XXXXMenuColor
// From $AesXXXXMenuColor in config.inc.php
function AestanMenuColor($AesMenuColor,$AesMenuText) {
	$MenuColorText = '';
	foreach($AesMenuColor as $key => $value) {
		$MenuColorText .= $AesMenuText.$key.'=';
		if(strpos($value[0],'$') === 0) {
			if(strpos($value[0],'[') !== false) {
				$temp = explode('[',substr($value[0],1));
				$temp[1] = substr($temp[1],0,-1);
				$array_temp = $GLOBALS[$temp[0]];
				$value[0] = trim(str_replace(',',' ',$array_temp[$temp[1]]));
			}
			else {
				$temp = substr($value[0],1);
				$value[0] = trim(str_replace(',',' ',$GLOBALS[$temp]));
			}
		}
		$TextTemp = '';
		foreach($value as $indice) $TextTemp .= $indice.',';
		$MenuColorText .= substr($TextTemp,0,-1)."\r\n";
	}
	$MenuColorText .="\r\n";
	return $MenuColorText;
}

//Function to replace some characters by entities
//for Aestan Tray Menu PromptText fields and Text menu items
//$What = 'all'  : \r\n by #13 and , by &#44;
//        else   : \r\n by nothing and , by space
function ReplaceAestan($value,$What = 'all') {
	if($What == 'all') {
		$search  = array("\r\n","\r","\n",',');
		$replace = array("#13",'','','&#44;');
	}
	else {
		$search  = array("\r\n","\r","\n",', ',',');
		$replace = array('','','',' ',' ');
	}
	return str_replace($search,$replace,$value);
}

// Function to get PhpMyAdmin version's and other alias (adminer, phpsysinfo, etc.)
// Retrieving the different aliases and versions for PhpMyAdmin
// $Alias_Contents['alias'] = alias for example phpmyadmin or phpmyadmin4.9.7 or adminer or phpsysinfo
// $Alias_Contents[x]['version'] = version for example 5.0.4 or 4.9.7 or 5.1.0rc1
// $Alias_Contents[x]['compat'] = true Compatible with PHP version used
//   if false $Alias_Contents[x]['notcompat'] = incompatibily text
// $Alias_Contents[x]['fcgid'] = true Use fcgid Apache module
// $Alias_Contents[x]['fcgidPHP'] = PHP version used with fcgid
// $Alias_Contents[x]['fcgidPHPOK'] = true PHP version exists
// $phmyadOK = true if at least one version of PhpMyAdmin

function GetAliasVersions(){
	global $c_installDir, $aliasDir, $phmyadOK, $c_phpVersion, $c_phpExe, $Alias_Contents, $wamp_versions_here,
	$WarningMenuPMA, $WarningTextPMA, $WarningsPMA, $c_ApacheDefine, $c_phpVersionDir,$phpFcgiVersionList,$phpFcgiVersionListUsed;
	$phpVersionList = listDir($c_phpVersionDir,'checkPhpConf','php',true);	$temp = array();
	$phmyadOK = false;
	if(!isset($phpFcgiVersionList)) $phpFcgiVersionList = $phpFcgiVersionListUsed = array();
	$temp = glob($aliasDir.'phpmyadmin*.conf');
	if(!empty($temp)) {
		$phmyadOK = true;
		$Alias_Contents['PMyAd'] = $Alias_Contents['PMyAdVer'] = array();
		foreach($temp as $key => $value) {
			$alias_contents = @file_get_contents($value);
	  	preg_match('~^Alias\s+/(phpmyadmin[0-9abrc\.]*)\s+"(.*apps/phpmyadmin([0-9abrc\.]+))/".*\r?$~m',$alias_contents,$matches);
	  	//error_log("matches0=".print_r($matches,true));
	  	//preg_match('~^Alias\s+/(phpmyadmin[0-9abrc\.]*)\s+".*apps/phpmyadmin([0-9abrc\.]+)/".*\r?$~m',$alias_contents,$matches);
	  	//error_log("matches1=".print_r($matches,true));
	  	$Alias_Contents[$matches[1]]['OK'] = true;
	  	$Alias_Contents['PMyAd'][] = $matches[1];
	  	$Alias_Contents['PMyAdVer'][] = 'phpmyadmin'.$matches[3];
			$Alias_Contents['alias'][] = $matches[1];
			$Alias_Contents[$matches[1]]['alias'] = $matches[1];
			$Alias_Contents[$matches[1]]['version'] = $matches[3];
			$Alias_Contents[$matches[1]]['path'] = str_ireplace('${INSTALL_DIR}',$c_installDir,$matches[2]);
			$Alias_Contents[$matches[1]]['aliaspath'] = $value;
			$Alias_Contents[$matches[1]]['compat'] = true;
			$Alias_Contents[$matches[1]]['fcgid'] = false;
			$Alias_Contents[$matches[1]]['fcgidPHP'] = '0.0.0';
			$Alias_Contents[$matches[1]]['fcgidPHPOK'] = false;
			$Alias_Contents[$matches[1]]['fcgiaff'] = '';
			//Retrieve php_admin_value's and php_admin_flag's
			$key_admin = -1;
			if(preg_match_all("/php_admin_value[ \t]+(.*)[ \t]+(.*)/mi",$alias_contents,$matches_admin,PREG_SET_ORDER) > 0) {
				//error_log("matches_admin=".print_r($matches_admin,true));
				foreach($matches_admin as $value_admin) {
					$Alias_Contents[$matches[1]]['php_admin_value'][++$key_admin]['param'] = trim($value_admin[1]);
					$Alias_Contents[$matches[1]]['php_admin_value'][$key_admin]['value'] = trim($value_admin[2]);
				}
			}
			if(preg_match_all("/php_admin_flag[ \t]+(.*)[ \t]+(.*)/mi",$alias_contents,$matches_admin,PREG_SET_ORDER) > 0) {
				//error_log("matches_admin=".print_r($matches_admin,true));
				foreach($matches_admin as $value_admin) {
					$Alias_Contents[$matches[1]]['php_admin_value'][++$key_admin]['param'] = trim($value_admin[1]);
					$Alias_Contents[$matches[1]]['php_admin_value'][$key_admin]['value'] = trim($value_admin[2]);
				}
			}

			//Check if PhpMyAdmin config hide mysql native databases - line commented or not
			// //$cfg['Servers'][$i]['hide_db'] = '(information_schema|mysql|performance_schema|sys)';
			$Alias_Contents[$matches[1]]['hide'] = $Alias_Contents[$matches[1]]['nopassword'] = true;
			$config_contents = @file_get_contents($Alias_Contents[$matches[1]]['path'].'/config.inc.php');
			if(stripos($config_contents,'//$cfg[\'Servers\'][$i][\'hide_db\']') !== false) {
				$Alias_Contents[$matches[1]]['hide'] = false;
			}
			if(preg_match("/(.cfg\['Servers.+AllowNoPassword.+=\s*)(.+)\s*;/mi",$config_contents,$matchesnopass) > 0) {
				if($matchesnopass[2] === false || $matchesnopass[2] == 'false')
					$Alias_Contents[$matches[1]]['nopassword'] = false;
			}
			if(isset($c_ApacheDefine['PHPROOT'])) {
				if(preg_match("~^(#)?[ \t]*\<IfModule fcgid_module\>\r?$~m",$alias_contents,$comment) === 1) {
					if(!isset($comment[1])) {
						$Alias_Contents[$matches[1]]['fcgid'] = true;
						//Search PHP version
						if(preg_match('~Define FCGIPHPVERSION "([0-9\.]+)"~im',$alias_contents,$matches_fcgi) === 1) {
							//PHP version used is $matches_fcgi[1]
							$Alias_Contents[$matches[1]]['fcgidPHP'] = $phpFcgiVersionList[] = $matches_fcgi[1];
							$phpFcgiVersionListUsed[$matches_fcgi[1]][] = 'phpmyadmin'.$matches[3];
							$Alias_Contents[$matches[1]]['fcgiaff'] = '';
							if(in_array($Alias_Contents[$matches[1]]['fcgidPHP'],$phpVersionList))
								$Alias_Contents[$matches[1]]['fcgidPHPOK'] = true;
								$Alias_Contents[$matches[1]]['fcgiaff'] .= ' #13 [FCGI -> PHP '.$Alias_Contents[$matches[1]]['fcgidPHP'].']';
						}
					}
				}
			}
		}//End foreach phpmyadmin
		//Check if PhpMyAdmin version is compatible with PHP version
		if(file_exists($c_installDir.'/scripts/appsversusphp.ini')) {
			$WarningsPMA = false;
			$WarningMenuPMA = ';WAMPMULTIPLEPHPMYADMINEND
';
			$WarningTextPMA = '';
			$temp = @parse_ini_file($c_installDir.'/scripts/appsversusphp.ini',true,INI_SCANNER_RAW);
			$phpVerPhpMyAdmin = $temp['phpphpmyadmin'];
			foreach($Alias_Contents['PMyAd'] as $cle => $version) {
				$VersionPhpMyAdmin = $Alias_Contents[$version]['version'];
				foreach($phpVerPhpMyAdmin as $key => $value) {
					$php_used = $c_phpVersion;
					//Change PHP version used if alias use fcgid_module
					if(isset($c_ApacheDefine['PHPROOT']) && $Alias_Contents[$version]['fcgid'] && $Alias_Contents[$version]['fcgidPHPOK']) {
						$php_used = $Alias_Contents[$version]['fcgidPHPOK'];
					}
					if(version_compare($php_used,$key,'>=')) {
						if(!(version_compare($VersionPhpMyAdmin,$value[0],'>=') && version_compare($VersionPhpMyAdmin,$value[1],'<='))) {
							$Alias_Contents[$version]['compat']= false;
							//$Alias_Contents[$version]['notcompat'] = 'PhpMyAdmin '.$VersionPhpMyAdmin.' not compatible with PHP '.$php_used;
							$Alias_Contents[$version]['notcompat'] = 'Not compatible with PHP '.$php_used;
							$WarningsPMA = true;
							$WarningMenuPMA .= 'Type: item; Caption: "PhpMyAdmin '.$VersionPhpMyAdmin.' - '.$Alias_Contents[$version]['notcompat'].'"; Glyph: 23; Action: multi; Actions: warning_phpmyadmin'.$VersionPhpMyAdmin.'
';
							$temp = "\r\nPhpMyAdmin ".$VersionPhpMyAdmin.' - '.$Alias_Contents[$version]['notcompat']."\r\nYou must use a version of PhpMyAdmin from ".$value[0]." to ".$value[1];
							$temp .= "\r\n----------------------------------------\r\n";
							$WarningTextPMA .= '[warning_phpmyadmin'.$VersionPhpMyAdmin.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($temp).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
						}
						break;
					}
				}
			}
		}
	}//End !empty($temp)

	//Get Adminer Version and parameters
	$Alias_Contents['adminer']['OK'] = false;
	$Alias_Contents['adminer']['version'] = $Alias_Contents['adminer']['fcgiaff'] = '';
	if(file_exists($aliasDir.'adminer.conf')) {
		$Alias_Contents['alias'][] = 'adminer';
		$Alias_Contents['adminer']['OK'] = true;
		$myalias = @file_get_contents($aliasDir.'adminer.conf');
		//Alias /adminer "J:/wamp/apps/adminer4.3.1/"
		preg_match('~^Alias\s*/adminer\s*".*apps/adminer([0-9\.]*)/"\s?$~m',$myalias,$matches);
		$Alias_Contents['adminer']['alias'] = '/adminer';
		$Alias_Contents['adminer']['version'] = $matches[1];
		$wamp_versions_here += array('wamp_adminer' => $matches[1]);
		//Check if FCGI PHP used
		$Alias_Contents['adminer']['fcgid'] = false;
		$Alias_Contents['adminer']['fcgidPHP'] = '0.0.0';
		$Alias_Contents['adminer']['fcgidPHPOK'] = false;
		if(isset($c_ApacheDefine['PHPROOT'])) {
			if(preg_match("~^(#)?[ \t]*\<IfModule fcgid_module\>\r?$~m",$myalias,$comment) === 1) {
				if(!isset($comment[1])) {
					$Alias_Contents['adminer']['fcgid'] = true;
					//Search PHP version
					if(preg_match('~Define FCGIPHPVERSION "([0-9\.]+)"~im',$myalias,$matches_fcgi) === 1) {
						//PHP version used is $matches_fcgi[1]
						$Alias_Contents['adminer']['fcgidPHP'] = $phpFcgiVersionList[] = $matches_fcgi[1];
						$phpFcgiVersionListUsed[$matches_fcgi[1]][] = 'adminer';
						if(in_array($Alias_Contents['adminer']['fcgidPHP'],$phpVersionList)) {
							$Alias_Contents['adminer']['fcgidPHPOK'] = true;
							$Alias_Contents['adminer']['fcgiaff'] .= ' #13 [FCGI -> PHP '.$Alias_Contents['adminer']['fcgidPHP'].']';
						}
					}
				}
			}
		}
	}

	//Get PhpSysInfo version and parameter
	$Alias_Contents['phpsysinfo']['OK'] = false;
	$Alias_Contents['phpsysinfo']['version'] = $Alias_Contents['phpsysinfo']['fcgiaff'] = '';
	if(file_exists($aliasDir.'phpsysinfo.conf')) {
		$Alias_Contents['alias'][] = 'phpsysinfo';
		$Alias_Contents['phpsysinfo']['OK'] = true;
		$myalias = @file_get_contents($aliasDir.'phpsysinfo.conf');
		//Alias /phpsysinfo "J:/wamp/apps/phpsysinfo3.4.0/"
		preg_match('~^Alias\s*/phpsysinfo\s*".*apps/phpsysinfo([0-9\.]*)/"\s?$~m',$myalias,$matches);
		$Alias_Contents['phpsysinfo']['alias'] = '/phpsysinfo';
		$Alias_Contents['phpsysinfo']['version'] = $matches[1];
		$wamp_versions_here += array('wamp_phpsysinfo' => $matches[1]);
		//Check if FCGI PHP used
		$Alias_Contents['phpsysinfo']['fcgid'] = false;
		$Alias_Contents['phpsysinfo']['fcgidPHP'] = '0.0.0';
		$Alias_Contents['phpsysinfo']['fcgidPHPOK'] = false;
		if(isset($c_ApacheDefine['PHPROOT'])) {
			if(preg_match("~^(#)?[ \t]*\<IfModule fcgid_module\>\r?$~m",$myalias,$comment) === 1) {
				if(!isset($comment[1])) {
					$Alias_Contents['phpsysinfo']['fcgid'] = true;
					//Search PHP version
					if(preg_match('~Define FCGIPHPVERSION "([0-9\.]+)"~im',$myalias,$matches_fcgi) === 1) {
						//PHP version used is $matches_fcgi[1]
						$Alias_Contents['phpsysinfo']['fcgidPHP'] = $phpFcgiVersionList[] = $matches_fcgi[1];
						$phpFcgiVersionListUsed[$matches_fcgi[1]][] = 'phpsysinfo';
						if(in_array($Alias_Contents['phpsysinfo']['fcgidPHP'],$phpVersionList)) {
							$Alias_Contents['phpsysinfo']['fcgidPHPOK'] = true;
							$Alias_Contents['phpsysinfo']['fcgiaff'] .= ' #13 [FCGI -> PHP '.$Alias_Contents['phpsysinfo']['fcgidPHP'].']';
						}
					}
				}
			}
		}
	}
	//Get alias other than phpmyadmin, adminer or phpsysinfo
	$temp = glob($aliasDir.'*.conf');
	if(!empty($temp)) {
		foreach($temp as $key => $value) {
			if(stripos($value,'phpmyadmin') === false && stripos($value,'adminer') === false && stripos($value,'phpsysinfo') === false ) {
				$myalias = @file_get_contents($value);
				$aliasName = basename($value,'.conf');
				$Alias_Contents['alias'][] = $aliasName;
				$Alias_Contents[$aliasName]['aliaspath'] = $Alias_Contents[$aliasName]['path'] = 'unknown';
				if(preg_match('~^[ \t]*Alias[ \t]+(.+)[ \t]+"(.+)"~mi',$myalias,$matches) === 1) {
					$Alias_Contents[$aliasName]['aliaspath'] = $value;
					$Alias_Contents[$aliasName]['alias'] = $matches[1];
					$Alias_Contents[$aliasName]['path'] = $matches[2];
				}
				//Check if FCGI PHP used
				$Alias_Contents[$aliasName]['OK'] = true;
				$Alias_Contents[$aliasName]['fcgid'] = false;
				$Alias_Contents[$aliasName]['fcgidPHP'] = '0.0.0';
				$Alias_Contents[$aliasName]['fcgidPHPOK'] = false;
				$Alias_Contents[$aliasName]['fcgiaff'] = '';
				if(isset($c_ApacheDefine['PHPROOT'])) {
					if(preg_match("~^(#)?[ \t]*\<IfModule fcgid_module\>\r?$~m",$myalias,$comment) === 1) {
						if(!isset($comment[1])) {
							$Alias_Contents[$aliasName]['fcgid'] = true;
							//Search PHP version
							if(preg_match('~Define FCGIPHPVERSION "([0-9\.]+)"~im',$myalias,$matches_fcgi) === 1) {
								//PHP version used is $matches_fcgi[1]
								$Alias_Contents[$aliasName]['fcgidPHP'] = $phpFcgiVersionList[] = $matches_fcgi[1];
								$phpFcgiVersionListUsed[$matches_fcgi[1]][] = $aliasName;
								if(in_array($Alias_Contents[$aliasName]['fcgidPHP'],$phpVersionList)) {
									$Alias_Contents[$aliasName]['fcgidPHPOK'] = true;
									$Alias_Contents[$aliasName]['fcgiaff'] .= ' #13 [FCGI -> PHP '.$Alias_Contents[$aliasName]['fcgidPHP'].']';
								}
							}
						}
					}
				}
			}
		}
	}

	if(!empty($Alias_Contents['alias'])) $Alias_Contents['alias'] = array_unique($Alias_Contents['alias']);
	if(!empty($Alias_Contents['PMyAd'])) $Alias_Contents['PMyAd'] = array_unique($Alias_Contents['PMyAd']);
	if(!empty($Alias_Contents['PMyAdVer'])) $Alias_Contents['PMyAdVer'] = array_unique($Alias_Contents['PMyAdVer']);
	asort($Alias_Contents['PMyAdVer']);

	return $Alias_Contents;
}

function GetPhpLoadedExtensions($PhpExtVersion, $nbLines = 8,$modeWeb = true,$doReport = false){
	global $c_phpConfFile, $c_phpWebExe, $wampConf, $c_phpVersion, $c_ApacheDefine;
	$NoFcgiModule = '';
	if(!isset($c_ApacheDefine['PHPROOT'])) {
		$NoFcgiModule = color('red','PHP cannot be used in FCGI mode').color('blue',' because the Apache module fcgid_module is not loaded')."\n";
	}
	$message = ($doReport ? "--------------------------------------------------\n" : '');
	$message .= ($modeWeb) ? "<b>PHP Loaded Extensions - Function get_loaded_extensions()</b>\n" : color('blue',"-- PHP Loaded Extensions\n With function get_loaded_extensions()")."\n\n";
	foreach(array('apachemodule','clifcgi') as $value) {
		if($value == 'apachemodule') {
			if($PhpExtVersion != $c_phpVersion) continue;
			// For PHP used as Apache module
			$message .= ($modeWeb) ? '<u>PHP '.$PhpExtVersion.' -> Apache module'."</u>\n" : color('blue','-- For PHP '.$PhpExtVersion.' used as Apache module')."\n";
			$command = $c_phpWebExe.' -c '.$c_phpConfFile.' -r print(var_export(get_loaded_extensions(),true));';
		}
		elseif($value == 'clifcgi') {
			//For PHP used as CLI or FCGI
			$message .= ($modeWeb) ? "<u>PHP ".$PhpExtVersion." -> CLI - FCGI</u>\n".$NoFcgiModule : "\n".color('blue','-- For PHP '.$PhpExtVersion.' used as CLI or FCGI')."\n".$NoFcgiModule;
			$phpToCheckExt = $wampConf['installDir'].'/bin/php/php'.$PhpExtVersion.'/'.$wampConf['phpExeFile'];
			$command = $phpToCheckExt.' -r print(var_export(get_loaded_extensions(),true));';
		}
		$commandOK = true;
		$loaded_extensions = array();
		$output = proc_open_output($command);
		if(stripos($output,'Failed') !== false) {
			$commandOK = false;
			if(preg_match('~^Failed.*\r?$~mi',$output,$matches) === 1){
				$loaded_extensions[] = ($modeWeb) ? "<p style='color:red;'>".$matches[0]."</p>" : color('red',$matches[0]);
			}
		}
		if(preg_match('~(Notice|Warning|Deprecated|Parse).*$~mi',$output,$matches) === 1) {
			$PHP_Error = ($modeWeb) ? "<p style='color:red;'>".$matches[0]."</p>" : color('red',$matches[0])."\n";
			$message .= $PHP_Error;
		}
		if(preg_match('~^array \(.*\)$~sm',$output,$matches) === 1) {
			$output = $matches[0];
		}
		else {
			$commandOK = false;
			$loaded_extensions[] = ($modeWeb) ? "<p style='color:red;'>result of get_loaded_extensions() is not valid</p>" : color('red','result of get_loaded_extensions() is not valid');
		}
		if($commandOK) {
			$NewFileContents = '<?php'."\n\n".'$loaded_extensions = '.$output.';'."\n\n".'?>';
			write_file('loaded_extensions.php',$NewFileContents);
			$loaded_extensions = array();
			include 'loaded_extensions.php';
			unlink('loaded_extensions.php');
			unset($NewFileContents,$output);
			natcasesort($loaded_extensions);
		}
		$nbbyline = 0;
		if(count($loaded_extensions) > 0){
			foreach ($loaded_extensions as $extension) {
				$temp = str_pad(' '.$extension,14);
				$message .= ($modeWeb && $commandOK) ? str_replace(' ','&nbsp;',$temp) : $temp;
				if(++$nbbyline >= $nbLines) {
					$message .= "\n";
					$nbbyline = 0;
				}
			}
		}
		$message .= "\n";
		if($doReport && $value == 'apachemodule'){
			write_file($wampConf['installDir']."/wampConfReportTemp.txt",$message,false,false,'ab');
			exit;
		}
	}
	if($modeWeb) $message = nl2br($message,false);
	return $message;
}

function GetPhpVersionsUsage($modeWeb = true, $doReport = false){
	global $phpFcgiVersionListUsed, $fcgid_module_loaded, $Alias_Contents, $wampConf;
	//Verify if Apache module fcgid_module is loaded
	$fcgid_module_loaded = is_apache_var('${PHPROOT}');
	$message = ($doReport ? "--------------------------------------------------\n" : '');
	$virtualHost = check_virtualhost();
	GetAliasVersions();
	//All PHP versions with CLI and/or USED and/or FCGI added
	$Versions = ListAllVersions();
	$PHP_versions = $Versions['php'];
	foreach($phpFcgiVersionListUsed as $key => $value) $phpFcgiVersionListUsed[$key] = array_unique($phpFcgiVersionListUsed[$key]);
	//PHP versions usage
	$message .= ($modeWeb) ? "<b>-- Use of PHP versions</b>\n" : color('blue',"-- Use of PHP versions")."\n\n";
	foreach($PHP_versions as $PhpUsed) {
		$Used = $ModeFCGI = $usedTxt = false;
		$Usage = $UsageFCGI = '';
		if(strpos($PhpUsed,'CLI')) {
			$Usage .= " used for ".(($modeWeb) ? "<span style='color:red;'>Wampserver internal PHP scripts</span> " : color('red','Wampserver internal PHP scripts'));
			$PhpUsed = str_replace('CLI','',$PhpUsed);
			$Used = $usedTxt = true;
		}
		if(strpos($PhpUsed,'USED')) {
			$Usage .= ($usedTxt ? ' and ' : ' used as ').(($modeWeb) ? "<span style='color:green;'>APACHE module</span>" : color('green','APACHE module'));
			$PhpUsed = str_replace('USED','',$PhpUsed);
			$Used = $usedTxt = true;
		}
		if(strpos($PhpUsed,'FCGI')) {
			$Usage .= ($usedTxt ? ' and ' : ' used as ').(($modeWeb) ? "<span style='color:blue;'>FCGI</span>" : color('blue','FCGI'));
			$PhpUsed = str_replace('FCGI','',$PhpUsed);
			$Used = $ModeFCGI = true;
		}
		if(!$Used) {
			$Usage .= ' not used';
		}
		if($ModeFCGI) {
			$UsageFCGI .= ($modeWeb) ? "\n&nbsp;&nbsp;<span style='color:blue;'>FCGI used by:</span>\n" : color('blue',"\n  FCGI used by:\n");
			foreach($phpFcgiVersionListUsed[$PhpUsed] as $site) {
				if(in_array($site,$virtualHost['ServerName'])) {
					$temp = str_pad('  VirtualHost:',15);
					$UsageFCGI .= ($modeWeb) ? "<span style='color:#777;'>".str_replace(' ','&nbsp;',$temp)."</span>" : color('bold',$temp);
				}
				elseif(in_array($site,$Alias_Contents['alias'])){
					$temp = str_pad('  Alias:',15);
					$UsageFCGI .= ($modeWeb) ? "<span style='color:#777;'>".str_replace(' ','&nbsp;',$temp)."</span>" : color('bold',$temp);
				}
				else{
					$temp = str_pad('  Alias:',15);
					$UsageFCGI .= ($modeWeb) ? "<span style='color:#777;'>".str_replace(' ','&nbsp;',$temp)."</span>" : color('bold',$temp);
				}
				$UsageFCGI .= ($modeWeb) ? "<span style='color:blue;'>".$site."</span>\n" : color('blue',$site)."\n";
			}
			$UsageFCGI .= ($modeWeb) ? '' : color('reset');
		}
		$temp = str_pad(' PHP '.$PhpUsed,12);
		$message .= (($modeWeb) ? str_replace(' ','&nbsp;',$temp) : $temp).$Usage.$UsageFCGI."\n";
	}
	if(!$fcgid_module_loaded) {
		$temp = "\nApache module fcgid_module is not loaded. PHP cannot be used in FCGI mode.\n";
		$message .= ($modeWeb) ? "<span style='color:red;'>".$temp."</span>" : color('red',$temp);
	}
	if($doReport){
		write_file($wampConf['installDir']."/wampConfReportTemp.txt",$message,false,false,'ab');
		exit;
	}
	if($modeWeb) $message = nl2br($message,false);
	return $message;
}

// Function test of IPv6 support
function test_IPv6() {
	if(extension_loaded('sockets')) {
		//Create socket IPv6
		$socket = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP);
		if($socket === false) {
			$errorcode = socket_last_error() ;
			$errormsg = socket_strerror($errorcode);
			//echo "<p>Error socket IPv6: ".$errormsg."</p>\n" ;
			error_log("For information only: IPv6 not supported");
			return false;
		}
		else {
			//echo "<p>IPv6 supported</p>\n" ;
			socket_close($socket);
			error_log("For information only: IPv6 supported");
			return true;
		}
	}
	else {
		error_log("Extension PHP 'sockets' not loaded, cannot check support of IPv6");
		return false;
	}
}

$c_ApacheDefine = retrieve_apache_define($c_apacheDefineConf);

?>
