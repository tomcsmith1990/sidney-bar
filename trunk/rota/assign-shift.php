<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	$shiftId = $_POST['shiftId'];
	$workerNumber = $_POST['workerNumber'];

	if (!isset($crsId) || !isset($shiftId) || !isset($workerNumber)) {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else {
	
		$con = openDatabase();
		
		$shift = getShift($shiftId, $workerNumber);
		
		if ($shift['available'] === false) {
			// Shift is already taken.
			
			if ($shift['crsId'] !== $crsId) {
				// Shift is taken by another worker.
				
				$results = array("error" => "The shift has been taken.");
				
			} else if (makeShiftAvailable($shiftId, $workerNumber, $crsId)) {
				
				// Shift is taken by user, make the shift available.
				$results = array("crsId" => $crsId, "shiftAvailable" => true);
					
			} else {
				// Error making the shift available.
				$results = array("error" => "Failed to update database.");
			}

		} else if (workerHasAnotherShiftThisNight($shiftId, $workerNumber, $crsId)) {
			// User is already working on this night.
			
			$results = array("error" => "You are already working on that night.");
			
		} else if ($shift['experienced'] && userIsExperienced($crsId) === false) {
			// Experienced shift and user is not experienced.
			
			$results = array("error" => "You must be an experienced worker to work this shift.");
		
		} else if (assignShiftToWorker($shiftId, $workerNumber, $crsId)) {
			// Assign the shift to the user.
			
			$results = array("crsId" => $crsId, "shiftAvailable" => false);
				
		} else {
			// Error assigning the shift to the user.
			
			$results = array("error" => "Failed to update database.");
		}
		
		closeDatabase($con);
	}
	
	// Return JSON message with result of POST.
	echo json_encode($results);
?>
