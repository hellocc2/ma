<?php
namespace Module\Shop;
use Helper\String as H;
use Helper\ResponseUtil as rew;
/**
 * 商品详情页
 * @Jerry Yang<yang.tao.php@gmail.com>
 *
 * 
 */

class Popitem extends \Lib\common\Application{	

	function __construct(){
		global $tpl ;
		$tpl = \Lib\common\Template::getSmarty ();
		$act = isset($this::$requestParams->params['act']) ? $this::$requestParams->params['act'] : '';
		$cartid = isset($this::$requestParams->params['cartid']) ? $this::$requestParams->params['cartid'] : '';
		$productId = $this::$requestParams->params['productid'];
		$productsCustom = '';
		$tpl->assign('act',$act);
		$tpl->assign('cartid',$cartid);
		$tpl->assign('ProductsId',$productId);
		$cartArray = array(
				'cookieId'=>$_COOKIE['CartId'],
				'memberId'=>isset($_SESSION [SESSION_PREFIX . "MemberId"]) ? $_SESSION [SESSION_PREFIX . "MemberId"] : '',
				'coupon'=>isset($_SESSION ['COUPON']) ? $_SESSION ['COUPON'] : '',
				'countryId'=>'',
				'expressType'=>'',
				'priceUnit'=>CurrencyCode,
				'languageCode'=>SELLER_LANG,
				
		);	
		$data = \Helper\ShoppingCart::getCart($cartArray);	
		if($data['code'] == 0){
			$data = \Helper\ShoppingCart::saveCart($data);	
		}
		if(isset($data['shoppingCart']['productCarts'][$cartid])){
			$products_info = $data['shoppingCart']['productCarts'][$cartid];
		}
		$isGift = '';
		if(isset($products_info) && $products_info['isGift'] == 1){
			//header('Location:'.rew::rewrite(array('url'=>'?module=shop&action=Cart','isxs'=>'no')));
			//exit;
			$isGift = 1;
			$tpl->assign('isGiftProduct',1);
		}
		//print '<pre>';print_r($products_info);exit;
		$custom_unit = '';
		switch ($act){
			case 'edit':
				//print '<pre>';print_r($products_info);exit;
				/**
				 * 销售属性
				 */
				$productsProperties = isset($products_info['propertiesOfDB']) ? $products_info['propertiesOfDB'] : '';
				if(!empty($productsProperties)){
					$attr = array();
					$propertiesArr = explode(',',$productsProperties);
					foreach ($propertiesArr as $v){
						$arr = explode("|",$v);
						$attr[$arr[0]] = $arr[1];
					}
				}
				
				/**
				 * 定制参数
				 */
				if(isset($products_info['customArgs']) && is_array($products_info['customArgs'])){
					$custom_unit = $products_info['customArgs']['unit'];
					$productsCustom = array();
					foreach ($products_info['customArgs']['customArgArr'] as $v){
						$productsCustom[$v['customKey']] = $v['argsValue'];
					}
				}
				/**
				 * 定制属性
				 */
				if(isset($products_info['customProperties']) && is_array($products_info['customProperties'])){
					$customsProps = explode(',',$products_info['customsOfDB']);
				}
				$tpl->assign('custom_unit',$custom_unit);
				$custom_unit = $products_info['customArgs']['unit'];
				$tpl->assign('num',$products_info['buyNum']);
				$tpl->assign('customsProps',$customsProps);
				$tpl->assign('productsInfo',$products_info);
				$tpl->assign('attr',$attr);
			break;
			case 'addition':
				$additionPrice = '';
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
							if($v['productId'] == $productId){
								if(isset($v['promotionPrice'])){
									$additionPrice = \Lib\common\Language::priceByCurrency($v['promotionPrice']);
								}else{
									$additionPrice = \Lib\common\Language::priceByCurrency($v['productPrice']);
								}
								break;
							}
						}
					}
				}
				$tpl->assign('additionPrice',$additionPrice);
			break;
			case 'addgift':
				$isGift = 1;
				$tpl->assign('num',1);
				$tpl->assign('isGiftProduct',1);
				$tpl->assign('gPrice',$this::$requestParams->params['gPrice']);
				$giftid = isset($this::$requestParams->params['giftid']) ? $this::$requestParams->params['giftid']:0;
				$tpl->assign('giftid',$giftid);
			break;
		}
		/******************************************************************/
		/*                     获取商品详细信息开始                          */
		/******************************************************************/
		$mProduct = new \Model\Product ();
		
		$pObject = new \Helper\ProductsDetails($productId,$isGift);
		$result = $pObject->GetProductsDetails();
		if(isset($result['productDetails'])){
			
			if($result['productDetails']['productsActivator'] == -1 || empty($result['productDetails']['productName'])){
				include(ROOT_PATH.'errors/notfound.php');
				return false;
			}
			
			/**
			 * 检查商品是否有促销信息
			 */
			$promotion = $pObject->GetProductsPromotions();
			/**
			 * 获取商品的价格
			 */
			$productsPrice = $pObject->getProductsPrice();
			/**
			 * 批发
			 */
			$json_wholesales = "";
			if(!$promotion){
				$wholesales_array = $pObject->GetProductsWholesales();
				if(count($wholesales_array) > 0){
					$json_wholesales = json_encode($wholesales_array);
				}
			}
			/**
			 * sku遍历
			 */
			$json_property_data = "";
			$color = array();
			$size = array();
			$sku_status = 0;
			
			$property_data = $pObject->getProducstsSku();
			
			if((isset($result['productDetails']['salesProperty']['skusArr']) || isset($result['productDetails']['salesProperty']['skuPropertyArr'])) && $result['productDetails']['productsActivator'] == 1){
				$result['productDetails']['productsActivator'] = 1;
			}else{
				$result['productDetails']['productsActivator'] = 0;
			}
			
			/**
			 * 清仓商品如果库存为0，商品下架
			 */
			$clearanceNum = $pObject->GetClearanceStockNum();
			if(isset($promotion) && isset($promotion['promotionType']) && $promotion['promotionType'] == 'CLEAROUT'){	
				if($clearanceNum == 0){
					$result['productDetails']['productsActivator'] = 0;
				}
			}else{
				if($clearanceNum > 0 || $clearanceNum == -1){
					$result['productDetails']['productsActivator'] = 1;
				}else{
					$result['productDetails']['productsActivator'] = 0;
				}
			}
			
			/**
			 * 没有销售属性的商品，是没有sku属性的
			 */
			if(isset($property_data['products_property']) && count($property_data['products_property']) == 0){
				if(!empty($result['productDetails']['salesProperty']['skusArr'])){
					$sku_noProductPropertys = $result['productDetails']['salesProperty']['skusArr'][0];
					if(!empty($promotion) && $promotion['promotionType'] == 'CLEAROUT'){
						$sku_skuType = 0;
					}else{
						$sku_skuType = $sku_noProductPropertys['skuType'];
					}
					$noProductPropertys = array(
						'skuType'=>$sku_skuType,
						'stockQuantity'=>$sku_noProductPropertys['stockQuantity'],
						'occupyStockQuantity'=>$sku_noProductPropertys['occupyStockQuantity'],
					);
					$tpl->assign('noProductPropertys',$noProductPropertys);
				}
			}
			if(isset($property_data['products_property']) && count($property_data['products_property']) > 0){
				$sku_status = 1;
			}
			$json_property =  "";
			$one_size_array = array();
			$size_count = 0;
			if(is_array($property_data)){
				$sku_property_array = $property_data['products_property'];
				$one_size_array = $property_data['one_size'];
				$size_count = $property_data['size_count'];
				if(isset($result['productDetails']['salesProperty']['isLinkage']) && $result['productDetails']['salesProperty']['isLinkage'] == 1){
					$json_property_data = array('products_sku'=>$property_data['products_sku']);
					$json_property =  json_encode($json_property_data);
				}
			}
			
			/**
			 * 自定义参数
			 */
			$products_custom_status = 0; //是否有自定义custom-made
			$custom_arr = array ('productId' => $productId);
			$custom_result = $mProduct->getProductsCustomParameters ( $custom_arr );

			$custom_temp = array();
			if(isset($custom_result['productCustomTemplate'])){
				$custom_temp['img_url'] = isset($custom_result['productCustomTemplate']['templateImageUrl']) ? $custom_result['productCustomTemplate']['templateImageUrl'] : '';
				$custom_temp['desc'] = isset($custom_result['productCustomTemplate']['templateDesc']) ? $custom_result['productCustomTemplate']['templateDesc'] : '';
				$custom_temp['name'] = isset($custom_result['productCustomTemplate']['templateName']) ? stripslashes($custom_result['productCustomTemplate']['templateName']) : '';
				$custom_temp['html_tag'] = array();
				if(isset($custom_result['productCustomTemplate']['customParametersArr']) && count($custom_result['productCustomTemplate']['customParametersArr']) > 0){
					$i = 65;
					foreach ($custom_result['productCustomTemplate']['customParametersArr'] as $v){
						$custom_temp['html_tag'][] = $this->getHtmlTag($v,$result['productDetails']['productsActivator'],$i,$productsCustom,$custom_unit);	
						$i++;
					}
				}
				$products_custom_status = 1;
			}
			/**
			 * 定制属性
			 */
			$customPropertyData = $pObject->getProductsCustomProperty();
			$customProperty_status = $customPropertyData['customProperty_status']; //是否有定制属性
			$customProperty = $customPropertyData['customProperty'];
			$customMutexIdsJson= $customPropertyData['customMutexIdsJson'];
			
			/**
			 * 检查是否有混合属性
			 */
			$propertyCheckboxText = "";
			if(isset($result['productDetails']['productPropertys']) && count($result['productDetails']['productPropertys'] ) > 0){
				$propertyCheckboxText = array();
				$propertyCheckboxText_title = array();
				foreach ($result['productDetails']['productPropertys'] as $v){
					if($v['propertyType'] == 'checkbox_text'){
						if(!isset($propertyCheckboxText[$v['propertyName']])){
							$propertyCheckboxText[$v['propertyName']] = array();
						}
						foreach ($v['propertyOption'] as $vo){
							if(!in_array($vo['configurationName'],$propertyCheckboxText_title)){
								$propertyCheckboxText_title[] = $vo['configurationName'];
							}
							$propertyCheckboxText[$v['propertyName']][$vo['configurationName']] = $vo['configurationContent'];
						}
					}
				}
				$propertyCheckboxText = array_merge(array('title'=>$propertyCheckboxText_title),array('content'=>$propertyCheckboxText));
			}
			
			/**
			 * 促销信息
			 */
			$tpl->assign('promotion_info',$promotion);

			/**
			 * 获取自定义的属性值templateName
			 */
			$customKey = $pObject->GetCustomKey();
			$customValue = $pObject->GetCustomValue();
			if($act == 'addition' || $act == 'copy'){
				$tpl->assign('num',$result['productDetails']['tied']);
			}
			$tpl->assign('productPictures',isset($result['productDetails']['productPicturesArr'][0]) ? $result['productDetails']['productPicturesArr'][0] : '');
			$tpl->assign('customKey',$customKey); //自定义的属性值
			$tpl->assign('customValue',$customValue); //自定义的属性值
			$tpl->assign('propertyCheckboxText',$propertyCheckboxText);
			$tpl->assign('size_count',$size_count);
			$tpl->assign('customMutexIdsJson',$customMutexIdsJson);//定制属性互斥
			$tpl->assign('one_size_array',$one_size_array);
			$tpl->assign('property_data',$property_data); //商品的SKU属性
			$tpl->assign('customProperty_status',$customProperty_status);  //是否有定制属性
			$tpl->assign('customProperty',$customProperty);  //是否有定制属性
			$tpl->assign('products_custom_status',$products_custom_status); //是否有custom-made属性
			$tpl->assign('custom_temp',$custom_temp);
			$tpl->assign('sku_status',$sku_status); //是否有sku属性
			if(isset($sku_property_array['color'])){
				$tpl->assign('products_color',$sku_property_array['color']); //颜色销售属性
			}
			if(isset($sku_property_array['size'])){
				$tpl->assign('products_size',$sku_property_array['size']);   //尺码销售属性
			}
			if(isset($sku_property_array['other'])){
				$tpl->assign('products_other',$sku_property_array['other']);   //其他销售属性
			}
			$tpl->assign('json_property_data',$json_property); //传输到前台的json
			$tpl->assign('wholesales_json',$json_wholesales); //传输到前台的批发json
			$tpl->assign('productsPrice',$productsPrice);  //商品的价格
			$tpl->assign('productsDetails',$result['productDetails']);	
		}
		//$tpl->display('product_details_model_edit.htm');
		
		//混合属性尺码表数据封装
		if(!empty($result['productDetails']['productPropertys'])){
			$result['productDetails']['productPropertys'] = H::strDosTrip($result['productDetails']['productPropertys']);
			foreach($result['productDetails']['productPropertys'] as $k=>$v){
				if(!empty($v['propertyOption']) && $v['isSizeChart']==1 && $v['propertyType']=='checkbox_text'){
					foreach($v['propertyOption'] as $k2=>$v2){
						if(!empty($v2['configurationContent'])){
							$productMixedPropertys[$v['propertyName']][$k2]['name'] = $v2['configurationName'];
							$productMixedPropertys[$v['propertyName']][$k2]['content'] = $v2['configurationContent'];
							$productMixedPropertys[$v['propertyName']][$k2]['value'] = $v2['configurationValue'];
							$productMixedPropertys[$v['propertyName']][$k2]['id'] = $v2['configurationId'];
							//转换尺码到英寸
							if(strpos($v2['configurationContent'],'-')!==false){
								//处理出现区间类型的尺寸 如：86-102
								$inchConten = explode('-',$v2['configurationContent']);
								if(!empty($inchConten) && count($inchConten)>=2){
									$tempOne = round($inchConten[0]/2.54,0);
									$tempTwo = round($inchConten[1]/2.54,0);
									$productMixedPropertys[$v['propertyName']][$k2]['inchContent'] = $tempOne.'-'.$tempTwo;
								}
							}else{
								//处理正常的尺寸
								$tempInch = round($v2['configurationContent']/2.54,0);
								$productMixedPropertys[$v['propertyName']][$k2]['inchContent'] = $tempInch;
							}
						}
					}
				}
			}
		}
		
		/**
		 * 色卡的color options和size options显示
		 */
		$tpl->assign('productMixedPropertys_status',(!empty($productMixedPropertys) && count($productMixedPropertys)>1) ? 1 : 0);
		$isDisplayMixedSizeChart = 0;//默认显示标准尺码表
		$displayContent = '';
		$productTheme = '';
		$parentCategories=H::cat_ga_custom_var($result['productDetails']['productCategory'],2);
		$parentCategories=explode(',',$parentCategories);
		/* 
		 if(empty($productMixedPropertys)){//当不存在尺码混合属性时，则显示标准尺码模板
			$modulePath = THEME.'TemplateGoods/';
			//商品模块信息
			$mem = \Lib\Cache::init();
			$cacheKey = md5(SELLER_LANG.$productId).'_proTheme';
			$temp = $mem->get($cacheKey);
			if(empty($temp)) {
				if(!empty($result['productDetails']['productsTheme'])){
					$productTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/'.$result['productDetails']['productsTheme'];
				}
				//return policy模板路径
				//$returnPolicyTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/kindly.htm';
				//输出到缓冲
				
				ob_start();
				if(!empty($productTheme)){
					$tpl->display($productTheme);
				}			
				$tpl->display($returnPolicyTheme);
				if(in_array('392',$parentCategories) && SELLER_LANG=='en-uk'){
					//style guide模板
					$styleGuideTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/style_guide.htm';
					//quality dresses模板
					$qualityDressesTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/quality_dresses.htm';
					if(!empty($styleGuideTheme)){						$tpl->display($styleGuideTheme);					} 
					if(!empty($qualityDressesTheme)){						$tpl->display($qualityDressesTheme);					} 	
					$tpl->assign('in_392',1);
				}
				if((in_array('2186',$parentCategories) || in_array('2188',$parentCategories) || in_array('333',$parentCategories) || in_array('2424',$parentCategories) || in_array('533',$parentCategories) )&& SELLER_LANG!='it-it'){
					//des模板
					$cosDesTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/cos_des.htm';
					if(!empty($cosDesTheme)){						$tpl->display($cosDesTheme);					} 
					$tpl->assign('in_cos',1);
				}
				if( in_array('314',$parentCategories) && SELLER_LANG!='it-it'){
					//des模板
					$zentaiDesTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/zentai_des.htm';						
					if(!empty($zentaiDesTheme)){						$tpl->display($zentaiDesTheme);					} 
					$tpl->assign('in_zentai',1);
				}
				if((in_array('635',$parentCategories) || in_array('639',$parentCategories) || in_array('1006',$parentCategories) || in_array('637',$parentCategories) || in_array('641',$parentCategories)|| in_array('642',$parentCategories))&& SELLER_LANG!='it-it'){
					//des模板
					$lolitaDesTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/lolita_des.htm';
					if(!empty($lolitaDesTheme)){						$tpl->display($lolitaDesTheme);					} 
					$tpl->assign('in_lolita',1);
				}
				$displayContent = ob_get_clean();
				ob_end_clean();
				$mem->set($cacheKey,$displayContent,0,60);
			}else{
				if(!empty($result['productDetails']['productsTheme'])){
					$productTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/'.$result['productDetails']['productsTheme'];
				}
			}
		}else{
			$isDisplayMixedSizeChart = 1;//存在尺码混合属性，显示混合属性尺码表
			$modulePath = THEME.'TemplateGoods/';
			//构建混合属性尺码表
			$mem = \Lib\Cache::init();
			$cacheKey = md5(SELLER_LANG.$productId).'_proTheme';
			$temp = $mem->get($cacheKey);
			if(empty($temp)){
				$productTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/mixed_sizechart.htm';
				//增加输出原来的商品模板
				//echo $result['productDetails']['productsTheme'];exit();
				if(!empty($result['productDetails']['productsTheme'])){
					$productOldTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/'.$result['productDetails']['productsTheme'];
				}
				//return policy模板路径
				//$returnPolicyTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/kindly.htm';
				//输出到缓冲
				ob_start();
				if(!empty($productTheme)){
					$tpl->assign('productMixedPropertys',$productMixedPropertys);
					$tpl->display($productTheme);
					if(!empty($productOldTheme)){
						$tpl->display($productOldTheme);
					}
				}

				$tpl->display($returnPolicyTheme);
				if(in_array('392',$parentCategories) && SELLER_LANG=='en-uk'){
					//style guide模板
					$styleGuideTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/style_guide.htm';
					//quality dresses模板
					$qualityDressesTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/quality_dresses.htm';
					if(!empty($styleGuideTheme)){						$tpl->display($styleGuideTheme);					} 
					if(!empty($qualityDressesTheme)){						$tpl->display($qualityDressesTheme);					} 
					$tpl->assign('in_392',1);						
				}
				if((in_array('2186',$parentCategories) || in_array('2188',$parentCategories) || in_array('333',$parentCategories) || in_array('2424',$parentCategories) || in_array('533',$parentCategories) )&& SELLER_LANG!='it-it'){
					//des模板
					$cosDesTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/cos_des.htm';
					if(!empty($cosDesTheme)){						$tpl->display($cosDesTheme);					} 
					$tpl->assign('in_cos',1);
				}
				if( in_array('314',$parentCategories) && SELLER_LANG!='it-it'){
					//des模板
					$zentaiDesTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/zentai_des.htm';						
					if(!empty($zentaiDesTheme)){						$tpl->display($zentaiDesTheme);					} 
					$tpl->assign('in_zentai',1);
				}
				if((in_array('635',$parentCategories) || in_array('639',$parentCategories) || in_array('1006',$parentCategories) || in_array('637',$parentCategories) || in_array('641',$parentCategories)|| in_array('642',$parentCategories) )&& SELLER_LANG!='it-it'){
					//des模板
					$lolitaDesTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/lolita_des.htm';
					if(!empty($lolitaDesTheme)){						$tpl->display($lolitaDesTheme);					} 
					$tpl->assign('in_lolita',1);
				}
				$displayContent = ob_get_clean();
				ob_end_clean();
				$mem->set($cacheKey,$displayContent,0,60);
			}else{
				$productTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/mixed_sizechart.htm';
			}
		}
			
		if(empty($temp)){
			$tpl->assign('cacheTheme_1',$displayContent);
		}else{
			$tpl->assign('cacheTheme_1',$temp);
		}
		$tpl->assign('productTheme',$productTheme);
		$tpl->assign('isDisplayMixedSizeChart',$isDisplayMixedSizeChart);//1:显示混合属性尺码，0：显示标准尺码
		 */
		$modulePath = THEME.'TemplateGoods/';
		$productTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/'.$result['productDetails']['productsTheme'];
		$tpl->assign('productTheme',$productTheme);
		$tpl->assign('isDisplayMixedSizeChart',$isDisplayMixedSizeChart);//1:显示混合属性尺码，0：显示标准尺码
		
		//商品模板新处理方法
		$productModules = $pObject->getProductModules();
		if(!empty($productModules)){
			foreach($productModules['templateModule'] as $k=>$v){
				if($v['categoryId']==4){
					$tpl->assign('sizeChartShowContent',$v['content']);
					$tpl->assign('sizeChartShow',$k);
				}elseif($v['categoryId']==6){
					$tpl->assign('colorChartShow',$k);
				}
			}
			$tpl->assign('productModules',$productModules['templateModule']);
			$tpl->assign('tabArray',$productModules['tabArray']);
		}
		
		$tpl->assign('cart_act',$act);
		$tpl->display('product_details_model.htm');
		return;
	}
	function getHtmlTag($data,$status,$index_sort,$editProductsCustom,$custom_unit=''){
		$html_string = array(
			'text'=> $data['parameterName'],
			'index'=>strtoupper(chr($index_sort)),
		);
		$value = '';
		$html = "";
		$type = $data['parameterType'];
		$disabled = $status == 0 ? " disabled='true' " : '';
		if(!empty($editProductsCustom)){	
			$value = isset($editProductsCustom[$data['parameterKey']]) ? $editProductsCustom[$data['parameterKey']] : '';
		}
		switch ($type){
			case 1:
				$html .= "<input type='text' class='numpass' value='".$value."'";
				$html .= $disabled;
				$unit_array = array();
				if(isset($data['minValue']) && isset($data['maxValue'])){
					$cm_min = $data['minValue'];
					$cm_max = $data['maxValue'];
					$unit_array['cm'][] = $cm_min;
					$unit_array['cm'][] = $cm_max;
					if($data['parameterUnit'] == 1){
						$in_min = round($data['minValue'] / 2.54,1);
						$in_max = round($data['maxValue'] / 2.54,1);
						$unit_array['in'][] = $in_min;
						$unit_array['in'][] = $in_max;
						$unit_min_one = $cm_min." cm";
						$unit_max_one = $cm_max." cm";
						$unit_min_two = $in_min." in";
						$unit_max_two = $in_max." in";
					}elseif ($data['parameterUnit'] == 2){
						$in_min = round($data['minValue'] / 0.45359237,1);
						$in_max = round($data['maxValue'] / 0.45359237,1);
						$unit_array['in'][] = $in_min;
						$unit_array['in'][] = $in_max;
						$unit_min_one = $cm_min." kg";
						$unit_max_one = $cm_max." kg";
						$unit_min_two = $in_min." lb";
						$unit_max_two = $in_max." lb";
					}
					$html .= " cm='". implode(',', $unit_array['cm']) ."'";
					$html .= " in='". implode(',', $unit_array['in']) ."'";
					$cm_error = sprintf(\LangPack::$items['custom_tips'],$unit_min_one,$unit_max_one);
					$in_error = sprintf(\LangPack::$items['custom_tips'],$unit_min_two,$unit_max_two);
				}
				$units_custom = $data['parameterUnit'] == 1 ? 1 : 2;
				
				$html .= " units='".$units_custom."'";
				$html .= " name='Customszie[".$data['parameterKey']."__".$index_sort."__input]' />";
				if(empty($custom_unit)){
					$html_string['unit'] = intval($data['parameterUnit']) == 1 ? '<label class="custom_unit">cm</label>' : '<label class="custom_unit_kg">kg</label>';
				}else{
					$weight_unit = $custom_unit == 'in' ? 'lb' : 'kg';
					$html_string['unit'] = intval($data['parameterUnit']) == 1 ? '<label class="custom_unit">'.$custom_unit.'</label>' : '<label class="custom_unit_kg">'.$weight_unit.'</label>';
				}
				if(!empty($cm_error)){
					$html_string['tips'] = '<div class="tips1" style="display:none">'.$cm_error.'</div><div class="tips2" style="display:none">'.$in_error.'</div>';
				}
				$html_string['tag_type'] = 'input';
			break;
			case 2:
				$html .= "<input type='file' name='".$data['parameterKey']."' ";
				$html .= $disabled;
				$html .= "/>";
			break;
			case 3:
				if(isset($data['optionArr']) && count($data['optionArr']) > 0){
					foreach ($data['optionArr'] as $v){
						if($value == $v['optionName']){
							$html.= "<input type='radio' name='Customszie[".$data['parameterKey']."__".$index_sort."]' value='".$v['optionKey']."' checked>".$v['optionName'];
						}else{
							$html.= "<input type='radio' name='Customszie[".$data['parameterKey']."__".$index_sort."]' value='".$v['optionKey']."'>".$v['optionName'];
						}
					}
				}
				//$html_string['unit'] = intval($data['parameterUnit']) == 1 ? '<label class="custom_unit">cm</label>' : 'kg';
				$html_string['unit'] = '';
				$html_string['tag_type'] = 'input';
			break;
			case 4:
				if(isset($data['optionArr']) && count($data['optionArr']) > 0){
					foreach ($data['optionArr'] as $v){
						$html.= "<input type='checkbox' name='Customszie[".$data['parameterKey']."__".$index_sort."]' value='".$v['optionKey']."' $disabled >".$v['optionName'];
					}
				}
				//$html_string['unit'] = intval($data['parameterUnit']) == 1 ? '<label class="custom_unit">cm</label>' : 'kg';
				$html_string['unit'] = '';
				$html_string['tag_type'] = 'input';
			break;
			case 5:
				if(isset($data['optionArr']) && count($data['optionArr']) > 0){
					$html .= "<select name='Customszie[".$data['parameterKey']."__".$index_sort."]' $disabled>";
					foreach ($data['optionArr'] as $v){
						if($value == $v['optionKey']){
							$html.= "<option value='".$v['optionKey']."' selected>".$v['optionName']."</option>";
						}else{
							$html.= "<option value='".$v['optionKey']."'>".$v['optionName']."</option>";
						}
					}
					$html .= "</select>";
					$html_string['tag_type'] = 'select';
				}	
			break;
		}
		$html_string['html'] = $html;
		return $html_string;
	}
}