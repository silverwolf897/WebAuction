<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$user = $_SESSION['User'];
	require 'config.php';
	require 'config.php';
	require 'itemInfo.php';
	$playerMoney = 0;
	if ($useMySQLiConomy){
		$queryiConomy=mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
		$iConRow = mysql_fetch_row($queryiConomy);
		//echo "mysql<br/>";
		$playerMoney = round($iConRow['2'],2);
		//echo $playerMoney."<br/>";
	}else{
		$playerQuery = mysql_query("SELECT * FROM WA_Players WHERE name='$user'");
		$playerRow = mysql_fetch_row($playerQuery);
		$playerMoney = $playerRow['3'];
		//echo "not mysql<br/>";
	}
	
	$itemId = $_POST['ID'];
	$buyQuantity = $_POST['Quantity'];
	if (!is_numeric($buyQuantity)) {
		header("Location: ../index.php?error=3");
	}
	$queryAuctions=mysql_query("SELECT * FROM WA_Auctions WHERE id='$itemId'");
	list($id, $itemName, $itemDamage, $itemOwner, $itemQuantity, $itemPrice)= mysql_fetch_row($queryAuctions);
    $totalPrice = round($itemPrice*$buyQuantity, 2);
	$numberLeft = $itemQuantity-$buyQuantity;
	if ($numberLeft < 0){
		header("Location: ../index.php?error=3");
	}
	else if ($numberLeft == 0){
		header("Location: buyItem.php?id=$itemId");
	}else{
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
	if ($playerMoney >= $totalPrice){
		if ($user != $itemOwner){
			$timeNow = time();
			$newPlayerMoney = $playerMoney-$totalPrice;
			$newOwnerMoney = $ownerMoney+$totalPrice;		
			if ($useMySQLiConomy){
				$playerQuery = mysql_query("UPDATE $iConTableName SET balance='$newPlayerMoney' WHERE username='$user'");
				$ownerQuery = mysql_query("UPDATE $iConTableName SET balance='$newOwnerMoney' WHERE username='$itemOwner'");
				$alertQuery = mysql_query("INSERT INTO WA_SaleAlerts (seller, quantity, price, buyer, item) VALUES ('$itemOwner', '$buyQuantity', '$itemPrice', '$user', '$itemFullName')");
			}else{
				$playerQuery = mysql_query("UPDATE WA_Players SET money='$newPlayerMoney' WHERE name='$user'");
				$ownerQuery = mysql_query("UPDATE WA_Players SET money='$newOwnerMoney' WHERE name='$itemOwner'");
				$alertQuery = mysql_query("INSERT INTO WA_SaleAlerts (seller, quantity, price, buyer, item) VALUES ('$itemOwner', '$buyQuantity', '$itemPrice', '$user', '$itemFullName')");
			}
			if ($sendPurchaceToMail){
				$maxStack = getItemMaxStack($itemName, $itemDamage);
				while($buyQuantity > $maxStack)
				{
					$buyQuantity -= $maxStack;
					$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$maxStack')");
				}
				if ($buyQuantity > 0)
				{
					$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$buyQuantity')");
				}
			}else{
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
					$newQuantity = $buyQuantity + $stackQuant;
					$itemQuery = mysql_query("UPDATE WA_Items SET quantity='$newQuantity' WHERE id='$stackId'");
				}else
				{
					$itemQuery = mysql_query("INSERT INTO WA_Items (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$buyQuantity')");
				}
			}
			$itemDelete = mysql_query("UPDATE WA_Auctions SET quantity='$numberLeft' WHERE id='$itemId'");
			$logPrice = mysql_query("INSERT INTO WA_SellPrice (name, damage, time, buyer, seller, quantity, price) VALUES ('$itemName', '$itemDamage', '$timeNow', '$user', '$itemOwner', '$buyQuantity', '$itemPrice')");
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
				$marketCount = $buyQuantity;
			}else{
				//found get first item
				$rowMarket = mysql_fetch_row($queryMarket);
				$marketId = $rowMarket[0];
				$marketPrice = $rowMarket[4];
				$marketCount = $rowMarket[5];
				$newMarketPrice = (($marketPrice*$marketCount)+$totalPrice)/($marketCount+$buyQuantity);
				$marketCount = $marketCount+$buyQuantity;
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
		//echo "not enough money";
	}
	}
?>