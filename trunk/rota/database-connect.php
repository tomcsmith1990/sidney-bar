<?php
	// CHMOD 660 on this file!
	function connectToDatabase() {
		return mysql_connect("localhost", "<username>", "<password>");
	}
	
	function openDatabase() {
		// Open database connection, return that connection.
		
		$con = connectToDatabase();
		
		if (!$con) {
			die('Could not connect: ' . mysql_error());
		}

		mysql_select_db("<db>", $con);
		
		return $con;
	}
	
	function closeDatabase($con) {
		// Close the connection.
		
		mysql_close($con);	
	}
	
?>
