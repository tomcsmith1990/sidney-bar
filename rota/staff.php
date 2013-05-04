<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	
	$con = openDatabase();

	if (!userIsCommittee($crsId)) {
		$results = array("error" => "Insufficient rights to perform this operation");
	
	} else {
		$results = array("staff" => getStaff());
	}
	
	closeDatabase($con);

	// Return JSON message with result of POST.
	echo json_encode($results);
	
?>
