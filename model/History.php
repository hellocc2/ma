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
	*批量上传数据
	*/
	function  multi_upload($values){
		//testshow($keys);
		//testshow($values,1);	
		$db_flag=1;
		$values=eval('return '.iconv('GBK', 'UTF-8', var_export($values,true)).';');		
		$num=$upload_result['fail']=$upload_result['succeed']=0;
		$this->db->BeginTrans();
			
		foreach($values as $data){
			$num++;
			$keys=array_keys($data);
			
			//组装sql
			$sql="insert  into `".TABLE_PREFIX."rmb` (`";
			$sql.=implode('`,`',$keys);
			$sql.='`) values';
			$temp=implode("','",$data);
			$value="('".$temp."')";
			$sql.=$value;
		
			$result=$this->db->Execute($sql);
			if(!$result){
				$upload_result['fail']++;
				$upload_result['fail_serial_num'][]=$num;
				$db_flag=0;
			}
			$insert_id=$this->db->Insert_ID();
			if(!empty($insert_id)){
				$log['id']=$insert_id;
				$log['message']="<span style='color:#006699'>[系统]</span>添加成功";
				//$this->addLog($log);
				$upload_result['succeed']++;
			}					
		}
		if($db_flag==1){
			$this->db->CommitTrans();
		}else{
			$this->db->RollbackTrans();
		}
		return 	$upload_result;
	}
	
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
	
	/**
	*查询历史行情
	*/
	public function selectHistory($data=array()){
		$output=false;
		$where[]="WHERE 1 = 1";
		if(!empty($data['hit_note'])){
				$where[]="h.his_note = {$data['hit_note']}";
		}
		$where=implode(' and ', $where);
		
		$sql = "SELECT   h.his_date,h.his_point
				FROM a56rmgri_money.rmb_history AS h  
				{$where}";
				
		$query	= $this->db->Execute($sql);
		$pageSize=12;
		$page=1;
		if(!empty($data['pageSize'])){
			$pageSize=$data['pageSize'];
		}
		if(!empty($data['page'])){
			$page=$data['page'];
		}		
		$last_num= $pageSize* ($page-1);
		$num	= $query->MaxRecordCount($query);
		
		if($last_num>=$num) $last_num=(ceil( $num / $pageSize )-1) * $pageSize;
		$rs= $this->db->SelectLimit($sql,$pageSize,$last_num);
		$row = array ();
		$hitory_all = array ();
		if ($rs->RecordCount ()){
			$roles=array();
			while ( ! $rs->EOF ) {
				$row = $rs -> fields;
	            $hitory_all['his_date'][]=date('d',strtotime($row['his_date']));
				$hitory_all['his_point_high'][]=$row['his_point'];
				$rs->MoveNext ();
			}
		}
		$hitory_all['totalCount']=$num;
		//echo '<pre/>';print_r($hitory_all);exit;
		return $hitory_all;
	}
	
	
	
}