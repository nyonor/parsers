<?php
require_once 'base/IDbController.php';

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 20.10.15
 * Time: 10:01
 */

/**
 * Class MongoDbController
 * @deprecated
 */
class MongoDbController implements IDbController
{
	const MONGO_DB = "RivalParsingDB";
	const RIVAL_PARSED_RESULTS_COLLECTION = "RivalParsedResults";

	/**
	 * @var MongoClient $_client
	 */
	protected $_client;

	public function __construct() {
		$this->_client = new MongoClient();
	}

	/**
	 * Добавляем результат парсинга КОНКУРЕНТА в коллекцию
	 * @param $objectToInsert  RivalTireModel | StdClass | RivalTireModel[] | StdClass[]
	 */
	function AddParsingResult($objectToInsert)
	{
		//если это массив
		if (is_array($objectToInsert)) {
			foreach($objectToInsert as $key => $model) {
				//todo сохранение результатов через массив
			}
		}
		//если это НЕ массив
		else {
			$collection = $this->_client->selectCollection(self::MONGO_DB, self::RIVAL_PARSED_RESULTS_COLLECTION);
			var_dump($objectToInsert);

			//если запись есть, то апдейт, иначе инсерт
			//$collection->update((array)$objectToInsert, (array)$objectToInsert, ["upsert" => true]);
			$collection->save($objectToInsert);
		}
	}

	/**
	 * @param $siteUrl
	 */
	function TruncateOldParseResult($siteUrl)
	{
		// TODO: Implement TruncateOldParseResult() method.
	}

	/**
	 * @return mixed
	 */
	function TruncateOldProductsData()
	{
		// TODO: Implement TruncateOldProductsData() method.
	}


	/**
	 * @param $objectToInsert RivalTireModel | StdClass
	 * @return mixed
	 */
	function AddProducts($objectToInsert)
	{
		// TODO: Implement AddProductsData() method.
	}

	/**
	 * Поиск товара в нашей номенклатуре
	 * @param RivalTireModel[] | StdClass[] $models
	 * @return ProductTireModel[] | StdClass[]
	 */
	function CompareWithProducts($models = null)
	{
		// TODO: Implement FindInProducts() method.
	}

	function FindParsedResultsBySiteUrl($siteUrl)
	{
		// TODO: Implement FindParsedResultsBySiteUrl() method.
	}

	function GetAllModels()
	{
		// TODO: Implement GetAllModels() method.
	}

	function GetAllBrands()
	{
		// TODO: Implement GetAllBrands() method.
	}


	/**
	 * Связывает результаты сопоставления в соответствии с релевантностью
	 * @param $parsedResultId int
	 * @param $productCae string
	 * @param $relevanceModel float
	 * @param float $relevanceBrand
	 * @param $shouldCheckByOperator
	 * @return mixed
	 */
	function LinkParsedResultToProduct($parsedResultId, $productCae, $relevanceModel, $relevanceBrand, $shouldCheckByOperator)
	{
		// TODO: Implement LinkParsedResultToProduct() method.
	}

	/**
	 * Поиск по спарсенному в таблице сравнений
	 * @param $rivalModel RivalTireModel
	 * @return CsvViewModel | null
	 */
	function FindInComparedByRivalModel($rivalModel)
	{
		// TODO: Implement FindInComparedByRivalModel() method.
	}

	/**
	 * Поиск по сопоставленному по
	 * @param $siteUrl string
	 * @return CsvViewModel[]
	 */
	function FindInComparedByUrl($siteUrl)
	{
		// TODO: Implement FindInComparedByUrl() method.
	}

	/**
	 * @return TypeSizeModel[]
	 */
	function GetAllTypeSizes()
	{
		// TODO: Implement GetAllTypeSizes() method.
	}
}