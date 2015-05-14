<?php
namespace Wap\Module\Thing;
use \Helper\RequestUtil as RequestUtil;
use Helper\RequestUtil as R;
/**
 * 手机站终端页显示模块
 * @author Cheng Jun<cgjp123@163.com>
 * @since 2012-06-14
 */
class Nosale extends \Lib\common\Application{
	public function __construct(){
		$productsId = R::getParams('productsId');
		$nosale_mail = R::getParams('mail');
		$lang = SELLER_LANG;
		$data = array(
			'productId'=>$productsId,
			'email'=>$nosale_mail,
			'languageCode'=>$lang,		
		);
		$productObject = new \Model\Product ();
		$result = $productObject->getArriveEmail($data);
		if(isset($result['code']) && $result['code'] == 0){
			if($result['flag'] == 0){
				echo \LangPack::$items['notice_tips_sucess'];
				exit;
			}
			if($result['flag'] == 1){
				switch (SELLER_LANG)
				{
					case "en-uk":
						echo "A customer account with this email address already exists!";
						break;
				
					case "ja-jp":
						echo "このメールアドレスによるアカウントは別のユーザーにより使用されており、ご利用になれません。";
						break;
				
					case "fr-fr":
						echo "Un compte clientèle avec ce mail adresse existait déjà.";
						break;
				
					case "es-sp":
						echo "Ya existe una cuenta registrada con esta dirección de email.";
						break;
				
					case "de-ge":
						echo "Ein Kundenkonto mit dieser Email Adresse ist schon besetzt.";
						break;
				
					case "it-it":
						echo "Esite già un account con quest'indirizzo email.";
						break;
				
					case "ru-ru":
						echo "Аккаунт с этим адресом электронной почты уже существует.";
						break;
				
					case "cn-cn":
						echo "该用户名已被注册";
						break;
				
					case "zh-hk":
						echo "該用戶名已被注冊";
						break;
				
					case "ar-ar":
						echo "البريد الالكترونى موجود مسبقا";
						break;
				}
				exit;
			}
		}
	}
}