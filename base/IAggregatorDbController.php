<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 29.02.16
 * Time: 13:11
 */

/**
 * Interface IAggregatorDbController @deprecated
 */
interface IAggregatorDbController extends IDbController
{

	/**
	 * Возвращает все данные о минимальных цена и конкурентах
	 * собранные по аггрегатору (ЯндексМаркет)
	 * @return TireModelMinPriceInfo[]|array|mixed
	 */
	public function GetAllMinimalPriceInfoProductModels();
}