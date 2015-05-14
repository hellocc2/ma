<?php
namespace Module\Promotions;
use \Model\Feature;
use Helper\RequestUtil as RequestUtil;
use Helper\ResponseUtil as ResponseUtil;
use Helper\String as HString;
use Helper\Js as JS;
use \Lib\common\Language;
/**
 * 专题处理类
 * @author JerryYang<yang.tao.php@gmail.com>
 * 
 */
class Specials extends \Lib\common\Application {
	public function __construct() {
		
		$tpl = \Lib\common\Template::getSmarty ();
		$tpl->assign('ExchangeCurrency',$tpl->_tpl_vars['Currency']);
		
		$params = RequestUtil::getParams ('params');
		$tpl->assign('requestParams',$params);
		$customUrl 	= $params['customUrl']?$params['customUrl']:(RequestUtil::getParams ('customUrl'));//自定义url进来的
		$id 		= $params['id']?$params['id']:(RequestUtil::getParams ('id'));
		
		$_SESSION['PreviousPage'] = RequestUtil::getUrl(true);
		//paypal使用  http://www.milanoo.com/en/promotions/specials-index-paypal.html
		if(stripos( $_SESSION['PreviousPage'] ,'index-paypal'))
		{
			if (SELLER_LANG == 'en-uk') {
				$id = 112033;
			} elseif (SELLER_LANG == 'ja-jp') {
				$id = 112033;
			} elseif (SELLER_LANG == 'fr-fr') {
				$id = 112367;
			} elseif (SELLER_LANG == 'es-sp') {
				$id = 112369;
			} elseif (SELLER_LANG == 'de-ge') {
				$id = 112371;
			} elseif (SELLER_LANG == 'it-it') {
				$id = 112373;
			}
			elseif (SELLER_LANG == 'ru-ru') {
				$id = 112375;
			}
		}
		//======================================
		//万圣节专题规则补充，参看需求 http://192.168.0.2/issues/10498
		//此修改永久有效。
		if($id=='halloween'){
				if(SELLER_LANG == 'en-uk'){
					$id = '116009';
				}elseif (SELLER_LANG == 'ja-jp') {
						$id = '116011';
				} elseif (SELLER_LANG == 'fr-fr') {
						$id = '116013';
				} elseif (SELLER_LANG == 'es-sp') {
						$id = '116015';
				} elseif (SELLER_LANG == 'de-ge') {
						$id = '116017';
				} elseif (SELLER_LANG == 'it-it') {
						$id = '116019';
				}elseif (SELLER_LANG == 'ru-ru') {
						$id = '116023';
				}elseif(SELLER_LANG == 'pt-pt'){
						$id = '116021';
				}
				//给前端页面一个是否十月份之后的判断
				$catch_october	= mktime(0,0,0,9,23,2012);
				$catch_october  = time()>$catch_october?1:0;
				$tpl->assign('catch_october',$catch_october);
				
				//给前端页面一个是否9月27日早上6点之后的判断
				$catch_dawn	= mktime(6,0,0,9,27,2012);
				$catch_dawn=time()>$catch_dawn?1:0;
				$tpl->assign('catch_dawn',$catch_dawn);
				
				if(SELLER_LANG=='de-ge'){
					//给前端页面德语一个是否10月31日早上6点之后的判断
					$catch_dawn_de	= mktime(6,0,0,10,31,2012);
					$catch_dawn_de=time()>$catch_dawn_de?1:0;
					$tpl->assign('catch_dawn_de',$catch_dawn_de);
				}
				if(SELLER_LANG=='ja-jp'){
					//给前端页面日语一个是否10月22日23点之后的判断
					$catch_night_jp	= mktime(23,0,0,10,22,2012);
					$catch_night_jp=time()>$catch_night_jp?1:0;
					$tpl->assign('catch_night_jp',$catch_night_jp);
				}
		}
		//end
		//======================================	
		
		$special=new \Model\Feature();
		$featureInfo=$special->getFeatureInfoById($id,$customUrl);
		$id=$featureInfo['featureInfo']['id'];
		$tpl->assign('featureid',$id);
		/*      echo '<pre>';
		//unset($featureInfo[featureInfo]['products']);
		print_r($featureInfo);
		die;  */    
		
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
		$parameters_ALL=array();
		$parameters=$specials_array['parameters'];
		//print_r($parameters);
		if(!empty($parameters)){
			$parameters=explode("|||",$parameters);
			foreach($parameters as $parameters_item){
				$parameters_item_a=explode(":",$parameters_item);
				$parameters_ALL[]=$parameters_item_a;
			}
		}
		$tpl->assign( 'parameters_ALL', $parameters_ALL );
		$productsAll='';
		//处理专题产品信息-切图型专题，不用调用产品id信息	
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
		
		if(isset($_SESSION[SESSION_PREFIX.'subscribe_email_id']) && isset($_SESSION[SESSION_PREFIX.'subscribe_email'])){
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
			if($specials_array['take_facebook']==1){
				$facebook_lang=array(
						'en-uk'=>'en_US',
						'fr-fr'=>'fr_FR',
						'es-sp'=>'es_ES',
						'de-ge'=>'de_DE',
						'it-it'=>'it_IT',
						'ru-ru'=>'ru_RU',
						'ja-jp'=>'ja_JP',
						'pt-pt'=>'pt_PT',
				);
				$facebook_websites=array(
						'en-uk'=>'https://www.facebook.com/milanoo.us',
						'fr-fr'=>'https://www.facebook.com/milanoo.fr',
						'ja-jp'=>'https://www.facebook.com/Milanoo.JP',
						'es-sp'=>'https://www.facebook.com/pages/Milanoo-Espa%C3%B1a/133836160036911',
						'de-ge'=>'https://www.facebook.com/Milanoo.Deutsch',
						'it-it'=>'https://www.facebook.com/mipiace.milanooitalia',
						'lolita_show_clothing'=>'https://www.facebook.com/Lolitafashionclothing',
						'lolita_beauty'=>'https://www.facebook.com/lolitafashion',
						'wedding_milanoo'=>'https://www.facebook.com/pages/Wedding-Milanoo/186808631366434',
						'sexy_lingerie'=>'https://www.facebook.com/pages/HOT-SEXY-Lingerie-milanoo/193528310676346',
						'fashion_shoes'=>'https://www.facebook.com/fashionshoesmilano',
						'cosplay_costume'=>'https://www.facebook.com/cosplaycommunity',
						'zentai'=>'https://www.facebook.com/lycrazentaisuits'
				);
				$facebook_html="
				<div id='fb-root'>
				<script>(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src ='//connect.facebook.net/".$facebook_lang[SELLER_LANG]."/all.js#xfbml=1';
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
			<div class='fb-like' data-href='".$facebook_websites[$specials_array['facebookWebsite']]."' data-send='true' data-width='900' data-show-faces='true'></div>
			</div>
			";
				$tpl->assign('facebook_html',$facebook_html);
			}
			$productTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/global_temp.htm';
			$tpl->assign('customHTML',stripslashes($specials_array['customHTML']));
			
			$tpl->display('file:'.$productTheme);
		}else{
			
			$productTheme = $modulePath.\config\Language::$webtheme_lang[SELLER_LANG].'/'.$specials_array['FeatureTheme'].'.htm';
			/* echo '<pre>';
			print_r($specials_array);
			echo $productTheme;
			die(); */
			if($params['referer']=='ht'){//后台专题预览
				$temp_website_lang=array('en-uk'=>'en','ja-jp'=>'ja','fr-fr'=>'fr','es-sp'=>'es','it-it'=>'it','ru-ru'=>'ru','de-ge'=>'de','pt-pt'=>'pt');
				$tpl->display('file:'.DATA.'feature/'.$temp_website_lang[SELLER_LANG].'default/'.$specials_array['FeatureTheme'].'.htm');
			}else{
				$tpl->display('file:'.$productTheme);
			}
		}
		
	}	
}