<?php
namespace Module\Index;
use Helper\RequestUtil as R;

class Left extends \Lib\common\Application {
	public function __construct() {
		$competence = new \model\Competence ();
		$menulist = $competence->getMenuList ();
		$tpl = \Lib\common\Template::getSmarty ();
		$rule = explode(",", $_SESSION [SESSION_PREFIX . "rule"]);
		$tpl->assign ( 'rule', $rule );
		$tpl->assign ( 'all_id', $menulist ["all_id"] );
		$tpl->assign ( 'pid', $menulist ["pid"] );
		$tpl->assign ( 'name', $menulist ["name"] );
		$tpl->assign ( 'module', $menulist ["module"] );
		$tpl->assign ( 'action', $menulist ["action"] );
		$tpl->assign ( 'competence', $menulist ["competence"] );
		$tpl->assign ( 'exit_id', $menulist ["exit_id"] );		
		$tpl->display ( 'left.htm' );
	}
}