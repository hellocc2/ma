<?php 
namespace Module\Story;
use \Helper\RequestUtil as R;
/**
 * 
 * @author chengjun<chengjun@milanoo.com>
 *
 */
class Index extends \Lib\common\Application{
	public function __construct(){
		$tpl = \Lib\common\Template::getSmarty ();
		
		$pageUrl = '?module=story&action=index';
		
		$params = R::getParams();
		
		$page = isset($params->page) ? $params->page : 1;
		$pageSize = isset($params->s) ? $params->s : 16;
		$sort = isset($params->sort) ? $params->sort : 'newest';
		
		if(!$page) $page = 1;
		
		if($pageSize!=16) $pageSize = 16;
		
		if(isset($params->sort)){
			$pageUrl .= '&sort='.$sort;
		}
		if($sort!='newest' && $sort!='view' && $sort!='support'){
			$sort = 'newest';
		}
		
		$data = array();
		$data['pageNo'] = $page;
		$data['pageSize'] = $pageSize;
		$data['sort'] = $sort;
		
		try{
			$mStory = new \Helper\Story($data);
		}catch (\Exception $e){
			//异常发生
			header('HTTP/1.1 301 Moved Permanently');//发出301头部
			header('location:'.ROOT_URL);
			exit;
		}
		
		//获取请求结果
		$result = $mStory->getStory();
		if(!empty($result)){
			foreach($result as $key=>$val){
				if(isset($val['firstPicUrl'])){
					if(strpos($val['firstPicUrl'], 'http://')===false){
						$result[$key]['firstPicUrl'] = STORY_IMG.'mystory/u/'.$val['firstPicUrl'];
					}
				}elseif(empty($val['firstPicUrl'])){
					//$result[$key]['firstPicUrl'] = IMAGE_GLOBAL_URL.'story_defalut_img.png';
					$result[$key]['firstPicUrl'] = '';
				}
				$result[$key]['content'] = htmlspecialchars($val['content']);
				$result[$key]['title'] = htmlspecialchars($val['title']);
			}
			$totalCount = $mStory->getTotalCount();
			$pages = \Helper\Page::storyPage($totalCount,$pageSize,$page,$pageUrl);
			$tpl->assign('pages',$pages);
			$tpl->assign('storyResult',$result);
		}
		
		$tpl->assign('listPageParams',$mStory->getListParam());
		$tpl->assign('comment_img_url',STORY_IMG);
		$tpl->assign('sort',$sort);
		$tpl->display('story_index.htm');
		
		return 0;
	}
}