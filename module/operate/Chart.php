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
		$res=$history->selectHistory();
		//echo '<pre/>';print_r($res);exit;
		
		//$chartData_3 ='[{"CUSTOMER":1,"date":"15-10-01"},{"CUSTOMER":6,"date":"15-10-02"},{"CUSTOMER":1,"date":"15-10-03"},{"CUSTOMER":5,"date":"15-10-04"},{"INTERNATIONAL":41,"date":"15-10-05"},{"INTERNATIONAL":11,"date":"15-10-06","DOCUMENT":2,"CUSTOMER":2,"DOMESTIC":1},{"INTERNATIONAL":11,"date":"15-10-07"},{"DOCUMENT":10,"date":"15-10-08","DOMESTIC":4,"INTERNATIONAL":44,"CUSTOMER":1},{"DOCUMENT":8,"date":"15-10-09","INTERNATIONAL":40,"CUSTOMER":9},{"INTERNATIONAL":21,"date":"15-10-10","DOMESTIC":5,"DOCUMENT":3,"CUSTOMER":4},{"DOCUMENT":6,"date":"15-10-12","CUSTOMER":5,"INTERNATIONAL":23},{"DOCUMENT":7,"date":"15-10-13","CUSTOMER":8,"INTERNATIONAL":19,"DOMESTIC":1},{"DOCUMENT":7,"date":"15-10-14","DOMESTIC":2,"CUSTOMER":6,"INTERNATIONAL":26},{"DOMESTIC":1,"date":"15-10-15","DOCUMENT":7,"CUSTOMER":10,"INTERNATIONAL":25},{"DOCUMENT":5,"date":"15-10-16","CUSTOMER":1,"INTERNATIONAL":32},{"INTERNATIONAL":5,"date":"15-10-17"},{"DOCUMENT":2,"date":"15-10-18"},{"DOMESTIC":3,"date":"15-10-19","DOCUMENT":10,"CUSTOMER":5,"INTERNATIONAL":32},{"CUSTOMER":8,"date":"15-10-20","INTERNATIONAL":38,"DOMESTIC":2,"DOCUMENT":1},{"CUSTOMER":5,"date":"15-10-21","DOCUMENT":4,"INTERNATIONAL":17},{"CUSTOMER":7,"date":"15-10-22","INTERNATIONAL":28,"DOCUMENT":1,"REFUND":5},{"CUSTOMER":4,"date":"15-10-23","":2,"DOCUMENT":4,"INTERNATIONAL":25,"DOMESTIC":1},{"INTERNATIONAL":1,"date":"15-10-24"}]';
		//echo '<pre/>';print_r(json_decode($chartData_3));exit;
		
		$colors = array(
				'top_high'=>'#FCD202',
                "top_low"=>'#00cc0B',
		);
		
		$type=array(
				'top_high'=>'最高',
				'top_low'=>'最低',
		);
		
		if(!empty($res)){
			$tpl->assign('history',json_encode($res));
			$tpl->assign('colors',$colors);
			$tpl->assign('type',$type);
		}
	
		$tpl->display ( 'operate_chart.html' );
	}
}



