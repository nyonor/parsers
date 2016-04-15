<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 14.03.16
 * Time: 15:08
 */
interface IUniversalRenderer extends IRenderer
{

	/**
	 * Название колонок
	 * @param $rowNames array
	 * @return mixed
	 */
	function SetColumnNames($rowNames);

	/**
	 * Значения одного ряда
	 * @param $rowValues array
	 * @return mixed
	 */
	function FeedValues($rowValues);

	function Clear();

	function SetFileName($fileName);

}