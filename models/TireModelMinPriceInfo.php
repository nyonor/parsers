<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 27.02.16
 * Time: 14:27
 *
 * Модель для хранения данных с минимальной ценой на товар
 */
class TireModelMinPriceInfo extends ProductTireModel
{
	/**
	 * Url яндекс маркета
	 * @var string
	 */
	public $yandexMarketUrl;

	/**
	 * Адрес магазина с минимальной ценой
	 * @var string
	 */
	public $rivalStoreUrl;

	/**
	 * Название магазина с минимальной ценой
	 * @var string
	 */
	public $rivalStoreName;

	/**
	 * Минимальная цена на товар указанная яндекс маркетом
	 * @var int
	 */
	public $minimalPrice;
}