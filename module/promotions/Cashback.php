<?php
namespace Module\Promotions;
use \Helper\RequestUtil as R;
use \Helper\Js as Js;
/**
 * cashback处理层
 * @author chengjun<chengjun@milanoo.com>
 *
 */
class Cashback extends \Lib\Common\Application {
	public function __construct(){
		//验证登录
		$memberId = \Helper\CheckLogin::getMemberID();
		
		$tpl = \Lib\common\Template::getSmarty ();
		$params = R::getParams ();
		
		//初始化好评指定发布平台
		$LinkUrlArray = array('coolcomputing'=>'http://www.coolcomputing.com/',
							'resellerratings'=>'http://www.resellerratings.com/',
							'five-starsite'=>'http://www.five-starsite.com/');
		
		if(!empty($params->POSTFORM) && $params->POSTFORM==1){
			//接收表单数据
			$link_type = !empty($params->link_type) ? $params->link_type : js::alertForward('Please Select Review Site', '', '1',4000,0);
			$link_url = !empty($params->link_url) ? $params->link_url : js::alertForward('Please Enter Review URL', '', '1',4000,0);
			$order_id = !empty($params->order_id) ? $params->order_id : js::alertForward('Please Select Order', '', '1',4000,0);
			$order_cid = !empty($params->order_cid) ? $params->order_cid : js::alertForward('Please Select Order', '', '1',4000,0);
			$cashback_method = !empty($params->cashback_method) ? $params->cashback_method : js::alertForward('Please Select Cashback Type', '', '1',4000,0);
			$notes = !empty($params->notes) ? $params->notes : '';
			
			$linkUrlArrayNumKey = array();
			foreach($LinkUrlArray as $v){
				$linkUrlArrayNumKey[] = $v;
			}
			
			if(($linkKey = array_search($link_type,$linkUrlArrayNumKey))!==false){
				$link_type = $linkKey+1;
			}
			
			//上传图片
			$uploadFiles = array();
			if(!empty($_FILES['screenshots'])){
				$myFiles  = $_FILES['screenshots'];
				$fileCount = count($myFiles['name']);
				if($fileCount > 0){
					for($i=0;$i<$fileCount;$i++){
						if(isset($myFiles['tmp_name'][$i]) && !empty($myFiles['tmp_name'][$i])){
							$resultUpload = \Helper\Upload::imageUpload($myFiles['tmp_name'][$i],$myFiles['size'][$i],$myFiles['name'][$i],'2048000','/fs/file/uploadCashback.htm');
							if($resultUpload == 10000){
								\Helper\Js::alertForward('file_type_error', '', '1');
							}
							if($resultUpload == 10001){
								\Helper\Js::alertForward('file_size_error', '', '1');
							}
							if(isset($resultUpload['filePath'])){
								$uploadFiles[] = $resultUpload['filePath'];
							}else{
								Js::alertForward('upload_error', '', '1');
							}
						}
					}
				}
			}
			
			$firstPicUrl = '';
			$secondPicUrl = '';
			if(!empty($uploadFiles)){
				if(isset($uploadFiles[0])){
					$firstPicUrl = $uploadFiles[0];
				}
				if(isset($uploadFiles[1])){
					$secondPicUrl = $uploadFiles[1];
				}
			}else{
				Js::alertForward('upload_error', '', '1');
			}
			
			if($cashback_method<0 || $cashback_method>3){
				js::alertForward('Please Select Cashback Type', '', '1',4000,0);
			}
			
			$data = array();
			$data['c.memberId'] = $memberId;
			$data['c.langCode'] = SELLER_LANG;
			$data['c.reviewWeb'] = $link_type;
			$data['c.reviewUrl'] = $link_url;
			$data['c.orderId'] = $order_id;
			$data['c.orderCid'] = $order_cid;
			$data['c.screenshot1'] = $firstPicUrl;
			$data['c.screenshot2'] = $secondPicUrl;
			$data['c.cashbackType'] = $cashback_method;
			$data['c.note'] = $notes;
			
			
			$mSendCashBack = new \Model\Promotion();
			$result = $mSendCashBack->addCashback($data);
			if(!empty($result) && $result['code']==0){
				$jumpUrl = \Helper\ResponseUtil::rewrite(array('url'=>'?module=member&action=cashBack','isxs'=>'no'));
				js::alertForward('advisoryOk',$jumpUrl,1);
			}else{
				js::alertForward('submit_failed','',1);
			}
		}else{
			//展示cashback表单页面
			if(SELLER_LANG != 'en-uk'){
				//只有英文站才有CASHBACK
				header('location:'.ROOT_URL);
				exit;
			}
		
			$cashBackResult = array();
			$cashBackResult['code'] = 0;
			$cashBackResult['msg'] = '操作成功';
			
			//获取对应订单
			$data = array();
			$data['memberId'] = $memberId;
			$data['langCode'] = SELLER_LANG;
			$data['websiteId'] = 1;
			$data['num'] = 0;
			
			$mGetOrder = new \Model\Promotion();
			$orderForCashBack = $mGetOrder->findOrderForCashBack($data);
			if(!empty($orderForCashBack) && $orderForCashBack['code']==0){
				$cashBackResult['orders'] = array();
				if(!empty($orderForCashBack['orders'])){
					foreach($orderForCashBack['orders'] as $key=>$order){
						$cashBackResult['orders'][$key]['orderCid'] = $order['orderCid'];
						$cashBackResult['orders'][$key]['payType'] = $order['payType'];
						$cashBackResult['orders'][$key]['orderId'] = $order['orderId'];
						$cashBackResult['orders'][$key]['cashback'] = $order['cashback'];
						$cashBackResult['orders'][$key]['backAmount'] = $order['backAmount'];
						if($order['payType'] == 'xyk' && !empty($val['cardNum'])){
							$cashBackResult['orders'][$key]['cardNum'] = $order['cardNum'];
						}
						if(!empty($order['products'])){
							$cashBackResult['orders'][$key]['products'] = array();
							foreach($order['products'] as $kPro=>$vPro){
								$tempProduct = array();
								$tempProduct['productName'] = $vPro['productName'];
								$tempProduct['productId'] = $vPro['productId'];
								$tempProduct['orderId'] = $vPro['orderId'];
								if(!empty($vPro['productPicUrl'])){
									$picArray = explode('|||',$vPro['productPicUrl']);
									if(!empty($picArray)){
										$tempProduct['productPicUrl'] = $picArray;
									}
								}
								if(!empty($tempProduct) && !in_array($tempProduct,$cashBackResult['orders'][$key]['products'])){
									$cashBackResult['orders'][$key]['products'][$kPro] = $tempProduct;
								}
							}
						}
					}
				}else{
					$cashBackResult['code'] = 1;
					$cashBackResult['msg'] = 'no orders data';
				}
			}else{
				$cashBackResult['code'] = 2;
				$cashBackResult['msg'] = 'get orders failed';
			}
			
			//获取linkurl
			$cashBackResult['linkUrl'] = array();
			if(!empty($LinkUrlArray)){
				$cashBackResult['linkUrl'] = $LinkUrlArray;
			}
			$tpl->assign('cashBackResult',$cashBackResult);
			$tpl->display('cashback.htm');
		}
		return true;
	}
}