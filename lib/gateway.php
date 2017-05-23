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

class PhpCameraAlarmGateway {

	public static $cfg;	
	
	var $bin_name		='pcag.php';
	var $server_port	= 15000;
	var $server_ip		='';
	var $log_level		=6;
	var $log_php		=false;

	var $runmode		=array(
			'no-daemon' 	=> false,
			'help' 			=> false,
			'write-initd' 	=> false
	);
	var $runmode_help	=array(
			'no-daemon' 	=> "Don't run as Daemon",
			'help' 			=> "Shows this help",
			'write-initd' 	=> 'Build the /etc/init.d script. (then do "update-rc.d {$this->bin_name} defaults")'
	);

	// -----------------------------------------------------------------------------------
	public function __construct(){
	}

	// -----------------------------------------------------------------------------------
	public function Daemon($cfg,$debug=false){
		$this->_Init();
		$this->_LoadConfig($cfg);
		
		if($debug){
			$this->log_level	=7;
			$this->log_php		=true;
		}
		else{
			$this->log_level	=6;
			$this->log_php		=false;		
		}
		$this->_ProcessArguments();
		$this->_StartDaemon();
	}
	
	// -----------------------------------------------------------------------------------
	private function _Init(){
		require_once(dirname(__FILE__)."/pmss/sock/SocketServer.php");
		require_once(dirname(__FILE__)."/pmss/sock/SocketServerBroadcast.php");
		require_once 'System/Daemon.php';	// Include PEAR System/Daemon Class
	}

	// -----------------------------------------------------------------------------------
	private function _LoadConfig($cfg){
		$cfg['server_ip'] 	or  $cfg['server_ip'] 	=$this->_FindServerIP();
		$cfg['server_port'] or  $cfg['server_port'] =$this->server_port;
		$cfg['bin_name'] 	or  $cfg['bin_name'] 	=$this->bin_name;

		$this->server_ip	=$cfg['server_ip'];
		$this->server_port	=$cfg['server_port'];
		$this->bin_name		=$cfg['bin_name'];

		self::$cfg=$cfg;
	}

	// -----------------------------------------------------------------------------------
	private function _ProcessArguments(){
		global $argv;
		// Scan command line attributes for  allowed arguments 
		foreach ($argv as $k=>$arg) {
			if (substr($arg, 0, 2) == '--' && isset($this->runmode[substr($arg,  2)])) {
				$this->runmode[substr($arg, 2)] = true;
			}
		}

		// Help mode. Shows allowed argumentents  and quit 
		if ($this->runmode['help'] == true) {
			echo 'Usage: '.$argv[0].' [runmode]' . "\n";
			echo 'Available runmodes:' . "\n";
			foreach ($this->runmode as $runmod=>$val) {
				echo str_pad(" --$runmod",15) .": {$this->runmode_help[$runmod]}\n";
			}
			die();
		}
	}

	// -----------------------------------------------------------------------------------
	private function _StartDaemon(){
		$options = array(
			'appName' 				=> $this->bin_name,
			'appDir' 				=> dirname(dirname(__FILE__)),
			'appDescription'		=> 'IP Camera Alarms Server',
			'authorName'			=> 'Francois Dechery',
//			'authorEmail'			=> 'something@gmail.com',
			'sysMaxExecutionTime' 	=> '0',
			'sysMaxInputTime' 		=> '0',
			'sysMemoryLimit' 		=> '128M',
			//'usePEARLogInstance'	=> true,
			'useCustomLogHandler'	=> false,
			'logPhpErrors'			=> $this->log_php,
			'logVerbosity'			=> $this->log_level,
		//	'appRunAsGID' 			=> 1000,
		//	'appRunAsUID' 			=> 1000,
		);
		System_Daemon::setOptions($options);


		/* This program can also be run in  the forground with runmode --no-daemon */
		if ($this->runmode['no-daemon']) {
			System_Daemon::notice("-----------------------------------------");
			System_Daemon::warning("# Starting {$this->bin_name} in TEST mode");
		}
		else{
			set_time_limit (0);
			System_Daemon::start();
			System_Daemon::notice("-----------------------------------------");
			System_Daemon::info("# Starting {$this->bin_name} as daemon");
		}

		System_Daemon::info("# {$this->bin_name} is listening on {$this->server_ip}:{$this->server_port}");


		/* With the runmode --write-initd,  this program can automatically write a system startup file called:  'init.d' 
		This will make sure your daemon  will be started on reboot */

		if ($this->runmode['write-initd']) {
			if (($initd_location = System_Daemon::writeAutoRun()) === false) {
				System_Daemon::err('# Unable to write  init.d script');
			} 
			else {
				System_Daemon::notice('# sucessfully written startup script: ' . $initd_location);
			}
		}

		// Start SERVER ++++++++++++++++++++++++++++++++++++++++
		//$is_daemon 			= System_Daemon::isInBackground();

		// check required PHP extensions
		if( ! extension_loaded('sockets' ) ) {
			System_Daemon::err('# missing sockets extension (http://www.php.net/manual/en/sockets.installation.php)');
			exit(-1);
		}
		if( ! extension_loaded('pcntl' ) ) {
			System_Daemon::err('# missing PCNTL extension (http://www.php.net/manual/en/pcntl.installation.php)');
			exit(-1);
		}

		// ---------------------------------------------------------------------------------------
		$server = new \Sock\SocketServer($this->server_port,$this->server_ip);
		$server->showLog(false);
		$server->init();		
		$server->setConnectionHandler( array($this, 'OnConnect') );
		$server->listen();

		System_Daemon::stop();
	}	


	// -----------------------------------------------------------------------------------
	private function _FindServerIP(){
		$ifconfig = shell_exec('/sbin/ifconfig eth0');
		preg_match('/adr:([\d\.]+)/', $ifconfig, $match);
		$serv_ip=$match[1];
		//$serv_ip 		= gethostbyname(gethostname());
		//$serv_ip 		= gethostbyname(php_uname('n'));
		return  $serv_ip;
	}


	// ###################################################################################
	// ### STATIC ########################################################################
	// ###################################################################################

	// -----------------------------------------------------------------------------------
	static function OnConnect($client){

		// take care of parent/child --------
		$pid = pcntl_fork();
		if ($pid == -1) {
			System_Daemon::warning( "# Could not fork");
		}
		else if ($pid) {
			// parent process handles client
		}
		else {
			return;// new child handles next connection
		}	

		// process TCP client
		$read = '';
		$client_ip		=$client->getPeerAddress();
		$client_port	=$client->getPeerPort();
		System_Daemon::debug( "# [$client_ip] (PID=$pid) Connected from port $client_port");
			
		while( true ) {
			$read = $client->read();

			if( $read != '' ) {
				//reply to client
				$client->send( '[' . date( DATE_RFC822 ) . '] ' . $read  );
			}
			else {
				break;
			}

			// if( preg_replace( '/[^a-z]/', '', $read ) == 'exit' ) {break;}

			if( $read === null ) {
				System_Daemon::debug( "# [$client_ip] (PID=$pid) Disconnected NULL");
				return false;
			}
			else {
				//handle message
				System_Daemon::debug( "# [$client_ip] (PID=$pid) Received => " . trim($read) );
				self::processMessage($client_ip, $read);
				//break;	//needed?
			}
		}
		$client->close();
		System_Daemon::debug( "# [$client_ip] (PID=$pid) Disconnected" );
		exit(0);
	}


	// -----------------------------------------------------------------------------------
	static function processMessage($client_ip, $msg){
		if($type=self::$cfg['cameras'][$client_ip]['type']){
			System_Daemon::info( "# [$client_ip] parsing '$type' message ...");
			require_once(dirname(__FILE__)."/process_{$type}.php");
			$class ="PhpCameraAlarmProcess_{$type}";
			$obj = new $class(self::$cfg);
			$obj->process($client_ip,$msg);
		}
		else{
			System_Daemon::debug( "# [$client_ip] Not processed ! ");
		}
	}

}
?>