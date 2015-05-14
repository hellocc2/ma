<?php
namespace Module\Shop;

use helper\ResponseUtil as Rewrite;
use Helper\String as H;

/**
 * 下单成功页面
 * @Jiang Lin<jianglin@mialnoo.com>
 *
 */
class Achieve extends \Lib\common\Application {
	
	function __construct() {
		global $tpl;
		$tpl = \Lib\common\Template::getSmarty ();

		//检查是否登录
		$this->memberId = \Helper\CheckLogin::getNoReMemberId();

		//获取传递参数
		$params_all = self::$requestParams;
		$orderId = self::$requestParams->params['id'];
		if(empty($orderId)) {
			header("Location: " . Rewrite::rewrite(array('url' => '?module=shop&action=Cart', 'isxs' => 'no')));
			exit();
		}

		//memcached初始化
		$mem = \Lib\Cache::init();
		$this->cacheKey = $_COOKIE['CartId'];
		$quickPay = 0;
		$noAddress = 0;//当前登录用户是否有收货地址表示，针对快速支付
		$noBillginAddress = 0;//当前登录用户是否有账单地址表示，针对快速支付
		if(empty($this->memberId)){
			$this->memberId = $mem->get($this->cacheKey . 'memberId');
			$orderTempId = $mem->get($this->cacheKey . 'orderId');
			if(!empty($orderTempId) && $orderTempId==$orderId && !empty($this->memberId)){//快速支付标识
				$quickPay = 1;
			}else{
				header("Location: " . Rewrite::rewrite(array('url' => '?module=member&action=login', 'isxs' => 'no')));
				exit();
			}
		}else{
			//将订单合并到新注册或登录用户
			$memTempberId = $mem->get($this->cacheKey . 'memberId');
			$orderTempId = $mem->get($this->cacheKey . 'orderId');
			if(!empty($memTempberId) && !empty($orderTempId) && $orderTempId==$orderId){
				//hebing
				$shoppingProcessForHeBing = new \Model\ShoppingProcess();
				//获取新注册或登录的用户收货地址信息
				$address = $shoppingProcessForHeBing->getAddressList(array('cr.memberId'=>$this->memberId));
				if($address['code']==0){
					if(empty($address['address'])){
						$noAddress = 1;
					}else{
						foreach($address['address'] as $k=>$v){
							if(!empty($v['consigneeAddr'])){
								$noAddress = 0;
								break;
							}else{
								$noAddress = 1;
							}
						}
					}
				}
				
				//获取新注册或登录的用户账单地址信息
				$billingAddress = $shoppingProcessForHeBing->getBillAdress(array('order.memberId'=>$this->memberId));
				if($billingAddress['code']==0){
					if(empty($billingAddress['billingAddress'])){
						$noBillginAddress = 1;
					}else{
						foreach($billingAddress['billingAddress'] as $k=>$v){
							if(!empty($v['bill_address'])){
								$noBillginAddress = 0;
								break;
							}else{
								$noBillginAddress = 1;
							}
						}
					}
				}
				
				$status = $shoppingProcessForHeBing->updateOrderMemberId(array('order.memberId'=>$this->memberId,'order.ordersId'=>$orderTempId));
				if($status['code']==0){
					//合并成功则删除mem中对应键
					$memTempberId = $mem->delete($this->cacheKey . 'memberId');
					$orderTempId = $mem->delete($this->cacheKey . 'orderId');
				}
			}
		}
		
		//获取订单信息
		$shoppingProcess = new \Model\ShoppingProcess();
		$orderInfo = $shoppingProcess->GetOrderById(array('cr.ordersId' => $orderId, 'cr.lang' => SELLER_LANG, 'cr.memberId' => $this->memberId));
		
		if(!empty($orderInfo['orderInfo']) && $orderInfo['code']==0){
			//处理订单信息
			if(!empty($orderInfo['orderInfo']['order'])){
				//快速支付新注册用户收货地址写入
				if($noAddress == 1){
						$operateAddress = $shoppingProcess->setAddress(array(
						'mc.memberId'=>$this->memberId,
						'mc.consigneeName'=>$orderInfo['orderInfo']['order']['consigneeName'],
						'mc.consigneePhone'=>$orderInfo['orderInfo']['order']['consigneePhone'],
						'mc.consigneeStateId'=>$orderInfo['orderInfo']['order']['stateId'],
						'mc.consigneePostalcode'=>$orderInfo['orderInfo']['order']['consigneePostalcode'],
						'mc.consigneeCity'=>$orderInfo['orderInfo']['order']['consigneeCity'],
						'mc.consigneeGender'=>$orderInfo['orderInfo']['order']['consigneeGender'],
						'mc.memberUrbanAreas'=>$orderInfo['orderInfo']['order']['consigneeUrbanAreas'],
						'mc.consigneeAddr'=>$orderInfo['orderInfo']['order']['consigneeAddr']
						));
				}
				//快速支付新注册用户账单地址写入
				if($noBillginAddress == 1){
						$operateBillingAddress = $shoppingProcess->setBillingAddress(array('order.memberId'=>$this->memberId,
						'order.ordersBillingStateId'=>$orderInfo['orderInfo']['order']['ordersBillingStateId'],
						'order.billingName'=>$orderInfo['orderInfo']['order']['billingName'],
						'order.billingPostalcode'=>$orderInfo['orderInfo']['order']['billingPostalcode'],'order.billingPhone'=>$orderInfo['orderInfo']['order']['billingPhone'],
						'order.billingCtiy'=>$orderInfo['orderInfo']['order']['billingCtiy'],
						'order.billingUrbanAreas'=>$orderInfo['orderInfo']['order']['billingUrbanAreas'],
						'order.billingGender'=>$orderInfo['orderInfo']['order']['billingGender'],
						'order.billingAddr'=>$orderInfo['orderInfo']['order']['billingAddr']
						));
				}
				
				//判断是否支付成功(快速支付不进入判断)
				$payClassType = $orderInfo['orderInfo']['order']['payClass'];
				$noLocationTypeArray=array('xlmk','yhhk','yzhk');//不用即时支付成功的支付类型
				if(in_array($payClassType,$noLocationTypeArray)){
					$tpl->assign('yhpay',1);
					if(!$orderInfo['orderInfo']['order']['ordersPay']){
						$tpl->assign('payFaild',1);
					}else{
						$tpl->assign('payFaild',0);
					}
				}else{
					$tpl->assign('yhpay',0);
					if(!$orderInfo['orderInfo']['order']['ordersPay']){
						$tpl->assign('payFaild',1);
					}else{
						$tpl->assign('payFaild',0);
					}
				}
			
				$orderInfo['orderInfo']['order'] = H::strDosTrip($orderInfo['orderInfo']['order']);
				//货币处理
				//$orderInfo['orderInfo']['order']['amount'] = \Lib\common\Language::priceByCurrency($orderInfo['orderInfo']['order']['amount']);
				//$orderInfo['orderInfo']['order']['logisticsCosts'] = \Lib\common\Language::priceByCurrency($orderInfo['orderInfo']['order']['logisticsCosts']);
				if(!empty($orderInfo['orderInfo']['order']['dicountOfVIP'])){
					//$orderInfo['orderInfo']['order']['dicountOfVIP'] = \Lib\common\Language::priceByCurrency($orderInfo['orderInfo']['order']['dicountOfVIP']);
				}
				//收件人姓名处理
				$consigneeNameArray = explode('|',$orderInfo['orderInfo']['order']['consigneeName']);
				if(!empty($consigneeNameArray)){
					$orderInfo['orderInfo']['order']['consigneeFirstName'] = $consigneeNameArray[0];
					$orderInfo['orderInfo']['order']['consigneeLastName'] = $consigneeNameArray[1];
				}
				//收件人地址处理
				$consigneeAddrArray = explode('|',$orderInfo['orderInfo']['order']['consigneeAddr']);
				if(!empty($consigneeAddrArray)){
					if(!empty($consigneeAddrArray[0])){
						$orderInfo['orderInfo']['order']['consigneeAddrLine1'] = $consigneeAddrArray[0];
					}
					if(!empty($consigneeAddrArray[1])){
						$orderInfo['orderInfo']['order']['consigneeAddrLine2'] = $consigneeAddrArray[1];
					}
				}
				//电话处理
				//$orderInfo['orderInfo']['order']['consigneeHiddenPhone'] = substr_replace($orderInfo['orderInfo']['order']['consigneePhone'],'***',floor(strlen($orderInfo['orderInfo']['order']['consigneePhone'])/2)-1,3);
				//计划到货时间
				$orderInfo['orderInfo']['order']['endTime'] = date('Y-m-d H:i:s',$orderInfo['orderInfo']['order']['endTime']);
				//支付方式信息
				$payClass = $orderInfo['orderInfo']['order']['payClass'];
				
				\Config\PaymentMethod::initMethod();
				$payMethod = \Config\PaymentMethod::$payAll;
				foreach($payMethod as $k=>$v){
					if($v['key']==$payClass){
						$orderInfo['orderInfo']['order']['payMethod'] = H::strDosTrip($v['Introduction'][SELLER_LANG]);
						$orderInfo['orderInfo']['order']['payName'] = $v['name'][SELLER_LANG];
						break;
					}
				}
				//银行汇款第三方支付信息
				if($payClass=='yhhk' && !empty($orderInfo['orderInfo']['order']['cardType']) && $orderInfo['orderInfo']['order']['cardType']!='MILANOOBANK' && $orderInfo['orderInfo']['order']['cardType']!='jpanBankTransfer' &&  !empty($orderInfo['orderInfo']['order']['ordersPayDetails']) && !empty($orderInfo['orderInfo']['order']['pamentToken'])){
					$cardType = $orderInfo['orderInfo']['order']['cardType'];
					$payBankCountry = substr($cardType,9,2);
					if(isset(\Config\PaymentMethod::$bankTransfer['worldPay'][$payBankCountry])){
						$bankTransferInfo = \Config\PaymentMethod::$bankTransfer['worldPay'][$payBankCountry]['bankInfo'];
						//处理reference
						if($orderInfo['orderInfo']['order']['ordersPayDetails'] != 'payWrong'){
							$detailsArray = explode('|', $orderInfo['orderInfo']['order']['ordersPayDetails']);
							$payCurreny = '';
							$payAmount = '';
							$reference = '';
							if(!empty($detailsArray)){
								foreach($detailsArray as $v){
									$vArray = array();
									$vArray = explode(':',$v);
									if(isset($vArray[0]) && isset($vArray[1])){
										if($vArray[0]=='CurrencyCode'){
											$payCurreny = $vArray[1];
										}
										if($vArray[0]=='amount'){
											$payAmount = $vArray[1];
										}
									}
								}
							}
							$reference = $orderInfo['orderInfo']['order']['pamentToken'];
							if(!empty($bankTransferInfo)){
								$tpl->assign('bankTransferTag',1);
								$tpl->assign('reference',$reference);
								$tpl->assign('payAmount',$payAmount);
								$tpl->assign('payCurreny',$payCurreny);
								$tpl->assign('yhhkPayCurreny',\Config\Currency::$currencyTranslations[$payCurreny]['Currency']);
								$tpl->assign('bankTransferInfo',$bankTransferInfo);
							}
						}
					}
				}elseif($payClass=='yhhk' && $orderInfo['orderInfo']['order']['cardType']=='jpanBankTransfer'){
					//日本银行汇款
					$tpl->assign('bankTransferTag',1);
					$tpl->assign('jpanBankTransfer',1);
					$bankTransferInfo = \Config\PaymentMethod::$bankTransfer['jpanBankTransfer']['bankInfo'];
					$tpl->assign('bankTransferInfo',$bankTransferInfo);
					
				}elseif($payClass=='yhhk' && (empty($orderInfo['orderInfo']['order']['cardType']) || $orderInfo['orderInfo']['order']['cardType']!='MILANOOBANK')){
					//wp支付失败
					header('Location:'.\Helper\ResponseUtil::rewrite(array('url'=>'?module=shop&action=Payment&id='.$orderInfo['orderInfo']['order']['ordersId'],'isxs'=>'no')));
					exit();
				}
				//获取国家列表
				$country = new \Model\CountryList ();
				$countryList = $country->getCountryList ( array ('cr.lang' => SELLER_LANG ) );
				if(!empty($countryList) && $countryList['code']==0){
					$orderInfo['orderInfo']['order']['countryName'] = $countryList['counties'][$orderInfo['orderInfo']['order']['stateId']];
				}
				
				//先记录totalprice
				$orderInfo['orderInfo']['order']['totalPay'] = $orderInfo['orderInfo']['order']['amount']+$orderInfo['orderInfo']['order']['logisticsCosts'];
				
				//商品总价处理，加上促销的钱
				if(!empty($orderInfo['orderInfo']['order']['dicountOfVIP'])){
					$orderInfo['orderInfo']['order']['amount']+=$orderInfo['orderInfo']['order']['dicountOfVIP'];
				}
				if(!empty($orderInfo['orderInfo']['order']['dicountOfDropship'])){
					$orderInfo['orderInfo']['order']['amount']+=$orderInfo['orderInfo']['order']['dicountOfDropship'];
				}
				
			}
			
			//处理订单商品
			if(!empty($orderInfo['orderInfo']['productList'])){
				$orderInfo['orderInfo']['productList'] = H::strDosTrip($orderInfo['orderInfo']['productList']);
				//foreach($orderInfo['orderInfo']['productList'] as $key=>$val){
					//货币处理
				//	$orderInfo['orderInfo']['productList'][$key]['unitPrice'] = \Lib\common\Language::priceByCurrency($val['unitPrice'],$currencyCode);
				//	$orderInfo['orderInfo']['productList'][$key]['totalPrice'] = \Lib\common\Language::priceByCurrency($val['totalPrice'],$currencyCode);
				//	
				//}
			}
			//处理折扣
			$shippingDiscount = false;
			$shipingAmount  = 0;
			$shipingAmount += $orderInfo['orderInfo']['order']['logisticsCosts'];
			if(!empty($orderInfo['orderInfo']['discountList'])){
				$orderInfo['orderInfo']['discountList'] = H::strDosTrip($orderInfo['orderInfo']['discountList']);
				foreach($orderInfo['orderInfo']['discountList'] as $k=>$v){
					//$orderInfo['orderInfo']['discountList'][$k]['discount_Amount'] = \Lib\common\Language::priceByCurrency($v['discount_Amount']);
					if($v['couponType']==3){
						//$orderInfo['orderInfo']['order']['logisticsCosts'] += $orderInfo['orderInfo']['discountList'][$k]['discount_Amount'];
						$shipingAmount += $orderInfo['orderInfo']['discountList'][$k]['discount_Amount'];
						$shippingDiscount = true;
					}elseif($v['couponType']!=98 && $v['couponType']!=99){
						$orderInfo['orderInfo']['order']['amount'] += $orderInfo['orderInfo']['discountList'][$k]['discount_Amount'];
					}
				}
			}
			
			//处理运费强制折扣
			if(!empty($orderInfo['orderInfo']['order']['logisticsCosts']) && !$shippingDiscount ){
				if($orderInfo['orderInfo']['order']['logistics']=='Standard'){
					$old_priceTotal = round($orderInfo['orderInfo']['order']['logisticsCosts'] / 0.6,2);
					$shipping_off = '40';
				}else{
					$old_priceTotal = round($orderInfo['orderInfo']['order']['logisticsCosts'] / 0.5,2);
					$shipping_off = '50';
				}
				$tpl->assign('shippingDiscount',$shippingDiscount);
				$tpl->assign('old_priceTotal',$old_priceTotal);
				$tpl->assign('shipping_off',$shipping_off);
			}
		}
		
		//订单货币，不能随语言改变
		$tpl->assign('Currency',\Config\Currency::$currencyTranslations[$orderInfo['orderInfo']['order']['currencyCode']]['Currency']);
		
		$tpl->assign('shipingAmount',$shipingAmount);//运费
		$tpl->assign('quickPay',$quickPay);//快速支付标识
		$tpl->assign('paySuccessTag',1);
		$tpl->assign('orderInfo',$orderInfo['orderInfo']);
		
		$tpl->assign('shopping_process',1); 
		if($orderInfo['orderInfo']['order']['ordersPay']==1){
			$tpl->assign('pay_succeed',1);
		}
		$tpl->display('pay_success.htm');
	}
}