<?php
namespace Module\logout;

class Sso extends \Lib\common\Application {
	public function __construct() {
		$client=\Helper\CheckLogin::sso('logout');
	}
}
?>
