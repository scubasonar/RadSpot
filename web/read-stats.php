<?php
	
	date_default_timezone_set ('America/New_York');
	include_once ("config.php");

	$id = trim($_GET["id"]);
	$label = trim($_GET["label"]);

	if ($id === "") {
		$id = 0;
	}

	if ($label === "") {
		$label = "";
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

	// Get maximum ID if none supplied
	if ($id == 0) {
		$sql = "SELECT max(id) as max FROM collector;";
		$results = mysql_query ($sql, $link);
		$row = mysql_fetch_array($results, MYSQL_ASSOC);
		$id = $row["max"]-5;
	}

	$sql = "SELECT id, locationkey, timestamp, latitude, longitude FROM collector WHERE id > $id AND label='$label' ORDER BY id ASC;";
	$results = mysql_query ($sql, $link);

	$return = "[";
	while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
		$return .= "{";
		$return .= "\"id\":\"".$row["id"]."\",";
		$return .= "\"key\":\"".$row["locationkey"]."\",";
		$return .= "\"timestamp\":\"".$row["timestamp"]."\",";
		$return .= "\"latitude\":\"".$row["latitude"]."\",";
		$return .= "\"longitude\":\"".$row["longitude"]."\"";
		$return .= "},";
	}
	$return .= "]";
	
	echo ($return);

?>