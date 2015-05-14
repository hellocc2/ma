<?php
namespace Module\Shop;

use helper\ResponseUtil as Rewrite;
/**
 * 信用卡付款通知
 * @Jiang Lin<jianglin@mialnoo.com>
 *
 */
class CreditCardIPN extends \Lib\common\Application {
	
function __construct() {
		$responseRequest = $this->soaputils_autoFindSoapRequest();
		$data = $this->xml2array($responseRequest, 1);
		
		if(!empty($data['paymentService']['notify']['orderStatusEvent']) && !empty($data['paymentService_attr']) && $data['paymentService_attr']['merchantCode']=='MILANOOUSD'){
			$orderId = isset($data['paymentService']['notify']['orderStatusEvent_attr']) ? $data['paymentService']['notify']['orderStatusEvent_attr']['orderCode'] : '';
			$payment = isset($data['paymentService']['notify']['orderStatusEvent']['payment']) ? $data['paymentService']['notify']['orderStatusEvent']['payment']['paymentMethod']: '';
			$payStatus = isset($data['paymentService']['notify']['orderStatusEvent']['payment']) ? $data['paymentService']['notify']['orderStatusEvent']['payment']['lastEvent']: '';
			if(!empty($data['paymentService']['notify']['orderStatusEvent']['payment']['amount_attr'])){
				$totalAmount = isset($data['paymentService']['notify']['orderStatusEvent']['payment']['amount_attr']) ? $data['paymentService']['notify']['orderStatusEvent']['payment']['amount_attr']['value']: '';
				$currency = isset($data['paymentService']['notify']['orderStatusEvent']['payment']['amount_attr']) ? $data['paymentService']['notify']['orderStatusEvent']['payment']['amount_attr']['currencyCode']: '';
				$exponent = isset($data['paymentService']['notify']['orderStatusEvent']['payment']['amount_attr']) ? $data['paymentService']['notify']['orderStatusEvent']['payment']['amount_attr']['exponent']: '0';
			}
			if(!empty($data['paymentService']['notify']['orderStatusEvent']['journal']['bookingDate']['date_attr'])){
				$payDate = $data['paymentService']['notify']['orderStatusEvent']['journal']['bookingDate']['date_attr']['year'] . '-' . $data['paymentService']['notify']['orderStatusEvent']['journal']['bookingDate']['date_attr']['month'] . '-' . $data['paymentService']['notify']['orderStatusEvent']['journal']['bookingDate']['date_attr']['dayOfMonth'];
			}
			if(strpos($payment,'TRANSFER_')!==false){
				//表示WP银行汇款
				if(!empty($orderId) && $payStatus==='AUTHORISED'){
					$orderCodeArray = explode('_',$orderId);
					if(!empty($orderCodeArray) && isset($orderCodeArray[0])){
						$orderId = $orderCodeArray[0];
					}
					if(!empty($totalAmount)){
						//生成实际支付价格
						$sub = pow(10,$exponent);
						$totalAmount = $totalAmount/$sub;
						
						$orderInfoM = new \Model\ShoppingProcess();
						$orderInfo = $orderInfoM->GetOrderById(array('cr.ordersId' => $orderId, 'cr.lang' => SELLER_LANG));
						//已支付单不再重复确认
						if(!empty($orderInfo) && $orderInfo['code']==0 && $orderInfo['orderInfo']['order']['ordersPay'] == 0 && $orderInfo['orderInfo']['order']['ordersEstate'] == 'UnderOrders'){
							$ordersCid = $orderInfo['orderInfo']['order']['ordersCid'];
							$lang = strtolower(substr(trim($ordersCid), 0, 5));
							$orderDetails = $orderInfo['orderInfo']['order']['ordersPayDetails'];
							$orderDetailsArray = explode('|',$orderInfo['orderInfo']['order']['ordersPayDetails']);
							$orderFromIdInfo = array();
							foreach($orderDetailsArray as $k=>$v){
								if(!empty($v)){
									$temp = explode(':',$v);
									if(!empty($v)){
										$orderFromIdInfo[$temp[0]] = $temp[1];
									}
								}
							}
							if(isset($orderFromIdInfo['Payment']) && $orderFromIdInfo['Payment']=='yhhk'){
								if(isset($orderFromIdInfo['CurrencyCode']) && $orderFromIdInfo['CurrencyCode']==$currency){
									if(isset($orderFromIdInfo['amount']) && $orderFromIdInfo['amount']==$totalAmount){
										//验证通过
										$paytime = isset($payDate) ? strtotime($payDate) : time();
										$ordersPayDetails = "Payment:{$orderFromIdInfo['Payment']}|CurrencyCode:{$orderFromIdInfo['CurrencyCode']}|amount:{$orderFromIdInfo['amount']}|Remarks:{$orderFromIdInfo['Remarks']}|time:".$paytime;
										//更新订单支付状态为已支付,同时修改订单状态
										//todo 暂时没有写手续费进来，等汇率问题解决后再写	`
										$orderInfoM->updateOrder(array('cr.ordersCid' => $ordersCid, 'cr.ordersPay' => 1, 'cr.ordersPayDetails' => $ordersPayDetails,'cr.ordersEstate' => 'payConfirm', 'cr.payTime' => $paytime,'cr.endTime'=>$paytime + (($orderInfo['orderInfo']['order']['viewStock']+$orderInfo['orderInfo']['order']['expressTime']) * 24 * 3600)));
										//插入支付日志
										$orderInfoM->insertAdminRecord(array('record.ordersId' => $orderId, 'record.action' => '支付确认', 'record.username' => '系统', 'record.userip' => '127.0.0.1', 'record.action_time' => time()));
										//发送支付确认邮件,重新获取对应语言的修改状态后的订单信息
										if(SELLER_LANG != $lang){
											$shoppingProcess = new \Model\ShoppingProcess();
											$orderInfo = $shoppingProcess->GetOrderById(array('cr.ordersId' => $orderId, 'cr.lang' => $lang));
										}
										$orderInfo['orderInfo']['order']['ordersPay'] = 1;
										$orderInfo['orderInfo']['order']['ordersEstate'] = 'payConfirm';
										$orderInfo['orderInfo']['order']['payTime'] = $paytime;
										
										$orderInfoAfterUpdate = $orderInfo['orderInfo'];
										$emailAll = array('lang' => $lang, 'email' => $orderInfoAfterUpdate['order']['consigneeEmail'], 'products' => $orderInfoAfterUpdate['productList'], 'Orders' => $orderInfoAfterUpdate, 'emailtitle' => 'Email_CKOK', 'theme' => THEME . 'default/email/order_achieve.htm');
										\Helper\Stomp::SendEmail($emailAll);
									}
								}
							}
						}
					}
				}
			}else{
				//表示信用卡
				//信用卡继续写入文件
				$filename = ROOT_PATH.'/data/log/'.(date('Ym')).'_xyk.log';
				$handle = fopen($filename, a);
				fwrite($handle, $responseRequest."\n");
				fwrite($handle, var_export($data,true)."\n------END\n\n");
				fclose($handle);
			}
			echo '[OK]';exit;
		}
	}
	
	function soaputils_autoFindSoapRequest() {
		global $HTTP_RAW_POST_DATA;
		
		if($HTTP_RAW_POST_DATA)
			return $HTTP_RAW_POST_DATA;
		
		$f = file("php://input");
		return implode(" ", $f);
	
		//return $f;
	}
	
	function xml2array($contents, $get_attributes = 1, $priority = 'tag') {
		if(!$contents)
			return array();
		
		if(!function_exists('xml_parser_create')) {
			//print "'xml_parser_create()' function not found!";
			return array();
		}
		
		//Get the XML parser of PHP - PHP must have this module for the parser to work
		$parser = xml_parser_create('');
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);
		
		if(!$xml_values)
			return; //Hmm...
		

		//Initializations
		$xml_array = array();
		$parents = array();
		$opened_tags = array();
		$arr = array();
		
		$current = &$xml_array; //Refference
		

		//Go through the tags.
		$repeated_tag_index = array(); //Multiple tags with same name will be turned into an array
		foreach($xml_values as $data) {
			unset($attributes, $value); //Remove existing values, or there will be trouble
			

			//This command will extract these variables into the foreach scope
			// tag(string), type(string), level(int), attributes(array).
			extract($data); //We could use the array by itself, but this cooler.
			

			$result = array();
			$attributes_data = array();
			
			if(isset($value)) {
				if($priority == 'tag')
					$result = $value;
				else
					$result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
			}
			
			//Set the attributes too.
			if(isset($attributes) and $get_attributes) {
				foreach($attributes as $attr => $val) {
					if($priority == 'tag')
						$attributes_data[$attr] = $val;
					else
						$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}
			
			//See tag status and do the needed.
			if($type == "open") { //The starting of the tag '<tag>'
				$parent[$level - 1] = &$current;
				if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
					$current[$tag] = $result;
					if($attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					
					$current = &$current[$tag];
				
				} else { //There was another element with the same tag name
					

					if(isset($current[$tag][0])) { //If there is a 0th element it is already an array
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						$repeated_tag_index[$tag . '_' . $level]++;
					} else { //This section will make the value an array if multiple tags with the same name appear together
						$current[$tag] = array($current[$tag], $result); //This will combine the existing item and the new item together to make an array
						$repeated_tag_index[$tag . '_' . $level] = 2;
						
						if(isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset($current[$tag . '_attr']);
						}
					
					}
					$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
					$current = &$current[$tag][$last_item_index];
				}
			
			} elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
				//See if the key is already taken.
				if(!isset($current[$tag])) { //New Key
					$current[$tag] = $result;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if($priority == 'tag' and $attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
				
				} else { //If taken, put all things inside a list(array)
					if(isset($current[$tag][0]) and is_array($current[$tag])) { //If it is already an array...
						

						// ...push the new element into that array.
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						
						if($priority == 'tag' and $get_attributes and $attributes_data) {
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag . '_' . $level]++;
					
					} else { //If it is not an array...
						$current[$tag] = array($current[$tag], $result); //...Make it an array using using the existing value and the new value
						$repeated_tag_index[$tag . '_' . $level] = 1;
						if($priority == 'tag' and $get_attributes) {
							if(isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
								

								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset($current[$tag . '_attr']);
							}
							
							if($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
					}
				}
			
			} elseif($type == 'close') { //End of tag '</tag>'
				$current = &$parent[$level - 1];
			}
		}
		
		return ($xml_array);
	}
}