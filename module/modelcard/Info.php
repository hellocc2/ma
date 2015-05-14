<?php
namespace Module\modelcard;
use Helper\RequestUtil as R;
use Helper\ResponseUtil as rew;
use Helper\String as H;
use Helper\Js as JS;
use Helper\Page as P;
/**
 * 商品详情页
 * @Jerry Yang<yang.tao.php@gmail.com>
 *
 */
class Info extends \Lib\common\Application{	
	function __construct(){
		$tpl = \Lib\common\Template::getSmarty ();
		if($_POST){
			$data['modelUserName'] = R::getParams('mode_card_name');
			$data['dressSize'] = R::getParams('clothes_size');
			$data['height'] = R::getParams('height');
			$data['bust'] = R::getParams('bust');
			$data['waist'] = R::getParams('waist');
			$data['hips'] = R::getParams('hip');
			$data['desc'] = R::getParams('content');
			$data['isVideo'] = R::getParams('display_style');
			$data['videoUrl'] = R::getParams('video');
			$data['userImage'] = R::getParams('picture');
			$tpl->assign('data',$data);
		}else{
			//include(ROOT_PATH.'errors/notfound.php');
			//return;
		}
		if(isset($data['isVideo']) && $data['isVideo'] == 1){
			$tpl->display('model_mini.html');
		}else{
			$tpl->display('model_demo.html');
		}
	}
}
?>