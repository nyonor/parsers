<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 28.03.16
 * Time: 12:27
 */

use Sunra\PhpSimple\HtmlDomParser;

class SShinaParser extends RivalParserBase implements IProductParametersParser
{
	use ProductParametersParserTrait;

	const SITE_URL = "s-shina.ru";

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		$ourBrands = $dbController->GetAllBrands();

		$page = 1;
		$maxPage = 0;

		$results = [];

		do {

			$shouldContinue = false;

			$url = sprintf($this->_urlPattern, $page);
			$requestResRawString = iconv('cp1251', 'utf8',$this->Request($url, 0, 0));

			//print_r($requestResRawString);die;

			$res = [];
			preg_match('/(Шин с выбранными параметрами не найдено)/is', $requestResRawString, $res);
			if (isset($res[1])) {

				//echo "STOP";
				break;
				$shouldContinue = false;

			} else {
				//echo "CONT!";
				$shouldContinue = true;
			}

			$parser = new HtmlDomParser();
			$dom = $parser->str_get_html($requestResRawString);

			if(method_exists($dom, "find") == false) {
				continue;
			}

			foreach($dom->find(".srl-item") as $div) {

				$a = $div->find("b a", 0);
				//уберем двойные пробелы и запятые
				$rawTitle = str_replace(',', '', $a->plaintext);
				$rawTitle = trim(str_replace('  ', ' ', $rawTitle));

				$rivalTire = new RivalTireModel();

				//brand
				$rivalTire->brand = $this->GetBrandWithList($rawTitle, $ourBrands);
				if (isset($rivalTire->brand) == false) {
					continue;
				}
				//$rawTitle = str_ireplace($rivalTire->brand, '', $rawTitle);

				//runFlat
				if (stripos($rawTitle, "Run Flat") === false){
					$rivalTire->runFlat = false;
				} else {
					$rivalTire->runFlat = true;
				}

				//model
				$titleWithoutTypeSizes = substr($rawTitle, stripos($rawTitle, $rivalTire->brand));
				$model = trim(str_ireplace($rivalTire->brand, '', $titleWithoutTypeSizes));
				$rivalTire->model = $model;

				//typesizes
				$typeSizesRaw = trim(str_ireplace($titleWithoutTypeSizes, '', $rawTitle));

				//width && height
				$widthAndHeight = [];
				preg_match('/(\d+)\/?(\d+)?/is', $typeSizesRaw, $widthAndHeight);
				$height = isset($widthAndHeight[2]) ? $widthAndHeight[2] : null;
				$width = isset($widthAndHeight[1]) ? $widthAndHeight[1] : null;
				$rivalTire->width = $width;
				$rivalTire->height = $height;

				//constr
				$rivalTire->constructionType = $this->GetConstructionType($typeSizesRaw);

				//diameter
				$rivalTire->diameter = $this->GetDiameter($typeSizesRaw);


				//loadIndex & speedIndex
				$loadAndSpeedIndexPregMatchRes = [];
				preg_match('/\s(\d+\/\d+|\d+)([a-zA-Z])/is', $typeSizesRaw, $loadAndSpeedIndexPregMatchRes);
				$rivalTire->loadIndex = isset($loadAndSpeedIndexPregMatchRes[1]) ?
					$loadAndSpeedIndexPregMatchRes[1] : null;
				$rivalTire->speedIndex = isset($loadAndSpeedIndexPregMatchRes[2]) ?
					$loadAndSpeedIndexPregMatchRes[2] : null;

				//season
				$seasonRawString = trim($div->find(".text div", 1)->plaintext);
				$rivalTire->season = SeasonModel::Factory($seasonRawString)->GetSeasonName();

				//price
				$justPriceRawString = trim($div->find("table tr td", 1)->find("span", 0)->plaintext);
				$discountedPriceRawString = trim($div->find("table tr td", 1)->find("span", 1)->plaintext);
				$rivalTire->price = isset($justPriceRawString) ? (int)$justPriceRawString : (int)$discountedPriceRawString;

				//qty
				$rivalTire->quantity = (int)$div->find("table tr", 1)->find("td", 1)->plaintext;

				//site & url
				$rivalTire->site = self::SITE_URL;
				$rivalTire->url = $url;

				MyLogger::WriteToLog($rawTitle, LOG_ERR);

				var_dump($rivalTire);

				$results[] = $rivalTire;

				$shouldContinue = true;
			}

			if (method_exists($dom, "clear")) {

				$dom->clear();
				unset($dom);

			}

			$page++;

		} while ($shouldContinue == true && ($maxPage == 0 || $page <= $maxPage));

		return $results;

	}

	/**
	 * Возвращает url сайта для парсинга
	 * @return string
	 */
	public function GetSiteToParseUrl()
	{
		return self::SITE_URL;
	}

	/**
	 * Возвращает готовый объект curl для запросов
	 * @param $url
	 * @return resource
	 */
	protected function GetCurl($url)
	{
		if ($this->_curl != null) {
			curl_close($this->_curl);
		}

		$this->_curl = curl_init($url);
		curl_setopt($this->_curl, CURLOPT_URL,$url);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->_curl, CURLOPT_USERAGENT,
			"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36");
		curl_setopt($this->_curl, CURLOPT_AUTOREFERER, true);
		curl_setopt($this->_curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
		//curl_setopt($this->_curl, CURLOPT_HEADER, true);
		//curl_setopt($this->_curl, CURLOPT_COOKIE, $this->GetResponseCookies());

		return $this->_curl;
	}
}