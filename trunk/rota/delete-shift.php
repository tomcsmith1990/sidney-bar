<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	$shiftId = $_POST['shiftId'];
	$workerNumber = $_POST['workerNumber'];
	$date = $_POST['date'];
	
	$con = openDatabase();

	if (!isset($crsId) || !isset($shiftId) || !isset($workerNumber)) {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else if (!userIsCommittee($crsId)) {
		$results = array("error" => "Insufficient rights to perform this operation");
	
	} else {

		if (deleteShift($shiftId, $workerNumber)) {
			// Delete the shift.
			
			$results = array("date" => $date, "worker" => $workerNumber);
		
		} else {
			// Error deleteing the shift.
			
			$results = array("error" => "Failed to update database.");
		}
	}
	
	closeDatabase($con);
	
	// Return JSON message with result of POST.
	echo json_encode($results);
?>
