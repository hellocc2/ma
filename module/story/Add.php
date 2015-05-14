<?php 
namespace Module\Story;
use \Helper\RequestUtil as R;
/**
 * 
 * 添加新的成功故事
 * @author admin
 *
 */
class Add extends \Lib\common\Application{
	public function __construct(){
		//验证登录
		$memberId = \Helper\CheckLogin::getMemberID();
		
		$tpl = \Lib\common\Template::getSmarty ();
		$params = R::getParams();
		if(!empty($params->POSTFORM) && $params->POSTFORM==1){
			//提交表单
						
			if(empty($params->title)) \Helper\Js::alertForward('empty_title','','1');
			if(empty($params->nickName)) \Helper\Js::alertForward('empty_nickname','','1');
			if(empty($params->countryId) || empty($params->countryName)) \Helper\Js::alertForward('empty_country','','1');
			if(empty($params->content)) \Helper\Js::alertForward('empty_content','','1');
			
			$title = htmlspecialchars($params->title);
			$nickName = htmlspecialchars($params->nickName);
			$countryId = $params->countryId;
			$countryName = $params->countryName;
			$city = isset($params->city) ? htmlspecialchars($params->city) : '';
			$content = htmlspecialchars($params->content);
			$videoUrl = isset($params->videoUrl) ? $params->videoUrl : '';
			
			$productUrl = '';
			$productId = '';
			if(!empty($params->productUrl)){
				if(preg_match('#.*p(\d+)\.html#', $params->productUrl,$mat)){
					$productUrl = $params->productUrl;
					$productId = $mat[1];
				}else{
					\Helper\Js::alertForward('url_error','','1');
				}
			}
			$allowReply = isset($params->allowReply) ? $params->allowReply : 1;//0:允许 1:不允许
			
			$uploadFiles = array();
			if(!empty($_FILES['files'])){
				$myFiles  = $_FILES['files'];
				$fileCount = count($myFiles['name']);
				if($fileCount > 0){
					for($i=0;$i<$fileCount;$i++){
						if(isset($myFiles['tmp_name'][$i]) && !empty($myFiles['tmp_name'][$i])){
							$resultUpload = \Helper\Upload::imageUpload($myFiles['tmp_name'][$i],$myFiles['size'][$i],$myFiles['name'][$i],'2048000','fs/file/uploadMyStory.htm');
							if($resultUpload == 10000){
								\Helper\Js::alertForward('file_type_error', '', '1');
							}
							if($resultUpload == 10001){
								\Helper\Js::alertForward('file_size_error', '', '1');
							}
							if(isset($resultUpload['filePath'])){
								$uploadFiles[] = $resultUpload['filePath'];
							}else{
								\Helper\Js::alertForward('upload_error', '', '1');
							}
						}
					}
				}
			}
			
			$firstPicUrl = '';
			$secondPicUrl = '';
			if(!empty($uploadFiles)){
				if(isset($uploadFiles[0])){
					$firstPicUrl = $uploadFiles[0];
				}
				if(isset($uploadFiles[1])){
					$secondPicUrl = $uploadFiles[1];
				}
			}
			
			$memberIp = \Helper\RequestUtil::getClientIp();
			$memberEmail = !empty($_SESSION[SESSION_PREFIX . "MemberEmail"]) ? $_SESSION[SESSION_PREFIX . "MemberEmail"] : '';
			
			$data = array();
			$data['s.memberId'] = $memberId;
			$data['s.langCode'] = SELLER_LANG;
			$data['s.title'] = $title;
			$data['s.nickName'] = $nickName;
			$data['s.countryId'] = $countryId;
			$data['s.countryName'] = $countryName;
			$data['s.city'] = $city;
			$data['s.content'] = $content;
			$data['s.firstPicUrl'] = $firstPicUrl;
			$data['s.secondPicUrl'] = $secondPicUrl;
			$data['s.videoUrl'] = $videoUrl;
			$data['s.productId'] = $productId;
			$data['s.productUrl'] = $productUrl;
			$data['s.allowReply'] = $allowReply;
			$data['s.memberIp'] = $memberIp;
			$data['s.memberEmail'] = $memberEmail;
			
			$mStory = new \Model\Story();
			$status = $mStory->addNewStory($data);
			if(!empty($status) && $status['code']==0){
				$jumpUrl = \Helper\ResponseUtil::rewrite(array('url'=>'?module=story&action=index','isxs'=>'no'));
				\Helper\Js::alertForward('advisoryOk', $jumpUrl, '1');
			}else{
				\Helper\Js::alertForward('submit_failed', '', '1');
			}
			exit;
		}else{
			//显示表单
			$data = array();
			
			$data['memberId'] = $memberId;
			$data['langCode'] = SELLER_LANG;
			$data['num'] = 10;//music说的，暂定10个
			
			$mStory = new \Model\Story();
			$result = $mStory->findProduct($data);
			
			/**
			 * 获取购买记录
			 * @var unknown_type
			 */
			$productResult = array();
			if(!empty($result) && $result['code']==0){
				if(!empty($result['products'])){
					$productResult = $result['products'];
				}
			}
			$tpl->assign('productList',$productResult);
			
			/**
			 * 获取国家列表
			 * @var unknown_type
			 */
			$mCountry = new \Model\CountryList();
			$countryList = $mCountry->getCountryList(array ('cr.lang' => SELLER_LANG ));
			asort ( $countryList ['counties'] );
			$tpl->assign('countryList',$countryList);
			
			/**
			 * 
			 */
			$guestCountryCode = isset($_SERVER ['HTTP_X_REAL_COUNTRY']) && !empty($_SERVER ['HTTP_X_REAL_COUNTRY']) ? $_SERVER ['HTTP_X_REAL_COUNTRY'] : 'us';
			$guestCountryFlag = 1;
			if(!empty($countryList)){
				$guestCountryCode = strtolower($guestCountryCode);
				$guestCountryFlag = isset($countryList['countriesFlag'][$guestCountryCode]) ? $countryList['countriesFlag'][$guestCountryCode] : 1;
			}
			$tpl->assign('guestCountryFlag',$guestCountryFlag);
			
			$tpl->display('story_add.htm');
		}
		return '';
	}
}