<?php

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('America/Los_Angeles');


include_once('auth_header.php');
include 'db.php';
?>


<?php
$user_uploaded_file_id="";
if(isset($_GET['user_uploaded_file_id'])) {
	$user_uploaded_file_id = $_GET['user_uploaded_file_id'];
}

?>

<?php
/** Include PHPExcel */
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';

$conn = OpenConnection();

$uploaded_filename="";
$uploaded_date="";	
$inputFileName="";
$fileToWrite="";
$fileToDownload="";
if ($user_uploaded_file_id) {

	$qry = "select id, filename, filepath, uploaded from user_uploaded_files where id=".$user_uploaded_file_id;
	
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
		echo "Fatal Error! Could not fetch filename from database. Please contact Administrator! ";
		exit();
	} else {
		$currentTimeStamp = date('m-d-Y h:i:s', time());
		$uploaddir = './uploads/'."download_".$userId.'_';
		$fileToWrite = $uploaddir . $currentTimeStamp . '-'. $uploaded_filename;
		$fileToDownload = $currentTimeStamp."_".$uploaded_filename;
		
	}
} else {
	echo "Fatal Error! User Uploaded File Not Available. Please contact Administrator!";
	exit();
}
if ($fileToWrite=="" || $fileToDownload=="") {
	echo "Fatal Error! Error creating download file. Please contact Administrator!";
	exit();
}
$rows = getUploadedData($conn, $user_uploaded_file_id);
CloseConnection($conn);

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);

$excelColumns = array('Case ID', 'Case Assignee First Name', 'Case Assignee Surname', 'Region', 'SSO', 'Receiving Country', 'Case Type', 'GE CoE Case Number', 'GE Business', 'Industry Focus Group', 'Case Initiation Date', 'Proposed Assignment From date', 'Work Ready Estimated Date (BM:1j)', 'First Contact with Applicant', 'All documents received from GE and Assignee to Prepare Application', 'Application sent to GE/ Assignee for Signature', 'All Documentation Received for Filing', 'Application Filed', 'Application Finalised', 'Last Action Code', 'Last Action Comment', 'Last Action Date');


	$numCols = count($excelColumns);
	$rowCount = 1; //setting rouCount = 1 for displaying the headers
	$column = 'A';
	for($x = 0; $x < $numCols; $x++) {
		$objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, $excelColumns[$x]);
        $column++;
	}
	
	$rowCount = 2; //setting rouCount = 2 as this is the start of the data rows
	for ($i = 0; $i < count($rows); $i++) {
			
		/*if(!isset($rows[$x]))
			$value = NULL;  
		elseif ($rows[$x] != "")
			$value = strip_tags($rows[$x]);
		else  
			$value = "";
		*/
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, $rows[$i]['CaseID']);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, $rows[$i]['Case_Assignee_First_Name']);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount, $rows[$i]['Case_Assignee_Surname']);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount, $rows[$i]['Region']);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$rowCount, $rows[$i]['SSO']);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount, $rows[$i]['Receiving_Country']);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount, $rows[$i]['Case_Type']);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$rowCount, $rows[$i]['GE_CoE_Case_Number']);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$rowCount, $rows[$i]['GE_Business']);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$rowCount, $rows[$i]['Industry_Focus_Group']);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$rowCount, (new DateTime($rows[$i]['Case_Initiation_Date']))->format('d-M-Y H:i:s'));
		
		$objPHPExcel->getActiveSheet()->setCellValue('L'.$rowCount, (new DateTime($rows[$i]['Proposed_Assignment_From_Date']))->format('d-M-Y H:i:s'));
		$objPHPExcel->getActiveSheet()->setCellValue('M'.$rowCount, (new DateTime($rows[$i]['Work_Ready_Estimated_Date']))->format('d-M-Y H:i:s'));
		$objPHPExcel->getActiveSheet()->setCellValue('N'.$rowCount, (new DateTime($rows[$i]['First_Contact_with_Applicant_Date']))->format('d-M-Y H:i:s'));
		$objPHPExcel->getActiveSheet()->setCellValue('O'.$rowCount, (new DateTime($rows[$i]['All_Documents_Received_Date']))->format('d-M-Y H:i:s'));
		$objPHPExcel->getActiveSheet()->setCellValue('P'.$rowCount, (new DateTime($rows[$i]['Application_sent_to_GE_Signature_Date']))->format('d-M-Y H:i:s'));
		$objPHPExcel->getActiveSheet()->setCellValue('Q'.$rowCount, (new DateTime($rows[$i]['All_Documentation_Received_for_Filing_Date']))->format('d-M-Y H:i:s'));
		$objPHPExcel->getActiveSheet()->setCellValue('R'.$rowCount, (new DateTime($rows[$i]['Application_Filed_Date']))->format('d-M-Y H:i:s'));
		$objPHPExcel->getActiveSheet()->setCellValue('S'.$rowCount, (new DateTime($rows[$i]['Application_Finalised_Date']))->format('d-M-Y H:i:s'));
		$objPHPExcel->getActiveSheet()->setCellValue('T'.$rowCount, $rows[$i]['Last_Action_Code']);
		$objPHPExcel->getActiveSheet()->setCellValue('U'.$rowCount, $rows[$i]['Last_Action_Comment']);
		$objPHPExcel->getActiveSheet()->setCellValue('V'.$rowCount, (new DateTime($rows[$i]['Last_Action_Date']))->format('d-M-Y H:i:s'));
		
		$rowCount++;
	}
	
	$styleThinBlackBorderOutline = array(
		'borders' => array(
			'outline' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('argb' => 'FF000000'),
			),
		),
	);
	$objPHPExcel->getActiveSheet()->getStyle('A1:V1')->applyFromArray($styleThinBlackBorderOutline); // set the borderoutline for the 1st row
	
	$objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '6f74eb')
            )
        )
    )
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:V1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$objPHPExcel->getActiveSheet()->getStyle('A1:V1')->getFill()->getStartColor()->setARGB('FF6f74eb');

	$objPHPExcel->getActiveSheet()->getStyle('A1:V1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
	$objPHPExcel->getActiveSheet()->getStyle('A1:V1')->getFont()->setBold(true);
	
	$hori_center = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        )
    );
    $objPHPExcel->getActiveSheet()->getStyle("A1:V1")->applyFromArray($hori_center);
	$objPHPExcel->getActiveSheet()->getStyle("A1:V1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	
	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(60);
	
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
	
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
	
	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
	
	$objPHPExcel->getActiveSheet()->getStyle("L1")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
	
	$objPHPExcel->getActiveSheet()->getStyle("M1")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
	
	$objPHPExcel->getActiveSheet()->getStyle("N1")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
	
	$objPHPExcel->getActiveSheet()->getStyle("O1")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
	
	$objPHPExcel->getActiveSheet()->getStyle("P1")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getStyle("Q1")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
	
	$objPHPExcel->getActiveSheet()->getStyle('T1:T'.$objPHPExcel->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(25);
	
	$objPHPExcel->getActiveSheet()->getStyle('U1:U'.$objPHPExcel->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(30);
	
	$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
	

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
ob_end_clean();
$objWriter->save($fileToWrite);
header('Content-type: application/vnd.ms-excel');
//header('Content-Disposition: attachment; filename="downloaded.xlsx"');
header('Content-Disposition: attachment; filename="'.$fileToDownload.'"');
$objWriter->save('php://output');
exit;
?>

<?php

	
function getUploadedData($conn, $user_uploaded_file_id) {
	$query = 'select CaseID, Case_Assignee_First_Name, Case_Assignee_Surname, Region, SSO, Receiving_Country, Case_Type, GE_CoE_Case_Number, GE_Business, Industry_Focus_Group, Case_Initiation_Date, Proposed_Assignment_From_Date, Work_Ready_Estimated_Date, First_Contact_with_Applicant_Date, All_Documents_Received_Date, Application_sent_to_GE_Signature_Date, All_Documentation_Received_for_Filing_Date, Application_Filed_Date, Application_Finalised_Date, Last_Action_Code, Last_Action_Comment, Last_Action_Date from uploaded_data where user_uploaded_file_id='.$user_uploaded_file_id;
	
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

?>
