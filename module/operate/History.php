<?php

namespace Module\operate;

use Helper\RequestUtil as R;

/**
 * 历史行情
 */
class History extends \Lib\common\Application {
	public function __construct() {
		//$client=\Helper\CheckLogin::sso();
		$tpl = \Lib\common\Template::getSmarty ();
		$act = R::getParams ('act');
		$result=array('code'=>500,'msg'=>'操作失败');
		$history=new \Model\History();
				
		switch($act){
			case 'add':
				$tpl->assign('time',time());
				$tpl->display ( 'operate_history_add.html' );
				exit;
			break;
			case 'addpost':
				$hdate=R::getParams ('hdate');
				$htime=R::getParams ('htime');
				$trend=R::getParams ('trend');
				$point=R::getParams ('point');
				$note=R::getParams ('note');
				$open=R::getParams ('open');
				$close=R::getParams ('close');
				$thing=R::getParams ('thing');
				//$memberId=R::getParams ('memberId');
				$gmt_create=time();
				
				$data=array();
				$data['hdate']=$hdate;
				$data['htime']=$htime;
				$data['trend']=$trend;
				$data['point']=$point;
				$data['note']=$note;
				$data['open']=$open;
				$data['close']=$close;
				$data['thing']=$thing;
				//$data['memberId']=$memberId;
				$data['gmt_create']=$gmt_create;				
				
				$res=$history->addHistory($data);
				if($res){
					$result=array('code'=>200,'msg'=>'操作成功');
				}
				echo json_encode($result);	exit;		
			break;
			default:
				$tpl->display ( 'operate_history.html' );
			
		}
		
	}
}



