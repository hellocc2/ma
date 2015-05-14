<?php
namespace Module\Sale;
use Helper\RequestUtil as R;
use Helper\CheckLogin as CheckLogin;

class Category extends \Lib\Common\Application {
	public function __construct() {
		CheckLogin::getMemberID();
		$tpl = \Lib\common\Template::getSmarty ();
		$time = R::requestParam ( 'time' );
		$id = R::requestParam ( 'id' ); 
		$level = R::requestParam ( 'level' );
		$category = R::requestParam ( 'category' );
		$categoryname = R::requestParam ( 'categoryname' );
		$list = new \model\Sale ();
		
		if (!empty($category)) {
			$tpl->assign ( 'category', $category );
		}
		if (!empty($categoryname)) {
			$tpl->assign ( 'categoryname', $categoryname );
		}
		
		$sell = $list->getCategorySell ( $time, $id ,$level,$category);
		//print_r($sell);exit;
		$sell_list = array ();
		$sell_web = $sell_wap = $sell_num = $sell_suppliersprice = 0;
		foreach ( $sell as $key => $value ) {
			$sell_array = array ();
			$sell_num_order = $sell_num_product = $sell_num_price = $sell_num_suppliersprice = 0;
			foreach ( $value as $k => $v ) {
				
				$sell_num_order += $v ["OrderNum"];
				$sell_num_product += $v ["ProductsNum"];
				$sell_num_price += $v ["ProductsPrice"];
				$sell_num_suppliersprice += $v ["SuppliersPrice"];
				
				$category_sell[$k]["ProductsNum"] += $v ["ProductsNum"];
				$category_sell[$k]["OrderNum"] += $v ["OrderNum"];
				$category_sell[$k]["ProductsPrice"] += round($v ["ProductsPrice"],2);
				$category_sell[$k]["SuppliersPrice"] += round($v ["SuppliersPrice"],2);
				$category_sell[$k]["Maori"] = round(($category_sell[$k]["ProductsPrice"]-$category_sell[$k]["SuppliersPrice"])*100/$category_sell[$k]["ProductsPrice"],2);
				$category_sell[$k]["category_name"] = $v ["category_name"];
				$category_sell[$k]["category_code"] = $v ["category_code"];
				$category_sell[$k]["level"] = $v ["level"];
				
				$sell_array [$k] = array ("OrderNum" => $v ["OrderNum"], "ProductsNum" => $v ["ProductsNum"], "ProductsPrice" => round($v ["ProductsPrice"],2), "category_name" => $v ["category_name"], "level" => $v ["level"] );
			}
			$sell_web += $sell_num_product;
			$sell_wap += round($sell_num_price,2);
			$sell_suppliersprice += round($sell_num_suppliersprice,2);
			$sell_num += $sell_num_order;
			$sell_list [] = array ("date" => $key, "sell_num_price" => $sell_num_price, "sell_num_product" => $sell_num_product, "sell_num_order" => $sell_num_order, "sell_suppliersprice"=>$sell_num_suppliersprice, "sell" => $sell_array );
		}
		//print_r($category_sell);
		//$sell_rate = round($sell_wap/($sell_wap+$sell_web)*100,2);
		//print_r ( $sell_list );
		$sell_Maori = round(($sell_wap-$sell_suppliersprice)*100/$sell_wap,2);
		$tpl->assign ( 'sell_list', $sell_list );
		$tpl->assign ( 'sell_Maori', $sell_Maori);
		$tpl->assign ( 'sell_suppliersprice', $sell_suppliersprice );
		$tpl->assign ( 'sell_num', $sell_num );
		$tpl->assign ( 'sell_wap', $sell_wap );
		$tpl->assign ( 'sell_web', $sell_web );
		$tpl->assign ( 'category_sell', $category_sell );
		$tpl->assign ( "lang", $_SESSION ["ma_lang"] );
		$tpl->assign ( 'start_time', $_SESSION ["ma_starttime"] );
		$tpl->assign ( 'end_time', $_SESSION ["ma_endtime"] );
		$tpl->display ( 'sale_category.htm' );
	}
}