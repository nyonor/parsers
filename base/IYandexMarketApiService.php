<?php
use models\YMModelDetailed;
use \models\YMOfferDetailed;

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.04.16
 * Time: 14:51
 */

interface IYandexMarketApiService
{

	/**
	 * Поиск модели яндекс-маркета по имени
	 * @param $name
	 * @param $returnFields
	 * @param $regionId
	 * @return YMModelDetailed
	 */
     function FindYMModelsByParams($name, $returnFields, $regionId);

	/**
	 * @param $ymModelId int
	 * @param $ymGeoId
	 * @param $ymReturnFields
	 * @param $filtersDictionary array ассоциативный массив по типу [название_параметра=>значение]
	 * @param $sortBy string
	 * @param $page int
	 * @param $ymCategoryId
	 * @param $count int
	 * @return mixed|\models\YMOfferDetailed[]|string json
	 */
	function FindYMOffersByModel ($ymModelId, $ymGeoId, $ymReturnFields, $filtersDictionary, $sortBy, $page, $ymCategoryId, $count);

	/**
	 * Получает данные по фильтрам от yandex-market,
	 * которые отнсятся к категории
	 * @param int $categoryId
	 * @param int $geoId
	 * @return stdClass
	 */
	function FindFiltersByYMCategoryId($categoryId = YandexMarketApiService::YM_CATEGORY_TIRES_ID,
									   $geoId = YandexMarketApiService::YM_GEO_ID_MOSCOW);
}