<?php
namespace helper;
/**
 * 订单处理
 * @author huchuande
 *
 */
class Orders {
	//$memberid="1031267";
	//$orderid="895885";
	//$OrdersPriority="2";
	//autoOrderApproved($memberid,$orderid,$OrdersPriority);
	
	/**
	 * IPN 支付确认后，调用这个函数模似登录后台进行自动审单操作！
	 * @param int $memberid 用户ID
	 * @param int $orderid 订单ID
	 * @param int $OrdersPriority 订单优先级
	*/
	function autoOrderApproved($memberid=0,$orderid=0,$OrdersPriority=2)
	{
		if(!$memberid || !$orderid)
		{
			return false;
		}
		$url_login = MILANOO_HT."milanooht/index.php";
		$url_auto_order = MILANOO_HT."milanooht/index.php?module_id=73&module_action=action&menu_action=Management&id={$orderid}&mid={$memberid}&ManagementAct=post&followid=739&OrdersPriority={$OrdersPriority}";
		$ch = curl_init ();
		//临时目录文件存储会话信息huchuande
		$cookie_file = tempnam ( '/tmp', 'cookie'.$orderid );
		$UserAgent='Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/2008052906 auto_order';
	
		// print_r($url);exit;
		//这个参考后台的用户和密码
		$post_login['username']="系统";
		$post_login['userpass']="123456b";
		curl_setopt_array ( $ch, array (
		CURLOPT_URL => $url_login,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 10 ,
		CURLOPT_USERAGENT=>$UserAgent,
		CURLOPT_REFERER=>"http://www.milanoo.com/".$_SERVER['REQUEST_URI'],
		CURLOPT_POSTFIELDS=>$post_login,
		CURLOPT_COOKIEJAR=>$cookie_file,
		CURLOPT_COOKIEFILE=>$cookie_file,
		//CURLOPT_COOKIESESSION=> true
		));
		$response = curl_exec ( $ch );
		curl_close ( $ch );
		$ch1 = curl_init ();
		curl_setopt_array ( $ch1, array (
		CURLOPT_URL => $url_auto_order,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 10 ,
		CURLOPT_USERAGENT=>$UserAgent,
		CURLOPT_REFERER=>"http://www.milanoo.com/".$_SERVER['REQUEST_URI'],
		CURLOPT_COOKIEJAR=>$cookie_file,
		CURLOPT_COOKIEFILE=>$cookie_file,
		//CURLOPT_COOKIESESSION=> true
		));
		$response = curl_exec ( $ch1 );
	
		$info=curl_getinfo($ch1);
		//print_R($info);
		curl_close ( $ch1 );
		unlink($cookie_file);
		//print_r ( $response );
	}
}

?>