<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.04.16
 * Time: 14:22
 */

error_reporting(E_ALL & ~E_NOTICE);

require __DIR__ . '/vendor/autoload.php';
require_once "sys/myAutoLoader.php";

use models\YMOfferDetailed;
use models\YMTireCategory;



/*$ymservice = new YandexMarketApiService();
$ymservice->TestReq("https://api.content.market.yandex.ru/v1/model/5080959/offers.json?fields=discounts,filters&geo_id=213&sort=price&count=1&page=1&2142418422=-5066978&2142418412=-449829776&2142418426=-2022570288");
die;*/
/*
$m = new \models\YMModelDetailed();
$m->name = "dunlop sp sport lm703";
$m->geoId = 213; //todo это не удобно
$m->returnFields = "all";


$res = $ymservice->FindYMModelsByName($m);

$filters = [

	YMTireCategory::FILTER_WIDTH_ID => 215,
	YMTireCategory::FILTER_HEIGHT_ID => 40,
	YMTireCategory::FILTER_DIAMETER_ID => 17,
	YMTireCategory::FILTER_LOADINDEX_ID => 87,
	YMTireCategory::FILTER_SPEEDINDEX_ID => 'w',
	YMTireCategory::FILTER_SEASON_ID => YMTireCategory::FILTER_SEASON_VALUE_SUMMER

];

$offers = $ymservice->FindYMOffersByModel($res[0]->id,
	$res[0]->geoId,
	YMOfferDetailed::RET_KEY_FIELDS_DICOUNTS.",".YMOfferDetailed::RET_KEY_FIELDS_FILTERS,
	$filters,
	"price");

var_dump($offers);*/

const YANDEX_MARKET_TIRES_MINIMAL_PRICES = "ymTiresMinimalPrices";
$db = new MysqlDbController();

$controller = new YandexMarketController($db, new YandexMarketApiService(),
	new CsvUniversalRenderer(YANDEX_MARKET_TIRES_MINIMAL_PRICES), new ProductsUpdater($db));

$controller->UpdateProducts();
$controller->UpdateProductsAvailability();

$controller
	->GetMinimalPricesOnTires("москва")
	->Render(YandexMarketController::RENDER_FOR_USER)
	->Render(YandexMarketController::RENDER_FOR_1C);