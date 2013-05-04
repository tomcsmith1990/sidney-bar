<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];

	// What hour shifts become available on the rota - N.B. 0 = midnight, 23 = 11pm.
	$hourToRelease = 0;

	if ($hourToRelease > 23 || $hourToRelease < 0) return;
	
	$currentYear = date('Y');
	$currentMonth = date('m');
	$currentDay = date('d');
	$currentHour = date('H');

	$con = openDatabase();

	$events = array();
	
	// Number of days in advance we want shifts for.
	if (userIsCommittee($crsId)) {
		$daysInAdvance = 14;
	} else {
		$daysInAdvance = 7;
	}
	
	// Get the default number of workers for each day.
	$defaultNumberOfWorkers = getDefaultNumberOfWorkers();
	
	// Get bar operating dates.
	$operatingDates = getBarOperatingDates();
	$open = strtotime($operatingDates['open']);
	$close = strtotime($operatingDates['close']);

	// Calculate the hour for the date we make.
	// If the current hour is greater than $hourToRelease, we release the next day.
	$hour = $currentHour - $hourToRelease;

	// Only show shifts in the present or future.
	$today = mktime(0, 0, 0, $currentMonth, $currentDay, $currentYear);
	
	for ($daysAhead = 0; $daysAhead < $daysInAdvance; $daysAhead++) {
	
		// Day in question.
		$day = mktime($hour, 0, 0, $currentMonth, $currentDay + $daysAhead, $currentYear);
				
		$date = date('Y-m-d', $day);
			
		if (strtotime($date) >= $open && strtotime($date) <= $close && strtotime($date) >= $today) {
			// Provided we are within the bounds of the bar operating dates and the shift is not in the past.
			
			// Get the shift id for the day.
			$shiftId = getShiftId($date);
		
			if (!$shiftId) {
				// Create the day in the database, if not already.
		
				$shiftId = createDay($date);
		
				// Create the shifts for this day.
				$weekday = date('w', $day);

				createWorkers($shiftId, $defaultNumberOfWorkers[$weekday]['workers'], $defaultNumberOfWorkers[$weekday]['steward'], $defaultNumberofWorkers[$weekday]['oncall']);
			}

			// Get array of workers for the day, key: WorkerNumber, value: (CrsId, Full Name, Available, Experienced).
			$workers = getWorkersForDay($shiftId);
		
			// Create the fullcalendar event for this day.
			foreach ($workers as $workerNumber => $shift) {
		
				// add WorkerNumber to day, just so they are in correct order (on call worker last).
				array_push($events, prepareShift($shiftId, $shift, $day + ($workerNumber == 0 ? 60 : $workerNumber), $workerNumber));
			
			}
		}
	}
	
	closeDatabase($con);
	
	// Return JSON events.
	echo json_encode($events);
	
	
	
	function prepareShift($shiftId, $shift, $start, $workerNumber) {

		$result = array(	'id' => $shiftId . "worker" . $workerNumber, 
							'day' => $shiftId,
							'start' => $start, 
							'workerNumber' => $workerNumber,
							'experiencedOnly' => $shift['experienced']);
							
		if ($shift['crsId']) {
			
			$result['crsId'] = $shift['crsId'];
			$result['shiftAvailable'] = $shift['available'];
			
			if ($result['shiftAvailable']) {
				$result['title'] = "[" . $shift['crsId'] . " - Available]";
				$result['color'] = "#36C";
			} else {
				$result['title'] = $shift['fullname'];
				$result['color'] = "green";
			}
			
		} else {
			// Decide name of worker.
			if ($workerNumber === 0) {
				$result['title'] = "[On-Call]";
				
			} else {
				$result['title'] = "[" . ($shift['experienced'] ? "Experienced" : "Worker") . " $workerNumber]";
			}
			
			$result['shiftAvailable'] = true;
			$result['color'] = "gray";
		}
		
		return $result;
	}
	
?>
