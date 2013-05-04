<?php	
	$crsId = $_ENV['REMOTE_USER'];

	include("database.php");
	
	$results = array();

	$con = openDatabase();

	$results = array("defaultTimes" => getDefaultTimes());

	closeDatabase($con);

	echo json_encode($results);
?>
