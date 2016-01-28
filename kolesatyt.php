<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 16.12.15
 * Time: 14:30
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
require_once 'parsers/KolesatytParser.php';
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
require_once 'sys/MyLogger.php';

$hub = new RivalParseHub();
$db = new MysqlDbController();
$hub->InjectDBController($db);

//все и вся
$urlPattern  = "http://kolesatyt.ru/podbor/shiny/?PAGEN_1=%d";
$parser = new KolesatytParser($urlPattern);
$hub->InjectParser($parser)
	->ProcessParsedDataFromInjectedParserToDB(true);

$comparedResult = $hub->GetComparingResult(KolesatytParser::SITE_URL);

$renderer = new CsvRenderer(str_replace('.','',KolesatytParser::SITE_URL));
$renderer->Render($comparedResult);