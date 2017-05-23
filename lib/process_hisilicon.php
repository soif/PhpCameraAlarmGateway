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

require_once(dirname(__FILE__).'/process.php');

class PhpCameraAlarmProcess_hisilicon extends PhpCameraAlarmProcess {

	public function process($ip, $msg){

		//decode message
		$msg=trim($msg);
		$msg=preg_replace('#^[^{]+#','',$msg);
		//System_Daemon::debug( "[$ip] process : Cleaned Message=$msg");
		$arr=json_decode($msg,true);

		// triggers defaults
		$p=$this->cfg['cameras'][$ip];
		isset($p['event'])	and $trig_event		=$p['event']	or $trig_event	='MotionDetect';
		isset($p['status'])	and $trig_status	=$p['status']	or $trig_status	='Start';

		if(is_array($arr)){
			//System_Daemon::debug( "# [$ip] JSON ".print_r($arr,true));
			if($event=$arr['Event'] ){
				if($event == $trig_event){
					$status=$arr['Status'];
					if($status==$trig_status){
						$this->performActions($ip);			
					}
					else{
						System_Daemon::debug( "# [$ip] process canceled : ignored '$status' status");			
					}
				}
				else{
					System_Daemon::debug( "# [$ip] process canceled : unknown '{$event}' event");			
				}
			}
			else{
				System_Daemon::debug( "# [$ip] process canceled : not an event");			
			}
		}
		else{
			System_Daemon::debug( "# [$ip] process canceled : Not a valid JSON");			
		}
	}
}
?>