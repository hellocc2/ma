<?php

namespace Module\operate;

use Helper\RequestUtil as R;

/**
 * 统计表格
 */
class Chart extends \Lib\common\Application {
	public function __construct() {
		//$client=\Helper\CheckLogin::sso();
		$tpl = \Lib\common\Template::getSmarty ();
				
		$history=new \Model\History();
		$res=$history->selectHistory();
		//echo '<pre/>';print_r($res);exit;
		if(!empty($res['his_date'])){
			$tpl->assign('his_date',implode(',',$res['his_date']));
		}
		
		if(!empty($res['his_point_high'])){
			$tpl->assign('his_point_high',implode(',',$res['his_point_high']));
		}
		
		$tpl->display ( 'operate_chart.html' );
	}
}



