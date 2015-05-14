<?php
namespace Module\Competence;
use Helper\RequestUtil as R;
use \Helper\Promotion as Promotion;
use Helper\CheckLogin as CheckLogin;
use Helper\ServiceData as ServiceData;

class Index extends \Lib\common\Application {
	public function __construct() {
		$_SESSION ['base_url'] = R::getUrl ();
		$module_id = R::requestParam ( 'module_id' );
		
		$tpl = \Lib\common\Template::getSmarty ();
		$competence = new \model\Competence ();
		$operate = R::requestParam ( 'operate' );
		$act = R::requestParam ( 'act' );
		if($operate == "edit" && !$act){
				$id = R::requestParam ( 'id' );
				$edit_array = $competence->getCompetence ( $id );
				/*子权限处理*/
				$edit_details = $edit_array ["competence_details"];
				if ($edit_details) {
					$edit_details = explode ( ",", $edit_details );
					for($i = 0; $i < sizeof ( $edit_details ); $i ++) {
						if ($edit_details [$i]) {
							$edit_details_array = array ();
							$edit_details_array = explode ( "||", $edit_details [$i] );
							$edit_details_ip [$edit_details_array [0]] = explode ( "|", $edit_details_array [1] );
						}
					}
				}
				
				/*栏目权限处理*/
				$edit_menu_ip = explode ( ",", $edit_array ["competence_menu"] );
				$menulist = $competence->getMenuList ();
				$tpl->assign ( 'all_id', $menulist ["all_id"] );
				$tpl->assign ( 'pid', $menulist ["pid"] );
				$tpl->assign ( 'name', $menulist ["name"] );
				$tpl->assign ( 'competence', $menulist ["competence"] );
				$tpl->assign ( 'exit_id', $menulist ["exit_id"] );
				$tpl->assign ( 'edit_array', $edit_array );
				$tpl->assign ( 'edit_details_ip', $edit_details_ip );
				$tpl->assign ( 'edit_menu_ip', $edit_menu_ip );
				$tpl->assign ( 'action_name', "edit" );
				$tpl->assign ( 'competence_id', $id );
				$tpl->assign ( 'module_id', $module_id );
				$tpl->display ( 'competence_add.htm' );
				exit();
		}
		
		//exit ();
		switch ($act) {
			case 'editpost' :
				$id = R::getParams ( 'id' );
				$competence_name = htmlspecialchars ( R::getParams ( 'competence_name' ));
				$competence_note = htmlspecialchars ( R::getParams ( 'competence_note' ) );
				$competence_menu = R::getParams ( 'competence_menu' );
				$competence_details = R::getParams ( 'competence_details' );
				$result = $competence->editCompetence ( $competence_name, $competence_menu, $competence_note, $competence_details, $id );
				header("Location: index.php?module=competence&action=Index");
				// if (! $competence_name)
					// \Helper\ArrayStr::alert_forward ( '权限组名称不能为空', '', '1', '0' );
				// if ($result)
					// \Helper\ArrayStr::alert_forward ( '编辑成功', '?module_id=' . $module_id, 1, 0 );
				// else
					// \Helper\ArrayStr::alert_forward ( '编辑失败', '', '1', '0' );
				// break;
				break;
			case 'del' :
				$id = R::requestParam ( 'id' );
				$result = $competence->delCompetence ($id);
				header("Location: index.php?module=competence&action=Index");
				break;
			default :
				$competencelist = $competence->getCompetenceList ();
				$tpl->assign ( 'competence_all', $competencelist );
				$tpl->assign ( 'module_id', $module_id );
				$tpl->display ( 'competence.htm' );
				break;
		}
	
	}
}