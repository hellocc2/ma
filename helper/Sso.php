<?php
namespace Helper;
class  Sso {

	public static function client()
	{
		include_once ROOT_PATH . 'lib/cas/CAS.php';
		
		//error_reporting(E_ALL);
		//ini_set("display_errors", 1);
		
		$cas_host='192.168.11.16';
		$cas_port=8580;
		//$cas_host='192.168.6.49';
		//$cas_port=8080;
		$cas_context='cas';
		
		$phpCAS = new \phpCAS();
		//$phpCAS->setDebug();
		$phpCAS->client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
		$phpCAS->setNoCasServerValidation();
		$phpCAS->handleLogoutRequests();
		$phpCAS->forceAuthentication();
		
		if (isset($_REQUEST['menu_action'])&&$_REQUEST['menu_action']=='logout') {
			$phpCAS->logout();
		}
		
		return $client=$phpCAS->getAttributes();
	}	
}