<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 18.02.16
 * Time: 11:24
 */

// todo данный класс должен наследовать (или как тут принято говорить расширяться) от расширенного класса RivalParserBase! Так как возвращает иной тип!

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
	protected $_currentParsingBrand;
	protected $_currentParsingModel;

	/**
	 * @var TireModelMinPriceInfo[]
	 */
	protected $_results;

	/**
	 * @var ProductTireModel[]
	 */
	protected $_ourTyresByCurrentModel;

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

		//var_dump($ourBrands);die;

		//$fakeBrands = ['Dunlop'];

		sleep(rand(12,19));

		//изначально заходим на страницу со списками брендов ЯндексМаркета
		$curl = $this->GetCurl($this->_urlPattern);
		$rawRes = curl_exec($curl);

		//print_r($rawRes);die;

		//страница со списком брендов
		$htmlDomParser = new HtmlDomParser();
		$this->_htmlDomParser = $htmlDomParser;
		$brandsPageDom = $htmlDomParser->str_get_html($rawRes);

		$results = [];

		//бежим по НАШИМ брендам
		//foreach($fakeBrands as $brandName) { //todo вместо $fakeBrands должны быть $ourBrands
		foreach($ourBrands as $brandName) {

			$brandName = ucfirst(strtolower($brandName));

			if ($brandName == "Bfgoodrich") {
				$brandName = "BFGoodrich";
			}

			//ищем в списке брендов ЯндексМаркета НАШ бренд
			foreach($brandsPageDom->find(".b-vendor__item a") as $vendorAnchorDomElement) {

				//нашли!
				if ($vendorAnchorDomElement->plaintext == $brandName) {

					MyLogger::WriteToLog($brandName . " " . "WAS FOUND on yandex-market!!!", LOG_ERR);

					//текущий бренд парсинга
					$this->_currentParsingBrand = $brandName;

					//пройдем по ссылке на бренд в раздел моделей
					$brandHref = $vendorAnchorDomElement->href;

					//var_dump($brandName);die;

					sleep(rand(20, 30));

					$this->ParseModelsPage($brandHref);
					MyLogger::WriteToLog("Brand " . $this->_currentParsingBrand . " complete!", LOG_ERR);
					$this->_currentParsingBrand = null;

					//return $this->_results; //todo УБРАТЬ в РЕЛИЗЕ или при финальном тестировании
				}/* else {

					MyLogger::WriteToLog($brandName. " " . "not found on yandex-market..", LOG_ERR);

				}*/

			}

			sleep(rand(22,31));

		}

		return $this->_results;
	}

	protected function ParseModelsPage($brandHref) {

		//первый запрос к моделям для получения эффективного url
		$curl = $this->GetCurl(self::MAIN_URL_PART . $brandHref);
		$firstModelRawRes = curl_exec($curl);

		//print_r($firstModelRawRes);die; //todo TEST ON RELEASE

		$effectiveUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);

		$partOfUrlPattern = $effectiveUrl;
		$currentPage = 1;
		$shouldContinue = false;

		$firstModelName = null;
		//бежим по страницам моделей бренда
		do {

			sleep(rand(20, 30));

			$modelsDom = null;
			//если это не первый запрос к странице моделей
			if ($firstModelRawRes == null) {

				$url = $partOfUrlPattern.(sprintf("&page=%d", $currentPage));
				//MyLogger::WriteToLog($url,LOG_ERR);//die;
				$curl = $this->GetCurl($url);
				$rawRes = curl_exec($curl);
				$modelsDom = $this->_htmlDomParser->str_get_html($rawRes);

			} else {

				$modelsDom = $this->_htmlDomParser->str_get_html($firstModelRawRes);

			}

			//$stopIndex = 1; //todo удалить после теста...  для начала пройдем хотябы две модели...
			//бежим по карточкам моделей
			foreach($modelsDom->find(".snippet-card") as $modelCardDom) {

				/**
				 * @var $modelCardDom HtmlDomParser
				 */
				$modelNameTrimmed = trim($modelCardDom->find(".snippet-card__header-text",0)->plaintext);

				MyLogger::WriteToLog("Doing model..." . $modelNameTrimmed, LOG_ERR);

				//отделим название модели от бренда и сохраним текущую модель парсинга
				$this->_currentParsingModel = trim(str_ireplace($this->_currentParsingBrand, '',
					str_replace("&nbsp;", '', $modelNameTrimmed)));
				MyLogger::WriteToLog("Чистая модель ".$this->_currentParsingModel, LOG_ERR);

				//нужно ли продолжать...?
				if ($modelNameTrimmed == $firstModelName) {

					$shouldContinue = false;
					break;

				} else {

					$shouldContinue = true;

				}

				$ourTires = $this->_dbController
					->FindTireByModelAndBrand(null, $this->_currentParsingModel);

				//если у нас есть такие модели
				if (count($ourTires) > 0) {

					$this->_ourTyresByCurrentModel = $ourTires;
					//цикл типоразмеров
					$typeSizeHref = $modelCardDom->find(".snippet-card__action a", 0)->href;
					$this->ParseTypeSizePage($typeSizeHref);
					$this->_ourTyresByCurrentModel = null;
					MyLogger::WriteToLog("MODEL " . $this->_currentParsingModel . " is DONE!", LOG_ERR);
					$this->_currentParsingModel = null;

				} else {

					MyLogger::WriteToLog("MODEL " . $this->_currentParsingModel . " NOT found on yandex-market...", LOG_ERR);
					$this->_currentParsingModel = null;
				}

				//return; //todo УБРАТЬ в РЕЛИЗЕ или при финальном тестировании

				/*
				//todo Удалить после тестирования
				$stopIndex++;
				if ($stopIndex > 2) {
					MyLogger::WriteToLog("Две модели пройдены!!!!", LOG_ERR);
					die;
				}
				*/

			}

			$firstModelName = trim($modelsDom->find(".snippet-card__header-text",0)->plaintext);

			MyLogger::WriteToLog("Model PAGE NUM " . $currentPage, LOG_ERR);

			$firstModelRawRes = null;

			$currentPage++;

		} while ($shouldContinue == true);

	}

	public function ParseTypeSizePage($typeSizeHref) {

		$yandexDiameterParams = [
			12 => '&gfilter=2142418420%3A-5066796',
			13 => '&gfilter=2142418420%3A-5066797',
			14 => '&gfilter=2142418420%3A-5066798',
			15 => '&gfilter=2142418420%3A-5066799',
			16 => '&gfilter=2142418420%3A-5066800',
			16.5 => '&gfilter=2142418420%3A-6578359',
			17 => '&gfilter=2142418420%3A-5066801',
			18 => '&gfilter=2142418420%3A-5066802',
			19 => '&gfilter=2142418420%3A-5066803',
			20 => '&gfilter=2142418420%3A-5066825',
			21 => '&gfilter=2142418420%3A-5066826',
			22 => '&gfilter=2142418420%3A-5066827',
			23 => '&gfilter=2142418420%3A-5066828',
			24 => '&gfilter=2142418420%3A-5066829',
			25 => '&gfilter=2142418420%3A-5066830',
			26 => '&gfilter=2142418420%3A-5066831',
			28 => '&gfilter=2142418420%3A-5066833',
			30 => '&gfilter=2142418420%3A-5066856',
		];

		$yandexWidthParams = [
			125 => '&gfilter=2142418424%3A-5113915',
			135 => '&gfilter=2142418424%3A-5113946',
			145 => '&gfilter=2142418424%3A-5113977',
			155 => '&gfilter=2142418424%3A-5114008',
			165 => '&gfilter=2142418424%3A-5114039',
			175 => '&gfilter=2142418424%3A-5114070',
			185 => '&gfilter=2142418424%3A-5114101',
			195 => '&gfilter=2142418424%3A-5114132',
			205 => '&gfilter=2142418424%3A-5114814',
			215 => '&gfilter=2142418424%3A-5114845',
			225 => '&gfilter=2142418424%3A-5114876',
			235 => '&gfilter=2142418424%3A-5114907',
			245 => '&gfilter=2142418424%3A-5114938',
			255 => '&gfilter=2142418424%3A-5114969',
			265 => '&gfilter=2142418424%3A-5115000',
			275 => '&gfilter=2142418424%3A-5115031',
			285 => '&gfilter=2142418424%3A-5115062',
			295 => '&gfilter=2142418424%3A-5115093',
			305 => '&gfilter=2142418424%3A-5115775',
			315 => '&gfilter=2142418424%3A-5115806',
			325 => '&gfilter=2142418424%3A-5115837',
			335 => '&gfilter=2142418424%3A-5115868',
			345 => '&gfilter=2142418424%3A-5115899',
			355 => '&gfilter=2142418424%3A-5115930',
			365 => '&gfilter=2142418424%3A-5115961',
			375 => '&gfilter=2142418424%3A-5115992',
			385 => '&gfilter=2142418424%3A-5116023',
			395 => '&gfilter=2142418424%3A-5116054',
			405 => '&gfilter=2142418424%3A-5116736',
			455 => '&gfilter=2142418424%3A-5116891'
		];

		$yandexHeightParams = [
			25 => '&gfilter=2142418422%3A-5066828',
			30 => '&gfilter=2142418422%3A-5066854',
			35 => '&gfilter=2142418422%3A-5066859',
			40 => '&gfilter=2142418422%3A-5066885',
			45 => '&gfilter=2142418422%3A-5066890',
			50 => '&gfilter=2142418422%3A-5066916',
			55 => '&gfilter=2142418422%3A-5066921',
			60 => '&gfilter=2142418422%3A-5066947',
			65 => '&gfilter=2142418422%3A-5066952',
			70 => '&gfilter=2142418422%3A-5066978',
			75 => '&gfilter=2142418422%3A-5066983',
			80 => '&gfilter=2142418422%3A-5067009',
			85 => '&gfilter=2142418422%3A-5067014',
			90 => '&gfilter=2142418422%3A-5067040',
			95 => '&gfilter=2142418422%3A-5067045',
			105 => '&gfilter=2142418422%3A-5113855'
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

		$yandexSpikesParams = [
			true => '&gfilter=2142418415%3Aselect',
			false => '&gfilter=2142418415%3Aexclude'
		];

		$yandexSeasonParams = [
			SeasonModel::ALL_SEASONS => '&gfilter=2142418426%3A-2022570288',
			SeasonModel::WINTER => '&gfilter=2142418426%3A-1861921444',
			SeasonModel::SUMMER => '&gfilter=2142418426%3A-1973846231'
		];

		$yandexRunflatParam = '&gfilter=2142418417%3Aselect';

		$yandexLoadIndexPattern = "%d~%d";
		$yandexLoadIndex = "&gfilter=2142418414%3A";

		/*
		 * Начнем опрашивать страницу типоразмеров
		 */

		//var_dump($this->_ourTyresByCurrentModel);die;

		//бежим по нашим типоразмерам в зависимости от текущего бренда и модели
		foreach($this->_ourTyresByCurrentModel as $ourTire) {

			/*
			 * формируем url на основе данных модели
			 * и данных по параметрам запроса yandexMarket
			 */

			$url = self::MAIN_URL_PART . $typeSizeHref . self::TYPESIZE_PAGE_SORT_BY_PRICE_PARAM;

			//диаметр
			$url .= $yandexDiameterParams[(real)$ourTire->diameter];

			//ширина
			$url .= $yandexWidthParams[(real)$ourTire->width];

			//высота (профиль)
			$url .= $yandexHeightParams[(real)$ourTire->height];

			//индекс скорости
			$si = str_replace(')','',str_replace('(','',strtoupper($ourTire->speedIndex)));
			$url .= $yandexSpeedIndexParams[$si];

			//нагрузка
			$fromAndTo = explode('/',$ourTire->loadIndex);
			$url .= count($fromAndTo) > 1 ?
				$yandexLoadIndex . sprintf($yandexLoadIndexPattern, $fromAndTo[1],$fromAndTo[0]) :
				$yandexLoadIndex . sprintf($yandexLoadIndexPattern, $fromAndTo[0], $fromAndTo[0]);

			MyLogger::WriteToLog("Trying typesize by url " . $url, LOG_ERR);

			/*
			 * Cпарсим дополнительные данные:
			 * Мин. цену и магазин
			 */

			sleep(rand(20, 32));

			$curl = $this->GetCurl($url);
			$rawRes = curl_exec($curl);
			//print_r($rawRes);//die;

			$typeSizeDom = $this->_htmlDomParser->str_get_html($rawRes);

			$div = $typeSizeDom->find(".snippet-card",1); //Это важно! (1),
			// так как первым результатом яндекс маркет выдает совершенно "левый" результат.
			// Видимо проплаченный

			if ($div != null) {

				$minModel = new TireModelMinPriceInfo();

				//парсим минимальную цену
				$minPriceTrimmedText = $div->find(".snippet-card__price", 0)->plaintext;
				$matches = [];
				preg_match_all('/(\d)/', $minPriceTrimmedText, $matches);
				$minModel->minimalPrice = (int)implode('',$matches[1]);

				//парсим url магазина!
				$rivalStoreTemporaryPartUrl = $div->find(".snippet-card__content .snippet-card__shop a", 0)->href;
				//$minModel->rivalStoreUrl = $this->GetRealUrl("https:".$rivalStoreTemporaryPartUrl, $url);

				$minModel->rivalStoreUrl = $rivalStoreTemporaryPartUrl;

				//парсим название магазина!
				$minModel->rivalStoreName = $div->find(".snippet-card__content .snippet-card__shop a", 0)->plaintext;

				//текущий url яндекс маркета
				$minModel->yandexMarketUrl = $url;

				//скопируем все остальные свойства на всякий случай
				$minModel->cae = $ourTire->cae;
				$minModel->brand = $ourTire->brand;
				$minModel->constructionType = $ourTire->constructionType;
				$minModel->diameter = $ourTire->diameter;
				$minModel->height = $ourTire->height;
				$minModel->width = $ourTire->width;
				$minModel->loadIndex = $ourTire->loadIndex;
				$minModel->runFlat = $ourTire->runFlat;
				$minModel->speedIndex = $ourTire->speedIndex;
				$minModel->season = $ourTire->season;
				$minModel->model = $ourTire->model;

				MyLogger::WriteToLog($ourTire->cae . " ...DONE!!!", LOG_ERR);
				MyLogger::WriteToLog(json_encode($minModel), LOG_ERR);

				$this->_results[] = $minModel;

			} else {

				MyLogger::WriteToLog($ourTire->cae . "... CANT FIND!!!", LOG_ERR);

			}
			//return; //todo TEST!
		}
	}

	public function GetRealUrl($url, $referrer) {

		$curl = $this->GetCurlWithReferrer($url, $referrer);
		$rawRes = curl_exec($curl);
		print_r($rawRes);
		return curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);

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

	protected function GetCurlWithReferrer($url, $referrer) {

		$curl = $this->GetCurl($url);
		curl_setopt($curl, CURLOPT_REFERER, $referrer);
		return $curl;

	}
}