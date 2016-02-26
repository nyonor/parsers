<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 13.12.15
 * Time: 16:49
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
require_once 'parsers/SvrautoParser.php';
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

//лето
$urlPattern  = "http://www.svrauto.ru/index.php/tyre/size/%d/0/0/0/0/abc/%d";
$parser = new SvrautoParser($urlPattern);
$parser->season = SeasonModel::SUMMER;
$hub->InjectParser($parser)
	->ProcessParsedDataFromInjectedParserToDB(true);

//зима
$urlPattern  = "http://www.svrauto.ru/index.php/tyre/size/%d/0/0/0/1/abc/%d";
$parser = new SvrautoParser($urlPattern);
$parser->season = SeasonModel::WINTER;
$hub->InjectParser($parser)
	->ProcessParsedDataFromInjectedParserToDB(false);


//всесезон
$urlPattern  = "http://www.svrauto.ru/index.php/tyre/size/%d/0/0/0/2/abc/%d";
$parser = new SvrautoParser($urlPattern);
$parser->season = SeasonModel::ALL_SEASONS;
$hub->InjectParser($parser)
	->ProcessParsedDataFromInjectedParserToDB(false);

$comparedResult = $hub->GetComparingResult(SvrautoParser::SITE_URL);

$renderer = new CsvRenderer(str_replace('.','',SvrautoParser::SITE_URL));
$renderer->Render($comparedResult);