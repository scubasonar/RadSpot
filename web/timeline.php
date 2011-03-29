<html>
  <head>
	<meta http-equiv="refresh" content="600">
    <script type='text/javascript' src='http://www.google.com/jsapi'></script>
    <script type='text/javascript'>
      google.load('visualization', '1', {'packages':['annotatedtimeline']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('datetime', 'Time');
        data.addColumn('number', 'Events');
        data.addColumn('string', 'title1');
        data.addColumn('string', 'text1');
        data.addRows([
        

<?php
	
	date_default_timezone_set ('America/New_York');
	//date_default_timezone_set ('UTC');
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
		$events[] = round ($row["timestamp"]/1000, 0);//+(60*60);
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
	//$step	= 1;

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

			$rowcount++;

			$year	= date ("Y", $i);
			$month	= date ("n", $i);
			$day	= date ("j", $i);
			$hour	= date ("G", $i);
			$minute	= date ("i", $i);
			$second	= date ("s", $i);
			
			//new Date(year, month, day, hours, minutes, seconds, milliseconds)

			//echo ("[new Date($year, $month ,$day, $hour, $minute, 0, 0), $count, undefined, undefined],\n");
			echo ("[new Date(".($j*1000)."), $count, undefined, undefined],\n");
			//$buffer.= "dataTable.setValue(".$rowcount.", 0, $count);\n";
			//$buffer.= "dataTable.setValue(".$rowcount.", 1, $rowcount);\n";
			
			//if (isset($distribution["".$count])) {
			//	$distribution["".$count]++;
			//} else {
			//	$distribution["".$count]=1;
			//}
			//$countlist[] = $count;
			//$lastcount = $count;
		}
		$count = 0;
	}
	
	/*$lastcount = $countlist[sizeof($countlist) - 2];
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
	}*/

	//echo ("dataTable.addRows($rowcount);\n");
	//echo ("dataTable.addColumn('number', 'Events / Minute');\n");
	//echo ("dataTable.addColumn('number', 'Observed');\n");
	//echo ($buffer);
	
?>
	 ]);
	var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('chart_div'));
			chart.draw(data, {displayAnnotations: true});
	}
    </script>
  </head>

  <body topmargin="0" leftmargin="0">
    <div id='chart_div' style='width: 400px; height: 300px; overflow: none;'></div>

  </body>
</html>