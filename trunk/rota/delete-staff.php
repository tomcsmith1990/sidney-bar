<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];

	$workerCrsId = $_POST['crsId'];
	$workerForename = $_POST['forename'];
	$workerSurname = $_POST['surname'];
	
	$con = openDatabase();

	if (!isset($crsId) || !isset($workerCrsId)) {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else if (!userIsCommittee($crsId)) {
		$results = array("error" => "Insufficient rights to perform this operation");
	
	} else {

		if (deleteStaffMember($workerCrsId)) {
			// Delete the staff member.
			
			$results = array("forename" => $workerForename, "surname" => $workerSurname);
		
		} else {
			// Error deleting the shift.
			
			$results = array("error" => "Failed to update database.");
		}
	
	}
	
	closeDatabase($con);
	
	// Return JSON message with result of POST.
	echo json_encode($results);
?>
