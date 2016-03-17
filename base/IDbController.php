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
}