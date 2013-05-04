<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	$date = $_POST['date'];
	
	$con = openDatabase();

	if (!isset($crsId) || !isset($date)) {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else if (!userIsCommittee($crsId)) {
		$results = array("error" => "Insufficient rights to perform this operation");
	
	} else {
	
		if (strtotime($date) - strtotime(date("Y-m-d"))  > 13 * 24 * 60 * 60) {
			// If more than two weeks in advance (including today)
			
			$results = array("error" => "Cannot lookup workers for a shift more than 2 weeks in advance.");
			
		} else if (($workers = lookupWorkers($date)) !== false) {
			// Lookup who was working on this date.
			
			$results = $workers;
			
		} else {
			
			$results = array("error" => "Failed to update database.");
			
		}
	}
	
	closeDatabase($con);
	
	// Return JSON message with result of POST.
	echo json_encode($results);
?>
