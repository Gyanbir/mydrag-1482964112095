<?php

error_reporting(E_ALL);
set_time_limit(0);

//date_default_timezone_set('Europe/London');
date_default_timezone_set('America/Los_Angeles');
?>
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<link rel="stylesheet" href="css/page.css" />
<link rel="stylesheet" href="css/runnable.css" />
<link rel="stylesheet" href="css/dropzone.css" />

<script src="js/dropzone.js"></script>
<title>Prediction Engine - Upload Data</title>

</head>
<body>

<?php

echo "test mysql methods<BR/>";

include 'db.php';
$conn = OpenConnection();

	$query = 'select id,  Work_Ready_Estimated_Date, First_Contact_with_Applicant_Date, All_Documents_Received_Date, Application_sent_to_GE_Signature_Date, All_Documentation_Received_for_Filing_Date, Application_Filed_Date, Application_Finalised_Date, Last_Action_Code, Last_Action_Comment, Last_Action_Date from uploaded_data where user_uploaded_file_id=29';
	echo "$query<BR/>";
	$result = $conn->query($query);
	$rows = resultToArray($result);
	
	
	//echo "<BR>dumping array of rows:<BR>";
	//var_dump($rows); // Array of rows
	
	for ($i = 0; $i < count($rows); $i++) {
		
		$Work_Ready_Estimated_Date = (!empty($rows[$i]['Work_Ready_Estimated_Date']) ? (new DateTime($rows[$i]['Work_Ready_Estimated_Date']))->format('d-F-Y H:i:s') : "");
		echo "<BR/>Work_Ready_Estimated_Date:".$Work_Ready_Estimated_Date."--";
	}
	
	$result->free();
	
	CloseConnection($conn);

?>
</body>
</html>

<?php
function resultToArray($result) {
    $rows = array();
    while($row = $result->fetch_assoc()) {
		//var_dump($row)."<BR>";
        $rows[] = $row;
    }
    return $rows;
}
?>