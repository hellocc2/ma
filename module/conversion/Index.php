<?php
namespace Module\Conversion;
use Helper\RequestUtil as R;
use Helper\CheckLogin as CheckLogin;

class Index extends \Lib\common\Application {
	public function __construct() {
		CheckLogin::getMemberID();
		$time = R::requestParam ( 'time' );
		if($_POST){
			//echo '<pre>';print_r($_POST);
		}
		$tpl = \Lib\common\Template::getSmarty ();
		$list = new \model\Conversion ();
		$conversion = $list->getConversionRate ($time);
		// $payrate = $list->getPayRate ($time);
		// var_dump($conversion);exit;
		$tpl->assign ( 'conversion', $conversion );
		$tpl->assign ( 'lang', !empty($_SESSION["ma_lang"])?$_SESSION["ma_lang"]:'' );
		$tpl->assign ( 'websiteId', !empty($_SESSION["ma_websiteId"])?$_SESSION["ma_websiteId"]:1 );
		// echo $_SESSION["ma_websiteId"];die;
		$tpl->assign ( 'start_time', $_SESSION["ma_starttime"] );
		$tpl->assign ( 'end_time', $_SESSION["ma_endtime"] );
		$tpl->display ( 'conversion.htm' );
	}
}