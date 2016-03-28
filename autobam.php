<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 07.12.15
 * Time: 7:53
 */

//настроим выполнение
//error_reporting(E_ERROR);
ini_set("display_errors", true);
error_reporting(E_ALL);

//todo СДЕЛАТЬ АВТОЛОАДЕР!!!!!!
//require_once "include.php";
require_once "sys/MyLogger.php";
require_once "base/IParseHub.php";
require_once "base/RivalParserBase.php";
require_once 'base/RivalParseHub.php';
require_once 'base/IProductParametersParser.php';
require_once 'base/ProductParametersParserTrait.php';
require_once 'parsers/AutobamParser.php';
require_once 'models/TireModel.php';
require_once 'models/RivalTireModel.php';
require_once 'models/ProductTireModel.php';
require_once 'models/ComparisonResult.php';
require_once 'models/SeasonModel.php';
require_once 'db/MongoDbController.php';
require_once 'db/MysqlDbController.php';
require_once 'base/IProductsUpdater.php';
require_once 'base/ProductsUpdater.php';
require_once 'base/IRenderer.php';
require_once 'renderers/CsvRenderer.php';
require_once 'sys/Timer.php';
require __DIR__ . '/vendor/autoload.php';

$hub = new RivalParseHub();
$db = new MysqlDbController();
$hub->InjectDBController($db);

//зима
$urlPattern  = "http://www.autobam.ru/tyres/filter?season=winter&w=-&h=-&r=&thorns=-&brand=-&w2=-&h2=-&r2=&thorns2=-&brand2=-&nonavail=0&page=%d";
$parser = new AutobamParser($urlPattern);
$parser->season = SeasonModel::WINTER;
$hub->InjectParser($parser)
	->ProcessParsedDataFromInjectedParserToDB(true);

//лето
$urlPattern = "http://www.autobam.ru/tyres/filter?season=summer&w=-&h=-&r=&thorns=-&brand=-&w2=-&h2=-&r2=&thorns2=-&brand2=-&nonavail=0&page=%d";
$parser = new AutobamParser($urlPattern);
$parser->season = SeasonModel::SUMMER;
$hub->InjectParser($parser)
	->ProcessParsedDataFromInjectedParserToDB(false);

$comparedResult = $hub->GetComparingResult(AutobamParser::SITE_URL);

$renderer = new CsvRenderer(str_replace('.','',AutobamParser::SITE_URL));
$renderer->Render($comparedResult);