<?php
namespace Module\Producttags;
/**
 * 关键词首字母查询显示模块
 * @author Su Chao<suchaoabc@163.com>
 * @sinc 2011-10-17
 * @param int 
 * @param int 
 */
class Index extends \Lib\common\Application {
	public function __construct() {
	
		if(SELLER_LANG=='en-uk'){
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ".ROOT_URL);
			exit;
		}
		
		$tpl = \Lib\common\Template::getSmarty ();
		$params=\Helper\RequestUtil::getParams();
		$params=$params->params;
		if (! isset ( $params['index'] )) {
			$pageNo = '1';
		} else {
			$pageNo = (int)$params['index'];
		}
		if($pageNo<=0) $pageNo=1;
		$tpl->assign('index',$pageNo);
		$firstletter = isset($params['sort'])?$params['sort']:'';
		$pageSize = '80';
		// echo '<pre>';
		// print_r($params);
		// die;
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
		$url_order	="?module=producttags";
		$tpl->assign( 'url_order', $url_order );
		$url_p	=$url_order;
		
		for($i=1;$i<=ceil($num/1000/80);$i++){
			$arrtag[$i-1]['index']=($i-1)*1000+1;
			$arrtag[$i-1]['title']=(($i-1)*1000+1).'-'.($i*1000);
			$arrtag[$i-1]['sel']=($pageNo>=($i-1)*1000+1 && $pageNo<=$i*1000)?true:'';
		}
		$tpl->assign('arrtag',$arrtag);
		
		$pages	= ceil( $num / $pageNo );
		$page	= min( $pages, $pageNo );
		$fpage = (ceil($page/1000)-1)*1000+1;
		$epage = $fpage+999;
		$epage = min($pages,$epage);
		for($i=$fpage;$i<=$epage;$i++){
			$arrpage[$i]['title']=$i;
			$arrpage[$i]['link']=\Helper\ResponseUtil::rewrite(array('url'=>$url_p."&index=".$i,'isxs'=>'no'));
			$arrpage[$i]['sel']=$page==$i?true:'';
		}
		
		$tpl->assign( 'arrpage', $arrpage );
		$tag_date_result_list = $tag_date_array ['listResults']['keywordList'];
		
		$tpl->assign ( 'sort', $firstletter );
		foreach($tag_date_result_list as $v){
			$v['name']=stripslashes($v['name']);
			$new_ar[]=$v;
		}
		$tpl->assign('keywordstags', $new_ar);
		
		//Omniture-------------------------------------------
	/* 	$tpl->assign( 'channel', "Tag List");
		$tpl->assign( 'prop1', 'Tag List');
		$tpl->assign( 'prop2', 'Tag List');
		$tpl->assign( 'prop3', "Tag List");
		$tpl->assign( 'prop4', 'Tag List'); */
		//---------------------------------------------------
		if(!empty($params['sort'])){
			$tpl->assign('meta_tags',strtolower($params['sort']));//tags页面seo
		}
		$tpl->display ( 'producttags.htm' );
	}
}


