<?php

	session_start();
	require 'config.php';
	
	$Username = mysql_real_escape_string(stripslashes($_POST['Username']));
	$Username = trim($Username);
	$Password = md5(mysql_real_escape_string(stripslashes($_POST['Password'])));
	$result = mysql_query("SELECT * FROM WA_Players WHERE name='$Username' AND pass='$Password'");
	$count = mysql_num_rows($result);
	$playerRow = mysql_fetch_row($result);
	if ($count==1){
		$hour = time() + 3600;
		$_SESSION['User'] = $playerRow[1];
		$queryAdmins = mysql_query("SELECT * FROM WA_WebAdmins WHERE name='$Username'");
		$countAdmins = mysql_num_rows($queryAdmins);
		if ($countAdmins==1){
			$_SESSION['Admin'] = "true";
		}else{
			$_SESSION['Admin'] = "false";
		}
		header("Location: ../index.php");
	}else{
		$past = time() - 100;
		unset($_SESSION['User']);
		header("Location: ../login.php?error=1");
	}
	
?>