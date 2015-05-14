<?php
namespace Module\inquiry;
use Helper\RequestUtil;
use Helper\Js;
use Helper\String;

/**
 * 
 * 商品详细页提交咨询(批发)：AdvisoryType为Product即为咨询，否则为批发。
 * 接口：inquiry(java接口)、	CRM(CRM接口)
 * 调用inquiry接口把数据提交到JAVA，成功写入数据库后，再提交到CRM。如果CRM提交失败，调用JAVA接口更新刚才提交的咨询为失效。
 * @author hung
 *
 */

class Product extends \Lib\common\Application {
	
	public function __construct() {
		
		
		//返回地址
		$redirectURL = $_SESSION ['b2cbast_url'];
		unset($_SESSION ['b2cbast_url']);
		//咨询方式:Product,Wholesale
		$AdvisoryType = RequestUtil::getParams ( 'AdvisoryType' );
		$AdvisoryType = trim ( String::dhtmlspecialchars ( $AdvisoryType ) );
		
		//topic 咨询类型ID 
		$inquiry_data ['inquiryTypeId'] = RequestUtil::getParams ( 'inquiryTypeId' );
		$inquiry_data ['inquiryTypeId'] = trim ( String::dhtmlspecialchars ( $inquiry_data ['inquiryTypeId'] ) );
		
		//检查验证码
		$defaultTyep = array ('Product', 'Wholesale' );
		
		if ($AdvisoryType == 'Wholesale') {
			//批发验证码
			$detail_code = RequestUtil::getParams ( 'detail_wholesale_code' );
			$detail_code ['productId'] = trim ( String::dhtmlspecialchars ( $detail_code ) );
			$verificationCode = $_SESSION ['captcha'] ['detail_wholesale'];
			unset($_SESSION ['captcha'] ['detail_wholesale']);
			//topic，后台ID
			$inquiry_data ['inquiryTypeId'] = 35;
		} else {
			//customber service验证码
			$detail_code = RequestUtil::getParams ( 'item_service_code' );
			$detail_code = trim ( String::dhtmlspecialchars ( $detail_code ) );
			$verificationCode = $_SESSION ['captcha'] ['item_service'];
			unset($_SESSION ['captcha'] ['item_service']);
		}
		if ( $detail_code != $verificationCode) {
			Js::alertForward ( 'My_NewGuests_input_code_error', $redirectURL );
		}


		//商品ID
		$inquiry_data ['productId'] = RequestUtil::getParams ( 'productId' );
		$inquiry_data ['productId'] = trim ( String::dhtmlspecialchars ( $inquiry_data ['productId'] ) );
		
		//空商品
		if (empty ( $inquiry_data ['productId'] )) {
			Js::alertForward ( 'no_inquiry_product', $redirectURL );
		}
		
		//用户id
		$inquiry_data ['memberId'] = isset($_SESSION [SESSION_PREFIX . 'MemberId'])?$_SESSION [SESSION_PREFIX . 'MemberId']:'';
		
		//用户名称
		$inquiry_data ['memberName'] = RequestUtil::getParams ( 'memberName' );
		$inquiry_data ['memberName'] = trim ( String::dhtmlspecialchars ( $inquiry_data ['memberName'] ) );
		//空名
		if (empty ( $inquiry_data ['memberName'] )) {
			Js::alertForward ( 'noMemberContact',$redirectURL );
		}
		//用户邮件
		$inquiry_data ['memberEmail'] = RequestUtil::getParams ( 'memberEmail' );
		$inquiry_data ['memberEmail'] = trim ( String::dhtmlspecialchars ( $inquiry_data ['memberEmail'] ) );
		//空mail
		if (empty ( $inquiry_data ['memberEmail'] )) {
			Js::alertForward ( 'email1',$redirectURL );
		}
		//邮件格错误
		if (! \Helper\Verification::isemail ( $inquiry_data ['memberEmail'] )) {
			Js::alertForward ( 'email1',$redirectURL );
		}
		//咨询内容
		$inquiry_data ['inquiryContent'] = RequestUtil::getParams ( 'inquiryContent' );
		$inquiry_data ['inquiryContent'] = trim ( String::dhtmlspecialchars ( $inquiry_data ['inquiryContent'] ) );
		//空咨询内容
		if (empty ( $inquiry_data ['inquiryContent'] )) {
			Js::alertForward ( 'noContent',$redirectURL );
		}
		
		//咨询类别 advisory:咨询(售前)，Complaints:投诉(售后),reply:回复
		$inquiry_data ['inquiryCategory'] = 'advisory';
		//站点语言
		$inquiry_data ['languageCode'] = SELLER_LANG;
		//标记 web:1,wap:2,app:3
		$inquiry_data ['marking'] = 1;
		
		
		//提交 到JAVA写入库
		$InquiryModel = new \Model\Inquiry ();
		$result = $InquiryModel->InsertData ( $inquiry_data );

		//成功返回咨询ID，提交到CRM
		if (empty ( $result ['code'] )) {
			$crm_flag = FALSE;
			$result = String::slashes($result);
			$InsertData = array_merge ( $inquiry_data, $result );
			
			//CRM的source类型：Wholesale,Product
			$InsertData ['crmType'] = $AdvisoryType;
			//增加咨询URL
			$inquiryUrl = RequestUtil::getParams ( 'inquiryUrl' );
			$InsertData ['inquiryContent'] .= "\n\n" . trim ( String::dhtmlspecialchars ( $inquiryUrl ) );
			$CrmApi = new \Api\Crm ();
			$crm_flag = $CrmApi->AskToSaleForce ($InsertData);
			//CRM写入数据失败，CRM咨询状态写为无效
			if ($crm_flag == false) {
				$update_data = array ();
				$update_data ['saleforceStatus'] = 0;
				$update_data ['inquiryCaseId'] = $result ['inquiryCaseId'];
				$result = $InquiryModel->UpdateData ( $update_data );
			}
			unset($InsertData,$inquiry_data);
			Js::alertForward ( 'advisoryOk',$redirectURL,1 );
			
		}
		
		Js::alertForward ( 'submit_failed',$redirectURL,1 );

	}

}


