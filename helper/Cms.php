<?php
namespace Helper;
class Cms{
	public static function getCmsInfo($dataParms){
		$CmsObject = new \Model\Cms();
		$data = $CmsObject->getCategory($dataParms);
		return $data;
	}
}