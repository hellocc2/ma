<?php

namespace Module\member;

use Helper\RequestUtil as R;
//use Helper\ResponseUtil as rew;

/**
 * 会员注册
 *
 * @author wujianjun<wujianjun127@163.com>
 *         @sinc 2012-05-15
 * @param
 *        	int
 * @param
 *        	int
 */
class Login extends \Lib\common\Application {
	public function __construct() {
		$client=\Helper\CheckLogin::sso();
		$tpl = \Lib\common\Template::getSmarty ();
		$params_all = R::getParams ();
		
		if (!empty ( $client['uid'] )) {
			$bi_db = \Lib\common\Db::get_db('default'); 
			$db = \Lib\common\Db::get_db ( 'milanoo' );

			$uid = $client['uid'];
			if ($uid) {
				$sql = "SELECT competence_id FROM `milanoo_admin_user` WHERE uid = {$uid}";
				$competence_id = $db->getrow ( $sql );
				if (empty ( $competence_id['competence_id'] )) {
					$tpl->assign ( 'error', '用户名密码验证成功，但是你没有查看 MA 的权限请找相关人员开通' );
					$tpl->display ( 'member_login.htm' );
					exit ();
				}
				$sql = "SELECT * FROM `milanoo_admin_user` au, milanoo_admin_competence ac WHERE ac.id IN ({$competence_id['competence_id']}) AND uid = {$uid} AND FIND_IN_SET  ('1351', competence_menu)";
				$row = $db->getrow ( $sql );
				if (empty ( $row['realname'] )) {
					$tpl->assign ( 'error', '用户名密码验证成功，但是你没有查看 MA 的权限请找相关人员开通' );
					$tpl->display ( 'member_login.htm' );
					exit ();
				}
				$_SESSION [SESSION_PREFIX . "MemberId"] = $row['uid'];
				
				$sql = "SELECT oc.`competence_menu` FROM `oa_competence` oc,`user_rights` us WHERE us.uid = '{$row['uid']}' AND oc.name = us.rule";
				$_SESSION [SESSION_PREFIX . "rule"] = $bi_db -> getone($sql);
				
				if ($_SESSION [SESSION_PREFIX . "rule"] == false){
					$sql = "SELECT oc.`competence_menu` FROM `oa_competence` oc WHERE oc.`note` = '默认权限'";
					$_SESSION [SESSION_PREFIX . "rule"] = $bi_db -> getone($sql);
				}
				//var_dump($_SESSION [SESSION_PREFIX . "rule"]);exit;
				// setcookie('auth', '1', time() + 60 * 60 * 24 * 30);
				header ( "Location: index.php" );
			} else {
				$tpl->assign ( 'error', '登录失败，请使用米兰账号登陆' );
				$tpl->display ( 'member_login.htm' );
				exit ();
			}
		}
		$tpl->display ( 'member_login.htm' );
	}
}



