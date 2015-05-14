<?php
namespace Module\Thing;
use Helper\RequestUtil as R;
use Helper\String as H;
use Helper\Js as JS;
use Helper\Page as P;
class Comments extends \Lib\common\Application{	
	function __construct(){

		$tpl = \Lib\common\Template::getSmarty ();
		$params_all = R::getParams();
		$productId = R::getParams('id');
		//isset($params_all->params['id']) ? $params_all->params['id'] : 0;
		/******************************************************************/
		/*                     获取商品详细信息开始                          */
		/******************************************************************/
		$mProduct = new \Model\Product ();
		$pObject = new \Helper\ProductsDetails($productId);
		$result = $pObject->GetProductsDetails();
		//print '<pre>';print_r($result);exit;
		if(isset($result['productDetails'])){
			$productsInfo = $result['productDetails'];
			if($productsInfo['productsActivator'] == -1 || empty($productsInfo['productName'])){
				include(ROOT_PATH.'errors/notfound.php');
				return false;
			}
			if(isset($productsInfo['productPicturesArr']) && is_array($productsInfo['productPicturesArr'])){
				$tpl->assign('productsFirstPicture',$productsInfo['productPicturesArr'][0]);	
			}
			$tpl->assign('productsDetails',$productsInfo);	
			
			/**
			 * 获取商品的销售属性
			 */
			$productPropertys = array();
			if(!empty($productsInfo['productPropertys'])){
				$productsInfo['productPropertys'] = H::strDosTrip($productsInfo['productPropertys']);
				foreach($productsInfo['productPropertys'] as $k=>$v){
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
			$tpl->assign('productPropertys',$productPropertys);	
			/**
			 * 获取商品的价格
			 */
			$productsPrice = $pObject->getProductsPrice();
			$tpl->assign('productsPrice',$productsPrice);
		}else{
			include(ROOT_PATH.'errors/notfound.php');
			return false;
		}
		
		//$pageNo = isset($params_all->params['page']) ? $params_all->params['page'] : 1;
		//$act = isset($params_all->params['act']) ? $params_all->params['act'] : 'comment';
		$pageNo = R::getParams('page');
		if(intval($pageNo) == 0) $pageNo = 1;
		$act = R::getParams('act');
		if(empty($act)) $act = "comment";
		$tpl->assign('act',$act);	//评论类型
		if(empty($pageNo) || !is_numeric($pageNo)){$pageNo=1;}
		$cPageNo = $pageNo;
		if($act == 'qa'){
			$cPageNo = 1;
		}
		$pageSize=6;
		$comment_obj=new \Model\Comment ();
		$comments=$comment_obj->getCommentsByPid($productId,MAIN_WEBSITEID,$cPageNo,$pageSize);
		$reviewsCount = 0;
		//print '<pre>';print_r($comments);exit;
		if(isset($comments ['listResults'] ['totalCount']) && $comments ['listResults'] ['totalCount']!=0 ){
			$reviewsCount = $comments ['listResults'] ['totalCount'];
		}
		if(isset($comments ['listResults']['results'])){
			foreach($comments ['listResults']['results'] as $key=>$v){
				$comments ['listResults']['results'][$key]['commentContent'] = stripslashes($v['commentContent']);
				if(isset($comments ['listResults']['results']['commentReplyList'])){
					foreach($comments ['listResults']['results']['commentReplyList'] as $rk=>$rv){
						$comments ['listResults']['results'][$key]['commentReplyList'][$rk]['replyContent'] = stripcslashes($rv['replyContent']);
					}
				}
			}
			$tpl->assign('comments',$comments ['listResults']['results']);//评论列表
		}
		$tpl->assign('reviewsCount',$reviewsCount);	//评论总数
		$newurl = '?module=thing&action=comments&id=' . $productId . '&act=comment';
		$pages = \Helper\Page::newgetpage ( $reviewsCount, $pageSize, $cPageNo, $newurl, stripslashes($productsInfo['productName']),'.html' ,0,'');
		
		//-----------------------商品评论分数------------------------
		if(isset($productsInfo['comments'])){
			$comments_proce = $productsInfo['comments'];
			
			krsort($comments_proce);
			$comment_star_list = array();
			//print '<pre>';print_r($comments_proce);exit;
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
		//------------------------------QA---------------------------------
		if($act == 'comment'){
			$pageNo = 1;
		}
		//$typeid = isset($params_all->params['typeid']) ? $params_all->params['typeid'] : 0;
		$typeid = intval(R::getParams('typeid'));
		$tpl->assign('typeid',$typeid);
		$qa = new \Model\Qa();
		$qa_data = array(
			'productId'=>$productId,
			'pageSize'=>$pageSize,
			'typeId'=>$typeid,
			'pageNo'=>$pageNo,
		);
		$qa_result = $qa->getQAGroupByType($qa_data);
		if(isset($qa_result['code']) && $qa_result['code']==0){
			foreach ($qa_result['QA'] as $key=>$v){
				$qPageNo = $v['id'] != $typeid ? 1 : $pageNo;
				$newurl = '?module=thing&action=comments&act=qa&typeid='.$v['id'].'&id=' . $productId;
				$pagenav = \Helper\Page::newgetpage ( $v['totalCount'], $pageSize, $qPageNo, $newurl, stripslashes($productsInfo['productName']),'.html' ,0,'');
				$qa_result['QA'][$key]['pagenav'] = $pagenav;
			}
			$tpl->assign('qa_category_list',$qa_result);
		}
		$qa_type_result = $qa->getAllQAType();
		if(isset($qa_type_result['code']) &&  $qa_type_result['code'] == 0 && isset($qa_type_result['QAType'])){
			$tpl->assign('qa_type_list',$qa_type_result['QAType']);
		}
		
		//print '<pre>';print_r($qa_result);exit;
		//------------------------------QA---------------------------------
		//print '<pre>';print_r($comment_star_list);
		/**
		 * 获取登陆的用户名和邮箱
		 */
		$tpl->assign('login_user',isset($_SESSION[SESSION_PREFIX . "MemberUserName"]) ? $_SESSION[SESSION_PREFIX . "MemberUserName"] : '');
		$tpl->assign('login_user_email',isset($_SESSION[SESSION_PREFIX . "MemberEmail"]) ? $_SESSION[SESSION_PREFIX . "MemberEmail"] : '');
		$tpl->assign('productId',$productId);
		$tpl->assign('pagenav',$pages);
		$tpl->display('comments.htm');
	}
}
?>