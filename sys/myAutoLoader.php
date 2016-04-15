<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.04.16
 * Time: 16:20
 * @param $className string
 */

function myAutoLoad($className) {

	$dirsToLook = [
		'base',
		'db',
		'models',
		'parsers',
		'renderers',
		'services',
		'sys'
	];

	//если класс с неймспейсами - превратим их просто в директории для поиска файла
	if (stripos($className, '\\') !== false) {

		$replaced = str_ireplace('\\','/',$className) . ".php";

		if (file_exists($replaced)) {

			require_once $replaced;
			return;

		}

	}

	//если класс из глобального неймспейса - будем искать его в папках (всего один уровень вложенности)
	foreach ($dirsToLook as $dirToLook) {

		$path = $dirToLook."/".$className.".php";

		if (file_exists($path)) {

			require_once $path;

		}

	}

}

spl_autoload_register("myAutoLoad");