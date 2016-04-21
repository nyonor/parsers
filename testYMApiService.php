<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 19.04.16
 * Time: 8:12
 */


require __DIR__ . '/vendor/autoload.php';
require_once "sys/myAutoLoader.php";

$db = new MysqlDbController();
/**
 * @var TireModel $prod
 */
$prods = $db->GetProductsByCae(['2366300'], "ProductTireModel");
var_dump($prods);//die;

$ym = new YandexMarketApiService();

foreach($prods as $prod){

	$modelName = $modelNameConcrete = "Шина" . " " . $prod->brand. " " . $prod->model . " " .
		(int)$prod->width ."/". (int)$prod->height . " " . "R" .
		(int)$prod->diameter;

	var_dump($modelName);//die;

	$res = $ym->FindYMModelsByParams($modelName, "all", 213);
	var_dump($res);

}