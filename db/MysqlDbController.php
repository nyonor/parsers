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
	/*const MYSQL_USER_LOGIN = "root";
	const MYSQL_PASSWORD = "-oA8M%#%%x";*/
	const KEY_PREPARED_STATEMENT_SELECT_MATCH_PARSED_IN_PRODUCTS = "prepStForSel";
	const KEY_PREPARED_STATEMENT_INSERT_PARSED_RESULT = "prepStForIns";
	const KEY_PREPARED_STATEMENT_INSERT_LINK_PRODUCTS_TO_PARSED_RESULT = "prepStForLink";
	const KEY_PREPARED_STATEMENT_SELECT_COMPARED_BY_PARSED = "prepStSelComparedByParsed";
	const KEY_PREPARED_STATEMENT_INSERT_AGGREGATOR_PARSED_RESULT = "aggregatorInsRes";
	const KEY_PREPARED_STATEMENT_UPDATE_OUR_PRODUCT_AVAILABILITY = "updateOurProdAvailability";
	const KEY_PREPARED_STATEMENT_SELECT_FOR_YM_MINIMAL_PRICES = "selectForYmMinimalPrices";
	const KEY_PREPARED_STATEMENT_ADD_YMMODEL = "addYmModel";
	const KEY_PREPARED_STATEMENT_ADD_YM_LINKS_MODEL = "addYmLinksModel";
	const KEY_PREPARED_STATEMENT_ADD_YM_LINKS_OFFER = "addYmLinksOffer";
	const KEY_PREPARED_STATEMENT_ADD_YMOFFER = "addYmoffer";
	const KEY_PREPARED_STATEMENT_ADD_YMOSHOP = "addYmShop";

	protected $_db;
	protected $_lastPreparedStatementsArray = [];

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
	 */
	function AddParsingResult($objectToInsert)
	{
		$insertQuery = "INSERT INTO 4tochki.RivalParsedResults
				  (`brand`, `model`, `width`, `height`, `constructionType`, `diameter`,
				  `loadIndex`, `speedIndex`, `season`, `runFlat`, `site`, `url`, `price`, `quantity`)
				  VALUES (:brand, :model, :width, :height, :constructionType, :diameter,
				  :loadIndex, :speedIndex, :season, :runFlat, :site, :url, :price, :quantity)";

		$db = $this->_db;

		//если подготовленного выражения нет, то добавим его по ключу
		if ($this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_INSERT_PARSED_RESULT) == null) {
			$statement = $db->prepare($insertQuery);
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_INSERT_PARSED_RESULT, $statement);
		}

		/**
		 * @var $statement PDOStatement
		 */
		$statement = $this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_INSERT_PARSED_RESULT);

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
		$statement->bindParam(':price', $objectToInsert->price);
		$statement->bindParam(':quantity', $objectToInsert->quantity);

		$statement->execute();
	}

	function TruncateOldParseResult($siteUrl) {
		$deleteQuery = "DELETE FROM RivalParsedResulstToProducts
						WHERE RivalParsedResulstToProducts.rivalParsedResultsId
							IN (SELECT id FROM RivalParsedResults WHERE site = '".$siteUrl."')";
		$this->_db->query($deleteQuery)->execute();
		$deleteQuery = "DELETE FROM RivalParsedResults WHERE site = '".$siteUrl."'";
		$this->_db->query($deleteQuery)->execute();
	}

	/**
	 * Очищение старой номенклатуры
	 * @return null
	 */
	function TruncateOldProductsData()
	{
		$deleteQuery = "DELETE FROM Products";
		$this->_db->query($deleteQuery)->execute();
	}


	/**
	 * Добавляет нашу номенклатуру в бд
	 * @param $objectToInsert ProductTireModel[] | StdClass[]
	 * @return mixed
	 */
	function AddProducts($objectToInsert)
	{
		$insertQuery = "INSERT INTO 4tochki.Products
				  (`cae`,`brand`, `model`, `width`, `height`, `constructionType`, `diameter`,
				  `loadIndex`, `speedIndex`, `season`, `runFlat`)
				  VALUES (:cae, :brand, :model, :width, :height, :constructionType, :diameter,
				  :loadIndex, :speedIndex, :season, :runFlat)";

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
	 * Поиск в таблице сравнений
	 * @param $siteUrl string
	 * @return ComparisonResult[] | boolean
	 */
	function FindInComparedByUrl($siteUrl)
	{
		$selectQuery = "SELECT *, parsed.id as rivalParsedId, parsed.brand as conBrand, parsed.model as conModel
						FROM 4tochki.RivalParsedResulstToProducts as compared
						LEFT JOIN Products as products on products.cae = compared.productCae
						LEFT JOIN RivalParsedResults as parsed on parsed.id = compared.rivalParsedResultsId
						WHERE
						parsed.site = '".$siteUrl."'";

		$res = $this->_db->query($selectQuery)->fetchAll(PDO::FETCH_CLASS, 'ComparisonResult');
		//var_dump($res);die;
		return $res;
	}

	/**
	 * Точный поиск в таблице сравнений
	 * @param $rivalModel RivalTireModel
	 * @return ComparisonResult[]
	 */
	function FindInComparedByRivalModel($rivalModel) {

		$selectQuery = "SELECT products.cae, parsed.price, compared.shouldCheckByOperator, parsed.quantity,
						parsed.brand as conBrand, parsed.model as conModel
						FROM 4tochki.RivalParsedResulstToProducts as compared
						LEFT JOIN Products as products on products.cae = compared.productCae
						LEFT JOIN RivalParsedResults as parsed on parsed.id = compared.rivalParsedResultsId
						WHERE
						parsed.brand = :brand AND
						parsed.model = :model AND
						parsed.width = :width AND
						parsed.height = :height AND
						parsed.constructionType = :constructionType AND
						parsed.diameter = :diameter AND
						parsed.loadIndex = :loadIndex AND
						parsed.speedIndex = :speedIndex AND
						parsed.productType = :productType AND
						parsed.season = :season AND
						parsed.runFlat = :runFlat";

		$statement = $this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_SELECT_COMPARED_BY_PARSED);

		if ($statement == null) {
			$statement = $this->_db->prepare($selectQuery);
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_SELECT_COMPARED_BY_PARSED, $statement);
		}

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
		$statement->bindValue(':productType', "tire"); //todo а если диски?

		if ($statement->execute()) {

			$result = $statement->fetchObject('ComparisonResult');
			return $result;
		}

		return null;
	}

	/**
	 * Сопоставление спарсенного к нашей номенклатуре
	 * @param $rivalModel RivalTireModel
	 * @return ComparisonResult | null
	 */
	function CompareWithProducts($rivalModel)
	{
		//var_dump($rivalModel);

		$rfStr = "";
		if($rivalModel->runFlat != null) {
			$rfStr = " AND runFlat = :runFlat ";
		}

		//ищем
		$selectSql = "SELECT *,
						MATCH(model) AGAINST(:model) as 'relevanceModel',
						MATCH (brand) AGAINST (:brand) as 'relevanceBrand'
						FROM Products WHERE
						MATCH (brand) AGAINST (:brand) AND
						MATCH(model) AGAINST(:model) AND
						width = :width AND
						height = :height AND
						constructionType = :constructionType AND
						diameter = :diameter AND
						loadIndex = :loadIndex AND
						speedIndex = :speedIndex AND
						productType = :productType AND
						season = :season
						$rfStr
						ORDER BY relevanceBrand DESC, relevanceModel DESC, length(model) DESC";

		//var_dump($selectSql);

		//если подготовленного выражения нет, то добавим его по ключу
		if ($this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_SELECT_MATCH_PARSED_IN_PRODUCTS) == null) {
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_SELECT_MATCH_PARSED_IN_PRODUCTS, $this->_db->prepare($selectSql));
		}

		/**
		 * @var $statement PDOStatement
		 */
		$statement = $this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_SELECT_MATCH_PARSED_IN_PRODUCTS);

		$modelParam = $rivalModel->model;

		//надстройка полнотекстового поиска
		/*$modelParamExploded = explode(' ',$rivalModel->model);
		foreach($modelParamExploded as $key => $word) {
			//$modelParamExploded[$key] = '+"'.$word.'"';
			$modelParamExploded[$key] = '*'.$word.'*';
		}
		$modelParam = implode(' ',$modelParamExploded);*/
		//var_dump($modelParam);

		$statement->bindValue(':brand', $rivalModel->brand);
		$statement->bindValue(':model', $modelParam);
		$statement->bindValue(':width', $rivalModel->width);
		$statement->bindValue(':height', $rivalModel->height);
		$statement->bindValue(':constructionType', $rivalModel->constructionType);
		$statement->bindValue(':diameter', $rivalModel->diameter);
		$statement->bindValue(':loadIndex', $rivalModel->loadIndex);
		$statement->bindValue(':speedIndex', $rivalModel->speedIndex);
		$statement->bindValue(':season', $rivalModel->season);
		if ($rivalModel->runFlat != null)
			$statement->bindValue(':runFlat', $rivalModel->runFlat);
		$statement->bindValue(':productType', "tire");

		//выполняем
		if ($statement->execute()) {
			/**
			 * @var $comparisonResultModel ComparisonResult
			 */
			$result = $statement->fetchObject('ComparisonResult');

			if ($result != null) {
				$comparisonResultModel = $result;
			} else {
				$comparisonResultModel = new ComparisonResult();
			}

			//var_dump($cr);

			/* если название моделей или брендов не совпадает то установим свойство в true
			 * в последствии его можно будет использовать в выгрузке или
			 * для формирования таблицы с правилами
			 */
			if ($comparisonResultModel != null && (strcasecmp($rivalModel->model, $comparisonResultModel->model) != 0
					|| strcasecmp($rivalModel->brand, $comparisonResultModel->brand) != 0)) {
				$comparisonResultModel->shouldCheckByOperator = true;
			}
			else if ($comparisonResultModel != null ) {
				$comparisonResultModel->shouldCheckByOperator = false;
			}

			if($comparisonResultModel != null) {
				//$comparisonResultModel->rivalModel = $rivalModel;
				$comparisonResultModel->quantity = $rivalModel->quantity;
				$comparisonResultModel->price = $rivalModel->price;
				$comparisonResultModel->conModel = $rivalModel->model;
				$comparisonResultModel->conBrand = $rivalModel->brand;
				//var_dump($productModel);

				//var_dump($rivalModel);

				//сделаем запись в таблице связей
				if ($comparisonResultModel->cae != null) {
					try {
						$this->LinkParsedResultToProduct
						($rivalModel->id, $comparisonResultModel->cae, $comparisonResultModel->relevanceModel, $comparisonResultModel->relevanceBrand,
							$comparisonResultModel->shouldCheckByOperator);
					} catch(PDOException $e) {
						//на таблице висит констрэйнт UNIQUE
					} catch (Exception $e) {
						echo $e;
					}

				}
				return $comparisonResultModel;
			} else {
				//TODO: если не нашли то записать в таблицу не найденных? Нужно ли нам хранить историю парсинга? Или очищать предыдущие результаты каждый раз при новом парсинге ресурса?
			}
		}

		return null;
	}

	/**
	 * Связывает результаты сопоставления в соответствии с релевантностью
	 * @param $parsedResultId int
	 * @param $productCae string
	 * @param $relevanceModel float
	 * @param $relevanceBrand float
	 * @param $shouldCheckByOperator
	 * @return mixed
	 */
	function LinkParsedResultToProduct($parsedResultId, $productCae, $relevanceModel, $relevanceBrand, $shouldCheckByOperator)
	{
		//echo $parsedResultId;
		if ($this->GetPreparedStatementByKey
			(self::KEY_PREPARED_STATEMENT_INSERT_LINK_PRODUCTS_TO_PARSED_RESULT) == null) {
			$insertQuery = "INSERT INTO RivalParsedResulstToProducts
							(rivalParsedResultsId, productCae, relevanceModel, relevanceBrand, shouldCheckByOperator)
							VALUES
							(:rivalParsedResultId, :productCae, :relevanceModel, :relevanceBrand, :shouldCheckByOperator)";
			$statement = $this->_db->prepare($insertQuery);
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_INSERT_LINK_PRODUCTS_TO_PARSED_RESULT,
				$statement);
		}

		/**
		 * @var $statement PDOStatement
		 */
		$statement = $this->GetPreparedStatementByKey
		(self::KEY_PREPARED_STATEMENT_INSERT_LINK_PRODUCTS_TO_PARSED_RESULT);

		//echo "<br/><br/>" . $parsedResultId . " " . $productCae . " " . $relevance . "<br/><br/>";
		//return;
		$statement->bindValue(':rivalParsedResultId', $parsedResultId);
		$statement->bindValue(':productCae', $productCae);
		$statement->bindValue(':relevanceModel', $relevanceModel);
		$statement->bindValue(':relevanceBrand', $relevanceBrand);
		$statement->bindValue(':shouldCheckByOperator', $shouldCheckByOperator);
		$statement->execute();
	}

	/**
	 * @param $siteUrl
	 * @return RivalTireModel[]
	 */
	function FindParsedResultsBySiteUrl($siteUrl)
	{
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
		$sql = "SELECT DISTINCT(Products.brand) AS 'Brand' FROM 4tochki.Products ";
		$sqlResult = $this->_db->query($sql)->fetchAll();
		$brandsArr = [];
		foreach($sqlResult as $row) {
			$brandsArr[] = $row['Brand'];
		}
		return $brandsArr;
	}

	function __destruct() {
		$this->_lastPreparedStatementsArray = null;
	}

	/**
	 * @return TypeSizeModel[]
	 */
	function GetAllTypeSizes()
	{
		$sql = "SELECT Products.width as 'width', Products.height as 'height', Products.diameter as 'diameter'
				FROM Products GROUP BY width, height, diameter";
		$sqlRes = $this->_db->query($sql)->fetchAll(PDO::FETCH_CLASS, "TypeSizeModel");
		return $sqlRes;
	}

	/**
	 * Поиск всех типоразмеров с указанием модели и бренда
	 * @param $brand string
	 * @param $model string
	 * @return TireModel[]
	 */
	function FindTireByModelAndBrand($brand = null, $model)
	{
		$sqlPart = "SELECT * FROM Products WHERE ";
		if($brand != null) {
			$sqlPart .= "brand LIKE '%".$brand."%' AND ";
		}
		$sqlPart .= "model = '".$model."'";
		$sql = $sqlPart;
		//var_dump($sql);
		$statement = $this->_db->query($sql);
		$res = $statement->fetchAll(PDO::FETCH_CLASS, "ProductTireModel");

		return $res;
	}

	/**
	 * Сохранение спарсенного результата
	 * @param TireModelMinPriceInfo $objectToInsert
	 */
	public function AddAggregatorParsingResult($objectToInsert) {

		$insertQuery = "INSERT INTO 4tochki.TireModelMinPriceInfo
				  (`productCae`, `yandexMarketUrl`, `rivalStoreUrl`, `rivalStoreName`, `minimalPrice`, `date`)
				  VALUES (:productCae, :yandexMarketUrl, :rivalStoreUrl, :rivalStoreName, :minimalPrice, NOW())
				  ON DUPLICATE KEY UPDATE
				  `yandexMarketUrl` = :yandexMarketUrl,
				  `rivalStoreUrl` = :rivalStoreUrl,
				  `rivalStoreName` = :rivalStoreName,
				  `minimalPrice` = :minimalPrice,
				  `date` = NOW()";

		$db = $this->_db;

		$statement = null;
		//если подготовленного выражения нет, то добавим его по ключу
		if ($this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_INSERT_AGGREGATOR_PARSED_RESULT) == null) {
			$statement = $db->prepare($insertQuery);
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_INSERT_AGGREGATOR_PARSED_RESULT, $statement);
		}

		/**
		 * @var $statement PDOStatement
		 */
		$statement = $this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_INSERT_AGGREGATOR_PARSED_RESULT);

		$statement->bindValue(':productCae', $objectToInsert->cae);
		$statement->bindValue(':yandexMarketUrl', $objectToInsert->yandexMarketUrl);
		$statement->bindValue(':rivalStoreUrl', $objectToInsert->rivalStoreUrl);
		$statement->bindValue(':rivalStoreName', $objectToInsert->rivalStoreName);
		$statement->bindValue(':minimalPrice', $objectToInsert->minimalPrice);

		$statement->execute();

	}

	/**
	 * Возвращает все данные о минимальных цена и конкурентах
	 * собранные по аггрегатору (ЯндексМаркет)
	 * @return TireModelMinPriceInfo[]|array|mixed
	 */
	public function GetAllMinimalPriceInfoProductModels()
	{
		$query = "SELECT * FROM TireModelMinPriceInfo as TMPI
					LEFT JOIN Products as P on P.cae = TMPI.productCae";
		$result = $this->_db->query($query)->fetchAll(PDO::FETCH_CLASS, 'TireModelMinPriceInfo');
		return $result;
	}

	/**
	 * Обновляет доступность нашего товара (есть ли он на складе)
	 * @param $cae
	 * @param bool|true $isAvailable
	 * @return mixed
	 */
	public function UpdateProductAvailability($cae, $isAvailable = true)
	{
		$sql = "UPDATE Products SET Products.available = :isAvailable WHERE Products.cae = :cae";

		$statement = null;
		//если подготовленного выражения нет, то добавим его по ключу
		if ($this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_UPDATE_OUR_PRODUCT_AVAILABILITY) == null) {
			$statement = $this->_db->prepare($sql);
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_UPDATE_OUR_PRODUCT_AVAILABILITY, $statement);
		}

		$statement = $this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_UPDATE_OUR_PRODUCT_AVAILABILITY);

		/**
		 * @var $statement PDOStatement
		 */
		$statement->bindValue(":isAvailable", $isAvailable);
		$statement->bindValue(":cae", $cae);

		$statement->execute();
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function GetTiresForYandexMarketMinimalPriceSearch()
	{
		$sql = "SELECT  PTab.brand, PTab.model, PTab.width, PTab.height,
				PTab.diameter, PTab.loadIndex, PTab.speedIndex, PTab.season, PTab.runFlat,
				YMMTab.*, YMOTab.*, PTab.cae
				FROM Products as PTab
				LEFT JOIN ProductsToYandexMarketKeys AS PTYMTab ON PTYMTab.cae = PTab.cae
				LEFT JOIN YMOffers AS YMOTab on YMOTab.cae = PTab.cae
				LEFT JOIN YMModels AS YMMTab on YMMTab.ymModelId = PTYMTab.ymModelId
				WHERE PTab.available IS TRUE AND PTYMTab.updateDate <= NOW() - INTERVAL 1 WEEK
				|| PTYMTab.updateDate IS NULL
				ORDER BY YMMTab.ymModelUpdateDate ASC, model, brand
				";

		$statement = null;
		//если подготовленного выражения нет, то добавим его по ключу
		if ($this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_SELECT_FOR_YM_MINIMAL_PRICES) == null) {
			$statement = $this->_db->prepare($sql);
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_SELECT_FOR_YM_MINIMAL_PRICES, $statement);
		}

		$statement = $this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_SELECT_FOR_YM_MINIMAL_PRICES);

		/**
		 * @var $statement PDOStatement
		 */
		if($statement->execute()){
			$res = $statement->fetchAll(PDO::FETCH_ASSOC);

			return $res;
		}

		throw new Exception("Something going wrong in DB!");
	}

	protected function AddYMModelLink($cae, $ymModelId) {

		$sql = "INSERT INTO 4tochki.ProductsToYandexMarketKeys (cae, ymModelId)
			  	VALUES (:cae, :ymModelId)
				ON DUPLICATE KEY UPDATE
				ymModelId = :ymModelId,
				updateDate = NOW()";

		$statement = null;
		//если подготовленного выражения нет, то добавим его по ключу
		if ($this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YM_LINKS_MODEL) == null) {
			$statement = $this->_db->prepare($sql);
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YM_LINKS_MODEL, $statement);
		}

		/**
		 * @var $statement PDOStatement
		 */
		$statement = $this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YM_LINKS_MODEL);

		$statement->bindValue(":cae", $cae);

		//if ($ymModelId != null)
		$statement->bindValue(":ymModelId", $ymModelId);

		//if ($ymOfferId != null)
		//$statement->bindValue(":ymOfferId", $ymOfferId);

		$statement->execute();

	}

	protected function AddYMOfferLink($cae, $ymOfferId) {

		$sql = "INSERT INTO 4tochki.ProductsToYandexMarketKeys (cae, ymOfferId)
			  	VALUES (:cae, :ymOfferId)
				ON DUPLICATE KEY UPDATE
				ymOfferId = :ymOfferId,
				updateDate = NOW()";

		$statement = null;
		//если подготовленного выражения нет, то добавим его по ключу
		if ($this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YM_LINKS_OFFER) == null) {
			$statement = $this->_db->prepare($sql);
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YM_LINKS_OFFER, $statement);
		}

		/**
		 * @var $statement PDOStatement
		 */
		$statement = $this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YM_LINKS_OFFER);

		$statement->bindValue(":cae", $cae);

		//if ($ymModelId != null)
		//$statement->bindValue(":ymModelId", $ymModelId);

		//if ($ymOfferId != null)
		$statement->bindValue(":ymOfferId", $ymOfferId);

		$statement->execute();

	}


	/**
	 * Сохраняет результат парсинга модели в яндекс-маркете
	 * @param $ymModel YMModel
	 * @return mixed
	 */
	public function AddYMModel($ymModel)
	{
		//var_dump($ymModel);die;

		$this->AddYMModelLink($ymModel->cae, $ymModel->ymModelId);

		if ($ymModel->ymModelId == null)
			return;

		$sql = "INSERT INTO 4tochki.YMModels
			  	(ymModelId, ymModelJsonRaw, ymModelName)
			  	VALUES (:ymModelId, :ymModelJsonRaw, :ymModelName)
				ON DUPLICATE KEY UPDATE
				ymModelId = :ymModelId,
				ymModelUpdateDate = NOW(),
				ymModelJsonRaw = :ymModelJsonRaw,
				ymModelName = :ymModelName";

		$statement = null;
		//если подготовленного выражения нет, то добавим его по ключу
		if ($this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YMMODEL) == null) {
			$statement = $this->_db->prepare($sql);
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YMMODEL, $statement);
		}

		/**
		 * @var $statement PDOStatement
		 */
		$statement = $this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YMMODEL);

		$statement->bindValue(":cae", $ymModel->cae);
		$statement->bindValue(":ymModelId", $ymModel->ymModelId);
		$statement->bindValue(":ymModelJsonRaw", $ymModel->ymModelJsonRaw);
		$statement->bindValue(":ymModelName", $ymModel->ymModelName);

		$statement->execute();
	}

	/**
	 * Добавляет предложение яндекс маркета вместе с магазином
	 * @param YMOffer $ymOffer
	 * @return mixed
	 */
	public function AddYMOffer($ymOffer)
	{
		$this->AddYMOfferLink($ymOffer->cae, $ymOffer->ymOfferId);

		if ($ymOffer->ymOfferId == null)
			return;

		$sql = "INSERT INTO YMOffers (ymOfferId, cae, shopId, price, minimalPrice, ymOfferJsonRaw,
				ymModelId, ymRegionId, ymModelIdReturned)
				VALUES (:ymOfferId, :cae, :shopId, :price, :minimalPrice, :ymOfferJsonRaw,:ymModelId, :ymRegionId,
				:ymModelIdReturned)
				ON DUPLICATE KEY UPDATE
				ymOfferId = :ymOfferId,
				cae = :cae,
				shopId = :shopId,
				price = :price,
				minimalPrice = :minimalPrice,
				ymOfferJsonRaw = :ymOfferJsonRaw,
				ymModelId = :ymModelId,
				ymRegionId = :ymRegionId,
				ymModelIdReturned = :ymModelIdReturned
				";

		$statement = null;
		//если подготовленного выражения нет, то добавим его по ключу
		if ($this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YMOFFER) == null) {
			$statement = $this->_db->prepare($sql);
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YMOFFER, $statement);
		}

		/**
		 * @var $statement PDOStatement
		 */
		$statement = $this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YMOFFER);
		$statement->bindValue(":ymOfferId", $ymOffer->ymOfferId);
		$statement->bindValue(":cae", $ymOffer->cae);
		$statement->bindValue(":shopId", $ymOffer->shopId);
		$statement->bindValue(":price", $ymOffer->price);
		$statement->bindValue(":minimalPrice", $ymOffer->minimalPrice);
		$statement->bindValue(":ymOfferJsonRaw", $ymOffer->ymOfferJsonRaw);
		$statement->bindValue(":ymModelId", $ymOffer->ymModelId);
		$statement->bindValue(":ymRegionId", $ymOffer->ymRegionId);
		$statement->bindValue(":ymModelIdReturned", $ymOffer->ymModelIdReturned);

		$statement->execute();
	}

	/**
	 * Добавляет магазин указанный в yandex-market
	 * @param $ymShop YMShop
	 * @return mixed
	 */
	public function AddYMShop($ymShop)
	{
		var_dump($ymShop);
		$sql = "INSERT INTO YMShops (`ymShopId`, `name`, `siteUrl`, `jsonRaw`)
				VALUES (:ymShopId, :name, :siteUrl, :jsonRaw)
				ON DUPLICATE KEY UPDATE
				name = :name,
				siteUrl = :siteUrl,
				jsonRaw = :jsonRaw";

		$statement = null;
		//если подготовленного выражения нет, то добавим его по ключу
		if ($this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YMOSHOP) == null) {
			$statement = $this->_db->prepare($sql);
			$this->SetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YMOSHOP, $statement);
		}

		/**
		 * @var $statement PDOStatement
		 */
		$statement = $this->GetPreparedStatementByKey(self::KEY_PREPARED_STATEMENT_ADD_YMOSHOP);


		$statement->bindValue(":ymShopId", $ymShop->id);
		$statement->bindValue(":name", $ymShop->name);
		$statement->bindValue(":siteUrl", $ymShop->siteUrl);
		$statement->bindValue(":jsonRaw", $ymShop->jsonRaw);

		$statement->execute();
	}

	/**
	 * Возвращает данные (по минимальным ценам из яндекс-маркет результатов АПИ парсинга)
	 * для рендеринга
	 * @return mixed|array
	 */
	public function GetYMTiresMinPriceDataForRender()
	{
		$sql = "SELECT *, ymo.price FROM YMOffers as ymo
				left join Products as p on p.cae = ymo.cae
				left join YMShops as yms on yms.ymShopId = ymo.shopId
				";

		return $this->_db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}
}