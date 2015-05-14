<?php
namespace Module\Competence;
use Helper\RequestUtil as R;

class Operate extends \Lib\common\Application {
	public function __construct() {
		//$_SESSION['base_url'] = R::getUrl();
		$module_id = R::requestParam ( 'module_id' );
		
		$tpl = \Lib\common\Template::getSmarty ();
		$competence = new \model\Competence ();
		$menulist = $competence->getMenuList ();
		$tpl->assign ( 'all_id', $menulist ["all_id"] );
		$tpl->assign ( 'pid', $menulist ["pid"] );
		$tpl->assign ( 'name', $menulist ["name"] );
		$tpl->assign ( 'competence', $menulist ["competence"] );
		$tpl->assign ( 'exit_id', $menulist ["exit_id"] );
		$tpl->assign ( 'module_id', $module_id );
		$act = R::getParams ( 'act' );
		switch ($act) {
			case 'addpost' :
				$competence_name = htmlspecialchars ( R::getParams ( 'competence_name' ) );
				$competence_note = htmlspecialchars ( R::getParams ( 'competence_note' ) );
				$competence_menu = R::getParams ( 'competence_menu' );
				$competence_details = R::getParams ( 'competence_details' );
				if (! $competence_name)
					\Helper\ArrayStr::alert_forward ( '权限组名称不能为空', '', '1', '0' );
				$result = $competence->addCompetence ( $competence_name, $competence_menu, $competence_note, $competence_details );
				if ($result)
					\Helper\ArrayStr::alert_forward ( '添加成功', '?module_id=' . $module_id, 1, 0 );
				else
					\Helper\ArrayStr::alert_forward ( '添加失败', '', '1', '0' );
				break;
			default :
				$tpl->assign ( 'action_name', "add" );
				break;
		}
		$tpl->display ( 'competence_add.htm' );
	}
}