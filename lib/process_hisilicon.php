<?php
require_once(dirname(__FILE__).'/process.php');

class PhpCameraAlarmProcess_hisilicon extends PhpCameraAlarmProcess {

	function process($ip, $msg){
		//decode message
		$msg=trim($msg);
		$msg=preg_replace('#^[^{]+#','',$msg);
		//System_Daemon::debug( "[$ip] process : Cleaned Message=$msg");
		$arr=json_decode($msg,true);

		// triggers
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