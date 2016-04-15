<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.04.16
 * Time: 16:06
 */

trait HttpRequestTrait
{
	private $_curl;
	private $_curlResponseCookies;
	private $_lastRequestUrl;

	protected $shouldLogRequest = false;
	protected $waitAfterRequestInSeconds = 3;
	protected $userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36";
	protected $headers = [];

	private function GetResponseCookies() {
		if ($this->_curlResponseCookies == null) {
			$curl = curl_init($this->_lastRequestUrl);
			curl_setopt($curl, CURLOPT_URL, $this->_lastRequestUrl);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
			curl_setopt($curl, CURLOPT_AUTOREFERER, true);
			curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_HEADER, true);
			$response = curl_exec($curl);
			preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
			if (count($matches) > 1)
				$this->_curlResponseCookies = $matches[1][1];
			curl_close($curl);
			sleep($this->waitAfterRequestInSeconds);
		}

		return $this->_curlResponseCookies;
	}

	protected function Request($url, $minWaitSeconds = 0, $maxWaitSeconds = 0, $withResponseCookies = false) {

		$this->_lastRequestUrl = $url;

		$rawRes = null;

		$curl = $this->GetCurl($url, $withResponseCookies);

		if ($this->shouldLogRequest)
			MyLogger::WriteToLog("Вызываю URL " . $url, LOG_ERR);

		$rawRes = curl_exec($curl);

		//выждем время
		if ($minWaitSeconds > 0 && $maxWaitSeconds > 0)
			sleep(rand($minWaitSeconds, $maxWaitSeconds));

		var_dump("REQ!");

		return $rawRes;
	}

	private function GetCurl($url, $withResponseCookies = false)
	{
		if ($this->_curl != null) {
			curl_close($this->_curl);
		}

		$this->_curl = curl_init($url);
		curl_setopt($this->_curl, CURLOPT_URL,$url);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->_curl, CURLOPT_USERAGENT, $this->userAgent);
		curl_setopt($this->_curl, CURLOPT_AUTOREFERER, true);
		curl_setopt($this->_curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
		//curl_setopt($this->_curl, CURLOPT_HEADER, true);

		if (count($this->headers) > 0) {
			curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $this->headers);
		}


		if ($withResponseCookies == true)
			curl_setopt($this->_curl, CURLOPT_COOKIE, $this->GetResponseCookies());

		return $this->_curl;
	}
}