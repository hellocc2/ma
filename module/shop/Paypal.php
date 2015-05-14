<?php
namespace Module\Shop;

use helper\ResponseUtil as Rewrite;
/**
 * 快速支付
 * @Jiang Lin<jianglin@mialnoo.com>
 *
 */
class Paypal extends \Lib\common\Application {
	
	function __construct() {
		
		$params_all = \Helper\RequestUtil::getParams();
		$mem = \Lib\Cache::init();
		$this->cacheKey = $_COOKIE['CartId'];
		$this->memberId = \Helper\CheckLogin::getNoReMemberId();
		//快速支付接口
		$paypalExpress = new \Lib\_3rd\paypal\PaypalExpress();
		
		if(!isset($params_all->params['act'])) {
			$params_all->params['act'] = '';
		}
		
		if(isset($params_all->act)) {
			$params_all->params['act'] = $params_all->act;
		}
		
		switch($params_all->params['act']) {
			case 'paypal':
				
				//获取购物车数据
				$data = \Helper\ShoppingCart::getCart();
				
				$shippingCart = $data['shoppingCart'];
				$shippingAmt = 100;
				$Amt = $shippingCart['cartPriceTotal'] - $shippingCart['cartPriceCouponTotal'] - $shippingCart['cartPriceMemberTotal'];
				$paymentType = 'Sale';
				$returnURL = Rewrite::rewrite(array('url' => '?module=shop&action=Paypal', 'isxs' => 'no'));
				$cancelURL = Rewrite::rewrite(array('url' => '?module=shop&action=Cart', 'isxs' => 'no'));
				$currency = $shippingCart['priceUnit'];
				
				$lc = '';
				switch($shippingCart['languageCode']) {
					case 'ja-jp':
						$lc = "jp";
						break;
					case 'fr-fr':
						$lc = "fr";
						break;
					case 'es-sp':
						$lc = "es";
						break;
					case 'de-ge':
						$lc = "de";
						break;
					case 'it-it':
						$lc = "it";
						break;
					default:
						$lc = "us";
				}
				
				if($currency == 'JPY') {
					$Amt = round($Amt);
					$shippingAmt = round($shippingAmt);
				}
				
				$nvpstr = "&Amt=" . ($Amt + $shippingAmt) . "&PAYMENTACTION=" . $paymentType . "&ReturnUrl=" . $returnURL . "&CANCELURL=" . $cancelURL . "&HDRIMG=https://www.milanoo.com/image/default/logo.jpg" . "&CURRENCYCODE=" . $currency . "&SHIPPINGAMT={$shippingAmt}&ITEMAMT=" . $Amt . "&LOCALECODE=" . $lc . "&DESC=MILANOO|PAYPALEXPRESS";
				
				$resArray = $paypalExpress->hash_call("SetExpressCheckout", $nvpstr);
				
				if(strtoupper($resArray["ACK"]) == 'SUCCESS') {
					$token = urldecode($resArray["TOKEN"]);
					$payPalURL = PAYPAL_URL . $token;
					$mem->set($this->cacheKey . 'paypalEX', $data);
					header("Location: " . $payPalURL);
					exit();
				}
				break;
			case 'POSTDATA':
				$order = $mem->get($this->cacheKey . 'order');
				
				if(!empty($params_all->logistics_key) && !empty($order)) {
					
					//根据参数重新计算订单
					$order = \Helper\ReData::getOrder($order['data'], $order, array('logistics_key' => $params_all->logistics_key, 'memberId' => $this->memberId, 'cacheKey' => $this->cacheKey));
					
					//返回的订单为空时，跳回购物车
					if(empty($order['shoppingCart'])) {
						header("Location:" . Rewrite::rewrite(array('url' => '?module=shop&action=Cart', 'isxs' => 'no')));
					}
					
					$memberNewId = $this->memberId;
					if(empty($memberNewId)) {
						$member = new \Model\Member();
						$memArr = $member->addMember(array('email' => $order['shippingAddress']['consigneeEmail'], 'pw' => '', 'webSiteId' => 1, 'cookieId' => $this->cacheKey, 'languageCode' => SELLER_LANG));
						$memberNewId = $memArr['id'];
					}
					
					$orderRequest = new \stdClass();
					$orderArray = array();
					$orderArray['memberId'] = $memberNewId;
					$orderArray['stateId'] = $order['shoppingCart']['countryId'];
					$orderArray['consigneeName'] = $order['shippingAddress']['consigneeName'];
					$orderArray['consigneeGender'] = 1;
					$orderArray['consigneePostalcode'] = $order['shippingAddress']['consigneePostalcode'];
					$orderArray['consigneeAddr'] = $order['shippingAddress']['consigneeAddr'];
					$orderArray['consigneePhone'] = $order['shippingAddress']['consigneePhone'];
					$orderArray['consigneeCity'] = $order['shippingAddress']['consigneeCity'];
					$orderArray['consigneeUrbanAreas'] = $order['shippingAddress']['consigneeUrbanAreas'];
					$orderArray['consigneeEmail'] = $order['shippingAddress']['consigneeEmail'];
					$orderArray['logistics'] = $order['shoppingCart']['expressType'];
					$orderArray['logisticsCosts'] = $order['shoppingCart']['freight'];
					if($order['shoppingCart']['priceUnit'] == 'JPY') {
						$orderArray['amount'] = round($order['shoppingCart']['cartPriceTotal'] - $order['shoppingCart']['cartPriceCouponTotal'] - $order['shoppingCart']['cartPriceMemberTotal'] - $order['shoppingCart']['cartPriceDropshipTotal']);
					} else {
						$orderArray['amount'] = $order['shoppingCart']['cartPriceTotal'] - $order['shoppingCart']['cartPriceCouponTotal'] - $order['shoppingCart']['cartPriceMemberTotal'] - $order['shoppingCart']['cartPriceDropshipTotal'];
					}
					if($params_all->isremarks == 1) {
						$orderArray['remarks'] = $params_all->remarks;
					}
					
					$orderArray['lang'] = $order['shoppingCart']['languageCode'];
					$orderArray['currencyCode'] = $order['shoppingCart']['priceUnit'];
					$orderArray['addTime'] = time();
					$orderArray['viewStock'] = $order['shoppingCart']['cartStockDay'];
					$orderArray['expressTime'] = $order['shoppingCart']['max_postTime'];
					$orderArray['endTime'] = time() + (($order['shoppingCart']['cartStockDay']+$orderArray['expressTime']) * 24 * 3600);
					$orderArray['dicountOfVIP'] = $order['shoppingCart']['cartPriceMemberTotal'];
					$orderArray['dicountOfDropship'] = $order['shoppingCart']['cartPriceDropshipTotal'];
					$orderArray['payClass'] = 'paypal';
					$orderArray['ordersUserIp'] = \Helper\RequestUtil::getClientIp();
					if(isset($_COOKIE['PromotionURL'])) {
						$orderArray['promotionURL'] = $_COOKIE['PromotionURL'];
					}
					if(isset($_COOKIE['WebsiteURL'])) {
						$orderArray['websiteURL'] = $_COOKIE['WebsiteURL'];
					}
					$orderRequest->order = $orderArray;
					foreach($order['shoppingCart']['productCarts'] as $key => $product) {
						unset($product['categoryName']);
						unset($product['promotion']['promotionName']);
						$order['shoppingCart']['productCarts'][$key] = $product;
					}
					$orderRequest->productList = $order['shoppingCart']['productCarts'];
					
					$discountList = array();
					
					//针对非折扣吗类型的免运费，也就是促销类型的免运费
					if(isset($order['shoppingCart']['freightOtherSub'])){
						$discountList[] = array('discountName' => '非折扣券免邮', 'libkey' => 'NoConponFreeShipping', 'discount_Amount' => $order['shoppingCart']['freightOtherSub'], 'couponType' => '99');
					}
					
					if(!empty($order['shoppingCart']['coupon'])) {
						$coupon = $order['shoppingCart']['coupon'];
						
						$discountList[] = array('discountName' => $coupon['name'], 'libkey' => $coupon['libkey'], 'discount_Amount' => $coupon['costs'], 'couponType' => $coupon['discountWay']);
						
					}
					
					if(!empty($discountList)){
						$orderRequest->discountList = $discountList;
					}
					
					$shoppingProcess = new \Model\ShoppingProcess();
					
					$orderArr = $shoppingProcess->CreatOrder(array('json' => json_encode($orderRequest)));
					
					$shippingAdressArr = $shoppingProcess->getAddressList(array('cr.memberId' => $memberNewId));
					
					if(count($shippingAdressArr['address']) == 0) {
						$webserviceParam = array();
						$webserviceParam['mc.memberId'] = $memberNewId;
						$webserviceParam['mc.consigneeName'] = $orderArray['consigneeName'];
						$webserviceParam['mc.consigneePhone'] = $orderArray['consigneePhone'];
						$webserviceParam['mc.consigneeStateId'] = $orderArray['stateId'];
						$webserviceParam['mc.consigneePostalcode'] = $orderArray['consigneePostalcode'];
						$webserviceParam['mc.consigneeAddr'] = $orderArray['consigneeAddr'];
						$webserviceParam['mc.consigneeCity'] = $orderArray['consigneeCity'];
						$webserviceParam['mc.memberUrbanAreas'] = $orderArray['consigneeUrbanAreas'];
						$webserviceParam['mc.consigneeGender'] = $orderArray['consigneeGender'];
						
						$resultOfAddress = $shoppingProcess->setAddress($webserviceParam);
					}
					
					if(!isset($orderArr['orderId'])) {
						header("Location: " . Rewrite::rewrite(array('url' => '?module=shop&action=Cart', 'isxs' => 'no')));
						exit();
					}
					//生成订单后删除缓存
					\Helper\ReData::emptyOrder($this->cacheKey);
					if(empty($this->memberId)) {
						$mem->set($this->cacheKey . 'orderId', $orderArr['orderId']);
						$mem->set($this->cacheKey . 'memberId', $memberNewId);
					}
					
					$orderInfo = $shoppingProcess->GetOrderById(array('cr.ordersId' => $orderArr['orderId'], 'cr.lang' => $order['shoppingCart']['languageCode'], 'cr.memberId' => $memberNewId));
					
					
					$order = $orderInfo['orderInfo']['order'];
					
					//快速支付参数
					$ip = \Helper\RequestUtil::getClientIp();
					$Amt = round($order['amount'], 2);
					$shippingCost = round($order['logisticsCosts'], 2);
					
					$amountTotal = $Amt + $shippingCost;
					
					if($order['currencyCode'] == 'JPY') {
						$amountTotal = round($amountTotal);
					}
					
					$nvpstr = '&TOKEN=' . urlencode($_REQUEST['token']) . '&PAYERID=' . urlencode($_REQUEST['PayerID']) . '&PAYMENTACTION=Sale&AMT=' . $amountTotal . '&CURRENCYCODE=' . $order['currencyCode'] . '&IPADDRESS=' . $ip . '&INVNUM=' . $order['ordersCid'];
					
					$resArray = $paypalExpress->hash_call("DoExpressCheckoutPayment", $nvpstr);
					$ack = strtoupper($resArray["ACK"]);
					if($ack == "SUCCESS") {
						if(strtoupper($resArray['PAYMENTSTATUS']) == 'COMPLETED') {
							$orderInfo['orderInfo']['order']['ordersPay'] = 1;
							$paytime = time();
							$RemarksParpal = 'Api Set Express Checkout. NO.' . $_REQUEST['token'];
							$OrdersPayDetails = 'Payment:paypal|' . 'CurrencyCode:' . $order['currencyCode'] . '|' . 'amount:' . ($Amt + $shippingCost) . '|' . 'Remarks:' . $RemarksParpal . '|' . 'time:' . $paytime;
							$shoppingProcess->updateOrder(array('cr.ordersId' => $order['ordersId'], 'cr.ordersPay' => 1, 'cr.ordersPayDetails' => $OrdersPayDetails, 'cr.cardType' => 'EXPRESS CHECKOUT', 'cr.pamentToken' => $_REQUEST['token'], 'cr.payTime' => $paytime, 'cr.payClass' => 'paypal'));
						}
					}
					$title = 'Email_orderOK';
					if($orderInfo['orderInfo']['order']['ordersPay'] == 1) {
						$title = 'Email_CKOK';
					}
					$emailAll = array('lang' => SELLER_LANG, 'email' => $orderInfo['orderInfo']['order']['consigneeEmail'], 'products' => $orderInfo['orderInfo']['productList'], 'Orders' => $orderInfo['orderInfo'], 'emailtitle' => $title, 'theme' => THEME . 'default/email/order_achieve.htm');
					\Helper\Stomp::SendEmail($emailAll);
					
					
					header("Location: " . Rewrite::rewrite(array('url' => '?module=shop&action=Achieve&id=' . $order['ordersId'], 'isxs' => 'no')));
					exit();
				} else {
					header("Location: " . Rewrite::rewrite(array('url' => '?module=shop&action=Cart', 'isxs' => 'no')));
					exit();
				}
				break;
				case 'editShipping':
					$order = $mem->get($this->cacheKey . 'order');
					$order['shippingAddress']['consigneeName'] =  implode("|", $params_all->consigneeName);
					$order['shippingAddress']['consigneePostalcode'] = $params_all->consigneePostalcode;
					$order['shippingAddress']['consigneeAddr'] = implode("|", $params_all->consigneeAddr);
					$order['shippingAddress']['consigneePhone'] = $params_all->countryCode.' '.$params_all->consigneePhone;
					$order['shippingAddress']['consigneeCity'] = $params_all->consigneeCity;
					$order['shippingAddress']['consigneeUrbanAreas'] = $params_all->consigneeUrbanAreas;
					$order['shippingAddress']['consigneeStateId'] = $params_all->consigneeStateId;
					if(isset($params_all->consigneeNameJa)) {
						$order['shippingAddress']['consigneeNameJa'] = implode('|', $params_all->consigneeNameJa);
					}
					$order = \Helper\ReData::getOrder($order['data'], $order, array('memberId' => $this->memberId,'cacheKey' => $this->cacheKey));
					$tpl = \Lib\common\Template::getSmarty();
					$formAuthMD5 = \helper\FormAuth::createAuthCode();
					$tpl->assign('formAuth', $formAuthMD5);
					$tpl->assign('order', $order);
					$tpl->display('paypal_order.htm');
					break;
			default:
				$nvpstr = "&TOKEN=" . urlencode($_REQUEST['token']);
				
				$resArray = $paypalExpress->hash_call("GetExpressCheckoutDetails", $nvpstr);
				
				if(strtoupper($resArray['ACK']) != 'SUCCESS') {
					header("Location: " . Rewrite::rewrite(array('url' => '?module=shop&action=Cart', 'isxs' => 'no')));
					exit();
				}
				
				//地址设定
				$shippingAdress = array();
				$shippingAdress['memberId'] = $this->memberId;
				$shippingAdress['consigneeName'] = $resArray['FIRSTNAME'] . '|' . $resArray['LASTNAME'];
				$shippingAdress['consigneePostalcode'] = $resArray['SHIPTOZIP'];
				
				$shippingAdress2 = isset($resArray['SHIPTOSTREET2'])?$resArray['SHIPTOSTREET2']:'';
				$shippingAdress['consigneeAddr'] = $resArray['SHIPTOSTREET'] . '|' . $shippingAdress2;
				$shippingAdress['consigneePhone'] = $resArray['PHONENUM'];
				$shippingAdress['consigneeCity'] = $resArray['SHIPTOCITY'];
				$shippingAdress['consigneeUrbanAreas'] = $resArray['SHIPTOSTATE'];
				if(isset($_SESSION[SESSION_PREFIX . "MemberEmail"])) {
					$shippingAdress['consigneeEmail'] = $_SESSION[SESSION_PREFIX . "MemberEmail"];
				} else {
					$shippingAdress['consigneeEmail'] = $resArray['EMAIL'];
				}
				
				$shippingAdress['countryCode'] = $resArray['SHIPTOCOUNTRYCODE'];
				
				$order['shippingAddress'] = $shippingAdress;
				$checkShipping = false;
				if(empty($shippingAdress['consigneeCity']) || empty($shippingAdress['consigneeName'])||empty($shippingAdress['consigneeAddr'])||empty($shippingAdress['consigneePhone'])||empty($shippingAdress['consigneeUrbanAreas'])) {
					$checkShipping = true;
				}
				
				//获取订单缓存
				$data = $mem->get($this->cacheKey . 'paypalEX');
				
				$order = \Helper\ReData::getOrder($data, $order, array('memberId' => $this->memberId, 'countryCode' => $resArray['SHIPTOCOUNTRYCODE'], 'cacheKey' => $this->cacheKey));
				//根据paypal返回客人国家代码取得对应国家ID
				$country = new \Model\CountryList ();
				$countryList = $country->getCountryList ( array ('cr.lang' => SELLER_LANG ) );
				$orderCountryId = 1;
				foreach($countryList['countriesFlag'] as $countryFlag => $countryId){
					if(strtolower($resArray['SHIPTOCOUNTRYCODE']) == strtolower($countryFlag)){
						$orderCountryId = $countryId;
						break;
					}
				}
				$order['shippingAddress']['consigneeStateId'] = $orderCountryId;
				
				if(empty($order)) {
					header("Location: " . Rewrite::rewrite(array('url' => '?module=shop&action=Cart', 'isxs' => 'no')));
					exit();
				}
				
				$tpl = \Lib\common\Template::getSmarty();
				$formAuthMD5 = \helper\FormAuth::createAuthCode();
				$tpl->assign('formAuth', $formAuthMD5);
				$tpl->assign('order', $order);
				$tpl->assign('checkShipping', $checkShipping);
				$tpl->display('paypal_order.htm');
				break;
		}
	
	}
}