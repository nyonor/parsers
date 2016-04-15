<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 05.04.16
 * Time: 17:01
 */
class YMOffer
{
	public $ymOfferId;
	public $shopId;
	public $price;
	public $minimalPrice;
	public $ymOfferUpdateDate;
	public $ymOfferJsonRaw;
	public $ymModelId;
	public $ymModelIdReturned;
	public $ymRegionId;
	public $cae;

	public $YMShop;

	/**
	 * Создать экземпляр из строки
	 * @param $json string YM
	 * @return YMOffer[]
	 */
	public static function Factory($json) {

		$result = [];

		$decoded = json_decode($json);
		foreach($decoded->offers->items as $offerItem) {
			$ymOffer = new YMOffer();
			$ymOffer->ymOfferJsonRaw = json_encode($offerItem);
			$ymOffer->minimalPrice = $offerItem->price->value;
			$ymOffer->shopId = $offerItem->price->shopInfo->id;
			$ymOffer->ymOfferId = $offerItem->id;
			$ymOffer->price = $offerItem->price->value;
			$ymOffer->ymModelIdReturned = $offerItem->modelId;

			$ymShop = YMShop::Factory(json_encode($offerItem->shopInfo));
			$ymOffer->YMShop = $ymShop;
			$ymOffer->shopId = $ymShop->id;

			$result[] = $ymOffer;
		}

		return $result;

	}
}