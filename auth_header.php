<?php
session_start();
$userId = $_SESSION["userId"];
$firstName = $_SESSION["firstName"];
//TODO: redirect to login page if user is not logged-in
if ($userId==null){
	header("Location: index.php");
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?= isset($PageTitle) ? $PageTitle : "Prediction Engine"?></title>
	<link rel="stylesheet" href="css/page.css" />
	<link rel="stylesheet" href="css/runnable.css" />
	<link rel="stylesheet" href="css/dropzone.css" />
	<script src="js/dropzone.js"></script>	
</head>

<body>

	<header id="header">
		<div class="container">
			<a href="#" class="btn-menu"></a>
			<strong class="logo"></strong>

			<ul class="login-links">
				<li class="login-sub-link"> Prediction Engine </li>
			</ul>
			<ul class="login-top-links">
				<li class="login-top-link"> Welcome <?=$firstName?>! </li>
			</ul>
			<ul class="login-sub-links">
				<li class="login-sub-link"><a href="./userfiles.php">My File(s)</a> </li>
			</ul>
			<ul class="login-sub-links">
				<li class="login-sub-link"><a href="./logout.php">Logout</a> </li>
			</ul>

		</div><!-- container -->
	</header><!-- header -->