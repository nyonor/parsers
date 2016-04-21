<?php
use models\YMModelDetailed;
use models\YMOfferDetailed;
use models\YMTireCategory;

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 05.04.16
 * Time: 11:48
 */
class YandexMarketController implements IRenderer, IProductsUpdater
{
	const RENDER_FOR_1C = "yandexMarketModelsIdFor1C";
	const RENDER_FOR_USER = "yandexMarketMinimalPrices";

	protected $_db;
	protected $_apiService;
	protected $_regionName;
	protected $_regionId;

	/**
	 * @var IUniversalRenderer $_renderer
	 */
	protected $_renderer;
	protected $_productsUpdater;

	//protected $_queriesLimit = 5500;
	protected $_queriesLimit = 10;

	public function __construct(IDbController $dbController, IYandexMarketApiService $apiService,
								IUniversalRenderer $renderer, IProductsUpdater $productsUpdater) {

		$this->_renderer = $renderer;
		$this->_db = $dbController;
		$this->_apiService = $apiService;
		$this->_productsUpdater = $productsUpdater;


		set_time_limit(0);
	}

	public function GetMinimalPricesOnTires($regionName, array $caeToSearch = null) {

		//регион, по которому будем искать минимальные цены
		$this->_regionId = $this->GetYMRegionIdByName($regionName);
		$this->_regionName = $regionName;

		if (empty($caeToSearch))
			$tiresToSearch = $this->_db->GetTiresForYandexMarketMinimalPriceSearch();
		else {

			$tiresToSearch = (array)$this->_db->GetProductsByCae($caeToSearch, "ProductTireModel");
			foreach($tiresToSearch as $k=>$v) {

				$tiresToSearch[$k] = (array)$v;

			}
		}

		//var_dump($tiresToSearch);die;

		$lastModelName = null;

		$ymModelDetailed = null;

		foreach($tiresToSearch as $tireToSearch) {

			var_dump($ymModelDetailed);

			if ($this->_queriesLimit < 1){

				return $this;

			}

			$modelNameConcrete = "Шина" . " " . $tireToSearch['brand']. " " .$tireToSearch['model'] . " " .
				(int)$tireToSearch['width'] ."/". (int)$tireToSearch['height'] . " " . "R" .
				(int)$tireToSearch['diameter'];

			$modelName = $tireToSearch['brand']. " " .$tireToSearch['model'];

			//новая модель в списке
			if ($lastModelName != $modelName){

				var_dump("NEW MODEL...");

				/*$ymModelDetailed = new YMModelDetailed();
				$ymModelDetailed->geoId = $this->_regionId;
				$ymModelDetailed->category = YandexMarketApiService::YM_CATEGORY_TIRES_ID;
				$ymModelDetailed->name = $modelName;
				$ymModelDetailed->cae = $tireToSearch['cae'];
				$ymModelDetailed->returnFields = "all";*/

				//небольшая хитрость - todo пока хитрость отменяется =)
				//ищем сначала по длинному имени
				$ymModelDetailed = $this->GetYMModelThroughApiService($modelNameConcrete, $this->_regionId);

				//если не нашли то по короткому из номенклатурного названия модели + бренд
				//if ($ymModelDetailed->ymModelJsonRaw == null)
				//{
				//	$ymModelDetailed = $this->GetYMModelThroughApiService($modelName , $this->_regionId);
				//}

				$ymModelDetailed->categoryId =
					$ymModelDetailed->categoryId == null
						? YandexMarketApiService::YM_CATEGORY_TIRES_ID
						: $ymModelDetailed->categoryId;
				$ymModelDetailed->cae = $tireToSearch['cae'];
				var_dump($ymModelDetailed);

			}
			// та же модель в списке
			else {

				var_dump("SAME MODEL... ");

			}

			$lastModelName = $modelName;

			$ymModelDetailed->cae = $tireToSearch['cae'];

			//запишем результат вне зависимости от результата
			$this->_db->AddYMModel($ymModelDetailed);//die;

			//если такой модели не найдено
			if ($ymModelDetailed->ymModelJsonRaw == null){

				continue;

			}

			//если на модель нет товарных предложений
			if($ymModelDetailed->offerCount == null || $ymModelDetailed->offerCount < 1) {

				continue;

			}

			//найдем предложение по минимальной цене
			$returnFields = YMOfferDetailed::RET_KEY_FIELDS_DICOUNTS . "," . YMOfferDetailed::RET_KEY_FIELDS_FILTERS;

			$filterSeason = null;
			switch($tireToSearch['season']){
				case('лето'):
					$filterSeason = YMTireCategory::FILTER_SEASON_VALUE_SUMMER;
					break;

				case('зима'):
					$filterSeason = YMTireCategory::FILTER_SEASON_VALUE_WINTER;
					break;

				case('всесезонка'):
					$filterSeason = YMTireCategory::FILTER_SEASON_VALUE_ALLSEASONS;
					break;
			}

			$allowedFiltersStdClass =
				$this->_apiService->FindFiltersByYMCategoryId(YandexMarketApiService::YM_CATEGORY_TIRES_ID,
					$this->_regionId);

			//формируем фильтры
			$filters = [];
			if (empty($tireToSearch['diameter']) == false) {

			}

			if (empty($tireToSearch['height']) == false)
				$filters[YMTireCategory::FILTER_HEIGHT_ID] = (int)$tireToSearch['height'];

			if (empty($tireToSearch['width']) == false)
				$filters[YMTireCategory::FILTER_WIDTH_ID] = (int)$tireToSearch['width'];

			if (empty($tireToSearch['loadIndex']) == false)
				$filters[YMTireCategory::FILTER_LOADINDEX_ID] = (int)$tireToSearch['loadIndex'];

			if (empty($tireToSearch['speedIndex']) == false)
				$filters[YMTireCategory::FILTER_SPEEDINDEX_ID] = $tireToSearch['speedIndex'];

			$filters[YMTireCategory::FILTER_RUNFLAT_ID] = $tireToSearch['runFlat'];
			$filters[YMTireCategory::FILTER_SEASON_ID] = $filterSeason;
			$filters[YMTireCategory::FILTER_OFFER_MINPRICE] = $ymModelDetailed->minModelPrice; // из яндекса

			if ($this->_queriesLimit < 1) {

				return $this;

			}

			$count = 30;
			$offerResultJson = null;
			try {

				var_dump("Параметры шины из нашей БД");
				var_dump($tireToSearch);

				var_dump("Сформированные фильтры:");
				var_dump($filters);

				$offerResultJson = $this->GetYMOfferThroughApiService($ymModelDetailed->ymModelId, $this->_regionId,
					$returnFields, $filters, "price", 1, $ymModelDetailed->categoryId, $count);

			} catch (Exception $e) {

				//запишем в лог - потом разберемся
				$message = $e->getMessage() . " CAE - " . $ymModelDetailed->cae;
				MyLogger::WriteToLog($message, LOG_ERR);

				//будто бы ничего не нашли от яндекса
				var_dump("Исключение при попытке поиска предложения..." . $message);
				$emptyOffer = new YMOffer();
				$emptyOffer->cae = $ymModelDetailed->cae;
				$this->_db->AddYMOffer($emptyOffer);

				continue;

			}

			//print_r($offerResultJson);

			$ymOffers = YMOffer::Factory($offerResultJson);

			var_dump("Предложения получены:");
			var_dump($ymOffers);

			//найдем предложение с наименьшей ценой todo имеет смысл делать это если потом искать по названию модели, пока оставим
			$offerToRec = null;
			$lastYmOfferChecked = $ymOffers[0];
			foreach($ymOffers as $ymOffer) {


				if ($ymOffer->price <= $lastYmOfferChecked->price){
					$offerToRec = $ymOffer;
					$lastYmOfferChecked = $ymOffer;
				}

			}

			$offerToRec->ymRegionId = $this->_regionId;
			$offerToRec->cae = $tireToSearch['cae'];
			$offerToRec->ymModelId = $ymModelDetailed->ymModelId;

			/*
			 * todo также чтобы убедиться что это тот самый товар, а не какие нибудь пассатижы
			 * можно попробовать проверить поле json_result->offers->items[N]->name и сравнить его с нашим
			 * названием
			 */

			//сохраним предложение
			var_dump("Saving offer...");
			var_dump($offerToRec);
			$this->_db->AddYMOffer($offerToRec);

			var_dump($offerToRec);



			if ($offerToRec->YMShop != null && $offerToRec->YMShop->id != null) {

				var_dump("Saving shop...");
				$this->_db->AddYMShop($offerToRec->YMShop);

			}


			if ($this->_queriesLimit < 1){

				return $this;

			}

		}

		return $this;

	}

	/*public function GetMinimalPricesOnTires2($regionName) {

		//регион, по которому будем искать минимальные цены
		$this->_regionId = $this->GetYMRegionIdByName($regionName);
		$this->_regionName = $regionName;

		$tiresToSearch = $this->_db->GetTiresForYandexMarketMinimalPriceSearch();

		//var_dump($tiresToSearch);die;

		//пробегаем наши продукты из БД
		$lastModelName = null;
		$ymModelDetailed = null;
		foreach($tiresToSearch as $tireToSearch) {

			if ($this->_queriesLimit < 1)
				break;

			//если такую модель мы еще не искали в яндекс-маркет
			//var_dump($lastModelName . " VS " . $tireToSearch['model']);
			if ($lastModelName != $tireToSearch['model']) {

				//var_dump("New model on cycle!");
				$ymModelDetailed = new YMModelDetailed();
				$ymModelDetailed->geoId = $this->_regionId;
				$ymModelDetailed->category = YandexMarketApiService::YM_CATEGORY_TIRES_ID;
				$ymModelDetailed->name = $tireToSearch['brand']. " " .$tireToSearch['model'];
				$ymModelDetailed->cae = $tireToSearch['cae'];
				//$ymModelDetailed->name = $tireToSearch['brand']. " testNotFoundModelName";
				$ymModelDetailed->returnFields = "all";

				$lastModelName = $tireToSearch['model'];

				//берем
				if ($this->_queriesLimit > 0)
					$this->GetYMModelThroughApiService($ymModelDetailed);
				else break;

			} else {

				$ymModelDetailed->name = $tireToSearch['brand']. " " .$tireToSearch['model'];
				$ymModelDetailed->cae = $tireToSearch['cae'];
				$ymModelDetailed->returnFields = "all";

			}

			//var_dump($lastYmModelDetailed);

			$this->_db->AddYMModel($ymModelDetailed);

			//если такой модели не найдено
			if ($ymModelDetailed->ymModelJsonRaw == null)
				continue;

			//если на модель нет товарных предложений
			if($ymModelDetailed->offerCount == null || $ymModelDetailed->offerCount < 1) {
				continue;
			}


			//найдем предложение по минимальной цене
			$returnFields = YMOfferDetailed::RET_KEY_FIELDS_DICOUNTS . "," . YMOfferDetailed::RET_KEY_FIELDS_FILTERS;

			$filterSeason = null;
			switch($tireToSearch['season']){
				case('лето'):
					$filterSeason = YMTireCategory::FILTER_SEASON_VALUE_SUMMER;
					break;

				case('зима'):
					$filterSeason = YMTireCategory::FILTER_SEASON_VALUE_WINTER;
					break;

				case('всесезонка'):
					$filterSeason = YMTireCategory::FILTER_SEASON_VALUE_ALLSEASONS;
					break;
			}

			$filters = [

				YMTireCategory::FILTER_DIAMETER_ID => $tireToSearch['diameter'],
				YMTireCategory::FILTER_HEIGHT_ID => $tireToSearch['height'],
				YMTireCategory::FILTER_WIDTH_ID => $tireToSearch['width'],
				YMTireCategory::FILTER_LOADINDEX_ID => $tireToSearch['loadIndex'],
				YMTireCategory::FILTER_SPEEDINDEX_ID => $tireToSearch['speedIndex'],
				YMTireCategory::FILTER_RUNFLAT_ID => $tireToSearch['runFlat'],
				YMTireCategory::FILTER_SEASON_ID => $filterSeason,
				YMTireCategory::FILTER_OFFER_MINPRICE => 999

			];

			if ($this->_queriesLimit > 0) {

				var_dump("ASKING FOR OFFER!");

				$count = 30;
				$offerResultJson = $this->GetYMOfferThroughApiService($ymModelDetailed->ymModelId, $this->_regionId,
					$returnFields, $filters, "price", 1, $ymModelDetailed->category, $count);

				$ymOffers = YMOffer::Factory($offerResultJson);

				//найдем предложение с наименьшей ценой
				$offerToRec = null;
				$lastYmOfferChecked = $ymOffers[0];
				foreach($ymOffers as $ymOffer) {

					//var_dump($ymOffer->price ." VS ".$lastYmOfferChecked->price);

					if ($ymOffer->price <= $lastYmOfferChecked->price){
						$offerToRec = $ymOffer;
						$lastYmOfferChecked = $ymOffer;
					}

				}

				$offerToRec->ymRegionId = $this->_regionId;
				$offerToRec->cae = $tireToSearch['cae'];
				$offerToRec->ymModelId = $ymModelDetailed->ymModelId;

				/*
				 * todo также чтобы убедиться что это тот самый товар, а не какие нибудь пассатижы
				 * можно попробовать проверить поле json_result->offers->items[N]->name и сравнить его с нашим
				 * названием
				 */

				//сохраним предложение
				/*var_dump("Saving offer...");
				$this->_db->AddYMOffer($offerToRec);

				var_dump($offerToRec);


				var_dump("Saving shop...");
				if ($offerToRec->YMShop != null && $offerToRec->YMShop->id != null)
					$this->_db->AddYMShop($offerToRec->YMShop);


			} else break;

		}

		var_dump("END OF METHOD!!! " . $this->_queriesLimit);

		return $this;
	}*/

	/**
	 * @param $name
	 * @param $regionId
	 * @return YMModelDetailed
	 */
	protected function GetYMModelThroughApiService($name, $regionId) {

		$res = null;
		if ($this->_queriesLimit > 0){

			$res = $this->_apiService->FindYMModelsByParams($name, "all", $regionId);
			$this->_queriesLimit--; //todo нужна проверка ответа , если ответа не было вообще, то не уменьшать

		}

		return $res;

	}

	protected function GetYMRegionIdByName($regionName) {

		if (strtolower($regionName) == "москва"){

			return YandexMarketApiService::YM_GEO_ID_MOSCOW;

		}

		throw new Exception("Этот регион пока не поддерживается!");
	}

	protected function GetYMOfferThroughApiService($ymModelId, $ymGeoId, $ymReturnFields, $filtersDictionary,
												   $sortBy, $page, $ymCategoryId, $count) {
		$res = null;
		if ($this->_queriesLimit > 0){

			$res = $this->_apiService->FindYMOffersByModel($ymModelId, $ymGeoId, $ymReturnFields, $filtersDictionary, $sortBy,
				$page, $ymCategoryId, $count);

			$this->_queriesLimit --; //todo нужна проверка ответа , если ответа не было вообще, то не уменьшать
		}

		return $res;
	}

	/**
	 * @param $arg mixed
	 * @return mixed
	 */
	function Render($arg = null)
	{

		switch($arg) {

			case self::RENDER_FOR_1C:
				$this->RenderFor1C();
				$this->_renderer->Clear();
				break;

			case self::RENDER_FOR_USER:
				$this->RenderForUser();
				$this->_renderer->Clear();
				break;

		}

		return $this;

	}

	protected function RenderFor1C() {

		$res = $this->_db->GetYMTiresMinPriceDataForRender();

		$this->_renderer->SetColumnNames(['cae','ymOfferId', 'ymModelId', 'price', 'ymOfferUpdateDate']);

		foreach($res as $row) {

			$values = [

				$row['cae'],
				$row['ymOfferId'],
				$row['ymModelId'],
				$row['price'],
				$row['ymOfferUpdateDate']

			];

			$this->_renderer->FeedValues($values);

		}

		$this->_renderer->SetFileName(self::RENDER_FOR_1C);
		$this->_renderer->Render(self::RENDER_FOR_1C); //todo wtf )

	}

	protected function RenderForUser() {

		$cols = ['САЕ','Бренд','Модель','Ширина','Профиль','Диаметр','Индекс скорости',
			'Индекс нагрузки', 'Сезон', 'RunFlat', 'Кол-во','Цена', 'Название магазина', 'Сайт','Дата обновления цены'];

		$this->_renderer->SetColumnNames($cols);

		$res = $this->_db->GetYMTiresMinPriceDataForRender();

		//var_dump($res);

		foreach($res as $row) {

			$values = [

				$row['cae'],
				$row['brand'],
				$row['model'],
				$row['width'],
				$row['height'],
				$row['diameter'],
				$row['speedIndex'],
				$row['loadIndex'],
				$row['season'],
				$row['runFlat'],
				0,
				$row['price'],
				$row['name'],
				$row['siteUrl'],
				$row['ymOfferUpdateDate']

			];

			$this->_renderer->FeedValues($values);

		}

		$this->_renderer->SetFileName(self::RENDER_FOR_USER);
		$this->_renderer->Render(self::RENDER_FOR_USER);

	}

	/**
	 * Обновить продукцию
	 * @return mixed
	 */
	function UpdateProducts()
	{
		$this->_productsUpdater->UpdateProducts();
		return $this;
	}

	/**
	 * Обновление наличия товара
	 * @return mixed
	 */
	function UpdateProductsAvailability()
	{
		$this->_productsUpdater->UpdateProductsAvailability();
		return $this;
	}
}