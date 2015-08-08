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
		$params_all = R::getParams ();
		
		
		$tpl->display ( 'operate_chart.html' );
	}
}



