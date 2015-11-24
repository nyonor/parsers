<?php

/**
 * Класс, который реализует этот интерфейс позволит получать параметры товаров
 * из входного аргумента
 * например с помощью RegExp
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 27.10.15
 * Time: 12:17
 */
interface IProductParametersParser
{

	/**
	 * Получить марку (Бренд)
	 * @param $subject
	 * @return string
	 * @internal param $allModels
	 */
	function GetBrand($subject);

	/**
	 * Получить имя модели
	 * @param $subject
	 * @return string
	 */
	function GetModelName($subject);

	/**
	 * Получить ширину шины
	 * @param $subject
	 * @return string
	 */
	function GetWidth($subject);

	/**
	 * Получить профиль шины
	 * @param $subject
	 * @return string
	 */
	function GetHeight($subject);

	/**
	 * Получить тип конструкции
	 * @param $subject
	 * @return string
	 */
	function GetConstructionType($subject);

	/**
	 * Получить диаметр шины
	 * @param $subject
	 * @return string
	 */
	function GetDiameter($subject);

	/**
	 * Получить индекс нагрузки
	 * @param $subject
	 * @return string
	 */
	function GetLoadIndex($subject);

	/**
	 * Получить индекс скорости
	 * @param $subject
	 * @return string
	 */
	function GetSpeedIndex($subject);

	/**
	 * Возвращает сезон шин
	 * @param $subject
	 * @return string
	 */
	function GetSeason($subject);

	/**
	 * Получить runFlat
	 * @param $subject
	 * @return string
	 */
	function GetRunFlat($subject);

	/**
	 * Получить имя сайта
	 * @return string
	 */
	function GetSiteName();

	/**
	 * Получить url спарсенного товара
	 * @return string
	 */
	function GetParseUrl();

	/**
	 * Возвращает цену
	 * @param $subject
	 * @return float
	 */
	function GetPrice($subject);

	/**
	 * Возвращает количество
	 * @param $subject
	 * @return int
	 */
	function GetQuantity($subject);

	/**
	 * Получить имя модели
	 * @param $subject
	 * @return string
	 */
	function GetProductType($subject);
}