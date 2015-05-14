<?php

namespace config;

$file = '../config/Db.php';

if (is_file ( $file )) {
	include $file;
	$db_config = Db::$default;
}

namespace Helper;

$file_promotion = '../helper/Promotion.php';
if (is_file ( $file_promotion )) {
	include $file_promotion;
}

try {
	// Open database connection
	$con = mysql_connect ( $db_config ['host'] . ":" . $db_config ['port'], $db_config ['dbuser'], $db_config ['dbpassword'] );
	mysql_set_charset ( "utf8", $con );
	mysql_query ( 'SET NAMES utf8' );
	mysql_select_db ( "web_statis", $con );
	
	if (isset ( $_GET ['range'] ) && ! empty ( $_GET ['range'] )) {
		$time = explode ( " - ", $_GET ['range'] );
		$start_time = $time ['0'];
		$end_time = $time ['1'];
	} else {
		$start_time = date ( "m\/d\/Y", strtotime ( "-2 day" ) );
		$end_time = date ( "m\/d\/Y", strtotime ( "-1 day" ) );
	}
	
	$sql = "SELECT SUM(`pv`) AS pv,SUM(`ip`) AS ip,SUM(`uv`) AS uv,SUM(`newUv`) AS newUv,`time` FROM DAY WHERE TIME >= '2013-12-01' AND TIME <= '2013-12-15' GROUP BY TIME ORDER BY TIME";
	$result = mysql_query ( $sql );
	
	$rows = array ();
	while ( $row = mysql_fetch_array ( $result ) ) {
		$rows [strtotime ( $row ['time'] )] = array (
				'pv' => $row ['pv'],
				'ip' => $row ['ip'],
				'uv' => $row ['uv'],
				'newUv' => $row ['newUv'] 
		);
	}
	mysql_close ( $con );
	echo json_encode ( $rows );
} catch ( Exception $ex ) {
	// Return error message
	$jTableResult = array ();
	$jTableResult ['Result'] = "ERROR";
	$jTableResult ['Message'] = $ex->getMessage ();
	print json_encode ( $jTableResult );
}

?>