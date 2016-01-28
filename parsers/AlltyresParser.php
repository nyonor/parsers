<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 11.01.16
 * Time: 19:04
 */

use Sunra\PhpSimple\HtmlDomParser;

class AlltyresParser extends RivalParserBase implements IProductParametersParser
{
	use ProductParametersParserTrait;

	const SITE_URL = "all-tyres.ru";

	protected $_curl;

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		$ourBrands = $this->_dbController->GetAllBrands();
		//var_dump($ourBrands);die;

		$curl = $this->GetCurl($this->_urlPattern);
		$rawRes =  iconv('cp1251', 'utf8', curl_exec($curl));

		$simpleHtmlDomParser = new HtmlDomParser();
		$dom = $simpleHtmlDomParser->str_get_html($rawRes);

		$results = [];
		foreach($dom->find(".card") as $div) {

			$rivalTireModel = new RivalTireModel();

			//$title = $div->plaintext;
			//var_dump($title);

			//brand
			$brandRaw = trim($div->find(".card_title a div", 0)->plaintext);

			if (in_array($brandRaw, $ourBrands) == false){
				continue;
			}

			$rivalTireModel->brand = $brandRaw;

			//model
			$modelRaw = trim($div->find(".card_title .model_name", 0)->plaintext);
			$rivalTireModel->model = $modelRaw;

			//width & height
			$specsRawString = $div->find(".card_title .articul", 0)->plaintext;
			$widthAndHeightMatchResult = [];
			preg_match('/(\d+)(?:\/|x|X)(\d+[,.]?\d+|\d+)/is', $specsRawString, $widthAndHeightMatchResult);
			$rivalTireModel->width = isset($widthAndHeightMatchResult[1]) ? $widthAndHeightMatchResult[1] : null;
			$rivalTireModel->height = isset($widthAndHeightMatchResult[2]) ? $widthAndHeightMatchResult[2] : null;

			//diameter
			$rivalTireModel->diameter = $this->GetDiameter($specsRawString);

			//constr
			$rivalTireModel->constructionType = $this->GetConstructionType($specsRawString);

			//speedIndex
			$rivalTireModel->speedIndex = $this->GetSpeedIndex($specsRawString);

			//loadIndex
			$loadIndexPregRes = [];
			preg_match('/\s?(\d+\/\d+|\d+)(?:J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)\s?/', $specsRawString, $loadIndexPregRes);
			$rivalTireModel->loadIndex = isset($loadIndexPregRes[1]) ? $loadIndexPregRes[1] : null;

			//site & url
			$rivalTireModel->url = $this->_urlPattern;

			//price
			$priceRaw = $div->find(".price", 0)->plaintext;
			$rivalTireModel->price = (int)$priceRaw;

			//season
			$seasonDiv = $div->find(".sun_snow", 0);
			if ($seasonDiv) {
				$rivalTireModel->season = SeasonModel::ALL_SEASONS;
			}

			$seasonDiv = $div->find(".snow", 0);
			if ($seasonDiv) {
				$rivalTireModel->season = SeasonModel::WINTER;
			}

			$seasonDiv = $div->find(".snow_spikes", 0);
			if ($seasonDiv) {
				$rivalTireModel->season = SeasonModel::WINTER;
			}

			$seasonDiv = $div->find(".sun", 0);
			if ($seasonDiv) {
				$rivalTireModel->season = SeasonModel::SUMMER;
			}

			//site
			$rivalTireModel->site = self::GetSiteToParseUrl();

			//qty
			$rivalTireModel->quantity = 1;

			//var_dump($rivalTireModel);
			$results[] = $rivalTireModel;

			$div->clear();
			unset($div);

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
		//curl_setopt($this->_curl, CURLOPT_HEADER, true);
		//curl_setopt($this->_curl, CURLOPT_COOKIE, $this->GetResponseCookies());

		return $this->_curl;
	}
}