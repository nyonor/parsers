<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 30.10.15
 * Time: 14:50
 */

//namespace nyonor\renderers\interfaces;


interface IRenderer
{
	/**
	 * @param $arg mixed
	 * @return mixed
	 */
	function Render($arg);
}