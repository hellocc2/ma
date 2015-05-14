<?php
namespace Module\Shop;

use helper\ResponseUtil as Rewrite;

/**
 * 支付流程第一步
 * @Jiang Lin<jianglin@mialnoo.com>
 *
 */
class Step1 extends \Lib\common\Application {
	private $displayProdustList = true;
	private $shippingAddList;
	private $shoppingProcess;
	private $cacheKey;
	private $memberId;
	function __construct() {
		global $tpl;
	
		//检查是否登录
		$this->memberId = \Helper\CheckLogin::getMemberID();
		
		//memcached初始化
		$mem = \Lib\Cache::init();
		$this->cacheKey = $_COOKIE['CartId'];
		
		//获取传递参数
		$params_all = \Helper\RequestUtil::getParams();
		
		//获取订单缓存
		$order = $mem->get($this->cacheKey . 'order');
		
		//购物流程初始化
		$this->shoppingProcess = new \Model\ShoppingProcess();
		
		//获取地址会员地址列表
		$this->shippingAddList = $this->shoppingProcess->getAddressList(array('cr.memberId' => $this->memberId));
		if(!in_array($order['shippingAddress'], $this->shippingAddList['address'])) {
			$this->displayProdustList = false;
			$order['shippingAddress'] = array();
		}
		//获取购物车数据
		$data = \Helper\ShoppingCart::getCart();

		$tpl = \Lib\common\Template::getSmarty();
		
		if(!isset($params_all->params['act'])) {
			$params_all->params['act'] = '';
		}
		
		if(isset($params_all->act)) {
			$params_all->params['act'] = $params_all->act;
		}
		
//		echo $params_all->params['act'];
		switch($params_all->params['act']) {
			case 'emptyCode':
				unset($_SESSION['COUPON']);
				header('Location:'.Rewrite::rewrite(array('url'=>'?module=shop&action=Step1','isxs'=>'no')));
			break;
			case 'edit':
				//js调用的id
				$order = \Helper\ReData::getOrder($data, $order, array('memberId' => $this->memberId, 'cacheKey' => $this->cacheKey));
				$tpl->assign('addressId',$params_all->params['id']);
					
				$this->shippingAddList['address'] = \Helper\ResponseUtil::formatArrSpe($this->shippingAddList['address']);
				
				$tpl->assign('shippingAddList', $this->shippingAddList);
				$tpl->assign('shopping_process',1);
				$tpl->assign('shippingEdit',1);
				$this->displayProdustList = false;
				break;
			case 'editpost':
				\helper\FormAuth::auth();
				
				$webserviceParam = array();
				$webserviceParam['mc.memberId'] = $this->memberId;
				$webserviceParam['mc.consigneeName'] = implode("|", $params_all->MemberContact);
				$webserviceParam['mc.consigneePhone'] = $params_all->country_Code.' '.$params_all->MemberContactPhone;
				$webserviceParam['mc.consigneeStateId'] = $params_all->MemberState;
				$webserviceParam['mc.consigneePostalcode'] = $params_all->MemberZip;
				$webserviceParam['mc.consigneeAddr'] = implode("|", $params_all->MemberContactAddr);
				$webserviceParam['mc.consigneeCity'] = $params_all->MemberCtiy;
				$webserviceParam['mc.memberUrbanAreas'] = $params_all->MemberUrbanAreas;
				$webserviceParam['mc.consigneeGender'] = $params_all->ConsigneeGender;
				
				if(isset($params_all->ConsigneeNameJa) && is_array($params_all->ConsigneeNameJa)) {
					$webserviceParam['mc.consigneeNameJa'] = implode("|", $params_all->ConsigneeNameJa);
				}
				if(isset($params_all->addnew)&&isset($params_all->addressId)&& $params_all->addnew== 0 && $params_all->addressId) {
					$webserviceParam['mc.id'] = $params_all->addressId;
				}
				$this->shoppingProcess = new \Model\ShoppingProcess();
				
				
				$resultOfAddress = $this->shoppingProcess->setAddress($webserviceParam);
				
				if($resultOfAddress['code'] == 0 && !empty($resultOfAddress['addressResult'])) {
					$this->displayProdustList = true;
					$order['shippingAddress'] = $resultOfAddress['addressResult'];
					$order = \Helper\ReData::getOrder($data, $order, array('memberId' => $this->memberId, 'cacheKey' => $this->cacheKey, 'address' => $this->shippingAddList['address']));
				} else {
					//echo '失败';
					\helper\Js::alertForward('fail',$url,1,4000,1);
					//exit();
				}
				header('Location:'.Rewrite::rewrite(array('url'=>'?module=shop&action=Step1','isxs'=>'no')));
				break;
			default:
				//查看订单是否有shipping地址
				if(!isset($order['shippingAddress']) || empty($order['shippingAddress'])) {
					$this->displayProdustList = false;
					//设置初始地址
					if(!empty($this->shippingAddList['address'])) {
						$defaultKey = 0;
						foreach($this->shippingAddList['address'] as $k=>$v){
							if($v['defaultAddress']==2){//表示默认地址
								$defaultKey = $k;
								break;
							}
						}
						$order['shippingAddress'] = $this->shippingAddList['address'][$defaultKey];
						$this->displayProdustList = true;
					}
				}
				
				
				$order = \Helper\ReData::getOrder($data, $order, array('memberId' => $this->memberId, 'cacheKey' => $this->cacheKey, 'address' => $this->shippingAddList['address']));
				$tempPhone=explode(' ',$order['shippingAddress']['consigneePhone']);
				$tempZip = $order['shippingAddress']['consigneePostalcode'];
				
				if($order['shoppingCart']['languageCode']=='ja-jp'){
					if(strlen($tempPhone[1])<6||strlen($tempPhone[1])>17){
						if(!empty($this->shippingAddList['address'])){
							$shippingAddressId = $this->shippingAddList['address'][0]['id'];
						}
						header('Location:'.Rewrite::rewrite ( array ('url' => '?module=shop&action=Step1&act=edit&id='.$shippingAddressId, 'isxs' => 'no' ) ));
					}
				}
				if(strlen($tempPhone[1])<6 ||strlen($tempPhone[1])>17 ){
					if(!empty($this->shippingAddList['address'])){
						$shippingAddressId = $this->shippingAddList['address'][0]['id'];
					}
					header('Location:'.Rewrite::rewrite ( array ('url' => '?module=shop&action=Step1&act=edit&id='.$shippingAddressId, 'isxs' => 'no' ) ));
				} 

				if (!preg_match('/^[\d\-]+$/' , $tempPhone[1])){
					if (!empty($this->shippingAddList['address'])){
						echo 1;
						$shippingAddressId = $this->shippingAddList['address'][0]['id'];
					}
					header('Location:'.Rewrite::rewrite ( array ('url' => '?module=shop&action=Step1&act=edit&id='.$shippingAddressId, 'isxs' => 'no' ) ));
				}
				
				if (!preg_match('/^[a-zA-Z0-9\s]+$/',$tempZip)){
					if (!empty($this->shippingAddress['address'])){
						$shippingAddressId = $this->shippingAddList['address'][0]['id'];
					}
					header('Location:'.Rewrite::rewrite ( array ('url' => '?module=shop&action=Step1&act=edit&id='.$shippingAddressId, 'isxs' => 'no' ) ));
				}
				
				if(empty($order['shoppingCart'])){
					header('Location:'.Rewrite::rewrite(array('url'=>'?module=shop&action=Cart','isxs'=>'no')));
				}
				break;
		}
		
		//获取购物车所有商品重量的总和的值
		if(!empty($data['shoppingCart'])){
			$tpl->assign('weight',$data['shoppingCart']['weight']);
		}

		$formAuthMD5 = \helper\FormAuth::createAuthCode();
		$tpl->assign('formAuth', $formAuthMD5);
		$tpl->assign('displayProdustList', $this->displayProdustList);
		//echo'<pre>'; var_dump($data['shoppingCart']['productCarts']);exit;
		$tpl->assign('shoppingData', $order);
		$tpl->assign('shopping_process',1);
		$tpl->assign('orderinfo',1);
		$tpl->display('order_info.htm');
		return;
	}
}