#!/usr/bin/php -q
<?php
/*
	Inspired by	http://kvz.io/blog/2009/01/09/create-daemons-in-php/
	--------------------------------------------------------------------------------------

	PEAR Must be installed for this to work! 
	Under Debian:
		apt-get install php-pear
		pear install System_Daemon 
		pear install Log

*/

// Make it possible to test in  source directory This is for PEAR developers only
//ini_set('include_path',  ini_get('include_path').':..');

// Load config #############################################################################
$dir=dirname(__FILE__);
$file_config		="config.php";
$file_config_def	="config.default.php";

if(file_exists($file_config)){
	require($file_config);
}
else{
	require($file_config_def);
}

// Start #################################################################################
require_once('lib/gateway.php');
$obj= new PhpCameraAlarmGateway();
//$obj->Daemon($cfg);
$obj->Daemon($cfg, true); //to debug

?>