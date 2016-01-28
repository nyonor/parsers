<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 26.01.16
 * Time: 12:34
 */
require_once 'sys/MyLogger.php';
use Sunra\PhpSimple\HtmlDomParser;

class ShinserviceParser extends RivalParserBase implements IProductParametersParser
{

	use ProductParametersParserTrait;

	const SITE_URL = "shinservice.ru";
	protected $_curl;

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		$allBrands = $this->_dbController->GetAllBrands();
		$allTypeSizes = $this->_dbController->GetAllTypeSizes();

		$results = [];

		foreach ($allTypeSizes as $typeSizeModel) {

			if (empty($typeSizeModel->width)
				|| empty($typeSizeModel->height)
				|| empty($typeSizeModel->diameter)) {

				continue;
			}

			//$url = sprintf($this->_urlPattern, $typeSizeModel->width, $typeSizeModel->height, $typeSizeModel->diameter);
			$url = "http://www.shinservice.ru/search/ALL/winter/255-40-R17/";

			//continue;

			$curl = $this->GetCurl($url);
			$rawRes = curl_exec($curl);

			$htmlParser = new HtmlDomParser();
			$dom = $htmlParser->str_get_html($rawRes);

			$any = $dom->find(".result-item",0);
			if($any == null) {
				//MyLogger::WriteToLog("SKIPPING... ".$url, LOG_ERR);
				continue;
			}

			//MyLogger::WriteToLog($url, LOG_ERR);
			//var_dump($url);

			foreach($dom->find(".result-item") as $div) {

				$rivalTireModel = new RivalTireModel();

				$rawTitle = $div->find(".item-description h2 a", 0)->plaintext;

				//brand
				$rivalTireModel->brand = $this->GetBrandWithList($rawTitle, $allBrands);

				//model
				$modelRawStr = str_ireplace($rivalTireModel->brand, '', $rawTitle);
				$rivalTireModel->model = trim($modelRawStr);

				//width & height & diameter & constr
				$rivalTireModel->width = $typeSizeModel->width;
				$rivalTireModel->height = $typeSizeModel->height;
				$rivalTireModel->diameter = $typeSizeModel->diameter;
				$rivalTireModel->constructionType = "R";

				//url & site
				$rivalTireModel->url = $url;
				$rivalTireModel->site = $this->GetSiteToParseUrl();

				//loadIndex
				$loadIndexRaw = $div->find(".item-description span", 0)->plaintext;
				$rivalTireModel->loadIndex = trim($loadIndexRaw);

				//speedIndex
				$speedIndexRaw = $div->find(".item-description span", 1)->plaintext;
				$rivalTireModel->speedIndex = $speedIndexRaw;

				//season
				if($div->find(".icon-winter", 0)) {
					$rivalTireModel->season = SeasonModel::WINTER;
				}

				if($div->find(".icon-summer", 0)) {
					$rivalTireModel->season = SeasonModel::SUMMER;
				}

				//qty
				$qtyRawStr = $div->find(".item-description .item-control-btn nobr", 0)->plaintext;
				$qtyPregMatchRes = [];
				preg_match('/(\d+)/', $qtyRawStr, $qtyPregMatchRes);
				$rivalTireModel->quantity = isset($qtyPregMatchRes[1]) ? $qtyPregMatchRes[1] : 0;

				//price
				$priceRawStr = $div->find(".item-description .item-price", 0)->plaintext;
				$priceRawStr = str_ireplace(' ','',$priceRawStr);
				$pricePregMatchRes = [];
				preg_match('/(\d+)/', $priceRawStr, $pricePregMatchRes);
				$rivalTireModel->price = isset($pricePregMatchRes[1]) ? $pricePregMatchRes[1] : null;

				//var_dump($rivalTireModel);

				$results[] = $rivalTireModel;

			}

			//return $results;

		}

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

		return $this->_curl;
	}
}