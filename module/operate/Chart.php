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
		$data=array();
		$data['his_note']=1;//最高
		$data['his_note']=2;//最低
		$high=$history->selectHistory($data);
		//echo '<pre/>';print_r($res);exit;
		if(!empty($high['his_date'])){
			$tpl->assign('his_date',implode(',',$high['his_date']));
		}
		
		if(!empty($high['his_point_high'])){
			$tpl->assign('his_point_high',implode(',',$high['his_point_high']));
		}
		
		
		
		$tpl->display ( 'operate_chart.html' );
	}
}



