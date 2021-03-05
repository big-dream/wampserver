<?php
//3.0.6
require 'config.inc.php';

echo "\n\n\n\n\n\n\n\n\n\n\n\n\nEnter your alias.\nFor example,\n\n'test'\n\nwould create an alias for the url\n
http://localhost/test/\n: ";
$newAliasDir = trim(fgets(STDIN));
$newAliasDir = trim($newAliasDir,'/\'');
if (is_file($aliasDir.$newAliasDir.'.conf')) {
 echo "\n\nAlias already exists. Press Enter to exit...";
 trim(fgets(STDIN));
 exit();
}
if(empty($newAliasDir)) {
  echo "\n\nAlias not created. Press Enter to exit...";
  trim(fgets(STDIN));
  exit();
}
echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
Enter the destination path of your alias.\nFor example,\n\n'c:/test/'\n\n
would make http://localhost/".$newAliasDir."/ point to\n\n
c:/test/\n:";
$newAliasDest = trim(fgets(STDIN));
$newAliasDest = trim($newAliasDest,'\'');
if($newAliasDest[strlen($newAliasDest)-1] != '/')
	$newAliasDest .= '/';
if(!is_dir($newAliasDest)) {
	echo "\nThis directory doesn\'t exist.\n";
  $newAliasDest = '';
}
if(empty($newAliasDest)) {
	echo "\n\nAlias not created. Press Enter to exit...\n";
  trim(fgets(STDIN));
  exit();
}

$newConfFileContents = <<< ALIASEOF
Alias /${newAliasDir} "${newAliasDest}"

<Directory "${newAliasDest}">
	Options +Indexes +FollowSymLinks +MultiViews
  AllowOverride all
	Require local
</Directory>

ALIASEOF;

file_put_contents($aliasDir.$newAliasDir.'.conf',$newConfFileContents) or die ("unable to create conf file");
echo "\n\nAias created. Press Enter to exit...";
trim(fgets(STDIN));
exit();

?>