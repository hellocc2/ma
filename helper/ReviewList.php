<?php
namespace Helper;

/**
 * 评论列表页综合处理
 * @author chengjun<chengjun@milanoo.com>
 *
 */
class ReviewList{
	
	/**
	 *
	 * 接口调用返回结果
	 */
	private $reviewResult;
	
	/**
	 * 评论列表页类目结果
	 * @var unknown_type
	 */
	private $reviewCategory;
	
	/**
	 * 当前分类ID
	 * @var unknown_type
	 */
	private $classId;
	
	/**
	 *
	 * 捕获错误，默认为false,当接口调用失败或参数不完整时为true，表示在实例化时发生错误
	 * boolean
	 */
	public $getWrong = false;
	
	public function __construct($params=array()){
		$data = array();
		$data['languageCode'] = SELLER_LANG;
		$data['categoryId'] = $params['rCid'];
		$data['pageNo'] = isset($params['pageNo']) ? $params['pageNo']  : 1 ;
		$data['pageSize'] = isset($params['pageSize']) ? $params['pageSize']  : 24 ;
		
		$this->classId =$params['rCid'];
				
		$mReview = new \Model\Reviews();
		
		
		$reviewResult = $mReview->getListResult($data);
		if(!empty($reviewResult) && $reviewResult['code']==0){
			$reviewResult = \Helper\String::strDosTrip($reviewResult);
			$this->reviewResult = $reviewResult;
		}else{
			$this->getWrong = true;
		}
		
		$categoryParam = array();
		$categoryParam['languageCode'] = SELLER_LANG;
		$categoryParam['categoryId'] = $params['rCid'];
		
		$categoryResult = $mReview->getListCategory($categoryParam);
		if(!empty($categoryResult) && $categoryResult['code']==0){
			$categoryResult = \Helper\String::strDosTrip($categoryResult);
			$this->reviewCategory = $categoryResult;
		}else{
			$this->getWrong = true;
		}
		
		return;
	}
	
	/**
	 * 获取错误状态
	 */
	public function getWrongStatus(){
		return $this->getWrong;
	}
	
	/**
	 * 获取返回结果
	 */
	public function getResult(){
		return $this->reviewResult;
	}
	
	/**
	 * 获取返回分类
	 */
	public function getCategoryResult(){
		return $this->reviewCategory;
	}
	
	/**
	 * 获取评论总数
	 */
	public function getReviewsTotalCount(){
		return !empty($this->reviewResult['listResults']['commentTotalCount']) ? $this->reviewResult['listResults']['commentTotalCount'] : 0 ;
	}
	
	/**
	 * 获取评论商品总数
	 */
	public function getProductsTotalCount(){
		return !empty($this->reviewResult['listResults']['totalCount']) ? $this->reviewResult['listResults']['totalCount'] : 0 ;
	}
	
	/**
	 * 获取故事列表
	 */
	public function getReview(){
		if(!empty($this->reviewResult['listResults']['results'])){
			foreach($this->reviewResult['listResults']['results'] as $key=>&$val){
				if(!empty($val['comment'])){
					$val['commentSorce'] = round($val['commentSorce']);
					$val['commentSorce'] = $val['commentSorce']>5 ? 5 : $val['commentSorce'];
					$val['commentSorce'] = $val['commentSorce']<0 ? 0 : $val['commentSorce'];
					$reviewAddTime = strtotime($val['comment']['commentTime']);
					$val['comment']['commentTime'] = date("M d, Y",$reviewAddTime);
				}
			}
			return $this->reviewResult['listResults']['results'];
		}
		return false;
	}
	
	/**
	 * 获取返回分类列表
	 */
	public function getCategory(){
		if(!empty($this->reviewCategory['productCategory'])){
			//如果当前分类是最底层分类，则返回的子级分类中必然存在当前级的同级及其本身，此时将返回值中的当前分类做上标记
			if(!empty($this->reviewCategory['productCategory']['childrenList']) && $this->reviewCategory['isLast']==1){
				foreach($this->reviewCategory['productCategory']['childrenList'] as $key=>&$val){
					if(empty($val['childrenList']) && $this->classId==$val['categoryId']){
						$val['current'] = 1;
					}
				}
			}
			return $this->reviewCategory['productCategory'];
		}
		return false;
	}
	
	/**
	 * 获取面包屑
	 */
	public function getBreadcrumbNavigation(){
		if(!empty($this->reviewCategory['categoryBreadcrumbNavigation'])){
			return $this->reviewCategory['categoryBreadcrumbNavigation'];
		}
		return false;
	}
	
	
	/**
	 *
	 * 重新组装面包削成为一维数组
	 * @param unknown_type $cateBread
	 */
	public function getNewBreadArray($cateBread,$newArray = array()){
		if(!empty($cateBread) && !empty($cateBread['nextCategory'])){
			$newArray = $this->getNewBreadArray($cateBread['nextCategory'],$newArray);
			$newArray[] = $cateBread['categoryId'];
		}else{
			$newArray[] = $cateBread['categoryId'];
		}
		return $newArray;
	}
	
	/**
	 * 通过面包屑获取父分类ID
	 * 如果当前分类为顶级分类，则返回本身
	 */
	public function getParentClassId(){
		if(!empty($this->reviewCategory['categoryBreadcrumbNavigation'])){
			$breadArray = $this->getNewBreadArray($this->reviewCategory['categoryBreadcrumbNavigation']);
			if(!empty($breadArray) && in_array($this->classId,$breadArray)){
				$currentKey = array_search ($this->classId,$breadArray);
				if(isset($breadArray[$currentKey+1])){
					return $breadArray[$currentKey+1];
				}else{
					return $this->classId;
				}
			}
		}
		return false;
	}
	
	/**
	 * 从面包屑获取对应分类名
	 */
	public function getCategoryNameFromBread($cate,$class){
		$bread = '';
		if(empty($cate)){
			$cate = $this->reviewCategory['categoryBreadcrumbNavigation'];
		}
		if(!empty($cate) && !empty($class)){
			if(!empty($cate) && isset($cate['categoryId']) && $cate['categoryId'] == $class){
				$bread = $cate['categoryName'];
			}elseif(!empty($cate['nextCategory']) && is_array($cate['nextCategory'])){
				$bread = $this->getCategoryNameFromBread($cate['nextCategory'],$class);
			}
		}
		return $bread;
	}
}