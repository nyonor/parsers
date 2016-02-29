<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 29.02.16
 * Time: 12:10
 */
class AggregatorParseHub extends RivalParseHub implements IParseHub
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
		$this->_lastParseResults = $parsedModel;

		//пишем в бд
		foreach($this->_lastParseResults as $parseRes) {

			$this->StoreObjectToDB($parseRes);

		}

		return $this;

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
}