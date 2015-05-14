<?php
namespace Lib\common;
class Db {
	/**
	 * 数据库类
	 * @return string
	 */
	public static function get_db($config = 'default') {
		global $db_global;
		if ($db_global && $db_global->databaseName) {
			return $db_global;
		}
		$db_global_config = \config\Db::$$config;
		$db_global_connect = new \config\Db ();
		
		include_once ROOT_PATH . 'lib/adodb/adodb.inc.php';
		$db_global = &ADONewConnection ( $db_global_config["driver"] );
		//echo $config;$db_global->debug = 1;
		ini_set($db_global_config["driver"].'.default_port', $db_global_config["port"]);
		$db_global->Connect ( $db_global_config["host"], $db_global_config["dbuser"], $db_global_config["dbpassword"], $db_global_config["dbname"] );
		$ADODB_CACHE_DIR = ROOT_PATH . "data/db";
		$db_global->query ( 'SET NAMES UTF8' );
		$db_global->SetFetchMode ( 2 );
		//这只缓存时间
		if (! defined ( 'CacheTime' ))
			define ( 'CacheTime', 600 );
		if (! $db_global) {
			die ( '不能连接数据库.\n' );
		}
		//print_r($db_global);
		$GLOBALS ['db'] = $db_global;
		return $db_global;
	}
}