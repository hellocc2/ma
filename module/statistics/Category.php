<?php

namespace Module\Statistics;

use Helper\RequestUtil as R;
use \Helper\Promotion as Promotion;
use Helper\CheckLogin as CheckLogin;

class Category extends \Lib\common\Application {
	public function __construct() {
		CheckLogin::getMemberID();
		$params = R::getParams ();
		$tpl = \Lib\common\Template::getSmarty ();
		$menu_action = $params->menu_action;
		$id = $params->id;
		$WebsiteId = $_SESSION['ma_websiteId'];
// 		var_dump($_SESSION);
// 		var_dump($WebsiteId);
		$tpl->assign ( 'WebsiteId', $WebsiteId );
		switch ($menu_action) {
			case "add" :
				$class_all = new Promotion ( 'promotion_category', 0, 'ASC', '', 0, 1 );
				$class_all_option = $class_all->class_option ( '', 0, $id, '' );
				$tpl->assign ( 'class_all_option', $class_all_option );
				$tpl->display ( 'add_category.htm' );
				break;
			case 'edit' :
				$db = \Lib\common\Db::get_db ( 'default' );
				$sql_id = "	SELECT * FROM `milanoo_promotion_category` where `id` = $id";
				$res_id = $db->getRow ( $sql_id );
				$edit_array = $res_id;
				$class_all = new promotion ( 'promotion_category', 0, 'ASC', '', 0, 1 );
				$class_all_option = $class_all->class_option ( '', 0, $edit_array [pid], '', $id );
				$tpl->assign ( 'class_all_option', $class_all_option );
				$tpl->assign ( 'action_name', 'edit' );
				$tpl->assign ( 'id', $id );
				$tpl->assign ( 'edit_array', $edit_array );
				$tpl->display ( 'add_category.htm' );
				break;
			case 'del' :
				$idarray = $params->idarray;
				$id = $params->id;
				
				if ($idarray) {
					for($i = 0; $i < sizeof ( $idarray ); $i ++) {
						if ($del_id)
							$del_id .= ",";
						$del_id .= $idarray [$i];
					}
				} else {
					$del_id = $id;
				}
				
				$db = \Lib\common\Db::get_db ( 'default' );
				if (! $del_id) {
					$this->alert_forward ( 'del_no_id', '', '1' );
				}
				
				$sql = "delete from `milanoo_promotion_category` where id in( " . $del_id . " )";
				if ($db->execute ( $sql )) {
					if ($WebsiteId == 1) {
						@unlink ( "../data/promotion_category.php" );
					} else {
						@unlink ( "../data/promotion_category.php" );
						//@unlink ( "../data/promotion_category_" . $WebsiteId . ".php" );
					}
					echo "删除分类成功！";
				} else {
					echo '删除分类失败';
				}
				header('Location: ../index.php?module=statistics&action=category');
				break;
			case 'add_categroypost' :
				$name = $params->name;
				$fup = $params->fup;
				if (! $fup) {
					$fup = 0;
				}
				$db = \Lib\common\Db::get_db ( 'default' );
				$sql = "INSERT INTO `milanoo_promotion_category` (`pid`, `name`,`WebsiteId`) VALUES ('" . $fup . "','" . $name . "','1')";
				if ($db->execute ( $sql )) {
					if ($WebsiteId == 1) {
						@unlink ( "../data/promotion_category.php" );
					} else {
						@unlink ( "../data/promotion_category.php" );
						//@unlink ( "../data/promotion_category_" . $WebsiteId . ".php" );
					}
					echo "添加分类成功！";
				} else {
					echo '添加分类失败';
				}
				header('Location: ../index.php?module=statistics&action=category');
				break;
			case 'edit_categroypost' :
				$name = $params->name;
				$fup = $params->fup;
				$id = $params->id;
				
				if (! $fup) {
					$fup = 0;
				}
				$db = \Lib\common\Db::get_db ( 'default' );
				$sql = "UPDATE `milanoo_promotion_category` SET `pid` = '" . $fup . "' ,`name` = '" . $name . "' where Id= '" . $id . "'";
				if ($db->execute ( $sql )) {
					if ($WebsiteId == 1) {
						@unlink ( "../data/promotion_category.php" );
					} else {
						@unlink ( "../data/promotion_category.php" );
						//@unlink ( "../data/promotion_category_" . $WebsiteId . ".php" );
					}
					echo "修改分类成功！";
				} else {
					echo '修改分类失败';
				}
				header('Location: ../index.php?module=statistics&action=category');
				break;
			default :
				//echo $WebsiteId;
				$promotionurl_all = new Promotion ( 'promotion_category', 0, 'ASC', '', 0, 1 );
				$promotionurl_all_print = $promotionurl_all->class_print ( '0', 1 );
				$tpl->assign ( 'promotionurl_all_print', $promotionurl_all_print );
				$tpl->display ( 'category_management.htm' );
				break;
		}
	}
}
