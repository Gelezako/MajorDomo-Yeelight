<?
//========= метод set_bright (установка яркости) ====================
include_once(DIR_MODULES.'Yeelight/Yeelight_library.php');
$Location = $this->getProperty('Location');
$id = $this->getProperty('id');
$bright = (int) ($this->getProperty('bright'));
$data = [
"Location" => $Location,
"id" => $id, 
];
$socketFactory = new Factory();
$bulbFactory = new BulbFactory($socketFactory);
$bulb = $bulbFactory->create($data);
$res = $bulb->setBright($bright, 'smooth', 1000);  //установить яркость
if (array_key_exists('result', $res)) {
    $result = $res [result][0];
    //переменная содержит ответ от лампочки
    }
if (array_key_exists('error', $res)) {
    $result = $res [error][message].". Code ".$res [error][code];
    DebMes("Ошибка Yeelight: ".$result);
    }
