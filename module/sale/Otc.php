<?php
namespace Module\Sale;
use Helper\RequestUtil as R;
use Helper\CheckLogin as CheckLogin;
 
class Otc extends \Lib\Common\Application {
	public function __construct() {
		CheckLogin::getMemberID();
		$tpl = \Lib\common\Template::getSmarty ();
		$time = R::requestParam ( 'time' );
		$id = R::requestParam ( 'c_name' );
		$cid = R::requestParam ( 'cid' );
		$list = new \model\Sale ();
		$sell = $list->getCategoryURLSell ( $time, $cid );

		$c_n  = array_flip($sell['c']);
		//var_dump($c_n); 
		unset ($sell['c']);
		$sell_list = array ();
		$sell_ip = $sell_pv = $sell_uv = $sell_web = $sell_wap = $sell_num = $sell_suppliersprice = 0;
		foreach ( $sell as $key => $value ) {
			$sell_array = array ();
			$sell_num_order = $sell_num_product = $sell_num_price = $sell_num_suppliersprice = $sell_num_purate = $sell_num_uv = $sell_num_payamount = $sell_num_pv = $sell_num_ip = 0;
			foreach ( $value as $k => $v ) {
				
				// $page_list_array [$date] [$row ["category_name"]] ["purate"] += $row ["purate"];
				// $page_list_array [$date] [$row ["category_name"]] ["payorder"] += $row ["payorder"];
				// $page_list_array [$date] [$row ["category_name"]] ["unpayorder"] += $row ["unpayorder"];
				// $page_list_array [$date] [$row ["category_name"]] ["payamount"] += $row ["payamount"];
				
				$sell_num_order += $v ["payorder"];
				$sell_num_product += $v ["ProductsNum"];
				$sell_num_price += $v ["unpayorder"];
				$sell_num_payamount += $v ["payamount"];
				$sell_num_pv += $v ["pv"];
				$sell_num_ip += $v ["ip"];
				$sell_num_uv += $v ["uv"];
				$sell_num_purate += $v ["purate"];
				
				$category_sell[$k]["pv"] += $v ["pv"];
				$category_sell[$k]["ip"] += $v ["ip"];
				$category_sell[$k]["uv"] += $v ["uv"];
				$category_sell[$k]["ProductsNum"] += $v ["payorder"];
				$category_sell[$k]["OrderNum"] += $v ["payorder"];
				$category_sell[$k]["purate"] += $v ["purate"];
				$category_sell[$k]["ProductsPrice"] += round($v ["payamount"],2);
				$category_sell[$k]["Maori"] = round(($category_sell[$k]["ProductsPrice"]-$category_sell[$k]["SuppliersPrice"])*100/$category_sell[$k]["ProductsPrice"],2);
				$category_sell[$k]["category_name"] = $k;
				$category_sell[$k]["category_code"] = $v ["category_code"];
				$category_sell[$k]["level"] = 1;
				//b.`WebsiteId`
				$sell_array [$k] = array ("OrderNum" => $v ["payorder"], "ProductsNum" => $v ["1"], "ProductsPrice" => round($v ["payamount"],2), "category_name" => $k, "level" => 1 );
			}

			$sell_wap += $sell_num_payamount;
			$sell_web += $sell_num_product;
			$sell_pv  += $sell_num_pv;
			$sell_ip  += $sell_num_ip;
			$sell_uv  += $sell_num_uv;
			$sell_purate += $sell_num_purate;
			$sell_suppliersprice += round($sell_num_suppliersprice,2);
			$sell_num += $sell_num_order;
			$sell_list [] = array ("date" => $key, "sell_num_price" => $sell_num_price, "sell_num_product" => $sell_num_product, "sell_num_order" => $sell_num_order, "sell_suppliersprice"=>$sell_num_suppliersprice, "sell" => $sell_array );
		}

		//print_r($category_sell);exit;
		//$sell_rate = round($sell_wap/($sell_wap+$sell_web)*100,2);
		//print_r ( $sell_list );exit;
		$sell_Maori = round(($sell_wap-$sell_suppliersprice)*100/$sell_wap,2);
		$tpl->assign ( 'sell_list', $sell_list );
		$tpl->assign ( 'sell_Maori', $sell_Maori);
		$tpl->assign ( 'sell_suppliersprice', $sell_suppliersprice );
		$tpl->assign ( 'sell_num', $sell_num );
		$tpl->assign ( 'sell_wap', $sell_wap );
		$tpl->assign ( 'sell_web', $sell_web );
		$tpl->assign ( 'sell_pv', $sell_pv );
		$tpl->assign ( 'sell_ip', $sell_ip );
		$tpl->assign ( 'sell_uv', $sell_uv );
		$tpl->assign ( 'c_n', $c_n );
		$tpl->assign ( 'time', $time );
		$tpl->assign ( 'sell_purate', $sell_purate );
		$tpl->assign ( 'category_sell', $category_sell );
		$tpl->assign ( "lang", $_SESSION ["ma_lang"] );
		$tpl->assign ( 'start_time', $_SESSION ["ma_starttime"] );
		$tpl->assign ( 'end_time', $_SESSION ["ma_endtime"] );
		$tpl->display ( 'sale_otc.htm' );
	}
}