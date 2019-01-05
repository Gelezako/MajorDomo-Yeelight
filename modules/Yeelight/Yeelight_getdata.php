<?
include_once(DIR_MODULES.'Yeelight/Yeelight_library.php');
$objects=getObjectsByClass("Yeelight");
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
    $bulb = $bulbFactory->create($data);
    $prop=[BulbProperties::POWER
    ,BulbProperties::BRIGHT
    ,BulbProperties::RGB
    ,BulbProperties::COLOR_TEMPERATURE
    ,BulbProperties::HUE
    ,BulbProperties::SATURATION
    ,BulbProperties::COLOR_MODE
    ,BulbProperties::FLOWING
    //,BulbProperties::DELAY_OFF  
    ,BulbProperties::FLOW_PARAMS  
    //,BulbProperties::MUSIC_ON
    //,BulbProperties::NAME            
    ,BulbProperties::bg_power
    ,BulbProperties::bg_flowing
    ,BulbProperties::bg_flow_params
    ,BulbProperties::bg_bright
    ,BulbProperties::bg_rgb
    ,BulbProperties::bg_ct
    ,BulbProperties::bg_lmode
    ,BulbProperties::bg_hue
    ,BulbProperties::bg_sat
    ,BulbProperties::nl_br
    ,BulbProperties::active_mode
    ];    
    echo "<table border=1><tr><td><pre>\n old data:\n";
    foreach ($prop as $p)
    echo $p." =>".gg($objName.".".$p)."\n";

    $res = $bulb->getProp($prop);    
    //print_r($res);
    echo "<td><pre>receive:\n";
    foreach ($res['result'] as $k => $v)
    {
    	echo "[". $prop[$k]."] => ".$v."\n";
    	$v=trim($v);
    	if(strlen($v)>0)sg($objName.".".$prop[$k],$v);
    }
    //echo $objName."\n";
    echo "<td><pre>";
    echo "new data:\n";
    foreach ($prop as $p)
    echo $p." =>".gg($objName.".".$p)."\n";  
  }    


/*    
+1*power | on: smart LED is turned on / off: smart LED is turned off
+2*bright | Brightness percentage. Range 1 ~ 100
+3*ct | Color temperature. Range 1700 ~ 6500(k)
+4rgb | Color. Range 1 ~ 16777215
+5*hue |Hue. Range 0 ~ 359
+6*sat |Saturation. Range 0 ~ 100
7*color_mode | 1: rgb mode / 2: color temperature mode / 3: hsv mode
8*flowing | 0: no flow is running / 1:color flow is running
9*delayoff| The remaining time of a sleep timer. Range 1 ~ 60 (minutes)
10*flow_params |Current flow parameters (only meaningful when 'flowing' is 1)
11*music_on | 1: Music mode is on / 0: Music mode is off
+12*name |The name of the device set by “set_name” command
13*bg_power |Background light power status
14*bg_flowing | Background light is flowing
15*bg_flow_params | Current flow parameters of background light
16*bg_ct |Color temperature of background light
17*bg_lmode | 1: rgb mode / 2: color temperature mode / 3: hsv mode
18*bg_bright |Brightness percentage of background light
19*bg_rgb |Color of background light
20*bg_hue |Hue of background light
21*bg_sat |Saturation of background light
22*nl_br |Brightness of night mode light
23*active_mode |0: daylight mode / 1: moonlight mode (ceiling light only)
*/     