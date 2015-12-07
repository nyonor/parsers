<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 12.11.15
 * Time: 18:06
 */

use Sunra\PhpSimple\HtmlDomParser;

class KolesaDaromParser extends RivalParserBase implements IParserAdvanced
{
	const SITE_URL = "kolesa-darom.ru";
	protected $_curl;
	public $season;

	use ProductParametersParserTrait; //эта примесь реализует дефолтное поведение IParserAdvanced

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		$this->SetIDbController($dbController);
		//var_dump(file_get_contents("http://www.kolesa-darom.ru/nn/shiny/letnie/?cur_cc=15772&recNum=50&curPos=0"));die;
		//организуем цикл пробега курлом по url
		$currentSprint = 1;
		$maxSprint = 0;
		$brands = $this->_dbController->GetAllBrands();
		$result = [];

		do {
			$urlString = sprintf($this->_urlPattern, $currentSprint * 50);

			$curl = $this->GetCurl($urlString);
			$rawRes =  iconv('cp1251', 'utf8', curl_exec($curl));
			curl_close($curl);
			//print_r($rawRes);//die;

			$htmlDom = new HtmlDomParser();
			$strHtmlDom = $htmlDom->str_get_html($rawRes);

			if (!method_exists($strHtmlDom,"find"))
			{
				//$currentSprint++;
				//continue;
				break;
			}

			if (empty($strHtmlDom->find('.offer', 0)) == true) {
				var_dump("BREAK!");
				break;
			}

			foreach($strHtmlDom->find('.offer') as $divOffer) {
				$rivalTireModel = new RivalTireModel();
				$rivalTireModel->url = $urlString;
				$rivalTireModel->site = self::SITE_URL;

				//бренд
				$brandAndModelRawString = trim($divOffer->find('.product-info-top h3 a',0)->plaintext);
				$brand = $this->GetBrandWithListWithAddition($brandAndModelRawString, $brands);
				if(empty($brand)) {
					continue;
				}
				$rivalTireModel->brand = $brand;
				//echo "<br/>".$brandAndModelRawString;

				//модель
				$modelString = str_replace($rivalTireModel->brand,"",$brandAndModelRawString);
				$rivalTireModel->model = !empty($modelString) ? trim($modelString) : null;

				//ширина
				$widthHeightDiameterConstructionString = $divOffer->find(".offer-table .offer-table-item td",0)->plaintext;
				$width = $this->GetWidth($widthHeightDiameterConstructionString);
				$rivalTireModel->width = $width;
				//echo "<br/>".$widthHeightDiameterConstructionString;

				//профиль
				$height = $this->GetHeight($widthHeightDiameterConstructionString);
				$rivalTireModel->height = $height;

				//конструкция
				$construction = $this->GetConstructionType($widthHeightDiameterConstructionString);
				$rivalTireModel->constructionType = $construction;

				//диаметер
				$diameter = $this->GetDiameter($widthHeightDiameterConstructionString);
				$rivalTireModel->diameter = $diameter;

				//индекс нагрузки
				$loadSpeedIndexString = preg_replace('/&#?[a-z0-9]+;/i',"",$divOffer->find(".offer-table .offer-table-item td",1)->plaintext);
				//var_dump($loadSpeedIndexString);
				$loadIndex = $this->GetLoadIndex($loadSpeedIndexString);
				$rivalTireModel->loadIndex = $loadIndex;

				//индекс скорости
				$speedIndex = $this->GetSpeedIndex($loadSpeedIndexString);
				$rivalTireModel->speedIndex = $speedIndex;

				//сезон
				$season = $this->GetSeason("");
				$rivalTireModel->season = $season;

				//цена
				$price = (int)$divOffer->find(".offer-table .offer-table-item td",4)->plaintext;
				$rivalTireModel->price = $price;

				//количество
				$qty = (int)$divOffer->find(".offer-table .offer-table-item td",2)->plaintext +
					(int)$divOffer->find(".offer-table .offer-table-item td",3)->plaintext;
				$rivalTireModel->quantity = $qty;

				//var_dump($rivalTireModel);

				$result[] = $rivalTireModel;
			}

			//print_r($rawRes);

			//подождем
			sleep(rand(5,10));

			$currentSprint++;
			//echo "!!!!!!".$currentSprint."!!!!!!";
		} while ($currentSprint < $maxSprint || $maxSprint == 0 );

		return $result;

	}

	/**
	 * В данном случае сезон устанавливается извне
	 * @param $subject
	 * @return mixed
	 */
	public function GetSeason($subject) {
		return $this->season;
	}

	public function GetLoadIndex($subject) {
		$loadIndexMatchResult = "";
		preg_match('/(J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)?\s?(\d+\/\d+|\d+)/is', $subject, $loadIndexMatchResult);
		return $loadIndexMatchResult[2];
	}

	public function GetSpeedIndex($subject) {
		$loadIndexMatchResult = "";
		preg_match('/(J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)?\s?(\d+\/\d+|\d+)/is', $subject, $loadIndexMatchResult);
		return $loadIndexMatchResult[1];
	}

	protected function GetBrandWithListWithAddition($brandAndModelRawString, $brands) {
		$brands[] = "BF GOODRICH"; // у них bfgoodrich пишется раздельно
		return $this->GetBrandWithList($brandAndModelRawString, $brands);
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
	protected function GetCurl($url) {

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
		curl_setopt($this->_curl, CURLOPT_COOKIE, $this->GetResponseCookies());

		return $this->_curl;
	}


}