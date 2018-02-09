<?
//=======метод set_name(установка имени)=======================
include_once(DIR_MODULES.'Yeelight/Yeelight_library.php');
$Location = $this->getProperty('Location');
$id = $this->getProperty('id');
$name = $this->getProperty('name');
$data = [
"Location" => "$Location",
"id" => "$id", 
];
$socketFactory = new Factory();
$bulbFactory = new BulbFactory($socketFactory);
$bulb = $bulbFactory->create($data);
$res = $bulb->setName($name);  //установить имя
if (array_key_exists('result', $res)) {
    $result = $res [result][0];
    //переменная содержит ответ от лампочки
    }
if (array_key_exists('error', $res)) {
    $result = $res [error][message].". Code ".$res [error][code];
	$model=$this->getProperty('model');
    DebMes("Ошибка при установлении имени Yeelight устройства ".$Location.", модель: ".$model);
    //DebMes("Ошибка при установке имени Yeelight: ".$result);
    }
