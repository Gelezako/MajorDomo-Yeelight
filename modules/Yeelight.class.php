<?php
/**
* Yeelight 
* @package project
* @author Alex Sokolov <admin@gelezako.com>
* @copyright http://blog.gelezako.com/ (c)
* @version 0.1 (wizard, 16:12:20 [Dec 01, 2017])
*/

include_once(DIR_MODULES.'Yeelight/Yeelight_library.php');
class Yeelight extends module {
/**
* Yeelight
*
* Module class constructor
*
* @access private
*/
function Yeelight() {
  $this->name="Yeelight";
  $this->title="Устройства Yeelight";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
}


function usual(&$out) {
 $this->admin($out);
 
}
 function processCycle() {
  //to-do
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
 //Скрипт для MD, поиск устройств в сети, создание объектов MD, создание и заполнение свойств объектов MD

//Создание класса MD Yeelight, классовых свойств и методов
addClass('Yeelight');
addClassProperty('Yeelight', 'id', 0); //Создаёт свойство класса и указывает, что необходимо хранить историю значений 0 дней
addClassProperty('Yeelight', 'model', 0);
addClassProperty('Yeelight', 'status', 0);
addClassProperty('Yeelight', 'model', 0);
addClassProperty('Yeelight', 'bright', 0);
addClassProperty('Yeelight', 'Location', 0);
addClassProperty('Yeelight', 'name', 0);
addClassProperty('Yeelight', 'support', 0);
addClassProperty('Yeelight', 'rgb', 0);
addClassProperty('Yeelight', 'hue', 0);
addClassProperty('Yeelight', 'sat', 0);
addClassProperty('Yeelight', 'ct', 0);
addClassMethod('Yeelight', 'on_off',"require(DIR_MODULES.'Yeelight/Yeelight_on_off.php');");
addClassMethod('Yeelight', 'set_bright',"require(DIR_MODULES.'Yeelight/Yeelight_set_bright.php');");
addClassMethod('Yeelight', 'set_name',"require(DIR_MODULES.'Yeelight/Yeelight_set_name.php');");

//=======================================
//Создание объектов класса
//    Поиск устройств
$client = new YeelightClient();
$bulbList_prop = $client->search_prop();
foreach ($bulbList_prop as $bulb) {
 //получаем из массива bulbList_prop характеристики устройств
 $id = trim($bulb[id]);
 $Location = trim($bulb[Location]);
 $model = trim($bulb[model]); 
 $name =  trim($bulb[name]); 
 $COLOR_MODE = trim($bulb[color_mode]);
 $powerTXT = $bulb[power];
 if ($powerTXT == "on") $power = 1;
 if ($powerTXT = "off") $power = 0;
 $bright = trim($bulb[bright]);
 $ct = trim($bulb[ct]);
 $rgb = dechex($bulb[rgb]);
 $hue = trim($bulb[hue]);
 $sat = trim($bulb[sat]);
 $support = trim($bulb[support]); 
 //получаем список объектов класса
 $objects=getObjectsByClass("Yeelight");
 $searhID = 0;
 foreach($objects as $obj) {
  if ((gg($obj['TITLE'].".id")) == $id){
   $searhID += 1;   
  }     
 }
 if ($searhID) { 
  say("Устройство Илайт уже установлено.",2);
 } else {  
  if ($name) {
   $objName = $name;
  } else {
   $objName = $model."_".$id; 
  }
  addClassObject('Yeelight', $objName); //создаем объект с новым id
  //заполняем классовые свойства объекта
  setGlobal($objName.".id",$id);
  setGlobal($objName.".model",$model);
  setGlobal($objName.".status",$power);
  setGlobal($objName.".bright",$bright);
  setGlobal($objName.".Location",$Location);
  setGlobal($objName.".name",$name);
  setGlobal($objName.".support",$support);
   
  //создаем свойства объекта с учетом специфики ламп
  if ($model =="stripe" OR $model =="color") {
    
   $result = strpos ($support, 'set_rgb');
   if ($result) {  
    setGlobal($objName.".rgb",$rgb);
    addClassMethod('Yeelight', 'set_rgb',"require(DIR_MODULES.'Yeelight/Yeelight_set_rgb.php');");
    //say("Cоздается свойство РГБ",2); //GRB
   }
   
   $result = strpos ($support, 'set_ct_abx');
   if ($result) {
    setGlobal($objName.".ct",$ct);
    addClassMethod('Yeelight', 'set_ct',"require(DIR_MODULES.'Yeelight/Yeelight_set_ct.php');");
	//say("Cоздается свойство cи тэ",2);//CT
   }
   
   $result = strpos ($support, 'set_hsv');
   if ($result) {
    setGlobal($objName.".hue",$hue);
    setGlobal($objName.".sat",$sat);
    addClassMethod('Yeelight', 'set_hsv',"require(DIR_MODULES.'Yeelight/Yeelight_set_hsv.php');");
	//say("Cоздается свойство аш эс вэ",2); //HSV
   }
  } elseif ($model =="mono") {
     say("Найдено новое устройство: Илайт белая лампочка",2);
  }
   if($model="stripe")say("Найдено новое устройство: Илайт диодная лента",2);
   if($model="color")say("Найдено новое устройство: Илайт цветная лампочка",2);   
 }
}

  parent::install();
 }
 
 public function uninstall()
   {
      SQLExec("delete from pvalues where property_id in (select id FROM properties where object_id in (select id from objects where class_id = (select id from classes where title = 'Yeelight')))");
      SQLExec("delete from properties where object_id in (select id from objects where class_id = (select id from classes where title = 'Yeelight'))");
      SQLExec("delete from objects where class_id = (select id from classes where title = 'Yeelight')");
      SQLExec("delete from classes where title = 'Yeelight'");
      
      parent::uninstall();
   }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgRGVjIDAxLCAyMDE3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
