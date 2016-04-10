<?php
/* 
**  ===========
**  PlaatEnergy
**  ===========
**
**  Created by wplaat
**
**  For more information visit the following website.
**  Website : www.plaatsoft.nl 
**
**  Or send an email to the following address.
**  Email   : info@plaatsoft.nl
**
**  All copyrights reserved (c) 2008-2016 PlaatSoft
*/

/**
 * @file
 * @brief realtime page fetch data script 
 */
 
/*
** ---------------------
** Author: wplaat part
** ---------------------
*/

include "config.inc";
include "general.inc";
include "database.inc";

plaatenergy_db_connect($dbhost, $dbuser, $dbpass, $dbname);

// ---------------------------------------------

$timestamp1 = date("Y-m-d 00:00:00");
$timestamp2 = date("Y-m-d 23:59:59");

$sql1  = 'select temperature, pressure, humidity from weather where ';
$sql1 .= 'timestamp>="'.$timestamp1.'" and timestamp<"'.$timestamp2.'" order by id desc limit 0,1';
$result1 = plaatenergy_db_query($sql1);
$data1 = plaatenergy_db_fetch_object($result1);

$temperature = 0;
$pressure = 0;
$humidity = 0;	
if (isset($data1->temperature)) {
   $temperature = $data1->temperature;
	$pressure = $data1->pressure;
	$humidity = $data1->humidity;
}
  
// ---------------------------------------------

$sql2  = 'select power from energy1 where ';
$sql2 .= 'timestamp>="'.$timestamp1.'" and timestamp<"'.$timestamp2.'" order by id desc limit 0,1';
$result2 = plaatenergy_db_query($sql2);
$data2 = plaatenergy_db_fetch_object($result2);

$power = 0;
if ( isset($data2->power) ) {
	$power = $data2->power;
}

// ---------------------------------------------

$timestamp1=date('Y-m-d');
$timestamp2=date('Y-m-d');

$sql3  = 'select low_used, normal_used, low_delivered, normal_delivered, solar_delivered, gas_used ';
$sql3 .= 'from energy_summary ';
$sql3 .= 'where date>="'.$timestamp1.'" and date<="'.$timestamp2.'"';
$result3 = plaatenergy_db_query($sql3);
$data3 = plaatenergy_db_fetch_object($result3);

$today_energy_used = 0;
$today_energy_delivered = 0;
$today_gas_used = 0;
if ( isset ($data3->low_used)) {

	$delivered_low = $data3->low_delivered;
 	$delivered_normal = $data3->normal_delivered;
	$tmp = $data3->solar_delivered - $delivered_low - $delivered_normal;
	$delivered_local = 0;
	if ($tmp >0 ) {
		$delivered_local = $tmp;
	}
	$today_energy_delivered = $delivered_low + $delivered_normal + $delivered_local;			
	$today_energy_used = $data3->low_used + $data3->normal_used + $delivered_local;
	$today_gas_used = $data3->gas_used;
}

// ---------------------------------------------

$time=mktime(0, 0, 0, 1, 1, date('Y'));
$timestamp1=date('Y-1-1', $time);
$timestamp2=date('Y-12-t', $time);

$sql5  = 'select sum(low_used) as low_used, sum(normal_used) as normal_used, ';
$sql5 .= 'sum(low_delivered) as low_delivered, sum(normal_delivered) as normal_delivered, ';
$sql5 .= 'sum(solar_delivered) as solar_delivered, sum(gas_used) as gas_used ';
$sql5 .= 'FROM energy_summary ';
$sql5 .= 'where date>="'.$timestamp1.'" and date<="'.$timestamp2.'"';
$result5 = plaatenergy_db_query($sql5);
$data5 = plaatenergy_db_fetch_object($result5);

$total_energy_used = 0;
$total_energy_delivered = 0;
$total_gas_used = 0;
if ( isset ($data5->low_used)) {
	$delivered_low = $data5->low_delivered;
	$delivered_normal = $data5->normal_delivered;
	$tmp = $data5->solar_delivered - $delivered_low - $delivered_normal;
	$delivered_local = 0;
	if ($tmp >0 ) {
		$delivered_local = $tmp;
	}	
	$total_energy_delivered = $delivered_low + $delivered_normal + $delivered_local;		
	$total_energy_used = $data5->low_used + $data5->normal_used + $delivered_local;
	$total_gas_used = $data5->gas_used;
}

// ---------------------------------------------

// Creating 1 kWh energy results in 0.001 ton CO2 emission.
$total_energy_co2 = round(($total_energy_used - $total_energy_delivered), 2);  

// Burning 1 m3 gas results in 0.00178 ton CO2 emission.
$total_gas_co2 = round(($data5->gas_used * 1.78), 2);

// Amount of tree needed to offset gas + energy co2 emission
$total_tree_offset = round((($total_energy_co2 + $total_gas_co2) / 200),2);

/*
** ---------------------
** Author: bplaat part
** ---------------------
*/

// Functie om getallen mooi te maken 1.000,4 = 0 / 1,000.4 = 1
if ($_GET["q"][0] == "0") {
  function num ($number, $d = 1) {
    if ($number != 0) {
      return number_format($number, $d);
    } else {
      return 0;
    }
  }
}
elseif ($_GET["q"][0] == "1") {
  function num ($number, $d = 1) {
    if ($number != 0) {
      return str_replace("|", ".", str_replace(".", ",", str_replace(",", "|", number_format($number, $d))));
    } else {
      return 0;
    }
  }
}

// Set HTML headers
header("Content-Type: application/json");
header("Cache-Control: no-cache");
header("Pragma: no-cache");

// Zorg ervoor dat Bastiaans website dit het kan uitlezen
header("Access-Control-Allow-Origin: http://bastiaan.plaatsoft.nl");

// Calculate actual energy in Watt
if ($power > 0) {
  $json["current_watt"] = "- ".num($power,0)." Watt";
} else {
  $json["current_watt"] = "+ ".num(($power*-1),0)." Watt";
}

// Calculate actuel used energy today in kWh 
$energy_today = $today_energy_delivered - $today_energy_used;
if ($energy_today > 0) {
  $json["energy_today"] = "+ " .num($energy_today) . " kWh";
} else {
  $json["energy_today"] = str_replace("-", "- ", num($energy_today)) . " kWh";
}

$json["total_decrease"] = num($total_energy_used) . " kWh";
$json["total_delivery"] = num($total_energy_delivered). " kWh";

// Calculate actual used gas today in m3 = 0 / dm3 = 1
if ($_GET["q"][1] == "0") {
  $json["total_gas"] = num($total_gas_used) . " m&sup3;";
  $json["gas_today"] = num($today_gas_used) . " m&sup3;";
} elseif ($_GET["q"][1] == "1") {
  $json["total_gas"] = num($total_gas_used * 1000, 0) . " dm&sup3;";
  $json["gas_today"] = num($today_gas_used * 1000, 0) . " dm&sup3;";
}

// Calculate actual temperature in graden celcius = 0 / fahrenheit = 1 / kelvin = 2
if ($_GET["q"][2] == "0") {
  $json["temperature"] = num($temperature) . " &deg;C";
} elseif ($_GET["q"][2] == "1") {
  $json["temperature"] = num($temperature * 9 / 5 + 32) . " &deg;F";
} elseif ($_GET["q"][2] == "2") {
  $json["temperature"] = num($temperature + 273.15) . " K";
}

$json["pressure"] = num($pressure) . " hPa";
$json["humidity"] = num($humidity) . " %";

// Calculate actual energy and gas co2 emission this year in kg
if ($total_energy_co2 > 0) {
  $json["total_energy_co2"] = "+ " .num($total_energy_co2) . " kg";
} else {
  $json["total_energy_co2"] = str_replace("-", "- ", num($total_energy_co2)) . " kg";
}

if ($total_gas_co2 > 0) {
  $json["total_gas_co2"] = "+ " .num($total_gas_co2) . " kg";
} else {
  $json["total_gas_co2"] = str_replace("-", "- ", num($total_gas_co2)) . " kg";
}

// Trees for co2
if ($total_tree_offset > 0) {
  $json["total_tree_offset"] = "+ " .num($total_tree_offset);
} else {
  $json["total_tree_offset"] = str_replace("-", "- ", num($total_tree_offset));
}

echo json_encode($json);

/*
** ---------------------
** THE END
** ---------------------
*/

?>
