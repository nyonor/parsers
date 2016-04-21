<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 21.04.16
 * Time: 12:48
 */

ini_set("display_errors", true);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require_once "sys/myAutoLoader.php";

use models\YMOfferDetailed;
use models\YMTireCategory;

if (empty($_GET['caes']) == true) {

	echo "NO CAES PROVIDED!";
	die;
}

const YANDEX_MARKET_TIRES_MINIMAL_PRICES = "ymTiresMinimalPrices";
$db = new MysqlDbController();

$controller = new YandexMarketController($db, new YandexMarketApiService(),
	new CsvUniversalRenderer(YANDEX_MARKET_TIRES_MINIMAL_PRICES), new ProductsUpdater($db));

$caes = is_array($_GET['caes']) ? $_GET['caes'] : [$_GET['caes']];

$controller
	->GetMinimalPricesOnTires("москва", $caes);
	//->Render(YandexMarketController::RENDER_FOR_USER)
	//->Render(YandexMarketController::RENDER_FOR_1C);