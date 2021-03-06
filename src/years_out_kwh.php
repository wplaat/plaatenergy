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
 * @brief contain years out energy report
 */
 
/*
** ---------------------
** PAGES
** ---------------------
*/

function plaatenergy_years_out_energy_page() {

	// input
	global $pid;
	global $eid;

	global $date; 
	global $out_forecast;
	global $kwh_to_co2_factor;
	
	$prev_date = plaatenergy_prev_year($date);
	$next_date = plaatenergy_next_year($date);
	
	list($year) = explode("-", $date);	

	$energy_price = plaatenergy_db_get_config_item('energy_price', ENERGY_METER_1);
	$energy_delivery_forecast = plaatenergy_db_get_config_item('energy_delivery_forecast');

	$total_sum = 0;
	$total_price = 0;
	$total_co2 = 0;
	$total_max = 0;
	$year_count = 0;
	$data = "";
	
	for($y=($year-10); $y<=$year; $y++) {
	
		$delivered_low_total = 0;
		$delivered_normal_total = 0;
		$delivered_local_total = 0;		
		$total = 0;
		$forecast_factor = 0;
		$month_count = 0;
		
		for($m=1; $m<=12; $m++) {

			$delivered_low = 0;
			$delivered_normal = 0;
			$delivered_local = 0;
					
			$time=mktime(0, 0, 0, $m, 1, $y);
			$timestamp1=date('Y-m-0 00:00:00', $time);
			$timestamp2=date('Y-m-t 23:59:59', $time);
		
			$sql1  = 'select sum(low_delivered) as low_delivered, sum(normal_delivered) as normal_delivered, ';
			$sql1 .= 'sum(solar_delivered) as solar_delivered from energy_summary ';
			$sql1 .= 'where date>="'.$timestamp1.'" and date<="'.$timestamp2.'"';
		
			$result1 = plaatenergy_db_query($sql1);
			$row1 = plaatenergy_db_fetch_object($result1);
	
			if ( isset($row1->solar_delivered)) {		
				// Use realtime solar information
				
				$month_count++;			
				$delivered_low = $row1->low_delivered;
				$delivered_normal = $row1->normal_delivered;
				$tmp = $row1->solar_delivered - $delivered_low -$delivered_normal;
				if ($tmp >0 ) {
					$delivered_local=$tmp;
				}				
				$forecast_factor += $out_forecast[$m];

			} else {			
				// if realtime information is not there. Check if there is solar history information available
				
				$sql2  = 'select energy from solar_history where date>="'.$timestamp1.'" and date<="'.$timestamp2.'"';
				$result2 = plaatenergy_db_query($sql2);
				$row2 = plaatenergy_db_fetch_object($result2);
				
				if ( isset($row2->energy)) { 				
					$month_count++;									
					$delivered_local=$row2->energy;					
					$forecast_factor += $out_forecast[$m];
				}
			}
			
			$delivered_low_total += $delivered_low;
			$delivered_normal_total += $delivered_normal;
			$delivered_local_total += $delivered_local;
				
			$total += $delivered_low + $delivered_normal + $delivered_local;			
		}

		if ($month_count>0) {
			$year_count ++;
		}
		
		$forecast_total = $forecast_factor * $energy_delivery_forecast;
	
		if (strlen($data)>0) {
			$data.=',';
		}
		
		$price2 = $total * $energy_price;
		$data .= "['".date("Y", $time)."',";
		if ($eid==EVENT_KWH) {	
			$data .= round($delivered_low_total,2).',';
			$data .= round($delivered_normal_total,2).',';
			$data .= round($delivered_local_total,2).',';
			$data .= round($forecast_total,2).']';
			
		} else if ($eid==EVENT_CO2) {
			$data .= round(($delivered_low_total + $delivered_normal_total + $delivered_local_total)*$kwh_to_co2_factor,2).',';
			$data .= round(($forecast_total*$kwh_to_co2_factor),2).']';
			
		} else { 
			$data .= round($price2,2).']';
		}
		
		$total_sum += $total;
		$total_co2 += $total * $kwh_to_co2_factor;
		$total_price += $price2;
		
		if ($total>$total_max) {
			$total_max = $total;
		}

		if ($forecast_total > $total_max) {
			$total_max = $forecast_total;
		}
	}

	if ($eid==EVENT_KWH) {
		$json = "[['','".t('DELIVERED_LOW_KWH')."','".t('DELIVERED_NORMAL_KWH')."','".t('DELIVERED_LOCAL_KWH')."','".t('FORECAST_KWH')."'],".$data."]";
	} else if ($eid==EVENT_CO2) {
		$json = "[['','".t('REDUCTION_CO2')."','".t('FORECAST_CO2')."'],".$data."]";		
	} else { 
		$json = "[['','".t('EURO')."'],".$data."]";
	}
	
	$page = '
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["bar"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() { ';

	if ($eid==EVENT_KWH) {
		$page .= '
		
		 var options = {
          bars: "vertical",
          bar: {groupWidth: "90%"},
          legend: { position: "'.plaatenergy_db_get_config_item('chart_legend',LOOK_AND_FEEL).'", textStyle: {fontSize: 10} },
          vAxis: {format: "decimal"},
          isStacked:true,
			 backgroundColor: "transparent",
			 chartArea: {
            backgroundColor: "transparent"
          },
		
			colors: ["#0066cc", "#808080"],
				vAxis: {   
					format:"decimal", 
					viewWindow: { min: 0, max: "'.round($total_max+60).'" },
				},';
				
	} else if ($eid==EVENT_CO2) {			 
		$page .= '
		
		var options = {
          bars: "vertical",
          bar: {groupWidth: "90%"},
          legend: { position: "'.plaatenergy_db_get_config_item('chart_legend',LOOK_AND_FEEL).'", textStyle: {fontSize: 10} },
          vAxis: {format: "decimal"},
          isStacked:false,
			 backgroundColor: "transparent",
			 chartArea: {
            backgroundColor: "transparent"
          },
			 
			 colors: ["#e0ee20", "#808080"],';				
			 
	} else {
	
		$page .= '
		
		var options = {
          bars: "vertical",
          bar: {groupWidth: "90%"},
          legend: { position: "'.plaatenergy_db_get_config_item('chart_legend',LOOK_AND_FEEL).'", textStyle: {fontSize: 10} },
          vAxis: {format: "decimal"},
          isStacked:false,
			 backgroundColor: "transparent",
			 chartArea: {
            backgroundColor: "transparent"
          },
			 colors: ["#e0440e"],';
	}
		
	$page .= 'series: {
            0: { targetAxisIndex: 0 },
            1: { targetAxisIndex: 0 },
            2: { targetAxisIndex: 0 },
            3: { targetAxisIndex: 1 },
          },
        };

        var data = google.visualization.arrayToDataTable('.$json.');
        var chart = new google.charts.Bar(document.getElementById("chart_div"));
        chart.draw(data, google.charts.Bar.convertOptions(options));

        google.visualization.events.addListener(chart, "select", selectHandler);

        function selectHandler(e)     {
           var year = data.getValue(chart.getSelection()[0].row, 0);
			  link("pid='.PAGE_YEAR_OUT_ENERGY.'&eid='.$eid.'&date="+year+"-1-1");
        }
      }
    </script>';
    
	$page .= '<h1>'.t('TITLE_YEARS_OUT_KWH', ($year-10), $year).'</h1>';
	$page .= '<div id="chart_div" style="'.plaatenergy_db_get_config_item('chart_dimensions',LOOK_AND_FEEL).'"></div>';

	$page .= '<div class="remark">';
	if ($year_count>0) {
		if ($eid==EVENT_KWH) {
			$page .= t('AVERAGE_PER_YEAR_KWH', round(($total_sum/$year_count),2), round($total_sum,2));
		} else if ($eid==EVENT_CO2) {
			$page .= t('AVERAGE_PER_YEAR_CO2_REDUCTION', round(($total_co2/$year_count),2), round($total_co2,2));			
		} else {
			$page .= t('AVERAGE_PER_YEAR_EURO', round(($total_price/$year_count),2), round($total_price,2));
		}
	} else {
		$page .= '&nbsp;';
	}
	$page .= '</div>';		

	$page .= plaatenergy_navigation_years();

	return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

function plaatenergy_years_out_energy() {

  /* input */
  global $pid;
  global $eid;
  
   /* Event handler */
  switch ($eid) {
  
		case EVENT_KWH:
				break;
				
		case EVENT_EURO:
				break;
	}
	
	/* Page handler */
	switch ($pid) {

		case PAGE_YEARS_OUT_ENERGY:
			return plaatenergy_years_out_energy_page();
			break;
	}
}

/*
** ---------------------
** THE END
** ---------------------
*/
