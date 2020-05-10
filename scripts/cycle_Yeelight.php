<?php
chdir(dirname(__FILE__) . '/../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");
set_time_limit(0);
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();
include_once(DIR_MODULES . 'Yeelight/Yeelight.class.php');
$Yeelight_module = new Yeelight();
$Yeelight_module->getConfig();

$objects=getObjectsByClass("Yeelight");
if (!is_array($objects))
   exit; // no devices added -- no need to run this cycle

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

$cycle_debug = true;
$classname="Yeelight";
$latest_check=0;
$rcv_count = 0;
$latest_data_received = time();
$check_period=5; // every 5 seconds
$report_period = 10;//in sec


$objects=getObjectsByClass("Yeelight");
//print_r($objects);
foreach($objects as $obj) {
    $objName = $obj['TITLE'];
    $id = gg($objName.".id");
    $Location = gg($objName.".Location");    
    $data = [
    "Location" => $Location,
    "id" => $id, 
    ];    
    $socketFactory = new Factory();
    $bulbFactory = new BulbFactory($socketFactory);
    $bulbs[$objName] = $bulbFactory->create($data);
    $bulbs[$objName]->setBlocking(false);
}
while (1)
{
	 if ((time() - $latest_check) >= $check_period) {
      $latest_check = time();
      setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   }
////CODE
foreach($bulbs as $k => $bulb) {       
		$data=$bulb->recv();
		if($data)
		{
			DebMes("Get from lamp:".json_encode($data),$classname);
			if($data['method']=='props' && is_array($data['params']))
			{
				foreach ($data['params'] as $param => $param_value)
				{
					DebMes("sg('".$k.".".$param."','".$param_value."')",$classname);
					if($param == 'main_power')
					{
					if($param_value =='on')sg($k.".status",1);
					if($param_value =='off')sg($k.".status",0);
					
					}
					sg($k.".".$param,$param_value);
				}
		$rcv_count++;	
		}
		else {echo "I receive:\n";print_r($data);}
		}
}
		
////
  if ($cycle_debug) {
      if ((time() - $latest_report) >= $report_period) {
         $latest_report = time();
         echo date('H:i:s') . " Received messages count = $rcv_count" . PHP_EOL;
         $rcv_count=0;
      }
   }    
   if (file_exists('./reboot') || isset($_GET['onetime'])) {
      $db->Disconnect();
      echo date('H:i:s') . ' Stopping by command REBOOT or ONETIME ' . basename(__FILE__) . PHP_EOL;
      exit;
   }
   
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));


