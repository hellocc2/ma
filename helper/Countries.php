<?php
namespace Helper;
/**
 * 公共函数类
 */
class Countries {
	/**
	 * 获取国家地质
	 *
	 * @param int $this_zid 当前登录用户的国家地址ID
	 * @param string $lang 当前语言
	 * @param string $ProductsParcels 产品包装规格
	 * @return string
	 */
	public static function GetCountries($lang,$this_zid = '',$ProductsParcels='')
	{
		if(empty($this_zid)){
			$this_zid = 1;
		}
		$string="";
		$fileName = DATA_CACHE_ROOT_PATH.'Countries.php';
		
		include_once DATA_CACHE_ROOT_PATH.'logistics.php';
        include_once $fileName;
		self::array_sort_e($CountriesAll,"CountriesName|".($lang=='zh-cn'?'en-uk':$lang)." ASC");
		if ($lang == "ja-jp"){
			$string = "<option value=\"36\" img='".CDN_UPLOAD_URL."upload/flag/2008/200809/Flag_1222582958.gif'>日本</option>";
		}
		else if($lang == "ru-ru"){
			$string = "<option value=\"58\">Россия</option>
											<option value=\"179\">Грузия</option>
											<option value=\"181\">Киргизстан</option>
											<option value=\"182\">Республика Молдова</option>
											<option value=\"184\">Туркменистан</option>
											<option value=\"185\">Узбекистан</option>
											<option value=\"166\">Азербайджан</option>
											<option value=\"174\">Армения</option>
											<option value=\"151\">Казахстан</option>
											<option value=\"68\">Украина</option>
											<option value=\"69\">Белоруссия</option>
											<option value=\"87\">Эстония</option>";
		}
		//if ($lang=="fr-fr")$string="<option value=\"23\">France</option>";//法文站修改
		//if ($lang=="kk-kk")$string="<option value=\"12\">中国</option>";//缺省
		else {
			foreach ( $CountriesAll as $id => $key ) {
				$i=0;
				foreach ($logisticsAll as $k => $value) {
					if (@in_array($id, $value['Countries']) && $i==0) {
						if(($ProductsParcels==1 && $value['key']=='Standard') or $ProductsParcels!=1)
						{
							if ($id == $this_zid)
							$selected = 'selected';
							else
							$selected = '';
							$string .= "<option value=\"" . $id . "\"  " . $selected . " img='".CDN_UPLOAD_URL.'upload/flag/'.$key['CountriesBanner']."'>";
							if($lang == 'zh-cn')
							$string .= substr ( stripslashes ( $key ["CountriesName"] ['en-uk'] ), 0, 1 ) . " ";
							$string .= stripslashes ( $key ["CountriesName"] [$lang] ) . "</option>";
						}
						$i++;
					}
				}
				
				
			}
		}
		return $string;
	}
	
	public static function array_sort_e(&$value,$fes)
	{
		if(!is_array($value) || count($value)<=1) return false;
		$fes=explode(',',$fes);
		$fescount=count($fes);
		for($i=0;$i<$fescount;$i++){
			$arr1=explode(' ',$fes[$i]);
			$ord=count($arr1)==2?$arr1[1]:'ASC';
			$arrfe[$i]['fe']=explode('|',$arr1[0]);
			$arrfe[$i]['ord']=count($arr1)==2?$arr1[1]:'ASC';
		}
		//把数组的健名存入健值中，排序后再还原
		$keys=array_keys($value);
		for($i=0;$i<count($keys);$i++){
			$value2[$i]=$value[$keys[$i]];
			$value2[$i]['tmpordervalue_w']=$keys[$i];
		}
		self::array_sort_e_chuli($value2,$arrfe,0);
		for($i=0;$i<count($value2);$i++){
			$st=$value2[$i]['tmpordervalue_w'];
			unset($value2[$i]['tmpordervalue_w']);
			$value3[$st]=$value2[$i];
		}
		$value=$value3;
	}
	public static function array_sort_e_chuli(&$value,$arrfe,$fenum)
	{
		$fes=$arrfe[$fenum]['fe'];
		$lev=count($arrfe[$fenum]['fe']);
		if($arrfe[$fenum]['ord']=='ASC'){
			for($i=0;$i<count($value);$i++){
				for($j=count($value)-1;$j>$i;$j--){
					if(($lev==1 && strtolower($value[$j][$fes[0]])<strtolower($value[$j-1][$fes[0]])) || ($lev==2 && strtolower($value[$j][$fes[0]][$fes[1]])<strtolower($value[$j-1][$fes[0]][$fes[1]]))){
						$ee=$value[$j];
						$value[$j]=$value[$j-1];
						$value[$j-1]=$ee;
					}
				}
			}
		}else{
			for($i=0;$i<count($value);$i++){
				for($j=count($value)-1;$j>$i;$j--){
					if(($lev==1 && strtolower($value[$j][$fes[0]])>strtolower($value[$j-1][$fes[0]])) || ($lev==2 && strtolower($value[$j][$fes[0]][$fes[1]])>strtolower($value[$j-1][$fes[0]][$fes[1]]))){
						$ee=$value[$j];
						$value[$j]=$value[$j-1];
						$value[$j-1]=$ee;
					}
				}
			}
		}
		//取得排序后还相同的行
		$xtong=array();
		$snum=0;
		$fenum++;
		if(count($arrfe)>$fenum){
			for($i=0;$i<count($value);$i++){
				if($i+1<count($value)){
					$oldval=$lev==1?$value[$i][$fes[0]]:$value[$i][$fes[0]][$fes[1]];
					$newval=$lev==1?$value[$i+1][$fes[0]]:$value[$i+1][$fes[0]][$fes[1]];
				}
				if($i+1<count($value) && $oldval==$newval){
					$xtong[]=$value[$i];
					$lnum++;
				}else{
					if(count($xtong)>0){
						$lnum++;
						$xtong[]=$value[$i];
						//print_r($xtong);
						array_sort_e_chuli($xtong,$arrfe,$fenum);
						array_splice($value,$snum,$lnum,$xtong);
						$xtong=array();
					}
					$snum=$i+1;
				}
			}
		}
	}
}