<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 18.04.16
 * Time: 9:31
 */

ini_set("display_errors", true);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require_once "sys/myAutoLoader.php";

use models\YMOfferDetailed;
use models\YMTireCategory;


const YANDEX_MARKET_TIRES_MINIMAL_PRICES = "ymTiresMinimalPrices";
$db = new MysqlDbController();

$controller = new YandexMarketController($db, new YandexMarketApiService(),
	new CsvUniversalRenderer(YANDEX_MARKET_TIRES_MINIMAL_PRICES), new ProductsUpdater($db));

$controller
	->Render(YandexMarketController::RENDER_FOR_USER)
	->Render(YandexMarketController::RENDER_FOR_1C);