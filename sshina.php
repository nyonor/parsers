<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 19.03.16
 * Time: 12:20
 */
require __DIR__ . '/vendor/autoload.php';

require_once "sys/MyLogger.php";
require_once "base/IParseHub.php";
require_once "base/RivalParserBase.php";
require_once 'base/RivalParseHub.php';
require_once 'base/IProductParametersParser.php';
require_once 'base/ProductParametersParserTrait.php';
require_once 'parsers/SShinaParser.php';
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

$hub = new RivalParseHub();
$db = new MysqlDbController();
$hub->InjectDBController($db);

$urlPattern  = "http://www.s-shina.ru/tyre/search/?autoproducer=&automodel=&autoyears=&automodifity=&autopod=&producer=&series=&runflat=0&commercial_auto=0&tyre_w=0&tyre_h=0&tyre_r=0&price_min=0&price_max=0&&page=%d";
$parser = new SShinaParser($urlPattern);
$hub->InjectParser($parser)
	->ProcessParsedDataFromInjectedParserToDB(true);

$comparedResult = $hub->GetComparingResult(SShinaParser::SITE_URL);

$renderer = new CsvRenderer(str_replace('.','',SShinaParser::SITE_URL));
$renderer->Render($comparedResult);