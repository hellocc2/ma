<?php
namespace Module\Shop;

use helper\ResponseUtil as Rewrite;

/**
 * 支付流程第一步
 * @Jiang Lin<jianglin@mialnoo.com>
 *
 */
class Payment extends \Lib\common\Application {
	
	function __construct() {
		global $tpl;
		$tpl = \Lib\common\Template::getSmarty();
		
		//检查是否登录
		$this->memberId = \Helper\CheckLogin::getMemberID();
		$mem = \Lib\Cache::init();
		
		//获取传递参数
		$params_all = \Helper\RequestUtil::getParams();
		$orderId = $params_all->params['id'];
		
		$shoppingProcess = new \Model\ShoppingProcess();
		
		$paySelected = 'xyk';
		
		if(!isset($params_all->act)) {
			$params_all->act = '';
		}
		
		if(!empty($orderId)) {
			if($params_all->act != 'editBillingPost') {
				$orderInfo = $shoppingProcess->GetOrderById(array('cr.ordersId' => $orderId, 'cr.lang' => SELLER_LANG, 'cr.memberId' => $this->memberId));
				if($orderInfo['orderInfo']['order']['ordersPay'] == 1) {
					header("Location:" . Rewrite::rewrite(array('url' => '?module=shop&action=Achieve&id=' . $orderId, 'isxs' => 'no')));
					exit();
				}
			}
		
		} else {
			header("Location:" . Rewrite::rewrite(array('url' => '?module=shop&action=Cart', 'isxs' => 'no')));
		}
		switch($params_all->act) {
			case 'payment':
				\helper\FormAuth::auth();
				\Helper\Payment::orderPayment($orderInfo['orderInfo'], $params_all);
				header("Location:" . Rewrite::rewrite(array('url' => '?module=shop&action=Achieve&id=' . $orderId, 'isxs' => 'no')));
				exit;
				break;
			case 'editBillingPost':
				$paramData = array();
				$paramData['order.memberId'] = $this->memberId;
				$paramData['order.ordersId'] = $orderId;
				
				$paramData['order.billingName'] = implode('|', $params_all->billingName);
				if(isset($params_all->ConsigneeNameJa)) {
					$paramData['order.billingNameJa'] = implode('|', $params_all->ConsigneeNameJa);
				}
				$paramData['order.billingGender'] = $params_all->billingGender;
				$paramData['order.billingPostalcode'] = $params_all->billingZip;
				$paramData['order.billingAddr'] = implode('|', $params_all->billingAddress);
				$paramData['order.billingPhone'] = $params_all->countryCode.' '.$params_all->billingPhone;
				$paramData['order.billingCtiy'] = $params_all->billingCity;
				$paramData['order.billingUrbanAreas'] = $params_all->billingUrbanAreas;
				$paramData['order.ordersBillingStateId'] = $params_all->billingState;
				$response = $shoppingProcess->updateBillingAddress($paramData);
				
				$orderInfo = $shoppingProcess->GetOrderById(array('cr.ordersId' => $orderId, 'cr.lang' => SELLER_LANG, 'cr.memberId' => $this->memberId));
				if($orderInfo['orderInfo']['order']['ordersPay'] == 1) {
					header("Location:" . Rewrite::rewrite(array('url' => '?module=shop&action=Achieve&id=' . $orderId, 'isxs' => 'no')));
					exit();
				}
			
			default:
				if(!isset($_SESSION[SESSION_PREFIX . "ceyberSource"]) || $_SESSION[SESSION_PREFIX . "ceyberSource"] == "") {
					$ceyberSourceSession = md5($this->memberId . time());
					$_SESSION[SESSION_PREFIX . "ceyberSource"] = $ceyberSourceSession;
				}
				
				$tpl->assign('cbSession', $_SESSION[SESSION_PREFIX . "ceyberSource"]);
				$tpl->assign('cbMerchantID', 'milanoocom');
				$tpl->assign('cbOrgID', 'k8vif92e');
		}
		$formAuthMD5 = \helper\FormAuth::createAuthCode();
		$tpl->assign('formAuth', $formAuthMD5);
		$tpl->assign('paymentSelected', $paySelected);
		$tpl->assign('orderInfo', $orderInfo);
		
		$tpl->assign('shopping_process',1);
		$tpl->assign('order_succeed',1);
		$tpl->display('payment.htm');
		
		return;
	}

}