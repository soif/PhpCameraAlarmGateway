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

class PhpCameraAlarmProcess {
	var $cfg;

	// -----------------------------------------------------------------------------------
	public function __construct($cfg){
		$this->cfg=$cfg;
	}
	

	// -----------------------------------------------------------------------------------
	protected function performActions($ip){
		$actions= $this->cfg['cameras'][$ip]['actions'];
				
		if(is_array($actions) and count($actions) ){
			System_Daemon::debug( "# [$ip] processing actions...");
			foreach($actions as $act_type => $params){
				if(preg_match("#^url_#",$act_type)){
					System_Daemon::info( "# [$ip] ACTION=custom_url , url = {$params['url']}");						
					$this->action_custom_url($params['url']);
				}
				else{
					$method="action_$act_type";
					if(method_exists($this,$method)){
						$txt_id="";
						if(isset($params['id'])){
							$txt_id=", id={$params['id']} ";
						}
						System_Daemon::info( "# [$ip] ACTION=$act_type {$txt_id}");						
						$this->$method($ip,$params);
					}
					else{
						System_Daemon::warning( "# [$ip] invalid '$act_type' action ");
					}
				}
			}
		}
		else{
			$log=". Actions=".print_r($actions,true);
			System_Daemon::warning( "# [$ip] NO action to process$log");
		}
	}

	// -----------------------------------------------------------------------------------
	private function callUrl($url,$timeout=1){
		if($url){
			$options = array(
				'http' => array(
					'user_agent'=> "{$this->cfg['bin_name']} (PhpCameraAlarmGateway)",
					'timeout'	=> $timeout
				),
			  );
			$context  = stream_context_create($options);

			//add timestamp to traverse proxies
			$query = parse_url($url, PHP_URL_QUERY);
			if ($query) {
				$url .= '&';
			}
			else {
				$url .= '?';
			}
			$url.="timestamp=".time();//.microtime();
			
			$result=file_get_contents($url,false,$context);
			System_Daemon::debug( "# ==========> called : $url");
			return true;					
		}
		else{
			System_Daemon::err( "# ERR! callUrl canceled : No URL provided");			
		}
	}
	

	// -----------------------------------------------------------------------------------
	private function action_custom_url($ip,$url){
		return $this->callUrl($url);
	}


	// -----------------------------------------------------------------------------------
	private function action_zm_api_alarm($ip,$p){
		$url=$this->cfg['urls']['zoneminder']."/api/monitors/alarm/id:{$p['id']}/command:on.json";
		return $this->callUrl($url);
	}


	// -----------------------------------------------------------------------------------
	private function action_zm_trigger($ip,$p){
	
		if(!$id=$p['id']){
			System_Daemon::err( "# ERR! zm_trigger action canceled : No ID provided");
			return false;
		}

		// set action
		if(isset($p['action'])){
			$action=$p['action'];
		}
		else{
			$action='on+';
			isset($p['dur']) and $dur=$p['dur'] or $dur=5;
			$action .=$dur;
		}
		// set others params defaults
		isset($p['score'])	and $score	=$p['score']	or $score	=1;
		isset($p['cause'])	and $cause	=$p['cause']	or $cause	='CamMotion';		// max 32
		isset($p['text'])	and $text	=$p['text']		or $text	='Detection by Camera';	// max 255
		isset($p['show'])	and $show	=$p['show']		or $show	='';	//max 32

		$message="$id|$action|$score|$cause|$text|$show\n";
		/*
		$sock = socket_create(AF_INET, SOCK_RAW, SOL_TCP);
		$msg = "Ping !";
		$len = strlen($msg);
		socket_sendto($sock, $msg, $len, 0, '127.0.0.1', 1223);
		socket_close($sock);
		*/
		$host=parse_url($this->cfg['urls']['zoneminder'], PHP_URL_HOST);
		$fp = fsockopen($host, 6802, $errno, $errstr, 3);
		if (!$fp) {
			System_Daemon::err( "# ERR! socket failed : ($errno) $errstr");
		}
		else {
    		fputs($fp, $message);
			System_Daemon::debug( "# ==========> sent : ".trim($message));
			//socket_close($sock);
    		fclose($fp);
			return true;
		}
	}


	// -----------------------------------------------------------------------------------
	private function action_domoticz($ip,$p){
		$url=$this->cfg['urls']['domoticz']."/json.htm?type=command&param=switchlight&idx={$p['id']}&switchcmd=On";
		return $this->callUrl($url,2);
	}



}
?>