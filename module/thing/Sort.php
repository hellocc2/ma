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
		
		if (! isset ( $_GET ['sort'] )) {
			header ( 'HTTP/1.1 404 Not found' );
			header ( 'Location:' . ROOT_URL );
			die ();
		}

		if (! isset ( $_GET ['page'] )) {
			$pageno = '1';
		} else {
			$pageno = $_GET ['page'];
		}
		
		$firstletter = $_GET ['sort'];
		$modulename = 'keyword';
		$activename = 'getKeywordByLetter';
		$pagesize = '81';
		
		$param = array (languageCode => 'en-uk', firstLetter => $firstletter, pageSize => $pagesize, pageNo => $pageno );
		
		$tpl = \Lib\common\Template::getSmarty ();
		$mTag = new \Model\Tag ();
		$tag_date_array = $mTag->getTag ( $modulename, $activename, $param );
		
		//var_dump($tag_date_array);exit;
		
		//java API 请求失败后跳转
		if ($tag_date_array ['returnCode'] == 1) {
			header ( 'HTTP/1.1 404 Not found' );
			header ( 'Location:' . ROOT_URL . '404.php' );
			die ();
		}
		
		$tag_date_result_list = $tag_date_array ['resultList'];
		
		$tpl->assign ( tag_first_letter, $firstletter );
		$tpl->assign ( tag_date_array, $tag_date_result_list );
		
		$tpl->display ( 'tag.htm' );
	}
}


