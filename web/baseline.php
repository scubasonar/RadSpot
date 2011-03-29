<html>
  <head>
	<meta http-equiv="refresh" content="600">
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var dataTable = new google.visualization.DataTable();
<?php
	
	date_default_timezone_set ('America/New_York');
	include_once ("config.php");

	$mode = trim($_GET['m']);
	if ($mode === "") {
		$mode = "";
	}
	

	/**
	 * Connect to database
	 */
	$link = mysql_connect ($databaseHost, $databaseUser, $databasePass);
	if (!$link) {
		die ("Not Connected " . mysql_error());
		// TODO: LOG
	}
	$db_selected = mysql_select_db ($databaseName, $link);
	if (!$db_selected) {
		die ("Can't Use " . mysql_error());
		// TODO: LOG
	}

	$sql = "SELECT timestamp FROM collector WHERE label='$mode' ORDER BY timestamp ASC;";
	$results = mysql_query ($sql, $link);
	
	// Store data
	$events = array();
	while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
		$events[] = round ($row["timestamp"]/1000, 0)+(60*60);
	}

	// Find starting minute
	
	$time	= $events[0];
	$year	= date ("Y", $time);
	$month	= date ("n", $time);
	$day	= date ("j", $time);
	$hour	= date ("G", $time);
	$minute	= date ("i", $time);
	$second	= date ("s", $time);

	$start	= mktime ($hour,$minute,0,$month,$day,$year);

	// Find ending minute
	
	$time	= $events[sizeof($events)-1];
	$year	= date ("Y", $time);
	$month	= date ("n", $time);
	$day	= date ("j", $time);
	$hour	= date ("G", $time);
	$minute	= date ("i", $time);
	$second	= date ("s", $time);

	$stop	= mktime ($hour,$minute+1,0,$month,$day,$year);

	// Resolution = 1 minute
	$step	= 60;

	// Index
	$index = 0;
	$buffer = "";
	$rowcount = 0;
	$distribution = array();
	$std_key = array();
	$std_val = array();
	$lastcount = 0;
	$countlist = array ();
	
	// Loop through records
	for ($i = $start; $i < $stop-1; $i += $step) {
	
		// Get endpoint
		$j = $i + $step;
		
		// Look for events
		while (($events[$index] >= $i) && ($events[$index] < $j)) {
			$count++;
			$index++;
		}

		if ($count > 0) {
			//$buffer.= "dataTable.setValue(".$rowcount.", 0, $count);\n";
			//$buffer.= "dataTable.setValue(".$rowcount.", 1, $rowcount);\n";
			$rowcount++;
			if (isset($distribution["".$count])) {
				$distribution["".$count]++;
			} else {
				$distribution["".$count]=1;
			}
			$countlist[] = $count;
			//$lastcount = $count;
		}
		$count = 0;
	}
	
	$lastcount = $countlist[sizeof($countlist) - 2];
	$acum = 0;
	$sums = 0;

	foreach ($distribution as $key=>$value) {
		$acum += ($key * $value);
		$sums += $value;
		$buffer.= "dataTable.setValue(".$rowcount.", 0, $key);\n";
		$buffer.= "dataTable.setValue(".$rowcount.", 1, $value);\n";
		$rowcount++;
	}
	
	$mean = $acum/$sums;
	$diff = array();
	$sqrd = array();

	$valsum = 0;
	$keysum = 0;
	$sqrdsum = 0;

	foreach ($distribution as $key=>$value) {

		$keysum += ($key * $value);
		$valsum += $value;

		$diff["".$key] = $key - $mean;
		$sqrd["".$key] = $value*$value;

		$sqrdsum += $sqrd["".$key];
	}

	echo ("dataTable.addRows($rowcount);\n");
	echo ("dataTable.addColumn('number', 'Events / Minute');\n");
	echo ("dataTable.addColumn('number', 'Observed');\n");
	echo ($buffer);
	
?>
	
var chart = new google.visualization.ScatterChart(document.getElementById('chart_div'));
        chart.draw(dataTable, {width:  '100%', height: '100%',
                          title: 'Event Count Distribution (Counts / Minute)',
                          hAxis: {title: 'Events / Minute', minValue: 0, maxValue: 30},
                          vAxis: {title: 'Observed', minValue: 0, maxValue: 30},
                          legend: 'none'
		});
	}
    </script>
  </head>

  <body topmargin="0" leftmargin="0">
    <div id="chart_div" style="width:400px; height: 300px; overflow: none;"></div>
	<!--div id="info">
		<pre>
			Dataset: <?php print $mode ?><br>
			Event Ranges: <?php print date (DATE_ATOM, $start); print " <b>TO</b> "; print date (DATE_ATOM, $stop); ?><br>
			Total Events: <?php print ($rowcount); ?><br>
			Mean: <?php print ($mean); ?><br>
			Stddev: <?php print sqrt(($sqrdsum/$valsum)); ?><br>
			Last Event: <?php print ($lastcount); ?><br>
			Number of deviations: <?php print abs($lastcount-$mean)/sqrt($sqrdsum/$valsum); ?><br>
			Last 10 minutes event counts: <div style=float: left;>
			<table border=1>
				<tr>
					<?php
						for ($i = sizeof($countlist)-10; $i < sizeof($countlist); $i++) {
							if ($i == sizeof($countlist)-1) {
								print "<td width=20 style='background-color: green;'><b>".$countlist[$i]."</b></td>\n";
							} else {
								print "<td width=20>".$countlist[$i]."</td>\n";
							}
						}
					?>
				</tr>
			</table>
			</div>
		</pre>
		Mean: <?php echo ($mean); ?><br>
		x-Mean: <pre><?php print_r ($diff); ?></pre><br>
		x-Sqrd: <pre><?php print_r ($sqrd); ?></pre>
a
		valsum: <pre><?php print ($valsum); ?></pre>
		keysum: <pre><?php print ($keysum); ?></pre>
		sqrdsum: <pre><?php print ($sqrdsum); ?></pre>
		variance: <pre><?php print ($sqrdsum/$valsum); ?></pre>
		stddev: <pre><?php print sqrt(($sqrdsum/$valsum)); ?></pre>


	</div-->

  </body>
</html>