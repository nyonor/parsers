<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.10.15
 * Time: 20:35
 * Класс представляет из себя контейнер для хранения данных по 'спарсенной' шине
 */
class TireModel
{
	/**
	 * @var string
	 * Бренд
	 */
	public $brand;

	/**
	 * @var string
	 * Модель
	 */
	public $model;

	/**
	 * @var float
	 * Ширина
	 */
	public $width;

	/**
	 * @var float
	 * Профиль
	 */
	public $height;

	/**
	 * @var string
	 * Конструкция
	 */
	public $constructionType;

	/**
	 * @var float
	 * Диаметр
	 */
	public $diameter;

	/**
	 * @var float
	 * Индекс нагрузки
	 */
	public $loadIndex;

	/**
	 * @var string
	 * Индекс скорости
	 */
	public $speedIndex;

	/**
	 * @var string | int;
	 * Сезон
	 */
	public $season;

	/**
	 * @var boolean
	 * Технология Run Flat?
	 */
	public $runFlat;
}