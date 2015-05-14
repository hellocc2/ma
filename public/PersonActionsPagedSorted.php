<?php

namespace config;

$file = '../config/Db.php';
if (is_file($file)) {
	include $file;
	$db_config = Db::$default;
}

$file = '../config/Java_webservice_url.php';
if (is_file($file)) {
	include $file;
}
namespace Helper;

$file_promotion = '../helper/Promotion.php';
if (is_file($file_promotion)) {
	include $file_promotion;
}

try {
	// Open database connection
	$con = mysql_connect($db_config['host'] . ":" . $db_config['port'], $db_config['dbuser'], $db_config['dbpassword']);
	mysql_set_charset("utf8", $con);
	mysql_query('SET NAMES utf8');
	mysql_select_db("web_statis", $con);

	if (isset($_GET["menu_action"]) and $_GET["menu_action"] == 'categories_zh-cn') {
		unset($file);

		if (isset($_GET["cid"])) {
			$cid = $_GET["cid"];
		}

		$cateName = isset($_GET['seach']) ? $_GET['seach'] : '';
		// $cateName = isset($_GET['seach']) ? $_GET['seach'] : '';
		if (isset($_GET['cids'])) {
			$a = explode(',', $_GET['cids']);
			if (count($a) > 1) {
				$cids = substr($_GET['cids'], 1, -1);
			} else {
				$cids = $_GET['cids'];
			}
		}
		$WebsiteId = $_GET["WebsiteId"];
		//$WebsiteId = 1;
		if (!empty($cateName)) {
			$data = array('keyword' => $cateName, 'langCode' => 'zh-cn', 'categoryId' => 0);
			if ($WebsiteId != 0) {
				$data['websiteId'] = $WebsiteId;
			}
			$categoriesData = getServiceData('productCategory', 'searchProductsCategory', $data, 'GET', 'products');

			if ($categoriesData['code'] == 0 && count($categoriesData['categorys']) > 0) {
				$reusltData = array();
				foreach ($categoriesData ['categorys'] as $v) {
					$data = array('pid' => 0, 'name' => array(), 'cuurentId' => 0);
					getSeachCategoriesName($data, $v);
					$reusltData[] = $data;
				}
			}
			foreach ($reusltData as $v) {
				$currentId = $v['cuurentId'];
				$pid = $v['pid'];
				$str = '';
				foreach ($v ['name'] as $n) {
					$str .= ' / ' . $n;
				}
				$arr[] = "['" . $currentId . "','" . $str . "','" . $pid . "']";
			}
			// print '<pre>';print_r($arr);exit;
		} elseif (isset($_GET['cids'])) {
			$cids_array = explode(',', $cids);
			foreach ($cids_array as $c) {
				$data = array('categoryId' => $c, 'langCode' => 'zh-cn');
				if ($WebsiteId != 0) {
					$data['websiteId'] = $WebsiteId;
				}

				$categoriesData = getServiceData('productCategory', 'productsCategoryNav', $data, 'GET', 'products');
				if ($categoriesData['code'] == 0) {
					$data = array('pid' => 0, 'name' => array(), 'cuurentId' => 0);
					getSeachCategoriesName($data, $categoriesData);
				}
				$currentId = $data['cuurentId'];
				$pid = $data['pid'];
				$str = '';
				foreach ($data ['name'] as $n) {
					$str .= ' / ' . $n;
				}
				$arr[] = "['" . $currentId . "','" . $str . "','" . $pid . "']";
			}
			// print '<pre>';print_r($data);exit;
		} else {
			$data = array('parentCategoryId' => $cid, 'languageCode' => 'en-uk', 'returnLevel' => 1, 'returnChildNum' => '-1:-1:-1:-1');
			if ($WebsiteId != 0) {
				$data['websiteId'] = $WebsiteId;
			}
			$categoriesData = getServiceData('products', 'getProductsCategory', $data, 'GET', 'products');

			if (isset($categoriesData['resultList']) && count($categoriesData['resultList']) > 0) {
				$arr = array();
				$parentId = $cid;
				foreach ($categoriesData ['resultList'] as $v) {
					$childrenSize = isset($v['childrenSize']) ? $v['childrenSize'] : 0;
					$arr[] = "['" . $v['categoryId'] . "','" . $v['categoryName_zh'] . "','" . $parentId . "','" . $childrenSize . "']";
				}
			}
		}

		$ajax_lr = '';
		if (count($arr) > 0) {
			$ajax_lr = implode(",", $arr);
		}
		/*
		 * include_once LIB_PATH . 'comm/get.class.' . PHP_EX; $class_all = new get_class_tree ( 'products_categories', '0', 'ASC', 'zh-cn' ); $class_all->Categories_name = trim ( $this->get_parameter ( 'seach' ) ); $cids = array_unnull ( explode ( ',', $this->get_parameter ( 'cids' ) ) ); //$ajax_lr = $class_all->class_Commodity3 ( ( int ) $this->get_parameter ( 'cid' ), $cids );
		 */
		echo "var result=[$ajax_lr];";
		exit();
	}

	$where = " and 1=1";
	$where_roi = " and 1=1";
	$left_where = " and 1=1";

	$websiteId = $_GET["websiteId"];

	if ($websiteId == 666) {
		$websiteId = 'v.`WebsiteId`';
	}

	if (isset($_GET["lang"])) {
		$lang = $_GET["lang"];
	}
	
	if (!empty($_POST["ROI"]) and $_POST["ROI"] == 1) {
		
		if (!empty($_GET["s_range"])) {
			$s_range = date("Y-m-d 00:00:00", $_GET["s_range"]);
			$where_roi .= " and `time` >='" . $s_range . "'";
		}
	
		if (!empty($_GET["e_range"])) {
			$e_range = date("Y-m-d 00:00:00", $_GET["e_range"]);
			$where_roi .= " and `time` <='" . $e_range . "'";
		}
		
		$sql_roi = "SELECT * FROM (SELECT p.`PromotionName`, IFNULL((SUM(`payamount`)-SUM(`adcostUsd`))/SUM(`adcostUsd`),0) AS ROI, IFNULL(SUM(`payamount`), 0) AS payamount, ROUND(SUM(`adcostUsd`),2) AS adcostUsd, SUM(`payorder`) AS payorder FROM `milanoo_promotionurl` p, `ma_promotion_visits` v WHERE v.`WebsiteId` = v.`WebsiteId` AND p.id = v.`promotionid` AND v.`WebsiteId` = " . $websiteId . " AND 1 = 1 " . $where_roi . " GROUP BY PromotionName ) a WHERE ROI <-0.3 ORDER BY ROI ASC,payamount ASC,adcostUsd DESC";
		$rs_roi = mysql_query($sql_roi);
		
		$row_roi = array();
		while ($row_roi = mysql_fetch_assoc($rs_roi)) {
			$low_roi[] = '"' . $row_roi['PromotionName'] . '"';
		}
		
		$nu_roi = count($low_roi);
		$low_roi_srt = implode(',', $low_roi);
		
		$where .= " and p.`PromotionName` in (" . $low_roi_srt . ")";
	} else {
		if (!empty($_POST["PromotionName"])) {
			$vowels = array("_");
			$PromotionName = trim(str_replace($vowels, "\_", $_POST["PromotionName"]));
			if ($_POST["search_type"] == 'regex') {
				$sql = "SELECT DISTINCT category from milanoo_promotionurl where PromotionName REGEXP '" . $PromotionName . "'";
			} else {
				$sql = "SELECT DISTINCT category from milanoo_promotionurl where PromotionName like '%" . $PromotionName . "%'";
			}

			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
			$category = $row['category'];

			if ($category == 0) {
				if ($_POST["search_type"] == 'regex') {
					$where .= " and p.`PromotionName` REGEXP '" . $PromotionName . "'";
				} else {
					$where .= " and p.`PromotionName` like '%" . $PromotionName . "%'";
				}
			} else {

				if ($_POST["search_type"] == 'regex') {
					$where .= " and p.`PromotionName` REGEXP '" . $PromotionName . "'";
				} else {
					$where .= " and p.`PromotionName` like '%" . $PromotionName . "%'";
				}
				//$where .= " and p.`PromotionName` like '%".$PromotionName."%' and pa.`Id` ='" . $category . "'";
				//$where .= " and p.`PromotionName` like '%" . $PromotionName . "%'";
			}
		}
	}

	if (!empty($_POST["categories_id"])) {
		$categories_id = trim($_POST["categories_id"], ',');
		//$_POST ["addition"]
		if (isset($_POST["addition"]) and $_POST["addition"] == 1) {
			$data = array('parentCategoryId' => $categories_id, //$categories_id
			'languageCode' => 'en-uk', 'returnLevel' => 1, 'returnChildNum' => '-1:-1:-1:-1');
			$categoriesData = getServiceData('products', 'getProductsCategory', $data, 'GET', 'products');
			//var_dump($categoriesData);exit;
			$categories_Child = implode(",", array_multiToSingle($categoriesData));
		}

		if (empty($categories_Child)) {
			$where .= " and p.`category_id` in (" . trim($categories_id, ",") . ")";
		} else {
			$where .= " and p.`category_id` in (" . $categories_Child . ")";
		}

	}

	if (!empty($_GET["s_range"])) {
		$s_range = date("Y-m-d 00:00:00", $_GET["s_range"]);
		$where .= " and `time` >='" . $s_range . "'";
		$where_roi .= " and `time` >='" . $s_range . "'";
	}

	if (!empty($_GET["e_range"])) {
		$e_range = date("Y-m-d 00:00:00", $_GET["e_range"]);
		$where .= " and `time` <='" . $e_range . "'";
		$where_roi .= " and `time` <='" . $e_range . "'";
	}

	if (!empty($_POST["name"]) and $_POST["name"] != 0) {
		$name = $_POST["name"];
		//$where .= " and pa.`id` ='" . $name . "'";
		@$class_all = new Promotion('promotion_category', 0, 'ASC', '', 0, 1);
		@$pid = $class_all -> idALL($name);
		$where .= " and pa.`id` in (" . $name . $pid . ")";
	}

	if (!empty($_GET["category"]) and $_GET["category"] != 0) {
		$category_chart = $_GET["category"];
		//$where .= " and p.`category` ='" . $category_chart . "'";
		@$class_all = new Promotion('promotion_category', 0, 'ASC', '', 0, 1);
		@$pid = $class_all -> idALL($category_chart);
		$where .= " and pa.`id` in (" . $category_chart . $pid . ")";
	}

	// Getting records (listAction)
	if ($_GET["action"] == "list") {

		if (isset($PromotionName)) {

		}

		if (!empty($lang)) {
			$where .= " and v.`lang` ='" . $lang . "'";
			$where_roi .= " and v.`lang` ='" . $lang . "'";
			$left_where .= " and `lang` =v.`lang`";
		}

		$sql = "SELECT COUNT(DISTINCT promotionid) AS RecordCount FROM `milanoo_promotionurl` p LEFT JOIN `milanoo_promotion_category` pa ON pa.`Id` = `category` AND pa.`WebsiteId` = '1', `ma_promotion_visits` v WHERE v.`WebsiteId` = " . $websiteId . " AND p.id = v.`promotionid`" . $where;
		// Get record count
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];

		$sql = "SELECT IFNULL(SUM(`subscribers`),0) as sum_subscribers,IFNULL(SUM(`regmember`),0) as sum_regmember,ROUND(IFNULL(SUM(`adcostUsd`),0),2) as sum_adcostUsd,IFNULL(SUM(`adClicks`),0) as sum_adClicks,IFNULL(SUM(`impressions`),0) as sum_impressions,IFNULL(SUM(`unpayorder`),0) as sum_unpayorder,IFNULL(SUM(`payorder`),0) as sum_payorder,IFNULL(SUM(`pv`),0) as sum_pv,IFNULL(SUM(`ip`),0) as sum_ip,IFNULL(SUM(`uv`),0) as sum_uv,IFNULL(SUM(`newUv`),0) as sum_newUv,IFNULL(SUM(`payamount`),0) as sum_payamount FROM `milanoo_promotionurl` p LEFT JOIN `milanoo_promotion_category` pa ON pa.`Id` = `category` AND pa.`WebsiteId` = 1,`ma_promotion_visits` v WHERE v.`WebsiteId` = " . $websiteId . " AND p.id = v.`promotionid` " . $where . "";
		$result = mysql_query($sql);

		$row = mysql_fetch_array($result);
		$payorderTotal = $row['sum_payorder'];
		$unpayorderTotal = $row['sum_unpayorder'];
		$pvTotal = $row['sum_pv'];
		$ipTotal = $row['sum_ip'];
		$uvTotal = $row['sum_uv'];
		$newUvTotal = $row['sum_newUv'];
		$payamountTotal = $row['sum_payamount'];
		$adcostUsdTotal = $row['sum_adcostUsd'];
		$adClicksTotal = $row['sum_adClicks'];
		$impressionsTotal = $row['sum_impressions'];
		$regmemberTotal = $row['sum_regmember'];
		$subscribersTotal = $row['sum_subscribers'];
		$orderTotal = $payorderTotal + $unpayorderTotal;

		if (isset($_GET['is_export_csv']) and $_GET['is_export_csv'] == 1) {

			if ($websiteId == "v.`WebsiteId`") {
				$dim_websiteId = 1;
			} else {
				$dim_websiteId = $websiteId;
			}

			$site_array = array('1' => 'Milanoo', '7' => 'milanoo.fr', '101' => 'Wap', '201' => 'iPad', '2' => 'Dressinwedding', '3' => 'Lolitashow', '4' => 'Cosplay', '5' => 'Costumeslive', 'v.`WebsiteId`' => @iconv('utf-8', 'gb2312', '全站'));
			// Get records from database
			$sql = "SELECT p.`category_parent_id`, p.category_id, ROUND(IFNULL(SUM(`payorder`), 0) /IFNULL(SUM(v.`uv`), 0),4) AS CR,ROUND( (IFNULL(SUM(`payorder`), 0) + IFNULL(SUM(`unpayorder`), 0)) / IFNULL(SUM(v.`uv`), 0), 4 ) AS SR, ROUND(SUM(`payorder`)/(IFNULL(SUM(`payorder`), 0) + IFNULL(SUM(`unpayorder`), 0)),4) AS PR,SUM(`payamount`)/SUM(`payorder`) AS AAO,SUM(`adcostUsd`)/SUM(`payorder`) AS ACC,ROUND(SUM(`regmember`)/IFNULL(SUM(v.`uv`), 0), 4) AS RR,pa.`WebsiteId`,`time`,IFNULL(SUM(`unpayorder`),0)+IFNULL(SUM(`payorder`),0) as orders," . $orderTotal . " as orderTotal," . $subscribersTotal . " as subscribersTotal," . $regmemberTotal . " as regmemberTotal,ROUND(SUM(`adClicks`)/SUM(`impressions`),4) as CTR,ROUND(SUM(`adcostUsd`)/SUM(`adClicks`),4) as CP," . $adcostUsdTotal . " as adcostUsdTotal," . $impressionsTotal . " as impressionsTotal," . $adClicksTotal . " as adClicksTotal," . $payamountTotal . " as payamountTotal,IFNULL((SUM(`payamount`)-SUM(`adcostUsd`))/SUM(`adcostUsd`),0) as ROI,SUM(`impressions`) as impressions,SUM(`adClicks`) as adClicks,round(SUM(`adcostUsd`),2) as adcostUsd, " . $pvTotal . " as pvTotal ," . $ipTotal . " as ipTotal ," . $uvTotal . " as uvTotal ," . $newUvTotal . " as newUvTotal ," . $payamountTotal . " as payamountTotal," . $unpayorderTotal . " as unPayorderTotal," . $payorderTotal . " as PayorderTotal ,IFNULL(round(SUM(`payorder`)/SUM(v.`uv`)*100,4),0) as purate, pa.`name`,p.`id`,p.`PromotionName`,p.`category`,IFNULL(SUM(v.`pv`),0) as pv,IFNULL(SUM(v.`uv`),0) as uv,IFNULL(SUM(v.`newUv`),0) as newUv,IFNULL(SUM(`payorder`),0) as payorder,IFNULL(SUM(`unpayorder`),0) as unpayorder,IFNULL(SUM(`payamount`), 0) AS payamount,IFNULL(SUM(`regmember`),0) as regmember,IFNULL(SUM(`subscribers`),0) as subscribers FROM `milanoo_promotionurl` p LEFT JOIN `milanoo_promotion_category` pa ON pa.`Id` = `category` AND pa.`WebsiteId` = 1,`ma_promotion_visits` v WHERE v.`WebsiteId` = " . $websiteId . " AND p.id = v.`promotionid` " . $where . " GROUP BY v.`promotionid` ORDER BY time DESC;";
			$result = mysql_query($sql);

			// Add all records to an array
			$str = "时间,域名,语言站,外链标记,外链分类,一级目录,二级目录,三级目录,四级目录,PV,UV,点击次数,展示次数,点击率	（请手修改单元格为百分数）,广告费,订阅会员,注册会员,转化订单,付款订单,转化销售额,注册率（请手修改单元格为百分数）,下单率（请手修改单元格为百分数）,支付率（请手修改单元格为百分数）,平均订单额,平均转化成本,转化率,ROI（请手修改单元格为百分数）\n";
			$str = iconv('utf-8', 'gb2312', $str);
			$rows = array();
			if (empty($lang)) {
				$lang = @iconv('utf-8', 'gb2312', '全站');
			}
			while ($row = mysql_fetch_array($result)) {
				//$s_range $e_range
				if (!empty($row['category_id'])) {
					$dim_sql = 'SELECT `category_name_1`,`category_name_2`,`category_name_3`,`category_name_4` FROM `product_category_dim` WHERE `category_id` = ' . $row['category_id'] . ' AND `websiteid` = ' . $dim_websiteId;
					$dim_result = mysql_query($dim_sql);
					$dim_row = mysql_fetch_row($dim_result);
					$r06 = @iconv('utf-8', 'gb2312', $dim_row[0]);
					$r07 = @iconv('utf-8', 'gb2312', $dim_row[1]);
					$r08 = @iconv('utf-8', 'gb2312', $dim_row[2]);
					$r09 = @iconv('utf-8', 'gb2312', $dim_row[3]);
				} else {
					$r06 = @iconv('utf-8', 'gb2312', '无记录');
					$r07 = @iconv('utf-8', 'gb2312', '无记录');
					$r08 = @iconv('utf-8', 'gb2312', '无记录');
					$r09 = @iconv('utf-8', 'gb2312', '无记录');
				}
				$r00 = explode(" ", $s_range);
				$r01 = explode(" ", $e_range);
				$r02 = @iconv('utf-8', 'gb2312', $row['PromotionName']);
				//文转码
				$r05 = @iconv('utf-8', 'gb2312', str_replace(',', ' ', $row['name']));
				//中文转码

				// @$r02 = iconv('utf-8','gb2312',$row['sellerOrdersCid']);
				// @$r03 = iconv('utf-8','gb2312',$row['seller_name']);
				// @$r04 = iconv('utf-8','gb2312',$row['seller_platform']);
				// @$r05 = iconv('utf-8','gb2312',$row['OrdersEmsWay']);
				// @$r06 = iconv('utf-8','gb2312',$row['OrdersEms']);
				$str .= $r00[0] . '<->' . $r01[0] . "," . $site_array[$websiteId] . "," . $lang . "," . $r02 . "," . $r05 . "," . $r06 . "," . $r07 . "," . $r08 . "," . $r09 . "," . $row['pv'] . "," . $row['uv'] . "," . $row['adClicks'] . "," . $row['impressions'] . "," . $row['CTR'] . "," . $row['adcostUsd'] . "," . $row['subscribers'] . "," . $row['regmember'] . "," . $row['orders'] . "," . $row['payorder'] . "," . $row['payamount'] . "," . @$row['RR'] . "," . $row['SR'] . "," . $row['PR'] . "," . $row['AAO'] . "," . $row['ACC'] . "," . $row['CR'] . "," . $row['ROI'] . "\n";
				//用引文逗号分开
				unset($dim_row);
			}

			$filename = date('Ymd') . '.csv';
			//设置文件名
			export_csv($filename, $str);
			//导出
			exit ;
		} else {
			// Get records from database
			$sql = "SELECT IFNULL(SUM(`unpayorder`),0)+IFNULL(SUM(`payorder`),0) as orders," . $orderTotal . " as orderTotal," . $subscribersTotal . " as subscribersTotal," . $regmemberTotal . " as regmemberTotal,ROUND(SUM(`adcostUsd`)/SUM(`adClicks`),2) as CP," . $adcostUsdTotal . " as adcostUsdTotal," . $impressionsTotal . " as impressionsTotal," . $adClicksTotal . " as adClicksTotal," . $payamountTotal . " as payamountTotal,IFNULL(round((SUM(`payamount`)-SUM(`adcostUsd`))/SUM(`adcostUsd`)*100,2),0) as ROI,SUM(`impressions`) as impressions,SUM(`adClicks`) as adClicks,round(SUM(`adcostUsd`),2) as adcostUsd, " . $pvTotal . " as pvTotal ," . $ipTotal . " as ipTotal ," . $uvTotal . " as uvTotal ," . $newUvTotal . " as newUvTotal ," . $payamountTotal . " as payamountTotal," . $unpayorderTotal . " as unPayorderTotal," . $payorderTotal . " as PayorderTotal ,IFNULL(round(SUM(`payorder`)/SUM(v.`uv`)*100,2),0) as purate, pa.`name`,p.`id`,p.`PromotionName`,p.`category`,IFNULL(SUM(v.`pv`),0) as pv,IFNULL(SUM(v.`uv`),0) as uv,IFNULL(SUM(v.`newUv`),0) as newUv,IFNULL(SUM(`payorder`),0) as payorder,IFNULL(SUM(`unpayorder`),0) as unpayorder,IFNULL(SUM(`payamount`), 0) AS payamount,IFNULL(SUM(`regmember`),0) as regmember,IFNULL(SUM(`subscribers`),0) as subscribers FROM `milanoo_promotionurl` p LEFT JOIN `milanoo_promotion_category` pa ON pa.`Id` = `category` AND pa.`WebsiteId` = 1,`ma_promotion_visits` v WHERE v.`WebsiteId` = " . $websiteId . " AND p.id = v.`promotionid` " . $where . " GROUP BY v.`promotionid` ORDER BY  " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";";
			$result = mysql_query($sql);
			
			// Add all records to an array
			$rows = array();
			while ($row = mysql_fetch_array($result)) {
				$rows[] = $row;
			}
		}
		// var_dump($sql);exit;
		// Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		//$jTableResult ['PayorderTotal'] = $payorderTotal;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}// Updating a record (updateAction)
	else if ($_GET["action"] == "keyword") {
		if (!empty($_POST["KeywordName"])) {
			$KeywordName=$_POST["KeywordName"];
			if (!empty($_POST["search_type"])&&$_POST["search_type"] == 'regex') {
				$where .= " and k.`name` REGEXP '" . $KeywordName . "'";
			} else {
				$where .= " and k.`name` like '%" . $KeywordName . "%'";
			}			
		}
		if (!empty($lang)) {
			$where .= " and v.`lang` ='" . $lang . "'";
		}
		
		$sql = "SELECT COUNT(DISTINCT k.`name`) AS RecordCount,SUM(v.`pv`) AS pvTotal, SUM(v.`ip`) AS ipTotal, SUM(v.`uv`) AS uvTotal, SUM(v.`newUv`) AS newUvTotal FROM `ma_keywords_visits` v LEFT JOIN `ma_keywords_name` k ON v.`kid`=k.`id` WHERE v.`WebsiteId` = " . $websiteId . $where;
		//echo $sql;exit;
		$result = mysql_query($sql);
		
		$row = mysql_fetch_array($result);
		$recordCount= !empty($row['RecordCount'])?$row['RecordCount']:0;
		$pvTotal= !empty($row['pvTotal'])?$row['pvTotal']:0;
		$ipTotal= !empty($row['ipTotal'])?$row['ipTotal']:0;
		$uvTotal= !empty($row['uvTotal'])?$row['uvTotal']:0;
		$newUvTotal= !empty($row['newUvTotal'])?$row['newUvTotal']:0;
		
		if (isset($_GET['is_export_csv']) and $_GET['is_export_csv'] == 1) {
			$site_array = array('1' => 'Milanoo', '7' => 'milanoo.fr', '101' => 'Wap', '201' => 'iPad', '2' => 'Dressinwedding', '3' => 'Lolitashow', '4' => 'Cosplay', '5' => 'Costumeslive', 'v.`WebsiteId`' => @iconv('utf-8', 'gb2312', '全站'));
			// Get records from database
			$sql = "SELECT k.`name`,v.`time`,SUM(v.`pv`) AS pv, SUM(v.`ip`) AS ip, SUM(v.`uv`) AS uv, SUM(v.`newUv`) AS newUv FROM `ma_keywords_visits` v LEFT JOIN `ma_keywords_name` k ON v.`kid`=k.`id` WHERE v.`WebsiteId` = " . $websiteId . $where . " GROUP BY  k.`name` ORDER BY  v.`pv` DESC;";
			//echo $sql;exit;
			$result = mysql_query($sql);

			// Add all records to an array
			$str = "时间,域名,语言站,关键词,PV,UV,IP\n";
			$str = iconv('utf-8', 'gb2312', $str);
			$rows = array();
			if (empty($lang)) {
				$lang = @iconv('utf-8', 'gb2312', '全站');
			}
			while ($row = mysql_fetch_array($result)) {
				//$s_range $e_range
				$r00 = explode(" ", $s_range);
				$r01 = explode(" ", $e_range);
				//$r02 = @iconv('utf-8', 'gb2312', $row['name']);
				//文转码
				$r05 = @iconv('utf-8', 'gb2312', str_replace(',', ' ', $row['name']));
				//中文转码

				$str .= $r00[0] . '<->' . $r01[0] . "," . $site_array[$websiteId] . "," . $lang . "," . $r05 . "," . $row['pv'] . "," . $row['uv'] . "," . $row['ip'] . "\n";
				//用引文逗号分开
				unset($dim_row);
			}

			$filename = 'keyword'.date('Ymd') . '.csv';
			//设置文件名
			export_csv($filename, $str);
			//导出
			exit ;
		} else {
			// Get records from database
			
			$sql = "SELECT k.`name`,v.`time`,".$recordCount." AS keywordTotal,".$ipTotal." AS ipTotal,".$uvTotal." AS uvTotal,".$newUvTotal." AS newUvTotal,".$pvTotal." AS pvTotal,SUM(v.`pv`) AS pv, SUM(v.`ip`) AS ip, SUM(v.`uv`) AS uv, SUM(v.`newUv`) AS newUv FROM `ma_keywords_visits` v LEFT JOIN `ma_keywords_name` k ON v.`kid`=k.`id` WHERE v.`WebsiteId` = " . $websiteId . $where . " GROUP BY  k.`name` ORDER BY  " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";";
			$result = mysql_query($sql);
			//echo $sql;
			// Add all records to an array
			$rows = array();
			while ($row = mysql_fetch_array($result)) {
				if (empty($row['ip'])) {
					$row['ip'] = 0;
				}
				if (empty($row['pv'])) {
					$row['pv'] = 0;
				}
				if (empty($row['uv'])) {
					$row['uv'] = 0;
				}
				if (empty($row['newUv'])) {
					$row['newUv'] = 0;
				}
				$rows[] = $row;
			}
			
		}

		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}	
	// Creating a new record (createAction)
	else if ($_GET["action"] == "create") {
		// Insert record into database
		$result = mysql_query("INSERT INTO people(Name, Age, RecordDate) VALUES('" . $_POST["Name"] . "', " . $_POST["Age"] . ",now());");

		// Get last inserted record (to return to jTable)
		$result = mysql_query("SELECT * FROM people WHERE PersonId = LAST_INSERT_ID();");
		$row = mysql_fetch_array($result);

		// Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Record'] = $row;
		print json_encode($jTableResult);
	}// Updating a record (updateAction)
	else if ($_GET["action"] == "update") {
		// Update record in database
		$result = mysql_query("UPDATE people SET Name = '" . $_POST["Name"] . "', Age = " . $_POST["Age"] . " WHERE PersonId = " . $_POST["PersonId"] . ";");

		// Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}// Deleting a record (deleteAction)
	else if ($_GET["action"] == "delete") {
		// Delete from database
		$result = mysql_query("DELETE FROM people WHERE PersonId = " . $_POST["PersonId"] . ";");

		// Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	else if ($_GET["action"] == "keywordChart") {
			if (!empty($_GET["KeywordName"])&& isset($_GET["islike"])) {
				$KeywordName=$_GET["KeywordName"];
				$where .= " and k.`name` = '" . $KeywordName . "'";				 		
			}
			if (!empty($lang)) {
				$where .= " and `lang` ='" . $lang . "'";
			}
		
			$s_range = strtotime($s_range);
			$e_range = strtotime($e_range);
			$ss_range = date("Y-m-d", $s_range);
			$ee_range = date("Y-m-d", $e_range);

			for ($d = $s_range; $d <= $e_range; $d = $d + 86400) {
				$day_array[date("Y-m-d", $d)] = array();
			}
			
			$sql = "SELECT v.`time`,SUM(v.`pv`) AS pv, SUM(v.`ip`) AS ip, SUM(v.`uv`) AS uv, SUM(v.`newUv`) AS newUv FROM `ma_keywords_visits` v LEFT JOIN `ma_keywords_name` k ON v.`kid`=k.`id` WHERE v.`WebsiteId` = " . $websiteId . $where . " GROUP BY  v.`time` ORDER BY v.`time` DESC;";
			//echo $sql;
			$result = mysql_query($sql);
			// Add all records to an array
			
			$row = array();
			while ($row = mysql_fetch_assoc($result)) {

				if (empty($row['ip'])) {
					$row['ip'] = 0;
				}
				if (empty($row['pv'])) {
					$row['pv'] = 0;
				}
				if (empty($row['uv'])) {
					$row['uv'] = 0;
				}
				if (empty($row['newUv'])) {
					$row['newUv'] = 0;
				}
				
				$row["time"] = date("Y-m-d", strtotime($row["time"]));
				$day_array[date("Y-m-d", strtotime($row["time"]))] = $row;
			}
			//var_dump($day_array);exit;
			foreach ($day_array as $key => $value) {
				if (empty($value)) {
					$data_array[] = $day_array[$key] = array('ip' => 0, 'pv' => 0, 'uv' => 0, 'newUv' => 0, 'time' => $key);
				} else {
					$data_array[] = $value;
				}
			}

			print json_encode($data_array);		
	}
	else if ($_GET["action"] == "chart") {

		if (!empty($lang)) {
			$where .= " and `lang` ='" . $lang . "'";
		}

		$s_range = strtotime($s_range);
		$e_range = strtotime($e_range);
		$ss_range = date("Y-m-d", $s_range);
		$ee_range = date("Y-m-d", $e_range);

		for ($d = $s_range; $d <= $e_range; $d = $d + 86400) {
			$day_array[date("Y-m-d", $d)] = array();
		}

		if (!empty($_GET["ROI"]) and $_GET["ROI"] == 1) {

			$sql_roi = "SELECT * FROM (SELECT p.`PromotionName`, IFNULL((SUM(`payamount`)-SUM(`adcostUsd`))/SUM(`adcostUsd`),0) AS ROI, IFNULL(SUM(`payamount`), 0) AS payamount, ROUND(SUM(`adcostUsd`),2) AS adcostUsd, SUM(`payorder`) AS payorder FROM `milanoo_promotionurl` p, `ma_promotion_visits` v WHERE v.`WebsiteId` = v.`WebsiteId` AND p.id = v.`promotionid` AND v.`WebsiteId` = " . $websiteId . " AND 1 = 1 " . $where_roi . " GROUP BY PromotionName ) a WHERE ROI <-0.3 ORDER BY ROI ASC,payamount ASC,adcostUsd DESC";
			$rs_roi = mysql_query($sql_roi);
			$row_roi = array();
			while ($row_roi = mysql_fetch_assoc($rs_roi)) {
				$low_roi[] = '"' . $row_roi['PromotionName'] . '"';
			}

			$nu_roi = count($low_roi);
			$low_roi_srt = implode(',', $low_roi);

			$where .= " and p.`PromotionName` in (" . $low_roi_srt . ")";

		} else {
			if (!empty($_GET["promotion_name_row"]) and isset($_GET["islike"])) {
				$PromotionName = trim($_GET["promotion_name_row"]);
				$sql = "SELECT DISTINCT category from milanoo_promotionurl where PromotionName = '%" . $PromotionName . "'";
				$result = mysql_query($sql);
				$row = mysql_fetch_array($result);
				$category = $row['category'];
				if (empty($category)) {
					$where .= " and p.`PromotionName` = '" . $PromotionName . "'";
				} else {
					$where .= " and p.`PromotionName` = '" . $PromotionName . "' and p.`category` ='" . $category . "'";
				}

			} elseif (!empty($_GET["promotion_name_row"]) and !isset($_GET["islike"])) {
				$PromotionName = rawurldecode(trim($_GET["promotion_name_row"]));
				//$sql = "SELECT DISTINCT category from milanoo_promotionurl where PromotionName = '%".$PromotionName."'";
				//$result = mysql_query ( $sql );
				//$row = mysql_fetch_array ( $result );
				//$category = $row ['category'];

				if ($_GET["search_type"] == 'regex') {
					$where .= " and p.`PromotionName` REGEXP '" . $PromotionName . "'";
				} else {
					$where .= " and p.`PromotionName` like '%" . $PromotionName . "%'";
				}
			}
		}
		// if (! empty ( $_GET ["categories_id"] )) {
		// $categories_id = trim ( $_GET ["categories_id"] );
		// $where .= " and p.`category_id` in (" . trim($categories_id, ",") . ")";
		// }

		if (!empty($_GET["categories_id"])) {
			$categories_id = trim($_GET["categories_id"], ',');
			//$_POST ["addition"]
			if (isset($_GET["addition"]) and $_GET["addition"] == 1) {
				$data = array('parentCategoryId' => $categories_id, //$categories_id
				'languageCode' => 'en-uk', 'returnLevel' => 1, 'returnChildNum' => '-1:-1:-1:-1');
				$categoriesData = getServiceData('products', 'getProductsCategory', $data, 'GET', 'products');
				//var_dump($categoriesData);exit;
				$categories_Child = implode(",", array_multiToSingle($categoriesData));
			}

			if (empty($categories_Child)) {
				$where .= " and p.`category_id` in (" . trim($categories_id, ",") . ")";
			} else {
				$where .= " and p.`category_id` in (" . $categories_Child . ")";
			}

		}

		$sql = "SELECT round(SUM(`payorder`)/(SUM(`payorder`)+SUM(`unpayorder`))*100,2) as paymentrate,IFNULL(round((SUM(`payamount`)-SUM(`adcostUsd`))/SUM(`adcostUsd`)*100,2),0) as ROI,v.`time` ,SUM(`payorder`)/SUM(v.`uv`) as purate,SUM(v.`ip`) as ip ,SUM(v.`pv`) as pv ,SUM(v.`uv`) as uv ,SUM(v.`newUv`) as newUv ,SUM(`payorder`) as payorder,SUM(`unpayorder`) as unpayorder ,SUM(`regmember`) as regmember ,SUM(`subscribers`) as subscribers FROM `milanoo_promotionurl` p LEFT JOIN `milanoo_promotion_category` pa ON pa.`Id` = `category` AND pa.`WebsiteId` = 1,`ma_promotion_visits` v WHERE v.`WebsiteId` = " . $websiteId . " and p.id = v.`promotionid` " . $where . " GROUP BY  v.`time` ORDER BY v.`time`";
		$result = mysql_query($sql);
		// Add all records to an array
		// echo $sql;
		$row = array();
		while ($row = mysql_fetch_assoc($result)) {

			if (empty($row['ip'])) {
				$row['ip'] = 0;
			}
			if (empty($row['pv'])) {
				$row['pv'] = 0;
			}
			if (empty($row['uv'])) {
				$row['uv'] = 0;
			}
			if (empty($row['newUv'])) {
				$row['newUv'] = 0;
			}
			if (empty($row['payorder'])) {
				$row['payorder'] = 0;
			}
			if (empty($row['unpayorder'])) {
				$row['unpayorder'] = 0;
			}
			if (empty($row['regmember'])) {
				$row['regmember'] = 0;
			}
			if (empty($row['subscribers'])) {
				$row['subscribers'] = 0;
			}
			if (empty($row['payamount'])) {
				$row['payamount'] = 0;
			}
			if (empty($row['purate'])) {
				$row['purate'] = 0;
			}
			$row["time"] = date("Y-m-d", strtotime($row["time"]));
			$day_array[date("Y-m-d", strtotime($row["time"]))] = $row;
		}
		//var_dump($day_array);exit;
		foreach ($day_array as $key => $value) {
			if (empty($value)) {
				$data_array[] = $day_array[$key] = array('ip' => 0, 'pv' => 0, 'uv' => 0, 'newUv' => 0, 'payorder' => 0, 'unpayorder' => 0, 'payamount' => 0, 'purate' => 0, 'regmember' => 0, 'subscribers' => 0, 'time' => $key);
			} else {
				$data_array[] = $value;
			}
		}

		print json_encode($data_array);
	}

	// Close database connection
	mysql_close($con);
} catch ( Exception $ex ) {
	// Return error message
	$jTableResult = array();
	$jTableResult['Result'] = "ERROR";
	$jTableResult['Message'] = $ex -> getMessage();
	print json_encode($jTableResult);
}

/***********************
 **将多维数组合并为一位数组
 ***********************/
function array_multiToSingle($array, $clearRepeated = false) {
	static $result_array = array();
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			array_multiToSingle($value);
		} else if ($key == 'categoryId') {
			$result_array[] = $value;
		}
	}
	return $result_array;
}

/***********************
 **CSV 导出
 ***********************/
function export_csv($filename, $data) {
	header("Content-type:text/csv");
	header("Content-Disposition:attachment;filename=" . $filename);
	header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
	header('Expires:0');
	header('Pragma:public');
	echo $data;
}

function getSeachCategoriesName(&$data, $item) {
	$data['cuurentId'] = $item['categoryId'];
	$data['name'][] = $item['categoryName'];
	if (isset($item['nextCategory'])) {
		$data['pid'] = $item['categoryId'];
		getSeachCategoriesName($data, $item['nextCategory']);
	}
}

function replaceAccents($str) {
	$search = explode(",");
	$replace = explode(" ");
	return str_replace($search, $replace, $str);
}

function getServiceData($module, $action, $param, $method = 'GET', $namespace = '') {
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
?>