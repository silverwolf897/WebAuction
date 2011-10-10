<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$user = $_SESSION['User'];
	require 'config.php';
	require 'itemInfo.php';
	
	$itemId = mysql_real_escape_string(stripslashes($_GET['id']));
	$queryItems=mysql_query("SELECT * FROM WA_Items WHERE id='$itemId'");
	list($id, $itemName, $itemDamage, $itemOwner, $itemQuantity)= mysql_fetch_row($queryItems);
	$maxStack = getItemMaxStack($itemName, $itemDamage);
	if ($user == $itemOwner){
		while($itemQuantity > $maxStack)
		{
			$itemQuantity -= $maxStack;
			$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$maxStack')");
		}
		if ($itemQuantity > 0)
		{
			$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$itemQuantity')");
		}
		$itemDelete = mysql_query("DELETE FROM WA_Items WHERE id='$itemId'");
		header("Location: ../myitems.php");
	}else {
		header("Location: ../myitems.php?error=1");
	}

?>