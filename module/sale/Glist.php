<?php
namespace Module\Sale;
use \Helper\ResponseUtil as R;

class Glist extends \Lib\Common\Application {
	public function __construct(){
		$tpl = \Lib\Common\Template::getSmarty();
		$params = \Helper\RequestUtil::getParams();
		$classId = $params->class;
		if(empty($classId)){
			header('Location:'.R::rewrite(array('url'=>'?module=sale&action=index','isxs'=>'no')));
			exit;
		}
		
		$newurl = '?module=sale&action=glist&class='.$classId.'&promotiontype='.$params->promotiontype;
		$curUrlTag = $params->promotiontype;
		$libkey = !empty($params->promotiontype) && $params->promotiontype=='clearance'  ? 'CLEAROUT' :'';
		$pageNo = !empty($params->page) ? $params->page : 1;
		$pageSize = !empty($params->s) ? $params->s : '';
		$viewtype = !empty($params->v) ? $params->v : '';
		$sort = !empty($params->sort) ? $params->sort : '';
		$sortBy = !empty($params->sortby) ? $params->sortby : '0';
		
		if($pageNo!=1){
			$newurl .= '&page='.$pageNo;
		}
		
		if(empty($viewtype)){
			if (isset ( $_COOKIE ['viewtype'] ) && $_COOKIE ['viewtype'] != '') {
				$viewtype = $_COOKIE ['viewtype'];
				if($viewtype!=='list' && $viewtype!=='text' && $viewtype!=='grid' &&  $viewtype!=='stream'){
					$viewtype = 'text';
				}
			} else {
				$viewtype = 'text';
			}
		}else{
			if($viewtype!=='list' && $viewtype!=='text' && $viewtype!=='grid' &&  $viewtype!=='stream'){
				$viewtype = 'text';
			}
			setcookie('viewtype',$viewtype,0,'/');
		}
		
		if (empty($pageSize)) {
			if (isset ( $_COOKIE ['pagesize'] ) && $_COOKIE ['pagesize'] != '') {
				$pageSize = $_COOKIE ['pagesize'];
				if($pageSize!='24' && $pageSize!='36' && $pageSize!='48'){
					$pageSize = 36;
				}
			} else {
				$pageSize = 36;
			}
		} else {
			if($pageSize!='24' && $pageSize!='36' && $pageSize!='48'){
				$pageSize = 36;
			}
			setcookie('pagesize',$pageSize,0,'/');
		}
		
		//排序筛选
		$sortjson = '';
		if (!empty($sort)) {
			if($sort!=='recommend' && $sort!=='addedTime' && $sort!=='sortPrice'){
				$sort = 'recommend';
				$sortBy = 0;
			}
			$sortjson = $sort . ':' . $sortBy;
			$newurl .= '&sort=' .$sort.'&sortby='.$sortBy;
		}
		
		$data = array();
		$data['classId'] = $classId;
		$data['libkey'] = $libkey;
		$data['sort'] = $sortjson;
		$data['pageNo'] = $pageNo;
		$data['pageSize'] = $pageSize;
		$listM = new \Helper\Sale($data);
		
		//获取分类
		$indexResult = $listM->getIndexResult();
		$saleCategoryResult = array();
		if(!empty($indexResult['categories'])){
			foreach($indexResult['categories'] as $k=>&$v){
				$saleCategoryResult['result'][] = @array('categoryId'=>$v['categoryId'],
														'categoryName'=>$v['categoryName'],
														'categoryAliasName'=>$v['categoryAliasName'],
														'num'=>$v['num'],
														'clearOutNum'=>$v['clearOutNum'],
														'categoryCode'=>$v['categoryCode']);
			}
			unset($v);
			$saleCategoryResult['code'] = 0;
			$saleCategoryResult['msg'] = '操作成功';
		}else{
			$saleCategoryResult['code'] = 1;
			$saleCategoryResult['msg'] = '数据获取失败';
		}
		
		//获取TOPSELLING
		$topSelling = $listM->getTopSellingResult();
		$topSellingResult = array();
		if(!empty($topSelling)){
			$topSellingResult['result'] = $topSelling['topSellingResults'];
			$topSellingResult['code'] = 0;
			$topSellingResult['msg'] = '操作成功';
		}else{
			$topSellingResult['code'] = 1;
			$topSellingResult['msg'] = '数据获取失败';
		}
		
		//获取商品
		$productList = $listM->getSaleResult();
		$productListResult = array();
		if(!empty($productList['products'])){
			foreach($productList['products'] as $k=>&$v){
				
				$v['productPicturesArr']=preg_replace('/^/','http://www.mlo.me/upen/l/',$v['productPicturesArr']);
				$v['productPicturesArr']=implode(',',$v['productPicturesArr']);
				$productListResult['result'][] = $v;
			}
			unset($v);
			$productListResult['categoryId'] = $productList['categoryId'];
			$productListResult['categoryName'] = $productList['categoryName'];
			$productListResult['totalCount'] = $productList['totalCount'];
			$productListResult['viewType'] = $viewtype;
			$productListResult['pageSize'] = $pageSize;
			$productListResult['page'] = $pageNo;
			$productListResult['sort'] = !empty($sort) ? $sort : 'recommend';
			$productListResult['sortby'] = !empty($sortBy) ? $sortBy : '0';
			$productListResult['newurl'] = $newurl;
			$productListResult['code'] = 0;
			$productListResult['msg'] = '操作成功';
			$totalCount = $productList['totalCount'];
			$pages = \Helper\Page::getpage($totalCount,$pageSize,$pageNo,$newurl,$productList['categoryName']);
		}else{
			$productListResult['code'] = 1;
			$productListResult['msg'] = '数据获取失败';
			$pages = '';
		}
		
		$tpl->assign('saleCategoryResult',$saleCategoryResult);
		$tpl->assign('topSellingResult',$topSellingResult);
		$tpl->assign('productListResult',$productListResult);
		$tpl->assign('pages',$pages);
		$tpl->assign('curUrlTag',$curUrlTag);
		$tpl->display('sale_glist.htm');
	}
}