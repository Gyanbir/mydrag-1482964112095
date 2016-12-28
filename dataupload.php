<?php

error_reporting(E_ALL);
set_time_limit(0);

//date_default_timezone_set('Europe/London');
date_default_timezone_set('America/Los_Angeles');

$PageTitle="Prediction Engine - Upload Excel Data";

include_once('auth_header.php');
include 'db.php';
?>


<?php
$conn = OpenConnection();

// file has been uploaded. Perform excel parsing only if the file has been uploaded;
if(isset($_GET['upload'])) {
	$fileUploaded = $_GET['upload'];
}

//$Calc_Work_Ready_Estimated_Date="";
$user_uploaded_file_id="";
//this is a stop-gap arrangement to populate "Work Ready Estimate Date" till the time this script is not integrated with R-scripts!!
/*if(($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['submitworkflow'])) {	
	$Calc_Work_Ready_Estimated_Date = (new DateTime('2017-01-02'))->format('d-F-Y H:i:s');
}*/

$uploaded_filename="";
$uploaded_date="";	
$inputFileName="";
if ($fileUploaded || isset($_POST['user_uploaded_file_id'])) {
	
	$user_uploaded_file_id = isset($_POST['user_uploaded_file_id']) ? $_POST['user_uploaded_file_id'] : "";
	if ($user_uploaded_file_id) {
		$qry = "select id, filename, filepath, uploaded from user_uploaded_files where id=".$user_uploaded_file_id;
	} else {
		$qry = "select id, filename, filepath, uploaded from user_uploaded_files where user_id=".$userId." order by uploaded desc limit 1";
	}
	//echo $qry;
	$result = $conn->query($qry);

	if ($result->num_rows) {
		$row = $result->fetch_row();
		$user_uploaded_file_id = $row[0];
		$inputFileName = $row[2];
		$uploaded_filename=$row[1];
		$uploaded_date=$row[3];
		$result->close();
	}
	if ($inputFileName=="") {
		echo "Error fetching filename from database";
		exit();
	}
}
?>

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
		<table cellpadding="2" cellmargin="0" style="border:1px solid;padding:2 2 2 2;" width="450px" height="100px">
		<tr bgcolor="grey" style="color:white;">
			<td><b>File Id#</b></td>
			<td><b>Uploaded Report</b></td>
			<td><b>Uploaded Date</b></td>
			<?php 
			if (isset($_POST['submitworkflow'])) {
			?>
				<td><b>Output Report</b></td>
			<?php
			}
			?>
		</tr>
		<tr>
			<?php 
			if ($user_uploaded_file_id) {
			?>	
				<td><?=$user_uploaded_file_id?><td><?=$uploaded_filename?></td><td><?=$uploaded_date?></td>
				<?php 
				if (isset($_POST['submitworkflow'])) {
				?>
					<td><a href="datadownload.php?user_uploaded_file_id=<?=$user_uploaded_file_id?>"><img src="images/xlslogo.png" /></a></td>
				<?php
				}
				?>			
			<?php
			} else {
			?>
				<td colspan="3" align="center">No Files Uploaded!</td>
			<?php
			}
			?>
		</tr>
		</table>
	</div>
	
	<div id="three">

<?php

if ($fileUploaded) {
	//echo "user_uploaded_file_id inside if:".$user_uploaded_file_id;
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

	// start the row-count from 2, in the below loop, as the 1st row is the heading row in the uploaded xls sheet
	for ($row = 2; $row <= $highestRow; ++$row) {
		
		$dbArray = array();
		// column 0 to column 9 are all texts
		for ($col = 0; $col <= 9; ++$col) {
			$cell = $objWorksheet->getCellByColumnAndRow($col, $row);
			$cellVal = $cell->getValue();
			$dbArray[$col] = $cellVal;
		}
		
		//echo "printing dates of the xls rows:<BR/>";
		// column 10 to column 18 are all dates
		for ($col = 10; $col <= 18; ++$col) {
			$cell = $objWorksheet->getCellByColumnAndRow($col, $row);
			
			if ($cell->getValue()!="") {
				$dt = PHPExcel_Shared_Date::ExcelToPHPObject($cell->getValue());
				$cellVal = $dt->format('Y-m-d H:i:s');
				$dbArray[$col] = $dt->format('Y-m-d H:i:s');
			} else {
				$dbArray[$col] = null;
				//echo "inside null<br>";
			}
			//echo "cell-".$col.": ".$cell." cellVal from xls:".$cell->getValue()." cellVal changed to date:".$cellVal." cellVal going to db:".$dbArray[$col]."<BR/>";
		}
		
		// column 19 and 20 are texts
		for ($col = 19; $col <= 20; ++$col) {
			$cell = $objWorksheet->getCellByColumnAndRow($col, $row);
			$cellVal = $cell->getValue();
			$dbArray[$col] = $cellVal;
		}
		
		// column 21 is date
		$cell = $objWorksheet->getCellByColumnAndRow(21, $row);
		if ($cell->getValue()!="") {
			$dt = PHPExcel_Shared_Date::ExcelToPHPObject($cell->getValue());
			$cellVal = $dt->format('Y-m-d H:i:s');
			$dbArray[21] = $dt->format('Y-m-d H:i:s');
		} else {
			$dbArray[21] = null;
		}
		
		// debugging array values - start
		/*for ($z=0;$z<=21;++$z){
			echo "dbarray:".$dbArray[$z]."<BR>";
		}
		echo "<BR/>";*/
		// debugging array values - end
		
		//echo "user_uploaded_file_id inside for loop about to sent to function:".$user_uploaded_file_id;
		insert_data($conn, $user_uploaded_file_id, $dbArray);
	} // iterate over the data rows from excel
	
	// display the uploaded data 
	$rows = getUploadedData($conn, $user_uploaded_file_id);
	displayUploadedData($rows, $user_uploaded_file_id, false, "");

} //if ($fileUploaded)

	
if(($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['submitworkflow'])) {
	
	$user_uploaded_file_id = $_POST['user_uploaded_file_id'];
	$selected_cases_string="";
	
	foreach($_POST['applicant_case_ids'] as $selected){
		//echo "gyan check - ".$selected."</br>";
		$selected_cases_string = $selected_cases_string."'".$selected."',";
		
	}
	
	$CR_rows = getUploadedDataForCR($conn, $user_uploaded_file_id, $selected_cases_string);
	calculateAndUpdateCriticalDates($conn, $CR_rows, $user_uploaded_file_id);
	$rows = getUploadedData($conn, $user_uploaded_file_id);
	displayUploadedData($rows, $user_uploaded_file_id, true, $selected_cases_string);
}
	
CloseConnection($conn);

?>
</div>
</section>

<?php
function calculateAndUpdateCriticalDates($conn, $rows, $user_uploaded_file_id) {
	
	//Rules for Procedure_Name: CR-TEMP RES INVESTOR REN
	//iterate over the incoming rows and calcuate critical dates
	// Case_Initiation_Date is important to calculate all the critical dates - it should not be null or zero
	//echo "gyan +++++++++<br><br>";
	
	for ($i = 0; $i < count($rows); $i++) {
		$benchMarks_rows = getBenchMarks($conn, $user_uploaded_file_id, $rows[$i]['Case_Type']);	
		
		//$Case_Initiation_Date = (!empty($rows[$i]['Case_Initiation_Date']) ? (new DateTime($rows[$i]['Case_Initiation_Date']))->format('d-F-Y H:i:s') : "");
		$Case_Initiation_Date = strtotime($rows[$i]['Case_Initiation_Date']);
		//echo "Case_Initiation_Date:".$Case_Initiation_Date." formatted: ". date("m/d/Y h:i:s",$Case_Initiation_Date)."<BR/>";
		if ($Case_Initiation_Date != 0 && count($benchMarks_rows)>0) {
			for ($j = 0; $j < count($benchMarks_rows); $j++) { 
				//echo "<br>gyan in PPPP>>> ".($benchMarks_rows[$j]['benchmark_name']);
				//echo "<br>gyan in QQQQQ>>> ".($benchMarks_rows[$j]['estimated_days']);
				$add_day="+".$benchMarks_rows[$j]['estimated_days']." day";
				//echo "gtest --".strtotime($rows[$i]['First_Contact_with_Applicant_Date']);
				
				if( $benchMarks_rows[$j]['benchmark_name'] == 'BM:1b') {
					$First_Contact_with_Applicant_Date = strtotime($add_day, $Case_Initiation_Date);
					$First_Contact_with_Applicant_Date_formatted = date("Y-m-d H:i:s",$First_Contact_with_Applicant_Date);	
				}
				if( $benchMarks_rows[$j]['benchmark_name'] == 'BM:1c') {					
					$All_Documents_Received_Date = strtotime($add_day, $Case_Initiation_Date);
					$All_Documents_Received_Date_formatted = date("Y-m-d H:i:s",$All_Documents_Received_Date);	
					//echo "All_Documents_Received_Date:".$All_Documents_Received_Date." formatted: ". $All_Documents_Received_Date_formatted ."<BR/>";					
				}
				if( $benchMarks_rows[$j]['benchmark_name'] == 'BM:1d') {					
					$Application_sent_to_GE_Signature_Date = strtotime($add_day, $Case_Initiation_Date);
					$Application_sent_to_GE_Signature_Date_formatted = date("Y-m-d H:i:s",$Application_sent_to_GE_Signature_Date);	
					//echo "Application_sent_to_GE_Signature_Date:".$Application_sent_to_GE_Signature_Date." formatted: ". $Application_sent_to_GE_Signature_Date_formatted ."<BR/>";					
				}
				if( $benchMarks_rows[$j]['benchmark_name'] == 'BM:1e') {					
					$All_Documentation_Received_for_Filing_Date = strtotime($add_day, $Case_Initiation_Date);
					$All_Documentation_Received_for_Filing_Date_formatted = date("Y-m-d H:i:s",$All_Documentation_Received_for_Filing_Date);	
					//echo "All_Documentation_Received_for_Filing_Date:".$All_Documentation_Received_for_Filing_Date." formatted: ". $All_Documentation_Received_for_Filing_Date_formatted ."<BR/>";					
				}
				if( $benchMarks_rows[$j]['benchmark_name'] == 'BM:1f') {					
					$Application_Filed_Date = strtotime($add_day, $Case_Initiation_Date);
					$Application_Filed_Date_formatted = date("Y-m-d H:i:s",$Application_Filed_Date);	
					//echo "Application_Filed_Date:".$Application_Filed_Date." formatted: ". $Application_Filed_Date_formatted ."<BR/>";					
				}
				if( $benchMarks_rows[$j]['benchmark_name'] == 'BM:1g') {					
					$Application_Finalised_Date = strtotime($add_day, $Case_Initiation_Date);
					$Application_Finalised_Date_formatted = date("Y-m-d H:i:s",$Application_Finalised_Date);	
					//echo "Application_Finalised_Date:".$Application_Finalised_Date." formatted: ". $Application_Finalised_Date_formatted ."<BR/>";					
				}
				if( $benchMarks_rows[$j]['benchmark_name'] == 'BM:1j') {					
					$Predicted_Work_Ready_Date = strtotime($add_day, $Case_Initiation_Date);
					$Predicted_Work_Ready_Date_formatted = date("Y-m-d H:i:s",$Predicted_Work_Ready_Date);	
					//echo "Predicted_Work_Ready_Date:".$Predicted_Work_Ready_Date." formatted: ". $Predicted_Work_Ready_Date_formatted ."<BR/>";					
				}
				if( $benchMarks_rows[$j]['benchmark_name'] == 'BM:1h') {					
					$Last_Action_Date = strtotime($add_day, $Case_Initiation_Date);
					$Last_Action_Date_formatted = date("Y-m-d H:i:s",$Last_Action_Date);	
					//echo "Last_Action_Date:".$Last_Action_Date." formatted: ". $Last_Action_Date_formatted ."<BR/>";					
				}
			}				
			
			$update_qry = "update uploaded_data set 
							Predicted_Work_Ready_Date='".$Predicted_Work_Ready_Date_formatted."',
							First_Contact_with_Applicant_Date='".$First_Contact_with_Applicant_Date_formatted."', 
							All_Documents_Received_Date='".$All_Documents_Received_Date_formatted."',
							Application_sent_to_GE_Signature_Date='".$Application_sent_to_GE_Signature_Date_formatted."',
							All_Documentation_Received_for_Filing_Date='".$All_Documentation_Received_for_Filing_Date_formatted."',
							Application_Filed_Date='".$Application_Filed_Date_formatted."',
							Application_Finalised_Date='".$Application_Finalised_Date_formatted."',
							Last_Action_Date='".$Last_Action_Date_formatted."' where id='".$rows[$i]['id']."'";
			//echo "<br><br>update_qry:".$update_qry."<BR/>";
			mysqli_query($conn,$update_qry);
			//echo "<BR/>Updated";
			
		}
		//echo "<BR/>----------<BR/>";
	}
	//echo "gyan end +++++++++<br><br>";
}

function displayUploadedData($rows, $user_uploaded_file_id, $availableCalculatedDates, $selected_cases_string) {
	
	echo '<form action="dataupload.php" method="post"> <input type="hidden" name="submitworkflow" value="1" >';
	echo '<input type="hidden" name="user_uploaded_file_id" value="'.$user_uploaded_file_id.'" >';
	
	echo '<table id="box-table-a">' . "\n";
	echo '<thead><tr >' . "\n";
	
	//some of the columns need not be shown
	//$excelColumns = array('#', 'Case ID', 'Case Assignee First Name', 'Case Assignee Surname', 'Region', 'SSO', 'Receiving Country', 'Case Type', 'GE CoE Case Number', 'GE Business', 'Industry Focus Group', 'Case Initiation Date', 'Proposed Assignment From date', 'Work Ready Estimated Date (BM:1j)', 'First Contact with Applicant', 'All documents received from GE and Assignee to Prepare Application', 'Application sent to GE/ Assignee for Signature', 'All Documentation Received for Filing', 'Application Filed', 'Application Finalised', 'Last Action Code', 'Last Action Comment', 'Last Action Date');
	$excelColumns = array('#', 'Case ID', 'Case Assignee First Name', 'Case Assignee Surname', 'Region', 'SSO', 'Receiving Country', 'Case Type', 'GE CoE Case Number', 'GE Business', 'Industry Focus Group', 'Work Ready Estimated Date (BM:1j)', 'Last Action Code', 'Last Action Comment', 'Last Action Date');
	$arrlength = count($excelColumns);
	//echo "xls column len:".$arrlength."<BR>";
	
	for($x = 0; $x < $arrlength; $x++) {
		echo '<th scope="col" >' . $excelColumns[$x] . '</th>' . "\n";
		if ($availableCalculatedDates && $excelColumns[$x] === 'Work Ready Estimated Date (BM:1j)') {
			echo '<th scope="col"><font color="#ffff00">Predicted Work Ready Date</font></th>' . "\n";
			echo '<th scope="col"><font color="#ffff00">First Contact</font></th>' . "\n";
			echo '<th scope="col"><font color="#ffff00">All documents received</font></th>' . "\n";
			echo '<th scope="col"><font color="#ffff00">Application sent for Signature</font></th>' . "\n";
			echo '<th scope="col"><font color="#ffff00">All Documentation Received for Filing</font></th>' . "\n";
			echo '<th scope="col"><font color="#ffff00">Application Filed</font></th>' . "\n";
			echo '<th scope="col"><font color="#ffff00">Application Finalised</font></th>' . "\n";			
		}
	}
	echo '</tr></thead><tbody>' . "\n";
	
	// iterate over the rows parameter to display the values;
	//echo "xls column len:".count($rows)."<BR>";
	for ($i = 0; $i < count($rows); $i++) {
		
		//$Case_Initiation_Date = (!empty($rows[$i]['Case_Initiation_Date']) ? (new DateTime($rows[$i]['Case_Initiation_Date']))->format('d-F-Y H:i:s') : "");
		//$Proposed_Assignment_From_Date = (!empty($rows[$i]['Proposed_Assignment_From_Date']) ? (new DateTime($rows[$i]['Proposed_Assignment_From_Date']))->format('d-F-Y H:i:s') : "");
		//$Work_Ready_Estimated_Date = (!empty($rows[$i]['Work_Ready_Estimated_Date']) ? (new DateTime($rows[$i]['Work_Ready_Estimated_Date']))->format('d-F-Y H:i:s') : "");
		/*$Work_Ready_Estimated_Date = strtotime($rows[$i]['Work_Ready_Estimated_Date']);
		if ($Work_Ready_Estimated_Date != 0) {
			$Work_Ready_Estimated_Date = (new DateTime($rows[$i]['Work_Ready_Estimated_Date']))->format('d-F-Y H:i:s');
		}*/
		
		$Work_Ready_Estimated_Date = (new DateTime($rows[$i]['Work_Ready_Estimated_Date']))->format('d-M-Y H:i:s');
		$Predicted_Work_Ready_Date = (new DateTime($rows[$i]['Predicted_Work_Ready_Date']))->format('d-M-Y H:i:s');
		$First_Contact_with_Applicant_Date = (new DateTime($rows[$i]['First_Contact_with_Applicant_Date']))->format('d-M-Y H:i:s');
		$All_Documents_Received_Date = (new DateTime($rows[$i]['All_Documents_Received_Date']))->format('d-M-Y H:i:s');
		$Application_sent_to_GE_Signature_Date = (new DateTime($rows[$i]['Application_sent_to_GE_Signature_Date']))->format('d-M-Y H:i:s');
		$All_Documentation_Received_for_Filing_Date = (new DateTime($rows[$i]['All_Documentation_Received_for_Filing_Date']))->format('d-M-Y H:i:s');
		$Application_Filed_Date = (new DateTime($rows[$i]['Application_Filed_Date']))->format('d-M-Y H:i:s');
		$Application_Finalised_Date = (new DateTime($rows[$i]['Application_Finalised_Date']))->format('d-M-Y H:i:s');
		$Last_Action_Date = (new DateTime($rows[$i]['Last_Action_Date']))->format('d-M-Y H:i:s');
		
		echo '<tr>' . "\n";
		echo '<td><input type="checkbox" name="applicant_case_ids[]" value="'.$rows[$i]['CaseID'].'" ></td>' . "\n";
		echo '<td>' .  $rows[$i]['CaseID'] . '</td>' . "\n";
		//echo '<td>' .  $rows[$i]['Case_Assignee_First_Name'] . '</td>' . "\n";
		//echo '<td>' .  $rows[$i]['Case_Assignee_Surname'] . '</td>' . "\n";
		echo '<td>xxxxx</td>' . "\n"; // firstname and lastname masked
		echo '<td>xxxxx</td>' . "\n";
		echo '<td>' .  $rows[$i]['Region'] . '</td>' . "\n";
		echo '<td>' .  $rows[$i]['SSO'] . '</td>' . "\n";
		echo '<td>' .  $rows[$i]['Receiving_Country'] . '</td>' . "\n";
		echo '<td>' .  $rows[$i]['Case_Type'] . '</td>' . "\n" ;
		echo '<td>' .  $rows[$i]['GE_CoE_Case_Number'] . '</td>' . "\n" ;
		echo '<td>' .  $rows[$i]['GE_Business'] . '</td>' . "\n" ;
		echo '<td>' .  $rows[$i]['Industry_Focus_Group'] . '</td>' . "\n" ;
		//echo '<td>' .  $Case_Initiation_Date. '</td>' . "\n" ;
		//echo '<td>' .  $Proposed_Assignment_From_Date . '</td>' . "\n" ;
		
		echo '<td>' .  $Work_Ready_Estimated_Date . '</td>' . "\n" ;
		//below is a stop-gap arrangement to populate "Work Ready Estimate Date" till the time this script is not integrated with R-scripts!!
		if ($availableCalculatedDates) {			
			if( strpos( $selected_cases_string, $rows[$i]['CaseID'] ) !== false ) {
				echo '<td ><B><font color="darkblue">' .  $Predicted_Work_Ready_Date . '</font></B></td>' . "\n" ;
				echo '<td ><B><font color="darkblue">' .  $First_Contact_with_Applicant_Date . '</font></B></td>' . "\n" ;
				echo '<td ><B><font color="darkblue">' .  $All_Documents_Received_Date . '</font></B></td>' . "\n" ;
				echo '<td ><B><font color="darkblue">' .  $Application_sent_to_GE_Signature_Date . '</font></B></td>' . "\n" ;
				echo '<td ><B><font color="darkblue">' .  $All_Documentation_Received_for_Filing_Date . '</font></B></td>' . "\n" ;
				echo '<td ><B><font color="darkblue">' .  $Application_Filed_Date . '</font></B></td>' . "\n" ;
				echo '<td ><B><font color="darkblue">' .  $Application_Finalised_Date . '</font></B></td>' . "\n" ;
			} else {
				echo '<td>' .  $Predicted_Work_Ready_Date . '</td>' . "\n" ;
				echo '<td>' .  $First_Contact_with_Applicant_Date . '</td>' . "\n" ;
				echo '<td>' .  $All_Documents_Received_Date . '</td>' . "\n" ;
				echo '<td>' .  $Application_sent_to_GE_Signature_Date . '</td>' . "\n" ;
				echo '<td>' .  $All_Documentation_Received_for_Filing_Date . '</td>' . "\n" ;
				echo '<td>' .  $Application_Filed_Date . '</td>' . "\n" ;
				echo '<td>' .  $Application_Finalised_Date . '</td>' . "\n" ;
			}
			
		}
			
		//echo '<td>' .  $First_Contact_with_Applicant_Date . '</td>' . "\n" ;
		//echo '<td>' .  $All_Documents_Received_Date . '</td>' . "\n" ;
		//echo '<td>' .  $Application_sent_to_GE_Signature_Date . '</td>' . "\n";
		//echo '<td>' .  $All_Documentation_Received_for_Filing_Date . '</td>' . "\n";
		//echo '<td>' .  $Application_Filed_Date . '</td>' . "\n";
		//echo '<td>' .  $Application_Finalised_Date. '</td>' . "\n";
		echo '<td>' .  $rows[$i]['Last_Action_Code'] . '</td>' . "\n";
		echo '<td>' .  $rows[$i]['Last_Action_Comment'] . '</td>' . "\n";
		echo '<td>' .  $Last_Action_Date . '</td>' . "\n";
		echo '</tr>' . "\n";
	}
	echo '</tbody></table><input type="submit" name="SubmitForCalc" value="Calculate Work Ready Date" style="background-color:  #716967;border: none;border-radius: 3px;-moz-border-radius: 3px;-webkit-border-radius: 3px;color: #f4f4f4;cursor: pointer;font-family: Arial, Helvetica, sans-serif;height: 50px;text-transform: uppercase;width: 300px;-webkit-appearance:none;"></form>' . "\n";
}
	
function getUploadedData($conn, $user_uploaded_file_id) {
	$query = 'select id, CaseID, Case_Assignee_First_Name, Case_Assignee_Surname, Region, SSO, Receiving_Country, Case_Type, GE_CoE_Case_Number, GE_Business, Industry_Focus_Group, Case_Initiation_Date, Proposed_Assignment_From_Date, Work_Ready_Estimated_Date, Predicted_Work_Ready_Date, First_Contact_with_Applicant_Date, All_Documents_Received_Date, Application_sent_to_GE_Signature_Date, All_Documentation_Received_for_Filing_Date, Application_Filed_Date, Application_Finalised_Date, Last_Action_Code, Last_Action_Comment, Last_Action_Date from uploaded_data where user_uploaded_file_id='.$user_uploaded_file_id;
	
	$result = $conn->query($query);
	$rows = resultToArray($result);
	//echo "<BR>dumping array of rows:<BR>";
	//var_dump($rows); // Array of rows
	$result->free();
	
	return $rows;
}

function getUploadedDataForCR($conn, $user_uploaded_file_id, $selected_cases_string) {
	$query = 'select id, CaseID, Case_Assignee_First_Name, Case_Assignee_Surname, Region, SSO, Receiving_Country, Case_Type, GE_CoE_Case_Number, GE_Business, Industry_Focus_Group, Case_Initiation_Date, Proposed_Assignment_From_Date, Work_Ready_Estimated_Date, Predicted_Work_Ready_Date, First_Contact_with_Applicant_Date, All_Documents_Received_Date, Application_sent_to_GE_Signature_Date, All_Documentation_Received_for_Filing_Date, Application_Filed_Date, Application_Finalised_Date, Last_Action_Code, Last_Action_Comment, Last_Action_Date from uploaded_data where user_uploaded_file_id='.$user_uploaded_file_id;
	
	if($selected_cases_string != "") {
		$selected_cases_string = rtrim($selected_cases_string,',');
		$query = $query." and CaseID in (".$selected_cases_string.")";
	}
	//echo " <br><br>- query - ".$query;
	$result = $conn->query($query);
	$rows = resultToArray($result);
	//echo "<BR>dumping array of rows:<BR>";
	//var_dump($rows); // Array of rows
	$result->free();
	
	return $rows;
}


function resultToArray($result) {
    $rows = array();
    while($row = $result->fetch_assoc()) {
		//var_dump($row)."<BR>";
		//echo " in resultToArraywork ready date:".$row['Work_Ready_Estimated_Date']."<BR/>";
        $rows[] = $row;
    }
    return $rows;
}

	
function insert_data($conn, $user_uploaded_file_id, $dbArray) {
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
		`Last_Action_Date`) 
		VALUES ('".$user_uploaded_file_id."',
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
		'".$dbArray[21]."')";
		//echo "<BR/>ins query:".$insert_query;
		
		mysqli_query($conn,$insert_query);
}

function getBenchMarks($conn, $user_uploaded_file_id,$procedure_name) {
	$query = 
	"select benchmark_name, estimated_days from procedure_benchmarks where procedure_id in (select id from country_procedures where procedure_name='".$procedure_name."')";
	
	$result = $conn->query($query);
	$rows = resultToArray($result);
	//echo "<BR>dumping array of rows:<BR>";
	//var_dump($rows); // Array of rows
	$result->free();
	
	return $rows;
}

?>


<?php
include_once('footer.php');
?>