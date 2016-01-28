<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 13.12.15
 * Time: 17:07
 */

use Sunra\PhpSimple\HtmlDomParser;

class SvrautoParser extends RivalParserBase implements IProductParametersParser
{
	const SITE_URL = "svrauto.ru";
	protected $_curl;
	public $season;

	use ProductParametersParserTrait;

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{

		$ourBrands = $this->_dbController->GetAllBrands();

		$rivalBrands = [
			'Aeolus' => 2,
			'Amtel' => 66,
			'Barum' => 78,
			'BFGoodrich' => 83,
			'Cordiant' => 99,
			'Daewoo' => 163,
			'Falken' => 124,
			'Goodride' => 149,
			'Kenda' => 173,
			'Matador' => 108,
			'Matador - Омскшина' => 112,
			'Maxxis' => 132,
			'Mirage' => 186,
			'Nordman' => 115,
			'Rosava' => 72,
			'Satoya' => 199,
			'Sava' => 118,
			'Tigar' => 120,
			'Tunga' => 227,
			'West Lake' => 179,
			'Yokohama' => 128,
			'Белшина' => 80,
			'Волжский шинный завод' => 86,
			'Кировский шинный завод' => 101,
			'Нижнекамский шинный завод' => 81,
			'Омский шинный завод' => 76,
			'Ярославский шинный завод' => 109,
			'Dunlop' => 91,
			'Marshal' => 106,
			'Gislaved' => 87,
			'Continental' => 98,
			'GoodYear' => 90,
			'Pirelli' => 116,
			'Kumho' => 102,
			'Bridgestone' => 82,
			'Michelin' => 111,
			'Nokian Tyres' => 114
		];

		$intermediateBrands = [
			'Кировский ШЗ' => 'Кировский шинный завод'
		];

		$loweredRivalBrands = [];
		foreach($rivalBrands as $k => $v) {

			$loweredRivalBrands[strtolower($k)] = strtolower($v);

		}

		$intermediateBrandsLowered = [];
		foreach($intermediateBrands as $v => $k) {

			$intermediateBrandsLowered[strtolower($k)] = strtolower($v);

		}

		$results = [];
		foreach($ourBrands as $ourBrand) {

			$ourBrandLowered = strtolower($ourBrand);
			$currentBrandID = null;
			$key = null;

			if(array_key_exists($ourBrandLowered, $intermediateBrandsLowered)){

				$key = $intermediateBrandsLowered[$ourBrandLowered];

			} else {

				$key = $ourBrandLowered;

			}

			if(array_key_exists($key,$loweredRivalBrands) == false)
				continue;

			$currentBrandID = $loweredRivalBrands[$key];
			var_dump($currentBrandID);

			$currentSprint = 1;

			do {

				//начинаем
				$maxSprint = null;

				$url = sprintf($this->_urlPattern, $currentBrandID, $currentSprint);
				$curl = $this->GetCurl($url);
				$rawRes = iconv('cp1251', 'utf8', curl_exec($curl));

				$simpleHtmlDomParser = new HtmlDomParser();
				$strHtmlDom = $simpleHtmlDomParser->str_get_html($rawRes);

				if (!method_exists($strHtmlDom, "find")) {

					var_dump("STOPPED!!!");
					break;

				}

				//установка спринтов
				if($maxSprint == null) {

					$maxSprintRawString = $strHtmlDom->find('span.ShuTovarViborPage_VsegoPages', 0)->plaintext;
					if (empty($maxSprintRawString) == true) {

						var_dump("BREAK!!!");
						break;

					}
					$maxSprintPregMatchResArray = null;
					preg_match('/(\d+)/is',$maxSprintRawString,$maxSprintPregMatchResArray);
					$maxSprint = isset($maxSprintPregMatchResArray[1]) ? $maxSprintPregMatchResArray[1] : 0;

				}
				//$maxSprint = 1; //TODO : TEST
				//var_dump($maxSprint);die;


				//пробегаем товары на странице
				foreach($strHtmlDom->find("div.ShuTovarInfoTableContainee") as $div) {

					$rivalTireModel = new RivalTireModel();

					//url
					$rivalTireModel->url = $url;

					//site
					$rivalTireModel->site = $this->GetSiteToParseUrl();

					//brand
					$rivalTireModel->brand = $ourBrand;

					//model
					$modelRawString = $div->find("td.ShuTovarInfoHeaderNaimenovanieTd",0)->plaintext;
					$rivalTireModel->model = trim($modelRawString);

					//width
					$widthHeightConstrDiameter = $div->find(".ShuTovarInfoTiporazmerTd1",0)->plaintext;
					$rivalTireModel->width = trim($this->GetWidth($widthHeightConstrDiameter));

					//hieght
					$rivalTireModel->height = trim($this->GetHeight($widthHeightConstrDiameter));

					//construction
					$rivalTireModel->constructionType = trim($this->GetConstructionType($widthHeightConstrDiameter));

					//diameter
					$rivalTireModel->diameter = trim($this->GetDiameter($widthHeightConstrDiameter));

					//loadIndex
					$loadSpeedIndexesRawString = trim($div->find(".ShuTovarInfoTiporazmerTd2",0)->plaintext);
					$loadIndexRes = $this->GetLoadIndex($loadSpeedIndexesRawString);
					if ($loadIndexRes == null) {

						$rivalTireModel->loadIndex = '';

					} else {

						$rivalTireModel->loadIndex = $loadIndexRes;

					}

					//speedIndex
					$speedIndexPregMatchResultArr = null;
					preg_match('/(?:\d+)?(J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)/is',$loadSpeedIndexesRawString,
						$speedIndexPregMatchResultArr);
					$speedIndexRes = isset($speedIndexPregMatchResultArr[1]) ? $speedIndexPregMatchResultArr[1] : '';
					$rivalTireModel->speedIndex = $speedIndexRes;

					//qty
					$qtyRawString = $div->find(".ShuTovarInfoNalichieTd",0)->plaintext;
					$qtyPregMatchResArr = null;
					preg_match('/(\d+)/is',$qtyRawString,$qtyPregMatchResArr);
					$rivalTireModel->quantity = $qtyPregMatchResArr[1];

					//price
					$priceRawString = $div->find(".ShuTovarInfoTsenaTd",0)->plaintext;
					$pricePregMatchResArr = null;
					preg_match('/(\d+)/is',$priceRawString,$pricePregMatchResArr);
					$rivalTireModel->price = $pricePregMatchResArr[1];

					//season
					$rivalTireModel->season = $this->season;

					//var_dump($rivalTireModel);

					$results[] = $rivalTireModel;
				}
				$currentSprint++;

			} while ($maxSprint >= $currentSprint);

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