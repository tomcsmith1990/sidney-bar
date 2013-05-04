<?php

	// List of CrsIDs to give committee status to, whether or not they are in database.
	$committeeCrsIdOverride = array("tcs40");
	
	require("database-connect.php");
	
	/* info.php */
	
	function getUser($crsId) {
		// This is for setting a user as committee in code.
		global $committeeCrsIdOverride;
		
		$crsId = mysql_escape_string($crsId);
		
		$result = mysql_query("SELECT * FROM Staff WHERE CrsID = '$crsId';");

		if ($row = mysql_fetch_array($result)) {
			return array('forename' => $row['Forename'], 'surname' => $row['Surname'], 'experienced' => $row['Experienced'] == 1, 'committee' => ($row['Committee'] == 1) || in_array($crsId, $committeeCrsIdOverride, true));
		} else {
			return false;
		}
	}
	
	function userIsCommittee($crsId) {
		// This is for setting a user as committee in code.
		global $committeeCrsIdOverride;
		
		$crsId = mysql_escape_string($crsId);
		
		if (in_array($crsId, $committeeCrsIdOverride, true))
			return true;
		
		$result = mysql_query("SELECT Committee FROM Staff WHERE CrsID = '$crsId';");

		if ($row = mysql_fetch_array($result))
			return $row['Committee'] == 1;
		else
			return false;
	}
	
	function userIsExperienced($crsId) {
		$crsId = mysql_escape_string($crsId);
		
		$result = mysql_query("SELECT Experienced FROM Staff WHERE CrsID = '$crsId';");

		if ($row = mysql_fetch_array($result))
			return $row['Experienced'] == 1;
		else
			return false;
	}
	
	/* shifts.php */
	
	function createDay($date) {
		// Create an entry for the date in the Shifts table.
		$date = mysql_escape_string($date);
		
		mysql_query("INSERT INTO Shifts(Date) VALUES('$date');");
		$result = mysql_query("SELECT ShiftId FROM Shifts WHERE Date='$date';");

		if ($row = mysql_fetch_array($result)) {
			return $row['ShiftId'];
		}
		
		return false;
	}
	
	function getShiftId($date) {
		// Get the ShiftId from date.
		$date = mysql_escape_string($date);
		
		$result = mysql_query("SELECT ShiftId FROM Shifts WHERE Date = '$date';");

		if ($row = mysql_fetch_array($result))
			return $row['ShiftId'];
		else return false;	  
	}
	
	function createWorkers($shiftId, $numberOfWorkers, $steward, $oncall) {
		// Create Shifts for the the ShiftId for the NumberOfWorkers.
		$shiftId = mysql_escape_string($shiftId);
		
		// On call worker;
		if ($oncall == 1) {
			$result = mysql_query("INSERT INTO Workers(ShiftId, WorkerNumber, CrsId, Available, Experienced) VALUES ($shiftId, 0, NULL, '1', '0');");
		}
		
		$worker = 'NULL';
		$available = '1';
		$experienced = '1';
		
		if ($steward == 1) {
			$worker = "'steward'";
			$available = '0';
		}

		$i = 1;
		while ($i <= $numberOfWorkers) {
			$result = 
				mysql_query("INSERT INTO Workers(ShiftId, WorkerNumber, CrsId, Available, Experienced) 
								VALUES($shiftId, $i, $worker, '$available', '$experienced');") && $result;
			$worker = 'NULL';
			$available = '1';
			$experienced = '0';
			$i++;
		}
		
		if ($result)
			return true;
		else
			return false;	
	}
	
	function getWorkersForDay($shiftId) {
		// Get the workers for the ShiftId. Return array of key: WorkerNumber, value: (CrsId, Available).
		$shiftId = mysql_escape_string($shiftId);
		
		$result = mysql_query("SELECT Workers.WorkerNumber, Workers.CrsId, Workers.Available, Workers.Experienced, Staff.Forename, Staff.Surname 
								FROM Workers, Staff 
								WHERE ShiftId = $shiftId 
								AND (Workers.CrsId = Staff.CrsId OR Workers.CrsId IS NULL)
								ORDER BY WorkerNumber;");

		$workers = array();
		
		while ($row = mysql_fetch_array($result)) {
			$workers[$row['WorkerNumber']] = 
				array('crsId' => $row['CrsId'], 
						'fullname' => $row['Forename'] . " " . $row['Surname'], 
						'available' => $row['Available'] == 1,
						'experienced' => $row['Experienced'] == 1);			  
		}
		
		return $workers;
	}
	
	function getDefaultNumberOfWorkers() {
		// Get default number of workers for each day.
		
		$result = mysql_query("SELECT * FROM DefaultShifts ORDER BY Day");
		
		$defaults = array();
		
		while ($row = mysql_fetch_array($result)) {
			$defaults[$row['Day']] = array('day' => $row['Weekday'], "workers" => $row['Amount'], 'steward' => $row['Steward'] == 1, 'oncall' => $row['OnCall'] == 1); 
		}
		
		return $defaults;
	}

	
	/* assign-shift.php */
	
	function getShift($shiftId, $workerNumber) {
	
		// Check if the shift is available for the ShiftId and WorkerNumber.
		$shiftId = mysql_escape_string($shiftId);
		$workerNumber = mysql_escape_string($workerNumber);
		
		$result = mysql_query("SELECT * FROM Workers 
								WHERE ShiftId = $shiftId 
								AND WorkerNumber = $workerNumber;");
		
		if ($row = mysql_fetch_array($result)) {
			return array('crsId' => $row['CrsId'],
							'available' => $row['Available'] == 1,
							'experienced' => $row['Experienced'] == 1);		  
		}		
		return false;
	}
	
	function workerHasAnotherShiftThisNight($shiftId, $workerNumber, $crsId) {
		// Check if the CrsId already has a Shift with the ShiftId.
		$shiftId = mysql_escape_string($shiftId);
		$workerNumber = mysql_escape_string($workerNumber);
		$crsId = mysql_escape_string($crsId);
		
		$result = mysql_query("SELECT * FROM Workers 
								WHERE ShiftId = $shiftId 
								AND WorkerNumber <> $workerNumber 
								AND CrsId = '$crsId';");
		
		if (mysql_num_rows($result) > 0) {
			return true;	  
		}
		
		return false;
	}
	
	function assignShiftToWorker($shiftId, $workerNumber, $crsId) {
		// Assign ShiftId, WorkerNumber to CrsId.
		$shiftId = mysql_escape_string($shiftId);
		$workerNumber = mysql_escape_string($workerNumber);
		$crsId = mysql_escape_string($crsId);
		$experiencedOnly = mysql_escape_string($experiencedOnly);
		
		$result = mysql_query("UPDATE Workers 
								SET CrsId = '$crsId', Available = false  
								WHERE ShiftId = $shiftId AND WorkerNumber = $workerNumber;");
		
		if ($result)
			return true;
		else
			return false;	  
	}
	
	function makeShiftAvailable($shiftId, $workerNumber, $crsId) {
		// Make ShiftId, WorkerNumber available for signup, if CrsId has it.
		$shiftId = mysql_escape_string($shiftId);
		$workerNumber = mysql_escape_string($workerNumber);
		$crsId = mysql_escape_string($crsId);
		
		$result = mysql_query("UPDATE Workers 
								SET Available = true 
								WHERE ShiftId = $shiftId 
									AND WorkerNumber = $workerNumber
									AND CrsId = '$crsId';");

		if ($result)
			return true;
		else
			return false;	
	}
	
	
	/* edit-shift.php */
	
	function setExperiencedShift($shiftId, $workerNumber, $experiencedOnly) {
		// Update whether a shift must be an experienced worker.
		$shiftId = mysql_escape_string($shiftId);
		$workerNumber = mysql_escape_string($workerNumber);
		$experiencedOnly = mysql_escape_string($experiencedOnly);
		
		$result = mysql_query("UPDATE Workers 
								SET Experienced = $experiencedOnly 
								WHERE ShiftId = $shiftId 
									AND WorkerNumber = $workerNumber;");

		if ($result)
			return true;
		else
			return false;	
	}
	
	function unassignShift($shiftId, $workerNumber, $experiencedOnly) {
		$shiftId = mysql_escape_string($shiftId);
		$workerNumber = mysql_escape_string($workerNumber);
		$experiencedOnly = mysql_escape_string($experiencedOnly);
	
		$result = mysql_query("UPDATE Workers 
								SET Available = true, CrsId = NULL, Experienced = $experiencedOnly 
								WHERE ShiftId = $shiftId 
									AND WorkerNumber = $workerNumber;");
									
		if ($result)
			return true;
		else
			return false;	
	}
	
	
	/* create-shift.php */
	
	function addShift($date, $experiencedOnly) {
		// Add a new shift on the date.
		$date = mysql_escape_string($date);
		$experiencedOnly = mysql_escape_string($experiencedOnly);
		
		$shiftId = getShiftId($date);

		if ($shiftId === false) return false;
		
		$workers = getWorkersForDay($shiftId);
		
		$nextWorker = 0;
		
		// Get the next worker number for tonight.
		foreach ($workers as $workerNumber => $value) {
			if ($workerNumber > $nextWorker) break;
			else $nextWorker++;
		}
		
		$result = mysql_query("INSERT INTO Workers(ShiftId, WorkerNumber, Experienced) VALUES ($shiftId, $nextWorker, $experiencedOnly);");

		if ($result)
			return $nextWorker;
		else
			return false;	
	}
	
	/* delete-shift.php */
	
	function deleteShift($shiftId, $workerNumber) {
		// Delete the shift with ShiftId, WorkerNumber.
		$shiftId = mysql_escape_string($shiftId);
		$workerNumber = mysql_escape_string($workerNumber);
		
		$result = mysql_query("DELETE FROM Workers WHERE ShiftId = $shiftId AND WorkerNumber = $workerNumber;");

		if ($result)
			return true;
		else
			return false;	
	}
	
	/* edit-default-shifts.php */
	
	function setDefaultNumberOfWorkers($defaults) {
		// Set the default number of workers.

		$result = true;
		
		foreach ($defaults as $day) {
				$result = $result && 
					mysql_query("UPDATE DefaultShifts " .
								"SET Amount=" . mysql_escape_string($day->workers) . ", Steward='" . mysql_escape_string($day->steward) . "', OnCall ='" . mysql_escape_string($day->oncall) . 
								"' WHERE Weekday LIKE '" . mysql_escape_string($day->day) . "';");
		}

		if ($result)
			return $defaults;
		else
			return false;	
	}
	
	/* add-staff.php */
	
	function addStaffMember($crsId, $forename, $surname, $phone, $experienced, $committee) {
		// Add a new staff member.
		$crsId = mysql_escape_string($crsId);
		$forename = mysql_escape_string($forename);
		$surname = mysql_escape_string($surname);
		$phone = mysql_escape_string($phone);
		$experienced = mysql_escape_string($experienced);
		$committee = mysql_escape_string($committee);

		$result = mysql_query("INSERT INTO Staff(CrsId, Forename, Surname, PhoneNumber, Experienced, Committee) " .
								"VALUES ('$crsId', '$forename', '$surname', '$phone', $experienced, $committee);");

		if ($result)
			return true;
		else
			return false;	
	}
	
	/* staff.php */
	
	function getStaff() {
	
		$result = mysql_query("SELECT * FROM Staff ORDER BY CrsId;");
		
		$staff = array();
		
		while ($row = mysql_fetch_array($result)) {
			array_push($staff, array('crsId' => $row['CrsId'], 
										'forename' => $row['Forename'], 
										'surname' => $row['Surname'],
										'fullname' => $row['Forename'] . " " . $row['Surname'],
										'phone' => $row['PhoneNumber'],
										'experienced' => $row['Experienced'] == 1,
										'committee' => $row['Committee'] == 1));			  
		}
		
		return $staff;
	}
	
	/* email-staff.php */
	
	function getStaffEmail($experienced, $committee) {
	
		$query = "SELECT CrsId, Forename, Surname FROM Staff WHERE CrsId <> 'steward'";
		if ($experienced === true)
			$where['Experienced'] = 1;
		if ($committee === true)
			$where['Committee'] = 1;
			
		if ($where) {
			foreach ($where as $key => $value) {
				$query .= " AND " . $key . " = " . $value;
			}
		}
		$query .= " ORDER BY CrsId;";

		$result = mysql_query($query);
		
		$staff = "";
		
		while ($row = mysql_fetch_array($result)) {
			$staff .= $row['Forename'] . " " . $row['Surname'] . " <" . $row['CrsId'] . "@cam.ac.uk>, ";			  
		}
		
		return $staff;
	}
	
	/* delete-staff.php */
	
	function deleteStaffMember($crsId) {
		$crsId = mysql_escape_string($crsId);
		
		$result = mysql_query("DELETE FROM Staff WHERE CrsId = '$crsId';");

		if ($result)
			return true;
		else
			return false;	
	}
	
	/* edit-staff.php */
	
	function editStaffMember($crsId, $forename, $surname, $phone, $experienced, $committee) {
		// Edit a staff member.
		$crsId = mysql_escape_string($crsId);
		$forename = mysql_escape_string($forename);
		$surname = mysql_escape_string($surname);
		$phone = mysql_escape_string($phone);
		$experienced = mysql_escape_string($experienced);
		$committee = mysql_escape_string($committee);

		$result = mysql_query("UPDATE Staff SET Forename='$forename', Surname='$surname', PhoneNumber='$phone', Experienced=$experienced, Committee=$committee WHERE CrsId='$crsId';");

		if ($result)
			return true;
		else
			return false;	
	}
	
	/* open-dates.php */
	
	function getBarOperatingDates() {
	
		$result = mysql_query("SELECT * FROM BarOpening;");
		
		$dates = array();
		
		while ($row = mysql_fetch_array($result)) {
			$dates[$row['Type']] = $row['Date'];
		}
		
		return $dates;
	}
	
	/* edit-open-dates.php */
	
	function setBarOperatingDates($open, $close) {
		$open = mysql_escape_string($open);
		$close = mysql_escape_string($close);
		
		$result = mysql_query("UPDATE BarOpening SET Date = CASE Type WHEN 'open' THEN '$open' WHEN 'close' THEN '$close' END WHERE Type IN ('open', 'close');") or die (mysql_error());

		if ($result)
			return true;
		else
			return false;	
	}
	
	/* staff-info.php */

	function getLastShift($crsId) {
		$crsId = mysql_escape_string($crsId);

		$result = mysql_query("SELECT Shifts.Date FROM Workers
						INNER JOIN Shifts ON
						Shifts.ShiftId = Workers.ShiftId 
						WHERE Workers.CrsId = '$crsId' AND
						Shifts.Date <= CURDATE() 
						ORDER BY Shifts.Date DESC
						LIMIT 0, 1;");

		if ($result) {
		
			if ($row = mysql_fetch_array($result)) {
				return $row['Date'];
			} else {
				return NULL;
			}
		}
		
		return false;
	}
	
	function getNumberOfShifts($crsId, $interval, $amount) {
		$crsId = mysql_escape_string($crsId);
		$days = mysql_escape_string($days);
		
		$result = mysql_query("SELECT COUNT(DISTINCT Shifts.Date) AS NumberOfShifts FROM Workers
						INNER JOIN Shifts ON
						Shifts.ShiftId = Workers.ShiftId 
						WHERE Workers.CrsId = '$crsId' AND
						Shifts.Date <= CURDATE() AND
						Shifts.Date > DATE_SUB(CURDATE(), INTERVAL '$amount' $interval);");
						
		if ($result) {
		
			if ($row = mysql_fetch_array($result)) {
				return $row['NumberOfShifts'];
			}

			return 0;
		}
		
		return false;

	}
	
	/* lookup-workers.php */
	
	function lookupWorkers($date) {
		$date = mysql_escape_string($date);
		
		$result = mysql_query("SELECT Staff.CrsId, Staff.Forename, Staff.Surname, Workers.WorkerNumber 
								FROM Staff 
								INNER JOIN Workers ON Workers.CrsId = Staff.CrsId 
								INNER JOIN Shifts ON Workers.ShiftID = Shifts.ShiftID 
								WHERE Shifts.Date = '$date';");
								
		if ($result) {
		
			$workers = array();

			while ($row = mysql_fetch_array($result))
				array_push($workers, array('crsId' => $row['CrsId'], 'forename' => $row['Forename'], 'surname' => $row['Surname'], 'worker' => $row['WorkerNumber']));		
				
			return $workers;	  
		}
		
		return false;
	}

	/* default-times.php */

	function getDefaultTimes() {
		// Get default number of workers for each day.
		
		$result = mysql_query("SELECT * FROM DefaultTimes ORDER BY Day, WorkerNumber");
		
		$defaults = array();
			
		// Pack times in a nice format.
		while ($row = mysql_fetch_array($result)) {
			$defaults[$row['Day']][$row['WorkerNumber']] = array('start' => date("H:i", strtotime($row['Start'])), 'end' => date("H:i", strtotime($row['End']))); 
		}
		
		return $defaults;

	}
	
	/* edit-default-times.php */
	function setDefaultTimes($defaults) {
		// Set the default number of workers.

		$result = true;
		
		foreach ($defaults as $time) {
		
				$result = $result && 
					mysql_query("UPDATE DefaultTimes 
									SET Start='" . mysql_escape_string($time->start) . "', End='" . mysql_escape_string($time->end) . "' 
									WHERE Day = '" . mysql_escape_string($time->day) . "' && WorkerNumber = '" . mysql_escape_string($time->worker) . "';");
		}

		if ($result)
			return $defaults;
		else
			return false;
	}
?>
