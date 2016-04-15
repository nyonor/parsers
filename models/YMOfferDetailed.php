<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 12.04.16
 * Time: 18:08
 */

namespace models;

use \YMOffer;


class YMOfferDetailed extends YMOffer
{
	const RET_KEY_FIELDS_DICOUNTS = "discounts";
	const RET_KEY_FIELDS_FILTERS = "filters";

	/*
	 * Сейчас эти значения не используются
	 */
	public $geoId;
	public $filters;
	public $count;
	public $delivery;
	public $fields;
	public $groupBy; //todo нужны значения
	public $how; //todo values!
	public $latitude;
	public $longitude;
	public $page;
	public $shipping; //todo values!
	public $shopId;
	public $shopRegions;
	public $sort; //todo values!

	public $total;
	public $items;
	public $options;
	public $outlet;
	public $geo;
	public $phone;
	public $photos;
	public $price;
	public $shopInfo;



	public static function GetAllReturnFields() {

		return self::RET_KEY_FIELDS_DICOUNTS . "," . self::RET_KEY_FIELDS_FILTERS;

	}
}