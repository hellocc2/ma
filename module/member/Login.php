<?php
namespace Module\member;
use Helper\RequestUtil as R;

/**
 * 会员登录
 * @author jiangcai
 */

class Login extends \Lib\common\Application {
	public function __construct() {
		$tpl = \Lib\common\Template::getSmarty ();
		$username = R::getParams ('username');
		$password = R::getParams ('password');
		
		if (!empty ($username)) {

			$db = \Lib\common\Db::get_db ();
			$password=md5($password);

			if ($username&&$password) {
 				$sql = "SELECT * FROM `rmb_money_member` m WHERE username='".$username."' AND password='".$password."'";
 				$row = $db->getrow ( $sql );
 				if (empty($row)){
 					$tpl->assign ( 'error', '用户名密码验证成功，但是你没有查看 MA 的权限请找相关人员开通' );
 					$tpl->display ( 'member_login.htm' );
					exit ();
 				}
				
				$_SESSION [SESSION_PREFIX . "MemberId"] = $row['uid'];
				header ( "Location: index.php" );
				exit;
			} else {
				$tpl->assign ( 'error', '登录失败，请使用米兰账号登陆' );
				$tpl->display ( 'member_login.htm' );
				exit ();
			}
		}
		$tpl->display ( 'member_login.html' );
	}
}



