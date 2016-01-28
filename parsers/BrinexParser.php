<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 21.01.16
 * Time: 10:36
 */

use Sunra\PhpSimple\HtmlDomParser;

class BrinexParser extends RivalParserBase implements IProductParametersParser
{

	use ProductParametersParserTrait;

	const SITE_URL = "brinex.ru";

	protected $_curl;


	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		$allBrands = $this->_dbController->GetAllBrands();

		$curl = $this->GetCurl($this->_urlPattern);
		$rawRes = iconv('cp1251', 'utf8', curl_exec($curl));
		//print_r($rawRes);
		//die;
		$simpleHtmlParser = new HtmlDomParser();
		$htmlDom = $simpleHtmlParser->str_get_html($rawRes);
		$results = [];

		foreach($htmlDom->find(".offer") as $div) {

			$rivalModel = new RivalTireModel();

			//brand
			$brandAndModelRawStr = $div->find(".product-info-top a", 0)->plaintext;
			$rivalModel->brand = $this->GetBrandWithList($brandAndModelRawStr, $allBrands);

			if ($rivalModel->brand == null)
				continue;

			//var_dump($div->find(".offer-table-item td",0)->plaintext);

			//model
			$modelRawStr = str_ireplace($rivalModel->brand, '', $brandAndModelRawStr);
			$rivalModel->model = trim($modelRawStr);

			//width
			$modelHeightContrDiam = $div->find(".offer-table-item td",0)->plaintext;
			$rivalModel->width = $this->GetWidth($modelHeightContrDiam);

			//height
			$rivalModel->height = $this->GetHeight($modelHeightContrDiam);

			//diam
			$rivalModel->diameter = $this->GetDiameter($modelHeightContrDiam);

			//constr
			$rivalModel->constructionType = $this->GetConstructionType($modelHeightContrDiam);

			//site&url
			$rivalModel->site = $this->GetSiteToParseUrl();
			$rivalModel->url = $this->_urlPattern;

			//speedIndex
			$speedAndLoadIndexesRaw = $div->find(".offer-table-item td", 1)->plaintext;
			$withoutNbsp = str_replace("&nbsp;", '', $speedAndLoadIndexesRaw);
			$rivalModel->speedIndex = $this->GetSpeedIndex($withoutNbsp);

			//loadIndex
			$rivalModel->loadIndex = $this->GetLoadIndex($withoutNbsp);

			//season
			if($div->find(".sprt-sneg", 0)) {
				$rivalModel->season = SeasonModel::WINTER;
			}

			if($div->find(".sprt-solnce", 0)) {
				$rivalModel->season = SeasonModel::SUMMER;
			}

			//qty
			$atHands = (int)$div->find(".offer-table-item td", 2)->plaintext;
			$atStore = (int)$div->find(".offer-table-item td", 3)->plaintext;
			$rivalModel->quantity = $atHands + $atStore;

			//price
			$price = (int)$div->find(".offer-table-item td", 4)->plaintext;
			$rivalModel->price = $price;

			//var_dump($rivalModel);die;

			$results[] = $rivalModel;
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
		curl_setopt($this->_curl, CURLOPT_COOKIE, $this->GetResponseCookies());

		return $this->_curl;
	}
}