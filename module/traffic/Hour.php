<?php
namespace Module\Statistics;
use Helper\RequestUtil as R;
use \Helper\Analyzer as Analyzer;

class Hour extends \Lib\common\Application {
	public function __construct() {
		$tpl 				= \Lib\common\Template::getSmarty ();
		$list 				= new \model\Conversion ();
		$analyzer 	= new Analyzer();		
		$params		= R::getParams();
		$minuteDataAjax=$params->minuteDataAjax;
		if(!empty($minuteDataAjax)){
			$minuteData=$analyzer->get_nowM_statistics();
			//$minuteData =array_values($minuteData);
			echo json_encode($minuteData);
			die;
		}
		if($_POST){
			//echo '<pre>';print_r($_GET);print_r($_POST);die;
		}
		/* 统计 */
		 $minute_range=$params->minute_range;
		 $minute_timepicker_start=$params->minute_timepicker_start;
		 $minute_timepicker_end=$params->minute_timepicker_end;
		if (empty($minute_range)) {
			$yesterday = date("m\/d\/Y", strtotime('- 1 month'));
			$today = date("Y-m-d", time());
			$tpl->assign ( 'yesterday', $yesterday );
			$tpl->assign ( 'today', $today );
			$minute_range=$today;
			$minute_timepicker_start = date("H\hi" , strtotime("-15 minutes"));
			$minute_timepicker_end = date("H\hi", time());
			$tpl->assign ( 'beToday', '1' );
		}
		$tpl->assign ( 'minute_timepicker_start', $minute_timepicker_start );
		$tpl->assign ( 'minute_timepicker_end', $minute_timepicker_end );
		$tpl->assign ( 'minute_range', $minute_range );
		
		$baseData = $list->getBaseDataByRedis ();
		$baseData =array_values($baseData);
		//echo json_encode($baseData);die;
		$tpl->assign ( 'baseData', json_encode($baseData));
		/* 统计 end*/
		//前端js数据准备-日期范围分析
		/* if (!empty($_POST['range'])) {
			$range = $_POST['range'];	
			$tpl->assign ( 'range', $_POST['range'] );
			//echo $_POST['range'];die;
			$tpl->assign ( 'dayhour', $_POST['dayhour'] );
		} else {
			$yesterday = date("m\/d\/Y", strtotime('- 1 month'));
			$today = date("Y-m-d", time());
			$range = $yesterday . " - " . $today;
			//$range = $today;
			//$tpl->assign ( 'yesterday', $yesterday );
			//$tpl->assign ( 'today', $today );
			$tpl->assign ( 'dayhour', '1');
			$tpl->assign ( 'range', $range );
		} */
		
		//同期对比
		$l_range			=$params->l_range;
		$r_range			=$params->r_range;
		$start_time		=$params->timepicker_start;
		$end_time		=$params->timepicker_end;
		if (empty($l_range)) {
			$l_range 		= date("Y-m-d", strtotime("-1 day"));
			$r_range		= date("Y-m-d", time());		
			$start_time 	= date("H\hi" , strtotime("-15 minutes"));
			$end_time 	= date("H\hi", time());
		}
		$tpl->assign ( 'l_range', $l_range );
		$tpl->assign ( 'r_range', $r_range );
		$tpl->assign ( 'start_time', $start_time );
		$tpl->assign ( 'end_time', $end_time );
		//echo $start_time;die;
		//$compare = $list->getCompareByDb($l_range,$r_range);	
		
		$compare = $list->getCompareByRedis($l_range,$r_range,$start_time,$end_time,'m');
		//$compare =array_values($compare);
		$tpl->assign ( 'compare', json_encode($compare));
		//同期对比 end 
		
		$tpl->assign ( 'lang', !empty($_SESSION["ma_lang"])?$_SESSION["ma_lang"]:'' );
		$tpl->assign ( 'websiteId', !empty($_SESSION["ma_websiteId"])?$_SESSION["ma_websiteId"]:1 );
		$tpl->display ( 'statistics_hour.htm' );
	}
}
