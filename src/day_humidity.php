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
 * @brief contain day humidity report
 */
 
/*
** ---------------------
** PAGE
** ---------------------
*/

function plaatenergy_day_humidity_page() {

   // input
	global $pid;
	global $date;  
		
	list($year, $month, $day) = explode("-", $date);	
	$current_date = mktime(0, 0, 0, $month, $day, $year);
	
	$i = 0;
	$data = "";
	$value = 0;
	$min = 100;
	$max = 0;
	$found = 0;
	
	while ($i<97) {

		$timestamp = date("Y-m-d H:i:s", $current_date+(900*$i));
		$sql = 'select humidity from weather where timestamp="'.$timestamp.'"';
	
		$result = plaatenergy_db_query($sql);
		$row = plaatenergy_db_fetch_object($result);
		
		if ($timestamp>date("Y-m-d H:i:s")) {
			$value = 0;
		} else if ( isset($row->humidity)) {
			$value= $row->humidity;
			$found = 1;
		
		  if ($value>$max) {
			$max=$value;
		  }
		
		  if ($value<$min) {
			$min=$value;
		  }
                }
		
		if (strlen($data)>0) {
			$data.=',';
		}
		$data .= "['".date("H:i", $current_date+(900*$i))."',";
		$data .= round($value,2).']';

		$i++;
	}

	$json = "[['','".t('HUMIDITY')."'],".$data."]";

	$page = '
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["bar"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {

        var options = {
          bars: "vertical",
          bar: {groupWidth: "90%"},
          legend: { position: "'.plaatenergy_db_get_config_item('chart_legend',LOOK_AND_FEEL).'", textStyle: {fontSize: 10} },
          vAxis: {format: "decimal", baseline:0},
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
	 
	$page .= '<h1>'.t('TITLE_DAY_HUMIDITY', plaatenergy_dayofweek($date), $day, $month, $year).'</h1>';
        $page .= '<div id="chart_div" style="'.plaatenergy_db_get_config_item('chart_dimensions',LOOK_AND_FEEL).'"></div>';
		
	$page .= '<div class="remark">';		
	if ($found == 1) {
		$page .= t('MIN_MAX_HUMIDITY',$min,$max);
	} else {
		$page .= '&nbsp;';
	}
	$page .- '</div>';
	
	$page .= plaatenergy_navigation_day();
		
	return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

function plaatenergy_day_humidity() {

  /* input */
  global $pid;

  /* Page handler */
  switch ($pid) {

     case PAGE_DAY_HUMIDITY:
        return plaatenergy_day_humidity_page();
        break;
  }
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>
