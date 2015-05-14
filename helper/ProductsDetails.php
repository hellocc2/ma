<?php
namespace Helper;
/**
 * 公共函数类
 */
class ProductsDetails {
	
	private $Details;
	
	private $CustomKey;
	
	private $CustomValue;
	
	private $Clearance = 0;
	
	private $inToCm = 2.54;
	
	private $ftToCm = 30.48;
	
	public function __construct($products_id,$isGift=''){
		$mProduct = new \Model\Product ();
		$search_arr = array ('productId' => $products_id);
		if(!empty($isGift)){
			$search_arr['isGift'] = $isGift;
		}
		$this->Details = $mProduct->getProductsDetails ( $search_arr ); 
	}
	
	/**
	 * 获取商品的详细信息
	 *
	 * @return array
	 */
	public function GetProductsDetails(){
		if(isset($this->Details['productDetails'])){
			$this->Details['productDetails']['productName'] = str_replace('&apos;','\'',html_entity_decode(stripslashes($this->Details['productDetails']['productName']),ENT_NOQUOTES,'UTF-8'));
			$this->initModelCard();
			return $this->Details;	
		}else{
			return false;
		}
		
	}
	
	private function initModelCard(){
		if(isset($this->Details['productDetails']['modelCard'])){
			if(SELLER_LANG != 'en-uk'){
				$this->Details['productDetails']['modelCard']['height'] = intval($this->Details['productDetails']['modelCard']['height'] * $this->ftToCm);
				$this->Details['productDetails']['modelCard']['bust'] = intval($this->Details['productDetails']['modelCard']['bust'] * $this->inToCm);
				$this->Details['productDetails']['modelCard']['waist'] = intval($this->Details['productDetails']['modelCard']['waist'] * $this->inToCm);
				$this->Details['productDetails']['modelCard']['hip'] = intval($this->Details['productDetails']['modelCard']['hip'] * $this->inToCm);
				
			}
		}
	}
	
	/**
	 * 获取商品的评分
	 *
	 * @return int
	 */
	public function GetProductsScore(){
		if(isset($this->Details['productDetails']['productScore'])){
			$products_score = $this->Details['productDetails']['productScore'] * 20;//商品评分
		}else{
			$products_score = 0;
		}
		return $products_score;
	}
	
	/**
	 * 获取商品的价格，包括促销价格，市场价格，销售价格
	 *
	 * @return array
	 */
	public function getProductsPrice(){
		$productsPrice = array();
		/**
		 * 商品市场价格
		 */
		$productsPrice['marketPrice'] = \Lib\common\Language::priceByCurrency($this->Details['productDetails']['marketPrice']);
		/**
		 * 商品销售价格
		 */
		$productsPrice['productPrice'] = \Lib\common\Language::priceByCurrency($this->Details['productDetails']['productPrice']);
		/**
		 * 商品促销价格
		 */
		if(!isset($this->Details['productDetails']['promotion'])){
			$productsPrice['promotionPrice'] = 0;
		}else{
			$productsPrice['promotionPrice'] = round(\Lib\common\Language::priceByCurrency($this->Details['productDetails']['promotion'][0]['promotionPrice']),2);
			$productsPrice['savePrice'] = $productsPrice['productPrice'] - $productsPrice['promotionPrice'];
		}
		/**
		 * 网站卖出价格
		 */
		if($productsPrice['promotionPrice'] == 0){
			$productsPrice['US_Price']=$this->Details['productDetails']['productPrice'];
			$productsPrice['salePrice'] = $productsPrice['productPrice'];
		}else{
			$productsPrice['US_Price']=round($this->Details['productDetails']['promotion'][0]['promotionPrice'],2);
			$productsPrice['salePrice'] = $productsPrice['promotionPrice'];
		}
		$productsPrice['customPrice'] = \Lib\common\Language::priceByCurrency($this->Details['productDetails']['customPrice']);
		return $productsPrice;
	}
	
	/**
	 * 获取商品批发数据
	 *
	 * @return array
	 */
	public function GetProductsWholesales(){
		$wholesales_array = array();
		if(isset($this->Details['productDetails']['wholesales']) && is_array($this->Details['productDetails']['wholesales'])){
			foreach ($this->Details['productDetails']['wholesales'] as $v){
				$wholesales_array[] = array(
					'salenum1'=>intval($v['minAmount']),
					'salenum2'=>intval($v['maxAmount']),
					'saleprice'=>\Lib\common\Language::priceByCurrency($v['wholesalePrice']),
					'salepired'=>$v['discount'],
				);
			}
		}
		return $wholesales_array;
	}
	
	/**
	 * 获取商品的附加商品
	 *
	 * @return array
	 */
	public function GetProductsAdditional(){
		$productsAdditional = array();
		if(isset($this->Details['productDetails']['additionalProducts']) && is_array($this->Details['productDetails']['additionalProducts'])){
			foreach ($this->Details['productDetails']['additionalProducts'] as $additionKey=>$additionItem){
				$sku = "";
				if(isset($additionItem['skuPropertyArr'])){
					$sku = $this->getProductsProperty($additionItem['skuPropertyArr'],1);
				}
				$additionalPrice = 0;
				if(isset($additionItem['promotionPrice'])){
					$additionalPrice = \Lib\common\Language::priceByCurrency($additionItem['promotionPrice']);
				}else{
					$additionalPrice = \Lib\common\Language::priceByCurrency($additionItem['additionalPrice']);
				}
				$productsAdditional[] = array(
					'marketPrice'=>\Lib\common\Language::priceByCurrency($additionItem['marketPrice']),
					'additionalPrice'=>$additionalPrice,
					'productPrice'=>\Lib\common\Language::priceByCurrency($additionItem['productPrice']),
					'firstPictureUrl'=>$additionItem['firstPictureUrl'],
					'productName'=>stripcslashes($additionItem['productName']),
					'productId'=>$additionItem['productId'],
					'sku'=>$sku,
				); 
			}
		}
		return $productsAdditional;
	}
	
	/**
	 * 获取商品的属性
	 *
	 * @return array
	 */
	public function getProducstsSku(){
		$property_data = "";
		$json_property_data = "";
		$json_property = "";
		$color = array();
		$size = array();
		if(isset($this->Details['productDetails']['salesProperty'])){
			if($this->Details['productDetails']['salesProperty']['isLinkage'] == 1){
				if(isset($this->Details['productDetails']['salesProperty']['skusArr'])){
					$property_data = $this->getLinkageProductsProperty($this->Details['productDetails']['salesProperty']['skusArr']);
				}
			}else{
				if(isset($this->Details['productDetails']['salesProperty']['skuPropertyArr'])){
					$property_data = $this->getProductsProperty($this->Details['productDetails']['salesProperty']['skuPropertyArr']);
				}
			}
		}else{
			return false;
		}
		return $property_data;
	}
	
	/**
	 * 获取商品的的促销信息
	 *
	 */
	public function GetProductsPromotions(){
		$promotion = "";
		if(isset($this->Details['productDetails']['promotion'])){
			$promotion_info = $this->Details['productDetails']['promotion'][0];
			$days = intval(($promotion_info['promotionEndTime'] - time()) / 3600);
			$promotionPic = !empty($promotion_info['promotionPic']) ? (strpos($promotion_info['promotionPic'],'/')==0 ? substr($promotion_info['promotionPic'],1) : $promotion_info['promotionPic'])  : '';
			$promotion = array(
				'startTime'=>date('Y/m/d H:i:s',time()),
				'endTime'=>date('Y/m/d H:i:s',$promotion_info['promotionEndTime']),
				'promotionName'=>$promotion_info['promotionName'],
				'promotionType'=>$promotion_info['promotionType'],
				'promotionPrice'=>\Lib\common\Language::priceByCurrency($promotion_info['promotionPrice']),
				'promotionDiscount'=>$promotion_info['promotionDiscount'],
				'promotionPic'=>$promotionPic,
				'hours'=>$days,
				'superposition'=>$promotion_info['superposition'],
			);		
		}
		return $promotion;
	}
	
	public function GetCustomKey(){
		return $this->CustomKey;
	}
	
	public function GetCustomValue(){
		return $this->CustomValue;
	}
	
	public function GetClearanceStockNum(){
		/*if($this->Clearance > 0){
			return $this->Clearance;
		}else {
			return 0;
		}*/
		return $this->Clearance;
	}
	
	/**
	 * 采用接口返回模块数据
	 * @author chengjun
	 * @return array
	 */
	public function getProductModules(){
		if(!empty($this->Details['productDetails']['productModules'])){
			
			$ModuleInfo = array();
			foreach($this->Details['productDetails']['productModules'] as $k=>$v){
				if(!empty($v) && !empty($v['templateModule'])){
					foreach($v['templateModule'] as $key=>$val){
						if(!empty($val['moduleClassKey']) && !empty($val['modulePosition'])){//必须存在moduleClassKey和modulePosition，不然就不显示该模块
							if($val['modulePosition'] == 'TAB'){//只有显示位置在TAB的才写入
								if(isset($val['frontName'])){
									$ModuleInfo['tabArray'][$val['moduleClassKey']] = $val['frontName'];
								}else{
									$ModuleInfo['tabArray'][$val['moduleClassKey']] = '';
								}
							}
							$ModuleInfo['templateModule'][$val['moduleClassKey']] = array('content'=>$val['moduleContent'],'categoryId'=>$val['moduleCategoryId'],'modulePosition'=>$val['modulePosition']);
						}
					}
				}
			}
			
			return $ModuleInfo;
		}
	}
	
	
	/**
	 * 获取联动sku的数据
	 *
	 * @param unknown_type $sku
	 * @return unknown
	 */
	private function getLinkageProductsProperty($sku){
		$property = array();
		$sku_property = array();
		$promotion = $this->GetProductsPromotions();
		
		foreach ($sku as $v){	
			if(isset($v['skuPropertyArr']) && is_array($v['skuPropertyArr'])){
				foreach ($v['skuPropertyArr'] as $sku_v){
					if(strtolower(trim($sku_v['option']['configurationValue'])) == 'custom' || trim($sku_v['option']['configurationValue']) == 9392){
						if($promotion && $promotion['promotionType'] == 'CLEAROUT'){
							continue;
						}
						if($v['skuType'] == 1){	
							$this->Clearance = -1;
						}
						$this->CustomKey = $sku_v['propertyId'];
						$this->CustomValue = $sku_v['option']['configurationValue'];
						continue;
					}
					
					if(!isset($property[$sku_v['propertyId']])){
						/*if(strstr(strtolower($sku_v['propertyEnName']),'color') !== false){
							$colorP = 'color';
						}else{
							$colorP = 'size';
						}*/
						$colorP = $this->getSkuPropertyType($sku_v['colorProperty']);
						$property[$sku_v['propertyId']] = array(
							'propertyName'=>stripcslashes($sku_v['propertyName']),
							'colorProperty'=>$colorP,
							'property'=>array(),
							'sort'=>array(),
						);
						
					}
					
					if(!isset($property[$sku_v['propertyId']][$sku_v['option']['configurationValue']])){
						$property[$sku_v['propertyId']]['property'][$sku_v['option']['configurationValue']] = array('name'=>stripcslashes($sku_v['option']['configurationName']));
						if(isset($property[$sku_v['propertyId']]['sort'][$sku_v['option']['configurationOrder']])){
							$sortOrder = $sku_v['option']['configurationOrder'] + $sku_v['option']['configurationId'];
						}else{
							$sortOrder = $sku_v['option']['configurationOrder'];
						}
						$property[$sku_v['propertyId']]['sort'][$sortOrder] = array('name'=>stripcslashes($sku_v['option']['configurationName']),'value'=>$sku_v['option']['configurationValue']);
					}
					
					if(count($v['skuPropertyArr']) >= 1){
						if(!isset($sku_property[$sku_v['option']['configurationValue']])){
							$sku_property[$sku_v['option']['configurationValue']] = array();
						}
						$sku_stock_num = isset($v['stockQuantity']) ? $v['stockQuantity'] : 0;
						$sku_occupy_stock_num = isset($v['occupyStockQuantity']) ? $v['occupyStockQuantity'] : 0;
						if($promotion && $promotion['promotionType'] == 'CLEAROUT'){
							
							$skuNum	= $sku_stock_num - $sku_occupy_stock_num;
							$skuNum = $skuNum <= 0 ? 0 : $skuNum;
							$this->Clearance += $skuNum;
							if($skuNum <= 0){
								//echo $skuNum;exit;
								unset($property[$sku_v['propertyId']]['property'][$sku_v['option']['configurationValue']]);
								unset($property[$sku_v['propertyId']]['sort'][$sku_v['option']['configurationOrder']]);
								unset($sku_property[$sku_v['option']['configurationValue']]);
								continue;
							}
						}else{
							$result = $sku_stock_num - $sku_occupy_stock_num;
							$result = $result <= 0 ? 0 : $result;
							$skuNum = $v['skuType'] == 1 ? -1 : $result;
							if($this->Clearance >= 0){
								if($v['skuType'] == 1){
									$this->Clearance = -1;
								}else{
									$this->Clearance += $skuNum;
								}
							}	
						}
						
						if(count($v['skuPropertyArr']) == 1){
							
							if(count($sku_property[$sku_v['option']['configurationValue']]) == 0){
								$sku_property[$sku_v['option']['configurationValue']] = $skuNum;
							}
						}else{
							foreach ($v['skuPropertyArr'] as $sku_p){	
								
								if($sku_v['option']['configurationValue'] != $sku_p['option']['configurationValue']){
									$value = $sku_p['propertyId'].'|'.$sku_p['option']['configurationValue'].'|'.$skuNum;
									if(!in_array($value,$sku_property[$sku_v['option']['configurationValue']])){
										$sku_property[$sku_v['option']['configurationValue']][]  = $sku_p['propertyId'].'|'.$sku_p['option']['configurationValue'].'|'.$skuNum;
									}
								}
							}
						
						}
					}
				}
			}else{
				$sku_stock_num = isset($v['stockQuantity']) ? $v['stockQuantity'] : 0;
				$sku_occupy_stock_num = isset($v['occupyStockQuantity']) ? $v['occupyStockQuantity'] : 0;
				$result = $sku_stock_num - $sku_occupy_stock_num;
				$result = $result <= 0 ? 0 : $result;
				if($promotion && $promotion['promotionType'] == 'CLEAROUT'){
					$skuNum = $result;
				}else{
					$skuNum = $v['skuType'] == 1 ? -1 : $result;
				}
				
				if($this->Clearance >= 0){
					if($promotion && $promotion['promotionType'] == 'CLEAROUT'){
						$this->Clearance += $skuNum;
					}elseif($v['skuType'] == 1){
						$this->Clearance = -1;
					}else{
						$this->Clearance += $skuNum;
					}
				}
			}
		}
		$property = $this->sortSku($property);
		$skuData = $this->initSkuData($property);
		
		$data = array(
			'products_property'=>$skuData['sku'],
			'one_size'=>$skuData['oneSize'],
			'size_count'=>$skuData['size_count'],
			'products_sku'=>count($sku_property)>0 ? $sku_property : '',
		);
		return $data;
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
	
	private function sortSku($data){
		$perpty = array();
		foreach ($data as $key=>$v){
			$perpty[$key] = array();
			$perpty[$key]['propertyName'] = $v['propertyName'];
			$perpty[$key]['colorProperty'] = $v['colorProperty'];
			$perpty[$key]['property'] = array();
			ksort($v['sort']);
			foreach ($v['sort'] as $value){
				$perpty[$key]['property'][$value['value']] = array('name'=>$value['name']);
			}
		}
		return $perpty;
	}
	
	
	
	/**
	 * 获取非联动的sku数据
	 *
	 * @param 商品的sku数据 array $sku
	 * @param 商品类型 0为商品1为附加商品
	 * @return array
	 */
	private function getProductsProperty($sku,$type=0){
		$property = array();	
		$this->Clearance = -1;
		foreach ($sku as $sku_v){			
			if(!isset($property[$sku_v['propertyId']])){
				
				/*if(strstr(strtolower($sku_v['propertyEnName']),'color') !== false){
					$colorP = 'color';
				}else{
					$colorP = 'size';
				}*/

				$colorP = $this->getSkuPropertyType($sku_v['colorProperty']);
				$property[$sku_v['propertyId']] = array(
					'propertyName'=>stripcslashes($sku_v['propertyName']),
					'colorProperty'=>$colorP,
					'property'=>array(),
					'sort'=>array(),
				);
			}
			
			foreach ($sku_v['propertyOptions'] as $op){
				if(strtolower(trim($op['configurationValue'])) == 'custom' || trim($op['configurationValue']) == 9392){
					if($type == 0){
						$this->CustomKey = $sku_v['propertyId'];
						$this->CustomValue = $op['configurationValue'];
					}
					continue;
				}
				$property[$sku_v['propertyId']]['sort'][$op['configurationOrder']] = array('name'=>stripcslashes($op['configurationName']),'value'=>$op['configurationValue']);
				$property[$sku_v['propertyId']]['property'][$op['configurationValue']] = array('name'=>stripcslashes($op['configurationName']));
			}
			if(count($property[$sku_v['propertyId']]['property']) == 0){
				unset($property[$sku_v['propertyId']]);
			}
		}	
	
		if(count($property) == 0){
			return false;
		}
		$property = $this->sortSku($property);
		$skuData = $this->initSkuData($property);
		$data = array(
			'products_property'=>$skuData['sku'],
			'products_sku'=>'',
			'one_size'=>$skuData['oneSize'],
			'size_count'=>$skuData['size_count'],
		);
		return $data;
	}
	
	private function initSkuData($property){
		$size_count = 0;
		$one_size = "";
		$one_size_property_id = 0;
		$json_property =  "";
		$sku_size_array = array();
		$sku_property_array = array(); 
		$oneSizeProperty = array(); //均码的属性值
		$sku_data = array();
		if(is_array($property)){
			if(isset($property)){
				foreach ($property as $key=>$v){
					if(!isset($sku_property_array[$v['colorProperty']])){
						$sku_property_array[$v['colorProperty']] = array();
					}
					if(!isset($sku_property_array[$v['colorProperty']][$key])){
						$sku_property_array[$v['colorProperty']][$key] = array();
					}
					$sku_property_array[$v['colorProperty']][$key]['name'] = $v['propertyName'];
					$sku_property_array[$v['colorProperty']][$key]['attr'] = $v['property'];
					if($v['colorProperty'] == 'size'){
						foreach($v['property'] as $pk=>$pv){
							$sku_size_array[] = array(
									'propertyId'=>$key,
									'keys'=>$pk,
									'values'=>$pv['name'],
							);
						}
					}
				}
			}
			$sku_data['sku'] = $sku_property_array;
			
			$size_count = count($sku_size_array);
			$sku_data['size_count'] = $size_count;
			if($size_count == 1){
				$one_size = $sku_size_array[0]['values'];
				$one_size_value = $sku_size_array[0]['keys'];
				$one_size_property_id = $sku_size_array[0]['propertyId'];
			}
			if($size_count ==0 or $size_count == 1){
				$one_size_data = array(
						'one_size'=>$sku_size_array[0]['values'],
						'one_size_value'=>$sku_size_array[0]['keys'],
						'one_size_property_id'=>$sku_size_array[0]['propertyId'],
				);
				$oneSizeProperty = $this->getOneSizeProperty($one_size_data,$size_count);
			}
			
			$sku_data['oneSize'] = $oneSizeProperty;
		}
		return $sku_data;
	}
	
	/**
	 * 获取单尺码或没有尺码的商品的属性
	 * @param unknown_type $data
	 */
	private function getOneSizeProperty($data,$size_count){
		$one_size_array = array();
		$one_size = $data['one_size'];
		$one_size_value = $data['one_size_value'];
		$one_size_property_id = $data['one_size_property_id'];
		if(isset($this->Details['productDetails']['productPropertys'])){
			if($size_count == 1)
			{
				$one_size_array = array(
						'values'=>$one_size_value,
						'propertyId'=>$one_size_property_id,
						'display_property'=>array(),
				);
				foreach ($this->Details['productDetails']['productPropertys'] as $v){
					if(!isset($v['propertyOption'])){
						continue;
					}
					foreach ($v['propertyOption'] as $sv){
						$is_unit = 0;
						if(trim(strtolower($sv['configurationName'])) == trim(strtolower($one_size)) && isset($sv['configurationContent'])){
							if(preg_match('[a-zA-Z]', $sv['configurationContent'])){
								$is_unit = 1;
							}
							$one_size_array['display_property'][] = array(
									'text'=>$v['propertyName'],
									'value'=>$sv['configurationContent'],
									'is_unit'=>$is_unit,
							);
						}
					}
				}
			}elseif($size_count == 0){
				$one_size_array = array(
						'display_property'=>array(),
				);
				foreach ($this->Details['productDetails']['productPropertys'] as $v){
					if(!isset($v['propertyOption']) || $v['propertyType'] != 'checkbox_text'){
						continue;
					}
					if(count($v['propertyOption']) == 1){
						foreach ($v['propertyOption'] as $sv){
							$is_unit = 0;
							if(preg_match('[a-zA-Z]', $sv['configurationContent'])){
								$is_unit = 1;
							}
							$one_size_array['display_property'][] = array(
									'text'=>$v['propertyName'],
									'value'=>$sv['configurationContent'],
									'is_unit'=>$is_unit,
							);
						}
					}
				}
			}
		}
		return $one_size_array;
	}
	
	
	public function getProductsCustomProperty(){
		$customProperty_status = 0; //是否有定制属性
		$customProperty = array();
		$customMutexIdsJson= '';
		if(isset($this->Details['productDetails']['customPropertyArr']) && count($this->Details['productDetails']['customPropertyArr']) > 0){
			$customMutexIdsArray = array();
			foreach ($this->Details['productDetails']['customPropertyArr'] as $cp){
				if(!isset($customProperty[$cp['customCategory']['customCategoryIdentId']]) || !is_array($customProperty[$cp['customCategory']['customCategoryIdentId']])){
					$customProperty[$cp['customCategory']['customCategoryIdentId']] = array(
							'name'=>stripslashes($cp['customCategory']['categoryName']),
							'data'=>array(),
					);
				}
				$customProperty[$cp['customCategory']['customCategoryIdentId']]['data'][$cp['customIdentId']] = array(
						'price'=>$cp['price'] == 0 ? 0 : \Lib\common\Language::priceByCurrency($cp['price']),
						'customId'=>$cp['customId'],
						'customIdentId'=>$cp['customIdentId'],
						'customName'=>$cp['customName'],
				);
				$madePrice['min'][] = \Lib\common\Language::priceByCurrency($cp['price']);
				$madePrice['max'] += \Lib\common\Language::priceByCurrency($cp['price']);
				if(isset($cp['customMutexIds'])){
					$customMutexIdsArray[$cp['customIdentId']] = explode(',', $cp['customMutexIds']);
				}
			}
			if(count($customMutexIdsArray) > 0){
				$customMutexIdsJson = json_encode($customMutexIdsArray);
			}
			$customProperty_status = 1;
		}
		$data = array(
			'customProperty_status'=>$customProperty_status,
			'customProperty'=>$customProperty,
			'customMutexIdsJson'=>$customMutexIdsJson,		
		);
		return $data;
	}
	
	public function getCategoryCode(){
		$categoryCode = '';
		if(isset($this->Details['productDetails']['productCategory']['categoryCode'])){
			$categoryCode = $this->Details['productDetails']['productCategory']['categoryCode'];
		}
		return $categoryCode;
	}
}