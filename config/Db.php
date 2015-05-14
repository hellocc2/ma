<?php
namespace config;
use PDO;
class Db{
	public static $default=array('driver'=>'mysql',
						  'host'=>'192.168.11.23',
						  'port'=>3306,
						  'dbname'=>'web_statis',
						  'dbuser'=>'bi',
						  'dbpassword'=>'bi',
						  'driveroptions'=>array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES \'UTF8\'')
	
	);
	public static $products=array('driver'=>'mysql',
						  'host'=>'192.168.11.28',
						  'port'=>3306,
						  'dbname'=>'milanoo_gaea',
						  'dbuser'=>'dev',
						  'dbpassword'=>'devpass',
						  'driveroptions'=>array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES \'UTF8\'')
	
	);
	public static $bi=array('driver'=>'mysql',
							  'host'=>'172.20.100.12',
							  'port'=>3307,
							  'dbname'=>'web_statis',
							  'dbuser'=>'bi',
							  'dbpassword'=>'bi',
							  'driveroptions'=>array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES \'UTF8\'')
	
	);
	public static $milanoo=array('driver'=>'mysql',
							  'host'=>'192.168.11.28',
							  'port'=>3306,
							  'dbname'=>'milanoo',
							  'dbuser'=>'dev',
							  'dbpassword'=>'devpass',
							  'driveroptions'=>array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES \'UTF8\'')
		
	);
}