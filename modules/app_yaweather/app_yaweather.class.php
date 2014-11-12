<?php

/**
 * Yandex Weather Application
 *
 * module for MajorDoMo project
 * @author Fedorov Ivan <4fedorov@gmail.com>
 * @copyright Fedorov I.A.
 * @version 0.1 October 2014
 */
class app_yaweather extends module {
/**
* yaweather
*
* Module class constructor
*
* @access private
*/
function app_yaweather() {
  $this->name="app_yaweather";
  $this->title="Погода от Яндекс";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
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
  $this->forecast_day = 3;
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
 
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
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

	global $subm;
	if($subm == 'setCityId'){
		$this->save_cityId();
		$this->view_mode = "setting";
	}
	else if($subm == 'setting'){
		$this->save_setting();
		$this->get_weather(gg('yaweather.city.id'));
		$this->view_mode = "";
	}
	else if($subm == 'getCityId'){
		$this->view_mode = "getCityId";
		$this->get_cityId($out);
	}
	else if($subm == 'getWeather'){
		$this->get_weather(gg('yaweather.city.id'));
	}
	
	if($this->view_mode == ''){
		$cityId = gg('yaweather.city.id');
		$cityName = gg('yaweather.city.name');
		if($cityId != '' && $cityName != ''){
			$out["city"] = $cityName;
			$out["data_update"] = gg('yaweather.city.data_update');
			$this->view_weather($out);
		}
		else {
			$this->view_mode = "getCityId";
			$this->get_cityId($out);
		}
	}
	else if($this->view_mode == 'setting'){
		$this->get_setting($out);
	}
	else if($this->view_mode == 'getCityId'){
		$this->get_cityId($out);
	}
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 //$this->admin($out);
	$cityId = gg('yaweather.city.id');
	$cityName = gg('yaweather.city.name');
	if($cityId != '' && $cityName != ''){
		$out["city"] = $cityName;
		$out["data_update"] = gg('yaweather.city.data_update');
		$this->view_weather($out);
	}
	else {
		$out["fact_weather"] = 'Укажите город';
	}
}

function view_weather(&$out) {
$windDirection = array('n'=>'С','nne'=>'ССВ','ne'=>'СВ','ene'=>'ВСВ','e'=>'В','ese'=>'ВЮВ','se'=>'ЮВ','sse'=>'ЮЮВ','s'=>'Ю','ssw'=>'ЮЮЗ','sw'=>'ЮЗ','wsw'=>'ЗЮЗ','w'=>'З','wnw'=>'ЗСЗ','nw'=>'CЗ','nnw'=>'CCЗ');


if(gg('yaweather.setting.imgCache') == 'on'){
	$url_ico = BASE_URL.ROOTHTML."cached/yaweather/48x48/";
}
else{
	$url_ico = "http://yandex.st/weather/1.2.77/i/icons/48x48/";
}
	$temp = gg('yaweather.fact.temperature');
	if($temp > 0) $temp = "+".$temp;
	$fact_weather = "<div class=yaweather>
	  <table width=100% border=0>
		<tr>";
	$fact_weather .= "<td rowspan=2><div class=ya_icon><img src=".$url_ico.gg('yaweather.fact.image').".png width=48 height=48>".$temp." °C</div></td>";
	$fact_weather .= "<td align=right><#LANG_WIND#>:</td>";
	$fact_weather .= "<td align=left>&nbsp;".$windDirection[gg('yaweather.fact.wind_direction')]."&nbsp;(".gg('yaweather.fact.wind_speed')."м/с)</td>
		</tr>
		<tr>
			<td align=right><#LANG_HUMIDITY#>:</td>";
	$fact_weather .= "<td align=left>&nbsp;".gg('yaweather.fact.humidity')."%</td>
		</tr>
		<tr>";
	$fact_weather .= "<td>".gg('yaweather.fact.weather_type')."</td>";
	$fact_weather .= "<td align=right><#LANG_PRESSURE#>:</td>";
	$fact_weather .= "<td align=left>&nbsp;".gg('yaweather.fact.pressure')."</td>
		 </tr>
	   </table>
	</div>";	
	$out["fact_weather"] = $fact_weather;
	
	$type = array(5 => 'day_short', 6 => 'night_short');
	
	for($i=0;$i<=$this->forecast_day-1;$i++){
	
		foreach($type as $types){
			
			$temp = gg('yaweather.day'.$i.'.'.$types.'_temperature-data_avg');
			if($temp > 0) $temp = "+".$temp;
			
			if($types == $type[5]){
				$day_type[$types] = "<div style=\"float:left; display:inline-table;\"><div><strong>Днём</strong></div>";
			}
			else{
				$day_type[$types] = "<div style=\"display:inline-table;\"><div><strong>Ночью</strong></div>";
			}
			$day_type[$types] .= "<div class=yaweather>
			  <table width=100% border=0>
				<tr>";
			$day_type[$types] .= "<td rowspan=2><div class=ya_icon><img src=".$url_ico.gg('yaweather.day'.$i.'.'.$types.'_image-v3').".png width=48 height=48>".$temp." °C</div></td>";
			$day_type[$types] .= "<td align=right><#LANG_WIND#>:</td>";
			$day_type[$types] .= "<td align=left>&nbsp;".$windDirection[gg('yaweather.day'.$i.'.'.$types.'_wind_direction')]."&nbsp;(".gg('yaweather.day'.$i.'.'.$types.'_wind_speed')."м/с)</td>
				</tr>
				<tr>
					<td align=right><#LANG_HUMIDITY#>:</td>";
			$day_type[$types] .= "<td align=left>&nbsp;".gg('yaweather.day'.$i.'.'.$types.'_humidity')."%</td>
				</tr>
				<tr>";
			$day_type[$types] .= "<td>".gg('yaweather.day'.$i.'.'.$types.'_weather_type')."</td>";
			$day_type[$types] .= "<td align=right><#LANG_PRESSURE#>:</td>";
			$day_type[$types] .= "<td align=left>&nbsp;".gg('yaweather.day'.$i.'.'.$types.'_pressure')."</td>
				 </tr>
			   </table>
			</div></div>";
		}
		if($i == 0){
			$day_weather[$i]["weather"] = "<h4>Сегодня ".gg('yaweather.day'.$i.'.date')."</h4>";
		}
		else{
			$day_weather[$i]["weather"] = "<h4>Прогноз на ".gg('yaweather.day'.$i.'.date')."</h4>";
		}
		$day_weather[$i]["weather"] .= $day_type[$type[5]].$day_type[$type[6]];
	}
	$out["DAY_WEATHER"] = $day_weather;
}

function get_weather($city) {
	$data_file = 'http://export.yandex.ru/weather-ng/forecasts/'.$city.'.xml';
	$xml = simplexml_load_file($data_file);
	$forecast_day =  $this->forecast_day;
	$day_count = 0;
	if(gg('yaweather.setting.forecastType') == 'full'){
		$type = array(1 => 'morning', 2 => 'day', 3 => 'evening', 4 => 'night', 5 => 'day_short', 6 => 'night_short');
	}
	else{
		$type = array(5 => 'day_short', 6 => 'night_short');
	}
	$imgCache = gg('yaweather.setting.imgCache');
	$indicators = array(0 => 'temperature-data',1 => 'weather_type',2 => 'wind_direction',3 => 'wind_speed',4 => 'humidity',5 => 'pressure', 6 => 'image-v3');
	$temperature = array(0 => 'avg',1 => 'from',2 => 'to');
	
	if($fact = $xml->fact) {
		$res = explode ("T" , $fact->observation_time);
		$get_date = explode ("-" , $res[0]);
		$date = $res[1]." ".$get_date[2].".".$get_date[1].".".$get_date[0];
		
		sg('yaweather.fact.temperature', $fact->temperature);
		sg('yaweather.fact.weather_type', $fact->weather_type);
		sg('yaweather.fact.wind_direction', $fact->wind_direction);
		sg('yaweather.fact.wind_speed', $fact->wind_speed);
		sg('yaweather.fact.humidity', $fact->humidity);
		sg('yaweather.fact.pressure', $fact->pressure);
		sg('yaweather.fact.image', $fact->{'image-v3'});
		sg('yaweather.city.data_update', $date);
		if($imgCache == 'on') $this->get_icon($fact->{'image-v3'});
	}
	
	foreach($xml->day as $day) {
		if($day_count == $forecast_day) break;
		
		$get_date = explode ("-" , $day["date"]);
		$date = $get_date[2].".".$get_date[1].".".$get_date[0];
		sg('yaweather.day'.$day_count.'.date', $date);
		sg('yaweather.day'.$day_count.'.sunrise', $day->sunrise);
		sg('yaweather.day'.$day_count.'.sunset', $day->sunset);
		sg('yaweather.day'.$day_count.'.moonrise', $day->moonrise);
		sg('yaweather.day'.$day_count.'.moonset', $day->moonset);
		
		for($i=0;$i<=5;$i++){
			foreach($type as $types){
				if($day->day_part[$i]["type"] == $types){
					foreach($indicators as $res){
						if($res == $indicators[0]){
							if($types != $type[5] && $types != $type[6]){
								foreach($temperature as $temp){
									//DebMes($types.'_'.$res.'_'.$temp.'====>'.$day->day_part[$i]->$res->$temp);
									sg('yaweather.day'.$day_count.'.'.$types.'_'.$res.'_'.$temp, $day->day_part[$i]->$res->$temp);
								}
							}
							else{
								//DebMes($types.'_'.$res.'_avg'.'====>'.$day->day_part[$i]->$res->avg);
								sg('yaweather.day'.$day_count.'.'.$types.'_'.$res.'_avg', $day->day_part[$i]->$res->avg);
							}
						}
						else{
							//DebMes($types.'_'.$res.'====>'.$day->day_part[$i]->$res);
							sg('yaweather.day'.$day_count.'.'.$types.'_'.$res, $day->day_part[$i]->$res);
							if($res == $indicators[6] && $imgCache == 'on'){
								$this->get_icon($day->day_part[$i]->$res);
							}
						}
					}
				}
			}
		}
		$day_count++;
	}
}

function get_icon($image){
	DebMes($image);
	$filename=$image.'.png';
	
	if (!file_exists(ROOT.'cached/yaweather/48x48/'.$filename)) {
		$url = 'http://yandex.st/weather/1.2.77/i/icons/48x48/'.$filename;
		$contents = file_get_contents($url);
		if ($contents){
			if (!is_dir(ROOT.'cached/yaweather/48x48')) {
			@mkdir(ROOT.'cached/yaweather', 0777);
			@mkdir(ROOT.'cached/yaweather/48x48', 0777);
		}
		SaveFile(ROOT.'cached/yaweather/48x48/'.$filename, $contents);
		}
	}
}

function save_setting()
{
	global $forecastType;
	global $imgCache;
	global $update_interval;
	
	if(isset($forecastType)) sg('yaweather.setting.forecastType',$forecastType);
	if(!isset($imgCache)) $imgCache = 'off';
	sg('yaweather.setting.imgCache',$imgCache);
	sg('yaweather.setting.updateTime',$update_interval);
	sg('yaweather.setting.countTime',1);
	
	if($forecastType == 'shot'){
		$forecast_day =  $this->forecast_day;
		$type = array(1 => 'morning', 2 => 'day', 3 => 'evening', 4 => 'night');
		
		$indicators = array(0 => 'temperature-data',1 => 'weather_type',2 => 'wind_direction',3 => 'wind_speed',4 => 'humidity',5 => 'pressure', 6 => 'image-v3');
		$temperature = array(0 => 'avg',1 => 'from',2 => 'to');
		
		$class = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE 'yaweather'");
		if ($class['ID']) {
			for($i=0;$i<=$forecast_day-1;$i++){
				$obj = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $class['ID'] . "' AND TITLE LIKE '" . DBSafe('day'.$i) . "'");
				foreach($type as $types){
					foreach($indicators as $res){
						if($res == $indicators[0]){
							foreach($temperature as $temp){
								$pr=SQLSelectOne("SELECT * FROM properties WHERE OBJECT_ID='" . $obj['ID'] . "' AND  TITLE LIKE '".$types.'_'.$res.'_'.$temp."'");
								 $value=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='" . $pr['ID'] ."' AND OBJECT_ID='".$obj['ID']."'");
								 if ($value['ID']) {
									SQLExec("DELETE FROM phistory WHERE VALUE_ID='".$value['ID']."'");
									SQLExec("DELETE FROM pvalues WHERE PROPERTY_ID='".$pr['ID']."' AND OBJECT_ID='".$obj['ID']."'");
									SQLExec("DELETE FROM properties WHERE ID='".$pr['ID']."' AND OBJECT_ID='".$obj['ID']."'");
								 }
							}
						}
						else{
							$pr=SQLSelectOne("SELECT * FROM properties WHERE OBJECT_ID='" . $obj['ID'] . "' AND  TITLE LIKE '".$types.'_'.$res."'");
							$value=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='" . $pr['ID'] ."' AND OBJECT_ID='".$obj['ID']."'");
							if ($value['ID']) {
								SQLExec("DELETE FROM phistory WHERE VALUE_ID='".$value['ID']."'");
								SQLExec("DELETE FROM pvalues WHERE PROPERTY_ID='".$pr['ID']."' AND OBJECT_ID='".$obj['ID']."'");
								SQLExec("DELETE FROM properties WHERE ID='".$pr['ID']."' AND OBJECT_ID='".$obj['ID']."'");
							}
						}
					}
				}	
			}
		}
	}
}

function get_setting(&$out)
{
	$out["city"] = gg('yaweather.city.name');
	$out["forecastType"] = gg('yaweather.setting.forecastType');
	$out["imgCache"] = gg('yaweather.setting.imgCache');
	$out["updateTime"] = gg('yaweather.setting.updateTime');
	//DebMes($out);
}

function save_cityId()
{
	global $city_id;
	global $city_name;
	if((isset ($city_id) && $city_id !=0) && isset($city_name)){
		sg('yaweather.city.id', $city_id);
		sg('yaweather.city.name', $city_name);
		//$this->admin($out);
	}
}

function get_cityId(&$out)
{
	global $country;
	if(!isset($country)) $country = '';
	$data_file = 'http://weather.yandex.ru/static/cities.xml';
	$xml = simplexml_load_file($data_file);
	$out["country"] = '<option value="0">--Выберите страну--</option>';
	$out["city"] = '<option value="0">--Выберите город--</option>';
	foreach ( $xml->country as $key => $value)  {
		$out["country"] .= '<option value="'.$value["name"].'"';
		if ($value["name"] == $country) {
			$out["country"] .= ' selected';
			foreach ($value->city as $key1 => $value1) {
				$out["city"] .= '<option value="'.$value1["id"].'">' .$value1. '</option>';
			}
		}
	$out["country"] .= '>'.$value["name"].'</option>';
	}
}
/**
* Install
*
* Module installation routine
*
* @access private
*/
function install() {

$className = 'yaweather';
$objectName = array('city', 'setting', 'fact', 'day0', 'day1', 'day2');
$objDescription = array('Место положение', 'Настройки', 'Текущая температура', 'Прогноз погоды на день', 'Прогноз погоды на завтра', 'Прогноз погоды на послезавтра');

$rec = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe($className) . "'");
if (!$rec['ID']) {
	$rec = array();
	$rec['TITLE'] = $className;
	$rec['DESCRIPTION'] = 'Яндекс погода';
	$rec['ID'] = SQLInsert('classes', $rec);
}
for ($i = 0; $i < count($objectName); $i++) {
	$obj_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($objectName[$i]) . "'");
	if (!$obj_rec['ID']) {
		$obj_rec = array();
		$obj_rec['CLASS_ID'] = $rec['ID'];
		$obj_rec['TITLE'] = $objectName[$i];
		$obj_rec['DESCRIPTION'] = $objDescription[$i];
		$obj_rec['ID'] = SQLInsert('objects', $obj_rec);
	}
}

 
$code = '
// START yaWeather module
$updateTime = gg(\'yaweather.setting.updateTime\');
if($updateTime > 0){
$count = gg(\'yaweather.setting.countTime\');
echo"<br>updateTime = $updateTime || Count = $count";
	if($count >= $updateTime){
		include_once(DIR_MODULES.\'app_yaweather/app_yaweather.class.php\');
		$app_yaweather=new app_yaweather();
		$app_yaweather->get_weather(gg(\'yaweather.city.id\'));
		sg(\'yaweather.setting.countTime\',1);
		echo"<br>Update Weather";
	} else {
		$count++;
		sg(\'yaweather.setting.countTime\',$count);
		echo"<br>Count ++ $count";
	}
}
// END yaWeather module
';

$res=SQLSelectOne("SELECT ID, CODE FROM methods WHERE OBJECT_ID='0' AND  TITLE LIKE 'onNewHour'");

	if (!in_array($code, $res)) {
		$res["CODE"] = $res["CODE"].$code;
		SQLUpdate('methods', $res);
	}
parent::install($parent_name);
// --------------------------------------------------------------------
}
}
?>