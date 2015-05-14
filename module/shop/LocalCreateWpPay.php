<?php
namespace Module\Shop;
use \Helper\RequestUtil as R;
/**
 * 只用于本地，不可上传到线上
 * 用于向worldPay提交一个虚拟订单
 * 帮助财务收取日本银行汇款的销售
 * @author chengjun<chengjun@milanoo.com>
 *
 */
class LocalCreateWpPay extends \Lib\Common\Application {
	public function __construct(){	
		$tpl = \Lib\Common\Template::getSmarty();
		$params = R::getParams();
		$memberId = $_SESSION[SESSION_PREFIX.'localMemberId'];
		if($memberId){
			if(!empty($params->POSTFORM) && $params->POSTFORM==1){
				$orderId = !empty($params->orderId) ? $params->orderId : \Helper\Js::alertForward('请输入正确的订单号','',1,4000,0);
				$amount = !empty($params->amount) ? $params->amount : \Helper\Js::alertForward('请输入正确的金额','',1,4000,0);
				$currency = !empty($params->currency) ? $params->currency : 'JPY';
				$result = $this->payBankTransfer($orderId, $amount,$currency);
				if($result == false){
					$jumpUrl =\Helper\ResponseUtil::rewrite(array('url'=>'?module=shop&action=localCreateWpPay','isxs'=>'no'));
					\Helper\Js::alertForward('提交失败',$jumpUrl,1,4000,0);
				}
			}else{
				if(!empty($params->act) && $params->act='seeRecods'){
					//查看记录
					$filename = ROOT_PATH.'/data/log/'.(date('Ym')).'_localCreateWpPayYhhk.log';
					$tpl->assign('no_login',0);
					$tpl->assign('record',1);
				}else{
					//支付表单
					$tpl->assign('no_login',0);
				}
			}
		}else{
			if(!empty($params->POSTFORM) && $params->POSTFORM==1){
				//登录
				$jumpUrl =\Helper\ResponseUtil::rewrite(array('url'=>'?module=shop&action=localCreateWpPay','isxs'=>'no'));
				if(!empty($params->userName) && $params->userName=='milanooWp' && !empty($params->userPass) && $params->userPass=='milanooWpPass'){
					$_SESSION[SESSION_PREFIX.'localMemberId'] = 'milanooWp';
					\Helper\Js::alertForward('登录成功',$jumpUrl,1,4000,0);
				}else{
					\Helper\Js::alertForward('登录失败',$jumpUrl,1,4000,0);
				}
			}else{
				$tpl->assign('no_login',1);
			}
		}
		$tpl->display('localCreateWpPay.htm');
		return;
	}
	
	//支付
	public static function payBankTransfer($orderId,$amount,$currency='JPY'){
		$countryCode = 'JP';
		$currenyCode = $currency;//对应支持货币
	
		$mr = 'Mr.';
	
	
		$session_id = session_id();
		$ip = \Helper\RequestUtil::getClientIp();
		//购物信息
		$shopperArray = array(
				"email" => 'wywcpa@gmail.com', //用户联系邮箱
				"firstname" => '新之助', "lastname" => '野塬', //用户姓名
				"street" => 'tianfuruanjianyuan B3-3', //用户地址
				"postalcode" => '610000', //用户邮编
				"city" => 'tokyo', //联系城市
				"telephone" => '1111111', //用户电话
				"countrycode" => 'JP',//国家代码
		);
		
		$order = array();
		$order['ordersId'] = $orderId;
		$order['ordersCid'] = $orderId;
	
		//将用户订单中的货币总价转换成对应支持的货币总价
		$order['amount'] = $amount;
		$order['logisticsCosts'] = 0;
		$total = $order['amount']+$order['logisticsCosts'];
		$amoundDisplay = $order['amount'] * 100;
		$logisticsCostsDisplay = $order['logisticsCosts'] * 100;
		$totalammount = $amoundDisplay + $logisticsCostsDisplay;
		$currenyName = \config\Currency::$currencyTranslations[$currenyCode]['name']['en-uk'];//取得用户支付的货币种类名字
	
		$lang = \Langpack::$items;
		$orderContent = <<<EOT
		<center><table>
		<tr><td bgcolor='#ffff00'>{$lang['shop_OrderNumber']}:</td><td colspan='2' bgcolor='#ffff00' align='right'>{$orderId}</td></tr>
		<tr><td colspan="2">{$lang['shop_Order_Subtotal']}:</td><td align="right">{$order['amount']}</td></tr>
		<tr><td colspan="2">{$lang['cart_freight']}:</td><td align="right">{$order['logisticsCosts']}</td></tr>
		<tr><td colspan="2" bgcolor="#c0c0c0">{$lang['thing_Item_Total']}:</td><td bgcolor="#c0c0c0" align="right">{$currenyName} {$total}</td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr><td bgcolor="#ffff00" colspan="3">{$lang['order_billing_address']}:</td></tr>
		<tr><td colspan="3">{$mr}野塬,<br>新之助,<br>tokyo,<br>japan</td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr><td bgcolor="#ffff00" colspan="3">{$lang['order_shipping_address']}:</td></tr>
		<tr><td colspan="3">{$mr}野塬,<br>新之助,<br>tokyo,<br>japan</td></tr>
		</table></center>
EOT;
	
		$worldPay = new \Lib\_3rd\worldpay\BankTransfer();
		//$worldPay->BankTransferstart(true);//测试
		$worldPay->BankTransferstart(false);
	
		//orderId增加特殊字符以避免多次支付造成单号重复提交失败
		$specialString = substr(time(),-4);
		$desc = 'jp-tr_'.rand(10000000,99999999);
	
		$worldPay->orderId = $order['ordersId'];
	
		//测试
		$worldPay->totalammount = $totalammount;
		$worldPay->description = $desc;
		$worldPay->currencyCode = $currenyCode;//强制使用银行支持货币
	
	
		$worldPay->StartXML();
		$worldPay->FillDataXML($orderContent);
		$worldPay->FillBankXml($countryCode);
		$worldPay->FillShopperXML($shopperArray);
		$worldPay->EndXML();
	
		$bankResult = $worldPay->CreateConnection();
	
		$xmlFormat = new \Lib\_3rd\worldpay\BibitFormat();
		$xmlFormat->ParseXML($bankResult);
		$reuturnUrl= $xmlFormat->ReadXml($bankResult, "reference");
	
		$shoppingProcess = new \Model\ShoppingProcess();
	
		if(strpos($reuturnUrl, 'https://')==0){
			//返回正确连接
			$orderCode = $xmlFormat->ReadXml($bankResult, "orderStatus","orderCode");//订单号
			$paymentReference = $xmlFormat->ReadXml($bankResult, "reference","id");//第三方单号
				
			//$orderCodeArray = explode('_',$orderCode);
			//if(!empty($orderCodeArray) && isset($orderCodeArray[0])){
			//	$orderCode = $orderCodeArray[0];
			//}
			if($orderCode == $order['ordersId'] && !empty($paymentReference)){
				$ordersPayDetails = "Payment:yhhk|CurrencyCode:{$currenyCode}|amount:{$total}|Remarks:{$paymentReference}|time:".time();				
				if(!empty($bankResult)){
					$data = array('orderCid'=>$order['ordersCid'],'orderId'=>$worldPay->orderId,'addTime'=>date('Y-m-d H:i:s',time()),'Reference'=>$paymentReference,'Amount'=>$total,'ip'=>$ip);
					$filename = ROOT_PATH.'/data/log/'.(date('Ym')).'_localCreateWpPayYhhk.log';
					$handle = fopen($filename, 'a');
					fwrite($handle, "\n\n-------发送xml-----\n\n");
					fwrite($handle, $worldPay->xml."\n");
					fwrite($handle, "\n-------回传xml-----\n\n");
					fwrite($handle, $bankResult."\n");
					fwrite($handle, "\n-------提交成功-----\n");
					fwrite($handle, var_export($data,true)."\n------END\n\n");
					fclose($handle);
				}
	
				$cancelURL = urlencode(\Helper\ResponseUtil::rewrite(array('url'=>'?module=shop&action=localCreateWpPay','isxs' => 'no')));
				$successURL = urlencode(\Helper\ResponseUtil::rewrite(array('url'=>'?module=shop&action=localCreateWpPay','isxs' => 'no')));
	
				if(LangDirName=='jp'){
					$language = 'ja';
				}else{
					$language = LangDirName;
				}
	
				//$urlParam = "&preferredPaymentMethod=TRANSFER_{$countryCode}-BANK&country={$countryCode}&language=".$language.'&successURL='.$successURL.'&cancelURL='.$cancelURL;
				//$reuturnUrl .= $urlParam;
				echo '<h1>Reference NO.：'.$paymentReference.'</h1>';
				echo '<br /><a href="http://test.milanoo.com/shop/localCreateWpPay.html">继续提交</a> | <a href="'.ROOT_URLD.'">回到首页</a>';
				//header("Location:" . $reuturnUrl);
				exit;
			}else{
				//返回XML写入临时文件
				if(!empty($bankResult)){
					$data = array('orderCid'=>$order['ordersCid'],'orderId'=>$worldPay->orderId,'addTime'=>date('Y-m-d H:i:s',time()),'Amount'=>$total,'ip'=>$ip);
					$filename = ROOT_PATH.'/data/log/'.(date('Ym')).'_localCreateWpPayYhhk.log';
					$handle = fopen($filename, 'a');
					fwrite($handle, "\n\n-------发送xml-----\n\n");
					fwrite($handle, $worldPay->xml."\n");
					fwrite($handle, "\n-------回传xml-----\n\n");
					fwrite($handle, $bankResult."\n");
					fwrite($handle, "\n-------提交失败-----\n");
					fwrite($handle, var_export($data,true)."\n------END\n\n");
					fclose($handle);
				}
				return false;
			}
		}else{
			//返回XML写入临时文件
			if(!empty($bankResult)){
				$data = array('orderCid'=>$order['ordersCid'],'orderId'=>$worldPay->orderId,'addTime'=>date('Y-m-d H:i:s',time()),'Amount'=>$total,'ip'=>$ip);
				$filename = ROOT_PATH.'/data/log/'.(date('Ym')).'_localCreateWpPayYhhk.log';
				$handle = fopen($filename, 'a');
				fwrite($handle, "\n\n-------发送xml-----\n\n");
				fwrite($handle, $worldPay->xml."\n");
				fwrite($handle, "\n-------回传xml-----\n\n");
				fwrite($handle, $bankResult."\n");
				fwrite($handle, "\n-------提交失败-----\n");
				fwrite($handle, var_export($data,true)."\n------END\n\n");
				fclose($handle);
			}
			return false;
		}
	}
}