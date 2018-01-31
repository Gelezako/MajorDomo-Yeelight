<?
//=======метод set_rgb (установка цвета RGB)======================
include_once(DIR_MODULES.'Yeelight/Yeelight_library.php');
$Location = $this->getProperty('Location');
$id = $this->getProperty('id');
$rgb = hexdec($this->getProperty('rgb'));
$data = [
"Location" => "$Location",
"id" => "$id", 
];
$socketFactory = new Factory();
$bulbFactory = new BulbFactory($socketFactory);
$bulb = $bulbFactory->create($data);
$res = $bulb->setRgb($rgb, 'smooth', 1000);  //установить цвет
if (array_key_exists('result', $res)) {
    $result = $res [result][0];
    //переменная содержит ответ от лампочки
    }
if (array_key_exists('error', $res)) {
    $result = $res [error][message].". Code ".$res [error][code];
    DebMes("Ошибка Yeelight: ".$result);
    }
