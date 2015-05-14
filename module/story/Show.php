<?php 
namespace Module\Story;
use Helper\RequestUtil as R;
/**
 * 
 * @author jiangcai@milanoo.com
 * @2012-9-13
 *
 */
class Show extends \Lib\common\Application{
	public function __construct(){
		
		$tpl = \Lib\common\Template::getSmarty ();
		
		//------------------获取story其他页面传入参数---------------------------------
		$storyId= R::getParams('id');//获取故事ID
		$listPageParams=R::getParams('listPageParams');//获取当前故事在数据库中排序
			
		if(!empty($storyId) && !empty($listPageParams)){
			//------------------调用接口member/story/storyInfo------------------------
			$story=new \Model\Story();
			$storySearch=array('id'=>$storyId,'listPageParams'=>$listPageParams,'langCode'=>SELLER_LANG);
			$storyInfo=$story->getStoryInfo($storySearch);

			//------------------处理story接口返回的数据------------------------------------
			if(!empty($storyInfo)&& $storyInfo['code']==0){
				$storyInfo=\Helper\String::strDosTrip($storyInfo);
				if(isset($storyInfo['story'])&&!empty($storyInfo['story'])){
					if(!empty($storyInfo['story']['addTime'])){
						$storyInfo['story']['addTime']=date('F j,Y',strtotime($storyInfo['story']['addTime']));
						}
					if(isset($storyInfo['story']['videoUrl']) && !empty($storyInfo['story']['videoUrl'])){
						if(strstr($storyInfo['story']['videoUrl'],'src')){
							$subject=$storyInfo['story']['videoUrl'];
							$pattern='/(https:\/\/|http:\/\/).*?\"/';
							preg_match($pattern,$subject,$video);
							$newvideo=preg_replace('/\"/','',$video);
							$storyInfo['story']['videoJmp']=0;
							
							$tpl->assign('newvideo',$newvideo[0]);
						}else{
							$tpl->assign('newvideo',$storyInfo['story']['videoUrl']);
						}
					}
					
					$tpl->assign('storyContent',htmlspecialchars($storyInfo['story']['content'],ENT_NOQUOTES, "UTF-8"));//SEO用
					
					$storyInfo['story']['title']=htmlspecialchars($storyInfo['story']['title'],ENT_NOQUOTES, "UTF-8");
					$storyInfo['story']['content']=nl2br(htmlspecialchars($storyInfo['story']['content'],ENT_NOQUOTES, "UTF-8"));
					$storyInfo['story']['nickName']=htmlspecialchars($storyInfo['story']['nickName'],ENT_NOQUOTES, "UTF-8");
					$storyInfo['story']['countryName']=htmlspecialchars($storyInfo['story']['countryName'],ENT_NOQUOTES, "UTF-8");
					$storyInfo['story']['city']=htmlspecialchars($storyInfo['story']['city'],ENT_NOQUOTES, "UTF-8");
					
					$tpl->assign('storyInfo',$storyInfo['story']);
					$tpl->assign('storyTitle',$storyInfo['story']['title']);//面包屑用
				}else{
					$jumpUrl = \Helper\ResponseUtil::rewrite(array('url'=>'?module=story&action=index','isxs'=>'no'));
					header('location:'.$jumpUrl);
					exit;
				}
				
				if(isset($storyInfo['storyPre'])&&!empty($storyInfo['storyPre'])){
					$tpl->assign('storyPre',$storyInfo['storyPre']);
				}
				
				if(isset($storyInfo['storyNext'])&&!empty($storyInfo['storyNext'])){
					$tpl->assign('storyNext',$storyInfo['storyNext']);
				}
				
				if(!defined('IMG_ORIGINAL')) define('IMG_ORIGINAL','mystory/o/');
				
				
				//------------------显示摸板判断---------------------------------------------
				if(isset($storyInfo['story']['displayStyle']) && $storyInfo['story']['displayStyle']==1){
					$tpl->display('extTemplate/story/themeExt/story_show.htm');//扩展风格
				}else{
					$tpl->display('story_show.htm');//默认风格
				}
			
			}else{
				$jumpUrl = \Helper\ResponseUtil::rewrite(array('url'=>'?module=story&action=index','isxs'=>'no'));
				header('location:'.$jumpUrl);
				exit;
			}
			
		}else{
			$jumpUrl = \Helper\ResponseUtil::rewrite(array('url'=>'?module=story&action=index','isxs'=>'no'));
			header('location:'.$jumpUrl);
			exit;
		}
	
	}
}