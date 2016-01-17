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

/*
** ---------------------------------------------------------------- 
** SUPPORT
** ---------------------------------------------------------------- 
*/

/**
 *  check_solar_meter
 */
function check_solar_meter() {

  $solar_meter_present = plaatenergy_db_get_config_item('solar_meter_present');

  if ($solar_meter_present=="false") {
  
   $page  = '<div class="checker disabled">';
   $page .= t('SOLAR_METER_DISABLED');		
   $page .= '</div>';
	
  } else {

    $sql = 'select etotal from solar where timestamp = "'.date("Y-m-d H:i:00").'"';	
    $result = plaatenergy_db_query($sql);
    $row = plaatenergy_db_fetch_object($result);

    if (isset($row->etotal)){

      $page  = '<div class="checker good">';
      $page .= t('SOLAR_METER_CONNECTION_UP');
      $page .='</div>';

    } else {

      $page = '<div class="checker bad">';
      $page .= t('SOLAR_METER_CONNECTION_DOWN');	
      $page .='</div>';
    }
  }
  return $page;
}

/**
 *  check_energy_meter
 */
function check_energy_meter() {
  
   $energy_meter_present = plaatenergy_db_get_config_item('energy_meter_present');
	
	if ($energy_meter_present=="false") {
  
		$page  = '<div class="checker disabled">';
		$page .= t('ENERGY_METER_DISABLED');		
		$page .= '</div>';

	} else {
	   
		$sql = 'select dal from energy where timestamp = "'.date("Y-m-d H:i:00").'"';	
		$result = plaatenergy_db_query($sql);
		$row = plaatenergy_db_fetch_object($result);

		if (isset($row->dal)){
			$page  = '<div class="checker good">';
			$page .= t('ENERGY_METER_CONNECTION_UP');
			$page .= '</div>';
			
		} else {
		
			$page  = '<div class="checker bad">';
			$page .= t('ENERGY_METER_CONNECTION_DOWN');
			$page .= '</div>';
		}
   }
	
	return $page;
}

/**
 * check_weather_station
 */
function check_weather_station() {

   $weather_station_present = plaatenergy_db_get_config_item('weather_station_present');
    
   if ($weather_station_present=="false") {
  
		$page  = '<div class="checker disabled">';
		$page .= t('WEATHER_METER_DISABLED');	
		$page .= '</div>';
	
	} else {
	
		$sql = 'select humidity from weather where timestamp = "'.date("Y-m-d H:i:00").'"';
		$result = plaatenergy_db_query($sql);
		$row = plaatenergy_db_fetch_object($result);

		if (isset($row->humidity)){
			$page  = '<div class="checker good">';
			$page .= t('WEATHER_METER_CONNECTION_UP');
			$page .= '</div>';
			
		} else {
		
			$page  = '<div class="checker bad">';
			$page .= t('WEATHER_METER_CONNECTION_DOWN');
			$page .= '</div>';
		}
	}
	return $page;
}

/*
** ---------------------------------------------------------------- 
** PAGE
** ---------------------------------------------------------------- 
*/

function plaatenergy_home_page() {

  global $version;

  $page = '<h1>' . t('TITLE') . ' ' . $version . '</h1>';

  $page .= "<div id='version'></div>";

	if ( !file_exists ( "config.inc" )) {
		$page .= '<br/><br/>';
		$page .= t('CONGIG_BAD');
		$page .= '<br/><br/>';
		
	} else {

		$page .= '<table>';

		$page .= '<tr>';
		$page .= '<th>'.t('YEARS_REPORT').'</th>';
		$page .= '<th>'.t('YEAR_REPORT').'</th>';
		$page .= '<th>'.t('MONTH_REPORT').'</th>';
		$page .= '<th>'.t('DAY_REPORT').'</th>';
		$page .= '<th>'.t('WEATHER_REPORT').'</th>';
		$page .= '</tr>';
	
		$page .= '<tr>';
		
		$page .= '<td>';
		$page .= plaatenergy_link('pid='.PAGE_YEARS_IN_ENERGY.'&eid='.EVENT_KWH, t('LINK_IN_ENERGY'));
		$page .= plaatenergy_link('pid='.PAGE_YEARS_OUT_ENERGY.'&eid='.EVENT_KWH, t('LINK_OUT_ENERGY'));
		$page .= plaatenergy_link('pid='.PAGE_YEARS_IN_GAS.'&eid='.EVENT_M3, t('LINK_IN_GAS'));
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= plaatenergy_link('pid='.PAGE_YEAR_IN_ENERGY.'&eid='.EVENT_KWH, t('LINK_IN_ENERGY'));
		$page .= plaatenergy_link('pid='.PAGE_YEAR_OUT_ENERGY.'&eid='.EVENT_KWH, t('LINK_OUT_ENERGY'));
		$page .= '<a href="year_in_gas.php">'.t('LINK_IN_GAS').'</a>';
		$page .= '</td>';

		$page .= '<td>';
		$page .= plaatenergy_link('pid='.PAGE_MONTH_IN_ENERGY.'&eid='.EVENT_KWH, t('LINK_IN_ENERGY'));
		$page .= plaatenergy_link('pid='.PAGE_MONTH_OUT_ENERGY.'&eid='.EVENT_KWH, t('LINK_OUT_ENERGY'));
		$page .= '<a href="month_in_gas.php">'.t('LINK_IN_GAS').'</a>';
		$page .= plaatenergy_link('pid='.PAGE_MONTH_OUT_ENERGY_MAX, t('LINK_OUT_ENERGY_MAX')); 
		$page .= '</td>';

		$page .= '<td>';
		$page .= plaatenergy_link('pid='.PAGE_DAY_IN_ENERGY.'&eid='.EVENT_KWH, t('LINK_IN_ENERGY'));
		$page .= '<a href="day_out_kwh.php">'.t('LINK_OUT_ENERGY').'</a>';
		$page .= '<a href="day_in_gas.php">'.t('LINK_IN_GAS').'</a>';
		$page .= '</td>';

		$page .= '<td>';
		$page .= plaatenergy_link('pid='.PAGE_DAY_PRESSURE, t('LINK_PRESSURE'));
		$page .= plaatenergy_link('pid='.PAGE_DAY_TEMPERATURE, t('LINK_TEMPERATURE'));
		$page .= plaatenergy_link('pid='.PAGE_DAY_HUMIDITY, t('LINK_HUMIDITY'));
		$page .= '</td>';

		$page .= '</tr>';

		$page .= '<tr>';
		$page .= '<td>';
		$page .= plaatenergy_link('pid='.PAGE_ABOUT, t('LINK_ABOUT'));
		$page .= '</td>';
		$page .= '<td>';
		$page .= plaatenergy_link('pid='.PAGE_DONATE, t('LINK_DONATE'));
		$page .= '</td>';
		$page .= '<td>';
		$page .= '<a href="./ui/">'.t('LINK_GUI').'</a>';
		$page .= '</td>';
		$page .= '<td>';
		$page .= plaatenergy_link('pid='.PAGE_RELEASE_NOTES, t('LINK_RELEASE_NOTES'));
		$page .= '</td>';
		$page .= '<td>';
		$page .= plaatenergy_link('pid='.PAGE_REPORT, t('LINK_REPORT'));
		$page .= '</td>';
		$page .= '</tr>';

		$page .= '</table>';

		$page .= '<br/><br/>';
	
		$page .= check_energy_meter();
		$page .= check_solar_meter(); 
		$page .= check_weather_station();

		$page .= '<br/><br/>';

		$page .= '<script type="text/javascript" src="js/version.js"></script>';
		$page .= '<script type="text/javascript">check_version("'.$version.'")</script>';
	}
	return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

function plaatenergy_home() {

  /* input */
  global $pid;
		
  /* Page handler */
  switch ($pid) {
		
     case PAGE_HOME:
			echo plaatenergy_home_page();
        break;
  }
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>