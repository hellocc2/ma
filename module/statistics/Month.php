<?php
namespace Module\Statistics;
use Helper\RequestUtil as R;
use \Helper\Analyzer as Analyzer;
use Helper\CheckLogin as CheckLogin;

class Month extends \Lib\common\Application {
	public function __construct() {
		CheckLogin::getMemberID();
		$tpl = \Lib\common\Template::getSmarty ();
		$analyzer = new Analyzer();
		$list = new \model\Conversion ();
		if($_POST){
			//echo '<pre>';print_r($_GET);print_r($_POST);die;
		}
		$params=R::getParams();
		//echo '<pre>';print_r($params);die;
		//重新改写优化

		/*本月分析（天）*/
		$range=$params->range;
		if (empty($range)) {
			$yesterday = date('Y/m/d', strtotime('- 1 month'));
			$today = date('Y/m/d', time());
			$range = $yesterday . " - " . $today;
		}
		$tpl->assign ( 'range', $range );
		if (!empty($range)) {
			$time = explode(" - ", $range);
			if (count($time) == 1) {
				$start_time 	= strtotime($time['0']);
				$end_time 	= $start_time+23*3600;
				$date 			= $analyzer -> getHourStatisticsByRedis($start_time, $end_time);
				$is_hous_range_statistics = 1;
			} else {
				$start_time 	= strtotime($time['0']);
				$end_time 	= strtotime($time['1']);
				$date 			= $list -> getDayStatisticsByDb($start_time, $end_time);
				$end_time 	= $end_time+23*3600;
				$hdate 		= $analyzer -> getHourStatisticsByRedis($start_time, $end_time);
			}
		} else {			
			$start_time 	= strtotime("-1 month");
			$end_time 	= time();
			$date 			= $list -> getDayStatisticsByDb($start_time, $end_time);
			
			$end_time 	= strtotime('today')+23*3600;
			$hdate			= $analyzer -> getHourStatisticsByRedis($start_time, $end_time); 
		}  
		//echo '123<pre>';print_r($hdate);die;
		$date=array_values($date);
		$tpl->assign ( 'date', $date);
		$tpl->assign ( 'dayHour', json_encode($date));
		$tpl->assign ( 'is_hous_range_statistics', $is_hous_range_statistics);
		/*本月分析（天） end*/
		
		/* 访问量分布 (数据依赖本月分析) */
		//计算各数据的总量
		$pv_sum=$ip_sum=$uv_sum=$newUv_sum=0;
		foreach($date as $time=>$value){			
			$pv_sum+=$value['pv'];
			$ip_sum+=$value['ip'];
			$uv_sum+=$value['uv'];
			$newUv_sum+=$value['newUv'];
		}
		$tpl->assign ( 'pv_sum', $pv_sum );
		$tpl->assign ( 'ip_sum', $ip_sum );
		$tpl->assign ( 'uv_sum', $uv_sum );
		$tpl->assign ( 'newUv_sum', $newUv_sum );
		if(!empty($hdate)){
			foreach($hdate as $time=>$value){
				$hour=date('H:i',$time);
				$new_hdate[$hour][$time]=$value;
			}
			foreach($new_hdate as $hour=>$hour_values){	
				$days=count($hour_values);
				$pv_sum_hour=$pv_avg_hour=$ip_sum_hour=$ip_avg_hour=0;
				$uv_sum_hour=$uv_avg_hour=$newUv_sum_hour=$newUv_avg_hour=0;
				//echo '123<pre>';print_r($hour_values);die;
				foreach($hour_values as $key=>$value){
					$pv_sum_hour			+=$value['pv'];
					$pv_avg_hour			=round($pv_sum_hour/$days);
					$ip_sum_hour			+=$value['ip'];
					$ip_avg_hour			=round($ip_sum_hour/$days);
					$uv_sum_hour			+=$value['uv'];
					$uv_avg_hour			=round($uv_sum_hour/$days);
					$newUv_sum_hour	+=$value['newUv'];
					$newUv_avg_hour	=round($newUv_sum_hour/$days); 
					$new_hdate3[$hour]['pv_avg_hour']			=$pv_avg_hour;
					$new_hdate3[$hour]['ip_avg_hour']			=$ip_avg_hour;
					$new_hdate3[$hour]['uv_avg_hour']			=$uv_avg_hour;
					$new_hdate3[$hour]['newUv_avg_hour']	=$newUv_avg_hour;
					$new_hdate3[$hour]['hour']						=$hour;
					$new_hdate3[$hour]['ratio']						=!empty($pv_sum_hour)?round(($pv_sum_hour/$pv_sum)*100).' %':0;
				}
			}
			$tpl->assign ( 'hdate', $new_hdate3 );
		}
		/* 访问量分布 end*/
		
		/* 同期对比 */
		$l_range=$params->l_range;
		$r_range=$params->r_range;
		if (empty($l_range) || empty($r_range)) {
			$l_yesterday 	= date('Y/m/d', strtotime("- 3 month"));
			$l_today 	    = date('Y/m/d', strtotime("- 2 month"));
			$r_yesterday 	= date('Y/m/d', strtotime("- 1 month"));
			$r_today 			= date('Y/m/d', time());
			$l_range = $l_yesterday . " - " . $l_today;
			$r_range = $r_yesterday . " - " . $r_today;
		}
		$tpl->assign ( 'l_range', $l_range );
		$tpl->assign ( 'r_range', $r_range );
		
		$compare = $list->getCompareByDb($l_range,$r_range);	
		//echo '123<pre>';print_r($compare);die;
		$tpl->assign ( 'compare', json_encode($compare));
		/* 同期对比  end*/
		
		$tpl->assign ( 'lang', !empty($_SESSION["ma_lang"])?$_SESSION["ma_lang"]:'' );
		$tpl->assign ( 'websiteId', !empty($_SESSION["ma_websiteId"])?$_SESSION["ma_websiteId"]:1 );
		$tpl->display ( 'statistics_month.htm' );
	}
}
