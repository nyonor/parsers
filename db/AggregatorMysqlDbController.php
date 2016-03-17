<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 29.02.16
 * Time: 12:47
 */

/**
 * Class AggregatorMysqlDbController @deprecated
 */
class AggregatorMysqlDbController extends MysqlDbController implements IAggregatorDbController
{
	const KEY_PREPARED_STATEMENT_INSERT_AGGREGATOR_PARSED_RESULT = "aggregatorInsRes";

	/**
	 * Сохранение спарсенного результата
	 * @param TireModelMinPriceInfo $objectToInsert
	 */
	public function AddParsingResult($objectToInsert) {

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
		$query = "SELECT * FROM TireModelMinPriceInfo as TMPI";
		$result = $this->_db->query($query)->fetchAll(PDO::FETCH_CLASS, 'TireModelMinPriceInfo');
		return $result;
	}
}