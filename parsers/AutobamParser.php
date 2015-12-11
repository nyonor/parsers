<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 07.12.15
 * Time: 7:54
 */

use Sunra\PhpSimple\HtmlDomParser;
use sys\Timer;


class AutobamParser extends RivalParserBase implements IProductParametersParser
{
	use ProductParametersParserTrait;
	const SITE_URL = "autobam.ru";
	protected $_curl;
	public $season;

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		$currentSprint = 1;
		$maxSprint = 1;
		$shouldContinue = null;

		$allBrands = $dbController->GetAllBrands();

		$results = [];

		do {

			$shouldContinue = false;

			$url = sprintf($this->_urlPattern, $currentSprint);
			$curl = $this->GetCurl($url);
			$rawRes = curl_exec($curl);

			//Запрошенной страницы не существует.
			$stopRequestsMatchResult = "";
			preg_match('/(Запрошенной страницы не существует.)/', $rawRes, $stopRequestsMatchResult);
			if (count($stopRequestsMatchResult) > 1 && $stopRequestsMatchResult[1] != null) {
				var_dump("PAGE NOT FOUND");
				break;
			}

			$htmlDom = new HtmlDomParser();
			$strHtmlDom = $htmlDom->str_get_html($rawRes);

			if (!method_exists($strHtmlDom, "find")) {
				var_dump("NO FIND METHOD");
				break;
			}

			//нужно ли продолжать?
			foreach($strHtmlDom->find("div.pager") as $anchor) {
				foreach ($anchor->find("a.pager-fast") as $pFast) {
					$text = html_entity_decode($pFast->plaintext);
					if(strpos($text,'>>') !== false) {
						$shouldContinue = true;
						break;
					}
				}
			}

			if($shouldContinue) {
				echo "SHOULD CONTINUE = TRUE";
			}

			foreach($strHtmlDom->find(".main-content .tyres-disks-content table.grided-content") as $div) {

				//brand
				$brandAndModelRawString = $div->find("tr",0)->plaintext;

				//var_dump($brandAndModelRawString);

				$rivalTireModel = new RivalTireModel();
				$rivalTireModel->url = $url;
				$rivalTireModel->site = self::SITE_URL;
				$rivalTireModel->brand = $this->GetBrandWithList($brandAndModelRawString, $allBrands);

				//model
				$modelString = str_ireplace($rivalTireModel->brand,"",$brandAndModelRawString);
				$rivalTireModel->model = !empty($modelString) ? trim($modelString) : null;

				//runflat
				if(stripos($brandAndModelRawString, 'runflat') !== false){
					$rivalTireModel->runFlat = true;
				} else {
					$rivalTireModel->runFlat = false;
				}

				//season
				$rivalTireModel->season = $this->season;

				foreach($div->find("tr.item-info-container") as $dataTag) {

					//id TODO:test
					//$rivalTireModel->id = $dataTag->find("td",0)->plaintext;

					//width
					$widthHeightConstrDiameterSpeedIndexLoadIndex = $dataTag->find("td",1)->plaintext;
					$rivalTireModel->width = $this->GetWidth($widthHeightConstrDiameterSpeedIndexLoadIndex);

					//height
					$rivalTireModel->height = $this->GetHeight($widthHeightConstrDiameterSpeedIndexLoadIndex);

					//construction
					$rivalTireModel->constructionType = $this->GetConstructionType($widthHeightConstrDiameterSpeedIndexLoadIndex);

					//diameter
					$rivalTireModel->diameter = $this->GetDiameter($widthHeightConstrDiameterSpeedIndexLoadIndex);

					//speedIndex
					$rivalTireModel->speedIndex = $this->GetSpeedIndex($widthHeightConstrDiameterSpeedIndexLoadIndex);

					//loadIndex
					preg_match('/(\d+\/\d+)(J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)|(\d+)(J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)/is',
						$widthHeightConstrDiameterSpeedIndexLoadIndex,
						$loadIndexAndSpeedIndexMatchResult);
					$loadIndex = $loadIndexAndSpeedIndexMatchResult[1] != null ? $loadIndexAndSpeedIndexMatchResult[1]
						:$loadIndexAndSpeedIndexMatchResult[3];
					$rivalTireModel->loadIndex = $loadIndex;

					//price
					$priceRawString = $dataTag->find("td span",3)->plaintext;
					$rivalTireModel->price = (int)$priceRawString;

					//qty
					$qtyRawString = $dataTag->find("td",5)->find("span.count_filial_i",0)->plaintext;
					$qtyPregMatchResult = null;
					preg_match('/(\d+)/is',$qtyRawString,$qtyPregMatchResult);
					if (count($qtyPregMatchResult) > 1 ) {
						$rivalTireModel->quantity = $qtyPregMatchResult[1];
					} else {
						$rivalTireModel->quantity = 0;
					}

					//var_dump($rivalTireModel);

				}

				$results[] = $rivalTireModel;

			}


			$strHtmlDom->clear();
			unset($strHtmlDom);
			//die;

			$currentSprint++;

			//sleep(rand(1,3));

		} while ($shouldContinue != null && $shouldContinue == true);

		curl_close($this->_curl);
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