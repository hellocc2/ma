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
		switch($act){
			case 'add':
				
				$tpl->assign('time',time());
				$tpl->display ( 'operate_history_add.html' );
				exit;
			break;
			default:
				$tpl->display ( 'operate_history.html' );
			
		}
		
	}
}



