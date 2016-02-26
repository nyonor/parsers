<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 11.01.16
 * Time: 13:40
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
require_once 'parsers/AlltyresParser.php';
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
$urlPattern  = "http://all-tyres.ru/tyres/?tyres%5BBRAND%5D%5B0%5D=ANTARES&tyres%5BBRAND%5D%5B1%5D=BFGOODRICH&tyres%5BBRAND%5D%5B2%5D=BRIDGESTONE&tyres%5BBRAND%5D%5B3%5D=CONTINENTAL&tyres%5BBRAND%5D%5B4%5D=COOPER&tyres%5BBRAND%5D%5B5%5D=CORDIANT&tyres%5BBRAND%5D%5B6%5D=DUNLOP&tyres%5BBRAND%5D%5B7%5D=ECOVISION&tyres%5BBRAND%5D%5B8%5D=FEDERAL&tyres%5BBRAND%5D%5B9%5D=GISLAVED&tyres%5BBRAND%5D%5B10%5D=GOODYEAR&tyres%5BBRAND%5D%5B11%5D=HANKOOK&tyres%5BBRAND%5D%5B12%5D=HEADWAY&tyres%5BBRAND%5D%5B13%5D=HERCULES&tyres%5BBRAND%5D%5B14%5D=INTERCO&tyres%5BBRAND%5D%5B15%5D=JINYU&tyres%5BBRAND%5D%5B16%5D=KINGSTAR&tyres%5BBRAND%5D%5B17%5D=KLEBER&tyres%5BBRAND%5D%5B18%5D=KUMHO&tyres%5BBRAND%5D%5B19%5D=MARSHAL&tyres%5BBRAND%5D%5B20%5D=MATADOR&tyres%5BBRAND%5D%5B21%5D=MAXXIS&tyres%5BBRAND%5D%5B22%5D=MICHELIN&tyres%5BBRAND%5D%5B23%5D=MICKEY+THOMPSON&tyres%5BBRAND%5D%5B24%5D=NEXEN&tyres%5BBRAND%5D%5B25%5D=NITTO&tyres%5BBRAND%5D%5B26%5D=NOKIAN&tyres%5BBRAND%5D%5B27%5D=OVATION&tyres%5BBRAND%5D%5B28%5D=PIRELLI&tyres%5BBRAND%5D%5B29%5D=ROADSTONE&tyres%5BBRAND%5D%5B30%5D=SAILUN&tyres%5BBRAND%5D%5B31%5D=SAVA&tyres%5BBRAND%5D%5B32%5D=SIMEX&tyres%5BBRAND%5D%5B33%5D=SUNNY&tyres%5BBRAND%5D%5B34%5D=TIGAR&tyres%5BBRAND%5D%5B35%5D=TOYO&tyres%5BBRAND%5D%5B36%5D=TRIANGLE&tyres%5BBRAND%5D%5B37%5D=UNIROYAL&tyres%5BBRAND%5D%5B38%5D=YOKOHAMA&tyres%5BBRAND%5D%5B39%5D=%C0%CB%D2%C0%C9%D8%C8%CD%C0&tyres%5BBRAND%5D%5B40%5D=%CA%C0%CC%C0&tyres%5BBRAND%5D%5B41%5D=%CA%D8%C7&SHOWALL_1=1";
$parser = new AlltyresParser($urlPattern);
$hub->InjectParser($parser)
	->ProcessParsedDataFromInjectedParserToDB(true);

$comparedResult = $hub->GetComparingResult(AlltyresParser::SITE_URL);

$renderer = new CsvRenderer(str_replace('.','',AlltyresParser::SITE_URL));
$renderer->Render($comparedResult);