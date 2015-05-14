<?php
namespace Helper;

use helper\ResponseUtil as Rewrite;
/**
 * 
 * 支付帮助类
 * @author Jiang Lin
 *
 */
class Payment {
	/**
	 * 
	 * 支付入口
	 * @param unknown_type $order
	 * @param unknown_type $params_all
	 */
	public static function orderPayment($orderInfo, $params_all) {
		
		$orderInfo['order']['amount'] = round($orderInfo['order']['amount'], 2);
		$orderInfo['order']['logisticsCosts'] = round($orderInfo['order']['logisticsCosts'], 2);
		
		
		$order = $orderInfo['order'];
		$shoppingProcess = new \Model\ShoppingProcess();
		
		$orderInfo['order']['payClass'] = $params_all->payment_type;
		//银行汇款在处理后发送邮件
		if($params_all->payment_type!='yhhk'){
			/*
			 * 增加payTimes参数
			* 当payTimes有值的时候表示不更新支付次数
			* 当payTimes不传或无值的时候表示同事更新支付次数
			*/
			$response = $shoppingProcess->updateOrder(array('cr.ordersId' => $order['ordersId'], 'cr.payClass' => $params_all->payment_type,'cr.endTime'=>time() + (($order['viewStock']+$order['expressTime']) * 24 * 3600),'payTimes'=>0));
			self::sendEmail($orderInfo);
		}

		
		switch($params_all->payment_type) {
			case 'xyk':
				
				self::paymentXYK($orderInfo, $params_all);
				
				break;
			case 'paypal':
				
				self::paymentPaypal($order, $params_all);
				
				break;
			
			case 'yhzx':
				
				
				
				self::paymentYHZX($order, $params_all);
				
				break;
			
			case 'yhhk':
				$worldPaySupport = \Config\PaymentMethod::$bankTransfer['worldPay'];
				if(!empty($params_all->worldPay_country) && $params_all->worldPay_country!='other' && !defined('APP_TYPE') && APP_TYPE!='wap'){//手机站暂时不处理
					$worldPay_country = $params_all->worldPay_country;
					if(array_key_exists($worldPay_country, $worldPaySupport)){
						self::payBankTransfer($orderInfo,$params_all);
					}
				}else{
					if(SELLER_LANG=='ja-jp'){
						$response = $shoppingProcess->updateOrder(array('cr.ordersId' => $order['ordersId'],'cr.cardType' => "jpanBankTransfer",'cr.payClass' => $params_all->payment_type,'cr.endTime'=>time() + (($order['viewStock']+$order['expressTime']) * 24 * 3600),'payTimes'=>0));
						$orderInfo['order']['cardType'] = 'jpanBankTransfer';
					}else{
						//写入订单信息，表示普通银行汇款
						$response = $shoppingProcess->updateOrder(array('cr.ordersId' => $order['ordersId'], 'cr.payClass' => $params_all->payment_type,'cr.endTime'=>time() + (($order['viewStock']+$order['expressTime']) * 24 * 3600),'cr.cardType' => "MILANOOBANK",'payTimes'=>0));
						$orderInfo['order']['cardType'] = 'MILANOOBANK';
					}
					//其他国家和手机站直接发送
					self::sendEmail($orderInfo);
				}
				
				break;
		
		}
	
	}
	
	public static function sendEmail($orderInfo) {
		if($orderInfo['order']['ordersPay']==1) {
			$title = 'Email_CKOK';
		} else {
			$title = 'Email_orderOK';
		}
		
		$emailAll = array('lang' => SELLER_LANG, 'email' => $orderInfo['order']['consigneeEmail'], 'products' => $orderInfo['productList'], 'Orders' => $orderInfo, 'emailtitle' => $title, 'theme' => 'order_achieve.htm');
		
		\Helper\Stomp::SendEmail($emailAll);
	}
	
	public static function paymentYHZX($order, $params_all) {
		
		$bankInfo = \config\PaymentMethod::$bank;
		
		$serviceName = $bankInfo[$params_all->bankSelect];
		
		if(!empty($serviceName)) {
			$memberContactAll = explode("|", $order['consigneeName']);
			$memberContactAddrALL = explode('|', $order['consigneeAddr']);
			
			$billingAll = explode("|", $order['billingName']);
			$billingAddrAll = explode('|', $order['billingAddr']);
			
			$request = new \stdClass();
			//接口调用设置
			$oneClickServiceInfo = new \stdClass();
			$oneClickServiceInfo->serviceName = $serviceName['service'];
			$oneClickServiceInfo->languageCode = $serviceName['country'][SELLER_LANG];
			
			//支付数据
			$paymentData = new \stdClass();
			$paymentData->customerRef = $order['ordersCid'];
			$paymentData->country = $serviceName['country'][SELLER_LANG];
			
			if($serviceName['service']=='SOFORT'){
				//SOFORT国家设定
				$stateId = $order['stateId'];
				if(isset($serviceName['countrySupport'][$stateId])){
					$paymentData->country = $serviceName['countrySupport'][$stateId];
				}
			}
			
			if($order['Currency'] != 'EUR') {
				$paymentData->amount = \Lib\common\Language::priceByCurrency($order['amount'] + $order['logisticsCosts'], 'EUR', $order['currencyCode']);
			} else {
				$paymentData->amount = $order['amount'] + $order['logisticsCosts'];
			}
			$paymentData->receiveAmount = '0';
			$paymentData->currency = 'EUR';
			$paymentData->receiveCurrency = 'EUR';
			
			//客户信息
			$customerData = new \stdClass();
			$customerData->email = $order['consigneeEmail'];
			$customerData->firstName = $memberContactAll[0];
			$customerData->lastName = $memberContactAll[1];
			$customerData->address1 = $memberContactAddrALL[0];
			$customerData->address2 = $memberContactAddrALL[1];
			$customerData->city = $order['consigneeCity'];
			$customerData->postcode = $order['consigneePostalcode'];
			$customerData->country = strtoupper($order['stateFlag']);
			$customerData->customerLevel = '';
			$customerData->customerId = '';
			$customerData->state = $order['billingUrbanAreas'];
			$customerData->phone = $order['consigneePhone'];
			
			//客户账户信息
			$customerAccountDetails = new \stdClass();
			$customerAccountDetails->paymentChannel = '';
			$customerAccountDetails->bankCode = '';
			$customerAccountDetails->accountNumber = '';
			$customerAccountDetails->accountPassword = '';
			
			//构造request
			$request->oneClickServiceInfo = $oneClickServiceInfo;
			$request->paymentData = $paymentData;
			$request->customerData = $customerData;
			$request->customerAccountDetails = $customerAccountDetails;
			$request->successUrl = ROOT_URL . 'shop/EnvoyReturn.html';
			$request->cancelUrl = ROOT_URL . 'shop/EnvoyReturn.html';
			$request->errorUrl = ROOT_URL . 'shop/EnvoyReturn.html';
			
			$envoy = new \Lib\_3rd\envoy\EnvoyLib();
			
			$response = $envoy->oneClickPaymentRequest($request);
			
			
//			header("Location:https://test.envoytransfers.com/Default.aspx?tokenId=" . $response->oneClickPaymentRequestResult->tokenId);
			header("Location:https://www.envoytransfers.com/Default.aspx?tokenId=" . $response->oneClickPaymentRequestResult->tokenId);
			exit();
		}
	}
	
	public static function paymentXYK($orderInfo, $params_all) {
		$order = $orderInfo['order'];
		$memberContactAll = explode("|", $order['consigneeName']);
		$memberContactAddrALL = explode('|', $order['consigneeAddr']);
		
		$billingAll = explode("|", $order['billingName']);
		$billingAddrAll = explode('|', $order['billingAddr']);
		$session_id = session_id();
		$ip = \Helper\RequestUtil::getClientIp();
		//购物信息
		$shopperArray = array(

		"email" => $order['consigneeEmail'], //用户联系邮箱
"firstname" => $memberContactAll[0], "lastname" => $memberContactAll[1], //用户姓名
"street" => $memberContactAddrALL[0], //用户地址
"postalcode" => $order['consigneePostalcode'], //用户邮编
"city" => $order['consigneeCity'], //联系城市
"telephone" => $order['consigneePhone'], //用户电话
"countrycode" => strtoupper($order['stateFlag'])); //			"countrycode" => strtoupper($order['Country'])
		

		//支付信息
		$PaymentArray = array("cardNumber" => $params_all->cardno, //卡号
"expirymonth" => $params_all->expirymonth, //有效期月份
"expiryyear" => $params_all->expiryyear, //有效期年份
"cardHolderName" => $params_all->cardHolderName, //持卡人姓名
"cvc" => $params_all->cvc, //CVC
"firstName" => $memberContactAll[0], "lastName" => $memberContactAll[1], //联系人姓名
"street" => $memberContactAddrALL[0], //联系人地址
"postalCode" => $order['consigneePostalcode'], //联系人邮编
"city" => $order['consigneeCity'], //			"countryCode" => $order['Country'], 
"countryCode" => strtoupper($order['stateFlag']), "telephoneNumber" => $order['consigneePhone'], 

		"id" => $session_id, "shopperIPAddress" => $ip, "billFirstName" => $billingAll[0], "billLastName" => $billingAll[1], "billStreet" => $billingAddrAll[0], "billPostalcode" => $order['billingPostalcode'], "billCity" => $order['billingCtiy'], //			"billCountryCode" => strtoupper($order['OrdersBillingCountry']),
"billCountryCode" => strtoupper($order['billingStateFlag']), "billTelephoneNumber" => $order['billingPhone']);
		
		$orderContent = <<<EOT
		<center><table>
		<tr><td bgcolor='#CCCCCC'>Your Internet Order:</td><td colspan='2' bgcolor='#ffff00' align='right'>{$order['ordersCid']}</td></tr>
		<tr><td bgcolor='#CCCCCC'>First Name:</td><td colspan='2' bgcolor='#ffff00' align='right'>{$memberContactAll[0]}</td></tr>
		<tr><td bgcolor='#CCCCCC'>Last Name:</td><td colspan='2' bgcolor='#ffff00' align='right'>{$memberContactAll[1]}</td></tr>
		<tr><td bgcolor='#CCCCCC'>Address Line:</td><td colspan='2' bgcolor='#ffff00' align='right'>{$memberContactAddrALL[0]}</td></tr>
		<tr><td bgcolor='#CCCCCC'>Zip/Postal Code:</td><td colspan='2' bgcolor='#ffff00' align='right'>{$order['consigneePostalcode']}</td></tr>
		<tr><td bgcolor='#CCCCCC'>Phone Number:</td><td colspan='2' bgcolor='#ffff00' align='right'>{$order['consigneePhone']}</td></tr>
		</table></center>
EOT;
		
		$worldPay = new \Lib\_3rd\worldpay\Bibit();
		$worldPay->Bibitstart(false);
		
		$worldPay->orderId = $order['ordersId'];
		$totalammount = ($order['amount'] + $order['logisticsCosts']) * 100;
		//测试
		$worldPay->totalammount = $totalammount;
		$worldPay->description = $order['ordersCid'];
		$Cid = str_replace('-', '=', $order['ordersCid']);
		$worldPay->currencyCode = $order['currencyCode'];
		
		$worldPay->StartXML();
		$worldPay->FillDataXML($orderContent);
		switch($params_all->select_card) {
			case 1:
				$worldPay->FillPaymentVISAXML($PaymentArray);
				$PaymentMethod = "Visa Credit";
				break;
			case 2:
				$worldPay->FillPaymentAMEXXML($PaymentArray);
				$PaymentMethod = "American Express";
				break;
			case 3:
				$worldPay->FillPaymentECMCXML($PaymentArray);
				$PaymentMethod = "Mastercard";
				break;
			case 4:
				$worldPay->FillPaymentJCBXML($PaymentArray);
				$PaymentMethod = "JCB";
				break;
			case 5:
				$worldPay->FillPaymentMAESTROXML($PaymentArray);
				$PaymentMethod = "Maestro";
				break;
			case 6:
				$worldPay->FillPaymentDISCOVERXML($PaymentArray);
				$PaymentMethod = "Discover";
				break;
			case 7:
				$worldPay->FillPaymentCARTEBLEUEXML($PaymentArray);
				$PaymentMethod = "Carte Bleue";
				break;
			case 8:
				$worldPay->FillPaymentDINERSXML($PaymentArray);
				$PaymentMethod = "Diners";
				break;
			case 9:
				$worldPay->FillPaymentGLOBALXML($PaymentArray, 'CB-SSL');
				$PaymentMethod = "Carte Bancaire";
				break;
			case 11:
				$worldPay->FillPaymentGLOBALXML($PaymentArray, 'LASER-SSL');
				$PaymentMethod = "Laser Card";
				break;
			case 12:
				$worldPay->FillPaymentVISAXML($PaymentArray);
				$PaymentMethod = "Postepay";
				break;
		}
		
		$worldPay->FillShopperXML($shopperArray);
		
		$worldPay->EndXML();
		$worldPay->xml = utf8_encode($worldPay->xml);
		$bibitResult = $worldPay->CreateConnection();
		
		$xmlFormat = new \Lib\_3rd\worldpay\BibitFormat();
		$xmlFormat->ParseXML($bibitResult);
		
		$Status = $xmlFormat->ReadXml($bibitResult, "lastEvent");
		
		$cardNumber = $xmlFormat->ReadXml($bibitResult, "cardNumber");
		
		if($Status == "AUTHORISED") {
			$orderPay = 1;
		} else {
			
			$filename = ROOT_PATH.'/data/log/'.(date('Ym')).'_paymentCredit.log';
			$handle = fopen($filename, a);
			fwrite($handle, $order['ordersId']."\n");
			fwrite($handle, var_export($worldPay->xml,true)."\n");
			fwrite($handle, var_export($bibitResult,true)."\n------END\n\n");
			fclose($handle);
			$orderPay = 0;
		}
		
		$paytime = time();
		$orderPayDetails = 'Payment:xyk|' . 'CurrencyCode:' . $order['currencyCode'] . '|' . 'amount:' . ($totalammount / 100) . '|' . 'PaymentMethod:' . $PaymentMethod . '|' . 'cardNumber:' . $cardNumber . '|' . 'Remarks:' . $Status . '|' . 'time:' . $paytime;
		$shoppingProcess = new \Model\ShoppingProcess();
		$response = $shoppingProcess->updateOrder(array('cr.ordersId' => $order['ordersId'], 'cr.ordersPay' => $orderPay, 'cr.ordersPayDetails' => $orderPayDetails, 'cr.cardType' => $PaymentMethod, 'cr.payTime' => $paytime, 'cr.payClass' => 'xyk'));
		
		if($orderPay == 1) {
			$orderInfo['order']['ordersPay'] = 1;
			$orderInfo['order']['payTime'] = $paytime;
			self::sendCyberSource($orderInfo, $params_all);
		}
	
	}
	
	public static function sendCyberSource($orderInfo, $params_all) {
		
		$order = $orderInfo['order'];
		
		$memberContactAll = explode("|", $order['consigneeName']);
		$memberContactAddrALL = explode('|', $order['consigneeAddr']);
		
		$billingAll = explode("|", $order['billingName']);
		$billingAddrAll = explode('|', $order['billingAddr']);
		
		$request = new \stdClass();
		
		$request->merchantReferenceCode = $order['ordersCid'];
		
		$request->clientLibrary = "PHP";
		
		$request->clientLibraryVersion = phpversion();
		
		$request->clientEnvironment = php_uname();
		
		$ccAuthService = new \stdClass();
		
		$ccAuthService->run = "true";
		
		//					$request->ccAuthService = $ccAuthService;
		

		$request->afsService = $ccAuthService;
		
		
		$search = array('１', '２', '３', '４', '５', '６', '７', '８', '９', '－', '０');
		$replace = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '-', '0');
		
		$billTo = new \stdClass();
		
		$billTo->firstName = $billingAll[0];
		$billTo->lastName = $billingAll[1];
		$billTo->street1 = $billingAddrAll[0];
		$billTo->street2 = $billingAddrAll[1];
		$billTo->city = $order['billingCtiy'];
		$billTo->postalCode = str_replace($search,$replace,$order['billingPostalcode']);
		
		if(preg_match("#ca|us#si", $order['billingStateFlag'])) {
			foreach (\Helper\State::$StateAll as $Statedata){
				if(strpos(strtoupper($order['billingUrbanAreas']),strtoupper($Statedata['StateName']))!==false){
					$order['billingUrbanAreas']=$Statedata['StateCode'];
					break;
				}
			}
			$billTo->state = $order['billingUrbanAreas'];
		}
		$billTo->country = $order['billingStateFlag'];
		$billTo->email = $order['consigneeEmail'];
		
		$billTo->ipAddress = \Helper\RequestUtil::getClientIp();
		
		$billTo->phoneNumber = str_replace($search,$replace,$order['billingPhone']);
		$request->billTo = $billTo;
		
		$shipTo = new \stdClass();
		$shipTo->city = $order['consigneeCity'];
		$shipTo->country = $order['stateFlag'];
		$shipTo->firstName = $memberContactAll[0];
		$shipTo->lastName = $memberContactAll[1];
		$shipTo->phoneNumber = str_replace($search,$replace,$order['consigneePhone']);
		$shipTo->postalCode = str_replace($search,$replace,$order['consigneePostalcode']);
		
		if(preg_match("#ca|us|ie#si", $order['stateFlag'])) {
			
			foreach (\Helper\State::$StateAll as $Statedata){
				if(strpos(strtoupper($order['consigneeUrbanAreas']),strtoupper($Statedata['StateName']))!==false){
					$order['consigneeUrbanAreas']=$Statedata['StateCode'];
					break;
				}
			}
			$shipTo->state = $order['consigneeUrbanAreas'];
		}
		$shipTo->street1 = $memberContactAddrALL[0];
		$shipTo->street2 = $memberContactAddrALL[1];
		$shipTo->shippingMethod = 'lowcost';
		$request->shipTo = $shipTo;
		
		//信用卡信息
		

		$card = new \stdClass();
		$card->accountNumber = $params_all->cardno;
		$card->expirationMonth = $params_all->expirymonth;
		$card->expirationYear = $params_all->expiryyear;
		$request->card = $card;
		
		$purchaseTotals = new \stdClass();
		$purchaseTotals->currency = $order['currencyCode'];
		$request->purchaseTotals = $purchaseTotals;
		
		$productsCb = array();
		
		$k = 0;
		
		foreach($orderInfo['productList'] as $key) {
			$itemPro = new \stdClass();
			$itemPro->unitPrice = $key['unitPrice'];
			$itemPro->productSKU = $key['skuId'];
			$itemPro->productName = $key['productName'];
			$itemPro->productCode = $key['cid'];
			$itemPro->quantity = $key['buyNum'];
			$itemPro->id = $k;
			$k++;
			$productsCb[] = $itemPro;
		}
		
		$request->item = $productsCb;
		
		$merchantDefinedData = new \stdClass();
		$merchantDefinedData->field1 = $order['logistics'];
		$merchantDefinedData->field2 = $order['logisticsCosts'];
		$merchantDefinedData->field3 = $order['currencyCode'];
		$merchantDefinedData->field4 = $_SERVER['HTTP_HOST'];
		
		$merchantDefinedData->field6 = '';
		if($order['currencyCode'] != 'USD') {
			$merchantDefinedData->field9 = \Lib\common\Language::priceByCurrency($order['amount'] + $order['logisticsCosts'], 'USD', $order['currencyCode']);
		} else {
			$merchantDefinedData->field9 = $order['amount'] + $order['logisticsCosts'];
		}
		
		if(!empty($orderInfo['discountList'])){
			foreach($orderInfo['discountList'] as $discountKey=>$discountVal){
				if($discountVal['couponType']=='1' || $discountVal['couponType']=='2' || $discountVal['couponType']=='3'){
					//表示使用折扣卷
					$couponCode = $discountVal['libkey'];
					$couponAmount = $discountVal['discount_Amount'];
				}
			}
		}
		
		$merchantDefinedData->field10 = !empty($couponCode) ? $couponCode : '';
		$merchantDefinedData->field11 = !empty($couponAmount) ? $couponAmount : '';
		if(defined('APP_TYPE') && APP_TYPE=='wap'){
			$merchantDefinedData->field13 = 'mobile';
		}else{
			$merchantDefinedData->field13 = 'web';
		}
		$merchantDefinedData->field14 = SELLER_LANG;
		
		//		if($order['memberid'] != "") {
		//			
		//			$sql = "	SELECT * FROM `" . TABLE_PREFIX . "member` where `MemberId` = '" . $order['memberid'] . "'";
		//			
		//			$sth = $this->db->Prepare($sql);
		//			
		//			$res = $this->db->Execute($sth);
		//			
		//			$Member = slashes($res->FetchRow());
		//			$merchantDefinedData->field5 = $order['Member']['ConsigneeGender'];
		//			$merchantDefinedData->field8 = date('Y-m-d', $Member['MemberUserTime']);
		//			$merchantDefinedData->field12 = $Member['MemberUserPass'];
		//		}
		

		$merchantDefinedData->field17 = $order['memberId'];
		
		$request->merchantDefinedData = $merchantDefinedData;
		$request->deviceFingerprintID = $_SESSION[SESSION_PREFIX . "ceyberSource"];
		$_SESSION[SESSION_PREFIX . "ceyberSource"] = "";
		
		$reply = \Lib\_3rd\cyberSource\CyberLib::runTransaction($request);
		if($reply->reasonCode==100){
			$paytime = $order['payTime'];
			$shoppingProcess = new \Model\ShoppingProcess();
			$response = $shoppingProcess->updateOrder(array('cr.ordersId' => $order['ordersId'], 'cr.ordersEstate' => 'payConfirm','cr.endTime'=>$paytime + (($order['viewStock']+$order['expressTime']) * 24 * 3600)));
			$shoppingProcess->insertAdminRecord(array('record.ordersId' => $order['ordersId'],'record.action' => '支付确认','record.username' => '系统','record.userip' => '127.0.0.1','record.action_time' => time()));
			self::sendEmail($orderInfo);
		}
		
	}
	
	public static function paymentPaypal($order) {
		
		$item_name = $order['ordersCid'];
		$memberContactAll = explode("|", $order['consigneeName']);
		$memberContactAddrALL = explode('|', $order['consigneeAddr']);
		
		$billingAll = explode("|", $order['billingName']);
		$billingAddrAll = explode('|', $order['billingAddr']);
		$cancel_return = Rewrite::rewrite(array('url' => '?module=shop&action=Achieve&id=' . $order['ordersId'], 'isxs' => 'no'));
		
		$returnUrl = Rewrite::rewrite(array('url' => '?module=shop&action=PaypalReturn', 'isxs' => 'no'));
		$ipnUrl = Rewrite::rewrite(array('url' => '?module=shop&action=PaypalIPN', 'isxs' => 'no'));
		if(SELLER_LANG == "ja-jp") {
			$lc = "jp";
		} elseif(SELLER_LANG == "fr-fr") {
			$lc = "fr";
		} elseif(SELLER_LANG == "es-sp") {
			$lc = "es";
		} elseif(SELLER_LANG == "de-ge") {
			$lc = "de";
		} elseif(SELLER_LANG == "it-it") {
			$lc = "it";
		} else {
			$lc = "us";
		}
		/* 
		$paypalurl = "https://www.paypal.com/cgi-bin/webscr?cmd=_xclick" . "&lc=".urlencode($lc)."&business=" . urlencode("paypal@milanoo.com") . "&undefined_quantity=0" . "&item_name=" . urlencode($item_name) . "&amount=" . urlencode($order['amount']) . "&shipping=" . urlencode($order['logisticsCosts']) . "&custom=" . urlencode($order['ordersId']) . "&invoice=" . urlencode($order['ordersCid']) . "&charset=" . urlencode("utf-8") . "&no_shipping=0" . "&image_url=" . urlencode("https://www.mlo.me/image/default/logo.jpg") . "&notify_url=" . urlencode($ipnUrl) . "&return=" . urlencode($returnUrl) . "&cancel_return=" . urlencode($cancel_return) . "&no_note=1" . "&currency_code=" . urlencode($order['currencyCode']) . "&city=" . urlencode($order['consigneeCity']) . "&state=" . urlencode($order['MemberUrbanAreas']) . "&address_override=0" . "&first_name=" . urlencode($memberContactAll[0]) . "&last_name=" . urlencode($memberContactAll[1]) . "&address1=" . urlencode($memberContactAddrALL[0]) . "&address2=" . urlencode($memberContactAddrALL[1]) . "&zip=" . urlencode($order['consigneePostalcode']) . //need modify
"&country=" . urlencode(strtoupper($order['stateFlag'])) . "&email=" . urlencode($order['consigneeEmail']) . "&night_phone_a=" . urlencode($order['consigneePhone']) . "&night_phone_b=" . "&night_phone_c=" . "&lc=" . urlencode($lc);
		 */
		//去掉国家代码
		$paypalurl = "https://www.paypal.com/cgi-bin/webscr?cmd=_xclick" . "&lc=".urlencode($lc)."&business=" . urlencode("paypal@milanoo.com") . "&undefined_quantity=0" . "&item_name=" . urlencode($item_name) . "&amount=" . urlencode($order['amount']) . "&shipping=" . urlencode($order['logisticsCosts']) . "&custom=" . urlencode($order['ordersId']) . "&invoice=" . urlencode($order['ordersCid']) . "&charset=" . urlencode("utf-8") . "&no_shipping=0" . "&image_url=" . urlencode("https://www.milanoo.com/image/default/logo.jpg") . "&notify_url=" . urlencode($ipnUrl) . "&return=" . urlencode($returnUrl) . "&cancel_return=" . urlencode($cancel_return) . "&no_note=1" . "&currency_code=" . urlencode($order['currencyCode']) . "&city=" . urlencode($order['consigneeCity']) . "&state=" . urlencode($order['MemberUrbanAreas']) . "&address_override=0" . "&first_name=" . urlencode($memberContactAll[0]) . "&last_name=" . urlencode($memberContactAll[1]) . "&address1=" . urlencode($memberContactAddrALL[0]) . "&address2=" . urlencode($memberContactAddrALL[1]) . "&zip=" . urlencode($order['consigneePostalcode']) . //need modify
		"&email=" . urlencode($order['consigneeEmail']) . "&night_phone_a=" . urlencode($order['consigneePhone']) . "&night_phone_b=" . "&night_phone_c=" . "&lc=" . urlencode($lc);
		header("Location:" . $paypalurl);
		exit();
	}
	
	public static function payBankTransfer($orderInfo,$params_all){
		$order = $orderInfo['order'];
		$worldPaySupport = \Config\PaymentMethod::$bankTransfer['worldPay'];
		$countryCode = $params_all->worldPay_country;
		$currenyCode = $worldPaySupport[$countryCode]['curreny'];//对应支持货币
		
		$memberContactAll = explode("|", $order['consigneeName']);
		$memberContactAddrALL = explode('|', $order['consigneeAddr']);
		
		$billingAll = explode("|", $order['billingName']);
		$billingAddrAll = explode('|', $order['billingAddr']);
		
		if($order['billingGender']==1) $mr = 'Mr.';
		if($order['billingGender']==2) $mr = 'Mrs.';
		
		$country = new \Model\CountryList ();
		$countryList = $country->getCountryList ( array ('cr.lang' => SELLER_LANG ) );
		$countryBillingName = $countryList ['counties'][$order['ordersBillingStateId']];//用户billing地址所在国家名字
		$countryShippingName = $countryList ['counties'][$order['stateId']];//用户shipping地址所在国家名字
		
		
		$session_id = session_id();
		$ip = \Helper\RequestUtil::getClientIp();
		//购物信息
		$shopperArray = array(
				"email" => $order['consigneeEmail'], //用户联系邮箱
				"firstname" => $memberContactAll[0], "lastname" => $memberContactAll[1], //用户姓名
				"street" => $memberContactAddrALL[0], //用户地址
				"postalcode" => $order['consigneePostalcode'], //用户邮编
				"city" => $order['consigneeCity'], //联系城市
				"telephone" => $order['consigneePhone'], //用户电话
				"countrycode" => strtoupper($order['stateFlag']),//国家代码
		); 
		
		//将用户订单中的货币总价转换成对应支持的货币总价
		$order['amount'] = \Lib\common\Language::priceByCurrency($order['amount'],$currenyCode,$order['currencyCode']);
		$order['logisticsCosts'] = \Lib\common\Language::priceByCurrency($order['logisticsCosts'],$currenyCode,$order['currencyCode']);
		$total = $order['amount']+$order['logisticsCosts'];
		$amoundDisplay = $order['amount'] * 100;
		$logisticsCostsDisplay = $order['logisticsCosts'] * 100;
		$totalammount = $amoundDisplay + $logisticsCostsDisplay;
		$currenyName = \config\Currency::$currencyTranslations[$currenyCode]['name']['en-uk'];//取得用户支付的货币种类名字
		
		$lang = \Langpack::$items;
		$orderContent = <<<EOT
		<center><table>
		<tr><td bgcolor='#ffff00'>{$lang['shop_OrderNumber']}:</td><td colspan='2' bgcolor='#ffff00' align='right'>{$order['ordersCid']}</td></tr>
		<tr><td colspan="2">{$lang['shop_Order_Subtotal']}:</td><td align="right">{$order['amount']}</td></tr>
		<tr><td colspan="2">{$lang['cart_freight']}:</td><td align="right">{$order['logisticsCosts']}</td></tr>
		<tr><td colspan="2" bgcolor="#c0c0c0">{$lang['thing_Item_Total']}:</td><td bgcolor="#c0c0c0" align="right">{$currenyName} {$total}</td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr><td bgcolor="#ffff00" colspan="3">{$lang['order_billing_address']}:</td></tr>
		<tr><td colspan="3">{$mr}{$billingAll[1]},<br>{$billingAddrAll},<br>{$order['billingCtiy']} {$order['billingUrbanAreas']},<br>{$countryBillingName}</td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr><td bgcolor="#ffff00" colspan="3">{$lang['order_shipping_address']}:</td></tr>
		<tr><td colspan="3">{$mr}{$memberContactAll[1]},<br>{$memberContactAddrALL[0]},<br>{$order['consigneeCity']} {$order['consigneeUrbanAreas']},<br>{$countryShippingName}</td></tr>
		</table></center>
EOT;
		
		$worldPay = new \Lib\_3rd\worldpay\BankTransfer();
		//$worldPay->BankTransferstart(true);//测试
		$worldPay->BankTransferstart(false);
		
		//orderId增加特殊字符以避免多次支付造成单号重复提交失败
		$specialString = substr(time(),-4);

		$worldPay->orderId = $order['ordersId'].'_'.$specialString;
		
		//测试
		$worldPay->totalammount = $totalammount;
		$worldPay->description = $order['ordersCid'];
		$Cid = str_replace('-', '=', $order['ordersCid']);
		$worldPay->currencyCode = $currenyCode;//强制使用银行支持货币
		
		
		$worldPay->StartXML();
		$worldPay->FillDataXML($orderContent);
		$worldPay->FillBankXml($countryCode);
		$worldPay->FillShopperXML($shopperArray);
		$worldPay->EndXML();
		
		//$worldPay->xml = utf8_encode($worldPay->xml);
		$bankResult = $worldPay->CreateConnection();

		$xmlFormat = new \Lib\_3rd\worldpay\BibitFormat();
		$xmlFormat->ParseXML($bankResult);
		$reuturnUrl= $xmlFormat->ReadXml($bankResult, "reference");
		
		$shoppingProcess = new \Model\ShoppingProcess();
		
		if(strpos($reuturnUrl, 'https://')==0){
			//返回正确连接
			$orderCode = $xmlFormat->ReadXml($bankResult, "orderStatus","orderCode");//订单号
			$paymentReference = $xmlFormat->ReadXml($bankResult, "reference","id");//第三方单号
			
			$orderCodeArray = explode('_',$orderCode);
			if(!empty($orderCodeArray) && isset($orderCodeArray[0])){
				$orderCode = $orderCodeArray[0];
			}
			if($orderCode == $order['ordersId'] && !empty($paymentReference)){
				$ordersPayDetails = "Payment:yhhk|CurrencyCode:{$currenyCode}|amount:{$total}|Remarks:{$paymentReference}|time:".time();
				//订单号正确。更新订单
				$response = $shoppingProcess->updateOrder(array('cr.ordersId' => $order['ordersId'], 'cr.ordersPayDetails' => $ordersPayDetails,'cr.pamentToken'=>$paymentReference,'cr.cardType' => "TRANSFER_{$countryCode}-BANK",'cr.payClass' => 'yhhk'));
				$orderInfo['order']['pamentToken'] =  $paymentReference;
				$orderInfo['order']['cardType'] = "TRANSFER_{$countryCode}-BANK";
				$orderInfo['order']['ordersPayDetails'] = $ordersPayDetails;
				$orderInfo['order']['payClass'] = 'yhhk';
				//发送下单成功邮件（邮件里包含worldpay返回信息）
				self::sendEmail($orderInfo);
				
				$cancelURL = urlencode(\Helper\ResponseUtil::rewrite(array('url'=>'?module=shop&action=Achieve&id='.$order['ordersId'],'isxs' => 'no')));
				$successURL = urlencode(\Helper\ResponseUtil::rewrite(array('url'=>'?module=shop&action=Achieve&id='.$order['ordersId'],'isxs' => 'no')));
				
				if(LangDirName=='jp'){
					$language = 'ja';
				}else{
					$language = LangDirName;
				}
				
				$urlParam = "&preferredPaymentMethod=TRANSFER_{$countryCode}-BANK&country={$countryCode}&language=".$language.'&successURL='.$successURL.'&cancelURL='.$cancelURL;
				//$reuturnUrl .= $urlParam;
				$reuturnUrl = \Helper\ResponseUtil::rewrite(array('url'=>'?module=shop&action=Achieve&id='.$order['ordersId'],'isxs' => 'no'));
				header("Location:" . $reuturnUrl);
				exit;
			}else{
				//返回XML写入临时文件
				if(!empty($bankResult)){
					$data = array('orderCid'=>$order['ordersCid'],'orderId'=>$worldPay->orderId,'addTime'=>date('Y-m-d H:i:s',time()));
					$filename = ROOT_PATH.'/data/log/'.(date('Ym')).'_wpYhhk.log';
					$handle = fopen($filename, 'a');
					fwrite($handle, "\n\n-------发送xml-----\n\n");
					fwrite($handle, $worldPay->xml."\n");
					fwrite($handle, "\n-------回传xml-----\n\n");
					fwrite($handle, $bankResult."\n");
					fwrite($handle, var_export($data,true)."\n------END\n\n");
					fclose($handle);
				}
				$msg = \Helper\String::strDosTrip($lang['pay_wrong_notice']);
				\Helper\ErrorTip::setError($msg);
				////更新订单,支付错误
				//$response = $shoppingProcess->updateOrder(array('cr.ordersId' => $order['ordersId'], 'cr.ordersPayDetails' => 'payWrong','cr.cardType' => "TRANSFER_{$countryCode}-BANK"));
								
				//返回错误，重新支付
				header("Location:" . \Helper\ResponseUtil::rewrite(array('url'=>'?module=shop&action=Payment&id='.$order['ordersId'],'isxs' => 'no')));
				exit;
			}
		}else{
			//返回XML写入临时文件
			if(!empty($bankResult)){
				$data = array('orderCid'=>$order['ordersCid'],'orderId'=>$worldPay->orderId,'addTime'=>date('Y-m-d H:i:s',time()));
				$filename = ROOT_PATH.'/data/log/'.(date('Ym')).'_wpYhhk.log';
				$handle = fopen($filename, 'a');
				fwrite($handle, "\n\n-------发送xml-----\n\n");
				fwrite($handle, $worldPay->xml."\n");
				fwrite($handle, "\n-------回传xml-----\n\n");
				fwrite($handle, $bankResult."\n");
				fwrite($handle, var_export($data,true)."\n------END\n\n");
				fclose($handle);
			}
			$msg = \Helper\String::strDosTrip($lang['pay_wrong_notice']);
			\Helper\ErrorTip::setError($msg);
			//更新订单,支付错误
			//$response = $shoppingProcess->updateOrder(array('cr.ordersId' => $order['ordersId'], 'cr.ordersPayDetails' => 'payWrong','cr.cardType' => "TRANSFER_{$countryCode}-BANK"));

			//返回错误，重新支付
			header("Location:" . \Helper\ResponseUtil::rewrite(array('url'=>'?module=shop&action=Payment&id='.$order['ordersId'],'isxs' => 'no')));
			exit;
		}
	}

}