<?php

namespace Module\Competence;
use Helper\RequestUtil as R;
use Helper\CheckLogin as CheckLogin;
use Helper\ServiceData as ServiceData;

class User extends \Lib\common\Application {
	public function __construct() {
		CheckLogin::getMemberID();
		$params = R::getParams();
		$tpl = \Lib\common\Template::getSmarty();
		$menu_action = $params -> menu_action;
		$competence = new \model\Competence ();
		$MAcompetencegroup = $competence->getMAcompetencegroup();
		
		$milanoo_admin_competencegroup = $competence->get_milanoo_admin_competence();
		
		$MAcompetence = array();
		foreach ($MAcompetencegroup as $key => $value) {
			$MAcompetence[]= $key;
		}

		$milanoo_admin_competence = array();
		foreach ($milanoo_admin_competencegroup as $key => $value) {
			$milanoo_admin_competence[]= $key;
		}
		
		$tpl -> assign('milanoo_admin_competence', $milanoo_admin_competence);
		$tpl -> assign('MAcompetence', $MAcompetence);
		$id = $params -> id;
		$WebsiteId = $_SESSION['ma_websiteId'];
		$tpl -> assign('WebsiteId', $WebsiteId);

		$db = \Lib\common\Db::get_db('default'); 
		$milanoo_db = \Lib\common\Db::get_db('milanoo');
		
		//$db -> debug =1;
		$realname = $params -> realname;
		$categoryId = $params -> categoryId;
		$Categories_id = $params -> Categories_id;
		$is_show_cid = $params -> is_show_cid;
		$filter_categoryId = $params -> filter_categoryId;
		$addition = $params -> addition;
		$is_show_c = $params -> is_show_c;
		$search_type = $params -> search_type;
		$tpl -> assign('search_type', $search_type);
		
		switch ($menu_action) {
			case 'binding' :
				$url_jump = $params -> url_jump;
				$userId = $params -> userId;
				$db = \Lib\common\Db::get_db('default');

				foreach ($userId as $key => $value) {
					$sql = "SELECT count(id) FROM `user_rights` WHERE uid = '{$key}'";
					$isseet = $db -> getone($sql);
					
					if ( $isseet > 0) {
						$sql = "UPDATE `user_rights` SET `rule` = '{$value}' WHERE `uid` = '{$key}'";
						$sth = $db->Prepare ( $sql );
						$res = $db->Execute ( $sth );
					} else {
						$sql = "INSERT INTO `user_rights` ( `uid`, `rule` ) VALUES ( '{$key}', '{$value}' )";
						$sth = $db->Prepare ( $sql );
						$res = $db->Execute ( $sth );
					}
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

				if (!empty($realname)) {
 
			 		$where .= " and au.`realname` = '" . $realname . "'";
 
					$tpl -> assign('realname', $realname);
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

				if (empty($is_show_c)) {
					if (!empty($categoryId)) {
						$sql = "SELECT `uid` FROM `user_rights` WHERE rule = '{$categoryId}'";
						$rs = $db -> execute($sql);
						$row = array();
						$rule_all =array();
						if ($rs -> RecordCount()) {
							while (!$rs -> EOF) {
								$row = $rs -> fields;
								$rule_all[] = $row["uid"];
								$rs -> MoveNext();
							}
						}
						$rule_str = implode(',', $rule_all);
						$where .= " and au.`uid` in (".$rule_str.")";
						$tpl -> assign('categoryId', $categoryId);
					}
				} else {
					$where .= " and p.`category` = 0";
					$tpl -> assign('is_show_c', $is_show_c);
				}

				$sql = "SELECT * FROM `milanoo_admin_user` au, milanoo_admin_competence ac WHERE ac.id IN (au.competence_id) ".$where." AND activation = 1 AND FIND_IN_SET  ('1351', competence_menu)";
				$query = $milanoo_db -> execute($sql);
				
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
				
				
				
				$reload = $_SERVER['PHP_SELF'] . "?module=competence&action=User&tpages=" . $tpages;
	
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
				$rs = $milanoo_db -> SelectLimit($sql, PAGE, $i);
				$row = array();
				$promotionurl_all = array();
				if ($rs -> RecordCount()) {
					while (!$rs -> EOF) {
						$row = $rs -> fields;
						if ($row["id"]) {
							$sql = "SELECT rule FROM `user_rights` WHERE uid = '{$row["uid"]}'";
							$rule = $db -> getone($sql);
							$row = $row + array( 'rule'=> $rule);
							$promotionurl_all[] = $row;
						}
						$rs -> MoveNext();
					}
				}
				$tpl -> assign('c_page', $page);
				$tpl -> assign('promotionurl_all', $promotionurl_all);
				$tpl -> assign('reload', $reload);
				$tpl -> display('user_management.htm');
				break;
		}
	}

}
