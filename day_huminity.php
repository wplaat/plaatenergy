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
 
include "config.inc";
include "general.inc";
include "database.inc";

day_parameters();

plaatenergy_db_connect($dbhost, $dbuser, $dbpass, $dbname);

$i=0;
$label="";
$data="";
$value=0;
$type=0;

while ($i<97) {

   $timestamp = date("Y-m-d H:i:s", $current_date+(900*$i));
   $sql = 'select humidity from weather where timestamp="'.$timestamp.'"';
	
	$result = plaatenergy_db_query($sql);
   $row = plaatenergy_db_fetch_object($result);
	
   if ($timestamp>date("Y-m-d H:i:s")) {
     $value=0;
     break;
   }

   if ( isset($row->humidity)) {
        $value= $row->humidity;
   }

   if (strlen($data)>0) {
     $data.=',';
   }
   $data .= "['".date("H:i", $current_date+(900*$i))."',";
   $data .= round($value,1).']';

   $i++;
}

$json = "[['','".t('HUMINITY')."'],".$data."]";

general_header();

?>

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["bar"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {

        var options = {
          bars: 'vertical',
          bar: {groupWidth: "90%"},
          legend: { position: 'none' },
          vAxis: {format: 'decimal', baseline:0},
        };

        var data = google.visualization.arrayToDataTable(<?php echo $json?>);
        var chart = new google.charts.Bar(document.getElementById('chart_div'));
        chart.draw(data, google.charts.Bar.convertOptions(options));
      }
    </script>
  
<?php

echo '<h1>'.t('TITLE_DAY_HUMINITY', $day, $month, $year).'</h1>';
echo '<div id="chart_div" style="width: '.$graph_width.'; height: '.$graph_height.';"></div>';

day_navigation();
general_footer();

?>