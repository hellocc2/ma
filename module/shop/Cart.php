<?php
namespace Module\Shop;
use Helper\RequestUtil as R;
use Helper\ResponseUtil as rew;

/**
 * 商品详情页
 * @Jerry Yang<yang.tao.php@gmail.com>
 *
 */
class Cart extends \Lib\common\Application{	
	function __construct(){
		global $tpl ;
		$tpl = \Lib\common\Template::getSmarty ();	
		unset($_SESSION['expressType']);
		//$_SESSION['countryId'] = "";
		//$_SESSION['expressType'] = "";
		//setcookie('milanoo_cc',$result['categoryBreadcrumbNavigation']['categoryId'],time()+3600,'/');
		if(isset($_COOKIE['milanoo_cc'])){
			$continue_url = rew::rewrite(array('url'=>'?module=thing&action=glist&class='.$_COOKIE['milanoo_cc'],'isxs'=>'no','seo'=>$_COOKIE['milanoo_cn']));
		}else{
			$continue_url = rew::rewrite(array('url'=>'?module=index','isxs'=>'no'));
		}
		if($_POST) {
			$productId = R::getParams('ProductsId');
			$itemData = array(
				'productId'=>intval($productId) == 0 ? 1 : $productId,
				'isGift'=>0,
			);
			/**
			 * 是否选择自定义参数
			 */
			$customFlag = false;
			/**
			 * 自定义参数值
			 */
			$customName = '';
			/**
			 * 销售属性
			 */
			
			$inventoryPropertyArr = array();
			$CustomAttributes_array = R::getParams('CustomAttributes_array');
			$customType = R::getParams('customType');
			if(!empty($CustomAttributes_array) && is_array($CustomAttributes_array)){
				foreach ($CustomAttributes_array as $key=>$v){
					if($v == 'custom' || $v == 9392){
						$customName = $v;
					}
					if(($v == 'custom' || $v == 9392) && $customType==1){
						$customFlag = true;
					}
					if(empty($v) || $v == 'please'){
						//出错
						\Helper\Js::alertForward('noSelect');
					}
					$inventoryPropertyArr[] = array(
						'propertyId'=>$key,
						'propertyValue'=>$v,
					);
				}
			}
			$itemData['inventoryPropertyArr'] = count($inventoryPropertyArr) == 0 ? '' : json_encode($inventoryPropertyArr);
			
			if(($customName == 'custom' || $customName == 9392) && !$customFlag){
				\Helper\Js::alertForward('noSelect');
			}
			
			/**
			 * 自定义参数
			 */
			
			if($customFlag){
				$customArgs = array(
					'unit'=>R::getParams('units'),
					'customArgsArr'=>array(),
				);
				$Customszie = R::getParams('Customszie');
				if(!empty($Customszie) && is_array($Customszie)){
					foreach ($Customszie as $key=>$v){
						if(empty($v)){
							\Helper\Js::alertForward('noSelect');
						}
						$karr = explode('__',$key);
						if(isset($karr[2]) && strtolower($karr[2]) == 'input' && intval($v) == 0){
							\Helper\Js::alertForward('noSelect');
						}
						$customArgs['customArgsArr'][] = array(
							'customKey'=>$karr[0],
							'argsValue'=>$v,
							'sort'=>$karr[1],
						);	
					}
				}
				$customArgs = json_encode($customArgs);
			}else{
				$customArgs = '';
			}

			$itemData['customArgs'] = $customArgs;
			/**
			 * 定制参数
			 */
			$customPropertyIds = "";
			$ProductsCustomArray = R::getParams('ProductsCustomArray');
			$customProperty = array();
			if(!empty($ProductsCustomArray) && is_array($ProductsCustomArray)){
				foreach ($ProductsCustomArray as $key=>$v){
					if(intval($v)==0){
							\Helper\Js::alertForward('noSelect');
					}
					$customProperty[] = $v;
				}
				$customPropertyIds = implode(",",$customProperty);
			}
			$itemData['customPropertyIds'] = $customPropertyIds;
			/**
			 * 购物车操作类型
			 */
			$act = R::getParams('act');
			switch ($act){
				case 'copy':
					$itemData['buyNum'] = R::getParams('num');
					$itemData['copyCartId'] = R::getParams('cartid');
					$data = \Helper\ShoppingCart::addItem($itemData);
					header('Location:'.rew::rewrite(array('url'=>'?module=shop&action=Cart','isxs'=>'no')));
				break;
				case 'addition':
					$itemData['buyNum'] = R::getParams('num');
					$data = \Helper\ShoppingCart::addItem($itemData);
					header('Location:'.rew::rewrite(array('url'=>'?module=shop&action=Cart','isxs'=>'no')));
				break;
				case 'gift':
					$itemData['giftId'] = '';
					$itemData['isGift'] = 1;		
				break;
				case 'addgift':
					$giftId = R::getParams('giftid');
					//$productsId = R::getParams('proid');
					$itemData['isGift'] = 1;
					$itemData['giftId'] = $giftId;
					$itemData['buyNum'] = 1;
					$data = \Helper\ShoppingCart::addItem($itemData);
					header('Location:'.rew::rewrite(array('url'=>'?module=shop&action=Cart','isxs'=>'no')));
				break;
				case 'edit':
					$itemData['buyNum'] = R::getParams('num');
					$itemData['cartId'] = R::getParams('cartid');
					$data = \Helper\ShoppingCart::editItem($itemData);
					header('Location:'.rew::rewrite(array('url'=>'?module=shop&action=Cart','isxs'=>'no')));
				break;
				case 'shipping':
					$countryId = R::getParams('countryId');
					if(!empty($countryId)){
						$_SESSION['countryId'] = R::getParams('countryId');
					}
					header('Location:'.rew::rewrite(array('url'=>'?module=shop&action=Cart','isxs'=>'no')));
				break;
				case 'coupons':
					$_SESSION['COUPON'] = R::getParams('libkey');
					header('Location:'.rew::rewrite(array('url'=>'?module=shop&action=Cart','isxs'=>'no')));
				break;
				default:
					$itemData['referType'] = R::getParams('referer_type');
					$itemData['referId'] = R::getParams('referer_id');
					$itemData['buyNum'] = R::getParams('num');
					$data = \Helper\ShoppingCart::addItem($itemData);
					//print '<pre>';print_r($data);exit;
					
					/*******************************添加附加商品到购物车*********************************/
					$buytype = R::getParams('buytype');
					if($buytype == 1){
						$addition_products_id_array = R::getParams('addition_products_id_array');
						$Additional_CustomAttributes_array = R::getParams('Additional_CustomAttributes_array');
						$this->addtionalProductsCart($addition_products_id_array,$Additional_CustomAttributes_array,$itemData);
					}
					/*******************************添加附加商品到购物车*********************************/
					
					/*******************************添加礼品到购物车*********************************/
					$giftId = intval(R::getParams('giftId'));
					$giftProductsId = R::getParams('gift');
					$GiftCustomAttributes_array = R::getParams('GiftCustomAttributes_array');
					if(is_array($giftProductsId) && count($giftProductsId) > 0){
						$this->addGift($GiftCustomAttributes_array, $giftId,$giftProductsId);
					}
					/*******************************添加礼品到购物车*********************************/
					/*
					$params_all = R::getParams();
					$types = isset($params_all->params['types']) ? $params_all->params['types'] : '';
					if($types == 'mini'){
						echo "<script>window.parent.saveBag(1)</script>";
						exit;
					}*/
					
					header('Location:'.rew::rewrite(array('url'=>'?module=shop&action=Cart','isxs'=>'no')));
					
			}		
		}else{
			$act = isset($this::$requestParams->params['act']) ? $this::$requestParams->params['act'] : '';
			$cartCookieid = isset($this::$requestParams->params['cartid']) ? $this::$requestParams->params['cartid'] : '';
			switch ($act){
				case 'remove': //删除商品
					$delItemArray = array(
						'cartIds'=>$cartCookieid,
					);
					$data = \Helper\ShoppingCart::delItem($delItemArray);
				break;
				case 'emptyCode':
					unset($_SESSION['COUPON']);
				break;
				case 'empty': //清空购物车
					$emptyCartArray = array(
						'cookieId'=>$_COOKIE['CartId'],
						'memberId'=>isset($_SESSION [SESSION_PREFIX . "MemberId"]) ? $_SESSION [SESSION_PREFIX . "MemberId"] : '',
					);
					$data = \Helper\ShoppingCart::emptyCart($emptyCartArray);
				
				break;
				case 'editnum': //修改商品数量
					$num = $this::$requestParams->params['num'];
					$itemData = array(
						'cartId'=>$cartCookieid,
						'buyNum'=>$num,
					);
					$data = \Helper\ShoppingCart::editItem($itemData);
				break;	
				//default:
				//	$data = \Helper\ShoppingCart::getCart();	
			}
			if(!empty($act)){
				header('Location:'.rew::rewrite(array('url'=>'?module=shop&action=Cart','isxs'=>'no')));
			}
			$data = \Helper\ShoppingCart::getCart();	
		}
		if($data['code'] != 0){
			//header('Location:'.rew::rewrite(array('url'=>'?module=shop&action=Cart','isxs'=>'no')));
		}
		
		//print '<pre>';print_r($data);exit;
		if(isset($data['shoppingCart']['productCarts']) && is_array($data['shoppingCart']['productCarts'])){
			$plist = $data['shoppingCart']['productCarts'];
			$list_cp = array();
			$list = array();
			$gift_list = array();
			$cf = array();
			ksort($plist);

			foreach ($plist as $key=>$v){
					if(!empty($v['copyCartId']) && isset($plist[$v['copyCartId']])){		
					if(!isset($list_cp[$v['productId']])){
						$list_cp[$v['productId']]['son'] = array();
						$list_cp[$v['productId']]['son'][$v['copyCartId']] = $v['copyCartId'];
						unset($list[$v['copyCartId']]);
						$cf[] = $v['copyCartId'];
					}
					$list_cp[$v['productId']]['son'][$key] = $key;
				}else{
					if(!in_array($key,$cf)){
						if($v['isGift'] == 0){
							$list[$key] = $key;
						}else{
							$gift_list[$key] = $key;
						}
					}
				}
			}
		
			$new_array = array();
			foreach ($list_cp as $k=>$v){
				ksort($v['son']);
				foreach ($v['son'] as $vk=>$vv){
					$keys = $vk;
					break;
				}
				$new_array[$keys] = $v;
			}
			foreach ($list as $lk=>$lv){
				$new_array[$lk]=$lv;
			}
			ksort($new_array);
			foreach ($gift_list as $gk=>$gv){
				$new_array[$gk]=$gv;
			}
			//echo '<pre>';var_dump($plist);exit;
			$tpl->assign('cartProductList',$new_array);
			$tpl->assign('productsData',$plist);
			if(!empty($data['shoppingCart']['weight'])){
			$tpl->assign('weight',$data['shoppingCart']['weight']);
			}
		}
		/**
		 * 获取购物车中的最大备货期
		 */
		$max_stock_time = 0;
		if(isset($data['shoppingCart']['productCarts'])){
			foreach ($data['shoppingCart']['productCarts'] as $v){
				if(isset($v['productStockTime']) && $v['productStockTime'] > $max_stock_time){
					$max_stock_time = $v['productStockTime'];
				}
			}
			$tpl->assign('max_stock_time',$max_stock_time);
		}
		/**
		 * 获取国家
		 */
		$countries_obj=new \Model\CountryList ();
		$countriesList = $countries_obj->getCountryList();
		/**
		 * 礼品
		 */
		//print '<pre>';print_r($data);exit;
		if(isset($data['giftInfo']) && is_array($data['giftInfo'])){
			$tpl->assign('giftProductList',$data['giftInfo']);
		}
		
		/**
		 * 获取运费
		 */
		$logistics_cost = 0;
		if(isset($data['shoppingCart']['freightCountList'])){
			$expressType = '';
			if(count($data['shoppingCart']['freightCountList']) == 1){
				$logistics_cost = $data['shoppingCart']['freightCountList'][0]['priceTotal'];
				$expressType = $data['shoppingCart']['freightCountList'][0]['expressType'];
			}else{
				foreach($data['shoppingCart']['freightCountList'] as $freight){
					if(strtolower($freight['expressType']) == 'standard'){
						$logistics_cost = $freight['priceTotal'];
						$expressType = $freight['expressType'];
					}
				}
			}
			//$logistics_cost = $data['shoppingCart']['freight'];
			if(!empty($expressType)){
				if(strtolower($expressType) == 'standard'){
					$old_price = round($logistics_cost / 0.6,2);
					$shipping_off = "40";
				}else{
					$old_price = round($logistics_cost / 0.5,2);
					$shipping_off = "50";
				}
				$tpl->assign('old_price',$old_price);
				$tpl->assign('shipping_off',$shipping_off);
			}
		}

		if(isset($_SESSION [SESSION_PREFIX . "MemberId"])){
			$tpl->assign('login_stauts',1);
		}
		$cartPrice = isset($data['shoppingCart']['cartPriceTotal']) ? $data['shoppingCart']['cartPriceTotal'] : 0;
		$cartPriceCouponTotal = isset($data['shoppingCart']['cartPriceCouponTotal']) ? $data['shoppingCart']['cartPriceCouponTotal'] : 0;
		$cartPriceMemberTotal = isset($data['shoppingCart']['cartPriceMemberTotal']) ? $data['shoppingCart']['cartPriceMemberTotal'] : 0;
		$dropshipperTotal = isset($data['shoppingCart']['cartPriceDropshipTotal']) ? $data['shoppingCart']['cartPriceDropshipTotal'] : 0;
		//echo $cartPrice.'A';$logistics_cost.'B';$cartPriceCouponTotal.'C';$cartPriceMemberTotal.'D';$dropshipperTotal.'E';exit;
		$total = $cartPrice + $logistics_cost - $cartPriceCouponTotal - $cartPriceMemberTotal - $dropshipperTotal;
		
		$orderMinPrice = \config\Language::$order_minPrice;
		
		$minPrice = 0;
		
		if($orderMinPrice[SELLER_LANG]['currency'] == CurrencyCode) {
			$minPrice = $orderMinPrice[SELLER_LANG]['amount'];
		} else {
			$minPrice = \Lib\common\Language::priceByCurrency($orderMinPrice[SELLER_LANG]['amount'], CurrencyCode, $orderMinPrice[SELLER_LANG]['currency']);
		}
		
		$pay_status = 0;
		if($cartPrice >= $minPrice ){
			$pay_status = 1;
		}
		if(SELLER_LANG == 'ru-ru'){
			$orderMinPriceLang = sprintf(\LangPack::$items['cart_minprice'],$minPrice.' '.Currency);
			$orderMinPriceLangTips = sprintf(\LangPack::$items['cart_minprice_tips'],$minPrice.' '.Currency);
		}else{
			$orderMinPriceLang = sprintf(\LangPack::$items['cart_minprice'],Currency.$minPrice);
			$orderMinPriceLangTips = sprintf(\LangPack::$items['cart_minprice_tips'],Currency.$minPrice);
		}
		
		$tpl->assign('orderMinPriceLang',$orderMinPriceLang);
		$tpl->assign('orderMinPriceLangTips',$orderMinPriceLangTips);
		$coupon = '';
		if(isset($data['shoppingCart']['coupon']) && $data['shoppingCart']['coupon']['status'] == 0){
			$coupon = array(
				'name'=>$data['shoppingCart']['coupon']['name'],
				'price'=>$cartPriceCouponTotal,
				'libkey'=>$_SESSION['COUPON'],
				'discountWay'=>$data['shoppingCart']['coupon']['discountWay'],
				'status'=>$data['shoppingCart']['coupon']['status'],
			);
		}
		
		//shop_codeErr
		/**
		 * 登录用的礼券信息
		 */
		if(isset($_SESSION [SESSION_PREFIX . "MemberId"])  && isset($data['coupons'])){
			$couponData = $data['coupons'];
			foreach ($couponData as $key=>$v){
				if($v['status'] == 2 || $v['status'] == 1){
					unset($couponData[$key]);
				}elseif($v['status'] == 3){
					$price2use = isset($v['price2use']) ? $v['price2use'] : 0;
					if($price2use > 0){
						$useMoney = Currency.$v['price2use'];
						$error_msg = sprintf(\LangPack::$items['coupon_error3'],$useMoney);
					}else{
						$error_msg = \LangPack::$items['coupon_error1'];
					}
					$couponData[$key]['msg'] = $error_msg;
				}
			}
			$tpl->assign('couponData',$couponData);
		}
		$cartChange = 0;
		//系统产生的错误
		if(isset($data['shoppingCart']['messages'])){
			$cartChange = 1;
		}
		$tpl->assign('cartChange',$cartChange);
		/**
		 * 附加商品
		 */
		$additionList = '';
		if(isset($data['shoppingCart']['additionalProducts'])){
			$additionList = $data['shoppingCart']['additionalProducts'];
		}
		if(isset($data['shoppingCart']['productCarts'])){
			$productRecommend = array();
			foreach($data['shoppingCart']['productCarts'] as $p){
				if($p['isGift'] == 0){
					$productRecommend[] = $p['productId'];
				}
			}
			if(count($productRecommend) > 0){
				$productRecommendData = array(
					'languageCode'=>SELLER_LANG,
					'productIds'=>implode(',', $productRecommend),
					'num'=>10,		
				);
				$mCart = new \Model\Cart();
				$productRecommendList = $mCart->productsRecommend($productRecommendData);
				if(isset($productRecommendList['code']) && $productRecommendList['code'] == 0 && isset($productRecommendList['products']) && count($productRecommendList['products'])>0){
					foreach($productRecommendList['products'] as $k=> $v){
						if(isset($productRecommendList['products'][$k]['promotionPrice'])){
							$productRecommendList['products'][$k]['productPrice'] = \Lib\common\Language::priceByCurrency($v['promotionPrice']);
						}else{
							$productRecommendList['products'][$k]['productPrice'] = \Lib\common\Language::priceByCurrency($v['productPrice']);
						}
					}
					$tpl->assign('productRecommendList',$productRecommendList['products']);
				}
			}
		}
		
		//print '<pre>';print_r($data);exit;
		if($cartPriceMemberTotal > 0){
			$tpl->assign('cartPriceMemberTotal',$cartPriceMemberTotal);
		}
		if($dropshipperTotal > 0){
			$tpl->assign('dropshipperTotal',$dropshipperTotal);
		}
		$forward = \Helper\RequestUtil::getUrl();
		$checkout_url = rew::rewrite(array('url'=>'?module=shop&action=Step1','isxs'=>'no','protocol'=>'https'));
		$tpl->assign('additionalProducts',$additionList);
		$tpl->assign('orderMinPrice',$minPrice);
		$tpl->assign('continue_url',$continue_url);
		$tpl->assign('pay_status',$pay_status);
		$tpl->assign('pay_forward',$checkout_url);
		$tpl->assign('forward',$forward);
		$tpl->assign('total_cost',$total);
		$tpl->assign('coupon',$coupon);
		$tpl->assign('logistics_cost',$logistics_cost);
		$tpl->assign('countryId',isset($_SESSION['countryId']) ? $_SESSION['countryId'] : $data['shoppingCart']['countryId']);
		$tpl->assign('cartPrice',$cartPrice);
		$tpl->assign('countriesList',$countriesList);
		$tpl->assign('cartData',isset($data['shoppingCart']) ? $data['shoppingCart'] : '');
		$tpl->assign('sitetype','cart');
		$tpl->assign('be_cart',1);
		$tpl->assign('shopping_process',1);
		$tpl->display('shopping_cart.htm');
		return;
	}
	
	
	function addGift($data,$giftId,$giftProductsId){
		$itemData['isGift'] = 1;
		$itemData['giftId'] = $giftId;
		$itemData['buyNum'] = 1;
		$itemData['customArgs'] = '';
		$itemData['customPropertyIds'] = '';
		foreach($giftProductsId as $v){
			$inventoryPropertyArr = array();
			if(isset($data[$v])){
				foreach($data[$v] as $pkey=>$property){
					if($property == 'plaese'){
						break;
					}
					$inventoryPropertyArr[] = array(
						'propertyId'=>$pkey,
						'propertyValue'=>$property,
					);
				}
			}
			$itemData['inventoryPropertyArr'] = count($inventoryPropertyArr) == 0 ? '' : json_encode($inventoryPropertyArr);
			$itemData['productId'] = $v;
			$result = \Helper\ShoppingCart::addItem($itemData);	
		}	
	}
	
	
	/**
	 * 附加商品
	 * @param string 附加商品ID
	 * @param array 销售属性信息
	 * @param unknown_type $itemData
	 */
	function addtionalProductsCart($additional_products_id_array,$addtional_info,$itemData){
		$itemData['buyNum'] = 1;
		$id_array = explode(",",$additional_products_id_array);
		if(count($id_array) == 0) return;
		foreach ($id_array as $key=>$v){
			/**
			 * 用户选择的商品尺码属性
			 */
			$productsId = $v;
			$CustomAttributes_array=$addtional_info[$v];  
			$inventoryPropertyArr = array();
			if(!empty($CustomAttributes_array) && is_array($CustomAttributes_array)){
				foreach ($CustomAttributes_array as $key=>$v){
					if($v == 'custom' || $v == 9392){
						$customFlag = true;
					}
					if(empty($v)){
						continue;
					}
					$inventoryPropertyArr[] = array(
						'propertyId'=>$key,
						'propertyValue'=>$v,
					);
				}
			}
			$itemData['inventoryPropertyArr'] = count($inventoryPropertyArr) == 0 ? '' : json_encode($inventoryPropertyArr);
			$itemData['customArgs'] = '';
			$itemData['customPropertyIds'] = '';
			$itemData['productId'] = $productsId;
			$data = \Helper\ShoppingCart::addItem($itemData);
			
		}
	}
}