<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.04.16
 * Time: 14:23
 */

/**
 * @deprecated
 * Class YandexMarketApiParser
 */
class YandexMarketApiParser
{

	/**
	 * @var IYandexMarketApiService
	 */
	protected $_yandexApiRequester;
	protected $_tires;

	/**
	 * @param IInstantStore $parseHub
	 * @param IYandexMarketApiService $ymApiRequester
	 * @param $tires ProductTireModel[]
	 */
	public function __construct(IInstantStore $parseHub, IYandexMarketApiService $ymApiRequester, $tires) {

		parent::__construct(null, $parseHub);

		$this->_yandexApiRequester = $ymApiRequester;
		$this->_tires = $tires;

	}

	/**
	 * Постпроцессинг данных собранных парсером-аггрегатора
	 * @return mixed
	 */
	public function PostProcess()
	{
		// TODO: Implement PostProcess() method.
	}

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		// TODO: Implement Parse() method.
		//начнем поиск
		foreach($this->_tires as $prodTireModel) {

			//$this->_yandexApiRequester->

		}
	}

	/**
	 * Возвращает url сайта для парсинга
	 * @return string
	 */
	public function GetSiteToParseUrl()
	{
		// TODO: Implement GetSiteToParseUrl() method.
	}

	/**
	 * Возвращает готовый объект curl для запросов
	 * @param $url
	 * @return resource
	 */
	protected function GetCurl($url)
	{
		// TODO: Implement GetCurl() method.
	}
}