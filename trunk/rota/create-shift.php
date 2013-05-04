<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	$date = $_POST['date'];
	$experiencedOnly = $_POST['experiencedOnly'];
	
	$con = openDatabase();

	if (!isset($crsId) || !isset($date) || !isset($experiencedOnly)) {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else if (!userIsCommittee($crsId)) {
		$results = array("error" => "Insufficient rights to perform this operation");
	
	} else {
	
		if (strtotime(date("Y-m-d")) - strtotime($date) > 0) {
			// Day before today.
			
			$results = array("error" => "Cannot create a shift in past.");
			
		} else if (strtotime($date) - strtotime(date("Y-m-d"))  > 13 * 24 * 60 * 60) {
			// If more than two weeks in advance (including today)
			
			$results = array("error" => "Cannot create a shift more than two weeks in advance.");
			
		} else if (($worker = addShift($date, $experiencedOnly)) !== false) {
			// Add a shift to the date.
			
			$results = array("date" => $date, "worker" => $worker);
			
		} else {
			// Error adding a shift to the date.
			
			$results = array("error" => "Failed to update database.");
			
		}
	}
	
	closeDatabase($con);
	
	// Return JSON message with result of POST.
	echo json_encode($results);
?>
