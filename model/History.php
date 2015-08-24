<?php
namespace Model;

class History{
	
	private $db;
	const PAGENUM =20;
	function __construct(){
		 $this->db =\Lib\common\Db::get_db ();		
	}
	//=======================获取数据start=====================
	/**
	 * 添加历史行情
	 */
	public function addHistory($data=array()){
		$output=false;
		
		$arr=array($data['hdate'],$data['htime'],$data['trend'],$data['point'],$data['note'],$data['open'],$data['close'],$data['thing'],$data['gmt_create']);
		
		$sql = "INSERT INTO `rmb_history` (`his_date`,`his_time`,`his_trend`,`his_point`,`his_note`,`his_point_open`,`his_point_close`,`his_thing`,`gmt_create`) VALUES (?,?,?,?,?,?,?,?,?)";

		$sth = $this->db->Prepare ($sql);
		$res = $this->db->Execute ($sth,$arr);
		
		if($res!==false){
			$output=true;
		}
		
		return $output;
	}
	
	
	
	
}