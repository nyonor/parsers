<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.10.15
 * Time: 20:35
 * Класс представляет из себя контейнер для хранения данных по 'спарсенной' шине
 */
class RivalTireModel extends TireModel
{
	/**
	 * @var string
	 */
	public $site;

	/**
	 * @var string
	 */
	public $url;

	/**
	 * @var float
	 */
	public $price;
}