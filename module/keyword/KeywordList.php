<?php

namespace Module\Keyword;

use Helper\RequestUtil as R;
use \Helper\Analyzer as Analyzer;
use \Helper\Promotion as Promotion;
use Helper\CheckLogin as CheckLogin;

class KeywordList extends \Lib\common\Application {
	public function __construct() {
		CheckLogin::getMemberID();
		$tpl = \Lib\common\Template::getSmarty ();
		$analyzer = new Analyzer ();
		$list = new \model\Conversion ();
		
		$params = R::getParams ();
		$db = \Lib\common\Db::get_db ( 'default' );
		
		$where = " and 1=1";
		$WebsiteId = $_SESSION ['ma_websiteId'];
		$tpl->assign ( 'WebsiteId', $WebsiteId );
		
		if( $WebsiteId == 666 ){
			$WebsiteId = 'v.`WebsiteId`';
		}

		$file = '../config/Language.php';
		
		if ($_SESSION ["ma_lang"] != 'all') {
			$lang = $_SESSION ["ma_lang"];
		}
		
		$range = $params->range;
		
		if (!empty ( $lang )) {
			$where .= " and v.`lang` ='" . $lang . "'";
			$tpl->assign ( 'isLang', $lang );
		}
		
		if (empty ( $_SESSION["ma_starttime"] ) or empty ( $_SESSION["ma_endtime"] )) {
			
			// 得到系统的年月
			$tmp_date = date ( "Ym" );
			// 切割出年份
			$tmp_year = substr ( $tmp_date, 0, 4 );
			// 切割出月份
			$tmp_mon = substr ( $tmp_date, 4, 2 );
			$tmp_nextmonth = mktime ( 0, 0, 0, $tmp_mon + 1, 1, $tmp_year );
			// 上一个月
			$tmp_forwardmonth = mktime ( 0, 0, 0, $tmp_mon - 1, 1, $tmp_year );
			
			$forwardmonth = date ( "Y/m/d", $tmp_forwardmonth );
			$yesterday = date ( "Y/m/d", strtotime ( "-1 day" ) );
			
			if (! empty ( $_SESSION ['ma_starttime'] ) and ! empty ( $_SESSION ['ma_endtime'] )) {
				$forwardmonth = str_replace ( "-", "/", $_SESSION ['ma_starttime'] );
				$yesterday = str_replace ( "-", "/", $_SESSION ['ma_endtime'] );
			}
			
			$range = $forwardmonth . " - " . $yesterday;
			// $range = $_SESSION['ma_starttime'] . " - " . $_SESSION['ma_endtime'];
		}

		$s_range = strtotime ( $_SESSION ['ma_starttime'] );
		$e_range = strtotime ( $_SESSION ['ma_endtime'] );

		$ss_range = date ( "Y-m-d", $s_range );
		$_SESSION ['ma_starttime'] = str_replace ( "/", "-", $ss_range );
		
		$ee_range = date ( "Y-m-d", $e_range );
		$_SESSION ['ma_endtime'] = str_replace ( "/", "-", $ee_range );
		
		$tpl->assign ( 'ss_range', $ss_range );
		$tpl->assign ( 'ee_range', $ee_range );
		$tpl->assign ( 's_range', $s_range );
		$tpl->assign ( 'e_range', $e_range );
		$tpl->assign ( 'range', $range );
		
		$tpl->assign ( 'start_time', $_SESSION["ma_starttime"] );
		$tpl->assign ( 'end_time', $_SESSION["ma_endtime"] );
		
		for($d = $s_range; $d <= $e_range; $d = $d + 86400) {
			$day_array [date ( "Y-m-d", $d )] = array ();
		}
	
		if (! empty ( $s_range )) {
			$s_range = date ( "Y-m-d 00:00:00", $s_range );
			$where .= " and `time` >='" . $s_range . "'";
		}
		
		if (! empty ( $e_range )) {
			$e_range = date ( "Y-m-d 00:00:00", $e_range );
			$where .= " and `time` <='" . $e_range . "'";
		}
		
		$sql = "SELECT v.`time`,SUM(v.`pv`) AS pv, SUM(v.`ip`) AS ip, SUM(v.`uv`) AS uv, SUM(v.`newUv`) AS newUv FROM `ma_keywords_visits` v LEFT JOIN `ma_keywords_name` k ON v.`kid`=k.`id` WHERE v.`WebsiteId` = " . $WebsiteId . $where . " GROUP BY  v.`time` ORDER BY v.`time` DESC";
		//echo $sql;
		$rs = $db->SelectLimit ( $sql );

		$row = array ();
		if ($rs->RecordCount ()) {
			while ( ! $rs->EOF ) {
				$row = $rs->fields;
				
				if (empty ( $row ['ip'] )) {
					$row ['ip'] = 0;
				}
				if (empty ( $row ['pv'] )) {
					$row ['pv'] = 0;
				}
				if (empty ( $row ['uv'] )) {
					$row ['uv'] = 0;
				}
				if (empty ( $row ['newUv'] )) {
					$row ['newUv'] = 0;
				}					
				$row ["time"] = date ( "Y-m-d", strtotime ( $row ["time"] ) );
				$day_array [date ( "Y-m-d", strtotime ( $row ["time"] ) )] = $row;
				$rs->MoveNext ();
			}
		}
		
		foreach ( $day_array as $key => $value ) {
			if (empty ( $value )) {
				$data_array [] = $day_array [$key] = array (
						'ip' => 0,
						'pv' => 0,
						'uv' => 0,
						'newUv' => 0,
						'time' => $key 
				);
			} else {
				$data_array [] = $value;
			}
		}
		//echo '<pre/>';print_r($data_array);
		$tpl->assign ( 'data_array', json_encode ( $data_array ) );
		$tpl->assign ( 'lang', ! empty ( $_SESSION ["ma_lang"] ) ? $_SESSION ["ma_lang"] : '' );
		$tpl->assign ( 'websiteId', ! empty ( $_SESSION ["ma_websiteId"] ) ? $_SESSION ["ma_websiteId"] : 1 );
		$tpl->display ( 'keywordList.htm' );
	}
}
