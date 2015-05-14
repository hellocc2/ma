<?php
namespace Module\Statistics;
use Helper\RequestUtil as R;
use \Helper\Analyzer as Analyzer;
use Helper\CheckLogin as CheckLogin;


class Day extends \Lib\common\Application {
	public function __construct() {
		
		$tpl = \Lib\common\Template::getSmarty ();
		$analyzer = new Analyzer();
		$list = new \model\Conversion ();
		$params=R::getParams();
		
		if (isset($_GET['chart']) and $_GET['chart'] == 'deng') {
			if (isset($_GET['chart'])) {
				$callback=$_GET['callback'];
			}
		} elseif(isset($_GET['chart']) and $_GET['chart'] == 'api') {
			if (isset($_GET['range'])) {
				if (isset($_GET['chart'])) {
					$callback=$_GET['callback'];
				}
				$type = substr_count($_GET['range'], '-');
				if ($type == 1) {
					$time = explode("-", $_GET['range']);
					$start_time = $time['0'];
					$end_time = $time['1'];
					$timepicker_start = 0;
					$timepicker_end = 23;
					//$start_time = $start_time . " " . $timepicker_start . ":00";
					//$end_time = $end_time . " " . $timepicker_end . ":00";
				} else {
					$start_time = date("m\/d\/Y", strtotime("-1 day"));
					$end_time = date("m\/d\/Y", time());
				}
			
				for ($i=strtotime($start_time); $i <= strtotime($end_time); $i=$i+1*24*60*60) {
					
					if( $i == strtotime('today') ) {
						$date = $list->getDayBaseDataByRedis ( date("Y/m/d", $i) );
						//var_dump($date);
						foreach($date as $time=>$value) {
							$pv_sum+=$value['pv'];
							$ip_sum+=$value['ip'];
							$uv_sum+=$value['uv'];
							$newUv_sum+=$value['newUv'];
						}
						$sum[$i] = array( 'pv'=>$pv_sum,'ip'=>$ip_sum,'uv'=>$uv_sum,'newUv'=>$newUv_sum );
						unset($pv_sum);
						unset($ip_sum);
						unset($uv_sum);
						unset($newUv_sum);
					} else {
						$date = $list->getDayStatisticsByDb ( $i, $i);
						foreach($date as $time=>$value) {
							$pv_sum+=$value['pv'];
							$ip_sum+=$value['ip'];
							$uv_sum+=$value['uv'];
							$newUv_sum+=$value['newUv'];
						}
						$sum[$i] = array( 'pv'=>$pv_sum,'ip'=>$ip_sum,'uv'=>$uv_sum,'newUv'=>$newUv_sum );
						unset($pv_sum);
						unset($ip_sum);
						unset($uv_sum);
						unset($newUv_sum);
					}
				}
				$json =  json_encode($sum);
				if (isset($_GET['callback'])) {
					print_r("$callback($json);");
				} else {
					print_r($json);
				}
				exit;
			}
		} else {
			CheckLogin::getMemberID();
		}
		

		if($_POST){
			//echo '<pre>';print_r($_GET);print_r($_POST);die;
		}
		/* 当日数据 */
		$day_range=$params->day_range;
		if (empty($day_range)) {
			$day_range = date("Y/m/d", time());
		}
		$tpl->assign ( 'day_range', $day_range );
		
		if (isset($_GET['chart']) and $_GET['chart'] == 'deng') {
			$date = $list->getDayBaseDataByRedis ($day_range);
			foreach( $date as $time=>$value) {
				$pv_sum+=$value['pv'];
				$ip_sum+=$value['ip'];
				$uv_sum+=$value['uv'];
				$newUv_sum+=$value['newUv'];
			}
		    $sum = array( 'pv'=>$pv_sum,'ip'=>$ip_sum,'uv'=>$uv_sum,'newUv'=>$newUv_sum );
			unset($pv_sum);
			unset($ip_sum);
			unset($uv_sum);
			unset($newUv_sum);
			$json =  json_encode($sum);
			print_r("$callback($json);");
			exit;
		}
		
		$baseData = $list->getDayBaseDataByRedis ($day_range);
		$baseData =array_values($baseData);
		$tpl->assign ( 'baseData', json_encode($baseData)); 
		/* 当日数据 end */
		//前端js数据准备-第二张图
		/* if (!empty($_POST['range'])) {
			$range = $_POST['range'];	
			$tpl->assign ( 'range', $_POST['range'] );
		} else {
			$yesterday = date("m\/d\/Y", strtotime('- 1 month'));
			$today = date("Y-m-d", time());
			$range = $yesterday . " - " . $today;
			$tpl->assign ( 'range', $range );
		} */
		
		/* 访问量分布 */
		$date = $analyzer -> get_date_range_statistics($day_range, $day_range);
		$start_time = $day_range . "00:00";
		$end_time = $day_range . "23:00";
		$hdate = $analyzer -> get_hour_range_statistics($start_time, $end_time);
		
		$pv_sum=$ip_sum=$uv_sum=$newUv_sum=0;
		//echo '<pre>';print_r($date);die;
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
 
		foreach($hdate as $time=>$value){		
			$hour=date('H:i',$time);
			$new_hdate[$hour][$time]=$value;
		}
		$pv_sum_hour=$pv_avg_hour=$ip_sum_hour=$ip_avg_hour=array();
		$uv_sum_hour=$uv_avg_hour=$newUv_sum_hour=$newUv_avg_hour=array();
		foreach($new_hdate as $hour=>$hour_values){	
			$days=count($hour_values);
			foreach($hour_values as $key=>$value){					
				$pv_sum_hour[$hour]+=$value['pv'];
				$pv_avg_hour[$hour]=round($pv_sum_hour[$hour]/$days);
				$ip_sum_hour[$hour]+=$value['ip'];
				$ip_avg_hour[$hour]=round($ip_sum_hour[$hour]/$days);
				$uv_sum_hour[$hour]+=$value['uv'];
				$uv_avg_hour[$hour]=round($uv_sum_hour[$hour]/$days);
				$newUv_sum_hour[$hour]+=$value['newUv'];
				$newUv_avg_hour[$hour]=round($newUv_sum_hour[$hour]/$days);
				//echo $pv_sum;die;
				$new_hdate3[$hour]['pv_avg_hour']			=$pv_avg_hour[$hour];
				$new_hdate3[$hour]['ip_avg_hour']			=$ip_avg_hour[$hour];
				$new_hdate3[$hour]['uv_avg_hour']			=$uv_avg_hour[$hour];
				$new_hdate3[$hour]['newUv_avg_hour']	=$newUv_avg_hour[$hour];
				$new_hdate3[$hour]['hour']						=$hour;
				$new_hdate3[$hour]['ratio']						=!empty($pv_sum_hour[$hour])?round($pv_sum_hour[$hour]/$pv_sum*100).' %':0;
			}
		}
		$tpl->assign ( 'date', $date );
		$tpl->assign ( 'hdate', $new_hdate3 );
		/* 访问量分布 end*/
		
		/* 同期对比 */
		$l_range	=$params->l_range;
		$r_range	=$params->r_range;
		if (empty($l_range) || empty($r_range)) {
			$l_range 	= date("m\/d\/Y", strtotime("-1 day"));
			$r_range 	= date("m\/d\/Y", time());
		}
		$tpl->assign ( 'l_range', $l_range );
		$tpl->assign ( 'r_range', $r_range );
		$compare = $list->getCompareByRedis($l_range,$r_range,'','','h');		
		//echo '<pre>';print_r($compare);die;
		//$compare =array_values($compare);
		$tpl->assign ( 'compare', json_encode($compare));
		/* 同期对比 end*/
		
		
		$tpl->assign ( 'lang', !empty($_SESSION["ma_lang"])?$_SESSION["ma_lang"]:'' );
		$tpl->assign ( 'websiteId', !empty($_SESSION["ma_websiteId"])?$_SESSION["ma_websiteId"]:1 );
		$tpl->display ( 'statistics_day.htm' );
	}
}
