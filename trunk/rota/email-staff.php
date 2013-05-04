<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	
	$subject = $_POST['subject'];
	$body = $_POST['body'];
	$fromName = $_POST['fromName'];
	$from = $fromName . " <" . $crsId . "@cam.ac.uk>";
	$experienced = $_POST['experienced'];
	$committee = $_POST['committee'];
	
	$con = openDatabase();

	if (!isset($crsId) || !isset($body) || !isset($subject) || !isset($from) || $body === "") {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else {
	
		$headers = "From: $from" . "\r\n" .
					"Reply-To: $from" . "\r\n" .
					"X-Mailer: PHP/" . phpversion();
					
		if ($to = getStaffEmail($experienced === 'true', $committee === 'true')) {

			if (mail($to, $subject, $body, $headers)) {
				// Send email.
			
				$results = array("subject" => $subject);
			
			} else {
						
				$results = array("error" => "Email was not sent.");
			
			}
		} else {
			$results = array("error" => "Error getting staff from database.");
		}
	}
	
	closeDatabase($con);
	
	// Return JSON message with result of POST.
	echo json_encode($results);
?>
