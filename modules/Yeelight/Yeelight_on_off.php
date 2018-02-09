<?
//========= метод on_off (включение/выключение) ===================
include_once(DIR_MODULES.'Yeelight/Yeelight_library.php');
$Location = $this->getProperty('Location');
$id = $this->getProperty('id');
$status = $this->getProperty('status');
if ($status) {$power = 'on'; }
if (!$status) {$power = 'off'; }
$data = [
"Location" => $Location,
"id" => $id, 
];
$socketFactory = new Factory();
$bulbFactory = new BulbFactory($socketFactory);
$bulb = $bulbFactory->create($data);
$res = $bulb->setPower($power, 'smooth', 1000); //включить/выключить
if (array_key_exists('result', $res)) {
    $result = $res [result][0];
    //переменная содержит ответ от лампочки
    }
if (array_key_exists('error', $res)) {
    $result = $res [error][message].". Code ".$res [error][code];
	$model=$this->getProperty('model');
    DebMes("Ошибка включения/выключения  Yeelight устройства ".$Location.", модель: ".$model);
    //DebMes("Ошибка включения/выключения Yeelight: ".$result);
    }
