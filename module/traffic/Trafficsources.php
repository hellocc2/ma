<?php

namespace Module\Traffic;

use Helper\RequestUtil as R;
use \Helper\Analyzer as Analyzer;
use \Helper\Promotion as Promotion;
use Helper\CheckLogin as CheckLogin;

class Trafficsources extends \Lib\common\Application {
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
		
		if ($_GET['otc_cid']){
			$_COOKIE['categories_id'] = $_GET['otc_cid'];
		}
		
		$file = '../config/Language.php';
		
		// $acceptLang = \config\Language::$acceptLang;
		
		if ($_SESSION ["ma_lang"] != 'all') {
			$lang = $_SESSION ["ma_lang"];
		}
		
		$range = $params->range;
		
		if (!isset($_COOKIE['addition'])) {
			$_COOKIE['addition'] = 1;	
		}
		
		$a = $_COOKIE['promotion_name_row'];
		$b = $_COOKIE['category_chart'];
		$c = $_COOKIE['categories_id'];
		$d = $_COOKIE['addition'];
		$e = $_COOKIE['search_type'];
		
		$tpl->assign ( 'a', $a );
		$tpl->assign ( 'b', $b );
		$tpl->assign ( 'c', $c );
		$tpl->assign ( 'd', $d );
		$tpl->assign ( 'e', $e );
		
		if (! empty ( $lang )) {
			$where .= " and v.`lang` >='" . $lang . "'";
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
		// var_dump($day_array);exit;
		if (! empty ( $s_range )) {
			$s_range = date ( "Y-m-d 00:00:00", $s_range );
			$where .= " and `time` >='" . $s_range . "'";
		}
		
		if (! empty ( $e_range )) {
			$e_range = date ( "Y-m-d 00:00:00", $e_range );
			$where .= " and `time` <='" . $e_range . "'";
		}
		
		$sql = "SELECT * FROM `milanoo_promotion_category` WHERE `name` = 'SEM' AND websiteid = 1";
		$SEM_id = $db->getone ( $sql );
		
		$class_all = new Promotion ( 'promotion_category', 0, 'ASC', '', 0, 1 );
		$class = $class_all->class_option ( '', 0, $b, '' );
		$pid = $class_all -> idALL($SEM_id);
		$where_roi .= " and category in (" . $SEM_id . $pid . ")";
		
		$sql_roi = "SELECT * FROM (SELECT p.`PromotionName`, IFNULL((SUM(`payamount`)-SUM(`adcostUsd`))/SUM(`adcostUsd`),0) AS ROI, IFNULL(SUM(`payamount`), 0) AS payamount, ROUND(SUM(`adcostUsd`),2) AS adcostUsd, SUM(`payorder`) AS payorder FROM `milanoo_promotionurl` p, `ma_promotion_visits` v WHERE v.`WebsiteId` = v.`WebsiteId` AND p.id = v.`promotionid` ".$where_roi." AND `WebsiteId` = " . $WebsiteId . " AND 1 = 1 " . $where . " GROUP BY PromotionName ) a WHERE ROI <-0.3 ORDER BY ROI ASC,payamount ASC,adcostUsd DESC";
		$rs_roi = $db->SelectLimit ( $sql_roi );
		$row_roi = array ();
		if ($rs_roi->RecordCount ()) {
			while ( ! $rs_roi->EOF ) {
				$row_roi = $rs_roi->fields;
				$low_roi[] = $row_roi['PromotionName'];
				$rs_roi->MoveNext ();
			}
		}
		
		$nu_roi = count($low_roi);
		$low_roi_srt = implode(',', $low_roi);
		
		$tpl->assign ( 'nu_roi', $nu_roi );
		$tpl->assign ( 'low_roi', $low_roi );
		
		$sql = "SELECT v.`time` ,SUM(`payorder`)/SUM(v.`uv`) as purate,SUM(v.`ip`) as ip ,SUM(v.`pv`) as pv ,SUM(v.`uv`) as uv ,SUM(v.`newUv`) as newUv ,SUM(`payorder`) as payorder,SUM(`payamount`) as payamount,SUM(`regmember`) as regmember ,SUM(`subscribers`) as subscribers FROM `milanoo_promotionurl` p ,`ma_promotion_visits` v WHERE v.`WebsiteId` = " . $WebsiteId . " and p.id = v.`promotionid` " . $where . " GROUP BY  v.`time` ORDER BY v.`time`";
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
				if (empty ( $row ['payorder'] )) {
					$row ['payorder'] = 0;
				}
				if (empty ( $row ['regmember'] )) {
					$row ['regmember'] = 0;
				}
				if (empty ( $row ['subscribers'] )) {
					$row ['subscribers'] = 0;
				}
				if (empty ( $row ['payamount'] )) {
					$row ['payamount'] = 0;
				}
				if (empty ( $row ['purate'] )) {
					$row ['purate'] = 0;
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
						'payorder' => 0,
						'payamount' => 0,
						'purate' => 0,
						'regmember' => 0,
						'subscribers' => 0,
						'time' => $key 
				);
			} else {
				$data_array [] = $value;
			}
		}
 
		$tpl->assign ( 'SEM_id', $SEM_id );
		$tpl->assign ( 'data_array', json_encode ( $data_array ) );
		$tpl->assign ( 'class', $class );
		$tpl->assign ( 'lang', ! empty ( $_SESSION ["ma_lang"] ) ? $_SESSION ["ma_lang"] : '' );
		$tpl->assign ( 'websiteId', ! empty ( $_SESSION ["ma_websiteId"] ) ? $_SESSION ["ma_websiteId"] : 1 );
		$tpl->display ( 'trafficsources.htm' );
	}
}
