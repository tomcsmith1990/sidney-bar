<?php

	$results = array();

	$results["crsId"] = $_ENV['REMOTE_USER'];
	
	include("database.php");
	
	$con = openDatabase();
	
	$results["user"] = getUser($results["crsId"]);
	
	closeDatabase($con);

	echo json_encode($results);
?>
