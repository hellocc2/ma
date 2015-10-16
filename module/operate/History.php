<?php
namespace Module\operate;
use Helper\RequestUtil as R;

/**
 * 历史行情
 */
class History extends \Lib\common\Application {
	public function __construct() {
		//$client=\Helper\CheckLogin::sso();
		$tpl = \Lib\common\Template::getSmarty ();
		$act = R::getParams ('act');
		$result=array('code'=>500,'msg'=>'操作失败');
		$history=new \Model\History();
				
		switch($act){
			case 'add':
				$tpl->assign('time',time());
				$tpl->display ( 'operate_history_add.html' );
				exit;
			break;
			case 'addpost':
				$hdate=R::getParams ('hdate');
				$htime=R::getParams ('htime');
				$trend=R::getParams ('trend');
				$point=R::getParams ('point');
				$note=R::getParams ('note');
				$open=R::getParams ('open');
				$close=R::getParams ('close');
				$thing=R::getParams ('thing');
				//$memberId=R::getParams ('memberId');
				$gmt_create=time();
				
				$data=array();
				$data['hdate']=$hdate;
				$data['htime']=$htime;
				$data['trend']=$trend;
				$data['point']=$point;
				$data['note']=$note;
				$data['open']=$open;
				$data['close']=$close;
				$data['thing']=$thing;
				//$data['memberId']=$memberId;
				$data['gmt_create']=$gmt_create;				
				
				$res=$history->addHistory($data);
				if($res){
					$result=array('code'=>200,'msg'=>'操作成功');
				}
				echo json_encode($result);	exit;		
			break;
			case 'multi_upload':
				//testglob(1);
				if(strrchr($_FILES['filename']['name'],'.csv')!='.csv'){
					\Helper\Js::alertForward('文件格式错误，只能是csv格式的文件');
				}
				ini_set("max_execution_time", "0");
				set_time_limit(0);
				ini_set("memory_limit",'200M');
				
				$type_sub 		= R::getParams('multi_type_sub');
				$handle=fopen($_FILES['filename']['tmp_name'],'r');
				$keys=fgetcsv($handle,1000,',');
				
				while($data=fgetcsv($handle,1000,',')){
					$value=array_combine($keys, $data);
					$value['STATUS']='UNFINISHED';
					$value['TYPE']=$type;
					$value['TYPE_SUB']=$type_sub;
					$value['USER_ID']=$_SESSION[SESSION_PREFIX . "uid"];	
					if($type_sub!='RMATHD'){
						unset($value['trackingNumber']);
					}				
					$values[]=$value;
				}
				$upload_result=$history->multi_upload($values);
				fclose($handle);
				if($upload_result['fail']==0){
					echo ' 批量上传成功 '.$upload_result['succeed'].' 条';
				}else{
					$fail_serial_num=implode(',', $upload_result['fail_serial_num']);
					echo ' 批量上传失败,错误的数据为:第 '.$upload_result['succeed'].' 条';
				}				
				break;
			default:
				$tpl->display ( 'operate_history.html' );
			
		}
		
	}
}



