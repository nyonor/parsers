<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 26.10.15
 * Time: 13:25
 * Класс который реализует данный интерфейс может обновлять нашу номенклатуру в БД (табл. Products)
 */
interface IProductsUpdater
{
	/**
	 * Обновить продукцию
	 * @return mixed
	 */
	function UpdateProducts();
}