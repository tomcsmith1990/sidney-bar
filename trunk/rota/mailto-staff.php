<?php
	include("database.php");
	
	$crsId = $_ENV['REMOTE_USER'];
	
	$experienced = $_GET['experienced'];
	$committee = $_GET['committee'];
	
	$con = openDatabase();

	if (!isset($crsId)) {
		// Parameters are incorrect.
		$results = array("error" => "Incorrect Parameters.");
		
	} else {
	
		$headers = "From: $from" . "\r\n" .
					"Reply-To: $from" . "\r\n" .
					"X-Mailer: PHP/" . phpversion();
					
		if ($to = getStaffEmail($experienced === "true", $committee === "true")) {
			$location = "mailto: $to";
		}
	}
	
	closeDatabase($con);
	
	if ($location)
		header("Location: $location");
	else
		echo '<html><body><script>window.close();</script></body></html>';
?>
