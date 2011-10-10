<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$user = $_SESSION['User'];
	require 'config.php';
	require 'itemInfo.php';
	$playerMoney = 0;
	if ($useMySQLiConomy){
		$queryiConomy=mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
		$iConRow = mysql_fetch_row($queryiConomy);
		$playerMoney = $iConRow['2'];
	}else{
		$playerQuery = mysql_query("SELECT * FROM WA_Players WHERE name='$user'");
		$playerRow = mysql_fetch_row($playerQuery);
		$playerMoney = $playerRow['3'];
	}
	
	
	$itemId = mysql_real_escape_string(stripslashes($_GET['id']));
	$queryAuctions=mysql_query("SELECT * FROM WA_Auctions WHERE id='$itemId'");
	list($id, $itemName, $itemDamage, $itemOwner, $itemQuantity, $itemPrice)= mysql_fetch_row($queryAuctions);
    $totalPrice = $itemPrice*$itemQuantity;
	$ownerMoney = 0;
	if ($useMySQLiConomy){
		$queryiConomyOwner=mysql_query("SELECT * FROM $iConTableName WHERE username='$itemOwner'");
		$iConOwnerRow = mysql_fetch_row($queryiConomyOwner);
		$ownerMoney = $iConOwnerRow['2'];
	}else {
		$ownerQuery = mysql_query("SELECT * FROM WA_Players WHERE name='$itemOwner'");
		$ownerRow = mysql_fetch_row($ownerQuery);
		$ownerMoney = $ownerRow['3'];
	}
	$itemFullName = getItemName($itemName, $itemDamage);
	
	//echo $playerMoney."<br/>".$itemPrice;
	//echo $iplayer;
	
	
	if ($playerMoney >= $totalPrice){
		if ($user != $itemOwner){
			$timeNow = time();
			$newPlayerMoney = $playerMoney-$totalPrice;
			$newOwnerMoney = $ownerMoney+$totalPrice;
			if ($useMySQLiConomy){
				$playerQuery = mysql_query("UPDATE $iConTableName SET balance='$newPlayerMoney' WHERE username='$user'");
				$ownerQuery = mysql_query("UPDATE $iConTableName SET balance='$newOwnerMoney' WHERE username='$itemOwner'");
				$alertQuery = mysql_query("INSERT INTO WA_SaleAlerts (seller, quantity, price, buyer, item) VALUES ('$itemOwner', '$itemQuantity', '$itemPrice', '$user', '$itemFullName')");
			}else{
				$playerQuery = mysql_query("UPDATE WA_Players SET money='$newPlayerMoney' WHERE name='$user'");
				$ownerQuery = mysql_query("UPDATE WA_Players SET money='$newOwnerMoney' WHERE name='$itemOwner'");
				$alertQuery = mysql_query("INSERT INTO WA_SaleAlerts (seller, quantity, price, buyer, item) VALUES ('$itemOwner', '$itemQuantity', '$itemPrice', '$user', '$itemFullName')");
			}
			if ($sendPurchaceToMail){
				$maxStack = getItemMaxStack($itemName, $itemDamage);
				while($itemQuantity > $maxStack)
				{
					$itemQuantity -= $maxStack;
					$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$maxStack')");
				}
				if ($itemQuantity > 0)
				{
					$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$itemQuantity')");
				}
			}else {
				$queryPlayerItems =mysql_query("SELECT * FROM WA_Items WHERE player='$user' AND name='$itemName' AND damage='$itemDamage'");
				$playerRows = mysql_num_rows($queryPlayerItems);
				$foundItem = false;
				$stackId = 0;
				$stackQuant = 0;
				while(list($pid, $pitemName, $pitemDamage, $pitemOwner, $pitemQuantity)= mysql_fetch_row($queryPlayerItems))
				{	
					$foundItem = true;
					$stackId = $pid;
					$stackQuant = $pitemQuantity;
				}
				if ($foundItem == true)
				{
					$newQuantity = $itemQuantity + $stackQuant;
					$itemQuery = mysql_query("UPDATE WA_Items SET quantity='$newQuantity' WHERE id='$stackId'");
				}else
				{
					$itemQuery = mysql_query("INSERT INTO WA_Items (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$itemQuantity')");
				}
			}
			$itemDelete = mysql_query("DELETE FROM WA_Auctions WHERE id='$itemId'");
			$logPrice = mysql_query("INSERT INTO WA_SellPrice (name, damage, time, buyer, seller, quantity, price) VALUES ('$itemName', '$itemDamage', '$timeNow', '$user', '$itemOwner', '$itemQuantity', '$itemPrice')");
			$base = isTrueDamage($itemName, $itemDamage);
			
			if ($base > 0){
				$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$itemName' AND damage='0' ORDER BY id DESC");
				
			}else{
				$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$itemName' AND damage='$itemDamage' ORDER BY id DESC");	
				
			}
			$countMarket = mysql_num_rows($queryMarket);
			if ($countMarket == 0){
				//market price not found
				$newMarketPrice = $itemPrice;
				$marketCount = $itemQuantity;
			}else{
				//found get first item
				$rowMarket = mysql_fetch_row($queryMarket);
				$marketId = $rowMarket[0];
				$marketPrice = $rowMarket[4];
				$marketCount = $rowMarket[5];
				$newMarketPrice = (($marketPrice*$marketCount)+$totalPrice)/($marketCount+$itemQuantity);
				$marketCount = $marketCount+$itemQuantity;
			}
			if ($base > 0){
				
				$newMarketPrice = ($newMarketPrice/($base - $itemDamage))*$base;
				$insertMarketPrice = mysql_query("INSERT INTO WA_MarketPrices (name, damage, time, marketprice, ref) VALUES ('$itemName', '0', '$timeNow', '$newMarketPrice', '$marketCount')");
			}else{
				$insertMarketPrice = mysql_query("INSERT INTO WA_MarketPrices (name, damage, time, marketprice, ref) VALUES ('$itemName', '$itemDamage', '$timeNow', '$newMarketPrice', '$marketCount')");
				
			}
			header("Location: ../index.php?success=1");
		}else {
			header("Location: ../index.php?error=2");
		}
	}else{
		header("Location: ../index.php?error=1");
	}
?>