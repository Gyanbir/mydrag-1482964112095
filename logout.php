<?php
session_start();
		$_SESSION["userId"]=null;
		$_SESSION["firstName"]=null;
		
		// set in session userid
		header("Location: index.php");
		exit();		

?>