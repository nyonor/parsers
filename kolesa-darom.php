<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 13.11.15
 * Time: 14:33
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
require_once 'base/IParserAdvanced.php';
require_once 'base/ProductParametersParserTrait.php';
require_once 'parsers/KolesaDaromParser.php';
require_once 'models/TireModel.php';
require_once 'models/RivalTireModel.php';
require_once 'models/ProductTireModel.php';
require_once 'models/ComparisonResult.php';
require_once 'models/SeasonModel.php';
require_once 'db/MysqlDbController.php';
require_once 'base/IProductsUpdater.php';
require_once 'base/ProductsUpdater.php';
require_once 'base/IRenderer.php';
require_once 'renderers/CsvRenderer.php';
require_once 'sys/Timer.php';
require __DIR__ . '/vendor/autoload.php';

$hub = new RivalParseHub();
$hub->InjectDBController(new MysqlDbController());
//$hub->UpdateProducts();

//парсим летние шины
$url = "http://kolesa-darom.ru/nn/shiny/letnie/?cur_cc=15772&recNum=50&curPos=%d";
$parser = new KolesaDaromParser($url);
$parser->season = SeasonModel::SUMMER;
$hub->InjectParser($parser);
$hub->ProcessParsedDataFromInjectedParserToDB(true);

//sleep(rand(20,30)); //ждем

//парсим зимние шины
$url = "http://www.kolesa-darom.ru/nn/shiny/zimnie/?cur_cc=15772&recNum=50&curPos=%d";
$parser = new KolesaDaromParser($url);
$parser->season = SeasonModel::WINTER;
$hub->InjectParser($parser);
$hub->ProcessParsedDataFromInjectedParserToDB(false);

$compared = $hub->GetComparingResult(KolesaDaromParser::SITE_URL);

$renderer = new CsvRenderer(str_replace('.','',KolesaDaromParser::SITE_URL));
$renderer->Render($compared);