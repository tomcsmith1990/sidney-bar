<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	
	$defaults = json_decode($_POST['defaults']);
	
	$con = openDatabase();

	if (!isset($crsId) || !isset($defaults) || count($defaults) == 0) {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else if (!userIsCommittee($crsId)) {
		$results = array("error" => "Insufficient rights to perform this operation");
	
	} else {

		if ($numberOfWorkers = setDefaultTimes($defaults)) {
			// Set the default number of workers.
			
			$results = array("success" => "Updated default times.");
			
		} else {
			// Error setting the default number of workers.
			
			$results = array("error" => "Failed to update database.");		
		}
	}
	
	closeDatabase($con);
	
	// Return JSON message with result of POST.
	echo json_encode($results);
?>
