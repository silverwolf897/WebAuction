<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$user = $_SESSION['User'];
	require 'scripts/config.php';
	require 'scripts/itemInfo.php';
	$isAdmin = $_SESSION['Admin'];
	$queryAuctions=mysql_query("SELECT * FROM WA_Auctions");
	if ($useMySQLiConomy){
		$queryiConomy=mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
		$iConRow = mysql_fetch_row($queryiConomy);
	}
	$queryItems=mysql_query("SELECT * FROM WA_Items WHERE player='$user'");
	$queryAuctions=mysql_query("SELECT * FROM WA_Auctions WHERE player='$user'"); 

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
			} );
		</script>
	</head>
	<div id="holder">
		<?php include("topBoxes.php"); ?>
		<h1>Web Auction</h1>
			<br/>
        
        <p style="color:red"><?php
	if(isset($_GET['error'])) {
	if($_GET['error']==1){
		echo "There was an error when removing that auction.";
	}else if($_GET['error']==2){
		echo "You do not have enough items.";
	}else if($_GET['error']==3){
		echo "Price was not a number.";
	}else if($_GET['error']==4){
		echo "Quantity was not an integer.";
	}else if($_GET['error']==5){
		echo "Quantity not a valid number.";
	}else if($_GET['error']==6){
		echo "Price not a valid number.";
	}
	}

?></p>
		<div id="new-auction-box">
<h2>Create a new auction</h2>
<form action="scripts/newAuction.php" method="post" name="auction">
<label>Item</label><select name="Item" class="select">
<?php 
while(list($id, $name, $damage, $player, $quantity)= mysql_fetch_row($queryItems))
    { 
	$marketPrice = getMarketPrice($name, $damage);
	if ($marketPrice == 0){$marketPrice = "N/A";}
	?>
  		<option value="<?php echo $name ?>:<?php echo $damage ?>"><?php echo getItemName($name, $damage) ?> (<?php echo $quantity ?>) (Average $<?php echo $marketPrice ?>)</option>

<?php }?>
</select><br />
<label>Quantity</label><input name="Quantity" type="text" class="input" size="10" /><br />
<label>Price (Per Item)</label><input name="Price" type="text" class="input" size="10" /><br />
<label>&nbsp;</label><input name="Submit" type="submit" class="button" />
</form>
</div>	
<h2>My Auctions</h2>
	  <div class="demo_jui">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
	<thead>
		<tr>
			<th>Item</th>
			<th>Quantity</th>
            <th>Price (Each)</th>
			<th>Price (Total)</th>			
			<th>% of Market Price</th>
			<th>Cancel</th>
		</tr>
	</thead>
	<tbody>
	<?php
	while(list($id, $name, $damage, $player, $quantity, $price)= mysql_fetch_row($queryAuctions))
    { 
		$marketPrice = getMarketPrice($name, $damage);
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
			<td><img src="<?php echo getItemImage($name, $damage) ?>" alt="<?php echo getItemName($name, $damage) ?>"/><br/><?php echo getItemName($name, $damage) ?></td>
			<td><?php echo $quantity ?></td>
			<td class="center"><?php echo $price ?></td>
			<td class="center"><?php echo $price*$quantity ?></td>
			<td class="center"><?php echo $marketPercent ?></td>
			<td class="center"><a href="scripts/cancelAuction.php?id=<?php echo $id ?>">Cancel</a></td>
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