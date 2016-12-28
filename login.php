<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	$uname = $_POST["username"];
	$pwd = $_POST["password"];
	
	$conn = OpenConnection();
	$qry = "SELECT id, first_name from users where email='".$uname."' and password='". base64_encode($pwd)."' and enabled=1";
	//echo $qry;
	
	$result = $conn->query($qry);
	
	/* Select queries return a resultset */
	if ($result->num_rows) {
		$row = $result->fetch_row();
		$result->close();
		CloseConnection($conn);
		
		// set userId in session
		session_start();
		$_SESSION["userId"]=$row[0];
		$_SESSION["firstName"]=$row[1];
		
		// set in session userid
		header("Location: dataupload.php");
		exit();		
	} else {
		CloseConnection($conn);
		header("Location: index.php?err=1");
		exit();
	}
}

?>