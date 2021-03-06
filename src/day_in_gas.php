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
 * @brief contain day gas report
 */
 
/*
** ---------------------
** PAGES
** ---------------------
*/

function plaatenergy_day_in_gas_page() {

	// input
	global $pid;
	global $eid;

	global $date; 
	global $gas_forecast;
	
	$prev_date = plaatenergy_prev_day($date);
	$next_date = plaatenergy_next_day($date);
	
	list($year, $month, $day) = explode("-", $date);	
        $day = ltrim($day ,'0');
        $month = ltrim($month ,'0');

	$current_date=mktime(0, 0, 0, $month, $day, $year);  
	
	$gas_price = plaatenergy_db_get_config_item('gas_price', GAS_METER_1);
	$gas_use_forecast = plaatenergy_db_get_config_item('gas_use_forecast');
	$gas_prev = plaatenergy_db_get_config_item('meter_reading_used_gas', GAS_METER_1);
	
	$i = 0;
	$data = "";
	$page = "";
	$value = 0;
	$total = 0;
	
	// Get last energy measurement 
	$sql  = 'select gas_used from energy1 where ';
	$sql .= 'timestamp>="'.$prev_date.' 00:00:00" and timestamp<="'.$prev_date.' 23:59:59" order by timestamp desc limit 0,1';	
	$result = plaatenergy_db_query($sql);
	$row = plaatenergy_db_fetch_object($result);

	if ( isset($row->gas_used) ) {
		$gas_prev = $row->gas_used;
	}      
	
	while ($i<96) {

		$timestamp = date("Y-m-d H:i:s", $current_date+(900*$i));
		$sql = 'select gas_used FROM energy1 where timestamp="'.$timestamp.'"';
		$result = plaatenergy_db_query($sql);
		$row = plaatenergy_db_fetch_object($result);
	
		if ($timestamp>date("Y-m-d H:i:s")) {
			$value=0;
		}

		if ( isset($row->gas_used)) {
			$value= $row->gas_used - $gas_prev;
			$total = $value;
		}
  
		if (strlen($data)>0) {
			$data.=',';
		}
		$data .= "['".date("H:i", $current_date+(900*$i))."',";
		$data .= round($value,2).']';
		
		$i++;
	}		

	$json = "[['','".t('USED_M3')."'],".$data."]";

	$page = '
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["bar"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {

			var options = {
				bar: {groupWidth: "90%"},
            legend: { position: "'.plaatenergy_db_get_config_item('chart_legend',LOOK_AND_FEEL).'", textStyle: {fontSize: 10} },
				isStacked: true,
				vAxis: {format: "decimal"},
				backgroundColor: "transparent",
			   chartArea: {
              backgroundColor: "transparent"
            }
			};

			var data = google.visualization.arrayToDataTable('.$json.');
			var chart = new google.charts.Bar(document.getElementById("chart_div"));
			chart.draw(data, google.charts.Bar.convertOptions(options));
      }
		</script>';
    
	$page .= '<h1>'.t('TITLE_DAY_IN_GAS', plaatenergy_dayofweek($date), $day, $month, $year).'</h1>';
        $page .= '<div id="chart_div" style="'.plaatenergy_db_get_config_item('chart_dimensions',LOOK_AND_FEEL).'"></div>';

        $forecast = ($gas_forecast[$month] * $gas_use_forecast) / cal_days_in_month (CAL_GREGORIAN, $month, $year);

	$page .= '<div class="remark">';
	$page .= t('TOTAL_PER_DAY_M3', round($total,2), round($forecast,2));
	$page .= '</div>';
		
	$page .= plaatenergy_navigation_day();
	
	return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

function plaatenergy_day_in_gas() {

  /* input */
  global $pid;
  global $eid;
  
   /* Event handler */
  switch ($eid) {
     
		case EVENT_SAVE:
				plaatenergy_day_in_gas_edit_save_event();
				break;

		case EVENT_M3:
				break;
				
		case EVENT_EURO:
				break;
	}
	
	/* Page handler */
	switch ($pid) {

		case PAGE_DAY_IN_GAS:
			return plaatenergy_day_in_gas_page();
			break;
	}
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>
