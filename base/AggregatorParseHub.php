<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 29.02.16
 * Time: 12:10
 */
class AggregatorParseHub extends RivalParseHub implements IInstantStore
{

	/**
	 * @var IAggregatorDbController
	 */
	protected $_dbController;

	/**
	 * @var TireModelMinPriceInfo[]
	 */
	protected $_lastParseResults;

	public function ProcessParsedDataFromInjectedParserToDB($shouldTruncateOldData = true) {

		//начинаем парсинг
		$this->_currentParser->SetIDbController($this->_dbController);
		$parsedModel = $this->_currentParser->Parse($this->_dbController);
		/*$this->_lastParseResults = $parsedModel;

		//пишем в бд
		foreach($this->_lastParseResults as $parseRes) {

			$this->StoreObjectToDB($parseRes);

		}*/

		return $this;

	}

	/**
	 * Установка парсера - он содержит всю логику парсинга!
	 * Метод отличается от метода реализованного предком!!!
	 * @param RivalParserBase $parser
	 * @return $this
	 */
	public function InjectParser(RivalParserBase $parser) {

		if (is_a($parser, "AggregatorParserBase")) {

			/**
			 * @var $parser AggregatorParserBase
			 */
			$this->_currentParser = $parser;
			return $this;

		} else {

			throw new InvalidArgumentException("Injected parser should be of AggregatorParserBase class");

		}
	}

	/**
	 * Возвращает результат парсинга минимальных цен аггрегатора (ЯндексМаркета)
	 * @param null $rivalSiteUrl
	 * @return TireModelMinPriceInfo[]
	 */
	public function GetComparingResult($rivalSiteUrl = null) {

		var_dump("А ТУТ РЕЗУЛЬТАТ!!!");
		var_dump($this->_lastParseResults);

		die;//todo!!!!
	}

	/**
	 * Должен реализовавывать возможность
	 * сохранить результат парсинга сразу
	 * @param $result
	 */
	function InstantStoreResult($result)
	{

		if (is_a($result, "TireModelMinPriceInfo")) {

			$this->StoreObjectToDB($result);

		}

	}
}