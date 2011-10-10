<?php

	session_start();
	
	$past = time() - 100;
	unset($_SESSION['User']);
	//setcookie(User, gone, $past);
	header("Location: index.php");
	
?>