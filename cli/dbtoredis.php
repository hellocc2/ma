<?php
if (substr(php_sapi_name(), 0, 3) !== 'cli') {  
    die("This Programe can only be run in CLI mode");  
}
//echo sync_edm_catalog('SEM_1_fr_gdn_c1742_FR_Cardiganhomme_140224');exit;
error_reporting ( E_ALL ^ E_NOTICE );
ini_set ( 'display_errors', 'On' );
date_default_timezone_set ( 'Asia/Chongqing' );
ini_set ( 'memory_limit', '1024M' );
ini_set ( 'default_charset', 'utf-8' );

$pid = pcntl_fork();

if ($pid == -1) {
    //错误处理：创建子进程失败时返回-1.
     die('could not fork');
} else if ($pid) {
     //父进程会得到子进程号，所以这里是父进程执行的逻辑
     pcntl_wait($status); //等待子进程中断，防止子进程成为僵尸进程。
} else {

/*DEBUG THE hell of ASCII*/
//$variable = "gitCelebrit%C3%A0linkSpeciale";
//$variable = 'GDECOSPLAYKOST%FCMEKAUFEN';
//$variable = 'ru%E2%80%A6%08';
//$variable = 'gitCelebrit%C3%A0linkSpeciale';
//$variable = "t=%B1Z%2B%26k%C9%BF%B1%3Fh%3Fd%3F%9F%2Fa%90%3Ft%C5%8B%A0%3F-%F9s%D5d+%E2sJ-B%9DE%D0T%FA%A4.%93%AF%A05%98d%F9%85%CC%22H%3Fd%F9%9D%C3%22hE%8B%C1%D65%3F%A8%3A%25%24&charset=ISO-8859-1";
//echo replaceURLEncoded($variable);
//exit;

/* 配置数据库 */

/* ora 的 */
$ora_host = "192.168.11.26";
$ora_port = "1521";
$ora_sid = "orcl";
$ora_username = "bi";
$ora_password = "bi";
$charset = "UTF8"; ### zhs16gbk ###

/* milanoo 主站的 */
define("mysql_host","192.168.11.85");
define("mysql_port","3306");
define("mysql_username","andy_excel");
define("mysql_password","andy_excel");

/* web_statis 的 */
//ini_set ( 'mysql.default_port', '5029' );

function bi_connect() {
	$bi_statis_db = mysql_connect ( "192.168.11.91", "zhoupeng", "zhoupeng0422", true ) or die ( "Cann't open database!!! case:" . mysql_error () );
	mysql_select_db ( 'bi_data' );
	$bi_result = mysql_query ( "set names 'utf8'" );
	return $bi_statis_db;
}

function db_connect() {
	$web_statis_db = mysql_connect ( "192.168.11.23", "bi", "bi", true ) or die ( "Cann't open database!!! case:" . mysql_error () );
	mysql_select_db ( 'web_statis' );
	$mysql_result = mysql_query ( "set names 'utf8'" );
	return $web_statis_db;
}

function milanoo_connect() {
	$milanoo_db = mysql_connect ( mysql_host, mysql_username, mysql_password, true ) or die ( "Cann't open database!!! case:" . mysql_error () );
	mysql_select_db ( 'milanoo' );
	$milanoo_result = mysql_query ( "set names 'utf8'" );
	return $milanoo_db;
}

$web_statis_link = db_connect ();

$ora_connstr = "(description=(address=(protocol=tcp)
(host=" . $ora_host . ")(port=" . $ora_port . "))
(connect_data=(service_name=" . $ora_sid . ")))";
$conn = oci_connect ( $ora_username, $ora_password, $ora_connstr );
if (! $conn) {
	$e = oci_error ();
	print htmlentities ( $e ['message'] );
}

$stime = microtime ( true ); // 获取程序开始执行的时间
if (isset ( $argv [1] ) and isset ( $argv [2] )) {
	/* 导入开始日期 2013/8/21 2013/8/22 */
	$s_time = strtotime ( $argv [1] );
	$e_time = strtotime ( $argv [2] );
	$update_s_time = strtotime ( "-1 week",strtotime ( $argv [1] ) );
} else {
	$s_time = strtotime ( date ( "Y/m/d", strtotime ( "yesterday" ) ) );
	$e_time = strtotime ( date ( "Y/m/d", strtotime ( "yesterday" ) ) );
	$update_s_time = strtotime ( "-1 week",strtotime ( "yesterday" ) );
}

$acceptLangCookie = array ('en-uk' => 'EN', 'ja-jp' => 'JP', 'fr-fr' => 'FR', 'es-sp' => 'ES', 'de-ge' => 'DE', 'it-it' => 'IT', 'ru-ru' => 'RU', 'pt-pt' => 'PT', 'all' => 'all' );

$brower_type = array ('%msie%' => 'IE', '%firefox%' => 'Firefox', '%chrome%' => 'Chrome', '%opera%' => 'Opera', '%version%safari%' => 'Safari' );

for($d = $s_time; $d <= $e_time; $d = $d + 86400) {
	$day_array [] = $d;
}

for($update_d = $update_s_time; $update_d <= $s_time; $update_d = $update_d + 86400) {
	$update_day_array [] = $update_d;
}

for($h = $s_time; $h <= $e_time + 86399; $h = $h + 3600) {
	$hour_array [] = $h;
}

// foreach ( $hour_array as $time_value ) {
// var_dump( date ( 'Y-m-d H:i:s', $time_value + 3599 ));
// echo "\n";
// }


for($h = $s_time; $h <= $e_time + 86399; $h = $h + 60) {
	$minutes_array [] = $h;
}

// 计算referer相关信息
function getRefererHost($refererInfo) {
	if (strlen ( $refererInfo ) < 1)
		return "direct";
	$hostInfo = parse_url ( $refererInfo );
	if (isset ( $hostInfo ['host'] )) {
		// if(substr($hostInfo['host'],0,11)=='www.google.
		return $hostInfo ['host'];
	}
	return "direct";
}
// parse_str ( $baseData[5], $pageData );
// $refererInfo = $pageData['ref'];
// var_dump(getRefererHost());
// var_dump($update_day_array);exit;

foreach ( $day_array as $time_value ) {
	$s_timerang = date ( 'Y-m-d 00:00:00', $time_value );
	$e_timerang = date ( 'Y-m-d 23:59:59', $time_value );
	
	// 时间戳
	$s_timestamp = strtotime($s_timerang);
	$e_timestamp = strtotime($e_timerang);
	
	$time_rang = " gmt_datetime>=to_date('" . $s_timerang . "','yyyy-mm-dd hh24:mi:ss') and gmt_datetime<=to_date('" . $e_timerang . "','yyyy-mm-dd hh24:mi:ss')";
	$query = "DELETE FROM `day` WHERE `time` = '".$s_timerang."';";
	mysql_query ( $query ,$web_statis_link);
	$query = "DELETE FROM `ma_promotion_visits` WHERE `time` = '".$s_timerang."';";
	mysql_query ( $query ,$web_statis_link);
	$query = "DELETE FROM `bi_adcost` WHERE `date` = '".$s_timerang."';";
	mysql_query ( $query ,$web_statis_link);
	//$query = "DELETE FROM `ma_page_visits` WHERE `time` = '".$s_timerang."';";
	//mysql_query ( $query ,$web_statis_link);
    
	// 每日的数据
	// 这里开始屏蔽
	echo "\n";
	echo '----------------------------------------------------------------- ' . $code . '@' . $s_timerang . '--------------------------------------------------------';
	echo "\n";
	
	// PV IP UV
	$query = "select site_lang,WEBSITEID,COUNT(ip),COUNT(DISTINCT ip),COUNT(DISTINCT session_id) from web_log_result where ";
	$query .= $time_rang . " GROUP BY site_lang,WEBSITEID";
	$oracle_result = oci_parse ( $conn, $query );
	oci_execute ( $oracle_result );
	while ( $row = oci_fetch_array ( $oracle_result, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
		if ($row ['SITE_LANG'] == '') {
			$row ['SITE_LANG'] = 'all';
		}
		$lang = trim ( $row ['SITE_LANG'] );
		$lang = $acceptLangCookie [$lang];
		if ($lang == NULL) {
			continue;
		}
		$query = "INSERT INTO day ( id , pv , ip , uv ,lang , time ,websiteid)  VALUES ( NULL, '" . $row ['COUNT(IP)'] . "', '" . $row ['COUNT(DISTINCTIP)'] . "', '" . $row ['COUNT(DISTINCTSESSION_ID)'] . "', '" . $lang . "', '" . $s_timerang . "' , '" . $row ['WEBSITEID'] . "')";
		$mysql_insert_result = mysql_query ( $query ,$web_statis_link);
	}
	oci_free_statement($oracle_result);
	
	$query = "select site_lang,WEBSITEID,COUNT(DISTINCT session_id) from web_log_result where ";
	$query .= $time_rang . " and is_newuser = '1' GROUP BY site_lang,WEBSITEID";
	$oracle_result = oci_parse ( $conn, $query );
	oci_execute ( $oracle_result );
	while ( $row = oci_fetch_array ( $oracle_result, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
		if ($row ['SITE_LANG'] == '') {
			$row ['SITE_LANG'] = 'all';
		}
		$lang = trim ( $row ['SITE_LANG'] );
		$lang = $acceptLangCookie [$lang];
		if ($lang == NULL) {
			continue;
		}
		$query = "UPDATE day set newUv = '" . $row ['COUNT(DISTINCTSESSION_ID)'] . "' where lang ='" . $lang . "' and time ='" . $s_timerang . "' and websiteid='" . $row ['WEBSITEID'] . "'";
		$mysql_insert_result = mysql_query ( $query ,$web_statis_link);
	}
	oci_free_statement($oracle_result);
	
	//统计每天访问深度和平均停留时间
	echo "\n";
	echo '----------------------------------------------------------------- @' . $s_timerang . '-- order products---------------------------------------';
	echo "\n";
	$website_array = get_website ();
	//print_r($website_array);exit;
	foreach ( $website_array as $v ) {
		foreach ( $acceptLangCookie as $key => $value ) {
			$query = "select avg(COUNT(ip)) as visitdepth,AVG (max(GMT_DATETIME) - min(GMT_DATETIME)) * 24*3600 as avgtime from web_log_result where ";
			$query .= $time_rang . " and site_lang='$key' and WEBSITEID='$v' GROUP BY session_id";
			$oracle_result = oci_parse ( $conn, $query );
			oci_execute ( $oracle_result );
			while ( $row = oci_fetch_array ( $oracle_result, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
				$query = "UPDATE day set visitdepth = '" . $row ['VISITDEPTH'] . "',visittime='" . $row ["AVGTIME"] . "' where lang ='" . $value . "' and time ='" . $s_timerang . "' and websiteid='$v'";
				$mysql_insert_result = mysql_query ( $query,$web_statis_link);
			}
		}
	}
	oci_free_statement($oracle_result);
	
	//统计每天订单数及注册数和订单
	echo "\n";
	echo '----------------------------------------------------------------- @' . $s_timerang . '-- order products ---------------------------------------';
	echo "\n";
	
	$order_array = get_order ( $time_value, $acceptLangCookie );
	db_connect ();
	
	foreach ( $order_array as $key => $value ) {
		foreach ( $value as $k => $v ) {
			$sql = "update day set paynum='" . $v ['pay'] . "',notpaynum='" . $v ['notpay'] . "',member='" . $v ['member'] . "',payproduct='" . $v ['payproduct'] . "',paypostage='" . $v ['paypostage'] . "' where lang='$k' and time='$s_timerang' and websiteid='$key'";
			mysql_query ( $sql,$web_statis_link );
		}
	}
	
	//统计外链访问
	echo "\n";
	echo '----------------------------------------------------------------- @' . $s_timerang . '-- promotion ---------------------------------------------';
	echo "\n";
	
	$query = "select upper(promotionurl) as promotionurl,site_lang,WEBSITEID,COUNT(ip),COUNT(DISTINCT ip),COUNT(DISTINCT session_id) from web_log_result where ";
	$query .= $time_rang . " and promotionurl is not null and promotionurl!='-' GROUP BY upper(promotionurl),site_lang ,WEBSITEID";
	$oracle_result = oci_parse ( $conn, $query );
	
	if (! $oracle_result) {
		$e = oci_error ( $conn );
		print htmlentities ( $e ['message'] );
		exit ();
	}
	oci_execute ( $oracle_result );
	$milanoo_link = milanoo_connect();
	$bi_link = bi_connect();
	while ( $row = oci_fetch_array ( $oracle_result, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
		if ($row ['SITE_LANG'] == '') {
			$row ['SITE_LANG'] = 'all';
		}
		
		$WebsiteId = $row ["WEBSITEID"];
		
		$lang = trim ( $row ['SITE_LANG'] );
		$lang = $acceptLangCookie [$lang];
		
		if ($lang == NULL) {
			continue;
		}
		  
		$row ["PROMOTIONURL"] = replaceURLEncoded($row ["PROMOTIONURL"]);
		if (empty($row ["PROMOTIONURL"])){
			continue;
		}
		$sql = "select id,PromotionName from milanoo_promotionurl where PromotionName='" . $row ["PROMOTIONURL"] . "'";
		$p_query = mysql_query ( $sql, $web_statis_link );
 		$p_result = mysql_fetch_row ( $p_query );

		if (! $p_result ) {
			$query = "INSERT INTO milanoo_promotionurl ( PromotionName ) VALUES ( '" . $row ["PROMOTIONURL"] . "' )";
			$mysql_insert_result = mysql_query ( $query, $web_statis_link );
			$promotionid = mysql_insert_id ($web_statis_link);
		} else {
			$promotionid = $p_result [0];
		}
		
		$catalog = sync_edm_catalog($row ["PROMOTIONURL"]);
		
		//同步 EDM CID 到数据库
		if (! empty ( $catalog )) {
			$query = "UPDATE milanoo_promotionurl set category_id = '" . $catalog . "' where id = '" . $promotionid . "'";
			mysql_query ( $query, $web_statis_link );
		}
		//同步 EDM CID 到数据库
		
		$milanoo_lang = array_search ( $lang, $acceptLangCookie );
			
		// 如果是 WAP 站的 $row["WEBSITEID"] = 101 米兰表 device_type = 2
		if ($row ["WEBSITEID"] == '101') {
			$where = "device_type = 2 AND ";
			$WebsiteId = 1;
		} elseif ($row ["WEBSITEID"] == '201') {
			$where = "device_type = 5 AND ";
			$WebsiteId = 1;
		} else {
			$where = "device_type = 1 AND ";
		}
		
		//获得 bi_adcost 数据
		$sql = "SELECT SUM(impressions),SUM(adClicks),SUM(adcostUsd) FROM bi_data.`bi_adcost` WHERE " . $where . "`Promotion` = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND lang = '" . $milanoo_lang . "' and `date` = '" .date ( 'Y-m-d', $time_value ). "'";
		$bi_query = mysql_query( $sql, $bi_link );
		if ($bi_query){
			$bi_result = mysql_fetch_row ( $bi_query );
		}
		
		$impressions = $bi_result [0];
		$adClicks = $bi_result [1];
		$adcostUsd = $bi_result [2];
		
		
		//获得某个 PROMOTIONURL 当时的注册用户数
		$sql = "SELECT COUNT(MemberId) AS regmember FROM `milanoo_member` WHERE " . $where . "`PromotionURL` = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND MemberLang = '" . $milanoo_lang . "' AND MemberUserTime > '" . $s_timestamp . "' AND MemberUserTime < '" . $e_timestamp . "'";
		$milanoo_query = mysql_query( $sql, $milanoo_link );
		
		if ($milanoo_query){
			$regmember_row = mysql_fetch_row($milanoo_query);
		}
		$regmember = $regmember_row[0];
		
		//获得某个 PROMOTIONURL 当时的订阅用户数
		$sql = "SELECT COUNT(id) AS subscribers FROM `milanoo_mail_del` WHERE `PromotionURL` = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND lang = '" . $milanoo_lang . "' AND ADDTIME > '" . $s_timestamp . "' AND ADDTIME < '" . $e_timestamp . "'";
		$milanoo_query = mysql_query( $sql, $milanoo_link );
		if ($milanoo_query){
			$subscribers_row = mysql_fetch_row($milanoo_query);
		}
		$subscribers = $subscribers_row[0];

		//获得某个 PROMOTIONURL 当时支付订单数
		$sql = "SELECT COUNT(OrdersId) AS OrdersNUM FROM `milanoo_orders` s WHERE " . $where . "Promotion = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND `OrdersEstate` != 'RefuseOrders' AND `OrdersAddTime` > '" . $s_timestamp . "' AND `OrdersPay` > '0' AND `OrdersAddTime` < '" . $e_timestamp . "' AND lang = '" . $milanoo_lang . "'";
		$milanoo_query = mysql_query( $sql, $milanoo_link );
		if ($milanoo_query){
			$OrdersNUM_row = mysql_fetch_row($milanoo_query);
		}
		$OrdersNUM = $OrdersNUM_row[0];		

		//获得某个 PROMOTIONURL 当时未支付订单数
		$sql = "SELECT COUNT(OrdersId) AS OrdersNUM FROM `milanoo_orders` s WHERE " . $where . "Promotion = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND `OrdersEstate` != 'RefuseOrders' AND `OrdersAddTime` > '" . $s_timestamp . "' AND `OrdersPay` = '0' AND `OrdersAddTime` < '" . $e_timestamp . "' AND lang = '" . $milanoo_lang . "'";
		$milanoo_query = mysql_query( $sql, $milanoo_link );
		
		if ($milanoo_query){
			$Orders_Unpay_NUM_row = mysql_fetch_row($milanoo_query);
		}
		$Orders_Unpay_NUM = $Orders_Unpay_NUM_row[0];		
		
		//获得某个 PROMOTIONURL 当时的订单总金额
		$sql = "SELECT SUM(ROUND((OrdersAmount + OrdersLogisticsCosts + IF( insurance, insurance, 0 )) * IFNULL(SUBSTRING_INDEX(exchange_rate,',',-1),1) / IFNULL(SUBSTRING_INDEX(exchange_rate,',',1),1),2)) AS OrdersAmount FROM `milanoo_orders` s WHERE " . $where . "Promotion = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND `OrdersEstate` != 'RefuseOrders' AND `OrdersAddTime` > '" . $s_timestamp . "' AND `OrdersPay` > '0' AND `OrdersAddTime` < '" . $e_timestamp . "' AND lang = '" . $milanoo_lang . "'";
		$milanoo_query = mysql_query( $sql, $milanoo_link );
		if ($milanoo_query){
			$OrdersAmount_row = mysql_fetch_row($milanoo_query);
		}
		$OrdersAmount = $OrdersAmount_row[0];
		
		$query = "INSERT INTO ma_promotion_visits ( impressions, adClicks, adcostUsd, pv, ip, uv, promotionid, lang, time, websiteid, payorder, payamount, regmember, subscribers, unpayorder)  VALUES ('" . $impressions . "','" . $adClicks . "','" . $adcostUsd . "', '" . $row ['COUNT(IP)'] . "', '" . $row ['COUNT(DISTINCTIP)'] . "', '" . $row ['COUNT(DISTINCTSESSION_ID)'] . "', '" . $promotionid . "','" . $lang . "', '" . $s_timerang . "','".$row["WEBSITEID"]."','".$OrdersNUM."','".$OrdersAmount."','".$regmember."','".$subscribers."','".$Orders_Unpay_NUM."')";
		$mysql_insert_result = mysql_query ( $query,$web_statis_link );
		
		if (!empty($impressions) or !empty($adClicks) or !empty($adcostUsd)) {
			$query = "INSERT INTO bi_adcost ( impressions, adClicks, adcostUsd, date, websiteId, lang, promotionid ) VALUES (  '" . $impressions . "','" . $adClicks . "','" . $adcostUsd . "','" . date ( 'Y-m-d 00:00:00', $time_value ) . "','".$row['WEBSITEID']."','".$lang."','".$promotionid."')";
			$mysql_insert_result = mysql_query ( $query,$web_statis_link );			
		}
		
		unset($impressions);
		unset($adClicks);
		unset($adcostUsd);
		
		unset($regmember);
		unset($regmember_row);
		unset($subscribers);
		unset($subscribers_row);
		unset($milanoo_lang);
		unset($where);
		unset($promotionid);
		//mysql_free_result ( $milanoo_query );
		//mysql_free_result ( $p_query );
	}
	oci_free_statement($oracle_result);
	
	$query = "select promotionurl,site_lang,WEBSITEID,COUNT(DISTINCT session_id) from web_log_result where ";
	$query .= $time_rang . " and promotionurl is not null and promotionurl!='-' and is_newuser = '1' GROUP BY promotionurl,site_lang,WEBSITEID";
	
	$oracle_result = oci_parse ( $conn, $query );
	oci_execute ( $oracle_result );
	while ( $row = oci_fetch_array ( $oracle_result, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
		if ($row ['SITE_LANG'] == '') {
			$row ['SITE_LANG'] = 'all';
		}
		$lang = trim ( $row ['SITE_LANG'] );
		$lang = $acceptLangCookie [$lang];
		if ($lang == NULL) {
			continue;
		}
		$query = "UPDATE ma_promotion_visits set newUv = '" . $row ['COUNT(DISTINCTSESSION_ID)'] . "' where lang ='" . $lang . "' and time ='" . $s_timerang . " ' and websiteid='" . $row ['WEBSITEID'] . "'";
		$mysql_insert_result = mysql_query ( $query,$web_statis_link );
	}
	oci_free_statement($oracle_result);

	echo "\n";
	echo '-----------------------------------------------------------------Now memory_get_usage: ' . round((memory_get_usage()/(1024)/(1024)),3) . " Mb \n";
	echo '-----------------------------------------------------------------referer NewUV while end-------------------------';
	echo "\n";

}

//绑定处理
sync_edm_cid_cname();
sync_seo();
sync_edm();
sync_sem();

exit;
//更新一个星期内的数据
foreach ( $update_day_array as $time_value ) {
	$s_timerang = date ( 'Y-m-d 00:00:00', $time_value );
	$e_timerang = date ( 'Y-m-d 23:59:59', $time_value );
	
	// 时间戳
	$s_timestamp = strtotime ( $s_timerang );
	$e_timestamp = strtotime ( $e_timerang );
	
	$time_rang = " gmt_datetime>=to_date('" . $s_timerang . "','yyyy-mm-dd hh24:mi:ss') and gmt_datetime<=to_date('" . $e_timerang . "','yyyy-mm-dd hh24:mi:ss')";
	
	// 统计外链访问数据补全
	echo "\n";
	echo '----------------------------------------------------------------- @' . $s_timerang . '-- promotion ---------------------------------------------';
	echo "\n";
	
	$query = "select upper(promotionurl) as promotionurl,site_lang,WEBSITEID,COUNT(ip),COUNT(DISTINCT ip),COUNT(DISTINCT session_id) from web_log_result where ";
	$query .= $time_rang . " and promotionurl is not null and promotionurl!='-' GROUP BY upper(promotionurl),site_lang ,WEBSITEID";
	$oracle_result = oci_parse ( $conn, $query );
	if (! $oracle_result) {
		$e = oci_error ( $conn );
		print htmlentities ( $e ['message'] );
		exit ();
	}
	oci_execute ( $oracle_result );
	//$milanoo_link = milanoo_connect ();
	while ( $row = oci_fetch_array ( $oracle_result, OCI_ASSOC + OCI_RETURN_NULLS ) ) {
		if ($row ['SITE_LANG'] == '') {
			$row ['SITE_LANG'] = 'all';
		}
		
		$WebsiteId = $row ["WEBSITEID"];
		
		if ($row ["WEBSITEID"] == NULL) {
			$WebsiteId = 1;
			$row ["WEBSITEID"] = 1;
		}
		
		$lang = trim ( $row ['SITE_LANG'] );
		$lang = $acceptLangCookie [$lang];
		
		if ($lang == NULL) {
			continue;
		}
		
		$row ["PROMOTIONURL"] = replaceURLEncoded($row ["PROMOTIONURL"]);
		if (empty($row ["PROMOTIONURL"])){
			continue;
		}
		$sql = "select id,PromotionName from milanoo_promotionurl where PromotionName='" . $row ["PROMOTIONURL"] . "'";
		$p_query = mysql_query ( $sql, $web_statis_link );
		$p_result = mysql_fetch_row ( $p_query );
		$promotionid = $p_result [0];
		mysql_free_result ( $p_query );

		if (! $p_result ) {
			$query = "INSERT INTO milanoo_promotionurl ( PromotionName ) VALUES ( '" . $row ["PROMOTIONURL"] . "' )";
			$mysql_insert_result = mysql_query ( $query, $web_statis_link );
			$promotionid = mysql_insert_id ($web_statis_link);
		}
		
		//echo "\n";
		//echo '-----------------------------------------------------------------promotion id: ' . $promotionid . "\n";
		//echo "\n";
		
		$milanoo_lang = array_search ( $lang, $acceptLangCookie );
			
		// 如果是 WAP 站的 $row["WEBSITEID"] = 101 米兰表 device_type = 2
		if ($row ["WEBSITEID"] == '101') {
			$where = "device_type = 2 AND ";
			$WebsiteId = 1;
		} elseif ($row ["WEBSITEID"] == '201') {
			$where = "device_type = 5 AND ";
			$WebsiteId = 1;
		} else {
			$where = "device_type = 1 AND ";
		}

		//获得 bi_adcost 数据
// 		$sql = "SELECT impressions,adClicks,adcostUsd FROM bi_data.`bi_adcost` WHERE " . $where . "`Promotion` = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND lang = '" . $milanoo_lang . "' and `date` = '" .date ( 'Y-m-d', $time_value ). "'";
// 		$milanoo_query = mysql_query( $sql, $milanoo_link );
// 		if ($milanoo_query){
// 			$bi_result = mysql_fetch_row ( $milanoo_query );
// 		}
		
// 		$impressions = $bi_result [0];
// 		$adClicks = $bi_result [1];
// 		$adcostUsd = $bi_result [2];
		
		//获得某个 PROMOTIONURL 当时的注册用户数
		$sql = "SELECT COUNT(MemberId) AS regmember FROM `milanoo_member` WHERE " . $where . "`PromotionURL` = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND MemberLang = '" . $milanoo_lang . "' AND MemberUserTime > '" . $s_timestamp . "' AND MemberUserTime < '" . $e_timestamp . "'";
		$milanoo_query = mysql_query( $sql, $milanoo_link );
		
		if ($milanoo_query){
			$regmember_row = mysql_fetch_row($milanoo_query);
		}
		$regmember = $regmember_row[0];
		
		//获得某个 PROMOTIONURL 当时的订阅用户数
		$sql = "SELECT COUNT(id) AS subscribers FROM `milanoo_mail_del` WHERE `PromotionURL` = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND lang = '" . $milanoo_lang . "' AND ADDTIME > '" . $s_timestamp . "' AND ADDTIME < '" . $e_timestamp . "'";
		$milanoo_query = mysql_query( $sql, $milanoo_link );
		if ($milanoo_query){
			$subscribers_row = mysql_fetch_row($milanoo_query);
		}
		$subscribers = $subscribers_row[0];

		//获得某个 PROMOTIONURL 当时支付订单数
		$sql = "SELECT COUNT(OrdersId) AS OrdersNUM FROM `milanoo_orders` s WHERE " . $where . "Promotion = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND `OrdersEstate` != 'RefuseOrders' AND `OrdersAddTime` > '" . $s_timestamp . "' AND `OrdersPay` > '0' AND `OrdersAddTime` < '" . $e_timestamp . "' AND lang = '" . $milanoo_lang . "'";
		$milanoo_query = mysql_query( $sql, $milanoo_link );
		if ($milanoo_query){
			$OrdersNUM_row = mysql_fetch_row($milanoo_query);
		}
		$OrdersNUM = $OrdersNUM_row[0];	

		//获得某个 PROMOTIONURL 当时未支付订单数
		$sql = "SELECT COUNT(OrdersId) AS OrdersNUM FROM `milanoo_orders` s WHERE " . $where . "Promotion = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND `OrdersEstate` != 'RefuseOrders' AND `OrdersAddTime` > '" . $s_timestamp . "' AND `OrdersPay` = '0' AND `OrdersAddTime` < '" . $e_timestamp . "' AND lang = '" . $milanoo_lang . "'";
		$milanoo_query = mysql_query( $sql, $milanoo_link );
		if ($milanoo_query){
			$Orders_Unpay_NUM_row = mysql_fetch_row($milanoo_query);
		}
		$Orders_Unpay_NUM = $Orders_Unpay_NUM_row[0];		
		
		//获得某个 PROMOTIONURL 当时的订单总金额
		$sql = "SELECT SUM(ROUND((OrdersAmount + OrdersLogisticsCosts + IF( insurance, insurance, 0 )) * IFNULL(SUBSTRING_INDEX(exchange_rate,',',-1),1) / IFNULL(SUBSTRING_INDEX(exchange_rate,',',1),1),2)) AS OrdersAmount FROM `milanoo_orders` s WHERE " . $where . "Promotion = '" . $row ["PROMOTIONURL"] . "' AND WebsiteId = '".$WebsiteId."' AND `OrdersEstate` != 'RefuseOrders' AND `OrdersAddTime` > '" . $s_timestamp . "' AND `OrdersPay` > '0' AND `OrdersAddTime` < '" . $e_timestamp . "' AND lang = '" . $milanoo_lang . "'";
		$milanoo_query = mysql_query( $sql, $milanoo_link );
		if ($milanoo_query){
			$OrdersAmount_row = mysql_fetch_row($milanoo_query);
		}
		$OrdersAmount = $OrdersAmount_row[0];
		
		//echo "\n";
		//echo '-----------------------------------------------------------------UPDATE ma_promotion_visits promotion id: ' . $promotionid . "\n";
		//echo "\n";

		$query = "UPDATE ma_promotion_visits set payamount = '" . $OrdersAmount . "',unpayorder = '" . $Orders_Unpay_NUM . "',payorder = '" . $OrdersNUM . "', regmember = '" . $regmember . "', subscribers = '" . $subscribers . "' where promotionid = '" . $promotionid . "' AND lang ='" . $lang . "' and time ='" . $s_timerang . " ' and websiteid='" . $row ['WEBSITEID'] . "'";
		mysql_query ( $query, $web_statis_link );
		
// 		if (!empty($impressions) or !empty($adClicks) or !empty($adcostUsd)) {
// 			echo $query = "UPDATE bi_adcost set impressions = '" . $impressions . "', adClicks = '" . $adClicks . "', adcostUsd = '" . $adcostUsd . "' where date ='" . date ( 'Y-m-d 00:00:00', $time_value ) . "' AND websiteId ='" . $row['WEBSITEID'] . "' AND lang ='" . $lang . "' AND promotionid ='" . $promotionid . "'";
// 			mysql_query ( $query, $web_statis_link );
// 		}
		
// 		unset($impressions);
// 		unset($adClicks);
// 		unset($adcostUsd);
		
		unset ( $regmember );
		unset ( $regmember_row );
		unset ( $subscribers );
		unset ( $subscribers_row );
		unset ( $milanoo_lang );
		unset ( $where );
		//mysql_free_result ( $milanoo_query );
	}
	oci_free_statement($oracle_result);

	echo "\n";
	echo '-----------------------------------------------------------------Now memory_get_usage: ' . round((memory_get_usage()/(1024)/(1024)),3) . " Mb \n";
	echo '-----------------------------------------------------------------referer week date end-------------------------';
	echo "\n";
}
}

function get_order($time_value, $acceptLangCookie) {
	$end_date = $time_value + 86400;
	$dbbase = mysql_connect ( "192.168.11.85", "andy_excel", "andy_excel", true ) or die ( "Cann't open database!!! case:" . mysql_error () );
	mysql_select_db ( 'milanoo' );
	$mysql_result = mysql_query ( "set names 'utf8'" );
	
	$sql = "select count(OrdersId) as OrderNum,lang,WebsiteId,device_type from milanoo_orders where OrdersAddTime>='$time_value' AND OrdersAddTime<'$end_date' and OrdersPay>0  group by lang,WebsiteId,device_type";
	$mysql_result = mysql_query ( $sql ) or die ( mysql_error () );
	$order_pay = array ();
	while ( $row = mysql_fetch_assoc ( $mysql_result ) ) {
		if ($row ['lang'] == '') {
			$row ['lang'] = 'all';
		}
		$lang = trim ( $row ['lang'] );
		$lang = $acceptLangCookie [$lang];
		if ($lang == NULL) {
			continue;
		}
		if ($row ["device_type"] == 5) {
			$order_pay ["pay"] [$row ["device_type"]] [201] [$lang] += $row ["OrderNum"];
		} else {
			$order_pay ["pay"] [$row ["device_type"]] [$row ["WebsiteId"]] [$lang] = $row ["OrderNum"];
		}
	}
	
	$sql = "select count(OrdersId) as OrderNum,lang,WebsiteId,device_type from milanoo_orders where OrdersAddTime>='$time_value' and OrdersAddTime<'$end_date' and OrdersEstate!='RefuseOrders' group by lang,WebsiteId,device_type";
	$mysql_result = mysql_query ( $sql ) or die ( mysql_error () );
	//$order_pay = array ();
	while ( $row = mysql_fetch_assoc ( $mysql_result ) ) {
		if ($row ['lang'] == '') {
			$row ['lang'] = 'all';
		}
		$lang = trim ( $row ['lang'] );
		$lang = $acceptLangCookie [$lang];
		if ($lang == NULL) {
			continue;
		}
		if ($row ["device_type"] == 5) {
			$order_pay ["notpay"] [$row ["device_type"]] [201] [$lang] += $row ["OrderNum"];
		} else {
			$order_pay ["notpay"] [$row ["device_type"]] [$row ["WebsiteId"]] [$lang] = $row ["OrderNum"];
		}
	}

	$sql = "select count(MemberId) as num,MemberLang,WebsiteId,device_type from `milanoo_member` where MemberUserTime>='$time_value' and MemberUserTime<'$end_date' and MemberLang!='' group by MemberLang,WebsiteId,device_type";
	$mysql_result = mysql_query ( $sql ) or die ( mysql_error () );
	//$order_pay = array ();
	while ( $row = mysql_fetch_assoc ( $mysql_result ) ) {
		if ($row ['MemberLang'] == '') {
			$row ['MemberLang'] = 'all';
		}
		$lang = trim ( $row ['MemberLang'] );
		$lang = $acceptLangCookie [$lang];
		if ($lang == NULL) {
			continue;
		}
		if ($row ["device_type"] == 5) {
			$order_pay ["member"] [$row ["device_type"]] [201] [$lang] += $row ["num"];
		} else {
			$order_pay ["member"] [$row ["device_type"]] [$row ["WebsiteId"]] [$lang] = $row ["num"];
		}
		
	}
	
	$sql = "select a.`WebsiteId`,a.device_type,a.lang,ROUND(sum(a.`OrdersAmount` * c.ex_rate / d.`ex_rate`),2) as pm,ROUND(sum(a.`OrdersLogisticsCosts` * c.ex_rate / d.`ex_rate`),2) as fm FROM `milanoo_orders` a,`t_exchange_rate` c,`t_exchange_rate` d WHERE a.`CurrencyCode` = c.`currency` AND d.`currency` = 'USD' AND a.ordersaddtime >='$time_value' AND a.ordersaddtime <'$end_date' AND a.orderspay > 0 group by a.lang,a.`WebsiteId`,a.device_type";
	$mysql_result = mysql_query ( $sql ) or die ( mysql_error () );
	//$order_pay = array ();
	while ( $row = mysql_fetch_assoc ( $mysql_result ) ) {
		if ($row ['lang'] == '') {
			$row ['lang'] = 'all';
		}
		$lang = trim ( $row ['lang'] );
		$lang = $acceptLangCookie [$lang];
		if ($lang == NULL) {
			continue;
		}
		if ($row ["device_type"] == 5) {
			$order_pay ["payproduct"] [$row ["device_type"]] [201] [$lang] = $row ["pm"];
			$order_pay ["paypostage"] [$row ["device_type"]] [201] [$lang] = $row ["fm"];
		} else {
			$order_pay ["payproduct"] [$row ["device_type"]] [$row ["WebsiteId"]] [$lang] = $row ["pm"];
			$order_pay ["paypostage"] [$row ["device_type"]] [$row ["WebsiteId"]] [$lang] = $row ["fm"];
		}
	}
	
	$order_array = array ();
	
	foreach ( $order_pay as $key => $value ) {
		foreach ( $value as $k => $v ) {
			if ($k == 2) {
				foreach ( $v as $kk => $vv ) {
					foreach ( $vv as $kkk => $vvv ) {
						$order_array [101] [$kkk] [$key] = $vvv;
					}
				}
			} 
			elseif ($k==5) {
				foreach ( $v as $kk => $vv ) {
					foreach ( $vv as $kkk => $vvv ) {
						$order_array [201] [$kkk] [$key] = $vvv;
					}
				}
			}	
			elseif ($k==1) {
				foreach ( $v as $kk => $vv ) {
					foreach ( $vv as $kkk => $vvv ) {
						$order_array [$kk] [$kkk] [$key] = $vvv;
					}
				}
			}
		}
	}
	mysql_close ();
	return $order_array;
}

function get_website() {
	$dbbase = mysql_connect ( "192.168.11.85", "andy_excel", "andy_excel", true ) or die ( "Cann't open database!!! case:" . mysql_error () );
	mysql_select_db ( 'milanoo_gaea' );
	$mysql_result = mysql_query ( "set names 'utf8'" );
	
	$sql = "select * from web_site";
	$mysql_result = mysql_query ( $sql ) or die ( mysql_error () );
	$order_pay = array ();
	while ( $row = mysql_fetch_assoc ( $mysql_result ) ) {
		$website_array [] = $row ["websiteid"];
	}
	$website_array[101] = 101;
	$website_array[201] = 201;
	mysql_close ();
	return $website_array;
}

	//同步所属分类
function sync_sem() {
	$website_array = array('3'=>'SEM-Lolitashow','4'=>'SEM-Cosplayshow','5'=>'SEM-Costumeslive','7'=>'SEM-Milanoofr');
	$sync_sem_link = db_connect ();
	$sql = "SELECT id,PromotionName FROM `milanoo_promotionurl` WHERE `PromotionName` REGEXP '^sem_' AND category = '0'";
	$query = mysql_query ( $sql, $sync_sem_link );
	while ( $row = mysql_fetch_assoc ( $query ) ) {
		$PromotionNames = explode("_", $row['PromotionName']);
		$PromotionName = $PromotionNames['2'];
		$websiteid = $PromotionNames['1'];
	
		// echo $row['PromotionName']. "\n";
		// echo $websiteid . "\n";
		
		if ($websiteid == 1 ) {
			//$where = $website_array[$websiteid];
	   		//$sql = "SELECT `Id`,`name` FROM `milanoo_promotion_category` WHERE `name` = '".$where."' AND `WebsiteId` = 1";
		} else {
			$where = $website_array[$websiteid];
	   		$sql = "SELECT `Id`,`name` FROM `milanoo_promotion_category` WHERE `name` = '".$where."' AND `WebsiteId` = 1";
			$select_semname_query = mysql_query( $sql, $sync_sem_link );
			if ($select_semname_query){
				$seminfo = mysql_fetch_row ( $select_semname_query );
				$update_query = "UPDATE `milanoo_promotionurl` set category = '" . $seminfo ['0'] . "' WHERE category = '0' and id ='" . $row ['id'] . "'";
				$sem_insert_result = mysql_query ( $update_query ,$sync_sem_link);
			}
		}
	}
	//mysqli_close($sync_sem_link);
}
	
	//同步商品分类
function sync_edm() {
	$sync_edm_link = db_connect ();
	$sql = "SELECT id,PromotionName FROM `milanoo_promotionurl` WHERE `PromotionName` REGEXP '^edm_' AND category = '0'";
	$query = mysql_query ( $sql, $sync_edm_link );
	while ( $row = mysql_fetch_assoc ( $query ) ) {
		$PromotionNames = explode("_", $row['PromotionName']);
		$PromotionName = $PromotionNames['1'];
		
		$sql = "SELECT `Id`,`name` FROM `milanoo_promotion_category` WHERE `name` LIKE 'email-".$PromotionName."%' AND `WebsiteId` = 1";
		$select_edmname_query = mysql_query( $sql, $sync_edm_link );
		if ($select_edmname_query){
			$edminfo = mysql_fetch_row ( $select_edmname_query );
			$update_query = "UPDATE `milanoo_promotionurl` set category = '" . $edminfo ['0'] . "' WHERE category = '0' and id ='" . $row ['id'] . "'";
			$edm_insert_result = mysql_query ( $update_query ,$sync_edm_link);
		}
	}
	//mysqli_close($sync_edm_link);
}

function sync_seo() {
	$sync_seo_link = db_connect ();
	$sql = "SELECT id FROM `milanoo_promotion_category` WHERE `name` = 'seo'";
	$select_seo_id_query = mysql_query( $sql, $sync_seo_link );
	if ($select_seo_id_query){
		$seo_id = mysql_fetch_row ( $select_seo_id_query );
		$sql = "SELECT id FROM `milanoo_promotionurl` WHERE `PromotionName` REGEXP '^seo_' AND category = 0";
		$query = mysql_query ( $sql, $sync_seo_link );
		while ( $row = mysql_fetch_assoc ( $query ) ) {
			$update_query = "UPDATE `milanoo_promotionurl` set category = '" . $seo_id[0] . "' WHERE category = '0' and id ='" . $row ['id'] . "'";
			$edm_insert_result = mysql_query ( $update_query ,$sync_seo_link);
		}
	}
	//mysqli_close($sync_edm_link);
}

//这个有问题。。。
function sync_edm_catalog($promotionid) {
	$txt = $promotionid;
	
	$re1 = '^(SEM_)'; // Word 1
	$re2 = '.*?'; // Non-greedy match on filler
	              // $re3 = '_'; // Uninteresting: c
	              // $re4 = '.*?'; // Non-greedy match on filler
	              // $re5 = '_'; // Uninteresting: c
	              // $re6 = '.*?'; // Non-greedy match on filler
	              // $re7 = '_'; // Uninteresting: c
	              // $re8 = '.*?'; // Non-greedy match on filler
	              // $re9 = '_'; // Uninteresting: c
	              // $re10 = '.*?'; // Non-greedy match on filler
	$re11 = '(_)'; // Any Single Character 1
	$re12 = '(c)'; // Any Single Character 2
	$re13 = '(\\d+)'; // Integer Number 1
	                  // if ($c = preg_match_all ( "/" . $re1 . $re2 . $re3 . $re4 . $re5 . $re6 . $re7 . $re8 . $re9 . $re10 . $re11 . $re12 . "/is", $txt, $matches )) {
	if ($c = preg_match_all ( "/" . $re1 . $re2 . $re11 . $re12 . $re13 . "/is", $txt, $matches )) {
		$word1 = $matches [1] [0];
		$c1 = $matches [2] [0];
		$c2 = $matches [3] [0];
		$int1 = $matches [4] [0];
		//print "($word1) ($c1) ($c2) ($int1)\n";
		return $int1;
	}
}

function sync_edm_cid_cname() {
	$sync = db_connect ();
	$sync_milanoo = milanoo_connect();
	$sql = "SELECT id,`category_id` FROM `milanoo_promotionurl` WHERE category_id >0 AND category_parent_id IS NULL";
	$query = mysql_query ( $sql, $sync );
	while ( $row = mysql_fetch_assoc ( $query ) ) {
		$cid = $row['category_id'];
		$id = $row['id'];
		$sql = "SELECT b.`id`, b.`category_code` , c.`category_name` FROM milanoo_gaea.`products_categories` a, milanoo_gaea.`products_categories` b, milanoo_gaea.`products_categories_lang` c WHERE LEFT(a.`category_code`, 5) = b.`category_code` AND b.`id` = c.`products_categorie_id` AND c.`language_id` = 1 AND c.`data_status` = 0 AND a.`data_status` = 0 AND b.`data_status` = 0 AND a.`id` = ".$cid;
		$select_category_info_query = mysql_query( $sql, $sync_milanoo );
		if ($select_category_info_query){
			$categoryinfo = mysql_fetch_row ( $select_category_info_query );
			$update_query = "UPDATE `milanoo_promotionurl` set category_name = '" . $categoryinfo ['2'] . "',category_parent_id = '" . $categoryinfo ['0'] . "'WHERE id ='" . $id . "'";
			$sync_insert_result = mysql_query ( $update_query ,$sync);
		}
	}
	unset($sync);
	unset($sql);
	unset($query);
	unset($row);
	unset($cid);
	unset($id);
	unset($select_category_info_query);
	unset($categoryinfo);
	unset($update_query);
	unset($sync_insert_result);
}

function to_utf8( $string ) { 
// From http://w3.org/International/questions/qa-forms-utf-8.html 
    if ( preg_match('%^(?: 
      [\x09\x0A\x0D\x20-\x7E]            # ASCII 
    | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte 
    | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs 
    | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte 
    | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates 
    | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3 
    | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15 
    | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16 
)*$%xs', $string) ) { 
        return $string; 
    } else { 
        return iconv( 'CP1252', 'UTF-8', $string); 
    } 
}

function replaceAccents($str) {
	$search = explode(",",
"ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,Ø,Å,Á,À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ú,Ù,Û,Ü,Ÿ,Ç,Æ,Œ");
	$replace = explode(",",
"c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,O,A,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,Y,C,AE,OE");
	return str_replace($search, $replace, $str);
}

function replaceURLEncoded($variable) {
	$search = explode(",",
"%08,%09,%0A,%0D,%20,%21,%22,%23,%24,%25,%26,%27,%28,%29,%2A,%2B,%2C,%2D,%2E,%2F,%30,%31,%32,%33,%34,%35,%36,%37,%38,%39,%3A,%3B,%3C,%3D,%3E,%3F,%40,%41,%42,%43,%44,%45,%46,%47,%48,%49,%4A,%4B,%4C,%4D,%4E,%4F,%50,%51,%52,%53,%54,%55,%56,%57,%58,%59,%5A,%5B,%5C,%5D,%5E,%5F,%60,%61,%62,%63,%64,%65,%66,%67,%68,%69,%6A,%6B,%6C,%6D,%6E,%6F,%70,%71,%72,%73,%74,%75,%76,%77,%78,%79,%7A,%7B,%7C,%7D,%7E, %A2, %A3, %A5,%A6,%A7,%AB,%AC,%AD,%B0,%B1,%B2,%B4,%B5,%BB,%BC,%BD,%BF,%C0,%C1,%C2,%C3,%C4,%C5,%C6,%C7,%C8,%C9,%CA,%CB,%CC,%CD,%CE,%CF,%D0,%D1,%D2,%D3,%D4,%D5,%D6,%D8,%D9,%DA,%DB,%DC,%DD,%DE,%DF,%E0,%E1,%E2,%E3,%E4,%E5,%E6,%E7,%E8,%E9,%EA,%EB,%EC,%ED,%EE,%EF,%F0,%F1,%F2,%F3,%F4,%F5,%F6,%F7,%F8,%F9,%FA,%FB,%FC,%FD,%FE,%FF");
	$replace = explode(",",
",,,,,!,\",#,$,%,&,',(,),*,+,,,-,.,/,0,1,2,3,4,5,6,7,8,9,:,;,<,=,>,?,@,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,[,\,],^,_,`,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,{,|,},~,¢,£,¥,|,§,«,¬,¯,º,±,ª,,,µ,»,¼,½,¿,À,Á,Â,Ã,Ä,Å,Æ,Ç,È,É,Ê,Ë,Ì,Í,Î,Ï,Ð,Ñ,Ò,Ó,Ô,Õ,Ö,Ø,Ù,Ú,Û,Ü,Ý,Þ,ß,à,á,â,ã,ä,å,æ,ç,è,é,ê,ë,ì,í,î,ï,ð,ñ,ò,ó,ô,õ,ö,÷,ø,ù,ú,û,ü,ý,þ,ÿ");
	$variable = str_replace($search, $replace, $variable);
	$variable = rawurldecode(replaceAccents($variable));
	$variable = replaceAccents(to_utf8($variable));
	//$variable = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $variable);
	//parse_str ($variable, $out);
	//print_r($out);exit;
	//$variable = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",rawurldecode($variable)); 
	//$variable = html_entity_decode($variable,null,'UTF-8');
	return $variable;
}

$etime = microtime ( true ); // 获取程序执行结束的时间
$total = $etime - $stime; // 计算差值
echo "\n" . $total . "times" . "\n";