<?php
namespace Module\Sale;
use Helper\RequestUtil as R;
use Helper\CheckLogin as CheckLogin;

class Index extends \Lib\Common\Application {
	public function __construct() {
	    CheckLogin::getMemberID();
		$tpl = \Lib\common\Template::getSmarty ();
		$time = R::requestParam ( 'time' );
		$list = new \model\Sale ();
		$sell = $list->getSaleSell ($time);
		$sell_list = array();
		$sell_web = $sell_wap = $sell_ipad = $sell_num = $sell_web_num = 0;
		foreach ($sell as $key => $value){
			$sell_array =array();
			$sell_amount_web = $sell_amount_wap = $sell_amount_ipad = $sell_amount_ios = $sell_amount_android = $sell_num_wap = $sell_num_web = $sell_num_ipad = $sell_num_android = $sell_num_ios = 0;
			foreach ($value as $k=>$v){
				if($k==1){
					$sell_amount_web += $v["OrdersAmount"]+$v["LogisticsAmount"]+$v["Insurance"];
					$sell_num_web += $v["OrderNum"];
				}
				elseif ($k==5){
					$sell_amount_ipad += $v["OrdersAmount"]+$v["LogisticsAmount"]+$v["Insurance"];
					$sell_num_ipad += $v["OrderNum"];
				}
				elseif ($k==3){
					$sell_amount_ios += $v["OrdersAmount"]+$v["LogisticsAmount"]+$v["Insurance"];
					$sell_num_ios += $v["OrderNum"];
				}
				elseif ($k==4){
					$sell_amount_android += $v["OrdersAmount"]+$v["LogisticsAmount"]+$v["Insurance"];
					$sell_num_android += $v["OrderNum"];
				}
				else {
					$sell_num_wap += $v["OrderNum"];
					$sell_amount_wap += $v["OrdersAmount"]+$v["LogisticsAmount"]+$v["Insurance"];
				}
				$sell_array[$k] = array("OrdersAmount"=>$v["OrdersAmount"]+$v["LogisticsAmount"]+$v["Insurance"],"ProductAmount"=>$v["OrdersAmount"],"LogisticsAmount"=>$v["LogisticsAmount"],"OrderNum"=>$v["OrderNum"]);
			}
			$sell_web += $sell_amount_web;
			$sell_web_num += $sell_num_web;
			$sell_wap += $sell_amount_wap;
			$sell_ipad += $sell_amount_ipad;
			$sell_ios += $sell_amount_ios;
			$sell_android += $sell_amount_android;
			$sell_num += $sell_num_wap+$sell_num_ios+$sell_num_android;
			$sell_amount = $sell_amount_wap+$sell_amount_web+$sell_amount_ipad+$sell_amount_ios+$sell_amount_android;
			$sell_list[] = array("date"=>$key,"wap_rate"=>round($sell_amount_wap/($sell_amount)*100,2),"ipad_rate"=>round($sell_amount_ipad/($sell_amount)*100,2),"android_rate"=>round($sell_amount_android/($sell_amount)*100,2),"ios_rate"=>round($sell_amount_ios/($sell_amount)*100,2),"mobile_rate"=>round(($sell_amount_wap+$sell_amount_ipad+$sell_amount_ios+$sell_amount_android)/($sell_amount)*100,2),"phone_rate"=>round(($sell_amount_wap+$sell_amount_ios+$sell_amount_android)/($sell_amount)*100,2),"sell_amount_web"=>$sell_amount_web,"sell_amount_ipad"=>$sell_amount_ipad,"sell_amount_wap"=>$sell_amount_wap,"sell_amount_ios"=>$sell_amount_ios,"sell_amount_android"=>$sell_amount_android,'sell_num_wap'=>($sell_num_wap+$sell_num_android+$sell_num_ios),'sell_num_web'=>$sell_num_web,"sell"=>$sell_array);
		}
		$sell_rate = round(($sell_wap+$sell_ios+$sell_android)/($sell_wap+$sell_ios+$sell_android+$sell_web+$sell_ipad)*100,2);
		$sell_rate_ipad = round($sell_ipad/($sell_wap+$sell_ios+$sell_android+$sell_web+$sell_ipad)*100,2);
		$sell_rate_mobile = round(($sell_ipad+$sell_wap+$sell_ios+$sell_android)/($sell_wap+$sell_ios+$sell_android+$sell_web+$sell_ipad)*100,2);
		//print_r($sell_list);
		$tpl->assign ( 'sell_list', $sell_list );
		$tpl->assign ( 'sell_web_num', $sell_web_num );
		$tpl->assign ( 'sell_num', $sell_num );
		$tpl->assign ( 'sell_wap', $sell_wap+$sell_ios+$sell_android );
		$tpl->assign ( 'sell_ipad', $sell_ipad );
		$tpl->assign ( 'sell_mobile', $sell_rate_mobile );
		$tpl->assign ( 'sell_web', $sell_web );
		$tpl->assign ( 'sell_rate', $sell_rate );
		$tpl->assign ( 'sell_rate_ipad', $sell_rate_ipad );
		$tpl->assign ( "lang", $_SESSION["ma_lang"] );
		$tpl->assign ( 'start_time', $_SESSION["ma_starttime"] );
		$tpl->assign ( 'end_time', $_SESSION["ma_endtime"] );
		$tpl->display ( 'sale_index.htm' );
	}
}