<?php
	include_once ("config.php");

	$intensity	= $_GET['i'];
	$timestamp	= $_GET['t'];
	$latitude	= $_GET['lat'];
	$longitude	= $_GET['lon'];
	$altitude	= $_GET['alt'];
	$course		= $_GET['cou'];
	$speed		= $_GET['spd'];
	$label		= $_GET['lbl'];

	/*$intensity	= 1;
	$timestamp	= 1;
	$latitude	= 1;
	$longitude	= 1;
	$altitude	= 1;
	$course		= 1;
	$speed		= 1;
	$label		= "test";*/

	
	
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

	// Look for key
	$id = 0;
	$sql = "SELECT id FROM location WHERE latitude=$latitude AND longitude=$longitude;";
	$results = mysql_query ($sql, $link);
	if ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
		$id = $row["id"];
	} else {
		$sql = "INSERT INTO location VALUES (null, $latitude, $longitude);";
		$results = mysql_query ($sql, $link);
		$sql = "SELECT id FROM location WHERE latitude=$latitude AND longitude=$longitude;";
		$results = mysql_query ($sql, $link);
		$row = mysql_fetch_array($results, MYSQL_ASSOC);
		$id = $row["id"];
	}

	$sql = "INSERT INTO collector VALUES (null, $intensity, $timestamp, '$label', $latitude, $longitude, $id, $altitude, $course, $speed);";
	$results = mysql_query ($sql, $link);

	echo "OK 200";
	
?>