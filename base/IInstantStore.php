<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 01.03.16
 * Time: 9:58
 */
interface IInstantStore
{
	/**
	 * Должен реализовавывать возможность
	 * сохранить результат парсинга сразу
	 * @param $result
	 */
	function InstantStoreResult($result);
}