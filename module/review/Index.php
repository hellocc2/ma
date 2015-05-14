<?php 
namespace Module\Review;
/**
 * reviews首页
 * @author jiangcai@milanoo.com
 *
 */
class Index extends \Lib\common\Application{
	public function __construct(){
		$tpl=\Lib\common\Template::getSmarty();

		//获取最近评论商品
		$reviews=new \Model\Reviews();
		$reviewSearch=array('languageCode'=>SELLER_LANG);
		$result=$reviews->getRecent($reviewSearch);
		
		if(!empty($result) && $result['code']==0){
			$result=\Helper\String::strDosTrip($result);
			if(isset($result['listResults']) && !empty($result['listResults'])){
				$tpl->assign('recentReviews',$result['listResults']);
			}
		}

		//精彩故事
		$story=new \Model\Story();
		$storySearch=array('langCode'=>SELLER_LANG);
		$storysInfo=$story->getStoryList($storySearch);
		if(!empty($storysInfo) && $storysInfo['code']==0){
			$storysInfo=\Helper\String::strDosTrip($storysInfo);
			if(!empty($storysInfo['storys']['0']['addTime'])){
				$storysInfo['storys']['0']['addTime']=date('F j,Y',strtotime($storysInfo['storys']['0']['addTime']));
			}
			$tpl->assign('storyFirst',$storysInfo['storys']['0']);//显示第一条故事
		}
		
		//获取类目信息
		$comments=new \Model\Reviews();
		$commentSearch=array('languageCode'=>SELLER_LANG);
		$reComments=$comments->getIndexCategory($commentSearch);
		if(!empty($reComments) && $reComments['code']==0){
			$reComments=\Helper\String::strDosTrip($reComments);
			if(isset($reComments['listResults']) && !empty($reComments['listResults'])){
				$tpl->assign('CategoryReviews',$reComments['listResults']);
			}
		}
	 
		if(!defined('IMG_SHRINK')) define('IMG_SHRINK','mystory/u/');
		$tpl->display('review_index.htm');
		return;
	}
}