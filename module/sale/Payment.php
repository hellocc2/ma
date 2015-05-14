<?php
namespace Module\Sale;
use Helper\RequestUtil as R;
use Helper\CheckLogin as CheckLogin;
 
class Payment extends \Lib\Common\Application {
	public function __construct() {
		CheckLogin::getMemberID();
		$tpl = \Lib\common\Template::getSmarty ();
		$time = R::requestParam ( 'time' );
		$id = R::requestParam ( 'c_name' );
		$cid = R::requestParam ( 'cid' );
		$list = new \model\Sale ();
		$sell = $list->getPaymentSell ( $time, $cid );
		
		$c_n  = array_flip($sell['c']);
		//var_dump($sell);exit; 
		unset ($sell['c']);
		$sell_list = array ();
		$unsell_wap = $unsell_num = $sell_ip = $sell_pv = $sell_uv = $sell_web = $sell_wap = $sell_num = $sell_suppliersprice = 0;
		foreach ( $sell as $key => $value ) {
			$sell_array = array ();
			$unsell_num_payamount = $unsell_num_order = $sell_num_order = $sell_num_product = $sell_num_price = $sell_num_suppliersprice = $sell_num_purate = $sell_num_uv = $sell_num_payamount = $sell_num_pv = $sell_num_ip = 0;
			foreach ( $value as $k => $v ) {
				
				if ( strpos($k,"未") === FALSE ) {
					if ($k != '没有支付的订单') {
						$sell_num_order += $v ["payorder"];
						$sell_num_payamount += $v ["payamount"];
					}
				} else {
					$unsell_num_order += $v ["payorder"];
					$unsell_num_payamount += $v ["payamount"];
				}
				if ( strpos($k,"未") === FALSE ) {
					$category_sell_name[$k]["category_name"] = $k;
					$category_sell[$k]["ProductsNum"] += $v ["payorder"];
					$category_sell[$k]["OrderNum"] += $v ["payorder"];
					$category_sell[$k]["ProductsPrice"] += round($v ["payamount"],2);
					$category_sell[$k]["category_name"] = $k;
					$sell_array [$k][1] = array ("OrderNum" => $v ["payorder"], "ProductsNum" => $v ["payorder"], "ProductsPrice" => round($v ["payamount"],2));
				} else {
					$z = str_replace('未支付','',$k);
					$category_sell[$z]["unProductsNum"] += $v ["payorder"];
					$category_sell[$z]["unOrderNum"] += $v ["payorder"];
					$category_sell[$z]["unProductsPrice"] += round($v ["payamount"],2);
					$category_sell[$z]["uncategory_name"] = $k;
					$sell_array [$z][0] = array ("OrderNum" => $v ["payorder"], "ProductsNum" => $v ["payorder"], "ProductsPrice" => round($v ["payamount"],2));
				}
			}

			$sell_num += $sell_num_order;
			$sell_wap += $sell_num_payamount;

			$unsell_num += $unsell_num_order;
			$unsell_wap += $unsell_num_payamount;
			
			$sell_web += $sell_num_product;

			$sell_suppliersprice += round($sell_num_suppliersprice,2);
			
			foreach ($sell_array as $sell_array_key => $sell_array_value) {
				if ( $sell_array_key == '没选支付方式' or $sell_array_key =='没有支付的订单') {
					continue;
				}
				$new_sell_array[$sell_array_key]["OrderNum"] = round($sell_array_value[1]["OrderNum"]/($sell_array_value[1]["OrderNum"]+$sell_array_value[0]["OrderNum"])*100,2);
				$new_sell_array[$sell_array_key]["ProductsNum"] = round($sell_array_value[1]["ProductsNum"]/($sell_array_value[1]["ProductsNum"]+$sell_array_value[0]["ProductsNum"])*100,2);
			}
			
			//var_dump($new_sell_array);exit;
			$sell_list [] = array ("date" => $key, "sell" => $new_sell_array );
		}
		
		// print_r($category_sell);exit;
		// $sell_rate = round($sell_wap/($sell_wap+$sell_web)*100,2);
		// print_r ( $sell_list );exit;
		
		$tpl->assign ( 'category_sell_name', $category_sell_name );
		$tpl->assign ( 'sell_list', $sell_list );
		$tpl->assign ( 'sell_suppliersprice', $sell_suppliersprice );
		$tpl->assign ( 'sell_wap', $sell_wap );
		$tpl->assign ( 'sell_num', $sell_num );
		$tpl->assign ( 'unsell_wap', $unsell_wap );
		$tpl->assign ( 'unsell_num', $unsell_num );
		$tpl->assign ( 'c_n', $c_n );
		$tpl->assign ( 'time', $time );
		$tpl->assign ( 'sell_purate', $sell_purate );
		$tpl->assign ( 'category_sell', $category_sell );
		$tpl->assign ( "lang", $_SESSION ["ma_lang"] );
		$tpl->assign ( 'start_time', $_SESSION ["ma_starttime"] );
		$tpl->assign ( 'end_time', $_SESSION ["ma_endtime"] );
		$tpl->display ( 'sale_payment.htm' );
	}
}