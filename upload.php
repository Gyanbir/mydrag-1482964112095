<?php

include 'db.php';

session_start();
$userId = $_SESSION["userId"];

$fileName = basename($_FILES['file']['name']);
$uploaddir = './uploads/'.$userId.'_';
$uploadfile = $uploaddir . date("Y-m-d H:i:s") . '-'. $fileName;

echo '<pre>';
if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
    //echo "File is valid, and was successfully uploaded.\n";
	
	$conn = OpenConnection();
	$qry = "insert into user_uploaded_files (`user_id`,`filename`,`filepath`,`uploaded`) values ('".$userId."','".$fileName."','".$uploadfile."',now()) ";
	mysqli_query($conn, $qry);
	CloseConnection($conn);
} else {
    echo "Possible file upload attack!\n";
}

/*echo 'Here is some more debugging info:';
print_r($_FILES);
print_r(error_get_last());

print "</pre>";*/

?>