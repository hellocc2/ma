<?php
namespace Model;
use Helper\Promotion as Promotion;

class Sale {

	/**
	 * 转化率统计
	 * @param array $data
	 */
	public function getSaleSell($time) {
		$db = \Lib\common\Db::get_db("milanoo");
		$acceptLang = \config\Language::$acceptLang;
		if ($time == "month")
			$str = "y-m";
		else
			$str = "y-m-d";

		$sql = "select a.device_type,a.ordersaddtime,ROUND(sum(a.`OrdersAmount` * c.ex_rate / d.`ex_rate` ),2) as OrdersAmount,ROUND(sum(a.OrdersLogisticsCosts* c.ex_rate / d.`ex_rate` ),2) as LogisticsAmount,ROUND(sum(a.insurance* c.ex_rate / d.`ex_rate` ),2) as Insurance from milanoo.`milanoo_orders` a,milanoo.`t_exchange_rate` c,milanoo.`t_exchange_rate` d WHERE a.`CurrencyCode` = c.`currency` AND d.`currency` = 'USD'  AND a.orderspay > 0";
		if ($_SESSION["ma_starttime"]) {
			$starttime = strtotime($_SESSION["ma_starttime"]);
			$sql .= " and a.ordersaddtime>='" . $starttime . "'";
		}
		if ($_SESSION["ma_endtime"]) {
			$endtime = explode("-", $_SESSION["ma_endtime"]);
			$endtime = mktime(0, 0, 0, $endtime[1], $endtime[2] + 1, $endtime[0]);
			$sql .= " and a.ordersaddtime<'" . $endtime . "' ";
		}
		if ($_SESSION["ma_lang"] && $_SESSION["ma_lang"] != "all") {
			$lang = $acceptLang[$_SESSION["ma_lang"]];
			$sql .= " and a.lang='" . $lang . "'";
		}
		//语言站判断开始
		// 		if ($_SESSION ["ma_websiteId"] == 101) {
		// 			//$website = 1;
		// 			//$device_type = 2;
		// 			$sql .= " and a.websiteId='1' and a.device_type>1";
		// 		} elseif (! empty ( $_SESSION ["ma_websiteId"] ) && $_SESSION ["ma_websiteId"] != 666) {
		// 			$sql .= " and a.websiteId='" . $_SESSION ["ma_websiteId"] . "'";
		// 		}
		//语言站判断结束
		$sql .= " group by a.device_type,a.`OrdersId` order by a.`OrdersId` asc";
		//echo $sql;
		$rs = $db -> SelectLimit($sql);
		if ($rs -> RecordCount()) {
			$row = $page_list_array = array();
			while (!$rs -> EOF) {
				$row = $rs -> fields;
				if ($time == "week") {
					$date = $this -> get_week_format($row["ordersaddtime"]);
				} else 
					$date = date($str, $row["ordersaddtime"]);
				$page_list_array[$date][$row["device_type"]]["OrdersAmount"] += $row["OrdersAmount"];
				$page_list_array[$date][$row["device_type"]]["Insurance"] += $row["Insurance"];
				$page_list_array[$date][$row["device_type"]]["LogisticsAmount"] += $row["LogisticsAmount"];
				$page_list_array[$date][$row["device_type"]]["OrderNum"] += 1;
				$rs -> MoveNext();
			}
		}
		return $page_list_array;
	}
 
	public function getCategorySell($time, $pid, $leve, $category) {
		
		if ( $category == 'Direct' ) {
			$category_where = ' AND a.`Promotion` IS NULL';
		} elseif ( $category == 'VRM' ) {
			$category_where = ' AND a.`Promotion` ="'.$category.'"';
		} elseif ( $category == 'SEO' ) {
			$category_where = " AND a.`Promotion` REGEXP '^seo_'";
		} elseif ( is_numeric($category) ) {
			$db_bi = \Lib\common\Db::get_db("default");
			$class_all = new Promotion('promotion_category', 0, 'ASC', '', 0, 1);
		
			if ($category > 0) {
				$category_id = ltrim( $class_all -> idALL($category), ',' );
			    $sql = "SELECT PromotionName FROM `milanoo_promotionurl` WHERE category IN (".$category.",".$category_id.") GROUP BY PromotionName";
				$rs = $db_bi -> SelectLimit($sql);
				if ($rs -> RecordCount()) {
					$row = array();
					while (!$rs -> EOF) { 
						$row = $rs -> fields;
						$category_array[] = "'".trim($row['PromotionName'])."'";
						$rs -> MoveNext();
					}
				}
				$category = implode(',', $category_array);
				$category_where = ' AND a.`Promotion` in ('.$category.')';
			}
		}
 
		$db = \Lib\common\Db::get_db("milanoo");
		$acceptLang = \config\Language::$acceptLang;
		if ($time == "month")
			$str = "y-m";
		else
			$str = "y-m-d";
		$sql = "select a.`Promotion`,pa.`id`,pa.`category_code`,length(pa.`category_code`) as level,pl.`category_name`,a.ordersaddtime,count(a.`OrdersId`) as OrderNum,sum(op.`ProductsPrice`*op.`ProductsNum`* c.ex_rate / d.`ex_rate` ) as ProductsPrice,sum(op.`SuppliersPrice`*op.`ProductsNum` / d.`ex_rate` ) as SuppliersPrice,sum(op.`ProductsNum`) as ProductsNum from milanoo.`milanoo_orders` a,milanoo.`milanoo_orders_products` op,milanoo_gaea.`products` p,milanoo_gaea.`products_categories` ca,milanoo_gaea.`products_categories_lang` l,milanoo_gaea.`products_categories` pa,milanoo_gaea.`products_categories_lang` pl,milanoo.`t_exchange_rate` c,milanoo.`t_exchange_rate` d WHERE a.`CurrencyCode` = c.`currency` AND d.`currency` = 'USD' and a.OrdersId=op.`OrdersId` and op.`ProductsId`=p.`id` and p.`CategoriesId`=ca.`id` and ca.`id`=l.products_categorie_id and l.`language_id`=1 and pl.`language_id`=1 and pa.`id`=pl.products_categorie_id  AND a.orderspay > 0 ";
		if ($_SESSION["ma_starttime"]) {
			$starttime = strtotime($_SESSION["ma_starttime"]);
			$sql .= " and a.ordersaddtime>='" . $starttime . "'";
		}
		if ($_SESSION["ma_endtime"]) {
			$endtime = explode("-", $_SESSION["ma_endtime"]);
			$endtime = mktime(0, 0, 0, $endtime[1], $endtime[2] + 1, $endtime[0]);
			$sql .= " and a.ordersaddtime<'" . $endtime . "' ";
		}
		if ($_SESSION["ma_lang"] && $_SESSION["ma_lang"] != "all") {
			$lang = $acceptLang[$_SESSION["ma_lang"]];
			$sql .= " and a.lang='" . $lang . "'";
		}

		if ($_SESSION["ma_websiteId"] == 101) {
			$sql .= " and a.websiteId='1' and a.device_type>1 and a.device_type<5";
			//$website = 1; //$device_type = 2;
		} elseif ($_SESSION["ma_websiteId"] == 201) {
			$sql .= " and a.websiteId='1' and a.device_type ='5'";
		} elseif (!empty($_SESSION["ma_websiteId"]) && $_SESSION["ma_websiteId"] != 666) {
			$sql .= " and a.websiteId='" . $_SESSION["ma_websiteId"] . "'";
		}
		if ($pid) {
			$length = $leve ? ($leve + 1) * 5 : 5;
			$sql .= " and SUBSTR(ca.`category_code`, 1, " . $length . ") = pa.`category_code`";
			$sql .= " and ca.`category_code` LIKE '" . $pid . "%'";
		} else {
			$sql .= " and SUBSTR(ca.`category_code`, 1, 5) = pa.`category_code`";
		} 
			$sql .= $category_where;
		$sql .= " group by pa.`id`,a.`OrdersId` order by a.`OrdersId` asc";
		//echo $sql;
		$rs = $db -> SelectLimit($sql);
		if ($rs -> RecordCount()) {
			$row = $page_list_array = array();
			while (!$rs -> EOF) {
				$row = $rs -> fields;
				if ($time == "week") {
					$date = $this -> get_week_format($row["ordersaddtime"]);
				} else
					$date = date($str, $row["ordersaddtime"]);
				$page_list_array[$date][$row["id"]]["OrderNum"] += 1;
				$page_list_array[$date][$row["id"]]["ProductsNum"] += $row["ProductsNum"];
				$page_list_array[$date][$row["id"]]["SuppliersPrice"] += $row["SuppliersPrice"];
				$page_list_array[$date][$row["id"]]["ProductsPrice"] += $row["ProductsPrice"];
				$page_list_array[$date][$row["id"]]["category_name"] = $row["category_name"];
				$page_list_array[$date][$row["id"]]["category_code"] = $row["category_code"];
				$page_list_array[$date][$row["id"]]["level"] = $row["level"] / 5;
				$rs -> MoveNext();
			}
		}
		return $page_list_array;

	}

	public function getCategoryURLSell($time, $cid) {
		$db = \Lib\common\Db::get_db("default");
		$acceptLang = \config\Language::$acceptLang;
		$db_milanoo = \Lib\common\Db::get_db("milanoo");
		// $db_milanoo-> debug =1;
        // $db -> debug =1;
		$file = '../config/Java_webservice_url.php';
		if (is_file($file)) {
			include $file;
		}
 
		if ($_SESSION["ma_starttime"]) {
			$starttime = strtotime($_SESSION["ma_starttime"]);
		}
		if ($_SESSION["ma_endtime"]) {
			$endtime = explode("-", $_SESSION["ma_endtime"]);
			$endtime = mktime(0, 0, 0, $endtime[1], $endtime[2] + 1, $endtime[0]);
		}

		if ($time == "month") {
			$str = "y-m";
			for ($i=$starttime; $i < $endtime; $i=$i+1*24*60*60) { 
				$page_list_array[date($str, $i)] = array();
			}
		} elseif ($time == "week") {
			$str = "y-m-d";
			for ($i=$starttime; $i < $endtime; $i=$i+1*24*60*60) { 
				$page_list_array[$this -> get_week_format($i)] = array();
			}
		} else {
			$str = "y-m-d";
			for ($i=$starttime; $i < $endtime; $i=$i+1*24*60*60) { 
				$page_list_array[date($str, $i)] = array();
			}
		}

		if ($_SESSION["ma_lang"] && $_SESSION["ma_lang"] != "all") {
			$lang = $acceptLang[$_SESSION["ma_lang"]];
			$d = " and lang='" . $lang . "'";
		}

		$class_all = new Promotion('promotion_category', 0, 'ASC', '', 0, 1);

		// 主站 SQL 条件
		if ($_SESSION["ma_websiteId"] == 101) {
			$c = " and websiteId='101'";
			//$website = 1; //$device_type = 2;
		} elseif ($_SESSION["ma_websiteId"] == 201) {
			$c = " and websiteId='201'";
		} elseif (!empty($_SESSION["ma_websiteId"]) && $_SESSION["ma_websiteId"] != 666) {
			$c = " and websiteId='" . $_SESSION["ma_websiteId"] . "'";
		}

		if ($_SESSION["ma_lang"] && $_SESSION["ma_lang"] != "all") {
			$c .= " and lang = '" . $_SESSION["ma_lang"] . "'";
		}

		//Direct
		$sql_d = "SELECT a.pv-b.pv AS pv,a.uv-b.uv AS uv,a.ip-b.ip AS ip ,a.`time` FROM (SELECT d.`time`,SUM(d.`pv`) AS pv ,SUM(d.`ip`) AS ip,SUM(d.`uv`) AS uv FROM `day` d WHERE d.`time`>= '" . $_SESSION["ma_starttime"] . "' and d.`time` <= '" . $_SESSION["ma_endtime"] . "'" . $c . " GROUP BY  d.`time`) a, (SELECT pv.`time`,SUM(pv.`pv`) AS pv ,SUM(pv.`ip`) AS ip,SUM(pv.`uv`) AS uv FROM `ma_promotion_visits` pv WHERE pv.`time`>='" . $_SESSION["ma_starttime"] . "' and pv.`time` <= '" . $_SESSION["ma_endtime"] . "'" . $c . " GROUP BY  pv.`time`) b WHERE a.time = b.time GROUP BY a.time ORDER BY a.time";
		$rs_milanoo = $db -> SelectLimit($sql_d);

		if ($rs_milanoo -> RecordCount()) {
			while (!$rs_milanoo -> EOF) {
				$row_milanoo = $rs_milanoo -> fields;
				if ($time == "week") {
					$date = $this -> get_week_format(strtotime($row_milanoo["time"]));
				} else {
					$date = date($str, strtotime($row_milanoo["time"]));
				}
				
				if ($cid  < 1) {
					$page_list_array[$date]["Direct"]["pv"] += $row_milanoo["pv"];
					$page_list_array[$date]["Direct"]["ip"] += $row_milanoo["ip"];
					$page_list_array[$date]["Direct"]["uv"] += $row_milanoo["uv"];
				}
				$rs_milanoo -> MoveNext();
			}
		}
		
				
		$sql_d = "SELECT COUNT(OrdersAmount) AS payorder,'Direct' AS Direct, SUM(ROUND((OrdersAmount + OrdersLogisticsCosts + IF( insurance, insurance, 0 )) * IFNULL(SUBSTRING_INDEX(exchange_rate,',',-1),1) / IFNULL(SUBSTRING_INDEX(exchange_rate,',',1),1),2)) AS OrdersAmount ,FROM_UNIXTIME(OrdersAddTime,'%Y-%m-%d') as `time` FROM `milanoo_orders` s WHERE (Promotion IS NULL OR Promotion = '')  " . $d . " AND `OrdersEstate` != 'RefuseOrders' AND `OrdersAddTime` >='" . $starttime . "' AND `OrdersPay` > '0' AND `OrdersAddTime` <= '" . $endtime . "'";

		// 主站 SQL 条件
		if ($_SESSION["ma_websiteId"] == 101) {
			$sql_d .= " and s.websiteId='1' and s.device_type>1 and s.device_type<5";
			$c = " and websiteId='101'";
			//$website = 1; //$device_type = 2;
		} elseif ($_SESSION["ma_websiteId"] == 201) {
			$sql_d .= " and websiteId='1' and s.device_type ='5'";
			$c = " and websiteId='201'";
		} elseif (!empty($_SESSION["ma_websiteId"]) && $_SESSION["ma_websiteId"] != 666) {
			$sql_d .= " and websiteId='" . $_SESSION["ma_websiteId"] . "'";
			$c = " and websiteId='" . $_SESSION["ma_websiteId"] . "'";
		}

		if ($_SESSION["ma_lang"] && $_SESSION["ma_lang"] != "all") {
			$c .= " and lang = '" . $_SESSION["ma_lang"] . "'";
		}

		$sql_d .= " AND (Special_Order NOT IN ('-1','2','5') OR Special_Order IS NULL) GROUP BY FROM_UNIXTIME(OrdersAddTime,'%Y-%m-%d')";

		$rs_milanoo = $db_milanoo -> SelectLimit($sql_d);

		if ($rs_milanoo -> RecordCount()) {
			while (!$rs_milanoo -> EOF) {
				$row_milanoo = $rs_milanoo -> fields;
				if ($time == "week") {
					$date = $this -> get_week_format(strtotime($row_milanoo["time"]));
				} else {
					$date = date($str, strtotime($row_milanoo["time"]));
				}
				if ($cid  < 1) {
					$page_list_array[$date][$row_milanoo["Direct"]]["payorder"] += $row_milanoo["payorder"];
					$page_list_array[$date][$row_milanoo["Direct"]]["payamount"] += $row_milanoo["OrdersAmount"];
				}
				$rs_milanoo -> MoveNext();
			}
		}

		if ($cid > 0) {
			$pid = ltrim( $class_all -> idALL($cid), ',' );

			if (empty($pid)) {
				$pid = $cid;
			}
			//var_dump($pid);exit;
			$sql = "SELECT `id`, `name` FROM `milanoo_promotion_category` WHERE `pid` = " . $cid . " AND `WebsiteId` = 1";
			unset($pid);
		} else {
			$sql = "SELECT `id`, `name` FROM `milanoo_promotion_category` WHERE `name` IN ('sns','sem','SEO','Affiliate Marketing','email','VRM ') AND `WebsiteId` = 1";
		}
		//echo $sql;exit;
		$rs = $db -> SelectLimit($sql);
		$statistics = array();
		if ($rs -> RecordCount()) {
			while (!$rs -> EOF) {
				$row = $rs -> fields;

				$sql = "SELECT " . $row['id'] . " as `category`, '" . $row['name'] . "' as `category_link_c_name`, SUM(`payorder`) as payorder, SUM(`unpayorder`) as unpayorder, SUM(`payamount`) as payamount, SUM(`pv`) as pv, SUM(`ip`) as ip, SUM(`uv`) as uv, pv.time FROM `milanoo_promotionurl` p, `ma_promotion_visits` pv WHERE p.id = pv.`promotionid`";
				if ($_SESSION["ma_starttime"]) {
					$sql .= " and pv.time >='" . $_SESSION["ma_starttime"] . "'";
				}
				if ($_SESSION["ma_endtime"]) {
					$sql .= " and pv.time <='" . $_SESSION["ma_endtime"] . "' ";
				}
				if ($_SESSION["ma_lang"] && $_SESSION["ma_lang"] != "all") {
					$lang = $acceptLang[$_SESSION["ma_lang"]];
					$sql .= " and pv.lang='" . $_SESSION["ma_lang"] . "'";
				}

				$WebsiteId = $_SESSION["ma_websiteId"];

				if ($WebsiteId == 666) {
					$WebsiteId = 1;
				}

				if ($WebsiteId != 0) {
					$data['websiteId'] = $WebsiteId;
				}

				if ($_SESSION["ma_websiteId"] == 101) {
					$WebsiteId = 101;
					$sql .= " and pv.websiteId='101'";

				} elseif ($_SESSION["ma_websiteId"] == 201) {
					$WebsiteId = 201;
					$sql .= " and pv.websiteId='201'";

				} elseif (!empty($_SESSION["ma_websiteId"]) && $_SESSION["ma_websiteId"] != 666) {
					$WebsiteId = $_SESSION["ma_websiteId"];
					$sql .= " and pv.websiteId='" . $_SESSION["ma_websiteId"] . "'";
				}

				if (!empty($row['id'])) {
					$pid = $class_all -> idALL($row['id']);
					$sql .= " and p.`category` in (" . $row['id'] . $pid . ")";
				}
				
				$sql .= " GROUP BY p.`category_parent_id`, pv.time";
				$rs_c = $db -> SelectLimit($sql);
				
				unset($is_c);

				$is_sql = "SELECT `id`, `name` FROM `milanoo_promotion_category` WHERE `pid` = " . $row['id'] . " AND `WebsiteId` = 1";
				$rs_is = $db -> SelectLimit($is_sql);
				$is_c = $rs_is -> RecordCount();

				if ($rs_c -> RecordCount()) {
					while (!$rs_c -> EOF) {
						$row_c = $rs_c -> fields;
						if ($time == "week") {
							$date = $this -> get_week_format(strtotime($row_c["time"]));
						} else {
							$date = date($str, strtotime($row_c["time"]));
						}

						if ($row_c["category_link_c_name"] == 'email') {
							$row_c["category_link_c_name"] = 'EDM';
						}

						if ($is_c) {
							$page_list_array[$date][$row_c["category_link_c_name"]]["category_code"] = $row_c["category"];
						}
						$page_list_array[$date][$row_c["category_link_c_name"]]["payorder"] += $row_c["payorder"];
						$page_list_array[$date][$row_c["category_link_c_name"]]["unpayorder"] += $row_c["unpayorder"];
						$page_list_array[$date][$row_c["category_link_c_name"]]["payamount"] += $row_c["payamount"];
						$page_list_array[$date][$row_c["category_link_c_name"]]["pv"] += $row_c["pv"];
						$page_list_array[$date][$row_c["category_link_c_name"]]["ip"] += $row_c["ip"];
						$page_list_array[$date][$row_c["category_link_c_name"]]["uv"] += $row_c["uv"];

						$rs_c -> MoveNext();
					}
				} else {
					$page_list_array[$date][$row['name']]["payorder"] = 0;
					$page_list_array[$date][$row['name']]["unpayorder"] = 0;
					$page_list_array[$date][$row['name']]["payamount"] = 0;
					$page_list_array[$date][$row['name']]["pv"] = 0;
					$page_list_array[$date][$row['name']]["ip"] = 0;
					$page_list_array[$date][$row['name']]["uv"] = 0;
				}
				$rs -> MoveNext();
			}
		}


		return $page_list_array;
	}

	public function getPaymentSell($time, $cid) {
		$db = \Lib\common\Db::get_db("default");
		$acceptLang = \config\Language::$acceptLang;
		$db_milanoo = \Lib\common\Db::get_db("milanoo");
		// $db_milanoo-> debug =1;
        // $db -> debug =1;
		$file = '../config/Java_webservice_url.php';
		if (is_file($file)) {
			include $file;
		}

		if ($time == "month") {
			$str = "y-m";
		} else {
			$str = "y-m-d";
		}

		if ($_SESSION["ma_starttime"]) {
			$starttime = strtotime($_SESSION["ma_starttime"]);
		}
		if ($_SESSION["ma_endtime"]) {
			$endtime = explode("-", $_SESSION["ma_endtime"]);
			$endtime = mktime(0, 0, 0, $endtime[1], $endtime[2] + 1, $endtime[0]);
		}

		if ($_SESSION["ma_lang"] && $_SESSION["ma_lang"] != "all") {
			$lang = $acceptLang[$_SESSION["ma_lang"]];
			$d = " and lang='" . $lang . "'";
		}
		
		$paytype = array('paypal' => 'paypal', 'xlmk' => '西联汇款', 'xyk' => '信用卡', 'yhhk' => '银行汇款', 'yhzx' => '银行在线','unpaypal' => '未支付paypal', 'unxlmk' => '未支付西联汇款', 'unxyk' => '未支付信用卡', 'unyhhk' => '未支付银行汇款', 'unyhzx' => '未支付银行在线', );
		
		//Direct
		$sql_d = "SELECT orderspay,SUM(ROUND((OrdersAmount + OrdersLogisticsCosts + IF( insurance, insurance, 0 )) * IFNULL(SUBSTRING_INDEX(exchange_rate,',',-1),1) / IFNULL(SUBSTRING_INDEX(exchange_rate,',',1),1),2)) AS OrdersAmount ,COUNT(OrdersId) as nu,OrdersPayclass,FROM_UNIXTIME(OrdersAddTime,'%Y-%m-%d') AS 'time' FROM milanoo_orders WHERE (orderspay = 0 or orderspay > 0) " . $d . " AND `OrdersEstate` != 'RefuseOrders' AND `OrdersAddTime` >='" . $starttime . "' AND `OrdersAddTime` < '" . $endtime . "'";

		// 主站 SQL 条件
		if ($_SESSION["ma_websiteId"] == 101) {
			$sql_d .= " and websiteId='1' and device_type>1 and device_type<5";
			$c = " and websiteId='101'";
			//$website = 1; //$device_type = 2;
		} elseif ($_SESSION["ma_websiteId"] == 201) {
			$sql_d .= " and websiteId='1' and device_type ='5'";
			$c = " and websiteId='201'";
		} elseif (!empty($_SESSION["ma_websiteId"]) && $_SESSION["ma_websiteId"] != 666) {
			$sql_d .= " and websiteId='" . $_SESSION["ma_websiteId"] . "'";
			$c = " and websiteId='" . $_SESSION["ma_websiteId"] . "'";
		}

		if ($_SESSION["ma_lang"] && $_SESSION["ma_lang"] != "all") {
			$c .= " and lang = '" . $_SESSION["ma_lang"] . "'";
		}

		$sql_d .= " GROUP BY OrdersPayclass,FROM_UNIXTIME(OrdersAddTime,'%Y-%m-%d'),orderspay ORDER BY `time`" ;
		// echo $sql_d;
		$rs_milanoo = $db_milanoo -> SelectLimit($sql_d);

		if ($rs_milanoo -> RecordCount()) {
			while (!$rs_milanoo -> EOF) {
				$row_milanoo = $rs_milanoo -> fields;
				if ($time == "week") {
					$date = $this -> get_week_format(strtotime($row_milanoo["time"]));
				} else {
					$date = date($str, strtotime($row_milanoo["time"]));
				}
				
				if ( $row_milanoo["orderspay"] == 0 ) {
					if ($paytype[$row_milanoo["OrdersPayclass"]] == '') {
						$payname = '未支付没选支付方式';
					} else {
						$payname = $paytype['un'.$row_milanoo["OrdersPayclass"]];
					}
					
					$page_list_array[$date][$payname]["payorder"] += $row_milanoo["nu"];
					$page_list_array[$date][$payname]["payamount"] += $row_milanoo["OrdersAmount"];
					$page_list_array[$date][$payname]["OrdersPayclass"] += $row_milanoo["OrdersPayclass"];
					
					$page_list_array[$date]['没有支付的订单']["payorder"] += $row_milanoo["nu"];
					$page_list_array[$date]['没有支付的订单']["payamount"] += $row_milanoo["OrdersAmount"];
					$page_list_array[$date]['没有支付的订单']["OrdersPayclass"] += $row_milanoo["OrdersPayclass"];
					
				} else {
					if ($paytype[$row_milanoo["OrdersPayclass"]] == '') {
						$payname = '没选支付方式';
					} else {
						$payname = $paytype[$row_milanoo["OrdersPayclass"]];
					}					
					$page_list_array[$date][$payname]["payorder"] += $row_milanoo["nu"];
					$page_list_array[$date][$payname]["payamount"] += $row_milanoo["OrdersAmount"];
					$page_list_array[$date][$payname]["OrdersPayclass"] += $row_milanoo["OrdersPayclass"];
				}

				$rs_milanoo -> MoveNext();
			}
		}

		return $page_list_array;
	}

	public function get_week_format($time, $start = 6, $end = 5) {
		$week = date("N", $time);
		$last = $week - $start;
		if ($last >= 0) {
			$data = 6 - abs($week - $start);
		} else {
			$last = 6 - abs($end - $week);
			$data = abs($end - $week);
		}
		$result = date("y-m-d", mktime(0, 0, 0, date("m", $time), date("d", $time) - $last, date("Y", $time))) . " - " . date("y-m-d", mktime(0, 0, 0, date("m", $time), date("d", $time) + $data, date("Y", $time)));
		return $result;
	}

	public function getSeachCategoriesName(&$data, $item) {
		$data['cuurentId'] = $item['categoryId'];
		$data['name'][] = $item['categoryName'];
		if (isset($item['nextCategory'])) {
			$data['pid'] = $item['categoryId'];
			getSeachCategoriesName($data, $item['nextCategory']);
		}
	}

	public function getServiceData($module, $action, $param, $method = 'GET', $namespace = '') {
		if (empty($namespace)) {
			$url = JAVA_WEBSERVICE_URL . '/products/';
		} else {
			$url = JAVA_WEBSERVICE_URL;
		}
		$url = rtrim($url, '?\/');
		if (is_string($module) && is_string($action)) {
			if (!empty($namespace)) {
				$url .= '/' . $namespace;
			}
			$url .= '/' . $module . '/' . $action . '.htm';
		}

		if ($method == 'GET') {
			$appendedValues = array();
			if (is_array($param)) {
				foreach ($param as $k => $v) {
					$appendedValues[] = $k . '=' . urlencode($v);
				}
			}
			$appendedStr = implode('&', $appendedValues);
			if (!empty($appendedValues)) {
				$url .= '?' . $appendedStr;
			}
		}

		$ch = curl_init();
		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		}
		// print_r($url);
		// echo '<br />';
		curl_setopt_array($ch, array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 5));
		$response = curl_exec($ch);
		if ($errNo = curl_errno($ch)) {
			$handle = fopen(ROOT_PATH . 'data/curl.txt', 'a');
			$info = @curl_getinfo($ch);
			fwrite($handle, 'error--');
			fwrite($handle, $url . "\n");
			fwrite($handle, var_export($info, true) . "\n\r");
			fwrite($handle, "end--\n\r");
			fclose($handle);

			curl_close($ch);
			return false;
		}
		curl_close($ch);
		$str = gzuncompress($response);

		$responseArr = json_decode($str, true);

		if ($responseArr['code'] == '0') {
			return $responseArr;
		} else {
			$handle = fopen('../errors/curl.txt', 'a');
			$info = @curl_getinfo($ch);
			fwrite($handle, 'request--');
			fwrite($handle, $url . "\n");
			fwrite($handle, var_export($responseArr, true) . "\n\r");
			fwrite($handle, "end--\n\r");
			fclose($handle);
			return false;
		}
	}

}
