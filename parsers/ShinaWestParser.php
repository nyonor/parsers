<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 30.11.15
 * Time: 16:01
 */

use Sunra\PhpSimple\HtmlDomParser;
use sys\Timer;

class ShinaWestParser extends RivalParserBase implements IProductParametersParser
{
	use ProductParametersParserTrait;

	const SITE_URL = "shinawest.ru";
	protected $_curl;
	public $season;

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		$urlPattern = $this->_urlPattern;
		$brands = $dbController->GetAllBrands();
		$brandsToParse = $brands;

		$results = [];

		$totalT = 0;

		foreach($brandsToParse as $brandToParse) {

			$currentSprint = 1;
			$maxSprint = null;
			do {

				//var_dump($brandToParse);

				//$ts = Timer::Start();
				$url = sprintf($urlPattern, strtolower($brandToParse), $currentSprint * 15 - 14); // важно!
				$curl = $this->GetCurl($url);
				$rawRes = iconv('cp1251', 'utf8', curl_exec($curl));
				//$timeRes = Timer::StopAndResult($ts);
				//var_dump($timeRes);
				//$totalT += $timeRes;

				$stopRequestsMatchResult = "";
				preg_match('/(Данная страница не существует!)/', $rawRes, $stopRequestsMatchResult);
				if (count($stopRequestsMatchResult) > 1 && $stopRequestsMatchResult[1] != null) {
					var_dump("STOPPED");
					break;
				}

				$htmlDom = new HtmlDomParser();
				$strHtmlDom = $htmlDom->str_get_html($rawRes);

				if (!method_exists($strHtmlDom, "find")) {
					var_dump("STOPPED!!!");
					break;
				}

				//проверка на пустой каталог
				if (empty($strHtmlDom->find('div.mainContent figure', 0)) == true) {
					var_dump("BREAK!!!");
					break;
				}

				if ($maxSprint == null) {
					foreach ($strHtmlDom->find(".review-page") as $a) {
						$maxSprint++;
					}
				}
				//$maxSprint = 1;//todo ТЕСТ!

				if ($maxSprint == null) {
					break;
				}

				//var_dump("OK");
				//парсим
				foreach($strHtmlDom->find('div.mainContent figure') as $prodTag) {
					//echo $prodTag->outertext;
					$rivalTireModel = new RivalTireModel();
					$rivalTireModel->url = $url;
					$rivalTireModel->site = $this->GetSiteToParseUrl();
					$rivalTireModel->season = $this->season;

					//brand
					$rivalTireModel->brand = $brandToParse;

					//model
					$brandAndModelRawString = $prodTag->find("div.nofloat td",1)->plaintext;
					$modelString = str_ireplace($rivalTireModel->brand,"",$brandAndModelRawString);
					$rivalTireModel->model = !empty($modelString) ? trim($modelString) : null;

					//width
					$widthAndHeightAndDiameterRawString = $prodTag->find("div.nofloat td",2)->plaintext;
					$width = $this->GetWidth($widthAndHeightAndDiameterRawString);
					$rivalTireModel->width = $width;

					//height
					$height = $this->GetHeight($widthAndHeightAndDiameterRawString);
					$rivalTireModel->height = $height;

					//costrType
					$constructionType = $this->GetConstructionType($widthAndHeightAndDiameterRawString);
					$rivalTireModel->constructionType = $constructionType;

					//diameter
					$diameter = $this->GetDiameter($widthAndHeightAndDiameterRawString);
					$rivalTireModel->diameter = $diameter;

					//loadIndex
					$loadAndSpeedIndexesRawString = $prodTag->find("div.nofloat td",3)->plaintext;
					$loadIndex = $this->GetLoadIndex($loadAndSpeedIndexesRawString);
					$rivalTireModel->loadIndex = $loadIndex;

					//speedIndex
					$speedIndex = $this->GetSpeedIndex($loadAndSpeedIndexesRawString);
					$rivalTireModel->speedIndex = $speedIndex;

					//price
					//в цене находятся символы &nbsp; которые ничем не могу засечь...
					$priceArr = [];
					preg_match_all('/(\d)/',$prodTag->find("div.nofloat td",5)->plaintext,$priceArr);
					$price = implode('',$priceArr[0]);
					$rivalTireModel->price = (int)$price;

					//qty
					$qtyString = $prodTag->find("div.nofloat td",6)->plaintext;
					if(strripos($qtyString, "есть в наличии") === false) {
						$rivalTireModel->quantity = 0;
					} else {
						$rivalTireModel->quantity = 1;
					}

					$results[] = $rivalTireModel;

				}

				//sleep(rand(2,4));

				$currentSprint++;

			} while ($currentSprint <= $maxSprint && $maxSprint != null);
		}

		curl_close($this->_curl); //закроем только в конце

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
	 * TODO: в принципе можно вынести в базовый функционал...
	 */
	protected function GetCurl($url)
	{
		//TODO: попробуем не закрывать соединеие... пока...
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
		curl_setopt($this->_curl, CURLOPT_HEADER, true);
		//curl_setopt($this->_curl, CURLOPT_COOKIE, $this->GetResponseCookies());

		return $this->_curl;
	}
}