<div id="profile-box">
		<table cellspacing="3px">
		<tr>
		<td>
		<img width="64px" src="http://minotar.net/avatar/<?php echo $user ?>" />
		</td>
		<td>
        <p>Name: &nbsp;&nbsp;<?php echo $user?><?php if ($isAdmin == "true"){ echo " ADMIN"; } ?><br/>
		<?php if ($useMySQLiConomy){ ?>
		Money: &nbsp;$<?php echo $iConRow['2']?><br />
		<?php }else{ ?>
        Money: &nbsp;$<?php echo $playerRow['3']?><br />
		<?php } ?>
        Mail: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $mailCount ?><br />
        </p>
		</td>
		</tr>
		</table>
        
</div>
      <div id="link-box">
        <p><a href="index.php">Home</a><br />
        <a href="myitems.php">My Items</a><br />
        <a href="myauctions.php">My Auctions</a><br />
		<a href="info.php">Item Info</a><br />
		<a href="transactionLog.php">Transaction Log</a><br />
        <a href="logout.php">Logout</a></p>
        
        </div>