<?php

error_reporting(E_ALL);
set_time_limit(0);

//date_default_timezone_set('Europe/London');
date_default_timezone_set('America/Los_Angeles');

$PageTitle="Prediction Engine - User Files";

include_once('auth_header.php');
include 'db.php';
?>


<?php


// database connection - move this to some function or package

$conn = OpenConnection();

// file has been uploaded. Perform excel parsing only if the file has been uploaded;
// TODO: check the existence of this variable in the URL and then only GET it

$Calc_Work_Ready_Estimated_Date="";
$user_uploaded_file_id="";
if(($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['submitworkflow'])) {
	
	$Calc_Work_Ready_Estimated_Date = (new DateTime('2016-11-28'))->format('d-F-Y H:i:s');
}

$uploaded_filename="";
$uploaded_date="";	
if ($userId != null) {
	
	$qry = "select id, filename, filepath, uploaded from user_uploaded_files where user_id=".$userId." order by uploaded desc";
	
	$result = $conn->query($qry);
	
	$rows = array();
    while($row = $result->fetch_assoc()) {
		//var_dump($row)."<BR>";
//echo "inside while";
        $rows[] = $row;
    }
	$result->free();
	
//var_dump($rows)."<BR>"; 
	//echo count($rows);
}
?>

<section style="margin:0px 0 0 30px;width:80%">
    <div>
	<h1 style="text-align: center;font-size:23px;width:80%">User Files:
	</h1>
	<table id="box-table-a">
		<thead><tr>
			<th scope="col" >File Name</th><th scope="col" >File Path</th><th scope="col" >Uploaded Date</th>
		</tr>
		</thead>
		<tbody>
			<?php
			if (count($rows) >0)
			{
				
				for ($i = 0; $i < count($rows); $i++) {					
					echo "<tr><td>".$rows[$i]['filename']."</td><td><a href='".$rows[$i]['filepath']."'>".$rows[$i]['filepath']."</a></td><td>".$rows[$i]['uploaded']."</td></tr>";
				}			
			
			} else {
			?>
				<td colspan="2" align="center">No Files Uploaded!</td>
			<?php
			}
			?>
		</tbody>
	</table>
	</div>
	
	
</section>

<?php
include_once('footer.php');
?>