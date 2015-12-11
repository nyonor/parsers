<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 25.11.15
 * Time: 17:21
 */

	$c = null;
	//if ($this->_curlResponseCookies == null) {
		$curl = curl_init("http://www.kolesa-darom.ru/");
		//curl_setopt($curl, CURLOPT_URL, $this->GetSiteToParseUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl, CURLOPT_USERAGENT,"curl/7.29.0");
		curl_setopt($curl, CURLOPT_AUTOREFERER, true);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		$response = curl_exec($curl);
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
		if (count($matches) > 1)
			$c = $matches[1];
		curl_close($curl);
		sleep(3);
	//}

	//test
	echo "TST!";
	if ($c != null) {
		//print_r($c);die;
		$curl = curl_init("http://www.kolesa-darom.ru/");
		//curl_setopt($curl, CURLOPT_URL, $this->GetSiteToParseUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_USERAGENT,"curl/7.29.0");
		curl_setopt($curl, CURLOPT_AUTOREFERER, true);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_COOKIE, $c[1]);
		$res = curl_exec($curl);
		print_r($res);
		curl_close($curl);
	}

	//return $this->_curlResponseCookies;