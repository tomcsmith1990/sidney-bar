<?php	
	$crsId = $_ENV['REMOTE_USER'];

	include("database.php");
	
	$results = array();

	$con = openDatabase();
	
	if (!userIsCommittee($crsId)) {
		$results = array("error" => "Insufficient rights to perform this operation");
	
	} else {

		$results = array("defaultWorkers" => getDefaultNumberOfWorkers());
	}

	closeDatabase($con);

	echo json_encode($results);
?>
