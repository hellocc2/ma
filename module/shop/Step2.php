<?php
namespace Module\Shop;

use helper\ResponseUtil as Rewrite;

/**
 * 支付流程第一步
 * @Jiang Lin<jianglin@mialnoo.com>
 *
 */
class Step2 extends \Lib\common\Application {
	
	function __construct() {
		global $tpl;
		
		
		
		$tpl = \Lib\common\Template::getSmarty();
		
		//检查是否登录
		$this->memberId = \Helper\CheckLogin::getMemberID();
		
		\helper\FormAuth::auth('', Rewrite::rewrite(array('url' => '?module=shop&action=Step1', 'isxs' => 'no')));
		$mem = \Lib\Cache::init();
		$this->cacheKey = $_COOKIE['CartId'];
		//获取传递参数
		$params_all = \Helper\RequestUtil::getParams();
		$shoppingProcess = new \Model\ShoppingProcess();
		$order = $mem->get($this->cacheKey . 'order');
		
		if(!empty($params_all->logistics_key) && !empty($order)) {
			
			//根据参数重新计算订单
			$order = \Helper\ReData::getOrder($order['data'], $order, array('logistics_key' => $params_all->logistics_key, 'memberId' => $this->memberId, 'cacheKey' => $this->cacheKey));
			
			//返回的订单为空时，跳回购物车
			if(empty($order['shoppingCart'])) {
				header("Location:" . Rewrite::rewrite(array('url' => '?module=shop&action=Cart', 'isxs' => 'no')));
			}
			
			//申请生成订单的请求
			$orderRequest = new \stdClass();
			
			$order['shippingAddress'] = \Helper\ResponseUtil::formatArrSpe($order['shippingAddress']);
			$orderArray = array();
			$orderArray['memberId'] = $this->memberId;
			$orderArray['stateId'] = $order['shoppingCart']['countryId'];
			$orderArray['consigneeName'] = $order['shippingAddress']['consigneeName'];
			if(SELLER_LANG=='ja-jp' && !empty($order['shippingAddress']['consigneeNameJa'])){
				$orderArray['consigneeNameJa'] = $order['shippingAddress']['consigneeNameJa'];
			}
			$orderArray['consigneeGender'] = $order['shippingAddress']['consigneeGender'];
			$orderArray['consigneePostalcode'] = $order['shippingAddress']['consigneePostalcode'];
			$orderArray['consigneeAddr'] = $order['shippingAddress']['consigneeAddr'];
			$orderArray['consigneePhone'] = $order['shippingAddress']['consigneePhone'];
			$orderArray['consigneeCity'] = $order['shippingAddress']['consigneeCity'];
			$orderArray['consigneeUrbanAreas'] = $order['shippingAddress']['memberUrbanAreas'];
			$orderArray['consigneeEmail'] = $_SESSION[SESSION_PREFIX . "MemberEmail"];
			$orderArray['logistics'] = $order['shoppingCart']['expressType'];
			$orderArray['logisticsCosts'] = $order['shoppingCart']['freight'];
			if($order['shoppingCart']['priceUnit'] == 'JPY') {
				$orderArray['amount'] = round($order['shoppingCart']['cartPriceTotal'] - $order['shoppingCart']['cartPriceCouponTotal'] - $order['shoppingCart']['cartPriceMemberTotal'] - $order['shoppingCart']['cartPriceDropshipTotal']);
			} else {
				$orderArray['amount'] = $order['shoppingCart']['cartPriceTotal'] - $order['shoppingCart']['cartPriceCouponTotal'] - $order['shoppingCart']['cartPriceMemberTotal'] - $order['shoppingCart']['cartPriceDropshipTotal'];
			}
			if($orderArray['amount'] < 0) {
				$orderArray['amount'] = 0;
			}
			$orderArray['remarks'] = $params_all->remarks;
			$orderArray['lang'] = $order['shoppingCart']['languageCode'];
			$orderArray['currencyCode'] = $order['shoppingCart']['priceUnit'];
			$orderArray['addTime'] = time();
			$orderArray['viewStock'] = $order['shoppingCart']['cartStockDay'];
			$orderArray['expressTime'] = $order['shoppingCart']['max_postTime'];
			$orderArray['endTime'] = time() + (($order['shoppingCart']['cartStockDay'] + $orderArray['expressTime']) * 24 * 3600);
			$orderArray['dicountOfVIP'] = $order['shoppingCart']['cartPriceMemberTotal'];
			$orderArray['dicountOfDropship'] = $order['shoppingCart']['cartPriceDropshipTotal'];
			$orderArray['payClass'] = '';
			$orderArray['ordersUserIp'] = \Helper\RequestUtil::getClientIp();
			if(isset($_COOKIE['PromotionURL'])) {
				$orderArray['promotionURL'] = $_COOKIE['PromotionURL'];
			}
			if(isset($_COOKIE['WebsiteURL'])) {
				$orderArray['websiteURL'] = $_COOKIE['WebsiteURL'];
			}
			
			$orderRequest->order = $orderArray;
			
			$discountList = array();
			
			//针对非折扣吗类型的免运费，也就是促销类型的免运费
			if(isset($order['shoppingCart']['freightOtherSub'])){
				$discountList[] = array('discountName' => '非折扣券免邮', 'libkey' => 'NoConponFreeShipping', 'discount_Amount' => $order['shoppingCart']['freightOtherSub'], 'couponType' => '99');
			}
			
			
			foreach($order['shoppingCart']['productCarts'] as $key => $product) {
				unset($product['categoryName']);
				unset($product['promotion']['promotionName']);
				$order['shoppingCart']['productCarts'][$key] = $product;
			}
			
			
			
			if(!empty($order['shoppingCart']['coupon'])) {
				$coupon = $order['shoppingCart']['coupon'];
				
				$discountList[] = array('discountName' => $coupon['name'], 'libkey' => $coupon['libkey'], 'discount_Amount' => $coupon['costs'], 'couponType' => $coupon['discountWay']);
				
			}
			if(!empty($discountList)){
				$orderRequest->discountList = $discountList;
			}
			
			$orderRequest->productList = $order['shoppingCart']['productCarts'];
			
			//		$orderRequest['discountList'] = $order['shoppingCart']['coupons'];
			
			$orderArr = $shoppingProcess->CreatOrder(array('json' => json_encode($orderRequest)));
			
			if(isset($orderArr['orderId'])) {
				\Helper\ReData::emptyOrder($this->cacheKey);
				
				//#################写入IP记录st#################
				/*
				$fileName = ROOT_PATH.'/data/log/'.date('Ym').'_noip_serverInfo.log';
				$title = date('Y-m-d H:i:s').' SERVER info record:';
				$orderRecord = 'orderId:'.$orderArr['orderId'];
				$handle = fopen($filename, 'a+');
				if($handle!==false){
					fwrite($handle, $title."\n");
					fwrite($handle, $orderRecord."\n");
					fwrite($handle, var_export($_SERVER,true)."\n------END------\n\n");
					fclose($handle);
				}else{
					$f = '';
					$serverInfo = $title."\n".$orderRecord."\n".var_export($_SERVER,true)."\n------END------\n\n";
					if(file_exists($fileName)){
						$f = file_get_contents($fileName);
					}
					$f .= $serverInfo;
					file_put_contents($fileName,$f);
				}
				*/
				//#################写入IP记录end#################
				
				header("Location:" . Rewrite::rewrite(array('url' => '?module=shop&action=Payment&id=' . $orderArr['orderId'], 'isxs' => 'no')));
				exit();
			}else{
				\Helper\ErrorTip::setError(\LangPack::$items['fwcc']);
				header("Location:" . Rewrite::rewrite(array('url' => '?module=shop&action=Step1', 'isxs' => 'no')));
				exit;
			}
		
		} else {
			
			\Helper\ErrorTip::setError(\LangPack::$items['cart_select_shipping_method']);
			$url = Rewrite::rewrite(array('url' => '?module=shop&action=Step1', 'isxs' => 'no'));
			header("Location:" . $url);
		}
	}

}