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
		$datahigh['his_note']=1;//最高
		$datalow['his_note']=2;//最低
		$high=$history->selectHistory($datahigh);
		$low=$history->selectHistory($datalow);
		
		//echo '<pre/>';print_r($high);print_r($low);exit;
		if(!empty($high['his_date'])){
			$tpl->assign('his_date',implode(',',$high['his_date']));
		}
		
		if(!empty($high['his_point'])){
			$tpl->assign('his_point_high',implode(',',$high['his_point']));
		}
		
		if(!empty($low['his_point'])){
			$tpl->assign('his_point_low',implode(',',$low['his_point']));
		}
		
		
		
		$tpl->display ( 'operate_chart.html' );
	}
}



