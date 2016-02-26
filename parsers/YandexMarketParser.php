<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 18.02.16
 * Time: 11:24
 */

use Sunra\PhpSimple\HtmlDomParser;

class YandexMarketParser extends RivalParserBase implements IProductParametersParser
{

	use ProductParametersParserTrait;

	protected $_curl;
	/**
	 * @var HtmlDomParser
	 */
	protected $_htmlDomParser;

	protected $_allTypeSizes;

	const SITE_URL = "market.yandex.ru";
	const MAIN_URL_PART = "https://market.yandex.ru";
	const TYPESIZE_PAGE_SORT_BY_PRICE_PARAM = "&how=aprice";

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		$ourBrands = $this->_dbController->GetAllBrands();
		$allTypeSizes = $this->_dbController->GetAllTypeSizes();
		$this->_allTypeSizes = $allTypeSizes;

		$fakeBrands = ['Dunlop'];

		sleep(rand(12,19));

		//изначально заходим на страницу со списками брендов ЯндексМаркета
		$curl = $this->GetCurl($this->_urlPattern);
		$rawRes = curl_exec($curl);

		//print_r($rawRes);

		//страница со списком брендов
		$htmlDomParser = new HtmlDomParser();
		$this->_htmlDomParser = $htmlDomParser;
		$brandsPageDom = $htmlDomParser->str_get_html($rawRes);

		//бежим по НАШИМ брендам
		foreach($fakeBrands as $brandName) { //todo вместо $fakeBrands должны быть $ourBrands

			//ищем в списке брендов ЯндексМаркета НАШ бренд
			foreach($brandsPageDom->find(".b-vendor__item a") as $vendorAnchorDomElement) {

				//нашли!
				if ($vendorAnchorDomElement->plaintext == $brandName) {

					//пройдем по ссылке на бренд в раздел моделей
					$brandHref = $vendorAnchorDomElement->href;

					sleep(rand(3,7));

					$this->ParseModelsPage($brandHref);
					MyLogger::WriteToLog("Brand complete!", LOG_ERR);

					die; //todo УБРАТЬ в РЕЛИЗЕ или при финальном тестировании
				}

			}

			sleep(rand(12,18));

		}


	}

	protected function ParseModelsPage($brandHref) {

		//первый запрос к моделям для получения эффективного url
		$curl = $this->GetCurl(self::MAIN_URL_PART . $brandHref);
		$firstModelRawRes = curl_exec($curl);
		$effectiveUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);

		$partOfUrlPattern = $effectiveUrl;
		$currentPage = 1;
		$shouldContinue = false;

		$firstModelName = null;

		//бежим по страницам моделей бренда
		do {

			sleep(rand(12, 20));

			$modelsDom = null;
			//если это не первый запрос к странице моделей
			if ($firstModelRawRes == null) {

				$url = $partOfUrlPattern.(sprintf("&page=%d", $currentPage));
				MyLogger::WriteToLog($url,LOG_ERR);//die;
				$curl = $this->GetCurl($url);
				$rawRes = curl_exec($curl);
				$modelsDom = $this->_htmlDomParser->str_get_html($rawRes);

			} else {

				$modelsDom = $this->_htmlDomParser->str_get_html($firstModelRawRes);

			}

			//бежим по карточкам моделей
			foreach($modelsDom->find(".snippet-card") as $modelCardDom) {

				$modelNameTrimmed = trim($modelCardDom->find(".snippet-card__header-text",0)->plaintext);

				MyLogger::WriteToLog($modelNameTrimmed, LOG_ERR);

				//нужно ли продолжать...?
				if ($modelNameTrimmed == $firstModelName) {

					$shouldContinue = false;
					break;

				} else {

					$shouldContinue = true;

				}

				//цикл типоразмеров
				$typeSizeHref = $modelCardDom->find(".snippet-card__action a",0)->href;
				$this->ParseTypeSizePage($typeSizeHref);


				die; //todo УБРАТЬ в РЕЛИЗЕ или при финальном тестировании

			}

			$firstModelName = trim($modelsDom->find(".snippet-card__header-text",0)->plaintext);

			MyLogger::WriteToLog("PAGE NUM " . $currentPage, LOG_ERR);

			$firstModelRawRes = null;

			$currentPage++;

		} while ($shouldContinue == true);

	}

	public function ParseTypeSizePage($typeSizeHref) {

		$yandexDiameterParams = [
			13 => '&gfilter=2142418420%3A-5066797',
			14 => '&gfilter=2142418420%3A-5066798',
			15 => '&gfilter=2142418420%3A-5066799',
			16 => '&gfilter=2142418420%3A-5066800',
			17 => '&gfilter=2142418420%3A-5066801',
			18 => '&gfilter=2142418420%3A-5066802'
		];

		$yandexWidthParams = [
			155 => '&gfilter=2142418424%3A-5114008',
			175 => '&gfilter=2142418424%3A-5114070',
			185 => '&gfilter=2142418424%3A-5114101',
			195 => '&gfilter=2142418424%3A-5114132',
			205 => '&gfilter=2142418424%3A-5114814',
			215 => '&gfilter=2142418424%3A-5114845',
			225 => '&gfilter=2142418424%3A-5114876',
			235 => '&gfilter=2142418424%3A-5114907',
			245 => '&gfilter=2142418424%3A-5114938'
		];

		$yandexHeightParams = [
			40 => '&gfilter=2142418422%3A-5066885',
			45 => '&gfilter=2142418422%3A-5066890',
			50 => '&gfilter=2142418422%3A-5066916',
			55 => '&gfilter=2142418422%3A-5066921',
			60 => '&gfilter=2142418422%3A-5066947',
			65 => '&gfilter=2142418422%3A-5066952',
			70 => '&gfilter=2142418422%3A-5066978'
		];

		$yandexSpeedIndexParams = [
			"H" => '&gfilter=2142418412%3A-2130161133',
			"W" => '&gfilter=2142418412%3A-840561910',
			"Y" => '&gfilter=2142418412%3A-1561626688',
			"J" => '&gfilter=2142418412%3A-914941583',
			"K" => '&gfilter=2142418412%3A-129763569',
			"L" => '&gfilter=2142418412%3A-1174368721',
			"M" => '&gfilter=2142418412%3A-2076093423',
			"N" => '&gfilter=2142418412%3A-1031488271',
			"P" => '&gfilter=2142418412%3A-1494434928',
			"Q" => '&gfilter=2142418412%3A-449829776',
			"R" => '&gfilter=2142418412%3A-594875376',
			"S" => '&gfilter=2142418412%3A-1639480528',
			"T" => '&gfilter=2142418412%3A-1610981616',
			"V" => '&gfilter=2142418412%3A-1600553608',
			"Z" => '&gfilter=2142418412%3A-1694507230'
		];

		$yandexLoadIndexPattern = "%d~%d";
		$yandexLoadIndex = "&gfilter=2142418414%3A";

		/*
		 * Начнем опрашивать страницу типоразмеров
		 */

		$url = self::MAIN_URL_PART . $typeSizeHref . self::TYPESIZE_PAGE_SORT_BY_PRICE_PARAM;
		var_dump($url);





	}

	/**
	 * Возвращает url сайта для парсинга
	 * @return string
	 */
	public function GetSiteToParseUrl()
	{
		// TODO: Implement GetSiteToParseUrl() method.
	}

	/**
	 * Возвращает готовый объект curl для запросов
	 * @param $url
	 * @return resource
	 */
	protected function GetCurl($url)
	{
		//$x = fopen("files/cookies.txt", 'r');die;

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
		curl_setopt($this->_curl, CURLOPT_COOKIEJAR, "files/cookies.txt");
		curl_setopt($this->_curl, CURLOPT_COOKIEFILE, "files/cookies.txt");
		//curl_setopt($this->_curl, CURLOPT_PROXY, '37.143.8.59:81');
		//curl_setopt($this->_curl, CURLOPT_HEADER, true);
		//curl_setopt($this->_curl, CURLOPT_COOKIE, $this->GetResponseCookies());

		return $this->_curl;
	}
}