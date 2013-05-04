<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	
	$workerCrsId = $_POST['crsId'];
	$workerForename = $_POST['forename'];
	$workerSurname = $_POST['surname'];
	$workerPhone = $_POST['phone'];
	$workerExperienced = $_POST['experienced'];
	$workerCommittee = $_POST['committee'];
	
	$con = openDatabase();

	if (!isset($crsId) || !validWorker($workerCrsId, $workerForename, $workerSurname, $workerPhone, $workerExperienced, $workerCommittee)) {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else if (!userIsCommittee($crsId)) {
		$results = array("error" => "Insufficient rights to perform this operation");
	
	} else {

		if (editStaffMember($workerCrsId, $workerForename, $workerSurname, $workerPhone, $workerExperienced, $workerCommittee)) {
			// Edit worker to the database.
			
			$results = array("forename" => $workerForename, "surname" => $workerSurname);
			
		} else {
			// Error editing a staff member.
			
			$results = array("error" => "Failed to update database.");
			
		}
	}
	
	closeDatabase($con);
	
	// Return JSON message with result of POST.
	echo json_encode($results);
	
	
	function validWorker($crsId, $forename, $surname, $phone, $experienced, $committee) {
		// Check that fields are all valid.
		
		if (!isset($crsId) || $crsId == '') return false;		
		if (!isset($forename) || $forename == '') return false;
		if (!isset($surname) || $surname == '') return false;
		
		return true;
	}
?>
