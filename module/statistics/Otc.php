<?php

namespace Module\Statistics;

use Helper\RequestUtil as R;
use \Helper\Promotion as Promotion;
use Helper\CheckLogin as CheckLogin;
use Helper\ServiceData as ServiceData;

class Otc extends \Lib\common\Application {
	public function __construct() {
		CheckLogin::getMemberID();
		$params = R::getParams();
		$tpl = \Lib\common\Template::getSmarty();
		$menu_action = $params -> menu_action;
		$id = $params -> id;
		$WebsiteId = $_SESSION['ma_websiteId'];
		$tpl -> assign('WebsiteId', $WebsiteId);

		$db = \Lib\common\Db::get_db('default');
		//$db -> debug =1;
		$PromotionName = $params -> PromotionName;
		$categoryId = $params -> categoryId;
		$Categories_id = $params -> Categories_id;
		$is_show_cid = $params -> is_show_cid;
		$is_sales = $params -> is_sales;
		$filter_categoryId = $params -> filter_categoryId;
		$addition = $params -> addition;
		$is_show_c = $params -> is_show_c;
		$search_type = $params -> search_type;
		$tpl -> assign('search_type', $search_type);
		
		switch ($menu_action) {
			case 'binding' :
				$url_jump = $params -> url_jump;
				$category = $params -> category;
				$db = \Lib\common\Db::get_db('default');
				foreach ($category as $key => $value) {
					if ($Categories_id[$key] > 0) {
						$sql = "update `milanoo_promotionurl` set category ='$value',category_id = '$Categories_id[$key]' where id='$key'";
					} else {
						$sql = "update `milanoo_promotionurl` set category ='$value',category_id = NULL where id='$key'";
					}
					$db -> execute($sql);
				}
				header('Location:'.$url_jump);
				break;
			default :
				
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
				
				$file = '../config/Java_webservice_url.php';
				if (is_file($file)) {
					include $file;
				}
				
				if (isset($addition) and $addition == 1 and isset($filter_categoryId)) {
					$data = array('parentCategoryId' => $filter_categoryId, 'languageCode' => 'en-uk', 'returnLevel' => 1, 'returnChildNum' => '-1:-1:-1');
					$categoriesData = ServiceData::getServiceData('products', 'getProductsCategory', $data, 'GET', 'products');
					$categories_Child = ",".implode(",", array_multiToSingle($categoriesData));
					$tpl -> assign('addition', $addition);
				}

				if (!empty($PromotionName)) {
					if ($search_type == 'regex') {
 						$where .= " and p.`PromotionName` REGEXP '" . $PromotionName . "'";
					} else {
			 			$where .= " and p.`PromotionName` like '%" . $PromotionName . "%'";
					}
					
					$tpl -> assign('PromotionName', $PromotionName);
				}
				
				if (!empty($is_sales)) {
					$where .= " AND p.id = v.`promotionid` AND payorder > 0";
					$tpl -> assign('is_sales', $is_sales);
				}
				
				if (empty($is_show_cid)) {
					if (!empty($filter_categoryId)) {
						$where .= " and p.`category_id` in (" . $filter_categoryId . $categories_Child . ")";
						$tpl -> assign('filter_category', $filter_categoryId);
					}
				} else {
					$where .= " and p.`category_id` is NULL";
					$tpl -> assign('is_show_cid', $is_show_cid);
				}

				$class_all = new Promotion('promotion_category', 0, 'ASC', '', 0, 1);
				$class = $class_all -> class_option('', 0, $categoryId, '');
				$tpl -> assign('class', $class);

				if (empty($is_show_c)) {
					if (!empty($categoryId)) {
						$pid = $class_all -> idALL($categoryId);
						$where .= " and p.`category` in (" . $categoryId . $pid . ")";
						$tpl -> assign('categoryId', $categoryId);
					}
				} else {
					$where .= " and p.`category` = 0";
					$tpl -> assign('is_show_c', $is_show_c);
				}
				
				if (!empty($is_sales)) {
					$sql = "SELECT p.`id`,p.`PromotionName`,p.`category`,p.`category_id`,COUNT(v.`payorder`) as payorder FROM `milanoo_promotionurl` p ,`ma_promotion_visits` v WHERE 1 = 1 " . $where . " GROUP BY p.`PromotionName` ORDER BY payorder DESC";
				} else {
					$sql = "SELECT p.`id`,p.`PromotionName`,p.`category`,p.`category_id` FROM `milanoo_promotionurl` p WHERE 1 = 1 " . $where . " GROUP BY p.`PromotionName` ORDER BY p.id desc";
				}
				$query = $db -> execute($sql);
				// echo $sql;
				$total_results = $query -> MaxRecordCount($query);
				$total_pages = ceil($total_results / PAGE);

				if (isset($_GET['page'])) {
					$show_page = $_GET['page'];
					if ($show_page > 0 && $show_page <= $total_pages) {
						$start = ($show_page - 1) * PAGE;
						$end = $start + PAGE;
					} else {
						$start = 0;
						$end = PAGE;
					}
				} else {

					$start = 0;
					$end = PAGE;
				}

				$page = intval($_GET['page']);

				$tpages = $total_pages;
				if ($page <= 0)
					$page = 1;
				
				
				
				$reload = $_SERVER['PHP_SELF'] . "?module=statistics&action=Otc&tpages=" . $tpages;
	
				if (!empty($search_type)) {
					$reload .= '&search_type=' . $search_type;
				}
				
				if (!empty($categoryId)) {
					$reload .= '&categoryId=' . $categoryId;
				}

				if (!empty($PromotionName)) {
					$reload .= '&PromotionName=' . $PromotionName;
				}

				if (!empty($filter_categoryId)) {
					$reload .= '&filter_categoryId=' . $filter_categoryId;
				}

				if (!empty($is_show_c)) {
					$reload .= '&is_show_c=' . $is_show_c;
				}				
				
				if (!empty($is_show_cid)) {
					$reload .= '&is_show_cid=' . $is_show_cid;
				}
				
				if (!empty($is_sales)) {
					$reload .= '&is_sales=' . $is_sales;
				}
				
				if (isset($addition) and $addition == 1 and isset($filter_categoryId)) {
					$reload .= '&addition=' . $addition;
				}
				
				
				for ($i = $start; $i < $end; $i++) {
					if ($i == $total_results) {
						break;
					}
				}

				if ($total_pages > 1) {
					$page_html = \Lib\common\Page::paginate($reload, $page, $tpages);
					$tpl -> assign('page', $page_html);
				}
				$i = $i - PAGE;
				$rs = $db -> SelectLimit($sql, PAGE, $i);
				$row = array();
				$promotionurl_all = array();
				if ($rs -> RecordCount()) {
					while (!$rs -> EOF) {
						$row = $rs -> fields;
						if ($row["id"]) {
							$row['class_all'] = $class_all -> class_option('', 0, $row['category'], '');
							$promotionurl_all[] = $row;
							$zl[Click] += $row[Click];
							$zl['member'] += $row['member'];
							$zl['mail'] += $row['mail'];
							$zl[Orders2] += $row[Orders2];
							$zl[Orders] += $row[Orders];
							$zl[atm1] += $row[atm1];
							$zl[atm2] += $row[atm2];
						}
						$rs -> MoveNext();
					}
				}
				$tpl -> assign('c_page', $page);
				$tpl -> assign('promotionurl_all', $promotionurl_all);
				$tpl -> assign('reload', $reload);
				$tpl -> display('otc_management.htm');
				break;
		}
	}

}
