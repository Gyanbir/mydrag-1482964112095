<?php

$PageTitle="Prediction Engine - Home Page";

include_once('header.php');
?>

	<div id="login">
		<h1><strong>Login to Prediction Engine!</h1>
		<?php
		if ($_GET['err']) {
		?>
		<div><font color="red">Invalid Username and Password!</font></div>
		<?php
		}
		?>
		
		<form action="login.php" method="post">
			<fieldset>
				<p><input type="text" name="username" value="Username" onBlur="if(this.value=='')this.value='Username'" onFocus="if(this.value=='Username')this.value='' "></p>
				<p><input type="password" name="password" value="Password" onBlur="if(this.value=='')this.value='Password'" onFocus="if(this.value=='Password')this.value='' "></p>
				<!--<p><a href="#">Forgot Password?</a></p>-->
				<p><input type="submit" value="Login"></p>
			</fieldset>
		</form>	
	</div> <!-- end login -->

<?php
include_once('footer.php');
?>