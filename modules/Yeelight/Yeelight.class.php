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

public function SearchDevices(){
//=======================================
//Создание объектов класса
//    Поиск устройств
$client = new YeelightClient();
$bulbList_prop = $client->search_prop();
foreach ($bulbList_prop as $bulb) {
 //получаем из массива bulbList_prop характеристики устройств
 $id = trim($bulb['id']);
 $Location = trim($bulb['Location']);
 $model = trim($bulb['model']); 
 $name =  trim($bulb['name']); 
 $COLOR_MODE = trim($bulb['color_mode']);
 $powerTXT = $bulb['power'];
 if ($powerTXT == "on") { $power = 1; }
 if ($powerTXT = "off") { $power = 0; }
 $bright = trim($bulb['bright']);
 $ct = trim($bulb['ct']);
 $rgb = dechex($bulb['rgb']);
 $hue = trim($bulb['hue']);
 $sat = trim($bulb['sat']);
 $support = trim($bulb['support']); 
 
 //получаем список объектов класса
 $objects=getObjectsByClass("Yeelight");
 $searhID = 0;
 foreach($objects as $obj) {
  if ((gg($obj['TITLE'].".id")) == $id){
   $searhID += 1;   
  }     
 } 
 if (!$searhID){  
  if ($name) {
   $objName = $name;
  } 
else {
	$objName = $model."_".$id;
    //$objName = $model."_".$id.rand();
    if($model =="stripe" OR $model =="strip" OR $model =="stripe1" OR $model =="strip1"){
		$objDescription = array('Светодиодная лента');
		$rec = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe("Yeelight") . "'");
		if (!$rec['ID']) {
        $rec = array();
        $rec['TITLE'] = $objName;
        $rec['DESCRIPTION'] = $objDescription;
        $rec['ID'] = SQLInsert('classes', $rec);
		}
		for ($i = 0; $i < count($objName); $i++) {
        $obj_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($objName) . "'");
        if (!$obj_rec['ID']) {
            $obj_rec = array();
            $obj_rec['CLASS_ID'] = $rec['ID'];
            $obj_rec['TITLE'] = $objName;
            $obj_rec['DESCRIPTION'] = $objDescription[$i];
            $obj_rec['ID'] = SQLInsert('objects', $obj_rec);
        }
		}
	}
	
	if($model=="color" || $model=="color1") {$objDescription = array('Цветная лампочка');
	 $rec = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe("Yeelight") . "'");
		if (!$rec['ID']) {
			$rec = array();
			$rec['TITLE'] = $objName;
			$rec['DESCRIPTION'] = $objDescription;
			$rec['ID'] = SQLInsert('classes', $rec);
		}
		for ($i = 0; $i < count($objName); $i++) {
			$obj_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($objName) . "'");
			if (!$obj_rec['ID']) {
				$obj_rec = array();
				$obj_rec['CLASS_ID'] = $rec['ID'];
				$obj_rec['TITLE'] = $objName;
				$obj_rec['DESCRIPTION'] = $objDescription[$i];
				$obj_rec['ID'] = SQLInsert('objects', $obj_rec);
			}
		}
	}
	if($model=="mono" || $model=="mono1") {$objDescription = array('Белая лампочка');
	 $rec = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe("Yeelight") . "'");
		if (!$rec['ID']) {
			$rec = array();
			$rec['TITLE'] = $objName;
			$rec['DESCRIPTION'] = $objDescription;
			$rec['ID'] = SQLInsert('classes', $rec);
		}
		for ($i = 0; $i < count($objName); $i++) {
			$obj_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($objName) . "'");
			if (!$obj_rec['ID']) {
				$obj_rec = array();
				$obj_rec['CLASS_ID'] = $rec['ID'];
				$obj_rec['TITLE'] = $objName;
				$obj_rec['DESCRIPTION'] = $objDescription[$i];
				$obj_rec['ID'] = SQLInsert('objects', $obj_rec);
			}
		}
	}
 
  if($model=="ceiling" || $model == "ceiling1" || $model == "ceiling2" || $model == "ceiling3" || $model == "ceiling4" || "ceiling14") {
	$objDescription = array('Потолочный светильник');
	$rec = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe("Yeelight") . "'");
		if (!$rec['ID']) {
			$rec = array();
			$rec['TITLE'] = $objName;
			$rec['DESCRIPTION'] = $objDescription;
			$rec['ID'] = SQLInsert('classes', $rec);
		}
		for ($i = 0; $i < count($objName); $i++) {
			$obj_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($objName) . "'");
			if (!$obj_rec['ID']) {
				$obj_rec = array();
				$obj_rec['CLASS_ID'] = $rec['ID'];
				$obj_rec['TITLE'] = $objName;
				$obj_rec['DESCRIPTION'] = $objDescription[$i];
				$obj_rec['ID'] = SQLInsert('objects', $obj_rec);
			}
		}
	}
 
   if($model=="bslamp" || $model=="bslamp1") {
	$objDescription = array('Прикроватный ночник');
	$rec = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe("Yeelight") . "'");
		if (!$rec['ID']) {
			$rec = array();
			$rec['TITLE'] = $objName;
			$rec['DESCRIPTION'] = $objDescription;
			$rec['ID'] = SQLInsert('classes', $rec);
		}
		for ($i = 0; $i < count($objName); $i++) {
			$obj_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($objName) . "'");
			if (!$obj_rec['ID']) {
				$obj_rec = array();
				$obj_rec['CLASS_ID'] = $rec['ID'];
				$obj_rec['TITLE'] = $objName;
				$obj_rec['DESCRIPTION'] = $objDescription[$i];
				$obj_rec['ID'] = SQLInsert('objects', $obj_rec);
			}
		}
	}
 
    if($model=="lamp" || $model=="lamp1") {
	$objDescription = array('Настольная лампа');
	$rec = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe("Yeelight") . "'");
		if (!$rec['ID']) {
			$rec = array();
			$rec['TITLE'] = $objName;
			$rec['DESCRIPTION'] = $objDescription;
			$rec['ID'] = SQLInsert('classes', $rec);
		}
		for ($i = 0; $i < count($objName); $i++) {
			$obj_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($objName) . "'");
			if (!$obj_rec['ID']) {
				$obj_rec = array();
				$obj_rec['CLASS_ID'] = $rec['ID'];
				$obj_rec['TITLE'] = $objName;
				$obj_rec['DESCRIPTION'] = $objDescription[$i];
				$obj_rec['ID'] = SQLInsert('objects', $obj_rec);
			}
		}
	}
 
}
  //addClassObject('Yeelight', $objName); //создаем объект с новым id
  //заполняем классовые свойства объекта
  setGlobal($objName.".id",$id);
  setGlobal($objName.".model",$model);
  setGlobal($objName.".status",$power);
  setGlobal($objName.".bright",$bright);
  setGlobal($objName.".Location",$Location);
  setGlobal($objName.".name",$name);
  setGlobal($objName.".support",$support);
   
  //создаем свойства объекта с учетом специфики ламп
  if ($model =="stripe" OR $model =="strip" OR $model =="stripe1" OR $model =="strip1") {    
   $result = strpos ($support, 'set_rgb');
   if ($result) {  
    setGlobal($objName.".rgb",$rgb);
   }
   
   $result = strpos ($support, 'set_ct_abx');
   if ($result) {
    setGlobal($objName.".ct",$ct);
   }
   
   $result = strpos ($support, 'set_hsv');
   if ($result) {
    setGlobal($objName.".hue",$hue);
    setGlobal($objName.".sat",$sat);
   }
  } elseif ($model =="mono") {  }    
 }
}
	
}


/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
	// поиск устрйоств
	$this->SearchDevices();
	//получить все найденные устройства и отобразить на странице
	$objects=getObjectsByClass("Yeelight");
	if ($objects[0]) {
	for($i = 0; $i < count($objects); $i++) {
		 $model = getGlobal($objects[$i]['TITLE'].".model");
		 $ip = getGlobal($objects[$i]['TITLE'].".Location");
		 if ($model){
			 $objects[$i]['MODEL'] = $model;
		}
		 if ($ip){
			 $objects[$i]['IP'] = $ip ;
			 }
	}
	$out['RESULT'] = $objects;
	}
$this->SearchDevices();
}
function usual(&$out) {
 $this->admin($out);
 
}
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
	parent::install();
	
	@include_once(ROOT.'languages/'.$this->name.'_'.SETTINGS_SITE_LANGUAGE.'.php'); //локализация
    @include_once(ROOT.'languages/'.$this->name.'_default'.'.php');
	SQLExec("UPDATE project_modules SET TITLE='".LANG_YE_APP_TITLE."' WHERE NAME='".$this->name."'"); 
	
    addClass('Yeelight');
	
	$method_id=addClassMethod('Yeelight', 'getdata',"require(DIR_MODULES.'Yeelight/Yeelight_getdata.php');");
	if ($method_id) {
		$class=SQLSelectOne("SELECT * FROM methods WHERE ID=".$method_id);
		$class['DESCRIPTION']='Обновить данные';
		SQLUpdate('methods',$class);
	}
	
	$method_id=addClassMethod('Yeelight', 'on_off',"require(DIR_MODULES.'Yeelight/Yeelight_on_off.php');");
	if ($method_id) {
		$class=SQLSelectOne("SELECT * FROM methods WHERE ID=".$method_id);
		$class['DESCRIPTION']='Включить или выключить лампочку';
		SQLUpdate('methods',$class);
	}

	
	$method_id=addClassMethod('Yeelight', 'set_bright',"require(DIR_MODULES.'Yeelight/Yeelight_set_bright.php');");
	if ($method_id) {
		$class=SQLSelectOne("SELECT * FROM methods WHERE ID=".$method_id);
		$class['DESCRIPTION']='Установить яркость лампочки';
		SQLUpdate('methods',$class);
	}
	
	$method_id=addClassMethod('Yeelight', 'set_name',"require(DIR_MODULES.'Yeelight/Yeelight_set_name.php');");
	if ($method_id) {
		$class=SQLSelectOne("SELECT * FROM methods WHERE ID=".$method_id);
		$class['DESCRIPTION']='Установить имя лампочки';
		SQLUpdate('methods',$class);
	}
	
	$method_id=addClassMethod('Yeelight', 'set_rgb',"require(DIR_MODULES.'Yeelight/Yeelight_set_rgb.php');");
	if ($method_id) {
		$class=SQLSelectOne("SELECT * FROM methods WHERE ID=".$method_id);
		$class['DESCRIPTION']='Установить RGB цвет лампочки';
		SQLUpdate('methods',$class);
	}
	
	$method_id=addClassMethod('Yeelight', 'set_ct',"require(DIR_MODULES.'Yeelight/Yeelight_set_ct.php');");
	if ($method_id) {
		$class=SQLSelectOne("SELECT * FROM methods WHERE ID=".$method_id);
		$class['DESCRIPTION']='Установить цвет лампочки';
		SQLUpdate('methods',$class);
	}
	
	$method_id=addClassMethod('Yeelight', 'set_hsv',"require(DIR_MODULES.'Yeelight/Yeelight_set_hsv.php');");
	if ($method_id) {
		$class=SQLSelectOne("SELECT * FROM methods WHERE ID=".$method_id);
		$class['DESCRIPTION']='Установить HSV цвет лампочки';
		SQLUpdate('methods',$class);
	}

	$method_id=addClassMethod('Yeelight', 'switch',"$this->setProperty('status',($this->getProperty('status')+1)%2);");
	if ($method_id) {
		$class=SQLSelectOne("SELECT * FROM methods WHERE ID=".$method_id);
		$class['DESCRIPTION']='Переключить лампу';
		SQLUpdate('methods',$class);
	}

	$prop_id=addClassProperty('Yeelight', 'status', 0);//#1
				  if ($prop_id) {
					  $property=SQLSelectOne("SELECT * FROM properties WHERE ID=".$prop_id);
					  $property['ONCHANGE']='on_off';
					  SQLUpdate('properties',$property);
				  } 

	$prop_id=addClassProperty('Yeelight', 'bright', 0);//#2
				  if ($prop_id) {
					  $property=SQLSelectOne("SELECT * FROM properties WHERE ID=".$prop_id);
					  $property['ONCHANGE']='set_bright';
					  SQLUpdate('properties',$property);
				  } 


	$prop_id=addClassProperty('Yeelight', 'name', 0);//#12
				  if ($prop_id) {
					  $property=SQLSelectOne("SELECT * FROM properties WHERE ID=".$prop_id);
					  $property['ONCHANGE']='set_name';
					  SQLUpdate('properties',$property);
				  }
	$prop_id=addClassProperty('Yeelight', 'rgb', 0);//#4
				  if ($prop_id) {
					  $property=SQLSelectOne("SELECT * FROM properties WHERE ID=".$prop_id);
					  $property['ONCHANGE']='set_rgb';
					  SQLUpdate('properties',$property);
				  }
	$prop_id=addClassProperty('Yeelight', 'hue', 0);//#5
				  if ($prop_id) {
					  $property=SQLSelectOne("SELECT * FROM properties WHERE ID=".$prop_id);
					  $property['ONCHANGE']='set_hsv';
					  SQLUpdate('properties',$property);
				  }

	$prop_id=addClassProperty('Yeelight', 'sat', 0);//#6
				  if ($prop_id) {
					  $property=SQLSelectOne("SELECT * FROM properties WHERE ID=".$prop_id);
					  $property['ONCHANGE']='set_hsv';
					  SQLUpdate('properties',$property);
				  }
	$prop_id=addClassProperty('Yeelight', 'ct', 0);//#3
				  if ($prop_id) {
					  $property=SQLSelectOne("SELECT * FROM properties WHERE ID=".$prop_id);
					  $property['ONCHANGE']='set_ct';
					  SQLUpdate('properties',$property);
				  }	
				  			  
	addClassProperty('Yeelight', 'id', 0); //Создаёт свойство класса и указывает, что необходимо хранить историю значений 0 дней
	addClassProperty('Yeelight', 'model', 0);
	addClassProperty('Yeelight', 'Location', 0);				  

	addClassProperty('Yeelight', 'power', 0);//power дублирует статус для обеспечения совместимости
	addClassProperty('Yeelight', 'main_power', 0);//power дублирует статус для обеспечения совместимости
	addClassProperty('Yeelight', 'color_mode', 0);
	addClassProperty('Yeelight', 'flowing', 0);
	addClassProperty('Yeelight', 'flow_params', 0);
	addClassProperty('Yeelight', 'bg_power', 0);
	addClassProperty('Yeelight', 'bg_flowing', 0);
	addClassProperty('Yeelight', 'bg_flow_params', 0);
	addClassProperty('Yeelight', 'bg_ct', 0);
	addClassProperty('Yeelight', 'bg_lmode', 0);
	addClassProperty('Yeelight', 'bg_bright', 0);
	addClassProperty('Yeelight', 'bg_rgb', 0);
	addClassProperty('Yeelight', 'bg_hue', 0);
	addClassProperty('Yeelight', 'bg_sat', 0);
	addClassProperty('Yeelight', 'nl_br', 0);
	addClassProperty('Yeelight', 'active_mode', 0);


// поиск устрйоств
$this->SearchDevices();
}
 
 public function uninstall()
   {
	  /*
	  SQLExec("delete from methods where description = 'Включить или выключить лампочку'");
	  SQLExec("delete from methods where description = 'Установить яркость лампочки'");
	  SQLExec("delete from methods where description = 'Установить имя лампочки'");
	  SQLExec("delete from methods where description = 'Установить RGB цвет лампочки'");
	  SQLExec("delete from methods where description = 'Установить цвет лампочки'");
	  SQLExec("delete from methods where description = 'Установить HSV цвет лампочки'");
	  */
	    $data=SQLSelectOne("select id from classes where title = 'Yeelight'");
      SQLExec("delete from methods where class_id = ".$data['id']);
      /*SQLExec("delete from methods where title = 'set_bright'");
      SQLExec("delete from methods where title = 'set_name'");
      SQLExec("delete from methods where title = 'set_rgb'");
      SQLExec("delete from methods where title = 'set_ct'");
      SQLExec("delete from methods where title = 'set_hsv'"); 
	    */
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
