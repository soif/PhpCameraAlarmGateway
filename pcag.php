#!/usr/bin/php -q
<?php
/*
	PhpCameraAlarmGateway
	https://github.com/soif/PhpCameraAlarmGateway
	Copyright (C) 2017  Francois Dechery

	LICENCE: ###########################################################
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
	#####################################################################
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