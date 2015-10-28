<?php
require_once 'base/IDbController.php';

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 21.10.15
 * Time: 12:03
 */
class MysqlDbController implements IDbController
{
	const MYSQL_DSN = "mysql:host=localhost;dbname=4tochki;charset=utf8";
	const MYSQL_USER_LOGIN = "mysqlUser";
	const MYSQL_PASSWORD = "iddqd";
	const PREPARED_STATEMENT_FOR_SELECT = "prepStForSel";

	protected $_db;
	protected $_lastPreparedStatementsArray;

	public function __construct() {
		$this->_db = new PDO(self::MYSQL_DSN, self::MYSQL_USER_LOGIN, self::MYSQL_PASSWORD,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	}

	protected function SetPreparedStatementByKey($key, $statement) {
		$this->_lastPreparedStatementsArray[$key] = $statement;
	}

	protected function GetPreparedStatementByKey($key) {
		//var_dump($this->_lastPreparedStatementsArray[$key]);
		return $this->_lastPreparedStatementsArray[$key];
	}

	/**
	 * @param $objectToInsert RivalTireModel | StdClass
	 * todo НЕ правильно сделал подготовленный инсерт!
	 */
	function AddParsingResult($objectToInsert)
	{
		$insertQuery = "INSERT INTO 4tochki.RivalParsedResults
				  (`brand`, `model`, `width`, `height`, `constructionType`, `diameter`,
				  `loadIndex`, `speedIndex`, `season`, `runFlat`, `site`, `url`, `price`)
				  VALUES (:brand, :model, :width, :height, :constructionType, :diameter,
				  :loadIndex, :speedIndex, :season, :runFlat, :site, :url, :price)";

		$db = $this->_db;
		$statement = $db->prepare($insertQuery);

		$statement->bindParam(':brand',$objectToInsert->brand);
		$statement->bindParam(':model',$objectToInsert->model);
		$statement->bindParam(':width',$objectToInsert->width);
		$statement->bindParam(':height',$objectToInsert->height);
		$statement->bindParam(':constructionType',$objectToInsert->constructionType);
		$statement->bindParam(':diameter',$objectToInsert->diameter);
		$statement->bindParam(':loadIndex',$objectToInsert->loadIndex);
		$statement->bindParam(':speedIndex',$objectToInsert->speedIndex);
		$statement->bindParam(':season',$objectToInsert->season);
		$statement->bindParam(':runFlat',$objectToInsert->runFlat);
		$statement->bindParam(':site',$objectToInsert->site);
		$statement->bindParam(':url',$objectToInsert->url);
		$statement->bindParam(':price', $objectToInsert->price); //todo PRICE!!!

		$statement->execute();
	}

	function TruncateOldParseResult($siteUrl) {
		$deleteQuery = "DELETE FROM RivalParsedResults WHERE site = '".$siteUrl."'";
		echo $deleteQuery;
		$this->_db->query($deleteQuery)->execute();
	}

	/**
	 * @return null
	 */
	function TruncateOldProductsData()
	{
		// TODO: Implement TruncateOldProductsData() method.
	}


	/**
	 * Добавляет нашу номенклатуру в бд
	 * @param $objectToInsert ProductTireModel[] | StdClass[] | ProductTireModel | StdClass
	 * @return mixed
	 */
	function AddProductsData($objectToInsert)
	{
		$insertQuery = "INSERT INTO 4tochki.Products
				  (`cae`,`brand`, `model`, `width`, `height`, `constructionType`, `diameter`,
				  `loadIndex`, `speedIndex`, `season`, `runFlat`)
				  VALUES (:cae, :brand, :model, :width, :height, :constructionType, :diameter,
				  :loadIndex, :speedIndex, :season, :runFlat)"; //todo PRICE!!!

		if (is_array($objectToInsert)) {

			$db = $this->_db;
			$statement = $db->prepare($insertQuery);

			foreach ($objectToInsert as $productModel) {

				$statement->bindParam(':brand', $productModel->brand);
				$statement->bindParam(':model', $productModel->model);
				$statement->bindParam(':width', $productModel->width);
				$statement->bindParam(':height', $productModel->height);
				$statement->bindParam(':constructionType', $productModel->constructionType);
				$statement->bindParam(':diameter', $productModel->diameter);
				$statement->bindParam(':loadIndex', $productModel->loadIndex);
				$statement->bindParam(':speedIndex', $productModel->speedIndex);
				$statement->bindParam(':season', $productModel->season);
				$statement->bindParam(':runFlat', $productModel->runFlat);
				$statement->bindParam(':cae', $productModel->cae);
				//$statement->bindParam(':price',); todo PRICE!!!
				$statement->execute();
			}
		}
	}

	/**
	 * Поиск и сопоставление товара в нашей номенклатуре
	 * @param $rivalModel RivalTireModel
	 * @return ComparisonResult | null
	 */
	function FindInProducts($rivalModel)
	{
		//ищем
		$selectSql = "SELECT *, MATCH(model) AGAINST(:model) as 'relevance'
						FROM Products WHERE
						brand = :brand AND
						MATCH(model) AGAINST(:model) AND
						width = :width AND
						height = :height AND
						constructionType = :constructionType AND
						diameter = :diameter AND
						loadIndex = :loadIndex AND
						speedIndex = :speedIndex AND
						productType = :productType AND
						season = :season AND
						runFlat = :runFlat
						ORDER BY relevance DESC, length(model) DESC"; //todo season! runflat!

		//если подготовленного выражения нет, то добавим его по ключу
		if ($this->GetPreparedStatementByKey(self::PREPARED_STATEMENT_FOR_SELECT) == null) {
			$this->SetPreparedStatementByKey(self::PREPARED_STATEMENT_FOR_SELECT, $this->_db->prepare($selectSql));
		}

		/**
		 * @var $statement PDOStatement
		 */
		$statement = $this->GetPreparedStatementByKey(self::PREPARED_STATEMENT_FOR_SELECT);

		$statement->bindParam(':brand', $rivalModel->brand);
		$statement->bindParam(':model', $rivalModel->model);
		$statement->bindParam(':width', $rivalModel->width);
		$statement->bindParam(':height', $rivalModel->height);
		$statement->bindParam(':constructionType', $rivalModel->constructionType);
		$statement->bindParam(':diameter', $rivalModel->diameter);
		$statement->bindParam(':loadIndex', $rivalModel->loadIndex);
		$statement->bindParam(':speedIndex', $rivalModel->speedIndex);
		$statement->bindParam(':season', $rivalModel->season);
		$statement->bindParam(':runFlat', $rivalModel->runFlat);
		$statement->bindValue(':productType', "tire");

		//выполняем
		if ($statement->execute()) {
			//$statement->debugDumpParams();
			//echo "<br/>".$statement->rowCount();
			//echo "Looking FOR <br/>";
			//var_dump($rivalModel);
			//var_dump($statement->queryString);
			/**
			 * @var $productModel ComparisonResult
			 */
			$productModel = $statement->fetchObject('ComparisonResult');

			/* если название моделей не совпадает то установим свойство в true
			 * в последствии его можно будет использовать в выгрузке или
			 * для формирования таблицы с правилами
			 */
			if ($productModel != null && strcasecmp($rivalModel->model, $productModel->model) != 0) {
				$productModel->shouldCheckByOperator = true;
			}
			else if ($productModel != null) {
				$productModel->shouldCheckByOperator = false;
			}

			if($productModel != null) {
				$productModel->rivalModel = $rivalModel;
				return $productModel;
			} else {
				//TODO: если не нашли то записать в таблицу не найденных? Нужно ли нам хранить историю парсинга? Или очищать предыдущие результаты каждый раз при новом парсинге ресурса?
			}
		}

		return null;
	}

	/**
	 * @param $siteUrl
	 * @return array
	 */
	function FindParsedResultsBySiteUrl($siteUrl)
	{
		// TODO: Implement FindParsedResultsBySiteUrl() method.
		$selectSql = "SELECT * FROM RivalParsedResults WHERE site = '".$siteUrl."'";
		return $this->_db->query($selectSql)->fetchAll(PDO::FETCH_CLASS, "RivalTireModel");
	}

	function GetAllModels()
	{
		$selectQuery = "SELECT DISTINCT (Products.model) as 'model' FROM Products";
		return $this->_db->query($selectQuery)->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	function GetAllBrands()
	{
		//$selectQuery = ""
		//TODO: Implement GetAllBrands
	}

	function __destruct() {
		$this->_lastPreparedStatementsArray = null;
	}
}