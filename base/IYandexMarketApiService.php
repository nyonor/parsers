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
	 * @param $model mixed|YMModelDetailed
	 * @return mixed|YMModel|YMModel[]
	 */
	function FindYMModelsByName($model);

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

}