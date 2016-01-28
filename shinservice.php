<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 25.01.16
 * Time: 12:31
 */

//настроим выполнение
//error_reporting(E_ERROR);
ini_set("display_errors", true);
error_reporting(E_ALL);

//todo СДЕЛАТЬ АВТОЛОАДЕР!!!!!!
//require_once "include.php";
require_once "base/RivalParserBase.php";
require_once 'base/RivalParseHub.php';
require_once 'base/IProductParametersParser.php';
require_once 'base/ProductParametersParserTrait.php';
require_once 'parsers/ShinserviceParser.php';
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

$hub = new RivalParseHub();
$db = new MysqlDbController();
$hub->InjectDBController($db);

//все и вся
$urlPattern  = "http://www.shinservice.ru/search/ALL/winter/%d-%d-R%d/";
$parser = new ShinserviceParser($urlPattern);
$hub->InjectParser($parser)
	->ProcessParsedDataFromInjectedParserToDB(true);

$comparedResult = $hub->GetComparingResult(ShinserviceParser::SITE_URL);

$renderer = new CsvRenderer(str_replace('.','',ShinserviceParser::SITE_URL));
$renderer->Render($comparedResult);