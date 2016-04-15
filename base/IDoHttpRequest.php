<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 07.04.16
 * Time: 11:26
 */

namespace base;


interface IDoHttpRequest
{
	/**
	 * Делает запрос по http с указанием url запроса
	 * и параметрами ожидания после запроса
	 * @param $url string|mixed
	 * @param $minWaitSeconds int|float
	 * @param $maxWaitSeconds int|float
	 * @return mixed|string
	 */
	function Request($url, $minWaitSeconds = 0, $maxWaitSeconds = 0);
}