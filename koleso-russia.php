<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 25.09.15
 * Time: 10:57
 */

//настроим выполнение

//todo избавиться от require_once в пользу
require_once "include.php";
require_once "base/RivalParserBase.php";
require_once 'base/RivalParseHub.php';
require_once 'base/IProductParametersParser.php';
require_once 'parsers/KolesoRussiaParser.php';
require_once 'models/TireModel.php';
require_once 'models/RivalTireModel.php';
require_once 'models/ProductTireModel.php';
require_once 'models/ComparisonResult.php';
require_once 'models/SeasonModel.php';
require_once 'db/MongoDbController.php';
require_once 'db/MysqlDbController.php';
require_once 'base/IProductsUpdater.php';
require_once 'base/ProductsUpdater.php';
require __DIR__ . '/vendor/autoload.php';

header('Content-Type: text/html; charset=utf-8');

$urlPattern = "http://www.koleso-russia.ru/catalog/search/tires/bysize/?PAGEN_1=%d&AJAX=Y";

$hub = new RivalParseHub();
//$hub->ExecuteNodeJsScript("js/nodeJsKolesoRussia.js");
//$hub->ProcessParsedDataFromFileToDB("kolesoRussiaSwapFile.txt");
$hub->shouldUpdateProductsBeforeParsingResults = false;
$hub->InjectParser(new KolesoRussiaParser($urlPattern))
	->InjectDBController(new MysqlDbController())
	->ProcessParsedDataFromInjectedParserToDB()
	->GetComparingResultAsCsv(KolesoRussiaParser::SITE_URL);