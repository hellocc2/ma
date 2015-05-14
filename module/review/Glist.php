<?php 
namespace Module\Review;
use \Helper\ResponseUtil as RW;
use \Helper\RequestUtil as R;
/**
 * 
 * @author chengjun<chengjun@milanoo.com>
 *
 */
class Glist extends \Lib\common\Application{
	public function __construct(){
		$tpl = \Lib\common\Template::getSmarty ();
		$params = R::getParams();
		
		$rCid = isset($params->id) ? $params->id : 0;
		$page = isset($params->page) ? $params->page : 1;
		$pageSize = isset($params->s) ? $params->s : 24;
		
		if(empty($rCid)){
			header('Location:'.\Helper\ResponseUtil::rewrite(array('url'=>'?module=review&action=index','isxs'=>'no')));
			exit();
		}
		
		if(!$page) $page = 1;
		
		if($pageSize!=24) $pageSize = 24;
		
		
		$data = array();
		$data['rCid'] = $rCid;
		$data['pageNo'] = $page;
		$data['pageSize'] = $pageSize;
		
		$mReview = new \Helper\ReviewList($data);
		if($mReview->getWrongStatus()==false){
			//评论内容
			$result = $mReview->getReview();
			if(!empty($result)){
				$commentTotalCount = $mReview->getReviewsTotalCount();
				$productTotalCount = $mReview->getProductsTotalCount();
				$tpl->assign('commentTotalCount',$commentTotalCount);
				$tpl->assign('productTotalCount',$productTotalCount);
				$tpl->assign('reviewResult',$result);
			}
			
			//相关类目
			$category = $mReview->getCategory();
			if(!empty($category)){
				/**
				 * 当前分类有子分类时，则获取当前分类名称
				 * 当前分类无子分类时，则获取上级分类名称
				 */
				if(isset($category['isLast']) && $category['isLast']==0){
					$parentName = $category['categoryName'];
				}else{
					$parentClassId = $mReview->getParentClassId();
					$parentName = $mReview->getCategoryNameFromBread('',$parentClassId);
				}
				$tpl->assign('parentName',$parentName);
				
				//面包屑
				$breadResult = $mReview->getBreadcrumbNavigation();
				$tpl->assign('breadResultArray',$breadResult);
				
				//当前相关类目
				$tpl->assign('category',$category['childrenList']);
				
				//当前类目信息
				$tpl->assign('className',$category['categoryName']);
				$tpl->assign('classId',$category['categoryId']);
			}
			
			$url = '?module=review&action=glist&id='.$rCid;
			$pages = \Helper\Page::reviewPage($productTotalCount,$pageSize,$page,$url,$category['categoryName']);
			$tpl->assign('pages',$pages);
			
			$tpl->assign('rCid',$rCid);
			$tpl->display('review_list.htm');
		}else{
			exit('接口调用失败');
		}
		return '';
	}
}