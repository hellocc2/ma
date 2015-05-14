<?php
namespace Module\Shop;

use helper\ResponseUtil as Rewrite;
/**
 * 快速支付
 * @Jiang Lin<jianglin@mialnoo.com>
 *
 */
class PaypalReturn extends \Lib\common\Application {
	
	function __construct() {
		\Helper\PaypalInteface::getPaypalData();
	}
}