<?php
/*
	CONFIGURATION
	Copy this file to config.php (ignpored by git), then set you own configuration

*/
// Global #############################################################################
$cfg['server_port']	=15000;				//set your the port to listen to
//$cfg['server_ip']	='10.1.100.101';	//set your server IP if not auto detected correctly


// URLs of servers used in camera actions ############################################ 
$cfg['urls']['zoneminder']		='http://zm.lo.lo/zm';
$cfg['urls']['domoticz']		='http://domoticz.lo.lo:8080';


// IP CAMERA to support ############################################################## 
/*
For each camera the format is : $cfg['cameras'][CAMERA_IP]=array();
The array should at list contains: 
	- ['type'] 				=> (string)	: 'hisilicon', the method used to parse the message
	- ['actions'][ACTION] 	=> (array) : the list of actions to performs , where ACTION = 'zm_trigger'|'zm_api_alarm'|'domoticz'

EXAMPLES :
*/

// 10.1.202.51 (macbook to test) =============================================================================
/* Test command
	echo '???{ "Address" : "0x01D0010A", "Channel" : 0, "Descrip" : "", "Event" : "MotionDetect", "SerialID" : "47a880a97d944c37", "StartTime" : "2017-05-21 12:02:10", "Status" : "Start", "Type" : "Alarm" }' | nc 10.1.100.101 15000
*/

//$cfg['cameras']['10.1.202.51']['type']= 'hisilicon';

// ACTION zm_trigger : triggers ZonzMinder (OPT_TRIGGERS should be on in zm) -------------------------------
//$cfg['cameras']['10.1.202.51']['actions']['zm_trigger']['id']		= '1';		// zoneminder 'monitor' id to trigger

// these are the default value when NOT set
//$cfg['cameras']['10.1.202.51']['actions']['zm_trigger']['event']	= 'MotionDetect';	//event type that triggers the action
//$cfg['cameras']['10.1.202.51']['actions']['zm_trigger']['status']	= 'Start';			//status value that triggers the action
//$cfg['cameras']['10.1.202.51']['actions']['zm_trigger']['action']	= 'on';				// action to perform: can be on, on+X, off... (see zm docs)
//$cfg['cameras']['10.1.202.51']['actions']['zm_trigger']['dur']	= '3';				// when previous 'action' is not set, use this to change the duration
//$cfg['cameras']['10.1.202.51']['actions']['zm_trigger']['score']	= '123';			// score in ZM
//$cfg['cameras']['10.1.202.51']['actions']['zm_trigger']['cause']	= 'MOTION';			// cause in ZM (max 32 chars)
//$cfg['cameras']['10.1.202.51']['actions']['zm_trigger']['text']	= 'TEXT';			// description in ZM (max 255 chars)
//$cfg['cameras']['10.1.202.51']['actions']['zm_trigger']['show']	= 'SHOW';			// Text incrusted in ZM image (max 32 chars). Seems to be ignored by ZM...

// ACTION zm_api_alarm : set Alarm On using ZonzMinder API  -------------------------------
//$cfg['cameras']['10.1.202.51']['actions']['zm_api_alarm']['id']			= '1';	// zoneminder 'monitor' id to trigger

// ACTION domoticz : set the switch to On using domoticz API  -------------------------------
//$cfg['cameras']['10.1.202.51']['actions']['domoticz']['id']	= '241';

// ACTION url_XXXXX : trigger a custom URL  -------------------------------
//$cfg['cameras']['10.1.202.51']['actions']['url_custom']['url']			= 'http://www.whaterever:port/path?query';	// custom URL




// 10.1.208.1 (door camera) =============================================================================
$cfg['cameras']['10.1.208.1']['type']= 'hisilicon';
$cfg['cameras']['10.1.208.1']['actions']['zm_trigger']['id']= '1';
$cfg['cameras']['10.1.208.1']['actions']['domoticz']['id']	= '241';

// 10.1.208.2 (hall camera) =============================================================================
$cfg['cameras']['10.1.208.2']['type']= 'hisilicon';
$cfg['cameras']['10.1.208.2']['actions']['zm_api_alarm']['id']	= '2';
$cfg['cameras']['10.1.208.2']['actions']['domoticz']['id']	= '242';

// 10.1.208.3 (garden camera) =============================================================================
$cfg['cameras']['10.1.208.3']['type']= 'hisilicon';
$cfg['cameras']['10.1.208.3']['actions']['url_custom1']['url']	= 'http://192.168.1.0/switchLightOn?Night=1';
$cfg['cameras']['10.1.208.2']['actions']['domoticz']['id']		= '243';

?>