<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 25.09.15
 * Time: 10:57
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
require_once 'base/IRenderer.php';
require_once 'renderers/CsvRenderer.php';
require_once 'sys/Timer.php';
require __DIR__ . '/vendor/autoload.php';


$urlPattern = "http://www.koleso-russia.ru/catalog/search/tires/bysize/?PAGEN_1=%d&AJAX=Y";

$hub = new RivalParseHub();
$compared = $hub->InjectParser(new KolesoRussiaParser($urlPattern))
	->InjectDBController(new MysqlDbController())
	->UpdateProducts()
	->ProcessParsedDataFromInjectedParserToDB()
	->GetComparingResult(KolesoRussiaParser::SITE_URL);

$renderer = new CsvRenderer(str_replace('.','',KolesoRussiaParser::SITE_URL));
$renderer->Render($compared);