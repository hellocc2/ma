<?php
namespace Module\Producttags;
/**
 * 关键词首字母查询显示模块
 * @author Su Chao<suchaoabc@163.com>
 * @sinc 2011-10-17
 * @param int 
 * @param int 
 */
class Sort extends \Lib\common\Application {
	public function __construct() {

		$params=$params_all  =\Helper\RequestUtil::getParams();
		if (! isset ( $params->page )) {
			$pageNo = '1';
		} else {
			$pageNo = $params->page;
		}
		$firstletter = $params->sort;
		if (empty ( $_GET ['sort'] )) {
			header ( 'HTTP/1.1 404 Not found' );
			header ( 'Location:' . ROOT_URL );
			die ();
		}
		$pageSize = '81';
		$tpl = \Lib\common\Template::getSmarty ();
		$mTag = new \Model\KeyWords ();
		$tag_date_array = $mTag->getKeyWordsByLetter ( $firstletter,$pageSize, $pageNo );
		//java API 请求失败后跳转
		if ($tag_date_array ['code'] != 0) {
			header ( 'HTTP/1.1 404 Not found' );
			header ( 'Location:' . ROOT_URL . '404.php' );
			die ();
		}
		$num= $tag_date_array ['listResults']['totalCount'];
		$newurl = '?module=producttags&action=sort';
		if(!empty($pageNo)){
			$newurl .= '&page='.$pageNo;
		}
		if(!empty($firstletter)){
			$newurl .= '&sort='.$firstletter;
		}
		$pagenav = \Helper\Page::getpage ( $num, $pageSize, $pageNo, $newurl,'' );
		$tpl->assign( 'pages', $pagenav );
		$tag_date_result_list = $tag_date_array ['listResults']['keywordList'];
		
		$new_ar=array();
		foreach($tag_date_result_list as $v){
			$v['name']=stripslashes($v['name']);
			$new_ar[]=$v;
		}
		$tpl->assign ( 'sort', $firstletter );
		$tpl->assign ( 'keywords', $new_ar );
		//Omniture-------------------------------------------
		/* $tpl->assign( 'channel', "Tag List");
		$tpl->assign( 'prop1', 'Tag List');
		$tpl->assign( 'prop2', 'Tag List');
		$tpl->assign( 'prop3', "Tag List");
		$tpl->assign( 'prop4', 'Tag List'); */
		//---------------------------------------------------
		$tpl->assign('meta_tags',strtolower($params->sort));//tags页面seo
		$tpl->display ( 'tag.htm' );
	}
}


