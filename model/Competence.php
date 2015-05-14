<?php
namespace Model;

class Competence {
	
	/**
	 * 获取权限组
	 *
	 */
	public function getMAcompetencegroup() {
		$db = \Lib\common\Db::get_db ();
		$sql = "SELECT `name`,`competence_menu` FROM `oa_competence` GROUP BY `name`";
		$rs = $db->SelectLimit ( $sql );
		$row = array ();
		$competence_all = array ();
		if ($rs->RecordCount ()) {
			while ( ! $rs->EOF ) {
				$row = $rs -> fields;
				$competence_all [$row ["name"]] = $row ["competence_menu"] ;
				$rs->MoveNext ();
			}
		}
		return $competence_all;
	}

	public function get_milanoo_admin_competence() {
		$milanoo_db = \Lib\common\Db::get_db('milanoo');
		$sql = "SELECT `name` FROM milanoo_admin_competence GROUP BY `name`";
		$rs = $milanoo_db->SelectLimit ( $sql );
		$row = array ();
		$competence_all = array ();
		if ($rs->RecordCount ()) {
			while ( ! $rs->EOF ) {
				$row = $rs -> fields;
				$competence_all [$row ["name"]] = $row ["competence_menu"] ;
				$rs->MoveNext ();
			}
		}
		return $competence_all;
	}
		 
	public function getCompetenceList() {
		$db = \Lib\common\Db::get_db ();
		$sql = "	SELECT * FROM `oa_competence`";
		$rs = $db->SelectLimit ( $sql );
		$row = array ();
		$competence_all = array ();
		if ($rs->RecordCount ()) {
			while ( ! $rs->EOF ) {
				$row = $rs -> fields;
				$competence_all [] = array ('id' => $row ["id"], 'name' => $row ["name"] );
				$rs->MoveNext ();
			}
		} 
		return $competence_all;
	}
	
	public function getCompetence($id) {
		$db = \Lib\common\Db::get_db ();
		$sql = "SELECT * FROM `oa_competence` where id='$id'";
		$row = $db->getRow ( $sql );
		return $row;
	}
	
	public function getMenuList() {
		$db = \Lib\common\Db::get_db ();
		
		$sql = "SELECT * FROM `oa_menu`	ORDER BY `order` ASC";
		$rs = $db->SelectLimit ( $sql );
		$row = array ();
		if ($rs->RecordCount ()) {
			while ( ! $rs->EOF ) {
				$row = $rs->fields;
				$id [] = $row ["id"];
				$all_id [$row ["pid"]] [] = $row ["id"];
				$pid [$row ["id"]] = $row ["pid"];
				$name [$row ["id"]] = $row ["name"];
				$module [$row ["id"]] = $row ["module"];
				$action [$row ["id"]] = $row ["action"];
				$order [$row ["id"]] = $row ["order"];
				//$competence [$row ["id"]] = \Helper\ArrayStr::competence_exp ( $row ["competence"] );
				$competence [$row ["id"]] = $row ["competence"];
				$exit_id [$row ["pid"]] .= "," . $row ["id"];
				$rs->MoveNext ();
			}
		}
		$result ["module"] = $module;
		$result ["action"] = $action;
		$result ["all_id"] = $all_id;
		$result ["competence"] = $competence;
		$result ["exit_id"] = $exit_id;
		$result ["name"] = $name;
		$result ["pid"] = $pid;
		return $result;
	}
	
	public function addCompetence($competence_name, $competence_menu, $competence_note, $competence_details) {
		$db = \Lib\common\Db::get_db ();
		
		for($i = 0; $i < sizeof ( $competence_menu ); $i ++) {
			if ($competence_menu_text)
				$competence_menu_text .= ",";
			if ($competence_details_text)
				$competence_details_text .= ",";
			$competence_menu_text .= $competence_menu [$i];
			if (! empty ( $competence_details )) {
				for($s = 0; $s < sizeof ( $competence_details [$competence_menu [$i]] ); $s ++) {
					if ($details_text [$competence_menu [$i]])
						$details_text [$competence_menu [$i]] .= "|";
					$details_text [$competence_menu [$i]] .= $competence_details [$competence_menu [$i]] [$s];
				}
				if ($details_text [$competence_menu [$i]])
					$competence_details_text .= $competence_menu [$i] . "||" . $details_text [$competence_menu [$i]];
			}
		
		}
		$sql = "INSERT INTO `oa_competence`
									(`name`,
									`note`,
									`competence_menu`,
									`competence_details`) 
									VALUES ('$competence_name','$competence_note','$competence_menu_text','$competence_details_text')";
		$sth = $db->Prepare ( $sql );
		$res = $db->Execute ( $sth );
		return $res;
	}
	
	public function editCompetence($competence_name, $competence_menu, $competence_note, $competence_details, $id) {
		$db = \Lib\common\Db::get_db ();
		
		for($i = 0; $i < sizeof ( $competence_menu ); $i ++) {
			if ($competence_menu_text)
				$competence_menu_text .= ",";
			if ($competence_details_text)
				$competence_details_text .= ",";
			$competence_menu_text .= $competence_menu [$i];
			if ($competence_details [$competence_menu [$i]]) {
				for($s = 0; $s < sizeof ( $competence_details [$competence_menu [$i]] ); $s ++) {
					if ($competence_details [$competence_menu [$i]] [$s]) {
						if ($details_text [$competence_menu [$i]])
							$details_text [$competence_menu [$i]] .= "|";
						$details_text [$competence_menu [$i]] .= $competence_details [$competence_menu [$i]] [$s];
					}
				}
			}
			if ($details_text [$competence_menu [$i]])
				$competence_details_text .= $competence_menu [$i] . "||" . $details_text [$competence_menu [$i]];
		}
		$sql = "	UPDATE `oa_competence`
								SET `name` = '$competence_name' ,`note` = '$competence_note' , `competence_menu` = '$competence_menu_text' , `competence_details` = '$competence_details_text' 
								where `id` = '$id'";
		$sth = $db->Prepare ( $sql );
		$res = $db->Execute ( $sth );
		return $res;
	}
	
	public function delCompetence($id) {
		$db = \Lib\common\Db::get_db ();
		
		$sql = "delete from `oa_competence` where `id` = '$id'";
		$sth = $db->Prepare ( $sql );
		$res = $db->Execute ( $sth );
		return $res;
	}
}