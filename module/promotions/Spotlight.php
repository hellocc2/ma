<?php
/**
 * Spotlight
 * Author:@ChengJun<cgjp123@163.com>
 */
namespace Module\Promotions;
use Helper\RequestUtil as R;
class Spotlight extends \Module\promotions\NewHotProducts {
	public function __construct(){
		//R::resetParam('sort','recommended');
		//R::resetParam('sortby',0);
		$type = R::getParams ('t');//展示类型（必需）new 或者hot
 		$classId = R::getParams ('c');//目录ID
 		$viewType = R::getParams ('v');
 		$sort = R::getParams ('sort');//排序方式
 		$sortby = R::getParams ('sortby');//排序方式 ，1 升序  ， 0降序
 		$page = R::getParams ('page');
 		$pageSize = R::getParams ('s');
 		$priceRange = R::getParams ('priceRange');
 		$searchPrice = R::getParams ('searchPrice');//价格搜索标记
 		$priceRangeMin = R::getParams('priceRange_min');
 		$priceRangeMax = R::getParams('priceRange_max');
		R::resetParam('params',array('t'=>'hot','c'=>$classId,'v'=>$viewType,'sort'=>$sort,'sortby'=>$sortby,'page'=>$page,'s'=>$pageSize,'priceRange'=>$priceRange,'searchPrice'=>$searchPrice,'priceRange_min'=>$priceRangeMin,'priceRange_max'=>$priceRangeMax));
		//print_r(R::getParams());exit;
		parent::__construct();
	}
}