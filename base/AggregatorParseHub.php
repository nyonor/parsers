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

	/**
	 * @var AggregatorParserBase
	 */
	protected $_currentParser;

	/**
	 * @param bool|true $shouldTruncateOldData todo параметр не учитывается! все еще
	 * @return $this
	 */
	public function ProcessParsedDataFromInjectedParserToDB($shouldTruncateOldData = true) {

		//начинаем парсинг
		$this->_currentParser->SetIDbController($this->_dbController);
		$this->_lastParseResults = $this->_currentParser->Parse($this->_dbController);
		return $this;

	}

	public function PostProcess() {

		$this->_currentParser->SetIDbController($this->_dbController);
		$this->_currentParser->PostProcess();

	}

	/**
	 * Установка парсера - он содержит всю логику парсинга!
	 * Метод отличается от метода реализованного предком!!!
	 * @param AggregatorParserBase|RivalParserBase $parser
	 * @return $this
	 */
	public function InjectParser(AggregatorParserBase $parser) {

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

		$result = "";

		if($this->_lastParseResults == null) {

			$result = $this->_dbController->GetAllMinimalPriceInfoProductModels();

		} else {

			$result = $this->_lastParseResults;

		}

		return $result;

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

	/**
	 * Сохраняем результат парсинга
	 * @param $object TireModelMinPriceInfo
	 * @throws Exception
	 */
	protected function StoreObjectToDB($object) {
		if ($this->_dbController == null)
			throw new Exception("IDbController should be injected to RivalParserHub!");
		$this->_dbController->AddAggregatorParsingResult($object);
	}

	function UpdateProducts() {

		parent::UpdateProducts();

		$this->_productsUpdater->UpdateProductsAvailability();

	}

}