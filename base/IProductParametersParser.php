<?php

/**
 * Класс, который реализует этот интерфейс позволит получать параметры товаров из входного аргумента
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 27.10.15
 * Time: 12:17
 * например с помощью RegExp
 */
interface IProductParametersParser
{
	/**
	 * Получить имя модели
	 * @param $subject
	 * @return string
	 */
	function GetModelName($subject);

	/**
	 * Возвращает сезон шин
	 * @param $subject
	 * @return string
	 */
	function GetSeason($subject);

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
	function GetQuantity($subject); //todo РЕАЛИЗУЙ!

	//TODO: ДОПИСАТЬ МЕТОДЫ из KolesoRussiaParser->Parse()
}