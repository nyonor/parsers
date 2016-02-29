<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 29.02.16
 * Time: 13:11
 */
interface IAggregatorDbController extends IDbController
{

	/**
	 * Возвращает все данные о минимальных цена и конкурентах
	 * собранные по аггрегатору (ЯндексМаркет)
	 * @return mixed
	 */
	public function GetAllMinimalPriceInfoProductModels();
}