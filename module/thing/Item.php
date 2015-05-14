<?php
namespace Module\Thing;
use \Model\Comment;
use Model\KeyWords;
use Helper\RequestUtil as R;
use Helper\ResponseUtil as rew;
use Helper\String as H;
use Helper\Js as JS;
use Helper\Page as P;
/**
 * 商品详情页
 * @Jerry Yang<yang.tao.php@gmail.com>
 *
 */
class Item extends \Lib\common\Application{	
	function __construct(){
		/**留言评论添加**/
		$tabClass = R::getParams('tabClass');
		/**留言评论翻页**/
		//(R::getParams('fanye')) && ($this -> ajax_conment_fanye());
		global $tpl ;
		$tpl = \Lib\common\Template::getSmarty ();
		/**获取来路信息**/
		$this->parse_referer();
		$module = R::getParams('module');
		$action = R::getParams('action');
		$productId = R::getParams('id');
		$searchKeyword = R::getParams('searchKeyword');
		$tpl->assign('productId',$productId);
		
		//返回地址
		$_SESSION ['b2cbast_url'] = R::getUrl ();
		
		//获取咨询的类型
		$topicCategory = $this->getAdvisoryCategory();
		$topicCategory = H::slashes($topicCategory);
		$tpl->assign('topicCategory',$topicCategory);
		//================================
		//+--add by chengjun 2011-12-05--+
		//================================
		//获取外链关键词
		$refer = $this->getRefer();
		$keyword = '';
		$displayTopQuery = 0;
		$noscroll = 'false';
		if($refer === true){
			//直接输入地址访问
			$displayTopQuery = 1;//显示推荐商品
			$keyword = 1;//给推荐商品加上特殊关键词，点击推荐商品一直会显示对应的推荐商品
			
		}elseif(!empty($refer) && is_array($refer) && $refer['hostname']!=='milanoo' && !empty($refer['keyword'])){
			$keyword = $refer['keyword'];
			$noscroll = 'true';//搜索引擎进入页面，显示第一屏
			//有关键词则跳转一下，将关键词以参数的形式传递，避免出现刷新页面关键丢失的问题
			if(empty($searchKeyword)){
				$jumpUrl = $_SERVER['REQUEST_URI'].'?searchKeyword='.$keyword; 
				header('HTTP/1.1 301 Moved Permanently');//发出301头部
				header('Location:'.$jumpUrl);
				return ;
			}
			$displayTopQuery = 1;//显示推荐商品
		}else{
			//从本站其他地方进入，含有refer，但是没有关键词
			$displayTopQuery = 0;//不显示推荐商品
		}
		if(empty($keyword) && !empty($searchKeyword)){
			//如果没有查找到外链关键词， 而且连接中带有关键词参数，说明是由之前的链接带来的关键词
			$keyword = $searchKeyword;
			$displayTopQuery = 1;//显示推荐商品
		}
		//获取推荐商品
		$keyword = !empty($keyword) ? $keyword : '';
		if($displayTopQuery){
			//限制返回数量为50
			$searchTopQueryArray = array('productId'=>$productId,'searchContent'=>$keyword,'pageSize'=>36,'pageNo'=>1);
			$getTopQueryRecommend = new \Model\ItemOtherProducts();
			$topQueryData = $getTopQueryRecommend->getTopQuery($searchTopQueryArray);

			if(!empty($topQueryData) && $topQueryData['code']==0){
				if(!empty($topQueryData['listResults']['results'])){
					$topQueryData['listResults']['results'] = H::strDosTrip($topQueryData['listResults']['results']);
					foreach($topQueryData['listResults']['results'] as $key=>$val){
						if($val['productId']==$productId){
							$topQueryData['listResults']['results'][$key]['displayFocus'] = 1;//当前商品
						}
						$topQueryData['listResults']['results'][$key]['productPrice'] = \Lib\common\Language::priceByCurrency ( $val ['productPrice'] );
					}
					$tpl->assign('topQueryData',$topQueryData['listResults']['results']);
				}
			}
		}
		$tpl->assign('searchKeyword',$keyword);
		$tpl->assign('scrollLocked',$noscroll);
		//end
		//浏览记录操作
		//写入记录
		$this->setHistoryProducts();
		//读取记录
		$cookieHistoryView = $this->getHistoryProducts();
		$historyIdString = '';
		if(!empty($cookieHistoryView)){
			//去除第一条当前ID记录,避免刷新显示当前ID的浏览记录
			//if($cookieHistoryView[0] == $productId){
				array_shift($cookieHistoryView);
			//}
			if(count($cookieHistoryView)>3){
				array_pop($cookieHistoryView);
			}
			if(!empty($cookieHistoryView)){
				$historyIdString = implode(',', $cookieHistoryView);
			}
		}
		
		$tpl->assign('historyIdString',$historyIdString);
		//end
		//================================
		//+--------------end-------------+
		//================================
		
		/******************************************************************/
		/*                     获取商品详细信息开始                          */
		/******************************************************************/
		$mProduct = new \Model\Product ();
		/*$search_arr = array ('productId' => $productId);
		$result = $mProduct->getProductsDetails ( $search_arr );*/
		$pObject = new \Helper\ProductsDetails($productId);
		$result = $pObject->GetProductsDetails();
		if(isset($result['productDetails'])){
			
			
			if($result['productDetails']['productsActivator'] == -1 || empty($result['productDetails']['productName'])){
				include(ROOT_PATH.'errors/notfound.php');
				return false;
			}
			if(isset($result['productDetails']['productScore'])){
				$products_score = $result['productDetails']['productScore'] * 20;//商品评分
			}else{
				$products_score = 0;
			}
			if($tabClass == 'pl'){
				$this -> add_message(stripslashes($result['productDetails']['productName']));
			}
			
			if($tabClass == 'qa'){
				$this -> add_qa(stripslashes($result['productDetails']['productName']));
			}
			/**
			 * 检查商品是否有促销信息
			 */
			$promotion = $pObject->GetProductsPromotions();
			/**
			 * 获取商品的评分
			 */
			$products_score = $pObject->GetProductsScore();
			/**
			 * 获取商品的价格
			 */
			$productsPrice = $pObject->getProductsPrice();
			/**
			 * 是否高清
			 */
			$productPictureType = isset($result['productDetails']['productPictureType']) ? $result['productDetails']['productPictureType'] : 0;
			$productPictureType = $productPictureType == 1 ? 1 : 0;
			/**
			 * 图片总数
			 */
			$productPicturesCount = isset($result['productDetails']['productPicturesArr']) ? count($result['productDetails']['productPicturesArr']) : 0;
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
				if(($clearanceNum > 0 || $clearanceNum == -1) && $result['productDetails']['productsActivator'] == 1){
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
			$madePrice = array('min'=>array(),'max'=>0); //所有定制类的价格
			/**
			 * 自定义参数
			 */
			/**
			 * 获取自定义的属性值templateName
			 */
			$customKey = $pObject->GetCustomKey();
			$customValue = $pObject->GetCustomValue();
			$tpl->assign('customKey',$customKey); //自定义的属性值
			$tpl->assign('customValue',$customValue); //自定义的属性值
			
			$products_custom_status = 0; //是否有自定义custom-made
			if(!empty($customKey)){
				$custom_arr = array ('productId' => $productId);
				$custom_result = $mProduct->getProductsCustomParameters ( $custom_arr );
				$custom_temp = array();
				if(isset($custom_result['productCustomTemplate'])){
					$custom_temp['img_url'] = isset($custom_result['productCustomTemplate']['templateImageUrl']) ? $custom_result['productCustomTemplate']['templateImageUrl'] : '';
					$custom_temp['desc'] = isset($custom_result['productCustomTemplate']['templateDesc']) ?$custom_result['productCustomTemplate']['templateDesc'] : '';
					$custom_temp['name'] = isset($custom_result['productCustomTemplate']['templateName']) ? stripslashes($custom_result['productCustomTemplate']['templateName']) : '';
					$custom_temp['html_tag'] = array();
					if(isset($custom_result['productCustomTemplate']['customParametersArr']) && count($custom_result['productCustomTemplate']['customParametersArr']) > 0){
						$i = 65;
						foreach ($custom_result['productCustomTemplate']['customParametersArr'] as $v){
							$custom_temp['html_tag'][] = $this->getHtmlTag($v,$result['productDetails']['productsActivator'],$i);	
							$i++;
						}
					}
					$products_custom_status = 1;
					$madePrice['max'] += $productsPrice['customPrice'];
					$madePrice['min'][] = $productsPrice['customPrice'];
				}
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
			 * 附加商品
			 */
			$productsAdditional = $pObject->GetProductsAdditional();
			$parentId = isset($_GET['parentid']) ? intval($_GET['parentid']) : '';
			
			if($parentId && $parentId != $productId){
				$mainFlag = false;
				foreach ($productsAdditional as $v){
					if($v['productId'] == $parentId){
						$mainFlag = true;
						break;
					}
				}
		
				if(!$mainFlag){
					$parentObject = new \Helper\ProductsDetails($parentId);
					$parentProductsAdditional = $parentObject->GetProductsAdditional();
					$newProductsAdditional = array();
					$parentResult = $parentObject->GetProductsDetails();
					$parentPromotion = $parentObject->GetProductsPromotions();
					$parentProductsPrice = $parentObject->getProductsPrice();
					$parentSku = $parentObject->getProducstsSku();
					
					
					$mainProducts = array(
						'marketPrice'=>$parentProductsPrice['marketPrice'],
						'additionalPrice' => $parentProductsPrice['salePrice'],
			            'productPrice' => $parentProductsPrice['salePrice'],
			            'firstPictureUrl' => $parentResult['productDetails']['productPicturesArr'][0],
			            'productName' => $parentResult['productDetails']['productName'],
			            'productId' => $parentId,
			            'sku' => $parentSku,
					);
					$newProductsAdditional[] = $mainProducts;
					foreach ($parentProductsAdditional as $v){
						if($v['productId'] != $productId){
							$newProductsAdditional[] = $v;
						}else{
							$tpl->assign('cPrice',round($v['productPrice'] - $v['additionalPrice'],2));
							$tpl->assign('itemAdditionalPrice',$v['additionalPrice']);
						}
					}
					$productsAdditional = $newProductsAdditional;
					
					unset($parentProductsAdditional);
					unset($newProductsAdditional);
					unset($parentSku);
					unset($parentPromotion);
					unset($parentProductsPrice);
					unset($parentResult);
					unset($parentObject);
					unset($mainProducts);
				}
				
				$tpl->assign('parentProductsId',$parentId);	
			}
			//print '<pre>';print_r($productsAdditional);
			if(count($productsAdditional) > 0){
				$tpl->assign('additionalProducts',$productsAdditional);	
			}
			/**
			 * 获取国家信息
			 */
			//$countriesList = \Helper\Countries::GetCountries(SELLER_LANG,'',$result['productDetails']['productsParcels']);
			/**
			 * 获取国家
			 */
			$countries_obj=new \Model\CountryList ();
			$countriesList = $countries_obj->getCountryList();
			$countryFlag = isset($_SERVER ['HTTP_X_REAL_COUNTRY']) ? strtolower($_SERVER ['HTTP_X_REAL_COUNTRY']) : 'sa';
						
			$countryId = isset($countriesList['countriesFlag'][$countryFlag]) ? $countriesList['countriesFlag'][$countryFlag] : '';
			$cList = array();
			if(isset($countriesList['counties'])){
				foreach ($countriesList['counties'] as $key=>$v){
					$cList[] = array(
						'name'=>$v,
						'img'=>$countriesList['banner'][$key],
						'status'=>$countriesList['shipping'][$key],
						'value'=>$key,
					);
				}
			}
			$countriesList = $cList;
			/**
			 * 获取货币信息
			 */
			//$currency_all = \config\Currency::$currencyTranslations;
			
			/**
			 * 获取评论
			 */
			$WebsiteId=R::getParams('WebsiteId');
			if(empty($WebsiteId)){$WebsiteId=1;}
			$pageNo=R::getParams('page');
			if(empty($pageNo) || !is_numeric($pageNo)){$pageNo=1;}
			$pageSize=6;
			$comment_obj=new \Model\Comment ();
			$comments=$comment_obj->getCommentsByPid($productId,$WebsiteId,$pageNo,$pageSize);
			$reviewsCount = 0;
			if(isset($comments ['listResults'] ['totalCount']) && $comments ['listResults'] ['totalCount']!=0 ){
				$reviewsCount = $comments ['listResults'] ['totalCount'];
			}
			//-----------------------商品评论分数------------------------
			if(isset($result['productDetails']['comments'])){
				$comments_proce = $result['productDetails']['comments'];
				krsort($comments_proce);
				$comment_star_list = array();
				foreach ($comments_proce as $key=>$v){
					$per = 0;
					if($v > 0){
						$per = round($v / $reviewsCount,2) * 100;
					}
					$comment_star_list[] = array(
						'star'=>$key,
						'per'=>$per,
						'num'=>$v
					);
				}
				$tpl->assign('comment_star_list',$comment_star_list);	//评论级别列表
			}
			//-----------------------商品评论分数------------------------
			
			/**
			 * 登录用户的邮箱地址
			 */
			$tpl->assign('login_mail',isset($_SESSION[SESSION_PREFIX.'MemberEmail'])?$_SESSION[SESSION_PREFIX.'MemberEmail']:'');
			/**
			 * 判断是否切换语言
			 */
			if(isset($comments ['listResults']['results'])){
				$tpl->assign('comments',$comments ['listResults']['results']);
			}
			//$tpl->assign('currency_all',$currency_all);
			/**
			 * 促销信息
			 */
			$tpl->assign('promotion_info',$promotion);
			/**
			 * 国家地址
			 */
			//$tpl->assign('Countries_po',$countries);
			/**
			 * 促销商品的折扣值
			 */
			if(isset($promotion) && $promotion){
				$pro_discount_off = sprintf(\LangPack::$items['thing_off'],intval($promotion['promotionDiscount']));
				$tpl->assign('pro_discount_off',$pro_discount_off);
			}
			
			/**
			 * 获取商品运费
			 */
			
			$item_shipping_data = array(
				'categoryId'=>isset($result['productDetails']['productCategory']['categoryId']) ? $result['productDetails']['productCategory']['categoryId'] : 0,
				'price'=>isset($productsPrice['salePrice']) ? $productsPrice['salePrice'] : 0,
				'weight'=>isset($result['productDetails']['productsWeight']) ? $result['productDetails']['productsWeight'] : 0,
				'countryId'=>$countryId,
				'languageCode'=>SELLER_LANG,
				'priceUnit'=>CurrencyCode,
				'num'=>1,
			);
			if(isset($promotion['promotionType'])){
				$item_shipping_data['promotionType'] = $promotion['promotionType'];
				$item_shipping_data['superposition'] = $promotion['superposition'];
			}			
			$promotionType = R::getParams('promotionType');
			if($promotionType){
				$item_shipping_data['promotionType'] = $promotionType;		
				$item_shipping_data['superposition'] = intval(R::getParams('superposition'));
			}
			/**
			 * blog数据读取
			 */
			/*$blogListNum = 5;
			$mem = \Lib\Cache::init();
			$cacheBlogKey = md5(SELLER_LANG.$productId).'_blog';
			$blogList = $mem->get($cacheBlogKey);
			if(!$blogList){
				$blogList = \Helper\Blog::getBlogContent($blogListNum);
				$mem->set($cacheBlogKey,$blogList,0,60);
			}*/
			/**
			 * 商品细节图
			 */
			if(isset($result['productDetails']['pictureDetails'])){
				$pictureDetails = $result['productDetails']['pictureDetails'];
				$picture_details = json_decode($pictureDetails['picture'],true);
				$template_content = $pictureDetails['template_content'];
				foreach($picture_details as $key=>$v){
					//$product_details_url = CDN_ROOT;
					$product_details_url = CDN_UPLOAD_URL."upload/images/";
					$img_html = "<img src='".$product_details_url."d/".$v."'>";
					$template_content = str_replace("{".$key."}", $img_html, $template_content);
				}
				$template_content = preg_replace('/<p>.*?<\/p>/is', "", $template_content);
				$tpl->assign('template_content',$template_content);
			}
			//$tpl->assign('blogList',$blogList);
			
			
			/**
			 * 最小购买数量提示
			 */
			
			$min_products_nums_tips = sprintf(\LangPack::$items['min_products_nums_tips'],$result['productDetails']['tied']); 
			$min_products_nums = sprintf(\LangPack::$items['min_products_nums'],$result['productDetails']['tied']);
			$tpl->assign('min_products_nums_tips',$min_products_nums_tips);
			$tpl->assign('min_products_nums',$min_products_nums);
			
			
			$video_products_id = array('7706','186626');
			$video_products_flag = false;
			if(in_array($productId,$video_products_id)){
				$video_products_flag = true;
			}
			$tpl->assign('video_products_flag',$video_products_flag);
			/**
			 * 获取定制参数价格和定制属性价格区间值
			 */
			if($madePrice['max'] > 0){
				$initMadePrice = $this->initMadePrice($madePrice);
				$tpl->assign('initMadePrice',$initMadePrice);
			}
			
			/**
			 * 屏蔽版权标记需求10970
			 * @var unknown_type
			 */
			if(isset($result['productDetails']['photoCopyright']) && $result['productDetails']['photoCopyright']==1){
				$result['productDetails']['photoCopyright'] = 0;
			}
			
			/**
			 * 获取终端页的礼品
			 * @var unknown_type
			 */
			$categoryCode = $pObject->getCategoryCode();
			$giftObject = new \helper\Gifts($productId,$productsPrice['US_Price'],$categoryCode);
			if($giftObject->checkGiftStatus()){
				$giftList = $giftObject->getProductsGift();
				$giftId = $giftObject->getGiftId();
				$tpl->assign('giftList',$giftList);
				$tpl->assign('giftId',$giftId);
				//print '<pre>';print_r($giftList);exit;
			}
			
			$shippingData = $this->getItemShipping($item_shipping_data);
			$tpl->assign('shippingData',$shippingData);
			$tpl->assign('countryId',$countryId);
			//$tpl->assign('ajax_shipping_params',$ajax_shipping_params);
			$tpl->assign('propertyCheckboxText',$propertyCheckboxText);
			$tpl->assign('size_count',$size_count);
			$tpl->assign('customMutexIdsJson',$customMutexIdsJson);//定制属性互斥
			$tpl->assign('one_size_array',$one_size_array);
			$tpl->assign('property_data',$property_data); //商品的SKU属性
			$tpl->assign('customProperty_status',$customProperty_status);  //是否有定制属性
			$tpl->assign('customProperty',$customProperty);  //是否有定制属性
			$tpl->assign('products_custom_status',$products_custom_status); //是否有custom-made属性
			if(isset($custom_temp)){
				$tpl->assign('custom_temp',$custom_temp);
			}

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
			$tpl->assign('productPicturesCount',$productPicturesCount); //商品图片的总数量
			$tpl->assign('productPictureType',$productPictureType);  //商品图片的类型
			$tpl->assign('reviewsCount',$reviewsCount);	//评论总数
			$tpl->assign('productsPrice',$productsPrice);  //商品的价格
			$tpl->assign('productsScore',$products_score);	//商品的评分
			$tpl->assign('productsDetails',$result['productDetails']);	
			/**
			 * 获取登陆的用户名和邮箱
			 */
			$tpl->assign('login_user',isset($_SESSION[SESSION_PREFIX . "MemberUserName"]) ? $_SESSION[SESSION_PREFIX . "MemberUserName"] : '');
			$tpl->assign('login_user_email',isset($_SESSION[SESSION_PREFIX . "MemberEmail"]) ? $_SESSION[SESSION_PREFIX . "MemberEmail"] : '');
		}else{
			//echo 'ooxx';exit;
			include(ROOT_PATH.'errors/notfound.php');
			return;
		}
		/******************************************************************/
		/*                        获取商品详细信息结束                       */
		/******************************************************************/
		
		//=================================================
		//+---add by chengjun 2011-12-05 中部描述，模板等等---+
		//=================================================
		if(!empty($result) && $result['code']==0){
			if(!empty($result['productDetails'])){
				//商品简介
				$introduce = !empty($result['productDetails']['introduce']) ? nl2br(H::strDosTrip($result['productDetails']['introduce'])) : '';
				//商品介绍
				$productsIntroduction = !empty($result['productDetails']['productsIntroduction']) ? nl2br(H::strDosTrip($result['productDetails']['productsIntroduction'])) : '';
				//批发
				$wholesales = array();
				$wholesalesPriceMax = 0;
				$wholesalesPriceMin = 0;
				if(!empty($result['productDetails']['wholesales'])){
					foreach($result['productDetails']['wholesales'] as $k=>$v){
						if($v['wholesaleLevel']==1){
							//最高批发价
							$wholesalesPriceMax = \Lib\common\Language::priceByCurrency($v['wholesalePrice']);
						}
						if($v['wholesaleLevel']==count($result['productDetails']['wholesales'])){
							//最低批发价
							$wholesalesPriceMin = \Lib\common\Language::priceByCurrency($v['wholesalePrice']);
						}
						$v['wholesalePrice'] = \Lib\common\Language::priceByCurrency($v['wholesalePrice']);
						$v['discount'] = ($v['discount'] <= 1) ? round((1-$v['discount']),2)*100 : 0;
						$wholesales[$k] = $v;
					}
				}
				//商品自定义属性 非尺码
				$productPropertys = array();
				if(!empty($result['productDetails']['productPropertys'])){
					$result['productDetails']['productPropertys'] = H::strDosTrip($result['productDetails']['productPropertys']);
					foreach($result['productDetails']['productPropertys'] as $k=>$v){
						if(!empty($v['propertyOption']) && $v['isSizeChart']==0 && $v['propertyType']!='checkbox_text'){
							$productPropertys[$v['propertyName']]['num'] = count($v['propertyOption']);
							foreach($v['propertyOption'] as $v2){
								if(!empty($v2['configurationContent'])){
									$productPropertys[$v['propertyName']][] = $v2['configurationName'] . ':' . $v2['configurationContent'];
								}else{
									$productPropertys[$v['propertyName']][] = $v2['configurationName'];
								}
							}
						}
					}
				}
				//商品重量
				$weight = 0;
				if(isset($result['productDetails']['productsWeight'])){
					$weight = $result['productDetails']['productsWeight'];
				}
				
				//上架事件15天内的产品增加new标记
				$newProductTag = 0;
				if(!empty($result['productDetails']['productsAddTime'])){
					if((time()-$result['productDetails']['productsAddTime'])<=15*24*3600){
						$newProductTag = 1;
					}
				}
				
				$tpl->assign('productsWeight',$weight);
				$tpl->assign('newProductTag',$newProductTag);
				$tpl->assign('wholesalesPriceMax',$wholesalesPriceMax);
				$tpl->assign('wholesalesPriceMin',$wholesalesPriceMin);
				$tpl->assign('introduce',$introduce);
				$tpl->assign('productsIntroduction',$productsIntroduction);
				$tpl->assign('wholesales',$wholesales);
				$tpl->assign('productPropertys',$productPropertys);
			}
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
			
			 // 表示存在混合属性
			$tpl->assign('productMixedPropertys_status',(!empty($productMixedPropertys) && count($productMixedPropertys)>1) ? 1 : 0);
			//end
			$isDisplayMixedSizeChart = 0;//默认显示标准尺码表
			$displayContent = '';
			$productTheme = '';
			$parentCategories=H::cat_ga_custom_var($result['productDetails']['productCategory'],2);
			$parentCategories=explode(',',$parentCategories);
			$modulePath = THEME.'TemplateGoods/';
			$productTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/'.$result['productDetails']['productsTheme'];
			//商品模块信息
			$mem = \Lib\Cache::init();
			$cacheKey = md5(SELLER_LANG.$productId).'_proTheme';
			$temp = $mem->get($cacheKey);
			if(empty($temp)){
				//return policy模板路径
				$returnPolicyTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/kindly.htm';
				//输出到缓冲
				ob_start();	
				$tpl->display($returnPolicyTheme);
				$displayContent = ob_get_clean();
				ob_end_clean();
				$mem->set($cacheKey,$displayContent,0,60);
			}
			
			if(empty($temp)){
				$tpl->assign('cacheTheme_1',$displayContent);
			}else{
				$tpl->assign('cacheTheme_1',$temp);
			}
			$tpl->assign('productTheme',$productTheme);
			$tpl->assign('isDisplayMixedSizeChart',$isDisplayMixedSizeChart);//1:显示混合属性尺码，0：显示标准尺码
		}
		
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
		
		//信息返回标记，如果此标记为真，则说明有接口需调用商品信息 add by chengjun 2012-02-09
		$ajaxGetInfoMark = R::getParams('ajaxGetInfoMark');
		if(isset($ajaxGetInfoMark) && $ajaxGetInfoMark===1){
			return $result;
		}
		
		//------------------------------QA---------------------------------
		$qa = new \Model\Qa();
		$qa_data = array(
			'productId'=>$productId,
			'pageSize'=>5,
			'typeId'=>0,
		);
		$qa_result = $qa->getQAGroupByType($qa_data);
		if(isset($qa_result['code']) && $qa_result['code']==0){
			$qa_count = $qa_result['QA'][0]['totalCount'];
			$qa_count_str = sprintf(\LangPack::$items['qa_count'],$qa_count);
			$tpl->assign('qa_count_str',$qa_count_str);
			$tpl->assign('qa_count',$qa_count);
			$tpl->assign('qa_list',$qa_result['QA'][0]['productCommentList']);
		}
		//------------------------------QA---------------------------------
		
		
		/**
		 * 面包屑
		 */
		$breadcrumb = new \Model\Product(); 
		$search_array = array(
			'pcs.categoryId'=>intval($result['productDetails']['productCategory']['categoryId']),
		);
		$bread_result = $breadcrumb->getProductList($search_array);
		if($bread_result['code'] == 0){
			if(isset($bread_result['categoryBreadcrumbNavigation']['categoryId'])){
				setcookie('milanoo_cc',$bread_result['categoryBreadcrumbNavigation']['categoryId'],time()+1200,'/');
				setcookie('milanoo_cn',stripcslashes($bread_result['categoryBreadcrumbNavigation']['categoryName']),time()+1200,'/');
			}
			$tpl->assign('result',$bread_result);
		}
		//GA数据
		$productCategory=$tpl->_tpl_vars['productsDetails']['productCategory'];		
		$ga_custom_var=H::get_ga_custom_var('item',$productCategory,0,$productId);
		$tpl->assign('ga_custom_var',$ga_custom_var);
		//GA数据 End
		$tpl->display('thing_item.htm');


	}

	
	/**
	 * 获取定制价格的一个区间值
	 * @param 所有定制类价格的集合 $madePrice
	 * @return 一个最大值和最小值的价格数组
	 */
	function initMadePrice($madePrice){
		$price = array(
			'max'=>$madePrice['max'],
			'min'=>0,		
		);
		if(count($madePrice['min']) > 0){
			$minMadePrice = $madePrice['min']; 
			sort($minMadePrice);
			$price['min'] = $minMadePrice[0];
		}else{
			$price['min'] = 0;
		}
		return $price;
	}
	
	/***ADD 留言 评论***/
	function add_message($productsName) {
		$MemberId = isset($_SESSION[SESSION_PREFIX . "MemberId"])?$_SESSION[SESSION_PREFIX . "MemberId"]:0;
		$WebsiteId=R::getParams('websiteId');
		$WebsiteId = !empty($WebsiteId)?$WebsiteId:1;
		$id = R::getParams('id');
		$url = rew::rewrite(array('url'=>'?module=thing&action=item&id='.$id,'isxs'=>'no','seo'=>$productsName));
		$Title = R::getParams('title');
		$Content = R::getParams('Ucontent');
		if (!$Content)
			JS::alertForward('noContent', '', '1');
		
		$VCode = strtolower(R::getParams('VCode'));
		$VCodezx = strtolower(R::getParams('VCodezx'));

		if (!isset($_SESSION['captcha']['pl']) || $VCode != $_SESSION['captcha']['pl'] )
			JS::alertForward('nocode', '', '1');
		if(isset($_SESSION[SESSION_PREFIX . "MemberId"])){
			$temp_username=substr($_SESSION[SESSION_PREFIX.'MemberEmail'],0,strpos($_SESSION[SESSION_PREFIX.'MemberEmail'],'@'));
			$temp_username=!empty($_SESSION[SESSION_PREFIX.'MemberUserName'])?$_SESSION[SESSION_PREFIX.'MemberUserName']:$temp_username;
			$Uname=$temp_username;
		}else{
			$Uname = R::getParams('Uname');
		}	
		if (empty($Uname)){
			$Uname = R::getParams('Uname');
		}
		if (empty($Uname))
			JS::alertForward('noname1', '', '1');			
		
		if(isset($_SESSION[SESSION_PREFIX . "MemberId"])){
			$Uemail = $_SESSION [SESSION_PREFIX . "MemberEmail"];
		}else{
			$Uemail = R::getParams('Uemail');
		}
		if (!$Uemail && !$MemberId){
			JS::alertForward('email1', '', '1');
		}
		if(isset($_FILES['files'])){
			$uploadFiles = array();
			$myFiles  = $_FILES['files'];
			$fileCount = count($myFiles['name']);
			if($fileCount > 0){
				for($i=0;$i<$fileCount;$i++){
					if(isset($myFiles['tmp_name'][$i]) && !empty($myFiles['tmp_name'][$i])){
						$resultUpload = \Helper\Upload::imageUpload($myFiles['tmp_name'][$i],$myFiles['size'][$i],$myFiles['name'][$i]);
						if($resultUpload == 10000){
							JS::alertForward('file_type_error', '', '1');
						}
						if($resultUpload == 10001){
							JS::alertForward('file_size_error', '', '1');
						}
						if(isset($resultUpload['filePath'])){
							$uploadFiles[] = $resultUpload['filePath'];
						}
					}
				}
			}
		}
		
	
			
		$vote = R::getParams('vote');
		// -------------发表评论--------------
		$comment_obj=new \Model\Comment ();
		$comment_info['pc.productId']=$id;
		$comment_info['pc.commentTitle']=$Title;
		$comment_info['pc.commentContent']=$Content;
		$comment_info['pc.productScore']=$vote;
		$comment_info['pc.memberId']=$MemberId;
		$comment_info['pc.memberName']=$Uname;
		$comment_info['pc.memberEmail']=$Uemail;
		$comment_info['pc.userIp']=R::getClientIp();
		$comment_info['pc.userCountry']=isset($_SERVER['HTTP_X_REAL_COUNTRY'])?$_SERVER['HTTP_X_REAL_COUNTRY']:'us';
		$comment_info['pc.webSiteId']=$WebsiteId;
		$comment_info['pc.languageCode']=SELLER_LANG;
		$comment_info['pc.gmtCreate']=date('y-n-j H:i:s',(time() - date('Z'))+($_COOKIE['Timezone']*3600));
		if(count($uploadFiles) > 0){
			$comment_info['pc.commentPictureUrlArr'] = json_encode($uploadFiles);
		}
		$result=$comment_obj->createComment($comment_info);
		if($result['code']==0){
			JS::alertForward('advisoryOk', $url, '1');
		}else{
			JS::alertForward('submit_failed', '', '1');
		} 
		exit();
	}
	
	/***ADD Q&A***/
	function add_qa($productsName) {
		$MemberId = isset($_SESSION[SESSION_PREFIX . "MemberId"])?$_SESSION[SESSION_PREFIX . "MemberId"]:0;
		$WebsiteId=R::getParams('websiteId');
		$WebsiteId = !empty($WebsiteId)?$WebsiteId:1;
		$id = R::getParams('id');
		$url = rew::rewrite(array('url'=>'?module=thing&action=item&id='.$id,'isxs'=>'no','seo'=>$productsName));
		$Title = R::getParams('qa_title');
		$Content = R::getParams('qa_content');
		if (!$Content || !$Title){
			JS::alertForward('noContent', '', '1');
		}
		
		$VCode = strtolower(R::getParams('VCode'));
		if (!isset($_SESSION['captcha']['qa']) || $VCode != $_SESSION['captcha']['qa'] ){
			JS::alertForward('nocode', '', '1');
		}
		if(isset($_SESSION[SESSION_PREFIX . "MemberId"])){
			$temp_username=substr($_SESSION[SESSION_PREFIX.'MemberEmail'],0,strpos($_SESSION[SESSION_PREFIX.'MemberEmail'],'@'));
			$temp_username=!empty($_SESSION[SESSION_PREFIX.'MemberUserName'])?$_SESSION[SESSION_PREFIX.'MemberUserName']:$temp_username;
			$Uname=$temp_username;
		}else{
			$Uname = R::getParams('qa_name');
		}	
		if (empty($Uname))
			JS::alertForward('noname1', '', '1');			
		
		if(isset($_SESSION[SESSION_PREFIX . "MemberId"])){
			$Uemail = $_SESSION [SESSION_PREFIX . "MemberEmail"];
		}else{
			$Uemail =  R::getParams('qa_mail');
		}
		if (!$Uemail && !$MemberId){
			JS::alertForward('email1', '', '1');
		}
		
	
			
			
		//$vote = R::getParams('vote');
		// -------------发表评论--------------
		$qa_obj=new \Model\Qa ();
		$qa_info['pc.productId']=$id;
		$qa_info['pc.commentTitle']=$Title;
		$qa_info['pc.commentContent']=$Content;
		$qa_info['pc.type']=0;
		//$qa_info['pc.productScore']=$vote;
		$qa_info['pc.memberId']=$MemberId;
		$qa_info['pc.memberName']=$Uname;
		$qa_info['pc.memberEmail']=$Uemail;
		$qa_info['pc.userIp']=R::getClientIp();
		$qa_info['pc.userCountry']=isset($_SERVER['HTTP_X_REAL_COUNTRY'])?$_SERVER['HTTP_X_REAL_COUNTRY']:'CN';
		$qa_info['pc.webSiteId']=$WebsiteId;
		$qa_info['pc.languageCode']=SELLER_LANG;
		$qa_info['pc.gmtCreate']=date('y-n-j H:i:s',(time() - date('Z'))+($_COOKIE['Timezone']*3600));
		$qa_info['pc.isComment']=1;
		$qa_info['pc.qaStatus']=0;
		$qa_info['pc.rewardStatus']=0;
		$qa_info['pc.dataStatus']=2;
		$qa_info['pc.clickSum']=0;
		$qa_info['pc.usefulSum']=0;
		$result=$qa_obj->addQA($qa_info);
		
		if($result['code']==0){
			JS::alertForward('advisoryOk', $url, '1');
		}else{
			JS::alertForward('submit_failed', '', '1');
		} 
		exit();
	}
	
	/**
	 * 设置浏览记录。最多3条，无重复,并且第一个值始终是最新值
	 * (刷新当前页面不能将当前ID显示在浏览记录中，因此增加一条记录临时存储当前ID)
	 */
	public function setHistoryProducts(){
		$productId = R::getParams('id');
		if(!$productId) return false;
		$historyProducts = array();
		if(!empty($_COOKIE['historyView'])){
			$historyProducts = $this->getHistoryProducts();
			if(in_array($productId,$historyProducts)) {
				//如果当前ID已经存在于记录中，则将该ID记录提前并置于隐藏记录
				$key = array_search($productId, $historyProducts);
				if($key==0){//表示刷新当前页面，记录不作修改
					return false;
				}
				array_splice($historyProducts, $key,1);//移除该条记录
				array_unshift($historyProducts, $productId);//将该条记录提前
			}elseif(count($historyProducts)<4){
				//如果记录不足四条，则将最新记录写入数组开头
				array_unshift($historyProducts, $productId);
			}elseif(count($historyProducts)==4){
				array_pop($historyProducts);//移除数组末尾最老的记录
				array_unshift($historyProducts, $productId);//写入最新的记录到数组开头
			}
		}else{
			$historyProducts[] = $productId;
		}
		$historyJsonData = json_encode($historyProducts);
		if(!empty($_COOKIE['historyView'])){
			$_COOKIE['historyView'] = $historyJsonData;//修改当前页的cookie值
		}
		setcookie('historyView',$historyJsonData,time()+60*60*24*30,'/');//30天过期
		return true;
	}

	/**
	 * 获取历史浏览记录
	 */
	public function getHistoryProducts(){
		$historyProducts = array();
		if(!empty($_COOKIE['historyView'])){
			$historyProducts = json_decode($_COOKIE['historyView']);
		}
		return $historyProducts;
	}
	
	/**
	 * 获取页面refer信息，来路域名，来路关键词等等
	 * 目前处理google yahoo bing search aol yandex ask
	 */
	public function getRefer(){
		$referer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		if(!empty($referer)){//跳转进入页面
			$referInfo = array();
			$hostName = '';
			$referer_param = parse_url($referer);
			$referHost = $referer_param['host'];
			$referQuery = !empty($referer_param['query']) ? $referer_param['query'] : '' ;
			if(!empty($referHost)){
				$referInfo['host'] = $referHost;
				$domain = array('com','cn','name','org','net');
				$domainReg = implode('|', $domain);
				if(preg_match('/(?<![\w-])([\w-]+)(?=\.'.$domainReg.')(?:.*)$/', $referHost, $match)){
					$referInfo['hostname'] = $match[1];
					$hostName = $match[1];
				}
			}
			if($hostName === 'google'){//谷歌搜索
				if(!empty($referQuery)){
					parse_str ($referQuery,$referQueryArray);
					$referInfo['keyword'] = urldecode($referQueryArray['q']);
				}
				return $referInfo;
			}elseif($hostName=='yahoo'){
				if(!empty($referQuery)){
					parse_str ($referQuery,$referQueryArray);
					$referInfo['keyword'] = urldecode($referQueryArray['p']);
				}
				return $referInfo;
			}elseif($hostName=='bing'){
				if(!empty($referQuery)){
					parse_str ($referQuery,$referQueryArray);
					$referInfo['keyword'] = urldecode($referQueryArray['q']);
				}
				return $referInfo;
			}elseif($hostName=='search'){
				if(!empty($referQuery)){
					parse_str ($referQuery,$referQueryArray);
					$referInfo['keyword'] = urldecode($referQueryArray['q']);
				}
				return $referInfo;
			}elseif($hostName=='aol'){
				if(!empty($referQuery)){
					parse_str ($referQuery,$referQueryArray);
					$referInfo['keyword'] = urldecode($referQueryArray['q']);
				}
				return $referInfo;
			}elseif($hostName=='yandex'){
				if(!empty($referQuery)){
					parse_str ($referQuery,$referQueryArray);
					$referInfo['keyword'] = urldecode($referQueryArray['text']);
				}
				return $referInfo;
			}elseif($hostName=='ask'){
				if(!empty($referQuery)){
					parse_str ($referQuery,$referQueryArray);
					$referInfo['keyword'] = urldecode($referQueryArray['q']);
				}
				return $referInfo;
			}else{
				//有refer但没有关键词
				return '';
			}
		}else{//直接输入地址进入页面
			return true;
		}
	}
	
	/**
	 * 去掉反斜杠和html实体
	 */
	function dostrip($value) {
		if (is_array ( $value )) {
			$value = array_map ( 'self::dostrip', $value );
		} else {
			$value = stripslashes ( $value );
			$value = htmlspecialchars_decode ( $value );
		}
		return $value;
	}
	
	/**
	 * 自定义参数的html返回
	 * @param 参数信息 array $data
	 * @param 商品状态 int  $status
	 * @param 参数顺序 $index_sort
	 * @return multitype:string 返回html代码
	 */
	function getHtmlTag($data,$status,$index_sort){
		$html_string = array(
			'text'=> $data['parameterName'],
			'index'=>strtoupper(chr($index_sort)),
		);
		$html = "";
		$type = $data['parameterType'];
		$disabled = $status == 0 ? " disabled='true' " : '';
		switch ($type){
			case 1:
				$html .= "<input type='text' numpass:type='float' class='numpass'";
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
				$html_string['unit'] = intval($data['parameterUnit']) == 1 ? '<label class="custom_unit">cm</label>' : '<label class="custom_unit_kg">kg</label>';
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
						$html.= "<input type='radio' name='Customszie[".$data['parameterKey']."__".$index_sort."]' value='".$v['optionKey']."'>&nbsp;".$v['optionName']."&nbsp;&nbsp;";
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
						$html.= "<option value='".$v['optionKey']."'>".$v['optionName']."</option>";
					}
					$html .= "</select>";
					$html_string['tag_type'] = 'select';
				}	
			break;
		}
		$html_string['html'] = $html;
		return $html_string;
	}
	/**
	 * 
	 * 获取售前的分类
	 */
	public  function getAdvisoryCategory()
	{
		//咨询类型：咨询、投诉
		$data = array();
		$data['inquiryType'] = 'Consultation';	
		$InquiryModel = new \model\Inquiry();
		$result = $InquiryModel->getAdvisoryCategory($data);
		return $result['inquiryCategories'];
	}
	/**
	*	来路分析
	*/
	public function parse_referer(){
		if(!isset($_SERVER['HTTP_REFERER']))return;
		global $tpl;
		$referer_type=$referer_id='';
		$ar=parse_url($_SERVER['HTTP_REFERER']);
		if(isset($ar['query'])){//搜索页
			parse_str($ar['query']);
			if(isset($keyword)&&isset($ClassId)&&$ClassId!=0){//搜索页且是有分类id
				$referer_type='search';
				$referer_id=$ClassId;
			}
		}else{//其他页面进入的
			if(preg_match('/-c(\d{3,})\/?/i',$ar['path'])){//分类页进入
				preg_match_all('/-c(\d{3,})\/?/i',$ar['path'],$match_ar);
				$referer_type='category';
				$referer_id=$match_ar[1][0];
			}
		}
		$tpl->assign('referer_type',$referer_type);
		$tpl->assign('referer_id',$referer_id);
		return;
	}
	
	/**
	 * 获取商品运费
	 * @param array $data
	 */
	public function getItemShipping($data){
		$mProduct = new \Model\Product ();
		$result = $mProduct->getProducsTransportPrice ( $data ); 
		if(isset($result['code']) && $result['code'] == 0 && isset($result['freight'])){
			foreach ($result['freight'] as $v){
				$postArr = explode("-",$v['postTime']);
				if(count($postArr) > 1){
					$min = isset($min) ? $min > $postArr[0] ? $postArr[0] : $min : $postArr[0] ;
					$max = isset($max) ? $max < $postArr[1] ? $postArr[1] : $max : $postArr[1] ;
				}else{
					$min = isset($min) ? $min > $postArr[0] ? $postArr[0] : $min : $postArr[0] ;
					$max = isset($max) ? $max < $postArr[0] ? $postArr[0] : $max : $postArr[0] ;
				}
			}
		}else{
			$min = 0;
			$max = 0;
		}
		return array('min'=>$min,'max'=>$max);
	}
	
	/**
	 *
	 * 根据面包屑获取对应分类ID
	 * @param array $cateBread
	 * @param int level 需要获取分类的层级，1表示顶级，2表示2级，3表示3级，最大为3级
	 */
	public function getCategoryId($cateBread,$level=1){
		if(!empty($cateBread) && !empty($level) && $level<=3){
			$levelCodeLength = $level*5;
			if(isset($cateBread['categoryCode']) && strlen($cateBread['categoryCode'])==$levelCodeLength){
				return $cateBread['categoryId'];
			}else{
				if(isset($cateBread['parentProductCategory'])){
					return $this->getCategoryId($cateBread['parentProductCategory'],$level);
				}
				return false;
			}
		}
		return false;
	}
}