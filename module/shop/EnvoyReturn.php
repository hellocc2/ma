<?php
namespace Module\Shop;

use helper\ResponseUtil as Rewrite;
/**
 * Envoy 成功返回页面
 * @Jiang Lin<jianglin@mialnoo.com>
 *
 */
class EnvoyReturn extends \Lib\common\Application {
	
	function __construct() {
		
		
		if(isset($_REQUEST['epacsReference']) && !empty($_REQUEST['epacsReference'])) {
			$request = new \stdClass();
			$request->epacsReference = $_REQUEST['epacsReference'];
			
			$envoy = new \Lib\_3rd\envoy\EnvoyLib();
			$response = $envoy->payInConfirmation($request);
			
			//更新数据库中的数据
			if($response->payInConfirmationResult->statusCode == 0) {
				$paytime = strtotime($response->payInConfirmationResult->payment->postingDate);
				$OrdersPayDetails = 'Payment:yhzx|' . 'CurrencyCode:EUR|' . 'amount:' . $response->payInConfirmationResult->payment->bankAmount . '|' . 'Remarks:' . $response->payInConfirmationResult->payment->bankInformation . '|' . 'time:' . $paytime;
				$shoppingProcess = new \Model\ShoppingProcess();
				$response = $shoppingProcess->updateOrder(array('cr.ordersCid' => $response->payInConfirmationResult->payment->merchantReference, 'cr.ordersPay' => 1, 'cr.ordersPayDetails' => $OrdersPayDetails));
				header("Location:" . Rewrite::rewrite(array('url' => '?module=shop&action=Achieve&id=' . $response['ordersId'], 'isxs' => 'no')));
				exit();
			}
		}
		
		header("Location:" . Rewrite::rewrite(array('url' => '?module=index','isxs' => 'no')));
		exit;
	}

}