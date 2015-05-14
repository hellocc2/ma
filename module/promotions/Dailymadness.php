<?php
namespace Module\Promotions;
use \Model\Feature;
use Helper\RequestUtil as RequestUtil;
use Helper\ResponseUtil as ResponseUtil;
use Helper\String as HString;
use Helper\Js as JS;
use \Lib\common\Language;
/**
 * 每日抢购处理类
 * @author JerryYang<yang.tao.php@gmail.com>
 * 
 */
class Dailymadness extends \Lib\common\Application {
	public function __construct() {
		
		$tpl = \Lib\common\Template::getSmarty ();
		$tpl->assign('ExchangeCurrency',$tpl->_tpl_vars['Currency']);
		
		$params = RequestUtil::getParams ('params');
		$tpl->assign('requestParams',$params);		
		//======================================
		//每日抢购
		//此修改永久有效。
	
		if(SELLER_LANG == 'en-uk'){
			$id = '106';
		}elseif (SELLER_LANG == 'ja-jp') {
			$id = '113';
		} elseif (SELLER_LANG == 'fr-fr') {
			$id = '244';
		} elseif (SELLER_LANG == 'es-sp') {
			$id = '351';
		} elseif (SELLER_LANG == 'de-ge') {
			$id = '385';
		} elseif (SELLER_LANG == 'it-it') {
			$id = '431';
		}elseif (SELLER_LANG == 'ru-ru') {
			$id = '486';
		}elseif(SELLER_LANG == 'pt-pt'){
			$id = '114571';
		}
		
		//end
		//======================================
		
		$tpl->assign('featureid',$id);
		$special=new \Model\Feature();
		$featureInfo=$special->getFeatureInfoById($id);
		     // echo '<pre>';
		//unset($featureInfo[featureInfo]['products']);
		// print_r($featureInfo);
		// die;     
		
		$specials_array=$featureInfo['featureInfo'];
		//print_r($specials_array);
		//die;
		if(!is_array($specials_array))JS::alertForward('noOrder', '', '1');
		/*倒计时===========*/
		if (!empty($specials_array ['endtime'])) {
				
			if (SELLER_LANG == 'en-uk') {
				$endclock = $specials_array ['endtime'] - time ();
			} elseif (SELLER_LANG == 'ja-jp') {
				$endclock = $specials_array ['endtime'] - time () - 3 * 3600;
			} elseif (SELLER_LANG == 'fr-fr') {
				$endclock = $specials_array ['endtime'] - time ();
			} elseif (SELLER_LANG == 'es-sp') {
				$endclock = $specials_array ['endtime'] - time ();
			} elseif (SELLER_LANG == 'de-ge') {
				$endclock = $specials_array ['endtime'] - time ();
			} elseif (SELLER_LANG == 'it-it') {
				$endclock = $specials_array ['endtime'] - time ();
			}
			elseif (SELLER_LANG == 'ru-ru') {
				$endclock = $specials_array ['endtime'] - time ();
			}
			elseif (SELLER_LANG == 'cn-cn') {
				$endclock = $specials_array ['endtime'] - time ();
			}
			elseif (SELLER_LANG == 'zh-hk') {
				$endclock = $specials_array ['endtime'] - time ();
			}
			elseif (SELLER_LANG == 'ar-ar') {
				$endclock = $specials_array ['endtime'] - time ();
			}
			elseif (SELLER_LANG == 'pt-pt') {
				$endclock = $specials_array ['endtime'] - time ();
			}
				
			$end_time = strtotime((date("Ymd H:i:s",$specials_array ['endtime'])))- strtotime((date("Ymd H:i:s",time())));
			//判断活动是否开始
			if($specials_array ['starttime'] != 'NULL'){
				if($specials_array ['starttime'] > strtotime((date("Ymd H:i:s",time())))){
					//活动还没开始
					$tpl->assign('nostart',1);
					$end_time = 0;
				}else{
					//已经开始
					$tpl->assign('nostart',0);
				}
			}
			if($end_time<0) $end_time = 0;
			$tpl->assign ( 'endclock', $end_time );
			$endclock > 0 ? $tpl->assign ( 'libkey_clock', 1 ) : $tpl->assign ( 'libkey_clock', 0 );
		} else {
			$tpl->assign ( 'libkey_clock', 0 );
		}
		/*倒计时===========End*/
		
		
		
		$tpl->assign( 'specials', $specials_array );
		$tpl->assign( 'specail_title', 'yes' );
		
		$parameters=$specials_array['parameters'];
		//print_r($parameters);
		$parameters_ALL=array();
		if(!empty($parameters)){
			$parameters=explode("|||",$parameters);
			foreach($parameters as $parameters_item){
				$parameters_item_a=explode(":",$parameters_item);
				$parameters_ALL[]=$parameters_item_a;
			}
		}
		$tpl->assign( 'parameters_ALL', $parameters_ALL );
		
		
		//处理专题产品信息(切图型专题不用调用产品id信息)	
		if($specials_array['isTakeTemp']!=1 && !empty($specials_array['products'])){
			$t=$i=0;
			foreach($specials_array['products'] as $product){
				
				$new_product='';
				$new_product['id']=$product['productId'];
				$new_product['ProductsPrice']=\Lib\common\Language::priceByCurrency($product['productsPrice'], $_COOKIE['CurrencyCode'],'USD');
				$new_product['ProductsName']=$product['productName'];
				$new_product['ProductsPicture']=$product['firstPicUrl'];
				if(isset($product['productsIntroduction'])){
					$new_product['ProductsIntroduction']=$product['productsIntroduction'];
				}
				$new_product['ProductsParcels']=$product['productsParcels'];
				$new_product['ProductsStockTime']=$product['productsStockTime'];
				$new_product['Cid']=$product['cid'];
				if(isset($product['promotionPrice'])){
					$new_product['Price']=\Lib\common\Language::priceByCurrency($product['promotionPrice'], $_COOKIE['CurrencyCode'],'USD');
				}
				if(isset($product['libkey'])){
					$new_product['libkey']=$product['libkey'];
				}
				if(isset($product['fnum'])){
					$new_product['Fnum']=$product['fnum'];
				}
				if(isset($product['isNew'])){
					$new_product['new']=$product['isNew'];
				}
				if(isset($product['voteScore'])){
					$new_product['scorePj']=$product['voteScore'];
				}
				if(isset($product['commentsNum'])){
					$new_product['CommentsNum']=$product['commentsNum'];
				}
				if(isset($product['productsActivator'])){
					$new_product['ProductsActivator']=$product['productsActivator'];
				}
				if(isset($product['endclock'])){
					$new_product['endclock']=$product['endclock'];
				}
				$new_product['Priceoff']=$new_product['ProductsPrice']-$new_product['Price'];
				$productsAll[$t][]=$new_product;
				$i++;
				if($parameters_ALL[$t][1]==$i)
				{
					$i=0;
					$t++;
				}
			}			
		}		
		$tpl->assign('productsAll', $productsAll);
		//处理专题产品信息-切图型专题，不用调用产品id信息 end
		
		if($_SESSION[SESSION_PREFIX.'subscribe_email_id'] && $_SESSION[SESSION_PREFIX.'subscribe_email']){
			$tpl->assign('subscribe_email_id', $_SESSION[SESSION_PREFIX.'subscribe_email_id']);
			$tpl->assign('subscribe_email', $_SESSION[SESSION_PREFIX.'subscribe_email']);
			unset($_SESSION[SESSION_PREFIX.'subscribe_email_id']);
			unset($_SESSION[SESSION_PREFIX.'subscribe_email']);
		}
		//组装面包削
		$thisUrl=ResponseUtil::rewrite(array('url'=>'?module=promotions&action=specials&id='.$id,'isxs'=>'no'));		
		$classAll = '  <a class="blue" title="'.$specials_array['Featuretitle'].'" href="'.$thisUrl.'">'.stripslashes($specials_array['Featuretitle']).'</a>';
		$tpl->assign('classAll',$classAll);
		//END
		$modulePath = THEME.'FeatureTheme/';
		
		if($specials_array['isTakeTemp']==1){//切图专题
		
		}else{			
			$productTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/'.$specials_array['FeatureTheme'].'.htm';
			// echo '<pre>';
			// print_r($specials_array);
			// echo $productTheme;
			// die(); 
			if($params['referer']=='ht'){//后台专题预览
				$temp_website_lang=array('en-uk'=>'en','ja-jp'=>'ja','fr-fr'=>'fr','es-sp'=>'es','it-it'=>'it','ru-ru'=>'ru','de-ge'=>'de','pt-pt'=>'pt');
				$tpl->display('file:'.DATA.'feature/'.$temp_website_lang[SELLER_LANG].'default/'.$specials_array['FeatureTheme'].'.htm');
			}else{
				$tpl->display('file:'.$productTheme);
			}
		}
		
	}	
}