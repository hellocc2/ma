<?php

namespace Module\Statistics;

use Helper\RequestUtil as R;
use \Helper\Analyzer as Analyzer;

class Alarm extends \Lib\common\Application {
	public function __construct() {
		$tpl = \Lib\common\Template::getSmarty ();
		$list = new \model\Conversion ();
		$analyzer = new Analyzer ();
		$params = R::getParams ();
		$minuteDataAjax = $params->minuteDataAjax;
		if (! empty ( $minuteDataAjax )) {
			$minuteData = $analyzer->get_nowM_statistics ();
			// $minuteData =array_values($minuteData);
			echo json_encode ( $minuteData );
			die ();
		}
		if ($_POST) {
			// echo '<pre>';print_r($_GET);print_r($_POST);die;
		}
		/* 统计 */
		$minute_range = $params->minute_range;
		$minute_timepicker_start = $params->minute_timepicker_start;
		$minute_timepicker_end = $params->minute_timepicker_end;
		if (empty ( $minute_range )) {
			$yesterday = date ( "m\/d\/Y", strtotime ( '- 1 month' ) );
			$today = date ( "Y-m-d", time () );
			$tpl->assign ( 'yesterday', $yesterday );
			$tpl->assign ( 'today', $today );
			$minute_range = $today;
			$minute_timepicker_start = date ( "H\hi", strtotime ( "-1 minutes" ) );
			$minute_timepicker_end = date ( "H\hi", time () );
			$tpl->assign ( 'beToday', '1' );
		}
		$tpl->assign ( 'minute_timepicker_start', $minute_timepicker_start );
		$tpl->assign ( 'minute_timepicker_end', $minute_timepicker_end );
		$tpl->assign ( 'minute_range', $minute_range );
		
		$baseData = $list->getBase1mdataByRedis ();
		$baseData = array_values ( $baseData );
		//var_dump($baseData[0]);
		echo json_encode($baseData);die;
	}
}
