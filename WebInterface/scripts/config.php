<?php


/* Database config */

$db_host		= 'mywebsite.com'; //change these!!
$db_user		= 'username';//change these!!
$db_pass		= 'pasword';//change these!!
$db_database	= 'minecraft'; //change these!!

/* Market Price config */

$maxSellPrice = 10000; //this is per item
$marketDaysMin	= 14; //number of past days to take the average of the sales for to work out market price (bigger number for smaller servers)

/* Design config */

$uiPack = "start"; //name of the jquery ui pack you would like to use, "start" or "dark-hive" come installed by default find more @ http://jqueryui.com/themeroller/
$cssFile = "main"; //will be collecting a list of cool css files, but "main" is my default one

/* Other config */

$useMySQLiConomy = false; //you you have iConomy data in another table in the same database?
$sendPurchaceToMail = false; //if false send to my items, if true add to mail
$iConTableName = "iConomy"; //"iConomy" is the default table name when using MySQL with iConomy
$useBuyXMax = true; //Whether to set a maximum to buy from a buyX auction, useful to prevent large load times on items with large quantity
$buyXMax = 128; //Maximum value in the "BuyX" dropdown box

/* End config */


$marketTimeMin = $marketDaysMin * 86400;
$link = mysql_connect($db_host,$db_user,$db_pass);
if (!$link){die("Unable to connect to database".mysql_error());}

mysql_select_db($db_database,$link);
mysql_query("SET names UTF8");

?>