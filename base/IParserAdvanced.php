<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 13.11.15
 * Time: 17:50
 */


interface IParserAdvanced extends IProductParametersParser
{
	/**
	 * Получить марку (Бренд) используя список брендов
	 * @param $subject
	 * @param $allBrands
	 * @return string
	 */
	function GetBrandWithList($subject, $allBrands);
}