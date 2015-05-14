<?php
namespace Helper;

class ServiceData {

	public static function getServiceData($module, $action, $param, $method = 'GET', $namespace = '') {
		if (empty($namespace)) {
			$url = JAVA_WEBSERVICE_URL . '/products/';
		} else {
			$url = JAVA_WEBSERVICE_URL;
		}
		$url = rtrim($url, '?\/');
		if (is_string($module) && is_string($action)) {
			if (!empty($namespace)) {
				$url .= '/' . $namespace;
			}
			$url .= '/' . $module . '/' . $action . '.htm';
		}

		if ($method == 'GET') {
			$appendedValues = array();
			if (is_array($param)) {
				foreach ($param as $k => $v) {
					$appendedValues[] = $k . '=' . urlencode($v);
				}
			}
			$appendedStr = implode('&', $appendedValues);
			if (!empty($appendedValues)) {
				$url .= '?' . $appendedStr;
			}
		}

		$ch = curl_init();
		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		}
		// print_r($url);
		// echo '<br />';
		curl_setopt_array($ch, array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 5));
		$response = curl_exec($ch);
		if ($errNo = curl_errno($ch)) {
			$handle = fopen(ROOT_PATH . 'data/curl.txt', 'a');
			$info = @curl_getinfo($ch);
			fwrite($handle, 'error--');
			fwrite($handle, $url . "\n");
			fwrite($handle, var_export($info, true) . "\n\r");
			fwrite($handle, "end--\n\r");
			fclose($handle);

			curl_close($ch);
			return false;
		}
		curl_close($ch);
		$str = gzuncompress($response);

		$responseArr = json_decode($str, true);

		if ($responseArr['code'] == '0') {
			return $responseArr;
		} else {
			$handle = fopen('../errors/curl.txt', 'a');
			$info = @curl_getinfo($ch);
			fwrite($handle, 'request--');
			fwrite($handle, $url . "\n");
			fwrite($handle, var_export($responseArr, true) . "\n\r");
			fwrite($handle, "end--\n\r");
			fclose($handle);
			return false;
		}
	}

}
