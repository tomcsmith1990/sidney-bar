<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	
	$workerCrsId = $_POST['crsId'];
	
	$con = openDatabase();

	if (!isset($crsId) || !isset($workerCrsId)) {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else {

		// Get last shift.
		$lastShift = getLastShift($workerCrsId);
		
		$twoWeeks = getNumberOfShifts($workerCrsId, 'WEEK', 2);
		$oneMonth = getNumberOfShifts($workerCrsId, 'MONTH', 1);
		$twoMonths = getNumberOfShifts($workerCrsId, 'MONTH', 2);
			
		if ($lastShift === false && $twoWeeks === false && $oneMonth === false && $twoMonths === false) {
			// Error getting shifts from database.
			
			$results = array("error" => "Failed to get shift data from database.");
			
		} else {
			$results = array("lastShift" => $lastShift, 
								"shiftsInTwoWeeks" => $twoWeeks, 
								"shiftsInOneMonth" => $oneMonth,
								"shiftsInTwoMonths" => $twoMonths);
			
		}
	}
	
	closeDatabase($con);
	
	// Return JSON message with result of POST.
	echo json_encode($results);
	
?>
