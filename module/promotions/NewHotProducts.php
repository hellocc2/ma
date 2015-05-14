<?php
namespace Module\promotions;
use Model\Product;

use Helper\RequestUtil as RequestUtil;
use Helper\String as HString;
/**
 * 新品热销专区处理
 * FileName:NewHotProducts.php
 * @Author:chengjun <cgjp123@163.com>
 * @Since:2012-3-16
 */
class NewHotProducts extends \Lib\common\Application {      
	
	/**
	 * 
	 * 设定一个变量存储接口数据中的第一个顶级类目ID，以用于充当专区默认首页的类目ID
	 * @var int
	 */
	public $defaultCid = '';
	
	/**
	 * 
	 * 获取当前类目的showstyle设定值，如果没有类目ID，则默认取第一个顶级类目的showstyle
	 * @var string
	 */
 	public $ShowType = '';
 	
 	/**
 	 * 
 	 * 当前类目名
 	 * @var string
 	 */
 	public $className = '';
 	
 	/**
 	 * 
 	 * 子商品分类
 	 * @var string
 	 */
 	public $childCateIds = '';
 	
 	/**
 	 * 
 	 * 当前分类信息
 	 * @var array
 	 */
 	public $currentCatArray= array();
	
 	public function __construct(){
 		$tpl = \Lib\common\Template::getSmarty ();
 		$action = RequestUtil::getParams ( 'action' );
 		$params = RequestUtil::getParams ( 'params' );
 		$type = $params['t'];//展示类型（必需）new 或者hot
 		$classId = $params['c'];//目录ID
 		$viewType = $params['v'];
 		$sort = $params['sort'];//排序方式
 		$sortby = $params['sortby'];//排序方式 ，1 升序  ， 0降序
 		$page = $params['page'];
 		$pageSize = $params['s'];
 		$priceRange = $params['priceRange'];
 		$maxshowReviewWords = 300;
 		$tpl->assign('maxshowReviewWords',$maxshowReviewWords);
 		
 		$result = array();//商品结果集
 		$searchCateParam = array();//类目请求参数集
 		$searchStepParam = array();//集合商品请求参数集
 		$searchListParam = array();//列表商品请求参数集
 		if($type){
 			//取得展示类型
 			if($type=='new'){
 				 $dataType = 1; 
 				 $Nowaction = 'Newarrivals';
 			}elseif($type=='hot'){
 				 $dataType = 2;
 				 $Nowaction = 'Spotlight'; 
 			}else{
 				 exit('wrong type');
 			}
 			$searchParam['dataType'] = $searchCateParam['dataType'] = $dataType;
 			
 			$getProModel = new \Model\Product();
 			
 			//获取类目
 			//因为此类目一般不会变动，采用缓存
 			//memcached初始化
			$mem = \Lib\Cache::init();
			$cacheKey = md5(SELLER_LANG.$dataType).'_newhot';
			$temp = $mem->get($cacheKey);
			if(!empty($temp)){
				$carResult = $temp;
			}else{
 				$carResult = $getProModel->getProductsCate($searchCateParam);
 				$mem->set($cacheKey,$carResult,0,60);
			}
			//print_R($carResult);
 			if($carResult['code']==0 && !empty($carResult['productCategory'])){
 				$carResult['productCategory'] = \Helper\String::strDosTrip($carResult['productCategory']);
 				//组装参数给获取商品列表准备
	 			if($classId){
	 				 $carResult['productCategory'] = $this->categoryProcess($carResult['productCategory'],$classId);
	 				 $this->defaultCid = $classId;
	 				 if($this->ShowType === ''){
	 				 	$this->ShowType = 0;
	 				 }
	 				 //有目录ID表示非首页
	 				 $tpl->assign('indexPage',0);
	 			}else{
 					 $carResult['productCategory'] = $this->categoryProcess($carResult['productCategory']);	
 					 //没有目录ID表示专区首页
 					 if(isset($carResult['productCategoryShowType']) && $carResult['productCategoryShowType']!=''){
 					 	$this->ShowType =  $carResult['productCategoryShowType'];
 					 }
 					 if(!empty($carResult['productCategoryChild'])){
 					 	$this->childCateIds = $carResult['productCategoryChild'];
 					 }
 					 $tpl->assign('indexPage',1);
	 			}
 			}
 			
 			if($this->ShowType==1){//集合
 				$searchStepParam['dbProNum'] = '4';
 				$searchStepParam['allProNum'] = '8';
 				$searchStepParam['categoryId'] = $this->defaultCid;
	 			//$searchStepParam['showType'] = $this->ShowType;
	 			$searchStepParam['dataType'] = $dataType;
	 			$searchStepParam['childCateIds'] = $this->childCateIds;
	 			//获取数据
 				$result = $getProModel->getProductsInStepType($searchStepParam);
 			}elseif($this->ShowType==0){//列表
 				$url = '?module=promotions&action='.$Nowaction.'&c='. $this->defaultCid;
 				
 				//设置列表页默认参数
 				if (! $page) $page = 1; elseif($page!=1) {$page = !intval($page) ? 1 : $page ;}	
 				//设置每页商品数
 				if(!$pageSize){
 					if(isset($_COOKIE['newhotPageSize']) && $_COOKIE['newhotPageSize'] != ''){
 						$pageSize = $_COOKIE['newhotPageSize'];
 						if($pageSize!='24' && $pageSize!='36' && $pageSize!='48'){
 							$pageSize = 36;
 						}
 					}else{
 						$pageSize = 36;
 					}
 				}else{
 					if($pageSize!='24' && $pageSize!='36' && $pageSize!='48'){
						$pageSize = 36;
					}
					setcookie('newhotPageSize',$pageSize,0,'/');
 				}
 				//设置展示模式
 				if(empty($viewType)){
	 				if (isset ( $_COOKIE ['newhotViewType'] ) && $_COOKIE ['newhotViewType'] != '') {
						$viewType = $_COOKIE ['newhotViewType'];
						if($viewType!=='list' && $viewType!=='text' && $viewType!=='grid' && $viewType!=='stream'){
							$viewType = 'text';
						}
					}else{
						$viewType = 'text';
					}
 				}else{
 					if($viewType!=='list' && $viewType!=='text' && $viewType!=='grid' && $viewType!=='stream'){
						$viewType = 'text';
					}
					setcookie('newhotViewType',$viewType,0,'/');
 				}
 				//排序
	 			if (!empty($sort)) {
					if($sort!=='recommend' && $sort!=='addedTime' && $sort!=='sortPrice'){
						$sort = 'recommend';
						$sortby = 0;
					}
					if(empty($sortby)) $sortby = 0;
					$sortString = $sort . ':' . $sortby;
					$url .= '&sort=' .$sort.'&sortby='.$sortby;
					$searchListParam['sortObject'] = $sortString;
					$tpl->assign('sort',$sort);
					$tpl->assign('sortby',$sortby);
				}else{
					if($dataType==1){
						$sort = 'addedTime';
						$sortby = 0;
					}
					$tpl->assign('sort',$sort);
					$tpl->assign('sortby',$sortby);
				}
				//价格区间
				$urlForm = $url;
				$searchPrice = $params['searchPrice'];//价格搜索标记
 				if (isset($searchPrice) && $searchPrice==1) {
					$priceRangeMin = $params['priceRange_min'];
					$priceRangeMax = $params['priceRange_max'];
					//$price_param = explode ( '&', $price_param [1] );
					//foreach ( $price_param as $val ) {
					//	$price [] = explode ( '=', $val );
					//}
					//priceRange
					$priceIn = true;
					if ($priceRangeMin == '' && $priceRangeMax != '') {
						$priceRangeMin = 0;
					} elseif ($priceRangeMin != '' && $priceRangeMax == '') {
						$priceRangeMax = 0;
					} elseif ($priceRangeMin == '' && $priceRangeMax == '') {
						$priceIn = false;
					}
					if(strlen($priceRangeMin)>8) $priceRangeMin = substr($priceRangeMin,0,8);
					if(strlen($priceRangeMax)>8) $priceRangeMax = substr($priceRangeMax,0,8);
					if($priceRangeMin<0) $priceRangeMin = 0;
					if($priceRangeMax<0) $priceRangeMax = 0;
					if($priceRangeMin==0 && $priceRangeMax==0){//不能同时为0
						$priceIn = false;
					}
					if ($priceIn) {
						$priceRangeMin = floatval ( $priceRangeMin );
						$priceRangeMax = floatval ( $priceRangeMax );
						if($priceRangeMax==0) $priceRangeMax = '*';
						if($priceRangeMin==0){
							$tpl->assign ( 'priceRange_min', $priceRangeMin );
							$tpl->assign ( 'priceRange_max', $priceRangeMax );
							$str_url = $priceRangeMin . ':' . $priceRangeMax;
							$str = \Lib\common\Language::priceByCurrency ( $priceRangeMin, 'USD', CurrencyCode ) . ':' . \Lib\common\Language::priceByCurrency ( $priceRangeMax, 'USD', CurrencyCode );
						}elseif($priceRangeMax=='*'){
							$tpl->assign ( 'priceRange_min', $priceRangeMin );
							$tpl->assign ( 'priceRange_max', '');
							$str_url = $priceRangeMin . ':' . $priceRangeMax;
							$str = \Lib\common\Language::priceByCurrency ($priceRangeMin, 'USD', CurrencyCode ) . ':' . $priceRangeMax;
						}elseif ($priceRangeMin < $priceRangeMax) {
							$tpl->assign ( 'priceRange_min', $priceRangeMin );
							$tpl->assign ( 'priceRange_max', $priceRangeMax );
							$str_url = $priceRangeMin . ':' . $priceRangeMax;
							$str = \Lib\common\Language::priceByCurrency ( $priceRangeMin, 'USD', CurrencyCode ) . ':' . \Lib\common\Language::priceByCurrency ( $priceRangeMax, 'USD', CurrencyCode );
						} else {
							$tpl->assign ( 'priceRange_min', $priceRangeMax );
							$tpl->assign ( 'priceRange_max', $priceRangeMin );
							$str_url = $priceRangeMax . ':' . $priceRangeMin;
							$str = \Lib\common\Language::priceByCurrency ( $priceRangeMax, 'USD', CurrencyCode ) . ':' . \Lib\common\Language::priceByCurrency ( $priceRangeMin, 'USD', CurrencyCode );
						}
						$searchListParam ['priceRang'] = $str;
						$url .= '&priceRange=' . $str_url;
					}
				} else {
					if ($priceRange != '') {
						$price = explode ( ':', $priceRange );
						$tpl->assign ( 'priceRange_min', $price [0] );
						if($price [1]=='*'){
							$tpl->assign ( 'priceRange_max', '' );
							$searchListParam ['priceRang'] = \Lib\common\Language::priceByCurrency ( $price [0], 'USD', CurrencyCode ) . ':' . $price [1];
						}else{
							$tpl->assign ( 'priceRange_max', $price [1] );
							$searchListParam ['priceRang'] = \Lib\common\Language::priceByCurrency ( $price [0], 'USD', CurrencyCode ) . ':' . \Lib\common\Language::priceByCurrency ( $price [1], 'USD', CurrencyCode );
						}
						$str_url = $priceRange;
						$url .= '&priceRange=' . $priceRange;
					}
				}
				//价格区间搜索，302跳转
				if (isset($searchPrice) && $searchPrice==1){
					$priceJumpUrl = \Helper\ResponseUtil::rewrite(array('url'=>$url,'seo'=>$this->className,'isxs' => 'no'));
					header('HTTP/1.1 302 Moved Permanently');//发出302头部
					header('Location:'.$priceJumpUrl);
					exit();
				}
 				
 				$searchListParam['categoryId'] = $this->defaultCid;
 				$searchListParam['dataType'] = $dataType;
 				$searchListParam['pageNo'] = $page;
 				$searchListParam['pageSize'] = $pageSize;
 				//获取数据
 				$result = $getProModel->getProductsInListType($searchListParam);
 			}
 			
 			$totalCount = 0;
 			//print_r($result);
 			if(!empty($result) && $result['code']==0 && !empty($result['products'])){
 				$result['products'] = \Helper\String::strDosTrip($result['products']);
 				if($this->ShowType==1){//集合模式处理
	 				foreach($result['products'] as $k=>$v){
	 					if(!empty($v['productList'])){
	 						foreach($v['productList'] as $key=>$val){
	 							//处理价格
	 							if(!empty($val['productPrice'])){
	 								$result['products'][$k]['productList'][$key]['productPrice'] = \Lib\common\Language::priceByCurrency($val['productPrice']);
	 							}
	 							if(!empty($val['promotionsPrice'])){
	 								$result['products'][$k]['productList'][$key]['promotionsPrice'] = \Lib\common\Language::priceByCurrency($val['promotionsPrice']);
	 							}
	 							if(!empty($val['promotionMidPic'])){
	 								$result['products'][$k]['productList'][$key]['promotionMidPic'] = substr($val['promotionMidPic'], 1);
	 							}
	 							//处理星级
		 						if(!empty($val['stars'])) {
									$result['products'][$k]['productList'][$key]['stars'] = $val['stars']*20;
								}else{
									$result['products'][$k]['productList'][$key]['stars'] = 0;
								}
	 						}
	 					}
	 				}
 				}elseif($this->ShowType==0){//列表模式处理
 					$totalCount = $result['totalCount'];
 					foreach($result['products'] as $k=>$v){
 						//处理价格
	 					if(!empty($v['productPrice'])){
	 						$result['products'][$k]['productPrice'] = \Lib\common\Language::priceByCurrency($v['productPrice']);
	 					}
	 					if(!empty($v['promotionsPrice'])){
	 						$result['products'][$k]['promotionsPrice'] = \Lib\common\Language::priceByCurrency($v['promotionsPrice']);
	 					}
 						//处理星级
 						if(!empty($v['stars'])) {
							$result['products'][$k]['stars'] = $v['stars']*20;
						}else{
							$result['products'][$k]['stars'] = 0;
						}
						//多图处理
						if(!empty($v['imgs'])){
							//去掉重复的图片
							$v['imgs'] = array_unique($v['imgs']);
							$upenL = array();
							$upenM = array();
							foreach($v['imgs'] as $kimg=> $vimg){
								$upenL[] = CDN_UPLAN_URL.'upen/l/'.$vimg;
								$upenM[] = CDN_UPLAN_URL.'upen/m/'.$vimg;
							}
							$result ['products'][$k]['imgstringLsize'] = implode(',',$upenL);
							$result ['products'][$k]['imgstringMsize'] = implode(',',$upenM);
						}
						
						//评论处理
						if(!empty($v['reviewList'])){
							$result ['products'][$k]['reviewList'] = \Helper\String::strDosTrip($v['reviewList']);
						}
						
						//强制屏蔽高清图标记,需求10970
						if(!empty($v['productPictureType'])){
							$result ['products'][$k]['productPictureType'] = '';
						}
 					}
 					//处理列表模式左边商品
 					if(!empty($result['leftProducts'])){
 						foreach($result['leftProducts'] as $key=>$val){
 							if(!empty($val['productPrice'])){
 								$result['leftProducts'][$key]['productPrice'] = \Lib\common\Language::priceByCurrency($val['productPrice']);
 							}
 							if(!empty($val['promotionsPrice'])){
 								$result['leftProducts'][$key]['promotionsPrice'] = \Lib\common\Language::priceByCurrency($val['promotionsPrice']);
 							}
 						}
 						$tpl->assign('leftProductlist',$result['leftProducts']);
 					}
 				}
 				$tpl->assign('empty',0);
 				$tpl->assign('productlist',$result['products']);
 			}else{
 				$tpl->assign('empty',1);//数据为空
 				$tpl->assign('indexPage',1);//为面包屑做判断用
 				$tpl->assign('Nowaction',$Nowaction);//
 				$tpl->assign('category',$carResult['productCategory']);
 				$tpl->assign ( 'current_page', $page);
 				$tpl->display('newhotlist.htm');
 				return ;
 			}
 			
 			//输出SEO信息
 			if(!empty($this->currentCatArray)){
 				$seoTitle = isset($this->currentCatArray['seoTitle']) ? $this->currentCatArray['seoTitle'] : $this->className ;
 				$seoMeta = isset($this->currentCatArray['seoMeta']) ? $this->currentCatArray['seoMeta'] : $this->className ;
 				$seoDesc = isset($this->currentCatArray['categoryIntrduce']) ? strip_tags($this->currentCatArray['categoryIntrduce']) : $this->className ;
 			}else{
 				if($dataType==1){
	 				$seoTitle = $this->className.' '.\LangPack::$items['index_newArri'];
	 				$seoMeta = $this->className.' '.\LangPack::$items['index_newArri'];
	 				$seoDesc = $this->className.' '.\LangPack::$items['index_newArri'];
 				}elseif($dataType==2){
 					$seoTitle = $this->className.' '.\LangPack::$items['index_spotl'];
	 				$seoMeta = $this->className.' '.\LangPack::$items['index_spotl'];
	 				$seoDesc = $this->className.' '.\LangPack::$items['index_spotl'];
 				}
 			}
 			$tpl->assign('metaTitle',$seoTitle);
			$tpl->assign('metaKeywords',$seoMeta);
			$tpl->assign('metaDesc',$seoDesc);
 			//end
 			$tpl->assign ( 'ClassName', $this->className);
 			$tpl->assign('ClassIntr',isset($this->currentCatArray['categoryIntrduce']) ? strip_tags($this->currentCatArray['categoryIntrduce']) : '' );
 			$tpl->assign('Nowaction',$Nowaction);
 			$tpl->assign('showType',$type);
 			$tpl->assign('classId',$this->defaultCid);
 			$tpl->assign('category',$carResult['productCategory']);
 			if($this->ShowType==1){
 				$tpl->display('newhot.htm');
 			}elseif($this->ShowType==0){ 				
 				//liebiao
 				//分页
				$pages = \Helper\Page::getpage ( $totalCount, $pageSize, $page, $url,$this->className);
				$tpl->assign ( 'newurl', $url);
				$tpl->assign ( 'newurlForm', $urlForm);
				$tpl->assign ( 'pages', $pages);
 				$tpl->assign ( 'current_page', $page);
 				$tpl->assign('viewtype',$viewType);
 				$tpl->assign('PageSize',$pageSize);
 				$tpl->assign('totalCount',$totalCount);
 				$tpl->display('newhotlist.htm');
 			}
 		}else{
 			exit('no type');
 		}
 	}
 	
 	/**
 	 * 
 	 * 处理商品类目如果存在$cid,则会在对应ID数组位置加上标记：current，以标识是当前选中数组 
 	 * cid为空时默认设定第一个类目ID为当前值
 	 * @param unknown_type $categoryResult
 	 * @param unknown_type $cid
 	 */
 	public function categoryProcess($catArray,$cid=''){
 		if(!empty($catArray)){
 			if(!$cid){
	 		//首页显示为所有1级目录的集合商品
	 				$this->className = '';
	 				$this->defaultCid = '';
 					$this->ShowType = 1;
	 		}else{
		 		foreach($catArray as $key=>$val){
		 			if(!empty($cid)){
		 				if($val['categoryId'] == $cid){
		 					$catArray[$key]['current'] = 1;
		 					$this->className = $val['categoryName'];
		 					$this->ShowType = $val['showType'];
		 					if(!empty($val['childCateIds'])){
		 						$this->childCateIds = $val['childCateIds'];
		 					}
		 					$this->currentCatArray = $val;
		 					break;
		 				}elseif(!empty($val['childrenList'])){
		 					$catArray[$key]['childrenList'] = $this->categoryProcess($val['childrenList'],$cid);
		 					foreach($catArray[$key]['childrenList'] as $k=>$v){
		 						if(isset($v['current']) && $v['current']==1){
		 							$catArray[$key]['current'] = 1;
		 							break;
		 						}
		 					}
		 				}
		 			}		
		 		}
	 		}
	 		return $catArray;
 		}
 	}
 }