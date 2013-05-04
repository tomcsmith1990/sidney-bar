<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	
	$shiftId = $_POST['shiftId'];
	$workerNumber = $_POST['workerNumber'];
	$newWorker = $_POST['worker'];
	$date = $_POST['date'];
	$experiencedOnly = $_POST['experiencedOnly'];
	
	$con = openDatabase();

	if (!isset($crsId) || !isset($shiftId) || !isset($workerNumber) || !isset($experiencedOnly)) {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else if (!userIsCommittee($crsId)) {
		$results = array("error" => "Insufficient rights to perform this operation");
	
	} else if (!isset($newWorker) || $newWorker === "") {
		// There is no new worker, unassign the shift.
		
		
		if (unassignShift($shiftId, $workerNumber, $experiencedOnly)) {
			// Shift has been unassigned.
			
			$results = array("workerNumber" => $workerNumber, "shiftAvailable" => true, "date" => $date);
			
		} else {
			// Error unassigning the shift.
			
			$results = array("error" => "Failed to update database.");
		}
		
	} else {
	
		$con = openDatabase();
		
		if (workerHasAnotherShiftThisNight($shiftId, $workerNumber, $newWorker)) {
			// User is already working on this night.
			
			$results = array("error" => "$newWorker is already working on that night.");
			
		} else if (assignShiftToWorker($shiftId, $workerNumber, $newWorker) && setExperiencedShift($shiftId, $workerNumber, $experiencedOnly)) {
			// Assign the shift to the user.
			
			$results = array("crsId" => $newWorker, "workerNumber" => $workerNumber, "shiftAvailable" => false, "date" => $date, "experienced" => $experiencedOnly);
				
		} else {
			// Error assigning the shift to the user.
			
			$results = array("error" => "Failed to update database.");
		}	
	}
	
	closeDatabase($con);
	
	// Return JSON message with result of POST.
	echo json_encode($results);
?>
