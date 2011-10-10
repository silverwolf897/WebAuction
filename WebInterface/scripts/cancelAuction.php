<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$user = $_SESSION['User'];
	require 'config.php';
	
	$auctionId = mysql_real_escape_string(stripslashes($_GET['id']));
	$queryAuctions=mysql_query("SELECT * FROM WA_Auctions WHERE id='$auctionId'");
	list($id, $itemName, $itemDamage, $itemOwner, $itemQuantity, $itemPrice)= mysql_fetch_row($queryAuctions);
	$itemOwner = trim($itemOwner);
	//echo $itemOwner.":".$user;
	if ($itemOwner == $user){
		$queryPlayerItems =mysql_query("SELECT * FROM WA_Items WHERE player='$user'");
		$foundItem = false;
		$stackId = 0;
		$stackQuant = 0;
		while(list($pid, $pitemName, $pitemDamage, $pitemOwner, $pitemQuantity)= mysql_fetch_row($queryPlayerItems))
		{	
			if($itemName == $pitemName)
			{
				if ($pitemDamage == $itemDamage)
				{
					$foundItem = true;
					$stackId = $pid;
					$stackQuant = $pitemQuantity;
				}
			}
		}
		if ($foundItem == true)
		{
			$newQuantity = $itemQuantity + $stackQuant;
			$itemQuery = mysql_query("UPDATE WA_Items SET quantity='$newQuantity' WHERE id='$stackId'");
		}else
		{
			$itemQuery = mysql_query("INSERT INTO WA_Items (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$itemQuantity')");
		}
		$itemDelete = mysql_query("DELETE FROM WA_Auctions WHERE id='$id'");
		header("Location: ../myauctions.php");
	}else{
		header("Location: ../myauctions.php?error=1");
	}
?>