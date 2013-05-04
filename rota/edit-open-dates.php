<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	
	$open = $_POST['open'];
	$close = $_POST['close'];
	
	$con = openDatabase();

	if (!isset($crsId) || !isset($open) || !isset($close)) {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else if (strtotime($close) < strtotime($open)) {
		// Close is before open.
		$results = array("error" => "Cannot close before being open.");
	
	} else if (!userIsCommittee($crsId)) {
		$results = array("error" => "Insufficient rights to perform this operation");
	
	} else {

		if (setBarOperatingDates($open, $close)) {
			// Set the bar operating dates.
			
			$results = array("success" => "Updated bar operating dates.");
			
		} else {
			// Error setting the operating dates.
			
			$results = array("error" => "Failed to update database.");		
		}
	}
	
	closeDatabase($con);
	
	// Return JSON message with result of POST.
	echo json_encode($results);
?>
