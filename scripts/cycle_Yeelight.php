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
$tmp = SQLSelectOne("SELECT ID FROM  LIMIT 1");
if (!$tmp['ID'])
   exit; // no devices added -- no need to run this cycle
echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

//-- вставка от cahek2202
$socket=['IP'=>'192.168.0.108','PORT'=>55443];
$IP = $socket['IP'];
$port = $socket['PORT'];
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>0,"usec"=>10000));
$result = socket_connect($socket, $IP, $port);
$read_buf = '';
//-- конец вставки

$latest_check=0;
$checkEvery=5; // poll every 5 seconds
while (1)
{
   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   
   //-- вставка от cahek2202
        $res = socket_recv($socket, $read_buf, 2048, 0);
		$res=json_decode($read_buf,true);
		$status=$res["params"]["power"];
		$bright=$res["params"]["bright"];
		$ct=$res["params"]["ct"];
		$rgb=$res["params"]["rgb"];
		if($status){
			if($status=='on'){sg('mono_0x0000000003360b8319.status',1);}
			if($status=='off'){sg('mono_0x0000000003360b8319.status',0);}
			}
		if($bright){
			sg('mono_0x0000000003360b8319.bright',$bright);
			}
		if($ct){
			sg('mono_0x0000000003360b8319.ct',$ct);
			}
   //-- конец вставки
   
   if ((time()-$latest_check)>$checkEvery) {
    $latest_check=time();
    echo date('Y-m-d H:i:s').' Polling devices...';
    $Yeelight_module->processCycle();
   }
   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }
   sleep(1);
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));


