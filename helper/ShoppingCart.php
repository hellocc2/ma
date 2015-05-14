<?php
namespace Helper;
/**
 * 与购物车相关的方法调用
 */
class ShoppingCart {
	/**
	 * 获取购物车数据，检测在那个module刷新购物车数据，那个地方调用购物车缓存
	 *
	 * @return array
	 */
	static $action;
	public static function getCart($cartData=''){
		
		$params_all = \Helper\RequestUtil::getParams();
		$data = "";
		$mem = \Lib\Cache::init();
		self::$action = $params_all->action;
		if(($params_all->module == 'shop' && 
				($params_all->action == 'Cart' || 
			     $params_all->action == 'Step1' || 
				 self::$action == "Paypal" || 
				 self::$action=="popitem")) || self::$action='UserStatus' || self::$action='MiniCart'
			){
			$data = self::initCart($cartData);
		}else{
			$mem = \Lib\Cache::init();
			$cacheKey = $_COOKIE['CartId'];
			if($params_all->action == 'Step1'){
				$data = $mem->get($cacheKey.'PAY');
			}else{
				$data = $mem->get($cacheKey);
			}
			if(!$data){
				$data = self::initCart($cartData);
			}
		}
		return $data;
	}
	
	/**
	 * 初始化购物车数据
	 *
	 * @return array
	 */
	private static function initCart($cartData){
		$cart_obj=new \Model\Cart ();
		$data=$cart_obj->getCart($cartData);
		if($data['code'] == 0){
			$data = self::saveCart($data);
		}
		return $data;
	}
	
	public static function emptyCartCache(){
		$mem = \Lib\Cache::init();
		$cacheKey = $_COOKIE['CartId'];
		$mem->delete($cacheKey);
		$mem->delete($cacheKey.'PAY');
		unset($_SESSION['COUPON']);
		unset($_SESSION['countryId']);
		unset($_SESSION['expressType']);
		//$data=$cart_obj->clearShoppingCart($cartData);
	}
	
	
	
	public static function saveCart($data){
		if(!isset($_SESSION['autoAddGiftFlag']) || !isset($data['autoAddGiftFlag']) || $_SESSION['autoAddGiftFlag'] != $data['autoAddGiftFlag']){
			if(isset($data['autoAddGiftFlag'])){
				$_SESSION['autoAddGiftFlag'] = $data['autoAddGiftFlag'];
			}
		}
		$mem = \Lib\Cache::init();
		$cacheKey = $_COOKIE['CartId'];
		if(isset($data['shoppingCart']['productCarts']) && count($data['shoppingCart']['productCarts'])>0){
			foreach ($data['shoppingCart']['productCarts'] as $key=>$v){
				$data['shoppingCart']['productCarts'][$key]['productName'] = stripcslashes($v['productName']);
			}
		}
		if(self::$action == "Step1" || self::$action == "Paypal"){
			
			$mem->set($cacheKey.'PAY',$data);
			return $data;
		}else{
			$mem->set($cacheKey,$data);
		}

		if(isset($data['shoppingCart']['productCarts'])){		
			$productsList = self::initProductsList($data['shoppingCart']['productCarts']);
			$data['shoppingCart']['productCarts'] = $productsList;
		}
		if(isset($data['shoppingCart']['additionalProducts'])){		
			$additionalProductsList = self::initAdditionProductsList($data['shoppingCart']['additionalProducts']);
			$data['shoppingCart']['additionalProducts'] = $additionalProductsList;
		}
		if(isset($data['giftInfo'])){		
			$giftList = self::initGiftList($data['giftInfo']);
			$data['giftInfo'] = $giftList;
		}
		return $data;
	}
	
	public static function initAdditionProductsList($data){
		$list = array();
		foreach ($data as $key=>$v){
			$v['productName'] = stripslashes($v['productName']);
			$list[$v['productId']] = $v;
		}
		return $list;
	}
	
	
	public static function initProductsList($data){
		$productsList = array();
		foreach ($data as $v){
			if($v['isGift'] == 1){
				$v['giftKey'] = $v['productId'].'_'.$v['giftId'];
			}
			if(isset($v['customProperties']['customPropertyArr'])){
				$v['customProperties'] = self::initProductCustomProperties($v['customProperties']['customPropertyArr']);
			}
			$productsList[$v['cartId']] = $v;
		}
	
		return $productsList;
	}

	
	private static function sortProductlist($list){
		$sort = array();
		foreach ($list as $key=>$v){
			if(!in_array($key,$sort)){
				$sort[] = $key;
			}
			if(!empty($v['copyCartId'])){
				$ks = array_search($key,$sort);
				if(count($sort) == 1){
					$sort = array($v['copyCartId'],$key);
				}else{
					if($ks == 0){
						$sort = array_merge(array($v['copyCartId']),$sort);
					}else{
						array_splice($sort,$ks,0,$v['copyCartId']);
					}
				}
			}else{
				if(!in_array($key,$sort)) array_push($sort,$key);
			}
		}
		$newProductList = array();
		foreach ($sort as $v){
			$newProductList[] = $list[$v];
		}
		return $newProductList;
	}
	
	
	
	
	public static function initProductCustomProperties($data){
		$customProperty = array();
		foreach ($data as $v){
			if(!isset($customProperty[$v['customCategoryId']])){
				$customProperty[$v['customCategoryId']] = array(
					'categoryName'=>$v['categoryName'],
					'list'=>array(),
				);
			}
			$customProperty[$v['customCategoryId']]['list'][] = array(
				'customId'=>$v['customIdentId'],
				'customName'=>$v['customName'],
			);
		}
		
		return $customProperty;
	}
	
	private static function initGiftList($data){
		$giftList_y = array();
		$giftList_n = array();
		$cartTotalPrice = $data['cartTotalPrice'];
		$giftLimit = array();
		if(isset($data['giftList']) && is_array($data['giftList'])){
			foreach ($data['giftList'] as $v){
				if($cartTotalPrice >= $v['rangeLumpSum']){
					foreach ($v['giftProductList'] as $gv){
						$gv['giftId'] = $v['giftId'];
						$keys = $gv['productId'].'_'.$v['giftId'];
						$giftLimit[$keys] = $gv['limitNum'] - $gv['buyNum'];
						$gv['productName'] = stripcslashes($gv['productName']);
						$giftList_y[] = $gv;
					}
				}else{
					if(count($v['giftProductList']) > 0){
						$gift_price = $v['rangeLumpSum'] - $cartTotalPrice;	
						if(!isset($giftList_n[$v['rangeLumpSum']])) {
							$giftList_n[$v['rangeLumpSum']] = array(
								'price'=>round($gift_price,2),
								'rangeLumpSum'=>$v['rangeLumpSum'],
								'list'=>array(),
							);
						}
						
						foreach ($v['giftProductList'] as $gv){
							$gv['productName'] = stripcslashes($gv['productName']);
							$giftList_n[$v['rangeLumpSum']]['list'][$gv['productId']] = $gv;
						}
					}
				}
			}
		}
		ksort($giftList_n);
		$giftList = array(
			'cartTotalPrice'=>$cartTotalPrice,
			'giftLimit'=>$giftLimit,
			'gift_y' => $giftList_y,
			'gift_n'=>count($giftList_n) > 0 ? $giftList_n : '',
		);
		return $giftList;
	}
	
	
	
	/**
	 * 添加商品到购物车
	 *
	 * @param array $productsData 商品数据
	 * @return array 购物车数据
	 */
	public static function addItem($productsData){
		$cart_obj=new \Model\Cart ();
		$data=$cart_obj->addItem($productsData);
		if($data['code'] == 0){
			$data = self::saveCart($data);
		}
		return $data;
	}
	
	/**
	 * 修改购物车中的商品
	 *
	 * @param array $productsData 商品数据
	 * @return array 购物车数据
	 */
	public static function editItem($productsData){
		$cart_obj=new \Model\Cart ();
		$data=$cart_obj->editItem($productsData);
		if($data['code'] == 0){
			$data = self::saveCart($data);
		}
		return $data;
	}
	
	/**
	 * 删除购物车中的商品
	 *
	 * @param array $productsData 商品数据
	 * @return array 购物车数据
	 */
	public static function delItem($data){
		$cart_obj=new \Model\Cart ();
		$data=$cart_obj->delItem($data);
		if($data['code'] == 0){
			$data = self::saveCart($data);
		}
		return $data;
	}
	
	public static function emptyCart($data){
		$cart_obj=new \Model\Cart ();
		$data=$cart_obj->emptyCart($data);
		if($data['code'] == 0){
			$data = self::saveCart($data);
		}
		return $data;
	}
}
