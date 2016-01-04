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
**  All copyrights reserved (c) 2008-2015 PlaatSoft
*/
 
include "config.inc";
include "general.inc";

day_parameters();

$conn = new mysqli($servername, $username, $password, $dbname);

$type=0;
$first=1;
$value_first=0;
$i=0;
$value=0;
$total=0;
$power = array();
$data="";

while ($i<97) {

   $timestamp = date("Y-m-d H:i:s", $current_date+(900*$i));
   $sql = 'select gas FROM energy where timestamp="'.$timestamp.'"';
   $result = $conn->query($sql);
   $row = $result->fetch_assoc();

   if ($timestamp>date("Y-m-d H:i:s")) {
     $value=0;
     break;
   }

   if ( isset($row['gas'])) {

        if ($first==1) {
                $value_first= $row['gas'];
                $first=0;
        }
        $value= $row['gas']-$value_first;
   }
   if (strlen($data)>0) {
     $data.=',';
   }
   $data .= "['".date("H:i", $current_date+(900*$i))."',";
   $data .= round($value,2).']';
   $total = round($value,2);
   $i++;
}

$json = "[['','".t('USED_M3')."'],".$data."]";

general_header();

?>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["bar"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {

       var options = {
          bar: {groupWidth: "90%"},
          legend: { position: 'none' },
          isStacked: true,
	  vAxis: {format: 'decimal'},
        };

        var data = google.visualization.arrayToDataTable(<?php echo $json?>);
        var chart = new google.charts.Bar(document.getElementById('chart_div'));
        chart.draw(data, google.charts.Bar.convertOptions(options));
      }
    </script>
    
<?php

echo '<h1>'.t('TITLE_DAY_IN_GAS', $day, $month, $year).'</h1>';
echo '<div id="chart_div" style="width: '.$graph_width.'; height: '.$graph_height.';"></div>';

text_banner( t('TOTAL_PER_DAY_KWH', $total));
day_navigation();

?>