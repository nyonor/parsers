<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 21.10.15
 * Time: 8:19
 */
interface IDbController
{
	/**
	 * Добавить спарсенный результат в бд
	 * @param $objectToInsert RivalTireModel | StdClass
	 */
	function AddParsingResult($objectToInsert);

	/**
	 * Очистить ранее спарсенных результаты из таблицы по URL сайта
	 * @param $siteUrl
	 */
	function TruncateOldParseResult($siteUrl);

	/**
	 * Очищение старой номенклатуры
	 * @return mixed
	 */
	function TruncateOldProductsData();

	/**
	 * @param $objectToInsert ProductTireModel[] | StdClass[]
	 * @return mixed
	 */
	function AddProducts($objectToInsert);

	/**
	 * Сопоставление спарсенного к нашей номенклатуре
	 * TODO: можно потом реализовать передачу массива
	 * @param $rivalTireModel RivalTireModel
	 * @return ComparisonResult | null
	 */
	function CompareWithProducts($rivalTireModel);

	/**
	 * Поиск спарсенных результатов по url сайта
	 * @param $siteUrl
	 * @return RivalTireModel[]
	 */
	function FindParsedResultsBySiteUrl($siteUrl);

	/**
	 * @deprecated
	 * @return mixed
	 */
	function GetAllModels();

	/**
	 * @return mixed
	 */
	function GetAllBrands();

	/**
	 * @return TypeSizeModel[]
	 */
	function GetAllTypeSizes();

	/**
	 * Связывает результаты сопоставления в соответствии с релевантностью
	 * @param $parsedResultId int
	 * @param $productCae string
	 * @param $relevanceModel float
	 * @param $relevanceBrand float
	 * @param $shouldCheckByOperator
	 * @return mixed
	 * @internal param $shouldCheckByOperator
	 * @internal param float $relevance
	 */
	function LinkParsedResultToProduct($parsedResultId, $productCae, $relevanceModel, $relevanceBrand, $shouldCheckByOperator);

	/**
	 * Поиск по спарсенному в таблице сравнений
	 * @param $rivalModel RivalTireModel
	 * @return ComparisonResult[] | null
	 */
 	function FindInComparedByRivalModel($rivalModel);

	/**
	 * Поиск в таблице сравнений
	 * @param $siteUrl string
	 * @return ComparisonResult[] | boolean
	 */
	function FindInComparedByUrl($siteUrl);

	/**
	 * Поиск всех типоразмеров с указанием модели и бренда
	 * @param $brand string null
	 * @param $model string
	 * @return ProductTireModel[]
	 */
	function FindTireByModelAndBrand($brand = null, $model);

	/**
	 * Сохранение спарсенного результата
	 * @param TireModelMinPriceInfo $objectToInsert
	 */
	public function AddAggregatorParsingResult($objectToInsert);

	/**
	 * Возвращает все данные о минимальных цена и конкурентах
	 * собранные по аггрегатору (ЯндексМаркет)
	 * @return TireModelMinPriceInfo[]|array|mixed
	 */
	public function GetAllMinimalPriceInfoProductModels();

	/**
	 * Обновляет доступность нашего товара (есть ли он на складе)
	 * @param $cae
	 * @param bool|true $isAvailable
	 * @return mixed
	 */
	public function UpdateProductAvailability($cae, $isAvailable = true);

	/**
	 * Получает скопом весь список шин, которые нужно проверить в Яндекс-Маркете на минимальные цены
	 * Возвращает ассциативный массив связанных таблиц Products, YMOffers, YMModels
	 * С !!!последующей!!! группировкой по колонке Products.model (
	 * @return mixed
	 */
	public function GetTiresForYandexMarketMinimalPriceSearch();


	/**
	 * Добавляет модель яндекс маркета
	 * @param $ymModel YMModel
	 * @return mixed
	 */
	public function AddYMModel($ymModel);

	/**
	 * Добавляет предложение яндекс маркета вместе с магазином
	 * @param YMOffer $ymOffer
	 * @return mixed
	 */
	public function AddYMOffer($ymOffer);

	/**
	 * Добавляет магазин указанный в yandex-market
	 * @param $ymShop
	 * @return mixed
	 */
	public function AddYMShop($ymShop);

	/**
	 * Возвращает данные (по минимальным ценам из яндекс-маркет результатов АПИ парсинга)
	 * для рендеринга
	 * @return mixed
	 */
	public function GetYMTiresMinPriceDataForRender();

	/**
	 * Возвращает модели по всем товарам
	 * @return mixed
	 */
	public function GetYMTiresModelsByAvailableProducts();

	/**
	 * @param $caeArray array
	 * @param string $classNameToMap
	 * @return mixed
	 */
	function GetProductsByCae($caeArray, $classNameToMap);

}