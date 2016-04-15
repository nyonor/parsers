<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 05.04.16
 * Time: 11:34
 */

//настроим выполнение
//error_reporting(E_ERROR);
ini_set("display_errors", true);
error_reporting(E_ALL);

//todo СДЕЛАТЬ АВТОЛОАДЕР!!!!!!
//require_once "include.php";
require_once "base/IParseHub.php";
require_once "base/RivalParserBase.php";
require_once 'base/RivalParseHub.php';
require_once 'base/IProductParametersParser.php';
require_once 'base/ProductParametersParserTrait.php';
require_once 'base/IInstantStore.php';
require_once 'base/AggregatorParserBase.php';
require_once 'parsers/YandexMarketParser.php';
require_once 'models/TireModel.php';
require_once 'models/RivalTireModel.php';
require_once 'models/ProductTireModel.php';
require_once 'models/ComparisonResult.php';
require_once 'models/SeasonModel.php';
require_once 'models/TypeSizeModel.php';
require_once 'db/MongoDbController.php';
require_once 'db/MysqlDbController.php';
require_once 'base/IProductsUpdater.php';
require_once 'base/ProductsUpdater.php';
require_once 'base/IRenderer.php';
require_once 'renderers/CsvRenderer.php';
require_once 'sys/Timer.php';
require __DIR__ . '/vendor/autoload.php';
require_once 'sys/MyLogger.php';
require_once 'models/TireModelMinPriceInfo.php';
require_once 'base/IAggregatorDbController.php';
require_once 'base/AggregatorParseHub.php';
require_once 'db/AggregatorMysqlDbController.php';
require_once 'base/IUniversalRenderer.php';
require_once 'renderers/CsvUniversalRenderer.php';

$hub = new AggregatorParseHub();
$hub->InjectDBController(new MysqlDbController())
	->UpdateProducts();