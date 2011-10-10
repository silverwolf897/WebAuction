<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$user = $_SESSION['User'];
	require 'scripts/config.php';
	require 'scripts/itemInfo.php';
	require 'scripts/updateTables.php';
	$isAdmin = $_SESSION['Admin'];
	$queryAuctions=mysql_query("SELECT * FROM WA_Auctions");
	if ($useMySQLiConomy){
		$queryiConomy=mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
		$iConRow = mysql_fetch_row($queryiConomy);
	}
	$queryMySales=mysql_query("SELECT * FROM WA_SellPrice WHERE seller='$user'");
	$queryMyPurchases=mysql_query("SELECT * FROM WA_SellPrice WHERE buyer='$user'");

	$playerQuery = mysql_query("SELECT * FROM WA_Players WHERE name='$user'");
	$playerRow = mysql_fetch_row($playerQuery);
	$mailQuery = mysql_query("SELECT * FROM WA_Mail WHERE player='$user'");
	$mailCount = mysql_num_rows($mailQuery);
	
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/media/images/favicon.ico" />
		
		<title>WebAuction</title>
		<style type="text/css" title="currentStyle">
			@import "css/table_jui.css";
			@import "css/<?php echo $uiPack?>/jquery-ui-1.8.16.custom.css";
		</style>
        <link rel="stylesheet" type="text/css" href="css/<?php echo $cssFile?>.css" />
		<script type="text/javascript" language="javascript" src="js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script>
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				oTable = $('#example').dataTable({
					"bJQueryUI": true,
					"sPaginationType": "full_numbers"
				});
				jTable = $('#example2').dataTable({
					"bJQueryUI": true,
					"sPaginationType": "full_numbers"
				});
			} );
		</script>
	</head>
	<div id="holder">
		<?php include("topBoxes.php"); ?>
		<h1>Web Auction</h1>
		<br/>
		<h2>My Items Bought</h2>
        
			
	  <div class="demo_jui">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
	<thead>
		<tr>
			<th>Time</th>
			<th>Item</th>
			<th>Seller</th>
            <th>Quantity</th>
            <th>Price (Each)</th>
			<th>Price (Total)</th>	
			<th>% of Market Price</th>
		</tr>
	</thead>
	<tbody>
	<?php
	while(list($id, $name, $damage, $time, $quantity, $price, $seller, $buyer)= mysql_fetch_row($queryMyPurchases))
    { 
		$marketPrice = getMarketPrice($name, $damage, $marketTimeMin);
		$timeAgo = time() - $time;
		#Made by Insidiea :D ----------------------------	
		if ($timeAgo == 1) {
			$val = "Second ago";
			$div = 0;
		}
		if (($timeAgo > 1)&&($timeAgo < 60)) {
			$val = "Seconds ago";
			$div = 0;
		}
		if (($timeAgo >= 60)&&($timeAgo < 120)){
			$val = "Minute ago";
			$div = 60;
		}
		if (($timeAgo >= 120)&&($timeAgo < 3600)){
			$val = "Minutes ago";
			$div = 60;
		}
		if (($timeAgo >= 3600)&&($timeAgo < 7200)){
			$val = "Hour ago";
			$div = 3600;
		}
		if (($timeAgo >= 7200)&&($timeAgo < 86400)){
			$val = "Hours ago";
			$div = 3600;
		}
		if (($timeAgo >= 86400)&&($timeAgo < 172800)){
			$val = "Day ago";
			$div = 86400;
		}
		if ($timeAgo >= 172800){
			$val = "Days ago";
			$div = 86400;
		}
		$timeString = round($timeAgo/$div) ." ". $val;
		#-------------------------------------------------
		
		
		
		
		
		if ($marketPrice > 0)
		{
			$marketPercent = round((($price/$marketPrice)*100), 1);
		}
		else
		{
			$marketPercent = "N/A";
		}
		if ($marketPercent == "N/A")
		{
			$grade = "gradeU";
		}
		else if ($marketPercent <= 50)
		{
			$grade = "gradeA";
		}
		else if ($marketPercent <= 150)
		{
			$grade = "gradeC";
		}
		else
		{
			$grade = "gradeX";
		}
	?>
    	
        <tr class="<?php echo $grade ?>">
			<td><?php echo $timeString ?></td>
			<td><img src="<?php echo getItemImage($name, $damage) ?>" alt="<?php echo getItemName($name, $damage) ?>"/><br/><?php echo getItemName($name, $damage) ?></td>
			<td><img width="32px" src="http://minotar.net/avatar/<?php echo $seller ?>" /><br/><?php echo $seller ?></td>
          <td><?php echo $quantity ?></td>
          <td class="center"><?php echo $price ?></td>
			<td class="center"><?php echo $price*$quantity ?></td>
			
			<td class="center"><?php echo $marketPercent ?></td>
			
		</tr>
    <?php } ?>
	</tbody>
</table>	
        <h2>My Items Sold</h2>
        
			
	  <div class="demo_jui">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example2">
	<thead>
		<tr>
			<th>Date</th>
			<th>Item</th>
			<th>Buyer</th>
            <th>Quantity</th>
            <th>Price (Each)</th>
			<th>Price (Total)</th>	
			<th>% of Market Price</th>
		</tr>
	</thead>
	<tbody>
	<?php
	while(list($id, $name, $damage, $time, $quantity, $price, $seller, $buyer)= mysql_fetch_row($queryMySales))
    { 
		$marketPrice = getMarketPrice($name, $damage, $marketTimeMin);
		$timeAgo = time() - $time;
		#Made by Insidiea :D ----------------------------	
		if ($timeAgo == 1) {
			$val = "Second ago";
			$div = 0;
		}
		if (($timeAgo > 1)&&($timeAgo < 60)) {
			$val = "Seconds ago";
			$div = 0;
		}
		if (($timeAgo >= 60)&&($timeAgo < 120)){
			$val = "Minute ago";
			$div = 60;
		}
		if (($timeAgo >= 120)&&($timeAgo < 3600)){
			$val = "Minutes ago";
			$div = 60;
		}
		if (($timeAgo >= 3600)&&($timeAgo < 7200)){
			$val = "Hour ago";
			$div = 3600;
		}
		if (($timeAgo >= 7200)&&($timeAgo < 86400)){
			$val = "Hours ago";
			$div = 3600;
		}
		if (($timeAgo >= 86400)&&($timeAgo < 172800)){
			$val = "Day ago";
			$div = 86400;
		}
		if ($timeAgo >= 172800){
			$val = "Days ago";
			$div = 86400;
		}
		$timeString = round($timeAgo/$div) ." ". $val;
		#-------------------------------------------------
		$marketPrice = getMarketPrice($name, $damage, $marketTimeMin);
		if ($marketPrice > 0)
		{
			$marketPercent = round((($price/$marketPrice)*100), 1);
		}
		else
		{
			$marketPercent = "N/A";
		}
		if ($marketPercent == "N/A")
		{
			$grade = "gradeU";
		}
		else if ($marketPercent <= 50)
		{
			$grade = "gradeA";
		}
		else if ($marketPercent <= 150)
		{
			$grade = "gradeC";
		}
		else
		{
			$grade = "gradeX";
		}
	?>
    	
        <tr class="<?php echo $grade ?>">
			<td><?php echo $timeString ?></td>
			<td><img src="<?php echo getItemImage($name, $damage) ?>" alt="<?php echo getItemName($name, $damage) ?>"/><br/><?php echo getItemName($name, $damage) ?></td>
			<td><img width="32px" src="scripts/mcface.php?user=<?php echo $buyer ?>" /><br/><?php echo $buyer ?></td>
          <td><?php echo $quantity ?></td>
          <td class="center"><?php echo $price ?></td>
			<td class="center"><?php echo $price*$quantity ?></td>
			
			<td class="center"><?php echo $marketPercent ?></td>
		</tr>
    <?php } ?>
	</tbody>
</table>
			</div>
			<div class="spacer"></div>
			
			<?php include("footer.php"); ?>
		</div>
	
	</body>
</html>