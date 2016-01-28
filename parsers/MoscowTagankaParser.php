<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 11.01.16
 * Time: 15:44
 */

use Sunra\PhpSimple\HtmlDomParser;

class MoscowTagankaParser extends RivalParserBase implements IProductParametersParser
{
	use ProductParametersParserTrait;
	const SITE_URL = "moscow.taganka.biz";
	const REFERRER_URL_PATTERN = "http://moscow.taganka.biz/";
	protected $_curl;

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		$maxCycles = 0;
		$currentCycle = 1;

		$ourBrands = $this->_dbController->GetAllBrands();

		$results = [];

		do {

			$formData = [
				'action' => 'tyres_by_params',
				'price_min' => 615,
				'price_max' => 28660,
				'order_by' => 'popular_desc',
				'from_row' => $currentCycle * 20
			];
			$curl = $this->GetCurlAdvanced($this->_urlPattern, self::REFERRER_URL_PATTERN, $formData);
			$rawRes = curl_exec($curl);
			$jsonData = json_decode($rawRes);

			if (count($jsonData->items) == 0) {
				$shouldContinue = false;
			} else {
				$shouldContinue = true;
			}

			//var_dump($jsonData);die;
			foreach ($jsonData->items as $jsonObj) {

				$rivalModel = new RivalTireModel();

				$rivalModel->price = $jsonObj->price_out;

				$formDataForItem = [
					'action' => 'get_item',
					'type' => 'tyres',
					'code' => $jsonObj->code,
					'city_only' => 0,
					'sort' => 20
				];

				$nextCurl = $this->GetCurlAdvanced($this->_urlPattern, self::REFERRER_URL_PATTERN, $formDataForItem);

				$itemRawRes = curl_exec($nextCurl);
				//echo($itemRawRes);die;

				//разбираем html
				$htmlDomParser = new HtmlDomParser();
				$strHtmlDom = $htmlDomParser->str_get_html($itemRawRes);
				if (!method_exists($strHtmlDom,"find"))
				{
					break;
				}

				$rawTitle = $strHtmlDom->find(".name_item",0)->plaintext;
				//var_dump($rawTitle);//die;

				//brand
				$brand = $this->GetBrandWithList($rawTitle, $ourBrands);
				$rivalModel->brand = $brand;

				if (isset($brand) == false || empty($brand)) {
					continue;
				}

				//model
				$modelPregMatchResult = [];
				preg_match('/([^\n]+)/', str_ireplace($brand,'',$rawTitle), $modelPregMatchResult);
				$rivalModel->model = isset($modelPregMatchResult[1]) ? trim($modelPregMatchResult[1]) : null;

				//width
				$rivalModel->width = $this->GetWidth($rawTitle);

				//height
				$rivalModel->height = $this->GetHeight($rawTitle);

				//diameter
				$rivalModel->diameter = $this->GetDiameter($rawTitle);

				//constr
				$rivalModel->constructionType = $this->GetConstructionType($rawTitle);

				//speedIndex
				$speedIndexPregMatchRes = [];
				preg_match('/\s?(?:\d+\/\d+|\d+)(J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)\s?/', $rawTitle,
					$speedIndexPregMatchRes);
				$rivalModel->speedIndex = isset($speedIndexPregMatchRes[1]) ? $speedIndexPregMatchRes[1] : null;

				//loadIndex
				$loadIndexPregMatchRes = [];
				preg_match('/\s?(\d+\/\d+|\d+)(?:J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)\s?/', $rawTitle,
					$loadIndexPregMatchRes);
				$rivalModel->loadIndex = isset($loadIndexPregMatchRes[1]) ? $loadIndexPregMatchRes[1] : null;

				//site & url
				$rivalModel->site = $this->GetSiteToParseUrl();
				$rivalModel->url = http_build_query($formDataForItem);

				//season & runflat
				foreach ($strHtmlDom->find(".sticker") as $img) {

					switch($img->alt) {

						case("winter.png"):
							$rivalModel->season = SeasonModel::WINTER;
							break;
						case("summer.png"):
							$rivalModel->season = SeasonModel::SUMMER;
							break;
						case("run_flat.png"):
							$rivalModel->runFlat = true;

					}

				}

				//quantity
				$divText = $strHtmlDom->find(".answer",0)->plaintext;
				//var_dump($divText);
				if (stripos($divText, 'Есть') === false ) {
					$rivalModel->quantity = 0;
				} else {
					$rivalModel->quantity = 1;
				}

				//var_dump($rivalModel);//die;

				//sleep(1);

				$results[] = $rivalModel;
			}

			$currentCycle++;

		} while ($shouldContinue && $maxCycles == 0 || ($shouldContinue && $currentCycle < $maxCycles));

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
		if (isset($this->_curl)) {
			curl_close($this->_curl);
		}
		return curl_init($url);
	}

	protected  function GetCurlAdvanced($url, $referrerUrl, $formDataArray)
	{
		$curl = $this->GetCurl($url);
		curl_setopt($curl, CURLOPT_COOKIE, "");
		curl_setopt($curl, CURLOPT_REFERER, $referrerUrl);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formDataArray));
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$this->_curl = $curl;
		return $this->_curl;
	}
}