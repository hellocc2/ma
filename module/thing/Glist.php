<?php
namespace Module\Thing;
use Model\Navigator;

use Model\KeyWords;

use Helper\RequestUtil as RequestUtil;
use Helper\String as HString;
/**
 * 商品列表，搜索处理
 * @author ChengJun<cgjp123@163.com>
 * 
 */
class Glist extends \Lib\common\Application {
	public function __construct() {
		$tpl = \Lib\common\Template::getSmarty ();
		$property = '';
		$propertyArray = array ();
		$Product_List_Search_Criteria_1 = array ();
		$baseColorArray = array ();
		$sphinx_flag = '';
		$sortby = 0;
		$sort = '';
		$searchType = '';
		$is_wedding = 0;//判断是否婚纱目录
		$searchAll = 0;
		$maxShowPropertyNum = \config\Ued::MAX_PROPERTIES_SHOWN_ON_LEFT;
		$maxShowChildPropertyNum = 9;
		$maxshowReviewWords = 300;
		$tpl->assign('maxShowPropertyNum',$maxShowPropertyNum);
		$tpl->assign('maxShowChildPropertyNum',$maxShowChildPropertyNum);
		$tpl->assign('maxshowReviewWords',$maxshowReviewWords);
		
		$action = RequestUtil::getParams ( 'action' );
		$ClassId = RequestUtil::getParams ( 'class' );
		$searchClassId = RequestUtil::getParams ( 'ClassId' );
		$PageSize = RequestUtil::getParams ( 's' );
		$viewtype = RequestUtil::getParams ( 'v' );
		$page = RequestUtil::getParams ( 'page' );
		$sortby = RequestUtil::getParams ( 'sortby' );
		$sort = RequestUtil::getParams ( 'sort' );
		$priceRange = RequestUtil::getParams ( 'priceRange' );
		$param = RequestUtil::getParams ( 'aparams' );
		$keywords = RequestUtil::getParams ( 'keyword' );
		$searchPrice = RequestUtil::getParams('searchPrice');//价格搜索标记
		if(!empty($keywords)){
			$tpl->assign('url_keywords',$keywords);
		}
		$searchType = RequestUtil::getParams ( 'type' );
		//producttag 搜索
		$tag = RequestUtil::getParams ( 'sortlist' );
		$sType =  RequestUtil::getParams ( 't' );
		
		if ($tag) {
			$tpl->assign('producttags',1);//sp代码需要参数
			$keywords = RequestUtil::getParams ( 'textname' );
			if(strpos($keywords,'david') !== false) {
				header ('HTTP/1.1 404 Not found');
           		//require ROOT_PATH.'errors/404.php';
				exit();
			}
			$tag = $tag;
			$searchClassId = 0;
		} else {
			//TAG页标记
			$tag = 0;
		}
	
		//PPC成人内容检查
		if($ClassId){
			if(file_exists(ROOT_PATH.'data/PPC_filter_setting_'.SELLER_LANG.'.php')){
				include_once ROOT_PATH.'data/PPC_filter_setting_'.SELLER_LANG.'.php';
				if(!empty($PPC_setting)){
					if($PPC_setting['status']){
						if(!empty($PPC_setting['categories'])){
							$PPC_category = explode(',',$PPC_setting['categories']);
							if(in_array($ClassId,$PPC_category)){
								$adultCheckM = new \Helper\AdultCheck($ClassId,$PPC_setting['filterRules']);
								if($adultCheckM->getResult){
									//验证成功,显示提示页
									$redirectUrl = $adultCheckM->creatUrl();
									$tpl->assign('redirectUrl',$redirectUrl);
									$tpl->assign('classId',$ClassId);  
									
									$tpl->display('adultNotice_JP.htm');
									return;
								}
							}
						}
					}
				}
			}
		}
		//end
		
		//获取搜索根分类名字
		if (isset ( $searchClassId ) && $searchClassId) {
			$searchClassName = $this->getClassName ( $searchClassId );
			$searchClassName = $this->dostrip($searchClassName);
			if($searchClassId && $keywords==''){
				//按分类空搜索
				$jumpUrl = \Helper\ResponseUtil::rewrite(array('url'=>'?module=thing&action=glist&class='.$searchClassId,'seo'=>$searchClassName,'isxs' => 'no'));
				header('HTTP/1.1 301 Moved Permanently');//发出302头部
				header('Location:'.$jumpUrl);
				exit();
			}
		} else {
			$searchClassName = '';
		}
		//初始化URL
		$newurl = '?module=thing&action=glist&class=' . $ClassId;

		if (! $viewtype) {
			if (isset ( $_COOKIE ['viewtype'] ) && $_COOKIE ['viewtype'] != '') {
				$viewtype = $_COOKIE ['viewtype'];
				if($viewtype!=='list' && $viewtype!=='text' && $viewtype!=='grid' &&  $viewtype!=='stream'){
					$viewtype = 'text';
				}
			} else {
				$viewtype = 'text';
			}
		} else {
			if($viewtype!=='list' && $viewtype!=='text' && $viewtype!=='grid' &&  $viewtype!=='stream'){
				$viewtype = 'text';
			}
			setcookie('viewtype',$viewtype,0,'/');
		}
		if (! $PageSize) {
			if (isset ( $_COOKIE ['pagesize'] ) && $_COOKIE ['pagesize'] != '') {
				$PageSize = $_COOKIE ['pagesize'];
				if($PageSize!='24' && $PageSize!='36' && $PageSize!='48'){
					$PageSize = 36;
				}
			} else {
				$PageSize = 36;
			}
		} else {
			if($PageSize!='24' && $PageSize!='36' && $PageSize!='48'){
				$PageSize = 36;
			}
			setcookie('pagesize',$PageSize,0,'/');
		}
		//if (! $page) $page = 1; else $newurl .= '&page='.$page;	
		
		//商品列表页标记
		$tpl->assign ( 'thing_type', 'product_list' );
		$tpl->assign ( 'is_wedding', $is_wedding );
		//end
		
		if ($searchClassId == '' && $ClassId != '' && $keywords == '') { //商品分类页
			//评论
			if(!isset($page)){
				$reviewSearchParam = array();
				$reviewSearchParam['languageCode'] = SELLER_LANG;
				$reviewSearchParam['categoryId'] = $ClassId;
				$mReivew = new \Model\Reviews();
				$reviewResult = $mReivew->getMostHelpReview($reviewSearchParam);
				if(!empty($reviewResult) && $reviewResult['code']==0){
					foreach($reviewResult['listResults'] as $key=>$val){
						if(!empty($val['comment'])){
							$val['commentSorce'] = round($val['commentSorce']);
							$val['commentSorce'] = $val['commentSorce']>5 ? 5 : $val['commentSorce'];
							$val['commentSorce'] = $val['commentSorce']<0 ? 0 : $val['commentSorce'];
							$reviewResult['listResults'][$key]['comment']['commentSorce'] = $val['commentSorce'];
							$reviewAddTime = strtotime($val['comment']['commentTime']);
							$reviewResult['listResults'][$key]['comment']['commentTime'] = date("M d, Y",$reviewAddTime);
						}
					}
					$tpl->assign('reviewResult',$reviewResult['listResults']);
				}
			}
			//end
			
			//DCP页面
			$mNav = new \Model\Product();
			$navResult = $mNav->getNavShowType($ClassId);
			$getFaild = false;
			if(!empty($navResult) && $navResult['code']==0 && $navResult['showType']=='DCP'){
				$mDcp = new \Helper\Dcp($ClassId); 
				if(!$mDcp->getWrong){
					$tpl->assign('dcpPageTag',1);
					$tpl->assign ( 'class', $ClassId );
					$tpl->assign ( 'ClassId', $ClassId );
					$search = 0;
					$tpl->assign ( 'search', $search );
					
					$categoryBreadcrumb = $mDcp->getCategoryBreadcrumb();//面包屑
					if(!empty($categoryBreadcrumb) && !empty($categoryBreadcrumb['categoryBreadcrumbNavigation']['categoryId'])){
						setcookie('milanoo_cc',$categoryBreadcrumb['categoryBreadcrumbNavigation']['categoryId'],time()+1200,'/');
						setcookie('milanoo_cn',stripcslashes($categoryBreadcrumb['categoryBreadcrumbNavigation']['categoryName']),time()+1200,'/');
					}
					$productCategory = $mDcp->getProductCategory();//下级类目
					if(!empty($productCategory)){
						$tpl->assign ( 'className', $mDcp->getCategoryName());
						$tpl->assign ( 'classDesc', $mDcp->getCategoryDesc());
						$tpl->assign('category',$productCategory);
						$tpl->assign('productCategory',$productCategory);
					}
					$tpl->assign('result_ga',array('categoryBreadcrumbNavigation'=>array('categoryId'=>$ClassId)));
					$featureCategory = $mDcp->getFeatureCategory();//热门主题
					$featureCategoryRules = $mDcp->getFeatureCategoryRules();//热门主题显示规则
					if(!empty($featureCategory)){
						$tpl->assign('featureCategory',$featureCategory);
						$tpl->assign('featureCategoryRules',$featureCategoryRules);
					}
					$topSelling = $mDcp->getTopSelling();//TOPSELLING
					if(!empty($topSelling)){
						$tpl->assign('topSelling',$topSelling);
					}
					$newArrival =  $mDcp->getNewArrival();//新到货
					if(!empty($newArrival)){
						$tpl->assign('newArrival',$newArrival);
					}
					$sale = $mDcp->getSale();//促销
					if(!empty($sale)){
						$tpl->assign('sale',$sale);
					}
					$tpl->assign('site_type','mainCategory');
					$tpl->assign('mainCid', urlencode($mDcp->getCategoryName()));
					
					
					$tpl->display('thing_dcp.htm');
					return;
				}else{
					$getFaild = true;
				}
			}else{
				$getFaild = true;
			}
			
			if($getFaild){
				$catShowSubCats = array(300,1025,934,391,535,1058,1281,1155,149,1252,1399,782,2186,2188,2412,634,564,1632);
				$tpl->assign('dcpPageTag',1);
				$tpl->assign ( 'class', $ClassId );
				$tpl->assign ( 'ClassId', $ClassId );
				$search = 0;
				$tpl->assign ( 'search', $search );

				if (in_array ( $ClassId, $catShowSubCats )) {
					//获取随机关键词start
					/*
					if($keywords==''){
						if($searchClassId==0){
							$cid = 0;
						}else{
							$cid = $ClassId;
						}
						$Product_Search_NULL_keyword = array('categoryId'=>$cid,'languageCode'=>SELLER_LANG);
						$mProductKeywordListSearch = new \Model\Search ();
						$productRemKeywords = $mProductKeywordListSearch->getKeywordList ( $Product_Search_NULL_keyword );
						if($productRemKeywords['code']==0 && !empty($productRemKeywords['recommendKeyword'])){
							$max = count($productRemKeywords['recommendKeyword']) - 1;
							$randIndex = rand(0,$max);
							if(isset($productRemKeywords['recommendKeyword'][$randIndex])){
								$keywords = $productRemKeywords['recommendKeyword'][$randIndex];
							}
							$tpl->assign('textname',$this->dostrip($keywords));
						}
					}
					*/
					//获取随机关键词end
					 
					$mProductList = new \Model\Product ();
					$search_arr = array ('pcs.categoryId' => $ClassId ,'pcs.returnTopSellingNum' => 10);
					$result = $mProductList->getProductList ( $search_arr );
	
					if(isset($result['categoryBreadcrumbNavigation']['categoryId'])){
						setcookie('milanoo_cc',$result['categoryBreadcrumbNavigation']['categoryId'],time()+1200,'/');
						setcookie('milanoo_cn',stripcslashes($result['categoryBreadcrumbNavigation']['categoryName']),time()+1200,'/');
					}
					
					//获取当前目录ID和子ID
					if($ClassId && !empty($result)){
						$productlist = array();
						$productlist = $result;
						$idString = '';
						$idArray = array();
						if(!empty($productlist['productCategory'])){
							$idArray[] =$productlist['productCategory']['categoryId'];
							if(!empty($productlist['productCategory']['childrenList'])){
								foreach($productlist['productCategory']['childrenList'] as $kia=>$via){
									$idArray[] = $via['categoryId'];
								}
							}
						}
						 if(!empty($idArray)){
						 	$idArray = array_unique ( $idArray );
						 	foreach($idArray as $vidar){
						 		$idString .= $vidar.',';
						 	}
						 	$idString = substr($idString,-1)==',' ? substr($idString, 0,-1) : $idString;
						 }
						 $tpl->assign('idString',$idString);
					}
					
					$tpl->assign ( 'result', $result );
					$tpl->assign ( 'thing_type', 'category_list' );
					
					$tpl->assign('pagetype','categorylist');
					$tpl->assign('site_type','mainCategory');
					if(!empty($result['categoryBreadcrumbNavigation']['categoryName'])){
						$tpl->assign('mainCid', urlencode($result['categoryBreadcrumbNavigation']['categoryName']));
					}
					
					$tpl->display ( 'A-directory.html' );
					return;
				}
			}
			$Product_List_Search_Criteria_1 ['pcs.categoryId'] = $ClassId;
		} else {
			if ($searchClassId == ''){
				$searchClassId = 0;
			}
			if ($ClassId) {
				$Product_List_Search_Criteria_1 ['pcs.categoryId'] = $ClassId;
			}					
			if ($searchClassId != 0 && ! $ClassId) {
				$Product_List_Search_Criteria_1 ['pcs.categoryId'] = $searchClassId;
				$ClassId = $searchClassId;
			}
			$tpl->assign ( 'class', $searchClassId );
			$tpl->assign ( 'selfclass', $ClassId );
			$tpl->assign ( 'searchCategoryId', $searchClassId );
			if ($keywords) {
				$keywords = urldecode($keywords);
				if(SELLER_LANG=='ja-jp' && $tag!==0){
					$keywordsArray = array();
					//日文tag搜索传入ID值，根据ID查询关键词，再来根据关键词查询产品
					$mJpKeyword = new \Model\Product ();
					$Search_jp_keywords= array('id'=>intval($keywords));
					$keywordsArray = $mJpKeyword->getJpKeyword($Search_jp_keywords);
					if(!empty($keywordsArray) && $keywordsArray['code']==0){
						$keywords = $keywordsArray['name'];
					}
				}
				$keywords = addslashes ( str_replace ( array ("-", "\/", "\\" ), array (" ", "", "" ), htmlspecialchars_decode ( trim ( $keywords ) ) ) );
				
				$sphinx_flag = 1;
				$keywords = $this->dostrip($keywords);
				
				$Product_List_Search_Criteria_1 ['pcs.searchContent'] = $keywords;
				$newurl = '?module=thing&action=glist&keyword=' . urlencode($keywords) . '&ClassId=' . $searchClassId;
				if ($searchClassId != 0 && !$ClassId) {
					$newurl .= '&class=' . $searchClassId;
				}elseif($ClassId){
					$newurl .= '&class=' . $ClassId;
				}
			}else{
				$newurl = '?module=thing&action=glist&ClassId=' . $searchClassId;
				if(!$ClassId && !$searchClassId && !$keywords){
					//全局搜索
					$searchAll = 1;
				}
				if ($ClassId) {
					$newurl .= '&class=' . $ClassId;
				}elseif($searchClassId){
					$newurl .= '&class=' . $searchClassId;
				}
			}
			//搜索标志
			$search = 1;
			
			//评论
			if(!isset($page)){
				$reviewSearchParam = array();
				$reviewSearchParam['languageCode'] = SELLER_LANG;
				if(!empty($ClassId)){
					$reviewSearchParam['categoryId'] = $ClassId;
				}
				if(!empty($keywords)){
					$reviewSearchParam['keyword'] = $keywords;
				}
				$mReivew = new \Model\Reviews();
				$reviewResult = $mReivew->getMostHelpReview($reviewSearchParam);
				if(!empty($reviewResult) && $reviewResult['code']==0){
					foreach($reviewResult['listResults'] as $key=>$val){
						if(!empty($val['comment'])){
							$val['commentSorce'] = round($val['commentSorce']);
							$val['commentSorce'] = $val['commentSorce']>5 ? 5 : $val['commentSorce'];
							$val['commentSorce'] = $val['commentSorce']<0 ? 0 : $val['commentSorce'];
							$reviewResult['listResults'][$key]['comment']['commentSorce'] = $val['commentSorce'];
							$reviewAddTime = strtotime($val['comment']['commentTime']);
							$reviewResult['listResults'][$key]['comment']['commentTime'] = date("M d, Y",$reviewAddTime);
						}
					}
					$tpl->assign('reviewResult',$reviewResult['listResults']);
				}
			}
			//end
		}
		
		if(!$keywords && $search && $ClassId){
			//特殊搜索处理，如果没有关键词，走正常类目处理
			$newurl = '?module=thing&action=glist&class=' . $ClassId;
			$search = 0;
		}
		if (! $page) $page = 1; elseif($page!=1) {$page = !intval($page) ? 1 : $page ; $newurl .= '&page='.$page;}	
		$tpl->assign ( 'current_page', $page);
		$tpl->assign ( 'search', $search );

		$cosplayclass = 0;
		if ($ClassId) {
			$cosplayclass = RequestUtil::getParams ( 'cosplayclass' );
			$tpl->assign ( 'cosplayclass', $cosplayclass ? $cosplayclass : 0 );
			if (isset ( $cosplayclass )) {
				switch ($cosplayclass) {
					case 1 :
						//Cosplay Costumes 
						$Product_List_Search_Criteria_1 ['pcs.categoryId'] = $ClassId;
						$Product_List_Search_Criteria_1 ['pcs.mainCategoryId'] = $ClassId;
						break;
					case 2 :
						//Cosplay Accessories
						$Product_List_Search_Criteria_1 ['pcs.categoryId'] = $ClassId;
						$Product_List_Search_Criteria_1 ['pcs.mainCategoryId'] = '301,428,1028,650';
						$Product_List_Search_Criteria_1 ['pcs.addCategoryId'] = $ClassId;
						break;
					case 3 :
						//Miscellaneous
						$Product_List_Search_Criteria_1 ['pcs.categoryId'] = $ClassId;
						$Product_List_Search_Criteria_1 ['pcs.mainCategoryId'] = '1543';
						$Product_List_Search_Criteria_1 ['pcs.addCategoryId'] = $ClassId;
						break;
				}
			}
			$cosplayArray = array (2186, 2188 );
			$mCosplay = new \model\Navigator ();
			$cosplayChildArray = array ();
			$cosplayChildArray = $mCosplay->getPid ( $ClassId);
			if (! empty ( $cosplayChildArray['parentCategoryId'] )) {
				if(in_array ( $cosplayChildArray ['parentCategoryId'], $cosplayArray )){
					$tpl->assign ( 'cosplayTag', 1 );
				}
				$parentClassId = $cosplayChildArray ['parentCategoryId'];
			}
		}
		//组装属性
		if (! empty ( $param )) {
			/*
			 preg_match_all('#att_\d+\-\d+#', $param,$matattri);
			$matattriArray = array();
			if(!empty($matattri)){
				foreach ($matattri[0] as $k=>$v){
					$matattriArray[] = explode ( '-', $v );
				}
			}
			$param = preg_replace('#att_\d+\-\d+#', '', $param);
			*/
			$extPropertyArray = array();//存放以选择的所有属性
			$lastProperyId = -1;
			$param_array = explode ( '-', $param );
			$param_array = array_chunk ( $param_array, 2 );
			//$param_array = array_merge($param_array,$matattriArray);
			$outArray = array ('cosplayclass', 's', 'v', 'sortby', 'sort', 'priceRange', 'keyword', 'ClassId', 'searchclass' );
			foreach ( $param_array as $val ) {
				if (count ( $val ) < 2)
					continue;
				if (! in_array ( $val [0], $outArray )) {
					
					if(strpos($val [0], 'a_')!==false){
						if($val[0]=='a_priceRange' && $searchPrice==1){
							//当已存在属性价格筛选，这时用户采用输入价格区间，则去掉属性价格筛选的 已勾选值
							continue;
						}
						$val [0] = substr($val [0],2);
						$propertyArray [$val [0]] [] = $val [1];
						$newurl .= '&a_' . $val [0] . '[]=' . $val [1];
						//$newurl .= '&page=1';
						//$lastProperyId = $val [0];
						$extPropertyArray[] = $val [0] . '-' . $val [1];
					}
				} else {
					if ($val [0] != 'ClassId' && $val [0]!='') {
						if($val[0]!='s' && $val[0]!='v'){
							$newurl .= '&' . $val [0] . '=' . $val [1];
						}
					}
				}
			}
			if (! empty ( $propertyArray )) {
				$propertyString = '';
				$propertyChild = array();
				foreach($propertyArray as $prok=>$prov){
					if(!empty($prov)){
						foreach($prov as $prok2=>$prov2){
							$propertyString .= $prok;
							$propertyChild = explode('_',$prov2);
							krsort($propertyChild);
							$propertyString .= ','.implode(',', $propertyChild);
							$propertyString .= '@';
						}
					}
				}
				$propertyString = substr($propertyString,0,-1);
				//session存储已点击的ID
				//unset($_SESSION['propertyIdsHistory']);
				if(empty($_SESSION['propertyIdsHistory'])){
					$_SESSION['propertyIdsHistory'] = $propertyString;
					$lastProperyIdsArray = explode('@',$propertyString);
					if(!empty($lastProperyIdsArray)){
						$lastProperyIdArray = explode(',',$lastProperyIdsArray[0]);
						if(!empty($lastProperyIdArray)){
							$lastProperyId = $lastProperyIdArray[0];
							$_SESSION['propertyLastId'] = $lastProperyId;
						}
					}
				}else{
					$temp = $_SESSION['propertyIdsHistory'];
					if(!empty($temp)){
						$propertyArrayTemp = explode('@',$temp);
						$propertyArrayNow = explode('@',$propertyString);
						if(count($propertyArrayNow) > count($propertyArrayTemp)){//增加属性
							$propertyDiff = array_diff($propertyArrayNow,$propertyArrayTemp);
						}elseif(count($propertyArrayNow) < count($propertyArrayTemp)){//减少属性
							$propertyDiff = array_diff($propertyArrayTemp,$propertyArrayNow);
						}else{//相同或者个数不变只是属性ID改变
							$propertyDiff = array_diff($propertyArrayNow,$propertyArrayTemp);
						}
						if(!empty($propertyDiff)){
							foreach($propertyDiff as $k=>$v){
								if(!empty($v)){
									$lastProperyIdArray = explode(',',$v);
									if(!empty($lastProperyIdArray)){
										$lastProperyId = $lastProperyIdArray[0];
										$_SESSION['propertyLastId'] = $lastProperyId;
										break;
									}
								}
							}
						}
						unset($_SESSION['propertyIdsHistory']);
						$_SESSION['propertyIdsHistory'] = $propertyString;
					}
				}
				//$Product_List_Search_Criteria_1 ['pcs.propertyArray'] = json_encode ( (object)$propertyArray ,JSON_HEX_APOS|JSON_HEX_QUOT);
				$Product_List_Search_Criteria_1 ['pcs.propertyArray'] = $propertyString;
				
				if($lastProperyId==-1 && !empty($_SESSION['propertyLastId'])){
					$lastProperyId = $_SESSION['propertyLastId'];
				}
				
				//传入当前属性ID
				if($lastProperyId!=-1){
					$Product_List_Search_Criteria_1 ['pcs.propeytyId'] = $lastProperyId;
					unset($_SESSION['propertyLastId']);
					$_SESSION['propertyLastId'] = $lastProperyId;
				}
			}else{
				if(!empty($_SESSION['propertyIdsHistory'])){
					//当前没有传入属性值，如果存在历史属性，则清除掉，避免对下次选择有影响
					unset($_SESSION['propertyIdsHistory']);
					unset($_SESSION['propertyLastId']);
				}
			}
		}else{
			if(!empty($_SESSION['propertyIdsHistory'])){
				//当前没有传入属性值，如果存在历史属性，则清除掉，避免对下次选择有影响
				unset($_SESSION['propertyIdsHistory']);
				unset($_SESSION['propertyLastId']);
			}
		}
		
		
		if (isset($searchPrice) && $searchPrice==1) {
			$priceRangeMin = RequestUtil::getParams('priceRange_min');
			$priceRangeMax = RequestUtil::getParams('priceRange_max');
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
					//$str = \Lib\common\Language::priceByCurrency ( $priceRangeMin, 'USD', CurrencyCode ) . ':' . \Lib\common\Language::priceByCurrency ( $priceRangeMax, 'USD', CurrencyCode );
					$str = $priceRangeMin .':'.$priceRangeMax;
				}elseif($priceRangeMax=='*'){
					$tpl->assign ( 'priceRange_min', $priceRangeMin );
					$tpl->assign ( 'priceRange_max', '');
					$str_url = $priceRangeMin . ':' . $priceRangeMax;
					//$str = \Lib\common\Language::priceByCurrency ($priceRangeMin, 'USD', CurrencyCode ) . ':' . $priceRangeMax;
					$str = $priceRangeMin .':'.$priceRangeMax;
				}elseif ($priceRangeMin < $priceRangeMax) {
					$tpl->assign ( 'priceRange_min', $priceRangeMin );
					$tpl->assign ( 'priceRange_max', $priceRangeMax );
					$str_url = $priceRangeMin . ':' . $priceRangeMax;
					//$str = \Lib\common\Language::priceByCurrency ( $priceRangeMin, 'USD', CurrencyCode ) . ':' . \Lib\common\Language::priceByCurrency ( $priceRangeMax, 'USD', CurrencyCode );
					$str = $priceRangeMin .':'.$priceRangeMax;
				} else {
					$tpl->assign ( 'priceRange_min', $priceRangeMax );
					$tpl->assign ( 'priceRange_max', $priceRangeMin );
					$str_url = $priceRangeMax . ':' . $priceRangeMin;
					//$str = \Lib\common\Language::priceByCurrency ( $priceRangeMax, 'USD', CurrencyCode ) . ':' . \Lib\common\Language::priceByCurrency ( $priceRangeMin, 'USD', CurrencyCode );
					$str = $priceRangeMax .':'.$priceRangeMin;
				}
				$newurl .= '&priceRange=' . $str_url;
				$Product_List_Search_Criteria_1 ['pcs.priceRange'] = $str;
			}
		} else {
			if ($priceRange != '') {
				$price = explode ( ':', $priceRange );
				$tpl->assign ( 'priceRange_min', $price [0] );
				if($price [1]=='*'){
					$tpl->assign ( 'priceRange_max', '' );
					$str = $price [0] . ':' . $price [1];
				}else{
					$tpl->assign ( 'priceRange_max', $price [1] );
					$str =  $price [0] . ':' . $price [1];
				}
				$str_url = $priceRange;
				$newurl .= '&priceRange=' . $priceRange;
				$Product_List_Search_Criteria_1 ['pcs.priceRange'] = $str;
			}
		}
		//分类里查询关键词
		if (isset ( $keywords ) && $keywords != '' && isset ( $ClassId ) && $ClassId != '') {
			$newurl .= '&keyword=' . urlencode($keywords);
			$Product_List_Search_Criteria_1 ['pcs.searchContent'] = $keywords;
		}
		//排序筛选
		if (!empty($sort)) {
			if($sort!=='recommend' && $sort!=='addedTime' && $sort!=='sortPrice'){
				$sort = 'recommend';
				$sortby = 0;
			}
			$sortjson = $sort . ':' . $sortby;
			$newurl .= '&sort=' .$sort.'&sortby='.$sortby;
			$Product_List_Search_Criteria_1 ['pcs.sortObject'] = $sortjson;
		}
		//设置商品查询需要的条件
		if($searchType=='search'){
			//点击search进入，分俩种情况，全局搜索和类目搜索
			$tpl->assign('be_search','1');
			$Product_List_Search_Criteria_1 ['pcs.requestSource'] = 'default';
		}elseif($tag!==0 && $searchClassId==0){
			//tag
			$Product_List_Search_Criteria_1 ['pcs.requestSource'] = 'productTags';
		}elseif($ClassId){
			//类目
			$Product_List_Search_Criteria_1 ['pcs.requestSource'] = 'category';
		}//elseif(!empty($sType)){
		//	$Product_List_Search_Criteria_1 ['pcs.requestSource'] = $sType;
		//}
		if($searchType == '' && !$tag){
			$Product_List_Search_Criteria_1 ['pcs.pageNo'] = $page;
		}
		
		//当该url绑定了规则，则将其绑定的分类id作为参数
		if(isset($newPropertyClassId)){
			$Product_List_Search_Criteria_1 ['pcs.categoryId'] = $newPropertyClassId;
		}
		
		if(isset($_SERVER['REQUEST_URI'])){
			$requestUri = explode('/',$_SERVER['REQUEST_URI']);
			if(!empty($requestUri) && strpos($requestUri[count($requestUri)-1],'.html')!=false){
				array_pop($requestUri);
			}
			if($requestUri[0]==''){
				array_shift($requestUri);
			}
			if($requestUri[0]=='narrow' || $requestUri[0]=='nf'){
				array_shift($requestUri);
			}
			$currentUrl = ROOT_URLD . implode('/',$requestUri);
			$Product_List_Search_Criteria_1['pcs.currentUrl'] = $currentUrl;
		}
		
		$Product_List_Search_Criteria = array ('pcs.languageCode' => SELLER_LANG, 'pcs.pageSize' => $PageSize, 'pcs.isFacet' => 1, 'pcs.returnTopSellingNum' => 10, 'pcs.priceUnit'=>CurrencyCode );
		$Product_List_Search_Criteria = array_merge ( $Product_List_Search_Criteria_1, $Product_List_Search_Criteria );
		//查询商品
		if($keywords!='' && $search==1){
			$mProductList = new \Model\Product ();
			$productlist = $mProductList->getProductList ( $Product_List_Search_Criteria );
			
			$tpl->assign('select',array('textfield'=>$keywords));
		}else{
			/*
			if($searchClassId==0){
				$cid = 0;
			}else{
				$cid = $ClassId;
			}
			$Product_Search_NULL_keyword = array('categoryId'=>$cid,'languageCode'=>SELLER_LANG);
			$mProductKeywordListSearch = new \Model\Search ();
			$productRemKeywords = $mProductKeywordListSearch->getKeywordList ( $Product_Search_NULL_keyword );
			if($productRemKeywords['code']==0 && !empty($productRemKeywords['recommendKeyword'])){
				$max = count($productRemKeywords['recommendKeyword']) - 1;
				$randIndex = rand(0,$max);
				if(isset($productRemKeywords['recommendKeyword'][$randIndex])){
					$keywords = $productRemKeywords['recommendKeyword'][$randIndex];
				}
			}
			*/
			$mProductList = new \Model\Product ();
			$productlist = $mProductList->getProductList ( $Product_List_Search_Criteria );
	
		}
		if(isset($productlist['categoryBreadcrumbNavigation']['categoryId'])){
			setcookie('milanoo_cc',$productlist['categoryBreadcrumbNavigation']['categoryId'],time()+1200,'/');
			setcookie('milanoo_cn',stripcslashes($productlist['categoryBreadcrumbNavigation']['categoryName']),time()+1200,'/');
		}
		
		if (isset ( $productlist ['code'] ) && $productlist ['code'] == 0) {
			if(!empty($productlist['url'])){
				header('HTTP/1.1 301 Moved Permanently');//发出301头部
				header('Location:'.$productlist['url']);
				exit();
			}
			
			if(!empty($productlist['cateAttr2url'])){
				header('HTTP/1.1 301 Moved Permanently');//发出301头部
				header('Location:'.$productlist['cateAttr2url']);
				exit();
			}
			
			//新的非销售属性展示分类ID
			$newPropertyShowCidTag = 0;
			$newBreadArray = array();
			//需要展示非销售属性的ID
			$newPropertyShowCidArray = array(391,564,300,934,634,535,1155,1399,2412,1058);
			//不需要展示非销售属性的ID
			$newPropertyNotNShowCidArray = array(766,1008);
			if(!empty($productlist['categoryBreadcrumbNavigation'])){
				$newBreadArray = $this->getNewBreadArray($productlist['categoryBreadcrumbNavigation']);
			}
			foreach($newPropertyShowCidArray as $nbscK=>$nbscV){
				if(in_array($nbscV, $newBreadArray)){
					$newPropertyShowCidTag = 1;
					break;
				}
			}
			foreach($newPropertyNotNShowCidArray as $nbnscK=>$nbnscV){
				if(in_array($nbnscV, $newBreadArray)){
					$newPropertyShowCidTag = 0;
					break;
				}
			}
			//end
			
			//$newurl .= '&v=' . $viewtype . '&s=' . $PageSize . '&page=' . $page;
			//搜索时返回价格区间处理
			$priceRangeAuto = array();
			if($search==1 && !empty($productlist['listResults']['priceRange'])){
				$priceRangeAuto = explode(':', $productlist['listResults']['priceRange']);
				if(!empty($priceRangeAuto) && $priceRangeAuto[1]!='*'){
					$priceRangeAutoMin = \Lib\common\Language::priceByCurrency(floatval($priceRangeAuto[0]),CurrencyCode,'USD');
					$priceRangeAutoMax = \Lib\common\Language::priceByCurrency(floatval($priceRangeAuto[1]),CurrencyCode,'USD');
					$tpl->assign ( 'priceRange_min', $priceRangeAutoMin );
					$tpl->assign ( 'priceRange_max', $priceRangeAutoMax );
					$str_url = $priceRangeAutoMin.':'.$priceRangeAutoMax;
					$newurl .= '&priceRange=' . $str_url;
				}
			}
			
			//搜索时绑定属性处理
			$searchPropertyArray = array();
			if($search==1 && !empty($productlist['listResults']['propertyArray'])){
				$searchPropertyArray = json_decode($this->dostrip($productlist['listResults']['propertyArray']),true);
				if(!empty($searchPropertyArray)){
					foreach ($searchPropertyArray as $k=>$v){
						if(!empty($v)){
							foreach($v as $v2){
								$propertyArray[$k][] = $v2;
							}
							$propertyArray[$k] = @array_unique($propertyArray[$k]);
						}
					}
					//todo 返回的绑定属性可能有不存在 的属性，需要过滤掉
					if(! empty ( $productlist ['definedPropertyResults'] )){
						foreach($productlist ['definedPropertyResults'] as $kd=>$vd){
							$kd = $vd['propertyId'];
							if(!empty($vd)){
								foreach($vd['list'] as $kd2=>$vd2){
									if(!empty($propertyArray[$kd])){
										foreach($propertyArray[$kd] as $kd3=>$vd3){
											if($vd3==$vd2['valueId']){
												$newurl .= '&a_' . $kd . '[]=' . $vd3;
											}
										}
									}
								}
							}
						}
					}
					//todo 返回的绑定颜色属性可能有不存在 的属性，需要过滤掉
					if(! empty ( $productlist ['baseColorResults'] )){
						foreach($productlist ['baseColorResults'] as $kd=>$vd){
							$kd = $vd['propertyId'];
							if(!empty($vd)){
								foreach($vd['list'] as $kd2=>$vd2){
									if(!empty($propertyArray[$kd])){
										foreach($propertyArray[$kd] as $kd3=>$vd3){
											if($vd3==$vd2['valueId']){
												$newurl .= '&a_' . $kd . '[]=' . $vd3;
											}
										}
									}
								}
							}
						}
					}
				}
			}
			
			if(isset($productlist['listResults']['requestSource'])){
				//$newurl .= '&t='.$productlist['listResults']['requestSource'];
			}
			$ClassIntr = '';
			$ClassName = '';
			if (!empty($productlist ['productCategory']) && !empty($productlist ['productCategory']['childrenList'])){
				$ClassName = $this->dostrip($productlist ['productCategory']['categoryName']);
				if(!empty($productlist ['productCategory']['categoryIntroduce'])){
					$ClassIntr = $this->dostrip($productlist ['productCategory']['categoryIntroduce']);
				}
			}
			if (empty($ClassName) && ! empty ( $productlist ['categoryBreadcrumbNavigation'] )) {
				$ClassName = $this->dostrip($this->getCurrentCategories($productlist ['categoryBreadcrumbNavigation'], $ClassId));
			}

			if ($ClassName == '' && $cosplayclass == 1) {
				$ClassName = $this->getClassName ( $ClassId );
			}
			
			if ($ClassName != '') {
				$tpl->assign ( 'ClassName', $ClassName );
				//$tpl->assign ( 'ClassIntr', $ClassIntr );
				
				if ($keywords) {
					$keywords = $this->dostrip ( $keywords );
					$tpl->assign ( 'textname', htmlspecialchars($keywords,ENT_QUOTES ));
				}
			} else {
				if($searchAll) $keywords = '';
				if ($keywords) {
					$keywords = $this->dostrip ( $keywords );
					$tpl->assign ( 'ClassName', '' );
					$tpl->assign ( 'textname', htmlspecialchars($keywords,ENT_QUOTES ));
					
				} else {
					$tpl->assign ( 'textname', '');
				}
			}
			
			/**
			 * ======================================数据处理开始=====================================
			 */
			//print_r($productlist);exit();
			if(!empty($productlist ['listResults'] ['results'])){
				foreach ( $productlist ['listResults'] ['results'] as $key => $val ) {
					$productlist ['listResults'] ['results'] [$key] ['marketPrice'] = \Lib\common\Language::priceByCurrency ( $val ['marketPrice'] );
					$productlist ['listResults'] ['results'] [$key] ['productPrice'] = \Lib\common\Language::priceByCurrency ( $val ['productPrice'] );
					if (isset ( $productlist ['listResults'] ['results'] [$key] ['promotionsPrice'] )) {
						$productlist ['listResults'] ['results'] [$key] ['promotionsPrice'] = \Lib\common\Language::priceByCurrency ( $val ['promotionsPrice'] );
						
					}
					if (isset ( $productlist ['listResults'] ['results'] [$key] ['promotionMidPic'] )) {
						$productlist ['listResults'] ['results'] [$key] ['promotionMidPic'] = substr ( $val ['promotionMidPic'], 1 );
					}
					//if (isset ( $val ['vote1'] ) && isset ( $val ['vote2'] ) && isset ( $val ['vote3'] ) && isset ( $val ['vote4'] ) && isset ( $val ['vote5'] ) && isset ( $val ['review'] ) && $val ['review'] > 0) {
					//	$productlist ['listResults'] ['results'] [$key] ['stars'] = round ( ($val ['vote1'] * 1 + $val ['vote2'] * 2 + $val ['vote3'] * 3 + $val ['vote4'] * 4 + $val ['vote5'] * 5) / $val ['review'], 1 ) * 20;
					if(!empty($val['stars'])) {
						$productlist ['listResults'] ['results'] [$key] ['stars'] = $val['stars']*20;
					}else{
						$productlist ['listResults'] ['results'] [$key] ['stars'] = 0;
					}
					//新增加非促销加标功能
					//测试地址http://www.milanoo.com/Floor-length-Flower-Girl-Dresses-c562/a_321-695-3.html
					//测试商品14658
					if(!empty($val['tagIcon'])){
						
						/*
						if(!empty($val['tagIcon']['iconBeginTime']) && !empty($val['tagIcon']['iconEndTime'])){
							//限制结束时间
							if(strtotime($val['tagIcon']['iconBeginTime'])<=time() && strtotime($val['tagIcon']['iconEndTime'])>time()){
								$productlist ['listResults'] ['results'][$key]['tagIconlist'] = $val['tagIcon']['listIcon'];
							}
						}elseif(!empty($val['tagIcon']['iconBeginTime']) && empty($val['tagIcon']['iconEndTime'])){
							//不限制结束时间
							if(strtotime($val['tagIcon']['iconBeginTime'])<=time()){
								$productlist ['listResults'] ['results'][$key]['tagIconlist'] = $val['tagIcon']['listIcon'];
							}
						}
						*/
					}				
					//上架事件15天内的产品增加new标记
					if(!empty($val['addedTime'])){
						$addTime = strtotime($val['addedTime']);
						if((time()-$addTime)<=15*24*3600){
							$productlist ['listResults'] ['results'] [$key]['newProductTag'] = 1;
						}else{
							$productlist ['listResults'] ['results'] [$key]['newProductTag'] = 0;
						}
					}
					
					//多图处理
					if(!empty($val['imgs'])){
						//去掉重复的图片
						$val['imgs'] = array_unique($val['imgs']);
						$upenL = array();
						$upenM = array();
						foreach($val['imgs'] as $kimg=> $vimg){
							$upenL[] = CDN_UPLAN_URL.'upen/l/'.$vimg;
							$upenM[] = CDN_UPLAN_URL.'upen/m/'.$vimg;
						}
						$productlist ['listResults'] ['results'] [$key]['imgstringLsize'] = implode(',',$upenL);
						$productlist ['listResults'] ['results'] [$key]['imgstringMsize'] = implode(',',$upenM);
					}
					
					//评论处理
					if(!empty($val['reviewList'])){
						$productlist ['listResults'] ['results'][$key]['reviewList'] = $this->dostrip($val['reviewList']);
					}
					
					//强制屏蔽高清图标记,需求10970
					if(!empty($val['productPictureType'])){
						$productlist ['listResults'] ['results'][$key]['productPictureType'] = '';
					}
					
					/**
					 * 增加视频标记
					 */
					if(isset($val['haveVideo'])){
						//浏览器是IE10就不显示视频标记
						if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 10.0")){
							$productlist ['listResults'] ['results'][$key]['productVideoTag'] = 0;
						}else{
							$productlist ['listResults'] ['results'][$key]['productVideoTag'] = $val['haveVideo'];
						}
					}
				}
			}
			
			//
			if(!empty($productlist['currentUrlParse'])){//当url绑定了规则时才会存在
				$tpl->assign(PropertySelect,1);
				if(isset($productlist['currentUrlParse']['categoryId'])){
					$newPropertyClassId = $productlist['currentUrlParse']['categoryId'];
					$newPropertyUrl = $newurl;
					$newPropertyUrl = str_replace('class='.$ClassId,'class='.$newPropertyClassId,$newurl);
				}
				if(isset($productlist['currentUrlParse']['categoryName'])){
					$newPropertyUrlName = $productlist['currentUrlParse']['categoryName'];
				}
			}
			
			//属性处理,由于接口数据发生变动，新建数组重新以原来的格式组装数据
			$propertyUrlArray = array ();
			$definedPropertyResults = array();
			$baseColorResults = array();
			$propertyseo = array();
			$totalPropertyNum = 0;
			$seo_attrs=$seo_color_attrs=array();
			$attr_num_select = 0;//统计已选属性个数
			if (! empty ( $productlist ['definedPropertyResults'] ) && is_array ( $productlist ['definedPropertyResults'] )) {
				foreach ( $productlist ['definedPropertyResults'] as $kr => $vr ) {
					if(!empty($vr) && is_array($vr)){
						if($newPropertyShowCidTag && $vr['propertyId']>='100000'){//属性ID大于十万才显示
							$kr = $vr['propertyName'];
							$krid = $vr['propertyId'];
							$definedPropertyResults [$krid] ['self'] ['propertyName'] =  $this->dostrip($kr);
							$definedPropertyResults [$krid] ['self'] ['propertyId'] =  $this->dostrip($krid);
							$definedPropertyResults [$krid] ['self'] ['haveselect'] = 0;
							$definedPropertyResults [$krid] ['self'] ['propertyurl'] = '';
							if(!empty($vr['list']) && is_array($vr['list'])){
								if(isset($newPropertyUrl)){
									$proTempUrl = $newPropertyUrl;
								}else{
									$proTempUrl = $newurl;
								}
								$arrayDelect = $proTempUrl;//用于组删除
								$temp = 0;//统计符合条件的子属性个数
								foreach ( $vr['list'] as $kr2 => $vr2 ) {
										if (! empty ( $vr ) && ! empty ( $propertyArray [$krid] )) {//给已经选择的属性添加一个标记，并写入一个新的url以供点击取消选择该属性
											if($krid!='priceRange'){
												$propertyseo[$krid]['self'] = $this->dostrip($kr);
											}
											foreach ( $propertyArray [$krid] as $keyproperty => $pr2 ) {
												$propertyurl = '';
												if(isset($vr2['parentId'])){
													$haveSelect = $vr2 ['valueId'].'_'.$vr2['parentId'];
												}else{
													$haveSelect = $vr2 ['valueId'];
												}
												if ($pr2 == $haveSelect) {								
													if (strpos ( $proTempUrl, '&a_' . $krid . '[]=' . $pr2 ) !== false) {
														$propertyurl = str_replace ( '&a_' . $krid . '[]=' . $pr2, '', $proTempUrl );
														$arrayDelect = str_replace ( '&a_' . $krid . '[]=' . $pr2, '', $arrayDelect );
														$definedPropertyResults [$krid] ['self'] ['haveselect'] = 1;
														if(!empty($extPropertyArray) && count($extPropertyArray)==2){//只有当当前选中俩个属性，且其中一个属性是带url规则的，则另一个属性的组装链接需修改为url规则
															$currentKey = array_search($krid.'-'.$pr2,$extPropertyArray);
															if($currentKey!==false){
																$otherKey = $currentKey==1 ? 0 : 1 ;
																$currentProperty = explode('-',$extPropertyArray[$otherKey]);
															}
															//当前已选属性只有俩个，且带url的属性包含其中
															foreach($productlist ['definedPropertyResults'] as $ktemp => $vtemp){
																if($vtemp['propertyId']==$currentProperty[0]){
																	foreach($vtemp['list'] as $k2temp=>$v2temp){
																		if($v2temp['valueId']==$currentProperty[1]){
																			if(isset($v2temp['url'])){
																				$propertyurl = $this->settrip($v2temp['url']);
																				if(count($propertyArray)>1){//当选中的两种属性属于同一属性分类，则不修改组删除url
																					$arrayDelect = $this->settrip($v2temp['url']);
																				}
																			}
																		}
																	}
																}
															}
														}
													}
													$seo_attrs[]=$this->dostrip($vr2['value']);
													if($krid!='priceRange'){
														$propertyseo[$krid][$haveSelect] = $this->dostrip($vr2['value']);
													}
													$propertyurl = $this->linetrip ( $propertyurl );
													if(substr($propertyurl,0,4)=='http') $propertyurl = $this->settrip($propertyurl);
													$definedPropertyResults [$krid] [$kr2] ['propertyurl'] = $propertyurl;
													$definedPropertyResults [$krid] [$kr2] ['select'] = 1;
													$propertyUrlArray['property'] [] = array ('name' => $this->dostrip($vr2 ['value']), 'url' => $propertyurl );
													$attr_num_select ++;
												}
											}
											$definedPropertyResults [$krid] ['self'] ['propertyurl'] = $arrayDelect;
											
										}else{										
											//没有任何属性选中时处理
											if(!empty($productlist['currentUrlParse'])){//当url绑定了规则时才会存在
												if($krid==$productlist['currentUrlParse']['propertyId'] && $vr2['valueId'] == $productlist['currentUrlParse']['valueId']){//当url绑定了规则，自动将对应属性勾选
														
													if(isset($vr2['parentId'])){
														$haveSelect = $vr2 ['valueId'].'_'.$vr2['parentId'];
													}else{
														$haveSelect = $vr2 ['valueId'];
													}
													if($krid!='priceRange'){
														$propertyseo[$krid][$haveSelect] = $this->dostrip($vr2['value']);
													}
													$definedPropertyResults [$krid] ['self'] ['haveselect'] = 1;													
													$definedPropertyResults [$krid] [$kr2] ['select'] = 1;
													$definedPropertyResults [$krid] [$kr2] ['propertyurl'] = $newPropertyUrl;
													$propertyUrlArray['property'] [] = array ('name' => $this->dostrip($vr2 ['value']), 'url' => $newPropertyUrl );
													$definedPropertyResults [$krid] ['self'] ['propertyurl'] =  $newPropertyUrl;
												}										
											}
										}//endif
										if(isset($vr2['url'])){
											$definedPropertyResults [$krid] [$kr2] ['url'] = $vr2['url'];
										}		
										$definedPropertyResults [$krid] [$kr2] ['value'] = $this->dostrip($vr2['value']);
										$definedPropertyResults [$krid] [$kr2] ['productNum'] = isset($vr2['productNum'])?$vr2['productNum']:0;
										//新建一个键专门存储用于URL的属性ID和属性值ID
										$definedPropertyResults [$krid] [$kr2] ['urlkey'] = 'a_'.$krid;
										if(!empty($vr2['parentId'])){
											$definedPropertyResults [$krid] [$kr2] ['urlvalue'] = $vr2['valueId'].'_'.$vr2['parentId'];
										}else{
											$definedPropertyResults [$krid] [$kr2] ['urlvalue'] = $vr2['valueId'];
										}
										if($vr2['follow_flag']){
											$definedPropertyResults [$krid] [$kr2] ['rel'] = $vr2['follow_flag'];
										}else{
											$definedPropertyResults [$krid] [$kr2] ['rel'] = 'nofollow';
										}
										$temp++;
								}
								$definedPropertyResults [$krid] ['self'] ['childrenNum'] = $temp;
								$totalPropertyNum += $temp;
							}
						}
					}
				}
			}
			//颜色属性处理
			if (! empty ( $productlist ['baseColorResults'] ) && is_array ( $productlist ['baseColorResults'] )) {
				foreach ( $productlist ['baseColorResults'] as $kr => $vr ) {
					if(!empty($vr) && is_array($vr)){
						if($newPropertyShowCidTag && $vr['propertyId']>='100000'){//属性ID大于十万才显示
							$kr = $vr['propertyName'];
							$krid = $vr['propertyId'];
							$baseColorResults [$krid] ['self'] ['propertyName'] =  $this->dostrip($kr);
							$baseColorResults [$krid] ['self'] ['propertyId'] =  $krid;
							$baseColorResults [$krid] ['self'] ['haveselect'] =  0;
							$baseColorResults [$krid] ['self'] ['propertyurl'] =  '';
							if(!empty($vr['list']) && is_array($vr['list'])){
								if(isset($newPropertyUrl)){
									$proTempUrl = $newPropertyUrl;
								}else{
									$proTempUrl = $newurl;
								}
								$arrayDelect = $proTempUrl;//用于组删除
								$temp = 0;
								foreach ( $vr['list'] as $kr2 => $vr2 ) {
									//给已经选择的属性添加一个标记，并写入一个新的url以供点击取消选择该属性
										if (! empty ( $vr ) && ! empty ( $propertyArray [$krid] )) {
											if($krid!='priceRange'){
												$propertyseo[$krid]['self'] = $this->dostrip($kr);
											}
											foreach ( $propertyArray [$krid] as $keyproperty => $pr2 ) {
												$propertyurl = '';
												if(isset($vr2['parentId'])){
													$haveSelect = $vr2 ['valueId'].'_'.$vr2['parentId'];
												}else{
													$haveSelect = $vr2 ['valueId'];
												}
												if ($pr2 == $haveSelect) {									
													if (strpos ( $proTempUrl, '&a_' . $krid . '[]=' . $pr2 ) !== false) {
														$propertyurl = str_replace ( '&a_' . $krid . '[]=' . $pr2, '', $proTempUrl );
														$arrayDelect = str_replace ( '&a_' . $krid . '[]=' . $pr2, '', $arrayDelect );
														$baseColorResults [$krid] ['self'] ['haveselect'] = 1;
														if(!empty($extPropertyArray) && count($extPropertyArray)==2){
															$currentKey = array_search($krid.'-'.$pr2,$extPropertyArray);
															if($currentKey!==false){
																$otherKey = $currentKey==1 ? 0 : 1 ;
																$currentProperty = explode('-',$extPropertyArray[$otherKey]);
															}
															//当前已选属性只有俩个，且带url的属性包含其中
															foreach($productlist ['baseColorResults'] as $ktemp => $vtemp){
																if($vtemp['propertyId']==$currentProperty[0]){
																	foreach($vtemp['list'] as $k2temp=>$v2temp){
																		if($v2temp['valueId']==$currentProperty[1]){
																			if(isset($v2temp['url'])){
																				$propertyurl = $this->settrip($v2temp['url']);
																				if(count($propertyArray)>1){//当选中的两种属性属于同一属性分类，则不修改组删除url
																					$arrayDelect = $this->settrip($v2temp['url']);
																				}
																			}
																		}
																	}
																}
															}
														}
													}
													$seo_color_attrs[]=$this->dostrip($vr2['value']);
													if($krid!='priceRange'){
														$propertyseo[$krid][$haveSelect] = $this->dostrip($vr2['value']);
													}
													$propertyurl = $this->linetrip ( $propertyurl );
													if(substr($propertyurl,0,4)=='http')  $propertyurl = $this->settrip($propertyurl);
													$baseColorResults [$krid] [$kr2] ['propertyurl'] = $propertyurl;
													$baseColorResults [$krid] [$kr2] ['select'] = 1;
													$propertyUrlArray['basecolor'] [] = array ('name' => $this->dostrip($vr2 ['value']), 'url' => $propertyurl );
													$attr_num_select ++;
												}
											}
											$baseColorResults [$krid] ['self'] ['propertyurl'] =  $arrayDelect;
										}else{
											//没有任何属性选中时处理
											if(!empty($productlist['currentUrlParse'])){//当url绑定了规则时才会存在
												if($krid==$productlist['currentUrlParse']['propertyId'] && $vr2['valueId']==$productlist['currentUrlParse']['valueId']){//自动i勾选属性
													if(isset($vr2['parentId'])){
														$haveSelect = $vr2 ['valueId'].'_'.$vr2['parentId'];
													}else{
														$haveSelect = $vr2 ['valueId'];
													}
													if($krid!='priceRange'){
														$propertyseo[$krid][$haveSelect] = $this->dostrip($vr2['value']);
													}
													$baseColorResults [$krid] ['self'] ['haveselect'] = 1;													
													$baseColorResults [$krid] [$kr2] ['select'] = 1;
													$baseColorResults [$krid] [$kr2] ['propertyurl'] = $newPropertyUrl;
													$propertyUrlArray['basecolor'] [] = array ('name' => $this->dostrip($vr2 ['value']), 'url' => $newPropertyUrl );
													$definedPropertyResults [$krid] ['self'] ['propertyurl'] =  $newPropertyUrl;
												}
											}
											
											
										}//endif
										$baseColorResults[$krid][$kr2]['value'] =  $this->dostrip($vr2['value']);
										$baseColorResults[$krid][$kr2]['baseColor'] =  $this->dostrip($vr2['baseColor']);
										$baseColorResults [$krid] [$kr2] ['productNum'] = isset($vr2['productNum'])?$vr2['productNum']:0;
										//新建一个键专门存储用于URL的属性ID和属性值ID
										$baseColorResults [$krid] [$kr2] ['urlkey'] = 'a_'.$krid;
										if(!empty($vr2['parentId'])){
											$baseColorResults [$krid] [$kr2] ['urlvalue'] = $vr2['valueId'].'_'.$vr2['parentId'];
										}else{
											$baseColorResults [$krid] [$kr2] ['urlvalue'] = $vr2['valueId'];
										}
										$baseColorResults [$krid] [$kr2] ['basecolorid'] = $vr2['baseColorId'];
										if($vr2['follow_flag']){
											$baseColorResults [$krid] [$kr2] ['rel'] = $vr2['follow_flag'];
										}else{
											$baseColorResults [$krid] [$kr2] ['rel'] = 'nofollow';
										}
										$temp++;
								}
								$totalPropertyNum += $temp;
								$baseColorResults [$krid] ['self'] ['childrenNum'] = $temp;
							}
						}
					}
				}
			}
			
			if(isset($newPropertyUrl)){
				$newPropertyUrl .= '&a_' . $productlist['currentUrlParse']['propertyId'] . '[]=' . $productlist['currentUrlParse']['valueId'];
				$_SESSION['propertyIdsHistory'] = $productlist['currentUrlParse']['propertyId'] . ',' . $productlist['currentUrlParse']['valueId'];
				$tpl->assign('newPropertyUrl',$newPropertyUrl);
				$tpl->assign('newPropertyClassId',$newPropertyClassId);
				$tpl->assign('newPropertyUrlName',$newPropertyUrlName);
			}
			
			//已选属性名字处理
			$propertyseoName = '';
			$proSeoName = '';
			if(!empty($propertyseo)){
				$propertyseoName = $this->creatPropertySeo($propertyseo);
				$tpl->assign('propertyseoName',$propertyseoName);
			}
			
			//已选属性组装字符串给SEO用
			if(!empty($propertyseoName)){
				$seo_attr = explode('-', $propertyseoName);
				$seo_attr_str=implode(' ',$seo_attr);
				$tpl->assign('seo_attrs',$seo_attr_str);
			}
			
			//已选属性单独处理SEO名字，以便于点击去掉该属性SEO名字	
			$seeAllPropertyNum = 0;//有属性值被选中的属性组数量
			if(!empty($definedPropertyResults)){
					foreach($definedPropertyResults as $kdpr=>$vdpr){
						if(!empty($vdpr) && is_array($vdpr)){
							if($vdpr['self']['haveselect']==1){
								$seeAllPropertyNum += 1;
							}
							$i=0;
							foreach($vdpr as $kdpr2=>$vdpr2){
								if(isset($vdpr2['select']) && $vdpr2['select']===1){
									$i++;
									$definedPropertyResults[$kdpr][$kdpr2]['seoName'] = $propertyseoName;
									$definedPropertyResults[$kdpr][$kdpr2]['rel'] = 'follow';//已选属性的rel属性强制成follow
									if($attr_num_select==3){
										$definedPropertyResults[$kdpr][$kdpr2]['rewriteName'] = 'narrow';//已选属性的url部分名
									}elseif($attr_num_select<3){
										$definedPropertyResults[$kdpr][$kdpr2]['rewriteName'] = 'narrow';//已选属性的url部分名
									}elseif($attr_num_select>3){
										$definedPropertyResults[$kdpr][$kdpr2]['rewriteName'] = 'nf';//已选属性的url部分名
									}
								}else{
									$temp = '';
									if(isset($vdpr2['value'])){
										$vdpr2['value'] = $this->dostrip($vdpr2['value']);
										//if(strpos($propertyseoName,'/'.$vdpr['self']['propertyName'])!==false){
										//	$definedPropertyResults[$kdpr][$kdpr2]['seoName'] = str_replace('/'.$vdpr['self']['propertyName'], '/'.$vdpr['self']['propertyName'].'-'.$vdpr2['value'], $propertyseoName);
										//}else{
										//	$definedPropertyResults[$kdpr][$kdpr2]['seoName'] = $propertyseoName.'/'.$vdpr['self']['propertyName'].'-'.$vdpr2['value'];
										//}
										$addproperty = array();
										$addproperty[$kdpr][$vdpr2['urlvalue']] = ucfirst($vdpr2['value']);
										$thisPropertyseoName = $this->creatPropertySeo($propertyseo,$addproperty);
										$definedPropertyResults[$kdpr][$kdpr2]['seoName'] = $thisPropertyseoName;
										if($attr_num_select>=2){
											$definedPropertyResults[$kdpr][$kdpr2]['rewriteName'] = 'nf';//未选属性的url部分名
											$definedPropertyResults[$kdpr][$kdpr2]['rel'] = 'nofollow';//已选属性大于等于2个时，其他未选属性的的rel属性都强制成nofollow
										}elseif($attr_num_select<2){
											$definedPropertyResults[$kdpr][$kdpr2]['rewriteName'] = 'narrow';//未选属性的url部分名
										}
									}
								}
							}
							if($i>0){
								$arrayDelectSeoName = $propertyseoName;
								foreach($vdpr as $kdpr3=>$vdpr3){
									//if($i>1){
										if(isset($vdpr3['select']) && $vdpr3['select']===1){
											$vdpr3['value'] = $this->dostrip($vdpr3['value']);
											$proSeoName = str_replace(ucfirst($vdpr3['value']).'-', '', $propertyseoName);
											$arrayDelectSeoName = str_replace(ucfirst($vdpr3['value']).'-', '', $arrayDelectSeoName);
											$definedPropertyResults[$kdpr][$kdpr3]['seoName'] = $proSeoName;
											if(!empty($propertyUrlArray['property']) && is_array($propertyUrlArray['property'])){
												foreach($propertyUrlArray['property'] as $k=>$v){
													if($v['name']==$vdpr3['value']){
														$propertyUrlArray['property'][$k]['seoName'] = $proSeoName;
														$propertyUrlArray['property'][$k]['rel'] = 'follow';//已选属性的rel属性强制成follow
														if($attr_num_select==3){
															$propertyUrlArray['property'][$k]['rewriteName'] = 'narrow';//已选属性的url部分名
														}elseif($attr_num_select<3){
															$propertyUrlArray['property'][$k]['rewriteName'] = 'narrow';//已选属性的url部分名
														}elseif($attr_num_select>3){
															$propertyUrlArray['property'][$k]['rewriteName'] = 'nf';//已选属性的url部分名
														}
													}
												}
											}
										}
									/*}elseif($i==1){
										if(isset($vdpr3['select']) && $vdpr3['select']===1){
											$vdpr3['value'] = $this->dostrip($vdpr3['value']);
											$proSeoName = '/'.$definedPropertyResults[$kdpr]['self']['propertyName'].'-'.$vdpr3['value'];
											$proSeoName = str_replace($proSeoName, '', $propertyseoName);
											$definedPropertyResults[$kdpr][$kdpr3]['seoName'] = $proSeoName;
											if(!empty($propertyUrlArray['property']) && is_array($propertyUrlArray['property'])){
													foreach($propertyUrlArray['property'] as $k=>$v){
														if($v['name']==$vdpr3['value']){
															$propertyUrlArray['property'][$k]['seoName'] = $proSeoName;
														}
													}
											}
										}
									}*/
								}
								
								$definedPropertyResults [$kdpr] ['self'] ['seoName'] = $arrayDelectSeoName;
							}
						}
					}
				}
				//已选颜色单独处理SEO名字，以便于点击去掉该颜色SEO名字	
				if(!empty($baseColorResults)){
					foreach($baseColorResults as $kdpr=>$vdpr){
						if(!empty($vdpr) && is_array($vdpr)){
							if($vdpr['self']['haveselect']==1){
								$seeAllPropertyNum += 1;
							}
							$i=0;
							foreach($vdpr as $kdpr2=>$vdpr2){
								if(isset($vdpr2['select']) && $vdpr2['select']===1){
									$i++;
									$baseColorResults[$kdpr][$kdpr2]['seoName'] = $propertyseoName;
									$baseColorResults[$kdpr][$kdpr2]['rel'] = 'follow';//已选属性的rel属性强制成follow
									if($attr_num_select==3){
										$baseColorResults[$kdpr][$kdpr2]['rewriteName'] = 'narrow';//已选属性的url部分名
									}elseif($attr_num_select<3){
										$baseColorResults[$kdpr][$kdpr2]['rewriteName'] = 'narrow';//已选属性的url部分名
									}elseif($attr_num_select>3){
										$baseColorResults[$kdpr][$kdpr2]['rewriteName'] = 'nf';//已选属性的url部分名
									}
								}else{
									$temp = '';
									if(isset($vdpr2['value'])){
										$vdpr2['value'] = $this->dostrip($vdpr2['value']);
										//if(strpos($propertyseoName,'/'.$vdpr['self']['propertyName'])!==false){
										//	$baseColorResults[$kdpr][$kdpr2]['seoName'] = str_replace('/'.$vdpr['self']['propertyName'], '/'.$vdpr['self']['propertyName'].'-'.$vdpr2['value'], $propertyseoName);
										//}else{
										//	$baseColorResults[$kdpr][$kdpr2]['seoName'] = $propertyseoName.'/'.$vdpr['self']['propertyName'].'-'.$vdpr2['value'];
										//}
										$addproperty = array();
										$addproperty[$kdpr][$vdpr2['urlvalue']] = ucfirst($vdpr2['value']);
										$thisPropertyseoName = $this->creatPropertySeo($propertyseo,$addproperty);
										$baseColorResults[$kdpr][$kdpr2]['seoName'] = $thisPropertyseoName;
										if($attr_num_select>=2){
											$baseColorResults[$kdpr][$kdpr2]['rewriteName'] = 'nf';//未选属性的url部分名
											$baseColorResults[$kdpr][$kdpr2]['rel'] = 'nofollow';////已选属性大于等于2个时，其他未选属性的的rel属性都强制成nofollow
										}elseif($attr_num_select<2){
											$baseColorResults[$kdpr][$kdpr2]['rewriteName'] = 'narrow';//未选属性的url部分名
										}
									}
								}
							}
							if($i>0){
								$arrayDelectSeoName = $propertyseoName;
								foreach($vdpr as $kdpr3=>$vdpr3){
									//if($i>1){
										if(isset($vdpr3['select']) && $vdpr3['select']===1){
											$vdpr3['value'] = $this->dostrip($vdpr3['value']);
											$proSeoName = str_replace(ucfirst($vdpr3['value']).'-', '', $propertyseoName);
											$arrayDelectSeoName = str_replace(ucfirst($vdpr3['value']).'-', '', $arrayDelectSeoName);
											$baseColorResults[$kdpr][$kdpr3]['seoName'] = $proSeoName;
											if(!empty($propertyUrlArray['basecolor']) && is_array($propertyUrlArray['basecolor'])){
												foreach($propertyUrlArray['basecolor'] as $k=>$v){
													if($v['name']==$vdpr3['value']){
														$propertyUrlArray['basecolor'][$k]['seoName'] = $proSeoName;
														$propertyUrlArray['basecolor'][$k]['rel'] = 'follow';//已选属性的rel属性强制成follow
														if($attr_num_select==3){
															$propertyUrlArray['basecolor'][$k]['rewriteName'] = 'narrow';//已选属性的url部分名
														}elseif($attr_num_select<3){
															$propertyUrlArray['basecolor'][$k]['rewriteName'] = 'narrow';//已选属性的url部分名
														}elseif($attr_num_select>3){
															$propertyUrlArray['basecolor'][$k]['rewriteName'] = 'nf';//已选属性的url部分名
														}
													}
												}
											}
										}
									/*}elseif($i==1){
										if(isset($vdpr3['select']) && $vdpr3['select']===1){
											$vdpr3['value'] = $this->dostrip($vdpr3['value']);
											$proSeoName = '/'.$baseColorResults[$kdpr]['self']['propertyName'].'-'.$vdpr3['value'];
											$proSeoName = str_replace($proSeoName, '', $propertyseoName);
											$baseColorResults[$kdpr][$kdpr3]['seoName'] = $proSeoName;
											if(!empty($propertyUrlArray['basecolor']) && is_array($propertyUrlArray['basecolor'])){
												foreach($propertyUrlArray['basecolor'] as $k=>$v){
													if($v['name']==$vdpr3['value']){
														$propertyUrlArray['basecolor'][$k]['seoName'] = $proSeoName;
													}
												}
											}
										}
									}*/
								}
								
								$baseColorResults [$kdpr] ['self'] ['seoName'] =  $arrayDelectSeoName;
							}
						}
					}
				}
			if (! empty ( $productlist ['topSellingResults'] ) && is_array ( $productlist ['topSellingResults'] )) {
				foreach ( $productlist ['topSellingResults'] as $tsk => $tsv ) {
					$productlist ['topSellingResults'] [$tsk] ['productsPrice'] = \Lib\common\Language::priceByCurrency ( $tsv ['productsPrice'] );
					if(isset($productlist ['topSellingResults'] [$tsk] ['promotionsPrice'])){
						$productlist ['topSellingResults'] [$tsk] ['promotionsPrice']=\Lib\common\Language::priceByCurrency ( $tsv ['promotionsPrice'] );
					}
				}
			}
	
			//获取当前目录ID和子ID
			if($ClassId){
				$idString = '';
				$idArray = array();
				if(!empty($productlist['productCategory'])){
					if(isset($productlist['productCategory']['categoryId'])){
						$idArray[] =$productlist['productCategory']['categoryId'];
					}
					if(!empty($productlist['productCategory']['childrenList'])){
						foreach($productlist['productCategory']['childrenList'] as $kia=>$via){
							if(isset($via['categoryId'])){
								$idArray[] = $via['categoryId'];
							}
						}
					}
					//解析当前分类CODE判断层级
					if(!empty($productlist['productCategory']['categoryCode'])){
						$categoryLevel = strlen($productlist['productCategory']['categoryCode'])/5;
						$tpl->assign('categoryLevel',$categoryLevel);
					}
				}
				 if(!empty($idArray)){
				 	$idArray = array_unique ( $idArray );
				 	foreach($idArray as $vidar){
				 		$idString .= $vidar.',';
				 	}
				 	$idString = substr($idString,-1)==',' ? substr($idString, 0,-1) : $idString;
				 }
				 $tpl->assign('idString',$idString);
			}
			//去掉反斜杠
			$productlist = $this->dostrip ( $productlist );
			//将URL中的横线替换成下划线
			$newurl =$this->linetrip ( $newurl );

			//价格区间搜索，302跳转
			if (isset($searchPrice) && $searchPrice==1){
				$priceJumpUrl = \Helper\ResponseUtil::rewrite(array('url'=>$newurl,'seo'=>$propertyseoName.$ClassName,'isxs' => 'no'));
				header('HTTP/1.1 302 Moved Permanently');//发出302头部
				header('Location:'.$priceJumpUrl);
				exit();
			}
			//价格区间form表单专用url
			if(!empty($str_url)){
				$newurlForm = str_replace('&priceRange='.$str_url, '', $newurl);
			}else{
				$newurlForm = $newurl;
			}
			//分页
			$reName = '';
			if(!empty($propertyseo)){//有属性选择
				if($attr_num_select<3){
					$reName = 'narrow';
				}elseif($attr_num_select>=3){
					$reName = 'nf';
				}
				$title = strtolower($seo_attr_str.' ');
				switch (SELLER_LANG){
					case 'en-uk':
						$ClassIntr = "Shop online for <h1>".$title.strtolower($ClassName)."</h1> on Milanoo.com. Find your favorite ".$title.strtolower($ClassName)." from the great selection of ".strtolower($ClassName)." in competitive price and quality.";
					break;
					case 'ja-jp':
						$ClassIntr = '<h1>'.$title.strtolower($ClassName)."</h1> でオンラインショップMilanoo.com, 納得のプライスとクオリティを誇る ".strtolower($ClassName)." の豊富なラインナップから ".$title.strtolower($ClassName)." のお気に入りをお探しください！";
					break;
					case 'fr-fr':
						$ClassIntr = "Découvrez <h1>".strtolower($ClassName)." ".$title."</h1> chez Milanoo, le grossiste de la Chine, Trouvez votre favori ".strtolower($ClassName)." ".$title." moins cher en ligne. La plus grande sélection absolue de ".strtolower($ClassName)." discount avec la garantie de qualité!";
					break;
					case 'ru-ru':
						$ClassIntr = "Шопинг <h1>".$title.strtolower($ClassName)."</h1> в Milanoo.com. Выбирай любимые товары ".$title.strtolower($ClassName)." в большом ассортименте ".strtolower($ClassName)." отличного качества и по лучшей цене.";
					break;
					case 'it-it':
						$ClassIntr = "Acquista online per  <h1>".$title.strtolower($ClassName)."</h1> on Milanoo.com. Trova il tuo preferito ".$title.strtolower($ClassName)." dal grande selezione di ".strtolower($ClassName)." nel prezzo competitivo e qualità.";
					break;
					case 'es-sp':
						$ClassIntr = "Comprar online para <h1>".$title.strtolower($ClassName)."</h1> en Milanoo.com. Encontrar el favorito tuyo de ".$title.strtolower($ClassName)." de la gran selección de ".strtolower($ClassName)." de alta calidad y al mejor precio.";
					break;
					case 'de-ge':
						$ClassIntr = "Kaufen Sie <h1>".strtolower($ClassName)." ( ".$title." )</h1> online auf Milanoo.com. Finden Sie ".strtolower($ClassName)." ( ".$title." ) mit großer Auswahl von ".strtolower($ClassName)." mit Top Qualität zu besten Preisen.";
					break;
					
				}
			}
			if ($ClassName != '') {
				$tpl->assign ( 'ClassIntr', $ClassIntr );
			}
			$tpl->assign('reName',$reName);
			$pages = '';
			if(isset($productlist ['listResults'] ['totalCount'])){
				$pages = \Helper\Page::getpage ( $productlist ['listResults'] ['totalCount'], $PageSize, $page, $newurl, $propertyseoName.$ClassName,'.html' ,0,$reName);
			}
			
			/**
			 * ======================================数据处理完毕=====================================
			 */
			$tpl->assign ( 'tag', $tag ); //TAG页标记
			$tpl->assign ( 'newurl', $newurl );
			$tpl->assign ( 'newurl', $newurl );
			$tpl->assign ( 'newurlForm', $newurlForm );
			$tpl->assign ( 'sort', $sort );
			$tpl->assign ( 'sortby', $sortby );
			$tpl->assign ( 'pages', $pages );
			$tpl->assign ( 'PageSize', $PageSize );
			$tpl->assign ( 'viewtype', $viewtype );
			$tpl->assign ( 'propertyArray', $propertyArray );
			$parentName = '';
			if (! empty ( $productlist ['categoryBreadcrumbNavigation'] )) {
				$tpl->assign ( 'categoryBreadcrumbNavigation', $productlist ['categoryBreadcrumbNavigation'] );
				//获取上级分类名
				if(!empty($productlist ['productCategory']) && isset($productlist ['productCategory']['isLast']) && $productlist ['productCategory']['isLast'] && isset($parentClassId)){
					$parentName = $this->getCurrentCategories($productlist ['categoryBreadcrumbNavigation'],$parentClassId);
					$tpl->assign('parentClassId',$parentClassId);
				}
				
			}
			$propertyUrlArrayNum = 0;
			if (! empty ( $propertyUrlArray )) {
				$tpl->assign ( 'propertyUrlArray', $propertyUrlArray );
				if(!empty($propertyUrlArray['property'])){
					$propertyUrlArrayNum += count($propertyUrlArray['property']);
				}
				if(!empty($propertyUrlArray['basecolor'])){
					$propertyUrlArrayNum += count($propertyUrlArray['basecolor']);
				}
				$tpl->assign('propertyUrlArrayNum',$propertyUrlArrayNum);
				$tpl->assign('seeAllPropertyNum',$seeAllPropertyNum);
			}
			//$tpl->assign('rewriteName',$rewriteName);
			
			if (! empty ( $productlist ['topSellingResults'] ) && is_array ( $productlist ['topSellingResults'] )) {
				//处理TOPSELLING
				$tpl->assign ( 'topSelling', $productlist ['topSellingResults'] );
			}
			$totalPropertyNumTemp = 0;
			if (! empty ( $productlist ['baseColorResults'] )) {
				$tpl->assign('baseColorResults',$baseColorResults);
				$totalPropertyNumTemp += count($baseColorResults);
				//$tpl->assign ( 'baseColorResults', false );
			}
			if(isset($productlist ['listResults'] ['results'])){
				$tpl->assign ( 'productlist', $productlist ['listResults'] ['results'] );
			}
			$tpl->assign ( 'keyword', $productlist ['keyword'] );
			if (! empty ( $productlist ['keyword'] ['keywordList'] )) {
				$tpl->assign ( 'keywordnum', count ( $productlist ['keyword'] ['keywordList'] ) );
			}
			if (! empty ( $definedPropertyResults )) {
				$tpl->assign ( 'PropertyResults', $definedPropertyResults );
				$totalPropertyNumTemp += count($definedPropertyResults);
			}
			if($totalPropertyNumTemp >= $totalPropertyNum){
				$totalPropertyNum = 0;
			}
			$hiddenOneproperty = 1;
			if($propertyUrlArrayNum>0){//有属性筛选，所有属性都将显示
				$hiddenOneproperty = 1;
				$totalPropertyNum = 1;
			}
			$tpl->assign('totalPropertyNum',$totalPropertyNum);
			$tpl->assign('hiddenOneproperty',$hiddenOneproperty);
			
			if (! empty ( $productlist ['productCategory']['childrenList'] )) {
				$tpl->assign ( 'productCategory', $productlist ['productCategory']['childrenList'] );
				if(isset($productlist ['productCategory']['isLast'])){
					$tpl->assign ( 'productCategoryIsLast', $productlist ['productCategory']['isLast']);
				}
				$tpl->assign('currentCid',$productlist ['productCategory']['categoryId']);
				//print_r($productlist ['productCategory']['childrenList']);
			}//elseif(!$cosplayclass && !empty($productlist ['productCategory'])){
			//	$tpl->assign ( 'productCategory', $productlist ['productCategory'] );
			//}
			if(!empty($productlist ['productCategory']) && isset($productlist ['productCategory']['isLast']) && !$productlist ['productCategory']['isLast'] && $parentName==''){
				$parentName = $productlist ['productCategory']['categoryName'];
			}
			$tpl->assign('parentName',$parentName!='' ? $parentName : \LangPack::$items['thing_glist_RelatedCategories']);
			$tpl->assign ( 'searchinfo', $productlist ['listResults'] );
			$tpl->assign ( 'result', $productlist );
			if (empty ( $productlist ['listResults'] ['results'] ) && $search == 1) {
				//to do 搜索数据为空的页面
				$tpl->assign ( 'empty', 1 );
			} else {
				$tpl->assign ( 'empty', 0 );
			}
			$tpl->assign ( 'searchClassName', $this->dostrip($searchClassName) );
			
			$tpl->assign('site_type','subCategory');
			if(isset($productlist ['categoryBreadcrumbNavigation'])){
				$tpl->assign('mainCid', urlencode($productlist ['categoryBreadcrumbNavigation']['categoryName']));
				if(isset($productlist ['categoryBreadcrumbNavigation']['nextCategory']['categoryName'])){
					$tpl->assign('subCid', urlencode($productlist ['categoryBreadcrumbNavigation']['nextCategory']['categoryName']));
				}
			}
			$tpl->display ( 'thing_glist.htm' );
		} else {
			//11023关键词黑名单处理
			if(isset ( $productlist ['code'] ) && $productlist ['code'] == 11023){
				if($tag){
					header ('HTTP/1.1 404 Not found');
           			//require ROOT_PATH.'errors/404.php';
					exit();
				}	
			}
			//999错误
			if(isset ( $productlist ['code'] ) && $productlist ['code'] == 999){
				header("Location:".ROOT_URL);
				exit();
			}
			//to do 数据为空的页面
			//将URL中的横线替换成下划线
			//$newurl .= '&v=' . $viewtype . '&s=' . $PageSize . '&page=' . $page;
			$newurl = $this->linetrip ( $newurl );
			if ($search == 1) {
				$tpl->assign ( 'empty', 1 );
				if ($keywords) {
					$keywords = $this->dostrip($keywords);
					$tpl->assign ( 'ClassName', $keywords );
					$tpl->assign ( 'textname', $keywords );
				} else {
					$tpl->assign ( 'ClassName', \LangPack::$items ['thing_glist_all'] );
				}
				$tpl->assign ( 'searchClassName', $this->dostrip($searchClassName) );
			} else {
				header('HTTP/1.1 302 Moved Permanently');//发出302头部
				header ( 'Location:' . ROOT_URL );
				exit();
				$tpl->assign ( 'empty', 0 );
			}
			$tpl->assign ( 'newurl', $newurl );
			$tpl->assign ( 'PageSize', $PageSize );
			$tpl->assign ( 'viewtype', $viewtype );
			$tpl->assign('site_type','subCategory');
			$tpl->assign('mainCid', urlencode($productlist ['categoryBreadcrumbNavigation']['categoryName']));
			if(isset($productlist ['categoryBreadcrumbNavigation']['nextCategory']['categoryName'])){
				$tpl->assign('subCid', urlencode($productlist ['categoryBreadcrumbNavigation']['nextCategory']['categoryName']));
			}
			
			$tpl->display ( 'thing_glist.htm' );
		}
	}
	
	/**
	 * 去掉反斜杠
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
	 * 将横线替换成下划线
	 */
	function linetrip($value) {
		if (is_array ( $value )) {
			$value = array_map ( 'self::linetrip', $value );
		} else {
			$value = stripslashes ( $value );
			$value = preg_replace ( '#[-]#', '_', $value );
			$value = htmlspecialchars_decode ( $value, ENT_NOQUOTES);
		}
		return $value;
	}
	
	/**
	 * 还原操作，将下划线还原成横线
	 */
	function settrip($value) {
		if (is_array ( $value )) {
			$value = array_map ( 'self::settrip', $value );
		} else {
			$value = preg_replace ( '#[_]#', '-', $value );
			$value = htmlentities ( $value,ENT_NOQUOTES,'UTF-8');
		}
		return $value;
	}
	
	/**
	 * 获取分类名称
	 * 
	 */
	function getClassName($classId) {
		$mClass = new \model\Navigator ();
		$classChildArray = array ();
		$classChildArray = $mClass->getNav ( $classId, '0:0:0', 86400 ); //目录基本不变动，缓存时间设为一天
		if (! empty ( $classChildArray ['selfCategory'] ) && $classChildArray ['code'] == 0) {
			if(isset($classChildArray ['selfCategory'] ['categoryAliasName']) && $classChildArray ['selfCategory'] ['categoryAliasName']!= ''){
				$className = $classChildArray ['selfCategory'] ['categoryAliasName'];
			}else{
				$className = $classChildArray ['selfCategory'] ['categoryName'];
			}
		} else {
			$className = '';
		}
		return $className;
	}
	
	/**
	 * 根据传入数据获取分类信息
	 */
	function getClassInfoFromRequst($array = array(), $classid = 0) {
		if (is_array ( $array ) && ! empty ( $array )) {
			foreach ( $array as $k => $v ) {
				if (isset($v ['categoryId']) && ($v ['categoryId'] == $classid)) {
					return $v;
				} else {
					if (isset ( $v ['childrenList'] ) && ! empty ( $v ['childrenList'] )) {
						$value = $this->getClassInfoFromRequst ( $v ['childrenList'], $classid );
						return $value;
					}
				}
			}
		}
	}
	
	/**
	 * 从面包屑获取对应分类名
	 */
	function getCurrentCategories($cate,$class){
		$bread = '';
		if(!empty($cate) && isset($cate['categoryId']) && $cate['categoryId'] == $class){
			$bread = $cate['categoryName'];
		}elseif(!empty($cate['nextCategory']) && is_array($cate['nextCategory'])){
			$bread = $this->getCurrentCategories($cate['nextCategory'],$class);
		} 
		return $bread;
	}
	
	/**
	 * 
	 * 重新组装面包削成为一维数组
	 * @param unknown_type $cateBread
	 */
	function getNewBreadArray($cateBread,$newArray = array()){
		if(!empty($cateBread) && !empty($cateBread['nextCategory'])){
			$newArray = $this->getNewBreadArray($cateBread['nextCategory'],$newArray);
			$newArray[] = $cateBread['categoryId'];
		}else{
			$newArray[] = $cateBread['categoryId'];
		}
		return $newArray;
	}
	
	/**
	*	获取omniture分类上下级字符串
	*	Jerry Yang
	*/
	function cat_omniture($ar){
		if(!is_array($ar)) return '';
		if(!isset($ar['categoryId'])){return '';}
		$temp_omn='';
		$temp_omn.='/c'.$ar['categoryId'];
		if(!empty($ar['nextCategory'])){
			$temp_omn=$temp_omn.($this->cat_omniture($ar['nextCategory']));
		}
		return $temp_omn;
	}
	
	/**
	 * 
	 * 生成属性SEO字符串，已排序
	 * @param unknown_type $proArray
	 * @param unknown_type $addArray
	 */
	function creatPropertySeo($proArray=array(),$addArray=array()){
		$propertyseoName = '';
		if(!empty($addArray)){
			if(!empty($proArray)){
				foreach($addArray as $key=>$val){
					if(is_array($val)){
						foreach($val as $key2=>$val2){
							$proArray[$key][$key2] = $val2;
						}
					}
				}
			}else{
				$proArray = $addArray;
			}
		}
		if(!empty($proArray)){
			//先排序数组
			ksort($proArray);
			foreach($proArray as $k=>$v){
				//$propertyseoName .= '/'.$k;
				if(!empty($v) && is_array($v)){
					ksort($v);
					foreach($v as $k2=>$v2){
						if($k2!='self'){
							$patterns = array("#['’\"\(\)\&\%\$\:\[\]\{\}\+\!\,]#u","#\s*[\-\*\/\.\<\>\:\+×]\s*|\s+#u",'#-{2,}#u','#\s+#u','#%#u');
							$replace = array("","-",'-','','%25');
							$v2 = trim($v2);
							$v2 = preg_replace( $patterns,$replace,$v2);
							$v2TempArray = explode('-',$v2);
							if(!empty($v2TempArray)){
								foreach($v2TempArray as $vtk=>&$vtv){
									$vtv = ucfirst($vtv);
								}
								$v2 = implode('-',$v2TempArray);
							}
							$propertyseoName .=  ucfirst ($v2).'-';
						}
					}
				}
			}
		}
		return $propertyseoName;
	}
	
	/**
	 * 判断当前目录是否是顶级目录
	 * 顶级目录返回true
	 * 其他返回code，此code包含从顶级到改级的ID信息，5位一层
	 * 
	 * @param unknown_type $cate
	 * @return boolean
	 */
	function isTopCategory($cid){
		if(!empty($cid)){
			$mProductList = new \Model\Product ();
			$productlist = $mProductList->getProductList ( array('pcs.categoryId'=>$cid,'pcs.requestSource'=>'category','pcs.pageNo'=>1,'pcs.languageCode'=>SELLER_LANG) );
			if(!empty($productlist['productCategory'])){
				if(strlen($productlist['productCategory']['categoryCode'])==5){
					return true;
				}else{
					$len = strlen($productlist['productCategory']['categoryCode']);
					$catArray = array();
					$catString = '';
					$j=0;
					for($i=0;$i<=$len;$i++){	
						if($j==5){
							$j=0;
							$catArray[] = $catString;
							$catString = '';
						}
						if($productlist['productCategory']['categoryCode']{$i}!=0){
							$catString .= $productlist['productCategory']['categoryCode']{$i};
						}
						$j++;
					}
					return $catArray;
				}
			}
		}
	}
}