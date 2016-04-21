<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 05.04.16
 * Time: 17:03
 */
class YMShop
{
	public $id;
	public $name;
	public $siteUrl;
	public $jsonRaw;

	public static function Factory($shopInfoJson)
	{

		$decoded = json_decode($shopInfoJson);

		$ymShop = new YMShop();

		$ymShop->id = $decoded->id;
		$ymShop->jsonRaw = $shopInfoJson;
		$ymShop->name = $decoded->name;
		$ymShop->siteUrl = empty($decoded->url) ? null : $decoded->url;

		return $ymShop;
	}
}