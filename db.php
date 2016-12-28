<?php
 
function OpenConnection() {
	$dbhost = "localhost";
	$dbuser = "root";
	$dbpass = "root";
	$db = "prediction_engine";
 
	$conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
	if (mysqli_connect_errno()) {
		echo("Can't connect to MySQL Server. Error code: " . mysqli_connect_error());
		return null;
	}
	
	return $conn;
}
 
function CloseConnection($conn) {
	$conn->close();
}
   
?>