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
	
	$prev_date = plaatenergy_prev_year($date);
	$next_date = plaatenergy_next_year($date);
	
	list($year) = explode("-", $date);	

	$energy_price = plaatenergy_db_get_config_item('energy_price');
	$energy_delivery_forecast = plaatenergy_db_get_config_item('energy_delivery_forecast');

	$total_sum=0;
	$total_price=0;
	$total_max=0;
	$count=0;
	$data="";
	
	for($y=($year-10); $y<=$year; $y++) {
	
		$time = mktime(0, 0, 0, 1, 1, $y);
		$timestamp1 = date('Y-1-1', $time);
		$timestamp2 = date('Y-12-t', $time);
	
		$sql1  = 'select sum(dalterug) as dalterug, sum(piekterug) as piekterug, ';
		$sql1 .= 'sum(solar) as solar from energy_day ';
		$sql1 .= 'where date>="'.$timestamp1.'" and date<="'.$timestamp2.'"';
	
		$result1 = plaatenergy_db_query($sql1);
		$row1 = plaatenergy_db_fetch_object($result1);

		$delivered_low=0;
		$delivered_normal=0;
		$delivered_local=0;
		$total = 0;
	
		if ( isset($row1->solar)) {
			$count++;
			
			$delivered_low = $row1->dalterug;
			$delivered_normal = $row1->piekterug;
			$tmp = $row1->solar - $delivered_low -$delivered_normal;
			if ($tmp >0 ) {
				$delivered_local=$tmp;
			}
			$total = $delivered_low + $delivered_normal + $delivered_local;
		}
	
		$sql2  = 'select month(date) as month from energy_day ';
		$sql2 .= 'where date>="'.$timestamp1.'" and date<="'.$timestamp2.'" ';
		$sql2 .= 'group by month ';
		$result2 = plaatenergy_db_query($sql2);
	
		$forecast_total=0;
		while ($row2 = plaatenergy_db_fetch_object($result2)) {
			if (isset($row2->month)) {
				$forecast_total += $out_forecast[$row2->month];
			}
		}
	
		if (strlen($data)>0) {
			$data.=',';
		}
		
		$price2 = $total * $energy_price;
		$data .= "['".date("Y", $time)."',";
		if ($eid==EVENT_KWH) {	
			$data .= round($delivered_low,2).',';
			$data .= round($delivered_normal,2).',';
			$data .= round($delivered_local,2).',';
			$data .= round(($forecast_total*$energy_delivery_forecast),2).']';
		} else { 
			$data .= round($price2,2).']';
		}
		$total_sum += $total;
		$total_price += $price2;
		
		if ($total>$total_max) {
			$total_max=$total;
		}
	}

	if ($eid==EVENT_KWH) {
		$json = "[['','".t('DELIVERED_LOW_KWH')."','".t('DELIVERED_NORMAL_KWH')."','".t('DELIVERED_LOCAL_KWH')."','".t('FORECAST_KWH')."'],".$data."]";
	} else { 
		$json = "[['','".t('EURO')."'],".$data."]";
	}
	
	$page = '
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["bar"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {

      var options = {
          bars: "vertical",
          bar: {groupWidth: "90%"},
          legend: { position: "'.plaatenergy_db_get_config_item('chart_legend').'", textStyle: {fontSize: 10} },
          vAxis: {format: "decimal"},
          isStacked:true,';
			 
	if ($eid==EVENT_KWH) {
		$page .= '
                         colors: ["#0066cc", "#808080"],
		         vAxis: {   
                                   format:"decimal", 
                                   viewWindow: { min: 0, max: "'.round($total_max+50).'" },
                                 },';
	} else {
		$page .= 'colors: ["#e0440e"],';
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
        $page .= '<div id="chart_div" style="'.plaatenergy_db_get_config_item('chart_dimensions').'"></div>';

	$page .= '<div class="remark">';
	if ($count>0) {
			if ($eid==EVENT_KWH) {
			$page .= t('AVERAGE_PER_YEAR_KWH', round(($total_sum/$count),2), round($total_sum,2));
		} else {
			$page .= t('AVERAGE_PER_YEAR_EURO', round(($total_price/$count),2), round($total_price,2));
		}
	} else {
		$page .= '&nbsp;';
	}
	$page .= '</div>';		

	$page .= plaatenergy_navigation_year();

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
