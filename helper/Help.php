<?php
namespace Helper;
/**
 * 手机站帮助中心数据处理
 * @author Administrator
 *
 */
class Help {
	private $HelpData;
	
	/**
	 * 初始化数据
	 * @param string $id
	 */
	public function __construct($id = ''){
		$helperM = new \Model\HelpCenter();
		$helpParam = array();
		
		$helpParam['languageCode'] = SELLER_LANG;
		$helpParam['webSiteId'] = MAIN_WEBSITEID;
		$helpParam['pcoriesIds'] = 0;
		$helpParam['pIds4content'] = 0;
		$helpParam['contentIds'] = $id;
		$helpParam['pageSizeP'] = 0;
		$helpParam['pageSizeC'] = 0;
		
		$result = $helperM->getHelp($helpParam);
		
		$result = \helper\String::strDosTrip($result);//字符处理
		$this->HelpData = $result;
	}
	
	/**
	 * 获取接口返回状态
	 */
	public function getCode(){
		return $this->HelpData['code'];
	}
	
	/**
	 * 获取帮助中心分类
	 */
	public function getCategory(){
		if(!empty($this->HelpData['categories'])){
			return $this->HelpData['categories'];
		}
	}
	
	/**
	 * 获取帮助中心内容
	 */
	public function getContent(){
		if(!empty($this->HelpData['contents'])){
			return $this->HelpData['contents'];
		}
	}
	
	/**
	 * 获取返回内容父分类名
	 */
	public function getCategoryMenu(){
		$pcoriesArray = array();
		if(!empty($this->HelpData['contents']) && !empty($this->HelpData['contents']['results'])){
			$pcoriesIdArray = array();
			foreach($this->HelpData['contents']['results'] as $k=>$v){
				if(!empty($v)){
					$pcoriesIdArray[] = $v['pcoriesId'];
					$pcoriesArray[$v['pcoriesId']]['contentId'] = $v['id'];
				}
			}
			if(!empty($pcoriesIdArray) && !empty($this->HelpData['categories']['results'])){
				foreach($this->HelpData['categories']['results'] as $kc=>$vc){
					if(in_array($vc['id'],$pcoriesIdArray)){
						$pcoriesArray[$vc['id']]['pcoriesname'] = $vc['pcoriesname'];
					}
				}
			}
		}
		return $pcoriesArray;
	}
	
	
	public function getHelpMenuList(){
		$menuList = array();
		if(!empty($this->HelpData['categories']) && !empty($this->HelpData['categories']['results'])){
			foreach($this->HelpData['categories']['results'] as $c){
				$menuList[$c['id']] = array(
					'id'=>$c['id'],
					'pcoriesname'=>stripslashes($c['pcoriesname']),
					'article'=>array(),	
				);
			}
		}
		if(!empty($this->HelpData['contents']) && !empty($this->HelpData['contents']['results'])){
			foreach($this->HelpData['contents']['results'] as $k=>$v){
				if(isset($menuList[$v['pcoriesId']])){
					$menuList[$v['pcoriesId']]['article'][] = array(
						'id'=>$v['id'],
						'name'=>stripslashes($v['title']),
					);
				}
			}
		}
		return $menuList;
	}
	
	
	/**
	 * 获取返回内容title名
	 */
	public function getContentTitle(){
		$pcoriesArray = array();
		
		if(!empty($this->HelpData['contents']) && !empty($this->HelpData['contents']['results'])){
			$pcoriesIdArray = array();
			foreach($this->HelpData['contents']['results'] as $k=>$v){
				if(!empty($v)){
					$pcoriesArray[$v['id']]['contentId'] = $v['id'];
					$pcoriesArray[$v['id']]['pcoriesname'] = $v['title'];
				}
			}
		}
		return $pcoriesArray;
	}
}