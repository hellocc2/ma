<?php
namespace Module\Index;
use Helper\RequestUtil as R;
use Helper\CheckLogin as CheckLogin;

class Index extends \Lib\common\Application {
	public function __construct() {
		$tpl = \Lib\common\Template::getSmarty ();
		CheckLogin::getMemberID();
		//echo $_SERVER["HTTP_REFERER"];
		//header("Location:?module=member&action=login");
		$tpl->display ( 'index.htm' );
	}
}