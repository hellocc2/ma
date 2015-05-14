<?php
namespace Helper;
/**
 * 公共函数类
 */
class Gifts {
	
	private $giftInfo;
	
	private $giftId;
	
	public function __construct($products_id,$unitPrice,$categoryCode){
		$parms = array(
			'cookieId' => isset($_COOKIE['CartId']) ? $_COOKIE['CartId'] : '',
			'memberId' => isset($_SESSION [SESSION_PREFIX . "MemberId"]) ? $_SESSION [SESSION_PREFIX . "MemberId"] : '',
			'productId' => $products_id,
			'categoryCode' => $categoryCode,
			'unitPrice' => $unitPrice,
			'priceUnit' => 'USD',
			'languageCode' => SELLER_LANG,	
		);
		$giftModel = new \Model\Gifts();
		$data = $giftModel->queryViewGifts($parms);
		if(isset($data['code']) && $data['code'] == 0 && isset($data['giftInfo'])){
			$this->giftInfo = $data['giftInfo'];
			$this->giftId = $data['giftInfo']['giftId'];
		}
		
	}
	/**
	 * 检查该商品是否有礼品展示
	 */
	public function checkGiftStatus(){
		if(is_array($this->giftInfo)){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取礼品组ID
	 */
	public function getGiftId(){
		return $this->giftId;
	}
	
	/**
	 * 获取终端页的礼品列表
	 * 只返回可以加入购物车的礼品
	 */
	public function getProductsGift(){
		$giftList = array();
		if(isset($this->giftInfo['giftProductList']) && count($this->giftInfo['giftProductList']) > 0){
			$gift = $this->giftInfo['giftProductList'];
			$giftList['radio'] = $this->giftInfo['radio'];
			foreach ($gift as $g){
				if($g['status'] != 1){
					continue;
				}
				$giftList['list'][$g['productId']] = array(
					'productId'=>$g['productId'],
					'giftPrice'=>round($g['giftPrice'],2),
					'limitNum'=>$g['limitNum'],
					'firstPictureUrl'=>$g['firstPictureUrl'],
					'productName'=>stripslashes($g['productName']),
					'productPrice'=>round(\Lib\common\Language::priceByCurrency($g['productPrice']),2),
				);
				if(isset($g['skuPropertyArr']) && count($g['skuPropertyArr']) > 0){
					$sku = $this->getGiftSku($g['skuPropertyArr']);
					if(isset($sku['data'])){
						$giftList['list'][$g['productId']]['sku'] = $sku['data'];
						$giftList['list'][$g['productId']]['sku_count'] = $sku['count'];
					}else{
						$giftList['list'][$g['productId']]['sku_count'] = 2;
					}
				}else{
					$giftList['list'][$g['productId']]['sku_count'] = 2;
				}
			}
		}
		return $giftList;
	}
	
	/**
	 * 获取SKU的类型
	 * @param string $val
	 */
	private function getSkuPropertyType($val){
		$propertyString = '';
		switch($val){
			case 1:
				$propertyString = 'color';
				break;
			case 2:
				$propertyString = 'size';
				break;
			case 3:
				$propertyString = 'other';
				break;
		}
		return $propertyString;
	}
	
	/**
	 * 获取礼品的SKU
	 * @param unknown_type $data
	 */
	private function getGiftSku($data){
		$sku = array();
		$count = 0;
		foreach($data as $sku_v){
			$colorP = $this->getSkuPropertyType($sku_v['colorProperty']);
			if($count == 0 && count($sku_v['propertyOptions']) > 1){
				$count = 1;
			}
			if(isset($sku_v['propertyOptions']) && count($sku_v['propertyOptions']) > 0){
				$skup = array(
					'propertyName'=>stripslashes($sku_v['propertyName']),
					'propertyId'=>$sku_v['propertyId'],
				);
				$sku_arr = array();
				foreach($sku_v['propertyOptions'] as $v){
					$sku_arr[$v['configurationOrder']] = array(
						'name'=>stripslashes($v['configurationName']),
						'value'=>$v['configurationValue'],	
					);
				}
				ksort($sku_arr);
				$skup['data'] = $sku_arr;
			}else{
				continue;
			}
			$sku['data'][$colorP][] = $skup;
		}
		$sku['count'] = $count;
		return $sku;
	}
}