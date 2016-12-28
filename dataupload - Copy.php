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

<style>

#box-table-a
{
	font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
	font-size: 12px;
	width: 480px;
	text-align: left;
	border-collapse: collapse;
	border: 1px solid;
}
#box-table-a th
{
	font-size: 13px;
	font-weight: normal;
	padding: 8px;
	background: grey;
	border-top: 4px solid #aabcfe;
	border-bottom: 1px solid #fff;
	color: white;
}
#box-table-a td
{
	padding: 8px;
	background: #dddddd; 
	border-bottom: 1px solid #fff;
	color: black;
	border-top: 1px solid transparent;
}
#box-table-a tr:hover td
{
	background: #d0dafd;
	color: #339;
}

section {
    width: 100%;
    height: 200px;    
    margin: auto;
    padding: 130 10 40 10;
}
div#one {
    width: 55%;
    height: 200px;
    background: white;
    float: left;
}
div#two {
    margin-left: 35%;
    height: 200px;
    background: white;
}

div#three {
    padding-top: 190
}


</style>

</head>

<?php

// database connection - move this to some function or package
$conn = new mysqli('localhost', 'root', 'root', 'prediction_engine');
if (mysqli_connect_errno()) {
	echo("Can't connect to MySQL Server. Error code: " . mysqli_connect_error());
	return null;
}

$uploaded_filename="";
$uploaded_date="";
session_start();
$userId = $_SESSION["userId"];
//TODO: redirect to login page if user is not logged-in
if ($userId==null){
	header("Location: index.php");
	exit();
}
$qry = "select id, filename, filepath, created from user_uploaded_files where user_id=".$userId." order by created desc limit 1";
$result = $conn->query($qry);

if ($result->num_rows) {
	$row = $result->fetch_row();
	$inputFileName = $row[2];
	$uploaded_filename=$row[1];
	$uploaded_date=$row[3];
	$result->close();
	//echo "filename from the db:".$inputFileName."<BR/>";
}
?>

<body>

	<header id="header">
		<div class="container">
			<a href="#" class="btn-menu"></a>
			<strong class="logo"></strong>
			<!--<ul class="login-links">
				<li class="login-link">
					Prediction Engine
				</li>
			</ul>-->
			
			<ul class="login-links">
				<li class="login-sub-link"> Prediction Engine </li>
			</ul>
			<ul class="login-top-links">
				<li class="login-top-link"> Welcome Sharad! </li>
			</ul>
			<ul class="login-sub-links">
				<li class="login-sub-link">My File </li>
			</ul>
			<ul class="login-sub-links">
				<li class="login-sub-link">Logout </li>
			</ul>

		</div><!-- container -->
	</header><!-- header -->

<section >
    <div id="one">
		<div >
		<!--<span><B>Upload file using Select Or Drag & Drop</B></span>-->
			<form action="upload.php"
				  class="dropzone"
				  id="my-awesome-dropzone" 
				  style="background:url(images/upload-arrow_318-26670.jpg);background-repeat:no-repeat;background-position:center;border:1px solid">
				  <label for="file"><strong>Choose a file</strong><span > or drag it here</span>.</label>
				  <!--<img src="images/upload_icon.png" height="62" width="62" />-->
			</form>
		</div>
	</div> <!-- div one -->
	
    <div id="two">
		<table cellpadding="2" cellmargin="0" style="border:1px solid;padding:2 2 2 2;" width="350px" height="100px">
		<tr bgcolor="grey" style="color:white;">
			<td><b>FileName</b></td><td><b>Uploaded Date</b></td>
		</tr>
		<tr>
			<td><?=$uploaded_filename?></td><td><?=$uploaded_date?></td>			
		</tr>
		</table>
	</div>
	
	<div id="three">
	


<?php

/** Include path **/
set_include_path(get_include_path() . PATH_SEPARATOR . './Classes/');

/** PHPExcel_IOFactory */
include 'PHPExcel/IOFactory.php';


$inputFileType = 'Excel2007';
//$inputFileName = './IndiaTango-xls schema.xlsx';

$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($inputFileName);

$objWorksheet = $objPHPExcel->getActiveSheet();
$highestRow = $objWorksheet->getHighestRow(); 
//echo "highestRow:".$highestRow.'<BR>';
$highestColumn = $objWorksheet->getHighestColumn(); 
//echo "highestColumn:".$highestColumn.'<BR>';
$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
//echo "highestColumnIndex:".$highestColumnIndex.'<BR>';

$display_table = true;
if ($display_table) {
	
	echo '<table id="box-table-a">' . "\n";
	// render the header row - start
	echo '<thead><tr >' . "\n";
	for ($col = 0; $col <= $highestColumnIndex; ++$col) {
		echo '<th scope="col" >' . $objWorksheet->getCellByColumnAndRow($col, 1)->getValue() . '</th>' . "\n";
	}
	echo '</tr></thead><tbody>' . "\n";
	// render the header row - end

	for ($row = 2; $row <= $highestRow; ++$row) {
		echo '<tr>' . "\n";
		
		$dbArray = array();
		// column 0 to column 9 are all texts
		for ($col = 0; $col <= 9; ++$col) {
			$cell = $objWorksheet->getCellByColumnAndRow($col, $row);
			$cellVal = $cell->getValue();
			echo '<td>' . $cellVal . '</td>' . "\n";
			$dbArray[$col] = $cellVal;
		}
		
		// column 10 to column 18 are all dates
		for ($col = 10; $col <= 18; ++$col) {
			//alternatively this can be used as well for getting the date: $dateObj = date($format, PHPExcel_Shared_Date::ExcelToPHPObject($cellVal));
			$cell = $objWorksheet->getCellByColumnAndRow($col, $row);
			$dt = PHPExcel_Shared_Date::ExcelToPHPObject($cell->getValue());
			$cellVal = $dt->format('d-F-Y');
			echo '<td>' . $cellVal . '</td>' . "\n";
			$dbArray[$col] = $dt->format('Y-m-d H:i:s');
		}
		
		// column 19 and 20 are texts
		for ($col = 19; $col <= 20; ++$col) {
			$cell = $objWorksheet->getCellByColumnAndRow($col, $row);
			$cellVal = $cell->getValue();
			echo '<td>' . $cellVal . '</td>' . "\n";
			//$dbArray[$col] = mysqli_real_escape_string($cellVal);
			$dbArray[$col] = $cellVal;
		}
		
		// column 21 is date
		$cell = $objWorksheet->getCellByColumnAndRow(21, $row);
		$dt = PHPExcel_Shared_Date::ExcelToPHPObject($cell->getValue());
		$cellVal = $dt->format('d-F-Y');
		echo '<td>' . $cellVal . '</td>' . "\n";
		$dbArray[21] = $dt->format('Y-m-d H:i:s');
		
		echo '</tr>' . "\n";
		
		// debugging array values - start
		/*for ($z=0;$z<=21;++$z){
			echo $dbArray[$z].", ";
		}
		echo "<BR/>";*/
		// debugging array values - end
		
		insert_data($conn, $dbArray);
		

		
	} // iterate over the data rows from excel
	echo '</tbody></table>' . "\n";
	
	$conn->close();

	/* This section is for iterating over the xls rows and columns
	echo '<table border="1">' . "\n";
	for ($row = 1; $row <= $highestRow; ++$row) {
	  echo '<tr>' . "\n";
	  for ($col = 0; $col <= $highestColumnIndex; ++$col) {
		echo '<td>' . $objWorksheet->getCellByColumnAndRow($col, $row)->getValue() . '</td>' . "\n";
	  }
	  echo '</tr>' . "\n";
	}
	echo '</table>' . "\n";
	*/

}


function insert_data($conn, $dbArray) {
	$insert_query = "INSERT INTO uploaded_data (`user_uploaded_file_id`, 
		`CaseID`, 
		`Case_Assignee_First_Name`, 
		`Case_Assignee_Surname`, 
		`Region`,
		`SSO`,
		`Receiving_Country`,
		`Case_Type`,
		`GE_CoE_Case_Number`,
		`GE_Business`,
		`Industry_Focus_Group`,
		`Case_Initiation_Date`,
		`Proposed_Assignment_From_Date`,
		`Work_Ready_Estimated_Date`,
		`First_Contact_with_Applicant_Date`, 
		`All_Documents_Received_Date`,
		`Application_sent_to_GE_Signature_Date`,
		`All_Documentation_Received_for_Filing_Date`,
		`Application_Filed_Date`,
		`Application_Finalised_Date`,
		`Last_Action_Code`,
		`Last_Action_Comment`,
		`Last_Action_Date`,
		`uploaded`) 
		VALUES ('1',
		'".$dbArray[0]."',
		'".$dbArray[1]."',
		'".$dbArray[2]."',
		'".$dbArray[3]."',
		'".$dbArray[4]."',
		'".$dbArray[5]."',
		'".$dbArray[6]."',
		'".$dbArray[7]."',
		'".$dbArray[8]."',
		'".$dbArray[9]."',
		'".$dbArray[10]."',
		'".$dbArray[11]."',
		'".$dbArray[12]."',
		'".$dbArray[13]."',
		'".$dbArray[14]."',
		'".$dbArray[15]."',
		'".$dbArray[16]."',
		'".$dbArray[17]."',
		'".$dbArray[18]."',
		'".$dbArray[19]."',
		'".$dbArray[20]."',
		'".$dbArray[21]."',now())";
		
		mysqli_query($conn,$insert_query);
}

?>
</div>
</section>
<body>
</html>