<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 11.04.16
 * Time: 11:14
 */

namespace models;

use YMModel;

class YMModelDetailed extends YMModel
{
	const REQ_KEY_MODEL_NAME = "name";
	const REQ_KEY_CURRENCY = "currency";
	const REQ_KEY_GEO_ID = "geo_id";

	const RET_KEY_FIELDS_CATEGORY = "category";
	const RET_KEY_FIELDS_DISCOUNTS = "discounts";
	const RET_KEY_FIELDS_FACTS = "facts";
	const RET_KEY_FIELDS_MEDIA = "media";
	const RET_KEY_FIELDS_PHOTO = "photo";
	const RET_KEY_FIELDS_PRICE = "price";
	const RET_KEY_FIELDS_RAITING = "raiting";
	const RET_KEY_FIELDS_OFFERS = "offers";
	const RET_KEY_FIELDS_VENDOR = "vendor";

	/* значения для запроса
	см https://tech.yandex.ru/market/content-data/doc/dg/reference/model-match-docpage/ параметр fields */
	/**
	 * @deprecated $returnFields
	 */
	public $returnFields;
	public $geoId;

	/*
	 *  возвращенные значение
	 *  некоторые значения будут содержать stdclass
	 *  см. что возвращает яндекс-маркет-апи
	*/

	public $minModelPrice;
	public $vendorId;
	public $vendorName;

	public $name;
	public $offerCount;
	public $type;
	public $categoryId;

	/**
	 * @deprecated $photo
	 */
	public $photo;

	/**
	 * @deprecated $price
	 */
	public $price;

	/**
	 * @deprecated $vendor
	 */
	public $vendor;
	public $media;
	public $facts;

	/*public function GetAllReturnFields() {

		return [

			self::RET_KEY_FIELDS_CATEGORY,
			self::RET_KEY_FIELDS_DISCOUNTS,
			self::RET_KEY_FIELDS_FACTS,
			self::RET_KEY_FIELDS_MEDIA,
			self::RET_KEY_FIELDS_PHOTO,
			self::RET_KEY_FIELDS_OFFERS,
			self::RET_KEY_FIELDS_VENDOR,
			self::RET_KEY_FIELDS_RAITING

		];

	}*/
}