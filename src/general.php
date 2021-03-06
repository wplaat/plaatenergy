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
 * @brief contain general logic
 */

/*
** -----------
** GENERAL
** -----------
*/

define('DEBUG', 0);

// Installation path
define('BASE_DIR', '/var/www/html/plaatenergy');
 
/*
** -----------
** PAGES
** -----------
*/

define('PAGE_HOME_LOGIN',           10);
define('PAGE_HOME',                 11);
define('PAGE_ABOUT',                12);
define('PAGE_DONATE',               13);
define('PAGE_RELEASE_NOTES',        14);
define('PAGE_REPORT',               15);
define('PAGE_REALTIME',             16);
define('PAGE_SYSTEM',               17);
define('PAGE_EXPORT_IMPORT',        18);

define('PAGE_SETTING_LOGIN',        20);
define('PAGE_SETTING_CATEGORY',     21);
define('PAGE_SETTING_LIST',         22);
define('PAGE_SETTING_EDIT',         23);

define('PAGE_YEARS_IN_ENERGY',      30);
define('PAGE_YEARS_OUT_ENERGY',     31);
define('PAGE_YEARS_IN_GAS',         32);

define('PAGE_YEAR_IN_ENERGY',       40);
define('PAGE_YEAR_OUT_ENERGY',      41);
define('PAGE_YEAR_IN_GAS',          42);

define('PAGE_MONTH_IN_ENERGY',      50);
define('PAGE_MONTH_OUT_ENERGY',     51);
define('PAGE_MONTH_IN_GAS',         52);

define('PAGE_DAY_IN_ENERGY',        60);
define('PAGE_DAY_OUT_ENERGY',       61);
define('PAGE_DAY_IN_GAS',           62);

define('PAGE_DAY_IN_KWH_EDIT',      63);
define('PAGE_DAY_OUT_KWH_EDIT',     64);
define('PAGE_DAY_IN_GAS_EDIT',      65);

define('PAGE_DAY_PRESSURE',         70);
define('PAGE_DAY_TEMPERATURE',      71);
define('PAGE_DAY_HUMIDITY',         72);

define('PAGE_DAY_IN_VOLTAGE',       80);
define('PAGE_DAY_IN_CURRENT',       81);
define('PAGE_DAY_IN_POWER',         82);

/*
** -----------
** EVENTS
** -----------
*/

define('EVENT_NONE',                10);
define('EVENT_PROCESS_TODAY',       11);
define('EVENT_PROCESS_ALL_DAYS',    12);
define('EVENT_PREV',                13);
define('EVENT_NEXT',                14);
define('EVENT_EXECUTE',             15);
define('EVENT_SAVE',                16);
define('EVENT_EURO',                17);
define('EVENT_KWH',                 18);
define('EVENT_M3',                  19);
define('EVENT_WATT',                20);
define('EVENT_CO2',                 21);
define('EVENT_MAX',                 22);
define('EVENT_BACKUP',              23);
define('EVENT_EXPORT',              24);
define('EVENT_SCATTER',             25);
define('EVENT_LOGIN',               26);
define('EVENT_SCHEME',              27);
define('EVENT_LANGUAGE',            28);
define('EVENT_DELETE',              29);

/*
** -----------
** CATEGORY
** -----------
*/

define('FORECAST',                  0);
define('GAS_METER_1',              11);
define('ENERGY_METER_1',           21);
define('SOLAR_METER_1',            31);
define('SOLAR_METER_2',            32);
define('SOLAR_METER_3',            33);
define('WEATHER_METER_1',          41);
define('SECURITY',                 51);
define('LOOK_AND_FEEL',            52);
define('NOTIFICATION',             81);

/*
** -----------
** CONSTANTS
** -----------
*/

// Energy use forecast (per month)
$in_forecast = array(0,100/1040,100/1040,90/1040,90/1040,80/1040,70/1040,70/1040,70/1040,80/1040,90/1040,100/1040,100/1040);

// Energy delivery forecast (per month)
$out_forecast = array(0,50/2550,100/2550,210/2550,310/2550,360/2550,360/2550,330/2550,290/2550,240/2550,160/2550,90/2550,50/2550);

// Gas use forecast (per month)
$gas_forecast = array(0,250/1500,220/1500,180/1500,110/1500,60/1500,40/1500,30/1500,30/1500,50/1500,110/1500,190/1500,230/1500);

// Energy co2 emission = Generate 1kWh electricity generate 0.997 kg co2
$kwh_to_co2_factor = 0.997;

// Burning 1m3 gas results in 1.78 kg CO2 emission
$m3_to_co2_factor = 1.78;

/*
** -----------
** PAGE
** -----------
*/

function plaatenergy_format_watt($value) {
	
	if (($value / 1000) > 0.0) {
		$value = $value / 1000;
		return number_format($value,2,',','.').' '.t('KW');
	} else { 
		return number_format($value,2,',','.').' '.t('WATT');
	}
}

function plaatenergy_dayofweek2($value) {

    list($year, $month, $day) = explode("-", $value);
    return jddayofweek( cal_to_jd(CAL_GREGORIAN, $month, $day, $year)); 
}

function plaatenergy_dayofweek($value) {

    list($year, $month, $day) = explode("-", $value);
    return t("DAY_".jddayofweek( cal_to_jd(CAL_GREGORIAN, $month, $day, $year))); 
}

/**
 * Language function 
 * @return Combine string in selected language
 */
function t() {

	global $lang;
	
   $numArgs = func_num_args();

   $temp = $lang[func_get_arg(0)];

   $pos = 0;
   $i = 1;

   while (($pos = strpos($temp, "%s", $pos)) !== false) {
      if ($i >= $numArgs) {
         throw new InvalidArgumentException("Not enough arguments passed.");
		}

      $temp = substr($temp, 0, $pos) . func_get_arg($i) . substr($temp, $pos + 2);
      $pos += strlen(func_get_arg($i));
      $i++;
   }      
	
	$temp = mb_convert_encoding($temp, "UTF-8", "HTML-ENTITIES" ); 
   return $temp; 
}

/**
 * Add title icon 
 */
function add_icons() {
	
	// Charset
	$page = '<meta charset="UTF-8">';
	
	// Normal icons
	$page .= '<link rel="shortcut icon" type="image/png" sizes="16x16" href="images/16.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="24x24" href="images/24.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="32x32" href="images/32.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="48x48" href="images/48.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="64x64" href="images/64.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="128x128" href="images/128.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="256x256" href="images/256.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="512x512" href="images/512.png">';
	
	// Apple icons
	$page .= '<link rel="apple-touch-icon" type="image/png" href="images/apple-60.png">';
	$page .= '<link rel="apple-touch-icon" type="image/png" sizes="76x76" href="images/apple-76.png">';
	$page .= '<link rel="apple-touch-icon" type="image/png" sizes="120x120" href="images/apple-120.png">';
	$page .= '<link rel="apple-touch-icon" type="image/png" sizes="152x152" href="images/apple-152.png">';
	
	// Web app cable (runs the website as app)
	$page .= '<meta name="apple-mobile-web-app-capable" content="yes">';
	$page .= '<meta name="mobile-web-app-capable" content="yes">';

	// Workarround to get transparant Google Charts
	//$page .= '<style>rect{fill:none;}</style>'; 
	
	// Title
	$page .= '<title>'.t('TITLE').'</title>';
	   
	return $page;
}

function loadCSS($url) {
	return '<link href="'.$url.'" rel="stylesheet" type="text/css" />';
	//return '<style>' . file_get_contents($url) . '</style>';
}


function loadJS($url) {
	return '<script language="JavaScript" src="'.$url.'" type="text/javascript"></script>';	
	//return '<script>' . file_get_contents($url) . '</script>';
}

/**
 * General header
 */
function general_header() {

	// input
	global $ip;
	global $pid;
	global $eid;
	global $sid;
	global $date;
	global $session;

	$sql  = 'select theme,language from session where ip="'.$ip.'"';
	$result = plaatenergy_db_query($sql);
	$row = plaatenergy_db_fetch_object($result);

	$lang = "en";
	$theme = "light";

	if (isset($row->language)) {
		$lang = $row->language;
		$theme = $row->theme;
	}
    
	$page  = '<!DOCTYPE html>';
	$page .= '<html>';
	$page .= '<head>'; 
	
	$page .= add_icons();

	$page .= loadJS('js/link.js');
	
	if ($pid != PAGE_REALTIME) {
		$page .= loadCSS('css/general1.css');

		// Load the icons from Font Awesome not with loadCSS because this file never will change and it cant load the font
		$page .= '<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css"/>';
	
		// Load the dark css theme if db theme var = dark
		if ($theme == "dark") {
		$page .= loadCSS('css/theme-dark.css');
		}
	}
  
	if ($pid == PAGE_REALTIME) {
		$page .= '<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">';
	}
	
   $slide_show_on = plaatenergy_db_get_config_item('slide_show_on', LOOK_AND_FEEL);
   if (($slide_show_on=="true") && ($pid!=PAGE_SETTING_LIST) && ($pid!=PAGE_SETTING_EDIT)) {

     $slide_show_page_delay = plaatenergy_db_get_config_item('slide_show_page_delay', LOOK_AND_FEEL)*1000;

     switch ($sid) {
   
          default: $pid = PAGE_YEAR_IN_ENERGY;
                   $eid = EVENT_KWH;
                   $sid = 2;
                   break;

          case 2:  $pid = PAGE_YEAR_OUT_ENERGY;
                   $eid = EVENT_KWH;
                   $sid = 3;
                   break;

          case 3:  $pid = PAGE_YEAR_IN_GAS;
                   $eid = EVENT_M3;
                   $sid = 4;
                   break;

          case 4:  $pid = PAGE_ABOUT;
                   $eid = EVENT_NONE;
                   $sid = 5;
                   break;
     }
     $page .= '<script>setTimeout(link,'.$slide_show_page_delay.',\'pid='.$pid.'&eid='.$eid.'&sid='.$sid.'\');</script>';
	}

	$page .= '</head>';
	
	$page .= '<body>';
	$page .= '<form id="plaatenergy" method="POST">';  
  
	$page .= '<input type="hidden" name="session" value="'.$session.'" />';
  
	if ($pid != PAGE_REALTIME) {	    
		$page .= '<div class="language">';
		if ($lang=="en") {
			$page .= plaatenergy_normal_link('pid='.$pid.'&eid='.$eid.'&date='.$date.'&sid='.EVENT_LANGUAGE, t('DUTCH'));
		} else { 
			$page .= plaatenergy_normal_link('pid='.$pid.'&eid='.$eid.'&date='.$date.'&sid='.EVENT_LANGUAGE , t('ENGLISH'));
		}
		$page .= '</div>';
		
		$page .= '<div class="theme">';
		if ($theme == "light") {
			$page .= plaatenergy_normal_link('pid='.$pid.'&eid='.$eid.'&date='.$date.'&sid='.EVENT_SCHEME, t('THEME_TO_DARK'));
		} else {
			$page .= plaatenergy_normal_link('pid='.$pid.'&eid='.$eid.'&date='.$date.'&sid='.EVENT_SCHEME, t('THEME_TO_LIGHT'));		
		}
		$page .= '</div>';
	}
   
	return $page;
}

/**
 * General footer
 */
function general_footer($time=0) {
	global $pid;
	
	$page = '';
	
	if ($pid != PAGE_REALTIME) {
		$page .= '<div class="copyright">'.t('LINK_COPYRIGHT') .' - '.t('TITLE').'<br/>';
		$page .= '['.round($time*1000).' ms - '.plaatenergy_db_count().' queries]';
		$page .= '</div>';
	}

	$page .= '</form>';
	$page .= '</body>';
	$page .= '</html>';
  
	return $page;
}

/**
 * Set cookie
 */
function set_cookie_and_refresh ($name, $value) {
	setcookie($name, $value, time() + (86400 * 30), "/");
	header("Location: " . $_SERVER['PHP_SELF']);
}

/**
 * Set icon to link
 */
function i ($name) { 
	$icon = '<i class="fa fa-' . $name;
	if ($name == 'chevron-right') {
		$icon .= ' right';
	}
	$icon .= ' fa-fw"></i>';
	return $icon;
}

// ----------------------------
// NAVIGATION
// ----------------------------

/**
 * Get previous day
 */
function plaatenergy_prev_day($date) {

  list($year, $month, $day) = explode("-", $date);

  $prev_day=$day-1;
  $prev_month=$month;
  $prev_year=$year;   

  if ($prev_day<=0) {
     $prev_month=$month-1;
     $prev_year=$year;
     $prev_day=date("t", strtotime($prev_year.'-'.$prev_month.'-1'));
  }

  if ($prev_month<=0) {
     $prev_month=12;
     $prev_year=$year-1;
     $prev_day=date("t", strtotime($prev_year.'-'.$prev_month.'-1'));
  }
  
  return $prev_year.'-'.$prev_month.'-'.$prev_day; 
}

/**
 * Get next day
 */
function plaatenergy_next_day($date) {

  list($year, $month, $day) = explode("-", $date);

  $next_day=$day+1;   
  $next_month=$month;
  $next_year=$year;   
  
  if ($next_day>date("t", strtotime($next_year.'-'.$next_month.'-1'))) {
     $next_day=1;
     $next_month=$next_month+1;
     $next_year=$year;
  }
  
  if ($next_month>12) {
     $next_day=1;
     $next_month=1;
     $next_year=$year+1;
  }
  
  return $next_year.'-'.$next_month.'-'.$next_day; 
}
 
/**
* Get previous year 
*/
function plaatenergy_prev_month($date) {

	list($year, $month) = explode("-", $date);

	$prev_year=$year;   
	$prev_month=$month-1;
	if ($prev_month<=0) {
		$prev_month=12;
		$prev_year=$year-1;
	}
  
	return $prev_year.'-'.$prev_month; 
}

/**
 * Get next month
 */
function plaatenergy_next_month($date) {

	list($year, $month) = explode("-", $date);

	$next_year=$year;   
	$next_month=$month+1;
	if ($next_month>12) {
		$next_month=1;
		$next_year=$year+1;
	}
  
	return $next_year.'-'.$next_month; 
}

/**
 * Get previous year 
 */
function plaatenergy_prev_year($year) {

	return $year-1;   
}

/**
 * Get next year 
 */
function plaatenergy_next_year($year) {

	return $year+1;  
}

function plaatenergy_get($label, $default) {
	
	$value = $default;
	
	if (isset($_GET[$label])) {
		$value = $_GET[$label];
		$value = stripslashes($value);
		$value = htmlspecialchars($value);
	}
	
	return $value;
}

/**
 * Process post parameters 
 */
function plaatenergy_post($label, $default) {
	
	$value = $default;
	
	if (isset($_POST[$label])) {
		$value = $_POST[$label];
		$value = stripslashes($value);
		$value = htmlspecialchars($value);
	}
	
	return $value;
}

/** 
 * Encode link data
 */
function plaatenergy_token_decode($token) {
	
	return htmlspecialchars_decode($token);
}

/** 
 * Encode link data
 */
function plaatenergy_token_encode($token) {
   
	return htmlspecialchars($token);	
}

/**
 * Create button like link 
 */
function plaatenergy_link($parameters, $label, $title="") {
   
	global $link_counter;
	
	$link_counter++;
	
	$link  = '<a href="javascript:link(\''.plaatenergy_token_encode($parameters).'\');" class="link" ';			
	
	if (strlen($title)!=0) {
		$link .= ' title="'.strtolower($title).'"';
	}
	
	$link .= '>'.$label.'</a>';	
	return $link;
}

/**
 * Create hyperlink like link 
 */
function plaatenergy_normal_link($parameters, $label, $id="", $title="") {
   
	global $link_counter;
	
	$link_counter++;
	
	$link  = '<a href="javascript:link(\''.plaatenergy_token_encode($parameters).'\');" class="normal_link" ';			
	if (strlen($id)!=0) {
		$link .= ' id="'.strtolower($id).'"';
	}
	if (strlen($title)!=0) {
		$link .= ' title="'.strtolower($title).'"';
	}
	$link .= '>'.$label.'</a>';	
	return $link;
}

function plaatenergy_navigation_day() {

	// input
	global $pid;
	global $eid;
	global $date;
	
	$page = '<div class="nav">';
	
	// If zero or one measurements are found. Measurement can be manually adapted.
	switch ($pid) {
		case PAGE_DAY_OUT_ENERGY:
			$sql = 'select * FROM solar1 where timestamp>="'.$date.' 00:00:00" and timestamp<="'.$date.' 23:59:59"';
			$result = plaatenergy_db_query($sql);
			if ( plaatenergy_db_num_rows($result) <= 1 ) {
				$page .= plaatenergy_link('pid='.PAGE_DAY_OUT_KWH_EDIT.'&date='.$date, t('LINK_EDIT'));			
			}
			break;
	
		case PAGE_DAY_IN_ENERGY:
			// If zero or one measurements are found. Measurement can be manully adapted.	
			$sql = 'select * FROM energy1 where timestamp>="'.$date.' 00:00:00" and timestamp<="'.$date.' 23:59:59"';
			$result = plaatenergy_db_query($sql);
			if ( plaatenergy_db_num_rows($result) <= 1 ) {
				$page .= plaatenergy_link('pid='.PAGE_DAY_IN_KWH_EDIT.'&date='.$date, t('LINK_EDIT'));			
			}
			break;
		
		case PAGE_DAY_IN_GAS:		
			$sql = 'select * FROM energy1 where timestamp>="'.$date.' 00:00:00" and timestamp<="'.$date.' 23:59:59"';
			$result = plaatenergy_db_query($sql);
			if ( plaatenergy_db_num_rows($result) <= 1) {
				$page .= plaatenergy_link('pid='.PAGE_DAY_IN_GAS_EDIT.'&date='.$date, t('LINK_EDIT'));			
			}
			break;
	}
	
	$page .= plaatenergy_link('pid='.$pid.'&eid='.$eid.'&date='.plaatenergy_prev_day($date), t('LINK_PREV_DAY'));
	$page .= plaatenergy_link('pid='.PAGE_HOME, t('LINK_HOME'));
	
	if (strtotime($date)<strtotime(date("Y-m-d"))) {		
		$page .= plaatenergy_link('pid='.$pid.'&eid='.$eid.'&date='.plaatenergy_next_day($date), t('LINK_NEXT_DAY'));	
	} else {
		$page .= plaatenergy_link('pid='.$pid.'&eid='.$eid.'&date='.$date, t('LINK_NEXT_DAY'));	
	}
		
	if ($eid==EVENT_KWH) {		
		$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_WATT,t('LINK_WATT'));	
	} else if ($eid==EVENT_WATT ) {
		$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_KWH,t('LINK_KWH'));		
	}
	
	$page .= '</div>';
  
	return $page;
}


function plaatenergy_navigation_month() {

	// input 
	global $pid;
	global $eid;
	global $date;
	
	$page  = '<div class="nav">';
	$page .= plaatenergy_link('pid='.$pid.'&eid='.$eid.'&date='.plaatenergy_prev_month($date),t('LINK_PREV_MONTH'));
	$page .= plaatenergy_link('pid='.PAGE_HOME, t('LINK_HOME'));
	
	if (strtotime($date) < strtotime(date("Y-m"))) {		
		$page .= plaatenergy_link('pid='.$pid.'&eid='.$eid.'&date='.plaatenergy_next_month($date), t('LINK_NEXT_MONTH'));	
	} else {
		$page .= plaatenergy_link('pid='.$pid.'&eid='.$eid.'&date='.$date, t('LINK_NEXT_MONTH'));	
	}

	if ($pid==PAGE_MONTH_IN_GAS) {
	
		switch ($eid) {
			
			case EVENT_EURO: 
				$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_M3,t('LINK_M3'));		
				break;
				
			default:
				$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_EURO,t('LINK_EURO'));	
				break;
		}
	}
	
	if ($pid == PAGE_MONTH_IN_ENERGY) {
	
		switch ($eid) {

			case EVENT_EURO:
				$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_KWH,t('LINK_KWH'));		
				break;
				
			default:	
				$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_EURO,t('LINK_EURO'));	
				break;
		}
	} 
	
	if ($pid == PAGE_MONTH_OUT_ENERGY) {
		switch ($eid) {
			
			case EVENT_EURO: 
				$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_MAX, t('LINK_MAX'));		
				break;
				
			case EVENT_MAX: 
				$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_KWH, t('LINK_KWH'));		
				break;
				
			default:	
				$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_EURO, t('LINK_EURO'));	
				break;
		}
	}
	
	$page .= '</div>';

	return $page;
}
		
function plaatenergy_navigation_year() {

	// input 
	global $pid;
	global $eid;
	global $date;
		
	$page  = '<div class="nav">';
	
	$page .= plaatenergy_link('pid='.$pid.'&date='.plaatenergy_prev_year($date).'&eid='.$eid,t('LINK_PREV_YEAR'));
	$page .= plaatenergy_link('pid='.PAGE_HOME, t('LINK_HOME'));
	
	if ($date < date("Y")) {		
		$page .= plaatenergy_link('pid='.$pid.'&date='.plaatenergy_next_year($date).'&eid='.$eid,t('LINK_NEXT_YEAR'));	
	} else { 
		$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.$eid,t('LINK_NEXT_YEAR'));	
	}
	
	if ($pid==PAGE_YEAR_IN_GAS)  {
	
		if ($eid==EVENT_M3) {		
			$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_EURO,t('LINK_EURO'));	
		} else {
			$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_M3,t('LINK_M3'));		
		}
		
	} else {

		if ($eid==EVENT_KWH) {		
			$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_EURO,t('LINK_EURO'));	
		} else if ($eid==EVENT_EURO) {
			$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_SCATTER,t('LINK_SCATTER'));		
		} else {
			$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_KWH,t('LINK_KWH'));		
      }
   }
	
	$page .= '</div>';	

	return $page;
}

function plaatenergy_navigation_years() {

	// input 
	global $pid;
	global $eid;
	global $date;
		
	$page  = '<div class="nav">';
	
	$page .= plaatenergy_link('pid='.$pid.'&date='.plaatenergy_prev_year($date).'&eid='.$eid,t('LINK_PREV_YEAR'));
	$page .= plaatenergy_link('pid='.PAGE_HOME, t('LINK_HOME'));
	
	if ($date < date("Y")) {		
		$page .= plaatenergy_link('pid='.$pid.'&date='.plaatenergy_next_year($date).'&eid='.$eid,t('LINK_NEXT_YEAR'));	
	} else { 
		$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.$eid,t('LINK_NEXT_YEAR'));	
	}
	
	if ($pid==PAGE_YEARS_IN_GAS) {
	
		if ($eid==EVENT_M3) {		
			$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_EURO,t('LINK_EURO'));	
		} else if ($eid==EVENT_EURO) {
			$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_CO2,t('LINK_CO2'));		
		} else {
			$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_M3,t('LINK_M3'));		
		}
		
	} else {

		if ($eid==EVENT_KWH) {		
			$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_EURO,t('LINK_EURO'));	
		} else if ($eid==EVENT_EURO) {
			$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_CO2,t('LINK_CO2'));		
		} else {
			$page .= plaatenergy_link('pid='.$pid.'&date='.$date.'&eid='.EVENT_KWH,t('LINK_KWH'));
		}
	}
	
	$page .= '</div>';	

	return $page;
}

function plaatenergy_create_path($path) {
    if (is_dir($path)) return true;
    $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
    $return = plaatenergy_create_path($prev_path);
    umask(0);
    return ($return && is_writable($prev_path)) ? mkdir($path, 0777) : false;
}

/** 
 * @mainpage PlaatEnergy Documentation
 *   Welcome to the PlaatEnergy documentation.
 *
 * @section Introduction
 *   PlaatEnergy collects information from your Energy meter, Gas meter, Solar meter and AstroPi 
 *   (weather station) and process all data. With a web GUI all data is presented.
 *
 * @section Links
 *   Website: http://www.plaatsoft.nl
 *   Code: https://github.com/wplaat/plaatenergy
 *
 * @section Credits
 *   Documentation: wplaat\n
 *
 * @section Licence
 *   <b>Copyright (c) 2008-2016 Plaatsoft</b>
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *   
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *   
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
// ----------------------------
// THE END
// ----------------------------



