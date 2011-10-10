<?php
	add_column_if_not_exist("WA_SellPrice", "seller");
	add_column_if_not_exist("WA_SellPrice", "buyer");



function add_column_if_not_exist($table, $column, $column_attr = "VARCHAR( 255 ) NULL" ){
    $exists = false;
    $columns = mysql_query("show columns from $table");
    while($c = mysql_fetch_assoc($columns)){
        if($c['Field'] == $column){
            $exists = true;
            break;
        }
    }      
    if(!$exists){
        mysql_query("ALTER TABLE `$table` ADD `$column`  $column_attr");
    }
}
?>